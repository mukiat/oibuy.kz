<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAppTouchCatadsUrlToCategoryTable extends Migration
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

        if (!Schema::hasColumn($this->name, 'app_touch_catads_url')) {
            Schema::table($this->name, function (Blueprint $table) {
                $table->string('app_touch_catads_url', 255)->default('')->comment("APP分类列表广告链接");
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
        if (Schema::hasColumn($this->table, 'app_touch_catads_url')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('app_touch_catads_url');
            });
        }
    }
}
