<?php

namespace App\Services\CrowdFund;


use App\Repositories\Common\TimeRepository;
use App\Services\Order\OrderRefoundService;
use App\Services\User\AccountService;
use Illuminate\Support\Facades\DB;

/**
 * Class CrowdRefoundService
 * @package App\Services\CrowdFund
 */
class CrowdRefoundService
{

    /**
     * 众筹项目失败订单退款
     * @param array $order
     * @param array $zc_project
     * @return bool
     */
    public static function zcRefund($order = [], $zc_project = [])
    {
        if (empty($order) || empty($zc_project)) {
            return false;
        }

        // 1. 判断订单状态 已确认 已付款 未发货 可退款
        if (!(in_array($order['order_status'], [OS_CONFIRMED, OS_SPLITED]) && $order['pay_status'] == PS_PAYED && $order['shipping_status'] == SS_UNSHIPPED && $order['main_count'] == 0)) {
            return false;
        }

        // 2. 判断众筹是否失败：当前时间大于结束时间且参与金额小于众筹总金额
        $time = TimeRepository::getGmTime();

        $zc_result = 0;
        if (!empty($zc_project['end_time']) && $time > $zc_project['end_time'] && $zc_project['amount'] > $zc_project['join_money']) {
            $zc_result = 2; // 众筹失败
        }

        if (empty($zc_result) || $zc_result != 2) {
            return false;
        }

        // 开始退款
        $is_paid = false;
        $surplus_is_paid = false;

        $update_order['order_amount'] = $order['order_amount'] ?? 0;

        // - 订单如果使用了余额 退余额
        $surplus = $order['surplus'] ?? 0;
        if ($surplus > 0) {
            $update_order['surplus'] = 0;
            $money_paid = $order['money_paid'] ?? 0;
            $update_order['money_paid'] = ($money_paid > 0 && $money_paid >= $surplus) ? $money_paid - $surplus : 0;
            $update_order['order_amount'] = $update_order['order_amount'] + $surplus;
            // 退款到账户余额 并记录会员账目明细
            $change_desc = trans('crowdfunding.zc_order_fail_refound') . $order['order_sn'] . '，' . trans('crowdfunding.zc_money') . '：' . $surplus;
            $surplus_is_paid = AccountService::logAccountChange($order['user_id'], $surplus, 0, 0, 0, $change_desc);
        }

        // - 订单在线支付部分 原路退款
        $money_paid = $order['money_paid'] ?? 0;
        if ($money_paid > 0) {
            // 原路退款
            $refundOrder = [
                'order_id' => $order['order_id'],
                'pay_id' => $order['pay_id'],
                'pay_status' => $order['pay_status'],
                'referer' => $order['referer'],
                'return_sn' => $order['order_sn']
            ];
            $is_paid = OrderRefoundService::refoundPay($refundOrder, $money_paid);
        }

        if ($surplus_is_paid == true || $is_paid == true) {

            // - 订单在线支付部分 原路退款
            if ($money_paid > 0) {
                $update_order['money_paid'] = 0;
                $update_order['order_amount'] = $update_order['order_amount'] + $money_paid;
            }

            // - 订单使用了储值卡 退储值卡
            $use_val = OrderRefoundService::returnValueCardMoney($order['order_id']);
            if ($use_val > 0) {
                $update_order['order_amount'] = $update_order['order_amount'] + $use_val;
            }

            //记录订单操作记录
            $action_note = trans('crowdfunding.zc_order_fail_refound');

            // 修改订单状态为已取消，付款状态为未付款
            $update_order['order_status'] = OS_CANCELED;
            $update_order['to_buyer'] = trans('crowdfunding.cancel_order_reason'); // 订单留言给用户 众筹项目失败
            $update_order['pay_status'] = PS_REFOUND;
            $update_order['pay_time'] = 0;
            $update_order['shipping_status'] = $order['shipping_status'];

            /* 更新订单信息 */
            DB::table('order_info')->where('order_id', $order['order_id'])->update($update_order);

            // 操作日志
            self::orderActionChange($order['order_id'], 'admin', $update_order, $action_note);

            return true;
        }

        return false;
    }


    /**
     * 记录修改订单状态
     * @param int $order_id 订单id
     * @param string $action_user 操作人员
     * @param array $order 订单信息
     * @param string $action_note 变动说明
     * @return bool
     */
    public static function orderActionChange($order_id = 0, $action_user = 'admin', $order = [], $action_note = '')
    {
        if (empty($order_id) || empty($order)) {
            return false;
        }

        $time = TimeRepository::getGmTime();

        $action_log = [
            'order_id' => $order_id,
            'action_user' => $action_user,
            'order_status' => $order['order_status'],
            'shipping_status' => $order['shipping_status'],
            'pay_status' => $order['pay_status'],
            'action_note' => $action_note,
            'log_time' => $time
        ];

        return DB::table('order_action')->insertGetId($action_log);
    }
}