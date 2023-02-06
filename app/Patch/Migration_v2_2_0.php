<?php

namespace App\Patch;

use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Migration_v2_2_0
{
    public function run()
    {
        try {
            $this->keyword_list();
            $this->goods_keyword();

            $this->seller_divide();
            $this->seller_divide_apply();
            $this->seller_divide_log();
            $this->seller_divide_return();
            $this->change_order_add_divide_channel();
            $this->change_seller_divide();

            $this->admin_action();

            $this->shopConfig();
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    protected static function seller_divide()
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

    protected static function seller_divide_apply()
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

    protected static function seller_divide_log()
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

    protected static function seller_divide_return()
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

    protected static function change_order_add_divide_channel()
    {
        // 订单表
        $tableName = 'order_info';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->integer('divide_channel')->unsigned()->default(0)->index()->comment('分账渠道：0 无，1 微信收付通');
                });
            }
        }

        $tableName = 'order_return';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->integer('divide_channel')->unsigned()->default(0)->index()->comment('分账渠道：0 无，1 微信收付通');
                });
            }
        }

        // 支付日志表
        $tableName = 'pay_log';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->integer('divide_channel')->unsigned()->default(0)->index()->comment('分账渠道：0 无，1 微信收付通');
                });
            }
        }

        // 商家订单账单表
        $tableName = 'seller_bill_order';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->integer('divide_channel')->unsigned()->default(0)->index()->comment('分账渠道：0 无，1 微信收付通');
                });
            }
        }
        $tableName = 'seller_commission_bill';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->integer('divide_channel')->unsigned()->default(0)->index()->comment('分账渠道：0 无，1 微信收付通');
                });
            }
        }

        $tableName = 'seller_negative_order';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->integer('divide_channel')->unsigned()->default(0)->index()->comment('分账渠道：0 无，1 微信收付通');
                });
            }
        }
        $tableName = 'seller_negative_bill';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->integer('divide_channel')->unsigned()->default(0)->index()->comment('分账渠道：0 无，1 微信收付通');
                });
            }
        }

        $tableName = 'seller_account_log';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->integer('divide_channel')->unsigned()->default(0)->index()->comment('分账渠道：0 无，1 微信收付通');
                });
            }
        }
        $tableName = 'merchants_account_log';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->integer('divide_channel')->unsigned()->default(0)->index()->comment('分账渠道：0 无，1 微信收付通');
                });
            }
        }
    }


    protected static function change_seller_divide()
    {
        $tableName = 'seller_divide'; // 二级商户号表
        if (!Schema::hasTable($tableName)) {
            return false;
        }

        // 判断字段是否存在添加
        if (!Schema::hasColumn($tableName, 'receivers_add')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->tinyInteger('receivers_add')->unsigned()->default(0)->index()->comment('是否已添加分账接收方：0 否， 1 是');
            });
        }
        if (!Schema::hasColumn($tableName, 'merchant_name')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('merchant_name')->default('')->comment('分账接收方的名称 商户全称');
            });
        }
    }

    protected static function keyword_list()
    {
        $tableName = 'keyword_list';
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('cat_id')->default(0)->index('cat_id')->comment('关键词分类ID');
                $table->string('name', 120)->default('')->comment('关键词名称');
                $table->integer('ru_id')->default(0)->index('ru_id')->comment('商家ID');
                $table->integer('update_time')->default(0)->comment('更新时间');
                $table->integer('add_time')->default(0)->comment('添加时间');
            });
        }
    }

    protected static function goods_keyword()
    {
        if (Schema::hasTable('goods_keyword')) {
            return false;
        }

        Schema::create('goods_keyword', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('keyword_id')->default(0)->index('keyword_id')->comment('关键词ID');
            $table->integer('goods_id')->default(0)->index('goods_id')->comment('商品ID');
            $table->integer('add_time')->default(0)->comment('添加时间');
        });
    }

    protected static function admin_action()
    {
        /* 添加关键词权限 */
        $count = DB::table('admin_action')->where('action_code', 'goods_keyword')->count();
        if (empty($count)) {
            $action_id = DB::table('admin_action')->where('action_code', 'goods')->value('action_id');
            $action_id = $action_id ? $action_id : 0;

            $action = [
                'parent_id' => $action_id,
                'action_code' => 'goods_keyword',
                'seller_show' => 1
            ];
            DB::table('admin_action')->insert($action);
        }

        /* 订单导出权限 */
        $count = DB::table('admin_action')->where('action_code', 'order_export')->count();
        if (empty($count)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'order_manage')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;
            DB::table('admin_action')->insert([
                'parent_id' => $parent_id,
                'action_code' => 'order_export',
                'seller_show' => 1
            ]);
        }

        // 结算工具菜单权限
        $count = DB::table('admin_action')->where('action_code', 'seller_divide')->count();
        if (empty($count)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'merchants')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;
            // 默认数据
            $other = [
                'parent_id' => $parent_id,
                'action_code' => 'seller_divide',
                'seller_show' => 1
            ];
            DB::table('admin_action')->insert($other);
        }
        // 子商户进件申请 权限
        $count = DB::table('admin_action')->where('action_code', 'seller_divide_apply')->count();
        if (empty($count)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'merchants')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;
            // 默认数据
            $other = [
                'parent_id' => $parent_id,
                'action_code' => 'seller_divide_apply',
                'seller_show' => 1
            ];
            DB::table('admin_action')->insert($other);
        }

        // 订单分账 权限
        $count = DB::table('admin_action')->where('action_code', 'order_divide')->count();
        if (empty($count)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'merchants')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;
            // 默认数据
            $other = [
                'parent_id' => $parent_id,
                'action_code' => 'order_divide',
                'seller_show' => 0
            ];
            DB::table('admin_action')->insert($other);
        }

        /* 支付单权限 */
        $count = DB::table('admin_action')->where('action_code', 'pay_log')->count();
        if (empty($count)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'order_manage')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;
            // 默认数据
            $other = [
                'parent_id' => $parent_id,
                'action_code' => 'pay_log',
                'seller_show' => 1
            ];
            DB::table('admin_action')->insert($other);
        }
    }

    /**
     * 更新版本
     * @throws Exception
     */
    private function shopConfig()
    {
        /**
         * 新增商品价格/库存类型
         *
         * value：1-开启，0-关闭
         */
        $count = ShopConfig::where('code', 'goods_stock_model')->count();

        if (empty($count)) {
            $parent_id = ShopConfig::where('code', 'goods_base')->value('id');
            $parent_id = !empty($parent_id) ? $parent_id : 0;

            ShopConfig::insert([
                'code' => 'goods_stock_model',
                'parent_id' => $parent_id,
                'type' => 'select',
                'store_range' => '1,0',
                'value' => '0',
                'shop_group' => 'goods'
            ]);
        }

        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v2.2.0'
        ]);

        $this->clearCache();
    }

    /**
     * @throws Exception
     */
    private function clearCache()
    {
        cache()->flush();
    }
}