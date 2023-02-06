<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeSellerDivideAddAmountTable extends Migration
{
    /**
     * 运行数据库迁移
     *
     * @return void
     */
    public function up()
    {
        $tableName = 'seller_divide'; // 二级商户号表
        if (!Schema::hasTable($tableName)) {
            return false;
        }

        // 判断字段是否存在添加
        if (!Schema::hasColumn($tableName, 'available_amount')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('available_amount')->unsigned()->default(0)->comment('可用余额（单位分）');
            });
        }
        if (!Schema::hasColumn($tableName, 'pending_amount')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('pending_amount')->unsigned()->default(0)->comment('不可用余额（单位分）');
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
        $tableName = 'seller_divide'; // 二级商户号表

        if (Schema::hasColumn($tableName, 'available_amount')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('available_amount');
            });
        }
        if (Schema::hasColumn($tableName, 'pending_amount')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('pending_amount');
            });
        }
    }
}
