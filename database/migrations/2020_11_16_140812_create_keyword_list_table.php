<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateKeywordListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $name = 'keyword_list';
        if (!Schema::hasTable($name)) {
            Schema::create($name, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('cat_id')->default(0)->index('cat_id')->comment('关键词分类ID');
                $table->string('name', 120)->default('')->comment('关键词名称');
                $table->integer('ru_id')->default(0)->index('ru_id')->comment('商家ID');
                $table->integer('update_time')->default(0)->comment('更新时间');
                $table->integer('add_time')->default(0)->comment('添加时间');
            });

            $prefix = config('database.connections.mysql.prefix');
            DB::statement("ALTER TABLE `" . $prefix . "$name` comment '关键词表'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('keyword_list');
    }
}
