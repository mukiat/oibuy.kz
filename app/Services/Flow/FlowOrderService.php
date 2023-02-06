<?php

namespace App\Services\Flow;

use App\Models\AutoSms;
use App\Models\BonusType;
use App\Models\Cart;
use App\Models\Coupons;
use App\Models\CouponsUser;
use App\Models\Goods;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\PayLog;
use App\Models\Payment;
use App\Models\SeckillGoods;
use App\Models\Shipping;
use App\Models\UserBonus;
use App\Models\ValueCard;
use App\Models\ValueCardRecord;
use App\Models\ValueCardType;
use App\Models\VirtualCard;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CalculateRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Category\CategoryService;
use App\Services\Coupon\CouponDataHandleService;
use App\Services\Coupon\CouponsUserService;
use App\Services\Cron\CronService;
use App\Services\Erp\JigonManageService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Order\OrderCommonService;
use App\Services\Order\OrderDataHandleService;
use App\Services\Order\OrderTransportService;
use App\Services\Shipping\ShippingService;
use App\Services\ValueCard\ValueCardDataHandleService;

class FlowOrderService
{
    protected $orderTransportService;
    protected $couponsUserService;
    protected $dscRepository;
    protected $commonRepository;
    protected $sessionRepository;
    protected $merchantCommonService;
    protected $orderCommonService;
    protected $categoryService;

    public function __construct(
        OrderTransportService $orderTransportService,
        CouponsUserService $couponsUserService,
        DscRepository $dscRepository,
        CommonRepository $commonRepository,
        SessionRepository $sessionRepository,
        MerchantCommonService $merchantCommonService,
        OrderCommonService $orderCommonService,
        CategoryService $categoryService
    )
    {
        $this->orderTransportService = $orderTransportService;
        $this->couponsUserService = $couponsUserService;
        $this->dscRepository = $dscRepository;
        $this->commonRepository = $commonRepository;
        $this->sessionRepository = $sessionRepository;
        $this->merchantCommonService = $merchantCommonService;
        $this->orderCommonService = $orderCommonService;
        $this->categoryService = $categoryService;
    }

