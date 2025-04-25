<?php

declare(strict_types=1);

/*
 * pdir contao seo plugin for Contao Open Source CMS
 *
 * Copyright (c) 2024 pdir / digital agentur // pdir GmbH
 *
 * @package    contao-seo-plugin
 * @link       https://pdir.de/contao-seo-plugin
 * @license    LGPL-3.0-or-later
 * @author     pdir GmbH <https://pdir.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pdir\ContaoSeoPlugin\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Pdir\ContaoSeoPlugin\Backend\Automator;

#[AsHook('removeOldFeeds')]
class RemoveOldFeedsListener
{
    public function __invoke(): array
    {
        return Automator::getSitemapXmlFiles();
    }
}
