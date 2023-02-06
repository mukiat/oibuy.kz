<?php

namespace App\Http\Requests;

use App\Exceptions\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest as Request;

class FormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * 验证失败自定义跳转
     *
     * @param  \Illuminate\Contracts\Validation\Validator $validator
     * @throws ValidationException
     */
    public function failedValidation(Validator $validator)
    {
        if ($this->is(ADMIN_PATH . '/*')) {
            // 平台后台
            $redirectUrl = route('admin/base/message');
        } elseif ($this->is(SELLER_PATH . '/*')) {
            // 商家后台
            $redirectUrl = route('seller/base/message');
        } else {
            // 默认返回上一页
            $redirectUrl = $this->redirector->back();
        }

        throw (new ValidationException($validator))
            ->status(2)
            ->redirectTo($redirectUrl);
    }
}
