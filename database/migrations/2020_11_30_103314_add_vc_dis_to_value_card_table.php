<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVcDisToValueCardTable extends Migration
{
    private $table_name = 'value_card';

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

        if (!Schema::hasColumn($this->table_name, 'vc_dis')) {
            Schema::table($this->table_name, function (Blueprint $table) {
                $table->decimal('vc_dis', 10, 2)->default(0)->unsigned()->after('card_money')->comment('储值卡折扣');
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
        Schema::table('value_card', function (Blueprint $table) {
            //
        });
    }
}
