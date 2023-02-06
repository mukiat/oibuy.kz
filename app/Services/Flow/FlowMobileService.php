<?php

namespace App\Services\Flow;

use App\Exceptions\HttpException;
use App\Jobs\ProcessSeparateBuyOrder;
use App\Models\AutoSms;
use App\Models\BargainStatisticsLog;
use App\Models\BonusType;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Crons;
use App\Models\ExchangeGoods;
use App\Models\Goods;
use App\Models\GoodsActivity;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\PackageGoods;
use App\Models\PresaleActivity;
use App\Models\Products;
use App\Models\Region;
use App\Models\RegionWarehouse;
use App\Models\SeckillGoods;
use App\Models\SellerShopinfo;
use App\Models\Shipping;
use App\Models\SolveDealconcurrent;
use App\Models\StoreOrder;
use App\Models\TeamLog;
use App\Models\UserBonus;
use App\Models\UserMembershipCard;
use App\Models\UserOrderNum;
use App\Models\Users;
use App\Models\ValueCard;
use App\Models\ValueCardType;
use App\Plugins\UserRights\Discount\Services\DiscountRightsService;
use App\Repositories\Cart\CartRepository;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscEncryptRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\StrRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Flow\FlowRepository;
use App\Repositories\Order\OrderRepository;
use App\Services\Activity\BonusService;
use App\Services\Activity\CouponsService;
use App\Services\Activity\ValueCardService;
use App\Services\Cart\CartCommonService;
use App\Services\Cart\CartGoodsService;
use App\Services\Category\CategoryService;
use App\Services\Cgroup\CgroupService;
use App\Services\Coupon\CouponsUserService;
use App\Services\CrossBorder\CrossBorderService;
use App\Services\Erp\JigonManageService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Goods\GoodsMobileService;
use App\Services\Goods\GoodsProdutsService;
use App\Services\Goods\GoodsWarehouseService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\OfflineStore\OfflineStoreService;
use App\Services\Order\OrderCommonService;
use App\Services\Package\PackageGoodsService;
use App\Services\Payment\PaymentService;
use App\Services\Shipping\ShippingDataHandleService;
use App\Services\Shipping\ShippingService;
use App\Services\Team\TeamService;
use App\Services\User\UserAddressService;
use App\Services\User\UserCommonService;
use Illuminate\Support\Carbon;

// 收银台代码
use App\Events\PickupOrdersPayedAfterDoSomething;

/**
 *
 * Class FlowMobileService
 * @package App\Services\Flow
 */
class FlowMobileService
{
    protected $couponsService;
    protected $BonusService;
    protected $valueCardService;
    protected $userAddressService;
    protected $shippingService;
    protected $paymentService;
    protected $offlineStoreService;
    protected $commonService;
    protected $userCommonService;
    protected $commonRepository;
    protected $jigonManageService;
    protected $dscRepository;
    protected $goodsCommonService;
    protected $goodsWarehouseService;
    protected $sessionRepository;
    protected $bonusService;
    protected $cartCommonService;
    protected $flowUserService;
    protected $orderCommonService;
    protected $packageGoodsService;
    protected $cartRepository;
    protected $cartGoodsService;
    protected $categoryService;
    protected $goodsProdutsService;
    protected $flowOrderService;
    protected $couponsUserService;
    protected $flowService;
    protected $flowActivityService;

    public function __construct(
        UserAddressService $userAddressService,
        PaymentService $paymentService,
        OfflineStoreService $offlineStoreService,
        CommonRepository $commonRepository,
        DscRepository $dscRepository,
        GoodsCommonService $goodsCommonService,
        GoodsWarehouseService $goodsWarehouseService,
        SessionRepository $sessionRepository,
        UserCommonService $userCommonService,
        CartCommonService $cartCommonService,
        FlowUserService $flowUserService,
        OrderCommonService $orderCommonService,
        PackageGoodsService $packageGoodsService,
        CartRepository $cartRepository,
        CartGoodsService $cartGoodsService,
        CategoryService $categoryService,
        GoodsProdutsService $goodsProdutsService,
        CouponsService $couponsService,
        BonusService $bonusService,
        ValueCardService $valueCardService,
        ShippingService $shippingService,
        FlowOrderService $flowOrderService,
        CouponsUserService $couponsUserService,
        FlowService $flowService,
        FlowActivityService $flowActivityService
    )
    {
        //加载外部类
        $files = [
            'clips',
            'common',
            'main',
            'order',
            'function',
            'base',
            'goods',
            'ecmoban'
        ];
        load_helper($files);
        $this->cartGoodsService = $cartGoodsService;
        $this->categoryService = $categoryService;
        $this->goodsProdutsService = $goodsProdutsService;
        $this->couponsService = $couponsService;
        $this->bonusService = $bonusService;
        $this->valueCardService = $valueCardService;
        $this->userAddressService = $userAddressService;
        $this->shippingService = $shippingService;
        $this->paymentService = $paymentService;
        $this->offlineStoreService = $offlineStoreService;
        $this->commonRepository = $commonRepository;
        $this->jigonManageService = app(JigonManageService::class);
        $this->dscRepository = $dscRepository;
        $this->goodsCommonService = $goodsCommonService;
        $this->goodsWarehouseService = $goodsWarehouseService;
        $this->sessionRepository = $sessionRepository;
        $this->userCommonService = $userCommonService;
        $this->cartCommonService = $cartCommonService;
        $this->flowUserService = $flowUserService;
        $this->orderCommonService = $orderCommonService;
        $this->packageGoodsService = $packageGoodsService;
        $this->cartRepository = $cartRepository;
        $this->flowOrderService = $flowOrderService;
        $this->couponsUserService = $couponsUserService;
        $this->flowService = $flowService;
        $this->flowActivityService = $flowActivityService;
    }

    /**
     * 订单信息确认
     *
     * @param $uid
     * @param int $rec_type
     * @param int $t_id
     * @param int $team_id
     * @param int $bs_id
     * @param int $store_id
     * @param int $type_id
     * @param int $leader_id
     * @return array
     * @throws \Exception
     */
    public function orderInfo($uid, $rec_type = 0, $t_id = 0, $team_id = 0, $bs_id = 0, $store_id = 0, $type_id = 0, $leader_id = 0)
    {
        $flow_type = isset($rec_type) ? intval($rec_type) : CART_GENERAL_GOODS;

        if ($uid > 0) {
            $rank = $this->userCommonService->getUserRankByUid($uid);
            $user_rank['rank_id'] = isset($rank['rank_id']) ? $rank['rank_id'] : 1;
            $user_rank['discount'] = isset($rank['discount']) ? $rank['discount'] / 100 : 1;
        }

        // 购物车商品
        $where = [
            'user_id' => $uid,
            'rec_type' => $rec_type,
            'store_id' => $store_id,
            'is_checked' => 1,
            'discount' => $user_rank['discount'] ?? 1,
            'user_rank' => $user_rank['rank_id'] ?? 0,
        ];
        $cart_goods = $this->cartGoodsService->getGoodsCartList($where);

        if (empty($cart_goods)) {
            return [];
        }

        $countRu = BaseRepository::getArraySum($cart_goods, 'ru_id');
        $drpUserAudit = cache('drp_user_audit_' . $uid) ?? 0;

        $drp_show_price = config('shop.drp_show_price') ?? 0;
        if (empty($drpUserAudit) && $countRu > 0 && $drp_show_price == 1) {
            return [];
        }

        if (CROSS_BORDER === true) { // 跨境多商户
            $cbec = app(CrossBorderService::class)->cbecExists();

            if (!empty($cbec)) {
                $excess = $cbec->mobile_check_kj_price($cart_goods);
                if (!empty($excess)) {
                    $store_name = app(MerchantCommonService::class)->getShopName($excess['ru_id'], 1);

                    $msg['error'] = 'excess';
                    $msg['msg'] = $store_name . lang('common.cross_order_excess') . $this->dscRepository->getPriceFormat($excess['limited_amount']);
                    return $msg;
                }
            }
        }

        $list = $this->GoodsInCartByUser($uid, $cart_goods);

        /* 如果能开发票，取得发票内容列表 */
        $invoice_content = trim(config('shop.invoice_content'));
        if (config('shop.can_invoice') == 1 && $invoice_content != '' && $flow_type != CART_EXCHANGE_GOODS) {

            $inv_content_list = $invoice_content ? explode("\n", str_replace("\r", '', $invoice_content)) : [];

            //默认发票计算
            $list['total']['need_inv'] = 1;
            $list['total']['inv_type'] = '';
            $list['total']['inv_payee'] = lang('common.personal');
            $list['total']['inv_content'] = $inv_content_list[0] ?? '';
        }

        /* 税额 */
        if (config('shop.can_invoice') == 1 && isset($list['total']['inv_content'])) {
            $list['total']['tax'] = CommonRepository::orderInvoiceTotal($list['total']['goods_price'], $list['total']['inv_content']);
        } else {
            $list['total']['tax'] = 0;
        }

        $list['total']['tax_formated'] = $this->dscRepository->getPriceFormat($list['total']['tax'], true, true, $list['goodsRuSelf']);

        $list['user_rank'] = $user_rank['rank_id'] ?? 0;

        // 社区驿站
        if (file_exists(MOBILE_GROUPBUY) && !empty(config('shop.open_community_post'))) {
            $list['consignee'] = $this->change_consignee($uid, 0, $leader_id);
        } else {
            /*收货人地址*/
            $list['consignee'] = $this->change_consignee($uid);
        }

        if (!isset($list['consignee']['province']) && $store_id <= 0) {
            $msg['error'] = 'address';
            $msg['msg'] = lang('common.address_prompt_two');
            return $msg;
        }

        $list['consignee']['province'] = $list['consignee']['province'] ?? 0;
        $list['consignee']['city'] = $list['consignee']['city'] ?? 0;
        $list['consignee']['district'] = $list['consignee']['district'] ?? 0;
        $list['consignee']['street'] = $list['consignee']['street'] ?? 0;

        $list['consignee']['province_name'] = $this->DeliveryArea($list['consignee']['province']);
        $list['consignee']['city_name'] = $this->DeliveryArea($list['consignee']['city']);
        $list['consignee']['district_name'] = $this->DeliveryArea($list['consignee']['district']);
        $list['consignee']['street_name'] = $this->DeliveryArea($list['consignee']['street']);

        $result = [];
        foreach ($list['goods_list'] as $key => $value) {
            $result[$value['shop_name']][] = $value;
        }

        $ret = [];
        foreach ($result as $key => $value) {
            array_push($ret, $value);
        }

        $shipping_rec = [];
        $rec_list = BaseRepository::getKeyPluck($cart_goods, 'rec_id');
        $goods_list = [];
        $ruList = BaseRepository::getKeyPluck($cart_goods, 'ru_id');

        /* 初始化当前购物车商品活动均摊金额为0元 */
        if ($rec_list) {
            $other = [
                'goods_bonus' => 0,
                'goods_coupons' => 0,
                'goods_favourable' => 0,
                'goods_integral' => 0,
                'goods_integral_money' => 0,
            ];
            Cart::whereIn('rec_id', $rec_list)->update($other);
        }

        /* 商家ID */
        $list['total']['rec_list'] = $rec_list;
        $list['total']['consignee'] = [
            "province" => $list['consignee']['province'],
            "city" => $list['consignee']['city'],
            "district" => $list['consignee']['district'],
            "street" => $list['consignee']['street']
        ];

        // 社区驿站
        if (file_exists(MOBILE_GROUPBUY) && !empty(config('shop.open_community_post'))) {
            $is_support = 1; // 订单所有商家支持社区驿站
            $sellerShopList = MerchantDataHandleService::SellerShopinfoDataList($ruList, ['ru_id', 'open_community_post']);
        } else {
            $is_support = 0;
        }

        $is_kj = 0;
        if (CROSS_BORDER === true) { // 跨境多商户

            $stepsFieldsList = MerchantDataHandleService::getMerchantsStepsFieldsDataList($ruList, ['user_id', 'source']);
            $sourceList = BaseRepository::getKeyPluck($stepsFieldsList, 'source');
            $sourceList = ArrRepository::getArrayUnset($sourceList);
            $sourceList = BaseRepository::getArrayUnique($sourceList);

            if ($sourceList) {
                foreach ($sourceList as $sk => $sv) {
                    if (!in_array($sv, [SOURCE_DOMESTIC])) {
                        $is_kj += 1;
                    }
                }
            }

            $is_kj = $is_kj ? 1 : 0;
        }

        $list['is_kj'] = $is_kj;
        $list['total']['is_kj'] = $is_kj;

        $list['total']['free_shipping_fee'] = 0;//初始免邮金额为0

        $shippingCartGoodsList = $this->cartCommonService->shippingCartGoodsList($cart_goods);
        $shippingList = $this->shippingService->goodsShippingTransport($shippingCartGoodsList, $list['consignee']);

        $shipping_fee = 0;
        foreach ($ret as $k => $v) {
            foreach ($v as $key => $val) {
                $goods_list[$k]['cross_warehouse_name'] = $val['goods'][0]['cross_warehouse_name'] ?? '';
                $goods_list[$k]['shop_name'] = $val['shop_name'];
                $goods_list[$k]['ru_id'] = $val['ru_id'];
                $goods_list[$k]['goods'] = $val['goods'];
                $goods_list[$k]['goods_count'] = BaseRepository::getArraySum($val['goods'], 'goods_number');
                $amount = BaseRepository::getArraySum($val['goods'], ['goods_price', 'goods_number']);
                $goods_list[$k]['amount'] = $this->dscRepository->getPriceFormat($amount, true, true, $list['goodsRuSelf']);

                $ruShipping = $shippingList[$val['ru_id']] ?? [];
                $goods_list[$k]['shipping'] = $ruShipping;

                $shipping_fee += $ruShipping['default_shipping']['shipping_fee'];

                /* 不支持配送 */
                if (isset($ruShipping['shipping_rec']) && $ruShipping['shipping_rec']) {
                    $shipping_rec[] = $ruShipping['shipping_rec'];
                }

                // 社区驿站
                if (file_exists(MOBILE_GROUPBUY)) {
                    if ($is_support > 0) {
                        $shopInfo = $sellerShopList[$val['ru_id']] ?? [];
                        $open_communtiy_post = $shopInfo['open_community_post'] ?? 0;
                        $is_support = $open_communtiy_post ? $open_communtiy_post : 0;
                    } else {
                        $is_support = 0;
                    }
                }

                //自营有自提点--key=ru_id
                if ($val['ru_id'] == 0 && $list['consignee']['district'] > 0) {
                    $point_id = 0;
                    $self_point = $this->shippingService->getSelfPoint($list['consignee']['district'], $point_id);

                    if (!empty($self_point)) {
                        $goods_list[$k]['self_point'] = $self_point;
                    }
                }
            }
        }

        // 社区驿站
        if (file_exists(MOBILE_GROUPBUY) && !empty(config('shop.open_community_post'))) {
            $post = app(CgroupService::class)->postExists();
            if (!empty($post) && $is_support == 1) {
                $address = $list['consignee']['province_name'] . $list['consignee']['city_name'] . $list['consignee']['district_name'] . $list['consignee']['consignee'];
                $list['consignee']['nearbyleader'] = $post->nearleader($list['consignee']['address_id'], 0);
                $location = $post->addressChange($address);
                $list['consignee']['lat'] = $location['lat'] ?? 0;
                $list['consignee']['lng'] = $location['lng'] ?? 0;
            }
        }

        $shipping_rec = BaseRepository::getFlatten($shipping_rec);
        $shipping_rec = ArrRepository::getArrayUnset($shipping_rec);

        $list['isshipping_list'] = BaseRepository::getArrayDiff($rec_list, $shipping_rec); //支持配送购物车商品ID
        $list['isshipping_list'] = $list['isshipping_list'] ? array_values($list['isshipping_list']) : [];

        $list['noshipping_list'] = $shipping_rec; //不支持配送购物车商品ID
        $list['noshipping_list'] = $list['noshipping_list'] ? array_values($list['noshipping_list']) : [];

        $product = [];
        foreach ($list['product'] as $k => $v) {
            $product[$k] = $v['goods'];
        }

        /* 处理优惠活动商品均摊金额 start */
        if (!empty($goods_list)) {
            $favourableGoodsList = $goods_list;
            $favourableGoodsList = $this->flowActivityService->getFavourableCartGoodsList($favourableGoodsList, $uid);
            $favourableGoodsList = $this->flowActivityService->merchantActivityCartGoodsList($favourableGoodsList);
            $cartFavGoods = $this->favourableGoodsListDiscount($favourableGoodsList);
            $discount = $cartFavGoods['discount'];
            $list['goods_list'] = $cartFavGoods['cartGoodsList'];
            $list['get_goods_list'] = $cartFavGoods['get_goods_list'];
        } else {
            $discount = 0;
            $list['goods_list'] = [];
            $list['get_goods_list'] = [];
        }
        /* 处理优惠活动商品均摊金额 end */

        /* 计算折扣 */
        $list['total']['discount'] = $discount;
        if ($list['total']['discount'] > $list['total']['goods_price']) {
            $list['total']['discount'] = $list['total']['goods_price'];
        }

        $list['total']['discount'] = $list['total']['discount'] + $list['total']['dis_amount'];
        $list['total']['discount_formated'] = $this->dscRepository->getPriceFormat($list['total']['discount'], true, true, $list['goodsRuSelf']);

        $list['coupons_list'] = [];
        if (config('shop.use_coupons') == 1 && in_array($flow_type, [CART_GENERAL_GOODS, CART_ONESTEP_GOODS, CART_OFFLINE_GOODS])) {
            $coupons_list_all = $this->couponsService->flowUserCoupons($uid, $cart_goods, true, $list['consignee'], $shipping_fee);

            // 取得用户可用优惠券
            $user_coupons = $coupons_list_all['coupons_list'] ?? [];

            if (!empty($user_coupons)) {
                foreach ($user_coupons as $k => $v) {

                    $cou_end_time = $v['cou_end_time'];
                    if ($v['valid_type'] == 2) {
                        $cou_end_time = $v['valid_time'];
                    }

                    $user_coupons[$k]['cou_money'] = $v['cou_money'] = !empty($v['uc_money']) ? $v['uc_money'] : $v['cou_money'];
                    $user_coupons[$k]['cou_money_formated'] = $this->dscRepository->getPriceFormat($v['cou_money'], true, true, $list['goodsRuSelf']);

                    $user_coupons[$k]['cou_end_time'] = TimeRepository::getLocalDate('Y-m-d', $cou_end_time);
                    $user_coupons[$k]['cou_type_name'] = CommonRepository::couTypeFormat($v['cou_type']);

                    if (!empty($v['spec_cat'])) {
                        $user_coupons[$k]['cou_goods_name'] = lang('common.lang_goods_coupons.is_cate');
                    } elseif (!empty($v['cou_goods'])) {
                        $user_coupons[$k]['cou_goods_name'] = lang('common.lang_goods_coupons.is_goods');
                    } else {
                        $user_coupons[$k]['cou_goods_name'] = lang('common.lang_goods_coupons.is_all');
                    }
                }

                $list['coupons_list'] = $user_coupons;
            }
        }

        $list['total']['coupons_count'] = isset($user_coupons) ? count($user_coupons) : 0;
        $list['total']['discount'] = $list['total']['discount'] ?? 0;
        $list['total']['bonus_money'] = 0;
        $list['total']['bonus_id'] = 0;//红包id
        $list['total']['card'] = 0;
        $list['total']['card_money'] = 0;
        $list['total']['coupons_money'] = 0;
        $list['total']['coupons_id'] = 0;//优惠券id
        $list['total']['vc_dis'] = 1;

        if ($list['total']['goods_price'] >= $list['total']['discount']) {
            $list['total']['amount'] = $list['total']['goods_price'] - $list['total']['discount'];
        } else {
            $list['total']['amount'] = 0;
        }

        $list['total']['amount'] = $list['total']['amount'] + $list['total']['tax'];

        $list['total']['amount_formated'] = $this->dscRepository->getPriceFormat($list['total']['amount'], true, true, $list['goodsRuSelf']);
        $list['total']['goods_price_formated'] = $this->dscRepository->getPriceFormat($list['total']['goods_price'], true, true, $list['goodsRuSelf']);
        $list['total']['integral'] = 0;
        $list['total']['integral_money'] = 0;
        $list['total']['integral_money_formated'] = $this->dscRepository->getPriceFormat($list['total']['integral_money'], true, true, $list['goodsRuSelf']);
        $list['total']['value_card_id'] = 0;//储值卡id

        //积分商城商品添加商品对应的积分
        $list['total']['exchange_integral'] = 0;
        if ($rec_type == CART_EXCHANGE_GOODS) {
            $list['total']['exchange_integral'] = ExchangeGoods::where('goods_id', $cart_goods[0]['goods_id'])->value('exchange_integral');
        }

        /* 取得货到付款手续费 */
        $cod_fee = 0;
        // 显示余额支付
        $is_balance = empty($list['is_kj']) ? 1 : 0;
        // 银行转账
        $is_bank = 1;

        /*取得支付列表*/
        $payment_list = $this->paymentService->availablePaymentList(1, $cod_fee, 0, $is_balance, $is_bank);

        if ($payment_list) {
            foreach ($payment_list as $key => $payment) {
                /* 如果积分商城商品、拼团商品、门店自提商品、虚拟商品、商家商品不显示货到付款  */
                if (in_array($flow_type, [CART_EXCHANGE_GOODS, CART_TEAM_GOODS, CART_OFFLINE_GOODS]) || $list['total']['real_goods_count'] == 0 || $list['total']['seller_goods_count'] > 0) {
                    if ($payment ['pay_code'] == 'cod') {
                        unset($payment_list[$key]);
                    }
                }
            }
            $list['payment_list'] = $payment_list ? array_values($payment_list) : [];
        }

        // 红包
        $list['bonus_list'] = [];
        if ($rec_list && config('shop.use_bonus') == 1 && in_array($flow_type, [CART_GENERAL_GOODS, CART_ONESTEP_GOODS, CART_OFFLINE_GOODS])) {
            $list['bonus_list'] = $this->bonusService->getUserBonusInfo($uid, $list['total']['bonus_goods_price'], $list['total']['seller_amount']);//可用的红包列表

            foreach ($list['bonus_list'] as $k => $val) {
                $list['bonus_list'][$k]['use_start_date'] = TimeRepository::getLocalDate(config('shop.time_format'), $val['use_start_date']);
                $list['bonus_list'][$k]['use_end_date'] = TimeRepository::getLocalDate(config('shop.time_format'), $val['use_end_date']);
                unset($list['bonus_list'][$k]['get_bonus_type']);
            }
        }

        $user_info = $this->UserIntegral($uid);

        // 用户信息
        $list['user_info'] = $user_info;

        // 消费积分使用
        $list['allow_use_integral'] = 0;
        $list['integral'] = [];
        if (config('shop.use_integral') == 1 && in_array($flow_type, [CART_GENERAL_GOODS, CART_ONESTEP_GOODS, CART_OFFLINE_GOODS])) {

            $order_integral = $this->flowActivityService->getFlowAvailablePoints($shippingCartGoodsList);

            if ($order_integral > 0) {
                if ($user_info['pay_points'] >= $order_integral) {
                    $list['integral'][0]['integral'] = $order_integral;
                    $list['integral'][0]['integral_money'] = $this->dscRepository->valueOfIntegral($order_integral);
                    $list['integral'][0]['integral_money_formated'] = $this->dscRepository->getPriceFormat($list['integral'][0]['integral_money'], true, true, $list['goodsRuSelf']);
                }

                $list['allow_use_integral'] = empty($list['integral']) ? 0 : 1;
            }
        }

        $list['total']['allow_use_integral'] = $list['allow_use_integral'];
        $list['total']['use_integral_money'] = $list['integral'][0]['integral_money'] ?? 0;

        /*判断储值卡能否使用*/
        $list['use_value_card'] = 0;
        $list['value_card'] = [];
        if (config('shop.use_value_card') == 1 && $flow_type != CART_EXCHANGE_GOODS) {
            $value_card = $this->valueCardService->getUserValueCard($uid, $product);

            $is_kj = $is_kj ?? 0;
            if ((!empty($value_card) && isset($value_card['is_value_cart'])) || $is_kj == 1) {
                $value_card = [];
            }

            $list['value_card'] = $value_card;

            $list['use_value_card'] = empty($value_card) ? 0 : 1;
        }

        /*判断余额是否足够*/
        $list['use_surplus'] = 0;
        if (config('shop.use_surplus') == 1) {
            $use_surplus = $user_info['user_money'] ?? 0;
            $shipping_fee = 0;
            foreach ($list['goods_list'] as $v) {
                if (isset($v['shipping']['default_shipping']['shipping_fee'])) {
                    $shipping_fee += $v['shipping']['default_shipping']['shipping_fee'];
                }
            }

            if ($use_surplus > 0 && empty($list['is_kj'])) {
                $list['use_surplus'] = 1;
                $list['user_money'] = $use_surplus;  // 账户余额
                $list['user_money_formated'] = $this->dscRepository->getPriceFormat($use_surplus, true, true, $list['goodsRuSelf']);
            }
        }

        // 如果开启用户支付密码配置
        $list['use_paypwd'] = 0;
        $list['use_paypwd_open'] = 0; // 用户是否开启支付密码
        if (config('shop.use_paypwd') == 1) {
            try {
                $this->flowService->check_user_paypwd($uid);
            } catch (HttpException $httpException) {
                if ($httpException->getCode() == 1) {
                    // 未开启
                    $list['use_paypwd_open'] = 0;
                } else {
                    $list['use_paypwd_open'] = 1;
                }
            }

            // 可使用余额，且用户有余额 或  能使用储值卡  显示支付密码
            if ($list['use_surplus'] == 1 || $list['use_value_card'] == 1) {
                $list['use_paypwd'] = 1;
            }
        }

        /**
         * 判断门店自提
         */
        $list['store_lifting'] = 0;
        if ($product) {

            $productStoreList = BaseRepository::getKeyPluck($product, 'store_id');
            $productStoreList = BaseRepository::getArrayUnique($productStoreList);
            $product_store_id = $productStoreList[0] ?? 0;

            if ($product_store_id > 0) {
                $list['store'] = $this->offlineStoreService->infoOfflineStore($product_store_id);
                $list['store_lifting'] = 1;

                $list['isshipping_list'] = BaseRepository::getKeyPluck($product, 'rec_id');
                $list['noshipping_list'] = [];
            }
        }

        // 门店自提时间与手机号
        if ($store_id > 0) {
            $store_cart = Cart::select('store_mobile', 'take_time')
                ->whereIn('rec_id', $rec_list)
                ->where('store_id', $store_id);
            $list['store_cart'] = BaseRepository::getToArrayFirst($store_cart);
        } else {
            $list['store_cart'] = [];
        }

        $list['can_invoice'] = 0;
        /*是否支持开发票*/
        if (config('shop.can_invoice') == 1) {
            $list['can_invoice'] = 1;
        }

        $list['how_oos'] = 0;
        /*支持缺货处理*/
        if (config('shop.use_how_oos') == 1) {
            $list['how_oos'] = 1;
        }

        // 购物车商品类型
        $list['flow_type'] = $flow_type;

        //砍价返回标识
        if ($bs_id) {
            $list['bs_id'] = $bs_id; //砍价参与id
        }

        //拼团返回标识
        if ($t_id) {
            $list['t_id'] = $t_id;       //拼团活动id
            $list['team_id'] = $team_id; //拼团开团id
        }

        // 团购支付保证金标识
        $list['is_group_deposit'] = 0;
        if ($flow_type == 1) {
            $group_buy = GoodsActivity::select('act_id', 'ext_info')->where('act_id', $type_id)
                ->where('act_type', GAT_GROUP_BUY);
            $group_buy = BaseRepository::getToArrayFirst($group_buy);

            if ($group_buy && $group_buy['ext_info']) {
                $ext_info = unserialize($group_buy['ext_info']);
                $group_buy = array_merge($group_buy, $ext_info);

                if ($group_buy['deposit'] > 0) {
                    $list['is_group_deposit'] = 1;
                }
            }
        }

        //发票内容
        if (config('shop.can_invoice') == 1) {
            $list['invoice_content'] = explode("\n", str_replace("\r", '', config('shop.invoice_content')));
        }

        $list['total']['ru_id'] = BaseRepository::getKeyPluck($list['goods_list'], 'ru_id');

        return $list;
    }

