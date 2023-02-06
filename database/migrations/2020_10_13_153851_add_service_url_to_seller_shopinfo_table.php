<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiceUrlToSellerShopinfoTable extends Migration
{
    private $table = 'seller_shopinfo';

    /**
     * @return bool
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            return false;
        }

        if (!Schema::hasColumn($this->table, 'service_url')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->text('service_url')->nullable()->comment('自定义客服链接');
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
        Schema::table($this->table, function (Blueprint $table) {
            //
        });
    }
}