    /**
     * 分单插入数据
     *
     * @param int $order_id
     * @param int $user_id
     * @return bool
     * @throws \Exception
     */
    public function OrderSeparateBill($order_id = 0, $user_id = 0)
    {

        /* 主订单信息 */
        $row = OrderInfo::where('order_id', $order_id)->where('user_id', $user_id);
        $row = BaseRepository::getToArrayFirst($row);

        if (empty($row)) {
            return false;
        }

        /* 更新子订单信息 */
        $confirm_time = $row['confirm_time'];
        $pay_time = $row['pay_time'];
        $order_status = $row['order_status'];
        $pay_status = $row['pay_status'];

        $pay_code = Payment::where('pay_id', $row['pay_id'])->value('pay_code');
        $pay_code = $pay_code ? $pay_code : '';

        $is_paid = 0;
        $child_show = 0;
        if ($row['pay_status'] == PS_PAYED || $pay_code == 'cod' || $pay_code == 'bank') {

            $child_show = 1;

            if ($row['pay_status'] == PS_PAYED) {
                $is_paid = 1;
            }

            /* 会员中心不显示主订单 */
            if ($pay_code == 'cod' || $pay_code == 'bank') {
                OrderInfo::where('order_id', $order_id)->where('user_id', $user_id)->update([
                    'main_pay' => 0
                ]);
            }
        }

        /* 主订单商品 */
        $separateOrderGoodsList = $this->separateOrderGoods($order_id, $user_id);

        $newInfo = BaseRepository::getGroupBy($separateOrderGoodsList, 'ru_id');
        $surplus = $row['surplus']; //主订单余额
        $integral_money = $row['integral_money']; //主订单积分金额
        $main_pay_fee = $row['pay_fee']; //主订单支付手续费
        $main_goods_amount = $row['goods_amount']; //主订单商品金额
        $uc_id = $row['uc_id']; //优惠券ID
        $shipping_id = $row['shipping_id']; //主订单配送方式
        $shipping_id = $this->mainOrderShippingList($shipping_id);

        $consignee = [
            'country' => $row['country'],
            'province' => $row['province'],
            'city' => $row['city'],
            'district' => $row['district'],
            'street' => $row['street']
        ];
        $mainShippingList = app(ShippingService::class)->goodsShippingTransport($separateOrderGoodsList, $consignee, $row['uc_id'], $shipping_id);
        /* 获取订单的运费 end */

        //是否开启下单自动发短信、邮件 start
        $auto_sms = app(CronService::class)->getSmsOpen();

        /**
         * 获取主订单储值卡使用金额
         */
        $cartUseVal = ValueCardRecord::select('vc_id', 'use_val', 'vc_dis')
            ->where('order_id', $order_id);
        $cartUseVal = BaseRepository::getToArrayFirst($cartUseVal);
        $vc_id = $cartUseVal ? $cartUseVal['vc_id'] : 0;
        $use_val = $cartUseVal ? $cartUseVal['use_val'] : 0;
        $useValAll = $this->dscRepository->changeFloat($use_val);
        $vc_dis = $cartUseVal ? $cartUseVal['vc_dis'] : 0;
        $vc_dis_money = $row['vc_dis_money'] ?? 0;
        $vc_dis_money = $this->dscRepository->changeFloat($vc_dis_money);

        $order = $row;
        $order['vc_id'] = $vc_id;

        /* 排除超值礼包 */
        $sql = [
            'where' => [
                [
                    'name' => 'extension_code',
                    'value' => 'package_buy',
                    'condition' => '<>'
                ]
            ]
        ];
        $order_goods = BaseRepository::getArraySqlGet($separateOrderGoodsList, $sql);

        //满足储值卡的商品 start
        $valueCardGoodsList = $this->cardOrderGoodsList($order_goods, $order);
        $valueCardGoodsTotal = BaseRepository::getArraySum($valueCardGoodsList, ['goods_price', 'goods_number']);

        $valueCardGoodsBonus = BaseRepository::getArraySum($valueCardGoodsList, 'goods_bonus'); //促销活动均摊金额
        $valueCardGoodsCoupons = BaseRepository::getArraySum($valueCardGoodsList, 'goods_coupons'); //促销活动均摊金额
        $valueCardGoodsFavourable = BaseRepository::getArraySum($valueCardGoodsList, 'goods_favourable'); //促销活动均摊金额
        $valueCardDisAmount = BaseRepository::getArraySum($valueCardGoodsList, 'dis_amount'); //商品阶梯优惠金额
        //满足储值卡的商品 end

        // 主订单购买权益卡金额与折扣
        $order_membership_card = [];
        if (file_exists(MOBILE_DRP)) {
            $order_membership_card = \App\Plugins\UserRights\Discount\Services\DiscountRightsService::getOrderInfoMembershipCard($order_id, $row['user_id']);
        }

        $shipping_id = $row['shipping_id'];
        $shipping_name = $row['shipping_name'];
        $shipping_code = $row['shipping_code'];
        $shipping_type = $row['shipping_type'];
        $postscript_desc = $row['postscript'];

        $flow_type = intval(CART_GENERAL_GOODS);

        $orderCouponsList = $this->getUserOrderCoupons($order_id, $uc_id);

        $ru_id = BaseRepository::getArrayKeys($newInfo);
        $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

        $order_goods = [];
        $recCardList = []; //订单商品rec_id
        $i = 0;
        foreach ($newInfo as $key => $info) {
            $i += 1;
            $order_goods[$key] = $info;

            /* 店铺运费 */
            $shipping = $this->sellerShippingOrder($key, $shipping_id, $shipping_name, $shipping_code, $shipping_type);
            $row['shipping_type'] = $shipping['shipping_type'];
            $row['shipping_id'] = $mainShippingList[$key]['default_shipping']['shipping_id'] ?? 0;
            $row['shipping_name'] = $mainShippingList[$key]['default_shipping']['shipping_name'] ?? '';
            $row['shipping_code'] = $mainShippingList[$key]['default_shipping']['shipping_code'] ?? '';
            $row['shipping_fee'] = $mainShippingList[$key]['default_shipping']['shipping_fee'] ?? 0;
            $row['shipping_fee'] = $this->dscRepository->changeFloat($row['shipping_fee']);

            $postscript = explode(',', $postscript_desc);//留言分单
            $row['postscript'] = '';
            if ($postscript) {
                foreach ($postscript as $postrow) {
                    $postrow = explode('|', $postrow);
                    if ($postrow[0] == $key) {//$key是商家ID
                        $row['postscript'] = $postrow[1] ?? '';
                    }
                }
            }

            // 插入订单表 start
            $order_sn = $this->getOrderChildSn(); //获取新订单号
            $row['order_sn'] = $order_sn;

            if (empty(config('queue.default')) || config('queue.default') == 'sync') {
                session([
                    'order_done_sn' => $row['order_sn']
                ]);
            }

            $row['main_order_id'] = $order_id; //获取主订单ID

            /* 扣除商品阶梯优惠金额 */
            $goods_amount = BaseRepository::getArraySum($info, ['goods_number', 'goods_price']);
            $dis_amount = BaseRepository::getArraySum($info, ['dis_amount']);

            $row['goods_amount'] = $goods_amount - $dis_amount; //商品总金额

            //订单应付金额
            $row['order_amount'] = $row['goods_amount'] + $row['shipping_fee'];

            $cartRecId = BaseRepository::getKeyPluck($valueCardGoodsList, 'rec_id');

            $sql = [
                'whereIn' => [
                    [
                        'name' => 'rec_id',
                        'value' => $cartRecId
                    ]
                ]
            ];
            $cardGoodsList = BaseRepository::getArraySqlGet($order_goods[$key], $sql);
            $card_order_amount = BaseRepository::getArraySum($cardGoodsList, ['goods_price', 'goods_number']);

            /* 均摊支付费用 */
            $row['pay_fee'] = ($row['goods_amount'] / $main_goods_amount) * $main_pay_fee;

            /* 加支付费用金额 */
            $row['order_amount'] += $row['pay_fee'];

            if (CROSS_BORDER === true) { // 跨境多商户
                $row['order_amount'] += 0;
            }

            /* 处理订单折扣 */
            $sql = [
                'where' => [
                    [
                        'name' => 'ru_id',
                        'value' => $key
                    ]
                ]
            ];
            $orderGoodsList = BaseRepository::getArraySqlGet($separateOrderGoodsList, $sql);
            $row['discount'] = BaseRepository::getArraySum($orderGoodsList, 'goods_favourable');
            $row['order_amount'] -= $row['discount'];

            /* 减去优惠券金额 start */
            $couponsInfo = [];
            if ($orderCouponsList) {
                $sql = [
                    'where' => [
                        [
                            'name' => 'ru_id',
                            'value' => $key
                        ]
                    ]
                ];
                $couponsInfo = BaseRepository::getArraySqlFirst($orderCouponsList, $sql);
            }

            if (!empty($couponsInfo)) {
                $row['coupons'] = $couponsInfo['uc_money'] ?? 0;
                $row['uc_id'] = $couponsInfo['uc_id'] ?? 0;
            } else {
                $row['coupons'] = 0;
                $row['uc_id'] = '';
            }

            if ($row['coupons'] > 0) {
                if ($row['order_amount'] >= $row['coupons']) {
                    $row['order_amount'] -= $row['coupons'];
                } else {
                    $row['coupons'] = $row['order_amount'];
                    $row['order_amount'] = 0;
                }
            }
            /* 减去优惠券金额 end */

            /* 减去红包金额 start */
            $sql = [
                'where' => [
                    [
                        'name' => 'ru_id',
                        'value' => $key
                    ]
                ]
            ];
            $orderGoodsList = BaseRepository::getArraySqlGet($separateOrderGoodsList, $sql);
            $row['bonus'] = BaseRepository::getArraySum($orderGoodsList, 'goods_bonus');
            $row['order_amount'] -= $row['bonus'];
            /* 减去红包金额 end */

            //积分 start
            if ($row['order_amount'] > 0 && $integral_money > 0) {

                //子订单商品可支付积分金额
                $integral_ratio = $this->getIntegralRatio($order_id, $key);

                //当总积分金额大于店铺订单的积分可用金额
                if ($integral_ratio > 0) {
                    if ($integral_money >= $integral_ratio && $row['order_amount'] > $integral_ratio) {

                        /* 当总积分金额大于店铺订单的积分可用金额并且订单金额大于可用积分金额 */
                        $integral_money -= $integral_ratio;
                        $row['order_amount'] -= $integral_ratio;
                        $row['integral_money'] = $integral_ratio;
                        $row['integral'] = $this->dscRepository->integralOfValue($integral_ratio);
                    } else {
                        if ($integral_money > $row['order_amount']) {
                            $integral_money -= $row['order_amount'];
                            $row['integral_money'] = $row['order_amount'];
                            $row['integral'] = $this->dscRepository->integralOfValue($row['order_amount']);

                            $row['order_amount'] = 0;
                        } else {
                            $row['order_amount'] -= $integral_money;
                            $row['integral_money'] = $integral_money;
                            $row['integral'] = $this->dscRepository->integralOfValue($integral_money);

                            $integral_money = 0;
                        }
                    }
                } else {
                    $row['integral_money'] = 0;
                    $row['integral'] = 0;
                }
            } else {
                $integral_money = 0;
                $row['integral_money'] = 0;
                $row['integral'] = 0;
            }
            //积分 end

            /* 税额 */
            $row['tax'] = CommonRepository::orderInvoiceTotal($row['goods_amount'], $row['inv_content']);
            $row['order_amount'] += $row['tax'];

            if (CROSS_BORDER === true) { // 跨境多商户
                $row['rate_fee'] = 0;
            }

            // 开通购买会员权益卡 应付金额 = 原应付金额 - 折扣差价 + 购买权益卡金额
            if (file_exists(MOBILE_DRP) && !empty($order_membership_card)) {
                if ($order_membership_card['membership_card_id'] > 0) {
                    $row['order_amount'] = $row['order_amount'] - $order_membership_card['membership_card_discount_price'] + $order_membership_card['membership_card_buy_money'];
                }
            }

            /* 储值卡start */
            $sql = [
                'where' => [
                    [
                        'name' => 'ru_id',
                        'value' => $key
                    ]
                ]
            ];
            $valueCardGoods = BaseRepository::getArraySqlGet($valueCardGoodsList, $sql);

            /* 商家储值卡商品总额 */
            $ruGoodsTotal = BaseRepository::getArraySum($valueCardGoods, ['goods_price', 'goods_number']);
            $ruCardDisAmount = BaseRepository::getArraySum($valueCardGoods, 'dis_amount');
            $ruGoodsTotal = $ruGoodsTotal - $ruCardDisAmount;
            $ruGoodsTotal = $ruGoodsTotal > 0 ? $ruGoodsTotal : 0;

            $cartValOther = [];
            $ruVcDisMoney = 0;
            $payValCard = 0;

            $row['vc_dis_money'] = 0;
            if (!empty($valueCardGoods) && ($useValAll > 0 || $vc_dis_money > 0)) {

                $goods_bonus = BaseRepository::getArraySum($cardGoodsList, 'goods_bonus');
                $goods_coupons = BaseRepository::getArraySum($cardGoodsList, 'goods_coupons');
                $goods_favourable = BaseRepository::getArraySum($cardGoodsList, 'goods_favourable');
                $goodsDisAmount = BaseRepository::getArraySum($cardGoodsList, 'dis_amount');

                $vcDisOrderAmount = $card_order_amount - $goods_bonus - $goods_coupons - $goods_favourable - $goodsDisAmount;
                $vcDisGoodsTotal = $valueCardGoodsTotal - $valueCardGoodsBonus - $valueCardGoodsCoupons - $valueCardGoodsFavourable - $valueCardDisAmount;
                $vcDisGoodsTotal = $vcDisGoodsTotal > 0 ? $vcDisGoodsTotal : 0;

                if ($vc_dis_money > 0) {
                    $ruVcDisMoney = ($vcDisOrderAmount / $vcDisGoodsTotal) * $vc_dis_money;
                    $ruVcDisMoney = $this->dscRepository->changeFloat($ruVcDisMoney);

                    if ($ruVcDisMoney > 0) {
                        if ($ruVcDisMoney > $row['order_amount']) {
                            $row['order_amount'] = 0;
                        } else {
                            $row['order_amount'] -= $ruVcDisMoney;
                        }

                        $cartValOther['vc_dis_money'] = $ruVcDisMoney;
                        $row['vc_dis_money'] = $ruVcDisMoney;

                        $cartValOther['use_val'] = 0;
                        $cartValOther['vc_id'] = $vc_id;
                        $cartValOther['vc_dis'] = $vc_dis;
                    }
                } else {
                    $cartValOther['vc_dis_money'] = 0;
                }

                if ($useValAll > 0) {

                    /* 查询储值卡支付商品金额 */
                    $ruCardOrderAmount = $card_order_amount - $goods_bonus - $goods_coupons - $goods_favourable - $goodsDisAmount - $row['vc_dis_money'] + $row['shipping_fee'];

                    $ruCardOrderAmount = $ruCardOrderAmount > 0 ? $ruCardOrderAmount : 0;
                    $ruCardOrderAmount = $this->dscRepository->changeFloat($ruCardOrderAmount);

                    $row['order_amount'] = $this->dscRepository->changeFloat($row['order_amount']);

                    if ($useValAll > $ruCardOrderAmount) {
                        $useValAll -= $ruCardOrderAmount;
                    } else {
                        $ruCardOrderAmount = $useValAll;
                        $useValAll = 0;
                    }

                    $payValCard = $ruCardOrderAmount;
                    $payValCard = $this->dscRepository->changeFloat($payValCard);

                    if ($row['order_amount'] > $payValCard) {
                        $row['order_amount'] -= $payValCard;
                    } else {
                        $row['order_amount'] = 0;
                    }

                    if ($payValCard > 0) {
                        $cartValOther['use_val'] = $payValCard;
                        $cartValOther['vc_id'] = $vc_id;
                        $cartValOther['vc_dis'] = $vc_dis;
                    }
                }
            }

            $row['vc_money'] = $cartValOther['use_val'] ?? 0;
            /* 储值卡end */

            //余额 start
            if ($surplus > 0) {
                if ($surplus >= $row['order_amount']) {
                    $surplus = $surplus - $row['order_amount'];
                    $row['surplus'] = $row['order_amount']; //订单金额等于当前使用余额
                    $row['order_amount'] = 0;
                } else {
                    $row['order_amount'] = $row['order_amount'] - $surplus;
                    $row['surplus'] = $surplus;
                    $surplus = 0;
                }
            } else {
                $row['surplus'] = 0;
            }
            //余额 end

            $row['order_amount'] = number_format($row['order_amount'], 2, '.', ''); //格式化价格为一个数字

            /* 如果订单金额为0（使用余额或积分或红包支付），修改订单状态为已确认、已付款，同时主订单也要是已付款 */
            $row['order_status'] = $order_status;
            $row['pay_status'] = $pay_status;
            $row['confirm_time'] = $confirm_time;
            $row['pay_time'] = $pay_time;
            $row['child_show'] = $child_show;

            $money_paid = $row['order_amount'];

            /* 更新在线支付（微信、支付宝等支付方式） */
            if ($row['pay_status'] == PS_PAYED) {
                $row['money_paid'] = $row['order_amount'];
                $row['order_amount'] = 0;
            }

            unset($row['order_id']);
            //商家---剔除自提点信息
            if ($row['shipping_code'] != 'cac') {
                $row['point_id'] = 0;
                $row['shipping_dateStr'] = '';
            }

            $row['main_count'] = 0;

            //商家ID
            $row['ru_id'] = $key;

            $new_orderId = $this->AddToOrder($row);

            /* 记录分单订单使用储值卡 */
            if ($cartValOther && $new_orderId > 0) {
                $cartValOther['order_id'] = $new_orderId;
                $cartValOther['record_time'] = TimeRepository::getGmTime();
                $cartValOther['change_desc'] = sprintf(lang('order.pay_order_sn'), $row['order_sn']);
                ValueCardRecord::insert($cartValOther);
            }

            if ($new_orderId > 0) {

                if ($row['uc_id'] > 0) {
                    CouponsUser::where('is_delete', 0)->where('uc_id', $row['uc_id'])->update([
                        'order_id' => $new_orderId
                    ]);
                }

                /* 如果需要，发短信 */
                if ($key == 0) {
                    $sms_shop_mobile = config('shop.sms_shop_mobile'); //手机
                } else {
                    $sms_shop_mobile = $merchantList[$key]['mobile'] ?? '';
                }

                /* 给商家发短信 */
                if (config('shop.sms_order_placed') == 1 && $sms_shop_mobile != '') {
                    if (!empty($auto_sms)) {
                        $other = [
                            'item_type' => 1,
                            'user_id' => $row['user_id'],
                            'ru_id' => $key,
                            'order_id' => $new_orderId,
                            'add_time' => TimeRepository::getGmTime()
                        ];
                        AutoSms::insert($other);
                    } else {
                        $shop_name = $merchantList[$key]['shop_name'] ?? '';
                        $order_region = $this->getFlowOrderUserRegion($new_orderId);
                        //阿里大鱼短信接口参数
                        $smsParams = [
                            'shop_name' => $shop_name,
                            'shopname' => $shop_name,
                            'order_sn' => $row['order_sn'],
                            'ordersn' => $row['order_sn'],
                            'consignee' => $row['consignee'],
                            'order_region' => $order_region,
                            'orderregion' => $order_region,
                            'address' => $row['address'],
                            'order_mobile' => $row['mobile'],
                            'ordermobile' => $row['mobile'],
                            'mobile_phone' => $sms_shop_mobile,
                            'mobilephone' => $sms_shop_mobile
                        ];

                        $this->commonRepository->smsSend($sms_shop_mobile, $smsParams, 'sms_order_placed');
                    }
                }

                $cost_amount = 0;
                $order_goods[$key] = array_values($order_goods[$key]);

                $drp_money = 0;
                $goods_card_money = 0;
                $goods_value_card_discount = 0;
                $value_card_discount_list = [];
                $value_card_list = [];
                for ($j = 0; $j < count($order_goods[$key]); $j++) {
                    $drp_money += $order_goods[$key][$j]['drp_money'];

                    $order_goods[$key][$j]['order_id'] = $new_orderId;
                    unset($order_goods[$key][$j]['rec_id']);
                    $order_goods[$key][$j]['goods_name'] = addslashes($order_goods[$key][$j]['goods_name']);
                    $order_goods[$key][$j]['goods_attr'] = addslashes($order_goods[$key][$j]['goods_attr']);

                    unset($order_goods[$key][$j]['get_goods']);

                    $order_goods[$key][$j] = BaseRepository::getArrayfilterTable($order_goods[$key][$j], 'order_goods');

                    /* 均摊储值卡金额 */
                    $goods = [];
                    if (!empty($valueCardGoods)) {
                        $sql = [
                            'where' => [
                                [
                                    'name' => 'goods_id',
                                    'value' => $order_goods[$key][$j]['goods_id']
                                ]
                            ]
                        ];
                        $goods = BaseRepository::getArraySqlFirst($valueCardGoods, $sql);

                        if ($goods) {
                            $goodsTotal = $goods['goods_price'] * $goods['goods_number'] - $goods['dis_amount'];
                            $goodsTotal = $goodsTotal > 0 ? $goodsTotal : 0;

                            /* 均摊储值卡折扣 */
                            $order_goods[$key][$j]['value_card_discount'] = ($goodsTotal / $ruGoodsTotal) * $ruVcDisMoney;
                            $order_goods[$key][$j]['value_card_discount'] = $this->dscRepository->changeFloat($order_goods[$key][$j]['value_card_discount']);

                            $value_card_discount_list[$j]['value_card_discount'] = $order_goods[$key][$j]['value_card_discount'];
                            $goods_value_card_discount += $order_goods[$key][$j]['value_card_discount'];

                            /* 均摊储值卡使用金额 */
                            $order_goods[$key][$j]['goods_value_card'] = ($goodsTotal / $ruGoodsTotal) * $payValCard;
                            $order_goods[$key][$j]['goods_value_card'] = $this->dscRepository->changeFloat($order_goods[$key][$j]['goods_value_card']);

                            $value_card_list[$j]['goods_value_card'] = $order_goods[$key][$j]['goods_value_card'];
                            $goods_card_money += $order_goods[$key][$j]['goods_value_card'];
                        }
                    }

                    $rec_id = OrderGoods::insertGetId($order_goods[$key][$j]);

                    if (!empty($goods) && $order_goods[$key][$j]['extension_code'] != 'package_buy') {
                        $value_card_list[$j]['rec_id'] = $rec_id;
                        $value_card_discount_list[$j]['rec_id'] = $rec_id;

                        $recCardList[$key][$j] = [
                            'rec_id' => $rec_id,
                            'cart_recid' => $order_goods[$key][$j]['cart_recid']
                        ];
                    }

                    /* 虚拟卡 */
                    $virtual_goods = $this->getVirtualGoods($new_orderId);

                    if ($virtual_goods && $flow_type != CART_GROUP_BUY_GOODS && $row['order_amount'] <= 0) {
                        /* 虚拟卡发货 */
                        if ($this->orderVirtualGoodsShip($virtual_goods, $new_orderId, $order_sn)) {

                            /* 如果没有实体商品，修改发货状态，送积分和红包 */
                            $count = OrderGoods::where('order_id', $new_orderId)
                                ->where('is_real', 1)
                                ->count();

                            if ($count <= 0) {
                                /* 修改订单状态 */
                                OrderInfo::where('order_id', $new_orderId)->update([
                                    'shipping_status' => SS_RECEIVED,
                                    'shipping_time' => TimeRepository::getGmTime()
                                ]);
                            }
                        }
                    }

                    $cost_price = $order_goods[$key][$j]['cost_price'] ?? 0;
                    $cost_amount += $cost_price * $order_goods[$key][$j]['goods_number'];
                }

                /* 核对均摊储值卡商品金额 */
                if (!empty($value_card_list) && $payValCard > 0) {
                    $value_card_list = BaseRepository::getSortBy($value_card_list, 'cart_recid');
                    CommonRepository::collateOrderValueCard($value_card_list, $payValCard, $goods_card_money);
                }

                /* 核对均摊储值卡折扣商品金额 */
                if (!empty($value_card_discount_list) && $ruVcDisMoney > 0) {
                    $value_card_discount_list = BaseRepository::getSortBy($value_card_discount_list, 'cart_recid');
                    CommonRepository::collateOrderValueCardDiscount($value_card_discount_list, $ruVcDisMoney, $goods_value_card_discount);
                }

                $data = [
                    'cost_amount' => $cost_amount
                ];

                /* 更新订单非分销状态 */
                if (file_exists(MOBILE_DRP)) {
                    if (empty($drp_money) && $row['is_drp'] == 1) {
                        $data['is_drp'] = 0;
                    }
                }

                //更新子订单成本价格
                OrderInfo::where('order_id', $new_orderId)->update($data);

                /* 插入支付日志 */
                $row['log_id'] = $this->insertPayLog($new_orderId, $money_paid, PAY_ORDER, $is_paid);

                // 记录子订单操作记录
                $this->orderCommonService->orderAction($row['order_sn'], $row['order_status'], $row['shipping_status'], $row['pay_status'], lang('common.main_order_pay'), lang('common.buyer'));

                if ($row['pay_status'] == PS_PAYED) {
                    app(JigonManageService::class)->jigonConfirmOrder($new_orderId); // 贡云确认订单
                }
            }
        }

        if ($order_id > 0 && !empty($recCardList)) {
            $recCardList = ArrRepository::getArrCollapse($recCardList);

            $rec_id = BaseRepository::getKeyPluck($recCardList, 'rec_id');
            $orderGoodsCardList = OrderDataHandleService::orderGoodsDataList($rec_id, ['rec_id', 'cart_recid', 'goods_value_card', 'value_card_discount']);

            foreach ($orderGoodsCardList as $k => $v) {
                OrderGoods::where('order_id', $order_id)->where('cart_recid', $v['cart_recid'])->update([
                    'goods_value_card' => $v['goods_value_card'],
                    'value_card_discount' => $v['value_card_discount']
                ]);
            }
        }
    }

