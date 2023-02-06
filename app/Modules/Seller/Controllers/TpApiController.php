<?php

namespace App\Modules\Seller\Controllers;

use App\Models\OrderPrintSetting;
use App\Models\OrderPrintSize;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Message\TpApiManageService;
use Illuminate\Support\Facades\DB;

/**
 * 订单打印
 *
 * Class TpApiController
 * @package App\Modules\Seller\Controllers
 */
class TpApiController extends InitController
{
    protected $tpApiManageService;
    protected $dscRepository;

    public function __construct(
        TpApiManageService $tpApiManageService,
        DscRepository $dscRepository
    )
    {
        $this->tpApiManageService = $tpApiManageService;
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {
        $act = e(request()->input('act', ''));

        if (empty($act)) {
            return sys_msg('Hacking attempt', 1);
        }

        $adminru = get_admin_ru_id();

        //快递鸟打印
        if ($act == 'kdniao_print') {
            load_helper(['order', 'goods']);

            $order_id = (int)request()->input('order_id', 0);
            $order_sn = e(request()->input('order_sn', ''));

            $order_ids = [];
            if (!empty($order_id)) {
                $order_ids[] = $order_id;
            }

            if (!empty($order_sn)) {
                $order_sn = explode(',', $order_sn);
                $ids = DB::table('order_info')->whereIn('order_sn', $order_sn)->pluck('order_id')->toArray();
                $order_ids = array_merge($order_ids, $ids);
            }

            $link[] = array('text' => trans('common.close_window'), 'href' => 'javascript:window.close()');

            //判断订单
            if (empty($order_ids)) {
                return sys_msg(trans('admin::tp_api.not_select_order'), 1, $link);
            }

            //判断快递是否一样
            $shipping_ids = DB::table('order_info')->whereIn('order_id', $order_ids)->pluck('shipping_id')->toArray();
            $shipping_ids = array_unique($shipping_ids);
            if (count($shipping_ids) > 1) {
                return sys_msg($GLOBALS['_LANG']['select_express_same_batch_print'], 1, $link);
            }

            //处理数据
            $batch_html = [];
            $batch_error = [];
            if ($order_ids && $order_ids[0]) {
                $order_info = order_info($order_ids[0]);

                //识别快递
                $shipping_info = get_shipping_info($order_info['shipping_id'], $adminru['ru_id']);
                $shipping_spec = get_shipping_spec($shipping_info['shipping_code']);
                $GLOBALS['smarty']->assign('shipping_info', $shipping_info);
                $GLOBALS['smarty']->assign('shipping_spec', $shipping_spec);

                foreach ($order_ids as $order_id) {
                    $result = get_kdniao_print_content($order_id, $shipping_spec, $shipping_info);

                    //判断是否成功
                    if ($result["ResultCode"] != "100") {
                        $batch_error[] = $GLOBALS['_LANG']['04_order'] . "（" . $order_id . "）：" . $GLOBALS['_LANG']['dzmd_order_fail'] . "：{$result['Reason']}";
                        continue;
                    }

                    //输出打印模板
                    if (!empty($result['PrintTemplate'])) {
                        $batch_html[] = $result['PrintTemplate'];
                    } else {
                        $batch_error[] = $GLOBALS['_LANG']['04_order'] . "（" . $order_id . "）：" . $GLOBALS['_LANG']['no_print_tpl'];
                        continue;
                    }

                    //将物流单号填入系统
                    if (isset($result['Order']['LogisticCode'])) {
                        DB::table('order_info')->where('order_id', $order_id)->update(['invoice_no' => $result['Order']['LogisticCode']]);
                    }
                }
            }

            $this->smarty->assign('batch_html', $batch_html);
            $this->smarty->assign('batch_error', implode(',', $batch_error));

            $kdniao_printer = DB::table('seller_shopinfo')->where('ru_id', $adminru['ru_id'])->value('kdniao_printer');
            $kdniao_printer = $kdniao_printer ?? '';
            $this->smarty->assign('kdniao_printer', $kdniao_printer);

            return $this->smarty->display('kdniao_print.dwt');
        }

        /*------------------------------------------------------ */
        //-- 电子面单列表页面
        /*------------------------------------------------------ */
        if ($act == 'order_print_setting') {
            admin_priv('order_print_setting');
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['19_merchants_store']);
            $this->smarty->assign('menu_select', ['action' => '19_merchants_store', 'current' => 'order_print_setting']);

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['order_print_setting']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['order_print_setting_add'], 'href' => 'tp_api.php?act=order_print_setting_add']);
            $this->smarty->assign('full_page', 1);

