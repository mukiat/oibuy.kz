<?php

use App\Models\OrderGoods;
use App\Models\OrderInfo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class OrderGoodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $drp = Storage::disk('local')->exists('seeder/order_drp.lock.php');
        if ($drp === false) {
            $this->orderGoodsDrp();

            $data = '大商创x https://www.dscmall.cn/';
            Storage::disk('local')->put('seeder/order_drp.lock.php', $data);
        }
    }

    /**
     * 更新是否分销商品订单
     */
    private function orderGoodsDrp()
    {
        OrderGoods::query()->where('drp_money', '>', 0)
            ->chunk(10, function ($list) {
                foreach ($list as $key => $val) {
                    $res = OrderInfo::where('is_drp', 0)
                        ->where('order_id', $val->order_id)
                        ->update(['is_drp' => 1]);

                    if ($res) {
                        info("更新是否分销商品订单， 订单商品ID【" . $val->rec_id . "】更新成功，订单ID【" . $val->order_id . "】");
                    }

                    sleep(0.2);
                }
            });
    }
}
