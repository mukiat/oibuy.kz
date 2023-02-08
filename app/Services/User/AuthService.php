<?php

namespace App\Services\User;

use App\Exceptions\OauthException;
use App\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\StrRepository;
use App\Rules\PasswordRule;
use App\Rules\UserName;
use App\Services\Cart\CartCommonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Class AuthService
 * @package App\Services\User
 */
class AuthService
{
    protected $connectUserService;
    protected $userCommonService;
    protected $cartCommonService;
    protected $userLoginRegisterService;

    public function __construct(
        ConnectUserService $connectUserService,
        UserCommonService $userCommonService,
        CartCommonService $cartCommonService,
        UserLoginRegisterService $userLoginRegisterService
    )
    {
        $this->connectUserService = $connectUserService;
        $this->userCommonService = $userCommonService;
        $this->cartCommonService = $cartCommonService;
        $this->userLoginRegisterService = $userLoginRegisterService;
    }

    /**
     * 登录|注册
     *
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function loginOrRegister(Request $request)
    {
        $mobile = $request->get('mobile');
        $user = $this->userCommonService->getUserByName($mobile);

        // 统一普通注册和手机号快捷注册
        if ($request->has('pwd')) {
            $password = $request->get('pwd');
        } else {
            $password = $request->get('code', StrRepository::random(6) . mt_rand(10, 99));
        }

        $connect_type = $request->get('type', '');
        $platform = $request->get('platform', '');
        $unionid = $request->get('unionid', '');

        $request->offsetSet('username', StrRepository::generate_username($connect_type, $unionid));
        $request->offsetSet('password', $password);
        $request->offsetSet('email', '');
        $request->offsetSet('mobile_phone', $mobile);

        // $connect_type 参数为空的情况下为H5模式，否则为授权登录
        if (!empty($connect_type)) {
            // 定义服务类型映射
            $provider = [
                'weixin' => 'wechat',
            ];

            // 覆写类型
            if (isset($provider[$connect_type])) {
                $connect_type = $provider[$connect_type];
            }

            // 获得 OAuth 授权信息
            $oauthUser = [];
            if (!empty($connect_type) && !empty($unionid)) {
                $cache_id = $connect_type . md5($unionid . $platform);
                $oauthUser = Cache::get($cache_id);
            }

            if (empty($oauthUser)) {
                throw new OauthException(trans('user.msg_authoriza_error'), 422);
            }
        }

        // 注册
        if (is_null($user)) {
            if ($request->has('pwd')) {
                // 混合app 登录注册验证密码
                $validator = Validator::make($request->all(), [
                    'username' => ['required', 'string', new UserName()],
                    'password' => ['required', 'different:username', new PasswordRule()], // 密码
                ], [
                    'password.required' => trans('user.user_pass_empty'),
                    'password.different' => trans('user.user_pass_same')
                ]);
                // 返回错误
                if ($validator->fails()) {
                    throw new OauthException($validator->errors()->first(), 422);
                }
            }

            if (!empty($mobile)) {
                // 验证此手机号是否已经被绑定
                $user_connect = $this->connectUserService->checkMobileBind($mobile, $connect_type);
                if (!empty($user_connect)) {
                    // 该手机号已被绑定
                    throw new OauthException(trans('user.mobile_isbinded'), 422);
                }
            }

            // 自动注册并登录
            $other['mobile_phone'] = $mobile;
            $parent_id = $request->get('parent_id', 0);
            //$parent_id = !empty($parent_id) ? base64_decode($parent_id) : 0; // 解密值
            if (file_exists(MOBILE_DRP)) {
                $other['drp_parent_id'] = $parent_id;
            }
            $other['parent_id'] = $parent_id;
            // 微信通粉丝 保存的推荐参数信息
            if (file_exists(MOBILE_WECHAT) && $unionid) {
                $wechat_user = app(\App\Modules\Wechat\Services\WechatUserService::class)->get_parent($unionid);
                if (!empty($wechat_user)) {
                    if (file_exists(MOBILE_DRP)) {
                        $drp_parent_id = $wechat_user->drp_parent_id > 0 ? $wechat_user->drp_parent_id : 0;
                    }
                    $parent_id = $wechat_user->parent_id > 0 ? $wechat_user->parent_id : 0;
                }
            }
            // 分销用户
            if (file_exists(MOBILE_DRP)) {
                $other['drp_parent_id'] = (isset($drp_parent_id) && $drp_parent_id > 0) ? $drp_parent_id : $other['drp_parent_id'];
            }
            // 普通用户
            $other['parent_id'] = (isset($parent_id) && $parent_id > 0) ? $parent_id : $other['parent_id'];

            if (!empty($connect_type) && !empty($oauthUser)) {
                $other['nick_name'] = $oauthUser['nickname'] ?? ''; // 取授权昵称
                $other['user_picture'] = $oauthUser['headimgurl'] ?? ''; // 授权头像
                $other['sex'] = $oauthUser['sex'] ?? 0;
            }

            if ($this->userLoginRegisterService->register($request->get('username'), $request->get('password'), $request->get('email'), $other)) {
                $user = $this->userCommonService->getUserByName($mobile);

                // 更新 OAuth 授权信息
                if (!empty($connect_type) && !empty($oauthUser)) {
                    $oauthUser['user_id'] = $user->user_id;
                    $oauthUser['unionid'] = $unionid;

                    // 更新社会化登录信息
                    $this->connectUserService->updateConnectUser($oauthUser, $connect_type);
                    if (file_exists(MOBILE_WECHAT) && is_wechat_browser() && $connect_type == 'wechat') {
                        // 更新微信粉丝信息
                        $oauthUser['from'] = 1;
                        app(\App\Modules\Wechat\Services\WechatUserService::class)->update_wechat_user($oauthUser);

                        // 关注送红包
                        app(\App\Modules\Wechat\Services\WechatService::class)->sendBonus($oauthUser['openid']);
                    }
                }

                return $user->user_id;
            } else {
                $error = $this->userLoginRegisterService->getError();
                $message = current($error->last_message());

                throw new OauthException($message, $error->error_no());
            }
        } else {
            // 不启用自动注册的时候，如果已存在账户信息，返回error
            if ($request->get('allow_login', 1) == 0) {
                throw new OauthException(trans('user.mobile_isbinded'), 422);
            }

            // 更新 OAuth 授权信息
            if (!empty($connect_type) && !empty($oauthUser)) {
                // 校验手机账号是否已绑定其他账号
                $connect = $this->connectUserService->getUserinfo($user->user_id, $connect_type);
                if (isset($connect['open_id']) && $connect['open_id'] != $unionid) {
                    throw new OauthException(trans('user.mobile_isbinded'), 422);
                }

                $oauthUser['user_id'] = $user->user_id;
                $oauthUser['unionid'] = $unionid;

                // 更新社会化登录信息
                $this->connectUserService->updateConnectUser($oauthUser, $connect_type);
                if (file_exists(MOBILE_WECHAT) && is_wechat_browser() && $connect_type == 'wechat') {
                    // 更新微信粉丝信息
                    app(\App\Modules\Wechat\Services\WechatUserService::class)->update_wechat_user($oauthUser, 1);
                }
            }

            // 登录成功更新用户信息
            $this->userCommonService->updateUserInfo($user->user_id, 'mobile');
            // 重新计算购物车信息
            $this->cartCommonService->recalculatePriceMobileCart($user->user_id);

            return $user->user_id;
        }
    }

    /**
     * 授权注册
     *
     * @param Request $request
     * @return bool|mixed
     * @throws OauthException
     */
    public function autoRegister(Request $request)
    {
        $connect_type = $request->get('type', '');
        $platform = $request->get('platform', '');
        $unionid = $request->get('unionid', '');

        $mobile = $request->get('mobile', '');
        $password = $request->get('code', StrRepository::random(6) . mt_rand(10, 99));

        $request->offsetSet('username', StrRepository::generate_username($connect_type, $unionid));
        $request->offsetSet('password', $password);
        $request->offsetSet('email', '');
        $request->offsetSet('mobile_phone', $mobile);

        // $connect_type 参数为空的情况下为H5模式，否则为授权登录
        if (!empty($connect_type)) {
            // 定义服务类型映射
            $provider = [
                'weixin' => 'wechat',
            ];

            // 覆写类型
            if (isset($provider[$connect_type])) {
                $connect_type = $provider[$connect_type];
            }

            // 获得 OAuth 授权信息
            $oauthUser = [];
            if (!empty($connect_type) && !empty($unionid)) {
                $cache_id = $connect_type . md5($unionid . $platform);
                $oauthUser = Cache::get($cache_id);
            }

            if (empty($oauthUser)) {
                throw new OauthException(trans('user.msg_authoriza_error'), 422);
            }
        }

        // 2 输手机号验证授权注册
        $step = $request->get('step', 1);
        if ($step == 2) {
            if (empty($mobile)) {
                throw new OauthException(trans('user.bind_mobile_null'), 422);
            }

            // 验证此手机号是否已经被绑定
            $user_connect = $this->connectUserService->checkMobileBind($mobile, $connect_type);
            if (!empty($user_connect)) {
                // 该手机号已被绑定
                return $user_connect['user_id'];
            }

            // 验证会员名或手机号是否已注册
            $users = $this->connectUserService->checkMobileRegister($mobile);
            if (!empty($users)) {
                // 更新当前手机号会员 社会化登录信息
                $oauthUser['user_id'] = $users['user_id'];
                $this->connectUserService->updateConnectUser($oauthUser, $connect_type);

                // 更新微信粉丝信息
                if (file_exists(MOBILE_WECHAT) && is_wechat_browser() && $connect_type == 'wechat') {
                    app(\App\Modules\Wechat\Services\WechatUserService::class)->update_wechat_user($oauthUser);
                }

                return $users['user_id'];
            }
        }

        // 自动注册并登录
        $other['mobile_phone'] = $mobile;
        $parent_id = $request->get('parent_id', 0);
        //$parent_id = !empty($parent_id) ? base64_decode($parent_id) : 0; // 解密值
        if (file_exists(MOBILE_DRP)) {
            $other['drp_parent_id'] = $parent_id;
        }
        $other['parent_id'] = $parent_id;
        // 微信通粉丝 保存的推荐参数信息
        if (file_exists(MOBILE_WECHAT) && $unionid) {
            $wechat_user = app(\App\Modules\Wechat\Services\WechatUserService::class)->get_parent($unionid);
            if (!empty($wechat_user)) {
                if (file_exists(MOBILE_DRP)) {
                    $drp_parent_id = $wechat_user->drp_parent_id > 0 ? $wechat_user->drp_parent_id : 0;
                }
                $parent_id = $wechat_user->parent_id > 0 ? $wechat_user->parent_id : 0;
            }
        }
        // 分销用户
        if (file_exists(MOBILE_DRP)) {
            $other['drp_parent_id'] = (isset($drp_parent_id) && $drp_parent_id > 0) ? $drp_parent_id : $other['drp_parent_id'];
        }
        // 普通用户
        $other['parent_id'] = (isset($parent_id) && $parent_id > 0) ? $parent_id : $other['parent_id'];

        if (!empty($connect_type) && !empty($oauthUser)) {
            $other['nick_name'] = $oauthUser['nickname'] ?? ''; // 取授权昵称
            $other['user_picture'] = $oauthUser['headimgurl'] ?? ''; // 授权头像
            $other['sex'] = $oauthUser['sex'] ?? 0;
        }

        $username = $request->get('username');

        if ($this->userLoginRegisterService->register($username, $request->get('password'), $request->get('email'), $other)) {
            $user = $this->userCommonService->getUserByName($username);

            if (!empty($connect_type) && !empty($oauthUser)) {
                $oauthUser['user_id'] = $user->user_id;
                $oauthUser['unionid'] = $unionid;

                // 更新社会化登录信息
                $this->connectUserService->updateConnectUser($oauthUser, $connect_type);
                // 更新微信粉丝信息
                if (file_exists(MOBILE_WECHAT) && is_wechat_browser() && $connect_type == 'wechat') {
                    $oauthUser['from'] = 1;
                    app(\App\Modules\Wechat\Services\WechatUserService::class)->update_wechat_user($oauthUser);

                    // 关注送红包
                    app(\App\Modules\Wechat\Services\WechatService::class)->sendBonus($oauthUser['openid']);
                }
            }

            return $user->user_id;
        } else {
            $error = $this->userLoginRegisterService->getError();
            $message = current($error->last_message());

            throw new OauthException($message, 422);
        }
    }