    /**
     * 提交订单
     *
     * @param int $uid
     * @param array $flow
     * @return string|array
     * @throws \Exception
     */
    public function done($uid = 0, $flow = [])
    {
        $done_cart_value = $flow['cart_value'];
        $done_cart_value = DscEncryptRepository::filterValInt($done_cart_value);

        /* 取得购物类型 */
        $flow_type = isset($flow['flow_type']) ? intval($flow['flow_type']) : CART_GENERAL_GOODS;
        $flow_type = ($flow['flow_type'] == CART_ONESTEP_GOODS) ? CART_ONESTEP_GOODS : $flow_type;
        $store_id = isset($flow['store_id']) ? intval($flow['store_id']) : 0;  // 门店id
        $leader_id = isset($flow['leader_id']) ? intval($flow['leader_id']) : 0;  // 社区驿站ID

        $doneCartGoodsList = $this->doneCartGoodsList($uid, $done_cart_value, $flow_type, $store_id);
        $recList = BaseRepository::getKeyPluck($doneCartGoodsList, 'rec_id');

        /* 检查购物车中是否有商品 */
        if (empty($doneCartGoodsList)) {
            return ['error' => 1, 'msg' => lang('flow.cart_empty_goods')];
        }

        /* 检查商品、货品库存 start */
        /* 如果使用库存，且下订单时减库存，则减少库存 */
        //--库存管理use_storage 1为开启 0为未启用-------  SDT_PLACE：0为发货时 1为下订单时
        if (config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_PLACE) {
            try {
                $_cart_goods_stock = $this->cartCommonService->cartGoodsNumber($done_cart_value, $uid, $flow['area_id'], $flow['area_city'], [$flow_type]);

                $this->cartCommonService->getFlowCartStock($_cart_goods_stock, $store_id, $flow['warehouse_id'], $flow['area_id'], $flow['area_city']);

            } catch (HttpException $httpException) {
                return ['error' => $httpException->getCode(), 'msg' => $httpException->getMessage()];
            }
        }
        /* 检查商品库存 end */

        /* 订单队列 先进先出 */
        $time = TimeRepository::getGmTime();
        DscEncryptRepository::SolveDealInsert($uid, $done_cart_value, $time);

        $order_fifo = DscEncryptRepository::order_fifo($uid, $done_cart_value, $time);

        if ($order_fifo['error'] == 1) {
            return ['error' => 1, 'msg' => lang('flow.flow_salve_error')];
        } elseif ($order_fifo['error'] == 2) {
            return ['error' => 1, 'msg' => sprintf(lang('shopping_flow.stock_insufficiency'), $order_fifo['msg'], $order_fifo['number'], $order_fifo['number'])];
        }

        // 会员等级
        $user_rank = $this->userCommonService->getUserRankByUid($uid);

        // 社区驿站
        if (file_exists(MOBILE_GROUPBUY) && $leader_id > 0) {
            $consignee = $this->change_consignee($uid, 0, $leader_id);
        } else {
            $consignee = $this->change_consignee($uid);
        }

        $consignee['country'] = $consignee['country'] ?? 0;
        $consignee['province'] = $consignee['province'] ?? 0;
        $consignee['city'] = $consignee['city'] ?? 0;
        $consignee['district'] = $consignee['district'] ?? 0;

        /* 检查收货人信息是否完整 */
        if (!$this->flowUserService->checkConsigneeInfo($consignee, $flow_type, $uid) && $store_id <= 0) {
            return ['error' => 1, 'msg' => lang('common.not_set_address')];
        }

        $area_info = RegionWarehouse::select('region_id', 'regionId', 'region_name', 'parent_id')->where('regionId', $consignee['province']);
        $area_info = BaseRepository::getToArrayFirst($area_info);

        $area_city = RegionWarehouse::select('region_id', 'regionId', 'region_name', 'parent_id')->where('regionId', $consignee['city']);
        $area_city = BaseRepository::getToArrayFirst($area_city);

        $area_info['region_id'] = isset($area_info['region_id']) ? $area_info['region_id'] : 0;
        $area_city['region_id'] = isset($area_city['region_id']) ? $area_city['region_id'] : 0;

        $total['how_oos'] = isset($flow['how_oos']) ? intval($flow['how_oos']) : 0;
        $total['card_message'] = isset($flow['card_message']) ? $this->compile_str($flow['card_message']) : '';
        $total['inv_type'] = !empty($flow['inv_type']) ? $this->compile_str($flow['inv_type']) : '';
        $total['inv_payee'] = isset($flow['inv_payee']) ? $this->compile_str($flow['inv_payee']) : '';
        $total['tax_id'] = isset($flow['tax_id']) ? $this->compile_str($flow['tax_id']) : '';
        $total['inv_content'] = isset($flow['inv_content']) ? $this->compile_str($flow['inv_content']) : '';

        $msg = $flow['postscript'];
        $ru_id_arr = $flow['ru_id']; // 商家id数组

        if (!is_array($ru_id_arr) && $ru_id_arr == 0) {
            $ru_id_arr = explode(',', $ru_id_arr);
        } else {
            $ru_id_arr = BaseRepository::getExplode($ru_id_arr);
        }

        $shipping_arr = $flow['shipping'] ? $flow['shipping'] : []; // 配送方式数组
        $shipping_type = $flow['shipping_type'] ? $flow['shipping_type'] : [];
        $shipping_code = $flow['shipping_code'] ? $flow['shipping_code'] : [];

        $point_id = $flow['point_id'];
        $shipping_dateStr = $flow['shipping_dateStr'];

        $shipping = [];
        if (!empty($shipping_arr) && !in_array(0, $shipping_arr)) {
            // 订单 分单配送方式
            if (count($shipping_arr) == 1) {
                $shipping['shipping_id'] = $shipping_arr['0'] ?? 0;
                $shipping['shipping_type'] = $shipping_type['0'] ?? 0;
                $shipping['shipping_code'] = $shipping_code['0'] ?? 0;
            } else {
                $shipping = FlowRepository::get_order_post_shipping($shipping_arr, $shipping_code, $shipping_type, $ru_id_arr);
            }
        }

        // 分单上门自提
        $point_info = [];
        if ($point_id && count($ru_id_arr) == 1) {
            $point_info['point_id'] = $point_id['0'] ?? 0;
            $point_info['shipping_dateStr'] = $shipping_dateStr['0'] ?? '';
        } else {
            $point_info = $this->get_order_points($point_id, $shipping_dateStr, $ru_id_arr);
        }

        // 分单买家留言
        if ($msg && count($ru_id_arr) == 1) {
            $postscript = $msg['0'] ?? '';
        } else {
            $postscript = FlowRepository::get_order_post_postscript($msg, $ru_id_arr);
        }

        $order = [
            'shipping_id' => $shipping['shipping_id'] ?? 0,
            'shipping_type' => $shipping['shipping_type'] ?? '',
            'shipping_code' => $shipping['shipping_code'] ?? '',
            'support_cod' => $shipping['support_cod'] ?? 0,
            'pay_id' => intval($flow['pay_id']),
            'pack_id' => isset($flow['pack']) ? intval($flow['pack']) : 0,
            'card_id' => isset($flow['card']) ? intval($flow['card']) : 0,
            'card_message' => trim($flow['card_message']),
            'surplus' => isset($flow['surplus']) ? floatval($flow['surplus']) : 0.00,
            'integral' => isset($flow['integral']) ? intval($flow['integral']) : 0,
            'use_integral' => isset($flow['use_integral']) ? intval($flow['use_integral']) : 0,
            'is_surplus' => isset($flow['is_surplus']) ? intval($flow['is_surplus']) : 0,
            'bonus_id' => isset($flow['bonus_id']) ? $flow['bonus_id'] : 0,
            'bonus' => isset($flow['bonus']) ? $flow['bonus'] : 0,
            'uc_id' => isset($flow['uc_id']) ? $flow['uc_id'] : 0, //优惠券id bylu
            'vc_id' => isset($flow['vc_id']) ? $flow['vc_id'] : 0, //储值卡ID
            'need_inv' => empty($flow['need_inv']) ? 0 : 1,
            'tax_id' => isset($flow['tax_id']) ? trim($flow['tax_id']) : '', //纳税人识别号
            'inv_type' => isset($flow['inv_type']) ? $flow['inv_type'] : 1,
            'inv_payee' => isset($flow['inv_payee']) ? trim($flow['inv_payee']) : '个人',
            'invoice_id' => isset($flow['invoice_id']) ? $flow['invoice_id'] : 0,
            'invoice' => isset($flow['invoice']) ? $flow['invoice'] : 1,
            'invoice_type' => isset($flow['inv_type']) ? $flow['inv_type'] : 1,
            'inv_content' => isset($flow['inv_content']) ? trim($flow['inv_content']) : '不开发票',
            'vat_id' => isset($flow['vat_id']) ? $flow['vat_id'] : 0,
            'postscript' => $postscript ?? '',
            'how_oos' => trans('common.oos.' . $flow['how_oos']),
            'need_insure' => isset($flow['need_insure']) ? intval($flow['need_insure']) : 0,
            'user_id' => $uid,
            'add_time' => $time,
            'order_status' => OS_CONFIRMED,
            'shipping_status' => SS_UNSHIPPED,
            'pay_status' => PS_UNPAYED,
            'agency_id' => $this->get_agency_by_regions([$consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']]),
            'point_id' => empty($point_info['point_id']) ? 0 : $point_info['point_id'],
            'shipping_dateStr' => empty($point_info['shipping_dateStr']) ? '' : $point_info['shipping_dateStr'],
            'mobile' => isset($flow['store_mobile']) && !empty($flow['store_mobile']) ? addslashes(trim($flow['store_mobile'])) : '',
            'referer' => !empty($flow['referer']) ? $flow['referer'] : 'H5', // 订单来源
        ];

        if (file_exists(MOBILE_WXAPP) && isset($flow['media_type']) && !empty($flow['media_type'])) {
            //微信视频号订单
            $order['media_type'] = $flow['media_type'];
        }

        $order['uc_id'] = DscEncryptRepository::filterValInt($order['uc_id']);

        if (CROSS_BORDER === true) { // 跨境多商户
            $order['rel_name'] = $flow['rel_name'] ?? '';
            $order['id_num'] = $flow['id_num'] ?? '';
            if (!empty($order['rel_name']) && !empty($order['id_num'])) {
                // 实名认证验证
                $cbecService = app(CrossBorderService::class)->cbecExists();

                $consignee['address_id'] = isset($flow['address_id']) && !empty($flow['address_id']) ? $flow['address_id'] : $consignee['address_id'];

                $real_data = [
                    'rel_name' => $order['rel_name'],
                    'id_num' => $order['id_num'],
                ];
                $config['identity_auth_status'] = config('shop.identity_auth_status') ?? 0;
                // 开启验证
                if ($config['identity_auth_status'] == 1) {
                    // 未通过或编辑时必验证
                    $check = false;
                    // 判断修改实名信息时
                    if (empty($consignee['rel_name']) || empty($consignee['id_num']) || $consignee['rel_name'] != $order['rel_name'] || $consignee['id_num'] != $order['id_num']) {
                        $check = true;
                    }
                    // 验证
                    if ((isset($consignee['real_status']) && $consignee['real_status'] == 0) || $check == true) {
                        // 请求接口
                        $res = $cbecService->checkIdentity($order['rel_name'], $order['id_num']);
                        if ($res == true) {
                            // 保存为已实名认证
                            $real_data['real_status'] = 1;
                        } else {
                            // 实名认证信息不匹配
                            $real_data['real_status'] = 0;
                            $cbecService->updateUserAddress($uid, $consignee['address_id'], $real_data);
                            return ['error' => 1, 'msg' => lang('flow.user_real_info_error')];
                        }
                    }
                }
                // 保存实名信息与状态
                $cbecService->updateUserAddress($uid, $consignee['address_id'], $real_data);
                // 保存订单
                $consignee['rel_name'] = $order['rel_name'];
                $consignee['id_num'] = $order['id_num'];
            }
        }

        if (file_exists(MOBILE_GROUPBUY)) {
            if (!empty($consignee['leader_id'])) {
                $order['use_community_post'] = 1; // 订单使用社区驿站
                $order['leader_id'] = $consignee['leader_id']; //社区驿站的团长ID
            }
        }

        /* 扩展信息 */
        if (isset($flow_type) && $flow_type != CART_GENERAL_GOODS && $flow_type != CART_ONESTEP_GOODS) {
            $order['extension_code'] = $flow['extension_code'];
            $order['extension_id'] = $flow['extension_id'];
        } else {
            $order['extension_code'] = '';
            $order['extension_id'] = 0;
        }

        if ($flow_type == CART_BARGAIN_GOODS) { // 砍价
            $order['extension_code'] = 'bargain_buy';
            $order['extension_id'] = $flow['bs_id'] ?? 0;
        }
        if ($flow_type == CART_TEAM_GOODS) {// 拼团
            $order['extension_code'] = 'team_buy';
        }
        if ($flow_type == CART_SECKILL_GOODS) {// 秒杀
            $order['extension_code'] = 'seckill';
        }
        if ($flow_type == CART_GROUP_BUY_GOODS) {//团购
            $order['extension_code'] = 'group_buy';
        }
        if ($flow_type == CART_EXCHANGE_GOODS) {// 积分兑换
            $order['extension_code'] = 'exchange_goods';
        }
        if ($flow_type == CART_PRESALE_GOODS) {// 预售
            $order['extension_code'] = 'presale';
        }
        if ($flow_type == CART_AUCTION_GOODS) {// 拍卖
            $order['extension_code'] = 'auction';
        }
        // 超值礼包
        if ($flow_type == CART_PACKAGE_GOODS) {
            $cart_package = collect($doneCartGoodsList)->where('extension_code', 'package_buy')->first();
            $order['extension_code'] = 'package_buy';
            $order['extension_id'] = $cart_package['goods_id'];
        }

        $user_info = $this->UserIntegral($uid);

        $integral_total = BaseRepository::getArraySum($doneCartGoodsList, 'integral_total');

        /* 检查积分余额是否合法 */
        $user_id = $uid;
        if ($user_id > 0) {
            $order['surplus'] = min($order['surplus'], $user_info['user_money'] + $user_info['credit_line']);
            if ($order['surplus'] < 0) {
                $order['surplus'] = 0;
            }

            // 该订单允许使用的积分
            $flow_points = $this->dscRepository->integralOfValue($integral_total);

            $user_points = $user_info['pay_points']; // 用户的积分总数

            $order['integral'] = min($order['integral'], $user_points, $flow_points);

            if ($order['integral'] < 0) {
                $order['integral'] = 0;
            }
        } else {
            $order['surplus'] = 0;
            $order['integral'] = 0;
        }

        //未开启使用积分，积分归0
        if ($flow['use_integral'] == 0) {
            $order['integral'] = 0;
        }

        if ($order['integral'] <= 0 && $integral_total > 0) {
            Cart::whereIn('rec_id', $recList)->where('user_id', $uid)->update(['goods_integral' => 0]);
        }

        $cart_goods_list = cart_goods($flow_type, $done_cart_value, 1, $consignee, $store_id, $uid); // 取得商品列表，计算合计

        /* 订单中的商品 */
        $cart_goods = $this->dscRepository->turnPluckFlattenOne($cart_goods_list);
        $cart_goods = $this->cartCommonService->shippingCartGoodsList($cart_goods);

        if (empty($cart_goods)) {
            return ['error' => 1, 'msg' => lang('flow.mobile_cartnot_goods')];
        }

        /* 订单商品总额 */
        $cart_total = BaseRepository::getArraySum($cart_goods, ['goods_price', 'goods_number']);

        /* 检查红包是否存在 */
        if ($order['bonus_id'] > 0) {
            $bonus = $this->bonus_info($order['bonus_id']);
            $bonus['min_goods_amount'] = $bonus['min_goods_amount'] ?? 0;
            if (empty($bonus) || $bonus['user_id'] != $user_id || $bonus['order_id'] > 0 || $bonus['min_goods_amount'] > $cart_total) {
                $order['bonus_id'] = 0;
            }
        } elseif (isset($flow['bonus_sn'])) {
            $bonus_sn = trim($flow['bonus_sn']);
            $bonus = $this->bonus_info(0, $bonus_sn);
            if (empty($bonus) || $bonus['user_id'] > 0 || $bonus['order_id'] > 0 || $bonus['min_goods_amount'] > $cart_total || $time > $bonus['use_end_date']) {
            } else {
                if ($user_id > 0) {
                    UserBonus::where('bonus_id', $bonus['bonus_id'])->update(['user_id' => $user_id]);
                }
                $order['bonus_id'] = $bonus['bonus_id'];
                $order['bonus_sn'] = $bonus_sn;
            }
        }

        /* 检查储值卡ID是否存在 */
        if ($order['vc_id'] > 0) {
            $value_card = $this->valueCardService->orderValueCardInfo($order['vc_id'], '', $uid);

            if (empty($value_card) || $value_card['user_id'] != $user_id) {
                $order['vc_id'] = 0;
            }
        } elseif (isset($flow['value_card_psd'])) {
            $value_card_psd = trim($flow['value_card_psd']);
            $value_card = $this->valueCardService->orderValueCardInfo(0, $value_card_psd);
            if (!(empty($value_card) || $value_card['user_id'] > 0)) {
                if ($user_id > 0 && empty($value_card['end_time'])) {
                    $end_time = TimeRepository::getLocalStrtoTime("+" . $value_card['vc_indate'] . " months ");
                    $other = [
                        'user_id' => $user_id,
                        'bind_time' => $time,
                        'end_time' => $end_time
                    ];
                    ValueCard::where('vid', $value_card['vid'])->update($other);

                    $order['vc_id'] = $value_card['vid'];
                    $order['vc_psd'] = $value_card_psd;
                } elseif ($time > $value_card['end_time']) {
                    $order['vc_id'] = 0;
                }
            }
        }

        /* 检查优惠券是否存在 */
        if (!empty($order['uc_id'])) {
            $couponsUserList = $this->couponsUserService->getCouponsUserSerList($order['uc_id'], $user_id, $cart_goods);

            $sql = [
                'where' => [
                    [
                        'name' => 'is_use',
                        'value' => 0
                    ]
                ]
            ];
            $couponsUserList = BaseRepository::getArraySqlGet($couponsUserList, $sql);
            $order['uc_id'] = BaseRepository::getKeyPluck($couponsUserList, 'uc_id');
        }

        $order['uc_id'] = !empty($order['uc_id']) ? BaseRepository::getImplode($order['uc_id']) : '';

        // 开通购买会员权益卡验证
        if (file_exists(MOBILE_DRP) && isset($flow['order_membership_card_id']) && $flow['order_membership_card_id'] > 0) {
            $memberCardInfo = app(DiscountRightsService::class)->getMemberCardInfo($user_id, $flow['order_membership_card_id']);
            if (empty($memberCardInfo)) {
                $flow['order_membership_card_id'] = 0;
            } else {
                if (isset($memberCardInfo['membership_card_order_goods']) && $memberCardInfo['membership_card_order_goods']) {
                    // 合并订单商品数组 用于分别记录订单商品权益卡折扣
                    $cart_goods = merge_arrays($cart_goods, $memberCardInfo['membership_card_order_goods']);
                }
            }
        }

        /* 检查商品总额是否达到最低限购金额 */
        if (($flow_type == CART_GENERAL_GOODS || $flow_type == CART_ONESTEP_GOODS) && $cart_total < config('shop.min_goods_amount')) {
            return ['error' => 1, 'msg' => lang('flow.not_meet_low_purchase_limit')];
        }

        /* 收货人信息 */
        foreach ($consignee as $key => $value) {
            if ($key == 'mobile' && !empty($order['mobile'])) {
                $order[$key] = $order['mobile'];  //门店取货手机号
            } else {
                $order[$key] = !empty($value) ? addslashes($value) : '';
            }
        }

        $order['email'] = !empty($order['email']) ? $order['email'] : ($user_info['email'] ?? '');

        /* 判断是不是实体商品 */
        foreach ($cart_goods as $val) {
            /* 统计实体商品的个数 */
            if (isset($val['is_real']) && $val['is_real']) {
                $is_real_good = 1;
            }
        }

        // 虚拟商品不用选择配送方式
        if (isset($is_real_good)) {
            if (empty($order['shipping_id']) && empty($point_id) && empty($store_id)) {
                return ['error' => 1, 'msg' => lang('flow.please_checked_shipping')];
            }
        }

        // 必段选择支付方式
        if (empty($order['pay_id'])) {
            return ['error' => 1, 'msg' => lang('flow.please_checked_pay')];
        }

        /* 支付方式 */
        $payment = [];
        if ($order['pay_id'] > 0) {
            $payment = payment_info($order['pay_id']);
            $order['pay_name'] = addslashes($payment['pay_name']);
            $order['is_online'] = 1;
        }

        $ruShipping = BaseRepository::getArrayCombine($ru_id_arr, $shipping_arr);

        //切换配送方式
        if ($cart_goods_list) {
            foreach ($cart_goods_list as $key => $val) {
                $cart_goods_list[$key]['tmp_shipping_id'] = $ruShipping[$val['ru_id']] ?? 0;
            }
        }

        $total = order_fee($order, $cart_goods, $consignee, $done_cart_value, $cart_goods_list, $store_id, $flow['store_type'], $uid, $user_rank['rank_id'], $flow_type);

        /* 判断提交的订单商品是否支持配送方式 */
        if (!empty($total['no_shipping_cart_list'])) {

            $noShippingCartList = Cart::select('rec_id', 'goods_name')->whereIn('rec_id', $total['no_shipping_cart_list']);
            $noShippingCartList = BaseRepository::getToArrayGet($noShippingCartList);

            $goods_name = BaseRepository::getKeyPluck($noShippingCartList, 'goods_name');
            $goods_name = $goods_name ? implode('，', $goods_name) : '';
            $remarks = lang('flow.no_shipping_cart_list_propmt');
            $remarks = $goods_name ? '【' . $goods_name . '】' . $remarks : $remarks;

            return ['error' => 1, 'msg' => $remarks];
        }

        if (CROSS_BORDER === true) {
            // 跨境多商户
            /*
             * 计算订单的费用
             */
            $type = array(
                'type' => 0,
                'shipping_list' => $shipping_arr,
                'step' => 0,
            );

            $web = app(CrossBorderService::class)->webExists();

            if (!empty($web)) {
                $arr = [
                    'consignee' => $consignee ?? '',
                    'rec_type' => $flow_type ?? 0,
                    'store_id' => $store_id ?? 0,
                    'cart_value' => $done_cart_value ?? '',
                    'type' => $type ?? 0,
                    'uc_id' => $order['uc_id'] ?? 0
                ];
                $amount = $web->assignNewRatePriceMobileDone($cart_goods_list, $total['amount'], $arr);
                $total['amount'] = $amount['amount'];
                $total['amount_formated'] = $amount['amount_formated'];
                $order['rate_fee'] = $amount['rate_price'];
                $order['format_rate_fee'] = $amount['format_rate_fee'];
            }
        }

        // 开通购买会员权益卡 应付金额 = 原应付金额 - 折扣差价 + 购买权益卡金额
        if (file_exists(MOBILE_DRP) && isset($flow['order_membership_card_id']) && $flow['order_membership_card_id'] > 0) {
            if (isset($memberCardInfo) && !empty($memberCardInfo)) {
                $total['amount'] = $total['amount'] - $flow['membership_card_discount_price'] + $memberCardInfo['membership_card_buy_money'];
            }
        }

        $order['bonus'] = isset($total['bonus']) ? $total['bonus'] : 0;
        $order['coupons'] = isset($total['coupons']) ? $total['coupons'] : 0; //优惠券金额 bylu
        $order['use_value_card'] = isset($total['use_value_card']) ? $total['use_value_card'] : 0; //储值卡使用金额
        $order['vc_dis_money'] = isset($total['vc_dis_money']) ? $total['vc_dis_money'] : 0; //储值卡使用金额
        $order['vc_rec_list'] = $total['vc_rec_list'] ?? []; // 储值卡购物车商品ID
        $order['goods_amount'] = $total['goods_price'];
        $order['cost_amount'] = isset($total['cost_price']) ? $total['cost_price'] : 0;
        $order['discount'] = $total['discount'] ? $total['discount'] : 0;
        $order['surplus'] = isset($total['surplus']) ? $total['surplus'] : 0;
        $order['tax'] = isset($total['tax']) ? $total['tax'] : 0;
        $order['dis_amount'] = isset($total['dis_amount']) ? $total['dis_amount'] : 0;
        $order['ru_shipping_fee_list'] = $total['ru_shipping_fee_list'] ?? [];

        // 购物车中的商品能享受红包支付的总额
        $discount_amout = compute_discount_amount($done_cart_value, $uid, $flow_type);

        // 红包和积分最多能支付的金额为商品总额
        $temp_amout = $order['goods_amount'] - $discount_amout;
        if ($temp_amout <= 0) {
            $order['bonus_id'] = 0;
        }

        /* 配送方式 ecmoban模板堂 --zhuo */
        if (!empty($order['shipping_id'])) {

            $shipping_arr = BaseRepository::getExplode($shipping_arr);

            if (count($shipping_arr) == 1) {
                $shipping = shipping_info($order['shipping_id']);
            }
            $order['shipping_isarr'] = 0;
            $order['shipping_name'] = addslashes($shipping['shipping_name']);
            $order['shipping_code'] = addslashes($shipping['shipping_code']);
            $shipping_name = !empty($order['shipping_name']) ? explode(",", $order['shipping_name']) : '';
            if ($shipping_name && count($shipping_name) > 1) {
                $order['shipping_isarr'] = 1;
            }
        }

        $order['shipping_fee'] = isset($total['shipping_fee']) ? $total['shipping_fee'] : 0;
        $order['insure_fee'] = isset($total['shipping_insure']) ? $total['shipping_insure'] : 0;

        $order['pay_fee'] = isset($total['pay_fee']) ? $total['pay_fee'] : 0;
        $order['cod_fee'] = isset($total['cod_fee']) ? $total['cod_fee'] : 0;

        /* 商品包装 */
        if ($order['pack_id'] > 0) {
            $pack = pack_info($order['pack_id']);
            $order['pack_name'] = addslashes($pack['pack_name']);
        }
        $order['pack_fee'] = isset($total['pack_fee']) ? $total['pack_fee'] : 0;

        /* 祝福贺卡 */
        if ($order['card_id'] > 0) {
            $card = card_info($order['card_id']);
            $order['card_name'] = addslashes($card['card_name']);
        }
        $order['card_fee'] = isset($total['card_fee']) ? $total['card_fee'] : 0;

        $order['order_amount'] = number_format($total['amount'], 2, '.', '');

        // 在线支付输入了一个金额(含部分使用余额),检查余额是否足够
        if ($order['is_surplus'] == 1 && $order['surplus'] > 0) {
            if ($order['surplus'] > ($user_info['user_money'] + $user_info['credit_line'])) {
                return ['error' => 1, 'msg' => lang('flow.balance_not_enough')];
            }
        }
        /* 如果全部使用余额支付，检查余额是否足够 */
        if ($payment['pay_code'] == 'balance' && $order['order_amount'] > 0) {
            if ($order['surplus'] > 0) { //余额支付里如果输入了一个金额
                $order['order_amount'] = $order['order_amount'] + $order['surplus'];
                $order['surplus'] = 0;
            }

            if ($order['order_amount'] > ($user_info['user_money'] + $user_info['credit_line'])) {
                $order['surplus'] = $user_info['user_money'];
                $order['order_amount'] = $order['order_amount'] - $user_info['user_money'];
                return ['error' => 1, 'msg' => lang('shopping_flow.balance_not_enough')];
            } else {
                if ($flow_type == CART_PRESALE_GOODS || $flow_type == CART_GROUP_BUY_GOODS) {
                    //余额支付 预售、团购-- 首次付定金
                    $order['surplus'] = $order['order_amount'];
                    $order['pay_status'] = PS_PAYED_PART; //部分付款
                    $order['order_status'] = OS_CONFIRMED; //已确认
                    $order['order_amount'] = $order['goods_amount'] + $order['shipping_fee'] + $order['insure_fee'] + $order['tax'] - $order['discount'] - $order['surplus'];
                } else {
                    $order['surplus'] = $order['order_amount'];
                    $order['order_amount'] = 0;
                }
            }

            $order['pay_name'] = isset($payment['pay_name']) ? addslashes($payment['pay_name']) : '';
            $order['pay_id'] = $payment['pay_id'] ?? 0;
            $order['is_online'] = 0;
        }

        // 开启配置支付密码 且使用余额 或 使用储值卡 验证支付密码
        $pay_pwd = e(trim($flow['pay_pwd']));
        if (config('shop.use_paypwd') == 1 && ($payment && $payment['pay_code'] == 'balance' || $order['vc_id'] > 0 || $order['is_surplus'] == 1)) {
            try {
                $this->flowService->check_user_paypwd($user_id, $pay_pwd);
            } catch (HttpException $httpException) {
                return ['error' => 1, 'msg' => $httpException->getMessage()];
            }
        }

        $snapshot = false; // 是否创建快照
        $stores_sms = 0; //门店提货码是否发送信息 0不发送  1发送
        /* 如果订单金额为0（使用余额或积分或红包支付），修改订单状态为已确认、已付款 */
        if ($order['order_amount'] <= 0) {
            $order['order_status'] = OS_CONFIRMED;
            $order['confirm_time'] = $time;
            $order['pay_status'] = PS_PAYED;
            $order['pay_time'] = $time;
            $order['order_amount'] = 0;
            $stores_sms = 1;
            $snapshot = true;
        }

        /* 预售订单已付款且金额为0, 使用储值卡付款、使用余额付款 首次付定金, 预售状态更改成部分付款*/
        if ($order['order_amount'] <= 0 && $flow_type == CART_PRESALE_GOODS && (!empty($order['use_value_card']) || $order['surplus'] > 0)) {
            $order['pay_status'] = PS_PAYED_PART;
            $order['order_amount'] = $order['goods_amount'] + $order['shipping_fee'] + $order['insure_fee'] + $order['tax'] - $order['discount'] - $order['surplus'] - $order['use_value_card'];
        }

        $order['integral_money'] = $total['integral_money'];
        $order['integral'] = $total['integral'];
        if ($order['extension_code'] == 'exchange_goods') {
            $order['integral_money'] = $this->dscRepository->valueOfIntegral($total['exchange_integral']);
            $order['integral'] = $total['exchange_integral'];
            $order['goods_amount'] = 0;
        }
        $order['from_ad'] = 0;

        $affiliate = unserialize(config('shop.affiliate'));
        if (isset($affiliate['on']) && $affiliate['on'] == 1 && $affiliate['config']['separate_by'] == 1) {
            //推荐订单分成
            $parent_id = CommonRepository::getUserAffiliate();
            if ($user_id == $parent_id) {
                $parent_id = 0;
            }
        } elseif (isset($affiliate['on']) && $affiliate['on'] == 1 && $affiliate['config']['separate_by'] == 0) {
            //推荐注册分成
            $parent_id = 0;
        } else {
            //分成功能关闭
            $parent_id = 0;
        }

        // 微分销
        $is_distribution = 0;
        $is_drp_goods = 0; // 是否分销 订单商品
        if (file_exists(MOBILE_DRP) && $order['extension_code'] == '') {
            // 订单分销条件
            $affiliate_drp_id = CommonRepository::getDrpShopAffiliate($uid); // 获取分享人 user_id 且必须是分销商

            $result = app(\App\Modules\Drp\Services\Drp\DrpService::class)->orderAffiliate($uid, $affiliate_drp_id);

            // 是否分销
            $is_distribution = $result['is_distribution'] ?? 0;
            $parent_id = $result['parent_id'] ?? 0; // 推荐人id
            // 是否分销订单
            $order['is_drp'] = $is_distribution;
        }

        $order['parent_id'] = $parent_id;

        /* 插入拼团信息记录 start */
        if (file_exists(MOBILE_TEAM) && $flow_type == CART_TEAM_GOODS) {
            $order['team_parent_id'] = 0;
            $order['team_user_id'] = 0;

            if (isset($flow['team_id']) && $flow['team_id'] > 0) {
                $team_info = TeamLog::where('team_id', $flow['team_id'])->first();
                $team_info = $team_info ? $team_info->toArray() : [];
                if (empty($team_info)) {
                    // 插入开团活动信息
                    $team_log_id = app(TeamService::class)->addTeamLog($uid, $flow_type, $flow);
                    $order['team_id'] = $team_log_id;
                    $order['team_parent_id'] = $uid;
                } else {
                    if ($team_info['status'] > 0) {
                        //参与拼团人数溢出时，开启新的团
                        $team_log_id = app(TeamService::class)->addTeamLog($uid, $flow_type, $flow);
                        $order['team_id'] = $team_log_id;
                        $order['team_parent_id'] = $uid;
                    } else {
                        // 参团
                        $order['team_id'] = $flow['team_id'];
                        $order['team_user_id'] = $uid;
                    }
                }
            } else {
                // 插入开团活动信息
                $team_log_id = app(TeamService::class)->addTeamLog($uid, $flow_type, $flow);
                $order['team_id'] = $team_log_id;
                $order['team_parent_id'] = $uid;
            }
        }
        /* 插入拼团信息记录 end */

        /* 插入订单表 */
        $new_order_id = 0;
        if ($cart_goods) {
            $error_no = 0;
            do {
                $order['order_sn'] = $this->orderCommonService->getOrderSn(); //获取新订单号
                $new_order = BaseRepository::getArrayfilterTable($order, 'order_info');
                try {
                    $new_order_id = OrderInfo::insertGetId($new_order);
                } catch (\Exception $e) {
                    $error_no = (stripos($e->getMessage(), '1062 Duplicate entry') !== false) ? 1062 : $e->getCode();

                    if ($error_no > 0 && $error_no != 1062) {
                        die($e->getMessage());
                    }
                }
            } while ($error_no == 1062); //如果是订单号重复则重新提交数据
        }

        $order['order_id'] = $new_order_id;

        $order_rec = [];
        if ($new_order_id > 0) {

            /* 订单优惠券均摊到订单商品， 检测优惠券是否支持商品参与均摊 start */
            $couponsShareEqually = $this->flowOrderService->orderCouponsShareEqually($cart_goods, $order, $uid);
            /* 订单优惠券均摊到订单商品， 检测优惠券是否支持商品参与均摊 end */

            $all_ru_id = BaseRepository::getKeyPluck($cart_goods, 'ru_id');
            $all_ru_id = BaseRepository::getArrayUnique($all_ru_id);

            /* 订单红包均摊到订单商品， 检测红包是否支持店铺商品参与均摊 start */
            $useType = 0;
            $bonus_ru_id = 0;
            $bonus_id = $order['bonus_id'] ?? 0;
            $bonusInfo = [];
            $bonusSubtotal = 0;
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

                if (count($all_ru_id) > 1) {
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
                if ($useType == 0 && count($all_ru_id) > 1 && $bonusInfo) {
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
            if (count($all_ru_id) == 1) {
                $goodsValueCardList = $this->flowOrderService->orderValueCardShareEqually($cart_goods, $order);
            }
            /* 订单储值卡均摊到订单商品 end */

            /* 红包 */
            $goods_bonus = 0;
            $bonus_list = [];

            /* 优惠券 */
            $goods_coupons = [];
            $coupons_list = [];

            $goods_id = BaseRepository::getKeyPluck($cart_goods, 'goods_id');

            $goods = GoodsDataHandleService::GoodsDataList($goods_id);
            $goodsExtend = GoodsDataHandleService::goodsExtendList($goods_id);

            $cart_goods_count = count($cart_goods);

            /* 均摊储值卡 */
            $goods_card_money = 0;
            $value_card_list = [];
            $goods_value_card_discount = 0;
            $value_card_discount_list = [];

            foreach ($cart_goods as $k => $v) {

                $v = BaseRepository::recursiveNullVal($v);

                /* 获取商家优惠券 */
                $goods_coupons[$v['ru_id']] = $goods_coupons[$v['ru_id']] ?? 0;
                $couponsGoods = $couponsShareEqually[$v['ru_id']]['couponsGoods'] ?? [];
                $couponSubtotal = $couponsShareEqually[$v['ru_id']]['couponSubtotal'] ?? [];
                $couponsInfo = $couponsShareEqually[$v['ru_id']]['couponsInfo'] ?? [];

                // 扩展商品信息
                $goods_extend = $goodsExtend[$v['goods_id']] ?? [];

                $order_goods = [];
                $order_goods['user_id'] = $uid;
                $order_goods['order_id'] = $order['order_id'];
                $order_goods['cart_recid'] = $v['rec_id'];
                $order_goods['goods_id'] = $v['goods_id'];
                $order_goods['goods_name'] = $v['goods_name'];
                $order_goods['goods_sn'] = $v['goods_sn'];
                $order_goods['product_id'] = $v['product_id'] ?? 0;
                $order_goods['product_sn'] = !empty($v['product_id']) ? $v['goods_sn'] : $v['product_sn'] ?? '';
                $order_goods['is_reality'] = $goods_extend['is_reality'] ?? 0;
                $order_goods['is_return'] = $goods_extend['is_return'] ?? 0;
                $order_goods['is_fast'] = $goods_extend['is_fast'] ?? 0;
                $order_goods['goods_number'] = $v['goods_number'];
                $order_goods['market_price'] = $v['market_price'];
                $order_goods['commission_rate'] = $v['commission_rate'];
                $order_goods['goods_price'] = $v['goods_price'];
                $order_goods['goods_attr'] = $v['goods_attr'];
                $order_goods['is_real'] = $v['is_real'];
                $order_goods['extension_code'] = $v['extension_code'] ?? '';
                $order_goods['parent_id'] = $v['parent_id'];
                $order_goods['is_gift'] = $v['is_gift'];
                $order_goods['model_attr'] = $v['model_attr'];
                $order_goods['goods_attr_id'] = $v['goods_attr_id'];
                $order_goods['ru_id'] = $v['ru_id'];
                $order_goods['shopping_fee'] = $v['shopping_fee'];
                $order_goods['warehouse_id'] = $v['warehouse_id'];
                $order_goods['area_id'] = $v['area_id'];
                $order_goods['area_city'] = $v['area_city'];
                $order_goods['freight'] = $v['freight'];
                $order_goods['tid'] = $v['tid'];
                $order_goods['shipping_fee'] = $v['shipping_fee'];
                $order_goods['cost_price'] = $v['cost_price'] ?? 0;
                $order_goods['dis_amount'] = $v['dis_amount'] ?? 0;
                $order_goods['goods_favourable'] = $v['goods_favourable'] ?? 0;
                $order_goods['goods_integral'] = $v['goods_integral'] ?? 0;
                $order_goods['goods_integral_money'] = $v['goods_integral_money'] ?? 0;

                /* 订单商品均摊 排除超值礼包和赠品 */
                $is_general_goods = $order_goods['extension_code'] != 'package_buy' && $v['is_gift'] == 0 && $v['parent_id'] == 0;
                $keySubtotal = $order_goods['goods_price'] * $order_goods['goods_number'];

                /* 订单红包均摊到订单商品， 检测红包是否支持店铺商品参与均摊 start */
                $isShareAlike = 1;
                if ($useType == 0 && $bonusInfo) {
                    if ($bonus_ru_id > 0) {
                        $isShareAlike = $v['ru_id'] == 0 ? 0 : 1;
                    } else {
                        $isShareAlike = $v['ru_id'] > 0 ? 0 : 1;
                    }
                }

                $order['bonus'] = $order['bonus'] ?? 0;
                $order_goods['goods_bonus'] = 0;
                if ($is_general_goods === true && $order['bonus'] > 0 && $bonusSubtotal > 0) {
                    if ($order_goods['goods_price'] > 0 && $isShareAlike == 1) {
                        $order_goods['goods_bonus'] = (($keySubtotal - $v['dis_amount']) / $bonusSubtotal) * $order['bonus'];
                    }

                    $order_goods['goods_bonus'] = $this->dscRepository->changeFloat($order_goods['goods_bonus']);

                    if ($order_goods['goods_bonus'] > 0) {
                        $bonus_list[$k]['goods_bonus'] = $order_goods['goods_bonus'];
                        $goods_bonus += $order_goods['goods_bonus'];
                    }
                }
                /* 订单红包均摊到订单商品， 检测红包是否支持店铺商品参与均摊 end */

                /* 订单优惠券均摊到订单商品， 检测优惠券是否支持商品参与均摊 start */
                $coupons_money = $couponsInfo['cou_money'] ?? 0;
                $order_goods['goods_coupons'] = 0;
                if ($couponsGoods && $couponsGoods['ru_id'] == $v['ru_id'] && $is_general_goods === true && $coupons_money > 0 && $couponSubtotal > 0) {
                    $cat_id = $v['cat_id'] ?? 0;
                    if ($order_goods['goods_price'] > 0) {
                        if ($couponsGoods['is_coupons'] == 1) {
                            $order_goods['goods_coupons'] = (($keySubtotal - $v['dis_amount']) / $couponSubtotal) * $coupons_money;
                        } elseif ($couponsGoods['is_coupons'] == 2) {
                            if ($couponsGoods['cou_goods'] && in_array($v['goods_id'], $couponsGoods['cou_goods'])) {
                                $order_goods['goods_coupons'] = (($keySubtotal - $v['dis_amount']) / $couponSubtotal) * $coupons_money;
                            }
                        } elseif ($couponsGoods['is_coupons'] == 3) {
                            if ($cat_id > 0 && in_array($cat_id, $couponsGoods['spec_cat'])) {
                                $order_goods['goods_coupons'] = (($keySubtotal - $v['dis_amount']) / $couponSubtotal) * $coupons_money;
                            }
                        }

                        $order_goods['goods_coupons'] = $this->dscRepository->changeFloat($order_goods['goods_coupons']);

                        if ($order_goods['goods_coupons'] > 0) {
                            $coupons_list[$v['ru_id']][$k]['goods_coupons'] = $order_goods['goods_coupons'];
                            $goods_coupons[$v['ru_id']] += $order_goods['goods_coupons'];
                        }
                    }
                }
                /* 订单优惠券均摊到订单商品， 检测优惠券是否支持商品参与均摊 end */

                /* 储值卡均摊金额 start */
                $goodsValueCard = $goodsValueCardList[$v['rec_id']] ?? [];

                if ($goodsValueCard) {
                    $order_goods['goods_value_card'] = $goodsValueCard['goods_value_card'];
                    $value_card_list[$k]['goods_value_card'] = $goodsValueCard['goods_value_card'];
                    $goods_card_money += $goodsValueCard['goods_value_card'];

                    if ($goodsValueCard['value_card_discount'] > 0) {
                        $order_goods['value_card_discount'] = $goodsValueCard['value_card_discount'];
                        $value_card_discount_list[$k]['value_card_discount'] = $goodsValueCard['value_card_discount'];
                        $goods_value_card_discount += $goodsValueCard['value_card_discount'];
                    }
                }
                /* 储值卡均摊金额 end */

                if (CROSS_BORDER === true) { // 跨境多商户
                    if (isset($rate['rate_arr']) && !empty($rate['rate_arr'])) {
                        foreach ($rate['rate_arr'] as $key => $val) {//插入跨境税费
                            if ($v['goods_id'] == $val['goods_id']) {
                                $order_goods['rate_price'] = $val['rate_price'];
                            }
                        }
                    }
                }

                // 原商品信息 非购物车商品信息
                $goodsInfo = $goods[$v['goods_id']] ?? [];
                $order_goods['commission_rate'] = $goodsInfo['commission_rate'] ?? 0;

                // 活动商品不参与分销 虚拟商品除外
                $is_distribution = ($order_goods['extension_code'] == '' || $order_goods['extension_code'] == 'virtual_card') ? $is_distribution : 0;
                $order_goods['is_distribution'] = isset($goodsInfo['is_distribution']) ? $goodsInfo['is_distribution'] * $is_distribution : 0;

                if (file_exists(MOBILE_DRP)) {
                    // 购买成为分销商商品订单
                    $order_goods['membership_card_id'] = $goodsInfo['membership_card_id'] ?? 0;
                    $order_goods['dis_commission'] = $goodsInfo['dis_commission'] ?? 0;

                    // 即是分销商品，又是会员卡指定购买商品，则优先使用会员卡商品中设置的【会员卡分销】分成奖励
                    if (isset($order_goods['membership_card_id']) && empty($order_goods['membership_card_id'])) {
                        // 计算订单商品佣金
                        $drp_order_goods = \App\Plugins\UserRights\DrpGoods\Services\DrpGoodsRightsService::drp_order_goods($v, $order_goods, $cart_goods_count, $order);
                        $order_goods['drp_goods_price'] = $drp_order_goods['drp_goods_price'] ?? 0;
                        $order_goods['drp_money'] = $drp_order_goods['drp_money'] ?? 0;

                        $is_drp_goods += $order_goods['is_distribution'];
                    }

                    // 购买权益卡订单商品折扣
                    if (isset($flow['order_membership_card_id']) && $flow['order_membership_card_id'] > 0 && isset($goodsInfo['membership_card_discount_price']) && $v['membership_card_discount_price'] > 0) {
                        $order_goods['membership_card_discount_price'] = $goodsInfo['membership_card_discount_price'];
                    }
                }

                $recId = OrderGoods::insertGetId($order_goods);

                if ($recId > 0) {
                    /* 删除购物车商品 */
                    Cart::where('rec_id', $v['rec_id'])->where('user_id', $uid)->delete();

                    /* 处理秒杀更新销量 */
                    if (stripos($v['extension_code'], 'seckill') !== false) {
                        $sec_id = (int)substr($order_goods['extension_code'], 7);
                        $dbRaw = [
                            'sales_volume' => "sales_volume + " . $order_goods['goods_number'],
                        ];
                        $dbRaw = BaseRepository::getDbRaw($dbRaw);
                        SeckillGoods::where('id', $sec_id)->update($dbRaw);
                    }

                    if ($coupons_money > 0 && $order_goods['goods_coupons'] > 0) {
                        $coupons_list[$v['ru_id']][$k]['rec_id'] = $recId;
                    }

                    if ($goodsValueCard) {
                        $value_card_list[$k]['rec_id'] = $recId;

                        if ($goodsValueCard['value_card_discount'] > 0) {
                            $value_card_discount_list[$k]['rec_id'] = $recId;
                        }
                    }

                    if ($order_goods['goods_bonus'] > 0) {
                        $bonus_list[$k]['rec_id'] = $recId;
                    }

                    $order_rec[] = $recId;
                }
            }

            /* 核对均摊优惠券商品金额 */
            if ($coupons_list && $cart_goods_count > 1) {
                foreach ($coupons_list as $ruId => $row) {

                    $row = array_values($row);

                    $couponsInfo = $couponsShareEqually[$ruId]['couponsInfo'] ?? [];
                    $coupons_money = $couponsInfo['cou_money'] ?? 0;
                    if ($coupons_money > 0) {
                        $this->dscRepository->collateOrderGoodsCoupons($row, $coupons_money ?? 0, $goods_coupons[$ruId]);
                    }
                }
            }

            if ($cart_goods_count == 1 && $order['coupons'] > 0) {
                OrderGoods::where('order_id', $order['order_id'])->where('user_id', $uid)->update(['goods_coupons' => $order['coupons']]);
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
                $this->dscRepository->collateOrderGoodsBonus($bonus_list, $order['bonus'], $goods_bonus);
            } else {
                if ($cart_goods_count == 1 && $order['bonus'] > 0) {
                    OrderGoods::where('order_id', $order['order_id'])->where('user_id', $uid)->update(['goods_bonus' => $order['bonus']]);
                }
            }
        }

        if ((empty($order_rec)) || (count($cart_goods) != count($order_rec))) {
            OrderInfo::where('order_id', $new_order_id)->delete();
            return 'order_failure';
        }

        $all_ru_id = BaseRepository::getKeyPluck($cart_goods, 'ru_id');

        $this->jigonManageService->pushJigonOrderGoods($cart_goods, $order, 'api'); //推送贡云订单

        /*插入门店订单表*/
        $pick_code = '';
        if ($new_order_id > 0 && $store_id > 0 && $ru_id_arr) {
            foreach ($ru_id_arr as $v) {
                if ($stores_sms != 1) {
                    $pick_code = '';
                } else {
                    $pick_code = substr($order['order_sn'], -3) . mt_rand(100, 999);
                }
                $store_order = [
                    'order_id' => $new_order_id,
                    'store_id' => $store_id,
                    'ru_id' => $v,
                    'pick_code' => $pick_code,
                    'take_time' => $flow['take_time'],
                ];
                StoreOrder::insert($store_order);
            }
        }
        //插入门店订单结束

        /* 记录优惠券使用 */
        if ($order['order_id'] > 0 && !empty($order['uc_id'])) {
            $this->flowOrderService->orderUseCoupons($order['uc_id'], $order['order_id'], $uid);
        }

        /* 修改拍卖活动状态 */
        if ($order['extension_code'] == 'auction') {
            $is_finished = 2; //完成状态默认为2(已完成已处理);

            //获取拍卖活动保证金
            $activity_ext_info = GoodsActivity::select('ext_info')
                ->where('act_id', $order['extension_id'])
                ->first();
            $activity_ext_info = $activity_ext_info ? $activity_ext_info->toArray() : [];

            //判断是否存在保证金
            if ($activity_ext_info) {
                $activity_ext_info = unserialize($activity_ext_info['ext_info']);
                //存在保证金状态为1（已完成未处理）
                if ($activity_ext_info['deposit'] > 0) {
                    $is_finished = 1;
                }
            }
            GoodsActivity::where('act_id', $order['extension_id'])->update(['is_finished' => $is_finished]);
        }

        /* 修改砍价活动状态 */
        if ($order['extension_code'] == 'bargain_buy') {
            BargainStatisticsLog::where('id', $flow['bs_id'])->update(['status' => 1]);
        }

        /* 处理储值卡 */
        if ($order['vc_id'] > 0) {
            $this->flowOrderService->useValueCard($order['vc_id'], $new_order_id, $order['order_sn'], $order['use_value_card'], $order['vc_dis_money']);
        }

        /* 处理余额、积分、红包 */
        if ($order['user_id'] > 0 && ($order['surplus'] > 0 || $order['integral'] > 0)) {
            if ($order['surplus'] > 0) {
                $order_surplus = $order['surplus'] * (-1);
            } else {
                $order_surplus = 0;
            }
            if ($order['integral'] > 0) {
                $order_integral = $order['integral'] * (-1);
            } else {
                $order_integral = 0;
            }
            log_account_change($order['user_id'], $order_surplus, 0, 0, $order_integral, sprintf(lang('shopping_flow.pay_order'), $order['order_sn']));
        }

        /*判断预售商品是否在支付尾款时间段内*/
        $order['presaletime'] = 0;
        if ($order['extension_code'] == 'presale') {
            $presale = PresaleActivity::select('pay_start_time', 'pay_end_time')
                ->where('act_id', $order['extension_id'])
                ->first();
            $presale = $presale ? $presale->toArray() : [];
            if ($presale) {
                if ($time < $presale['pay_end_time'] && $time > $presale['pay_start_time']) {
                    $order['presaletime'] = 1;
                } else {
                    $order['presaletime'] = 2;
                }
            }
        }

        if ($order['bonus_id'] > 0 && $temp_amout > 0) {
            $this->flowOrderService->useBonus($order['bonus_id'], $new_order_id);
        }

        // 收银台代码 调用事件 门店非分单 已付款 推单
        if (count($ru_id_arr) == 1 && $order['order_amount'] <= 0) {
            event(new PickupOrdersPayedAfterDoSomething($order));
        }

        /** 如果使用库存，且下订单时减库存，则减少库存 */
        if (config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_PLACE) {
            change_order_goods_storage($order['order_id'], true, SDT_PLACE, 1, 0, $store_id);
        }

        /* 如果使用库存，且付款时减库存，且订单金额为0，则减少库存 */
        if ($new_order_id > 0 && config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_PAID && $order['order_amount'] <= 0) {
            change_order_goods_storage($order['order_id'], true, SDT_PAID, 15, 0, $store_id);
        }

        $msg = $order['pay_status'] == PS_UNPAYED ? lang('shopping_flow.order_placed_sms') : lang('shopping_flow.order_placed_sms_ispay');

        /* 插入支付日志 */
        $order['log_id'] = insert_pay_log($new_order_id, $order['order_amount'], PAY_ORDER);

        //订单分子订单 start
        $order_id = $order['order_id'];

        $ru_number = count($all_ru_id);

        /* 更新会员订单信息 */
        $userOrderNumCount = UserOrderNum::where('user_id', $user_id)->count();
        if ($userOrderNumCount == 0) {
            UserOrderNum::insert([
                'user_id' => $uid
            ]);
        }
        //兼容货到付款订单放到未收货订单中
        if ($order['order_amount'] <= 0 || $payment['pay_code'] == 'cod') {
            $dbRaw = [
                'order_all_num' => "order_all_num + " . $ru_number,
                'order_nogoods' => "order_nogoods + " . $ru_number
            ];
            $dbRaw = BaseRepository::getDbRaw($dbRaw);

            UserOrderNum::where('user_id', $uid)->update($dbRaw);
        } else {
            $dbRaw = [
                'order_all_num' => "order_all_num + " . $ru_number,
                'order_nopay' => "order_nopay + " . $ru_number
            ];
            $dbRaw = BaseRepository::getDbRaw($dbRaw);

            UserOrderNum::where('user_id', $uid)->update($dbRaw);
        }

        if ($new_order_id && $ru_number > 1) {

            if ($order['order_amount'] <= 0 || $payment['pay_code'] == 'cod' || $payment['pay_code'] == 'bank') {
                /* 队列分单 */
                $filter = [
                    'order_id' => $order_id,
                    'user_id' => $uid
                ];
                ProcessSeparateBuyOrder::dispatch($filter);
            }

            $main_pay = 1;
            if ($order['order_amount'] <= 0) {
                $main_pay = 2;
            } elseif ($payment['pay_code'] == 'cod' || $payment['pay_code'] == 'bank') {
                $main_pay = 0;
            }

            $updateOrder = [
                'main_count' => $ru_number, //更新主订单商品标识
                'main_pay' => $main_pay
            ];

            $child_order_info = $this->flowOrderService->getChildOrderInfo($order_id);
        } else {
            $updateOrder = [
                'ru_id' => $all_ru_id[0]
            ];

            if (file_exists(MOBILE_DRP)) {
                $updateOrder['is_drp'] = $is_drp_goods > 0 ? 1 : 0;
            }

            $child_order_info = [];
        }

        OrderInfo::where('order_id', $new_order_id)->where('user_id', $user_id)->update($updateOrder);

        //门店发送短信
        if ($stores_sms == 1 && $store_id > 0) {
            /*门店下单时未填写手机号码 则用会员绑定号码*/
            if ($order['mobile']) {
                $user_mobile_phone = $order['mobile'];
            } else {
                $user_mobile_phone = !empty($user_info['mobile_phone']) ? $user_info['mobile_phone'] : '';
            }

            if (!empty($user_mobile_phone)) {
                $user_name = !empty($user_info['nick_name']) ? $user_info['nick_name'] : $user_info['user_name'];
                //发送提货码
                $content = [
                    'code' => $pick_code,
                    'user_name' => $user_name,
                    'username' => $user_name,
                    'order_sn' => $order['order_sn'],
                    'ordersn' => $order['order_sn']
                ];
                $this->commonRepository->smsSend($user_mobile_phone, $content, 'store_order_code');
            }
        }

        //对单商家下单发短信
        if ($new_order_id > 0 && $ru_number == 1) {
            $sellerId = $all_ru_id['0'];
            //商家客服手机号获取
            $seller_shopinfo = SellerShopinfo::select('mobile', 'seller_email')
                ->where('ru_id', $sellerId)
                ->first();
            $seller_shopinfo = $seller_shopinfo ? $seller_shopinfo->toArray() : [];

            $sms_shop_mobile = $seller_shopinfo['mobile'] ?? '';

            // 下单或付款发短信
            if (!empty($sms_shop_mobile) && (config('shop.sms_order_placed') == 1 || config('shop.sms_order_payed') == 1)) {
                //是否开启下单自动发短信、邮件 by wu start
                $auto_sms = Crons::select('*')
                    ->where('cron_code', 'sms')
                    ->where('enable', 1)
                    ->first();
                $auto_sms = $auto_sms ? $auto_sms->toArray() : [];

                if (!empty($auto_sms)) {
                    $autoData = [
                        'item_type' => 1,
                        'user_id' => $order['user_id'],
                        'ru_id' => $sellerId,
                        'order_id' => $order_id,
                        'add_time' => $time
                    ];
                    AutoSms::insert($autoData);
                } else {
                    $content = [
                        'consignee' => $order['consignee'],
                        'order_mobile' => $order['mobile'],
                        'ordermobile' => $order['mobile'], // 兼容变量
                    ];

                    // 下单发短信
                    if (config('shop.sms_order_placed') == '1') {
                        $this->commonRepository->smsSend($sms_shop_mobile, $content, 'sms_order_placed');
                    }

                    // 下单与付款发送短信 若同时开启 须间隔1s
                    if (config('shop.sms_order_placed') == '1' && config('shop.sms_order_payed') == '1') {
                        sleep(1);
                    }
                    // 余额支付等金额为0的订单 付款发短信
                    if ($order['order_amount'] <= 0 && config('shop.sms_order_payed') == '1') {
                        $this->commonRepository->smsSend($sms_shop_mobile, $content, 'sms_order_payed');
                    }
                }
            }
        }

        if ($new_order_id > 0) {

            // 删除收货地址缓存
            cache()->forget('flow_consignee_' . $user_id);

            /* 删除队列 */
            SolveDealconcurrent::where('user_id', $user_id)->delete();

            // 下单事件监听扩展参数
            $extendParam = [];

            if (is_wechat_browser() && file_exists(MOBILE_WECHAT)) {
                // 是否发送 微信通模板消息 订单通知
                $extendParam['send_wechat_template_order_create'] = 1;
            }

            // 开通购买会员权益卡订单记录
            if (file_exists(MOBILE_DRP) && isset($flow['order_membership_card_id']) && $flow['order_membership_card_id'] > 0) {
                if (isset($memberCardInfo) && !empty($memberCardInfo)) {
                    // 余额支付或使用余额（含部分） 支付订单成功
                    if (($payment['pay_code'] == 'balance' || $order['is_surplus'] == 1) && $order['order_amount'] == 0 && $order['surplus'] > 0) {
                        $membership_card_order_amount = $order['surplus'] + $order['order_amount'];
                    } else {
                        $membership_card_order_amount = $order['order_amount'];
                    }

                    $order_membership_card = [
                        'order_amount' => $membership_card_order_amount > 0 ? $membership_card_order_amount - $memberCardInfo['membership_card_buy_money'] : 0,
                        'membership_card_id' => $flow['order_membership_card_id'],
                        'membership_card_buy_money' => $memberCardInfo['membership_card_buy_money'] ?? 0,
                        'membership_card_discount_price' => $flow['membership_card_discount_price'] ?? 0,
                    ];
                    $extendParam['order_membership_card'] = $order_membership_card;
                }
            }

            // 分成插入drp_log
            if (file_exists(MOBILE_DRP)) {
                $affiliate_drp_id = CommonRepository::getDrpShopAffiliate($user_id, 1); // 推荐人
                $extendParam['affiliate_drp_id'] = $affiliate_drp_id;
            }

            // 下单事件监听
            $extendParam['shop_config'] = config('shop');
            event(new \App\Events\OrderDoneEvent($order, $extendParam));

            //下单推送消息给多商户商家掌柜事件
            event(new \App\Events\PushMerchantOrderPlaceEvent($order['order_sn']));

            // 下单支付成功
            $extendParam = [];
            if ($order['order_amount'] <= 0 && $order['pay_status'] == PS_PAYED) {

                // 付款成功创建快照
                if ($snapshot) {
                    $extendParam['create_snapshot'] = 1;
                }

                // 使用储值卡支付、余额支付或使用余额（含部分） 支付订单(含分单) 记录操作日志
                if (!empty($order['use_value_card']) || (($payment['pay_code'] == 'balance' || $order['is_surplus'] == 1) && $order['surplus'] > 0)) {
                    /* 记录主订单操作记录 */
                    $note = $order['use_value_card'] > 0 ? trans('shopping_flow.flow_value_card_pay') : trans('shopping_flow.flow_surplus_pay');

                    order_action($order['order_sn'], OS_CONFIRMED, SS_UNSHIPPED, PS_PAYED, $note, trans('common.buyer'));

                    if (!empty($child_order_info)) {
                        /* 记录子订单操作记录 */
                        foreach ($child_order_info as $key => $child) {
                            order_action($child['order_sn'], OS_CONFIRMED, SS_UNSHIPPED, PS_PAYED, $note, trans('common.buyer'));
                        }
                    }

                    // 更新拼团信息记录
                    if (file_exists(MOBILE_TEAM) && isset($order['team_id']) && $order['team_id'] > 0) {
                        $team_num = $this->orderCommonService->teamOrderNum($order['user_id']);
                        $this->orderCommonService->updateUserOrderNum($order['user_id'], ['order_team_num' => $team_num]);
                        app(TeamService::class)->updateTeamInfo($order['team_id'], $order['team_parent_id'], $order['user_id']);
                    }

                    // 开通购买会员权益卡 订单支付成功 更新成为分销商
                    if (file_exists(MOBILE_DRP) && isset($flow['order_membership_card_id']) && $flow['order_membership_card_id'] > 0) {
                        $extendParam['buy_order_update_drp'] = 1;
                    }

                    if (is_wechat_browser() && file_exists(MOBILE_WECHAT)) {
                        // 是否发送 微信通模板消息 余额变动提醒
                        $extendParam['send_wechat_template_user_balance_remind'] = 1;
                        $extendParam['send_wechat_template_order_surplus'] = $order_surplus ?? 0;
                    }
                }

                // 订单支付事件监听
                $extendParam['shop_config'] = config('shop');
                event(new \App\Events\OrderPaidEvent($order, $extendParam));

                //订单支付推送消息给多商户商家掌柜事件
                event(new \App\Events\PushMerchantOrderPayedEvent($order['order_sn']));
            }

            /* 下单支付成功 如果订单金额为0的订单（不分单） 处理虚拟卡 */
            if ($order['order_amount'] <= 0 && $ru_number == 1) {
                $this->jigonManageService->jigonConfirmOrder($new_order_id); //贡云确认订单

                /* 取得未发货虚拟商品 */
                $virtual_goods = get_virtual_goods($new_order_id);
                if ($virtual_goods && $flow_type != CART_GROUP_BUY_GOODS) {
                    /* 虚拟卡发货 */
                    $error_msg = '';
                    if (virtual_goods_ship($virtual_goods, $error_msg, $order['order_sn'], true)) {
                        /* 如果没有实体商品，修改发货状态，送积分和红包 */
                        $count = OrderGoods::where('order_id', $order['order_id'])->where('is_real', 1)->count();
                        if ($count <= 0) {
                            /* 修改订单状态 OS_CONFIRMED 1，OS_SPLITED 5  */
                            update_order($order['order_id'], ['order_status' => OS_CONFIRMED, 'shipping_status' => SS_SHIPPED, 'shipping_time' => $time]);

                            /* 如果订单用户不为空，计算积分，并发给用户；发红包 */
                            if ($order['user_id'] > 0) {

                                /* 计算并发放积分 */
                                $integral = integral_to_give($order);
                                log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf(lang('payment.order_gift_integral'), $order['order_sn']));

                                /* 发放红包 */
                                send_order_bonus($order['order_id']);

                                /* 发放优惠券 bylu */
                                send_order_coupons($order['order_id']);
                            }

                            if (config('shop.sales_volume_time') == SALES_SHIP) {
                                // 发货更新商品销量
                                \App\Repositories\Order\OrderRepository::increment_goods_sale_ship($order_id, ['order_id' => $order_id, 'pay_status' => $order['pay_status'], 'shipping_status' => SS_SHIPPED]);
                            }
                        }
                    }
                }
            }

        }

        return $order['order_sn'];
    }

    /**
     * 选择支付方式
     * @param int $uid
     * @param string $order_sn
     * @param int $store_id
     * @return array
     */
    public function PayCheck($uid = 0, $order_sn = '', $store_id = 0)
    {
        if (empty($uid) || empty($order_sn)) {
            return [];
        }

        $order_info = OrderInfo::where('order_sn', $order_sn)
            ->where('user_id', $uid)
            ->select('order_id', 'order_sn', 'order_status', 'shipping_status', 'pay_status', 'pay_id', 'surplus', 'pay_status', 'shipping_id', 'order_amount', 'extension_code', 'is_zc_order', 'zc_goods_id', 'team_id');

        $order_info = BaseRepository::getToArrayFirst($order_info);

        if (empty($order_info)) {
            return [];
        }

        /* 判断订单金额为0时，支付状态是否为已支付状态 */
        $order_amount = floatval($order_info['order_amount']);
        if ($order_amount <= 0 && $order_info['pay_status'] != PS_PAYED) {
            return [];
        }

        $order = [];
        $order['order_sn'] = $order_info['order_sn'];

        $payment = payment_info($order_info['pay_id']);

        $payment['pay_name'] = $payment['pay_name'] ?? '';
        $payment['pay_code'] = $payment['pay_code'] ?? '';
        $payment['is_online'] = $payment['is_online'] ?? 0;
        $payment['pay_desc'] = $payment['pay_desc'] ?? '';

        $order['pay_name'] = $payment['pay_name'];
        $order['pay_code'] = $payment['pay_code'];
        $order['pay_desc'] = $payment['pay_desc'];

        // 银行转账信息
        if ($payment['pay_code'] == 'bank') {
            $plugin = app(PaymentService::class)->getPayment($payment['pay_code']);
            $pay_config = [];
            if ($plugin) {
                $pay_config = $plugin->getConfig($payment['pay_code'], unserialize($payment['pay_config']));
            }
            $order['pay_config'] = $pay_config;
        }

        // 手机端 使用余额支付订单并且订单状态已付款
        $order['is_surplus'] = ($order_info['surplus'] > 0 && $order_info['pay_status'] == PS_PAYED) ? 1 : 0;
        // 是否在线支付
        $order['is_online'] = ($payment['is_online'] == 1 && $payment['pay_code'] != 'balance') ? 1 : 0;
        $order['pay_result'] = ($order_info['pay_id'] == 0 && $order_info['pay_status'] == PS_PAYED) ? 1 : 0; //余额支付订单并且订单状态已付款

        $order['cod_fee'] = 0;
        $shipping_id = explode('|', $order_info['shipping_id']);
        foreach ($shipping_id as $k => $v) {
            if ($v) {
                $order['support_cod'] = Shipping::where('shipping_id', $v)->value('support_cod');
            }
        }
        $order['pay_status'] = $order_info['pay_status'];
        $order['order_id'] = $order_info['order_id'];
        $order['order_amount'] = $order_info['order_amount'];
        $order['order_amount_format'] = $this->dscRepository->getPriceFormat($order_info['order_amount']);

        // 子订单
        $child_order_info = $this->flowOrderService->getChildOrderInfo($order_info['order_id']);

        $order['child_order'] = BaseRepository::getArrayCount($child_order_info);
        $order['child_order_info'] = $child_order_info ?? [];

        //门店信息
        if ($store_id > 0) {
            $store_info = $this->offlineStoreService->infoOfflineStore($store_id);
            if (!empty($store_info)) {
                $store_info['province_name'] = $this->get_region_name($store_info['province']);
                $store_info['city_name'] = $this->get_region_name($store_info['city']);
                $store_info['district_name'] = $this->get_region_name($store_info['district']);
            }
        }
        $order['store_info'] = isset($store_info) ? $store_info : [];

        $order['extension_code'] = $order_info['extension_code'];
        if ($order['extension_code'] == 'team_buy') {
            $team_id = $order_info['team_id'] ?? 0;
            $order['url'] = dsc_url('/#/team/wait') . '?' . http_build_query(['team_id' => $team_id, 'status' => 1], '', '&');
            $order['support_cod'] = 0; // 过滤货到付款
            // 小程序、app链接参数
            $order['team_id'] = $team_id;
            $order['status'] = 1;
        }
        if ($order_info['is_zc_order'] == 1 && $order_info['zc_goods_id'] > 0) {
            $order['extension_code'] = 'crowd_buy';
        }

        return $order;
    }

    /**
     * 使用优惠券
     *
     * @param array $uc_id
     * @param int $uid
     * @param array $total
     * @param array $shipping_id
     * @return array
     * @throws \Exception
     */
    public function ChangeCou($uc_id = [], $uid = 0, $total = [], $shipping_id = [])
    {
        $order_shipping_fee = 0;
        $couponsList = [];
        $shippingList = [];

        $rec_ids = $this->getRecList($total);
        $cart_goods = $this->couponsBonusFlowGoods($rec_ids, $uid);

        $order['user_id'] = $uid;
        $ru_id = $total['ru_id'] ?? [];
        $consignee = $total['consignee'] ?? [];

        if (!empty($uc_id)) {

            $couponsList = $this->couponsUserService->getCouponsUserSerList($uc_id, $uid, $cart_goods);

            $order['uc_id'] = BaseRepository::getKeyPluck($couponsList, 'uc_id');;

            if (!empty($couponsList) && $cart_goods) {
                $couponsList = BaseRepository::getGroupBy($couponsList, 'ru_id');
            }
        } else {
            $order['uc_id'] = [];
        }

        //均摊优惠券
        $cart_goods = $this->flowOrderService->couponsBonusFlowShareEqually($cart_goods, $order, $couponsList, 1); //重新赋值

        if (!empty($uc_id)) {
            $couponsList = $this->couponsUserService->getCouponsUserSerList($uc_id, $uid, $cart_goods);
            $uc_id = BaseRepository::getKeyPluck($couponsList, 'uc_id');

            if ($couponsList) {

                $rec_goods_id = BaseRepository::getColumn($cart_goods, 'goods_id', 'rec_id');
                $rec_extension_code = BaseRepository::getColumn($cart_goods, 'extension_code', 'rec_id');

                $realGoodsList = [];
                if ($rec_extension_code) {
                    foreach ($rec_extension_code as $rec_id => $val) {
                        if ($val != 'package_buy') {
                            $realGoodsList[$rec_id] = $rec_goods_id[$rec_id] ?? 0;
                        }
                    }
                }

                $realGoodsList = GoodsDataHandleService::GoodsDataList($realGoodsList, ['goods_id', 'cat_id']);
                if ($cart_goods) {
                    foreach ($cart_goods as $key => $val) {
                        if ($val['extension_code'] != 'package_buy') {
                            $cart_goods[$key]['cat_id'] = $realGoodsList[$val['goods_id']]['cat_id'];
                        } else {
                            $cart_goods[$key]['cat_id'] = 0;
                        }

                        $rank = app(UserCommonService::class)->getUserRankByUid($uid);
                        $user_rank = $rank['rank_id'] ?? 0;
                        $cart_goods[$key]['user_rank'] = $user_rank;
                    }
                }

                $tmp_shipping_id = [];
                if ($ru_id && $shipping_id && count($ru_id) == count($shipping_id)) {
                    $tmp_shipping_id = BaseRepository::getArrayCombine($ru_id, $shipping_id);
                }

                $shippingList = $this->shippingService->goodsShippingTransport($cart_goods, $consignee, $uc_id, $tmp_shipping_id);

                /* 已扣除免邮金额 */
                $ruShippingInfo = $this->shippingService->orderFeeShipping($cart_goods, $ru_id, $shippingList, $tmp_shipping_id);
                $order_shipping_fee = $ruShippingInfo['shipping_fee'] ?? 0;
                $order_shipping_fee = $this->dscRepository->getPriceFormat($order_shipping_fee, true, false);
            }
        }

        $order_shipping_fee = $order_shipping_fee ?? 0;

        /* 查询实际运费金额 */
        $shipping_fee = $this->shippingService->cartShippingListTotal($shippingList);
        $shipping_fee = $this->dscRepository->changeFloat($shipping_fee);
        $total['shopping_fee'] = $shipping_fee;
        $total['shopping_fee_formated'] = $this->dscRepository->getPriceFormat($total['shopping_fee']);

        if ($order_shipping_fee > 0) {
            $total['free_shipping_fee'] = $shipping_fee - $order_shipping_fee;
        } else {
            $total['free_shipping_fee'] = 0;
        }

        $total['free_shipping_fee_formated'] = $this->dscRepository->getPriceFormat($total['free_shipping_fee']);

        $total['success_type'] = 0;

        $total['bonus_money'] = $total['bonus_money'] ?? 0;
        $total['bonus_money'] = $this->dscRepository->changeFloat($total['bonus_money']);
        $total['coupons_money'] = $total['coupons_money'] ?? 0;
        $total['coupons_money'] = $this->dscRepository->changeFloat($total['coupons_money']);
        $total['vc_dis'] = isset($total['vc_dis']) && $total['vc_dis'] > 0 ? $total['vc_dis'] : 1;
        $total['vc_dis_money'] = $total['vc_dis_money'] ?? 0;//储值卡折扣
        $total['integral_money'] = isset($total['integral_money']) ? round($total['integral_money'], 2) : 0;
        $total['integral_money'] = $this->dscRepository->changeFloat($total['integral_money']);
        $total['card'] = isset($total['card']) && $total['card'] ? floatval($total['card']) : 0;
        $total['card'] = $this->dscRepository->changeFloat($total['card']);
        $total['card_money'] = isset($total['card_money']) && $total['card_money'] ? floatval($total['card_money']) : 0;
        $total['card_money'] = $this->dscRepository->changeFloat($total['card_money']);
        $total['discount'] = $total['discount'] ?? 0;
        $total['discount'] = $this->dscRepository->changeFloat($total['discount']);
        $total['bonus_id'] = isset($total['bonus_id']) && $total['bonus_id'] ? $total['bonus_id'] : 0; //红包id
        $total['coupons_id'] = isset($total['coupons_id']) && !empty($total['coupons_id']) ? $total['coupons_id'] : $uc_id; //优惠券id
        $total['value_card_id'] = $total['value_card_id'] ?? 0; //储值卡id
        $total['surplus'] = $total['surplus'] ?? 0;  //余额

        if (CROSS_BORDER === true) { // 跨境多商户
            $total['amount'] -= $total['rate_price'];
        }

        if (!empty($uc_id) && $couponsList) {
            $cou_money = BaseRepository::getArraySum($couponsList, 'cou_money');
            $total['success_type'] = 1;
            $total['coupons_money'] = StrRepository::priceFormat($cou_money);
            $total['coupons_money_formated'] = $this->dscRepository->getPriceFormat($total['coupons_money']);
        } else {
            $total['coupons_money'] = 0;
            $total['coupons_id'] = 0;
        }

        $total = $this->resetTotal($total, $uid);

        if (CROSS_BORDER === true) { // 跨境多商户
            $total['amount'] += $total['rate_price'];
        }

        // 开通购买会员特价权益卡
        $total['order_membership_card_id'] = $total['order_membership_card_id'] ?? 0; // 权益卡id
        if (file_exists(MOBILE_DRP) && $total['order_membership_card_id'] > 0) {
            $total['membership_card_buy_money'] = $total['membership_card_buy_money'] ?? 0; // 权益卡购买金额
            $total['membership_card_discount_price'] = $total['membership_card_discount_price'] ?? 0;  // 权益卡购买折扣

            $amount = 0;
            if (!empty($uc_id) && $couponsList) {
                $amount = $total['amount'];
            }

            $amount = $amount > $total['membership_card_discount_price'] ? $amount - $total['membership_card_discount_price'] : 0;

            // 使用优惠券 不抵扣 购卡金额
            $total['amount'] = $amount + $total['membership_card_buy_money'];
        }

        $total['amount'] = $this->dscRepository->getPriceFormat($total['amount'], true, false);
        $total['amount'] = StrRepository::priceFormat($total['amount']);
        $total['amount'] = $this->dscRepository->changeFloat($total['amount']);

        /* 运费已在模板中调用中相加，所以扣除运费，避免显示重复累加运费*/
        if ($total['amount'] > 0 && $total['amount'] >= $total['shopping_fee']) {
            $total['amount'] = $total['amount'] - $total['shopping_fee'];
        }

        /*储值卡折扣*/
        $total['amount_formated'] = $this->dscRepository->getPriceFormat($total['amount']);

        /* 重新赋值优惠券ID */
        $total['coupons_id'] = $uc_id;

        return $total;
    }

    /**
     * 使用红包
     *
     * @param int $bonus_id
     * @param int $uid
     * @param array $total
     * @param array $shipping_id
     * @return array
     * @throws \Exception
     */
    public function ChangeBon($bonus_id = 0, $uid = 0, $total = [], $shipping_id = [])
    {
        $bonus = [];
        if ($bonus_id > 0) {
            $bonus = Userbonus::where('bonus_id', $bonus_id)
                ->where('user_id', $uid)
                ->where('used_time', 0);

            $bonus = $bonus->with([
                'getBonusType'
            ]);

            $bonus = BaseRepository::getToArrayFirst($bonus);
            $bonus = isset($bonus['get_bonus_type']) ? BaseRepository::getArrayMerge($bonus, $bonus['get_bonus_type']) : $bonus;
        }

        $order['bonus'] = $bonus['type_money'] ?? 0;
        $order['bonus_id'] = $bonus['bonus_id'] ?? 0;
        $order['user_id'] = $uid;

        $rec_list = $this->getRecList($total);
        $cart_goods = $this->couponsBonusFlowGoods($rec_list, $uid);
        $this->flowOrderService->couponsBonusFlowShareEqually($cart_goods, $order, [], 2); //重新赋值

        $total['success_type'] = 0;

        $total['bonus_money'] = $total['bonus_money'] ?? 0;
        $total['bonus_money'] = $this->dscRepository->changeFloat($total['bonus_money']);
        $total['coupons_money'] = $total['coupons_money'] ?? 0;
        $total['coupons_money'] = $this->dscRepository->changeFloat($total['coupons_money']);
        $total['vc_dis'] = isset($total['vc_dis']) && $total['vc_dis'] > 0 ? $total['vc_dis'] : 1;
        $total['vc_dis_money'] = $total['vc_dis_money'] ?? 0;//储值卡折扣
        $total['integral_money'] = isset($total['integral_money']) ? round($total['integral_money'], 2) : 0;
        $total['integral_money'] = $this->dscRepository->changeFloat($total['integral_money']);
        $total['card'] = isset($total['card']) && $total['card'] ? floatval($total['card']) : 0;
        $total['card'] = $this->dscRepository->changeFloat($total['card']);
        $total['card_money'] = isset($total['card_money']) && $total['card_money'] ? floatval($total['card_money']) : 0;
        $total['card_money'] = $this->dscRepository->changeFloat($total['card_money']);
        $total['discount'] = $total['discount'] ?? 0;
        $total['discount'] = $this->dscRepository->changeFloat($total['discount']);
        $total['bonus_id'] = isset($total['bonus_id']) && $total['bonus_id'] ? $total['bonus_id'] : $bonus_id; //红包id
        $total['coupons_id'] = $total['coupons_id'] ?? 0; //优惠券id
        $total['value_card_id'] = $total['value_card_id'] ?? 0; //储值卡id
        $total['surplus'] = $total['surplus'] ?? 0; //余额

        /* 获取运费 */
        $total = $this->flowShippingFee($total, $uid, $shipping_id);

        if (CROSS_BORDER === true) { // 跨境多商户
            $total['amount'] -= $total['rate_price'];
        }

        if ($bonus_id > 0 && $bonus) {
            $total['bonus_money'] = $bonus['type_money'];
        } else {
            $total['bonus_money'] = 0;
            $total['bonus_id'] = 0;
        }

        $total = $this->resetTotal($total, $uid);

        if (CROSS_BORDER === true) { // 跨境多商户
            $total['amount'] += $total['rate_price'];
        }

        // 开通购买会员特价权益卡
        $total['order_membership_card_id'] = $total['order_membership_card_id'] ?? 0; // 权益卡id
        if (file_exists(MOBILE_DRP) && $total['order_membership_card_id'] > 0) {
            $total['membership_card_buy_money'] = $total['membership_card_buy_money'] ?? 0; // 权益卡购买金额
            $total['membership_card_discount_price'] = $total['membership_card_discount_price'] ?? 0;  // 权益卡购买折扣

            $amount = 0;
            if ($bonus_id > 0 && $bonus) {
                $amount = $total['amount'];
            }

            $amount = $amount > $total['membership_card_discount_price'] ? $amount - $total['membership_card_discount_price'] : 0;

            // 使用红包 不抵扣 购卡金额
            $total['amount'] = $amount + $total['membership_card_buy_money'];
        }

        $total['amount'] = $this->dscRepository->getPriceFormat($total['amount'], true, false);
        $total['amount'] = StrRepository::priceFormat($total['amount']);
        $total['amount'] = $this->dscRepository->changeFloat($total['amount']);

        /* 运费已在模板中调用中相加，所以扣除运费，避免显示重复累加运费*/
        if ($total['amount'] > 0 && $total['amount'] >= $total['shopping_fee']) {
            $total['amount'] = $total['amount'] - $total['shopping_fee'];
        }

        $total['amount_formated'] = $this->dscRepository->getPriceFormat($total['amount']);

        return $total;
    }

    /**
     * 商品税费
     *
     * @param int $uid
     * @param array $total
     * @param array $shipping_id
     * @return array
     * @throws \Exception
     */
    public function ChangeTax($uid = 0, $total = [], $shipping_id = [])
    {
        $total['integral_money'] = $total['integral_money'] ?? 0;
        $total['integral'] = $total['integral'] ?? 0;
        $total['bonus_money'] = $total['bonus_money'] ?? 0;
        $total['coupons_money'] = $total['coupons_money'] ?? 0;
        $total['vc_dis'] = isset($total['vc_dis']) && $total['vc_dis'] > 0 ? $total['vc_dis'] : 1;
        $total['vc_dis_money'] = $total['vc_dis_money'] ?? 0;
        $total['card'] = isset($total['card']) && $total['card'] ? floatval($total['card']) : 0;
        $total['card_money'] = isset($total['card_money']) && $total['card_money'] ? floatval($total['card_money']) : 0;
        $total['discount'] = $total['discount'] ?? 0;
        $total['bonus_id'] = $total['bonus_id'] ?? 0; //红包id
        $total['coupons_id'] = $total['coupons_id'] ?? 0; //优惠券id
        $total['value_card_id'] = $total['value_card_id'] ?? 0; //储值卡id
        $total['surplus'] = $total['surplus'] ?? 0; //余额

        /* 如果能开发票，取得发票内容列表 */
        $inv_content = isset($total['inv_content']) ? trim($total['inv_content']) : '';

        /* 获取运费 */
        $total = $this->flowShippingFee($total, $uid, $shipping_id);

        $amount = $total['goods_price'] - $total['discount'] - $total['bonus_money'] - $total['coupons_money'] - $total['card_money'] - $total['vc_dis_money'] - $total['dis_amount'];

        /* 税额 */
        if (config('shop.can_invoice') == 1 && $inv_content) {
            $total['tax'] = CommonRepository::orderInvoiceTotal($amount, $inv_content);
        } else {
            $total['tax'] = 0;
        }

        $total = $this->resetTotal($total, $uid);

        $total['amount'] = $this->dscRepository->getPriceFormat($total['amount'], true, false);
        $total['amount'] = StrRepository::priceFormat($total['amount']);
        $total['amount'] = $this->dscRepository->changeFloat($total['amount']);

        $total['tax_formated'] = $this->dscRepository->getPriceFormat($total['tax']);
        $total['amount_formated'] = $this->dscRepository->getPriceFormat($total['amount']);

        return $total;
    }

    /**
     * 使用积分
     *
     * @param int $uid
     * @param array $total
     * @param int $integral_type
     * @param array $shipping_id
     * @return array
     * @throws \Exception
     */
    public function ChangeInt($uid = 0, $total = [], $integral_type = 0, $shipping_id = [])
    {
        $integral_money = $total['use_integral_money'];
        $integral = $this->dscRepository->integralOfValue($integral_money);

        $integral_type = intval($integral_type);

        /* 选择状态 */
        if ($integral_type == 1) {
            $total['success_type'] = 1;
        } else {
            $integral_money = 0;
            $integral = 0;
            $total['success_type'] = 0;
        }

        $total['integral_money'] = $integral_money;
        $total['integral'] = $integral;

        $total['bonus_money'] = $total['bonus_money'] ?? 0;
        $total['coupons_money'] = $total['coupons_money'] ?? 0;
        $total['vc_dis'] = isset($total['vc_dis']) && $total['vc_dis'] > 0 ? $total['vc_dis'] : 1;
        $total['vc_dis_money'] = $total['vc_dis_money'] ?? 0;
        $total['integral_money'] = isset($total['integral_money']) ? round($total['integral_money'], 2) : 0;
        $total['card'] = isset($total['card']) && $total['card'] ? floatval($total['card']) : 0;
        $total['card_money'] = isset($total['card_money']) && $total['card_money'] ? floatval($total['card_money']) : 0;
        $total['discount'] = $total['discount'] ?? 0;
        $total['bonus_id'] = $total['bonus_id'] ?? 0; //红包id
        $total['coupons_id'] = $total['coupons_id'] ?? 0; //优惠券id
        $total['value_card_id'] = $total['value_card_id'] ?? 0; //储值卡id
        $total['surplus'] = $total['surplus'] ?? 0; //余额

        $order['is_integral'] = 1;

        /* 获取运费 */
        $total = $this->flowShippingFee($total, $uid, $shipping_id, $order);

        $total['amount'] = isset($total['amount']) ? floatval($total['amount']) : 0;

        $amount = $total['goods_price'] - $total['discount'] - $total['bonus_money'] - $total['coupons_money'] - $total['surplus'] - $total['card_money'] - $total['vc_dis_money'];
        //299-10-90-88-
        $amount = $amount > 0 ? $amount : 0;

        $total = $this->resetTotal($total, $uid);

        if (CROSS_BORDER === true) { // 跨境多商户
            $total['amount'] += $total['rate_price'];
        }

        // 开通购买会员特价权益卡
        $total['order_membership_card_id'] = $total['order_membership_card_id'] ?? 0; // 权益卡id
        if (file_exists(MOBILE_DRP) && $total['order_membership_card_id'] > 0) {
            $total['membership_card_buy_money'] = $total['membership_card_buy_money'] ?? 0; // 权益卡购买金额
            $total['membership_card_discount_price'] = $total['membership_card_discount_price'] ?? 0;  // 权益卡购买折扣

            if ($integral_type == 1) {
                $amount = $total['amount'];
            }

            $amount = $amount > $total['membership_card_discount_price'] ? $amount - $total['membership_card_discount_price'] : 0;

            // 使用积分 不抵扣 购卡金额
            $total['amount'] = $amount + $total['membership_card_buy_money'];
        }

        $total['amount'] = $this->dscRepository->getPriceFormat($total['amount'], true, false);
        $total['amount'] = StrRepository::priceFormat($total['amount']);
        $total['amount'] = $this->dscRepository->changeFloat($total['amount']);

        $total['amount_formated'] = $this->dscRepository->getPriceFormat($total['amount']);

        return $total;
    }

    /**
     * 余额使用
     *
     * @param int $uid
     * @param array $total
     * @param int $surplus
     * @param array $shipping_id
     * @return array
     * @throws \Exception
     */
    public function ChangeSurplus($uid = 0, $total = [], $surplus = 0, $shipping_id = [])
    {
        // 余额使用
        $total['success_type'] = 0;
        $total['bonus_money'] = $total['bonus_money'] ?? 0;
        $total['coupons_money'] = $total['coupons_money'] ?? 0;
        $total['vc_dis'] = isset($total['vc_dis']) && $total['vc_dis'] > 0 ? $total['vc_dis'] : 1;
        $total['integral_money'] = isset($total['integral_money']) ? round($total['integral_money'], 2) : 0;
        $total['card'] = isset($total['card']) && $total['card'] ? floatval($total['card']) : 0;
        $total['card_money'] = isset($total['card_money']) && $total['card_money'] ? floatval($total['card_money']) : 0;
        $total['discount'] = $total['discount'] ?? 0;
        $total['bonus_id'] = $total['bonus_id'] ?? 0; //红包id
        $total['coupons_id'] = $total['coupons_id'] ?? 0; //优惠券id
        $total['value_card_id'] = $total['value_card_id'] ?? 0; //储值卡id
        $total['vc_dis'] = isset($total['vc_dis']) && $total['vc_dis'] > 0 ? $total['vc_dis'] : 1;
        $total['vc_dis_money'] = $total['vc_dis_money'] ?? 0;
        $total['surplus'] = isset($total['surplus']) && $total['surplus'] ? $total['surplus'] : $surplus; //余额
        $total['amount'] = isset($total['amount']) ? floatval($total['amount']) : 0;

        /* 获取运费 */
        $total = $this->flowShippingFee($total, $uid, $shipping_id);

        if ($surplus > 0) {
            $user_money = Users::where('user_id', $uid)->value('user_money');
            $user_money = $user_money ? $user_money : 0;
            if ($surplus > $user_money) {
                $surplus = $user_money;
            }

            $total['success_type'] = 1;
            $total['surplus'] = $surplus;
        } else {
            $total['surplus'] = 0;
            $total['success_type'] = 0;
        }

        $total = $this->resetTotal($total, $uid);

        if (CROSS_BORDER === true) { // 跨境多商户
            $total['amount'] += $total['rate_price'];
        }

        // 开通购买会员特价权益卡
        $total['order_membership_card_id'] = $total['order_membership_card_id'] ?? 0; // 权益卡id
        if (file_exists(MOBILE_DRP) && $total['order_membership_card_id'] > 0) {
            $total['membership_card_buy_money'] = $total['membership_card_buy_money'] ?? 0; // 权益卡购买金额
            $total['membership_card_discount_price'] = $total['membership_card_discount_price'] ?? 0;  // 权益卡购买折扣

            // 购卡最终应付金额 = 权益卡购买折扣 - 权益卡购买金额
            $membership_card_amount = abs($total['membership_card_discount_price'] - $total['membership_card_buy_money']);

            if ($surplus > 0) {
                // 使用余额 抵扣购卡金额
                $total['surplus'] = $total['surplus'] > $membership_card_amount ? $total['surplus'] - $membership_card_amount : $total['surplus'];
                $total['surplus_formated'] = $this->dscRepository->getPriceFormat($total['surplus']);
            }

            $total['amount'] = $total['amount'] > $membership_card_amount ? $total['amount'] - $membership_card_amount : $total['amount'];
        }

        $total['amount'] = $this->dscRepository->getPriceFormat($total['amount'], true, false);
        $total['amount'] = StrRepository::priceFormat($total['amount']);
        $total['amount'] = $this->dscRepository->changeFloat($total['amount']);

        $total['amount_formated'] = $this->dscRepository->getPriceFormat($total['amount']);

        return $total;
    }

    /**
     * 使用储值卡
     *
     * @param int $vid
     * @param int $uid
     * @param array $total
     * @param array $shipping_id
     * @return array
     * @throws \Exception
     */
    public function ChangeCard($vid = 0, $uid = 0, $total = [], $shipping_id = [])
    {
        $total['success_type'] = 0;
        $total['goods_price'] = $total['goods_price'] ?? 0;
        $total['bonus_money'] = $total['bonus_money'] ?? 0;
        $total['coupons_money'] = $total['coupons_money'] ?? 0;
        $total['vc_dis'] = isset($total['vc_dis']) && $total['vc_dis'] > 0 ? $total['vc_dis'] : 1;
        $total['vc_dis_money'] = isset($total['vc_dis_money']) && $total['vc_dis_money'] > 0 ? $total['vc_dis_money'] : 0;
        $total['integral_money'] = $total['integral_money'] ?? 0;
        $total['card'] = isset($total['card']) && $total['card'] ? floatval($total['card']) : 0;
        $total['card_money'] = isset($total['card_money']) && $total['card_money'] ? floatval($total['card_money']) : 0;
        $total['discount'] = $total['discount'] ?? 0;
        $total['bonus_id'] = isset($total['bonus_id']) && $total['bonus_id'] ? $total['bonus_id'] : 0; //红包id
        $total['coupons_id'] = $total['coupons_id'] ?? 0; //优惠券id
        $total['value_card_id'] = $vid ?? 0; //储值卡id
        $total['surplus'] = $total['surplus'] ?? 0; //余额

        $total = $this->flowShippingFee($total, $uid, $shipping_id);

        $total = $this->resetTotal($total, $uid);
        if (CROSS_BORDER === true) { // 跨境多商户
            $total['amount'] += $total['rate_price'] ?? 0;
        }

        // 开通购买会员特价权益卡
        $total['order_membership_card_id'] = $total['order_membership_card_id'] ?? 0; // 权益卡id
        if (file_exists(MOBILE_DRP) && $total['order_membership_card_id'] > 0) {
            $total['membership_card_buy_money'] = $total['membership_card_buy_money'] ?? 0; // 权益卡购买金额
            $total['membership_card_discount_price'] = $total['membership_card_discount_price'] ?? 0;  // 权益卡购买折扣

            $amount = 0;
            if (!empty($total['value_card_id'])) {
                $amount = $total['amount'];
            }

            $amount = $amount > $total['membership_card_discount_price'] ? $amount - $total['membership_card_discount_price'] : 0;

            // 使用储值卡 不抵扣 购卡金额
            $total['amount'] = $amount + $total['membership_card_buy_money'];
        }

        $total['amount'] = $this->dscRepository->getPriceFormat($total['amount'], true, false);
        $total['amount'] = StrRepository::priceFormat($total['amount']);
        $total['amount'] = $this->dscRepository->changeFloat($total['amount']);

        /* 运费已在模板中调用中相加，所以扣除运费，避免显示重复累加运费*/
        if ($total['amount'] > 0 && $total['amount'] >= $total['shopping_fee']) {
            $total['amount'] = $total['amount'] - $total['shopping_fee'];
        }

        $total['amount_formated'] = $this->dscRepository->getPriceFormat($total['amount']);

        return $total;
    }

    /**
     * 格式化订单金额
     *
     * @param array $total
     * @param int $uid
     * @return array
     */
    private function resetTotal($total = [], $uid = 0)
    {
        //初始化数据
        $total['amount'] = 0;
        $total['goods_price'] = $total['goods_price'] ?? 0;
        $total['discount'] = $total['discount'] ?? 0;
        $total['integral_money'] = $total['integral_money'] ?? 0;
        $total['integral_money_formated'] = $this->dscRepository->getPriceFormat($total['integral_money']);
        $total['coupons_money'] = $total['coupons_money'] ?? 0;
        $total['bonus_money'] = $total['bonus_money'] ?? 0;
        $total['bonus_money_formated'] = $this->dscRepository->getPriceFormat($total['bonus_money']);
        $total['shopping_fee'] = $total['shopping_fee'] ?? 0;
        $total['shopping_fee_formated'] = $this->dscRepository->getPriceFormat($total['shopping_fee']);
        $total['free_shipping_fee'] = $total['free_shipping_fee'] ?? 0;
        $total['free_shipping_fee'] = $this->dscRepository->changeFloat($total['free_shipping_fee']);
        $total['vc_dis'] = $total['vc_dis'] ?? 1;//储值卡折扣率
        $total['vc_dis_money'] = $total['vc_dis_money'] ?? 0;//储值卡折扣额
        $total['card'] = $total['card'] ?? 0;//储值卡余额
        $total['value_card_id'] = $total['value_card_id'] ?? 0;//储值卡id
        $total['card_money'] = $total['card_money'] ?? 0;//使用储值卡金额
        $total['surplus'] = $total['surplus'] ?? 0;//使用余额
        $total['tax'] = $total['tax'] ?? 0;//商品税费

        /* 实时查储值卡 */
        if ($total['value_card_id'] > 0) {
            $value_card = ValueCard::select('vid', 'card_money', 'tid')->where('vid', $total['value_card_id'])->where('user_id', $uid);
            $value_card = BaseRepository::getToArrayFirst($value_card);

            if ($value_card) {
                $vc_dis = ValueCardType::where('id', $value_card['tid'])->value('vc_dis');
                $value_card['vc_dis'] = $vc_dis ? $vc_dis : 1;
            } else {
                $total['value_card_id'] = 0;
            }
        }

        //使用储值卡
        if ($total['value_card_id'] > 0) {

            /* 初始化优惠券、红包、折扣均摊金额，避免影响储值卡使用 */
            if (empty($total['bonus_id']) || empty($total['coupons_id']) || $total['discount'] <= 0) {
                $rec_list = $this->getRecList($total);

                $other = [];
                if (empty($total['bonus_id'])) {
                    $other['goods_bonus'] = 0;
                }

                if (empty($total['coupons_id'])) {
                    $other['goods_coupons'] = 0;
                }

                if ($total['discount'] <= 0) {
                    $other['goods_favourable'] = 0;
                }

                if (!empty($other)) {
                    Cart::whereIn('rec_id', $rec_list)->update($other);
                }
            }

            $cart_goods = $this->mobileCartGoods($total, $uid);
            $value_card = app(ValueCardService::class)->getUserValueCard($uid, $cart_goods, $total['value_card_id']);
            $value_card = BaseRepository::getArrayFirst($value_card);

            $total['success_type'] = 1;
            $total['vc_dis'] = $value_card['card_discount'];

            /* 去除优惠后的订单金额（未包含快递费） */
            $cardGoodsList = $value_card['goods_list'] ?? [];
            $cardGoodsTotal = BaseRepository::getArraySum($cardGoodsList, ['goods_price', 'goods_number']);
            $cardDisAmount = BaseRepository::getArraySum($cardGoodsList, 'dis_amount');

            //储值卡折扣金额 （商品金额 - 一切优惠金额）* 折扣率
            $goods_bonus = BaseRepository::getArraySum($cardGoodsList, 'goods_bonus');
            $goods_coupons = BaseRepository::getArraySum($cardGoodsList, 'goods_coupons');
            $goods_favourable = BaseRepository::getArraySum($cardGoodsList, 'goods_favourable');

            if ($value_card) {
                $total['vc_dis_money'] = ($cardGoodsTotal - $goods_bonus - $goods_coupons - $goods_favourable - $cardDisAmount) * (1 - $total['vc_dis']); //使用储值卡折扣的金额
                $total['vc_dis_money'] = $this->dscRepository->changeFloat($total['vc_dis_money']);
            } else {
                $total['vc_dis_money'] = 0;
            }

            $cardGoodsTotal = $cardGoodsTotal - ($goods_bonus + $goods_coupons + $goods_favourable + $total['vc_dis_money'] + $cardDisAmount);
            $cardGoodsTotal = $cardGoodsTotal > 0 ? $cardGoodsTotal : 0;

            $valCardRuList = BaseRepository::getKeyPluck($cardGoodsList, 'ru_id');
            $valCardRuList = BaseRepository::getArrayFlip($valCardRuList);
            $valCardRuListShipping = $total['ru_shipping_fee_list'] ? BaseRepository::getArrayIntersectByKeys($total['ru_shipping_fee_list'], $valCardRuList) : [];

            $cardGoodsTotal += BaseRepository::getArraySum($valCardRuListShipping); //加上运费金额

            if ($value_card) {
                $total['card_money'] = min($value_card['card_money'], $cardGoodsTotal); //满足条件商品的储值卡应付金额
                $total['card_money'] = app(DscRepository::class)->changeFloat($total['card_money']);
            } else {
                $total['card_money'] = 0;
            }

            $total['card'] = $total['card_money'];

            //重置使用储值卡
            if ($total['vc_dis_money'] == 0 && $total['card_money'] != $total['card']) {
                $total['vc_dis'] = 1;
                $total['value_card_id'] = 0;
                $total['card'] = 0;
                $total['card_money'] = 0;
            }
        } else {
            $total['card'] = 0;
            $total['card_money'] = 0;
            $total['vc_dis'] = 1;
            $total['vc_dis_money'] = 0;//储值卡折扣金额
        }

        /* 去除优惠后的订单金额（未包含快递费） */
        $calculateValueCardDiscount = $total['goods_price'] - $total['discount'] - $total['integral_money'] - $total['coupons_money'] - $total['bonus_money'];//储值卡折扣率计算金额
        $calculateValueCardDiscount = $calculateValueCardDiscount > 0 ? $calculateValueCardDiscount : 0;

        if ($calculateValueCardDiscount > 0) {

            $total['amount'] = $calculateValueCardDiscount - $total['vc_dis_money'] + $total['shopping_fee'] + $total['tax'];

            if ($total['card_money'] > 0 && $total['card_money'] >= $total['amount']) {
                $total['card'] = $total['amount'] - $total['free_shipping_fee'];
                $total['card_money'] = $total['card'];
                $total['card_formated'] = $this->dscRepository->getPriceFormat($total['card']);
                $total['amount'] = 0;
            } else {
                $total['amount'] -= $total['card_money'];
            }
        } else {
            $total['amount'] = $total['shopping_fee'] + $total['tax'];

            if ($total['card_money'] > 0) {

                if ($total['amount'] >= $total['card_money']) {
                    $total['amount'] -= $total['card_money'];
                } else {
                    $total['amount'] = 0;
                }
            }
        }

        /* 使用余额 */
        if ($total['amount'] > 0) {
            if ($total['surplus'] > 0) {
                if ($total['surplus'] > $total['amount']) {
                    $total['surplus'] = $total['amount'];

                    /* 扣除免邮券金额 */
                    if ($total['surplus'] >= $total['free_shipping_fee']) {
                        $total['surplus'] -= $total['free_shipping_fee'];
                    }

                    $total['amount'] = 0;
                } else {
                    $total['amount'] -= $total['surplus'];
                }
            }
        } else {
            $total['surplus'] = 0;
        }

        $total['surplus'] = $this->dscRepository->changeFloat($total['surplus']);

        $total['card_money'] = $this->dscRepository->changeFloat($total['card_money']);
        $total['vc_dis_money_formated'] = $this->dscRepository->getPriceFormat($total['vc_dis_money']);
        $total['card_formated'] = $this->dscRepository->getPriceFormat($total['card_money']); // 重复字段 兼容前端v1.5版本后可去除
        $total['card_money_formated'] = $this->dscRepository->getPriceFormat($total['card_money']);
        $total['surplus_formated'] = $this->dscRepository->getPriceFormat($total['surplus']);

        return $total;
    }

    /**
     * 获取可抵扣的积分金额
     *
     * @param array $cart_goods
     * @return float|int
     */
    public function AmountIntegral($cart_goods = [])
    {
        $integral = BaseRepository::getArraySum($cart_goods, 'integral_total');
        return $integral;
    }

    /**
     * 获取用户积分金额等信息
     * @param int $uid
     * @return array
     */
    public function UserIntegral($uid = 0)
    {
        $res = Users::select('user_name', 'nick_name', 'pay_points', 'user_money', 'credit_line', 'email', 'mobile_phone')
            ->where('user_id', $uid)
            ->first();
        $user = $res ? $res->toArray() : [];

        if (!empty($user)) {
            $user['user_point'] = $user['pay_points'];
            $user['integral'] = $user['pay_points'] * config('shop.integral_scale') / 100;
        }

        return $user;
    }

    /**
     * 根据用户ID获取购物车商品列表
     *
     * @param int $uid
     * @param array $cart_goods
     * @return array
     * @throws \Exception
     */
    public function GoodsInCartByUser($uid = 0, $cart_goods = [])
    {
        $cart = [];

        if (empty($cart_goods)) {
            return [];
        }

        foreach ($cart_goods as $key => $value) {
            if (isset($value['goods_id'])) {
                $cart[$key] = $value;
            }
        }

        $now = TimeRepository::getGmTime();

        $total = ['goods_price' => 0, 'market_price' => 0, 'goods_number' => 0, 'presale_price' => 0, 'dis_amount' => 0, 'seller_amount' => [], 'bonus_goods_price' => 0];

        /* 用于统计购物车中实体商品和虚拟商品的个数 */
        $virtual_goods_count = 0;
        $real_goods_count = 0;
        // 购物车商家商品数量
        $seller_goods_count = 0;
        $goods_list = [];

        if ($cart) {
            foreach ($cart as $k => $v) {
                // 计算总价
                $total['goods_price'] += $v['goods_price'] * $v['goods_number'];
                $total['market_price'] += $v['market_price'] * $v['goods_number'];
                $total['goods_number'] += $v['goods_number'];
                $total['dis_amount'] += $v['dis_amount'];

                //过滤虚拟商品
                if ($v['is_real'] == 1 && $v['extension_code'] != 'virtual_card') {
                    $total['bonus_goods_price'] += $v['goods_price'] * $v['goods_number'];
                    $total['seller_amount'][$v['ru_id']] = isset($total['seller_amount'][$v['ru_id']]) ? $total['seller_amount'][$v['ru_id']] + $v['goods_price'] * $v['goods_number'] : $v['goods_price'] * $v['goods_number'];
                }

                if ($v['rec_type'] == CART_PRESALE_GOODS) {
                    $v['deposit'] = PresaleActivity::where('goods_id', $v['goods_id'])->where('start_time', '<', $now)->where('end_time', '>', $now)->value('deposit');

                    $total['presale_price'] += $v['deposit'] * $v['goods_number'];//预售定金

                    if ($total['presale_price'] > 0) {
                        $total['goods_price'] = $total['presale_price'];
                    }
                }

                $goods_list[$k] = $v;

                /* 统计实体商品和虚拟商品的个数 */
                if ($v['is_real']) {
                    $real_goods_count++;
                } else {
                    $virtual_goods_count++;
                }
                // 购物车商家商品数量
                if ($v['ru_id'] > 0) {
                    $seller_goods_count++;
                }
            }
        }

        $ruCount = BaseRepository::getArraySum($cart, 'ru_id');

        $goodsRuSelf = true;
        if ($ruCount) {
            $goodsRuSelf = false;
        }

        $total['bonus_goods_price'] = $this->dscRepository->getPriceFormat($total['bonus_goods_price'], true, false, $goodsRuSelf);
        $total['goods_price'] = $this->dscRepository->getPriceFormat($total['goods_price'], true, false, $goodsRuSelf);
        $total['market_price'] = $this->dscRepository->getPriceFormat($total['market_price'], true, false, $goodsRuSelf);

        $ru_id_goods_list = [];
        $product_list = [];
        $package_goods_count = 0;
        $package_list_total = 0;

        $goods_id = BaseRepository::getKeyPluck($goods_list, 'goods_id');
        $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'goods_sn', 'goods_name', 'goods_number as product_number', 'market_price', 'goods_thumb', 'is_real', 'shop_price']);

        foreach ($goods_list as $key => $row) {
            $row['goods']['rec_id'] = $row['rec_id'];
            $row['goods']['user_id'] = $row['user_id'];
            $row['goods']['cat_id'] = $row['cat_id'] ?? 0;
            $row['goods']['brand_id'] = $row['brand_id'] ?? 0;
            $row['goods']['goods_name'] = $row['goods_name'];
            $row['goods']['goods_id'] = $row['goods_id'];
            $row['goods']['market_price'] = $row['market_price'];
            $row['goods']['market_price_format'] = $this->dscRepository->getPriceFormat($row['market_price'], true, true, $goodsRuSelf);
            $row['goods']['goods_price'] = $row['goods_price'];
            $row['goods']['goods_price_format'] = $this->dscRepository->getPriceFormat($row['goods_price'], true, true, $goodsRuSelf);
            $row['goods']['goods_number'] = $row['goods_number'];
            $row['goods']['subtotal'] = $row['goods_price'] * $row['goods_number'];
            $row['goods']['goods_attr'] = $row['goods_attr'];
            $row['goods']['is_checked'] = $row['is_checked'];
            $row['goods']['sku_weight'] = $row['sku_weight'];
            $row['goods']['dis_amount'] = $row['dis_amount'];
            $row['goods']['get_goods'] = $row['get_goods'] ?? [];

            $row['goods']['country_icon'] = $row['country_icon'] ?? '';
            $row['goods']['cross_warehouse_name'] = $row['cross_warehouse_name'] ?? '';

            $row['goods']['dis_amount'] = $row['dis_amount'];

            //判断商品类型，如果是超值礼包则修改链接和缩略图
            if ($row['extension_code'] == 'package_buy') {
                /* 取得礼包信息 */
                $activity_thumb = GoodsActivity::where('act_id', $row['goods_id'])->value('activity_thumb');
                $activity_thumb = $activity_thumb ? $activity_thumb : '';

                $row['goods_thumb'] = $activity_thumb;

                $package_goods = PackageGoods::select('package_id', 'goods_id', 'goods_number', 'admin_id')
                    ->where('package_id', $row['goods_id']);
                $package_goods = BaseRepository::getToArrayGet($package_goods);
                if (!empty($package_goods)) {

                    $goods_id = BaseRepository::getKeyPluck($goods_list, 'goods_id');

                    $sql = [
                        'whereIn' => [
                            [
                                'name' => 'goods_id',
                                'value' => $goods_id
                            ]
                        ]
                    ];
                    $package_goods = BaseRepository::getArraySqlGet($package_goods, $sql);

                    if ($package_goods) {
                        foreach ($package_goods as $k => $val) {

                            $goods = $goodsList[$val['goods_id']] ?? [];
                            $val = BaseRepository::getArrayMerge($val, $goods);

                            $package_goods[$k]['goods_thumb'] = $this->dscRepository->getImagePath($val['goods_thumb']);
                            $package_goods[$k]['market_price_format'] = $this->dscRepository->getPriceFormat($val['market_price'], true, true, $goodsRuSelf);
                            $package_goods[$k]['rank_price_format'] = $this->dscRepository->getPriceFormat($val['shop_price'], true, true, $goodsRuSelf);
                        }
                    }
                }

                $row['goods']['package_goods_list'] = $package_goods;

                $subtotal = $row['goods_price'] * $row['goods_number'];
                $package_goods_count++;
                if (!empty($package_goods)) {
                    foreach ($package_goods as $package_goods_val) {
                        $package_goods_val['shop_price'] = $package_goods_val['get_goods']['shop_price'] ?? 0;
                        $package_goods_val['goods_number'] = $package_goods_val['goods_number'] ?? 1;
                        $package_list_total += $package_goods_val['shop_price'] * $package_goods_val['goods_number'];
                    }

                    $row['goods']['package_list_total'] = $package_list_total;
                    $row['goods']['package_list_saving'] = $package_list_total - $subtotal;
                    $row['goods']['format_package_list_total'] = $this->dscRepository->getPriceFormat($row['goods']['package_list_total'], true, true, $goodsRuSelf);
                    $row['goods']['format_package_list_saving'] = $this->dscRepository->getPriceFormat($row['goods']['package_list_saving'], true, true, $goodsRuSelf);
                }
            }

            $row['goods']['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
            $row['goods']['is_real'] = $row['is_real'];
            $row['goods']['goods_attr_id'] = $row['goods_attr_id'];
            $row['goods']['is_shipping'] = $row['is_shipping'];
            $row['goods']['ru_id'] = $row['ru_id'];
            $row['goods']['warehouse_id'] = $row['warehouse_id'];
            $row['goods']['area_id'] = $row['area_id'];
            $row['goods']['area_city'] = $row['area_city'];
            $row['goods']['stages_qishu'] = $row['stages_qishu'];
            $row['goods']['add_time'] = $row['add_time'];
            $row['goods']['goods_sn'] = $row['goods_sn'];
            $row['goods']['product_id'] = $row['product_id'];
            $row['goods']['extension_code'] = $row['extension_code'];
            $row['goods']['parent_id'] = $row['parent_id'];
            $row['goods']['group_id'] = $row['group_id'];
            $row['goods']['parts'] = $row['parts'];
            $row['goods']['is_gift'] = $row['is_gift'];
            $row['goods']['model_attr'] = $row['model_attr'];
            $row['goods']['act_id'] = $row['act_id'];
            $row['goods']['store_id'] = $row['store_id'];

            if (isset($row['membership_card_id']) && !empty($row['membership_card_id'])) {
                // 符合领取设置为指定商品的 会员权益卡id
                $row['goods']['membership_card_id'] = $row['membership_card_id'] ?? 0;
                $row['goods']['membership_card_name'] = UserMembershipCard::where('id', $row['membership_card_id'])->value('name');
            }

            // 按ru_id分组
            $ru_id_goods_list[$row['ru_id']]['shop_name'] = $row['shop_name'];
            $ru_id_goods_list[$row['ru_id']]['user_id'] = $row['user_id'];
            $ru_id_goods_list[$row['ru_id']]['ru_id'] = $row['ru_id'];
            $ru_id_goods_list[$row['ru_id']]['goods'][] = $row['goods'];

            $product_list[$key]['goods'] = $row['goods'];
        }

        $ruIdList = BaseRepository::getKeyPluck($ru_id_goods_list, 'ru_id');
        $ShippingAreaList = ShippingDataHandleService::getShippingAreaDataList([], $ruIdList);
        $shippingIdList = BaseRepository::getKeyPluck($ShippingAreaList, 'shipping_id');
        $shippingIdList = BaseRepository::getArrayUnique($shippingIdList);
        $shippingIdList = ShippingDataHandleService::getShippingDataList($shippingIdList, 1, ['shipping_id', 'shipping_name', 'insure']);
        $shippingIdList = BaseRepository::getKeyPluck($shippingIdList, 'shipping_id');

        foreach ($ru_id_goods_list as $key => $value) {

            $sql = [
                'whereIn' => [
                    [
                        'name' => 'ru_id',
                        'value' => $value['ru_id']
                    ],
                    [
                        'name' => 'shipping_id',
                        'value' => $shippingIdList
                    ]
                ]
            ];
            $shipping = BaseRepository::getArraySqlGet($ShippingAreaList, $sql, 1);

            //商家配送方式
            $ship = [];
            if ($shipping) {
                foreach ($shipping as $k => $val) {
                    if ($val['ru_id'] == $value['ru_id']) {
                        $val['shipping']['ru_id'] = $val['ru_id'];
                        $val['shipping']['configure'] = $val['configure'];
                        $ship[] = $val['shipping'];
                    }
                }
            }

            $ru_id_goods_list[$key]['shop_info'] = $ship;
        }

        $total['saving'] = round($total['market_price'] - $total['goods_price'], 2);
        if ($total['saving'] > 0) {
            $total['save_rate'] = $total['market_price'] ? round($total['saving'] * 100 / $total['market_price']) . '%' : 0;
        }
        $total['saving_formated'] = $this->dscRepository->getPriceFormat($total['saving'], false, true, $goodsRuSelf);
        $total['dis_amount_formated'] = $this->dscRepository->getPriceFormat($total['dis_amount'], false, true, $goodsRuSelf);
        $total['goods_price_formated'] = $this->dscRepository->getPriceFormat($total['goods_price'], false, true, $goodsRuSelf);
        $total['market_price_formated'] = $this->dscRepository->getPriceFormat($total['market_price'], false, true, $goodsRuSelf);
        $total['real_goods_count'] = $real_goods_count;
        $total['virtual_goods_count'] = $virtual_goods_count;
        $total['seller_goods_count'] = $seller_goods_count;

        return ['goods_list' => $ru_id_goods_list, 'goodsRuSelf' => $goodsRuSelf, 'total' => $total, 'product' => $product_list, 'get_goods_list' => $goods_list];
    }

    /**
     * 获取商品分类树
     * @param int $cat_id
     * @return array
     */
    public function catList($cat_id = 0)
    {
        $arr = [];
        $count = Category::where('parent_id', $cat_id)
            ->where('is_show', 1)
            ->count();
        if ($count > 0) {
            $res = Category::select('cat_id', 'cat_name', 'touch_icon', 'parent_id', 'cat_alias_name', 'is_show')
                ->where('parent_id', $cat_id)
                ->where('is_show', 1)
                ->orderby('sort_order', 'ASC')
                ->orderby('cat_id', 'ASC')
                ->get()
                ->toArray();

            if ($res === null) {
                return [];
            }

            foreach ($res as $key => $row) {
                if (isset($row['cat_id'])) {
                    $arr[$row['cat_id']]['cat_id'] = $row['cat_id'];
                    $child_tree = $this->catList($row['cat_id']);
                    if ($child_tree) {
                        $arr[$row['cat_id']]['child_tree'] = $child_tree;
                    }
                }
            }
        }
        return $arr;
    }

    /*根据地区ID获取具体配送地区*/
    public function DeliveryArea($region_id)
    {
        if (empty($region_id)) {
            return '';
        }

        $res = Region::where('region_id', $region_id)->value('region_name');
        return $res ?? '';
    }

    /**
     * 过滤用户输入的基本数据，防止script攻击
     *
     * @access      public
     * @return      string
     */
    public function compile_str($str)
    {
        $arr = ['<' => '＜', '>' => '＞', '"' => '”', "'" => '’'];

        return strtr($str, $arr);
    }

    //提交订单自提点分单
    public function get_order_points($point_id_arr, $shipping_dateStr_arr, $ru_id = [])
    {
        $points_info = [];
        if ($point_id_arr) {
            $point_id = '';
            $shipping_dateStr = '';
            foreach ($point_id_arr as $k1 => $v1) {
                $v1 = !empty($v1) ? $v1 : 0;
                $shipping_dateStr_arr[$k1] = !empty($shipping_dateStr_arr[$k1]) ? addslashes($shipping_dateStr_arr[$k1]) : '';
                foreach ($ru_id as $k2 => $v2) {
                    if ($k1 == $k2) {
                        $point_id .= $v2 . "|" . $v1 . ",";  //商家ID + 配送ID
                        $shipping_dateStr .= $v2 . "|" . $shipping_dateStr_arr[$k1] . ",";  //商家ID + 配送名称
                    }
                }
            }

            $point_id = substr($point_id, 0, -1);
            $shipping_dateStr = substr($shipping_dateStr, 0, -1);
            $points_info = [
                'point_id' => $point_id,
                'shipping_dateStr' => $shipping_dateStr,
            ];
        }
        return $points_info;
    }

    /**
     * 查询配送区域属于哪个办事处管辖
     * @param array $regions 配送区域（1、2、3、4级按顺序）
     * @return  int     办事处id，可能为0
     */
    public function get_agency_by_regions($regions)
    {
        if (!is_array($regions) || empty($regions)) {
            return 0;
        }

        $regions = !is_array($regions) ? explode(',', $regions) : $regions;

        $res = Region::whereIn('region_id', $regions)
            ->where('region_id', '>', 0)
            ->where('agency_id', '>', 0);
        $res = $res->get();

        $res = $res ? $res->toArray() : [];

        $arr = [];
        if ($res) {
            foreach ($res as $row) {
                $arr[$row['region_id']] = $row['agency_id'];
            }
        }

        if (empty($arr)) {
            return 0;
        }

        for ($i = count($regions) - 1; $i >= 0; $i--) {
            if (isset($arr[$regions[$i]])) {
                return $arr[$regions[$i]];
            }
        }
    }

    /**
     * 取得红包信息
     *
     * @param int $bonus_id
     * @param string $bonus_sn
     * @param int $cart_value
     * @return array
     */
    public function bonus_info($bonus_id = 0, $bonus_sn = '', $cart_value = 0)
    {
        $goods_user = [];
        if ($cart_value != 0 || !empty($cart_value)) {
            $cart_value = !is_array($cart_value) ? explode(",", $cart_value) : $cart_value;

            $goods_list = Cart::select('ru_id', 'goods_id')
                ->whereIn('rec_id', $cart_value);
            $goods_list = BaseRepository::getToArrayGet($goods_list);

            if ($goods_list) {
                $goods_id = BaseRepository::getKeyPluck($goods_list, 'goods_id');
                $goods_id = Goods::select('goods_id')
                    ->whereIn('goods_id', $goods_id)
                    ->pluck('goods_id');
                $goods_id = BaseRepository::getToArray($goods_id);

                $sql = [
                    'whereIn' => [
                        [
                            'name' => 'goods_id',
                            'value' => $goods_id
                        ]
                    ]
                ];
                $goods_list = BaseRepository::getArraySqlGet($goods_list, $sql);

                $goods_user = BaseRepository::getKeyPluck($goods_list, 'ru_id');
            }
        }

        if ($bonus_id > 0) {
            $bonus = UserBonus::where('bonus_id', $bonus_id);
        } else {
            $bonus = UserBonus::where('bonus_sn', $bonus_sn);
        }

        $bonus = BaseRepository::getToArrayFirst($bonus);

        if ($goods_user && $bonus) {
            $goods_user = BaseRepository::getExplode($goods_user);

            $bonusType = BonusType::where('review_status', 3)
                ->where('type_id', $bonus['bonus_type_id'])
                ->whereRaw("IF(usebonus_type > 0, usebonus_type = 1, user_id in($goods_user))");
            $bonusType = BaseRepository::getToArrayFirst($bonusType);

            if ($bonusType) {
                $bonusType['admin_id'] = $bonusType['user_id'] ?? 0;
                $bonus = $bonusType ? array_merge($bonus, $bonusType) : $bonus;
            } else {
                $bonus = [];
            }
        }

        return $bonus;
    }

    /**
     * 取得购物车总金额
     * @params  boolean $include_gift   是否包括赠品
     * @param int $type 类型：默认普通商品
     * @return  float   购物车总金额
     */
    public function cart_amount($uid, $include_gift = true, $type = CART_GENERAL_GOODS, $cart_value = '')
    {
        $res = Cart::where('user_id', $uid);

        if (!$include_gift) {
            $res = $res->where('is_gift', 0)->where('goods_id', '>', 0);
        }

        if ($cart_value) {
            $res = $res->whereIn('rec_id', $cart_value);
        }
        $res = $res->sum('goods_price', '*', 'goods_number');

        return $res;
    }

    public function combination($goods = [])
    {
        $res = [];
        foreach ($goods as $k => $v) {
            $res[$k] = $v['rec_id'];
        }

        return implode(',', $res);
    }

    public function get_region_name($region_id)
    {
        return Region::where('region_id', $region_id)->value('region_name');
    }

    /**
     * 获得用户的可用积分
     *
     * @param array $cart_value
     * @param int $flow_type
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param int $user_id
     * @return float|int
     */
    public function flowAvailablePoints($cart_value = [], $flow_type = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0, $user_id = 0)
    {
        $res = Cart::select('goods_id', 'model_attr', 'goods_number')->where('rec_type', $flow_type);

        if ($cart_value) {
            $cart_value = BaseRepository::getExplode($cart_value);
            $res = $res->whereIn('rec_id', $cart_value);
        }

        if (!empty($user_id)) {
            $res = $res->where('user_id', $user_id);
        } else {
            $session_id = $this->sessionRepository->realCartMacIp();
            $res = $res->where('session_id', $session_id);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {

            $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');

            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id);
            $warehouseGoodsList = GoodsDataHandleService::getWarehouseGoodsDataList($goods_id, $warehouse_id);
            $warehouseAreaGoodsList = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods_id, $area_id, $area_city);

            foreach ($res as $key => $val) {

                $goods = $goodsList[$val['goods_id']] ?? [];
                $warehouseGoods = $warehouseGoodsList[$val['goods_id']] ?? [];
                $warehouseAreaGoods = $warehouseAreaGoodsList[$val['goods_id']] ?? [];

                if ($val['model_attr'] == 1) {
                    $ar = $warehouseGoods['pay_integral'] ?? 0;
                } elseif ($val['model_attr'] == 2) {
                    $ar = $warehouseAreaGoods['pay_integral'] ?? 0;
                } else {
                    $ar = $goods['integral'] ?? 0;
                }

                $arr[$key]['integral'] = $ar;
                $arr[$key]['goods_number'] = $val['goods_number'];
            }
        }

        $result = BaseRepository::getArraySum($arr, ['integral', 'goods_number']);

        $scale = 0;
        if ($result) {
            $scale = config('shop.integral_scale');
        }

        return $scale > 0 ? round($result / $scale * 100) : 0;
    }

    /**
     * 购物车商品信息
     *
     * @access  public
     * @param array $where
     * @return  array
     */
    public function get_cart_info($where = [])
    {
        $user_id = isset($where['user_id']) ? $where['user_id'] : 0;
        $where['rec_type'] = isset($where['rec_type']) ? $where['rec_type'] : CART_GENERAL_GOODS;

        $row = Cart::selectRaw("*, COUNT(*) AS cart_number, SUM(goods_number) AS number, SUM(goods_price * goods_number) AS amount")
            ->where('rec_type', $where['rec_type']);

        /* 附加查询条件 start */
        if (isset($where['rec_id'])) {
            $where['rec_id'] = !is_array($where['rec_id']) ? explode(",", $where['rec_id']) : $where['rec_id'];

            if (is_array($where['rec_id'])) {
                $row = $row->whereIn('rec_id', $where['rec_id']);
            } else {
                $row = $row->where('rec_id', $where['rec_id']);
            }
        }

        if (isset($where['stages_qishu'])) {
            $row = $row->where('stages_qishu', $where['stages_qishu']);
        }

        if (isset($where['store_id'])) {
            $row = $row->where('store_id', $where['store_id']);
        } else {
            $row = $row->where('store_id', 0);
        }
        /* 附加查询条件 end */

        if (!empty($user_id)) {
            $row = $row->where('user_id', $user_id);
        } else {
            $session_id = $this->sessionRepository->realCartMacIp();
            $row = $row->where('session_id', $session_id);
        }

        $row = $row->first();

        $row = $row ? $row->toArray() : [];

        return $row;
    }

    /**
     * 使用余额支付
     *
     * @param $uid
     * @param string $order_sn
     * @return array
     * @throws \Exception
     */
    public function Balance($uid, $order_sn = '')
    {
        $order = [];
        $time = TimeRepository::getGmTime();

        if ($order_sn) {
            $order_info = OrderInfo::where('order_sn', $order_sn)
                ->where('user_id', $uid)
                ->first();
            $order_info = $order_info ? $order_info->toArray() : '';

            $order_amount = $order_info['order_amount'] ?? 0;
            $order_amount = floatval($order_amount);

            $order_status = [
                OS_CANCELED,
                OS_INVALID,
                OS_RETURNED
            ];

            //订单详情
            if ($order_info && $order_amount > 0 && !in_array($order_info['order_status'], $order_status) && $order_info['pay_status'] != PS_PAYED) {

                $user_info = $this->UserIntegral($uid);
                $pay_money = 0;

                /* 如果全部使用余额支付，检查余额是否足够 */
                if ($order_info['order_amount'] > 0) {
                    if ($order_info['order_amount'] > ($user_info['user_money'] + $user_info['credit_line'])) {
                        $order_info['surplus'] = $user_info['user_money'];
                        $order_info['order_amount'] = $order_info['order_amount'] - $user_info['user_money'];
                        return ['msg' => L('balance_not_enough')];
                    } else {
                        $order['surplus'] = $order_info['surplus'] + $order_info['order_amount'];
                        $pay_money = $order_info['order_amount'] * (-1);
                        $order['order_amount'] = 0;
                    }

                    $payment = payment_info('balance', 1);
                    $order['pay_name'] = addslashes($payment['pay_name']);
                    $order['pay_id'] = $payment['pay_id'];
                    $order['is_online'] = 0;
                    $order['pay_name'] = $payment['pay_name'];
                    $order['pay_code'] = $payment['pay_code'];
                    $order['pay_desc'] = $payment['pay_desc'] ?? '';
                }

                /* 如果订单金额为0（使用余额或积分或红包支付），修改订单状态为已确认、已付款 */
                if ($order['order_amount'] <= 0) {
                    $order['order_status'] = OS_CONFIRMED;
                    $order['confirm_time'] = $time;
                    $order['pay_status'] = PS_PAYED;
                    $order['pay_time'] = $time;
                    $order['order_amount'] = 0;
                    $order['is_surplus'] = 1;

                    if ($order_info['main_count'] > 0) {
                        $order['main_pay'] = 2;
                    }
                }
                //更新余额
                log_account_change($uid, $pay_money, 0, 0, 0, sprintf(lang('presale.presale_pay_end_money'), $order_sn));

                update_order($order_info['order_id'], $order);
            } else {
                if (in_array($order_info['order_status'], $order_status)) {
                    $order_info['order_amount'] = -1; //返回支付页
                }
                $order = $order_info;
            }
        }

        return $order;
    }

    /**
     * 再次购买
     *
     * @param int $user_id
     * @param int $order_id
     * @param string $from
     * @return array
     * @throws \Exception
     */
    public function BuyAgain($user_id = 0, $order_id = 0, $from = 'wap')
    {
        $result = ['error' => 0, 'msg' => ''];
        if ($user_id && $order_id) {
            $res = OrderGoods::select(['goods_id', 'product_id', 'goods_attr', 'goods_attr_id', 'warehouse_id', 'area_id', 'ru_id', 'area_city', 'extension_code'])
                ->where(['user_id' => $user_id, 'order_id' => $order_id])
                ->with([
                    'getGoods' => function ($query) {
                        $query->selectRaw("*, goods_id AS id");
                    }
                ]);
            $res = BaseRepository::getToArrayGet($res);

            $cant_buy_goods = [];//不可以购买的商品
            $time = TimeRepository::getGmTime();
            if ($res) {
                //PC购物车勾选是通过session中的cart_value的值
                if ($from === 'pc') {
                    session(['cart_value' => '']);
                } else {
                    //把所有此用户的购物车商品全部取消勾选
                    Cart::where('user_id', $user_id)->update(['is_checked' => 0]);
                }

                foreach ($res as $key => $row) {
                    //检查商品的库存是否可以再次购买
                    if ($row['product_id']) {
                        $num = Products::where('product_id', $row['product_id'])->where('goods_id', $row['goods_id'])->value('product_number');
                        //库存为0，把数组赋值给不可以购买的商品数组
                        if ($num <= 0) {
                            $cant_buy_goods[$key] = $row;
                            continue;
                        }
                    } else {
                        $num = Goods::where('goods_id', $row['goods_id'])->value('goods_number');
                        //库存为0，把数组赋值给不可以购买的商品数组
                        if ($num <= 0) {
                            $cant_buy_goods[$key] = $row;
                            continue;
                        }
                    }

                    $row = array_merge($row, $row['get_goods']);
                    unset($row['get_goods']);

                    //检查商品是否上架，删除,开启限购并且当前时间正在限购时间内，开启最小起订量，并且在规定时间内
                    if ($row['is_delete'] == 1 || $row['is_show'] == 0 || $row['is_alone_sale'] == 0 || (!empty($row['extension_code']) && $row['extension_code'] != 'virtual_card') || ($row['is_xiangou'] == 1 && $row['xiangou_start_date'] < $time && $row['xiangou_end_date'] > $time) || ($row['is_minimum'] == 1 && $row['minimum_start_date'] < $time && $row['minimum_end_date'] > $time)) {
                        $cant_buy_goods[$key] = $row;
                        continue;
                    }

                    $property = isset($row['goods_attr_id']) ? explode(',', $row['goods_attr_id']) : [];
                    $final_price = app(GoodsMobileService::class)->getFinalPrice($user_id, $row['goods_id'], 1, true, $property, $row['warehouse_id'], $row['area_id'], $row['area_city']);

                    $add = Cart::where(['goods_id' => $row['goods_id'], 'user_id' => $user_id]);
                    if ($row['product_id']) {
                        $add = $add->where('product_id', $row['product_id']);
                    }
                    $add = $add->value('rec_id');

                    //如果购物车已有此商品则添加一个数量
                    if ($add > 0) {
                        //更新购物车数量及增加勾选
                        $up = Cart::where(['goods_id' => $row['goods_id'], 'user_id' => $user_id]);
                        if ($row['product_id']) {
                            $up = $up->where('product_id', $row['product_id']);
                        }

                        //pc和手机端勾选不同
                        if ($from === 'pc') {
                            $this->cartCommonService->getCartValue($add);
                            $up->increment('goods_number', 1);
                        } else {
                            $up->increment('goods_number', 1, ['is_checked' => 1]);
                        }
                    } else {
                        //如果购物车无此商品，则插入
                        $cart = [
                            'user_id' => $user_id,
                            'session_id' => $user_id,
                            'goods_id' => $row['goods_id'],
                            'goods_sn' => $row['goods_sn'],
                            'product_id' => $row['product_id'],
                            'goods_name' => $row['goods_name'],
                            'market_price' => $row['market_price'],
                            'goods_attr' => $row['goods_attr'],
                            'goods_attr_id' => $row['goods_attr_id'],
                            'is_real' => 1,
                            'model_attr' => 0,
                            'warehouse_id' => '0',
                            'area_id' => '0',
                            'ru_id' => $row['ru_id'],
                            'is_gift' => 0,
                            'is_shipping' => 0,
                            'rec_type' => '0',
                            'add_time' => $time,
                            'freight' => $row['freight'],
                            'tid' => $row['tid'],
                            'shipping_fee' => $row['shipping_fee'],
                            'commission_rate' => $row['commission_rate'],
                            'store_id' => 0,
                            'store_mobile' => 0,
                            'take_time' => '',
                            'goods_price' => $final_price,
                            'goods_number' => '1',
                            'parent_id' => 0,
                            'stages_qishu' => '-1',
                            'is_checked' => 1,
                            'extension_code' => $row['extension_code'] == 'virtual_card' ? 'virtual_card' : ''
                        ];

                        //pc和手机端勾选不同
                        if ($from === 'pc') {
                            $new_cart = BaseRepository::getArrayfilterTable($cart, 'cart');
                            $rec_id = Cart::insertGetId($new_cart);

                            if ($rec_id > 0) {
                                $this->cartRepository->pushCartValue($rec_id);
                            }
                        } else {
                            Cart::insert($cart);
                        }
                    }
                }
                if ($cant_buy_goods) {
                    foreach ($cant_buy_goods as $key => $v) {
                        $goods_thumb = $v['goods_thumb'] ?? (isset($v['get_goods']['goods_thumb']) ? $v['get_goods']['goods_thumb'] : '');
                        $cant_buy_goods[$key]['goods_thumb'] = $this->dscRepository->getImagePath($goods_thumb);
                    }
                }

                $result['error'] = 0;
                $result['msg'] = lang('user.return_to_cart_success');
                $result['cant_buy_goods'] = $cant_buy_goods;
            } else {
                $result['error'] = 1;
                $result['msg'] = lang('user.unknow_error');
            }
        } else {
            $result['error'] = 1;
            $result['msg'] = lang('user.unknow_error');
        }

        return $result;
    }

    /**
     * 结算页选择收货人地址
     *
     * @param int $user_id
     * @param int $address_id
     * @param int $leader_id
     * @return array|bool|\Illuminate\Cache\CacheManager|mixed
     * @throws \Exception
     */
    public function change_consignee($user_id = 0, $address_id = 0, $leader_id = 0)
    {
        if (empty($user_id)) {
            return [];
        }

        $consignee = cache('flow_consignee_' . $user_id);
        $consignee = !is_null($consignee) ? $consignee : [];

        if (empty($consignee)) {
            /* 如果不存在cache，则取得用户的默认收货人信息 */
            $consignee = $this->userAddressService->getDefaultByUserId($user_id);
            if ($consignee) {
                $consignee['default'] = 1;
            }
        }

        /* 如果存在cache，则直接返回cache中的收货人信息 */
        $consignee = !empty($address_id) ? [] : $consignee;

        // 保存至缓存
        if ($address_id > 0) {
            $consignee = $this->userAddressService->getUpdateFlowConsignee($address_id, $user_id);
        }

        if ($leader_id > 0) {
            // 社区驿站
            if (file_exists(MOBILE_GROUPBUY)) {
                $post = app(CgroupService::class)->postExists();
                if (!empty($post)) {
                    $consignee = cache('flow_consignee_' . $user_id);
                    $consignee = !is_null($consignee) ? $consignee : [];

                    return $post->getPostConsignee($consignee, $leader_id);
                }
            }
        }

        if ($consignee) {
            cache()->put('flow_consignee_' . $user_id, $consignee, Carbon::now()->addDays(1));
        }

        return $consignee ?? [];
    }

    /**
     * 合算购物流程红包、储值卡切换获取运费金额
     *
     * @param array $total
     * @param int $uid
     * @param array $shipping_id
     * @param array $order
     * @return array
     * @throws \Exception
     */
    private function flowShippingFee($total = [], $uid = 0, $shipping_id = [], $order = [])
    {
        /* 计算运费 */
        $uc_id = BaseRepository::getExplode($total['coupons_id']);
        $order_shipping_fee = 0;

        $cart_goods = [];
        $is_integral = $order['is_integral'] ?? 0;
        $rec_ids = $this->getRecList($total);
        if (!empty($shipping_id) || $is_integral > 0) {
            $cart_goods = Cart::select('rec_id', 'ru_id', 'goods_id', 'user_id', 'goods_price', 'goods_number', 'shipping_fee', 'extension_code', 'tid', 'freight', 'model_attr', 'warehouse_id', 'area_id', 'area_city', 'product_id')
                ->where('user_id', $uid)
                ->where('is_checked', 1)
                ->whereIn('rec_id', $rec_ids);
            $cart_goods = BaseRepository::getToArrayGet($cart_goods);

            /* 处理积分 */
            if ($cart_goods) {

                $goods_id = BaseRepository::getKeyPluck($cart_goods, 'goods_id');

                $goodsWhere['goods_select'] = [
                    'goods_id', 'integral', 'goods_weight', 'tid'
                ];
                $goodsList = GoodsDataHandleService::GoodsCartDataList($goods_id, $goodsWhere);

                $recGoodsModelList = BaseRepository::getColumn($cart_goods, 'model_attr', 'rec_id');
                $recGoodsModelList = $recGoodsModelList ? array_unique($recGoodsModelList) : [];

                $isModel = 0;
                if (in_array(1, $recGoodsModelList) || in_array(2, $recGoodsModelList)) {
                    $isModel = 1;
                }

                if ($isModel == 1) {
                    $warehouseGoodsList = GoodsDataHandleService::getWarehouseGoodsDataList($goods_id, 0, ['goods_id', 'pay_integral', 'region_id']);
                    $warehouseAreaGoodsList = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods_id, 0, 0, ['goods_id', 'region_id', 'city_id']);
                } else {
                    $warehouseGoodsList = [];
                    $warehouseAreaGoodsList = [];
                }

                $product_id = BaseRepository::getKeyPluck($cart_goods, 'product_id');
                $productsList = GoodsDataHandleService::getProductsDataList($product_id, '*', 'product_id');

                if ($isModel == 1) {
                    $productsWarehouseList = GoodsDataHandleService::getProductsWarehouseDataList($product_id, 0, '*', 'product_id');
                    $productsAreaList = GoodsDataHandleService::getProductsAreaDataList($product_id, 0, '*', 'product_id');
                } else {
                    $productsWarehouseList = [];
                    $productsAreaList = [];
                }

                foreach ($cart_goods as $key => $row) {

                    $goods = $goodsList[$row['goods_id']] ?? [];

                    $cart_goods[$key]['get_goods'] = $goods;
                    $cart_goods[$key]['tid'] = $goods['tid'] ?? 0;

                    /* 为兼容跨境代码写法 */
                    $warehouseGoods = $warehouseGoodsList[$row['goods_id']] ?? [];
                    $sql = [
                        'where' => [
                            [
                                'name' => 'region_id',
                                'value' => $row['warehouse_id']
                            ]
                        ]
                    ];
                    $warehouseGoods = BaseRepository::getArraySqlFirst($warehouseGoods, $sql);

                    $areaGoods = $warehouseAreaGoodsList[$row['goods_id']] ?? [];
                    $sql = [
                        'where' => [
                            [
                                'name' => 'region_id',
                                'value' => $row['area_id']
                            ]
                        ]
                    ];

                    if (config('shop.area_pricetype') == 1) {
                        $sql['where'][] = [
                            'name' => 'city_id',
                            'value' => $row['area_city']
                        ];
                    }
                    $areaGoods = BaseRepository::getArraySqlFirst($areaGoods, $sql);

                    // 商品信息
                    if ($row['model_attr'] == 1) {
                        $cart_goods[$key]['sku_weight'] = $productsWarehouseList[$row['product_id']]['sku_weight'] ?? 0; //货品重量
                        $integral = $warehouseGoods['pay_integral'] ?? 0;
                    } elseif ($row['model_attr'] == 2) {
                        $cart_goods[$key]['sku_weight'] = $productsAreaList[$row['product_id']]['sku_weight'] ?? 0; //货品重量
                        $integral = $areaGoods['pay_integral'] ?? 0;
                    } else {
                        $cart_goods[$key]['sku_weight'] = $productsList[$row['product_id']]['sku_weight'] ?? 0; //货品重量
                        $integral = $goods['integral'] ?? 0;
                    }

                    /**
                     * 取最小兑换积分
                     */
                    $integral = [
                        $this->dscRepository->integralOfValue($row['goods_price'] * $row['goods_number']),
                        $this->dscRepository->integralOfValue($integral * $row['goods_number'])
                    ];

                    $integral = BaseRepository::getArrayMin($integral);
                    $cart_goods[$key]['integral_total'] = $this->dscRepository->valueOfIntegral($integral);
                }
            }

            $this->flowOrderService->orderEquallyGoodsIntegral($cart_goods, $order, $total);
        }

        $shippingList = [];
        $total['ru_shipping_fee_list'] = [];
        if (!empty($shipping_id)) {

            $ru_id = $total['ru_id'] ?? [];
            $consignee = $total['consignee'] ?? [];

            $couponsList = $this->couponsUserService->getCouponsUserSerList($uc_id, $uid, $cart_goods);
            $uc_id = BaseRepository::getKeyPluck($couponsList, 'uc_id');

            $rec_goods_id = BaseRepository::getColumn($cart_goods, 'goods_id', 'rec_id');
            $rec_extension_code = BaseRepository::getColumn($cart_goods, 'extension_code', 'rec_id');

            $realGoodsList = [];
            if ($rec_extension_code) {
                foreach ($rec_extension_code as $rec_id => $val) {
                    if ($val != 'package_buy') {
                        $realGoodsList[$rec_id] = $rec_goods_id[$rec_id] ?? 0;
                    }
                }
            }

            $realGoodsList = GoodsDataHandleService::GoodsDataList($realGoodsList, ['goods_id', 'cat_id']);
            if ($cart_goods) {
                foreach ($cart_goods as $key => $val) {
                    if ($val['extension_code'] != 'package_buy') {
                        $cart_goods[$key]['cat_id'] = $realGoodsList[$val['goods_id']]['cat_id'];
                    } else {
                        $cart_goods[$key]['cat_id'] = 0;
                    }

                    $rank = app(UserCommonService::class)->getUserRankByUid($uid);
                    $user_rank = $rank['rank_id'] ?? 0;
                    $cart_goods[$key]['user_rank'] = $user_rank;
                }
            }

            $tmp_shipping_id = [];
            if ($ru_id && $shipping_id && count($ru_id) == count($shipping_id)) {
                $tmp_shipping_id = BaseRepository::getArrayCombine($ru_id, $shipping_id);
            }

            $shippingList = $this->shippingService->goodsShippingTransport($cart_goods, $consignee, $uc_id, $tmp_shipping_id);

            $ruShippingInfo = $this->shippingService->orderFeeShipping($cart_goods, $ru_id, $shippingList, $tmp_shipping_id);
            $total['ru_shipping_fee_list'] = $ruShippingInfo['ru_shipping_fee_list'] ?? 0;

            /* 已扣除免邮金额 */
            $order_shipping_fee = $ruShippingInfo['shipping_fee'] ?? 0;
            $order_shipping_fee = $this->dscRepository->getPriceFormat($order_shipping_fee, true, false);
        }

        $order_shipping_fee = $order_shipping_fee ?? 0;

        /* 查询购物车商品运费总金额 */
        $shipping_fee = $this->shippingService->cartShippingListTotal($shippingList);
        $shipping_fee = $this->dscRepository->changeFloat($shipping_fee);

        /* 购物车商品实际应付运费总金额 */
        $order_shipping_fee = $this->dscRepository->changeFloat($order_shipping_fee);

        $total['shopping_fee'] = $shipping_fee;
        if ($order_shipping_fee > 0) {
            $total['free_shipping_fee'] = $shipping_fee - $order_shipping_fee; //免邮券总金额
        } else {
            $total['free_shipping_fee'] = 0; //免邮券总金额
        }

        return $total;
    }

