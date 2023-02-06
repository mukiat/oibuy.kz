<?php

namespace App\Modules\Admin\Controllers;

use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;

/**
 * DSCMALL 会员资金管理程序
 */
class UserAccountManageController extends InitController
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    ) {
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {
        load_helper('order');

        $this->dscRepository->helpersLang('statistic', 'admin');

        $this->smarty->assign('lang', $GLOBALS['_LANG']);

        /* act操作项的初始化 */
        if (empty($_REQUEST['act'])) {
            $_REQUEST['act'] = 'list';
        } else {
            $_REQUEST['act'] = trim($_REQUEST['act']);
        }

        /* 权限判断 */
        admin_priv('account_manage');

        /*------------------------------------------------------ */
        //--数据查询
        /*------------------------------------------------------ */
        /* 时间参数 */

        $start_date = $end_date = '';
        if (isset($_POST) && isset($_POST['start_date'])) {
            $_POST['start_date'] = isset($_POST['start_date']) ? $_POST['start_date'] : '';
            $_POST['end_date'] = isset($_POST['end_date']) ? $_POST['end_date'] : '';
            $start_date = local_strtotime($_POST['start_date']);
            $end_date = local_strtotime($_POST['end_date']);
        } elseif (isset($_GET['start_date']) && !empty($_GET['end_date'])) {
            $start_date = local_strtotime($_GET['start_date']);
            $end_date = local_strtotime($_GET['end_date']);
        } else {
            $today = local_strtotime(TimeRepository::getLocalDate('Y-m-d H:i:s'));
            $start_date = $today - 86400 * 7;
            $end_date = $today;
        }

        /*------------------------------------------------------ */
        //--商品明细列表
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'list') {
            $account = $money_list = [];
            $account['voucher_amount'] = $this->get_total_amount($start_date, $end_date);//充值总额
            $account['to_cash_amount'] = $this->get_total_amount($start_date, $end_date, 1);//提现总额

            $sql = " SELECT IFNULL(SUM(user_money), 0) AS user_money, IFNULL(SUM(frozen_money), 0) AS frozen_money FROM " .
                $this->dsc->table('account_log') . " WHERE `change_time` >= " . $start_date . " AND `change_time` < " . ($end_date + 86400);
            $money_list = $this->db->getRow($sql);
            $account['user_money'] = price_format($money_list['user_money']);   //用户可用余额
            $account['frozen_money'] = price_format($money_list['frozen_money']);   //用户冻结金额

            $sql = "SELECT IFNULL(SUM(o.surplus), 0) AS surplus, IFNULL(SUM(o.integral_money), 0) AS integral_money FROM " .
                $this->dsc->table('order_info') . " AS o WHERE 1 AND o.main_count = 0 AND o.add_time >= " . $start_date . " AND o.add_time < " . ($end_date + 86400);
            $money_list = $this->db->getRow($sql);

            $account['surplus'] = price_format($money_list['surplus']);   //交易使用余额
            $account['integral_money'] = price_format($money_list['integral_money']);   //积分使用余额

            /* 赋值到模板 */
            $this->smarty->assign('account', $account);
            $this->smarty->assign('start_date', TimeRepository::getLocalDate('Y-m-d H:i:s', $start_date));
            $this->smarty->assign('end_date', TimeRepository::getLocalDate('Y-m-d H:i:s', $end_date));
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['user_account_manage']);

            /* 显示页面 */

            return $this->smarty->display('user_account_manage.dwt');
        } elseif ($_REQUEST['act'] == 'surplus') {
            $order_list = $this->order_list();

            /* 赋值到模板 */
            $this->smarty->assign('order_list', $order_list['order_list']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['order_by_surplus']);
            $this->smarty->assign('filter', $order_list['filter']);
            $this->smarty->assign('record_count', $order_list['record_count']);
            $this->smarty->assign('page_count', $order_list['page_count']);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['user_account_manage'], 'href' => 'user_account_manage.php?act=list&start_date=' . TimeRepository::getLocalDate('Y-m-d', $start_date) . '&end_date=' . TimeRepository::getLocalDate('Y-m-d', $end_date)]);

            /* 显示页面 */
            return $this->smarty->display('order_surplus_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- ajax返回用户列表
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'query') {
            $order_list = $this->order_list();

            $this->smarty->assign('order_list', $order_list['order_list']);
            $this->smarty->assign('filter', $order_list['filter']);
            $this->smarty->assign('record_count', $order_list['record_count']);
            $this->smarty->assign('page_count', $order_list['page_count']);

            $sort_flag = sort_flag($order_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result($this->smarty->fetch('order_surplus_list.dwt'), '', ['filter' => $order_list['filter'], 'page_count' => $order_list['page_count']]);
        }
    }

    /**
     * 获得账户变动金额
     * @param string $type 0,充值 1,提现
     * @return  array
     */
    private function get_total_amount($start_date, $end_date, $type = 0)
    {
        $sql = " SELECT IFNULL(SUM(amount), 0) AS total_amount FROM " . $this->dsc->table('user_account') . " AS a, " . $this->dsc->table('users') . " AS u " .
            " WHERE process_type = $type AND is_paid = 1 AND a.user_id = u.user_id AND paid_time >= '$start_date' AND paid_time < '" . ($end_date + 86400) . "'";

        $amount = $this->db->getone($sql);
        $amount = $type ? price_format(abs($amount)) : price_format($amount);
        return $amount;
    }


    /**
     *  返回用户订单列表数据
     *
     * @access  public
     * @param
     *
     * @return void
     */
    private function order_list()
    {
        global $start_date, $end_date;
        
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'order_list';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 过滤条件 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'order_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter['start_date'] = TimeRepository::getLocalDate('Y-m-d', $start_date);
        $filter['end_date'] = TimeRepository::getLocalDate('Y-m-d', $end_date);

        $ex_where = ' WHERE 1 ';
        if ($filter['keywords']) {
            $ex_where .= " AND user_name LIKE '%" . mysql_like_quote($filter['keywords']) . "%'";
        }

        $ex_where .= " AND o.user_id = u.user_id AND (o.surplus != 0 OR integral_money != 0) ";

        if ($start_date && $end_date) {
            $ex_where = "AND `add_time` >= " . $start_date . " AND `add_time` < " . ($end_date + 86400);
        }

        $filter['record_count'] = $this->db->getOne("SELECT COUNT(*) FROM " . $this->dsc->table('order_info') . " AS o, " . $this->dsc->table('users') . " AS u " . $ex_where);

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $sql = "SELECT o.order_id, o.order_sn, u.user_name, o.surplus, o.integral_money, o.add_time FROM " .
            $this->dsc->table('order_info') . " AS o," . $this->dsc->table('users') . " AS u " . $ex_where .
            " ORDER by " . $filter['sort_by'] . ' ' . $filter['sort_order'] .
            " LIMIT " . $filter['start'] . ',' . $filter['page_size'];

        $filter['keywords'] = stripslashes($filter['keywords']);

        $order_list = $this->db->getAll($sql);

        $count = count($order_list);
        for ($i = 0; $i < $count; $i++) {
            $order_list[$i]['add_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['date_format'], $order_list[$i]['add_time']);

            if (isset($GLOBALS['_CFG']['show_mobile']) && $GLOBALS['_CFG']['show_mobile'] == 0) {
                $order_list[$i]['user_name'] = $this->dscRepository->stringToStar($order_list[$i]['user_name']);
            }
        }

        $arr = ['order_list' => $order_list, 'filter' => $filter,
            'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }
}
