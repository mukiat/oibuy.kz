<?php

use App\Models\AccountLog;
use App\Models\BaitiaoLog;
use App\Models\BonusType;
use App\Models\Brand;
use App\Models\Card;
use App\Models\Cart;
use App\Models\CartCombo;
use App\Models\Category;
use App\Models\CouponsUser;
use App\Models\DeliveryOrder;
use App\Models\FavourableActivity;
use App\Models\Goods;
use App\Models\GoodsActivity;
use App\Models\GoodsAttr;
use App\Models\GoodsTransport;
use App\Models\GoodsTransportExpress;
use App\Models\GoodsTransportExtend;
use App\Models\GroupGoods;
use App\Models\IntelligentWeight;
use App\Models\MerchantsAccountLog;
use App\Models\MerchantsGrade;
use App\Models\OfflineStore;
use App\Models\OrderAction;
use App\Models\OrderCloud;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\OrderInvoice;
use App\Models\OrderReturn;
use App\Models\Pack;
use App\Models\PackageGoods;
use App\Models\PayLog;
use App\Models\Payment;
use App\Models\PresaleActivity;
use App\Models\Products;
use App\Models\ProductsArea;
use App\Models\ProductsWarehouse;
use App\Models\Region;
use App\Models\RegionWarehouse;
use App\Models\ReturnAction;
use App\Models\ReturnCause;
use App\Models\ReturnGoods;
use App\Models\SellerBillOrder;
use App\Models\SellerShopinfo;
use App\Models\Shipping;
use App\Models\ShopConfig;
use App\Models\StoreGoods;
use App\Models\UserAccount;
use App\Models\UserAddress;
use App\Models\UserBonus;
use App\Models\UserOrderNum;
use App\Models\Users;
use App\Models\UsersVatInvoicesInfo;
use App\Models\ValueCard;
use App\Models\ValueCardRecord;
use App\Models\WarehouseAreaGoods;
use App\Models\WarehouseGoods;
use App\Plugins\CloudApi\Cloud;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CalculateRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\StrRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Flow\FlowRepository;
use App\Services\Activity\CouponsService;
use App\Services\Activity\PresaleService;
use App\Services\Activity\ValueCardService;
use App\Services\Cart\CartCommonService;
use App\Services\Cart\CartDataHandleService;
use App\Services\Cart\CartService;
use App\Services\Category\CategoryService;
use App\Services\Coupon\CouponsUserService;
use App\Services\Erp\JigonManageService;
use App\Services\Flow\FlowActivityService;
use App\Services\Flow\FlowOrderService;
use App\Services\Goods\GoodsAttrService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Goods\GoodsService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\OfflineStore\OfflineStoreDataHandleService;
use App\Services\Order\OrderCommonService;
use App\Services\Order\OrderGoodsService;
use App\Services\Order\OrderRefoundService;
use App\Services\Order\OrderService;
use App\Services\Order\OrderTransportService;
use App\Services\Package\PackageGoodsService;
use App\Services\Payment\PaymentService;
use App\Services\Region\RegionDataHandleService;
use App\Services\Shipping\ShippingService;
use App\Services\Store\StoreDataHandleService;
use App\Services\Store\StoreService;
use App\Services\User\UserCommonService;
use App\Services\User\UserRankService;
use Illuminate\Support\Facades\DB;
use App\Services\Merchant\MerchantDataHandleService;

/**
 * 取得已安装的配送方式
 *
 * @param bool $is_cac true 显示上门自提，false 不显示上门自提
 * @return array
 */
function shipping_list($is_cac = false)
{
    $res = Shipping::where('enabled', 1);

    if ($is_cac == false) {
        //过滤商家“上门取货”
        $adminru = get_admin_ru_id();
        if ($adminru['ru_id'] > 0) {
            $res = $res->where('shipping_code', '<>', 'cac');
        }
    }

    $res = BaseRepository::getToArrayGet($res);

    $arr = [];
    if ($res) {
        foreach ($res as $key => $row) {
            if (substr($row['shipping_code'], 0, 5) == 'ship_') {
                unset($arr[$key]);
                continue;
            } else {
                $arr[$key]['shipping_id'] = $row['shipping_id'];
                $arr[$key]['shipping_name'] = $row['shipping_name'];
                $arr[$key]['shipping_code'] = $row['shipping_code'];
            }
        }
    }

    return $arr;
}

/**
 * 取得可用的配送区域的父级地区
 * @param array $region_id
 * @return  array   配送方式数组
 */
function get_parent_region($region_id)
{
    $res = Region::where('region_id', $region_id);
    $res = BaseRepository::getToArrayFirst($res);

    return $res;
}

/**
 * 获取指定配送的保价费用
 *
 * @access  public
 * @param string $shipping_code 配送方式的code
 * @param float $goods_amount 保价金额
 * @param mix $insure 保价比例
 * @return  float
 */
function shipping_insure_fee($shipping_code, $goods_amount, $insure)
{
    if (strpos($insure, '%') === false) {
        /* 如果保价费用不是百分比则直接返回该数值 */
        return floatval($insure);
    } else {
        $shipping_code = StrRepository::studly($shipping_code);
        $shipping_code = '\\App\\Plugins\\Shipping\\' . $shipping_code . '\\' . $shipping_code;

        if (class_exists($shipping_code)) {
            $shipping = app($shipping_code);
            $insure = floatval($insure) / 100;

            if (method_exists($shipping, 'calculate_insure')) {
                return $shipping->calculate_insure($goods_amount, $insure);
            } else {
                return ceil($goods_amount * $insure);
            }
        } else {
            return false;
        }
    }
}

/**
 * 取得已安装的支付方式列表
 * @return  array   已安装的配送方式列表
 */
function payment_list()
{
    $res = Payment::where('enabled', 1);
    $res = BaseRepository::getToArrayGet($res);

    return $res;
}

/**
 * 取得支付方式信息
 * @param string $field
 * @param int $type
 * @param array $columns
 * @return mixed
 */
function payment_info($field = '', $type = 0, $columns = [])
{
    $row = Payment::where('enabled', 1);

    if ($type == 1) {
        $row = $row->where('pay_code', $field);
    } else {
        $row = $row->where('pay_id', $field);
    }

    if (!empty($columns)) {
        $row = $row->select($columns);
    }

    return BaseRepository::getToArrayFirst($row);
}

/**
 * 获得订单需要支付的支付费用
 *
 * @param int $payment_id
 * @param int $order_amount
 * @param int $cod_fee
 * @return float
 */
function pay_fee($payment_id, $order_amount = 0, $cod_fee = null)
{
    return app(PaymentService::class)->order_pay_fee($payment_id, $order_amount, $cod_fee);
}

/**
 * 取得可用的支付方式列表
 * @param int $support_cod 配送方式是否支持货到付款
 * @param int $cod_fee 货到付款手续费（当配送方式支持货到付款时才传此参数）
 * @param int $is_online 是否支持在线支付
 * @return  array   配送方式数组
 */
function available_payment_list($support_cod = 0, $cod_fee = null, $is_online = 0)
{
    $res = Payment::where('enabled', 1);

    if (!$support_cod) {
        $res = $res->where('is_cod', 0);
    }

    if ($is_online) {
        if ($is_online == 2) {
            $res = $res->where(function ($query) {
                $query->where('is_online', 1)->orWhere('pay_code', 'balance');
            });
        } else {
            $res = $res->where('is_online', 1);
        }
    }

    $res = $res->orderBy('pay_order', 'ASC')->orderBy('pay_id', 'DESC');
    $payment_list = BaseRepository::getToArrayGet($res);

    if (empty($payment_list)) {
        return [];
    }

    foreach ($payment_list as $key => $payment) {
        $payment_list[$key]['format_pay_fee'] = pay_fee_list($payment, $cod_fee);

        //pc端去除ecjia的支付方式
        if (substr($payment['pay_code'], 0, 4) == 'pay_') {
            unset($payment_list[$key]);
            continue;
        }

        $pay_code = StrRepository::studly($payment['pay_code']);

        $plugins = plugin_path('Payment/' . $pay_code . '/' . $pay_code . '.php');
        if (!file_exists($plugins)) {
            unset($payment_list[$key]);
        }

        // 不显示pay_config
//        unset($payment_list[$key]['pay_config']);
    }

    $payment_list = empty($payment_list) ? [] : collect($payment_list)->values()->all();

    return $payment_list;
}

/**
 * 支付列表手续费
 * @param array $payment
 * @param null $cod_fee
 * @return float|int
 */
function pay_fee_list($payment = [], $cod_fee = null)
{
    return app(PaymentService::class)->pay_fee_list($payment, $cod_fee);
}

/**
 * 取得包装列表
 * @return  array   包装列表
 */
function pack_list()
{
    $res = Pack::whereRaw(1);
    $res = BaseRepository::getToArrayGet($res);

    $list = [];
    if ($res) {
        foreach ($res as $row) {
            $row['format_pack_fee'] = app(DscRepository::class)->getPriceFormat($row['pack_fee'], false);
            $row['format_free_money'] = app(DscRepository::class)->getPriceFormat($row['free_money'], false);
            $list[] = $row;
        }
    }

    return $list;
}

/**
 * 取得包装信息
 * @param int $pack_id 包装id
 * @return  array   包装信息
 */
function pack_info($pack_id)
{
    $res = Pack::where('pack_id', $pack_id);
    $res = BaseRepository::getToArrayFirst($res);

    return $res;
}

/**
 * 根据订单中的商品总额来获得包装的费用
 *
 * @access  public
 * @param integer $pack_id
 * @param float $goods_amount
 * @return  float
 */
function pack_fee($pack_id, $goods_amount)
{
    $pack = pack_info($pack_id);

    $val = (floatval($pack['free_money']) <= $goods_amount && $pack['free_money'] > 0) ? 0 : floatval($pack['pack_fee']);

    return $val;
}

/**
 * 取得贺卡列表
 * @return  array   贺卡列表
 */
function card_list()
{
    $res = Card::whereRaw(1);
    $res = BaseRepository::getToArrayGet($res);

    $list = [];
    if ($res) {
        foreach ($res as $row) {
            $row['format_card_fee'] = app(DscRepository::class)->getPriceFormat($row['card_fee'], false);
            $row['format_free_money'] = app(DscRepository::class)->getPriceFormat($row['free_money'], false);
            $list[] = $row;
        }
    }

    return $list;
}

/**
 * 取得贺卡信息
 * @param int $card_id 贺卡id
 * @return  array   贺卡信息
 */
function card_info($card_id)
{
    $res = Card::where('card_id', $card_id);
    $res = BaseRepository::getToArrayFirst($res);

    return $res;
}

/**
 * 根据订单中商品总额获得需要支付的贺卡费用
 *
 * @access  public
 * @param integer $card_id
 * @param float $goods_amount
 * @return  float
 */
function card_fee($card_id, $goods_amount)
{
    $card = card_info($card_id);

    return ($card['free_money'] <= $goods_amount && $card['free_money'] > 0) ? 0 : $card['card_fee'];
}

/**
 * 取得订单信息
 * @param int $order_id 订单id（如果order_id > 0 就按id查，否则按sn查）
 * @param string $order_sn 订单号
 * @return  array   订单信息（金额都有相应格式化的字段，前缀是formated_）
 */
function order_info($order_id, $order_sn = '', $seller_id = -1)
{
    $ValueCardLib = app(ValueCardService::class);
    $PaymentLib = app(PaymentService::class);

    /* 计算订单各种费用之和的语句 */
    if (CROSS_BORDER === true) { // 跨境多商户
        $total_fee = " (goods_amount - discount + tax + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee + rate_fee) AS total_fee ";
    } else {
        $total_fee = " (goods_amount - discount + tax + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee) AS total_fee ";
    }

    $order_id = intval($order_id);

    if ($order_id > 0) {
        //@模板堂-bylu 这里连表查下支付方法表,获取到"pay_code"字段值;
        $order = OrderInfo::selectRaw("*, $total_fee")->where('order_id', $order_id);
    } else {
        //@模板堂-bylu 这里连表查下支付方法表,获取到"pay_code"字段值;
        $order = OrderInfo::selectRaw("*, $total_fee")->where('order_sn', $order_sn);
    }

    if ($seller_id > -1) {
        $order = $order->where('ru_id', $seller_id);
    }

    $order = $order->with([
        'getOrderGoods',
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
        },
        'getSellerNegativeOrder'
    ]);

    $order = BaseRepository::getToArrayFirst($order);

    if (empty($order)) {
        return [];
    }

    if ($order) {
        if ($order['cost_amount'] <= 0) {
            $order['cost_amount'] = goods_cost_price($order['order_id']);
        }
        /*获取发票ID start*/
        $user_id = $order['user_id'];
        $order['invoice_id'] = OrderInvoice::whereHasIn('getOrder', function ($query) use ($user_id) {
            $query->where('user_id', $user_id);
        })->value('invoice_id');
        /*获取发票ID end*/

        $where = [
            'order_id' => $order_id
        ];

        $value_card = $ValueCardLib->getValueCardInfo($where);

        $order['use_val'] = isset($value_card['use_val']) ? $value_card['use_val'] : 0;
        $order['vc_dis'] = isset($value_card['vc_dis']) ? $value_card['vc_dis'] : '';

        $payWhere = [
            'pay_id' => $order['pay_id'],
            'enabled' => 1
        ];
        $payment = $PaymentLib->getPaymentInfo($payWhere);

        $order['vc_dis_money'] = app(DscRepository::class)->getPriceFormat($order['vc_dis_money'], true, false);
        $order['pay_code'] = isset($payment['pay_code']) ? $payment['pay_code'] : '';
        $order['child_order'] = get_seller_order_child($order['order_id'], $order['main_order_id']);

        $order['formated_goods_amount'] = app(DscRepository::class)->getPriceFormat($order['goods_amount'], false);
        $order['formated_cost_amount'] = $order['cost_amount'] > 0 ? app(DscRepository::class)->getPriceFormat($order['cost_amount'], false) : 0;
        $order['formated_profit_amount'] = app(DscRepository::class)->getPriceFormat($order['total_fee'] - $order['cost_amount'] - $order['shipping_fee'], false);
        $order['formated_discount'] = app(DscRepository::class)->getPriceFormat($order['discount'], false);
        $order['formated_tax'] = app(DscRepository::class)->getPriceFormat($order['tax'], false);
        $order['formated_shipping_fee'] = app(DscRepository::class)->getPriceFormat($order['shipping_fee'], false);
        $order['formated_insure_fee'] = app(DscRepository::class)->getPriceFormat($order['insure_fee'], false);
        $order['formated_pay_fee'] = app(DscRepository::class)->getPriceFormat($order['pay_fee'], false);
        $order['formated_pack_fee'] = app(DscRepository::class)->getPriceFormat($order['pack_fee'], false);
        $order['formated_card_fee'] = app(DscRepository::class)->getPriceFormat($order['card_fee'], false);
        $order['formated_total_fee'] = app(DscRepository::class)->getPriceFormat($order['total_fee'], false);
        $order['formated_money_paid'] = app(DscRepository::class)->getPriceFormat($order['money_paid'], false);
        $order['formated_bonus'] = app(DscRepository::class)->getPriceFormat($order['bonus'], false);
        $order['formated_coupons'] = app(DscRepository::class)->getPriceFormat($order['coupons'], false);
        $order['formated_integral_money'] = app(DscRepository::class)->getPriceFormat($order['integral_money'], false);
        $order['formated_value_card'] = app(DscRepository::class)->getPriceFormat($order['use_val'], false);
        $order['formated_vc_dis'] = (float)$order['vc_dis'] * 10;
        $order['formated_surplus'] = app(DscRepository::class)->getPriceFormat($order['surplus'], false);
        $order['formated_order_amount'] = app(DscRepository::class)->getPriceFormat(abs($order['order_amount']), false);
        $order['formated_vc_dis_money'] = app(DscRepository::class)->getPriceFormat(abs($order['vc_dis_money']), false);

        $total_fee = $order['total_fee'] - $order['vc_dis_money'];
        $order['formated_manage_total_fee'] = app(DscRepository::class)->getPriceFormat($total_fee, false);

        // 已付金额
        $order['realpay_amount'] = $order['money_paid'] + $order['surplus'];
        $order['formated_realpay_amount'] = app(DscRepository::class)->getPriceFormat($order['realpay_amount']);
        $order['formated_add_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $order['add_time']);
        $order['pay_points'] = $order['integral']; //by kong  获取积分

        /* 取得区域名 */
        $province = $order['get_region_province']['region_name'] ?? '';
        $city = $order['get_region_city']['region_name'] ?? '';
        $district = $order['get_region_district']['region_name'] ?? '';
        $street = $order['get_region_street']['region_name'] ?? '';
        $order['region'] = $province . ' ' . $city . ' ' . $district . ' ' . $street;

        if (CROSS_BORDER === true) { // 跨境多商户
            $order['formated_rate_fee'] = app(DscRepository::class)->getPriceFormat($order['rate_fee'], false);
        }

        // 推荐人
        if ($order['parent_id'] > 0 && $order['parent_id'] != $order['user_id']) {
            $user_name = Users::where('user_id', $order['parent_id'])->value('user_name');
            $reg_time = Users::where('user_id', $order['user_id'])->value('reg_time');
            $user = [];
            if (!empty($user_name) && !empty($reg_time)) {
                $user['user_name'] = $user_name;
                $user['reg_time_format'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $reg_time);
            }
            $order['parent'] = $user;
        }

        if (empty($order['confirm_take_time']) && $order['order_status'] == OS_CONFIRMED && $order['shipping_status'] == SS_RECEIVED && $order['shipping_status'] == PS_PAYED) {
            $log_time = OrderAction::where('order_status', OS_CONFIRMED)
                ->where('shipping_status', SS_RECEIVED)
                ->where('pay_status', PS_PAYED)
                ->where('order_id', $order['order_id'])
                ->value('log_time');

            $other['confirm_take_time'] = $log_time;

            OrderInfo::where('order_id', $order['order_id'])->update($other);

            $order['confirm_take_time'] = $log_time;
        }

        if (isset($GLOBALS['_CFG']['show_mobile']) && $GLOBALS['_CFG']['show_mobile'] == 0) {
            $order['mobile'] = app(DscRepository::class)->stringToStar($order['mobile']);
            $order['tel'] = app(DscRepository::class)->stringToStar($order['tel']);
        }

        if ($order['team_id'] > 0) {
            $order['warehouse_id'] = $order['get_order_goods']['warehouse_id'] ?? 0;
            $order['area_id'] = $order['get_order_goods']['area_id'] ?? 0;
            $order['area_city'] = $order['get_order_goods']['$area_city'] ?? 0;
        }

        if (isset($order['pay_code']) && $order['pay_code'] == 'bank') {
            // 上传支付凭证
            $bank_transfer = DB::table('order_info_bank_transfer')->where('order_id', $order['order_id'])->first();
            $order['pay_document'] = !empty($bank_transfer->pay_document) ? app(DscRepository::class)->getImagePath($bank_transfer->pay_document) : '';
        }
    }
    return $order;
}

/**
 * 判断订单是否已完成
 * @param array $order 订单信息
 * @return  bool
 */
function order_finished($order)
{
    return $order['order_status'] == OS_CONFIRMED &&
        ($order['shipping_status'] == SS_SHIPPED || $order['shipping_status'] == SS_RECEIVED) &&
        ($order['pay_status'] == PS_PAYED || $order['pay_status'] == PS_PAYING);
}

/*
 * 获取主订单的订单数量
 */
function get_seller_order_child($order_id, $main_order_id)
{
    $count = 0;
    if ($main_order_id == 0) {
        $count = OrderInfo::where('main_order_id', $order_id)->count();
    }
    return $count;
}

/**
 * 取得订单商品
 *
 * @param int $order_id
 * @return array
 * @throws Exception
 */
function order_goods($order_id = 0)
{
    if (empty($order_id)) {
        return [];
    }

    $res = OrderGoods::selectRaw("*, (goods_price * goods_number) AS subtotal")
        ->where('order_id', $order_id);

    $res = $res->with([
        'getGoods' => function ($query) {
            $query = $query->select('goods_id', 'shop_price', 'is_shipping', 'goods_weight AS goodsweight', 'goods_img', 'give_integral', 'goods_thumb', 'goods_cause');
            if (CROSS_BORDER === true) { // 跨境多商户
                $query = $query->addSelect('free_rate');
            }

            $query->with([
                'getGoodsConsumption'
            ]);
        },
        'getOrder' => function ($query) {
            $query->select('order_id', 'extension_code AS order_extension_code', 'extension_id', 'order_status', 'pay_status', 'shipping_status', 'is_delete');
        }
    ]);

    $res = BaseRepository::getToArrayGet($res);

    $goods_list = [];
    if ($res) {

        $ru_id = BaseRepository::getKeyPluck($res, 'ru_id');
        $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

        $orderGoodsAttrIdList = BaseRepository::getKeyPluck($res, 'goods_attr_id');
        $orderGoodsAttrIdList = BaseRepository::getArrayUnique($orderGoodsAttrIdList);
        $orderGoodsAttrIdList = ArrRepository::getArrayUnset($orderGoodsAttrIdList);
        $productsGoodsAttrList = [];
        if ($orderGoodsAttrIdList) {
            $orderGoodsAttrIdList = BaseRepository::getImplode($orderGoodsAttrIdList);
            $productsGoodsAttrList = GoodsDataHandleService::getGoodsAttrDataList($orderGoodsAttrIdList, ['goods_attr_id', 'attr_img_flie', 'attr_gallery_flie']);
        }

        foreach ($res as $row) {

            $shop_information = $merchantList[$row['ru_id']] ?? [];
            $row['country_icon'] = $shop_information['country_icon'] ?? '';

            $goods = $row['get_goods'] ?? [];

            $row = BaseRepository::getArrayMerge($row, $row['get_goods']);
            $row = BaseRepository::getArrayMerge($row, $row['get_order']);

            if ($row['extension_code'] == 'package_buy') {
                $row['package_goods_list'] = app(PackageGoodsService::class)->getPackageGoods($row['goods_id']);
            }

            $row['give_integral'] = isset($row['give_integral']) ? $row['give_integral'] : 0;
            if ($row['give_integral'] == '-1') {
                $order = array();
                $order['extension_code'] = $row['extension_code'];
                $order['extension_id'] = $row['extension_id'];
                $order['order_id'] = $row['order_id'];
                $integral = integral_to_give($order, $row['rec_id']);
                $row['give_integral'] = intval($integral['custom_points']);
            }
            $row['warehouse_name'] = RegionWarehouse::where('region_id', $row['warehouse_id'])->value('region_name');

            //ecmoban模板堂 --zhuo start 商品金额促销
            $row['goods_amount'] = $row['goods_price'] * $row['goods_number'];
            if (isset($goods['get_goods_consumption']) && $goods['get_goods_consumption']) {
                $row['amount'] = app(DscRepository::class)->getGoodsConsumptionPrice($goods['get_goods_consumption'], $row['goods_amount']);
            } else {
                $row['amount'] = $row['goods_amount'];
            }

            $row['dis_amount'] = $row['goods_amount'] - $row['amount'];
            $row['discount_amount'] = app(DscRepository::class)->getPriceFormat($row['dis_amount'], false);
            //ecmoban模板堂 --zhuo end 商品金额促销

            if ($row['order_extension_code'] == "presale" && !empty($row['extension_id'])) {
                $row['url'] = app(DscRepository::class)->buildUri('presale', ['act' => 'view', 'presaleid' => $row['extension_id']], $row['goods_name']);
            } elseif ($row['order_extension_code'] == "group_buy") {
                $row['url'] = app(DscRepository::class)->buildUri('group_buy', ['gbid' => $row['extension_id']]);
            } elseif ($row['order_extension_code'] == "snatch") {
                $row['url'] = app(DscRepository::class)->buildUri('snatch', ['sid' => $row['extension_id']]);
            } elseif (substr($row['extension_code'], 0, 7) == "seckill") {
                $row['url'] = app(DscRepository::class)->buildUri('seckill', ['act' => "view", 'secid' => $row['extension_id']]);
            } elseif ($row['order_extension_code'] == "auction") {
                $row['url'] = app(DscRepository::class)->buildUri('auction', ['auid' => $row['extension_id']]);
            } elseif ($row['order_extension_code'] == "exchange_goods") {
                $row['url'] = app(DscRepository::class)->buildUri('exchange_goods', ['gid' => $row['extension_id']]);
            } else {
                $row['url'] = app(DscRepository::class)->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
            }

            $row['shop_name'] = app(MerchantCommonService::class)->getShopName($row['ru_id'], 1); //店铺名称
            $row['shopUrl'] = app(DscRepository::class)->buildUri('merchants_store', ['urid' => $row['ru_id']]);
            $row['goods_img'] = app(DscRepository::class)->getImagePath($row['goods_img']);

            //图片显示
            $row['goods_thumb'] = app(DscRepository::class)->getImagePath($row['goods_thumb']);

            $goods_attr_id = $row['goods_attr_id'] ?? '';
            $goods_attr_id = BaseRepository::getExplode($goods_attr_id);
            $row['goods_thumb'] = app(GoodsAttrService::class)->cartGoodsAttrImage($goods_attr_id, $productsGoodsAttrList, $row['goods_thumb']);

            //是否申请退货或者退款
            $row['is_return'] = ReturnGoods::where('rec_id', $row['rec_id'])->count();

            $goods_list[] = $row;
        }
    }
    return $goods_list;
}

/**
 * 取得订单总金额
 * @param int $order_id 订单id
 * @param bool $include_gift 是否包括赠品
 * @return  float   订单总金额
 */
function order_amount($order_id, $include_gift = true)
{
    $res = OrderGoods::selectRaw("SUM(goods_price * goods_number) as total")->where('order_id', $order_id);

    if (!$include_gift) {
        $res = $res->where('is_gift', 0);
    }

    $res = BaseRepository::getToArrayFirst($res);

    $total = $res ? $res['total'] : 0;

    return floatval($total);
}

/**
 * 取得某订单商品总重量和总金额（对应 cart_weight_price）
 * @param int $order_id 订单id
 * @return  array   ('weight' => **, 'amount' => **, 'formated_weight' => **)
 */
function order_weight_price($order_id)
{
    $row = OrderGoods::where('order_id', $order_id)
        ->with([
            'getGoods' => function ($query) {
                $query->select('goods_id', 'goods_weight');
            }
        ]);

    $row = BaseRepository::getToArrayGet($row);

    $weight = 0;
    $amount = 0;
    $number = 0;
    if ($row) {
        foreach ($row as $key => $val) {
            $val = BaseRepository::getArrayMerge($val, $val['get_goods']);
            $val['goods_weight'] = isset($val['goods_weight']) ? $val['goods_weight'] : 0;
            $weight += $val['goods_weight'] * $val['goods_number'];
            $amount += $val['goods_price'] * $val['goods_number'];
            $number += $val['goods_number'];
        }
    }

    $arr['weight'] = floatval($weight);
    $arr['amount'] = floatval($amount);
    $arr['number'] = intval($number);

    /* 格式化重量 */
    $arr['formated_weight'] = formated_weight($arr['weight']);

    return $arr;
}

/**
 * 获得订单中的费用信息
 *
 * @param array $order
 * @param array $goods 购物车商品
 * @param string $consignee
 * @param string $cart_value
 * @param string $cart_goods_list
 * @param int $store_id
 * @param string $store_type
 * @param int $user_id
 * @param int $rank_id
 * @param int $rec_type
 * @return array
 */
function order_fee($order = [], $goods = [], $consignee = '', $cart_value = '', $cart_goods_list = '', $store_id = 0, $store_type = '', $user_id = 0, $rank_id = 0, $rec_type = CART_GENERAL_GOODS)
{
    if (empty($user_id)) {
        $user_id = session('user_id', 0);
    }

    /* 初始化订单的扩展code */
    if (!isset($order['extension_code'])) {
        $order['extension_code'] = '';
    }

    if ($order['extension_code'] == 'group_buy') {
        $group_buy_id = intval($order['extension_id']);
        /* 取得团购活动信息 */
        $group_buy = GoodsActivity::where('act_type', GAT_GROUP_BUY)
            ->where('act_id', $group_buy_id);
        $group_buy = BaseRepository::getToArrayFirst($group_buy);

        if ($group_buy) {
            $ext_info = unserialize($group_buy['ext_info']);
            $group_buy = array_merge($group_buy, $ext_info);
        }
    }

    if ($order['extension_code'] == 'presale') {
        $presale = app(PresaleService::class)->presaleInfo($order['extension_id']);
    }

    $total = [
        'real_goods_count' => 0,
        'seller_goods_count' => 0, // 购物车商家商品数量
        'gift_amount' => 0,
        'goods_price' => 0,
        'cost_price' => 0,
        'market_price' => 0,
        'discount' => 0,
        'pack_fee' => 0,
        'card_fee' => 0,
        'shipping_fee' => 0,
        'shipping_insure' => 0,
        'integral_money' => 0,
        'bonus' => 0,
        'value_card' => 0, //储值卡
        'coupons' => 0, //优惠券 bylu
        'surplus' => 0,
        'cod_fee' => 0,
        'pay_fee' => 0,
        'tax' => 0,
        'presale_price' => 0,
        'dis_amount' => 0,
        'goods_price_formated' => 0,
        'seller_amount' => [],
        'bonus_goods_price' => 0
    ];

    /* 商品总价 */

    $arr = [];
    foreach ($goods as $key => $val) {
        /* 统计实体商品的个数 */
        if ($val['is_real']) {
            $total['real_goods_count']++;
        }

        // 购物车商家商品数量
        if ($val['ru_id'] > 0) {
            $total['seller_goods_count']++;
        }

        //ecmoban模板堂 --zhuo start 商品金额促销
        $arr[$key]['goods_amount'] = $val['goods_price'] * $val['goods_number'];
        $total['goods_price_formated'] += $arr[$key]['goods_amount'];

        $goods_con = app(CartCommonService::class)->getConGoodsAmount($arr[$key]['goods_amount'], $val['goods_id'], 0, 0, $val['parent_id']);

        $goods_con['amount'] = explode(',', $goods_con['amount']);
        $arr[$key]['amount'] = min($goods_con['amount']);

        $total['goods_price'] += $arr[$key]['amount'];
        // 购物车商品成本价
        $total['cost_price'] += $val['cost_price'] * $val['goods_number'];

        if (!isset($total['seller_amount'][$val['ru_id']])) {
            $total['seller_amount'][$val['ru_id']] = 0;
        }

        //过滤虚拟商品
        if (($val['get_goods']['is_real'] ?? 0) == 1 && ($val['get_goods']['extension_code'] ?? '') != 'virtual_card') {
            /* 扣除参与活动金额【优惠券、折扣、储值卡折扣】 */
            $total['bonus_goods_price'] += $arr[$key]['amount'] - ($val['goods_coupons'] + $val['goods_favourable'] + $val['value_card_discount']); //红包可用金额
            @$total['seller_amount'][$val['ru_id']] += $arr[$key]['amount'];
        }

        //ecmoban模板堂 --zhuo end 商品金额促销

        if (isset($val['get_presale_activity']['deposit']) && $val['get_presale_activity']['deposit'] >= 0 && $val['rec_type'] == CART_PRESALE_GOODS) {
            $total['presale_price'] += $val['get_presale_activity']['deposit'] * $val['goods_number'];//预售定金
        }
        $total['market_price'] += $val['market_price'] * $val['goods_number'];
        $total['dis_amount'] += $val['dis_amount'];
    }

    $total['saving'] = $total['market_price'] - $total['goods_price'];
    $total['save_rate'] = $total['market_price'] ? round($total['saving'] * 100 / $total['market_price']) . '%' : 0;

    $total['goods_price_formated'] = app(DscRepository::class)->getPriceFormat($total['goods_price_formated'], false);
    $total['market_price_formated'] = app(DscRepository::class)->getPriceFormat($total['market_price'], false);
    $total['saving_formated'] = app(DscRepository::class)->getPriceFormat($total['saving'], false);
    $total['dis_amount_formated'] = app(DscRepository::class)->getPriceFormat($total['dis_amount'], false);

    /* 配送费用 */
    $total['no_shipping_cart_list'] = []; //不支持配送购物车商品
    $total['ru_shipping_fee_list'] = [];

    $shipping_cod_fee = null;
    if ($store_id > 0 || $total['real_goods_count'] == 0 || $store_type) {
        $total['shipping_fee'] = 0;
    } else {

        $ru_id = BaseRepository::getKeyPluck($cart_goods_list, 'ru_id');

        /* 商家选中的配送方式 处理提交订单时获取商家是否支持配送方式 */
        $tmp_shipping_id = BaseRepository::getColumn($cart_goods_list, 'tmp_shipping_id', 'ru_id');

        $order['uc_id'] = $order['uc_id'] ?? 0; //免邮券ID
        $cart_goods = FlowRepository::getNewGroupCartGoods($cart_goods_list);

        $shippingList = app(ShippingService::class)->goodsShippingTransport($cart_goods, $consignee, $order['uc_id'], $tmp_shipping_id);
        $ruShippingInfo = app(ShippingService::class)->orderFeeShipping($cart_goods, $ru_id, $shippingList, $tmp_shipping_id);

        $ruNotShippingCartGoodsList = app(ShippingService::class)->orderNotShippingCartGoodsList($shippingList);
        $ruNotShippingCartGoodsList = ArrRepository::getArrayUnset($ruNotShippingCartGoodsList);

        $total['ru_shipping_fee_list'] = $ruShippingInfo['ru_shipping_fee_list'] ?? [];
        $total['shipping_fee'] = $ruShippingInfo['shipping_fee'] ?? 0;
        $total['no_shipping_cart_list'] = $ruNotShippingCartGoodsList;
    }

    $total['shipping_fee_formated'] = app(DscRepository::class)->getPriceFormat($total['shipping_fee'], false);
    $total['shipping_insure_formated'] = app(DscRepository::class)->getPriceFormat($total['shipping_insure'], false);

    /* 折扣 */
    if ($order['extension_code'] != 'group_buy') {
        //$discount = compute_discount(3, $cart_value, 0, 0, $user_id, $rank_id, $rec_type);
        //$total['discount'] = $discount['discount'];

        $discount = BaseRepository::getArraySum($goods, 'goods_favourable');
        $total['discount'] = $discount ? $discount : 0;
        if ($total['discount'] > $total['goods_price']) {
            $total['discount'] = $total['goods_price'];
        }
    }

    $bonus_amount = $total['discount'];
    $total['discount_formated'] = app(DscRepository::class)->getPriceFormat($total['discount'], false);

    /* 包装费用 */
    if (!empty($order['pack_id'])) {
        $total['pack_fee'] = pack_fee($order['pack_id'], $total['goods_price']);
    }
    $total['pack_fee_formated'] = app(DscRepository::class)->getPriceFormat($total['pack_fee'], false);

    /* 贺卡费用 */
    if (!empty($order['card_id'])) {
        $total['card_fee'] = card_fee($order['card_id'], $total['goods_price']);
    }
    $total['card_fee_formated'] = app(DscRepository::class)->getPriceFormat($total['card_fee'], false);

    /* 红包 */
    if (!empty($order['bonus_id'])) {
        $bonus = bonus_info($order['bonus_id']);
        $total['bonus'] = $bonus['type_money'];
        $total['admin_id'] = $bonus['admin_id']; //ecmoban模板堂 --zhuo
    }

    $total['bonus_formated'] = app(DscRepository::class)->getPriceFormat($total['bonus'], false);

    /* 线下红包 */
    if (!empty($order['bonus_kill'])) {
        $total['bonus_kill'] = $order['bonus_kill'];
        $total['bonus_kill_formated'] = app(DscRepository::class)->getPriceFormat($total['bonus_kill'], false);
    }

    if (isset($order['uc_id']) && !empty($order['uc_id'])) {
        $couponsList = app(CouponsUserService::class)->getCouponsUserSerList($order['uc_id'], $user_id, $goods);

        /* 优惠券 非免邮 */
        $total['coupons'] = BaseRepository::getArraySum($couponsList, 'uc_money');// 优惠券面值

        $total['coupons_formated'] = app(DscRepository::class)->getPriceFormat($total['coupons'], false);
        $total['uc_id'] = BaseRepository::getImplode($order['uc_id']);
    } else {
        $total['uc_id'] = '';
    }

    /* 储值卡 */
    $total['vc_dis'] = 1;
    $total['vc_rec_list'] = [];
    if (!empty($order['vc_id'])) {

        $value_card = app(ValueCardService::class)->getUserValueCard($user_id, $goods, $order['vc_id']);
        $value_card = BaseRepository::getArrayFirst($value_card);

        $vc_dis = $value_card['card_discount'] ? $value_card['card_discount'] : 1;
        $total['vc_dis'] = $vc_dis;

        $cardGoodsList = $value_card['goods_list'] ?? [];

        $cardGoodsAmount = BaseRepository::getArraySum($cardGoodsList, ['goods_price', 'goods_number']);
        $cardDisAmount = BaseRepository::getArraySum($cardGoodsList, 'dis_amount');

        //储值卡折扣金额 （商品金额 - 一切优惠金额）* 折扣率
        $goods_bonus = BaseRepository::getArraySum($cardGoodsList, 'goods_bonus');
        $goods_coupons = BaseRepository::getArraySum($cardGoodsList, 'goods_coupons');
        $goods_favourable = BaseRepository::getArraySum($cardGoodsList, 'goods_favourable');

        if ($total['vc_dis'] > 0) {
            $total['vc_dis_money'] = ($cardGoodsAmount - $goods_bonus - $goods_coupons - $goods_favourable - $cardDisAmount) * (1 - $total['vc_dis']);
            $total['vc_dis_money'] = app(DscRepository::class)->changeFloat($total['vc_dis_money']);
        } else {
            $total['vc_dis_money'] = 0;
        }

        $cardGoodsTotal = $cardGoodsAmount - ($goods_bonus + $goods_coupons + $goods_favourable + $total['vc_dis_money'] + $cardDisAmount);
        $cardGoodsTotal = $cardGoodsTotal > 0 ? $cardGoodsTotal : 0;

        $total['vc_rec_list'] = BaseRepository::getKeyPluck($cardGoodsList, 'rec_id');

        if ($cardGoodsTotal == 0) {
            $total['vc_dis'] = 1;
        }

        $valCardRuList = BaseRepository::getKeyPluck($cardGoodsList, 'ru_id');
        $valCardRuList = BaseRepository::getArrayFlip($valCardRuList);
        $valCardRuListShipping = $total['ru_shipping_fee_list'] ? BaseRepository::getArrayIntersectByKeys($total['ru_shipping_fee_list'], $valCardRuList) : [];

        if ($cardGoodsTotal > 0) {
            $cardGoodsTotal += BaseRepository::getArraySum($valCardRuListShipping); //加上运费金额
        }

        if ($value_card) {
            $total['value_card'] = min($value_card['card_money'], $cardGoodsTotal); //满足条件商品的储值卡应付金额
        } else {
            $total['value_card'] = 0;
        }

        $total['value_card'] = app(DscRepository::class)->changeFloat($total['value_card']);
    }

    // 扣除优惠活动金额，红包和积分最多能支付的金额为商品总额
    if ($total['goods_price'] > 0) {
        if ($total['goods_price'] >= $bonus_amount) {
            $max_amount = $total['goods_price'] - $bonus_amount;
        } else {
            $max_amount = 0;
        }
    } else {
        $max_amount = $total['goods_price'];
    }

    $use_value_card = 0;
    /* 计算订单总额 */
    if (isset($group_buy['deposit']) && $order['extension_code'] == 'group_buy' && $group_buy['deposit'] > 0) {
        $total['amount'] = $total['goods_price'] + $total['shipping_fee'];
    } elseif (isset($presale['deposit']) && $order['extension_code'] == 'presale' && $presale['deposit'] >= 0) {
        $total['amount'] = $total['presale_price'] + $total['shipping_fee'];
    } else {
        $total['amount'] = $total['goods_price'] - $total['discount'] + $total['pack_fee'] + $total['card_fee'] +
            $total['shipping_insure'] + $total['cod_fee'];

        // 减去红包金额  //红包支付，如果红包的金额大于订单金额 则去订单金额定义为红包金额的最终结果(相当于订单金额减去本身的金额，为0) ecmoban模板堂 --zhuo
        $use_bonus = min($total['bonus'], $max_amount); // 实际减去的红包金额
        $use_coupons = $total['coupons']; //优惠券抵扣金额

        //还需要支付的订单金额
        if (isset($total['bonus_kill'])) {
            if ($total['amount'] > $total['bonus_kill']) {
                $total['amount'] -= $price = number_format($total['bonus_kill'], 2, '.', '');
            } else {
                $total['amount'] = 0;
            }
        }

        $total['bonus'] = $use_bonus;
        $total['bonus_formated'] = app(DscRepository::class)->getPriceFormat($total['bonus'], false);

        $total['coupons'] = $use_coupons;
        $total['coupons_formated'] = app(DscRepository::class)->getPriceFormat($total['coupons'], false);

        //还需要支付的订单金额 start
        if ($use_bonus > $total['amount']) {
            $total['amount'] = 0;
        } else {
            $total['amount'] -= $use_bonus;
        }

        if ($use_coupons > $total['amount']) {
            $total['amount'] = 0;
        } else {
            $total['amount'] -= $use_coupons;
        }
        //还需要支付的订单金额 end

        $total['amount'] += $total['shipping_fee'];

        $max_amount -= $use_bonus + $use_coupons; // 积分最多还能支付的金额
    }

    /* 积分 */
    $order['integral'] = $order['integral'] > 0 ? $order['integral'] : 0;
    if ($total['amount'] > 0 && $max_amount > 0 && $order['integral'] > 0) {
        $integral_money = app(DscRepository::class)->valueOfIntegral($order['integral']);

        // 使用积分支付
        $use_integral = min($total['amount'], $max_amount, $integral_money); // 实际使用积分支付的金额
        $total['amount'] -= $use_integral;
        $total['integral_money'] = $use_integral;
        $order['integral'] = app(DscRepository::class)->integralOfValue($use_integral);
    } else {
        $total['integral_money'] = 0;
        $order['integral'] = 0;
    }

    $total['integral'] = $order['integral'];
    $total['integral_formated'] = app(DscRepository::class)->getPriceFormat($total['integral_money'], false);

    /* 均摊积分金额 */
    app(FlowOrderService::class)->orderEquallyGoodsIntegral($goods, $order, $total);

    if (!empty($order['vc_id']) && $total['value_card'] > 0) {

        $use_value_card = $total['value_card']; //储值卡使用金额
        $total['value_card_formated'] = app(DscRepository::class)->getPriceFormat($use_value_card, false); //实际使用的储值卡金额
        $total['use_value_card'] = $use_value_card;
        $total['card_dis'] = app(DscRepository::class)->getPriceFormat($total['vc_dis_money'], false);
        $total['amount'] -= $total['vc_dis_money'];
        $total['vc_id'] = $order['vc_id'];
    } else {
        $total['vc_id'] = [];
    }

    $total['amount'] = app(DscRepository::class)->changeFloat($total['amount']);
    $use_value_card = app(DscRepository::class)->changeFloat($use_value_card);

    //使用储值卡支付
    if ($total['amount'] >= $use_value_card) {
        $total['amount'] -= $use_value_card;
    }

    /* 税额 */
    if (config('shop.can_invoice') == 1 && isset($order['inv_content'])) {
        $total['tax'] = CommonRepository::orderInvoiceTotal($total['amount'], $order['inv_content']);
    } else {
        $total['tax'] = 0;
    }

    $total['amount'] = $total['amount'] + $total['tax'];

    $total['tax_formated'] = app(DscRepository::class)->getPriceFormat($total['tax'], false);

    /* 余额 */
    $order['surplus'] = isset($order['surplus']) && $order['surplus'] > 0 ? $order['surplus'] : 0;
    if ($total['amount'] > 0) {
        if ($order['surplus'] > $total['amount']) {
            $order['surplus'] = $total['amount'];
            $total['amount'] = 0;
        } else {
            $total['amount'] -= floatval($order['surplus']);
        }
    } else {
        $order['surplus'] = 0;
        $total['amount'] = 0;
    }

    $total['surplus'] = $order['surplus'];
    $total['surplus_formated'] = app(DscRepository::class)->getPriceFormat($total['surplus'], false);

    /* 保存订单信息 */
    session([
        'flow_order' => $order
    ]);

    $se_flow_type = session('flow_type', '');

    /* 支付费用 */
    if (!empty($order['pay_id']) && ($total['real_goods_count'] > 0 || $se_flow_type != CART_EXCHANGE_GOODS)) {
        $total['pay_fee'] = pay_fee($order['pay_id'], $total['amount'], $shipping_cod_fee);
    }

    $total['pay_fee_formated'] = app(DscRepository::class)->getPriceFormat($total['pay_fee'], false);

    $total['amount'] += $total['pay_fee']; // 订单总额累加上支付费用
    $total['amount_formated'] = app(DscRepository::class)->getPriceFormat($total['amount'], false);

    /* 取得可以得到的积分和红包 */
    if ($order['extension_code'] == 'group_buy') {
        $total['will_get_integral'] = $group_buy['gift_integral'] ?? 0;
    } elseif ($order['extension_code'] == 'exchange_goods') {
        $total['will_get_integral'] = 0;
    } else {
        $total['will_get_integral'] = get_give_integral($cart_value, $user_id);
    }

    $total_bonus = app(FlowActivityService::class)->getTotalBonus($user_id);
    $total['will_get_bonus'] = $order['extension_code'] == 'exchange_goods' ? 0 : app(DscRepository::class)->getPriceFormat($total_bonus, false);
    $total['formated_goods_price'] = app(DscRepository::class)->getPriceFormat($total['goods_price'], false);
    $total['formated_market_price'] = app(DscRepository::class)->getPriceFormat($total['market_price'], false);
    $total['formated_saving'] = app(DscRepository::class)->getPriceFormat($total['saving'], false);
    $total['formated_presale_price'] = app(DscRepository::class)->getPriceFormat($total['presale_price'], false);

    if ($order['extension_code'] == 'exchange_goods') {
        $exchange_integral = Cart::select('goods_id', 'goods_number')
            ->where('rec_type', CART_EXCHANGE_GOODS)->where('is_gift', 0)->where('goods_id', '>', 0);

        $exchange_integral = $exchange_integral->whereHasIn('getExchangeGoods');

        $exchange_integral = $exchange_integral->with([
            'getExchangeGoods' => function ($query) {
                $query->select('goods_id', 'exchange_integral');
            }
        ]);

        if (!empty($user_id)) {
            $exchange_integral = $exchange_integral->where('user_id', $user_id);
        } else {
            $session_id = app(SessionRepository::class)->realCartMacIp();
            $exchange_integral = $exchange_integral->where('session_id', $session_id);
        }

        $exchange_integral = BaseRepository::getToArrayGet($exchange_integral);

        $integral_num = 0;
        if ($exchange_integral) {
            foreach ($exchange_integral as $key => $row) {
                $row = $row['get_exchange_goods'] ? array_merge($row, $row['get_exchange_goods']) : $row;

                $integral_num += $row['exchange_integral'] * $row['goods_number'];
            }
        }

        $total['exchange_integral'] = $integral_num;
    }

    return $total;
}

