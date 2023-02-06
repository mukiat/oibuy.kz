<?php

namespace App\Services\Order;

use App\Exceptions\HttpException;
use App\Models\DeliveryOrder;
use App\Models\GoodsActivity;
use App\Models\OfflineStore;
use App\Models\OrderDelayed;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\Payment;
use App\Models\PresaleActivity;
use App\Models\Region;
use App\Models\ShippingPoint;
use App\Models\StoreOrder;
use App\Models\TeamLog;
use App\Models\UserOrderNum;
use App\Models\ValueCardRecord;
use App\Plugins\UserRights\Discount\Services\DiscountRightsService;
use App\Repositories\Activity\ActivityRepository;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Activity\GroupBuyService;
use App\Services\Activity\PackageService;
use App\Services\Cgroup\CgroupService;
use App\Services\Comment\OrderCommentService;
use App\Services\Commission\CommissionService;
use App\Services\CrossBorder\CrossBorderService;
use App\Services\Goods\GoodsAttrService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Payment\PaymentDataHandleService;
use App\Services\Payment\PaymentService;
use App\Services\Region\RegionDataHandleService;
use App\Services\Team\TeamService;
use App\Services\User\UserOrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 会员订单
 * Class order
 * @package App\Services
 */
class OrderMobileService
{
    protected $orderApiService;
    protected $orderCommentService;
    protected $commonService;
    protected $packageService;
    protected $commonRepository;
    protected $orderRefoundService;
    protected $commissionService;
    protected $dscRepository;
    protected $userOrderService;
    protected $groupBuyService;
    protected $merchantCommonService;
    protected $orderDeliveryService;
    protected $goodsAttrService;

    public function __construct(
        OrderApiService $orderApiService,
        OrderCommentService $orderCommentService,
        PackageService $packageService,
        CommonRepository $commonRepository,
        OrderRefoundService $orderRefoundService,
        CommissionService $commissionService,
        DscRepository $dscRepository,
        UserOrderService $userOrderService,
        MerchantCommonService $merchantCommonService,
        OrderDeliveryService $orderDeliveryService,
        GoodsAttrService $goodsAttrService
    )
    {
        $files = [
            'clips',
            'main',
            'order',
            'function',
            'base',
            'goods',
            'ecmoban'
        ];
        load_helper($files);
        $this->orderApiService = $orderApiService;
        $this->orderCommentService = $orderCommentService;
        $this->packageService = $packageService;
        $this->commonRepository = $commonRepository;
        $this->orderRefoundService = $orderRefoundService;
        $this->commissionService = $commissionService;
        $this->dscRepository = $dscRepository;
        $this->userOrderService = $userOrderService;
        $this->groupBuyService = app(GroupBuyService::class);
        $this->merchantCommonService = $merchantCommonService;
        $this->orderDeliveryService = $orderDeliveryService;
        $this->goodsAttrService = $goodsAttrService;
    }

