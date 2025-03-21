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

use Contao\CoreBundle\Event\ContaoCoreEvents;
use Contao\CoreBundle\Event\RobotsTxtEvent;
use Contao\File;
use Contao\Folder;
use Contao\StringUtil;
use Contao\System;
use Pdir\ContaoSeoPlugin\Backend\Automator;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

use webignition\RobotsTxt\Directive\Directive;
use webignition\RobotsTxt\Directive\UserAgentDirective;
use webignition\RobotsTxt\Inspector\Inspector;
use webignition\RobotsTxt\Record\Record;

#[AsEventListener(ContaoCoreEvents::ROBOTS_TXT)]
class RobotsTxtListener
{
    public function __invoke(RobotsTxtEvent $event): void
    {
        $file = $event->getFile();

        $rootPage = $event->getRootPage();

        if ($rootPage->contaoSeoAddSitemapToRobotsTxt) {
            $xmlFiles = Automator::getSitemapXmlFiles(true, $rootPage);

            foreach ($xmlFiles as $xmlFile) {
                $sitemap = \sprintf(
                    '%s%s/share/'.$xmlFile,
                    $rootPage->useSSL ? 'https://' : 'http://',
                    $rootPage->dns ?: $event->getRequest()->server->get('HTTP_HOST'),
                );

                $file->getNonGroupDirectives()->add(new Directive('Sitemap', $sitemap));
            }
        }
    }
}
