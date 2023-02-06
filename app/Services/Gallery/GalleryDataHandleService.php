<?php

namespace App\Services\Gallery;

use App\Models\GoodsGallery;
use App\Repositories\Common\BaseRepository;

class GalleryDataHandleService
{
    /**
     * 查询商品相册列表信息
     *
     * @param array $goods_id
     * @param array $data
     * @return array
     */
    public static function getGoodsGalleryDataList($goods_id = [], $data = [])
    {
        $goods_id = BaseRepository::getExplode($goods_id);

        if (empty($goods_id)) {
            return [];
        }

        $goods_id = $goods_id ? array_unique($goods_id) : [];

        $data = $data ? $data : '*';

        $res = GoodsGallery::select($data)->whereIn('goods_id', $goods_id)
            ->orderBy('img_desc');
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['goods_id']][] = $row;
            }
        }

        return $arr;
    }
}