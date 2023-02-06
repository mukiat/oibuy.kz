<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Models\Users;
use App\Repositories\Common\CommonRepository;
use App\Rules\PhoneNumber;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class SmsController
 * @package App\Api\Controllers
 */
class SmsController extends Controller
{
    /**
     * @var CommonRepository
     */
    protected $commonRepository;

    /**
     * SmsController constructor.
     * @param CommonRepository $commonRepository
     * @throws Exception
     */
    public function __construct(
        CommonRepository $commonRepository
    ) {
        $this->commonRepository = $commonRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function index(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'mobile' => ['required', new PhoneNumber()]
        ]);

        // 返回错误
        if ($validator->fails()) {
            return $this->failed($validator->errors()->first());
        }

        // 准备数据
        $action = $request->get('act', '');
        $client_id = $request->get('client', '');
        $captcha = $request->get('captcha', '');
        $mobile = $request->get('mobile');
        $is_mobile = $request->get('is_mobile', '');
        $send_from = $request->get('send_from', '');//兼容找回密码发送短信

        // 验证手机号是否被绑定
        if ($is_mobile) {
            $count = Users::where('mobile_phone', $mobile)->orWhere('user_name', $mobile)->count('user_id');
            if ($count > 0) {
                return $this->failed(trans('user.mobile_isbinded'));
            }
        }

        // 校验图片验证码
        if (Cache::get($client_id) != $captcha && empty($send_from)) {
            return $this->failed(trans('user.bind_captcha_error'));
        }

        // 限制发送速率 60s
        $frequency = 60;
        if (Cache::get($mobile . '_send_frequency') > (time() - $frequency)) {
            return $this->failed(trans('user.send_wait'));
        }

        // 获取验证码
        $sms_code = mt_rand(100000, 999999);

        // 设置短信模板数据
        $send_time = 'sms_code';
        $message = ['code' => $sms_code];

        // 新用户注册
        if ($action === 'register') {
            $send_time = 'sms_signin';
            $message['product'] = config('shop.shop_name');
        }

        // 发送短信
        $res = $this->send($mobile, $message, $send_time);
        if ($res === true) {
            $result = [
                "status" => "success",
                "result" => [
                    "msg" => trans('user.send_success'),
                ]
            ];
        } else {
            $result = ["status" => "fail"];
        }

        // 校验发送
        if ($result['status'] === 'success') {
            // 验证码有效期 默认且不超过10分钟
            $expiry = (int)config('shop.sms_validity', 10);
            $expiry = ($expiry > 10 || empty($expiry)) ? 10 : $expiry;
            Cache::put($client_id . $mobile, $sms_code, Carbon::now()->addMinutes($expiry));
            // 限制发送速率
            Cache::put($mobile . '_send_frequency', time(), Carbon::now()->addSeconds($frequency));

            return $this->succeed($result);
        } else {
            return $this->failed(trans('user.send_fail'));
        }
    }

    /**
     * 短信验证码校验
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function verify(Request $request)
    {
        if ($this->verifySMS($request)) {
            return $this->succeed('ok');
        } else {
            return $this->failed(trans('user.bind_mobile_code_error'));
        }
    }

    /**
     * 发送短信
     * @param string $mobile 接收手机号码
     * @param string $content 发送短信的内容数据
     * @param string $send_time 发送内容模板时机标记
     * @return bool
     * @throws Exception
     */
    protected function send($mobile = '', $content = '', $send_time = '')
    {
        return $this->commonRepository->smsSend($mobile, $content, $send_time, false);
    }
}
