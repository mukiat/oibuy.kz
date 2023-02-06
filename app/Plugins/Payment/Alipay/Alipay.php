<?php

namespace App\Plugins\Payment\Alipay;

use App\Models\OrderReturn;
use App\Notifications\Order\OrderPaidNotify;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Payment\PaymentRepository;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\Log;
use Payment\Client\Charge;
use Payment\Client\Notify;
use Payment\Client\Query;
use Payment\Client\Refund;
use Payment\Common\PayException;
use Payment\Config;

/**
 * 支付宝
 *
 * Class Alipay
 * @package App\Plugins\Payment\Alipay
 */
class Alipay
{
    public $errCode = 0;
    public $errMsg = '';

    /**
     * 生成支付代码
     *
     * @param array $order
     * @param array $pay_config
     * @return mixed|string
     */
    public function get_code($order = [], $pay_config = [])
    {
        // 订单信息
        $payData = [
            'body' => $order['order_sn'],
            'subject' => (isset($order['subject']) && !empty($order['subject'])) ? $order['subject'] : $order['order_sn'],
            'order_no' => $this->make_trade_no($order['log_id'], $order['order_amount']),
            'amount' => $order['order_amount'],// 单位为元 ,最小为0.01
            'return_param' => (string)$order['log_id'],// 一定不要传入汉字，只能是 字母 数字组合
            'client_ip' => request()->getClientIp(),// 客户地址
            'goods_type' => 1,
            'store_id' => '',
        ];

        // 支付超时设置
        $pay_effective_time = config('shop.pay_effective_time', 0); // 单位：分钟
        if ($pay_effective_time > 0) {
            $time_expire = $order['add_time'] + $pay_effective_time * 60;
            $time_expire_format = TimeRepository::getLocalDate('Y-m-d H:i', $time_expire);
            $payData['time_expire'] = $time_expire_format; // 绝对超时时间，格式为yyyy-MM-dd HH:mm。
        }

        // App 支付
        $platform = request()->get('platform');
        if ($platform === 'APP') {
            $channel = Config::ALI_CHANNEL_APP;
            $refer = 'app';
        } else {
            $channel = is_mobile_device() ? Config::ALI_CHANNEL_WAP : Config::ALI_CHANNEL_WEB;
            // 来源
            $refer = is_mobile_device() ? 'mobile' : '';
        }

        try {
            $payUrl = Charge::run($channel, $this->getConfig($refer, $pay_config), $payData);
        } catch (PayException $e) {
            // 异常处理
            Log::error($e->getMessage());
            return false;
        }

        /* 生成支付按钮 */
        if (isset($payUrl)) {
            if ($refer == 'app' || (isset($order['merge']) && $order['merge'] == 1)) {
                return $payUrl;
            } elseif (is_mobile_device()) {
                return '<a type="button" class="box-flex btn btn-submit min-two-btn" onclick="javascript:_AP.pay(\'' . $payUrl . '\')">支付宝支付</a>';
            } else {
                return '<input type="button" onclick="window.open(\'' . $payUrl . '\')" value="支付宝支付" />';
            }
        }
        return false;
    }

