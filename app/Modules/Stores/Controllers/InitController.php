<?php

namespace App\Modules\Stores\Controllers;

use App\Api\Foundation\Components\HttpResponse;
use App\Kernel\Modules\Stores\Controllers\InitController as Base;

/**
 * Class InitController
 * @package App\Modules\Stores\Controllers
 * @method checkReferer() 判断地址来源，false 非原地址来源则返回商城首页或提示报错
 */
class InitController extends Base
{
    use HttpResponse;
}
