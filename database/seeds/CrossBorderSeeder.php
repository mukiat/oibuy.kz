<?php

use Illuminate\Database\Seeder;
use App\Models\ShopConfig;
use App\Models\AdminAction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CrossBorderSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 是否开启实名认证验证
        $this->identityAuthStatus();

        $this->country();
        $this->cross_warehouse();
        $this->admin_action();

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

        $name = 'seller_shopinfo';
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

    /**
     * 初始商品分类发票税率
     * @param int $cat_id
     * @param string $rate
     */
    protected function updateCategoryRate($cat_id = 0, $rate = '')
    {
        if ($cat_id > 0) {
            // 当前分类、子级分类
            DB::table('category')->where('cat_id', $cat_id)->orWhere('parent_id', $cat_id)->update(['rate' => $rate]);
        } else {
            DB::table('category')->update(['rate' => $rate]);
        }
    }

    /**
     * 是否开启实名认证验证
     */
    protected function identityAuthStatus()
    {
        $result = DB::table('shop_config')->where('code', 'identity_auth_status')->count();
        if (empty($result)) {
            $parent_id = DB::table('shop_config')->where('code', 'extend_basic')->value('id');
            $parent_id = !empty($parent_id) ? $parent_id : 0;

            // 默认数据
            $rows = [
                [
                    'parent_id' => $parent_id,
                    'code' => 'identity_auth_status',
                    'value' => 0,
                    'type' => 'hidden',
                    'shop_group' => 'identity'
                ]
            ];
            DB::table('shop_config')->insert($rows);
        }

        $result = DB::table('shop_config')->where('code', 'identity_auth_provider')->count();
        if (empty($result)) {
            $parent_id = DB::table('shop_config')->where('code', 'extend_basic')->value('id');
            $parent_id = !empty($parent_id) ? $parent_id : 0;

            // 默认数据
            $rows = [
                [
                    'parent_id' => $parent_id,
                    'code' => 'identity_auth_provider',
                    'value' => 2,
                    'type' => 'hidden',
                    'shop_group' => 'identity'
                ]
            ];
            DB::table('shop_config')->insert($rows);
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

        DB::table('admin_action')->where('action_code', 'country_manage')->where('seller_show', 1)->update(['seller_show' => 0]);
        DB::table('admin_action')->where('action_code', 'cross_warehouse_manage')->where('seller_show', 1)->update(['seller_show' => 0]);

        // 实名认证配置权限
        $count = DB::table('admin_action')->where('action_code', 'identity_auth_config')->count();
        if (empty($count)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'third_party_service')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;
            DB::table('admin_action')->insert([
                'parent_id' => $parent_id,
                'action_code' => 'identity_auth_config',
                'seller_show' => 0
            ]);
        }
    }
}
