<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeRuIdToCouponsTable extends Migration
{
    private $table = "coupons";

    /**
     * @return bool
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            return false;
        }

        if (Schema::hasColumn($this->table, 'ru_id')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->integer('ru_id')->change();
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
        Schema::table('coupons', function (Blueprint $table) {
            //
        });
    }
}
