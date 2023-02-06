<?php

namespace App\Services\Activity;

use App\Models\Cart;
use App\Models\FavourableActivity;
use App\Models\Goods;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Category\CategoryService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Goods\GoodsDataHandleService;

/**
 * 活动 ->【凑单】
 */
class AddonItemService
{
    protected $categoryService;
    protected $goodsCommonService;
    protected $sessionRepository;
    protected $dscRepository;

    public function __construct(
        CategoryService $categoryService,
        GoodsCommonService $goodsCommonService,
        SessionRepository $sessionRepository,
        DscRepository $dscRepository
    )
    {
        $this->categoryService = $categoryService;
        $this->goodsCommonService = $goodsCommonService;
        $this->sessionRepository = $sessionRepository;
        $this->dscRepository = $dscRepository;
    }

    /**
     * 取得某用户等级当前时间可以享受的优惠活动
     *
     * @param $user_rank
     * @param $favourable_id
     * @param string $sort
     * @param string $order
     * @param int $size
     * @param int $page
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @return array
     * @throws \Exception
     */
    public function getFavourableGoodsList($user_rank, $favourable_id, $sort = '', $order = '', $size = 15, $page = 1, $warehouse_id = 0, $area_id = 0, $area_city = 0)
    {
        /* 当前用户可享受的优惠活动 */
        $user_rank = ',' . $user_rank . ',';
        $now = TimeRepository::getGmTime();

        $favourable = FavourableActivity::where('review_status', 3)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->where('act_id', $favourable_id)
            ->whereRaw("CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'");

        $favourable = BaseRepository::getToArrayFirst($favourable);

        $res = Goods::where('is_on_sale', 1)
            ->where('is_alone_sale', 1)
            ->where('is_delete', 0);

        if ($favourable['userFav_type'] == 0) {
            $res = $res->where('user_id', $favourable['user_id']);
        }

        if (isset($favourable['act_range']) && $favourable['act_range'] == FAR_CATEGORY) {

            // 按分类
            $cat_list = BaseRepository::getExplode($favourable['act_range_ext']);

            /**
             * 当前分类下的所有子分类
             * 返回一维数组
             */
            $id_list = $this->categoryService->getCatListChildren($cat_list);
            $id_list = $id_list ? $id_list : [-1];

            $res = $res->whereIn('cat_id', $id_list);
        }

        if (isset($favourable['act_range']) && $favourable['act_range'] == FAR_BRAND) {

            // 按品牌
            $id_list = BaseRepository::getExplode($favourable['act_range_ext']);
            $id_list = $id_list ? $id_list : [-1];

            $res = $res->whereIn('brand_id', $id_list);
        }

        $ext = false;
        if (isset($favourable['act_range']) && $favourable['act_range'] == FAR_GOODS) {
            $ext = true;

            // 按商品分类
            $id_list = BaseRepository::getExplode($favourable['act_range_ext']);
            $id_list = $id_list ? $id_list : [-1];

            $res = $res->whereIn('goods_id', $id_list);
        }

        if (isset($favourable['userFav_type']) && $favourable['userFav_type'] == 0 && $ext) {
            $res = $res->where('user_id', $favourable['user_id']);
        }

        if (config('shop.review_goods') == 1) {
            $res = $res->whereIn('review_status', [3, 4, 5]);
        }

        $res = $this->dscRepository->getAreaLinkGoods($res, $area_id, $area_city);

        $user_rank = session('user_rank', 0);
        $discount = session('discount', 1);

        $res = $res->orderBy($sort, $order);

        $start = ($page - 1) * $size;

        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($size > 0) {
            $res = $res->take($size);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        $key = 0;

        if ($res) {

            $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');
            $memberPrice = GoodsDataHandleService::goodsMemberPrice($goods_id, $user_rank);
            $warehouseGoods = GoodsDataHandleService::getWarehouseGoodsDataList($goods_id, $warehouse_id);
            $warehouseAreaGoods = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods_id, $area_id, $area_city);

            foreach ($res as $row) {
                $price = [
                    'model_price' => isset($row['model_price']) ? $row['model_price'] : 0,
                    'user_price' => $memberPrice[$row['goods_id']]['user_price'] ?? 0,
                    'percentage' => $memberPrice[$row['goods_id']]['percentage'] ?? 0,
                    'warehouse_price' => $warehouseGoods[$row['goods_id']]['warehouse_price'] ?? 0,
                    'region_price' => $warehouseAreaGoods[$row['goods_id']]['region_price'] ?? 0,
                    'shop_price' => isset($row['shop_price']) ? $row['shop_price'] : 0,
                    'warehouse_promote_price' => $warehouseGoods[$row['goods_id']]['warehouse_promote_price'] ?? 0,
                    'region_promote_price' => $warehouseAreaGoods[$row['goods_id']]['region_promote_price'] ?? 0,
                    'promote_price' => isset($row['promote_price']) ? $row['promote_price'] : 0,
                    'wg_number' => $warehouseGoods[$row['goods_id']]['region_number'] ?? 0,
                    'wag_number' => $warehouseAreaGoods[$row['goods_id']]['region_number'] ?? 0,
                    'goods_number' => isset($row['goods_number']) ? $row['goods_number'] : 0
                ];

                $price = $this->goodsCommonService->getGoodsPrice($price, $discount, $row);

                $row['shop_price'] = $price['shop_price'];
                $row['promote_price'] = $price['promote_price'];
                $row['goods_number'] = $price['goods_number'];

                $arr[$key] = $row;

                if ($row['promote_price'] > 0) {
                    $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                } else {
                    $promote_price = 0;
                }

                $arr[$key]['goods_id'] = $row['goods_id'];
                $arr[$key]['goods_name'] = $row['goods_name'];
                $arr[$key]['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                $arr[$key]['format_shop_price'] = $this->dscRepository->getPriceFormat($row['shop_price']);
                $arr[$key]['format_promote_price'] = ($promote_price > 0) ? $this->dscRepository->getImagePath($promote_price) : '';
                $arr[$key]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);

                $key++;
            }
        }

        return $arr;
    }

