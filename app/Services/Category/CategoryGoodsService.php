<?php

namespace App\Services\Category;

use App\Models\Comment;
use App\Models\Coupons;
use App\Models\CouponsUser;
use App\Models\Goods;
use App\Models\GoodsAttr;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\StrRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Activity\CouponsService;
use App\Services\Brand\BrandDataHandleService;
use App\Services\Common\AreaService;
use App\Services\Gallery\GalleryDataHandleService;
use App\Services\Goods\GoodsAttrService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Goods\GoodsGalleryService;
use App\Services\Goods\GoodsWarehouseService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\User\UserCommonService;

class CategoryGoodsService
{
    protected $dscRepository;
    protected $goodsCommonService;
    protected $userCommonService;
    protected $merchantCommonService;
    protected $goodsGalleryService;
    protected $goodsWarehouseService;
    protected $city = 0;
    protected $brandDataHandleService;
    protected $couponsService;

    public function __construct(
        DscRepository $dscRepository,
        GoodsCommonService $goodsCommonService,
        UserCommonService $userCommonService,
        MerchantCommonService $merchantCommonService,
        GoodsGalleryService $goodsGalleryService,
        GoodsWarehouseService $goodsWarehouseService,
        BrandDataHandleService $brandDataHandleService,
        CouponsService $couponsService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->goodsCommonService = $goodsCommonService;
        $this->userCommonService = $userCommonService;
        $this->merchantCommonService = $merchantCommonService;
        $this->goodsGalleryService = $goodsGalleryService;
        $this->goodsWarehouseService = $goodsWarehouseService;
        $this->brandDataHandleService = $brandDataHandleService;
        $this->couponsService = $couponsService;

        /* 获取地区缓存 */
        $area_cookie = app(AreaService::class)->areaCookie();
        $this->city = $area_cookie['city'];
    }

    /**
     * 获得指定分类下的推荐商品
     *
     * @param string $cats 分类的ID
     * @param string $type 推荐类型，可以是 best, new, hot, promote
     * @param int $brand_id 品牌的ID
     * @param int $warehouse_id 仓库ID
     * @param int $area_id 仓库地区ID
     * @param int $area_city 仓库地区城市ID
     * @param array $where_ext
     * @param int $min 最小金额
     * @param int $max 最大金额
     * @param int $num 查询条数
     * @param int $start 起始查询条数
     * @return array|bool|\Illuminate\Cache\CacheManager|mixed
     * @throws \Exception
     */
    public function getCategoryRecommendGoods($cats = '', $type = '', $brand_id = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0, $where_ext = [], $min = 0, $max = 0, $num = 10, $start = 0)
    {
        $cats_cache = $cats && is_array($cats) ? implode(',', $cats) : $cats;

        $self = $where_ext['self'] ?? 0;
        $have = $where_ext['have'] ?? 0;
        $ship = $where_ext['ship'] ?? 0;
        $self_run_list = isset($where_ext['self_run_list']) && !empty($where_ext['self_run_list']) && is_array($where_ext['self_run_list']) ? implode(',', $where_ext['self_run_list']) : '';

        $cache_name = "get_category_recommend_goods_" . '_' . $cats_cache . '_' . $type . '_' . $brand_id . '_' . $warehouse_id .
            '_' . $area_id . '_' . $area_city . '_' . $self . '_' . $have . '_' . $ship . '_' . $self_run_list . '_' . $min . '_' . $max . '_' . $num . '_' . $start;

        $goods = cache($cache_name);
        $goods = !is_null($goods) ? $goods : false;

        if ($goods === false) {
            $extension_goods = $this->goodsCommonService->getCategoryGoodsId($cats);

            $goodsParam = [
                'children' => $cats,
                'extension_goods' => $extension_goods
            ];

            /* 查询分类商品数据 */
            $res = Goods::where('is_on_sale', 1)
                ->where('is_alone_sale', 1)
                ->where('is_delete', 0)
                ->where('is_show', 1)
                ->where(function ($query) use ($goodsParam) {
                    $query = $query->whereIn('cat_id', (array)$goodsParam['children']);
                    if ($goodsParam['extension_goods']) {
                        $query->orWhere(function ($query) use ($goodsParam) {
                            $query->whereIn('goods_id', $goodsParam['extension_goods']);
                        });
                    }
                });

            if ($brand_id) {
                $brand_id = BaseRepository::getExplode($brand_id);
                $res = $res->whereIn('brand_id', $brand_id);
            }

            /* 查询仅自营和标识自营店铺的商品 */
            if (isset($where_ext['self']) && $where_ext['self'] == 1) {
                $res = $res->where(function ($query) use ($where_ext) {
                    $query->where('user_id', 0)->orWhere(function ($query) use ($where_ext) {
                        $query->whereIn('user_id', $where_ext['self_run_list']);
                    });
                });
            }

            if (isset($where_ext['have']) && $where_ext['have'] == 1) {
                $res = $res->where('goods_number', '>', 0);
            }

            if (isset($where_ext['ship']) && $where_ext['ship'] == 1) {
                $res = $res->where('is_shipping', 1);
            }

            if ($min > 0) {
                $res = $res->where('shop_price', '>=', $min);
            }

            if ($max > 0) {
                $res = $res->where('shop_price', '<=', $max);
            }

            switch ($type) {
                case 'best':
                    $res = $res->where('is_best', 1);
                    break;
                case 'new':
                    $res = $res->where('is_new', 1);
                    break;
                case 'hot':
                    $res = $res->where('is_hot', 1);
                    break;
                case 'promote':
                    $time = TimeRepository::getGmTime();
                    $res = $res->where('is_promote', 1)
                        ->where('promote_start_date', '<=', $time)
                        ->where('promote_end_date', '>=', $time);
                    break;
                //随机by wu
                case 'rand':
                    $res = $res->where('is_best', 1);
                    break;
            }

            if (config('shop.review_goods')) {
                $res = $res->whereIn('review_status', [3, 4, 5]);
            }

            /* 关联地区 */
            $res = $this->dscRepository->getAreaLinkGoods($res, $area_id, $area_city);

            $user_rank = session('user_rank', 0);
            $discount = session('discount', 1);

            $order_type = config('shop.recommend_order');
            if ($type == 'rand') {
                $order_type = 1;
            }

            //随机
            if ($order_type == 1) {
                $res = $res->orderByRaw('RAND()');
            } else {
                $res = $res->orderByRaw('sort_order, last_update desc');
            }

            if ($start > 0) {
                $res = $res->skip($start);
            }

            if ($num > 0) {
                $res = $res->take($num);
            }

            $res = BaseRepository::getToArrayGet($res);

            $idx = 0;
            $goods = [];
            if ($res) {
                $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');

                $memberPrice = GoodsDataHandleService::goodsMemberPrice($goods_id, $user_rank);
                $warehouseGoods = GoodsDataHandleService::getWarehouseGoodsDataList($goods_id, $warehouse_id);
                $warehouseAreaGoods = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods_id, $area_id, $area_city);

                $brand_id = BaseRepository::getKeyPluck($res, 'brand_id');
                $brand = $this->brandDataHandleService->goodsBrand($brand_id);

                foreach ($res as $key => $row) {
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

                    $goods[$idx] = $row;

                    if ($row['promote_price'] > 0) {
                        $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                    } else {
                        $promote_price = 0;
                    }

                    $goodsSelf = false;
                    if ($row['user_id'] == 0) {
                        $goodsSelf = true;
                    }

                    $goods[$idx]['id'] = $row['goods_id'];
                    $goods[$idx]['comments_number'] = $row['comments_number'];
                    $goods[$idx]['sales_volume'] = $row['sales_volume'];
                    $goods[$idx]['name'] = $row['goods_name'];
                    $goods[$idx]['brief'] = $row['goods_brief'];
                    $goods[$idx]['brand_name'] = $brand[$row['brand_id']]['brand_name'] ?? '';
                    $goods[$idx]['short_name'] = config('shop.goods_name_length') > 0 ?
                        $this->dscRepository->subStr($row['goods_name'], config('shop.goods_name_length')) : $row['goods_name'];
                    $goods[$idx]['market_price'] = $this->dscRepository->getPriceFormat($row['market_price'], true, true, $goodsSelf);
                    $goods[$idx]['shop_price'] = $this->dscRepository->getPriceFormat($row['shop_price'], true, true, $goodsSelf);
                    $goods[$idx]['promote_price'] = ($promote_price > 0) ? $this->dscRepository->getPriceFormat($promote_price, true, true, $goodsSelf) : '';
                    $goods[$idx]['thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                    $goods[$idx]['goods_img'] = $this->dscRepository->getImagePath($row['goods_img']);
                    $goods[$idx]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);

                    $goods[$idx]['short_style_name'] = $this->goodsCommonService->addStyle($goods[$idx]['short_name'], $row['goods_name_style']);
                    $idx++;
                }
            }

            cache()->forever($cache_name, $goods);
        }

