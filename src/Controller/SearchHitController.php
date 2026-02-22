<?php

namespace Pdir\ContaoSeoPlugin\Controller;

use Pdir\ContaoSeoPlugin\Service\SearchStatsHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('_seo_plugin/search-hit/{keywords}', name: 'seo_plugin_search_hit', requirements: ['keywords' => '.+'], defaults: ['_scope' => 'frontend'])]
class SearchHitController
{
    private SearchStatsHelper $helper;

    public function __construct(SearchStatsHelper $helper)
    {
        $this->helper = $helper;
    }

    public function __invoke(string $keywords): Response
    {
        $keywords = $this->helper->urlSafeHashDecode($keywords);

        try {
            $this->helper->createOrUpdate($keywords);
        } catch (\Throwable $e) {
            // ignore
        }

        $response = new Response(base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw=='));
        $response->headers->set('Content-Type', 'image/gif');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache');
        $response->headers->addCacheControlDirective('must-revalidate');

        return $response;
    }
}
