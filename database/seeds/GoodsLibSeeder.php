<?php

use Illuminate\Database\Seeder;
use App\Models\GoodsLib;
use App\Repositories\Common\BaseRepository;
use App\Services\Goods\GoodsDataHandleService;

class GoodsLibSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->updateRuId();
    }

    private function updateRuId()
    {
        GoodsLib::query()->select('goods_id', 'lib_goods_id', 'from_seller')
            ->where('lib_goods_id', '>', 0)
            ->where('from_seller', 0)
            ->chunkById(5, function ($list) {

                $list = collect($list)->toArray();

                $goods_id = BaseRepository::getKeyPluck($list, 'lib_goods_id');
                $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'user_id']);

                foreach ($list as $key => $val) {
                    $val = collect($val)->toArray();

                    $goods = $goodsList[$val['lib_goods_id']] ?? [];

                    if ($goods && !empty($goods['user_id'])) {

                        GoodsLib::where('lib_goods_id', $val['lib_goods_id'])
                            ->update([
                                'from_seller' => $goods['user_id']
                            ]);

                        dump('已更新关联商品ID：' . $val['lib_goods_id']);

                        sleep(0.5);
                    }
                }
            });
    }
}