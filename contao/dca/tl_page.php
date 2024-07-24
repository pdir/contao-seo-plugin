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

// Add new legend
PaletteManipulator::create()
    ->addLegend('contao_seo_legend', 'meta_legend')
    ->addField(['contaoSeoActivateErrorLog', 'contaoSeoActivateIndexNow'], 'contao_seo_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('root', 'tl_page')
    ->applyToPalette('rootfallback', 'tl_page')
;


// Add to routing legend
PaletteManipulator::create()
    ->addField('urlRewriteList', 'routePriority')
    ->applyToPalette('regular', 'tl_page')
;

// Add to meta legend
PaletteManipulator::create()
    // ->addField('contaoSeoToolbar', 'type')
    ->addField(['contaoSeoMainKeyword', 'contaoSeoSecondaryKeywords'], 'robots')
    ->applyToPalette('regular', 'tl_page')
;


$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'contaoSeoActivateIndexNow';
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['contaoSeoActivateIndexNow'] = 'contaoSeoIndexNowEngines,contaoSeoIndexNowKey';

// add fields
$GLOBALS['TL_DCA']['tl_page']['fields']['contaoSeoToolbar'] = [
    'exclude' => true,
];

$GLOBALS['TL_DCA']['tl_page']['fields']['contaoSeoActivateErrorLog'] = [
    'inputType' => 'checkbox',
    'eval' => ['doNotCopy'=>true, 'submitOnChange'=>true, 'tl_class' => 'w50 clr'],
    'sql' => ['type' => 'boolean', 'default' => false]
];

$GLOBALS['TL_DCA']['tl_page']['fields']['contaoSeoActivateIndexNow'] = [
    'inputType' => 'checkbox',
    'eval' => ['doNotCopy'=>true, 'submitOnChange'=>true, 'tl_class' => 'w50 clr'],
    'sql' => ['type' => 'boolean', 'default' => false]
];

$GLOBALS['TL_DCA']['tl_page']['fields']['contaoSeoIndexNowEngines'] = [
    'inputType' => 'checkbox',
    'options' => ['microsoftBing', 'naver', 'seznam.cz', 'yandex', 'yep'],
    'eval' => ['multiple' => true, 'tl_class' => 'w50 clr'],
    'reference' => &$GLOBALS['TL_LANG']['tl_page'],
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_page']['fields']['contaoSeoIndexNowKey'] = [
    'search' => true,
    'inputType' => 'text',
    'eval' => ['mandatory'=>true, 'minlength' => 8, 'maxlength'=>128, 'tl_class'=>'w50'],
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_page']['fields']['contaoSeoMainKeyword'] = [
    'search' => true,
    'inputType' => 'text',
    'eval' => ['mandatory'=>false, 'basicEntities'=>true, 'maxlength'=>255, 'tl_class'=>'w50'],
    'sql' => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_page']['fields']['contaoSeoSecondaryKeywords'] = [
    'search' => true,
    'inputType' => 'text',
    'eval' => ['mandatory'=>false, 'basicEntities'=>true, 'maxlength'=>255, 'tl_class'=>'w50'],
    'sql' => "varchar(255) NOT NULL default ''"
];
