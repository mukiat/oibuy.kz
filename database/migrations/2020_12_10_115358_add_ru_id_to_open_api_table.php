<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRuIdToOpenApiTable extends Migration
{
    private $table = 'open_api';

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

        if (!Schema::hasColumn($this->table, 'ru_id')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->integer('ru_id')->default(0)->unsigned()->comment('商家ID');
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
        Schema::table('open_api', function (Blueprint $table) {
            //
        });
    }
}
