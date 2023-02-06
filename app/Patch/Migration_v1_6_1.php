<?php

namespace App\Patch;

use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Migration_v1_6_1
{
    public function run()
    {
        try {
            $this->goods_history();
            $this->changeCart();
            $this->shopConfig();
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function changeCart()
    {
        /*Schema::table('cart', function (Blueprint $table) {
            $table->integer('product_id')->default(0)->change();
            $table->string('group_id')->default('')->change();
            $table->string('store_mobile', 20)->default('')->change();
            $table->string('take_time', 30)->default('')->change();
        });*/
    }

    public function goods_history()
    {
        $tableName = 'goods_history';

        if (Schema::hasTable($tableName)) {
            return false;
        }

        Schema::create($tableName, function (Blueprint $table) {
            $table->increments('history_id')->comment('自增ID号');
            $table->integer('user_id')->unsigned()->default(0)->index('user_id')->comment('会员id');
            $table->string('session_id')->nullable()->index('session_id')->comment('登录的sessionid');
            $table->integer('goods_id')->unsigned()->default(0)->index('goods_id')->comment('商品的id');
            $table->integer('add_time')->default(0)->comment('添加时间');
        });
    }

    /**
     * 更新版本
     * @throws Exception
     */
    private function shopConfig()
    {
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v1.6.1'
        ]);

        $this->clearCache();
    }

    /**
     * @throws \Exception
     */
    private function clearCache()
    {
        cache()->flush();
    }
}