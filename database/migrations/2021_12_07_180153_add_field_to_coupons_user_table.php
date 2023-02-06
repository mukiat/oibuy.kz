<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldToCouponsUserTable extends Migration
{
    private $name = 'coupons_user';

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

        if (!Schema::hasColumn($this->name, 'valid_time')) {
            Schema::table($this->name, function (Blueprint $table) {
                $table->integer('valid_time')->default(0)->comment("领取有效截止时间");
            });
        }

        if (!Schema::hasColumn($this->name, 'status')) {
            Schema::table($this->name, function (Blueprint $table) {
                $table->tinyInteger('status')->default(2)->index('status')->comment('状态 1 未生效，编辑中 2 生效 3 已过期 4 已作废');
            });
        }

        if (!Schema::hasColumn($this->name, 'add_time')) {
            Schema::table($this->name, function (Blueprint $table) {
                $table->integer('add_time')->default(0)->comment("领取时间");
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
        Schema::table($this->name, function (Blueprint $table) {
            //
        });
    }
}
