<?php

namespace App\Patch;

use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Migration_v1_6_0
{
    public function run()
    {
        try {
            $this->orderInfo();
            $this->orderGoods();
            $this->add_pay_group();
            $this->shopConfig();
            $this->addGetBonusOrderIdToUserBonus();
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * 增加支付模块分组
     */
    protected function add_pay_group()
    {
        $result = ShopConfig::where('code', 'pay')->where('type', 'group')->count();
        if (empty($result)) {
            // 默认数据
            $rows = [
                [
                    'parent_id' => 0,
                    'code' => 'pay',
                    'value' => 0,
                    'type' => 'group',
                    'store_range' => '',
                    'sort_order' => 2,
                    'shop_group' => ''
                ]
            ];
            ShopConfig::insert($rows);
        }

        // 转移至支付模块下
        $parent_id = ShopConfig::where('code', 'pay')->value('id');
        $parent_id = !empty($parent_id) ? $parent_id : 0;
        if ($parent_id > 0) {
            $pay_code = [
                'use_integral',
                'use_pay_fee',
                'use_bonus',
                'use_surplus',
                'use_value_card',
                'use_coupons',
                'use_paypwd',
                'pay_effective_time'
            ];
            ShopConfig::whereIn('code', $pay_code)->where('parent_id', '<>', $parent_id)->update(['parent_id' => $parent_id]);
        }
    }

    /**
     * 更新版本
     * @throws Exception
     */
    private function shopConfig()
    {
        /* 开启IP库存类型选择（默认IP库） */
        $type = ShopConfig::where('code', 'ip_type')->value('type');
        if ($type && $type == 'hidden') {
            /* 开启IP库存类型选择（默认IP库） */
            ShopConfig::where('code', 'ip_type')->update([
                'type' => 'select',
                'store_range' => '0,1',
                'value' => 0
            ]);
        }

        //新增支付手续费启用开关 (购物流程配置)
        $result = ShopConfig::where('code', 'use_pay_fee')->count();
        if (empty($result)) {
            $parent_id = ShopConfig::where('code', 'shopping_flow')->value('id');
            $parent_id = !empty($parent_id) ? $parent_id : 0;

            // 默认数据
            $rows = [
                [
                    'parent_id' => $parent_id,
                    'code' => 'use_pay_fee',
                    'value' => 0,
                    'type' => 'select',
                    'store_range' => '1,0',
                    'sort_order' => 2,
                    'shop_group' => ''
                ]
            ];
            ShopConfig::insert($rows);
        }

        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v1.6.0'
        ]);

        $this->clearCache();
    }

    private function addGetBonusOrderIdToUserBonus()
    {
        $table_name = 'user_bonus';
        if (!Schema::hasTable($table_name)) {
            return false;
        }
        if (!Schema::hasColumn($table_name, 'return_order_id')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->integer('return_order_id')->default(0)->comment('通过该order_id得到的红包');
                $table->integer('return_goods_id')->default(0)->comment('通过该goods_id得到的红包,按商品发放用到');
            });
        }
    }

    private function orderInfo()
    {
        $table_name = 'order_info';
        if (!Schema::hasTable($table_name)) {
            return false;
        }
        if (!Schema::hasColumn($table_name, 'dis_amount')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->decimal('dis_amount', 10, 2)->default(0.00)->comment('商品满减优惠总金额');
            });
        }
    }

    public function orderGoods()
    {
        $table_name = 'order_goods';
        if (!Schema::hasTable($table_name)) {
            return false;
        }
        if (!Schema::hasColumn($table_name, 'dis_amount')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->decimal('dis_amount', 10, 2)->default(0.00)->comment('商品满减优惠金额');
            });
        }
    }

    /**
     * @throws \Exception
     */
    private function clearCache()
    {
        cache()->flush();
    }
}
