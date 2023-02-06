<?php

use Illuminate\Database\Seeder;
use App\Models\TradeSnapshot;
use App\Models\OrderInfo;

class TradeSnapshotSeeder extends Seeder
{
    public function run()
    {
        $this->update();
    }

    private function update()
    {
        TradeSnapshot::query()->where('order_id', '<', 1)->chunk(10, function ($list) {
            foreach ($list as $k => $v) {
                $order_id = OrderInfo::where('order_sn', $v->order_sn)->value('order_id');
                $order_id = $order_id ? $order_id : 0;

                $res = TradeSnapshot::where('trade_id', $v->trade_id)->update([
                    'order_id' => $order_id
                ]);

                if ($res > 0) {
                    dump('【' . $v->trade_id . '】更新成功, 订单号：' . $v->order_sn);
                }

                sleep(0.5);
            }
        });
    }
}
