<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Exceptions\OauthException;
use App\Models\Users;
use App\Repositories\Common\StrRepository;
use App\Rules\PhoneNumber;
use App\Services\User\AuthService;
use App\Services\User\ConnectUserService;
use App\Services\User\UserCommonService;
use App\Services\User\UserOauthService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class OAuthController
 * @package App\Api\Controllers
 */
class OAuthController extends Controller
{
    protected $connectUserService;
    protected $authService;
    protected $userCommonService;

    /**
     * OAuthController constructor.
     * @param ConnectUserService $connectUserService
     * @param AuthService $authService
     * @param UserCommonService $userCommonService
     */
    public function __construct(
        ConnectUserService $connectUserService,
        AuthService $authService,
        UserCommonService $userCommonService
    )
    {
        $this->connectUserService = $connectUserService;
        $this->authService = $authService;
        $this->userCommonService = $userCommonService;
    }

    /**
     * 生成链接
     * @param Request $request
     * @return JsonResponse|RedirectResponse|Redirector
     */
    public function code(Request $request)
    {
        $app = $this->getProvider($request);

        if (is_null($app)) {
            return $this->responseNotFound();
        }

        // 检测是否安装
        if ($app->status($request->get('type')) == 0) {
            if ($request->has('target_url')) {
                $url = $request->get('target_url');
            } else {
                $url = route('mobile');
            }
            return redirect($url);
        }

        // 记录回调目标URL
        $target_url = $request->get('target_url');
        // base64 解密
        $target_url = is_null($target_url) ? null : base64_decode($target_url);
        Cache::put('target_url', $target_url, Carbon::now()->addMinutes(5));

        return $app->redirect();
    }

