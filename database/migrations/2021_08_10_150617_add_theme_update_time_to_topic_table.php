<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddThemeUpdateTimeToTopicTable extends Migration
{
    private $table = 'topic';

    /**
     * Run the migrations.
     *
     * @return bool
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            return false;
        }

        if (!Schema::hasColumn($this->table, 'theme_update_time')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->integer('theme_update_time')->default(0)->comment("可视化更新时间");
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
        Schema::table('topic', function (Blueprint $table) {
            //
        });
    }
}
