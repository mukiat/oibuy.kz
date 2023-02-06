<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Repositories\Common\DscRepository;
use App\Rules\PhoneNumber;
use App\Services\User\AccountSafeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * 账户安全中心
 * Class AccountSafeController
 * @package App\Api\Controllers
 */
class AccountSafeController extends Controller
{
    protected $accountSafeService;
    protected $dscRepository;

    public function __construct(
        AccountSafeService $accountSafeService,
        DscRepository $dscRepository
    )
    {
        $this->accountSafeService = $accountSafeService;
        $this->dscRepository = $dscRepository;
    }

    /**
     * 账户安全首页
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request)
    {
        //返回用户ID
        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $user_info = $this->accountSafeService->users($user_id, ['is_validated', 'mobile_phone']);

        // 是否验证邮箱
        $result['is_validated'] = !empty($user_info['is_validated']) ? 1 : 0;

        // 是否启用支付密码
        $result['users_paypwd'] = $this->accountSafeService->userPaypwdCount($user_id);

        // 是否是授权登录用户 如果是 则不显示修改密码
        $result['is_connect_user'] = $this->accountSafeService->isConnectUser($user_id);

        // 是否绑定手机号
        $result['mobile_phone'] = $user_info['mobile_phone'] ?? '';

        return $this->succeed($result);
    }

    /**
     * 启用支付密码
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function addPaypwd(Request $request)
    {
        //返回用户ID
        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $result = ['code' => 0, 'msg' => ''];

        $result['notice'] = lang('user.Enable_pay_password_desc');

        // 判断是否启用支付密码
        $result['users_paypwd'] = $this->accountSafeService->userPaypwd($user_id);

        // 验证类型 开启短信 默认短信
        $result['validate_type'] = $validate_type = $this->accountSafeService->validateType();

        $result['user_info'] = $user_info = $this->accountSafeService->users($user_id);
        $mobile_is_validated = !empty($user_info['mobile_phone']) ? 1 : 0;

        // 开启短信优先验证手机
        if ($validate_type == 'phone' && $mobile_is_validated == 0) {
            // 手机未验证
            return $this->succeed(['code' => 1, 'msg' => lang('user.mobile_no_validate')]);
        }

        return $this->succeed($result);
    }

    /**
     * 修改支付密码
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function editPaypwd(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'paypwd_id' => 'required|integer'
        ]);

        // 返回错误
        if ($validator->fails()) {
            return $this->setErrorCode(422)->failed($validator->errors()->first());
        }

        //返回用户ID
        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $paypwd_id = (int)$request->input('paypwd_id', 0);
        $pay_paypwd = e($request->input('pay_paypwd', '')); // 支付密码
        $mobile = e($request->input('mobile', ''));

        $pay_online = (int)$request->input('pay_online', 0);
        $user_surplus = (int)$request->input('user_surplus', 0);
        $user_point = (int)$request->input('user_point', 0);
        $baitiao = (int)$request->input('baitiao', 0);
        $gift_card = (int)$request->input('gift_card', 0);

        $validate_type = $request->input('validate_type', 'phone'); // 验证类型 phone 手机 或 邮箱 email

        if ($validate_type == 'phone') {
            // 验证短信验证码
            if (!$this->verifySMS($request)) {
                return $this->failed(lang('user.bind_mobile_code_error'));
            }
        }

        if (empty($pay_paypwd)) {
            // 支付密码不能为空
            return $this->succeed(['code' => 1, 'msg' => lang('user.input_pay_password')]);
        }

        // 支付密码长度限制6位数字
        if (strlen($pay_paypwd) != 6) {
            return $this->succeed(['code' => 1, 'msg' => lang('flow.paypwd_length_limit')]);
        }

        $data = [
            'user_id' => $user_id,
            'pay_online' => $pay_online,
            'user_surplus' => $user_surplus,
            'user_point' => $user_point,
            'baitiao' => $baitiao,
            'gift_card' => $gift_card
        ];
        // 加密
        $ec_salt = rand(1, 9999);
        $new_password = md5(md5($pay_paypwd) . $ec_salt);

        $data['pay_password'] = $new_password;
        $data['ec_salt'] = $ec_salt;

        $user_info = $this->accountSafeService->users($user_id);
        // 未绑定手机号更新绑定
        if (empty($user_info['mobile_phone'])) {
            $this->accountSafeService->updateUserMobile($user_id, $mobile);
        }

        if ($paypwd_id) {
            $this->accountSafeService->updateUsersPaypwd($user_id, $paypwd_id, $data);

            return $this->succeed(['code' => 0, 'msg' => lang('user.edit_pay_password_success')]);
        } else {
            // 启用支付密码
            $this->accountSafeService->addUsersPaypwd($data);

            return $this->succeed(['code' => 0, 'msg' => lang('user.Enable_pay_password_notice')]);
        }
    }

    /**
     * 绑定手机号
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bind_mobile(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'mobile' => ['required', new PhoneNumber()],
        ], [
            'mobile.required' => trans('user.bind_mobile_null'),
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

        $mobile = e($request->input('mobile', ''));

        // 验证短信验证码
        if (!$this->verifySMS($request)) {
            return $this->setErrorCode(422)->failed(trans('user.bind_mobile_code_error'));
        }

        // 验证会员名或手机号是否已注册
        $count = $this->accountSafeService->checkUserMobile($user_id, $mobile);
        if (!empty($count)) {
            // 手机号已注册 请更换手机号
            return $this->setErrorCode(422)->failed(trans('user.please_change_mobile'));
        }

        $result = $this->accountSafeService->updateUserMobile($user_id, $mobile);
        if ($result) {
            return $this->succeed(['code' => 0, 'msg' => trans('user.bind_mobile_success')]);
        }

        return $this->setErrorCode(422)->failed(trans('user.bind_mobile_fail'));
    }

    /**
     * 修改手机号
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function change_mobile(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'mobile' => ['required', new PhoneNumber()],
        ], [
            'mobile.required' => trans('user.bind_mobile_null'),
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

        $mobile = e($request->input('mobile', ''));

        // 验证短信验证码
        if (!$this->verifySMS($request)) {
            return $this->setErrorCode(422)->failed(trans('user.bind_mobile_code_error'));
        }

        $step = $request->input('step', 1);
        if ($step == 1) {
            $user_info = $this->accountSafeService->users($user_id, ['mobile_phone']);
            $mobile_phone = $user_info['mobile_phone'] ?? '';

            if ($mobile != $mobile_phone) {
                // 绑定手机号与原手机号不符
                return $this->setErrorCode(422)->failed(trans('user.Real_name_authentication_Mobile_one'));
            }

            // 验证现绑定手机号 验证成功
            return $this->succeed(['code' => 0, 'step' => $step, 'msg' => trans('user.verify_old_mobile_success')]);
        } elseif ($step == 2) {
            // 验证新会员名或手机号是否已注册
            $count = $this->accountSafeService->checkUserMobile($user_id, $mobile);
            if (!empty($count)) {
                // 手机号已注册
                return $this->setErrorCode(422)->failed(trans('user.please_change_mobile'));
            }

            $result = $this->accountSafeService->updateUserMobile($user_id, $mobile);
            if ($result) {
                return $this->succeed(['code' => 0, 'step' => $step, 'msg' => trans('user.bind_mobile_success')]);
            }
        }

        return $this->setErrorCode(422)->failed(trans('user.bind_mobile_fail'));
    }
}
