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

use Contao\CalendarEventsModel;
use Contao\CalendarModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\NewsModel;
use Contao\NewsArchiveModel;
use Contao\PageModel;

trait BackendListenerTrait
{
    public function __construct(
        private readonly ContaoFramework $framework
    ) {
    }
    public function generateRedirectUrl($pageModel, $alias): string
    {
        $rootPage = PageModel::findById($pageModel->rootId);
        $currentUrl = '';

        if ($pageModel->rootUseSSL) {
            $currentUrl .= 'https://';
        }

        if (!$pageModel->rootUseSSL) {
            $currentUrl .= 'http://';
        }

        if ($pageModel->domain) {
            $currentUrl .= $pageModel->domain;
        }

        $currentUrl .= '/' . $alias . $rootPage->urlSuffix?? '';

        return $currentUrl;
    }

    public function getPageModelAndAliases(DataContainer $dc): array
    {
        $pageModel = null;
        $currentAlias = null;
        $currentAliasPrefix = '';

        if ('tl_page' === $dc->table) {
            $pageModel = $this->framework->getAdapter(PageModel::class)->findById($dc->id);
            $currentAlias = $pageModel->alias;
        }

        // get page model from archive
        if ('tl_news' === $dc->table) {
            $newsModel = $this->framework->getAdapter(NewsModel::class)->findById($dc->id);
            $newsArchiveModel = $this->framework->getAdapter(NewsArchiveModel::class)->findById($newsModel->pid);
            $pageModel = $this->framework->getAdapter(PageModel::class)->findById($newsArchiveModel->jumpTo);
            $currentAlias = $pageModel->alias.'/'.$newsModel->alias;
            $currentAliasPrefix = $pageModel->alias;
        }

        if('tl_calendar_events' === $dc->table) {
            $eventModel = $this->framework->getAdapter(CalendarEventsModel::class)->findById($dc->id);
            $calendarModel = $this->framework->getAdapter(CalendarModel::class)->findById($eventModel->pid);
            $pageModel = $this->framework->getAdapter(PageModel::class)->findById($calendarModel->jumpTo);
            $currentAlias = $pageModel->alias.'/'.$eventModel->alias;
            $currentAliasPrefix = $pageModel->alias;
        }

        return [$pageModel, $currentAlias, $currentAliasPrefix];
    }
}
