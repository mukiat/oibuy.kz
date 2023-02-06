<?php

namespace App\Console\Commands;

use App\Models\OrderGoods;
use App\Repositories\Common\TimeRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * 订单自动评价
 * Class OrderEvaluation
 * @package App\Console\Commands
 */
class OrderEvaluation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:order:evaluation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Order automatic evaluation';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 是否开启整站评论
        $shop_can_comment = (int)config('shop.shop_can_comment', 0);
        if (empty($shop_can_comment)) {
            return false;
        }

        // 是否开启自动评价
        $auto_evaluate = (int)config('shop.auto_evaluate', 0);
        if (empty($auto_evaluate)) {
            return false;
        }

        // 自动评价时间、内容
        $auto_evaluate_time = (int)config('shop.auto_evaluate_time', 60);
        $auto_evaluate_content = e(config('shop.auto_evaluate_content', ''));

        // 评论是否需要审核 默认通过审核
        $comment_check = 1;

        /**
         * 当前时间大于等于未评价订单确认收货时间+自动评价时间时，系统自动生成商品评价，默认时间为60天
         */

        // 自动评价 时间公式： confirm_take_time + auto_evaluate_time <= now
        $now = TimeRepository::getGmTime();
        $days = $now - $auto_evaluate_time * 24 * 60 * 60;

        // 未评价订单商品
        OrderGoods::where('order_id', '>', 0)
            ->where('main_count', 0)
            ->where('is_comment', 0)
            ->where('is_received', 0)
            ->whereHasIn('getOrder', function ($query) use ($days) {
                $query = $query->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED])->where('pay_status', PS_PAYED)->where('shipping_status', SS_RECEIVED)->where('main_count', 0);
                $query->where('confirm_take_time', '>', 0)->where('confirm_take_time', '<=', $days);
            })->whereHasIn('getSellerShopInfo', function ($query) {
                $query->where('shop_can_comment', 1);
            })->select('rec_id', 'order_id', 'user_id', 'goods_id', 'ru_id')->orderBy('rec_id')->chunk(10, function ($order_goods) use ($auto_evaluate_time, $auto_evaluate_content, $comment_check) {
                if (!empty($order_goods)) {
                    foreach ($order_goods as $k => $goods) {

                        // 兼容处理旧数据 已评价 未更新 is_comment => 1
                        $comment = DB::table('comment')->distinct()->select('comment_id', 'rec_id')->where('comment_type', 0)
                            ->where('parent_id', 0)
                            ->where('user_id', $goods->user_id)
                            ->where('order_id', $goods->order_id)
                            ->where('rec_id', $goods->rec_id)
                            ->where('add_comment_id', 0)
                            ->orderBy('comment_id')
                            ->first();

                        if (!empty($comment)) {
                            $comment_id = $comment->comment_id ?? 0;
                        } else {
                            /* 添加评论内容 */
                            $user = DB::table('users')->where('user_id', $goods->user_id)->select('user_name', 'email')->first();
                            $user_name = $user->user_name ?? '';
                            $email = $user->email ?? '';

                            $values = [
                                'comment_type' => 0, // 评论类型
                                'id_value' => $goods->goods_id, // 订单商品id
                                'email' => $email,
                                'user_name' => $user_name,
                                'content' => $auto_evaluate_content,
                                'comment_rank' => 5,
                                'comment_server' => 5,
                                'comment_delivery' => 5,
                                'add_time' => TimeRepository::getGmTime(),
                                'ip_address' => self::get_client_ip(),
                                'status' => $comment_check,
                                'parent_id' => 0,
                                'user_id' => $goods->user_id,
                                'ru_id' => $goods->ru_id,
                                'order_id' => $goods->order_id,
                                'rec_id' => $goods->rec_id,
                                'goods_tag' => '',
                            ];
                            $comment_id = DB::table('comment')->insertGetId($values);
                        }

                        if ($comment_id) {
                            // 更新订单商品已评价
                            DB::table('order_goods')->where('rec_id', $goods->rec_id)->where('user_id', $goods->user_id)->update(['is_comment' => 1]);
                        }
                    }

                    // 更新订单统计
                    Artisan::call('app:user:order');
                }

            });

    }

    /**
     * Get client ip.
     *
     * @return string
     */
    protected static function get_client_ip()
    {
        if (!empty(request()->server('REMOTE_ADDR'))) {
            $ip = request()->server('REMOTE_ADDR');
        } else {
            // for php-cli(phpunit etc.)
            $ip = defined('PHPUNIT_RUNNING') ? '127.0.0.1' : gethostbyname(gethostname());
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ?: '127.0.0.1';
    }
}
