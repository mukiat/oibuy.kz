<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAppClientTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $name = 'app_client';
        if (Schema::hasTable($name)) {
            return false;
        }

        Schema::create('app_client', function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->string('name', 50)->default('')->comment('应用名称');
            $table->string('appid', 50)->default('')->comment('APPID');
            $table->integer('create_time')->default(0)->comment('添加时间');
        });

        $prefix = config('database.connections.mysql.prefix');
        DB::statement("ALTER TABLE `" . $prefix . "$name` comment 'app客户端'");
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('app_client');
    }
}
