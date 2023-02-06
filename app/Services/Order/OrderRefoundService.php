<?php

namespace App\Services\Order;

use App\Exceptions\HttpException;
use App\Models\BonusType;
use App\Models\Goods;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\OrderReturn;
use App\Models\OrderReturnExtend;
use App\Models\Payment;
use App\Models\ReturnGoods;
use App\Models\ReturnImages;
use App\Models\SellerBillOrder;
use App\Models\SellerNegativeOrder;
use App\Models\UserBonus;
use App\Models\ValueCard;
use App\Models\ValueCardRecord;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Order\OrderReturnRepository;
use App\Services\Commission\CommissionManageService;
use App\Services\Erp\JigonManageService;
use App\Services\Goods\GoodsAttrService;
use App\Services\Goods\GoodsDataHandleService;

/**
 * Class OrderRefoundService
 * @package App\Services\Order
 */
class OrderRefoundService
{
    protected $commonRepository;
    protected $dscRepository;
    protected $goodsAttrService;
    protected $orderCommonService;

    public function __construct(
        CommonRepository $commonRepository,
        DscRepository $dscRepository,
        GoodsAttrService $goodsAttrService,
        OrderCommonService $orderCommonService
    )
    {
        $this->commonRepository = $commonRepository;
        $this->dscRepository = $dscRepository;
        $this->goodsAttrService = $goodsAttrService;
        $this->orderCommonService = $orderCommonService;
    }

    /**
     * 获取退换货图片列表
     *
     * @param array $where
     * @return mixed
     */
    public function getReturnImagesList($where = [])
    {
        $res = OrderReturnRepository::getReturnImagesList($where);

        if ($res) {
            foreach ($res as $key => $image) {
                $res[$key]['img'] = $image['img_file'];
                $res[$key]['img_file'] = $this->dscRepository->getImagePath($image['img_file']);
            }
        }

        return $res;
    }

    /**
     * 提交退换货
     *
     * @param int $user_id
     * @param array $request_info
     * @param string $action_user 操作者
     * @return int
     * @throws HttpException
     */
    public function submitReturn($user_id = 0, $request_info = [], $action_user = '')
    {
        if (empty($request_info)) {
            throw new HttpException(trans('user.Apply_abnormal'), 1);
        }

        $rec_ids = $request_info['rec_id'] ?? '';
        if (!empty($rec_ids) && !is_array($rec_ids)) {
            $rec_ids = explode(',', $rec_ids);
        }

        $last_option = $request_info['last_option'] ?? '';
        $last_option = (int)$last_option;

        $return_remark = $request_info['return_remark'] ?? '';
        $return_remark = addslashes($return_remark);

        $return_brief = $request_info['return_brief'] ?? '';
        $return_brief = addslashes($return_brief);

        $chargeoff_status = $request_info['chargeoff_status'] ?? 0;
        $chargeoff_status = (int)$chargeoff_status;

        $return_type = $request_info['return_type'] ?? 0; //退换货类型
        $return_type = (int)$return_type;

        $return_number = $request_info['return_number'] ?? 1; // 订单商品数量
        $return_number = (int)$return_number;

        if (empty($user_id) || empty($rec_ids)) {
            throw new HttpException(trans('user.Apply_abnormal'), 1);
        }

        // 最多可上传图片数量
        if (!empty($request_info['return_images'])) {
            $img_count = count($request_info['return_images']);
            $return_pictures = config('shop.return_pictures', 10);
            if ($img_count > $return_pictures) {
                throw new HttpException(trans('user.max_return_pictures', ['pic_count' => $return_pictures]), 1);
            }
        }

        load_helper(['ecmoban']);

        $time = TimeRepository::getGmTime();

        $rec_count = count($rec_ids); // 批量退换货
        $error = 0;

        foreach ($rec_ids as $rec_id) {
            if ($rec_id > 0) {
                $num = OrderReturn::where('rec_id', $rec_id)->count();
                if ($num > 0) {
                    throw new HttpException(trans('user.Repeated_submission'), 1);
                }
            } else {
                throw new HttpException(trans('user.Return_abnormal'), 1);
            }

            $order_goods = OrderReturnRepository::getReturnOrderGoods($rec_id, $user_id);
            if (empty($order_goods)) {
                throw new HttpException(trans('user.Apply_Abnormal'), 1);
            }

            // 订单商品数量
            if ($rec_count > 1) {
                $return_number = empty($order_goods['goods_number']) ? 1 : intval($order_goods['goods_number']);
            } else {
                $return_number = $return_number > $order_goods['goods_number'] ? $order_goods['goods_number'] : $return_number;//最大不超过购买数量
            }

            $maintain = 0;
            $return_status = RF_APPLICATION;
            if ($return_type == 1) {
                $back = 1;
                $exchange = 0;
            } elseif ($return_type == 2) {
                $back = 0;
                $exchange = 2;
            } elseif ($return_type == 3) {
                $back = 0;
                $exchange = 0;
                $return_status = RF_RETURNMON; // 仅退款
                $return_number = $order_goods['goods_number'] ?? 1; // 仅退款退换所有商品
            } else {
                $back = 0;
                $exchange = 0;
            }

            $return_number = (int)$return_number;

            // 贡云售后信息
            $jigonWhere = [
                'user_id' => $user_id,
                'rec_id' => $rec_id,
                'return_type' => $return_type,
                'return_number' => $return_number,
                'return_brief' => $return_brief,
                'type' => 'api',
            ];
            $aftersn = app(JigonManageService::class)->jigonAfterSales($jigonWhere);
            $aftersn = $aftersn ?: '';

            $attr_val = '';
            $return_attr_id = '';
            if ($rec_count == 1) {
                $attr_val = $request_info['attr_val'] ?? ''; //获取属性ID数组
                $return_attr_id = !empty($attr_val) ? implode(',', $attr_val) : '';
                // 换回商品属性
                if (!empty($attr_val)) {
                    $attr_val = get_goods_attr_info_new($attr_val, 'pice');
                }
            }

            $order_return = [
                'rec_id' => $rec_id,
                'goods_id' => $order_goods['goods_id'],
                'order_id' => $order_goods['order_id'],
                'order_sn' => $order_goods['order_sn'],
                'chargeoff_status' => $chargeoff_status, // 账单 0 未结账 1 已出账 2 已结账单
                'return_type' => $return_type, //唯一标识
                'maintain' => $maintain, //维修标识
                'back' => $back, //退货标识
                'exchange' => $exchange, //换货标识
                'user_id' => $user_id,
                'goods_attr' => $order_goods['goods_attr'],   //换出商品属性
                'attr_val' => !empty($attr_val) ? $attr_val : '',
                'return_brief' => $return_brief ?? '',
                'remark' => $return_remark ?? '',
                'credentials' => $request_info['credentials'] ?? 0,
                'country' => $order_goods['country'] ?? 1,
                'province' => $order_goods['province'] ?? 0,
                'city' => $order_goods['city'] ?? 0,
                'district' => $order_goods['district'] ?? 0,
                'street' => $order_goods['street'] ?? 0,
                'cause_id' => $last_option, //退换货原因
                'apply_time' => $time,
                'actual_return' => 0,
                'address' => $request_info['return_address'] ?? '',
                'zipcode' => $request_info['code'] ?? '',
                'addressee' => $request_info['addressee'] ?? '',
                'phone' => $request_info['mobile'] ?? '',
                'return_status' => $return_status,
                'goods_bonus' => $order_goods['goods_bonus'],
                'goods_coupons' => $order_goods['goods_coupons'],
                'goods_favourable' => $order_goods['goods_favourable'] ?? 0,
                'divide_channel' => $order_goods['divide_channel'] ?? 0,
                'goods_value_card' => $order_goods['goods_value_card'] ?? 0,
                'value_card_discount' => $order_goods['value_card_discount'] ?? 0,
                'goods_integral' => $order_goods['goods_integral'] ?? 0,
                'goods_integral_money' => $order_goods['goods_integral_money'] ?? 0
            ];

            if (CROSS_BORDER === true) { // 跨境多商户
                $order_return['return_rate_price'] = $order_goods['rate_price'] / $order_goods['goods_number'] * $return_number;
            }

            // 1 退货、3 退款
            if (in_array($return_type, [1, 3])) {
                $orderReturnFee = OrderReturnRepository::getOrderReturnFee($order_return['order_id'], $order_return['rec_id'], $return_number);
                $order_return['should_return'] = $orderReturnFee['return_price'] ?? 0;
                $order_return['return_shipping_fee'] = $orderReturnFee['return_shipping_fee'] ?? 0;
            } else {
                $order_return['should_return'] = 0;
                $order_return['return_shipping_fee'] = 0;
            }

            // 订单退款 不退开通会员权益卡购买金额
            if (file_exists(MOBILE_DRP) && $order_return['should_return'] > 0) {
                $order_return['should_return'] = $order_return['should_return'] - $order_goods['membership_card_discount_price'] / $order_goods['goods_number'] * $return_number;
            }

            /* 插入订单表 */
            $order_return['return_sn'] = OrderCommonService::getOrderSn(); //获取新订单号

            $ret_id = OrderReturn::insertGetId($order_return);

            if ($ret_id) {

                /* 记录log */
                $action_return_status = ($return_type == 3) ? OS_ONLY_REFOUND : $return_status;
                $this->orderCommonService->returnAction($ret_id, $action_return_status, 0, $order_return['remark'], $action_user);

                $return_goods['rec_id'] = $order_return['rec_id'];
                $return_goods['ret_id'] = $ret_id;
                $return_goods['goods_id'] = $order_goods['goods_id'];
                $return_goods['goods_name'] = $order_goods['goods_name'];
                $return_goods['brand_name'] = $order_goods['brand_name'];
                $return_goods['product_id'] = $order_goods['product_id'];
                $return_goods['goods_sn'] = $order_goods['goods_sn'];
                $return_goods['is_real'] = $order_goods['is_real'];
                $return_goods['goods_attr'] = $attr_val ? $attr_val : $order_goods['goods_attr'];  //换货的商品属性名称
                $return_goods['attr_id'] = $return_attr_id ? $return_attr_id : $order_goods['goods_attr_id']; //换货的商品属性ID值
                $return_goods['refound'] = $order_goods['goods_price'];

                if (CROSS_BORDER === true) { // 跨境多商户
                    $return_goods['rate_price'] = $order_goods['rate_price'] / $order_goods['goods_number'];
                }

                // 订单退款 不退开通会员权益卡购买金额
                if (file_exists(MOBILE_DRP)) {
                    $return_goods['membership_card_discount_price'] = $order_goods['membership_card_discount_price'];
                }

                //添加到退换货商品表中
                $return_goods['return_type'] = $return_type; //退换货
                $return_goods['return_number'] = $return_number; //退换货数量

                if ($return_type == 1) { //退货
                    $return_goods['out_attr'] = '';
                } elseif ($return_type == 2) { //换货
                    $return_goods['out_attr'] = !empty($attr_val) ? $attr_val : '';
                    $return_goods['return_attr_id'] = $return_attr_id;
                } else {
                    $return_goods['out_attr'] = '';
                }

                ReturnGoods::insert($return_goods);

                // 保存退换货图片
                if (!empty($request_info['return_images'])) {
                    foreach ($request_info['return_images'] as $k => $v) {
                        if (stripos(substr($v, 0, 4), 'http') !== false) {
                            $v = str_replace(asset('/'), '', $v);
                        }
                        $img_file = str_replace('storage/', '', ltrim($v, '/'));
                        $data = [
                            'rec_id' => $rec_id,
                            'rg_id' => $order_goods['goods_id'],
                            'user_id' => $user_id,
                            'img_file' => $img_file,
                            'add_time' => $time
                        ];
                        ReturnImages::insert($data);
                    }
                }

                //退货数量插入退货表扩展表  by kong
                $order_return_extend = [
                    'ret_id' => $ret_id,
                    'return_number' => $return_number,
                    'aftersn' => $aftersn
                ];
                OrderReturnExtend::insert($order_return_extend);

                $address_detail = $order_goods['region'] . ' ' . $order_return['address'];
                $order_return['address_detail'] = $address_detail;
                $order_return['apply_time'] = TimeRepository::getLocalDate("Y-m-d H:i:s", $order_return['apply_time']);

                //订单申请售后推送消息给多商户商家掌柜事件
                event(new \App\Events\PushMerchantOrderRefundApplyEvent($order_return['return_sn']));

            } else {
                $error += 1; // 异常提交申请的数量
            }
        }

        if ($error > 0) {
            throw new HttpException(trans('user.Apply_abnormal'), 1);
        }

        return true;
    }

