<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_article']['palettes']['__selector__'][] = 'contaoSeoIsConditionalEntity';
$GLOBALS['TL_DCA']['tl_article']['subpalettes']['contaoSeoIsConditionalEntity'] = 'contaoSeoEntityCondition';

$GLOBALS['TL_DCA']['tl_article']['fields']['contaoSeoIsConditionalEntity'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true, 'tl_class' => 'clr'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_article']['fields']['contaoSeoEntityCondition'] = [
    'exclude' => true,
    'inputType' => 'textarea',
    'eval' => ['mandatory' => true, 'useRawRequestData' => true, 'style' => 'height:40px', 'tl_class' => 'clr'],
    'sql' => 'text NULL',
];

foreach($GLOBALS['TL_DCA']['tl_article']['palettes'] as $key=>$value) {
    if ($key == '__selector__') {
        continue;
    }
    $GLOBALS['TL_DCA']['tl_article']['palettes'][$key] .= ',contaoSeoIsConditionalEntity';
}
