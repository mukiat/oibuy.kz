<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSellerFollowListTable extends Migration
{
    private $name = 'seller_follow_list';

    /**
     * @return bool
     */
    public function up()
    {
        if (Schema::hasTable($this->name)) {
            return false;
        }

        Schema::create($this->name, function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->string('name', 60)->default('')->comment('名称');
            $table->string('desc', 100)->default('')->comment('描述');
            $table->integer('seller_id')->default(0)->index('seller_id')->comment('店铺ID');
            $table->string('qr_code')->default('')->comment('二维码');
            $table->string('cover_pic')->default('')->comment('封面图');
        });

        $prefix = config('database.connections.mysql.prefix');
        DB::statement("ALTER TABLE `" . $prefix . $this->name . "` comment '店铺二维码关注'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->name);
    }
}
