<?php

declare(strict_types=1);

use Contao\DataContainer;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_search_stats'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'closed' => true,
        'sql' => [
            'keys' => [
                'id'       => 'primary',
                'keywords' => 'unique',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['count DESC'],
            'flag' => DataContainer::SORT_MONTH_ASC,
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['keywords', 'count'],
            'format' => '%s (Anzahl: %s)',
        ],
    ],
    'fields' => [
        'id' => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default 0",
        ],
        'keywords' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'count' => [
            'sql' => "int(10) unsigned NOT NULL default 1",
        ],
        'year' => [
            'sql' => "smallint(4) unsigned NOT NULL default 0",
        ],
        'month' => [
            'sql' => "tinyint(2) unsigned NOT NULL default 0",
        ],
    ],
];
