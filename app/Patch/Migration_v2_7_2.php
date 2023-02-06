<?php

namespace App\Patch;


use App\Events\RunOtherModulesSeederEvent;
use App\Models\ShopConfig;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Migration_v2_7_2
{
    public function run()
    {
        try {
            $this->migration();

            // 执行其他模块seed
            event(new RunOtherModulesSeederEvent());

            $this->seed();
            $this->clean();
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    private function migration()
    {
        $this->order_info();
    }

    public function order_info()
    {
        $name = 'order_info';
        if (Schema::hasColumn($name, 'auto_delivery_time')) {
            Schema::table($name, function (Blueprint $table) {
                $table->integer('auto_delivery_time')->default(7)->change();
            });
        }
    }

    private function seed()
    {
        /**
         * 自动确认收货配置
         */
        $count = ShopConfig::where('code', 'auto_delivery_time')->count();

        if ($count <= 0) {
            $parent_id = ShopConfig::where('code', 'pay')->value('id');
            $parent_id = $parent_id ? $parent_id : 942;

            ShopConfig::insert([
                'parent_id' => $parent_id,
                'code' => 'auto_delivery_time',
                'type' => 'text',
                'store_range' => '',
                'sort_order' => 1,
                'value' => 15
            ]);
        }

        // 版本释放之前更新版本
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v2.7.2'
        ]);
    }

    private function clean()
    {
        cache()->flush();
    }
}