/**
 * 修改智能权重里的商品退换货数量
 * @param int $goods_id 订单商品id
 * @param array $return_num 商品退换货数量
 * @return  bool
 */
function update_return_num($goods_id, $return_num)
{
    $res = IntelligentWeight::select('return_number', 'goods_number')->where('goods_id', $goods_id);
    $res = BaseRepository::getToArrayFirst($res);

    $res['return_number'] = isset($res['return_number']) ? $res['return_number'] : 0;
    $res['goods_number'] = isset($res['goods_number']) ? $res['goods_number'] : 0;

    $return_num['goods_number'] = $res['goods_number'] - $return_num['return_number'];
    if ($res) {
        $return_num['return_number'] += $res['return_number'];
        $res = IntelligentWeight::where('goods_id', $goods_id)->update($return_num);
    } else {
        $res = IntelligentWeight::insertGetId($return_num);
    }
    update_goods_weights($goods_id); // 更新权重值

    return $res;
}

/**
 * 修改订单
 *
 * @param int $order_id
 * @param array $order
 * @return bool
 * @throws Exception
 */
function update_order($order_id = 0, $order = [])
{
    if (empty($order_id) || empty($order)) {
        return false;
    }

    /* 会员中心手动余额输入支付 */
    $old_order_amount = isset($order['old_order_amount']) ? floatval($order['old_order_amount']) : 0;
    $old_order_amount = app(DscRepository::class)->changeFloat($old_order_amount);

    $request_surplus = isset($order['request_surplus']) ? floatval($order['request_surplus']) : 0;
    $request_surplus = app(DscRepository::class)->changeFloat($request_surplus);

    $is_order_amount = isset($order['order_amount']) ? 1 : 0;
    $new_amount = 0;
    if ($is_order_amount == 1) {
        $order['order_amount'] = !empty($order['order_amount']) ? floatval($order['order_amount']) : 0;

        $new_amount = $order['order_amount'] + $request_surplus;
        $new_amount = app(DscRepository::class)->changeFloat($new_amount);

        /* 兼容主订单已付款 更新状态 */
        if ($order['order_amount'] <= 0) {
            if (isset($order['main_count']) && $order['main_count'] > 0) {
                $order_other['main_pay'] = 2;
            }
        }
    }

    /* 获取表字段 */
    $order_other = BaseRepository::getArrayfilterTable($order, 'order_info');

    $update = OrderInfo::where('order_id', $order_id)->update($order_other);

    if ($request_surplus) {
        $surplus = $request_surplus;
    } else {
        $surplus = $order['surplus'] ?? 0;
    }

    /* 操作主订单更新子订单 */
    if ($is_order_amount == 1 && (($order['order_amount'] <= 0) || ($order['order_amount'] > 0 && $request_surplus > 0 && $old_order_amount == $new_amount))) {
        if (isset($order['main_count']) && $order['main_count'] > 0) {
            $child_list = OrderInfo::select('order_id', 'order_sn', 'order_status', 'shipping_status', 'pay_status', 'order_amount', 'surplus', 'money_paid')
                ->where('main_order_id', $order_id)
                ->where('pay_status', '<>', PS_PAYED);

            $child_list = BaseRepository::getToArrayGet($child_list);

            if ($child_list) {
                $dbRaw = [];
                foreach ($child_list as $key => $val) {
                    if (isset($order['order_status'])) {
                        $dbRaw['order_status'] = $order['order_status'];
                    }

                    if (isset($order['shipping_status'])) {
                        $dbRaw['shipping_status'] = $order['shipping_status'];
                    }

                    if (isset($order['pay_status'])) {
                        $dbRaw['pay_status'] = $order['pay_status'];
                    }

                    $order_amount = $val['order_amount'];
                    if ($val['pay_status'] != PS_PAYED) {
                        if ($surplus > 0) {
                            if ($order_amount > 0) {
                                if ($surplus >= $order_amount) {
                                    $dbRaw['order_amount'] = 0;
                                    $surplus = $surplus - $order_amount;

                                    if ($val['surplus'] > 0) {
                                        $dbRaw['surplus'] = $order_amount + $val['surplus'];
                                    } else {
                                        $dbRaw['surplus'] = $order_amount;
                                    }
                                } else {
                                    $dbRaw['order_amount'] = $order_amount - $surplus;

                                    if ($val['surplus'] > 0) {
                                        $dbRaw['surplus'] = $surplus + $val['surplus'];
                                    } else {
                                        $dbRaw['surplus'] = $surplus;
                                    }

                                    $surplus = 0;
                                }

                                if ($dbRaw['order_amount'] <= 0) {
                                    $dbRaw['pay_status'] = PS_PAYED;
                                }
                            }
                        }
                    }

                    $other = BaseRepository::getDbRaw($dbRaw);

                    OrderInfo::where('order_id', $val['order_id'])->update($other);

                    $username = '';
                    if ($request_surplus > 0) {
                        $username = lang('common.buyer');
                    }

                    /* 记录订单操作记录 */
                    order_action($val['order_sn'], $other['order_status'], $other['shipping_status'], $other['pay_status'], lang('common.main_order_pay'), $username);
                }
            }
        }
    }

    return $update;
}

/**
 * 得到新订单号
 * @return  string
 */
function get_order_sn()
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
 * 取得购物车商品
 *
 * @param int $type 类型：默认普通商品
 * @param string $cart_value
 * @param int $ru_type
 * @param string $consignee
 * @param int $store_id
 * @param int $user_id
 * @param int $is_virtual
 * @return mixed
 * @throws Exception
 */
function cart_goods($type = CART_GENERAL_GOODS, $cart_value = '', $ru_type = 0, $consignee = '', $store_id = 0, $user_id = 0, $is_virtual = 0)
{
    if (empty($user_id)) {
        $user_id = session('user_id', 0);
    }

    $time = TimeRepository::getGmTime();

    $rec_txt = [
        lang('common.rec_txt.1'),
        lang('common.rec_txt.2'),
        lang('common.rec_txt.3'),
        lang('common.rec_txt.4'),
        lang('common.rec_txt.5'),
        lang('common.rec_txt.6'),
        lang('common.rec_txt.10'),
        lang('common.rec_txt.12'),
        lang('common.rec_txt.13'),
        CART_OFFLINE_GOODS => lang('common.rec_txt.offline'),
    ];

    $arr = Cart::selectRaw('*, goods_price * goods_number AS subtotal')
        ->where('rec_type', $type);

    $cart_value = BaseRepository::getExplode($cart_value);

    if ($cart_value) {
        $arr = $arr->whereIn('rec_id', $cart_value);
    }

    if (CROSS_BORDER === true) { // 跨境多商户
        if (empty($cart_value)) {
            return [];
        }
    }

    if ($store_id) {
        $arr = $arr->where('store_id', $store_id);
    }

    if (!empty($user_id)) {
        $arr = $arr->where('user_id', $user_id);
    } else {
        $session_id = app(SessionRepository::class)->realCartMacIp();
        $arr = $arr->where('session_id', $session_id);
    }

    $arr = $arr->orderByRaw("group_id DESC, parent_id ASC, rec_id DESC");
    $arr = BaseRepository::getToArrayGet($arr);

    $virtual = 0;

    //查询非超值礼包商品
    if ($arr) {

        $cartIdList = FlowRepository::cartGoodsAndPackage($arr);
        $goods_id = $cartIdList['goods_id']; //普通商品ID
        $package_goods_id = $cartIdList['package_goods_id']; //超值礼包ID

        $goodsWhere = [
            'type' => $type,
            'presale' => CART_PRESALE_GOODS,
            'goods_select' => [
                'goods_id', 'user_id', 'cat_id', 'goods_thumb', 'default_shipping',
                'goods_weight as goodsweight', 'goods_weight', 'is_shipping', 'freight',
                'tid', 'shipping_fee', 'brand_id', 'cloud_id', 'is_delete',
                'cloud_goodsname', 'dis_commission', 'is_distribution',
                'goods_number as number', 'is_real', 'extension_code', 'cost_price',
                'is_discount', 'shop_price as rec_shop_price', 'promote_price as rec_promote_price',
                'is_promote', 'promote_start_date', 'promote_end_date', 'integral'
            ]
        ];

        if (CROSS_BORDER === true) { // 跨境多商户
            array_push($goodsWhere['goods_select'], 'free_rate');
        }

        if (file_exists(MOBILE_DRP)) {
            // 分销
            array_push($goodsWhere['goods_select'], 'membership_card_id');
        }

        $goodsList = GoodsDataHandleService::GoodsCartDataList($goods_id, $goodsWhere);

        $presaleActivityList = GoodsDataHandleService::PresaleActivityDataList($goods_id);
        $goodsConsumptionList = GoodsDataHandleService::GoodsConsumptionDataList($goods_id);

        $warehouseId = BaseRepository::getKeyPluck($arr, 'warehouse_id');
        $warehouse = app(RegionDataHandleService::class)->regionWarehouseDataList($warehouseId);

        $user_rank = [];
        if (empty($user_id)) {
            $user_id = session('user_id', 0);
        } else {
            $rank = app(UserCommonService::class)->getUserRankByUid($user_id);
            $user_rank['rank_id'] = $rank['rank_id'] ?? 0;
            $user_rank['discount'] = isset($rank['discount']) ? $rank['discount'] / 100 : 1;
        }

        $recGoodsModelList = BaseRepository::getColumn($arr, 'model_attr', 'rec_id');
        $recGoodsModelList = $recGoodsModelList ? array_unique($recGoodsModelList) : [];

        $isModel = 0;
        if (in_array(1, $recGoodsModelList) || in_array(2, $recGoodsModelList)) {
            $isModel = 1;
        }

        if ($isModel == 1) {
            $warehouseGoodsList = GoodsDataHandleService::getWarehouseGoodsDataList($goods_id);
            $warehouseAreaGoodsList = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods_id);
        } else {
            $warehouseGoodsList = [];
            $warehouseAreaGoodsList = [];
        }

        $product_id = BaseRepository::getKeyPluck($arr, 'product_id');
        $productsList = GoodsDataHandleService::getProductsDataList($product_id, '*', 'product_id');

        if ($isModel == 1) {
            $productsWarehouseList = GoodsDataHandleService::getProductsWarehouseDataList($product_id, 0, '*', 'product_id');
            $productsAreaList = GoodsDataHandleService::getProductsAreaDataList($product_id, 0, '*', 'product_id');
        } else {
            $productsWarehouseList = [];
            $productsAreaList = [];
        }

        $productsGoodsAttrList = [];
        if ($productsList || $productsWarehouseList || $productsAreaList) {
            $productsGoodsAttr = BaseRepository::getKeyPluck($productsList, 'goods_attr');
            $productsWarehouseGoodsAttr = BaseRepository::getKeyPluck($productsList, 'goods_attr');
            $productsAreaGoodsAttr = BaseRepository::getKeyPluck($productsList, 'goods_attr');

            $productsGoodsAttr = BaseRepository::getArrayMerge($productsGoodsAttr, $productsWarehouseGoodsAttr);
            $productsGoodsAttr = BaseRepository::getArrayMerge($productsGoodsAttr, $productsAreaGoodsAttr);

            $productsGoodsAttrList = GoodsDataHandleService::getGoodsAttrDataList($productsGoodsAttr, ['goods_attr_id', 'attr_img_flie', 'attr_gallery_flie']);
        }

        $productsStoreList = StoreDataHandleService::getStoreProductsDataList($product_id, '*', 'product_id');

        /* 处理更新价格 */
        $payPriceList = app(CartCommonService::class)->cartFinalPrice($user_id, $arr, $goodsList, $warehouseGoodsList, $warehouseAreaGoodsList, $productsList, $productsWarehouseList, $productsAreaList);

        $goodsActivity = GoodsDataHandleService::getGoodsActivityDataList($package_goods_id, GAT_PACKAGE);

        /* 格式化价格及礼包商品 */
        foreach ($arr as $key => $value) {

            $is_null = 0;

            /* 解决冲突字段 */
            $extension_code = $value['extension_code'];

            $goods = $goodsList[$value['goods_id']] ?? [];

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

            $value['get_goods'] = $goods;
            $value['get_warehouse_goods'] = $warehouseGoods;
            $value['get_warehouse_area_goods'] = $areaGoods;

            // 商品信息
            if ($value['model_attr'] == 1) {
                $value['sku_weight'] = $productsWarehouseList[$value['product_id']]['sku_weight'] ?? 0; //货品重量
                $integral = $warehouseGoods['pay_integral'] ?? 0;
                $goods_number = $warehouseGoods['region_number'] ?? 0;
            } elseif ($value['model_attr'] == 2) {
                $value['sku_weight'] = $productsAreaList[$value['product_id']]['sku_weight'] ?? 0; //货品重量
                $integral = $areaGoods['pay_integral'] ?? 0;
                $goods_number = $areaGoods['region_number'] ?? 0;
            } else {
                $value['sku_weight'] = $productsList[$value['product_id']]['sku_weight'] ?? 0; //货品重量
                $integral = $goods['integral'] ?? 0;
                $goods_number = $goods['number'] ?? 0;
            }

            /**
             * 取最小兑换积分
             */
            $integral = [
                app(DscRepository::class)->integralOfValue($value['goods_price'] * $value['goods_number']),
                app(DscRepository::class)->integralOfValue($integral * $value['goods_number'])
            ];

            $integral = BaseRepository::getArrayMin($integral);

            $value['integral_total'] = app(DscRepository::class)->valueOfIntegral($integral);

            /* 显示预售 start */
            $sql = [
                'where' => [
                    [
                        'name' => 'is_finished',
                        'value' => 0
                    ],
                    [
                        'name' => 'start_time',
                        'condition' => '<',
                        'value' => $time
                    ],
                    [
                        'name' => 'end_time',
                        'condition' => '>',
                        'value' => $time
                    ]
                ]
            ];

            $goodsPresale = $presaleActivityList[$value['goods_id']] ?? [];
            $presale = BaseRepository::getArraySqlFirst($goodsPresale, $sql);

            $value['get_presale_activity'] = $presale;
            /* 显示预售 end */

            //当商品有属性货品时
            $product_info = [];
            if (!empty($value['goods_attr_id'])) {
                if ($value['model_attr'] == 1) {
                    $product_info = $productsWarehouseList[$value['product_id']] ?? [];
                } elseif ($value['model_attr'] == 2) {
                    $product_info = $productsAreaList[$value['product_id']] ?? [];
                } else {
                    $product_info = $productsList[$value['product_id']] ?? [];
                }

                $goods['product_sn'] = $product_info['product_sn'] ?? ''; // 属性货品号

                //当商品有属性时 取加入购物车成本价(属性货品成本价)
                $goods['cost_price'] = !empty($value['cost_price']) ? $value['cost_price'] : $product_info['product_cost_price'] ?? 0;
            }

            //过滤超值礼包合并数组
            if (empty($value['extension_code']) || $value['extension_code'] != 'package_buy') {

                if (isset($goods['user_id'])) {
                    $goods['ru_id'] = $goods['user_id'];
                    unset($goods['user_id']);
                }

                $value = $goods ? array_merge($value, $goods) : $value;
            }

            if ($extension_code) {
                $value['extension_code'] = $extension_code;
            }

            /* 存会员等级ID */
            $value['user_rank'] = $user_rank['rank_id'];

            $arr[$key] = $value;

            if ($type == CART_PRESALE_GOODS) {
                $value['deposit'] = $value['get_presale_activity']['deposit'] ?? 0;
            }

            if ($value['extension_code'] == 'virtual_card') {
                $virtual += 1;
            }

            // 更新购物车商品价格 - 普通商品
            if (in_array($value['rec_type'], [CART_GENERAL_GOODS, CART_ONESTEP_GOODS]) && $value['extension_code'] != 'package_buy' && $value['is_gift'] == 0 && $value['parent_id'] == 0) {
                $goods_price = $payPriceList[$value['rec_id']]['pay_price'] ?? 0;
                if ($value['goods_price'] != $goods_price) {
                    CartCommonService::getUpdateCartPrice($goods_price, $value['rec_id']);

                    $value['goods_price'] = $goods_price;
                }
            }

            $goodsSelf = false;
            if ($value['user_id'] == 0) {
                $goodsSelf = true;
            }

            $arr[$key]['goods_price'] = $value['goods_price'];
            $arr[$key]['goods_price_format'] = app(DscRepository::class)->getPriceFormat($value['goods_price'], true, true, $goodsSelf);
            $arr[$key]['formated_subtotal'] = app(DscRepository::class)->getPriceFormat($arr[$key]['subtotal'], true, true, $goodsSelf);
            $arr[$key]['goods_amount'] = $value['goods_price'] * $value['goods_number'];

            if (CROSS_BORDER === true) {
                $arr[$key]['free_rate'] = isset($value['free_rate']) && is_numeric($value['free_rate']) ? $value['free_rate'] : 1;
            }

            /* 增加是否在购物车里显示商品图 */
            if (($GLOBALS['_CFG']['show_goods_in_cart'] == "2" || $GLOBALS['_CFG']['show_goods_in_cart'] == "3") && $value['extension_code'] != 'package_buy') {
                $value['goods_thumb'] = $goods['goods_thumb'] ?? '';
                if (isset($goods['is_delete']) && $goods['is_delete'] == 1) {
                    $arr[$key]['is_invalid'] = 1;
                }
            }

            if ($value['extension_code'] == 'package_buy') {

                $package = $goodsActivity[$value['goods_id']] ?? [];

                if (empty($package)) {
                    //移除无效的超值礼包
                    $delPackage = Cart::where('goods_id', $value['goods_id'])->where('extension_code', 'package_buy');

                    if (!empty($user_id)) {
                        $delPackage = $delPackage->where('user_id', $user_id);
                    } else {
                        $session_id = app(SessionRepository::class)->realCartMacIp();
                        $delPackage = $delPackage->where('session_id', $session_id);
                    }

                    $delPackage->delete();
                    $is_null = 1;
                }

                $value['amount'] = 0;
                $arr[$key]['dis_amount'] = 0;
                $arr[$key]['discount_amount'] = app(DscRepository::class)->getPriceFormat($arr[$key]['dis_amount'], true, true, $goodsSelf);

                $arr[$key]['package_goods_list'] = app(PackageGoodsService::class)->getPackageGoods($value['goods_id']);

                if ($package) {
                    $arr[$key]['goods_thumb'] = !empty($package['activity_thumb']) ? app(DscRepository::class)->getImagePath($package['activity_thumb']) : app(DscRepository::class)->dscUrl('themes/ecmoban_dsc2017/images/17184624079016pa.jpg');
                }

                $arr[$key]['goods_weight'] = BaseRepository::getArraySum($arr[$key]['package_goods_list'], 'goods_weight');
                $arr[$key]['goodsweight'] = $arr[$key]['goods_weight'];
                $arr[$key]['goods_number'] = $value['goods_number'];
                $arr[$key]['attr_number'] = !app(PackageGoodsService::class)->judgePackageStock($value['goods_id'], $value['goods_number']);
            } else {
                //贡云商品参数
                $arr[$key]['cloud_goodsname'] = $value['cloud_goodsname'] ?? '';
                $arr[$key]['cloud_id'] = $value['cloud_id'] ?? 0;

                //ecmoban模板堂 --zhuo start 商品金额促销
                $goodsConsumption = $goodsConsumptionList[$value['goods_id']] ?? [];
                if ($goodsConsumption) {
                    $value['amount'] = app(DscRepository::class)->getGoodsConsumptionPrice($goodsConsumption, $value['subtotal']);
                } else {
                    $value['amount'] = $value['subtotal'];
                }

                $arr[$key]['dis_amount'] = $value['subtotal'] - $value['amount'];
                $arr[$key]['discount_amount'] = app(DscRepository::class)->getPriceFormat($arr[$key]['dis_amount'], true, true, $goodsSelf);
                //ecmoban模板堂 --zhuo end 商品金额促销

                $arr[$key]['goods_thumb'] = app(DscRepository::class)->getImagePath($value['goods_thumb']);
                $arr[$key]['formated_market_price'] = app(DscRepository::class)->getPriceFormat($value['market_price'], true, true, $goodsSelf);

                $goods_attr_id = $product_info['goods_attr'] ?? '';
                $goods_attr_id = BaseRepository::getExplode($goods_attr_id, '|');
                $arr[$key]['goods_thumb'] = app(GoodsAttrService::class)->cartGoodsAttrImage($goods_attr_id, $productsGoodsAttrList, $arr[$key]['goods_thumb']);

                $arr[$key]['formated_presale_deposit'] = isset($value['deposit']) ? app(DscRepository::class)->getPriceFormat($value['deposit'], true, true, $goodsSelf) : app(DscRepository::class)->getPriceFormat(0, true, true, $goodsSelf);
                $arr[$key]['region_name'] = $warehouse['region_name'] ?? '';

                // 立即购买为普通商品
                $value['rec_type'] = (isset($value['rec_type']) && $value['rec_type'] == CART_ONESTEP_GOODS) ? 0 : $value['rec_type'];
                $arr[$key]['rec_txt'] = $rec_txt[$value['rec_type']];

                if ($value['rec_type'] == CART_GROUP_BUY_GOODS) {
                    $group_buy = GoodsActivity::select('act_id', 'act_name')
                        ->where('review_status', 3)->where('act_type', GAT_GROUP_BUY)
                        ->where('goods_id', $value['goods_id']);

                    $group_buy = BaseRepository::getToArrayFirst($group_buy);

                    if ($group_buy) {
                        $arr[$key]['url'] = app(DscRepository::class)->buildUri('group_buy', ['gbid' => $group_buy['act_id']]);
                        $arr[$key]['act_name'] = $group_buy['act_name'];
                    }
                } elseif ($value['rec_type'] == CART_PRESALE_GOODS) {
                    $presale = PresaleActivity::select('act_id', 'act_name')
                        ->where('goods_id', $value['goods_id'])
                        ->where('review_status', 3);

                    $presale = BaseRepository::getToArrayFirst($presale);

                    if ($presale) {
                        $arr[$key]['act_name'] = $presale['act_name'];
                        $arr[$key]['url'] = app(DscRepository::class)->buildUri('presale', ['act' => 'view', 'presaleid' => $presale['act_id']], $presale['act_name']);
                    }
                } elseif ($value['rec_type'] == CART_EXCHANGE_GOODS) {
                    $arr[$key]['url'] = app(DscRepository::class)->buildUri('exchange_goods', ['gid' => $value['goods_id']], $value['goods_name']);
                } else {
                    $arr[$key]['url'] = app(DscRepository::class)->buildUri('goods', ['gid' => $value['goods_id']], $value['goods_name']);
                }

                //预售商品，不受库存限制
                if ($value['extension_code'] == 'presale' || $value['rec_type'] == CART_PRESALE_GOODS) {
                    $arr[$key]['attr_number'] = 1;
                } else {
                    if ($ru_type == 1 && $store_id == 0) {
                        if ($value['model_attr'] == 1) {
                            $prod = $productsWarehouseList[$value['product_id']] ?? [];
                        } elseif ($value['model_attr'] == 2) {
                            $prod = $productsAreaList[$value['product_id']] ?? [];
                        } else {
                            $prod = $productsList[$value['product_id']] ?? [];
                        }

                        if (empty($value['product_id'])) { //当商品没有属性库存时
                            $attr_number = ($GLOBALS['_CFG']['use_storage'] == 1) ? $goods_number : 1;
                        } else {
                            $attr_number = $prod['product_number'] ?? 0;
                        }

                        //贡云商品 验证库存
                        if ($value['cloud_id'] > 0 && isset($prod['cloud_product_id'])) {
                            $attr_number = app(JigonManageService::class)->jigonGoodsNumber(['cloud_product_id' => $prod['cloud_product_id']]);
                        }

                        $attr_number = !empty($attr_number) ? $attr_number : 0;
                        $arr[$key]['attr_number'] = $attr_number;
                    } else {
                        $arr[$key]['attr_number'] = $value['goods_number'];
                    }
                }

                $arr[$key]['goods_attr_text'] = app(GoodsAttrService::class)->getGoodsAttrInfo($value['goods_attr_id'], 'pice', $value['warehouse_id'], $value['area_id'], $value['area_city']);

                /* 获取门店信息 */
                if ($store_id > 0) {
                    if ($value['product_id']) {
                        $products = $productsStoreList[$value['product_id']] ?? [];
                        $attr_number = $products ? $products['product_number'] : 0;
                    } else {
                        $goodsInfo = StoreGoods::select('goods_number', 'ru_id')
                            ->where('store_id', $store_id)
                            ->where('goods_id', $value['goods_id']);
                        $goodsInfo = BaseRepository::getToArrayFirst($goodsInfo);
                        $attr_number = $goodsInfo['goods_number'] ?? 0;
                    }
                    $arr[$key]['attr_number'] = $attr_number;
                }
            }

            $flow_order = session('flow_order', []);

            $uc_id = $flow_order['uc_id'] ?? 0;
            if ($uc_id == 0 && $value['goods_coupons'] > 0) {
                Cart::where('rec_id', $value['rec_id'])->update([
                    'goods_coupons' => 0
                ]);
            }


            $uc_id = $flow_order['bonus_id'] ?? 0;
            if ($uc_id == 0 && $value['goods_bonus'] > 0) {
                Cart::where('rec_id', $value['rec_id'])->update([
                    'goods_bonus' => 0
                ]);
            }

            if ($is_null == 1) {
                unset($arr[$key]);
            }
        }
    }

    /* 检查是否含有配件 */
    $grouplist = BaseRepository::getKeyPluck($arr, 'group_id');
    $grouplist = ArrRepository::getArrayUnset($grouplist);

    if ($grouplist) {
        //过滤商品的配件 配件的数组新增到配件主商品的商品数组中
        $arr = app(CartCommonService::class)->cartGoodsGroupList($arr);
    }

    if ($ru_type == 1) {
        $arr = BaseRepository::getGroupBy($arr, 'ru_id');
        $arr = get_cart_ru_goods_list($arr, $consignee, $store_id);
    }

    if ($is_virtual == 1) {
        $total = [
            'goods_price' => 0, // 本店售价合计（有格式）
            'market_price' => 0, // 市场售价合计（有格式）
            'saving' => 0, // 节省金额（有格式）
            'save_rate' => 0, // 节省百分比
            'goods_amount' => 0, // 本店售价合计（无格式）
            'store_goods_number' => 0, // 门店商品
        ];

        $arr = [
            'goodslist' => $arr,
            'virtual' => $virtual,
            'total' => $total
        ];
    }

    return $arr;
}

/**
 * 取得贡云商品并推送
 *
 * @param array $cart_goods
 * @param array $order
 * @return array|bool|mix|string
 */
function set_cloud_order_goods($cart_goods = [], $order = [])
{
    $requ = [];

    //判断是否填写回调接口appkey，如果没有返回失败
    $app_key = ShopConfig::where('code', 'cloud_appkey')->value('value');
    if (!$app_key) {
        return $requ;
    }

    //商品信息
    $order_request = [];
    $order_detaillist = [];
    foreach ($cart_goods as $cart_goods_key => $cart_goods_val) {
        if ($cart_goods_val['cloud_id'] > 0) {
            $arr = [];
            $arr['goodName'] = $cart_goods_val['cloud_goodsname']; //商品名称
            $arr['goodId'] = $cart_goods_val['cloud_id']; //商品id
            //获取货品id，库存id
            if ($cart_goods_val['goods_attr_id']) {
                $goods_attr_id = explode(',', $cart_goods_val['goods_attr_id']);

                //获取货品信息
                $products_info = Products::select('cloud_product_id', 'inventoryid')->where('goods_id', $cart_goods_val['goods_id']);

                foreach ($goods_attr_id as $key => $val) {
                    $products_info = $products_info->whereRaw("FIND_IN_SET('$val', REPLACE(goods_attr, '|', ','))");
                }

                $products_info = BaseRepository::getToArrayFirst($products_info);

                $arr['inventoryId'] = $products_info['inventoryid']; //库存id
                $arr['productId'] = $products_info['cloud_product_id']; //货品id
                $arr['productPrice'] = ''; //new
            }
            $arr['quantity'] = $cart_goods_val['goods_number']; //购买数量
            $arr['deliveryWay'] = '3'; //快递方式 3为快递送  上门自提不支持
            $arr['brandId'] = 0; //new
            $arr['channel'] = 0; //new
            $arr['navigateImg1'] = ''; //new
            $arr['salePrice'] = 0; //new
            $arr['storeId'] = 0; //new

            $order_detaillist[] = $arr;
        }
    }

    //初始化数据
    if (!empty($order_detaillist)) {
        $order_request['orderDetailList'] = $order_detaillist;
        $order_request['address'] = $order['address']; //地址
        $order_request['area'] = get_table_date('region', "region_id='" . $order['district'] . "'", ['region_name'], 2); //地区
        $order_request['city'] = get_table_date('region', "region_id='" . $order['city'] . "'", ['region_name'], 2); //城市
        $order_request['province'] = get_table_date('region', "region_id='" . $order['province'] . "'", ['region_name'], 2); //城市
        $order_request['remark'] = $order['postscript']; //备注
        $order_request['mobile'] = intval($order['mobile']); //电话
        $order_request['payType'] = 99; //支付方式 统一用99
        $order_request['linkMan'] = $order['consignee']; //收件人
        $order_request['billType'] = !empty($order['invoice_type']) ? 2 : 1; //发票类型 2:公司，1、个人
        $order_request['billHeader'] = $order['inv_payee']; //发票抬头
        $order_request['isBill'] = 0; //是否开发票 根据开票规则 不直接开票给用户 所以默认传0
        $order_request['taxNumber'] = ''; //税号

        if ($order_request['billType'] == 2) {
            $users_vat_invoices_info = UsersVatInvoicesInfo::select('company_name', 'tax_id')
                ->where('user_id', $order['user_id']);
            $users_vat_invoices_info = BaseRepository::getToArrayFirst($users_vat_invoices_info);

            if ($users_vat_invoices_info) {
                $order_request['billHeader'] = $users_vat_invoices_info['company_name'];
                $order_request['taxNumber'] = $users_vat_invoices_info['tax_id'];
            }
        }

        $cloud = app(Cloud::class);
        $is_callable = [$cloud, 'addOrderMall'];

        /* 判断类对象方法是否存在 */
        if (is_callable($is_callable)) {
            $requ = $cloud->addOrderMall($order_request, $order);
            $requ = dsc_decode($requ, true);
        }
    }

    return $requ;
}

/**
 * 确认订单 推送给贡云
 */
function cloud_confirmorder($order_id)
{
    if ($order_id > 0) {
        //获取贡云服订单号  和上次订单总额
        $cloud_order = OrderCloud::select('rec_id', 'parentordersn AS orderSn')
            ->whereHasIn('getOrderGoods', function ($query) use ($order_id) {
                $query->where('order_id', $order_id);
            });

        $cloud_order = $cloud_order->with([
            'getOrderGoods' => function ($query) {
                $query->select('rec_id')->selectRaw("SUM(goods_number * goods_price) AS paymentFee");
            }
        ]);

        $cloud_order = BaseRepository::getToArrayFirst($cloud_order);

        if ($cloud_order) {
            $cloud_order['paymentFee'] = $cloud_order['get_order_goods'] ? floatval($cloud_order['get_order_goods']['paymentFee'] * 100) : 0;

            //获取支付流水号
            $payId = PayLog::where('order_id', $order_id)->where('order_type', PAY_ORDER)->value('log_id');
            $cloud_order['payId'] = $payId ? $payId : 0;

            $cloud_order['payType'] = 99; //支付方式  默认99

            $cloud_dsc_appkey = ShopConfig::where('code', 'cloud_dsc_appkey')->value('value');
            $cloud_order['notifyUrl'] = $GLOBALS['dsc']->url() . "api.php?app_key=" . $cloud_dsc_appkey . "&method=dsc.order.confirmorder.post&format=json&interface_type=1";

            $cloud = app(Cloud::class);
            $is_callable = [$cloud, 'confirmorder'];

            if (is_callable($is_callable)) {
                $cloud->confirmorder($cloud_order);
            }
        }
    }
}

