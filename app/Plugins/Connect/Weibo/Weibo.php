<?php

namespace App\Plugins\Connect\Weibo;

use App\Services\User\UserOauthService;
use Illuminate\Support\Facades\Log;
use Overtrue\Socialite\AuthorizeFailedException;
use Overtrue\Socialite\InvalidStateException;
use Overtrue\Socialite\SocialiteManager;

/**
 * Class Weibo
 * @package App\Plugins\Connect\Weibo
 */
class Weibo
{
    protected $app = [];
    protected $type = 'weibo';

    public function __construct()
    {
        $config = $this->getConfig();

        $this->app = new SocialiteManager($config);
    }

    /**
     * 获取授权地址
     * @param string $callback_url
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect($callback_url = null)
    {
        return $this->app->driver($this->type)->redirect($callback_url);
    }

    /**
     * 回调用户数据
     * @return array
     */
    public function callback()
    {
        try {
            $user = $this->app->driver($this->type)->user();

            $gender = $user->getOriginal()['gender'];
            if ($gender == 'f') {
                $sex = 1;
            } elseif ($gender == 'm') {
                $sex = 2;
            } else {
                $sex = 0;
            }

            $userinfo = [
                'openid' => $user->getId(),
                'unionid' => $user->getId(),
                'nickname' => $user->getNickname(),
                'sex' => $sex,
                'headimgurl' => $user->getAvatar(),
                'city' => $user->getOriginal()['city'] ?? '',
                'province' => $user->getOriginal()['province'] ?? ''
            ];
        } catch (AuthorizeFailedException $authorizeFailedException) {
            Log::error($authorizeFailedException->getMessage());
        } catch (InvalidStateException $invalidStateException) {
            Log::error($invalidStateException->getMessage());
        }

        // unionid 必需
        if (empty($userinfo['unionid'])) {
            return [];
        }

        return $userinfo;
    }

    /**
     * 组装配置
     */
    protected function getConfig()
    {
        if (is_mobile_device()) {
            // 回调地址
            $callback = dsc_url('/oauth/callback?type=' . $this->type);
        } else {
            // 回调地址
            $callback = route('oauth', ['type' => $this->type]);
        }

        $config = app(UserOauthService::class)->oauthConfig($this->type);

        return [
            'weibo' => [
                'client_id' => $config['app_key'] ?? '',
                'client_secret' => $config['app_secret'] ?? '',
                'redirect' => $callback,
            ],
        ];
    }

    /**
     * 判断是否安装
     * @param $type
     * @return int
     */
    public function status($type)
    {
        return app(UserOauthService::class)->oauthStatus($type);
    }
}
