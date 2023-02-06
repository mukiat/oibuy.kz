<?php

namespace App\Modules\Admin\Controllers;

use App\Repositories\Common\DscRepository;
use App\Services\Navigator\TouchNavManageService;
use Illuminate\Http\Request;

/**
 * Class TouchNavController
 * @package App\Modules\Admin\Controllers
 */
class TouchNavController extends BaseController
{
    protected $device = 'h5'; // 客户端 h5、wxapp、app
    protected $nav_parent_num = 100; // 显示工具栏分类数量

    protected $dscRepository;
    protected $touchNavManageService;

    public function __construct(
        DscRepository $dscRepository,
        TouchNavManageService $touchNavManageService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->touchNavManageService = $touchNavManageService;

        $this->device = $device = request()->input('device', 'h5');

        // 权限控制
        if ($device == 'h5') {
            $this->middleware('admin_priv:touch_nav_admin');
        } elseif ($device == 'wxapp') {
            $this->middleware('admin_priv:wxapp_touch_nav_admin');
        } elseif ($device == 'app') {
            $this->middleware('admin_priv:app_touch_nav_admin');
        }
    }

    protected function initialize()
    {
        // 初始化 每页分页数量
        $this->init_params();

        $this->assign('device', $this->device);
    }

    /**
     * 工具栏列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $parent_id = $request->input('parent_id', 0);

        // 搜索
        $keywords = e($request->input('keywords', ''));

        // 排序
        $sort_order = e($request->input('sort_order', 'DESC')); // 降序/升序
        $sort_by = e($request->input('sort_by', '')); // 排序字段

        $filter['sort_order'] = $sort_order;
        $filter['sort_by'] = $sort_by;
        $filter['device'] = $this->device;
        $filter['keywords'] = $keywords;
        $offset = $this->pageLimit(route('admin/touch_nav/index', $filter), $this->page_num);

        $result = $this->touchNavManageService->getList($this->device, $parent_id, $offset, $filter);

        $total = $result['total'] ?? 0;
        $list = $result['list'] ?? [];

        $page = $this->pageShow($total);
        $this->assign('page', $page);
        $this->assign('list', $list);
        $this->assign('parent_id', $parent_id);

        // 筛选
        $filter['page_count'] = intval($page['page_count'] ?? 1);
        $filter['page_size'] = $this->page_num;
        $filter['page'] = $page['page'] ?? 1;
        $this->assign('filter', $filter);

        $is_ajax = $request->input('is_ajax');
        if ($is_ajax == 1) {
            $respond['content'] = $this->fetch('admin.touch_nav.library.touch_nav_query');
            $respond['filter'] = $filter;
            $respond['page_count'] = $page['page_count'] ?? 1;
            return response()->json($respond);
        }

        return $this->display('admin.touch_nav.index');
    }

    /**
     * 添加、编辑工具栏
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function edit(Request $request)
    {
        if ($request->isMethod('POST')) {
            $id = $request->input('id', 0);

            $data = $request->input('data', []);

            // 工具栏图标上传
            $file_path = $request->input('file_path', '');
            $pic = $request->file('pic');
            if ($pic && $pic->isValid()) {
                // 验证文件大小 200kb
                if ($pic->getSize() > 1024 * 200) {
                    return $this->message(trans('file.file_size_limit'), null, 2);
                }
                // 验证文件格式
                if (!in_array($pic->getClientMimeType(), ['image/jpeg', 'image/png'])) {
                    return $this->message(trans('file.not_file_type'), null, 2);
                }
                $result = $this->upload('data/attached/nav', true);
                if ($result['error'] > 0) {
                    return $this->message($result['message'], null, 2);
                }
                $data['pic'] = 'data/attached/nav/' . $result['file_name'];
            } else {
                $data['pic'] = $file_path;
            }

            // oss图片处理
            $file_arr = [
                'pic' => $data['pic'],
                'file_path' => $file_path,
            ];
            $file_arr = $this->dscRepository->transformOssFile($file_arr);
            $data['pic'] = $file_arr['pic'];

            if ($this->device == 'h5') {
                // h5端 http or https url 处理 去除本站域名、支持外链
                $data['url'] = $data['url'] ?? '';
                if (!empty($data['url']) && strtolower(substr($data['url'], 0, 4)) == 'http') {
                    $data['url'] = str_replace([dsc_url('/'), url('/')], '', $data['url']);
                }
            }

            if (!empty($id)) {

                // 删除原图片
                if (isset($data['pic']) && isset($file_path) && $data['pic'] && $file_path != $data['pic']) {
                    $this->remove($file_path);
                }

                // 编辑
                $this->touchNavManageService->updateNav($id, $data);

                return $this->message(trans('admin/common.edit_success'), route('admin/touch_nav/index', ['device' => $this->device]));
            } else {
                // 添加
                $res = $this->touchNavManageService->createNav($data);
                if ($res) {
                    return $this->message(trans('admin/common.add_success'), route('admin/touch_nav/index', ['device' => $this->device]));
                }
            }

            return $this->message(trans('admin/common.fail'), route('admin/touch_nav/index', ['device' => $this->device]));
        }

        $id = $request->input('id', 0);
        $parent_id = $request->input('parent_id', 0);

        $info = $this->touchNavManageService->navInfo($id);
        $this->assign('info', $info);

        // 选择工具栏分类
        $parent_nav = $this->touchNavManageService->parentNav($this->device, $this->nav_parent_num);
        $this->assign('parent_nav', $parent_nav);

        // 选择常用页面链接
        $device_url = $this->touchNavManageService->devicePageUrl();
        $this->assign('device_url', $device_url);

        $this->assign('parent_id', $parent_id);
        return $this->display('admin.touch_nav.edit');
    }

    /**
     * 修改 ajx异步
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        if ($request->isMethod('POST')) {

            $id = $request->input('id', 0);

            // 是否显示
            $ifshow = $request->input('ifshow');
            if (isset($ifshow)) {
                $data = [
                    'ifshow' => $ifshow
                ];
                $this->touchNavManageService->updateNav($id, $data);

                return response()->json(['error' => 0, 'msg' => trans('admin/common.success')]);
            }

            // 修改排序
            $vieworder = $request->input('vieworder');
            if (isset($vieworder)) {
                $data = [
                    'vieworder' => $vieworder
                ];
                $this->touchNavManageService->updateNav($id, $data);

                return response()->json(['error' => 0, 'msg' => trans('admin/common.success')]);
            }
        }
    }

    /**
     * 删除工具栏
     * @param Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $id = $request->input('id', 0);
        if (!empty($id)) {

            // 检查分类id 是否有子工具栏 如果有禁止删除
            $forbidden = $this->touchNavManageService->check($id);
            if ($forbidden == true) {
                return response()->json(['error' => 1, 'msg' => trans('admin/touch_nav.forbidden_delete')]);
            }

            $this->touchNavManageService->deleteNav($id);
        }

        if ($request->isMethod('POST')) {
            return response()->json(['error' => 0, 'msg' => trans('admin/common.success')]);
        }

        return back()->withInput(); // 返回
    }

    /**
     * 工具栏分类列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function nav_parent(Request $request)
    {
        // 排序
        $sort_order = e($request->input('sort_order', 'DESC')); // 降序/升序
        $sort_by = e($request->input('sort_by', '')); // 排序字段

        $filter['sort_order'] = $sort_order;
        $filter['sort_by'] = $sort_by;
        $filter['device'] = $this->device;
        $offset = $this->pageLimit(route('admin/touch_nav/nav_parent', $filter), $this->nav_parent_num);

        $result = $this->touchNavManageService->getParentList($this->device, $offset, $filter);

        $total = $result['total'] ?? 0;
        $list = $result['list'] ?? [];

        $page = $this->pageShow($total);
        $this->assign('page', $page);
        $this->assign('list', $list);
        // 筛选
        $filter['page_count'] = intval($page['page_count'] ?? 1);
        $filter['page_size'] = $this->page_num;
        $filter['page'] = $page['page'] ?? 1;
        $this->assign('filter', $filter);

        $is_ajax = $request->input('is_ajax');
        if ($is_ajax == 1) {
            $respond['content'] = $this->fetch('admin.touch_nav.library.nav_parent_query');
            $respond['filter'] = $filter;
            $respond['page_count'] = $page['page_count'] ?? 1;
            return response()->json($respond);
        }

        return $this->display('admin.touch_nav.nav_parent');
    }

    /**
     * 添加、编辑工具栏分类
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function nav_parent_edit(Request $request)
    {
        if ($request->isMethod('POST')) {
            $id = $request->input('id', 0);
            $data = $request->input('data', []);

            if (!empty($id)) {
                // 编辑
                $this->touchNavManageService->updateNav($id, $data);

                return response()->json(['error' => 0, 'msg' => trans('admin/common.edit_success')]);
            } else {
                // 添加
                $res = $this->touchNavManageService->createNav($data);
                if ($res) {
                    return response()->json(['error' => 0, 'msg' => trans('admin/common.add_success')]);
                }
            }

            return response()->json(['error' => 1, 'msg' => trans('admin/common.fail')]);
        }

        $id = $request->input('id', 0);

        $info = $this->touchNavManageService->navInfo($id);
        $this->assign('info', $info);
        return $this->display('admin.touch_nav.nav_parent_edit');
    }


}
