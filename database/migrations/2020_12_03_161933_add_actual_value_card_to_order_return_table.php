<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActualValueCardToOrderReturnTable extends Migration
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

        if (!Schema::hasColumn($this->table_name, 'actual_value_card')) {
            Schema::table($this->table_name, function (Blueprint $table) {
                $table->decimal('actual_value_card', 10, 2)->default(0)->after('actual_return')->unsigned()->comment('实退储值卡金额');
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