    /**
     * 将支付LOG插入数据表
     *
     * @access  public
     * @param integer $id 订单编号
     * @param float $amount 订单金额
     * @param integer $type 支付类型
     * @param integer $is_paid 是否已支付
     *
     * @return  int
     */
    public function insertPayLog($id, $amount, $type = PAY_SURPLUS, $is_paid = 0)
    {
        if ($id) {
            $pay_log = [
                'order_id' => $id,
                'order_amount' => $amount,
                'order_type' => $type,
                'is_paid' => $is_paid
            ];

            $log_id = PayLog::insertGetId($pay_log);
        } else {
            $log_id = 0;
        }

        return $log_id;
    }

    /**
     * 获得订单地址信息
     *
     * @param int $order_id
     * @return string
     */
    private function getFlowOrderUserRegion($order_id = 0)
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
     * 查询订单是否红包全场通用
     *
     * @param int $bonus_id
     * @return mixed
     */
    private function bonusAllGoods($bonus_id = 0)
    {
        $usebonus_type = BonusType::whereHasIn('getUserBonus', function ($query) use ($bonus_id) {
            $query->where('bonus_id', $bonus_id);
        })
            ->value('usebonus_type');

        return $usebonus_type;
    }

    /**
     * 商家配送方式分单分组
     *
     * @param array $ru_id
     * @param array $shipping_id
     * @param array $shipping_name
     * @param array $shipping_code
     * @param array $shipping_type
     * @return array
     */
    private function sellerShippingOrder($ru_id = [], $shipping_id = [], $shipping_name = [], $shipping_code = [], $shipping_type = [])
    {
        $shipping_id = explode(',', $shipping_id);
        $shipping_name = explode(',', $shipping_name);
        $shipping_code = explode(',', $shipping_code);
        $shipping_type = explode(',', $shipping_type);

        $shippingId = '';
        $shippingName = '';
        $shippingCode = '';
        $shippingType = '';

        foreach ($shipping_id as $key => $row) {
            $row = explode('|', $row);
            if ($row[0] == $ru_id) {
                $shippingId = $row[1] ?? 0;
            }
        }

        foreach ($shipping_name as $key => $row) {
            $row = explode('|', $row);
            if ($row[0] == $ru_id) {
                $shippingName = $row[1] ?? '';
            }
        }

        if ($shipping_code) {
            foreach ($shipping_code as $key => $row) {
                $row = explode('|', $row);
                if ($row[0] == $ru_id) {
                    $shippingCode = $row[1] ?? '';
                }
            }
        }

        if ($shipping_type) {
            foreach ($shipping_type as $key => $row) {
                $row = explode('|', $row);
                if ($row[0] == $ru_id) {
                    $shippingType = $row[1] ?? 0;
                }
            }
        }

        $shipping = [
            'shipping_id' => $shippingId,
            'shipping_name' => $shippingName,
            'shipping_code' => $shippingCode,
            'shipping_type' => $shippingType
        ];

        return $shipping;
    }

    /**
     * 得到新订单号
     *
     * @return array|int|string
     */
    private function getOrderChildSn()
    {
        $time = explode(" ", microtime());
        $time = $time[1] . ($time[0] * 1000);
        $time = explode(".", $time);
        $time = isset($time[1]) ? $time[1] : 0;
        $time = TimeRepository::getLocalDate('YmdHis') + $time;

        /* 选择一个随机的方案 */
        mt_srand((double)microtime() * 1000000);
        $time = $time . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

        if (empty(config('queue.default')) || config('queue.default') == 'sync') {
            if (session('order_done_sn') == $time) {
                $time += 1;
            }
        }

        return $time;
    }

