<?php

namespace App\Models;

use App\Entities\SellerNegativeBill as Base;

/**
 * Class SellerNegativeBill
 */
class SellerNegativeBill extends Base
{
    /**
     * 关联负账单订单列表
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getSellerNegativeOrder()
    {
        return $this->hasMany('App\Models\SellerNegativeOrder', 'negative_id', 'id');
    }
}
