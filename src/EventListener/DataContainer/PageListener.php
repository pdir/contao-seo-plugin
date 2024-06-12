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

namespace Pdir\ContaoSeoPlugin\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\PageModel;
use Pdir\ContaoSeoPlugin\Model\UrlRewriteModel;
use Twig\Environment;

class PageListener
{
    use BackendListenerTrait;
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly Environment $twig,
    ) {
    }

    /*
     * @todo Add backend seo toolbar
    #[AsCallback(table: 'tl_page', target: 'fields.contaoSeoToolbar.attributes')]
    public function getToolbarAttributes(array $attributes, DataContainer|null $dc = null): array
    {
        if (!$dc || 'text' !== ($dc->getCurrentRecord()['type'] ?? null)) {
            return $attributes;
        }

        $attributes['label'] = 'Custom text label';

        return $attributes;
    } */

    #[AsCallback(table: 'tl_page', target: 'fields.urlRewriteList.input_field')]
    public function generateUrlRewriteList(DataContainer $dc): string
    {
        $pageModel = $this->framework->getAdapter(PageModel::class)->findById($dc->id);

        if (!$pageModel) {
            return '';
        }

        $currentUrl = $this->generateRedirectUrl($dc, $pageModel->alias);

        $rewriteModel = UrlRewriteModel::findBy(['responseUri=?'], [$currentUrl]);
        $rewrites = [];

        if (null !== $rewriteModel) {

            foreach ($rewriteModel as $item) {
                $rewrites[] = $item;

                // try to get redirects for current urls
                $children = UrlRewriteModel::findBy(['responseUri=?'], ['https://'.$pageModel->domain.$item->requestPath]);

                if (null !== $children) {
                    foreach ($children as $child) {
                        $rewrites[] = $child;
                    }
                }
            }
        }

        return $this->twig->render('@Contao_PdirContaoSeoPluginBundle/be_url_rewrite_list.html.twig', [
            'id' => $pageModel->id,
            'alias' => $pageModel->alias,
            'url' => $currentUrl,
            'domain' => $pageModel->domain,
            'suffix' => $pageModel->urlSuffix,
            'protocol' => $pageModel->rootUseSSL? 'https://' : 'http://',
            'rewrites' => !empty($rewrites)? $rewrites : null
        ]);
    }
}
