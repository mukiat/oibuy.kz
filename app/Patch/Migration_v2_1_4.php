<?php

namespace App\Patch;

use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Migration_v2_1_4
{
    public function run()
    {
        try {
            $this->shopConfig();
            $this->add_personal_to_merchants_steps_fields();
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * 更新版本
     *
     * @throws Exception
     */
    private function shopConfig()
    {
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v2.1.4'
        ]);

        $this->clearCache();
    }

    /**
     * Run the migrations.
     *
     * @return bool
     */
    private function add_personal_to_merchants_steps_fields()
    {
        $name = 'merchants_steps_fields';
        if (!Schema::hasTable($name)) {
            return false;
        }

        if (!Schema::hasColumn($name, 'name')) {
            Schema::table($name, function (Blueprint $table) {
                $table->text('name')->comment('姓名');
            });
        }
        if (!Schema::hasColumn($name, 'id_card')) {
            Schema::table($name, function (Blueprint $table) {
                $table->text('id_card')->comment('身份证号码');
            });
        }
        if (!Schema::hasColumn($name, 'business_address')) {
            Schema::table($name, function (Blueprint $table) {
                $table->text('business_address')->comment('经营地址');
            });
        }
        if (!Schema::hasColumn($name, 'business_category')) {
            Schema::table($name, function (Blueprint $table) {
                $table->text('business_category')->comment('经营类目');
            });
        }
        if (!Schema::hasColumn($name, 'business_category')) {
            Schema::table($name, function (Blueprint $table) {
                $table->text('business_category')->comment('经营类目');
            });
        }
        if (!Schema::hasColumn($name, 'is_personal')) {
            Schema::table($name, function (Blueprint $table) {
                $table->text('is_personal')->comment('单位');
            });
        }
        if (!Schema::hasColumn($name, 'id_card_img_one_fileImg')) {
            Schema::table($name, function (Blueprint $table) {
                $table->text('id_card_img_one_fileImg')->comment('身份证正面');
            });
        }
        if (!Schema::hasColumn($name, 'id_card_img_two_fileImg')) {
            Schema::table($name, function (Blueprint $table) {
                $table->text('id_card_img_two_fileImg')->comment('身份证反面');
            });
        }
        if (!Schema::hasColumn($name, 'id_card_img_three_fileImg')) {
            Schema::table($name, function (Blueprint $table) {
                $table->text('id_card_img_three_fileImg')->comment('手持身份证上半身照');
            });
        }
        if (!Schema::hasColumn($name, 'commitment_fileImg')) {
            Schema::table($name, function (Blueprint $table) {
                $table->text('commitment_fileImg')->comment('个人承诺书');
            });
        }
        if (!Schema::hasColumn($name, 'mobile')) {
            Schema::table($name, function (Blueprint $table) {
                $table->text('mobile')->comment('联系电话');
            });
        }
    }

    /**
     * @throws Exception
     */
    private function clearCache()
    {
        cache()->flush();
    }
}
