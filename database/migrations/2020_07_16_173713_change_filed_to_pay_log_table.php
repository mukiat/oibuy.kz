<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFiledToPayLogTable extends Migration
{
    private $table = 'pay_log';

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

        Schema::table($this->table, function (Blueprint $table) {
            $table->decimal('order_amount', 10, 2)->default(0)->change();
            $table->string('openid')->default('')->change();
            $table->string('transid')->default('')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->table, function (Blueprint $table) {
            //
        });
    }
}
