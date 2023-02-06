<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Lottery
 * @package App\Entities
 */
class Lottery extends Model
{
    /**
     * @var string
     */
    protected $table = 'lotteries';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'ru_id',
        'active_state',
        'start_time',
        'end_time',
        'active_desc',
        'participant',
        'single_amount',
        'participate_number',
    ];
}