    /**
     * 退换货 - 同意申请
     *
     * @param int $rec_id
     * @param int $ret_id
     * @param string $action_note
     * @return bool
     * @throws HttpException
     */
    public function operatePostAgreeApply($rec_id = 0, $ret_id = 0, $action_note = '')
    {
        if (empty($rec_id) || empty($rec_id)) {
            throw new HttpException('parameters of illegal.', 1);
        }

        if (empty($action_note) && config('shop.order_return_note', 0) == 1) {
            throw new HttpException(trans('admin/order.operation_notes_required'), 1);
        }

        $arr = ['agree_apply' => 1];
        $res = OrderReturn::where('rec_id', $rec_id)->update($arr);

        if ($res) {

            if (file_exists(WXAPP_MEDIA)) {
                //订单售后操作事件
                event(new \App\Modules\WxMedia\Events\MediaOrderRefoundOperateEvent($rec_id, ['status' => 2]));
            }

            /* 记录log */
            $this->orderCommonService->returnAction($ret_id, RF_AGREE_APPLY, '', $action_note);
        }

        return true;
    }

    /**
     * 退换货 - 退货/退款
     *
     * @param int $rec_id
     * @param int $ret_id
     * @param array $order
     * @param int $refund_type
     * @param int $rate_price
     * @param string $refund_note
     * @param string $action_note
     * @param string $operation
     * @return bool
     * @throws HttpException
     */
    public function operatePostRefound($rec_id = 0, $ret_id = 0, $order = [], $refund_type = 0, $rate_price = 0, $refund_note = '', $action_note = '', $operation = '')
    {
        if (empty($rec_id) || empty($rec_id) || empty($order)) {
            throw new HttpException('parameters of illegal.', 1);
        }

        if ($order['order_status'] == OS_RETURNED || $order['order_status'] == OS_ONLY_REFOUND) {
            // 已退货、仅退款 返回错误信息
            throw new HttpException(lang('admin/order.operation_error'), 1);
        }

        $return_goods = get_return_order_goods1($rec_id); //退换货商品
        $return_info = return_order_info($ret_id);        //退换货订单

        $vc_id = $return_info['vc_id'];
        $order['return_sn'] = $return_info['return_sn'];

        $refund_amount = $this->dscRepository->changeFloat($return_info['pay_goods_amount'] ?? 0);
        $refound_vcard = $this->dscRepository->changeFloat($return_info['pay_value_card'] ?? 0);
        $shippingFee = $this->dscRepository->changeFloat($return_info['pay_shipping_fee'] ?? 0);
        $refound_pay_points = $return_info['should_integral'];

        $order_id = $order['order_id'];

        $refound_status = $return_info['refound_status'] ?? 0;
        if ($refound_status == FF_REFOUND) {
            // 已退款 返回错误信息
            throw new HttpException(lang('admin/order.operation_error'), 1);
        }

        /* 可退储值卡金额 */
        $pay_card_money = $return_info['pay_card_money'] ?? 0;

        if ($pay_card_money > 0) {
            $refoundCardTotal = $refound_vcard + $shippingFee;
        } else {
            $refoundCardTotal = 0;
        }

        if ($refoundCardTotal > $pay_card_money) {
            $refund_amount += $refoundCardTotal - $pay_card_money; //剩余部分计入已付款的退款金额中
            $refound_vcard = $pay_card_money;
        } else {
            $refound_vcard = $refoundCardTotal;
        }
        /* 储值卡退款 end */

        //判断商品退款是否大于实际商品退款金额
        $refound_fee = $this->orderRefoundFee($order_id, $ret_id); //已退金额
        $paid_amount = $order['money_paid'] + $order['surplus'] - $refound_fee; //剩余可退的已付款金额

        if ($refound_vcard == 0) {

            $refundAmount = $refund_amount + $shippingFee;

            if ($refundAmount > $paid_amount) {
                $refund_amount = $paid_amount;
            } else {
                $refund_amount = $refundAmount;
            }
        } else {
            if ($refund_amount > $paid_amount) {
                $refund_amount = $paid_amount;
            }
        }

        /* 处理退款 */
        if ($order['pay_status'] != PS_UNPAYED) {

            // 获取退款后的订单状态
            $order_goods = OrderGoods::select('rec_id', 'order_id', 'goods_id', 'goods_number', 'send_number')->where('order_id', $order['order_id'])
                ->where('extension_code', '<>', 'virtual_card');
            $order_goods = BaseRepository::getToArrayGet($order_goods);//订单商品

            $get_order_arr = get_order_arr($return_info['return_number'], $return_info['rec_id'], $order_goods);

            foreach ($order_goods as $k => $v) {
                $res = OrderReturn::where('rec_id', $v['rec_id'])
                    ->where('order_id', $v['order_id'])
                    ->value('return_type');
                $res = $res ? $res : 0;
                // 仅退款
                if ($res == 3 && $get_order_arr['order_status'] == OS_RETURNED) {
                    $get_order_arr['order_status'] = OS_ONLY_REFOUND;
                }
            }

            // 订单应退款
            $order['should_return'] = $return_info['should_return'];

            // 在线原路退款
            if ($refund_type == 6) {

                try {

                    $refound_result = $this->refundApply($return_info['return_sn'], $refund_amount);

                } catch (HttpException $httpException) {
                    throw new HttpException(lang('admin/order.refund_type_notic_six') . ',' . $httpException->getMessage(), 1);
                }

                if ($refound_result == false) {
                    throw new HttpException(lang('admin/order.refund_type_notic_six'), 1);
                }
            } else {
                // 1, 2, 3, 5  等
                $is_ok = order_refound($order, $refund_type, $refund_note, $refund_amount, $operation);

                if ($is_ok == 2 && $refund_type == 1) {
                    throw new HttpException(lang('admin/order.refund_type_notic_two'), 1);
                }

                /* 余额已放入冻结资金 */
                $order['surplus'] = 0;
            }

            //标记order_return 表
            $return_status = [
                'refound_status' => FF_REFOUND,
                'agree_apply' => 1,
                'actual_return' => $refund_amount,
                'return_shipping_fee' => $shippingFee,
                'actual_value_card' => $refound_vcard,
                'actual_integral_money' => $return_info['should_integral_money'] ?? 0,
                'return_rate_price' => $rate_price,
                'refund_type' => $refund_type,
                'return_time' => TimeRepository::getGmTime()
            ];

            // 商家订单 平台禁用审批 商家管理员操作则默认通过审批
            if (config('shop.seller_return_check', 0) == 0 && $order['ru_id'] > 0) {
                $return_status['is_check'] = 1;
            }

            OrderReturn::where('ret_id', $ret_id)->update($return_status);

            if (file_exists(WXAPP_MEDIA)) { // 小程序视频号推广员管理
                app(\App\Modules\WxMedia\Services\WxappShopOrderService::class)->promoterCommissionOrderRefound($return_info['order_id'], $return_info['rec_id'], $refund_amount, session('seller_name'));
            }

            if ($shippingFee > 0 && $return_status['actual_return'] >= $shippingFee) {
                if ($refound_vcard == 0) {
                    $return_status['actual_return'] -= $shippingFee;
                } else {
                    if ($refound_vcard < $shippingFee) {
                        $return_status['actual_return'] = $return_status['actual_return'] - ($shippingFee - $refound_vcard);
                    }
                }
            }

            /* 负账单订单 start */
            if ($order['ru_id'] > 0) {
                $negativeCount = SellerNegativeOrder::where('ret_id', $ret_id)->count();

                if ($negativeCount == 0) {
                    $negative_time = TimeRepository::getGmTime();

                    $negative_refund_amount = $return_status['actual_return'] - $rate_price;

                    $commission = app(CommissionManageService::class)->commissionNegativeOrderList($ret_id, $negative_refund_amount);

                    $other = [
                        'order_id' => $order['order_id'],
                        'order_sn' => $order['order_sn'],
                        'ret_id' => $ret_id,
                        'return_sn' => $return_info['return_sn'],
                        'seller_id' => $order['ru_id'],
                        'return_amount' => $negative_refund_amount,
                        'return_shippingfee' => $shippingFee,
                        'return_rate_price' => $rate_price,
                        'add_time' => $negative_time,
                        'seller_proportion' => $commission['seller_proportion'],
                        'cat_proportion' => $commission['cat_proportion'],
                        'commission_rate' => $commission['commission_rate'],
                        'gain_commission' => $commission['gain_commission'],
                        'should_amount' => $commission['should_amount'],
                        'divide_channel' => $order['divide_channel'] ?? 0
                    ];

                    // 记录分销金额
                    if (file_exists(MOBILE_DRP)) {
                        if ($commission['should_amount'] >= $return_goods['drp_money']) {
                            $drp_money = $return_goods['drp_money'];
                        } else {
                            $drp_money = $commission['should_amount'];
                        }

                        $other['drp_money'] = $drp_money;
                    }

                    SellerNegativeOrder::insert($other);
                }
            }
            /* 负账单订单 end */

            /**
             * 将未结算的负账单作废
             */
            if ($get_order_arr['pay_status'] == PS_REFOUND) {
                SellerNegativeOrder::where('order_id', $order['order_id'])
                    ->where('negative_id', 0)
                    ->whereHasIn('getSellerBillOrder', function ($query) {
                        $query->where('bill_id', 0);
                    })
                    ->update([
                        'settle_accounts' => 2
                    ]);
            }

            /**
             * 更新订单商品状态
             */
            if ($get_order_arr['order_status'] == OS_RETURNED || $get_order_arr['order_status'] == OS_RETURNED_PART || $get_order_arr['order_status'] == OS_ONLY_REFOUND) {
                OrderGoods::where('rec_id', $return_info['rec_id'])->update(['is_received' => 1]);
            }

            update_order($order_id, $get_order_arr);

            /**
             * 更新账单订单状态
             */
            SellerBillOrder::where('order_id', $order_id)->update([
                'order_status' => $get_order_arr['order_status'],
                'pay_status' => $get_order_arr['pay_status']
            ]);

            // 减回商品销量
            $sales_volume = Goods::where('goods_id', $return_goods['goods_id'])->value('sales_volume');
            $sales_volume = $sales_volume ?? 0;
            if ($sales_volume >= $return_info['return_number']) {
                Goods::where('goods_id', $return_goods['goods_id'])->decrement('sales_volume', $return_info['return_number']);
            }

            // 更新订单操作记录log
            order_action($order['order_sn'], $get_order_arr['order_status'], $order['shipping_status'], $order['pay_status'], $action_note, session('seller_name'));
        } elseif (in_array($order['pay_code'], ['bank', 'cod'])) {

            //标记order_return 表
            $return_status = [
                'refound_status' => FF_REFOUND,
                'agree_apply' => 1,
                'refund_type' => 2,
                'return_time' => TimeRepository::getGmTime()
            ];

            // 商家订单 平台开启审批 平台管理员操作则通过审批
            if (config('shop.seller_return_check', 0) == 1 && $order['ru_id'] > 0) {
                $return_status['is_check'] = 1;
            }

            OrderReturn::where('ret_id', $ret_id)->update($return_status);

            $refoundStatusCount = OrderReturn::where('order_id', $order['order_id'])->where('refound_status', 1)->count('ret_id');
            $recGoodsCount = OrderGoods::where('order_id', $order['order_id'])->count('rec_id');

            if ($refoundStatusCount == $recGoodsCount) {
                OrderInfo::where('order_id', $order['order_id'])->update([
                    'pay_status' => PS_REFOUND
                ]);
            } else {
                OrderInfo::where('order_id', $order['order_id'])->update([
                    'pay_status' => PS_REFOUND_PART
                ]);
            }
        }

        $is_diff = get_order_return_rec($order_id, true);
        if ($is_diff) {
            //整单退换货
            $return_count = return_order_info_byId($order_id, false, true);
            if ($return_count == 1) {
                //退还红包
                UserBonus::where('order_id', $order_id)->update(['used_time' => '', 'order_id' => '']);

                /* 退还优惠券 start */
                unuse_coupons($order_id, $order['uc_id']);
            }
        }

        /* 1:只有退款|退货,并且红包类型为:按订单金额发放红包(2)|按商品发放红包(1) 如红包尚未使用，则删除该红包*/
        if ($order['user_id'] > 0 && ($return_info['return_type'] == 1 || $return_info['return_type'] == 3)) {
            $this->return_order_delete_bonus($return_info['order_id'], $return_info['user_id'], $return_goods['goods_id'], $order['total_fee'], $refund_amount);
        }

        // 退货退款(含部分退款) 重新计算待分成分销佣金
        if (file_exists(MOBILE_DRP)) {
            \App\Modules\Drp\Services\Drp\DrpRefoundService::return_drp_order($ret_id, $order);
        }

        /*判断是否需要退还积分  如果需要 则跟新退还日志   by kong*/
        if ($refound_pay_points > 0) {
            // 退还订单使用的积分
            log_account_change($order['user_id'], 0, 0, 0, $refound_pay_points, lang('order.order_return_prompt') . $order['order_sn'] . lang('order.buy_integral'));
        }

        // 原路退款同时使用余额 退回订单使用的余额
        if ($refund_type == 6 && $refund_amount > 0 && $return_info['surplus'] > 0) {

            $returnMoneyPaid = $this->orderReturnMoneyPaid($return_info['ret_id']); //获取当前可退余额

            if ($refund_amount > $returnMoneyPaid['refund_surplus']) {
                $refound_surplus = $returnMoneyPaid['refund_surplus'];
            } else {
                $refound_surplus = $refund_amount;
            }

            $refound_surplus = $refound_surplus > $return_info['surplus'] ? $return_info['surplus'] : $refound_surplus;

            if ($refound_surplus > 0) {
                return_user_surplus($order, $refound_surplus);
            }
        }

        //普通订单已支付(部分退款)且已收货, 货到付款订单 已发货 才会退回订单所赠送积分
        if ((isset($order['pay_code']) && $order['pay_code'] == 'cod' && $order['shipping_status'] == SS_SHIPPED) || (in_array($order['pay_status'], [PS_PAYED, PS_REFOUND_PART]) && $order['shipping_status'] == SS_RECEIVED)) {
            /* 退回订单赠送的积分 */
            return_integral_rank($ret_id, $order['user_id'], $order['order_sn'], $rec_id);
        }

        /* 退回订单消费储值卡金额 */
        get_return_vcard($order_id, $vc_id, $refound_vcard, $return_info['return_sn'], $ret_id);

        return true;
    }