    /**
     * 订单列表
     * @param int $uid
     * @param int $status
     * @param string $type
     * @param int $page
     * @param int $size
     * @param string $keywords
     * @return array
     * @throws \Exception
     */
    public function orderList($uid = 0, $status = 0, $type = '', $page = 1, $size = 10, $keywords = '')
    {
        $List = $this->orderApiService->getUserOrders($uid, $status, $type, $page, $size, $keywords);

        if (empty($List)) {
            return [];
        }

        $os = trans('order.os');
        $ps = trans('order.ps');
        $ss = trans('order.ss');

        $nowTime = TimeRepository::getGmTime();
        $sign_time = config('shop.sign') ?? 0; //发货日期起可退换货时间

        $order_ids = BaseRepository::getKeyPluck($List, 'order_id');
        $comment_order_id = OrderGoods::whereIn('order_id', $order_ids)->where('user_id', $uid)->where('is_comment', 0)->where('main_count', 0)->where('is_received', 0)->pluck('order_id'); // 未评价记录ID
        $comment_order_id = BaseRepository::getToArray($comment_order_id);
        $comment_order_id = $comment_order_id ? array_unique($comment_order_id) : [];

        $orderList = [];

        $order_id = BaseRepository::getKeyPluck($List, 'order_id');
        $returnList = OrderDataHandleService::getOrderReturnDataList($order_id, ['ret_id', 'rec_id', 'order_id'], 'order_id');
        $deliveryOrderList = OrderDataHandleService::getDeliveryOrderDataList($order_id, ['order_id', 'delivery_id', 'invoice_no', 'shipping_name', 'update_time']);
        $storeOrderList = OrderDataHandleService::isStoreOrder($order_id);

        $payIdList = BaseRepository::getKeyPluck($List, 'pay_id');
        $payList = PaymentDataHandleService::getPaymentDataList($payIdList, ['pay_id', 'pay_code']);

        $ruIdList = BaseRepository::getKeyPluck($List, 'ru_id');
        $ShopInfoList = MerchantDataHandleService::SellerShopinfoDataList($ruIdList);

        $provinceIdList = BaseRepository::getKeyPluck($List, 'province');
        $cityIdList = BaseRepository::getKeyPluck($List, 'city');
        $districtIdList = BaseRepository::getKeyPluck($List, 'district');
        $streetIdList = BaseRepository::getKeyPluck($List, 'street');
        $regionIdList = ArrRepository::getArrCollapse([$provinceIdList, $cityIdList, $districtIdList, $streetIdList]);
        $regionIdList = BaseRepository::getArrayUnique($regionIdList);

        $regionList = RegionDataHandleService::getRegionDataList($regionIdList, ['region_id', 'region_name']);

        $orderTeamList = [];
        if (file_exists(MOBILE_TEAM)) {
            $teamIdList = BaseRepository::getColumn($List, 'team_id', 'order_id');
            $orderTeamList = OrderDataHandleService::getOrderTeamList($teamIdList);
        }

        $ru_id = BaseRepository::getKeyPluck($List, 'ru_id');
        $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

        $time = TimeRepository::getGmTime();

        $orderGoodsList = OrderDataHandleService::orderGoodsDataList($order_id, '*', 1);

        $goodsIdList = BaseRepository::getKeyPluck(ArrRepository::getArrCollapse($orderGoodsList), 'goods_id');
        $goodsDataList = GoodsDataHandleService::GoodsDataList($goodsIdList, ['goods_id', 'goods_thumb', 'goods_cause']);

        $productOrderGoodsList = ArrRepository::getArrCollapse($orderGoodsList);
        $orderGoodsAttrIdList = BaseRepository::getKeyPluck($productOrderGoodsList, 'goods_attr_id');
        $orderGoodsAttrIdList = BaseRepository::getArrayUnique($orderGoodsAttrIdList);
        $orderGoodsAttrIdList = ArrRepository::getArrayUnset($orderGoodsAttrIdList);

        $productsGoodsAttrList = [];
        if ($orderGoodsAttrIdList) {
            $orderGoodsAttrIdList = BaseRepository::getImplode($orderGoodsAttrIdList);
            $productsGoodsAttrList = GoodsDataHandleService::getGoodsAttrDataList($orderGoodsAttrIdList, ['goods_attr_id', 'attr_img_flie', 'attr_gallery_flie']);
        }

        foreach ($List as $k => $v) {

            $goodsSelf = false;
            if ($v['ru_id'] == 0) {
                $goodsSelf = true;
            }

            $payInfo = $payList[$v['pay_id']] ?? [];
            $orderList[$k]['pay_code'] = $payInfo['pay_code'] ?? '';

            $v['user_order'] = $v['order_status'];
            $v['user_shipping'] = $v['shipping_status'];
            $v['user_pay'] = $v['pay_status'];

            //处理支付超时订单
            $pay_effective_time = config('shop.pay_effective_time') > 0 ? intval(config('shop.pay_effective_time')) : 0; //订单时效
            //订单时效大于零及开始时效性  且订单未付款未发货  支付方式为线上支付

            $pay_code = $orderList[$k]['pay_code'];

            if ($pay_effective_time > 0 && $v['pay_status'] == PS_UNPAYED && in_array($v['order_status'], [OS_UNCONFIRMED, OS_CONFIRMED]) && in_array($v['shipping_status'], [SS_UNSHIPPED, SS_PREPARING]) && !in_array($pay_code, ['cod', 'bank'])) {
                if ($v['order_status'] != OS_INVALID) {
                    //计算时效性时间戳
                    $pay_effective_time = $pay_effective_time * 60;

                    //如果订单超出时间设为无效
                    if (($time - $v['add_time']) > $pay_effective_time) {
                        $store_order_id = StoreOrder::where('order_id', $v['order_id'])->value('store_id');

                        $store_id = ($store_order_id > 0) ? $store_order_id : 0;

                        /* 标记订单为“无效” */
                        update_order($v['order_id'], ['order_status' => OS_INVALID]);

                        $v['order_status'] = OS_INVALID;

                        /* 记录log */
                        order_action($v['order_sn'], OS_INVALID, SS_UNSHIPPED, PS_UNPAYED, $GLOBALS['_LANG']['pay_effective_Invalid'], lang('common.buyer'));

                        /* 如果使用库存，且下订单时减库存，则增加库存 */
                        if (config('shop.use_storage') == 1 && config('shop.stock_dec_time') == SDT_PLACE) {
                            change_order_goods_storage($v['order_id'], false, SDT_PLACE, 2, 0, $store_id);
                        }

                        /* 退还用户余额、积分、红包 */
                        return_user_surplus_integral_bonus($v);

                        /* 更新会员订单数量 */
                        if (isset($v['user_id']) && !empty($v['user_id'])) {
                            $order_nopay = UserOrderNum::where('user_id', $v['user_id'])->value('order_nopay');
                            $order_nopay = $order_nopay ? intval($order_nopay) : 0;

                            if ($order_nopay > 0) {
                                $dbRaw = [
                                    'order_nopay' => "order_nopay - 1",
                                ];
                                $dbRaw = BaseRepository::getDbRaw($dbRaw);
                                UserOrderNum::where('user_id', $v['user_id'])->update($dbRaw);
                            }
                        }

                        sleep(1);
                    }
                }
            }

            $orderList[$k]['order_id'] = $v['order_id'];
            $orderList[$k]['order_sn'] = $v['order_sn'];
            $orderList[$k]['consignee'] = $v['consignee'];
            $orderList[$k]['main_order_id'] = $v['main_order_id'];
            $orderList[$k]['is_delete'] = $v['is_delete'];
            $orderList[$k]['add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $v['add_time']); // 订单时间

            // 格式化 订单综合状态
            if (($v['order_status'] == OS_CONFIRMED || $v['order_status'] == OS_SPLITED) && $v['pay_status'] == PS_PAYED && $v['shipping_status'] == SS_RECEIVED) {
                $orderList[$k]['order_status'] = trans('user.ss_received'); // 已完成
            } elseif ($v['order_status'] == OS_RETURNED) {
                $orderList[$k]['order_status'] = trans('order.os.' . $v['order_status']) . '，' . trans('order.ps.' . $v['pay_status']);
            } elseif ($v['order_status'] == OS_ONLY_REFOUND) {
                $orderList[$k]['order_status'] = trans('order.os.' . $v['order_status']) . '，' . trans('order.ps.' . $v['pay_status']);
            } else {
                $orderList[$k]['order_status'] = $os[$v['order_status']] . ',' . $ps[$v['pay_status']] . ',' . $ss[$v['shipping_status']];
            }

            $orderList[$k]['order_count'] = $v['main_count'];

            if ($v['main_count'] > 0 && empty($v['main_order_id'])) {
                $orderList[$k]['order_child'] = OrderInfo::where('main_order_id', $v['order_id'])->count('order_id');
            }

            $delivery = $deliveryOrderList[$v['order_id']] ?? [];
            $delivery = BaseRepository::getArrayFirst($delivery);

            if (isset($delivery['update_time'])) {
                $orderList[$k]['delivery_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $delivery['update_time']);
            }

            $v['user_order'] = $v['order_status'];
            $v['user_shipping'] = $v['shipping_status'];
            $v['user_pay'] = $v['pay_status'];

            if (in_array($v['order_status'], [OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART, OS_RETURNED_PART, OS_ONLY_REFOUND])) {
                // 是否显示确认收货 handler_receive 0 不显示 1 显示
                $v['handler_receive'] = OrderStatusService::can_receive($v);
                if ($v['handler_receive'] == 1) {
                    $v['handler'] = 2; //确认收货
                    $v['handler_this'] = ['order_id' => $v['order_id']];
                } elseif ($v['user_shipping'] == SS_RECEIVED) {
                    $v['handler'] = 4; // 已完成
                    $v['handler_this'] = ['order_id' => $v['order_id']];
                } else {
                    if ($v['user_pay'] == PS_UNPAYED || $v['pay_status'] == PS_PAYED_PART) {
                        $v['handler'] = "<a class=\"btn-default-new br-5\" href=\"" . '" >' . lang('user.pay_money') . '</a>';
                    } else {
                        $v['handler'] = "<a  class=\"btn-default-new br-5\" href=\"" . '">' . lang('user.view_order') . '</a>';
                        $v['handler_this'] = ['order_id' => $v['order_id']];
                    }
                }
            } else {
                $v['handler'] = '<a class="btn-default-new br-5">' . $os[$v['user_order']] . '</a>';
            }

            //延迟收货
            $delay = 0;
            if ($v['user_order'] == OS_SPLITED && $v['user_pay'] == PS_PAYED && $v['user_shipping'] == SS_SHIPPED) {
                //时间满足必须满足   距自动确认收货前多少天可申请 < （自动确认收货时间+发货时间）-当前时间
                //$a < ($b + $c) - $nowTime;
                $order_delay_day = config('shop.order_delay_day') * 86400;//$a
                $auto_delivery_time = $v['auto_delivery_time'] * 86400;//$b
                $shipping_time = $v['shipping_time'];//$c
                if ($order_delay_day > (($auto_delivery_time + $shipping_time) - $nowTime)) {
                    $num = OrderDelayed::where('order_id', $v['order_id'])->where('review_status', '<>', 1)->count();
                    if (config('shop.open_order_delay') == 1 && $num < config('shop.order_delay_num')) {
                        $delay = 1;
                    }
                }
            }
            $orderList[$k]['delay'] = $delay;

            // 已删除 可还原
            if ($v['is_delete'] == 1) {
                $orderList[$k]['is_restore'] = 1;
            } else {
                $orderList[$k]['is_restore'] = 0;
            }

            // 是否显示取消订单  handler_cancel 0 不显示 1 显示
            $v['handler_cancel'] = OrderStatusService::can_cancel($v);
            if ($v['handler_cancel'] == 1) {
                $v['handler'] = 1; //取消订单
            }

            // 是否显示删除订单 order_del： 0 不可删除 1 可删除
            $orderList[$k]['order_del'] = OrderStatusService::can_delete($v);

            $order_is_package_buy = 0;//过滤超值礼包

            $goodsList = $orderGoodsList[$v['order_id']] ?? [];
            $orderList[$k]['order_goods_num'] = count($goodsList);

            $goods_return_support = []; // 订单商品是否支持退换货
            if ($goodsList) {
                foreach ($goodsList as $key => $val) {

                    //过滤超值礼包合并数组
                    if (empty($val['extension_code']) || $val['extension_code'] != 'package_buy') {
                        $goods = $goodsDataList[$val['goods_id']] ?? [];
                        $val = $goods ? array_merge($val, $goods) : $val;
                    }

                    $orderList[$k]['order_goods'][$key]['goods_id'] = $val['goods_id'];
                    $orderList[$k]['order_goods'][$key]['goods_number'] = $val['goods_number'];
                    $orderList[$k]['order_goods'][$key]['goods_price'] = $this->dscRepository->getPriceFormat($val['goods_price'], true, true, $goodsSelf);
                    $orderList[$k]['order_goods'][$key]['goods_attr'] = str_replace("\n", '', $val['goods_attr']);

                    $val['goods_thumb'] = $val['goods_thumb'] ?? '';
                    $orderList[$k]['order_goods'][$key]['goods_thumb'] = $this->dscRepository->getImagePath($val['goods_thumb']);

                    if ($val['extension_code'] == 'package_buy') {
                        $order_is_package_buy = $order_is_package_buy + 1;//过滤超值礼包

                        $activity = GoodsActivity::select('act_id', 'activity_thumb')->where('review_status', 3)
                            ->where('act_id', $val['goods_id']);
                        $activity = BaseRepository::getToArrayFirst($activity);

                        if ($activity) {
                            $activity['goods_thumb'] = !empty($activity['activity_thumb']) ? $this->dscRepository->getImagePath($activity['activity_thumb']) : $this->dscRepository->dscUrl('themes/ecmoban_dsc2017/images/17184624079016pa.jpg');
                        }

                        $activity['goods_thumb'] = $activity['goods_thumb'] ?? '';
                        $orderList[$k]['order_goods'][$key]['goods_thumb'] = $this->dscRepository->getImagePath($activity['goods_thumb']);
                    }

                    $goods_attr_id = $val['goods_attr_id'] ?? '';
                    $goods_attr_id = BaseRepository::getExplode($goods_attr_id);
                    $orderList[$k]['order_goods'][$key]['goods_thumb'] = $this->goodsAttrService->cartGoodsAttrImage($goods_attr_id, $productsGoodsAttrList, $orderList[$k]['order_goods'][$key]['goods_thumb']);

                    $orderList[$k]['order_goods'][$key]['goods_name'] = $val['goods_name'];
                    $orderList[$k]['order_goods'][$key]['goods_sn'] = $val['goods_sn'];
                    $orderList[$k]['order_goods'][$key]['drp_money'] = $this->dscRepository->getPriceFormat($val['drp_money'], true, true, $goodsSelf);
                    $orderList[$k]['order_goods'][$key]['ru_id'] = $val['ru_id'];
                    $orderList[$k]['order_goods'][$key]['parent_id'] = $val['parent_id'];
                    $orderList[$k]['order_goods'][$key]['is_gift'] = $val['is_gift'];
                    $orderList[$k]['order_goods'][$key]['country_icon'] = $merchantList[$v['ru_id']]['country_icon'] ?? '';

                    // 订单活动标识
                    $orderList[$k]['extension_code'] = $val['extension_code'];

                    // 订单商品退换货标识
                    $order_goods_cause = app(GoodsCommonService::class)->getGoodsCause($val['goods_cause'] ?? '', $v, $val);
                    if (!empty($order_goods_cause)) {
                        $goods_return_support[$val['goods_id']] = 1;
                    }
                }
            } else {
                $orderList[$k]['order_goods'] = [];
            }

            /* 退换货显示处理 start */
            $handler_return = OrderStatusService::can_return($v);

            // 判断发货日期起可退换货时间
            if ($sign_time > 0) {
                $order_status = [OS_CANCELED, OS_INVALID, OS_RETURNED, OS_ONLY_REFOUND]; // 订单状态 已取消、已失效、已退款
                if (in_array($v['user_order'], $order_status) || in_array($v['user_pay'], [PS_REFOUND])) {
                    $handler_return = 0;
                } else {

                    $log_time = 0;
                    if ($v['user_pay'] == PS_UNPAYED && $v['user_shipping'] == SS_RECEIVED) {
                        // 未付款、已确认收货 [银行转账 货到付款]
                        $log_time = $v['confirm_take_time'];
                    } elseif ($v['user_pay'] == PS_PAYED) {
                        // 已付款
                        if (in_array($v['user_shipping'], [SS_SHIPPED, SS_SHIPPED_PART, OS_SHIPPED_PART])) {
                            // 已发货[含部分发货]
                            $log_time = $v['shipping_time'];
                        } elseif (in_array($v['user_shipping'], [SS_RECEIVED, SS_PART_RECEIVED])) {
                            // 已收货[含部分收货]
                            $log_time = $v['confirm_take_time'];
                        }
                    }

                    $handler_return = 0;
                    if ($v['user_shipping'] == SS_UNSHIPPED) {
                        // 未发货 可申请售后
                        $handler_return = 1;
                    } else {
                        $return_time = $log_time + $sign_time * 3600 * 24;
                        if (!empty($log_time) && $time < $return_time) {
                            // 可申请售后
                            $handler_return = 1;
                        }
                    }
                }
            }

            // 订单商品 其中一个支持 即显示申请售后
            $goods_handler_return = 0;
            if (!empty($goods_return_support) && !empty($goodsList)) {
                if (count($goodsList) >= count($goods_return_support)) {
                    $goods_handler_return = 1;
                }
            }

            if ($goods_handler_return == 1) {
                // 订单商品是否已申请退款(部分退款1，全退款0)
                $order_goods_count = BaseRepository::getArrayCount($goodsList);

                $return_goods = $returnList[$v['order_id']] ?? [];
                $return_goods = BaseRepository::getArrayCount($return_goods);

                if ($order_goods_count == $return_goods) {
                    $handler_return = 0;
                }
            }
            // 订单商品支持退换货显示申请售后
            if ($handler_return == 1 && $goods_handler_return == 1) {
                $handler_return = 1;
            } else {
                $handler_return = 0;
            }

            $orderList[$k]['handler_return'] = $handler_return;
            /* 退换货显示处理 end */

            $provinceInfo = $regionList[$v['province']] ?? [];
            $province = $provinceInfo['region_name'] ?? '';
            $cityInfo = $regionList[$v['city']] ?? [];
            $city = $cityInfo['region_name'] ?? '';
            $districtInfo = $regionList[$v['district']] ?? [];
            $district = $districtInfo['region_name'] ?? '';
            $streetInfo = $regionList[$v['street']] ?? [];
            $street = $streetInfo['region_name'] ?? '';

            $district_name = !empty($district) ? $district : '';
            $address_detail = $province . "&nbsp;" . $city . "&nbsp;" . $street . "&nbsp;" . $district_name;

            $shopInfo = $ShopInfoList[$v['ru_id']] ?? [];
            $orderList[$k]['shop_id'] = $shopInfo['id'] ?? 0;
            $orderList[$k]['shop_name'] = $merchantList[$v['ru_id']]['shop_name'] ?? '';

            $orderList[$k]['kf_qq'] = $shopInfo['kf_qq'] ?? '';
            $orderList[$k]['kf_ww'] = $shopInfo['kf_ww'] ?? '';
            $orderList[$k]['kf_type'] = $shopInfo['kf_type'] ?? '';
            $orderList[$k]['invoice_no'] = $v['invoice_no'];
            $orderList[$k]['email'] = $v['email'];
            $orderList[$k]['shipping_name'] = $v['shipping_name'];
            $orderList[$k]['shipping_id'] = $v['shipping_id'];
            $orderList[$k]['address_detail'] = $address_detail;
            $orderList[$k]['tel'] = $v['tel'];
            $orderList[$k]['handler'] = $v['handler'];
            $orderList[$k]['online_pay'] = $v['online_pay'] ?? '';
            $orderList[$k]['team_id'] = $v['team_id'];
            $orderList[$k]['extension_code'] = $v['extension_code'];
            $orderList[$k]['goods_amount_formated'] = $this->dscRepository->getPriceFormat($v['goods_amount'], true, true, $goodsSelf);
            $orderList[$k]['money_paid_formated'] = $this->dscRepository->getPriceFormat($v['money_paid']);
            $orderList[$k]['order_amount_formated'] = $this->dscRepository->getPriceFormat($v['order_amount'], true, true, $goodsSelf);
            $orderList[$k]['shipping_fee_formated'] = $this->dscRepository->getPriceFormat($v['shipping_fee'], true, true, $goodsSelf);

            $orderList[$k]['invoice_no'] = $v['invoice_no'];
            $orderList[$k]['total_amount'] = $v['order_amount']; // 总金额

            //是否是门店订单
            $is_store_order = $storeOrderList[$v['order_id']]['is_store'] ?? 0;
            $orderList[$k]['is_store_order'] = $is_store_order > 0 ? 1 : 0;

            if ($v['user_pay'] == PS_PAYED) {
                $total_fee_order = $v['money_paid'] + $v['surplus'];
                $orderList[$k]['is_pay'] = 1;
            } else {
                $amount = $v['goods_amount'] + $v['insure_fee'] + $v['pay_fee'] + $v['pack_fee'] + $v['card_fee'] + $v['tax'] - $v['discount'] - $v['vc_dis_money'];

                if (CROSS_BORDER === true) { // 跨境多商户
                    $amount += $v['rate_fee'];
                }

                if ($amount > $v['bonus']) {
                    $amount -= $v['bonus'];
                } else {
                    $amount = 0;
                }

                if ($amount > $v['coupons']) {
                    $amount -= $v['coupons'];
                } else {
                    $amount = 0;
                }

                if ($amount > $v['integral_money']) {
                    $amount -= $v['integral_money'];
                } else {
                    $amount = 0;
                }

                $total_fee_order = $amount + $v['shipping_fee'];
                $orderList[$k]['is_pay'] = 0;
            }

            //验证拼团订单是否失败
            $orderList[$k]['failure'] = 0;
            if (file_exists(MOBILE_TEAM) && $v['team_id'] > 0) {
                $orderList[$k]['failure'] = $orderTeamList[$v['team_id']]['failure'] ?? 0;
            }

            $orderList[$k]['shop_can_comment'] = 0;
            if (config('shop.shop_can_comment') == 1 && $v['is_delete'] == 0) {
                $orderList[$k]['shop_can_comment'] = $shopInfo && $shopInfo['shop_can_comment'] == 1 ? 1 : 0;
            }

            // is_comment 0、还可以评论 1、不可评价
            $orderList[$k]['is_comment'] = 1;

            // 订单是否可以评价  handler_comment 0 不可评价 1 可评价
            $orderList[$k]['handler_comment'] = OrderStatusService::can_comment($v, $orderList[$k]['shop_can_comment']);
            // 订单商品全部已评价 其中一个未评价 可显示评价按钮
            if ($orderList[$k]['handler_comment'] == 1 && $comment_order_id && in_array($v['order_id'], $comment_order_id)) {
                $orderList[$k]['is_comment'] = 0;
            }

            /* 活动语包 */
            $orderList[$k]['activity_lang'] = ActivityRepository::activityLang($v);

            $orderList[$k]['total_amount_formated'] = $this->dscRepository->getPriceFormat($total_fee_order, true, true, $goodsSelf);
        }

        return $orderList;
    }

    /**
     * 订单详情
     *
     * @param array $args
     * @return array
     * @throws \Exception
     */
    public function orderDetail($args = [])
    {
        $lang = lang('user');

        $order = OrderInfo::where('user_id', $args['uid'])
            ->where('order_id', $args['order_id']);

        $order = BaseRepository::getToArrayFirst($order);

        if (empty($order)) {
            return [];
        }

        /**
         * 自动确认收货
         */
        $deliveryData = $this->userOrderService->OrderDeliveryTime($order);

        if ($deliveryData) {
            $order['shipping_status'] = $deliveryData['shipping_status'];
        }

        $order = $this->userOrderService->mainShipping($order);

        // 店铺名称
        $merchantList = MerchantDataHandleService::getMerchantInfoDataList($order['ru_id']);
        $shopinfo = $merchantList[$order['ru_id']] ?? [];

        $user_name = $this->orderApiService->getOrderStore($args['order_id']);

        $address = $this->getRegionName($order['country']);
        $address .= $this->getRegionName($order['province']);
        $address .= $this->getRegionName($order['city']);
        $address .= $this->getRegionName($order['district']);
        $address .= $order['address'];
        //订单使用储值卡
        $card_info = $this->value_card_record($args['order_id']);
        $card_amount_money = $card_info['use_val'] ?? '';
        $card_vc_id = $card_info['vc_id'] ?? 0;

        $payment_info = app(PaymentService::class)->getPaymentInfo(['pay_id' => $order['pay_id']]);
        $pay_code = empty($payment_info) ? '' : $payment_info['pay_code'];

        if ($order['main_count'] > 0) {
            $shopinfo['shop_name'] = config('shop.shop_name');
            $shopinfo['shop_id'] = 0;
        }

        /* 活动语包 */
        $activity_lang = ActivityRepository::activityLang($order);

        $goodsSelf = false;
        if ($order['ru_id'] == 0) {
            $goodsSelf = true;
        }

        $list = [
            'activity_lang' => $activity_lang,
            'add_time' => TimeRepository::getLocalDate(config('shop.time_format'), $order['add_time']),
            'address' => $address,
            'consignee' => $order['consignee'],
            'mobile' => $order['mobile'],
            'shop_id' => $order['ru_id'],
            'shop_name' => $shopinfo['shop_name'],
            'kf_qq' => $user_name['kf_qq'],
            'kf_ww' => $user_name['kf_ww'],
            'kf_type' => $user_name['kf_type'],
            'money_paid' => $order['money_paid'],
            'money_paid_formated' => $this->dscRepository->getPriceFormat($order['money_paid'], true, true, $goodsSelf),
            'goods_amount' => $order['goods_amount'],
            'goods_amount_formated' => $this->dscRepository->getPriceFormat($order['goods_amount'], true, true, $goodsSelf),
            'order_amount' => $order['order_amount'],
            'order_amount_formated' => $this->dscRepository->getPriceFormat($order['order_amount'], true, true, $goodsSelf),
            'order_id' => $order['order_id'],
            'order_sn' => $order['order_sn'],
            'tax_id' => $order['tax_id'], //纳税人识别码
            'inv_payee' => $order['inv_payee'],   //个人还是公司名称 ，增值发票时此值为空
            'inv_content' => $order['inv_content'],//发票明细
            'vat_id' => $order['vat_id'],//增值发票对应的id
            'invoice_type' => $order['invoice_type'],// 0普通发票，1增值发票
            //'invoice_no' => $order['invoice_no'],// 发货单号
            'order_status' => $order['order_status'],
            'pay_status' => $order['pay_status'],
            'shipping_status' => $order['shipping_status'],
            'order_status_formated' => OrderStatusService::orderStatus($order['order_status']),
            'pay_status_formated' => OrderStatusService::payStatus($order['pay_status']),
            'shipping_status_formated' => OrderStatusService::shipStatus($order['shipping_status']),
            'pay_time' => TimeRepository::getLocalDate(config('shop.time_format'), $order['pay_time']),
            'shipping_time' => TimeRepository::getLocalDate(config('shop.time_format'), $order['shipping_time']),
            'pay_fee' => $order['pay_fee'],
            'pay_fee_formated' => $this->dscRepository->getPriceFormat($order['pay_fee'], true, true, $goodsSelf),
            'pay_name' => $order['pay_name'],
            'pay_note' => $order['pay_note'],
            'pay_code' => $pay_code,
            'pack_name' => $order['pack_name'],
            'pack_id' => $order['pack_id'],
            'card_name' => $order['card_name'],
            'card_id' => $order['card_id'],
            'card_amount' => $card_amount_money,
            'vc_dis_money' => $order['vc_dis_money'],
            'vc_dis_money_formated' => $this->dscRepository->getPriceFormat($order['vc_dis_money'], true, true, $goodsSelf),
            'vc_id' => $card_vc_id,
            'parent_id' => $order['parent_id'],
            'shipping_fee' => $order['shipping_fee'],
            'bonus_id' => $order['bonus_id'],
            'bonus' => $this->dscRepository->getPriceFormat($order['bonus'], true, true, $goodsSelf),
            'discount' => $order['discount'],
            'shipping_fee_formated' => $this->dscRepository->getPriceFormat($order['shipping_fee'], true, true, $goodsSelf),
            'discount_formated' => $this->dscRepository->getPriceFormat($order['discount'], true, true, $goodsSelf),
            'shipping_id' => $order['shipping_id'],
            'shipping_name' => $order['shipping_name'],
            'team_id' => $order['team_id'],
            'team_parent_id' => $order['team_parent_id'],
            'team_user_id' => $order['team_user_id'],
            'team_price' => $order['team_price'],
            'coupons_type' => $order['coupons'] > 0 ? 1 : 0,
            'coupons' => $this->dscRepository->getPriceFormat($order['coupons'], true, true, $goodsSelf),
            'integral' => $order['integral'],
            'integral_money' => $this->dscRepository->getPriceFormat($order['integral_money'], true, true, $goodsSelf),
            'surplus' => $order['surplus'],
            'surplus_formated' => $this->dscRepository->getPriceFormat($order['surplus'], true, true, $goodsSelf),
            'exchange_goods' => $order['extension_code'] == 'exchange_goods' ? 1 : 0,
            'postscript' => isset($order['postscript']) ? $order['postscript'] : '',//用户留言 --1.3.7
            'main_count' => $order['main_count'],
            'extension_code' => $order['extension_code'],
            'extension_id' => $order['extension_id'],
            'deliveries' => $this->orderDeliveryService->getDeliveryOrderByOrderId($args['order_id']),
        ];

        if ($order['main_count'] > 0) {
            $list['cross_warehouse_name'] = '';
        } else {
            $list['cross_warehouse_name'] = $shopinfo['cross_warehouse_name'] ?? '';
        }

        // 银行转账信息
        if ($pay_code == 'bank') {
            $payment = app(PaymentService::class)->getPayment($pay_code);
            $pay_config = [];
            if ($payment) {
                $pay_config = $payment->getConfig($pay_code, unserialize($payment_info['pay_config']));
            }
            $list['pay_config'] = $pay_config;

            // 上传支付凭证
            $bank_transfer = DB::table('order_info_bank_transfer')->where('order_id', $order['order_id'])->first();
            $list['pay_document'] = !empty($bank_transfer->pay_document) ? $this->dscRepository->getImagePath($bank_transfer->pay_document) : '';
        }

        $list['order_status_formated'] = $list['order_status_formated'] . ',' . $list['pay_status_formated'] . ',' . $list['shipping_status_formated'];
        // 已付金额
        $list['realpay_amount'] = $order['money_paid'] + $order['surplus'];
        $list['realpay_amount_formated'] = $this->dscRepository->getPriceFormat($list['realpay_amount'], true, true, $goodsSelf);
        // 应付金额
        $list['total_amount'] = $order['order_amount'];
        $list['total_amount_formated'] = $this->dscRepository->getPriceFormat($list['order_amount'], true, true, $goodsSelf);

        if (CROSS_BORDER === true) {
            // 跨境多商户
            $list['rate_fee'] = $order['rate_fee'];
            $list['rate'] = $this->dscRepository->getPriceFormat($list['rate_fee'], true, true, $goodsSelf);

            $cbec = app(CrossBorderService::class)->cbecExists();

            if (!empty($cbec)) {
                $list['is_kj'] = 0;
                $is_kj = $cbec->isKj($order['ru_id']);
                $list['is_kj'] = empty($is_kj) && $list['is_kj'] == 0 ? 0 : 1;
            }

            if ($list['is_kj'] > 0) {
                $list['cross_border'] = true;
            }
        }

        if (file_exists(MOBILE_GROUPBUY)) {
            $post = app(CgroupService::class)->postExists();
            if (!empty($post)) {
                $leader_info = $post->leaderDetail($order['leader_id']);
                $list['leader_id'] = $order['leader_id'] ?? 0;
                $list['post_mobile'] = $leader_info['mobile'] ?? '';
                $list['post_delivery_code'] = $order['use_community_post'] == 1 && $order['use_community_post'] == 1 ? $order['post_delivery_code'] : '';
            }
        }

        if (file_exists(MOBILE_DRP)) {
            // 购买开通权益卡折扣
            $order_membership_card = DiscountRightsService::getOrderInfoMembershipCard($order['order_id'], $order['user_id']);
            if (!empty($order_membership_card)) {
                $list['membership_card_id'] = $order_membership_card['membership_card_id'];

                $list['membership_card_buy_money'] = $order_membership_card['membership_card_buy_money'] ?? 0;
                $list['membership_card_discount_price'] = $order_membership_card['membership_card_discount_price'] ?? 0;

                $list['membership_card_buy_money_formated'] = $this->dscRepository->getPriceFormat($order_membership_card['membership_card_buy_money'], true, true, $goodsSelf);
                $list['membership_card_discount_price_formated'] = $this->dscRepository->getPriceFormat($order_membership_card['membership_card_discount_price'], true, true, $goodsSelf);
            } else {
                $list['membership_card_id'] = 0;
            }
        }

        /* 订单追踪 */
        if (!empty($order['invoice_no'])) {
            $list['tracker'] = route('tracker', ['order_sn' => $order['order_sn']]);
        }

        $nowTime = TimeRepository::getGmTime();

        /*
        * 正常订单显示支付倒计时
        */
        $pay_id = Payment::whereIn('pay_code', ['cod', 'bank'])->pluck('pay_id'); // 货到付款或银行汇款
        $pay_id = $pay_id ? $pay_id->toArray() : [];
        if ($order['extension_code'] != 'presale' && in_array($order['order_status'], [OS_UNCONFIRMED, OS_CONFIRMED]) && in_array($order['shipping_status'], [SS_UNSHIPPED, SS_PREPARING]) && $order['pay_status'] == PS_UNPAYED && !in_array($order['pay_id'], $pay_id)) {
            // 处理支付超时订单
            $pay_effective_time = intval(config('shop.pay_effective_time') ?? 0);
            if ($pay_effective_time > 0) {
                $pay_effective_time = $order['add_time'] + $pay_effective_time * 60;
                if ($pay_effective_time < $nowTime) {
                    $list['pay_effective_time'] = 0;
                } else {
                    $list['pay_effective_time'] = $pay_effective_time;
                }
            }
        }

        if (in_array($order['order_status'], [OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART, OS_RETURNED_PART, OS_ONLY_REFOUND])) {
            // 是否显示确认收货 handler_receive 0 不显示 1 显示
            $list['handler_receive'] = OrderStatusService::can_receive($order);
            if ($list['handler_receive'] == 1) {
                $list['handler'] = 2;// 确认收货
            } elseif ($order['shipping_status'] == SS_RECEIVED) {
                $list['handler'] = 4; // 已完成
            } else {
                if ($order['pay_status'] == PS_UNPAYED) {
                    $list['handler'] = 5; // 付款
                } else {
                    $list['handler'] = ''; // 查看订单
                }
            }
        } elseif ($order['order_status'] == OS_CANCELED) {
            $list['handler'] = 7; // 已取消
        } elseif ($order['order_status'] == OS_INVALID || $order['order_status'] == OS_RETURNED) {
            $list['handler'] = 8; // 无效
        } else {
            $list['handler'] = 6; // 已确认
        }

        // 是否回收站订单
        $list['is_delete'] = $order['is_delete'] ?? 0;

        // 是否显示取消订单  handler_cancel 0 不显示 1 显示
        $list['handler_cancel'] = OrderStatusService::can_cancel($order);
        if ($list['handler_cancel'] == 1) {
            $list['handler'] = 1; //取消订单
        }

        /* 退换货显示处理 start */
        $handler_return = OrderStatusService::can_return($order);

        // 判断发货日期起可退换货时间
        $sign_time = config('shop.sign', 0);
        if ($sign_time > 0) {

            $order_status = [OS_CANCELED, OS_INVALID, OS_RETURNED, OS_ONLY_REFOUND]; // 订单状态 已取消、已失效、已退款
            if (in_array($order['order_status'], $order_status) || in_array($order['pay_status'], [PS_REFOUND])) {
                $handler_return = 0;
            } else {

                $log_time = 0;
                if ($order['pay_status'] == PS_UNPAYED && $order['shipping_status'] == SS_RECEIVED) {
                    // 未付款、已确认收货 [银行转账 货到付款]
                    $log_time = $order['confirm_take_time'];
                } elseif ($order['pay_status'] == PS_PAYED) {
                    // 已付款
                    if (in_array($order['shipping_status'], [SS_SHIPPED, SS_SHIPPED_PART, OS_SHIPPED_PART])) {
                        // 已发货[含部分发货]
                        $log_time = $order['shipping_time'];
                    } elseif (in_array($order['shipping_status'], [SS_RECEIVED, SS_PART_RECEIVED])) {
                        // 已收货[含部分收货]
                        $log_time = $order['confirm_take_time'];
                    }
                }

                $handler_return = 0;
                if ($order['shipping_status'] == SS_UNSHIPPED) {
                    // 未发货 可申请售后
                    $handler_return = 1;
                } else {
                    $return_time = $log_time + $sign_time * 3600 * 24;
                    if (!empty($log_time) && $nowTime < $return_time) {
                        // 可申请售后
                        $handler_return = 1;
                    }
                }
            }
        }

        if (!empty($list)) {
            $order_goods = OrderDataHandleService::orderGoodsDataList($order['order_id'], '*', 1);
            $orderGoods = $order_goods[$order['order_id']] ?? [];

            $orderGoodsAttrIdList = BaseRepository::getKeyPluck($orderGoods, 'goods_attr_id');
            $orderGoodsAttrIdList = BaseRepository::getArrayUnique($orderGoodsAttrIdList);
            $orderGoodsAttrIdList = ArrRepository::getArrayUnset($orderGoodsAttrIdList);
            $productsGoodsAttrList = [];
            if ($orderGoodsAttrIdList) {
                $orderGoodsAttrIdList = BaseRepository::getImplode($orderGoodsAttrIdList);
                $productsGoodsAttrList = GoodsDataHandleService::getGoodsAttrDataList($orderGoodsAttrIdList, ['goods_attr_id', 'attr_img_flie', 'attr_gallery_flie']);
            }

            $goodsList = [];
            $total_number = 0;
            $goods_count = 0;
            $package_goods_count = 0;
            $package_list_total = 0;

            if ($orderGoods) {
                $goods_id = BaseRepository::getKeyPluck($orderGoods, 'goods_id');
                $goods = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'goods_thumb', 'goods_cause']);

                // 订单商品是否退换货
                $rec_id = BaseRepository::getKeyPluck($orderGoods, 'rec_id');
                $orderReturn = OrderReturnDataHandleService::OrderReturn($rec_id);

                foreach ($orderGoods as $k => $v) {

                    $v['get_goods'] = $goods[$v['goods_id']] ?? [];

                    // 订单商品信息
                    $goodsList[$k]['goods_number'] = $v['goods_number'];
                    $total_number += $v['goods_number'];
                    $subtotal = $v['goods_price'] * $v['goods_number'];

                    // 原商品信息
                    $v = $v['get_goods'] ? array_merge($v, $v['get_goods']) : $v;
                    $goodsList[$k]['goods_id'] = $v['goods_id'];
                    $goodsList[$k]['goods_name'] = $v['goods_name'];

                    //礼包统计
                    if ($v['extension_code'] == 'package_buy') {
                        /* 取得礼包信息 */
                        $package = $this->packageService->getPackageInfo($v['goods_id']);
                        $goodsList[$k]['goods_thumb'] = $package['activity_thumb'] ?? '';

                        $package['goods_list'] = $package['goods_list'] ?? [];
                        $goodsList[$k]['package_goods_list'] = $package['goods_list'];

                        $package_goods_count++;
                        if (!empty($package['goods_list'])) {
                            foreach ($package['goods_list'] as $package_goods_val) {
                                $package_list_total += ($package_goods_val['shop_price'] ?? 0) * $package_goods_val['goods_number'];
                            }
                        }

                        $goodsList[$k]['package_list_total'] = $package_list_total;
                        $goodsList[$k]['package_list_saving'] = $subtotal - $package_list_total;
                        $goodsList[$k]['format_package_list_total'] = $this->dscRepository->getPriceFormat($goodsList[$k]['package_list_total'], true, true, $goodsSelf);
                        $goodsList[$k]['format_package_list_saving'] = $this->dscRepository->getPriceFormat($goodsList[$k]['package_list_saving'], true, true, $goodsSelf);
                    } else {
                        $goods_count++;

                        $goodsList[$k]['goods_thumb'] = empty($v['goods_thumb']) ? '' : $this->dscRepository->getImagePath($v['goods_thumb']);
                    }

                    $goods_attr_id = $v['goods_attr_id'] ?? '';
                    $goods_attr_id = BaseRepository::getExplode($goods_attr_id);
                    $goodsList[$k]['goods_thumb'] = $this->goodsAttrService->cartGoodsAttrImage($goods_attr_id, $productsGoodsAttrList, $goodsList[$k]['goods_thumb']);

                    $goodsList[$k]['goods_price'] = $v['goods_price'];
                    $goodsList[$k]['goods_price_formated'] = $this->dscRepository->getPriceFormat($v['goods_price'], true, true, $goodsSelf);
                    $goodsList[$k]['goods_sn'] = $v['goods_sn'];

                    $goodsList[$k]['shop_name'] = $shopinfo['shop_name'] ?? '';
                    $goodsList[$k]['country_icon'] = $shopinfo['country_icon'] ?? '';
                    $goodsList[$k]['parent_id'] = $v['parent_id'];
                    $goodsList[$k]['goods_attr'] = $v['goods_attr'] ?: '';
                    $goodsList[$k]['is_gift'] = $v['is_gift'];
                    $goodsList[$k]['is_real'] = $v['is_real'];
                    if ($v['is_real'] == 0) {
                        $goodsList[$k]['virtual_goods'] = $this->userOrderService->get_virtual_goods_info($v['rec_id']);
                    }
                    $goodsList[$k]['extension_code'] = $v['extension_code'];
                    $goodsList[$k]['is_single'] = $v['is_single'];
                    $goodsList[$k]['freight'] = $v['freight'];
                    $goodsList[$k]['shipping_fee'] = $v['shipping_fee'];
                    $goodsList[$k]['commission_rate'] = $v['commission_rate'];

                    if (file_exists(MOBILE_DRP)) {
                        // 分销佣金
                        $goodsList[$k]['drp_money'] = $v['drp_money'];
                    }

                    // 订单商品申请售后
                    $order_goods_cause = app(GoodsCommonService::class)->getGoodsCause($v['get_goods']['goods_cause'] ?? '', $order, $v);
                    $goodsList[$k]['goods_handler_return'] = 0; // 退换货标识是否支持： 0 不支持 1 支持
                    if (!empty($order_goods_cause) && $order['is_delete'] == 0 && $handler_return == 1) {
                        $goodsList[$k]['goods_handler_return'] = 1;

                        //判断是否退换货过：0 未退 1 已退
                        $returnGoods = $orderReturn[$v['rec_id']] ?? [];
                        if (!empty($returnGoods)) {
                            $goodsList[$k]['is_refound'] = 1;
                            $goodsList[$k]['ret_id'] = $returnGoods['ret_id'];
                        } else {
                            $goodsList[$k]['is_refound'] = 0;
                            $goodsList[$k]['ret_id'] = 0;
                        }
                    }

                    $goodsList[$k]['rec_id'] = $v['rec_id'];
                }
            }

            $list['goods'] = $goodsList;
            $list['total_number'] = $total_number;
        }

        //延迟收货
        $list['delay'] = 0;
        if ($order['order_status'] == OS_SPLITED && $order['pay_status'] == PS_PAYED && $order['shipping_status'] == SS_SHIPPED) {
            $order_delay_day = config('shop.order_delay_day') * 86400;
            $auto_delivery_time = $order['auto_delivery_time'] * 86400;
            $shipping_time = $order['shipping_time'];

            if ($order_delay_day > (($auto_delivery_time + $shipping_time) - TimeRepository::getGmTime())) {
                $num = OrderDelayed::where('review_status', '<>', 1)->where('order_id', $list['order_id'])->count();

                if (config('shop.open_order_delay') == 1 && $num < config('shop.order_delay_num')) {
                    $list['delay'] = 1;
                }
            }
        }

        $delay_type = OrderDelayed::where('order_id', $list['order_id'])
            ->orderBy('delayed_id', 'DESC')
            ->value('review_status');
        if (isset($delay_type)) {
            if ($delay_type == 0) {
                $list['delay_type'] = $lang['is_confirm'][$delay_type];//"未审核";
            }
            if ($delay_type == 1) {
                $list['delay_type'] = $lang['is_confirm'][$delay_type];//"已审核";
            }
            if ($delay_type == 2) {
                $list['delay_type'] = $lang['is_confirm'][$delay_type];//"审核未通过";
            }
        } else {
            $list['delay_type'] = $lang['applied']; // 审请
        }

        if ($order['extension_code'] == 'presale') {//预售是否可支付尾款
            $list['presale_final_pay'] = 0;
            $time = TimeRepository::getGmTime();
            $presale = PresaleActivity::select('pay_start_time', 'pay_end_time')->where('act_id', $order['extension_id'])->first();
            $presale = $presale ? $presale->toArray() : [];
            if ($presale && $presale['pay_start_time'] <= $time && $presale['pay_end_time'] > $time) {
                $list['presale_final_pay'] = 1;//支付尾款时间内
            }
        }

        /* 获取订单门店信息  start */
        $stores = StoreOrder::select('id', 'store_id', 'pick_code', 'take_time')
            ->where('order_id', $args['order_id'])
            ->first();
        $stores = $stores ? $stores->toArray() : '';

        if (!empty($stores)) {
            $list['store_id'] = $stores['store_id'];
            $list['pick_code'] = $stores['pick_code'];
            $list['take_time'] = TimeRepository::getLocalStrtoTime($stores['take_time']) > 0 ? $stores['take_time'] : '';

            $offline_store = OfflineStore::from('offline_store as o')
                ->select('o.*', 'p.region_name as province', 'c.region_name as city', 'd.region_name as district')
                ->leftjoin('region as p', 'p.region_id', 'o.province')
                ->leftjoin('region as c', 'c.region_id', 'o.city')
                ->leftjoin('region as d', 'd.region_id', 'o.district')
                ->where('o.id', $list['store_id'])
                ->first();
            $offline_store = $offline_store ? $offline_store->toArray() : [];

            $list['offline_store'] = $offline_store;
        }

        /* 自提点信息 */
        if (!empty($order['point_id'])) {
            $point = ShippingPoint::where('id', $order['point_id'])->first();
            $point = $point ? $point->toArray() : [];
            if ($point) {
                $shipping_date_str = $order['shipping_date_str'] ?? '';
                $order['shipping_datestr'] = $shipping_date_str;
                $point['pickDate'] = TimeRepository::getLocalDate('Y', TimeRepository::getLocalStrtoTime($order['add_time'])) . '年' . $shipping_date_str;

                $list['point'] = $point;
            }
        }

        //验证拼团订单是否失败
        $list['failure'] = 0;
        if (file_exists(MOBILE_TEAM) && $list['team_id'] > 0) {
            $failure = $this->getTeamInfo($list['team_id'], $list['order_id']);
            $list['failure'] = $failure ?? 0;
        }

        // 团购支付保证金标识
        $list['is_group_deposit'] = 0;
        if ($order['extension_code'] == 'group_buy') {
            $group_buy = $this->groupBuyService->getGroupBuyInfo(['group_buy_id' => $order['extension_id']]);
            if (isset($group_buy) && $group_buy['deposit'] > 0 && $group_buy['is_finished'] == 0) {
                $list['is_group_deposit'] = 1;
            }
        }

        // 砍价
        if (file_exists(MOBILE_BARGAIN) && $order['extension_code'] == 'bargain_buy') {
            $bargain_info = \App\Models\BargainStatisticsLog::where('id', $order['extension_id'])->where('user_id', $order['user_id'])->select('id', 'bargain_id');
            $bargain_info = BaseRepository::getToArrayFirst($bargain_info);
            $list['extension_id'] = $bargain_info['id'] ?? 0;
            $list['bargain_id'] = $bargain_info['bargain_id'] ?? 0;
        }

        /*是否支持开发票*/
        $list['can_invoice'] = config('shop.can_invoice') ?? 0;

        return $list;
    }