    /**
     * 查询订单使用的优惠券
     *
     * @param int $order_id
     * @param array $uc_id
     * @return array
     */
    private function getUserOrderCoupons($order_id = 0, $uc_id = [])
    {
        $uc_id = BaseRepository::getExplode($uc_id);

        if (empty($uc_id)) {
            return [];
        }

        $res = CouponsUser::where('is_delete', 0)->where('order_id', $order_id)->whereIn('uc_id', $uc_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {

            $cou_id = BaseRepository::getKeyPluck($res, 'cou_id');
            $couponsList = CouponDataHandleService::getCouponsDataList($cou_id);

            if ($res) {
                foreach ($res as $key => $row) {
                    $row['uc_money'] = $row['cou_money'];
                    $coupons = $couponsList[$row['cou_id']] ?? [];

                    if ($coupons) {

                        $cou_money = 0;
                        if ($coupons['cou_type'] != VOUCHER_SHIPPING) {
                            $cou_money = $row['uc_money'] > 0 ? $row['uc_money'] : $coupons['cou_money'];
                        }

                        $row['uc_money'] = $cou_money;
                        $coupons['cou_money'] = $cou_money;
                        unset($row['cou_money']);

                        $arr[$key] = $coupons ? array_merge($row, $coupons) : $row;
                    }
                }
            }
        }

        return $arr;
    }

    /**
     * 商家订单运费
     *
     * @param array $sellerOrderInfo
     * @param array $cart_goods
     * @return int
     */
    private function getSellerShippingFee($sellerOrderInfo = [], $cart_goods = [])
    {

        //获取配送区域
        $val = Shipping::where('shipping_id', $sellerOrderInfo['shipping_id']);
        $val = BaseRepository::getToArrayFirst($val);

        if (empty($val)) {
            return 0;
        }

        if ($sellerOrderInfo['region']) {
            $sellerOrderInfo['region'] = array_values($sellerOrderInfo['region']);
        }

        $consignee['country'] = $sellerOrderInfo['region'][0];
        $consignee['province'] = $sellerOrderInfo['region'][1];
        $consignee['city'] = $sellerOrderInfo['region'][2];
        $consignee['district'] = $sellerOrderInfo['region'][3];
        $consignee['street'] = $sellerOrderInfo['region'][4];
        $order_transpor = $this->orderTransportService->getOrderTransport($cart_goods, $consignee, $val['shipping_id'], $val['shipping_code']);

        $shippingFee = 0;
        if ($order_transpor['freight']) {
            $shippingFee += $order_transpor['sprice']; //有配送按配送区域计算运费
        } else {
            $shippingFee = $order_transpor['sprice'];
        }

        return $shippingFee;
    }

    /**
     * 获取子订单可支付积分金额
     *
     * @param int $order_id
     * @param int $ru_id
     * @return float
     */
    private function getIntegralRatio($order_id = 0, $ru_id = 0)
    {
        // 获取订单商品总共可用积分
        $integral_total = $this->getIntegral($order_id, $ru_id);

        return $integral_total;
    }


    /**
     * 订单商品总共可用积分
     *
     * @param int $order_id
     * @param int $ru_id
     * @return int
     */
    private function getIntegral($order_id = 0, $ru_id = 0)
    {
        $res = OrderGoods::select('goods_id', 'goods_price', 'goods_number', 'ru_id', 'model_attr', 'warehouse_id', 'area_id', 'area_city')
            ->where('order_id', $order_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        $integral_money = 0;
        if ($res) {

            $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id);
            $warehouseGoodsList = GoodsDataHandleService::getWarehouseGoodsDataList($goods_id);
            $warehouseAreaGoodsList = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods_id);

            foreach ($res as $key => $v) {

                $goods = $goodsList[$v['goods_id']] ?? [];

                $sql = [
                    'where' => [
                        [
                            'name' => 'region_id',
                            'value' => $v['warehouse_id']
                        ]
                    ]
                ];
                $warehouseGoods = BaseRepository::getArraySqlGet($warehouseGoodsList, $sql);

                $sql = [
                    'where' => [
                        [
                            'name' => 'region_id',
                            'value' => $v['area_id']
                        ]
                    ]
                ];

                if (config('shop.area_pricetype') == 1) {
                    $sql['where'][] = [
                        'name' => 'city_id',
                        'value' => $v['area_city']
                    ];
                }

                $warehouseAreaGoods = BaseRepository::getArraySqlGet($warehouseAreaGoodsList, $sql);

                $goods_integral = $goods['integral'] ?? 0;
                $warehouse_pay_integral = $warehouseGoods['pay_integral'] ?? 0;
                $area_pay_integral = $warehouseAreaGoods['pay_integral'] ?? 0;

                if ($v['model_attr'] == 1) {
                    $integral = $warehouse_pay_integral;
                } elseif ($v['model_attr'] == 2) {
                    $integral = $area_pay_integral;
                } else {
                    $integral = $goods_integral;
                }

                /**
                 * 取最小兑换积分
                 */
                $integral_list = [
                    $this->dscRepository->integralOfValue($v['goods_price'] * $v['goods_number']),
                    $this->dscRepository->integralOfValue($integral * $v['goods_number'])
                ];

                $integral = BaseRepository::getArrayMin($integral_list);
                $integral = $this->dscRepository->valueOfIntegral($integral);

                $v['integral'] = $this->dscRepository->integralOfValue($integral);
                $v['integral_money'] = $integral;

                $arr[$v['ru_id']]['goods_list'][$key] = $v;
            }

            $goods_list = $arr[$ru_id]['goods_list'];
            $integral_money = BaseRepository::getArraySum($goods_list, 'integral_money');
        }

        return $integral_money;
    }

    /**
     * 插入订单表
     *
     * @param array $order
     * @param array $cart_value
     * @return int
     */
    public function AddToOrder($order = [], $cart_value = [])
    {
        $order_other = BaseRepository::getArrayfilterTable($order, 'order_info');
        $cart_value = BaseRepository::getExplode($cart_value);

        $order_id = 0;
        if (!empty($cart_value)) {
            $count = Cart::whereIn('rec_id', $cart_value)->count();
        } else {
            $count = 1;
        }

        if ($count > 0) {
            return OrderInfo::insertGetId($order_other);
        }

        return $order_id;
    }

