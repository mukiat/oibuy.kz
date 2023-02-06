<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeSellerDivideAddReceiversTable extends Migration
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
        if (!Schema::hasColumn($tableName, 'receivers_add')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->tinyInteger('receivers_add')->unsigned()->default(0)->index()->comment('是否已添加分账接收方：0 否， 1 是');
            });
        }
        if (!Schema::hasColumn($tableName, 'merchant_name')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('merchant_name')->default('')->comment('分账接收方的名称 商户全称');
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

        if (Schema::hasColumn($tableName, 'receivers_add')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('receivers_add');
            });
        }
        if (Schema::hasColumn($tableName, 'merchant_name')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('merchant_name');
            });
        }
    }
}
