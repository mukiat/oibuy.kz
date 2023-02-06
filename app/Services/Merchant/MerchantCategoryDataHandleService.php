<?php

namespace App\Services\Merchant;

use App\Models\MerchantsCategory;
use App\Repositories\Common\BaseRepository;

class MerchantCategoryDataHandleService
{
    /**
     * 商品分类列表
     *
     * @param array $id
     * @param array $data
     * @return array
     */
    public static function getCategoryDataList($id = [], $data = [])
    {
        $id = BaseRepository::getExplode($id);

        if (empty($id)) {
            return [];
        }

        $id = $id ? array_unique($id) : [];

        $data = $data ? $data : '*';

        $res = MerchantsCategory::select($data)->whereIn('cat_id', $id);

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['cat_id']] = $row;
            }
        }

        return $arr;
    }
}