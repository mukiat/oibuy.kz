<?php

namespace App\Services\Goods;

use App\Extensions\File;
use App\Extensions\SharePoster;
use App\Models\BargainGoods;
use App\Models\Cart;
use App\Models\CartCombo;
use App\Models\CollectGoods;
use App\Models\Goods;
use App\Models\GoodsActivity;
use App\Models\GoodsAttr;
use App\Models\GoodsConshipping;
use App\Models\GoodsConsumption;
use App\Models\GoodsHistory;
use App\Models\GoodsVideo;
use App\Models\GroupGoods;
use App\Models\LinkDescGoodsid;
use App\Models\LinkGoods;
use App\Models\MemberPrice;
use App\Models\MerchantsShopInformation;
use App\Models\PresaleActivity;
use App\Models\SeckillGoods;
use App\Models\TeamGoods;
use App\Plugins\UserRights\Discount\Services\DiscountRightsService;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\StrRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Activity\DiscountService;
use App\Services\Category\CategoryService;
use App\Services\Common\AreaService;
use App\Services\Common\ConfigService;
use App\Services\Common\TemplateService;
use App\Services\Coupon\CouponDataHandleService;
use App\Services\Coupon\CouponService;
use App\Services\CrossBorder\CrossBorderService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\User\UserCommonService;
use Illuminate\Support\Facades\DB;

/**
 * Class GoodsMobileService
 * @package App\Services\Goods
 */
class GoodsMobileService
{
    protected $goodsAttrService;
    protected $couponService;
    protected $categoryService;
    protected $dscRepository;
    protected $goodsGalleryService;
    protected $goodsCommonService;
    protected $goodsWarehouseService;
    protected $goodsCommentService;
    protected $merchantCommonService;
    protected $sessionRepository;
    protected $discountService;
    protected $city = 0;
    protected $userCommonService;
    protected $templateService;

    public function __construct(
        GoodsAttrService $goodsAttrService,
        CouponService $couponService,
        CategoryService $categoryService,
        DscRepository $dscRepository,
        DiscountService $discountService,
        GoodsGalleryService $goodsGalleryService,
        GoodsCommonService $goodsCommonService,
        GoodsWarehouseService $goodsWarehouseService,
        GoodsCommentService $goodsCommentService,
        MerchantCommonService $merchantCommonService,
        SessionRepository $sessionRepository,
        UserCommonService $userCommonService,
        TemplateService $templateService
    )
    {
        //加载外部类
        $files = [
            'clips',
            'common',
            'time',
            'main',
            'order',
            'function',
            'base',
            'goods',
            'ecmoban'
        ];
        load_helper($files);
        $this->goodsAttrService = $goodsAttrService;
        $this->couponService = $couponService;
        $this->categoryService = $categoryService;
        $this->dscRepository = $dscRepository;
        $this->discountService = $discountService;
        $this->goodsGalleryService = $goodsGalleryService;
        $this->goodsCommonService = $goodsCommonService;
        $this->goodsWarehouseService = $goodsWarehouseService;
        $this->goodsCommentService = $goodsCommentService;
        $this->merchantCommonService = $merchantCommonService;
        $this->sessionRepository = $sessionRepository;
        $this->userCommonService = $userCommonService;
        $this->templateService = $templateService;
        $this->city = app(AreaService::class)->areaCookie();
    }

    /**
     * 商品详情
     *
     * @param $id
     * @param int $uid
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @return mixed
     * @throws \Exception
     */
    public function goodsInfo($id, $uid = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0)
    {
        $goods = Goods::where('goods_id', $id)
            ->where('is_delete', 0);

        $where = [
            'warehouse_id' => $warehouse_id,
            'area_id' => $area_id,
            'area_city' => $area_city,
            'area_pricetype' => config('shop.area_pricetype')
        ];

        if ($uid > 0) {
            $rank = $this->userCommonService->getUserRankByUid($uid);
            $user_rank = $rank['rank_id'];
            $user_discount = isset($rank['discount']) ? $rank['discount'] : 100;
        } else {
            $user_rank = 1;
            $user_discount = 100;
        }

        $goods = $goods->with([
            'getMemberPrice' => function ($query) use ($user_rank) {
                $query->where('user_rank', $user_rank);
            },
            'getWarehouseGoods' => function ($query) use ($warehouse_id) {
                $query->where('region_id', $warehouse_id);
            },
            'getWarehouseAreaGoods' => function ($query) use ($where) {
                $query = $query->where('region_id', $where['area_id']);

                if ($where['area_pricetype'] == 1) {
                    $query->where('city_id', $where['area_city']);
                }
            },
        ]);

        $goods = BaseRepository::getToArrayFirst($goods);

        if ($goods) {
            $price = [
                'model_price' => isset($goods['model_price']) ? $goods['model_price'] : 0,
                'user_price' => isset($goods['get_member_price']['user_price']) ? $goods['get_member_price']['user_price'] : 0,
                'percentage' => isset($goods['get_member_price']['percentage']) ? $goods['get_member_price']['percentage'] : 0,
                'warehouse_price' => isset($goods['get_warehouse_goods']['warehouse_price']) ? $goods['get_warehouse_goods']['warehouse_price'] : 0,
                'region_price' => isset($goods['get_warehouse_area_goods']['region_price']) ? $goods['get_warehouse_area_goods']['region_price'] : 0,
                'shop_price' => isset($goods['shop_price']) ? $goods['shop_price'] : 0,
                'warehouse_promote_price' => isset($goods['get_warehouse_goods']['warehouse_promote_price']) ? $goods['get_warehouse_goods']['warehouse_promote_price'] : 0,
                'region_promote_price' => isset($goods['get_warehouse_area_goods']['region_promote_price']) ? $goods['get_warehouse_area_goods']['region_promote_price'] : 0,
                'promote_price' => isset($goods['promote_price']) ? $goods['promote_price'] : 0,
                'wg_number' => isset($goods['get_warehouse_goods']['region_number']) ? $goods['get_warehouse_goods']['region_number'] : 0,
                'wag_number' => isset($goods['get_warehouse_area_goods']['region_number']) ? $goods['get_warehouse_area_goods']['region_number'] : 0,
                'goods_number' => isset($goods['goods_number']) ? $goods['goods_number'] : 0
            ];

            $price = $this->goodsCommonService->getGoodsPrice($price, $user_discount / 100, $goods);

            $goods['shop_price'] = $price['shop_price'];
            $goods['promote_price'] = $price['promote_price'];
            $goods['goods_number'] = $price['goods_number'];
        }

        return $goods;
    }

    /**
     * 获得推荐商品
     *
     * @param array $where
     * @return array|mixed
     * @throws \Exception
     */
    public function getRecommendGoods($where = [])
    {
        if (isset($where['type']) && !in_array($where['type'], ['best', 'new', 'hot'])) {
            return [];
        }

        $time = TimeRepository::getGmTime();

        $goods_id = [];
        $presaleActivityList = [];
        if (isset($where['presale']) && $where['presale'] == 'presale') {

            $cat_id = BaseRepository::getExplode($where['cat_id']);

            $goods_id = PresaleActivity::query()->select('goods_id');

            if ($cat_id) {
                $goods_id = $goods_id->whereIn('cat_id', $cat_id);
            }

            $goods_id = $goods_id->pluck('goods_id');
            $goods_id = BaseRepository::getToArray($goods_id);

            $presaleActivityList = GoodsDataHandleService::PresaleActivityDataList($goods_id);

            if ($presaleActivityList) {

                $presaleActivityList = BaseRepository::getArrayCollapse($presaleActivityList);

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
                        ],
                        [
                            'name' => 'review_status',
                            'value' => 3
                        ]
                    ]
                ];
                $presaleActivityList = BaseRepository::getArraySqlGet($presaleActivityList, $sql);

