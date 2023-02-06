<?php

namespace App\Models;

use App\Entities\GoodsLibCat as Base;

/**
 * Class GoodsLibCat
 */
class GoodsLibCat extends Base
{
    /**
     * 获取当前分类子分类树
     *
     * @param int $parent_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getList($parent_id = 0)
    {
        $model = GoodsLibCat::with([
            'catList' => function ($query) {
                $query = $query->where('is_show', 1);
                $query->orderBy('sort_order')->orderBy('cat_id');
            }
        ])
            ->where('parent_id', $parent_id);

        return $model;
    }

    /**
     * 关联当前分类
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function catList()
    {
        return $this->hasMany('App\Models\GoodsLibCat', 'parent_id')->where('is_show', 1)->with('catList');
    }
}
