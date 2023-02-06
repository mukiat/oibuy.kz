<?php

namespace App\Patch;

use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Migration_v1_6_2
{
    public function run()
    {
        try {
            $this->goodsAddWeights();
            $this->order_info();
            $this->order_goods();
            $this->pay_log();
            $this->goods_inventory_logs();
            $this->shopConfig();
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * 添加商品权重值字段
     * @throws Exception
     */
    private function goodsAddWeights()
    {
        $table_name = 'goods';

        if (!Schema::hasTable($table_name)) {
            return false;
        }
        if (!Schema::hasColumn($table_name, 'weights')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->integer('weights')->default(100)->comment('商品权重值');
            });
        }
    }

    public function order_info()
    {
        $tableName = 'order_info';
        if (!Schema::hasTable($tableName)) {
            return false;
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->integer('agency_id')->default(0)->change();
            $table->string('inv_type')->default('')->change();
            $table->decimal('tax', 10, 2)->default(0)->change();
            $table->decimal('discount', 10, 2)->default(0)->change();
            $table->decimal('discount_all', 10, 2)->default(0)->change();
            $table->integer('zc_goods_id')->default(0)->change();
            $table->string('rel_name')->default('')->change();
            $table->string('id_num')->default('')->change();
            $table->string('sign_time', 60)->default(0)->change();
        });

        if (Schema::hasColumn($tableName, 'shipping_dateStr')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('shipping_dateStr')->default('')->change();
            });
        }
    }

    public function order_goods()
    {
        $tableName = 'order_goods';

        if (!Schema::hasTable($tableName)) {
            return false;
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->string('product_sn')->default('')->change();
            $table->decimal('rate_price', 10, 2)->default(0)->change();
        });
    }

    public function goods_inventory_logs()
    {
        $tableName = 'goods_inventory_logs';

        if (!Schema::hasTable($tableName)) {
            return false;
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->string('number', 160)->default('')->change();
            $table->integer('add_time')->default(0)->change();
            $table->string('batch_number', 50)->default('')->change();
            $table->string('remark')->default('')->change();
        });
    }

    public function pay_log()
    {
        $tableName = 'pay_log';

        if (!Schema::hasTable($tableName)) {
            return false;
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->decimal('order_amount', 10, 2)->default(0)->change();
            $table->string('openid')->default('')->change();
            $table->string('transid')->default('')->change();
        });
    }

    /**
     * 更新版本
     * @throws Exception
     */
    private function shopConfig()
    {
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v1.6.2'
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