<?php

namespace App\Services\Goods;

use App\Libraries\Http;
use App\Libraries\QRCode;
use App\Models\Attribute;
use App\Models\AutoManage;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Goods;
use App\Models\GoodsActivity;
use App\Models\GoodsArticle;
use App\Models\GoodsAttr;
use App\Models\LinkDescGoodsid;
use App\Models\LinkGoods;
use App\Models\MerchantsShopInformation;
use App\Models\PresaleActivity;
use App\Models\Seckill;
use App\Models\SeckillGoods;
use App\Models\SeckillTimeBucket;
use App\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Common\StrRepository;
use App\Services\Brand\BrandDataHandleService;
use App\Services\Cart\CartCommonService;
use App\Services\Category\CategoryService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\User\UserRankService;
use think\Image;

class GoodsService
{
    protected $dscRepository;
    protected $userRankService;
    protected $goodsCommonService;
    protected $goodsCommentService;
    protected $merchantCommonService;
    protected $goodsWarehouseService;
    protected $brandDataHandleService;

    public function __construct(
        DscRepository $dscRepository,
        UserRankService $userRankService,
        GoodsCommonService $goodsCommonService,
        GoodsCommentService $goodsCommentService,
        MerchantCommonService $merchantCommonService,
        GoodsWarehouseService $goodsWarehouseService,
        BrandDataHandleService $brandDataHandleService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->userRankService = $userRankService;
        $this->goodsCommonService = $goodsCommonService;
        $this->goodsCommentService = $goodsCommentService;
        $this->merchantCommonService = $merchantCommonService;
        $this->goodsWarehouseService = $goodsWarehouseService;
        $this->brandDataHandleService = $brandDataHandleService;
    }

