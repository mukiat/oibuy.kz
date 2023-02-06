<?php

namespace App\Http\Controllers;

use App\Kernel\Http\Controllers\InitController as Base;
use App\Repositories\Common\SessionRepository;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

/**
 * Class InitController
 * @method checkReferer() 判断地址来源，false 非原地址来源则返回商城首页或提示报错
 * @method warehouseId() 仓库ID
 * @method areaId() 仓库地区
 * @method areaCity() 仓库地区区县
 * @method config() 系统配置
 * @method assign($name, $value) 模板赋值
 * @package App\Http\Controllers
 */
class InitController extends Base
{
    /**
     * 加载模板和页面输出 可以返回输出内容
     * @param string $name
     * @param string $cache_id
     * @return Factory|View
     */
    protected function display($name = '', $cache_id = '')
    {
        $ttl = config('app.debug') ? 0 : Carbon::now()->addHours(1);

        $name = str_replace('.dwt', '', $name);
        $name = 'themes.' . config('shop.template') . '.' . $name;

        $cache_id = !empty($cache_id) ? $cache_id : md5(serialize(request()->all())) . SessionRepository::realCartMacIp();

        return Cache::remember('view::' . $name . $cache_id, $ttl, function () use ($name) {
            return parent::display($name)->render();
        });
    }
}
