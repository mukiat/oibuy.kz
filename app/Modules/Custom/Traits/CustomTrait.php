<?php

namespace App\Modules\Custom\Traits;

use App\Repositories\Common\StrRepository;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\View;
use Throwable;

trait CustomTrait
{
    /**
     * 模板输出变量
     *
     * @var array
     */
    protected $tVar = [];

    // 分页数量
    protected $page_num = 10;

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
     * 获取当前路由属于哪个后台
     *
     * @return string
     */
    protected function getCurrentManageName()
    {
        return $this->getCurrentAction()['manage'];
    }

    /**
     * 获取当前控制器 所在模块与方法
     *
     * @return array
     */
    protected function getCurrentAction()
    {
        $action = request()->route()->getAction();

        $namespace = explode('\\', $action['namespace']);
        $namespace = array_pad($namespace, 5, '');
        list($app, $module_path, $module, $controllers, $manage) = $namespace;

        $action = str_replace($action['namespace'] . '\\', '', $action['controller']);

        list($controller, $method) = explode('@', $action);

        return ['module' => $module, 'controller' => StrRepository::studly($controller), 'method' => $method, 'manage' => $manage];
    }

    /**
     * 显示开发模块模板
     * @param string $tpl
     * @return Factory|View|string
     */
    protected function display($tpl = null)
    {
        if (!is_null($tpl)) {
            return view($tpl, $this->tVar);
        }

        $action = $this->getCurrentAction();

        // 当前主模块
        $module = StrRepository::snake($action['module']);
        // 子模块
        $manage = StrRepository::snake($action['manage']);

        // 控制器
        $controller = str_replace('Controller', '', $action['controller']);
        // 方法名
        $method = str_replace('action', '', $action['method']);

        // 自定义模板
        $tpl = StrRepository::snake($controller . '.' . $method);

        if (!empty($manage)) {
            $tpl = $manage . '.' . $tpl;
        }

        return view($module . '::' . $tpl, $this->tVar);
    }

    /**
     * 异步加载开发blade模板
     * @param null $tpl
     * @return array|string
     * @throws Throwable
     */
    protected function fetch($tpl = null)
    {
        if (!is_null($tpl)) {
            return view($tpl, $this->tVar)->render();
        }

        $action = $this->getCurrentAction();

        // 当前主模块
        $module = StrRepository::snake($action['module']);
        // 子模块
        $manage = StrRepository::snake($action['manage']);

        // 控制器
        $controller = str_replace('Controller', '', $action['controller']);
        // 方法名
        $method = str_replace('action', '', $action['method']);

        // 自定义模板
        $tpl = StrRepository::snake($controller . '.' . $method);

        if (!empty($manage)) {
            $tpl = $manage . '.' . $tpl;
        }

        return view($module . '::' . $tpl, $this->tVar)->render();
    }


    /**
     * 加载当前模块下函数库
     * @param array $files
     * @param array $module
     */
    protected function load_helper($files = [], $module = null)
    {
        if (is_null($module)) {
            // 当前模块名
            $module = StrRepository::studly($this->getCurrentModuleName());
        }

        if (!is_array($files)) {
            $files = [$files];
        }

        $base_path = app_path('Modules/' . $module . '/Support/');

        foreach ($files as $vo) {
            $helper = $base_path . $vo . '.php';
            if (file_exists($helper)) {
                require_once $helper;
            }
        }
    }

    /**
     * 消息提示跳转页
     * @return Factory|View|string
     */
    protected function message()
    {
        $url = null;
        $type = '1';
        $seller = false;
        $waitSecond = 2;
        if (func_num_args() === 0) {
            $msg = request()->session()->get('msg', '');
            $type = request()->session()->get('type', 1);
            $url = request()->session()->get('url', null);
        } else {
            $argments = func_get_args();

            $msg = isset($argments['0']) ? $argments['0'] : '';
            $url = isset($argments['1']) ? $argments['1'] : $url;
            $type = isset($argments['2']) ? $argments['2'] : $type;
            $seller = isset($argments['3']) ? $argments['3'] : $seller;
            $waitSecond = isset($argments['4']) ? $argments['4'] : $waitSecond;
        }

        if (is_null($url)) {
            $url = 'javascript:history.back();';
        }
        if ($type == '2') {
            $title = lang('error_information');
        } else {
            $title = lang('prompt_information');
        }

        $data = [
            'title' => $title,
            'message' => $msg,
            'type' => $type,
            'url' => $url,
            'second' => $waitSecond,
        ];
        $this->assign('data', $data);

        $tpl = ($seller == true) ? 'seller/base.seller_message' : 'admin/base.message';
        return $this->display($tpl);
    }

    /**
     * 处理分页参数
     */
    protected function init_params()
    {
        $page_num = request()->cookie('page_size');
        $this->page_num = is_null($page_num) ? 10 : $page_num;
        $this->assign('page_num', $this->page_num);
    }

    /**
     * 根据过滤条件获得排序的标记
     *
     * @param array $filter
     * @param string $sort_by
     * @return  array
     */
    protected static function sort_flag($filter, $sort_by = '')
    {
        $filter['sort_by'] = isset($filter['sort_by']) && !empty($filter['sort_by']) ? $filter['sort_by'] : $sort_by;
        $filter['sort_order'] = isset($filter['sort_order']) ? $filter['sort_order'] : '';
        $flag['tag'] = 'sort_' . preg_replace('/^.*\./', '', $filter['sort_by']);
        $flag['img'] = asset('assets/admin/images/' . ($filter['sort_order'] == "DESC" ? 'sort_desc.gif' : 'sort_asc.gif'));

        return $flag;
    }

}
