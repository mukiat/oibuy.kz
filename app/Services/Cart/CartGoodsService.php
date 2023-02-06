<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\Goods;
use App\Models\GroupGoods;
use App\Models\OrderGoods;
use App\Models\Products;
use App\Models\ProductsArea;
use App\Models\ProductsWarehouse;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Flow\FlowRepository;
use App\Services\Category\CategoryDataHandleService;
use App\Services\Common\ConfigService;
use App\Services\Goods\GoodsAttrService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Package\PackageGoodsService;

class CartGoodsService
{
    protected $goodsCommonService;
    protected $sessionRepository;
    protected $dscRepository;
    protected $packageGoodsService;
    protected $merchantCommonService;
    protected $goodsAttrService;

    public function __construct(
        GoodsCommonService $goodsCommonService,
        SessionRepository $sessionRepository,
        DscRepository $dscRepository,
        PackageGoodsService $packageGoodsService,
        MerchantCommonService $merchantCommonService,
        GoodsAttrService $goodsAttrService
    )
    {
        $this->goodsCommonService = $goodsCommonService;
        $this->sessionRepository = $sessionRepository;
        $this->dscRepository = $dscRepository;
        $this->packageGoodsService = $packageGoodsService;
        $this->merchantCommonService = $merchantCommonService;
        $this->goodsAttrService = $goodsAttrService;
    }

