<?php

namespace App\Patch;

use App\Events\RunOtherModulesSeederEvent;
use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Migration_v2_6_2
{
    public function run()
    {
        try {
            $this->migration();

            // 执行其他模块seed
            event(new RunOtherModulesSeederEvent());

            $this->seed();
            $this->clean();
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    private function migration()
    {
        $this->merchants_shop_information();
        $this->trade_snapshot();

        if (file_exists(MOBILE_DRP)) {
            $this->seller_negative_bill();
            $this->seller_negative_order();
        }

        $this->seller_bill_order();
    }

    public function merchants_shop_information()
    {
        $name = 'merchants_shop_information';
        if (!Schema::hasTable($name)) {
            return false;
        }

        if (Schema::hasColumn($name, 'subShoprz_type')) {
            Schema::table($name, function (Blueprint $table) {
                $table->renameColumn("subShoprz_type", "sub_shoprz_type");
            });
        }

        if (Schema::hasColumn($name, 'shop_expireDateStart')) {
            Schema::table($name, function (Blueprint $table) {
                $table->renameColumn("shop_expireDateStart", "shop_expire_date_start");
            });
        }

        if (Schema::hasColumn($name, 'shop_expireDateEnd')) {
            Schema::table($name, function (Blueprint $table) {
                $table->renameColumn("shop_expireDateEnd", "shop_expire_date_end");
            });
        }

        if (Schema::hasColumn($name, 'authorizeFile')) {
            Schema::table($name, function (Blueprint $table) {
                $table->renameColumn("authorizeFile", "authorize_file");
            });
        }

        if (Schema::hasColumn($name, 'shop_hypermarketFile')) {
            Schema::table($name, function (Blueprint $table) {
                $table->renameColumn("shop_hypermarketFile", "shop_hypermarket_file");
            });
        }

        if (Schema::hasColumn($name, 'shop_categoryMain')) {
            Schema::table($name, function (Blueprint $table) {
                $table->renameColumn("shop_categoryMain", "shop_category_main");
            });
        }

        if (Schema::hasColumn($name, 'user_shopMain_category')) {
            Schema::table($name, function (Blueprint $table) {
                $table->renameColumn("user_shopMain_category", "user_shop_main_category");
            });
        }

        if (Schema::hasColumn($name, 'shoprz_brandName')) {
            Schema::table($name, function (Blueprint $table) {
                $table->renameColumn("shoprz_brandName", "shoprz_brand_name");
            });
        }

        if (Schema::hasColumn($name, 'shop_class_keyWords')) {
            Schema::table($name, function (Blueprint $table) {
                $table->renameColumn("shop_class_keyWords", "shop_class_key_words");
            });
        }

        if (Schema::hasColumn($name, 'shopNameSuffix')) {
            Schema::table($name, function (Blueprint $table) {
                $table->renameColumn("shopNameSuffix", "shop_name_suffix");
            });
        }

        if (Schema::hasColumn($name, 'rz_shopName')) {
            Schema::table($name, function (Blueprint $table) {
                $table->renameColumn("rz_shopName", "rz_shop_name");
            });
        }

        if (Schema::hasColumn($name, 'hopeLoginName')) {
            Schema::table($name, function (Blueprint $table) {
                $table->renameColumn("hopeLoginName", "hope_login_name");
            });
        }

        if (Schema::hasColumn($name, 'is_IM')) {
            Schema::table($name, function (Blueprint $table) {
                $table->renameColumn("is_IM", "is_im");
            });
        }
    }

    public function seller_negative_bill()
    {
        $name = 'seller_negative_bill';
        if (!Schema::hasTable($name)) {
            return false;
        }

        if (!Schema::hasColumn($name, 'drp_money')) {
            Schema::table($name, function (Blueprint $table) {
                $table->decimal('drp_money', 10, 2)->default(0)->comment("订单商品分销金额");
            });
        }
    }

    public function seller_bill_order()
    {
        $name = 'seller_bill_order';
        if (!Schema::hasTable($name)) {
            return false;
        }

        if (!Schema::hasColumn($name, 'dis_amount')) {
            Schema::table($name, function (Blueprint $table) {
                $table->decimal('dis_amount', 10, 2)->default(0)->comment("商品满减优惠总金额");
            });
        }
    }

    public function seller_negative_order()
    {
        $name = 'seller_negative_order';
        if (!Schema::hasTable($name)) {
            return false;
        }

        if (!Schema::hasColumn($name, 'drp_money')) {
            Schema::table($name, function (Blueprint $table) {
                $table->decimal('drp_money', 10, 2)->default(0)->comment("订单商品分销金额");
            });
        }
    }

    public function trade_snapshot()
    {
        $name = 'trade_snapshot';
        if (!Schema::hasTable($name)) {
            return false;
        }

        if (Schema::hasColumn($name, 'rz_shopName')) {
            Schema::table($name, function (Blueprint $table) {
                $table->renameColumn("rz_shopName", "rz_shop_name");
            });
        }
    }

    private function seed()
    {

        $count = ShopConfig::where('code', 'seller_review')->count();
        if (empty($count)) {

            $parent_id = ShopConfig::where('code', 'basic')->value('id');
            $parent_id = $parent_id ? $parent_id : 0;

            ShopConfig::insert([
                'parent_id' => $parent_id,
                'code' => 'seller_review',
                'type' => 'select',
                'store_range' => '0,1,2,3',
                'value' => 2,
                'shop_group' => 'seller'
            ]);
        }

        if (file_exists(WXAPP_MEDIA_PROMOTER)) {
            $count = ShopConfig::where('code', 'wxapp_media_id')->count();
            if (empty($count)) {

                $parent_id = ShopConfig::where('code', 'wxapp_shop_config')->value('id');
                $parent_id = $parent_id ? $parent_id : 0;

                ShopConfig::insert([
                    'parent_id' => $parent_id,
                    'code' => 'wxapp_media_id',
                    'type' => 'text',
                    'value' => '',
                    'shop_group' => 'wxapp_shop_config'
                ]);
            }
        }

        $count = ShopConfig::where('code', 'json_field')->count();
        if (empty($count)) {

            $parent_id = ShopConfig::where('code', 'basic')->value('id');
            $parent_id = $parent_id ? $parent_id : 0;

            ShopConfig::insert([
                'parent_id' => $parent_id,
                'code' => 'json_field',
                'type' => 'hidden',
                'store_range' => '0,1',
                'value' => 0
            ]);
        }

        ShopConfig::where('code', 'server_model')->update([
            'sort_order' => 2
        ]);

        ShopConfig::where('code', 'cloud_storage')->update([
            'sort_order' => 4
        ]);

        $count = ShopConfig::where('code', 'cloud_file_ip')->count();
        if (empty($count)) {

            $parent_id = ShopConfig::where('code', 'basic')->value('id');
            $parent_id = $parent_id ? $parent_id : 0;

            ShopConfig::insert([
                'parent_id' => $parent_id,
                'code' => 'cloud_file_ip',
                'type' => 'textarea',
                'value' => '',
                'sort_order' => 3,
                'shop_group' => 'cloud'
            ]);
        }

        // 版本释放之前更新版本
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v2.6.2'
        ]);
    }

    private function clean()
    {
        cache()->flush();
    }
}
