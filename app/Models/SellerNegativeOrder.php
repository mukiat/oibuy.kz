<?php

namespace App\Models;

use App\Entities\SellerNegativeOrder as Base;

/**
 * Class SellerNegativeOrder
 */
class SellerNegativeOrder extends Base
{
    public function getSellerNegativeBill()
    {
        return $this->hasOne('App\Models\SellerNegativeBill', 'id', 'negative_id');
    }

    public function getSellerBillOrder()
    {
        return $this->hasOne('App\Models\SellerBillOrder', 'order_id', 'order_id');
    }

    public function getOrderReturn()
    {
        return $this->hasOne('App\Models\OrderReturn', 'ret_id', 'ret_id');
    }

    public function orderInfo()
    {
        return $this->hasOne('App\Models\OrderInfo', 'order_id', 'order_id');
    }
}
