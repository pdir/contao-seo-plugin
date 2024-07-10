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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/_indexNow', defaults: ['_scope' => 'backend', '_token_check' => false])]
class IndexNowController
{
    const SEARCH_ENGINES = [
        'microsoftBing' => 'www.bing.com',
        'naver' =>  'searchadvisor.naver.com',
        'seznam.cz' => 'search.seznam.cz',
        'yandex' => 'yandex.com',
        'yep' => 'indexnow.yep.com'
    ];

    #[Route('/send', name: 'indexNow_send', methods: ['GET'])]
    public function sendAction(Request $request): Response
    {
        $url = $request->query->get('url');
        $engine = $request->query->get('engine');
        $key = $request->query->get('key');

        if (!$url && !$engine && !$key) {
            return new Response('Nothing to do!');
        }

        // Generate endpoint url from params
        $endpointUrl = $this->generateEndpointUrl($engine, $url, $key);

        // Engine is not supported
        if (null === $endpointUrl) {
            return new JsonResponse([
                'message'       => 'Search engine `'.$engine.'` is not supported.',
                'code'          => 501,
            ], 501);
        }

        $result = $this->callUrl($endpointUrl);

        if ((202 === $result[0] || 200 === $result[0]) && '' === $result[1]) {
            $result[1] = 'The url was transferred to the search engine `'.$engine.'`.';
        }

        return new JsonResponse([
            'message' => $this->isJson($result[2])? json_decode($result[2]) : $result[2],
            'errors' => $result[1],
            'code' => $result[0],
        ], $result[0]);
    }

    private function generateEndpointUrl(string $engine, string $url, string $key): ?string
    {
        if (isset(self::SEARCH_ENGINES[$engine])) {
            return 'https://'.self::SEARCH_ENGINES[$engine].'/indexnow?url='.$url.'&key=indexNow'.$key;
        }

        return null;
    }

    private function callUrl(string $url): array
    {
        $ch = curl_init();
        curl_setopt_array(
            $ch,
            [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_ENCODING => 'UTF-8',
            ]
        );
        $result = curl_exec($ch);

        $errors = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [$code, $errors?? null, $result];
    }

    private function isJson($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
