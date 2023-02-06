<?php

namespace App\Models;

use App\Entities\AppClientProduct as Base;

/**
 * Class AppAd
 * @package App\Models
 */
class AppClientProduct extends Base
{
    /**
     * 关联客户端
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function appClient()
    {
        return $this->hasOne('App\Models\AppClient', 'id', 'client_id');
    }
}
