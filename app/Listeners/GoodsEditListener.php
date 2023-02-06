<?php

namespace App\Listeners;

use App\Events\GoodsEditEvent;
use App\Models\UserRank;
use App\Models\VolumePrice;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\TimeRepository;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class GoodsEditListener
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
     * @param GoodsEditEvent $event
     * @return bool|mixed
     */
    public function handle(GoodsEditEvent $event)
    {
        $handler = $event->handler ?? '';
        if (empty($handler)) {
            return false;
        }

        $goods_info = $event->goods_info ?? [];
        // 扩展参数
        $extendParam = $event->extendParam ?? [];

        $goods_id = $goods_info['goods_id'] ?? 0;

        if ($handler == 'change_log') {
            if (empty($goods_id) || empty($goods_info)) {
                return false;
            }

            $admin_id = $extendParam['admin_id'] ?? [];

            // 商品操作日志 更新前数据

            // 商品修改前 会员等级价格
            $member_price_arr = UserRank::with([
                'getMemberPrice' => function ($query) use ($goods_id) {
                    $query->where('goods_id', $goods_id);
                }
            ])->orderBy('min_points');
            $member_price_arr = BaseRepository::getToArrayGet($member_price_arr);
            $member_price_old = [];
            if ($member_price_arr) {
                foreach ($member_price_arr as $val) {
                    $user_price = '-1';
                    $get_member_price = $val['get_member_price'] ?? [];
                    if ($get_member_price) {
                        $percentage = $get_member_price['percentage'] ?? 0;
                        $user_price = $get_member_price['user_price'] ?? 0;
                        if ($percentage == 1) {
                            $user_price = $user_price . '%';
                        }
                    }

                    $member_price_old[$val['rank_id']] = $user_price ?? -1;
                }
            }

            // 商品修改前 阶梯价格
            $volume_price_old = [];
            $res = VolumePrice::where('goods_id', $goods_id);
            $volume_price_arr = BaseRepository::getToArrayGet($res);
            if ($volume_price_arr) {
                foreach ($volume_price_arr as $v) {
                    $volume_price_old[$v['volume_number']] = $v['volume_price'];
                }
            }

            $goods_info['member_price'] = empty($member_price_old) ? '' : serialize($member_price_old);
            $goods_info['volume_price'] = empty($volume_price_old) ? '' : serialize($volume_price_old);

            $data_old = [
                'goods_id' => $goods_id,
                'shop_price' => $goods_info['shop_price'] ?? 0,
                'shipping_fee' => $goods_info['shipping_fee'] ?? 0,
                'promote_price' => $goods_info['promote_price'] ?? 0,
                'member_price' => $goods_info['member_price'] ?? 0,
                'volume_price' => $goods_info['volume_price'] ?? 0,
                'give_integral' => $goods_info['give_integral'] ?? 0,
                'rank_integral' => $goods_info['rank_integral'] ?? 0,
                'goods_weight' => $goods_info['goods_weight'] ?? 0,
                'is_on_sale' => $goods_info['is_on_sale'] ?? 0,
                'user_id' => $admin_id,
                'handle_time' => TimeRepository::getGmTime(),
                'old_record' => 1,
            ];
            DB::table('goods_change_log')->insert($data_old);

            //商品操作日志 更新后数据
            $logs_change_new = $extendParam['logs_change_new'] ?? [];

            $data_new = [
                'goods_id' => $goods_id,
                'shop_price' => $logs_change_new['shop_price'] ?? ($goods_info['shop_price'] ?? 0),
                'shipping_fee' => $logs_change_new['shipping_fee'] ?? ($goods_info['shipping_fee'] ?? 0),
                'promote_price' => $logs_change_new['promote_price'] ?? ($goods_info['promote_price'] ?? 0),
                'member_price' => $logs_change_new['member_price'] ?? ($goods_info['member_price'] ?? 0),
                'volume_price' => $logs_change_new['volume_price'] ?? ($goods_info['volume_price'] ?? 0),
                'give_integral' => $logs_change_new['give_integral'] ?? ($goods_info['give_integral'] ?? 0),
                'rank_integral' => $logs_change_new['rank_integral'] ?? ($goods_info['rank_integral'] ?? 0),
                'goods_weight' => $logs_change_new['goods_weight'] ?? ($goods_info['goods_weight'] ?? 0),
                'is_on_sale' => $logs_change_new['is_on_sale'] ?? ($goods_info['is_on_sale'] ?? 0),
                'user_id' => $admin_id,
                'handle_time' => TimeRepository::getGmTime(),
                'old_record' => 0
            ];
            DB::table('goods_change_log')->insert($data_new);
            return true;
        }

    }
}

