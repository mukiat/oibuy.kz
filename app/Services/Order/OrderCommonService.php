<?php

namespace App\Services\Order;

use App\Models\Ad;
use App\Models\AdminUser;
use App\Models\Adsense;
use App\Models\Goods;
use App\Models\OrderAction;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\OrderReturn;
use App\Models\PayLog;
use App\Models\Payment;
use App\Models\ReturnAction;
use App\Models\Shipping;
use App\Models\TeamGoods;
use App\Models\TradeSnapshot;
use App\Models\UserOrderNum;
use App\Models\Users;
use App\Models\ValueCardRecord;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Category\CategoryService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Payment\PaymentService;
use App\Services\Team\TeamDataHandleService;
use App\Services\User\UserDataHandleService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * 商城商品订单
 * Class CrowdFund
 * @package App\Services
 */
class OrderCommonService
{
    protected $merchantCommonService;
    protected $dscRepository;
    protected $paymentService;

    public function __construct(
        MerchantCommonService $merchantCommonService,
        DscRepository $dscRepository,
        PaymentService $paymentService
    )
    {
        $this->merchantCommonService = $merchantCommonService;
        $this->dscRepository = $dscRepository;
        $this->paymentService = $paymentService;
    }

    /**
     * 取得订单概况数据(包括订单的几种状态)
     *
     * @param int $start_date 开始查询的日期
     * @param int $end_date 查询的结束日期
     * @param array $adminru
     * @return array
     */
    public function getStatsOrderInfo($start_date = 0, $end_date = 0, $adminru = [])
    {
        $order_info = [];

        /* 未确认订单数 */
        $time = $end_date + 86400;

        $unconfirmed_num = OrderInfo::where('order_status', OS_UNCONFIRMED)
            ->where('add_time', '>=', $start_date)
            ->where('add_time', '<', $time)
            ->where('ru_id', $adminru['ru_id'])
            ->where('main_count', 0)
            ->count();

        $order_info['unconfirmed_num'] = $unconfirmed_num;

        /* 已确认订单数 */
        $confirmed_num = OrderInfo::where('order_status', OS_CONFIRMED)
            ->whereIn('shipping_status', [SS_SHIPPED, SS_RECEIVED])
            ->whereNotIn('pay_status', [PS_PAYED, PS_PAYING])
            ->where('add_time', '>=', $start_date)
            ->where('add_time', '<', $time)
            ->where('ru_id', $adminru['ru_id'])
            ->where('main_count', 0)
            ->count();

        $order_info['confirmed_num'] = $confirmed_num;

        /* 已成交订单数 */
        $succeed_num = OrderInfo::where('add_time', '>=', $start_date)
            ->where('add_time', '<', $time)
            ->where('ru_id', $adminru['ru_id'])
            ->where('main_count', 0);

        $succeed_num = $this->orderQuerySelect($succeed_num, 'real_pay');

        $succeed_num = $succeed_num->count();

        $order_info['succeed_num'] = $succeed_num;

        /* 无效或已取消订单数 */
        $invalid_num = OrderInfo::whereIn('order_status', [OS_CANCELED, OS_INVALID])
            ->where('add_time', '>=', $start_date)
            ->where('add_time', '<', $time)
            ->where('ru_id', $adminru['ru_id'])
            ->where('main_count', 0)
            ->count();

        $order_info['invalid_num'] = $invalid_num;

        return $order_info;
    }

    /**
     * 获取支付类型
     *
     * @param string $start_date
     * @param string $end_date
     * @param array $adminru
     * @return mixed
     */
    public function getPayType($start_date = '', $end_date = '', $adminru = [])
    {
        $where = [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'ru_id' => $adminru['ru_id']
        ];

        $list = Payment::whereHasIn('getOrder', function ($query) use ($where) {
            $query = $this->orderQuerySelect($query, 'real_pay');
            $query->where('add_time', '>=', $where['start_date'])
                ->where('add_time', '<=', $where['end_date'])
                ->where('ru_id', $where['ru_id'])
                ->where('main_count', 0);
        });

        $list = $list->orderBy('pay_id', 'desc');

        $list = BaseRepository::getToArrayGet($list);

        return $list;
    }

    /**
     * 获取配送类型
     *
     * @param string $start_date
     * @param string $end_date
     * @param array $adminru
     * @return mixed
     */
    public function getShippingType($start_date = '', $end_date = '', $adminru = [])
    {
        $where = [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'ru_id' => $adminru['ru_id']
        ];

        $list = Shipping::whereHasIn('getOrder', function ($query) use ($where) {
            $query = $this->orderQuerySelect($query, 'real_pay');
            $query->where('add_time', '>=', $where['start_date'])
                ->where('add_time', '<=', $where['end_date'])
                ->where('ru_id', $where['ru_id'])
                ->where('main_count', 0);
        });

        $list = $list->orderBy('shipping_id', 'desc');

        $list = BaseRepository::getToArrayGet($list);

        if ($list) {
            foreach ($list as $key => $val) {
                $list[$key]['ship_name'] = $val['shipping_name'];

                unset($list[$key]['shipping_name']);
            }
        }

        return $list;
    }

    /**
     * 转为二维数组
     *
     * @param $arr1
     * @param $arr2
     * @param string $str1
     * @param string $str2
     * @param string $str3
     * @param string $str4
     * @return array
     */
    public function getToArray($arr1, $arr2, $str1 = '', $str2 = '', $str3 = '', $str4 = '')
    {
        $ship_arr = [];
        foreach ($arr1 as $key1 => $row1) {
            foreach ($arr2 as $key2 => $row2) {
                if ($row1["{$str1}"] == $row2["{$str1}"]) {
                    $ship_arr[$row1["{$str1}"]]["{$str2}"][$key2] = $row2;
                    $ship_arr[$row1["{$str1}"]]["{$str3}"] = $row1["{$str3}"];
                    if (!empty($str4)) {
                        $ship_arr[$row1["{$str1}"]]["{$str4}"] = $row1["{$str4}"];
                    }
                }
            }
        }

        return $ship_arr;
    }