    /**
     * 将指定订单中的商品添加到购物车
     *
     * @param int $order_id
     * @param array $rec_id
     * @return bool
     * @throws \Exception
     */
    public function returnToCart($order_id = 0, $rec_id = [])
    {
        $user_id = session('user_id', 0);
        $session_id = $this->sessionRepository->realCartMacIp();

        $sess = empty($user_id) ? $session_id : '';

        /* 初始化基本件数量 goods_id => goods_number */
        $basic_number = [];

        /* 查订单商品：不考虑赠品 */
        $res = OrderGoods::where('order_id', $order_id)
            ->where('is_gift', 0)
            ->where('extension_code', '<>', 'package_buy')
            ->orderBy('parent_id');

        $res = BaseRepository::getToArrayGet($res);

        $user_rank = session('user_rank', 0);
        $discount = session('discount', 1);

        if ($res) {
            foreach ($res as $row) {
                // 查该商品信息：是否删除、是否上架
                $goods = Goods::where('goods_id', $row['goods_id'])
                    ->where('is_delete', 0);

                $where = [
                    'area_id' => $row['area_id'],
                    'area_city' => $row['area_city']
                ];

                $goods = $goods->with([
                    'getMemberPrice' => function ($query) use ($user_rank) {
                        $query->where('user_rank', $user_rank);
                    },
                    'getWarehouseGoods' => function ($query) use ($row) {
                        $query->where('region_id', $row['warehouse_id']);
                    },
                    'getWarehouseAreaGoods' => function ($query) use ($where) {
                        $query = $query->where('region_id', $where['area_id']);

                        if (config('shop.area_pricetype') == 1) {
                            $query->where('city_id', $where['area_city']);
                        }
                    }
                ]);

                $goods = BaseRepository::getToArrayFirst($goods);

                // 如果该商品不存在，处理下一个商品
                if (empty($goods) || (!empty($rec_id) && !in_array($row['rec_id'], $rec_id))) {
                    continue;
                }

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

                $price = $this->goodsCommonService->getGoodsPrice($price, $discount, $goods);

                $goods['shop_price'] = $price['shop_price'];
                $goods['promote_price'] = $price['promote_price'];
                $goods['goods_number'] = $price['goods_number'];

                if ($goods['promote_price'] > 0) {
                    $goods['promote_price'] = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                } else {
                    $goods['promote_price'] = 0;
                }

                if ($goods['promote_price'] > 0) {
                    $goods['goods_price'] = $goods['promote_price'];
                } else {
                    $goods['goods_price'] = $goods['shop_price'];
                }

                $product_number = 0;
                if ($row['product_id']) {
                    $order_goods_product_id = $row['product_id'];

                    if ($row['model_attr'] == 1) {
                        $res = ProductsWarehouse::whereRaw(1);
                    } elseif ($row['model_attr'] == 2) {
                        $res = ProductsArea::whereRaw(1);
                    } else {
                        $res = Products::whereRaw(1);
                    }

                    $product_number = $res->where('product_id', $order_goods_product_id)->value('product_number');
                    $product_number = $product_number ? $product_number : 0;
                }

                // 如果使用库存，且库存不足，修改数量
                if (config('shop.use_storage') == 1 && ($row['product_id'] ? ($product_number < $row['goods_number']) : ($goods['goods_number'] < $row['goods_number']))) {
                    if ($goods['goods_number'] == 0 || $product_number === 0) {
                        // 如果库存为0，处理下一个商品
                        continue;
                    } else {
                        if ($row['product_id']) {
                            $row['goods_number'] = $product_number;
                        } else {
                            // 库存不为0，修改数量
                            $row['goods_number'] = $goods['goods_number'];
                        }
                    }
                }

                //检查商品价格是否有会员价格
                $temp_number = Cart::where('goods_id', $row['goods_id'])
                    ->where('rec_type', CART_GENERAL_GOODS);

                if (!empty($user_id)) {
                    $temp_number = $temp_number->where('user_id', $user_id);
                } else {
                    $temp_number = $temp_number->where('session_id', $session_id);
                }

                $temp_number = $temp_number->value('goods_number');
                $temp_number = $temp_number ? $temp_number : 0;

                $row['goods_number'] += $temp_number;

                $attr_array = empty($row['goods_attr_id']) ? [] : explode(',', $row['goods_attr_id']);
                $goods['goods_price'] = $this->goodsCommonService->getFinalPrice($row['goods_id'], $row['goods_number'], true, $attr_array, $row['warehouse_id'], $row['area_id'], $row['area_city']);

                // 要返回购物车的商品
                $return_goods = [
                    'goods_id' => $row['goods_id'],
                    'goods_sn' => addslashes($goods['goods_sn']),
                    'goods_name' => addslashes($goods['goods_name']),
                    'market_price' => $goods['market_price'],
                    'product_id' => $row['product_id'],
                    'goods_price' => $goods['goods_price'],
                    'warehouse_id' => $row['warehouse_id'],
                    'area_id' => $row['area_id'],
                    'ru_id' => $row['ru_id'],
                    'model_attr' => $row['model_attr'],
                    'shopping_fee' => $row['shopping_fee'],
                    'goods_number' => $row['goods_number'],
                    'goods_attr' => empty($row['goods_attr']) ? '' : addslashes($row['goods_attr']),
                    'goods_attr_id' => empty($row['goods_attr_id']) ? '' : $row['goods_attr_id'],
                    'is_real' => $goods['is_real'],
                    'extension_code' => addslashes($goods['extension_code']),
                    'parent_id' => '0',
                    'is_gift' => '0',
                    'rec_type' => CART_GENERAL_GOODS
                ];

                // 如果是配件
                if ($row['parent_id'] > 0) {
                    // 查询基本件信息：是否删除、是否上架、能否作为普通商品销售
                    $parent = Goods::where('goods_id', $row['parent_id'])
                        ->where('is_delete', 0)
                        ->where('is_on_sale', 1)
                        ->where('is_alone_sale', 1)
                        ->count();

                    if ($parent) {
                        // 如果基本件存在，查询组合关系是否存在
                        $fitting_price = GroupGoods::where('parent_id', $row['parent_id'])
                            ->where('goods_id', $row['goods_id'])
                            ->value('goods_price');

                        if ($fitting_price) {
                            // 如果组合关系存在，取配件价格，取基本件数量，改parent_id
                            $return_goods['parent_id'] = $row['parent_id'];
                            $return_goods['goods_price'] = $fitting_price;
                            $return_goods['goods_number'] = $basic_number[$row['parent_id']];
                        }
                    }
                } else {
                    // 保存基本件数量
                    $basic_number[$row['goods_id']] = $row['goods_number'];
                }

                // 返回购物车：看有没有相同商品
                $cart_goods = Cart::where('goods_id', $return_goods['goods_id'])
                    ->where('goods_attr', $return_goods['goods_attr'])
                    ->where('parent_id', $return_goods['parent_id'])
                    ->where('is_gift', 0)
                    ->where('rec_type', CART_GENERAL_GOODS)
                    ->value('goods_id');

                if (!empty($user_id)) {
                    $cart_goods = $cart_goods->where('user_id', $user_id);
                } else {
                    $cart_goods = $cart_goods->where('session_id', $session_id);
                }

                $cart_goods = $cart_goods->count();

                if (empty($cart_goods)) {
                    // 没有相同商品，插入
                    $return_goods['session_id'] = $sess;
                    $return_goods['user_id'] = session('user_id', 0);

                    Cart::insert($return_goods);
                } else {
                    // 有相同商品，修改数量
                    $res = Cart::where('goods_id', $return_goods['goods_id'])
                        ->where('rec_type', CART_GENERAL_GOODS);

                    if (!empty($user_id)) {
                        $res = $res->where('user_id', $user_id);
                    } else {
                        $res = $res->where('session_id', $session_id);
                    }

                    $other = [
                        'goods_number' => $return_goods['goods_number']
                    ];

                    if ($return_goods['goods_price'] > 0) {
                        $other['goods_price'] = $return_goods['goods_price'];
                    }

                    $res->update($other);
                }
            }
        }

        // 清空购物车的赠品
        $del = Cart::where('is_gift', 1);

        if (!empty($user_id)) {
            $del = $del->where('user_id', $user_id);
        } else {
            $del = $del->where('session_id', $session_id);
        }

        $del->delete();

        return true;
    }

