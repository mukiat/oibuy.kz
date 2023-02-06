<?php

use App\Jobs\ProcessSeparateBuyOrder;
use App\Models\BaitiaoLog;
use App\Models\BaitiaoPayLog;
use App\Models\MerchantsAccountLog;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\PayLog;
use App\Models\SellerAccountLog;
use App\Models\SellerApplyInfo;
use App\Models\SellerShopinfo;
use App\Models\SellerTemplateApply;
use App\Models\Stages;
use App\Models\StoreOrder;
use App\Models\TemplateMall;
use App\Models\UserAccount;
use App\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Erp\JigonManageService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Order\OrderCommonService;
use App\Services\Order\OrderService;
use App\Services\Payment\PaymentService;
use App\Services\Team\TeamService;
use App\Services\User\UserBaitiaoService;
use Illuminate\Support\Facades\DB;

/**
 * 取得同步通知返回信息地址
 * @param string $code
 * @return string
 */
function return_url($code = '')
{
    return url('/') . '/' . 'respond.php?code=' . $code;
}

/**
 * 取得异步通知返回信息地址
 *
 * @param string $code
 * @return string
 */
function notify_url($code = '')
{
    return route('notify') . '/' . $code;
}

/**
 *  取得某支付方式信息
 * @param string $value 支付方式代码/支付方式ID
 * @param int $type
 * @return array
 */
function get_payment($value = '', $type = 0)
{
    if ($type == 1) {
        $field = 'pay_id';
    } else {
        $field = 'pay_code';
    }

    return \App\Repositories\Payment\PaymentRepository::getPaymentConfig($value, $field);
}


/**
 * 通过订单ID取得订单商品名称
 *
 * @param $order_id
 * @return mixed
 */
function get_goods_name_by_id($order_id)
{
    $res = OrderGoods::select('goods_name')->where('order_id', $order_id);
    $res = BaseRepository::getToArrayGet($res);
    $goods_name = BaseRepository::getKeyPluck($res, 'goods_name');
    $goods_name = BaseRepository::getImplode($goods_name);

    return $goods_name;
}

/**
 * 检查支付的金额是否与订单相符
 *
 * @access  public
 * @param string $log_id 支付编号
 * @param float $money 支付接口返回的金额
 * @return  true
 */
function check_money($log_id, $money)
{
    if (is_numeric($log_id)) {
        $pay = PayLog::where('log_id', $log_id);
        $pay = BaseRepository::getToArrayFirst($pay);

        $pay['order_id'] = isset($pay['order_id']) ? $pay['order_id'] : 0;
        $pay['order_amount'] = isset($pay['order_amount']) ? $pay['order_amount'] : 0;

        $order_id = BaseRepository::getExplode($pay['order_id']);

        $order = OrderInfo::whereIn('order_id', $order_id);
        $order = BaseRepository::getToArrayFirst($order);

        $order['order_amount'] = isset($order['order_amount']) ? $order['order_amount'] : 0;
        $order['surplus'] = isset($order['surplus']) ? $order['surplus'] : 0;

        if ($order['surplus'] > 0) {
            $amount = $order['order_amount'];
        } else {
            $amount = $pay['order_amount'];
        }
    } else {
        return false;
    }

    if ($money == $amount) {
        return true;
    } else {
        return false;
    }
}

/**
 * @param int $log_id 支付编号
 * @param int $pay_status 状态
 * @param string $note 备注
 * @param string $order_sn
 * @param int $pay_money
 * @throws Exception
 */
