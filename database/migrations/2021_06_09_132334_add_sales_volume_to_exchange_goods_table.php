<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalesVolumeToExchangeGoodsTable extends Migration
{
    private $table = 'exchange_goods';

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

        if (!Schema::hasColumn($this->table, 'sales_volume')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->integer('sales_volume')->default(0)->comment("积分商品销量");
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
        Schema::table('exchange_goods', function (Blueprint $table) {
            //
        });
    }
}