    /**
     * @param array $where
     * @param int $type 0，查关联数据|1，只查本身数据
     * @return mixed
     * @throws \Exception
     */
    public function getGoodsCartList($where = [], $type = 0)
    {
        $source_domestic = ConfigService::searchSourceDomestic();
        $user_rank = isset($where['user_rank']) ? $where['user_rank'] : session('user_rank');

        if (isset($where['user_id'])) {
            $user_id = $where['user_id'];
        } else {
            $user_id = session('user_id', 0);
        }

        $store_id = $where['store_id'] ?? 0;

        if ($store_id > 0) {
            $where['rec_type'] = CART_OFFLINE_GOODS;
        } else {
            $where['rec_type'] = isset($where['rec_type']) ? $where['rec_type'] : CART_GENERAL_GOODS;
        }

        $res = Cart::where('goods_number', '>', 0);

        if (!(isset($where['type']) && $where['type'] == 1)) {
            $res = $res->where('rec_type', $where['rec_type']);
        }

        if (!empty($user_id)) {
            $res = $res->where('user_id', $user_id);
        } else {
            $session_id = $this->sessionRepository->realCartMacIp();
            $res = $res->where('session_id', $session_id);
        }

        if (isset($where['is_checked'])) {
            $res = $res->where('is_checked', $where['is_checked']);
        }

        /* 附加查询条件 start */
        if (isset($where['rec_id']) && $where['rec_id']) {
            $where['rec_id'] = BaseRepository::getExplode($where['rec_id']);
            $res = $res->whereIn('rec_id', $where['rec_id']);
        }

        if (isset($where['goods_id']) && $where['goods_id']) {
            $where['goods_id'] = !is_array($where['goods_id']) ? explode(",", $where['goods_id']) : $where['goods_id'];
            $res = $res->whereIn('goods_id', $where['goods_id']);
        }

        if (isset($where['stages_qishu'])) {
            $res = $res->where('stages_qishu', $where['stages_qishu']);
        }

        if ($store_id > 0) {
            $res = $res->where('store_id', $store_id);
        } else {
            if ($where['rec_type'] != CART_OFFLINE_GOODS) {
                $res = $res->where('store_id', 0);
            }
        }

        if (isset($where['extension_code'])) {
            $res = $res->where('extension_code', '<>', $where['extension_code']);
        }

        if (isset($where['parent_id'])) {
            $res = $res->where('parent_id', $where['parent_id']);
        }

        if (isset($where['is_gift'])) {
            $res = $res->where('is_gift', $where['is_gift']);
        }
        /* 附加查询条件 end */

        if ($type == 0) {
            if (isset($where['limit']) && $where['limit']) {
                $res = $res->take($where['limit']);
            }
        }

        $res = $res->orderByRaw("group_id DESC, parent_id ASC, rec_id DESC");
        $res = BaseRepository::getToArrayGet($res);

        if ($res && $type == 0) {

            $cartIdList = FlowRepository::cartGoodsAndPackage($res);
            $goods_id = $cartIdList['goods_id']; //普通商品ID
            $package_goods_id = $cartIdList['package_goods_id']; //超值礼包ID

            $sql = [
                'whereIn' => [
                    [
                        'name' => 'goods_id',
                        'value' => $goods_id
                    ]
                ]
            ];
            $res = BaseRepository::getArraySqlGet($res, $sql);

            $goodsWhere = [
                'goods_select' => [
                    'goods_id', 'user_id', 'cat_id', 'brand_id', 'goods_name', 'goods_thumb', 'freight', 'tid', 'goods_weight',
                    'shipping_fee', 'cloud_id', 'cloud_goodsname', 'model_price', 'integral', 'is_delete', 'shop_price',
                    'is_discount', 'is_promote', 'promote_price', 'promote_start_date', 'promote_end_date', 'is_real', 'extension_code',
                    'shop_price as rec_shop_price', 'promote_price as rec_promote_price'
                ]
            ];

            if (CROSS_BORDER === true) {
                // 跨境多商户
                array_push($goodsWhere['goods_select'], 'free_rate');
            }

            if (file_exists(MOBILE_DRP)) {
                array_push($goodsWhere['goods_select'], 'dis_commission', 'is_distribution', 'membership_card_id');
            }

            $goodsList = GoodsDataHandleService::GoodsCartDataList($goods_id, $goodsWhere);
            $goodsConsumptionList = GoodsDataHandleService::GoodsConsumptionDataList($goods_id);
            $memberPrice = GoodsDataHandleService::goodsMemberPrice($goods_id, $user_rank);

            $recGoodsModelList = BaseRepository::getColumn($res, 'model_attr', 'rec_id');
            $recGoodsModelList = $recGoodsModelList ? array_unique($recGoodsModelList) : [];

            $isModel = 0;
            if (in_array(1, $recGoodsModelList) || in_array(2, $recGoodsModelList)) {
                $isModel = 1;
            }

            if ($isModel == 1) {
                $warehouseGoodsList = GoodsDataHandleService::getWarehouseGoodsDataList($goods_id);
                $warehouseAreaGoodsList = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods_id);
            } else {
                $warehouseGoodsList = [];
                $warehouseAreaGoodsList = [];
            }

            $product_id = BaseRepository::getKeyPluck($res, 'product_id');
            $productsList = GoodsDataHandleService::getProductsDataList($product_id, '*', 'product_id');

            if ($isModel == 1) {
                $productsWarehouseList = GoodsDataHandleService::getProductsWarehouseDataList($product_id, 0, '*', 'product_id');
                $productsAreaList = GoodsDataHandleService::getProductsAreaDataList($product_id, 0, '*', 'product_id');
            } else {
                $productsWarehouseList = [];
                $productsAreaList = [];
            }

            $productsGoodsAttrList = [];
            if ($productsList || $productsWarehouseList || $productsAreaList) {
                $productsGoodsAttr = BaseRepository::getKeyPluck($productsList, 'goods_attr');
                $productsWarehouseGoodsAttr = BaseRepository::getKeyPluck($productsList, 'goods_attr');
                $productsAreaGoodsAttr = BaseRepository::getKeyPluck($productsList, 'goods_attr');

                $productsGoodsAttr = BaseRepository::getArrayMerge($productsGoodsAttr, $productsWarehouseGoodsAttr);
                $productsGoodsAttr = BaseRepository::getArrayMerge($productsGoodsAttr, $productsAreaGoodsAttr);

                $productsGoodsAttrList = GoodsDataHandleService::getGoodsAttrDataList($productsGoodsAttr, ['goods_attr_id', 'attr_img_flie', 'attr_gallery_flie']);
            }

            $goodsActivity = GoodsDataHandleService::getGoodsActivityDataList($package_goods_id, GAT_PACKAGE);

            /* 处理更新价格 */
            $payPriceList = app(CartCommonService::class)->cartFinalPrice($user_id, $res, $goodsList, $warehouseGoodsList, $warehouseAreaGoodsList, $productsList, $productsWarehouseList, $productsAreaList);

            $seller_id = BaseRepository::getKeyPluck($res, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($seller_id);

            $sourceList = MerchantDataHandleService::getMerchantsStepsFieldsDataList($seller_id, ['user_id', 'source']);
            $catIdList = BaseRepository::getKeyPluck($goodsList, 'cat_id');
            $catList = CategoryDataHandleService::getCategoryDataList($catIdList, ['cat_id', 'rate']);

            foreach ($res as $k => $v) {

                $goodsSelf = false;
                if ($v['ru_id'] == 0) {
                    $goodsSelf = true;
                }

                $shop_information = $merchantList[$v['ru_id']] ?? []; //通过ru_id获取到店铺信息;

                $goods = $goodsList[$v['goods_id']] ?? [];

                /* 为兼容跨境代码写法 */
                $v['get_goods'] = $goods;

                $warehouseGoods = $warehouseGoodsList[$v['goods_id']] ?? [];
                $sql = [
                    'where' => [
                        [
                            'name' => 'region_id',
                            'value' => $v['warehouse_id']
                        ]
                    ]
                ];
                $warehouseGoods = BaseRepository::getArraySqlFirst($warehouseGoods, $sql);
                $v['get_warehouse_goods'] = $warehouseGoods;

                $areaGoods = $warehouseAreaGoodsList[$v['goods_id']] ?? [];
                $sql = [
                    'where' => [
                        [
                            'name' => 'area_id',
                            'value' => $v['area_id']
                        ]
                    ]
                ];

                if (config('shop.area_pricetype') == 1) {
                    $sql['where'][] = [
                        'name' => 'city_id',
                        'value' => $v['area_city']
                    ];
                }
                $areaGoods = BaseRepository::getArraySqlFirst($areaGoods, $sql);
                $v['get_warehouse_area_goods'] = $areaGoods;

                if ($v['model_attr'] == 1) {
                    $v['sku_weight'] = $productsWarehouseList[$v['product_id']]['sku_weight'] ?? 0; //货品重量
                    $integral = $warehouseGoods['pay_integral'] ?? 0;
                } elseif ($v['model_attr'] == 2) {
                    $v['sku_weight'] = $productsAreaList[$v['product_id']]['sku_weight'] ?? 0; //货品重量
                    $integral = $areaGoods['pay_integral'] ?? 0;
                } else {
                    $v['sku_weight'] = $productsList[$v['product_id']]['sku_weight'] ?? 0; //货品重量
                    $integral = $goods['integral'] ?? 0;
                }

                /**
                 * 取最小兑换积分
                 */
                $integral = [
                    $this->dscRepository->integralOfValue($v['goods_price'] * $v['goods_number']),
                    $this->dscRepository->integralOfValue($integral * $v['goods_number'])
                ];

                $integral = BaseRepository::getArrayMin($integral);
                $v['integral_total'] = $this->dscRepository->valueOfIntegral($integral);

                $v['get_member_price'] = $memberPrice[$v['goods_id']] ?? [];

                $res[$k] = $v;

                $res[$k]['rate_price'] = 0;

                //判断商品类型，如果是超值礼包则修改链接和缩略图
                if ($v['extension_code'] == 'package_buy') {
                    $res[$k]['url'] = 'package.php';

                    $package = $goodsActivity[$v['goods_id']] ?? [];

                    if (empty($package)) {
                        //移除无效的超值礼包
                        $delPackage = Cart::where('goods_id', $v['goods_id'])->where('extension_code', 'package_buy');

                        if (!empty($user_id)) {
                            $delPackage = $delPackage->where('user_id', $user_id);
                        } else {
                            $session_id = $this->sessionRepository->realCartMacIp();
                            $delPackage = $delPackage->where('session_id', $session_id);
                        }

                        $delPackage->delete();

                        unset($res[$k]);
                        continue;
                    }

                    if ($package) {
                        $res[$k]['goods_thumb'] = !empty($package['activity_thumb']) ? $this->dscRepository->getImagePath($package['activity_thumb']) : $this->dscRepository->dscUrl('themes/ecmoban_dsc2017/images/17184624079016pa.jpg');
                    }

                    $res[$k]['package_goods_list'] = $this->packageGoodsService->getPackageGoods($v['goods_id']);
                } else {

                    $is_kj = 0;
                    if (CROSS_BORDER === true) { // 跨境多商户
                        $source = $sourceList[$v['ru_id']]['source'] ?? '';
                        if ($source && !in_array($source, [$source_domestic])) {
                            $is_kj = 1;
                        }
                    }

                    //跨境税金
                    if ($is_kj == 1 && $v['store_id'] == 0 && $goods['free_rate'] == 0) {
                        $rate = $catList[$goods['cat_id']]['rate'] ?? 0;
                        $res[$k]['rate_price'] = round(($v['goods_price'] * $v['goods_number']) * ($rate / 100), 2);
                    }

                    $res[$k]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $v['goods_id']], $v['goods_name']);
                    $res[$k]['goods_thumb'] = $this->dscRepository->getImagePath($goods['goods_thumb']);
                }

                // 属性图片
                if (!empty($v['product_id'])) { // 属性库存
                    if ($v['model_attr'] == 1) {
                        $prod = $productsWarehouseList[$v['product_id']] ?? [];
                    } elseif ($v['model_attr'] == 2) {
                        $prod = $productsAreaList[$v['product_id']] ?? [];
                    } else {
                        $prod = $productsList[$v['product_id']] ?? [];
                    }

                    $goods_attr_id = $prod['goods_attr'] ?? '';
                    $goods_attr_id = BaseRepository::getExplode($goods_attr_id, '|');
                    $res[$k]['goods_thumb'] = $this->goodsAttrService->cartGoodsAttrImage($goods_attr_id, $productsGoodsAttrList, $res[$k]['goods_thumb']);
                }

                // 更新购物车商品价格 - 普通商品
                if (in_array($v['rec_type'], [CART_GENERAL_GOODS, CART_ONESTEP_GOODS]) && $v['extension_code'] != 'package_buy' && $v['is_gift'] == 0 && $v['parent_id'] == 0) {
                    $goods_price = $payPriceList[$v['rec_id']]['pay_price'] ?? 0;
                    if ($v['goods_price'] != $goods_price) {
                        CartCommonService::getUpdateCartPrice($goods_price, $v['rec_id']);

                        $v['goods_price'] = $goods_price;
                    }
                }

                // 购物车商品信息
                $res[$k]['short_name'] = config('shop.goods_name_length') > 0 ? $this->dscRepository->subStr($v['goods_name'], config('shop.goods_name_length')) : $v['goods_name'];
                $res[$k]['goods_number'] = $v['goods_number'];
                $res[$k]['goods_name'] = $v['goods_name'];
                $res[$k]['cart_price'] = $v['goods_price'];
                $res[$k]['goods_price'] = $v['goods_price'];
                $res[$k]['shop_price'] = $this->dscRepository->getPriceFormat($res[$k]['goods_price'], true, true, $goodsSelf);
                $res[$k]['subtotal'] = $v['goods_price'] * $v['goods_number'];
                $res[$k]['format_subtotal'] = $this->dscRepository->getPriceFormat($res[$k]['subtotal'], true, true, $goodsSelf);
                $res[$k]['warehouse_id'] = $v['warehouse_id'];
                $res[$k]['area_id'] = $v['area_id'];
                $res[$k]['rec_id'] = $v['rec_id'];
                $res[$k]['extension_code'] = $v['extension_code'];
                $res[$k]['is_gift'] = $v['is_gift'];
                $res[$k]['shop_name'] = $shop_information['shop_name'] ?? '';
                $res[$k]['add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $v['add_time']);
                $res[$k]['tid'] = $goods['tid'] ?? 0;

                $res[$k]['country_icon'] = $shop_information['country_icon'] ?? '';
                $res[$k]['cross_warehouse_name'] = $shop_information['cross_warehouse_name'] ?? '';

                // 购物车商品总价 = 购物车商品价格 * 购物车商品数理
                $row['goods_amount'] = $res[$k]['goods_price'] * $v['goods_number'];

                // 商品满减金额
                $goodsConsumption = $goodsConsumptionList[$v['goods_id']] ?? [];
                if ($goodsConsumption) {
                    $res[$k]['amount'] = $this->dscRepository->getGoodsConsumptionPrice($goodsConsumption, $row['goods_amount']);
                } else {
                    $res[$k]['amount'] = $row['goods_amount'];
                }

                $res[$k]['subtotal'] = $row['goods_amount'];
                $res[$k]['formated_subtotal'] = $this->dscRepository->getPriceFormat($row['goods_amount'], false, true, $goodsSelf);
                $res[$k]['dis_amount'] = $row['goods_amount'] - $res[$k]['amount'];
                $res[$k]['dis_amount'] = $this->dscRepository->changeFloat($res[$k]['dis_amount']);
                $res[$k]['discount_amount'] = $this->dscRepository->getPriceFormat($res[$k]['dis_amount'], false, true, $goodsSelf);

                $res[$k]['is_delete'] = $goods['is_delete'] ?? 0;
                $res[$k]['cat_id'] = $goods['cat_id'] ?? 0;
                $res[$k]['brand_id'] = $goods['brand_id'] ?? 0;
                $res[$k]['goods_weight'] = $goods['goods_weight'] ?? 0;
                $res[$k]['shipping_fee'] = $goods['shipping_fee'] ?? 0;
                $res[$k]['cloud_id'] = $goods['cloud_id'] ?? 0;
                $res[$k]['cloud_goodsname'] = $goods['cloud_goodsname'] ?? 0;

                $res[$k]['shop_price'] = $goods['shop_price'] ?? 0; // 商品原价
                $res[$k]['is_promote'] = $goods['is_promote'] ?? 0;
                $res[$k]['promote_price'] = $goods['promote_price'] ?? 0;
                $res[$k]['promote_start_date'] = $goods['promote_start_date'] ?? 0;
                $res[$k]['promote_end_date'] = $goods['promote_end_date'] ?? 0;

                $res[$k]['is_discount'] = $goods['is_discount'] ?? 0; // 是否禁止参与会员特价权益

                if (CROSS_BORDER === true) {
                    $res[$k]['free_rate'] = $goods['free_rate'] ?? 0;
                }

                if (file_exists(MOBILE_DRP)) {
                    $res[$k]['dis_commission'] = $goods['dis_commission'] ?? 0;
                    $res[$k]['is_distribution'] = $goods['is_distribution'] ?? 0;
                    $res[$k]['membership_card_id'] = $goods['membership_card_id'] ?? 0;
                }
            }

            /* 检查是否含有配件 */
            $grouplist = BaseRepository::getKeyPluck($res, 'group_id');
            $grouplist = ArrRepository::getArrayUnset($grouplist);

            if ($grouplist) {
                //过滤商品的配件 配件的数组新增到配件主商品的商品数组中
                $res = app(CartCommonService::class)->cartGoodsGroupList($res);
            }
        }