    /**
     * 获取拼团信息,验证失败提示
     *
     * @param int $team_id
     * @param int $order_id
     * @return int
     */
    public function getTeamInfo($team_id = 0, $order_id = 0)
    {
        $time = TimeRepository::getGmTime();
        $info = TeamLog::from('team_log as tl')
            ->select('tg.team_num', 'tg.validity_time', 'tg.is_team', 'tl.start_time', 'tl.status', 'oi.order_status', 'oi.pay_status')
            ->leftjoin('order_info as oi', 'tl.team_id', '=', 'oi.team_id')
            ->leftjoin('team_goods as tg', 'tl.t_id', '=', 'tg.id')
            ->where('tl.team_id', $team_id)
            ->where('oi.order_id', $order_id)
            ->first();

        $team_info = $info ? $info->toArray() : [];

        $end_time = ($team_info['start_time'] + ($team_info['validity_time'] * 3600));

        if ($time < $end_time && $team_info['status'] == 1 && $team_info['pay_status'] != 2 && $team_info['order_status'] != 4) {
            //参团 ：拼团完成、未结束、未付款订单过期
            $failure = 1;
        } elseif ($time > $end_time && $team_info['status'] == 1 && $team_info['pay_status'] != 2 && $team_info['order_status'] != 4) {
            //参团 ：拼团结束，完成，未付款过期
            $failure = 1;
        } elseif (($time > $end_time || $time < $end_time) && $team_info['status'] != 1 && $team_info['order_status'] == 2) {
            //订单取消
            $failure = 1;
        } elseif ($time > $end_time && $team_info['status'] != 1 && $team_info['pay_status'] != 2 && $team_info['order_status'] != 2) {
            //未付款
            $failure = 1;
        } elseif ($team_info['status'] != 1 && ($time > $end_time || $team_info['is_team'] != 1)) {
            //开团：未成功
            $failure = 1;
        } else {
            $failure = 0;
        }
        return $failure;
    }


