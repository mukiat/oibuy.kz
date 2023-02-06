<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSeckillGoods extends Migration
{
    protected $tableName = 'seckill_goods';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn($this->tableName, 'sec_limit')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                //修改字段结构
                $table->integer('sec_limit')->unsigned()->default(0)->comment('秒杀限购数量')->change();
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
        // 还原字段类型
        if (Schema::hasColumn($this->tableName, 'sec_limit')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->boolean('sec_limit')->comment('秒杀限购数量')->change();
            });
        }
    }
}
