<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsReceivedToOrderGoodsTable extends Migration
{
    private $table = 'order_goods';

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

        if (!Schema::hasColumn($this->table, 'is_received')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->integer('is_received')->index('is_received')->default(0)->comment('是否退货退款：0 否, 1 是');
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
        if (Schema::hasColumn($this->table, 'is_received')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('is_received');
            });
        }
    }
}
