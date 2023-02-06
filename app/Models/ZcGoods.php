<?php

namespace App\Models;

use App\Entities\ZcGoods as Base;

/**
 * Class ZcGoods
 * @package App\Models
 */
class ZcGoods extends Base
{
    /**
     * 关联众筹项目
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getZcProject()
    {
        return $this->hasOne('App\Models\ZcProject', 'id', 'pid');
    }

    /**
     * 关联众筹商品订单
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getOrder()
    {
        return $this->hasOne('App\Models\OrderInfo', 'zc_goods_id', 'id');
    }

    /**
     * 关联众筹商品订单列表
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getOrderList()
    {
        return $this->hasMany('App\Models\OrderInfo', 'zc_goods_id', 'id');
    }
}
