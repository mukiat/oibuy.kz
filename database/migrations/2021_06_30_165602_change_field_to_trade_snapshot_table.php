<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFieldToTradeSnapshotTable extends Migration
{

    private $table = "trade_snapshot";

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

        if (Schema::hasColumn($this->table, 'rz_shopName')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->renameColumn("rz_shopName", "rz_shop_name");
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
        Schema::table('trade_snapshot', function (Blueprint $table) {
            //
        });
    }
}
