<?php

namespace App\Patch;

use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;


class Migration_v2_2_2
{
    public function run()
    {
        try {
            $this->upgrade_db();
            $this->value_card();
            $this->order_goods();
            $this->order_return();
            $this->cart();
            $this->value_card_record();

            $this->open_api();

            $this->change_seller_divide_add_amount();
            $this->create_seller_divide_account_log();

            $this->goods_use_label();

            $this->add_admin_action();

            $this->add_upload_use_original_name();

            $this->shopConfig();
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    protected function upgrade_db()
    {
        if (Schema::hasTable('order_export_history') && !Schema::hasTable('export_history')) {
            Schema::rename('order_export_history', 'export_history');
        }

        if (!Schema::hasColumn('export_history', 'type')) {
            Schema::table('export_history', function (Blueprint $table) {
                $table->string('type')->comment('导出类型');
            });
        }
    }

    protected static function change_seller_divide_add_amount()
    {
        $tableName = 'seller_divide'; // 二级商户号表
        if (!Schema::hasTable($tableName)) {
            return false;
        }

        // 判断字段是否存在添加
        if (!Schema::hasColumn($tableName, 'available_amount')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('available_amount')->unsigned()->default(0)->comment('可用余额（单位分）');
            });
        }
        if (!Schema::hasColumn($tableName, 'pending_amount')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('pending_amount')->unsigned()->default(0)->comment('不可用余额（单位分）');
            });
        }
    }

    protected static function create_seller_divide_account_log()
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

    protected static function add_admin_action()
    {
        // 二级商户资金管理权限
        $count = DB::table('admin_action')->where('action_code', 'seller_divide_account')->count();
        if (empty($count)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'merchants')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;
            // 默认数据
            $other = [
                'parent_id' => $parent_id,
                'action_code' => 'seller_divide_account',
                'seller_show' => 1,
            ];
            DB::table('admin_action')->insert($other);
        }
    }

    private function value_card()
    {
        $table_name = 'value_card';
        if (!Schema::hasTable($table_name)) {
            return false;
        }

        if (!Schema::hasColumn($table_name, 'vc_dis')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->decimal('vc_dis', 10, 2)->default(0)->unsigned()->after('card_money')->comment('储值卡折扣');
            });
        }

        // 判断字段是否存在添加
        if (Schema::hasColumn($table_name, 'vc_value')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->decimal('vc_value', 10, 2)->default(0)->change();
            });
        }
    }

    private function order_goods()
    {
        $table_name = 'order_goods';
        if (!Schema::hasTable($table_name)) {
            return false;
        }

        if (!Schema::hasColumn($table_name, 'goods_value_card')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->decimal('goods_value_card', 10, 2)->default(0)->comment('储值卡均摊金额');
            });
        }

        if (!Schema::hasColumn($table_name, 'value_card_discount')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->decimal('value_card_discount', 10, 2)->default(0)->comment('储值卡均摊折扣金额');
            });
        }
    }

    private function order_return()
    {
        $table_name = 'order_return';
        if (!Schema::hasTable($table_name)) {
            return false;
        }

        if (!Schema::hasColumn($table_name, 'goods_value_card')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->decimal('goods_value_card', 10, 2)->default(0)->unsigned()->comment('储值卡均摊金额');
            });
        }

        if (!Schema::hasColumn($table_name, 'value_card_discount')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->decimal('value_card_discount', 10, 2)->default(0)->unsigned()->comment('储值卡均摊折扣金额');
            });
        }

        if (!Schema::hasColumn($table_name, 'actual_value_card')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->decimal('actual_value_card', 10, 2)->default(0)->after('actual_return')->unsigned()->comment('实退储值卡金额');
            });
        }

        if (!Schema::hasColumn($table_name, 'actual_integral_money')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->decimal('actual_integral_money', 10, 2)->default(0)->unsigned()->comment('实退积分金额');
            });
        }
    }

    public function cart()
    {
        $table_name = 'cart';
        if (!Schema::hasTable($table_name)) {
            return false;
        }

        if (!Schema::hasColumn($table_name, 'goods_coupons')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->decimal('goods_coupons', 10, 2)->default(0)->unsigned()->comment('优惠券均摊商品');
            });
        }

        if (!Schema::hasColumn($table_name, 'goods_bonus')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->decimal('goods_bonus', 10, 2)->default(0)->unsigned()->comment('优惠券均摊商品');
            });
        }
    }

    private function value_card_record(){

        $table_name = 'value_card_record';
        if (!Schema::hasTable($table_name)) {
            return false;
        }

        if (!Schema::hasColumn($table_name, 'ret_id')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->integer('ret_id')->default(0)->unsigned()->comment('单品退货单ID');
            });
        }
    }

    public function open_api()
    {
        $table_name = 'open_api';
        if (!Schema::hasTable($table_name)) {
            return false;
        }

        if (!Schema::hasColumn($table_name, 'ru_id')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->integer('ru_id')->default(0)->unsigned()->comment('商家ID');
            });
        }
    }

    private function goods_use_label(){

        $table_name = 'goods_use_label';
        if (Schema::hasTable($table_name)) {
            return false;
        }

        Schema::create('goods_use_label', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('自增ID');
            $table->string('label_id')->default(0)->comment('标签ID');
            $table->string('goods_id')->default(0)->comment('商品ID');
            $table->integer('add_time')->default(0)->comment('添加时间');
        });
    }

    /**
     * 更新版本
     * @throws Exception
     */
    private function shopConfig()
    {
        ShopConfig::where('code', 'user_helpart')->update([
            'type' => 'hidden'
        ]);

        ShopConfig::where('code', 'cart_confirm')->update([
            'type' => 'hidden'
        ]);

        ShopConfig::where('code', 'cron_method')->update([
            'type' => 'hidden'
        ]);

        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v2.2.2'
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

    /**
     * 商品相册图片是否保留原名称
     *
     * @throws \Exception
     */
    private function add_upload_use_original_name()
    {
        $parent_id = ShopConfig::where('code', 'goods_picture')->where('shop_group', 'goods')->value('id'); // 商品图片设置

        if ($parent_id > 0) {

            $result = ShopConfig::where('code', 'upload_use_original_name')->count();
            if (empty($result)) {
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'upload_use_original_name',
                        'value' => 0,
                        'type' => 'select',
                        'store_range' => '1,0',
                        'sort_order' => 1,
                        'shop_group' => 'goods',
                    ]
                ];
                ShopConfig::insert($rows);
            }
        }
    }
}
