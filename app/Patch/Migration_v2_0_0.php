<?php

namespace App\Patch;

use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Migration_v2_0_0
{
    public function run()
    {
        try {
            $this->shopConfig();
            $this->seckill_goods();
            $this->goods_video();
            $this->order_info();
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function seckill_goods()
    {
        $table_name = 'seckill_goods';
        if (!Schema::hasTable($table_name)) {
            return false;
        }

        if (!Schema::hasColumn($table_name, 'sales_volume')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->integer('sales_volume')->default(0)->comment('秒杀商品销量');
            });
        }
    }

    public function goods_video()
    {
        $table_name = 'goods_video';
        if (Schema::hasTable($table_name)) {
            return false;
        }

        Schema::create($table_name, function (Blueprint $table) {
            $table->increments('video_id')->comment('自增ID号');
            $table->integer('goods_id')->unsigned()->default(0)->index('goods_id')->comment('商品的id');
            $table->string('goods_video')->default('')->comment('商品视频');
            $table->integer('look_num')->default(0)->comment('观看人数');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function order_info()
    {
        $table_name = 'order_info';

        if (!Schema::hasTable($table_name)) {
            return false;
        }

        if (!Schema::hasColumn($table_name, 'vc_dis_money')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->decimal('vc_dis_money', 10, 2)->default(0)->comment('储值卡折扣金额');
            });
        }
    }

    /**
     * 更新版本
     *
     * @throws Exception
     */
    private function shopConfig()
    {
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v2.0.0'
        ]);

        $this->clearCache();
    }

    /**
     * @throws Exception
     */
    private function clearCache()
    {
        cache()->flush();
    }
}
