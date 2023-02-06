<?php

namespace App\Modules\Admin\Controllers;

use App\Api\Foundation\Components\HttpResponse;
use App\Kernel\Modules\Admin\Controllers\InitController as Base;

/**
 * Class InitController
 * @package App\Modules\Admin\Controllers
 * @method checkReferer() 判断地址来源，false 非原地址来源则返回商城首页或提示报错
 */
class InitController extends Base
{
    use HttpResponse;

    /**
     * 获取当前模块名
     *
     * @return string
     */
    protected function getCurrentModuleName()
    {
        return $this->getCurrentAction()['module'];
    }

    /**
     * 获取当前控制器名
     *
     * @return string
     */
    protected function getCurrentControllerName()
    {
        return $this->getCurrentAction()['controller'];
    }

    /**
     * 获取当前方法名
     *
     * @return string
     */
    protected function getCurrentMethodName()
    {
        return $this->getCurrentAction()['method'];
    }

    /**
     * 获取当前控制器与方法
     *
     * @return array
     */
    protected function getCurrentAction()
    {
        return parent::getCurrentAction();
    }

    /**
     * 模板变量赋值
     *
     * @param $name
     * @param string $value
     * @return mixed
     */
    protected function assign($name, $value = '')
    {
        return parent::assign($name, $value);
    }

    /**
     * 加载模板和页面输出 可以返回输出内容
     * @param string $filename
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    protected function display($filename = '')
    {
        return parent::display($filename);
    }

    /**
     * 异步加载blade模板
     * @param string $filename
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    protected function fetch($filename = '')
    {
        return parent::fetch($filename);
    }

    /**
     * 获取分页查询limit
     * @param $url
     * @param int $num
     * @return array
     */
    protected function pageLimit($url, $num = 10)
    {
        return parent::pageLimit($url, $num);
    }

    /**
     * 分页结果显示
     */
    protected function pageShow($count)
    {
        return parent::pageShow($count);
    }
}