    /**
     * 获得推荐商品
     *
     * @param array $where
     * @param int $num 限制显示数量
     * @return mixed
     * @throws \Exception
     */
    public function getRecommendGoods($where = [], $num = 10)
    {
        $type = $where['type'] ?? '';
        $seller_id = $where['seller_id'] ?? 0;
        $warehouse_id = $where['warehouse_id'] ?? 0;
        $area_id = $where['area_id'] ?? 0;
        $area_city = $where['area_city'] ?? 0;
        $presale = $where['presale'] ?? '';
        $discount = session('discount', 1);
        $user_rank = session('user_rank', 0);
        $area_pricetype = config('shop.area_pricetype');

        $where['discount'] = $discount;
        $where['user_rank'] = $user_rank;
        $where['area_pricetype'] = $area_pricetype;
        $where['num'] = $num;

        $keywords = $where['keywords'] ?? [];
        $keywords = BaseRepository::getExplode($keywords);
        $cache_keywords = $keywords ? implode(',', $keywords) : '';

        $brand_id = $where['brand_id'] ?? [];
        $brand_id = BaseRepository::getExplode($brand_id);
        $cache_brand = $brand_id ? implode(',', $brand_id) : '';

        //缓存
        $cache_id = $type . '_' . $seller_id . '_' . $warehouse_id . '_' . $area_id . '_' . $area_city . '_' . $presale . '_' . $discount . '_' . $user_rank . '_' . $area_pricetype . '_' . $cache_keywords . '_' . $cache_brand;

        $content = cache()->remember('get_recommend_goods.' . $cache_id, config('shop.cache_time'), function () use ($brand_id, $keywords, $where) {

            if (isset($where['type']) && !empty($where['type']) && !in_array($where['type'], ['best', 'new', 'hot'])) {
                return [];
            }

            $time = TimeRepository::getGmTime();

            $goods_id = [];
            $presaleActivityList = [];
            if (isset($where['presale']) && $where['presale'] == 'presale') {

                $cat_id = isset($where['cat_id']) ? BaseRepository::getExplode($where['cat_id']) : [];

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
            $result = Goods::select('goods_id', 'goods_name', 'user_id', 'is_show', 'goods_name_style', 'goods_brief', 'is_promote', 'comments_number', 'sales_volume', 'model_price', 'shop_price', 'promote_price', 'market_price', 'goods_thumb', 'goods_img', 'goods_number', 'promote_start_date', 'promote_end_date')
                ->addSelect('cat_id', 'is_new', 'is_best', 'is_hot', 'store_new', 'store_best', 'store_hot')
                ->where('is_alone_sale', 1)
                ->where('is_delete', 0)
                ->where('is_show', 1);

            if ($brand_id) {
                $brand_id = BaseRepository::getExplode($brand_id);
                $result = $result->whereIn('brand_id', $brand_id);
            }

            if ($keywords) {
                $result = $result->where(function ($query) use ($keywords) {
                    $query->where(function ($query) use ($keywords) {
                        $query = $query->where('is_on_sale', 1);

                        $query->where(function ($query) use ($keywords) {
                            foreach ($keywords as $key => $val) {
                                $query->orWhere(function ($query) use ($val) {
                                    $val = $this->dscRepository->mysqlLikeQuote(trim($val));

                                    $query = $query->orWhere('goods_name', 'like', '%' . $val . '%');
                                    $query->orWhere('keywords', 'like', '%' . $val . '%');
                                });
                            }

                            $keyword_goods_sn = $keywords[0] ?? '';

                            if ($keyword_goods_sn) {
                                // 搜索商品货号
                                $query->orWhere('goods_sn', 'like', '%' . $keyword_goods_sn . '%');
                            }
                        });
                    });
                });
            }

            if (!(isset($where['presale']) && $where['presale'] == 'presale')) {
                $result = $result->where('is_on_sale', 1);
            }

            $catList = $where['catList'] ?? [];
            if (!empty($catList)) {
                $result = $result->whereIn('cat_id', $catList);
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

            $result = $result->with([
                'getMemberPrice' => function ($query) use ($where) {
                    $query->where('user_rank', $where['user_rank']);
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

            $result = $result->orderBy('sort_order', 'desc');

            $result = $result->take($where['num']);

            $result = BaseRepository::getToArrayGet($result);

            $goods = [];
            if ($result) {

                $brand_id = BaseRepository::getKeyPluck($result, 'brand_id');
                $brandList = BrandDataHandleService::goodsBrand($brand_id, ['brand_id', 'brand_name']);

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

                    $price = $this->goodsCommonService->getGoodsPrice($price, $where['discount'], $row);

                    $row['shop_price'] = $price['shop_price'];
                    $row['promote_price'] = $price['promote_price'];
                    $row['goods_number'] = $price['goods_number'];

                    $goods[$idx] = $row;

                    if ($row['promote_price'] > 0) {
                        $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                    } else {
                        $promote_price = 0;
                    }

                    $goods[$idx]['id'] = $row['goods_id'];
                    $goods[$idx]['name'] = $row['goods_name'];
                    $goods[$idx]['is_promote'] = $row['is_promote'];
                    $goods[$idx]['brief'] = $row['goods_brief'];
                    $goods[$idx]['comments_number'] = $row['comments_number'];
                    $goods[$idx]['sales_volume'] = $row['sales_volume'];
                    $goods[$idx]['brand_name'] = $brandList[$row['brand_id']]['brand_name'] ?? '';
                    $goods[$idx]['goods_style_name'] = $this->goodsCommonService->addStyle($row['goods_name'], $row['goods_name_style']);
                    $goods[$idx]['short_name'] = config('shop.goods_name_length') > 0 ? $this->dscRepository->subStr($row['goods_name'], config('shop.goods_name_length')) : $row['goods_name'];

                    $goodsSelf = false;
                    if ($row['user_id'] == 0) {
                        $goodsSelf = true;
                    }

                    $goods[$idx]['short_style_name'] = $this->goodsCommonService->addStyle($goods[$idx]['short_name'], $row['goods_name_style']);
                    $goods[$idx]['market_price'] = $this->dscRepository->getPriceFormat($row['market_price'], true, true, $goodsSelf);
                    $goods[$idx]['shop_price'] = $this->dscRepository->getPriceFormat($row['shop_price'], true, true, $goodsSelf);
                    $goods[$idx]['promote_price'] = $promote_price > 0 ? $this->dscRepository->getPriceFormat($promote_price, true, true, $goodsSelf) : '';
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
        });

        return $content;
    }

    /**
     * 获得促销商品
     *
     * @param array $where
     * @param int $num 限制显示数量
     * @return array
     */
    public function getPromoteGoods($where = [], $num = 3)
    {
        $time = TimeRepository::getGmTime();
        $order_type = config('shop.recommend_order');

        $result = Goods::where('is_on_sale', 1)
            ->where('is_alone_sale', 1)
            ->where('is_delete', 0)
            ->where('is_show', 1)
            ->where('is_promote', 1);

        $result = $result->where('promote_start_date', '<=', $time)
            ->where('promote_end_date', '>=', $time);

        $result = $this->dscRepository->getAreaLinkGoods($result, $where['area_id'], $where['area_city']);

        if (config('shop.review_goods') == 1) {
            $result = $result->whereIn('review_status', [3, 4, 5]);
        }

        $where['area_pricetype'] = config('shop.area_pricetype');

        $user_rank = session('user_rank');
        $result = $result->with([
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

        if ($order_type == 0) {
            $result = $result->orderByRaw('sort_order, last_update desc');
        } else {
            $result = $result->orderByRaw('RAND()');
        }

        $result = $result->take($num);

        $result = BaseRepository::getToArrayGet($result);

        $goods = [];
        if ($result) {
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

                $price = $this->goodsCommonService->getGoodsPrice($price, session('discount'), $row);

                $row['shop_price'] = $price['shop_price'];
                $row['promote_price'] = $price['promote_price'];
                $row['goods_number'] = $price['goods_number'];

                $row = $row['get_brand'] ? array_merge($row, $row['get_brand']) : $row;

                $goods[$idx] = $row;

                if ($row['promote_price'] > 0) {
                    $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                } else {
                    $promote_price = 0;
                }

                $goods[$idx]['id'] = $row['goods_id'];
                $goods[$idx]['s_time'] = $row['promote_start_date'];
                $goods[$idx]['e_time'] = $row['promote_end_date'];
                $goods[$idx]['t_now'] = $time;
                $goods[$idx]['id'] = $row['goods_id'];
                $goods[$idx]['name'] = $row['goods_name'];
                $goods[$idx]['brief'] = $row['goods_brief'];
                $goods[$idx]['brand_name'] = isset($row['brand_name']) ? $row['brand_name'] : '';
                $goods[$idx]['comments_number'] = $row['comments_number'];
                $goods[$idx]['sales_volume'] = $row['sales_volume'];
                $goods[$idx]['goods_style_name'] = $this->goodsCommonService->addStyle($row['goods_name'], $row['goods_name_style']);
                $goods[$idx]['short_name'] = config('shop.goods_name_length') > 0 ? $this->dscRepository->subStr($row['goods_name'], config('shop.goods_name_length')) : $row['goods_name'];
                $goods[$idx]['short_style_name'] = $this->goodsCommonService->addStyle($goods[$idx]['short_name'], $row['goods_name_style']);
                $goods[$idx]['market_price'] = $this->dscRepository->getPriceFormat($row['market_price']);
                $goods[$idx]['shop_price'] = $this->dscRepository->getPriceFormat($row['shop_price']);
                $goods[$idx]['promote_price'] = ($promote_price > 0) ? $this->dscRepository->getPriceFormat($promote_price) : '';
                $goods[$idx]['thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                $goods[$idx]['goods_img'] = $this->dscRepository->getImagePath($row['goods_img']);
                $goods[$idx]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
            }
        }

        return $goods;
    }

    /**
     * 获得商品的详细信息
     *
     * @param array $where
     * @return mixed
     * @throws \Exception
     */
    public function getGoodsInfo($where = [])
    {
        $area_pricetype = config('shop.area_pricetype');

        if (session()->has('user_rank')) {
            $user_rank = session('user_rank');
            $discount = session('discount', 1);
        } else {
            $user_rank = 1;
            $discount = 1;
            if (isset($where['user_id']) && $where['user_id']) {
                $user_rank = $this->userRankService->getUserRankInfo($where['user_id']);
                if ($user_rank) {
                    $user_rank = $user_rank['user_rank'] ?? 1;
                    $discount = $user_rank['discount'] ?? 1;
                }
            }
        }

        $where['area_pricetype'] = $area_pricetype;
        $where['user_rank'] = $user_rank;
        $where['discount'] = $discount;

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

        $row = BaseRepository::getToArrayFirst($res);
        $time = TimeRepository::getGmTime();

        $tag = [];
        if ($row) {
            $warehouse_id = $where['warehouse_id'] ?? 0;
            $area_id = $where['area_id'] ?? 0;
            $area_city = $where['area_city'] ?? 0;

            $memberPrice = GoodsDataHandleService::goodsMemberPrice($row['goods_id'], $user_rank);
            $warehouseGoods = GoodsDataHandleService::getWarehouseGoodsDataList($row['goods_id'], $warehouse_id);
            $warehouseAreaGoods = GoodsDataHandleService::getWarehouseAreaGoodsDataList($row['goods_id'], $area_id, $area_city);
            $brand = $this->brandDataHandleService->goodsBrand($row['brand_id']);
            $category = GoodsDataHandleService::getGoodsCategoryDataList($row['cat_id']);

            $row['get_member_price'] = $memberPrice[$row['goods_id']] ?? [];
            $row['get_warehouse_goods'] = $warehouseGoods[$row['goods_id']] ?? [];
            $row['get_warehouse_area_goods'] = $warehouseAreaGoods[$row['goods_id']] ?? [];
            $row['get_goods_category'] = $category[$row['cat_id']] ?? [];
            $row['get_brand'] = $brand[$row['brand_id']] ?? [];
            $brand = $row['get_brand'];
            $cat = $row['get_goods_category'];

            $row['brand_name'] = '';
            $row['brand_url'] = '';
            if ($brand) {
                $brand['url'] = $this->dscRepository->buildUri('brand', ['bid' => $brand['brand_id']], $brand['brand_name']);
                $brand['brand_logo'] = $this->dscRepository->getImagePath($this->dscRepository->dataDir() . '/brandlogo/' . $brand['brand_logo']);

                $row['brand'] = $brand;
                $row['goods_brand_url'] = !empty($brand) ? $brand['url'] : '';

                $row['brand_name'] = $brand['brand_name'] ?? '';
                $row['brand_url'] = $this->dscRepository->buildUri('brand', ['bid' => $row['brand_id']], $brand['brand_name']);
            }

            $row['cat_measure_unit'] = $cat['measure_unit'];

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

            // 商品原价不含会员折扣
            $row['shop_price_original'] = $row['shop_price'] ?? 0;

            $price = $this->goodsCommonService->getGoodsPrice($price, $where['discount'], $row);

            $row['shop_price'] = $price['shop_price'];
            $row['promote_price'] = $price['promote_price'];
            $row['integral'] = $price['integral'];
            $row['goods_number'] = $price['goods_number'];

            //@author-bylu 将分期数据反序列化为数组 start
            if (!empty($row)) {
                $row['stages'] = unserialize($row['stages']);
            }
            //@author-bylu  end

            /* 修正促销价格 */
            if ($row['promote_price'] > 0) {
                $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
            } else {
                $promote_price = 0;
            }

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

            $row['is_promote'] = ($promote_price > 0) ? 1 : 0;
            $row['promote_price_org'] = $promote_price;
            $row['promote_price'] = ($promote_price > 0) ? $this->dscRepository->getPriceFormat($promote_price) : 0;

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

            /* 促销时间倒计时 */
            if ($time >= $row['promote_start_date'] && $time <= $row['promote_end_date']) {
                $row['gmt_end_time'] = $row['promote_end_date'];
            } else {
                $row['gmt_end_time'] = 0;
            }

            $row['promote_end_time'] = !empty($row['gmt_end_time']) ? TimeRepository::getLocalDate(config('shop.time_format'), $row['gmt_end_time']) : 0;

            /* 是否显示商品库存数量 */
            $row['goods_number'] = (config('shop.use_storage') == 1) ? $row['goods_number'] : 1;

            /* 修正积分：转换为可使用多少积分（原来是可以使用多少钱的积分） */
            $row['integral'] = config('shop.integral_scale') ? round($row['integral'] * 100 / config('shop.integral_scale')) : 0;

            /* 修正商品图片 */

            //查询关联商品描述 start
            if ($row['goods_desc'] == '<p><br/></p>' || empty($row['goods_desc'])) {
                $GoodsDesc = $this->getLinkGoodsDesc($row['goods_id'], $row['user_id']);
                $link_desc = $GoodsDesc ? $GoodsDesc['goods_desc'] : '';

                if (!empty($link_desc)) {
                    $row['goods_desc'] = $link_desc;
                }
            }
            //查询关联商品描述 end

            $desc_preg = $this->dscRepository->descImagesPreg($row['goods_desc']);
            $row['goods_desc'] = $desc_preg['goods_desc'];

            $row['goods_img'] = $this->dscRepository->getImagePath($row['goods_img']);
            $row['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
            $row['original_img'] = $this->dscRepository->getImagePath($row['original_img']);
            $row['goods_video_path'] = !empty($row['goods_video']) ? $this->dscRepository->getImagePath($row['goods_video']) : '';

            /* 获得商品的销售价格 */
            $row['marketPrice'] = $row['market_price'];
            $row['market_price'] = $this->dscRepository->getPriceFormat($row['market_price']);
            if ($promote_price > 0) {
                $row['shop_price_formated'] = $row['promote_price'];
                $row['goods_price'] = $promote_price;
            } else {
                $row['shop_price_formated'] = $this->dscRepository->getPriceFormat($row['shop_price']);
                $row['goods_price'] = $row['shop_price'];
            }

            $row['shop_price'] = round($row['shop_price'], 2);
            $row['format_promote_price'] = $promote_price > 0 ? $promote_price : 0;

            $row['goodsweight'] = $row['goods_weight'];

            $row['isHas_attr'] = GoodsAttr::where('goods_id', $row['goods_id'])->count('goods_attr_id');

            $merchantList = MerchantDataHandleService::getMerchantInfoDataList([$row['user_id']]);
            $shop_information = $merchantList[$row['user_id']] ?? [];

            $row['country_name'] = $shop_information['country_name'] ?? '';
            $row['country_icon'] = $shop_information['country_icon'] ?? '';
            $row['cross_warehouse_name'] = $shop_information['cross_warehouse_name'] ?? '';

            $row['shop_information'] = $shop_information;
            $row['rz_shop_name'] = isset($shop_information['shop_name']) ? $shop_information['shop_name'] : '';

            $build_uri = [
                'urid' => $row['user_id'],
                'append' => $row['rz_shop_name']
            ];
            $domain_url = $this->merchantCommonService->getSellerDomainUrl($row['user_id'], $build_uri);
            $row['store_url'] = $domain_url['domain_name'];

            if ($shop_information) {
                $row['shopinfo'] = $shop_information;
                $row['shopinfo']['brand_thumb'] = $this->dscRepository->brandImagePath($shop_information['brand_thumb']);
                $row['shopinfo']['brand_thumb'] = str_replace(['../'], '', $shop_information['brand_thumb']);
                $row['shopinfo']['brand_thumb'] = $this->dscRepository->getImagePath($shop_information['brand_thumb']);
            }

            $row['goods_url'] = $this->dscRepository->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);

            $consumption = app(CartCommonService::class)->getGoodsConList($row['goods_id'], 'goods_consumption'); //满减订单金额
            $row['consumption'] = $consumption;

            /* 修正重量显示 */
            $row['goods_weight'] = $row['goods_weight'] . lang('goods.kilogram');

            $row['suppliers_name'] = '';
            if ($row['suppliers_id'] > 0 && file_exists(SUPPLIERS)) {
                $row['suppliers_name'] = \App\Modules\Suppliers\Models\Suppliers::where('suppliers_id', $row['suppliers_id'])->value('suppliers_name');
                $row['suppliers_name'] = $row['suppliers_name'] ?? '';
            }

            //买家印象
            if ($row['goods_product_tag']) {
                $impression_list = !empty($row['goods_product_tag']) ? explode(',', $row['goods_product_tag']) : '';

                if ($impression_list) {
                    foreach ($impression_list as $kk => $vv) {
                        $tag[$kk]['txt'] = $vv;
                        //印象数量
                        $tag[$kk]['num'] = $this->goodsCommentService->commentGoodsTagNum($row['goods_id'], $vv);
                    }
                }

                $row['impression_list'] = $tag;
            }

            //上架下架时间
            $manage_info = AutoManage::where('type', 'goods')->where('item_id', $row['goods_id']);
            $manage_info = BaseRepository::getToArrayFirst($manage_info);

            if ($manage_info) {
                $row['add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $manage_info['starttime']);
            } else {
                /* 修正上架时间显示 */
                $row['add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['add_time']);
            }

            $row['end_time'] = $manage_info ? TimeRepository::getLocalDate(config('shop.time_format'), $manage_info['endtime']) : '';

            // 活动标签
            $self_run = MerchantsShopInformation::where('user_id', $row['user_id'])->value('self_run');
            $self_run = $row['user_id'] == 0 || $self_run == 1 ? 1 : 0;
            $goods_label_all = $this->goodsCommonService->getGoodsLabelList($row['goods_id'], $self_run);

            $row['goods_label'] = $goods_label_all['goods_label'] ?? [];
            $row['goods_label_suspension'] = $goods_label_all['goods_label_suspension'] ?? [];

            // 服务标签
            $self_run = $shop_information['self_run'];
            $self_run = $row['user_id'] == 0 || $self_run == 1 ? 1 : 0;
            $goods_label_all = $this->goodsCommonService->getGoodsServicesLabelList($row['goods_id'], $self_run, $row['goods_cause']);

            $row['goods_services_label'] = $goods_label_all['goods_services_label'] ?? [];
        }

        return $row;
    }

    /**
     * 获得商品列表
     *
     * @param array $where
     * @return mixed
     */
    public function getGoodsList($where = [])
    {
        $res = Goods::whereRaw(1);

        if (isset($where['goods_id'])) {
            $res = $res->where('goods_id', $where['goods_id']);
        }

        if (isset($where['is_delete'])) {
            $res = $res->where('is_delete', $where['is_delete']);
        }

        if (isset($where['is_on_sale'])) {
            $res = $res->where('is_on_sale', $where['is_on_sale']);
        }

        if (isset($where['is_alone_sale'])) {
            $res = $res->where('is_alone_sale', $where['is_alone_sale']);
        }

        if (isset($where['cat_id'])) {
            $where['cat_id'] = !is_array($where['cat_id']) ? explode(",", $where['cat_id']) : $where['cat_id'];
            $res = $res->whereIn('cat_id', $where['cat_id']);
        }

        if (isset($where['user_cat'])) {
            $where['user_cat'] = !is_array($where['user_cat']) ? explode(",", $where['user_cat']) : $where['user_cat'];
            $res = $res->whereIn('user_cat', $where['user_cat']);
        }

        if (isset($where['brand_id'])) {
            $res = $res->where('brand_id', $where['brand_id']);
        }

        if (isset($where['intro_type']) && $where['intro_type'] == 'is_promote') {
            $res = $res->where('promote_start_date', '<=', $where['time']);
            $res = $res->where('promote_end_date', '>=', $where['time']);
        }

        if (isset($where['intro_type']) && $where['intro_type']) {
            $res = $res->where($where['intro_type'], 1);
        }

        if (isset($where['collect']) && $where['collect'] == 'collect_goods') {
            $res = $res->whereHasIn('getCollectGoods', function ($query) use ($where) {
                if (isset($where['user_id'])) {
                    $query->where('user_id', $where['user_id']);
                }
            });
        }

        $where['area_pricetype'] = config('shop.area_pricetype');
        $where['warehouse_id'] = $where['warehouse_id'] ?? 0;
        $where['area_id'] = $where['area_id'] ?? 0;
        $where['area_city'] = $where['area_city'] ?? 0;

        $user_rank = session('user_rank');
        $res = $res->with([
            'getGoodsCategory',
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

        if (isset($where['sort_rnd'])) {
            $res = $res->orderByRaw($where['sort_rnd']);
        } else {
            if (isset($where['sort']) && isset($where['order'])) {
                if (is_array($where['sort'])) {
                    $where['sort'] = implode(",", $where['sort']);
                    $res = $res->orderByRaw($where['sort'] . " " . $where['order']);
                } else {
                    $res = $res->orderBy($where['sort'], $where['order']);
                }
            }
        }

        if (isset($where['page'])) {
            $start = ($where['page'] - 1) * $where['size'];
        }

        if ($start > 0) {
            $res = $res->skip($start);
        }

        if (isset($where['size']) && $where['size'] > 0) {
            $res = $res->take($where['size']);
        }

        $res = $res->get();

        $res = $res ? $res->toArray() : [];

        if ($res) {
            foreach ($res as $key => $row) {
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

                $price = $this->goodsCommonService->getGoodsPrice($price, session('discount'), $row);

                $row['shop_price'] = $price['shop_price'];
                $row['promote_price'] = $price['promote_price'];
                $row['goods_number'] = $price['goods_number'];

                if ($row['promote_price'] > 0) {
                    $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                } else {
                    $promote_price = 0;
                }

                $row['promote_price'] = $promote_price;

                $cat = $row['get_goods_category'] ? $row['get_goods_category'] : [];
                $row['cat_id'] = $cat ? $cat['cat_id'] : 0;
                $row['cat_name'] = $cat ? $cat['cat_name'] : '';

                $brand = $row['get_brand'] ? $row['get_brand'] : [];
                $row['brand_id'] = $brand ? $brand['brand_id'] : 0;
                $row['brand_name'] = $brand ? $brand['brand_name'] : '';

                $res[$key] = $row;
            }
        }

        return $res;
    }

    /**
     * 获得指定商品的关联商品
     *
     * @param int $goods_id
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @return array
     */
    public function getLinkedGoods($goods_id = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0)
    {
        $user_rank = session('user_rank', 0);
        $discount = session('discount', 1);

        $res = LinkGoods::where('goods_id', $goods_id);

        $where = [
            'open_area_goods' => config('shop.open_area_goods'),
            'area_id' => $area_id,
            'area_city' => $area_city
        ];
        $res = $res->whereHasIn('getGoods', function ($query) use ($where) {
            $query = $query->where('is_on_sale', 1)
                ->where('is_alone_sale', 1)
                ->where('is_delete', 0);

            $this->dscRepository->getAreaLinkGoods($query, $where['area_id'], $where['area_city']);
        });

        $where = [
            'area_id' => $area_id,
            'area_city' => $area_city,
            'area_pricetype' => config('shop.area_pricetype')
        ];

        $res = $res->with([
            'getGoods',
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

        $res = $res->take(config('shop.related_goods_number'));

        $res = $res->orderby('sort', 'ASC');

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $row = BaseRepository::getArrayMerge($row, $row['get_goods']);

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

                $goods_id = $row['link_goods_id'];

                $arr[$goods_id] = $row;

                if ($row['promote_price'] > 0) {
                    $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                } else {
                    $promote_price = 0;
                }

                $watermark_img = '';

                if ($promote_price != 0) {
                    $watermark_img = "watermark_promote_small";
                } elseif ($row['is_new'] != 0) {
                    $watermark_img = "watermark_new_small";
                } elseif ($row['is_best'] != 0) {
                    $watermark_img = "watermark_best_small";
                } elseif ($row['is_hot'] != 0) {
                    $watermark_img = 'watermark_hot_small';
                }

                if ($watermark_img != '') {
                    $arr[$goods_id]['watermark_img'] = $watermark_img;
                }

                $arr[$goods_id]['goods_id'] = $row['goods_id'];
                $arr[$goods_id]['goods_name'] = $row['goods_name'];
                $arr[$goods_id]['short_name'] = config('shop.goods_name_length') > 0 ?
                    $this->dscRepository->subStr($row['goods_name'], config('shop.goods_name_length')) : $row['goods_name'];
                $arr[$goods_id]['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                $arr[$goods_id]['goods_img'] = $this->dscRepository->getImagePath($row['goods_img']);
                $arr[$goods_id]['market_price'] = $this->dscRepository->getPriceFormat($row['market_price']);
                $arr[$goods_id]['shop_price'] = $this->dscRepository->getPriceFormat($row['shop_price']);
                $arr[$goods_id]['promote_price'] = ($promote_price > 0) ? $this->dscRepository->getPriceFormat($promote_price) : '';
                $arr[$goods_id]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
                $arr[$goods_id]['sales_volume'] = $row['sales_volume'];
            }
        }

        return $arr;
    }

    /**
     * 获得指定商品的关联文章
     *
     * @param $goods_id
     * @return array|\Illuminate\Support\Collection
     */
    public function getLinkedArticles($goods_id)
    {
        $res = GoodsArticle::where('goods_id', $goods_id);

        $res = $res->whereHasIn('getArticleInfo', function ($query) {
            $query->where('is_open', 1);
        });

        $res = $res->with(['getArticleInfo']);

        $res = $res->get();

        $res = $res ? $res->toArray() : [];

        if ($res) {
            foreach ($res as $key => $row) {
                $row = $row['get_article_info'] ? array_merge($row, $row['get_article_info']) : $row;

                $row['url'] = $row['open_type'] != 1 ?
                    $this->dscRepository->buildUri('article', ['aid' => $row['article_id']], $row['title']) : trim($row['file_url']);
                $row['file_url'] = $this->dscRepository->getImagePath($row['file_url']);
                $row['add_time'] = TimeRepository::getLocalDate(config('shop.date_format'), $row['add_time']);
                $row['short_title'] = config('shop.article_title_length') > 0 ?
                    $this->dscRepository->subStr($row['title'], config('shop.article_title_length')) : $row['title'];

                $res[$key] = $row;
            }

            $res = collect($res)->sortByDesc('add_time');
            $res = $res->values()->all();
        }

        return $res;
    }

    /**
     * 取得跟商品关联的礼包列表
     *
     * @param int $goods_id
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @return array
     */
    public function getPackageGoodsList($goods_id = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0)
    {
        $user_rank = session('user_rank', 0);
        $discount = session('discount', 1);

        $now = TimeRepository::getGmTime();

        $res = GoodsActivity::where('review_status', 3)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now);

        $res = $res->whereHasIn('getPackageGoods', function ($query) use ($goods_id) {
            $query->where('goods_id', $goods_id);
        });

        $whereGoods = [
            'goods_id' => $goods_id,
            'user_rank' => $user_rank,
            'warehouse_id' => $warehouse_id,
            'area_id' => $area_id,
            'area_city' => $area_city,
            'area_pricetype' => config('shop.area_pricetype')
        ];

        $res = $res->with([
            'getPackageGoodsList' => function ($query) use ($whereGoods) {
                $query = $query->select('package_id', 'goods_id', 'goods_number', 'admin_id', 'product_id');
                $query->with([
                    'getGoods' => function ($query) use ($whereGoods) {
                        $query->with([
                            'getMemberPrice' => function ($query) use ($whereGoods) {
                                $query->where('user_rank', $whereGoods['user_rank']);
                            },
                            'getWarehouseGoods' => function ($query) use ($whereGoods) {
                                $query->where('region_id', $whereGoods['warehouse_id']);
                            },
                            'getWarehouseAreaGoods' => function ($query) use ($whereGoods) {
                                $query = $query->where('region_id', $whereGoods['area_id']);

                                if ($whereGoods['area_pricetype'] == 1) {
                                    $query->where('city_id', $whereGoods['area_city']);
                                }
                            },

                        ]);
                    },
                    'getGoodsAttrList' => function ($query) {
                        $query = $query->whereHasIn('getGoodsAttribute', function ($query) {
                            $query->where('attr_type', 1);
                        });

                        $query->orderBy('attr_sort');
                    },
                    'getProducts' => function ($query) {
                        $query->select('product_id', 'goods_attr');
                    },
                    'getProductsWarehouse' => function ($query) use ($whereGoods) {
                        $query->select('product_id', 'goods_attr')
                            ->where('warehouse_id', $whereGoods['warehouse_id']);
                    },
                    'getProductsArea' => function ($query) use ($whereGoods) {
                        $query = $query->select('product_id', 'goods_attr')
                            ->where('area_id', $whereGoods['area_id']);

                        if ($whereGoods['area_pricetype'] == 1) {
                            $query->where('city_id', $whereGoods['area_city']);
                        }
                    }
                ]);
            }
        ]);

        $res = $res->orderBy('act_id');

        $res = $res->get();

        $res = $res ? $res->toArray() : [];

        if ($res) {
            foreach ($res as $tempkey => $value) {
                $res[$tempkey] = $value;

                $subtotal = 0;
                $row = unserialize($value['ext_info']);
                unset($value['ext_info']);
                if ($row) {
                    foreach ($row as $key => $val) {
                        $res[$tempkey][$key] = $val;
                    }
                }

                $goods_res = $value['get_package_goods_list'] ? $value['get_package_goods_list'] : [];

                $result_goods_attr = [];
                if ($goods_res) {
                    foreach ($goods_res as $key => $val) {
                        $val['goods_thumb'] = '';
                        $val['market_price'] = 0;
                        $val['shop_price'] = 0;
                        $val['promote_price'] = 0;

                        $goods = $val['get_goods'] ?? [];

                        /* 取商品属性 */
                        $result_goods_attr[] = $val['get_goods_attr_list'];

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
                            'wg_number' => isset($row['get_warehouse_goods']['region_number']) ? $row['get_warehouse_goods']['region_number'] : 0,
                            'wag_number' => isset($row['get_warehouse_area_goods']['region_number']) ? $row['get_warehouse_area_goods']['region_number'] : 0,
                            'goods_number' => isset($row['goods_number']) ? $row['goods_number'] : 0
                        ];

                        $price = $this->goodsCommonService->getGoodsPrice($price, $discount, $goods);

                        $goods['shop_price'] = $price['shop_price'];
                        $goods['promote_price'] = $price['promote_price'];

                        if ($goods['promote_price'] > 0) {
                            $goods['promote_price'] = $this->goodsCommonService->getBargainPrice($goods['promote_price'], $goods['promote_start_date'], $goods['promote_end_date']);
                        } else {
                            $goods['promote_price'] = 0;
                        }

                        $val = $goods ? array_merge($val, $goods) : $val;


                        $goods_res[$key]['goods_thumb'] = $this->dscRepository->getImagePath($val['goods_thumb']);
                        $goods_res[$key]['market_price'] = $this->dscRepository->getPriceFormat($val['market_price']);
                        $goods_res[$key]['rank_price'] = $this->dscRepository->getPriceFormat($val['shop_price']);
                        $goods_res[$key]['promote_price'] = $this->dscRepository->getPriceFormat($val['promote_price']);
                        $subtotal += $goods['shop_price'] * $val['goods_number'];

                        if ($price['model_price'] == 1) {
                            $products = $val['get_products_warehouse'] ?? [];
                        } elseif ($price['model_price'] == 1) {
                            $products = $val['get_parehouse_areaGoods'] ?? [];
                        } else {
                            $products = $val['get_products'] ?? [];
                        }

                        $goods_res[$key]['goods_attr'] = $products['goods_attr'] ?? '';
                    }
                }

                /* 取商品属性 */
                $result_goods_attr = BaseRepository::getArrayCollapse($result_goods_attr);

                $_goods_attr = [];
                if ($result_goods_attr) {
                    foreach ($result_goods_attr as $attrValue) {
                        if ($attrValue && $attrValue['attr_value']) {
                            $_goods_attr[$attrValue['goods_attr_id']] = $attrValue['attr_value'];
                        }
                    }
                }

                /* 处理货品 */
                $format = '[%s]';
                if ($goods_res) {
                    foreach ($goods_res as $key => $val) {
                        if (isset($val['goods_attr']) && $val['goods_attr'] != '') {
                            $goods_attr_array = explode('|', $val['goods_attr']);

                            $goods_attr = [];
                            foreach ($goods_attr_array as $_attr) {
                                if (isset($_goods_attr[$_attr]) && $_goods_attr[$_attr]) {
                                    $goods_attr[] = $_goods_attr[$_attr];
                                }
                            }

                            $goods_res[$key]['goods_attr_str'] = sprintf($format, implode('，', $goods_attr));
                        }
                    }
                }

                $res[$tempkey]['goods_list'] = $goods_res;
                $res[$tempkey]['subtotal'] = $this->dscRepository->getPriceFormat($subtotal);
                $res[$tempkey]['saving'] = $this->dscRepository->getPriceFormat(($subtotal - $res[$tempkey]['package_price']));
                $res[$tempkey]['package_price'] = $this->dscRepository->getPriceFormat($res[$tempkey]['package_price']);
            }
        }

        return $res;
    }

    /*
     * 相关分类
     */
    public function getGoodsRelatedCat($cat_id)
    {
        $parent_id = Category::where('cat_id', $cat_id)->value('parent_id');

        $res = Category::where('parent_id', $parent_id)->get();
        $res = $res ? $res->toArray() : [];

        if ($res) {
            foreach ($res as $key => $row) {
                $res[$key]['cat_id'] = $row['cat_id'];
                $res[$key]['cat_name'] = $row['cat_name'];
                $res[$key]['url'] = $this->dscRepository->buildUri('category', ['cid' => $row['cat_id']], $row['cat_name']);
            }
        }

        return $res;
    }

    /**
     * 同类其他品牌
     *
     * @param int $cat_id
     * @return array
     */
    public function getGoodsSimilarBrand($cat_id = 0)
    {
        $brand = Goods::select('brand_id')
            ->where('cat_id', $cat_id)
            ->groupBy('brand_id');
        $brand = BaseRepository::getToArrayGet($brand);
        $brand = BaseRepository::getKeyPluck($brand, 'brand_id');

        $res = [];
        if ($brand) {
            $res = Brand::whereIn('brand_id', $brand)
                ->where('is_show', 1);

            $res = $res->take(10);

            $res = BaseRepository::getToArrayGet($res);

            if ($res) {
                foreach ($res as $key => $row) {
                    $res[$key]['url'] = $this->dscRepository->buildUri('brand', ['bid' => $row['brand_id']], $row['brand_name']);
                }
            }
        }

        return $res;
    }

    /**
     * 获取商品ajax属性是否都选中
     *
     * @param $goods_id
     * @param $goods_attr
     * @param $goods_attr_id
     * @return array
     */
    public function getGoodsAttrAjax($goods_id, $goods_attr, $goods_attr_id)
    {
        $arr = [];
        $arr['attr_id'] = [];
        if ($goods_attr) {
            $goods_attr = BaseRepository::getExplode($goods_attr);

            $res = GoodsAttr::whereIn('attr_id', $goods_attr)
                ->where('goods_id', $goods_id);

            if ($goods_attr_id) {
                $goods_attr_id = !is_array($goods_attr_id) ? explode(",", $goods_attr_id) : $goods_attr_id;
                $res = $res->whereIn('goods_attr_id', $goods_attr_id);
            }

            $res = $res->orderBy('attr_id')
                ->orderBy('attr_sort');

            $res = BaseRepository::getToArrayGet($res);

            if ($res) {

                $attr_id = BaseRepository::getKeyPluck($res, 'attr_id');
                $attributeList = GoodsDataHandleService::getAttributeDataList($attr_id, [1, 2], ['attr_id', 'attr_name', 'sort_order']);

                $attr_id = BaseRepository::getKeyPluck($attributeList, 'attr_id');

                if ($attr_id) {
                    $sql = [
                        'whereIn' => [
                            [
                                'name' => 'attr_id',
                                'value' => $attr_id
                            ]
                        ]
                    ];
                    $res = BaseRepository::getArraySqlGet($res, $sql);
                } else {
                    $res = [];
                }

                if ($res) {
                    foreach ($res as $key => $row) {
                        $attribute = $attributeList[$row['attr_id']] ?? [];

                        $res[$key]['sort_order'] = $attribute['sort_order'] ?? 0;
                        $res[$key]['attr_id'] = $attribute['attr_id'] ?? 0;
                        $res[$key]['attr_name'] = $attribute['attr_name'] ?? '';
                    }

                    $res = BaseRepository::getSortBy($res, 'sort_order');

                    $arr['attr_id'] = BaseRepository::getKeyPluck($res, 'attr_id');

                    foreach ($res as $key => $row) {
                        $arr[$row['attr_id']][$row['goods_attr_id']] = $row;
                    }
                }
            }
        }

        return $arr;
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
     * 筛选获取分类/品牌/商品ID下的商品数量
     *
     * @param array $filter
     * @param int $size
     * @return int
     */
    public function getFilterGoodsListCount($filter = ['goods_ids' => '', 'cat_ids' => '', 'brand_ids' => '', 'user_id' => 0, 'mer_ids' => ''], $size = 0)
    {
        $res = Goods::where('is_on_sale', 1)
            ->where('is_alone_sale', 1)
            ->where('is_delete', 0)
            ->where('is_show', 1);

        //商品
        if (isset($filter['goods_ids']) && !empty($filter['goods_ids'])) {
            $goods_ids = !is_array($filter['goods_ids']) ? explode(",", $filter['goods_ids']) : $filter['goods_ids'];

            $res = $res->whereIn('goods_id', $goods_ids);
        }

        //分类
        if (isset($filter['cat_ids']) && !empty($filter['cat_ids'])) {
            $cat_list = explode(',', $filter['cat_ids']);

            $cat_ids = [];
            foreach ($cat_list as $key => $val) {
                $cat_ids[] = $val;

                $cat_keys = app(CategoryService::class)->getCatListChildren($val);

                $cat_ids = array_merge($cat_ids, $cat_keys);
            }

            $cat_ids = array_unique($cat_ids);

            $res = $res->whereIn('cat_id', $cat_ids);
        }

        //品牌
        if (isset($filter['brand_ids']) && !empty($filter['brand_ids'])) {
            $brand_ids = !is_array($filter['brand_ids']) ? explode(",", $filter['brand_ids']) : $filter['brand_ids'];

            $res = $res->whereIn('brand_id', $brand_ids);
        }

        //商家
        if (isset($filter['user_id'])) {
            $res = $res->where('user_id', $filter['user_id']);
        }

        if ($size > 0) {
            $res = $res->take($size);

            $res = $res->get();

            $res = $res ? $res->toArray() : [];

            $count = 0;
            if ($res) {
                $count = collect($res)->count();
            }
        } else {
            $count = $res->count();
        }

        return $count;
    }

    /**
     * 筛选获取分类/品牌/商品ID下的商品列表
     *
     * @param array $filter
     * @param string $type
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param int $size
     * @param int $page
     * @param string $sort
     * @param string $order
     * @return array
     */
    public function getFilterGoodsList($filter = ['goods_ids' => '', 'cat_ids' => '', 'brand_ids' => '', 'user_id' => 0, 'mer_ids' => ''], $type = '', $warehouse_id = 0, $area_id = 0, $area_city = 0, $size = 10, $page = 1, $sort = "sort_order", $order = "ASC")
    {
        $user_rank = session('user_rank', 0);
        $discount = session('discount', 1);

        $res = Goods::where('is_on_sale', 1)
            ->where('is_alone_sale', 1)
            ->where('is_delete', 0)
            ->where('is_show', 1);

        //商品
        if (isset($filter['goods_ids']) && !empty($filter['goods_ids'])) {
            $goods_ids = !is_array($filter['goods_ids']) ? explode(",", $filter['goods_ids']) : $filter['goods_ids'];

            $res = $res->whereIn('goods_id', $goods_ids);
        }

        //分类
        if (isset($filter['cat_ids']) && !empty($filter['cat_ids'])) {
            $cat_list = explode(',', $filter['cat_ids']);

            $cat_ids = [];
            foreach ($cat_list as $key => $val) {
                $cat_ids[] = $val;

                $cat_keys = app(CategoryService::class)->getCatListChildren($val);

                $cat_ids = array_merge($cat_ids, $cat_keys);
            }

            $cat_ids = array_unique($cat_ids);

            $res = $res->whereIn('cat_id', $cat_ids);
        }

        //品牌
        if (isset($filter['brand_ids']) && !empty($filter['brand_ids'])) {
            $brand_ids = !is_array($filter['brand_ids']) ? explode(",", $filter['brand_ids']) : $filter['brand_ids'];

            $res = $res->whereIn('brand_id', $brand_ids);
        }

        //商家
        if (isset($filter['user_id'])) {
            $res = $res->where('user_id', $filter['user_id']);
        }

        $res = $this->dscRepository->getAreaLinkGoods($res, $area_id, $area_city);

        if (config('shop.review_goods')) {
            $res = $res->whereIn('review_status', [3, 4, 5]);
        }

        $res = $res->orderBy($sort, $order)->orderByDesc('goods_id');

        $start = ($page - 1) * $size;

        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($size > 0) {
            $res = $res->take($size);
        }

        $res = BaseRepository::getToArrayGet($res);

        //处理
        $arr = [];
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

                $arr[$row['goods_id']] = $row;

                if ($row['promote_price'] > 0) {
                    $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                } else {
                    $promote_price = 0;
                }

                $arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
                $arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
                $arr[$row['goods_id']]['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                $arr[$row['goods_id']]['goods_video'] = $this->dscRepository->getImagePath($row['goods_video']);
                $arr[$row['goods_id']]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
                $arr[$row['goods_id']]['shop_price'] = $this->dscRepository->getPriceFormat($row['shop_price']);
                $arr[$row['goods_id']]['promote_price'] = ($promote_price) > 0 ? $this->dscRepository->getPriceFormat($promote_price) : '';
            }
        }

        //总数
        if ($type == 'goods') {
            $record_count = $this->getFilterGoodsListCount($filter, $size);
        } else {
            $record_count = $this->getFilterGoodsListCount($filter);
        }

        $page_count = $record_count > 0 ? ceil($record_count / $size) : 1;

        return ['goods_list' => $arr, 'page_count' => $page_count, 'record_count' => $record_count];
    }

    /**
     * 取商品的规格列表
     *
     * @param int $goods_id
     * @return array
     */
    public function getSpecificationsList($goods_id = 0)
    {
        /* 取商品属性 */

        $result = GoodsAttr::select(['goods_attr_id', 'attr_id', 'attr_value'])
            ->where('goods_id', $goods_id);
        $result = BaseRepository::getToArrayGet($result);

        $return_array = [];
        if ($result) {

            $attr_id = BaseRepository::getKeyPluck($result, 'attr_id');
            $attributeList = GoodsDataHandleService::getAttributeDataList($attr_id, null, ['attr_id', 'attr_name']);
            $attr_id = BaseRepository::getKeyPluck($attributeList, 'attr_id');

            $sql = [
                'whereIn' => [
                    [
                        'name' => 'attr_id',
                        'value' => $attr_id
                    ]
                ]
            ];
            $result = BaseRepository::getArraySqlGet($result, $sql);

            if ($result) {
                foreach ($result as $value) {

                    $attribute = $attributeList[$value['attr_id']] ?? [];
                    $value = BaseRepository::getArrayMerge($value, $attribute);

                    $return_array[$value['goods_attr_id']] = $value;
                }
            }
        }

        return $return_array;
    }

    /**
     * 获取商品属性列表
     *
     * @param array $specs
     * @return array|string
     */
    public function getGoodsAttrList($specs = [])
    {
        if (empty($specs)) {
            return '';
        }

        $specs = BaseRepository::getExplode($specs);

        $goodsAttrList = GoodsAttr::select('goods_attr_id', 'attr_id', 'attr_value', 'attr_sort')
            ->whereIn('goods_attr_id', $specs)
            ->orderBy('goods_attr_id')
            ->orderBy('attr_sort');

        $goodsAttrList = BaseRepository::getToArrayGet($goodsAttrList);

        $list = [];
        if ($goodsAttrList) {
            $attr_id = BaseRepository::getKeyPluck($goodsAttrList, 'attr_id');
            $attributeList = GoodsDataHandleService::getAttributeDataList($attr_id, null, ['attr_id', 'attr_name']);
            $attr_id = BaseRepository::getKeyPluck($attributeList, 'attr_id');

            if ($attr_id) {
                $sql = [
                    'whereIn' => [
                        [
                            'name' => 'attr_id',
                            'value' => $attr_id
                        ]
                    ]
                ];
                $goodsAttrList = BaseRepository::getArraySqlGet($goodsAttrList, $sql);

                foreach ($attributeList as $key => $val) {
                    $list[$key]['attr_id'] = $val['attr_id'];
                    $list[$key]['attr_name'] = $val['attr_name'];

                    $sql = [
                        'where' => [
                            [
                                'name' => 'attr_id',
                                'value' => $val['attr_id']
                            ]
                        ]
                    ];
                    $goodsAttr = BaseRepository::getArraySqlGet($goodsAttrList, $sql);

                    $attr_value = BaseRepository::getKeyPluck($goodsAttr, 'attr_value');
                    $list[$key]['attr_value'] = $attr_value ? implode(',', $attr_value) : '';
                }
            }
        }

        $attr_list = [];
        $goods_attr = '';
        if ($list) {
            foreach ($list as $row) {
                $attr_list[] = $row['attr_name'] . ': ' . $row['attr_value'];
            }
            $goods_attr = join(chr(13) . chr(10), $attr_list);
        }

        return $goods_attr;
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

        $seckillTime = SeckillTimeBucket::select('id', 'begin_time')->where('begin_time', '<=', $stb_time)
            ->where('end_time', '>', $stb_time);
        $seckillTime = BaseRepository::getToArrayGet($seckillTime);

        $seckillTimeList = [];
        if ($seckillTime) {
            foreach ($seckillTime as $key => $row) {
                $seckillTimeList[$row['id']] = $row;
            }
        }

        $tb_id = BaseRepository::getKeyPluck($seckillTimeList, 'id');

        $seckill = Seckill::where('begin_time', '<=', $date_begin)
            ->where('acti_time', '>', $date_begin)
            ->where('is_putaway', 1)
            ->where('review_status', 3);
        $seckill = BaseRepository::getToArrayGet($seckill);
        $sec_id = BaseRepository::getKeyPluck($seckill, 'sec_id');

        if (empty($tb_id) || empty($sec_id)) {
            return 0;
        }

        $res = $res->whereIn('tb_id', $tb_id)
            ->whereIn('sec_id', $sec_id);

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $key => $val) {
                $bucket = $seckillTimeList[$val['tb_id']] ?? [];
                $res[$key]['begin_time'] = $bucket['begin_time'] ?? 0;
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
     * 验证属性是多选，单选
     * @param $goods_attr_id
     * @return mixed
     */
    public function getGoodsAttrType($goods_attr_id = 0)
    {
        $attr_type = Attribute::wherehas('getGoodsAttr', function ($query) use ($goods_attr_id) {
            $query->where('goods_attr_id', $goods_attr_id);
        })->value('attr_type');

        $attr_type = $attr_type ? $attr_type : 0;

        return $attr_type;
    }

    /**
     * 验证是否关联地区
     *
     * @param $goods_id
     * @param int $area_id
     * @param int $area_city
     * @return mixed
     */
    public function getHasLinkAreaGods($goods_id, $area_id = 0, $area_city = 0)
    {
        $res = Goods::where('goods_id', $goods_id);
        $res = $this->dscRepository->getAreaLinkGoods($res, $area_id, $area_city);
        $count = $res->count();

        return $count;
    }

    /**
     * 获取店铺二维码
     *
     * @param int $ru_id
     * @return mixed
     * @throws \Endroid\QrCode\Exception\InvalidPathException
     */
    public function getShopQrcode($ru_id = 0)
    {
        // 二维码内容
        $url = dsc_url('/#/shopHome/' . $ru_id);

        // 生成的文件位置
        $path = storage_public('data/attached/shophome_qrcode/');

        // 输出二维码路径
        $out_img = $path . 'shop_qrcode_' . $ru_id . '.png';

        if (!is_dir($path)) {
            @mkdir($path, 0777);
        }

        // 生成二维码条件
        $generate = false;
        if (file_exists($out_img)) {
            $lastmtime = filemtime($out_img) + 3600 * 24 * 30; // 30天有效期之后重新生成
            if (time() >= $lastmtime) {
                $generate = true;
            }
        }

        if (!file_exists($out_img) || $generate == true) {
            QRCode::png($url, $out_img);
        }

        $image_name = 'data/attached/shophome_qrcode/' . basename($out_img);

        $this->dscRepository->getOssAddFile([$image_name]);

        return $this->dscRepository->getImagePath($image_name);
    }

    /**
     * 生成商品二维码
     *
     * @param array $goods
     * @return array
     * @throws \Endroid\QrCode\Exception\QrCodeException
     */
    public function getGoodsQrcode($goods = [])
    {
        if (empty($goods)) {
            return [];
        }

        // 二维码内容
        $two_code_links = trim(config('shop.two_code_links'));
        $two_code_links = empty($two_code_links) ? url('/') . '/' : $two_code_links;
        $url = rtrim($two_code_links, '/') . '/goods.php?id=' . $goods['goods_id'];

        // 保存二维码目录
        $file_path = storage_public('images/weixin_img/');
        if (!file_exists($file_path)) {
            make_dir($file_path);
        }
        // logo目录
        $logo_path = storage_public('images/weixin_img/logo/');
        if (!file_exists($logo_path)) {
            make_dir($logo_path);
        }
        // 输出logo
        $logo = $logo_path . 'logo_' . $goods['goods_id'] . '.png';
        // 输出图片
        $out_img = $file_path . 'weixin_code_' . $goods['goods_id'] . '.png';

        // 生成二维码条件
        $generate = false;
        if (file_exists($out_img)) {
            $lastmtime = filemtime($out_img) + 3600 * 24 * 1; // 1天有效期之后重新生成
            if (time() >= $lastmtime) {
                $generate = true;
            }
        }

        if (!file_exists($out_img) || $generate == true) {
            /**
             * 生成二维码+logo
             */
            // 优先用商品缩略图
            $goods_img = !empty($goods['goods_thumb']) ? $goods['goods_thumb'] : $goods['goods_img'];
            $two_code_logo = trim(config('shop.two_code_logo'));
            if (!empty($two_code_logo)) {
                $two_code_logo = str_replace('../', '', $two_code_logo);
                $logo_picture = $this->dscRepository->getImagePath($two_code_logo);
            } else {
                $logo_picture = $this->dscRepository->getImagePath($goods_img);
            }
            if (!empty($logo_picture)) {
                $avatar_open = '';
                // 远程图片（非本站）
                if (strtolower(substr($logo_picture, 0, 4)) == 'http' && stripos($logo_picture, url('/')) === false) {

                    $logo_picture_url = $logo_picture;
                    $logo_picture = Http::doGet($logo_picture);

                    if (empty($logo_picture)) {
                        $logo_picture = file_get_contents($logo_picture_url);
                    }

                    if ($logo_picture !== false) {
                        $avatar_open = $logo;
                        file_put_contents($avatar_open, $logo_picture);
                    }
                } else {
                    // 本站图片 带http 或 不带http
                    if (strtolower(substr($logo_picture, 0, 4)) == 'http') {
                        $picture = str_replace(storage_url('/'), '', $logo_picture);
                        $picture = BaseRepository::getExplode($picture, '?');
                        $logo_picture = $picture[0] ?? '';
                    }

                    // 默认图片
                    if (stripos($logo_picture, 'no_image') !== false) {
                        $avatar_open = $logo_picture;
                    } else {
                        $avatar_open = storage_public($logo_picture);
                    }
                }

                if (file_exists($avatar_open)) {
                    Image::open($avatar_open)->thumb(36, 36, Image::THUMB_FILLED)->save($logo);
                }
            }

            $linkExists = $this->dscRepository->remoteLinkExists($logo);

            if (!$linkExists) {
                $logo = null;
            }

            // 生成二维码
            QRCode::png($url, $out_img, $logo);
        }

        $image_name = 'images/weixin_img/' . basename($out_img);

        $link_image_name = $this->dscRepository->getImagePath($image_name);
        if (config('shop.open_oss') == 1 && !$this->dscRepository->remoteLinkExists($link_image_name)) {
            // 同步镜像上传到OSS
            $this->dscRepository->getOssAddFile([$image_name]);
        }

        return [
            'url' => $this->dscRepository->getImagePath($image_name) . '?v=' . StrRepository::random(32)
        ];
    }

    /**
     * 生成购买分销商h5链接 二维码
     *
     * @param int $user_id
     * @return mixed
     * @throws \Endroid\QrCode\Exception\QrCodeException
     */
    public function getVipRegisterQrcode($user_id = 0)
    {
        // 二维码内容
        $url = dsc_url('/#/drp/register') . '?' . http_build_query(['parent_id' => $user_id], '', '&');

        // 生成的文件位置
        $file_path = storage_public('data/attached/drp_register/');
        if (!file_exists($file_path)) {
            make_dir($file_path);
        }

        $avatar_file = storage_public('data/attached/avatar/');
        if (!file_exists($avatar_file)) {
            make_dir($avatar_file);
        }

        // 用户头像
        $avatar = $avatar_file . 'avatar_' . $user_id . '.png';
        // 输出二维码路径
        $out_img = $file_path . 'drp_register_' . $user_id . '.png';

        // 生成二维码条件
        $generate = false;
        if (file_exists($out_img)) {
            $lastmtime = filemtime($out_img) + 3600 * 24 * 30; // 30天有效期之后重新生成
            if (time() >= $lastmtime) {
                $generate = true;
            }
        }

        if (!file_exists($out_img) || $generate == true) {
            $users = Users::select('user_picture')->where('user_id', $user_id)->first();
            $users = $users ? $users->toArray() : [];

            // 生成二维码+微信头像
            $user_picture = empty($users['user_picture']) ? public_path('img/user_default.png') : $this->dscRepository->getImagePath($users['user_picture']);
            // 生成微信头像缩略图
            if (!empty($user_picture)) {
                // 远程图片（非本站）
                if (strtolower(substr($user_picture, 0, 4)) == 'http' && strpos($user_picture, url('/')) === false) {

                    $user_picture_url = $user_picture;
                    $user_picture = Http::doGet($user_picture);

                    if (empty($user_picture)) {
                        $user_picture = file_get_contents($user_picture_url);
                    }

                    $avatar_open = $avatar;
                    file_put_contents($avatar_open, $user_picture);
                } else {
                    // 本站图片 带http 或 不带http
                    if (strtolower(substr($user_picture, 0, 4)) == 'http') {
                        $user_picture = str_replace(storage_url('/'), '', $user_picture);
                    }
                    // 默认图片
                    if (strpos($user_picture, 'user_default') !== false || strpos($user_picture, 'no_image') !== false) {
                        $avatar_open = $user_picture;
                    } else {
                        $avatar_open = storage_public($user_picture);
                    }
                }

                if (file_exists($avatar_open)) {
                    Image::open($avatar_open)->thumb(60, 60, Image::THUMB_FILLED)->save($avatar);
                }
            }

            QRCode::png($url, $out_img, $avatar, 200);
        }

        $image_name = 'data/attached/drp_register/' . basename($out_img);

        $this->dscRepository->getOssAddFile([$image_name]);

        return $this->dscRepository->getImagePath($image_name);
    }

    /**
     * 查询商品库存
     *
     * @param int $goods_id
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param array $select
     * @return mixed
     */
    public function getGoodsStock($goods_id = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0, $select = [])
    {
        if ($select) {
            array_push($select, 'model_attr', 'goods_number');
            $select = BaseRepository::getExplode($select);
            $res = Goods::select($select)->where('goods_id', $goods_id);
        } else {
            $res = Goods::where('goods_id', $goods_id);
        }

        $where = [
            'warehouse_id' => $warehouse_id,
            'area_id' => $area_id,
            'area_city' => $area_city
        ];

        $res = $res->with([
            'getWarehouseGoods' => function ($query) use ($where) {
                $query->where('region_id', $where['warehouse_id']);
            },
            'getWarehouseAreaGoods' => function ($query) use ($where) {
                $query = $query->where('region_id', $where['area_id']);

                if (config('shop.area_pricetype') == 1) {
                    $query->where('city_id', $where['area_city']);
                }
            }
        ]);

        $res = BaseRepository::getToArrayFirst($res);

        if ($res) {
            if ($res['model_attr'] == 1) {
                $goods_number = $res['get_warehouse_goods']['region_number'] ?? 0;
            } elseif ($res['model_attr'] == 2) {
                $goods_number = $res['get_warehouse_area_goods']['region_number'] ?? 0;
            } else {
                $goods_number = $res['goods_number'];
            }

            $res['goods_number'] = $goods_number;
        }

        return $res;
    }
}
