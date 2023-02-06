<?php

namespace App\Models;

use App\Entities\Single as Base;

/**
 * Class Single
 */
class Single extends Base
{
    public function getGoods()
    {
        return $this->hasOne('App\Models\Goods', 'goods_id', 'goods_id');
    }
}
