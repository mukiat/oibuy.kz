<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTableMigrations extends Migration
{
    /**
     * @var string
     */
    private $prefix = '';

    /**
     * CreateCreateTableMigrations constructor.
     */
    public function __construct()
    {
        $this->prefix = config('database.connections.mysql.prefix');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
