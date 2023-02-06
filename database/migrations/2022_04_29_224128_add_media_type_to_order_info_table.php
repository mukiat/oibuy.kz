<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMediaTypeToOrderInfoTable extends Migration
{
    private $name = 'order_info';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->name)) {
            return false;
        }

        if (!Schema::hasColumn($this->name, 'media_type')) {
            Schema::table($this->name, function (Blueprint $table) {
                $table->tinyInteger('media_type')->default(0)->unsigned()->comment('');
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
        Schema::table('order_info', function (Blueprint $table) {
            //
        });
    }
}
