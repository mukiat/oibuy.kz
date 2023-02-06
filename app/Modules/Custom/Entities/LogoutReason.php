<?php

namespace App\Modules\Custom\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class LogoutReason
 * @package App\Modules\Custom\Entities
 */
class LogoutReason extends Model
{
    protected $table = 'logout_reason';

    public $timestamps = false;

    protected $fillable = [
        'reason_name',
        'create_time',
        'update_time',
        'delete_time'
    ];

    protected $guarded = [];
}