    /**
     * 在线退款申请 （第三方在线支付)
     * 说明： 走退换货流程订单
     * @param string $return_sn
     * @param int $refund_amount
     * @throws HttpException
     */
    public function refundApply($return_sn = '', $refund_amount = 0)
    {
        if (empty($return_sn)) {
            throw new HttpException(trans('admin/order.return_sn_required'), 422);
        }

        /**
         * 判断订单是否是在线支付 且 是否支付完成 已退款
         * 如果支付完成 则可以发起退款申请 接口
         */
        $model = OrderReturn::where('return_sn', $return_sn)->where('refound_status', 0); // 未退款的
        $model = $model->with([
            'orderInfo' => function ($query) {
                $query->select('order_id', 'order_sn', 'pay_id', 'pay_status', 'money_paid', 'referer', 'ru_id', 'divide_channel');
            }
        ]);

        $return_order = BaseRepository::getToArrayFirst($model);

        if (empty($return_order)) {
            throw new HttpException(trans('admin/order.return_order_not_exists'), 422);
        }

        $orderInfo = $return_order['order_info'];

        $return_order = collect($return_order)->merge($return_order['order_info'])->except('order_info')->all();

        // 是否支持原路退款的 在线支付方式
        $can_refund = OrderReturnRepository::showReturnOnline($return_order['pay_id']);
        if ($can_refund === false) {
            throw new HttpException(trans('admin/order.return_order_pay_not_supported'), 422);
        }

        $pay_status = [
            PS_PAYED,
            PS_PAYED_PART,
            PS_REFOUND_PART
        ];
        if (in_array($return_order['pay_status'], $pay_status)) {
            $pay_code = Payment::where('pay_id', $return_order['pay_id'])->value('pay_code');
            $pay_code = $pay_code ? $pay_code : '';

            if (!empty($pay_code) && strpos($pay_code, 'pay_') === false) {
                $payObject = CommonRepository::paymentInstance($pay_code);
                if (!is_null($payObject) && is_callable([$payObject, 'refund'])) {

                    // 扣除已退款部分
                    if ($refund_amount > 0) {

                        $paid_amount = $orderInfo['money_paid'] + $orderInfo['surplus'];
                        $refoundFee = $this->orderRefoundFee($orderInfo['order_id'] ?? 0);

                        if ($paid_amount >= $refoundFee) {
                            $paid_amount -= $refoundFee;
                        }

                        if ($refund_amount > $paid_amount) {
                            /* 判断退款金额是否大于剩余应金额 */
                            $refund_amount = $paid_amount;
                        }
                    }

                    /* 控制原路退回金额不能大于实际可退在线支付金额 start */
                    $order_id = $return_order['order_id'] ?? 0;
                    $refundApplyTotal = $this->orderReturnRefundApplyTotal($order_id);

                    $money_paid = $orderInfo['money_paid'] ?? 0;
                    $refundTotal = $money_paid - $refundApplyTotal; //可退金额范围
                    if ($refund_amount > $refundTotal) {
                        $refund_amount = $refundTotal;
                    }
                    /* 控制原路退回金额不能大于实际可退在线支付金额 end */

                    // 同意申请的同时提交退款申请到在线支付官方退款接口 等待结果
                    $return_order['should_return'] = $refund_amount;

                    $respond = $payObject->refund($return_order);
                    if ($respond === true) {
                        return true;
                    } else {
                        throw new HttpException($payObject->errMsg, 422);
                    }
                } else {
                    throw new HttpException(trans('admin/order.return_order_pay_not_exists'), 422);
                }
            } else {
                throw new HttpException(trans('admin/order.return_order_pay_not_exists'), 422);
            }
        }

        throw new HttpException(trans('admin/order.return_order_no_pay'), 422);
    }

