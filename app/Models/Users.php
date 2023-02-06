<?php

namespace App\Models;

use App\Entities\Users as Base;
use Illuminate\Notifications\Notifiable;

/**
 * Class Users
 * @package App\Models
 */
class Users extends Base
{
    use Notifiable;

    /**
     * 获取用户头像
     * @return mixed
     */
    public function getUserPictureAttribute()
    {
        $this->attributes['user_picture'] = empty($this->attributes['user_picture']) ? '' : $this->attributes['user_picture'];
        if (request()->isSecure() && !empty($this->attributes['user_picture'])) {
            $this->attributes['user_picture'] = str_replace('http://', 'https://', $this->attributes['user_picture']);
        }
        return $this->attributes['user_picture'];
    }

    /**
     * 关联会员父级
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getUserParent()
    {
        return $this->hasOne('App\Models\Users', 'user_id', 'parent_id');
    }

    /**
     * 关联会员红包
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getUserBonus()
    {
        return $this->hasOne('App\Models\UserBonus', 'user_id', 'user_id');
    }

    /**
     * 关联会员红包
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getUserBonusList()
    {
        return $this->hasMany('App\Models\UserBonus', 'user_id', 'user_id');
    }

    /**
     * 关联会员优惠券
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getCouponsUserList()
    {
        return $this->hasMany('App\Models\CouponsUser', 'user_id', 'user_id');
    }

    /**
     * 关联会员支付密码
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getUsersPaypwd()
    {
        return $this->hasOne('App\Models\UsersPaypwd', 'user_id', 'user_id');
    }

    /**
     * 关联会员身份认证
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getUsersReal()
    {
        return $this->hasOne('App\Models\UsersReal', 'user_id', 'user_id');
    }

    /**
     * 关联社会化登录用户
     * @param user_id
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getConnectUser()
    {
        return $this->hasOne('App\Models\ConnectUser', 'user_id', 'user_id');
    }

    /**关联储值卡
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getValueCard()
    {
        return $this->hasMany('App\Models\ValueCard', 'user_id', 'user_id');
    }

    /**
     * 关联回复商品
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getDiscussCircle()
    {
        return $this->hasOne('App\Models\DiscussCircle', 'user_id', 'user_id');
    }

    /**
     * 关联订单
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getOrder()
    {
        return $this->hasOne('App\Models\OrderInfo', 'user_id', 'user_id');
    }

    /**
     * 关联订单列表
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getOrderList()
    {
        return $this->hasMany('App\Models\OrderInfo', 'user_id', 'user_id');
    }

    /**
     * 关联商家信息
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getMerchantsShopInformation()
    {
        return $this->hasOne('App\Models\MerchantsShopInformation', 'user_id', 'user_id');
    }
}