        return $goods;
    }

    /**
     * 获得对比商品
     *
     * @param $goods_ids
     * @param string $compare
     * @param string $highlight
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @return array
     */
    public function getCatCompare($goods_ids, $compare = '', $highlight = '', $warehouse_id = 0, $area_id = 0, $area_city = 0)
    {
        $goods_ids = BaseRepository::getExplode($goods_ids);

        $cmtres = Comment::selectRaw('id_value , AVG(comment_rank) AS cmt_rank, COUNT(*) AS cmt_count');
        $cmtres = $cmtres->whereIn('id_value', $goods_ids)
            ->where('comment_type', 0);
        $cmtres = $cmtres->groupBy('id_value');

        $cmtres = BaseRepository::getToArrayGet($cmtres);

        $cmt = [];
        if ($cmtres) {
            foreach ($cmtres as $row) {
                $cmt[$row['id_value']] = $row;
            }
        }

        $res = Goods::where('is_alone_sale', 1)
            ->where('is_delete', 0)
            ->whereIn('goods_id', $goods_ids);

        $res = $res->orderBy('goods_id');

        $res = BaseRepository::getToArrayGet($res);

        $type_id = 0;
        $basic_arr = [];
        $goods_list = [];

        $user_rank = session('user_rank', 0);
        $discount = session('discount', 1);

        if ($res) {
            $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');

            $memberPrice = GoodsDataHandleService::goodsMemberPrice($goods_id, $user_rank);
            $warehouseGoods = GoodsDataHandleService::getWarehouseGoodsDataList($goods_id, $warehouse_id);
            $warehouseAreaGoods = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods_id, $area_id, $area_city);

            $properties = app(GoodsAttrService::class)->goodsPropertiesList($goods_id, $warehouse_id, $area_id, $area_city, null, true);

            $brand_id = BaseRepository::getKeyPluck($res, 'brand_id');
            $brand = $this->brandDataHandleService->goodsBrand($brand_id);

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

                $goods_id = $row['goods_id'];

                $goods_list[$goods_id] = $row;

                if ($row['promote_price'] > 0) {
                    $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                } else {
                    $promote_price = 0;
                }

                $type_id = $row['goods_type'];
                $goods_list[$goods_id]['goods_id'] = $goods_id;
                $goods_list[$goods_id]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
                $goods_list[$goods_id]['goods_name'] = $row['goods_name'];
                $goods_list[$goods_id]['shop_price'] = $this->dscRepository->getPriceFormat($row['shop_price']);
                $goods_list[$goods_id]['promote_price'] = ($promote_price > 0) ? $this->dscRepository->getPriceFormat($promote_price) : '';
                $goods_list[$goods_id]['goods_weight'] = (intval($row['goods_weight']) > 0) ?
                    ceil($row['goods_weight']) . $GLOBALS['_LANG']['kilogram'] : ceil($row['goods_weight'] * 1000) . $GLOBALS['_LANG']['gram'];
                $goods_list[$goods_id]['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                $goods_list[$goods_id]['goods_brief'] = $row['goods_brief'];
                $goods_list[$goods_id]['brand_name'] = $brand[$row['brand_id']]['brand_name'] ?? '';

                $tmp = $goods_ids;
                $key = array_search($goods_id, $tmp);

                if ($key !== null && $key !== false) {
                    unset($tmp[$key]);
                }

                $goods_list[$goods_id]['ids'] = !empty($tmp) ? "goods[]=" . implode('&amp;goods[]=', $tmp) : '';

                if (isset($properties[$row['goods_id']]) && $properties[$row['goods_id']]) {
                    $basic_arr[$row['goods_id']] = $properties[$row['goods_id']];
                }

                if ($cmt && !isset($basic_arr[$goods_id]['comment_rank'])) {
                    $basic_arr[$goods_id]['comment_rank'] = isset($cmt[$goods_id]) ? ceil($cmt[$goods_id]['cmt_rank']) : 0;
                    $basic_arr[$goods_id]['comment_number'] = isset($cmt[$goods_id]) ? $cmt[$goods_id]['cmt_count'] : 0;
                    $basic_arr[$goods_id]['comment_number'] = sprintf($GLOBALS['_LANG']['comment_num'], $basic_arr[$goods_id]['comment_number']);
                }
            }
        }

        $res = [
            'goods_list' => $goods_list,
            'basic_arr' => $basic_arr,
            'type_id' => $type_id
        ];

        return $res;
    }

    /**
     * 获得分类下的商品
     *
     * @param int $uid
     * @param array $keywords
     * @param array $children
     * @param int $brand_id
     * @param int $price_min
     * @param int $price_max
     * @param array $filter_attr
     * @param array $where_ext
     * @param int $goods_num
     * @param int $size
     * @param int $page
     * @param string $sort
     * @param string $order
     * @param int $show_visual
     * @return array
     * @throws \Exception
     */
    public function getMobileCategoryGoodsList($uid = 0, $keywords = [], $children = [], $brand_id = 0, $price_min = 0, $price_max = 0, $filter_attr = [], $where_ext = [], $goods_num = 0, $size = 10, $page = 1, $sort = 'goods_id', $order = 'DESC', $show_visual = 0)
    {
        $time = TimeRepository::getGmTime();

        /* 查询分类商品数据 */
        $res = Goods::where('is_show', 1)
            ->where('is_alone_sale', 1)
            ->where('is_delete', 0);

        $presale_goods_id = $where_ext['presale_goods_id'] ?? [];

        if ($keywords) {
            $keywordsParam = [
                'keywords' => $keywords,
                'presale_goods_id' => $presale_goods_id,
                'brand_id' => $brand_id,
                'brand_name' => $where_ext['brand_name'] ?? ''
            ];

            $brandKeyword = $this->goodsCommonService->keywordFilter($keywordsParam);
            if ($brandKeyword) {
                $res = $this->goodsCommonService->searchKeywordFilter($res, $brandKeyword, $keywordsParam);
            } else {
                $res = $this->goodsCommonService->searchKeywords($res, $keywordsParam);
            }
        } else {

            if ($brand_id) {
                $brand_id = BaseRepository::getExplode($brand_id);
                $res = $res->whereIn('brand_id', $brand_id);
            }

            $res = $res->where(function ($query) use ($presale_goods_id) {
                $query = $query->where('is_on_sale', 1);

                //兼容预售
                if ($presale_goods_id) {
                    $query->orWhere(function ($query) use ($presale_goods_id) {
                        $query->where('is_on_sale', 0)
                            ->whereIn('goods_id', $presale_goods_id);
                    });
                }
            });

            /* 查询扩展分类数据 */
            $extension_goods = $this->goodsCommonService->getCategoryGoodsId($children);

            $goodsParam = [
                'children' => $children,
                'extension_goods' => $extension_goods
            ];

            // 子分类 或 扩展分类
            $res = $res->where(function ($query) use ($goodsParam) {
                if ($goodsParam['children']) {
                    $query = $query->whereIn('cat_id', $goodsParam['children']);
                }
                if ($goodsParam['extension_goods']) {
                    $query->orWhere(function ($query) use ($goodsParam) {
                        $query->whereIn('goods_id', $goodsParam['extension_goods']);
                    });
                }
            });
        }

        //仅看有货
        if ($goods_num > 0) {
            $res = $res->where('goods_number', '>', 0);
        }

        $ru_id = $where_ext['ru_id'] ?? 0;
        if ($ru_id > 0) {
            $res = $res->where('user_id', $ru_id);
        }

        if ($price_min > 0) {
            $res = $res->where('shop_price', '>=', $price_min);
        }

        if ($price_max > 0) {
            $res = $res->where('shop_price', '<=', $price_max);
        }

        if (config('shop.review_goods')) {
            $res = $res->whereIn('review_status', [3, 4, 5]);
        }

        if (!empty($filter_attr)) {
            $goodsList = GoodsAttr::whereIn('goods_attr_id', $filter_attr)->pluck('goods_id');
            $goodsList = $goodsList ? $goodsList->toArray() : [];
            $goodsList = $goodsList ? array_unique($goodsList) : [];

            if ($goodsList) {
                $res = $res->whereIn('goods_id', $goodsList);
            }
        }

        /* 查询仅自营和标识自营店铺的商品 */
        if (isset($where_ext['self']) && $where_ext['self'] == 1) {
            $res = $res->where(function ($query) use ($where_ext) {
                $query->where('user_id', 0)->orWhere(function ($query) use ($where_ext) {
                    $query->whereIn('user_id', $where_ext['self_run_list']);
                });
            });
        }

        // 是否免邮
        if (isset($where_ext['ship']) && $where_ext['ship'] == 1) {
            $res = $res->where('is_shipping', 1);
        }

        $warehouse_id = $where_ext['warehouse_id'] ?? 0;
        $area_id = $where_ext['area_id'] ?? 0;
        $area_city = $where_ext['area_city'] ?? 0;

        /* 关联地区 */
        $res = $this->dscRepository->getAreaLinkGoods($res, $area_id, $area_city);

        if ($uid > 0) {
            $rank = $this->userCommonService->getUserRankByUid($uid);
            $user_rank = $rank['rank_id'];
            $discount = isset($rank['discount']) ? $rank['discount'] : 100;
        } else {
            $user_rank = 1;
            $discount = 100;
        }

        $intro = $where_ext['intro'] ?? '';

        $promotion = $where_ext['promotion'] ?? 0;
        if ($promotion) {
            $intro = 'promote';
        }

        if ($intro == 'hot') {
            $res = $res->where('is_hot', 1);
        } elseif ($intro == 'new') {
            $res = $res->where('is_new', 1);
        } elseif ($intro == 'best') {
            $res = $res->where('is_best', 1);
        } elseif ($intro == 'promote') {
            $res = $res->where('is_promote', 1)
                ->where('promote_price', '>', 0)
                ->where('promote_start_date', '<=', $time)
                ->where('promote_end_date', '>=', $time);
        }

        // 优惠券商品条件
        $cou_id = $where_ext['cou_id'] ?? 0;
        if (empty($children) && $cou_id > 0) {
            $cou_data = Coupons::where('cou_id', $cou_id)
                ->where('status', COUPON_STATUS_EFFECTIVE);
            $cou_data = BaseRepository::getToArrayFirst($cou_data);

            if ($cou_data) {
                // 优惠券商品 过滤虚拟商品
                $res = $res->where('user_id', $cou_data['ru_id'])->where('is_real', 1);
                $cou_count = CouponsUser::where('is_delete', 0)->where('user_id', $uid)->where('cou_id', $cou_id)->count();

                if ($cou_count == 0) {
                    if ($cou_data['cou_ok_goods']) {
                        $cou_data['cou_ok_goods'] = BaseRepository::getExplode($cou_data['cou_ok_goods']);
                        $res = $res->whereIn('goods_id', $cou_data['cou_ok_goods']);
                    } elseif ($cou_data['cou_ok_cat']) {
                        $cou_children = $this->couponsService->getCouChildren($cou_data['cou_ok_cat']);
                        $cou_children = BaseRepository::getExplode($cou_children);
                        if ($cou_children) {
                            $res = $res->whereIn('cat_id', $cou_children);
                        }
                    }
                } else {
                    if ($cou_data['cou_goods']) {
                        $cou_data['cou_goods'] = BaseRepository::getExplode($cou_data['cou_goods']);
                        $res = $res->whereIn('goods_id', $cou_data['cou_goods']);
                    } elseif ($cou_data['spec_cat']) {
                        $cou_children = $this->couponsService->getCouChildren($cou_data['spec_cat']);
                        $cou_children = BaseRepository::getExplode($cou_children);
                        if ($cou_children) {
                            $res = $res->whereIn('cat_id', $cou_children);
                        }
                    }
                }
            }
        }

        if (!empty($keywords) && isset($where_ext['brand_name']) && !empty($where_ext['brand_name'])) {
            $keywords = BaseRepository::getArrayPrepend($keywords, $where_ext['brand_name']);
            $keywords = $keywords ? BaseRepository::getArrayUnique($keywords) : [];
        }

        if (strpos($sort, 'goods_id') !== false) {
            // 排序关键词匹配度 默认时优先匹配关键词
            if (!empty($keywords)) {
                foreach ($keywords as $value) {
                    $res = $res->orderByRaw("LOCATE('" . $value . "',goods_name) DESC");
                }
            }

            $sort = "sort_order";
            $res = $res->orderBy('weights', 'DESC'); // 权重值
            $res = $res->orderBy($sort, $order)->orderBy('goods_id', $order);
        } else {
            $res = $res->orderBy($sort, $order);
            $res = $res->orderBy('weights', 'DESC'); // 权重值

            // 排序关键词匹配度 有筛选时优先匹配筛选
            if (!empty($keywords)) {
                foreach ($keywords as $value) {
                    $res = $res->orderByRaw("LOCATE('" . $value . "',goods_name) DESC");
                }
            }
        }

        if ($brand_id) {
            $res = $res->orderBy('brand_id', 'DESC'); // 品牌自增权重
        }

        $start = ($page - 1) * $size;
        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($size > 0) {
            $res = $res->take($size);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');

            $memberPrice = GoodsDataHandleService::goodsMemberPrice($goods_id, $user_rank);
            $warehouseGoods = GoodsDataHandleService::getWarehouseGoodsDataList($goods_id, $warehouse_id);
            $warehouseAreaGoods = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods_id, $area_id, $area_city);
            $presaleActivityList = GoodsDataHandleService::PresaleActivityDataList($goods_id);
            $merchantUseGoodsLabelList = GoodsDataHandleService::gettMerchantUseGoodsLabelDataList($goods_id);
            $merchantNoUseGoodsLabelList = GoodsDataHandleService::getMerchantNoUseGoodsLabelDataList($goods_id);

            $seller_id = BaseRepository::getKeyPluck($res, 'user_id');

            $sellerShopinfoList = MerchantDataHandleService::SellerShopinfoDataList($seller_id);
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($seller_id, 0, $sellerShopinfoList);

            $galleryList = GalleryDataHandleService::getGoodsGalleryDataList($goods_id);

            $productList = [];
            $productWarehouseList = [];
            $productAreaList = [];
            if ($show_visual == 1) {
                $productList = GoodsDataHandleService::getProductsDataList($goods_id, ['product_id', 'goods_id', 'product_number']);
                $productWarehouseList = GoodsDataHandleService::getProductsWarehouseDataList($goods_id, $warehouse_id, ['product_id', 'goods_id', 'product_number']);
                $productAreaList = GoodsDataHandleService::getProductsAreaDataList($goods_id, $area_id, $area_city, ['product_id', 'goods_id', 'product_number']);
            }

            foreach ($res as $k => $row) {

                /* 查询商品货品总库存 */
                if ($show_visual == 1) {
                    if ($row['model_price'] == 1) {
                        $product = $productWarehouseList[$row['goods_id']] ?? [];
                        $row['goods_number'] = $product ? BaseRepository::getArraySum($product, 'product_number') : $row['goods_number'];
                    } elseif ($row['model_price'] == 2) {
                        $product = $productAreaList[$row['goods_id']] ?? [];
                        $row['goods_number'] = $product ? BaseRepository::getArraySum($product, 'product_number') : $row['goods_number'];
                    } else {
                        $product = $productList[$row['goods_id']] ?? [];
                        $row['goods_number'] = $product ? BaseRepository::getArraySum($product, 'product_number') : $row['goods_number'];
                    }
                }

                $sellerShopinfo = $merchantList[$row['user_id']] ?? [];

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

                $price = $this->goodsCommonService->getGoodsPrice($price, $discount / 100, $row);

                $row['shop_price'] = $price['shop_price'];
                $row['promote_price'] = $price['promote_price'];
                $row['goods_number'] = $price['goods_number'];

                $arr[$k] = $row;

                $sql = [
                    'where' => [
                        [
                            'name' => 'is_finished',
                            'value' => 0
                        ],
                        [
                            'name' => 'start_time',
                            'condition' => '<',
                            'value' => $time
                        ],
                        [
                            'name' => 'end_time',
                            'condition' => '>',
                            'value' => $time
                        ]
                    ]
                ];

                $goodsPresale = $presaleActivityList[$row['goods_id']] ?? [];
                $presale = BaseRepository::getArraySqlFirst($goodsPresale, $sql);
                $arr[$k]['get_presale_activity'] = $presale ? $presale : null;

                if ($presale) {
                    //兼容预售
                    $arr[$k]['presale_id'] = $presale['act_id'] ?? 0;
                    $arr[$k]['url'] = dsc_url('/#/presale/detail/' . $arr[$k]['presale_id']);
                    $arr[$k]['app_page'] = config('route.presale.detail') . $arr[$k]['presale_id'];
                } else {
                    $arr[$k]['presale_id'] = 0;
                    $arr[$k]['url'] = dsc_url('/#/goods/' . $row['goods_id']);
                    $arr[$k]['app_page'] = config('route.goods.detail') . $row['goods_id'];
                }

                $arr[$k]['model_price'] = $row['model_price'];

                if ($row['promote_price'] > 0) {
                    $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                } else {
                    $promote_price = 0;
                }

                /* 处理商品水印图片 */
                $watermark_img = '';
                if ($promote_price > 0) {
                    $watermark_img = "watermark_promote_small";
                } elseif ($row['is_new'] != 0) {
                    $watermark_img = "watermark_new_small";
                } elseif ($row['is_best'] != 0) {
                    $watermark_img = "watermark_best_small";
                } elseif ($row['is_hot'] != 0) {
                    $watermark_img = 'watermark_hot_small';
                }

                if ($watermark_img != '') {
                    $arr[$k]['watermark_img'] = $watermark_img;
                }

                $arr[$k]['sort_order'] = $row['sort_order'];

                $arr[$k]['goods_id'] = $row['goods_id'];
                $arr[$k]['goods_name'] = $row['goods_name'];
                $arr[$k]['name'] = $row['goods_name'];
                $arr[$k]['goods_brief'] = $row['goods_brief'];
                $arr[$k]['sales_volume'] = $row['sales_volume'];
                $arr[$k]['is_promote'] = $row['is_promote'];
                $arr[$k]['promote_start_date'] = $row['promote_start_date'];
                $arr[$k]['promote_end_date'] = $row['promote_end_date'];

                if ($promote_price > 0) {
                    $arr[$k]['is_promote'] = 1;
                    $arr[$k]['market_price'] = $row['shop_price'];
                } else {
                    $arr[$k]['is_promote'] = 0;
                    $arr[$k]['market_price'] = $row['market_price'];
                }
                $arr[$k]['market_price'] = StrRepository::priceFormat($arr[$k]['market_price']);

                $goodsSelf = false;
                if ($row['user_id'] == 0) {
                    $goodsSelf = true;
                }

                $arr[$k]['market_price_formated'] = $this->dscRepository->getPriceFormat($arr[$k]['market_price'], true, true, $goodsSelf);
                $arr[$k]['shop_price'] = $row['shop_price'];
                if ($promote_price > 0) {
                    $arr[$k]['shop_price_formated'] = $this->dscRepository->getPriceFormat($row['promote_price'], true, true, $goodsSelf);
                    $arr[$k]['shop_price'] = $row['promote_price'];
                } else {
                    $arr[$k]['shop_price_formated'] = $this->dscRepository->getPriceFormat($row['shop_price'], true, true, $goodsSelf);
                }

                $arr[$k]['shop_price'] = StrRepository::priceFormat($arr[$k]['shop_price'], true, true, $goodsSelf);
                $arr[$k]['shop_price'] = $this->dscRepository->getPriceFormat($arr[$k]['shop_price'], true, false, $goodsSelf);

                $arr[$k]['type'] = $row['goods_type'];
                $arr[$k]['promote_price'] = ($promote_price > 0) ? $this->dscRepository->getPriceFormat($row['promote_price'], true, true, $goodsSelf) : '';
                $arr[$k]['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                $arr[$k]['goods_img'] = $this->dscRepository->getImagePath($row['goods_img']);
                $arr[$k]['original_img'] = $this->dscRepository->getImagePath($row['original_img']);
                $arr[$k]['is_hot'] = $row['is_hot'];
                $arr[$k]['is_best'] = $row['is_best'];
                $arr[$k]['is_new'] = $row['is_new'];
                $arr[$k]['pictures'] = $this->goodsGalleryService->getGoodsGallery($row['goods_id'], $galleryList, $row['goods_thumb'], 1); // 商品相册
                if (!empty($arr[$k]['pictures'])) {
                    $arr[$k]['goods_thumb'] = !empty($arr[$k]['pictures']['0']['thumb_url']) ? $arr[$k]['pictures']['0']['thumb_url'] : $arr[$k]['goods_thumb'];
                }

                $arr[$k]['self_run'] = $sellerShopinfo['self_run'] ?? 0;

                $arr[$k]['is_shipping'] = $row['is_shipping'];

                $arr[$k]['goods_number'] = $row['goods_number'];

                $arr[$k]['rz_shop_name'] = $sellerShopinfo['shop_name'] ?? '';
                $arr[$k]['user_id'] = $row['user_id'];
                $arr[$k]['country_icon'] = $sellerShopinfo['country_icon'] ?? '';

                $arr[$k]['sale'] = $row['sales_volume'];

                // 活动标签
                $where = [
                    'user_id' => $arr[$k]['user_id'],
                    'goods_id' => $row['goods_id'],
                    'self_run' => $arr[$k]['self_run'],
                ];
                $goods_label_all = $this->goodsCommonService->getListGoodsLabelList($merchantUseGoodsLabelList, $merchantNoUseGoodsLabelList, $where);

                $arr[$k]['goods_label'] = $goods_label_all['goods_label'] ?? [];
                $arr[$k]['goods_label_suspension'] = $goods_label_all['goods_label_suspension'] ?? [];
            }
        }

        return $arr;
    }

    /**
     * 获得分类下的商品总数
     *
     * @param array $children
     * @param int $brand_id
     * @param int $area_id
     * @param int $area_city
     * @param int $min
     * @param int $max
     * @param array $filter_attr
     * @param array $attrGoodsList
     * @param array $where_ext
     * @return array|mixed
     */
    public function getCagtegoryGoodsCount($children = [], $brand_id = 0, $area_id = 0, $area_city = 0, $min = 0, $max = 0, $filter_attr = [], $attrGoodsList = [], $where_ext = [])
    {
        $filter_attr = BaseRepository::getExplode($filter_attr);
        $filter_attr = BaseRepository::getArrayUnique($filter_attr);
        $filter_attr = count($filter_attr) == 1 && empty((int)$filter_attr[0]) ? [] : $filter_attr;

        /* 当有属性时，获取商品时空的则直接返回空数据 */
        if ($filter_attr && empty($attrGoodsList)) {
            return 0;
        }

        /* 查询扩展分类数据 */
        $extension_goods = $this->goodsCommonService->getCategoryGoodsId($children);;

        $goodsParam = [
            'children' => $children,
            'extension_goods' => $extension_goods
        ];

        /* 查询分类商品数据 */
        $res = Goods::where('is_alone_sale', 1)
            ->where('is_delete', 0)
            ->where('is_show', 1)
            ->where(function ($query) use ($goodsParam) {
                if (!empty($goodsParam['children'])) {
                    $query = $query->whereIn('cat_id', $goodsParam['children']);
                }

                if ($goodsParam['extension_goods']) {
                    $query->orWhere(function ($query) use ($goodsParam) {
                        $query->whereIn('goods_id', $goodsParam['extension_goods']);
                    });
                }
            });

        $presale_goods_id = $where_ext['presale_goods_id'] ?? [];

        $res = $res->where(function ($query) use ($presale_goods_id) {
            $query = $query->where('is_on_sale', 1);

            //兼容预售
            if ($presale_goods_id) {
                $query->orWhere(function ($query) use ($presale_goods_id) {
                    $query->where('is_on_sale', 0)
                        ->whereIn('goods_id', $presale_goods_id);
                });
            }
        });

        if ($brand_id) {
            $brand_id = BaseRepository::getExplode($brand_id);
            $res = $res->whereIn('brand_id', $brand_id);
        }

        if ($min > 0) {
            $res = $res->where('shop_price', '>=', $min);
        }

        if ($max > 0) {
            $res = $res->where('shop_price', '<=', $max);
        }

        if (!empty($attrGoodsList)) {
            $res = $res->whereIn('goods_id', $attrGoodsList);
        }

        /* 查询仅自营和标识自营店铺的商品 */
        if (isset($where_ext['self']) && $where_ext['self'] == 1) {
            $res = $res->where(function ($query) use ($where_ext) {
                $query->where('user_id', 0)->orWhere(function ($query) use ($where_ext) {
                    $query->whereIn('user_id', $where_ext['self_run_list']);
                });
            });
        }

        if (isset($where_ext['have']) && $where_ext['have'] == 1) {
            $res = $res->where('goods_number', '>', 0);
        }

        if (isset($where_ext['ship']) && $where_ext['ship'] == 1) {
            $res = $res->where('is_shipping', 1);
        }

        if (config('shop.review_goods')) {
            $res = $res->whereIn('review_status', [3, 4, 5]);
        }

        /* 关联地区显示商品 */
        $res = $this->dscRepository->getAreaLinkGoods($res, $area_id, $area_city);


        $res = $res->count();

        /* 返回商品总数 */
        return $res;
    }

    /**
     * 获得分类下的商品
     *
     * @param array $children
     * @param int $brand_id
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param int $min
     * @param int $max
     * @param array $filter_attr
     * @param array $attrGoodsList
     * @param array $where_ext
     * @param int $goods_num
     * @param int $size
     * @param int $page
     * @param string $sort
     * @param string $order
     * @return array
     * @throws \Exception
     */
    public function getCategoryGoodsList($children = [], $brand_id = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0, $min = 0, $max = 0, $filter_attr = [], $attrGoodsList = [], $where_ext = [], $goods_num = 0, $size = 10, $page = 1, $sort = 'goods_id', $order = 'DESC')
    {
        $time = TimeRepository::getGmTime();

        /* 过滤属性筛选 */
        $filter_attr = BaseRepository::getExplode($filter_attr);
        $filter_attr = BaseRepository::getArrayUnique($filter_attr);
        $filter_attr = count($filter_attr) == 1 && empty((int)$filter_attr[0]) ? [] : $filter_attr;

        /* 当有属性时，获取商品时空的则直接返回空数据 */
        if ($filter_attr && empty($attrGoodsList)) {
            return [];
        }

        /* 查询扩展分类数据 */
        $extension_goods = $this->goodsCommonService->getCategoryGoodsId($children);;
        $goodsParam = [
            'children' => $children,
            'extension_goods' => $extension_goods
        ];

        /* 查询分类商品数据 */
        $res = Goods::where('is_alone_sale', 1)
            ->where('is_delete', 0)
            ->where('is_show', 1)
            ->where(function ($query) use ($goodsParam) {
                if (!empty($goodsParam['children'])) {
                    $query = $query->whereIn('cat_id', $goodsParam['children']);
                }

                if ($goodsParam['extension_goods']) {
                    $query->orWhere(function ($query) use ($goodsParam) {
                        $query->whereIn('goods_id', $goodsParam['extension_goods']);
                    });
                }
            });

        $presale_goods_id = $where_ext['presale_goods_id'] ?? [];

        $res = $res->where(function ($query) use ($presale_goods_id) {
            $query = $query->where('is_on_sale', 1);

            //兼容预售
            if ($presale_goods_id) {
                $query->orWhere(function ($query) use ($presale_goods_id) {
                    $query->where('is_on_sale', 0)
                        ->whereIn('goods_id', $presale_goods_id);
                });
            }
        });

        if ($brand_id) {
            $brand_id = BaseRepository::getExplode($brand_id);
            $res = $res->whereIn('brand_id', $brand_id);
        }

        if ($min > 0) {
            $res = $res->where('shop_price', '>=', $min);
        }

        if ($max > 0) {
            $res = $res->where('shop_price', '<=', $max);
        }

        if (!empty($attrGoodsList)) {
            $res = $res->whereIn('goods_id', $attrGoodsList);
        }

        /* 查询仅自营和标识自营店铺的商品 */
        if (isset($where_ext['have']) && $where_ext['self'] == 1) {
            $res = $res->where(function ($query) use ($where_ext) {
                $query->where('user_id', 0)->orWhere(function ($query) use ($where_ext) {
                    $query->whereIn('user_id', $where_ext['self_run_list']);
                });
            });
        }

        if (isset($where_ext['have']) && $where_ext['have'] == 1) {
            $res = $res->where('goods_number', '>', 0);
        }

        if (isset($where_ext['ship']) && $where_ext['ship'] == 1) {
            $res = $res->where('is_shipping', 1);
        }

        if (config('shop.review_goods')) {
            $res = $res->whereIn('review_status', [3, 4, 5]);
        }

        /* 关联地区显示商品 */
        $res = $this->dscRepository->getAreaLinkGoods($res, $area_id, $area_city);

        $user_rank = session('user_rank', 0);
        $discount = session('discount', 1);

        //瀑布流加载分类商品 by wu
        if ($goods_num) {
            $start = $goods_num;
        } else {
            $start = ($page - 1) * $size;
        }

        if (strpos($sort, 'goods_id') !== false) {
            $sort = "sort_order";
            $res = $res->orderBy('weights', 'DESC'); // 权重值
            $res = $res->orderBy($sort, $order)->orderBy('goods_id', $order);
        } else {
            $res = $res->orderBy($sort, $order);
            $res = $res->orderBy('weights', 'DESC'); // 权重值
        }

        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($size > 0) {
            $res = $res->take($size);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];

        if ($res) {
            $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');

            $memberPrice = GoodsDataHandleService::goodsMemberPrice($goods_id, $user_rank);
            $warehouseGoods = GoodsDataHandleService::getWarehouseGoodsDataList($goods_id, $warehouse_id);
            $warehouseAreaGoods = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods_id, $area_id, $area_city);
            $productsWarehouse = GoodsDataHandleService::getProductsWarehouseDataList($goods_id, $warehouse_id);
            $productsArea = GoodsDataHandleService::getProductsAreaDataList($goods_id, $area_id, $area_city);
            $products = GoodsDataHandleService::getProductsDataList($goods_id);
            $commentGoodsList = GoodsDataHandleService::CommentGoodsReviewCount($goods_id, ['comment_id', 'id_value']);
            $collectGoodsList = GoodsDataHandleService::CollectGoodsDataList($goods_id, ['goods_id']);
            $presaleActivityList = GoodsDataHandleService::PresaleActivityDataList($goods_id);

            $merchantUseGoodsLabelList = GoodsDataHandleService::gettMerchantUseGoodsLabelDataList($goods_id);
            $merchantNoUseGoodsLabelList = GoodsDataHandleService::getMerchantNoUseGoodsLabelDataList($goods_id);

            $seller_id = BaseRepository::getKeyPluck($res, 'user_id');

            $shopInformation = MerchantDataHandleService::MerchantsShopInformationDataList($seller_id);
            $sellerShopinfo = MerchantDataHandleService::SellerShopinfoDataList($seller_id);
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($seller_id, 0, $sellerShopinfo, $shopInformation);

            $galleryList = GalleryDataHandleService::getGoodsGalleryDataList($goods_id);

            foreach ($res as $row) {

                $shop_price = $row['shop_price'];

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

                $arr[$row['goods_id']] = $row;

                $arr[$row['goods_id']]['model_price'] = $row['model_price'];

                if ($row['promote_price'] > 0) {
                    $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                } else {
                    $promote_price = 0;
                }

                $sql = [
                    'where' => [
                        [
                            'name' => 'is_finished',
                            'value' => 0
                        ],
                        [
                            'name' => 'start_time',
                            'condition' => '<',
                            'value' => $time
                        ],
                        [
                            'name' => 'end_time',
                            'condition' => '>',
                            'value' => $time
                        ]
                    ]
                ];

                $goodsPresale = $presaleActivityList[$row['goods_id']] ?? [];
                $presale = BaseRepository::getArraySqlFirst($goodsPresale, $sql);

                /* 预售商品 start */
                if ($presale) {
                    $arr[$row['goods_id']]['presale'] = lang('common.presell');
                    $arr[$row['goods_id']]['act_id'] = $presale['act_id'];
                    $arr[$row['goods_id']]['act_name'] = $presale['act_name'];
                    $arr[$row['goods_id']]['thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                    $arr[$row['goods_id']]['goods_img'] = $this->dscRepository->getImagePath($row['goods_img']);
                    $arr[$row['goods_id']]['purl'] = $this->dscRepository->buildUri('presale', ['act' => 'view', 'presaleid' => $presale['act_id']], $presale['goods_name']);
                    $arr[$row['goods_id']]['rz_shop_name'] = isset($row['get_shop_info']['rz_shop_name']) ? $row['get_shop_info']['rz_shop_name'] : ''; //店铺名称
                    $arr[$row['goods_id']]['start_time_date'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $presale['start_time']);
                    $arr[$row['goods_id']]['end_time_date'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $presale['end_time']);

                    if ($presale['start_time'] >= $time) {
                        $arr[$row['goods_id']]['no_start'] = 1;
                    }
                    if ($presale['end_time'] <= $time) {
                        $arr[$row['goods_id']]['already_over'] = 1;
                    }
                } else {
                    $arr[$row['goods_id']]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
                }
                /* 预售商品 end */

                /* 处理商品水印图片 */
                $watermark_img = '';

                if ($promote_price > 0) {
                    $watermark_img = "watermark_promote_small";
                } elseif ($row['is_new'] != 0) {
                    $watermark_img = "watermark_new_small";
                } elseif ($row['is_best'] != 0) {
                    $watermark_img = "watermark_best_small";
                } elseif ($row['is_hot'] != 0) {
                    $watermark_img = 'watermark_hot_small';
                }

                if ($watermark_img != '') {
                    $arr[$row['goods_id']]['watermark_img'] = $watermark_img;
                }

                $arr[$row['goods_id']]['sort_order'] = $row['sort_order'];

                $arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
                $arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
                $arr[$row['goods_id']]['name'] = $row['goods_name'];
                $arr[$row['goods_id']]['goods_brief'] = $row['goods_brief'];
                $arr[$row['goods_id']]['sales_volume'] = $row['sales_volume'];

                $arr[$row['goods_id']]['goods_style_name'] = $this->goodsCommonService->addStyle($row['goods_name'], $row['goods_name_style']);

                if ($promote_price > 0) {
                    $row['is_promote'] = 1;
                    $row['shop_price'] = $shop_price;
                } else {
                    $row['is_promote'] = 0;
                }

                $goodsSelf = false;
                if ($row['user_id'] == 0) {
                    $goodsSelf = true;
                }

                $arr[$row['goods_id']]['is_promote'] = $row['is_promote'];
                $arr[$row['goods_id']]['market_price'] = $this->dscRepository->getPriceFormat($row['market_price'], true, true, $goodsSelf);
                $arr[$row['goods_id']]['shop_price'] = $this->dscRepository->getPriceFormat($row['shop_price'], true, true, $goodsSelf);
                $arr[$row['goods_id']]['type'] = $row['goods_type'];
                $arr[$row['goods_id']]['promote_price'] = ($promote_price > 0) ? $this->dscRepository->getPriceFormat($promote_price, true, true, $goodsSelf) : '';
                $arr[$row['goods_id']]['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                $arr[$row['goods_id']]['goods_img'] = $this->dscRepository->getImagePath($row['goods_img']);
                $arr[$row['goods_id']]['is_hot'] = $row['is_hot'];
                $arr[$row['goods_id']]['is_best'] = $row['is_best'];
                $arr[$row['goods_id']]['is_new'] = $row['is_new'];
                $arr[$row['goods_id']]['self_run'] = $shopInformation[$row['user_id']]['self_run'] ?? 0;
                $arr[$row['goods_id']]['is_shipping'] = $row['is_shipping'];

                /* 商品仓库货品 */
                if ($row['model_price'] == 1) {
                    $prod = $productsWarehouse[$row['goods_id']] ?? [];
                } elseif ($row['model_price'] == 2) {
                    $prod = $productsArea[$row['goods_id']] ?? [];
                } else {
                    $prod = $products[$row['goods_id']] ?? [];
                }

                if (empty($prod)) { //当商品没有属性库存时
                    $arr[$row['goods_id']]['prod'] = 1;
                } else {
                    $arr[$row['goods_id']]['prod'] = 0;
                }

                $arr[$row['goods_id']]['goods_number'] = $row['goods_number'];

                $basic_info = $sellerShopinfo[$row['user_id']] ?? [];

                $chat = $this->dscRepository->chatQq($basic_info);

                $arr[$row['goods_id']]['kf_type'] = $chat['kf_type'];
                $arr[$row['goods_id']]['kf_ww'] = $chat['kf_ww'];
                $arr[$row['goods_id']]['kf_qq'] = $chat['kf_qq'];

                /* 评分数 */
                $sql = [
                    'where' => [
                        [
                            'name' => 'id_value',
                            'value' => $row['goods_id'],
                        ]
                    ]
                ];

                $comment_list = BaseRepository::getArraySqlGet($commentGoodsList, $sql, 1);
                $review_count = BaseRepository::getArrayCount($comment_list);

                $arr[$row['goods_id']]['review_count'] = $review_count;

                $arr[$row['goods_id']]['pictures'] = $this->goodsGalleryService->getGoodsGallery($row['goods_id'], $galleryList, $row['goods_thumb'], 6); // 商品相册

                $shop_information = $merchantList[$row['user_id']] ?? []; //通过ru_id获取到店铺信息;
                $arr[$row['goods_id']]['rz_shop_name'] = isset($shop_information['shop_name']) ? $shop_information['shop_name'] : ''; //店铺名称
                $arr[$row['goods_id']]['user_id'] = $row['user_id'];

                if (config('shop.customer_service') == 0) {
                    $seller_id = 0;
                } else {
                    $seller_id = $row['user_id'];
                }

                $build_uri = [
                    'urid' => $row['user_id'],
                    'append' => $arr[$row['goods_id']]['rz_shop_name']
                ];

                $domain_url = $this->merchantCommonService->getSellerDomainUrl($row['user_id'], $build_uri);
                $arr[$row['goods_id']]['store_url'] = $domain_url['domain_name'];

                /*  @author-bylu 判断当前商家是否允许"在线客服" start */
                //平台是否允许商家使用"在线客服";

                $arr[$row['goods_id']]['is_im'] = 0;
                if (isset($shop_information['is_im']) && !empty($shop_information['is_im']) && empty($shop_information['kf_qq'])) {
                    $arr[$row['goods_id']]['is_im'] = $shop_information['is_im'];
                }

                //判断当前商家是平台,还是入驻商家 bylu
                if ($seller_id == 0) {
                    //判断平台是否开启了IM在线客服
                    $kf_im_switch = MerchantDataHandleService::kfImSwitch();

                    if ($kf_im_switch) {
                        $arr[$row['goods_id']]['is_dsc'] = true;
                    } else {
                        $arr[$row['goods_id']]['is_dsc'] = false;
                    }
                } else {
                    $arr[$row['goods_id']]['is_dsc'] = false;
                }
                /*  @author-bylu  end */

                /* 商品关注度 */
                $sql = [
                    'where' => [
                        [
                            'name' => 'goods_id',
                            'value' => $row['goods_id'],
                        ],
                        [
                            'name' => 'is_attention',
                            'value' => 1
                        ],
                        [
                            'name' => 'user_id',
                            'value' => session('user_id', 0)
                        ]
                    ]
                ];

                $collect_list = BaseRepository::getArraySqlGet($collectGoodsList, $sql, 1);
                $collect_count = BaseRepository::getArrayCount($collect_list);

                $arr[$row['goods_id']]['is_collect'] = $collect_count;
                $arr[$row['goods_id']]['shop_information'] = $shop_information;

                // 活动标签
                $where = [
                    'user_id' => $arr[$row['goods_id']]['user_id'],
                    'goods_id' => $row['goods_id'],
                    'self_run' => $arr[$row['goods_id']]['self_run'],
                ];
                $goods_label_all = $this->goodsCommonService->getListGoodsLabelList($merchantUseGoodsLabelList, $merchantNoUseGoodsLabelList, $where);

                $arr[$row['goods_id']]['goods_label'] = $goods_label_all['goods_label'] ?? [];
                $arr[$row['goods_id']]['goods_label_suspension'] = $goods_label_all['goods_label_suspension'] ?? [];

                $arr[$row['goods_id']]['country_icon'] = $shop_information['country_icon'] ?? '';
            }
        }

        return $arr;
    }

    /**
     * 获得当前分类下商品价格的最大值、最小值
     *
     * @param array $children
     * @param int $brand_id
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param array $where_ext
     * @param array $goods_ids
     * @param array $keywords
     * @param string $sc_ds
     * @param string $cat_type
     * @return array
     */
    public function getGoodsPriceMaxMin($children = [], $brand_id = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0, $where_ext = [], $goods_ids = [], $keywords = [], $sc_ds = '', $cat_type = 'cat_id')
    {
        /* 查询扩展分类数据 */
        if ($cat_type == 'cat_id') {
            $extension_goods = $this->goodsCommonService->getCategoryGoodsId($children);;
        } else {
            $extension_goods = [];
        }

        $goodsParam = [
            'children' => $children,
            'extension_goods' => $extension_goods,
            'goods_ids' => $goods_ids,
            'cat_type' => $cat_type,
            'keywords' => $keywords,
            'presale_goods_id' => $where_ext['presale_goods_id'] ?? [],
        ];

        /* 查询分类商品数据 */
        $res = Goods::select('shop_price', 'model_price')
            ->where('is_alone_sale', 1)
            ->where('is_delete', 0)
            ->where(function ($query) use ($goodsParam) {
                if ($goodsParam['children']) {
                    $query = $query->whereIn($goodsParam['cat_type'], $goodsParam['children']);
                }

                if ($goodsParam['extension_goods']) {
                    $query->orWhere(function ($query) use ($goodsParam) {
                        $query = $query->whereIn('goods_id', $goodsParam['extension_goods']);

                        if ($goodsParam['goods_ids']) {
                            $query->orWhereIn('goods_id', $goodsParam['goods_ids']);
                        }
                    });
                }
            });

        if ($goodsParam['keywords']) {
            $res = $res->where(function ($query) use ($goodsParam) {
                $query->orWhere(function ($query) use ($goodsParam) {
                    $query = $query->where('is_on_sale', 1);

                    $query->where(function ($query) use ($goodsParam) {
                        foreach ($goodsParam['keywords'] as $key => $val) {
                            $query->orWhere(function ($query) use ($val) {
                                $val = $this->dscRepository->mysqlLikeQuote(trim($val));

                                $query = $query->orWhere('goods_name', 'like', '%' . $val . '%');
                                $query->orWhere('keywords', 'like', '%' . $val . '%');
                            });
                        }

                        $keyword_goods_sn = $goodsParam['keywords'][0] ?? '';

                        if ($keyword_goods_sn) {
                            // 搜索商品货号
                            $query->orWhere('goods_sn', 'like', '%' . $keyword_goods_sn . '%');
                        }
                    });
                });


                //兼容预售
                if ($goodsParam['keywords'] && isset($goodsParam['presale_goods_id']) && $goodsParam['presale_goods_id']) {
                    $query->orWhere(function ($query) use ($goodsParam) {
                        $query->where('is_on_sale', 0)
                            ->whereIn('goods_id', $goodsParam['presale_goods_id']);
                    });
                }
            });
        } else {
            $res = $res->where(function ($query) use ($goodsParam) {
                $query = $query->where('is_on_sale', 1);

                //兼容预售
                if ($goodsParam['presale_goods_id']) {
                    $query->orWhere(function ($query) use ($goodsParam) {
                        $query->where('is_on_sale', 0)
                            ->whereIn('goods_id', $goodsParam['presale_goods_id']);
                    });
                }
            });
        }

        if ($brand_id) {
            $brand_id = BaseRepository::getExplode($brand_id);
            $res = $res->whereIn('brand_id', $brand_id);
        }

        /* 查询仅自营和标识自营店铺的商品 */
        if (isset($where_ext['self']) && $where_ext['self'] == 1) {
            $res = $res->where(function ($query) use ($where_ext) {
                $query->where('user_id', 0)->orWhere(function ($query) use ($where_ext) {
                    $query->whereIn('user_id', $where_ext['self_run_list']);
                });
            });
        }

        if (isset($where_ext['have']) && $where_ext['have'] == 1) {
            $res = $res->where('goods_number', '>', 0);
        }

        if (isset($where_ext['ship']) && $where_ext['ship'] == 1) {
            $res = $res->where('is_shipping', 1);
        }

        if (config('shop.review_goods')) {
            $res = $res->whereIn('review_status', [3, 4, 5]);
        }

        /* 关联地区 */
        $res = $this->dscRepository->getAreaLinkGoods($res, $area_id, $area_city);

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $key => $val) {
                if ($val['shop_price'] <= 0) {
                    unset($res[$key]);
                }
            }
        }

        $min = $res ? collect($res)->min('shop_price') : 0;
        $max = $res ? collect($res)->max('shop_price') : 0;

        $arr = [
            'list' => $res,
            'min' => $min,
            'max' => $max
        ];

        return $arr;
    }

    /**
     * 获得当前分类下商品价格的跨度
     *
     * @access  public
     * @param array $list
     * @param int $min
     * @param int $dx
     *
     * @return array
     */
    public function getGoodsPriceGrade($list, $min, $dx)
    {
        $arr = [];
        if ($list) {
            foreach ($list as $key => $val) {
                $list[$key]['sn'] = intval(floor(($val['shop_price'] - $min) / $dx));
            }

            $list = BaseRepository::getGroupBy($list, 'sn');

            foreach ($list as $key => $val) {
                $arr[$key]['sn'] = $key;
                $arr[$key]['goods_num'] = collect($val)->count();
            }
        }

        return $arr;
    }

    /**
     * 获取商品属性
     *
     * @param array $filter_attr
     * @return array
     */
    public function goodsFilterAttr($filter_attr = [])
    {
        $filter_attr = BaseRepository::getExplode($filter_attr);

        if (!empty($filter_attr)) {
            $res = GoodsAttr::select('attr_id', 'attr_value')->whereIn('goods_attr_id', $filter_attr);
            $res = BaseRepository::getToArrayGet($res);

            $attr_id = BaseRepository::getKeyPluck($res, 'attr_id');
            $attr_id = BaseRepository::getArrayUnique($attr_id);
            $attr_value = BaseRepository::getKeyPluck($res, 'attr_value');
            $attr_value = BaseRepository::getArrayUnique($attr_value);

            $res = GoodsAttr::select('attr_id', 'attr_value', 'goods_id')->whereIn('attr_id', $attr_id)->whereIn('attr_value', $attr_value);
            $res = BaseRepository::getToArrayGet($res);

            if (config('shop.cat_attr_search') == 1) {
                $goodsList = BaseRepository::getArrayCompose($res, 'goods_id', 'attr_value');
                $goodsList = BaseRepository::SearchIntersectArray($attr_value, $goodsList);
            } else {
                $goodsList = BaseRepository::getKeyPluck($res, 'goods_id');
                $goodsList = BaseRepository::getArrayUnique($goodsList);
            }

            return $goodsList;
        }

        return [];
    }
}
