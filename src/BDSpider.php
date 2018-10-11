<?php
/*
 * MIT License
 * 
 */
namespace BDSpider;

use QL\QueryList;
use GuzzleHttp\Client as GHttpClient;
use GuzzleHttp\RequestOptions as GROption;
use BDSpider\Exceptions\SpiderException;

class BDSpider
{

    /**
     * @var GHttpClient 
     */
    private static $ghttpclient = null;

    const URL_SEARCH = 'https://www.baidu.com/s';
    const KEY_KEYWORD = 'wd';
    const KEY_PAGENUM = 'pn';
    const ANALYSIS_SLEEP = 1;

    public static function getGHttpClient()
    {
        if (is_null(self::$ghttpclient)) {
            self::$ghttpclient = new GHttpClient();
        }
        return self::$ghttpclient;
    }
    
    public static function search($keyword, $max = 10)
    {
        $query = [
            self::KEY_KEYWORD => $keyword,
            self::KEY_PAGENUM => 0,
            'rqlang'          => 'cn',
        ];

        $requestUrl = self::URL_SEARCH . '?' . http_build_query($query);
        // search by baidu.
        $links = QueryList::get($requestUrl)->rules([
                'list' => ['.c-container .t a', ['href' => 'href', 'text' => 'text']],
            ])->range('body')->query()->getData()->all();
        
        // list result.
        $searcResults = $links[ 0 ][ 'list' ] ?? [];
        if (!$searcResults) {
            throw new SpiderException('search error : no matched results.');
        }
        return self::analysisSearchResultRawUrls($searcResults, $max);
    }

    private static function analysisSearchResultRawUrls($urls, $max)
    {
        $result = $urls;
        $num = 0;
        foreach ($urls as $key => $url) {
            $num ++;
            if ($num > $max) {
                break;
            }
            if (!isset($url['href'])) {
                unset($result[$key]);
                continue;
            }
            sleep(self::ANALYSIS_SLEEP);
            $url[ 'href' ] = self::getRealUrl($url[ 'href' ]);
            $result[ $key ] = $url;
        }
        return array_values($result);
    }

    public static function getRealUrl($href)
    {
        $client = self::getGHttpClient();
        $realRequestOption = [
            GROption::ALLOW_REDIRECTS => false
        ];
        $response = $client->request('GET', $href, $realRequestOption);
        $location = $response->getHeader('Location');
        $reallink = $location[ 0 ] ?? $href;
        return $reallink;
    }
}
