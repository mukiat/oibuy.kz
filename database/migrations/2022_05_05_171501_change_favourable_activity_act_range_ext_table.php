<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFavourableActivityActRangeExtTable extends Migration
{
    /**
     * @return bool
     */
    public function up()
    {
        if (Schema::hasTable('favourable_activity')) {
            if (Schema::hasColumn('favourable_activity', 'act_range_ext')) {
                Schema::table('favourable_activity', function (Blueprint $table) {
                    $table->text('act_range_ext')->default('')->change();
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

        if (Schema::hasTable('favourable_activity')) {
            if (Schema::hasColumn('favourable_activity', 'act_range_ext')) {
                Schema::table('favourable_activity', function (Blueprint $table) {
                    $table->string('act_range_ext')->default('')->change();
                });
            }
        }
    }
}