    /*------------------------------------------------------ */
    //--排行统计需要的函数
    /*------------------------------------------------------ */
    /**
     * 取得销售排行数据信息
     *
     * @param int $ru_id
     * @param bool $is_pagination 是否分页
     * @return array 销售排行数据
     * @throws \Exception
     */
    public function getSalesOrder($ru_id = 0, $is_pagination = true)
    {
        $filter['ru_id'] = isset($_REQUEST['ru_id']) && !empty($_REQUEST['ru_id']) ? intval($_REQUEST['ru_id']) : $ru_id;
        $filter['start_date'] = empty($_REQUEST['start_date']) ? TimeRepository::getLocalStrtoTime('today') : TimeRepository::getLocalStrtoTime($_REQUEST['start_date']);
        $filter['end_date'] = empty($_REQUEST['end_date']) ? TimeRepository::getLocalStrtoTime('+7 day') : TimeRepository::getLocalStrtoTime($_REQUEST['end_date']);
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'goods_num' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = [
            'start_date' => $filter['start_date'],
            'end_date' => $filter['end_date'],
            'ru_id' => $filter['ru_id'],
        ];

        $filter['record_count'] = OrderGoods::whereHasIn('getOrder', function ($query) use ($where) {
            $query = $this->orderQuerySelect($query, 'finished');

            if ($where['start_date']) {
                $query = $query->where('add_time', '>=', $where['start_date']);
            }
            if ($where['end_date']) {
                $query = $query->where('add_time', '<=', $where['end_date']);
            }

            $query->where('ru_id', $where['ru_id']);
        })->groupBy('goods_id')
            ->pluck('goods_id')
            ->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        $res = OrderGoods::selectRaw("order_id, goods_id, goods_sn, goods_name, ru_id, SUM(goods_number) AS goods_num, SUM(goods_number * goods_price) AS turnover")
            ->whereHasIn('getOrder', function ($query) use ($where) {
                $query = $this->orderQuerySelect($query, 'finished');

                if ($where['start_date']) {
                    $query = $query->where('add_time', '>=', $where['start_date']);
                }
                if ($where['end_date']) {
                    $query = $query->where('add_time', '<=', $where['end_date']);
                }

                $query->where('ru_id', $where['ru_id']);
            });

        $res = $res->with([
            'getOrder' => function ($query) {
                $query->select('order_id', 'order_status');
            }
        ]);
        $res = $res->groupBy('goods_id');
        $res = $res->orderBy($filter['sort_by'], $filter['sort_order']);

        if ($is_pagination) {
            if ($filter['start'] > 0) {
                $res = $res->skip($filter['start']);
            }

            if ($filter['page_size'] > 0) {
                $res = $res->take($filter['page_size']);
            }
        }

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $ru_id = BaseRepository::getKeyPluck($res, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($res as $key => $item) {
                if (isset($item['order_id']) && $item['order_id']) {
                    $res[$key]['order_status'] = $item['get_order']['order_status'] ?? 0;
                    $res[$key]['wvera_price'] = $this->dscRepository->getPriceFormat($item['goods_num'] ? $item['turnover'] / $item['goods_num'] : 0);
                    $res[$key]['short_name'] = $this->dscRepository->subStr($item['goods_name'], 30, true);
                    $res[$key]['turnover'] = $this->dscRepository->getPriceFormat($item['turnover']);
                    $res[$key]['taxis'] = $key + 1;
                    $res[$key]['ru_name'] = $merchantList[$item['ru_id']]['shop_name'] ?? '';
                } else {
                    unset($res[$key]);
                }
            }
        }

        $arr = ['sales_order_data' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /**
     * 获取广告数据
     */
    public function getAdsStats()
    {
        $res = Ad::select('ad_id', 'ad_name')
            ->whereHasIn('getAdsense');

        $res = $res->with([
            'getAdsense',
        ]);

        $res = $res->orderBy('ad_name', 'desc');

        $res = BaseRepository::getToArrayGet($res);

        $ads_stats = [];
        if ($res) {
            foreach ($res as $rows) {
                $rows = BaseRepository::getArrayMerge($rows, $rows['get_adsense']);

                /* 获取当前广告所产生的订单总数 */
                $rows['referer'] = addslashes($rows['referer']);

                $count = OrderInfo::where('from_ad', $rows['ad_id'])
                    ->where('referer', $rows['referer'])
                    ->count();
                $rows['order_num'] = $count;

                /* 当前广告所产生的已完成的有效订单 */
                $count = OrderInfo::where('from_ad', $rows['ad_id'])
                    ->where('referer', $rows['referer']);

                $count = $this->orderQuerySelect($count, 'finished');

                $count = $count->count();

                $rows['order_confirm'] = $count;

                $ads_stats[] = $rows;
            }
        }

        return $ads_stats;
    }

    /**
     * 站外JS投放商品的统计数据
     */
    public function getGoodsStats()
    {
        $goods_res = Adsense::where('from_ad', '-1')
            ->orderBy('referer', 'desc');
        $goods_res = BaseRepository::getToArrayGet($goods_res);

        $goods_stats = [];
        if ($goods_res) {
            foreach ($goods_res as $rows) {

                /* 获取当前广告所产生的订单总数 */
                $rows['referer'] = addslashes($rows['referer']);
                $rows2['order_num'] = OrderInfo::where('referer', $rows['referer'])->count();

                /* 当前广告所产生的已完成的有效订单 */
                $order_confirm = OrderInfo::where('referer', $rows['referer']);
                $order_confirm = $this->orderQuerySelect($order_confirm, 'finished');
                $rows2['order_confirm'] = $order_confirm->count();

                $rows['ad_name'] = $GLOBALS['_LANG']['adsense_js_goods'];

                $goods_stats[] = $rows;
            }
        }

        return $goods_stats;
    }

    /*------------------------------------------------------ */
    //--订单统计需要的函数
    /*------------------------------------------------------ */
    /**
     * 取得订单概况数据(包括订单的几种状态)
     * @param       $start_date    开始查询的日期
     * @param       $end_date      查询的结束日期
     * @param       $type          查询类型：0为订单数量，1为销售额
     * @return      $order_info    订单概况数据
     */
    public function getOrderInfoStats($start_date, $end_date, $type = 0)
    {
        $order_info = [];
        $adminru = get_admin_ru_id();

        /* 未确认订单数 */
        $order_info['unconfirmed_num'] = $this->unconfirmedNum($start_date, $end_date, $adminru['ru_id'], $type);

        /* 已确认订单数 */
        $order_info['confirmed_num'] = $this->confirmedNum($start_date, $end_date, $adminru['ru_id'], $type);

        /* 已成交订单数 */
        $order_info['succeed_num'] = $this->succeedNum($start_date, $end_date, $adminru['ru_id'], $type);

        /* 无效或已取消订单数 */
        $order_info['invalid_num'] = $this->invalidNum($start_date, $end_date, $adminru['ru_id'], $type);

        return $order_info;
    }

    /**
     * 未确认订单数
     *
     * @param int $start_date
     * @param int $end_date
     * @param int $ru_id
     * @param int $type
     * @return int
     */
    public function unconfirmedNum($start_date = 0, $end_date = 0, $ru_id = 0, $type = 0)
    {
        /* 未确认订单数 */
        $time = $end_date + 86400;

        if ($type == 1) {
            $unconfirmed_num = OrderInfo::selectRaw("IFNULL(SUM('" . $this->orderTotalField() . "'), 0) as num");
        } else {
            $unconfirmed_num = OrderInfo::whereRaw(1);
        }

        $unconfirmed_num = $unconfirmed_num->where('order_status', OS_UNCONFIRMED)
            ->where('add_time', '>=', $start_date)
            ->where('add_time', '<', $time)
            ->where('main_count', 0);

        $unconfirmed_num = $unconfirmed_num->where('ru_id', $ru_id);

        if ($type == 1) {
            $num = $unconfirmed_num->value('num');
            $num = $num ? $num : 0;
        } else {
            $num = $unconfirmed_num->count();
        }

        return $num;
    }

    /**
     * 已确认订单数
     *
     * @param int $start_date
     * @param int $end_date
     * @param int $ru_id
     * @param int $type
     * @return int
     */
    public function confirmedNum($start_date = 0, $end_date = 0, $ru_id = 0, $type = 0)
    {
        /* 已确认订单数 */
        $time = $end_date + 86400;

        if ($type == 1) {
            $confirmed_num = OrderInfo::selectRaw("IFNULL(SUM('" . $this->orderTotalField() . "'), 0) as num");
        } else {
            $confirmed_num = OrderInfo::whereRaw(1);
        }

        $confirmed_num = $confirmed_num->where('order_status', OS_CONFIRMED)
            ->whereNotIn('shipping_status', [SS_SHIPPED, SS_RECEIVED])
            ->whereNotIn('pay_status', [PS_PAYED, PS_PAYING])
            ->where('add_time', '>=', $start_date)
            ->where('add_time', '<', $time)
            ->where('main_count', 0);

        $confirmed_num = $confirmed_num->where('ru_id', $ru_id);

        if ($type == 1) {
            $num = $confirmed_num->value('num');
            $num = $num ? $num : 0;
        } else {
            $num = $confirmed_num->count();
        }

        return $num;
    }

    /**
     * 已成交订单数
     *
     * @param int $start_date
     * @param int $end_date
     * @param int $ru_id
     * @param int $type
     * @return int
     */
    public function succeedNum($start_date = 0, $end_date = 0, $ru_id = 0, $type = 0)
    {
        $time = $end_date + 86400;

        if ($type == 1) {
            $succeed_num = OrderInfo::selectRaw("IFNULL(SUM('" . $this->orderTotalField() . "'), 0) as num");
        } else {
            $succeed_num = OrderInfo::whereRaw(1);
        }

        $succeed_num = $succeed_num->where('add_time', '>=', $start_date)
            ->where('add_time', '<', $time)
            ->where('main_count', 0);

        $succeed_num = $this->orderQuerySelect($succeed_num, 'finished');

        $succeed_num = $succeed_num->where('ru_id', $ru_id);

        if ($type == 1) {
            $num = $succeed_num->value('num');
            $num = $num ? $num : 0;
        } else {
            $num = $succeed_num->count();
        }

        return $num;
    }

    /**
     * 无效或已取消订单数
     *
     * @param int $start_date
     * @param int $end_date
     * @param int $ru_id
     * @param int $type
     * @return int
     */
    public function invalidNum($start_date = 0, $end_date = 0, $ru_id = 0, $type = 0)
    {
        /* 无效或已取消订单数 */
        $time = $end_date + 86400;

        if ($type == 1) {
            $invalid_num = OrderInfo::selectRaw("IFNULL(SUM('" . $this->orderTotalField() . "'), 0) as num");
        } else {
            $invalid_num = OrderInfo::whereRaw(1);
        }

        $invalid_num = $invalid_num->whereIn('order_status', [OS_CANCELED, OS_INVALID])
            ->where('add_time', '>=', $start_date)
            ->where('add_time', '<', $time)
            ->where('main_count', 0);

        $invalid_num = $invalid_num->where('ru_id', $ru_id);

        if ($type == 1) {
            $num = $invalid_num->value('num');
            $num = $num ? $num : 0;
        } else {
            $num = $invalid_num->count();
        }

        return $num;
    }

    /**
     * @param string $start_date
     * @param string $end_date
     * @param int $ru_id
     * @return mixed
     */
    public function getPayTypeStats($start_date = '', $end_date = '')
    {
        $adminru = get_admin_ru_id();

        $where = [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'adminru' => $adminru
        ];
        $res = Payment::whereHasIn('getOrder', function ($query) use ($where) {
            $query = $this->orderQuerySelect($query, 'finished');
            $query = $query->where('add_time', '>=', $where['start_date'])
                ->where('add_time', '<=', $where['end_date']);
            $query->where('ru_id', $where['adminru']['ru_id'])
                ->where('main_count', 0);
        });

        $res = BaseRepository::getToArrayGet($res);

        return $res;
    }

    /**
     * @param string $start_date
     * @param string $end_date
     * @return mixed
     */
    public function getShippingTypeStats($start_date = '', $end_date = '')
    {
        $adminru = get_admin_ru_id();

        $where = [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'adminru' => $adminru
        ];
        $res = Shipping::whereHasIn('getOrder', function ($query) use ($where) {
            $query = $this->orderQuerySelect($query, 'finished');
            $query = $query->where('add_time', '>=', $where['start_date'])
                ->where('add_time', '<=', $where['end_date']);
            $query->where('ru_id', $where['adminru']['ru_id'])
                ->where('main_count', 0);
        });

        $res = BaseRepository::getToArrayGet($res);

        return $res;
    }

    /*------------------------------------------------------ */
    //--会员排行需要的函数
    /*------------------------------------------------------ */
    /*
     * 取得会员订单量/购物额排名统计数据
     * @param   bool  $is_pagination  是否分页
     * @return  array   取得会员订单量/购物额排名统计数据
     */
    public function getUserOrderInfo($is_pagination = true)
    {
        $adminru = get_admin_ru_id();

        /* 时间参数 */
        $filter['start_date'] = empty($_REQUEST['start_date']) ? TimeRepository::getLocalStrtoTime('-7 days') : TimeRepository::getLocalStrtoTime($_REQUEST['start_date']);
        $filter['end_date'] = empty($_REQUEST['end_date']) ? TimeRepository::getLocalStrtoTime('today') : TimeRepository::getLocalStrtoTime($_REQUEST['end_date']);
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'order_num' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter['ru_id'] = $adminru['ru_id'];

        $row = Users::whereRaw(1);

        $res = $record_count = $row;

        $filter['record_count'] = $record_count->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        $res = $res->withCount([
            'getOrder as order_num' => function ($query) use ($filter) {
                if ($filter['ru_id'] > 0) {
                    $query = $query->where('ru_id', $filter['ru_id']);
                }

                $query = $this->orderQuerySelect($query, 'finished');

                if ($filter['start_date']) {
                    $query = $query->where('add_time', '>=', $filter['start_date']);
                }
                if ($filter['end_date']) {
                    $query->where('add_time', '<=', $filter['end_date']);
                }
            }
        ]);

        $res = $res->with([
            'getOrderList' => function ($query) use ($filter) {
                if ($filter['ru_id'] > 0) {
                    $query = $query->where('ru_id', $filter['ru_id']);
                }

                $query = $this->orderQuerySelect($query, 'finished');

                if ($filter['start_date']) {
                    $query = $query->where('add_time', '>=', $filter['start_date']);
                }
                if ($filter['end_date']) {
                    $query->where('add_time', '<=', $filter['end_date']);
                }
            }
        ]);

        $res = $res->orderBy($filter['sort_by'], $filter['sort_order'])->orderBy('user_id', 'ASC');

        if ($is_pagination) {
            if ($filter['start'] > 0) {
                $res = $res->skip($filter['start']);
            }

            if ($filter['page_size'] > 0) {
                $res = $res->take($filter['page_size']);
            }
        }

        $res = BaseRepository::getToArrayGet($res);

        $user_orderinfo = [];
        if ($res) {
            foreach ($res as $items) {
                $turnover = 0;
                $order = $items['get_order_list'];
                if ($order) {
                    /* 计算订单各种费用之和的语句 */
                    foreach ($order as $k => $v) {
                        $turnover += $v['goods_amount'] + $v['tax'] + $v['shipping_fee'] + $v['insure_fee'] + $v['pay_fee'] + $v['pack_fee'] + $v['card_fee'];
                    }
                }

                if (config('shop.show_mobile') == 0) {
                    $items['mobile_phone'] = $this->dscRepository->stringToStar($items['mobile_phone']);
                    $items['user_name'] = $this->dscRepository->stringToStar($items['user_name']);
                    $items['email'] = $this->dscRepository->stringToStar($items['email']);
                }

                $items['turnover'] = $turnover;

                $user_orderinfo[] = $items;
            }
        }

        $arr = [
            'user_orderinfo' => $user_orderinfo,
            'filter' => $filter,
            'page_count' => $filter['page_count'],
            'record_count' => $filter['record_count']
        ];

        return $arr;
    }

    /**
     * 取得会员总数
     *
     * @return mixed
     */
    public function GuestStatsUserCount()
    {
        $user_num = Users::count();
        return $user_num;
    }

    /**
     * 有过订单的会员数
     *
     * @return mixed
     */
    public function GuestStatsUserOrderCount()
    {
        $mun = Users::whereHasIn('getOrder', function ($query) {
            $query = $query->where('main_count', 0);
            $this->orderQuerySelect($query, 'finished');
        });

        $mun = $mun->count();

        return $mun;
    }

    /**
     * 会员订单总数和订单总购物额
     *
     * @return mixed
     */
    public function GuestStatsUserOrderAll()
    {
        /* 计算订单各种费用之和的语句 */
        $order = OrderInfo::selectRaw("COUNT(*) AS order_num, SUM(" . $this->orderTotalField() . ") AS turnover ")
            ->where('main_count', 0);
        $order = CommonRepository::constantMaxId($order, 'user_id');
        $order = $this->orderQuerySelect($order, 'finished');
        $order = BaseRepository::getToArrayFirst($order);

        $order['turnover'] = isset($order['turnover']) ? floatval($order['turnover']) : 0;
        $order['order_num'] = $order['order_num'] ?? 0;

        return $order;
    }

    /**
     * 匿名会员订单总数和总购物额
     *
     * @return mixed
     */
    public function GguestAllOrder()
    {
        /* 计算订单各种费用之和的语句 */
        $order = OrderInfo::selectRaw("COUNT(*) AS order_num, SUM(" . $this->orderTotalField() . ") AS turnover ")
            ->where('user_id', 0)
            ->where('main_count', 0);
        $order = $this->orderQuerySelect($order, 'finished');
        $order = BaseRepository::getToArrayFirst($order);

        $order['turnover'] = isset($order['turnover']) ? floatval($order['turnover']) : 0;
        $order['order_num'] = $order['order_num'] ?? 0;

        return $order;
    }

    /**
     * 取得访问和购买次数统计数据
     *
     * @param int $ru_id
     * @param int $cat_id 分类编号
     * @param int $brand_id 品牌编号
     * @param int $show_num 显示个数
     * @return array 访问购买比例数据
     * @throws \Exception
     */
    public function clickSoldInfo($ru_id = 0, $cat_id = 0, $brand_id = 0, $show_num = 0)
    {
        $res = Goods::whereRaw(1);

        if ($ru_id > 0) {
            $res = $res->where('user_id', $ru_id);
        }

        if ($cat_id > 0) {
            $cats = app(CategoryService::class)->getCatListChildren($cat_id);
            $res = $res->whereIn('cat_id', $cats);
        }
        if ($brand_id > 0) {
            $res = $res->where('brand_id', $brand_id);
        }

        $res = $res->whereHasIn('getOrderGoods', function ($query) {
            $query->whereHasIn('getOrder', function ($query) {
                $this->orderQuerySelect($query, 'finished');
            });
        });

        $res = $res->withCount('getOrderGoods as sold_times');

        $res = $res->orderBy('click_count', 'desc');

        $res = $res->take($show_num);

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {

            $ru_id = BaseRepository::getKeyPluck($res, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($res as $key => $item) {
                $item['ru_id'] = $item['user_id'];

                $key = $key + 1;
                $arr[$key] = $item;
                if ($item['click_count'] <= 0) {
                    $arr[$key]['scale'] = 0;
                } else {
                    /* 每一百个点击的订单比率 */
                    $arr[$key]['scale'] = sprintf("%0.2f", ($item['sold_times'] / $item['click_count']) * 100) . '%';
                }
                $arr[$key]['ru_name'] = $merchantList[$item['ru_id']]['shop_name'] ?? '';
            }
        }

        return $arr;
    }

    /**
     * 会员地区统计
     *
     * @param int $page
     * @return array
     */
    public function userAreaStats($page = 0)
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'userAreaStats';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 过滤信息 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        $filter['start_date'] = empty($_REQUEST['start_date']) ? '' : (strpos($_REQUEST['start_date'], '-') > 0 ? TimeRepository::getLocalStrtoTime($_REQUEST['start_date']) : $_REQUEST['start_date']);
        $filter['end_date'] = empty($_REQUEST['end_date']) ? '' : (strpos($_REQUEST['end_date'], '-') > 0 ? TimeRepository::getLocalStrtoTime($_REQUEST['end_date']) : $_REQUEST['end_date']);
        $filter['shop_categoryMain'] = empty($_REQUEST['shop_categoryMain']) ? 0 : intval($_REQUEST['shop_categoryMain']);
        $filter['shopNameSuffix'] = empty($_REQUEST['shopNameSuffix']) ? '' : trim($_REQUEST['shopNameSuffix']);
        $filter['area'] = empty($_REQUEST['area']) ? '' : trim($_REQUEST['area']);

        /* 默认信息 */
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'user_num' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        /* 查询语句 */
        $where_o = " WHERE 1 AND o.main_count = 0 AND order_status <> '" . OS_INVALID . "' ";//剔除无效订单

        if ($filter['start_date']) {
            $where_o .= " AND o.add_time >= '$filter[start_date]'";
        }
        if ($filter['end_date']) {
            $where_o .= " AND o.add_time <= '$filter[end_date]'";
        }
        if ($filter['keywords']) {
            $where_o .= " AND (o.consignee LIKE '%" . $filter['keywords'] . "%')";
        }
        if ($filter['area']) {
            $sql = " SELECT region_id FROM " . $GLOBALS['dsc']->table('merchants_region_info') . " WHERE ra_id = '$filter[area]' ";
            $region_ids = $GLOBALS['db']->getCol($sql);
            $where_o .= " AND o.province " . db_create_in($region_ids);
        }

        /* 分页大小 */
        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);
        if ($page > 0) {
            $filter['page'] = $page;
        }

        $page_size = request()->cookie('dsccp_page_size');
        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        } elseif (intval($page_size) > 0) {
            $filter['page_size'] = intval($page_size);
        } else {
            $filter['page_size'] = 15;
        }

        /* 分组 */
        $groupBy = " GROUP BY o.district ";

        /* 关联查询 */
        $leftJoin = '';
        $leftJoin .= " LEFT JOIN " . $GLOBALS['dsc']->table('region') . " AS r ON r.region_id = o.district ";

        /* 记录总数 */
        $sql = "SELECT o.district FROM " . $GLOBALS['dsc']->table('order_info') . " AS o " .
            $leftJoin .
            $where_o . $groupBy;

        $record_count = count($GLOBALS['db']->getAll($sql));

        $filter['record_count'] = $record_count;
        $filter['page_count'] = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 查询 */
        $sql = "SELECT o.district, r.region_name as district_name, " .
            statistical_field_user_num() . " AS user_num, " . //会员数量
            statistical_field_order_num() . " AS total_num, " . //订单数量
            statistical_field_total_fee() . " AS total_fee " . //下单金额
            " FROM " . $GLOBALS['dsc']->table('order_info') . " AS o " .
            $leftJoin .
            $where_o . $groupBy .
            " ORDER BY $filter[sort_by] $filter[sort_order] " .
            " LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ",$filter[page_size]";

        $row = $GLOBALS['db']->getAll($sql);

        /* 格式化数据 */
        if ($row) {
            foreach ($row as $key => $value) {
                $row[$key]['formated_total_fee'] = $this->dscRepository->getPriceFormat($value['total_fee']);
                $city_id = get_table_date('region', "region_id='$value[district]'", array('parent_id'), 2);
                $row[$key]['city_name'] = get_table_date('region', "region_id='$city_id'", array('region_name'), 2);
                $province_id = get_table_date('region', "region_id='$city_id'", array('parent_id'), 2);
                $row[$key]['province_name'] = get_table_date('region', "region_id='$province_id'", array('region_name'), 2);
            }
        }

        $arr = array('orders' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

        return $arr;
    }

    /**
     * 会员销售统计
     *
     * @param int $page
     * @return array
     */
    public function userSaleStats($page = 0)
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'userSaleStats';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 过滤信息 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        $filter['start_date'] = empty($_REQUEST['start_date']) ? '' : (strpos($_REQUEST['start_date'], '-') > 0 ? TimeRepository::getLocalStrtoTime($_REQUEST['start_date']) : $_REQUEST['start_date']);
        $filter['end_date'] = empty($_REQUEST['end_date']) ? '' : (strpos($_REQUEST['end_date'], '-') > 0 ? TimeRepository::getLocalStrtoTime($_REQUEST['end_date']) : $_REQUEST['end_date']);

        /* 默认信息 */
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'total_fee' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        /* 分页大小 */
        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);
        if ($page > 0) {
            $filter['page'] = $page;
        }

