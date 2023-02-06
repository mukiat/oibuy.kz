<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCommissionRateToSellerNegativeOrderTable extends Migration
{
    private $table = 'seller_negative_order';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            return false;
        }
        if (!Schema::hasColumn($this->table, 'commission_rate')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->string('seller_proportion', 10)->default(0)->comment('店铺佣金利率百分比');
                $table->string('cat_proportion', 10)->default(0)->comment('商品分类佣金利率百分比');
                $table->string('commission_rate', 10)->default(0)->comment('商品佣金利率百分比');
                $table->decimal('gain_commission', 10, 2)->default(0)->comment('收取退款佣金金额');
                $table->decimal('should_amount', 10, 2)->default(0)->comment('应结退款佣金金额');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 删除字段
        if (Schema::hasColumn($this->table, 'commission_rate')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('gain_commission');
                $table->dropColumn('should_amount');
                $table->dropColumn('seller_proportion');
                $table->dropColumn('cat_proportion');
                $table->dropColumn('commission_rate');
            });
        }
    }
}
