<?php

namespace App\Services\Store;

use App\Models\StoreGoods;
use App\Models\StoreProducts;
use App\Repositories\Common\BaseRepository;

class StoreDataHandleService
{

    /**
     * 门店商品信息列表
     *
     * @param array $goods_id
     * @param array $data
     * @param int $limit
     * @return array
     */
    public static function getStoreGoodsDataList($goods_id = [], $data = [], $limit = 0)
    {
        $goods_id = BaseRepository::getExplode($goods_id);

        if (empty($goods_id)) {
            return [];
        }

        $goods_id = $goods_id ? array_unique($goods_id) : [];

        $data = $data ? $data : '*';

        $res = StoreGoods::select($data)->whereIn('goods_id', $goods_id);

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['goods_id']] = $val;
            }
        }

        return $arr;
    }

    /**
     * 门店商品货品库存
     *
     * @param array $id
     * @param array $data
     * @param string $field
     * @param int $limit
     * @return array
     */
    public static function getStoreProductsDataList($id = [], $data = [], $field = 'goods_id', $limit = 0)
    {
        $id = BaseRepository::getExplode($id);

        if (empty($id)) {
            return [];
        }

        $id = $id ? array_unique($id) : [];

        $data = $data ? $data : '*';

        $res = StoreProducts::select($data);

        if (stripos($field, 'goods_id') !== false) {
            $res = $res->whereIn('goods_id', $id);
        } else {
            $res = $res->whereIn('product_id', $id);
        }

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                if (stripos($field, 'goods_id') !== false) {
                    $arr[$val['goods_id']][] = $val;
                } else {
                    $arr[$val['product_id']] = $val;
                }
            }
        }

        return $arr;
    }
}