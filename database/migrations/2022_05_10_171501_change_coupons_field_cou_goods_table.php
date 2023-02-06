<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCouponsFieldCouGoodsTable extends Migration
{
    private $name = 'coupons';

    /**
     * @return bool
     */
    public function up()
    {
        if (Schema::hasTable($this->name)) {
            if (Schema::hasColumn($this->name, 'cou_goods')) {
                Schema::table($this->name, function (Blueprint $table) {
                    $table->text('cou_goods')->default('')->change();
                });
            }

            if (Schema::hasColumn($this->name, 'cou_ok_goods')) {
                Schema::table($this->name, function (Blueprint $table) {
                    $table->text('cou_ok_goods')->default('')->change();
                });
            }
        }
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable($this->name)) {
            if (Schema::hasColumn($this->name, 'cou_goods')) {
                Schema::table($this->name, function (Blueprint $table) {
                    $table->string('cou_goods')->default('')->change();
                });
            }

            if (Schema::hasColumn($this->name, 'cou_ok_goods')) {
                Schema::table($this->name, function (Blueprint $table) {
                    $table->string('cou_ok_goods')->default('')->change();
                });
            }
        }
    }
}