    /**
     * 订单列表数量
     * @param int $uid
     * @return array
     */
    public function orderNum($uid = 0)
    {
        return [
            'all' => $this->orderApiService->getOrderCount($uid, 0), //订单数量
            'nopay' => $this->orderApiService->getOrderCount($uid, 1), //待付款订单数量
            'nogoods' => $this->orderApiService->getOrderCount($uid, 2), //待收货订单数量
            'isfinished' => $this->orderApiService->getOrderCount($uid, 3), //已完成订单数量
            'isdelete' => $this->orderApiService->getOrderCount($uid, 4), //回收站订单数量
            'team_num' => TeamService::teamOrderNum($uid),
            'not_comment' => $this->orderCommentService->getOrderCommentCount($uid),  //待评价订单数量
            'return_count' => $this->orderApiService->getUserOrdersReturnCount($uid), //待同意状态退换货申请数量
        ];
    }

    /**
     * 确认订单收货
     *
     * @param int $user_id
     * @param int $order_id
     * @return array|bool
     * @throws \Exception
     */
    public function orderConfirm($user_id = 0, $order_id = 0)
    {
        try {
            $this->userOrderService->orderReceive($user_id, $order_id);
        } catch (HttpException $httpException) {
            Log::error($httpException->getMessage());
            return ['error' => 1, 'msg' => $httpException->getMessage()];
        }

        return true;
    }