    /**
     * 插入订单商品数据列表
     *
     * @param array $where
     * @param array $cart_goods
     * @param array $order
     * @return array
     * @throws \Exception
     */
    public function AddToOrderGoods($where = [], $cart_goods = [], $order = [])
    {
        $rec_list = [];
        if ($where['order_id'] > 0 && !empty($cart_goods)) {

            $user_id = $where['user_id'] ?? 0;
            $is_distribution = $where['is_distribution'] ?? 0;

            $ruList = BaseRepository::getKeyPluck($cart_goods, 'ru_id');
            $ruList = BaseRepository::getArrayUnique($ruList);

            $goods_id = BaseRepository::getKeyPluck($cart_goods, 'goods_id');

            $goods = GoodsDataHandleService::GoodsDataList($goods_id);
            $goodsExtend = GoodsDataHandleService::goodsExtendList($goods_id);

            /* 订单优惠券均摊到订单商品， 检测优惠券是否支持商品参与均摊 start */
            $couponsShareEqually = $this->orderCouponsShareEqually($cart_goods, $order, $user_id);
            /* 订单优惠券均摊到订单商品， 检测优惠券是否支持商品参与均摊 end */

            /* 订单红包均摊到订单商品， 检测红包是否支持店铺商品参与均摊 start */
            $bonusSubtotal = 0;
            $useType = 0;
            $bonus_ru_id = 0;
            $bonus_id = $order['bonus_id'] ?? 0;
            $bonusInfo = [];
            if ($bonus_id > 0) {
                /* 购物车商品总金额[排除活动商品：夺宝奇兵、拍卖、超值礼包等，仅支持普通商品] */
                $sql = [
                    'where' => [
                        [
                            'name' => 'extension_code',
                            'value' => ''
                        ],
                        [
                            'name' => 'rec_type',
                            'value' => 0
                        ],
                        [
                            'name' => 'is_gift',
                            'value' => 0
                        ],
                        [
                            'name' => 'parent_id',
                            'value' => 0
                        ]
                    ]
                ];

                $bonusSumList = BaseRepository::getArraySqlGet($cart_goods, $sql);
                $bonusSubtotal = BaseRepository::getArraySum($bonusSumList, 'subtotal');

                if (count($ruList) > 1) {
                    $bonusInfo = UserBonus::where('bonus_id', $bonus_id)->where('user_id', $user_id);
                    $bonusInfo = $bonusInfo->with([
                        'getBonusType' => function ($query) {
                            $query->select('type_id', 'usebonus_type', 'user_id');
                        }
                    ]);

                    $bonusInfo = BaseRepository::getToArrayFirst($bonusInfo);

                    /* [0|自主使用，1|平台和店铺通用] */
                    $useType = $bonusInfo['get_bonus_type']['usebonus_type'] ?? 0;
                    $bonus_ru_id = $bonusInfo['get_bonus_type']['user_id'] ?? 0;
                }

                /* 自主使用时，店铺商品不计算均摊 */
                if ($useType == 0 && count($ruList) > 1 && $bonusInfo) {
                    if ($bonus_ru_id > 0) {
                        $sql = [
                            'where' => [
                                [
                                    'name' => 'ru_id',
                                    'value' => $bonus_ru_id
                                ],
                                [
                                    'name' => 'extension_code',
                                    'value' => ''
                                ],
                                [
                                    'name' => 'rec_type',
                                    'value' => 0
                                ],
                                [
                                    'name' => 'is_gift',
                                    'value' => 0
                                ],
                                [
                                    'name' => 'parent_id',
                                    'value' => 0
                                ]
                            ]
                        ];
                        $ruGoodsList = BaseRepository::getArraySqlGet($cart_goods, $sql);

                        $disAmountAll = BaseRepository::getArraySum($ruGoodsList, 'dis_amount');
                        $bonusSubtotal = BaseRepository::getArraySum($ruGoodsList, 'subtotal');
                    } else {
                        $sql = [
                            'where' => [
                                [
                                    'name' => 'ru_id',
                                    'value' => 0
                                ],
                                [
                                    'name' => 'extension_code',
                                    'value' => ''
                                ],
                                [
                                    'name' => 'rec_type',
                                    'value' => 0
                                ],
                                [
                                    'name' => 'is_gift',
                                    'value' => 0
                                ],
                                [
                                    'name' => 'parent_id',
                                    'value' => 0
                                ]
                            ]
                        ];
                        $sellerList = BaseRepository::getArraySqlGet($cart_goods, $sql);

                        $disAmountAll = BaseRepository::getArraySum($sellerList, 'dis_amount');
                        $bonusSubtotal = BaseRepository::getArraySum($sellerList, 'subtotal');
                    }
                } else {
                    $disAmountAll = BaseRepository::getArraySum($cart_goods, 'dis_amount');
                }

                $bonusSubtotal = $bonusSubtotal - $disAmountAll;
            }
            /* 订单红包均摊到订单商品， 检测红包是否支持店铺商品参与均摊 end */

            /* 订单储值卡均摊到订单商品 start */
            if (count($ruList) == 1) {
                $goodsValueCardList = $this->orderValueCardShareEqually($cart_goods, $order);
            }
            /* 订单储值卡均摊到订单商品 end */

            /* 红包 */
            $goods_bonus = 0;
            $bonus_list = [];

            /* 优惠券 */
            $goods_coupons = [];
            $coupons_list = [];

            $is_drp_goods = 0;

            $cart_goods_count = count($cart_goods);

            /* 均摊储值卡 */
            $goods_card_money = 0;
            $value_card_list = [];
            $goods_value_card_discount = 0;
            $value_card_discount_list = [];

            foreach ($cart_goods as $key => $val) {

                $val = BaseRepository::recursiveNullVal($val);

                /* 获取商家优惠券 */
                $goods_coupons[$val['ru_id']] = $goods_coupons[$val['ru_id']] ?? 0;
                $couponsGoods = $couponsShareEqually[$val['ru_id']]['couponsGoods'] ?? [];
                $couponSubtotal = $couponsShareEqually[$val['ru_id']]['couponSubtotal'] ?? [];
                $couponsInfo = $couponsShareEqually[$val['ru_id']]['couponsInfo'] ?? [];

                // 扩展商品信息
                $goods_extend = $goodsExtend[$val['goods_id']] ?? [];

                $order_goods = [
                    'order_id' => $where['order_id'],
                    'cart_recid' => $val['rec_id'],
                    'user_id' => $user_id,
                    'goods_id' => $val['goods_id'],
                    'goods_name' => $val['goods_name'],
                    'goods_sn' => $val['goods_sn'],
                    'product_id' => $val['product_id'] ?? 0,
                    'product_sn' => !empty($val['product_id']) ? $val['goods_sn'] : $val['product_sn'] ?? '',
                    'is_reality' => $goods_extend['is_reality'] ?? 0,
                    'is_return' => $goods_extend['is_return'] ?? 0,
                    'is_fast' => $goods_extend['is_fast'] ?? 0,
                    'goods_number' => $val['goods_number'],
                    'market_price' => $val['market_price'],
                    'goods_price' => $val['goods_price'],
                    'stages_qishu' => $val['stages_qishu'],
                    'goods_attr' => $val['goods_attr'],
                    'is_real' => $val['is_real'],
                    'extension_code' => $val['extension_code'],
                    'parent_id' => $val['parent_id'],
                    'is_gift' => $val['is_gift'],
                    'model_attr' => $val['model_attr'],
                    'goods_attr_id' => $val['goods_attr_id'],
                    'ru_id' => $val['ru_id'],
                    'warehouse_id' => $val['warehouse_id'],
                    'area_id' => $val['area_id'],
                    'area_city' => $val['area_city'],
                    'freight' => $val['freight'],
                    'tid' => $val['tid'],
                    'shipping_fee' => $val['shipping_fee'],
                    'cost_price' => $val['cost_price'],
                    'dis_amount' => $order['dis_amount_list'][$val['rec_id']] ?? 0, //商品满减优惠金额
                    'goods_favourable' => $val['goods_favourable'],
                    'goods_integral' => $val['goods_integral'],
                    'goods_integral_money' => $val['goods_integral_money'],
                ];

                /* 订单均摊 排除超值礼包 */
                $is_general_goods = $order_goods['extension_code'] != 'package_buy' && $val['is_gift'] == 0 && $val['parent_id'] == 0;
                $keySubtotal = $val['goods_price'] * $val['goods_number'];

                /* 订单红包均摊到订单商品， 检测红包是否支持店铺商品参与均摊 start */
                $isShareAlike = 1;
                if ($useType == 0 && $bonusInfo) {
                    if ($bonus_ru_id > 0) {
                        $isShareAlike = $val['ru_id'] == 0 ? 0 : 1;
                    } else {
                        $isShareAlike = $val['ru_id'] > 0 ? 0 : 1;
                    }
                }

                $order['bonus'] = $order['bonus'] ?? 0;
                $order_goods['goods_bonus'] = 0;
                if ($is_general_goods === true && $order['bonus'] > 0 && $bonusSubtotal > 0) {
                    if ($val['goods_price'] > 0 && $isShareAlike == 1) {
                        $order_goods['goods_bonus'] = (($keySubtotal - $val['dis_amount']) / $bonusSubtotal) * $order['bonus'];
                    }

                    $order_goods['goods_bonus'] = $this->dscRepository->changeFloat($order_goods['goods_bonus']);

                    if ($order_goods['goods_bonus'] > 0) {
                        $bonus_list[$key]['goods_bonus'] = $order_goods['goods_bonus'];
                        $goods_bonus += $order_goods['goods_bonus'];
                    }
                }
                /* 订单红包均摊到订单商品， 检测红包是否支持店铺商品参与均摊 end */

                /* 订单优惠券均摊到订单商品， 检测优惠券是否支持商品参与均摊 start */
                $coupons_money = $couponsInfo['cou_money'] ?? 0;
                $order_goods['goods_coupons'] = 0;
                if ($couponsGoods && $couponsGoods['ru_id'] == $val['ru_id'] && $is_general_goods === true && $coupons_money > 0 && $couponSubtotal > 0) {
                    $cat_id = $val['cat_id'] ?? 0;
                    if ($val['goods_price'] > 0) {
                        if ($couponsGoods['is_coupons'] == 1) {
                            $order_goods['goods_coupons'] = (($keySubtotal - $val['dis_amount']) / $couponSubtotal) * $coupons_money;
                        } elseif ($couponsGoods['is_coupons'] == 2) {
                            if ($couponsGoods['cou_goods'] && in_array($val['goods_id'], $couponsGoods['cou_goods'])) {
                                $order_goods['goods_coupons'] = (($keySubtotal - $val['dis_amount']) / $couponSubtotal) * $coupons_money;
                            }
                        } elseif ($couponsGoods['is_coupons'] == 3) {
                            if ($cat_id > 0 && in_array($cat_id, $couponsGoods['spec_cat'])) {
                                $order_goods['goods_coupons'] = (($keySubtotal - $val['dis_amount']) / $couponSubtotal) * $coupons_money;
                            }
                        }

                        $order_goods['goods_coupons'] = $this->dscRepository->changeFloat($order_goods['goods_coupons']);

                        if ($order_goods['goods_coupons'] > 0) {
                            $coupons_list[$val['ru_id']][$key]['goods_coupons'] = $order_goods['goods_coupons'];
                            $goods_coupons[$val['ru_id']] += $order_goods['goods_coupons'];
                        }
                    }
                }
                /* 订单优惠券均摊到订单商品， 检测优惠券是否支持商品参与均摊 end */

                /* 储值卡均摊金额 start */
                $goodsValueCard = $goodsValueCardList[$val['rec_id']] ?? [];

                if ($goodsValueCard) {
                    $order_goods['goods_value_card'] = $goodsValueCard['goods_value_card'];
                    $value_card_list[$key]['goods_value_card'] = $goodsValueCard['goods_value_card'];
                    $goods_card_money += $goodsValueCard['goods_value_card'];

                    if ($goodsValueCard['value_card_discount'] > 0) {
                        $order_goods['value_card_discount'] = $goodsValueCard['value_card_discount'];
                        $value_card_discount_list[$key]['value_card_discount'] = $goodsValueCard['value_card_discount'];
                        $goods_value_card_discount += $goodsValueCard['value_card_discount'];
                    }
                }
                /* 储值卡均摊金额 end */

                if (CROSS_BORDER === true) { // 跨境多商户
                    $order_goods['rate_price'] = 0;
                    if (isset($where['rate_arr']) && !empty($where['rate_arr'])) {
                        foreach ($where['rate_arr'] as $k => $v) {//插入跨境税费
                            if ($val['goods_id'] == $v['goods_id']) {
                                $order_goods['rate_price'] = $v['rate_price'];
                            }
                        }
                    }
                }

                // 原商品信息 非购物车商品信息
                $goodsInfo = $goods[$val['goods_id']] ?? [];
                $order_goods['commission_rate'] = $goodsInfo['commission_rate'] ?? 0;

                $goodsInfo['extension_code'] = $goodsInfo['extension_code'] ?? '';

                // 活动商品不参与分销 虚拟商品除外
                $is_distribution = ($goodsInfo['extension_code'] == '' || $goodsInfo['extension_code'] == 'virtual_card') ? $is_distribution : 0;
                $order_goods['is_distribution'] = isset($goodsInfo['is_distribution']) ? $goodsInfo['is_distribution'] * $is_distribution : 0;

                if (file_exists(MOBILE_DRP)) {
                    // 购买成为分销商商品订单
                    $order_goods['membership_card_id'] = $goodsInfo['membership_card_id'] ?? 0;
                    $order_goods['dis_commission'] = $goodsInfo['dis_commission'] ?? 0;

                    // 即是分销商品，又是会员卡指定购买商品，则优先使用会员卡商品中设置的【会员卡分销】分成奖励
                    if (isset($order_goods['membership_card_id']) && empty($order_goods['membership_card_id'])) {
                        // 计算订单商品佣金
                        $drp_order_goods = \App\Plugins\UserRights\DrpGoods\Services\DrpGoodsRightsService::drp_order_goods($val, $order_goods, $cart_goods_count, $order);
                        $order_goods['drp_goods_price'] = $drp_order_goods['drp_goods_price'] ?? 0;
                        $order_goods['drp_money'] = $drp_order_goods['drp_money'] ?? 0;

                        $is_drp_goods += $order_goods['is_distribution'];
                    }

                    // 购买权益卡订单商品折扣
                    if (isset($order['order_membership_card_id']) && $order['order_membership_card_id'] > 0 && isset($goodsInfo['membership_card_discount_price']) && $goodsInfo['membership_card_discount_price'] > 0) {
                        $order_goods['membership_card_discount_price'] = $goodsInfo['membership_card_discount_price'];
                    }
                }

                $recId = OrderGoods::insertGetId($order_goods);

                if ($recId > 0) {
                    /* 删除购物车商品 */
                    Cart::where('rec_id', $val['rec_id'])->where('user_id', $user_id)->delete();

                    /* 处理秒杀更新销量 */
                    if (stripos($val['extension_code'], 'seckill') !== false) {
                        $sec_id = (int)substr($val['extension_code'], 7);
                        $dbRaw = [
                            'sales_volume' => "sales_volume + " . $val['goods_number'],
                        ];
                        $dbRaw = BaseRepository::getDbRaw($dbRaw);
                        SeckillGoods::where('id', $sec_id)->update($dbRaw);
                    }

                    if ($coupons_money > 0 && $order_goods['goods_coupons'] > 0) {
                        $coupons_list[$val['ru_id']][$key]['rec_id'] = $recId;
                    }

                    if ($goodsValueCard) {
                        $value_card_list[$key]['rec_id'] = $recId;

                        if ($goodsValueCard['value_card_discount'] > 0) {
                            $value_card_discount_list[$key]['rec_id'] = $recId;
                        }
                    }

                    if ($is_general_goods === true && $order['bonus'] > 0 && $bonusSubtotal > 0 && $order_goods['goods_bonus']) {
                        $bonus_list[$key]['rec_id'] = $recId;
                    }

                    $rec_list[] = $recId;
                }
            }

            /* 核对均摊优惠券商品金额 */
            if ($coupons_list && $cart_goods_count > 1) {
                foreach ($coupons_list as $ruId => $row) {

                    $row = array_values($row);

                    $couponsInfo = $couponsShareEqually[$ruId]['couponsInfo'] ?? [];
                    $coupons_money = $couponsInfo['cou_money'] ?? 0;

                    if ($coupons_money > 0) {
                        $this->dscRepository->collateOrderGoodsCoupons($row, $coupons_money, $goods_coupons[$ruId]);
                    }
                }
            }
            if ($cart_goods_count == 1 && $order['coupons'] > 0) {
                OrderGoods::where('order_id', $where['order_id'])->where('user_id', $user_id)->update(['goods_coupons' => $order['coupons']]);
            }

            /* 核对均摊储值卡商品金额 */
            if (!empty($value_card_list) && isset($order['use_value_card']) && $order['use_value_card'] > 0) {
                CommonRepository::collateOrderValueCard($value_card_list, $order['use_value_card'], $goods_card_money);
            }

            /* 核对均摊储值卡折扣商品金额 */
            if (!empty($value_card_discount_list) && isset($order['vc_dis_money']) && $order['vc_dis_money'] > 0) {
                CommonRepository::collateOrderValueCardDiscount($value_card_discount_list, $order['vc_dis_money'], $goods_value_card_discount);
            }

            /* 核对均摊红包商品金额 */
            if ($bonus_list && $cart_goods_count > 1) {
                $this->dscRepository->collateOrderGoodsBonus($bonus_list, $order['bonus'] ?? 0, $goods_bonus);
            } else {
                if ($cart_goods_count == 1 && $order['bonus'] > 0) {
                    OrderGoods::where('order_id', $where['order_id'])->where('user_id', $user_id)->update(['goods_bonus' => $order['bonus']]);
                }
            }

            $count = OrderGoods::where('order_id', $where['order_id'])->count();
            if (empty($count)) {
                OrderInfo::where('order_id', $where['order_id'])->delete();
            } else {
                if (file_exists(MOBILE_DRP)) {
                    $updateOrder = [
                        'is_drp' => $is_drp_goods > 0 ? 1 : 0,
                    ];
                    OrderInfo::where('order_id', $where['order_id'])->update($updateOrder);
                }
            }
        }

        /* 购物车商品为空时删除订单 */
        if (empty($cart_goods) || empty($rec_list)) {
            OrderInfo::where('order_id', $where['order_id'])->where('user_id', $where['user_id'])->delete();
            return [];
        }

        return $rec_list;
    }

