<?php

namespace App\Models;

use App\Entities\OrderDelayed as Base;

/**
 * Class OrderDelayed
 */
class OrderDelayed extends Base
{
    /**
     * 关联订单
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getOrder()
    {
        return $this->hasOne('App\Models\OrderInfo', 'order_id', 'order_id');
    }
}
