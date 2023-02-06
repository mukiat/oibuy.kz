<?php

namespace App\Plugins\Connect\Qq;

use App\Services\User\UserOauthService;
use Illuminate\Support\Facades\Log;
use Overtrue\Socialite\AuthorizeFailedException;
use Overtrue\Socialite\InvalidStateException;
use Overtrue\Socialite\SocialiteManager;

/**
 * Class Qq
 * @package App\Plugins\Connect\Qq
 */
class Qq
{
    protected $app = [];
    protected $type = 'qq';

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
            $user = $this->app->driver($this->type)->withUnionId()->user();

            $info = $user->getOriginal();

            $userinfo = [
                'openid' => $user->getId(),
                'unionid' => $user->getAttributes()['unionid'],
                'nickname' => $user->getNickname(),
                'headimgurl' => $user->getAvatar(),
                'city' => $info['city'] ?? '',
                'province' => $info['province'] ?? ''
            ];

            $gender = $info['gender'] ?? 0;
            if ($gender == '男') {
                $sex = 1;
            } elseif ($gender == '女') {
                $sex = 2;
            } else {
                $sex = 0;
            }

            $userinfo['sex'] = $sex;
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
            'qq' => [
                'client_id' => $config['app_id'] ?? '',
                'client_secret' => $config['app_key'] ?? '',
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
