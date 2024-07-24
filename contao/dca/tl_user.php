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

PaletteManipulator::create()
    ->addLegend('pdirSeoPlugin_legend', null)
    ->addField('pdirSeoPlugin', 'pdirSeoPlugin_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('extend', 'tl_user')
    ->applyToPalette('custom', 'tl_user')
;

$GLOBALS['TL_DCA']['tl_user']['fields']['pdirSeoPlugin'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['multiple' => true],
    'options' => &$GLOBALS['TL_LANG']['tl_user']['pdirSeoPluginOptions'],
    'sql' => ['type' => 'blob', 'notnull' => false],
];

$GLOBALS['TL_DCA']['tl_user']['fields']['pdirSeoPluginRewriteWidget'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'clr w50'],
    'sql' => "char(1) NOT NULL default '0'",
];

foreach ($GLOBALS['TL_DCA']['tl_user']['palettes'] as $key => $value) {
    $GLOBALS['TL_DCA']['tl_user']['palettes'][$key] = preg_replace('(([,;}]useCE)([,;{]))i', '$1,pdirSeoPluginRewriteWidget$2', $GLOBALS['TL_DCA']['tl_user']['palettes'][$key]);
}




