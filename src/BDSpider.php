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

    const URL_SEARCH = 'https://www.baidu.com/s';
    const KEY_KEYWORD = 'wd';
    const KEY_PAGENUM = 'pn';

    public static function search($keyword, $max = 10)
    {
        $result = [];

        $query = [
            self::KEY_KEYWORD => $keyword,
            self::KEY_PAGENUM => 0,
        ];

        $requestUrl = self::URL_SEARCH . '?' . http_build_query($query);
        // search by baidu.
        $links = QueryList::get($requestUrl)->rules([
                'list' => ['.result.c-container>.t>a', ['href' => 'href', 'text' => 'text']],
            ])->range('body')->query()->getData()->all();

        // list result.
        $searcResults = $links[ 0 ][ 'list' ] ?? [];
        if (!$searcResults) {
            throw new SpiderException('search error : no matched results.');
        }
        
        for ($i = 0; $i < 10; $i ++) {
            if (!isset($searcResults[ $i ])) {
                break;
            }
            if ($i >= $max) {
                break;
            }
            $spoofinglink = $searcResults[ $i ];
            $href = $spoofinglink[ 'href' ];
            $client = new GHttpClient();
            $realRequestOption = [
                GROption::ALLOW_REDIRECTS => false
            ];
            // sleep 1.
            sleep(1);
            $response = $client->request('GET', $href, $realRequestOption);
            $location = $response->getHeader('Location');
            $reallink = $location[0] ?? $href;
            
            $result[$i] = [
                'text' => $spoofinglink['text'],
                'href' => $reallink,
            ];
        }
        
        return $result;
    }
}
