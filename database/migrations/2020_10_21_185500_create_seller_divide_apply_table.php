<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSellerDivideApplyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $name = 'seller_divide_apply';
        if (Schema::hasTable($name)) {
            return false;
        }
        Schema::create($name, function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->integer('ru_id')->unsigned()->default(0)->index('ru_id')->comment('入驻商家id');
            $table->tinyInteger('divide_channel')->unsigned()->default(0)->comment('分账渠道 ：1 微信收付通');
            $table->string('out_request_no')->default('')->comment('业务申请编号');
            $table->string('applyment_id')->default('')->comment('微信支付申请单号');
            $table->string('organization_type')->default('')->comment('主体类型');
            $table->text('business_license_info')->comment('营业执照/登记证书信息');
            $table->text('organization_cert_info')->comment('组织机构代码证信息');
            $table->string('id_doc_type')->default('')->comment('经营者/法人证件类型');
            $table->text('id_card_info')->comment('经营者/法人身份证信息');
            $table->text('id_doc_info')->comment('经营者/法人其他类型证件信息');
            $table->tinyInteger('need_account_info')->unsigned()->default(0)->comment('是否填写结算银行账户 0 否 1 是');
            $table->text('account_info')->comment('结算银行账户信息');
            $table->text('contact_info')->comment('超级管理员信息');
            $table->text('sales_scene_info')->comment('店铺信息');
            $table->string('merchant_shortname')->default('')->comment('商户简称');
            $table->string('qualifications')->default('')->comment('特殊资质');
            $table->string('business_addition_pics')->default('')->comment('补充材料');
            $table->string('business_addition_desc')->default('')->comment('补充说明');
            $table->string('applyment_state')->default('')->comment('申请状态');
            $table->string('applyment_state_desc')->default('')->comment('申请状态描述');
            $table->string('sign_url')->default('')->comment('签约链接');
            $table->text('account_validation')->comment('汇款账户验证信息');
            $table->text('audit_detail')->comment('驳回原因详情');
            $table->string('legal_validation_url')->default('')->comment('法人验证链接');
            $table->integer('apply_time')->unsigned()->default(0)->comment('申请时间');
            $table->integer('update_time')->unsigned()->default(0)->comment('更新时间');
        });

        $prefix = config('database.connections.mysql.prefix');
        DB::statement("ALTER TABLE `" . $prefix . "$name` comment '商家二级商户进件申请表'");
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seller_divide_apply');
    }

}
