<?php

namespace App\Services\Flow;

use App\Models\BonusType;
use App\Models\Cart;
use App\Models\FavourableActivity;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Flow\FlowRepository;
use App\Services\Cart\CartCommonService;
use App\Services\Category\CategoryService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\User\UserCommonService;

class FlowActivityService
{
    protected $dscRepository;
    protected $sessionRepository;
    private $categoryService;

    public function __construct(
        DscRepository $dscRepository,
        SessionRepository $sessionRepository,
        CategoryService $categoryService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->sessionRepository = $sessionRepository;
        $this->categoryService = $categoryService;
    }

    /**
     * 获得用户的可用积分
     *
     * @param array $cart_goods 购物车商品列表
     * @param $cart_value
     * @param $flow_type
     * @param int $user_id
     * @return float|int
     */
    public function getFlowAvailablePoints($cart_goods = [], $cart_value = [], $flow_type = CART_GENERAL_GOODS, $user_id = 0)
    {
        if (empty($cart_value) && empty($cart_goods)) {
            return 0;
        }

        if (empty($cart_goods)) {
            $session_id = $this->sessionRepository->realCartMacIp();

            $cart_value = BaseRepository::getExplode($cart_value);

            $res = Cart::select('rec_id', 'goods_id', 'goods_price', 'goods_number', 'model_attr', 'is_real', 'rec_type', 'extension_code')
                ->where('is_gift', 0)
                ->whereIn('rec_id', $cart_value)
                ->where('rec_type', $flow_type)
                ->where('is_real', '<>', 0)
                ->where('extension_code', '<>', 'virtual_card');

            if ($user_id > 0) {
                $res = $res->where('user_id', $user_id);
            } else {
                $res = $res->where('session_id', $session_id);
            }

            $res = BaseRepository::getToArrayGet($res);

            if ($res) {

                $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');
                $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'integral']);
                $warehouseGoodsList = GoodsDataHandleService::getWarehouseGoodsDataList($goods_id, 0, ['goods_id', 'region_id', 'pay_integral', 'region_id']);
                $areaGoodsList = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods_id, 0, 0, ['goods_id', 'region_id', 'city_id', 'pay_integral', 'region_id']);

