<?php

namespace App\Services\Goods;

use App\Models\FavourableActivity;
use App\Models\Goods;
use App\Models\GoodsCat;
use App\Models\MemberPrice;
use App\Models\OrderGoods;
use App\Models\Products;
use App\Models\ProductsArea;
use App\Models\ProductsWarehouse;
use App\Models\UserRank;
use App\Models\WarehouseAreaGoods;
use App\Models\WarehouseGoods;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Goods\GoodsLabelRepository;
use App\Repositories\Goods\GoodsServicesLabelRepository;
use App\Services\Category\CategoryService;

class GoodsCommonService
{
    protected $goodsWarehouseService;
    protected $goodsAttrService;
    protected $dscRepository;

    public function __construct(
        GoodsWarehouseService $goodsWarehouseService,
        GoodsAttrService $goodsAttrService,
        DscRepository $dscRepository
    )
    {
        $this->goodsWarehouseService = $goodsWarehouseService;
        $this->goodsAttrService = $goodsAttrService;
        $this->dscRepository = $dscRepository;
    }

    /**
     * 商品URL地址
     *
     * @param $params
     * @return string
     */
    public function goodsUrl($params)
    {
        return route('goods', $params);
    }

    /**
     * 添加商品名样式
     * @param string $goods_name 商品名称
     * @param string $style 样式参数
     * @return  string
     */
    public function addStyle($goods_name, $style)
    {
        $goods_style_name = $goods_name;

        $arr = explode('+', $style);

        $font_color = !empty($arr[0]) ? $arr[0] : '';
        $font_style = !empty($arr[1]) ? $arr[1] : '';

        if ($font_color != '') {
            $goods_style_name = '<font style="color:' . $font_color . '; font-size:inherit;">' . $goods_style_name . '</font>';
        }
        if ($font_style != '') {
            $goods_style_name = '<' . $font_style . '>' . $goods_style_name . '</' . $font_style . '>';
        }
        return $goods_style_name;
    }

    /**
     * 获取商品判断最终价格
     *
     * @param array $price
     * @param int $discount
     * @param array $goods
     * @return array
     */
    public function getGoodsPrice($price = [], $discount = 0, $goods = [])
    {
        // 商家商品禁用会员权益折扣
        if (isset($goods['user_id']) && $goods['user_id'] > 0 && isset($goods['is_discount']) && $goods['is_discount'] == 0) {
            $discount = 1;
        }

        /* 本店价 */
        if (isset($price['user_price']) && $price['user_price'] > 0) {
            // 会员价格
            if (isset($price['percentage']) && $price['percentage'] == 1) {
                $shop_price = $price['shop_price'] * $price['user_price'] / 100; // 百分比
            } else {
                $shop_price = $price['user_price']; // 固定价格
            }
        } else {
            if (isset($price['warehouse_price']) && $price['model_price'] == 1) {
                $shop_price = $price['warehouse_price'] * $discount;
            } elseif (isset($price['region_price']) && $price['model_price'] == 2) {
                $shop_price = $price['region_price'] * $discount;
            } else {
                $shop_price = $price['shop_price'] * $discount;
            }
        }
        $shop_price = number_format($shop_price, 2, '.', '');

        /* 促销价 */
        if (isset($price['warehouse_promote_price']) && $price['model_price'] == 1) {
            $promote_price = $price['warehouse_promote_price'];
        } elseif (isset($price['region_promote_price']) && $price['model_price'] == 2) {
            $promote_price = $price['region_promote_price'];
        } else {
            $promote_price = $price['promote_price'];
        }
        $promote_price = number_format($promote_price, 2, '.', '');

        /* 消费积分 */
        if (isset($price['wpay_integral']) && $price['model_price'] == 1) {
            $integral = $price['wpay_integral'];
        } elseif (isset($price['apay_integral']) && $price['model_price'] == 2) {
            $integral = $price['apay_integral'];
        } else {
            $integral = isset($price['integral']) ? $price['integral'] : 0;
        }

        /* 库存 */
        if (isset($price['wg_number']) && $price['model_price'] == 1) {
            $goods_number = $price['wg_number'];
        } elseif (isset($price['wag_number']) && $price['model_price'] == 2) {
            $goods_number = $price['wag_number'];
        } else {
            $goods_number = isset($price['goods_number']) ? $price['goods_number'] : 0;
        }

        $price['shop_price'] = number_format($price['shop_price'], 2, '.', '');
        $price['promote_price'] = number_format($price['promote_price'], 2, '.', '');

        $arr = [
            'shop_price' => $shop_price > 0 ? $shop_price : $price['shop_price'] * $discount,
            'promote_price' => $promote_price > 0 ? $promote_price : $price['promote_price'],
            'integral' => $integral,
            'goods_number' => $goods_number,
            'model_price' => $price['model_price'] ?? 0,
            'user_price' => $price['user_price'] ?? 0,
            'percentage' => $price['percentage'] ?? 0
        ];

        return $arr;
    }

    /**
     * 判断某个商品是否正在特价促销期
     *
     * @access  public
     * @param float $price 促销价格
     * @param string $start 促销开始日期
     * @param string $end 促销结束日期
     * @return  float   如果还在促销期则返回促销价，否则返回0
     */
    public function getBargainPrice($price, $start, $end)
    {
        if ($price == 0) {
            return 0;
        } else {
            $time = TimeRepository::getGmTime();
            if ($time >= $start && $time <= $end) {
                return $price;
            } else {
                return 0;
            }
        }
    }

