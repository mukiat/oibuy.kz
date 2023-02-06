<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPersonalToMerchantsStepsFieldsTable extends Migration
{
    protected $table = 'merchants_steps_fields';

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

        if (!Schema::hasColumn($this->table, 'name')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->text('name')->comment('姓名');
            });
        }
        if (!Schema::hasColumn($this->table, 'id_card')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->text('id_card')->comment('身份证号码');
            });
        }
        if (!Schema::hasColumn($this->table, 'business_address')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->text('business_address')->comment('经营地址');
            });
        }
        if (!Schema::hasColumn($this->table, 'business_category')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->text('business_category')->comment('经营类目');
            });
        }
        if (!Schema::hasColumn($this->table, 'business_category')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->text('business_category')->comment('经营类目');
            });
        }
        if (!Schema::hasColumn($this->table, 'is_personal')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->text('is_personal')->comment('单位');
            });
        }
        if (!Schema::hasColumn($this->table, 'id_card_img_one_fileImg')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->text('id_card_img_one_fileImg')->comment('身份证正面');
            });
        }
        if (!Schema::hasColumn($this->table, 'id_card_img_two_fileImg')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->text('id_card_img_two_fileImg')->comment('身份证反面');
            });
        }
        if (!Schema::hasColumn($this->table, 'id_card_img_three_fileImg')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->text('id_card_img_three_fileImg')->comment('手持身份证上半身照');
            });
        }
        if (!Schema::hasColumn($this->table, 'commitment_fileImg')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->text('commitment_fileImg')->comment('个人承诺书');
            });
        }
        if (!Schema::hasColumn($this->table, 'mobile')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->text('mobile')->comment('联系电话');
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
        if (Schema::hasColumn($this->table, 'name')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
        if (Schema::hasColumn($this->table, 'id_card')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('id_card');
            });
        }
        if (Schema::hasColumn($this->table, 'business_address')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('business_address');
            });
        }
        if (Schema::hasColumn($this->table, 'business_category')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('business_category');
            });
        }
        if (Schema::hasColumn($this->table, 'is_personal')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('is_personal');
            });
        }
        if (Schema::hasColumn($this->table, 'id_card_img_one_fileImg')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('id_card_img_one_fileImg');
            });
        }
        if (Schema::hasColumn($this->table, 'id_card_img_two_fileImg')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('id_card_img_two_fileImg');
            });
        }
        if (Schema::hasColumn($this->table, 'id_card_img_three_fileImg')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('id_card_img_three_fileImg');
            });
        }
        if (Schema::hasColumn($this->table, 'commitment_fileImg')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('commitment_fileImg');
            });
        }
        if (Schema::hasColumn($this->table, 'mobile')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('mobile');
            });
        }
    }
}
