<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class LotteryRecord
 * @package App\Entities
 */
class LotteryRecord extends Model
{
    /**
     * @var string
     */
    protected $table = 'lottery_records';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string[]
     */
    protected $fillable = [
        'lottery_id',
        'user_id',
        'user_name',
        'lottery_prize_id',
        'prize_name',
        'prize_type',
        'prize',
        'channel',
        'created_at',
    ];
}
