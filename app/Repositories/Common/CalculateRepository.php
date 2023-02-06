<?php

namespace App\Repositories\Common;

use App\Kernel\Repositories\Common\CalculateRepository as Base;

/**
 * Class CalculateRepository
 * @method static math_add($a = 0, $b = 0, $scale = 6) 精确加法
 * @method static math_sub($a = 0, $b = 0, $scale = 6) 精确减法
 * @method static math_mul($a = 0, $b = 0, $scale = 6) 精确乘法
 * @method static math_div($a = 0, $b = 0, $scale = 6) 精确除法
 * @method static math_mod($a = 0, $b = 0) 精确求余/取模
 * @method static math_comp($a = 0, $b = 0, $scale = 6) 比较大小[注明：大于 返回 1 等于返回 0 小于返回 -1]
 * @method static math_pow($x, $y, $scale = 6) 高精确度数字次方值[注明：高精确度数字 x 的 y 次方]
 * @method static math_sqrt($val, $scale = 6) 任意精度数字的二次方根
 * @package App\Repositories\Common
 */
class CalculateRepository extends Base
{

}