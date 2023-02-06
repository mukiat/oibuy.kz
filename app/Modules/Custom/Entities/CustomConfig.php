<?php

namespace App\Modules\Custom\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CustomConfig
 * @package App\Modules\Custom\Entities
 */
class CustomConfig extends Model
{
    protected $table = 'custom_config';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'value',
        'group',
        'create_time',
        'update_time'
    ];

    protected $guarded = [];
}
