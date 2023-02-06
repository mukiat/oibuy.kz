<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UsersErrorLog
 */
class UsersErrorLog extends Model
{
    protected $table = 'users_error_log';

    protected $primaryKey = 'log_id';

    public $timestamps = false;

    protected $fillable = [
        'user_name',
        'user_id',
        'admin_id',
        'store_user_id',
        'create_time',
        'ip_address',
        'operation_note',
        'user_agent',
        'expired'
    ];

    protected $guarded = [];
}
