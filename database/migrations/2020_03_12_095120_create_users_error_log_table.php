<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUsersErrorLogTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $name = 'users_error_log';
        if (Schema::hasTable($name)) {
            return false;
        }

        Schema::create('users_error_log', function (Blueprint $table) {
            $table->increments('log_id')->comment('自增ID');
            $table->string('user_name', 30)->default('')->comment('姓名');
            $table->integer('user_id')->unsigned()->default(0)->index('user_id')->comment('用户ID');
            $table->integer('admin_id')->unsigned()->default(0)->index('admin_id')->comment('管理员id');
            $table->integer('store_user_id')->unsigned()->default(0)->index('store_user_id')->comment('门店会员id');
            $table->integer('create_time')->default(0)->comment('记录时间');
            $table->string('ip_address', 15)->default('')->comment('ip地址');
            $table->string('operation_note')->default('')->comment('操作备注');
            $table->string('user_agent')->default('')->comment('来源userAgent');
            $table->tinyInteger('expired')->unsigned()->default(0)->comment('是否过登录锁定期：0未过期 1 过期的');
        });

        $prefix = config('database.connections.mysql.prefix');
        DB::statement("ALTER TABLE `" . $prefix . "$name` comment '账号登录失败日志'");
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users_error_log');
    }
}
