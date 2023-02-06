<?php

namespace App\Jobs;

use App\Models\OrderInfo;
use App\Repositories\Order\OrderRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessMcOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $params;
    protected $extendParam;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params = [], $extendParam = [])
    {
        $this->params = $params;
        $this->extendParam = $extendParam;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        $order_id_list = $this->params ?? [];

        if (empty($order_id_list)) {
            return false;
        }

        // 扩展参数
        $extendParam = $this->extendParam ?? [];
        $admin_id = $extendParam['admin_id'] ?? 0;

        // 开启异步队列 获取最新缓存配置
        $queue_connection = config('queue.default');
        if ($queue_connection != 'sync' && !empty($extendParam['shop_config'])) {
            config(['shop' => $extendParam['shop_config']]);
        }

        // 已支付 订单
        $model = OrderInfo::query()->where('main_count', 0)
            ->whereIn('order_id', $order_id_list)
            ->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED])
            ->where('pay_status', PS_PAYED)
            ->select('order_id', 'order_status', 'pay_status', 'shipping_status', 'is_update_sale', 'extension_code');

        $model->chunkById(10, function ($order_list) use ($admin_id) {
            foreach ($order_list as $order) {
                $order = $order ? collect($order)->toArray() : [];
                if (!empty($order)) {
                    Log::info('order', $order);
                    $order_id = $order['order_id'] ?? 0;

                    // 订单状态 已确认已分单、已付款 未发货或收货确认
                    if (in_array($order['order_status'], [OS_CONFIRMED, OS_SPLITED]) && $order['pay_status'] == PS_PAYED && in_array($order['shipping_status'], [SS_UNSHIPPED, SS_RECEIVED])) {
                        if ($order['shipping_status'] == SS_UNSHIPPED && config('shop.sales_volume_time') == SALES_PAY) {
                            /* 付款更新商品销量 */
                            Log::info('sales_volume_time 付款更新商品销量' . '，订单id:' . $order_id);
                            OrderRepository::increment_goods_sale_pay($order_id, $order);
                        }
                        // 收货确认
                        if ($order['shipping_status'] == SS_RECEIVED) {
                            if (config('shop.sales_volume_time') == SALES_PAY) {
                                /* 付款更新商品销量 */
                                Log::info('sales_volume_time 付款更新商品销量' . '，订单id:' . $order_id);
                                OrderRepository::increment_goods_sale_pay($order_id, $order);
                            }
                            if (config('shop.sales_volume_time') == SALES_SHIP) {
                                /* 发货更新商品销量 */
                                Log::info('sales_volume_time 发货更新商品销量' . '，订单id:' . $order_id);
                                self::increment_goods_sale_ship($order_id, $order);
                            }
                        }
                    }

                    // 订单状态 已确认已分单、已付款、已发货
                    if (in_array($order['order_status'], [OS_CONFIRMED, OS_SPLITED]) && $order['pay_status'] == PS_PAYED && $order['shipping_status'] == SS_SHIPPED) {
                        if (config('shop.sales_volume_time') == SALES_PAY) {
                            /* 付款更新商品销量 */
                            Log::info('sales_volume_time 付款更新商品销量' . '，订单id:' . $order_id);
                            OrderRepository::increment_goods_sale_pay($order_id, $order);
                        }
                        if (config('shop.sales_volume_time') == SALES_SHIP) {
                            /* 发货更新商品销量 */
                            Log::info('sales_volume_time 发货更新商品销量' . '，订单id:' . $order_id);
                            self::increment_goods_sale_ship($order_id, $order);
                        }
                    }

                }
            }

        });

        return true;
    }

    /**
     * 发货更新商品销量(兼容收货确认，仅用于后台添加订单流程)
     *
     * @param int $order_id
     * @param array $order
     * @return bool|int
     */
    public static function increment_goods_sale_ship($order_id = 0, $order = [])
    {
        // 增加销量时机 - 发货
        if (empty($order_id)) {
            return false;
        }

        if (empty($order)) {
            $order = DB::table('order_info')->select('order_id', 'shipping_status', 'is_update_sale')->where('order_id', $order_id)->first();
            $order = collect($order)->toArray() ?? [];
        }

        if (!empty($order)) {
            $is_update_sale = $order['is_update_sale'] ?? 0; // 订单是否已更新销量
            if (in_array($order['shipping_status'], [SS_SHIPPED, SS_RECEIVED]) && $is_update_sale == 0) {
                // 订单已发货或确认收货  修改订单销量更新状态 已更新
                $up = DB::table('order_info')->where('order_id', $order_id)->where('is_update_sale', 0)->update(['is_update_sale' => 1]);
                if ($up) {
                    $goodsList = DB::table('order_goods')->select('goods_id', 'goods_number', 'send_number')->where('order_id', $order_id)->get();
                    if ($goodsList) {
                        foreach ($goodsList as $goods) {
                            $sales_volume = empty($goods->send_number) ? $goods->goods_number : $goods->send_number;
                            DB::table('goods')->where('goods_id', $goods->goods_id)->increment('sales_volume', $sales_volume);

                            if ($order['extension_code'] == 'exchange_goods') {
                                DB::table('exchange_goods')->where('goods_id', $goods->goods_id)->increment('sales_volume', $goods->goods_number);
                            }
                        }
                    }
                }

                return $up;
            }
        }

        return false;
    }
}
