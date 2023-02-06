<?php

namespace App\Modules\Admin\Controllers;

use App\Exports\UserStatsAreaExport;
use App\Jobs\Export\ProcessUserConsumptionRank;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Common\CommonManageService;
use App\Services\Order\OrderCommonService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class UserStatsController extends InitController
{
    protected $orderCommonService;
    protected $commonManageService;
    protected $dscRepository;

    public function __construct(
        OrderCommonService $orderCommonService,
        CommonManageService $commonManageService,
        DscRepository $dscRepository
    )
    {
        $this->orderCommonService = $orderCommonService;
        $this->commonManageService = $commonManageService;
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {
        load_helper(['order', 'statistical']);

        $this->dscRepository->helpersLang(['statistic'], 'admin');

        /* act操作项的初始化 */
        $act = e(request()->input('act', 'list'));

        /* 时间参数 */
        if (isset($_REQUEST['start_date']) && !empty($_REQUEST['end_date'])) {
            $start_date = TimeRepository::getLocalStrtoTime($_REQUEST['start_date']);
            $end_date = TimeRepository::getLocalStrtoTime($_REQUEST['end_date']);
            if ($start_date == $end_date) {
                $end_date = $start_date + 86400;
            }
        } else {
            $today = TimeRepository::getLocalStrtoTime(TimeRepository::getLocalDate('Y-m-d'));
            $start_date = $today - 86400 * 6;
            $end_date = $today + 86400;
        }

        $this->smarty->assign('start_date', TimeRepository::getLocalDate('Y-m-d H:i:s', $start_date));
        $this->smarty->assign('end_date', TimeRepository::getLocalDate('Y-m-d H:i:s', $end_date));
        $this->smarty->assign('area_list', $this->commonManageService->getAreaRegionList());

        /*------------------------------------------------------ */
        //-- 新会员
        /*------------------------------------------------------ */
        if ($act == 'new') {
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['newadd_user']);

            return $this->smarty->display('new_user_stats.dwt');
        }

        /*------------------------------------------------------ */
        //-- 新会员异步
        /*------------------------------------------------------ */
        elseif ($act == 'get_chart_data') {
            $search_data = array();
            $search_data['start_date'] = $start_date;
            $search_data['end_date'] = $end_date;
            $chart_data = get_statistical_new_user($search_data);

            return make_json_result($chart_data);
        }

        /*------------------------------------------------------ */
        //-- 会员统计
        /*------------------------------------------------------ */
        elseif ($act == 'user_analysis') {
            $order_list = $this->orderCommonService->userSaleStats();

            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('order_list', $order_list['orders']);
            $this->smarty->assign('filter', $order_list['filter']);
            $this->smarty->assign('record_count', $order_list['record_count']);
            $this->smarty->assign('page_count', $order_list['page_count']);

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['user_analysis']);

            return $this->smarty->display('user_analysis.dwt');
        }

        /*------------------------------------------------------ */
        //-- 会员统计查询
        /*------------------------------------------------------ */
        elseif ($act == 'user_analysis_query') {
            $order_list = $this->orderCommonService->userSaleStats();

            $this->smarty->assign('order_list', $order_list['orders']);
            $this->smarty->assign('filter', $order_list['filter']);
            $this->smarty->assign('record_count', $order_list['record_count']);
            $this->smarty->assign('page_count', $order_list['page_count']);
            $sort_flag = sort_flag($order_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result($this->smarty->fetch('user_analysis.dwt'), '', array('filter' => $order_list['filter'], 'page_count' => $order_list['page_count']));
        }

        /*------------------------------------------------------ */
        //-- 会员区域分析
        /*------------------------------------------------------ */
        elseif ($act == 'user_area_analysis') {
            $order_list = $this->orderCommonService->userAreaStats();

            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('order_list', $order_list['orders']);
            $this->smarty->assign('filter', $order_list['filter']);
            $this->smarty->assign('record_count', $order_list['record_count']);
            $this->smarty->assign('page_count', $order_list['page_count']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['user_area_analysis']);

            return $this->smarty->display('user_area_analysis.dwt');
        }

        /*------------------------------------------------------ */
        //-- 会员区域分析查询
        /*------------------------------------------------------ */
        elseif ($act == 'user_area_analysis_query') {
            $order_list = $this->orderCommonService->userAreaStats();

            $this->smarty->assign('order_list', $order_list['orders']);
            $this->smarty->assign('filter', $order_list['filter']);
            $this->smarty->assign('record_count', $order_list['record_count']);
            $this->smarty->assign('page_count', $order_list['page_count']);
            $sort_flag = sort_flag($order_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result($this->smarty->fetch('user_area_analysis.dwt'), '', array('filter' => $order_list['filter'], 'page_count' => $order_list['page_count']));
        }

        /*------------------------------------------------------ */
        //-- 会员等级分析
        /*------------------------------------------------------ */
        elseif ($act == 'user_rank_analysis') {
            $user_rank = get_statistical_user_rank();
            $this->smarty->assign('user_rank', $user_rank['source']);
            $this->smarty->assign('json_data', json_encode($user_rank));
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['user_rank_analysis']);

            return $this->smarty->display('user_rank_analysis.dwt');
        }

        /*------------------------------------------------------ */
        //-- 会员消费排行
        /*------------------------------------------------------ */
        elseif ($act == 'user_consumption_rank') {
            $order_list = $this->orderCommonService->userSaleStats();

            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('order_list', $order_list['orders']);
            $this->smarty->assign('filter', $order_list['filter']);
            $this->smarty->assign('record_count', $order_list['record_count']);
            $this->smarty->assign('page_count', $order_list['page_count']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['user_consumption_rank']);

            $this->smarty->assign('current_time', TimeRepository::getLocalDate('Y-m-d H:i:s'));
            $this->smarty->assign('current_url', urlencode(request()->getRequestUri()));
            $this->smarty->assign('lang', array_merge($GLOBALS['_LANG'], trans('admin/order_export')));

            return $this->smarty->display('user_consumption_rank.dwt');
        }

        /*------------------------------------------------------ */
        //-- 会员消费排行查询
        /*------------------------------------------------------ */
        elseif ($act == 'user_consumption_rank_query') {
            $order_list = $this->orderCommonService->userSaleStats();

            $this->smarty->assign('order_list', $order_list['orders']);
            $this->smarty->assign('filter', $order_list['filter']);
            $this->smarty->assign('record_count', $order_list['record_count']);
            $this->smarty->assign('page_count', $order_list['page_count']);
            $sort_flag = sort_flag($order_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result($this->smarty->fetch('user_consumption_rank.dwt'), '', array('filter' => $order_list['filter'], 'page_count' => $order_list['page_count']));
        }

        /*------------------------------------------------------ */
        //-- 导出地区
        /*------------------------------------------------------ */
        elseif ($act == 'download_area') {
            $_GET['uselastfilter'] = 1;

            $filename = 'users_tats_area_' . TimeRepository::getLocalDate('Y-m-d H:i:s');
            return Excel::download(new UserStatsAreaExport, $filename . '.xlsx');
        }

        /*------------------------------------------------------ */
        //-- 导出会员消费排行（队列）
        /*------------------------------------------------------ */
        elseif ($act == 'export_user_consumption_rank') {

            $filter = request()->post();

            $filter['page_size'] = 100;
            $filter['file_name'] = date('YmdHis') . mt_rand(1000, 9999);
            $filter['type'] = $type = 'user_consumption_rank';
            $adminru = get_admin_ru_id();
            $filter['admin_id'] = $adminru['ru_id'] ?? 0; // 导出操作管理员id

            $filter['start_date'] = !empty($filter['start_date']) ? (strpos($filter['start_date'], '-') > 0 ? TimeRepository::getLocalStrtoTime($filter['start_date']) : $filter['start_date']) : '';
            $filter['end_date'] = !empty($filter['end_date']) ? (strpos($filter['end_date'], '-') > 0 ? TimeRepository::getLocalStrtoTime($filter['end_date']) : $filter['end_date']) : '';

            // 插入导出记录表
            $filter['request_id'] = DB::table('export_history')->insertGetId([
                'ru_id' => $filter['admin_id'],
                'type' => $type,
                'file_name' => $filter['file_name'] . '_' . 1,
                'file_type' => 'xls',
                'download_params' => json_encode($filter),
                'created_at' => Carbon::now(),
            ]);

            ProcessUserConsumptionRank::dispatch($filter);

            return make_json_result($type);
        }
    }
}
