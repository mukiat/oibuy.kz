<?php

namespace App\Patch;

use App\Events\RunOtherModulesSeederEvent;
use App\Models\ShopConfig;
use App\Repositories\Common\TimeRepository;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Migration_v2_5_2
{
    public function run()
    {
        try {
            $this->goods_services_label();
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
        $this->order_info();
        $this->seller_shopinfo();
        $this->touch_nav();
        $this->coupons();
    }

    private function order_info()
    {
        $name = "order_info";
        if (!Schema::hasTable($name)) {
            return false;
        }

        if (Schema::hasColumn($name, 'shipping_dateStr')) {
            Schema::table($name, function (Blueprint $table) {
                $table->renameColumn('shipping_dateStr', 'shipping_date_str');
            });
        }
    }

    private function seller_shopinfo()
    {
        $name = "seller_shopinfo";
        if (!Schema::hasTable($name)) {
            return false;
        }

        if (Schema::hasColumn($name, 'kf_welcomeMsg')) {
            Schema::table($name, function (Blueprint $table) {
                $table->renameColumn('kf_welcomeMsg', 'kf_welcome_msg');
            });
        }
    }

    private function touch_nav()
    {
        if (!Schema::hasTable('touch_page_nav')) {
            Schema::create('touch_page_nav', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('ru_id')->comment('店铺ID');
                $table->string('device')->comment('客户端类型：app，applate，h5');
                $table->string('page_name')->comment('页面名称：默认为发现页（discover）');
                $table->string('nav_name')->comment('导航名称');
                $table->string('nav_key')->comment('导航key');
                $table->string('nav_url')->comment('导航连接');
                $table->unsignedInteger('sort')->comment('排序');
                $table->boolean('display')->comment('是否显示');
            });
        }
    }

    private function seed()
    {
        ShopConfig::where('code', 'server_model')->update([
            'type' => 'select',
            'shop_group' => 'cloud'
        ]);


        if (file_exists(MOBILE_GROUPBUY)) {
            $this->updateCgroupShopConfig();
        }

        // 增加权限
        $touch_page_nav = DB::table('admin_action')->where('action_code', 'touch_page_nav')->count();
        if (empty($touch_page_nav)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'ectouch')->value('action_id');
            DB::table('admin_action')->insertGetId([
                'parent_id' => $parent_id,
                'action_code' => 'touch_page_nav',
                'relevance' => '',
                'seller_show' => 0,
            ]);

            if (file_exists(MOBILE_WXAPP)) {
                $parent_id = DB::table('admin_action')->where('action_code', 'wxapp')->value('action_id');
                DB::table('admin_action')->insertGetId([
                    'parent_id' => $parent_id,
                    'action_code' => 'touch_page_nav',
                    'relevance' => '',
                    'seller_show' => 0,
                ]);
            }

            if (file_exists(MOBILE_APP)) {
                $parent_id = DB::table('admin_action')->where('action_code', 'app')->value('action_id');
                DB::table('admin_action')->insertGetId([
                    'parent_id' => $parent_id,
                    'action_code' => 'touch_page_nav',
                    'relevance' => '',
                    'seller_show' => 0,
                ]);
            }
        }

        // 增加发现页默认数据
        $pageNav = DB::table('touch_page_nav')->count();
        if (empty($pageNav)) {
            DB::table('touch_page_nav')->insert([
                [
                    'ru_id' => 0,
                    'device' => 'h5',
                    'page_name' => 'discover',
                    'nav_name' => '社区',
                    'nav_key' => 'community',
                    'nav_url' => '/integration?type=0',
                    'sort' => 0,
                    'display' => 1,
                ], [
                    'ru_id' => 0,
                    'device' => 'h5',
                    'page_name' => 'discover',
                    'nav_name' => '店铺街',
                    'nav_key' => 'shop',
                    'nav_url' => '/integration?type=1',
                    'sort' => 0,
                    'display' => 1,
                ], [
                    'ru_id' => 0,
                    'device' => 'h5',
                    'page_name' => 'discover',
                    'nav_name' => '视频',
                    'nav_key' => 'video',
                    'nav_url' => '/integration?type=2',
                    'sort' => 0,
                    'display' => 1,
                ],
                // wxapp
                [
                    'ru_id' => 0,
                    'device' => 'wxapp',
                    'page_name' => 'discover',
                    'nav_name' => '社区',
                    'nav_key' => 'community',
                    'nav_url' => '0',
                    'sort' => 0,
                    'display' => 1,
                ], [
                    'ru_id' => 0,
                    'device' => 'wxapp',
                    'page_name' => 'discover',
                    'nav_name' => '店铺街',
                    'nav_key' => 'shop',
                    'nav_url' => '1',
                    'sort' => 0,
                    'display' => 1,
                ], [
                    'ru_id' => 0,
                    'device' => 'wxapp',
                    'page_name' => 'discover',
                    'nav_name' => '视频',
                    'nav_key' => 'video',
                    'nav_url' => '2',
                    'sort' => 0,
                    'display' => 1,
                ], [
                    'ru_id' => 0,
                    'device' => 'wxapp',
                    'page_name' => 'discover',
                    'nav_name' => '直播',
                    'nav_key' => 'live',
                    'nav_url' => '3',
                    'sort' => 0,
                    'display' => 1,
                ],
                // app
                [
                    'ru_id' => 0,
                    'device' => 'app',
                    'page_name' => 'discover',
                    'nav_name' => '社区',
                    'nav_key' => 'community',
                    'nav_url' => '0',
                    'sort' => 0,
                    'display' => 1,
                ], [
                    'ru_id' => 0,
                    'device' => 'app',
                    'page_name' => 'discover',
                    'nav_name' => '店铺街',
                    'nav_key' => 'shop',
                    'nav_url' => '1',
                    'sort' => 0,
                    'display' => 1,
                ], [
                    'ru_id' => 0,
                    'device' => 'app',
                    'page_name' => 'discover',
                    'nav_name' => '视频',
                    'nav_key' => 'video',
                    'nav_url' => '2',
                    'sort' => 0,
                    'display' => 1,
                ],
            ]);
        }

        ShopConfig::where('code', 'enable_gzip')->update([
            'type' => 'hidden'
        ]);

        // 版本释放之前更新版本
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v2.5.2'
        ]);
    }

    private function clean()
    {
        cache()->flush();
    }

    /**
     * 商品服务标签
     * @throws Exception
     */
    private function goods_services_label()
    {
        $name = 'goods_services_label';
        if (!Schema::hasTable($name)) {
            Schema::create('goods_services_label', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('自增ID');
                $table->string('label_name')->default('')->comment('标签名称');
                $table->string('label_explain')->default('')->comment('标签说明');
                $table->string('label_code')->default('')->comment('标签编码');
                $table->string('label_image')->default('')->comment('标签图片');
                $table->integer('sort')->default(50)->comment('标签排序');
                $table->tinyInteger('merchant_use')->unsigned()->default(0)->comment('商家可用：0 否， 1 是');
                $table->integer('bind_goods_number')->unsigned()->default(0)->comment('标签绑定商品数量');
                $table->tinyInteger('status')->unsigned()->default(0)->comment('标签状态：0 关闭， 1 开启');
                $table->integer('add_time')->default(0)->comment('添加时间');
            });

            $prefix = config('database.connections.mysql.prefix');
            DB::statement("ALTER TABLE `" . $prefix . "$name` comment '商品服务标签表'");
        }

        $name = 'goods_use_services_label';
        if (!Schema::hasTable($name)) {
            Schema::create('goods_use_services_label', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('自增ID');
                $table->string('label_id')->default(0)->comment('标签ID');
                $table->string('goods_id')->default(0)->comment('商品ID');
                $table->integer('add_time')->default(0)->comment('添加时间');
            });

            $prefix = config('database.connections.mysql.prefix');
            DB::statement("ALTER TABLE `" . $prefix . "$name` comment '商品服务标签使用表'");
        }

        // 活动标签权限
        $action_id = DB::table('admin_action')->where('action_code', 'goods_services_label')->value('action_id');
        $action_id = $action_id ? $action_id : 0;

        if ($action_id < 1) {
            $parent_id = DB::table('admin_action')->where('action_code', 'goods')->value('action_id');

            DB::table('admin_action')->insert([
                'parent_id' => $parent_id,
                'action_code' => 'goods_services_label',
                'seller_show' => 1
            ]);
        }

        //
        $result = DB::table('goods_services_label')->where('label_code', 'no_reason_return')->count();

        if (empty($result)) { // 默认数据
            $rows = [
                'label_name' => '7天无理由退货',
                'label_code' => 'no_reason_return',
                'label_image' => 'images/service_icon_1.png',
                'merchant_use' => '1',
                'status' => '1',
                'add_time' => TimeRepository::getGmTime(),
            ];

            DB::table('goods_services_label')->insert($rows);
        }
    }

    protected static function updateCgroupShopConfig()
    {
        // 增加社区团购模块分组
        $parent_id = DB::table('shop_config')->where('code', 'cgroup')->value('id');
        $parent_id = $parent_id ?? 0;
        if (empty($parent_id)) {
            // 默认数据
            $rows = [
                'parent_id' => 0,
                'code' => 'cgroup',
                'value' => 0,
                'type' => 'hidden',
                'store_range' => '',
                'sort_order' => 50,
                'shop_group' => ''
            ];
            $parent_id = DB::table('shop_config')->insertGetId($rows);
        }

        if ($parent_id > 0) {
            $recruit_leader = DB::table('shop_config')->where('code', 'recruit_leader')->count(); // 招募团长 dsc_shop_config 配置
            if (empty($recruit_leader)) {
                $rows = [
                    'parent_id' => $parent_id,
                    'code' => 'recruit_leader',
                    'type' => 'hidden',
                    'value' => 'a:4:{s:5:"title";s:15:"团长招募令";s:4:"link";s:43:"http://www.hbf.dscmall.zhuo/admin/index.php";s:7:"content";s:1095:"<ol class=" list-paddingleft-2" style="list-style-type: decimal;"><li><p>什么是团长？想成为团长需要具备什么条件？</p><p>商家通过线下或者线上推广的方式在各个社区招募团长，利用社交软件组成团长群，之后可定时发布团购活动，团长再将活动分享转发到自己维护的社区群中，活动结束之后商家发货给团长，团长安排社区的买家自提或团长配送，过程中团不会接触到买家支付的钱，也不需要囤货，利润会在团购结束后自动结算给团长。</p></li><li><p>什么是团长？想成为团长需要具备什么条件？</p><p>商家通过线下或者线上推广的方式在各个社区招募团长，利用社交软件组成团长群，之后可定时发布团购活动，团长再将活动分享转发到自己维护的社区群中，活动结束之后商家发货给团长，团长安排社区的买家自提或团长配送，过程中团不会接触到买家支付的钱，也不需要囤货，利润会在团购结束后自动结算给团长。</p><p><br/></p></li></ol>";s:4:"file";s:70:"data/groupbuy_recruit_img/w9RsEysI8x3zJ47Iy4KcA34PY17GsSI4Fxudeex6.png";}',
                    'store_range' => '',
                    'shop_group' => 'leader',
                ];
                DB::table('shop_config')->insert($rows);
            } else {
                DB::table('shop_config')->where('code', 'recruit_leader')->where('parent_id', '<>', $parent_id)->update(['parent_id' => $parent_id]);
            }

            $leader_timeliness = DB::table('shop_config')->where('code', 'leader_timeliness')->count(); // 社区团购举报时效 dsc_shop_config 配置
            if (empty($leader_timeliness)) {
                $rows = [
                    'parent_id' => $parent_id,
                    'code' => 'leader_timeliness',
                    'type' => 'text',
                    'value' => '15',
                    'store_range' => '',
                    'shop_group' => 'leader_report',
                ];
                DB::table('shop_config')->insert($rows);
            } else {
                DB::table('shop_config')->where('code', 'leader_timeliness')->where('parent_id', '<>', $parent_id)->update(['parent_id' => $parent_id]);
            }

            $need_agress_refound = DB::table('shop_config')->where('code', 'need_agress_refound')->count(); // 社区团购商家退款是否审核 dsc_shop_config 配置
            if (empty($need_agress_refound)) {
                $rows = [
                    'parent_id' => $parent_id,
                    'code' => 'need_agress_refound',
                    'type' => 'select',
                    'value' => '0',
                    'store_range' => '0,1',
                    'shop_group' => 'return_setting',
                ];
                DB::table('shop_config')->insert($rows);
            } else {
                DB::table('shop_config')->where('code', 'need_agress_refound')->where('parent_id', '<>', $parent_id)->update(['parent_id' => $parent_id]);
            }

            $refound_money_type = DB::table('shop_config')->where('code', 'refound_money_type')->count(); // 社区团购优先退款方式 dsc_shop_config 配置
            if (empty($refound_money_type)) {
                $rows = [
                    'parent_id' => $parent_id,
                    'code' => 'refound_money_type',
                    'type' => 'options',
                    'value' => '1',
                    'store_range' => '1,2,6',
                    'shop_group' => 'return_setting',
                ];
                DB::table('shop_config')->insert($rows);
            } else {
                DB::table('shop_config')->where('code', 'refound_money_type')->where('parent_id', '<>', $parent_id)->update(['parent_id' => $parent_id, 'store_range' => '1,2,6', 'value' => '1']);
            }
        }


        $parent_id = DB::table('shop_config')->where('code', 'recruit_leader')->value('parent_id');
        $parent_id = $parent_id ?? 0;

        // 开启社区驿站
        $post = DB::table('shop_config')->where('code', 'open_community_post')->value('id'); // 开启社区驿站
        if (empty($post)) {
            // 默认数据
            $rows = [
                [
                    'parent_id' => $parent_id,
                    'code' => 'open_community_post',
                    'type' => 'hidden',
                    'value' => '0',
                    'shop_group' => 'community',
                ]
            ];
            DB::table('shop_config')->insert($rows);
        } else {
            DB::table('shop_config')->where('id', $post)->where('parent_id', '<>', $parent_id)->update(['parent_id' => $parent_id]);
        }

        // 开启社区朋友圈
        $circles = DB::table('shop_config')->where('code', 'open_community_circles')->value('id');
        if (empty($circles)) {
            // 默认数据
            $rows = [
                [
                    'parent_id' => $parent_id,
                    'code' => 'open_community_circles',
                    'type' => 'hidden',
                    'value' => '0',
                    'shop_group' => 'community',
                ]
            ];
            DB::table('shop_config')->insert($rows);
        } else {
            DB::table('shop_config')->where('id', $circles)->where('parent_id', '<>', $parent_id)->update(['parent_id' => $parent_id]);
        }

        DB::table('shop_config')->where('code', 'cgroup')->where('type', 'group')->update(['type' => 'hidden']);
    }

    public function coupons()
    {
        $name = 'coupons';
        if (!Schema::hasTable($name)) {
            return false;
        }

        if (Schema::hasColumn($name, 'ru_id')) {
            Schema::table($name, function (Blueprint $table) {
                $table->integer('ru_id')->change();
            });
        }
    }
}