    /**
     * 在线退款申请 （第三方在线支付)  -- 视频号推广员操作退款
     *
     * 说明： 走退换货流程订单
     * @param string $return_sn
     * @param int $refund_amount
     * @param string $pay_code
     * @return bool
     * @throws \App\Modules\WxMedia\Exceptions\MediaPromoterException
     */
    public function mediaRefundApply($return_sn = '', $refund_amount = 0, $pay_code = '')
    {
        if (file_exists(WXAPP_MEDIA_PROMOTER)) {
            if (empty($return_sn)) {
                throw new \App\Modules\WxMedia\Exceptions\MediaPromoterException(trans('admin/order.return_sn_required'), 422);
            }

            /**
             * 判断订单是否是在线支付 且 是否支付完成 已退款
             * 如果支付完成 则可以发起退款申请 接口
             */
            $model = OrderReturn::where('return_sn', $return_sn)->where('refound_status', 0); // 未退款的
            $model = $model->with([
                'orderInfo' => function ($query) {
                    $query->select('order_id', 'order_sn', 'pay_id', 'pay_status', 'money_paid', 'referer', 'ru_id', 'divide_channel');
                }
            ]);

            $return_order = BaseRepository::getToArrayFirst($model);

            if (empty($return_order)) {
                throw new \App\Modules\WxMedia\Exceptions\MediaPromoterException(trans('admin/order.return_order_not_exists'), 422);
            }

            $orderInfo = $return_order['order_info'];

            $return_order = collect($return_order)->merge($return_order['order_info'])->except('order_info')->all();

            // 是否支持原路退款的 在线支付方式
            $can_refund = OrderReturnRepository::showReturnOnline($return_order['pay_id']);
            /* if ($can_refund === false) {
                 throw new \App\Modules\WxMedia\Exceptions\MediaPromoterException(trans('admin/order.return_order_pay_not_supported'), 422);
             }*/

            $pay_status = [
                PS_PAYED,
                PS_PAYED_PART,
                PS_REFOUND_PART
            ];
            if (in_array($return_order['pay_status'], $pay_status)) {
                if (!empty($pay_code) && strpos($pay_code, 'pay_') === false) {
                    $payObject = CommonRepository::paymentInstance($pay_code);
                    if (!is_null($payObject) && is_callable([$payObject, 'refund'])) {

                        // 扣除已退款部分
                        if ($refund_amount > 0) {

                            $paid_amount = $orderInfo['money_paid'] + $orderInfo['surplus'];
                            $refoundFee = $this->orderRefoundFee($orderInfo['order_id'] ?? 0);

                            if ($paid_amount >= $refoundFee) {
                                $paid_amount -= $refoundFee;
                            }

                            if ($refund_amount > $paid_amount) {
                                /* 判断退款金额是否大于剩余应金额 */
                                $refund_amount = $paid_amount;
                            }
                        }

                        /* 控制原路退回金额不能大于实际可退在线支付金额 start */
                        $order_id = $return_order['order_id'] ?? 0;
                        $refundApplyTotal = $this->orderReturnRefundApplyTotal($order_id);

                        $money_paid = $orderInfo['money_paid'] ?? 0;
                        $refundTotal = $money_paid - $refundApplyTotal; //可退金额范围
                        if ($refund_amount > $refundTotal) {
                            $refund_amount = $refundTotal;
                        }
                        /* 控制原路退回金额不能大于实际可退在线支付金额 end */

                        // 同意申请的同时提交退款申请到在线支付官方退款接口 等待结果
                        $return_order['should_return'] = $refund_amount;

                        $respond = $payObject->refund($return_order);
                        if ($respond === true) {
                            return true;
                        } else {
                            throw new \App\Modules\WxMedia\Exceptions\MediaPromoterException($payObject->errMsg, 422);
                        }
                    } else {
                        throw new \App\Modules\WxMedia\Exceptions\MediaPromoterException(trans('admin/order.return_order_pay_not_exists'), 422);
                    }
                } else {
                    throw new \App\Modules\WxMedia\Exceptions\MediaPromoterException(trans('admin/order.return_order_pay_not_exists'), 422);
                }
            }

            throw new \App\Modules\WxMedia\Exceptions\MediaPromoterException(trans('admin/order.return_order_no_pay'), 422);
        } else {
            return true;
        }
    }

