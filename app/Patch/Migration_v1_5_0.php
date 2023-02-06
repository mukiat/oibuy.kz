<?php

namespace App\Patch;

use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Migration_v1_5_0
{
    public function run()
    {
        if (!Schema::hasTable('merchants_steps_fields')) {
            return false;
        }
        if (Schema::hasColumn('merchants_steps_fields', 'companyName')) {
            Schema::table('merchants_steps_fields', function (Blueprint $table) {
                $table->renameColumn('companyName', 'company');
            });
        }

        $this->updateMerchantField();

        $this->order();

        $this->sellerShopinfo();

        $this->bookingGoods();
        $this->merchantsShopBrand();
        $this->merchantsCategoryTemporarydate();
        if (file_exists(MOBILE_WXAPP)) {
            $this->changeWxappTemplate();
        }

        try {
            $this->shopConfig();
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * 更新商家入驻公司名称字段
     */
    private function updateMerchantField()
    {
        $textFields = DB::table('merchants_steps_fields_centent')->where('id', 19)->value('textFields');

        DB::table('merchants_steps_fields_centent')->where('id', 19)->update(
            ['textFields' => str_replace('companyName', 'company', $textFields)]
        );
    }

    public function bookingGoods()
    {
        $tableName = 'booking_goods';

        if (!Schema::hasTable($tableName)) {
            return false;
        }

        if (!Schema::hasColumn($tableName, 'ru_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('ru_id')->index('ru_id')->default(0)->comment('店铺ID');
            });
        }
    }

    public function order()
    {
        $table = 'trade_snapshot';

        if (!Schema::hasTable($table)) {
            return false;
        }

        if (!Schema::hasColumn($table, 'order_id')) {
            Schema::table($table, function (Blueprint $table) {
                $table->integer('order_id')->index('order_id')->default(0)->comment('订单ID')->after('trade_id');
            });
        }
    }

    /**
     * v1.4.7 更换小程序模板消息为订阅消息
     */
    protected function changeWxappTemplate()
    {
        $tuan = DB::table('wxapp_template')->where('wx_code', '5008')->count();
        if (empty($tuan)) {
            // 开团成功提醒 AT0541
            $data = [
                'wx_wechat_id' => 1,
                'wx_code' => '5008',
                'wx_template' => "拼团商品 {{thing1.DATA}}\r\n拼团进度 {{thing3.DATA}}\r\n拼团价格 {{amount5.DATA}}\r\n成团人数 {{number7.DATA}}\r\n温馨提示 {{thing6.DATA}}",
                'wx_keyword_id' => '1,3,5,7,6',
                'wx_title' => '拼团进度通知',
                'wx_content' => '开团成功，赶快拉人来拼团吧',
            ];
            DB::table('wxapp_template')->where('wx_code', 'AT0541')->update($data);

            // 参团成功提醒 AT0933
            DB::table('wxapp_template')->where('wx_code', 'AT0933')->delete();
        }

        // 砍价成功通知 AT1173
        $kan = DB::table('wxapp_template')->where('wx_code', '4875')->count();
        if (empty($kan)) {
            $data = [
                'wx_wechat_id' => 1,
                'wx_code' => '4875',
                'wx_template' => "砍价进度 {{phrase1.DATA}}\r\n商品名称 {{thing2.DATA}}\r\n商品原价 {{amount3.DATA}}\r\n当前价 {{amount4.DATA}}\r\n温馨提示 {{thing5.DATA}}",
                'wx_keyword_id' => '1,2,3,4,5',
                'wx_title' => '砍价成功通知',
                'wx_content' => '砍价成功，您可以以当前价购买，或者再继续邀请好友砍至底价购买哦',
            ];
            DB::table('wxapp_template')->where('wx_code', 'AT1173')->update($data);
        }
    }

    /**
     * 更新版本
     * @throws Exception
     */
    private function shopConfig()
    {

        /**
         * 新增分类商品属性筛选类型选择
         *
         * value：1-属性只要符合存在，2-属性要全部同时符合存在
         */
        $count = ShopConfig::where('code', 'cat_attr_search')->count();

        if (empty($count)) {
            $parent_id = ShopConfig::where('code', 'extend_basic')->value('id');
            $parent_id = !empty($parent_id) ? $parent_id : 0;

            ShopConfig::insert([
                'code' => 'cat_attr_search',
                'parent_id' => $parent_id,
                'type' => 'hidden',
                'value' => '1'
            ]);
        }

        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v1.5.0'
        ]);

        cache()->forget('shop_config');
    }

    public function sellerShopinfo()
    {
        $table = 'seller_shopinfo';

        if (!Schema::hasTable($table)) {
            return false;
        }

        if (!Schema::hasColumn($table, 'shop_desc')) {
            Schema::table($table, function (Blueprint $table) {
                $table->text('shop_desc')->comment('店铺描述');
            });
        }
    }

    /**
     *  品牌表新增字段admin_id用于区分是哪个用户加的
     */
    public function merchantsShopBrand()
    {
        $table = 'merchants_shop_brand';
        if (Schema::hasTable($table)) {
            if (!Schema::hasColumn($table, 'admin_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->integer('admin_id')->index('admin_id')->default(0)->comment('区别是这条数据是那个用户的');
                });
            }
        }
    }

    /**
     *  可经营类目表新增字段admin_id用于区分是哪个用户加的
     */
    public function merchantsCategoryTemporarydate()
    {
        $table = 'merchants_category_temporarydate';
        if (Schema::hasTable($table)) {
            if (!Schema::hasColumn($table, 'admin_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->integer('admin_id')->index('admin_id')->default(0)->comment('区别是这条数据是那个用户的');
                });
            }
        }
    }
}
