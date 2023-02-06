<?php

namespace App\Patch;

use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Migration_v2_1_3
{
    public function run()
    {
        try {
            // 起始页客户服务显示
            $this->add_start_customer_service();
            // 会员余额提现设置项
            $this->add_balance_withdrawal();
            //新增自定义客服链接
            $this->add_start_service_url();

            $this->express();

            $this->seller_shopinfo();

            $this->touch_nav();

            $this->shopConfig();

        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function express()
    {
        $name = 'express';
        if (!Schema::hasTable($name)) {
            Schema::create($name, function (Blueprint $table) {
                $table->increments('id')->comment('自增ID');
                $table->string('name', 60)->comment('快递查询服务商名称');
                $table->string('code', 60)->comment('快递查询code');
                $table->string('description')->default('')->comment('快递查询说明');
                $table->text('express_configure')->comment('快递查询配置,序列化');
                $table->tinyInteger('enable')->unsigned()->default(0)->comment('启用状态：0 关闭 1 开启');
                $table->tinyInteger('default')->unsigned()->default(0)->comment('是否默认，0 否 1 是');
                $table->integer('add_time')->unsigned()->default(0)->comment('添加时间');
                $table->integer('update_time')->unsigned()->default(0)->comment('修改时间');
                $table->integer('sort')->unsigned()->default(50)->comment('排序');
            });

            $prefix = config('database.connections.mysql.prefix');
            DB::statement("ALTER TABLE `" . $prefix . "$name` comment '快递跟踪插件表'");
        }
        
        // 快递跟踪配置权限
        $count = DB::table('admin_action')->where('action_code', 'express_setting')->count();
        if (empty($count)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'third_party_service')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;

            DB::table('admin_action')->insert([
                'parent_id' => $parent_id,
                'action_code' => 'express_setting',
                'seller_show' => 0
            ]);
        }
        
    }

    public function seller_shopinfo()
    {
        $name = 'seller_shopinfo';

        if (!Schema::hasTable($name)) {
            return false;
        }
        if (!Schema::hasColumn($name, 'service_url')) {
            Schema::table($name, function (Blueprint $table) {
                $table->text('service_url')->nullable()->comment('自定义客服链接');
            });
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
            'value' => 'v2.1.3'
        ]);

        $this->clearCache();
    }

    /**
     * 起始页客户服务显示
     *
     * @throws \Exception
     */
    private function add_start_customer_service()
    {
        $parent_id = ShopConfig::where('code', 'display')->where('type', 'group')->value('id');
        if ($parent_id > 0) {
            $result = ShopConfig::where('code', 'enable_customer_service')->count();
            if (empty($result)) {
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'enable_customer_service',
                        'value' => '1',
                        'type' => 'select',
                        'store_range' => '1,0',
                        'sort_order' => 2
                    ]
                ];
                ShopConfig::insert($rows);
            }
        }
    }

    /**
     * 会员余额提现设置项
     *
     * @throws \Exception
     */
    private function add_balance_withdrawal()
    {
        $parent_id = ShopConfig::where('code', 'basic')->where('type', 'group')->value('id');
        if ($parent_id > 0) {
            $result = ShopConfig::where('code', 'user_balance_withdrawal')->count();
            if (empty($result)) {
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'user_balance_withdrawal',
                        'value' => '1',
                        'type' => 'select',
                        'store_range' => '1,0',
                        'sort_order' => 1
                    ]
                ];
                ShopConfig::insert($rows);
            }
        }
    }

    /**
     * 新增自定义客服链接
     *
     * @throws \Exception
     */
    private function add_start_service_url()
    {
        $parent_id = ShopConfig::where('code', 'shop_info')->where('type', 'group')->value('id');
        if ($parent_id > 0) {
            $result = ShopConfig::where('code', 'service_url')->count();
            if (empty($result)) {
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'service_url',
                        'value' => '',
                        'type' => 'text',
                        'store_range' => '',
                        'sort_order' => 1
                    ]
                ];
                ShopConfig::insert($rows);
            }
        }
    }

    /**
     * 自定义工具栏
     */
    private function touch_nav()
    {
        $result = DB::table('touch_nav')->where('device', '')->count();
        if (!empty($result)) {
            // 旧数据删除
            DB::table('touch_nav')->where('device', '')->delete();
        }

        // 增加H5工具栏分类
        $page_user_h5 = DB::table('touch_nav')->where('page', 'user')->where('device', 'h5')->count();
        if (empty($page_user_h5)) {
            $data = [
                'parent_id' => 0, 'device' => 'h5', 'name' => '全部工具', 'page' => 'user', 'ifshow' => 1, 'vieworder' => 1
            ];
            $parent_id = DB::table('touch_nav')->insertGetId($data);
            if ($parent_id > 0) {
                // 默认导航数据
                $rows = [
                    [
                        'parent_id' => $parent_id, 'device' => 'h5', 'name' => '收藏商品', 'page' => 'user', 'url' => '/#/user/collectionGoods', 'pic' => 'data/attached/nav/shoucang.png', 'ifshow' => 1, 'vieworder' => 1
                    ],
                    [
                        'parent_id' => $parent_id, 'device' => 'h5', 'name' => '关注店铺', 'page' => 'user', 'url' => '/#/user/collectionShop', 'pic' => 'data/attached/nav/store.png', 'ifshow' => 1, 'vieworder' => 2
                    ],
                    [
                        'parent_id' => $parent_id, 'device' => 'h5', 'name' => '我的分享', 'page' => 'user', 'url' => '/#/user/affiliate', 'pic' => 'data/attached/nav/share.png', 'ifshow' => 1, 'vieworder' => 3
                    ],
                    [
                        'parent_id' => $parent_id, 'device' => 'h5', 'name' => '我的众筹', 'page' => 'user', 'url' => '/#/crowdfunding', 'pic' => 'data/attached/nav/guanzhu.png', 'ifshow' => 1, 'vieworder' => 4,
                    ],
                    [
                        'parent_id' => $parent_id, 'device' => 'h5', 'name' => '浏览记录', 'page' => 'user', 'url' => '/#/user/history', 'pic' => 'data/attached/nav/history.png', 'ifshow' => 1, 'vieworder' => 5,
                    ],
                    [
                        'parent_id' => $parent_id, 'device' => 'h5', 'name' => '我的礼品卡', 'page' => 'user', 'url' => '/#/giftCard', 'pic' => 'data/attached/nav/gift_card.png', 'ifshow' => 1, 'vieworder' => 6,
                    ],
                    [
                        'parent_id' => $parent_id, 'device' => 'h5', 'name' => '我的拍卖', 'page' => 'user', 'url' => '/#/user/auction', 'pic' => 'data/attached/nav/auction.png', 'ifshow' => 1, 'vieworder' => 7,
                    ],
                    [
                        'parent_id' => $parent_id, 'device' => 'h5', 'name' => '商家入驻', 'page' => 'user', 'url' => '/#/user/merchants', 'pic' => 'data/attached/nav/merchants.png', 'ifshow' => 1, 'vieworder' => 8,
                    ]
                ];
                DB::table('touch_nav')->insert($rows);
            }
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