    /**
     * 获取支付方式原路退回已退总金额
     *
     * @param int $order_id
     * @return int
     */
    public function orderReturnRefundApplyTotal($order_id = 0)
    {
        $res = OrderReturn::select('rec_id', 'order_id', 'return_trade_data')->where('order_id', $order_id)
            ->where('refound_status', 1)
            ->where('refund_type', 6);

        $res = $res->with([
            'orderInfo' => function ($query) {
                $query->select('order_id', 'pay_id')
                    ->with([
                        'getPayment' => function ($query) {
                            $query->select('pay_id', 'pay_code');
                        }
                    ]);
            }
        ]);

        $res = BaseRepository::getToArrayGet($res);

        $refundFeeTotal = 0;
        if ($res) {
            foreach ($res as $key => $row) {

                $pay_code = $row['order_info']['get_payment']['pay_code'] ?? '';

                if ($row['return_trade_data']) {
                    $return_trade_data = json_decode($row['return_trade_data'], true);
                    $refund_fee = $return_trade_data['refund_fee'];

                    /* 转正常支付金额 */
                    if ($pay_code == 'wxpay' && $refund_fee > 0) {
                        $refund_fee = $refund_fee / 100;
                    }
                } else {
                    $refund_fee = 0;
                }

                $refundFeeTotal += $refund_fee;
            }
        }

        $refundFeeTotal = $this->dscRepository->changeFloat($refundFeeTotal);

        return $refundFeeTotal;
    }