    /**
     * 优惠活动
     *
     * @param $user_rank
     * @param $favourable_id
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @return mixed
     * @throws \Exception
     */
    public function getFavourableGoodsCount($user_rank, $favourable_id, $warehouse_id = 0, $area_id = 0, $area_city = 0)
    {
        /* 当前用户可享受的优惠活动 */
        $user_rank = ',' . $user_rank . ',';
        $now = TimeRepository::getGmTime();

        $favourable = FavourableActivity::where('review_status', 3)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->where('act_id', $favourable_id)
            ->whereRaw("CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'");

        $favourable = $favourable->first();

        $favourable = $favourable ? $favourable->toArray() : [];

        $res = Goods::where('is_on_sale', 1)
            ->where('is_alone_sale', 1)
            ->where('is_delete', 0);

        if ($favourable['userFav_type'] == 0) {
            $res = $res->where('user_id', $favourable['user_id']);
        }

        $ext = true;
        if (isset($favourable['act_range']) && $favourable['act_range'] == FAR_CATEGORY) {

            // 按分类
            $cat_list = BaseRepository::getExplode($favourable['act_range_ext']);

            /**
             * 当前分类下的所有子分类
             * 返回一维数组
             */
            $id_list = $cat_list ? $this->categoryService->getCatListChildren($cat_list) : [];
            $id_list = $id_list ? $id_list : [-1];

            $res = $res->whereIn('cat_id', $id_list);
        }
        if (isset($favourable['act_range']) && $favourable['act_range'] == FAR_BRAND) {

            // 按品牌
            $id_list = BaseRepository::getExplode($favourable['act_range_ext']);
            $id_list = $id_list ? $id_list : [-1];

            $res = $res->whereIn('brand_id', $id_list);
        }
        if (isset($favourable['act_range']) && $favourable['act_range'] == FAR_GOODS) {

            // 按商品分类
            $id_list = BaseRepository::getExplode($favourable['act_range_ext']);
            $id_list = $id_list ? $id_list : [-1];

            $res = $res->whereIn('goods_id', $id_list);
        }

        if (isset($favourable['userFav_type']) && $favourable['userFav_type'] == 0 && $ext) {
            $res = $res->where('user_id', $favourable['user_id']);
        }

        if (config('shop.review_goods') == 1) {
            $res = $res->whereIn('review_status', [3, 4, 5]);
        }

        $res = $this->dscRepository->getAreaLinkGoods($res, $area_id, $area_city);

        return $res->count();
    }

