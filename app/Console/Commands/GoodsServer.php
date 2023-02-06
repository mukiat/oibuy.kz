<?php

namespace App\Console\Commands;

use App\Models\ExchangeGoods;
use App\Models\Goods;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\StrRepository;
use App\Services\Brand\BrandDataHandleService;
use App\Services\Goods\GoodsDataHandleService;
use Illuminate\Console\Command;

class GoodsServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:goods {action=name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cache command';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 缓存操作
     *
     * @throws \Exception
     */
    public function handle()
    {
        $action = $this->argument('action');

        if ($action == 'name') {
            $this->updateGoodsNameList();
        } elseif ($action == 'exchange') {
            $this->updateExchangeGoodsSalesVolume();
        }
    }

    /**
     * 更新商品名称
     */
    private function updateGoodsNameList()
    {
        Goods::query()->select('goods_id', 'goods_name', 'brand_id')->chunkById(5, function ($list) {
            foreach ($list as $key => $row) {
                $row = collect($row)->toArray();

                $goods_name = $row['goods_name'] ?? '';
                $brand_id = $row['brand_id'] ?? 0;

                $brandList = BrandDataHandleService::goodsBrand($brand_id, ['brand_id', 'brand_name']);
                $brand_name = $brandList[$brand_id]['brand_name'] ?? '';

                if (!empty($brand_name) && stripos(config('app.goods_symbol'), 'null') === false) {
                    $goods_name = StrRepository::replaceFirst($goods_name, $brand_name);
                    $goods_name = StrRepository::replaceFirst($goods_name, config('app.replace_symbol'));
                    $goods_name = $brand_name . config('app.goods_symbol') . $goods_name;

                    Goods::where('goods_id', $row['goods_id'])->update([
                        'goods_name' => $goods_name
                    ]);

                    dump("【" . $row['goods_id'] . "】更新成功：" . $goods_name);
                }

                sleep(0.5);
            }
        });
    }

    /**
     * 更新积分商城库存
     */
    private function updateExchangeGoodsSalesVolume()
    {
        ExchangeGoods::query()->chunkById(5, function ($list) {

            $goods_id = BaseRepository::getKeyPluck($list, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'sales_volume', 'goods_name']);

            foreach ($list as $key => $row) {

                $row = collect($row)->toArray();
                $goods = $goodsList[$row['goods_id']] ?? [];

                if ($goods) {
                    ExchangeGoods::where('goods_id', $row['goods_id'])->update([
                        'sales_volume' => $goods['sales_volume']
                    ]);

                    dump("【" . $row['goods_id'] . "】更新成功：" . $goods['goods_name'] . " 销量：" . $goods['sales_volume']);
                } else {
                    dump("【" . $row['goods_id'] . "】更新失败，商品不存在");
                }

                sleep(0.5);
            }
        });
    }
}