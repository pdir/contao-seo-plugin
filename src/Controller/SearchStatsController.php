<?php

namespace Pdir\ContaoSeoPlugin\Controller;

use Contao\CoreBundle\Controller\AbstractBackendController;
use Contao\Database;
use Contao\System;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route('%contao.backend.route_prefix%/search-stats', name: self::class, defaults: ['_scope' => 'backend'])]
class SearchStatsController extends AbstractBackendController
{
    public function __invoke(): Response
    {
        // @todo Implement month and year statistics

        $data = Database::getInstance()
            ->execute("SELECT keywords, count FROM tl_search_stats ORDER BY count DESC LIMIT 20")
            ->fetchAllAssoc();

        $keywords = array_column($data, 'keywords');
        $counts = array_column($data, 'count');

        $allKeywords = Database::getInstance()
            ->execute("SELECT keywords, count FROM tl_search_stats ORDER BY count DESC LIMIT 1000")
            ->fetchAllAssoc();

        System::loadLanguageFile('tl_search_stats');

        return $this->render(
            '@Contao_PdirContaoSeoPluginBundle/be_search_stats.html.twig',
            [
                #'error' => 'Oh no, an error!',
                'title' => 'Search Statistik',
                'headline' => 'Search Statistik',
                'topKeywords' => $keywords?? null,
                'topCounts' => $counts?? null,
                'allKeywords' => $allKeywords?? null,
            ]
        );
    }
}
