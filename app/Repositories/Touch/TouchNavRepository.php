<?php

namespace App\Repositories\Touch;

use App\Models\TouchNav;
use App\Repositories\Common\BaseRepository;

/**
 * Class TouchNavRepository
 * @package App\Repositories
 */
class TouchNavRepository
{
    /**
     * 新增
     * @param $data
     * @return bool
     */
    public static function create($data)
    {
        if (empty($data)) {
            return false;
        }

        return TouchNav::insert($data);
    }

    /**
     * 修改
     * @param int $id
     * @param array $data
     * @return bool
     */
    public static function update($id = 0, $data = [])
    {
        if (empty($id) || empty($data)) {
            return false;
        }

        $data = BaseRepository::getArrayfilterTable($data, 'touch_nav');

        return TouchNav::where('id', $id)->update($data);
    }

    /**
     * 检查是否有子级
     * @param int $id
     * @return bool
     */
    public static function check($id = 0)
    {
        $count = TouchNav::query()->where('parent_id', $id)->count();

        return $count > 0 ? true : false;
    }

    /**
     * 删除
     * @param int $id
     * @return bool
     */
    public static function delete($id = 0)
    {
        if (empty($id)) {
            return false;
        }

        return TouchNav::where('id', $id)->delete();
    }

    /**
     * 查询
     * @param int $id
     * @return array
     */
    public static function info($id = 0)
    {
        $model = TouchNav::where('id', $id)->first();

        return $model ? $model->toArray() : [];
    }

