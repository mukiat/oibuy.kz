<?php

namespace App\Services\Goods;

use App\Models\CartCombo;
use App\Models\Goods;
use App\Models\GoodsAttr;
use App\Models\GroupGoods;
use App\Models\Products;
use App\Models\ProductsArea;
use App\Models\ProductsWarehouse;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscEncryptRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Services\Cart\CartDataHandleService;

class GoodsFittingService
{
    protected $sessionRepository;
    protected $dscRepository;
    protected $goodsCommonService;

    public function __construct(
        SessionRepository $sessionRepository,
        DscRepository $dscRepository,
        GoodsCommonService $goodsCommonService
    )
    {
        $this->sessionRepository = $sessionRepository;
        $this->dscRepository = $dscRepository;
        $this->goodsCommonService = $goodsCommonService;
    }

    /**
     * 获取组合购买里面的单个商品（属性总）库存量
     *
     * @param $goods_number
     * @param $goods_id
     * @param $warehouse_id
     * @param $area_id
     * @param int $area_city
     * @return int
     */
    public function getGoodsFittingsNumber($goods_number, $goods_id, $warehouse_id, $area_id, $area_city = 0)
    {
        $model_attr = Goods::where('goods_id', $goods_id)->value('model_attr');

        if ($model_attr == 1) {
            $res = ProductsWarehouse::where('goods_id', $goods_id)->where('warehouse_id', $warehouse_id);
        } elseif ($model_attr == 2) {
            $res = ProductsArea::where('goods_id', $goods_id)->where('area_id', $area_id);

            if (config('shop.area_pricetype') == 1) {
                $res = $res->where('city_id', $area_city);
            }
        } else {
            $res = Products::where('goods_id', $goods_id);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        //当商品没有属性库存时
        if ($res) {
            $arr['product_number'] = 0;
            foreach ($res as $key => $row) {
                $arr[$key] = $row;
                $arr['product_number'] += $row['product_number'];
            }
        } else {
            $arr['product_number'] = $goods_number;
        }

        return $arr['product_number'];
    }

    /**
     * 获得购物车中商品的配件
     *
     * @param array $goods_list
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param string $rev
     * @param int $type
     * @param array $goods_equal
     * @param int $user_id
     * @param array $userRank
     * @return array
     */
    public function getGoodsFittings($goods_list = [], $warehouse_id = 0, $area_id = 0, $area_city = 0, $rev = '', $type = 0, $goods_equal = [], $user_id = 0, $userRank = [])
    {
        if (empty($goods_list)) {
            return [];
        }

        if (empty($user_id)) {
            $user_id = session('user_id', 0);
        }

        $session_id = $this->sessionRepository->realCartMacIp();

        $goods_list = BaseRepository::getExplode($goods_list);
        $res = GroupGoods::whereIn('parent_id', $goods_list);

        if ($type > 0) {

            $cartCombo = CartCombo::select('goods_id');

            if ($goods_equal) {

                $goods_equal = BaseRepository::getExplode($goods_equal);
                $goods_equal = DscEncryptRepository::filterValInt($goods_equal);

                $cartCombo = $cartCombo->whereIn('goods_id', $goods_equal);
            }

            if ($type == 1) {
                if (!empty($user_id)) {
                    $cartCombo = $cartCombo->where('user_id', $user_id);
                } else {
                    $cartCombo = $cartCombo->where('session_id', $session_id);
                }

                $cartCombo = $cartCombo->where('group_id', $rev);
            }

            $cartCombo = $cartCombo->pluck('goods_id');
            $goods_id = BaseRepository::getToArray($cartCombo);

            $res = $res->whereIn('goods_id', $goods_id);

            if ($type == 2) {
                $res = $res->where('group_id', $rev);
            }
        }

        $user_rank = isset($userRank['rank_id']) && $userRank['rank_id'] ? $userRank['rank_id'] : session('user_rank', 0);
        $discount = isset($userRank['discount']) && $userRank['discount'] ? $userRank['discount'] : session('discount', 1);

        if ($type == 0 && $rev) {
            // group_id
            if (strstr($rev, '_1_') != false) {
                $res = $res->where('group_id', 1);
            }

            if (strstr($rev, '_2_') != false) {
                $res = $res->where('group_id', 2);
            }
        }

        $res = $res->orderByRaw('parent_id, goods_id asc');

        $res = BaseRepository::getToArrayGet($res);

        $temp_index = 1;
        $arr = [];
        if ($res) {

            $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');

            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id);
            $memberPrice = GoodsDataHandleService::goodsMemberPrice($goods_id, $user_rank);
            $warehouseGoods = GoodsDataHandleService::getWarehouseGoodsDataList($goods_id, $warehouse_id);
            $warehouseAreaGoods = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods_id, $area_id, $area_city);

            $cartComboList = CartDataHandleService::getCartComboDataList($goods_id, ['goods_id', 'parent_id', 'goods_attr_id', 'group_id as cc_group_id']);

            $warehouse_area = [
                'warehouse_id' => $warehouse_id,
                'area_id' => $area_id,
                'area_city' => $area_city
            ];

            foreach ($res as $row) {
                $row['goods_attr_id'] = isset($row['goods_attr_id']) ? $row['goods_attr_id'] : "";

                $goods = $goodsList[$row['goods_id']] ?? [];

                $sql = [
                    'where' => [
                        [
                            'name' => 'goods_id',
                            'value' => $row['goods_id']
                        ],
                        [
                            'name' => 'parent_id',
                            'value' => $row['parent_id']
                        ]
                    ]
                ];
                $cartCombo = BaseRepository::getArraySqlFirst($cartComboList, $sql);
                $cartCombo = $cartCombo ? $cartCombo : [];

                $row = $goods ? array_merge($row, $goods) : $row;
                $row = $cartCombo ? array_merge($row, $cartCombo) : $row;

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

                $arr[$temp_index] = $row;

                $row['parent_name'] = $row['goods_name'];

                $arr[$temp_index]['parent_id'] = $row['parent_id']; //配件的基本件ID
                $arr[$temp_index]['parent_name'] = $row['parent_name']; //配件的基本件的名称
                $arr[$temp_index]['parent_short_name'] = config('shop.goods_name_length') > 0 ?
                    $this->dscRepository->subStr($row['parent_name'], config('shop.goods_name_length')) : $row['parent_name']; //配件的基本件显示的名称
                $arr[$temp_index]['goods_id'] = $row['goods_id']; //配件的商品ID
                $arr[$temp_index]['goods_name'] = $row['goods_name']; //配件的名称
                $arr[$temp_index]['comments_number'] = $row['comments_number'];
                $arr[$temp_index]['sales_volume'] = $row['sales_volume'];
                $arr[$temp_index]['short_name'] = config('shop.goods_name_length') > 0 ?
                    $this->dscRepository->subStr($row['goods_name'], config('shop.goods_name_length')) : $row['goods_name']; //配件显示的名称
                $arr[$temp_index]['fittings_price'] = $this->dscRepository->getPriceFormat($row['goods_price']); //配件价格
                $arr[$temp_index]['shop_price'] = $this->dscRepository->getPriceFormat($row['shop_price']); //配件原价格
                $arr[$temp_index]['spare_price'] = $this->dscRepository->getPriceFormat($goods['shop_price'] - $row['goods_price']); //节省的差价 by mike

                $arr[$temp_index]['market_price'] = $row['market_price'];

                $minMax_price = $this->getGoodsNinMaxPrice($row['goods_id'], $user_rank, $discount, $warehouse_id, $area_id, $area_city, $row['goods_price'], $row['market_price']); //配件价格min与max
                $arr[$temp_index]['fittings_minPrice'] = $minMax_price['goods_min'];
                $arr[$temp_index]['fittings_maxPrice'] = $minMax_price['goods_max'];

                $arr[$temp_index]['market_minPrice'] = $minMax_price['market_min'];
                $arr[$temp_index]['market_maxPrice'] = $minMax_price['market_max'];

                if (!empty($row['goods_attr_id'])) {
                    $prod_attr = explode(',', $row['goods_attr_id']);
                } else {
                    $prod_attr = [];
                }

                $attr_price = app(GoodsAttrService::class)->specPrice($prod_attr, $row['goods_id'], $warehouse_area);
                $arr[$temp_index]['attr_price'] = $attr_price;

                $arr[$temp_index]['shop_price_ori'] = $row['shop_price']; //配件原价格 by mike
                $arr[$temp_index]['fittings_price_ori'] = $row['goods_price']; //配件价格 by mike
                $arr[$temp_index]['spare_price_ori'] = ($goods['shop_price'] - $row['goods_price']); //节省的差价 by mike
                $arr[$temp_index]['group_id'] = $row['group_id']; //套餐组 by mike

                if ($type == 2) {
                    $cc_rev = "m_goods_" . $rev . "_" . $row['parent_id'];
                    $img_flie = CartCombo::where('goods_id', $row['goods_id'])
                        ->where('group_id', $cc_rev);

                    if (!empty($user_id)) {
                        $img_flie = $img_flie->where('user_id', $user_id);
                    } else {
                        $img_flie = $img_flie->where('session_id', $session_id);
                    }
                } else {
                    $img_flie = CartCombo::where('goods_id', $row['goods_id'])
                        ->where('group_id', $rev);

                    if (!empty($user_id)) {
                        $img_flie = $img_flie->where('user_id', $user_id);
                    } else {
                        $img_flie = $img_flie->where('session_id', $session_id);
                    }
                }

                $img_flie = $img_flie->value('img_flie');
                $arr[$temp_index]['img_flie'] = $img_flie;

                if (!empty($img_flie)) {
                    $arr[$temp_index]['goods_thumb'] = $arr[$temp_index]['img_flie'];
                } else {
                    $arr[$temp_index]['goods_thumb'] = $row['goods_thumb'];
                }

                $arr[$temp_index]['goods_thumb'] = $this->dscRepository->getImagePath($arr[$temp_index]['goods_thumb']);
                $arr[$temp_index]['goods_img'] = $this->dscRepository->getImagePath($row['goods_img']);
                $arr[$temp_index]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
                $arr[$temp_index]['attr_id'] = !empty($row['goods_attr_id']) ? str_replace(',', '|', $row['goods_attr_id']) : "";

                //求组合购买商品已选择属性的库存量 start
                if (empty($row['goods_attr_id'])) {
                    $arr[$temp_index]['goods_number'] = $this->getGoodsFittingsNumber($row['goods_number'], $row['goods_id'], $warehouse_id, $area_id, $area_city);
                } else {
                    $products = app(GoodsWarehouseService::class)->getWarehouseAttrNumber($row['goods_id'], $row['goods_attr_id'], $warehouse_id, $area_id, $area_city);
                    $attr_number = $products ? $products['product_number'] : 0;

                    if ($row['model_attr'] == 1) {
                        $prod = ProductsWarehouse::where('goods_id', $row['goods_id'])->where('warehouse_id', $warehouse_id);
                    } elseif ($row['model_attr'] == 2) {
                        $prod = ProductsArea::where('goods_id', $row['goods_id'])->where('area_id', $area_id);

                        if (config('shop.area_pricetype') == 1) {
                            $prod = $prod->where('city_id', $area_city);
                        }
                    } else {
                        $prod = Products::where('goods_id', $row['goods_id']);
                    }

                    $prod = BaseRepository::getToArrayFirst($prod);

                    //当商品没有属性库存时
                    if (empty($prod)) {
                        $attr_number = $row['goods_number'];
                    }

                    $arr[$temp_index]['goods_number'] = $attr_number;
                }
                //求组合购买商品已选择属性的库存量 end

                $row['goods_attr_id'] = isset($row['goods_attr_id']) ? $row['goods_attr_id'] : '';
                $arr[$temp_index]['properties'] = app(GoodsAttrService::class)->getGoodsProperties($row['goods_id'], $warehouse_id, $area_id, $area_city, $row['goods_attr_id']);

                if ($type == 2) {
                    $group_id = "m_goods_" . $rev . "_" . $row['parent_id'];
                    $rec_id = CartCombo::where('goods_id', $row['goods_id'])
                        ->where('group_id', $group_id);

                    if (!empty($user_id)) {
                        $rec_id = $rec_id->where('user_id', $user_id);
                    } else {
                        $rec_id = $rec_id->where('session_id', $session_id);
                    }

                    $rec_id = $rec_id->value('rec_id');

                    $group_cnt = "m_goods_" . $rev . "=" . $row['parent_id'];
                    $arr[$temp_index]['group_top'] = $row['goods_id'] . "|" . $warehouse_id . "|" . $area_id . "|" . $group_cnt;

                    if ($rec_id > 0) {
                        $arr[$temp_index]['selected'] = 1;
                    } else {
                        $arr[$temp_index]['selected'] = 0;
                    }
                }

                $temp_index++;
            }
        }

