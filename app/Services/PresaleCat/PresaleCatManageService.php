<?php

namespace App\Services\PresaleCat;

use App\Models\PresaleCat;
use App\Repositories\Common\BaseRepository;
use App\Services\Merchant\MerchantCommonService;

class PresaleCatManageService
{
    protected $merchantCommonService;

    public function __construct(
        MerchantCommonService $merchantCommonService
    )
    {
        $this->merchantCommonService = $merchantCommonService;
    }

    /**
     * 检查分类是否已经存在
     *
     * @param string $cat_name 分类名称
     * @param int $parent_cat 上级分类
     * @param int $exclude 排除的分类ID
     * @return bool
     */
    public function presaleCatExists($cat_name = '', $parent_cat = 0, $exclude = 0)
    {
        $res = PresaleCat::where('parent_id', $parent_cat)
            ->where('cat_name', $cat_name)
            ->where('cat_id', '<>', $exclude)
            ->count();

        return ($res > 0) ? true : false;
    }

    /**
     * 添加商品分类
     *
     * @param $cat_id
     * @param $args
     * @return bool
     */
    public function catUpdate($cat_id, $args)
    {
        if (empty($args) || empty($cat_id)) {
            return false;
        }

        $res = PresaleCat::where('cat_id', $cat_id)->update($args);
        return $res;
    }

    /**
     * 检查分类是否已经存在
     *
     * @param string $cat_name 分类名称
     * @param integer $parent_cat 上级分类
     * @param integer $exclude 排除的分类ID
     *
     * @return  boolean
     */
    public function cnameExists($cat_name, $parent_cat, $exclude = 0)
    {
        $res = PresaleCat::where('parent_id', $parent_cat)
            ->where('cat_name', $cat_name)
            ->where('cat_id', '<>', $exclude)
            ->count();


        return ($res > 0) ? true : false;
    }

    /**
     * 预售商品下级分类
     *
     * @param $pid
     * @return mixed
     */
    public function presaleChildCat($pid)
    {
        $res = PresaleCat::where('parent_id', $pid);
        $row = BaseRepository::getToArrayGet($res);
        return $row;
    }

    /**
     * ajax分类列表
     *
     * @param int $parent_id
     * @param int $level
     * @return mixed
     * @throws \Exception
     */
    public function getCatLevel($parent_id = 0, $level = 0)
    {
        $res = PresaleCat::where('parent_id', $parent_id)
            ->orderBy('sort_order')
            ->orderBy('cat_id');

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $k => $row) {
                $res[$k]['level'] = $level;
            }
        }

        return $res;
    }

    /**
     * 预售分类等级列表形式
     *
     * @param int $parent_id
     * @param int $level
     * @return array
     */
    public function presaleCatSelect($parent_id = 0, $level = 0)
    {

        $list = PresaleCat::where('parent_id', $parent_id);
        $list = BaseRepository::getToArrayGet($list);

        $cat_select = [];
        if ($list) {
            foreach ($list as $key => $val) {
                $cat_select[$key]['cat_id'] = $val['cat_id'];
                $cat_select[$key]['parent_id'] = $val['parent_id'];
                $cat_select[$key]['name'] = str_repeat('&nbsp;', $level * 4) . $val['cat_name'];
                $cat_select[$key]['level'] = $level;
            }
        }

        return $cat_select;
    }
}
