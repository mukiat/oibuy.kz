<?php

namespace App\Services\Store;

use App\Models\MerchantsShopInformation;
use App\Models\OfflineStore;
use App\Models\StoreGoods;
use App\Models\StoreProducts;
use App\Repositories\Common\BaseRepository;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;

class StoreCommonService
{
    protected $merchantCommonService;

    public function __construct(
        MerchantCommonService $merchantCommonService
    )
    {
        $this->merchantCommonService = $merchantCommonService;
    }

    /**
     * 店铺列表
     *
     * @return array|bool|\Illuminate\Cache\CacheManager|mixed
     * @throws \Exception
     */
    public function getCommonStoreList()
    {
        $cache_name = "get_common_store_list";
        $arr = cache($cache_name);
        $arr = !is_null($arr) ? $arr : [];

        if (empty($arr)) {
            $res = MerchantsShopInformation::select('shop_id', 'user_id')->where('merchants_audit', 1)->orderby('shop_id', 'ASC');
            $res = BaseRepository::getToArrayGet($res);
            if ($res) {

                $seller_id = BaseRepository::getKeyPluck($res, 'user_id');
                $merchantList = MerchantDataHandleService::getMerchantInfoDataList($seller_id);

                foreach ($res as $key => $row) {

                    $shop_information = $merchantList[$row['user_id']] ?? []; //通过ru_id获取到店铺信息;

                    $arr[$key]['shop_id'] = $row['shop_id'];
                    $arr[$key]['ru_id'] = $row['user_id'];
                    $arr[$key]['store_name'] = $shop_information['shop_name'] ?? ''; //店铺名称
                }
            }

            cache()->forever($cache_name, $arr);
        }

        return $arr;
    }

    /**
     * 判断是否支持门店自提
     *
     * @param array $goods_id
     * @return mixed
     */
    public function judgeStoreGoods($goods_id = [])
    {
        $goods_id = BaseRepository::getExplode($goods_id);

        if (empty($goods_id)) {
            return [];
        }

        $goods_id = array_unique($goods_id);

        $store = StoreGoods::distinct()->select('store_id', 'goods_id')
            ->whereIn('goods_id', $goods_id)
            ->where('goods_number', '>', 0);

        $store = BaseRepository::getToArrayGet($store);
        $store_id = BaseRepository::getKeyPluck($store, 'store_id');

        $goods = [];
        if ($store_id) {
            $store_goods = OfflineStore::select('id')->where('is_confirm', 1)
                ->whereIn('id', $store_id);
            $store_goods = BaseRepository::getToArrayGet($store_goods);

            $arr1 = [];
            if ($store_goods) {
                foreach ($store_goods as $key => $val) {
                    $arr1[$val['id']] = $val;
                }
            }

            if ($store) {
                foreach ($store as $k => $v) {
                    $goods[$v['goods_id']][] = $arr1[$v['store_id']] ?? [];
                }
            }
        }

        $store = StoreProducts::distinct()->select('store_id', 'goods_id')->whereIn('goods_id', $goods_id);
        $store = BaseRepository::getToArrayGet($store);
        $store_id = BaseRepository::getKeyPluck($store, 'store_id');

        $products = [];
        if ($store_id) {
            $store_products = OfflineStore::select('id')->where('is_confirm', 1)
                ->whereIn('id', $store_id);
            $store_products = BaseRepository::getToArrayGet($store_products);

            $arr2 = [];
            if ($store_products) {
                foreach ($store_products as $key => $val) {
                    $arr2[$val['id']] = $val;
                }
            }

            if ($store) {
                foreach ($store as $k => $v) {
                    $products[$v['goods_id']][] = $arr2[$v['store_id']] ?? [];
                }
            }
        }

        $data = [
            'goods' => $goods,
            'products' => $products
        ];

        return $data;
    }

    /**
     * 校验商品是否可选门店商品
     *
     * @param int $goods_id
     * @param array $judgeStoreGoods
     * @return bool
     */
    public function getJudge($goods_id = 0, $judgeStoreGoods = [])
    {
        $flag = false;

        $goods = $judgeStoreGoods['goods'] ?? [];
        if ($goods) {
            foreach ($goods as $k => $v) {
                if ($k == $goods_id) {
                    $flag = true;
                }

                if ($flag == true) {
                    return $flag;
                }
            }
        }

        $products = $judgeStoreGoods['products'] ?? [];
        if ($products) {
            foreach ($products as $k => $v) {
                if ($k == $goods_id) {
                    $flag = true;
                }

                if ($flag == true) {
                    return $flag;
                }
            }
        }

        return $flag;
    }
}
