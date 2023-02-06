<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPayCodeToOrderInfoTable extends Migration
{
    private $name = 'order_info';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->name)) {
            return false;
        }

        if (!Schema::hasColumn($this->name, 'pay_code')) {
            Schema::table($this->name, function (Blueprint $table) {
                $table->string('pay_code')->default('')->comment("支付编码");
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
        Schema::table($this->name, function (Blueprint $table) {
            //
        });
    }
}
