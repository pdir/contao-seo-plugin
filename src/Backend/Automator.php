<?php

declare(strict_types=1);

/*
 * pdir contao seo plugin for Contao Open Source CMS
 *
 * Copyright (c) 2024 pdir / digital agentur // pdir GmbH
 *
 * @package    contao-seo-plugin
 * @link       https://pdir.de/contao-seo-plugin
 * @license    LGPL-3.0-or-later
 * @author     pdir GmbH <https://pdir.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pdir\ContaoSeoPlugin\Backend;

use Contao\Automator as ContaoAutomator;
use Contao\ArticleModel;
use Contao\CoreBundle\Routing\Page\PageRegistry;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\File;
use Contao\Folder;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use SitemapPHP\Sitemap;

class Automator extends System
{
    private PageRegistry $pageRegistry;
    private readonly ContaoAutomator $contaoAutomator;

    public function __construct(PageRegistry $pageRegistry) {
        parent::__construct();
        $this->contaoAutomator = new ContaoAutomator();
        $this->pageRegistry = $pageRegistry;
    }

    /**
     * Regenerate the XML files.
     */
    public function generateSitemapFiles(): void
    {
        $shareDir = System::getContainer()->getParameter('contao.web_dir') . '/share';
        $rootPages = $this->getRootPages();

        foreach($rootPages as $rootPage) {
            $loc = (1 === $rootPage->useSSL? 'https://' : 'http://').$rootPage->dns;

            $sitemap = new Sitemap(''); # $sitemap = new Sitemap($loc);
            $sitemap->setPath($shareDir.'/'.$rootPage->contaoSeoSitemapName);

            # get urls
            $urls = $this->getUrls($rootPage);

            foreach($urls as $url) {
                $sitemap->addItem($url);
                # @todo add priority $sitemap->addItem($url, '0.6', 'weekly', $post['created_at']);
            }

            #if (50000 <= \count($urls)) {
            $sitemap->createSitemapIndex($loc.'/share/'.$rootPage->contaoSeoSitemapName, 'Today');
            #}
        }

        // Also empty the shared cache so there are no links to deleted files
        $this->contaoAutomator->purgePageCache();

        System::getContainer()->get('monolog.logger.contao.cron')->info('Regenerated the SEO Plugin sitemap XML file(s)');
    }

    public static function getRootPages()
    {
        $pageModel = System::getContainer()->get('contao.framework')->getAdapter(PageModel::class);
        $rootPages = $pageModel->findBy(['contaoSeoActivateSitemap=?','dns!=?'], [1, '']);

        if (null === $rootPages) {
            System::getContainer()->get('monolog.logger.contao.cron')->warning('No active root pages found for sitemap generation.');
        }

        return $rootPages;
    }

    private function getUrls($rootPage): array
    {
        $urls = [];

        $pages = $this->getPageAndArticleUrls((int) $rootPage->id);
        $urls[] = $this->callLegacyHook($rootPage, $pages);

        return array_unique(array_merge(...$urls));
    }

    private function callLegacyHook(PageModel $rootPage, array $pages): array
    {
        $systemAdapter = System::getContainer()->get('contao.framework')->getAdapter(System::class);

        // HOOK: take additional pages
        if (isset($GLOBALS['TL_HOOKS']['contaoSeoGetSearchablePages']) && \is_array($GLOBALS['TL_HOOKS']['contaoSeoGetSearchablePages'])) {
            foreach ($GLOBALS['TL_HOOKS']['contaoSeoGetSearchablePages'] as $callback) {
                $pages = $systemAdapter->importStatic($callback[0])->{$callback[1]}($pages, $rootPage->id, true, $rootPage->language);
            }
        }

        return $pages;
    }

    private function getPageAndArticleUrls(int $parentPageId): array
    {
        $pageModelAdapter = System::getContainer()->get('contao.framework')->getAdapter(PageModel::class);

        // Since the publication status of a page is not inherited by its child
        // pages, we have to use findByPid() instead of findPublishedByPid() and
        // filter out unpublished pages in the foreach loop (see #2217)
        $pageModels = $pageModelAdapter->findByPid($parentPageId, ['order' => 'sorting']);

        if (null === $pageModels) {
            return [];
        }

        $articleModelAdapter = System::getContainer()->get('contao.framework')->getAdapter(ArticleModel::class);

        $result = [];

        // Recursively walk through all subpages
        foreach ($pageModels as $pageModel) {
            // Load details in order to inherit permission settings (see #5556)
            $pageModel->loadDetails();

            if ($pageModel->protected && !System::getContainer()->get('security.authorization_checker')->isGranted(ContaoCorePermissions::MEMBER_IN_GROUPS, $pageModel->groups)) {
                continue;
            }

            $isPublished = $pageModel->published && (!$pageModel->start || $pageModel->start <= time()) && (!$pageModel->stop || $pageModel->stop > time());

            if (
                $isPublished
                && !$pageModel->requireItem
                && 'noindex,nofollow' !== $pageModel->robots
                && $this->pageRegistry->supportsContentComposition($pageModel)
                && $this->pageRegistry->isRoutable($pageModel)
                && 'html' === $this->pageRegistry->getRoute($pageModel)->getDefault('_format')
            ) {
                try {
                    $urls = [$pageModel->getAbsoluteUrl()];

                    // Get articles with teaser
                    if (null !== ($articleModels = $articleModelAdapter->findPublishedWithTeaserByPid($pageModel->id, ['ignoreFePreview' => true]))) {
                        foreach ($articleModels as $articleModel) {
                            $urls[] = $pageModel->getAbsoluteUrl('/articles/'.($articleModel->alias ?: $articleModel->id));
                        }
                    }

                    $result[] = $urls;
                } catch (ExceptionInterface $exception) {
                    // Skip URL for this page but generate child pages
                }
            }

            $result[] = $this->getPageAndArticleUrls((int) $pageModel->id);
        }

        return array_merge(...$result);
    }

    public static function getSitemapXmlFiles($withExtension = false, $rootPage = false): array
    {
        $xmlFiles = [];

        $rootPages = [$rootPage];

        if (!$rootPage) {
            $rootPages = self::getRootPages();
        }

        if (null === $rootPages) {
            return [];
        }

        foreach($rootPages as $rootPage) {
            $shareDir = System::getContainer()->getParameter('contao.web_dir') . '/share';

            // Scan for xml files which we want to remove
            foreach (Folder::scan($shareDir) as $file)
            {
                if (\is_dir($shareDir . '/' . $file))
                {
                    continue; // see #6652
                }

                $objFile = new File(StringUtil::stripRootDir($shareDir) . '/' . $file);

                // For removeOldFeeds
                if (!$withExtension && $objFile->extension == 'xml' && \str_contains($file, $rootPage->contaoSeoSitemapName.'sitemap'))
                {
                    $xmlFiles[] = \str_replace('.xml', '', $file);
                }

                // For robots.txt if we found a sitemap index file
                if ($withExtension && $objFile->extension == 'xml' && \str_contains($file, $rootPage->contaoSeoSitemapName.'sitemap-index'))
                {
                    return [$file];
                }

                // For robots.txt if there is only a single sitemap file
                if ($withExtension && $objFile->extension == 'xml' && \str_contains($file, $rootPage->contaoSeoSitemapName.'sitemap'))
                {
                    $xmlFiles[] = $file;
                }
            }
        }

        return $xmlFiles;
    }
}