    /**
     * 获取单品退货已退在线支付金额
     *
     * @param int $ret_id
     * @return int|mixed
     */
    public function orderReturnMoneyPaid($ret_id = 0)
    {
        $row = OrderReturn::select('ret_id', 'order_id', 'actual_return', 'return_trade_data')->where('ret_id', $ret_id)
            ->with([
                'orderInfo' => function ($query) {
                    $query->select('order_id', 'pay_id')
                        ->with([
                            'getPayment' => function ($query) {
                                $query->select('pay_id', 'pay_code');
                            }
                        ]);
                }
            ]);
        $row = BaseRepository::getToArrayFirst($row);

        $pay_code = $row['order_info']['get_payment']['pay_code'] ?? '';

        $return_trade_data = $row['return_trade_data'] ?? '';

        $returnTradeData = $return_trade_data ? json_decode($return_trade_data, true) : [];
        $refund_fee = $returnTradeData['refund_fee'] ?? 0;

        /* 转正常支付金额 */
        if ($pay_code == 'wxpay' && $refund_fee > 0) {
            $refund_fee = $refund_fee / 100;
        }

        $arr = [
            'actual_return' => $row['actual_return'] ?? 0, //实退单品总金额
            'refund_fee' => $refund_fee, //已退在线支付金额
        ];

        $arr['refund_surplus'] = $arr['actual_return'] - $arr['refund_fee']; //剩余可退余额金额
        $arr['refund_surplus'] = $arr['refund_surplus'] > 0 ? $this->dscRepository->changeFloat($arr['refund_surplus']) : 0;

        return $arr;
    }

