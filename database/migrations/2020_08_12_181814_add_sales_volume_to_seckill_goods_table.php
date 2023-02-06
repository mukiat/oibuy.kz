<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalesVolumeToSeckillGoodsTable extends Migration
{
    protected $table_name = 'seckill_goods';

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

        if (!Schema::hasColumn($this->table_name, 'sales_volume')) {
            Schema::table($this->table_name, function (Blueprint $table) {
                $table->integer('sales_volume')->default(0)->comment('秒杀商品销量');
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
        if (Schema::hasColumn($this->table_name, 'sales_volume')) {
            Schema::table($this->table_name, function (Blueprint $table) {
                $table->dropColumn('sales_volume');
            });
        }
    }
}
