<?php

namespace App\Services\Category;

use App\Models\Category;
use App\Models\Goods;
use App\Repositories\Common\BaseRepository;

class CategoryDataHandleService
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

        $res = Category::select($data)->whereIn('cat_id', $id);

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['cat_id']] = $row;
            }
        }

        return $arr;
    }

    /**
     * 商品子分类列表
     *
     * @param array $id
     * @param array $data
     * @return array
     */
    public static function getCategoryParentDataList($id = [], $data = [])
    {
        $id = BaseRepository::getExplode($id);

        if (empty($id)) {
            return [];
        }

        $id = $id ? array_unique($id) : [];

        $data = $data ? $data : '*';

        $res = Category::select($data)->whereIn('parent_id', $id)->where('is_show', 1)
            ->with([
                'getGoods' => function ($query) {
                    $query->select('goods_id', 'goods_thumb', 'cat_id')
                        ->where('is_alone_sale', 1)
                        ->where('is_delete', 0)
                        ->where('is_show', 1)
                        ->groupBy('cat_id');
                }
            ]);
        $res = $res->orderBy('sort_order');
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['parent_id']][] = $row;
            }
        }

        return $arr;
    }
}
