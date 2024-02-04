<?php

namespace Pdir\ContaoSeoPlugin\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/indexNow{key}.txt', name: IndexNowTxtController::class, defaults: ['_scope' => 'frontend'])]
#[AsController]
class IndexNowTxtController
{
    public function __construct(
        ContaoFramework $framework
        # private readonly PageFinder $pageFinder
    ) {
        $this->framework = $framework;
    }
    public function __invoke(Request $request, string $key): Response
    {
        # c5 only $rootPage = $this->pageFinder->findRootPageForHostAndLanguage($request->getHost());

        $adapter = $this->framework->getAdapter(PageModel::class);

        $rootPage = $adapter->findPublishedFallbackByHostname(
            $request->getHost(),
            ['fallbackToEmpty' => false]
        );

        if (!$rootPage) {
            throw new NotFoundHttpException();
        }

        if ($rootPage->contaoSeoIndexNowKey !== $key) {
            throw new NotFoundHttpException();
        }

        return new Response($rootPage->contaoSeoIndexNowKey);
    }
}
