<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Api\Transformers\UserTransformer;
use App\Exceptions\OauthException;
use App\Extensions\AdvancedThrottlesLogins;
use App\Rules\PasswordRule;
use App\Rules\UserName;
use App\Services\Cart\CartCommonService;
use App\Services\User\AuthService;
use App\Services\User\UserCommonService;
use App\Services\User\UserLoginRegisterService;
use App\Services\User\UserOauthService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class PassportController
 * @package App\Api\Controllers
 */
class PassportController extends Controller
{
    use AdvancedThrottlesLogins;

    protected $userTransformer;
    protected $authService;
    protected $userCommonService;
    protected $userOauthService;
    protected $cartCommonService;
    protected $userLoginRegisterService;

    public function __construct(
        UserTransformer $userTransformer,
        AuthService $authService,
        UserCommonService $userCommonService,
        UserOauthService $userOauthService,
        CartCommonService $cartCommonService,
        UserLoginRegisterService $userLoginRegisterService
    )
    {
        $this->userTransformer = $userTransformer;
        $this->authService = $authService;
        $this->userCommonService = $userCommonService;
        $this->userOauthService = $userOauthService;
        $this->cartCommonService = $cartCommonService;
        $this->userLoginRegisterService = $userLoginRegisterService;
    }

    /**
     * 电子邮箱规则验证
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function register(Request $request)
    {
        // 是否开启注册
        if (config('shop.shop_reg_closed') == 1) {
            return $this->setErrorCode(422)->failed(trans('common.shop_register_closed'));
        }

        // 数据验证
        $validator = Validator::make($request->all(), [
            'username' => ['unique:users,user_name', new UserName()], // 用户名唯一
            'email' => 'required|email', // 电子邮箱
            'password' => ['required', 'different:username', new PasswordRule()], // 密码
            'code' => 'required', // 图片验证码
        ], [
            'username.unique' => lang('user.msg_un_registered'),
            'password.required' => lang('user.user_pass_empty'),
            'password.different' => lang('user.user_pass_same')
        ]);

        // 返回错误
        if ($validator->fails()) {
            return $this->setErrorCode(422)->failed($validator->errors()->first());
        }

        $username = $request->get('username');
        $email = $request->get('email');
        $password = $request->get('password');

        // 推荐参数
        $parent_id = $request->get('parent_id', 0);

        if (file_exists(MOBILE_DRP)) {
            $other['drp_parent_id'] = $parent_id;
        }
        $other['parent_id'] = $parent_id;

        if ($this->userLoginRegisterService->register($username, $password, $email, $other)) {
            return $this->login($request);
        } else {
            $message = current($GLOBALS['err']->last_message());
            return $this->setErrorCode(422)->failed($message);
        }
    }

    /**
     * 返回用户登录成功之后的token
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function login(Request $request)
    {
        $username = $request->get('username');
        $password = $request->get('password');

        $user = $this->userCommonService->getUserByName($username);

        if (is_null($user)) {
            return $this->failed(lang('user.user_not_exist'));
        }

        // 登录锁定1: 判断登录失败次数超过限制则返回true 触发锁定操作, 返回false 不会触发
        $request->offsetSet('user_id', $user->user_id);
        if ($this->hasTooManyLoginAttempts($request)) {
            $respond = $this->sendLockoutResponse($request);

            return $this->setErrorCode(422)->failed($respond);
        }

        // 兼容原md5密码
        if (strlen($user->password) == 32) {
            $password = empty($user->ec_salt) ? md5($password) : md5(md5($password) . $user->ec_salt);

            if ($password != $user->password) {

                // 登录锁定2: 如果登录尝试不成功，增加尝试次数
                $respond = $this->incrementLoginAttempts($request);

                return $this->setErrorCode(422)->failed($respond);
            }

            // 通过验证 生成hash密码
            $GLOBALS['user']->edit_user([
                'user_id' => $user->user_id,
                'username' => $user->user_name,
                'password' => $request->get('password')
            ]);
        } else {
            // 验证
            $verify = $GLOBALS['user']->verify_password($password, $user->password);
            if ($verify === false) {

                // 登录锁定2: 如果登录尝试不成功，增加尝试次数
                $respond = $this->incrementLoginAttempts($request);

                return $this->setErrorCode(422)->failed($respond);
            }
        }

        // 登录锁定3: 在用户通过身份验证后 清除计数器
        $this->clearLoginAttempts($request);

        $jwt = $this->JWTEncode(['user_id' => $user->user_id]);

        $this->userCommonService->usersLogChange($user->user_id, USER_LOGIN, MOBILE_USER);

        // 登录成功更新用户信息
        $this->userCommonService->updateUserInfo($user->user_id, 'mobile');

        // 重新计算购物车信息
        $this->cartCommonService->recalculatePriceMobileCart($user->user_id);

        return $this->succeed($jwt);
    }

    /**
     * 手机号码快捷登录
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function fastLogin(Request $request)
    {
        // ECJia App 快捷登录
        if ($request->has('ecjiahash')) {
            $user = $this->ecjiaLogin($request);

            if ($user === false) {
                return $this->setErrorCode(422)->failed(lang('user.ecjia_auth_login_fail'));
            } else {
                $jwt = $this->JWTEncode(['user_id' => collect($user)->get('user_id')]);
                return $this->succeed($jwt);
            }
        }

        // 验证短信验证码
        if (!$this->verifySMS($request)) {
            return $this->setErrorCode(422)->failed(lang('user.bind_mobile_code_error'));
        }

        try {
            $user_id = $this->authService->loginOrRegister($request);
        } catch (OauthException $oauthException) {
            return $this->setErrorCode($oauthException->getCode())->failed($oauthException->getMessage());
        }

        if ($user_id) {
            // 登录日志
            $this->userCommonService->usersLogChange($user_id, USER_LOGIN, MOBILE_USER);

            $jwt = $this->JWTEncode(['user_id' => $user_id]);
            return $this->succeed($jwt);
        }

        return $this->setErrorCode(404)->failed('error');
    }

    /**
     * 重置密码
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function reset(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'different:mobile', new PasswordRule()], // 密码
        ], [
            'password.filled' => lang('user.user_pass_empty'),
            'password.different' => lang('user.user_pass_same')
        ]);

        // 返回错误
        if ($validator->fails()) {
            return $this->setErrorCode(422)->failed($validator->errors()->first());
        }

        // 验证短信验证码
        if (!$this->verifySMS($request)) {
            return $this->setErrorCode(422)->failed(lang('user.bind_mobile_code_error'));
        }

        // 查找用户信息
        $mobile = $request->get('mobile');
        $user = $this->userCommonService->getUserByName($mobile);

        if (is_null($user)) {
            return $this->setErrorCode(102)->failed(lang('user.user_not_exist'));
        }

        // 重置用户新密码
        $GLOBALS['user']->edit_user([
            'user_id' => collect($user)->get('user_id'),
            'username' => collect($user)->get('user_name'),
            'password' => $request->get('password')
        ], 1);

        $res = $this->userTransformer->transform($user);

        $this->userCommonService->usersLogChange(collect($user)->get('user_id'), USER_LPASS, MOBILE_USER);

        return $this->succeed($res);
    }

    /**
     * h5社会化登录列表
     * @return JsonResponse
     */
    public function oauth_list()
    {
        // 获取已经安装的
        $columns = ['id', 'type'];
        $list = $this->userOauthService->mobileOauthList(1, $columns);

        return $this->succeed($list);
    }

    /**
     * 获取注册配置
     * @return JsonResponse
     */
    public function loginConfig()
    {
        $res['shop_reg_closed'] = config('shop.shop_reg_closed');

        return $this->succeed($res);
    }
}
