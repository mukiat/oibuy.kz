<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeShippingDateStrToOrderInfoTable extends Migration
{

    private $table = 'order_info';

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

        if (Schema::hasColumn($this->table, 'shipping_dateStr')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->renameColumn('shipping_dateStr', 'shipping_date_str');
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
