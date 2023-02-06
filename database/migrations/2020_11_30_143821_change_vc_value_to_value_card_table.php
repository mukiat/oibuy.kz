<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeVcValueToValueCardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = 'value_card';
        if (!Schema::hasTable($tableName)) {
            return false;
        }

        // 判断字段是否存在添加
        if (Schema::hasColumn($tableName, 'vc_value')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->decimal('vc_value', 10, 2)->default(0)->unsigned()->change();
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
        Schema::table('value_card', function (Blueprint $table) {
            //
        });
    }
}
