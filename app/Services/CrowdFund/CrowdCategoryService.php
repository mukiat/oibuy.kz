<?php

namespace App\Services\CrowdFund;

use App\Models\ZcCategory;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;

/**
 * Class CrowdCategoryService
 * @package App\Services\CrowdFund
 */
class CrowdCategoryService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    ) {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 众筹商品分类列表
     *
     * @param int $parent_id
     * @param int $level
     * @return mixed
     */
    public static function getCatLevel($parent_id = 0, $level = 0)
    {
        $res = ZcCategory::where('parent_id', $parent_id)
            ->orderByRaw('sort_order, cat_id asc');

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $k => $row) {
                $res[$k]['level'] = $level;
            }
        }

        return $res;
    }

    /**
     * 多维数组转一维数组【分类】
     *
     * @param int $parent_id
     * @return array|bool|\Illuminate\Cache\CacheManager|mixed
     * @throws \Exception
     */
    public function getZcCatListChildren($parent_id = 0)
    {
        //顶级分类页分类显示
        $cat_list = cache('get_zccat_list_children' . $parent_id);
        $cat_list = !is_null($cat_list) ? $cat_list : false;

        //将数据写入缓存文件
        if ($cat_list === false) {
            $cat_list = ZcCategory::getList($parent_id)
                ->where('is_show', 1)
                ->orderBy('sort_order')
                ->orderBy('cat_id');

            $cat_list = BaseRepository::getToArrayGet($cat_list);

            if ($cat_list) {
                $cat_list = $this->dscRepository->getCatVal($cat_list);
                $cat_list = BaseRepository::getFlatten($cat_list);

                $cat_list = !empty($parent_id) ? collect($cat_list)->prepend($parent_id)->all() : $cat_list;
            } else {
                $cat_list = [$parent_id];
            }

            $cat_list = collect($cat_list)->values()->all();

            cache()->forever('get_zccat_list_children' . $parent_id, $cat_list);
        }

        return $cat_list;
    }
}
