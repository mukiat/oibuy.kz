<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdminIdToMerchantsShopBrand extends Migration
{
    protected $table_name = 'merchants_shop_brand';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->table_name)) {
            if (!Schema::hasColumn($this->table_name, 'admin_id')) {
                Schema::table($this->table_name, function (Blueprint $table) {
                    $table->integer('admin_id')->index('admin_id')->default(0)->comment('区别是这条数据是那个用户的');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('merchants_shop_brand', function (Blueprint $table) {
            //
        });
    }
}
