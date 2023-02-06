<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShopCanCommentToSellerShopinfoTable extends Migration
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

        if (!Schema::hasColumn($this->table, 'shop_can_comment')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->integer('shop_can_comment')->default(1)->comment('店铺是否可评论：0 否, 1 是');
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
        if (Schema::hasColumn($this->table, 'shop_can_comment')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('shop_can_comment');
            });
        }
    }
}
