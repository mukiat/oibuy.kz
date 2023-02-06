<?php

namespace App\Models;

use App\Entities\UserRank as Base;

class UserRank extends Base
{
    /**
     * 关联会员
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getUsers()
    {
        return $this->hasOne('App\Models\Users', 'user_rank', 'rank_id');
    }

    /**
     * 关联会员等级价
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getMemberPrice()
    {
        return $this->hasOne('App\Models\MemberPrice', 'user_rank', 'rank_id');
    }


    /**
     * 关联会员等级权益
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userRankRights()
    {
        return $this->hasMany('App\Models\UserRankRights', 'user_rank_id', 'rank_id');
    }
}
