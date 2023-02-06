<?php

namespace App\Api\Foundation\Controllers;

use App\Api\Foundation\Components\ApiResponse;
use App\Api\Foundation\Components\HttpResponse;
use App\Http\Controllers\Controller as BaseController;
use App\Libraries\Error;
use App\Libraries\Mysql;
use App\Libraries\Shop;
use App\Rules\PhoneNumber;
use App\Services\Common\AreaService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class Controller
 * @package App\Api\Foundation\Controllers
 */
class Controller extends BaseController
{
    use HttpResponse, ApiResponse;

    /**
     * 地区-省份
     *
     * @var int
     */
    protected $province_id = 0;

    /**
     * 地区-城市
     *
     * @var int
     */
    protected $city_id = 0;

    /**
     * 地区-区县
     *
     * @var int
     */
    protected $district_id = 0;

    /**
     * 仓库
     *
     * @var int
     */
    protected $warehouse_id = 0;

    /**
     * 仓库-省份
     *
     * @var int
     */
    protected $area_id = 0;

    /**
     * 仓库-城市
     *
     * @var int
     */
    protected $area_city = 0;

    /**
     * 登录会员ID
     *
     * @var int
     */
    protected $uid = 0;

    protected function initialize()
    {
        if (!isset($GLOBALS['_CFG'])) {
            load_helper([
                'time', 'base', 'common', 'main', 'insert', 'goods', 'article',
                'ecmoban', 'function', 'seller_store', 'scws', 'wholesale', 'passport'
            ]);

            $GLOBALS['_CFG'] = config('shop');
        }

        if (!isset($GLOBALS['_LANG'])) {
            load_lang(['common', 'js_languages', 'user', 'shopping_flow']);
        }

        $GLOBALS['dsc'] = app(Shop::class);
        $GLOBALS['db'] = app(Mysql::class);
        $GLOBALS['err'] = app(Error::class);

        defined('SESS_ID') or define('SESS_ID', session()->getId());
        if (function_exists('init_users')) {
            $GLOBALS['user'] = init_users();
        }

        /* 登录会员ID */
        $this->uid = $this->authorization();

        $area_cache_name = app(AreaService::class)->getCacheName('area_cookie', $this->uid);

        $area_cookie_list = cache($area_cache_name);
        $area_cookie_list = is_null($area_cookie_list) ? false : $area_cookie_list;

        #需要查询的IP start
        if (!isset($area_cookie_list['province']) || empty($area_cookie_list['province'])) {
            $areaInfo = app(AreaService::class)->selectAreaInfo();

            $this->province_id = $areaInfo['province_id'];
            $this->city_id = $areaInfo['city_id'];
            $this->district_id = $areaInfo['district_id'];

            if ($area_cookie_list === false) {
                $area_cookie_cache = [
                    'province' => $this->province_id,
                    'city_id' => $this->city_id,
                    'district' => $this->district_id,
                    'street' => 0,
                    'street_area' => 0
                ];

                cache()->forever($area_cache_name, $area_cookie_cache);
            }
        } else {
            $this->province_id = $area_cookie_list['province'];
            $this->city_id = $area_cookie_list['city_id'];
            $this->district_id = $area_cookie_list['district'];
        }
        #需要查询的IP end

        /**
         * Start
         *
         * @param $warehouse_id 仓库ID
         * @param $area_id 省份ID
         * @param $area_city 城市ID
         */
        $warehouse_cache_name = app(AreaService::class)->getCacheName('warehouse_cookie', $this->uid);
        $warehouse_cookie_list = cache($warehouse_cache_name);

        if (is_null($warehouse_cookie_list)) {
            $areaOther = [
                'province_id' => $this->province_id,
                'city_id' => $this->city_id
            ];

            $areaInfo = app(AreaService::class)->getAreaInfo($areaOther, $this->uid);

            $this->warehouse_id = $areaInfo['area']['warehouse_id'];
            $this->area_id = $areaInfo['area']['area_id'];
            $this->area_city = $areaInfo['area']['city_id'];
        } else {
            $this->warehouse_id = $warehouse_cookie_list['warehouse_id'];
            $this->area_id = $warehouse_cookie_list['area_id'];
            $this->area_city = $warehouse_cookie_list['area_city'];
        }
        /* End */
    }

    /**
     * 短信验证码校验
     * @param Request $request
     * @return bool
     * @throws ValidationException
     */
    protected function verifySMS(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'client' => 'required',
            'mobile' => ['required', new PhoneNumber()],
            'code' => 'required',
        ], [
            'client.required' => trans('user.bind_captcha_null'),
            'mobile.required' => trans('user.bind_mobile_null'),
            'code.required' => trans('user.bind_mobile_code_null'),
        ]);
        // 返回错误
        if ($validator->fails()) {
            Log::error($validator->errors()->first());
            return false;
        }

        $client_id = $request->get('client', '');
        $mobile = $request->get('mobile');
        $sms_code = $request->get('code');

        $label = $client_id . $mobile;

        // 记录错误次数
        $errorNum = Cache::get($label . 'error', 0);

        // 错误验证码且超过3次，直接返回错误
        if ((Cache::get($label) != $sms_code) || $errorNum > 3) {
            Cache::put($label . 'error', $errorNum + 1, Carbon::now()->addMinutes(1));
            return false;
        } else {
            return true;
        }
    }

    /**
     * @return array
     */
    protected function getAction()
    {
        $name = request()->route()->getActionName();
        $actions = explode('\\', $name);
        list($controller, $action) = explode('@', end($actions));
        return [
            'controller' => $controller,
            'action' => $action,
            'script_name' => parse_name(substr($controller, 0, -10)) . '.php',
        ];
    }
}
