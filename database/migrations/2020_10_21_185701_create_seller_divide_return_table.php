<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSellerDivideReturnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $name = 'seller_divide_return';
        if (Schema::hasTable($name)) {
            return false;
        }
        Schema::create($name, function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->integer('ru_id')->unsigned()->default(0)->index('ru_id')->comment('入驻商家id');
            $table->tinyInteger('divide_channel')->unsigned()->default(0)->comment('分账渠道 ：1 微信收付通');
            $table->integer('order_id')->unsigned()->default(0)->index('order_id')->comment('订单id(关联order_info表order_id)');
            $table->integer('bill_id')->unsigned()->default(0)->index('bill_id')->comment('账单id(关联seller_commission_bill表id)');
            $table->string('bill_out_order_no')->default('')->comment('商户分账单号(以订单号为维度)');
            $table->string('divide_order_id')->default('')->comment('微信分账单号');
            $table->string('bill_out_return_no')->default('')->comment('商户回退单号');
            $table->string('return_no')->default('')->comment('微信回退单号');
            $table->integer('amount')->unsigned()->default(0)->comment('回退金额 单位：分');
            $table->string('return_mchid')->default('')->comment('回退商户号');
            $table->string('result')->default('')->comment('回退结果');
            $table->text('return_trade_data')->comment('分账回退交易详情');
            $table->integer('add_time')->unsigned()->default(0)->comment('添加时间');
            $table->integer('update_time')->unsigned()->default(0)->comment('更新时间');
        });

        $prefix = config('database.connections.mysql.prefix');
        DB::statement("ALTER TABLE `" . $prefix . "$name` comment '分账回退交易记录表'");
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seller_divide_return');
    }

}