                $goods_id = BaseRepository::getKeyPluck($presaleActivityList, 'goods_id');
            }

            if (empty($goods_id)) {
                return [];
            }
        }

        //取出所有符合条件的商品数据，并将结果存入对应的推荐类型数组中
        $result = Goods::where('is_alone_sale', 1)
            ->where('is_delete', 0);

        if (!(isset($where['presale']) && $where['presale'] == 'presale')) {
            $result = $result->where('is_on_sale', 1);
        }

        if (config('shop.review_goods')) {
            $result = $result->whereIn('review_status', [3, 4, 5]);
        }

        if (!empty($goods_id)) {
            $result = $result->whereIn('goods_id', $goods_id);
        }

        $result = $this->dscRepository->getAreaLinkGoods($result, $where['area_id'], $where['area_city']);

        if (isset($where['seller_id'])) {
            $result = $result->where('user_id', $where['seller_id']);

            if ($where['type'] === 'new') {
                $result = $result->where(function ($query) {
                    $query->orWhere('store_new', 1);
                });
            } elseif ($where['type'] === 'best') {
                $result = $result->where(function ($query) {
                    $query->orWhere('store_best', 1);
                });
            } elseif ($where['type'] === 'hot') {
                $result = $result->where(function ($query) {
                    $query->orWhere('store_hot', 1);
                });
            }
        } else {
            if ($where['type'] === 'new') {
                $result = $result->where(function ($query) {
                    $query->orWhere('is_new', 1);
                });
            } elseif ($where['type'] === 'best') {
                $result = $result->where(function ($query) {
                    $query->orWhere('is_best', 1);
                });
            } elseif ($where['type'] === 'hot') {
                $result = $result->where(function ($query) {
                    $query->orWhere('is_hot', 1);
                });
            }
        }

        $where['area_pricetype'] = config('shop.area_pricetype');

        $uid = $where['uid'] ?? 0;
        $rank = $this->userCommonService->getUserRankByUid($uid);

        if ($rank) {
            $user_rank = $rank['rank_id'];
            $discount = $rank['discount'] / 100;
        } else {
            $user_rank = session('user_rank', 0);
            $discount = session('discount', 1);
        }

        $result = $result->with([
            'getBrand',
            'getMemberPrice' => function ($query) use ($user_rank) {
                $query->where('user_rank', $user_rank);
            },
            'getWarehouseGoods' => function ($query) use ($where) {
                $query->where('region_id', $where['warehouse_id']);
            },
            'getWarehouseAreaGoods' => function ($query) use ($where) {
                $query = $query->where('region_id', $where['area_id']);

                if ($where['area_pricetype'] == 1) {
                    $query->where('city_id', $where['area_city']);
                }
            }
        ]);

        $result = $result->orderByRaw('sort_order, last_update desc');

        $result = $result->take(20);

        $result = BaseRepository::getToArrayGet($result);

        $goods = [];
        if ($result) {

            $ru_id = BaseRepository::getKeyPluck($result, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($result as $idx => $row) {
                $price = [
                    'model_price' => isset($row['model_price']) ? $row['model_price'] : 0,
                    'user_price' => isset($row['get_member_price']['user_price']) ? $row['get_member_price']['user_price'] : 0,
                    'percentage' => isset($row['get_member_price']['percentage']) ? $row['get_member_price']['percentage'] : 0,
                    'warehouse_price' => isset($row['get_warehouse_goods']['warehouse_price']) ? $row['get_warehouse_goods']['warehouse_price'] : 0,
                    'region_price' => isset($row['get_warehouse_area_goods']['region_price']) ? $row['get_warehouse_area_goods']['region_price'] : 0,
                    'shop_price' => isset($row['shop_price']) ? $row['shop_price'] : 0,
                    'warehouse_promote_price' => isset($row['get_warehouse_goods']['warehouse_promote_price']) ? $row['get_warehouse_goods']['warehouse_promote_price'] : 0,
                    'region_promote_price' => isset($row['get_warehouse_area_goods']['region_promote_price']) ? $row['get_warehouse_area_goods']['region_promote_price'] : 0,
                    'promote_price' => isset($row['promote_price']) ? $row['promote_price'] : 0,
                    'wg_number' => isset($row['get_warehouse_goods']['region_number']) ? $row['get_warehouse_goods']['region_number'] : 0,
                    'wag_number' => isset($row['get_warehouse_area_goods']['region_number']) ? $row['get_warehouse_area_goods']['region_number'] : 0,
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

                if (isset($where['seller_id'])) {
                    $goods[$idx]['is_best'] = $row['store_best'];
                    $goods[$idx]['is_new'] = $row['store_new'];
                    $goods[$idx]['is_hot'] = $row['store_hot'];
                }

                $goods[$idx]['id'] = $row['goods_id'];
                $goods[$idx]['name'] = $row['goods_name'];
                $goods[$idx]['is_promote'] = $row['is_promote'];
                $goods[$idx]['brief'] = $row['goods_brief'];
                $goods[$idx]['comments_number'] = $row['comments_number'];
                $goods[$idx]['sales_volume'] = $row['sales_volume'];
                $goods[$idx]['brand_name'] = $row['get_brand']['brand_name'] ?? '';
                $goods[$idx]['goods_style_name'] = $this->goodsCommonService->addStyle($row['goods_name'], $row['goods_name_style']);
                $goods[$idx]['short_name'] = config('shop.goods_name_length') > 0 ? $this->dscRepository->subStr($row['goods_name'], config('shop.goods_name_length')) : $row['goods_name'];
                $goods[$idx]['short_style_name'] = $this->goodsCommonService->addStyle($goods[$idx]['short_name'], $row['goods_name_style']);
                $goods[$idx]['market_price'] = $this->dscRepository->getPriceFormat($row['market_price']);
                $goods[$idx]['shop_price'] = $this->dscRepository->getPriceFormat($row['shop_price']);
                $goods[$idx]['promote_price'] = $promote_price > 0 ? $this->dscRepository->getPriceFormat($promote_price) : '';
                $goods[$idx]['thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                $goods[$idx]['goods_img'] = $this->dscRepository->getImagePath($row['goods_img']);
                $goods[$idx]['shop_name'] = $merchantList[$row['user_id']]['shop_name'] ?? '';

                if ($presaleActivityList) {
                    $sql = [
                        'where' => [
                            [
                                'name' => 'goods_id',
                                'value' => $row['goods_id']
                            ]
                        ]
                    ];
                    $presale = BaseRepository::getArraySqlFirst($presaleActivityList, $sql);
                } else {
                    $presale = [];
                }

                if ($presale) {
                    $goods[$idx]['url'] = $this->dscRepository->buildUri('presale', ['act' => 'view', 'presaleid' => $presale['act_id']]);
                } else {
                    $goods[$idx]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
                }

                $goods[$idx]['shopUrl'] = $this->dscRepository->buildUri('merchants_store', ['urid' => $row['user_id']]);
            }
        }

        return $goods;
    }

    /**
     * 获得促销商品
     *
     * @param array $where
     * @return array
     * @throws \Exception
     */
    public function getPromoteGoods($where = [])
    {
        $time = TimeRepository::getGmTime();

        $where['area_pricetype'] = config('shop.area_pricetype');
        $where['warehouse_id'] = $where['warehouse_id'] ?? 0;
        $where['area_id'] = $where['area_id'] ?? 0;
        $where['area_city'] = $where['area_city'] ?? 0;

        $num = 10;
        $res = Goods::where('is_on_sale', 1)
            ->where('is_alone_sale', 1)
            ->where('is_delete', 0)
            ->where('is_promote', 1);

        $res = $res->where('promote_start_date', '<=', $time)
            ->where('promote_end_date', '>=', $time);

        $res = $this->dscRepository->getAreaLinkGoods($res, $where['area_id'], $where['area_city']);

        if (config('shop.review_goods') == 1) {
            $res = $res->whereIn('review_status', [3, 4, 5]);
        }

        if ($where['uid'] > 0) {
            $rank = $this->userCommonService->getUserRankByUid($where['uid']);
            $user_rank = $rank['rank_id'];
            $user_discount = $rank['discount'];
        } else {
            $user_rank = 1;
            $user_discount = 100;
        }

        $res = $res->with([
            'getMemberPrice' => function ($query) use ($user_rank) {
                $query->where('user_rank', $user_rank);
            },
            'getWarehouseGoods' => function ($query) use ($where) {
                $query->where('region_id', $where['warehouse_id']);
            },
            'getWarehouseAreaGoods' => function ($query) use ($where) {
                $query = $query->where('region_id', $where['area_id']);

                if ($where['area_pricetype'] == 1) {
                    $query->where('city_id', $where['area_city']);
                }
            },
            'getBrand'
        ]);

        if (config('shop.recommend_order') == 0) {
            $res = $res->orderByRaw('sort_order, last_update desc');
        } else {
            $res = $res->orderByRaw('RAND()');
        }

        $res = $res->take($num);

        $res = $res->get();

        $res = $res ? $res->toArray() : [];


        $goods = [];
        if ($res) {
            foreach ($res as $idx => $row) {
                $price = [
                    'model_price' => isset($row['model_price']) ? $row['model_price'] : 0,
                    'user_price' => isset($row['get_member_price']['user_price']) ? $row['get_member_price']['user_price'] : 0,
                    'percentage' => isset($row['get_member_price']['percentage']) ? $row['get_member_price']['percentage'] : 0,
                    'warehouse_price' => isset($row['get_warehouse_goods']['warehouse_price']) ? $row['get_warehouse_goods']['warehouse_price'] : 0,
                    'region_price' => isset($row['get_warehouse_area_goods']['region_price']) ? $row['get_warehouse_area_goods']['region_price'] : 0,
                    'shop_price' => isset($row['shop_price']) ? $row['shop_price'] : 0,
                    'warehouse_promote_price' => isset($row['get_warehouse_goods']['warehouse_promote_price']) ? $row['get_warehouse_goods']['warehouse_promote_price'] : 0,
                    'region_promote_price' => isset($row['get_warehouse_area_goods']['region_promote_price']) ? $row['get_warehouse_area_goods']['region_promote_price'] : 0,
                    'promote_price' => isset($row['promote_price']) ? $row['promote_price'] : 0,
                    'wg_number' => isset($row['get_warehouse_goods']['region_number']) ? $row['get_warehouse_goods']['region_number'] : 0,
                    'wag_number' => isset($row['get_warehouse_area_goods']['region_number']) ? $row['get_warehouse_area_goods']['region_number'] : 0,
                    'goods_number' => isset($row['goods_number']) ? $row['goods_number'] : 0
                ];

                $price = $this->goodsCommonService->getGoodsPrice($price, $user_discount / 100, $row);

                $row['shop_price'] = $price['shop_price'];
                $row['promote_price'] = $price['promote_price'];
                $row['goods_number'] = $price['goods_number'];

                $goods[$idx] = $row;

                if ($row['promote_price'] > 0) {
                    $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                } else {
                    $promote_price = 0;
                }

                $goods[$idx]['promote_price'] = ($promote_price > 0) ? $this->dscRepository->getPriceFormat($promote_price) : '';
                $goods[$idx]['market_price'] = $this->dscRepository->getPriceFormat($row['market_price']);
                $goods[$idx]['shop_price'] = $this->dscRepository->getPriceFormat($row['shop_price']);

                $goods[$idx]['s_time'] = $row['promote_start_date'];
                $goods[$idx]['e_time'] = $row['promote_end_date'];

                $goods[$idx]['id'] = $row['goods_id'];
                $goods[$idx]['name'] = $row['goods_name'];
                $goods[$idx]['brief'] = $row['goods_brief'];

                $goods[$idx]['brand_name'] = $row['get_brand']['brand_name'] ?? '';
                $goods[$idx]['comments_number'] = $row['comments_number'];
                $goods[$idx]['sales_volume'] = $row['sales_volume'];
            }
        }

        return $goods;
    }

    /**
     * 获得商品的详细信息
     *
     * @param array $where
     * @return array
     * @throws \Exception
     */
    public function getGoodsInfo($where = [])
    {
        $source_domestic = ConfigService::searchSourceDomestic();

        $where['uid'] = $where['uid'] ?? 0;

        $res = Goods::where('goods_id', $where['goods_id']);

        if (isset($where['is_delete'])) {
            $res = $res->where('is_delete', $where['is_delete']);
        }

        if (isset($where['is_on_sale'])) {
            $res = $res->where('is_on_sale', $where['is_on_sale']);
        }

        if (isset($where['is_alone_sale'])) {
            $res = $res->where('is_alone_sale', $where['is_alone_sale']);
        }

        if (isset($where['uid']) && $where['uid'] > 0) {
            $rank = $this->userCommonService->getUserRankByUid($where['uid']);
            $user_rank = isset($rank['rank_id']) ? $rank['rank_id'] : 1;
            $user_discount = isset($rank['discount']) ? $rank['discount'] : 100;
        } else {
            $user_rank = 1;
            $user_discount = 100;
        }

        $where['warehouse_id'] = $where['warehouse_id'] ?? 0;
        $where['area_id'] = $where['area_id'] ?? 0;
        $where['area_city'] = $where['area_city'] ?? 0;
        $where['area_pricetype'] = config('shop.area_pricetype');

        $res = $res->with([
            'getGoodsCategory',
            'getMemberPrice' => function ($query) use ($user_rank) {
                $query->where('user_rank', $user_rank);
            },
            'getWarehouseGoods' => function ($query) use ($where) {
                if (isset($where['warehouse_id'])) {
                    $query->where('region_id', $where['warehouse_id']);
                }
            },
            'getWarehouseAreaGoods' => function ($query) use ($where) {
                $query = $query->where('region_id', $where['area_id']);

                if ($where['area_pricetype'] == 1) {
                    $query->where('city_id', $where['area_city']);
                }
            },
            'getBrand',
            'getGoodsExtend'
        ]);

        $row = BaseRepository::getToArrayFirst($res);

        //当前时间
        $time = TimeRepository::getGmTime();

        $tag = [];
        if ($row) {

            $row['get_seller_shop_info'] = MerchantDataHandleService::getMerchantInfoDataList([$row['user_id']]);
            $row['get_seller_shop_info'] = BaseRepository::getArraySqlFirst($row['get_seller_shop_info']);

            $properties = $this->goodsAttrService->getGoodsProperties($row['goods_id'], $where['warehouse_id'], $where['area_id'], $where['area_city']);
            $row['spe'] = $properties['spe'] ?? [];
            $category = $row['get_goods_category'];
            $brand = $row['get_brand'];

            if ($brand) {
                $row['brand'] = $brand;
            }

            $row['cat_measure_unit'] = $category['measure_unit'];

            if ($row['brand_id']) {
                $row['brand_name'] = $brand['brand_name'];
            }

            if (!isset($where['spec'])) {
                // 获得商品的规格和属性
                $row['attr'] = $row['spe'];

                $attr_str = [];
                if ($row['attr']) {
                    $row['attr_name'] = '';
                    $row['goods_attr_id'] = '';
                    foreach ($row['attr'] as $k => $v) {
                        $select_key = 0;

                        if ($v['attr_type'] == 0) {
                            unset($row['attr'][$k]);
                            continue;
                        }

                        foreach ($v['values'] as $key => $val) {
                            if ($val['attr_checked'] == 1) {
                                $select_key = $key;
                                break;
                            }
                        }

                        //默认选择第一个属性为checked
                        if ($select_key == 0) {
                            $row['attr'][$k]['values'][0]['attr_checked'] = 1;
                        }
                        if ($row['attr_name']) {
                            $row['attr_name'] = $row['attr_name'] . '' . $v['values'][$select_key]['attr_value'];
                            $row['goods_attr_id'] = $row['goods_attr_id'] . ',' . $v['values'][$select_key]['goods_attr_id'];
                        } else {
                            $row['attr_name'] = $v['values'][$select_key]['attr_value'];
                            $row['goods_attr_id'] = $v['values'][$select_key]['goods_attr_id'];
                        }
                        $attr_str[] = $v['values'][$select_key]['goods_attr_id'];
                    }

                    $row['attr'] = array_values($row['attr']);

                    foreach ($row['attr'] as $key => $value) {
                        sort($value['values']);

                        $row['attr'][$key]['attr_key'] = $value['values'];

                        unset($row['attr'][$key]['values']);
                    }


                }

                if ($attr_str) {
                    sort($attr_str);
                }
            } else {
                $attr_str = $where['spec'];
            }

            $price = [
                'model_price' => isset($row['model_price']) ? $row['model_price'] : 0,
                'user_price' => isset($row['get_member_price']['user_price']) ? $row['get_member_price']['user_price'] : 0,
                'percentage' => isset($row['get_member_price']['percentage']) ? $row['get_member_price']['percentage'] : 0,
                'warehouse_price' => isset($row['get_warehouse_goods']['warehouse_price']) ? $row['get_warehouse_goods']['warehouse_price'] : 0,
                'region_price' => isset($row['get_warehouse_area_goods']['region_price']) ? $row['get_warehouse_area_goods']['region_price'] : 0,
                'shop_price' => isset($row['shop_price']) ? $row['shop_price'] : 0,
                'warehouse_promote_price' => isset($row['get_warehouse_goods']['warehouse_promote_price']) ? $row['get_warehouse_goods']['warehouse_promote_price'] : 0,
                'region_promote_price' => isset($row['get_warehouse_area_goods']['region_promote_price']) ? $row['get_warehouse_area_goods']['region_promote_price'] : 0,
                'promote_price' => isset($row['promote_price']) ? $row['promote_price'] : 0,
                'integral' => isset($row['integral']) ? $row['integral'] : 0,
                'wpay_integral' => isset($row['get_warehouse_goods']['pay_integral']) ? $row['get_warehouse_goods']['pay_integral'] : 0,
                'apay_integral' => isset($row['get_warehouse_area_goods']['pay_integral']) ? $row['get_warehouse_area_goods']['pay_integral'] : 0,
                'goods_number' => isset($row['goods_number']) ? $row['goods_number'] : 0,
                'wg_number' => isset($row['get_warehouse_goods']['region_number']) ? $row['get_warehouse_goods']['region_number'] : 0,
                'wag_number' => isset($row['get_warehouse_area_goods']['region_number']) ? $row['get_warehouse_area_goods']['region_number'] : 0,
            ];

            $goodsSelf = false;
            if ($row['user_id'] == 0) {
                $goodsSelf = true;
            }

            $row['shop_price_original'] = $this->dscRepository->getPriceFormat($price['shop_price'], true, false, $goodsSelf); // 商品原价不含折扣
            $price = $this->goodsCommonService->getGoodsPrice($price, $user_discount / 100, $row);

            $row['shop_price'] = StrRepository::priceFormat($price['shop_price']);
            $row['shop_price'] = $this->dscRepository->getPriceFormat($row['shop_price'], true, false, $goodsSelf);

            $row['promote_price'] = StrRepository::priceFormat($price['promote_price']);
            $row['integral'] = $price['integral'];
            $row['goods_number'] = $price['goods_number'];

            if ($row['promote_price'] > 0) {
                $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
            } else {
                $promote_price = 0;
            }

            $row['market_price'] = $this->dscRepository->getPriceFormat($price['market_price'], true, false, $goodsSelf);

            $is_promote = 0;
            //当前商品正在促销时间内
            if ($time >= $row['promote_start_date'] && $time <= $row['promote_end_date'] && $row['is_promote']) {
                $is_promote = 1;
            }

            // 当前商品正在最小起订量
            if ($time >= $row['minimum_start_date'] && $time <= $row['minimum_end_date'] && $row['is_minimum']) {
                $row['is_minimum'] = 1;
            } else {
                $row['is_minimum'] = 0;
            }

            if (!empty($attr_str)) {
                $rank = $this->userCommonService->getUserRankByUid($where['uid']);
                $rank['discount'] = !empty($rank['discount']) ? $rank['discount'] / 100 : 1;
                $row['shop_price'] = $this->goodsCommonService->getFinalPrice($row['goods_id'], 1, true, $attr_str, $where['warehouse_id'], $where['area_id'], $where['area_city'], 1, 0, 0, 0, 0, $rank);

                $attr_str = is_array($attr_str) ? implode(',', $attr_str) : $attr_str;
                $row['goods_number'] = $this->goodsWarehouseService->goodsAttrNumber($row['goods_id'], $row['model_attr'], $attr_str, $where['warehouse_id'], $where['area_id'], $where['area_city']);
            }

            $row['is_kj'] = 0;
            $row['goods_rate'] = 0;
            $row['cross_source'] = '';
            if (CROSS_BORDER === true) {
                // 跨境多商户
                $cbec = app(CrossBorderService::class)->cbecExists();

                if (!empty($cbec)) {
                    $source = $cbec->get_merchants_source($row['user_id']);
                    $row['goods_rate'] = $cbec->get_goods_rate($row['goods_id'], $row['shop_price']);
                    $row['formated_goods_rate'] = $this->dscRepository->getPriceFormat($row['goods_rate'], true, true, $goodsSelf);
                    $row['goods_rate'] = $this->dscRepository->getPriceFormat($row['goods_rate'], true, false, $goodsSelf);
                    if ($row['user_id'] > 0 && $source != $source_domestic) {
                        $row['cross_border'] = true;
                    }
                }

                $stepsFieldsList = MerchantDataHandleService::getMerchantsStepsFieldsDataList([$row['user_id']], ['user_id', 'source']);
                $source = $stepsFieldsList[$row['user_id']]['source'] ?? '';

                if ($source && !in_array($source, [$source_domestic])) {
                    $row['is_kj'] = 1;
                }

                $row['cross_source'] = $source;
            }

            //@author-bylu 将分期数据反序列化为数组 start
            if (!empty($row['stages'])) {
                $row['stages'] = unserialize($row['stages']);
            }
            //@author-bylu  end

            /* 计算商品的促销价格 */
            if ($is_promote == 1 && !empty($attr_str)) {
                $promote_price = $row['shop_price'];
            }

            if (!($time >= $row['promote_start_date'] && $time <= $row['promote_end_date'])) {
                $row['promote_start_date'] = 0;
                $row['promote_end_date'] = 0;
            }

            $row['now_promote'] = StrRepository::priceFormat($promote_price);
            $row['promote_price_org'] = StrRepository::priceFormat($promote_price);
            $row['goods_style_name'] = $this->goodsCommonService->addStyle($row['goods_name'], $row['goods_name_style']);

            /* 处理商品水印图片 */
            $watermark_img = '';
            if ($promote_price != 0) {
                $watermark_img = "watermark_promote";
            } elseif ($row['is_new'] != 0) {
                $watermark_img = "watermark_new";
            } elseif ($row['is_best'] != 0) {
                $watermark_img = "watermark_best";
            } elseif ($row['is_hot'] != 0) {
                $watermark_img = 'watermark_hot';
            }
            if ($watermark_img != '') {
                $row['watermark_img'] = $watermark_img;
            }

            /*获取优惠券数量*/
            $count = $this->couponService->goodsCoupons($where['uid'], $where['goods_id'], $row['user_id']);
            $row['coupon_count'] = $count['total'];

            /*商品相册*/
            $row['gallery_list'] = $this->goodsGalleryService->getGalleryList($where);
            if ($row['gallery_list']) {
                foreach ($row['gallery_list'] as $k => $v) {
                    $row['gallery_list'][$k]['img_original'] = $this->dscRepository->getImagePath($v['img_original']);
                    $row['gallery_list'][$k]['img_url'] = $this->dscRepository->getImagePath($v['img_url']);
                    $row['gallery_list'][$k]['thumb_url'] = $this->dscRepository->getImagePath($v['thumb_url']);
                }
            }

            $row['ru_id'] = $row['user_id'] ?? 0;

            /*获取商品规格参数*/
            $row['attr_parameter'] = [];
            if ($properties['pro']) {
                $properties['pro'] = array_values($properties['pro']);
                $properties['pro'] = BaseRepository::getArrayCollapse($properties['pro']);

                foreach ($properties['pro'] as $key => $val) {
                    $row['attr_parameter'][$key]['attr_name'] = $val['name'];
                    $row['attr_parameter'][$key]['attr_value'] = $val['value'];
                }
            }

            if ($where['uid'] > 0) {
                /*会员关注状态*/
                $collect_goods = CollectGoods::where('user_id', $where['uid'])
                    ->where('goods_id', $where['goods_id'])
                    ->count();
                if ($collect_goods > 0) {
                    $row['is_collect'] = 1;
                } else {
                    $row['is_collect'] = 0;
                }
            } else {
                $row['is_collect'] = 0;
            }

            /* 获取商家等级信息、等级积分 */
            $grade_rank = get_merchants_grade_rank($row['user_id']);

            /* 获取最高赠送消费积分 */
            $row['use_give_integral'] = 0;
            if ($row['user_id'] > 0 && $grade_rank) {
                if ($promote_price) { //促销
                    $row['use_give_integral'] = intval($grade_rank['give_integral'] * $promote_price);
                } else { //本店价
                    $row['use_give_integral'] = intval($grade_rank['give_integral'] * $row['shop_price']);
                }
            }

            /* 判断商家 */
            if ($row['user_id'] > 0) {
                if ($row['give_integral'] == -1) {
                    if (isset($row['use_give_integral']) && ($row['shop_price'] > $row['use_give_integral'] || $promote_price > $row['use_give_integral'])) {
                        $row['give_integral'] = intval($row['use_give_integral']);
                    } else {
                        $row['give_integral'] = 0;
                    }
                }
            } else {
                /* 判断赠送消费积分是否默认为-1 */
                if ($row['give_integral'] == -1) {
                    if ($promote_price) {
                        $row['give_integral'] = intval($promote_price);
                    } else {
                        $row['give_integral'] = intval($row['shop_price']);
                    }
                }
            }

            /* 获取商家等级信息 */
            if ($row['user_id'] > 0 && $grade_rank) {
                $row['grade_name'] = $grade_rank['grade_name'] ?? '';
                $row['grade_img'] = empty($grade_rank['grade_img']) ? '' : $this->dscRepository->getImagePath($grade_rank['grade_img']);
                $row['grade_introduce'] = $grade_rank['grade_introduce'] ?? '';
            }

            /* 是否显示商品库存数量 */
            $row['goods_number'] = (config('shop.use_storage') > 0) ? $row['goods_number'] : 1;

            /* 修正积分：转换为可使用多少积分（原来是可以使用多少钱的积分） */
            $row['integral'] = config('shop.integral_scale') ? round($row['integral'] * 100 / config('shop.integral_scale')) : 0;

            // 商品详情图 PC
            if (empty($row['desc_mobile']) && !empty($row['goods_desc'])) {
                $desc_preg = $this->dscRepository->descImagesPreg($row['goods_desc']);
                $row['goods_desc'] = $desc_preg['goods_desc'];
            }

            if (!empty($row['desc_mobile'])) {
                // 处理手机端商品详情 图片（手机相册图） data/gallery_album/
                $desc_preg = $this->dscRepository->descImagesPreg($row['desc_mobile'], 'desc_mobile', 1);
                $row['goods_desc'] = $desc_preg['desc_mobile'];
            }

            //查询关联商品描述 start
            if (empty($row['desc_mobile']) && empty($row['goods_desc'])) {
                $GoodsDesc = $this->getLinkGoodsDesc($row['goods_id'], $row['user_id']);
                $link_desc = $GoodsDesc ? $GoodsDesc['goods_desc'] : '';

                if (!empty($link_desc)) {
                    $row['goods_desc'] = $link_desc;
                }
            }
            //查询关联商品描述 end

            /* 修正商品图片 */
            $row['goods_img'] = $this->dscRepository->getImagePath($row['goods_img']);
            $row['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
            $row['original_img'] = $this->dscRepository->getImagePath($row['original_img']);
            $row['goods_video'] = empty($row['goods_video']) ? '' : $this->dscRepository->getImagePath($row['goods_video']);

            /* 获得商品的销售价格 */
            $attr_id = !empty($attr_str) ? explode(',', $attr_str) : [];
            $row['marketPrice'] = $this->goodsMarketPrice($row['goods_id'], $attr_id, $where['warehouse_id'], $where['area_id'], $where['area_city']);
            $row['marketPrice'] = $this->dscRepository->getPriceFormat($row['marketPrice'], true, false, $goodsSelf);
            $row['market_price_formated'] = $this->dscRepository->getPriceFormat($row['marketPrice'], true, true, $goodsSelf);
            $row['shop_price_formated'] = $this->dscRepository->getPriceFormat($row['shop_price'], true, true, $goodsSelf);
            $row['promote_price_formated'] = $is_promote == 1 ? $this->dscRepository->getPriceFormat($promote_price, true, true, $goodsSelf) : '';

            $row['goodsweight'] = $row['goods_weight'];

            $row['isHas_attr'] = empty($attr_str) ? 0 : 1;

            if (isset($row['shopinfo'])) {
                $row['shopinfo']['brand_thumb'] = $this->dscRepository->brandImagePath($row['shopinfo']['brand_thumb']);
                $row['shopinfo']['brand_thumb'] = str_replace(['../'], '', $row['shopinfo']['brand_thumb']);
                $row['shopinfo']['brand_thumb'] = $this->dscRepository->getImagePath($row['shopinfo']['brand_thumb']);
            }
            // 购物车商品数量
            if ($where['uid']) {
                $row['cart_number'] = Cart::where('user_id', $where['uid'])->where('rec_type', 0)
                    ->where('store_id', 0)
                    ->sum('goods_number');
            } else {
                $session_id = $this->sessionRepository->realCartMacIp();
                $row['cart_number'] = Cart::where('session_id', $session_id)->where('rec_type', 0)
                    ->where('store_id', 0)
                    ->sum('goods_number');
            }

            $row['goods_extend'] = $row['get_goods_extend'] ?? [];

            $row['volume_price_list'] = $this->goodsCommonService->getVolumePriceList($where['goods_id'], 1, 1);
            // 商品满减
            $row['consumption'] = $this->goodsConList($row['goods_id'], 'goods_consumption');

            /**
             * 商品详情页 显示开通购买权益卡 条件 drp_shop 0 显示 1 不显示
             * 1. 无分销模块 不显示 is_drp = 0
             * 2. 商家商品且禁用会员权益折扣 不显示 user_id > 0 && is_discount = 0
             * 3. 会员未登录或普通会员且不是分销商 显示立即开通 drp_shop_membership_card_id = 0
             * 4. 分销商权益已过期 显示重新购买 drp_shop_membership_card_id > 0
             * 5. 禁用会员特价权益 不显示
             */
            if (file_exists(MOBILE_DRP)) {
                $row['is_drp'] = 1; // 有分销模块
                // 商家商品且禁用会员权益折扣 不显示开通购买权益卡
                if (isset($row['user_id']) && $row['user_id'] > 0 && isset($row['is_discount']) && $row['is_discount'] == 0) {
                    $row['drp_shop'] = 1;
                } else {
                    $drp_shop = app(\App\Modules\Drp\Services\Drp\DrpShopService::class)->getDrpShop($where['uid']);

                    // 显示分销权益卡绑定的会员特价权益（最低折扣）; 非分销商显示开通购买权益卡，已过期分销商显示重新购买
                    if (empty($drp_shop) || ($drp_shop && $drp_shop['membership_status'] == 0)) {
                        $row['drp_shop'] = 0;
                        if ($drp_shop && $drp_shop['membership_status'] == 0) {
                            $row['drp_shop_membership_card_id'] = $drp_shop['membership_card_id'];
                        }

                        $row['membership_card_discount_price'] = app(DiscountRightsService::class)->membershipCardDiscount('discount', $row, 1, $attr_str, $where['warehouse_id'], $where['area_id'], $where['area_city']);
                        $row['membership_card_discount_price_formated'] = $this->dscRepository->getPriceFormat($row['membership_card_discount_price'], true, true, $goodsSelf);
                        // 禁用会员特价权益 不显示开通购买
                        if (empty($row['membership_card_discount_price']) || $row['membership_card_discount_price'] == 0) {
                            $row['drp_shop'] = 1;
                        }

                        $row['membership_card_discount_price'] = $this->dscRepository->getPriceFormat($row['membership_card_discount_price'], true, false, $goodsSelf);
                    } else {
                        $row['drp_shop'] = 1;
                    }
                }
            } else {
                $row['drp_shop'] = 1; // 不显示开通购买权益卡
                $row['is_drp'] = 0;// 没有分销模块
            }

            $row['suppliers_name'] = '';
            if ($row['suppliers_id'] > 0 && file_exists(SUPPLIERS)) {
                $row['suppliers_name'] = \App\Modules\Suppliers\Models\Suppliers::where('suppliers_id', $row['suppliers_id'])->value('suppliers_name');
                $row['suppliers_name'] = $row['suppliers_name'] ?? '';
            }

            //买家印象
            if ($row['goods_product_tag']) {
                $impression_list = !empty($row['goods_product_tag']) ? explode(',', $row['goods_product_tag']) : '';
                foreach ($impression_list as $kk => $vv) {
                    $tag[$kk]['txt'] = $vv;
                    //印象数量
                    $tag[$kk]['num'] = $this->goodsCommentService->commentGoodsTagNum($row['goods_id'], $vv);
                }
                $row['impression_list'] = $tag;
            }
            //上架下架时间

            //商品未审核，展示状态已下架
            if ($row['review_status'] <= 2) {
                $row['is_on_sale'] = 0;
            }
            // 会员等级价格
            $row['rank_prices'] = $this->goodsCommonService->getUserRankPrices($row, $row['shop_price_original'], $user_rank);

            //商品设置->显示设置
            $row['show_goodssn'] = config('shop.show_goodssn');        // 是否显示货号
            $row['show_brand'] = config('shop.show_brand');          // 是否显示品牌
            $row['show_goodsweight'] = config('shop.show_goodsweight');    // 是否显示重量
            $row['show_goodsnumber'] = config('shop.show_goodsnumber');    // 是否显示库存
            $row['show_addtime'] = config('shop.show_addtime');        // 是否显示上架时间
            $row['show_marketprice'] = config('shop.show_marketprice');    // 是否显示市场价格
            $row['show_rank_price'] = config('shop.show_rank_price');     // 是否显示等级价格
            $row['show_give_integral'] = config('shop.show_give_integral');  // 是否赠送消费积分

            //当前时间戳
            $row['current_time'] = $time;
            // 格式化上架时间
            $row['add_time_format'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['add_time']);

            // 活动标签
            $self_run = MerchantsShopInformation::where('user_id', $row['user_id'])->value('self_run');
            $self_run = $row['user_id'] == 0 || $self_run == 1 ? 1 : 0;
            $goods_label_all = $this->goodsCommonService->getGoodsLabelList($row['goods_id'], $self_run);

            $row['goods_label'] = $goods_label_all['goods_label'] ?? [];
            $row['goods_label_suspension'] = $goods_label_all['goods_label_suspension'] ?? [];

            // 服务标签
            $self_run = MerchantsShopInformation::query()->where('user_id', $row['user_id'])->value('self_run');
            $self_run = $row['user_id'] == 0 || $self_run == 1 ? 1 : 0;
            $goods_label_all = $this->goodsCommonService->getGoodsServicesLabelList($row['goods_id'], $self_run, $row['goods_cause']);

            $row['goods_services_label'] = $goods_label_all['goods_services_label'] ?? [];

            $row['rz_shop_name'] = $row['get_seller_shop_info']['shop_name'] ?? '';
            $row['rz_shopName'] = $row['rz_shop_name'];

            $row['country_name'] = $row['get_seller_shop_info']['country_name'] ?? '';
            $row['country_icon'] = $row['get_seller_shop_info']['country_icon'] ?? '';
            $row['cross_warehouse_name'] = $row['get_seller_shop_info']['cross_warehouse_name'] ?? '';

            $row['goods_qrcod_list'] = [];
            $row['media_qrcod_pic'] = '';

            if (file_exists(WXAPP_MEDIA) && config('shop.wxapp_shop_status') && file_exists(WXAPP_MEDIA_CONCISE)) {
                $goodsQrcod = \App\Modules\WxMedia\Models\WxappMediaGoodsQrcod::select('qrcod_pic')
                    ->where('goods_id', $row['goods_id'])
                    ->orderBy('sort', 'asc');
                $goodsQrcod = BaseRepository::getToArrayGet($goodsQrcod);

                if ($goodsQrcod) {
                    foreach ($goodsQrcod as $qk => $qv) {
                        $goodsQrcod[$qk]['qrcod_pic'] = $qv['qrcod_pic'] ? $this->dscRepository->getImagePath($qv['qrcod_pic']) : '';
                    }

                    $media_qrcod = BaseRepository::getArraySqlFirst($goodsQrcod);
                    $row['media_qrcod_pic'] = $media_qrcod['qrcod_pic'] ?? '';

                    unset($goodsQrcod[0]);
                }

                $row['goods_qrcod_list'] = array_values($goodsQrcod);
            }

            return $row;
        } else {
            return [];
        }
    }

    /**
     * 查询猜你喜欢商品
     *
     * @param int $uid
     * @param string $session_id
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param int $page
     * @param int $size
     * @return array
     * @throws \Exception
     */
    public function getUserOrderGoodsGuess($uid = 0, $session_id = '', $warehouse_id = 0, $area_id = 0, $area_city = 0, $page = 1, $size = 12)
    {
        $end_time = TimeRepository::getGmTime();
        $start_time = $end_time - 90 * 24 * 60 * 60;

        $goods = GoodsHistory::query()->where('add_time', '>=', $start_time)
            ->where('add_time', '<=', $end_time);

        if ($uid > 0) {
            $goods = $goods->where('user_id', $uid);
        } else {
            $goods = $goods->where('session_id', $session_id);
        }

        $goods_id = $goods->pluck('goods_id');

        $goods_id = BaseRepository::getToArray($goods_id);

        $query = [];

        if ($uid > 0) {
            $rank = $this->userCommonService->getUserRankByUid($uid);
            $user_rank = $rank['rank_id'];
            $user_discount = isset($rank['discount']) ? $rank['discount'] : 100;
        } else {
            $user_rank = 1;
            $user_discount = 100;
        }

        //商品详情页
        if (!empty($goods_id)) {

            $res = Goods::where('is_on_sale', 1)
                ->where('is_alone_sale', 1)
                ->where('is_delete', 0)
                ->where('is_show', 1)
                ->whereIn('goods_id', $goods_id);

            $res = BaseRepository::getToArrayGet($res);

            $link_cats = BaseRepository::getKeyPluck($res, 'cat_id');
            $link_goods = BaseRepository::getKeyPluck($res, 'goods_id');

            //历史商品、分类
            $query = $this->getGuessGoods($link_cats, $link_goods, $warehouse_id, $area_id, $area_city, 0, $page, $size, $user_rank);
        }

        //默认

        if (empty($query) && (count($query) < $size)) {
            //历史商品、分类
            $query = $this->getGuessGoods([], [], $warehouse_id, $area_id, $area_city, 0, $page, $size, $user_rank);
        }

        $guess_goods = [];

        if ($query) {
            $i = 0;

            $seller_id = BaseRepository::getKeyPluck($query, 'user_id');
            $sellerList = MerchantDataHandleService::MerchantsShopInformationDataList($seller_id);

            foreach ($query as $k => $row) {
                $price = [
                    'model_price' => isset($row['model_price']) ? $row['model_price'] : 0,
                    'user_price' => isset($row['get_member_price']['user_price']) ? $row['get_member_price']['user_price'] : 0,
                    'percentage' => isset($row['get_member_price']['percentage']) ? $row['get_member_price']['percentage'] : 0,
                    'warehouse_price' => isset($row['get_warehouse_goods']['warehouse_price']) ? $row['get_warehouse_goods']['warehouse_price'] : 0,
                    'region_price' => isset($row['get_warehouse_area_goods']['region_price']) ? $row['get_warehouse_area_goods']['region_price'] : 0,
                    'shop_price' => isset($row['shop_price']) ? $row['shop_price'] : 0,
                    'warehouse_promote_price' => isset($row['get_warehouse_goods']['warehouse_promote_price']) ? $row['get_warehouse_goods']['warehouse_promote_price'] : 0,
                    'region_promote_price' => isset($row['get_warehouse_area_goods']['region_promote_price']) ? $row['get_warehouse_area_goods']['region_promote_price'] : 0,
                    'promote_price' => isset($row['promote_price']) ? $row['promote_price'] : 0,
                    'wg_number' => isset($row['get_warehouse_goods']['region_number']) ? $row['get_warehouse_goods']['region_number'] : 0,
                    'wag_number' => isset($row['get_warehouse_area_goods']['region_number']) ? $row['get_warehouse_area_goods']['region_number'] : 0,
                    'goods_number' => isset($row['goods_number']) ? $row['goods_number'] : 0
                ];

                $price = $this->goodsCommonService->getGoodsPrice($price, $user_discount / 100, $row);

                $row['shop_price'] = StrRepository::priceFormat($price['shop_price']);
                $row['promote_price'] = StrRepository::priceFormat($price['promote_price']);
                $row['goods_number'] = $price['goods_number'];

                $guess_goods[$i] = $row;

                if ($row['promote_price'] > 0) {
                    $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                } else {
                    $promote_price = 0;
                }

                $guess_goods[$i]['goods_id'] = $row['goods_id'];
                $guess_goods[$i]['goods_name'] = $row['goods_name'];
                $guess_goods[$i]['sales_volume'] = $row['sales_volume'];
                $guess_goods[$i]['short_name'] = config('shop.goods_name_length') > 0 ? $this->dscRepository->subStr($row['goods_name'], config('shop.goods_name_length')) : $row['goods_name'];

                $goodsSelf = false;
                if ($row['user_id'] == 0) {
                    $goodsSelf = true;
                }

                $guess_goods[$i]['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                $guess_goods[$i]['shop_price'] = $this->dscRepository->getPriceFormat($guess_goods[$i]['shop_price'], true, false, $goodsSelf);
                $guess_goods[$i]['shop_price_formated'] = $this->dscRepository->getPriceFormat($row['shop_price'], true, true, $goodsSelf);
                $guess_goods[$i]['promote_price_formated'] = ($promote_price > 0) ? $this->dscRepository->getPriceFormat($promote_price, true, true, $goodsSelf) : '';
                $guess_goods[$i]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);

                $seller = $sellerList[$row['user_id']] ?? [];
                $guess_goods[$i]['shop_name'] = $seller['shop_name'] ?? '';
                $guess_goods[$i]['country_icon'] = $seller['country_icon'] ?? '';

                $guess_goods[$i]['shopUrl'] = $this->dscRepository->buildUri('merchants_store', ['urid' => $row['user_id']]);
                $i++;
            }
        }

        return $guess_goods;
    }

    /**
     * 获取商品统一描述内容
     *
     * @access  public
     * @param  $goods_id
     * @param  $seller_id
     * @return  array
     */
    public function getLinkGoodsDesc($goods_id = 0, $seller_id = 0)
    {
        $res = LinkDescGoodsid::where('goods_id', $goods_id);

        $res = $res->whereHasIn('getLinkGoodsDesc', function ($query) use ($seller_id) {
            $query->where('ru_id', $seller_id)->whereIn('review_status', [3, 4, 5]);
        });

        $res = $res->with(['getLinkGoodsDesc']);

        $res = $res->first();

        $res = $res ? $res->toArray() : [];

        if ($res) {
            $res['goods_desc'] = $res['get_link_goods_desc']['goods_desc'];
        }

        return $res;
    }

    /**
     * 商品市场价格（多模式下）
     *
     * @param $goods_id
     * @param $attr_id
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @return int|mixed
     */
    public function goodsMarketPrice($goods_id, $attr_id, $warehouse_id = 0, $area_id = 0, $area_city = 0)
    {
        $goods = Goods::select('model_attr', 'market_price')->where('goods_id', $goods_id);
        $goods = BaseRepository::getToArrayFirst($goods);

        $products = app(GoodsProdutsService::class)->getProductsAttrPrice($goods_id, $attr_id, $warehouse_id, $area_id, $area_city, $goods['model_attr']); //获取有属性价格

        if (empty($products) || $products['product_price'] <= 0) {
            $market_price = !empty($goods['market_price']) ? $goods['market_price'] : 0;
        } else {
            $attr_price = $products['product_price'];

            // SKU价格模式： 商品价格 + 属性货品价格 时， 市场价 = 原市场价 + 属性货品价格
            if (config('shop.add_shop_price') == 1) {
                $market_price = $attr_price + $goods['market_price'];
            } else {
                // SKU价格模式： 属性货品价格 时， 市场价 = 属性市场价格
                $market_price = !empty($products['product_market_price']) ? $products['product_market_price'] : 0;
            }
        }

        return !empty($market_price) ? $market_price : 0;
    }

    /**
     * 验证属性是多选，单选
     * @param $goods_attr_id
     * @return mixed
     */
    public function getGoodsAttrId($goods_attr_id)
    {
        $res = GoodsAttr::from('goods_attr as ga')
            ->select('a.attr_type')
            ->join('attribute as a', 'ga.attr_id', '=', 'a.attr_id')
            ->where('ga.goods_attr_id', $goods_attr_id)
            ->first();
        if ($res === null) {
            return [];
        }

        return $res['attr_type'];
    }

    /**
     * 商品属性图片
     * @param $goods_id
     * @return mixed
     */
    public function getAttrImgFlie($goods_id, $attr_id = 0)
    {
        $attr_id = BaseRepository::getExplode($attr_id);

        $res = [];
        if ($attr_id) {
            foreach ($attr_id as $key => $val) {
                $res = GoodsAttr::select('attr_img_flie', 'attr_gallery_flie')
                    ->where('goods_id', $goods_id)
                    ->where('goods_attr_id', $val);
                $res = BaseRepository::getToArrayFirst($res);
                if ($res) {
                    if (isset($res['attr_gallery_flie']) && !empty($res['attr_gallery_flie'])) {
                        $res['attr_img_flie'] = $res['attr_gallery_flie'];
                        break;
                    }
                    if (isset($res['attr_img_flie']) && !empty($res['attr_img_flie'])) {
                        break;
                    }
                }
            }
        }

        return $res;
    }


    /**
     * 商品属性价格与库存
     *
     * @param $uid
     * @param $goods_id
     * @param $attr_id
     * @param int $num
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param int $store_id
     * @param array $region
     * @return array
     * @throws \Exception
     */
    public function goodsPropertiesPrice($uid, $goods_id, $attr_id, $num = 1, $warehouse_id = 0, $area_id = 0, $area_city = 0, $store_id = 0, $region = [])
    {
        $attr_id = BaseRepository::getExplode($attr_id);

        $result = [
            'stock' => '',       //库存
            'market_price' => '',      //市场价
            'qty' => '',               //数量
            'spec_price' => '',        //属性价格
            'goods_price' => '',           //商品价格(最终使用价格)
            'attr_img' => ''           //商品属性图片
        ];

        if ($attr_id) {
            sort($attr_id);
        }

        $goods = Goods::select('user_id', 'model_attr')->where('goods_id', $goods_id);
        $goods = BaseRepository::getToArrayFirst($goods);

        $ru_id = $goods['user_id'] ?? 0;
        $model_attr = $goods['model_attr'] ?? 0;

        $goodsSelf = false;
        if ($ru_id == 0) {
            $goodsSelf = true;
        }

        $result['stock'] = $this->goodsWarehouseService->goodsAttrNumber($goods_id, $model_attr, $attr_id, $warehouse_id, $area_id, $area_city, $store_id);

        $result['market_price'] = $this->goodsMarketPrice($goods_id, $attr_id, $warehouse_id, $area_id, $area_city);
        $result['market_price_formated'] = $this->dscRepository->getPriceFormat($result['market_price'], true, true, $goodsSelf);
        $result['qty'] = $num;

        $result['spec_price'] = app(GoodsProdutsService::class)->goodsPropertyPrice($goods_id, $attr_id, $warehouse_id, $area_id, $area_city);
        $result['spec_price_formated'] = $this->dscRepository->getPriceFormat($result['spec_price'], true, true, $goodsSelf);

        $result['spec_promote_price'] = 0;
        if (config('shop.add_shop_price') == 0) {
            $result['spec_promote_price'] = app(GoodsProdutsService::class)->goodsPropertyPrice($goods_id, $attr_id, $warehouse_id, $area_id, $area_city, 'product_promote_price');
        }

        $result['spec_promote_price_formated'] = $this->dscRepository->getPriceFormat($result['spec_promote_price'], true, true, $goodsSelf);

        $result['goods_price'] = $this->getFinalPrice($uid, $goods_id, $num, true, $attr_id, $warehouse_id, $area_id, $area_city);
        $result['goods_price'] = $this->dscRepository->getPriceFormat($result['goods_price'], true, false, $goodsSelf);

        $result['goods_price_formated'] = $this->dscRepository->getPriceFormat($result['goods_price'], true, true, $goodsSelf);

        $result['shop_price'] = $result['goods_price'];
        $result['shop_price_formated'] = $result['goods_price_formated'];

        // 商品属性运费
        $result['shipping_fee'] = goodsShippingFee($goods_id, $warehouse_id, $area_id, $area_city, $region, '', $attr_id);

        if ($attr_id) {
            // 商品属性名称与图片
            $attr_value = [];
            $attr_img = [];
            foreach ($attr_id as $key => $val) {
                $res = DB::table('goods_attr')->select('attr_value', 'attr_img_flie', 'attr_gallery_flie')
                    ->where('goods_id', $goods_id)
                    ->where('goods_attr_id', $val)
                    ->first();
                if (!empty($res->attr_value)) {
                    $attr_value[$key] = $res->attr_value;
                }
                $attr_img[$key] = !empty($res->attr_gallery_flie) ? $res->attr_gallery_flie : (!empty($res->attr_img_flie) ? $res->attr_img_flie : '');
                if (empty($attr_img[$key])) {
                    unset($attr_img[$key]);
                }
            }

            $result['attr_name'] = implode(' ', $attr_value);
            if (!empty($attr_img)) {
                $attr_img = collect($attr_img)->first();
                $result['attr_img'] = !empty($attr_img) ? $this->dscRepository->getImagePath($attr_img) : '';
            }
        }

        return $result;
    }


    /**
     * 商品属性名称
     * @param $goods_id
     * @param $attr_id
     * @return string
     */
    public function getAttrName($goods_id, $attr_id)
    {
        $attr_name = '';
        if ($attr_id) {
            $name = [];
            foreach ($attr_id as $k => $v) {
                $name[$k] = GoodsAttr::where('goods_id', $goods_id)
                    ->where('goods_attr_id', $v)
                    ->value('attr_value');
            }
            $attr_name = implode(' ', $name);
        }

        return $attr_name;
    }

    /**
     * 取得商品最终使用价格
     *
     * @param string $goods_id 商品编号
     * @param string $goods_num 购买数量
     * @param boolean $is_spec_price 是否加入规格价格
     * @param array $property 规格ID的数组或者逗号分隔的字符串
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @return  float|int|mixed 商品最终购买价格
     */

    /**
     * 取得商品最终使用价格
     *
     * @param int $uid 会员ID
     * @param int $goods_id 商品编号
     * @param string $goods_num 购买数量
     * @param bool $is_spec_price 是否加入规格价格
     * @param array $property 规格ID的数组或者逗号分隔的字符串
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @return float|int|mixed
     * @throws \Exception
     */
    public function getFinalPrice($uid = 0, $goods_id = 0, $goods_num = '1', $is_spec_price = false, $property = [], $warehouse_id = 0, $area_id = 0, $area_city = 0)
    {
        $final_price = 0; //商品最终购买价格
        $volume_price = 0; //商品优惠价格
        $promote_price = 0; //商品促销价格
        $user_price = 0; //商品会员价格
        $spec_price = 0;

        //如果需要加入规格价格
        if ($is_spec_price && !empty($property)) {
            $warehouse_area['warehouse_id'] = $warehouse_id;
            $warehouse_area['area_id'] = $area_id;
            $warehouse_area['area_city'] = $area_city;
            $spec_price = $this->goodsAttrService->specPrice($property, $goods_id, $warehouse_area);
        }

        //取得商品优惠价格列表
        $price_list = $this->goodsCommonService->getVolumePriceList($goods_id);
        if (!empty($price_list)) {
            foreach ($price_list as $value) {
                if ($goods_num >= $value['number']) {
                    $volume_price = $value['price'];
                }
            }
        }

        if ($uid > 0) {
            $rank = $this->userCommonService->getUserRankByUid($uid);
            $user_rank = $rank['rank_id'] ?? 1;
            $user_discount = isset($rank['discount']) ? $rank['discount'] : 100;
        } else {
            $user_rank = 1;
            $user_discount = 100;
        }

        /* 取得商品信息 */
        $goods = Goods::where('goods_id', $goods_id);

        $where = [
            'warehouse_id' => $warehouse_id,
            'area_id' => $area_id,
            'area_city' => $area_city,
            'area_pricetype' => config('shop.area_pricetype')
        ];

        $goods = $goods->with([
            'getMemberPrice' => function ($query) use ($user_rank) {
                $query->where('user_rank', $user_rank);
            },
            'getWarehouseGoods' => function ($query) use ($warehouse_id) {
                $query->where('region_id', $warehouse_id);
            },
            'getWarehouseAreaGoods' => function ($query) use ($where) {
                $query = $query->where('region_id', $where['area_id']);

                if ($where['area_pricetype'] == 1) {
                    $query->where('city_id', $where['area_city']);
                }
            },
        ]);

        $goods = BaseRepository::getToArrayFirst($goods);

        if ($goods) {

            $price = [
                'model_price' => isset($goods['model_price']) ? $goods['model_price'] : 0,
                'user_price' => isset($goods['get_member_price']['user_price']) ? $goods['get_member_price']['user_price'] : 0,
                'percentage' => isset($goods['get_member_price']['percentage']) ? $goods['get_member_price']['percentage'] : 0,
                'warehouse_price' => isset($goods['get_warehouse_goods']['warehouse_price']) ? $goods['get_warehouse_goods']['warehouse_price'] : 0,
                'region_price' => isset($goods['get_warehouse_area_goods']['region_price']) ? $goods['get_warehouse_area_goods']['region_price'] : 0,
                'shop_price' => isset($goods['shop_price']) ? $goods['shop_price'] : 0,
                'warehouse_promote_price' => isset($goods['get_warehouse_goods']['warehouse_promote_price']) ? $goods['get_warehouse_goods']['warehouse_promote_price'] : 0,
                'region_promote_price' => isset($goods['get_warehouse_area_goods']['region_promote_price']) ? $goods['get_warehouse_area_goods']['region_promote_price'] : 0,
                'promote_price' => isset($goods['promote_price']) ? $goods['promote_price'] : 0,
                'wg_number' => isset($goods['get_warehouse_goods']['region_number']) ? $goods['get_warehouse_goods']['region_number'] : 0,
                'wag_number' => isset($goods['get_warehouse_area_goods']['region_number']) ? $goods['get_warehouse_area_goods']['region_number'] : 0,
                'goods_number' => isset($goods['goods_number']) ? $goods['goods_number'] : 0
            ];

            // 商品原价不含会员折扣
            $goods['shop_price_original'] = $goods['shop_price'] ?? 0;

            $price = $this->goodsCommonService->getGoodsPrice($price, $user_discount / 100, $goods);

            $goods['user_price'] = $price['user_price'];
            $goods['shop_price'] = $price['shop_price'];
            $goods['promote_price'] = $price['promote_price'];
            $goods['goods_number'] = $price['goods_number'];
        } else {
            $goods['user_price'] = 0;
            $goods['shop_price'] = 0;
        }

        $time = TimeRepository::getGmTime();
        $now_promote = 0;

        //当前商品正在促销时间内
        if ($time >= $goods['promote_start_date'] && $time <= $goods['promote_end_date'] && $goods['is_promote']) {
            $now_promote = 1;
        }

        /* 计算商品的属性促销价格 */
        if ($property && config('shop.add_shop_price') == 0) {
            $goods['promote_price'] = app(GoodsProdutsService::class)->goodsPropertyPrice($goods_id, $property, $warehouse_id, $area_id, $area_city, 'product_promote_price');
        }

        /* 计算商品的促销价格 */
        if (isset($goods['promote_price']) && $goods['promote_price'] > 0) {
            $promote_price = $this->goodsCommonService->getBargainPrice($goods['promote_price'], $goods['promote_start_date'], $goods['promote_end_date']);
        } else {
            $promote_price = 0;
        }

        //取得商品会员价格列表
        if (!empty($spec_price) && config('shop.add_shop_price') == 0) {

            /**
             * 会员等级价格与属性价关系
             * 1. 开启会员价格后 有会员等级价 优先取会员等级价; 若设置 百分比, 取属性价*会员等级百分比后价格
             * 2. 开启会员价格后 有会员等级价 取会员等级价与属性价 最小值
             * 3. 开启会员价格后 无会员等级价 取属性价*会员等级折扣
             * 4. 禁用会员价格后 取 属性价，有属性促销价格 则优先取 属性促销价
             */
            // 商家商品禁用会员权益折扣
            if (isset($goods['user_id']) && $goods['user_id'] > 0 && isset($goods['is_discount']) && $goods['is_discount'] == 0) {
                $user_discount = 100;

            } else {
                if (isset($price['user_price']) && $price['user_price'] > 0) {
                    // 会员价格
                    if (isset($price['percentage']) && $price['percentage'] == 1) {
                        $price_user_price = $spec_price * $price['user_price'] / 100; // 百分比
                    } else {
                        $price_user_price = $price['user_price']; // 固定价格
                    }

                    /* 取 会员等级价 与 属性价 取小值*/
                    $price_user_price = min($price_user_price, $spec_price);
                }
            }

            if (isset($price_user_price) && !empty($price_user_price)) {
                $user_price = $price_user_price;
            } else {
                // 无会员等级价 有属性促销价格 则优先取 属性促销价
                if ($now_promote == 1) {
                    $user_price = $promote_price;
                } else {
                    // 无会员等级价 取 属性价 * 会员等级折扣
                    $user_price = $spec_price * $user_discount / 100;
                }
            }

        } else {
            $user_price = $goods['shop_price'];
        }

        //比较商品的促销价格，会员价格，优惠价格
        if (empty($volume_price) && $now_promote == 0) {
            //如果优惠价格，促销价格都为空则取会员价格
            $final_price = $user_price;
        } elseif (!empty($volume_price) && $now_promote == 0) {
            //如果优惠价格为空时不参加这个比较。
            $final_price = min($volume_price, $user_price);
        } elseif (empty($volume_price) && $now_promote == 1) {
            //如果促销价格为空时不参加这个比较。
            $final_price = min($promote_price, $user_price);
        } elseif (!empty($volume_price) && $now_promote == 1) {
            //取促销价格，会员价格，优惠价格最小值
            $final_price = min($volume_price, $promote_price, $user_price);
        } else {
            $final_price = $user_price;
        }

        //如果需要加入规格价格
        if ($is_spec_price) {
            if (!empty($spec_price) && config('shop.add_shop_price') == 1) {
                $final_price += $spec_price;
            }
        }

        //返回商品最终购买价格
        return $final_price;
    }

    /**
     * 获取用户等级价格
     *
     * @param int $uid
     * @param int $goods_id
     * @return float|int|mixed
     * @throws \Exception
     */
    public function getMemberRankPriceByGid($uid = 0, $goods_id = 0)
    {
        $user_rank = $this->userCommonService->getUserRankByUid($uid);

        $shop_price = Goods::where('goods_id', $goods_id)->pluck('shop_price');
        $shop_price = $shop_price[0];

        if ($user_rank) {
            if ($price = $this->getMemberPriceByUid($user_rank['rank_id'], $goods_id)) {
                return $price;
            }
            if ($user_rank['discount']) {
                $member_price = $shop_price * $user_rank['discount'];
            } else {
                $member_price = $shop_price;
            }
            return $member_price;
        } else {
            return $shop_price;
        }
    }

    /**
     * 根据用户ID获取会员价格
     * @param $rank
     * @param $goods_id
     * @return mixed
     */
    public function getMemberPriceByUid($rank, $goods_id)
    {
        $price = MemberPrice::where('user_rank', $rank)->where('goods_id', $goods_id)->pluck('user_price');
        $price = $price ? $price->toArray() : [];

        if (!empty($price)) {
            $price = $price[0];
        }

        return $price;
    }

    /**
     * 获取促销活动
     *
     * @param int $goods_id
     * @param int $ru_id
     * @return array
     */
    public function goodsActivityList($goods_id = 0, $ru_id = 0)
    {
        //当前时间
        $gmtime = TimeRepository::getGmTime();

        $list = GoodsActivity::select('act_id', 'act_name', 'act_type', 'start_time', 'end_time')
            ->where('user_id', $ru_id)
            ->where('review_status', 3)
            ->where('is_finished', 0)
            ->where('start_time', '<=', $gmtime)
            ->where('end_time', '>=', $gmtime)
            ->where('goods_id', $goods_id);

        if (!empty($goods_id)) {
            $list = $list->where('goods_id', $goods_id);
        }
        $list = $list->limit(10);

        $list = BaseRepository::getToArrayGet($list);

        return $list;
    }

    /**
     * 生成商品海报 H5
     *
     * @param int $user_id
     * @param int $goods_id
     * @param string $extension_code
     * @param string $code_url
     * @param string $thumb
     * @param string $title
     * @param string $price
     * @param int $share_type 分享类型 0 分享， 1 分销
     * @return string|false
     */
    public function createSharePoster($user_id = 0, $goods_id = 0, $extension_code = '', $code_url = '', $thumb = '', $title = '', $price = '', $share_type = 0)
    {
        $sharePoster = new SharePoster($user_id, $goods_id, $extension_code, $share_type);

        // 设置背景图
        if ($extension_code == 'team') {
            $background_image = public_path('img/goods_bg_team.png');
        } elseif ($extension_code == 'bargain') {
            $background_image = public_path('img/goods_bg_bargain.png');
        } else {
            $background_image = public_path('img/goods_bg.png');
        }
        $sharePoster->setBackgroundImagePath($background_image);

        // 商品信息  微筹活动无商品
        $goods = [];
        if ($extension_code != 'crowdfunding') {
            $goods = DB::table('goods')->where('goods_id', $goods_id)->select('goods_name', 'goods_thumb', 'goods_img', 'shop_price')->first();
            $goods = $goods ? collect($goods)->toArray() : [];

            if (empty($thumb)) {
                // 设置缩略图: 优先获取顺序 商品相册图排序第一张、缩略图、商品图
                $gallery = DB::table('goods_gallery')->where('goods_id', $goods_id)->orderBy('img_desc')->orderBy('img_id')->select('img_url')->first();
                $gallery = $gallery ? collect($gallery)->toArray() : [];
                if (!empty($gallery['img_url'])) {
                    $thumb = $gallery['img_url'];
                } elseif (!empty($goods['goods_thumb'])) {
                    $thumb = $goods['goods_thumb'];
                } else {
                    $thumb = $goods['goods_img'] ?? '';
                }
            }
        }

        $thumb = !empty($thumb) ? $this->dscRepository->getImagePath($thumb) : '';
        $thumb_y = 110;
        $sharePoster->setThumbImage($thumb, $thumb_y);

        if ($extension_code == 'exchange') {
            $currency_format = trans('common.integral'); // 积分
        } else {
            // 货币符号
            $currency_format = strip_tags(config('shop.currency_format', '￥'));
            $currency_format = str_replace('%s', '', $currency_format);
        }
        $sharePoster->setCurrencyFormat($currency_format);

        // 添加价格
        if ($extension_code == 'exchange') {
            $price = !empty($price) ? $price : '';
            $price_x = 65;
        } else {
            $price = !empty($price) ? $price : $this->dscRepository->getPriceFormat($goods['shop_price'] ?? 0);
            $price_x = 25;
        }
        $sharePoster->setPrice($price, $price_x);

        // 添加标题
        $title = !empty($title) ? $title : $goods['goods_name'] ?? '';
        $sharePoster->setTitle($title, 65, 135);

        // 分享h5链接 拼接推荐参数
        if (stripos($code_url, 'parent_id') === false) {
            $code_url .= stripos($code_url, '?') === false ? '?' : '&';
            $code_url = $code_url . http_build_query(['parent_id' => $user_id], '', '&');
        }
        $code_url = html_out($code_url);
        $sharePoster->setCodeUrl($code_url);

        // 设置二维码logo图
        $two_code_logo = trim(config('shop.two_code_logo'));
        if (!empty($two_code_logo)) {
            $two_code_logo = str_replace('../', '', $two_code_logo);
            $logo_picture = $this->dscRepository->getImagePath($two_code_logo);
        } else {
            $logo_picture = !empty($thumb) ? $this->dscRepository->getImagePath($thumb) : '';
        }

        $sharePoster->setLogoImage($logo_picture);

        try {
            // 输出最终海报图
            $image_name = $sharePoster->createOutImage();

            if ($image_name) {
                // 同步镜像上传到OSS
                File::ossMirror($image_name, true);
            }

        } catch (\Exception $exception) {
            return false;
        }

        return $this->dscRepository->getImagePath($image_name) . '?v=' . StrRepository::random(16);
    }

    /**
     * 清空配件购物车
     * @param int $goods_id
     * @return string
     */
    public function clearCartCombo($user_id = 0, $goods_id = 0)
    {
        $user_id = isset($user_id) && !empty($user_id) ? intval($user_id) : 0;
        $cart = CartCombo::where('goods_id', $goods_id)
            ->where('parent_id', 0)
            ->orWhere('parent_id', $goods_id);

        if (!empty($user_id)) {
            $cart = $cart->where('user_id', $user_id);
        } else {
            $real_ip = $this->sessionRepository->realCartMacIp();
            $cart = $cart->where('session_id', $real_ip);
        }

        return $cart->delete();
    }


    /**
     * 配件商品价格
     *
     * @param int $goods_id
     * @param int $parent_id
     * @return int
     */
    public function groupGoodsInfo($goods_id = 0, $parent_id = 0)
    {
        $goods_price = GroupGoods::where('goods_id', $goods_id)->where('parent_id', $parent_id)->value('goods_price');
        $goods_price = $goods_price ? $goods_price : 0;
        return $goods_price;
    }

    /**
     * 更新配件购物车
     *
     * @param int $user_id
     * @param int $group_id
     * @param int $goods_id
     * @param array $cart_combo_data
     * @return mixed
     */
    public function updateCartCombo($user_id = 0, $group_id = 0, $goods_id = 0, $cart_combo_data = [])
    {
        $user_id = isset($user_id) && !empty($user_id) ? intval($user_id) : 0;
        $cart = CartCombo::where('goods_id', $goods_id)
            ->where('group_id', $group_id);

        if (!empty($user_id)) {
            $cart = $cart->where('user_id', $user_id);
        } else {
            $real_ip = $this->sessionRepository->realCartMacIp();
            $cart = $cart->where('session_id', $real_ip);
        }

        return $cart->update($cart_combo_data);
    }

    /**
     * 查看是否秒杀
     *
     * @param int $goods_id
     * @return int
     */
    public function getIsSeckill($goods_id = 0)
    {
        $date_begin = TimeRepository::getLocalStrtoTime(TimeRepository::getLocalDate('Ymd'));
        $stb_time = TimeRepository::getLocalDate('H:i:s');

        $res = SeckillGoods::select('tb_id', 'id as sec_goods_id')
            ->where('goods_id', $goods_id);

        $where = [
            'date_begin' => $date_begin,
            'stb_time' => $stb_time
        ];

        $res = $res->whereHasIn('getSeckillTimeBucket', function ($query) use ($where) {
            $query->where('begin_time', '<=', $where['stb_time'])
                ->where('end_time', '>', $where['stb_time']);
        });

        $res = $res->whereHasIn('getSeckill', function ($query) use ($where) {
            $query->where('begin_time', '<=', $where['date_begin'])
                ->where('acti_time', '>', $where['date_begin'])
                ->where('is_putaway', 1)
                ->where('review_status', 3);
        });

        $res = $res->with([
            'getSeckillTimeBucket' => function ($query) {
                $query->select('id', 'begin_time');
            }
        ]);

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $key => $val) {
                $res[$key]['begin_time'] = $val['get_seckill_time_bucket'] ? $val['get_seckill_time_bucket']['begin_time'] : 0;
                $res[$key]['begin_time'] = $res[$key]['begin_time'] ? TimeRepository::getLocalStrtoTime($res[$key]['begin_time']) : $res[$key]['begin_time'];
            }
        }

        $res = BaseRepository::getSortBy($res, 'begin_time');
        $res = BaseRepository::getTake($res, 1);

        $sec_goods_id = 0;
        if ($res) {
            $sec_goods_id = $res[0]['sec_goods_id'];
        }

        return $sec_goods_id;
    }

    /**
     * 所有的促销活动信息
     *
     * @param int $user_id
     * @param int $goods_id
     * @param int $ru_id
     * @param array $goods
     * @return array
     * @throws \Exception
     */
    public function getPromotionInfo($user_id = 0, $goods_id = 0, $ru_id = 0, $goods = [])
    {
        $snatch = [];
        $group = [];
        $auction = [];
        $package = [];
        $favourable = [];
        $team = []; // 拼团
        $bargain = []; // 砍价

        // 获取促销活动
        $list = $this->goodsActivityList($goods_id, $ru_id);
        foreach ($list as $data) {
            switch ($data['act_type']) {
                case GAT_SNATCH: //夺宝奇兵
                    $snatch[$data['act_id']]['act_id'] = $data['act_id'];
                    $snatch[$data['act_id']]['act_name'] = $data['act_name'];
                    $snatch[$data['act_id']]['url'] = url('snatch/index/detail', ['id' => $data['act_id']]);
                    $snatch[$data['act_id']]['time'] = sprintf(L('promotion_time'), TimeRepository::getLocalDate('Y-m-d', $data['start_time']), TimeRepository::getLocalDate('Y-m-d', $data['end_time']));
                    $snatch[$data['act_id']]['sort'] = $data['start_time'];
                    $snatch[$data['act_id']]['type'] = 'snatch';
                    break;

                case GAT_GROUP_BUY: //团购
                    $group[$data['act_id']]['act_id'] = $data['act_id'];
                    $group[$data['act_id']]['act_name'] = $data['act_name'];
                    $group[$data['act_id']]['url'] = route('api.groupbuy.detail', ['group_buy_id' => $data['act_id']]);
                    $group[$data['act_id']]['time'] = sprintf(L('promotion_time'), TimeRepository::getLocalDate('Y-m-d', $data['start_time']), TimeRepository::getLocalDate('Y-m-d', $data['end_time']));
                    $group[$data['act_id']]['sort'] = $data['start_time'];
                    $group[$data['act_id']]['type'] = 'group_buy';
                    break;

                case GAT_AUCTION: //拍卖
                    $auction[$data['act_id']]['act_id'] = $data['act_id'];
                    $auction[$data['act_id']]['act_name'] = $data['act_name'];
                    $auction[$data['act_id']]['url'] = route('api.auction.detail', ['id' => $data['act_id']]);
                    $auction[$data['act_id']]['time'] = sprintf(L('promotion_time'), TimeRepository::getLocalDate('Y-m-d', $data['start_time']), TimeRepository::getLocalDate('Y-m-d', $data['end_time']));
                    $auction[$data['act_id']]['sort'] = $data['start_time'];
                    $auction[$data['act_id']]['type'] = 'auction';
                    break;

                case GAT_PACKAGE: //礼包
                    $package[$data['act_id']]['act_id'] = $data['act_id'];
                    $package[$data['act_id']]['act_name'] = $data['act_name'];
                    $package[$data['act_id']]['url'] = route('api.package.list');
                    $package[$data['act_id']]['time'] = sprintf(L('promotion_time'), TimeRepository::getLocalDate('Y-m-d', $data['start_time']), TimeRepository::getLocalDate('Y-m-d', $data['end_time']));
                    $package[$data['act_id']]['sort'] = $data['start_time'];
                    $package[$data['act_id']]['type'] = 'package';
                    break;
            }
        }

        //查询符合条件的优惠活动
        $res = $this->discountService->activityListAll($user_id, $ru_id);
        if (empty($goods_id)) {
            foreach ($res as $rows) {
                if ($rows['userFav_type'] == 1) {
                    $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
                } else {
                    $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                }
                $favourable[$rows['act_id']]['url'] = $favourable[$rows['act_id']]['url'] = route('api.activity.show', ['act_id' => $rows['act_id']]);
                $favourable[$rows['act_id']]['time'] = sprintf(L('promotion_time'), TimeRepository::getLocalDate('Y-m-d', $rows['start_time']), TimeRepository::getLocalDate('Y-m-d', $rows['end_time']));
                $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                $favourable[$rows['act_id']]['type'] = 'favourable';
                $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
            }
        } else {
            // 商品信息

            $category_id = $goods['cat_id'];
            $brand_id = $goods['brand_id'];

            foreach ($res as $rows) {
                if ($rows['act_range'] == FAR_ALL) {
                    $favourable[$rows['act_id']]['act_id'] = $rows['act_id'];
                    if ($rows['userFav_type'] == 1) {
                        $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
                    } else {
                        $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                    }
                    $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                    $favourable[$rows['act_id']]['type'] = 'favourable';
                    $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
                    $favourable[$rows['act_id']]['url'] = route('api.activity.show', ['act_id' => $rows['act_id']]);
                } elseif ($rows['act_range'] == FAR_CATEGORY) {
                    /* 找出分类id的子分类id */
                    $id_list = [];
                    $raw_id_list = explode(',', $rows['act_range_ext']);

                    foreach ($raw_id_list as $id) {
                        /**
                         * 当前分类下的所有子分类
                         * 返回一维数组
                         */
                        $cat_list = $this->categoryService->getCatListChildren($id);
                        $id_list = array_merge($id_list, $cat_list);
                        array_unshift($id_list, $id);
                    }
                    $ids = join(',', array_unique($id_list));
                    if (strpos(',' . $ids . ',', ',' . $category_id . ',') !== false) {
                        $favourable[$rows['act_id']]['act_id'] = $rows['act_id'];
                        if ($rows['userFav_type'] == 1) {
                            $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
                        } else {
                            $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                        }
                        $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                        $favourable[$rows['act_id']]['type'] = 'favourable';
                        $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
                        $favourable[$rows['act_id']]['url'] = route('api.activity.show', ['act_id' => $rows['act_id']]);
                    }
                } elseif ($rows['act_range'] == FAR_BRAND) {
                    if (strpos(',' . $rows['act_range_ext'] . ',', ',' . $brand_id . ',') !== false) {
                        $favourable[$rows['act_id']]['act_id'] = $rows['act_id'];
                        if ($rows['userFav_type'] == 1) {
                            $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
                        } else {
                            $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                        }
                        $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                        $favourable[$rows['act_id']]['type'] = 'favourable';
                        $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
                        $favourable[$rows['act_id']]['url'] = route('api.activity.show', ['act_id' => $rows['act_id']]);
                    }
                } elseif ($rows['act_range'] == FAR_GOODS) {
                    if (strpos(',' . $rows['act_range_ext'] . ',', ',' . $goods_id . ',') !== false) {
                        $favourable[$rows['act_id']]['act_id'] = $rows['act_id'];
                        if ($rows['userFav_type'] == 1) {
                            $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
                        } else {
                            $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                        }
                        $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                        $favourable[$rows['act_id']]['type'] = 'favourable';
                        $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
                        $favourable[$rows['act_id']]['url'] = route('api.activity.show', ['act_id' => $rows['act_id']]);
                    }
                }
            }
        }

        $sort_time = [];

        if (file_exists(MOBILE_TEAM)) {
            $team = $this->getGoodsTeamActivity($goods_id);
        }

        if (file_exists(MOBILE_BARGAIN)) {
            $bargain = $this->getGoodsBargainActivity($goods_id);
        }

        $arr = array_merge($snatch, $group, $auction, $package, $favourable, $team, $bargain);
        foreach ($arr as $key => $value) {
            $sort_time[] = $value['sort'];
        }
        array_multisort($sort_time, SORT_NUMERIC, SORT_DESC, $arr);

        return $arr;
    }

    /**
     * 查询商品满减促销信息
     * @param int $goods_id
     * @param string $table
     * @param int $type
     * @return array
     */
    public function goodsConList($goods_id = 0, $table = '', $type = 0)
    {
        if ($table == 'goods_consumption') {
            $res = GoodsConsumption::where('goods_id', $goods_id);
        } else {
            $res = GoodsConshipping::where('goods_id', $goods_id);
        }
        $res = $res->get();
        $res = $res ? $res->toArray() : [];
        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$key]['id'] = $row['id'];
                if ($type == 0) {
                    $arr[$key]['cfull'] = $row['cfull'];
                    $arr[$key]['creduce'] = $row['creduce'];
                } elseif ($type == 1) {
                    $arr[$key]['sfull'] = $row['sfull'];
                    $arr[$key]['sreduce'] = $row['sreduce'];
                }
            }

            if ($type == 1) {
                $sort = 'sfull';
            } else {
                $sort = 'cfull';
            }
            $arr = collect($arr)->sortBy($sort)->values()->all();
        }
        return $arr;
    }

    /**
     * 获取商品的视频列表
     *
     * @param int $size
     * @param int $page
     * @param int $user_id
     * @param $where
     * @return mixed
     * @throws \Exception
     */
    public function getVideoList($size = 10, $page = 1, $user_id = 0, $where)
    {
        $sort = 'goods_id';
        $order = 'DESC';

        $res = Goods::select('goods_id', 'user_id', 'goods_video', 'goods_name', 'goods_thumb', 'goods_img', 'original_img', 'sales_volume', 'model_price', 'shop_price', 'promote_price', 'integral', 'goods_number', 'is_discount')
            ->where('goods_video', '<>', '')
            ->where('is_on_sale', 1)
            ->where('is_alone_sale', 1)
            ->where('is_delete', 0)
            ->whereIn('review_status', [3, 4, 5]);
        if ($user_id > 0) {
            $collectWhere = [
                'user_id' => $user_id
            ];
            $res = $res->withCount([
                'getCollectGoods as is_collect' => function ($query) use ($collectWhere) {
                    $query->where('user_id', $collectWhere['user_id']);
                }
            ]);
        }
        $res = $res->withCount([
            'getComment as comment_num',
            'getManyCollectGoods as user_collect' => function ($query) {
                $query->where('is_attention', 1);
            }
        ]);

        $where['warehouse_id'] = $where['warehouse_id'] ?? 0;
        $where['area_id'] = $where['area_id'] ?? 0;
        $where['area_city'] = $where['area_city'] ?? 0;
        $where['area_pricetype'] = config('shop.area_pricetype');
        if ($user_id > 0) {
            $rank = $this->userCommonService->getUserRankByUid($user_id);
            $user_rank = $rank['rank_id'];
            $user_discount = isset($rank['discount']) ? $rank['discount'] : 100;
        } else {
            $user_rank = 1;
            $user_discount = 100;
        }
        $res = $res->with([
            'getMemberPrice' => function ($query) use ($user_rank) {
                $query->where('user_rank', $user_rank);
            },
            'getWarehouseGoods' => function ($query) use ($where) {
                if (isset($where['warehouse_id'])) {
                    $query->where('region_id', $where['warehouse_id']);
                }
            },
            'getWarehouseAreaGoods' => function ($query) use ($where) {
                $query = $query->where('region_id', $where['area_id']);

                if ($where['area_pricetype'] == 1) {
                    $query->where('city_id', $where['area_city']);
                }
            },
            'getGoodsExtend',
            'getSellerShopInfo' => function ($query) {
                $query->select('shop_name', 'ru_id', 'shop_logo', 'logo_thumb');
            },
            'getGoodsVideo' => function ($query) {
                $query->select('goods_id', 'look_num');
            }
        ]);

        $start = ($page - 1) * $size;

        $res = $res->orderBy($sort, $order);

        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($size > 0) {
            $res = $res->take($size);
        }
        $res = BaseRepository::getToArrayGet($res);

        foreach ($res as $key => $val) {
            $price = [
                'model_price' => isset($val['model_price']) ? $val['model_price'] : 0,
                'user_price' => isset($val['get_member_price']['user_price']) ? $val['get_member_price']['user_price'] : 0,
                'percentage' => isset($val['get_member_price']['percentage']) ? $val['get_member_price']['percentage'] : 0,
                'warehouse_price' => isset($val['get_warehouse_goods']['warehouse_price']) ? $val['get_warehouse_goods']['warehouse_price'] : 0,
                'region_price' => isset($val['get_warehouse_area_goods']['region_price']) ? $val['get_warehouse_area_goods']['region_price'] : 0,
                'shop_price' => isset($val['shop_price']) ? $val['shop_price'] : 0,
                'warehouse_promote_price' => isset($val['get_warehouse_goods']['warehouse_promote_price']) ? $val['get_warehouse_goods']['warehouse_promote_price'] : 0,
                'region_promote_price' => isset($val['get_warehouse_area_goods']['region_promote_price']) ? $val['get_warehouse_area_goods']['region_promote_price'] : 0,
                'promote_price' => isset($val['promote_price']) ? $val['promote_price'] : 0,
                'integral' => isset($val['integral']) ? $val['integral'] : 0,
                'wpay_integral' => isset($val['get_warehouse_goods']['pay_integral']) ? $val['get_warehouse_goods']['pay_integral'] : 0,
                'apay_integral' => isset($val['get_warehouse_area_goods']['pay_integral']) ? $val['get_warehouse_area_goods']['pay_integral'] : 0,
                'goods_number' => isset($val['goods_number']) ? $val['goods_number'] : 0,
                'wg_number' => isset($val['get_warehouse_goods']['region_number']) ? $val['get_warehouse_goods']['region_number'] : 0,
                'wag_number' => isset($val['get_warehouse_area_goods']['region_number']) ? $val['get_warehouse_area_goods']['region_number'] : 0,
            ];
            $price = $this->goodsCommonService->getGoodsPrice($price, $user_discount / 100, $val);

            $res[$key]['shop_price_formated'] = $this->dscRepository->getPriceFormat($price['shop_price']);
            if ($user_id == 0) {
                $res[$key]['is_collect'] = 0;
            }
            // 观看人数
            $res[$key]['look_num'] = 0;
            if (isset($val['get_goods_video']) && !empty($val['get_goods_video'])) {
                $res[$key]['look_num'] = $val['get_goods_video']['look_num'] ?? 0;
            }

            $res[$key]['goods_img'] = empty($val['goods_img']) ? '' : $this->dscRepository->getImagePath($val['goods_img']);
            $res[$key]['original_img'] = empty($val['original_img']) ? '' : $this->dscRepository->getImagePath($val['original_img']);
            $res[$key]['goods_thumb'] = empty($val['goods_thumb']) ? '' : $this->dscRepository->getImagePath($val['goods_thumb']);
            $res[$key]['goods_video'] = empty($val['goods_video']) ? '' : $this->dscRepository->getImagePath($val['goods_video']);
            $res[$key]['shop_name'] = $val['get_seller_shop_info']['shop_name'];
            $res[$key]['shop_logo'] = $this->dscRepository->getImagePath($val['get_seller_shop_info']['shop_logo']);
            $res[$key]['logo_thumb'] = $this->dscRepository->getImagePath(str_replace('../', '', $val['get_seller_shop_info']['logo_thumb']));
        }

        return $res;
    }

    /**
     * 获取商品的视频详情
     *
     * @param int $user_id
     * @param $where
     * @return mixed
     * @throws \Exception
     */
    public function getVideoInfo($goods_id = 0, $user_id = 0, $where)
    {
        $row = [];

        if ($goods_id > 0) {
            $row = Goods::select('goods_id', 'user_id', 'goods_video', 'goods_name', 'goods_thumb', 'goods_img', 'original_img', 'sales_volume', 'model_price', 'shop_price', 'promote_price', 'integral', 'goods_number', 'is_discount')
                ->where('goods_video', '<>', '')
                ->where('is_on_sale', 1)
                ->where('is_alone_sale', 1)
                ->where('is_delete', 0)
                ->whereIn('review_status', [3, 4, 5])
                ->where('goods_id', $goods_id);

            if ($user_id > 0) {
                $collectWhere = [
                    'user_id' => $user_id
                ];
                $row = $row->withCount([
                    'getCollectGoods as is_collect' => function ($query) use ($collectWhere) {
                        $query->where('user_id', $collectWhere['user_id']);
                    }
                ]);
            }
            $row = $row->withCount([
                'getComment as comment_num',
                'getManyCollectGoods as user_collect' => function ($query) {
                    $query->where('is_attention', 1);
                }
            ]);

            $where['warehouse_id'] = $where['warehouse_id'] ?? 0;
            $where['area_id'] = $where['area_id'] ?? 0;
            $where['area_city'] = $where['area_city'] ?? 0;
            $where['area_pricetype'] = config('shop.area_pricetype');
            if ($user_id > 0) {
                $rank = $this->userCommonService->getUserRankByUid($user_id);
                $user_rank = $rank['rank_id'];
                $user_discount = isset($rank['discount']) ? $rank['discount'] : 100;
            } else {
                $user_rank = 1;
                $user_discount = 100;
            }
            $row = $row->with([
                'getMemberPrice' => function ($query) use ($user_rank) {
                    $query->where('user_rank', $user_rank);
                },
                'getWarehouseGoods' => function ($query) use ($where) {
                    if (isset($where['warehouse_id'])) {
                        $query->where('region_id', $where['warehouse_id']);
                    }
                },
                'getWarehouseAreaGoods' => function ($query) use ($where) {
                    $query = $query->where('region_id', $where['area_id']);

                    if ($where['area_pricetype'] == 1) {
                        $query->where('city_id', $where['area_city']);
                    }
                },
                'getGoodsExtend',
                'getSellerShopInfo' => function ($query) {
                    $query->select('shop_name', 'ru_id', 'shop_logo');
                },
            ]);

            $row = BaseRepository::getToArrayFirst($row);

            if ($row) {
                $price = [
                    'model_price' => isset($row['model_price']) ? $row['model_price'] : 0,
                    'user_price' => isset($row['get_member_price']['user_price']) ? $row['get_member_price']['user_price'] : 0,
                    'percentage' => isset($row['get_member_price']['percentage']) ? $row['get_member_price']['percentage'] : 0,
                    'warehouse_price' => isset($row['get_warehouse_goods']['warehouse_price']) ? $row['get_warehouse_goods']['warehouse_price'] : 0,
                    'region_price' => isset($row['get_warehouse_area_goods']['region_price']) ? $row['get_warehouse_area_goods']['region_price'] : 0,
                    'shop_price' => isset($row['shop_price']) ? $row['shop_price'] : 0,
                    'warehouse_promote_price' => isset($row['get_warehouse_goods']['warehouse_promote_price']) ? $row['get_warehouse_goods']['warehouse_promote_price'] : 0,
                    'region_promote_price' => isset($row['get_warehouse_area_goods']['region_promote_price']) ? $row['get_warehouse_area_goods']['region_promote_price'] : 0,
                    'promote_price' => isset($row['promote_price']) ? $row['promote_price'] : 0,
                    'integral' => isset($row['integral']) ? $row['integral'] : 0,
                    'wpay_integral' => isset($row['get_warehouse_goods']['pay_integral']) ? $row['get_warehouse_goods']['pay_integral'] : 0,
                    'apay_integral' => isset($row['get_warehouse_area_goods']['pay_integral']) ? $row['get_warehouse_area_goods']['pay_integral'] : 0,
                    'goods_number' => isset($row['goods_number']) ? $row['goods_number'] : 0,
                    'wg_number' => isset($row['get_warehouse_goods']['region_number']) ? $row['get_warehouse_goods']['region_number'] : 0,
                    'wag_number' => isset($row['get_warehouse_area_goods']['region_number']) ? $row['get_warehouse_area_goods']['region_number'] : 0,
                ];
                $price = $this->goodsCommonService->getGoodsPrice($price, $user_discount / 100, $row);

                $row['shop_price_formated'] = $this->dscRepository->getPriceFormat($price['shop_price']);
                if ($user_id == 0) {
                    $row['is_collect'] = 0;
                }

                $row['goods_img'] = empty($row['goods_img']) ? '' : $this->dscRepository->getImagePath($row['goods_img']);
                $row['original_img'] = empty($row['original_img']) ? '' : $this->dscRepository->getImagePath($row['original_img']);
                $row['goods_thumb'] = empty($row['goods_thumb']) ? '' : $this->dscRepository->getImagePath($row['goods_thumb']);
                $row['goods_video'] = empty($row['goods_video']) ? '' : $this->dscRepository->getImagePath($row['goods_video']);
                $row['shop_name'] = $row['get_seller_shop_info']['shop_name'] ?? '';
            }
        }

        return $row;
    }

    /**
     * 更新商品点击量
     * @param int $goods_id
     * @return bool
     */
    public function updateGoodsClick($goods_id = 0)
    {
        if (empty($goods_id)) {
            return false;
        }
        return Goods::where('goods_id', $goods_id)->increment('click_count', 1);
    }

    /**
     * 更新商品视频点击量
     * @param int $goods_id
     * @return bool
     */
    public function getVideoLookNum($goods_id = 0)
    {
        if (empty($goods_id)) {
            return false;
        }

        $count = GoodsVideo::where('goods_id', $goods_id)->count();
        if ($count > 0) {
            GoodsVideo::where('goods_id', $goods_id)->increment('look_num', 1);
        } else {
            $date = [
                'goods_id' => $goods_id,
                'look_num' => 1
            ];
            GoodsVideo::insert($date);
        }

        $look_num = GoodsVideo::where('goods_id', $goods_id)->value('look_num');

        return $look_num;
    }


    /**
     * 查询猜你喜欢商品
     *
     * @param array $link_cats
     * @param array $link_goods
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param int $is_volume
     * @param int $skip
     * @param int $take
     * @return mixed
     */
    private function getGuessGoods($link_cats = [], $link_goods = [], $warehouse_id = 0, $area_id = 0, $area_city = 0, $is_volume = 0, $page = 1, $size = 12, $user_rank = 0)
    {
        $res = Goods::where('is_on_sale', 1)
            ->where('is_alone_sale', 1)
            ->where('is_delete', 0)
            ->where('is_show', 1);

        if ($is_volume == 1) {
            $res = $res->where('sales_volume', '>', 0);
        } elseif ($is_volume == 2) {
            $res = $res->where('sales_volume', '>', 0)->where('is_hot', 1);
        }

        if ($link_cats) {
            $res = $res->whereIn('cat_id', $link_cats);
        }

        $res = $this->dscRepository->getAreaLinkGoods($res, $area_id, $area_city);

        if (config('shop.review_goods')) {
            $res = $res->whereIn('review_status', [3, 4, 5]);
        }

        if ($link_goods) {
            $res = $res->whereNotIn('goods_id', $link_goods);
        }

        $where = [
            'area_id' => $area_id,
            'area_city' => $area_city,
            'area_pricetype' => config('shop.area_pricetype')
        ];

        $res = $res->with([
            'getMemberPrice' => function ($query) use ($user_rank) {
                $query->where('user_rank', $user_rank);
            },
            'getWarehouseGoods' => function ($query) use ($warehouse_id) {
                $query->where('region_id', $warehouse_id);
            },
            'getWarehouseAreaGoods' => function ($query) use ($where) {
                $query = $query->where('region_id', $where['area_id']);

                if ($where['area_pricetype'] == 1) {
                    $query->where('city_id', $where['area_city']);
                }
            }
        ]);

        $res = $res->orderBy('sales_volume', 'DESC');

        $res = $res->skip(($page - 1) * $size)->take($size);

        $res = BaseRepository::getToArrayGet($res);

        return $res;
    }

    /**
     * 商品列表
     *
     * @param array $filter
     * @return array
     * @throws \Exception
     */
    public function getGoodsTypeList($filter = [])
    {
        if (empty($filter)) {
            return [];
        }

        $filter['page'] = $filter['page'] ?? 1;
        $filter['size'] = $filter['size'] ?? 10;
        $filter['sort'] = $filter['sort'] ?? '';
        $filter['order'] = $filter['order'] ?? 'desc';
        $filter['type'] = $filter['type'] ?? '';
        $filter['ru_id'] = $filter['ru_id'] ?? 0;
        $filter['user_id'] = $filter['user_id'] ?? 0;
        $filter['warehouse_id'] = $filter['warehouse_id'] ?? 0;
        $filter['area_id'] = $filter['area_id'] ?? 0;
        $filter['area_city'] = $filter['area_city'] ?? 0;

        $seconds = 3 * 60 * 60; //缓存3小时
        $cache_id = md5(serialize($filter));
        $goods = cache()->remember('goods_type_list' . $cache_id, $seconds, function () use ($filter) {
            if ($filter['user_id'] > 0) {
                $rank = $this->userCommonService->getUserRankByUid($filter['user_id']);
                $user_rank = $rank['rank_id'];
                $discount = isset($rank['discount']) ? $rank['discount'] : 100;
            } else {
                $user_rank = 1;
                $discount = 100;
            }

            $list = Goods::where('is_on_sale', 1)
                ->where('is_delete', 0)
                ->where('is_alone_sale', 1)
                ->where('is_show', 1);

            if ($filter['ru_id']) {
                $list = $list->where('ru_id', $filter['ru_id']);
            }

            $typeList = ['is_new', 'is_hot', 'is_best'];
            if (!empty($filter['type']) && in_array($filter['type'], $typeList)) {
                $list = $list->where($filter['type'], 1);
            }

            $start = ($filter['page'] - 1) * $filter['size'];

            if (!empty($filter['sort'])) {
                $list = $list->orderBy($filter['sort'], $filter['order']);
            } else {
                $list = $list->orderBy('sort_order', $filter['order'])
                    ->orderBy('goods_id', $filter['order']);
            }

            $list = $list->skip($start)
                ->take($filter['size']);

            $list = BaseRepository::getToArrayGet($list);
            $goods = [];
            if ($list) {

                $goods_id = BaseRepository::getKeyPluck($list, 'goods_id');

                $memberPrice = GoodsDataHandleService::goodsMemberPrice($goods_id, $user_rank);
                $warehouseGoods = GoodsDataHandleService::getWarehouseGoodsDataList($goods_id, $filter['warehouse_id']);
                $warehouseAreaGoods = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods_id, $filter['area_id'], $filter['area_city']);

                $merchantUseGoodsLabelList = GoodsDataHandleService::gettMerchantUseGoodsLabelDataList($goods_id);
                $merchantNoUseGoodsLabelList = GoodsDataHandleService::getMerchantNoUseGoodsLabelDataList($goods_id);

                $seller_id = BaseRepository::getKeyPluck($list, 'user_id');

                $shopInformation = MerchantDataHandleService::MerchantsShopInformationDataList($seller_id);
                $sellerShopinfo = MerchantDataHandleService::SellerShopinfoDataList($seller_id);
                $merchantList = MerchantDataHandleService::getMerchantInfoDataList($seller_id, 0, $sellerShopinfo, $shopInformation);

                foreach ($list as $key => $row) {
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

                    if ($row['promote_price'] > 0) {
                        $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                    } else {
                        $promote_price = 0;
                    }

                    $goods[$key]['promote_price'] = $row['promote_price'];
                    $goods[$key]['shop_price'] = $row['shop_price'];

                    $goodsSelf = false;
                    if ($row['user_id'] == 0) {
                        $goodsSelf = true;
                    }

                    if ($promote_price > 0) {
                        $goods[$key]['shop_price_formated'] = $this->dscRepository->getPriceFormat($promote_price, true, true, $goodsSelf);
                    } else {
                        $goods[$key]['shop_price_formated'] = $this->dscRepository->getPriceFormat($row['shop_price'], true, true, $goodsSelf);
                    }

                    $goods[$key]['shop_price'] = $this->dscRepository->getPriceFormat($goods[$key]['shop_price'], true, false, $goodsSelf);

                    $goods[$key]['market_price'] = $row['market_price'] ?? '';
                    $goods[$key]['market_price_formated'] = $this->dscRepository->getPriceFormat($row['market_price'], true, true, $goodsSelf);

                    $goods[$key]['market_price'] = $this->dscRepository->getPriceFormat($goods[$key]['market_price'], true, false, $goodsSelf);

                    $goods[$key]['goods_number'] = $row['goods_number'];
                    $goods[$key]['goods_id'] = $row['goods_id'];
                    $goods[$key]['title'] = $row['goods_name'];
                    $goods[$key]['sale'] = $row['sales_volume'];
                    $goods[$key]['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                    $goods[$key]['goods_img'] = $this->dscRepository->getImagePath($row['goods_img']);
                    $goods[$key]['url'] = dsc_url('/#/goods/' . $row['goods_id']);
                    $goods[$key]['app_page'] = config('route.goods.detail') . $row['goods_id'];
                    $goods[$key]['applet_page'] = config('route.goods.detail') . $row['goods_id'];

                    // 活动标签
                    $shop_information = $merchantList[$row['user_id']] ?? []; //通过ru_id获取到店铺信息;
                    $goods[$key]['country_icon'] = $shop_information['country_icon'] ?? '';

                    $where = [
                        'user_id' => $row['user_id'],
                        'goods_id' => $row['goods_id'],
                        'self_run' => $shop_information['self_run'] ?? 0,
                    ];
                    $goods_label_all = $this->goodsCommonService->getListGoodsLabelList($merchantUseGoodsLabelList, $merchantNoUseGoodsLabelList, $where);

                    $goods[$key]['goods_label'] = $goods_label_all['goods_label'] ?? [];
                    $goods[$key]['goods_label_suspension'] = $goods_label_all['goods_label_suspension'] ?? [];
                }
            }

            return $goods;
        });

        return $goods;
    }

    /**
     * 返回商品拼团活动
     */
    private function getGoodsTeamActivity($goods_id)
    {
        $team = [];

        $team_goods = TeamGoods::where('is_team', 1)
            ->where('is_audit', 2)
            ->whereHasIn('getGoods', function ($query) use ($goods_id) {
                $query->where('is_alone_sale', 1)
                    ->where('is_on_sale', 1)
                    ->where('is_delete', 0)
                    ->where('goods_id', $goods_id)
                    ->whereIn('review_status', [3, 4, 5]);
            });

        $team_goods = BaseRepository::getToArrayFirst($team_goods);

        if ($team_goods) {
            $team[$team_goods['id']] = [
                'team_id' => $team_goods['id'],
                'team_desc' => $team_goods['team_desc'],
                'sort' => $team_goods['sort_order'],
                'goods_id' => $goods_id,
                'type' => 'team',
                'url' => route('api.team.detail', ['goods_id' => $goods_id]),
            ];
        }

        return $team;
    }

    /**
     * 返回商品砍价活动
     */
    private function getGoodsBargainActivity($goods_id)
    {
        $bargain = [];

        $time = TimeRepository::getGmTime();

        $bargain_goods = BargainGoods::where('status', 0)
            ->where('is_audit', 2)
            ->where('is_delete', 0)
            ->where('start_time', '<', $time)
            ->where('end_time', '>', $time)
            ->where('goods_id', $goods_id)
            ->orderBy('id', 'DESC');

        $bargain_goods = BaseRepository::getToArrayFirst($bargain_goods);

        if ($bargain_goods) {
            $bargain[$bargain_goods['id']] = [
                'bargain_id' => $bargain_goods['id'],
                'bargain_desc' => $bargain_goods['bargain_desc'],
                'goods_id' => $goods_id,
                'type' => 'bargain',
                'url' => route('api.bargain.detail', ['id' => $bargain_goods['id']]),
            ];
        }

        return $bargain;
    }

    /**
     * 查询商品的关联商品
     *
     * @param int $goods_id
     * @param int $uid
     * @param int $size
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @return array
     * @throws \Exception
     */
    public function getLinkGoods($goods_id, $uid = 0, $size = 30, $warehouse_id = 0, $area_id = 0, $area_city = 0)
    {
        if ($uid > 0) {
            $rank = $this->userCommonService->getUserRankByUid($uid);
            $user_rank = $rank['rank_id'];
            $user_discount = isset($rank['discount']) ? $rank['discount'] : 100;
        } else {
            $user_rank = 1;
            $user_discount = 100;
        }

        //商品详情页
        $res = LinkGoods::query()->where('goods_id', $goods_id);
        $res = $res->with([
            'getGoods' => function ($query) {
                $query->where('is_on_sale', 1)
                    ->where('is_alone_sale', 1)
                    ->where('is_delete', 0)
                    ->where('is_show', 1);
            }
        ]);
        $res = $res->orderBy('sort', 'ASC');
        $goods_ids = $res->pluck('link_goods_id');
        $goods_ids = BaseRepository::getToArray($goods_ids);

        $query = [];

        if ($goods_ids) {
            $query = Goods::query()->where('is_on_sale', 1)
                ->where('is_alone_sale', 1)
                ->where('is_delete', 0)
                ->where('is_show', 1)
                ->whereIn('goods_id', $goods_ids);

            $where = [
                'area_id' => $area_id,
                'area_city' => $area_city,
                'area_pricetype' => config('shop.area_pricetype')
            ];

            $query = $query->with([
                'getMemberPrice' => function ($query) use ($user_rank) {
                    $query->where('user_rank', $user_rank);
                },
                'getWarehouseGoods' => function ($query) use ($warehouse_id) {
                    $query->where('region_id', $warehouse_id);
                },
                'getWarehouseAreaGoods' => function ($query) use ($where) {
                    $query = $query->where('region_id', $where['area_id']);

                    if ($where['area_pricetype'] == 1) {
                        $query->where('city_id', $where['area_city']);
                    }
                }
            ]);

            $query = $query->orderByRaw('FIND_IN_SET(goods_id,"' . BaseRepository::getImplode($goods_ids) . '"' . ")"); // 指定排序
            $query = $query->limit($size); // 限制条数

            $query = BaseRepository::getToArrayGet($query);
        }

        $link_goods = [];

        if ($query) {

            $ru_id = BaseRepository::getKeyPluck($query, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            $i = 0;
            foreach ($query as $k => $row) {
                $price = [
                    'model_price' => isset($row['model_price']) ? $row['model_price'] : 0,
                    'user_price' => isset($row['get_member_price']['user_price']) ? $row['get_member_price']['user_price'] : 0,
                    'percentage' => isset($row['get_member_price']['percentage']) ? $row['get_member_price']['percentage'] : 0,
                    'warehouse_price' => isset($row['get_warehouse_goods']['warehouse_price']) ? $row['get_warehouse_goods']['warehouse_price'] : 0,
                    'region_price' => isset($row['get_warehouse_area_goods']['region_price']) ? $row['get_warehouse_area_goods']['region_price'] : 0,
                    'shop_price' => isset($row['shop_price']) ? $row['shop_price'] : 0,
                    'warehouse_promote_price' => isset($row['get_warehouse_goods']['warehouse_promote_price']) ? $row['get_warehouse_goods']['warehouse_promote_price'] : 0,
                    'region_promote_price' => isset($row['get_warehouse_area_goods']['region_promote_price']) ? $row['get_warehouse_area_goods']['region_promote_price'] : 0,
                    'promote_price' => isset($row['promote_price']) ? $row['promote_price'] : 0,
                    'wg_number' => isset($row['get_warehouse_goods']['region_number']) ? $row['get_warehouse_goods']['region_number'] : 0,
                    'wag_number' => isset($row['get_warehouse_area_goods']['region_number']) ? $row['get_warehouse_area_goods']['region_number'] : 0,
                    'goods_number' => isset($row['goods_number']) ? $row['goods_number'] : 0
                ];

                $price = $this->goodsCommonService->getGoodsPrice($price, $user_discount / 100, $row);

                $row['shop_price'] = StrRepository::priceFormat($price['shop_price']);
                $row['promote_price'] = StrRepository::priceFormat($price['promote_price']);
                $row['goods_number'] = $price['goods_number'];

                $link_goods[$i] = $row;

                if ($row['promote_price'] > 0) {
                    $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                } else {
                    $promote_price = 0;
                }

                $link_goods[$i]['goods_id'] = $row['goods_id'];
                $link_goods[$i]['goods_name'] = $row['goods_name'];
                $link_goods[$i]['sales_volume'] = $row['sales_volume'];
                $link_goods[$i]['short_name'] = config('shop.goods_name_length') > 0 ? $this->dscRepository->subStr($row['goods_name'], config('shop.goods_name_length')) : $row['goods_name'];
                $link_goods[$i]['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                $link_goods[$i]['shop_price_formated'] = $this->dscRepository->getPriceFormat($row['shop_price']);
                $link_goods[$i]['promote_price_formated'] = ($promote_price > 0) ? $this->dscRepository->getPriceFormat($promote_price) : '';
                $link_goods[$i]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);

                $link_goods[$i]['shop_name'] = $merchantList[$row['user_id']]['shop_name'] ?? '';
                $link_goods[$i]['shopUrl'] = $this->dscRepository->buildUri('merchants_store', ['urid' => $row['user_id']]);
                $i++;
            }
        }

        return $link_goods;
    }

    /**
     * 获取商品最优价格
     *
     * @param $user_id
     * @param $goods
     * @return array
     * @throws \Exception
     */
    public function getBestPrice($user_id, $goods)
    {
        $coupons = $this->couponService->goodsCoupons($user_id, $goods['goods_id'], $goods['user_id']);

        // 商品信息
        $favourable = [];
        $res = $this->discountService->activityListAll($user_id, $goods['user_id']);

        $category_id = $goods['cat_id'];
        $brand_id = $goods['brand_id'];

        $time = TimeRepository::getGmTime();

        if ($goods['promote_end_date'] > $time) {
            $arr = [
                $goods['promote_price'],
                $goods['shop_price']
            ];
            $shop_price = BaseRepository::getArrayMin($arr);
        } else {
            $shop_price = $goods['shop_price'];
        }

        if ($res) {
            foreach ($res as $rows) {
                if ($rows['min_amount'] <= $shop_price) {
                    if ($rows['act_range'] == FAR_ALL) {
                        $favourable[$rows['act_id']]['act_id'] = $rows['act_id'];
                        if ($rows['userFav_type'] == 1) {
                            $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
                        } else {
                            $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                        }

                        $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                        $favourable[$rows['act_id']]['type'] = 'favourable';
                        $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
                        $favourable[$rows['act_id']]['url'] = route('api.activity.show', ['act_id' => $rows['act_id']]);
                        $favourable[$rows['act_id']]['min_amount'] = $rows['min_amount'];
                        $favourable[$rows['act_id']]['act_type_ext'] = $rows['act_type_ext'];

                        switch ($rows['act_type']) {
                            case 1: // 现金减免
                                $favourable[$rows['act_id']]['discount_amount'] = $rows['act_type_ext'];
                                break;
                            case 2: // 现金折扣
                                $favourable[$rows['act_id']]['discount_amount'] = $shop_price * (100 - $rows['act_type_ext']) / 100;
                                break;
                        }
                    } elseif ($rows['act_range'] == FAR_CATEGORY) {
                        /* 找出分类id的子分类id */
                        $id_list = [];
                        $raw_id_list = explode(',', $rows['act_range_ext']);

                        foreach ($raw_id_list as $id) {
                            /**
                             * 当前分类下的所有子分类
                             * 返回一维数组
                             */
                            $cat_list = $this->categoryService->getCatListChildren($id);
                            $id_list = array_merge($id_list, $cat_list);
                            array_unshift($id_list, $id);
                        }
                        $ids = join(',', array_unique($id_list));
                        if (strpos(',' . $ids . ',', ',' . $category_id . ',') !== false) {
                            $favourable[$rows['act_id']]['act_id'] = $rows['act_id'];
                            if ($rows['userFav_type'] == 1) {
                                $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
                            } else {
                                $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                            }
                            $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                            $favourable[$rows['act_id']]['type'] = 'favourable';
                            $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
                            $favourable[$rows['act_id']]['url'] = route('api.activity.show', ['act_id' => $rows['act_id']]);
                            $favourable[$rows['act_id']]['min_amount'] = $rows['min_amount'];
                            $favourable[$rows['act_id']]['act_type_ext'] = $rows['act_type_ext'];

                            switch ($rows['act_type']) {
                                case 1: // 现金减免
                                    $favourable[$rows['act_id']]['discount_amount'] = $rows['act_type_ext'];
                                    break;
                                case 2: // 现金折扣
                                    $favourable[$rows['act_id']]['discount_amount'] = $shop_price * (100 - $rows['act_type_ext']) / 100;
                                    break;
                            }
                        }
                    } elseif ($rows['act_range'] == FAR_BRAND) {
                        if (strpos(',' . $rows['act_range_ext'] . ',', ',' . $brand_id . ',') !== false) {
                            $favourable[$rows['act_id']]['act_id'] = $rows['act_id'];
                            if ($rows['userFav_type'] == 1) {
                                $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
                            } else {
                                $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                            }
                            $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                            $favourable[$rows['act_id']]['type'] = 'favourable';
                            $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
                            $favourable[$rows['act_id']]['url'] = route('api.activity.show', ['act_id' => $rows['act_id']]);
                            $favourable[$rows['act_id']]['min_amount'] = $rows['min_amount'];
                            $favourable[$rows['act_id']]['act_type_ext'] = $rows['act_type_ext'];

                            switch ($rows['act_type']) {
                                case 1: // 现金减免
                                    $favourable[$rows['act_id']]['discount_amount'] = $rows['act_type_ext'];
                                    break;
                                case 2: // 现金折扣
                                    $favourable[$rows['act_id']]['discount_amount'] = $shop_price * (100 - $rows['act_type_ext']) / 100;
                                    break;
                            }
                        }
                    } elseif ($rows['act_range'] == FAR_GOODS) {
                        if (strpos(',' . $rows['act_range_ext'] . ',', ',' . $goods['goods_id'] . ',') !== false) {
                            $favourable[$rows['act_id']]['act_id'] = $rows['act_id'];
                            if ($rows['userFav_type'] == 1) {
                                $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
                            } else {
                                $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                            }
                            $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                            $favourable[$rows['act_id']]['type'] = 'favourable';
                            $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
                            $favourable[$rows['act_id']]['url'] = route('api.activity.show', ['act_id' => $rows['act_id']]);
                            $favourable[$rows['act_id']]['min_amount'] = $rows['min_amount'];
                            $favourable[$rows['act_id']]['act_type_ext'] = $rows['act_type_ext'];

                            switch ($rows['act_type']) {
                                case 1: // 现金减免
                                    $favourable[$rows['act_id']]['discount_amount'] = $rows['act_type_ext'];
                                    break;
                                case 2: // 现金折扣
                                    $favourable[$rows['act_id']]['discount_amount'] = $shop_price * (100 - $rows['act_type_ext']) / 100;
                                    break;
                            }
                        }
                    }
                }
            }
        }

        $best_price = -1; // 初始化价格
        $cou_id = 0; // 初始化优惠券信息

        if ($coupons['res']) {

            $couIdList = BaseRepository::getKeyPluck($coupons['res'], 'cou_id');
            $couponsUserList = CouponDataHandleService::getCouponsUserDataList($couIdList, ['cou_id', 'is_delete']);

            foreach ($coupons['res'] as $val) {

                $sql = [
                    'where' => [
                        [
                            'name' => 'is_delete',
                            'value' => 0
                        ],
                        [
                            'name' => 'cou_id',
                            'value' => $val['cou_id']
                        ]
                    ]
                ];
                $couponsUser = BaseRepository::getArraySqlGet($couponsUserList, $sql);
                $cou_num = BaseRepository::getArrayCount($couponsUser);

                // 优惠券已领取数量小于总数 单品价格不小于最低门槛
                if ($cou_num < $val['cou_total'] && $shop_price >= $val['cou_man']) {
                    $price = $shop_price - $val['cou_money'];
                    $price = $price > 0 ? $price : 0;
                    // 已存在最优价格 需比对目前最优价格
                    if ($best_price >= 0) {
                        if ($price < $best_price) {
                            $best_price = $price;
                            $cou_id = $val['cou_id'];
                        }
                    } else {
                        $best_price = $price;
                        $cou_id = $val['cou_id'];
                    }
                }
            }
        }

        if ($favourable) {
            foreach ($favourable as $val) {
                if ($shop_price >= $val['min_amount']) {
                    $price = $shop_price - $val['discount_amount'];
                    $price = $price > 0 ? $price : 0;
                    // 已存在最优价格 需比对目前最优价格
                    if ($best_price >= 0) {
                        if ($price < $best_price) {
                            $best_price = $price;
                            $cou_id = 0;
                        }
                    } else {
                        $best_price = $price;
                    }
                }
            }
        }

        /* 扣除商品阶梯优惠金额 */
        $consumptionPrice = $this->dscRepository->getGoodsConsumptionPrice($goods['consumption'], $shop_price);
        $consumptionPrice = $shop_price > $consumptionPrice ? $shop_price - $consumptionPrice : 0;
        $best_price = $best_price - $consumptionPrice;

        $goodsSelf = false;
        if ($goods['user_id'] == 0) {
            $goodsSelf = true;
        }

        $price = $this->dscRepository->getPriceFormat($best_price, true, false, $goodsSelf);
        $formated_price = $this->dscRepository->getPriceFormat($best_price, true, true, $goodsSelf);

        return ['price' => $price, 'formated_price' => $formated_price, 'cou_id' => $cou_id];
    }
}
