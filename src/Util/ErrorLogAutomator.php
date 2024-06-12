<?php

declare(strict_types=1);

/*
 * social feed bundle for Contao Open Source CMS
 *
 * Copyright (c) 2024 pdir / digital agentur // pdir GmbH
 *
 * @package    social-feed-bundle
 * @link       https://github.com/pdir/social-feed-bundle
 * @license    http://www.gnu.org/licences/lgpl-3.0.html LGPL
 * @author     Mathias Arzberger <develop@pdir.de>
 * @author     Philipp Seibt <develop@pdir.de>
 * @author     pdir GmbH <https://pdir.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pdir\ContaoSeoPlugin\Util;

use Contao\Database;
use Contao\System;

class ErrorLogAutomator extends System
{
    public function purgeErrorLog(): void
    {
        $objDatabase = Database::getInstance();

        // Truncate the table
        $objDatabase->execute("TRUNCATE TABLE tl_error_log");

        System::getContainer()->get('monolog.logger.contao.cron')->info('Purged the 404 error log');
    }
}
