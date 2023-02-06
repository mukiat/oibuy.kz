<?php

use App\Services\Express\ExpressCommonService;
use Illuminate\Database\Seeder;


class ExpressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // php artisan db:seed --class=ExpressSeeder
        // 添加快递跟踪配置
        ExpressCommonService::oldConfig();
    }

}