    /**
     * 延迟收货申请
     * @param int $uid
     * @param int $order_id
     * @return mixed
     */
    public function orderDelay($uid = 0, $order_id = 0)
    {
        $lang = lang('user');

        $order = OrderInfo::where('order_id', $order_id)->first();

        // 验证订单所属
        if ($order->user_id != $uid) {
            $result['error'] = 1;
            $result['msg'] = $lang['not_your_order'];
            return $result;
        }

        //判断开关config['open_order_delay']
        if (config('shop.open_order_delay') != 1) {
            $result['error'] = 1;
            $result['msg'] = $lang['order_delayed_wrong'];
            return $result;
        }

        //判断订单状态
        if (!in_array($order->order_status, [OS_CONFIRMED, OS_SPLITED]) || $order->shipping_status != SS_SHIPPED) { //发货状态
            $result['error'] = 2;
            $result['msg'] = $lang['order_delayed_wrong'];
            return $result;
        }

        //判断时间config['order_delay_day']
        $nowTime = TimeRepository::getGmTime();
        $delivery_time = $order->shipping_time + 24 * 3600 * $order->auto_delivery_time;
        $order_delay_day = config('shop.order_delay_day') > 0 ? intval(config('shop.order_delay_day')) : 3;//如无配置，最多可提前3天申请
        $order_delay_num = config('shop.order_delay_num') > 0 ? intval(config('shop.order_delay_num')) : 3;//如无配置，最多可申请3次
        if (($nowTime > $delivery_time || ($delivery_time - $nowTime) / 86400 > $order_delay_day)) {
            $result['error'] = 1;
            $result['msg'] = sprintf(lang('user.order_delay_day_desc'), $order_delay_day);
            return $result;
        }

        /**
         * 延迟收货和pc统一
         */

        //判断该单号申请次数,如无配置，最多可申请3次，config['order_delay_num'] review_status=1通过审核 2未通过
        $apply_count = OrderDelayed::where('order_id', $order_id)->count();
        if ($apply_count < $order_delay_num) {

            // 判断是否有未审核的申请
            $no_review = OrderDelayed::where('order_id', $order_id)->where('review_status', 0)->count();
            if ($no_review > 0) {
                return ['error' => 1, 'msg' => $lang['not_audit_applications']];
            }

            $action_log = [
                'order_id' => $order_id,
                'apply_time' => $nowTime,
                'apply_day' => 1
            ];
            OrderDelayed::insertGetId($action_log);

            return ['error' => 0, 'msg' => $lang['application_is_successful']];
        } else {
            return ['error' => 1, 'msg' => $lang['much_applications']];
        }
    }