/**
 * 检查某商品是否已经存在于购物车
 *
 * @access  public
 * @param integer $id
 * @param array $spec
 * @param int $type 类型：默认普通商品
 * @return  boolean
 */
function cart_goods_exists($id, $spec, $type = CART_GENERAL_GOODS)
{
    $user_id = session('user_id', 0);
    $session_id = app(SessionRepository::class)->realCartMacIp();

    $goods_attr = '';
    if ($spec) {
        $goods_attr = app(GoodsAttrService::class)->getGoodsAttrInfo($spec);
    }

    /* 检查该商品是否已经存在在购物车中 */
    $res = Cart::where('goods_id', $id)
        ->where('parent_id', 0)
        ->where('rec_type', $type);

    if ($goods_attr) {
        $res = $res->where('goods_attr', $goods_attr);
    }

    if (!empty($user_id)) {
        $res = $res->where('user_id', $user_id);
    } else {
        $res = $res->where('session_id', $session_id);
    }

    $count = $res->count();

    return ($count > 0);
}

/**
 * 获得购物车中商品的总重量、总价格、总数量
 *
 * @access  public
 * @param int $type 类型：默认普通商品
 * @return  array
 */
function cart_weight_price($type = CART_GENERAL_GOODS, $cart_value)
{
    $user_id = session('user_id', 0);
    $session_id = app(SessionRepository::class)->realCartMacIp();

    $cart_value = BaseRepository::getExplode($cart_value);

    $package_row['weight'] = 0;
    $package_row['amount'] = 0;
    $package_row['number'] = 0;

    $packages_row['free_shipping'] = 1;

    /* 计算超值礼包内商品的相关配送参数 */
    $row = Cart::where('extension_code', 'package_buy');

    if (!empty($user_id)) {
        $row = $row->where('user_id', $user_id);
    } else {
        $row = $row->where('session_id', $session_id);
    }

    if (!empty($cart_value)) {
        $row = $row->whereIn('rec_id', $cart_value);
    }

    $row = BaseRepository::getToArrayGet($row);

    if ($row) {
        $packages_row['free_shipping'] = 0;
        $free_shipping_count = 0;

        foreach ($row as $val) {

            // 如果商品全为免运费商品，设置一个标识变量
            $shipping_count = PackageGoods::where('package_id', $val['goods_id'])
                ->whereHasIn('getGoods', function ($query) {
                    $query->where('is_shipping', 0);
                });

            $shipping_count = $shipping_count->count();

            if ($shipping_count > 0) {
                // 循环计算每个超值礼包商品的重量和数量，注意一个礼包中可能包换若干个同一商品
                $goods_row = PackageGoods::where('package_id', $val['goods_id'])
                    ->whereHasIn('getGoods', function ($query) {
                        $query->where('is_shipping', 0)
                            ->where('freight', '<>', 2);
                    });

                $goods_row = $goods_row->with([
                    'getGoods' => function ($query) {
                        $query->select('goods_id', 'goods_weight', 'freight');
                    }
                ]);

                $goods_row = BaseRepository::getToArrayGet($goods_row);

                $weight = 0;
                $goods_price = 0;
                $number = 0;
                if ($goods_row) {
                    foreach ($goods_row as $pgkey => $pgval) {
                        $pgval = BaseRepository::getArrayMerge($pgval, $pgval['get_goods']);

                        $weight += $pgval['goods_weight'] * $pgval['goods_number'];
                        $goods_price += $pgval['goods_price'] * $pgval['goods_number'];
                        $number += $pgval['goods_number'];
                    }
                }

                $package_row['weight'] += floatval($weight) * $val['goods_number'];
                $package_row['amount'] += floatval($goods_price) * $val['goods_number'];
                $package_row['number'] += intval($number) * $val['goods_number'];
            } else {
                $free_shipping_count++;
            }
        }

        $packages_row['free_shipping'] = $free_shipping_count == count($row) ? 1 : 0;
    }

    /* 获得购物车中非超值礼包商品的总重量 */
    $res = Cart::where('rec_type', $type)
        ->where('extension_code', '<>', 'package_buy');

    $res = $res->whereHasIn('getGoods', function ($query) {
        $query->where('is_shipping', 0)
            ->where('freight', '<>', 2);
    });

    if (!empty($user_id)) {
        $res = $res->where('user_id', $user_id);
    } else {
        $res = $res->where('session_id', $session_id);
    }

    if (!empty($cart_value)) {
        $res = $res->whereIn('rec_id', $cart_value);
    }

    $res = $res->with([
        'getGoods' => function ($query) {
            $query->select('goods_id', 'goods_weight');
        }
    ]);


    $res = BaseRepository::getToArrayGet($res);

    $weight = 0;
    $amount = 0;
    $number = 0;

    if ($res) {
        foreach ($res as $key => $row) {
            $row = BaseRepository::getArrayMerge($row, $row['get_goods']);

            if ($row['freight'] == 1) {
                $weight += 0;
            } else {
                $weight += $row['goods_weight'] * $row['goods_number'];
            }

            $amount += $row['goods_price'] * $row['goods_number'];
            $number += $row['goods_number'];
        }
    }

    $packages_row['weight'] = floatval($weight) + $package_row['weight'];
    $packages_row['amount'] = floatval($amount) + $package_row['amount'];
    $packages_row['number'] = intval($number) + $package_row['number'];

    /* 格式化重量 */
    $packages_row['formated_weight'] = formated_weight($packages_row['weight']);

    return $packages_row;
}

/**
 * 添加商品到购物车（配件组合） by mike
 *
 * @access  public
 * @param integer $goods_id 商品编号
 * @param integer $num 商品数量
 * @param array $spec 规格值对应的id数组
 * @param integer $parent 基本件
 * @return  boolean
 */
function addto_cart_combo($goods_id, $num = 1, $spec = [], $parent = 0, $group = '', $warehouse_id = 0, $area_id = 0, $area_city = 0, $goods_attr = '') //ecmoban模板堂 --zhuo $warehouse_id
{
    $user_id = session('user_id', 0);

    $GoodsLib = app(GoodsService::class);

    if (!is_array($goods_attr)) {
        if (!empty($goods_attr)) {
            $goods_attr = explode(',', $goods_attr);
        } else {
            $goods_attr = [];
        }
    }

    $ok_arr = get_insert_group_main($parent, $num, $goods_attr, 0, $group, $warehouse_id, $area_id, $area_city);

    if ($ok_arr['is_ok'] == 1) { // 商品不存在
        $GLOBALS['err']->add($GLOBALS['_LANG']['group_goods_not_exists'], ERR_NOT_EXISTS);
        return false;
    }
    if ($ok_arr['is_ok'] == 2) { // 商品已下架
        $GLOBALS['err']->add($GLOBALS['_LANG']['group_not_on_sale'], ERR_NOT_ON_SALE);
        return false;
    }
    if ($ok_arr['is_ok'] == 3 || $ok_arr['is_ok'] == 4) { // 商品缺货
        $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['group_shortage']), ERR_OUT_OF_STOCK);
        return false;
    }

    $GLOBALS['err']->clean();
    $_parent_id = $parent;

    /* 取得商品信息 */
    $where = [
        'goods_id' => $goods_id,
        'warehouse_id' => $warehouse_id,
        'area_id' => $area_id,
        'area_city' => $area_city
    ];
    $goods = $GoodsLib->getGoodsInfo($where);

    if (empty($goods)) {
        $GLOBALS['err']->add($GLOBALS['_LANG']['goods_not_exists'], ERR_NOT_EXISTS);

        return false;
    }

    /* 是否正在销售 */
    if ($goods['is_on_sale'] == 0) {
        $GLOBALS['err']->add($GLOBALS['_LANG']['not_on_sale'], ERR_NOT_ON_SALE);

        return false;
    }

    /* 不是配件时检查是否允许单独销售 */
    if (empty($parent) && $goods['is_alone_sale'] == 0) {
        $GLOBALS['err']->add($GLOBALS['_LANG']['cannt_alone_sale'], ERR_CANNT_ALONE_SALE);

        return false;
    }

    /* 如果商品有规格则取规格商品信息 配件除外 */
    if ($goods['model_attr'] == 1) {
        $prod = ProductsWarehouse::where('goods_id', $goods_id)->where('warehouse_id', $warehouse_id);
    } elseif ($goods['model_attr'] == 2) {
        $prod = ProductsArea::where('goods_id', $goods_id)->where('area_id', $area_id);

        if ($GLOBALS['_CFG']['area_pricetype'] == 1) {
            $prod = $prod->where('city_id', $area_city);
        }
    } else {
        $prod = Products::where('goods_id', $goods_id);
    }

    $prod = BaseRepository::getToArrayFirst($prod);

    if (is_spec($spec) && !empty($prod)) {
        $product_info = app(GoodsAttrService::class)->getProductsInfo($goods_id, $spec, $warehouse_id, $area_id, $area_city);
    }
    if (empty($product_info)) {
        $product_info = ['product_number' => 0, 'product_id' => 0];
    }

    /* 检查：库存 */
    if ($GLOBALS['_CFG']['use_storage'] == 1) {
        $is_product = 0;
        //商品存在规格 是货品
        if (is_spec($spec) && !empty($prod)) {
            if (!empty($spec)) {
                /* 取规格的货品库存 */
                if ($num > $product_info['product_number']) {
                    $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $product_info['product_number']), ERR_OUT_OF_STOCK);

                    return false;
                }
            }
        } else {
            $is_product = 1;
        }

        if ($is_product == 1) {
            //检查：商品购买数量是否大于总库存
            if ($num > $goods['goods_number']) {
                $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $goods['goods_number']), ERR_OUT_OF_STOCK);

                return false;
            }
        }
    }

    /* 计算商品的促销价格 */
    $warehouse_area['warehouse_id'] = $warehouse_id;
    $warehouse_area['area_id'] = $area_id;
    $warehouse_area['area_city'] = $area_city;

    $spec_price = app(GoodsAttrService::class)->specPrice($spec, $goods_id, $warehouse_area);
    $goods['marketPrice'] += $spec_price;
    $goods_attr = app(GoodsAttrService::class)->getGoodsAttrInfo($spec, 'pice', $warehouse_id, $area_id, $area_city);
    $goods_attr_id = join(',', $spec);

    $session_id = app(SessionRepository::class)->realCartMacIp();
    $sess = empty($user_id) ? $session_id : '';

    /* 初始化要插入购物车的基本件数据 */
    $parent = [
        'user_id' => session('user_id'),
        'session_id' => $sess,
        'goods_id' => $goods_id,
        'goods_sn' => addslashes($goods['goods_sn']),
        'product_id' => $product_info['product_id'],
        'goods_name' => addslashes($goods['goods_name']),
        'market_price' => $goods['marketPrice'],
        'goods_attr' => addslashes($goods_attr),
        'goods_attr_id' => $goods_attr_id,
        'is_real' => $goods['is_real'],
        'model_attr' => $goods['model_attr'], //ecmoban模板堂 --zhuo 属性方式
        'warehouse_id' => $warehouse_id, //ecmoban模板堂 --zhuo 仓库
        'area_id' => $area_id, //ecmoban模板堂 --zhuo 仓库地区
        'area_city' => $area_city,
        'ru_id' => $goods['user_id'], //ecmoban模板堂 --zhuo 商家ID
        'extension_code' => $goods['extension_code'],
        'is_gift' => 0,
        'commission_rate' => $goods['commission_rate'],
        'is_shipping' => $goods['is_shipping'],
        'rec_type' => CART_GENERAL_GOODS,
        'add_time' => gmtime(),
        'group_id' => $group
    ];

    /* 如果该配件在添加为基本件的配件时，所设置的“配件价格”比原价低，即此配件在价格上提供了优惠， */
    /* 则按照该配件的优惠价格卖，但是每一个基本件只能购买一个优惠价格的“该配件”，多买的“该配件”不享 */
    /* 受此优惠 */

    $basic_list = GroupGoods::select('parent_id', 'goods_price')
        ->where('goods_id', $goods_id)
        ->where('parent_id', $_parent_id)
        ->orderBy('goods_price');

    $basic_list = BaseRepository::getToArrayGet($basic_list);

    /* 循环插入配件 如果是配件则用其添加数量依次为购物车中所有属于其的基本件添加足够数量的该配件 */
    foreach ($basic_list as $key => $value) {
        $attr_info = app(GoodsAttrService::class)->getGoodsAttrInfo($spec, 'pice', $warehouse_id, $area_id, $area_city);

        /* 检查该商品是否已经存在在购物车中 */
        $row = CartCombo::where('goods_id', $goods_id)
            ->where('parent_id', $value['parent_id'])
            ->where('extension_code', '<>', 'package_buy')
            ->where('rec_type', CART_GENERAL_GOODS)
            ->where('group_id', $group);

        if (!empty($user_id)) {
            $row = $row->where('user_id', $user_id);
        } else {
            $row = $row->where('session_id', $session_id);
        }

        $row = $row->count();

        if ($row) { //如果购物车已经有此物品，则更新
            $num = 1; //临时保存到数据库，无数量限制
            if (is_spec($spec) && !empty($prod)) {
                $goods_storage = $product_info['product_number'];
            } else {
                $goods_storage = $goods['goods_number'];
            }

            if ($GLOBALS['_CFG']['use_storage'] == 0 || $num <= $goods_storage) {
                $CartComboOther = [
                    'goods_number' => $num,
                    'commission_rate' => $goods['commission_rate'],
                    'goods_price' => $value['goods_price'],
                    'product_id' => $product_info['product_id'],
                    'goods_attr' => $attr_info,
                    'goods_attr_id' => $goods_attr_id,
                    'market_price' => $goods['marketPrice'],
                    'warehouse_id' => $warehouse_id,
                    'area_id' => $area_id,
                    'area_city' => $area_city
                ];
                $res = CartCombo::where('goods_id', $goods_id)
                    ->where('parent_id', $value['parent_id'])
                    ->where('extension_code', '<>', 'package_buy')
                    ->where('rec_type', CART_GENERAL_GOODS)
                    ->where('group_id', $group);

                if (!empty($user_id)) {
                    $res = $res->where('user_id', $user_id);
                } else {
                    $res = $res->where('session_id', $session_id);
                }

                $res->update($CartComboOther);
            } else {
                $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);

                return false;
            }
        } //购物车没有此物品，则插入
        else {
            /* 作为该基本件的配件插入 */
            $parent['goods_price'] = $value['goods_price'];
            $parent['goods_number'] = 1; //临时保存到数据库，无数量限制
            $parent['parent_id'] = $value['parent_id'];

            /* 添加 */
            CartCombo::insert($parent);
        }
    }

    return true;
}

//首次添加配件时，查看主件是否存在，否则添加主件
function get_insert_group_main($goods_id, $num = 1, $goods_spec = [], $parent = 0, $group = '', $warehouse_id = 0, $area_id = 0, $area_city = 0)
{
    $user_id = session('user_id', 0);

    $GoodsLib = app(GoodsService::class);

    $ok_arr['is_ok'] = 0;
    $spec = $goods_spec;

    $GLOBALS['err']->clean();

    /* 取得商品信息 */
    $where = [
        'goods_id' => $goods_id,
        'warehouse_id' => $warehouse_id,
        'area_id' => $area_id,
        'area_city' => $area_city
    ];
    $goods = $GoodsLib->getGoodsInfo($where);

    if (empty($goods)) {
        $ok_arr['is_ok'] = 1;
        return $ok_arr;
    }

    /* 是否正在销售 */
    if ($goods['is_on_sale'] == 0) {
        $ok_arr['is_ok'] = 2;
        return $ok_arr;
    }

    /* 如果商品有规格则取规格商品信息 */
    if ($goods['model_attr'] == 1) {
        $prod = ProductsWarehouse::where('goods_id', $goods_id)->where('warehouse_id', $warehouse_id);
    } elseif ($goods['model_attr'] == 2) {
        $prod = ProductsArea::where('goods_id', $goods_id)->where('area_id', $area_id);

        if ($GLOBALS['_CFG']['area_pricetype'] == 1) {
            $prod = $prod->where('city_id', $area_city);
        }
    } else {
        $prod = Products::where('goods_id', $goods_id);
    }

    $prod = BaseRepository::getToArrayFirst($prod);

    if (is_spec($spec) && !empty($prod)) {
        $product_info = app(GoodsAttrService::class)->getProductsInfo($goods_id, $spec, $warehouse_id, $area_id, $area_city);
    }
    if (empty($product_info)) {
        $product_info = ['product_number' => 0, 'product_id' => 0];
    }

    /* 检查：库存 */
    if ($GLOBALS['_CFG']['use_storage'] == 1) {
        $is_product = 0;
        //商品存在规格 是货品
        if (is_spec($spec) && !empty($prod)) {
            if (!empty($spec)) {
                /* 取规格的货品库存 */
                if ($num > $product_info['product_number']) {
                    $ok_arr['is_ok'] = 3;
                    return $ok_arr;
                }
            }
        } else {
            $is_product = 1;
        }

        if ($is_product == 1) {
            //检查：商品购买数量是否大于总库存
            if ($num > $goods['goods_number']) {
                $ok_arr['is_ok'] = 4;
                return $ok_arr;
            }
        }
    }

    /* 计算商品的促销价格 */
    $warehouse_area['warehouse_id'] = $warehouse_id;
    $warehouse_area['area_id'] = $area_id;
    $warehouse_area['area_city'] = $area_city;

    $spec_price = app(GoodsAttrService::class)->specPrice($spec, $goods_id, $warehouse_area);

    if ($GLOBALS['_CFG']['add_shop_price'] == 1) {
        $add_tocart = 1;
    } else {
        $add_tocart = 0;
    }

    $goods_price = app(GoodsCommonService::class)->getFinalPrice($goods_id, $num, true, $spec, $warehouse_id, $area_id, $area_city, 0, 0, $add_tocart);
    $goods['marketPrice'] += $spec_price;
    $goods_attr = app(GoodsAttrService::class)->getGoodsAttrInfo($spec, 'pice', $warehouse_id, $area_id, $area_city); //ecmoban模板堂 --zhuo
    $goods_attr_id = join(',', $spec);

    $session_id = app(SessionRepository::class)->realCartMacIp();
    $sess = empty(session('user_id')) ? $session_id : '';

    /* 初始化要插入购物车的基本件数据 */
    $parent = [
        'user_id' => $user_id,
        'session_id' => $sess,
        'goods_id' => $goods_id,
        'goods_sn' => addslashes($goods['goods_sn']),
        'product_id' => $product_info['product_id'],
        'goods_name' => addslashes($goods['goods_name']),
        'market_price' => $goods['marketPrice'],
        'goods_attr' => addslashes($goods_attr),
        'goods_attr_id' => $goods_attr_id,
        'is_real' => $goods['is_real'],
        'model_attr' => $goods['model_attr'], //ecmoban模板堂 --zhuo 属性方式
        'warehouse_id' => $warehouse_id, //ecmoban模板堂 --zhuo 仓库
        'area_id' => $area_id, //ecmoban模板堂 --zhuo 仓库地区
        'area_city' => $area_city,
        'ru_id' => $goods['user_id'], //ecmoban模板堂 --zhuo 商家ID
        'extension_code' => $goods['extension_code'],
        'is_gift' => 0,
        'is_shipping' => $goods['is_shipping'],
        'rec_type' => CART_GENERAL_GOODS,
        'add_time' => gmtime(),
        'group_id' => $group
    ];

    $attr_info = app(GoodsAttrService::class)->getGoodsAttrInfo($spec, 'pice', $warehouse_id, $area_id, $area_city);

    /* 检查该套餐主件商品是否已经存在在购物车中 */
    $row = CartCombo::where('goods_id', $goods_id)
        ->where('parent_id', 0)
        ->where('extension_code', '<>', 'package_buy')
        ->where('rec_type', CART_GENERAL_GOODS)
        ->where('group_id', $group);

    if (!empty($user_id)) {
        $row = $row->where('user_id', $user_id);
    } else {
        $row = $row->where('session_id', $session_id);
    }

    $row = $row->where('warehouse_id', $warehouse_id);

    $row = $row->count();

    if ($row) {
        $CartComboOther = [
            'goods_number' => $num,
            'goods_price' => $goods_price,
            'product_id' => $product_info['product_id'],
            'goods_attr' => $attr_info,
            'goods_attr_id' => $goods_attr_id,
            'market_price' => $goods['marketPrice'],
            'warehouse_id' => $warehouse_id,
            'area_id' => $area_id
        ];
        $res = CartCombo::where('goods_id', $goods_id)
            ->where('parent_id', 0)
            ->where('extension_code', '<>', 'package_buy')
            ->where('rec_type', CART_GENERAL_GOODS)
            ->where('group_id', $group);

        if (!empty($user_id)) {
            $res = $res->where('user_id', $user_id);
        } else {
            $res = $res->where('session_id', $session_id);
        }

        $res->update($CartComboOther);
    } else {
        $parent['goods_price'] = max($goods_price, 0);
        $parent['goods_number'] = $num;
        $parent['parent_id'] = 0;

        CartCombo::insert($parent);
    }
}

/**
 * 获取商品的原价、配件价、库存（配件组合） by mike
 * 返回数组
 */
function get_combo_goods_info($goods_id, $num = 1, $spec = [], $parent = 0, $warehouse_area)
{
    $warehouse_id = $warehouse_area['warehouse_id'];
    $area_id = $warehouse_area['area_id'];
    $area_city = $warehouse_area['area_city'];

    $result = [];

    /* 取得商品信息 */
    $goods = Goods::select('goods_id', 'goods_number', 'model_attr')
        ->where('goods_id', $goods_id)->where('is_delete', 0);

    $goods = BaseRepository::getToArrayFirst($goods);

    /* 如果商品有规格则取规格商品信息 配件除外 */
    if ($goods['model_attr'] == 1) {
        $prod = ProductsWarehouse::where('goods_id', $goods_id)->where('warehouse_id', $warehouse_id);
    } elseif ($goods['model_attr'] == 2) {
        $prod = ProductsArea::where('goods_id', $goods_id)->where('area_id', $area_id);
    } else {
        $prod = Products::where('goods_id', $goods_id);
    }

    $prod = BaseRepository::getToArrayFirst($prod);

    if (is_spec($spec) && !empty($prod)) {
        $product_info = app(GoodsAttrService::class)->getProductsInfo($goods_id, $spec, $warehouse_id, $area_id, $area_city);
    }
    if (empty($product_info)) {
        $product_info = ['product_number' => '', 'product_id' => 0];
    }

    //商品库存
    $result['stock'] = $goods['goods_number'];

    //商品存在规格 是货品 检查该货品库存
    if (is_spec($spec) && !empty($prod)) {
        if (!empty($spec)) {
            /* 取规格的货品库存 */
            $result['stock'] = $product_info['product_number'];
        }
    }

    /* 如果该配件在添加为基本件的配件时，所设置的“配件价格”比原价低，即此配件在价格上提供了优惠， */
    $res = GroupGoods::where('goods_id', $goods_id)
        ->where('parent_id', $parent)
        ->orderBy('goods_price');

    $res = BaseRepository::getToArrayGet($res);

    if ($res) {
        foreach ($res as $row) {
            $result['fittings_price'] = $row['goods_price'];
        }
    }

    /* 计算商品的促销价格 */
    $result['fittings_price'] = (isset($result['fittings_price'])) ? $result['fittings_price'] : app(GoodsCommonService::class)->getFinalPrice($goods_id, $num, true, $spec, $warehouse_id, $area_id, $area_city);
    $result['spec_price'] = app(GoodsAttrService::class)->specPrice($spec, $goods_id, $warehouse_area);//属性价格
    $result['goods_price'] = app(GoodsCommonService::class)->getFinalPrice($goods_id, $num, true, $spec, $warehouse_id, $area_id, $area_city);

    return $result;
}

/**
 * 修改用户
 * @param int $user_id 订单id
 * @param array $user key => value
 * @return  bool
 */
function update_user($user_id, $user)
{
    $res = Users::where('user_id', $user_id)->update($user);

    return $res;
}

/**
 * 取得用户地址列表
 * @param int $user_id 用户id
 * @return  array
 */
function address_list($user_id)
{
    $res = UserAddress::where('user_id', $user_id);
    $res = BaseRepository::getToArrayGet($res);

    return $res;
}

/**
 * 取得用户地址信息
 * @param int $address_id 地址id
 * @return  array
 */
function address_info($address_id)
{
    $res = UserAddress::where('address_id', $address_id);
    $res = BaseRepository::getToArrayFirst($res);

    return $res;
}

/**
 * 取得红包信息
 *
 * @param int $bonus_id 红包id
 * @param string $bonus_psd 红包序列号
 * @param int $cart_value
 * @return array
 */
function bonus_info($bonus_id = 0, $bonus_psd = '', $cart_value = 0)
{
    $goods_user = '';
    $where = '';
    if ($cart_value != 0 || !empty($cart_value)) {
        $cart_value = !is_array($cart_value) ? explode(",", $cart_value) : $cart_value;

        $goods_list = Cart::selectRaw('ru_id, ru_id AS user_id')->whereIn('rec_id', $cart_value);

        $goods_list = $goods_list->whereHasIn('getGoods');

        if ($goods_list) {
            foreach ($goods_list as $key => $row) {
                $goods_user .= $row['user_id'] . ',';
            }
        }

        if (!empty($goods_user)) {
            $goods_user = substr($goods_user, 0, -1);
            $goods_user = explode(',', $goods_user);
            $goods_user = array_unique($goods_user);
            $goods_user = implode(',', $goods_user);
            $goods_user = app(DscRepository::class)->delStrComma($goods_user);
            $goods_user = !is_array($goods_user) ? explode(",", $goods_user) : $goods_user;
            $where = "IF(usebonus_type > 0, usebonus_type = 1, user_id in($goods_user)) ";
        }
    }

    if (!empty($bonus_psd)) {
        $bonus = UserBonus::where('bonus_password', $bonus_psd);
    } else {
        $bonus = UserBonus::where('bonus_id', $bonus_id);
    }

    $bonus = $bonus->whereHasIn('getBonusType', function ($query) use ($where) {
        $query->where('review_status', 3);
        if ($where) {
            $query->whereRaw($where);
        }
    });

    $bonus = $bonus->with([
        'getBonusType' => function ($query) {
            $query->selectRaw("type_id, type_name, type_money, send_type, usebonus_type, min_amount, max_amount, send_start_date, send_end_date, use_start_date, use_end_date, min_goods_amount, review_status, review_content, user_id AS admin_id");
        }
    ]);

    $bonus = BaseRepository::getToArrayFirst($bonus);

    $get_bonus_type = $bonus['get_bonus_type'] ?? [];
    $bonus = $get_bonus_type ? array_merge($bonus, $get_bonus_type) : $bonus;

    return $bonus;
}

/**
 * 检查红包是否已使用
 * @param int $bonus_id 红包id
 * @return  bool
 */
function bonus_used($bonus_id)
{
    $res = UserBonus::where('bonus_id', $bonus_id)->value('order_id');

    return $res;
}

/**
 * 设置优惠券为未使用,并删除订单满额返券
 *
 * @param int $order_id 订单ID
 * @param array $uc_id 优惠券ID
 * @return mixed
 */
function unuse_coupons($order_id = 0, $uc_id = [])
{
    $uc_id = BaseRepository::getExplode($uc_id);
    $orderInfo = OrderInfo::select('order_id', 'main_count', 'coupons')->where('order_id', $order_id);
    $orderInfo = BaseRepository::getToArrayFirst($orderInfo);

    $coupons = $orderInfo['coupons'] ?? 0;

    $childOrder = [$order_id];
    if ($orderInfo['main_count'] > 0) {
        $childOrder = OrderInfo::select('order_id')
            ->where('main_order_id', $order_id)
            ->pluck('order_id');
        $childOrder = BaseRepository::getToArray($childOrder);
        $childOrder = BaseRepository::getArrayMerge($childOrder, [$order_id]);
    }

    //使用了优惠券才退券
    if ($coupons > 0) {
        // 判断当前订单是否满足了返券要求
        $other = [
            'order_id' => 0,
            'is_use_time' => 0,
            'is_use' => 0
        ];
        $res = CouponsUser::where('is_delete', 0)->whereIn('order_id', $childOrder)->whereIn('uc_id', $uc_id)->update($other);

        OrderInfo::whereIn('order_id', $childOrder)->update([
            'coupons' => 0,
            'uc_id' => ''
        ]);

        return $res;
    }
}

/**
 * 退还订单使用的储值卡消费金额
 *
 * @param int $order_id
 * @param int $ret_id
 * @param string $return_sn
 * @param int $is_shipping 默认退运费
 * @param int $noShippingFee 不退运费金额
 * @throws Exception
 */
function return_card_money($order_id = 0, $ret_id = 0, $return_sn = '', $is_shipping = 1, $noShippingFee = 0)
{
    $row = ValueCardRecord::where('order_id', $order_id);
    $row = BaseRepository::getToArrayFirst($row);

    if ($row) {
        $count = ValueCardRecord::where('vc_id', $row['vc_id'])
            ->where('order_id', $order_id)
            ->where('add_val', $row['use_val'])
            ->where('vc_dis', 1)
            ->count();

        if ($count == 0) {
            $time = TimeRepository::getGmTime();

            $order_info = OrderInfo::select('order_id', 'order_sn', 'order_status', 'shipping_status', 'pay_status')->where('order_id', $order_id);
            $order_info = BaseRepository::getToArrayFirst($order_info);

            /* 不退运费 */
            if ($is_shipping != 1) {
                if ($row['use_val'] > 0) {
                    $row['use_val'] -= $noShippingFee;
                }
            }

            /* 更新储值卡金额 */
            ValueCard::where('vid', $row['vc_id'])->increment('card_money', $row['use_val']);

            /* 更新储值卡金额使用日志 */
            $log = [
                'vc_id' => $row['vc_id'],
                'order_id' => $order_id,
                'use_val' => $row['use_val'],
                'vc_dis' => 1,
                'add_val' => $row['use_val'],
                'record_time' => $time,
                'change_desc' => sprintf(lang('admin/order.return_card_record'), $order_info['order_sn']),
                'ret_id' => $ret_id
            ];

            ValueCardRecord::insert($log);

            if ($return_sn) {
                $return_note = sprintf(lang('user.order_vcard_return'), $row['use_val']);
                app(OrderCommonService::class)->returnAction($ret_id, RF_AGREE_APPLY, FF_REFOUND, $return_note);

                $return_sn = "<br/>" . lang('order.order_return_running_number') . "：" . $return_sn;
            }

            $note = sprintf(lang('user.order_vcard_return') . $return_sn, $row['use_val']);
            order_action($order_info['order_sn'], $order_info['order_status'], $order_info['shipping_status'], $order_info['pay_status'], $note, null, 0, $time);
        }
    }
}

/**
 * 订单退款
 *
 * @param array $order 订单
 * @param int $refund_type 退款方式 1 到帐户余额 2 到退款申请（先到余额，再申请提款） 3 不处理
 * @param string $refund_note 退款说明
 * @param null $refund_amount 退款金额（如果为0，取订单已付款金额）
 * @param int $shipping_fee 退款运费金额（如果为0，取订单已付款金额）
 * @return bool
 * @throws Exception
 */
function order_refund($order = [], $refund_type = 0, $refund_note = '', $refund_amount = null, $shipping_fee = 0)
{
    if (empty($order)) {
        return false;
    }

    /* 检查参数 */
    $user_id = $order['user_id'];
    if ($user_id == 0 && $refund_type == 1) {
        return false;
    }

    if (is_null($refund_amount)) {
        $amount = $order['money_paid'] + $order['surplus'];

        if ($amount > 0 && $shipping_fee > 0) {
            $amount = $amount - $order['shipping_fee'] + $shipping_fee;
        }
    } else {
        $amount = $refund_amount + $shipping_fee;
    }

    if ($amount < 0) {
        return false;
    }

    if (!in_array($refund_type, [1, 2, 3])) {
        return false;
    }

    /* 备注信息 */
    if ($refund_note) {
        $change_desc = $refund_note;
    } else {
        $change_desc = sprintf(lang('admin/order.order_refund'), $order['order_sn']);
    }

    //退款不退发票金额
    if ($order['tax'] > 0) {
        $amount = $amount - $order['tax'];
    }

    if (($refund_type == 1 || $refund_type == 2) && !empty($refund_amount)) {
        //退款更新账单
        $other = [
            'return_shippingfee' => DB::raw("return_shippingfee  + ('$shipping_fee')"),
            'order_status' => $order['order_status'],
            'pay_status' => $order['pay_status'],
            'shipping_status' => $order['shipping_status']
        ];
        SellerBillOrder::where('order_id', $order['order_id'])->increment('return_amount', $refund_amount, $other);
    }

    /* 处理退款 */
    if (1 == $refund_type) {
        /* 如果非匿名，退回余额 */
        if ($user_id > 0) {
            $is_ok = 1;
            if (isset($order['ru_id']) && $order['ru_id'] && $order['chargeoff_status'] == 2) {
                $seller_shopinfo = SellerShopinfo::selectRaw("seller_money, credit_money, (seller_money + credit_money) AS credit")
                    ->where('ru_id', $order['ru_id']);
                $seller_shopinfo = BaseRepository::getToArrayFirst($seller_shopinfo);

                if ($seller_shopinfo && $seller_shopinfo['credit'] > 0 && $seller_shopinfo['credit'] >= $amount) {
                    $adminru = get_admin_ru_id();

                    $change_desc = lang('admin/order.action_user') . "：【" . $adminru['user_name'] . "】" . $refund_note;
                    $log = [
                        'user_id' => $order['ru_id'],
                        'user_money' => (-1) * $amount,
                        'change_time' => gmtime(),
                        'change_desc' => $change_desc,
                        'change_type' => 2
                    ];

                    MerchantsAccountLog::insert($log);
                    SellerShopinfo::where('ru_id', $order['ru_id'])->increment('seller_money', $log['user_money']);
                } else {
                    $is_ok = 0;
                }
            }

            if ($is_ok == 1) {
                log_account_change($user_id, $amount, 0, 0, 0, $change_desc);

                return true;
            } else {
                /* 返回失败，不允许退款 */
                return false;
            }
        }
    } elseif (2 == $refund_type) {
        /* 如果非匿名，退回冻结资金 */
        if ($user_id > 0) {
            log_account_change($user_id, 0, $amount, 0, 0, $change_desc);

            /* user_account 表增加提款申请记录 */
            $account = [
                'user_id' => $user_id,
                'amount' => (-1) * $amount,
                'add_time' => gmtime(),
                'user_note' => $refund_note,
                'process_type' => SURPLUS_RETURN,
                'admin_user' => session()->has('admin_name') ? session('admin_name') : (session()->has('seller_name') ? session('seller_name') : ''),
                'admin_note' => sprintf(lang('admin/order.order_refund'), $order['order_sn']),
                'is_paid' => 0
            ];

            UserAccount::insert($account);

            return true;
        }

        return false;
    } else {
        return false;
    }
}

/**
 * 订单退款[储值卡金额]
 *
 * @param int $order_id 订单ID
 * @param int $vc_id 储值卡ID
 * @param int $refound_vcard 储值卡金额
 * @param string $return_sn 订单编号
 * @param int $ret_id 单品退货单ID
 * @throws Exception
 */
function get_return_vcard($order_id = 0, $vc_id = 0, $refound_vcard = 0, $return_sn = '', $ret_id = 0)
{
    if ($vc_id && $refound_vcard > 0) {
        $time = TimeRepository::getGmTime();
        $order_info = OrderInfo::select('order_id', 'user_id', 'order_sn', 'order_status', 'shipping_status', 'pay_status')->where('order_id', $order_id);
        $order_info = BaseRepository::getToArrayFirst($order_info);

        $refound_vcard = empty($refound_vcard) ? 0 : $refound_vcard;

        /* 更新储值卡金额 */
        ValueCard::where('vid', $vc_id)->where('user_id', $order_info['user_id'])->increment('card_money', $refound_vcard);

        /* 更新订单使用储值卡金额 */
        $log = [
            'vc_id' => $vc_id,
            'order_id' => $order_id,
            'use_val' => $refound_vcard,
            'vc_dis' => 1,
            'add_val' => $refound_vcard,
            'record_time' => $time,
            'change_desc' => sprintf(lang('admin/order.return_card_record'), $order_info['order_sn']),
            'ret_id' => $ret_id
        ];

        ValueCardRecord::insert($log);

        if ($return_sn) {
            $return_sn = "<br/>退换货-流水号：" . $return_sn;
        }

        $note = sprintf($GLOBALS['_LANG']['order_vcard_return'] . $return_sn, $refound_vcard);
        order_action($order_info['order_sn'], $order_info['order_status'], $order_info['shipping_status'], $order_info['pay_status'], $note, null, 0, $time);

        $return_note = sprintf($GLOBALS['_LANG']['order_vcard_return'], $refound_vcard);
        app(OrderCommonService::class)->returnAction($ret_id, RF_AGREE_APPLY, FF_REFOUND, $return_note);
    }
}

/**
 * 查询订单退换货已退运费金额
 * refund_type 1 退还余额, 3 不处理, 6 原路退款
 * @param int $order_id
 * @param int $ret_id
 * @return mixed
 */
function order_refound_shipping_fee($order_id = 0, $ret_id = 0)
{
    $price = OrderReturn::selectRaw("SUM(return_shipping_fee) AS return_shipping_fee")
        ->where('order_id', $order_id)
        ->whereIn('refund_type', [1, 3, 6])
        ->where('refound_status', 1); // 已退款

    if ($ret_id > 0) {
        $price = $price->where('ret_id', '<>', $ret_id);
    }

    $price = $price->value('return_shipping_fee');

    return $price;
}

/**
 * 查询订单退换货已退储值卡金额
 */
function get_query_vcard_return($order_id)
{
    $res = OrderAction::where('order_id', $order_id)->where('order_status', OS_RETURNED_PART);

    $res = BaseRepository::getToArrayGet($res);

    $price = 0;
    if ($res) {
        foreach ($res as $key => $row) {
            $res[$key]['action_note'] = !empty($row['action_note']) ? explode("<br/>", $row['action_note']) : '';
            $res[$key]['action_note'] = isset($res[$key]['action_note'][0]) && !empty($res[$key]['action_note'][0]) ? explode("：", $res[$key]['action_note'][0]) : '';
            $price += isset($res[$key]['action_note'][1]) && !empty($res[$key]['action_note'][1]) ? $res[$key]['action_note'][1] : 0;
        }
    }

    return floatval($price);
}

/**
 * 获得购物车中的商品
 *
 * @param string $cart_value
 * @param int $type
 * @param int $uid
 * @param int $favourable_id
 * @return array
 */
