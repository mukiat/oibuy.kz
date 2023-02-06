<?php

namespace App\Repositories\User;

use App\Models\ConnectUser;
use App\Repositories\Common\CommonRepository;

/**
 * Class ConnectUserRepository
 * @package App\Repositories\User
 */
class ConnectUserRepository
{

    /**
     * 获取用户openid
     * @param int $user_id
     * @param string $type
     * @return mixed|string
     */
    public static function get_openid($user_id = 0, $type = 'wechat')
    {
        if (empty($user_id)) {
            return '';
        }

        $profile = ConnectUser::where('user_id', $user_id)->where('open_id', '<>', '')->where('connect_code', 'sns_' . $type)->orderBy('id', 'DESC')->value('profile');
        $profile = !empty($profile) ? unserialize($profile) : [];
        return $profile['openid'] ?? '';
    }

    /**
     * 重置 profile 授权信息
     * @return mixed
     */
    public static function reset_wechat_profile()
    {
        $res = ConnectUser::where('open_id', '<>', '')
            ->where('connect_code', 'sns_wechat');
        $res = CommonRepository::constantMaxId($res, 'user_id');
        $res = $res->update(['profile' => '']);

        return $res;
    }

    /**
     * 获取 profile 授权信息
     * @param int $user_id
     * @return mixed
     */
    public static function get_wechat_profile($user_id = 0)
    {
        $connect_user = ConnectUser::where('user_id', $user_id)->where('connect_code', 'sns_wechat')->where('open_id', '<>', '')->select('user_id', 'profile')->first();
        if (!empty($connect_user)) {
            // 更换新的公众号（同一开放平台下） 已绑定微信授权会员 需要微信重新授权 更新微信会员信息
            $connect_user_profile = $connect_user->profile ?? '';
            if (empty($connect_user_profile)) {
                // connect_user profile 为空 则返回授权信息已过期，请重新登录
                return false;
            }
        }

        return true;
    }
}