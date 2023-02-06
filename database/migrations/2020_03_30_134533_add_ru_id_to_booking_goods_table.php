<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRuIdToBookingGoodsTable extends Migration
{
    private $table = 'booking_goods';
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

        if (!Schema::hasColumn($this->table, 'ru_id')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->integer('ru_id')->index('ru_id')->default(0)->comment('店铺ID');
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
        if (Schema::hasColumn($this->table, 'ru_id')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('ru_id');
            });
        }
    }
}
