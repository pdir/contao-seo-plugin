<?php

declare(strict_types=1);

/*
 * pdir contao seo plugin for Contao Open Source CMS
 *
 * Copyright (c) 2025 pdir / digital agentur // pdir GmbH
 *
 * @package    contao-seo-plugin
 * @link       https://pdir.de/contao-seo-plugin
 * @license    LGPL-3.0-or-later
 * @author     pdir GmbH <https://pdir.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pdir\ContaoSeoPlugin\Util;

use Contao\Database;
use Contao\System;

class SearchStatsAutomator extends System
{
    public function purgeSearchStats(): void
    {
        $objDatabase = Database::getInstance();

        // Truncate the table
        $objDatabase->execute("TRUNCATE TABLE tl_search_stats");

        System::getContainer()->get('monolog.logger.contao.cron')->info('Purged the search stats log');
    }
}