    /**
     * 支付同步通知 (PC)
     * @return mixed
     */
    public function respond()
    {
        try {
            $log_id = $this->parse_trade_no(request()->get('out_trade_no'));
            $order = [];
            $order['log_id'] = $log_id;

            return $this->orderQuery($order);
        } catch (PayException $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    /**
     * 支付同步通知 (手机)
     * @return mixed
     */
    public function callback()
    {
        return $this->respond();
    }

    /**
     * 支付异步通知 (PC+手机)
     *
     * @param string $refer 来源
     * @param string $type 支付类型
     * @return mixed
     */
    public function notify($refer = '', $type = '')
    {
        try {
            CommonRepository::notifyPay();

            $callback = app(OrderPaidNotify::class);

            $ret = Notify::run(Config::ALI_CHARGE, $this->getConfig($refer), $callback);// 处理回调，内部进行了签名检查
            return $ret;
        } catch (PayException $e) {
            Log::error($e->getMessage());
            return 'fail';
        }
    }

    /**
     * 退款异步通知 (PC+手机)
     * alipay 退款的异步通知是依据支付接口的触发条件来触发的，异步通知也是发送到支付接口传入的异步地址上
     *
     * @param string $refer 来源
     * @return string
     */
    public function notify_refound($refer = '')
    {
        // 这里如果退款触发了异步信息，退款的异步信息中会有 refund_fee 退款总金额参数，如果有这个参数就可以确定这一笔退款成功了
        return $this->notify($refer);
    }

    /**
     * 订单查询
     *
     * @param array $order
     * @return bool
     * @throws \Exception
     */
    public function orderQuery($order = [])
    {
        $payLog = PaymentService::getUnPayOrder($order['log_id']);

        if (empty($payLog)) {
            return false;
        }

        // 查询未支付的订单
        if ($payLog['is_paid'] == 0) {

            // 有主订单调用主订单 查询主订单交易信息
            if (!empty($payLog['main_pay_log'])) {
                $main_pay_log = $payLog['main_pay_log'];

                $data = [
                    'out_trade_no' => $this->make_trade_no($main_pay_log['log_id'], $main_pay_log['order_amount']),
                ];
            } else {
                $data = [
                    'out_trade_no' => $this->make_trade_no($payLog['log_id'], $payLog['order_amount']),
                ];
            }

            $refer = '';
            if (isset($payLog['referer']) && $payLog['referer'] == 'H5') {
                $refer = 'mobile';
            } elseif (file_exists(MOBILE_APP) && isset($payLog['referer']) && $payLog['referer'] == 'app') {
                $refer = 'app';
            }

            try {
                $ret = Query::run(Config::ALI_CHARGE, $this->getConfig($refer), $data);

                if ($ret) {
                    if (isset($ret['response']) && $ret['response']['trade_state'] === Config::TRADE_STATUS_SUCC) {

                        load_helper(['payment']);

                        order_paid($payLog['log_id'], PS_PAYED);
                        return true;
                    }
                }
            } catch (PayException $e) {
                Log::error($e->getMessage());
                return false;
            }
        } elseif ($payLog['is_paid'] == 1) {
            return true;
        }

        return false;
    }

    /**
     * 退款申请接口 （支付宝仅支持3个月内支付订单退款）
     * array(
     *     'order_id' => '1',
     *     'order_sn' => '2017061609464501623',
     *     'return_sn' => '2018112218244971853'
     *     'should_return' => '11',
     *     'pay_id' => '',
     *     'pay_status' => 2
     * )
     *
     * @param array $return_order
     * @param int $order_type
     * @return bool
     */
    public function refund($return_order = [], $order_type = PAY_ORDER)
    {
        // 查询已支付的订单
        $payLog = PaymentService::getPaidOrder($return_order, $order_type);

        if (empty($payLog)) {
            $this->errCode = 422;
            $this->errMsg = trans('admin/order.return_order_no_pay_log');
            return false;
        }

        $pay_status = [
            PS_PAYED,
            PS_PAYED_PART,
            PS_REFOUND_PART
        ];

        // 已付款的订单 才可申请退款
        if ($payLog['is_paid'] == 1 && in_array($return_order['pay_status'], $pay_status)) {
            $refund_fee = !empty($return_order['should_return']) ? $return_order['should_return'] : $payLog['order_amount'];   // 退款金额 默认退全款

            $data = [
                'refund_fee' => $refund_fee,
                'reason' => $return_order['return_brief'] ?? 'refund apply',
                'refund_no' => $return_order['return_sn'],
            ];

            // 有主订单调用主订单 查询主订单交易信息
            if (!empty($payLog['main_pay_log'])) {
                $main_pay_log = $payLog['main_pay_log'];

                // 支付宝交易号 trade_no ， 与 商户订单号 out_trade_no 必须二选一
                if (!empty($main_pay_log['pay_trade_data'])) {
                    $pay_trade_data = json_decode($main_pay_log['pay_trade_data'], true);
                    $trade_no = $pay_trade_data['transaction_id'] ?? '';

                    $data['out_trade_no'] = '';
                    $data['trade_no'] = $trade_no;
                } else {
                    // 商户订单号
                    $out_trade_no = $this->make_trade_no($main_pay_log['log_id'], $main_pay_log['order_amount']);
                    $data['out_trade_no'] = $out_trade_no;
                    $data['trade_no'] = '';
                }

            } else {
                // 支付宝交易号 trade_no ， 与 商户订单号 out_trade_no 必须二选一
                if (!empty($payLog['pay_trade_data'])) {
                    $pay_trade_data = json_decode($payLog['pay_trade_data'], true);
                    $trade_no = $pay_trade_data['transaction_id'] ?? '';

                    $data['out_trade_no'] = '';
                    $data['trade_no'] = $trade_no;
                } else {
                    // 商户订单号
                    $out_trade_no = $this->make_trade_no($payLog['log_id'], $payLog['order_amount']);
                    $data['out_trade_no'] = $out_trade_no;
                    $data['trade_no'] = '';
                }
            }

            $refer = '';
            if (isset($payLog['referer']) && $payLog['referer'] == 'H5') {
                $refer = 'mobile';
            } elseif (isset($payLog['referer']) && $payLog['referer'] == 'app') {
                $refer = 'app';
            }

            try {
                $ret = Refund::run(Config::ALI_REFUND, $this->getConfig($refer), $data);

                if ($ret) {
                    // 退款成功
                    if (isset($ret['is_success']) && $ret['is_success'] == 'T' && isset($ret['response']['refund_fee']) && isset($ret['response']['refund_time'])) {
                        // 记录退款交易信息
                        PaymentService::updateReturnLog($return_order['return_sn'], '', $ret);
                        return true;
                    } else {
                        $this->errCode = 422;
                        $this->errMsg = $ret['error'] ?? '';
                        return false;
                    }
                }
            } catch (PayException $e) {
                Log::error($e->getMessage());
                $this->errCode = $e->getCode();
                $this->errMsg = $e->getMessage();
                return false;
            }
        }

        $this->errCode = 422;
        $this->errMsg = trans('admin/order.return_order_no_pay');
        return false;
    }

    /**
     * 查询退款接口
     * @param $return_order
     * @return bool
     */
    public function refundQuery($return_order = [])
    {
        if (empty($return_order)) {
            return false;
        }

        // 查询退换货表已申请、未退款的退换货订单
        $order_return_info = OrderReturn::select('return_sn', 'order_sn', 'return_status', 'refound_status', 'agree_apply')
            ->where(['return_sn' => $return_order['return_sn']])
            ->where('refound_status', '<>', 1)
            ->where(function ($query) {
                $query->whereIn('return_type', [1, 3]);
            })
            ->first();
        $order_return_info = $order_return_info ? $order_return_info->toArray() : [];

        if ($order_return_info && $order_return_info['agree_apply'] == 1) {
            $payLog = PaymentService::getPaidOrder($return_order);

            // 有主订单调用主订单 查询主订单交易信息
            if (!empty($payLog['main_pay_log'])) {
                $main_pay_log = $payLog['main_pay_log'];

                // 商户订单号
                $out_trade_no = $this->make_trade_no($main_pay_log['log_id'], $main_pay_log['order_amount']);
            } else {
                // 商户订单号
                $out_trade_no = $this->make_trade_no($payLog['log_id'], $payLog['order_amount']);
            }

            $data = [
                'out_trade_no' => $out_trade_no,
                'trade_no' => '', // 支付宝交易号， 与 out_trade_no 必须二选一
                'refund_no' => $order_return_info['return_sn'],
            ];

            $refer = '';
            if (isset($payLog['referer']) && $payLog['referer'] == 'H5') {
                $refer = 'mobile';
            } elseif (file_exists(MOBILE_APP) && isset($payLog['referer']) && $payLog['referer'] == 'app') {
                $refer = 'app';
            }

            try {
                $ret = Query::run(Config::ALI_REFUND, $this->getConfig($refer), $data);

                if ($ret) {
                    // 退款成功
                    if (isset($ret['is_success']) && $ret['is_success'] == 'T' && isset($ret['response']['refund_fee']) && isset($ret['response']['refund_time'])) {

                        // 记录退款交易信息
                        PaymentService::updateReturnLog($return_order['return_sn'], '', $ret);

                        return true;
                    }
                }
                return false;
            } catch (PayException $e) {
                Log::error($e->errorMessage());
                return false;
            }
        }
        return false;
    }

    /**
     * 获取配置参数
     * @param string $refer
     * @param array $pay_config
     * @return array
     */
    protected static function getConfig($refer = '', $pay_config = [])
    {
        if (empty($pay_config)) {
            $pay_config = PaymentRepository::getPaymentConfig('alipay');
        }

        if (empty($pay_config)) {
            return [];
        }

        $use_sandbox = isset($pay_config['use_sandbox']) ? (bool)$pay_config['use_sandbox'] : false;

        $config = [
            'use_sandbox' => $use_sandbox,
            'partner' => $pay_config['alipay_partner'] ?? '',
            'app_id' => $pay_config['app_id'] ?? '',
            'sign_type' => $pay_config['sign_type'] ?? 'RSA2',
            // 可以填写文件路径，或者密钥字符串  当前字符串是 rsa2 的支付宝公钥(开放平台获取)
            'ali_public_key' => $pay_config['ali_public_key'] ?? '',
            // 可以填写文件路径，或者密钥字符串  我的沙箱模式，rsa与rsa2的私钥相同，为了方便测试
            'rsa_private_key' => $pay_config['rsa_private_key'] ?? '',
            'notify_url' => route('notify') . '/alipay', // 支付异步通知地址
            'return_url' => url('respond.php?code=alipay'), // 支付同步通知地址
            'return_raw' => false,
        ];
        // 手机通知地址
        if ($refer == 'mobile' || $refer == 'app') {
            $config['notify_url'] = route('api.payment.notify') . '/alipay'; // 支付异步通知地址
            $config['return_url'] = route('mobile.pay.respond', ['code' => 'alipay']); // 支付同步通知地址
        }

        return $config;
    }

    /**
     * 生成支付订单号
     * @param int $log_id
     * @param int $order_amount
     * @return string
     */
    public static function make_trade_no($log_id = 0, $order_amount = 0)
    {
        $trade_no = '6';
        $trade_no .= str_pad($log_id, 15, 0, STR_PAD_LEFT);
        $trade_no .= str_pad($order_amount * 100, 16, 0, STR_PAD_LEFT);

        return $trade_no;
    }

    /**
     * 获取log_id
     * @param string $trade_no
     * @return int
     */
    public static function parse_trade_no($trade_no = '')
    {
        $log_id = substr($trade_no, 1, 15);

        return intval($log_id);
    }
}
