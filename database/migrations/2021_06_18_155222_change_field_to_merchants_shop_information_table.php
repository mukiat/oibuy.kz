<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFieldToMerchantsShopInformationTable extends Migration
{
    private $table = "merchants_shop_information";

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

        if (Schema::hasColumn($this->table, 'subShoprz_type')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->renameColumn("subShoprz_type", "sub_shoprz_type");
            });
        }

        if (Schema::hasColumn($this->table, 'shop_expireDateStart')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->renameColumn("shop_expireDateStart", "shop_expire_date_start");
            });
        }

        if (Schema::hasColumn($this->table, 'shop_expireDateEnd')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->renameColumn("shop_expireDateEnd", "shop_expire_date_end");
            });
        }

        if (Schema::hasColumn($this->table, 'authorizeFile')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->renameColumn("authorizeFile", "authorize_file");
            });
        }

        if (Schema::hasColumn($this->table, 'shop_hypermarketFile')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->renameColumn("shop_hypermarketFile", "shop_hypermarket_file");
            });
        }

        if (Schema::hasColumn($this->table, 'shop_categoryMain')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->renameColumn("shop_categoryMain", "shop_category_main");
            });
        }

        if (Schema::hasColumn($this->table, 'user_shopMain_category')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->renameColumn("user_shopMain_category", "user_shop_main_category");
            });
        }

        if (Schema::hasColumn($this->table, 'shoprz_brandName')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->renameColumn("shoprz_brandName", "shoprz_brand_name");
            });
        }

        if (Schema::hasColumn($this->table, 'shop_class_keyWords')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->renameColumn("shop_class_keyWords", "shop_class_key_words");
            });
        }

        if (Schema::hasColumn($this->table, 'shopNameSuffix')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->renameColumn("shopNameSuffix", "shop_name_suffix");
            });
        }

        if (Schema::hasColumn($this->table, 'rz_shopName')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->renameColumn("rz_shopName", "rz_shop_name");
            });
        }

        if (Schema::hasColumn($this->table, 'hopeLoginName')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->renameColumn("hopeLoginName", "hope_login_name");
            });
        }

        if (Schema::hasColumn($this->table, 'is_IM')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->renameColumn("is_IM", "is_im");
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
        Schema::table('merchants_shop_information', function (Blueprint $table) {
            //
        });
    }
}
