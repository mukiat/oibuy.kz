<?php

namespace App\Models;

use App\Entities\GoodsKeyword as Base;

class GoodsKeyword extends Base
{
    public function getKeywordList()
    {
        return $this->hasOne('App\Models\KeywordList', 'id', 'keyword_id');
    }
}