    /**
     * 取得当前活动 已经加入购物车的商品
     *
     * @param int $user_rank
     * @param int $favourable_id
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @return array
     * @throws \Exception
     */
    public function getCartFavourableGoods($user_rank = 0, $favourable_id = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0)
    {
        $user_rank = ',' . $user_rank . ',';
        $now = TimeRepository::getGmTime();

        $favourable = FavourableActivity::where('review_status', 3)
            ->where('act_id', $favourable_id)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->whereRaw("CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'");

        $favourable = BaseRepository::getToArrayFirst($favourable);

        /* 查询优惠范围内购物车的商品 */
        $res = Cart::select('rec_id', 'goods_number', 'goods_id', 'goods_price')
            ->where('rec_type', CART_GENERAL_GOODS)
            ->where('is_gift', 0);

        if (!empty(session('user_id'))) {
            $res = $res->where('user_id', session('user_id'));
        } else {
            $session_id = $this->sessionRepository->realCartMacIp();
            $res = $res->where('session_id', $session_id);
        }

        $mer_ids = [];
        /* 根据优惠范围修正sql */
        if (isset($favourable['act_range']) && $favourable['act_range'] == FAR_ALL) {
        } elseif (isset($favourable['act_range']) && $favourable['act_range'] == FAR_CATEGORY) {

            /* 取得优惠范围分类的所有下级分类 */
            $cat_list = BaseRepository::getExplode($favourable['act_range_ext']);

            /**
             * 当前分类下的所有子分类
             * 返回一维数组
             */
            $id_list = $cat_list ? $this->categoryService->getCatListChildren($cat_list) : [];

        } elseif (isset($favourable['act_range']) && $favourable['act_range'] == FAR_BRAND) {
            $id_list = BaseRepository::getExplode($favourable['act_range_ext']);
        } else {
            $id_list = BaseRepository::getExplode($favourable['act_range_ext']);
        }

        $goodsWhere = [
            'mer_ids' => $mer_ids,
            'user_id' => $favourable['user_id'] ?? 0,
            'far_all' => FAR_ALL,
            'categoty' => FAR_CATEGORY,
            'brand' => FAR_BRAND,
            'goods' => FAR_GOODS,
            'id_list' => $id_list ? $id_list : [-1],
            'favourable' => $favourable,
            'open_area_goods' => config('shop.open_area_goods'),
            'warehouse_id' => $warehouse_id,
            'area_id' => $area_id,
            'area_city' => $area_city
        ];

        $res = $res->whereHasIn('getGoods', function ($query) use ($goodsWhere) {
            $ext = true;

            $query = $query->where('is_on_sale', 1)
                ->where('is_alone_sale', 1)
                ->where('is_delete', 0);

            if (isset($goodsWhere['favourable']['act_range']) && $goodsWhere['favourable']['act_range'] == $goodsWhere['far_all']) {
                $query = $query->whereIn('user_id', $goodsWhere['mer_ids']);
            } elseif (isset($goodsWhere['favourable']['act_range']) && $goodsWhere['favourable']['act_range'] == $goodsWhere['categoty']) {
                $query = $query->whereIn('cat_id', $goodsWhere['id_list']);
            } elseif (isset($goodsWhere['favourable']['act_range']) && $goodsWhere['favourable']['act_range'] == $goodsWhere['brand']) {
                $query = $query->whereIn('brand_id', $goodsWhere['id_list']);
            } else {
                $query = $query->whereIn('goods_id', $goodsWhere['id_list']);
            }

            if (isset($goodsWhere['favourable']['userFav_type']) && $goodsWhere['favourable']['userFav_type'] == 0 && $ext) {
                $query = $query->where('user_id', $goodsWhere['user_id']);
            }

            $this->dscRepository->getAreaLinkGoods($query, $goodsWhere['area_id'], $goodsWhere['area_city']);
        });

        $res = BaseRepository::getToArrayGet($res);

        /* 优惠范围内的商品总额 */
        $cart_favourable_goods = [];

        if ($res) {

            $goodsIdList = BaseRepository::getKeyPluck($res, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goodsIdList, ['goods_id', 'goods_thumb', 'goods_name']);

            foreach ($res as $key => $row) {

                $goods = $goodsList[$row['goods_id']] ?? [];
                $row = BaseRepository::getArrayMerge($row, $goods);

                $cart_favourable_goods[$key]['rec_id'] = $row['rec_id'];
                $cart_favourable_goods[$key]['goods_id'] = $row['goods_id'];
                $cart_favourable_goods[$key]['goods_name'] = $row['goods_name'];
                $cart_favourable_goods[$key]['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                $cart_favourable_goods[$key]['shop_price'] = number_format($row['goods_price'], 2, '.', '');
                $cart_favourable_goods[$key]['goods_number'] = $row['goods_number'];
                $cart_favourable_goods[$key]['goods_url'] = $this->dscRepository->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
            }
        }

        return $cart_favourable_goods;
    }

    // 获取优惠活动类型 满赠-满减-打折
    public function getActType($user_rank, $favourable_id)
    {
        $user_rank = ',' . $user_rank . ',';
        $now = TimeRepository::getGmTime();

        $selected = FavourableActivity::where('review_status', 3)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->where('act_id', $favourable_id)
            ->whereRaw("CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'");

        $selected = BaseRepository::getToArrayFirst($selected);

        $act_type_txt = '';
        if ($selected) {
            switch ($selected['act_type']) {
                case 0:
                    $act_type_txt = lang('coudan.With_a_gift') . lang('coudan.man') . $selected['min_amount'] . lang('coudan.change_purchase_gift');
                    break;
                case 1:
                    $act_type_txt = lang('coudan.Full_reduction') . lang('coudan.man') . $selected['min_amount'] . lang('coudan.reduction_gift') . $selected['act_type_ext'] . lang('coudan.yuan');
                    break;
                case 2:
                    $act_type_txt = lang('coudan.discount') . lang('coudan.man') . $selected['min_amount'] . lang('coudan.discount_gift');
                    break;

                default:
                    break;
            }
        }

        return $act_type_txt;
    }
}
