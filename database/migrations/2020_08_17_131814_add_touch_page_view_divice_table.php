<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTouchPageViewDiviceTable extends Migration
{
    protected $table_name = 'touch_page_view';

    /**
     * Run the migrations.  
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table_name)) {
            return false;
        }
		// 判断字段是否存在添加
        if (!Schema::hasColumn($this->table_name, 'device')) {
            Schema::table($this->table_name, function (Blueprint $table) {
				$table->string('device', 50)->default('')->comment('设备 h5 app wxapp');
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
		// 删除字段
        if (Schema::hasColumn($this->table_name, 'device')) {
            Schema::table($this->table_name, function (Blueprint $table) {
                $table->dropColumn('device');
            });
        }
    }
}
