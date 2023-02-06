<?php

namespace App\Modules\Admin\Controllers;

use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Category\CategoryService;

class IndustryAnalysisController extends InitController
{
    protected $categoryService;
    protected $dscRepository;

    public function __construct(
        CategoryService $categoryService,
        DscRepository $dscRepository
    )
    {
        $this->categoryService = $categoryService;
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {
        load_helper(['order', 'statistical']);

        $this->dscRepository->helpersLang(['statistic'], 'admin');

        /* act操作项的初始化 */
        $act = request()->input('act', 'list');

        $start_date = request()->input('start_date');
        $end_date = request()->input('end_date');

        /* 时间参数 */
        if (isset($start_date) && !empty($end_date)) {
            $start_date = local_strtotime($start_date);
            $end_date = local_strtotime($end_date);
            if ($start_date == $end_date) {
                $end_date = $start_date + 86400;
            }
        } else {
            $today = local_strtotime(TimeRepository::getLocalDate('Y-m-d'));
            $start_date = $today - 86400 * 6;
            $end_date = $today + 86400;
        }

        $this->smarty->assign('start_date', TimeRepository::getLocalDate('Y-m-d H:i:s', $start_date));
        $this->smarty->assign('end_date', TimeRepository::getLocalDate('Y-m-d H:i:s', $end_date));

        $main_category = $this->categoryService->catList();
        $this->smarty->assign('main_category', $main_category);

        /*------------------------------------------------------ */
        //-- 账单统计管理
        /*------------------------------------------------------ */
        if ($act == 'list') {
            $order_list = industry_analysis();

            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('order_list', $order_list['orders']);
            $this->smarty->assign('filter', $order_list['filter']);
            $this->smarty->assign('record_count', $order_list['record_count']);
            $this->smarty->assign('page_count', $order_list['page_count']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['04_industry_analysis']);

            return $this->smarty->display('industry_analysis.dwt');
        }

        /*------------------------------------------------------ */
        //-- 店铺销售查询
        /*------------------------------------------------------ */
        if ($act == 'query') {
            $order_list = industry_analysis();

            $this->smarty->assign('order_list', $order_list['orders']);
            $this->smarty->assign('filter', $order_list['filter']);
            $this->smarty->assign('record_count', $order_list['record_count']);
            $this->smarty->assign('page_count', $order_list['page_count']);
            $sort_flag = sort_flag($order_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result($this->smarty->fetch('industry_analysis.dwt'), '', array('filter' => $order_list['filter'], 'page_count' => $order_list['page_count']));
        }

        /*------------------------------------------------------ */
        //-- 异步
        /*------------------------------------------------------ */
        elseif ($act == 'get_chart_data') {

            $search_data = array();
            $type = request()->input('type', '');
            $search_data['type'] = $type;
            $chart_data = get_statistical_industry_analysis($search_data);

            return make_json_result($chart_data);
        }

        /*------------------------------------------------------ */
        //-- 导出
        /*------------------------------------------------------ */
        elseif ($act == 'download') {
            $_GET['uselastfilter'] = 1;
            $filter['start_date'] = empty($_REQUEST['start_date']) ? '' : (strpos($_REQUEST['start_date'], '-') > 0 ? local_strtotime($_REQUEST['start_date']) : $_REQUEST['start_date']);
            $filter['end_date'] = empty($_REQUEST['end_date']) ? '' : (strpos($_REQUEST['end_date'], '-') > 0 ? local_strtotime($_REQUEST['end_date']) : $_REQUEST['end_date']);
            $filter['cat_id'] = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);

            $where_c = ' WHERE 1 ';
            $where_o = '';

            if ($filter['start_date']) {
                $where_o .= " AND o.add_time >= '$filter[start_date]'";
            }
            if ($filter['end_date']) {
                $where_o .= " AND o.add_time <= '$filter[end_date]'";
            }

            if ($filter['cat_id']) {
                $where_c .= " AND " . get_children($filter['cat_id'], 0, 0, 'category', 'c.cat_id');
            }
            /* 分组 */
            $groupBy = " GROUP BY c.cat_id ";

            /* 关联查询 */
            $leftJoin = '';
            $leftJoin .= " LEFT JOIN " . $GLOBALS['dsc']->table('goods') . " AS g ON g.cat_id = c.cat_id ";
            $leftJoin .= " LEFT JOIN " . $GLOBALS['dsc']->table('order_goods') . " AS og ON og.goods_id = g.goods_id ";
            $leftJoin .= " LEFT JOIN " . $GLOBALS['dsc']->table('order_info') . " AS o ON o.order_id = og.order_id ";

            /* 查询 */
            $sql = "SELECT c.cat_id, c.cat_name, " .
                statistical_field_order_goods_amount() . " AS goods_amount, " .
                statistical_field_valid_goods_amount() . " AS valid_goods_amount, " .
                statistical_field_goods_num() . " AS goods_num, " .
                statistical_field_no_order_goods_num() . " AS no_order_goods_num, " .
                statistical_field_order_goods_num() . " AS order_goods_num, " .
                statistical_field_user_num() . " AS user_num, " .
                statistical_field_order_num() . " as order_num, " .
                statistical_field_valid_num() . " as valid_num " .
                " FROM " . $GLOBALS['dsc']->table('category') . " AS c " .
                $leftJoin .
                $where_c . $where_o . $groupBy;

            $tdata = $GLOBALS['db']->getAll($sql);

            /* 格式化数据 */
            foreach ($tdata as $key => $value) {
                $tdata[$key]['formated_goods_amount'] = price_format($value['goods_amount']);
                $tdata[$key]['formated_valid_goods_amount'] = price_format($value['valid_goods_amount']);
            }
            $thead = array($GLOBALS['_LANG']['03_category_manage'], $GLOBALS['_LANG']['sale_money'], $GLOBALS['_LANG']['effective_sale_money'], $GLOBALS['_LANG']['total_quantity'], $GLOBALS['_LANG']['effective_quantity'], $GLOBALS['_LANG']['goods_total_num'], $GLOBALS['_LANG']['effective_goods_num'], $GLOBALS['_LANG']['not_sale_money_goods_num'], $GLOBALS['_LANG']['order_user_total']);
            $tbody = array('cat_name', 'goods_amount', 'valid_goods_amount', 'order_num', 'valid_num', 'goods_num', 'order_goods_num', 'no_order_goods_num', 'user_num');

            $config = array(
                'filename' => $GLOBALS['_LANG']['04_industry_analysis'],
                'thead' => $thead,
                'tbody' => $tbody,
                'tdata' => $tdata
            );

            list_download($config);
        }
    }
}