    /**
     * 工具栏列表
     *
     * @param string $device
     * @param int $parent_id
     * @param array $offset
     * @param array $filter
     * @return array
     */
    public static function getList($device = '', $parent_id = 0, $offset = [], $filter = [])
    {
        $model = TouchNav::query()->where('device', $device)->where('parent_id', '>', 0);

        if (!empty($filter)) {
            // 搜索名称
            $keywords = $filter['keywords'] ?? '';
            if (!empty($keywords)) {
                $model = $model->where('name', 'like', '%' . $keywords . '%');
            }
        }

        if ($parent_id > 0) {
            $model = $model->where('parent_id', $parent_id);
        }

        $model = $model->with([
            'parentNav' => function ($query) {
                $query->select('id', 'parent_id', 'name');
            }
        ]);

        $total = $model->count();

        if (!empty($offset)) {
            $model = $model->offset($offset['start'])->limit($offset['limit']);
        }

        if (isset($filter['sort_by']) && !empty($filter['sort_by'])) {
            $filter['sort_order'] = $filter['sort_order'] ?? 'DESC';
            $model = $model->orderBy($filter['sort_by'], $filter['sort_order']);
        } else {
            $model = $model->orderBy('vieworder', 'ASC')
                ->orderBy('parent_id', 'ASC');
        }

        $list = $model->orderBy('id', 'DESC')
            ->get();
        $list = $list ? $list->toArray() : [];

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 工具栏分类列表
     *
     * @param string $device
     * @param array $offset
     * @param array $filter
     * @return array
     */
    public static function getParentList($device = '', $offset = [], $filter = [])
    {
        $model = TouchNav::query()->where('device', $device)->where('parent_id', 0);

        if (!empty($filter)) {
            // 搜索名称
            $keywords = $filter['keywords'] ?? '';
            if (!empty($keywords)) {
                $model = $model->where('name', 'like', '%' . $keywords . '%');
            }
        }

        $total = $model->count();

        if (!empty($offset)) {
            $model = $model->offset($offset['start'])->limit($offset['limit']);
        }

        $list = $model->orderBy('vieworder', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();
        $list = $list ? $list->toArray() : [];

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 工具栏分类
     * @param string $device
     * @param int $limit
     * @return array
     */
    public static function parentNav($device = 'h5', $limit = 100)
    {
        $model = TouchNav::query()->where('device', $device)->where('parent_id', 0);

        $list = $model->select('id', 'parent_id', 'name')
            ->limit($limit)
            ->orderBy('vieworder', 'ASC')
            ->orderBy('id', 'DESC')
            ->get();

        return $list ? $list->toArray() : [];
    }

    /**
     * 客户端 url: h5 wxapp app
     * @return array
     */
    public static function device_page_url()
    {
        $url = [
            // 收藏的商品
            ['cat_id' => 1, 'cat_name' => trans('common.collect_goods'), 'parent_id' => 0, 'url' => dsc_url('/#/user/collectionGoods'), 'app_page' => config('route.user.collect_goods'), 'applet_page' => config('route.user.collect_goods')],
            // 关注的店铺
            ['cat_id' => 2, 'cat_name' => trans('common.follow_store'), 'parent_id' => 0, 'url' => dsc_url('/#/user/collectionShop'), 'app_page' => config('route.user.collect_shop'), 'applet_page' => config('route.user.collect_shop')],
            // 我的分享
            ['cat_id' => 3, 'cat_name' => trans('user.my_share'), 'parent_id' => 0, 'url' => dsc_url('/#/user/affiliate'), 'app_page' => config('route.user.affiliate'), 'applet_page' => config('route.user.affiliate')],
            // 我的微筹
            ['cat_id' => 4, 'cat_name' => trans('user.crowdfunding'), 'parent_id' => 0, 'url' => dsc_url('/#/crowdfunding'), 'app_page' => config('route.crowdfunding.index'), 'applet_page' => config('route.crowdfunding.index')],
            // 商家入驻
           ['cat_id' => 5, 'cat_name' => trans('common.my_merchants'), 'parent_id' => 0, 'url' => dsc_url('/#/user/merchants'), 'app_page' => config('route.merchants.index'), 'applet_page' => config('route.merchants.index')],
            // 浏览记录
            ['cat_id' => 6, 'cat_name' => trans('common.Browsing_record'), 'parent_id' => 0, 'url' => dsc_url('/#/user/history'), 'app_page' => config('route.user.history'), 'applet_page' => config('route.user.history')],
            // 礼品卡兑换
            ['cat_id' => 7, 'cat_name' => trans('common.gift_card_exchange'), 'parent_id' => 0, 'url' => dsc_url('/#/giftCard'), 'app_page' => config('route.giftcard.index'), 'applet_page' => config('route.giftcard.index')],
            // 我的提货 (礼品卡)
            ['cat_id' => 8, 'cat_name' => trans('common.my_take_delivery'), 'parent_id' => 0, 'url' => dsc_url('/#/giftCardOrder'), 'app_page' => config('route.giftcard.order'), 'applet_page' => config('route.giftcard.order')],
            // 我的拍卖
            ['cat_id' => 9, 'cat_name' => trans('user.my_auction'), 'parent_id' => 0, 'url' => dsc_url('/#/user/auction'), 'app_page' => config('route.auction.index'), 'applet_page' => config('route.auction.index')]
        ];

        // 如果有分销模块则显示
        if (file_exists(MOBILE_DRP)) {
            $drp = [
                // 我的微店
                ['cat_id' => 10, 'cat_name' => trans('drp::drp.drp_store'), 'parent_id' => 0, 'url' => dsc_url('/#/drp'), 'app_page' => config('route.drp.index'), 'applet_page' => config('route.drp.index')]
            ];
            $url = collect($url)->merge($drp)->all();
        }

        $activity_url = TouchPageViewRepository::common_url('activity');
        if (!empty($activity_url)) {
            $url = collect($url)->merge($activity_url)->all();
        }

        return $url;
    }

    /**
     * 工具栏列表 for 前端
     *
     * @param string $device
     * @param string $page_flag 页面标识 不是分页！
     * @param int $top_id
     * @param int $limit
     * @return array
     */
    public static function getTouchNav($device = '', $page_flag = 'user', $top_id = 0, $limit = 100)
    {
        // 显示的工具栏 page页面标识 不是分页！
        $model = TouchNav::query()->where('device', $device)->where('page', $page_flag)->where('ifshow', 1)->where('parent_id', 0);

        if ($top_id > 0) {
            $model = $model->where('id', $top_id);
        }

        $model = $model->with([
            'childNav' => function ($query) {
                $query->select('id', 'parent_id', 'name', 'url', 'pic', 'vieworder', 'device')->where('ifshow', 1)->orderBy('vieworder', 'ASC');
            }
        ]);

        $list = $model->select('id', 'name', 'vieworder')->limit($limit)->orderBy('vieworder', 'ASC')
            ->orderBy('id', 'DESC')
            ->get();

        return $list ? $list->toArray() : [];
    }

}
