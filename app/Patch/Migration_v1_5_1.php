<?php

namespace App\Patch;

use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Migration_v1_5_1
{
    public function run()
    {
        $this->orderGoods();
        $this->orderInfo();

        if (file_exists(MOBILE_DRP)) {
            $this->upgradeTov1_4_6();

            $this->upgradeTov1_5_1();
        }

        $this->products();

        $this->seckillGoods();

        // 店铺评论开关
        $this->shop_can_comment();

        try {
            $this->shopConfig();
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    private function orderGoods()
    {
        $table = 'order_goods';
        if (!Schema::hasTable($table)) {
            return false;
        }

        if (!Schema::hasColumn($table, 'is_received')) {
            Schema::table($table, function (Blueprint $table) {
                $table->integer('is_received')->index('is_received')->default(0)->comment('是否退货退款：0 否, 1 是');
            });
        }

        if (!Schema::hasColumn($table, 'main_count')) {
            Schema::table($table, function (Blueprint $table) {
                $table->integer('main_count')->index('main_count')->default(0)->comment('是否主订单：0 否, 1 是');
            });
        }

        if (!Schema::hasColumn($table, 'is_comment')) {
            Schema::table($table, function (Blueprint $table) {
                $table->integer('is_comment')->index('is_comment')->default(0)->comment('是否评论：0 否, 1 是');
            });
        }
    }

    private function orderInfo()
    {
        $table = 'order_info';
        if (!Schema::hasTable($table)) {
            return false;
        }

        if (!Schema::hasColumn($table, 'is_drp')) {
            Schema::table($table, function (Blueprint $table) {
                $table->integer('is_drp')->index('is_drp')->default(0)->comment('是否有分销商品：0 否, 1 是');
            });
        }
    }

    public function products()
    {
        $tableName = 'products'; // 属性货品表

        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'product_cost_price')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->decimal('product_cost_price', 10, 2)->default(0)->after('product_price')->comment('属性成本价格');
                });
            }
        }

        $tableNameChange = 'products_changelog'; // 属性货品日志表

        if (Schema::hasTable($tableNameChange)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableNameChange, 'product_cost_price')) {
                Schema::table($tableNameChange, function (Blueprint $table) {
                    $table->decimal('product_cost_price', 10, 2)->default(0)->after('product_price')->comment('属性成本价格');
                });
            }
        }
    }

    /**
     * 升级 v1.4.6
     */
    protected function upgradeTov1_4_6()
    {
        // 更新分销权益卡购买指定商品 is_show
        DB::table('goods')->where('membership_card_id', '>', 0)->where('is_show', 0)->orderBy('goods_id')
            ->chunk(1000, function ($goods) {
                foreach ($goods as $val) {
                    DB::table('goods')
                        ->where('goods_id', $val->goods_id)
                        ->update(['is_show' => 1, 'is_on_sale' => 0]);
                }
            });

        // 同步更新默认分成时间 为发货日期起可退换货时间
        $default_settlement_time = DB::table('shop_config')->where('code', 'sign')->value('value');
        $default_settlement_time = empty($default_settlement_time) ? 1 : $default_settlement_time;
        DB::table('drp_config')->where('code', 'settlement_time')->where('value', '<', $default_settlement_time)->update(['value' => $default_settlement_time]);
    }

    protected function upgradeTov1_5_1()
    {
        // 分销商佣金记录表
        $this->change_drp_translog();

        // 分销权益卡订单表
        $this->change_drp_accountlog();

        // 支付日志表
        $this->change_pay_log();

        // 分销店铺表
        $this->add_drp_store();

        // 分销操作记录表
        $this->add_drp_action_log();
    }

    private function change_drp_translog()
    {
        $tableName = 'drp_transfer_log'; // 分销商佣金记录表

        if (!Schema::hasTable($tableName)) {
            return false;
        }

        // 判断字段是否存在添加
        if (!Schema::hasColumn($tableName, 'transfer_type')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedInteger('transfer_type')->default(0)->comment('账单类型: 0 提现, 1 分成');
            });
        }

        if (!Schema::hasColumn($tableName, 'separate_status')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedInteger('separate_status')->default(0)->comment('分成状态: 0 未分成, 1 已分成');
            });
        }

        if (!Schema::hasColumn($tableName, 'point')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedInteger('point')->default(0)->after('money')->comment('分成积分');
            });
        }

        if (!Schema::hasColumn($tableName, 'user_name')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('user_name')->default('')->after('user_id')->comment('用户名');
            });
        }

        if (!Schema::hasColumn($tableName, 'drp_log_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedInteger('drp_log_id')->default(0)->index()->comment('分成drp_log_id 关联 drp_log 表');
            });
        }
    }

    private function change_drp_accountlog()
    {
        $tableName = 'drp_account_log'; // 分销权益卡订单表

        if (!Schema::hasTable($tableName)) {
            return false;
        }

        // 判断字段是否存在添加
        if (!Schema::hasColumn($tableName, 'trade_sn')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('trade_sn', 100)->default('')->after('user_id')->comment('交易订单号');
            });
        }

        if (Schema::hasColumn($tableName, 'admin_user')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('admin_user');
            });
        }

        if (Schema::hasColumn($tableName, 'admin_note')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('admin_note');
            });
        }

        if (Schema::hasColumn($tableName, 'account_type')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('account_type');
            });
        }

        if (Schema::hasColumn($tableName, 'log_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('log_id');
            });
        }
    }

    private function change_pay_log()
    {
        $tableName = 'pay_log'; // 支付日志表

        if (!Schema::hasTable($tableName)) {
            return false;
        }

        // 判断字段是否存在添加
        if (!Schema::hasColumn($tableName, 'drp_account_log_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('drp_account_log_id')->unsigned()->default(0)->index()->comment('权益卡订单id,关联 drp_account_log 表');
            });
        }

        if (Schema::hasColumn($tableName, 'membership_card_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('membership_card_id');
            });
        }
    }

    protected function add_drp_store()
    {
        $name = 'drp_store';
        if (Schema::hasTable($name)) {
            return false;
        }

        Schema::create($name, function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->string('store_name')->default('')->comment('店铺名称');
            $table->string('store_desc')->default('')->comment('店铺简介');
            $table->unsignedInteger('drp_shop_id')->default(0)->index()->comment('分销商id,关联drp_shop表');
            $table->string('real_name')->default('')->comment('真实姓名');
            $table->unsignedInteger('user_id')->default(0)->index()->comment('会员id,关联users表');
            $table->string('background_img')->default('')->comment('店铺背景图');
            $table->tinyInteger('review_status')->default(0)->comment('审核状态：0 未审核，1 通过，2 拒绝');
            $table->integer('open_time')->unsigned()->default(0)->comment('审核开店时间');
            $table->tinyInteger('open_status')->default(0)->comment('开店状态：0 关闭，1 开启');
            $table->integer('apply_time')->unsigned()->default(0)->comment('申请时间');
            $table->integer('update_time')->unsigned()->default(0)->comment('更新时间');
            $table->unsignedInteger('sort')->default(50)->comment('排序');
        });

        $prefix = config('database.connections.mysql.prefix');
        DB::statement("ALTER TABLE `" . $prefix . "$name` comment '分销店铺表'");
    }

    protected function add_drp_action_log()
    {
        $name = 'drp_action_log'; // 分销操作记录表
        if (Schema::hasTable($name)) {
            return false;
        }

        Schema::create($name, function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->unsignedInteger('drp_store_id')->default(0)->index()->comment('分销店铺id,关联drp_store表');
            $table->unsignedInteger('drp_shop_id')->default(0)->index()->comment('分销商id,关联drp_shop表');
            $table->unsignedInteger('admin_id')->default(0)->index()->comment('管理员id,关联admin_user表');
            $table->string('log_user_name', 30)->default('')->comment('操作者名称');
            $table->string('log_content')->default('')->comment('操作内容');
            $table->integer('log_time')->unsigned()->default(0)->comment('操作时间');
            $table->string('log_type', 100)->default('')->comment('操作类型: check 审核、edit 编辑');
            $table->string('ip_address', 15)->default('')->comment('操作ip');
        });

        $prefix = config('database.connections.mysql.prefix');
        DB::statement("ALTER TABLE `" . $prefix . "$name` comment '分销操作记录表'");
    }

    public function seckillGoods()
    {
        $table_name = 'seckill_goods';
        if (Schema::hasColumn($table_name, 'sec_limit')) {
            Schema::table($table_name, function (Blueprint $table) {
                //修改字段结构
                $table->integer('sec_limit')->unsigned()->default(0)->comment('秒杀限购数量')->change();
            });
        }
    }

    /**
     * 更新版本
     * @throws Exception
     */
    private function shopConfig()
    {
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v1.5.1'
        ]);

        /**
         * 新增商品评论开关
         *
         * value：1-开启，0-关闭
         */
        $count = DB::table('shop_config')->where('code', 'shop_can_comment')->count();

        if (empty($count)) {
            $parent_id = DB::table('shop_config')->where('code', 'goods_name_length')->value('parent_id');
            $parent_id = !empty($parent_id) ? $parent_id : 0;

            DB::table('shop_config')->insert([
                'code' => 'shop_can_comment',
                'parent_id' => $parent_id,
                'type' => 'select',
                'store_range' => '1,0',
                'value' => '1',
                'shop_group' => 'goods'
            ]);
        }

        cache()->forget('shop_config');
    }

    /**
     * @return bool
     */
    private function shop_can_comment()
    {
        $tableName = 'seller_shopinfo';

        if (!Schema::hasTable($tableName)) {
            return false;
        }

        // 判断字段是否存在添加
        if (!Schema::hasColumn($tableName, 'shop_can_comment')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('shop_can_comment')->default(1)->comment('店铺是否可评论：0 否, 1 是');
            });
        }
    }
}
