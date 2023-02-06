<?php

namespace App\Console\Commands;

use App\Models\GoodsHistory;
use App\Repositories\Common\TimeRepository;
use Illuminate\Console\Command;

class GoodsHistoryServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:goods:history:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear Goods History';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 清除商品浏览记录超过70天数据
     */
    public function handle()
    {
        $new_time = TimeRepository::getGmTime();
        $min_time = $new_time - (int)(60 * 60 * 24 * 70);
        GoodsHistory::where('add_time', '<', $min_time)->delete();
    }
}
