<?php

namespace App\Plugins\Connect\Wechat;

use App\Extensions\CustomCache;
use App\Services\User\UserOauthService;
use EasyWeChat\Factory;
use Illuminate\Support\Facades\Log;
use Overtrue\Socialite\AuthorizeFailedException;
use Overtrue\Socialite\InvalidStateException;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class Wechat
 * @package App\Plugins\Connect\Wechat
 */
class Wechat
{
    protected $app = [];
    protected $type = 'wechat';

    public function __construct()
    {
        $config = $this->getConfig();

        $this->app = Factory::officialAccount($config);
        $this->app->rebind('cache', new CustomCache());
    }

    /**
     * 获取授权地址
     * @param string $callback_url
     * @return RedirectResponse
     */
    public function redirect($callback_url = null)
    {
        return $this->app->oauth->scopes(['snsapi_userinfo'])->redirect($callback_url);
    }

    /**
     * 回调用户数据
     * @return array
     */
    public function callback()
    {
        /**
         * APP 微信登录 授权类型如：weixin/weibo/qq/apple
         * @link  https://uniapp.dcloud.io/api/plugins/provider
         */
        $type = request()->get('type', '');
        $platform = request()->get('platform'); // platform平台类型如下：H5/APP/MP-WEIXIN等
        if ($platform === 'APP' && $type === 'wechat') {
            $userInfo = request()->get('userInfo', []);
            // unionid 必需
            if (!empty($userInfo['unionId'])) {
                $userInfo['unionid'] = $userInfo['unionId'];
                $userInfo['nickname'] = $userInfo['nickName'];
                $userInfo['headimgurl'] = $userInfo['avatarUrl'] ?? '';
                $userInfo['sex'] = $userInfo['gender'] ?? 0;

                // 强制更新微信头像图片资源为https链接
                if (!empty($userInfo['headimgurl'])) {
                    $userInfo['headimgurl'] = str_replace('http://', 'https://', $userInfo['headimgurl']);
                }
            }

            return $userInfo;
        }

        try {
            $code = request()->input('code');
            $userInfo = [];
            if ($code) {
                $accessToken = $this->app->oauth->getAccessToken($code);
                $user = $this->app->oauth->user($accessToken);

                $userInfo = $user->getOriginal();
            }
        } catch (AuthorizeFailedException $authorizeFailedException) {
            Log::error($authorizeFailedException->getMessage());
            return [];
        } catch (InvalidStateException $invalidStateException) {
            Log::error($invalidStateException->getMessage());
            return [];
        }

        // unionid 必需
        if (empty($userInfo['unionid'])) {
            Log::error('Open platforms have no bindings');
            return [];
        }

        // 强制更新微信头像图片资源为https链接
        if (isset($userInfo['headimgurl'])) {
            $userInfo['headimgurl'] = str_replace('http://', 'https://', $userInfo['headimgurl']);
        }

        return $userInfo;
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
                'scopes' => 'snsapi_userinfo',
                'callback' => dsc_url('/oauth/callback?type=wechat'),
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
