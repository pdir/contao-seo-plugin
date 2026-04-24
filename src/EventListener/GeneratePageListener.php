<?php

declare(strict_types=1);

/*
 * pdir contao seo plugin for Contao Open Source CMS
 *
 * Copyright (c) 2026 pdir / digital agentur // pdir GmbH
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

use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Environment;
use Contao\ModuleModel;
use Contao\PageRegular;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Pdir\ContaoSeoPlugin\Model\ErrorLogModel;

#[AsHook('generatePage')]
class GeneratePageListener
{
    private string $injectionLocation;
    private array $elements = [];

    public function __construct(string $injectionLocation)
    {
        $this->injectionLocation = $injectionLocation;
    }

    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        // Is error page
        if('error_404' === $pageModel->type) {
            if ($rootPage = PageModel::findById($pageModel->rootId)) {
                // Check if error log is active
                if (isset($rootPage->contaoSeoActivateErrorLog) && true === boolval($rootPage->contaoSeoActivateErrorLog)) {
                    // Write error to database
                    $error = new ErrorLogModel();
                    $error->tstamp = time();
                    $error->source_url = Environment::get('uri');
                    $error->user_agent = Environment::get('agent')? Environment::get('agent')->string : '-';
                    $error->ip = Environment::get('ip');
                    $error->save();
                }
            }
        }

        // For FAQ schema feature
        if('regular' === $pageModel->type) {
            $this->addFaqSchema($pageModel);
        }

    }

    private function addFaqSchema(PageModel $pageModel): void
    {
        $faqEntities = [];
        $arrElements = [];

        $articles = ArticleModel::findPublishedByPidAndColumn($pageModel->id, 'main');

        if (null === $articles) {
            return;
        }

        #pdump($articles);

        foreach($articles as $article) {
            pdump('Article id: '.$article->id);
            pdump('Article title: '.$article->title);
            array_merge($arrElements, $this->findArticleElements($article->id, 'tl_article'));
        }

        if (empty($this->elements)) {
            pdump('empty');
            return;
        }

        foreach ($this->elements as $element) {
            pdump($element);
            switch ($element->type) {

                case 'text':
                    $faqEntities = array_merge(
                        $faqEntities,
                        $this->extractFromTextElement($element)
                    );
                    break;

                case 'accordionSingle':
                    $faqEntities[] = [
                        "@type" => "Question",
                        "name" => strip_tags($element->headline),
                        "acceptedAnswer" => [
                            "@type" => "Answer",
                            "text" => strip_tags($element->text)
                        ]
                    ];
                    break;
            }
        }

        if (empty($faqEntities)) {
            return;
        }

        $schema = [
            "@context" => "https://schema.org",
            "@type" => "FAQPage",
            "mainEntity" => $faqEntities
        ];

        $json = '<script type="application/ld+json">'
            . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . '</script>';

        if ($this->injectionLocation === 'body') {
            $GLOBALS['TL_BODY'][] = $json;
        } else {
            $GLOBALS['TL_HEAD'][] = $json;
        }
    }

    private function findArticleElements(int $id, string $ptable): array
    {
        $arrElements = [];

        pdump($id, $ptable);

        $elements = ContentModel::findPublishedByPidAndTable($id, $ptable);

        if ($elements !== null)
        {
            while ($elements->next())
            {
                pdump('---- Element ID: '.$elements->id);
                pdump('PID: '.$elements->pid);
                pdump('TYPE: '.$elements->type);

                if ('module' === $elements->type) {
                    pdump('Module', $elements);
                }

                switch ($elements->type) {
                    case 'module':
                        pdump('is module');
                        // Check if a module has content children
                        $module = ModuleModel::findById($elements->module);
                        pdump($module);

                        $children = $this->findArticleElements($module->id, 'tl_content');
                        pdump('module children');
                        pdump($children);

                        break;
                    default:
                        // Check if an element has content children
                        $children = $this->findArticleElements($elements->id, 'tl_content');
                }



                pdump('childrens of '.$elements->headline);
                pdump($children);

                if (count($children) > 0) {
                    foreach ($children as $child) {
                        if (!$child->contaoSeoAddFaqSchema) {
                            $arrElements[] = $child->current();
                            continue;
                        }

                        $this->elements[] = $elements->current();
                    }
                }

                if (!$elements->contaoSeoAddFaqSchema) {
                    continue;
                }

                # $this->elements[] = $elements->current();

                return $arrElements;
            }
        }

        return $arrElements ?? [];
    }

    private function extractFromTextElement(ContentModel $contentModel): array
    {
        $faq = [];

        $header = StringUtil::deserialize($contentModel->sectionHeadline, true);

        if (empty($header)) {
            $header = StringUtil::deserialize($contentModel->headline, true);
        }

        $question = trim($header['value']?? $contentModel->title);

        $faq[] = [
            "@type" => "Question",
            "name" => $question,
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => trim(strip_tags(System::getContainer()->get('contao.insert_tag.parser')->replace($contentModel->text)))
            ]
        ];

        return $faq;
    }

    private function extractFromTextBuffer(string $html): array
    {
        $faq = [];

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $headings = $xpath->query('//h2');

        foreach ($headings as $heading) {

            $question = trim($heading->textContent);
            $answerNode = $heading->nextSibling;

            while ($answerNode && $answerNode->nodeName !== 'p') {
                $answerNode = $answerNode->nextSibling;
            }

            if ($answerNode) {
                $faq[] = [
                    "@type" => "Question",
                    "name" => $question,
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => trim(strip_tags($answerNode->textContent))
                    ]
                ];
            }
        }

        return $faq;
    }
}
