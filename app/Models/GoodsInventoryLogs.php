<?php

namespace App\Models;

use App\Entities\GoodsInventoryLogs as Base;

/**
 * Class GoodsInventoryLogs
 */
class GoodsInventoryLogs extends Base
{
    public function getGoods()
    {
        return $this->hasOne('App\Models\Goods', 'goods_id', 'goods_id');
    }

    public function getOrderInfo()
    {
        return $this->hasOne('App\Models\OrderInfo', 'order_id', 'order_id');
    }

    public function getAdminUser()
    {
        return $this->hasOne('App\Models\AdminUser', 'user_id', 'admin_id');
    }

    public function getBrand()
    {
        return $this->hasOne('App\Models\Brand', 'brand_id', 'brand_id');
    }

    public function getProductsWarehouse()
    {
        return $this->hasOne('App\Models\ProductsWarehouse', 'product_id', 'product_id');
    }

    public function getProductsArea()
    {
        return $this->hasOne('App\Models\ProductsArea', 'product_id', 'product_id');
    }

    public function getProducts()
    {
        return $this->hasOne('App\Models\Products', 'product_id', 'product_id');
    }
}
