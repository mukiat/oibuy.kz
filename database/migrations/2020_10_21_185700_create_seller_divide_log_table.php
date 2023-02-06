<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSellerDivideLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $name = 'seller_divide_log';
        if (Schema::hasTable($name)) {
            return false;
        }
        Schema::create($name, function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->integer('ru_id')->unsigned()->default(0)->index('ru_id')->comment('入驻商家id');
            $table->tinyInteger('divide_channel')->unsigned()->default(0)->comment('分账渠道 ：1 微信收付通');
            $table->integer('order_id')->unsigned()->default(0)->index('order_id')->comment('订单id(关联order_info表order_id)');
            $table->integer('bill_id')->unsigned()->default(0)->index('bill_id')->comment('账单id(关联seller_commission_bill表id)');
            $table->string('bill_out_order_no')->default('')->comment('商户账单号(以订单号为维度)');
            $table->string('transaction_id')->default('')->comment('微信支付订单号');
            $table->string('divide_order_id')->default('')->comment('微信分账单号');
            $table->integer('divide_amount')->unsigned()->default(0)->comment('分账金额(分账接收方) 单位：分');
            $table->string('divide_proportion', 20)->default('')->comment('分账比例(分账接收方)');
            $table->integer('should_amount')->unsigned()->default(0)->comment('商家分账金额 单位：分');
            $table->string('should_proportion', 20)->default('')->comment('商家分账比例');
            $table->string('status')->default('')->comment('分账单状态');
            $table->text('divide_trade_data')->comment('分账交易详情');
            $table->integer('add_time')->unsigned()->default(0)->comment('添加时间');
            $table->integer('update_time')->unsigned()->default(0)->comment('更新时间');
        });

        $prefix = config('database.connections.mysql.prefix');
        DB::statement("ALTER TABLE `" . $prefix . "$name` comment '分账交易记录表'");
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seller_divide_log');
    }

}
