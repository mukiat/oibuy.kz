<?php

namespace App\Modules\Search\Models;

use App\Models\Goods as BaseGoods;
use App\Services\Merchant\MerchantDataHandleService;
use Illuminate\Support\Arr;
use Laravel\Scout\Searchable;

/**
 * Class Goods
 * @package App\Modules\Search\Models
 */
class Goods extends BaseGoods
{
    use Searchable;

    /**
     * 获取索引名称
     *
     * @return string
     */
    public function searchableAs()
    {
        return config('scout.elasticsearch.index');
    }

    /**
     * 获取模型的可搜索数据
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $shopList = MerchantDataHandleService::getMerchantInfoDataList($this->user_id);
        $shopInfo = $shopList[$this->user_id] ?? [];

        $array = $this->toArray();
        $array['brand_name'] = $this->getBrand->brand_name;
        $array['rz_shop_name'] = $shopInfo['shop_name'] ?? '';

        // 自定义数组...
        return Arr::only($array, [
            'goods_id',
            'user_id',
            'goods_sn',
            'goods_name',
            'keywords',
            'shop_price',
            'goods_thumb',
            'brand_id',
            'brand_name',
            'is_alone_sale', // 作为普通商品销售
            'is_on_sale', // 上架
            'is_delete', // 未删除
            'is_show', // 显示
            'sales_volume', // 销量
            'rz_shop_name',
        ]);
    }
}
