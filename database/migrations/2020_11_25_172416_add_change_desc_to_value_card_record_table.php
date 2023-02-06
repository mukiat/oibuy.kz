<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChangeDescToValueCardRecordTable extends Migration
{
    private $table_name = 'value_card_record';

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

        if (!Schema::hasColumn($this->table_name, 'change_desc')) {
            Schema::table($this->table_name, function (Blueprint $table) {
                $table->string('change_desc')->default('')->comment('操作记录');
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
        Schema::table('value_card_record', function (Blueprint $table) {
            //
        });
    }
}
