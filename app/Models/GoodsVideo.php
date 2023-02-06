<?php

namespace App\Models;

use App\Entities\GoodsVideo as Base;

/**
 * Class GoodsVideo
 */
class GoodsVideo extends Base
{
    public function getGoods()
    {
        return $this->hasOne('App\Models\Goods', 'goods_id', 'goods_id');
    }
}
