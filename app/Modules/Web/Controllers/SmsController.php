<?php

namespace App\Modules\Web\Controllers;

use App\Libraries\CaptchaVerify;
use App\Models\Users;
use App\Repositories\Common\CommonRepository;
use App\Rules\PhoneNumber;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

/**
 * 发送短信
 */
class SmsController extends InitController
{
    protected $commonRepository;

    public function __construct(
        CommonRepository $commonRepository
    )
    {
        $this->commonRepository = $commonRepository;
    }

    public function index()
    {
        $user_id = session('user_id', 0);
        $mobile = e(request()->input('mobile', ''));

        $act = e(trim(request()->input('act', '')));

        // 检查验证码
        if ($act == 'check') {
            // 数据验证
            $message = [
                'mobile.required' => trans('sms.json_msg.phone_not_null'),
                'mobile_code.required' => trans('sms.json_msg.identify_not_null'),
            ];
            $validator = Validator::make(request()->all(), [
                'mobile' => ['required', new PhoneNumber()],
                'mobile_code' => 'required',
            ], $message);

            // 返回错误
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->first(), 'error' => 1]);
            }

            $mobile_code = e(request()->input('mobile_code', ''));

            // 验证码有效期
            if ((session()->has('sms_expiry') && session('sms_expiry') < Carbon::now())) {
                return response()->json(['msg' => lang('sms.json_msg.sms_expire'), 'error' => 1]);
            }

            if ((session()->has('sms_mobile') && $mobile != session('sms_mobile')) || (session()->has('sms_mobile_code') && $mobile_code != session('sms_mobile_code'))) {
                return response()->json(['msg' => lang('sms.json_msg.identify_error'), 'error' => 1]);
            } else {
                return response()->json(['code' => '2']);
            }
        }

        // 发送验证码
        if ($act == 'send') {
            // 数据验证
            $message = [
                'username.required' => trans('sms.json_msg.phone_not_null'),
                'mobile.required' => trans('sms.json_msg.phone_not_null'),
            ];
            $validator = Validator::make(request()->all(), [
                'username' => 'required',
                'mobile' => ['required', new PhoneNumber()]
            ], $message);

            // 返回错误
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->first(), 'error' => 1]);
            }

            $username = e(trim(request()->input('username', '')));

            // 发送时机
            $send_time = addslashes(trim(request()->input('sms_value', 'sms_signin')));

            $flag = e(request()->input('flag', '')); // 标识：注册、找回密码

            $captcha_config = (int)config('shop.captcha');
            if (($flag == 'register' && ($captcha_config & CAPTCHA_REGISTER) && gd_version() > 0) ||
                request()->exists('captcha') ||
                ($flag == 'change_password_f' && ($captcha_config & CAPTCHA_SAFETY) && gd_version() > 0)
            ) {
                $captcha = e(trim(request()->input('captcha', '')));
                $seKey = e(trim(request()->input('sekey', 'mobile_phone')));

                if (empty($captcha)) {
                    return response()->json(['msg' => lang('sms.json_msg.identify_not_null')]);
                }

                $captcha_code = app(CaptchaVerify::class)->check($captcha, $seKey, '', 'ajax');
                if (!$captcha_code) {
                    return response()->json(['msg' => lang('sms.json_msg.identify_has_error')]);
                }
            }

            // 限制发送速率 60s
            $frequency = 60;
            if (session('sms_mobile')) {
                if (Cache::get($mobile . '_send_frequency') > (time() - $frequency)) {
                    return response()->json(['msg' => lang('sms.json_msg.code_not_noeminute')]);
                }
            }

            $row = Users::where('mobile_phone', $mobile)->orWhere('user_name', $mobile);

            if ($user_id > 0) {
                $row = $row->where('user_id', '<>', $user_id);
            }

            $count = $row->count('user_id');

            if ($flag) {
                if ($flag == 'register' || $flag == 'change_mobile') {
                    //手机注册
                    if ($count > 0) {
                        return response()->json(['msg' => lang('sms.json_msg.phone_ishas')]);
                    }
                } elseif ($flag == 'forget') {
                    //找回密码
                    if (empty($count)) {
                        return response()->json(['msg' => lang('sms.json_msg.phone_not_has')]);
                    }
                }
            }

            // 获取验证码
            $sms_code = mt_rand(100000, 999999);

            $message['code'] = $sms_code;

            if ($send_time == 'sms_signin') {
                $message['product'] = config('shop.shop_name');
            } else {
                $message['mobile_phone'] = $mobile;
                $message['mobilephone'] = $mobile;
                $message['product'] = $username;
            }

            $send_result = $this->commonRepository->smsSend($mobile, $message, $send_time);

            if ($send_result === true) {
                $sms_security_code = mt_rand(1000, 9999);

                // 验证码有效期 默认且不超过10分钟
                $expiry = (int)config('shop.sms_validity', 10);
                $expiry = ($expiry > 10 || empty($expiry)) ? 10 : $expiry;

                session([
                    'sms_mobile' => $mobile,
                    'sms_mobile_code' => $sms_code,
                    'sms_security_code' => $sms_security_code,
                    'sms_expiry' => Carbon::now()->addMinutes($expiry),
                ]);

                // 限制发送速率
                Cache::put($mobile . '_send_frequency', time(), Carbon::now()->addSeconds($frequency));

                return response()->json(['code' => 2, 'flag' => $flag, 'sms_security_code' => $sms_security_code]);
            } else {
                return response()->json(['msg' => $send_result, 'error' => 1]);
            }

        }
    }

}