function get_cart_goods($cart_value = '', $type = 0, $uid = 0, $favourable_id = 0, $district_id = 0)
{

    /* 初始化 */
    $goods_list = [];
    $total = [
        'goods_price' => 0, // 本店售价合计（有格式）
        'market_price' => 0, // 市场售价合计（有格式）
        'saving' => 0, // 节省金额（有格式）
        'save_rate' => 0, // 节省百分比
        'goods_amount' => 0, // 本店售价合计（无格式）
        'store_goods_number' => 0, // 门店商品
    ];

    /* 循环、统计 */
    if ($uid > 0) {
        $user_id = $uid;
    } else {
        $user_id = session('user_id', 0);
    }

    $res = Cart::selectRaw('*, IF(parent_id, parent_id, goods_id) AS pid')
        ->where('rec_type', CART_GENERAL_GOODS)
        ->where('stages_qishu', '-1')
        ->where('store_id', 0);

    if (!empty($user_id)) {
        $res = $res->where('user_id', $user_id);
    } else {
        $session_id = app(SessionRepository::class)->realCartMacIp();
        $res = $res->where('session_id', $session_id);
    }

    if (!empty($cart_value)) {
        $cart_value = !is_array($cart_value) ? explode(",", $cart_value) : $cart_value;
        $res = $res->whereIn('rec_id', $cart_value);
    }

    //把购物车商品参与优惠活动的赠品查询出来
    if ($favourable_id > 0) {
        $favourable_arr['favourable_id'] = $favourable_id;
        $favourable_arr['user_id'] = $user_id;

        $res = $res->orWhere(function ($query) use ($favourable_arr) {
            $query->where('is_gift', $favourable_arr['favourable_id']);
            $query->where('user_id', $favourable_arr['user_id']);
        });
    }

    $res = $res->orderByRaw("group_id DESC, parent_id ASC, rec_id DESC");

    $res = BaseRepository::getToArrayGet($res);

    if (!empty($cart_value) && $uid > 0) {
        $groupList = BaseRepository::getKeyPluck($res, 'group_id');
        $goodsList = BaseRepository::getKeyPluck($res, 'goods_id');

        if ($groupList) {
            $cartGroup = Cart::where('user_id', $uid)->whereIn('group_id', $groupList)->where('parent_id', $goodsList);
            $cartGroup = BaseRepository::getToArrayGet($cartGroup);

            $res = BaseRepository::getArrayMerge($res, $cartGroup);
        }
    }

    /* 用于统计购物车中实体商品和虚拟商品的个数 */
    $virtual_goods_count = 0;
    $real_goods_count = 0;
    $total['subtotal_dis_amount'] = 0;
    $total['subtotal_discount_amount'] = 0;
    $store_type = 0;
    $stages_qishu = 0;

    $isRu = 0;
    $cart_value = [];
    if ($res) {

        $cartIdList = FlowRepository::cartGoodsAndPackage($res);
        $goods_id = $cartIdList['goods_id']; //普通商品ID
        $package_goods_id = $cartIdList['package_goods_id']; //超值礼包ID

        $store_id = BaseRepository::getKeyPluck($res, 'store_id');
        $warehouseId = BaseRepository::getKeyPluck($res, 'warehouse_id');

        $goodsWhere = [
            'type' => $type,
            'presale' => CART_PRESALE_GOODS,
            'goods_select' => [
                'goods_id', 'cat_id', 'user_id', 'goods_thumb', 'default_shipping',
                'goods_weight as goodsweight', 'goods_weight', 'is_shipping', 'freight',
                'tid', 'shipping_fee', 'brand_id', 'cloud_id', 'is_delete',
                'is_minimum', 'minimum_start_date', 'minimum_end_date', 'minimum',
                'is_xiangou', 'xiangou_num', 'xiangou_start_date', 'xiangou_end_date', 'goods_name',
                'goods_number as number', 'is_promote', 'promote_price', 'promote_start_date', 'promote_end_date',
                'is_discount', 'shop_price as rec_shop_price', 'promote_price as rec_promote_price'
            ]
        ];

        if (CROSS_BORDER === true) { // 跨境多商户
            array_push($goodsWhere['goods_select'], 'free_rate');
        }

        $goodsList = GoodsDataHandleService::GoodsCartDataList($goods_id, $goodsWhere);
        $goodsConsumptionList = GoodsDataHandleService::GoodsConsumptionDataList($goods_id);

        $offline_store = OfflineStoreDataHandleService::getOfflineStoreDataList($store_id);

        $whereStore = [
            'goods_id' => $goods_id,
            'is_confirm' => 1,
            'district' => $district_id
        ];

        $storeGoodsCount = OfflineStoreDataHandleService::getStoreGoodsCount($whereStore);
        $storeGoodsProductCount = OfflineStoreDataHandleService::getStoreGoodsProductCount($whereStore);

        $warehouse = app(RegionDataHandleService::class)->regionWarehouseDataList($warehouseId);

        $user_rank = [];
        if ($user_id > 0) {
            $rank = app(UserCommonService::class)->getUserRankByUid($user_id);
            $user_rank['rank_id'] = isset($rank['rank_id']) ? $rank['rank_id'] : 1;
            $user_rank['discount'] = isset($rank['discount']) ? $rank['discount'] / 100 : 1;
        }

        $recGoodsModelList = BaseRepository::getColumn($res, 'model_attr', 'rec_id');
        $recGoodsModelList = $recGoodsModelList ? array_unique($recGoodsModelList) : [];

        $isModel = 0;
        if (in_array(1, $recGoodsModelList) || in_array(2, $recGoodsModelList)) {
            $isModel = 1;
        }

        if ($isModel == 1) {
            $warehouseGoodsList = GoodsDataHandleService::getWarehouseGoodsDataList($goods_id);
            $warehouseAreaGoodsList = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods_id);
        } else {
            $warehouseGoodsList = [];
            $warehouseAreaGoodsList = [];
        }

        $product_id = BaseRepository::getKeyPluck($res, 'product_id');
        $productsList = GoodsDataHandleService::getProductsDataList($product_id, '*', 'product_id');

        if ($isModel == 1) {
            $productsWarehouseList = GoodsDataHandleService::getProductsWarehouseDataList($product_id, 0, '*', 'product_id');
            $productsAreaList = GoodsDataHandleService::getProductsAreaDataList($product_id, 0, '*', 'product_id');
        } else {
            $productsWarehouseList = [];
            $productsAreaList = [];
        }

        $productsGoodsAttrList = [];
        if ($productsList || $productsWarehouseList || $productsAreaList) {
            $productsGoodsAttr = BaseRepository::getKeyPluck($productsList, 'goods_attr');
            $productsWarehouseGoodsAttr = BaseRepository::getKeyPluck($productsList, 'goods_attr');
            $productsAreaGoodsAttr = BaseRepository::getKeyPluck($productsList, 'goods_attr');

            $productsGoodsAttr = BaseRepository::getArrayMerge($productsGoodsAttr, $productsWarehouseGoodsAttr);
            $productsGoodsAttr = BaseRepository::getArrayMerge($productsGoodsAttr, $productsAreaGoodsAttr);

            $productsGoodsAttrList = GoodsDataHandleService::getGoodsAttrDataList($productsGoodsAttr, ['goods_attr_id', 'attr_img_flie', 'attr_gallery_flie']);
        }

        /* 处理更新价格 */
        $payPriceList = app(CartCommonService::class)->cartFinalPrice($user_id, $res, $goodsList, $warehouseGoodsList, $warehouseAreaGoodsList, $productsList, $productsWarehouseList, $productsAreaList);

        $goodsActivity = GoodsDataHandleService::getGoodsActivityDataList($package_goods_id, GAT_PACKAGE);

        $nowTime = TimeRepository::getGmTime();

        foreach ($res as $key => $row) {

            $is_null = 0;

            $goods = $goodsList[$row['goods_id']] ?? [];

            if (isset($goods['user_id'])) {
                $goods['ru_id'] = $goods['user_id'];
                unset($goods['user_id']);
            }

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
            $areaGoods = BaseRepository::getArraySqlGet($areaGoods, $sql);

            $row['get_goods'] = $goods;
            $row['get_warehouse_goods'] = $warehouseGoods;
            $row['get_warehouse_area_goods'] = $areaGoods;

            if ($row['model_attr'] == 1) {
                $row['sku_weight'] = $productsWarehouseList[$row['product_id']]['sku_weight'] ?? 0; //货品重量
                $goods_number = $warehouseGoods['region_number'] ?? 0;
            } elseif ($row['model_attr'] == 2) {
                $row['sku_weight'] = $productsAreaList[$row['product_id']]['sku_weight'] ?? 0; //货品重量
                $goods_number = $areaGoods['region_number'] ?? 0;
            } else {
                $row['sku_weight'] = $productsList[$row['product_id']]['sku_weight'] ?? 0; //货品重量
                $goods_number = $goods['number'] ?? 0;
            }

            $row = $goods ? array_merge($row, $goods) : $row;

            //ecmoban模板堂 --zhuo start 限购
            if ($row['extension_code'] != 'package_buy') {
                $goods['is_xiangou'] = $goods['is_xiangou'] ?? 0;
                $goods['xiangou_num'] = $goods['xiangou_num'] ?? 0;

                $start_date = $goods['xiangou_start_date'] ?? '';
                $end_date = $goods['xiangou_end_date'] ?? '';

                if ($goods['is_xiangou'] == 1 && $nowTime > $start_date && $nowTime < $end_date) {
                    $orderGoods = app(OrderGoodsService::class)->getForPurchasingGoods($start_date, $end_date, $row['goods_id'], $user_id);
                    if ($orderGoods['goods_number'] >= $goods['xiangou_num']) {

                        //更新购物车中的商品数量
                        Cart::where('rec_id', $row['rec_id'])->update(['goods_number' => 0]);
                    } else {
                        if ($goods['xiangou_num'] > 0) {
                            if ($goods['is_xiangou'] == 1 && $orderGoods['goods_number'] + $row['goods_number'] > $goods['xiangou_num']) {
                                $cart_Num = $goods['xiangou_num'] - $orderGoods['goods_number'];

                                //更新购物车中的商品数量
                                Cart::where('rec_id', $row['rec_id'])->update(['goods_number' => $cart_Num]);

                                //更新限购购物车内商品数量
                                $row['goods_number'] = $cart_Num;
                            }
                        }
                    }
                }
            }
            //ecmoban模板堂 --zhuo end 限购

            // 更新购物车商品价格 - 普通商品
            if (in_array($row['rec_type'], [CART_GENERAL_GOODS, CART_ONESTEP_GOODS]) && $row['extension_code'] != 'package_buy' && $row['is_gift'] == 0 && $row['parent_id'] == 0) {
                $goods_price = $payPriceList[$row['rec_id']]['pay_price'] ?? 0;
                if ($row['goods_price'] != $goods_price) {
                    CartCommonService::getUpdateCartPrice($goods_price, $row['rec_id']);

                    $row['goods_price'] = $goods_price;
                }
            }

            //ecmoban模板堂 --zhuo start 商品金额促销
            $row['goods_amount'] = $row['goods_price'] * $row['goods_number'];
            $goodsConsumption = $goodsConsumptionList[$row['goods_id']] ?? [];
            if ($goodsConsumption) {
                $row['amount'] = app(DscRepository::class)->getGoodsConsumptionPrice($goodsConsumption, $row['goods_amount']);
            } else {
                $row['amount'] = $row['goods_amount'];
            }

            $goodsSelf = false;
            if ($row['ru_id'] == 0) {
                $goodsSelf = true;
            } else {
                $isRu = 1;
            }

            $total['goods_price'] += $row['amount'];
            $row['subtotal'] = $row['goods_amount'];
            $row['formated_subtotal'] = app(DscRepository::class)->getPriceFormat($row['goods_amount'], true, true, $goodsSelf);
            $row['dis_amount'] = $row['goods_amount'] - $row['amount'];
            $row['dis_amount'] = number_format($row['dis_amount'], 2, '.', '');
            $row['discount_amount'] = app(DscRepository::class)->getPriceFormat($row['dis_amount'], true, true, $goodsSelf);
            //ecmoban模板堂 --zhuo end 商品金额促销

            $total['subtotal_dis_amount'] += $row['dis_amount'];
            $total['subtotal_discount_amount'] = app(DscRepository::class)->getPriceFormat($total['subtotal_dis_amount'], true, true, $goodsSelf);

            $total['market_price'] += $row['market_price'] * $row['goods_number'];
            $row['goods_price_format'] = app(DscRepository::class)->getPriceFormat($row['goods_price'], true, true, $goodsSelf);
            $row['formated_market_price'] = app(DscRepository::class)->getPriceFormat($row['market_price'], true, true, $goodsSelf);

            $row['url'] = app(DscRepository::class)->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);

            $row['region_name'] = $warehouse[$row['warehouse_id']]['region_name'] ?? '';

            /* 统计实体商品和虚拟商品的个数 */
            if ($row['is_real']) {
                $real_goods_count++;
            } else {
                $virtual_goods_count++;
            }

            /* 查询规格 */
            if (trim($row['goods_attr']) != '') {
                $row['goods_attr'] = addslashes($row['goods_attr']);

                $goods_attr = BaseRepository::getExplode($row['goods_attr']);
                $attr_list = GoodsAttr::select('attr_value')->whereIn('goods_attr_id', $goods_attr);
                $attr_list = BaseRepository::getToArrayGet($attr_list);
                $attr_list = BaseRepository::getFlatten($attr_list);

                if ($attr_list) {
                    foreach ($attr_list as $attr) {
                        $row['goods_name'] .= ' [' . $attr . '] ';
                    }
                }
            }

            /* 增加是否在购物车里显示商品图 */
            if (($GLOBALS['_CFG']['show_goods_in_cart'] == "2" || $GLOBALS['_CFG']['show_goods_in_cart'] == "3") && $row['extension_code'] != 'package_buy') {
                $row['goods_thumb'] = isset($goods['goods_thumb']) ? app(DscRepository::class)->getImagePath($goods['goods_thumb']) : '';
                if (isset($goods['is_delete']) && $goods['is_delete'] == 1) {
                    $row['is_invalid'] = 1;
                }
            }

            if ($row['extension_code'] == 'package_buy') {
                $package = $goodsActivity[$row['goods_id']] ?? [];

                if ($package) {
                    $row['package_goods_list'] = app(PackageGoodsService::class)->getPackageGoods($package['act_id']);
                    $row['goods_thumb'] = !empty($package['activity_thumb']) ? app(DscRepository::class)->getImagePath($package['activity_thumb']) : app(DscRepository::class)->dscUrl('themes/ecmoban_dsc2017/images/17184624079016pa.jpg');
                } else {

                    //移除无效的超值礼包
                    $delPackage = Cart::where('goods_id', $row['goods_id'])->where('extension_code', 'package_buy');

                    if (!empty($user_id)) {
                        $delPackage = $delPackage->where('user_id', $user_id);
                    } else {
                        $session_id = app(SessionRepository::class)->realCartMacIp();
                        $delPackage = $delPackage->where('session_id', $session_id);
                    }

                    $delPackage->delete();

                    $is_null = 1;
                }
            }

            /* by kong 判断改商品是否存在门店商品 20160725 start */
            $storeCount = $storeGoodsCount[$row['goods_id']] ?? [];
            $storeProductCount = $storeGoodsProductCount[$row['goods_id']] ?? [];

            if (($row['group_id'] && $row['parent_id'] == 0) || (empty($row['group_id']) && $row['parent_id'] == 0)) {
                $row['store_count'] = isset($storeCount['store_count']) && !empty($storeCount['store_count']) ? $storeCount['store_count'] : $storeProductCount['store_count'] ?? 0;
            } else {
                $row['store_count'] = 0;
            }

            if (empty($row['store_count'])) {
                $store_type++; //循环购物车门店商品数量
                $row['store_type'] = 1;
            } else {
                $row['store_type'] = 0;
            }
            /* by kong 判断改商品是否存在门店商品 20160725 end */

            //循环购物车分期商品数量
            if ($row['stages_qishu'] != -1) {
                $stages_qishu++;
            }

            if ($row['extension_code'] != 'package_buy') {
                if ($row['model_attr'] == 1) {
                    $prod = $productsWarehouseList[$row['product_id']] ?? [];
                } elseif ($row['model_attr'] == 2) {
                    $prod = $productsAreaList[$row['product_id']] ?? [];
                } else {
                    $prod = $productsList[$row['product_id']] ?? [];
                }

                $goods_attr_id = $prod['goods_attr'] ?? '';
                $goods_attr_id = BaseRepository::getExplode($goods_attr_id, '|');
                $row['goods_thumb'] = app(GoodsAttrService::class)->cartGoodsAttrImage($goods_attr_id, $productsGoodsAttrList, $row['goods_thumb']);

                //当商品没有属性库存时
                if (empty($row['product_id'])) {
                    $attr_number = ($GLOBALS['_CFG']['use_storage'] == 1) ? $goods_number : 1;
                } else {
                    $attr_number = $prod['product_number'] ?? 0;
                }

                //贡云商品 验证库存
                if (isset($row['cloud_id']) && $row['cloud_id'] > 0 && $row['product_id'] > 0) {
                    $attr_number = app(JigonManageService::class)->jigonGoodsNumber(['product_id' => $row['product_id']]);
                }

                $attr_number = !empty($attr_number) ? $attr_number : 0;
                $row['attr_number'] = $attr_number;

                $row['product_number'] = $attr_number;

            } else {
                if ($row['extension_code'] == 'package_buy') {
                    $row['attr_number'] = !app(PackageGoodsService::class)->judgePackageStock($row['goods_id'], $row['goods_number']);
                } else {
                    $row['attr_number'] = $row['goods_number'];
                }

                $row['product_number'] = 0;
            }

            $row['stores_name'] = $offline_store[$row['store_id']]['stores_name'] ?? '';

            //判断是否支持门店自提
            $row['is_chain'] = $row['store_count'] > 0 ? 1 : 0;
            if ($row['is_chain']) {
                $total['store_goods_number'] += 1;
            }

            if ($row['is_checked'] == 1) {
                $cart_value[$row['ru_id']][$key] = $row['rec_id'];
            }

            if ($is_null == 0) {
                $goods_list[] = $row;
            }
        }

        /* 检查是否含有配件 */
        $grouplist = BaseRepository::getKeyPluck($goods_list, 'group_id');
        $grouplist = ArrRepository::getArrayUnset($grouplist);

        if ($grouplist) {
            //过滤商品的配件 配件的数组新增到配件主商品的商品数组中
            $goods_list = app(CartCommonService::class)->cartGoodsGroupList($goods_list);
        }
    } else {
        $cart_value = [];
    }

    $goodsSelf = true;
    if ($isRu > 0) {
        $goodsSelf = false;
    }

    $total['goods_amount'] = $total['goods_price'];

    $total['saving'] = app(DscRepository::class)->getPriceFormat($total['market_price'] - $total['goods_price'], true, true, $goodsSelf);
    if ($total['market_price'] > 0) {
        $total['save_rate'] = $total['market_price'] ? round(($total['market_price'] - $total['goods_price']) * 100 / $total['market_price']) . '%' : 0;
    }
    $total['goods_price'] = app(DscRepository::class)->getPriceFormat($total['goods_price'], true, true, $goodsSelf);
    $total['market_price'] = app(DscRepository::class)->getPriceFormat($total['market_price'], true, true, $goodsSelf);
    $total['real_goods_count'] = $real_goods_count;
    $total['virtual_goods_count'] = $virtual_goods_count;

    if ($type == 1) {
        $goods_list = BaseRepository::getGroupBy($goods_list, 'ru_id');
        $goods_list = get_cart_ru_goods_list($goods_list);
    }

    $total['store_type'] = $store_type;
    $total['stages_qishu'] = $stages_qishu;

    return ['goods_list' => $goods_list, 'total' => $total, 'cart_value' => $cart_value];
}

/**
 * 区分商家商品
 *
 * @param array $goods_list
 * @param string $consignee
 * @param int $store_id
 * @return array
 * @throws Exception
 */
function get_cart_ru_goods_list($goods_list = [], $consignee = '', $store_id = 0)
{
    //配送方式选择
    $point_id = session('flow_consignee.point_id', 0);
    $consignee_district_id = session('flow_consignee.district', 0);

    $cart_goods = ArrRepository::getArrCollapse($goods_list);

    $shippingList = [];
    if ($consignee) {
        $shippingList = app(ShippingService::class)->goodsShippingTransport($cart_goods, $consignee);
    }

    $offline_store = [];
    if ($store_id > 0) {
        $offline_store = OfflineStore::where('id', $store_id);
        $offline_store = BaseRepository::getToArrayFirst($offline_store);

        if ($offline_store) {
            $regionList = RegionDataHandleService::getRegionDataList([$offline_store['province'], $offline_store['city'], $offline_store['district'], $offline_store['street']], ['region_id', 'region_name']);

            $provinceInfo = $regionList[$offline_store['province']] ?? [];
            if ($provinceInfo) {
                $provinceInfo['province'] = $provinceInfo['region_name'];
                unset($provinceInfo['region_name']);
            }

            $cityInfo = $regionList[$offline_store['city']] ?? [];
            if ($cityInfo) {
                $cityInfo['city'] = $cityInfo['region_name'];
                unset($cityInfo['region_name']);
            }

            $districtInfo = $regionList[$offline_store['district']] ?? [];
            if ($districtInfo) {
                $districtInfo['district'] = $districtInfo['region_name'];
                unset($districtInfo['region_name']);
            }

            $streetInfo = $regionList[$offline_store['street']] ?? [];
            if ($streetInfo) {
                $streetInfo['street'] = $streetInfo['region_name'];
                unset($streetInfo['region_name']);
            }

            $offline_store = ArrRepository::getArrCollapse([$offline_store, $provinceInfo, $cityInfo, $districtInfo]);

            if (isset($offline_store['region_id'])) {
                unset($offline_store['region_id']);
            }

            $offline_store['stores_img'] = app(DscRepository::class)->getImagePath($offline_store['stores_img']);
        }
    }

    // 商品活动标签
    $goods_id = BaseRepository::getKeyPluck($cart_goods, 'goods_id');

    $merchantUseGoodsLabelList = GoodsDataHandleService::gettMerchantUseGoodsLabelDataList($goods_id, 1);
    $merchantNoUseGoodsLabelList = GoodsDataHandleService::getMerchantNoUseGoodsLabelDataList($goods_id, 1);

    $arr = [];
    $shipping_type = session()->has('merchants_shipping.shipping_type') ? intval(session()->get('merchants_shipping.shipping_type', 0)) : 0;

    $ru_id = BaseRepository::getArrayKeys($goods_list);
    $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

    foreach ($goods_list as $key => $row) {

        //通过ru_id获取到店铺信息;
        $shop_information = $merchantList[$key] ?? [];
        $ru_name = $shop_information['shop_name'] ?? '';

        $arr[$key]['ru_id'] = $key;
        $arr[$key]['country_icon'] = $shop_information['country_icon'] ?? '';
        $arr[$key]['shipping_type'] = $shipping_type;
        $arr[$key]['ru_name'] = $ru_name;
        $arr[$key]['url'] = app(DscRepository::class)->buildUri('merchants_store', ['urid' => $key], $ru_name);
        $arr[$key]['goods_amount'] = BaseRepository::getArraySum($row, ['goods_price', 'goods_number']);

        $shipping = $shippingList[$key] ?? [];
        $arr[$key]['shipping'] = $shipping['shipping'] ?? [];
        $arr[$key]['is_freight'] = $shipping['is_freight'] ?? 0;
        $arr[$key]['shipping_rec'] = $shipping['shipping_rec'] ?? [];
        $arr[$key]['shipping_count'] = $shipping['shipping_count'] ?? 0;
        $arr[$key]['tmp_shipping_id'] = $shipping['default_shipping']['shipping_id'] ?? 0;

        /*  @author-bylu 判断当前商家是否允许"在线客服" start */
        $arr[$key]['is_im'] = isset($shop_information['is_im']) ? $shop_information['is_im'] : ''; //平台是否允许商家使用"在线客服";
        //判断当前商家是平台,还是入驻商家 bylu
        if ($key == 0) {
            //判断平台是否开启了IM在线客服
            $kf_im_switch = SellerShopinfo::where('ru_id', 0)->value('kf_im_switch');
            if ($kf_im_switch) {
                $arr[$key]['is_dsc'] = true;
            } else {
                $arr[$key]['is_dsc'] = false;
            }
        } else {
            $arr[$key]['is_dsc'] = false;
        }
        /*  @author-bylu  end */

        //自营有自提点--key=ru_id
        if ($key > 0) {
            $basic_info = $shop_information;
        } else {
            $basic_info = SellerShopinfo::select('ru_id', 'kf_type', 'kf_qq', 'kf_ww')
                ->where('ru_id', $key);
            $basic_info = BaseRepository::getToArrayFirst($basic_info);
        }

        $chat = app(DscRepository::class)->chatQq($basic_info);
        $arr[$key]['kf_type'] = $chat['kf_type'];
        $arr[$key]['kf_qq'] = $chat['kf_qq'];
        $arr[$key]['kf_ww'] = $chat['kf_ww'];

        if ($key == 0 && $consignee_district_id > 0) {
            $self_point = app(CartService::class)->getSelfPointCart($consignee_district_id, $point_id, 1);

            if (!empty($self_point)) {
                $arr[$key]['self_point'] = $self_point[0];
            }
        }

        /*获取门店信息 by kong 20160726 start*/
        $arr[$key]['offline_store'] = in_array($key, $offline_store) ? $offline_store : [];
        /*获取门店信息 by kong 20160726 end*/

        if ($row) {
            $shipping_rec = $shipping['shipping_rec'] ?? [];
            foreach ($row as $k => $v) {

                $row[$k]['country_icon'] = $arr[$key]['country_icon'];

                if ($shipping_rec && in_array($v['rec_id'], $shipping_rec)) {
                    $row[$k]['rec_shipping'] = 0; //不支持配送
                } else {
                    $row[$k]['rec_shipping'] = 1; //支持配送
                }

                // 活动标签
                $where = [
                    'user_id' => $key,
                    'goods_id' => $v['goods_id'],
                    'self_run' => $shop_information['self_run'] ?? 0,
                ];
                $goods_label_all = app(GoodsCommonService::class)->getListGoodsLabelList($merchantUseGoodsLabelList, $merchantNoUseGoodsLabelList, $where);

                $row[$k]['goods_label'] = $goods_label_all['goods_label'] ?? [];
                $row[$k]['goods_label_suspension'] = $goods_label_all['goods_label_suspension'] ?? [];
            }
        }

        $arr[$key]['goods_list'] = $row;
    }

    return array_values($arr);
}

/*
 * 查询商家默认配送方式
 */
