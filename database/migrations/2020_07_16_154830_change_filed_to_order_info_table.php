<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFiledToOrderInfoTable extends Migration
{
    private $table = 'order_info';

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

        Schema::table($this->table, function (Blueprint $table) {
            $table->integer('agency_id')->default(0)->change();
            $table->string('inv_type')->default('')->change();
            $table->decimal('tax', 10, 2)->default(0)->change();
            $table->decimal('discount', 10, 2)->default(0)->change();
            $table->decimal('discount_all', 10, 2)->default(0)->change();
            $table->integer('zc_goods_id')->default(0)->change();
            $table->string('rel_name')->default('')->change();
            $table->string('id_num')->default('')->change();
            $table->string('sign_time', 60)->default(0)->change();
        });

        if (Schema::hasColumn($this->table, 'shipping_date_str')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->string('shipping_date_str')->default('')->change();
            });
        } elseif (Schema::hasColumn($this->table, 'shipping_dateStr')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->string('shipping_dateStr')->default('')->change();
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
