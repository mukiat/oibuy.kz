<?php

namespace App\Http\Controllers;

use App\Extensions\File;
use App\Libraries\Page;
use App\Repositories\Common\StrRepository;
use App\Repositories\Common\TimeRepository;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 模板输出变量
     *
     * @var array
     */
    protected $tVar = [];

    protected $pager = [];

    /**
     * Execute an action on the controller.
     *
     * @param string $method
     * @param array $parameters
     * @return Response
     */
    public function callAction($method, $parameters)
    {
        if (method_exists($this, 'initialize')) {
            $response = call_user_func([$this, 'initialize']);
            if (!is_null($response)) {
                return $response;
            }
        }

        return call_user_func_array([$this, $method], $parameters);
    }

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
        $action = request()->route()->getAction();

        list($app, $module_path, $module_name) = explode('\\', $action['namespace']);

        $action = str_replace($action['namespace'] . '\\', '', $action['controller']);

        $field = explode('\\', $action);

        if (count($field) > 1) {
            $actions = explode('\\', $action);
            $action = 'Http\\Controllers\\' . $actions[1];
        } else {
            $action = 'Http\\Controllers\\' . $action;
        }

        list($module, $_, $action) = explode('\\', $action);

        list($controller, $action) = explode('@', $action);

        if ($app && $module_path == 'Modules') {
            $module = $module_name; // 获取模块名
        }

        return ['module' => $module, 'controller' => StrRepository::studly($controller), 'method' => $action];
    }

    /**
     * 模板变量赋值
     *
     * @param $name
     * @param string $value
     */
    protected function assign($name, $value = '')
    {
        if (is_array($name)) {
            $this->tVar = array_merge($this->tVar, $name);
        } else {
            $this->tVar[$name] = $value;
        }
    }

    /**
     * 加载模板和页面输出 可以返回输出内容
     * @param string $filename
     * @return Factory|View
     */
    protected function display($filename = '')
    {
        if ($filename) {
            return view($filename, $this->tVar);
        }

        $path = strtolower($this->getCurrentModuleName());

        $controller = str_replace('Controller', '', $this->getCurrentControllerName());

        $method = strtolower(StrRepository::camel($this->getCurrentMethodName()));

        $file = $controller . '.' . str_replace('action', '', $method);

        $filename = $path . '.' . strtolower($file);

        return view($filename, $this->tVar);
    }

    /**
     * 异步加载blade模板
     * @param null $tpl
     * @return array|string
     */
    protected function fetch($tpl = null)
    {
        $action = $this->getCurrentAction();

        // 当前主模块
        $module = StrRepository::snake($action['module']);
        // 子模块
        $manage = !empty($action['manage']) ? StrRepository::snake($action['manage']) : '';
        // 控制器
        $controller = str_replace('Controller', '', $action['controller']);
        // 方法名
        $method = str_replace('action', '', $action['method']);

        // 设置视图
        View::addNamespace($module, app_path('Modules') . '/' . $action['module']);

        if (!is_null($tpl)) {
            return view($tpl, $this->tVar)->render();
        }

        // 默认模板
        $tpl = $tpl ? $tpl : StrRepository::snake($controller . '.' . $method);

        if (!empty($manage)) {
            $tpl = $manage . '.' . $tpl;
        }

        return view($module . '::' . $tpl, $this->tVar)->render();
    }

    /**
     * ECJia App 快捷登录
     * @desc $package = ['origin', 'usertype', 'openid', 'gmtime', 'sign']
     * @param Request $request
     * @return bool|string
     */
    protected function ecjiaLogin(Request $request)
    {
        if ($request->has('ecjiahash')) {
            $package = $request->get('ecjiahash');

            $data = dsc_decode(base64_decode($package), true);
            $sign = $data['sign'];
            unset($data['sign']);

            // 查询
            $user = DB::table('connect_user')
                ->leftJoin('users', 'users.user_id', '=', 'connect_user.user_id')
                ->where('connect_user.user_type', $data['usertype'])
                ->where('connect_user.open_id', $data['openid'])
                ->where('connect_code', $data['origin'])
                ->orderBy('id', 'DESC')
                ->select('connect_user.*', 'users.user_id', 'users.user_name')
                ->first();

            if (is_null($user)) {
                return false;
            }

            // 授权数据校验
            $data['token'] = collect($user)->get('access_token');
            ksort($data);

            $signed = hash_hmac('md5', http_build_query($data, '', '&'), collect($user)->get('refresh_token'));

            // 检测签名与过期时间5分钟
            if ($signed === $sign && TimeRepository::inGmTimeInterval($data['gmtime'], 5)) {
                return $user;
            }
        }

        return false;
    }

    /**
     * 获取分页查询limit
     * @param $url
     * @param int $num
     * @return array
     */
    protected function pageLimit($url, $num = 10)
    {
        $url = str_replace(urlencode('{page}'), '{page}', $url);
        $page = isset($this->pager['obj']) && is_object($this->pager['obj']) ? $this->pager['obj'] : app(Page::class);
        $cur_page = $page->getCurPage($url);
        $limit_start = ($cur_page - 1) * $num;
        $limit = $limit_start . ',' . $num;
        $this->pager = [
            'obj' => $page,
            'url' => $url,
            'num' => $num,
            'cur_page' => $cur_page,
            'limit' => $limit
        ];
        list($start, $pernum) = explode(',', $limit);
        return ['start' => $start, 'limit' => $pernum];
    }

    /*
     * 分页结果显示
     */
    protected function pageShow($count)
    {
        return $this->pager['obj']->show($this->pager['url'], $count, $this->pager['num']);
    }

    /**
     * 上传文件（可上传到本地服务器或OSS）
     * @param string $savePath
     * @param bool $hasOne
     * @param string $upload_name 指定上传 name 值
     * @param bool $isHasName 是否保留原文件名
     * @return array
     * @throws Exception
     */
    protected function upload($savePath = '', $hasOne = false, $upload_name = null, $isHasName = true)
    {
        return File::upload($savePath, $hasOne, $upload_name, $isHasName);
    }

    /**
     * 删除文件（可删除本地服务器文件或OSS文件）
     * @param string $file 相对路径 data/attached/article/pOFEQJ3wSab1vhsrCVr5k6eU2m7e1bQ7W16dcc14.jpeg
     * @param array $except 排除文件名数组
     * @return bool
     * @throws Exception
     */
    protected function remove($file = '', $except = ['no_image', 'errorImg'])
    {
        return File::remove($file, $except);
    }

    /**
     * 下载服务器文件到本地
     *
     * @param string $file
     * @return bool|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    protected function file_download($file = '')
    {
        return File::fileDownload($file);
    }

    /**
     * 附件镜像到阿里云OSS
     * @param string $file 文件相对路径 如 data/attend/1.jpg
     * @param bool $is_delete 是否要删除本地图片
     * @return string
     * @throws FileNotFoundException
     */
    protected function ossMirror($file = '', $is_delete = false)
    {
        return File::ossMirror($file, $is_delete);
    }

    /**
     * 同步上传服务器图片到OSS
     * @param array $file_list 图片列表 如 array('0'=>'data/attend/1.jpg', '1'=>'data/attend/2.png')
     * @param bool $is_delete 是否要删除本地图片
     * @return bool
     * @throws FileNotFoundException
     */
    protected function BatchUploadOss($file_list = [], $is_delete = false)
    {
        return File::batchUploadOss($file_list, $is_delete);
    }

    /**
     * 同步下载OSS图片到本地服务器
     * @param array $file_list 图片列表 如 array('0'=>'data/attend/1.jpg', '1'=>'data/attend/2.png')
     * @return bool
     * @throws FileNotFoundException
     */
    protected function BatchDownloadOss($file_list = [])
    {
        return File::batchDownloadOss($file_list);
    }
}
