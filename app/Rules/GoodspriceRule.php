<?php


namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;


class GoodspriceRule implements Rule
{
    protected $price_format;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->price_format = config('shop.price_format', 0);  // 0保留2位小数, 2保留1位小数, 3不保留小数
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($this->price_format == 3) {
            return preg_match("/^\d+$/", $value) ? true : false;
        }

        if ($this->price_format == 2) {

            return preg_match("/^[0-9]+(.[0-9]{1})?$/", $value) ? true : false;
        }

        if ($this->price_format == 0) {
            return preg_match("/^[0-9]+(.[0-9]{1,2})?$/", $value) ? true : false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ($this->price_format == 3) {
            return __('admin::goods.js_languages.price_format_not_decimal');
        }

        if ($this->price_format == 2) {
            return __('admin::goods.js_languages.price_format_support_1_decimal');
        }

        if ($this->price_format == 0) {
            return __('admin::goods.js_languages.price_format_support_2_decimal');
        }
    }

}
