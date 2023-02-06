<?php

namespace App\Patch;

use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Migration_v1_4_6
{
    public function run()
    {
        $this->change_password();
        $this->login_attempts();

        try {
            $this->shopConfig();
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    protected function change_password()
    {
        $tableName = 'users';
        if (!Schema::hasTable($tableName)) {
            return false;
        }
        if (Schema::hasColumn($tableName, 'password')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('password', 255)->change();
            });
        }

        $tableName = 'admin_user';
        if (!Schema::hasTable($tableName)) {
            return false;
        }
        if (Schema::hasColumn($tableName, 'password')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('password', 255)->change();
            });
        }

        $tableName = 'store_user';
        if (!Schema::hasTable($tableName)) {
            return false;
        }
        if (Schema::hasColumn($tableName, 'stores_pwd')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('stores_pwd', 255)->change();
            });
        }

        if (!Schema::hasColumn($tableName, 'last_login')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('last_login')->unsigned()->default(0)->comment('最后登录时间');
            });
        }

        if (!Schema::hasColumn($tableName, 'last_ip')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('last_ip', 15)->default('')->comment('最后一次登录ip');
            });
        }
    }

    /**
     * 登录锁定新增表
     */
    protected function login_attempts()
    {
        $this->create_cache();
        $this->create_user_error_log();
    }

    protected function create_cache()
    {
        $name = 'cache';
        if (Schema::hasTable($name)) {
            return false;
        }

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key', 190)->unique();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        $prefix = config('database.connections.mysql.prefix');
        DB::statement("ALTER TABLE `" . $prefix . "$name` comment '缓存数据库'");
    }

    protected function create_user_error_log()
    {
        $name = 'users_error_log';
        if (Schema::hasTable($name)) {
            return false;
        }

        Schema::create('users_error_log', function (Blueprint $table) {
            $table->increments('log_id')->comment('自增ID');
            $table->string('user_name', 30)->default('')->comment('姓名');
            $table->integer('user_id')->unsigned()->default(0)->index('user_id')->comment('用户ID');
            $table->integer('admin_id')->unsigned()->default(0)->index('admin_id')->comment('管理员id');
            $table->integer('store_user_id')->unsigned()->default(0)->index('store_user_id')->comment('门店会员id');
            $table->integer('create_time')->default(0)->comment('记录时间');
            $table->string('ip_address', 15)->default('')->comment('ip地址');
            $table->string('operation_note')->default('')->comment('操作备注');
            $table->string('user_agent')->default('')->comment('来源userAgent');
            $table->tinyInteger('expired')->unsigned()->default(0)->comment('是否过登录锁定期：0未过期 1 过期的');
        });

        $prefix = config('database.connections.mysql.prefix');
        DB::statement("ALTER TABLE `" . $prefix . "$name` comment '账号登录失败日志'");
    }

    /**
     * 更新版本
     * @throws Exception
     */
    private function shopConfig()
    {
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v1.4.6'
        ]);

        cache()->forget('shop_config');
    }
}
