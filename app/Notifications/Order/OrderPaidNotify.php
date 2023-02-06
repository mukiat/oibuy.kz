<?php

namespace App\Notifications\Order;

use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\Log;
use Payment\Config;
use Payment\Notify\PayNotifyInterface;

/**
 * 支付异步通知
 *
 * 客户端需要继承该接口，并实现这个方法，在其中实现对应的业务逻辑
 * Class OrderPaidNotify
 */
class OrderPaidNotify implements PayNotifyInterface
{
    /**
     * @param array $data
     * @return bool
     */
    public function notifyProcess(array $data)
    {
        // 初始值
        $log_id = 0;
        $order_sn = '';
        $pay_code = '';
        $paymentAmount = 0;

        $channel = $data['channel'];
        if ($channel === Config::ALI_CHARGE) {
            $pay_code = 'alipay';
            // 支付宝支付 alipay 退款的异步通知是依据支付接口的触发条件来触发的，异步通知也是发送到支付接口传入的异步地址上

            $order_sn = $data['subject'];

            $log_id = \App\Plugins\Payment\Alipay\Alipay::parse_trade_no($data['order_no']); // 商户订单号 out_trade_no
            $paymentAmount = $data['amount']; //交易金额

            $refund_fee = $data['refund_fee'] ?? 0; // 退款金额
            $trade_refund_time = $data['trade_refund_time'] ?? 0; // 交易退款时间
            /**
             * 改变退换货订单状态
             * 这里如果退款触发了异步信息，退款的异步信息中会有 refund_fee 退款总金额参数，如果有这个参数就可以确定这一笔退款成功了
             */
            if ($refund_fee && $trade_refund_time) {
                // 记录退款交易信息
                PaymentService::updateReturnLog('', $order_sn, $data);
                return true;
            }
        } elseif ($channel === Config::WX_CHARGE) {
            // 微信支付
            $pay_code = 'wxpay';

            $order_sn = $data['order_no'];
        }

        // 记录交易信息
        PaymentService::updatePayLog($log_id, $data);

        // 优化切换支付方式后支付 更新订单支付方式
        PaymentService::updateOrderPayment($order_sn, $pay_code);

        /**
         * 改变订单状态
         */
        load_helper('payment');
        $rs = order_paid($log_id, PS_PAYED, '', '', $paymentAmount);

        if (isset($rs['status']) && $rs['status'] === 'error') {
            Log::error($rs['message'] ?? 'notify error', $data);
            return false;
        } else {
            return true;
        }
    }
}
