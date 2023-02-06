<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRetIdToValueCardRecordTable extends Migration
{
    private $table = 'value_card_record';

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

        if (!Schema::hasColumn($this->table, 'ret_id')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->integer('ret_id')->default(0)->unsigned()->comment('单品退货单ID');
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
