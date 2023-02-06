<?php

namespace App\Models;

use App\Entities\SellerCommissionBill as Base;

/**
 * Class SellerCommissionBill
 */
class SellerCommissionBill extends Base
{
    /**
     * 关联佣金比例
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getMerchantsServer()
    {
        return $this->hasOne('App\Models\MerchantsServer', 'user_id', 'seller_id');
    }

    /**
     * 关联负账单
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getSellerNegativeBillList()
    {
        return $this->hasMany('App\Models\SellerNegativeBill', 'commission_bill_id', 'id');
    }
}
