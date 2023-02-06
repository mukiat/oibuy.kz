<?php

namespace App\Services\OfflineStore;

use App\Models\OfflineStore;
use App\Models\StoreGoods;
use App\Models\StoreProducts;
use App\Models\StoreUser;
use App\Repositories\Common\BaseRepository;

class OfflineStoreDataHandleService
{
    /**
     * 门店信息列表
     *
     * @param array $store_id
     * @param array $data
     * @param int $limit
     * @return array
     */
    public static function getOfflineStoreDataList($store_id = [], $data = [], $limit = 0)
    {
        $store_id = BaseRepository::getExplode($store_id);

        if (empty($store_id)) {
            return [];
        }

        $store_id = $store_id ? array_unique($store_id) : [];

        $data = $data ? $data : '*';

        $res = OfflineStore::select($data)->whereIn('id', $store_id);

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['id']] = $val;
            }
        }

        return $arr;
    }

    /**
     * 获取商品门店商品数量
     *
     * @param $whereStore
     * @return array
     */
    public static function getStoreGoodsCount($whereStore)
    {

        $goods_id = BaseRepository::getExplode($whereStore['goods_id'] ?? '');
        $goods_id = array_unique($goods_id);

        $res = StoreGoods::select('store_id', 'goods_id')->whereIn('goods_id', $goods_id);
        $res = BaseRepository::getToArrayGet($res);
        $store_id = BaseRepository::getKeyPluck($res, 'store_id');

        if (isset($whereStore['is_confirm']) || !empty($store_id)) {
            $store_id = OfflineStore::query()->select('id')
                ->whereIn('id', $store_id)
                ->where('is_confirm', $whereStore['is_confirm']);

            if (isset($whereStore['province']) && $whereStore['province'] > 0) {
                $store_id = $store_id->where('province', $whereStore['province']);
            }

            if (isset($whereStore['city']) && $whereStore['city'] > 0) {
                $store_id = $store_id->where('city', $whereStore['city']);
            }

            if (isset($whereStore['district']) && $whereStore['district'] > 0) {
                $store_id = $store_id->where('district', $whereStore['district']);
            }

            $store_id = $store_id->pluck('id');
            $store_id = BaseRepository::getToArray($store_id);
        }

        if ($store_id) {
            $sql = [
                'whereIn' => [
                    [
                        'name' => 'store_id',
                        'value' => $store_id
                    ],
                    [
                        'name' => 'goods_id',
                        'value' => $goods_id
                    ]
                ]
            ];

            $res = BaseRepository::getArraySqlGet($res, $sql);
        } else {
            $res = [];
        }

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {

                $arr[$val['goods_id']]['store_count'] = $arr[$val['goods_id']]['store_count'] ?? 0;

                if ($val) {
                    $arr[$val['goods_id']]['list'] = $val;
                    $arr[$val['goods_id']]['store_count'] += 1;
                }
            }
        }

        return $arr;
    }

    /**
     * 获取商品门店商品货品数量
     *
     * @param $whereStore
     * @return array
     */
    public static function getStoreGoodsProductCount($whereStore)
    {

        $goods_id = BaseRepository::getExplode($whereStore['goods_id'] ?? '');
        $goods_id = array_unique($goods_id);

        $res = StoreProducts::select('store_id', 'goods_id')->whereIn('goods_id', $goods_id);
        $res = BaseRepository::getToArrayGet($res);
        $store_id = BaseRepository::getKeyPluck($res, 'store_id');

        if (isset($whereStore['is_confirm']) && $store_id) {
            $store_id = OfflineStore::query()->select('id')
                ->whereIn('id', $store_id)
                ->where('is_confirm', $whereStore['is_confirm']);

            if (isset($whereStore['province']) && $whereStore['province'] > 0) {
                $store_id = $store_id->where('province', $whereStore['province']);
            }

            if (isset($whereStore['city']) && $whereStore['city'] > 0) {
                $store_id = $store_id->where('city', $whereStore['city']);
            }

            if (isset($whereStore['district']) && $whereStore['district'] > 0) {
                $store_id = $store_id->where('district', $whereStore['district']);
            }

            $store_id = $store_id->pluck('id');
            $store_id = BaseRepository::getToArray($store_id);
        }

        if ($store_id) {

            $sql = [
                'whereIn' => [
                    [
                        'name' => 'store_id',
                        'value' => $store_id
                    ],
                    [
                        'name' => 'goods_id',
                        'value' => $goods_id
                    ]
                ]
            ];

            $res = BaseRepository::getArraySqlGet($res, $sql);
        }

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {

                $arr[$val['goods_id']]['store_count'] = $arr[$val['goods_id']]['store_count'] ?? 0;

                if ($val) {
                    $arr[$val['goods_id']]['list'] = $val;
                    $arr[$val['goods_id']]['store_count'] += 1;
                }
            }
        }

        return $arr;
    }

    /**
     * 店铺门店列表
     *
     * @param array $store_id
     * @param array $data
     * @param int $limit
     * @return array
     */
    public static function getStoreUserDataList($store_id = [], $data = [], $limit = 0)
    {
        $store_id = BaseRepository::getExplode($store_id);

        if (empty($store_id)) {
            return [];
        }

        $store_id = $store_id ? array_unique($store_id) : [];

        $data = $data ? $data : '*';

        $res = StoreUser::select($data)->whereIn('store_id', $store_id);

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['id']] = $val;
            }
        }

        return $arr;
    }
}
