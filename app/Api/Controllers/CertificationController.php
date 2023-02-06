<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Rules\BankCard;
use App\Rules\BankName;
use App\Rules\IdCardNumber;
use App\Rules\PhoneNumber;
use App\Rules\RealName;
use App\Services\User\VerifyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class CertificationController
 * @package App\Api\Controllers
 */
class CertificationController extends Controller
{
    protected $verifyService;

    public function __construct(
        VerifyService $verifyService
    )
    {
        $this->verifyService = $verifyService;
    }

    /**
     * 个人实名认证详情
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request)
    {
        //返回用户ID
        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(trans('user.not_login'));
        }

        //实名认证详情
        $verify_info = $this->verifyService->infoVerify($user_id);

        return $this->succeed($verify_info);
    }

    /**
     * 新增个人实名认证
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        //数据验证
        $message = [
            'real_name.required' => trans('user.Real_name_null'),
            'bank_mobile.required' => trans('user.bank_mobile_null'),
            'bank_name.required' => trans('user.bank_name_null'),
            'bank_card.required' => trans('user.bank_card_null'),
            'self_num.required' => trans('user.self_num_null'),
        ];
        $validator = Validator::make($request->all(), [
            'real_name' => ['required', 'string', new RealName()],
            'bank_mobile' => ['required', 'string', new PhoneNumber()],
            'bank_name' => ['required', 'string', new BankName()],
            'bank_card' => ['required', 'string', new BankCard()],
            'self_num' => ['required', 'string', new IdCardNumber()],
            'front_of_id_card' => 'required|string',
            'reverse_of_id_card' => 'required|string',
        ], $message);

        // 返回错误
        if ($validator->fails()) {
            return $this->setErrorCode(422)->failed($validator->errors()->first());
        }

        //返回用户ID
        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(trans('user.not_login'));
        }

        $info = $request->all();

        $info['user_id'] = $user_id;

        // 新增个人实名认证
        $verify = $this->verifyService->updateVerify($info);

        return $this->succeed($verify);
    }

    /**
     * 更新个人实名认证
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request)
    {
        //数据验证
        $message = [
            'real_name.required' => trans('user.Real_name_null'),
            'bank_mobile.required' => trans('user.bank_mobile_null'),
            'bank_name.required' => trans('user.bank_name_null'),
            'bank_card.required' => trans('user.bank_card_null'),
            'self_num.required' => trans('user.self_num_null'),
        ];
        $validator = Validator::make($request->all(), [
            'real_id' => 'required|integer',
            'real_name' => ['required', 'string', new RealName()],
            'bank_mobile' => ['required', 'string', new PhoneNumber()],
            'bank_name' => ['required', 'string', new BankName()],
            'bank_card' => ['required', 'string', new BankCard()],
            'self_num' => ['required', 'string', new IdCardNumber()],
            'front_of_id_card' => 'required|string',
            'reverse_of_id_card' => 'required|string',
        ], $message);

        // 返回错误
        if ($validator->fails()) {
            return $this->setErrorCode(422)->failed($validator->errors()->first());
        }

        //返回用户ID
        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(trans('user.not_login'));
        }

        $info = $request->all();

        $info['user_id'] = $user_id;

        // 更新个人实名认证
        $verify = $this->verifyService->updateVerify($info);

        return $this->succeed($verify);
    }
}
