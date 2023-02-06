<?php

namespace App\Services\Brand;

use App\Models\Brand;
use App\Models\CollectBrand;
use App\Repositories\Common\BaseRepository;

class BrandDataHandleService
{
    /**
     * 商品品牌
     *
     * @param array $brand_id
     * @param array $data
     * @return array
     */
    public static function goodsBrand($brand_id = [], $data = [])
    {
        $brand_id = BaseRepository::getExplode($brand_id);

        if (empty($brand_id)) {
            return [];
        }

        $brand_id = array_unique($brand_id);

        $data = $data ? $data : '*';

        $res = Brand::select($data)->whereIn('brand_id', $brand_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];

        if ($res) {
            foreach ($res as $val) {
                $arr[$val['brand_id']] = $val;
            }
        }

        return $arr;
    }

    /**
     * 用户关注品牌
     *
     * @param array $brand_id
     * @param array $data
     * @return array
     */
    public static function getCollectBrandDataList($brand_id = [], $data = [])
    {
        $brand_id = BaseRepository::getExplode($brand_id);

        if (empty($brand_id)) {
            return [];
        }

        $brand_id = array_unique($brand_id);

        $data = $data ? $data : '*';

        $res = CollectBrand::select($data)->whereIn('brand_id', $brand_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];

        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['rec_id']] = $val;
            }
        }

        return $arr;
    }
}
