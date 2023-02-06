<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TouchNav
 */
class TouchNav extends Model
{
    protected $table = 'touch_nav';

    public $timestamps = false;

    protected $fillable = [
        'ctype',
        'cid',
        'name',
        'ifshow',
        'vieworder',
        'opennew',
        'url',
        'type',
        'pic',
        'device',
        'parent_id',
        'page'
    ];

    protected $guarded = [];
}