                foreach ($res as $key => $row) {

                    $goods = $goodsList[$row['goods_id']] ?? [];

                    $warehouse_goods = $warehouseGoodsList[$row['goods_id']] ?? [];
                    $sql = [
                        'where' => [
                            [
                                'name' => 'region_id',
                                'value' => $row['warehouse_id']
                            ]
                        ]
                    ];
                    $warehouse_goods = BaseRepository::getArraySqlGet($warehouse_goods, $sql);

                    $areaGoods = $areaGoodsList[$row['goods_id']] ?? [];
                    $sql = [
                        'where' => [
                            [
                                'name' => 'region_id',
                                'value' => $row['area_id']
                            ]
                        ]
                    ];

                    if (config('shop.area_pricetype') == 1) {
                        $sql['where'][] = [
                            'name' => 'city_id',
                            'value' => $row['area_city']
                        ];
                    }
                    $areaGoods = BaseRepository::getArraySqlGet($areaGoods, $sql);

                    // 商品信息
                    if ($row['model_attr'] == 1) {
                        $row['integral'] = $warehouse_goods[$row['goods_id']]['pay_integral'] ?? 0;
                    } elseif ($row['model_attr'] == 2) {
                        $row['integral'] = $areaGoods[$row['goods_id']]['pay_integral'] ?? 0;
                    } else {
                        $row['integral'] = $goods['integral'] ?? 0;
                    }

                    /**
                     * 取最小兑换积分
                     */
                    $integral = [
                        $this->dscRepository->integralOfValue($row['goods_price'] * $row['goods_number']),
                        $this->dscRepository->integralOfValue($row['integral'] * $row['goods_number'])
                    ];

                    $integral = BaseRepository::getArrayMin($integral);
                    $res[$key]['integral_total'] = $this->dscRepository->valueOfIntegral($integral);
                }
            }
        } else {

            $sql = [
                'where' => [
                    [
                        'name' => 'is_gift',
                        'value' => '0'
                    ],
                    [
                        'name' => 'is_real',
                        'value' => 1
                    ],
                    [
                        'name' => 'extension_code',
                        'value' => 'virtual_card',
                        'condition' => '<>'
                    ],
                    [
                        'name' => 'parent_id',
                        'value' => 0
                    ]
                ]
            ];
            $cart_goods = BaseRepository::getArraySqlGet($cart_goods, $sql);

            $res = $cart_goods;
        }

        $integral_total = BaseRepository::getArraySum($res, 'integral_total');
        $integral_total = $this->dscRepository->getIntegralOfValue($integral_total);

        return $integral_total;
    }

    /**
     * 取得当前用户应该得到的红包总额
     *
     * @param int $user_id
     * @return float
     */
    public function getTotalBonus($user_id = 0)
    {
        $session_id = $this->sessionRepository->realCartMacIp();
        if (empty($user_id)) {
            $user_id = session('user_id', 0);
        }

        $day = TimeRepository::getLocalGetDate();
        $today = TimeRepository::getLocalMktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

        /* 按商品发的红包 */
        $goods_total = Cart::where('is_gift', 0)->where('rec_type', CART_GENERAL_GOODS);

        $where = [
            'send_type' => SEND_BY_GOODS,
            'today' => $today
        ];
        $goods_total = $goods_total->whereHasIn('getGoods', function ($query) use ($where) {
            $query->whereHasIn('getBonusType', function ($query) use ($where) {
                $query->where('send_type', $where['send_type'])
                    ->where('send_start_date', '<=', $where['today'])
                    ->where('send_end_date', '>=', $where['today']);
            });
        });

        if (!empty($user_id)) {
            $goods_total = $goods_total->where('user_id', $user_id);
        } else {
            $goods_total = $goods_total->where('session_id', $session_id);
        }

        $goods_total = $goods_total->with([
            'getGoods' => function ($query) {
                $query->bonusTypeInfo();
            }
        ]);

        $goods_total = BaseRepository::getToArrayGet($goods_total);

        $total = 0;
        if ($goods_total) {
            foreach ($goods_total as $key => $row) {
                $get_bonus_type = isset($goods_total['get_goods']['get_bonus_type']) && $goods_total['get_goods']['get_bonus_type'] ? $goods_total['get_goods']['get_bonus_type'] : [];
                $row['type_money'] = $get_bonus_type ? $get_bonus_type['type_money'] : 0;

                $total += $row['goods_number'] + $row['type_money'];
            }
        }

        $goods_total = floatval($total);

        /* 取得购物车中非赠品总金额 */
        $amount = Cart::selectRaw("SUM(goods_price * goods_number) as total")
            ->where('is_gift', 0)
            ->where('rec_type', CART_GENERAL_GOODS);

        if (!empty($user_id)) {
            $amount = $amount->where('user_id', $user_id);
        } else {
            $amount = $amount->where('session_id', $session_id);
        }

        $amount = BaseRepository::getToArrayFirst($amount);
        $amount = $amount ? $amount['total'] : 0;
        $amount = floatval($amount);

        /* 按订单发的红包 */
        $order_total = BonusType::selectRaw("FLOOR('$amount' / min_amount) * type_money")
            ->where('send_type', SEND_BY_ORDER)
            ->where('send_start_date', '<=', $today)
            ->where('send_end_date', '>=', $today)
            ->where('min_amount', '>', 0);
        $order_total = BaseRepository::getToArrayFirst($order_total);

        $order_total = isset($order_total['type_money']) ? $order_total['type_money'] : 0;
        $order_total = floatval($order_total);

        return $goods_total + $order_total;
    }

    /**
     * 重组购物车商品优惠活动
     *
     * @param array $goodsList
     * @param int $user_id
     * @return array
     * @throws \Exception
     */
    private function flowFavourableActivityList($goodsList = [], $user_id = 0)
    {
        /* 获取购物车商品 */
        $cart_goods = FlowRepository::getNewGroupCartGoods($goodsList);

        if (empty($cart_goods)) {
            return [];
        }

        if (session()->has('user_rank')) {
            $user_rank = session('user_rank', 0);
        } else {
            $userRankInfo = app(UserCommonService::class)->getUserRankByUid($user_id);
            $user_rank = $userRankInfo['rank_id'] ?? 0;
        }

        /* 获取商家ID */
        $ruList = BaseRepository::getKeyPluck($cart_goods, 'ru_id');

        /* 按商家分组 */
        $ruGoodsList = BaseRepository::getGroupBy($cart_goods, 'ru_id');

        $goodsTotal = BaseRepository::getArraySum($cart_goods, ['goods_number', 'goods_price']);

        $time = TimeRepository::getGmTime();
        $favourableList = FavourableActivity::where('start_time', '<=', $time)
            ->where('end_time', '>=', $time)
            ->whereRaw("FIND_IN_SET('$user_rank', user_rank)");

        $favourableList = $favourableList->where(function ($query) use ($ruList, $goodsTotal) {
            $query->whereIn('user_id', $ruList)
                ->orWhere(function ($query) use ($goodsTotal) {
                    $query->where('userFav_type', 1)->where('min_amount', '<=', $goodsTotal);
                });
        });

        $favourableList = BaseRepository::getToArrayGet($favourableList);

        $ruFavourableList = []; //获取购物车商品商家活动列表
        if ($favourableList) {

            //$ru_id 店铺ID
            foreach ($ruGoodsList as $ru_id => $value) {

                $sql = [
                    'whereIn' => [
                        [
                            'name' => 'userFav_type',
                            'value' => 1
                        ]
                    ]
                ];
                $userFavList = BaseRepository::getArraySqlGet($favourableList, $sql); //全场通用优惠活动

                $sql = [
                    'where' => [
                        [
                            'name' => 'user_id',
                            'value' => $ru_id
                        ]
                    ]
                ];
                $favourable = BaseRepository::getArraySqlGet($favourableList, $sql);

                $favourable = BaseRepository::getArrayMerge($userFavList, $favourable);
                $favourable = BaseRepository::getArrayUnique($favourable, 'act_id');
                /* 店铺优惠活动 end */

                if ($favourableList) {

                    $cat_id = BaseRepository::getKeyPluck($value, 'cat_id');

                    $cartIdList = FlowRepository::cartGoodsAndPackage($value);
                    $goods_id = $cartIdList['goods_id']; //普通商品ID

                    $brand_id = BaseRepository::getKeyPluck($value, 'brand_id');

                    foreach ($favourable as $k => $v) {

                        $favourable[$k] = $v;

                        $favourable[$k]['local_start_time'] = TimeRepository::getLocalDate("Y-m-d H:i:s", $v['start_time']);
                        $favourable[$k]['local_end_time'] = TimeRepository::getLocalDate("Y-m-d H:i:s", $v['end_time']);

                        $act_gift_list = [];
                        if ($v['gift']) {
                            $act_gift_list = unserialize($v['gift']);

                            $gift_goods_id = BaseRepository::getKeyPluck($act_gift_list, 'id');
                            $giftGoodsList = GoodsDataHandleService::GoodsDataList($gift_goods_id, ['goods_id', 'goods_thumb']);

                            if ($act_gift_list) {
                                foreach ($act_gift_list as $idx => $row) {
                                    $cart_gift_num = $this->goodsNumInCartGift($row['id'], $user_id, $cart_goods);
                                    $gift_goods = $giftGoodsList[$row['id']] ?? [];
                                    if ($gift_goods) {
                                        $act_gift_list[$idx]['thumb_img'] = $gift_goods['goods_thumb'] ? $this->dscRepository->getImagePath($gift_goods['goods_thumb']) : '';
                                        $act_gift_list[$idx]['ru_id'] = $v['user_id'];
                                        $act_gift_list[$idx]['act_id'] = $v['act_id'];
                                        $act_gift_list[$idx]['formated_price'] = $this->dscRepository->getPriceFormat($row['price']);
                                        $act_gift_list[$idx]['is_checked'] = $cart_gift_num ? true : false;;
                                    } else {
                                        unset($act_gift_list[$idx]);
                                    }
                                }
                            }
                        }

                        $favourable[$k]['act_gift_list'] = $act_gift_list;

                        $favourable[$k]['catIdList'] = []; //初始活动分类ID
                        $favourable[$k]['brandIdList'] = []; //初始活动品牌ID
                        $favourable[$k]['goodsIdList'] = []; //初始活动商品ID
                        if ($v['act_range'] == FAR_CATEGORY) {
                            //按分类选择
                            $favourableCatListExt = BaseRepository::getExplode($v['act_range_ext']);
                            $favourable[$k]['catIdList'] = !empty($favourableCatListExt) ? $this->categoryService->getCatListChildren($favourableCatListExt) : [];

                            /* 判断是否有商品分类符合条件 */
                            if (!empty($favourable[$k]['catIdList']) && !empty($cat_id)) {
                                $intersectCat = BaseRepository::getArrayIntersect($cat_id, $favourable[$k]['catIdList']);
                                if (empty($intersectCat)) {
                                    unset($favourable[$k]);
                                } else {
                                    $sql = [
                                        'whereIn' => [
                                            [
                                                'name' => 'cat_id',
                                                'value' => $intersectCat
                                            ]
                                        ]
                                    ];
                                    $goods_list = BaseRepository::getArraySqlGet($cart_goods, $sql);
                                    $favourable[$k]['goods_list'] = $goods_list;
                                }
                            } else {
                                unset($favourable[$k]);
                            }

                        } elseif ($v['act_range'] == FAR_BRAND) {
                            //按品牌选择
                            $favourable[$k]['brandIdList'] = BaseRepository::getExplode($v['act_range_ext']);

                            /* 判断是否有商品品牌符合条件 */
                            if (!empty($favourable[$k]['brandIdList']) && !empty($brand_id)) {
                                $intersectBrand = BaseRepository::getArrayIntersect($brand_id, $favourable[$k]['brandIdList']);
                                if (empty($intersectBrand)) {
                                    unset($favourable[$k]);
                                }
                            } else {
                                unset($favourable[$k]);
                            }

                        } elseif ($v['act_range'] == FAR_GOODS) {
                            //按商品选择
                            $favourable[$k]['goodsIdList'] = BaseRepository::getExplode($v['act_range_ext']);

                            /* 判断是否有商品品牌符合条件 */
                            if (!empty($favourable[$k]['goodsIdList']) && !empty($goods_id)) {
                                $intersectGoods = BaseRepository::getArrayIntersect($goods_id, $favourable[$k]['goodsIdList']);

                                if (empty($intersectGoods)) {
                                    unset($favourable[$k]);
                                }
                            } else {
                                unset($favourable[$k]);
                            }
                        } else {
                            if (empty($goods_id)) {
                                unset($favourable[$k]);
                            }
                        }
                    }
                }

                if ($favourable) {
                    $ruFavourableList[$ru_id] = $favourable;
                }
            }
        }

        return $ruFavourableList;
    }

    /**
     * 重组商家购物车数组[按照优惠活动对购物车商品进行分类]
     *
     * @param array $goodsList 购物车商品
     * @param int $user_id 会员ID
     * @param string $sort 排序字段
     * @return array
     * @throws \Exception
     */
    public function getFavourableCartGoodsList($goodsList = [], $user_id = 0, $sort = 'ru_id')
    {

        $session_id = '';
        if (empty($user_id)) {
            $session_id = $this->sessionRepository->realCartMacIp();
        }

        /* 店铺活动列表 */
        $ruFavourableList = $this->flowFavourableActivityList($goodsList, $user_id);

        $arr = [];
        if ($goodsList) {
            foreach ($goodsList as $key => $value) {

                if (isset($value['store_id'])) {
                    $ru_id = $value['store_id'];
                } else {
                    $ru_id = $value['ru_id'] ?? null;
                }

                $arr[$key] = $value;

                if (isset($value['goods'])) {
                    $ruCartGoods = $value['goods'];
                } else {
                    $ruCartGoods = $value['goods_list'] ?? [];
                }

                /* 获取获取活动商品分组 */
                if ($ruCartGoods) {
                    $ruFavourable = $ruFavourableList[$ru_id] ?? [];
                    $favGoodsId = []; //已存在活动中商品
                    $updateFavGoodsRec = []; //更新商品活动ID 作用：避免二次更新
                    if ($ruFavourable) {
                        $new_list = [];
                        $actList = BaseRepository::getKeyPluck($ruFavourable, 'act_id');
                        foreach ($ruFavourable as $item => $favourable) {

                            $favourable['left_gift_num'] = 0; //可领取活动商品数量
                            $favourable['act_type_ext_format'] = 0;
                            if ($favourable['act_type'] == FAT_DISCOUNT) {
                                $favourable['act_type_ext_format'] = $favourable['act_type_ext'] / 10; //换算显示折扣
                                $favourable['act_type_txt'] = lang('flow.discount');
                            } elseif ($favourable['act_type'] == FAT_PRICE) {
                                $favourable['act_type_ext_format'] = $favourable['act_type_ext']; //满减金额
                                $favourable['act_type_txt'] = lang('flow.Full_reduction');
                            } else {
                                $favourable['left_gift_num'] = intval($favourable['act_type_ext']); //可领取活动商品数量
                                $favourable['act_type_txt'] = lang('flow.With_a_gift');
                            }

                            $favourable['available'] = false; //满足活动条件
                            $favourable['cart_favourable_gift_num'] = 0; //取得购物车中已有的优惠活动及数量
                            $favourable['favourable_used'] = false; //购物车中是否已经有某优惠

                            /* 处理选择商品 */
                            $cartGoodsList = $ruCartGoods;

                            if ($favourable['act_range'] == FAR_CATEGORY) {
                                //按分类选择
                                $catIdList = $favourable['catIdList'] ?? [];

                                if (!empty($catIdList)) {
                                    $sql = [
                                        'whereIn' => [
                                            [
                                                'name' => 'cat_id',
                                                'value' => $catIdList
                                            ]
                                        ],
                                        'where' => [
                                            [
                                                'name' => 'extension_code',
                                                'value' => 'package_buy',
                                                'condition' => '<>'
                                            ],
                                            [
                                                'name' => 'is_gift',
                                                'value' => 0
                                            ],
                                            [
                                                'name' => 'parent_id',
                                                'value' => 0
                                            ]
                                        ]
                                    ];
                                    $cartGoodsList = BaseRepository::getArraySqlGet($cartGoodsList, $sql);
                                } else {
                                    $cartGoodsList = [];
                                }
                            } elseif ($favourable['act_range'] == FAR_BRAND) {
                                //按品牌选择
                                $favourableBrandListExt = $favourable['brandIdList'];

                                if (!empty($favourableBrandListExt)) {
                                    $sql = [
                                        'whereIn' => [
                                            [
                                                'name' => 'brand_id',
                                                'value' => $favourableBrandListExt
                                            ]
                                        ],
                                        'where' => [
                                            [
                                                'name' => 'extension_code',
                                                'value' => 'package_buy',
                                                'condition' => '<>'
                                            ],
                                            [
                                                'name' => 'is_gift',
                                                'value' => 0
                                            ],
                                            [
                                                'name' => 'parent_id',
                                                'value' => 0
                                            ]
                                        ]
                                    ];
                                    $cartGoodsList = BaseRepository::getArraySqlGet($cartGoodsList, $sql);
                                } else {
                                    $cartGoodsList = [];
                                }

                            } elseif ($favourable['act_range'] == FAR_GOODS) {
                                //按商品选择
                                $favourableGoodsListExt = $favourable['goodsIdList'];

                                if (!empty($favourableGoodsListExt)) {
                                    $sql = [
                                        'whereIn' => [
                                            [
                                                'name' => 'goods_id',
                                                'value' => $favourableGoodsListExt
                                            ]
                                        ],
                                        'where' => [
                                            [
                                                'name' => 'extension_code',
                                                'value' => 'package_buy',
                                                'condition' => '<>'
                                            ],
                                            [
                                                'name' => 'is_gift',
                                                'value' => 0
                                            ],
                                            [
                                                'name' => 'parent_id',
                                                'value' => 0
                                            ]
                                        ]
                                    ];
                                    $cartGoodsList = BaseRepository::getArraySqlGet($cartGoodsList, $sql);
                                } else {
                                    $cartGoodsList = [];
                                }
                            } else {
                                $sql = [
                                    'where' => [
                                        [
                                            'name' => 'extension_code',
                                            'value' => 'package_buy',
                                            'condition' => '<>'
                                        ],
                                        [
                                            'name' => 'is_gift',
                                            'value' => 0
                                        ],
                                        [
                                            'name' => 'parent_id',
                                            'value' => 0
                                        ]
                                    ]
                                ];
                                $cartGoodsList = BaseRepository::getArraySqlGet($cartGoodsList, $sql);
                            }

                            if ($cartGoodsList) {
                                foreach ($cartGoodsList as $k => $v) {

                                    /* 当商品活动不满足条件时，初始化商品活动ID start */
                                    if (!empty($v['act_id']) && !empty($actList) && !in_array($v['act_id'], $actList)) {
                                        $v['act_id'] = 0;
                                    }

                                    if ($favourable['act_range'] == FAR_CATEGORY) {
                                        if (empty($favourable['catIdList']) || !in_array($v['cat_id'], $favourable['catIdList'])) {
                                            $v['act_id'] = 0;
                                        }
                                    } elseif ($favourable['act_range'] == FAR_BRAND) {
                                        if (empty($favourable['brandIdList']) || !in_array($v['brand_id'], $favourable['brandIdList'])) {
                                            $v['act_id'] = 0;
                                        }
                                    } elseif ($favourable['act_range'] == FAR_GOODS) {
                                        if (empty($favourable['goodsIdList']) || !in_array($v['goods_id'], $favourable['goodsIdList'])) {
                                            $v['act_id'] = 0;
                                        }
                                    }
                                    /* 当商品活动不满足条件时，初始化商品活动ID end */

                                    $sql = [
                                        'where' => [
                                            [
                                                'name' => 'act_range',
                                                'value' => FAR_CATEGORY
                                            ]
                                        ]
                                    ];
                                    $catFavourableList = BaseRepository::getArraySqlGet($ruFavourable, $sql, 1); //分类筛选活动
                                    $catFavourableList = $this->actRangeCat($v['cat_id'], $catFavourableList);

                                    $sql = [
                                        'where' => [
                                            [
                                                'name' => 'act_range',
                                                'value' => FAR_BRAND
                                            ]
                                        ]
                                    ];

                                    $brandFavourableList = BaseRepository::getArraySqlGet($ruFavourable, $sql, 1); //品牌筛选活动
                                    $brandFavourableList = $this->actRangeBrand($v['brand_id'], $brandFavourableList);

                                    $sql = [
                                        'where' => [
                                            [
                                                'name' => 'act_range',
                                                'value' => FAR_GOODS
                                            ]
                                        ]
                                    ];
                                    $goodsFavourableList = BaseRepository::getArraySqlGet($ruFavourable, $sql, 1);  //商品筛选活动
                                    $goodsFavourableList = $this->actRangeGoods($v['goods_id'], $goodsFavourableList);

                                    $sql = [
                                        'where' => [
                                            [
                                                'name' => 'act_range',
                                                'value' => FAR_ALL
                                            ]
                                        ]
                                    ];
                                    $allGoodsFavourableList = BaseRepository::getArraySqlGet($ruFavourable, $sql, 1);  //全部商品筛选活动

                                    $favourable_list = ArrRepository::getArrCollapse([$catFavourableList, $brandFavourableList, $goodsFavourableList, $allGoodsFavourableList]);
                                    $favourable_list = BaseRepository::getArrayUnique($favourable_list, 'act_id');

                                    $favList = [];
                                    if ($favourable_list) {
                                        foreach ($favourable_list as $fkey => $fval) {
                                            $favList[$fval['act_id']] = $fval;
                                        }
                                    }

                                    if ((empty($updateFavGoodsRec) && empty($v['act_id'])) || (!empty($updateFavGoodsRec) && empty($v['act_id']) && !in_array($v['rec_id'], $updateFavGoodsRec))) {
                                        $cartGoodsList[$k]['act_id'] = $favourable['act_id'];
                                        $this->updateFavourableIdCartGoods($v['rec_id'], $favourable['act_id'], $user_id);

                                        $updateFavGoodsRec[] = $v['rec_id']; //赋值判断 作用：避免二次更新
                                    }

                                    $cartGoodsList[$k]['favourable_list'] = $favList ? array_values($favList) : [];
                                }

                                /* 处理全部商品 */
                                if ($favourable['act_range'] == FAR_ALL) {
                                    $sql = [
                                        'where' => [
                                            [
                                                'name' => 'act_id',
                                                'value' => $favourable['act_id']
                                            ],
                                            [
                                                'name' => 'is_gift',
                                                'value' => 0
                                            ],
                                            [
                                                'name' => 'parent_id',
                                                'value' => 0
                                            ]
                                        ]
                                    ];
                                    $cartGoodsList = BaseRepository::getArraySqlGet($cartGoodsList, $sql);
                                }
                            }

                            $sql = [
                                'where' => [
                                    [
                                        'name' => 'is_checked',
                                        'value' => 1
                                    ]
                                ]
                            ];

                            $cartGoodsListSum = BaseRepository::getArraySqlGet($cartGoodsList, $sql);

                            $favourable['cart_fav_amount'] = BaseRepository::getArraySum($cartGoodsListSum, ['goods_price', 'goods_number']); //活动商品金额

                            if ($favourable['cart_fav_amount'] >= $favourable['min_amount']) {
                                $favourable['act_goods_list'] = $cartGoodsList;
                                $favourable['available'] = true;
                            }

                            /* 折扣金额 */
                            if ($favourable['act_type'] == FAT_DISCOUNT) {
                                $goods_fav_amount = $favourable['cart_fav_amount'] * floatval(100 - intval($favourable['act_type_ext'])) / 100;
                                $favourable['goods_fav_amount'] = $this->dscRepository->changeFloat($goods_fav_amount);
                                $favourable['goods_fav_amount_format'] = $this->dscRepository->getPriceFormat($favourable['goods_fav_amount']);
                            }

                            /* 获取赠品 */
                            $act_gift_list = $favourable['act_gift_list'] ?? [];
                            $favourable['act_cart_gift'] = [];
                            if ($favourable['act_type'] == FAT_GOODS && $favourable['available'] == true) {
                                if ($act_gift_list) {
                                    $giftListId = BaseRepository::getKeyPluck($act_gift_list, 'id');

                                    $actGiftPriceList = BaseRepository::getColumn($act_gift_list, 'price', 'id');

                                    if ($giftListId) {
                                        $sql = [
                                            'whereIn' => [
                                                [
                                                    'name' => 'goods_id',
                                                    'value' => $giftListId
                                                ]
                                            ],
                                            'where' => [
                                                [
                                                    'name' => 'extension_code',
                                                    'value' => 'package_buy',
                                                    'condition' => '<>'
                                                ],
                                                [
                                                    'name' => 'is_gift',
                                                    'value' => $favourable['act_id']
                                                ],
                                                [
                                                    'name' => 'parent_id',
                                                    'value' => 0
                                                ]
                                            ]
                                        ];

                                        if ($user_id > 0) {
                                            $sql['where'][] = [
                                                'name' => 'user_id',
                                                'value' => $user_id
                                            ];
                                        } else {

                                            $sql['where'][] = [
                                                'name' => 'session_id',
                                                'value' => $session_id
                                            ];
                                        }

                                        $favourable['act_cart_gift'] = BaseRepository::getArraySqlGet($ruCartGoods, $sql);
                                        $favourable['act_cart_gift'] = $this->cartGiftPriceList($actGiftPriceList, $favourable['act_cart_gift'], $favourable['act_id'], $user_id, $ruCartGoods);
                                    }
                                }
                            } else {

                                /* 删除赠品 */
                                if ($act_gift_list) {
                                    $this->delCartFavourableGift($favourable['act_id'], $user_id, $session_id);
                                }
                            }

                            $favourable['left_gift_num'] = 0;
                            if ($favourable['act_type'] == FAT_GOODS) {
                                $favourable['cart_favourable_gift_num'] = $act_gift_list ? BaseRepository::getArrayCount($favourable['act_cart_gift']) : 0;
                                $favourable['favourable_used'] = $favourable['cart_favourable_gift_num'] > 0 ? true : false;

                                $act_type_ext = intval($favourable['act_type_ext']);

                                if ($act_type_ext >= $favourable['cart_favourable_gift_num']) {
                                    $favourable['left_gift_num'] = $act_type_ext - $favourable['cart_favourable_gift_num'];
                                }

                                $favourable['act_type_ext'] = $act_type_ext;
                            } else {
                                $favourable['cart_favourable_gift_num'] = 0;
                                $favourable['favourable_used'] = false;
                            }


                            /* 获取已在活动里面的商品ID */
                            $favGoodsId[] = BaseRepository::getKeyPluck($cartGoodsList, 'goods_id');

                            $actIdList = BaseRepository::getKeyPluck($cartGoodsList, 'act_id');

                            if (in_array($favourable['act_id'], $actIdList)) {

                                if (isset($favourable['gift'])) {
                                    unset($favourable['gift']);
                                }

                                $sql = [
                                    'where' => [
                                        [
                                            'name' => 'act_id',
                                            'value' => $favourable['act_id']
                                        ]
                                    ]
                                ];
                                $cartGoodsList = BaseRepository::getArraySqlGet($cartGoodsList, $sql);

                                $favourable['act_goods_list'] = $cartGoodsList;
                                $favourable['act_goods_list_num'] = BaseRepository::getArrayCount($cartGoodsList);
                                $new_list[$item] = $favourable;
                            }
                        }

                        $arr[$key]['new_list'] = $new_list ? array_values($new_list) : [];
                    }

                    if (isset($arr[$key]['new_list']) && !empty($arr[$key]['new_list'])) {
                        $notFavGoodsId = ArrRepository::getArrCollapse($favGoodsId);
                        $sql = [
                            'whereNotIn' => [
                                [
                                    'name' => 'goods_id',
                                    'value' => $notFavGoodsId
                                ]
                            ],
                            'where' => [
                                [
                                    'name' => 'is_gift',
                                    'value' => 0
                                ],
                                [
                                    'name' => 'parent_id',
                                    'value' => 0
                                ]
                            ]
                        ];

                        if ($user_id > 0) {
                            $sql['where'][] = [
                                'name' => 'user_id',
                                'value' => $user_id
                            ];
                        } else {

                            $sql['where'][] = [
                                'name' => 'session_id',
                                'value' => $session_id
                            ];
                        }

                        $notFavGoodsList = BaseRepository::getArraySqlGet($ruCartGoods, $sql);

                        $arr[$key]['new_list'][] = [
                            'act_goods_list' => $notFavGoodsList,
                            'act_goods_list_num' => BaseRepository::getArrayCount($notFavGoodsList)
                        ];

                        $arr[$key]['new_list'] = $arr[$key]['new_list'] ? array_values($arr[$key]['new_list']) : [];
                    }

                    /* 店铺商品没有满足活动情况下 */
                    if (empty($ruFavourable)) {

                        $giftIdList = BaseRepository::getKeyPluck($ruCartGoods, 'is_gift');
                        $giftIdList = ArrRepository::getArrayUnset($giftIdList);

                        /* 删除赠品 */
                        if ($giftIdList) {
                            $this->delCartFavourableGift($giftIdList, $user_id, $session_id);
                        }

                        $sql = [
                            'where' => [
                                [
                                    'name' => 'is_gift',
                                    'value' => 0
                                ],
                                [
                                    'name' => 'parent_id',
                                    'value' => 0
                                ]
                            ]
                        ];

                        if ($user_id > 0) {
                            $sql['where'][] = [
                                'name' => 'user_id',
                                'value' => $user_id
                            ];
                        } else {

                            $sql['where'][] = [
                                'name' => 'session_id',
                                'value' => $session_id
                            ];
                        }

                        $ruCartGoods = BaseRepository::getArraySqlGet($ruCartGoods, $sql); //过滤赠品商品

                        $arr[$key]['new_list'][0]['act_goods_list'] = $ruCartGoods;
                        $arr[$key]['new_list'][0]['act_goods_list_num'] = BaseRepository::getArrayCount($ruCartGoods);
                    }
                }

                if ($arr[$key]['new_list']) {
                    foreach ($arr[$key]['new_list'] as $nkey => $nrow) {
                        if (isset($nrow['act_goods_list']) && empty($nrow['act_goods_list'])) {
                            unset($arr[$key]['new_list'][$nkey]);
                        }
                    }

                    $arr[$key]['new_list'] = isset($arr[$key]['new_list']) && !empty($arr[$key]['new_list']) ? array_values($arr[$key]['new_list']) : [];
                }
            }
        }

        $merchant_goods = app(CartCommonService::class)->merchantGoodsListData($arr);
        $merchant_goods = BaseRepository::getSortBy($merchant_goods, $sort);

        return $merchant_goods;
    }

    /**
     * 处理组合购买商品
     *
     * @param array $cart_goods
     * @return array
     */
    public function merchantActivityCartGoodsList($cart_goods = [])
    {
        if ($cart_goods) {

            foreach ($cart_goods as $key => $row) {

                $cart_goods[$key] = $row;
                $new_list = $row['new_list'] ?? [];

                $goodsList = [];
                $giftList = [];
                foreach ($new_list as $k => $v) {

                    $cart_goods[$key]['new_list'][$k] = $v;
                    $act_goods_list = $v['act_goods_list'] ?? []; //活动商品
                    $cart_gift = $v['act_cart_gift'] ?? []; //活动商品

                    $group_id = BaseRepository::getKeyPluck($act_goods_list, 'group_id');

                    if (!empty($group_id)) {
                        $arr = [];
                        foreach ($act_goods_list as $idx => $value) {

                            $parts = $value['parts'] ?? [];
                            if (!empty($parts)) {
                                $act_goods = [[$value], $value['parts']];
                                $act_goods = ArrRepository::getArrCollapse($act_goods);
                                $arr[$idx] = $act_goods;
                            }
                        }

                        $arr = ArrRepository::getArrCollapse($arr);
                        $act_goods_list = BaseRepository::getArrayMerge($act_goods_list, $arr);
                        $act_goods_list = BaseRepository::getArrayUnique($act_goods_list, 'rec_id');
                        $act_goods_list = BaseRepository::getSortBy($act_goods_list, 'group_id');
                    }

                    if ($act_goods_list) {
                        $goodsList[$key][$k] = $act_goods_list;
                    }

                    if ($cart_gift) {
                        $giftList[$key][$k] = $cart_gift;
                    }

                    $cart_goods[$key]['new_list'][$k]['act_goods_list'] = $act_goods_list;
                }

                $goods = ArrRepository::getArrCollapse($goodsList);
                $goods = ArrRepository::getArrCollapse($goods);

                $act_gift = ArrRepository::getArrCollapse($giftList);
                $act_gift = ArrRepository::getArrCollapse($act_gift);

                if ($act_gift) {
                    $goods = ArrRepository::getArrCollapse([$goods, $act_gift]);
                }

                $cart_goods[$key]['goods'] = array_values($goods);
                $cart_goods[$key]['goods_count'] = BaseRepository::getArrayCount($cart_goods[$key]['goods']);
            }
        }

        return $cart_goods;
    }

    /**
     * 根据商品条件获取
     *
     * @param int $goods_id 商品ID
     * @param array $goodsFavourableList 活动列表
     * @return array
     */
    private function actRangeGoods($goods_id = 0, $goodsFavourableList = [])
    {
        $arr = [];
        if ($goodsFavourableList) {
            foreach ($goodsFavourableList as $key => $row) {
                if (!empty($row['goodsIdList']) && in_array($goods_id, $row['goodsIdList'])) {
                    $arr[$key] = $row;
                }
            }
        }

        $arr = $arr ? array_values($arr) : [];

        return $arr;
    }

    /**
     * 根据品牌条件获取
     *
     * @param int $brand_id 商品品牌
     * @param array $brandFavourableList 活动列表
     * @return array
     */
    private function actRangeBrand($brand_id = 0, $brandFavourableList = [])
    {
        $arr = [];
        if ($brandFavourableList) {
            foreach ($brandFavourableList as $key => $row) {
                if (!empty($row['brandIdList']) && in_array($brand_id, $row['brandIdList'])) {
                    $arr[$key] = $row;
                }
            }
        }

        $arr = $arr ? array_values($arr) : [];

        return $arr;
    }

    /**
     * 根据品牌条件获取
     *
     * @param int $cat_id 商品分类
     * @param array $catFavourableList 活动列表
     * @return array
     */
    private function actRangeCat($cat_id = 0, $catFavourableList = [])
    {
        $arr = [];
        if ($catFavourableList) {
            foreach ($catFavourableList as $key => $row) {
                if (!empty($row['catIdList']) && in_array($cat_id, $row['catIdList'])) {
                    $arr[$key] = $row;
                }
            }
        }

        $arr = $arr ? array_values($arr) : [];

        return $arr;
    }

    /**
     * 更新购物车活动ID
     *
     * @param int $rec_id
     * @param int $act_id
     * @param int $user_id
     */
    private function updateFavourableIdCartGoods($rec_id = 0, $act_id = 0, $user_id = 0)
    {
        if ($rec_id > 0 && $act_id > 0) {
            $update = Cart::where('rec_id', $rec_id)
                ->where('parent_id', 0);

            if ($user_id > 0) {
                $update = $update->where('user_id', $user_id);
            } else {
                $session_id = $this->sessionRepository->realCartMacIp();
                $update = $update->where('session_id', $session_id);
            }

            $update->update([
                'act_id' => $act_id
            ]);
        }
    }

    /**
     * 查询购物车商赠品数量
     *
     * @param int $goods_id 赠品商品ID
     * @param int $user_id 会员ID
     * @param array $cart_goods 购物车商品
     * @return int
     */
    private function goodsNumInCartGift($goods_id = 0, $user_id = 0, $cart_goods = [])
    {
        $goods_number = 0;
        if ($cart_goods) {
            $sql = [
                'where' => [
                    [
                        'name' => 'goods_id',
                        'value' => $goods_id
                    ],
                    [
                        'name' => 'is_gift',
                        'value' => 0,
                        'condition' => '>'
                    ],
                    [
                        'name' => 'parent_id',
                        'value' => 0
                    ]
                ]
            ];

            if ($user_id > 0) {
                $sql['where'][] = [
                    'name' => 'user_id',
                    'value' => $user_id
                ];
            } else {

                $session_id = $this->sessionRepository->realCartMacIp();

                $sql['where'][] = [
                    'name' => 'session_id',
                    'value' => $session_id
                ];
            }

            $cart_goods = BaseRepository::getArraySqlGet($cart_goods, $sql);
            $goods_number = BaseRepository::getArraySum($cart_goods, 'goods_number');
        }

        return $goods_number;
    }

    /**
     * 处理优惠活动赠品商品的金额
     *
     * @param array $actGiftPriceList
     * @param array $actCartGiftList
     * @param int $act_id
     * @param int $user_id
     * @param array $ruCartGoods
     * @return array
     */
    private function cartGiftPriceList($actGiftPriceList = [], $actCartGiftList = [], $act_id = 0, $user_id = 0, $ruCartGoods = [])
    {
        $session_id = '';
        if (empty($user_id)) {
            $session_id = $this->sessionRepository->realCartMacIp();
        }

        $updateGiftList = [];
        if ($actGiftPriceList) {

            $gift_goods_id = BaseRepository::getArrayKeys($actGiftPriceList);

            if (empty($gift_goods_id)) {
                /* 删除赠品 */
                $this->delCartFavourableGift($act_id, $user_id, $session_id);
            } else {
                $sql = [
                    'where' => [
                        [
                            'name' => 'is_gift',
                            'value' => $act_id
                        ],
                        [
                            'name' => 'parent_id',
                            'value' => 0
                        ]
                    ],
                    'whereNotIn' => [
                        [
                            'name' => 'goods_id',
                            'value' => $gift_goods_id
                        ]
                    ]
                ];

                if ($user_id > 0) {
                    $sql['where'][] = [
                        'name' => 'user_id',
                        'value' => $user_id
                    ];
                } else {

                    $sql['where'][] = [
                        'name' => 'session_id',
                        'value' => $session_id
                    ];
                }

                $ruCartGoodsGiftList = BaseRepository::getArraySqlGet($ruCartGoods, $sql);

                if (!empty($ruCartGoodsGiftList)) {
                    /* 删除赠品 */
                    $delGiftGoodsId = BaseRepository::getKeyPluck($ruCartGoodsGiftList, 'goods_id');
                    $this->delCartFavourableGift($act_id, $user_id, $session_id, $delGiftGoodsId);
                }
            }

            $actCartGiftPriceList = BaseRepository::getColumn($actCartGiftList, 'goods_price', 'goods_id');

            foreach ($actGiftPriceList as $key => $value) {

                $actCartGiftPrice = $actCartGiftPriceList[$key] ?? [];

                if ($actCartGiftPrice) {
                    $giftPrice = $this->dscRepository->changeFloat($value);
                    $cartGiftPrice = $this->dscRepository->changeFloat($actCartGiftPrice);

                    if ($giftPrice != $cartGiftPrice) {
                        $update = Cart::where('is_gift', $act_id)->where('goods_id', $key);

                        if ($user_id > 0) {
                            $update = $update->where('user_id', $user_id);
                        } else {
                            $update = $update->where('session_id', $session_id);
                        }

                        $res = $update->update([
                            'goods_price' => $giftPrice
                        ]);

                        if ($res > 0) {
                            $updateGiftList[$key]['price'] = $giftPrice;
                        }
                    }
                }
            }
        }

        /* 产生更新时重新赋值 */
        if (!empty($updateGiftList)) {
            foreach ($actCartGiftList as $k => $v) {
                $goods_price = $updateGiftList[$v['goods_id']]['price'] ?? 0;
                $actCartGiftList[$k]['goods_price'] = $goods_price;
                $actCartGiftList[$k]['goods_price_format'] = $this->dscRepository->getPriceFormat($goods_price);

                $subtotal = $goods_price * $v['goods_number'];
                $actCartGiftList[$k]['subtotal'] = $subtotal;
                $actCartGiftList[$k]['formated_subtotal'] = $this->dscRepository->getPriceFormat($subtotal);
            }
        }

        return $actCartGiftList;
    }

    /**
     * 删除赠品
     *
     * @param int $act_id 活动ID
     * @param int $user_id 会员ID
     * @param string $session_id
     * @param int $goods_id 指定赠品商品ID
     */
    private function delCartFavourableGift($act_id = 0, $user_id = 0, $session_id = '', $goods_id = 0)
    {
        if ($act_id > 0) {
            $act_id = BaseRepository::getExplode($act_id);
            $delCart = Cart::whereIn('is_gift', $act_id);

            if ($user_id > 0) {
                $delCart = $delCart->where('user_id', $user_id);
            } else {
                $delCart = $delCart->where('session_id', $session_id);
            }

            if (!empty($goods_id)) {
                $goods_id = BaseRepository::getExplode($goods_id);
                $delCart = $delCart->whereIn('goods_id', $goods_id);
            }

            $delCart->delete();
        }
    }
}
