<?php

namespace App\Modules\Admin\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Class TouchPageNavController
 * @package App\Modules\Admin\Controllers
 */
class TouchPageNavController extends BaseController
{
    protected $device = '';

    public function __construct()
    {

        $this->device = $device = request()->input('device', 'h5');

        // 权限控制
        if ($device == 'h5') {
            $this->middleware('admin_priv:touch_page_nav');
        } elseif ($device == 'wxapp') {
            $this->middleware('admin_priv:wxapp_touch_page_nav');
        } elseif ($device == 'app') {
            $this->middleware('admin_priv:app_touch_page_nav');
        }
    }

    /**
     * 视图显示
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $device = $request->get('device', 'h5'); // 客户端 h5、wxapp、app

        $this->assign('device', $device);

        return $this->display('admin.touch_page_nav.index');
    }

    /**
     * 获取菜单数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function modules(Request $request)
    {
        $device = $request->get('device', 'h5'); // 客户端 h5、wxapp、app
        $page = $request->get('page', 'discover');

        return $this->succeed($this->getAllModules($device, $page));
    }

    /**
     * 更新菜单状态
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $id = $request->get('id', '0');
        $sort = $request->get('sort', '0');
        $display = $request->get('display', '0');

        if ($this->updateModule($id, $sort, $display) > 0) {
            Cache::forget('touch_page_nav');
            return $this->succeed('ok');
        } else {
            return $this->failed('fail');
        }
    }

    /**
     * 更新菜单排序
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resort(Request $request)
    {
        $items = $request->get('sort');

        foreach ($items as $sort => $id) {
            $this->updateModule($id, $sort, 1);
        }

        Cache::forget('touch_page_nav');
        return $this->succeed('ok');
    }

    /**
     * 获取模块数据
     * @param string $device 设备类型
     * @param string $page 页面
     * @return array
     */
    private function getAllModules($device = '', $page = '')
    {
        $modules = DB::table('touch_page_nav')
            ->where('ru_id', $this->ru_id)
            ->where('device', $device)
            ->where('page_name', $page)
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        return collect($modules)->toArray();
    }

    /**
     * 更新模块
     * @param int $id 模块菜单ID
     * @param int $sort 排序
     * @param int $display 是否显示
     * @return int
     */
    private function updateModule($id, $sort, $display)
    {
        return DB::table('touch_page_nav')
            ->where('id', $id)
            ->where('ru_id', $this->ru_id)
            ->update([
                'sort' => $sort,
                'display' => $display,
            ]);
    }
}
