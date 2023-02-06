<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCosConfigureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $name = 'cos_configure';
        if (Schema::hasTable($name)) {
            return false;
        }
        Schema::create($name, function (Blueprint $table) {
            $table->increments('id')->comment('自增ID号');
            $table->string('bucket')->comment('COS模块对象名称');
            $table->string('app_id')->comment('APP ID');
            $table->string('secret_id')->comment('SECRET ID');
            $table->string('secret_key')->comment('SECRET Key');
            $table->boolean('is_cname')->default(0)->comment('是否域名绑定');
            $table->string('endpoint')->comment('绑定域名地址');
            $table->string('regional', 100)->comment('OSS绑定区域');
            $table->string('port', 15)->comment('端口号');
            $table->boolean('is_use')->default(0)->index('is_use')->comment('是否启用（0否，1是）');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cos_configure');
    }
}
