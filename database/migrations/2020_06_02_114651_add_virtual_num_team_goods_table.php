<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVirtualNumTeamGoodsTable extends Migration
{
    private $table = 'team_goods';

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

        if (!Schema::hasColumn($this->table, 'virtual_num')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->integer('virtual_num')->default(0)->comment('拼团虚拟销量');
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
        if (Schema::hasColumn($this->table, 'virtual_num')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('virtual_num');
            });
        }
    }
}
