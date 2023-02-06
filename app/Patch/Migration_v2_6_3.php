<?php

namespace App\Patch;

use App\Events\RunOtherModulesSeederEvent;
use App\Models\AdminAction;
use App\Models\MerchantsStepsFields;
use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Migration_v2_6_3
{
    public function run()
    {
        try {
            $this->migration();

            // 执行其他模块seed
            event(new RunOtherModulesSeederEvent());

            $this->seed();
            $this->clean();
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    private function migration()
    {
        if (CROSS_BORDER === true) { // 跨境多商户
            $this->country();
            $this->cross_warehouse();
        }

        $this->merchantsStepsFields();
        $this->home_templates();
        $this->topic();
        $this->seller_shopinfo();
    }

    private function merchantsStepsFields()
    {
        MerchantsStepsFields::where('source', '0.00')->update([
            'source' => ''
        ]);
    }

    /**
     * 首页可视化
     *
     * @return bool
     */
    public function home_templates()
    {
        $name = 'home_templates';
        if (!Schema::hasTable($name)) {
            return false;
        }

        if (!Schema::hasColumn($name, 'update_time')) {
            Schema::table($name, function (Blueprint $table) {
                $table->integer('update_time')->default(0)->comment("更新时间");
            });
        }
    }

    /**
     * 专题可视化
     *
     * @return bool
     */
    public function topic()
    {
        $name = 'topic';
        if (!Schema::hasTable($name)) {
            return false;
        }

        if (!Schema::hasColumn($name, 'theme_update_time')) {
            Schema::table($name, function (Blueprint $table) {
                $table->integer('theme_update_time')->default(0)->comment("可视化更新时间");
            });
        }
    }

    /**
     * 店铺可视化
     *
     * @return bool
     */
    public function seller_shopinfo()
    {
        $name = 'seller_shopinfo';
        if (!Schema::hasTable($name)) {
            return false;
        }

        if (!Schema::hasColumn($name, 'seller_templates_time')) {
            Schema::table($name, function (Blueprint $table) {
                $table->integer('seller_templates_time')->default(0)->after('seller_templates')->comment("可视化更新时间");
            });
        }

        if (CROSS_BORDER === true) { // 跨境多商户
            if (!Schema::hasColumn($name, 'cross_country_id')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->integer('cross_country_id')->default(0)->comment('跨境国家ID');
                });
            }

            if (!Schema::hasColumn($name, 'cross_warehouse_id')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->integer('cross_warehouse_id')->default(0)->comment('跨境仓库ID');
                });
            }
        }
    }

    public function country()
    {
        $name = 'country';
        if (Schema::hasTable($name)) {
            return false;
        }

        Schema::create($name, function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->string('country_name')->default('')->comment('国家名称');
            $table->string('country_icon')->default('')->comment('国家旗帜');
            $table->integer('add_time')->default(0)->comment('添加时间');
        });
    }

    public function cross_warehouse()
    {
        $name = 'cross_warehouse';
        if (Schema::hasTable($name)) {
            return false;
        }

        Schema::create($name, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('')->comment('跨境仓库名称');
            $table->integer('add_time')->default(0)->comment('添加时间');
        });
    }

    public function admin_action()
    {
        $action_id = AdminAction::where('action_code', 'admin_message')->count('action_id');

        if (empty($action_id)) {
            $parent_id = AdminAction::where('action_code', 'priv_manage')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;

            AdminAction::insert([
                'parent_id' => $parent_id,
                'action_code' => 'admin_message',
                'seller_show' => 1
            ]);
        }

        if (CROSS_BORDER === true) { // 跨境多商户
            $action_id = AdminAction::where('action_code', 'country_manage')->count('action_id');

            if (empty($action_id)) {
                $parent_id = AdminAction::where('action_code', 'sys_manage')->value('action_id');
                $parent_id = $parent_id ? $parent_id : 0;

                AdminAction::insert([
                    'parent_id' => $parent_id,
                    'action_code' => 'country_manage'
                ]);
            }

            $action_id = AdminAction::where('action_code', 'cross_warehouse_manage')->count('action_id');

            if (empty($action_id)) {
                $parent_id = AdminAction::where('action_code', 'sys_manage')->value('action_id');
                $parent_id = $parent_id ? $parent_id : 0;

                AdminAction::insert([
                    'parent_id' => $parent_id,
                    'action_code' => 'cross_warehouse_manage'
                ]);
            }
        }
    }

    private function seed()
    {

        $this->admin_action();

        $count = ShopConfig::where('code', 'seller_step_email')->count();
        if (empty($count)) {

            $parent_id = ShopConfig::where('code', 'basic')->value('id');
            $parent_id = $parent_id ? $parent_id : 0;

            ShopConfig::insert([
                'parent_id' => $parent_id,
                'code' => 'seller_step_email',
                'type' => 'select',
                'store_range' => '0,1',
                'value' => 1,
                'shop_group' => 'seller'
            ]);
        }

        if (file_exists(MOBILE_WXAPP)) {
            $count = ShopConfig::where('code', 'wxapp_chat')->count('id');
            $count = $count ? $count : 0;

            if (empty($count)) {
                $customer_service_id = ShopConfig::where('code', 'system_customer_service')->value('id');
                $customer_service_id = $customer_service_id ? $customer_service_id : 0;

                // 增加客服类型
                $typeRows = [
                    'parent_id' => $customer_service_id,
                    'code' => 'wxapp_chat',
                    'value' => 0,
                    'type' => 'select',
                    'store_range' => '0,1',
                    'sort_order' => 1,
                    'shop_group' => ''
                ];
                ShopConfig::insertGetId($typeRows);
            }

            ShopConfig::where('code', 'customer_service_type')->update([
                'sort_order' => 2
            ]);

            ShopConfig::where('code', 'qq_name')->update([
                'sort_order' => 3
            ]);

            ShopConfig::where('code', 'qq')->update([
                'sort_order' => 4
            ]);

            ShopConfig::where('code', 'service_url')->update([
                'sort_order' => 5
            ]);
        }

        if (CROSS_BORDER === true) { // 跨境多商户
            $count = ShopConfig::where('code', 'cross_source')->count();
            if (empty($count)) {

                $parent_id = ShopConfig::where('code', 'shop_info')->value('id');
                $parent_id = $parent_id ? $parent_id : 0;

                ShopConfig::insert([
                    'parent_id' => $parent_id,
                    'code' => 'cross_source',
                    'type' => 'text',
                    'store_range' => '',
                    'value' => '国内仓库,自贸区,海外直邮'
                ]);
            }
        }

        // 版本释放之前更新版本
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v2.6.3'
        ]);
    }

    private function clean()
    {
        cache()->flush();
    }
}
