<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserBonusFieldToUserBonus extends Migration
{
    protected $table_name = 'user_bonus';

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
        if (!Schema::hasColumn($this->table_name, 'return_order_id')) {
            Schema::table($this->table_name, function (Blueprint $table) {
                $table->integer('return_order_id')->default(0)->comment('通过该order_id得到的红包');
                $table->integer('return_goods_id')->default(0)->comment('通过该goods_id得到的红包,按商品发放用到');
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
        Schema::table('user_bonus', function (Blueprint $table) {
            //
        });
    }
}
