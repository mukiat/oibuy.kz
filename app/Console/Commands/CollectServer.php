<?php

namespace App\Console\Commands;

use App\Models\CollectStore;
use App\Models\MerchantsShopInformation;
use App\Repositories\Common\BaseRepository;
use Illuminate\Console\Command;

class CollectServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:collect {action=store}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect command';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $action = $this->argument('action');

        /*------------------------------------------------------ */
        //-- 更新同步店铺关注数据
        /*------------------------------------------------------ */
        if ($action == 'store') {
            $this->store();
        }
    }

    /**
     * 更新同步店铺关注数据
     */
    private function store()
    {

        $ruIdList = CollectStore::query()->pluck('ru_id');
        $ruIdList = BaseRepository::getToArray($ruIdList);

        if ($ruIdList) {
            MerchantsShopInformation::whereIn('user_id', $ruIdList)->update([
                'collect_count' => 0
            ]);

            CollectStore::query()->chunkById(1, function ($list) {
                foreach ($list as $key => $row) {
                    $row = collect($row)->toArray();
                    MerchantsShopInformation::query()->where('user_id', $row['ru_id'])->increment('collect_count', 1);
                }
            });
        }
    }
}