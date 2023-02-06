<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValueCardDiscountToOrderGoodsTable extends Migration
{
    private $table_name = 'order_goods';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table_name)) {
            return false;
        }

        if (!Schema::hasColumn($this->table_name, 'value_card_discount')) {
            Schema::table($this->table_name, function (Blueprint $table) {
                $table->decimal('value_card_discount', 10, 2)->default(0)->unsigned()->comment('储值卡均摊折扣金额');
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
