<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWeightsToGoodsTable extends Migration
{
    protected $table_name = 'goods';

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
        if (!Schema::hasColumn($this->table_name, 'weights')) {
            Schema::table($this->table_name, function (Blueprint $table) {
                $table->integer('weights')->default(100)->comment('商品权重值');
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
        if (Schema::hasColumn($this->table_name, 'weights')) {
            Schema::table($this->table_name, function (Blueprint $table) {
                $table->dropColumn('weights');
            });
        }
    }
}
