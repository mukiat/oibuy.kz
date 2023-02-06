<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFiledToGoodsInventoryLogsTable extends Migration
{
    private $table = 'goods_inventory_logs';

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

        Schema::table($this->table, function (Blueprint $table) {
            $table->string('number', 160)->default('')->change();
            $table->integer('add_time')->default(0)->change();
            $table->string('batch_number', 50)->default('')->change();
            $table->string('remark')->default('')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->table, function (Blueprint $table) {
            //
        });
    }
}
