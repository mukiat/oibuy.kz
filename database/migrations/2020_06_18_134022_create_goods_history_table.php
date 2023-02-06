<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateGoodsHistoryTable extends Migration
{
    protected $table_name = 'goods_history';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->table_name)) {
            return false;
        }

        Schema::create($this->table_name, function (Blueprint $table) {
            $table->increments('history_id')->comment('自增ID号');
            $table->integer('user_id')->unsigned()->default(0)->index('user_id')->comment('会员id');
            $table->string('session_id')->nullable()->index('session_id')->comment('登录的sessionid');
            $table->integer('goods_id')->unsigned()->default(0)->index('goods_id')->comment('商品的id');
            $table->integer('add_time')->default(0)->comment('添加时间');
        });

        $prefix = config('database.connections.mysql.prefix');
        DB::statement("ALTER TABLE `" . $prefix . "$this->table_name` comment '商品浏览记录表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods_history');
    }
}
