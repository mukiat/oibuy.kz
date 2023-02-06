<?php

namespace App\Patch;

use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Migration_v2_1_2
{
    public function run()
    {
        try {

            $this->cart();
            $this->order_goods();
            $this->order_return();

            $this->shopConfig();
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function cart()
    {
        $table_name = 'cart';
        if (!Schema::hasTable($table_name)) {
            return false;
        }

        if (!Schema::hasColumn($table_name, 'goods_favourable')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->decimal('goods_favourable', 10, 2)->default(0)->comment('优惠活动均摊金额');
            });
        }
    }

    public function order_goods()
    {
        $table_name = 'order_goods';
        if (!Schema::hasTable($table_name)) {
            return false;
        }

        if (!Schema::hasColumn($table_name, 'goods_favourable')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->decimal('goods_favourable', 10, 2)->default(0)->comment('优惠活动均摊金额');
            });
        }
    }

    public function order_return()
    {
        $table_name = 'order_return';
        if (!Schema::hasTable($table_name)) {
            return false;
        }

        if (!Schema::hasColumn($table_name, 'goods_favourable')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->decimal('goods_favourable', 10, 2)->default(0)->comment('优惠活动金额');
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
            'value' => 'v2.1.2'
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