    /**
     * 授权回调
     * @param Request $request
     * @return JsonResponse|RedirectResponse|Redirector
     * @throws ValidationException
     */
    public function callback(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'type' => 'required|string', // 授权登录类型
        ]);
        // 返回错误
        if ($validator->fails()) {
            return $this->setErrorCode(422)->failed($validator->errors()->first());
        }

        // APP 微信登录
        $platform = $request->get('platform', ''); // platform平台类型如下：H5/APP/MP-WEIXIN等
        $type = $request->get('type');
        if ($platform === 'APP' && $type === 'weixin') {
            $request->offsetSet('type', 'wechat');
        }

        $app = $this->getProvider($request);

        $oauthUser = $app->callback();

        if (empty($oauthUser)) {
            $url = $request->get('target_url');
            $url = is_null($url) ? route('mobile') : $url;
            return redirect($url);
        }

        // 保存授权信息
        $connect_type = $request->get('type');
        $unionid = $oauthUser['unionid'] ?? '';

        $cache_id = $connect_type . md5($unionid . $platform);
        Cache::put($cache_id, $oauthUser, Carbon::now()->addHours(2));

        // 处理授权登录后原路返回跳转地址
        $target_url = Cache::get('target_url');
        $target_url = is_null($target_url) ? dsc_url('/#/user') : $target_url;
        // base64 加密
        $url = base64_encode($target_url);

        // 仅获取授权信息 然后返回
        $getOauth = $request->input('get_oauth');
        if (!is_null($getOauth) && !empty($oauthUser)) {
            return $this->succeed(['unionid' => $unionid, 'type' => $connect_type, 'url' => $url]);
        }

        // 会员中心授权绑定
        $get_user_id = $request->get('user_id', 0);
        if ($get_user_id > 0) {
            return $this->userBind($request);
        }

        $request->offsetSet('unionid', $unionid);

        /**
         * 授权登录、注册
         */
        return $this->bind_register($request);
    }

    /**
     * 授权登录、注册
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bind_register(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'type' => 'required|string', // 授权登录类型
        ]);
        // 返回错误
        if ($validator->fails()) {
            return $this->setErrorCode(422)->failed($validator->errors()->first());
        }

        $connect_type = $request->get('type');
        $platform = $request->get('platform', '');
        $unionid = $request->get('unionid');

        // 获得 OAuth 授权信息
        $oauthUser = [];
        if (!empty($connect_type) && !empty($unionid)) {
            $cache_id = $connect_type . md5($unionid . $platform);
            $oauthUser = Cache::get($cache_id);
        }

        if (empty($oauthUser)) {
            return $this->setErrorCode(422)->failed(trans('user.msg_authoriza_error'));
        }

        // 处理授权登录后原路返回跳转地址
        $target_url = Cache::get('target_url');
        $target_url = is_null($target_url) ? dsc_url('/#/user') : $target_url;
        // base64 加密
        $url = base64_encode($target_url);

        // 查询是否绑定授权
        $connectUser = $this->connectUserService->getConnectUserinfo($unionid, $connect_type);

        if (empty($connectUser) && $connect_type == 'wechat') {
            // 查询同一微信开放平台下（unionid 相同） 绑定的授权用户
            $connectUser = $this->connectUserService->getConnectUserWeixin($unionid);
        }

        if (!empty($connectUser)) {
            /**
             * 已绑定授权 登录更新
             */
            try {

                $user_id = $this->authService->autoLogin($request, $oauthUser, $connectUser);

            } catch (OauthException $oauthException) {
                return $this->setErrorCode($oauthException->getCode())->failed($oauthException->getMessage());
            }

            if ($user_id) {
                // 登录日志
                $this->userCommonService->usersLogChange($user_id, USER_LOGIN, MOBILE_USER);

                $jwt = $this->JWTEncode(['user_id' => $user_id, 'oauth_type' => $connect_type]);

                return $this->succeed(['login' => 1, 'token' => $jwt, 'type' => $connect_type, 'url' => $url]);
            }
        } else {
            /**
             * 未绑定授权 注册并登录
             */
            if (file_exists(MOBILE_WECHAT) && is_wechat_browser() && $connect_type == 'wechat') {
                // 注册保存推荐人参数
                $parent_id = $request->get('parent_id', 0);
                //$parent_id = !empty($parent_id) ? base64_decode($parent_id) : 0;// 解密值
                $oauthUser['parent_id'] = $parent_id;
                $oauthUser['drp_parent_id'] = $parent_id;
                // 更新微信粉丝信息
                app(\App\Modules\Wechat\Services\WechatUserService::class)->update_wechat_user($oauthUser, 1);
            }

            $step = $request->get('step', 1); // step 默认 1 弹窗提示选择 0 跳过 直接授权注册（无手机号） 2 输手机号验证授权注册

            if ($step == 1) {
                // 提示 如果您希望绑定已有账号，请选择【去绑定】
                return $this->succeed(['code' => 42201, 'login' => 0, 'unionid' => $unionid, 'type' => $connect_type, 'url' => $url, 'msg' => trans('user.msg_rebind_exit_account')]);
            }

            if ($step == 2) {
                // 验证短信验证码
                if (!$this->verifySMS($request)) {
                    return $this->setErrorCode(422)->failed(lang('user.bind_mobile_code_error'));
                }
            }

            try {
                // 自动注册并登录
                $user_id = $this->authService->autoRegister($request);

            } catch (OauthException $oauthException) {
                return $this->setErrorCode($oauthException->getCode())->failed($oauthException->getMessage());
            }

            if ($user_id) {
                // 登录日志
                $this->userCommonService->usersLogChange($user_id, USER_LOGIN, MOBILE_USER);

                $jwt = $this->JWTEncode(['user_id' => $user_id, 'oauth_type' => $connect_type]);

                return $this->succeed(['login' => 1, 'token' => $jwt, 'type' => $connect_type, 'url' => $url]);
            }
        }

        return $this->setErrorCode(404)->failed('error');
    }

    /**
     * @param $request
     * @return mixed
     */
    protected function getProvider($request)
    {
        $type = StrRepository::studly($request->get('type'));

        $provider = 'App\\Plugins\\Connect\\' . $type . '\\' . $type;

        if (!class_exists($provider)) {
            return null;
        }

        return new $provider();
    }

    /**
     * 会员中心授权管理绑定帐号(自动)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function userBind(Request $request)
    {
        $get_user_id = $request->get('user_id', 0);
        if (empty($get_user_id)) {
            return $this->setErrorCode(12)->failed('user error');
        }

        $connect_type = $request->get('type');
        $platform = $request->get('platform', '');
        $unionid = $request->get('unionid');

        // 获得 OAuth 授权信息
        $oauthUser = [];
        if (!empty($connect_type) && !empty($unionid)) {
            $cache_id = $connect_type . md5($unionid . $platform);
            $oauthUser = Cache::get($cache_id);
        }

        if (empty($oauthUser)) {
            return $this->setErrorCode(422)->failed(trans('user.msg_authoriza_error'));
        }

        // 处理授权登录后原路返回跳转地址
        $target_url = Cache::get('target_url');
        $target_url = is_null($target_url) ? dsc_url('/#/user') : $target_url;
        // base64 加密
        $url = base64_encode($target_url);

        $user_id = $this->authorization();
        if ($user_id > 0 && $user_id != $get_user_id) {
            return $this->setErrorCode(12)->failed('user error');
        }

        // 查询users用户是否存在
        $users = Users::where('user_id', $user_id)->first();
        $users = $users ? $users->toArray() : [];

        if (!empty($users) && !empty($oauthUser['unionid'])) {
            // 查询users用户是否被其他人绑定
            $connect_user_id = $this->connectUserService->checkConnectUserId($oauthUser['unionid'], $connect_type);
            if ($connect_user_id > 0 && $connect_user_id != $users['user_id']) {
                return $this->setErrorCode(422)->failed(trans('user.mobile_isbinded'));
            }

            // 更新社会化登录信息
            $oauthUser['user_id'] = $user_id;
            $this->connectUserService->updateConnectUser($oauthUser, $connect_type);
            // 更新微信粉丝信息
            if (file_exists(MOBILE_WECHAT) && is_wechat_browser() && $connect_type == 'wechat') {
                app(\App\Modules\Wechat\Services\WechatUserService::class)->update_wechat_user($oauthUser, 1); // 1 不更新ect_uid
            }

            // 重新登录
            $jwt = $this->JWTEncode(['user_id' => $get_user_id, 'oauth_type' => $connect_type]);
            return $this->succeed(['login' => 1, 'token' => $jwt, 'type' => $connect_type, 'url' => $url]);
        } else {
            return $this->setErrorCode(12)->failed(trans('user.msg_account_bound_fail'));
        }
    }

    /**
     * 用户绑定登录信息列表
     *
     * @param Request $request
     * @param UserOauthService $userOauthService
     * @return JsonResponse
     * @throws Exception
     */
    public function bindList(Request $request, UserOauthService $userOauthService)
    {
        //返回用户ID
        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(trans('user.not_login'));
        }

        $connect_user = $this->connectUserService->connectUserList($user_id);

        // 显示已经安装的社会化登录插件
        $columns = ['id', 'type', 'status', 'sort'];
        $oauth_list = $userOauthService->getOauthList(1, $columns);

        $list = [];
        if (!empty($oauth_list)) {
            foreach ($oauth_list as $key => $vo) {
                $list[$vo['type']]['type'] = $vo['type'];
                $list[$vo['type']]['install'] = $vo['status'];
                // PC微信扫码
                if ($vo['type'] == 'weixin') {
                    $vo['type'] = 'wechat';
                }
                if ($vo['type'] == 'wechat' && !is_wechat_browser()) {
                    unset($list[$vo['type']]); // 过滤微信登录
                }
            }
        }

        if (!empty($connect_user)) {
            foreach ($connect_user as $key => $value) {
                $type = substr($value['connect_code'], 4);
                $list[$type]['user_id'] = $value['user_id'];
                $list[$type]['id'] = $value['id'];
                // 已绑定
                if ($value['user_id'] == $user_id) {
                    $list[$type]['is_bind'] = 1;
                }
            }

            $list = collect($list)->values()->all();
        }

        return $this->succeed($list);
    }

    /**
     * 授权解绑
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function unbind(Request $request)
    {
        //返回用户ID
        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(trans('user.not_login'));
        }

        $id = $request->input('id', 0);

        if (!empty($id)) {
            // 查询是否绑定 并且需填写验证手机号
            $users = $this->connectUserService->connectUserById($id, $user_id);
            if (!empty($users)) {
                if (!empty($users['mobile_phone'])) {
                    // 删除绑定记录
                    $this->connectUserService->connectUserDelete($users['open_id'], $user_id);

                    // 删除授权登录缓存
                    $cache_id = md5($users['open_id']);
                    if (Cache::has($cache_id)) {
                        Cache::forget($cache_id);
                    }
                    Cache::forget('target_url');

                    return $this->succeed(['code' => 0, 'msg' => trans('user.Un_bind') . trans('admin/common.success')]);
                } else {
                    return $this->succeed(['code' => 1, 'msg' => trans('user.Real_name_authentication_Mobile_two')]);
                }
            } else {
                return $this->succeed(['code' => 1, 'msg' => trans('user.not_Bound')]);
            }
        }

        return $this->succeed(['code' => 1, 'msg' => trans('admin/common.fail')]);
    }

    /**
     * 重新绑定社会化登录账号
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function rebind(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'mobile' => ['required', new PhoneNumber()],
            'password' => 'required',
            'type' => 'required',
        ], [
            'mobile.required' => trans('user.bind_mobile_null'),
            'password.required' => trans('user.bind_password_null'),
        ]);

        // 返回错误
        if ($validator->fails()) {
            return $this->setErrorCode(422)->failed($validator->errors()->first());
        }

        //返回用户ID
        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(trans('user.not_login'));
        }

        // 验证短信验证码
        if (!$this->verifySMS($request)) {
            return $this->setErrorCode(422)->failed(trans('user.bind_mobile_code_error'));
        }

        $connect_type = $request->get('type');

        $mobile = e($request->input('mobile', ''));

        // 验证会员名或手机号是否已注册
        $users = $this->connectUserService->checkMobileRegister($mobile);
        if (!empty($users)) {
            // 验证此手机号是否已经被绑定
            $user_connect = $this->connectUserService->checkMobileBind($mobile, $connect_type);
            if (!empty($user_connect)) {
                // 该会员已被绑定授权
                //$new_user_id = $user_connect['user_id'];
                // 解绑原绑定关系 删除绑定记录
                //$this->connectUserService->connectUserDelete($user_connect['open_id'], $new_user_id);

                // 该手机号已被绑定
                return $this->setErrorCode(422)->failed(trans('user.mobile_isbinded'));

            } else {

                $new_user_id = $users['user_id'];
            }

            // 重新绑定社会化登录用户信息
            $res['user_id'] = $new_user_id;
            $respond = $this->connectUserService->rebindConnectUser($user_id, $res, $connect_type);
            if ($respond) {
                // 重新登录
                $jwt = $this->JWTEncode(['user_id' => $new_user_id, 'oauth_type' => $connect_type]);
                return $this->succeed(['code' => 0, 'login' => 1, 'token' => $jwt, 'type' => $connect_type, 'msg' => trans('user.msg_account_bound_success')]);
            }

        } else {
            return $this->setErrorCode(422)->failed(trans('user.user_pass_error'));
        }

        return $this->setErrorCode(422)->failed(trans('user.msg_authoriza_error'));
    }
}
