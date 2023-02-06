<?php

namespace App\Services\Order;

use App\Models\DeliveryGoods;
use App\Models\DeliveryOrder;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\Payment;
use App\Models\Region;
use App\Models\StoreOrder;
use App\Repositories\Activity\ActivityRepository;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Order\OrderRepository;
use App\Services\Comment\CommentService;

/**
 * 商城商品订单
 * Class CrowdFund
 * @package App\Services
 */
class OrderService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 获取订单详情
     *
     * @param array $where
     * @return array
     * @throws \Exception
     */
    public function getOrderInfo($where = [])
    {
        if (empty($where)) {
            return [];
        }

        if (isset($where['main_order_id'])) {
            $res = OrderInfo::selectRaw("GROUP_CONCAT(order_id) AS order_id, GROUP_CONCAT(order_sn) AS order_sn")
                ->where('main_order_id', $where['main_order_id']);
        } else {
            $res = OrderInfo::whereRaw(1);
        }

        if (isset($where['order_id'])) {
            $res = $res->where('order_id', $where['order_id']);
        }

        if (isset($where['order_sn'])) {
            $res = $res->where('order_sn', $where['order_sn']);
        }

        if (isset($where['user_id'])) {
            $res = $res->where('user_id', $where['user_id']);
        }

        $res = $res->with([
            'getPayment',
            'getSellerNegativeOrder'
        ]);

        $res = BaseRepository::getToArrayFirst($res);

        if ($res) {
            /* 活动语包 */
            $res['activity_lang'] = ActivityRepository::activityLang($res);
        }

        return $res;
    }

    /**
     * 获取未支付,有效订单信息
     *
     * @param int $order_id
     * @param int $main_order_id
     * @return  array
     */
    public function getUnPayedOrderInfo($order_id = 0, $main_order_id = 0)
    {
        return OrderRepository::getUnPayedOrderInfo($order_id, $main_order_id);
    }

    /**
     * 获取发货订单详情
     *
     * @access  public
     * @param array $where
     * @return  array
     */
    public function getDeliveryOrderInfo($where = [])
    {
        if (empty($where)) {
            return [];
        }

        $res = DeliveryOrder::whereRaw(1);

        if (isset($where['order_id'])) {
            $res = $res->where('order_id', $where['order_id']);
        }

        if (isset($where['user_id'])) {
            $res = $res->where('user_id', $where['user_id']);
        }

        $res = $res->first();

        $res = $res ? $res->toArray() : [];

        return $res;
    }

    /**
     * 获取订单数量
     *
     * @access  public
     * @param array $where
     * @return  array
     */
    public function getOrderCount($where = [])
    {
        if (empty($where)) {
            return 0;
        }

        $res = OrderInfo::whereRaw(1);

        if (isset($where['order_id'])) {
            $res = $res->where('order_id', $where['order_id']);
        }

        if (isset($where['order_sn'])) {
            $res = $res->where('order_sn', $where['order_sn']);
        }

        if (isset($where['user_id'])) {
            $res = $res->where('user_id', $where['user_id']);
        }

        if (isset($where['main_order_id'])) {
            $res = $res->where('main_order_id', $where['main_order_id']);
        }

        $count = $res->count();

        return $count;
    }

    /**
     * 获取订单商品信息
     *
     * @access  public
     * @param array $where
     * @return  array
     */
    public function getOrderGoodsInfo($where = [])
    {
        if (empty($where)) {
            return [];
        }

        $res = OrderGoods::whereRaw(1);

        if (isset($where['rec_id'])) {
            $res = $res->where('rec_id', $where['rec_id']);
            $res = BaseRepository::getToArrayFirst($res);
        }

        $tid = [];
        if (isset($where['order_id'])) {
            $res = $res->where('order_id', $where['order_id']);
            $res = BaseRepository::getToArrayGet($res);

            $tid = BaseRepository::getKeyPluck($res, 'tid');
        }

        $res = BaseRepository::getArraySqlFirst($res);

        if ($tid) {
            $res['tid'] = $tid;
            $res['tid'] = ArrRepository::getArrayUnset($res['tid']);
            $res['tid'] = $res['tid'] ? $res['tid'] : [];

            $res['freight'] = $res['tid'] ? 2 : 1;
        }

        if ($res) {
            $order = OrderInfo::where('order_id', $res['order_id']);
            $order = BaseRepository::getToArrayFirst($order);

            if ($order) {

                $res['order_sn'] = $order['order_sn'];
                $res['divide_channel'] = $order['divide_channel'];

                /* 取得区域名 */
                $province = Region::where('region_id', $order['province'])->value('region_name');
                $province = $province ?? '';

                $city = Region::where('region_id', $order['city'])->value('region_name');
                $city = $city ?? '';

                $district = Region::where('region_id', $order['district'])->value('region_name');
                $district = $district ?? '';

                $street = Region::where('region_id', $order['street'])->value('region_name');
                $street = $street ?? '';

                $res['region'] = $province . ' ' . $city . ' ' . $district . ' ' . $street;
            }
        }

        return $res;
    }

    /**
     * 获取订单商品数量
     *
     * @access  public
     * @param array $where
     * @return  array
     */
    public function getOrderGoodsCount($where = [])
    {
        if (empty($where)) {
            return 0;
        }

        $res = OrderGoods::whereRaw(1);

        if (isset($where['order_id'])) {
            $res = $res->where('order_id', $where['order_id']);
        }

        if (isset($where['is_real'])) {
            $res = $res->where('is_real', $where['is_real']);
        }

        $count = $res->count();

        return $count;
    }

    /**
     * 获取订单列表
     *
     * @access  public
     * @param  $where
     * @return  array
     */
    public function getOrderList($where = [])
    {
        $res = OrderInfo::whereRaw(1);

        if (isset($where['order_id']) && !empty($where['order_id'])) {
            $order_id = BaseRepository::getExplode($where['order_id']);
            $res = $res->whereIn('order_id', $order_id);
        }

        if (isset($where['main_order_id']) && !empty($where['main_order_id'])) {
            $main_order_id = BaseRepository::getExplode($where['main_order_id']);
            $res = $res->whereIn('main_order_id', $main_order_id);
        }

        if (isset($where['order_sn']) && !empty($where['order_sn'])) {
            $order_sn = BaseRepository::getExplode($where['order_sn']);
            $res = $res->whereIn('order_sn', $order_sn);
        }

        if (isset($where['sort']) && isset($where['order'])) {
            $res = $res->orderBy($where['sort'], $where['order']);
        }

        if (isset($where['size'])) {
            if (isset($where['page'])) {
                $start = ($where['page'] - 1) * $where['size'];

                if ($start > 0) {
                    $res = $res->skip($start);
                }
            }

            if ($where['size'] > 0) {
                $res = $res->take($where['size']);
            }
        }

        $res = BaseRepository::getToArrayGet($res);

        return $res;
    }

    /**
     * 获得订单地址信息
     *
     * @param int $order_id
     * @return string
     */
    public static function getOrderUserRegion($order_id = 0)
    {

        /* 取得区域名 */
        $res = OrderInfo::where('order_id', $order_id);

        $res = $res->with([
            'getRegionProvince' => function ($query) {
                $query->select('region_id', 'region_name as province_name');
            },
            'getRegionCity' => function ($query) {
                $query->select('region_id', 'region_name as city_name');
            },
            'getRegionDistrict' => function ($query) {
                $query->select('region_id', 'region_name as district_name');
            },
            'getRegionStreet' => function ($query) {
                $query->select('region_id', 'region_name as street_name');
            }
        ]);

        $res = BaseRepository::getToArrayFirst($res);

        $region = '';
        if ($res) {
            $res = $res['get_region_province'] ? array_merge($res, $res['get_region_province']) : $res;
            $res = $res['get_region_city'] ? array_merge($res, $res['get_region_city']) : $res;
            $res = $res['get_region_district'] ? array_merge($res, $res['get_region_district']) : $res;
            $res = $res['get_region_street'] ? array_merge($res, $res['get_region_street']) : $res;


            $province_name = isset($res['province_name']) && $res['province_name'] ? $res['province_name'] : '';
            $city_name = isset($res['city_name']) && $res['city_name'] ? $res['city_name'] : '';
            $district_name = isset($res['district_name']) && $res['district_name'] ? $res['district_name'] : '';
            $street_name = isset($res['street_name']) && $res['street_name'] ? $res['street_name'] : '';

            $region = $province_name . " " . $city_name . " " . $district_name . " " . $street_name;
            $region = trim($region);
        }

        return $region;
    }

    /**
     * 获得门店订单信息
     *
     * @param array $where
     * @return array
     */
    public function getStoreOrderInfo($where = [])
    {
        $res = StoreOrder::whereRaw(1);

        if (isset($where['order_id'])) {
            $res = $res->where('order_id', $where['order_id']);
        }

        $res = $res->first();

        $res = $res ? $res->toArray() : [];

        return $res;
    }

    /**
     * 生成查询订单总金额的字段
     * @param string $alias order表的别名（包括.例如 o.）
     * @return  string
     */
    public static function orderAmountField($alias = '')
    {
        return " " . $alias . "goods_amount + " . $alias . "tax + " . $alias . "shipping_fee" .
            " + " . $alias . "insure_fee + " . $alias . "pay_fee + " . $alias . "pack_fee" .
            " + " . $alias . "card_fee ";
    }

    /**
     * 生成查询订单的sql
     * @param string $type 类型
     * @param string $alias order表的别名（包括.例如 o.）
     * @return  string
     */
    public function orderQuerySql($type = 'finished', $alias = '')
    {
        $where = '';

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
            $order_status = BaseRepository::getImplode($order_status);

            $shipping_status = [
                defined(SS_RECEIVED) ? SS_RECEIVED : 2
            ];
            $shipping_status = BaseRepository::getImplode($shipping_status);

            $pay_status = [
                defined(PS_PAYED) ? PS_PAYED : 2,
                defined(PS_PAYING) ? PS_PAYING : 1
            ];
            $pay_status = BaseRepository::getImplode($pay_status);

            return " AND " . $alias . "order_status IN (" . $order_status . ") " .
                " AND " . $alias . "shipping_status IN (" . $shipping_status . ") " .
                " AND " . $alias . "pay_status  IN (" . $pay_status . ") ";
        } elseif ($type == 'queren') {
            $order_status = [
                defined(OS_CONFIRMED) ? OS_CONFIRMED : 1,
                defined(OS_SPLITED) ? OS_SPLITED : 5,
                defined(OS_SPLITING_PART) ? OS_SPLITING_PART : 6
            ];
            $order_status = BaseRepository::getImplode($order_status);

            return " AND " . $alias . "order_status IN (" . $order_status . ") " .
                " AND " . $alias . "pay_status = '" . PS_PAYED . "' ";
        }
        if ($type == 'confirm_take') {
            $order_status = [
                defined(OS_CONFIRMED) ? OS_CONFIRMED : 1,
                defined(OS_SPLITED) ? OS_SPLITED : 5,
                defined(OS_SPLITING_PART) ? OS_SPLITING_PART : 6,
                defined(OS_RETURNED_PART) ? OS_RETURNED_PART : 7,
                defined(OS_ONLY_REFOUND) ? OS_ONLY_REFOUND : 8
            ];
            $order_status = BaseRepository::getImplode($order_status);

            $shipping_status = [
                defined(SS_RECEIVED) ? SS_RECEIVED : 2,
                defined(SS_PART_RECEIVED) ? SS_PART_RECEIVED : 7,
            ];
            $shipping_status = BaseRepository::getImplode($shipping_status);

            $pay_status = [
                defined(PS_PAYED) ? PS_PAYED : 2,
                defined(PS_REFOUND_PART) ? PS_REFOUND_PART : 5
            ];
            $pay_status = BaseRepository::getImplode($pay_status);

            $return = " AND " . $alias . "order_status IN (" . $order_status . ") " .
                " AND " . $alias . "shipping_status IN (" . $shipping_status . ") " .
                " AND " . $alias . "pay_status IN (" . $pay_status . ") ";

            return $return;
        }
        if ($type == 'confirm_wait_goods') {
            $order_status = [
                defined(OS_CONFIRMED) ? OS_CONFIRMED : 1,
                defined(OS_SPLITED) ? OS_SPLITED : 5
            ];
            $order_status = BaseRepository::getImplode($order_status);

            $shipping_status = [
                defined(SS_SHIPPED) ? SS_SHIPPED : 1
            ];
            $shipping_status = BaseRepository::getImplode($shipping_status);

            $pay_status = [
                defined(PS_PAYED) ? PS_PAYED : 2
            ];
            $pay_status = BaseRepository::getImplode($pay_status);

            return " AND " . $alias . "order_status IN (" . $order_status . ")" .
                " AND " . $alias . "shipping_status IN (" . $shipping_status . ")" .
                " AND " . $alias . "pay_status IN (" . $pay_status . ") ";
        } elseif ($type == 'await_ship') {
            $order_status = [
                defined(OS_CONFIRMED) ? OS_CONFIRMED : 1,
                defined(OS_SPLITED) ? OS_SPLITED : 5,
                defined(OS_SPLITING_PART) ? OS_SPLITING_PART : 6,
                defined(OS_RETURNED_PART) ? OS_RETURNED_PART : 7
            ];
            $order_status = BaseRepository::getImplode($order_status);

            //待发货,--191012 update：后台部分发货加入待发货列表，前台部分发货加入待收货列表
            $shipping_status = [
                defined(SS_UNSHIPPED) ? SS_UNSHIPPED : 0,
                defined(SS_PREPARING) ? SS_PREPARING : 3,
                //defined(SS_SHIPPED_PART) ? SS_SHIPPED_PART : 4,
                defined(SS_SHIPPED_ING) ? SS_SHIPPED_ING : 5
            ];
            $shipping_status = BaseRepository::getImplode($shipping_status);

            $pay_status = [
                defined(PS_PAYED) ? PS_PAYED : 2,
                defined(PS_PAYING) ? PS_PAYING : 1,
                defined(PS_REFOUND_PART) ? PS_REFOUND_PART : 5,
            ];
            $pay_status = BaseRepository::getImplode($pay_status);

            $payList = $this->orderPaymentList(true);
            $payList = BaseRepository::getImplode($payList);

            if ($payList) {
                $where = " OR " . $alias . "pay_id IN (" . $payList . ")";
            }

            return " AND " . $alias . "order_status IN (" . $order_status . ") " .
                " AND " . $alias . "shipping_status IN (" . $shipping_status . ") " .
                " AND (" . $alias . "pay_status IN (" . $pay_status . ")" . $where . ") ";
        } elseif ($type == 'await_pay') {
            $order_status = [
                defined(OS_CONFIRMED) ? OS_CONFIRMED : 1,
                defined(OS_SPLITED) ? OS_SPLITED : 5
            ];
            $order_status = BaseRepository::getImplode($order_status);

            $shipping_status = [
                defined(SS_SHIPPED) ? SS_SHIPPED : 1,
                defined(SS_RECEIVED) ? SS_RECEIVED : 2
            ];
            $shipping_status = BaseRepository::getImplode($shipping_status);

            $pay_status = [
                defined(PS_UNPAYED) ? PS_UNPAYED : 0,
                defined(PS_PAYING) ? PS_PAYING : 1
            ];

            $payList = $this->orderPaymentList(false);
            $payList = BaseRepository::getImplode($payList);
            if ($payList) {
                $where = " OR " . $alias . "pay_id IN (" . $payList . ")";
            }

            return " AND " . $alias . "order_status IN (" . $order_status . ") " .
                " AND (" . $alias . "shipping_status IN (" . $shipping_status . ")" . $where . ") " .
                " AND " . $alias . "pay_status IN (" . $pay_status . ") ";
        } elseif ($type == 'unconfirmed') {
            $order_status = defined(OS_UNCONFIRMED) ? OS_UNCONFIRMED : 0;

            return " AND " . $alias . "order_status = '" . $order_status . "' ";
        } elseif ($type == 'unprocessed') {
            $order_status = [
                defined(OS_UNCONFIRMED) ? OS_UNCONFIRMED : 0,
                defined(OS_CONFIRMED) ? OS_CONFIRMED : 1
            ];
            $order_status = BaseRepository::getImplode($order_status);

            $shipping_status = defined(SS_UNSHIPPED) ? SS_UNSHIPPED : 0;

            $pay_status = defined(PS_UNPAYED) ? PS_UNPAYED : 0;

            return " AND " . $alias . "order_status IN (" . $order_status . ") " .
                " AND " . $alias . "shipping_status = '" . $shipping_status . "'" .
                " AND " . $alias . "pay_status = '" . $pay_status . "' ";
        } elseif ($type == 'unpay_unship') {
            $order_status = [
                defined(OS_UNCONFIRMED) ? OS_UNCONFIRMED : 0,
                defined(OS_CONFIRMED) ? OS_CONFIRMED : 1
            ];
            $order_status = BaseRepository::getImplode($order_status);

            $shipping_status = [
                defined(SS_UNSHIPPED) ? SS_UNSHIPPED : 0,
                defined(SS_PREPARING) ? SS_PREPARING : 3
            ];
            $shipping_status = BaseRepository::getImplode($shipping_status);

            $pay_status = defined(PS_UNPAYED) ? PS_UNPAYED : 0;

            return " AND " . $alias . "order_status IN (" . $order_status . ") " .
                " AND " . $alias . "shipping_status IN (" . $shipping_status . ") " .
                " AND " . $alias . "pay_status = '" . $pay_status . "' ";
        } elseif ($type == 'shipped') {
            $order_status = defined(OS_CONFIRMED) ? OS_CONFIRMED : 1;

            $shipping_status = [
                defined(SS_SHIPPED) ? SS_SHIPPED : 1,
                defined(SS_RECEIVED) ? SS_RECEIVED : 2
            ];
            $shipping_status = BaseRepository::getImplode($shipping_status);

            return " AND " . $alias . "order_status = '" . $order_status . "'" .
                " AND {$alias}shipping_status IN (" . $shipping_status . ") ";
        } elseif ($type == 'real_pay') {
            $order_status = [
                defined(OS_CONFIRMED) ? OS_CONFIRMED : 1,
                defined(OS_SPLITED) ? OS_SPLITED : 5,
                defined(OS_SPLITING_PART) ? OS_SPLITING_PART : 6,
                defined(OS_RETURNED_PART) ? OS_RETURNED_PART : 7
            ];
            $order_status = BaseRepository::getImplode($order_status);

            $shipping_status = defined(SS_UNSHIPPED) ? SS_UNSHIPPED : 0;

            $pay_status = [
                defined(PS_PAYED) ? PS_PAYED : 2,
                defined(PS_PAYING) ? PS_PAYING : 1
            ];
            $pay_status = BaseRepository::getImplode($pay_status);

            return " AND " . $alias . "order_status IN (" . $order_status . ") " .
                " AND " . $alias . "shipping_status <> " . $shipping_status .
                " AND " . $alias . "pay_status IN (" . $pay_status . ") ";
        }
    }

    /**
     * 取得支付方式id列表
     * @param bool $is_cod 是否货到付款
     * @return  array
     */
    public function orderPaymentList($is_cod)
    {
        $res = Payment::select('pay_id')->whereRaw(1);

        if ($is_cod) {
            $res = $res->where('is_cod', 1);
        } else {
            $res = $res->where('is_cod', 0);
        }

        $res = BaseRepository::getToArrayGet($res);
        $res = BaseRepository::getKeyPluck($res, 'pay_id');

        return $res;
    }

    /**
     * 订单数量
     * @param $uid
     * @param int $status
     * @return mixed
     */
    public function getUserOrderCount($uid, $status = 0)
    {
        $model = OrderInfo::where('main_count', 0)
            ->where('user_id', $uid)
            ->where('is_delete', 0)
            ->where('is_zc_order', 0); //排除众筹订单

        if ($status == 1) {
            // 待付款
            $model = $model->where('pay_status', PS_UNPAYED)
                ->whereNotIn('order_status', [OS_CANCELED, OS_INVALID, OS_RETURNED]);
        } elseif ($status == 2) {
            // 待收货
            $model = $model->where('pay_status', PS_PAYED)
                ->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART])
                ->where('shipping_status', '>=', SS_UNSHIPPED)
                ->where('shipping_status', '!=', SS_RECEIVED);
        }

        $order_count = $model->count();

        return $order_count;
    }

    /**
     * 统计拼团中数量
     *
     * @param int $user_id
     * @return mixed
     */
    public function teamUserOrderNum($user_id = 0)
    {
        $time = TimeRepository::getGmTime();

        $num = OrderInfo::where('user_id', $user_id)
            ->where('extension_code', 'team_buy')
            ->where('order_status', '<>', OS_CANCELED);

        $prefix = config('database.connections.mysql.prefix');

        $where = [
            'time' => $time,
            'prefix' => $prefix
        ];

        $num = $num->whereHasIn('getTeamLog', function ($query) use ($where) {
            $query->where('status', '<', 1)
                ->whereRaw("`" . $where['prefix'] . "team_log`.start_time + (SELECT `" . $where['prefix'] . "team_goods`.validity_time * 3600 FROM `" . $where['prefix'] . "team_goods` WHERE `" . $where['prefix'] . "team_goods`.id = `" . $where['prefix'] . "team_log`.t_id LIMIT 1) > " . $where['time'] .
                    " AND (SELECT COUNT(*) FROM `" . $where['prefix'] . "team_goods` WHERE `" . $where['prefix'] . "team_goods`.is_team = 1) > 0");
        });

        $num = $num->count();

        return $num;
    }

    /**
     * 发货单商品图片列表
     * @param string $invoice_no
     * @param int $order_id
     * @return array
     */
    public function getDeliveryGoods($invoice_no = '', $order_id = 0)
    {
        if (empty($invoice_no)) {
            return [];
        }

        $delivery = DeliveryOrder::query();

        if ($order_id > 0) {
            $delivery = $delivery->where('order_id', $order_id);
        } else {
            $delivery = $delivery->where('invoice_no', $invoice_no);
        }
        $delivery_id = $delivery->value('delivery_id');

        $res = DeliveryGoods::where('delivery_id', $delivery_id)->with(['getGoods' => function ($query) {
            $query->select('goods_id', 'goods_thumb');
        }])->orderByDesc('delivery_id');

        $res = BaseRepository::getToArrayGet($res);
        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $row = BaseRepository::getArrayMerge($row, $row['get_goods']);
                $arr[$key]['goods_thumb'] = empty($row['goods_thumb']) ? '' : $this->dscRepository->getImagePath($row['goods_thumb']);
                $arr[$key]['goods_id'] = $row['goods_id'];
            }
        }

        return $arr;
    }
}
