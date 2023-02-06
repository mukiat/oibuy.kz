<?php

namespace App\Models;

use App\Entities\Complaint as Base;

/**
 * Class Complaint
 */
class Complaint extends Base
{
    /**
     * 关联投诉订单
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getOrder()
    {
        return $this->hasOne('App\Models\OrderInfo', 'order_id', 'order_id');
    }
}
