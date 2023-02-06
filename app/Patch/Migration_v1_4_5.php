<?php

namespace App\Patch;

use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Migration_v1_4_5
{
    public function run()
    {
        $this->Goods();
        $this->orderSettlementLog();
        $this->sellerNegativeOrder();
        $this->sellerNegativeBill();

        if (Schema::hasTable('ad_custom')) {
            Schema::drop('ad_custom');
        }

        try {
            $this->shopConfig();
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }


    public function Goods()
    {
        $tableName = 'goods';
        if (!Schema::hasTable($tableName)) {
            return false;
        }

        // 判断字段是否存在添加
        if (!Schema::hasColumn($tableName, 'is_discount')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedTinyInteger('is_discount')->default(0)->comment('是否参与会员特价权益: 0 否，1 是');
            });
        }
    }

    public function orderSettlementLog()
    {
        $tableName = 'order_settlement_log';
        if (!Schema::hasTable($tableName)) {
            return false;
        }

        if (!Schema::hasColumn($tableName, 'gain_amount')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->decimal('gain_amount', 10, 2)->default(0)->after('is_settlement')->comment('实际收取金额');
            });
        }
    }

    public function sellerNegativeOrder()
    {
        $tableName = 'seller_negative_order';
        if (!Schema::hasTable($tableName)) {
            return false;
        }
        if (!Schema::hasColumn($tableName, 'commission_rate')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('seller_proportion', 10)->default(0)->comment('店铺佣金利率百分比');
                $table->string('cat_proportion', 10)->default(0)->comment('商品分类佣金利率百分比');
                $table->string('commission_rate', 10)->default(0)->comment('商品佣金利率百分比');
                $table->decimal('gain_commission', 10, 2)->default(0)->comment('收取退款佣金金额');
                $table->decimal('should_amount', 10, 2)->default(0)->comment('应结退款佣金金额');
            });
        }
    }

    public function sellerNegativeBill()
    {
        $tableName = 'seller_negative_bill';
        if (!Schema::hasTable($tableName)) {
            return false;
        }
        if (!Schema::hasColumn($tableName, 'actual_deducted')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->decimal('actual_deducted', 10, 2)->default(0)->comment('实际扣除总金额');
            });
        }
    }

    /**
     * 更新版本
     * @throws Exception
     */
    private function shopConfig()
    {
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v1.4.5'
        ]);

        cache()->forget('shop_config');
    }
}
