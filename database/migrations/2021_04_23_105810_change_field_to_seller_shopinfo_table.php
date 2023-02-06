<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFieldToSellerShopinfoTable extends Migration
{
    private $table = "seller_shopinfo";

    /**
     * @return bool
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            return false;
        }

        if (Schema::hasColumn($this->table, 'kf_welcome_msg')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->renameColumn('kf_welcome_msg', 'kf_welcome_msg');
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
