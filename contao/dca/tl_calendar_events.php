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

use Contao\CoreBundle\DataContainer\PaletteManipulator;

// Add to title legend
PaletteManipulator::create()
    ->addField('urlRewriteList', 'author')
    ->applyToPalette('default', 'tl_calendar_events')
;

// Add to meta legend
PaletteManipulator::create()
    // ->addField('contaoSeoToolbar', 'type')
    ->addField(['contaoSeoMainKeyword', 'contaoSeoSecondaryKeywords'], 'robots')
    ->applyToPalette('default', 'tl_calendar_events')
;

$GLOBALS['TL_DCA']['tl_news']['fields']['contaoSeoMainKeyword'] = [
    'search' => true,
    'inputType' => 'text',
    'eval' => ['mandatory'=>false, 'basicEntities'=>true, 'maxlength'=>255, 'tl_class'=>'w50'],
    'sql' => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_news']['fields']['contaoSeoSecondaryKeywords'] = [
    'search' => true,
    'inputType' => 'text',
    'eval' => ['mandatory'=>false, 'basicEntities'=>true, 'maxlength'=>255, 'tl_class'=>'w50'],
    'sql' => "varchar(255) NOT NULL default ''"
];
