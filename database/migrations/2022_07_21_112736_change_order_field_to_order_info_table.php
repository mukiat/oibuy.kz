<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeOrderFieldToOrderInfoTable extends Migration
{
    private $name = 'order_info';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->name)) {
            if (Schema::hasColumn($this->name, 'auto_delivery_time')) {
                Schema::table($this->name, function (Blueprint $table) {
                    $table->integer('auto_delivery_time')->default(7)->change();
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
        Schema::table($this->name, function (Blueprint $table) {
            //
        });
    }
}
