<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActualDeductedToSellerNegativeBillTable extends Migration
{
    private $table = 'seller_negative_bill';

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
        if (!Schema::hasColumn($this->table, 'actual_deducted')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->decimal('actual_deducted', 10, 2)->default(0)->comment('实际扣除总金额');
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
        if (Schema::hasColumn($this->table, 'actual_deducted')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('actual_deducted');
            });
        }
    }
}
