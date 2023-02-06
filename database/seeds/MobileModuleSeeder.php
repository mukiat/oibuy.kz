<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MobileModuleSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->shopConfig();
        $this->touchAdPosition();

        $this->touch_nav();
    }

    private function shopConfig()
    {
        $result = DB::table('shop_config')->where('code', 'wap_category')->first();
        if (empty($result)) {
            // 默认数据
            $rows = [
                [
                    'parent_id' => '9',
                    'code' => 'wap_category',
                    'type' => 'select',
                    'store_range' => '0,1',
                    'sort_order' => '1',
                    'value' => '0',
                ]
            ];
            DB::table('shop_config')->insert($rows);
        }
    }

    /**
     * 秒杀广告位
     */
    private function touchAdPosition()
    {
        // 删除旧数据
        $result = DB::table('touch_ad_position')->where('ad_type', 'seckill')->first();
        if (!empty($result)) {
            // 删除秒杀广告位
            DB::table('touch_ad_position')->where('ad_type', 'seckill')->delete();
            // 删除秒杀广告位下的广告
            DB::table('touch_ad')->where('position_id', $result->position_id)->delete();
        }

        $result = DB::table('touch_ad_position')->where('ad_type', 'seckill')->get();
        $result = $result->toArray();
        if (empty($result)) {
            // 默认广告位数据
            $row = [
                'position_name' => '秒杀-banner广告位',
                'ad_width' => '360',
                'ad_height' => '168',
                'position_style' => '{foreach $ads as $ad}<div class="swiper-slide">{$ad}</div>{/foreach}' . "\n" . '',
                'theme' => 'ecmoban_dsc2017',
                'tc_type' => 'banner',
                'ad_type' => 'seckill'
            ];
            $position_id = DB::table('touch_ad_position')->insertGetId($row);

            if ($position_id) {
                // 默认广告数据
                $rows = [
                    [
                        'position_id' => $position_id,
                        'ad_name' => '秒杀-banner001',
                        'ad_code' => '1490123276201426626.jpg',
                        'start_time' => '1481585927',
                        'end_time' => '1577229853',
                        'enabled' => '1',
                    ],
                    [
                        'position_id' => $position_id,
                        'ad_name' => '秒杀-banner002',
                        'ad_code' => '1490123436237080312.jpg',
                        'start_time' => '1481585927',
                        'end_time' => '1577229853',
                        'enabled' => '1',
                    ],
                    [
                        'position_id' => $position_id,
                        'ad_name' => '秒杀-banner003',
                        'ad_code' => '1490123319351624402.jpg',
                        'start_time' => '1481585927',
                        'end_time' => '1577229853',
                        'enabled' => '1',
                    ]
                ];
                DB::table('touch_ad')->insert($rows);
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
}
