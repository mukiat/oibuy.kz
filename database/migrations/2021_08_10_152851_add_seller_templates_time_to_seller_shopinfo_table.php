<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSellerTemplatesTimeToSellerShopinfoTable extends Migration
{
    private $table = 'seller_shopinfo';

    /**
     * Run the migrations.
     *
     * @return bool
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            return false;
        }

        if (!Schema::hasColumn($this->table, 'seller_templates_time')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->integer('seller_templates_time')->default(0)->after('seller_templates')->comment("可视化更新时间");
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
        Schema::table('seller_shopinfo', function (Blueprint $table) {
            //
        });
    }
}
