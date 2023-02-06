<?php

use App\Modules\Suppliers\Models\Suppliers;
use App\Modules\Suppliers\Models\SuppliersAccountLog;
use App\Modules\Suppliers\Models\SuppliersAccountLogDetail;
use App\Modules\Suppliers\Models\Wholesale;
use App\Modules\Suppliers\Models\WholesaleOrderGoods;
use App\Modules\Suppliers\Models\WholesaleOrderInfo;
use App\Modules\Suppliers\Models\WholesaleProducts;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;

/**
 * 供应商基本信息
 *
 * @param int $suppliers_id
 * @return mixed
 */
function get_suppliers_info($suppliers_id = 0)
{
    $row = Suppliers::where('suppliers_id', $suppliers_id);
    $row = BaseRepository::getToArrayFirst($row);

    return $row;
}

/**
 * 供应商帐户变动
 *
 * @param int $suppliers_id
 * @param int $suppliers_money
 * @param int $frozen_money
 */
function log_suppliers_account_change($suppliers_id = 0, $suppliers_money = 0, $frozen_money = 0)
{
    if ($suppliers_money || $frozen_money) {
        /* 更新用户信息 */
        $other = [
            'suppliers_money' => "suppliers_money + ('$suppliers_money')",
            'frozen_money' => "frozen_money + ('$frozen_money')"
        ];

        $other = BaseRepository::getDbRaw($other);

        Suppliers::where('suppliers_id', $suppliers_id)->update($other);
    }
}

/**
 * 供应商帐户变动记录
 *
 * @param int $suppliers_id
 * @param int $user_money
 * @param int $frozen_money
 * @param $change_desc
 * @param int $change_type
 */
function suppliers_account_log($suppliers_id = 0, $user_money = 0, $frozen_money = 0, $change_desc, $change_type = 1)
{
    if ($user_money || $frozen_money) {
        $log = [
            'user_id' => $suppliers_id,
            'user_money' => $user_money,
            'frozen_money' => $frozen_money,
            'change_time' => TimeRepository::getGmTime(),
            'change_desc' => $change_desc,
            'change_type' => $change_type
        ];

        SuppliersAccountLog::insert($log);
    }
}

/**
 * 资金管理日志
 *
 * @return array
 */
