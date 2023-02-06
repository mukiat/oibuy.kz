<?php

namespace App\Patch;

use App\Events\RunOtherModulesSeederEvent;
use App\Models\AdminAction;
use App\Models\ShopConfig;
use App\Models\TouchAd;
use App\Models\TouchAdPosition;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Migration_v2_6_0
{
    public function run()
    {
        try {
            $this->migration();
            $this->seed();
            $this->clean();
            // 执行其他模块seed
            event(new RunOtherModulesSeederEvent());
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    private function migration()
    {
        $this->sellerShopInfo();
        $this->couponsUser();
        $this->create_seckill_goods_attr();
    }

    private function seed()
    {
        ShopConfig::where('code', 'cache_time')->update([
            'type' => 'text'
        ]);

        ShopConfig::where('code', 'wap_logo')->update([
            'type' => 'file'
        ]);

        // 移除加密串权限
        AdminAction::where('action_code', 'merch_virualcard')->delete();

        // 移除拼图广告位
        $adPosition = [1000, 1001, 1002, 1004, 1005, 1006, 1008, 1009, 1010,
            1011, 1012, 1013, 1014, 1023, 1024, 1025, 1026, 1027, 1028, 1029,
            1030, 1031, 1032, 1033, 1034, 1035];
        TouchAdPosition::whereIn('position_id', $adPosition)->delete();
        TouchAd::whereIn('position_id', $adPosition)->delete();

        // 增加移动端版权
        $result = ShopConfig::where('code', 'copyright_text_mobile')->count();
        if (empty($result)) {
            $result = ShopConfig::where('code', 'copyright_text')->first();
            // 版权内容
            $rows = [
                'parent_id' => $result->parent_id,
                'code' => 'copyright_text_mobile',
                'value' => '由大商创提供技术支持',
                'type' => 'text',
                'store_range' => '',
                'sort_order' => 2,
                'shop_group' => ''
            ];
            ShopConfig::insert($rows);
        }

        $this->addWxappShopConfig();

        // 版本释放之前更新版本
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v2.6.0'
        ]);
    }

    private function clean()
    {
        cache()->flush();
    }

    private function sellerShopInfo()
    {
        $table = "seller_shopinfo";
        if (Schema::hasTable($table)) {
            if (Schema::hasColumn($table, 'kf_welcomeMsg')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->renameColumn('kf_welcomeMsg', 'kf_welcome_msg');
                });
            }
        }
    }

    /**
     * 更新优惠券编号长度
     */
    private function couponsUser()
    {
        if (Schema::hasTable('coupons_user')) {
            if (Schema::hasColumn('coupons_user', 'uc_sn')) {
                Schema::table('coupons_user', function (Blueprint $table) {
                    $table->string('uc_sn', 32)->change();
                });
            }
        }
    }

    /**
     * 秒杀商品属性表 seckill_goods_attr
     */
    public function create_seckill_goods_attr()
    {
        $name = 'seckill_goods_attr';
        if (!Schema::hasTable($name)) {
            Schema::create($name, function (Blueprint $table) {
                $table->increments('id')->comment('自增ID');
                $table->integer('seckill_goods_id')->unsigned()->default(0)->index('seckill_goods_id')->comment('秒杀活动商品id(关联seckill_goods表id)');
                $table->integer('goods_id')->unsigned()->default(0)->index('goods_id')->comment('商品id(关联goods表goods_id)');
                $table->integer('product_id')->unsigned()->default(0)->index('product_id')->comment('货品属性id(关联products货品表product_id)');
                $table->decimal('sec_price', 10, 2)->default(0)->comment('属性商品秒杀价格');
                $table->integer('sec_num')->unsigned()->default(0)->comment('属性商品秒杀库存');
                $table->integer('sec_limit')->unsigned()->default(0)->comment('属性商品秒杀限购数量');
            });

            $prefix = config('database.connections.mysql.prefix');
            DB::statement("ALTER TABLE `" . $prefix . "$name` comment '秒杀活动商品属性表'");
        }
    }

    private function addWxappShopConfig()
    {
        $wxapp_config = ShopConfig::where('code', 'wxapp_shop_config')->count();
        if (file_exists(MOBILE_WXAPP) && empty($wxapp_config)) {
            // 增加微信小程序配置
            $wxappId = ShopConfig::insertGetId([
                'parent_id' => 0,
                'code' => 'wxapp_shop_config',
                'type' => 'hidden',
                'store_range' => '',
                'store_dir' => '',
                'value' => '',
                'sort_order' => 1,
                'shop_group' => ''
            ]);

            ShopConfig::insert([
                [
                    'parent_id' => $wxappId,
                    'code' => 'wxapp_shop_status',
                    'type' => 'select',
                    'store_range' => '0,1',
                    'store_dir' => '',
                    'value' => '',
                    'sort_order' => 1,
                    'shop_group' => 'wxapp_shop_config'
                ]
            ]);
        }
    }
}
