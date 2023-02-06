<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateGoodsVideoTable extends Migration
{
    protected $table_name = 'goods_video';

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
            $table->increments('video_id')->comment('自增ID号');
            $table->integer('goods_id')->unsigned()->default(0)->index('goods_id')->comment('商品的id');
            $table->string('goods_video')->default('')->comment('商品视频');
            $table->integer('look_num')->default(0)->comment('观看人数');
        });

        $prefix = config('database.connections.mysql.prefix');
        DB::statement("ALTER TABLE `" . $prefix . "$this->table_name` comment '商品视频表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->table_name);
    }
}
