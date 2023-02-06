<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSellerDivideTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $name = 'seller_divide';
        if (Schema::hasTable($name)) {
            return false;
        }
        Schema::create($name, function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->integer('ru_id')->unsigned()->default(0)->index('ru_id')->comment('入驻商家id');
            $table->string('shop_name', 50)->default('')->comment('店铺名称');
            $table->string('sub_mchid', 100)->default('')->comment('分账二级商户号');
            $table->string('sub_key')->default('')->comment('分账二级商户密钥');
            $table->tinyInteger('divide_channel')->unsigned()->default(0)->comment('分账渠道 ：1 微信收付通');
            $table->tinyInteger('add_way')->unsigned()->default(0)->comment('添加方式：0 手动绑定 1 进件接口');
            $table->integer('add_time')->unsigned()->default(0)->comment('添加时间');
            $table->integer('update_time')->unsigned()->default(0)->comment('修改时间');
        });

        $prefix = config('database.connections.mysql.prefix');
        DB::statement("ALTER TABLE `" . $prefix . "$name` comment '商家二级商户表'");
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seller_divide');
    }
}
