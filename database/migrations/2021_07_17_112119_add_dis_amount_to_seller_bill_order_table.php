<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDisAmountToSellerBillOrderTable extends Migration
{
    private $table = 'seller_bill_order';

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

        if (!Schema::hasColumn($this->table, 'dis_amount')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->decimal('dis_amount', 10, 2)->default(0)->comment("商品满减优惠总金额");
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
        Schema::table('seller_bill_order', function (Blueprint $table) {
            //
        });
    }
}
