<?php

namespace App\Models;

use App\Entities\StoreOrder as Base;

/**
 * Class StoreOrder
 */
class StoreOrder extends Base
{
    /**
     * 关联订单
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function orderInfo()
    {
        return $this->hasOne('App\Models\OrderInfo', 'order_id', 'order_id');
    }
}
