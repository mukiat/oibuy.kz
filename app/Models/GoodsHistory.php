<?php

namespace App\Models;

use App\Entities\GoodsHistory as Base;

/**
 * Class GoodsHistory
 */
class GoodsHistory extends Base
{
    public function getGoods()
    {
        return $this->hasOne('App\Models\Goods', 'goods_id', 'goods_id');
    }
}
