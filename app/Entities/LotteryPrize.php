<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class LotteryPrize
 * @package App\Entities
 */
class LotteryPrize extends Model
{
    /**
     * @var string
     */
    protected $table = 'lottery_prizes';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string[]
     */
    protected $fillable = [
        'ru_id',
        'lottery_id',
        'prize_name',
        'prize_image',
        'prize_type',
        'prize_number',
        'prize_issued',
        'prize_prob',
    ];
}
