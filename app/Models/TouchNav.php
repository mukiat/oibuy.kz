<?php

namespace App\Models;

use App\Entities\TouchNav as Base;

/**
 * Class TouchNav
 */
class TouchNav extends Base
{
    /**
     * 关联父级工具栏
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function parentNav()
    {
        return $this->hasOne('App\Models\TouchNav', 'id', 'parent_id');
    }

    /**
     * 关联子级工具栏
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function childNav()
    {
        return $this->hasMany('App\Models\TouchNav', 'parent_id', 'id');
    }
}
