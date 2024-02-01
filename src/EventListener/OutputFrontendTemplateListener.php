<?php

namespace Pdir\ContaoSeoPlugin\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Input;
use Contao\NewsModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

#[AsHook('outputFrontendTemplate')]
class OutputFrontendTemplateListener
{
    private string $assetsDir = 'bundles/pdircontaoseoplugin';

    public function __invoke(string $buffer, string $template): string
    {
        if (!System::getContainer()->get('contao.routing.scope_matcher')->isFrontendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) {
            return $buffer;
        }

        if (!(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))->hasPreviousSession()) {
            return $buffer;
        }

        global $objPage;
        $rootPage = PageModel::findOneById($objPage->rootId);

        if (str_contains($template, 'fe_page') && true === boolval($rootPage->contaoSeoActivateIndexNow)) {

            $mainKeyword = $objPage->contaoSeoMainKeyword;
            $secondaryKeywords = $objPage->contaoSeoSecondaryKeywords;

            // check if current page is a news reader
            $alias = Input::get('auto_item');

            if (isset($alias)) {
                $newsModel = NewsModel::findBy('alias', $alias);

                if (null !== $newsModel) {
                    $mainKeyword = $newsModel->contaoSeoMainKeyword;
                    $secondaryKeywords = $newsModel->contaoSeoSecondaryKeywords;
                }
            }


            $engines = json_encode(StringUtil::deserialize($rootPage->contaoSeoIndexNowEngines));

            $GLOBALS['TL_JAVASCRIPT'][] = $this->assetsDir . '/js/toolbar.js';
            $GLOBALS['TL_CSS'][] = $this->assetsDir . '/scss/toolbar.scss|static';
            $GLOBALS['TL_BODY'][] = <<<JS
<script>
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