    /**
     * 取消订单
     * @param $args
     * @return mixed
     * 订单状态只能是“未确认”或“已确认”
     * 发货状态只能是“未发货”
     * 如果付款状态是“已付款”、“付款中”，不允许取消，要取消和商家联系
     * @throws \Exception
     */
    public function orderCancel($args)
    {
        $order = OrderInfo::where('user_id', $args['uid'])
            ->where('pay_status', '<>', PS_PAYED)
            ->where('order_id', $args['order_id']);

        $order = $order->first();

        $order = $order ? $order->toArray() : [];

        if (empty($order)) {
            return ['error' => 1, 'msg' => lang('user.order_exist')];
        }

        if ($order['user_id'] != $args['uid']) {
            return ['error' => 1, 'msg' => lang('user.no_priv')];
        }

        // 订单状态只能是“未确认”或“已确认”
        if ($order['order_status'] != OS_UNCONFIRMED && $order['order_status'] != OS_CONFIRMED) {
            return ['error' => 1, 'msg' => lang('user.current_os_not_unconfirmed')];
        }
        // 发货状态只能是“未发货”
        if ($order['shipping_status'] != SS_UNSHIPPED) {
            return ['error' => 1, 'msg' => lang('user.current_ss_not_cancel')];
        }
        // 如果付款状态是“已付款”、“付款中”，不允许取消，要取消和商家联系
        if ($order['pay_status'] != PS_UNPAYED) {
            return ['error' => 1, 'msg' => lang('user.current_ps_not_cancel')];
        }

        $res = $this->orderApiService->orderCancel($order);

        return $res;
    }

