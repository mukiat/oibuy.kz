<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAppClientProductTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $name = 'app_client_product';
        if (Schema::hasTable($name)) {
            return false;
        }

        Schema::create('app_client_product', function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->integer('client_id')->unsigned()->comment('客户端ID');
            $table->string('version_id', 30)->default('')->comment('版本ID');
            $table->text('update_desc')->comment('更新描述');
            $table->string('download_url')->default('')->comment('下载地址');
            $table->tinyInteger('is_show')->unsigned()->default(1)->comment('是否显示');
            $table->string('update_time')->default('')->comment('更新时间');
            $table->string('create_time')->default('')->comment('添加时间');
        });

        $prefix = config('database.connections.mysql.prefix');
        DB::statement("ALTER TABLE `" . $prefix . "$name` comment 'app客户端产品'");
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('app_client_product');
    }
}
