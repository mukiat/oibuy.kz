<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeStoreUserPasswordTable extends Migration
{
    protected $tableName = 'store_user';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tableName)) {
            return false;
        }

        if (Schema::hasColumn($this->tableName, 'stores_pwd')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->string('stores_pwd', 255)->change();
            });
        }

        if (!Schema::hasColumn($this->tableName, 'last_login')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->integer('last_login')->unsigned()->default(0)->comment('最后登录时间');
            });
        }

        if (!Schema::hasColumn($this->tableName, 'last_ip')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->string('last_ip', 15)->default('')->comment('最后一次登录ip');
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
        if (!Schema::hasTable($this->tableName)) {
            return false;
        }

        if (Schema::hasColumn($this->tableName, 'stores_pwd')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->string('stores_pwd', 32)->change();
            });
        }

        // 删除字段
        if (Schema::hasColumn($this->tableName, 'last_login')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->dropColumn('last_login');
            });
        }
        if (Schema::hasColumn($this->tableName, 'last_ip')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->dropColumn('last_ip');
            });
        }
    }
}
