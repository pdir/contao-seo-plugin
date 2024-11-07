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

use Composer\InstalledVersions;
use Contao\BackendUser;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Input;
use Contao\NewsModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[AsHook('outputFrontendTemplate')]
class OutputFrontendTemplateListener
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $security
    ) {
    }
    private string $assetsDir = 'bundles/pdircontaoseoplugin';

    public function __invoke(string $buffer, string $template): string
    {
        if (!System::getContainer()->get('contao.routing.scope_matcher')->isFrontendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) {
            return $buffer;
        }

        if (!(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))->hasPreviousSession()) {
            return $buffer;
        }

        $user = BackendUser::getInstance();

        if (null === $user) {
            return $buffer;
        }

        // Is not admin or no access is granted
        if (!$user->isAdmin && !$user->pdirSeoPlugin) { // $this->security->isGranted('contao_user.pdirSeoPlugin', 'canUseToolbar')) {
            return $buffer;
        }

        global $objPage;
        $rootPage = PageModel::findOneById($objPage->rootId);

        if (\str_contains($template, 'fe_page')) {

            $mainKeyword = $objPage->contaoSeoMainKeyword;
            $secondaryKeywords = $objPage->contaoSeoSecondaryKeywords;

            // check if current page is a newsreader
            $alias = Input::get('auto_item');

            if (isset($alias) && InstalledVersions::isInstalled('contao/news-bundle')) {
				$newsModel = NewsModel::findBy('alias', $alias);

				if (null !== $newsModel) {
					$mainKeyword = $newsModel->contaoSeoMainKeyword;
					$secondaryKeywords = $newsModel->contaoSeoSecondaryKeywords;
				}
            }

            $engines = \json_encode(StringUtil::deserialize($rootPage->contaoSeoIndexNowEngines));

            $GLOBALS['TL_JAVASCRIPT'][] = $this->assetsDir . '/js/toolbar.js';
            $GLOBALS['TL_CSS'][] = $this->assetsDir . '/scss/toolbar.scss|static';
            $GLOBALS['TL_BODY'][] = <<<JS
<script>
  var cspIndexNowActive =  '$rootPage->contaoSeoActivateIndexNow';
  var cspIndexNowEngines = $engines;
  var cspIndexNowKey = '$rootPage->contaoSeoIndexNowKey';
  var cspMainKeyword = '$mainKeyword';
  var cspSecondaryKeywords = '$secondaryKeywords';
</script>
JS;
        }

        return $buffer;
    }
}
