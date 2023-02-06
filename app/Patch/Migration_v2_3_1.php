<?php

namespace App\Patch;

use App\Contracts\MigrationContract;
use App\Events\RunOtherModulesSeederEvent;
use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Migration_v2_3_1 implements MigrationContract
{
    public function run()
    {
        try {
            $this->migration();
            $this->seed();
            $this->clean();
            // 执行其他模块seed
            event(new RunOtherModulesSeederEvent());
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
        /* 对外开发接口表 */
        $tableName = 'value_card';
        if (Schema::hasTable($tableName)) {
            if (!Schema::hasColumn($tableName, 'use_status')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->tinyInteger('use_status')->default(1)->unsigned()->comment('储值卡状态：0 无效 1 正常');
                });
            }
        }

        // 修改小程序配置
        $tableName = 'wxapp_config';
        if (Schema::hasTable($tableName)) {
            if (Schema::hasColumn($tableName, 'mp_checked')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('mp_checked');
                });
            }

            if (!Schema::hasColumn($tableName, 'weapp_in_review')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->string('weapp_in_review')->default('')->comment('提交审核版本号');
                });
            } else {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->string('weapp_in_review')->default('')->change();
                });
            }
        }

        // 添加商品活动标签类型
        $this->change_goods_label_add_type();

    }

    /**
     * 添加商品活动标签类型
     *
     * @return void
     */
    protected function change_goods_label_add_type()
    {
        $tableName = 'goods_label';
        if (!Schema::hasTable($tableName)) {
            return false;
        }

        // 判断字段是否存在添加
        if (!Schema::hasColumn($tableName, 'type')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->tinyInteger('type')->unsigned()->default(0)->comment('标签类型：0 普通,1 悬浮');
            });
        }
        if (!Schema::hasColumn($tableName, 'start_time')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('start_time')->unsigned()->default(0)->comment('标签显示开始时间');
            });
        }
        if (!Schema::hasColumn($tableName, 'end_time')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('end_time')->unsigned()->default(0)->comment('标签显示结束时间');
            });
        }
        if (!Schema::hasColumn($tableName, 'bind_goods_number')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('bind_goods_number')->unsigned()->default(0)->comment('标签绑定商品数量');
            });
        }
    }

    /**
     * 数据库填充
     * @return mixed|void
     */
    public function seed()
    {
        // 版本释放之前更新版本
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v2.3.1'
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
}
