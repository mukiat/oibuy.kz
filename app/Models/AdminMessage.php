<?php

namespace App\Models;

use App\Entities\AdminMessage as Base;

/**
 * Class AdminMessage
 */
class AdminMessage extends Base
{
    /**
     * 发送者
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getAdminUser()
    {
        return $this->hasOne('App\Models\AdminUser', 'user_id', 'sender_id');
    }

    /**
     * 接收者
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getAdminUserReceiver()
    {
        return $this->hasOne('App\Models\AdminUser', 'user_id', 'receiver_id');
    }
}