        return $arr;
    }

    /**
     * 获取商品最大的价格和最小价格
     *
     * @param int $goods_id
     * @param int $user_rank
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param int $goods_price
     * @param int $market_price
     * @param int $type
     * @return mixed
     */
    public function getGoodsNinMaxPrice($goods_id = 0, $user_rank = 0, $discount = 1, $warehouse_id = 0, $area_id = 0, $area_city = 0, $goods_price = 0, $market_price = 0, $type = 1)
    {
        $res = GoodsAttr::select('goods_id', 'attr_id', 'attr_price')
            ->where('goods_id', $goods_id);

        $res = $res->whereHasIn('getGoods')
            ->whereHasIn('getGoodsAttribute');

        $where = [
            'warehouse_id' => $warehouse_id,
            'area_id' => $area_id,
            'area_city' => $area_city,
            'area_pricetype' => config('shop.area_pricetype')
        ];

        $res = $res->with([
            'getGoods' => function ($query) {
                $query->select('goods_id', 'model_attr');
            },
            'getGoodsWarehouseAttr' => function ($query) use ($where) {
                $query->select('goods_id', 'attr_price')
                    ->where('warehouse_id', $where['warehouse_id']);
            },
            'getGoodsWarehouseAreaAttr' => function ($query) use ($where) {
                $query = $query->select('goods_id', 'attr_price')
                    ->where('area_id', $where['area_id']);

                if ($where['area_pricetype'] == 1) {
                    $query->where('city_id', $where['area_city']);
                }
            }
        ]);

        $res = BaseRepository::getToArrayGet($res);

        if (!empty($res)) {
            foreach ($res as $key => $row) {
                if ($row['get_goods']) {
                    if ($row['get_goods']['model_attr'] == 1) {
                        $row['attr_price'] = $row['get_goods_warehouse_attr']['attr_price'];
                    } elseif ($row['get_goods']['model_attr'] == 2) {
                        $row['attr_price'] = $row['get_goods_warehouse_area_attr']['attr_price'];
                    }
                }

                $res[$key] = $row;
            }
        }

        $arr_res = $res;

        $arr_k = '';
        $k_res = [];
        if ($arr_res) {
            foreach ($arr_res as $val) {
                $arr_k .= $val['attr_id'] . '@';
            }
            $arr_k = rtrim($arr_k, '@');

            $k_res = explode('@', $arr_k);
            $k_res = array_flip(array_flip($k_res));
        }

        $new_arr = [];
        if (isset($k_res) && !empty($k_res)) {
            foreach ($k_res as $val) {
                foreach ($arr_res as $v) {
                    if ($v['attr_id'] == $val) {
                        $new_arr[$val][] = $v['attr_price'];
                    }
                }
            }
        }

        if ($type == 1) {
            $new_arr = $this->getUnsetNullArray($new_arr, 2);
        }

        $new_arr_res = [];
        $num_res_max = 0;
        $num_res_min = 0;

        if ($new_arr) {
            foreach ($new_arr as $k => $val) {
                $new_arr_res[$k]['max'] = $val[array_search(max($val), $val)];
                $new_arr_res[$k]['min'] = $val[array_search(min($val), $val)];
            }

            foreach ($new_arr_res as $val) {
                $num_res_max += $val['max'];
                $num_res_min += $val['min'];
            }
        }

        if (config('shop.add_shop_price') == 0) {
            $num_res_min = 0;
        } else {
            $num_res_max = 0;
        }

        $arr = [];
        if ($type == 1) {
            //商品组合购买

            $arr['goods_min'] = $goods_price + $num_res_min;
            $arr['goods_max'] = $goods_price + $num_res_max;

            $arr['market_min'] = $market_price + $num_res_min;
            $arr['market_max'] = $market_price + $num_res_max;
        } elseif ($type == 2) {
            //商品普通购买

            $row = Goods::select('goods_id', 'model_price', 'shop_price', 'integral', 'goods_number', 'promote_price', 'promote_start_date', 'promote_end_date')->where('goods_id', $goods_id);
            $row = BaseRepository::getToArrayFirst($row);

            if ($row) {

                $memberPrice = GoodsDataHandleService::goodsMemberPrice($row['goods_id'], $user_rank);
                $warehouseGoods = GoodsDataHandleService::getWarehouseGoodsDataList($row['goods_id'], $warehouse_id);
                $warehouseAreaGoods = GoodsDataHandleService::getWarehouseAreaGoodsDataList($row['goods_id'], $area_id, $area_city);

                $price = [
                    'model_price' => $row['model_price'],
                    'user_price' => $memberPrice[$row['goods_id']]['user_price'] ?? 0,
                    'percentage' => $memberPrice[$row['goods_id']]['percentage'] ?? 0,
                    'warehouse_price' => $warehouseGoods[$row['goods_id']]['warehouse_price'] ?? 0,
                    'region_price' => $warehouseAreaGoods[$row['goods_id']]['region_price'] ?? 0,
                    'shop_price' => $row['shop_price'],
                    'warehouse_promote_price' => $warehouseGoods[$row['goods_id']]['warehouse_promote_price'] ?? 0,
                    'region_promote_price' => $warehouseAreaGoods[$row['goods_id']]['region_promote_price'] ?? 0,
                    'promote_price' => $row['promote_price'],
                    'integral' => $row['integral'],
                    'wpay_integral' => $warehouseGoods[$row['goods_id']]['pay_integral'] ?? 0,
                    'apay_integral' => $warehouseAreaGoods[$row['goods_id']]['pay_integral'] ?? 0,
                    'goods_number' => $row['goods_number'],
                    'wg_number' => $warehouseGoods[$row['goods_id']]['region_number'] ?? 0,
                    'wag_number' => $warehouseAreaGoods[$row['goods_id']]['region_number'] ?? 0,
                ];

                $price = $this->goodsCommonService->getGoodsPrice($price, $discount, $row);

                $row['shop_price'] = $price['shop_price'];
                $row['promote_price'] = $price['promote_price'];

                /* 修正促销价格 */
                if ($row['promote_price'] > 0) {
                    $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                } else {
                    $promote_price = 0;
                }

                $format_promote_price = $promote_price > 0 ? $promote_price : 0;

                if (!empty($format_promote_price)) {
                    $arr['promote_minPrice'] = $this->dscRepository->getPriceFormat($format_promote_price + $num_res_min);
                    $arr['promote_maxPrice'] = $this->dscRepository->getPriceFormat($format_promote_price + $num_res_max);
                } else {
                    $arr['promote_minPrice'] = $format_promote_price;
                    $arr['promote_maxPrice'] = $format_promote_price;
                }

                $arr['shop_minPrice'] = $this->dscRepository->getPriceFormat($row['shop_price'] + $num_res_min);
                $arr['shop_maxPrice'] = $this->dscRepository->getPriceFormat($row['shop_price'] + $num_res_max);
            }
        }

        return $arr;
    }

    //删掉值为0的数组
    /*
     * 1 = 一维数组
     * 2 = 二维数组
    */
    public function getUnsetNullArray($arr = [], $type = 0)
    {
        $arr = array_values($arr);

        $new_arr = [];
        if ($arr && $type == 2) {
            for ($i = 0; $i < count($arr); $i++) {
                for ($j = 0; $j < count($arr[$i]); $j++) {
                    if ($arr[$i][$j] > 0) {
                        $new_arr[$i][$j] = $arr[$i][$j];
                    }
                }
            }
        } elseif ($arr && $type == 1) {
            for ($i = 0; $i < count($arr); $i++) {
                if ($arr[$i] > 0) {
                    $new_arr[$i] = $arr[$i];
                }
            }
        }

        return $new_arr;
    }

    /**
     * 查询已选择组合购买商品的区间价格
     *
     * @param $fittings
     * @param int $number
     * @return array
     */
    public function getChooseGoodsComboCart($fittings, $number = 1)
    {
        $arr = [];

        $arr['fittings_min'] = 0;
        $arr['fittings_max'] = 0;
        $arr['market_min'] = 0;
        $arr['market_max'] = 0;
        $arr['save_price'] = '';
        $arr['collocation_number'] = 0;
        $arr['save_minPrice'] = 0;
        $arr['save_maxPrice'] = 0;
        $arr['fittings_price'] = 0;
        $arr['fittings_market_price'] = 0;
        $arr['save_price_amount'] = 0;
        $arr['groupId'] = 0;
        $arr['all_price_ori'] = 0;
        $arr['return_attr'] = 0;
        $arr['all_market_price'] = 0;

        if ($fittings) {
            foreach ($fittings as $key => $row) {
                $arr[$key]['goods_id'] = $row['goods_id'];
                $arr[$key]['market_price'] = $row['market_price'] + $row['attr_price']; //实际市场价
                $arr[$key]['fittings_minPrice'] = $row['fittings_minPrice'];        //配件区间价格 min
                $arr[$key]['fittings_maxPrice'] = $row['fittings_maxPrice'];        //配件区间价格 max
                $arr[$key]['market_minPrice'] = $row['market_minPrice'];        //市场区间价格 min
                $arr[$key]['market_maxPrice'] = $row['market_maxPrice'];        //市场区间价格 max
                $arr[$key]['shop_price_ori'] = $row['shop_price_ori'];            //商品原价
                $arr[$key]['fittings_price_ori'] = $row['fittings_price_ori'];        //配件价格
                $arr[$key]['attr_price'] = $row['attr_price'];            //配件商品属性金额
                $arr[$key]['spare_price_ori'] = $row['spare_price_ori'];        //商品原价 - 配件价格 = 节省价
                $arr[$key]['group_id'] = !empty($row['group_id']) ? $row['group_id'] : 0;                //组ID
                $arr[$key]['is_attr'] = $this->getCartComboGoodsProductList($row['goods_id']);

                $arr[$key]['shop_price'] = $row['get_goods']['shop_price'] ?? $row['shop_price_ori'];

                if (config('shop.add_shop_price') == 0) {
                    $row['attr_price'] = 0;
                }

                if ($arr[$key]['group_id'] == 0) {
                    $arr[$key]['price_ori'] = $row['shop_price_ori'] + $row['attr_price'];
                } else {
                    $arr[$key]['price_ori'] = $row['fittings_price_ori'] + $row['attr_price'];
                }

                $arr['save_price_amount'] += $row['spare_price_ori']; //配件商品节省总金额
                $arr['fittings_price'] += $arr[$key]['price_ori']; //配件商品总金额
                $arr['fittings_market_price'] += $row['market_price']; //配件商品市场价总金额

                $arr['save_price'] .= $row['spare_price_ori'] . ",";

                if (!empty($row['group_id'])) {
                    $arr['groupId'] .= $row['group_id'] . ",";
                }
            }

            $arr['collocation_number'] = count($fittings) - 1;

            $arr['save_price'] = substr($arr['save_price'], 0, -1);
            $arr['save_price'] = explode(',', $arr['save_price']);
            $arr['save_price'] = $this->getUnsetNullArray($arr['save_price'], 1);

            $arr['save_minPrice'] = !empty($arr['save_price']) ? min($arr['save_price']) : 0;
            $arr['save_maxPrice'] = $this->getSaveMaxPrice($arr['save_price']);

            $arr['groupId'] = substr($arr['groupId'], 1, -1);
            $arr['groupId'] = explode(',', $arr['groupId']);
            $arr['groupId'] = array_unique($arr['groupId']);
            $arr['groupId'] = implode(',', $arr['groupId']);

            $minmax_values = $this->getNinMaxValues($arr);

            $arr['fittings_min'] = $minmax_values['goods_price_ori'];
            $arr['fittings_max'] = $minmax_values['all_price_ori'];
            $arr['market_min'] = $minmax_values['market_minPrice'];
            $arr['market_max'] = $minmax_values['market_maxPrice'];

            $arr['return_attr'] = $minmax_values['return_attr']; //判断配件商品是否有属性
            $arr['all_price_ori'] = $minmax_values['all_price_ori'];
            $arr['all_market_price'] = $minmax_values['all_market_price'];
        }

        return $arr;
    }

    /**
     * 查询组合购买里面的配件商品是否有货品
     *
     * @param $goods_id
     * @return int
     */
    public function getCartComboGoodsProductList($goods_id)
    {
        $attr_list = GoodsAttr::where('goods_id', $goods_id)->count('goods_id');

        if ($attr_list) { //当商品没有货品时
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 获取数组里面最小值和最大值
     *
     * @param $arr
     * @return array
     */
    public function getNinMaxValues($arr)
    {
        $unsetStr = "fittings_min,fittings_max,market_min,market_max,save_price,collocation_number,save_minPrice,save_maxPrice,fittings_price,save_price_amount,groupId,all_price_ori,return_attr,fittings_market_price";
        $unsetStr = explode(',', $unsetStr);

        foreach ($unsetStr as $str) {
            unset($arr[$str]);
        }

        $newArr = [];
        $newArr['fittings_minPrice'] = '';
        $newArr['fittings_maxPrice'] = '';
        $newArr['market_minPrice'] = '';
        $newArr['market_maxPrice'] = '';
        $newArr['is_attr'] = '';
        $shop_price = 0;
        $market_price = 0;
        $newArr['all_price_ori'] = 0;
        $newArr['return_attr'] = 0;
        $newArr['all_market_price'] = 0;

        foreach ($arr as $key => $row) {
            if ($key > 0) {
                $newArr['all_price_ori'] += $row['price_ori'];
                $newArr['all_market_price'] += $row['shop_price'];
                $newArr['fittings_minPrice'] .= $row['fittings_minPrice'] . ',';
                $newArr['fittings_maxPrice'] .= $row['fittings_maxPrice'] . ',';
                $newArr['market_minPrice'] .= $row['shop_price'] . ',';
                $newArr['market_maxPrice'] .= $row['shop_price'] . ',';

                if ($row['is_attr']) {
                    $newArr['is_attr'] .= $row['is_attr'] . ',';
                }
            }
        }

        if ($newArr['is_attr']) {
            $newArr['is_attr'] = $this->dscRepository->delStrComma($newArr['is_attr']);
            $is_attr = explode(",", $newArr['is_attr']);

            foreach ($is_attr as $key => $row) {
                if ($row) {
                    $row = floatval($row);
                    $newArr['return_attr'] += $row;
                }
            }
        }

        $newArr['fittings_minPrice'] = $this->dscRepository->delStrComma($newArr['fittings_minPrice']);
        $newArr['market_minPrice'] = $this->dscRepository->delStrComma($newArr['market_minPrice']);

        $newArr['fittings_maxPrice'] = $this->dscRepository->delStrComma($newArr['fittings_maxPrice']);
        $newArr['market_maxPrice'] = $this->dscRepository->delStrComma($newArr['market_maxPrice']);

        $fittings_minPrice = !empty($newArr['fittings_minPrice']) ? explode(",", $newArr['fittings_minPrice']) : [];
        $market_minPrice = !empty($newArr['market_minPrice']) ? explode(",", $newArr['market_minPrice']) : [];

        $fittings_maxPrice = !empty($newArr['fittings_maxPrice']) ? explode(",", $newArr['fittings_maxPrice']) : [];
        $market_maxPrice = !empty($newArr['market_maxPrice']) ? explode(",", $newArr['market_maxPrice']) : [];

        foreach ($fittings_maxPrice as $key => $shop) {
            $shop_price += $shop;
        }

        $newArr['fittings_maxPrice'] = $shop_price;

        foreach ($market_maxPrice as $key => $market) {
            $market_price += $market;
        }

        $newArr['market_maxPrice'] = $market_price;

        array_push($fittings_minPrice, $arr[0]['fittings_minPrice']);
        array_push($fittings_maxPrice, $arr[0]['fittings_maxPrice']);

        array_push($market_minPrice, $arr[0]['shop_price']);
        array_push($market_maxPrice, $arr[0]['shop_price']);

        $newArr['fittings_minPrice'] = min($fittings_minPrice);
        $newArr['fittings_maxPrice'] = max($fittings_maxPrice);

        $newArr['market_minPrice'] = min($market_minPrice);
        $newArr['market_maxPrice'] = max($market_maxPrice);

        $newArr['goods_price_ori'] = $arr[0]['price_ori'];
        $newArr['all_price_ori'] = $arr[0]['price_ori'] + $newArr['all_price_ori']; //实际搭配价
        $newArr['all_market_price'] = $arr[0]['shop_price'] + $arr[0]['attr_price'] + $newArr['all_market_price']; //实际搭配市场价

        return $newArr;
    }


    /**
     * 获得组合购买的的主件商品
     *
     * @param int $goods_id
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param string $rev
     * @param int $type
     * @param int $fittings_goods
     * @param array $fittings_attr
     * @param int $user_id
     * @param array $userRank
     * @return array
     * @throws \Exception
     */
    public function getGoodsFittingsInfo($goods_id = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0, $rev = '', $type = 0, $fittings_goods = 0, $fittings_attr = [], $user_id = 0, $userRank = [])
    {
        if (empty($user_id)) {
            $user_id = session('user_id');
        }


        $temp_index = 0;
        $arr = [];

        $res = Goods::where('is_on_sale', 1)
            ->where('is_alone_sale', 1)
            ->where('is_delete', 0)
            ->where('goods_id', $goods_id);

        if (config('shop.review_goods')) {
            $res = $res->whereIn('review_status', [3, 4, 5]);
        }

        $res = $this->dscRepository->getAreaLinkGoods($res, $area_id, $area_city);

        $session_id = $this->sessionRepository->realCartMacIp();
        if ($type == 0) {
            $comboWhere = [
                'rev' => $rev,
                'user_id' => $user_id,
                'session_id' => $session_id
            ];
            $res = $res->whereHasIn('getCartCombo', function ($query) use ($comboWhere) {
                $query = $query->where('group_id', $comboWhere['rev']);

                if (!empty($comboWhere['user_id'])) {
                    $query->where('user_id', $comboWhere['user_id']);
                } else {
                    $query->where('session_id', $comboWhere['session_id']);
                }
            });
        }

        $where = [
            'area_id' => $area_id,
            'area_city' => $area_city,
            'area_pricetype' => config('shop.area_pricetype')
        ];

        $user_rank = session('user_rank', 0);

        $user_rank = isset($userRank['rank_id']) && $userRank['rank_id'] ? $userRank['rank_id'] : $user_rank;
        $discount = isset($userRank['discount']) && $userRank['discount'] ? $userRank['discount'] : session('discount', 1);

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
            },
            'getCartCombo' => function ($query) {
                $query->select('goods_id', 'parent_id', 'goods_attr_id', 'goods_price', 'group_id');
            }
        ]);

        $res = $res->orderBy('goods_id', 'desc');

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            $warehouse_area = [
                'warehouse_id' => $warehouse_id,
                'area_id' => $area_id,
                'area_city' => $area_city
            ];

            foreach ($res as $row) {
                $row = BaseRepository::getArrayMerge($row, $row['get_cart_combo']);

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

                $arr[$temp_index] = $row;

                $arr[$temp_index]['parent_id'] = isset($row['parent_id']) ? $row['parent_id'] : 0; //配件的基本件ID
                $row['parent_name'] = isset($row['parent_name']) ? $row['parent_name'] : ''; //配件的基本件的名称
                $arr[$temp_index]['parent_name'] = $row['parent_name'];
                $arr[$temp_index]['parent_short_name'] = config('shop.goods_name_length') > 0 ? $this->dscRepository->subStr($row['parent_name'], config('shop.goods_name_length')) : $row['parent_name']; //配件的基本件显示的名称
                $arr[$temp_index]['goods_id'] = $row['goods_id']; //配件的商品ID
                $arr[$temp_index]['goods_name'] = $row['goods_name']; //配件的名称
                $arr[$temp_index]['comments_number'] = isset($row['comments_number']) ? $row['comments_number'] : 0;
                $arr[$temp_index]['sales_volume'] = $row['sales_volume'];
                $arr[$temp_index]['short_name'] = config('shop.goods_name_length') > 0 ? $this->dscRepository->subStr($row['goods_name'], config('shop.goods_name_length')) : $row['goods_name']; //配件显示的名称

                $row['goods_price'] = isset($row['goods_price']) ? $row['goods_price'] : 0;
                $arr[$temp_index]['fittings_price'] = $this->dscRepository->getPriceFormat($row['goods_price']); //配件价格

                if ($row['promote_price'] > 0) {
                    $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                } else {
                    $promote_price = 0;
                }

                if (isset($row['goods_attr_id']) && !empty($row['goods_attr_id'])) {
                    $prod_attr = explode(',', $row['goods_attr_id']);
                } else {
                    $prod_attr = [];
                }

                if (config('shop.add_shop_price') == 0) {
                    $add_tocart = 0;

                    if (empty($fittings_goods)) {
                        $fittings_goods = $row['goods_id'];
                    }

                    if (empty($fittings_attr)) {
                        $fittings_attr = $prod_attr;
                    }

                    $goods_price = $this->goodsCommonService->getFinalPrice($fittings_goods, 1, true, $fittings_attr, $warehouse_id, $area_id, $area_city, 0, 0, $add_tocart, 0, 0, $userRank);
                } else {
                    $goods_price = ($promote_price > 0) ? $promote_price : $row['shop_price'];
                }
                $arr[$temp_index]['market_price'] = $row['market_price'];

                $arr[$temp_index]['shop_price'] = $this->dscRepository->getPriceFormat($goods_price); //配件原价格
                $arr[$temp_index]['spare_price'] = $this->dscRepository->getPriceFormat(0); //节省的差价 by mike

                $minMax_price = $this->getGoodsNinMaxPrice($row['goods_id'], $user_rank, $discount, $warehouse_id, $area_id, $area_city, $goods_price, $row['market_price']); //配件价格min与max
                $arr[$temp_index]['fittings_minPrice'] = $minMax_price['goods_min'];
                $arr[$temp_index]['fittings_maxPrice'] = $minMax_price['goods_max'];

                $arr[$temp_index]['market_minPrice'] = $minMax_price['market_min'];
                $arr[$temp_index]['market_maxPrice'] = $minMax_price['market_max'];

                if (!empty($row['goods_attr_id'])) {
                    $prod_attr = explode(',', $row['goods_attr_id']);
                } else {
                    $prod_attr = [];
                }

                $attr_price = app(GoodsAttrService::class)->specPrice($prod_attr, $row['goods_id'], $warehouse_area);
                $arr[$temp_index]['attr_price'] = $attr_price;

                $arr[$temp_index]['shop_price_ori'] = $goods_price; //配件原价格 by mike
                $arr[$temp_index]['fittings_price_ori'] = 0; //配件价格 by mike
                $arr[$temp_index]['spare_price_ori'] = 0; //节省的差价 by mike

                $row['group_id'] = isset($row['group_id']) ? $row['group_id'] : 0; //套餐组 by mike
                $arr[$temp_index]['group_id'] = $row['group_id'];

                $img_flie = CartCombo::where('goods_id', $row['goods_id'])
                    ->where('group_id', $rev);

                if (!empty($user_id)) {
                    $img_flie = $img_flie->where('user_id', $user_id);
                } else {
                    $img_flie = $img_flie->where('session_id', $session_id);
                }

                $img_flie = $img_flie->value('img_flie');
                $arr[$temp_index]['img_flie'] = $img_flie;

                if (!empty($img_flie)) {
                    $arr[$temp_index]['goods_thumb'] = $arr[$temp_index]['img_flie'];
                } else {
                    $arr[$temp_index]['goods_thumb'] = $row['goods_thumb'];
                }

                $arr[$temp_index]['goods_img'] = $this->dscRepository->getImagePath($row['goods_img']);
                $arr[$temp_index]['goods_thumb'] = $this->dscRepository->getImagePath($arr[$temp_index]['goods_thumb']);
                $arr[$temp_index]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
                $arr[$temp_index]['attr_id'] = isset($row['goods_attr_id']) ? str_replace(',', '|', $row['goods_attr_id']) : '';

                $row['goods_attr_id'] = isset($row['goods_attr_id']) ? $row['goods_attr_id'] : '';

                $products = app(GoodsWarehouseService::class)->getWarehouseAttrNumber($row['goods_id'], $row['goods_attr_id'], $warehouse_id, $area_id, $area_city);
                $attr_number = $products ? $products['product_number'] : 0;

                if ($row['model_attr'] == 1) {
                    $prod = ProductsWarehouse::where('goods_id', $row['goods_id'])->where('warehouse_id', $warehouse_id);
                } elseif ($row['model_attr'] == 2) {
                    $prod = ProductsArea::where('goods_id', $row['goods_id'])->where('area_id', $area_id);

                    if (config('shop.area_pricetype') == 1) {
                        $prod = $prod->where('city_id', $area_city);
                    }
                } else {
                    $prod = Products::where('goods_id', $row['goods_id']);
                }

                $prod = BaseRepository::getToArrayFirst($prod);

                //当商品没有属性库存时
                if (empty($prod)) {
                    $attr_number = $row['goods_number'];
                }

                $attr_number = !empty($attr_number) ? $attr_number : 0;
                $arr[$temp_index]['goods_number'] = $attr_number;

                $arr[$temp_index]['properties'] = app(GoodsAttrService::class)->getGoodsProperties($row['goods_id'], $warehouse_id, $area_id, $area_city, $row['goods_attr_id']);

                $temp_index++;
            }
        }

        return $arr;
    }

    /**
     * 累加金额
     *
     * @param $save_price
     * @return int
     */
    public function getSaveMaxPrice($save_price)
    {
        $save_maxPrice = 0;
        if ($save_price) {
            foreach ($save_price as $key => $row) {
                $save_maxPrice += $row;
            }
        }

        return $save_maxPrice;
    }

    /**
     * 购物车组合购买商品配件列表
     *
     * @param int $goods_id
     * @param int $parent
     * @param string $group
     * @param int $user_id
     * @return array
     */
    public function getCartComboGoodsList($goods_id = 0, $parent = 0, $group = '', $user_id = 0)
    {
        if (empty($user_id)) {
            $user_id = session('user_id', 0);
        }

        $res = CartCombo::where('group_id', $group);

        $res = $res->where(function ($query) use ($parent) {
            $query->where('parent_id', $parent)
                ->orWhere(function ($query) use ($parent) {
                    $query->where('goods_id', $parent)
                        ->where('parent_id', 0);
                });
        });

        if (!empty($user_id)) {
            $res = $res->where('user_id', $user_id);
        } else {
            $session_id = $this->sessionRepository->realCartMacIp();
            $res = $res->where('session_id', $session_id);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        $arr['combo_amount'] = 0;
        $arr['combo_number'] = 0;

        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$key]['goods_number'] = $row['goods_number'];
                $arr[$key]['goods_price'] = $row['goods_price'];
                $arr[$key]['goods_id'] = $row['goods_id'];
                $arr['combo_amount'] += $row['goods_price'] * $row['goods_number'];
                $arr['combo_number'] += $row['goods_number'];
            }
        }

        $arr['shop_price'] = $arr['combo_amount'];
        $arr['combo_amount'] = $this->dscRepository->getPriceFormat($arr['combo_amount'], false);

        return $arr;
    }

    /**
     * 获取组合购买配件名称
     *
     * @return array
     */
    public function getCfgGroupGoods()
    {
        $group_goods = config('shop.group_goods');

        $arr = [];
        if (!empty($group_goods)) {
            $group_goods = explode(',', $group_goods);

            foreach ($group_goods as $key => $row) {
                $key += 1;
                $arr[$key] = $row;
            }
        }

        return $arr;
    }

    /**
     * 合并配件
     *
     * @param $fittings_index
     * @param $fittings
     * @return array
     */
    public function getMergeFittingsArray($fittings_index, $fittings)
    {
        $arr = [];
        if ($fittings_index) {
            for ($i = 1; $i <= count($fittings_index); $i++) {
                for ($j = 0; $j <= count($fittings); $j++) {
                    if (isset($fittings[$j]['group_id']) && isset($fittings_index[$i]) && $fittings_index[$i] == $fittings[$j]['group_id']) {
                        $arr[$i][$j] = $fittings[$j];
                    }
                }
            }
        }

        $arr = array_values($arr);
        return $arr;
    }

    /**
     * 配件数组列表
     *
     * @param $merge_fittings
     * @param $goods_fittings
     * @return array
     */
    public function getFittingsArrayList($merge_fittings, $goods_fittings)
    {
        $arr = [];
        if ($merge_fittings) {
            for ($i = 0; $i < count($merge_fittings); $i++) {
                $merge_fittings[$i] = array_merge($goods_fittings, $merge_fittings[$i]);
                $merge_fittings[$i] = array_values($merge_fittings[$i]);
                $arr[$i]['fittings_interval'] = $this->getChooseGoodsComboCart($merge_fittings[$i]);
            }
        }

        return $arr;
    }

    /**
     * 组合购买商品选择
     *
     * @param int $goods_id
     * @param int $parent
     * @param string $group
     * @return int
     */
    public function getComboGoodsListSelect($goods_id = 0, $parent = 0, $group = '')
    {
        $user_id = session('user_id', 0);

        //商品判断属性是否选完
        $res = CartCombo::select('rec_id', 'goods_id', 'group_id', 'goods_attr_id')
            ->where('group_id', $group);

        if (!empty($user_id)) {
            $res = $res->where('user_id', $user_id);
        } else {
            $session_id = $this->sessionRepository->realCartMacIp();
            $res = $res->where('session_id', $session_id);
        }

        $res = $res->where(function ($query) use ($parent) {
            $query->where('parent_id', $parent)
                ->orWhere(function ($query) use ($parent) {
                    $query->where('goods_id', $parent)
                        ->where('parent_id', 0);
                });
        });

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        $arr['attr_count'] = '';
        $attr_array = 0;
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$key]['rec_id'] = $row['rec_id'];
                $arr[$key]['goods_id'] = $row['goods_id'];
                $arr[$key]['group_id'] = $row['group_id'];
                $arr[$key]['goods_attr_id'] = $row['goods_attr_id'];

                $attr_count = GoodsAttr::where('goods_id', $goods_id)->count('goods_attr_id');
                $arr[$key]['attr_count'] = $attr_count;

                if (!empty($arr[$key]['goods_attr_id'])) {
                    $attr_count = count(explode(',', $arr[$key]['goods_attr_id']));
                } else {
                    $attr_count = 0;
                }

                if ($arr[$key]['attr_count'] > 0) {
                    if ($attr_count == $arr[$key]['attr_count']) {
                        $arr[$key]['yes_attr'] = 1;
                    } else {
                        $arr[$key]['yes_attr'] = 0;
                    }
                } else {
                    $arr[$key]['yes_attr'] = 1;
                }

                $arr['attr_count'] .= $arr[$key]['yes_attr'] . ",";
            }

            $attr_yes = explode(',', substr($arr['attr_count'], 0, -1));
            foreach ($attr_yes as $row) {
                $attr_array += $row;
            }
        }

        $goods_count = count($res);
        if ($attr_array == $goods_count) {
            return 1;
        } else {
            return 0;
        }
    }
}