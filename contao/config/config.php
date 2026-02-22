<?php

use Pdir\ContaoSeoPlugin\Model\ErrorLogModel;
use Pdir\ContaoSeoPlugin\Model\UrlRewriteModel;
use Pdir\ContaoSeoPlugin\Model\SearchStatsModel;
use Pdir\ContaoSeoPlugin\Util\ErrorLogAutomator;
use Pdir\ContaoSeoPlugin\Util\SearchStatsAutomator;

/*
 * Backend modules
 */
if (!isset($GLOBALS['BE_MOD']['pdir']) || !\is_array($GLOBALS['BE_MOD']['pdir'])) {
    \array_splice($GLOBALS['BE_MOD'], 1,0, ['pdir' => []]);
}

$GLOBALS['BE_MOD']['pdir']['contaoSeoErrorLog'] = [
    'tables' => ['tl_error_log'],
];

#$GLOBALS['BE_MOD']['pdir']['contaoSeoSearchStats'] = [
#    'tables' => ['tl_search_stats'],
#];

/*
 * Models
 */
$GLOBALS['TL_MODELS']['tl_error_log'] = ErrorLogModel::class;
$GLOBALS['TL_MODELS']['tl_url_rewrite'] = UrlRewriteModel::class;
$GLOBALS['TL_MODELS']['tl_search_stats'] = SearchStatsModel::class;

/*
 *  Purge jobs
 */
$GLOBALS['TL_PURGE']['tables']['error_log'] = [
    'callback' => [ErrorLogAutomator::class, 'purgeErrorLog'],
    'affected' => ['tl_error_log']
];

$GLOBALS['TL_PURGE']['tables']['search_stats'] = [
    'callback' => [SearchStatsAutomator::class, 'purgeSearchStats'],
    'affected' => ['tl_search_stats']
];

/*
 * Permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'pdirSeoPlugin';
