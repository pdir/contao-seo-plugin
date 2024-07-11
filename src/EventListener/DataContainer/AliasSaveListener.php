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

namespace Pdir\ContaoSeoPlugin\EventListener\DataContainer;

use Contao\BackendUser;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\PageModel;
use Pdir\ContaoSeoPlugin\Model\UrlRewriteModel;

#[AsCallback(table: 'tl_page', target: 'fields.alias.save')]
#[AsCallback(table: 'tl_news', target: 'fields.alias.save')]
#[AsCallback(table: 'tl_calendar_events', target: 'fields.alias.save')]
class AliasSaveListener
{
    use BackendListenerTrait;
    public function __invoke($value, DataContainer $dc)
    {
        if (!BackendUser::getInstance()->pdirSeoPluginRewriteWidget) {
            return $value;
        }

        if ($value === ($dc->activeRecord->alias ?? null)) {
            return $value;
        }

        [$pageModel, $currentAlias, $currentAliasPrefix] = $this->getPageModelAndAliases($dc);

        // Do nothing if current alias is empty
        if (empty($currentAlias)) {
            return $value;
        }

        // Get old alias
        $oldAlias = $this->generateRedirectUrl($pageModel, $currentAlias);

        // Get new alias
        $newAlias = $this->generateRedirectUrl($pageModel, $currentAliasPrefix? $currentAliasPrefix.'/'.$value : $value);

        // Do nothing if new alias is empty
        if (empty($newAlias)) {
            return $value;
        }

        // Check if the old url has already been rewritten, we want to update it
        $oldRewrites = UrlRewriteModel::findBy(['responseUri=?'], [$oldAlias]);

        if(null !== $oldRewrites) {
            // We need to update all the entries found
            foreach($oldRewrites as $oldRewrite) {
                // Set new Alias
                $oldRewrite->responseUri = $newAlias;

                // Update existing comment
                if ('contao-seo-plugin' !== $oldRewrite->comment) {
                    $oldRewrite->comment = '' === $oldRewrite->comment ? 'contao-seo-plugin' : $oldRewrite->comment;
                }

                $oldRewrite->save();
            }
        }

        // Add new rewrite to url rewrite table
        $rewrite = new UrlRewriteModel();
        $rewrite->name = $dc->activeRecord->title?? $dc->activeRecord->headline;
        $rewrite->type = 'basic';
        $rewrite->priority = 0;
        $rewrite->inactice = 0;
        $rewrite->requestPath = '/'.$currentAlias;
        $rewrite->responseCode = 301;
        $rewrite->responseUri = $newAlias;
        $rewrite->tstamp = time();
        $rewrite->comment = 'contao-seo-plugin';
        $rewrite->save();

        // Return the processed value
        return $value;
    }
}
