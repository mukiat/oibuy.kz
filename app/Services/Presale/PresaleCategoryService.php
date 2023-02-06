<?php

namespace App\Services\Presale;

use App\Models\PresaleCat;
use App\Repositories\Common\DscRepository;

class PresaleCategoryService
{
    private $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 多维数组转一维数组【分类】
     *
     * @param int $parent_id
     * @return array|bool|\Illuminate\Cache\CacheManager|mixed
     * @throws \Exception
     */
    public function getPresaleCatListChildren($parent_id = 0)
    {

        //顶级分类页分类显示
        $cache_name = 'get_presale_cat_children' . $parent_id;

        $cat_list = cache($cache_name);
        $cat_list = !is_null($cat_list) ? $cat_list : false;

        //将数据写入缓存文件
        if ($cat_list === false) {
            $cat_list = PresaleCat::getList($parent_id)
                ->orderByRaw('sort_order, cat_id desc')
                ->get();

            $cat_list = $cat_list ? $cat_list->toArray() : [];

            if ($cat_list) {
                $cat_list = $this->dscRepository->getCatVal($cat_list);

                $cat_list = collect($cat_list)->flatten()->all();

                $cat_list = !empty($parent_id) ? collect($cat_list)->prepend($parent_id)->all() : $cat_list;
            } else {
                $cat_list = [$parent_id];
            }

            cache()->forever($cache_name, $cat_list);
        }

        return $cat_list;
    }
}