function get_ru_shippng_info($cart_goods, $cart_value, $ru_id, $consignee = '', $user_id = 0)
{
    if (empty($user_id)) {
        $user_id = session('user_id', 0);
    }

    //分离商家信息by wu start
    $cart_value_arr = [];
    $cart_freight = [];
    $shipping_rec = [];

    $freight = '';
    foreach ($cart_goods as $cgk => $cgv) {
        if ($cgv['ru_id'] != $ru_id) {
            unset($cart_goods[$cgk]);
        } else {
            $cart_value_list = !is_array($cart_value) ? explode(',', $cart_value) : $cart_value;
            if (in_array($cgv['rec_id'], $cart_value_list)) {
                $cart_value_arr[] = $cgv['rec_id'];

                if ($cgv['freight'] == 2) {
                    if (empty($cgv['tid'])) {
                        $shipping_rec[] = $cgv['rec_id'];
                    }

                    @$cart_freight[$cgv['rec_id']][$cgv['freight']] = $cgv['tid'];
                }

                $freight .= $cgv['freight'] . ",";
            }
        }
    }

    if ($freight) {
        $freight = app(DscRepository::class)->delStrComma($freight);
    }

    $is_freight = 0;
    if ($freight) {
        $freight = explode(",", $freight);
        $freight = array_unique($freight);

        /**
         * 判断是否有《地区运费》
         */
        if (in_array(2, $freight)) {
            $is_freight = 1;
        }
    }

    $cart_value = implode(',', $cart_value_arr);
    //分离商家信息by wu end

    $order = flow_order_info($user_id);

    $seller_shipping = get_seller_shipping_type($ru_id);
    $shipping_id = $seller_shipping && isset($seller_shipping['shipping_id']) ? $seller_shipping['shipping_id'] : 0;

    $consignee = session()->has('flow_consignee') ? session('flow_consignee') : $consignee;

    $region = [0, 0, 0, 0, 0];
    if ($consignee) {
        $consignee['country'] = $consignee['country'] ?? 0;
        $consignee['street'] = isset($consignee['street']) ? $consignee['street'] : 0;
        $region = [$consignee['country'], $consignee['province'], $consignee['city'], $consignee['district'], $consignee['street']];
    }

    $insure_disabled = true;
    $cod_disabled = true;

    // 查看购物车中是否全为免运费商品，若是则把运费赋为零
    $shipping_count = Cart::where('extension_code', '<>', 'package_buy')
        ->where('is_shipping', 0)
        ->where('ru_id', $ru_id);

    if (!empty($user_id)) {
        $shipping_count = $shipping_count->where('user_id', $user_id);
    } else {
        $session_id = app(SessionRepository::class)->realCartMacIp();
        $shipping_count = $shipping_count->where('session_id', $session_id);
    }

    if ($cart_value) {
        $cart_value = !is_array($cart_value) ? explode(",", $cart_value) : $cart_value;

        $shipping_count = $shipping_count->whereIn('rec_id', $cart_value);
    }

    $shipping_count = $shipping_count->count();

    $shipping_list = [];

    if ($is_freight) {
        if ($cart_freight) {
            $list1 = [];
            $list2 = [];

            $tid = '';
            foreach ($cart_freight as $key => $row) {
                if (isset($row[2]) && $row[2]) {
                    $tid .= $row[2] . ',';
                }
            }

            $transport_list = [];
            if ($tid) {
                $tid = trim($tid, ',');
                $tid = explode(',', $tid);

                $transport_list = GoodsTransport::whereIn('tid', $tid);
                $transport_list = BaseRepository::getToArrayGet($transport_list);
            }

            if ($transport_list) {
                foreach ($transport_list as $tkey => $trow) {
                    if ($trow['freight_type'] == 1) {
                        $shipping_list1 = Shipping::select('shipping_id', 'shipping_code', 'shipping_name', 'shipping_order')->where('enabled', 1);
                        $shipping_list1 = $shipping_list1->whereHasIn('getGoodsTransportTpl', function ($query) use ($region, $ru_id, $trow) {
                            $query->whereRaw("(FIND_IN_SET('" . $region[1] . "', region_id) OR FIND_IN_SET('" . $region[2] . "', region_id) OR FIND_IN_SET('" . $region[3] . "', region_id) OR FIND_IN_SET('" . $region[4] . "', region_id))")
                                ->where('user_id', $ru_id)
                                ->where('tid', $trow['tid']);
                        });
                        $shipping_list1 = BaseRepository::getToArrayGet($shipping_list1);

                        if (empty($shipping_list1)) {
                            $shipping_rec[] = $key;
                        }

                        $list1[] = $shipping_list1;
                    } else {
                        $shipping_list2 = GoodsTransportExpress::where('tid', $trow['tid'])->where('ru_id', $ru_id);

                        $shipping_list2 = $shipping_list2->whereHasIn('getGoodsTransportExtend', function ($query) use ($ru_id, $trow, $region) {
                            $query->where('ru_id', $ru_id)
                                ->where('tid', $trow['tid'])
                                ->whereRaw("((FIND_IN_SET('" . $region[1] . "', top_area_id)) OR (FIND_IN_SET('" . $region[2] . "', area_id) OR FIND_IN_SET('" . $region[3] . "', area_id) OR FIND_IN_SET('" . $region[4] . "', area_id)))");
                        });

                        $shipping_list2 = BaseRepository::getToArrayGet($shipping_list2);

                        if ($shipping_list2) {
                            $new_shipping = [];
                            foreach ($shipping_list2 as $gtkey => $gtval) {
                                $gt_shipping_id = !is_array($gtval['shipping_id']) ? explode(",", $gtval['shipping_id']) : $gtval['shipping_id'];
                                $new_shipping[] = $gt_shipping_id ? $gt_shipping_id : [];
                            }

                            $new_shipping = BaseRepository::getFlatten($new_shipping);

                            if ($new_shipping) {
                                $shippingInfo = Shipping::select('shipping_id', 'shipping_code', 'shipping_name', 'shipping_order')
                                    ->where('enabled', 1)
                                    ->whereIn('shipping_id', $new_shipping);
                                $list2[] = BaseRepository::getToArrayGet($shippingInfo);
                            }
                        }

                        if (empty($list2)) {
                            $shipping_rec[] = $key;
                        }
                    }
                }
            }

            $shipping_list1 = get_three_to_two_array($list1);
            $shipping_list2 = get_three_to_two_array($list2);

            if ($shipping_list1 && $shipping_list2) {
                $shipping_list = array_merge($shipping_list1, $shipping_list2);
            } elseif ($shipping_list1) {
                $shipping_list = $shipping_list1;
            } elseif ($shipping_list2) {
                $shipping_list = $shipping_list2;
            }

            if ($shipping_list) {
                //去掉重复配送方式 start
                $new_shipping = [];
                foreach ($shipping_list as $key => $val) {
                    @$new_shipping[$val['shipping_code']][] = $key;
                }

                foreach ($new_shipping as $key => $val) {
                    if (count($val) > 1) {
                        for ($i = 1; $i < count($val); $i++) {
                            unset($shipping_list[$val[$i]]);
                        }
                    }
                }
                //去掉重复配送方式 end

                $shipping_list = collect($shipping_list)->sortBy('shipping_order');
                $shipping_list = $shipping_list->values()->all();
            }
        }

        $configure_value = 0;
        $configure_type = 0;

        if ($shipping_list) {
            $str_shipping = '';
            foreach ($shipping_list as $key => $row) {
                if ($row['shipping_id']) {
                    $str_shipping .= $row['shipping_id'] . ",";
                }
            }

            $str_shipping = app(DscRepository::class)->delStrComma($str_shipping);
            $str_shipping = explode(",", $str_shipping);
            if (in_array($shipping_id, $str_shipping)) {
                $have_shipping = 1;
            } else {
                $have_shipping = 0;
            }

            foreach ($shipping_list as $key => $val) {
                if (substr($val['shipping_code'], 0, 5) != 'ship_') {
                    if ($GLOBALS['_CFG']['freight_model'] == 0) {

                        /* 商品单独设置运费价格 start */
                        if ($cart_goods) {
                            if (count($cart_goods) == 1) {
                                $cart_goods = array_values($cart_goods);

                                if (!empty($cart_goods[0]['freight']) && $cart_goods[0]['is_shipping'] == 0) {
                                    if ($cart_goods[0]['freight'] == 1) {
                                        $configure_value = $cart_goods[0]['shipping_fee'] * $cart_goods[0]['goods_number'];
                                    } else {
                                        $trow = get_goods_transport($cart_goods[0]['tid']);

                                        if ($trow['freight_type']) {
                                            $cart_goods[0]['user_id'] = $cart_goods[0]['ru_id'];
                                            $transport_tpl = get_goods_transport_tpl($cart_goods[0], $region, $val, $cart_goods[0]['goods_number']);

                                            $configure_value = isset($transport_tpl['shippingFee']) ? $transport_tpl['shippingFee'] : 0;
                                        } else {

                                            /**
                                             * 商品运费模板
                                             * 自定义
                                             */
                                            $custom_shipping = app(OrderTransportService::class)->getGoodsCustomShipping($cart_goods);

                                            /* 运费模板配送方式 start */
                                            $transport = ['top_area_id', 'area_id', 'tid', 'ru_id', 'sprice'];
                                            $goods_transport = GoodsTransportExtend::select($transport)
                                                ->where('ru_id', $cart_goods[0]['ru_id'])
                                                ->where('tid', $cart_goods[0]['tid']);

                                            $goods_transport = $goods_transport->whereRaw("(FIND_IN_SET('" . $consignee['city'] . "', area_id))");
                                            $goods_transport = BaseRepository::getToArrayFirst($goods_transport);
                                            /* 运费模板配送方式 end */

                                            /* 运费模板配送方式 start */
                                            $ship_transport = ['tid', 'ru_id', 'shipping_fee'];
                                            $goods_ship_transport = GoodsTransportExpress::select($ship_transport)
                                                ->where('ru_id', $cart_goods[0]['ru_id'])
                                                ->where('tid', $cart_goods[0]['tid']);

                                            $goods_ship_transport = $goods_ship_transport->whereRaw("(FIND_IN_SET('" . $val['shipping_id'] . "', shipping_id))");
                                            $goods_ship_transport = BaseRepository::getToArrayFirst($goods_ship_transport);
                                            /* 运费模板配送方式 end */

                                            $goods_transport['sprice'] = isset($goods_transport['sprice']) ? $goods_transport['sprice'] : 0;
                                            $goods_ship_transport['shipping_fee'] = isset($goods_ship_transport['shipping_fee']) ? $goods_ship_transport['shipping_fee'] : 0;

                                            /* 是否免运费 start */
                                            if ($custom_shipping && $custom_shipping[$cart_goods[0]['tid']]['amount'] >= $trow['free_money'] && $trow['free_money'] > 0) {
                                                $is_shipping = 1; /* 免运费 */
                                            } else {
                                                $is_shipping = 0; /* 有运费 */
                                            }
                                            /* 是否免运费 end */

                                            if ($is_shipping == 0) {
                                                if ($trow['type'] == 1) {
                                                    $configure_value = $goods_transport['sprice'] * $cart_goods[0]['goods_number'] + $goods_ship_transport['shipping_fee'] * $cart_goods[0]['goods_number'];
                                                } else {
                                                    $configure_value = $goods_transport['sprice'] + $goods_ship_transport['shipping_fee'];
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    /* 有配送按配送区域计算运费 */
                                    $configure_type = 1;
                                }
                            } else {
                                $order_transpor = app(OrderTransportService::class)->getOrderTransport($cart_goods, $consignee, $val['shipping_id'], $val['shipping_code']);

                                if ($order_transpor['freight']) {
                                    /* 有配送按配送区域计算运费 */
                                    $configure_type = 1;
                                }

                                $configure_value = isset($order_transpor['sprice']) ? $order_transpor['sprice'] : 0;
                            }
                        }
                        /* 商品单独设置运费价格 end */

                        $shipping_fee = $shipping_count == 0 ? 0 : $configure_value;
                        $shipping_list[$key]['free_money'] = app(DscRepository::class)->getPriceFormat(0, false);
                    }

                    // 上门自提免配送费
                    if ($val['shipping_code'] == 'cac') {
                        $shipping_fee = 0;
                    }

                    $shipping_list[$key]['shipping_id'] = $val['shipping_id'];
                    $shipping_list[$key]['shipping_name'] = $val['shipping_name'];
                    $shipping_list[$key]['shipping_code'] = $val['shipping_code'];
                    $shipping_list[$key]['format_shipping_fee'] = app(DscRepository::class)->getPriceFormat($shipping_fee, false);
                    $shipping_list[$key]['shipping_fee'] = $shipping_fee;

                    if (isset($val['insure']) && $val['insure']) {
                        $shipping_list[$key]['insure_formated'] = strpos($val['insure'], '%') === false ? app(DscRepository::class)->getPriceFormat($val['insure'], false) : $val['insure'];
                    }

                    /* 当前的配送方式是否支持保价 */
                    if ($val['shipping_id'] == $order['shipping_id']) {
                        if (isset($val['insure']) && $val['insure']) {
                            $insure_disabled = ($val['insure'] == 0);
                        }
                        if (isset($val['support_cod']) && $val['support_cod']) {
                            $cod_disabled = ($val['support_cod'] == 0);
                        }
                    }

                    //默认配送方式
                    if ($have_shipping == 1) {
                        $shipping_list[$key]['default'] = 0;
                        if ($shipping_id == $val['shipping_id']) {
                            $shipping_list[$key]['default'] = 1;
                        }
                    } else {
                        if ($key == 0) {
                            $shipping_list[$key]['default'] = 1;
                        }
                    }

                    $shipping_list[$key]['insure_disabled'] = $insure_disabled;
                    $shipping_list[$key]['cod_disabled'] = $cod_disabled;
                }

                // 兼容过滤ecjia配送方式
                if (substr($val['shipping_code'], 0, 5) == 'ship_') {
                    unset($shipping_list[$key]);
                }
            }

            //去掉重复配送方式 by wu start
            $shipping_type = [];
            foreach ($shipping_list as $key => $val) {
                @$shipping_type[$val['shipping_code']][] = $key;
            }

            foreach ($shipping_type as $key => $val) {
                if (count($val) > 1) {
                    for ($i = 1; $i < count($val); $i++) {
                        unset($shipping_list[$val[$i]]);
                    }
                }
            }
            //去掉重复配送方式 by wu end
        }
    } else {
        $configure_value = 0;

        /* 商品单独设置运费价格 start */
        if ($cart_goods) {
            if (count($cart_goods) == 1) {
                $cart_goods = array_values($cart_goods);

                if (!empty($cart_goods[0]['freight']) && $cart_goods[0]['is_shipping'] == 0) {
                    $configure_value = $cart_goods[0]['shipping_fee'] * $cart_goods[0]['goods_number'];
                } else {
                    /* 有配送按配送区域计算运费 */
                    $configure_type = 1;
                }
            } else {
                $sprice = 0;
                foreach ($cart_goods as $key => $row) {
                    if ($row['is_shipping'] == 0) {
                        $sprice += $row['shipping_fee'] * $row['goods_number'];
                    }
                }

                $configure_value = $sprice;
            }
        }
        /* 商品单独设置运费价格 end */

        $shipping_fee = $shipping_count == 0 ? 0 : $configure_value;

        // 上门自提免配送费
        if (isset($seller_shipping['shipping_code']) && $seller_shipping['shipping_code'] == 'cac') {
            $shipping_fee = 0;
        }

        $shipping_list[0]['free_money'] = app(DscRepository::class)->getPriceFormat(0, false);
        $shipping_list[0]['format_shipping_fee'] = app(DscRepository::class)->getPriceFormat($shipping_fee, false);
        $shipping_list[0]['shipping_fee'] = $shipping_fee;
        $shipping_list[0]['shipping_id'] = isset($seller_shipping['shipping_id']) && !empty($seller_shipping['shipping_id']) ? $seller_shipping['shipping_id'] : 0;
        $shipping_list[0]['shipping_name'] = isset($seller_shipping['shipping_name']) && !empty($seller_shipping['shipping_name']) ? $seller_shipping['shipping_name'] : '';
        $shipping_list[0]['shipping_code'] = isset($seller_shipping['shipping_code']) && !empty($seller_shipping['shipping_code']) ? $seller_shipping['shipping_code'] : '';
        $shipping_list[0]['default'] = 1;
    }

    $arr = ['is_freight' => $is_freight, 'shipping_list' => $shipping_list, 'shipping_rec' => $shipping_rec];

    return $arr;
}

/**
 * 返回固定运费价格
 */
function get_configure_order($configure, $value = 0, $type = 0)
{
    if ($configure) {
        foreach ($configure as $key => $val) {
            if ($val['name'] === 'base_fee') {
                if ($type == 1) {
                    $configure[$key]['value'] += $value;
                } else {
                    $configure[$key]['value'] = $value;
                }
            }
        }
    }

    return $configure;
}

/**
 * 获得上一次用户采用的支付和配送方式
 *
 * @return array
 */
function last_shipping_and_payment()
{
    $user_id = session('user_id', 0);

    $OrderRep = app(OrderService::class);

    $where = [
        'user_id' => $user_id
    ];
    $row = $OrderRep->getOrderInfo($where);

    if (empty($row)) {
        /* 如果获得是一个空数组，则返回默认值 */
        $row = ['shipping_id' => 0, 'pay_id' => 0];
    }

    return $row;
}

/**
 * 处理红包（下订单时设为使用，取消（无效，退货）订单时设为未使用
 *
 * @param int $bonus_id 红包编号
 * @param int $order_id 订单号
 * @param bool $is_used 是否使用了
 */
function change_user_bonus($bonus_id = 0, $order_id = 0, $is_used = true)
{
    if ($is_used) {
        $other = [
            'used_time' => gmtime(),
            'order_id' => $order_id
        ];
    } else {
        $other = [
            'used_time' => 0,
            'order_id' => 0
        ];
    }

    UserBonus::where('bonus_id', $bonus_id)->update($other);
}

/**
 * 获得订单信息
 *
 * @param int $user_id
 * @param array $flow_order
 * @return array|\Illuminate\Session\SessionManager|\Illuminate\Session\Store|mixed
 */
function flow_order_info($user_id = 0, $flow_order = [])
{
    $session_flow_order = session()->has('flow_order') ? session('flow_order') : [];
    $order = empty($flow_order) ? $session_flow_order : $flow_order;

    /* 初始化配送和支付方式 */
    if (!isset($order['shipping_id']) || !isset($order['pay_id'])) {
        /* 如果还没有设置配送和支付 */
        if ($user_id > 0) {
            /* 用户已经登录了，则获得上次使用的配送和支付 */
            $orderInfo = OrderInfo::select('order_id', 'shipping_id', 'pay_id')->where('user_id', $user_id);
            $orderInfo = BaseRepository::getToArrayFirst($orderInfo);

            if (!isset($order['shipping_id'])) {
                $order['shipping_id'] = $orderInfo['shipping_id'] ?? 0;
            }
            if (!isset($order['pay_id'])) {
                $order['pay_id'] = $orderInfo['pay_id'] ?? 0;
            }
        } else {
            if (!isset($order['shipping_id'])) {
                $order['shipping_id'] = 0;
            }
            if (!isset($order['pay_id'])) {
                $order['pay_id'] = 0;
            }
        }
    }

    if (!isset($order['pack_id'])) {
        $order['pack_id'] = 0;  // 初始化包装
    }
    if (!isset($order['card_id'])) {
        $order['card_id'] = 0;  // 初始化贺卡
    }
    if (!isset($order['bonus'])) {
        $order['bonus'] = 0;    // 初始化红包
    }
    if (!isset($order['value_card'])) {
        $order['value_card'] = 0;    // 初始化储值卡
    }
    if (!isset($order['coupons'])) {
        $order['coupons'] = 0;    // 初始化优惠券 bylu
    }
    if (!isset($order['integral'])) {
        $order['integral'] = 0; // 初始化积分
    }
    if (!isset($order['surplus'])) {
        $order['surplus'] = 0;  // 初始化余额
    }

    /* 扩展信息 */
    if (session('flow_type') != CART_GENERAL_GOODS) {
        $order['extension_code'] = session('extension_code', '');
        $order['extension_id'] = session('extension_id', 0);
    } else {
        $order['extension_code'] = $order['extension_code'] ?? '';
        $order['extension_id'] = $order['extension_id'] ?? 0;
    }

    return $order;
}

/**
 * 合并订单
 *
 * @param array $from_order_sn_arr 从订单号
 * @param string $to_order_sn 主订单号
 * @return bool
 */
function merge_order($from_order_sn_arr = [], $to_order_sn = '')
{
    /* 订单号不能为空 */
    if (empty($from_order_sn_arr) || trim($to_order_sn) == '') {
        return $GLOBALS['_LANG']['order_sn_not_null'];
    }

    /* 订单号不能相同 */
    if (in_array($to_order_sn, $from_order_sn_arr)) {
        return $GLOBALS['_LANG']['two_order_sn_same'];
    }

    $order_id_arr = [];
    $order = $to_order = order_info(0, $to_order_sn);

    foreach ($from_order_sn_arr as $key => $from_order_sn) {
        /* 查询订单商家ID */
        $from_order_seller = get_order_seller_id($from_order_sn, 1);
        $to_order_seller = get_order_seller_id($to_order_sn, 1);

        if (empty($from_order_seller) || empty($to_order_seller) || ($from_order_seller['ru_id'] != $to_order_seller['ru_id'])) {
            return $GLOBALS['_LANG']['seller_order_sn_same'];
        }

        /* 取得订单信息 */
        $from_order = order_info(0, $from_order_sn);

        /* 检查订单是否存在 */
        if (!$from_order) {
            return sprintf($GLOBALS['_LANG']['order_not_exist'], $from_order_sn);
        }

        /* 检查合并的订单是否为普通订单，非普通订单不允许合并 */
        if ($from_order['extension_code'] != '' || $order['extension_code'] != 0) {
            return $GLOBALS['_LANG']['merge_invalid_order'];
        }

        /* 检查订单状态是否是已确认或未确认、未付款、未发货 */
        if ($from_order['order_status'] != OS_CONFIRMED) {
            return sprintf($GLOBALS['_LANG']['os_not_unconfirmed_or_confirmed'], $from_order_sn);
        } elseif ($from_order['pay_status'] != $order['pay_status']) {
            return $GLOBALS['_LANG']['ps_not_same'];
        } elseif ($from_order['shipping_status'] != SS_UNSHIPPED) {
            return sprintf($GLOBALS['_LANG']['ss_not_unshipped'], $from_order_sn);
        }

        /* 检查订单用户是否相同 */
        if ($from_order['user_id'] != $order['user_id']) {
            return $GLOBALS['_LANG']['order_user_not_same'];
        }

        /* 合并订单 */

        $order['order_id'] = '';
        $order['add_time'] = TimeRepository::getGmTime();

        // 合并商品总额
        $order['goods_amount'] += $from_order['goods_amount'];

        // 合并折扣
        $order['discount'] += $from_order['discount'];

        if ($order['shipping_id'] > 0) {
            $shipping_area = shipping_info($order['shipping_id']);
            $shipping_area['configure'] = !empty($shipping_area['configure']) ? unserialize($shipping_area['configure']) : '';
            $order['shipping_fee'] += $from_order['shipping_fee'];

            // 如果保价了，重新计算保价费
            if ($order['insure_fee'] > 0) {
                $order['insure_fee'] += $from_order['insure_fee'];
            }
        }

        // 重新计算包装费、贺卡费
        if ($order['pack_id'] > 0) {
            $pack = pack_info($order['pack_id']);
            $order['pack_fee'] = $pack['free_money'] > $order['goods_amount'] ? $pack['pack_fee'] : 0;
        }
        if ($order['card_id'] > 0) {
            $card = card_info($order['card_id']);
            $order['card_fee'] = $card['free_money'] > $order['goods_amount'] ? $card['card_fee'] : 0;
        }

        // 红包不变，合并积分、余额、已付款金额
        $order['integral'] += $from_order['integral'];
        $order['integral_money'] = app(DscRepository::class)->valueOfIntegral($order['integral']);
        $order['surplus'] += $from_order['surplus'];
        $order['money_paid'] += $from_order['money_paid'];

        // 计算应付款金额（不包括支付费用）
        $order['order_amount'] = $order['goods_amount'] - $order['discount']
            + $order['shipping_fee']
            + $order['insure_fee']
            + $order['pack_fee']
            + $order['card_fee']
            - $order['bonus']
            - $order['integral_money']
            - $order['surplus']
            - $order['money_paid'];

        // 重新计算支付费
        if ($order['pay_id'] > 0) {
            // 货到付款手续费
            $cod_fee = !empty($shipping_area) ? $shipping_area['pay_fee'] : 0;
            $order['pay_fee'] = pay_fee($order['pay_id'], $order['order_amount'], $cod_fee);

            // 应付款金额加上支付费
            $order['order_amount'] += $order['pay_fee'];
        }

        /* 返还 from_order 的红包，因为只使用 to_order 的红包 */
        if ($from_order['bonus_id'] > 0) {
            app(FlowOrderService::class)->unuseBonus($from_order['bonus_id']);
        }
        array_push($order_id_arr, $from_order['order_id'], $to_order['order_id']);
    }

    $order_id_arr = array_unique($order_id_arr);

    /* 插入订单表 */
    $order['order_sn'] = get_order_sn();
    $order = BaseRepository::getArrayfilterTable($order, 'order_info');

    $order_id = OrderInfo::insertGetId(addslashes_deep($order));

    if (!$order_id) {
        return false;
    }

    /* 更新订单商品 */
    OrderGoods::whereIn('order_id', $order_id_arr)->update(['order_id' => $order_id]);

    load_helper('clips');

    /* 插入支付日志 */
    insert_pay_log($order_id, $order['order_amount'], PAY_ORDER);

    /* 删除原订单 */
    OrderInfo::whereIn('order_id', $order_id_arr)->delete();

    /* 删除原订单支付日志 */
    PayLog::whereIn('order_id', $order_id_arr)->delete();

    /* 返回成功 */
    return true;
}

/**
 * 查询配送区域属于哪个办事处管辖
 * @param array $regions 配送区域（1、2、3、4级按顺序）
 * @return  int     办事处id，可能为0
 */
function get_agency_by_regions($regions)
{
    if (!is_array($regions) || empty($regions)) {
        return 0;
    }

    $regions = BaseRepository::getExplode($regions);

    $res = Region::whereIn('region_id', $regions)
        ->where('region_id', '>', 0)
        ->where('agency_id', '>', 0);
    $res = BaseRepository::getToArrayGet($res);

    $arr = [];
    if ($res) {
        foreach ($res as $row) {
            $arr[$row['region_id']] = $row['agency_id'];
        }
    }

    if (empty($arr)) {
        return 0;
    }

    $agency_id = 0;
    for ($i = count($regions) - 1; $i >= 0; $i--) {
        if (isset($arr[$regions[$i]])) {
            return $arr[$regions[$i]];
        }
    }
}

/**
 * 获取配送插件的实例
 * @param int $shipping_id 配送插件ID
 * @return  object     配送插件对象实例
 */
function get_shipping_object($shipping_id)
{
    $shipping = shipping_info($shipping_id);
    if (!$shipping) {
        return false;
    }

    if ($shipping['shipping_code']) {
        // 过滤ecjia配送方式
        if (substr($shipping['shipping_code'], 0, 5) == 'ship_') {
            $shipping['shipping_code'] = str_replace('ship_', '', $shipping['shipping_code']);
        }

        $shipping_name = StrRepository::studly($shipping['shipping_code']);
        $shipping = '\\App\\Plugins\\Shipping\\' . $shipping_name . '\\' . $shipping_name;

        if (class_exists($shipping)) {
            return app($shipping, []);
        } else {
            return false;
        }
    }
}

/**
 * 改变订单中商品库存
 * @param int $order_id 订单号
 * @param bool $is_dec 是否减少库存
 * @param int $storage 减库存的时机，2，付款时； 1，下订单时；0，发货时；
 * @param int $use_storage 出库（0,1）、入库(2,3,5)
 * @param int $admin_id 管理员id
 * @param int $store_id 门店id
 * @return mixed
 */
function change_order_goods_storage($order_id = 0, $is_dec = true, $storage = 0, $use_storage = 0, $admin_id = 0, $store_id = 0)
{
    return \App\Repositories\Order\OrderRepository::change_order_goods_storage($order_id, $is_dec, $storage, $use_storage, $admin_id, $store_id);
}

/**
 * 商品库存增与减 货品库存增与减
 *
 * @param int $goods_id 商品ID
 * @param int $product_id 货品ID
 * @param int $number 增减数量，默认0；
 * @param int $warehouse_id
 * @param int $area_id
 * @param int $area_city
 * @param int $order_id
 * @param int $use_storage
 * @param int $admin_id
 * @param int $store_id
 * @return bool
 */
function change_goods_storage($goods_id = 0, $product_id = 0, $number = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0, $order_id = 0, $use_storage = 0, $admin_id = 0, $store_id = 0)
{
    return \App\Repositories\Order\OrderRepository::change_goods_storage($goods_id, $product_id, $number, $warehouse_id, $area_id, $area_city, $order_id, $use_storage, $admin_id, $store_id);
}

/**
 * 生成查询订单的sql
 * @param string $type 类型
 * @param string $alias order表的别名（包括.例如 o.）
 * @return  string
 */
function order_take_query_sql($type = 'finished', $alias = '')
{
    /* 已完成订单 */
    if ($type == 'finished') {
        return " AND {$alias}order_status " . db_create_in([OS_SPLITED]) .
            " AND {$alias}shipping_status " . db_create_in([SS_RECEIVED]) .
            " AND {$alias}pay_status " . db_create_in([PS_PAYED]) . " ";
    } else {
        return '函数 order_query_sql 参数错误';
    }
}

/**
 * 生成查询佣金总金额的字段
 * @param string $alias order表的别名（包括.例如 o.）
 * @return  string
 *  + {$alias}shipping_fee  不含运费
 */
function order_commission_field($alias = '')
{
    return "   {$alias}goods_amount + {$alias}tax" .
        " + {$alias}insure_fee + {$alias}pay_fee + {$alias}pack_fee" .
        " + {$alias}card_fee -{$alias}discount -{$alias}coupons - {$alias}integral_money - {$alias}bonus - {$alias}vc_dis_money ";
}

/**
 * 生成计算应付款金额的字段
 * @param string $alias order表的别名（包括.例如 o.）
 * @return  string
 */
function order_due_field($alias = '')
{
    return OrderService::orderAmountField($alias) .
        " - {$alias}money_paid - {$alias}surplus - {$alias}integral_money" .
        " - {$alias}bonus - {$alias}coupons - {$alias}discount - {$alias}vc_dis_money ";
}

/**
 * 生成计算应付款金额的字段
 * @param string $alias order表的别名（包括.例如 o.）
 * @return  string
 */
function order_activity_field_add($alias = '')
{
    return " {$alias}discount + {$alias}coupons + {$alias}integral_money + {$alias}bonus + {$alias}vc_dis_money ";
}

/**
 * 计算折扣：根据购物车和优惠活动
 *
 * @param int $type 0-默认 1-分单
 * @param array $newInfo
 * @param int $use_type 购物流程显示 0， 分单使用 1
 * @param int $ru_id
 * @param int $user_id
 * @param int $user_rank
 * @param int $rec_type
 * @return array
 */
function compute_discount($type = 0, $newInfo = [], $use_type = 0, $ru_id = 0, $user_id = 0, $user_rank = 0, $rec_type = CART_GENERAL_GOODS)
{
    if (empty($user_id)) {
        $user_id = session('user_id', 0);
    }

    $session_id = app(SessionRepository::class)->realCartMacIp();

    $CategoryLib = app(CategoryService::class);
    if (empty($user_rank)) {
        $user_rank = session('user_rank', 1);
    }

    /* 查询优惠活动 */
    $now = TimeRepository::getGmTime();
    $user_rank = ',' . $user_rank . ',';
    $favourable_list = FavourableActivity::where('review_status', 3)
        ->where('start_time', '<=', $now)
        ->where('end_time', '>=', $now)
        ->whereIn('act_type', [FAT_DISCOUNT, FAT_PRICE]);

    $favourable_list = $favourable_list->whereRaw("CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'");

    $favourable_list = BaseRepository::getToArrayGet($favourable_list);

    if (!$favourable_list) {
        return ['discount' => 0, 'name' => ''];
    }

    $goods_list = [];
    if ($type == 0 || $type == 3) {

        /* 查询购物车商品 */
        $goods_list = Cart::selectRaw("goods_id, goods_price * goods_number AS subtotal, ru_id, act_id")
            ->where('parent_id', 0)
            ->where('is_gift', 0)
            ->where('rec_type', $rec_type);

        $rec_ids = BaseRepository::getExplode($newInfo);

        if ($rec_ids) {
            $goods_list = $goods_list->whereIn('rec_id', $rec_ids);
        }

        if (!empty($user_id)) {
            $goods_list = $goods_list->where('user_id', $user_id);
        } else {
            $goods_list = $goods_list->where('session_id', $session_id);
        }

        if ($type == 3) {
            $goods_list = $goods_list->where('is_checked', 1);
        }

        $goods_list = BaseRepository::getToArrayGet($goods_list);

        if ($goods_list) {
            $goods_id = BaseRepository::getKeyPluck($goods_list, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'cat_id', 'brand_id']);

            /* 重新获取goods_id */
            $goods_id = BaseRepository::getKeyPluck($goodsList, 'goods_id');

            if ($goods_id) {
                $sql = [
                    'whereIn' => [
                        [
                            'name' => 'goods_id',
                            'value' => $goods_id
                        ]
                    ]
                ];
                $goods_list = BaseRepository::getArraySqlGet($goods_list, $sql);

                if ($goods_list) {
                    foreach ($goods_list as $key => $row) {

                        $goods = $goodsList[$row['goods_id']] ?? [];

                        $row['cat_id'] = $goods['cat_id'] ?? 0;
                        $row['brand_id'] = $goods['brand_id'] ?? 0;

                        $goods_list[$key] = $row;
                    }
                }
            } else {
                $goods_list = [];
            }
        }
    } elseif ($type == 2) {

        if ($newInfo && is_array($newInfo)) {

            $goods_id = BaseRepository::getKeyPluck($newInfo, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'cat_id', 'brand_id']);

            foreach ($newInfo as $key => $row) {

                $goods = $goodsList[$row['goods_id']] ?? [];
                $goods_list[$key]['cat_id'] = $goods['cat_id'] ?? 0;
                $goods_list[$key]['brand_id'] = $goods['brand_id'] ?? 0;

                $goods_list[$key]['goods_id'] = $row['goods_id'];
                $goods_list[$key]['ru_id'] = $row['ru_id'];
                $goods_list[$key]['subtotal'] = $row['goods_price'] * $row['goods_number'];
            }
        }
    }

    if (empty($goods_list)) {
        return ['discount' => 0, 'name' => ''];
    }

    /* 初始化折扣 */
    $discount = 0;
    $favourable_name = [];
    $list_array = [];

    /* 循环计算每个优惠活动的折扣 */
    foreach ($favourable_list as $favourable) {
        $total_amount = 0;
        if ($favourable['act_range'] == FAR_ALL) {
            foreach ($goods_list as $goods) {
                if (isset($goods['act_id'])) {
                    if ($goods['act_id'] == $favourable['act_id']) {//购物车匹配促销活动
                        if ($use_type == 1) {
                            if ($favourable['user_id'] == $goods['ru_id']) {
                                $total_amount += $goods['subtotal'];
                            }
                        } else {
                            if ($favourable['userFav_type'] == 1) {
                                $total_amount += $goods['subtotal'];
                            } else {
                                if ($favourable['user_id'] == $goods['ru_id']) {
                                    $total_amount += $goods['subtotal'];
                                }
                            }
                        }
                    }
                }
            }
        } elseif ($favourable['act_range'] == FAR_CATEGORY) {
            /* 找出分类id的子分类id */
            $raw_id_list = explode(',', $favourable['act_range_ext']);

            $str_cat = '';
            foreach ($raw_id_list as $id) {
                /**
                 * 当前分类下的所有子分类
                 * 返回一维数组
                 */
                $cat_keys = $CategoryLib->getCatListChildren(intval($id));

                if ($cat_keys) {
                    $str_cat .= implode(",", $cat_keys);
                }
            }

            if ($str_cat) {
                $list_array = explode(",", $str_cat);
            }

            $list_array = !empty($list_array) ? array_merge($raw_id_list, $list_array) : $raw_id_list;
            $id_list = arr_foreach($list_array);
            $id_list = array_unique($id_list);

            $ids = join(',', array_unique($id_list));

            foreach ($goods_list as $goods) {
                //购物车匹配促销活动
                if (isset($goods['act_id']) && $goods['act_id'] == $favourable['act_id']) {
                    if (strpos(',' . $ids . ',', ',' . $goods['cat_id'] . ',') !== false) {
                        if ($use_type == 1) {
                            if ($favourable['user_id'] == $goods['ru_id'] && $favourable['userFav_type'] == 0) {
                                $total_amount += $goods['subtotal'];
                            }
                        } else {
                            if ($favourable['userFav_type'] == 1) {
                                $total_amount += $goods['subtotal'];
                            } else {
                                if ($favourable['user_id'] == $goods['ru_id']) {
                                    $total_amount += $goods['subtotal'];
                                }
                            }
                        }
                    }
                }
            }
        } elseif ($favourable['act_range'] == FAR_BRAND) {
            $favourable['act_range_ext'] = act_range_ext_brand($favourable['act_range_ext'], $favourable['userFav_type'], $favourable['act_range']);
            foreach ($goods_list as $goods) {
                //购物车匹配促销活动
                if (isset($goods['act_id']) && $goods['act_id'] == $favourable['act_id']) {
                    if (strpos(',' . $favourable['act_range_ext'] . ',', ',' . $goods['brand_id'] . ',') !== false) {
                        if ($use_type == 1) {
                            if ($favourable['user_id'] == $goods['ru_id']) {
                                $total_amount += $goods['subtotal'];
                            }
                        } else {
                            if ($favourable['userFav_type'] == 1) {
                                $total_amount += $goods['subtotal'];
                            } else {
                                if ($favourable['user_id'] == $goods['ru_id']) {
                                    $total_amount += $goods['subtotal'];
                                }
                            }
                        }
                    }
                }
            }
        } elseif ($favourable['act_range'] == FAR_GOODS) {
            foreach ($goods_list as $goods) {
                //购物车匹配促销活动
                if (isset($goods['act_id']) && isset($favourable['act_id']) && $goods['act_id'] == $favourable['act_id']) {
                    if (strpos(',' . $favourable['act_range_ext'] . ',', ',' . $goods['goods_id'] . ',') !== false) {
                        if ($use_type == 1) {
                            if ($favourable['user_id'] == $goods['ru_id']) {
                                $total_amount += $goods['subtotal'];
                            }
                        } else {
                            if ($favourable['userFav_type'] == 1) {
                                $total_amount += $goods['subtotal'];
                            } else {
                                if ($favourable['user_id'] == $goods['ru_id']) {
                                    $total_amount += $goods['subtotal'];
                                }
                            }
                        }
                    }
                }
            }
        } else {
            continue;
        }

        /* 如果金额满足条件，累计折扣 */
        if ($total_amount > 0 && $total_amount >= $favourable['min_amount'] && ($total_amount <= $favourable['max_amount'] || $favourable['max_amount'] == 0)) {
            if ($favourable['act_type'] == FAT_DISCOUNT) {
                $discount += $total_amount * (1 - $favourable['act_type_ext'] / 100);

                $favourable_name[] = $favourable['act_name'];
            } elseif ($favourable['act_type'] == FAT_PRICE) {
                $discount += $favourable['act_type_ext'];
                $favourable_name[] = $favourable['act_name'];
            }

            if ($rec_type == CART_ONESTEP_GOODS) {
                return ['discount' => $discount, 'name' => $favourable_name, 'favourable' => $favourable];
            }
        }
    }

    return ['discount' => $discount, 'name' => $favourable_name];
}

/**
 * 取得购物车该赠送的积分数
 *
 * @param string $cart_value
 * @param int $user_id
 * @return int
 */
function get_give_integral($cart_value = '', $user_id = 0)
{
    $res = Cart::select('goods_id', 'goods_number', 'goods_price', 'warehouse_id', 'area_id', 'area_city')
        ->where('goods_id', '>', 0)
        ->where('parent_id', 0)
        ->where('rec_type', CART_GENERAL_GOODS)
        ->where('is_gift', 0);

    if (!empty($user_id)) {
        $res = $res->where('user_id', $user_id);
    } else {
        $session_id = app(SessionRepository::class)->realCartMacIp();
        $res = $res->where('session_id', $session_id);
    }

    if (!empty($cart_value)) {
        $cart_value = !is_array($cart_value) ? explode(",", $cart_value) : $cart_value;
        $res = $res->whereIn('rec_id', $cart_value);
    }

    $res = $res->with([
        'getGoods' => function ($query) {
            $query->select('goods_id', 'model_price', 'give_integral');
        }
    ]);

    $res = BaseRepository::getToArrayGet($res);

    $price = 0;
    if ($res) {
        foreach ($res as $key => $row) {
            $warehouse_integral = WarehouseGoods::where('goods_id', $row['goods_id'])
                ->where('region_id', $row['warehouse_id'])
                ->value('give_integral');
            $warehouse_integral = $warehouse_integral ? $warehouse_integral : 0;

            $area_integral = WarehouseAreaGoods::where('goods_id', $row['goods_id'])
                ->where('region_id', $row['area_id']);

            if ($GLOBALS['_CFG']['area_pricetype'] == 1) {
                $area_integral = $area_integral->where('city_id', $row['area_city']);
            }

            $area_integral = $area_integral->value('give_integral');

            $area_integral = $area_integral ? $area_integral : 0;

            $model_price = $row['get_goods'] ? $row['get_goods']['model_price'] : 0;
            $goods_integral = $row['get_goods'] ? $row['get_goods']['give_integral'] : 0;

            if ($model_price == 1) {
                $integral = $warehouse_integral;
            } elseif ($model_price == 2) {
                $integral = $area_integral;
            } else {
                $integral = $goods_integral;
            }

            if ($integral <= -1) {
                $integral = $row['goods_price'];
            }

            $price += $integral;
        }
    }

    return $price;
}

/**
 * 取得某订单应该赠送的积分数
 * @param array $order 订单
 * @param int $rec_id
 * @return array  积分数
 */
function integral_to_give($order = [], $rec_id = 0)
{
    /* 判断是否团购 */
    if (isset($order['extension_code']) && $order['extension_code'] == 'group_buy') {
        $group_buy_id = intval($order['extension_id']);
        /* 取得团购活动信息 */
        $group_buy = GoodsActivity::where('act_type', GAT_GROUP_BUY)
            ->where('act_id', $group_buy_id);
        $group_buy = BaseRepository::getToArrayFirst($group_buy);

        if ($group_buy) {
            $ext_info = unserialize($group_buy['ext_info']);
            $group_buy = array_merge($group_buy, $ext_info);
        }

        $gift_integral = $group_buy['gift_integral'] ?? 0;

        return ['custom_points' => $gift_integral, 'rank_points' => $order['goods_amount']];
    } else {
        $res = OrderGoods::where('order_id', $order['order_id'])
            ->where('goods_id', '>', 0)
            ->where('parent_id', 0)
            ->where('is_gift', 0)
            ->where('extension_code', '<>', 'package_buy');

        if ($rec_id > 0) {
            $res = $res->where('rec_id', $rec_id);
        }

        $res = $res->with(['getGoods']);

        $res = BaseRepository::getToArrayGet($res);

        $custom_points = 0;
        $rank_points = 0;
        if ($res) {
            foreach ($res as $key => $row) {
                $goods = $row['get_goods'];

                if ($row['ru_id'] > 0) {
                    $grade = MerchantsGrade::query()->whereHasIn('getSellerGrade')
                        ->where('ru_id', $row['ru_id'])
                        ->with([
                            'getSellerGrade' => function ($query) {
                                $query->select('id', 'give_integral', 'rank_integral');
                            }
                        ]);

                    $grade = $grade->select('id', 'grade_id');

                    $grade = BaseRepository::getToArrayFirst($grade);

                    $give = $grade && $grade['get_seller_grade'] ? $grade['get_seller_grade']['give_integral'] / 100 : 0;
                    $rank = $grade && $grade['get_seller_grade'] ? $grade['get_seller_grade']['rank_integral'] / 100 : 0;
                } else {
                    $give = 1;
                    $rank = 1;
                }

                $give_integral = 0;
                $rank_integral = 0;
                if ($goods) {
                    if ($goods['model_price'] == 1) {
                        $res = WarehouseGoods::where('goods_id', $row['goods_id'])->where('region_id', $row['warehouse_id']);
                        $res = BaseRepository::getToArrayFirst($res);

                        $give_integral = $res ? $res['give_integral'] : 0;
                        $rank_integral = $res ? $res['rank_integral'] : 0;
                    } elseif ($goods['model_price'] == 2) {
                        $res = WarehouseAreaGoods::where('goods_id', $row['goods_id'])->where('region_id', $row['area_id']);
                        $res = BaseRepository::getToArrayFirst($res);

                        $give_integral = $res ? $res['give_integral'] : 0;
                        $rank_integral = $res ? $res['rank_integral'] : 0;
                    } else {
                        $give_integral = $goods['give_integral'];
                        $rank_integral = $goods['rank_integral'];
                    }
                }

                if ($give_integral > 0) {
                    $row['custom_points'] = $row['goods_number'] * $give_integral;
                } elseif ($give_integral == -1) {
                    $row['custom_points'] = $row['goods_number'] * ($row['goods_price'] * $give);
                }

                if ($rank_integral > 0) {
                    $row['rank_points'] = $row['goods_number'] * $rank_integral;
                } elseif ($rank_integral == -1) {
                    $row['rank_points'] = $row['goods_number'] * ($row['goods_price'] * $rank);
                }

                $custom_points += $row['custom_points'] ?? 0;
                $rank_points += $row['rank_points'] ?? 0;
            }
        }

        $custom_points = $custom_points ? intval($custom_points) : 0;
        $rank_points = $rank_points ? intval($rank_points) : 0;

        $arr = [
            'custom_points' => $custom_points,
            'rank_points' => $rank_points
        ];

        return $arr;
    }
}

/**
 *  发红包：发货时发红包发红包：发货时发红包
 *
 * @param int $order_id 订单ID
 * @return bool
 * @throws Exception
 */
function send_order_bonus($order_id = 0)
{
    /* 取得订单应该发放的红包 */
    $bonus_list = order_bonus($order_id);
    /* 如果有红包，统计并发送 */
    if ($bonus_list) {
        /* 用户信息 */
        $user_id = OrderInfo::where('order_id', $order_id)->value('user_id');
        $user_id = $user_id ? $user_id : 0;

        $user = [];
        if ($user_id) {
            $user = Users::select('user_id', 'user_name', 'email')->where('user_id', $user_id);
            $user = BaseRepository::getToArrayFirst($user);
        }

        /* 统计 */
        $count = 0;
        $money = '';
        foreach ($bonus_list as $bonus) {
            //优化一个订单只能发一个红包
            if ($bonus['number']) {
                $count = 1;
                $bonus['number'] = 1;
            }
            $money .= strip_tags(app(DscRepository::class)->getPriceFormat($bonus['type_money'])) . ', ';


            $bonus_info = BonusType::where('type_id', $bonus['type_id']);
            $bonus_info = BaseRepository::getToArrayFirst($bonus_info);

            if (empty($bonus_info)) {
                $bonus_info = [
                    'date_type' => 0,
                    'valid_period' => 0,
                    'use_start_date' => '',
                    'use_end_date' => '',
                ];
            }

            /* 修改用户红包 */
            $other = [
                'bonus_type_id' => $bonus['type_id'],
                'user_id' => $user_id,
                'bind_time' => gmtime(),
                'date_type' => $bonus_info['date_type'],
                'return_order_id' => $order_id,
                'return_goods_id' => $bonus['goods_id'] ?? 0,
            ];
            if ($bonus_info['valid_period'] > 0) {
                $other['start_time'] = $other['bind_time'];
                $other['end_time'] = $other['bind_time'] + $bonus_info['valid_period'] * 3600 * 24;
            } else {
                $other['start_time'] = $bonus_info['use_start_date'];
                $other['end_time'] = $bonus_info['use_end_date'];
            }

            $bonus_id = 0;
            if ($user_id > 0) {
                $bonus_id = UserBonus::insertGetId($other);
            }

            for ($i = 0; $i < $bonus['number']; $i++) {
                if (!$bonus_id) {
                    return $GLOBALS['db']->errorMsg();
                }
            }
        }

        /* 如果有红包，发送邮件 */
        if ($count > 0) {
            $user_name = $user['user_name'] ?? '';
            $email = $user['email'] ?? '';

            $template = app(\App\Libraries\Template::class);

            $tpl = get_mail_template('send_bonus');
            $template->assign('user_name', $user_name);
            $template->assign('count', $count);
            $template->assign('money', $money);
            $template->assign('shop_name', config('shop.shop_name'));
            $template->assign('send_date', TimeRepository::getLocalDate(config('shop.date_format')));
            $template->assign('sent_date', TimeRepository::getLocalDate(config('shop.date_format')));
            $content = $template->fetch('str:' . $tpl['template_content']);
            CommonRepository::sendEmail($user_name, $email, $tpl['template_subject'], $content, $tpl['is_html']);
        }
    }

    return true;
}

/**
 * [优惠券发放 (发货的时候)]达到条件的的订单,反购物券
 *
 * @param int $order_id
 * @param int $user_id
 */
function send_order_coupons($order_id = 0, $user_id = 0)
{
    $order_id = (int)$order_id;
    $order = OrderInfo::select('order_id', 'user_id', 'goods_amount')
        ->where('order_id', $order_id);

    if ($user_id > 0) {
        $order = $order->where('user_id', $user_id);
    }

    $order = BaseRepository::getToArrayFirst($order);

    //获优惠券信息
    $coupons_buy_info = app(CouponsService::class)->getCouponsTypeInfoNoPage(2);

    //获取会员等级id
    $user_rank_info = app(UserRankService::class)->getUserRankInfo($order['user_id']);
    $user_rank = $user_rank_info['user_rank'] ?? 0;

    if ($coupons_buy_info) {
        foreach ($coupons_buy_info as $k => $v) {

            //判断当前会员等级能不能领取
            $cou_ok_user = !empty($v['cou_ok_user']) ? explode(",", $v['cou_ok_user']) : '';

            if ($cou_ok_user) {
                if (!in_array($user_rank, $cou_ok_user)) {
                    continue;
                }
            } else {
                continue;
            }

            //获取当前的注册券已被发放的数量(防止发放数量超过设定发放数量)
            $num = CouponsUser::where('is_delete', 0)->where('cou_id', $v['cou_id'])->count();
            if ($v['cou_total'] <= $num) {
                continue;
            }

            //当前用户已经领取的数量,超过允许领取的数量则不再返券
            $cou_user_num = CouponsUser::where('is_delete', 0)->where('user_id', $order['user_id'])
                ->where('cou_id', $v['cou_id'])
                ->count();

            if ($cou_user_num < $v['cou_user_num']) {

                //获取订单商品详情
                $order_id = $order['order_id'];
                $goods = Goods::selectRaw("GROUP_CONCAT(goods_id) AS goods_id, GROUP_CONCAT(cat_id) AS cat_id, GROUP_CONCAT(user_id) AS user_id")->whereHasIn('getOrderGoods', function ($query) use ($order_id) {
                    $query->where('order_id', $order_id);
                });

                $goods = BaseRepository::getToArrayFirst($goods);

                $goods_ids = !empty($goods['goods_id']) ? array_unique(explode(",", $goods['goods_id'])) : [];
                $goods_cats = !empty($goods['cat_id']) ? array_unique(explode(",", $goods['cat_id'])) : [];
                $user_id = isset($goods['user_id']) ? array_unique(explode(",", $goods['user_id'])) : [];

                $flag = false;

                //返券的金额门槛满足
                if ($order['goods_amount'] >= $v['cou_get_man']) {
                    if ($v['cou_ok_goods']) {
                        $cou_ok_goods = explode(",", $v['cou_ok_goods']);

                        if ($goods_ids) {
                            foreach ($goods_ids as $m => $n) {
                                //商品门槛满足(如果当前订单有多件商品,只要有一件商品满足条件,那么当前订单即反当前券)
                                if (in_array($n, $cou_ok_goods)) {
                                    $flag = true;
                                    break;
                                }
                            }
                        }
                    } elseif ($v['cou_ok_cat']) {
                        $cou_ok_cat = app(CouponsService::class)->getCouChildren($v['cou_ok_cat']);
                        $cou_ok_cat = explode(",", $cou_ok_cat);

                        if ($goods_cats) {
                            foreach ($goods_cats as $m => $n) {
                                //商品门槛满足(如果当前订单有多件商品 ,只要有一件商品的分类满足条件,那么当前订单即反当前券)
                                if (in_array($n, $cou_ok_cat)) {
                                    $flag = true;
                                    break;
                                }
                            }
                        }
                    } elseif ($v['cou_type'] == 2 && empty($v['cou_ok_goods'])) {
                        if (in_array($v['ru_id'], $user_id)) {
                            $flag = true;
                        }
                    } else {
                        $flag = true;
                    }

                    //返券
                    if ($flag) {
                        $other = [
                            'user_id' => $order['user_id'],
                            'cou_id' => $v['cou_id'],
                            'cou_money' => $v['cou_money'],
                            'uc_sn' => $v['uc_sn']
                        ];
                        CouponsUser::insert($other);
                    }
                }
            }
        }
    }
}

/**
 * 返回订单发放的红包
 * @param int $order_id 订单id
 */
function return_order_bonus($order_id)
{
    /* 取得订单应该发放的红包 */
    $bonus_list = order_bonus($order_id);

    /* 删除 */
    if ($bonus_list) {

        /* 取得订单信息 */
        $user_id = OrderInfo::where('order_id', $order_id)->value('user_id');

        foreach ($bonus_list as $bonus) {
            UserBonus::where('bonus_type_id', $bonus['type_id'])
                ->where('user_id', $user_id)
                ->where('order_id', 0)
                ->take($bonus['number'])
                ->delete();
        }
    }
}

/**
 * 取得订单应该发放的红包
 * @param int $order_id 订单id
 * @return  array
 */
function order_bonus($order_id)
{
    /* 查询按商品发的红包 */
    $day = getdate();
    $today = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

    $where = [
        'send_type' => SEND_BY_GOODS,
        'today' => $today
    ];
    $goods_id_arr = OrderGoods::select('goods_id')
        ->where('order_id', $order_id)
        ->whereHasIn('getGoods');
    $goods_id_arr = BaseRepository::getToArrayGet($goods_id_arr);
    $goods_id = !empty($goods_id_arr) ? BaseRepository::getKeyPluck($goods_id_arr, 'goods_id') : [];

    if ($goods_id) {
        $list = app(\App\Services\Bonus\BonusManageService::class)->getOrderGoodsBonusList($goods_id, $where);
    }

    /* 查询定单中非赠品总金额 */
    $amount = order_amount($order_id, false);

    /* 查询订单日期 */
    $order = OrderInfo::select('order_id', 'add_time', 'ru_id')
        ->where('order_id', $order_id);

    $order = BaseRepository::getToArrayFirst($order);

    $order_time = $order['add_time'] ?? '';
    $ru_id = $order['ru_id'] ?? 0;

    /* 查询按订单发的红包 */
    $bonus = BonusType::select('type_id', 'type_name', 'type_money')
        ->selectRaw("IFNULL(FLOOR('$amount' / min_amount), 1) AS number")
        ->where('send_type', SEND_BY_ORDER)
        ->where('send_start_date', '<=', $order_time)
        ->where('send_end_date', '>=', $order_time)
        ->where('user_id', $ru_id)
        ->where('min_amount', '<=', $amount);

    $bonus = BaseRepository::getToArrayGet($bonus);
    $list = BaseRepository::getArrayMerge($list, $bonus);

    return $list;
}

/**
 * 计算购物车中的商品能享受红包支付的总额
 *
 * @param string $cart_value
 * @param int $user_id
 * @param int $rec_type
 * @return float|int
 */
function compute_discount_amount($cart_value = '', $user_id = 0, $rec_type = CART_GENERAL_GOODS)
{
    if (empty($user_id)) {
        $user_id = session('user_id', 0);
    }

    $CategoryLib = app(CategoryService::class);

    // 会员等级
    $user_rank = app(UserCommonService::class)->getUserRankByUid($user_id);
    $user_rank['rank_id'] = $user_rank['rank_id'] ?? 0;
    /* 查询优惠活动 */
    $now = TimeRepository::getGmTime();
    $user_rank = ',' . $user_rank['rank_id'] . ',';

    $favourable_list = FavourableActivity::where('review_status', 3)
        ->where('start_time', '<=', $now)
        ->where('end_time', '>=', $now)
        ->whereIn('act_type', [FAT_DISCOUNT, FAT_PRICE]);

    $favourable_list = $favourable_list->whereRaw("CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'");

    $favourable_list = BaseRepository::getToArrayGet($favourable_list);

    if (!$favourable_list) {
        return 0;
    }

    /* 查询购物车商品 */
    $goods_list = Cart::selectRaw("goods_id, goods_price * goods_number AS subtotal, ru_id")
        ->where('parent_id', 0)
        ->where('is_gift', 0)
        ->where('rec_type', $rec_type);

    if (!empty($user_id)) {
        $goods_list = $goods_list->where('user_id', $user_id);
    } else {
        $session_id = app(SessionRepository::class)->realCartMacIp();
        $goods_list = $goods_list->where('session_id', $session_id);
    }

    if (!empty($cart_value)) {
        $cart_value = !is_array($cart_value) ? explode(",", $cart_value) : $cart_value;
        $goods_list = $goods_list->whereIn('rec_id', $cart_value);
    }

    $goods_list = $goods_list->with([
        'getGoods' => function ($query) {
            $query->select('goods_id', 'cat_id', 'brand_id');
        }
    ]);

    $goods_list = BaseRepository::getToArrayGet($goods_list);

    if (!$goods_list) {
        return 0;
    } else {
        foreach ($goods_list as $k => $v) {
            $goods_list[$k] = collect($v)->merge($v['get_goods'])->except('get_goods')->all();
        }
    }

    /* 初始化折扣 */
    $discount = 0;
    $favourable_name = [];

    /* 循环计算每个优惠活动的折扣 */
    foreach ($favourable_list as $favourable) {
        $total_amount = 0;
        if ($favourable['act_range'] == FAR_ALL) {
            foreach ($goods_list as $goods) {
                //ecmoban模板堂 --zhuo start
                if ($favourable['userFav_type'] == 1) {
                    $total_amount += $goods['subtotal'];
                } else {
                    if ($favourable['user_id'] == $goods['ru_id']) {
                        $total_amount += $goods['subtotal'];
                    }
                }
                //ecmoban模板堂 --zhuo end
            }
        } elseif ($favourable['act_range'] == FAR_CATEGORY) {
            /* 找出分类id的子分类id */
            $id_list = [];
            $raw_id_list = explode(',', $favourable['act_range_ext']);
            foreach ($raw_id_list as $id) {
                /**
                 * 当前分类下的所有子分类
                 * 返回一维数组
                 */
                $cat_keys = $CategoryLib->getCatListChildren(intval($id));

                $id_list = array_merge($id_list, $cat_keys);
            }
            $ids = join(',', array_unique($id_list));

            foreach ($goods_list as $goods) {
                if (isset($goods['cat_id']) && strpos(',' . $ids . ',', ',' . $goods['cat_id'] . ',') !== false) {
                    //ecmoban模板堂 --zhuo start
                    if ($favourable['userFav_type'] == 1) {
                        $total_amount += $goods['subtotal'];
                    } else {
                        if ($favourable['user_id'] == $goods['ru_id']) {
                            $total_amount += $goods['subtotal'];
                        }
                    }
                    //ecmoban模板堂 --zhuo end
                }
            }
        } elseif ($favourable['act_range'] == FAR_BRAND) {
            $favourable['act_range_ext'] = act_range_ext_brand($favourable['act_range_ext'], $favourable['userFav_type'], $favourable['act_range']);
            foreach ($goods_list as $goods) {
                if (strpos(',' . $favourable['act_range_ext'] . ',', ',' . ($goods['brand_id'] ?? 0) . ',') !== false) {

                    //ecmoban模板堂 --zhuo start
                    if ($favourable['userFav_type'] == 1) {
                        $total_amount += $goods['subtotal'];
                    } else {
                        if ($favourable['user_id'] == $goods['ru_id']) {
                            $total_amount += $goods['subtotal'];
                        }
                    }
                    //ecmoban模板堂 --zhuo end
                }
            }
        } elseif ($favourable['act_range'] == FAR_GOODS) {
            foreach ($goods_list as $goods) {
                if (strpos(',' . $favourable['act_range_ext'] . ',', ',' . $goods['goods_id'] . ',') !== false) {
                    //ecmoban模板堂 --zhuo start
                    if ($favourable['userFav_type'] == 1) {
                        $total_amount += $goods['subtotal'];
                    } else {
                        if ($favourable['user_id'] == $goods['ru_id']) {
                            $total_amount += $goods['subtotal'];
                        }
                    }
                    //ecmoban模板堂 --zhuo end
                }
            }
        } else {
            continue;
        }
        if ($total_amount > 0 && $total_amount >= $favourable['min_amount'] && ($total_amount <= $favourable['max_amount'] || $favourable['max_amount'] == 0)) {
            if ($favourable['act_type'] == FAT_DISCOUNT) {
                $discount += $total_amount * (1 - $favourable['act_type_ext'] / 100);
            } elseif ($favourable['act_type'] == FAT_PRICE) {
                $discount += $favourable['act_type_ext'];
            }
        }
    }


    return $discount;
}

/**
 * 添加礼包到购物车
 *
 * @access  public
 * @param integer $package_id 礼包编号
 * @param integer $num 礼包数量
 * @param int $warehouse_id
 * @param int $area_id
 * @param int $area_city
 * @return  array|boolean
 */
function add_package_to_cart($package_id, $num = 1, $warehouse_id = 0, $area_id = 0, $area_city = 0, $type = 0)
{
    $user_id = session('user_id', 0);
    $session_id = app(SessionRepository::class)->realCartMacIp();

    $GLOBALS['err']->clean();

    $goods_number = $num;
    if ($type == 0) {
        $goods_number = Cart::where('goods_id', $package_id)
            ->where('extension_code', 'package_buy')
            ->value('goods_number');
        $goods_number = $goods_number ? $goods_number : 0;

        $goods_number = $goods_number + $num;
    }

    /* 取得礼包信息 */
    $package = get_package_info($package_id, $warehouse_id, $area_id, $area_city);

    $is_fail = 0;
    $goods_name = '';
    foreach ($package['goods_list'] as $key => $val) {
        if (!$val['stock_number'] || $goods_number * $val['goods_number'] > $val['stock_number']) {
            $is_fail = 2;
            $goods_name = $val['goods_name'];
            break;
        } else {
            if ($num * $val['goods_number'] > $val['stock_number']) {
                $is_fail = 3;
                $goods_name = $val['goods_name'];
                break;
            }
        }
    }

    if ($is_fail) {
        $arr = [
            'error' => $is_fail,
            'goods_name' => $goods_name,
        ];
        return $arr;
    } else {
        if (empty($package)) {
            $GLOBALS['err']->add($GLOBALS['_LANG']['goods_not_exists'], ERR_NOT_EXISTS);

            return false;
        }

        /* 是否正在销售 */
        if ($package['is_on_sale'] == 0) {
            $GLOBALS['err']->add($GLOBALS['_LANG']['not_on_sale'], ERR_NOT_ON_SALE);

            return false;
        }

        /* 现有库存是否还能凑齐一个礼包 */
        if ($GLOBALS['_CFG']['use_storage'] == '1' && app(PackageGoodsService::class)->judgePackageStock($package_id, $num)) {
            $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['package_nonumer'], 1), ERR_OUT_OF_STOCK);

            return false;
        }

        $sess = empty($user_id) ? $session_id : '';

        /* 初始化要插入购物车的基本件数据 */
        $parent = [
            'user_id' => session('user_id', 0),
            'session_id' => $sess,
            'goods_id' => $package_id,
            'goods_sn' => '',
            'goods_name' => addslashes($package['package_name']),
            'market_price' => $package['market_package'],
            'goods_price' => $package['package_price'],
            'goods_number' => $num,
            'goods_attr' => '',
            'goods_attr_id' => '',
            'warehouse_id' => $warehouse_id, //ecmoban模板堂 --zhuo 仓库
            'area_id' => $area_id, //ecmoban模板堂 --zhuo 仓库地区
            'ru_id' => $package['user_id'],
            'is_real' => $package['is_real'],
            'extension_code' => 'package_buy',
            'is_gift' => 0,
            'rec_type' => CART_PACKAGE_GOODS,
            'add_time' => gmtime()
        ];

        /* 如果数量不为0，作为基本件插入 */
        if ($num > 0) {
            /* 检查该商品是否已经存在在购物车中 */
            $row = Cart::select('goods_number')
                ->where('parent_id', 0)
                ->where('goods_id', $package_id)
                ->where('extension_code', 'package_buy')
                ->where('rec_type', CART_PACKAGE_GOODS);

            if (!empty($user_id)) {
                $row = $row->where('user_id', $user_id);
            } else {
                $row = $row->where('session_id', $session_id);
            }

            $row = BaseRepository::getToArrayFirst($row);

            if ($row) { //如果购物车已经有此物品，则更新

                //超值礼包列表添加
                if ($type == 0) {
                    $num += $row['goods_number'];
                }

                if ($GLOBALS['_CFG']['use_storage'] == 0 || $num > 0) {
                    $res = Cart::where('parent_id', 0)
                        ->where('goods_id', $package_id)
                        ->where('extension_code', 'package_buy')
                        ->where('rec_type', CART_PACKAGE_GOODS);

                    if (!empty($user_id)) {
                        $res = $res->where('user_id', $user_id);
                    } else {
                        $res = $res->where('session_id', $session_id);
                    }

                    $res->update(['goods_number' => $num]);
                } else {
                    $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
                    return false;
                }
            } else {
                //购物车没有此物品，则插入
                Cart::insert($parent);
            }
        }

        /* 把赠品删除 */
        $res = Cart::where('is_gift', '<>', 0);

        if (!empty($user_id)) {
            $res = $res->where('user_id', $user_id);
        } else {
            $res = $res->where('session_id', $session_id);
        }

        $res->delete();

        return true;
    }
}

