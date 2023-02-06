<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * APP模块数据填充
 * Class AppModuleSeeder
 */
class AppModuleSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 广告默认数据
        $this->appAdPosition();
        $this->adminAction();

        // 处理 ecjia app登录旧用户数据
        $this->handleConnectUser();

        // app自定义工具栏
        $this->touch_nav();
    }

    /**
     * 广告位
     */
    private function appAdPosition()
    {
        $result = DB::table('app_ad_position')->where('position_type', 'loading_screen')->count();
        if (empty($result)) {
            // 插入新数据
            $row = [
                'position_name' => 'app启动加载页',
                'ad_width' => '750',
                'ad_height' => '1920',
                'position_desc' => 'app启动加载页banner',
                'position_type' => 'loading_screen',
            ];
            $position_id = DB::table('app_ad_position')->insertGetId($row);

            // 插入广告
            $this->appAd($position_id);
        }
    }

    /**
     * 广告
     */
    private function appAd($position_id = 0)
    {
        if ($position_id > 0) {
            $result = DB::table('app_ad')->where('position_id', $position_id)->count();
            if (empty($result)) {
                // 插入新数据
                $rows = [
                    [
                        'position_id' => $position_id,
                        'media_type' => '0',
                        'ad_name' => 'app启动页banner-01',
                        'ad_code' => 'data/attached/app/20190329165752.jpg',
                        'sort_order' => '50',
                        'enabled' => '1',
                    ],
                    [
                        'position_id' => $position_id,
                        'media_type' => '0',
                        'ad_name' => 'app启动页banner-02',
                        'ad_code' => 'data/attached/app/20190329165734.jpg',
                        'sort_order' => '50',
                        'enabled' => '1',
                    ]
                ];
                DB::table('app_ad')->insert($rows);
            }
        }
    }

    /**
     * 权限
     */
    private function adminAction()
    {
        $result = DB::table('admin_action')->where('action_code', 'app')->count();
        if (empty($result)) {
            // 默认数据
            $row = [
                'parent_id' => 0,
                'action_code' => 'app',
                'seller_show' => 0
            ];
            $action_id = DB::table('admin_action')->insertGetId($row);

            // 默认数据
            $rows = [
                [
                    'parent_id' => $action_id,
                    'action_code' => 'app_config',
                    'seller_show' => 0
                ],
                [
                    'parent_id' => $action_id,
                    'action_code' => 'app_ad_position',
                    'seller_show' => 0
                ]
            ];

            DB::table('admin_action')->insert($rows);
        }

        /* app权限 */
        $parent_id = DB::table('admin_action')->where('action_code', 'app')->value('action_id');
        $parent_id = $parent_id ? $parent_id : 0;

        // app可视化权限
        $count = DB::table('admin_action')->where('action_code', 'app_touch_visual')->count();
        if (empty($count)) {
            if ($parent_id > 0) {
                DB::table('admin_action')->insert([
                    'parent_id' => $parent_id,
                    'action_code' => 'app_touch_visual',
                    'seller_show' => 0
                ]);
            }
        }

        // app 客户端管理
        $count = DB::table('admin_action')->where('action_code', 'app_client_manage')->count();
        if (empty($count)) {
            if ($parent_id > 0) {
                DB::table('admin_action')->insert([
                    'parent_id' => $parent_id,
                    'action_code' => 'app_client_manage',
                    'seller_show' => '0'
                ]);
            }
        }

        // app自定义工具栏
        $count = DB::table('admin_action')->where('action_code', 'app_touch_nav_admin')->count();
        if (empty($count)) {
            if ($parent_id > 0) {
                DB::table('admin_action')->insert([
                    'parent_id' => $parent_id,
                    'action_code' => 'app_touch_nav_admin',
                    'seller_show' => 0
                ]);
            }
        }

        // 删除 app_config
        DB::table('admin_action')->where('action_code', 'app_config')->delete();

        // app设置
        $count = DB::table('admin_action')->where('action_code', 'app_client_setting')->count();
        if (empty($count)) {
            if ($parent_id > 0) {
                DB::table('admin_action')->insert([
                    'parent_id' => $parent_id,
                    'action_code' => 'app_client_setting',
                    'seller_show' => 0
                ]);
            }
        }

        // app 发现页权限
        $touch_page_nav = DB::table('admin_action')->where('parent_id', $parent_id)->where('action_code', 'touch_page_nav')->count();
        if (!empty($touch_page_nav)) {
            DB::table('admin_action')->where('parent_id', $parent_id)->where('action_code', 'touch_page_nav')->update(['action_code' => 'app_touch_page_nav']);
        }
        $count = DB::table('admin_action')->where('action_code', 'app_touch_page_nav')->count();
        if (empty($count)) {
            if ($parent_id > 0) {
                DB::table('admin_action')->insert([
                    'parent_id' => $parent_id,
                    'action_code' => 'app_touch_page_nav',
                    'seller_show' => 0
                ]);
            }
        }

    }

    /**
     * 处理 ecjia app登录旧用户数据
     */
    private function handleConnectUser()
    {
        DB::table('connect_user')->where('connect_code', '')->update(['connect_code' => 'app']);
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

        // 增加app工具栏分类
        $page_user_app = DB::table('touch_nav')->where('page', 'user')->where('device', 'app')->count();
        if (empty($page_user_app)) {
            $data = [
                'parent_id' => 0, 'device' => 'app', 'name' => '全部工具', 'page' => 'user', 'ifshow' => 1, 'vieworder' => 1
            ];
            $parent_id = DB::table('touch_nav')->insertGetId($data);
            if ($parent_id > 0) {
                // 默认导航数据
                $rows = [
                    [
                        'parent_id' => $parent_id, 'device' => 'app', 'name' => '收藏的商品', 'page' => 'user', 'url' => config('route.user.collect_goods'), 'pic' => 'data/attached/nav/shoucang.png', 'ifshow' => 1, 'vieworder' => 1
                    ],
                    [
                        'parent_id' => $parent_id, 'device' => 'app', 'name' => '关注的店铺', 'page' => 'user', 'url' => config('route.user.collect_shop'), 'pic' => 'data/attached/nav/store.png', 'ifshow' => 1, 'vieworder' => 2
                    ],
                    [
                        'parent_id' => $parent_id, 'device' => 'app', 'name' => '我的分享', 'page' => 'user', 'url' => config('route.user.affiliate'), 'pic' => 'data/attached/nav/share.png', 'ifshow' => 1, 'vieworder' => 3
                    ],
                    [
                        'parent_id' => $parent_id, 'device' => 'app', 'name' => '浏览记录', 'page' => 'user', 'url' => config('route.user.history'), 'pic' => 'data/attached/nav/guanzhu.png', 'ifshow' => 1, 'vieworder' => 4,
                    ],
                    [
                        'parent_id' => $parent_id, 'device' => 'app', 'name' => '礼品卡', 'page' => 'user', 'url' => config('route.giftcard.index'), 'pic' => 'data/attached/nav/package.png', 'ifshow' => 1, 'vieworder' => 5,
                    ],
                    [
                        'parent_id' => $parent_id, 'device' => 'app', 'name' => '我的拍卖', 'page' => 'user', 'url' => config('route.auction.index'), 'pic' => 'data/attached/nav/auction.png', 'ifshow' => 1, 'vieworder' => 6,
                    ],
                ];
                DB::table('touch_nav')->insert($rows);
            }
        }
    }
}