    /**
     * 购物车商品
     *
     * @param array $total
     * @param int $uid
     * @return array
     */
    private function mobileCartGoods($total = [], $uid = 0)
    {
        $recList = $this->getRecList($total);

        $cart_goods = [];
        if ($recList) {
            $cart_goods = Cart::select('rec_id', 'goods_id', 'ru_id', 'extension_code', 'goods_price', 'goods_number', 'goods_bonus', 'goods_coupons', 'goods_favourable')
                ->where('user_id', $uid)
                ->where('extension_code', '<>', 'package_buy')
                ->whereIn('rec_id', $recList);
            $cart_goods = BaseRepository::getToArrayGet($cart_goods);

            if ($cart_goods) {

                $cartIdList = FlowRepository::cartGoodsAndPackage($cart_goods);
                $goods_id = $cartIdList['goods_id']; //普通商品ID
                $goodsConsumptionList = GoodsDataHandleService::GoodsConsumptionDataList($goods_id);

                $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, 'goods_id', 'cat_id');
                foreach ($cart_goods as $key => $row) {
                    $goods = $goodsList[$row['cat_id']] ?? [];
                    $cart_goods[$key]['cat_id'] = $goods['cat_id'] ?? 0;

                    $subtotal = $row['goods_price'] * $row['goods_number'];

                    $goodsConsumption = $goodsConsumptionList[$row['goods_id']] ?? [];
                    if ($goodsConsumption) {
                        $amount = $this->dscRepository->getGoodsConsumptionPrice($goodsConsumption, $subtotal);
                    } else {
                        $amount = $subtotal;
                    }

                    $cart_goods[$key]['subtotal'] = $subtotal;
                    $cart_goods[$key]['dis_amount'] = $subtotal - $amount;
                }
            }
        }

