<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    // apply the field "custom_field" after the field "username"
    ->addField('pdirSeoAiUsage', 'text')
    ->addField('pdirSeoAiSource', 'text')

    // now the field is registered in the PaletteManipulator
    // but it still has to be registered in the globals array:
    ->applyToPalette('text', 'tl_content')
    ->applyToPalette('html', 'tl_content')
;

$GLOBALS['TL_DCA']['tl_content']['fields']['pdirSeoAiUsage'] = [
    'filter' => true,
    'inputType' => 'select',
    'options' => ['100%', '75%', '50%', '25%', '0%'],
    'eval' => ['chosen'=>true, 'tl_class'=>'w25'],
    'sql' => ['type' => 'string', 'length' => 64, 'default' => '0%']
];

$GLOBALS['TL_DCA']['tl_content']['fields']['pdirSeoAiSource'] = [
    'filter' => true,
    'inputType' => 'select',
    'options' => &$GLOBALS['TL_LANG']['tl_content']['pdirSeoAiSources'],
    'eval' => ['chosen'=>true, 'tl_class'=>'w25', 'includeBlankOption'=>true],
    'sql' => ['type' => 'string', 'length' => 128, 'default' => '']
];
