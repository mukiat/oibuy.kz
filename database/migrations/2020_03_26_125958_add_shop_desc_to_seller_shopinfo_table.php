<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShopDescToSellerShopinfoTable extends Migration
{
    private $table = 'seller_shopinfo';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            return false;
        }
        
        if (!Schema::hasColumn($this->table, 'shop_desc')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->text('shop_desc')->comment('店铺描述');
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
        // 删除字段
        if (Schema::hasColumn($this->table, 'shop_desc')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('shop_desc');
            });
        }
    }
}