    /**
     * 删除订单
     *
     * @param array $args
     * @return mixed
     */
    public function orderDelete($args = [])
    {
        $order = OrderInfo::where('user_id', $args['uid'])
            ->where('order_id', $args['order_id'])
            ->first();

        if ($order->is_delete == 1) {
            $is_delete = 2;

            //隐藏会员查看订单
            $order->is_delete = $is_delete;

            if ($order->main_count > 0) {
                OrderInfo::where('main_order_id', $order->order_id)->update(['is_delete' => $is_delete]);
            }
        } else {
            //放入订单回收站
            $order->is_delete = 1;
        }

        $res = $order->save();

        if ($res) {
            OrderCommonService::getUserOrderNumServer($args['uid']);
        }

        return $res;
    }

    /**
     * 订单还原
     *
     * @param $args
     * @return mixed
     */
    public function orderRestore($args = [])
    {
        $order = OrderInfo::where('user_id', $args['uid'])
            ->where('order_id', $args['order_id'])
            ->first();

        $order->is_delete = 0;

        if ($order->main_count > 0) {
            OrderInfo::where('main_order_id', $order->order_id)->update(['is_delete' => 0]);
        }

        $res = $order->save();

        if ($res) {
            OrderCommonService::getUserOrderNumServer($args['uid']);
        }

        return $res;
    }

