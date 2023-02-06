<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddZipcodeToSellerShopinfoTable extends Migration
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

        if (!Schema::hasColumn($this->table, 'zipcode')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->string('zipcode', 60)->default('')->comment('邮政编码');
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
        if (Schema::hasColumn($this->table, 'zipcode')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('zipcode');
            });
        }
    }
}
