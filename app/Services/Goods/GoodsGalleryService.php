<?php

namespace App\Services\Goods;

use App\Models\Goods;
use App\Models\GoodsGallery;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;

class GoodsGalleryService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 获得指定商品的相册
     *
     * @param int $goods_id
     * @param array $galleryList
     * @param string $goods_thumb
     * @param int $gallery_number
     * @return array|mixed
     */
    public function getGoodsGallery($goods_id = 0, $galleryList = [], $goods_thumb = '', $gallery_number = 0)
    {
        if (!$gallery_number) {
            $gallery_number = config('shop.goods_gallery_number');
        }


        if (!empty($galleryList)) {
            $row = $galleryList[$goods_id] ?? [];
            $row = $row ? BaseRepository::getTake($row, $gallery_number) : [];
        } else {
            $row = GoodsGallery::where('goods_id', $goods_id)->orderBy('img_desc')->take($gallery_number);
            $row = BaseRepository::getToArrayGet($row);
        }

        /* 格式化相册图片路径 */
        if ($row) {
            foreach ($row as $key => $gallery_img) {
                if (!empty($gallery_img['external_url'])) {
                    $row[$key]['img_url'] = $gallery_img['external_url'];
                    $row[$key]['thumb_url'] = $gallery_img['external_url'];
                } else {
                    $row[$key]['img_url'] = $this->dscRepository->getImagePath($gallery_img['img_url']);
                    $row[$key]['thumb_url'] = $this->dscRepository->getImagePath($gallery_img['thumb_url']);
                }
            }
        } else {
            /* 商品无相册图调用商品图 */
            if (empty($galleryList) && empty($goods_thumb)) {
                $goods_thumb = Goods::where('goods_id', $goods_id)->value('goods_thumb');
            }

            $row = [
                [
                    'img_url' => $this->dscRepository->getImagePath($goods_thumb),
                    'thumb_url' => $this->dscRepository->getImagePath($goods_thumb)
                ]
            ];
        }

        return $row;
    }

    /**
     * 获取相册图库列表
     *
     * @param array $where
     * @return mixed
     */
    public function getGalleryList($where = [])
    {
        $img_list = GoodsGallery::whereRaw(1);

        if (isset($where['img_id']) && $where['img_id']) {
            $img_id = BaseRepository::getExplode($where['img_id']);
            if (count($img_id) > 1) {
                $img_list = $img_list->whereIn('img_id', $img_id);
            } else {
                $img_list = $img_list->where('img_id', $img_id);
            }
        }

        if (isset($where['goods_id'])) {
            $img_list = $img_list->where('goods_id', $where['goods_id']);
        }

        if (isset($where['single_id'])) {
            $img_list = $img_list->where('single_id', $where['single_id']);
        }

        $img_list = $img_list->orderBy('img_desc');

        $img_list = BaseRepository::getToArrayGet($img_list);

        if ($img_list) {
            foreach ($img_list as $key => $val) {
                if (!empty($val['external_url'])) {
                    $val['img_url'] = $val['img_original'] = $val['thumb_url'] = $val['external_url'];
                }
                $img_list[$key]['thumb_url'] = $this->dscRepository->getImagePath($val['thumb_url']);
                $img_list[$key]['img_original'] = $this->dscRepository->getImagePath($val['img_original']);
                $img_list[$key]['img_url'] = $this->dscRepository->getImagePath($val['img_url']);
            }
        }

        return $img_list;
    }
}
