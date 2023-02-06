<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGoodsIntegralToOrderGoodsTable extends Migration
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

        if (!Schema::hasColumn($this->table, 'goods_integral')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->integer('goods_integral')->default(0)->comment('积分均摊');
            });
        }

        if (!Schema::hasColumn($this->table, 'goods_integral_money')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->decimal('goods_integral_money', 10, 2)->default(0)->comment('积分金额均摊');
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
        Schema::table('order_goods', function (Blueprint $table) {
            //
        });
    }
}
