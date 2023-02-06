<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWxappTouchCatadsUrlToCategoryTable extends Migration
{
    private $name = 'category';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->name)) {
            return false;
        }

        if (!Schema::hasColumn($this->name, 'wxapp_touch_catads_url')) {
            Schema::table($this->name, function (Blueprint $table) {
                $table->string('wxapp_touch_catads_url', 255)->default('')->comment("小程序分类列表广告链接");
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
        if (Schema::hasColumn($this->table, 'wxapp_touch_catads_url')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('wxapp_touch_catads_url');
            });
        }
    }
}
