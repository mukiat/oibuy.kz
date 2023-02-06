<?php

namespace App\Console\Commands;

use App\Models\SeckillGoods;
use App\Services\Seckill\SeckillGoodsService;
use Illuminate\Console\Command;

class SeckillServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seckill {action=volume}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'seckill command';

    private $seckillGoodsService;

    public function __construct(
        SeckillGoodsService $seckillGoodsService
    )
    {
        parent::__construct();
        $this->seckillGoodsService = $seckillGoodsService;
    }

    /**
     * 缓存操作
     *
     * @throws \Exception
     */
    public function handle()
    {
        $action = $this->argument('action');

        if ($action == 'volume') {
            $this->seckillVolume();
        }
    }

    private function seckillVolume()
    {
        SeckillGoods::chunk(10, function ($list) {
            foreach ($list as $key => $value) {
                $value = collect($value)->toArray();

                $stats = $this->seckillGoodsService->secGoodsStats($value['id']);

                SeckillGoods::where('id', $value['id'])->update([
                    'sales_volume' => $stats['valid_goods']
                ]);
            }
        });
    }
}