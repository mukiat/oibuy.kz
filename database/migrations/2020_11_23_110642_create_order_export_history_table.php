<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateOrderExportHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $name = 'order_export_history';
        if (!Schema::hasTable($name)) {
            Schema::create($name, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('ru_id')->comment('店铺ID');
                $table->string('file_name')->comment('文件名');
                $table->string('file_type')->comment('文件格式');
                $table->string('download_params')->comment('下载参数');
                $table->string('download_url')->comment('下载地址');
                $table->timestamps();
                $table->softDeletes();
            });

            $prefix = config('database.connections.mysql.prefix');
            DB::statement("ALTER TABLE `" . $prefix . "$name` comment '导出记录表'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_export_history');
    }
}
