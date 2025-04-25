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

#[AsCallback(table: 'tl_article', target: 'fields.contaoSeoEntityCondition.save')]
#[AsCallback(table: 'tl_content', target: 'fields.contaoSeoEntityCondition.save')]
#[AsCallback(table: 'tl_module', target: 'fields.contaoSeoEntityCondition.save')]
class EntityConditionSaveListener
{
    public function __invoke($value, DataContainer $dc)
    {
        if (empty($value)) {
            return $value;
        }

        // Return the processed value
        return $value;
    }

    private function validateCondition($code)
    {
        // Only safe characters (letters, numbers, operators and spaces) are allowed
        if (preg_match('/^[a-zA-Z0-9+\-*/%\(\)\s]+$/', $code)) {
            return true;
        }

        return false;
    }
}
