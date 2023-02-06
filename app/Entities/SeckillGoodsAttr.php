<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SeckillGoodsAttr
 */
class SeckillGoodsAttr extends Model
{
    protected $table = 'seckill_goods_attr';

    public $timestamps = false;

    protected $fillable = [
        'seckill_goods_id',
        'goods_id',
        'product_id',
        'sec_price',
        'sec_num',
        'sec_limit'
    ];

    protected $guarded = [];

}
