<?php

use Illuminate\Database\Seeder;
use App\Models\BookingGoods;
use App\Models\Goods;

class BookingGoodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /* 微商城 */
        $BookingUpRu = Storage::disk('local')->exists('seeder/BookingUpRu.lock.php');
        if (!$BookingUpRu) {
            $this->bookingUpRu();

            $data = '大商创x https://www.dscmall.cn/';
            Storage::disk('local')->put('seeder/BookingUpRu.lock.php', $data);
        }
    }

    private function bookingUpRu()
    {
        BookingGoods::query()->chunk(1, function ($list) {
            foreach ($list as $k => $v) {
                $ru_id = Goods::where('goods_id', $v->goods_id)->value('user_id');
                $ru_id = $ru_id ? $ru_id : 0;

                $id = BookingGoods::where('rec_id', $v->rec_id)->update([
                    'ru_id' => $ru_id
                ]);

                if ($id > 0) {
                    dump("更新商品缺货数据成功");
                }

                sleep(0.3);
            }
        });
    }
}