    /**
     * 支付原路退款
     * 说明：可以不用走退换货申请流程,但必须要有支付日志 pay_log  pay_trade_data
     *
     * @param array $return_order
     * $return_order = [
     *      'order_id' => '2018011111',
     *      'pay_id' => '1',
     *      'pay_status' => '2',
     *      'referer' => 'wxapp'
     *      'ru_id' => '0',
     *      'return_sn' => 'order_sn'
     * ];
     *
     * @param int $refund_amount
     * @param int $order_type 支付类型 0 订单支付
     * @return bool
     */
    public static function refoundPay($return_order = [], $refund_amount = 0, $order_type = PAY_ORDER)
    {
        if (empty($return_order)) {
            return false;
        }

        // 是否支持原路退款的 在线支付方式
        $can_refund = OrderReturnRepository::showReturnOnline($return_order['pay_id']);

        // 已支付订单
        $pay_status = [
            PS_PAYED,
            PS_PAYED_PART,
            PS_REFOUND_PART
        ];
        if ($can_refund == true && in_array($return_order['pay_status'], $pay_status)) {
            $pay_code = Payment::where('pay_id', $return_order['pay_id'])->value('pay_code');
            $pay_code = $pay_code ? $pay_code : '';

            if ($pay_code && strpos($pay_code, 'pay_') === false) {
                $payObject = CommonRepository::paymentInstance($pay_code);
                if (!is_null($payObject) && is_callable([$payObject, 'refund'])) {
                    // 同意申请的同时提交退款申请到在线支付官方退款接口 等待结果
                    $return_order['should_return'] = $refund_amount;

                    $respond = $payObject->refund($return_order, $order_type);
                    if ($respond === true) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * 订单退款 如果使用储值卡 退还储值卡金额
     * @param int $order_id
     * @return int|mixed
     */
    public static function returnValueCardMoney($order_id = 0)
    {
        $row = ValueCardRecord::where('order_id', $order_id)->first();
        $row = $row ? $row->toArray() : [];

        if ($row) {
            /* 更新储值卡金额 */
            ValueCard::where('vid', $row['vc_id'])->increment('card_money', $row['use_val']);

            /* 更新订单使用储值卡金额 */
            ValueCardRecord::where('vc_id', $row['vc_id'])->where('order_id', $order_id)->where('use_val', '>=', $row['use_val'])->decrement('use_val', $row['use_val']);

            return $row['use_val'];
        }

        return 0;
    }

    /**
     * 获取订单退款金额
     *
     * @param int $order_id
     * @return array
     */
    public static function orderReturnAmount($order_id = 0)
    {
        $order_id = BaseRepository::getExplode($order_id);

        $return_amount = 0;
        $return_rate_price = 0;
        $ret_id = [];

        if ($order_id) {
            $row = OrderReturn::selectRaw("GROUP_CONCAT(ret_id) AS ret_id, SUM(actual_return) AS actual_return, SUM(return_rate_price) AS return_rate_price")
                ->whereIn('order_id', $order_id)
                ->whereIn('return_type', [1, 3])
                ->where('refound_status', 1);

            $row = BaseRepository::getToArrayFirst($row);

            if ($row) {
                $row['ret_id'] = $row['ret_id'] ?? [];
                $row['actual_return'] = $row['actual_return'] ?? 0;
                $row['return_rate_price'] = $row['return_rate_price'] ?? 0;

                $return_amount = $row['actual_return'] - $row['return_rate_price'];
                $return_rate_price = $row['return_rate_price'];
                $ret_id = BaseRepository::getExplode($row['ret_id']);
            }
        }

        $arr = [
            'return_amount' => $return_amount,
            'return_rate_price' => $return_rate_price,
            'ret_id' => $ret_id
        ];

        return $arr;
    }

    /**
     * 判断退款时需要退回的储值卡余额
     *
     * @param int $order_id
     * @return mixed
     */
    public function judgeValueCardMoney($order_id = 0)
    {
        //查询出订单使用的储值卡金额
        $res = ValueCardRecord::where('order_id', $order_id);
        $value_card = BaseRepository::getToArrayFirst($res);

        //查询已经返还的储值卡金额
        $add_val = ValueCardRecord::where('order_id', $order_id)->sum('add_val');

        /* 获取订单使用储值卡金额 */
        $use_val = OrderGoods::where('order_id', $order_id)->sum('goods_value_card');

        /* 获取剩余应退款的储值卡 */
        if ($use_val > 0) {
            $value_card['use_val'] = $use_val - $add_val; //减去已经返还的金额
        } else {
            if ($value_card) {
                $value_card['use_val'] = $value_card['use_val'] - $add_val; //减去已经返还的金额
                $value_card['use_val'] = $value_card['use_val'] > 0 ? $value_card['use_val'] : 0;
            }
        }

        return $value_card;
    }

    /**
     * @param int $return_order_id 退款|退货订单ID
     * @param int $return_order_user_id 退款|退货订单用户ID
     * @param int $return_goods_id 退款|退货订单商品ID
     * @param int $total_fee 订单总金额
     * @param int $refund_amount 应退金额
     * @return bool
     */
    function return_order_delete_bonus($return_order_id = 0, $return_order_user_id = 0, $return_goods_id = 0, $total_fee = 0, $refund_amount = 0)
    {
        if (empty($return_order_user_id)) {
            return false;
        }

        //查询和订单相关的红包且未使用的
        $user_bonus = UserBonus::where('return_order_id', $return_order_id)
            ->where('user_id', $return_order_user_id)
            ->where('used_time', '');
        $user_bonus = BaseRepository::getToArrayGet($user_bonus);
        if (empty($user_bonus)) {
            return false;
        }

        //订单已经退款的金额
        $actual_return = OrderReturn::where('order_id', $return_order_id)->sum('actual_return');
        $actual_return = floatval($actual_return);

        foreach ($user_bonus as $value) {
            $bonus_type = BonusType::where('type_id', $value['bonus_type_id']);
            $bonus_type = BaseRepository::getToArrayFirst($bonus_type);
            if (empty($bonus_type)) {
                continue;
            }

            //删除按商品发放红包
            if ($bonus_type['send_type'] == 1 && $value['return_goods_id'] == $return_goods_id) {
                UserBonus::where('bonus_id', $value['bonus_id'])->delete();
            } elseif ($bonus_type['send_type'] == 2) {
                $surplus_order_fee = $total_fee - $refund_amount - $actual_return;
                //退货或者退款 之后的订单金额小于红包发放金额则删除红包
                if ($surplus_order_fee < $bonus_type['min_amount']) {
                    UserBonus::where('bonus_id', $value['bonus_id'])->delete();
                }
            }
        }
    }

    /**
     * 取得用户退换货商品
     *
     * @param int $user_id
     * @param int $size
     * @param int $start
     * @return array
     * @throws \Exception
     */
    public function userReturnOrderList($user_id = 0, $size = 0, $start = 0)
    {
        //判断是否支持激活
        $activation_number_type = (int)config('shop.activation_number_type', 0);
        $activation_number_type = $activation_number_type > 0 ? $activation_number_type : 2;

        $res = OrderReturn::where('user_id', $user_id);

        if ($start > 0) {
            $res = $res->skip($start);
        }
        if ($size > 0) {
            $res = $res->take($size);
        }

        $res = $res->orderBy('ret_id', 'desc');

        $res = BaseRepository::getToArrayGet($res);

        $goods_list = [];
        if ($res) {

            $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'goods_thumb', 'goods_name']);

            $rec_id = BaseRepository::getKeyPluck($res, 'rec_id');
            $returnGoodsList = OrderDataHandleService::getReturnGoodsDataList($rec_id, ['rec_id', 'attr_id', 'goods_name']);

            $orderGoodsAttrIdList = BaseRepository::getKeyPluck($returnGoodsList, 'attr_id');
            $orderGoodsAttrIdList = BaseRepository::getArrayUnique($orderGoodsAttrIdList);
            $orderGoodsAttrIdList = ArrRepository::getArrayUnset($orderGoodsAttrIdList);

            $productsGoodsAttrList = [];
            if ($orderGoodsAttrIdList) {
                $orderGoodsAttrIdList = BaseRepository::getImplode($orderGoodsAttrIdList);
                $productsGoodsAttrList = GoodsDataHandleService::getGoodsAttrDataList($orderGoodsAttrIdList, ['goods_attr_id', 'attr_img_flie', 'attr_gallery_flie']);
            }

            foreach ($res as $row) {

                $goods = $goodsList[$row['goods_id']] ?? [];

                $row = BaseRepository::getArrayMerge($row, $goods);

                $goods_name = $returnGoodsList[$row['rec_id']]['goods_name'];
                $row['goods_name'] = $goods_name ? $goods_name : $row['goods_name'] ?? '';
                $row['goods_thumb'] = $row['goods_thumb'] ?? '';
                $row['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                $row['apply_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['apply_time']);
                $row['should_return'] = $this->dscRepository->getPriceFormat($row['should_return']);

                $goods_attr_id = $returnGoodsList[$row['rec_id']]['attr_id'] ?? '';
                $goods_attr_id = BaseRepository::getExplode($goods_attr_id);
                $row['goods_thumb'] = $this->goodsAttrService->cartGoodsAttrImage($goods_attr_id, $productsGoodsAttrList, $row['goods_thumb']);

                // 是否可取消申请
                $row['refound_cancel'] = OrderStatusService::refound_cancel($row);

                $row['order_status'] = '';
                if ($row['return_status'] == 0 && $row['refound_status'] == 0) {
                    //  提交退换货后的状态 由用户寄回
                    $row['order_status'] .= "<span>" . trans('user.user_return') . "</span>";
                } elseif ($row['return_status'] == 1) {
                    //退换商品收到
                    $row['order_status'] .= "<span>" . trans('user.get_goods') . "</span>";
                } elseif ($row['return_status'] == 2) {
                    //换货商品寄出 （分单）
                    $row['order_status'] .= "<span>" . trans('user.send_alone') . "</span>";
                } elseif ($row['return_status'] == 3) {
                    //换货商品寄出
                    $row['order_status'] .= "<span>" . trans('user.send') . "</span>";
                } elseif ($row['return_status'] == 4) {
                    //完成
                    $row['order_status'] .= "<span>" . trans('user.complete') . "</span>";
                } elseif ($row['return_status'] == 6) {
                    //被拒
                    $row['order_status'] .= "<span>" . trans('user.rf.' . $row['return_status']) . "</span>";
                } else {
                    //其他
                }

                //维修-退款-换货状态
                if ($row['return_type'] == 0) {
                    if ($row['return_status'] == 4) {
                        $row['reimburse_status'] = trans('user.ff.' . FF_MAINTENANCE);
                    } else {
                        $row['reimburse_status'] = trans('user.ff.' . FF_NOMAINTENANCE);
                    }
                } elseif ($row['return_type'] == 1) {
                    if ($row['refound_status'] == 1) {
                        $row['reimburse_status'] = trans('user.ff.' . FF_REFOUND);
                    } else {
                        $row['reimburse_status'] = trans('user.ff.' . FF_NOREFOUND);
                    }
                } elseif ($row['return_type'] == 2) {
                    if ($row['return_status'] == 4) {
                        $row['reimburse_status'] = trans('user.ff.' . FF_EXCHANGE);
                    } else {
                        $row['reimburse_status'] = trans('user.ff.' . FF_NOEXCHANGE);
                    }
                } elseif ($row['return_type'] == 3) {
                    if ($row['refound_status'] == 1) {
                        $row['reimburse_status'] = trans('user.ff.' . FF_REFOUND);
                    } else {
                        $row['reimburse_status'] = trans('user.ff.' . FF_NOREFOUND);
                    }
                }

                $row['activation_type'] = 0;
                //判断是否支持激活
                if ($row['return_status'] == 6) {
                    if ($row['activation_number'] < $activation_number_type) {
                        $row['activation_type'] = 1;
                    }
                    $row['agree_apply'] = -1; // 可激活时 不显示待同意 状态
                }

                if (isset($row['get_goods']['extension_code']) && $row['get_goods']['extension_code'] == 'package_buy') {
                    $is_package_buy = 1;
                } else {
                    $is_package_buy = 0;
                }

                if ($is_package_buy == 0) {
                    $goods_list[] = $row;
                }
            }
        }

        return $goods_list;
    }


    /**
     * 编辑退换货快递信息
     *
     * @param int $user_id
     * @param int $ret_id
     * @param int $back_shipping_name
     * @param string $back_other_shipping
     * @param string $back_invoice_no
     * @return bool
     * @throws HttpException
     */
    public function editExpress($user_id = 0, $ret_id = 0, $back_shipping_name = 0, $back_other_shipping = '', $back_invoice_no = '')
    {
        if (empty($user_id) || empty($ret_id)) {
            throw new HttpException('parameters of illegal.', 1);
        }

        /* 查询订单信息，检查状态 */
        $order = OrderReturn::where('ret_id', $ret_id);
        $order = BaseRepository::getToArrayFirst($order);

        // 如果用户ID大于0，检查订单是否属于该用户
        if ($user_id > 0 && $order['user_id'] != $user_id) {
            throw new HttpException(trans('user.unauthorized_access'), 1);
        }

        $other = [
            'back_shipping_name' => $back_shipping_name,
            'back_other_shipping' => $back_other_shipping,
            'back_invoice_no' => $back_invoice_no
        ];
        OrderReturn::where('ret_id', $ret_id)->where('user_id', $user_id)->update($other);
        return true;
    }

    /**
     * 取消退换货订单
     *
     * @param int $user_id
     * @param int $ret_id
     * @return bool
     * @throws HttpException
     */
    public function cancelReturnOrder($user_id = 0, $ret_id = 0)
    {
        if (empty($user_id) || empty($ret_id)) {
            throw new HttpException('parameters of illegal.', 1);
        }

        /* 查询订单信息，检查状态 */
        $order = OrderReturn::where('ret_id', $ret_id);
        $order = BaseRepository::getToArrayFirst($order);

        if (empty($order)) {
            throw new HttpException(lang('user.return_exist'), 1);
        }

        // 如果用户ID大于0，检查订单是否属于该用户
        if ($user_id > 0 && $order['user_id'] != $user_id) {
            throw new HttpException(trans('user.unauthorized_access'), 1);
        }

        // 订单状态只能是用户寄回和未退款状态
        if ($order['return_status'] != RF_APPLICATION && $order['refound_status'] != FF_NOREFOUND) {
            throw new HttpException(lang('user.return_not_unconfirmed'), 1);
        }

        //一旦由商家收到退换货商品，不允许用户取消
        if ($order['return_status'] == RF_RECEIVE) {
            throw new HttpException(lang('user.current_os_already_receive'), 1);
        }

        // 商家已发送退换货商品
        if ($order['return_status'] == RF_SWAPPED_OUT_SINGLE || $order['return_status'] == RF_SWAPPED_OUT) {
            throw new HttpException(lang('user.already_out_goods'), 1);
        }

        // 如果付款状态是“已付款”、“付款中”，不允许取消，要取消和商家联系
        if ($order['refound_status'] == FF_REFOUND) {
            throw new HttpException(lang('user.have_refound'), 1);
        }

        //将用户订单设置为取消
        $del = OrderReturn::where('ret_id', $ret_id)->where('user_id', $user_id)->delete();

        if ($del) {
            // 删除退换货商品
            ReturnGoods::where('rec_id', $order['rec_id'])->delete();

            $where = [
                'user_id' => $user_id,
                'rec_id' => $order['rec_id']
            ];
            $img_list = $this->getReturnImagesList($where);

            if ($img_list) {
                foreach ($img_list as $key => $row) {
                    dsc_unlink(storage_public($row['img_file']));
                }

                ReturnImages::where('user_id', $user_id)->where('rec_id', $order['rec_id'])->delete();
            }

            /* 删除扩展记录  by kong*/
            OrderReturnExtend::where('ret_id', $ret_id)->delete();

            /* 记录log */
            $this->orderCommonService->returnAction($ret_id, RF_APPLICATION, 0, lang('user.cancel_return'), lang('common.buyer'));

            return true;
        } else {
            throw new HttpException(lang('admin/common.fail'), 1);
        }
    }

    /**
     * 退换货订单确认收货
     *
     * @param int $user_id
     * @param int $ret_id
     * @return bool
     * @throws HttpException
     */
    public function receivedReturnOrder($user_id = 0, $ret_id = 0)
    {
        if (empty($user_id) || empty($ret_id)) {
            throw new HttpException('parameters of illegal.', 1);
        }

        /* 查询订单信息，检查状态 */
        $return_order = OrderReturn::where('ret_id', $ret_id);
        $return_order = BaseRepository::getToArrayFirst($return_order);

        if (empty($return_order)) {
            throw new HttpException(trans('user.return_exist'), 1);
        }

        // 检查订单是否属于该用户
        if ($user_id > 0 && $return_order['user_id'] != $user_id) {
            throw new HttpException(trans('user.unauthorized_access'), 1);
        }

        /* 检查订单 */
        if ($return_order['return_status'] == RF_COMPLETE) {
            throw new HttpException(trans('order.order_confirm_receipt'), 1);
        }

        /* 修改退换货订单状态为"收到退换货" */
        $res = OrderReturn::where('user_id', $user_id)->where('ret_id', $ret_id)->update(['return_status' => 4]);

        if ($res) {
            /* 记录log */
            $this->orderCommonService->returnAction($ret_id, RF_COMPLETE, $return_order['refound_status'], trans('user.received'), trans('common.buyer'));

            return true;
        } else {
            throw new HttpException(trans('admin/common.fail'), 1);
        }
    }

    /**
     * 激活退换货订单
     *
     * @param int $user_id
     * @param int $ret_id
     * @return bool
     * @throws HttpException
     */
    public function activeReturnOrder($user_id = 0, $ret_id = 0)
    {
        if (empty($user_id) || empty($ret_id)) {
            throw new HttpException('parameters of illegal.', 1);
        }

        $activation_number_type = (int)config('shop.activation_number_type', 0);
        $activation_number_type = $activation_number_type > 0 ? $activation_number_type : 2;

        $order_return = OrderReturn::select('ret_id', 'order_id', 'user_id', 'activation_number', 'return_type')->where('ret_id', $ret_id)->where('user_id', $user_id);
        $order_return = BaseRepository::getToArrayFirst($order_return);

        if (empty($order_return) || $order_return['user_id'] != $user_id) {
            throw new HttpException(trans('user.unauthorized_access'), 1);
        }

        $activation_number = $order_return['activation_number'] ?? 0;
        if ($activation_number_type > $activation_number) {
            $other = [
                'return_status' => 0
            ];
            // 仅退款
            $return_type = $order_return['return_type'] ?? 0;
            if ($return_type == 3) {
                $other['return_status'] = -1;
            }
            OrderReturn::where('ret_id', $ret_id)->where('user_id', $user_id)->increment('activation_number', 1, $other);

            return true;
        } else {
            throw new HttpException(sprintf(lang('user.activation_number_msg'), $activation_number_type), 1);
        }
    }

    /**
     * 删除已完成退换货订单
     *
     * @param int $user_id
     * @param int $ret_id
     * @return bool
     * @throws HttpException
     */
    public function deleteReturnOrder($user_id = 0, $ret_id = 0)
    {
        if (empty($user_id) || empty($ret_id)) {
            throw new HttpException('parameters of illegal.', 1);
        }

        /* 查询订单信息，检查状态 */
        $order = OrderReturn::where('ret_id', $ret_id);
        $order = BaseRepository::getToArrayFirst($order);

        if (empty($order)) {
            throw new HttpException(trans('user.return_exist'), 1);
        }

        // 检查订单是否属于该用户
        if ($user_id > 0 && $order['user_id'] != $user_id) {
            throw new HttpException(trans('user.unauthorized_access'), 1);
        }

        // 只能删除已完成退换货订单
        if ($order['return_status'] != 4) {
            throw new HttpException(trans('admin/common.fail'), 1);
        }

        // 删除退换货订单
        $del = OrderReturn::where(['ret_id' => $ret_id, 'user_id' => $user_id])->delete();
        if ($del) {
            // 删除退换货商品
            ReturnGoods::where('ret_id', $ret_id)->delete();

            /* 删除扩展记录  by kong */
            OrderReturnExtend::where('ret_id', $ret_id)->delete();

            return true;
        } else {
            throw new HttpException(trans('admin/common.fail'), 1);
        }
    }

    /**
     * 查询订单退换货已退金额
     * refund_type 1 退还余额, 3 不处理, 6 原路退款
     * @param int $order_id
     * @param int $ret_id
     * @return mixed
     */
    public function orderRefoundFee($order_id = 0, $ret_id = 0)
    {
        $price = OrderReturn::selectRaw("SUM(actual_return) AS actual_return")
            ->where('order_id', $order_id)
            ->whereIn('refund_type', [1, 3, 6])
            ->where('refound_status', 1);// 已退款

        if ($ret_id > 0) {
            $price = $price->where('ret_id', '<>', $ret_id);
        }

        $price = $price->value('actual_return');
        $price = $price ? $price : 0;

        return $price;
    }
}
