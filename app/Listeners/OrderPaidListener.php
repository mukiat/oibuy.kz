<?php

namespace App\Listeners;

use App\Events\OrderPaidEvent;
use App\Repositories\Order\OrderRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Class OrderPaidListener
 * @package App\Listeners
 */
class OrderPaidListener implements ShouldQueue
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
     * @param OrderPaidEvent $event
     * @return bool|mixed
     */
    public function handle(OrderPaidEvent $event)
    {
        $order = $event->order ?? [];
        $order_id = $order['order_id'] ?? 0;

        if (empty($order) || empty($order_id)) {
            return false;
        }

        // 扩展参数
        $extendParam = $event->extendParam ?? [];

        // 开启异步队列 获取最新缓存配置
        $queue_connection = config('queue.default');
        if ($queue_connection != 'sync' && !empty($extendParam['shop_config'])) {
            config(['shop' => $extendParam['shop_config']]);
        }

        if (config('shop.sales_volume_time') == SALES_PAY) {
            /* 付款更新商品销量 */
            OrderRepository::increment_goods_sale_pay($order_id, $order);
        }

        /**
         * 付款成功创建快照
         */
        $create_snapshot = $extendParam['create_snapshot'] ?? 0;
        if ($create_snapshot == 1) {
            OrderRepository::create_snapshot($order_id);
        }


    }
}