    /**
     * 取得商品最终使用价格
     *
     * @param $goods_id 商品编号
     * @param string $goods_num 购买数量
     * @param bool $is_spec_price 是否加入规格价格
     * @param array $spec 规格ID的数组或者逗号分隔的字符串
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param int $type
     * @param int $presale
     * @param int $add_tocart 0,1  1代表非购物车进入该方法（SKU价格）
     * @param int $show_goods 0,1  商品详情页ajax，1代表SKU价格开启（SKU价格）
     * @param int $product_promote_price
     * @param array $rank
     * @return float|int|mixed|string
     * @throws \Exception
     */
    public function getFinalPrice($goods_id, $goods_num = '1', $is_spec_price = false, $spec = [], $warehouse_id = 0, $area_id = 0, $area_city = 0, $type = 0, $presale = 0, $add_tocart = 1, $show_goods = 0, $product_promote_price = 0, $rank = [])
    {
        $final_price = 0; //商品最终购买价格
        $volume_price = 0; //商品优惠价格
        $promote_price = 0; //商品促销价格
        $user_price = 0; //商品会员价格
        $spec_price = 0;

        if ($is_spec_price && !empty($spec)) {
            $warehouse_area['warehouse_id'] = $warehouse_id;
            $warehouse_area['area_id'] = $area_id;
            $warehouse_area['area_city'] = $area_city;
            $spec_price = $this->goodsAttrService->specPrice($spec, $goods_id, $warehouse_area);
        }

        //取得商品优惠价格列表
        $price_list = $this->getVolumePriceList($goods_id);
        if (!empty($price_list)) {
            foreach ($price_list as $value) {
                if ($goods_num >= $value['number']) {
                    $volume_price = $value['price'];
                }
            }
        }

        //预售条件---预售没有会员价、折扣价
        $is_presale = Goods::where('goods_id', $goods_id)->where('is_on_sale', 0)->where('is_alone_sale', 1)->where('is_delete', 0);
        $is_presale = $is_presale->whereHasIn('getPresaleActivity', function ($query) {
            $query->where('review_status', 3);
        });
        $is_presale = $is_presale->count('goods_id');

        if ($is_presale > 0 || $presale == 1) {
            $user_rank = 1;
            $discount = 1; //会员折扣
        } else {
            if ($rank) {
                $user_rank = $rank['rank_id'] ?? 1;
                $discount = $rank['discount'] ?? 1;
            } else {
                $user_rank = session('user_rank', 0); //用户等级
                $discount = session('discount', 1); //会员折扣
            }
        }

        $now_promote = 0;
        if ($presale != CART_PACKAGE_GOODS) {
            /* 取得商品信息 */
            $goods = Goods::where('goods_id', $goods_id);

            $where = [
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

                $price = $this->getGoodsPrice($price, $discount, $goods);

                $goods['user_price'] = $price['user_price'];
                $goods['shop_price'] = $price['shop_price'];
                $goods['promote_price'] = $price['promote_price'];
                $goods['goods_number'] = $price['goods_number'];
            }

            $goods['user_id'] = isset($goods['user_id']) ? $goods['user_id'] : 0;

            if (config('shop.add_shop_price') == 0 && $product_promote_price <= 0) {
                $product_spec = !empty($spec) && is_array($spec) ? implode(",", $spec) : '';
                $products = $this->goodsWarehouseService->getWarehouseAttrNumber($goods_id, $product_spec, $warehouse_id, $area_id, $area_city);
                $product_promote_price = isset($products['product_promote_price']) ? $products['product_promote_price'] : 0;
            }

            $time = TimeRepository::getGmTime();

            //当前商品正在促销时间内
            if (isset($goods['promote_start_date']) && isset($goods['promote_end_date'])) {
                if ($time >= $goods['promote_start_date'] && $time <= $goods['promote_end_date'] && $goods['is_promote']) {
                    $now_promote = 1;
                }
            }

            if (config('shop.add_shop_price') == 0 && $now_promote == 1 && $spec) {
                $goods['promote_price'] = $product_promote_price;
            }

            /* 计算商品的促销价格 */
            if (isset($goods['promote_price']) && $goods['promote_price'] > 0) {
                $promote_price = $this->getBargainPrice($goods['promote_price'], $goods['promote_start_date'], $goods['promote_end_date']);
            } else {
                $promote_price = 0;
            }

            $goods['shop_price'] = $goods['shop_price'] ?? 0;
            $goods['user_price'] = $goods['user_price'] ?? 0;
        } else {
            $goods['user_price'] = 0;
            $goods['shop_price'] = 0;
        }
        //取得商品促销价格列表

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
                $discount = 1;
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
                    $user_price = $spec_price * $discount;
                }
            }

            /* SKU价格 */
            if ($show_goods == 1) {
                /* 会员等级价格 */
                if (!empty($goods['user_price'])) {
                    $spec_price = $goods['user_price'];
                } else {
                    $spec_price = $spec_price * $discount;
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
            if (!empty($spec_price) && $type == 0) {
                if ($add_tocart == 1) {
                    $final_price += $spec_price;
                }
            }
        }

        if (config('shop.add_shop_price') == 1 && !empty($spec_price)) {
            if ($type == 1 && $now_promote == 0) {
                //返回商品属性价
                $final_price = $spec_price;
            }
        }

        //返回商品最终购买价格
        return $final_price;
    }

    /**
     * 取得商品优惠价格列表
     *
     * @param $goods_id
     * @param int $price_type
     * @param int $is_pc
     * @return array
     * @throws \Exception
     */
    public function getVolumePriceList($goods_id, $price_type = 1, $is_pc = 0)
    {
        $list = GoodsDataHandleService::getVolumePriceDataList($goods_id, $price_type, $is_pc);
        $volume_price = $list[$goods_id] ?? [];

        return $volume_price;
    }

    /**
     * 商品退换货标识
     *
     * @param string $goods_cause
     * @param array $order
     * @param array $order_goods
     * @param int $buy_drp_show
     * @return array
     * @throws \Exception
     */
    public function getGoodsCause($goods_cause = '', $order = [], $order_goods = [], $buy_drp_show = 0)
    {
        $list = [];

        /**
         * 维修 0 、换货 1、退货 2 、仅退款 3
         * 1. 订单 已支付 未发货 不显示维修、换货、退货，只显示 仅退款
         * 2. 订单 已支付 已发货 显示维修、换货、退货，仅退款
         * 3. 货到付款订单 已收货 未付款  显示维修、换货、退货，不显示 仅退款
         */

        if ($goods_cause || $goods_cause === '0') {
            $_LANG = trans('user.order_return_type');

            $goods_cause = explode(',', $goods_cause);

            foreach ($goods_cause as $key => $row) {
                $list[$key]['cause'] = $row;
                $list[$key]['lang'] = $_LANG[$row];

                if ($key == 0) {
                    $list[$key]['is_checked'] = 1;
                } else {
                    $list[$key]['is_checked'] = 0;
                }

                if (!empty($order)) {
                    // 未付款 （包含 货到付款订单 未付款）
                    if (isset($order['pay_status']) && $order['pay_status'] == PS_UNPAYED) {
                        if ($row == 3) {
                            unset($list[$key]);
                            continue;
                        }
                    }
                    // 未发货
                    if (isset($order['shipping_status']) && $order['shipping_status'] == SS_UNSHIPPED) {
                        if ($row == 3) {
                            $list[$key]['is_checked'] = 1;
                        } else {
                            unset($list[$key]);
                            continue;
                        }
                    }
                }

                // 购买成为分销商品不显示退货、退款
                if ($buy_drp_show == 1 && in_array($row, [1, 3])) {
                    unset($list[$key]);
                    continue;
                }

                if (!empty($order_goods)) {
                    // 虚拟商品仅支持退款
                    if (isset($order_goods['is_real']) && $order_goods['is_real'] == 0 && $row != 3) {
                        unset($list[$key]);
                        continue;
                    }
                }
            }

            $list = empty($list) ? [] : collect($list)->values()->all();
        }

        return $list;
    }

    /**
     * 商品限购
     *
     * @param int $goods_id
     * @return mixed
     */
    public function getPurchasingGoodsInfo($goods_id = 0)
    {
        $row = Goods::select('is_xiangou', 'xiangou_num', 'xiangou_start_date', 'xiangou_end_date', 'goods_name', 'is_real', 'extension_code')
            ->where('goods_id', $goods_id);

        $row = BaseRepository::getToArrayFirst($row);

        return $row;
    }

    /**
     * Ajax楼层分类商品列表
     *
     * @param array $children 分类列表
     * @param int $num 查询数量
     * @param int $warehouse_id 仓库ID
     * @param int $area_id 仓库地区
     * @param int $area_city 仓库地区城市
     * @param string $goods_ids 商品ID
     * @param int $ru_id 店铺ID
     * @param null $user_rank 会员等级ID
     * @param null $discount 商品折扣
     * @return bool|\Illuminate\Cache\CacheManager|mixed
     * @throws \Exception
     */
    public function getFloorAjaxGoods($children = [], $num = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0, $goods_ids = '', $ru_id = 0, $user_rank = null, $discount = null)
    {
        $user_rank = !is_null($user_rank) ? $user_rank : session('user_rank', 0);
        $discount = !is_null($discount) ? $discount : session('discount', 1);

        if ($children) {
            $childrenCache = is_array($children) ? implode(',', $children) : $children;
        } else {
            $childrenCache = '';
        }

        if ($goods_ids) {
            $goodsidsCache = is_array($goods_ids) ? implode(',', $goods_ids) : $goods_ids;
        } else {
            $goodsidsCache = '';
        }

        $cache_name = "get_floor_ajax_goods_" . $childrenCache . '_' . $num . '_' . $warehouse_id . '_' . $area_id . '_' . $area_city . '_' . $goodsidsCache . '_' . $ru_id . '_' . $user_rank . '_' . $discount;

        /* 查询扩展分类数据 */
        $goodsParam = [];
        if (!empty($children)) {
            $extension_goods = GoodsCat::select('goods_id')->whereIn('cat_id', $children);
            $extension_goods = BaseRepository::getToArrayGet($extension_goods);
            $extension_goods = BaseRepository::getFlatten($extension_goods);

            $goodsParam = [
                'children' => $children,
                'extension_goods' => $extension_goods
            ];
        }

        $goods_res = cache($cache_name);

        if (is_null($goods_res)) {
            $goods_res = Goods::where('is_on_sale', 1)
                ->where('is_alone_sale', 1)
                ->where('is_delete', 0)
                ->where('is_show', 1)
                ->where(function ($query) use ($goodsParam) {
                    if (isset($goodsParam['children']) && $goodsParam['children']) {
                        $query = $query->whereIn('cat_id', $goodsParam['children']);
                    }

                    if (isset($goodsParam['extension_goods']) && $goodsParam['extension_goods']) {
                        $query->orWhere(function ($query) use ($goodsParam) {
                            $query->whereIn('goods_id', $goodsParam['extension_goods']);
                        });
                    }
                });

            if ($ru_id > 0) {
                $goods_res = $goods_res->where('user_id', $ru_id);
            }

            if (!empty($goods_ids)) {
                $goods_ids = BaseRepository::getExplode($goods_ids);
                $goods_res = $goods_res->whereIn('goods_id', $goods_ids);
            }

            $goods_res = $this->dscRepository->getAreaLinkGoods($goods_res, $area_id, $area_city);

            if (config('shop.review_goods') == 1) {
                $goods_res = $goods_res->whereIn('review_status', [3, 4, 5]);
            }

            if ($num > 0) {
                $goods_res = $goods_res->take($num);
            }

            $goods_res = $goods_res->orderBy('sort_order', 'desc')
                ->orderBy('goods_id', 'asc');

            $goods_res = BaseRepository::getToArrayGet($goods_res);

            if ($goods_res) {

                $goods_id = BaseRepository::getKeyPluck($goods_res, 'goods_id');
                $memberPrice = GoodsDataHandleService::goodsMemberPrice($goods_id, $user_rank);
                $warehouseGoods = GoodsDataHandleService::getWarehouseGoodsDataList($goods_id, $warehouse_id);
                $warehouseAreaGoods = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods_id, $area_id, $area_city);

                foreach ($goods_res as $idx => $row) {
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

                    $price = $this->getGoodsPrice($price, $discount, $row);

                    $row['shop_price'] = $price['shop_price'];
                    $row['promote_price'] = $price['promote_price'];
                    $row['goods_number'] = $price['goods_number'];

                    $goods_res[$idx] = $row;

                    if ($row['promote_price'] > 0) {
                        $promote_price = $this->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                    } else {
                        $promote_price = 0;
                    }

                    $goodsSelf = false;
                    if ($row['user_id'] == 0) {
                        $goodsSelf = true;
                    }

                    $goods_res[$idx]['is_promote'] = $row['is_promote'];
                    $goods_res[$idx]['goods_img'] = $this->dscRepository->getImagePath($row['goods_img']);
                    $goods_res[$idx]['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                    $goods_res[$idx]['market_price'] = $this->dscRepository->getPriceFormat($row['market_price'], true, true, $goodsSelf);
                    $goods_res[$idx]['shop_price'] = $this->dscRepository->getPriceFormat($row['shop_price'], true, true, $goodsSelf);
                    $goods_res[$idx]['promote_price'] = ($promote_price > 0) ? $this->dscRepository->getPriceFormat($promote_price, true, true, $goodsSelf) : '';
                    $goods_res[$idx]['shop_price'] = $this->dscRepository->getPriceFormat($row['shop_price'], true, true, $goodsSelf);
                    $goods_res[$idx]['short_name'] = config('shop.goods_name_length') > 0 ? $this->dscRepository->subStr($row['goods_name'], config('shop.goods_name_length')) : $row['goods_name'];
                    $goods_res[$idx]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
                }
            }

            cache()->forever($cache_name, $goods_res);
        }

        return $goods_res;
    }

    /**
     * 获得指定商品的各会员等级对应的价格
     *
     * @param array $goods
     * @param int $shop_price_original 商品原价
     * @param int $user_rank
     * @return array
     */
    public function getUserRankPrices($goods = [], $shop_price_original = 0, $user_rank = 0)
    {
        if (empty($goods)) {
            return [];
        }

        // 不显示会员等级价格
        if (empty(config('shop.show_rank_price'))) {
            return [];
        }

        $goods_id = $goods['goods_id'];

        // 商家商品禁用会员权益折扣
        $discount = 0;
        if (isset($goods['user_id']) && $goods['user_id'] > 0 && isset($goods['is_discount']) && $goods['is_discount'] == 0) {
            $discount = 1;
        }

        if (empty($shop_price_original)) {
            $shop_price_original = 0;
        }

        $res = UserRank::select('rank_id', 'discount', 'rank_name')
            ->where('show_price', 1)
            ->orWhere('rank_id', $user_rank);

        $res = $res->orderBy('rank_id', 'ASC');

        $res = BaseRepository::getToArrayGet($res);

        $rank_id = BaseRepository::getKeyPluck($res, 'rank_id');

        if (empty($rank_id)) {
            return [];
        }

        $rank_id = BaseRepository::getArrayUnique($rank_id);

        $member_rank_id = MemberPrice::select('user_rank')
            ->where('goods_id', $goods_id)
            ->whereIn('user_rank', $rank_id);
        $member_rank_id = $member_rank_id->pluck('user_rank');

        $member_rank_id = BaseRepository::getToArray($member_rank_id);

        $arr = [];
        if ($member_rank_id) {
            $sql = [
                'whereIn' => [
                    [
                        'name' => 'rank_id',
                        'value' => $member_rank_id
                    ]
                ]
            ];
            $res = BaseRepository::getArraySqlGet($res, $sql);

            if ($res) {
                $user_rank = BaseRepository::getKeyPluck($res, 'rank_id');
                $memberPrice = GoodsDataHandleService::goodsMemberPrice($goods_id, $user_rank);

                foreach ($res as $row) {
                    $price = [
                        'user_price' => $memberPrice[$goods_id][$row['rank_id']]['user_price'] ?? 0,
                        'percentage' => $memberPrice[$goods_id][$row['rank_id']]['percentage'] ?? 0,
                    ];

                    if (isset($price['user_price']) && $price['user_price'] > 0) {
                        // 会员价格
                        if (isset($price['percentage']) && $price['percentage'] == 1) {
                            $shop_price = $shop_price_original * $price['user_price'] / 100; // 百分比
                        } else {
                            $shop_price = $price['user_price']; // 固定价格
                        }
                    } else {
                        $row['discount'] = (isset($discount) && $discount == 1) ? 1 : $row['discount'];

                        $shop_price = $row['discount'] * $shop_price_original / 100;
                    }

                    $arr[$row['rank_id']] = [
                        'rank_name' => htmlspecialchars($row['rank_name']),
                        'price' => $this->dscRepository->getPriceFormat($shop_price)
                    ];
                }
            }
        }

        return $arr;
    }

    /**
     * 获得商品最优优惠活动ID
     *
     * @param int $goods_id 商品ID
     * @param int $price 价格
     * @param string $user_rank 会员等级ID
     * @return false|int|string
     */
    public function getBestFavourableId($goods_id = 0, $price = 0, $user_rank = '')
    {
        $user_rank = ',' . $user_rank . ',';
        $favourable = [];
        $gmtime = TimeRepository::getGmtime();

        //查询
        $goodsRes = Goods::select('user_id', 'cat_id', 'brand_id')->where('goods_id', $goods_id);
        $goodsRes = BaseRepository::getToArrayFirst($goodsRes);

        if (empty($goodsRes)) {
            return 0;
        }

        $ru_id = $goodsRes['user_id'];

        if ($ru_id > 0) {
            $res = FavourableActivity::where(function ($query) use ($ru_id) {
                $query->where('user_id', $ru_id)
                    ->orWhere('userFav_type', 1);
            });
        } else {
            $res = FavourableActivity::where('user_id', $ru_id);
        }

        $res = $res->where('review_status', 3)
            ->where('start_time', '<=', $gmtime)
            ->where('end_time', '>=', $gmtime);

        if (!empty($goods_id)) {
            $res = $res->whereRaw("CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'");
        }

        $res = $res->take(15);

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $rows) {

                $category_id = $goodsRes['cat_id'];
                $brand_id = $goodsRes['brand_id'];

                if ($rows['act_range'] == FAR_ALL) {
                    if ($price >= $rows['min_amount']) {
                        if ($rows['act_type'] == 1) {
                            $favourable[$rows['act_id']] = $rows['act_type_ext'];
                        } elseif ($rows['act_type'] == 2) {
                            $favourable[$rows['act_id']] = $price * (100 - $rows['act_type_ext']) / 100;
                        }
                    }
                } elseif ($rows['act_range'] == FAR_CATEGORY) {
                    /* 找出分类id的子分类id */
                    $raw_id_list = explode(',', $rows['act_range_ext']);
                    $CategoryRep = app(CategoryService::class);

                    foreach ($raw_id_list as $id) {
                        /**
                         * 当前分类下的所有子分类
                         * 返回一维数组
                         */
                        $cat_keys = $CategoryRep->getCatListChildren(intval($id));
                        $list_array[$rows['act_id']][$id] = $cat_keys;
                    }

                    $list_array = !empty($list_array) ? array_merge($raw_id_list, $list_array[$rows['act_id']]) : $raw_id_list;
                    $id_list = arr_foreach($list_array);
                    $id_list = array_unique($id_list);

                    $ids = join(',', array_unique($id_list));

                    if (strpos(',' . $ids . ',', ',' . $category_id . ',') !== false) {
                        if ($price >= $rows['min_amount']) {
                            if ($rows['act_type'] == 1) {
                                $favourable[$rows['act_id']] = $rows['act_type_ext'];
                            } elseif ($rows['act_type'] == 2) {
                                $favourable[$rows['act_id']] = $price * (100 - $rows['act_type_ext']) / 100;
                            }
                        }
                    }
                } elseif ($rows['act_range'] == FAR_BRAND) {
                    $rows['act_range_ext'] = act_range_ext_brand($rows['act_range_ext'], $rows['userFav_type'], $rows['act_range']);
                    if (strpos(',' . $rows['act_range_ext'] . ',', ',' . $brand_id . ',') !== false) {
                        if ($price >= $rows['min_amount']) {
                            if ($rows['act_type'] == 1) {
                                $favourable[$rows['act_id']] = $rows['act_type_ext'];
                            } elseif ($rows['act_type'] == 2) {
                                $favourable[$rows['act_id']] = $price * (100 - $rows['act_type_ext']) / 100;
                            }
                        }
                    }
                } elseif ($rows['act_range'] == FAR_GOODS) {
                    if (strpos(',' . $rows['act_range_ext'] . ',', ',' . $goods_id . ',') !== false) {
                        if ($price >= $rows['min_amount']) {
                            if ($rows['act_type'] == 1) {
                                $favourable[$rows['act_id']] = $rows['act_type_ext'];
                            } elseif ($rows['act_type'] == 2) {
                                $favourable[$rows['act_id']] = $price * (100 - $rows['act_type_ext']) / 100;
                            }
                        }
                    }
                }
            }
        }

        return $favourable ? array_search(max($favourable), $favourable) : 0; // 返回优惠金额最大的键名（act_id）
    }

    /**
     * 猜你喜欢
     *
     * @return array
     * @throws \Exception
     */
    public function getGoodsGuessYouLike()
    {
        $result = ['error' => 0, 'content' => '', 'message' => ''];

        $goods_ids = addslashes_deep(trim(request()->input('goods_ids', '')));
        $warehouse_id = intval(request()->input('warehouse_id', 0));
        $area_id = intval(request()->input('area_id', 0));
        $area_city = intval(request()->input('area_city', 0));
        $type = trim(request()->input('type', ''));

        $goods_list = [];
        if ($goods_ids) {
            $goods_list = $this->getFloorAjaxGoods([], 0, $warehouse_id, $area_id, $area_city, $goods_ids);
        }
        $GLOBALS['smarty']->assign('goods_list', $goods_list);
        $GLOBALS['smarty']->assign('type', $type);
        $result['content'] = $GLOBALS['smarty']->fetch('library/guessYouLike_list.lbi');

        return $result;
    }

    /**
     * 处理品牌关键词
     *
     * @param array $list
     * @return array
     */
    public function keywordFilter($list = [])
    {
        $brand_name = $list['brand_name'] ?? '';
        $keywords = $list['keywords'] ?? [];

        $arr = [];
        if (!empty($keywords) && !empty($brand_name)) {
            foreach ($keywords as $val) {
                if (stripos($val, $brand_name) !== false) {
                    $arr[] = $val;
                } else {
                    $arr[] = [
                        $brand_name,
                        $val
                    ];
                }
            }
        }

        return $arr;
    }

    /**
     * 处理品牌关键词搜索
     *
     * @param $res
     * @param $brandKeyword
     * @param $goods_arr
     * @return mixed
     */
    public function searchKeywordFilter($res, $brandKeyword, $goods_arr)
    {
        $nameKeyword = [];
        if (!empty($brandKeyword)) {
            $keywords = $brandKeyword[0] ?? '';

            /* 处理搜索数字分词失败 */
            preg_match_all('/\d+/', $keywords, $m);
            $nameKeyword = $m[0] ?? [];
        }

        $brand_id = BaseRepository::getExplode($goods_arr['brand_id']);
        $brand_name = $goods_arr['brand_name'] ?? '';

        if (!empty($brand_id) && !empty($brand_name)) {
            $brandKeyword = BaseRepository::getFlatten($brandKeyword);
            $brandKeyword = BaseRepository::getArrayUnique($brandKeyword);

            /* 去除品牌名称关键字 */
            if ($brandKeyword) {
                foreach ($brandKeyword as $k => $v) {
                    if (stripos($brand_name, $v) !== false) {
                        unset($brandKeyword[$k]);
                    }
                }
            }

            $brandKeyword = $brandKeyword ? array_values($brandKeyword) : [$brand_name];
        }

        $brandKeyword = $nameKeyword ? BaseRepository::getArrayCollapse([$brandKeyword, $nameKeyword]) : $brandKeyword;

        if (!empty($brandKeyword) && !empty($keywords) && $brand_name === $keywords) {
            $res = $res->where(function ($query) use ($brand_id, $keywords) {
                $query->whereIn('brand_id', $brand_id)
                    ->orWhere('goods_name', 'like', '%' . $keywords . '%')
                    ->orWhereRaw("FIND_IN_SET('$keywords', REPLACE(keywords, ' ', ','))");
            });
        } else {
            $res = $res->where(function ($query) use ($brandKeyword, $goods_arr, $brand_id, $brand_name) {
                $query = $query->where(function ($query) use ($brandKeyword) {
                    foreach ($brandKeyword as $k => $v) {
                        if ($v) {
                            if (is_array($v)) {
                                $query = $query->orWhere(function ($query) use ($v) {
                                    foreach ($v as $item) {
                                        $item = $this->dscRepository->mysqlLikeQuote(trim($item));
                                        $query = $query->where('goods_name', 'like', '%' . $item . '%');
                                        $query->orWhereRaw("FIND_IN_SET('$item', REPLACE(keywords, ' ', ','))");
                                    }
                                });
                            } else {
                                $v = $this->dscRepository->mysqlLikeQuote(trim($v));
                                $query = $query->orWhere('goods_name', 'like', '%' . $v . '%');
                                $query->orWhereRaw("FIND_IN_SET('$v', REPLACE(keywords, ' ', ','))");
                            }
                        }
                    }
                });

                $query->orWhere(function ($query) use ($brandKeyword, $brand_id, $brand_name) {

                    $query = $query->whereIn('brand_id', $brand_id);

                    $query->where(function ($query) use ($brandKeyword, $brand_name) {
                        foreach ($brandKeyword as $k => $v) {
                            if ($v) {
                                if (is_array($v)) {
                                    $query->orWhere(function ($query) use ($v, $brand_name) {
                                        foreach ($v as $item) {
                                            if (stripos($brand_name, $item) === false) {
                                                $item = $this->dscRepository->mysqlLikeQuote(trim($item));
                                                $query->where('goods_name', 'like', '%' . $item . '%');
                                            }
                                        }
                                    });
                                } else {
                                    if (stripos($brand_name, $v) === false) {
                                        $v = $this->dscRepository->mysqlLikeQuote(trim($v));
                                        $query->orWhere('goods_name', 'like', '%' . $v . '%');
                                    }
                                }
                            }
                        }
                    });
                });
            });
        }

        return $res;
    }

    /**
     * 搜索页关键字
     *
     * @param $res
     * @param $goods_arr
     * @return mixed
     */
    public function searchKeywords($res, $goods_arr)
    {
        if ($goods_arr['brand_id']) {
            $goods_arr['brand_id'] = BaseRepository::getExplode($goods_arr['brand_id']);
            $res = $res->whereIn('brand_id', $goods_arr['brand_id']);
        }

        $res = $res->where(function ($query) use ($goods_arr) {
            $query->where(function ($query) use ($goods_arr) {
                $query = $query->where('is_on_sale', 1);

                $query->where(function ($query) use ($goods_arr) {
                    foreach ($goods_arr['keywords'] as $key => $val) {
                        $query->orWhere(function ($query) use ($val) {
                            $val = $this->dscRepository->mysqlLikeQuote(trim($val));

                            $query->orWhere('goods_name', 'like', '%' . $val . '%')
                                ->orWhereRaw("FIND_IN_SET('$val', REPLACE(keywords, ' ', ','))");

                        });
                    }

                    $keyword_goods_sn = $goods_arr['keywords'][0] ?? '';

                    if ($keyword_goods_sn) {
                        // 搜索商品货号
                        $query->orWhere('goods_sn', 'like', '%' . $keyword_goods_sn . '%');
                    }
                });
            });

            //兼容预售
            if ($goods_arr['keywords'] && isset($goods_arr['presale_goods_id']) && $goods_arr['presale_goods_id']) {
                $query->orWhere(function ($query) use ($goods_arr) {
                    $query->where('is_on_sale', 0)
                        ->whereIn('goods_id', $goods_arr['presale_goods_id']);
                });
            }
        });

        return $res;
    }

    /**
     * 通过扩展分类ID获取相关商品ID
     *
     * @param array $children
     * @return array
     */
    public function getCategoryGoodsId($children = [])
    {
        if (empty($children)) {
            return [];
        }

        $children = BaseRepository::getExplode($children);

        /* 查询扩展分类数据 */
        $extension_goods = GoodsCat::query()->select('goods_id')
            ->whereIn('cat_id', $children)
            ->pluck('goods_id');
        $extension_goods = BaseRepository::getToArray($extension_goods);

        return $extension_goods;
    }

    /**
     * 通过商品ID 返回活动标签
     *
     * @param int $goods_id
     * @param int $self_run
     * @return array
     */
    public function getGoodsLabelList($goods_id = 0, $self_run = 0)
    {
        $res = GoodsLabelRepository::getAllGoodsLabel($goods_id, $self_run);

        $goods_label = [];// 通用标签
        $goods_label_suspension = [];// 悬浮标签

        if ($res) {
            foreach ($res as $key => $val) {
                $val = collect($val)->merge($val['get_goods_label'])->except('get_goods_label')->all();

                $val['formated_label_image'] = $this->dscRepository->getImagePath($val['label_image']);

                if (isset($val['type']) && $val['type'] == 1) {
                    $goods_label_suspension[] = $val;
                } else {
                    $goods_label[] = $val;
                }
            }
        }

        // 通用标签排序
        $goods_label = $goods_label ? collect($goods_label)->sortBy('sort')->values()->all() : [];

        if (!empty($goods_label_suspension)) {
            // 显示时间段内的悬浮标签, 最多显示1个悬浮标签, 当商品存在多个悬浮标签时，优先显示有效期最近且排序靠前的悬浮标签
            $time = TimeRepository::getGmTime();
            $collection = collect($goods_label_suspension)->where('start_time', '<=', $time)->where('end_time', '>=', $time);
            $goods_label_suspension = $collection->sortBy('sort')->sortByDesc('end_time')->sortByDesc('label_id')->values()->first();
        }

        return ['goods_label' => $goods_label, 'goods_label_suspension' => $goods_label_suspension];
    }

    /**
     * 返回列表页商品标签列表
     *
     * @param array $merchant_use_arr 商家可用标签
     * @param array $merchant_no_use_arr 商家不可用标签
     * @param array $where 条件
     * @return array
     */
    public function getListGoodsLabelList($merchant_use_arr = [], $merchant_no_use_arr = [], $where = [])
    {
        $merchant_no_use_goods_label = []; // 初始化
        $merchant_use_goods_label = $merchant_use_arr[$where['goods_id']] ?? [];

        if ($where['self_run'] > 0 || $where['user_id'] == 0) {
            $merchant_no_use_goods_label = $merchant_no_use_arr[$where['goods_id']] ?? [];
        }

        $goods_label_all = BaseRepository::getArrayCollapse([$merchant_use_goods_label, $merchant_no_use_goods_label]);

        $goods_label = [];// 通用标签
        $goods_label_suspension = []; // 悬浮标签

        if ($goods_label_all) {
            foreach ($goods_label_all as $key => $val) {
                if (isset($val['type']) && $val['type'] == 1) {
                    $goods_label_suspension[] = $val;
                } else {
                    $goods_label[] = $val;
                }
            }
        }

        // 通用标签排序
        $goods_label = $goods_label ? collect($goods_label)->sortBy('sort')->values()->all() : [];

        if (!empty($goods_label_suspension)) {
            // 显示时间段内的悬浮标签, 最多显示1个悬浮标签, 当商品存在多个悬浮标签时，优先显示有效期最近且排序靠前的悬浮标签
            $time = TimeRepository::getGmTime();
            $collection = collect($goods_label_suspension)->where('start_time', '<=', $time)->where('end_time', '>=', $time);
            $goods_label_suspension = $collection->sortBy('sort')->sortByDesc('end_time')->sortByDesc('label_id')->values()->first();
        }

        return ['goods_label' => $goods_label, 'goods_label_suspension' => $goods_label_suspension];
    }

    /**
     * 商品活动标签(普通标签)
     *
     * @param int $goods_id
     * @param array $label_use_id
     * @return array
     */
    public function getGoodsLabel($goods_id = 0, $label_use_id = [])
    {
        $list = GoodsLabelRepository::getGoodsLabel($goods_id, 0, ['id', 'label_name', 'label_image'], $label_use_id);

        if ($list) {
            foreach ($list as $k => $val) {
                $list[$k]['formated_label_image'] = $this->dscRepository->getImagePath($val['get_goods_label']['label_image']);
            }
        }

        return $list;
    }

    /**
     * 搜索标签
     * @param string $keyword
     * @return mixed
     */
    public function searchGoodsLabel($keyword = '')
    {
        $list = GoodsLabelRepository::searchGoodsLabel($keyword);

        if ($list) {
            foreach ($list as $k => $val) {
                $list[$k]['formated_label_image'] = $this->dscRepository->getImagePath($val['label_image']);
            }
        }

        return $list;
    }

    /**
     * 获取商品标签列表（平台后台）
     * @param int $goods_id
     * @param array $label_use_id
     * @return array
     */
    public function getGoodsLabelForAdmin($goods_id = 0, $label_use_id = [])
    {
        $list = GoodsLabelRepository::getGoodsLabelForAdmin($goods_id, $label_use_id);

        if ($list) {
            foreach ($list as $k => $val) {
                $list[$k]['formated_label_image'] = $this->dscRepository->getImagePath($val['label_image']);
            }
        }

        return $list;
    }

    /**
     * 获取商品标签列表（商家后台）
     * @param int $goods_id
     * @param array $label_use_id
     * @return array
     */
    public function getGoodsLabelForSeller($goods_id = 0, $label_use_id = [])
    {
        $list = GoodsLabelRepository::getGoodsLabelForSeller($goods_id, $label_use_id);

        if ($list) {
            foreach ($list as $k => $val) {
                $list[$k]['formated_label_image'] = $this->dscRepository->getImagePath($val['label_image']);
            }
        }

        return $list;
    }

    /**
     * 商品服务标签
     *
     * @param int $goods_id
     * @param array $label_use_id
     * @return array
     */
    public function getGoodsServicesLabel($goods_id = 0, $label_use_id = [])
    {
        $list = GoodsLabelRepository::getGoodsServicesLabel($goods_id, ['id', 'label_name', 'label_image'], $label_use_id);

        if ($list) {
            foreach ($list as $k => $val) {
                $list[$k]['formated_label_image'] = $this->dscRepository->getImagePath($val['get_goods_services_label']['label_image']);
            }
        }

        return $list;
    }

    /**
     * 搜索标签
     * @param string $keyword
     * @return mixed
     */
    public function searchGoodsServicesLabel($keyword = '')
    {
        $list = GoodsServicesLabelRepository::searchGoodsServicesLabel($keyword);

        if ($list) {
            foreach ($list as $k => $val) {
                $list[$k]['formated_label_image'] = $this->dscRepository->getImagePath($val['label_image']);
            }
        }

        return $list;
    }

    /**
     * 获取商品服务标签列表（平台后台）
     * @param int $goods_id
     * @param array $services_label_use_id
     * @return array
     */
    public function getGoodsServicesLabelForAdmin($goods_id = 0, $services_label_use_id = [])
    {
        $list = GoodsLabelRepository::getGoodsServicesLabelForAdmin($goods_id, $services_label_use_id);

        if ($list) {
            foreach ($list as $k => $val) {
                $list[$k]['formated_label_image'] = $this->dscRepository->getImagePath($val['label_image']);
            }
        }

        return $list;
    }

    /**
     * 获取商品服务标签列表（商家后台）
     * @param int $goods_id
     * @param array $services_label_use_id
     * @return array
     */
    public function getGoodsServicesLabelForSeller($goods_id = 0, $services_label_use_id = [])
    {
        $list = GoodsLabelRepository::getGoodsServicesLabelForSeller($goods_id, $services_label_use_id);

        if ($list) {
            foreach ($list as $k => $val) {
                $list[$k]['formated_label_image'] = $this->dscRepository->getImagePath($val['label_image']);
            }
        }

        return $list;
    }

    /**
     * 通过商品ID 返回服务标签
     *
     * @param int $goods_id
     * @param int $self_run
     * @param string $goods_cause
     * @return array
     */
    public function getGoodsServicesLabelList($goods_id = 0, $self_run = 0, $goods_cause = '')
    {
        $res = GoodsLabelRepository::getAllGoodsServicesLabel($goods_id, $self_run);

        $goods_services_label = [];// 通用标签

        if ($res) {
            foreach ($res as $key => $val) {
                $val = collect($val)->merge($val['get_goods_services_label'])->except('get_goods_services_label')->all();

                $goods_services_label[$key]['label_explain'] = $val['label_explain'];
                $goods_services_label[$key]['label_name'] = $val['label_name'];
                $goods_services_label[$key]['formated_label_image'] = $this->dscRepository->getImagePath($val['label_image']);
                $goods_services_label[$key]['sort'] = $val['sort'];

                if ($val['label_code'] == 'no_reason_return') { // 无理由退换货支持
                    $goods_cause = BaseRepository::getExplode($goods_cause);
                    $fruit = [1, 2, 3]; //退货，换货，仅退款
                    $intersection = array_intersect($fruit, $goods_cause); //判断是否支持
                    $goods_services_label[$key]['label_name'] = (empty($intersection) ? trans('common.not') : '') . trans('common.Support') . $val['label_name'];
                    $goods_services_label[$key]['label_explain'] = empty($intersection) ? '' : $val['label_explain'];
                }
            }
        }

        // 标签排序
        $goods_services_label = $goods_services_label ? collect($goods_services_label)->sortBy('sort')->values()->all() : [];

        return ['goods_services_label' => $goods_services_label];
    }
}
