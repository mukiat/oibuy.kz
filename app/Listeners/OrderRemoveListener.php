<?php

namespace App\Listeners;

use App\Events\OrderRemoveEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class OrderRemoveListener
 * @package App\Listeners
 */
class OrderRemoveListener
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param OrderRemoveEvent $event
     * @return bool|mixed
     */
    public function handle(OrderRemoveEvent $event)
    {
        $order = $event->param ?? [];
        // 扩展参数
        $extendParam = $event->extendParam ?? [];

        $order_id = $order['order_id'] ?? 0;

        if (empty($order) || empty($order_id)) {
            return false;
        }

        // 订单删除
        DB::table('order_goods')->where('order_id', $order_id)->delete();
        DB::table('store_order')->where('order_id', $order_id)->delete();
        DB::table('order_action')->where('order_id', $order_id)->delete();

        DB::table('complaint')->where('order_id', $order_id)->delete();

        // 更新会员订单信息
        $user_id = $order['user_id'] ?? 0;
        Artisan::call('app:user:order', ['user_id' => $user_id]);

        if (config('app.debug')) {
            Log::info('remove_order:' . $order_id);
        }

        return true;
    }
}
