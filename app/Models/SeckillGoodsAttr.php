<?php

namespace App\Models;

use App\Entities\SeckillGoodsAttr as Base;

/**
 * Class SeckillGoodsAttr
 */
class SeckillGoodsAttr extends Base
{
    /**
     * 关联商品货品
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getProducts()
    {
        return $this->hasOne('App\Models\Products', 'product_id', 'product_id');
    }

    /**
     * 关联仓库商品货品
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getProductsWarehouse()
    {
        return $this->hasOne('App\Models\ProductsWarehouse', 'product_id', 'product_id');
    }

    /**
     * 关联地区商品货品
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getProductsArea()
    {
        return $this->hasOne('App\Models\ProductsArea', 'product_id', 'product_id');
    }
}
