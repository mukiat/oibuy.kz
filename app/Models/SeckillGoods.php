<?php

namespace App\Models;

use App\Entities\SeckillGoods as Base;

/**
 * Class SeckillGoods
 * @package App\Models
 */
class SeckillGoods extends Base
{
    /**
     * 关联秒杀时间
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getSeckillTimeBucket()
    {
        return $this->hasOne('App\Models\SeckillTimeBucket', 'id', 'tb_id');
    }

    /**
     * 关联秒杀商品
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getGoods()
    {
        return $this->hasOne('App\Models\Goods', 'goods_id', 'goods_id');
    }

    /**
     * 关联秒杀主题
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getSeckill()
    {
        return $this->hasOne('App\Models\Seckill', 'sec_id', 'sec_id');
    }

    /**
     * 关联秒杀商品属性
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getSeckillGoodsProduct()
    {
        return $this->hasOne('App\Models\SeckillGoodsAttr', 'seckill_goods_id', 'id');
    }

    /**
     * 关联秒杀商品属性
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getSeckillGoodsAttr()
    {
        return $this->hasMany('App\Models\SeckillGoodsAttr', 'seckill_goods_id', 'id');
    }
}
