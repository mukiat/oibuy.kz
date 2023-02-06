<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GoodsKeyword
 */
class GoodsKeyword extends Model
{
    protected $table = 'goods_keyword';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'keyword_id',
        'goods_id',
        'add_time'
    ];

    protected $guarded = [];
}
