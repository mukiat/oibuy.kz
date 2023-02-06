<?php

namespace App\Models;

use App\Entities\OrderInfoBankTransfer as Base;

/**
 * Class OrderInfoBankTransfer
 */
class OrderInfoBankTransfer extends Base
{

    /**
     * 关联订单
     *
     * @param order_id
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function orderInfo()
    {
        return $this->hasOne('App\Models\OrderInfo', 'order_id', 'order_id');
    }
}
