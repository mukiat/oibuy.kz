<?php

namespace App\Services\Order;

use App\Models\AdminUser;
use App\Models\Agency;
use App\Models\BackGoods;
use App\Models\BackOrder;
use App\Models\DeliveryGoods;
use App\Models\DeliveryOrder;
use App\Models\Goods;
use App\Models\GoodsActivity;
use App\Models\GoodsAttr;
use App\Models\GoodsExtend;
use App\Models\GoodsInventoryLogs;
use App\Models\MerchantsShopInformation;
use App\Models\OrderAction;
use App\Models\OrderCloud;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\OrderReturn;
use App\Models\PackageGoods;
use App\Models\Products;
use App\Models\ProductsArea;
use App\Models\ProductsWarehouse;
use App\Models\Region;
use App\Models\ReturnCause;
use App\Models\StoreOrder;
use App\Models\TeamLog;
use App\Models\UserAddress;
use App\Models\Users;
use App\Models\ValueCard;
use App\Models\ValueCardRecord;
use App\Models\WarehouseAreaGoods;
use App\Models\WarehouseGoods;
use App\Models\ZcGoods;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Order\OrderRepository;
use App\Services\Activity\GroupBuyService;
use App\Services\Commission\CommissionDataHandleService;
use App\Services\Common\CommonManageService;
use App\Services\CrowdFund\CrowdRefoundService;
use App\Services\ExpressManage\ExpressManageService;
use App\Services\Goods\GoodsAttrService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Goods\GoodsWarehouseService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Team\TeamService;
use App\Services\User\UserAddressService;
use App\Services\User\UserDataHandleService;
use Illuminate\Support\Facades\DB;

class OrderManageService
{
    protected $goodsWarehouseService;
    protected $commonRepository;
    protected $merchantCommonService;
    protected $dscRepository;
    protected $commonManageService;
    protected $orderRefoundService;
    protected $orderCommonService;
    protected $userAddressService;
    protected $commissionDataHandleService;
    protected $orderGoodsDataHandleService;
    protected $userDataHandleService;
    protected $expressManageService;
    protected $goodsAttrService;

    public function __construct(
        GoodsWarehouseService $goodsWarehouseService,
        CommonRepository $commonRepository,
        MerchantCommonService $merchantCommonService,
        DscRepository $dscRepository,
        CommonManageService $commonManageService,
        OrderRefoundService $orderRefoundService,
        OrderCommonService $orderCommonService,
        UserAddressService $userAddressService,
        CommissionDataHandleService $commissionDataHandleService,
        OrderGoodsDataHandleService $orderGoodsDataHandleService,
        UserDataHandleService $userDataHandleService,
        ExpressManageService $expressManageService,
        GoodsAttrService $goodsAttrService
    )
    {
        $this->goodsWarehouseService = $goodsWarehouseService;
        $this->commonRepository = $commonRepository;
        $this->merchantCommonService = $merchantCommonService;
        $this->dscRepository = $dscRepository;
        $this->commonManageService = $commonManageService;
        $this->orderRefoundService = $orderRefoundService;
        $this->orderCommonService = $orderCommonService;
        $this->userAddressService = $userAddressService;
        $this->commissionDataHandleService = $commissionDataHandleService;
        $this->orderGoodsDataHandleService = $orderGoodsDataHandleService;
        $this->userDataHandleService = $userDataHandleService;
        $this->expressManageService = $expressManageService;
        $this->goodsAttrService = $goodsAttrService;
    }

    /**
     * 退换货描述类型
     *
     * @param $c_id
     * @return array
     */
    public function causeInfo($c_id)
    {
        $res = ReturnCause::where('cause_id', $c_id);
        $res = BaseRepository::getToArrayFirst($res);

        if ($res) {
            return $res;
        } else {
            return [];
        }
    }

    /**
     * 取得状态列表
     *
     * @param string $type
     * @return array
     */
    public function getStatusList($type = 'all')
    {
        $list = [];

        if ($type == 'all' || $type == 'order') {
            $pre = $type == 'all' ? 'os_' : '';
            foreach ($GLOBALS['_LANG']['os'] as $key => $value) {
                $list[$pre . $key] = $value;
            }
        }

        if ($type == 'all' || $type == 'shipping') {
            $pre = $type == 'all' ? 'ss_' : '';
            foreach ($GLOBALS['_LANG']['ss'] as $key => $value) {
                $list[$pre . $key] = $value;
            }
        }

        if ($type == 'all' || $type == 'payment') {
            $pre = $type == 'all' ? 'ps_' : '';
            foreach ($GLOBALS['_LANG']['ps'] as $key => $value) {
                $list[$pre . $key] = $value;
            }
        }
        return $list;
    }

    /**
     * 更新订单总金额
     * @param int $order_id 订单id
     * @return  bool
     */
    public function updateOrderAmount($order_id)
    {
        load_helper('order');
        //更新订单总金额

        $res = OrderInfo::selectRaw(order_due_field() . ' as order_amount')->where('order_id', $order_id)->value('order_amount');
        $res = $res ? $res : 0;
        $data = ['order_amount' => $res];
        $res = OrderInfo::where('order_id', $order_id)->update($data);

        return $res;
    }

    /**
     * 返回某个订单可执行的操作列表，包括权限判断
     * @param array $order 订单信息 order_status, shipping_status, pay_status
     * @param string $actions 取得订单操作权限
     * @param array $payment 用于判断 $is_cod 支付方式是否货到付款
     * @return  array   可执行的操作  confirm, pay, unpay, prepare, ship, unship, receive, cancel, invalid, return, drop
     * 格式 array('confirm' => true, 'pay' => true)
     */
    public function operableList($order = [], $actions = '', $payment = [])
    {
        /* 取得订单状态、发货状态、付款状态 */
        $os = $order['order_status'];
        $ss = $order['shipping_status'];
        $ps = $order['pay_status'];

        /* 佣金账单状态 0 未出账 1 出账 2 结账 */
        $chargeoff_status = $order['chargeoff_status'];
        $is_delete = $order['is_delete']; // 订单是否删除 0 正常 1 回收站 2 用户中心删除

        /* 取得订单操作权限 */
        $actions = $actions ?: 'all';
        if ($actions == 'all') {
            $priv_list = ['os' => true, 'ss' => true, 'ps' => true, 'edit' => true];
        } else {
            $actions = ',' . $actions . ',';
            $priv_list = [
                'os' => strpos($actions, ',order_os_edit,') !== false,
                'ss' => strpos($actions, ',order_ss_edit,') !== false,
                'ps' => strpos($actions, ',order_ps_edit,') !== false,
                'edit' => strpos($actions, ',order_edit,') !== false
            ];
        }

        /* 取得订单支付方式是否货到付款 */
        if (empty($payment)) {
            $payment = payment_info($order['pay_id']);
        }

        if (isset($payment['is_cod']) && $payment['is_cod'] == 1) {
            $is_cod = true;
        } else {
            $is_cod = false;
        }

        /* 根据状态返回可执行操作 */
        $list = [];

        /*------------------------------------------------------ */
        //-- 0 订单状态：未确认
        /*------------------------------------------------------ */
        if (OS_UNCONFIRMED == $os && $is_delete == 0) {
            /* 状态：未确认 => 未付款、未发货 */
            if ($priv_list['os']) {
                $list['confirm'] = true; // 确认
                $list['invalid'] = true; // 无效
                $list['cancel'] = true; // 取消

                if ($is_cod) {
                    /* 货到付款 */
                    if ($priv_list['ss']) {
                        $list['prepare'] = true; // 可配货
                        $list['split'] = true; // 分单
                    }
                } else {
                    /* 不是货到付款 */
                    if ($priv_list['ps']) {
                        $list['pay'] = true;  // 可付款
                    }
                }
            }
        }
        /*------------------------------------------------------ */
        //-- 1 订单状态：已确认、已分单、部分分单
        /*------------------------------------------------------ */
        elseif ((OS_CONFIRMED == $os || OS_SPLITED == $os || OS_SPLITING_PART == $os) && $is_delete == 0) {

            /* 总状态：已确认、未付款 、部分付款 */
            if (PS_UNPAYED == $ps || PS_PAYED_PART == $ps) {
                /* 状态：已确认、未付款、未发货（或配货中） */
                if (SS_UNSHIPPED == $ss || SS_PREPARING == $ss || OS_SHIPPED_PART == $ss || SS_SHIPPED_PART == $ss) {
                    if ($priv_list['os']) {
                        $list['cancel'] = true; // 取消
                        $list['invalid'] = true; // 无效
                    }

                    if ($is_cod) {
                        /* 货到付款 */
                        if ($priv_list['ss']) {
                            if (SS_UNSHIPPED == $ss) {
                                $list['prepare'] = true; // 配货
                            }
                            $list['split'] = true; // 分单
                            $list['split_goods'] = true; // 订单商品可单独发货
                        }
                    } elseif ($payment['pay_code'] == 'bank') {
                        /* 银行转账 */
                        if ($priv_list['ps']) {
                            $list['pay'] = true; // 可付款
                        }

                        if ($priv_list['ss']) {
                            if (SS_UNSHIPPED == $ss) {
                                $list['prepare'] = true; // 配货
                            }
                            $list['split'] = true; // 分单
                            $list['split_goods'] = true; // 订单商品可单独发货
                        }
                    } else {
                        /* 不是货到付款 */
                        if ($priv_list['ps']) {
                            $list['pay'] = true; // 可付款
                        }
                    }

                } elseif (SS_SHIPPED_ING == $ss || SS_SHIPPED_PART == $ss) {
                    /* 状态：已确认、未付款、发货中、部分发货 */

                    // 部分分单
                    if (OS_SPLITING_PART == $os) {
                        $list['split'] = true; // 分单
                    }

                    $list['to_delivery'] = true; // 去发货
                } else {
                    /* 状态：已确认、未付款、已发货或已收货 => 货到付款 */

                    if ($priv_list['ps']) {
                        $list['pay'] = true; // 付款
                    }
                    if ($priv_list['ss']) {
                        if (SS_SHIPPED == $ss) {
                            $list['receive'] = true; // 收货确认
                        }
                        $list['unship'] = true; // 设为未发货

                        if ($priv_list['os'] && $is_cod) {
                            $list['return'] = true; // 退货
                        }
                    }
                }
            } else {

                /* 总状态：已确认、已付款和付款中 */

                /* 状态：已确认、已付款和付款中、未发货（配货中） => 不是货到付款 */
                if (SS_UNSHIPPED == $ss || SS_PREPARING == $ss) {

                    if ($priv_list['ss'] && !$is_cod) {
                        if (SS_UNSHIPPED == $ss) {
                            $list['prepare'] = true; // 配货
                        }

                        $list['split'] = true; // 分单
                        $list['split_goods'] = true; // 订单商品可单独发货
                    }

                    if ($priv_list['ps']) {
                        $list['unpay'] = true; // 设为未付款
                    }

                } /* 状态：已确认、发货中、部分发货 */
                elseif (SS_SHIPPED_ING == $ss || SS_SHIPPED_PART == $ss) {
                    // 部分分单
                    if (OS_SPLITING_PART == $os) {
                        $list['split'] = true; // 分单

                        if ($priv_list['ss'] && !$is_cod) {
                            $list['split_goods'] = true; // 订单商品可单独发货
                        }
                    }
                    $list['to_delivery'] = true; // 去发货
                } else {
                    /* 状态：已确认、已付款和付款中、已发货或已收货 */
                    if ($priv_list['ss']) {
                        if (SS_SHIPPED == $ss) {
                            $list['receive'] = true; // 已收货  => 可收货确认
                        } elseif (SS_PART_RECEIVED == $ss) {
                            $list['split_goods'] = true; // 部分已收货 => 订单商品可单独发货
                        }

                        /* 不是货到付款 */
                        if (!$is_cod && $ss != SS_RECEIVED) {
                            $list['unship'] = true; // 可设为未发货
                        }
                    }

                    /* 货到付款 */
                    if ($priv_list['ps'] && $is_cod) {
                        $list['unpay'] = true; // 可设为未付款
                    }
                }
            }
        }
        /*------------------------------------------------------ */
        //-- 2 订单状态：已取消
        /*------------------------------------------------------ */
        elseif (OS_CANCELED == $os) {
            if ($priv_list['os']) {
                // $list['confirm'] = true; 暂时注释 liu
            }

            if ($priv_list['edit']) {
                $list['remove'] = true; // 可删除
            }
        }
        /*------------------------------------------------------ */
        //-- 3 订单状态：无效
        /*------------------------------------------------------ */
        elseif (OS_INVALID == $os) {
            if ($priv_list['os']) {
                //$list['confirm'] = true; 暂时注释 liu
            }

            if ($priv_list['edit']) {
                $list['remove'] = true; // 可删除
            }
        }
        /*------------------------------------------------------ */
        //-- 4 订单状态：退货
        /*------------------------------------------------------ */
        elseif (OS_RETURNED == $os && $is_delete == 0) {
            if ($priv_list['os']) {
                //$list['confirm'] = true;
            }
        }
        /*------------------------------------------------------ */
        //-- 7 订单状态：部分已退货
        /*------------------------------------------------------ */
        elseif (OS_RETURNED_PART == $os && $is_delete == 0) {
            /* 状态：未付款 */
            if ($priv_list['ps'] && $chargeoff_status > 0 && $ps < PS_PAYED && $ss != SS_RECEIVED) {
                $list['pay'] = true; // 可付款
            }

            if ($priv_list['ss'] && $chargeoff_status > 0 && $ss != SS_RECEIVED) {
                /* 状态：部分退货 */
                $list['receive'] = true; // 可收货确认
            }

            // 部分已退货 部分退款 未发货(含部分发货)
            if ($priv_list['ss'] && $ps == PS_REFOUND_PART && !in_array($ss, [SS_SHIPPED, SS_RECEIVED])) {
                $list['split_goods'] = true; // 订单商品可单独发货
            }
        }
        /*------------------------------------------------------ */
        //-- 8 订单状态：仅退款
        /*------------------------------------------------------ */
        elseif (OS_ONLY_REFOUND == $os && $is_delete == 0) {
            // 部分退款, 未 已发货、已收货(含部分发货)
            if ($priv_list['ss'] && $ps == PS_REFOUND_PART && !in_array($ss, [SS_SHIPPED, SS_RECEIVED])) {
                $list['split_goods'] = true; // 订单商品可单独发货
            }
        }

        /* 不是货到付款 */
        if ($is_cod == false) {
            // 显示 订单退款、退货
            if ($priv_list['os'] && $priv_list['ss'] && $priv_list['ps'] && $is_delete == 0) {
                // 订单状态 （已确认、已分单）、已付款  配送状态 != 3、5
                if (in_array($os, [OS_CONFIRMED, OS_SPLITED]) && PS_PAYED == $ps && PS_REFOUND != $ps && !in_array($ss, [SS_PREPARING, SS_SHIPPED_ING])) {
                    $list['return'] = true;
                }
            } else {
                $list['return'] = false;
            }
        }

        /* 修正发货操作 */
        if (!empty($list['split'])) {
            /* 如果是团购活动且未处理成功，不能发货 */
            if ($order['extension_code'] == 'group_buy') {
                /* 仅获取团购活动状态 无需查询其他 */
                $group_buy_id = intval($order['extension_id']);
                $group_buy_status = GroupBuyService::groupBuyStatusByActId($group_buy_id);

                if (isset($group_buy_status) && $group_buy_status != GBS_SUCCEED) {
                    unset($list['split']);
                    unset($list['to_delivery']);
                    $list['split_goods'] = false;
                }
            }

            /* 如果部分发货 不允许 取消 订单 */
            if ($this->orderDeliveryed($order['order_id']) && $is_delete == 0) {
                $list['return'] = true; // 退货（包括退款）
                unset($list['cancel']); // 取消
            }
        }

        /**
         * 同意申请
         */
        $list['after_service'] = true;
        $list['receive_goods'] = true;
        $list['agree_apply'] = true;
        $list['refound'] = true;
        $list['swapped_out_single'] = true;
        $list['swapped_out'] = true;
        $list['complete'] = true;
        $list['refuse_apply'] = true;

        return $list;
    }

    /**
     * 处理编辑订单时订单金额变动
     * @param array $order 订单信息
     * @param array $msgs 提示信息
     * @param array $links 链接信息
     */
    public function handleOrderMoneyChange($order, &$msgs, &$links)
    {
        $order_id = $order['order_id'];
        if ($order['pay_status'] == PS_PAYED || $order['pay_status'] == PS_PAYING) {
            /* 应付款金额 */
            $money_dues = $order['order_amount'];
            if ($money_dues > 0) {
                /* 修改订单为未付款 */
                update_order($order_id, ['pay_status' => PS_UNPAYED, 'pay_time' => 0]);
                $msgs[] = $GLOBALS['_LANG']['amount_increase'];
                $links[] = ['text' => lang('admin/order.order_info'), 'href' => 'order.php?act=info&order_id=' . $order_id];
            } elseif ($money_dues < 0) {
                $anonymous = $order['user_id'] > 0 ? 0 : 1;
                $msgs[] = $GLOBALS['_LANG']['amount_decrease'];
                $links[] = ['text' => lang('admin/order.refund'), 'href' => 'order.php?act=process&func=load_refund&anonymous=' .
                    $anonymous . '&order_id=' . $order_id . '&refund_amount=' . abs($money_dues)];
            }
        }
    }

    /**
     * 获取订单列表信息
     *
     * @param int $page
     * @param array $adminru
     * @return array
     * @throws \Exception
     */
    public function orderList($page = 0, $adminru = [])
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'orderList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 过滤信息 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        $filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
        $filter['consignee'] = empty($_REQUEST['consignee']) ? '' : trim($_REQUEST['consignee']);
        $filter['address'] = empty($_REQUEST['address']) ? '' : trim($_REQUEST['address']);

