<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeGoodsLabelAddTypeTable extends Migration
{
    /**
     * 运行数据库迁移
     *
     * @return void
     */
    public function up()
    {
        $tableName = 'goods_label';
        if (!Schema::hasTable($tableName)) {
            return false;
        }

        // 判断字段是否存在添加
        if (!Schema::hasColumn($tableName, 'type')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->tinyInteger('type')->unsigned()->default(0)->comment('标签类型：0 普通,1 悬浮');
            });
        }
        if (!Schema::hasColumn($tableName, 'start_time')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('start_time')->unsigned()->default(0)->comment('标签显示开始时间');
            });
        }
        if (!Schema::hasColumn($tableName, 'end_time')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('end_time')->unsigned()->default(0)->comment('标签显示结束时间');
            });
        }
        if (!Schema::hasColumn($tableName, 'bind_goods_number')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('bind_goods_number')->unsigned()->default(0)->comment('标签绑定商品数量');
            });
        }
        
    }

    /**
     * 回滚数据库迁移
     *
     * @return void
     */
    public function down()
    {
        // 删除字段
        $tableName = 'goods_label';
        if (!Schema::hasTable($tableName)) {
            return false;
        }
        
        if (Schema::hasColumn($tableName, 'type')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
        if (Schema::hasColumn($tableName, 'start_time')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('start_time');
            });
        }
        if (Schema::hasColumn($tableName, 'end_time')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('end_time');
            });
        }
        if (Schema::hasColumn($tableName, 'bind_goods_number')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('bind_goods_number');
            });
        }
    }
}
