<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'contaoSeoIsConditionalEntity';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['contaoSeoIsConditionalEntity'] = 'contaoSeoEntityCondition';

$GLOBALS['TL_DCA']['tl_module']['fields']['contaoSeoIsConditionalEntity'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true, 'tl_class' => 'clr'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['contaoSeoEntityCondition'] = [
    'exclude' => true,
    'inputType' => 'textarea',
    'eval' => ['mandatory' => true, 'useRawRequestData' => true, 'style' => 'height:40px', 'tl_class' => 'clr'],
    'sql' => 'text NULL',
];

foreach($GLOBALS['TL_DCA']['tl_module']['palettes'] as $key=>$value) {
    if ($key == '__selector__') {
        continue;
    }
    $GLOBALS['TL_DCA']['tl_module']['palettes'][$key] .= ',contaoSeoIsConditionalEntity';
}