function order_paid($log_id, $pay_status = PS_PAYED, $note = '', $order_sn = '', $pay_money = 0)
{
    $log_id = intval($log_id);

    $OrderLib = app(OrderService::class);

    $time = TimeRepository::getGmTime();

    /* 取得要修改的支付记录信息 */
    $pay_log = PayLog::where('log_id', $log_id);
    $pay_log = BaseRepository::getToArrayFirst($pay_log);

    $pay_order = [];
    if (!empty($order_sn) && $pay_log['order_type'] == PAY_ORDER) {
        $pay_order = OrderInfo::where('order_id', $pay_log['order_id']);
        $pay_order = $pay_order->with([
            'getBaitiaoLog' => function ($query) {
                $query->select('order_id', 'is_stages');
            }
        ]);
        $pay_order = BaseRepository::getToArrayFirst($pay_order);

        if ($pay_order && $pay_order['get_baitiao_log']) {
            if ($pay_order['get_baitiao_log']['is_stages']) {
                $pay_order['is_stages'] = $pay_order['get_baitiao_log']['is_stages'];
            } else {
                $pay_order['is_stages'] = 0;
            }
        }
    }

    if (!empty($order_sn) && $pay_order && $pay_order['is_stages'] == 1) {
        /**
         * 白条订单
         */
        $where_other = [
            'id' => $log_id
        ];
        $log_info = app(UserBaitiaoService::class)->getBaitiaoPayLogInfo($where_other);

        $other = [
            'is_pay' => 1,
            'pay_time' => $time
        ];
        BaitiaoPayLog::where('id', $log_id)->update($other);

        Stages::where('order_sn', $order_sn)->increment('yes_num', 1, ['repay_date' => $time]);

        BaitiaoLog::where('log_id', $log_info['log_id'])->increment('yes_num', 1, ['repayed_date' => $time]);

        $baitiao_log_info = app(UserBaitiaoService::class)->getBaitiaoLogInfo(['log_id' => $log_info['log_id']]);
        if ($baitiao_log_info && $baitiao_log_info['stages_total'] == $baitiao_log_info['yes_num'] && $baitiao_log_info['is_repay'] == 0) {
            //已还清,更新白条状态为已还清;
            BaitiaoLog::where('log_id', $log_info['log_id'])->update(['is_repay' => 1]);
        }
    } else {

        /**
         * 普通订单
         */

        /* 取得支付编号 */
        if ($pay_log) {
            load_helper('order');

            $config = config('shop');

            if ($pay_log && $pay_log['is_paid'] == 0) {

                $isDifference = false;
                $difference = 0;
                //检查支付金额是否正确
                if ($pay_money && $pay_log['order_amount'] != $pay_money) {
                    if ($pay_log['order_type'] == PAY_ORDER && $pay_log['order_amount'] > $pay_money) {
                        $isDifference = true;
                        $difference = $pay_log['order_amount'] - $pay_money;
                    } else {
                        return ['status' => 'error', 'message' => trans('payment.pay_money_error')];
                    }
                }

                /* 修改此次支付操作的状态为已付款 */
                PayLog::where('log_id', $log_id)->update(['is_paid' => 1]);

                /* 根据记录类型做相应处理 */
                if ($pay_log['order_type'] == PAY_ORDER) {
                    $order_id_arr = explode(',', $pay_log['order_id']);
                    foreach ($order_id_arr as $o_key => $o_val) {
                        /* 取得未支付，未取消，未退款订单信息 */
                        $order = $OrderLib->getUnPayedOrderInfo($o_val);

                        if (!empty($order)) {
                            $order_id = $order['order_id'] ?? 0;
                            $order_sn = $order['order_sn'] ?? '';
                            $user_id = $order['user_id'] ?? 0;
                            $pay_code = $order['get_payment']['pay_code'] ?? '';
                            $media_type = $order['media_type'] ?? 0;

                            if (isset($order['is_zc_order']) && $order['is_zc_order'] == 1) {
                                /* 众筹状态的更改 */
                                update_zc_project($order_id);
                            }

                            $order['extension_code'] = $isDifference == true ? 'presale' : $order['extension_code'];

                            //预售首先支付定金--无需分单
                            if ($order['extension_code'] == 'presale') {
                                $money_paid = $order['money_paid'] + $order['order_amount'];

                                if ($order['pay_status'] == PS_UNPAYED) {
                                    /* 支付定金 修改订单状态为已部分付款 */
                                    $order_amount = $order['goods_amount'] + $order['shipping_fee'] + $order['insure_fee'] + $order['pay_fee'] + $order['tax'] - $order['money_paid'] - $order['order_amount'];

                                    $money_paid = $isDifference == true ? $pay_money : $money_paid;
                                    $order_amount = $isDifference == true ? $difference : $order_amount;

                                    $other = [
                                        'order_status' => OS_CONFIRMED,
                                        'confirm_time' => $time,
                                        'pay_status' => PS_PAYED_PART,
                                        'pay_time' => $time,
                                        'money_paid' => $money_paid,
                                        'order_amount' => $order_amount
                                    ];
                                    OrderInfo::where('order_id', $order_id)->update($other);

                                    /* 记录订单操作记录 */
                                    order_action($order_sn, OS_CONFIRMED, SS_UNSHIPPED, PS_PAYED_PART, $note, trans('payment.buyer'));
                                    //更新pay_log
                                    app(OrderCommonService::class)->updateOrderPayLog($order_id);
                                } else {
                                    // 支付尾款 修改订单状态为已付款
                                    $other = [
                                        'pay_status' => PS_PAYED,
                                        'pay_time' => $time,
                                        'money_paid' => $money_paid,
                                        'order_amount' => 0
                                    ];

                                    if ($order['main_count'] > 0) {
                                        $other['main_pay'] = 2;
                                    }

                                    OrderInfo::where('order_id', $order_id)->update($other);

                                    /* 记录订单操作记录 */
                                    order_action($order_sn, OS_CONFIRMED, SS_UNSHIPPED, PS_PAYED, $note, trans('payment.buyer'));

                                    //付款成功后增加预售人数
                                    get_presale_num($order_id);
                                }
                            } else {
                                //判断订单状态
                                if (in_array($order['order_status'], [OS_CANCELED, OS_INVALID, OS_RETURNED])) {
                                    return ['status' => 'error', 'message' => trans('order.order_status_not_support')];
                                }

                                //判断付款状态
                                if ($order['pay_status'] == PS_PAYED) {
                                    return ['status' => 'error', 'message' => trans('payment.pay_repeat')];
                                }

                                /* 修改普通订单状态为已付款 */
                                $other = [
                                    'order_status' => OS_CONFIRMED,
                                    'confirm_time' => $time,
                                    'pay_status' => $pay_status,
                                    'pay_time' => $time,
                                    'money_paid' => DB::raw("money_paid + order_amount"),
                                    'order_amount' => 0
                                ];

                                if ($order['main_count'] > 0) {
                                    $other['main_pay'] = 2;
                                }

                                OrderInfo::where('order_id', $order_id)->update($other);

                                //付款成功创建快照
                                $extendParam = [
                                    'create_snapshot' => 1,
                                    'shop_config' => config('shop')
                                ];

                                /* 如果使用库存，且付款时减库存，且订单金额为0，则减少库存 */
                                if (isset($config['use_storage']) && $config['use_storage'] == '1' && $config['stock_dec_time'] == SDT_PAID) {
                                    $store_id = StoreOrder::where('order_id', $order_id)->value('store_id');
                                    change_order_goods_storage($order_id, true, SDT_PAID, 15, 0, $store_id);
                                }

                                //检查/改变主订单状态
                                check_main_order_status($order_id);

                                /* 记录订单操作记录 */
                                order_action($order_sn, OS_CONFIRMED, SS_UNSHIPPED, $pay_status, $note, trans('payment.buyer'));
                            }

                            $extendParam['order_id'] = $order_id;
                            $extendParam['user_id'] = $user_id;
                            $extendParam['pay_code'] = $pay_code;
                            $extendParam['media_type'] = $media_type;

                            // 更新视频号推广员
                            if (file_exists(WXAPP_MEDIA_PROMOTER)) {
                                event(new \App\Modules\WxMedia\Events\MediaPromoterAffiliateEvent($extendParam));
                            }

                            /* 修改普通子订单状态为已付款 */
                            if ($order['main_count'] > 0) {
                                /* 队列分单 */
                                $filter = [
                                    'order_id' => $order_id,
                                    'user_id' => $user_id
                                ];
                                ProcessSeparateBuyOrder::dispatch($filter);
                            } else {
                                app(JigonManageService::class)->jigonConfirmOrder($order_id); // 贡云确认订单
                            }

                            /* 取得未发货虚拟商品 */
                            $virtual_goods = get_virtual_goods($order_id);
                            if (!empty($virtual_goods) && $order['shipping_status'] == SS_UNSHIPPED) {
                                /* 虚拟卡发货 */
                                $error_msg = '';
                                if (virtual_goods_ship($virtual_goods, $error_msg, $order['order_sn'], true)) {
                                    /* 如果没有实体商品，修改发货状态，送积分和红包 */
                                    $count = OrderGoods::where('order_id', $order_id)->where('is_real', 1)->count();
                                    if ($count <= 0) {
                                        /* 修改订单状态 */
                                        update_order($order_id, ['shipping_status' => SS_SHIPPED, 'shipping_time' => $time]);

                                        /* 记录订单操作记录 */
                                        order_action($order_sn, OS_CONFIRMED, SS_SHIPPED, $pay_status, $note, trans('payment.buyer'));

                                        /* 如果订单用户不为空，计算积分，并发给用户；发红包 */
                                        if ($order['user_id'] > 0) {

                                            /* 计算并发放积分 */
                                            $integral = integral_to_give($order);
                                            log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf(trans('payment.order_gift_integral'), $order['order_sn']));

                                            /* 发放红包 */
                                            send_order_bonus($order_id);

                                            /* 发放优惠券 */
                                            send_order_coupons($order_id);
                                        }

                                        // 发货更新商品销量
                                        if (config('shop.sales_volume_time') == SALES_SHIP) {
                                            \App\Repositories\Order\OrderRepository::increment_goods_sale_ship($order_id, ['order_id' => $order_id, 'pay_status' => $order['pay_status'], 'shipping_status' => SS_SHIPPED]);
                                        }
                                    }
                                }
                            }

                            /* 如果需要，发短信 */
                            $order_goods = OrderGoods::where('order_id', $order_id);
                            $order_goods = BaseRepository::getToArrayFirst($order_goods);

                            $ru_id = $order_goods ? $order_goods['ru_id'] : 0;
                            $stages_qishu = $order_goods ? $order_goods['stages_qishu'] : -1;

                            //商家客服手机号获取
                            $sms_shop_mobile = SellerShopinfo::where('ru_id', $ru_id)->value('mobile');

                            if (isset($config['sms_order_payed']) && $config['sms_order_payed'] == '1' && $sms_shop_mobile != '') {
                                $shop_name = app(MerchantCommonService::class)->getShopName($ru_id, 1);
                                $order_region = $OrderLib->getOrderUserRegion($order_id);
                                //阿里大鱼短信接口参数
                                $smsParams = [
                                    'shop_name' => $shop_name,
                                    'shopname' => $shop_name,
                                    'order_sn' => $order_sn,
                                    'ordersn' => $order_sn,
                                    'consignee' => $order['consignee'],
                                    'order_region' => $order_region,
                                    'orderregion' => $order_region,
                                    'address' => $order['address'],
                                    'order_mobile' => $order['mobile'],
                                    'ordermobile' => $order['mobile'],
                                    'mobile_phone' => $sms_shop_mobile,
                                    'mobilephone' => $sms_shop_mobile
                                ];

                                app(CommonRepository::class)->smsSend($sms_shop_mobile, $smsParams, 'sms_order_payed', false);
                            }

                            //门店处理
                            $stores_order = StoreOrder::where('order_id', $order_id);
                            $stores_order = BaseRepository::getToArrayFirst($stores_order);

                            if ($stores_order && $stores_order['store_id'] > 0) {
                                $user_info = Users::where('user_id', $order['user_id'])->select('mobile_phone', 'user_name');
                                $user_info = BaseRepository::getToArrayFirst($user_info);

                                if ($order['mobile']) {
                                    $user_mobile_phone = $order['mobile'];
                                } else {
                                    $user_mobile_phone = $user_info['mobile_phone'] ?? '';
                                }

                                if (!empty($user_mobile_phone)) {
                                    $pick_code = substr($order['order_sn'], -3) . mt_rand(100, 999);

                                    StoreOrder::where('id', $stores_order['id'])->update(['pick_code' => $pick_code]);

                                    //门店短信处理
                                    $user_name = $user_info['user_name'] ?? '';

                                    //门店订单->短信接口参数
                                    $store_smsParams = [
                                        'user_name' => $user_name,
                                        'username' => $user_name,
                                        'order_sn' => $order_sn,
                                        'ordersn' => $order_sn,
                                        'code' => $pick_code
                                    ];
                                    app(CommonRepository::class)->smsSend($user_mobile_phone, $store_smsParams, 'store_order_code');
                                }

                                // 收银台代码 调用事件 门店 已付款 推单
                                event(new \App\Events\PickupOrdersPayedAfterDoSomething($order));
                            }

                            /* 将白条订单商品更新为普通订单商品 */
                            if ($stages_qishu > 0) {
                                OrderGoods::where('order_id', $order_id)->update(['stages_qishu' => '-1']);
                            }

                            /* 如果安装微信通,订单支付成功消息提醒 */
                            if (file_exists(MOBILE_WECHAT)) {
                                $pushData = [
                                    'first' => ['value' => trans('wechat.order_pay_first')], // 标题
                                    'keyword1' => ['value' => $order_sn, 'color' => '#173177'], // 订单号
                                    'keyword2' => ['value' => trans('admin/common.paid'), 'color' => '#173177'], // 付款状态
                                    'keyword3' => ['value' => date('Y-m-d', $time), 'color' => '#173177'], // 付款时间
                                    'keyword4' => ['value' => $config['shop_name'], 'color' => '#173177'], // 商户
                                    'keyword5' => ['value' => number_format($pay_log['order_amount'], 2, '.', ''), 'color' => '#173177'], // 支付金额
                                ];
                                $url = dsc_url('/#/user/orderDetail/' . $order_id);
                                app(\App\Modules\Wechat\Services\WechatService::class)->push_template('OPENTM204987032', $pushData, $url, $order['user_id']);
                            }

                            if (file_exists(MOBILE_TEAM)) {
                                // 在线支付更新拼团活动状态
                                if (isset($order['team_id']) && $order['team_id'] > 0) {
                                    $orderCommonService = app(OrderCommonService::class);
                                    $team_num = $orderCommonService->teamOrderNum($order['user_id']);
                                    $orderCommonService->updateUserOrderNum($order['user_id'], ['order_team_num' => $team_num]);

                                    app(TeamService::class)->updateTeamInfo($order['team_id'], $order['team_parent_id'], $order['user_id']);
                                }
                            }

                            // 开通购买会员权益卡 订单支付成功 更新成为分销商
                            if (file_exists(MOBILE_DRP)) {
                                app(\App\Modules\Drp\Services\Drp\DrpService::class)->buyOrderUpdateDrpShop($order['user_id'], $order['order_id'], $pay_log);
                            }

                            //订单支付推送消息给多商户商家掌柜事件
                            event(new \App\Events\PushMerchantOrderPayedEvent($order['order_sn']));

                            //订单支付成功事件
                            $order['pay_status'] = $pay_status;
                            event(new \App\Events\OrderPaidEvent($order, $extendParam));
                        }

                    }
                } elseif ($pay_log['order_type'] == PAY_SURPLUS) {

                    /**
                     * 取得添加预付款的用户以及金额
                     * 超过一天的未付款完成的 不处理
                     */
                    $oneday = $time - 24 * 60 * 60;
                    $user_account = UserAccount::where('id', $pay_log['order_id'])->where('add_time', '>', $oneday);
                    $user_account = BaseRepository::getToArrayFirst($user_account);

                    if ($user_account && $user_account['is_paid'] == 0) {

                        /* 更新会员预付款的到款状态 */
                        UserAccount::where('id', $pay_log['order_id'])->update(['paid_time' => $time, 'is_paid' => 1]);

                        /* 修改会员帐户金额 */
                        log_account_change($user_account['user_id'], $user_account['amount'], 0, 0, 0, trans('payment.surplus_type_0'), ACT_SAVING);

                        $user_info = Users::where('user_id', $user_account['user_id']);
                        $user_info = BaseRepository::getToArrayFirst($user_info);

                        //短信接口参数
                        $smsParams = [
                            'user_name' => $user_info['user_name'],
                            'username' => $user_info['user_name'],
                            'user_money' => $user_info['user_money'],
                            'usermoney' => $user_info['user_money'],
                            'op_time' => TimeRepository::getLocalDate('Y-m-d H:i:s', $time),
                            'optime' => TimeRepository::getLocalDate('Y-m-d H:i:s', $time),
                            'add_time' => TimeRepository::getLocalDate('Y-m-d H:i:s', $time),
                            'addtime' => TimeRepository::getLocalDate('Y-m-d H:i:s', $time),
                            'examine' => trans('common.through'),
                            'process_type' => trans('common.surplus_type_0'),
                            'processtype' => trans('common.surplus_type_0'),
                            'fmt_amount' => $user_account['amount'],
                            'fmtamount' => $user_account['amount'],
                            'mobile_phone' => $user_info['mobile_phone'] ? $user_info['mobile_phone'] : '',
                            'mobilephone' => $user_info['mobile_phone'] ? $user_info['mobile_phone'] : ''
                        ];

                        //添加条件 by wu
                        if (isset($config['user_account_code']) && $config['user_account_code'] == '1' && $user_info['mobile_phone'] != '') {
                            app(CommonRepository::class)->smsSend($user_info['mobile_phone'], $smsParams, 'user_account_code', false);
                        }
                    }
                } elseif ($pay_log['order_type'] == PAY_APPLYTEMP) {
                    load_helper('visual');

                    //获取订单信息
                    $seller_template_apply = SellerTemplateApply::where('apply_id', $pay_log['order_id']);
                    $seller_template_apply = BaseRepository::getToArrayFirst($seller_template_apply);

                    //导入已付款的模板
                    $new_suffix = get_new_dir_name($seller_template_apply['ru_id']); //获取新的模板
                    Import_temp($seller_template_apply['temp_code'], $new_suffix, $seller_template_apply['ru_id']);

                    //更新模板使用数量
                    TemplateMall::where('temp_id', $seller_template_apply['temp_id'])->increment('sales_volume', 1);

                    /* 修改申请的支付状态 */
                    $other = [
                        'pay_status' => 1,
                        'pay_time' => $time,
                        'apply_status' => 1
                    ];
                    SellerTemplateApply::where('apply_id', $pay_log['order_id'])->update($other);
                } elseif ($pay_log['order_type'] == PAY_APPLYGRADE) {

                    /* 修改申请的支付状态 */
                    $other = [
                        'is_paid' => 1,
                        'pay_time' => $time,
                        'pay_status' => 1
                    ];
                    SellerApplyInfo::where('apply_id', $pay_log['order_id'])->update($other);
                } elseif ($pay_log['order_type'] == PAY_TOPUP) {
                    $account_log = SellerAccountLog::where('log_id', $pay_log['order_id']);
                    $account_log = BaseRepository::getToArrayFirst($account_log);

                    if ($account_log && $account_log['is_paid'] == 0) {

                        /* 修改商家充值的支付状态 */
                        SellerAccountLog::where('log_id', $pay_log['order_id'])->update(['is_paid' => 1]);

                        /* 改变商家金额 */
                        SellerShopinfo::where('ru_id', $account_log['ru_id'])->increment('seller_money', $pay_log['order_amount']);

                        $log = [
                            'user_id' => $account_log['ru_id'],
                            'user_money' => $pay_log['order_amount'],
                            'change_time' => $time,
                            'change_desc' => trans('order.merchant_handle_recharge'),
                            'change_type' => 2
                        ];
                        MerchantsAccountLog::insert($log);
                    }
                } elseif ($pay_log['order_type'] == PAY_WHOLESALE) {
                    if (!file_exists(SUPPLIERS)) {
                        return false;
                    }

                    $extendParam = [
                        'pay_status' => $pay_status,
                        'shop_config' => config('shop'),
                    ];
                    event(new \App\Modules\Suppliers\Events\WholesaleOrderPaidEvent($pay_log, $extendParam));

                } elseif ($pay_log['order_type'] == PAY_REGISTERED) {
                    // 购买指定金额成为分销商
                    if (file_exists(MOBILE_DRP)) {
                        app(\App\Modules\Drp\Services\Drp\DrpService::class)->buyUpdateDrpShop($pay_log);
                    }
                } elseif ($pay_log['order_type'] == PAY_GROUPBUY_ORDER) { // 社区团购
                    if (file_exists(MOBILE_GROUPBUY)) {
                        app(\App\Modules\Cgroup\Services\Order\OrderCommonService::class)->groupOrderPaid($pay_log);
                    }
                }
            } else {
                $order_id = $pay_log['order_id'];

                $order = $OrderLib->getUnPayedOrderInfo($order_id);

                /* 取得未发货虚拟商品 */
                $virtual_goods = get_virtual_goods($order_id);

                if (!empty($virtual_goods)) {
                    /* 虚拟卡发货 */
                    $error_msg = '';
                    if (virtual_goods_ship($virtual_goods, $error_msg, $order['order_sn'], true)) {
                        if ($order['shipping_id'] == -1 || empty($order['shipping_id'])) {

                            /* 如果没有实体商品，修改发货状态，送积分和红包 */
                            $count = OrderGoods::where('order_id', $order_id)->where('is_real', 1)->count();

                            if ($count <= 0) {
                                /* 修改订单状态 */
                                update_order($order_id, ['shipping_status' => SS_RECEIVED, 'shipping_time' => $time]);

                                /* 记录订单操作记录 */
                                order_action($order_sn, OS_CONFIRMED, SS_RECEIVED, $order['pay_status'], $note, trans('payment.surplus_type_0'));

                                /* 如果订单用户不为空，计算积分，并发给用户；发红包 */
                                if ($order['user_id'] > 0) {

                                    /* 计算并发放积分 */
                                    $integral = integral_to_give($order);
                                    log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf(trans('payment.order_gift_integral'), $order['order_sn']));

                                    /* 发放红包 */
                                    send_order_bonus($order_id);

                                    /* 发放优惠券 bylu */
                                    send_order_coupons($order_id);
                                }
                            }
                        }
                    }
                }

                $is_number = order_virtual_card_count($order_id);
                $pay_success = trans('payment.pay_success');
                $virtual_goods_ship_fail = trans('payment.virtual_goods_ship_fail');
                if ($is_number == 1) {
                    $pay_success .= '<br />' . $virtual_goods_ship_fail;
                }
            }
        }
    }

    /* 执行更新会员订单信息 */
    $user_id = $order['user_id'] ?? 0;
    if (empty($user_id)) {
        $user_id = session()->exists('user_id') ? session('user_id', 0) : 0;
    }

    if ($user_id > 0) {
        app(OrderCommonService::class)->getUserOrderNumServer($user_id);
    }
}

/**
 * 获得订单需要支付的支付费用
 *
 * @access  public
 * @param integer $payment_id
 * @param float $order_amount
 * @param null $cod_fee
 * @return  float
 */
function order_pay_fee($payment_id = 0, $order_amount = 0, $cod_fee = null)
{
    return app(PaymentService::class)->order_pay_fee($payment_id, $order_amount, $cod_fee);
}
