<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGoodsCouponsToCartTable extends Migration
{
    private $table = 'cart';

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

        if (!Schema::hasColumn($this->table, 'goods_coupons')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->decimal('goods_coupons', 10, 2)->default(0)->unsigned()->comment('优惠券均摊商品');
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
        Schema::table('cart', function (Blueprint $table) {
            //
        });
    }
}