    /**
     * 授权登录
     *
     * @param Request $request
     * @param array $oauthUser
     * @param array $connectUser
     * @return bool|mixed
     * @throws OauthException
     */
    public function autoLogin(Request $request, $oauthUser = [], $connectUser = [])
    {
        $connect_type = $request->get('type', '');
        $unionid = $request->get('unionid', '');

        $user_id = $connectUser['user_id'];

        // 更新 OAuth 授权信息
        if (!empty($connect_type) && !empty($oauthUser)) {
            // 校验手机账号是否已绑定其他账号
            $connect = $this->connectUserService->getUserinfo($user_id, $connect_type);
            if (isset($connect['open_id']) && $connect['open_id'] != $unionid) {
                throw new OauthException(trans('user.mobile_isbinded'), 422);
            }

            $oauthUser['user_id'] = $user_id;
            $oauthUser['unionid'] = $unionid;

            // 更新社会化登录信息
            $this->connectUserService->updateConnectUser($oauthUser, $connect_type);
            // 更新微信粉丝信息
            if (file_exists(MOBILE_WECHAT) && is_wechat_browser() && $connect_type == 'wechat') {
                app(\App\Modules\Wechat\Services\WechatUserService::class)->update_wechat_user($oauthUser, 1);
            }

            // 登录成功更新用户信息
            $this->userCommonService->updateUserInfo($user_id, 'mobile');
            // 重新计算购物车信息
            $this->cartCommonService->recalculatePriceMobileCart($user_id);

            return $user_id;
        }

        throw new OauthException(trans('user.msg_authoriza_error'), 422);
    }

    /**
     * 验证账号密码
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function checkUser($username = '', $password = '')
    {
        $condition = ['field' => 'user_name', 'value' => $username];
        if (CommonRepository::getMatchEmail($username)) {
            $condition['field'] = 'email';
        } elseif (is_phone_number($username)) {
            $condition['field'] = 'mobile_phone';
        }

        $row = Users::where('user_name', $username)->orWhere($condition['field'], $condition['value'])->select('user_id', 'password', 'ec_salt', 'mobile_phone');

        $user = BaseRepository::getToArrayFirst($row);

        if (empty($user)) {
            return false;
        }

        // 兼容原md5密码
        if (isset($user['password']) && strlen($user['password']) == 32) {

            $password = !empty($user['ec_salt']) ? md5(md5($password) . $user['ec_salt']) : md5($password);

            if ($password == $user['password']) {
                return $user['user_id'];
            }
        } else {
            // 验证hash密码
            if (Hash::check($password, $user['password'])) {
                // 密码匹配
                return $user['user_id'];
            }
        }

        return false;
    }
}
