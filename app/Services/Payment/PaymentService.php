<?php

namespace App\Services\Payment;

use App\Models\OrderInfo;
use App\Models\OrderReturn;
use App\Models\PayLog;
use App\Models\Payment;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\StrRepository;
use App\Repositories\Payment\PaymentRepository;
use App\Repositories\User\ConnectUserRepository;

/**
 * Class PaymentService
 * @package App\Services\Payment
 */
class PaymentService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 获取支付信息
     * @param array $where
     * @param array $columns 查询指定列
     * @return array
     */
    public static function getPaymentInfo($where = [], $columns = [])
    {
        if (empty($where)) {
            return [];
        }

        return PaymentRepository::getPaymentInfo($where, $columns);
    }

    /**
     * 订单确认页可选支付列表
     *
     * @param int $order_id
     * @param int $support_cod
     * @param int $cod_fee
     * @param int $is_online
     * @return array
     * @throws \Exception
     */
    public function getPaymentList($order_id = 0, $support_cod = 0, $cod_fee = 0, $is_online = 0)
    {
        $list = $this->availablePaymentList($support_cod, $cod_fee, $is_online);

        if (empty($list)) {
            return [];
        }

        $pay_id = OrderInfo::where('order_id', $order_id)->value('pay_id');

        foreach ($list as $k => $val) {
            if (isset($pay_id) && $val['pay_id'] == $pay_id) {
                $list[$k]['selected'] = true;
            }
        }

        return $list;
    }

    /**
     * 取得可用的支付方式列表
     *
     * @param int $support_cod 配送方式是否支持货到付款
     * @param int $cod_fee 货到付款手续费（当配送方式支持货到付款时才传此参数）
     * @param int $is_online 是否支持在线支付
     * @param int $is_balance 是否支持余额支付
     * @param int $is_bank 是否支持银行转账
     * @return array
     * @throws \Exception
     */
    public function availablePaymentList($support_cod = 0, $cod_fee = 0, $is_online = 0, $is_balance = 0, $is_bank = 0)
    {
        $payment_list = Payment::select('pay_id', 'pay_code', 'pay_name', 'pay_fee', 'is_online', 'pay_desc', 'is_cod')
            ->where('enabled', 1);

        // 如果不支持货到付款
        if ($support_cod == 0) {
            $payment_list = $payment_list->where(function ($query) {
                $query->where('pay_code', '<>', 'cod')->orWhere('is_cod', 0);
            });
        }
        if ($is_online == 1) {
            $payment_list = $payment_list->where('is_online', 1); // 在线支付
        }

        $payment_list = $payment_list->orderBy('pay_order', 'ASC')->orderBy('pay_id', 'DESC');

        $payment_list = BaseRepository::getToArrayGet($payment_list);

        if (empty($payment_list)) {
            return ['code' => 1, 'msg' => lang('payment.pay_not_install')];
        }

        foreach ($payment_list as $key => $payment) {

            $payment_list[$key]['format_pay_fee'] = $payment_list[$key]['pay_fee'] = $this->pay_fee_list($payment, $cod_fee);

            //pc端去除ecjia的支付方式
            if (substr($payment['pay_code'], 0, 4) == 'pay_') {
                unset($payment_list[$key]);
                continue;
            }

            $plugins = plugin_path('Payment/' . StrRepository::studly($payment['pay_code']) . '/' . StrRepository::studly($payment['pay_code']) . '.php');
            if (!file_exists($plugins)) {
                unset($payment_list[$key]);
            }
            // 白条支付
            if ($payment['pay_code'] == 'chunsejinrong') {
                unset($payment_list[$key]);
                continue;
            }
            // 收银台 不显示 在线支付, 仅显示 微信或支付宝等
            if ($is_online == 1 && $payment['is_online'] != 1) {
                unset($payment_list[$key]);
            }
            // 结算页 显示在线支付
            if ($is_online == 0 && $payment['is_online'] == 1) {
                unset($payment_list[$key]);
            }
            // 手机端 是否支持余额支付
            if ($payment['pay_code'] == 'balance' && $is_balance == 0) {
                unset($payment_list[$key]);
            }

            // 是否支持银行转账
            if ($payment['pay_code'] == 'bank' && $is_bank == 0) {
                unset($payment_list[$key]);
            }

            // 手机端 不显示邮局汇款
            if ($payment['pay_code'] == 'post') {
                unset($payment_list[$key]);
            }

            if ($payment['pay_code'] == 'wxpay') {
                if (is_wechat_browser() && !file_exists(MOBILE_WECHAT)) {
                    unset($payment_list[$key]);
                }
                // 非微信浏览控制显示h5
                if (is_wechat_browser() == false && $this->is_wxh5() == 0) {
                    unset($payment_list[$key]);
                }
            }
        }

        return empty($payment_list) ? [] : collect($payment_list)->values()->all();
    }

    /**
     * 取得支付按钮
     *
     * @param int $user_id
     * @param int $pay_id
     * @param array $order
     * @return array
     * @throws \Exception
     */
    public function getPaymentCode($user_id = 0, $pay_id = 0, $order = [])
    {
        if (empty($user_id) || empty($pay_id)) {
            return ['code' => 1, 'msg' => lang('common.illegal_operate')];
        } else {
            $payment_info = $this->getpaymentInfo(['pay_id' => $pay_id, 'enabled' => 1]);

            $order['pay_desc'] = $payment_info['pay_desc'];

            $payment = $this->getPayment($payment_info['pay_code']);

            if ($payment === false) {
                return ['code' => 1, 'msg' => lang('common.illegal_operate')];
            }

            return $payment->get_code($order, unserialize_config($payment_info['pay_config']), $user_id);
        }
    }

    /**
     * 支付同步回调通知
     *
     * @param array $info
     * @param string $code
     * @return array
     * @throws \Exception
     */
    public function getCallback($info = [], $code = '')
    {
        $log_id = isset($info['log_id']) ? intval($info['log_id']) : 0;

        $payment = $this->getPayment($code);
        // 提示类型
        if ($payment === false) {
            $result = [
                'msg' => lang('payment.pay_disabled'),
                'msg_type' => 2,
            ];
        } else {
            if ($code == 'alipay') {
                $log_id = $payment->parse_trade_no($info['out_trade_no']);
            }

            // 微信h5中间页面
            if (isset($info['type']) && $info['code'] == 'wxpay' && $info['type'] == 'wxh5') {
                $result = $this->Wxh5($code, $log_id);
                // 跳转至h5中间页面
                return $result;
            }

            if ($payment->callback()) {
                $result = [
                    'msg' => lang('payment.pay_success'),
                    'msg_type' => 0,
                ];
            } else {
                $result = [
                    'msg' => lang('payment.pay_fail'),
                    'msg_type' => 1,
                ];
            }

            $result['url'] = dsc_url('/#/home');

            // 根据不同订单类型（普通、充值） 跳转
            if (isset($log_id) && !empty($log_id)) {
                $pay_log = PayLog::query()->select('order_type', 'order_id')->where('log_id', $log_id)->orderBy('log_id', 'DESC')->first();
                $pay_log = $pay_log ? $pay_log->toArray() : [];

                if (empty($pay_log)) {
                    return ['msg' => lang('payment.pay_fail'), 'msg_type' => 1, 'url' => url('mobile/#/home')];
                }

                // 订单类支付
                if ($pay_log['order_type'] == PAY_ORDER) {
                    $order = OrderInfo::query()->select('order_id', 'extension_code', 'team_id', 'is_zc_order', 'zc_goods_id')->where('order_id', $pay_log['order_id'])->first();
                    $order = $order ? $order->toArray() : [];

                    if (empty($order)) {
                        return ['msg' => lang('payment.pay_fail'), 'msg_type' => 1, 'url' => url('mobile/#/')];
                    }

                    $result['extension_code'] = $order['extension_code'] ?? '';
                    if ($order['extension_code'] == 'team_buy') {
                        // 拼团
                        $result['extension_code'] = $order['extension_code'];
                        $result['url'] = dsc_url('/#/team/wait') . '?' . http_build_query(['team_id' => $order['team_id'], 'status' => 1], '', '&');
                    } elseif ($order['is_zc_order'] == 1 && $order['zc_goods_id'] > 0) {
                        // 众筹
                        $result['extension_code'] = 'crowd_buy';
                        $result['url'] = dsc_url('/#/crowdfunding/order');
                    } else {
                        // 普通订单
                        $result['url'] = dsc_url('/#/user/orderDetail/' . $pay_log['order_id']);
                    }
                } elseif ($pay_log['order_type'] == PAY_WHOLESALE) {
                    // 供求订单支付
                    $result['url'] = dsc_url('/#/supplier/orderlist');
                } else {
                    // 无订单类支付
                    if ($pay_log['order_type'] == PAY_REGISTERED) {
                        // 分销购买
                        $result['extension_code'] = 'drp_buy';
                        $result['url'] = dsc_url('/#/drp/drpinfo');
                    } elseif ($pay_log['order_type'] == PAY_SURPLUS) {
                        // 会员充值
                        $result['url'] = dsc_url('/#/user/account');
                    }
                }

                return $result;
            }
        }

        return $result;
    }

    /**
     * 获得支付实例
     *
     * @param $code
     * @return bool|\Illuminate\Foundation\Application|mixed
     */
    public function getPayment($code)
    {
        /* 判断启用状态 */
        $condition = [
            'pay_code' => $code,
            'enabled' => 1
        ];
        $enabled = Payment::where($condition)->count();

        $plugin = false;
        if ($code && strpos($code, 'pay_') === false) {
            $plugin = CommonRepository::paymentInstance($code);
            if (is_null($plugin) || $enabled == 0) {
                return false;
            }
        }

        /* 实例化插件 */
        return $plugin;
    }

    /**
     * 微信支付h5同步通知中间页面
     *
     * @param string $code
     * @param int $log_id
     * @return array
     */
    public function Wxh5($code = '', $log_id = 0)
    {
        // 显示页面
        if (!empty($log_id)) {
            $log_id = intval($log_id);
            $pay_log = PayLog::query()->select('order_type', 'order_id', 'is_paid')->where('log_id', $log_id)->orderBy('log_id', 'DESC')->first();
            $pay_log = $pay_log ? $pay_log->toArray() : [];

            // order_type 0 普通订单, 1 会员充值订单
            if ($pay_log['order_type'] == PAY_ORDER) {
                $order_url = dsc_url('/#/user/orderDetail') . '/' . $pay_log['order_id'];
            } elseif ($pay_log['order_type'] == PAY_SURPLUS) {
                $order_url = dsc_url('/#/user/account');
            } elseif ($pay_log['order_type'] == PAY_REGISTERED) {
                //分销购买
                $order_url = dsc_url('/#/drp');
            } elseif ($pay_log['order_type'] == PAY_TOPUP) {
                //拼团
                $order_url = dsc_url('/#/team/order');
            } elseif ($pay_log['order_type'] == PAY_WHOLESALE) {
                //供求
                $result['url'] = dsc_url('/#/supplier/orderlist');
            } else {
                $order_url = dsc_url('/#/user/order');
            }
            // 支付状态
            $args = [
                'code' => $code,
                'status' => $pay_log['is_paid'] ?? 0,
                'log_id' => $log_id
            ];
            $respond_url = dsc_url('/#/respond?' . http_build_query($args, '', '&'));
        } else {
            $args = [
                'code' => $code,
                'status' => 0
            ];
            $respond_url = dsc_url('/#/respond?' . http_build_query($args, '', '&'));
        }

        $is_wxh5 = ($code == 'wxpay' && !is_wechat_browser()) ? 1 : 0;

        $result = [
            'is_wxh5' => $is_wxh5,
            'repond_url' => $respond_url,
            'order_url' => $order_url ?? ''
        ];
        return $result;
    }

    /**
     * 支付异步回调通知
     *
     * @param string $code
     * @param string $refer
     * @param string $type
     * @return array
     */
    public function getNotify($code, $refer = '', $type = '')
    {
        $payment = $this->getPayment($code);
        if ($payment === false) {
            return [];
        }

        return $payment->notify($refer, $type);
    }

    /**
     * 合单支付异步回调通知
     *
     * @param string $code
     * @param string $refer
     * @param string $type
     * @return array
     */
    public function getNotifyCombine($code, $refer = '', $type = '')
    {
        $payment = $this->getPayment($code);
        if ($payment === false) {
            return [];
        }

        return $payment->notifyCombine($refer, $type);
    }

    /**
     * 退款异步回调通知
     *
     * @param string $code
     * @param string $refer
     * @return array
     */
    public function getNotifyRefound($code, $refer = '')
    {
        $payment = $this->getPayment($code);
        if ($payment === false) {
            return [];
        }

        return $payment->notify_refound($refer);
    }

    /**
     * 合单支付退款异步回调通知
     *
     * @param string $code
     * @param string $refer
     * @return array
     */
    public function getNotifyCombineRefound($code, $refer = '')
    {
        $payment = $this->getPayment($code);
        if ($payment === false) {
            return [];
        }

        return $payment->notifyCombineRefund($refer);
    }

    /**
     * 计算订单支付手续费
     * @param int $payment_id
     * @param int $order_amount
     * @param int $cod_fee
     * @return float
     */
    public function order_pay_fee($payment_id = 0, $order_amount = 0, $cod_fee = null)
    {
        return PaymentRepository::order_pay_fee($payment_id, $order_amount, $cod_fee);
    }

    /**
     * 支付列表手续费
     * @param array $payment
     * @param null $cod_fee
     * @return float|int
     */
    public function pay_fee_list($payment = [], $cod_fee = null)
    {
        // 是否启用支付手续费
        $use_pay_fee = config('shop.use_pay_fee') ?? 0;
        if ($use_pay_fee == 0) {
            return 0;
        }

        if (empty($payment)) {
            return 0;
        }

        // 在线支付、余额支付、白条支付 手续费为 0
        if (in_array($payment['pay_code'], ['onlinepay', 'balance', 'chunsejinrong'])) {
            return 0;
        }

        $rate = ($payment['is_cod'] && !is_null($cod_fee)) ? $cod_fee : $payment['pay_fee'];

        if (strpos($rate, '%') !== false) {
            /* 支付费用是一个比例 */
            return $rate;
        } else {
            $pay_fee = floatval($rate);

            if ($pay_fee > 0) {
                $pay_fee = round($pay_fee, 2);
                $pay_fee = $this->dscRepository->getPriceFormat($pay_fee);
            }

            return $pay_fee;
        }
    }

    /**
     * 切换支付方式
     *
     * @param int $order_id
     * @param int $pay_id
     * @param int $user_id
     * @return bool
     * @throws \Exception
     */
    public function change_payment($order_id = 0, $pay_id = 0, $user_id = 0)
    {
        $order_id = intval($order_id);
        if (empty($order_id)) {
            return false;
        }

        /* 订单详情 */
        /* 计算订单各种费用之和的语句 */
        $total_fee = " (goods_amount - discount + tax + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee) AS total_fee ";

        $order = OrderInfo::selectRaw("*, $total_fee")->where('order_id', $order_id)->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED]);
        $order = BaseRepository::getToArrayFirst($order);

        if (empty($order) || $order['user_id'] != $user_id) {
            return false;
        }

        if ($order['pay_status'] == PS_PAYED) {
            return false;
        }

        // 改变订单的支付名称和支付id
        $payment_info = $this->getPaymentInfo(['pay_id' => $pay_id, 'enabled' => 1]);
        if (empty($payment_info)) {
            return false;
        }

        if (file_exists(MODULES_DIVIDE) && $payment_info['pay_code'] != 'balance' && $order['media_type'] == 0) {
            // 收付通合单支付切换
            return \App\Manager\DivideTrace\Services\Payment\CombinePaymentService::change_payment($order, $payment_info, $user_id);
        }

        // 计算应付款金额（不包括支付费用）
        // $order['order_amount'] = $order['goods_amount'] - $order['discount'] + $order['shipping_fee'] + $order['insure_fee'] + $order['pack_fee'] + $order['card_fee'] - $order['bonus'] - $order['coupons'] - $order['integral_money'] - $order['surplus'] - $order['money_paid'];

        // 计算支付手续费
        $order['order_amount'] = $order['order_amount'] - $order['pay_fee'];
        $pay_fee = $this->order_pay_fee($payment_info['pay_id'], $order['order_amount']);
        $order_amount = $order['order_amount'] + $pay_fee;

        $payData = [
            'pay_id' => $payment_info['pay_id'],
            'pay_name' => $payment_info['pay_name'],
            'pay_code' => $payment_info['pay_code'],
            'pay_fee' => $pay_fee,
            'order_amount' => $order_amount
        ];
        OrderInfo::where('order_id', $order_id)->update($payData);

        //是否有子订单
        $child_order = OrderInfo::query()->where('main_order_id', $order_id)->select('order_id', 'order_amount')->get();
        $child_order = $child_order ? $child_order->toArray() : [];
        if ($order['main_order_id'] == 0 && !empty($child_order)) {
            foreach ($child_order as $child) {
                // 子订单支付手续费
                $childData = [
                    'pay_id' => $payment_info['pay_id'],
                    'pay_name' => $payment_info['pay_name'],
                    'pay_code' => $payment_info['pay_code'],
                    'pay_fee' => PaymentRepository::child_order_pay_fee($child['order_amount'], $order['order_amount'], $pay_fee),
                ];
                OrderInfo::where('order_id', $child['order_id'])->update($childData);
            }
        }

        //获取log_id
        $model = PayLog::query()->where('order_id', $order_id)->where('order_type', PAY_ORDER)->select('log_id', 'order_amount')->orderBy('log_id', 'DESC')->first();
        $order['log_id'] = $model->log_id ?? 0;

        // 有支付手续费 更新 pay_log
        if (isset($pay_fee) && $pay_fee) {
            $model->order_amount = $order['order_amount'] + $pay_fee;
            $model->save();
        }

        $order['pay_desc'] = $payment_info['pay_desc'];

        $payment = $this->getPayment($payment_info['pay_code']);

        if ($payment === false) {
            return false;
        }

        $button = $payment->get_code($order, unserialize_config($payment_info['pay_config']), $user_id);

        if ($payment_info['pay_code'] == 'balance') {
            $button = "<a href='flow.php?step=done&act=balance&order_sn=" . $order['order_sn'] . "'>" . lang('flow.balance_pay') . "</a>";
        }

        if ($button == false) {
            return false;
        }

        $result['code'] = 0;
        $result['order_amount'] = $order_amount;
        $result['order_amount_format'] = $this->dscRepository->getPriceFormat($order_amount);
        $result['pay_fee'] = $pay_fee;
        $result['button'] = $button;

        return $result;
    }

    /**
     * 是否开通微信h5配置
     * @return int
     */
    protected static function is_wxh5()
    {
        $rs = Payment::where(['pay_code' => 'wxpay'])->value('pay_config');
        $config = [];
        if (!empty($rs)) {
            $rs = unserialize($rs);
            foreach ($rs as $key => $value) {
                $config[$value['name']] = $value['value'];
            }
        }

        return (isset($config) && isset($config['is_h5'])) ? $config['is_h5'] : 0;
    }

    /**
     * 记录订单交易信息
     *
     * @param int $log_id
     * @param array $data
     * @param int $divide_channel
     * @return bool
     */
    public static function updatePayLog($log_id = 0, $data = [], $divide_channel = 0)
    {
        if ($log_id > 0 && $data) {
            // 数组转json
            $pay_trade_data = is_array($data) ? json_encode($data, JSON_UNESCAPED_SLASHES) : $data;

            $model = PayLog::where('log_id', $log_id);

            $model = $model->first();
            if ($model) {
                $transaction_id = $data['transaction_id'] ?? '';
                if (!empty($transaction_id)) {
                    $model->transid = $transaction_id;
                }

                // 普通支付订单
                if ($model->order_id > 0 && $model->order_type == PAY_ORDER) {
                    $child_order = OrderInfo::query()->where('main_order_id', $model->order_id)->pluck('order_id');
                    $child_order_id = $child_order ? $child_order->toArray() : [];
                    if (!empty($child_order_id)) {
                        // 是否有子订单、更新子订单支付日志
                        PayLog::whereIn('order_id', $child_order_id)->where('order_amount', '>', 0)->update(['transid' => $transaction_id, 'pay_trade_data' => $pay_trade_data]);
                    }
                }

                if (file_exists(MODULES_DIVIDE)) {
                    $model->divide_channel = $divide_channel;
                }

                $model->pay_trade_data = $pay_trade_data;
                return $model->save();
            }
        }
    }

    /**
     * 记录退款交易信息
     *
     * @param string $return_sn
     * @param string $order_sn
     * @param array $data
     */
    public static function updateReturnLog($return_sn = '', $order_sn = '', $data = [])
    {
        if ($data) {
            // 数组转json
            $return_trade_data = is_array($data) ? json_encode($data, JSON_UNESCAPED_SLASHES) : $data;

            if ($order_sn) {
                $model = OrderReturn::where('order_sn', $order_sn)->first();
            } else {
                $model = OrderReturn::where('return_sn', $return_sn)->first();
            }

            if ($model) {
                $model->return_trade_data = $return_trade_data;
                $model->save();
            }
        }
    }

    /**
     * 优化切换支付方式后支付 异步更新订单支付方式
     * @param string $order_sn
     * @param string $pay_code
     * @param array $payment
     * @param int $divide_channel
     * @return bool
     */
    public static function updateOrderPayment($order_sn = '', $pay_code = '', $payment = [], $divide_channel = 0)
    {
        if (!empty($order_sn)) {
            // 通过pay_code查
            if (!empty($pay_code)) {
                $payment = Payment::where('pay_code', $pay_code)->select('pay_id', 'pay_name')->first();
                $payment = $payment ? $payment->toArray() : [];
            }

            $orderModel = OrderInfo::where('order_sn', $order_sn)->first();

            if ($orderModel) {
                // 白条支付 用支付宝或微信支付还款 不更新支付方式
                $order_pay_code = Payment::where('pay_id', $orderModel->pay_id)->value('pay_code');
                if ($order_pay_code == 'chunsejinrong') {
                    return false;
                }

                if (file_exists(MODULES_DIVIDE)) {
                    $orderModel->divide_channel = $divide_channel;
                }

                //是否有子订单
                if ($orderModel->main_order_id == 0 && $orderModel->main_count > 0) {
                    // 更新子订单支付方式
                    $childData = [
                        'pay_id' => $payment['pay_id'],
                        'pay_name' => $payment['pay_name'],
                    ];
                    OrderInfo::query()->where('main_order_id', $orderModel->order_id)->update($childData);
                }

                $orderModel->pay_id = $payment['pay_id'];
                $orderModel->pay_name = $payment['pay_name'];
                return $orderModel->save;
            }
        }
    }

    /**
     * 微信支付二维码
     * @param string $code_url
     * @return bool|string
     */
    public static function wxpayQrcode($code_url = '')
    {
        if (empty($code_url)) {
            return false;
        }

        $img_src = route('qrcode', ['code_url' => $code_url, 't' => time()]);

        $qrcode_right = asset('themes/ecmoban_dsc2017/images/weixin-qrcode.jpg');
        $sj = asset('themes/ecmoban_dsc2017/images/sj.png');

        $wxpay_lbi = '<div id="wxpay_dialog" class="hide">' .
            '<div class="modal-box">' .
            '<div class="modal-left">' .
            '<p><span>请使用 </span><span class="orange">微信 </span><i class="icon icon-qrcode"></i><span class="orange"> 扫一扫</span><br>扫描二维码支付</p>' .
            '<div class="modal-qr">' .
            '<div class="modal-qrcode"><img src="' . $img_src . '" /></div>' .
            '<div class="model-info"><img src="' . $sj . '" class="icon-clock" /><span>二维码有效时长为2小时, 请尽快支付</span></div>' .
            '</div>' .
            '</div>' .
            '<div class="modal-right"><img src="' . $qrcode_right . '" /></div>' .
            '</div>' .
            '</div>';

        return $wxpay_lbi;
    }

    /**
     * 取得支付方式id列表
     * @param bool $is_cod 是否货到付款
     * @return  array
     */
    public function paymentIdList($is_cod)
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
     * 支付日志关联查询未支付、有效订单信息 （用于支付订单查询）
     * @param int $log_id
     * @return array
     */
    public static function getUnPayOrder($log_id = 0)
    {
        if (empty($log_id)) {
            return [];
        }

        $model = PayLog::query()->where('log_id', $log_id);

        // 订单状态 未确认，已确认、已分单、未支付
        $model = $model->with([
            'orderInfo' => function ($query) {
                $query->select('order_id', 'main_order_id', 'order_sn', 'referer', 'divide_channel')->whereIn('order_status', [OS_UNCONFIRMED, OS_CONFIRMED, OS_SPLITED])->whereIn('pay_status', [PS_PAYING, PS_UNPAYED]);
            }
        ]);

        $model = $model->orderBy('log_id', 'DESC')
            ->first();

        $payLog = $model ? $model->toArray() : [];

        if ($payLog) {
            if ($payLog['order_type'] == PAY_ORDER) {
                if (isset($payLog['order_info']) && !empty($payLog['order_info'])) {
                    $payLog = collect($payLog)->merge($payLog['order_info'])->except('order_info')->all();

                    // 子订单有主订单 查询主订单交易信息
                    $payLog['main_pay_log'] = [];
                    if (!empty($payLog['main_order_id']) && $payLog['main_order_id'] > 0) {
                        $main_pay_log = PayLog::query()->where('order_id', $payLog['main_order_id'])->where('order_type', PAY_ORDER)->orderBy('log_id', 'DESC')->first();
                        $payLog['main_pay_log'] = $main_pay_log ? $main_pay_log->toArray() : [];
                    }
                }
            }
        }

        return $payLog;
    }

    /**
     * 支付日志关联查询已支付、有效订单信息 （用于支付退款申请）
     * @param array $return_order
     * @param int $order_type 支付类型 0 订单支付 等 详见 constant.php
     * @return array
     */
    public static function getPaidOrder($return_order = [], $order_type = PAY_ORDER)
    {
        if ($order_type == PAY_ORDER) {
            // 订单支付 log
            $order_id = $return_order['order_id'] ?? 0;

            return self::payLogOrder($order_id);
        }

        if ($order_type == PAY_GROUPBUY_ORDER) {
            // 订单支付 log
            $order_id = $return_order['order_id'] ?? 0;

            return self::payGroupbuyLogOrder($order_id);
        }

        return [];
    }

    /**
     * 订单支付 log
     * @param int $order_id
     * @return array
     */
    protected static function payLogOrder($order_id = 0)
    {
        if (empty($order_id)) {
            return [];
        }

        $model = PayLog::query()->where('order_id', $order_id)->where('order_type', PAY_ORDER);

        // 订单状态 已确认、已分单、已支付(部分付款--预售定金)、未退款
        $model = $model->with([
            'orderInfo' => function ($query) {
                $query->select('order_id', 'main_order_id', 'order_sn', 'referer')
                    ->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART, OS_RETURNED_PART])
                    ->whereIn('pay_status', [PS_PAYED, PS_PAYED_PART, PS_REFOUND_PART]);
            }
        ]);

        $model = $model->orderBy('log_id', 'DESC')
            ->first();

        $payLog = $model ? $model->toArray() : [];

        if ($payLog) {
            if (isset($payLog['order_info']) && !empty($payLog['order_info'])) {
                $payLog = collect($payLog)->merge($payLog['order_info'])->except('order_info')->all();

                // 子订单有主订单 查询主订单交易信息
                $payLog['main_pay_log'] = [];
                if (!empty($payLog['main_order_id']) && $payLog['main_order_id'] > 0) {
                    $main_pay_log = PayLog::query()->where('order_id', $payLog['main_order_id'])->where('order_type', PAY_ORDER)->orderBy('log_id', 'DESC')->first();
                    $payLog['main_pay_log'] = $main_pay_log ? $main_pay_log->toArray() : [];
                }
            }
        }

        return $payLog;
    }

    /**
     * 社区团购订单支付 log
     * @param int $order_id
     * @return array
     */
    protected static function payGroupbuyLogOrder($order_id = 0)
    {
        if (empty($order_id)) {
            return [];
        }

        $model = PayLog::where('order_id', $order_id)->where('order_type', PAY_GROUPBUY_ORDER);
        $model = $model->orderBy('log_id', 'DESC')
            ->first();

        return $model ? $model->toArray() : [];
    }

    /**
     * 获取用户openid
     * @param int $user_id
     * @param string $type
     * @return mixed|string
     */
    public static function get_openid($user_id = 0, $type = 'wechat')
    {
        return ConnectUserRepository::get_openid($user_id, $type);
    }
}
