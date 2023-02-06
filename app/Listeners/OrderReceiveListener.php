<?php

namespace App\Listeners;

use App\Events\OrderReceiveEvent;
use App\Models\UserOrderNum;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Order\OrderRepository;
use App\Services\Commission\CommissionService;
use App\Services\Order\OrderCommonService;
use App\Services\Order\OrderRefoundService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Class OrderReceiveListener
 * @package App\Listeners
 */
class OrderReceiveListener implements ShouldQueue
{
    use InteractsWithQueue;


    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param OrderReceiveEvent $event
     * @return bool|mixed
     */
    public function handle(OrderReceiveEvent $event)
    {
        $order = $event->order ?? [];
        $order_id = $order['order_id'] ?? 0;

        if (empty($order) || empty($order_id)) {
            return false;
        }

        $user_id = $order['user_id'] ?? 0;

        // 扩展参数
        $extendParam = $event->extendParam ?? [];

        // 配送状态
        $shipping_status = $extendParam['shipping_status'] ?? 0;

        $confirm_take_time = TimeRepository::getGmTime();

        /* 记录日志 */
        $note = $extendParam['note'] ?? trans('user.received'); // 记录备注
        $name = $extendParam['action_name'] ?? trans('common.buyer'); // 触发者
        OrderRepository::order_action($order['order_id'], $order['order_status'], $shipping_status, $order['pay_status'], $note, $name, 0, $confirm_take_time);

        /* 更新主订单付款状态 */
        app(OrderCommonService::class)->updateMainOrder($order, 4, $note, $name);

        if ($shipping_status == SS_RECEIVED && $order['is_zc_order'] == 0 && $order['main_count'] == 0) {
            // 更新订单统计
            Artisan::call('app:user:order', ['user_id' => $user_id]);
        }

        if ($order['main_count'] == 0) {
            $seller_id = $order['ru_id'];

            $value_card = DB::table('value_card_record')
                ->where('order_id', $order_id)
                ->where('add_val', 0)
                ->value('use_val');
            $value_card = $value_card ? $value_card : 0;

            if (empty($order['get_seller_negative_order'])) {
                $return_amount_info = OrderRefoundService::orderReturnAmount($order_id);
            } else {
                $return_amount_info['return_amount'] = 0;
                $return_amount_info['return_rate_price'] = 0;
                $return_amount_info['ret_id'] = [];
            }

            if ($order['order_amount'] > 0 && $order['order_amount'] > $order['rate_fee']) {
                $order_amount = $order['order_amount'] - $order['rate_fee'];
            } else {
                $order_amount = $order['order_amount'];
            }

            $other = [
                'user_id' => $order['user_id'],
                'seller_id' => $seller_id,
                'order_id' => $order['order_id'],
                'order_sn' => $order['order_sn'],
                'order_status' => $order['order_status'],
                'shipping_status' => $shipping_status,
                'pay_status' => $order['pay_status'],
                'order_amount' => $order_amount,
                'return_amount' => $return_amount_info['return_amount'],
                'goods_amount' => $order['goods_amount'],
                'tax' => $order['tax'],
                'shipping_fee' => $order['shipping_fee'],
                'insure_fee' => $order['insure_fee'],
                'pay_fee' => $order['pay_fee'],
                'pack_fee' => $order['pack_fee'],
                'card_fee' => $order['card_fee'],
                'bonus' => $order['bonus'],
                'integral_money' => $order['integral_money'],
                'coupons' => $order['coupons'],
                'discount' => $order['discount'],
                'dis_amount' => $order['dis_amount'],
                'vc_dis_money' => $order['vc_dis_money'],
                'value_card' => $value_card,
                'money_paid' => $order['money_paid'],
                'surplus' => $order['surplus'],
                'rate_fee' => $order['rate_fee'] ?? 0,
                'return_rate_fee' => $return_amount_info['return_rate_price'] ?? 0,
                'divide_channel' => $order['divide_channel'] ?? 0,
            ];

            if ($shipping_status == SS_RECEIVED) {
                $other['confirm_take_time'] = $confirm_take_time;
            }

            if ($seller_id > 0 && $order['main_count'] == 0 && ($order['pay_status'] == PS_PAYED || $order['pay_status'] == PS_REFOUND_PART)) {
                app(CommissionService::class)->getOrderBillLog($other);
                app(CommissionService::class)->setBillOrderReturn($return_amount_info['ret_id'], $other['order_id']);
            }

            // 确认收货 购买成为分销商商品 绑定权益卡
            if (file_exists(MOBILE_DRP) && $order['pay_status'] == PS_PAYED) {
                app(\App\Modules\Drp\Services\Drp\DrpService::class)->buyGoodsUpdateDrpShop($order);
            }
        }
    }
}
