<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class KeywordList
 */
class KeywordList extends Model
{
    protected $table = 'keyword_list';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'cat_id',
        'name',
        'update_time',
        'add_time'
    ];

    protected $guarded = [];
}
