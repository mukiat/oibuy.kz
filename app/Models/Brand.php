<?php

namespace App\Models;

use App\Entities\Brand as Base;

/**
 * Class Brand
 */
class Brand extends Base
{
    public function getBrandExtend()
    {
        return $this->hasOne('App\Models\BrandExtend', 'brand_id', 'brand_id');
    }

    public function getGoods()
    {
        return $this->hasOne('App\Models\Goods', 'brand_id', 'brand_id');
    }

    public function getGoodsList()
    {
        return $this->hasMany('App\Models\Goods', 'brand_id', 'brand_id');
    }

    public function getCollectBrand()
    {
        return $this->hasOne('App\Models\CollectBrand', 'brand_id', 'brand_id');
    }

    public function getFloorContent()
    {
        return $this->hasOne('App\Models\FloorContent', 'brand_id', 'brand_id');
    }
}