    /**
     * 返回订单中的虚拟商品
     *
     * @param int $order_id 订单id值
     * @return array
     */
    public function getVirtualGoods($order_id = 0)
    {
        $res = OrderGoods::selectRaw("goods_id, goods_name, (goods_number - send_number) AS num, extension_code")
            ->where('order_id', $order_id)
            ->where('is_real', 0)
            ->where('extension_code', 'virtual_card')
            ->whereRaw("(goods_number - send_number) > 0");

        $res = BaseRepository::getToArrayGet($res);

        $virtual_goods = [];
        if ($res) {
            foreach ($res as $row) {
                $virtual_goods[$row['extension_code']][] = ['goods_id' => $row['goods_id'], 'goods_name' => $row['goods_name'], 'num' => $row['num']];
            }
        }

        return $virtual_goods;
    }

    /**
     * 虚拟商品发货
     *
     * @param $virtual_goods 虚拟商品数组
     * @param int $order_id 订单ID
     * @param string $order_sn 订单号
     * @return bool
     * @throws \Exception
     */
    private function orderVirtualGoodsShip(&$virtual_goods, $order_id = 0, $order_sn = '')
    {
        if ($virtual_goods) {
            foreach ($virtual_goods as $code => $goods_list) {
                /* 只处理虚拟卡 */
                if ($code == 'virtual_card') {
                    foreach ($goods_list as $goods) {
                        if (!$this->virtualCardShipping($goods, $order_id, $order_sn)) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * 虚拟卡发货
     *
     * @param array $goods
     * @param int $order_id
     * @param string $order_sn
     * @return bool
     */
    private function virtualCardShipping($goods = [], $order_id = 0, $order_sn = '')
    {
        /* 检查有没有缺货 */
        $num = VirtualCard::where('goods_id', $goods['goods_id'])
            ->where('is_saled', 0)
            ->count();

        if ($num < $goods['num']) {
            return false;
        }

        /* 取出卡片信息 */
        $arr = VirtualCard::where('goods_id', $goods['goods_id'])
            ->where('is_saled', 0)
            ->take($goods['num']);

        $arr = BaseRepository::getToArrayGet($arr);

        $card_ids = [];
        if ($arr) {
            foreach ($arr as $virtual_card) {
                /* 卡号和密码解密 */
                if (($virtual_card['crc32'] == 0 || $virtual_card['crc32'] == crc32(AUTH_KEY) || $virtual_card['crc32'] == crc32(OLD_AUTH_KEY)) === false) {
                    return false;
                } else {
                    $card_ids[] = $virtual_card['card_id'];
                }
            }
        }

        /* 标记已经取出的卡片 */
        $other = [
            'is_saled' => 1,
            'order_sn' => $order_sn
        ];
        $res = VirtualCard::whereIn('card_id', $card_ids)->update($other);
        if (!$res) {
            return false;
        }

        /* 更新库存 */
        Goods::where('goods_id', $goods['goods_id'])->increment('goods_number', -$goods['num']);

        $order = [];
        if (true) {
            /* 更新订单信息 */
            $res = OrderGoods::where('order_id', $order_id)
                ->where('goods_id', $goods['goods_id'])
                ->update(['send_number' => $goods['num']]);

            if (!$res) {
                return false;
            }
        }

        if (!$order) {
            return false;
        }

        return true;
    }

    /**
     * 订单优惠券均摊到订单商品
     *
     * @param array $res
     * @param array $order
     * @param int $user_id
     * @return array
     * @throws \Exception
     */
    public function orderCouponsShareEqually($res = [], $order = [], $user_id = 0)
    {
        $uc_id = BaseRepository::getExplode($order['uc_id']);

        $arr = [];
        if (!empty($uc_id)) {

            $order['user_id'] = $order['user_id'] ?? $user_id;

            if (isset($order['coupons']) && $order['coupons'] > 0) {
                if ($res) {
                    foreach ($res as $key => $val) {

                        /* 普通商品 */
                        $is_general_goods = $val['extension_code'] == '' && $val['rec_type'] == 0;

                        $cat_id = 0;
                        if ($is_general_goods === true) {
                            $cat_id = $val['cat_id'] ?? 0;
                        }

                        $val['cat_id'] = $cat_id;

                        $res[$key] = $val;
                    }
                };

                $couponsUserList = CouponDataHandleService::getCouponsUserDataList($uc_id, [], $order['user_id']);

                $couponsLlist = [];
                if ($couponsUserList) {
                    $couIdList = BaseRepository::getKeyPluck($couponsUserList, 'cou_id');
                    $couponsLlist = Coupons::whereIn('cou_id', $couIdList)->where('status', COUPON_STATUS_EFFECTIVE);
                    $couponsLlist = BaseRepository::getToArrayGet($couponsLlist);
                }

                if ($couponsLlist) {
                    foreach ($couponsLlist as $key => $coupons) {
                        if (!empty($coupons['cou_goods']) && empty($coupons['spec_cat'])) {
                            $is_coupons = 2;
                        } elseif (empty($coupons['cou_goods']) && !empty($coupons['spec_cat'])) {
                            $is_coupons = 3;
                        } else {
                            $is_coupons = 1;
                        }

                        $cou_goods = BaseRepository::getExplode($coupons['cou_goods']);
                        $spec_cat = BaseRepository::getExplode($coupons['spec_cat']);

                        if ($spec_cat) {
                            $catList[$key] = $this->categoryService->getCatListChildren($spec_cat);
                        }

                        $sql = [
                            'where' => [
                                [
                                    'name' => 'cou_id',
                                    'value' => $coupons['cou_id']
                                ]
                            ]
                        ];
                        $couponsUser = BaseRepository::getArraySqlFirst($couponsUserList, $sql);

                        $cou_money = $couponsUser['cou_money'] > 0 ? $couponsUser['cou_money'] : $coupons['cou_money'];
                        $coupons['cou_money'] = $cou_money;
                        $couponsUser['cou_money'] = $cou_money;

                        $arr[$coupons['ru_id']]['couponsInfo'] = ArrRepository::getArrCollapse([$coupons, $couponsUser]);
                        $arr[$coupons['ru_id']]['couponsGoods'] = [
                            'is_coupons' => $is_coupons,
                            'ru_id' => $coupons['ru_id'],
                            'cou_goods' => $cou_goods,
                            'spec_cat' => $catList[$key] ?? []
                        ];

                        /* 购物车商品总金额[排除活动商品：夺宝奇兵、拍卖、超值礼包等，仅支持普通商品] */
                        $sql = [
                            'where' => [
                                [
                                    'name' => 'extension_code',
                                    'value' => ''
                                ],
                                [
                                    'name' => 'rec_type',
                                    'value' => 0
                                ],
                                [
                                    'name' => 'ru_id',
                                    'value' => $coupons['ru_id']
                                ],
                                [
                                    'name' => 'is_gift',
                                    'value' => 0
                                ],
                                [
                                    'name' => 'parent_id',
                                    'value' => 0
                                ]
                            ]
                        ];

                        if ($arr[$coupons['ru_id']]['couponsGoods']['is_coupons'] == 2) {
                            $sql['whereIn'][] = [
                                'name' => 'goods_id',
                                'value' => $arr[$coupons['ru_id']]['couponsGoods']['cou_goods']
                            ];
                        } elseif ($arr[$coupons['ru_id']]['couponsGoods']['is_coupons'] == 3) {
                            $sql['whereIn'][] = [
                                'name' => 'cat_id',
                                'value' => $arr[$coupons['ru_id']]['couponsGoods']['spec_cat']
                            ];
                        }

                        $couponsSumList = BaseRepository::getArraySqlGet($res, $sql);

                        $couponSubtotal = BaseRepository::getArraySum($couponsSumList, 'subtotal');
                        $disAmountAll = BaseRepository::getArraySum($couponsSumList, 'dis_amount');
                        $arr[$coupons['ru_id']]['couponSubtotal'] = $couponSubtotal - $disAmountAll;
                    }
                }
            }
        }

        return $arr;
    }

    /**
     * 储值卡均摊商品金额
     *
     * @param array $res
     * @param array $order
     * @return array
     * @throws \Exception
     */
    public function orderValueCardShareEqually($res = [], $order = [])
    {
        if (empty($res)) {
            return $res;
        }

        $arr = [];
        $use_value_card = $order['use_value_card'] ?? 0;
        $vc_dis_money = $order['vc_dis_money'] ?? 0;
        $vc_rec_list = $order['vc_rec_list'] ?? [];

        if ($use_value_card > 0 && !empty($vc_rec_list)) {

            $sql = [
                'whereIn' => [
                    [
                        'name' => 'rec_id',
                        'value' => $vc_rec_list
                    ]
                ]
            ];
            $res = BaseRepository::getArraySqlGet($res, $sql);

            if ($res) {

                /* 按商家分组 */
                $disAmount = BaseRepository::getArraySum($res, 'dis_amount');
                $cartSubtotal = BaseRepository::getArraySum($res, ['goods_price', 'goods_number']);
                $cartSubtotal = $cartSubtotal - $disAmount;

                foreach ($res as $key => $value) {
                    $arr[$value['rec_id']]['ru_id'] = $value['ru_id'];
                    $arr[$value['rec_id']]['rec_id'] = $value['rec_id'];
                    $arr[$value['rec_id']]['goods_id'] = $value['goods_id'];
                    $keySubtotal = $value['goods_price'] * $value['goods_number'] - $value['dis_amount'];
                    $goods_value_card = ($keySubtotal / $cartSubtotal) * $use_value_card;
                    $goods_value_card = $this->dscRepository->changeFloat($goods_value_card);
                    $arr[$value['rec_id']]['goods_value_card'] = $goods_value_card;

                    if ($vc_dis_money > 0) {
                        $card_discount_money = ($keySubtotal / $cartSubtotal) * $vc_dis_money;
                        $card_discount_money = $this->dscRepository->changeFloat($card_discount_money);
                        $arr[$value['rec_id']]['value_card_discount'] = $card_discount_money;
                    } else {
                        $arr[$value['rec_id']]['value_card_discount'] = 0;
                    }
                }
            }
        }

        $arr = BaseRepository::valueErrorArray($arr, 'value_card_discount', 'rec_id', $vc_dis_money);

        return $arr;
    }

    /**
     * 获取满足储值卡商品条件的购物商品列表
     *
     * @param array $res
     * @param array $order
     * @return array
     * @throws \Exception
     */
    public function cardOrderGoodsList($res = [], $order = [])
    {
        $vc_id = $order['vc_id'] ?? 0;
        if (empty($vc_id)) {
            return [];
        }

        $sql = [
            'where' => [
                [
                    'name' => 'extension_code',
                    'value' => 'package_buy',
                    'condition' => '<>'
                ]
            ]
        ];
        $res = BaseRepository::getArraySqlGet($res, $sql); //排除超值礼包商品

        $valueCardList = ValueCardDataHandleService::getValueCardDataList($vc_id);

        $sql = [
            'where' => [
                [
                    'name' => 'user_id',
                    'value' => $order['user_id']
                ]
            ]
        ];
        $valueCard = BaseRepository::getArraySqlFirst($valueCardList, $sql);

        if (!empty($valueCard)) {
            $cardTypeList = ValueCardDataHandleService::getValueCardTypeDataList($valueCard['tid'], ['use_condition', 'use_merchants', 'spec_goods', 'spec_cat']);
            $cardType = BaseRepository::getArrayFirst($cardTypeList);

            $use_condition = $cardType['use_condition'] ?? 0;
            $spec_cat = $cardType['spec_cat'] ?? 0;
            $spec_goods = $cardType['spec_goods'] ?? 0;
            if ($cardType) {
                if ($cardType['use_merchants'] == 'self') {
                    $sql = [
                        'where' => [
                            [
                                'name' => 'ru_id',
                                'value' => 0
                            ]
                        ]
                    ];
                    $res = BaseRepository::getArraySqlGet($res, $sql);
                } elseif ($cardType['use_merchants'] != 'all') {
                    $ruList = BaseRepository::getExplode($cardType['use_merchants']);
                    $sql = [
                        'whereIn' => [
                            [
                                'name' => 'ru_id',
                                'value' => $ruList
                            ]
                        ]
                    ];
                    $res = BaseRepository::getArraySqlGet($res, $sql);
                }
            } else {
                $res = [];
            }

            if ($use_condition == 1) {

                $spec_cat = BaseRepository::getExplode($spec_cat);
                $catList = $this->categoryService->getCatListChildren($spec_cat);

                $sql = [
                    'whereIn' => [
                        [
                            'name' => 'cat_id',
                            'value' => $catList
                        ]
                    ]
                ];
                $res = BaseRepository::getArraySqlGet($res, $sql);
            } elseif ($use_condition == 2) {
                $specGoodsList = BaseRepository::getExplode($spec_goods);
                $sql = [
                    'whereIn' => [
                        [
                            'name' => 'goods_id',
                            'value' => $specGoodsList
                        ]
                    ]
                ];
                $res = BaseRepository::getArraySqlGet($res, $sql);
            }
        }

        return $res;
    }

    /**
     * 设置优惠券为已使用
     *
     * @param array $uc_id 优惠券ID
     * @param int $order_id 订单ID
     * @param int $uid 会员ID
     * @return int
     */
    public function orderUseCoupons($uc_id = [], $order_id = 0, $uid = 0)
    {
        $res = 0;
        if (!empty($uc_id)) {
            $uc_id = BaseRepository::getExplode($uc_id);
            $time = TimeRepository::getGmTime();
            $other = [
                'order_id' => $order_id,
                'is_use_time' => $time,
                'is_use' => 1
            ];
            $res = CouponsUser::where('is_delete', 0)->whereIn('uc_id', $uc_id)->where('user_id', $uid)->update($other);
        }

        return $res;
    }

    /**
     * 改变储值卡余额
     *
     * @param int $vc_id 储值卡ID
     * @param int $order_id 订单ID
     * @param string $order_sn 订单编号
     * @param int $use_val 使用金额
     * @param int $vc_dis_money 储值卡折扣金额
     * @return bool
     * @throws \Exception
     */
    public function useValueCard($vc_id = 0, $order_id = 0, $order_sn = '', $use_val = 0, $vc_dis_money = 0)
    {
        $valueCard = ValueCard::select('card_money', 'tid')->where('vid', $vc_id);
        $valueCard = BaseRepository::getToArrayFirst($valueCard);

        $card_money = $valueCard ? $valueCard['card_money'] : 0;
        $card_money -= $use_val;

        if (empty($valueCard) || $card_money < 0) {
            return false;
        }

        $res = ValueCard::where('vid', $vc_id)->update(['card_money' => $card_money]);

        if (!$res) {
            return false;
        }

        $vc_dis = ValueCardType::where('id', $valueCard['tid'])->value('vc_dis');
        $vc_dis = $vc_dis ? $vc_dis : 0;

        $other = [
            'vc_id' => $vc_id,
            'order_id' => $order_id,
            'use_val' => $use_val,
            'vc_dis_money' => $vc_dis_money,
            'vc_dis' => $vc_dis,
            'record_time' => TimeRepository::getGmTime()
        ];

        if (!empty($order_sn)) {
            $other['change_desc'] = sprintf(lang('order.pay_order_sn'), $order_sn);
        }

        $res = ValueCardRecord::insertGetId($other);

        if (!$res) {
            return false;
        }

        return true;
    }

    /**
     * 设置红包为已使用
     *
     * @param int $bonus_id 红包id
     * @param int $order_id 订单id
     * @return  bool
     */
    public function useBonus($bonus_id = 0, $order_id = 0)
    {
        $other = [
            'order_id' => $order_id,
            'used_time' => TimeRepository::getGmTime()
        ];
        $res = UserBonus::where('bonus_id', $bonus_id)->update($other);

        return $res;
    }

    /**
     * 设置红包为未使用
     *
     * @param int $bonus_id
     * @return mixed
     */
    public function unuseBonus($bonus_id = 0)
    {
        $other = [
            'order_id' => 0,
            'used_time' => 0
        ];
        $res = UserBonus::where('bonus_id', $bonus_id)->update($other);

        return $res;
    }

    /**
     * 均摊优惠券、红包[均摊金额存储购物车]
     *
     * @param array $cart_goods
     * @param array $order
     * @param array $couponsList
     * @param int $type
     * @return array
     * @throws \Exception
     */
    public function couponsBonusFlowShareEqually($cart_goods = [], $order = [], $couponsList = [], $type = 0)
    {
        $ruList = BaseRepository::getKeyPluck($cart_goods, 'ru_id');
        $ruList = BaseRepository::getArrayUnique($ruList);

        $uc_id = $order['uc_id'] ?? 0;
        $bonus_id = $order['bonus_id'] ?? 0;
        $user_id = $order['user_id'] ?? 0;

        $couponsOther = $order;
        $coupons = BaseRepository::getArrayCollapse($couponsList);
        $coupons = BaseRepository::getArraySum($coupons, 'cou_money');
        $couponsOther['coupons'] = $coupons;

        $couponsShareEqually = [];
        if ($type == 1) {
            $couponsShareEqually = $this->orderCouponsShareEqually($cart_goods, $couponsOther, $user_id);
        }

        $bonusSubtotal = 0;
        $useType = 0;
        $bonus_ru_id = 0;
        $bonusInfo = [];
        if ($type == 2) {

            /* 订单红包均摊到订单商品， 检测红包是否支持店铺商品参与均摊 start */
            if ($bonus_id > 0) {
                /* 购物车商品总金额[排除活动商品：夺宝奇兵、拍卖、超值礼包等，仅支持普通商品] */
                $sql = [
                    'where' => [
                        [
                            'name' => 'extension_code',
                            'value' => ''
                        ],
                        [
                            'name' => 'rec_type',
                            'value' => 0
                        ],
                        [
                            'name' => 'is_gift',
                            'value' => 0
                        ],
                        [
                            'name' => 'parent_id',
                            'value' => 0
                        ]
                    ]
                ];

                $bonusSumList = BaseRepository::getArraySqlGet($cart_goods, $sql);
                $bonusSubtotal = BaseRepository::getArraySum($bonusSumList, 'subtotal');

                if (count($ruList) > 1) {
                    $bonusInfo = UserBonus::where('bonus_id', $bonus_id)->where('user_id', $user_id);
                    $bonusInfo = $bonusInfo->with([
                        'getBonusType' => function ($query) {
                            $query->select('type_id', 'usebonus_type', 'user_id');
                        }
                    ]);

                    $bonusInfo = BaseRepository::getToArrayFirst($bonusInfo);

                    /* [0|自主使用，1|平台和店铺通用] */
                    $useType = $bonusInfo['get_bonus_type']['usebonus_type'] ?? 0;
                    $bonus_ru_id = $bonusInfo['get_bonus_type']['user_id'] ?? 0;
                }

                /* 自主使用时，店铺商品不计算均摊 */
                if ($useType == 0 && count($ruList) > 1 && $bonusInfo) {
                    if ($bonus_ru_id > 0) {
                        $sql = [
                            'where' => [
                                [
                                    'name' => 'ru_id',
                                    'value' => $bonus_ru_id
                                ],
                                [
                                    'name' => 'extension_code',
                                    'value' => ''
                                ],
                                [
                                    'name' => 'rec_type',
                                    'value' => 0
                                ],
                                [
                                    'name' => 'is_gift',
                                    'value' => 0
                                ],
                                [
                                    'name' => 'parent_id',
                                    'value' => 0
                                ]
                            ]
                        ];
                        $ruGoodsList = BaseRepository::getArraySqlGet($cart_goods, $sql);

                        $disAmountAll = BaseRepository::getArraySum($ruGoodsList, 'dis_amount');
                        $bonusSubtotal = BaseRepository::getArraySum($ruGoodsList, 'subtotal');
                    } else {
                        $sql = [
                            'where' => [
                                [
                                    'name' => 'ru_id',
                                    'value' => 0
                                ],
                                [
                                    'name' => 'extension_code',
                                    'value' => ''
                                ],
                                [
                                    'name' => 'rec_type',
                                    'value' => 0
                                ],
                                [
                                    'name' => 'is_gift',
                                    'value' => 0
                                ],
                                [
                                    'name' => 'parent_id',
                                    'value' => 0
                                ]
                            ]
                        ];
                        $sellerList = BaseRepository::getArraySqlGet($cart_goods, $sql);

                        $disAmountAll = BaseRepository::getArraySum($sellerList, 'dis_amount');
                        $bonusSubtotal = BaseRepository::getArraySum($sellerList, 'subtotal');
                    }
                } else {
                    $disAmountAll = BaseRepository::getArraySum($cart_goods, 'dis_amount');
                }

                $bonusSubtotal = $bonusSubtotal - $disAmountAll;
            }
            /* 订单红包均摊到订单商品， 检测红包是否支持店铺商品参与均摊 end */
        }

        foreach ($cart_goods as $key => $val) {

            $cartOther = [];

            /* 获取商家优惠券 */
            $couponsGoods = $couponsShareEqually[$val['ru_id']]['couponsGoods'] ?? [];
            $couponSubtotal = $couponsShareEqually[$val['ru_id']]['couponSubtotal'] ?? [];
            $couponsInfo = $couponsShareEqually[$val['ru_id']]['couponsInfo'] ?? [];

            $is_general_goods = $val['extension_code'] != 'package_buy' && $val['is_gift'] == 0 && $val['parent_id'] == 0;
            $keySubtotal = $val['goods_price'] * $val['goods_number'];

            /* 订单红包均摊到订单商品， 检测红包是否支持店铺商品参与均摊 start */
            $isShareAlike = 1;
            if ($useType == 0) {
                if ($bonusInfo) {
                    if ($bonus_ru_id > 0) {
                        if ($val['ru_id'] == 0) {
                            $isShareAlike = 0;
                        } else {
                            $isShareAlike = 1;
                        }
                    } else {
                        if ($val['ru_id'] > 0) {
                            $isShareAlike = 0;
                        } else {
                            $isShareAlike = 1;
                        }
                    }
                }
            }

            $cartOther[$key]['goods_bonus'] = 0;
            if ($type == 2) {
                $order['bonus'] = $order['bonus'] ?? 0;
                if ($is_general_goods === true && $order['bonus'] > 0 && $bonusSubtotal > 0) {
                    if ($val['goods_price'] > 0 && $isShareAlike == 1) {
                        $cartOther[$key]['goods_bonus'] = (($keySubtotal - $val['dis_amount']) / $bonusSubtotal) * $order['bonus'];
                        $cartOther[$key]['goods_bonus'] = $this->dscRepository->changeFloat($cartOther[$key]['goods_bonus']);
                    } else {
                        $cartOther[$key]['goods_bonus'] = 0;
                    }
                } else {
                    $cartOther[$key]['goods_bonus'] = 0;
                }

                $cart_goods[$key]['goods_bonus'] = $cartOther[$key]['goods_bonus'] ?? 0;
            }
            /* 订单红包均摊到订单商品， 检测红包是否支持店铺商品参与均摊 end */

            /* 订单优惠券均摊到订单商品， 检测优惠券是否支持商品参与均摊 start */
            $coupons_money = $couponsInfo['cou_money'] ?? 0;
            $cartOther[$key]['goods_coupons'] = 0;
            if ($type == 1) {
                if ($uc_id > 0 && $couponsGoods) {
                    if ($couponsGoods['ru_id'] == $val['ru_id'] && $is_general_goods === true && $coupons_money > 0 && $couponSubtotal > 0) {
                        $cat_id = $val['cat_id'] ?? 0;
                        if ($couponsGoods && $val['goods_price'] > 0) {
                            $cartOther[$key]['goods_coupons'] = 0;

                            if ($couponsGoods['is_coupons'] == 1) {
                                $cartOther[$key]['goods_coupons'] = (($keySubtotal - $val['dis_amount']) / $couponSubtotal) * $coupons_money;
                                $cartOther[$key]['goods_coupons'] = $this->dscRepository->changeFloat($cartOther[$key]['goods_coupons']);
                            } elseif ($couponsGoods['is_coupons'] == 2) {
                                if ($couponsGoods['cou_goods'] && in_array($val['goods_id'], $couponsGoods['cou_goods'])) {
                                    $cartOther[$key]['goods_coupons'] = (($keySubtotal - $val['dis_amount']) / $couponSubtotal) * $coupons_money;
                                    $cartOther[$key]['goods_coupons'] = $this->dscRepository->changeFloat($cartOther[$key]['goods_coupons']);
                                }
                            } elseif ($couponsGoods['is_coupons'] == 3) {
                                if ($cat_id > 0 && in_array($cat_id, $couponsGoods['spec_cat'])) {
                                    $cartOther[$key]['goods_coupons'] = (($keySubtotal - $val['dis_amount']) / $couponSubtotal) * $coupons_money;
                                    $cartOther[$key]['goods_coupons'] = $this->dscRepository->changeFloat($cartOther[$key]['goods_coupons']);
                                }
                            }
                        }
                    }
                } else {
                    $cartOther[$key]['goods_coupons'] = 0;
                }

                $cart_goods[$key]['goods_coupons'] = $cartOther[$key]['goods_coupons'] ?? 0;
            }
            /* 订单优惠券均摊到订单商品， 检测优惠券是否支持商品参与均摊 end */
        }

        /* 核对均摊优惠券商品金额 */
        if ($type == 1) {
            $coupon_cart_goods = BaseRepository::getGroupBy($cart_goods, 'ru_id');

            if (count($ruList) > 1) {
                $arr = [];
                foreach ($coupon_cart_goods as $ruId => $row) {

                    $row = array_values($row);

                    $couponsInfo = $couponsShareEqually[$ruId]['couponsInfo'] ?? [];
                    $coupons_money = $couponsInfo['cou_money'] ?? 0;

                    $row = BaseRepository::valueErrorArray($row, 'goods_coupons', 'rec_id', $coupons_money);

                    $arr[$ruId] = $row;
                }

                $cart_goods = ArrRepository::getArrCollapse($arr);
            } else {
                $ru_id = $ruList[0] ?? 0;
                $couponsInfo = $couponsShareEqually[$ru_id]['couponsInfo'] ?? [];
                $coupons_money = $couponsInfo['cou_money'] ?? 0;

                $cart_goods = BaseRepository::valueErrorArray($cart_goods, 'goods_coupons', 'rec_id', $coupons_money);
            }
        }

        /* 核对均摊红包商品金额 */
        if ($type == 2) {
            $goods_bonus = $order['bonus'] ?? 0;
            $cart_goods = BaseRepository::valueErrorArray($cart_goods, 'goods_bonus', 'rec_id', $goods_bonus);
        }

        $other = [];
        if (in_array($type, [1, 2])) {
            foreach ($cart_goods as $k => $v) {

                if ($type == 1) {
                    $other['goods_coupons'] = $v['goods_coupons'];
                } else {
                    $other['goods_bonus'] = $v['goods_bonus'];
                }

                Cart::where('rec_id', $v['rec_id'])->update($other);
            }
        }

        return $cart_goods;
    }

    /**
     * 获取主订单配送方式列表
     *
     * @param string $shippingIdList
     * @return array
     */
    private function mainOrderShippingList($shippingIdList = '')
    {
        $shippingIdList = BaseRepository::getExplode($shippingIdList);

        $shipping_id = [];
        if ($shippingIdList) {
            foreach ($shippingIdList as $key => $val) {
                $shipping = BaseRepository::getExplode($val, '|');

                $ru_id = $shipping[0] ?? 0;
                $shipping_id[$ru_id] = $shipping[1] ?? 0;
            }
        }

        return $shipping_id;
    }

    /**
     * 订单均摊积分金额
     *
     * @param array $cart_goods
     * @param array $order
     * @param array $total
     * @return array
     */
    public function orderEquallyGoodsIntegral($cart_goods = [], $order = [], $total = [])
    {
        $is_integral = $order['is_integral'] ?? 0;

        $integralTotalList = [];
        if ($is_integral > 0) {
            if ($total['integral_money'] > 0) {
                $sql = [
                    'where' => [
                        [
                            'name' => 'integral_total',
                            'value' => 0,
                            'condition' => '>' //条件查询
                        ]
                    ]
                ];
                $integralGoods = BaseRepository::getArraySqlGet($cart_goods, $sql);
                $integralGoods = BaseRepository::getSortBy($integralGoods, 'integral_total', 'desc');

                if ($integralGoods) {
                    $goodsIntegralTotal = BaseRepository::getArraySum($integralGoods, 'integral_total');

                    $manyIntegral = 0;
                    $integralTotalList = [];
                    foreach ($integralGoods as $key => $val) {
                        $integral = CalculateRepository::math_div($val['integral_total'], $goodsIntegralTotal);
                        $integralMathDiv = $this->dscRepository->changeFloat($integral);

                        $integralTotalList[$key]['rec_id'] = $val['rec_id'];
                        $integralTotalList[$key]['goods_id'] = $val['goods_id'];
                        $integralMoneyPrice = $integralMathDiv * $total['integral_money'];

                        /* 处理使用所有金额时出现有异常问题 */
                        if ($manyIntegral > 0) {
                            $integralMoneyPrice += $manyIntegral;
                        }

                        if ($integralMoneyPrice > $val['integral_total']) {
                            $manyIntegral += $integralMoneyPrice - $val['integral_total'];
                            $integralMoneyPrice = $val['integral_total'];
                        }

                        $integralTotalList[$key]['integral_money'] = $this->dscRepository->changeFloat($integralMoneyPrice);
                        $integralTotalList[$key]['integral'] = $this->dscRepository->integralOfValue($integralMoneyPrice);
                        $integralTotalList[$key]['integral_money'] = $this->dscRepository->valueOfIntegral($integralTotalList[$key]['integral']);
                    }

                    $integralTotalList = BaseRepository::valueErrorArray($integralTotalList, 'integral_money', 'rec_id', $total['integral_money']);

                    foreach ($integralTotalList as $key => $value) {

                        if ($key == 0) {
                            $maxIntegral = BaseRepository::getArraySum($integralTotalList, 'integral');

                            if ($maxIntegral > $total['integral']) {
                                $value['integral'] -= ($maxIntegral - $total['integral']);
                            }
                        }

                        Cart::where('rec_id', $value['rec_id'])->update(
                            [
                                'goods_integral' => $value['integral'],
                                'goods_integral_money' => $value['integral_money']
                            ]
                        );
                    }
                }
            } else {
                $recList = BaseRepository::getKeyPluck($cart_goods, 'rec_id');
                Cart::whereIn('rec_id', $recList)->update(
                    [
                        'goods_integral' => 0,
                        'goods_integral_money' => 0
                    ]
                );
            }
        }

        return $integralTotalList;
    }

    /**
     * 主订单商品列表
     *
     * @param int $order_id
     * @param int $user_id
     * @return mixed
     */
    private function separateOrderGoods($order_id = 0, $user_id = 0)
    {
        $res = OrderGoods::where('order_id', $order_id)->where('user_id', $user_id);
        $res = BaseRepository::getToArrayGet($res);

        return $res;
    }

    /**
     * 获取子订单信息
     *
     * @param $order_id
     * @return array
     */
    public function getChildOrderInfo($order_id)
    {
        $res = OrderInfo::select('order_id', 'order_sn', 'order_amount', 'shipping_fee', 'shipping_name', 'money_paid', 'surplus')->where('main_order_id', $order_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$key]['order_sn'] = $row['order_sn'];
                $arr[$key]['order_id'] = $row['order_id'];
                $arr[$key]['shipping_name'] = $row['shipping_name'];

                $arr[$key]['order_amount'] = $row['order_amount'];
                $arr[$key]['amount_formated'] = $this->dscRepository->getPriceFormat($row['order_amount'], false);
                $arr[$key]['shipping_fee_formated'] = $this->dscRepository->getPriceFormat($row['shipping_fee'], false);

                $arr[$key]['pay_total'] = $row['money_paid'] + $row['surplus'];
                $arr[$key]['total_formated'] = $this->dscRepository->getPriceFormat($arr[$key]['pay_total'], false);
            }
        }

        return $arr;
    }
}
