<?php

namespace App\Patch;

use App\Events\RunOtherModulesSeederEvent;
use App\Models\AdminAction;
use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Migration_v2_7_0
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
        $this->admin_action();
        $this->article();
        $this->change_admin_id();
    }

    public function article()
    {
        $name = "article";
        if (Schema::hasTable($name)) {
            if (!Schema::hasColumn($name, 'author_email')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->string('author_email', 60)->default('')->comment("作者邮箱");
                });
            }
        }
    }

    public function change_admin_id()
    {
        if (Schema::hasTable('package_goods')) {
            if (Schema::hasColumn('package_goods', 'admin_id')) {
                Schema::table('package_goods', function (Blueprint $table) {
                    $table->integer('admin_id')->unsigned()->change();
                });
            }
        }

        if (Schema::hasTable('admin_message')) {
            if (Schema::hasColumn('admin_message', 'sender_id')) {
                Schema::table('admin_message', function (Blueprint $table) {
                    $table->integer('sender_id')->unsigned()->change();
                });
            }
            if (Schema::hasColumn('admin_message', 'receiver_id')) {
                Schema::table('admin_message', function (Blueprint $table) {
                    $table->integer('receiver_id')->unsigned()->change();
                });
            }
        }

        if (Schema::hasTable('goods_article')) {
            if (Schema::hasColumn('goods_article', 'admin_id')) {
                Schema::table('goods_article', function (Blueprint $table) {
                    $table->integer('admin_id')->unsigned()->change();
                });
            }
        }

        if (Schema::hasTable('group_goods')) {
            if (Schema::hasColumn('group_goods', 'admin_id')) {
                Schema::table('group_goods', function (Blueprint $table) {
                    $table->integer('admin_id')->unsigned()->change();
                });
            }
            if (Schema::hasColumn('group_goods', 'group_id')) {
                Schema::table('group_goods', function (Blueprint $table) {
                    $table->integer('group_id')->unsigned()->change();
                });
            }
        }

        if (Schema::hasTable('link_goods')) {
            if (Schema::hasColumn('link_goods', 'admin_id')) {
                Schema::table('link_goods', function (Blueprint $table) {
                    $table->integer('admin_id')->unsigned()->change();
                });
            }
        }
    }

    public function admin_action()
    {
        $name = "admin_action";
        // 修改 seller_show 默认值
        if (Schema::hasTable($name)) {
            if (Schema::hasColumn($name, 'seller_show')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->boolean('seller_show')->default(0)->comment("商家可使用的权限 1，是；0，否")->change();
                });
            }
        }

        if (CROSS_BORDER === true) { // 跨境多商户
            DB::table($name)->where('action_code', 'country_manage')->where('seller_show', 1)->update(['seller_show' => 0]);
            DB::table($name)->where('action_code', 'cross_warehouse_manage')->where('seller_show', 1)->update(['seller_show' => 0]);
        }

        if (file_exists(WXAPP_MEDIA)) { // 小程序视频号推广员管理
            $action_id = AdminAction::where('action_code', 'media_promoter_manage')->count('action_id');
            if (empty($action_id)) {
                $parent_id = AdminAction::where('action_code', 'wxapp_media')->value('action_id');
                $parent_id = $parent_id ? $parent_id : 0;

                AdminAction::insert([
                    'parent_id' => $parent_id,
                    'action_code' => 'media_promoter_manage',
                    'seller_show' => 0
                ]);
            }


            $tableName = 'wxapp_order_media_call_back';
            if (Schema::hasTable($tableName)) {
                //修改字段结构
                if (Schema::hasColumn($tableName, 'prepay_id')) {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->string('prepay_id')->default('')->comment('微信支付prepay_id')->change();
                    });
                }

                if (Schema::hasColumn($tableName, 'wechat_order_id')) {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->string('wechat_order_id')->default('')->comment('微信侧订单ID')->change();
                    });
                }
            }

            $tableName = 'wxapp_shop_brand';
            if (Schema::hasTable($tableName)) {
                //修改字段结构
                if (Schema::hasColumn($tableName, 'audit_id')) {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->string('audit_id')->default('')->comment('审核单id')->change();
                    });
                }
            }
        }
    }

    private function seed()
    {
        if (file_exists(WXAPP_MEDIA_PROMOTER)) { // 小程序视频号推广员管理

            $id = ShopConfig::where('code', 'media_promoter_day')->count('id');

            if (empty($id)) {

                $parent_id = ShopConfig::where('code', 'wxapp_shop_config')->value('id');
                $parent_id = $parent_id ? $parent_id : 0;

                ShopConfig::query()->insert([
                    'parent_id' => $parent_id,
                    'code' => 'media_promoter_day',
                    'type' => 'text',
                    'shop_group' => 'wxapp_shop_config'
                ]);
            }
        }

        // 版本释放之前更新版本
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v2.7.0'
        ]);
    }

    private function clean()
    {
        cache()->flush();
    }
}
