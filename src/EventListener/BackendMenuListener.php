<?php

namespace Pdir\ContaoSeoPlugin\EventListener;

use Pdir\ContaoSeoPlugin\Controller\SearchStatsController;
use Contao\CoreBundle\Event\ContaoCoreEvents;
use Contao\CoreBundle\Event\MenuEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(ContaoCoreEvents::BACKEND_MENU_BUILD, priority: -255)]
class BackendMenuListener
{
    public function __invoke(MenuEvent $event): void
    {
        $factory = $event->getFactory();
        $tree = $event->getTree();

        if ('mainMenu' !== $tree->getName()) {
            return;
        }

        $contentNode = $tree->getChild('content');

        $node = $factory
            ->createItem('search-stats', ['route' => SearchStatsController::class])
            ->setLabel('Search Statistik')
            ->setLinkAttribute('title', 'Search Statistik')
            ->setLinkAttribute('class', 'search-stats')
        ;

        $contentNode->addChild($node);
    }
}
