<?php

namespace App\Modules\Admin\Controllers;

use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;

/**
 * 会员管理程序
 */
class WarehouseOrderController extends InitController
{
    public function index()
    {

        /*------------------------------------------------------ */
        //-- 订单仓库列表
        /*------------------------------------------------------ */

        if ($_REQUEST['act'] == 'list') {
            /* 检查权限 */
            admin_priv('warehouse_manage');

            $order_id = isset($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0;

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['09_warehouse_management']);

            $warehouse_list = $this->order_warehouse_list($order_id);

            session([
                'warehouse_order_id' => $order_id
            ]);

            $this->smarty->assign('warehouse_list', $warehouse_list['warehouse_list']);
            $this->smarty->assign('filter', $warehouse_list['filter']);
            $this->smarty->assign('record_count', $warehouse_list['record_count']);
            $this->smarty->assign('page_count', $warehouse_list['page_count']);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('sort_user_id', '<img src="' . __TPL__ . '/images/sort_desc.gif">');


            return $this->smarty->display('order_warehouse_list.htm');
        }

        /*------------------------------------------------------ */
        //-- ajax返回订单仓库列表
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'query') {
            $warehouse_list = $this->order_warehouse_list(session('warehouse_order_id'));

            $this->smarty->assign('warehouse_list', $warehouse_list['warehouse_list']);
            $this->smarty->assign('filter', $warehouse_list['filter']);
            $this->smarty->assign('record_count', $warehouse_list['record_count']);
            $this->smarty->assign('page_count', $warehouse_list['page_count']);

            $sort_flag = sort_flag($warehouse_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result($this->smarty->fetch('order_warehouse_list.htm'), '', ['filter' => $warehouse_list['filter'], 'page_count' => $warehouse_list['page_count']]);
        }
    }

    /**
     * 返回订单仓库列表数据
     *
     * @param $order_id
     * @return array
     */
    private function order_warehouse_list($order_id)
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'order_warehouse_list';
        $get_filter = app(DscRepository::class)->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 过滤条件 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'rw.region_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $ex_where = ' WHERE 1 ';

        $sql = "SELECT  og.warehouse_id, rw.region_name " .
            " FROM " . $this->dsc->table('order_info') . " as oi" .

            " left join " . $this->dsc->table('order_goods') . " as og on oi.order_id = og.order_id" .
            " left join " . $this->dsc->table('region_warehouse') . " as rw on og. warehouse_id  = rw.region_id" .

            $ex_where . " AND oi.order_id = '$order_id' group by og. warehouse_id";

        $filter['record_count'] = count($this->db->getAll($sql));

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        app(DscRepository::class)->setSessionFilter($filter, $param_str);

        $sql = "SELECT  og.order_id, u.user_id, u.user_name, og.warehouse_id, rw.region_name, sum(og.attr_number) as attr_number" .
            " FROM " . $this->dsc->table('order_info') . " as oi" .

            " left join " . $this->dsc->table('order_goods') . " as og on oi.order_id = og.order_id" .
            " left join " . $this->dsc->table('region_warehouse') . " as rw on og. warehouse_id  = rw.region_id" .
            " left join " . $this->dsc->table('users') . " as u on oi.user_id  = u.user_id" .

            $ex_where .

            " AND oi.order_id = '$order_id' group by og. warehouse_id" .

            " ORDER by " . $filter['sort_by'] . ' ' . $filter['sort_order'] .
            " LIMIT " . $filter['start'] . ',' . $filter['page_size'];

        $filter['keywords'] = stripslashes($filter['keywords']);

        $warehouse_list = $this->db->getAll($sql);

        $count = count($warehouse_list);
        for ($i = 0; $i < $count; $i++) {
            $warehouse_list[$i]['add_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['date_format'], $warehouse_list[$i]['add_time']);
        }

        $arr = ['warehouse_list' => $warehouse_list, 'filter' => $filter,
            'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }
}
