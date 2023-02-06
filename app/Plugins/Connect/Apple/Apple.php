<?php

namespace App\Plugins\Connect\Apple;

/**
 * Class Apple
 * @package App\Plugins\Connect\Weibo
 */
class Apple
{
    protected $type = 'apple';

    /**
     * 获取授权地址
     * @param null $callback_url
     * @return null
     */
    public function redirect($callback_url = null)
    {
        return null;
    }

    /**
     * 回调用户数据
     * @return array
     */
    public function callback()
    {
        /**
         * APP 苹果登录 授权类型如：weixin/weibo/qq/apple
         * @link  https://uniapp.dcloud.io/api/plugins/provider
         */

        $userData = request()->post('userInfo');

        $userInfo = [
            'openid' => $userData['openId'],
            'unionid' => $userData['openId'],
            'nickname' => implode('', $userData['fullName']) ?? '',
            'sex' => 0, // 未知
            'headimgurl' => '',
            'country' => '',
            'province' => '',
            'city' => '',
        ];

        if (empty($userInfo['unionid'])) {
            return [];
        }

        return $userInfo;
    }
}
