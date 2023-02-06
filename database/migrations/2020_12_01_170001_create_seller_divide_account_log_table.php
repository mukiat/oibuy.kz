<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSellerDivideAccountLogTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $name = 'seller_divide_account_log';
        if (Schema::hasTable($name)) {
            return false;
        }
        Schema::create($name, function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->integer('ru_id')->unsigned()->default(0)->index('ru_id')->comment('入驻商家id');
            $table->string('shop_name', 50)->default('')->comment('店铺名称');
            $table->tinyInteger('divide_channel')->unsigned()->default(0)->comment('渠道 ：1 微信收付通');
            $table->string('out_request_no')->default('')->comment('商户提现单号');
            $table->tinyInteger('business_type')->unsigned()->default(0)->comment('业务类型 0 提现');
            $table->tinyInteger('audit')->unsigned()->default(0)->comment('审核状态： 0 未审核 1 已审核 2 拒绝');
            $table->integer('audit_time')->unsigned()->default(0)->comment('审核时间');
            $table->integer('amount')->unsigned()->default(0)->comment('交易金额（单位分）');
            $table->integer('balance_amount')->unsigned()->default(0)->comment('剩余金额（单位分）');
            $table->string('status')->default('')->comment('交易状态');
            $table->string('withdraw_id')->default('')->comment('微信支付提现单号');
            $table->string('reason')->default('')->comment('失败原因');
            $table->string('account_type')->default('')->comment('出款账户类型 BASIC：基本户 OPERATION：运营账户 FEES：手续费账户');
            $table->text('trans_data')->comment('资金交易详情');
            $table->integer('create_time')->unsigned()->default(0)->comment('添加时间');
            $table->integer('update_time')->unsigned()->default(0)->comment('修改时间');
        });

        $prefix = config('database.connections.mysql.prefix');
        DB::statement("ALTER TABLE `" . $prefix . "$name` comment '商家二级商户资金记录表'");
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seller_divide_account_log');
    }
}
