<?php

$GLOBALS['TL_DCA']['tl_content']['fields']['contaoSeoAddFaqSchema'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'clr', 'submitOnChange'=>true],
    'sql' => ['type' => 'boolean', 'default' => false]
];

$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'contaoSeoAddFaqSchema';

foreach ($GLOBALS['TL_DCA']['tl_content']['palettes'] as $key => $palette) {
    if (is_string($palette) && $key !== '__selector__') {
        $GLOBALS['TL_DCA']['tl_content']['palettes'][$key] .= ';{contao_seo_legend},contaoSeoAddFaqSchema';
    }
}
