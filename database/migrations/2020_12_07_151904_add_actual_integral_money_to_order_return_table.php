<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActualIntegralMoneyToOrderReturnTable extends Migration
{
    private $table = 'order_return';

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

        if (!Schema::hasColumn($this->table, 'actual_integral_money')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->decimal('actual_integral_money', 10, 2)->default(0)->unsigned()->comment('实退积分金额');
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
