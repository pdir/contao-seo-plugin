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

        $response  = new Response('indexNow'.$rootPage->contaoSeoIndexNowKey , 200);
        $response->headers->set('Content-Type', 'text/plain');

        return $response;
    }
}