            $print_setting = $this->get_order_print_setting($adminru['ru_id']);

            $this->smarty->assign('print_setting', $print_setting['list']);
            $this->smarty->assign('filter', $print_setting['filter']);
            $this->smarty->assign('record_count', $print_setting['record_count']);
            $this->smarty->assign('page_count', $print_setting['page_count']);

            $sort_flag = sort_flag($print_setting['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);


            return $this->smarty->display('order_print_setting.dwt');
        }

        /*------------------------------------------------------ */
        //-- 排序、分页、查询
        /*------------------------------------------------------ */
        elseif ($act == 'order_print_setting_query') {
            $check_auth = check_authz_json('order_print_setting');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $print_setting = $this->get_order_print_setting($adminru['ru_id']);

            $this->smarty->assign('print_setting', $print_setting['list']);
            $this->smarty->assign('filter', $print_setting['filter']);
            $this->smarty->assign('record_count', $print_setting['record_count']);
            $this->smarty->assign('page_count', $print_setting['page_count']);

            $sort_flag = sort_flag($print_setting['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result($this->smarty->fetch('order_print_setting.dwt'), '', ['filter' => $print_setting['filter'], 'page_count' => $print_setting['page_count']]);
        }

        /*------------------------------------------------------ */
        //-- 删除
        /*------------------------------------------------------ */
        elseif ($act == 'order_print_setting_remove') {
            $check_auth = check_authz_json('order_print_setting');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = (int)request()->input('id', 0);

            if (empty($id)) {
                return sys_msg('Hacking attempt', 1);
            }

            DB::table('order_print_setting')->where('id', $id)->where('ru_id', $adminru['ru_id'])->delete();

            $url = 'tp_api.php?act=order_print_setting_query&' . str_replace('act=order_print_setting_remove', '', request()->server('QUERY_STRING'));
            return dsc_header("Location: $url\n");
        }

        /*------------------------------------------------------ */
        //-- 编辑打印机
        /*------------------------------------------------------ */
        elseif ($act == 'edit_order_printer') {
            $check_auth = check_authz_json('order_print_setting');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = (int)request()->input('id', 0);
            $val = e(request()->input('val', ''));

            if (empty($id)) {
                return make_json_error('Hacking attempt');
            }

            DB::table('order_print_setting')->where('id', $id)->where('ru_id', $adminru['ru_id'])->update(['printer' => $val]);

            return make_json_result($val);
        }

        /*------------------------------------------------------ */
        //-- 编辑宽度
        /*------------------------------------------------------ */
        elseif ($act == 'edit_print_width') {
            $check_auth = check_authz_json('order_print_setting');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = (int)request()->input('id', 0);
            $val = e(request()->input('val', ''));

            if (empty($id)) {
                return make_json_error('Hacking attempt');
            }

            DB::table('order_print_setting')->where('id', $id)->where('ru_id', $adminru['ru_id'])->update(['width' => $val]);

            return make_json_result($val);
        }

        /*------------------------------------------------------ */
        //-- 编辑打印机排序
        /*------------------------------------------------------ */
        elseif ($act == 'edit_sort_order') {
            $check_auth = check_authz_json('order_print_setting');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = (int)request()->input('id', 0);
            $val = e(request()->input('val', ''));

            if (empty($id)) {
                return make_json_error('Hacking attempt');
            }

            DB::table('order_print_setting')->where('id', $id)->where('ru_id', $adminru['ru_id'])->update(['sort_order' => $val]);

            return make_json_result($val);
        }

        /*------------------------------------------------------ */
        //-- 切换默认
        /*------------------------------------------------------ */
        elseif ($act == 'toggle_order_is_default') {
            $check_auth = check_authz_json('order_print_setting');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = (int)request()->input('id', 0);
            $val = (int)request()->input('val', 0);

            if (empty($id)) {
                return make_json_error('Hacking attempt');
            }

            DB::table('order_print_setting')->where('id', $id)->where('ru_id', $adminru['ru_id'])->update(['is_default' => $val]);

            if ($val) {
                DB::table('order_print_setting')->where('id', '<>', $id)->where('ru_id', $adminru['ru_id'])->update(['is_default' => 0]);
            }

            return make_json_result($val);
        }

        /*------------------------------------------------------ */
        //-- 添加、编辑电子面单
        /*------------------------------------------------------ */
        elseif ($act == 'order_print_setting_add' || $act == 'order_print_setting_edit') {
            admin_priv('order_print_setting');
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['19_merchants_store']);
            $this->smarty->assign('menu_select', ['action' => '19_merchants_store', 'current' => 'order_print_setting']);

            $id = (int)request()->input('id', 0);

            $print_size = OrderPrintSize::whereRaw(1);
            $print_size = BaseRepository::getToArrayGet($print_size);
            $this->smarty->assign('print_size', $print_size);

            if ($id > 0) {
                $print_setting = OrderPrintSetting::where('id', $id);
                $print_setting = BaseRepository::getToArrayFirst($print_setting);

                $this->smarty->assign('print_setting', $print_setting);
                $this->smarty->assign('ur_here', $GLOBALS['_LANG']['order_print_setting_edit']);
                $this->smarty->assign('form_action', 'order_print_setting_update');
            } else {
                $this->smarty->assign('ur_here', $GLOBALS['_LANG']['order_print_setting_add']);
                $this->smarty->assign('form_action', 'order_print_setting_insert');
            }
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['order_print_setting'], 'href' => 'tp_api.php?act=order_print_setting']);

            return $this->smarty->display('order_print_setting_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 添加、编辑电子面单
        /*------------------------------------------------------ */
        elseif ($act == 'order_print_setting_insert' || $act == 'order_print_setting_update') {
            admin_priv('order_print_setting');

            $data = [];
            $id = (int)request()->input('id', 0);

            $data['ru_id'] = $adminru['ru_id'];
            $data['is_default'] = (int)request()->input('is_default', 0);
            $data['specification'] = e(request()->input('specification', ''));
            $data['printer'] = e(request()->input('printer', ''));
            $data['width'] = (int)request()->input('width', 0);

            if (empty($data['width'])) {
                $print_size = OrderPrintSize::where('specification', $data['specification'])->select('width');
                $print_size = BaseRepository::getToArrayFirst($print_size);
                $data['width'] = $print_size['width'] ?? 0;
            }

            /* 检查是否重复 */
            $is_only = DB::table('order_print_setting')->where('ru_id', $adminru['ru_id'])->where('id', '<>', $id)->where('specification', $data['specification'])->count('id');
            if (!empty($is_only)) {
                return sys_msg($GLOBALS['_LANG']['specification_exist'], 1);
            }
            /* 插入、更新 */
            if ($id > 0) {
                DB::table('order_print_setting')->where('id', $id)->where('ru_id', $adminru['ru_id'])->update($data);
                $msg = $GLOBALS['_LANG']['edit_success'];
            } else {
                $id = DB::table('order_print_setting')->insertGetId($data);
                $msg = $GLOBALS['_LANG']['add_success'];
            }

            /* 默认设置 */
            if ($data['is_default']) {
                DB::table('order_print_setting')->where('id', '<>', $id)->where('ru_id', $adminru['ru_id'])->update(['is_default' => 0]);
            }

            $link[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'tp_api.php?act=order_print_setting'];
            return sys_msg($msg, 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 电子面单 by wu
        /*------------------------------------------------------ */
        elseif ($act == 'order_print') {
            /* 检查权限 */
            admin_priv('order_view');

            /* 打印数据 */
            $print_specification = OrderPrintSetting::where('ru_id', $adminru['ru_id'])->where('is_default', 1)->value('specification');
            $print_specification = $print_specification ? $print_specification : '';
            if (empty($print_specification)) {
                $print_specification = OrderPrintSetting::where('ru_id', $adminru['ru_id'])->orderByRaw('sort_order, id asc')->value('specification');
            }

            $print_size_info = OrderPrintSize::where('specification', $print_specification);
            $print_size_info = BaseRepository::getToArrayFirst($print_size_info);

            $print_size_list = OrderPrintSetting::where('ru_id', $adminru['ru_id'])->orderByRaw('sort_order, id asc');
            $print_size_list = BaseRepository::getToArrayGet($print_size_list);

            $print_spec_info = OrderPrintSetting::where('specification', $print_specification);
            $print_spec_info = BaseRepository::getToArrayFirst($print_spec_info);

            if (empty($print_size_list)) {
                $link[] = ['text' => $GLOBALS['_LANG']['back_set'], 'href' => 'tp_api.php?act=order_print_setting'];
                return sys_msg($GLOBALS['_LANG']['no_print_setting'], 1, $link);
            }

            $this->smarty->assign('print_specification', $print_specification);
            $this->smarty->assign('print_size_info', $print_size_info);
            $this->smarty->assign('print_size_list', $print_size_list);
            $this->smarty->assign('print_spec_info', $print_spec_info);

            /* 订单数据 */
            $order_id = (int)request()->input('order_id', 0);
            $order_sn = e(request()->input('order_sn', ''));
            $order_type = e(request()->input('order_type', 'order'));

            $order_ids = [];
            if (!empty($order_id)) {
                $order_ids[] = $order_id;
            }

            if (!empty($order_sn)) {
                $action_id = DB::table('admin_action')->where('action_code', 'supply_and_demand')->value('action_id');//判断是否安装供求模块
                $action_id = $action_id ? $action_id : 0;

                if ($order_type == 'order' || empty($action_id)) {
                    $table = 'order_info';
                } else {
                    $table = 'wholesale_order_info';
                }

                $order_sn = explode(',', $order_sn);
                $ids = DB::table($table)->whereIn('order_sn', $order_sn)->pluck('order_id')->toArray();
                $order_ids = array_merge($order_ids, $ids);
            }

            if (empty($order_ids)) {
                return sys_msg(trans('admin::tp_api.not_select_order'), 1);
            }

            $web_url = asset('assets/seller') . '/';
            $this->smarty->assign('web_url', $web_url);
            $this->smarty->assign('order_type', $order_type);
            $this->smarty->assign('shop_url', $this->dsc->seller_url());

            $order_print_logo = empty(config('shop.order_print_logo')) ? asset('assets/admin/images/print/order_print_logo.png') : 'assets' . '/' . config('shop.order_print_logo');
            $order_print_logo = $this->dscRepository->getImagePath($order_print_logo);
            $this->smarty->assign('order_print_logo', $order_print_logo);

            $part_html = [];
            foreach ($order_ids as $order_id) {
                $order_info = $this->tpApiManageService->printOrderInfo($order_id, $order_type);
                $this->smarty->assign('order_info', $order_info);
                $this->smarty->assign('order_sn', $order_info['order_sn']);
                $part_html[] = $this->smarty->fetch('library/order_print_part.lbi');
            }
            $this->smarty->assign('part_html', $part_html);

            /* 显示模板 */

            return $this->smarty->display('order_print.dwt');
        }

        /*------------------------------------------------------ */
        //-- 切换电子面单 by wu
        /*------------------------------------------------------ */
        elseif ($act == 'change_order_print') {
            /* 检查权限 */
            $check_auth = check_authz_json('order_view');
            if ($check_auth !== true) {
                return $check_auth;
            }

            /* 打印数据 */
            $print_specification = e(request()->input('specification', ''));

            $print_size_info = OrderPrintSize::where('specification', $print_specification);
            $print_size_info = BaseRepository::getToArrayFirst($print_size_info);

            $print_size_list = OrderPrintSetting::where('ru_id', $adminru['ru_id'])->orderByRaw('sort_order, id asc');
            $print_size_list = BaseRepository::getToArrayGet($print_size_list);

            $print_spec_info = OrderPrintSetting::where('specification', $print_specification);
            $print_spec_info = BaseRepository::getToArrayFirst($print_spec_info);

            $this->smarty->assign('print_specification', $print_specification);
            $this->smarty->assign('print_size_info', $print_size_info);
            $this->smarty->assign('print_size_list', $print_size_list);
            $this->smarty->assign('print_spec_info', $print_spec_info);

            /* 订单数据 */
            $order_id = (int)request()->input('order_id', 0);
            $order_sn = e(request()->input('order_sn', ''));
            $order_type = e(request()->input('order_type', 'order'));

            $order_ids = [];
            if (!empty($order_id)) {
                $order_ids[] = $order_id;
            }

            if (!empty($order_sn)) {
                $action_id = DB::table('admin_action')->where('action_code', 'supply_and_demand')->value('action_id');//判断是否安装供求模块
                $action_id = $action_id ? $action_id : 0;

                if ($order_type == 'order' || empty($action_id)) {
                    $table = 'order_info';
                } else {
                    $table = 'wholesale_order_info';
                }

                $order_sn = explode(',', $order_sn);
                $ids = DB::table($table)->whereIn('order_sn', $order_sn)->pluck('order_id')->toArray();
                $order_ids = array_merge($order_ids, $ids);
            }

            if (empty($order_ids)) {
                return make_json_error(trans('admin::tp_api.not_select_order'));
            }

            $web_url = asset('assets/seller') . '/';
            $this->smarty->assign('web_url', $web_url);
            $this->smarty->assign('order_type', $order_type);
            $this->smarty->assign('shop_url', $this->dsc->seller_url());

            $order_print_logo = empty(config('shop.order_print_logo')) ? asset('assets/admin/images/print/order_print_logo.png') : 'assets' . '/' . config('shop.order_print_logo');
            $order_print_logo = $this->dscRepository->getImagePath($order_print_logo);
            $this->smarty->assign('order_print_logo', $order_print_logo);

            $part_html = [];
            foreach ($order_ids as $order_id) {
                $order_info = $this->tpApiManageService->printOrderInfo($order_id, $order_type);
                $this->smarty->assign('order_info', $order_info);
                $this->smarty->assign('order_sn', $order_info['order_sn']);
                $part_html[] = $this->smarty->fetch('library/order_print_part.lbi');
            }
            $this->smarty->assign('part_html', $part_html);

            /* 显示模板 */
            $content = $this->smarty->fetch('library/order_print.lbi');
            return make_json_result($content);
        }
    }

    /**
     * 获取电子面单设置列表
     *
     * @param $ru_id
     * @return array
     */
    private function get_order_print_setting($ru_id = 0)
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'get_order_print_setting';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 过滤查询 */
        $filter = [];

        $filter['keyword'] = !empty($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
        }

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'ops.sort_order' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'ASC' : trim($_REQUEST['sort_order']);

        $where = 'WHERE 1 ';

        /* 关键字 */
        if (!empty($filter['keyword'])) {
            $where .= " AND (ops.specification LIKE '%" . mysql_like_quote($filter['keyword']) . "%'" . " OR ops.printer LIKE '%" . mysql_like_quote($filter['keyword']) . "%'" . ")";
        }

        if ($ru_id > 0) {
            $where .= " AND ops.ru_id = '$ru_id' ";
        }

        /* 获得总记录数据 */
        $sql = 'SELECT COUNT(*) FROM ' . $this->dsc->table('order_print_setting') . ' AS ops ' . $where;
        $filter['record_count'] = $this->db->getOne($sql);

        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 获得数据 */
        $arr = [];
        $sql = 'SELECT ops.* FROM ' . $this->dsc->table('order_print_setting') . 'AS ops ' .
            $where . 'ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'];

        $res = $this->db->selectLimit($sql, $filter['page_size'], $filter['start']);

        foreach ($res as $rows) {
            $arr[] = $rows;
        }

        return ['list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }
}