/**
 * 发货单详情
 * @return  array
 */
function get_delivery_info($order_id = 0)
{
    $res = DeliveryOrder::where('order_id', $order_id);
    $res = BaseRepository::getToArrayFirst($res);

    return $res;
}

/**
 * 得到新发货单号
 * @return  string
 */
function get_delivery_sn()
{
    /* 选择一个随机的方案 */
    mt_srand((double)microtime() * 1000000);

    return date('YmdHi') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

/**
 *  by　　Leah
 * @param type $shipping_config
 * @return type
 */
function free_price($shipping_config)
{
    $shipping_config = unserialize($shipping_config);

    $arr = [];

    if (is_array($shipping_config)) {
        foreach ($shipping_config as $key => $value) {
            foreach ($value as $k => $v) {
                $arr['configure'][$value['name']] = $value['value'];
            }
        }
    }
    return $arr;
}

/**
 * 相同商品退换货单 by leah
 * @param type $ret_id
 * @param type $order_sn
 */
function return_order_info_byId($order_id, $refound = true, $is_whole = false)
{
    if (!$refound) {
        if (!$is_whole) {
            $refound_status = $refound === 0 ? 0 : 1;
            //获得唯一一个订单下 申请了全部退换货的退换货订单
            $res = OrderReturn::where('order_id', $order_id)
                ->where('refound_status', $refound_status)
                ->count();
        } else {
            $res = OrderReturn::where('order_id', $order_id)
                ->count();
        }
    } else {
        $res = OrderReturn::where('order_id', $order_id);
        $res = BaseRepository::getToArrayGet($res);
    }

    return $res;
}

/**
 * 获取退换货订单是否整单
 */
function get_order_return_rec($order_id, $is_whole = false)
{
    $res = OrderGoods::select('rec_id')
        ->where('order_id', $order_id);
    $res = BaseRepository::getToArrayGet($res);
    $order_goods_count = count($res);
    $rec_list = BaseRepository::getKeyPluck($res, 'rec_id');

    $res = OrderReturn::select('rec_id')
        ->where('order_id', $order_id);
    $res = BaseRepository::getToArrayGet($res);
    $return_goods = BaseRepository::getKeyPluck($res, 'rec_id');

    $is_diff = false;

    if ($is_whole) {
        $order_goods_count = 0;
    }

    if (!array_diff($rec_list, $return_goods) && count($return_goods) === 1 && $order_goods_count != 1) {
        $is_diff = true;
    }

    return $is_diff;
}

/**
 * 退货单信息
 *
 * @param int $ret_id
 * @param string $order_sn
 * @param int $order_id
 * @return mixed
 */
function return_order_info($ret_id = 0, $order_sn = '', $order_id = 0)
{
    $ret_id = intval($ret_id);
    if ($ret_id > 0) {
        $res = ReturnGoods::select('rec_id', 'ret_id', 'attr_id', 'goods_id', 'return_number', 'refound')
            ->whereHasIn('getOrderReturn', function ($query) use ($ret_id) {
                $query->where('ret_id', $ret_id);
            });

        $res = $res->with([
            'getOrderReturn',
            'getGoods' => function ($query) {
                $query->select('goods_id', 'goods_thumb', 'goods_name', 'shop_price', 'user_id AS ru_id');
            },
            'getOrderReturnExtend' => function ($query) {
                $query->select('ret_id', 'return_number');
            }
        ]);

        $res = BaseRepository::getToArrayFirst($res);
        if ($res) {
            $res = isset($res['get_order_return']) ? BaseRepository::getArrayMerge($res, $res['get_order_return']) : $res;
            $res = isset($res['get_goods']) ? BaseRepository::getArrayMerge($res, $res['get_goods']) : $res;
            $res = isset($res['get_order_return_extend']) ? BaseRepository::getArrayMerge($res, $res['get_order_return_extend']) : $res;
        }

        if ($res) {
            $order = OrderInfo::select('order_id', 'order_sn', 'add_time', 'chargeoff_status', 'goods_amount', 'discount', 'chargeoff_status as order_chargeoff_status', 'is_zc_order', 'agency_id', 'country', 'province', 'city', 'district', 'street', 'is_delete', 'money_paid', 'surplus', 'order_amount', 'integral_money', 'shipping_fee')
                ->where('order_id', $res['order_id'])
                ->with([
                    'getDeliveryOrder' => function ($query) {
                        $query->select('delivery_id', 'order_id', 'delivery_sn', 'update_time', 'how_oos', 'shipping_fee', 'insure_fee', 'invoice_no');
                    },
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

            $order = BaseRepository::getToArrayFirst($order);
            $order = BaseRepository::getArrayMerge($order, $order['get_delivery_order']);

            $res = BaseRepository::getArrayMerge($res, $order);

            if ($res && $res['chargeoff_status'] != 0) {
                $res['chargeoff_status'] = $res['order_chargeoff_status'] ? $res['order_chargeoff_status'] : 0;
            }
        }

        $order = $res;
    } else {
        $order = OrderReturn::whereRaw(1);

        if ($order_id) {
            $order = $order->where('order_id', $order_id);
        } else {
            $order = $order->where('order_sn', $order_sn);
        }

        $order = $order->with([
            'getReturnGoods' => function ($query) {
                $query->select('ret_id', 'attr_id', 'return_number', 'refound');
            },
            'orderInfo',
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

        $order = BaseRepository::getToArrayFirst($order);
        $order = BaseRepository::getArrayMerge($order, $order['order_info']);
    }

    if ($order) {

        /* 商品可退金额 */
        $goods_refound = $order['get_return_goods']['refound'] ?? $order['refound'];
        $return_number = $order['get_return_goods']['return_number'] ?? $order['return_number'];
        $goodsRefoundTotal = $return_number * $goods_refound;

        $refound_status = $order['get_order_return']['refound_status'] ?? 0;
        $refound_status = $order['refound_status'] ?? $refound_status;
        $return_shipping_fee = $order['get_order_return']['return_shipping_fee'] ?? 0;
        $return_shipping_fee = $order['return_shipping_fee'] ?? $return_shipping_fee;
        $order['return_shipping_fee'] = $return_shipping_fee;
        $order['formated_return_shipping_fee'] = app(DscRepository::class)->getPriceFormat($return_shipping_fee, false);
        $ret_id = $order['get_order_return']['ret_id'] ?? 0;
        $ret_id = $order['ret_id'] ?? $ret_id;

        $actual_return = $order['get_order_return']['actual_return'] ?? 0;
        $actual_return = $order['actual_return'] ?? $actual_return;

        $orderGoodsAttrIdList = $order['get_return_goods']['attr_id'] ?? $order['attr_id'];

        $productsGoodsAttrList = [];
        if ($orderGoodsAttrIdList) {
            $orderGoodsAttrIdList = BaseRepository::getImplode($orderGoodsAttrIdList);
            $productsGoodsAttrList = GoodsDataHandleService::getGoodsAttrDataList($orderGoodsAttrIdList, ['goods_attr_id', 'attr_img_flie', 'attr_gallery_flie']);
        }

        /* 整订单金额 */
        $all_goods_amount = $order['goods_amount'] ?? 0;
        $all_goods_amount = $order['order_info']['goods_amount'] ?? $all_goods_amount;

        /* 商品金额 */
        $goods_amount = $order['get_order_return']['should_return'] ?? 0;
        $goods_amount = $order['should_return'] ?? $goods_amount;

        /* 退款单商品金额占比订单总额比例 */
        $goods_scale = $goods_amount / $all_goods_amount;

        $goods_coupons = $order['get_order_return']['goods_coupons'] ?? 0;
        $goods_bonus = $order['get_order_return']['goods_bonus'] ?? 0;
        $goods_favourable = $order['get_order_return']['goods_favourable'] ?? 0;
        $goods_value_card = $order['get_order_return']['goods_value_card'] ?? 0;
        $value_card_discount = $order['get_order_return']['value_card_discount'] ?? 0;
        $goods_integral = $order['get_order_return']['goods_integral'] ?? 0;
        $goods_integral_money = $order['get_order_return']['goods_integral_money'] ?? 0;

        $order['goods_coupons'] = $order['goods_coupons'] ?? $goods_coupons; //商品均摊优惠券金额
        $order['goods_bonus'] = $order['goods_bonus'] ?? $goods_bonus; //商品均摊红包金额
        $order['goods_favourable'] = $order['goods_favourable'] ?? $goods_favourable; //商品均摊优惠折扣金额
        $order['value_card_discount'] = $order['value_card_discount'] ?? $value_card_discount; //商品均摊储值卡折扣金额
        $order['goods_integral'] = $order['goods_integral'] ?? $goods_integral; //商品均摊积分
        $order['goods_integral_money'] = $order['goods_integral_money'] ?? $goods_integral_money; //商品均摊积分金额

        $integral_money = $order['integral_money'] ?? 0; //使用积分金额

        $order_should_return = $goods_amount - $order['goods_coupons'] - $order['goods_bonus'] - $order['goods_favourable'] - $order['value_card_discount'] + $order['return_shipping_fee'];

        $order['formated_value_card'] = sprintf($GLOBALS['_LANG']['average_value_card'], app(DscRepository::class)->getPriceFormat($order['goods_value_card']));
        $order['goods_value_card'] = $order['goods_value_card'] ?? $goods_value_card; //商品均摊使用储值卡金额

        $order_id = $order['order_id'] ?? 0;
        $order_sn = $order['order_sn'] ?? 0;

        $money_paid = $order['money_paid'] ?? 0; //在线支付金额
        $surplus = $order['surplus'] ?? 0; //余额支付
        $order_amount = $order['order_amount'] ?? 0; //未付款金额
        $order_shipping_fee = $order['shipping_fee'] ?? 0; //运费金额

        if (isset($order['order_info']) && $order['order_info']) {
            $money_paid = $order['order_info']['money_paid'] ?? 0;
            $surplus = $order['order_info']['surplus'] ?? 0;
            $integral_money = $order['order_info']['integral_money'] ?? 0;
            $order_amount = $order['order_info']['order_amount'] ?? 0;
            $order_id = $order['order_info']['order_id'] ?? 0;
            $order_sn = $order['get_order_return']['order_sn'] ?? 0;
            $order_shipping_fee = $order['order_info']['shipping_fee'] ?? 0;
        }

        $order['order_id'] = $order_id;
        $order['order_sn'] = $order_sn;

        $order['integral_money'] = $integral_money;
        $order['formated_integral_money'] = app(DscRepository::class)->getPriceFormat($order['integral_money'], false);

        if (isset($order['discount']) && $order['discount'] > 0) {
            $discount_percent = $order['discount'] / $order['goods_amount'];
            $order['discount_percent_decimal'] = number_format($discount_percent, 2, '.', '');
            $order['discount_percent'] = $order['discount_percent_decimal'] * 100;
        } else {
            $order['discount_percent_decimal'] = 0;
            $order['discount_percent'] = 0;
        }

        $return_number = $order['get_return_goods']['return_number'] ?? 0;
        $return_number = $order['return_number'] ?? $return_number;

        $order['return_number'] = $return_number;

        //如果订单只有一个商品  折扣金额为全部折扣  否则按折扣比例计算
        $order['discount_amount'] = $order['goods_favourable'];

        $order_return_count = 0;
        $is_refound_status = 0;
        $order_goods_count = OrderGoods::where('order_id', $order_id)->count('rec_id');

        if ($refound_status != 1) {
            $is_refound_status = OrderReturn::where('order_id', $order_id)->where('refound_status', 1)->count('ret_id');
            $order_return_count = OrderReturn::where('order_id', $order_id)->count('ret_id');
        }

        /* 判断是否为最后一个退款 */
        $last_return = ($order_return_count > 0 && $is_refound_status > 0 && $order_return_count == ($is_refound_status + 1)) || ($order_goods_count == 1);
        $order['last_return'] = $last_return ? true : false;

        if ($order['goods_integral_money'] > 0) {
            $order['should_integral_money'] = $order['goods_integral_money'];
            $order['should_integral'] = $order['goods_integral'];
        } else {
            if ($last_return) {
                $is_actual_integral_money = OrderReturn::where('order_id', $order_id)->where('refound_status', 1)->sum('actual_integral_money');
                $order['should_integral_money'] = $integral_money - $is_actual_integral_money;
            } else {
                $order['should_integral_money'] = $goods_scale * $integral_money;
            }

            $order['should_integral'] = app(DscRepository::class)->getIntegralOfValue($order['should_integral_money']);
        }

        $order['formated_should_integral_money'] = app(DscRepository::class)->getPriceFormat($order['should_integral_money'], false);

        $order['money_paid'] = $money_paid;
        $order['formated_money_paid'] = app(DscRepository::class)->getPriceFormat($money_paid, false);
        $order['surplus'] = $surplus;
        $order['formated_surplus'] = app(DscRepository::class)->getPriceFormat($surplus, false);
        $order['order_amount'] = $order_amount;
        $order['formated_order_amount'] = app(DscRepository::class)->getPriceFormat($order_amount, false);

        /* 订单储值支付金额 */
        $orderCardInfo = app(OrderCommonService::class)->orderUseValueCard($order_id);
        $order['vc_id'] = $orderCardInfo['vc_id'];
        $use_val = $orderCardInfo['use_val_card'] ?? 0;

        /* 已退储值卡金额 */
        $orderReturnCardInfo = app(OrderCommonService::class)->orderReturnValueCard($order_id, $order_sn);
        $order['return_val_card'] = $orderReturnCardInfo['return_val_card'];

        /* 储值卡剩余可退金额 */
        $pay_card_money = $orderCardInfo['use_val_card'] - $orderReturnCardInfo['return_val_card'];
        $order['pay_card_money'] = $pay_card_money;

        $is_pay_amount = 1;
        if ($refound_status != 1) {
            /* 可退储值卡金额 */
            $goods_value_card = 0;
            if ($order['goods_value_card'] > 0) {

                if ($order['goods_value_card'] >= $goods_amount) {
                    /* 当均摊使用的储值卡金额大于或等于商品金额时，优先退回使用储值卡金额 */
                    if ($order['goods_value_card'] > $goods_amount) {
                        //扣除活动优惠金额
                        $goods_value_card = $goods_amount - $order['discount_amount'] - $order['goods_bonus'] - $order['goods_coupons'] - $order['value_card_discount'];
                    } else {
                        $goods_value_card = $order['goods_value_card'];
                    }

                    $is_pay_amount = 0;
                } else {

                    $order['vc_id'] = $orderCardInfo['vc_id'] ?? 0;
                    $order['use_val_card'] = $use_val;
                    $order['formated_use_val_card'] = $order['formated_order_amount'] = app(DscRepository::class)->getPriceFormat($order['use_val_card'], false);

                    if ($order['goods_value_card'] > $order['pay_card_money']) {
                        $order['goods_value_card'] = $order['pay_card_money'];
                    } else {
                        /* 处理最后一个退货单 */
                        if ($last_return) {
                            $order['goods_value_card'] = $order['pay_card_money'];
                        }
                    }

                    if ($last_return) {
                        $deductionActivityTotal = $goods_amount - $order['discount_amount'] - $order['goods_bonus'] - $order['goods_coupons'] - $order['value_card_discount'];
                        if ($order['goods_value_card'] >= $deductionActivityTotal) {
                            $goods_value_card = $deductionActivityTotal;
                        } else {
                            $goods_value_card = $order['goods_value_card'];
                        }
                    } else {
                        if ($order_should_return > $order['goods_value_card']) {
                            $goods_value_card = $order['goods_value_card'];
                        } else {
                            $goods_value_card = $order_should_return;
                        }
                    }
                }
            } else {
                /* 当最后一个退款单时，把剩余可退的储值卡金额退回 */
                if ($last_return) {
                    $goods_value_card = $pay_card_money;
                }
            }
        } else {
            $goods_value_card = $order['return_val_card'];
        }

        if ($goods_value_card > $pay_card_money) {
            $goods_value_card = $pay_card_money;
        }

        $goods_value_card = app(DscRepository::class)->changeFloat($goods_value_card);

        $order['formated_goods_coupons'] = app(DscRepository::class)->getPriceFormat($order['goods_coupons'], false);
        $order['formated_goods_bonus'] = app(DscRepository::class)->getPriceFormat($order['goods_bonus'], false);
        $order['formated_goods_favourable'] = app(DscRepository::class)->getPriceFormat($order['goods_favourable'], false);
        $order['formated_value_card_discount'] = app(DscRepository::class)->getPriceFormat($order['value_card_discount'], false);

        $paid_amount = $money_paid + $surplus + $use_val;
        $order['paid_amount'] = $paid_amount;
        $order['formated_paid_amount'] = app(DscRepository::class)->getPriceFormat($paid_amount, false);

        /* 取得区域名 */
        $province = $order['get_region_province']['region_name'] ?? '';
        $city = $order['get_region_city']['region_name'] ?? '';
        $district = $order['get_region_district']['region_name'] ?? '';
        $street = $order['get_region_street']['region_name'] ?? '';
        $order['region'] = $province . ' ' . $city . ' ' . $district . ' ' . $street;

        $order['attr_val'] = is_string($order['attr_val']) ? $order['attr_val'] : unserialize($order['attr_val']);
        $order['apply_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $order['apply_time']);
        $order['formated_update_time'] = isset($order['update_time']) ? TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $order['update_time']) : '';
        $order['formated_return_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $order['return_time']);
        $order['formated_add_time'] = isset($order['add_time']) ? TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $order['add_time']) : '';
        $order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;

        $order['should_return'] = app(DscRepository::class)->changeFloat($order['should_return']);

        /* 已退款金额（余额 + 在线支付金额） */
        $orderRefoundFee = app(OrderRefoundService::class)->orderRefoundFee($order_id);
        $order['orderRefoundFee'] = $orderRefoundFee;

        $actual_integral_money = 0;
        if ($refound_status == 1) {

            /* 退余额 + 在线支付金额 */
            $order['pay_actual_return'] = $actual_return;
            $order['formated_pay_actual_return'] = app(DscRepository::class)->getPriceFormat($actual_return, false);

            /* 退积分金额 */
            $actual_integral_money = $order['get_order_return']['actual_integral_money'] ?? 0;
            $actual_integral_money = $order['actual_integral_money'] ?? $actual_integral_money;
            $order['actual_integral_money'] = $actual_integral_money;
            $order['formated_actual_integral_money'] = app(DscRepository::class)->getPriceFormat($actual_integral_money, false);

            /* 已退储值卡金额 */
            $actual_add_val = app(OrderCommonService::class)->orderReturnValueCardRecord($ret_id);

            if ($actual_add_val == 0) {
                $actual_value_card = $order['get_order_return']['actual_value_card'] ?? 0;
                $actual_add_val = $order['actual_value_card'] ?? $actual_value_card;
            }

            $order['actual_add_val'] = app(DscRepository::class)->changeFloat($actual_add_val);
            $order['formated_actual_value_card'] = app(DscRepository::class)->getPriceFormat($order['actual_add_val'], false);
            $order['actual_return'] = $order['pay_actual_return'] + $actual_add_val;

            $pay_shipping_fee = $return_shipping_fee;

            $order['formated_should_return'] = app(DscRepository::class)->getPriceFormat($order['should_return'], false);

            /* 仅用于退款页面详情页显示 */
            $order['return_amount'] = $order['pay_actual_return'] + $actual_add_val;
            $order['formated_return_amount'] = app(DscRepository::class)->getPriceFormat($order['return_amount'], false);
        } else {
            $pay_shipping_fee = 0;
            if ($order_amount > 0) {
                /* 含运费未付款 */
                if ($order_amount >= $order['return_shipping_fee']) {
                    if ($order['pay_card_money'] > $order_should_return) {
                        $pay_shipping_fee = $order['return_shipping_fee'];
                    } elseif (($money_paid + $surplus - $orderRefoundFee) > $order['return_shipping_fee']) {
                        $pay_shipping_fee = $order['return_shipping_fee'];
                    } else {
                        $pay_shipping_fee = 0;
                    }
                } elseif ($order_amount <= $order['return_shipping_fee']) {
                    $pay_shipping_fee = $order['return_shipping_fee'] - $order_amount;
                }
            } else {
                $pay_shipping_fee = $order['return_shipping_fee'];
            }

            $returnShippingTotal = app(OrderCommonService::class)->orderReturnShippingFee($order_id);

            /* 控制退运费不能大于实际支付运费金额 */
            if ($pay_shipping_fee > ($order_shipping_fee - $returnShippingTotal)) {
                $pay_shipping_fee = $order_shipping_fee - $returnShippingTotal;
            }

            $return_rate_price = 0;
            if (CROSS_BORDER === true) { // 跨境多商户
                $order['formated_return_rate_price'] = app(DscRepository::class)->getPriceFormat($order['return_rate_price'], false);
                $return_rate_price = $order['return_rate_price'];
            }

            $goods_should_return = $order['should_return'] - $order['discount_amount'] - $order['goods_bonus'] - $order['goods_coupons'] - $order['value_card_discount'] + $pay_shipping_fee + $return_rate_price;
            $goods_should_return = app(DscRepository::class)->changeFloat($goods_should_return); //含运费 + 跨境税费
            $order['goods_should_return'] = $goods_should_return;
            $order['formated_should_return'] = app(DscRepository::class)->getPriceFormat($goods_should_return, false);

            $is_shipping = 0;
            $order['pay_goods_amount'] = 0;
            if ($is_pay_amount == 1) {
                /* 在线支付 + 余额支付 + 储值卡 - 已退款金额 - 跨境税费 */
                if ($use_val > $pay_shipping_fee) {
                    $order['pay_goods_amount'] = $surplus + $money_paid;
                } else {
                    if (($surplus + $money_paid) >= $pay_shipping_fee) {
                        $order['pay_goods_amount'] = $surplus + $money_paid - $pay_shipping_fee; //不含运费
                    } elseif ($surplus > 0 && $surplus >= $pay_shipping_fee) {
                        $order['pay_goods_amount'] = $surplus - $pay_shipping_fee; //不含运费
                    } elseif ($money_paid > 0 && $money_paid >= $pay_shipping_fee) {
                        $order['pay_goods_amount'] = $money_paid - $pay_shipping_fee; //不含运费
                    }
                }

                if ($order['pay_goods_amount'] > $orderRefoundFee) {
                    $order['pay_goods_amount'] = $order['pay_goods_amount'] - $orderRefoundFee; //减去已退款金额
                }

                if ($goods_should_return >= $goods_value_card) {
                    if ($order['pay_goods_amount'] > ($goods_should_return - $goods_value_card)) {
                        $order['pay_goods_amount'] = $goods_should_return - $goods_value_card - $pay_shipping_fee;
                        $is_shipping = 1;
                    }
                }
            } else {

                if ($order_return_count > 0 && $is_refound_status > 0 && $order_return_count == ($is_refound_status + 1)) {
                    if ($order['pay_goods_amount'] == 0) {
                        $order['pay_goods_amount'] = $money_paid + $surplus - $orderRefoundFee;
                    }
                }

                /* 当申请退款金额的运费为0元时，判断均摊使用的金额是否大于应退储值卡金额，如大于则剩余部分退运费 */
                if ($pay_shipping_fee == 0 && $order['goods_value_card'] > $goods_value_card) {

                    $pay_shipping_fee = $order['goods_value_card'] - $goods_value_card;

                    /* 当没有运费可退时，则全部退储值卡金额 */
                    if (($order_shipping_fee - $returnShippingTotal) == 0) {
                        $pay_shipping_fee = 0;
                        $goods_value_card = $order['goods_value_card'];
                    }
                }
            }

            /* 退款金额不能大于商品可退金额 */
            if ($order['pay_goods_amount'] > $goodsRefoundTotal) {
                $order['pay_goods_amount'] = $goodsRefoundTotal;
            }

            /* 控制储值卡退款不能大于可退款金额 */
            if ($goods_value_card > $order['pay_card_money']) {
                $goods_value_card = $order['pay_card_money'];
            }

            $order['pay_goods_amount'] = app(DscRepository::class)->changeFloat($order['pay_goods_amount']);

            /* 确保退款金额不能大于已付款金额 */
            if ($order['pay_goods_amount'] > ($surplus + $money_paid - $orderRefoundFee)) {
                $order['pay_goods_amount'] = ($surplus + $money_paid - $orderRefoundFee);
            }

            $order['formated_pay_goods_amount'] = app(DscRepository::class)->getPriceFormat($order['pay_goods_amount'], false);

            if ($order['pay_goods_amount'] > 0) {
                $return_amount = $order['pay_goods_amount']; //实退：在线支付 + 余额支付
                if ($goods_value_card > 0) {
                    $return_amount += $goods_value_card; //实退：在线支付 + 余额支付 + 储值卡
                }

                $return_amount += $pay_shipping_fee; //实退：在线支付 + 余额支付 + 储值卡

            } else {

                $return_amount = $goods_value_card;
                if ($goods_should_return >= $return_amount) {
                    $pay_shipping_fee = $goods_should_return - $return_amount;
                }

                if ($is_shipping == 0) {
                    $return_amount += $pay_shipping_fee;
                }
            }

            $return_amount = app(DscRepository::class)->changeFloat($return_amount);

            /* 仅用于退款页面详情页显示 */
            $order['return_amount'] = $return_amount; //含运费 + 跨境税费
            $order['formated_return_amount'] = app(DscRepository::class)->getPriceFormat($order['return_amount'] + $order['should_integral_money'], false);
        }

        $order['should_return'] = app(DscRepository::class)->changeFloat($order['should_return']);
        $order['formated_goods_amount'] = app(DscRepository::class)->getPriceFormat($order['should_return'], false);
        $order['formated_discount_amount'] = app(DscRepository::class)->getPriceFormat($order['discount_amount'], false);

        $order['pay_shipping_fee'] = app(DscRepository::class)->changeFloat($pay_shipping_fee);
        $order['formated_pay_shipping_fee'] = app(DscRepository::class)->getPriceFormat($order['pay_shipping_fee'], false);

        $order['pay_value_card'] = app(DscRepository::class)->changeFloat($goods_value_card);
        $order['formated_pay_value_card'] = app(DscRepository::class)->getPriceFormat($order['pay_value_card'], false);

        $order['formated_actual_return'] = app(DscRepository::class)->getPriceFormat($order['actual_return'] + $actual_integral_money, false);
        $order['return_status1'] = $order['return_status'];
        $order['return_status_original'] = $order['return_status'];
        if ($order['return_status'] < 0) {
            $order['return_status'] = trans('user.only_return_money');
        } else {
            $order['return_status'] = trans('user.rf.' . $order['return_status']);
        }
        $order['refound_status1'] = $refound_status;
        // 原始退款状态
        $order['refound_status_original'] = $order['refound_status'];
        $order['refound_status'] = trans('user.ff.' . $order['refound_status']);
        // 退款状态格式化
        $order['refound_status_formated'] = $order['refound_status'];
        // 退换货类型格式化
        $order['return_type_formated'] = trans('user.return_type.' . $order['return_type']);
        $order['address_detail'] = $order['region'] . ' ' . $order['address'];

        $order['shop_price'] = isset($order['shop_price']) ? app(DscRepository::class)->getPriceFormat($order['shop_price'], false) : '';

        // 退换货原因
        $return_cause = ReturnCause::select('parent_id', 'cause_name')->where('cause_id', $order['cause_id'])->first();
        $parent_id = $return_cause['parent_id'] ?? 0;
        $cause_name = $return_cause['cause_name'] ?? '';

        $parent = ReturnCause::where('cause_id', $parent_id)->value('cause_name');
        if ($parent) {
            $order['return_cause'] = $parent . "-" . $cause_name;
        } else {
            $order['return_cause'] = $cause_name;
        }

        if ($order['refound_status_original'] == REFUSE_APPLY) {
            $order['action_note'] = ReturnAction::where('ret_id', $order['ret_id'])
                ->where('return_status', REFUSE_APPLY)
                ->orderBy('log_time', 'desc')
                ->value('action_note');
        }

        $shipping = get_shipping_code($order['back_shipping_name']);
        if (!empty($order['back_other_shipping'])) {
            $order['back_shipp_shipping'] = $order['back_other_shipping'];
        } else {
            if ($order['back_shipping_name'] != "999") {
                $order['back_shipp_shipping'] = $shipping['shipping_name'];
            } else {
                $order['back_shipp_shipping'] = "其他";
            }
        }
        $order['back_shipp_code'] = $shipping['shipping_code'];

        if ($order['out_shipping_name']) {
            $shipping = get_shipping_code($order['out_shipping_name']);
            $order['out_shipp_shipping'] = $shipping['shipping_name'];
            $order['out_shipp_code'] = $shipping['shipping_code'];
        }

        //下单，商品单价
        $goods_price = OrderGoods::where('order_id', $order['order_id'])
            ->where('goods_id', $order['goods_id'])
            ->value('goods_price');
        $order['goods_price'] = app(DscRepository::class)->getPriceFormat($goods_price, false);
        $order['goods_thumb'] = isset($order['goods_thumb']) ? app(DscRepository::class)->getImagePath($order['goods_thumb']) : '';
        $order['url'] = app(DscRepository::class)->buildUri('goods', ['gid' => $order['goods_id']]);

        $goods_attr_id = BaseRepository::getExplode($orderGoodsAttrIdList);
        $order['goods_thumb'] = app(GoodsAttrService::class)->cartGoodsAttrImage($goods_attr_id, $productsGoodsAttrList, $order['goods_thumb']);

        // 取得退换货商品客户上传图片凭证
        $where = [
            'user_id' => $order['user_id'],
            'rec_id' => $order['rec_id']
        ];
        $order['img_list'] = app(OrderRefoundService::class)->getReturnImagesList($where);
        $order['img_count'] = count($order['img_list']);

        //IM or 客服
        if ($GLOBALS['_CFG']['customer_service'] == 0) {
            $ru_id = 0;
        } else {
            $ru_id = $order['ru_id'] ?? 0;
        }

        $shop_information = app(MerchantCommonService::class)->getShopName($ru_id); //通过ru_id获取到店铺信息;
        $order['is_im'] = isset($shop_information['is_im']) ? $shop_information['is_im'] : 0; //平台是否允许商家使用"在线客服";
        $order['shop_name'] = $shop_information['shop_name'] ?? '';
        $order['shop_url'] = app(DscRepository::class)->buildUri('merchants_store', ['urid' => $ru_id], $order['shop_name']);

        if ($ru_id == 0) {
            //判断平台是否开启了IM在线客服
            $kf_im_switch = SellerShopinfo::where('ru_id', 0)->value('kf_im_switch');
            if ($kf_im_switch) {
                $order['is_dsc'] = true;
            } else {
                $order['is_dsc'] = false;
            }
        } else {
            $order['is_dsc'] = false;
        }

        $order['ru_id'] = $ru_id;

        $basic_info = $shop_information;

        $chat = app(DscRepository::class)->chatQq($basic_info);

        $order['kf_type'] = $chat['kf_type'];
        $order['kf_ww'] = $chat['kf_ww'];
        $order['kf_qq'] = $chat['kf_qq'];
    }

    return $order;
}

/**
 * 获得退换货商品
 *
 * @param int $ret_id
 * @return array
 */
function get_return_goods($ret_id = 0)
{
    $ret_id = intval($ret_id);

    $res = ReturnGoods::whereHasIn('getOrderReturn', function ($query) use ($ret_id) {
        $query->where('ret_id', $ret_id);
    });

    $res = $res->with([
        'getGoods' => function ($query) {
            $query->select('goods_id', 'goods_thumb', 'brand_id')
                ->with('getBrand');
        }
    ]);

    $res = BaseRepository::getToArrayGet($res);

    $goods_list = [];
    if ($res) {

        $orderGoodsAttrIdList = BaseRepository::getKeyPluck($res, 'attr_id');
        $orderGoodsAttrIdList = BaseRepository::getArrayUnique($orderGoodsAttrIdList);
        $orderGoodsAttrIdList = ArrRepository::getArrayUnset($orderGoodsAttrIdList);

        $productsGoodsAttrList = [];
        if ($orderGoodsAttrIdList) {
            $orderGoodsAttrIdList = BaseRepository::getImplode($orderGoodsAttrIdList);
            $productsGoodsAttrList = GoodsDataHandleService::getGoodsAttrDataList($orderGoodsAttrIdList, ['goods_attr_id', 'attr_img_flie', 'attr_gallery_flie']);
        }

        $returnGoodsAttrIdList = BaseRepository::getKeyPluck($res, 'return_attr_id');
        $returnGoodsAttrIdList = BaseRepository::getArrayUnique($returnGoodsAttrIdList);
        $returnGoodsAttrIdList = ArrRepository::getArrayUnset($returnGoodsAttrIdList);

        $returnProductsGoodsAttrList = [];
        if ($returnGoodsAttrIdList) {
            $returnGoodsAttrIdList = BaseRepository::getImplode($returnGoodsAttrIdList);
            $returnProductsGoodsAttrList = GoodsDataHandleService::getGoodsAttrDataList($returnGoodsAttrIdList, ['goods_attr_id', 'attr_img_flie', 'attr_gallery_flie']);
        }

        foreach ($res as $row) {
            $row = BaseRepository::getArrayMerge($row, $row['get_goods']);
            $row = BaseRepository::getArrayMerge($row, $row['get_brand']);

            $row['refound'] = app(DscRepository::class)->getPriceFormat($row['refound'], false);

            //图片显示
            $row['goods_thumb'] = app(DscRepository::class)->getImagePath($row['goods_thumb']);

            if ($row['return_type'] == 2) {
                $goods_attr_id = $row['return_attr_id'];
                $goods_attr_id = BaseRepository::getExplode($goods_attr_id);
                $row['goods_thumb'] = app(GoodsAttrService::class)->cartGoodsAttrImage($goods_attr_id, $returnProductsGoodsAttrList, $row['goods_thumb']);
            } else {
                $goods_attr_id = $row['attr_id'];
                $goods_attr_id = BaseRepository::getExplode($goods_attr_id);
                $row['goods_thumb'] = app(GoodsAttrService::class)->cartGoodsAttrImage($goods_attr_id, $productsGoodsAttrList, $row['goods_thumb']);
            }

            $goods_list[] = $row;
        }
    }

    return $goods_list;
}

/**
 * 取的退换货表单里的商品
 * by Leah
 * @param type $rec_id
 * @return type
 */
function get_return_order_goods($rec_id)
{
    $goods_list = OrderGoods::where('rec_id', $rec_id);

    $goods_list = $goods_list->with([
        'getGoods' => function ($query) {
            $query->select('goods_id', 'brand_id', 'goods_thumb')
                ->with('getBrand');
        }
    ]);

    $goods_list = BaseRepository::getToArrayGet($goods_list);

    if ($goods_list) {

        $orderGoodsAttrIdList = BaseRepository::getKeyPluck($goods_list, 'goods_attr_id');
        $orderGoodsAttrIdList = BaseRepository::getArrayUnique($orderGoodsAttrIdList);
        $orderGoodsAttrIdList = ArrRepository::getArrayUnset($orderGoodsAttrIdList);

        $productsGoodsAttrList = [];
        if ($orderGoodsAttrIdList) {
            $orderGoodsAttrIdList = BaseRepository::getImplode($orderGoodsAttrIdList);
            $productsGoodsAttrList = GoodsDataHandleService::getGoodsAttrDataList($orderGoodsAttrIdList, ['goods_attr_id', 'attr_img_flie', 'attr_gallery_flie']);
        }

        foreach ($goods_list as $key => $row) {
            $row = BaseRepository::getArrayMerge($row, $row['get_goods']);
            $row = BaseRepository::getArrayMerge($row, $row['get_brand']);

            $goods_list[$key] = $row;
            $goods_list[$key]['goods_thumb'] = app(DscRepository::class)->getImagePath($row['goods_thumb']);

            $goods_attr_id = $row['goods_attr_id'] ?? '';
            $goods_attr_id = BaseRepository::getExplode($goods_attr_id);
            $goods_list[$key]['goods_thumb'] = app(GoodsAttrService::class)->cartGoodsAttrImage($goods_attr_id, $productsGoodsAttrList, $goods_list[$key]['goods_thumb']);
        }
    }

    return $goods_list;
}

/**
 * 取的订单上商品中的某一商品
 * by　Leah
 * @param type $rec_id
 */
function get_return_order_goods1($rec_id = 0)
{
    $res = OrderGoods::where('rec_id', $rec_id);
    $res = BaseRepository::getToArrayFirst($res);

    return $res;
}

/**
 * 计算退款金额
 * by Leah  by kong
 * @param type $order_id
 * @param type $rec_id
 * @param type $num
 * @return type
 */
function get_return_refound($order_id = 0, $rec_id = 0, $num = 0)
{
    $orders = OrderInfo::select('money_paid', 'goods_amount', 'surplus', 'shipping_fee')
        ->where('order_id', $order_id);
    $orders = BaseRepository::getToArrayFirst($orders);

    $return_shipping_fee = OrderReturn::selectRaw("SUM(return_shipping_fee) AS return_shipping_fee")
        ->where('order_id', $order_id)
        ->whereIn('return_type', [1, 3])
        ->value('return_shipping_fee');
    $return_shipping_fee = $return_shipping_fee ?? 0;

    $res = OrderGoods::selectRaw("goods_number, goods_price, (goods_number * goods_price) AS goods_amount")
        ->where('rec_id', $rec_id);
    $res = BaseRepository::getToArrayFirst($res);

    if ($res && $num > $res['goods_number'] || empty($num)) {
        $num = $res['goods_number'];
    }

    $return_price = $num * $res['goods_price'];
    $return_shipping_fee = $orders['shipping_fee'] - $return_shipping_fee;

    if ($return_price > 0) {
        $return_price = number_format($return_price, 2, '.', '');
    }

    if ($return_shipping_fee > 0) {
        $return_shipping_fee = number_format($return_shipping_fee, 2, '.', '');
    }

    $arr = [
        'return_price' => $return_price,
        'return_shipping_fee' => $return_shipping_fee
    ];

    return $arr;
}

/**
 * 获得退换货操作log
 *
 * @param $ret_id
 * @return array
 * @throws Exception
 */
function get_return_action($ret_id)
{
    $res = ReturnAction::where('ret_id', $ret_id)
        ->orderBy('log_time', 'desc')
        ->orderBy('ret_id', 'desc')
        ->orderBy('action_id', 'desc');
    $res = BaseRepository::getToArrayGet($res);

    $act_list = [];
    if ($res) {
        foreach ($res as $row) {
            $row['return_status'] = lang('user.rf.' . $row['return_status']);
            $row['refound_status'] = lang('user.ff.' . $row['refound_status']);
            $row['action_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $row['log_time']);

            $act_list[] = $row;
        }
    }

    return $act_list;
}

/**
 *  获取订单里某个商品 信息
 * @param int $rec_id
 * @return
 */
function rec_goods($rec_id = 0)
{
    $where = [
        'rec_id' => $rec_id
    ];
    $res = app(OrderService::class)->getOrderGoodsInfo($where);

    if (empty($res)) {
        return [];
    }

    $subtotal = $res['goods_price'] * $res['goods_number'];

    if ($res['extension_code'] == 'package_buy') {
        $res['package_goods_list'] = app(PackageGoodsService::class)->getPackageGoods($res['goods_id']);
    }
    $res['market_price_formated'] = app(DscRepository::class)->getPriceFormat($res['market_price']);
    $res['goods_price1'] = $res['goods_price'];
    $res['goods_price'] = app(DscRepository::class)->getPriceFormat($res['goods_price']);
    $res['subtotal'] = app(DscRepository::class)->getPriceFormat($subtotal);

    $res['format_goods_coupons'] = app(DscRepository::class)->getPriceFormat($res['goods_coupons']);
    $res['format_goods_bonus'] = app(DscRepository::class)->getPriceFormat($res['goods_bonus']);
    $res['format_goods_favourable'] = app(DscRepository::class)->getPriceFormat($res['goods_favourable']);
    $res['format_goods_value_card'] = app(DscRepository::class)->getPriceFormat($res['goods_value_card']);
    $res['format_value_card_discount'] = app(DscRepository::class)->getPriceFormat($res['value_card_discount']);
    $res['format_actual_return'] = app(DscRepository::class)->getPriceFormat($subtotal - $res['goods_coupons'] - $res['goods_bonus'] - $res['goods_favourable'] - $res['value_card_discount']);

    $goods = Goods::select('goods_img', 'goods_thumb', 'user_id', 'goods_cause')->where('goods_id', $res['goods_id']);
    $goods = BaseRepository::getToArrayFirst($goods);

    $res['user_name'] = app(MerchantCommonService::class)->getShopName($goods['user_id'], 1);

    $basic_info = SellerShopinfo::where('ru_id', $goods['user_id']);
    $basic_info = BaseRepository::getToArrayFirst($basic_info);

    $chat = app(DscRepository::class)->chatQq($basic_info);
    $res['kf_type'] = $chat['kf_type'];
    $res['kf_qq'] = $chat['kf_qq'];
    $res['kf_ww'] = $chat['kf_ww'];

    $productsGoodsAttrList = [];
    if ($res['goods_attr_id']) {
        $productsGoodsAttrList = GoodsDataHandleService::getGoodsAttrDataList($res['goods_attr_id'], ['goods_attr_id', 'attr_img_flie', 'attr_gallery_flie']);
    }

    /* 修正商品图片 */
    $res['goods_img'] = app(DscRepository::class)->getImagePath($goods['goods_img']);
    $res['goods_thumb'] = app(DscRepository::class)->getImagePath($goods['goods_thumb']);

    $goods_attr_id = BaseRepository::getExplode($res['goods_attr_id']);
    $res['goods_thumb'] = app(GoodsAttrService::class)->cartGoodsAttrImage($goods_attr_id, $productsGoodsAttrList, $res['goods_thumb']);

    $res['goods_cause'] = $goods['goods_cause'];

    $res['url'] = app(DscRepository::class)->buildUri('goods', ['gid' => $res['goods_id']], $res['goods_name']);

    return $res;
}

/**
 * 是否有退换货记录 by Leah
 * @param int $rec_id
 * @return
 */
function get_is_refound($rec_id = 0)
{
    $count = OrderReturn::where('rec_id', $rec_id)->count();

    if ($count > 0) {
        $is_refound = 1;
    } else {
        $is_refound = 0;
    }

    return $is_refound;
}

/**
 * 订单单品退款
 *
 * @param $order 订单
 * @param $refund_type 退款方式 1 到帐户余额 2 到退款申请（先到余额，再申请提款） 3 不处理
 * @param $refund_note 退款说明
 * @param int $refund_amount 退款金额（如果为0，取订单已付款金额）
 * @param string $operation
 * @return int|string
 * @throws Exception
 */
function order_refound($order, $refund_type, $refund_note, $refund_amount = 0, $operation = '')
{
    $StoreRep = app(StoreService::class);

    /* 检查参数 */
    $user_id = $order['user_id'];
    if ($user_id == 0 && $refund_type == 1) {
        return 'anonymous, cannot return to account balance';
    }

    $in_operation = ['refound'];

    //过滤白条
    if ($refund_type != 5) {
        if (in_array($operation, $in_operation)) {
            $amount = $refund_amount;
        } else {
            $amount = $refund_amount > 0 ? $refund_amount : $order['should_return'];
        }

        if ($amount <= 0) {
            return 1;
        }
    }

    if (!in_array($refund_type, [1, 2, 3, 5])) { //5:白条退款 bylu;
        return 'invalid params';
    }

    /* 备注信息 */
    if (!empty($order['order_sn'])) {

        $change_desc = '';
        if (isset($order['return_sn'])) {
            $change_desc .= "[" . lang('admin/order.return_change_sn') . "：" . $order['return_sn'] . "] - ";
        }

        $change_desc .= "[" . sprintf(lang('admin/order.order_refund'), $order['order_sn']) . "]" . $refund_note;
    }

    /* 处理退款 */
    if (1 == $refund_type) {
        /* 如果非匿名，退回余额 */
        if ($user_id > 0) {
            $is_ok = 1;
            if ($order['ru_id'] && $order['chargeoff_status'] == 2) {
                $seller_shopinfo = $StoreRep->getShopInfo($order['ru_id']);

                if ($seller_shopinfo) {
                    $seller_shopinfo['credit'] = $seller_shopinfo['seller_money'] + $seller_shopinfo['credit_money'];
                }

                if ($seller_shopinfo && $seller_shopinfo['credit'] > 0 && $seller_shopinfo['credit'] >= $amount) {
                    $adminru = get_admin_ru_id();

                    $change_desc = lang('admin/order.action_user') . "：【" . $adminru['user_name'] . "】，" . sprintf(lang('admin/order.order_refund'), $order['order_sn']) . $refund_note;
                    $log = [
                        'user_id' => $order['ru_id'],
                        'user_money' => (-1) * $amount,
                        'change_time' => gmtime(),
                        'change_desc' => $change_desc,
                        'change_type' => 2
                    ];
                    MerchantsAccountLog::insert($log);

                    SellerShopinfo::where('ru_id', $order['ru_id'])->increment('seller_money', $log['user_money']);
                } else {
                    $is_ok = 0;
                }
            }

            if ($is_ok == 1) {
                log_account_change($user_id, $amount, 0, 0, 0, $change_desc);
            } else {
                /* 返回失败，不允许退款 */
                return 2;
            }
        }

        return 1;
    } elseif (2 == $refund_type) {
        return true;
    } elseif (22222 == $refund_type) {
        /* 如果非匿名，退回余额 */
        if ($user_id > 0) {
            log_account_change($user_id, $amount, 0, 0, 0, $change_desc);
        }

        /* user_account 表增加提款申请记录 */
        $account = [
            'user_id' => $user_id,
            'amount' => DB::raw((-1) * $amount),
            'add_time' => gmtime(),
            'user_note' => $refund_note,
            'process_type' => SURPLUS_RETURN,
            'admin_user' => session('admin_name', ''),
            'admin_note' => sprintf(lang('admin/order.order_refund'), $order['order_sn']),
            'is_paid' => 0
        ];

        UserAccount::insert($account);

        return 1;
    } /*  @bylu 白条退款 start */
    elseif (5 == $refund_type) {

        //查询当前退款订单使用了多少余额支付;
        $surplus = OrderInfo::where('order_id', $order['order_id'])->value('surplus');

        //余额退余额,白条退白条;
        if ($surplus != 0.00) {
            log_account_change($user_id, $surplus, 0, 0, 0, lang('baitiao.baitiao') . $change_desc);
        } else {
            $baitiao_info = BaitiaoLog::where('order_id', $order['order_id']);
            $baitiao_info = BaseRepository::getToArrayFirst($baitiao_info);

            if ($baitiao_info['is_stages'] == 1) {
                $surplus = $baitiao_info['yes_num'] * $baitiao_info['stages_one_price'];
                log_account_change($user_id, $surplus, 0, 0, 0, lang('baitiao.baitiao_stages') . $change_desc);
            } else {
                $surplus = $order['order_amount'];
                log_account_change($user_id, $surplus, 0, 0, 0, lang('baitiao.baitiao') . $change_desc);
            }
        }

        //将当前退款订单的白条记录表中的退款信息变更为"退款";
        BaitiaoLog::where('order_id', $order['order_id'])->update(['is_refund' => 1]);
        return 1;
    } /*  @bylu 白条退款 end */
    else {
        return 1;
    }
}

/**
 * 退换货 用户积分退还
 * by Leah
 */
function return_surplus_integral_bonus($user_id, $goods_price, $return_goods_price)
{
    $pay_points = Users::where('user_id', $user_id)->value('pay_points');

    $pay_points = $pay_points - $goods_price + $return_goods_price;

    if ($pay_points > 0) {
        $other = [
            'pay_points' => $pay_points
        ];
        Users::where('user_id', $user_id)->update($other);
    }
}

/**
 * 取得某用户等级当前时间可以享受的优惠活动
 *
 * @param int $user_rank 用户等级id，0表示非会员
 * @param int $seller_id
 * @param int $fav_id 优惠活动ID
 * @param array $act_sel_id
 * @param int $ru_id 商家id
 * @param int $uid 会员ID
 * @return array
 */
function favourable_list($user_rank = 0, $seller_id = -1, $fav_id = 0, $act_sel_id = [], $ru_id = -1, $uid = 0)
{
    /* 购物车中已有的优惠活动及数量 */
    $used_list = cart_favourable($ru_id, $uid);

    /* 当前用户可享受的优惠活动 */
    $favourable_list = [];
    $user_rank = ',' . $user_rank . ',';
    $now = TimeRepository::getGmTime();

    $res = FavourableActivity::where('review_status', 3)
        ->where('start_time', '<=', $now)
        ->where('end_time', '>=', $now);

    $res = $res->whereRaw("CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'");

    if ($seller_id >= 0) {
        $res = $res->whereRaw("IF(userFav_type = 0, user_id = '$seller_id', 1 = 1)");
    }

    if ($fav_id > 0) {
        $res = $res->where('act_id', $fav_id);
    }

    $res = $res->orderBy('sort_order');

    $res = BaseRepository::getToArrayGet($res);

    if ($res) {
        foreach ($res as $favourable) {
            $favourable['start_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $favourable['start_time']);
            $favourable['end_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $favourable['end_time']);
            $favourable['formated_min_amount'] = app(DscRepository::class)->getPriceFormat($favourable['min_amount'], false);
            $favourable['formated_max_amount'] = app(DscRepository::class)->getPriceFormat($favourable['max_amount'], false);

            if ($favourable['gift']) {
                $favourable['gift'] = unserialize($favourable['gift']);

                /* 购物车商品赠品 */
                $cartGiftList = CartDataHandleService::getCartGiftDataList($favourable['act_id'], $uid);

                $goods_id = BaseRepository::getKeyPluck($favourable['gift'], 'id');
                $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'goods_thumb']);
                foreach ($favourable['gift'] as $key => $value) {

                    $favourable['gift'][$key]['formated_price'] = app(DscRepository::class)->getPriceFormat($value['price'], false);

                    $cartGift = $cartGiftList[$value['id']] ?? [];

                    if ($cartGift) {
                        $favourable['gift'][$key]['is_checked'] = true;
                    } else {
                        $favourable['gift'][$key]['is_checked'] = false;
                    }

                    // 赠品缩略图
                    $goods = $goodsList[$value['id']] ?? [];

                    $favourable['gift'][$key]['thumb_img'] = $goods ? app(DscRepository::class)->getImagePath($goods['goods_thumb']) : '';

                    if (!$goods) {
                        unset($favourable['gift'][$key]);
                    }
                }
            }

            $favourable['act_range_desc'] = act_range_desc($favourable);

            $lang_act_type = isset($GLOBALS['_LANG']['fat_ext'][$favourable['act_type']]) ? $GLOBALS['_LANG']['fat_ext'][$favourable['act_type']] : '';
            $favourable['act_type_desc'] = sprintf($lang_act_type, $favourable['act_type_ext']);

            /* 是否能享受 */
            $favourable['available'] = favourable_available($favourable, $act_sel_id, -1, $uid, $user_rank);

            if ($favourable['available']) {
                /* 是否尚未享受 */
                $favourable['available'] = !favourable_used($favourable, $used_list);
            }

            $favourable['act_range_ext'] = act_range_ext_brand($favourable['act_range_ext'], $favourable['userFav_type'], $favourable['act_range']);

            $favourable_list[] = $favourable;
        }
    }

    return $favourable_list;
}

/**
 * 取得购物车中已有的优惠活动及数量
 *
 * @param int $ru_id
 * @param int $user_id
 * @return array
 */
function cart_favourable($ru_id = -1, $user_id = 0)
{
    if (!$user_id) {
        $user_id = session('user_id', 0);
    }

    $res = Cart::selectRaw('is_gift, COUNT(*) AS num')
        ->where('rec_type', CART_GENERAL_GOODS)
        ->where('is_gift', '>', 0);

    if (!empty($user_id)) {
        $res = $res->where('user_id', $user_id);
    } else {
        $session_id = app(SessionRepository::class)->realCartMacIp();
        $res = $res->where('session_id', $session_id);
    }

    if ($ru_id > -1) {
        $res = $res->where('ru_id', $ru_id);
    }

    $res = $res->groupBy('is_gift');

    $res = BaseRepository::getToArrayGet($res);

    $list = [];
    if ($res) {
        foreach ($res as $row) {
            $list[$row['is_gift']] = $row['num'];
        }
    }

    return $list;
}

/**
 * 购物车中是否已经有某优惠
 *
 * @param array $favourable 优惠活动
 * @param array $cart_favourable 购物车中已有的优惠活动及数量
 * @return bool
 */
function favourable_used($favourable = [], $cart_favourable = [])
{
    if ($favourable['act_type'] == FAT_GOODS) {
        return isset($cart_favourable[$favourable['act_id']]) &&
            $cart_favourable[$favourable['act_id']] >= $favourable['act_type_ext'] &&
            $favourable['act_type_ext'] > 0;
    } else {
        return isset($cart_favourable[$favourable['act_id']]);
    }
}

/**
 * 取得优惠范围描述
 * @param array $favourable 优惠活动
 * @return  string
 */
function act_range_desc($favourable)
{
    if ($favourable && $favourable['act_range_ext']) {
        $act_range_ext = !is_array($favourable['act_range_ext']) ? explode(",", $favourable['act_range_ext']) : $favourable['act_range_ext'];

        if ($favourable['act_range'] == FAR_BRAND) {
            $brand_name = Brand::selectRaw("GROUP_CONCAT(brand_name) AS brand_name")->whereIn('brand_id', $act_range_ext)->value('brand_name');
            $brand_name = $brand_name ? $brand_name : '';

            return $brand_name;
        } elseif ($favourable['act_range'] == FAR_CATEGORY) {
            $cat_name = Category::selectRaw("GROUP_CONCAT(cat_name) AS cat_name")->whereIn('cat_id', $act_range_ext)->value('cat_name');
            $cat_name = $cat_name ? $cat_name : '';

            return $cat_name;
        } elseif ($favourable['act_range'] == FAR_GOODS) {
            $goods_name = Goods::selectRaw("GROUP_CONCAT(goods_name) AS goods_name")->whereIn('goods_id', $act_range_ext)->value('goods_name');
            $goods_name = $goods_name ? $goods_name : '';

            return $goods_name;
        }
    }

    return '';
}

/**
 * 根据购物车判断是否可以享受某优惠活动
 *
 * @param array $favourable 优惠活动信息
 * @param array $act_sel_id 购物车选中的商品id
 * @param int $ru_id
 * @param int $uid
 * @param int $rank_id
 * @return bool
 */
function favourable_available($favourable = [], $act_sel_id = [], $ru_id = -1, $uid = 0, $rank_id = 0)
{

    /* 会员等级是否符合 */
    if ($rank_id) {
        $user_rank = $rank_id;
    } else {
        $user_rank = session('user_rank', 1);
    }

    $user_rank = trim($user_rank, ',');

    $favourable_user_rank = isset($favourable['user_rank']) && !empty($favourable['user_rank']) ? explode(',', $favourable['user_rank']) : [];

    if (!in_array($user_rank, $favourable_user_rank)) {
        return false;
    }

    /* 优惠范围内的商品总额 */
    $amount = cart_favourable_amount($favourable, $act_sel_id, $ru_id, $uid);

    /* 金额上限为0表示没有上限 */
    return $amount >= $favourable['min_amount'] &&
        ($amount <= $favourable['max_amount'] || $favourable['max_amount'] == 0);
}

/**
 * @param array $favourable 取得购物车中某优惠活动范围内的总金额
 * @param array $act_sel_id 优惠活动
 * @param int $ru_id
 * @param int $user_id 购物车选中的商品id
 * @return mixed
 */
function cart_favourable_amount($favourable = [], $act_sel_id = ['act_sel_id' => '', 'act_pro_sel_id' => '', 'act_sel' => ''], $ru_id = -1, $user_id = 0)
{
    if (empty($user_id)) {
        $user_id = session('user_id', 0);
    }

    $id_list = [];
    $list_array = [];

    /* 优惠范围内的商品总额 */
    $amount = Cart::selectRaw("SUM(goods_price * goods_number) AS price")
        ->whereIn('rec_type', [CART_GENERAL_GOODS, CART_ONESTEP_GOODS])
        ->where('is_gift', 0)
        ->where('is_checked', 1)
        ->where('parent_id', 0);

    if (!empty($user_id)) {
        $amount = $amount->where('user_id', $user_id);
    } else {
        $session_id = app(SessionRepository::class)->realCartMacIp();
        $amount = $amount->where('user_id', $session_id);
    }

    if ($favourable['userFav_type'] == 0) {
        $amount = $amount->where('ru_id', $favourable['user_id']);
    } else {
        if ($ru_id > -1) {
            $amount = $amount->where('ru_id', $ru_id);
        }
    }

    $sel_id_list = isset($act_sel_id['act_sel_id']) && $act_sel_id['act_sel_id'] ? $act_sel_id['act_sel_id'] : '';

    if ($sel_id_list && !empty($act_sel_id['act_sel']) && ($act_sel_id['act_sel'] == 'cart_sel_flag')) {
        $sel_id_list = !is_array($sel_id_list) ? explode(',', $sel_id_list) : $sel_id_list;
        $amount = $amount->whereIn('rec_id', $sel_id_list);
    }

    if (isset($act_sel_id['act_sel_id']) && !empty($act_sel_id['act_sel_id'])) {
        $act_sel_id = !is_array($act_sel_id['act_sel_id']) ? explode(',', $act_sel_id['act_sel_id']) : $act_sel_id['act_sel_id'];
        $amount = $amount->whereIn('rec_id', $act_sel_id);
    } else {
        $amount = $amount->where('rec_id', 0);
    }

    $CategoryLib = app(CategoryService::class);

    if ($favourable['act_range_ext']) {
        /* 根据优惠范围修正sql */
        if ($favourable['act_range'] == FAR_ALL) {
            // sql do not change
        } elseif ($favourable['act_range'] == FAR_CATEGORY) {

            /* 取得优惠范围分类的所有下级分类 */
            $cat_list = explode(',', $favourable['act_range_ext']);

            $str_cat = '';
            foreach ($cat_list as $id) {
                /**
                 * 当前分类下的所有子分类
                 * 返回一维数组
                 */
                $cat_keys = $CategoryLib->getCatListChildren(intval($id));

                if ($cat_keys) {
                    $str_cat .= implode(",", $cat_keys);
                }
            }

            if ($str_cat) {
                $list_array = explode(",", $str_cat);
            }

            $list_array = !empty($list_array) ? array_merge($cat_list, $list_array) : $cat_list;
            $id_list = arr_foreach($list_array);
            $id_list = array_unique($id_list);
        } elseif ($favourable['act_range'] == FAR_BRAND) {
            $id_list = explode(',', $favourable['act_range_ext']);

            if ($favourable['userFav_type'] == 1 && $id_list) {
                $id_list = implode(",", $id_list);
                $id_list = act_range_ext_brand($favourable['act_range_ext'], $favourable['userFav_type'], $favourable['act_range']);
                $id_list = explode(",", $id_list);
            }
        } else {
            $id_list = explode(',', $favourable['act_range_ext']);
        }
    }

    $where = [
        'id_list' => $id_list,
        'act_range' => $favourable['act_range'],
        'range_type' => [
            'all' => FAR_ALL,
            'category' => FAR_CATEGORY,
            'brand' => FAR_BRAND
        ]
    ];
    $amount = $amount->whereHasIn('getGoods', function ($query) use ($where) {
        if ($where['id_list']) {
            $where['id_list'] = !is_array($where['id_list']) ? explode(',', $where['id_list']) : $where['id_list'];

            if ($where['act_range'] == $where['range_type']['all']) {
                // sql do not change
            } elseif ($where['act_range'] == $where['range_type']['category']) {
                $query->whereIn('cat_id', $where['id_list']);
            } elseif ($where['act_range'] == $where['range_type']['brand']) {
                $query->whereIn('brand_id', $where['id_list']);
            } else {
                $query->whereIn('goods_id', $where['id_list']);
            }
        }
    });

    if ($favourable && $favourable['act_id']) {
        $amount = $amount->where('act_id', $favourable['act_id']);
    }

    $amount = BaseRepository::getToArrayFirst($amount);

    $amount = $amount ? $amount['price'] : 0;

    return $amount;
}

// 对优惠商品进行归类
function sort_favourable($favourable_list)
{
    $arr = [];
    foreach ($favourable_list as $key => $value) {
        switch ($value['act_range']) {
            case FAR_ALL:
                $arr['by_all'][$key] = $value;
                break;
            case FAR_CATEGORY:
                $arr['by_category'][$key] = $value;
                break;
            case FAR_BRAND:
                $arr['by_brand'][$key] = $value;
                break;
            case FAR_GOODS:
                $arr['by_goods'][$key] = $value;
                break;
            default:
                break;
        }
    }
    return $arr;
}

// 同一商家所有优惠活动包含的所有优惠范围 -qin
function get_act_range_ext($user_rank, $user_id = 0, $act_range)
{
    /* 当前用户可享受的优惠活动 */
    $user_rank = ',' . $user_rank . ',';
    $now = TimeRepository::getGmTime();

    $res = FavourableActivity::where('review_status', 3)
        ->where('start_time', '<=', $now)
        ->where('end_time', '>=', $now);

    $res = $res->whereRaw("CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'");

    if ($user_id >= 0) {
        $res = $res->whereRaw("IF(userFav_type = 0, user_id = '$user_id', 1 = 1)");
    }

    if ($act_range > 0) {
        $res = $res->where('act_range', $act_range);
    }

    $res = $res->orderBy('sort_order');

    $res = BaseRepository::getToArrayGet($res);

    $arr = [];
    if ($res) {
        foreach ($res as $key => $row) {
            $row['act_range_ext'] = act_range_ext_brand($row['act_range_ext'], $row['userFav_type'], $row['act_range']);
            $id_list = explode(',', $row['act_range_ext']);
            $arr = array_merge($arr, $id_list);
        }
    }

    return array_unique($arr);
}

// 获取活动id数组
function get_favourable_id($favourable)
{
    $arr = [];
    foreach ($favourable as $key => $value) {
        $arr[$key] = $value['act_id'];
    }

    return $arr;
}

/* 查询订单商家ID */
function get_order_seller_id($order = '', $type = 0)
{
    if ($type == 1) {
        $res = OrderGoods::select('ru_id')
            ->whereHasIn('getOrder', function ($query) use ($order) {
                $query->where('order_sn', $order);
            });
    } else {
        $res = OrderGoods::select('ru_id')->where('order_id', $order);
    }

    $res = BaseRepository::getToArrayFirst($res);

    return $res;
}

/* 查询是否主订单商家 */
function get_order_main_child($order = '', $type = 0)
{
    $res = OrderInfo::whereRaw(1);

    if ($type == 1) {
        $res = $res->where('order_sn', $order);
        $order_id = $res->value('order_id');
    } else {
        $order_id = $order;
    }

    $child_count = OrderInfo::where('main_order_id', $order_id)->count();

    return $child_count;
}

//是否启用白条支付
function get_payment_code($code = 'chunsejinrong')
{
    $PaymentLib = app(PaymentService::class);
    $where = [
        'pay_code' => $code,
        'enabled' => 1
    ];
    $pament = $PaymentLib->getPaymentInfo($where);

    return $pament;
}

/**
 * 获取退款后的订单状态数组
 *
 * @param int $goods_number_return
 * @param int $rec_id
 * @param array $order_goods
 * @return array
 */
function get_order_arr($goods_number_return = 0, $rec_id = 0, $order_goods = [])
{
    $goods_number = 0;
    $goods_count = count($order_goods);
    $i = 1;

    if ($order_goods) {
        foreach ($order_goods as $k => $v) {
            if ($rec_id == $v['rec_id']) {
                $goods_number = $v['goods_number'];
            }

            $count = OrderReturn::where('rec_id', $v['rec_id'])
                ->where('order_id', $v['order_id'])
                ->where('refound_status', 1)
                ->count();

            if ($count > 0) {
                $i++;
            }
        }
    }

    if ($goods_number > $goods_number_return || $goods_count > $i) {
        //单品退货
        $arr = [
            'order_status' => OS_RETURNED_PART,
            'pay_status' => PS_REFOUND_PART,
        ];
    } else {
        //整单退货
        $arr = [
            'order_status' => OS_RETURNED,
            'pay_status' => PS_REFOUND,
            'money_paid' => 0,
            'invoice_no' => '',
            'order_amount' => 0
        ];
    }
    return $arr;
}

/* 获取购物车中同一活动下的商品和赠品 -qin
 *
 * 来源flow.php 转移函数
 *
 * $favourable_id int 优惠活动id
 * $act_sel_id string 活动中选中的cart id
 */
function cart_favourable_box($favourable_id, $act_sel_id = [], $user_id = 0, $rank_id = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0)
{

    $sel_ru_id = $act_sel_id['ru_id'] ?? -1;

    /* 会员ID */
    if (empty($user_id)) {
        $user_id = session()->has('user_id') ? session('user_id', 0) : 0;
    }

    $CategoryLib = app(CategoryService::class);

    $rank_id = $rank_id ? $rank_id : session('user_rank', 1);

    $fav_res = favourable_list($rank_id, -1, $favourable_id, $act_sel_id, -1, $user_id);
    $favourable_activity = $fav_res[0] ?? [];
    $favourable_activity['act_id'] = $favourable_activity['act_id'] ?? 0;
    $favourable_activity['act_range'] = $favourable_activity['act_range'] ?? '';

    /* 识别pc和wap */
    if (isset($act_sel_id['from']) && $act_sel_id['from'] == 'mobile') {
        //WAP端
        $cart_value = isset($act_sel_id['act_sel_id']) && !empty($act_sel_id['act_sel_id']) ? addslashes($act_sel_id['act_sel_id']) : 0;
    } else {
        //PC端
        $cart_value = isset($act_sel_id['act_pro_sel_id']) && !empty($act_sel_id['act_pro_sel_id']) ? addslashes($act_sel_id['act_pro_sel_id']) : 0;
    }

    $cart_goods = get_cart_goods($cart_value, 1, $user_id, $favourable_id);

    $merchant_goods = $cart_goods['goods_list'];

    $favourable_box = [];

    if ($cart_goods['total']['goods_price']) {
        $favourable_box['goods_amount'] = $cart_goods['total']['goods_price'];
    }

    $list_array = [];
    foreach ($merchant_goods as $key => $row) { // 第一层 遍历商家
        $user_cart_goods = $row['goods_list'];

        if ($favourable_activity['userFav_type'] == 1 || ($favourable_activity['userFav_type'] == 0 && $row['ru_id'] == $favourable_activity['user_id'])) { //判断是否商家活动
            foreach ($user_cart_goods as $goods_key => $goods_row) { // 第二层 遍历购物车中商家的商品

                $goods_row['original_price'] = $goods_row['goods_price'] * $goods_row['goods_number'];

                if (!empty($act_sel_id)) { // 用来判断同一个优惠活动前面是否全部不选
                    $goods_row['sel_checked'] = strstr(',' . $act_sel_id['act_sel_id'] . ',', ',' . $goods_row['rec_id'] . ',') ? 1 : 0; // 选中为1
                }

                if ($goods_row['act_id'] == $favourable_activity['act_id'] || empty($goods_row['act_id'])) {
                    // 活动-全部商品
                    if ($favourable_activity['act_range'] == 0 && $goods_row['extension_code'] != 'package_buy') {
                        if ($goods_row['is_gift'] == FAR_ALL && $goods_row['parent_id'] == 0) { // 活动商品

                            $favourable_box['act_id'] = $favourable_activity['act_id'];

                            if ($favourable_activity['userFav_type'] == 1) {
                                $favourable_box['act_name'] = "[" . lang('common.general_audience') . "]" . $favourable_activity['act_name'];
                            } else {
                                $favourable_box['act_name'] = $favourable_activity['act_name'];
                            }

                            $favourable_box['act_type'] = $favourable_activity['act_type'];
                            $favourable_box['userFav_type'] = $favourable_activity['userFav_type'];

                            // 活动类型
                            switch ($favourable_activity['act_type']) {
                                case 0:
                                    $favourable_box['act_type_txt'] = lang('flow.With_a_gift');
                                    $favourable_box['act_type_ext_format'] = intval($favourable_activity['act_type_ext']); // 可领取总件数
                                    break;
                                case 1:
                                    $favourable_box['act_type_txt'] = lang('flow.Full_reduction');
                                    $favourable_box['act_type_ext_format'] = number_format($favourable_activity['act_type_ext'], 2); // 满减金额
                                    break;
                                case 2:
                                    $favourable_box['act_type_txt'] = lang('flow.discount');
                                    $favourable_box['act_type_ext_format'] = floatval($favourable_activity['act_type_ext'] / 10); // 折扣百分比
                                    break;

                                default:
                                    break;
                            }
                            $favourable_box['min_amount'] = $favourable_activity['min_amount'];
                            $favourable_box['act_type_ext'] = intval($favourable_activity['act_type_ext']); // 可领取总件数
                            $favourable_box['cart_fav_amount'] = cart_favourable_amount($favourable_activity, $act_sel_id, $goods_row['ru_id'], $user_id);
                            $favourable_box['available'] = favourable_available($favourable_activity, $act_sel_id, $goods_row['ru_id'], $user_id, $rank_id); // 购物车满足活动最低金额
                            if ($favourable_box['available'] && $favourable_activity['act_type'] == 2) {//折扣显示折扣金额
                                $favourable_box['goods_fav_amount'] = $favourable_box['cart_fav_amount'] * floatval(100 - $favourable_activity['act_type_ext']) / 100;
                                $favourable_box['goods_fav_amount_format'] = app(DscRepository::class)->getPriceFormat($favourable_box['goods_fav_amount']);
                            }
                            // 购物车中已选活动赠品数量
                            $cart_favourable = cart_favourable($goods_row['ru_id'], $user_id);
                            $favourable_box['cart_favourable_gift_num'] = empty($cart_favourable[$favourable_activity['act_id']]) ? 0 : intval($cart_favourable[$favourable_activity['act_id']]);
                            $favourable_box['favourable_used'] = favourable_used($favourable_activity, $cart_favourable);
                            $favourable_box['left_gift_num'] = intval($favourable_activity['act_type_ext']) - (empty($cart_favourable[$favourable_activity['act_id']]) ? 0 : intval($cart_favourable[$favourable_activity['act_id']]));

                            // 活动赠品
                            if ($favourable_activity['gift']) {
                                $favourable_box['act_gift_list'] = $favourable_activity['gift'];
                            }

                            $goods_row['favourable_list'] = get_favourable_info($goods_row['goods_id'], $goods_row['ru_id'], $goods_row, $rank_id);

                            // new_list->活动id->act_goods_list
                            $favourable_box['act_goods_list'][$goods_row['rec_id']] = $goods_row;
                        }
                        // 赠品
                        if ($goods_row['is_gift'] == $favourable_activity['act_id']) {
                            $favourable_box['act_cart_gift'][$goods_row['rec_id']] = $goods_row;
                        }
                        continue; // 如果活动包含全部商品，跳出循环体
                    }

                    if (empty($goods_row)) {
                        continue;
                    }

                    // 活动-分类
                    if ($favourable_activity['act_range'] == FAR_CATEGORY && $goods_row['extension_code'] != 'package_buy') {
                        // 优惠活动关联的 分类集合
                        $get_act_range_ext = get_act_range_ext($rank_id, $row['ru_id'], 1); // 1表示优惠范围 按分类

                        $str_cat = '';
                        foreach ($get_act_range_ext as $id) {

                            /**
                             * 当前分类下的所有子分类
                             * 返回一维数组
                             */
                            $cat_keys = $CategoryLib->getCatListChildren(intval($id));

                            if ($cat_keys) {
                                $str_cat .= implode(",", $cat_keys);
                            }
                        }

                        if ($str_cat) {
                            $list_array = explode(",", $str_cat);
                        }

                        $list_array = !empty($list_array) ? array_merge($get_act_range_ext, $list_array) : $get_act_range_ext;
                        $id_list = arr_foreach($list_array);
                        $id_list = array_unique($id_list);
                        $cat_id = $goods_row['cat_id']; //购物车商品所属分类ID

                        // 判断商品或赠品 是否属于本优惠活动
                        if ((in_array(trim($cat_id), $id_list) && $goods_row['is_gift'] == 0 && $goods_row['parent_id'] == 0) || ($goods_row['is_gift'] == $favourable_activity['act_id'])) {
                            if ($goods_row['act_id'] == $favourable_activity['act_id'] || empty($goods_row['act_id'])) {
                                //优惠活动关联分类集合
                                $fav_act_range_ext = !empty($favourable_activity['act_range_ext']) ? explode(',', $favourable_activity['act_range_ext']) : [];

                                // 此 优惠活动所有分类
                                foreach ($fav_act_range_ext as $id) {
                                    /**
                                     * 当前分类下的所有子分类
                                     * 返回一维数组
                                     */
                                    $cat_keys = $CategoryLib->getCatListChildren(intval($id));
                                    $fav_act_range_ext = array_merge($fav_act_range_ext, $cat_keys);
                                }

                                if ($goods_row['is_gift'] == 0 && in_array($cat_id, $fav_act_range_ext)) { // 活动商品
                                    $favourable_box['act_id'] = $favourable_activity['act_id'];

                                    if ($favourable_activity['userFav_type'] == 1) {
                                        $favourable_box['act_name'] = "[" . lang('common.general_audience') . "]" . $favourable_activity['act_name'];
                                    } else {
                                        $favourable_box['act_name'] = $favourable_activity['act_name'];
                                    }

                                    $favourable_box['act_type'] = $favourable_activity['act_type'];
                                    $favourable_box['userFav_type'] = $favourable_activity['userFav_type'];
                                    // 活动类型
                                    switch ($favourable_activity['act_type']) {
                                        case 0:
                                            $favourable_box['act_type_txt'] = lang('flow.With_a_gift');
                                            $favourable_box['act_type_ext_format'] = intval($favourable_activity['act_type_ext']); // 可领取总件数
                                            break;
                                        case 1:
                                            $favourable_box['act_type_txt'] = lang('flow.Full_reduction');
                                            $favourable_box['act_type_ext_format'] = number_format($favourable_activity['act_type_ext'], 2); // 满减金额
                                            break;
                                        case 2:
                                            $favourable_box['act_type_txt'] = lang('flow.discount');
                                            $favourable_box['act_type_ext_format'] = floatval($favourable_activity['act_type_ext'] / 10); // 折扣百分比
                                            break;

                                        default:
                                            break;
                                    }
                                    $favourable_box['min_amount'] = $favourable_activity['min_amount'];
                                    $favourable_box['act_type_ext'] = intval($favourable_activity['act_type_ext']); // 可领取总件数
                                    $favourable_box['cart_fav_amount'] = cart_favourable_amount($favourable_activity, $act_sel_id, $goods_row['ru_id'], $user_id);
                                    $favourable_box['available'] = favourable_available($favourable_activity, $act_sel_id, $goods_row['ru_id'], $user_id, $rank_id); // 购物车满足活动最低金额
                                    if ($favourable_box['available'] && $favourable_activity['act_type'] == 2) {//折扣显示折扣金额
                                        $favourable_box['goods_fav_amount'] = $favourable_box['cart_fav_amount'] * floatval(100 - $favourable_activity['act_type_ext']) / 100;
                                        $favourable_box['goods_fav_amount_format'] = app(DscRepository::class)->getPriceFormat($favourable_box['goods_fav_amount']);
                                    }

                                    // 购物车中已选活动赠品数量
                                    $cart_favourable = cart_favourable($goods_row['ru_id'], $user_id);
                                    $favourable_box['cart_favourable_gift_num'] = empty($cart_favourable[$favourable_activity['act_id']]) ? 0 : intval($cart_favourable[$favourable_activity['act_id']]);
                                    $favourable_box['favourable_used'] = favourable_used($favourable_activity, $cart_favourable);
                                    $favourable_box['left_gift_num'] = intval($favourable_activity['act_type_ext']) - (empty($cart_favourable[$favourable_activity['act_id']]) ? 0 : intval($cart_favourable[$favourable_activity['act_id']]));

                                    //活动赠品
                                    if ($favourable_activity['gift']) {
                                        $favourable_box['act_gift_list'] = $favourable_activity['gift'];
                                    }

                                    $goods_row['favourable_list'] = get_favourable_info($goods_row['goods_id'], $goods_row['ru_id'], $goods_row, $rank_id);

                                    // new_list->活动id->act_goods_list
                                    $favourable_box['act_goods_list'][$goods_row['rec_id']] = $goods_row;
                                    $favourable_box['act_goods_list_num'] = count($favourable_box['act_goods_list']);
                                }
                                if ($goods_row['is_gift'] == $favourable_activity['act_id']) { // 赠品
                                    $favourable_box['act_cart_gift'][$goods_row['rec_id']] = $goods_row;
                                }
                            }

                            continue;
                        }
                    }

                    if (empty($goods_row)) {
                        continue;
                    }

                    // 活动-品牌
                    if ($favourable_activity['act_range'] == FAR_BRAND && $goods_row['extension_code'] != 'package_buy') {
                        // 优惠活动 品牌集合
                        $get_act_range_ext = get_act_range_ext($rank_id, $row['ru_id'], 2); // 2表示优惠范围 按品牌
                        $brand_id = $goods_row['brand_id'];

                        // 是品牌活动的商品或者赠品
                        if ((in_array(trim($brand_id), $get_act_range_ext) && $goods_row['is_gift'] == 0 && $goods_row['parent_id'] == 0) || ($goods_row['is_gift'] == $favourable_activity['act_id'])) {
                            if ($goods_row['act_id'] == $favourable_activity['act_id'] || empty($goods_row['act_id'])) {
                                $act_range_ext_str = ',' . $favourable_activity['act_range_ext'] . ',';
                                $brand_id_str = ',' . $brand_id . ',';

                                if ($goods_row['is_gift'] == 0 && strstr($act_range_ext_str, trim($brand_id_str))) { // 活动商品
                                    $favourable_box['act_id'] = $favourable_activity['act_id'];

                                    if ($favourable_activity['userFav_type'] == 1) {
                                        $favourable_box['act_name'] = "[" . lang('common.general_audience') . "]" . $favourable_activity['act_name'];
                                    } else {
                                        $favourable_box['act_name'] = $favourable_activity['act_name'];
                                    }

                                    $favourable_box['act_type'] = $favourable_activity['act_type'];
                                    $favourable_box['userFav_type'] = $favourable_activity['userFav_type'];
                                    // 活动类型
                                    switch ($favourable_activity['act_type']) {
                                        case 0:
                                            $favourable_box['act_type_txt'] = lang('flow.With_a_gift');
                                            $favourable_box['act_type_ext_format'] = intval($favourable_activity['act_type_ext']); // 可领取总件数
                                            break;
                                        case 1:
                                            $favourable_box['act_type_txt'] = lang('flow.Full_reduction');
                                            $favourable_box['act_type_ext_format'] = number_format($favourable_activity['act_type_ext'], 2); // 满减金额
                                            break;
                                        case 2:
                                            $favourable_box['act_type_txt'] = lang('flow.discount');
                                            $favourable_box['act_type_ext_format'] = floatval($favourable_activity['act_type_ext'] / 10); // 折扣百分比
                                            break;

                                        default:
                                            break;
                                    }
                                    $favourable_box['min_amount'] = $favourable_activity['min_amount'];
                                    $favourable_box['act_type_ext'] = intval($favourable_activity['act_type_ext']); // 可领取总件数
                                    $favourable_box['cart_fav_amount'] = cart_favourable_amount($favourable_activity, $act_sel_id, $goods_row['ru_id'], $user_id);
                                    $favourable_box['available'] = favourable_available($favourable_activity, $act_sel_id, $goods_row['ru_id'], $user_id, $rank_id); // 购物车满足活动最低金额
                                    if ($favourable_box['available'] && $favourable_activity['act_type'] == 2) {//折扣显示折扣金额
                                        $favourable_box['goods_fav_amount'] = $favourable_box['cart_fav_amount'] * floatval(100 - $favourable_activity['act_type_ext']) / 100;
                                        $favourable_box['goods_fav_amount_format'] = app(DscRepository::class)->getPriceFormat($favourable_box['goods_fav_amount']);
                                    }
                                    // 购物车中已选活动赠品数量
                                    $cart_favourable = cart_favourable($goods_row['ru_id'], $user_id);
                                    $favourable_box['cart_favourable_gift_num'] = empty($cart_favourable[$favourable_activity['act_id']]) ? 0 : intval($cart_favourable[$favourable_activity['act_id']]);
                                    $favourable_box['favourable_used'] = favourable_used($favourable_activity, $cart_favourable);
                                    $favourable_box['left_gift_num'] = intval($favourable_activity['act_type_ext']) - (empty($cart_favourable[$favourable_activity['act_id']]) ? 0 : intval($cart_favourable[$favourable_activity['act_id']]));

                                    //活动赠品
                                    if ($favourable_activity['gift']) {
                                        $favourable_box['act_gift_list'] = $favourable_activity['gift'];
                                    }

                                    $goods_row['favourable_list'] = get_favourable_info($goods_row['goods_id'], $goods_row['ru_id'], $goods_row, $rank_id);

                                    // new_list->活动id->act_goods_list
                                    $favourable_box['act_goods_list'][$goods_row['rec_id']] = $goods_row;
                                }
                                if ($goods_row['is_gift'] == $favourable_activity['act_id']) { // 赠品
                                    $favourable_box['act_cart_gift'][$goods_row['rec_id']] = $goods_row;
                                }
                            }

                            continue;
                        }
                    }

                    if (empty($goods_row)) {
                        continue;
                    }

                    // 活动-部分商品
                    if ($favourable_activity['act_range'] == FAR_GOODS && $goods_row['extension_code'] != 'package_buy') {
                        $get_act_range_ext = get_act_range_ext($rank_id, $row['ru_id'], 3); // 3表示优惠范围 按商品
                        // 判断购物商品是否参加了活动  或者  该商品是赠品
                        if (in_array($goods_row['goods_id'], $get_act_range_ext) || ($goods_row['is_gift'] == $favourable_activity['act_id'])) {
                            if ($goods_row['act_id'] == $favourable_activity['act_id'] || empty($goods_row['act_id'])) {
                                $act_range_ext_str = ',' . $favourable_activity['act_range_ext'] . ','; // 优惠活动中的优惠商品
                                $goods_id_str = ',' . $goods_row['goods_id'] . ',';

                                // 如果是活动商品
                                if (strstr($act_range_ext_str, trim($goods_id_str)) && $goods_row['is_gift'] == 0 && $goods_row['parent_id'] == 0) {
                                    $favourable_box['act_id'] = $favourable_activity['act_id'];

                                    if ($favourable_activity['userFav_type'] == 1) {
                                        $favourable_box['act_name'] = "[" . lang('common.general_audience') . "]" . $favourable_activity['act_name'];
                                    } else {
                                        $favourable_box['act_name'] = $favourable_activity['act_name'];
                                    }

                                    $favourable_box['act_type'] = $favourable_activity['act_type'];
                                    $favourable_box['userFav_type'] = $favourable_activity['userFav_type'];
                                    // 活动类型
                                    switch ($favourable_activity['act_type']) {
                                        case 0:
                                            $favourable_box['act_type_txt'] = lang('flow.With_a_gift');
                                            $favourable_box['act_type_ext_format'] = intval($favourable_activity['act_type_ext']); // 可领取总件数
                                            break;
                                        case 1:
                                            $favourable_box['act_type_txt'] = lang('flow.Full_reduction');
                                            $favourable_box['act_type_ext_format'] = number_format($favourable_activity['act_type_ext'], 2); // 满减金额
                                            break;
                                        case 2:
                                            $favourable_box['act_type_txt'] = lang('flow.discount');
                                            $favourable_box['act_type_ext_format'] = floatval($favourable_activity['act_type_ext'] / 10); // 折扣百分比
                                            break;

                                        default:
                                            break;
                                    }
                                    $favourable_box['min_amount'] = $favourable_activity['min_amount'];
                                    $favourable_box['act_type_ext'] = intval($favourable_activity['act_type_ext']); // 可领取总件数
                                    $favourable_box['cart_fav_amount'] = cart_favourable_amount($favourable_activity, $act_sel_id, $goods_row['ru_id'], $user_id);
                                    $favourable_box['available'] = favourable_available($favourable_activity, $act_sel_id, $goods_row['ru_id'], $user_id, $rank_id); // 购物车满足活动最低金额

                                    if ($favourable_box['available'] && $favourable_activity['act_type'] == 2) {//折扣显示折扣金额
                                        $favourable_box['goods_fav_amount'] = $favourable_box['cart_fav_amount'] * floatval(100 - $favourable_activity['act_type_ext']) / 100;
                                        $favourable_box['goods_fav_amount_format'] = app(DscRepository::class)->getPriceFormat($favourable_box['goods_fav_amount']);
                                    }

                                    // 购物车中已选活动赠品数量
                                    $cart_favourable = cart_favourable($goods_row['ru_id'], $user_id);
                                    $favourable_box['cart_favourable_gift_num'] = empty($cart_favourable[$favourable_activity['act_id']]) ? 0 : intval($cart_favourable[$favourable_activity['act_id']]);
                                    $favourable_box['favourable_used'] = favourable_used($favourable_box, $cart_favourable);
                                    $favourable_box['left_gift_num'] = intval($favourable_activity['act_type_ext']) - (empty($cart_favourable[$favourable_activity['act_id']]) ? 0 : intval($cart_favourable[$favourable_activity['act_id']]));

                                    // 活动赠品
                                    if ($favourable_activity['gift']) {
                                        $favourable_box['act_gift_list'] = $favourable_activity['gift'];
                                    }

                                    $goods_row['favourable_list'] = get_favourable_info($goods_row['goods_id'], $goods_row['ru_id'], $goods_row, $rank_id);

                                    // new_list->活动id->act_goods_list
                                    $favourable_box['act_goods_list'][$goods_row['rec_id']] = $goods_row;
                                }
                                // 如果是赠品
                                if ($goods_row['is_gift'] == $favourable_activity['act_id']) {
                                    $favourable_box['act_cart_gift'][$goods_row['rec_id']] = $goods_row;
                                }
                            }
                        }
                    } else {
                        if ($goods_row) {//如果循环完所有的活动都没有匹配的 那该商品就没有参加活动
                            $favourable_box[$goods_row['rec_id']] = $goods_row;
                        }
                    }
                } else {
                    $favourable_box[$goods_row['rec_id']] = $goods_row;
                }
            }
        }
    }

    if ($favourable_box) {

        if ($sel_ru_id > -1) {
            $sql = [
                'where' => [
                    [
                        'name' => 'ru_id',
                        'value' => $sel_ru_id
                    ]
                ]
            ];
            $favourable_box['act_goods_list'] = BaseRepository::getArraySqlGet($favourable_box['act_goods_list'], $sql);

            $seller_id = $sel_ru_id;
        } else {
            $seller_id = $merchant_goods[0]['ru_id'] ?? 0;
        }

        if ($favourable_box['act_type'] > 0) {

            /* 同一个活动的商品总金额 start */
            $cart_goods = get_cart_goods('', 0, $user_id);

            $list = $cart_goods['goods_list'] ?? [];

            $sql = [
                'whereIn' => [
                    [
                        'name' => 'act_id',
                        'value' => $favourable_box['act_id']
                    ]
                ],
                'where' => [
                    [
                        'name' => 'is_checked',
                        'value' => 1
                    ]
                ]
            ];
            $list = BaseRepository::getArraySqlGet($list, $sql);

            $amount = BaseRepository::getArraySum($list, ['goods_number', 'goods_price']);

            $favourable_box['cart_fav_amount'] = $amount;

            /* 同一个活动的商品总金额 end */

            /* 当前活动的商品总金额 start */
            $sql = [
                'where' => [
                    [
                        'name' => 'act_id',
                        'value' => $favourable_box['act_id']
                    ]
                ],
                'whereIn' => [
                    [
                        'name' => 'ru_id',
                        'value' => $seller_id
                    ]
                ]
            ];
            $act_goods_list = BaseRepository::getArraySqlGet($list, $sql);
            $goods_amount = BaseRepository::getArraySum($act_goods_list, ['goods_number', 'goods_price']);
            /* 当前活动的商品总金额 end */

            if ($amount >= $favourable_box['min_amount'] && !empty($act_goods_list)) {
                $favourable_box['available'] = true;
            }

            $math_div = CalculateRepository::math_div($goods_amount, $amount);
            $math_div = app(DscRepository::class)->changeFloat($math_div);

            if ($favourable_box['act_type'] == 1) {
                if ($math_div > 0) {
                    $act_type_ext_format = $favourable_box['act_type_ext_format'] * $math_div;
                } else {
                    $act_type_ext_format = $favourable_box['act_type_ext_format'];
                }
            } else {
                $act_type_ext_format = $favourable_box['act_type_ext_format'];
            }

            if ($act_type_ext_format > 0) {
                if ($list) {
                    foreach ($list as $i => $m) {

                        $act_goods_amoun = $m['goods_number'] * $m['goods_price'];

                        $act_math_div = CalculateRepository::math_div($act_goods_amoun, $goods_amount);
                        $act_math_div = app(DscRepository::class)->changeFloat($act_math_div);

                        if ($favourable_box['act_type'] == 1) {
                            $m['goods_favourable'] = $act_math_div * $act_type_ext_format;
                        } else {
                            $m['goods_favourable'] = 0;
                            if (isset($favourable_box['goods_fav_amount'])) {
                                $m['goods_favourable'] = $act_math_div * $favourable_box['goods_fav_amount'];
                            }
                        }

                        $m['goods_favourable'] = app(DscRepository::class)->changeFloat($m['goods_favourable']);

                        if ($favourable_box['available'] == false) {
                            $m['goods_favourable'] = 0;
                        }

                        Cart::where('rec_id', $m['rec_id'])->update([
                            'goods_favourable' => $m['goods_favourable']
                        ]);
                    }
                }
            }

            $favourable_box['act_type_ext_format'] = app(DscRepository::class)->getPriceFormat($act_type_ext_format);

            $favourable_box['min_amount'] = app(DscRepository::class)->getPriceFormat($favourable_box['min_amount']);
            $favourable_box['cart_fav_amount'] = app(DscRepository::class)->getPriceFormat($favourable_box['cart_fav_amount']);
        }
    }

    $favourable_box = favourable_cart_goods_list($favourable_box);

    return $favourable_box;
}

function favourable_cart_goods_list($favourable_box = [])
{
    $act_goods_list = $favourable_box['act_goods_list'] ?? [];

    if ($act_goods_list) {
        $group_id = BaseRepository::getKeyPluck($act_goods_list, 'group_id');

        if (!empty($group_id)) {
            $arr = [];
            foreach ($act_goods_list as $idx => $value) {

                $parts = $value['parts'] ?? [];
                if (!empty($parts)) {
                    $act_goods = [[$value], $value['parts']];
                    $act_goods = ArrRepository::getArrCollapse($act_goods);
                    $arr[$idx] = $act_goods;
                }
            }

            $arr = ArrRepository::getArrCollapse($arr);
            $act_goods_list = BaseRepository::getArrayMerge($act_goods_list, $arr);
            $act_goods_list = BaseRepository::getArrayUnique($act_goods_list, 'rec_id');
            $act_goods_list = BaseRepository::getSortBy($act_goods_list, 'group_id');
        }

        $act_goods_list = ArrRepository::getArrayUnset($act_goods_list);
        $act_goods_list = array_values($act_goods_list);
        $favourable_box['act_goods_list'] = $act_goods_list;
    }

    return $favourable_box;
}

/*
* 通过商品ID获取成本价
* @param $goods_id   商品ID
*/
function get_cost_price($goods_id)
{
    $cost_price = Goods::where('goods_id', $goods_id)->value('cost_price');
    $cost_price = $cost_price ? $cost_price : 0;

    return $cost_price;
}

/*
* 通过订单ID获取订单商品的成本合计
* @param $order_id   订单ID
*/
function goods_cost_price($order_id)
{
    $res = OrderGoods::select('goods_id', 'goods_number', 'cost_price')->where('order_id', $order_id)
        ->whereHasIn('getOrder');

    $res = BaseRepository::getToArrayGet($res);

    $cost_amount = 0;
    if ($res) {
        foreach ($res as $v) {
            $cost_amount += $v['cost_price'] * $v['goods_number'];
        }
    }

    return $cost_amount;
}

/**
 * 退回余额
 *
 * @param array $order
 * @param int $refound_surplus
 * @return bool
 * @throws Exception
 */
function return_user_surplus($order = [], $refound_surplus = 0)
{
    if (empty($order)) {
        return false;
    }

    $surplus = $order['surplus'] ?? 0;
    if ($order['user_id'] > 0 && $surplus > 0) {
        if ($refound_surplus > 0) {
            $surplus = $refound_surplus;
        }

        $money_count = AccountLog::where('user_id', $order['user_id'])->where('user_money', "-" . $surplus)->where('change_desc', 'like', '%' . $order['order_sn'] . '%')->count();
        if (!empty($money_count)) {
            log_account_change($order['user_id'], $surplus, 0, 0, 0, sprintf(lang('admin/order.return_order_surplus'), $order['order_sn']), ACT_OTHER, 1);
        }

        return true;
    }
}

/**
 * 退回余额、积分、红包（取消、无效、退货时），把订单使用余额、积分、红包、优惠券设为0
 *
 * @param array $order
 * @param int $refound_surplus
 * @return bool
 * @throws Exception
 */
function return_user_surplus_integral_bonus($order = [], $refound_surplus = 0)
{
    if (empty($order)) {
        return false;
    }

    $update = [];

    /* 处理余额 */
    $surplus = $order['surplus'] ?? 0;
    if ($order['user_id'] > 0 && $surplus > 0) {
        if ($refound_surplus > 0) {
            $user_refound_surplus = $refound_surplus;
        } else {
            $user_refound_surplus = $surplus;
        }

        $money_count = AccountLog::where('user_id', $order['user_id'])->where('user_money', "-" . $surplus)->where('change_desc', 'like', '%' . $order['order_sn'] . '%')->count();
        if ($money_count > 0) {
            log_account_change($order['user_id'], $user_refound_surplus, 0, 0, 0, sprintf(lang('admin/order.return_order_surplus'), $order['order_sn']), ACT_OTHER, 1);
        }

        // 余额已退款 不能再支付 所以修改为0
        OrderInfo::where('order_id', $order['order_id'])->update(['order_amount' => 0]);

        // 订单 剩余使用余额
        $order_surplus = $refound_surplus > 0 ? $surplus - $refound_surplus : 0;
        $update['surplus'] = $order_surplus;
    }

    /* 处理积分 */
    $integral = $order['integral'] ?? 0;
    if ($order['user_id'] > 0 && $integral > 0) {
        $integral_count = AccountLog::where('user_id', $order['user_id'])->where('pay_points', "-" . $integral)->where('change_desc', 'like', '%' . $order['order_sn'] . '%')->count();
        if ($integral_count > 0) {
            log_account_change($order['user_id'], 0, 0, 0, $integral, sprintf(lang('admin/order.return_order_integral'), $order['order_sn']), ACT_OTHER, 1);

            $update['integral'] = 0;
            $update['integral_money'] = 0;
        }
    }

    /* 处理红包 */
    if ($order['bonus_id'] > 0) {
        app(FlowOrderService::class)->unuseBonus($order['bonus_id']);

        $update['bonus_id'] = 0;
        $update['bonus'] = 0;
    }

    /*  @author-bylu 退优惠券 start */
    if ($order['order_id'] > 0) {
        unuse_coupons($order['order_id'], $order['uc_id']);

        $update['coupons'] = 0;
    }
    /*  @author-bylu  end */

    /* 退储值卡 start*/
    if ($order['order_id'] > 0) {
        return_card_money($order['order_id']);
    }
    /*退储值卡 end*/

    /* 修改订单 */
    update_order($order['order_id'], $update);

    return true;
}

/**
 * 处理支付超时订单
 *
 * @param int $order_id
 * @param string $username
 * @return bool
 * @throws Exception
 */
function checked_pay_Invalid_order($order_id = 0, $username = '')
{
    $pay_effective_time = isset($GLOBALS['_CFG']['pay_effective_time']) && $GLOBALS['_CFG']['pay_effective_time'] > 0 ? intval($GLOBALS['_CFG']['pay_effective_time']) : 0;//订单时效

    if ($pay_effective_time > 0) {
        $pay_effective_time = $pay_effective_time * 60;
        $time = TimeRepository::getGmTime();

        if (!empty($order_id)) {
            $order_id = BaseRepository::getExplode($order_id);
            $order_list = OrderInfo::whereIn('order_id', $order_id);
        } else {
            $order_list = OrderInfo::where('main_count', 0);
        }

        $order_list = $order_list->whereHasIn('getPayment', function ($query) {
            $query->whereNotIn('pay_code', ['cod', 'bank']);
        });

        $order_list = $order_list->whereRaw("($time - add_time) > $pay_effective_time")
            ->whereIn('order_status', [OS_UNCONFIRMED, OS_CONFIRMED])
            ->whereIn('shipping_status', [SS_UNSHIPPED, SS_PREPARING])
            ->where('pay_status', PS_UNPAYED);

        $order_list = BaseRepository::getToArrayGet($order_list);

        if (!empty($order_list)) {
            foreach ($order_list as $k => $v) {
                if ($v['order_status'] != OS_INVALID) {
                    $store_order_id = get_store_id($v['order_id']);
                    $store_id = ($store_order_id > 0) ? $store_order_id : 0;

                    /* 标记订单为“无效” */
                    update_order($v['order_id'], ['order_status' => OS_INVALID]);

                    /* 记录log */
                    order_action($v['order_sn'], OS_INVALID, SS_UNSHIPPED, PS_UNPAYED, $GLOBALS['_LANG']['pay_effective_Invalid'], $username);

                    /* 如果使用库存，且下订单时减库存，则增加库存 */
                    if ($GLOBALS['_CFG']['use_storage'] == '1' && $GLOBALS['_CFG']['stock_dec_time'] == SDT_PLACE) {
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
                }
            }
        }

        return true;
    }

    return false;
}

/**
 * 活动详情
 *
 * @param int $goods_id
 * @param int $ru_id
 * @param array $goods
 * @param int $rank_id
 * @return array
 * @throws Exception
 */
function get_favourable_info($goods_id = 0, $ru_id = 0, $goods = [], $rank_id = 0)
{
    $CategoryLib = app(CategoryService::class);

    $gmtime = TimeRepository::getGmTime();

    if ($rank_id) {
        $user_rank = ',' . $rank_id . ',';
    } else {
        $user_rank = ',' . session('user_rank') . ',';
    }

    $res = FavourableActivity::where('review_status', 3)
        ->where('start_time', '<=', $gmtime)
        ->where('end_time', '>=', $gmtime)
        ->whereRaw("CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'");

    if ($ru_id > 0) {
        $res = $res->whereRaw("IF(userFav_type = 0, user_id = '$ru_id', 1 = 1)");
    } else {
        $res = $res->where('user_id', $ru_id);
    }

    $res = $res->take(15);

    $res = BaseRepository::getToArrayGet($res);

    $favourable = [];
    if (empty($goods_id)) {
        foreach ($res as $rows) {
            if ($rows['userFav_type'] == 1) {
                $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
            } else {
                $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
            }
            $favourable[$rows['act_id']]['url'] = 'activity.php';
            $favourable[$rows['act_id']]['time'] = sprintf(lang('common.promotion_time'), TimeRepository::getLocalDate('Y-m-d', $rows['start_time']), TimeRepository::getLocalDate('Y-m-d', $rows['end_time']));
            $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
            $favourable[$rows['act_id']]['type'] = 'favourable';
            $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
        }
    } else {
        if ($goods) {
            $category_id = isset($goods['cat_id']) && !empty($goods['cat_id']) ? $goods['cat_id'] : 0;
            $brand_id = isset($goods['brand_id']) && !empty($goods['brand_id']) ? $goods['brand_id'] : 0;
        } else {
            $row = Goods::select('cat_id', 'brand_id')->where('goods_id', $goods_id);
            $row = BaseRepository::getToArrayFirst($row);

            $category_id = $row ? $row['cat_id'] : 0;
            $brand_id = $row ? $row['brand_id'] : 0;
        }

        foreach ($res as $rows) {
            if ($rows['act_range'] == FAR_ALL) {
                $favourable[$rows['act_id']]['act_id'] = $rows['act_id'];
                $favourable[$rows['act_id']]['userFav_type'] = $rows['userFav_type'];

                if ($rows['userFav_type'] == 1) {
                    $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
                } else {
                    $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                }

                $favourable[$rows['act_id']]['url'] = 'activity.php';
                $favourable[$rows['act_id']]['time'] = sprintf(lang('common.promotion_time'), TimeRepository::getLocalDate('Y-m-d', $rows['start_time']), TimeRepository::getLocalDate('Y-m-d', $rows['end_time']));
                $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                $favourable[$rows['act_id']]['type'] = 'favourable';
                $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
            } elseif ($rows['act_range'] == FAR_CATEGORY) {
                /* 找出分类id的子分类id */
                $raw_id_list = explode(',', $rows['act_range_ext']);

                foreach ($raw_id_list as $id) {
                    /**
                     * 当前分类下的所有子分类
                     * 返回一维数组
                     */
                    $cat_keys = $CategoryLib->getCatListChildren(intval($id));
                    $list_array[$rows['act_id']][$id] = $cat_keys;
                }

                $list_array = !empty($list_array) ? array_merge($raw_id_list, $list_array[$rows['act_id']]) : $raw_id_list;
                $id_list = arr_foreach($list_array);
                $id_list = array_unique($id_list);

                $ids = join(',', array_unique($id_list));

                if (strpos(',' . $ids . ',', ',' . $category_id . ',') !== false) {
                    $favourable[$rows['act_id']]['act_id'] = $rows['act_id'];
                    $favourable[$rows['act_id']]['userFav_type'] = $rows['userFav_type'];

                    if ($rows['userFav_type'] == 1) {
                        $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
                    } else {
                        $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                    }

                    $favourable[$rows['act_id']]['url'] = 'activity.php';
                    $favourable[$rows['act_id']]['time'] = sprintf(lang('common.promotion_time'), TimeRepository::getLocalDate('Y-m-d', $rows['start_time']), TimeRepository::getLocalDate('Y-m-d', $rows['end_time']));
                    $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                    $favourable[$rows['act_id']]['type'] = 'favourable';
                    $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
                }
            } elseif ($rows['act_range'] == FAR_BRAND) {
                $rows['act_range_ext'] = act_range_ext_brand($rows['act_range_ext'], $rows['userFav_type'], $rows['act_range']);
                if (strpos(',' . $rows['act_range_ext'] . ',', ',' . $brand_id . ',') !== false) {
                    $favourable[$rows['act_id']]['act_id'] = $rows['act_id'];
                    $favourable[$rows['act_id']]['userFav_type'] = $rows['userFav_type'];

                    if ($rows['userFav_type'] == 1) {
                        $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
                    } else {
                        $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                    }

                    $favourable[$rows['act_id']]['url'] = 'activity.php';
                    $favourable[$rows['act_id']]['time'] = sprintf(lang('common.promotion_time'), TimeRepository::getLocalDate('Y-m-d', $rows['start_time']), TimeRepository::getLocalDate('Y-m-d', $rows['end_time']));
                    $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                    $favourable[$rows['act_id']]['type'] = 'favourable';
                    $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
                }
            } elseif ($rows['act_range'] == FAR_GOODS) {
                if (strpos(',' . $rows['act_range_ext'] . ',', ',' . $goods_id . ',') !== false) {
                    $favourable[$rows['act_id']]['act_id'] = $rows['act_id'];
                    $favourable[$rows['act_id']]['userFav_type'] = $rows['userFav_type'];

                    if ($rows['userFav_type'] == 1) {
                        $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
                    } else {
                        $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                    }

                    $favourable[$rows['act_id']]['url'] = 'activity.php';
                    $favourable[$rows['act_id']]['time'] = sprintf(lang('common.promotion_time'), TimeRepository::getLocalDate('Y-m-d', $rows['start_time']), TimeRepository::getLocalDate('Y-m-d', $rows['end_time']));
                    $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                    $favourable[$rows['act_id']]['type'] = 'favourable';
                    $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
                }
            }
        }
    }

    return $favourable;
}

