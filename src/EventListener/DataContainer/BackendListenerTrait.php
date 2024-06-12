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

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;

trait BackendListenerTrait
{
    public function __construct(
        private readonly ContaoFramework $framework
    ) {
    }
    public function generateRedirectUrl($dc, $alias): string
    {
        $pageModel = $this->framework->getAdapter(PageModel::class)->findById($dc->id);

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

        $currentUrl .= '/' . $alias . $pageModel->urlSuffix;

        return $currentUrl;
    }
}
