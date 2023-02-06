<?php

namespace App\Modules\Search\Services;

use App\Api\Foundation\Components\HttpResponse;
use App\Repositories\Common\DscRepository;
use GuzzleHttp\Client;

/**
 * Class SearchService
 * @package App\Modules\Search\Services
 */
class SearchService
{
    use HttpResponse;

    /**
     * @param string $keyword
     * @param int $brand_id
     * @param int $page
     * @return array
     */
    public function search($keyword = '', $brand_id = 0, $page = 1)
    {
        $searchResult = $this->getSearchResult($keyword, $brand_id, $page);

        $total = $searchResult['total']['value'];

        $data = [];
        foreach ($searchResult['hits'] as $key => $item) {
            $goods = $item['_source'];
            $goods['goods_name_keyword'] = $goods['goods_name'];
            $goods['goods_thumb'] = app(DscRepository::class)->getImagePath($goods['goods_thumb']);
            $goods['shop_price_formatted'] = app(DscRepository::class)->getPriceFormat($goods['shop_price']);
            $goods['url'] = app(DscRepository::class)->buildUri('goods', ['gid' => $goods['goods_id']]);
            $data[$key] = $goods;
        }

        return ['list' => $data, 'total' => $total];
    }

    /**
     * 获取ES搜素结果
     * @param string $keyword
     * @param int $brand_id
     * @param int $page
     * @return mixed|null
     */
    private function getSearchResult($keyword = '', $brand_id = 0, $page = 1)
    {
        if (empty($keyword)) {
            return null;
        }

        $client = new Client();
        $size = 20;

        $query = [
            'q' => $keyword . ' AND is_alone_sale:(1) AND is_on_sale:(1) AND is_delete:(0) AND is_show:(1)',
            'size' => $size,
            'from' => ($page - 1) * $size,
        ];

        if ($brand_id > 0) {
            $query['q'] .= ' AND brand_id:(' . $brand_id . ')';
        }

        $ESConfig = config('scout.elasticsearch');

        $options = [];
        if (!is_null($ESConfig['username'])) {
            $options = ['auth' => [$ESConfig['username'], $ESConfig['password']]];
        }

        $esIndex = config('scout.elasticsearch.index');
        $uri = current($ESConfig['hosts']) . '/' . $esIndex . '/_search?' . http_build_query($query, '', '&');
        $contents = $client->get($uri, $options)->getBody()->getContents();
        $result = json_decode($contents, true);

        return isset($result['hits']) ? $result['hits'] : null;
    }
}
