<?php

namespace App\Services\Order;

class OrderStatusService
{
    /**
     * 订单状态
     *
     * @param $status
     * @return mixed
     * @throws \Exception
     */
    public static function orderStatus($status = 0)
    {
        $array = [
            OS_UNCONFIRMED => lang('order.os.' . OS_UNCONFIRMED),
            OS_CONFIRMED => lang('order.os.' . OS_CONFIRMED),
            OS_CANCELED => lang('order.os.' . OS_CANCELED),
            OS_INVALID => lang('order.os.' . OS_INVALID),
            OS_RETURNED => lang('order.os.' . OS_RETURNED),
            OS_SPLITED => lang('order.os.' . OS_SPLITED),
            OS_SPLITING_PART => lang('order.os.' . OS_SPLITING_PART),
            OS_RETURNED_PART => lang('order.os.' . OS_RETURNED_PART),
            OS_ONLY_REFOUND => lang('order.os.' . OS_ONLY_REFOUND)
        ];
        return $array[$status];
    }

    /**
     * 支付状态
     *
     * @param $status
     * @return mixed
     * @throws \Exception
     */
    public static function payStatus($status = 0)
    {
        $array = [
            PS_UNPAYED => lang('order.ps.' . PS_UNPAYED),
            PS_PAYING => lang('order.ps.' . PS_PAYING),
            PS_PAYED => lang('order.ps.' . PS_PAYED),
            PS_PAYED_PART => lang('order.ps.' . PS_PAYED_PART),
            PS_REFOUND => lang('order.ps.' . PS_REFOUND),
            PS_REFOUND_PART => lang('order.ps.' . PS_REFOUND_PART),
            PS_MAIN_PAYED_PART => lang('order.ps.' . PS_MAIN_PAYED_PART)
        ];

        return $array[$status];
    }

    /**
     * 配送状态
     *
     * @param int $status
     * @return mixed
     * @throws \Exception
     */
    public static function shipStatus($status = 0)
    {
        $array = [
            SS_UNSHIPPED => lang('order.ss.' . SS_UNSHIPPED),
            SS_SHIPPED => lang('order.ss.' . SS_SHIPPED),
            SS_RECEIVED => lang('order.ss.' . SS_RECEIVED),
            SS_PREPARING => lang('order.ss.' . SS_PREPARING),
            SS_SHIPPED_PART => lang('order.ss.' . SS_SHIPPED_PART),
            SS_SHIPPED_ING => lang('order.ss.' . SS_SHIPPED_ING),
            OS_SHIPPED_PART => lang('order.ss.' . OS_SHIPPED_PART),
            SS_PART_RECEIVED => lang('order.ss.' . SS_PART_RECEIVED)
        ];

        return $array[$status];
    }

    /**
     * 是否显示确认收货 handler_receive 0 不显示 1 显示
     * @param array $order
     * @return int
     */
    public static function can_receive($order = [])
    {
        if (empty($order)) {
            return 0;
        }

        $handler_receive = 0;
        // 订单 已确认或已分单 已付款 已发货 => 可收货
        // 订单 已确认或已分单 已付款 已发货（部分商品）=> 可收货
        if (in_array($order['order_status'], [OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART]) && $order['pay_status'] == PS_PAYED && in_array($order['shipping_status'], [SS_SHIPPED, SS_SHIPPED_PART])) {
            $handler_receive = 1;
        } elseif (in_array($order['order_status'], [OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART, OS_RETURNED_PART, OS_ONLY_REFOUND]) && in_array($order['shipping_status'], [SS_SHIPPED, SS_SHIPPED_PART])) {
            // 订单 已确认或已分单或部分退款、仅退款 未付款或已付款  已发货或部分已发货（部分商品）
            $handler_receive = 1;
        }

        // 订单 已确认或已分单 已付款 部分已发货，部分已收货 => 可收货
        if (in_array($order['order_status'], [OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART]) && $order['pay_status'] == PS_PAYED && in_array($order['shipping_status'], [SS_SHIPPED_PART, SS_PART_RECEIVED])) {
            $handler_receive = 1;
        }

        return $handler_receive;
    }

