<?php

use App\Models\AuctionLog;
use App\Models\Category;
use App\Models\FavourableActivity;
use App\Models\Goods;
use App\Models\GoodsCat;
use App\Models\GoodsExtend;
use App\Models\GroupGoods;
use App\Models\MerchantsGrade;
use App\Models\MerchantsShopBrand;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Category\CategoryService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Merchant\MerchantCommonService;
use App\Repositories\Common\TimeRepository;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\User\UserDataHandleService;

/**
 * 商品推荐usort用自定义排序行数
 *
 * @param $goods_a
 * @param $goods_b
 * @return int
 */
function goods_sort($goods_a, $goods_b)
{
    if ($goods_a['sort_order'] == $goods_b['sort_order']) {
        return 0;
    }
    return ($goods_a['sort_order'] < $goods_b['sort_order']) ? -1 : 1;
}

/**
 * 调用当前分类的销售排行榜
 *
 * @param int $cats
 * @param string $presale
 * @param int $ru_id
 * @param int $warehouse_id
 * @param int $area_id
 * @param int $area_city
 * @return mixed
 */
function get_top10($cats = 0, $presale = '', $ru_id = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0)
{
    $CategoryRep = app(CategoryService::class);

    $children = [];
    if (!empty($cats)) {
        $children = $CategoryRep->getCatListChildren($cats);
    }

    /* 查询扩展分类数据 */
    $extension_goods = [];
    if ($children) {
        $extension_goods = GoodsCat::select('goods_id')->whereIn('cat_id', $children);
        $extension_goods = BaseRepository::getToArrayGet($extension_goods);
        $extension_goods = BaseRepository::getFlatten($extension_goods);
    }

    $time = TimeRepository::getGmTime();

    $goodsParam = [
        'children' => $children,
        'extension_goods' => $extension_goods,
        'order_status' => [OS_CONFIRMED, OS_SPLITED],
        'pay_status' => [PS_PAYED, PS_PAYING],
        'shipping_status' => [SS_SHIPPED, SS_RECEIVED],
        'cfg_top' => $GLOBALS['_CFG']['top10_time'],
        'top_time' => [
            'one_year' => TimeRepository::getLocalDate('Ymd', $time - 365 * 86400),
            'half_year' => TimeRepository::getLocalDate('Ymd', $time - 180 * 86400),
            'three_month' => TimeRepository::getLocalDate('Ymd', $time - 90 * 86400),
            'one_month' => TimeRepository::getLocalDate('Ymd', $time - 30 * 86400)
        ]
    ];

    $arr = Goods::where('is_on_sale', 1)
        ->where('is_alone_sale', 1)
        ->where('is_delete', 0)
        ->where('is_show', 1)
        ->where(function ($query) use ($goodsParam) {
            if ($goodsParam['children']) {
                $query = $query->whereIn('cat_id', $goodsParam['children']);
            }

            if ($goodsParam['extension_goods']) {
                $query->orWhere(function ($query) use ($goodsParam) {
                    $query->whereIn('goods_id', $goodsParam['extension_goods']);
                });
            }
        });

    if (config('shop.review_goods') == 1) {
        $arr = $arr->whereIn('review_status', [3, 4, 5]);
    }

    $arr = app(DscRepository::class)->getAreaLinkGoods($arr, $area_id, $area_city);

    $arr = $arr->whereHasIn('getOrderGoods', function ($query) use ($goodsParam) {

        /* 排行统计的时间 */
        $query->whereHasIn('getOrder', function ($query) use ($goodsParam) {
            $query = $query->where('main_count', 0)
                ->whereIn('order_status', $goodsParam['order_status'])
                ->whereIn('pay_status', $goodsParam['pay_status'])
                ->whereIn('shipping_status', $goodsParam['shipping_status']);

            // 一年
            if ($goodsParam['cfg_top'] == 1) {
                $query->where('order_sn', '>=', $goodsParam['top_time']['one_year']);
            } // 半年
            elseif ($goodsParam['cfg_top'] == 2) {
                $query->where('order_sn', '>=', $goodsParam['top_time']['half_year']);
            } // 三个月
            elseif ($goodsParam['cfg_top'] == 3) {
                $query->where('order_sn', '>=', $goodsParam['top_time']['three_month']);
            } // 一个月
            elseif ($goodsParam['cfg_top'] == 4) {
                $query->where('order_sn', '>=', $goodsParam['top_time']['one_month']);
            }
        });
    });

    if ($presale == 'presale') {
        $arr = $arr->whereHasIn('getPresaleActivity', function ($query) {
            $query->where('review_status', 3);
        });
    }

    $where = [
        'area_id' => $area_id,
        'area_city' => $area_city,
        'area_pricetype' => $GLOBALS['_CFG']['area_pricetype']
    ];

    $user_rank = session('user_rank');
    $arr = $arr->with([
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
        'getOrderGoodsList'
    ]);

    if ($GLOBALS['_CFG']['top_number'] > 0) {
        $arr = $arr->take($GLOBALS['_CFG']['top_number']);
    }

    $arr = BaseRepository::getToArrayGet($arr);

    if ($arr) {
        foreach ($arr as $key => $row) {
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

            $price = app(GoodsCommonService::class)->getGoodsPrice($price, session('discount'), $row);

            $row['shop_price'] = $price['shop_price'];
            $row['promote_price'] = $price['promote_price'];
            $row['goods_number'] = $price['goods_number'];

            $arr[$key] = $row;

            $arr[$key]['short_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                app(DscRepository::class)->subStr($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
            $arr[$key]['url'] = app(DscRepository::class)->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
            $arr[$key]['goods_thumb'] = app(DscRepository::class)->getImagePath($row['goods_thumb']);

            if ($row['promote_price'] > 0) {
                $promote_price = app(GoodsCommonService::class)->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
            } else {
                $promote_price = 0;
            }

            $arr[$key]['market_price'] = app(DscRepository::class)->getPriceFormat($row['market_price']);
            $arr[$key]['shop_price'] = app(DscRepository::class)->getPriceFormat($row['shop_price']);
            $arr[$key]['promote_price'] = $promote_price > 0 ? app(DscRepository::class)->getPriceFormat($promote_price) : '';
            $arr[$key]['sales_volume'] = BaseRepository::getSum($row['get_order_goods_list'], 'goods_number');
        }

        $arr = BaseRepository::getSortBy($arr, 'sales_volume', 'desc');

        //判断是否启用库存，库存数量是否大于0
        if ($GLOBALS['_CFG']['use_storage'] == 1) {
            $arr = BaseRepository::getWhere($arr, ['str' => 'goods_number', 'estimate' => '>', 'val' => '0']);
        }
    }

    return $arr;
}

/**
 * 查找品牌
 *
 * @param int $brand_id
 * @param int $ru_id
 * @return mixed
 */
function get_goods_brand($brand_id = 0, $ru_id = 0)
{
    $res = MerchantsShopBrand::select('bid as brand_id', 'brandName as goods_brand')
        ->where('bid', $brand_id)
        ->where('user_id', $ru_id)
        ->where('audit_status', 1);

    $res = BaseRepository::getToArrayFirst($res);

    return $res;
}

/**
 * 获得商品扩展信息
 *
 * @param int $goods_id
 * @return mixed
 */
function get_goods_extends($goods_id = 0)
{
    $res = GoodsExtend::where('goods_id', $goods_id);

    $res = BaseRepository::getToArrayFirst($res);

    return $res;
}

/**
 * 获得指定分类下的商品
 *
 * @param $cat_id 分类ID
 * @param int $num 数量
 * @param string $from 来自web/wap的调用
 * @param string $order_rule 指定商品排序规则
 * @param string $return
 * @param int $warehouse_id
 * @param int $area_id
 * @param int $area_city
 * @param int $floor_sort_order
 * @return mixed
 */
function assign_cat_goods($cat_id, $num = 0, $from = 'web', $order_rule = '', $return = 'cat', $warehouse_id = 0, $area_id = 0, $area_city = 0, $floor_sort_order = 0)
{

    /* 分类信息 */
    $cat_info = Category::where('cat_id', $cat_id)
        ->where('is_show', 1);

    $cat_info = BaseRepository::getToArrayFirst($cat_info);

    $cat['name'] = $cat_info['cat_name'] ?? '';
    $cat['alias_name'] = $cat_info['cat_alias_name'] ?? '';

    $cat['url'] = app(DscRepository::class)->buildUri('category', ['cid' => $cat_id], $cat['name']);
    $cat['id'] = $cat_id;

    //获取二级分类下的商品
    $goods_index_cat1 = app(CategoryService::class)->getChildTree($cat_id);
    $goods_index_cat2 = get_cat_goods_index_cat2($cat_id, $num, $warehouse_id, $area_id, $area_city);

    $cat['goods_level2'] = array_values($goods_index_cat1);
    $cat['goods_level3'] = $goods_index_cat2;

    $cat['floor_num'] = $num;
    $cat['warehouse_id'] = $warehouse_id;
    $cat['area_id'] = $area_id;
    $cat['area_city'] = $area_city;

    $cat['floor_banner'] = 'floor_banner' . $cat_id;
    $cat['floor_sort_order'] = $floor_sort_order + 1;

    return $cat;
}

/**
 * 查询子分类
 *
 * @param int $cat_id
 * @param int $num
 * @param int $warehouse_id
 * @param int $area_id
 * @param int $area_city
 * @return array
 */
function get_cat_goods_index_cat2($cat_id = 0, $num = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0)
{
    $res = Category::where('parent_id', $cat_id)
        ->where('is_show', 1)
        ->orderBy('sort_order')
        ->orderBy('cat_id')
        ->take(10);

    $res = BaseRepository::getToArrayGet($res);

    $where = [
        'area_id' => $area_id,
        'area_city' => $area_city,
        'area_pricetype' => $GLOBALS['_CFG']['area_pricetype']
    ];

    $arr = [];
    if ($res) {
        $CategoryRep = app(CategoryService::class);

        foreach ($res as $key => $value) {
            if ($key == 0) {
                $children = $CategoryRep->getCatListChildren($value['cat_id']);

                /* 查询扩展分类数据 */
                $extension_goods = GoodsCat::select('goods_id')->whereIn('cat_id', $children);
                $extension_goods = BaseRepository::getToArrayGet($extension_goods);
                $extension_goods = BaseRepository::getFlatten($extension_goods);

                $goods_res = Goods::where('is_on_sale', 1)
                    ->where('is_alone_sale', 1)
                    ->where('is_delete', 0);

                if (config('shop.review_goods') == 1) {
                    $goods_res = $goods_res->whereIn('review_status', [3, 4, 5]);
                }

                $goods_res = app(DscRepository::class)->getAreaLinkGoods($goods_res, $area_id, $area_city);

                $where['children'] = $children;
                $where['extension_goods'] = $extension_goods;

                $goods_res = $goods_res->where(function ($query) use ($where) {
                    $query = $query->where('cat_id', $where['children']);

                    if ($where['extension_goods']) {
                        $query->orWhere('goods_id', $where['extension_goods']);
                    }
                });

                $user_rank = session('user_rank');
                $goods_res = $goods_res->with([
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

                if ($num > 0) {
                    $goods_res = $goods_res->take($num);
                }

                $goods_res = $goods_res->orderByRaw("sort_order, goods_id");

                $goods_res = BaseRepository::getToArrayGet($goods_res);

                if ($goods_res) {
                    foreach ($goods_res as $idx => $row) {
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

                        $price = app(GoodsCommonService::class)->getGoodsPrice($price, session('discount'), $row);

                        $row['shop_price'] = $price['shop_price'];
                        $row['promote_price'] = $price['promote_price'];
                        $row['goods_number'] = $price['goods_number'];

                        $goods_res[$idx] = $row;

                        if ($row['promote_price'] > 0) {
                            $promote_price = app(GoodsCommonService::class)->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                        } else {
                            $promote_price = 0;
                        }

                        $goods_res[$idx]['is_promote'] = $row['is_promote'];
                        $goods_res[$idx]['goods_thumb'] = app(DscRepository::class)->getImagePath($row['goods_thumb']);
                        $goods_res[$idx]['market_price'] = app(DscRepository::class)->getPriceFormat($row['market_price']);
                        $goods_res[$idx]['shop_price'] = app(DscRepository::class)->getPriceFormat($row['shop_price']);
                        $goods_res[$idx]['promote_price'] = ($promote_price > 0) ? app(DscRepository::class)->getPriceFormat($promote_price) : '';
                        $goods_res[$idx]['shop_price'] = app(DscRepository::class)->getPriceFormat($row['shop_price']);
                        $goods_res[$idx]['short_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ? app(DscRepository::class)->subStr($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
                        $goods_res[$idx]['url'] = app(DscRepository::class)->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
                        $arr[$key]['goods'] = $goods_res;
                    }
                }
            } else {
                $arr[$key]['goods'] = [];
            }

            $arr[$key]['cats'] = $value['cat_id'];
            $arr[$key]['floor_num'] = $num;
            $arr[$key]['warehouse_id'] = $warehouse_id;
            $arr[$key]['area_id'] = $area_id;
        }
    }


    return $arr;
}

function get_brands_theme2($brands)
{
    $arr = [];
    if ($brands) {
        foreach ($brands as $key => $row) {
            if ($key < 8) {
                $arr['one_brands'][$key] = $row;
            } elseif ($key >= 8 && $key <= 15) {
                $arr['two_brands'][$key] = $row;
            } elseif ($key >= 16 && $key <= 23) {
                $arr['three_brands'][$key] = $row;
            } elseif ($key >= 24 && $key <= 31) {
                $arr['foure_brands'][$key] = $row;
            } elseif ($key >= 32 && $key <= 39) {
                $arr['five_brands'][$key] = $row;
            }
        }

        $arr = array_values($arr);
    }

    return $arr;
}

/**
 * 获得所有扩展分类属于指定分类的所有商品ID
 *
 * @access  public
 * @param array $cats 分类信息
 * @return  array;
 */
function get_extension_goods($cats = [])
{
    $goods_id = [];
    if ($cats) {

        /* 查询扩展分类数据 */
        $res = GoodsCat::select('goods_id')->whereIn('cat_id', $cats);
        $res = BaseRepository::getToArrayGet($res);
        $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');
    }

    return $goods_id;
}

/**
 * 取得拍卖活动出价记录
 *
 * @param int $act_id
 * @param int $type
 * @param int $is_anonymous
 * @return array
 */
function auction_log($act_id = 0, $type = 0, $is_anonymous = 1)
{
    if ($type == 1) {
        $log = AuctionLog::where('act_id', $act_id);

        $log = $log->whereHasIn('getUsers');

        $log = $log->count();
    } else {
        $res = AuctionLog::where('act_id', $act_id);

        $res = $res->whereHasIn('getUsers');

        $res = $res->orderBy('log_id', 'desc');

        $res = BaseRepository::getToArrayGet($res);

        $log = [];
        if ($res) {

            $userIdList = BaseRepository::getKeyPluck($res, 'bid_user');
            $userList = UserDataHandleService::userDataList($userIdList, ['user_id', 'user_name']);

            foreach ($res as $row) {

                $user = $userList[$row['bid_user']] ?? [];

                $row = BaseRepository::getArrayMerge($row, $user);

                $row['user_name'] = isset($row['user_name']) ? $is_anonymous ? setAnonymous($row['user_name']) : $row['user_name'] : ''; //处理用户名 by wu

                $show_mobile = config('shop.show_mobile') ?? 0;
                if ($show_mobile == 0) {
                    $row['user_name'] = app(DscRepository::class)->stringToStar($row['user_name']);
                }

                $row['bid_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $row['bid_time']);
                $row['formated_bid_price'] = app(DscRepository::class)->getPriceFormat($row['bid_price'], false);
                $log[] = $row;
            }
        }
    }

    return $log;
}

/**
 * 取得优惠活动信息
 * @param int $act_id 活动id
 * @return  array
 */
function favourable_info($act_id, $path = '')
{
    $row = FavourableActivity::where('act_id', $act_id);

    if (empty($path)) {
        $row = $row->where('review_status', 3);
    }

    $row = BaseRepository::getToArrayFirst($row);

    if (!empty($row)) {
        $row['start_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $row['start_time']);
        $row['end_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $row['end_time']);
        $row['formated_min_amount'] = app(DscRepository::class)->getPriceFormat($row['min_amount']);
        $row['formated_max_amount'] = app(DscRepository::class)->getPriceFormat($row['max_amount']);
        $row['gift'] = unserialize($row['gift']);
        if ($row['act_type'] == FAT_GOODS) {
            $row['act_type_ext'] = round($row['act_type_ext']);
        }
    }

    return $row;
}

/**
 * 查询商品是否存在配件
 */
function get_group_goods_count($goods_id = 0)
{
    $count = GroupGoods::where('parent_id', $goods_id)->count();
    return $count;
}

/**
 * 取得秒杀活动信息
 * @param int $seckill_goods_id 秒杀活动商品id
 * @return  array
 */
function seckill_info($seckill_goods_id = 0)
{
    $seckill = \App\Repositories\Activity\SeckillRepository::seckill_detail($seckill_goods_id);

    /* 如果为空，返回空数组 */
    if (empty($seckill)) {
        return [];
    }

    if (isset($seckill['get_goods'])) {
        unset($seckill['get_goods']['sales_volume']);
        $seckill = BaseRepository::getArrayMerge($seckill, $seckill['get_goods']);
    }

    if (isset($seckill['get_seckill'])) {
        $seckill = BaseRepository::getArrayMerge($seckill, $seckill['get_seckill']);
    }

    if (isset($seckill['get_seckill_time_bucket'])) {
        $seckill = BaseRepository::getArrayMerge($seckill, $seckill['get_seckill_time_bucket']);
    }

    $tmr = 0;
    if (isset($_REQUEST['tmr']) && $_REQUEST['tmr'] == 1) {
        $tmr = 86400;
    }
    $begin_time = TimeRepository::getLocalStrtoTime($seckill['begin_time']) + $tmr;
    $end_time = TimeRepository::getLocalStrtoTime($seckill['end_time']) + $tmr;

    /* 格式化时间 */
    $seckill['formated_start_date'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $begin_time);
    $seckill['formated_end_date'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $end_time);

    $now = gmtime();
    if ($begin_time < $now && $end_time > $now) {
        $seckill['status'] = true;
    } else {
        $seckill['status'] = false;
    }
    $seckill['is_end'] = $now > $end_time ? 1 : 0;

    $get_seckill_goods_attr = $seckill['get_seckill_goods_attr'] ?? [];
    unset($seckill['get_seckill_goods_attr']);
    if (!empty($get_seckill_goods_attr)) {
        // 有秒杀属性取最小属性价、数量
        $get_seckill_goods_attr = collect($get_seckill_goods_attr)->sortBy('sec_price')->first();
        $seckill['sec_price'] = $get_seckill_goods_attr['sec_price'] ?? 0;
        $seckill['sec_num'] = $get_seckill_goods_attr['sec_num'] ?? 0;
        $seckill['sec_limit'] = $get_seckill_goods_attr['sec_limit'] ?? 0;
    }

    $merchantList = MerchantDataHandleService::getMerchantInfoDataList([$seckill['user_id']]);
    $merchant = $merchantList[$seckill['user_id']] ?? [];

    $seckill['country_name'] = $merchant['country_name'] ?? '';
    $seckill['country_icon'] = $merchant['country_icon'] ?? '';
    $seckill['cross_warehouse_name'] = $merchant['cross_warehouse_name'] ?? '';

    $seckill['rz_shop_name'] = $merchant['shop_name'] ?? ''; //店铺名称

    $seckill['goods_thumb'] = app(DscRepository::class)->getImagePath($seckill['goods_thumb']);

    $build_uri = [
        'urid' => $seckill['user_id'],
        'append' => $seckill['rz_shop_name']
    ];

    $domain_url = app(MerchantCommonService::class)->getSellerDomainUrl($seckill['user_id'], $build_uri);
    $seckill['store_url'] = $domain_url['domain_name'];

    $seckill['shopinfo'] = app(MerchantCommonService::class)->getShopName($seckill['user_id'], 2);
    $seckill['shopinfo']['brand_thumb'] = str_replace(['../'], '', $seckill['shopinfo']['brand_thumb']);
    if ($seckill['user_id'] == 0) {
        $seckill['brand'] = get_brand_url($seckill['brand_id']);
    }

    if ($GLOBALS['_CFG']['open_oss'] == 1) {
        $bucket_info = app(DscRepository::class)->getBucketInfo();
        $endpoint = $bucket_info['endpoint'];
    } else {
        $endpoint = url('/');
    }

    if ($seckill['goods_desc']) {
        $desc_preg = get_goods_desc_images_preg($endpoint, $seckill['goods_desc']);
        $seckill['goods_desc'] = $desc_preg['goods_desc'];
    }

    $seckill['formated_sec_price'] = app(DscRepository::class)->getPriceFormat($seckill['sec_price']);
    $seckill['formated_market_price'] = app(DscRepository::class)->getPriceFormat($seckill['market_price']);

    return $seckill;
}

/**
 * 获取当前商家的等级信息
 *
 * @param int $ru_id
 * @return array
 */
function get_merchants_grade_rank($ru_id = 0)
{
    if (empty($ru_id)) {
        return [];
    }

    $model = MerchantsGrade::query()->where('ru_id', $ru_id);

    $model = $model->whereHasIn('getSellerGrade');

    $model = $model->with([
        'getSellerGrade' => function ($query) {
            $query->select('id', 'goods_sun', 'seller_temp', 'favorable_rate', 'give_integral', 'rank_integral', 'pay_integral', 'grade_name', 'grade_img', 'grade_introduce');
        }
    ]);

    $res = BaseRepository::getToArrayFirst($model);

    if ($res) {
        $seller_grade = $res['get_seller_grade'];

        $res['goods_sun'] = $seller_grade['goods_sun'];
        $res['seller_temp'] = $seller_grade['seller_temp'];
        $res['favorable_rate'] = $seller_grade['favorable_rate'];
        $res['give_integral'] = $seller_grade['give_integral'];
        $res['rank_integral'] = $seller_grade['rank_integral'];
        $res['pay_integral'] = $seller_grade['pay_integral'];

        $res['grade_name'] = $seller_grade['grade_name'] ?? '';
        $res['grade_img'] = $seller_grade['grade_img'] ?? '';
        $res['grade_introduce'] = $seller_grade['grade_introduce'] ?? '';
    }

    $res['give_integral'] = isset($res['give_integral']) && !empty($res['give_integral']) ? $res['give_integral'] / 100 : 1;
    $res['rank_integral'] = isset($res['give_integral']) && !empty($res['rank_integral']) ? $res['rank_integral'] / 100 : 1;

    return $res;
}

/**
 * 判断商品分类是否可用
 *
 * @access  public
 * @param string $cat_id
 * @return  bool
 */
function judge_goods_cat_enabled($cat_id = 0)
{
    if ($cat_id > 0) {
        while ($cat_id > 0) {
            $cat_info = Category::select('is_show', 'parent_id')->where('cat_id', $cat_id);
            $cat_info = BaseRepository::getToArrayFirst($cat_info);

            if ($cat_info && $cat_info['is_show'] == 1) {
                $cat_id = $cat_info['parent_id'];
            } else {
                return false;
            }
        }
        return true;
    } else {
        return false;
    }
}
