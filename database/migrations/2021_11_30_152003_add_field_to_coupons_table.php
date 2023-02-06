<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldToCouponsTable extends Migration
{
    private $name = 'coupons';

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

        if (!Schema::hasColumn($this->name, 'receive_start_time')) {
            Schema::table($this->name, function (Blueprint $table) {
                $table->integer('receive_start_time')->default(0)->comment("领券开始时间");
            });
        }

        if (!Schema::hasColumn($this->name, 'receive_end_time')) {
            Schema::table($this->name, function (Blueprint $table) {
                $table->integer('receive_end_time')->default(0)->comment("领券结束时间");
            });
        }

        if (!Schema::hasColumn($this->name, 'status')) {
            Schema::table($this->name, function (Blueprint $table) {
                $table->tinyInteger('status')->default(1)->index('status')->comment('状态 1 未生效，编辑中 2 生效 3 已过期 4 已作废');
            });
        }

        if (!Schema::hasColumn($this->name, 'promoter_id')) {
            Schema::table($this->name, function (Blueprint $table) {
                $table->integer('promoter_id')->default(0)->comment('视频号推广员自增ID');
            });
        }

        if (!Schema::hasColumn($this->name, 'valid_day_num')) {
            Schema::table($this->name, function (Blueprint $table) {
                $table->smallInteger('valid_day_num')->default(1)->comment('领取有效天数');
            });
        }

        if (!Schema::hasColumn($this->name, 'valid_type')) {
            Schema::table($this->name, function (Blueprint $table) {
                $table->tinyInteger('valid_type')->default(1)->comment('有效期类型 1 按有效区间 2 按领取有效天数');
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
