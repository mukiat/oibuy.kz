<?php

namespace App\Modules\Admin\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class ExpressManageController
 * @package App\Modules\Admin\Controllers
 */
class ExpressManageController extends BaseController
{
    public function initialize()
    {
        parent::initialize();

        $this->init_params();
    }

    /**
     * @param Request $request
     * @return string
     */
    public function index(Request $request)
    {
        $act = $request->get('act', 'company');

        if ($act == 'company') {
            $filter['act'] = $act;
            $filter['status'] = e($request->input('status', '-1'));
            $filter['keywords'] = e($request->input('keywords', ''));

            $offset = $this->pageLimit(route('admin.express_manage', $filter), $this->page_num);

            // 获取默认的快递类型
            $type = DB::table('express')->where('default', 1)->value('code');

            $result = DB::table('express_company')->where('type', $type);
            if (in_array($filter['status'], [0, 1])) {
                $result = $result->where('status', $filter['status']);
            }
            if (!empty($filter['keywords'])) {
                $result = $result->where('name', 'like', "%{$filter['keywords']}%");
            }
            $result = $result->orderByDesc('status')->orderBy('id')->paginate($offset['limit']);

            $page = $this->pageShow($result->total());
            $this->assign('page', $page);
            $this->assign('list', $result);
            $this->assign('filter', $filter);

            $is_ajax = $request->input('is_ajax');
            if ($is_ajax == 1) {
                $respond['content'] = $this->fetch('admin.express_manage.company_list');
                $respond['filter'] = $filter;
                $respond['page_count'] = $page['page_count'] ?? 1;
                return response()->json($respond);
            }

            return $this->display('admin.express_manage.company');
        }

        if ($act == 'company_toggle') {
            $id = $request->input('id', 0);
            $status = $request->input('status', 0);

            $count = DB::table('express_company');
            if (is_array($id)) {
                $count = $count->whereIn('id', $id);
            } else {
                $status = empty($status) ? 1 : 0;
                $count = $count->where('id', $id);
            }
            $count = $count->update(['status' => $status]);

            if ($count > 0) {
                $result = [
                    'error' => 0,
                    'msg' => trans('admin/common.success')
                ];
            } else {
                $result = [
                    'error' => 1,
                    'msg' => trans('admin/common.fail')
                ];
            }

            return response()->json($result);
        }

        if ($act == 'history') {
            $filter['act'] = $act;
            $filter['keywords'] = e($request->input('keywords', ''));

            $offset = $this->pageLimit(route('admin.express_manage', $filter), $this->page_num);

            // 获取列表数据
            $result = DB::table('express_history');
            if (!empty($filter['keywords'])) {
                $result = $result->where('shop_name', 'like', "%{$filter['keywords']}%")
                    ->orWhere('order_sn', 'like', "%{$filter['keywords']}%")
                    ->orWhere('ship_sn', 'like', "%{$filter['keywords']}%")
                    ->orWhere('express_name', 'like', "%{$filter['keywords']}%")
                    ->orWhere('express_sn', 'like', "%{$filter['keywords']}%");
            }
            $result = $result->orderByDesc('id')->paginate($offset['limit']);

            $page = $this->pageShow($result->total());
            $this->assign('page', $page);
            $this->assign('list', $result);
            $this->assign('filter', $filter);

            $is_ajax = $request->input('is_ajax');
            if ($is_ajax == 1) {
                $respond['content'] = $this->fetch('admin.express_manage.history_list');
                $respond['filter'] = $filter;
                $respond['page_count'] = $page['page_count'] ?? 1;
                return response()->json($respond);
            }

            return $this->display('admin.express_manage.history');
        }
    }
}
