<?php

namespace App\Modules\Custom\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class LogoutUser
 * @package App\Modules\Custom\Entities
 */
class LogoutUser extends Model
{
    protected $table = 'logout_user';

    public $timestamps = false;

    protected $fillable = [
        'user_name',
        'nick_name',
        'mobile',
        'logout_reason',
        'create_time',
        'update_time',
        'delete_time'
    ];

    protected $guarded = [];
}