        return $cart_goods;
    }

    /**
     * 处理优惠活动金额
     *
     * @param array $cart_goods
     * @return array
     */
    protected function favourableGoodsListDiscount($cart_goods = [])
    {
        $discount = 0;
        $cartGoodsList = [];
        if ($cart_goods) {
            foreach ($cart_goods as $key => $goods) {

                $cartGoodsList[$key] = $goods;

                $new_list = $goods['new_list'] ?? [];

                if ($new_list) {
                    foreach ($new_list as $idx => $row) {
                        if (isset($row['available']) && $row['available'] == true) {
                            $discount += $row['goods_fav_total'] ?? 0;
                        }

                        $cartGoodsList[$key]['new_list'][$idx] = $row;
                    }
                }

                $cartGoodsList[$key]['new_list'] = $cartGoodsList[$key]['new_list'] ? array_values($cartGoodsList[$key]['new_list']) : $cartGoodsList[$key]['new_list'];

                $cartGoodsList[$key]['goods'] = $goods['goods'];
                $cartGoodsList[$key]['goods_count'] = BaseRepository::getArraySum($cartGoodsList[$key]['goods'], 'goods_number');
            }

            $discount = $this->dscRepository->changeFloat($discount);
        }

        $get_goods_list = FlowRepository::getNewGroupCartGoods($cartGoodsList);

        return [
            'discount' => $discount,
            'cartGoodsList' => $cartGoodsList,
            'get_goods_list' => $get_goods_list
        ];
    }

    /**
     * 提交订单的购物车商品
     *
     * @param int $uid
     * @param array $cart_value
     * @param int $flow_type
     * @param int $store_id
     * @return array
     */
    protected function doneCartGoodsList($uid = 0, $cart_value = [], $flow_type = CART_GENERAL_GOODS, $store_id = 0)
    {
        if (empty($cart_value)) {
            return [];
        }

        $cart_value = BaseRepository::getExplode($cart_value);
        $res = Cart::whereIn('rec_id', $cart_value)
            ->where('is_checked', 1)
            ->where('rec_type', $flow_type)
            ->where('parent_id', 0)
            ->where('is_gift', 0)
            ->where('store_id', $store_id);

        if ($uid > 0) {
            $res = $res->where('user_id', $uid);
        } else {
            $session_id = $this->sessionRepository->realCartMacIp();
            $res = $res->where('session_id', $session_id);
        }

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $cartIdList = FlowRepository::cartGoodsAndPackage($res);
            $goods_id = $cartIdList['goods_id']; //普通商品ID

            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'integral']);
            $warehouseGoodsList = GoodsDataHandleService::getWarehouseGoodsDataList($goods_id);
            $warehouseAreaGoodsList = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods_id);

            foreach ($res as $key => $value) {

                if ($value['extension_code'] != 'package_buy') {
                    $goods = $goodsList[$value['goods_id']];

                    /* 为兼容跨境代码写法 */
                    $warehouseGoods = $warehouseGoodsList[$value['goods_id']] ?? [];
                    $sql = [
                        'where' => [
                            [
                                'name' => 'region_id',
                                'value' => $value['warehouse_id']
                            ]
                        ]
                    ];
                    $warehouseGoods = BaseRepository::getArraySqlFirst($warehouseGoods, $sql);

                    $areaGoods = $warehouseAreaGoodsList[$value['goods_id']] ?? [];
                    $sql = [
                        'where' => [
                            [
                                'name' => 'region_id',
                                'value' => $value['area_id']
                            ]
                        ]
                    ];

                    if (config('shop.area_pricetype') == 1) {
                        $sql['where'][] = [
                            'name' => 'city_id',
                            'value' => $value['area_city']
                        ];
                    }
                    $areaGoods = BaseRepository::getArraySqlFirst($areaGoods, $sql);

                    // 商品信息
                    if ($value['model_attr'] == 1) {
                        $integral = $warehouseGoods['pay_integral'] ?? 0;
                    } elseif ($value['model_attr'] == 2) {
                        $integral = $areaGoods['pay_integral'] ?? 0;
                    } else {
                        $integral = $goods['integral'] ?? 0;
                    }

                    /**
                     * 取最小兑换积分
                     */
                    $integral = [
                        app(DscRepository::class)->integralOfValue($value['goods_price'] * $value['goods_number']),
                        app(DscRepository::class)->integralOfValue($integral * $value['goods_number'])
                    ];

                    $integral = BaseRepository::getArrayMin($integral);
                    $res[$key]['integral_total'] = app(DscRepository::class)->valueOfIntegral($integral);
                }
            }
        }

        return $res;
    }

    /**
     * 手机端购物车提交订单商品[红包、优惠券均摊]
     *
     * @param array $rec_list
     * @param int $uid
     * @return mixed
     */
    protected function couponsBonusFlowGoods($rec_list = [], $uid = 0)
    {
        $cart_goods = Cart::select('rec_id', 'goods_id', 'model_attr', 'user_id', 'product_id', 'goods_price', 'goods_number', 'extension_code', 'rec_type', 'is_gift', 'parent_id', 'ru_id', 'tid', 'freight')
            ->whereIn('rec_id', $rec_list)
            ->where('user_id', $uid)
            ->where('is_checked', 1);

        $cart_goods = $cart_goods->orderByRaw("group_id DESC, parent_id ASC, rec_id DESC");

        $cart_goods = BaseRepository::getToArrayGet($cart_goods);

        $cartIdList = FlowRepository::cartGoodsAndPackage($cart_goods);
        $goods_id = $cartIdList['goods_id']; //普通商品ID
        $goodsConsumptionList = GoodsDataHandleService::GoodsConsumptionDataList($goods_id);

        $goodsWhere = [
            'goods_select' => ['goods_id', 'goods_weight', 'tid']
        ];
        $goodsList = GoodsDataHandleService::GoodsCartDataList($goods_id, $goodsWhere);

        $recGoodsModelList = BaseRepository::getColumn($cart_goods, 'model_attr', 'rec_id');
        $recGoodsModelList = $recGoodsModelList ? array_unique($recGoodsModelList) : [];

        $isModel = 0;
        if (in_array(1, $recGoodsModelList) || in_array(2, $recGoodsModelList)) {
            $isModel = 1;
        }

        $product_id = BaseRepository::getKeyPluck($cart_goods, 'product_id');
        $productsList = GoodsDataHandleService::getProductsDataList($product_id, '*', 'product_id');

        if ($isModel == 1) {
            $productsWarehouseList = GoodsDataHandleService::getProductsWarehouseDataList($product_id, 0, '*', 'product_id');
            $productsAreaList = GoodsDataHandleService::getProductsAreaDataList($product_id, 0, '*', 'product_id');
        } else {
            $productsWarehouseList = [];
            $productsAreaList = [];
        }

        foreach ($cart_goods as $key => $value) {

            $cart_goods[$key]['get_goods'] = $goodsList[$value['goods_id']] ?? [];

            $subtotal = $value['goods_price'] * $value['goods_number'];

            $goodsConsumption = $goodsConsumptionList[$value['goods_id']] ?? [];
            if ($goodsConsumption) {
                $amount = $this->dscRepository->getGoodsConsumptionPrice($goodsConsumption, $subtotal);
            } else {
                $amount = $subtotal;
            }

            if ($value['model_attr'] == 1) {
                $sku_weight = $productsWarehouseList[$value['product_id']]['sku_weight'] ?? 0; //货品重量
            } elseif ($value['model_attr'] == 2) {
                $sku_weight = $productsAreaList[$value['product_id']]['sku_weight'] ?? 0; //货品重量
            } else {
                $sku_weight = $productsList[$value['product_id']]['sku_weight'] ?? 0; //货品重量
            }

            $cart_goods[$key]['sku_weight'] = $sku_weight;
            $cart_goods[$key]['subtotal'] = $subtotal;
            $cart_goods[$key]['dis_amount'] = $subtotal - $amount;

            $cart_goods[$key]['tid'] = $cart_goods[$key]['get_goods']['tid'] ?? $value['tid'];
        }

        return $cart_goods;
    }

    /**
     * 过滤初始化购物车商品ID
     *
     * @param $total
     * @return array
     */
    private function getRecList($total)
    {
        $rec_list = $total['rec_list'] ?? [];
        $rec_list = BaseRepository::getExplode($rec_list);
        $rec_list = DscEncryptRepository::filterValInt($rec_list);

        return $rec_list;
    }
}