        if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
            $filter['order_sn'] = json_str_iconv($filter['order_sn']);
            $filter['consignee'] = json_str_iconv($filter['consignee']);
            $filter['address'] = json_str_iconv($filter['address']);
        }

        $filter['email'] = empty($_REQUEST['email']) ? '' : trim($_REQUEST['email']);
        $filter['zipcode'] = empty($_REQUEST['zipcode']) ? '' : trim($_REQUEST['zipcode']);
        $filter['tel'] = empty($_REQUEST['tel']) ? '' : trim($_REQUEST['tel']);
        $filter['mobile'] = empty($_REQUEST['mobile']) ? 0 : trim($_REQUEST['mobile']);
        $filter['country'] = empty($_REQUEST['order_country']) ? 0 : intval($_REQUEST['order_country']);
        $filter['province'] = empty($_REQUEST['order_province']) ? 0 : intval($_REQUEST['order_province']);
        $filter['city'] = empty($_REQUEST['order_city']) ? 0 : intval($_REQUEST['order_city']);
        $filter['district'] = empty($_REQUEST['order_district']) ? 0 : intval($_REQUEST['order_district']);
        $filter['street'] = empty($_REQUEST['order_street']) ? 0 : intval($_REQUEST['order_street']);
        $filter['shipping_id'] = empty($_REQUEST['shipping_id']) ? 0 : intval($_REQUEST['shipping_id']);
        $filter['pay_id'] = empty($_REQUEST['pay_id']) ? 0 : intval($_REQUEST['pay_id']);
        $filter['order_status'] = isset($_REQUEST['order_status']) ? intval($_REQUEST['order_status']) : -1;
        $filter['shipping_status'] = isset($_REQUEST['shipping_status']) ? intval($_REQUEST['shipping_status']) : -1;
        $filter['pay_status'] = isset($_REQUEST['pay_status']) ? intval($_REQUEST['pay_status']) : -1;
        $filter['order_type'] = isset($_REQUEST['order_type']) ? intval($_REQUEST['order_type']) : 0;
        $filter['user_id'] = empty($_REQUEST['user_id']) ? 0 : intval($_REQUEST['user_id']);
        $filter['user_name'] = empty($_REQUEST['user_name']) ? '' : trim($_REQUEST['user_name']);
        $filter['composite_status'] = isset($_REQUEST['composite_status']) ? intval($_REQUEST['composite_status']) : -1;
        $filter['group_buy_id'] = isset($_REQUEST['group_buy_id']) ? intval($_REQUEST['group_buy_id']) : 0;
        $filter['presale_id'] = isset($_REQUEST['presale_id']) ? intval($_REQUEST['presale_id']) : 0; // 预售id
        $filter['store_id'] = isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0; // 门店id
        $filter['order_cat'] = isset($_REQUEST['order_cat']) ? trim($_REQUEST['order_cat']) : '';

        /**
         * 0 : 自营
         * 1 : 店铺
         * 3 : 主订单
         */
        $filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? intval($_REQUEST['seller_list']) : 0;

        $filter['order_referer'] = isset($_REQUEST['order_referer']) ? trim($_REQUEST['order_referer']) : '';

        $filter['source'] = empty($_REQUEST['source']) ? '' : trim($_REQUEST['source']); //来源起始页

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter['start_time'] = empty($_REQUEST['start_time']) ? '' : (strpos($_REQUEST['start_time'], '-') > 0 ? TimeRepository::getLocalStrtoTime($_REQUEST['start_time']) : $_REQUEST['start_time']);
        $filter['end_time'] = empty($_REQUEST['end_time']) ? '' : (strpos($_REQUEST['end_time'], '-') > 0 ? TimeRepository::getLocalStrtoTime($_REQUEST['end_time']) : $_REQUEST['end_time']);

        //确认收货时间 bylu:
        $filter['start_take_time'] = empty($_REQUEST['start_take_time']) ? '' : (strpos($_REQUEST['start_take_time'], '-') > 0 ? TimeRepository::getLocalStrtoTime($_REQUEST['start_take_time']) : $_REQUEST['start_take_time']);
        $filter['end_take_time'] = empty($_REQUEST['end_take_time']) ? '' : (strpos($_REQUEST['end_take_time'], '-') > 0 ? TimeRepository::getLocalStrtoTime($_REQUEST['end_take_time']) : $_REQUEST['end_take_time']);

        //管理员查询的权限 -- 店铺查询 start
        $filter['store_search'] = !isset($_REQUEST['store_search']) ? -1 : intval($_REQUEST['store_search']);
        $filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
        $filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
        $filter['store_type'] = isset($_REQUEST['store_type']) ? trim($_REQUEST['store_type']) : '';

        //众筹订单 by wu
        $filter['pid'] = !isset($_REQUEST['pid']) ? 0 : intval($_REQUEST['pid']);
        $filter['gid'] = !isset($_REQUEST['gid']) ? 0 : intval($_REQUEST['gid']);

        $filter['serch_type'] = !isset($_REQUEST['serch_type']) ? -1 : intval($_REQUEST['serch_type']);

        //卖场 start
        $filter['rs_id'] = empty($_REQUEST['rs_id']) ? 0 : intval($_REQUEST['rs_id']);
        if (isset($adminru['rs_id']) && $adminru['rs_id'] > 0) {
            $filter['rs_id'] = $adminru['rs_id'];
        }
        //卖场 end

        /* 分页大小 */
        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

        $res = OrderInfo::whereRaw(1);

        if ($filter['keywords']) {
            $orderGoods = OrderGoods::distinct()->select('order_id')->where(function ($query) use ($filter) {
                $query->where('goods_name', 'LIKE', '%' . $filter['keywords'] . '%')
                    ->orWhere('goods_sn', $filter['keywords']);
            });

            $orderGoods = $orderGoods->pluck('order_id');
            $order_id = BaseRepository::getToArray($orderGoods);

            $main_order_id = [];
            if ($filter['seller_list'] == 3) {
                $main_order_id = OrderInfo::distinct()->select('main_order_id')
                    ->where('order_sn', 'like', '%' . $filter['keywords'] . '%')
                    ->where('main_count', 0);

                $main_order_id = $main_order_id->pluck('main_order_id');
                $main_order_id = BaseRepository::getToArray($main_order_id);
            }

            $res = $res->where(function ($query) use ($filter, $order_id, $main_order_id) {
                $query = $query->where('order_sn', 'like', '%' . $filter['keywords'] . '%');

                if ($main_order_id) {
                    $query->orWhere(function ($query) use ($main_order_id) {
                        $query->whereIn('order_id', $main_order_id);
                    });
                }
                if ($order_id) {
                    $query->orWhere(function ($query) use ($order_id) {
                        $query->whereIn('order_id', $order_id);
                    });
                }
            });
        }

        $store_search = -1;

        if (isset($adminru['ru_id']) && $adminru['ru_id'] > 0) {
            $res = $res->where('ru_id', $adminru['ru_id']);
        } else {
            if ($filter['seller_list'] == 3) {
                //显示主订单
                $res = $res->where('main_count', '>', 0);
            } else {
                if ($filter['seller_list']) {
                    //优化提高查询性能，原始条件是 where('ru_id', '>', 0);
                    $res = CommonRepository::constantMaxId($res, 'ru_id');
                } else {
                    $res = $res->where('ru_id', 0);
                }

                $res = $res->where('main_count', 0);
            }

            if ($filter['store_search'] > -1) {
                if (isset($adminru['ru_id']) && $adminru['ru_id'] == 0) {
                    if ($filter['store_search'] > 0) {
                        if ($filter['store_search'] == 1) {
                            $res = $res->where('ru_id', $filter['merchant_id']);
                        }

                        if ($filter['store_search'] > 1) {
                            $seller_id = MerchantsShopInformation::distinct()->select('user_id')->where(function ($query) use ($filter) {
                                if ($filter['store_search'] == 2) {
                                    $query->where('rz_shop_name', 'LIKE', '%' . $this->dscRepository->mysqlLikeQuote($filter['store_keyword']) . '%');
                                } elseif ($filter['store_search'] == 3) {
                                    $query = $query->where('shoprz_brand_name', 'LIKE', '%' . $this->dscRepository->mysqlLikeQuote($filter['store_keyword']) . '%');
                                    if ($filter['store_type']) {
                                        $query->where('shop_name_suffix', $filter['store_type']);
                                    }
                                }
                            });

                            $seller_id = $seller_id->pluck('user_id');
                            $seller_id = BaseRepository::getToArray($seller_id);

                            $res = $res->where(function ($query) use ($seller_id) {
                                $query->whereIn('ru_id', $seller_id);
                            });
                        }
                    } else {
                        $store_search = 0;
                    }
                }
            }
            //管理员查询的权限 -- 店铺查询 end
        }

        if ($filter['store_id'] > 0) {
            $res = $res->where(function ($query) use ($filter) {
                $query->whereHasIn('getStoreOrder', function ($query) use ($filter) {
                    $query->where('store_id', $filter['store_id']);
                });
            });
        }

        if ($filter['order_sn']) {
            $res = $res->where('order_sn', 'LIKE', '%' . mysql_like_quote($filter['order_sn']) . '%');
        }
        if ($filter['consignee']) {
            $res = $res->where('consignee', 'LIKE', '%' . mysql_like_quote($filter['consignee']) . '%');
        }
        if ($filter['email']) {
            $res = $res->where('email', 'LIKE', '%' . mysql_like_quote($filter['email']) . '%');
        }
        if ($filter['address']) {
            $res = $res->where('address', 'LIKE', '%' . mysql_like_quote($filter['address']) . '%');
        }
        if ($filter['zipcode']) {
            $res = $res->where('zipcode', 'LIKE', '%' . mysql_like_quote($filter['zipcode']) . '%');
        }
        if ($filter['tel']) {
            $res = $res->where('tel', 'LIKE', '%' . mysql_like_quote($filter['tel']) . '%');
        }
        if ($filter['mobile']) {
            $res = $res->where('mobile', 'LIKE', '%' . mysql_like_quote($filter['mobile']) . '%');
        }
        if ($filter['country']) {
            $res = $res->where('country', $filter['country']);
        }
        if ($filter['province']) {
            $res = $res->where('province', $filter['province']);
        }
        if ($filter['city']) {
            $res = $res->where('city', $filter['city']);
        }
        if ($filter['district']) {
            $res = $res->where('district', $filter['district']);
        }
        if ($filter['street']) {
            $res = $res->where('street', $filter['street']);
        }
        if ($filter['shipping_id']) {
            $res = $res->where('shipping_id', $filter['shipping_id']);
        }
        if ($filter['pay_id']) {
            $res = $res->where('pay_id', $filter['pay_id']);
        }
        if ($filter['order_status'] != -1) {
            $res = $res->where('order_status', $filter['order_status']);
        }
        if ($filter['shipping_status'] != -1) {
            $res = $res->where('shipping_status', $filter['shipping_status']);
        }
        if ($filter['pay_status'] != -1) {
            $res = $res->where('pay_status', $filter['pay_status']);
        }
        if ($filter['user_id']) {
            $res = $res->where('user_id', $filter['user_id']);
        }

        if ($filter['user_name']) {
            $user = Users::distinct()->select('user_id')->where('user_name', 'LIKE', '%' . mysql_like_quote($filter['user_name']) . '%');
            $user = $user->pluck('user_id');
            $user_id = BaseRepository::getToArray($user);

            $res = $user_id ? $res->whereIn('user_id', $user_id) : $res;
        }

        if (file_exists(WXAPP_MEDIA)) {
            $res = $res->where('media_type', 0);
        }

        if ($filter['start_time']) {
            $res = $res->where('add_time', '>=', $filter['start_time']);
        }
        if ($filter['end_time']) {
            $res = $res->where('add_time', '<=', $filter['end_time']);
        }

        if (isset($filter['order_referer']) && $filter['order_referer']) {
            if ($filter['order_referer'] == 'pc') {
                $res = $res->whereNotIn('referer', ['mobile', 'touch', 'H5', 'app', 'wxapp', 'ecjia-cashdesk']);
            } else {
                $res = $res->where('referer', $filter['order_referer']);
            }
        }

        if ($filter['order_cat']) {
            switch ($filter['order_cat']) {
                case 'stages':
                    $res = $res->where(function ($query) {
                        $query->whereHasIn('getBaitiaoLog');
                    });
                    break;
                case 'zc':
                    $res = $res->where('is_zc_order', 1);
                    break;
                case 'store':
                    $res = $res->where(function ($query) {
                        $query->whereHasIn('getStoreOrder');
                    });
                    break;
                case 'other':
                    $res = $res->where('extension_code', '<>', '');
                    break;
                case 'dbdd':
                    $res = $res->where('extension_code', 'snatch');
                    break;
                case 'msdd':
                    $res = $res->where('extension_code', 'seckill');
                    break;
                case 'tgdd':
                    $res = $res->where('extension_code', 'group_buy');
                    break;
                case 'pmdd':
                    $res = $res->where('extension_code', 'auction');
                    break;
                case 'jfdd':
                    $res = $res->where('extension_code', 'exchange_goods');
                    break;
                case 'ysdd':
                    $res = $res->where('extension_code', 'presale');
                    break;
                default:
            }
        }

        $firstSecToday = TimeRepository::getLocalMktime(0, 0, 0, TimeRepository::getLocalDate("m"), TimeRepository::getLocalDate("d"), TimeRepository::getLocalDate("Y"));
        $lastSecToday = TimeRepository::getLocalMktime(0, 0, 0, TimeRepository::getLocalDate("m"), TimeRepository::getLocalDate("d") + 1, TimeRepository::getLocalDate("Y")) - 1;

        //综合状态
        switch ($filter['composite_status']) {
            case CS_AWAIT_PAY:
                $res = $this->orderCommonService->orderQuerySelect($res, 'await_pay');
                break;

            case CS_AWAIT_SHIP:
                $res = $this->orderCommonService->orderQuerySelect($res, 'await_ship');
                break;

            case CS_FINISHED:
                $res = $this->orderCommonService->orderQuerySelect($res, 'finished');
                break;
            case CS_CONFIRM_TAKE:
                $res = $this->orderCommonService->orderQuerySelect($res, 'confirm_take');
                break;

            case PS_PAYING:
                if ($filter['composite_status'] != -1) {
                    $res = $res->where('pay_status', $filter['composite_status']);
                }
                break;
            case OS_SHIPPED_PART:
                if ($filter['composite_status'] != -1) {
                    $res = $res->where('shipping_status', $filter['composite_status'] - 2);
                }
                break;
            case CS_NEW_ORDER:
                $res = $res->where('add_time', '>=', $firstSecToday)
                    ->where('add_time', '<=', $lastSecToday)
                    ->where('shipping_status', SS_UNSHIPPED);
                break;
            case CS_NEW_PAID_ORDER:
                $res = $res->where('pay_time', '>=', $firstSecToday)
                    ->where('pay_time', '<=', $lastSecToday);
                break;
            default:
                if ($filter['composite_status'] != -1) {
                    $res = $res->where('order_status', $filter['composite_status']);
                }
        }

        /* 团购订单 */
        if ($filter['group_buy_id']) {
            $res = $res->where('extension_code', 'group_buy')
                ->where('extension_id', $filter['group_buy_id']);
        }

        /* 预售订单 */
        if ($filter['presale_id']) {
            $res = $res->where('extension_code', 'presale')
                ->where('extension_id', $filter['presale_id']);
        }

        /* 众筹订单 by wu */
        if ($filter['pid']) {
            // 众筹项目id
            $zg_res = ZcGoods::select('id')->where('pid', $filter['pid']);
            $zg_res = BaseRepository::getToArrayGet($zg_res);
            $goods_ids = BaseRepository::getFlatten($zg_res);

            $res = $res->where('is_zc_order', 1)->whereIn('zc_goods_id', $goods_ids);
        }
        // 众筹商品id
        if ($filter['gid']) {
            $res = $res->where('is_zc_order', 1)->where('zc_goods_id', $filter['gid']);
        }

        $admin_id = $this->commonManageService->getAdminId();

        /* 如果管理员属于某个办事处，只列出这个办事处管辖的订单 */
        $agency_id = AdminUser::where('user_id', $admin_id)
            ->where('action_list', '<>', 'all')
            ->value('agency_id');
        $agency_id = $agency_id ? $agency_id : 0;

        if ($agency_id > 0) {
            $res = $res->where('agency_id', $agency_id);
        }

        $page_size = request()->cookie('dsccp_page_size');
        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        } elseif (intval($page_size) > 0) {
            $filter['page_size'] = intval($page_size);
        } else {
            $filter['page_size'] = 15;
        }

        if (empty($filter['start_take_time']) || empty($filter['end_take_time'])) {
            if ($store_search == 0 && isset($adminru['ru_id']) && $adminru['ru_id'] == 0) {
                $res = $res->where('ru_id', 0)->where('main_count', 0);
            }
        }

        if (!empty($filter['start_take_time']) || !empty($filter['end_take_time'])) {
            $res = $res->where(function ($query) use ($filter) {
                $query->whereHasIn('getOrderAction', function ($query) use ($filter) {
                    if ($filter['start_take_time']) {
                        $query = $query->where('log_time', '>=', $filter['start_take_time']);
                    }
                    if ($filter['end_take_time']) {
                        $query = $query->where('log_time', '<=', $filter['end_take_time']);
                    }

                    $this->orderCommonService->orderQuerySelect($query, 'finished');
                });
            });
        }

        //判断订单筛选条件
        switch ($filter['serch_type']) {
            case 0:
                //待确认
                $res = $res->where('order_status', OS_UNCONFIRMED);
                break;
            case 1:
                //待付款
                $res = $res->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED])->where('pay_status', PS_UNPAYED);
                break;
            case 2:
                //待收货，含已发货(部分商品)
                $res = $res->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED])->whereIn('shipping_status', [SS_SHIPPED, SS_SHIPPED_PART]);
                break;
            case 3:
                //已完成
                $res = $res->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED])->where('pay_status', PS_PAYED)->where('shipping_status', SS_RECEIVED);
                break;
            case 4:
                //付款中
                $res = $res->where('pay_status', PS_PAYING);
                break;
            case 5:
                //取消
                $res = $res->where('order_status', OS_CANCELED);
                break;
            case 6:
                //无效
                $res = $res->where('order_status', OS_INVALID);
                break;
            case 7:
                //退货
                $res = $res->whereNotIn('order_status', ["'" . OS_CANCELED . "', '" . OS_INVALID . "', '" .
                    OS_RETURNED . "', '" . OS_RETURNED_PART . "', '" . OS_ONLY_REFOUND . "'"]);

                $res = $res->where(function ($query) {
                    $query->whereHasIn('getOrderReturn', function ($query) {
                        $query->whereIn('return_type', [1, 3])
                            ->where('refound_status', 0)
                            ->where('return_status', '<>', 6);
                    });
                });

                break;
            case 8:
                //待发货,--191012 update：后台部分发货加入待发货列表，前台部分发货加入待收货列表
                $res = $this->orderCommonService->orderQuerySelect($res, 'await_ship');
                break;
        }

        /* 记录总数 */
        $filter['record_count'] = $res->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        if ($page > 0) {
            $filter['page'] = $page;
            $filter['start'] = ($page - 1) * $filter['page_size'];
        }

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 查询 */
        $res = $res->selectRaw("*, extension_code AS oi_extension_code, extension_code AS o_extension_code, " .
            "(" . $this->orderCommonService->orderTotalField() . ") AS total_fee, (goods_amount + tax + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee - discount - vc_dis_money) AS total_fee_order ");

        foreach (['order_sn', 'consignee', 'email', 'address', 'zipcode', 'tel', 'user_name'] as $val) {
            $filter[$val] = stripslashes($filter[$val]);
        }

        $res = $res->orderBy($filter['sort_by'], $filter['sort_order']);
        if ($filter['start'] > 0) {
            $res = $res->skip($filter['start']);
        }

        if ($filter['page_size'] > 0) {
            $res = $res->take($filter['page_size']);
        }

        $row = BaseRepository::getToArrayGet($res);

        if ($row) {

            /**
             * 当前订单ID
             */
            $order_id = BaseRepository::getKeyPluck($row, 'order_id');

            $drpOrder = [];
            if (file_exists(MOBILE_DRP)) {
                $drpOrder = OrderDataHandleService::isDrpOrder($order_id);
            }

            $childOrderList = OrderDataHandleService::orderDataList($order_id, ['order_id', 'main_order_id', 'order_sn'], 1);
            $storeOrder = OrderDataHandleService::isStoreOrder($order_id);
            $orderBill = $this->commissionDataHandleService->orderCommissionBill($order_id);
            $valueCard = OrderDataHandleService::orderValueCardRecord($order_id);
            $orderGoodsList = $this->orderGoodsDataHandleService->manageOrderGoods($order_id);
            $orderGoodsList = ArrRepository::getArrCollapse($orderGoodsList);

            $orderGoodsAttrIdList = BaseRepository::getKeyPluck($orderGoodsList, 'goods_attr_id');
            $orderGoodsAttrIdList = BaseRepository::getArrayUnique($orderGoodsAttrIdList);
            $orderGoodsAttrIdList = ArrRepository::getArrayUnset($orderGoodsAttrIdList);

            $productsGoodsAttrList = [];
            if ($orderGoodsAttrIdList) {
                $orderGoodsAttrIdList = BaseRepository::getImplode($orderGoodsAttrIdList);
                $productsGoodsAttrList = GoodsDataHandleService::getGoodsAttrDataList($orderGoodsAttrIdList, ['goods_attr_id', 'attr_img_flie', 'attr_gallery_flie']);
            }

            $packageKeyList = BaseRepository::getColumn($orderGoodsList, 'extension_code', 'goods_id');
            if ($packageKeyList) {
                $act_id = BaseRepository::getArrayKeys($packageKeyList);
                $packageBuyList = GoodsDataHandleService::getGoodsActivityDataList($act_id, GAT_PACKAGE);
            } else {
                $packageBuyList = [];
            }

            $stages = OrderDataHandleService::isStagesBaiTiao($order_id);
            $deliveryList = OrderDataHandleService::getDeliveryOrderDataList($order_id, ['order_id', 'delivery_id']);
            $deliveryList = ArrRepository::getArrCollapse($deliveryList);
            $orderReturnList = OrderDataHandleService::getOrderReturnDataList($order_id, ['ret_id', 'order_id', 'return_status'], 'order_id');
            $orderReturnList = ArrRepository::getArrCollapse($orderReturnList);
            $orderTradeList = OrderDataHandleService::getTradeSnapshotDataList($order_id);

            /**
             * 当前会员ID
             */
            $user_id = BaseRepository::getKeyPluck($row, 'user_id');
            $user = $this->userDataHandleService->orderUser($user_id);

            /**
             * 当前商家ID
             */
            $ru_id = BaseRepository::getKeyPluck($row, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            $countryRegion = BaseRepository::getColumn($row, 'country', 'order_id');
            $provinceRegion = BaseRepository::getColumn($row, 'province', 'order_id');
            $cityRegion = BaseRepository::getColumn($row, 'city', 'order_id');
            $districtRegion = BaseRepository::getColumn($row, 'district', 'order_id');
            $streetRegion = BaseRepository::getColumn($row, 'street', 'order_id');

            $orderRegion = OrderDataHandleService::orderRegionAddress($countryRegion, $provinceRegion, $cityRegion, $districtRegion, $streetRegion);

            foreach ($row as $key => $value) {

                $shop_information = $merchantList[$value['ru_id']] ?? []; //通过ru_id获取到店铺信息;

                //判断是否分销订单
                $row[$key]['is_drp_order'] = $drpOrder[$value['order_id']]['is_drp'] ?? 0;

                //判断是否为门店订单
                $row[$key]['is_store_order'] = $storeOrder[$value['order_id']]['is_store'] ?? 0;

                $is_stages = $stages[$value['order_id']]['is_stages'] ?? 0;
                $row[$key]['is_stages'] = $is_stages;

                //账单编号
                $bill = $orderBill[$value['order_id']] ?? [];

                if ($bill) {
                    $row[$key]['bill_id'] = $bill['id'];
                    $row[$key]['bill_sn'] = $bill['bill_sn'];
                    $row[$key]['seller_id'] = $bill['seller_id'];
                    $row[$key]['proportion'] = $bill['proportion'];
                    $row[$key]['commission_model'] = $bill['commission_model'];
                }

                //查会员名称
                $value['buyer'] = $user[$value['user_id']]['user_name'] ?? '';
                $row[$key]['buyer'] = !empty($value['buyer']) ? $value['buyer'] : lang('admin/order.anonymous');

                $row[$key]['ru_id'] = $value['ru_id'];

                $row[$key]['formated_order_amount'] = $this->dscRepository->getPriceFormat($value['order_amount']);
                $row[$key]['formated_money_paid'] = $this->dscRepository->getPriceFormat($value['money_paid']);
                $row[$key]['formated_total_fee'] = $this->dscRepository->getPriceFormat($value['total_fee']);
                $row[$key]['old_shipping_fee'] = $value['shipping_fee'];
                $row[$key]['shipping_fee'] = $this->dscRepository->getPriceFormat($value['shipping_fee']);
                $row[$key]['short_order_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $value['add_time']);

                $value_card = $valueCard[$value['order_id']]['use_val'] ?? 0;
                $row[$key]['value_card'] = $value_card;
                $row[$key]['formated_value_card'] = $this->dscRepository->getPriceFormat($value_card);

                $row[$key]['formated_total_fee_order'] = $this->dscRepository->getPriceFormat($value['total_fee_order']);

                /* 取得区域名 */
                $country = $orderRegion['country'][$value['order_id']]['region_name'] ?? '';
                $province = $orderRegion['province'][$value['order_id']]['region_name'] ?? '';
                $city = $orderRegion['city'][$value['order_id']]['region_name'] ?? '';
                $district = $orderRegion['district'][$value['order_id']]['region_name'] ?? '';
                $street = $orderRegion['street'][$value['order_id']]['region_name'] ?? '';

                $row[$key]['region'] = OrderDataHandleService::orderRegionCombination($country, $province, $city, $district, $street);

                //查询子订单
                $row[$key]['child_list'] = $childOrderList[$value['order_id']] ?? '';
                $row[$key]['order_child'] = $value['main_count'];

                if ($value['order_status'] == OS_INVALID || $value['order_status'] == OS_CANCELED) {
                    /* 如果该订单为无效或取消则显示删除链接 */
                    $row[$key]['can_remove'] = 1;
                } else {
                    $row[$key]['can_remove'] = 0;
                }

                $row[$key]['is_order_return'] = 0;

                if (isset($value['is_zc_order']) && $value['is_zc_order'] == 1 && $value['zc_goods_id'] > 0) {
                    $zc_goods = ZcGoods::where('id', $value['zc_goods_id']);
                    $zc_goods = $zc_goods->with('getZcProject');
                    $zc_goods = BaseRepository::getToArrayFirst($zc_goods);

                    $zc_project = $zc_goods['get_zc_project'] ?? [];

                    // 众筹失败且已支付订单 原路退款
                    if (in_array($value['order_status'], [OS_CONFIRMED, OS_SPLITED]) && $value['pay_status'] == PS_PAYED && $value['main_count'] == 0) {
                        CrowdRefoundService::zcRefund($value, $zc_project);
                    }

                    // 众筹订单信息
                    $row[$key]['goods_list'][] = [
                        'goods_name' => $zc_project['title'] ?? '',
                        'goods_thumb' => $this->dscRepository->getImagePath($zc_project['title_img'] ?? ''),
                        'goods_price' => !empty($zc_goods) ? $zc_goods['price'] : 0,
                        'format_goods_price' => !empty($zc_goods) ? $this->dscRepository->getPriceFormat($zc_goods['price']) : 0,
                        'goods_number' => 1
                    ];
                } else {

                    $sql = [
                        'where' => [
                            [
                                'name' => 'order_id',
                                'value' => $value['order_id']
                            ]
                        ]
                    ];
                    $goods_list = BaseRepository::getArraySqlGet($orderGoodsList, $sql);

                    if ($goods_list) {
                        $iog_extension_codes = [];
                        $is_cloud_order = 0;
                        foreach ($goods_list as $idx => $goods) {
                            if ($goods['extension_code']) {
                                $iog_extension_codes[$idx] = $goods['extension_code'];
                            }

                            $sql = [
                                'where' => [
                                    [
                                        'name' => 'order_id',
                                        'value' => $value['order_id']
                                    ],
                                    [
                                        'name' => 'goods_id',
                                        'value' => $goods['goods_id']
                                    ]
                                ]
                            ];
                            $tradeInfo = BaseRepository::getArraySqlFirst($orderTradeList, $sql);
                            $trade_id = $tradeInfo['trade_id'] ?? 0;

                            if ($trade_id) {
                                $goods_list[$idx]['trade_url'] = $this->dscRepository->dscUrl("trade_snapshot.php?act=trade&user_id=" . $value['user_id'] . "&tradeId=" . $trade_id . "&snapshot=true");
                            }

                            $goods_list[$idx]['ret_id'] = $goods['ret_id'] ?? 0;
                            if ($goods_list[$idx]['ret_id'] > 0) {

                                $sql = [
                                    'where' => [
                                        [
                                            'name' => 'order_id',
                                            'value' => $value['order_id']
                                        ],
                                        [
                                            'name' => 'ret_id',
                                            'value' => $goods_list[$idx]['ret_id']
                                        ]
                                    ]
                                ];
                                $orderReturn = BaseRepository::getArraySqlFirst($orderReturnList, $sql);

                                $return_status = $orderReturn['return_status'] ?? 0;

                                if ($return_status < 0) {
                                    $return_status = lang('user.only_return_money');
                                } else {
                                    $return_status = lang('user.rf.' . $return_status);
                                }

                                $goods_list[$idx]['back_order'] = [
                                    'return_status' => $return_status
                                ];

                                //判断是否为退换货订单
                                $row[$key]['is_order_return'] = 1;
                            }

                            //超值礼包图片
                            if ($goods['extension_code'] == 'package_buy') {

                                $activity = $packageBuyList[$goods['goods_id']] ?? [];

                                $activity_thumb = $activity['activity_thumb'] ?? '';
                                $goods_list[$idx]['goods_thumb'] = $activity_thumb ? $this->dscRepository->getImagePath($activity_thumb) : $this->dscRepository->dscUrl('themes/ecmoban_dsc2017/images/17184624079016pa.jpg');
                                $goods_list[$idx]['goods_img'] = $goods_list[$idx]['goods_thumb'];

                                $goods_list[$idx]['package_goods_list'] = $this->getPackageGoodsList($goods['goods_id']);
                            }

                            $goods_attr_id = $goods['goods_attr_id'] ?? '';
                            $goods_attr_id = BaseRepository::getExplode($goods_attr_id);
                            $goods_list[$idx]['goods_thumb'] = $this->goodsAttrService->cartGoodsAttrImage($goods_attr_id, $productsGoodsAttrList, $goods_list[$idx]['goods_thumb']);

                            $goods_list[$idx]['format_goods_price'] = $this->dscRepository->getPriceFormat($goods['goods_price']);

                            //判断是否是贡云订单
                            $cloud_count = OrderCloud::where('rec_id', $goods['rec_id'])->count();
                            if ($cloud_count > 0) {
                                $is_cloud_order = 1;
                            }
                        }

                        $iog_extension_codes = $iog_extension_codes ? array_unique($iog_extension_codes) : [];
                        $row[$key]['iog_extension_codes'] = $iog_extension_codes ? implode(',', $iog_extension_codes) : '';
                    }
                    $row[$key]['is_cloud_order'] = $is_cloud_order ?? 0;
                    $row[$key]['goods_list'] = $goods_list;

                    $row[$key]['self_run'] = $shop_information['self_run'] ?? '';
                    $row[$key]['user_name'] = $shop_information['shop_name'] ?? '';

                    if (!empty($child_list)) {
                        $row[$key]['shop_name'] = lang('manage/order.to_order_sn2');
                    } else {
                        $row[$key]['shop_name'] = $row[$key]['user_name'];
                    }
                }

                if (config('shop.show_mobile') == 0) {
                    $row[$key]['mobile'] = $this->dscRepository->stringToStar($row[$key]['mobile']);
                    $row[$key]['buyer'] = $this->dscRepository->stringToStar($row[$key]['buyer']);
                    $row[$key]['tel'] = $this->dscRepository->stringToStar($row[$key]['tel']);
                    $row[$key]['email'] = $this->dscRepository->stringToStar($row[$key]['email']);
                }

                if ($value['team_id'] > 0) {
                    $teamInfo = $this->teamOrderUserNum($value['team_id']);

                    $row[$key]['is_team'] = $teamInfo['is_team'];

                    if ($row[$key]['is_team'] == 1) {
                        $team_content = sprintf(lang('admin/order.order_team_ok'), $teamInfo['team_order_user_num']);
                    } else {
                        $team_content = sprintf(lang('admin/order.order_team_false'), $teamInfo['team_order_user_num'], $teamInfo['team_false_user_num']);
                        // 拼团失败
                        if ($value['pay_status'] == PS_PAYED && $value['main_count'] == 0) {
                            app(TeamService::class)->checkRefund($value['team_id']);
                        }
                    }

                    $row[$key]['team_content'] = $team_content;
                } else {
                    /* 默认 */
                    $row[$key]['is_team'] = 1;
                }

                $row[$key]['store_id'] = $storeOrder[$value['order_id']]['is_store'] ?? 0;

                //可执行的操作列表
                $row[$key]['operable_list'] = $this->operableList($value);

                $sql = [
                    'where' => [
                        [
                            'name' => 'order_id',
                            'value' => $value['order_id']
                        ]
                    ]
                ];
                $delivery = BaseRepository::getArraySqlFirst($deliveryList, $sql);
                $row[$key]['delivery_id'] = $delivery['delivery_id'] ?? 0;
            }
        }

        return ['orders' => $row, 'filter' => $filter, 'page_count' => intval($filter['page_count']), 'record_count' => $filter['record_count']];
    }

    /**
     * 取得供货商列表
     * @return array    二维数组
     */
    public function getSuppliersList()
    {
        if (!file_exists(SUPPLIERS)) {
            return [];
        }

        $res = \App\Modules\Suppliers\Models\Suppliers::where('is_check', 1)->orderBy('suppliers_name');
        return BaseRepository::getToArrayGet($res);
    }

    /**
     * 取得订单商品
     *
     * @param array $order
     * @return array
     * @throws \Exception
     */
    public function getOrderGoods($order = [])
    {
        $goods_list = [];
        $goods_attr = [];

        $res = OrderGoods::where('order_id', $order['order_id']);
        $res = $res->with(['getProducts', 'getOrder']);
        $res = $res->with(['getGoods' => function ($query) {
            $query->with(['getBrand']);
        }]);
        $res = BaseRepository::getToArrayGet($res);

        if (CROSS_BORDER === true) { // 跨境多商户
            $rate_price = 0;
        }

        $productsGoodsAttr = BaseRepository::getKeyPluck($res, 'goods_attr_id');
        $productsGoodsAttrList = GoodsDataHandleService::getGoodsAttrDataList($productsGoodsAttr, ['goods_attr_id', 'attr_img_flie', 'attr_gallery_flie']);

        foreach ($res as $row) {
            $row['product_sn'] = '';

            if (isset($row['get_products']) && !empty($row['get_products'])) {
                $row['product_sn'] = $row['get_products']['product_sn'];
            }

            $row['iog_extension_code'] = $row['extension_code'];

            $row['model_inventory'] = '';
            $row['model_attr'] = '';
            $row['suppliers_id'] = '';
            $row['storage'] = '';
            $row['goods_thumb'] = '';
            $row['goods_img'] = '';
            $row['bar_code'] = '';
            $row['goods_unit'] = '';

            $row['brand_name'] = '';

            if (isset($row['get_goods']) && !empty($row['get_goods'])) {
                $row['model_inventory'] = $row['get_goods']['model_inventory'];
                $row['model_attr'] = $row['get_goods']['model_attr'];
                $row['suppliers_id'] = $row['get_goods']['suppliers_id'];
                $row['storage'] = $row['get_goods']['goods_number'];
                $row['goods_thumb'] = $row['get_goods']['goods_thumb'];
                $row['goods_img'] = $row['get_goods']['goods_img'];
                $row['bar_code'] = $row['get_goods']['bar_code'];
                $row['goods_unit'] = $row['get_goods']['goods_unit'];

                if (isset($row['get_goods']['get_brand']) && !empty($row['get_goods']['get_brand'])) {
                    $row['brand_name'] = $row['get_goods']['get_brand']['brand_name'];
                }
            }

            $row['order_sn'] = '';
            $row['oi_extension_code'] = '';
            $row['extension_id'] = '';

            if (isset($row['get_order']) && !empty($row['get_order'])) {
                $row['order_sn'] = $row['get_order']['order_sn'];
                $row['oi_extension_code'] = $row['get_order']['extension_code'];
                $row['extension_id'] = $row['get_order']['extension_id'];
            }

            //ecmoban模板堂 --zhuo start
            if ($row['product_id'] > 0) {
                $products = $this->goodsWarehouseService->getWarehouseAttrNumber($row['goods_id'], $row['goods_attr_id'], $row['warehouse_id'], $row['area_id'], $row['area_city'], $row['model_attr']);
                $row['goods_storage'] = isset($products['product_number']) ? $products['product_number'] : 0;
            } else {
                if ($row['model_inventory'] == 1) {
                    $row['storage'] = get_warehouse_area_goods($row['warehouse_id'], $row['goods_id'], 'warehouse_goods');
                } elseif ($row['model_inventory'] == 2) {
                    $row['storage'] = get_warehouse_area_goods($row['area_id'], $row['goods_id'], 'warehouse_area_goods');
                }
            }
            //ecmoban模板堂 --zhuo end

            //图片显示
            $row['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
            $row['goods_img'] = $this->dscRepository->getImagePath($row['goods_img']);

            $row['formated_subtotal'] = $this->dscRepository->getPriceFormat($row['goods_price'] * $row['goods_number']);
            $row['formated_goods_price'] = $this->dscRepository->getPriceFormat($row['goods_price']);

            $goods_attr[] = explode(' ', trim($row['goods_attr'])); //将商品属性拆分为一个数组

            if ($row['extension_code'] == 'package_buy') {
                $row['storage'] = '';
                $row['brand_name'] = '';
                $row['package_goods_list'] = $this->getPackageGoodsList($row['goods_id']);

                $activity_thumb = $activity = GoodsActivity::where('act_id', $row['goods_id'])->value('activity_thumb');
                $activity_thumb = $activity_thumb ? $this->dscRepository->getImagePath($activity_thumb) : $this->dscRepository->dscUrl('themes/ecmoban_dsc2017/images/17184624079016pa.jpg');
                $row['goods_thumb'] = $activity_thumb;
            }

            $goods_attr_id = $row['goods_attr_id'] ?? '';
            $goods_attr_id = BaseRepository::getExplode($goods_attr_id);
            $row['goods_thumb'] = $this->goodsAttrService->cartGoodsAttrImage($goods_attr_id, $productsGoodsAttrList, $row['goods_thumb']);

            $ge_res = GoodsExtend::where('goods_id', $row['goods_id']);
            $goods_extend = BaseRepository::getToArrayFirst($ge_res);
            if (!empty($goods_extend)) {
                if ($row['is_reality'] == -1 && $goods_extend) {
                    $row['is_reality'] = $goods_extend['is_reality'];
                }
                if ($goods_extend['is_return'] == -1 && $goods_extend) {
                    $row['is_return'] = $goods_extend['is_return'];
                }
                if ($goods_extend['is_fast'] == -1 && $goods_extend) {
                    $row['is_fast'] = $goods_extend['is_fast'];
                }
            }
            //获得退货表数据
            $row['ret_id'] = OrderReturn::where('rec_id', $row['rec_id'])->value('ret_id');
            $row['ret_id'] = $row['ret_id'] ? $row['ret_id'] : 0;

            $row['back_order'] = return_order_info($row['ret_id']);

            $trade_id = $this->orderCommonService->getFindSnapshot($row['order_sn'], $row['goods_id']);
            if ($trade_id) {
                $row['trade_url'] = $this->dscRepository->dscUrl("trade_snapshot.php?act=trade&user_id=" . $row['user_id'] . "&tradeId=" . $trade_id . "&snapshot=true");
            }

            //处理货品id
            $row['product_id'] = empty($row['product_id']) ? 0 : $row['product_id'];

            if (CROSS_BORDER === true) { // 跨境多商户
                if ($row['rate_price'] > 0) {
                    $rate_price += $row['rate_price'];
                }
            }
            $goods_list[] = $row;
        }

        $attr = [];
        $arr = [];
        if ($goods_attr) {
            foreach ($goods_attr as $index => $array_val) {
                $array_val = BaseRepository::getExplode($array_val);

                if ($array_val) {
                    foreach ($array_val as $value) {
                        $arr = explode(':', $value);//以 : 号将属性拆开
                        $attr[$index][] = @['name' => $arr[0], 'value' => $arr[1]];
                    }
                }
            }
        }

        if (CROSS_BORDER === true) { // 跨境多商户
            return ['goods_list' => $goods_list, 'attr' => $attr, 'rate_price' => $rate_price];
        } else {
            return ['goods_list' => $goods_list, 'attr' => $attr];
        }
    }

    /**
     * 取得礼包列表
     * @param integer $package_id 订单商品表礼包类商品id
     * @return array
     */
    public function getPackageGoodsList($package_id)
    {
        $res = PackageGoods::where('package_id', $package_id);
        $res = $res->with(['getGoods', 'getProducts']);
        $resource = BaseRepository::getToArrayGet($res);


        if (!$resource) {
            return [];
        }

        $row = [];

        /* 生成结果数组 取存在货品的商品id 组合商品id与货品id */
        $good_product_str = '';
        if (!empty($resource)) {
            foreach ($resource as $_row) {
                $_row['order_goods_number'] = $_row['goods_number'];

                $_row['goods_name'] = '';
                $_row['goods_number'] = '';
                $_row['goods_sn'] = '';
                $_row['is_real'] = '';
                if (isset($_row['get_goods']) && !empty($_row['get_goods'])) {
                    $_row['goods_name'] = $_row['get_goods']['goods_name'];
                    if ($_row['product_id'] < 1) {
                        $_row['goods_number'] = $_row['get_goods']['goods_number'];
                    }
                    $_row['goods_sn'] = $_row['get_goods']['goods_sn'];
                    $_row['is_real'] = $_row['get_goods']['is_real'];
                }

                $_row['goods_attr'] = '';
                $_row['product_sn'] = '';
                if (isset($_row['get_products']) && !empty($_row['get_products'])) {
                    if ($_row['product_id'] > 0) {
                        $_row['goods_number'] = $_row['get_products']['product_number'];
                    }
                    $_row['goods_attr'] = $_row['get_products']['goods_attr'];
                    $_row['product_id'] = $_row['get_products']['product_id'];
                    $_row['product_sn'] = $_row['get_products']['product_sn'];
                }
                $_row['product_id'] = $_row['product_id'] ? $_row['product_id'] : 0;

                if ($_row['product_id'] > 0) {
                    /* 取存商品id */
                    $good_product_str .= ',' . $_row['goods_id'];

                    /* 组合商品id与货品id */
                    $_row['g_p'] = $_row['goods_id'] . '_' . $_row['product_id'];
                } else {
                    /* 组合商品id与货品id */
                    $_row['g_p'] = $_row['goods_id'];
                }

                //生成结果数组
                $row[] = $_row;
            }
        }
        $good_product_str = trim($good_product_str, ',');

        /* 释放空间 */
        unset($resource, $_row, $sql);

        /* 取商品属性 */
        if ($good_product_str != '') {
            $good_product_str = BaseRepository::getExplode($good_product_str);
            $res = GoodsAttr::whereIn('goods_id', $good_product_str);
            $res = $res->with(['getGoodsAttribute' => function ($query) {
                $query->where('attr_type', 1);
            }]);

            $res = $res->orderBy('goods_attr_id');

            $result_goods_attr = BaseRepository::getToArrayGet($res);

            $_goods_attr = [];
            foreach ($result_goods_attr as $value) {
                $value['attr_name'] = '';
                if (isset($value['get_goods_attribute']) && !empty($value['get_goods_attribute'])) {
                    $value['attr_name'] = $value['get_goods_attribute']['attr_name'];
                }

                $_goods_attr[$value['goods_attr_id']] = $value;
            }
        }

        /* 过滤货品 */
        $format[0] = "%s:%s[%d] <br>";
        $format[1] = "%s--[%d]";
        foreach ($row as $key => $value) {
            if ($value['goods_attr'] != '') {
                $goods_attr_array = explode('|', $value['goods_attr']);

                $goods_attr = [];
                foreach ($goods_attr_array as $_attr) {
                    $goods_attr[] = sprintf($format[0], $_goods_attr[$_attr]['attr_name'], $_goods_attr[$_attr]['attr_value'], $_goods_attr[$_attr]['attr_price']);
                }

                $row[$key]['goods_attr_str'] = implode('', $goods_attr);
            }

            $row[$key]['goods_name'] = sprintf($format[1], $value['goods_name'], $value['order_goods_number']);
        }

        return $row;
    }

    /**
     * 订单单个商品或货品的已发货数量
     *
     * @param int $order_id 订单 id
     * @param int $goods_id 商品 id
     * @param int $product_id 货品 id
     *
     * @return  int
     */
    public function orderDeliveryNum($order_id, $goods_id, $product_id = 0)
    {
        $res = DeliveryGoods::where('goods_id', $goods_id)->where(function ($query) {
            $query->whereNull('extension_code')
                ->orWhere('extension_code', '<>', 'package_buy');
        });

        if ($product_id > 0) {
            $res = $res->where('product_id', $product_id);
        }

        $sum = $res->whereHasIn('getDeliveryOrder', function ($query) use ($order_id) {
            $query->where('status', 0)->where('order_id', $order_id);
        })->sum('send_number');

        if (empty($sum)) {
            $sum = 0;
        }

        return $sum;
    }

    /**
     * 判断订单是否已发货（含部分发货）
     * @param int $order_id 订单 id
     * @return  int     1，已发货；0，未发货
     */
    public function orderDeliveryed($order_id)
    {
        $return_res = 0;

        if (empty($order_id)) {
            return $return_res;
        }

        $sum = DeliveryOrder::where('order_id', $order_id)->where('status', 0)->count();

        if ($sum) {
            $return_res = 1;
        }

        return $return_res;
    }

    /**
     * 更新订单商品信息
     * @param int $order_id 订单 id
     * @param array $_sended Array(‘商品id’ => ‘此单发货数量’)
     * @param array $goods_list
     * @return  Bool
     */
    public function updateOrderGoods($order_id, $_sended, $goods_list = [])
    {
        if (!is_array($_sended) || empty($order_id)) {
            return false;
        }

        foreach ($_sended as $key => $value) {
            // 超值礼包
            if (is_array($value)) {
                if (!is_array($goods_list)) {
                    $goods_list = [];
                }

                foreach ($goods_list as $goods) {
                    if (($key != $goods['rec_id']) || (!isset($goods['package_goods_list']) || !is_array($goods['package_goods_list']))) {
                        continue;
                    }

                    $goods['package_goods_list'] = $this->packageGoods($goods['package_goods_list'], $goods['goods_number'], $goods['order_id'], $goods['extension_code'], $goods['goods_id']);
                    $pg_is_end = true;

                    foreach ($goods['package_goods_list'] as $pg_key => $pg_value) {
                        if ($pg_value['order_send_number'] != $pg_value['sended']) {
                            $pg_is_end = false; // 此超值礼包，此商品未全部发货

                            break;
                        }
                    }

                    // 超值礼包商品全部发货后更新订单商品库存
                    if ($pg_is_end) {
                        $goods_number = OrderGoods::where('order_id', $order_id)
                            ->where('goods_id', $goods['goods_id'])
                            ->value('goods_number');
                        $goods_number = $goods_number ? $goods_number : '';

                        $data = ['send_number' => $goods_number];
                        OrderGoods::where('order_id', $order_id)
                            ->where('goods_id', $goods['goods_id'])
                            ->update($data);
                    }
                }
            } // 商品（实货）（货品）
            elseif (!is_array($value)) {
                /* 检查是否为商品（实货）（货品） */
                foreach ($goods_list as $goods) {
                    if ($goods['rec_id'] == $key && $goods['is_real'] == 1) {
                        OrderGoods::where('order_id', $order_id)
                            ->where('rec_id', $key)
                            ->increment('send_number', $value);

                        break;
                    }
                }
            }
        }

        return true;
    }

    /**
     * 更新订单虚拟商品信息
     * @param int $order_id 订单 id
     * @param array $_sended Array(‘商品id’ => ‘此单发货数量’)
     * @param array $virtual_goods 虚拟商品列表
     * @return  Bool
     */
    public function updateOrderVirtualGoods($order_id, $_sended, $virtual_goods)
    {
        if (!is_array($_sended) || empty($order_id)) {
            return false;
        }
        if (empty($virtual_goods)) {
            return true;
        } elseif (!is_array($virtual_goods)) {
            return false;
        }

        foreach ($virtual_goods as $goods) {
            $res = OrderGoods::where('order_id', $order_id)
                ->where('goods_id', $goods['goods_id'])
                ->increment('send_number', $goods['num']);

            if ($res < 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * 订单中的商品是否已经全部发货
     * @param int $order_id 订单 id
     * @return  int     1，全部发货；0，未全部发货
     */
    public function getOrderFinish($order_id)
    {
        return OrderRepository::getOrderFinish($order_id);
    }

    /**
     * 判断订单的发货单是否全部发货
     * @param int $order_id 订单 id
     * @return  int     1，全部发货；0，未全部发货；-1，部分发货；-2，完全没发货；
     */
    public function getAllDeliveryFinish($order_id = 0)
    {
        return OrderRepository::getAllDeliveryFinish($order_id);
    }

    /**
     * 删除发货单(不包括已退货的单子)
     * @param int $order_id 订单 id
     * @return  int     1，成功；0，失败
     */
    public function delOrderDelivery($order_id)
    {
        $return_res = 0;

        if (empty($order_id)) {
            return $return_res;
        }

        $delivery_id = DeliveryOrder::where('order_id', $order_id)
            ->where('status', 0)
            ->value('delivery_id');
        $delivery_id = $delivery_id ? $delivery_id : 0;

        $query = DeliveryOrder::where('order_id', $order_id)
            ->where('status', 0)
            ->delete();
        DeliveryGoods::where('delivery_id', $delivery_id)->delete();

        if ($query) {
            $return_res = 1;
        }

        return $return_res;
    }

    /**
     * 删除订单所有相关单子
     * @param int $order_id 订单 id
     * @param array $action_array 操作列表 Array('delivery', 'back', ......)
     * @return  int     1，成功；0，失败
     */
    public function delDelivery($order_id = 0, $action_array = [])
    {
        $return_res = 0;

        if (empty($order_id) || empty($action_array)) {
            return $return_res;
        }

        $query_delivery = 1;
        $query_back = 1;
        if (in_array('delivery', $action_array)) {
            $delivery_id = DeliveryOrder::where('order_id', $order_id)->value('delivery_id');
            $delivery_id = $delivery_id ? $delivery_id : 0;

            $query_delivery = DeliveryOrder::where('order_id', $order_id)->delete();
            DeliveryGoods::where('delivery_id', $delivery_id)->delete();
        }
        if (in_array('back', $action_array)) {
            $back_id = BackOrder::where('order_id', $order_id)->value('back_id');
            $back_id = $back_id ? $back_id : 0;

            $query_back = BackOrder::where('order_id', $order_id)->delete();
            BackGoods::where('back_id', $back_id)->delete();
        }

        if ($query_delivery && $query_back) {
            $return_res = 1;
        }

        return $return_res;
    }

    /**
     * 获取发货单列表信息
     *
     * @return array
     * @throws \Exception
     */
    public function deliveryList()
    {
        //ecmoban模板堂 --zhuo start
        $adminru = get_admin_ru_id();
        //ecmoban模板堂 --zhuo end

        $ajax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;

        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'delivery_list';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 过滤信息 */
        $filter['delivery_sn'] = empty($_REQUEST['delivery_sn']) ? '' : trim($_REQUEST['delivery_sn']);
        $filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
        $filter['order_id'] = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
        $filter['goods_id'] = empty($_REQUEST['goods_id']) ? 0 : intval($_REQUEST['goods_id']);
        if ($ajax == 1 && !empty($_REQUEST['consignee'])) {
            $_REQUEST['consignee'] = json_str_iconv($_REQUEST['consignee']);
        }

        $filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? 1 : 0;  //商家和自营订单标识
        $filter['consignee'] = empty($_REQUEST['consignee']) ? '' : trim($_REQUEST['consignee']);
        $filter['status'] = isset($_REQUEST['status']) ? $_REQUEST['status'] : -1;
        $filter['order_referer'] = isset($_REQUEST['order_referer']) ? trim($_REQUEST['order_referer']) : '';

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'update_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        //卖场 start
        $filter['rs_id'] = empty($_REQUEST['rs_id']) ? 0 : intval($_REQUEST['rs_id']);
        if ($adminru['rs_id'] > 0) {
            $filter['rs_id'] = $adminru['rs_id'];
        }
        //卖场 end

        $res = DeliveryOrder::whereRaw(1);

        if ($filter['order_sn']) {
            $res = $res->where('order_sn', 'LIKE', '%' . mysql_like_quote($filter['order_sn']) . '%');
        }
        if ($filter['goods_id']) {
            $goods_id = $filter['goods_id'];
            $res = $res->where(function ($query) use ($goods_id) {
                $query->whereHasIn('getDeliveryGoods', function ($query) use ($goods_id) {
                    $query->where('goods_id', $goods_id);
                });
            });
        }
        if ($filter['consignee']) {
            $res = $res->where('consignee', 'LIKE', '%' . mysql_like_quote($filter['consignee']) . '%');
        }
        if ($filter['status'] >= 0) {
            $res = $res->where('status', 'LIKE', '%' . mysql_like_quote($filter['status']) . '%');
        }
        if ($filter['delivery_sn']) {
            $res = $res->where('delivery_sn', 'LIKE', '%' . mysql_like_quote($filter['delivery_sn']) . '%');
        }

        /* 获取管理员信息 */
        $admin_info = admin_info();

        /* 如果管理员属于某个办事处，只列出这个办事处管辖的发货单 */
        if ($admin_info['agency_id'] > 0) {
            $res = $res->where('agency_id', $admin_info['agency_id']);
        }

        /* 如果管理员属于某个供货商，只列出这个供货商的发货单 */
        if ($admin_info['suppliers_id'] > 0) {
            $res = $res->where('suppliers_id', $admin_info['suppliers_id']);
        }

        /* 分页大小 */
        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

        $page_size = request()->cookie('dsccp_page_size');
        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        } elseif (intval($page_size) > 0) {
            $filter['page_size'] = intval($page_size);
        } else {
            $filter['page_size'] = 15;
        }

        $ru_id = $adminru['ru_id'] ?? 0;
        $res = $res->whereHasIn('getOrderInfo', function ($query) use ($ru_id) {
            if ($ru_id > 0) {
                $query->where('ru_id', $ru_id);
            }
        });

        if ($filter['seller_list']) {
            $res = $res->whereHasIn('getOrderInfo', function ($query) use ($ru_id) {
                $query->where('ru_id', '>', 0);
            });
        } else {
            $res = $res->whereHasIn('getOrderInfo', function ($query) use ($ru_id) {
                $query->where('ru_id', 0);
            });
        }

        if ($filter['order_referer']) {
            $order_referer = $filter['order_referer'];
            $res = $res->whereHasIn('getOrderInfo', function ($query) use ($order_referer) {
                if ($order_referer == 'pc') {
                    $query->whereNotIn('referer', ['mobile', 'touch', 'H5', 'app', 'wxapp', 'ecjia-cashdesk']);
                } else {
                    $query->where('referer', $order_referer);
                }
            });
        }

        $res = $res->with([
            'getOrderInfo' => function ($query) {
                $query->select('order_id', 'order_status', 'pay_status', 'shipping_status');
            }
        ]);

        /* 记录总数 */
        $filter['record_count'] = $res->count('delivery_id');

        $filter['page_count'] = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 获取供货商列表 */
        $suppliers_list = $this->getSuppliersList();
        $_suppliers_list = [];
        foreach ($suppliers_list as $value) {
            $_suppliers_list[$value['suppliers_id']] = $value['suppliers_name'];
        }

        /* 查询 */
        $res = $res->orderBy($filter['sort_by'], $filter['sort_order'])
            ->offset(($filter['page'] - 1) * $filter['page_size'])
            ->limit($filter['page_size']);

        $row = BaseRepository::getToArrayGet($res);

        if ($row) {

            $order_id = BaseRepository::getKeyPluck($row, 'order_id');
            $orderGoodsList = OrderDataHandleService::orderGoodsDataList($order_id, ['order_id', 'ru_id'], 1);
            $orderGoodsList = ArrRepository::getArrCollapse($orderGoodsList);

            $seller_id = BaseRepository::getKeyPluck($orderGoodsList, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($seller_id);

            foreach ($row as $key => $value) {

                $sql = [
                    'where' => [
                        [
                            'name' => 'order_id',
                            'value' => $value['order_id']
                        ]
                    ]
                ];
                $orderGoods = BaseRepository::getArraySqlFirst($orderGoodsList, $sql);
                $ru_id = $orderGoods['ru_id'] ?? -1;
                $shop_information = $ru_id > -1 ? $merchantList[$ru_id] : [];

                if (config('shop.show_mobile') == 0) {
                    $row[$key]['email'] = $this->dscRepository->stringToStar($value['email']);
                    $row[$key]['mobile'] = $this->dscRepository->stringToStar($value['mobile']);
                    $row[$key]['consignee'] = $this->dscRepository->stringToStar($value['consignee']);
                    $row[$key]['tel'] = $this->dscRepository->stringToStar($value['tel']);
                }

                $row[$key]['add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $value['add_time']);
                $row[$key]['update_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $value['update_time']);
                if ($value['status'] == 1) {
                    $row[$key]['status_name'] = $GLOBALS['_LANG']['delivery_status'][1];
                } elseif ($value['status'] == 2) {
                    $row[$key]['status_name'] = $GLOBALS['_LANG']['delivery_status'][2];
                } else {
                    $row[$key]['status_name'] = $GLOBALS['_LANG']['delivery_status'][0];
                }
                $row[$key]['suppliers_name'] = isset($_suppliers_list[$value['suppliers_id']]) ? $_suppliers_list[$value['suppliers_id']] : '';

                $_goods_thumb = isset($row[$key]['goods_thumb']) && $row[$key]['goods_thumb'] ? $this->dscRepository->getImagePath($row[$key]['goods_thumb']) : '';
                $row[$key]['goods_thumb'] = $_goods_thumb;

                $row[$key]['ru_name'] = $shop_information['shop_name'] ?? '';

                // 订单信息
                if (!empty($value['get_order_info'])) {
                    $row[$key]['order_status'] = $value['get_order_info']['order_status'];
                    $row[$key]['pay_status'] = $value['get_order_info']['pay_status'];
                    $row[$key]['shipping_status'] = $value['get_order_info']['shipping_status'];
                    unset($row[$key]['get_order_info']);
                }
            }
        }

        $arr = ['delivery' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /**
     * 获取退货单列表信息
     *
     * @return array
     */
    public function backList()
    {
        $adminru = get_admin_ru_id();

        //取消获取cookie信息
        $ajax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;

        /* 过滤信息 */
        $filter['delivery_sn'] = empty($_REQUEST['delivery_sn']) ? '' : trim($_REQUEST['delivery_sn']);
        $filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
        $filter['order_id'] = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
        if ($ajax == 1 && !empty($_REQUEST['consignee'])) {
            $_REQUEST['consignee'] = json_str_iconv($_REQUEST['consignee']);
        }

        $filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? 1 : 0;  //商家和自营订单标识
        $filter['order_referer'] = isset($_REQUEST['order_referer']) ? trim($_REQUEST['order_referer']) : '';
        $filter['consignee'] = empty($_REQUEST['consignee']) ? '' : trim($_REQUEST['consignee']);

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'update_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        //卖场 start
        $filter['rs_id'] = empty($_REQUEST['rs_id']) ? 0 : intval($_REQUEST['rs_id']);
        if ($adminru['rs_id'] > 0) {
            $filter['rs_id'] = $adminru['rs_id'];
        }
        //卖场 end
        $res = BackOrder::whereRaw(1);

        if ($filter['order_sn']) {
            $res = $res->where('order_sn', 'LIKE', '%' . mysql_like_quote($filter['order_sn']) . '%');
        }
        if ($filter['consignee']) {
            $res = $res->where('consignee', 'LIKE', '%' . mysql_like_quote($filter['consignee']) . '%');
        }
        if ($filter['delivery_sn']) {
            $res = $res->where('delivery_sn', 'LIKE', '%' . mysql_like_quote($filter['delivery_sn']) . '%');
        }

        $ru_id = $adminru['ru_id'];
        $res = $res->whereHasIn('getOrderInfo', function ($query) use ($ru_id) {
            if ($ru_id > 0) {
                $query->where('ru_id', $ru_id);
            }
        });

        if ($filter['order_referer']) {
            if ($filter['order_referer'] == 'pc') {
                $res = $res->whereHasIn('getOrderInfo', function ($query) {
                    $query->whereNotIn('referer', ['mobile', 'touch', 'H5', 'app', 'wxapp', 'ecjia-cashdesk']);
                });
            } else {
                $order_referer = $filter['order_referer'];
                $res = $res->whereHasIn('getOrderInfo', function ($query) use ($order_referer) {
                    $query->where('referer', $order_referer);
                });
            }
        }

        /* 获取管理员信息 */
        $admin_info = admin_info();

        /* 如果管理员属于某个办事处，只列出这个办事处管辖的发货单 */
        if ($admin_info['agency_id'] > 0) {
            $res = $res->where('agency_id', $admin_info['agency_id']);
        }

        /* 如果管理员属于某个供货商，只列出这个供货商的发货单 */
        if ($admin_info['suppliers_id'] > 0) {
            $res = $res->where('suppliers_id', $admin_info['suppliers_id']);
        }

        /* 分页大小 */
        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

        $page_size = request()->cookie('dsccp_page_size');
        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        } elseif (intval($page_size) > 0) {
            $filter['page_size'] = intval($page_size);
        } else {
            $filter['page_size'] = 15;
        }

        if ($filter['seller_list']) {
            $res = $res->whereHasIn('getOrderInfo', function ($query) {
                $query->where('ru_id', '>', 0);
            });
        } else {
            $res = $res->whereHasIn('getOrderInfo', function ($query) {
                $query->where('ru_id', 0);
            });
        }

        /* 记录总数 */
        $filter['record_count'] = $res->distinct('back_id')->count();

        $filter['page_count'] = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */
        $res = $res->orderBy($filter['sort_by'], $filter['sort_order'])
            ->offset(($filter['page'] - 1) * $filter['page_size'])
            ->limit($filter['page_size']);

        $row = BaseRepository::getToArrayGet($res);
        /* 格式化数据 */
        foreach ($row as $key => $value) {
            $row[$key]['return_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $value['return_time']);
            $row[$key]['add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $value['add_time']);
            $row[$key]['update_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $value['update_time']);
            if ($value['status'] == 1) {
                $row[$key]['status_name'] = $GLOBALS['_LANG']['delivery_status'][1];
            } else {
                $row[$key]['status_name'] = $GLOBALS['_LANG']['delivery_status'][0];
            }
        }
        $arr = ['back' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /**
     * 取得发货单信息
     * @param int $delivery_order 发货单id（如果delivery_order > 0 就按id查，否则按sn查）
     * @param string $delivery_sn 发货单号
     * @return  array   发货单信息（金额都有相应格式化的字段，前缀是formated_）
     */
    public function deliveryOrderInfo($delivery_id, $delivery_sn = '')
    {
        $return_order = [];
        if (empty($delivery_id) || !is_numeric($delivery_id)) {
            return $return_order;
        }

        $res = DeliveryOrder::whereRaw(1);
        /* 获取管理员信息 */
        $admin_info = admin_info();

        /* 如果管理员属于某个办事处，只列出这个办事处管辖的发货单 */
        if ($admin_info['agency_id'] > 0) {
            $res = $res->where('agency_id', $admin_info['agency_id']);
        }

        /* 如果管理员属于某个供货商，只列出这个供货商的发货单 */
        if ($admin_info['suppliers_id'] > 0) {
            $res = $res->where('suppliers_id', $admin_info['suppliers_id']);
        }

        if ($delivery_id > 0) {
            $res = $res->where('delivery_id', $delivery_id);
        } else {
            $res = $res->where('delivery_sn', $delivery_sn);
        }

        $res = $res->with([
            'getRegionProvince' => function ($query) {
                $query->select('region_id', 'region_name');
            },
            'getRegionCity' => function ($query) {
                $query->select('region_id', 'region_name');
            },
            'getRegionDistrict' => function ($query) {
                $query->select('region_id', 'region_name');
            },
            'getRegionStreet' => function ($query) {
                $query->select('region_id', 'region_name');
            }
        ]);

        $delivery = BaseRepository::getToArrayFirst($res);

        if ($delivery) {

            /* 取得区域名 */
            $province = $delivery['get_region_province']['region_name'] ?? '';
            $city = $delivery['get_region_city']['region_name'] ?? '';
            $district = $delivery['get_region_district']['region_name'] ?? '';
            $street = $delivery['get_region_street']['region_name'] ?? '';
            $delivery['region'] = $province . ' ' . $city . ' ' . $district . ' ' . $street;

            /* 格式化金额字段 */
            $delivery['formated_insure_fee'] = $this->dscRepository->getPriceFormat($delivery['insure_fee'], false);
            $delivery['formated_shipping_fee'] = $this->dscRepository->getPriceFormat($delivery['shipping_fee'], false);

            /* 格式化时间字段 */
            $delivery['formated_add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $delivery['add_time']);
            $delivery['formated_update_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $delivery['update_time']);

            if (config('shop.show_mobile') == 0) {
                $delivery['email'] = $this->dscRepository->stringToStar($delivery['email']);
                $delivery['tel'] = $this->dscRepository->stringToStar($delivery['tel']);
                $delivery['mobile'] = $this->dscRepository->stringToStar($delivery['mobile']);
                $delivery['consignee'] = $this->dscRepository->stringToStar($delivery['consignee']);
            }

            $return_order = $delivery;
        }

        return $return_order;
    }

    /**
     *  取得退货单信息
     *
     * @param int $back_id 退货单 id（如果 back_id > 0 就按 id 查，否则按 sn 查）
     * @return array
     */
    public function backOrderInfo($back_id = 0)
    {
        $return_order = [];
        if (empty($back_id) || !is_numeric($back_id)) {
            return $return_order;
        }

        $res = BackOrder::where('back_id', $back_id);

        /* 获取管理员信息 */
        $admin_info = admin_info();

        /* 如果管理员属于某个办事处，只列出这个办事处管辖的发货单 */
        if ($admin_info['agency_id'] > 0) {
            $res = $res->where('agency_id', $admin_info['agency_id']);
        }

        /* 如果管理员属于某个供货商，只列出这个供货商的发货单 */
        if ($admin_info['suppliers_id'] > 0) {
            $res = $res->where('suppliers_id', $admin_info['suppliers_id']);
        }

        $res = $res->with([
            'getOrderInfo',
            'getRegionProvince' => function ($query) {
                $query->select('region_id', 'region_name');
            },
            'getRegionCity' => function ($query) {
                $query->select('region_id', 'region_name');
            },
            'getRegionDistrict' => function ($query) {
                $query->select('region_id', 'region_name');
            },
            'getRegionStreet' => function ($query) {
                $query->select('region_id', 'region_name');
            }
        ]);

        $back = BaseRepository::getToArrayFirst($res);

        if ($back) {

            /* 取得区域名 */
            $province = $back['get_region_province']['region_name'] ?? '';
            $city = $back['get_region_city']['region_name'] ?? '';
            $district = $back['get_region_district']['region_name'] ?? '';
            $street = $back['get_region_street']['region_name'] ?? '';
            $back['region'] = $province . ' ' . $city . ' ' . $district . ' ' . $street;

            /* 格式化金额字段 */
            $back['formated_insure_fee'] = $this->dscRepository->getPriceFormat($back['insure_fee'], false);
            $back['formated_shipping_fee'] = $this->dscRepository->getPriceFormat($back['shipping_fee'], false);

            $order = $back['get_order_info'] ?? [];

            $back['is_zc_order'] = $order['is_zc_order'] ?? 0;

            /* 格式化时间字段 */
            $back['formated_add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $back['add_time']);
            $back['formated_update_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $back['update_time']);
            $back['formated_return_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $back['return_time']);

            $return_order = $back;
        }

        return $return_order;
    }

    /**
     * 超级礼包发货数处理
     * @param array   超级礼包商品列表
     * @param int     发货数量
     * @param int     订单ID
     * @param varchar 虚拟代码
     * @param int     礼包ID
     * @return  array   格式化结果
     */
    public function packageGoods(&$package_goods, $goods_number, $order_id, $extension_code, $package_id)
    {
        $return_array = [];

        if (count($package_goods) == 0 || !is_numeric($goods_number)) {
            return $return_array;
        }

        foreach ($package_goods as $key => $value) {
            $return_array[$key] = $value;
            $return_array[$key]['order_send_number'] = $value['order_goods_number'] * $goods_number;
            $return_array[$key]['sended'] = $this->packageSended($package_id, $value['goods_id'], $order_id, $extension_code, $value['product_id']);
            $return_array[$key]['send'] = ($value['order_goods_number'] * $goods_number) - $return_array[$key]['sended'];
            $return_array[$key]['storage'] = $value['goods_number'];

            if ($return_array[$key]['send'] <= 0) {
                $return_array[$key]['send'] = lang('admin/order.act_good_delivery');
                $return_array[$key]['readonly'] = 'readonly="readonly"';
            }

            /* 是否缺货 */
            if ($return_array[$key]['storage'] <= 0 && config('shop.use_storage') == 1) {
                $return_array[$key]['send'] = lang('admin/order.act_good_vacancy');
                $return_array[$key]['readonly'] = 'readonly="readonly"';
            }
        }
        return $return_array;
    }

    /**
     * 获取超级礼包商品已发货数
     *
     * @param int $package_id 礼包ID
     * @param int $goods_id 礼包的产品ID
     * @param int $order_id 订单ID
     * @param varchar $extension_code 虚拟代码
     * @param int $product_id 货品id
     *
     * @return  int     数值
     */
    public function packageSended($package_id, $goods_id, $order_id, $extension_code, $product_id = 0)
    {
        if (empty($package_id) || empty($goods_id) || empty($order_id) || empty($extension_code)) {
            return false;
        }

        $res = DeliveryGoods::where('parent_id', $package_id)
            ->where('goods_id', $goods_id)
            ->where('extension_code', $extension_code);
        if ($product_id > 0) {
            $res = $res->where('product_id', $product_id);
        }

        $res = $res->whereHasIn('getDeliveryOrder', function ($query) use ($order_id) {
            $query->whereIn('status', [0, 2])->where('order_id', $order_id);
        });

        $send = $res->sum('send_number');
        return empty($send) ? 0 : $send;
    }

    /**
     * 改变订单中商品库存
     * @param int $order_id 订单 id
     * @param array $_sended Array(‘商品id’ => ‘此单发货数量’)
     * @param array $goods_list
     * @return  Bool
     */
    public function changeOrderGoodsStorageSplit($order_id, $_sended, $goods_list = [])
    {
        /* 参数检查 */
        if (!is_array($_sended) || empty($order_id)) {
            return false;
        }

        foreach ($_sended as $key => $value) {
            // 商品（超值礼包）
            if (is_array($value)) {
                if (!is_array($goods_list)) {
                    $goods_list = [];
                }
                foreach ($goods_list as $goods) {
                    if (($key != $goods['rec_id']) || (!isset($goods['package_goods_list']) || !is_array($goods['package_goods_list']))) {
                        continue;
                    }

                    // 超值礼包无库存，只减超值礼包商品库存
                    foreach ($goods['package_goods_list'] as $package_goods) {
                        if (!isset($value[$package_goods['goods_id']])) {
                            continue;
                        }

                        // 减库存：商品（超值礼包）（实货）、商品（超值礼包）（虚货）
                        Goods::where('goods_id', $package_goods['goods_id'])->decrement('goods_number', $value[$package_goods['goods_id']]);
                    }
                }
            } // 商品（实货）
            elseif (!is_array($value)) {
                /* 检查是否为商品（实货） */
                foreach ($goods_list as $goods) {
                    if ($goods['rec_id'] == $key && $goods['is_real'] == 1) {
                        Goods::where('goods_id', $goods['goods_id'])->decrement('goods_number', $value);
                        break;
                    }
                }
            }
        }

        return true;
    }

    /**
     * 删除发货单时进行退货
     *
     * @access   public
     * @param array $delivery_order 发货单信息数组
     *
     * @return  bool
     */
    public function deliveryReturnGoods($delivery_order = [])
    {
        if (empty($delivery_order)) {
            return false;
        }

        /* 查询：取得发货单商品 */

        $goods_list = DeliveryGoods::where('delivery_id', $delivery_order['delivery_id'])->select('goods_id', 'send_number');
        $goods_list = BaseRepository::getToArrayGet($goods_list);

        if (empty($goods_list)) {
            return false;
        }

        /* 更新： */
        foreach ($goods_list as $val) {
            OrderGoods::where('order_id', $delivery_order['order_id'])
                ->where('goods_id', $val['goods_id'])
                ->where('send_number', '>=', $val['send_number'])
                ->decrement('send_number', $val['send_number']);
        }
        $data = [
            'shipping_status' => 0,
            'order_status' => 1
        ];
        return OrderInfo::where('order_id', $delivery_order['order_id'])->update($data);
    }

    /**
     * 删除发货单时删除其在订单中的发货单号
     *
     * @access   public
     * @param int $order_id 定单id
     * @param string $delivery_invoice_no 发货单号
     *
     * @return bool
     */
    public function delOrderInvoiceNo($order_id = 0, $delivery_invoice_no = '')
    {
        if (empty($order_id)) {
            return false;
        }

        /* 查询：取得订单中的发货单号 */
        $order_invoice_no = OrderInfo::where('order_id', $order_id)->value('invoice_no');
        $order_invoice_no = $order_invoice_no ? $order_invoice_no : '';

        /* 如果为空就结束处理 */
        if (empty($order_invoice_no)) {
            return false;
        }

        /* 去除当前发货单号 */
        $order_array = explode(',', $order_invoice_no);
        $delivery_array = explode(',', $delivery_invoice_no);

        foreach ($order_array as $key => $invoice_no) {
            $ii = array_search($invoice_no, $delivery_array);
            if ($ii !== false) {
                unset($order_array[$key], $delivery_array[$ii]);
            }
        }

        $arr['invoice_no'] = implode(',', $order_array);

        return OrderInfo::where('order_id', $order_id)->update($arr);
    }

    /**
     * 输出导入数据
     * @param $result
     * @return array
     */
    public function downloadOrderListContent($result)
    {
        $count = count($result);
        $order_list = [];
        for ($i = 0; $i < $count; $i++) {
            // 订单商品信息
            $goods_count = 0;
            if (!empty($result[$i]['goods_list'])) {
                foreach ($result[$i]['goods_list'] as $j => $g) {

                    $goods_info = !empty($g['goods_attr']) ? rtrim($g['goods_name'] ?? '') . "(" . rtrim($g['goods_attr']) . ")" : rtrim($g['goods_name'] ?? ''); // 去除最末位换行符

                    if (isset($result[$i]['order_return_count']) && $result[$i]['order_return_count'] > 0) {
                        $goods_info .= lang('admin/order.download_order_refound_status');
                    }

                    $row['goods_info'] = "\"$goods_info\""; // 转义字符是关键 不然不是表格内换行
                    $goods_sn = rtrim($g['goods_sn'] ?? ''); // 去除最末位换行符
                    $goods_sn = "\"$goods_sn\""; // 转义字符是关键 不然不是表格内换行

                    $row['postscript'] = $result[$i]['postscript'];
                    if (!empty($row['postscript'])) {
                        $postscript = explode('|', $row['postscript']);
                        $row['postscript'] = $postscript[1] ?? '';
                    }
                    $row['order_sn'] = $result[$i]['order_sn']; //订单号前加'#',避免被四舍五入 by wu
                    $row['order_user'] = $result[$i]['buyer'];
                    $row['order_time'] = $result[$i]['short_order_time'];
                    $row['consignee'] = $result[$i]['consignee'];
                    $row['tel'] = !empty($result[$i]['mobile']) ? $result[$i]['mobile'] : $result[$i]['tel'];
                    $row['address'] = addslashes(str_replace(",", "，", "[" . $result[$i]['region'] . "] " . $result[$i]['address']));
                    $row['goods_info'] = $goods_info; // 商品信息
                    $row['goods_number'] = $g['goods_number'] ?? 1;
                    $row['goods_price'] = $g['goods_price'] ?? 0;
                    $row['cost_price'] = $g['cost_price'] ?? 0;
                    if (file_exists(MOBILE_DRP)) {
                        $row['drp_money'] = $g['drp_money'] ?? 0;
                    }
                    $row['goods_sn'] = $goods_sn; // 商品货号
                    $row['order_amount'] = $result[$i]['order_amount'];
                    $row['goods_amount'] = $result[$i]['goods_amount'];
                    $row['shipping_fee'] = $result[$i]['old_shipping_fee'];//配送费用
                    $row['insure_fee'] = $result[$i]['insure_fee'];//保价费用
                    $row['pay_fee'] = $result[$i]['pay_fee'];//支付费用
                    if (CROSS_BORDER === true) { // 跨境多商户
                        $row['rate_fee'] = $result[$i]['rate_fee'];//综合税费
                        $row['rel_name'] = $result[$i]['rel_name'];//真实姓名
                        $row['id_num'] = $result[$i]['id_num'];//身份证号
                    }
                    $row['surplus'] = $result[$i]['surplus'];//余额费用
                    $row['money_paid'] = $result[$i]['money_paid'];//已付款金额
                    $row['integral_money'] = $result[$i]['integral_money'];//积分金额
                    $row['bonus'] = $result[$i]['bonus'];//红包金额
                    $row['tax'] = $result[$i]['tax'];//发票税额
                    $row['discount'] = $result[$i]['discount'];//折扣金额
                    $row['coupons'] = $result[$i]['coupons'];//优惠券金额
                    $row['value_card'] = $result[$i]['value_card']; // 储值卡
                    $row['order_status'] = lang('admin/order.os.' . $result[$i]['order_status']);
                    $row['seller_name'] = isset($result[$i]['user_name']) ? $result[$i]['user_name'] : ''; //商家名称
                    $row['pay_status'] = lang('admin/order.ps.' . $result[$i]['pay_status']);
                    $row['shipping_status'] = lang('admin/order.ss.' . $result[$i]['shipping_status']);
                    $froms = $result[$i]['referer'];

                    if ($froms == 'touch') {
                        $row['froms'] = "WAP";
                    } elseif ($froms == 'mobile' || $froms == 'app') {
                        $row['froms'] = "APP";
                    } elseif ($froms == 'H5') {
                        $row['froms'] = "H5";
                    } elseif ($froms == 'wxapp') {
                        $row['froms'] = lang('admin/order.wxapp');
                    } elseif ($froms == 'ecjia-cashdesk') {
                        $row['froms'] = lang('admin/order.cashdesk');
                    } else {
                        $row['froms'] = "PC";
                    }

                    $row['pay_name'] = $result[$i]['pay_name'];
                    $row['total_fee'] = $result[$i]['total_fee']; // 总金额
                    $row['total_fee_order'] = $result[$i]['total_fee_order']; // 订单总金额
                    $goods_count += 1;
                    $row['goods_count'] = $goods_count;

                    $order_list[] = $row;
                }
            }
        }

        return $order_list;
    }

    //ecmoban模板堂 --zhuo end
    public function getCauseCatLevel($parent_id = 0)
    {
        $res = [];
        $level = isset($level) ? $level : 0;

        $res = ReturnCause::where('parent_id', $parent_id)
            ->withCount(['getReturnCauseChild as has_children'])
            ->groupBy('cause_id')
            ->orderBy('parent_id')
            ->orderBy('sort_order');
        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $k => $row) {
                $res[$k]['level'] = $level;
            }
        }

        return $res;
    }

    /**
     * 取得收货人地址列表
     * @param int $user_id 用户编号
     * @return  array
     */
    public function getConsigneeLog($address_id = 0, $user_id = 0)
    {
        $res = UserAddress::where('user_id', $user_id)->where('address_id', $address_id);
        $user_address = BaseRepository::getToArrayFirst($res);

        return $user_address;
    }

    /**
     * 获得指定国家的所有省份
     *
     * @access      public
     * @param int     country    国家的编号
     * @return      array
     */
    public function getRegionsLog($type = 0, $parent = 0)
    {
        $res = Region::where('region_type', $type)->where('parent_id', $parent);
        $region_list = BaseRepository::getToArrayGet($res);

        return $region_list;
    }

    /**
     * 后台批量添加订单
     * 状态分组
     *
     * @return array
     */
    public function BatchAddOrderStatus()
    {
        $_LANG = trans('admin::order');

        $arr = [
            [
                //已确认，未付款，未发货
                'status' => [
                    'order_status' => OS_CONFIRMED,
                    'pay_status' => PS_UNPAYED,
                    'shipping_status' => SS_UNSHIPPED,
                ],
                'lang' => $_LANG['os'][OS_CONFIRMED] . ',' . $_LANG['ps'][PS_UNPAYED] . ',' . $_LANG['ss'][SS_UNSHIPPED]
            ],
            [
                //已分单，未付款，已发货
                'status' => [
                    'order_status' => OS_SPLITED,
                    'pay_status' => PS_UNPAYED,
                    'shipping_status' => SS_SHIPPED,
                ],
                'lang' => $_LANG['os'][OS_SPLITED] . ',' . $_LANG['ps'][PS_UNPAYED] . ',' . $_LANG['ss'][SS_SHIPPED]
            ],
            [
                //已确认，已付款，收货确认
                'status' => [
                    'order_status' => OS_CONFIRMED,
                    'pay_status' => PS_PAYED,
                    'shipping_status' => SS_RECEIVED,
                ],
                'lang' => $_LANG['os'][OS_CONFIRMED] . ',' . $_LANG['ps'][PS_PAYED] . ',' . $_LANG['ss'][SS_RECEIVED]
            ],
            [
                //已确认，已付款，未发货
                'status' => [
                    'order_status' => OS_CONFIRMED,
                    'pay_status' => PS_PAYED,
                    'shipping_status' => SS_UNSHIPPED,
                ],
                'lang' => $_LANG['os'][OS_CONFIRMED] . ',' . $_LANG['ps'][PS_PAYED] . ',' . $_LANG['ss'][SS_UNSHIPPED]
            ],
            [
                //已分单，已付款，已发货
                'status' => [
                    'order_status' => OS_SPLITED,
                    'pay_status' => PS_PAYED,
                    'shipping_status' => SS_SHIPPED,
                ],
                'lang' => $_LANG['os'][OS_SPLITED] . ',' . $_LANG['ps'][PS_PAYED] . ',' . $_LANG['ss'][SS_SHIPPED]
            ],
            [
                //已分单，已付款，收货确认
                'status' => [
                    'order_status' => OS_SPLITED,
                    'pay_status' => PS_PAYED,
                    'shipping_status' => SS_RECEIVED,
                ],
                'lang' => $_LANG['os'][OS_SPLITED] . ',' . $_LANG['ps'][PS_PAYED] . ',' . $_LANG['ss'][SS_RECEIVED]
            ],
            [
                //取消，未付款，未发货
                'status' => [
                    'order_status' => OS_CANCELED,
                    'pay_status' => PS_UNPAYED,
                    'shipping_status' => SS_UNSHIPPED,
                ],
                'lang' => $_LANG['os'][OS_CANCELED] . ',' . $_LANG['ps'][PS_UNPAYED] . ',' . $_LANG['ss'][SS_UNSHIPPED]
            ],
            [
                //无效，未付款，未发货
                'status' => [
                    'order_status' => OS_INVALID,
                    'pay_status' => PS_UNPAYED,
                    'shipping_status' => SS_UNSHIPPED,
                ],
                'lang' => $_LANG['os'][OS_INVALID] . ',' . $_LANG['ps'][PS_UNPAYED] . ',' . $_LANG['ss'][SS_UNSHIPPED]
            ],
            [
                //退货，已退款，已发货
                'status' => [
                    'order_status' => OS_RETURNED,
                    'pay_status' => PS_REFOUND,
                    'shipping_status' => SS_SHIPPED,
                ],
                'lang' => $_LANG['os'][OS_RETURNED] . ',' . $_LANG['ps'][PS_REFOUND] . ',' . $_LANG['ss'][SS_SHIPPED]
            ],
            [
                //退货，已退款，未发货
                'status' => [
                    'order_status' => OS_RETURNED,
                    'pay_status' => PS_REFOUND,
                    'shipping_status' => SS_UNSHIPPED,
                ],
                'lang' => $_LANG['os'][OS_RETURNED] . ',' . $_LANG['ps'][PS_REFOUND] . ',' . $_LANG['ss'][SS_UNSHIPPED]
            ]
        ];

        return $arr;
    }

    /**
     * 获取订单拼团信息
     *
     * @param int $team_log_id
     * @return array
     */
    public function teamOrderUserNum($team_log_id = 0)
    {
        $team = TeamLog::where('team_id', $team_log_id)->with([
            'getTeamGoods'
        ]);

        $team = BaseRepository::getToArrayFirst($team);

        $team_user_num = $team['get_team_goods']['team_num'] ?? 0;

        $team_user = OrderInfo::query()->select('order_id', 'user_id')->where('team_id', $team_log_id)
            ->whereNotIn('order_status', [OS_CANCELED, OS_INVALID, OS_RETURNED, OS_RETURNED_PART, OS_ONLY_REFOUND])
            ->where('pay_status', PS_PAYED)
            ->whereHasIn('getTeamLog')
            ->pluck('user_id');
        $team_user = $team_user ? $team_user->toArray() : [];
        $team_user = $team_user ? array_unique($team_user) : [];
        $team_order_user_num = count($team_user);

        $is_team = 0;
        if ($team_order_user_num > 0 && $team_order_user_num >= $team_user_num) {
            $is_team = 1;
        }

        $arr = [
            'is_team' => $is_team, //是否拼团成功（0：否， 1：是）
            'team_order_user_num' => $team_order_user_num //已拼人数
        ];

        if ($is_team == 0) {
            //差团人数
            $arr['team_false_user_num'] = $team_user_num - $team_order_user_num;
        }

        return $arr;
    }

    /**
     * 订单设置未付款
     * 退还储值卡金额
     *
     * @param int $order_id 订单ID
     * @param int $vc_id 储值卡ID
     * @param int $refound_vcard 储值卡金额
     * @param int $user_id 会员ID
     * @param string $order_sn 订单编号
     * @throws \Exception
     */
    public function unpayOrderReturnVcard($order_id = 0, $vc_id = 0, $refound_vcard = 0, $user_id = 0, $order_sn = '')
    {
        $refound_vcard = $this->dscRepository->changeFloat($refound_vcard);

        if ($vc_id && $refound_vcard > 0) {
            $refound_vcard = empty($refound_vcard) ? 0 : $refound_vcard;

            /* 更新储值卡金额 */
            ValueCard::where('vid', $vc_id)->where('user_id', $user_id)->increment('card_money', $refound_vcard);

            //所有此订单的order_id清空
            ValueCardRecord::where('order_id', $order_id)->update([
                'order_id' => 0
            ]);

            /* 更新订单使用储值卡金额 */
            $log = [
                'vc_id' => $vc_id,
                'order_id' => 0,
                'use_val' => $refound_vcard,
                'vc_dis' => 1,
                'add_val' => $refound_vcard,
                'record_time' => TimeRepository::getGmTime(),
                'change_desc' => sprintf(lang('admin/order.return_card_record'), $order_sn)
            ];

            $id = ValueCardRecord::insertGetId($log);

            if ($id > 0 && $order_id > 0) {

                $admin_id = get_admin_id();

                $username = AdminUser::where('user_id', $admin_id)->value('user_name');
                $username = $username ? $username : '';

                $order_info = OrderInfo::select('order_id', 'order_sn', 'order_status', 'shipping_status', 'pay_status')->where('order_id', $order_id);
                $order_info = BaseRepository::getToArrayFirst($order_info);

                $time = TimeRepository::getGmTime();
                $note = sprintf("【" . $order_sn . "】" . $GLOBALS['_LANG']['order_vcard_return'], $refound_vcard);

                $other = [
                    'order_id' => $order_id,
                    'action_user' => $username,
                    'order_status' => $order_info['order_status'],
                    'shipping_status' => $order_info['shipping_status'],
                    'pay_status' => $order_info['pay_status'],
                    'action_note' => $note,
                    'log_time' => $time
                ];
                OrderAction::insert($other);
            }
        }
    }

    /**
     * 操作后台子订单修改价格，更新主订单价格信息
     *
     * @param array $old_order
     * @param array $order
     * @param string $name
     * @return bool
     * @throws \Exception
     */
    public function updateOrderMoneyLog($old_order = [], $order = [], $name = '')
    {
        if ($old_order['pay_status'] >= PS_PAYED) {
            return false;
        }

        $note = '';
        $dbRaw = [];

        /* 发票税额 start */
        $old_tax = floatval($old_order['tax']);
        $tax = floatval($order['tax']);

        if ($old_tax != $tax) {
            if ($old_tax > $tax) {
                $update_tax = $old_tax - $tax;
                $update_tax = '-' . $update_tax;
            } else {
                $update_tax = $tax - $old_tax;
                $update_tax = '+' . $update_tax;
            }

            $dbRaw['tax'] = "tax" . $update_tax;

            $note .= '，' . lang('common.tax') . '：' . $update_tax;
        }
        /* 发票税额 end */

        /* 配送费用 start */
        $old_shipping_fee = floatval($old_order['shipping_fee']);
        $shipping_fee = floatval($order['shipping_fee']);

        if ($old_shipping_fee != $shipping_fee) {
            if ($old_shipping_fee > $shipping_fee) {
                $update_shipping_fee = $old_shipping_fee - $shipping_fee;
                $update_shipping_fee = '-' . $update_shipping_fee;
            } else {
                $update_shipping_fee = $shipping_fee - $old_shipping_fee;
                $update_shipping_fee = '+' . $update_shipping_fee;
            }

            $dbRaw['shipping_fee'] = "shipping_fee" . $update_shipping_fee;

            $note .= '，' . lang('common.shipping_fee') . '：' . $update_shipping_fee;
        }
        /* 配送费用 end */

        /* 支付手续费 start */
        $old_pay_fee = floatval($old_order['pay_fee']);
        $pay_fee = floatval($order['pay_fee']);

        if ($old_pay_fee != $pay_fee) {
            if ($old_pay_fee > $pay_fee) {
                $update_pay_fee = $old_pay_fee - $pay_fee;
                $update_pay_fee = '-' . $update_pay_fee;
            } else {
                $update_pay_fee = $pay_fee - $old_pay_fee;
                $update_pay_fee = '+' . $update_pay_fee;
            }

            $dbRaw['pay_fee'] = "pay_fee" . $update_pay_fee;

            $note .= '，' . lang('common.pay_fee') . '：' . $update_pay_fee;
        }
        /* 支付手续费 end */

        /* 折扣 start */
        $old_discount = floatval($old_order['discount']);
        $discount = floatval($order['discount']);

        if ($old_discount != $discount) {
            if ($old_discount > $discount) {
                $update_discount = $old_discount - $discount;
                $update_discount = '-' . $update_discount;
            } else {
                $update_discount = $discount - $old_discount;
                $update_discount = '+' . $update_discount;
            }

            $dbRaw['discount'] = "discount" . $update_discount;

            $note .= '，' . lang('common.discount') . '：' . $update_discount;
        }
        /* 折扣 end */

        /* 积分 start */
        $old_integral = floatval($old_order['integral']);
        $integral = floatval($order['integral']);
        $old_integral_money = floatval($old_order['integral_money']);
        $integral_money = floatval($order['integral_money']);

        if ($old_integral != $integral) {
            if ($old_integral > $integral) {
                $update_integral = $old_integral - $integral;
                $update_integral = '-' . $update_integral;

                $update_integral_money = $old_integral_money - $integral_money;
                $update_integral_money = '-' . $update_integral_money;
            } else {
                $update_integral = $integral - $old_integral;
                $update_integral = '+' . $update_integral;

                $update_integral_money = $integral_money - $old_integral_money;
                $update_integral_money = '+' . $update_integral_money;
            }

            $dbRaw['integral'] = "integral" . $update_integral;
            $dbRaw['integral_money'] = "integral_money" . $update_integral_money;

            $note .= '，' . lang('common.integral') . '：' . $update_integral;
        }
        /* 积分 end */

        /* 优惠券 start */
        $old_coupons = floatval($old_order['coupons']);
        $coupons = floatval($order['coupons']);

        if ($old_coupons != $coupons) {
            if ($old_coupons > $coupons) {
                $update_coupons = $old_coupons - $coupons;
                $update_coupons = '-' . $update_coupons;
            } else {
                $update_coupons = $coupons - $old_coupons;
                $update_coupons = '+' . $update_coupons;
            }

            $dbRaw['coupons'] = "coupons" . $update_coupons;

            $note .= '，' . lang('common.coupons') . '：' . $update_coupons;
        }
        /* 优惠券 end */

        /* 红包 start */
        $old_bonus = floatval($old_order['bonus']);
        $bonus = floatval($order['bonus']);

        if ($old_bonus != $bonus) {
            if ($old_bonus > $bonus) {
                $update_bonus = $old_bonus - $bonus;
                $update_bonus = '-' . $update_bonus;
            } else {
                $update_bonus = $bonus - $old_bonus;
                $update_bonus = '+' . $update_bonus;
            }

            $dbRaw['bonus'] = "bonus" . $update_bonus;

            $note .= '，' . lang('common.bonus') . '：' . $update_bonus;
        }
        /* 红包 end */

        /* 保价费用 start */
        $old_insure_fee = floatval($old_order['insure_fee']);
        $insure_fee = floatval($order['insure_fee']);

        if ($old_insure_fee != $insure_fee) {
            if ($old_insure_fee > $insure_fee) {
                $update_insure_fee = $old_insure_fee - $insure_fee;
                $update_insure_fee = '-' . $update_insure_fee;
            } else {
                $update_insure_fee = $insure_fee - $old_insure_fee;
                $update_insure_fee = '+' . $update_insure_fee;
            }

            $dbRaw['insure_fee'] = "insure_fee" . $update_insure_fee;

            $note .= '，' . lang('common.insure_fee') . '：' . $update_insure_fee;
        }
        /* 保价费用 end */

        /* 使用余额 start */
        $old_surplus = floatval($old_order['surplus']);
        $surplus = floatval($order['surplus']);

        if ($old_surplus != $surplus) {
            if ($old_surplus > $surplus) {
                $update_surplus = $old_surplus - $surplus;
                $update_surplus = '-' . $update_surplus;
            } else {
                $update_surplus = $surplus - $old_surplus;
                $update_surplus = '+' . $update_surplus;
            }

            $dbRaw['surplus'] = "surplus" . $update_surplus;

            $note .= '，' . lang('common.use_surplus') . '：' . $update_surplus;
        }
        /* 使用余额 end */

        /* 订单金额 start */
        $old_order_amount = floatval($old_order['order_amount']);
        $order_amount = floatval($order['order_amount']);

        if ($old_order_amount != $order_amount) {
            if ($old_order_amount > $order_amount) {
                $update_order_amount = $old_order_amount - $order_amount;
                $update_order_amount = '-' . $update_order_amount;
            } else {
                $update_order_amount = $order_amount - $old_order_amount;
                $update_order_amount = '+' . $update_order_amount;
            }

            $dbRaw['order_amount'] = "order_amount" . $update_order_amount;

            $note .= '，' . lang('common.pay_total_fee') . '：' . $update_order_amount;
        }
        /* 订单金额 end */

        if ($old_order['main_order_id'] > 0) {

            $main_old_order_amount = OrderInfo::where('order_id', $old_order['main_order_id'])->value('order_amount');

            $main_order_sn = OrderInfo::where('order_id', $old_order['main_order_id'])->value('order_sn');
            $main_order_sn = $main_order_sn ? $main_order_sn : '';

            if (empty($main_order_sn)) {
                return false;
            }

            if ($dbRaw) {
                $dbRaw = BaseRepository::getDbRaw($dbRaw);
                $updateRes = OrderInfo::where('order_id', $old_order['main_order_id'])->update($dbRaw);

                $note = !empty($note) ? ltrim($note, '，') : '';

                if ($updateRes) {

                    $child_note = $note . '，' . sprintf(lang('common.total_fee_detail'), $old_order['order_amount'], $order['order_amount']);

                    $action_note = sprintf(lang('common.update_order_pay'), $child_note);
                    $order_status = isset($order['order_status']) ? $order['order_status'] : $old_order['order_status'];
                    $shipping_status = isset($order['shipping_status']) ? $order['shipping_status'] : $old_order['shipping_status'];
                    $pay_status = isset($order['pay_status']) ? $order['pay_status'] : $old_order['pay_status'];

                    /* 更新记录 */
                    $this->orderCommonService->orderAction($old_order['order_sn'], $order_status, $shipping_status, $pay_status, $action_note, $name, 0, TimeRepository::getGmTime());

                    /* 更新主订单记录 */
                    $main_order_amount = OrderInfo::where('order_id', $old_order['main_order_id'])->value('order_amount');

                    $main_note = $note . '，' . sprintf(lang('common.total_fee_detail'), $main_old_order_amount, $main_order_amount);
                    $main_action_note = sprintf(lang('common.update_main_order_pay'), $old_order['order_sn'], $main_note);
                    $this->orderCommonService->orderAction($main_order_sn, $order_status, $shipping_status, $pay_status, $main_action_note, $name, 0, TimeRepository::getGmTime());

                    $this->orderCommonService->updateOrderPayLog($old_order['main_order_id']);
                }
            }
        } else {
            $note = !empty($note) ? ltrim($note, '，') : '';
            $child_note = $note . '，' . sprintf(lang('common.total_fee_detail'), $old_order['order_amount'], $order['order_amount']);

            $action_note = sprintf(lang('common.update_order_pay'), $child_note);
            $order_status = isset($order['order_status']) ? $order['order_status'] : $old_order['order_status'];
            $shipping_status = isset($order['shipping_status']) ? $order['shipping_status'] : $old_order['shipping_status'];
            $pay_status = isset($order['pay_status']) ? $order['pay_status'] : $old_order['pay_status'];

            /* 更新记录 */
            $this->orderCommonService->orderAction($old_order['order_sn'], $order_status, $shipping_status, $pay_status, $action_note, $name, 0, TimeRepository::getGmTime());
        }

        return true;
    }

    /**
     * 一键发货
     *
     * @param array $order_arr
     * @param string $action_note
     * @param string $type
     * @param int $admin_id
     * @param string $action_user
     * @return array
     * @throws \Exception
     */
    public function oneClickShipping($order_arr = [], $action_note = '', $type = 'one', $admin_id = 0, $action_user = '')
    {
        if (empty($order_arr)) {
            return ['error_code' => 1, 'msg' => lang('admin/order.act_false')];
        }
        foreach ($order_arr as $row) {
            $order_id = $row['order_id'];
            $invoice_no = trim($row['invoice_no']);
            $express_code = $row['express_code'] ?? '';
            $express_info = $this->expressManageService->getExpressCompanyByCode($express_code);

            $delivery_info = get_delivery_info($order_id);
            if (!empty($invoice_no) && !$delivery_info) {
                $order = order_info($order_id);
                /* 查询：根据订单是否完成 检查权限 */
                if (order_finished($order)) {
                    admin_priv('order_view_finished');
                } else {
                    admin_priv('order_view');
                }

                /* 查询：如果管理员属于某个办事处，检查该订单是否也属于这个办事处 */
                $agency_id = AdminUser::where('user_id', $admin_id)->value('agency_id');
                $agency_id = $agency_id ? $agency_id : 0;

                if ($agency_id > 0) {
                    if (!empty($order['agency_id']) && $order['agency_id'] != $agency_id) {
                        if ($type == 'one') {
                            return ['error_code' => 0, 'msg' => lang('admin/common.priv_error')];
                        }
                        continue;
                    }
                }

                /* 查询：取得用户名 */
                if ($order['user_id'] > 0) {
                    $user_name = Users::where('user_id', $order['user_id'])->value('user_name');
                    $order['user_name'] = $user_name ?? '';
                }
                /* 查询：取得区域名 */

                $order['region'] = $agency_id;

                /* 查询：其他处理 */
                $order['invoice_no'] = $order['shipping_status'] == SS_UNSHIPPED || $order['shipping_status'] == SS_PREPARING ? $GLOBALS['_LANG']['ss'][SS_UNSHIPPED] : $order['invoice_no'];

                /* 查询：是否保价 */
                $order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;

                /* 查询：取得订单商品 */
                $_goods = $this->getOrderGoods(['order_id' => $order['order_id'], 'order_sn' => $order['order_sn']]);
                $goods_list = $_goods['goods_list'];
                unset($_goods);

                /* 查询：商品已发货数量 此单可发货数量 */
                if ($goods_list) {
                    foreach ($goods_list as $key => $goods_value) {
                        if (!$goods_value['goods_id']) {
                            continue;
                        }

                        /* 超级礼包 */
                        if (($goods_value['extension_code'] == 'package_buy') && (count($goods_value['package_goods_list']) > 0)) {
                            $goods_list[$key]['package_goods_list'] = $this->packageGoods($goods_value['package_goods_list'], $goods_value['goods_number'], $goods_value['order_id'], $goods_value['extension_code'], $goods_value['goods_id']);
                            foreach ($goods_list[$key]['package_goods_list'] as $pg_key => $pg_value) {
                                $goods_list[$key]['package_goods_list'][$pg_key]['readonly'] = '';
                                /* 使用库存 是否缺货 */
                                if ($pg_value['storage'] <= 0 && $GLOBALS['_CFG']['use_storage'] == '1' && $GLOBALS['_CFG']['stock_dec_time'] == SDT_SHIP) {
                                    $goods_list[$key]['package_goods_list'][$pg_key]['send'] = lang('admin/order.act_good_vacancy');
                                    $goods_list[$key]['package_goods_list'][$pg_key]['readonly'] = 'readonly="readonly"';
                                } /* 将已经全部发货的商品设置为只读 */
                                elseif ($pg_value['send'] <= 0) {
                                    $goods_list[$key]['package_goods_list'][$pg_key]['send'] = lang('admin/order.act_good_delivery');
                                    $goods_list[$key]['package_goods_list'][$pg_key]['readonly'] = 'readonly="readonly"';
                                }
                            }
                        } else {
                            $goods_list[$key]['sended'] = $goods_value['send_number'];
                            $goods_list[$key]['sended'] = $goods_value['goods_number'];
                            $goods_list[$key]['send'] = $goods_value['goods_number'] - $goods_value['send_number'];
                            $goods_list[$key]['readonly'] = '';
                            /* 是否缺货 */
                            if ($goods_value['storage'] <= 0 && $GLOBALS['_CFG']['use_storage'] == '1' && $GLOBALS['_CFG']['stock_dec_time'] == SDT_SHIP) {
                                $goods_list[$key]['send'] = lang('admin/order.act_good_vacancy');
                                $goods_list[$key]['readonly'] = 'readonly="readonly"';
                            } elseif ($goods_list[$key]['send'] <= 0) {
                                $goods_list[$key]['send'] = lang('admin/order.act_good_delivery');
                                $goods_list[$key]['readonly'] = 'readonly="readonly"';
                            }
                        }
                    }
                }

                $suppliers_id = 0;

                $delivery['order_sn'] = trim($order['order_sn']);
                $delivery['user_id'] = intval(trim($order['user_id']));
                $delivery['how_oos'] = trim($order['how_oos']);
                $delivery['shipping_id'] = trim($order['shipping_id']);
                $delivery['shipping_fee'] = trim($order['shipping_fee']);
                $delivery['consignee'] = trim($order['consignee']);
                $delivery['address'] = trim($order['address']);
                $delivery['country'] = intval(trim($order['country']));
                $delivery['province'] = intval(trim($order['province']));
                $delivery['city'] = intval(trim($order['city']));
                $delivery['district'] = intval(trim($order['district']));
                $delivery['sign_building'] = trim($order['sign_building']);
                $delivery['email'] = trim($order['email']);
                $delivery['zipcode'] = trim($order['zipcode']);
                $delivery['tel'] = trim($order['tel']);
                $delivery['mobile'] = trim($order['mobile']);
                $delivery['best_time'] = trim($order['best_time']);
                $delivery['postscript'] = trim($order['postscript']);
                $delivery['how_oos'] = trim($order['how_oos']);
                $delivery['insure_fee'] = floatval(trim($order['insure_fee']));
                $delivery['shipping_fee'] = floatval(trim($order['shipping_fee']));
                $delivery['agency_id'] = intval(trim($order['agency_id']));
                $delivery['shipping_name'] = isset($express_info['name']) ? $express_info['name'] : trim($order['shipping_name']);
                $delivery['express_code'] = $express_code;

                /* 检查能否操作 */
                $this->operableList($order);

                /* 初始化提示信息 */
                $msg = '';


                /* 检查此单发货商品库存缺货情况 */
                /* $goods_list已经过处理 超值礼包中商品库存已取得 */
                /* 生成发货单 */
                /* 获取发货单号和流水号 */
                $delivery['delivery_sn'] = get_delivery_sn();

                /* 获取当前操作员 */
                $delivery['action_user'] = $action_user;

                /* 获取发货单生成时间 */
                $delivery['update_time'] = TimeRepository::getGmTime();
                $delivery['add_time'] = $order['add_time'] ?? TimeRepository::getGmTime();

                /* 获取发货单所属供应商 */
                $delivery['suppliers_id'] = $suppliers_id;

                /* 设置默认值 */
                $delivery['status'] = DELIVERY_CREATE; // 正常
                $delivery['order_id'] = $order_id;

                /* 过滤字段项 */
                $filter_fileds = [
                    'order_sn', 'add_time', 'user_id', 'how_oos', 'shipping_id', 'shipping_fee',
                    'consignee', 'address', 'country', 'province', 'city', 'district', 'sign_building',
                    'email', 'zipcode', 'tel', 'mobile', 'best_time', 'postscript', 'insure_fee',
                    'agency_id', 'delivery_sn', 'action_user', 'update_time',
                    'suppliers_id', 'status', 'order_id', 'shipping_name', 'express_code'
                ];
                /**/
                $_delivery = [];
                foreach ($filter_fileds as $value) {
                    $_delivery[$value] = $delivery[$value];
                }

                DB::beginTransaction();
                try {
                    /* 发货单入库 */
                    $delivery_id = DeliveryOrder::insertGetId($_delivery);

                    if ($delivery_id) {
                        //发货单商品入库
                        if (!empty($goods_list)) {
                            foreach ($goods_list as $value) {
                                // 商品（实货）
                                if (empty($value['extension_code']) || in_array($value['extension_code'], ['presale', 'bargain_buy', 'bargain']) || substr($value['extension_code'], 0, 7) == 'seckill') {
                                    $delivery_goods = [
                                        'delivery_id' => $delivery_id,
                                        'goods_id' => $value['goods_id'],
                                        'product_id' => $value['product_id'] ?? '',
                                        'product_sn' => $value['product_sn'] ?? '',
                                        'goods_name' => $value['goods_name'],
                                        'brand_name' => $value['brand_name'],
                                        'goods_sn' => $value['goods_sn'],
                                        'send_number' => $value['goods_number'],
                                        'parent_id' => 0,
                                        'is_real' => $value['is_real'],
                                        'goods_attr' => $value['goods_attr']
                                    ];

                                    DeliveryGoods::insert($delivery_goods);

                                    OrderGoods::where('order_id', $value['order_id'])->where('rec_id', $value['rec_id'])->update(['send_number' => $value['goods_number']]);
                                } // 商品（超值礼包）
                                elseif ($value['extension_code'] == 'package_buy') {
                                    foreach ($value['package_goods_list'] as $pg_key => $pg_value) {
                                        $delivery_pg_goods = [
                                            'delivery_id' => $delivery_id,
                                            'goods_id' => $pg_value['goods_id'],
                                            'product_id' => $pg_value['product_id'] ?? '',
                                            'product_sn' => $pg_value['product_sn'] ?? '',
                                            'goods_name' => $pg_value['goods_name'],
                                            'brand_name' => '',
                                            'goods_sn' => $pg_value['goods_sn'],
                                            'send_number' => $pg_value['order_goods_number'],
                                            'parent_id' => $value['goods_id'], // 礼包ID
                                            'extension_code' => $value['extension_code'], // 礼包
                                            'is_real' => $pg_value['is_real']
                                        ];
                                        DeliveryGoods::insert($delivery_pg_goods);

                                        OrderGoods::where('order_id', $value['order_id'])->where('rec_id', $value['rec_id'])->update(['send_number' => $value['goods_number']]);
                                    }
                                }
                            }
                        }
                    } else {
                        /* 操作失败 */
                        throw new \Exception(lang('admin/order.act_false'), 1);
                    }
                    unset($filter_fileds, $delivery, $_delivery, $order_finish);

                    /* 标记订单为已确认 “发货中” */
                    /* 更新发货时间 */
                    $order_finish = $this->getOrderFinish($order_id);
                    $shipping_status = SS_SHIPPED_ING;
                    if ($order['order_status'] != OS_CONFIRMED && $order['order_status'] != OS_SPLITED && $order['order_status'] != OS_SPLITING_PART) {
                        $arr['order_status'] = OS_CONFIRMED;
                        $arr['confirm_time'] = TimeRepository::getGmTime();

                    }
                    $arr['order_status'] = $order_finish ? OS_SPLITED : OS_SPLITING_PART; // 全部分单、部分分单
                    $arr['shipping_status'] = $shipping_status;
                    if (isset($express_info['name'])) {
                        $arr['shipping_name'] = $express_info['name'];
                    }

                    update_order($order_id, $arr);

                    $this->orderCommonService->updateMainOrder($order, 3, $action_note);

                    /* 记录log */
                    order_action($order['order_sn'], $arr['order_status'], $shipping_status, $order['pay_status'], $action_note, $action_user);

                    /* 根据发货单id查询发货单信息 */
                    if (!empty($delivery_id)) {
                        $delivery_order = $this->deliveryOrderInfo($delivery_id);
                    } elseif (!empty($order_sn)) {
                        $delivery_id = DeliveryOrder::where('order_sn', $order_sn)->value('delivery_id');
                        $delivery_id = $delivery_id ? $delivery_id : 0;

                        $delivery_order = $this->deliveryOrderInfo($delivery_id);
                    } else {
                        throw new \Exception('order does not exist', 1);
                    }

                    /* 如果管理员属于某个办事处，检查该订单是否也属于这个办事处 */
                    $agency_id = AdminUser::where('user_id', $admin_id)->value('agency_id');
                    $agency_id = $agency_id ? $agency_id : 0;
                    if ($agency_id > 0) {
                        if (!empty($delivery_order['agency_id']) && $delivery_order['agency_id'] != $agency_id) {
                            throw new \Exception(lang('admin/common.priv_error'), 1);
                        }

                        /* 取当前办事处信息 */
                        $agency_name = Agency::where('agency_id', $agency_id)->value('agency_name');
                        $delivery_order['agency_name'] = $agency_name ?? '';
                    }

                    /* 取得用户名 */
                    if ($delivery_order['user_id'] > 0) {
                        $user_name = Users::where('user_id', $delivery_order['user_id'])->value('user_name');
                        $delivery_order['user_name'] = $user_name ?? '';
                    }

                    /* 取得区域名 */
                    $res = OrderInfo::where('order_id', $delivery_order['order_id'])
                        ->with(['getRegionProvince', 'getRegionCity', 'getRegionDistrict', 'getRegionCountry',]);
                    $res = BaseRepository::getToArrayFirst($res);

                    $delivery_order['region'] = '';
                    if (!empty($res)) {
                        if (isset($res['get_region_country']) && !empty($res['get_region_country'])) {
                            $delivery_order['region'] .= $res['get_region_country']['region_name'] . '  ';
                        }
                        if (isset($res['get_region_province']) && !empty($res['get_region_province'])) {
                            $delivery_order['region'] .= $res['get_region_province']['region_name'] . '  ';
                        }
                        if (isset($res['get_region_city']) && !empty($res['get_region_city'])) {
                            $delivery_order['region'] .= $res['get_region_city']['region_name'] . '  ';
                        }
                        if (isset($res['get_region_district']) && !empty($res['get_region_district'])) {
                            $delivery_order['region'] .= $res['get_region_district']['region_name'];
                        }
                    }

                    /* 是否保价 */
                    $order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;

                    /* 取得发货单商品 */
                    $res = DeliveryGoods::where('delivery_id', $delivery_order['delivery_id']);
                    $goods_list = BaseRepository::getToArrayGet($res);

                    /* 是否存在实体商品 */
                    $exist_real_goods = 0;
                    if ($goods_list) {
                        foreach ($goods_list as $value) {
                            if ($value['is_real']) {
                                $exist_real_goods++;
                            }
                        }
                    }

                    /* 取得订单操作记录 */
                    $act_list = [];
                    $res = OrderAction::where('order_id', $delivery_order['order_id'])
                        ->where('action_place', 1)
                        ->orderBy('log_time', 'DESC')
                        ->orderBy('action_id', 'DESC');
                    $res = BaseRepository::getToArrayGet($res);

                    foreach ($res as $item) {
                        $row['order_status'] = $GLOBALS['_LANG']['os'][$row['order_status']];
                        $row['pay_status'] = $GLOBALS['_LANG']['ps'][$row['pay_status']];
                        $row['shipping_status'] = ($row['shipping_status'] == SS_SHIPPED_ING) ? $GLOBALS['_LANG']['ss_admin'][SS_SHIPPED_ING] : $GLOBALS['_LANG']['ss'][$row['shipping_status']];
                        $row['action_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $item['log_time']);
                        $act_list[] = $item;
                    }

                    /* 同步发货 */
                    $order = order_info($delivery_order['order_id']);  //根据订单ID查询订单信息，返回数组$order

                    /* 根据发货单id查询发货单信息 */
                    if (!empty($delivery_id)) {
                        $this->deliveryOrderInfo($delivery_id);
                    } else {
                        throw new \Exception('order does not exist', 1);
                    }

                    /* 检查此单发货商品库存缺货情况  ecmoban模板堂 --zhuo start 下单减库存*/
                    $delivery_stock_sql = "SELECT DG.rec_id AS dg_rec_id, OG.rec_id AS og_rec_id, G.model_attr, G.model_inventory, DG.goods_id, DG.delivery_id, DG.is_real, DG.send_number AS sums, G.goods_number AS storage, G.goods_name, DG.send_number," .
                        " OG.goods_attr_id, OG.warehouse_id, OG.area_id, OG.area_city, OG.ru_id, OG.order_id, OG.product_id FROM " . $GLOBALS['dsc']->table('delivery_goods') . " AS DG, " .
                        $GLOBALS['dsc']->table('goods') . " AS G, " .
                        $GLOBALS['dsc']->table('delivery_order') . " AS D, " .
                        $GLOBALS['dsc']->table('order_goods') . " AS OG " .
                        " WHERE DG.goods_id = G.goods_id AND DG.delivery_id = D.delivery_id AND D.order_id = OG.order_id AND DG.goods_sn = OG.goods_sn AND DG.product_id = OG.product_id AND DG.delivery_id = '$delivery_id' GROUP BY OG.rec_id ";

                    $delivery_stock_result = $GLOBALS['db']->getAll($delivery_stock_sql);

                    $virtual_goods = [];
                    for ($i = 0; $i < count($delivery_stock_result); $i++) {
                        $res = Products::whereRaw(1);
                        if ($delivery_stock_result[$i]['model_attr'] == 1) {
                            $res = ProductsWarehouse::where('warehouse_id', $delivery_stock_result[$i]['warehouse_id']);
                        } elseif ($delivery_stock_result[$i]['model_attr'] == 2) {
                            $res = ProductsArea::where('area_id', $delivery_stock_result[$i]['area_id']);
                        }

                        $res = $res->where('goods_id', $delivery_stock_result[$i]['goods_id']);
                        $prod = BaseRepository::getToArrayFirst($res);

                        /* 如果商品存在规格就查询规格，如果不存在规格按商品库存查询 */
                        if (empty($prod)) {
                            if ($delivery_stock_result[$i]['model_inventory'] == 1) {
                                $delivery_stock_result[$i]['storage'] = get_warehouse_area_goods($delivery_stock_result[$i]['warehouse_id'], $delivery_stock_result[$i]['goods_id'], 'warehouse_goods');
                            } elseif ($delivery_stock_result[$i]['model_inventory'] == 2) {
                                $delivery_stock_result[$i]['storage'] = get_warehouse_area_goods($delivery_stock_result[$i]['area_id'], $delivery_stock_result[$i]['goods_id'], 'warehouse_area_goods');
                            }
                        } else {
                            $products = $this->goodsWarehouseService->getWarehouseAttrNumber($delivery_stock_result[$i]['goods_id'], $delivery_stock_result[$i]['goods_attr_id'], $delivery_stock_result[$i]['warehouse_id'], $delivery_stock_result[$i]['area_id'], $delivery_stock_result[$i]['area_city'], $delivery_stock_result[$i]['model_attr']);
                            $delivery_stock_result[$i]['storage'] = $products['product_number'];
                        }

                        if (($delivery_stock_result[$i]['sums'] > $delivery_stock_result[$i]['storage'] || $delivery_stock_result[$i]['storage'] <= 0) && (($GLOBALS['_CFG']['use_storage'] == '1' && $GLOBALS['_CFG']['stock_dec_time'] == SDT_SHIP) || ($GLOBALS['_CFG']['use_storage'] == '0' && $delivery_stock_result[$i]['is_real'] == 0))) {
                            /* 操作失败 */
                            throw new \Exception(sprintf(lang('admin/order.act_good_vacancy'), $delivery_stock_result[$i]['goods_name']), 1);
                            break;
                        }

                        /* 虚拟商品列表 virtual_card*/
                        if ($delivery_stock_result[$i]['is_real'] == 0) {
                            $virtual_goods[] = [
                                'goods_id' => $delivery_stock_result[$i]['goods_id'],
                                'goods_name' => $delivery_stock_result[$i]['goods_name'],
                                'num' => $delivery_stock_result[$i]['send_number']
                            ];
                        }
                    }
                    //ecmoban模板堂 --zhuo end 下单减库存

                    /* 发货 */
                    /* 处理虚拟卡 商品（虚货） */
                    if ($virtual_goods && is_array($virtual_goods) && count($virtual_goods) > 0) {
                        foreach ($virtual_goods as $virtual_value) {
                            virtual_card_shipping($virtual_value, $order['order_sn'], $msg, 'split');
                        }

                        //虚拟卡缺货
                        if (!empty($msg)) {
                            /* 操作失败 */
                            throw new \Exception($msg, 1);
                        }
                    }

                    /* 如果使用库存，且发货时减库存，则修改库存 */
                    if ($GLOBALS['_CFG']['use_storage'] == '1' && $GLOBALS['_CFG']['stock_dec_time'] == SDT_SHIP) {
                        foreach ($delivery_stock_result as $value) {

                            /* 商品（实货）、超级礼包（实货） ecmoban模板堂 --zhuo */
                            if ($value['is_real'] != 0) {
                                //（货品）
                                if (!empty($value['product_id'])) {
                                    if ($value['model_attr'] == 1) {
                                        ProductsWarehouse::where('product_id', $value['product_id'])
                                            ->decrement('product_number', $value['sums']);
                                    } elseif ($value['model_attr'] == 2) {
                                        ProductsArea::where('product_id', $value['product_id'])
                                            ->decrement('product_number', $value['sums']);
                                    } else {
                                        Products::where('product_id', $value['product_id'])
                                            ->decrement('product_number', $value['sums']);
                                    }
                                } else {
                                    if ($value['model_inventory'] == 1) {
                                        WarehouseGoods::where('goods_id', $value['goods_id'])
                                            ->where('region_id', $value['warehouse_id'])
                                            ->decrement('region_number', $value['sums']);
                                    } elseif ($value['model_inventory'] == 2) {
                                        WarehouseAreaGoods::where('goods_id', $value['goods_id'])
                                            ->where('region_id', $value['area_id'])
                                            ->decrement('region_number', $value['sums']);
                                    } else {
                                        Goods::where('goods_id', $value['goods_id'])
                                            ->decrement('goods_number', $value['sums']);
                                    }
                                }

                                //库存日志
                                $logs_other = [
                                    'goods_id' => $value['goods_id'],
                                    'order_id' => $value['order_id'],
                                    'use_storage' => $GLOBALS['_CFG']['stock_dec_time'],
                                    'admin_id' => $admin_id,
                                    'number' => "- " . $value['sums'],
                                    'model_inventory' => $value['model_inventory'],
                                    'model_attr' => $value['model_attr'],
                                    'product_id' => $value['product_id'],
                                    'warehouse_id' => $value['warehouse_id'],
                                    'area_id' => $value['area_id'],
                                    'add_time' => TimeRepository::getGmTime()
                                ];

                                GoodsInventoryLogs::insert($logs_other);
                            }
                        }
                    }

                    /* 修改发货单信息 */
                    $_delivery['invoice_no'] = $invoice_no;
                    $_delivery['express_code'] = $express_code;
                    $_delivery['status'] = DELIVERY_SHIPPED; // 0，为已发货
                    $query = DeliveryOrder::where('delivery_id', $delivery_id)->update($_delivery);
                    if (!$query) {
                        /* 操作失败 */
                        throw new \Exception(lang('admin/order.act_false'), 1);
                    }

                    /* 标记订单为已确认 “已发货” */
                    /* 更新发货时间 */
                    $order_finish = $this->getAllDeliveryFinish($order_id);
                    $shipping_status = ($order_finish == 1) ? SS_SHIPPED : SS_SHIPPED_PART;
                    $arr['shipping_status'] = $shipping_status;
                    $arr['shipping_time'] = TimeRepository::getGmTime(); // 发货时间
                    $arr['invoice_no'] = !empty($invoice_no) ? $invoice_no : $order['invoice_no'];
                    $arr['express_code'] = $express_code;

                    if (empty($order['pay_time'])) {
                        $arr['pay_time'] = TimeRepository::getGmTime();
                    }

                    update_order($order_id, $arr);

                    // 记录物流信息
                    $this->expressManageService->saveExpressHistory($order, $delivery_order, $invoice_no, $express_code);

                    /* 发货单发货记录log */
                    order_action($order['order_sn'], OS_CONFIRMED, $shipping_status, $order['pay_status'], $action_note, $action_user, 1);

                    /* 如果当前订单已经全部发货 */
                    if ($order_finish) {
                        /* 如果订单用户不为空，计算积分，并发给用户；发红包 */
                        if ($order['user_id'] > 0) {

                            /* 计算并发放积分 */
                            $integral = integral_to_give($order);

                            /* 如果已配送子订单的赠送积分大于0   减去已配送子订单积分 */
                            if (!empty($child_order)) {
                                $integral['custom_points'] = $integral['custom_points'] - $child_order['custom_points'];
                                $integral['rank_points'] = $integral['rank_points'] - $child_order['rank_points'];
                            }

                            log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf(lang('admin/order.order_gift_integral'), $order['order_sn']));

                            /* 发放红包 */
                            send_order_bonus($order_id);

                            /* 发放优惠券 bylu */
                            send_order_coupons($order_id);

                            /* 更新商品销量 一键发货 */
                            get_goods_sale($order_id);
                        }

                        /* 发送邮件 */
                        if (config('shop.send_ship_email', 0) == '1') {
                            $order['invoice_no'] = $invoice_no;
                            $tpl = get_mail_template('deliver_notice');
                            $GLOBALS['smarty']->assign('order', $order);
                            $GLOBALS['smarty']->assign('send_time', TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format']));
                            $GLOBALS['smarty']->assign('shop_name', $GLOBALS['_CFG']['shop_name']);
                            $GLOBALS['smarty']->assign('send_date', TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], TimeRepository::getGmTime()));
                            $GLOBALS['smarty']->assign('sent_date', TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], TimeRepository::getGmTime()));
                            $GLOBALS['smarty']->assign('confirm_url', url('user_order.php?act=order_detail&order_id=' . $order['order_id'])); //by wu
                            $GLOBALS['smarty']->assign('send_msg_url', url('user_message.php?act=message_list&order_id=' . $order['order_id']));
                            $content = $GLOBALS['smarty']->fetch('str:' . $tpl['template_content']);
                            if (!$this->commonRepository->sendEmail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html'])) {
                                $msg = lang('admin/order.send_mail_fail');
                            }
                        }

                        /* 如果需要，发短信 */
                        if (config('shop.sms_order_shipped', 0) == '1' && $order['mobile'] != '') {

                            //短信接口参数
                            if ($order['ru_id']) {
                                $shop_name = $this->merchantCommonService->getShopName($order['ru_id'], 1);
                            } else {
                                $shop_name = "";
                            }

                            $user_info = get_admin_user_info($order['user_id']);

                            $smsParams = [
                                'shop_name' => $shop_name,
                                'shopname' => $shop_name,
                                'user_name' => $user_info['user_name'],
                                'username' => $user_info['user_name'],
                                'consignee' => $order['consignee'],
                                'order_sn' => $order['order_sn'],
                                'ordersn' => $order['order_sn'],
                                'invoice_no' => $invoice_no,
                                'invoiceno' => $invoice_no,
                                'mobile_phone' => $order['mobile'],
                                'mobilephone' => $order['mobile']
                            ];

                            $this->commonRepository->smsSend($order['mobile'], $smsParams, 'sms_order_shipped', false);
                        }

                        // 微信通模板消息 发货通知
                        if (file_exists(MOBILE_WECHAT)) {
                            $pushData = [
                                'first' => ['value' => lang('wechat::wechat.order_shipping_first')], // 标题
                                'keyword1' => ['value' => $order['order_sn']], //订单
                                'keyword2' => ['value' => $order['shipping_name']], //物流服务
                                'keyword3' => ['value' => $invoice_no], //快递单号
                                'keyword4' => ['value' => $order['consignee']], // 收货信息
                                'remark' => ['value' => lang('wechat::wechat.order_shipping_remark')]
                            ];
                            $shop_url = url('/') . '/'; // 根域名 兼容商家后台
                            $order_url = dsc_url('/#/user/orderDetail/' . $order_id);

                            push_template_curl('OPENTM202243318', $pushData, $order_url, $order['user_id'], $shop_url);
                        }
                    }

                    /* 更新会员订单信息 */
                    $this->orderCommonService->getUserOrderNumServer($order['user_id'] ?? 0);
                    DB::commit();

                } catch (\Exception $exception) {
                    DB::rollBack();
                    if ($type == 'one') {
                        return ['error_code' => $exception->getCode(), 'msg' => $exception->getMessage()];
                    }
                    continue;
                }
            }

            if ($type == 'one') {
                return ['error_code' => 0, 'msg' => lang('admin/order.act_ok'), 'delivery_id' => $delivery_id, 'ru_id' => $order['ru_id']];
            }
        }
        return ['error_code' => 0, 'msg' => lang('admin/order.act_ok')];
    }

    /**
     * 获取订单的发货信息
     * @param array $order_sn
     * @param array $order_id
     * @return array
     */
    public function getShippingInfo($order_sn = [], $order_id = [])
    {
        $order = OrderInfo::query();

        if (!empty($order_sn)) {
            $order = $order->whereIn('order_sn', $order_sn);
        }

        if (!empty($order_id)) {
            $order = $order->whereIn('order_id', $order_id);
        }

        $order = $order->orderByDesc('order_id')->limit(10);

        $res = BaseRepository::getToArrayGet($order);

        $list = [];
        if ($res) {
            $countryRegion = BaseRepository::getColumn($res, 'country', 'order_id');
            $provinceRegion = BaseRepository::getColumn($res, 'province', 'order_id');
            $cityRegion = BaseRepository::getColumn($res, 'city', 'order_id');
            $districtRegion = BaseRepository::getColumn($res, 'district', 'order_id');
            $streetRegion = BaseRepository::getColumn($res, 'street', 'order_id');

            $orderRegion = OrderDataHandleService::orderRegionAddress($countryRegion, $provinceRegion, $cityRegion, $districtRegion, $streetRegion);
            $arr = [];
            foreach ($res as $key => $row) {
                /* 取得区域名 */
                $country = $orderRegion['country'][$row['order_id']]['region_name'] ?? '';
                $province = $orderRegion['province'][$row['order_id']]['region_name'] ?? '';
                $city = $orderRegion['city'][$row['order_id']]['region_name'] ?? '';
                $district = $orderRegion['district'][$row['order_id']]['region_name'] ?? '';
                $street = $orderRegion['street'][$row['order_id']]['region_name'] ?? '';
                $data['address'] = OrderDataHandleService::orderRegionCombination($country, $province, $city, $district, $street) . $row['address'];
                $data['shipping_id'] = $row['shipping_id'] ?? 0;
                $data['shipping_name'] = $row['shipping_name'] ?? '';
                $data['shipping_code'] = $row['shipping_code'] ?? '';
                $data['order_id'] = $row['order_id'] ?? '';
                $data['mobile'] = $row['mobile'] ?? '';
                $data['order_sn'] = $row['order_sn'] ?? '';
                $data['consignee'] = $row['consignee'] ?? '';

                $delivery_id = DeliveryOrder::where('order_sn', $row['order_sn'])->value('delivery_id');
                $delivery_id = $delivery_id ?? 0;
                //可执行的操作列表
                $operable_list = $this->operableList($row);
                $operable_list['split'] = $operable_list['split'] ?? false;
                $operable_list['receive'] = $operable_list['receive'] ?? false;

                //过滤门店订单
                $store_id = StoreOrder::where('order_id', $row['order_id'])->value('store_id');
                $store_id = $store_id ?? 0;

                //过滤拼团失败
                $is_team_suceess = 1;//默认成功
                if ($row['team_id'] > 0) {
                    $teamInfo = $this->teamOrderUserNum($row['team_id']);
                    $is_team_suceess = $teamInfo['is_team'] ?? 1;
                }

                //过滤主订单
                $order_count = OrderInfo::where('main_order_id', $row['order_id'])->count();

                //过滤不能发货的订单 和列表保持一致
                if ($operable_list['split'] === false || $operable_list['receive'] === true || $delivery_id > 0 || $store_id > 0 || $is_team_suceess == 0 || $order_count > 0) {
                    continue;
                }
                $arr[] = $data;
            }

            $list = collect($arr)->groupBy('shipping_name')->toArray();
        }
        return $list;
    }

}
