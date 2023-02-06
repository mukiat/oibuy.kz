<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMainCountToOrderGoodsTable extends Migration
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

        if (!Schema::hasColumn($this->table, 'main_count')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->integer('main_count')->index('main_count')->default(0)->comment('是否主订单：0 否, 1 是');
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
        if (Schema::hasColumn($this->table, 'main_count')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('main_count');
            });
        }
    }
}
