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

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;

#[AsHook('loadDataContainer')]
class BackendUsabilityListener
{
    public function __invoke(string $table): void
    {
        if ('tl_page' === $table) {
            // Show keywords
            $GLOBALS['TL_DCA']['tl_page']['list']['label']['fields'][] = 'contaoSeoMainKeyword';
            $GLOBALS['TL_DCA']['tl_page']['list']['label']['fields'][] = 'contaoSeoSecondaryKeywords';
            $GLOBALS['TL_DCA']['tl_page']['list']['label']['format'] = $GLOBALS['TL_DCA']['tl_page']['list']['label']['format'].' [<span style="color:cornflowerblue;padding-left:3px;font-weight:normal;">%s</span><span class="pdir-page-tree-label" style="color:darkgray;padding-left:3px;padding-right:3px;">%s</span>]';

            $GLOBALS['TL_MOOTOOLS'][] = '<style>.pdir-page-tree-label:not(:empty):before {content: "; ";}</style>';
        }
    }
}
