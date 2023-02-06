<?php

namespace App\Models;

use App\Entities\GoodsUseServicesLabel as Base;

/**
 * Class GoodsUseLabel
 */
class GoodsUseServicesLabel extends Base
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
	public function getGoodsServicesLabel()
    {
        return $this->hasOne('App\Models\GoodsServicesLabel', 'id', 'label_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
	public function getGoods()
    {
        return $this->hasOne('App\Models\Goods', 'goods_id', 'goods_id');
    }

}
