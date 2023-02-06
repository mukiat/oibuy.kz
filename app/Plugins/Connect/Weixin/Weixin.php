<?php

namespace App\Plugins\Connect\Weixin;

use App\Extensions\CustomCache;
use App\Services\User\UserOauthService;
use EasyWeChat\Factory;
use Illuminate\Support\Facades\Log;
use Overtrue\Socialite\AuthorizeFailedException;
use Overtrue\Socialite\InvalidStateException;

/**
 * Class Weixin
 * @package App\Plugins\Connect\Weixin
 */
class Weixin
{
    protected $app = [];
    protected $type = 'weixin';

    public function __construct()
    {
        $config = $this->getConfig();

        $this->app = Factory::officialAccount($config);
        $this->app->rebind('cache', new CustomCache());
    }

    /**
     * 获取授权地址
     * @param string $callback_url
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect($callback_url = null)
    {
        return $this->app->oauth->scopes(['snsapi_login'])->redirect($callback_url);
    }

    /**
     * @return array
     */
    public function callback()
    {
        try {
            $user = $this->app->oauth->user();

            $userinfo = $user->getOriginal();
        } catch (AuthorizeFailedException $authorizeFailedException) {
            Log::error($authorizeFailedException->getMessage());
        } catch (InvalidStateException $invalidStateException) {
            Log::error($invalidStateException->getMessage());
        }

        // unionid 必需
        if (empty($userinfo['unionid'])) {
            return [];
        }

        // 强制更新微信头像图片资源为https链接
        if (isset($userinfo['headimgurl'])) {
            $userinfo['headimgurl'] = str_replace('http://', 'https://', $userinfo['headimgurl']);
        }

        return $userinfo;
    }

    /**
     * 组装配置
     */
    protected function getConfig()
    {
        $config = app(UserOauthService::class)->oauthConfig($this->type);

        return [
            'app_id' => $config['app_id'] ?? '', // AppID
            'secret' => $config['app_secret'] ?? '', // AppSecret
            'oauth' => [
                'scopes' => 'snsapi_login',
                'callback' => route('oauth', ['type' => $this->type]),
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
