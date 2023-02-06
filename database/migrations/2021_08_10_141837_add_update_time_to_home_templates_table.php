<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUpdateTimeToHomeTemplatesTable extends Migration
{
    private $table = 'home_templates';

    /**
     * Run the migrations.
     *
     * @return bool
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            return false;
        }

        if (!Schema::hasColumn($this->table, 'update_time')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->integer('update_time')->default(0)->comment("更新时间");
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
        Schema::table('home_templates', function (Blueprint $table) {
            //
        });
    }
}
