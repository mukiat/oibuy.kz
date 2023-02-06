<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVcDisMoneyToValueCardRecordTable extends Migration
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

        if (!Schema::hasColumn($this->table_name, 'vc_dis_money')) {
            Schema::table($this->table_name, function (Blueprint $table) {
                $table->decimal('vc_dis_money', 10, 2)->default(0)->comment('储值卡折扣金额');
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
