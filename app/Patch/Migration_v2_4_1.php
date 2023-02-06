<?php

namespace App\Patch;

use App\Models\AdminAction;
use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Migration_v2_4_1
{
    public function run()
{
    try {

        $this->migration();
        $this->seed();
        $this->clean();
    } catch (Exception $e) {
        dd($e->getMessage());
    }
}

    /**
     * 数据库迁移
     * @return mixed|void
     */
    public function migration()
    {
        $this->addPK();
        //添加提现类型
        $this->add_process_type();
        // 添加关联商品排序
        $this->add_link_goods_sort();
        // 添加货品重量
        $this->add_sku_weight();

        $this->cart();
        $this->order_goods();
        $this->order_return();

        if (file_exists(MOBILE_DRP)) {
            $this->change_order_goods_add_drp_goods_price();
        }
    }

    private function cart()
    {
        $name = 'cart';
        if (!Schema::hasTable($name)) {
            return false;
        }

        if (!Schema::hasColumn($name, 'goods_integral')) {
            Schema::table($name, function (Blueprint $table) {
                $table->integer('goods_integral')->default(0)->comment('积分均摊');
            });
        }

        if (!Schema::hasColumn($name, 'goods_integral_money')) {
            Schema::table($name, function (Blueprint $table) {
                $table->decimal('goods_integral_money', 10, 2)->default(0)->comment('积分金额均摊');
            });
        }
    }

    private function order_goods()
    {
        $name = 'order_goods';
        if (!Schema::hasTable($name)) {
            return false;
        }

        if (!Schema::hasColumn($name, 'goods_integral')) {
            Schema::table($name, function (Blueprint $table) {
                $table->integer('goods_integral')->default(0)->comment('积分均摊');
            });
        }

        if (!Schema::hasColumn($name, 'goods_integral_money')) {
            Schema::table($name, function (Blueprint $table) {
                $table->decimal('goods_integral_money', 10, 2)->default(0)->comment('积分金额均摊');
            });
        }
    }

    private function order_return()
    {
        $name = 'order_return';
        if (!Schema::hasTable($name)) {
            return false;
        }

        if (!Schema::hasColumn($name, 'goods_integral')) {
            Schema::table($name, function (Blueprint $table) {
                $table->integer('goods_integral')->default(0)->comment('积分均摊');
            });
        }

        if (!Schema::hasColumn($name, 'goods_integral_money')) {
            Schema::table($name, function (Blueprint $table) {
                $table->decimal('goods_integral_money', 10, 2)->default(0)->comment('积分金额均摊');
            });
        }
    }

    /**
     * 数据库填充
     * @return mixed|void
     */
    public function seed()
    {
        // 小商店权限
        if (file_exists(MOBILE_WXSHOP)) {
            $this->wxshop();
        }

        // 会员余额修改发送短信配置
        $this->add_sms_change_user_money();

        // 版本释放之前更新版本
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v2.4.1'
        ]);
    }

    /**
     * @return mixed|void
     * @throws \Exception
     */
    public function clean()
    {
        cache()->flush();
    }

    private function wxshop()
    {
        $parent_id = AdminAction::where('action_code', 'seller_wxshop')->value('action_id');
        $parent_id = $parent_id ? $parent_id : 0;

        //小商店快递公司
        $action_id = AdminAction::where('action_code', 'wxshop_shipping')->value('action_id');
        $action_id = $action_id ? $action_id : 0;
        if (empty($action_id)) {
            AdminAction::insert([
                'parent_id' => $parent_id,
                'action_code' => 'wxshop_shipping',
                'seller_show' => 1
            ]);
        }
    }

    /**
     * 优化数据表主键
     */
    private function addPK()
    {
        if (!Schema::hasColumn('cat_recommend', 'id')) {
            Schema::table('cat_recommend', function (Blueprint $table) {
                $table->dropPrimary(['cat_id', 'recommend_type']);
                $table->unique(['cat_id', 'recommend_type']);
            });
            Schema::table('cat_recommend', function (Blueprint $table) {
                $table->increments('id')->comment('编号')->first();
            });
        }

        if (!Schema::hasColumn('goods_cat', 'id')) {
            Schema::table('goods_cat', function (Blueprint $table) {
                $table->dropPrimary(['goods_id', 'cat_id']);
                $table->unique(['goods_id', 'cat_id']);
            });
            Schema::table('goods_cat', function (Blueprint $table) {
                $table->increments('id')->comment('编号')->first();
            });
        }

        if (!Schema::hasColumn('keywords', 'id')) {
            Schema::table('keywords', function (Blueprint $table) {
                $table->dropPrimary(['date', 'searchengine', 'keyword']);
                $table->unique(['date', 'searchengine', 'keyword']);
            });
            Schema::table('keywords', function (Blueprint $table) {
                $table->increments('id')->comment('编号')->first();
            });
        }

        if (!Schema::hasColumn('link_goods', 'id')) {
            Schema::table('link_goods', function (Blueprint $table) {
                $table->dropPrimary(['goods_id', 'link_goods_id', 'admin_id']);
                $table->unique(['goods_id', 'link_goods_id', 'admin_id']);
            });
            Schema::table('link_goods', function (Blueprint $table) {
                $table->increments('id')->comment('编号')->first();
            });
        }

        if (!Schema::hasColumn('searchengine', 'id')) {
            Schema::table('searchengine', function (Blueprint $table) {
                $table->dropPrimary(['date', 'searchengine']);
                $table->unique(['date', 'searchengine']);
            });
            Schema::table('searchengine', function (Blueprint $table) {
                $table->increments('id')->comment('编号')->first();
            });
        }

        if (!Schema::hasColumn('stats', 'id')) {
            Schema::table('stats', function (Blueprint $table) {
                $table->increments('id')->comment('编号')->first();
            });
        }
    }

    /**
     * 会员余额修改发送短信配置
     */
    private function add_sms_change_user_money()
    {
        $parent_id = ShopConfig::where('code', 'sms')->where('type', 'group')->value('id');
        if ($parent_id > 0) {
            $result = ShopConfig::where('code', 'sms_change_user_money')->count();
            if (empty($result)) {
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'sms_change_user_money',
                        'value' => '0',
                        'type' => 'select',
                        'store_range' => '1,0',
                        'sort_order' => 3,
                        'shop_group' => 'sms'
                    ]
                ];
                ShopConfig::insert($rows);
            }
        }
    }

    /**
     * 添加提现类型
     */
    private function add_process_type()
    {
        $tableName = 'user_account_fields';
        if (Schema::hasTable($tableName)) {
            if (!Schema::hasColumn($tableName, 'withdraw_type')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->tinyInteger('withdraw_type')->default(0)->unsigned()->comment('提现类型：0 银行卡 1 微信 2 支付宝');
                });
            }
        }
    }

    /**
     * 添加关联商品排序
     * @return mixed|void
     */
    private function add_link_goods_sort()
    {
        // 关联商品 排序
        $tableName = 'link_goods';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'sort')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->tinyInteger('sort')->unsigned()->default(50)->comment('商品排序');
                });
            }
        }
    }

    /**
     * 添加货品重量
     * @return mixed|void
     */
    private function add_sku_weight()
    {
        // 商品货品重量（普通模式）
        $tableName = 'products';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'sku_weight')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->decimal('sku_weight', 10, 3)->unsigned()->default(0.000)->comment('商品货品重量');
                });
            }
        }

        // 商品货品重量（仓库模式）
        $tableName = 'products_warehouse';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'sku_weight')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->decimal('sku_weight', 10, 3)->unsigned()->default(0.000)->comment('商品货品重量');
                });
            }
        }

        // 商品货品重量（地区模式）
        $tableName = 'products_area';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'sku_weight')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->decimal('sku_weight', 10, 3)->unsigned()->default(0.000)->comment('商品货品重量');
                });
            }
        }

        // 商品货品重量（临时表）
        $tableName = 'products_changelog';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'sku_weight')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->decimal('sku_weight', 10, 3)->unsigned()->default(0.000)->comment('商品货品重量');
                });
            }
        }
    }

    /**
     * 订单商品表增加 计佣金额
     */
    protected function change_order_goods_add_drp_goods_price()
    {
        // 订单商品表
        $tableName = 'order_goods';
        if (Schema::hasTable($tableName)) {
            if (!Schema::hasColumn($tableName, 'drp_goods_price')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->decimal('drp_goods_price', 10, 2)->unsigned()->default(0)->after('drp_money')->comment('订单商品计佣金额');
                });
            }
        }

        // 分销订单商品记录表增加
        $tableName = 'drp_order_goods_log';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'drp_goods_price')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->decimal('drp_goods_price', 10, 2)->unsigned()->default(0)->after('drp_money')->comment('订单商品计佣金额');
                });
            }
        }
    }
}