function get_suppliers_account_log()
{
    $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'change_time' : trim($_REQUEST['sort_by']);
    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
    $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
    $filter['user_id'] = empty($_REQUEST['suppliers_id']) ? 0 : intval($_REQUEST['suppliers_id']);

    $row = SuppliersAccountLog::whereRaw(1);

    $adminru = get_admin_ru_id();
    if ($adminru['suppliers_id'] > 0) {
        $row = $row->where('user_id', $adminru['suppliers_id']);
    } elseif ($filter['user_id'] > 0) {
        $row = $row->where('user_id', $filter['user_id']);
    } elseif ($filter['keywords']) {
        $user_id = Suppliers::select('suppliers_id')->where('suppliers_name', 'like', '%' . app(DscRepository::class)->mysqlLikeQuote($filter['keywords']) . '%')
            ->orWhere('suppliers_desc', 'like', '%' . app(DscRepository::class)->mysqlLikeQuote($filter['keywords']) . '%')->pluck('suppliers_id');

        if ($user_id) {
            $row = $row->whereIn('user_id', $user_id);
        }
    }

    $row = $row->whereHasIn('getSuppliers', function ($query) {
        $query->where('review_status', 3);
    });

    $res = $record_count = $row;

    $filter['record_count'] = $record_count->count();
    /* 分页大小 */
    $filter = page_and_size($filter);

    $res = $res->with([
        'getSuppliers'
    ]);

    $res = $res->orderBy($filter['sort_by'], $filter['sort_order']);

    $res = $res->skip($filter['start']);

    $res = $res->take($filter['page_size']);

    $res = BaseRepository::getToArrayGet($res);

    if ($res) {
        foreach ($res as $key => $value) {
            $res[$key]['suppliers_name'] = $value['get_suppliers']['suppliers_name'];
            $res[$key]['change_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $value['change_time']);
        }
    }

    $arr = [
        'log_list' => $res,
        'filter' => $filter,
        'page_count' => $filter['page_count'],
        'record_count' => $filter['record_count']
    ];

    return $arr;
}

/**
 * 申请日志列表
 *
 * @param int $suppliers_id
 * @param int $type
 * @return array
 * @throws Exception
 */
function get_suppliers_account_log_detail($suppliers_id = 0, $type = 0)
{
    /* 过滤条件 */
    $filter['keywords'] = !isset($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
    if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
        $filter['keywords'] = json_str_iconv($filter['keywords']);
    }
    $filter['order_id'] = !isset($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
    $filter['order_sn'] = !isset($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
    $filter['out_up'] = !isset($_REQUEST['out_up']) ? 0 : intval($_REQUEST['out_up']);
    $filter['log_type'] = !isset($_REQUEST['log_type']) ? 0 : intval($_REQUEST['log_type']);
    $filter['handler'] = !isset($_REQUEST['handler']) ? 0 : intval($_REQUEST['handler']);
    $filter['rawals'] = !isset($_REQUEST['rawals']) ? 0 : intval($_REQUEST['rawals']);

    $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'log_id' : trim($_REQUEST['sort_by']);
    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

    $filter['act_type'] = !isset($_REQUEST['act_type']) ? 'detail' : $_REQUEST['act_type'];
    $filter['suppliers_id'] = !isset($_REQUEST['suppliers_id']) ? $suppliers_id : intval($_REQUEST['suppliers_id']);

    if ($filter['rawals'] == 1) {
        $type = [1];
    }

    $type = BaseRepository::getExplode($type);

    $row = SuppliersAccountLogDetail::whereIn('log_type', $type);

    //订单编号
    if ($filter['order_sn']) {
        $row = $row->where(function ($query) use ($filter) {
            $query = $query->where('apply_sn', $filter['order_sn']);

            $query->orWhere(function ($query) use ($filter) {
                $query->whereHasIn('getWholesaleOrderInfo', function ($query) use ($filter) {
                    $query->where('order_sn', $filter['order_sn']);
                });
            });
        });
    }

    //收入/支出
    if ($filter['out_up']) {
        if ($filter['out_up'] != 4) {
            if ($filter['out_up'] == 3) {
                $row = $row->where('log_type', $filter['out_up']);
            }

            $row = $row->where(function ($query) use ($filter) {
                $query->where('log_type', '>', $filter['out_up'])
                    ->orWhere('log_type', $filter['out_up']);
            });
        } else {
            $row = $row->where('log_type', $filter['out_up']);
        }
    }

    //待处理
    if ($filter['handler']) {
        if ($filter['handler'] == 1) {
            $row = $row->where('is_paid', 1);
        } else {
            $row = $row->where('is_paid', 0);
        }
    }

    //类型
    if ($filter['log_type']) {
        $row = $row->where('log_type', $filter['log_type']);
    }

    if ($filter['order_id']) {
        $row = $row->where('order_id', $filter['order_id']);
    }

    if ($filter['suppliers_id']) {
        $row = $row->where('suppliers_id', $filter['suppliers_id']);
    }

    $res = $record_count = $row;

    $filter['record_count'] = $record_count->count();

    /* 分页大小 */
    $filter = page_and_size($filter);

    $res = $res->with([
        'getSuppliers',
        'getWholesaleOrderInfo'
    ]);

    $res = $res->orderBy($filter['sort_by'], $filter['sort_order']);

    $res = $res->skip($filter['start']);

    $res = $res->take($filter['page_size']);

    $res = BaseRepository::getToArrayGet($res);

    if ($res) {
        foreach ($res as $key => $value) {
            $res[$key]['suppliers_name'] = $value['get_suppliers']['suppliers_name'] ?? '';

            $order_sn = $value['get_wholesale_order_info']['order_sn'] ?? '';
            $res[$key]['order_sn'] = !empty($order_sn) ? sprintf(lang('order.order_remark'), $order_sn) : $value['apply_sn'];

            $res[$key]['amount'] = app(DscRepository::class)->getPriceFormat($res[$key]['amount'], false);
            $res[$key]['add_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $value['add_time']);
            $res[$key]['payment_info'] = payment_info($value['pay_id']);
            $res[$key]['apply_sn'] = sprintf($GLOBALS['_LANG']['01_apply_sn'], $value['apply_sn']);
        }
    }

    $arr = [
        'log_list' => $res,
        'filter' => $filter,
        'page_count' => $filter['page_count'],
        'record_count' => $filter['record_count']
    ];

    return $arr;
}

/**
 * 获取统计数据
 *
 * @param int $start_date
 * @param int $end_date
 * @param string $type
 * @param int $suppliers_id
 * @return array
 * @throws Exception
 */
function get_suppliers_statistical_data($start_date = 0, $end_date = 0, $type = 'order', $suppliers_id = 0)
{
    $data = array();

    //格林威治时间与本地时间差
    $timezone = session()->has('timezone') ? session('timezone') : $GLOBALS['_CFG']['timezone'];
    $time_diff = $timezone * 3600;
    $date_start = $start_date;
    $date_end = $end_date;
    $day_num = intval(ceil(($date_end - $date_start) / 86400));

    $where_date = '';
    //获取系统数据 start
    $no_main_order = " AND (SELECT count(*) FROM " . $GLOBALS['dsc']->table('wholesale_order_info') . " AS oi2 WHERE oi2.main_order_id = oi.order_id) = 0 "; //主订单下有子订单时，则主订单不显示
    if ($suppliers_id > 0) {
        $where_date .= " AND oi.suppliers_id = '" . $suppliers_id . "'";
    }

    $sql = 'SELECT DATE_FORMAT(FROM_UNIXTIME(oi.add_time + ' . $time_diff . '),"%y-%m-%d") AS day,COUNT(*) AS count,SUM(oi.order_amount) AS money FROM ' .
        $GLOBALS['dsc']->table('wholesale_order_info') . " AS oi" . ' WHERE oi.pay_status =2 AND oi.order_status = 1 AND  oi.add_time BETWEEN ' . $date_start .
        ' AND ' . $date_end . $no_main_order . $where_date . '  GROUP BY day ORDER BY day ASC ';
    $result = $GLOBALS['db']->getAll($sql);

    $orders_series_data = [];
    $sales_series_data = [];
    $orders_xAxis_data = [];
    $sales_xAxis_data = [];
    if (!empty($result)) {
        foreach ($result as $key => $row) {
            $orders_series_data[$row['day']] = intval($row['count']);
            $sales_series_data[$row['day']] = floatval($row['money']);
        }
    }

    $end_time = TimeRepository::getLocalDate('y-m-d', $date_end - 86400);
    for ($i = 1; $i <= $day_num; $i++) {
        $day = TimeRepository::getLocalDate("y-m-d", TimeRepository::getLocalStrtoTime($end_time . " - " . ($day_num - $i) . " days"));
        if (empty($orders_series_data[$day])) {
            $orders_series_data[$day] = 0;
            $sales_series_data[$day] = 0;
        }
        //输出时间
        $day = TimeRepository::getLocalDate("m-d", TimeRepository::getLocalStrtoTime($day));
        $orders_xAxis_data[] = $day;
        $sales_xAxis_data[] = $day;
    }

    //获取系统数据 end

    //图表公共数据 start
    $title = array(
        'text' => '',
        'subtext' => ''
    );

    $toolbox = array(
        'show' => true,
        'orient' => 'vertical',
        'x' => 'right',
        'y' => '60',
        'feature' => array(
            'magicType' => array(
                'show' => true,
                'type' => array('line', 'bar')
            ),
            'saveAsImage' => array(
                'show' => true
            )
        )
    );
    $tooltip = array('trigger' => 'axis',
        'axisPointer' => array(
            'lineStyle' => array(
                'color' => '#6cbd40'
            )
        )
    );
    $xAxis = array(
        'type' => 'category',
        'boundaryGap' => false,
        'axisLine' => array(
            'lineStyle' => array(
                'color' => '#ccc',
                'width' => 0
            )
        ),
        'data' => array());
    $yAxis = array(
        'type' => 'value',
        'axisLine' => array(
            'lineStyle' => array(
                'color' => '#ccc',
                'width' => 0
            )
        ),
        'axisLabel' => array(
            'formatter' => ''));
    $series = array(
        array(
            'name' => '',
            'type' => 'line',
            'itemStyle' => array(
                'normal' => array(
                    'color' => '#6cbd40',
                    'lineStyle' => array(
                        'color' => '#6cbd40'
                    )
                )
            ),
            'data' => array(),
            'markPoint' => array(
                'itemStyle' => array(
                    'normal' => array(
                        'color' => '#6cbd40'
                    )
                ),
                'data' => array(
                    array(
                        'type' => 'max',
                        'name' => lang('order.max_value')
                    ),
                    array(
                        'type' => 'min',
                        'name' => lang('order.min_value')
                    )
                )
            )
        ),
        array(
            'type' => 'force',
            'name' => '',
            'draggable' => false,
            'nodes' => array(
                'draggable' => false
            )
        )
    );
    $calculable = true;
    $legend = array('data' => array());
    //图表公共数据 end

    //订单统计
    if ($type == 'order') {
        $title['text'] = lang('order.order_number');
        $xAxis['data'] = $orders_xAxis_data;
        $yAxis['formatter'] = '{value}' . lang('order.individual');
        ksort($orders_series_data);
        $series[0]['name'] = lang('order.order_individual_count');
        $series[0]['data'] = array_values($orders_series_data);
    }

    //销售统计
    if ($type == 'sale') {
        $title['text'] = lang('order.sale_money');
        $xAxis['data'] = $sales_xAxis_data;
        $yAxis['formatter'] = '{value}' . lang('order.money_unit');
        ksort($sales_series_data);
        $series[0]['name'] = lang('order.sale_money');
        $series[0]['data'] = array_values($sales_series_data);
    }

    //整理数据
    $data['title'] = $title;
    $data['series'] = $series;
    $data['tooltip'] = $tooltip;
    $data['legend'] = $legend;
    $data['toolbox'] = $toolbox;
    $data['calculable'] = $calculable;
    $data['xAxis'] = $xAxis;
    $data['yAxis'] = $yAxis;

    return $data;
}

/**
 * 申请日志详细信息
 */
function get_suppliers_account_log_info($log_id)
{
    $res = SuppliersAccountLogDetail::where('log_id', $log_id);
    $res = BaseRepository::getToArrayGet($res);

    if ($res) {
        $info = get_suppliers_info($res['suppliers_id']);
        $res['suppliers_name'] = $info['suppliers_name'];
        $res['payment_info'] = payment_info($res['pay_id']);
        $res['ru_id'] = $info['user_id'];

        /* 供应商资金 start */
        $res['suppliers_money'] = $info['suppliers_money']; //供应商可提现金额
        $res['suppliers_frozen'] = $info['frozen_money']; //供应商冻结金额
        /* 供应商资金 end */
    }

    return $res;
}

/**
 * 改变订单中商品库存
 *
 * @param int $order_id 订单号
 * @param bool $is_dec 是否减少库存
 * @param int $storage 减库存的时机，2，付款时； 1，下订单时；0，发货时；
 * return void
 */
function suppliers_change_order_goods_storage($order_id = 0, $is_dec = true, $storage = 0)
{
    if (!file_exists(SUPPLIERS)) {
        return false;
    }

    return \App\Modules\Suppliers\Repositories\WholesaleOrderRepository::suppliers_change_order_goods_storage($order_id, $is_dec, $storage);
}

/**
 * 供应商获取配置客服QQ
 *
 * @param int $suppliers_id
 * @return array
 */
function get_suppliers_kf($suppliers_id = 0)
{
    $kf_qq = Suppliers::where('suppliers_id', $suppliers_id)->value('kf_qq');
    if (!empty($kf_qq)) {
        $kf_qq = array_filter(preg_split('/\s+/', $kf_qq));

        if ($kf_qq) {
            foreach ($kf_qq as $k => $v) {
                $row['kf_qq_all'][] = explode("|", $v);
            }
        }

        $kf_qq = $kf_qq && $kf_qq[0] ? explode("|", $kf_qq[0]) : [];
        if (isset($kf_qq[1]) && !empty($kf_qq[1])) {
            $row['kf_qq'] = $kf_qq[1];
        } else {
            $row['kf_qq'] = "";
        }

        return $row;
    }

    return [];
}
