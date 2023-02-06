<?php

namespace App\Patch;

use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Migration_v1_5_2
{
    /**
     * @var string
     */
    private $prefix = '';

    public function run()
    {
        try {
            $this->sellerShopinfo();

            $this->articleVersionCode();

            if (file_exists(MOBILE_APP)) {
                $this->appClientManage();
                $this->addAppClient();
                $this->addAppClientProduct();
            }

            if (file_exists(MOBILE_DRP)) {
                $this->admin_action_add_drp_store();
                $this->admin_action_add_drp_commission_bills();
            }

            if (file_exists(MOBILE_WXAPP)) {
                $this->add_wxapp_live_goods();
                $this->admin_action_add_wxapp_live();
            }

            $this->createCosConfigureTable();
            $this->createTableMigrations();

            $this->shopConfig();
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * 更新版本
     * @throws Exception
     */
    private function shopConfig()
    {
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v1.5.2'
        ]);
    }

    /**
     * @throws Exception
     */
    private function articleVersionCode()
    {
        $tableName = 'article';

        if (!Schema::hasTable($tableName)) {
            return false;
        }

        // 判断字段是否存在添加
        if (!Schema::hasColumn($tableName, 'version_code')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('version_code')->default(1)->comment('文章版本号');
            });
        }
    }

    /**
     * app客户端管理
     */
    private function appClientManage()
    {
        $action_id = DB::table('admin_action')->where('action_code', 'app')->value('action_id');
        if (!empty($action_id)) {
            // app 客户端管理
            $count = DB::table('admin_action')->where('action_code', 'app_client_manage')->count();
            if ($count <= 0) {
                $parent_id = $action_id ? $action_id : 0;

                DB::table('admin_action')->insert([
                    'parent_id' => $parent_id,
                    'action_code' => 'app_client_manage',
                    'seller_show' => '0'
                ]);
            }
        }
    }

    /**
     * 增加 app客户端
     */
    protected function addAppClient()
    {
        $name = 'app_client';
        if (Schema::hasTable($name)) {
            return false;
        }

        Schema::create('app_client', function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->string('name', 50)->default('')->comment('应用名称');
            $table->string('appid', 50)->default('')->comment('APPID');
            $table->integer('create_time')->default(0)->comment('添加时间');
        });

        $prefix = config('database.connections.mysql.prefix');
        DB::statement("ALTER TABLE `" . $prefix . "$name` comment 'app客户端'");
    }

    /**
     * 增加 app客户端产品
     */
    protected function addAppClientProduct()
    {
        $name = 'app_client_product';
        if (Schema::hasTable($name)) {
            return false;
        }

        Schema::create('app_client_product', function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->integer('client_id')->unsigned()->comment('客户端ID');
            $table->string('version_id', 30)->default('')->comment('版本ID');
            $table->text('update_desc')->comment('更新描述');
            $table->string('download_url')->default('')->comment('下载地址');
            $table->tinyInteger('is_show')->unsigned()->default(1)->comment('是否显示');
            $table->string('update_time')->default('')->comment('更新时间');
            $table->string('create_time')->default('')->comment('添加时间');
        });

        $prefix = config('database.connections.mysql.prefix');
        DB::statement("ALTER TABLE `" . $prefix . "$name` comment 'app客户端产品'");
    }

    /**
     * 增加 微店权限控制
     */
    protected function admin_action_add_drp_store()
    {
        // 增加 微店权限
        $result = DB::table('admin_action')->where('action_code', 'drp_store')->count();

        if (empty($result)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'drp')->value('action_id');

            if ($parent_id) {
                $row = [
                    'parent_id' => $parent_id,
                    'action_code' => 'drp_store',
                    'seller_show' => 0
                ];
                DB::table('admin_action')->insert($row);
            }
        }

        // 增加 微店审核权限
        $result = DB::table('admin_action')->where('action_code', 'drp_store_check')->count();

        if (empty($result)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'drp')->value('action_id');

            if ($parent_id) {
                $row = [
                    'parent_id' => $parent_id,
                    'action_code' => 'drp_store_check',
                    'seller_show' => 0
                ];
                DB::table('admin_action')->insert($row);
            }
        }
    }

    /**
     * 增加 佣金账单权限控制
     */
    protected function admin_action_add_drp_commission_bills()
    {
        // 增加 微店权限
        $result = DB::table('admin_action')->where('action_code', 'drp_commission_bills')->count();

        if (empty($result)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'drp')->value('action_id');

            if ($parent_id) {
                $row = [
                    'parent_id' => $parent_id,
                    'action_code' => 'drp_commission_bills',
                    'seller_show' => 0
                ];
                DB::table('admin_action')->insert($row);
            }
        }
    }

    /**
     * 小程序直播商品表
     * @return bool
     */
    protected function add_wxapp_live_goods()
    {
        $name = 'wxapp_live_goods';
        if (Schema::hasTable($name)) {
            return false;
        }

        Schema::create('wxapp_live_goods', function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->string('goods_name', 50)->default('')->comment('商品名称');
            $table->string('cover_img_url')->default('')->comment('封面图 mediaID');
            $table->tinyInteger('price_type')->unsigned()->default(1)->comment('价格类型：1 一口价，2 价格区间，3 显示折扣价');
            $table->decimal('price', 10, 2)->default(0)->comment('商品价格1');
            $table->decimal('price2', 10, 2)->default(0)->comment('商品价格2');
            $table->string('url')->default('')->comment('商品小程序链接');
            $table->tinyInteger('is_audit')->unsigned()->default(0)->comment('是否提审：0 未提审，1 已提审');
            $table->tinyInteger('audit_status')->unsigned()->default(0)->comment('审核状态：0 未审核，1 审核中，2 审核通过，3 审核失败');
            $table->integer('audit_time')->unsigned()->default(0)->comment('提审时间');
            $table->integer('goods_id')->unsigned()->default(0)->comment('商品ID');
            $table->string('audit_id')->default('')->comment('审核单ID');
            $table->tinyInteger('third_party_tag')->unsigned()->default(0)->comment('2：表示是为api添加商品，否则不是api添加商品');
            $table->integer('add_time')->unsigned()->default(0)->comment('添加时间');
            $table->integer('update_time')->unsigned()->default(0)->comment('更新时间');
        });

        $prefix = config('database.connections.mysql.prefix');
        DB::statement("ALTER TABLE `" . $prefix . "$name` comment '小程序直播商品表'");
    }

    /**
     * 增加 小程序直播权限控制
     */
    protected function admin_action_add_wxapp_live()
    {
        // 增加 小程序直播权限控制
        $result = DB::table('admin_action')->where('action_code', 'wxapp_live')->count();

        if (empty($result)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'wxapp')->value('action_id');

            if ($parent_id) {
                $row = [
                    'parent_id' => $parent_id,
                    'action_code' => 'wxapp_live',
                    'seller_show' => 0
                ];
                DB::table('admin_action')->insert($row);
            }
        }
    }

    private function createCosConfigureTable()
    {
        $name = 'cos_configure';

        if (Schema::hasTable($name)) {
            return false;
        }

        Schema::create($name, function (Blueprint $table) {
            $table->increments('id')->comment('自增ID号');
            $table->string('bucket')->comment('COS模块对象名称');
            $table->string('app_id')->comment('APP ID');
            $table->string('secret_id')->comment('SECRET ID');
            $table->string('secret_key')->comment('SECRET Key');
            $table->boolean('is_cname')->default(0)->comment('是否域名绑定');
            $table->string('endpoint')->comment('绑定域名地址');
            $table->string('regional', 100)->comment('OSS绑定区域');
            $table->string('port', 15)->comment('端口号');
            $table->boolean('is_use')->default(0)->index('is_use')->comment('是否启用（0否，1是）');
        });
    }

    private function createTableMigrations()
    {
        $this->prefix = config('database.connections.mysql.prefix');

        $this->smsTemplate();
        $this->goods();
        $this->addFiledToOrderInfo();
        $this->dropAlidayuTable();
        $this->addFieldToOBS();
        $this->adminActionChange();
    }

    private function smsTemplate()
    {
        if (Schema::hasTable('alidayu_configure') && !Schema::hasTable('sms_template')) {
            $sql = "RENAME TABLE `" . $this->prefix . "alidayu_configure` TO `" . $this->prefix . "sms_template`";
            DB::statement($sql);
        }

        $name = 'sms_template';
        if (!Schema::hasColumn($name, 'sender')) {
            Schema::table($name, function (Blueprint $table) {
                $table->string('sender', 30)->default('')->comment('短信通道号');
            });
        }
    }

    private function goods()
    {
        $table = 'goods';

        /* 检查索引 */
        $hasIndex = $this->hasIndex($table, 'is_new');

        if ($hasIndex) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropIndex('is_new');
            });
        }

        /* 检查索引 */
        $hasIndex = $this->hasIndex($table, 'is_best');

        if ($hasIndex) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropIndex('is_best');
            });
        }

        /* 检查索引 */
        $hasIndex = $this->hasIndex($table, 'is_hot');

        if ($hasIndex) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropIndex('is_hot');
            });
        }

        /* 检查索引 */
        $hasIndex = $this->hasIndex($table, 'store_new');

        if ($hasIndex) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropIndex('store_new');
            });
        }

        /* 检查索引 */
        $hasIndex = $this->hasIndex($table, 'store_best');

        if ($hasIndex) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropIndex('store_best');
            });
        }

        /* 检查索引 */
        $hasIndex = $this->hasIndex($table, 'store_hot');

        if ($hasIndex) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropIndex('store_hot');
            });
        }

        /* 检查索引 */
        $hasIndex = $this->hasIndex($table, 'is_show');

        if ($hasIndex) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropIndex('is_show');
            });
        }
    }

    /**
     * 新增字段
     */
    private function addFiledToOrderInfo()
    {
        if (!Schema::hasColumn('order_info', 'ru_id')) {
            Schema::table('order_info', function (Blueprint $table) {
                $table->integer('ru_id')->unsigned()->default(0)->index('ru_id')->comment('商家ID（同dsc_users表user_id）');
            });
        }

        if (!Schema::hasColumn('order_info', 'main_count')) {
            Schema::table('order_info', function (Blueprint $table) {
                $table->smallInteger('main_count')->unsigned()->default(0)->index('main_count')->comment('子订单数量');
            });
        }
    }

    /**
     * 删除 alitongxin_configure 表
     */
    private function dropAlidayuTable()
    {
        $val = DB::table('shop_config')->where('code', 'sms_type')->value('value');

        if ($val == 2 && Schema::hasTable('alidayu_configure')) {
            $count = DB::table('alitongxin_configure')->count();

            if ($count > 0) {
                $list = DB::table('alitongxin_configure')->get();

                $list = $list ? collect($list)->toArray() : [];

                if ($list && Schema::hasTable('sms_template')) {
                    $sql = "TRUNCATE `" . $this->prefix . "sms_template`";
                    DB::statement($sql);

                    foreach ($list as $key => $value) {
                        $list[$key] = collect($value)->toArray();
                    }

                    DB::table('sms_template')->insert($list);
                }
            }
        }

        if (Schema::hasTable('alidayu_configure')) {
            $sql = "DROP TABLE `" . $this->prefix . "alidayu_configure`";
            DB::statement($sql);
        }

        if (Schema::hasTable('alitongxin_configure')) {
            $sql = "DROP TABLE `" . $this->prefix . "alitongxin_configure`";
            DB::statement($sql);
        }
    }

    /**
     * 补漏字段
     */
    public function addFieldToOBS()
    {
        if (!Schema::hasColumn('obs_configure', 'port')) {
            Schema::table('obs_configure', function (Blueprint $table) {
                $table->boolean('port', 15)->comment('端口号');
            });
        }
    }

    /**
     * 更新字段
     */
    public function adminActionChange()
    {
        // 判断 action_code 表字段是否存在添加
        if (Schema::hasColumn('admin_action', 'action_code')) {
            Schema::table('admin_action', function (Blueprint $table) {
                $table->string('action_code', 50)->change();
            });
        }
    }

    public function sellerShopinfo()
    {
        $table_name = 'seller_shopinfo';
        if (!Schema::hasTable($table_name)) {
            return false;
        }

        if (!Schema::hasColumn($table_name, 'zipcode')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->string('zipcode', 60)->default('')->comment('邮政编码');
            });
        }
    }

    /**
     * 判断索引是否存在
     *
     * @param string $table
     * @param string $name
     * @return bool
     */
    private function hasIndex($table = '', $name = '')
    {
        $sql = "SHOW index FROM `" . $this->prefix . $table . "` WHERE column_name LIKE '" . $name . "'";
        $list = DB::select($sql);

        if ($list) {
            return true;
        } else {
            return false;
        }
    }
}
