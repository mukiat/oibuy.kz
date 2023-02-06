<?php

namespace App\Listeners;

use App\Events\OrderDoneEvent;
use App\Libraries\Template;
use App\Models\MailTemplates;
use App\Models\OrderInfo;
use App\Models\SellerShopinfo;
use App\Repositories\Activity\PresaleRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Order\OrderRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Class OrderDoneListener
 * @package App\Listeners
 */
class OrderDoneListener implements ShouldQueue
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
     * @param OrderDoneEvent $event
     * @return bool|mixed
     */
    public function handle(OrderDoneEvent $event)
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

        if (isset($order['extension_code']) && $order['extension_code'] == 'presale') {
            // 付款成功后增加预售人数
            PresaleRepository::increment_presale_num($order_id);
        }

        // 下单给商家发邮件
        if (config('shop.send_service_email', 0) == 1) {

            $send_list = [];
            if ($order['main_order_id'] == 0) {
                // 是否有子订单
                $child_order = OrderInfo::query()->where('main_order_id', $order['order_id'])->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED]);
                $child_order = $child_order->select('order_id', 'order_sn', 'ru_id')->get();
                $child_order = $child_order ? $child_order->toArray() : [];
                if (!empty($child_order)) {
                    // 分单 获取子订单 商家邮箱地址
                    foreach ($child_order as $k => $child) {
                        if (isset($child['ru_id']) && $child['ru_id'] > 0) {
                            $seller_email = SellerShopinfo::where('ru_id', $child['ru_id'])->value('seller_email');
                            $seller_email = $seller_email ?? '';
                        } else {
                            $seller_email = config('shop.service_email', '');
                        }

                        $send_list[$k] = [
                            'order_id' => $child['order_id'],
                            'email' => $seller_email,
                        ];
                    }
                } else {
                    // 无分单
                    if (isset($order['ru_id']) && $order['ru_id'] > 0) {
                        // 商家
                        $seller_email = SellerShopinfo::where('ru_id', $order['ru_id'])->value('seller_email');
                        $seller_email = $seller_email ?? '';
                    } else {
                        // 平台
                        $seller_email = config('shop.service_email', '');
                    }

                    $send_list[0] = [
                        'order_id' => $order['order_id'],
                        'email' => $seller_email,
                    ];
                }
            }

            if (!empty($send_list)) {
                // 邮件模板内容
                $template = MailTemplates::where('template_code', 'remind_of_new_order')->select('is_html', 'template_subject', 'template_content')->first();
                $template = $template ? $template->toArray() : [];

                if (empty($template)) {
                    return false;
                }

                $tpl = new Template();

                foreach ($send_list as $item) {
                    $order_id = $item['order_id'] ?? 0;
                    $email = $item['email'] ?? '';

                    if ($order_id && $email) {
                        $order = OrderInfo::where('order_id', $order_id)->with([
                            'goods'
                        ])->first();

                        $order = $order ? $order->toArray() : [];
                        $goods_list = $order['goods'] ?? [];

                        $tpl->assign('order', $order);
                        $tpl->assign('goods_list', $goods_list);
                        $tpl->assign('shop_name', config('shop.shop_name'));
                        $tpl->assign('send_date', TimeRepository::getLocalDate(config('shop.time_format'), TimeRepository::getGmTime()));

                        $template['template_content'] = $tpl->fetch('str:' . $template['template_content']);

                        CommonRepository::sendEmail('', $email, $template['template_subject'], $template['template_content'], $template['is_html']);
                    }
                }
            }
        }

    }
}
