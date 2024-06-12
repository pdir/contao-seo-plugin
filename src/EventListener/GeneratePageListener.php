<?php

declare(strict_types=1);

/*
 * pdir contao seo plugin for Contao Open Source CMS
 *
 * Copyright (c) 2024 pdir / digital agentur // pdir GmbH
 *
 * @package    contao-seo-plugin
 * @link       https://pdir.de/contao-seo-plugin
 * @license   LGPL-3.0-or-later
 * @author     pdir GmbH <https://pdir.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pdir\ContaoSeoPlugin\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Environment;
use Contao\PageRegular;
use Contao\LayoutModel;
use Contao\PageModel;
use Pdir\ContaoSeoPlugin\Model\ErrorLogModel;

#[AsHook('generatePage')]
class GeneratePageListener
{
    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        // Is error page
        if('error_404' === $pageModel->type) {
            if ($rootPage = PageModel::findById($pageModel->rootId)) {
                // Check if error log is active
                if ($rootPage->contaoSeoActivateErrorLog && 1 === $rootPage->contaoSeoActivateErrorLog) {
                    // Write error to database
                    $error = new ErrorLogModel();
                    $error->tstamp = time();
                    $error->source_url = Environment::get('uri');
                    $error->user_agent = Environment::get('agent')? Environment::get('agent')->string : '-';
                    $error->ip = Environment::get('ip');
                    $error->save();
                }
            }
        }
    }
}
