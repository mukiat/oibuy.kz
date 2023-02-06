<?php

use App\Models\ShopConfig;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 小程序模块数据填充
 * Class WeappModuleSeeder
 */
class WeappModuleSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->touchAdPosition();
        $this->touchAd();
        // 小程序权限
        $this->adminAction();

        $this->changeWxappTemplate();

        // 兼容旧表更新小程序id
        $this->sync_wxapp_id();

        // 小程序自定义工具栏
        $this->touch_nav();

        $this->shopConfig();
    }


    private function touchAdPosition()
    {
        $result_1 = DB::table('touch_ad_position')->whereBetween('position_id', [1022, 1035])->count();
        if (empty($result_1)) {

            // 插入新数据
            $rows = [
                [
                    'position_id' => '1022',
                    'position_name' => '小程序砍价首页banner',
                    'ad_width' => '360',
                    'ad_height' => '168',
                    'position_desc' => '',
                    'position_style' => '{foreach $ads as $ad}<div class="swiper-slide">{$ad}</div>{/foreach}',
                    'is_public' => '0',
                    'theme' => 'ecmoban_dsc',
                    'tc_id' => '0',
                    'tc_type' => '',
                    'ad_type' => 'wxapp'
                ],
            ];
            DB::table('touch_ad_position')->insert($rows);
        }
    }

    private function touchAd()
    {
        $result = DB::table('touch_ad')->whereBetween('position_id', [1022, 1035])->count();
        if (empty($result)) {
            // 插入新数据
            $rows = [
                [
                    'position_id' => '1022',
                    'media_type' => '0',
                    'ad_name' => '小程序砍价首页banner-01',
                    'ad_link' => '',
                    'ad_code' => '1509663779787829146.jpg',
                    'start_time' => '1518197575',
                    'end_time' => '1637530979',
                    'enabled' => '1',
                ],
            ];
            DB::table('touch_ad')->insert($rows);
        }
    }


    private function adminAction()
    {
        $result = DB::table('admin_action')->where('action_code', 'wxapp')->count();
        if (empty($result)) {
            // 默认数据
            $row = [
                'parent_id' => 0,
                'action_code' => 'wxapp',
                'seller_show' => 0
            ];
            $action_id = DB::table('admin_action')->insertGetId($row);

            // 默认数据
            $rows = [
                [
                    'parent_id' => $action_id,
                    'action_code' => 'wxapp_wechat_config',
                    'seller_show' => 0
                ],
                [
                    'parent_id' => $action_id,
                    'action_code' => 'wxapp_template',
                    'seller_show' => 0
                ]
            ];
            DB::table('admin_action')->insert($rows);
        }

        $parent_id = DB::table('admin_action')->where('action_code', 'wxapp')->value('action_id');
        $parent_id = $parent_id ?? 0;

        // 增加 小程序直播商品权限控制
        $result = DB::table('admin_action')->where('action_code', 'wxapp_live_goods')->count();
        if (empty($result)) {
            if ($parent_id > 0) {
                $row = [
                    'parent_id' => $parent_id,
                    'action_code' => 'wxapp_live_goods',
                    'seller_show' => 0
                ];
                DB::table('admin_action')->insert($row);
            }
        }

        // 直播间权限
        $result = DB::table('admin_action')->where('action_code', 'wxapp_live_room')->count();
        if (empty($result)) {
            if ($parent_id > 0) {
                $row = [
                    'parent_id' => $parent_id,
                    'action_code' => 'wxapp_live_room',
                    'seller_show' => 0
                ];
                DB::table('admin_action')->insert($row);
            }
        }

        // 增加 小程序可视化权限
        $result = DB::table('admin_action')->where('action_code', 'wxapp_touch_visual')->count();
        if (empty($result)) {
            if ($parent_id > 0) {
                $row = [
                    'parent_id' => $parent_id,
                    'action_code' => 'wxapp_touch_visual',
                    'seller_show' => 0
                ];
                DB::table('admin_action')->insert($row);
            }
        }

        // 小程序自定义工具栏
        $count = DB::table('admin_action')->where('action_code', 'wxapp_touch_nav_admin')->count();
        if (empty($count)) {
            if ($parent_id > 0) {
                DB::table('admin_action')->insert([
                    'parent_id' => $parent_id,
                    'action_code' => 'wxapp_touch_nav_admin',
                    'seller_show' => 0
                ]);
            }
        }

        // 小程序设置
        $count = DB::table('admin_action')->where('action_code', 'wxapp_setting')->count();
        if (empty($count)) {
            if ($parent_id > 0) {
                DB::table('admin_action')->insert([
                    'parent_id' => $parent_id,
                    'action_code' => 'wxapp_setting',
                    'seller_show' => 0
                ]);
            }
        }

        // 小程序 发现页权限
        $touch_page_nav = DB::table('admin_action')->where('parent_id', $parent_id)->where('action_code', 'touch_page_nav')->count();
        if (!empty($touch_page_nav)) {
            DB::table('admin_action')->where('parent_id', $parent_id)->where('action_code', 'touch_page_nav')->update(['action_code' => 'wxapp_touch_page_nav']);
        }
        $count = DB::table('admin_action')->where('action_code', 'wxapp_touch_page_nav')->count();
        if (empty($count)) {
            if ($parent_id > 0) {
                DB::table('admin_action')->insert([
                    'parent_id' => $parent_id,
                    'action_code' => 'wxapp_touch_page_nav',
                    'seller_show' => 0
                ]);
            }
        }

        // 小程序广告位权限
        $count = DB::table('admin_action')->where('action_code', 'wxapp_ad_position')->count();
        if (empty($count)) {
            if ($parent_id > 0) {
                DB::table('admin_action')->insert([
                    'parent_id' => $parent_id,
                    'action_code' => 'wxapp_ad_position',
                    'seller_show' => 0
                ]);
            }
        }
    }

    /**
     * 删除无用权限
     */
    protected function deleteAction()
    {
        // 删除旧数据
        DB::table('admin_action')->where('action_code', 'wxapp_config')->delete();
        DB::table('admin_action')->where('action_code', 'wxapp_live')->delete();
    }

    /**
     * v1.4.7 更换小程序模板消息为订阅消息
     */
    private function changeWxappTemplate()
    {
        // 开团成功提醒 + 参团成功提醒
        $tuan = DB::table('wxapp_template')->where('wx_code', '5008')->count();
        if (empty($tuan)) {
            $data = [
                'wx_wechat_id' => 1,
                'wx_code' => '5008',
                'wx_template' => "拼团商品 {{thing1.DATA}}\r\n拼团进度 {{thing3.DATA}}\r\n拼团价格 {{amount5.DATA}}\r\n成团人数 {{number7.DATA}}\r\n温馨提示 {{thing6.DATA}}",
                'wx_keyword_id' => '1,3,5,7,6',
                'wx_title' => '拼团进度通知',
                'wx_content' => '开团成功，赶快拉人来拼团吧',
            ];
            DB::table('wxapp_template')->insert($data);
        }

        // 砍价成功通知
        $kan = DB::table('wxapp_template')->where('wx_code', '4875')->count();
        if (empty($kan)) {
            $data = [
                'wx_wechat_id' => 1,
                'wx_code' => '4875',
                'wx_template' => "砍价进度 {{phrase1.DATA}}\r\n商品名称 {{thing2.DATA}}\r\n商品原价 {{amount3.DATA}}\r\n当前价 {{amount4.DATA}}\r\n温馨提示 {{thing5.DATA}}",
                'wx_keyword_id' => '1,2,3,4,5',
                'wx_title' => '砍价进度通知',
                'wx_content' => '砍价成功，您可以以当前价购买，或者再继续邀请好友砍至底价购买哦',
            ];
            DB::table('wxapp_template')->insert($data);
        }

        // 原消息 已废弃
        DB::table('wxapp_template')->where('wx_code', 'AT0541')->delete();
        DB::table('wxapp_template')->where('wx_code', 'AT1173')->delete();
        DB::table('wxapp_template')->where('wx_code', 'AT0933')->delete();
    }

    /**
     * 兼容旧表更新小程序id
     */
    protected function sync_wxapp_id()
    {
        DB::table('wxapp_template')->where('wxapp_id', 0)->update(['wxapp_id' => 1]);
        DB::table('wxapp_live_goods')->where('wxapp_id', 0)->update(['wxapp_id' => 1]);
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

        // 增加小程序工具栏分类
        $page_user_wxapp = DB::table('touch_nav')->where('page', 'user')->where('device', 'wxapp')->count();
        if (empty($page_user_wxapp)) {
            $data = [
                'parent_id' => 0, 'device' => 'wxapp', 'name' => '全部工具', 'page' => 'user', 'ifshow' => 1, 'vieworder' => 1
            ];
            $parent_id = DB::table('touch_nav')->insertGetId($data);
            if ($parent_id > 0) {
                // 默认导航数据
                $rows = [
                    [
                        'parent_id' => $parent_id, 'device' => 'wxapp', 'name' => '收藏的商品', 'page' => 'user', 'url' => config('route.user.collect_goods'), 'pic' => 'data/attached/nav/shoucang.png', 'ifshow' => 1, 'vieworder' => 1
                    ],
                    [
                        'parent_id' => $parent_id, 'device' => 'wxapp', 'name' => '关注的店铺', 'page' => 'user', 'url' => config('route.user.collect_shop'), 'pic' => 'data/attached/nav/store.png', 'ifshow' => 1, 'vieworder' => 2
                    ],
                    [
                        'parent_id' => $parent_id, 'device' => 'wxapp', 'name' => '我的分享', 'page' => 'user', 'url' => config('route.user.affiliate'), 'pic' => 'data/attached/nav/share.png', 'ifshow' => 1, 'vieworder' => 3
                    ],
                    [
                        'parent_id' => $parent_id, 'device' => 'wxapp', 'name' => '浏览记录', 'page' => 'user', 'url' => config('route.user.history'), 'pic' => 'data/attached/nav/guanzhu.png', 'ifshow' => 1, 'vieworder' => 4,
                    ],
                    [
                        'parent_id' => $parent_id, 'device' => 'wxapp', 'name' => '礼品卡', 'page' => 'user', 'url' => config('route.giftcard.index'), 'pic' => 'data/attached/nav/package.png', 'ifshow' => 1, 'vieworder' => 5,
                    ],
                    [
                        'parent_id' => $parent_id, 'device' => 'wxapp', 'name' => '我的拍卖', 'page' => 'user', 'url' => config('route.auction.index'), 'pic' => 'data/attached/nav/auction.png', 'ifshow' => 1, 'vieworder' => 6,
                    ],
                ];
                DB::table('touch_nav')->insert($rows);
            }
        }
    }

    private function shopConfig()
    {
        $wxapp_config = \App\Models\ShopConfig::where('code', 'wxapp_config')->count();
        if (empty($wxapp_config)) {
            // 增加微信小程序配置
            $wxappId = ShopConfig::insertGetId([
                'parent_id' => 0,
                'code' => 'wxapp_config',
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
                    'code' => 'wxapp_top_img',
                    'type' => 'file',
                    'store_range' => '',
                    'store_dir' => 'images/common/',
                    'value' => '',
                    'sort_order' => 1,
                    'shop_group' => 'wxapp_config'
                ],
                [
                    'parent_id' => $wxappId,
                    'code' => 'wxapp_top_url',
                    'type' => 'text',
                    'store_range' => '',
                    'store_dir' => '',
                    'value' => '',
                    'sort_order' => 2,
                    'shop_group' => 'wxapp_config'
                ]
            ]);
        }
    }
}