    /**
     * 获取地区名称
     * @param int $regionId
     * @return mixed
     */
    public function getRegionName($regionId = 0)
    {
        if (empty($regionId)) {
            return '';
        }

        $regionName = Region::where('region_id', $regionId)->value('region_name');

        return $regionName ?? '';
    }

    /**
     * 获取储值卡使用金额及储值卡id
     * @param $order_id
     * @return $res
     */
    public function value_card_record($order_id)
    {
        $res = ValueCardRecord::select('use_val', 'vc_id')->where('order_id', $order_id)->first();
        $res = $res ? $res->toArray() : [];
        if ($res) {
            $res['use_val'] = $this->dscRepository->getPriceFormat($res['use_val']);
        }

        return $res;
    }

    /**
     * 获取发货单信息H5
     * @param string $delivery_sn
     * @return mixed
     */
    public function getTrackerOrderInfo($delivery_sn = '')
    {
        $deliver_order = DeliveryOrder::select('delivery_id', 'order_id', 'delivery_sn', 'invoice_no', 'shipping_id', 'shipping_name', 'express_code')
            ->where('delivery_sn', $delivery_sn)->with(['getDeliveryGoods' => function ($query) {
                $query->select('delivery_id', 'goods_id');
                $query->with(['getGoods' => function ($query) {
                    $query->select('goods_id', 'goods_thumb');
                }]);
            }, 'getShipping' => function ($query) {
                $query->select('shipping_id', 'shipping_code');
            }]);

        $deliver_order = BaseRepository::getToArrayGet($deliver_order);

        $arr = [];
        if ($deliver_order) {
            foreach ($deliver_order as $key => $row) {
                $arr[$key]['invoice_no'] = $row['invoice_no'] ?? '';//订单物流单号
                $arr[$key]['shipping_name'] = $row['shipping_name'] ?? '';//快递名称
                $arr[$key]['order_id'] = $row['order_id'] ?? '';
                $shipping_code = $row['get_shipping']['shipping_code'] ?? '';
                if ($shipping_code) {
                    $shippingObject = CommonRepository::shippingInstance($shipping_code);
                    if (!is_null($shippingObject)) {
                        $arr[$key]['shipping_code'] = $shippingObject->get_code_name();
                    }
                }

                if (!empty($row['express_code']) && $row['express_code'] != 'undefined') {
                    $arr[$key]['shipping_code'] = $row['express_code'];
                }

                //订单商品图片
                $img = [];
                if ($row['get_delivery_goods']) {
                    foreach ($row['get_delivery_goods'] as $k => $del) {
                        $img[$k]['goods_img'] = $this->dscRepository->getImagePath($del['get_goods']['goods_thumb'] ?? '');
                    }
                }
                $arr[$key]['img'] = $img;
            }
        }

        return $arr;
    }
}
