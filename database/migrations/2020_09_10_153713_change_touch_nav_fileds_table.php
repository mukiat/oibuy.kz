<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeTouchNavFiledsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = 'touch_nav';
        if (!Schema::hasTable($tableName)) {
            return false;
        }

        // 判断字段是否存在添加
        if (!Schema::hasColumn($tableName, 'device')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('device', 20)->default('')->comment('客户端：h5, wxapp, app');
            });
        }

        if (!Schema::hasColumn($tableName, 'parent_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('parent_id')->unsigned()->default(0)->index()->comment('父级导航id，对应本表的id字段');
            });
        }

        if (!Schema::hasColumn($tableName, 'page')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('page', 20)->default('')->comment('页面标识，如user');
            });
        }

        if (Schema::hasColumn($tableName, 'id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->increments('id')->comment('自增ID')->change();
            });
        }
        if (Schema::hasColumn($tableName, 'vieworder')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('vieworder')->unsigned()->default(0)->comment('排序')->change();
            });
        }

        // 增加 vieworder 索引
        if (!DB::table('touch_nav')->hasIndex('dsc_touch_nav_vieworder_index')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->index('vieworder');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableName = 'touch_nav';
        if (!Schema::hasTable($tableName)) {
            return false;
        }

        if (Schema::hasColumn($tableName, 'device')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('device');
            });
        }

        if (Schema::hasColumn($tableName, 'parent_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('parent_id');
            });
        }
        
        if (Schema::hasColumn($tableName, 'page')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('page');
            });
        }
    }
}
