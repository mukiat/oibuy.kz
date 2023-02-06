<?php

namespace App\Models;

use App\Entities\VirtualCard as Base;

/**
 * Class VirtualCard
 */
class VirtualCard extends Base
{
    /**
     * 关联订单
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getOrder()
    {
        return $this->hasOne('App\Models\OrderInfo', 'order_sn', 'order_sn');
    }

    /**
     * 关联商品
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getGoods()
    {
        return $this->hasOne('App\Models\Goods', 'goods_id', 'goods_id');
    }
}
