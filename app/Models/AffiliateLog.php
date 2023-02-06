<?php

namespace App\Models;

use App\Entities\AffiliateLog as Base;

/**
 * Class AffiliateLog
 */
class AffiliateLog extends Base
{
    /**
     * 关联会员
     * @param user_id
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getUsers()
    {
        return $this->hasOne('App\Models\Users', 'user_id', 'user_id');
    }

    /**
     * 关联订单
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getOrder()
    {
        return $this->hasOne('App\Models\OrderInfo', 'user_id', 'user_id');
    }
}
