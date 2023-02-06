<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateGoodsLabelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $name = 'goods_label';
        if (Schema::hasTable($name)) {
            return false;
        }

        Schema::create($name, function (Blueprint $table) {
            $table->bigIncrements('id')->comment('自增ID');
            $table->string('label_name')->default('')->comment('标签名称');
            $table->string('label_image')->default('')->comment('标签图片');
            $table->integer('sort')->default(50)->comment('标签排序');
            $table->tinyInteger('merchant_use')->unsigned()->default(0)->comment('商家可用：0 否， 1 是');
            $table->tinyInteger('status')->unsigned()->default(0)->comment('标签状态：0 关闭， 1 开启');
            $table->string('label_url')->default('')->comment('标签链接，填写可跳转到指定url');
            $table->integer('add_time')->default(0)->comment('添加时间');
        });

        $prefix = config('database.connections.mysql.prefix');
        DB::statement("ALTER TABLE `" . $prefix . "$name` comment '标签表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods_label');
    }
}
