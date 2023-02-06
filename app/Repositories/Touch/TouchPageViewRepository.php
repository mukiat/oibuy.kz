<?php

namespace App\Repositories\Touch;

/**
 * Class TouchPageViewRepository
 * @package App\Repositories
 */
class TouchPageViewRepository
{
    /**
     * 可视化可选 菜单url
     * @param string $type
     * @return array
     */
    public static function common_url($type = '')
    {
        $url = [];

        if ($type == 'function') {
            $url = [
                ['cat_id' => 1, 'cat_name' => lang('common.category'), 'parent_id' => 0, 'url' => dsc_url('/#/catalog'), 'app_page' => config('route.catalog.index'), 'applet_page' => config('route.catalog.index')],
                ['cat_id' => 2, 'cat_name' => lang('common.cat_list'), 'parent_id' => 0, 'url' => dsc_url('/#/cart'), 'app_page' => config('route.cart.index'), 'applet_page' => config('route.cart.index')],
                ['cat_id' => 3, 'cat_name' => lang('common.user_center_label'), 'parent_id' => 0, 'url' => dsc_url('/#/user'), 'app_page' => config('route.user.index'), 'applet_page' => config('route.user.index')],
                ['cat_id' => 4, 'cat_name' => lang('common.store_street'), 'parent_id' => 0, 'url' => dsc_url('/#/integration?type=1'), 'app_page' => config('route.shop.index'), 'applet_page' => config('route.shop.index')],
                ['cat_id' => 5, 'cat_name' => lang('common.brand_street'), 'parent_id' => 0, 'url' => dsc_url('/#/brand'), 'app_page' => config('route.brand.index'), 'applet_page' => config('route.brand.index')],
                ['cat_id' => 6, 'cat_name' => lang('common.mini_sns'), 'parent_id' => 0, 'url' => dsc_url('/#/discover'), 'app_page' => config('route.discover.index'), 'applet_page' => config('route.discover.index')],
            ];

            return $url;
        }

        if ($type == 'activity') {
            $url = [
                ['cat_id' => 1, 'cat_name' => lang('common.rec_txt.2'), 'parent_id' => 0, 'url' => dsc_url('/#/groupbuy'), 'app_page' => config('route.groupbuy.index'), 'applet_page' => config('route.groupbuy.index')],
                ['cat_id' => 2, 'cat_name' => lang('common.rec_txt.5'), 'parent_id' => 0, 'url' => dsc_url('/#/exchange'), 'app_page' => config('route.exchange.index'), 'applet_page' => config('route.exchange.index')],
                ['cat_id' => 3, 'cat_name' => lang('common.rec_txt.8'), 'parent_id' => 0, 'url' => dsc_url('/#/crowdfunding'), 'app_page' => config('route.crowdfunding.index'), 'applet_page' => config('route.crowdfunding.index')],
                ['cat_id' => 4, 'cat_name' => lang('common.rec_txt.7'), 'parent_id' => 0, 'url' => dsc_url('/#/topic'), 'app_page' => config('route.topic.index'), 'applet_page' => config('route.topic.index')],
                ['cat_id' => 5, 'cat_name' => lang('common.rec_txt.9'), 'parent_id' => 0, 'url' => dsc_url('/#/activity'), 'app_page' => config('route.activity.index'), 'applet_page' => config('route.activity.index')],
                ['cat_id' => 6, 'cat_name' => lang('common.rec_txt.3'), 'parent_id' => 0, 'url' => dsc_url('/#/auction'), 'app_page' => config('route.auction.index'), 'applet_page' => config('route.auction.index')],
                ['cat_id' => 7, 'cat_name' => lang('common.rec_txt.10'), 'parent_id' => 0, 'url' => dsc_url('/#/seckill'), 'app_page' => config('route.seckill.index'), 'applet_page' => config('route.seckill.index')],
                ['cat_id' => 8, 'cat_name' => lang('common.rec_txt.11'), 'parent_id' => 0, 'url' => dsc_url('/#/package'), 'app_page' => config('route.package.index'), 'applet_page' => config('route.package.index')],
                ['cat_id' => 11, 'cat_name' => lang('common.rec_txt.6'), 'parent_id' => 0, 'url' => dsc_url('/#/presale'), 'app_page' => config('route.presale.index'), 'applet_page' => config('route.presale.index')],
                // 优惠券列表
                ['cat_id' => 12, 'cat_name' => trans('user.Coupon_list'), 'parent_id' => 0, 'url' => dsc_url('/#/coupon'), 'app_page' => config('route.coupon.index'), 'applet_page' => config('route.coupon.index')]
            ];
            // 如果有拼团模块则显示
            if (file_exists(MOBILE_TEAM)) {
                $team = [
                    ['cat_id' => 9, 'cat_name' => lang('common.rec_txt.12'), 'parent_id' => 0, 'url' => dsc_url('/#/team'), 'app_page' => config('route.team.index'), 'applet_page' => config('route.team.index')]
                ];
                $url = collect($url)->merge($team)->all();
            }

            // 如果有砍价模块则显示
            if (file_exists(MOBILE_BARGAIN)) {
                $team = [
                    ['cat_id' => 10, 'cat_name' => lang('common.rec_txt.13'), 'parent_id' => 0, 'url' => dsc_url('/#/bargain'), 'app_page' => config('route.bargain.index'), 'applet_page' => config('route.bargain.index')]
                ];
                $url = collect($url)->merge($team)->all();
            }

            return $url;
        }

        return $url;
    }
}
