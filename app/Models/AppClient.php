<?php

namespace App\Models;

use App\Entities\AppClient as Base;

/**
 * Class AppAd
 * @package App\Models
 */
class AppClient extends Base
{
    /**
     * 关联产品列表
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function appAdPosition()
    {
        return $this->hasOne('App\Models\AppClientProduct', 'client_id', 'id');
    }
}
