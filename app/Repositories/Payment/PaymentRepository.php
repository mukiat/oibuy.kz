<?php

namespace App\Repositories\Payment;

use App\Models\Payment;
use App\Repositories\Common\BaseRepository;

class PaymentRepository
{

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

        $res = Payment::query();

        if (isset($where['pay_id'])) {
            $res = $res->where('pay_id', $where['pay_id']);
        }

        if (isset($where['pay_name'])) {
            $res = $res->where('pay_name', $where['pay_name']);
        }

        if (isset($where['pay_code'])) {
            $res = $res->where('pay_code', $where['pay_code']);
        }

        if (isset($where['enabled'])) {
            $res = $res->where('enabled', $where['enabled']);
        }

        if (!empty($columns)) {
            $res = $res->select($columns);
        }

        $res = BaseRepository::getToArrayFirst($res);

        return $res;
    }

    /**
     * 计算订单支付手续费
     * @param int $payment_id
     * @param int $order_amount
     * @param int $cod_fee
     * @return float
     */
    public static function order_pay_fee($payment_id = 0, $order_amount = 0, $cod_fee = null)
    {
        if (empty($payment_id)) {
            return 0;
        }

        $where = [
            'pay_id' => $payment_id
        ];
        $payment = self::getPaymentInfo($where, ['is_cod', 'pay_fee', 'pay_code']);

        return self::calculate_pay_fee($payment, $order_amount, $cod_fee);
    }

    /**
     * 计算订单支付手续费
     * @param array $payment
     * @param int $order_amount
     * @param null $cod_fee
     * @return float|int
     */
    public static function calculate_pay_fee($payment = [], $order_amount = 0, $cod_fee = null)
    {
        // 是否启用支付手续费
        $use_pay_fee = config('shop.use_pay_fee') ?? 0;
        if ($use_pay_fee == 0) {
            return 0;
        }

        if (empty($payment) || empty($order_amount)) {
            return 0;
        }

        // 在线支付、余额支付、白条支付 手续费为 0
        if (in_array($payment['pay_code'], ['onlinepay', 'balance', 'chunsejinrong'])) {
            return 0;
        }

        $rate = ($payment['is_cod'] && !is_null($cod_fee)) ? $cod_fee : $payment['pay_fee'];

        if (strpos($rate, '%') !== false) {
            /* 支付费用是一个比例 */
            $val = floatval($rate) / 100;
            $pay_fee = $val > 0 ? $order_amount * $val / (1 - $val) : 0;
        } else {
            $pay_fee = floatval($rate);
        }

        return round($pay_fee, 2);
    }

    /**
     * 子订单支付手续费  按应付金额均摊主订单支付手续费
     * 公式 = (子订单应付金额 / 主订单应付金额) * 主订单支付手续费
     * @param int $child_amount 子订单应付金额
     * @param int $main_amount 主订单应付金额
     * @param int $main_pay_fee 主订单支付手续费
     * @return float|int
     */
    public static function child_order_pay_fee($child_amount = 0, $main_amount = 0, $main_pay_fee = 0)
    {
        if (empty($main_amount) || empty($main_pay_fee)) {
            return 0;
        }

        // 是否启用支付手续费
        $use_pay_fee = config('shop.use_pay_fee') ?? 0;
        if ($use_pay_fee == 0) {
            return 0;
        }

        $pay_fee = ($child_amount / $main_amount) * $main_pay_fee;

        return round($pay_fee, 2);
    }

    /**
     * 处理序列化的支付、配送的配置参数
     * 返回一个以name为索引的数组
     * @param $cfg
     * @return array|bool
     */
    public static function unserialize_config($cfg)
    {
        if (is_string($cfg) && ($arr = unserialize($cfg)) !== false) {
            $config = [];

            foreach ($arr as $key => $val) {
                $config[$val['name']] = $val['value'];
            }

            return $config;
        } else {
            return false;
        }
    }

    /**
     * 支付方式列表 for 筛选
     * @return array
     */
    public static function online_payment_list()
    {
        $model = Payment::where('enabled', 1)->where('is_online', 1)->whereNotIn('pay_code', ['cod', 'balance', 'bank', 'chunsejinrong'])->select('pay_id', 'pay_name')->get();

        return $model ? $model->toArray() : [];
    }

    /**
     * 获取支付方式配置信息
     *
     * 原 payment.php get_payment 方法
     *
     * @param string $value 支付方式code/支付方式ID
     * @param string $field
     * @return array
     */
    public static function getPaymentConfig($value = '', $field = 'pay_code')
    {
        if (empty($value) || empty($field)) {
            return [];
        }

        $where = [
            'enabled' => 1
        ];

        if ($field == 'pay_id') {
            $where['pay_id'] = $value;
        }

        if ($field == 'pay_code') {
            $where['pay_code'] = $value;
        }

        $payment = self::getPaymentInfo($where);

        if (!empty($payment)) {
            $config_list = $payment['pay_config'] ? unserialize($payment['pay_config']) : [];
            if ($config_list) {
                foreach ($config_list as $config) {
                    $payment[$config['name']] = $config['value'];
                }
                unset($payment['pay_config']);
            }
        }

        return $payment;
    }
}