    /**
     * 是否显示取消订单 handler_cancel 0 不显示 1 显示
     * @param array $order
     * @return int
     */
    public static function can_cancel($order = [])
    {
        if (empty($order)) {
            return 0;
        }

        $handler_cancel = 0;
        if (in_array($order['order_status'], [OS_UNCONFIRMED, OS_CONFIRMED]) && $order['pay_status'] == PS_UNPAYED && $order['shipping_status'] == SS_UNSHIPPED) {
            // 已确认或未确认 未付款 未发货 => 可取消
            $handler_cancel = 1;
        }
        // 回收站不显示 取消订单
        if ($order['is_delete'] == 1) {
            $handler_cancel = 0;
        }

        return $handler_cancel;
    }

    /**
     * 是否显示删除订单 handler_delete： 0 不可删除 1 可删除
     *
     * @param array $order
     * @return int
     */
    public static function can_delete($order = [])
    {
        if (empty($order)) {
            return 0;
        }

        $handler_delete = 0;
        if (in_array($order['order_status'], [OS_CONFIRMED, OS_SPLITED]) && $order['pay_status'] == PS_PAYED && $order['shipping_status'] == SS_RECEIVED) {
            // 已确认或已分单 已付款 已收货  => 可删除
            $handler_delete = 1;
        } elseif (in_array($order['order_status'], [OS_CANCELED, OS_INVALID])) {
            // 已取消或无效 => 可删除
            $handler_delete = 1;
        }

        return $handler_delete;
    }

    /**
     * 订单是否可以评价 handler_comment : 0 不可以 1 可以评价
     * @param array $order
     * @param int $shop_can_comment 自营店铺是否开启评价
     * @return int
     */
    public static function can_comment($order = [], $shop_can_comment = 0)
    {
        if (empty($order)) {
            return 0;
        }

        $handler_comment = 0;

        // 是否开启整站评论
        $can_comment = (int)config('shop.shop_can_comment', 0);
        if (empty($can_comment)) {
            return 0;
        }

        // 自营店铺是否开启评价
        if (empty($shop_can_comment)) {
            return 0;
        }

        if (in_array($order['order_status'], [OS_CONFIRMED, OS_SPLITED]) && $order['pay_status'] == PS_PAYED && $order['shipping_status'] == SS_RECEIVED && $order['main_count'] == 0 && !empty($order['confirm_take_time'])) {
            // 已确认或已分单 已付款 已收货  => 可评价
            $handler_comment = 1;
        }

        if ($handler_comment == 1 && $order['extension_code'] != 'package_buy') {
            $handler_comment = 1;
        }

        return $handler_comment;
    }

    /**
     * 订单是否可以退换货申请 handler_return : 0 不可以 1 可以
     * @param array $order
     * @return int
     */
    public static function can_return($order = [])
    {
        if (empty($order)) {
            return 0;
        }

        $handler_return = 0;

        // 已确认或已分单，已付款，已收货 显示申请售后
        if (($order['order_status'] == OS_SPLITED || $order['order_status'] == OS_CONFIRMED) && $order['shipping_status'] == SS_RECEIVED && $order['pay_status'] == PS_PAYED) {
            $handler_return = 1;
        }
        // 已确认或已分单 已收货 未支付 （货到付款）可退货 显示申请售后
        if (($order['order_status'] == OS_CONFIRMED || $order['order_status'] == OS_SPLITED) && $order['shipping_status'] == SS_RECEIVED && $order['pay_status'] == PS_UNPAYED) {
            $handler_return = 1;
        }
        // 已付款 未发货 => 可退款 显示申请售后
        if ($order['shipping_status'] == SS_UNSHIPPED && $order['pay_status'] == PS_PAYED) {
            $handler_return = 1;
        }
        // 部分退货、部分退款 显示申请售后
        if ($order['order_status'] == OS_RETURNED_PART || $order['pay_status'] == PS_REFOUND_PART || $order['shipping_status'] == OS_RETURNED_PART) {
            $handler_return = 1;
        }

        // 回收站订单 不显示申请售后
        if ($order['is_delete']) {
            $handler_return = 0;
        }

        return $handler_return;
    }

    /**
     * 退换货订单是否可取消申请 refound_cancel 0 不可取消 1 可取消
     * @param array $order_return
     * @return int
     */
    public static function refound_cancel($order_return = [])
    {
        if (empty($order_return)) {
            return 0;
        }

        $refound_cancel = 0;

        // return_status 退换货状态 仅退款 或已申请（由买家寄回） 且 refound_status 退款状态 未退款 => 可取消
        if (($order_return['return_status'] == 0 || $order_return['return_status'] == -1) && $order_return['refound_status'] == 0) {
            $refound_cancel = 1;
        }

        return $refound_cancel;
    }
}
