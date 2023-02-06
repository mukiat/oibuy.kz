<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderIdToTradeSnapshotTable extends Migration
{
    private $table = 'trade_snapshot';

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

        if (!Schema::hasColumn($this->table, 'order_id')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->integer('order_id')->index('order_id')->default(0)->comment('订单ID')->after('trade_id');
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
        if (Schema::hasColumn($this->table, 'order_id')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('order_id');
            });
        }
    }
}