        $page_size = request()->cookie('dsccp_page_size');
        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        } elseif (intval($page_size) > 0) {
            $filter['page_size'] = intval($page_size);
        } else {
            $filter['page_size'] = 15;
        }

        $row = OrderInfo::query()->distinct()->select('user_id')->where('main_count', 0);

        $row = $row->withCount([
            //下单数量
            'getUserOrder as total_num' => function ($query) use ($filter) {
                if ($filter['start_date'] && $filter['end_date']) {
                    $query = $query->whereBetween('add_time', [$filter['start_date'], $filter['end_date']]);
                }
                $query->select(DB::raw("count(order_id) as total_num"));
            },
            //下单金额
            'getUserOrder as total_fee' => function ($query) use ($filter) {
                if ($filter['start_date'] && $filter['end_date']) {
                    $query = $query->whereBetween('add_time', [$filter['start_date'], $filter['end_date']]);
                }
                $query->select(DB::raw("SUM( goods_amount + tax + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee ) AS total_fee"));
            },
            //有效下单量
            'getUserOrder as valid_num' => function ($query) use ($filter) {
                if ($filter['start_date'] && $filter['end_date']) {
                    $query = $query->whereBetween('add_time', [$filter['start_date'], $filter['end_date']]);
                }
                $query->select(DB::raw("COUNT(DISTINCT IF((order_status!=" . OS_INVALID . " AND order_status!=" . OS_CANCELED . "), order_id, NULL)) as valid_num"));
            },
            //有效下单金额
            'getUserOrder as valid_fee' => function ($query) use ($filter) {
                if ($filter['start_date'] && $filter['end_date']) {
                    $query = $query->whereBetween('add_time', [$filter['start_date'], $filter['end_date']]);
                }
                $query->select(DB::raw("SUM(IF((order_status!=" . OS_INVALID . " AND order_status!=" . OS_CANCELED . "), " . OrderService::orderAmountField() . ", 0)) as valid_fee"));
            },
            //退款订单数量
            'getUserOrderReturn as return_num' => function ($query) use ($filter) {
                if ($filter['start_date'] && $filter['end_date']) {
                    $query = $query->whereBetween('return_time', [$filter['start_date'], $filter['end_date']]);
                }
                $query->select(DB::raw("COUNT(ret_id) as return_num"))->where('refound_status', 1)->whereIn('return_type', [1, 3]); //退换货， 已退款
            },
            //退款订单金额
            'getUserOrderReturn as return_fee' => function ($query) use ($filter) {
                if ($filter['start_date'] && $filter['end_date']) {
                    $query = $query->whereBetween('return_time', [$filter['start_date'], $filter['end_date']]);
                }
                $query->select(DB::raw("SUM(actual_return) as return_fee"))->where('refound_status', 1)->whereIn('return_type', [1, 3]); //退换货， 已退款
            }
        ]);

        if ($filter['start_date'] && $filter['end_date']) {
            $row = $row->whereBetween('add_time', [$filter['start_date'], $filter['end_date']]);
        }

        if ($filter['keywords']) {
            $keywords = $this->dscRepository->mysqlLikeQuote($filter['keywords']);
            $userIdList = Users::query()->where('user_name', 'like', $keywords)->pluck('user_id');
            $userIdList = BaseRepository::getToArray($userIdList);
            if ($userIdList) {
                $row = $row->whereIn('user_id', $userIdList);
            }
        }

        $res = $record_count = $row;

        $filter['record_count'] = $record_count->count('user_id');
        $filter['page_count'] = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        $start = ($filter['page'] - 1) * $filter['page_size'];
        $page_size = $filter['page_size'];

        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($page_size > 0) {
            $res = $res->take($page_size);
        }

        $res = $res->orderBy($filter['sort_by'], $filter['sort_order']);

        $res = BaseRepository::getToArrayGet($res);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 格式化数据 */
        if ($res) {

            $userIdList = BaseRepository::getKeyPluck($res, 'user_id');
            $userList = UserDataHandleService::userDataList($userIdList, ['user_id', 'user_name', 'nick_name', 'mobile_phone']);

            foreach ($res as $key => $value) {
                $value['total_fee'] = $value['total_fee'] ?? 0;
                $value['valid_fee'] = $value['valid_fee'] ?? 0;
                $value['return_fee'] = $value['return_fee'] ?? 0;

                $value['total_num'] = $value['total_num'] ?? 0;
                $value['valid_num'] = $value['valid_num'] ?? 0;
                $value['return_num'] = $value['return_num'] ?? 0;

                $res[$key]['user_name'] = $userList[$value['user_id']]['user_name'] ?? '';
                $res[$key]['nick_name'] = $userList[$value['user_id']]['nick_name'] ?? '';
                $res[$key]['mobile_phone'] = $userList[$value['user_id']]['mobile_phone'] ?? '';

                $res[$key]['formated_total_fee'] = $this->dscRepository->getPriceFormat($value['total_fee']);
                $res[$key]['formated_valid_fee'] = $this->dscRepository->getPriceFormat($value['valid_fee']);
                $res[$key]['formated_return_fee'] = $this->dscRepository->getPriceFormat($value['return_fee']);

                if (config('shop.show_mobile') == 0) {
                    $res[$key]['user_name'] = $this->dscRepository->stringToStar($value['user_name']);
                    $res[$key]['mobile_phone'] = $this->dscRepository->stringToStar($value['mobile_phone']);
                }
            }
        }

        $arr = array('orders' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

        return $arr;
    }

    /**
     * 店铺销售统计
     *
     * @param int $page
     * @return array
     * @throws \Exception
     */
    public function shopSaleStats($page = 0)
    {
        /* 过滤信息 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        $filter['start_date'] = empty($_REQUEST['start_date']) ? '' : (strpos($_REQUEST['start_date'], '-') > 0 ? TimeRepository::getLocalStrtoTime($_REQUEST['start_date']) : $_REQUEST['start_date']);
        $filter['end_date'] = empty($_REQUEST['end_date']) ? '' : (strpos($_REQUEST['end_date'], '-') > 0 ? TimeRepository::getLocalStrtoTime($_REQUEST['end_date']) : $_REQUEST['end_date']);
        $filter['shop_categoryMain'] = empty($_REQUEST['shop_categoryMain']) ? 0 : intval($_REQUEST['shop_categoryMain']);
        $filter['shopNameSuffix'] = empty($_REQUEST['shopNameSuffix']) ? '' : trim($_REQUEST['shopNameSuffix']);

        /* 默认信息 */
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'spi.ru_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'ASC' : trim($_REQUEST['sort_order']);

        if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        /* 查询语句 */
        $where_msi = ' WHERE 1 AND o.main_count = 0 ';
        $where_o = '';

        if ($filter['start_date']) {
            $where_o .= " AND o.add_time >= '$filter[start_date]'";
        }
        if ($filter['end_date']) {
            $where_o .= " AND o.add_time <= '$filter[end_date]'";
        }

        if ($filter['keywords']) {

            $keywords = str_replace([$GLOBALS['_LANG']['flagship_store'], $GLOBALS['_LANG']['exclusive_shop'], $GLOBALS['_LANG']['franchised_store']], '', $filter['keywords']);
            $keywords = $this->dscRepository->mysqlLikeQuote($keywords);

            $where_msi .= " AND (msi.rz_shop_name LIKE '%" . $keywords . "%' OR spi.shop_name LIKE '%" . $keywords . "%')";
        }


        if ($filter['shop_categoryMain']) {
            $where_msi .= " AND msi.shop_category_main = '$filter[shop_categoryMain]'";
        }
        if ($filter['shopNameSuffix']) {
            $where_msi .= " AND msi.shop_name_suffix = '$filter[shopNameSuffix]'";
        }

        /* 分页大小 */
        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);
        if ($page > 0) {
            $filter['page'] = $page;
        }

        $page_size = request()->cookie('dsccp_page_size');
        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        } elseif (intval($page_size) > 0) {
            $filter['page_size'] = intval($page_size);
        } else {
            $filter['page_size'] = 15;
        }

        /* 分组 */
        $groupBy = " GROUP BY spi.ru_id ";

        /* 关联查询 */
        $leftJoin = " LEFT JOIN " . $GLOBALS['dsc']->table('order_info') . " AS o ON o.ru_id = spi.ru_id ";
        $leftJoin .= " LEFT JOIN " . $GLOBALS['dsc']->table('order_return') . " AS re ON re.order_id = o.order_id ";
        $leftJoin .= " LEFT JOIN " . $GLOBALS['dsc']->table('merchants_shop_information') . " AS msi ON msi.user_id = spi.ru_id ";

        /* 记录总数 */
        $sql = "SELECT spi.ru_id FROM " . $GLOBALS['dsc']->table('seller_shopinfo') . " AS spi " .
            $leftJoin .
            $where_msi . $where_o . $groupBy;

        $record_count = count($GLOBALS['db']->getAll($sql));

        $filter['record_count'] = $record_count;
        $filter['page_count'] = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */
        $sql = "SELECT spi.ru_id, " .
            statistical_field_order_num() . " AS total_order_num, " . //下单量
            statistical_field_user_num() . " AS total_user_num, " .  //下单会员总数
            statistical_field_return_num() . " AS total_return_num, " .  //退款订单数量
            statistical_field_valid_num() . " AS total_valid_num, " . //有效下单量
            statistical_field_total_fee() . " AS total_fee, " . //下单金额
            statistical_field_valid_fee() . " AS valid_fee, " . //有效下单金额
            statistical_field_return_fee() . " AS return_amount " . //退款金额
            " FROM " . $GLOBALS['dsc']->table('seller_shopinfo') . " AS spi " .
            $leftJoin .
            $where_msi . $where_o . $groupBy .
            " ORDER BY $filter[sort_by] $filter[sort_order] " .
            " LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ",$filter[page_size]";

        $row = $GLOBALS['db']->getAll($sql);

        /* 格式化数据 */
        if ($row) {

            $ru_id = BaseRepository::getKeyPluck($row, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($row as $key => $value) {
                $row[$key]['user_name'] = $merchantList[$value['ru_id']]['shop_name'] ?? '';
                $row[$key]['formated_total_fee'] = $this->dscRepository->getPriceFormat($value['total_fee']);
                $row[$key]['formated_valid_fee'] = $this->dscRepository->getPriceFormat($value['valid_fee']);
                $row[$key]['formated_return_amount'] = $this->dscRepository->getPriceFormat($value['return_amount']);
            }
        }

        $arr = array('orders' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

        return $arr;
    }

    /**
     * 店铺地区统计
     *
     * @param int $page
     * @return array
     */
    public function shopAreaStats($page = 0)
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'shopAreaStats';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 过滤信息 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        $filter['start_date'] = empty($_REQUEST['start_date']) ? '' : (strpos($_REQUEST['start_date'], '-') > 0 ? TimeRepository::getLocalStrtoTime($_REQUEST['start_date']) : $_REQUEST['start_date']);
        $filter['end_date'] = empty($_REQUEST['end_date']) ? '' : (strpos($_REQUEST['end_date'], '-') > 0 ? TimeRepository::getLocalStrtoTime($_REQUEST['end_date']) : $_REQUEST['end_date']);
        $filter['shop_categoryMain'] = empty($_REQUEST['shop_categoryMain']) ? 0 : intval($_REQUEST['shop_categoryMain']);
        $filter['shopNameSuffix'] = empty($_REQUEST['shopNameSuffix']) ? '' : trim($_REQUEST['shopNameSuffix']);
        $filter['area'] = empty($_REQUEST['area']) ? '' : trim($_REQUEST['area']);

        /* 默认信息 */
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'store_num' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        /* 查询语句 */
        $where_spi = ' WHERE 1 ';
        $where_msi = '';

        if ($filter['start_date']) {
            $where_msi .= " AND msi.add_time >= '$filter[start_date]'";
        }
        if ($filter['end_date']) {
            $where_msi .= " AND msi.add_time <= '$filter[end_date]'";
        }
        if ($filter['keywords']) {
            $where_msi .= " AND (msi.rz_shop_name LIKE '%" . $filter['keywords'] . "%')";
        }
        if ($filter['shop_categoryMain']) {
            $where_msi .= " AND msi.shop_category_main = '$filter[shop_categoryMain]'";
        }
        if ($filter['shopNameSuffix']) {
            $where_msi .= " AND msi.shop_name_suffix = '$filter[shopNameSuffix]'";
        }
        if ($filter['area']) {
            $sql = " SELECT region_id FROM " . $GLOBALS['dsc']->table('merchants_region_info') . " WHERE ra_id = '$filter[area]' ";
            $region_ids = $GLOBALS['db']->getCol($sql);
            $where_spi .= " AND spi.province " . db_create_in($region_ids);
        }

        /* 分页大小 */
        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);
        if ($page > 0) {
            $filter['page'] = $page;
        }

        $page_size = request()->cookie('dsccp_page_size');
        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        } elseif (intval($page_size) > 0) {
            $filter['page_size'] = intval($page_size);
        } else {
            $filter['page_size'] = 15;
        }

        /* 分组 */
        $groupBy = " GROUP BY spi.district ";

        /* 关联查询 */
        $leftJoin = '';
        $leftJoin .= " LEFT JOIN " . $GLOBALS['dsc']->table('merchants_shop_information') . " AS msi ON msi.user_id = spi.ru_id ";
        $leftJoin .= " LEFT JOIN " . $GLOBALS['dsc']->table('region') . " AS r ON r.region_id = spi.district ";

        /* 记录总数 */
        $sql = "SELECT spi.district FROM " . $GLOBALS['dsc']->table('seller_shopinfo') . " AS spi " .
            $leftJoin .
            $where_spi . $where_msi . $groupBy;

        $record_count = count($GLOBALS['db']->getAll($sql));

        $filter['record_count'] = $record_count;
        $filter['page_count'] = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 查询 */
        $sql = "SELECT spi.district, r.region_name as district_name, " .
            statistical_field_shop_num() . " AS store_num " . //店铺数量
            " FROM " . $GLOBALS['dsc']->table('seller_shopinfo') . " AS spi " .
            $leftJoin .
            $where_spi . $where_msi . $groupBy .
            " ORDER BY $filter[sort_by] $filter[sort_order] " .
            " LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ",$filter[page_size]";

        $row = $GLOBALS['db']->getAll($sql);

        /* 格式化数据 */
        foreach ($row as $key => $value) {
            $city_id = get_table_date('region', "region_id='$value[district]'", array('parent_id'), 2);
            $row[$key]['city_name'] = get_table_date('region', "region_id='$city_id'", array('region_name'), 2);
            $province_id = get_table_date('region', "region_id='$city_id'", array('parent_id'), 2);
            $row[$key]['province_name'] = get_table_date('region', "region_id='$province_id'", array('region_name'), 2);
        }

        $arr = array('orders' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

        return $arr;
    }

    /**
     * 生成查询订单的sql
     *
     * @param $res 对象
     * @param string $type
     * @return mixed
     */
    public function orderQuerySelect($res, $type = 'finished')
    {

        /**
         * 已完成订单|finished
         * 已确认订单|queren
         * 已确认收货订单|confirm_take
         * 待确认收货订单|confirm_wait_goods
         * 待发货订单|await_ship
         * 待付款订单|await_pay
         * 未确认订单|unconfirmed
         * 未付款未发货订单：管理员可操作|unprocessed
         * 未付款未发货订单：管理员可操作|unpay_unship
         * 已发货订单：不论是否付款|shipped
         * 已付款订单：只要不是未发货（销量统计用）|real_pay
         */

        if ($type == 'finished') {
            $order_status = [
                defined(OS_CONFIRMED) ? OS_CONFIRMED : 1,
                defined(OS_SPLITED) ? OS_SPLITED : 5,
                defined(OS_RETURNED_PART) ? OS_RETURNED_PART : 7,
                defined(OS_ONLY_REFOUND) ? OS_ONLY_REFOUND : 8
            ];

            $shipping_status = [
                defined(SS_RECEIVED) ? SS_RECEIVED : 2
            ];

            $pay_status = [
                defined(PS_PAYED) ? PS_PAYED : 2,
                defined(PS_PAYING) ? PS_PAYING : 1
            ];

            $res = $res->whereIn('order_status', $order_status)
                ->whereIn('shipping_status', $shipping_status)
                ->whereIn('pay_status', $pay_status);
        } elseif ($type == 'queren') {
            $order_status = [
                defined(OS_CONFIRMED) ? OS_CONFIRMED : 1,
                defined(OS_SPLITED) ? OS_SPLITED : 5,
                defined(OS_SPLITING_PART) ? OS_SPLITING_PART : 6
            ];

            $res = $res->whereIn('order_status', $order_status)
                ->where('pay_status', PS_PAYED);
        } elseif ($type == 'confirm_take') {
            $order_status = [
                defined(OS_CONFIRMED) ? OS_CONFIRMED : 1,
                defined(OS_SPLITED) ? OS_SPLITED : 5,
                defined(OS_SPLITING_PART) ? OS_SPLITING_PART : 6,
                defined(OS_RETURNED_PART) ? OS_RETURNED_PART : 7,
                defined(OS_ONLY_REFOUND) ? OS_ONLY_REFOUND : 8
            ];

            $shipping_status = [
                defined(SS_RECEIVED) ? SS_RECEIVED : 2,
                defined(SS_PART_RECEIVED) ? SS_PART_RECEIVED : 7,
            ];

            $pay_status = [
                defined(PS_PAYED) ? PS_PAYED : 2,
                defined(PS_REFOUND_PART) ? PS_REFOUND_PART : 5
            ];

            $res = $res->whereIn('order_status', $order_status)
                ->whereIn('shipping_status', $shipping_status)
                ->whereIn('pay_status', $pay_status);
        }
        if ($type == 'confirm_wait_goods') {
            $order_status = [
                defined(OS_CONFIRMED) ? OS_CONFIRMED : 1,
                defined(OS_SPLITED) ? OS_SPLITED : 5
            ];

            $shipping_status = [
                defined(SS_SHIPPED) ? SS_SHIPPED : 1
            ];

            $pay_status = [
                defined(PS_PAYED) ? PS_PAYED : 2
            ];

            $res = $res->whereIn('order_status', $order_status)
                ->whereIn('shipping_status', $shipping_status)
                ->whereIn('pay_status', $pay_status);
        } elseif ($type == 'await_ship') {
            $order_status = [
                defined(OS_CONFIRMED) ? OS_CONFIRMED : 1,
                defined(OS_SPLITED) ? OS_SPLITED : 5,
                defined(OS_SPLITING_PART) ? OS_SPLITING_PART : 6,
                defined(OS_RETURNED_PART) ? OS_RETURNED_PART : 7
            ];

            //待发货,--191012 update：后台部分发货加入待发货列表，前台部分发货加入待收货列表
            $shipping_status = [
                defined(SS_UNSHIPPED) ? SS_UNSHIPPED : 0,
                defined(SS_PREPARING) ? SS_PREPARING : 3,
                // defined(SS_SHIPPED_PART) ? SS_SHIPPED_PART : 4,
                defined(SS_SHIPPED_ING) ? SS_SHIPPED_ING : 5
            ];

            $pay_status = [
                defined(PS_PAYED) ? PS_PAYED : 2,
                defined(PS_PAYING) ? PS_PAYING : 1,
                defined(PS_REFOUND_PART) ? PS_REFOUND_PART : 5,
            ];

            /* 货到付款 */
            $payList = $this->paymentService->paymentIdList(true);

            $where = [
                'pay_id' => $payList,
                'pay_status' => $pay_status
            ];
            $res = $res->whereIn('order_status', $order_status)
                ->whereIn('shipping_status', $shipping_status)
                ->where(function ($query) use ($where) {
                    $query->whereIn('pay_status', $where['pay_status'])->orWhereIn('pay_id', $where['pay_id']);
                });
        } elseif ($type == 'await_pay') {
            $order_status = [
                defined(OS_CONFIRMED) ? OS_CONFIRMED : 1,
                defined(OS_SPLITED) ? OS_SPLITED : 5
            ];

            $shipping_status = [
                defined(SS_SHIPPED) ? SS_SHIPPED : 1,
                defined(SS_RECEIVED) ? SS_RECEIVED : 2
            ];

            $pay_status = [
                defined(PS_UNPAYED) ? PS_UNPAYED : 0,
                defined(PS_PAYING) ? PS_PAYING : 1
            ];

            $payList = $this->paymentService->paymentIdList(false);

            $where = [
                'pay_id' => $payList,
                'shipping_status' => $shipping_status
            ];
            $res = $res->whereIn('order_status', $order_status)
                ->whereIn('pay_status', $pay_status)
                ->where(function ($query) use ($where) {
                    $query->whereIn('shipping_status', $where['shipping_status'])->orWhereIn('pay_id', $where['pay_id']);
                });
        } elseif ($type == 'unconfirmed') {
            $order_status = defined(OS_UNCONFIRMED) ? OS_UNCONFIRMED : 0;

            $res = $res->where('order_status', $order_status);
        } elseif ($type == 'unprocessed') {
            $order_status = [
                defined(OS_UNCONFIRMED) ? OS_UNCONFIRMED : 0,
                defined(OS_CONFIRMED) ? OS_CONFIRMED : 1
            ];

            $shipping_status = defined(SS_UNSHIPPED) ? SS_UNSHIPPED : 0;

            $pay_status = defined(PS_UNPAYED) ? PS_UNPAYED : 0;

            $res = $res->whereIn('order_status', $order_status)
                ->where('shipping_status', $shipping_status)
                ->where('pay_status', $pay_status);
        } elseif ($type == 'unpay_unship') {
            $order_status = [
                defined(OS_UNCONFIRMED) ? OS_UNCONFIRMED : 0,
                defined(OS_CONFIRMED) ? OS_CONFIRMED : 1
            ];

            $shipping_status = [
                defined(SS_UNSHIPPED) ? SS_UNSHIPPED : 0,
                defined(SS_PREPARING) ? SS_PREPARING : 3
            ];

            $pay_status = defined(PS_UNPAYED) ? PS_UNPAYED : 0;

            $res = $res->whereIn('order_status', $order_status)
                ->whereIn('shipping_status', $shipping_status)
                ->where('pay_status', $pay_status);
        } elseif ($type == 'shipped') {
            $order_status = defined(OS_CONFIRMED) ? OS_CONFIRMED : 1;

            $shipping_status = [
                defined(SS_SHIPPED) ? SS_SHIPPED : 1,
                defined(SS_RECEIVED) ? SS_RECEIVED : 2
            ];

            $res = $res->where('order_status', $order_status)
                ->whereIn('shipping_status', $shipping_status);
        } elseif ($type == 'real_pay') {
            $order_status = [
                defined(OS_CONFIRMED) ? OS_CONFIRMED : 1,
                defined(OS_SPLITED) ? OS_SPLITED : 5,
                defined(OS_SPLITING_PART) ? OS_SPLITING_PART : 6,
                defined(OS_RETURNED_PART) ? OS_RETURNED_PART : 7
            ];

            $shipping_status = defined(SS_UNSHIPPED) ? SS_UNSHIPPED : 0;

            $pay_status = [
                defined(PS_PAYED) ? PS_PAYED : 2,
                defined(PS_PAYING) ? PS_PAYING : 1
            ];

            $res = $res->whereIn('order_status', $order_status)
                ->where('shipping_status', '<>', $shipping_status)
                ->whereIn('pay_status', $pay_status);
        }

        return $res;
    }

    /**
     * 生成查询订单总金额的字段
     *
     * @return string
     */
    public static function orderTotalField()
    {
        return " goods_amount + tax + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee ";
    }

    /**
     * 记录订单操作记录
     *
     * @param string $order_sn 订单编号
     * @param int $order_status 订单状态
     * @param int $shipping_status 配送状态
     * @param int $pay_status 付款状态
     * @param string $note 备注
     * @param string $username 用户自己的操作则为 buyer
     * @param int $place
     * @param int $confirm_take_time 确认收货时间
     * @return bool
     */
    public function orderAction($order_sn = '', $order_status = 0, $shipping_status = 0, $pay_status = 0, $note = '', $username = '', $place = 0, $confirm_take_time = 0)
    {
        if (empty($order_sn)) {
            return false;
        }

        if (!empty($confirm_take_time)) {
            $log_time = $confirm_take_time;
        } else {
            $log_time = TimeRepository::getGmTime();
        }

        if (empty($username)) {
            $admin_id = get_admin_id();

            $username = AdminUser::where('user_id', $admin_id)->value('user_name');
            $username = $username ? $username : '';
        }

        $order_id = OrderInfo::where('order_sn', $order_sn)->value('order_id');
        $order_id = $order_id ? $order_id : 0;

        if ($order_id > 0) {
            $place = !is_null($place) ? $place : '';
            $note = !is_null($note) ? $note : '';

            // 同一订单 订单状态不同
            $count = OrderAction::where('order_id', $order_id)->where('order_status', $order_status)
                ->where('shipping_status', $shipping_status)
                ->where('pay_status', $pay_status);

            if (!empty($note)) {
                $count = $count->where('action_note', $note);
            }

            $count = $count->count('action_id');

            if ($count == 0) {
                $other = [
                    'order_id' => $order_id,
                    'action_user' => $username,
                    'action_place' => $place,
                    'action_note' => $note,
                    'log_time' => $log_time,
                    'order_status' => $order_status,
                    'shipping_status' => $shipping_status,
                    'pay_status' => $pay_status,
                ];

                OrderAction::insert($other);
            }

            return true;
        }
    }

    /**
     * 更新订单对应的 pay_log
     * 如果未支付，修改支付金额；否则，生成新的支付log
     * @param int $order_id 订单id
     */
    public static function updateOrderPayLog($order_id)
    {
        $order_id = intval($order_id);
        if ($order_id > 0) {
            $order_amount = OrderInfo::where('order_id', $order_id)->value('order_amount');

            if (!is_null($order_amount)) {
                $log_id = PayLog::where('order_id', $order_id)
                    ->where('order_type', PAY_ORDER)
                    ->where('is_paid', 0)
                    ->value('log_id');

                if ($log_id > 0) {

                    /* 未付款，更新支付金额 */
                    PayLog::where('log_id', $log_id)->update(['order_amount' => $order_amount]);
                } else {
                    /* 已付款，生成新的pay_log */
                    $other = [
                        'order_id' => $order_id,
                        'order_amount' => $order_amount,
                        'order_type' => PAY_ORDER,
                        'is_paid' => 0
                    ];
                    PayLog::insert($other);
                }
            }
        }
    }

    /**
     * 查找是否存在快照
     *
     * @param string $order_sn
     * @param int $goods_id
     * @return mixed
     */
    public function getFindSnapshot($order_sn = '', $goods_id = 0)
    {
        $trade_id = TradeSnapshot::where('order_sn', $order_sn)
            ->where('goods_id', $goods_id)
            ->value('trade_id');

        return $trade_id;
    }

    /**
     * 执行更新会员订单信息
     *
     * @param int $user_id
     */
    public static function getUserOrderNumServer($user_id = 0)
    {
        Artisan::call('app:user:order', ['user_id' => $user_id]);
    }

    /**
     * 得到新订单号
     * @return  string
     */
    public static function getOrderSn()
    {
        $time = explode(" ", microtime());
        $time = $time[1] . ($time[0] * 1000);
        $time = explode(".", $time);
        $time = isset($time[1]) ? $time[1] : 0;
        $time = TimeRepository::getLocalDate('YmdHis') + $time;

        /* 选择一个随机的方案 */
        mt_srand((double)microtime() * 1000000);
        return $time . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    /**
     * 更新会员订单数量
     *
     * @param int $user_id
     * @param array $other
     */
    public function updateUserOrderNum($user_id = 0, $other = [])
    {
        $count = UserOrderNum::where('user_id', $user_id)->count();

        if ($count > 0) {
            UserOrderNum::where('user_id', $user_id)->update($other);
        } else {
            $other['user_id'] = $user_id;
            UserOrderNum::insert($other);
        }
    }

    /**
     * 统计拼团中数量
     *
     * @param int $user_id
     * @return mixed
     */
    public function teamOrderNum($user_id = 0)
    {
        $time = TimeRepository::getGmTime();
        $res = OrderInfo::where('user_id', $user_id)
            ->where('extension_code', 'team_buy')
            ->where('order_status', '<>', 2);
        $res = BaseRepository::getToArrayGet($res);

        $total = 0;
        if ($res) {
            $team_id = BaseRepository::getKeyPluck($res, 'team_id');
            $teamLogList = TeamDataHandleService::getTeamLogDataList($team_id, ['team_id', 't_id', 'start_time']);
            $t_id = BaseRepository::getKeyPluck($teamLogList, 't_id');

            $team_goods = TeamGoods::query()->select('id as t_id', 'validity_time')->whereIn('id', $t_id)
                ->where('is_team', 1);
            $team_goods = BaseRepository::getToArrayGet($team_goods);

            if ($team_goods) {
                foreach ($team_goods as $key => $row) {
                    $team_goods[$row['t_id']] = $row;
                }
            }
            if (empty($teamLogList) || empty($team_goods)) {
                return 0;
            }
            foreach ($teamLogList as $key => $val) {
                if ($val['t_id'] == $team_goods[$val['t_id']]['t_id']) {
                    $start_time = $val['start_time'] ?? 0;
                    $validity_time = $team_goods[$val['t_id']]['validity_time'] ?? 0;
                    if ($time < ($start_time + $validity_time * 3600)) {
                        $total++;
                    }
                }
            }
        }

        return $total;
    }

    /**
     * 订单使用储值卡金额
     *
     * @param int $order_id
     * @return mixed
     */
    public function orderUseValueCard($order_id = 0)
    {
        $useValList = ValueCardRecord::select('vc_id', 'use_val')
            ->where('order_id', $order_id)
            ->where('add_val', 0);
        $useValList = BaseRepository::getToArrayGet($useValList);
        $use_val = BaseRepository::getArraySum($useValList, ['use_val']);

        $use_val_info = BaseRepository::getArrayFirst($useValList);
        $vc_id = $use_val_info['vc_id'] ?? 0;

        $card = [
            'use_val_card' => $this->dscRepository->changeFloat($use_val),
            'vc_id' => $vc_id
        ];

        return $card;
    }

    /**
     * 订单储值卡退款金额
     *
     * @param int $order_id
     * @param string $order_sn
     * @return array
     */
    public function orderReturnValueCard($order_id = 0, $order_sn = '')
    {
        $useValList = ValueCardRecord::select('vc_id', 'add_val')
            ->where('order_id', $order_id)
            ->where(function ($query) use ($order_sn) {
                $change_desc = sprintf(lang('admin/order.return_card_record'), $order_sn);
                $query->where('change_desc', $change_desc)
                    ->orWhere('add_val', '>', 0);
            });

        $useValList = BaseRepository::getToArrayGet($useValList);
        $return_val_card = BaseRepository::getArraySum($useValList, ['add_val']);

        $use_val_info = BaseRepository::getArrayFirst($useValList);
        $vc_id = $use_val_info['vc_id'] ?? 0;

        $card = [
            'return_val_card' => $this->dscRepository->changeFloat($return_val_card),
            'vc_id' => $vc_id
        ];

        return $card;
    }

    /**
     * 单品已退运费金额
     *
     * @param int $order_id
     * @return mixed
     */
    public function orderReturnShippingFee($order_id = 0)
    {
        $returnShippingTotal = OrderReturn::where('order_id', $order_id)->where('refound_status', 1)->sum('return_shipping_fee');
        return $returnShippingTotal;
    }

    /**
     * 处理整单退款
     *
     * @param int $order_id
     * @param int $ru_id
     * @return array
     */
    public function orderAllreturnGoods($order_id = 0, $ru_id = 0)
    {
        $orderInfo = OrderInfo::select('order_id', 'order_sn', 'surplus', 'integral_money', 'money_paid', 'shipping_fee', 'order_amount')
            ->where('order_id', $order_id);

        if ($ru_id > 0) {
            $orderInfo = $orderInfo->where('ru_id', $ru_id);
        }

        $orderInfo = BaseRepository::getToArrayFirst($orderInfo);

        if (empty($orderInfo)) {
            return [];
        }

        $orderGoods = OrderGoods::select('rec_id', 'order_id', 'goods_price', 'goods_number', 'goods_bonus', 'goods_coupons', 'goods_favourable', 'value_card_discount', 'goods_value_card')
            ->where('order_id', $orderInfo['order_id']);
        $orderGoods = BaseRepository::getToArrayGet($orderGoods);

        $arr = [];
        if ($orderGoods) {

            $order_shipping_fee = $orderInfo['shipping_fee'];

            $goods_amount = 0;
            foreach ($orderGoods as $key => $row) {
                $goods_amount += $row['goods_price'] * $row['goods_number'];
            }

            $pay_order_amount = $orderInfo['surplus'] + $orderInfo['money_paid'];

            /* 获取运费由余额 + 在线支付扣除的实际金额 */
            if ($orderInfo['order_amount'] == 0) {
                $pay_shipping_fee = $order_shipping_fee;
            } else {
                if ($pay_order_amount >= $order_shipping_fee) {
                    $pay_shipping_fee = $order_shipping_fee;
                } else {
                    $pay_shipping_fee = $order_shipping_fee - $pay_order_amount;
                }
            }

            foreach ($orderGoods as $key => $row) {

                $arr[$row['rec_id']]['rec_id'] = $row['rec_id'];
                $should_return = $row['goods_price'] * $row['goods_number'];
                $arr[$row['rec_id']]['should_return'] = $should_return;
                $arr[$row['rec_id']]['return_number'] = $row['goods_number'];

                $arr[$row['rec_id']]['goods_bonus'] = $row['goods_bonus'];
                $arr[$row['rec_id']]['goods_coupons'] = $row['goods_coupons'];
                $arr[$row['rec_id']]['goods_favourable'] = $row['goods_favourable'];
                $arr[$row['rec_id']]['value_card_discount'] = $row['value_card_discount'];
                $arr[$row['rec_id']]['goods_value_card'] = $row['goods_value_card'];

                /* 退款单商品金额占比订单总额比例 */
                $goods_scale = $should_return / $goods_amount;
                $arr[$row['rec_id']]['should_integral_money'] = $goods_scale * $orderInfo['integral_money'];

                /* 在线支付原路退回专用 */
                $arr[$row['rec_id']]['pay_money_paid'] = $goods_scale * $orderInfo['money_paid'];

                $arr[$row['rec_id']]['pay_goods_amount'] = $goods_scale * $pay_order_amount;
                $arr[$row['rec_id']]['pay_goods_amount'] = $this->dscRepository->changeFloat($arr[$row['rec_id']]['pay_goods_amount']);

                $arr[$row['rec_id']]['pay_shipping_fee'] = $goods_scale * $pay_shipping_fee;
                $arr[$row['rec_id']]['pay_shipping_fee'] = $this->dscRepository->changeFloat($arr[$row['rec_id']]['pay_shipping_fee']);
            }

            $arr = BaseRepository::valueErrorArray($arr, 'pay_goods_amount', 'rec_id', $pay_order_amount);
            $arr = BaseRepository::valueErrorArray($arr, 'pay_shipping_fee', 'rec_id', $pay_shipping_fee);
            $arr = BaseRepository::valueErrorArray($arr, 'pay_money_paid', 'rec_id', $orderInfo['money_paid']);
        }

        if ($arr) {
            foreach ($arr as $key => $row) {
                $arr[$key]['pay_goods_amount'] = $this->dscRepository->changeFloat($row['pay_goods_amount']);
                $arr[$key]['pay_shipping_fee'] = $this->dscRepository->changeFloat($row['pay_shipping_fee']);
                $arr[$key]['pay_money_paid'] = $this->dscRepository->changeFloat($row['pay_money_paid']);
            }
        }

        return $arr;
    }

    /**
     * 单个退货单单储值卡退款金额
     *
     * @param int $ret_id
     * @return int
     */
    public function orderReturnValueCardRecord($ret_id = 0)
    {
        $add_val = ValueCardRecord::where('ret_id', $ret_id)->value('add_val');
        $add_val = $add_val ? $add_val : 0;

        return $add_val;
    }

    /**
     * 更新主订单状态
     *
     * @param array $order 子订单信息
     * @param int $type [1|订单基础状态，2|支付状态， 3|订单配送状态]
     * @param string $action_note
     * @param string $action_user
     * @throws \Exception
     */
    public function updateMainOrder($order = [], $type = 0, $action_note = '', $action_user = '')
    {
        $other = [];
        if ($order['main_order_id'] > 0 && $order['main_count'] == 0 && $type > 0) {
            $mainOrder = OrderInfo::select('order_sn', 'order_status', 'shipping_status', 'pay_status', 'main_pay')->where('order_id', $order['main_order_id']);
            $mainOrder = BaseRepository::getToArrayFirst($mainOrder);

            if (!empty($mainOrder)) {
                $order_status = $mainOrder['order_status'];
                $shipping_status = $mainOrder['shipping_status'];
                $pay_status = $mainOrder['pay_status'];
                $main_pay = $mainOrder['main_pay'];

                $childCount = OrderInfo::where('main_order_id', $order['main_order_id']);
                if ($type == 1) {
                    $childCount = $childCount->whereIn('order_status', [OS_UNCONFIRMED, OS_CONFIRMED]);
                } elseif ($type == 2) {
                    $childCount = $childCount->whereIn('pay_status', [PS_UNPAYED, PS_PAYING]);
                } elseif ($type == 3) {
                    $childCount = $childCount->whereIn('shipping_status', [SS_UNSHIPPED, SS_PREPARING]);
                } elseif ($type == 4) {
                    $childCount = $childCount->where('shipping_status', '<>', SS_RECEIVED);
                }

                $childCount = $childCount->where('order_id', '<>', $order['order_id'])->count();

                $time = TimeRepository::getGmTime();
                if ($type == 1) {
                    if ($childCount > 0) {
                        $order_status = OS_SPLITING_PART;
                    } else {
                        $order_status = OS_SPLITED;
                    }

                    $other = [
                        'order_status' => $order_status
                    ];
                } elseif ($type == 2) {
                    if ($childCount > 0) {
                        $pay_status = PS_MAIN_PAYED_PART;
                    } else {
                        $pay_status = PS_PAYED;
                        $main_pay = 2;
                    }

                    $other = [
                        'main_pay' => $main_pay,
                        'pay_status' => $pay_status,
                        'pay_time' => $time
                    ];
                } elseif ($type == 3) {
                    if ($childCount > 0) {
                        $shipping_status = SS_SHIPPED_PART;
                    } else {
                        $shipping_status = SS_SHIPPED;
                    }

                    $other = [
                        'shipping_status' => $shipping_status,
                        'shipping_time' => $time
                    ];
                } elseif ($type == 4) {
                    if ($childCount > 0) {
                        $shipping_status = SS_PART_RECEIVED;
                    } else {
                        $shipping_status = SS_RECEIVED;
                    }

                    $other = [
                        'shipping_status' => $shipping_status
                    ];
                }

                OrderInfo::where('order_id', $order['main_order_id'])->update($other);

                OrderInfo::where('main_order_id', $order['main_order_id'])
                    ->where('order_id', '<>', $order['order_id'])
                    ->update([
                        'child_show' => 1
                    ]);

                $action_note = "【" . lang('common.action_child_order') . "：" . $order['order_sn'] . "】" . $action_note;

                /* 更新记录 */
                $this->orderAction($mainOrder['order_sn'], $order_status, $shipping_status, $pay_status, $action_note, $action_user, 0, $time);
            }
        }
    }

    /**
     * 记录订单操作记录
     *
     * @param int $ret_id 退货单ID
     * @param int $return_status 退货单状态
     * @param int $refound_status 退货单退款状态
     * @param string $note
     * @param null $username
     * @param int $place
     *
     * @return bool
     */
    public function returnAction($ret_id = 0, $return_status = 0, $refound_status = 0, $note = '', $username = null, $place = 0)
    {
        if (is_null($username)) {
            $username = CommonRepository::getAdminName();
        }

        if ($ret_id) {
            $other = [
                'ret_id' => $ret_id,
                'action_user' => $username,
                'return_status' => $return_status,
                'refound_status' => $refound_status,
                'action_place' => $place,
                'action_note' => $note,
                'log_time' => TimeRepository::getGmTime()
            ];
            ReturnAction::insert($other);

            return true;
        }

        return false;
    }
}
