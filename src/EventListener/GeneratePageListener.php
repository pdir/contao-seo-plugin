<?php

declare(strict_types=1);

/*
 * pdir contao seo plugin for Contao Open Source CMS
 *
 * Copyright (c) 2025 pdir / digital agentur // pdir GmbH
 *
 * @package    contao-seo-plugin
 * @link       https://pdir.de/contao-seo-plugin
 * @license    LGPL-3.0-or-later
 * @author     pdir GmbH <https://pdir.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pdir\ContaoSeoPlugin\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Environment;
use Contao\Input;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use Contao\System;
use Pdir\ContaoSeoPlugin\Model\ErrorLogModel;
use Pdir\ContaoSeoPlugin\Service\SearchStatsHelper;

#[AsHook('generatePage')]
class GeneratePageListener
{
    private SearchStatsHelper $helper;

    public function __construct(SearchStatsHelper $helper)
    {
        $this->helper = $helper;
    }

    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        // Is error page
        if ('error_404' === $pageModel->type) {
            if ($rootPage = PageModel::findById($pageModel->rootId)) {
                // Check if the error log is active
                if (isset($rootPage->contaoSeoActivateErrorLog) && true === boolval($rootPage->contaoSeoActivateErrorLog)) {
                    // Write error to database
                    $error = new ErrorLogModel();
                    $error->tstamp = time();
                    $error->source_url = Environment::get('uri');
                    $error->user_agent = Environment::get('agent') ? Environment::get('agent')->string : '-';
                    $error->ip = Environment::get('ip');
                    $error->save();
                }
            }
        }


        // Check for a search keyword hit if XMLHttpRequest is not used
        if ($rootPage = PageModel::findById($pageModel->rootId)) {

            if (isset($rootPage->contaoSeoActivateSearchStats) && true === boolval($rootPage->contaoSeoActivateSearchStats)) {
                // Get the search keywords
                $keywords = Input::get('keywords');

                // Use search hit count url
                if ($keywords && '' !== $keywords && isset($rootPage->contaoSeoXhrSearchHit) && true === boolval($rootPage->contaoSeoXhrSearchHit)) {

                    $hitCountUrl = System::getContainer()
                        ->get('router')
                        ->generate('seo_plugin_search_hit', ['keywords' => $this->helper->urlSafeHashEncode($keywords)]);

                    $GLOBALS['TL_BODY'][] = '<img src="' . $hitCountUrl . '" alt="" width="1" height="1" aria-hidden="true" style="position:absolute">';
                }

                // Use Page listener
                if ($keywords && '' !== $keywords  && isset($rootPage->contaoSeoXhrSearchHit) && false === boolval($rootPage->contaoSeoXhrSearchHit)) {
                    $this->helper->createOrUpdate($keywords);
                }
            }
        }
    }
}