        return $res;
    }

    /**
     * 删除购物车商品
     *
     * @return array
     * @throws \Exception
     */
    public function ajaxDeleteCartGoods()
    {
        $result = ['error' => 0, 'message' => '', 'content' => '', 'goods_id' => '', 'index' => -1];
        $result['index'] = (int)request()->input('index', 0);
        $rec_id = (int)request()->input('id', 0);

        $session_id = $this->sessionRepository->realCartMacIp();
        $user_id = session('user_id', 0);

        $delete = Cart::where('rec_id', $rec_id);

        if ($user_id > 0) {
            $delete = $delete->where('user_id', $user_id);
        } else {
            $delete = $delete->where('session_id', $session_id);
        }

        $delete->delete();

        $where = [
            'rec_type' => CART_GENERAL_GOODS
        ];
        $arr = $this->getGoodsCartList($where);

        $row = Cart::selectRaw("*, COUNT(*) AS cart_number, SUM(goods_number) AS number, SUM(goods_price * goods_number) AS amount")
            ->where('rec_type', CART_GENERAL_GOODS);

        if (!empty($user_id)) {
            $row = $row->where('user_id', $user_id);
        } else {
            $row = $row->where('session_id', $session_id);
        }

        $row = BaseRepository::getToArrayFirst($row);

        if ($row) {
            $number = intval($row['number']);
            $amount = floatval($row['amount']);
        } else {
            $number = 0;
            $amount = 0;
        }

        $result['cart_num'] = $number;

        $GLOBALS['smarty']->assign('str', sprintf(lang('common.cart_info'), $number, $this->dscRepository->getPriceFormat($amount, false)));
        $GLOBALS['smarty']->assign('goods', $arr);

        $urlHtml = $this->dscRepository->getUrlHtml(['flow']);
        $GLOBALS['smarty']->assign('urlHtml', $urlHtml);
        $cart_info = ['goods_list' => $arr, 'number' => $number, 'amount' => $this->dscRepository->getPriceFormat($amount, false)];
        $GLOBALS['smarty']->assign('cart_info', $cart_info);

        $result['content'] = $GLOBALS['smarty']->fetch('library/cart_info.lbi');
        $result['cart_content'] = $GLOBALS['smarty']->fetch('library/cart_menu_info.lbi');

        return $result;
    }
}
