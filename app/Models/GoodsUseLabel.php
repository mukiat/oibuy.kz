<?php

namespace App\Models;

use App\Entities\GoodsUseLabel as Base;

/**
 * Class GoodsUseLabel
 */
class GoodsUseLabel extends Base
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
	public function getGoodsLabel()
    {
        return $this->hasOne('App\Models\GoodsLabel', 'id', 'label_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
	public function getGoods()
    {
        return $this->hasOne('App\Models\Goods', 'goods_id', 'goods_id');
    }
    
}
