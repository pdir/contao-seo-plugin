<?php

declare(strict_types=1);

use Contao\Backend;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\System;

$GLOBALS['TL_DCA']['tl_error_log'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'closed' => true,
        'notEditable' => true,
        'notCopyable' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'tstamp' => 'index'
            ]
        ]
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTABLE,
            'fields' => ['tstamp', 'id'],
            'panelLayout' => 'filter;sort,search,limit',
            'defaultSearchField' => 'source_url'
        ],
        'label' => [
            'fields' => ['tstamp', 'source_url', 'user_agent', 'ip'],
            'format' => '<span style="color:#999;padding-right:3px">[%s]</span> <span style="padding-right:10px">%s</span> <span style="padding-right:10px;color:#999">%s</span> <span style="color:#006494">%s</span>',
        ],
        'operations' => [
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_error_log']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"',
            ],

            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_error_log']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
                'attributes' => 'style="margin-right: 3px"',
            ],
        ]
    ],

    // Fields
    'fields' => [
        'id' => [
            'flag' => 12,
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp' => [
            'filter' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_DAY_DESC,
            'sql' => "int(10) unsigned NOT NULL default 0"
        ],
        'source_url' => [
            'search' => true,
            'filter' => true,
            'sorting' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'user_agent' => [
            'search' => true,
            'filter' => true,
            'sorting' => true,
            'sql' => "text NULL"
        ],
        'ip' => [
            'search' => true,
            'filter' => true,
            'sorting' => true,
            'sql' => "varchar(64) NOT NULL default ''"
        ],
    ]
];
