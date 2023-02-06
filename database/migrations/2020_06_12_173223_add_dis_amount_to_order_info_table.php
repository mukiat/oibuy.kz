<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDisAmountToOrderInfoTable extends Migration
{
    protected $table_name = 'order_info';

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
        if (!Schema::hasColumn($this->table_name, 'dis_amount')) {
            Schema::table($this->table_name, function (Blueprint $table) {
                $table->decimal('dis_amount', 10, 2)->default(0.00)->comment('商品满减优惠总金额');
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
        Schema::table('order_info', function (Blueprint $table) {
            //
        });
    }
}
