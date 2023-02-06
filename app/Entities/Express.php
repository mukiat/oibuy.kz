<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Sms
 */
class Express extends Model
{
    protected $table = 'express';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'code',
        'description',
        'express_configure',
        'enable',
        'default',
        'add_time',
        'update_time',
        'sort'
    ];

    protected $guarded = [];
}
