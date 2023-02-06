<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGoodsFavourableToOrderReturnTable extends Migration
{
    private $table_name = 'order_return';

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

        if (!Schema::hasColumn($this->table_name, 'goods_favourable')) {
            Schema::table($this->table_name, function (Blueprint $table) {
                $table->decimal('goods_favourable', 10, 2)->default(0)->comment('优惠活动金额');
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
        Schema::table('order_return', function (Blueprint $table) {
            //
        });
    }
}
