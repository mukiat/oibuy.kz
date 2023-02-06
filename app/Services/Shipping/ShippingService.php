<?php

namespace App\Services\Shipping;

use App\Models\Cart;
use App\Models\GoodsTransport;
use App\Models\GoodsTransportExpress;
use App\Models\GoodsTransportExtend;
use App\Models\Products;
use App\Models\ProductsArea;
use App\Models\ProductsWarehouse;
use App\Models\Shipping;
use App\Models\ShippingPoint;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\StrRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Category\CategoryService;
use App\Services\Coupon\CouponDataHandleService;
use App\Services\CrossBorder\CrossBorderService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Order\OrderTransportService;
use App\Services\Region\RegionDataHandleService;

/**
 * Class ShippingService
 * @package App\Services\Shipping
 */
class ShippingService
{
    protected $sessionRepository;
    protected $dscRepository;
    protected $orderTransportService;
    protected $shippingDataHandleService;
    protected $regionDataHandleService;

    public function __construct(
        SessionRepository $sessionRepository,
        DscRepository $dscRepository,
        OrderTransportService $orderTransportService,
        ShippingDataHandleService $shippingDataHandleService,
        RegionDataHandleService $regionDataHandleService
    )
    {
        $files = [
            'base',
            'common',
            'time',
        ];
        load_helper($files);

        $this->sessionRepository = $sessionRepository;
        $this->dscRepository = $dscRepository;
        $this->orderTransportService = $orderTransportService;
        $this->shippingDataHandleService = $shippingDataHandleService;
        $this->regionDataHandleService = $regionDataHandleService;
    }

    /**
     * 配送列表
     *
     * @param $rec_ids
     * @param $user_id
     * @param $ru_id
     * @param string $consignee
     * @param int $flow_type
     * @return mixed
     * @throws \Exception
     */
    public function getShippingList($rec_ids, $user_id, $ru_id, $consignee = '', $flow_type = 0)
    {
        $whereCart['flow_type'] = $flow_type;
        $whereCart['flow_consignee'] = $consignee;

        $ru_shipping = $this->getRuShippngInfo($rec_ids, $user_id, $ru_id, $consignee, $whereCart);

        $arr['shipping'] = $ru_shipping['shipping_list'];
        $arr['is_freight'] = $ru_shipping['is_freight'];
        $arr['shipping_rec'] = $ru_shipping['shipping_rec'];

        $arr['shipping_count'] = !empty($arr['shipping']) ? count($arr['shipping']) : 0;
        if (!empty($arr['shipping'])) {
            $arr['tmp_shipping_id'] = isset($arr['shipping'][0]['shipping_id']) ? $arr['shipping'][0]['shipping_id'] : 0; //默认选中第一个配送方式
            foreach ($arr['shipping'] as $kk => $vv) {
                if (isset($vv['default']) && $vv['default'] == 1) {
                    $arr['tmp_shipping_id'] = $vv['shipping_id'];
                    $arr['default_shipping'] = $vv;
                    continue;
                }
            }
        }

        return $arr;
    }

    /**
     * 查询商家默认配送方式
     *
     * @param $rec_ids
     * @param $user_id
     * @param $ru_id
     * @param string $consignee
     * @param array $whereCart
     * @return array
     * @throws \Exception
     */
    public function getRuShippngInfo($rec_ids, $user_id, $ru_id, $consignee = '', $whereCart = [])
    {
        //分离商家信息by wu start
        $cart_value_arr = [];
        $cart_freight = [];
        $shipping_rec = [];
        $freight = '';

        $rec_ids = BaseRepository::getExplode($rec_ids);

        foreach ($rec_ids as $k => $v) {

            $cgv = Cart::select('rec_id', 'ru_id', 'tid', 'freight')->where('rec_id', $v);
            $cgv = BaseRepository::getToArrayFirst($cgv);

            if ($cgv['ru_id'] != $ru_id) {
                unset($rec_ids[$k]);
            } else {
                $cart_value_arr[] = $cgv['rec_id'];

                if ($cgv['freight'] == 2) {
                    // 检测单个商品地区是否支持配送
                    if (empty($cgv['tid'])) {
                        $shipping_rec[] = $cgv['rec_id'];
                    }
                    @$cart_freight[$cgv['rec_id']][$cgv['freight']] = $cgv['tid'];
                }

                $freight .= $cgv['freight'] . ",";
            }
        }

        if ($freight) {
            $freight = $this->dscRepository->delStrComma($freight);
        }

        $is_freight = 0;
        if ($freight) {
            $freight = explode(",", $freight);
            $freight = array_unique($freight);

            /**
             * 判断是否有《地区运费》
             */
            if (in_array(2, $freight)) {
                $is_freight = 1;
            }
        }
        //分离商家信息by wu end

        if (!empty($user_id)) {
            $sess_id = " user_id = '$user_id' ";
        } else {
            $session_id = $this->sessionRepository->realCartMacIp();
            $sess_id = " session_id = '$session_id' ";
        }

        $order = flow_order_info($user_id);

        $seller_shipping = get_seller_shipping_type($ru_id);
        $shipping_id = $seller_shipping['shipping_id'] ?? 0;

        $consignee = isset($whereCart['flow_consignee']) ? $whereCart['flow_consignee'] : $consignee;
        $consignee['country'] = $consignee['country'] ?? 0;
        $consignee['province'] = $consignee['province'] ?? 0;
        $consignee['city'] = $consignee['city'] ?? 0;
        $consignee['district'] = $consignee['district'] ?? 0;
        $consignee['street'] = $consignee['street'] ?? 0;

        $region = [$consignee['country'], $consignee['province'], $consignee['city'], $consignee['district'], $consignee['street']];

        $insure_disabled = true;
        $cod_disabled = true;


        // 查看购物车中是否全为免运费商品，若是则把运费赋为零
        $shipping_count = Cart::where('extension_code', '<>', 'package_buy')
            ->where('is_shipping', 0)
            ->where('ru_id', $ru_id)
            ->whereRaw($sess_id)
            ->whereIn('rec_id', $cart_value_arr)
            ->count();

        $shipping_list = [];

        if ($is_freight) {
            if ($cart_freight) {
                $list1 = [];
                $list2 = [];
                foreach ($cart_freight as $key => $row) {
                    if (isset($row[2]) && $row[2]) {
                        $transport_list = GoodsTransport::where('tid', $row[2])->get();
                        $transport_list = $transport_list ? $transport_list->toArray() : [];

                        if ($transport_list) {
                            foreach ($transport_list as $tkey => $trow) {
                                if ($trow['freight_type'] == 1) {
                                    $shipping_list1 = Shipping::select('shipping_id', 'shipping_code', 'shipping_name', 'shipping_order')->where('enabled', 1);
                                    $shipping_list1 = $shipping_list1->whereHasIn('getGoodsTransportTpl', function ($query) use ($region, $ru_id, $trow) {
                                        $query->whereRaw("(FIND_IN_SET('" . $region[1] . "', region_id) OR FIND_IN_SET('" . $region[2] . "', region_id) OR FIND_IN_SET('" . $region[3] . "', region_id) OR FIND_IN_SET('" . $region[4] . "', region_id))")
                                            ->where('user_id', $ru_id)
                                            ->where('tid', $trow['tid']);
                                    });
                                    $shipping_list1 = BaseRepository::getToArrayGet($shipping_list1);

                                    if (empty($shipping_list1)) {
                                        $shipping_rec[] = $key;
                                    }

                                    $list1[] = $shipping_list1;
                                } else {
                                    $shipping_list2 = GoodsTransportExpress::where('tid', $trow['tid'])->where('ru_id', $ru_id);

                                    $shipping_list2 = $shipping_list2->whereHasIn('getGoodsTransportExtend', function ($query) use ($ru_id, $trow, $region) {
                                        $query->where('ru_id', $ru_id)
                                            ->where('tid', $trow['tid'])
                                            ->whereRaw("((FIND_IN_SET('" . $region[1] . "', top_area_id)) OR (FIND_IN_SET('" . $region[2] . "', area_id) OR FIND_IN_SET('" . $region[3] . "', area_id) OR FIND_IN_SET('" . $region[4] . "', area_id)))");
                                    });

                                    $shipping_list2 = BaseRepository::getToArrayGet($shipping_list2);

                                    if ($shipping_list2) {
                                        $new_shipping = [];
                                        foreach ($shipping_list2 as $gtkey => $gtval) {
                                            $gt_shipping_id = !is_array($gtval['shipping_id']) ? explode(",", $gtval['shipping_id']) : $gtval['shipping_id'];
                                            $new_shipping[] = $gt_shipping_id ? $gt_shipping_id : [];
                                        }

                                        $new_shipping = BaseRepository::getFlatten($new_shipping);

                                        if ($new_shipping) {
                                            $shippingInfo = Shipping::select('shipping_id', 'shipping_code', 'shipping_name', 'shipping_order')
                                                ->where('enabled', 1)
                                                ->whereIn('shipping_id', $new_shipping);
                                            $list2[] = BaseRepository::getToArrayGet($shippingInfo);
                                        }
                                    } else {
                                        $shipping_rec[] = $key;
                                    }
                                }
                            }
                        }
                    }
                }

                $shipping_list1 = get_three_to_two_array($list1);
                $shipping_list2 = get_three_to_two_array($list2);

                if ($shipping_list1 && $shipping_list2) {
                    $shipping_list = array_merge($shipping_list1, $shipping_list2);
                } elseif ($shipping_list1) {
                    $shipping_list = $shipping_list1;
                } elseif ($shipping_list2) {
                    $shipping_list = $shipping_list2;
                }

                if ($shipping_list) {
                    //去掉重复配送方式 start
                    $new_shipping = [];
                    foreach ($shipping_list as $key => $val) {
                        @$new_shipping[$val['shipping_code']][] = $key;
                    }

                    foreach ($new_shipping as $key => $val) {
                        if (count($val) > 1) {
                            for ($i = 1; $i < count($val); $i++) {
                                unset($shipping_list[$val[$i]]);
                            }
                        }
                    }
                    //去掉重复配送方式 end

                    $shipping_list = BaseRepository::getSortBy($shipping_list, 'shipping_order');
                }
            }

            $configure_value = 0;
            $configure_type = 0;
            $shipping_fee = 0;

            if ($shipping_list) {
                $str_shipping = '';
                foreach ($shipping_list as $key => $row) {
                    $str_shipping .= $row['shipping_id'] . ",";
                }

                $str_shipping = $this->dscRepository->delStrComma($str_shipping);
                $str_shipping = explode(",", $str_shipping);
                if (in_array($shipping_id, $str_shipping)) {
                    $have_shipping = 1;
                } else {
                    $have_shipping = 0;
                }

                foreach ($shipping_list as $key => $val) {
                    if (substr($val['shipping_code'], 0, 5) != 'ship_') {
                        if (config('shop.freight_model') == 0) {

                            /* 商品单独设置运费价格 start */
                            if ($rec_ids) {

                                $rec_ids = BaseRepository::getExplode($rec_ids);

                                $cart_goods = Cart::whereIn("rec_id", $rec_ids);
                                $cart_goods = BaseRepository::getToArrayGet($cart_goods);

                                if ($cart_goods) {

                                    $goods_id = BaseRepository::getKeyPluck($cart_goods, 'goods_id');
                                    $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'goods_weight', 'shipping_fee']);

                                    foreach ($cart_goods as $k => $v) {

                                        $goods = $goodsList[$v['goods_id']] ?? [];

                                        $cart_goods[$k]['goodsweight'] = $goods['goods_weight'] ?? 0;
                                        $cart_goods[$k]['shipping_fee'] = $goods['shipping_fee'] ?? 0;
                                    }
                                }

                                if (count($rec_ids) == 1) {

                                    if (!empty($cart_goods[0]['freight']) && $cart_goods[0]['is_shipping'] == 0) {
                                        if ($cart_goods[0]['freight'] == 1) {
                                            $configure_value = $cart_goods[0]['shipping_fee'] * $cart_goods[0]['goods_number'];
                                        } else {
                                            $trow = get_goods_transport($cart_goods[0]['tid']);

                                            if (isset($trow['freight_type']) && $trow['freight_type']) {
                                                $cart_goods[0]['user_id'] = $cart_goods[0]['ru_id'];
                                                $transport_tpl = get_goods_transport_tpl($cart_goods[0], $region, $val, $cart_goods[0]['goods_number']);

                                                $configure_value = isset($transport_tpl['shippingFee']) ? $transport_tpl['shippingFee'] : 0;
                                            } else {

                                                /**
                                                 * 商品运费模板
                                                 * 自定义
                                                 */
                                                $custom_shipping = $this->orderTransportService->getGoodsCustomShipping($cart_goods);
                                                $goods_transport = GoodsTransportExtend::select('top_area_id', 'area_id', 'tid', 'ru_id', 'sprice')
                                                    ->where('ru_id', $cart_goods[0]['ru_id'])
                                                    ->where('tid', $cart_goods[0]['tid'])->whereRaw("FIND_IN_SET(" . $consignee['city'] . ", area_id)");
                                                $goods_transport = BaseRepository::getToArrayFirst($goods_transport);

                                                $goods_ship_transport = GoodsTransportExpress::select('tid', 'ru_id', 'shipping_fee')
                                                    ->where('ru_id', $cart_goods[0]['ru_id'])
                                                    ->where('tid', $cart_goods[0]['tid'])
                                                    ->whereRaw("FIND_IN_SET(" . $val['shipping_id'] . ", shipping_id)");
                                                $goods_ship_transport = BaseRepository::getToArrayFirst($goods_ship_transport);

                                                $goods_transport['sprice'] = isset($goods_transport['sprice']) ? $goods_transport['sprice'] : 0;
                                                $goods_ship_transport['shipping_fee'] = isset($goods_ship_transport['shipping_fee']) ? $goods_ship_transport['shipping_fee'] : 0;

                                                /* 是否免运费 start */
                                                if ($custom_shipping && $custom_shipping[$cart_goods[0]['tid']]['amount'] >= $trow['free_money'] && $trow['free_money'] > 0) {
                                                    $is_shipping = 1; /* 免运费 */
                                                } else {
                                                    $is_shipping = 0; /* 有运费 */
                                                }
                                                /* 是否免运费 end */

                                                if ($is_shipping == 0) {
                                                    if ($trow['type'] == 1) {
                                                        $configure_value = $goods_transport['sprice'] * $cart_goods[0]['goods_number'] + $goods_ship_transport['shipping_fee'] * $cart_goods[0]['goods_number'];
                                                    } else {
                                                        $configure_value = $goods_transport['sprice'] + $goods_ship_transport['shipping_fee'];
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                        /* 有配送按配送区域计算运费 */
                                        $configure_type = 1;
                                    }
                                } else {
                                    $order_transpor = $this->orderTransportService->getOrderTransport($cart_goods, $consignee, $val['shipping_id'], $val['shipping_code']);

                                    if (isset($order_transpor['freight']) && $order_transpor['freight']) {
                                        /* 有配送按配送区域计算运费 */
                                        $configure_type = 1;
                                    }

                                    $configure_value = isset($order_transpor['sprice']) ? $order_transpor['sprice'] : 0;
                                }
                            }
                            /* 商品单独设置运费价格 end */

                            $shipping_fee = $shipping_count == 0 ? 0 : $configure_value;
                            $shipping_list[$key]['free_money'] = $this->dscRepository->getPriceFormat(0, false);
                        }

                        if ($val['shipping_code'] == 'cac') {
                            $shipping_fee = 0;
                        }

                        $shipping_list[$key]['shipping_id'] = $val['shipping_id'];
                        $shipping_list[$key]['shipping_name'] = $val['shipping_name'];
                        $shipping_list[$key]['shipping_code'] = $val['shipping_code'];
                        $shipping_list[$key]['format_shipping_fee'] = $this->dscRepository->getPriceFormat($shipping_fee, false);
                        $shipping_list[$key]['shipping_fee'] = $this->dscRepository->changeFloat($shipping_fee);

                        if (isset($val['insure']) && $val['insure']) {
                            $shipping_list[$key]['insure_formated'] = strpos($val['insure'], '%') === false ? $this->dscRepository->getPriceFormat($val['insure'], false) : $val['insure'];
                        }

                        /* 当前的配送方式是否支持保价 */
                        if ($val['shipping_id'] == $order['shipping_id']) {
                            if (isset($val['insure']) && $val['insure']) {
                                $insure_disabled = ($val['insure'] == 0);
                            }
                            if (isset($val['support_cod']) && $val['support_cod']) {
                                $cod_disabled = ($val['support_cod'] == 0);
                            }
                        }

                        //默认配送方式
                        if ($have_shipping == 1) {
                            $shipping_list[$key]['default'] = 0;
                            if ($shipping_id == $val['shipping_id']) {
                                $shipping_list[$key]['default'] = 1;
                            }
                        } else {
                            if ($key == 0) {
                                $shipping_list[$key]['default'] = 1;
                            }
                        }

                        $shipping_list[$key]['insure_disabled'] = $insure_disabled;
                        $shipping_list[$key]['cod_disabled'] = $cod_disabled;
                    }

                    // 兼容过滤ecjia配送方式
                    if (substr($val['shipping_code'], 0, 5) == 'ship_') {
                        unset($shipping_list[$key]);
                    }
                }

                //去掉重复配送方式 by wu start
                $shipping_type = [];
                foreach ($shipping_list as $key => $val) {
                    @$shipping_type[$val['shipping_code']][] = $key;
                }

                foreach ($shipping_type as $key => $val) {
                    if (count($val) > 1) {
                        for ($i = 1; $i < count($val); $i++) {
                            unset($shipping_list[$val[$i]]);
                        }
                    }
                }
                //去掉重复配送方式 by wu end
            }
        } else {
            $configure_value = 0;

            /* 商品单独设置运费价格 start */
            if ($rec_ids) {

                $rec_ids = BaseRepository::getExplode($rec_ids);

                $cart_goods = Cart::whereIn("rec_id", $rec_ids);
                $cart_goods = BaseRepository::getToArrayGet($cart_goods);

                if ($cart_goods) {

                    $goods_id = BaseRepository::getKeyPluck($cart_goods, 'goods_id');
                    $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'goods_weight', 'shipping_fee']);

                    foreach ($cart_goods as $k => $v) {

                        $goods = $goodsList[$v['goods_id']] ?? [];

                        $cart_goods[$k]['goodsweight'] = $goods['goods_weight'] ?? 0;
                        $cart_goods[$k]['shipping_fee'] = $goods['shipping_fee'] ?? 0;
                    }
                }

                if (count($rec_ids) == 1) {
                    if (!empty($cart_goods[0]['freight']) && $cart_goods[0]['is_shipping'] == 0) {
                        $configure_value = $cart_goods[0]['shipping_fee'] * $cart_goods[0]['goods_number'];
                    } else {
                        /* 有配送按配送区域计算运费 */
                        $configure_type = 1;
                    }
                } else {
                    $sprice = 0;
                    foreach ($cart_goods as $key => $row) {
                        if ($row['is_shipping'] == 0) {
                            $sprice += $row['shipping_fee'] * $row['goods_number'];
                        }
                    }

                    $configure_value = $sprice;
                }
            }
            /* 商品单独设置运费价格 end */

            $shipping_fee = $shipping_count == 0 ? 0 : $configure_value;
            // 上门自提免配送费
            if (isset($seller_shipping['shipping_code']) && $seller_shipping['shipping_code'] == 'cac') {
                $shipping_fee = 0;
            }
            $shipping_list[0]['free_money'] = $this->dscRepository->getPriceFormat(0, false);
            $shipping_list[0]['format_shipping_fee'] = $this->dscRepository->getPriceFormat($shipping_fee, false);
            $shipping_list[0]['shipping_fee'] = $this->dscRepository->changeFloat($shipping_fee);
            $shipping_list[0]['shipping_id'] = isset($seller_shipping['shipping_id']) && !empty($seller_shipping['shipping_id']) ? $seller_shipping['shipping_id'] : 0;
            $shipping_list[0]['shipping_name'] = isset($seller_shipping['shipping_name']) && !empty($seller_shipping['shipping_name']) ? $seller_shipping['shipping_name'] : '';
            $shipping_list[0]['shipping_code'] = isset($seller_shipping['shipping_code']) && !empty($seller_shipping['shipping_code']) ? $seller_shipping['shipping_code'] : '';
            $shipping_list[0]['default'] = 1;
        }

        /* 此处删除多商户跨境商品计算税费累加代码【大部分用户用不到，所以删除，需要再根据情况开发重写代码】 */

        return ['is_freight' => $is_freight, 'shipping_list' => $shipping_list, 'shipping_rec' => $shipping_rec];
    }

    /**
     * 重新组合购物流程商品数组
     *
     * @param $cart_goods_list_new
     * @return array
     */
    public function get_new_group_cart_goods($cart_goods_list_new)
    {
        $car_goods = [];
        foreach ($cart_goods_list_new as $key => $goods) {
            foreach ($goods['goods_list'] as $k => $list) {
                $car_goods[] = $list;
            }
        }

        return $car_goods;
    }

    /**
     * 区域获得自提点
     *
     * @param int $district
     * @param int $point_id
     * @param int $limit
     * @return mixed
     */
    public function getSelfPoint($district = 0, $point_id = 0, $limit = 100)
    {
        $list = ShippingPoint::query();

        if ($point_id > 0) {
            $list = where('id', $point_id);
        }

        $list = $list->limit($limit);

        $list = BaseRepository::getToArrayGet($list);

        if ($list) {

            $shipping_area_id = BaseRepository::getKeyPluck($list, 'shipping_area_id');
            $shippingAreaList = $this->shippingDataHandleService->getShippingAreaDataList($shipping_area_id, null, ['shipping_id']);

            $region_id = BaseRepository::getKeyPluck($shippingAreaList, 'region_id');
            $regionList = $this->regionDataHandleService->getRegionDataList($region_id, ['region_id', 'region_name']);

            if ($district > 0 && $point_id == 0) {
                $sql = [
                    'where' => [
                        [
                            'name' => 'region_id',
                            'value' => $district
                        ]
                    ]
                ];
                $region = BaseRepository::getArraySqlGet($regionList, $sql, 1);

                if (empty($region)) {
                    return [];
                }
            }

            $shipping_id = BaseRepository::getKeyPluck($shippingAreaList, 'shipping_id');
            $shipping_id = $shipping_id ? array_unique($shipping_id) : [];

            $shippingList = $this->shippingDataHandleService->getShippingDataList($shipping_id, ['shipping_id, shipping_name']);

            foreach ($list as $key => $val) {

                $region = $regionList[$val['region_id']] ?? [];
                $shippingArea = $shippingAreaList[$val['shipping_area_id']] ?? [];

                $shipping_id = $shippingArea['shipping_id'] ?? 0;
                $shipping = $shippingList[$shipping_id] ?? [];

                $list[$key]['region_id'] = $region['region_id'] ?? 0;
                $list[$key]['region_name'] = $region['region_name'] ?? '';
                $list[$key]['city'] = $region['parent_id'] ?? 0;

                $list[$key]['shipping_id'] = $shipping['shipping_id'] ?? 0;
                $list[$key]['shipping_name'] = $shipping['shipping_name'] ?? '';
                $list[$key]['shipping_code'] = $shipping['shipping_code'] ?? '';

                $list[$key]['point_id'] = $val['id'];

                if ($point_id > 0 && $val['id'] == $point_id) {
                    $list[$key]['is_check'] = 1;
                }

                $list[$key]['shipping_dateStr'] = TimeRepository::getLocalDate("m", TimeRepository::getLocalStrtoTime(' +1day')) . "月" . TimeRepository::getLocalDate("d", TimeRepository::getLocalStrtoTime(' +1day')) . "日&nbsp;【周" . TimeRepository::transitionDate(TimeRepository::getLocalDate('Y-m-d', TimeRepository::getLocalStrtoTime(' +1day'))) . "】";
            }
        }

        return $list;
    }

    /**
     * 退换货可用配送列表
     * @return array
     */
    public function returnShippingList()
    {
        // 排除 上门自提、运费到付 配送方式
        $model = Shipping::query()->where('enabled', 1)->whereNotIn('shipping_code', ['cac', 'fpd']);

        $list = $model->select('shipping_id', 'shipping_code', 'shipping_name', 'shipping_order')->get();

        $cfg = [
            ['name' => 'item_fee', 'value' => 0],
            ['name' => 'base_fee', 'value' => 0],
            ['name' => 'step_fee', 'value' => 0],
            ['name' => 'free_money', 'value' => 100000],
            ['name' => 'step_fee1', 'value' => 0],
            ['name' => 'pack_fee', 'value' => 0],
        ];

        if ($list) {
            foreach ($list as $key => $row) {
                if (!isset($row['configure']) && empty($row['configure'])) {
                    $list[$key]['configure'] = serialize($cfg);
                }
            }
        }

        return $list ? $list->toArray() : [];
    }

    /**
     * 返回购物车商家配送方式列表
     *
     * @param array $cart_goods
     * @param array $consignee
     * @param array $uc_id
     * @param array $tmp_shipping_id 提交订单商品选择配送方式ID
     * @return array
     * @throws \Exception
     */
    public function goodsShippingTransport($cart_goods = [], $consignee = [], $uc_id = [], $tmp_shipping_id = [])
    {
        $cartShippingList = [];
        $shippingFeeList = [];
        $couList = [];
        $couponsRegionList = [];
        if ($cart_goods) {

            /* 初始化购物车商品包邮事件 start */
            foreach ($cart_goods as $cartKey => $cartRow) {
                if ($cartRow['is_shipping'] == 1) {
                    $cart_goods[$cartKey]['freight'] = 1;
                    $cart_goods[$cartKey]['shipping_fee'] = 0;
                }
            }
            /* 初始化购物车商品包邮事件 end */

            $user_rank = BaseRepository::getKeyPluck($cart_goods, 'user_rank');
            $user_rank = BaseRepository::getArrayUnique($user_rank);
            $user_rank = BaseRepository::getImplode($user_rank);

            $user_id = BaseRepository::getKeyPluck($cart_goods, 'user_id');
            $user_id = BaseRepository::getArrayUnique($user_id);
            $uc_id = empty($uc_id) ? 0 : $uc_id;

            /* 处理免邮券 */
            if (!empty($uc_id)) {
                $couponsUserList = CouponDataHandleService::getCouponsUserDataList($uc_id, [], $user_id, ['uc_id', 'cou_id']);
                $couIdList = BaseRepository::getKeyPluck($couponsUserList, 'cou_id');
                $couIdList = BaseRepository::getArrayUnique($couIdList);

                $couList = CouponDataHandleService::getCouponsDataList($couIdList);

                $sql = [
                    'where' => [
                        [
                            'name' => 'cou_type',
                            'value' => VOUCHER_SHIPPING
                        ]
                    ]
                ];
                $couList = BaseRepository::getArraySqlGet($couList, $sql, 1);
                $couponsRegionList = CouponDataHandleService::getCouponsRegionDataList($couIdList);
            }

            $tidList = BaseRepository::getKeyPluck($cart_goods, 'tid');
            $tidList = ArrRepository::getArrayUnset($tidList);
            $tidList = $tidList ? array_unique($tidList) : [];
            $goodsTransportList = GoodsDataHandleService::getGoodsTransportDataList($tidList);
            $transportList = BaseRepository::getGroupBy($goodsTransportList, 'freight_type');

            $customExtendList = [];
            $customExpressList = [];
            $shippingTypeList = [];
            $customList = [];
            $customExpressShippingList = [];
            $shippingTypeDataList = [];
            if ($transportList) {
                $customList = $transportList[0] ?? []; //自定义

                if ($customList) {
                    foreach ($customList as $key => $val) {
                        $sql = [
                            'where' => [
                                [
                                    'name' => 'ru_id',
                                    'value' => $val['ru_id']
                                ],
                                [
                                    'name' => 'tid',
                                    'value' => $val['tid']
                                ],
                                [
                                    'name' => 'is_shipping',
                                    'value' => 0
                                ]
                            ]
                        ];
                        $goodsList = BaseRepository::getArraySqlGet($cart_goods, $sql);

                        $customList[$key]['total_amount'] = BaseRepository::getArraySum($goodsList, ['goods_price', 'goods_number']);
                        $customList[$key]['total_number'] = BaseRepository::getArraySum($goodsList, 'goods_number');
                    }
                }

                $shippingList = $transportList[1] ?? []; //快递模板

                /* 处理自定义运费 start */
                $customTidList = BaseRepository::getKeyPluck($customList, 'tid');
                $shippingTidList = BaseRepository::getKeyPluck($shippingList, 'tid');

                $customExtendList = GoodsDataHandleService::getGoodsTransportExtendDataList($customTidList);
                $customExtendList = $this->customExtendList($customExtendList, $consignee);
                $customTidList = BaseRepository::getKeyPluck($customExtendList, 'tid');
                $customExpressShippingList = GoodsDataHandleService::getGoodsTransportExpressDataList($customTidList);
                $customExpressList = $this->customExpressList($customExpressShippingList, $goodsTransportList);
                /* 处理自定义运费 end */

                /* 处理快递方式运费模板 start */
                $shippingTypeList = GoodsDataHandleService::getGoodsTransportTplDataList($shippingTidList);
                $shippingTypeDataList = $this->shippingTypeDataList($shippingTypeList, $consignee);
                $shippingTypeList = BaseRepository::getGroupBy($shippingTypeDataList, 'user_id');

                $shippingFeeList = $this->shippingFeeList($shippingTypeList, $cart_goods);
                /* 处理快递方式运费模板 end */
            }

            $fixedFreightList = $this->goodsFixedFreight($cart_goods, $user_rank, $couList, $couponsRegionList, $consignee);

            $redRuList = BaseRepository::getColumn($cart_goods, 'ru_id', 'rec_id');
            $redRuList = BaseRepository::getArrayUnique($redRuList);

            $ruShippingList = $this->collectShippingList($redRuList, $customExpressList, $shippingTypeList);

            /* 商家默认快递方式 */
            $sellerInfoList = MerchantDataHandleService::getSellerShopInfoShippingList($redRuList, ['ru_id', 'shipping_id']);

            $customNoTidList = [];
            $insure_disabled = true;
            $cod_disabled = true;
            if ($ruShippingList) {

                $ruCartGoodsList = BaseRepository::getGroupBy($cart_goods, 'ru_id');

                $customExpressShippingList = $this->customExpressShippingList($customExpressShippingList);

                foreach ($ruShippingList as $key => $rows) {

                    $goodsSelf = false;
                    if ($key == 0) {
                        $goodsSelf = true;
                    }

                    //跨境运费
                    $rate_price = BaseRepository::getArraySum($ruCartGoodsList[$key], 'rate_price');

                    $shippingList = $rows['shipping_list'];
                    $shipping_tid_list = $rows['shipping_tid_list']; //获取的运费模板ID列表

                    $cartShippingList[$key]['shipping_rec'] = $cartShippingList[$key]['shipping_rec'] ?? []; //不支持配送购物车商品ID
                    $default_shipping = $sellerInfoList[$key]['shipping_id'] ?? 0;
                    $default_shipping_code = $sellerInfoList[$key]['shipping_code'] ?? '';

                    $sql = [
                        'where' => [
                            [
                                'name' => 'shipping_id',
                                'value' => $default_shipping
                            ]
                        ]
                    ];
                    $have_shipping = BaseRepository::getArraySqlGet($shippingList, $sql, 1);
                    $have_shipping = $have_shipping && $default_shipping ? 1 : 0;

                    /* 判断是否含有地区快递 */
                    $freightGoods = [];
                    if (isset($ruCartGoodsList[$key]) && $ruCartGoodsList[$key]) {
                        $sql = [
                            'where' => [
                                [
                                    'name' => 'freight',
                                    'value' => 2
                                ]
                            ]
                        ];
                        $freightGoods = BaseRepository::getArraySqlGet($ruCartGoodsList[$key], $sql, 1);

                        $sql = [
                            'where' => [
                                [
                                    'name' => 'freight',
                                    'value' => 2
                                ],
                                [
                                    'name' => 'tid',
                                    'value' => 0
                                ],
                                [
                                    'name' => 'is_gift', //不含赠送商品
                                    'value' => 0
                                ],
                                [
                                    'name' => 'is_shipping',
                                    'value' => 0
                                ]
                            ]
                        ];
                        $shipping_rec = BaseRepository::getArraySqlGet($ruCartGoodsList[$key], $sql, 1);
                        $cartShippingList[$key]['shipping_rec'][] = BaseRepository::getKeyPluck($shipping_rec, 'rec_id');

                        $sql = [
                            'where' => [
                                [
                                    'name' => 'freight',
                                    'value' => 2
                                ],
                                [
                                    'name' => 'tid',
                                    'value' => 0,
                                    'condition' => '>' //条件查询
                                ],
                                [
                                    'name' => 'is_gift', //不含赠送商品
                                    'value' => 0
                                ],
                                [
                                    'name' => 'is_shipping',
                                    'value' => 0
                                ]
                            ],
                            'whereNotIn' => [
                                [
                                    'name' => 'tid',
                                    'value' => $shipping_tid_list
                                ]
                            ]
                        ];
                        $shipping_rec = BaseRepository::getArraySqlGet($ruCartGoodsList[$key], $sql, 1);
                        $cartShippingList[$key]['shipping_rec'][] = BaseRepository::getKeyPluck($shipping_rec, 'rec_id');
                    }

                    $is_freight = count($freightGoods) > 0 ? 1 : 0;

                    $cartShippingList[$key]['is_freight'] = $is_freight;
                    $rufixedFreightList = $fixedFreightList[$key] ?? 0; //商品固定运费
                    $fixedFee = $rufixedFreightList['shipping_fee'];
                    $oldFixedFee = $rufixedFreightList['old_shipping_fee'];

                    $sql = [
                        'where' => [
                            [
                                'name' => 'freight',
                                'value' => 2
                            ],
                            [
                                'name' => 'tid',
                                'value' => 0
                            ],
                            [
                                'name' => 'is_gift', //不含赠送商品
                                'value' => 0
                            ],
                            [
                                'name' => 'is_shipping',
                                'value' => 0
                            ]
                        ]
                    ];
                    $ruCartTidGoodsList = BaseRepository::getArraySqlGet($ruCartGoodsList[$key], $sql);
                    $ruCartTidList = BaseRepository::getKeyPluck($ruCartTidGoodsList, 'tid');

                    /* 自定义 */
                    if ($ruCartTidList) {
                        $sql = [
                            'whereIn' => [
                                [
                                    'name' => 'tid',
                                    'value' => $ruCartTidList
                                ]
                            ]
                        ];
                        $customRuCartList = BaseRepository::getArraySqlGet($customList, $sql, 1);
                    } else {
                        $customRuCartList = [];
                    }

                    /* 自定义 */
                    if (!empty($customRuCartList)) {
                        $sql = [
                            'where' => [
                                [
                                    'name' => 'ru_id',
                                    'value' => $key
                                ]
                            ]
                        ];
                        $customCartList = BaseRepository::getArraySqlGet($customList, $sql, 1);
                        $customRuTidList = BaseRepository::getKeyPluck($customRuCartList, 'tid');

                        /* 获取错误tid信息购物车商品ID */
                        $customNoRecIdList = [];
                        if (empty($customCartList)) {
                            $customNoTidList = BaseRepository::getKeyPluck($customRuCartList, 'tid');
                            if ($customNoTidList) {
                                $sql = [
                                    'whereIn' => [
                                        [
                                            'name' => 'tid',
                                            'value' => $customNoTidList
                                        ]
                                    ],
                                    'where' => [
                                        [
                                            'name' => 'is_shipping',
                                            'value' => 0
                                        ]
                                    ]
                                ];
                                $customNoRecIdList = BaseRepository::getArraySqlGet($ruCartGoodsList[$key], $sql, 1);
                            }
                        } else {
                            $sql = [
                                'where' => [
                                    [
                                        'name' => 'ru_id',
                                        'value' => $key,
                                        'condition' => '<>'
                                    ]
                                ],
                                'whereIn' => [
                                    [
                                        'name' => 'tid',
                                        'value' => $ruCartTidList
                                    ]
                                ]
                            ];
                            $customNotEqualCartList = BaseRepository::getArraySqlGet($customList, $sql, 1);
                            $customNoTidList = BaseRepository::getKeyPluck($customNotEqualCartList, 'tid');

                            $customDiffTid = BaseRepository::getArrayDiff($customRuTidList, $customNoTidList);

                            $sql = [
                                'whereIn' => [
                                    [
                                        'name' => 'tid',
                                        'value' => $customDiffTid
                                    ]
                                ],
                                'where' => [
                                    [
                                        'name' => 'ru_id',
                                        'value' => $key
                                    ]
                                ]
                            ];
                            $customNotDiffTid = BaseRepository::getArraySqlGet($customExtendList, $sql, 1);

                            if (empty($customNotDiffTid)) {
                                /* 为空时合并不支持配送的运费模板ID */
                                $customNoTidList = BaseRepository::getArrayMerge($customNoTidList, $customDiffTid);
                            } else {
                                /* 获取自定义不支持配送的运费模板ID */
                                $customNotDiffTid = BaseRepository::getKeyPluck($customNotDiffTid, 'tid');
                                $customDiffTid = BaseRepository::getArrayDiff($customDiffTid, $customNotDiffTid);
                                $customNoTidList = BaseRepository::getArrayMerge($customNoTidList, $customDiffTid);
                            }

                            if ($customNoTidList) {
                                $sql = [
                                    'whereIn' => [
                                        [
                                            'name' => 'tid',
                                            'value' => $customNoTidList
                                        ]
                                    ],
                                    'where' => [
                                        [
                                            'name' => 'is_gift', //不含赠送商品
                                            'value' => 0
                                        ],
                                        [
                                            'name' => 'is_shipping',
                                            'value' => 0
                                        ]
                                    ]
                                ];
                                $customNoRecIdList = BaseRepository::getArraySqlGet($ruCartGoodsList[$key], $sql, 1);
                            }
                        }

                        $cartShippingList[$key]['shipping_rec'][] = BaseRepository::getKeyPluck($customNoRecIdList, 'rec_id');

                        if ($customNoTidList) {
                            $sql = [
                                'whereNotIn' => [
                                    [
                                        'name' => 'tid',
                                        'value' => $customNoTidList,
                                        'condition' => '<>'
                                    ]
                                ]
                            ];
                            $customCartList = BaseRepository::getArraySqlGet($customCartList, $sql, 1);
                        }
                    } else {
                        $customCartList = [];
                    }

                    /* 快递模板 */
                    $sql = [
                        'where' => [
                            [
                                'name' => 'user_id',
                                'value' => $key
                            ]
                        ]
                    ];
                    $ruShippingTypeDataList = BaseRepository::getArraySqlGet($shippingTypeDataList, $sql, 1);

                    if ($is_freight > 0 && !empty($shippingList)) {

                        //处理免邮券
                        $couInfo = $this->couInfo($couList, $couponsRegionList, $consignee, $key, $cart_goods, $user_rank);


                        $sql = [
                            'where' => [
                                [
                                    'name' => 'shipping_code',
                                    'value' => 'express'
                                ]
                            ]
                        ];
                        $expressInfo = BaseRepository::getArraySqlFirst($shippingList, $sql);

                        /*------------------------------------------------------ */
                        //-- 设置快递配送为默认显示
                        /*------------------------------------------------------ */
                        $cartShippingList[$key]['is_express'] = 0;
                        if (!empty($expressInfo)) {
                            $default_shipping = $expressInfo['shipping_id'];
                            $have_shipping = 1;
                        }

                        /*------------------------------------------------------ */
                        //-- 店铺默认配送方式为快递配送默认显示
                        /*------------------------------------------------------ */
                        if ($have_shipping && 0 && empty($shippingList) && $default_shipping_code == 'express') {
                            $shippingList[] = [
                                'shipping_id' => $sellerInfoList[$key]['shipping_id'],
                                'shipping_code' => $sellerInfoList[$key]['shipping_code'],
                                'shipping_name' => $sellerInfoList[$key]['shipping_name'],
                                'shipping_order' => $sellerInfoList[$key]['shipping_order'],
                                'tid' => $shippingList[0]['tid'],
                                'free_money' => $shippingList[0]['free_money']
                            ];

                            $default_shipping = $sellerInfoList[$key]['shipping_id'];
                            $have_shipping = 1;
                            $cartShippingList[$key]['is_express'] = 1;
                        }

                        /*------------------------------------------------------ */
                        //-- 显示快递
                        /*------------------------------------------------------ */
                        foreach ($shippingList as $skey => $svalue) {
                            $extendShippingFee = 0; //运费模板自定义模式运费金额

                            foreach ($customExpressList as $idx => $item) {

                                if ($cartShippingList[$key]['is_express'] == 1) {
                                    $item['shipping_id'] = BaseRepository::getArrayMerge($item['shipping_id'], [$sellerInfoList[$key]['shipping_id']]);
                                }

                                $cartShippingList[$key]['shipping'][$skey]['customExtend'][$item['tid']] = $cartShippingList[$key]['shipping'][$skey]['customExtend'][$item['tid']] ?? [];
                                $cartShippingList[$key]['shipping'][$skey]['customExpress'][$item['tid']] = $cartShippingList[$key]['shipping'][$skey]['customExpress'][$item['tid']] ?? [];

                                if (in_array($svalue['shipping_id'], $item['shipping_id'])) {
                                    $custom = [];
                                    if ($customCartList) {
                                        $sql = [
                                            'where' => [
                                                [
                                                    'name' => 'tid',
                                                    'value' => $item['tid']
                                                ]
                                            ]
                                        ];
                                        $custom = BaseRepository::getArraySqlFirst($customCartList, $sql);
                                    }

                                    //判断[自定义运费模板类型]是否免运费
                                    $is_shipping = 0;
                                    if ($custom && $custom['free_money'] > 0 && $custom['total_amount'] >= $custom['free_money']) {
                                        $is_shipping = 1;
                                    }

                                    /* 非免运费 */
                                    if ($is_shipping == 0) {

                                        $freightType = $svalue['free_money'][$item['tid']]['freight_type'] ?? 0;
                                        $custom_free_money = $svalue['free_money'][$item['tid']]['free'] ?? -1;
                                        $customType = $svalue['free_money'][$item['tid']]['type'] ?? 0;

                                        $sql = [
                                            'where' => [
                                                [
                                                    'name' => 'ru_id',
                                                    'value' => $key
                                                ],
                                                [
                                                    'name' => 'tid',
                                                    'value' => $item['tid']
                                                ]
                                            ]
                                        ];
                                        $customRuCartGoodsList = BaseRepository::getArraySqlGet($ruCartGoodsList[$key], $sql);
                                        $customRuCartAmount = BaseRepository::getArraySum($customRuCartGoodsList, ['goods_number', 'goods_price']);

                                        $sql = [
                                            'where' => [
                                                [
                                                    'name' => 'tid',
                                                    'value' => $item['tid']
                                                ],
                                                [
                                                    'name' => 'ru_id',
                                                    'value' => $key
                                                ]
                                            ],
                                        ];

                                        if ($customNoTidList) {
                                            $sql['whereNotIn'] = [
                                                [
                                                    'name' => 'tid',
                                                    'value' => $customNoTidList,
                                                    'condition' => '<>'
                                                ]
                                            ];
                                        }

                                        $customExtend = BaseRepository::getArraySqlGet($customExtendList, $sql, 1);

                                        /* 计算自定义按商品件数 */
                                        $customGoodsNumber = 0;
                                        if ($customType == 1) {
                                            $sql = [
                                                'where' => [
                                                    [
                                                        'name' => 'tid',
                                                        'value' => $item['tid']
                                                    ]
                                                ]
                                            ];
                                            $customTypeGoodsList = BaseRepository::getArraySqlGet($ruCartGoodsList[$key], $sql);
                                            $customGoodsNumber = BaseRepository::getArraySum($customTypeGoodsList, 'goods_number');
                                        }

                                        $cartShippingList[$key]['shipping'][$skey]['customExtend'][$item['tid']] = $customExtend;

                                        if (!($freightType == 0 && $customRuCartAmount >= $custom_free_money) || $custom_free_money <= 0) {

                                            $sprice = BaseRepository::getArraySum($customExtend, 'sprice');
                                            $sprice = $sprice ?: 0;

                                            if ($customGoodsNumber > 0) {
                                                $sprice = $sprice * $customGoodsNumber;
                                            }

                                            $extendShippingFee += $sprice;
                                        }

                                        if ($item['ru_id'] == $key) {

                                            if ($customNoTidList) {
                                                $sql = [
                                                    'whereNotIn' => [
                                                        [
                                                            'name' => 'tid',
                                                            'value' => $customNoTidList,
                                                            'condition' => '<>'
                                                        ]
                                                    ]
                                                ];
                                                $item = BaseRepository::getArraySqlGet($item, $sql, 1);
                                            }

                                            $cartShippingList[$key]['shipping'][$skey]['customExpress'][$item['tid']] = $item;

                                            if ($customNoTidList) {
                                                $sql = [
                                                    'whereNotIn' => [
                                                        [
                                                            'name' => 'tid',
                                                            'value' => $customNoTidList,
                                                            'condition' => '<>'
                                                        ]
                                                    ]
                                                ];
                                                $customExpressShippingList[$item['tid']] = BaseRepository::getArraySqlGet($customExpressShippingList[$item['tid']], $sql, 1);
                                            }

                                            $customExpress = $customExpressShippingList[$item['tid']];

                                            foreach ($customExpress as $ckey => $cval) {

                                                if ($cartShippingList[$key]['is_express'] == 1) {
                                                    $cval['shipping_id'] = BaseRepository::getArrayMerge($cval['shipping_id'], [$sellerInfoList[$key]['shipping_id']]);
                                                }

                                                if (in_array($svalue['shipping_id'], $cval['shipping_id'])) {
                                                    $cartShippingList[$key]['shipping'][$skey]['customExpress'][$item['tid']]['shipping_fee'] = $cval['shipping_fee'];

                                                    if (!($freightType == 0 && $customRuCartAmount >= $custom_free_money) || $custom_free_money <= 0) {
                                                        if ($customGoodsNumber > 0) {
                                                            $extendShippingFee += $cval['shipping_fee'] * $customGoodsNumber;
                                                        } else {
                                                            $extendShippingFee += $cval['shipping_fee'];
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            $sql = [
                                'where' => [
                                    [
                                        'name' => 'shipping_id',
                                        'value' => $svalue['shipping_id']
                                    ],
                                    [
                                        'name' => 'ru_id',
                                        'value' => $key
                                    ]
                                ]
                            ];
                            $shipping = BaseRepository::getArraySqlGet($shippingFeeList, $sql, 1);
                            $shipping_fee = BaseRepository::getArraySum($shipping, 'shipping_fee'); //快递模板运费金额

                            if (empty($couInfo)) {
                                $cartShippingList[$key]['shipping'][$skey]['shipping_fee'] = $this->dscRepository->changeFloat($extendShippingFee + $shipping_fee + $fixedFee);
                            } else {
                                //免邮券
                                $cartShippingList[$key]['shipping'][$skey]['shipping_fee'] = 0;
                            }

                            $cartShippingList[$key]['shipping'][$skey]['old_shipping_fee'] = $this->dscRepository->changeFloat($extendShippingFee + $shipping_fee + $oldFixedFee);

                            $cartShippingList[$key]['shipping'][$skey]['shipping_fee'] = $this->dscRepository->getPriceFormat($cartShippingList[$key]['shipping'][$skey]['shipping_fee'], true, false, $goodsSelf);
                            $cartShippingList[$key]['shipping'][$skey]['format_shipping_fee'] = $this->dscRepository->getPriceFormat($cartShippingList[$key]['shipping'][$skey]['shipping_fee'], true, true, $goodsSelf);
                            $cartShippingList[$key]['shipping'][$skey]['insure_disabled'] = $insure_disabled;
                            $cartShippingList[$key]['shipping'][$skey]['cod_disabled'] = $cod_disabled;
                            $cartShippingList[$key]['shipping'][$skey]['free_money'] = $this->dscRepository->getPriceFormat(0, true, true, $goodsSelf); //无作用

                            $cartShippingList[$key]['shipping'][$skey]['shipping_id'] = $svalue['shipping_id'];
                            $cartShippingList[$key]['shipping'][$skey]['shipping_name'] = $svalue['shipping_name'];
                            $cartShippingList[$key]['shipping'][$skey]['shipping_code'] = $svalue['shipping_code'];
                            $cartShippingList[$key]['shipping'][$skey]['shipping_order'] = $svalue['shipping_order'];
                            $cartShippingList[$key]['shipping'][$skey]['rate_price'] = $rate_price; //跨境运费
                            $cartShippingList[$key]['shipping'][$skey]['tid'] = $svalue['tid'];

                            //默认配送方式
                            $default = 0;
                            if (!empty($tmp_shipping_id)) {
                                foreach ($tmp_shipping_id as $tkey => $tSid) {
                                    if ($key == $tkey && $tSid == $svalue['shipping_id']) {
                                        $default_shipping = $svalue['shipping_id'];
                                        $default = 1;
                                    }
                                }
                            } else {
                                if ($have_shipping == 1) {
                                    $shipping_list[$key]['default'] = 0;
                                    if ($default_shipping == $svalue['shipping_id']) {
                                        $default_shipping = $svalue['shipping_id'];
                                        $default = 1;
                                    }
                                } else {
                                    if ($skey == 0) {
                                        $default = 1;
                                    }
                                }
                            }

                            $cartShippingList[$key]['shipping'][$skey]['default'] = $default;

                            unset($cartShippingList[$key]['shipping'][$skey]['customExtend']);
                            unset($cartShippingList[$key]['shipping'][$skey]['customExpress']);


                            /* 检测快递模板不支持配送快递方式，并找到对应购物车商品 */
                            if ($ruShippingTypeDataList) {
                                $sql = [
                                    'where' => [
                                        [
                                            'name' => 'shipping_id',
                                            'value' => $svalue['shipping_id']
                                        ]
                                    ]
                                ];
                                $ruSpList = BaseRepository::getArraySqlGet($ruShippingTypeDataList, $sql, 1);

                                if (empty($ruSpList)) {
                                    $noShippingTid = BaseRepository::getKeyPluck($ruShippingTypeDataList, 'tid');

                                    if ($noShippingTid) {
                                        $sql = [
                                            'whereIn' => [
                                                [
                                                    'name' => 'tid',
                                                    'value' => $noShippingTid
                                                ]
                                            ],
                                            'where' => [
                                                [
                                                    'name' => 'is_gift', //不含赠送商品
                                                    'value' => 0
                                                ],
                                                [
                                                    'name' => 'is_shipping',
                                                    'value' => 0
                                                ]
                                            ]
                                        ];
                                        $ruTipGoodsList = BaseRepository::getArraySqlGet($freightGoods, $sql);
                                        $cartShippingList[$key]['shipping_rec'][] = BaseRepository::getKeyPluck($ruTipGoodsList, 'rec_id');
                                    }
                                } else {
                                    $ruIsTidList = BaseRepository::getKeyPluck($ruSpList, 'tid');
                                    $sql = [
                                        'whereNotIn' => [
                                            [
                                                'name' => 'tid',
                                                'value' => $ruIsTidList
                                            ]
                                        ]
                                    ];
                                    $ruNotTidList = BaseRepository::getArraySqlGet($ruShippingTypeDataList, $sql, 1);
                                    $noShippingTid = BaseRepository::getKeyPluck($ruNotTidList, 'tid');

                                    if ($noShippingTid) {
                                        $sql = [
                                            'whereIn' => [
                                                [
                                                    'name' => 'tid',
                                                    'value' => $noShippingTid
                                                ]
                                            ],
                                            'where' => [
                                                [
                                                    'name' => 'is_gift', //不含赠送商品
                                                    'value' => 0
                                                ],
                                                [
                                                    'name' => 'is_shipping',
                                                    'value' => 0
                                                ]
                                            ]
                                        ];
                                        $ruTipGoodsList = BaseRepository::getArraySqlGet($freightGoods, $sql, 1);
                                        $cartShippingList[$key]['shipping_rec'][] = BaseRepository::getKeyPluck($ruTipGoodsList, 'rec_id');
                                    }
                                }
                            }
                        }

                        $cartShippingList[$key]['shipping'] = array_values($cartShippingList[$key]['shipping']);
                    } else {

                        $sql = [
                            'where' => [
                                [
                                    'name' => 'ru_id',
                                    'value' => $key
                                ]
                            ]
                        ];
                        $ruShippingFeeList = BaseRepository::getArraySqlGet($shippingFeeList, $sql);

                        // 快递模板地区不支持配送
                        if ($is_freight > 0 && empty($ruShippingFeeList)) {
                            $cartShippingList[$key]['shipping'] = [];
                        } else {
                            $ruDefaultShipping = ShippingDataHandleService::getShippingDataList($default_shipping, 1, ['shipping_id', 'shipping_code', 'shipping_name', 'shipping_order']);
                            if ($ruDefaultShipping) {
                                $ruDefaultShipping[$default_shipping]['shipping_fee'] = $this->dscRepository->changeFloat($fixedFee);
                                $ruDefaultShipping[$default_shipping]['old_shipping_fee'] = $this->dscRepository->changeFloat($oldFixedFee);
                                $ruDefaultShipping[$default_shipping]['format_shipping_fee'] = $this->dscRepository->getPriceFormat($ruDefaultShipping[$default_shipping]['shipping_fee'], true, true, $goodsSelf);
                                $ruDefaultShipping[$default_shipping]['insure_disabled'] = 0;
                                $ruDefaultShipping[$default_shipping]['cod_disabled'] = 0;
                                $ruDefaultShipping[$default_shipping]['free_money'] = $this->dscRepository->getPriceFormat(0, true, true, $goodsSelf); //无作用
                                $ruDefaultShipping[$default_shipping]['default'] = 1;
                                $ruDefaultShipping[$default_shipping]['rate_price'] = $rate_price; //跨境运费
                                $ruDefaultShipping[$default_shipping]['tid'] = [];
                                $ruDefaultShipping = array_values($ruDefaultShipping);
                            }

                            $cartShippingList[$key]['shipping'] = $ruDefaultShipping;
                        }
                    }

                    /* 此处删除多商户跨境商品计算税费累加代码【大部分用户用不到，所以删除，需要再根据情况开发重写代码】 */

                    $cartShippingList[$key]['shipping_count'] = count($cartShippingList[$key]['shipping']);
                    $cartShippingList[$key]['tmp_shipping_id'] = $default_shipping;

                    $cartShippingList[$key]['shipping'] = $cartShippingList[$key]['shipping'] ? $cartShippingList[$key]['shipping'] : [];

                    $defaultShippingList = [];
                    if ($cartShippingList[$key]['shipping']) {
                        $sql = [
                            'where' => [
                                [
                                    'name' => 'default',
                                    'value' => 1
                                ]
                            ]
                        ];
                        $defaultShippingList = BaseRepository::getArraySqlFirst($cartShippingList[$key]['shipping'], $sql);
                    }

                    $cartShippingList[$key]['default_shipping'] = $defaultShippingList ? $defaultShippingList : [];

                    if (empty($cartShippingList[$key]['default_shipping'])) {
                        $cartShippingList[$key]['default_shipping'] = [
                            'shipping_id' => 0,
                            'shipping_name' => lang('flow.shiping_prompt'),
                            'shipping_fee' => 0,
                            'rate_price' => $rate_price, //跨境运费
                            'tid' => []
                        ];

                        if (!empty($ruCartGoodsList[$key])) {
                            $sql = [
                                'where' => [
                                    [
                                        'name' => 'is_gift', //不含赠送商品
                                        'value' => 0
                                    ],
                                    [
                                        'name' => 'is_shipping',
                                        'value' => 0
                                    ]
                                ]
                            ];
                            $ruCartGoodsList[$key] = BaseRepository::getArraySqlGet($ruCartGoodsList[$key], $sql);
                        }

                        $cartShippingList[$key]['shipping_rec'][] = $ruCartGoodsList[$key] ? BaseRepository::getKeyPluck($ruCartGoodsList[$key], 'rec_id') : [];
                    }

                    $cartShippingList[$key]['shipping_rec'] = BaseRepository::getFlatten($cartShippingList[$key]['shipping_rec']);
                    $cartShippingList[$key]['shipping_rec'] = BaseRepository::getArrayUnique($cartShippingList[$key]['shipping_rec']);
                }
            }
        }

        /* 划分选择的配送方式商品不支持配送包含其中 */
        $cartShippingList = $this->cartShippingList($cartShippingList, $cart_goods);

        return $cartShippingList;
    }

    /**
     * 处理选择运费模板的商品
     *
     * @param array $cartShippingList
     * @param array $cart_goods
     * @return array
     */
    private function cartShippingList($cartShippingList = [], $cart_goods = [])
    {
        $arr = [];
        if ($cartShippingList) {
            foreach ($cartShippingList as $ru_id => $row) {

                $default_shipping = $row['default_shipping'] ?? []; //选中配送方式

                $is_express = $row['is_express'] ?? 0;
                if ($is_express == 1) {

                    $row['shipping'] = BaseRepository::getSortBy($row['shipping'], 'shipping_fee');

                    $sql = [
                        'where' => [
                            [
                                'name' => 'shipping_fee',
                                'value' => 0,
                                'condition' => '>' //条件查询
                            ]
                        ]
                    ];
                    $shippingInfo = BaseRepository::getArraySqlFirst($row['shipping'], $sql);

                    $sql = [
                        'where' => [
                            [
                                'name' => 'shipping_fee',
                                'value' => 0
                            ]
                        ]
                    ];
                    $express = BaseRepository::getArraySqlFirst($row['shipping'], $sql);

                    if (!empty($express)) {
                        foreach ($row['shipping'] as $k => $v) {
                            if ($v['shipping_code'] == 'express') {
                                $row['shipping'][$k]['shipping_fee'] = $shippingInfo['shipping_fee'];
                                $row['shipping'][$k]['old_shipping_fee'] = $shippingInfo['old_shipping_fee'];
                                $row['shipping'][$k]['format_shipping_fee'] = $shippingInfo['format_shipping_fee'];
                            }
                        }
                    }


                    if (empty($default_shipping)) {

                        $sql = [
                            'where' => [
                                [
                                    'name' => 'default',
                                    'value' => 1
                                ]
                            ]
                        ];

                        $default_shipping = BaseRepository::getArraySqlFirst($row['shipping'], $sql);
                    } else {
                        $sql = [
                            'where' => [
                                [
                                    'name' => 'shipping_id',
                                    'value' => $default_shipping['shipping_id']
                                ]
                            ]
                        ];


                        $default_shipping = BaseRepository::getArraySqlFirst($row['shipping'], $sql);
                    }

                    $row['default_shipping'] = $default_shipping;
                }

                $arr[$ru_id] = $row;

                $shipping_rec = $row['shipping_rec'];

                $sql = [
                    'where' => [
                        [
                            'name' => 'ru_id',
                            'value' => $ru_id
                        ],
                        [
                            'name' => 'tid',
                            'value' => 0,
                            'condition' => '>'
                        ]
                    ],
                    'whereIn' => [
                        [
                            'name' => 'rec_id',
                            'value' => $shipping_rec
                        ]
                    ]
                ];
                $goodsList = BaseRepository::getArraySqlGet($cart_goods, $sql);
                $recTidList = BaseRepository::getColumn($goodsList, 'tid', 'rec_id');

                $tidList = $default_shipping['tid'];
                foreach ($recTidList as $rec_id => $tid) {

                    if ($tidList) {
                        if (!in_array($tid, $tidList)) {
                            $arr[$ru_id]['shipping_rec'][] = $rec_id;
                        } else {
                            $arr[$ru_id]['shipping_rec'] = [];
                        }
                    }
                }

                $arr[$ru_id]['shipping_rec'] = BaseRepository::getArrayUnique($arr[$ru_id]['shipping_rec']);
            }
        }

        return $arr;
    }

    /**
     * 购物车商品按店铺获取配送方式
     *
     * @param $redRuList
     * @param $customExpressList
     * @param $shippingTypeList
     * @return array
     */
    private function collectShippingList($redRuList, $customExpressList, $shippingTypeList)
    {
        $list = [];
        if ($redRuList) {
            $redRuList = array_unique($redRuList);
            $arr = [];

            $shippingIdList = [];
            foreach ($redRuList as $key => $ru_id) {

                $sql = [
                    'where' => [
                        [
                            'name' => 'ru_id',
                            'value' => $ru_id
                        ]
                    ]
                ];
                $customExpress = BaseRepository::getArraySqlGet($customExpressList, $sql);
                $expressShippingId = BaseRepository::getKeyPluck($customExpress, 'shipping_id');

                $shippingType = $shippingTypeList[$ru_id] ?? [];
                $shippingTypeId = BaseRepository::getKeyPluck($shippingType, 'shipping_id');

                $shippingIdList[] = [
                    BaseRepository::getFlatten($expressShippingId), BaseRepository::getFlatten($shippingTypeId)
                ];

                $expressShippingId = BaseRepository::getFlatten($expressShippingId);
                $expressShippingId = BaseRepository::getImplode($expressShippingId);
                $expressShippingId = BaseRepository::getExplode($expressShippingId);
                $arr[$ru_id]['custom_express'] = $expressShippingId;
                $arr[$ru_id]['shipping_type'] = BaseRepository::getFlatten($shippingTypeId);

                $customExpressTid = BaseRepository::getKeyPluck($customExpress, 'tid');
                $shippingTypeTid = BaseRepository::getKeyPluck($shippingType, 'tid');

                $arr[$ru_id]['tid'] = BaseRepository::getArrayMerge($customExpressTid, $shippingTypeTid);
            }

            $shippingIdList = BaseRepository::getFlatten($shippingIdList);
            $shippingIdList = array_unique($shippingIdList);
            $shippingIdList = array_values($shippingIdList);

            $shippingList = ShippingDataHandleService::getShippingDataList($shippingIdList, 1, ['shipping_id', 'shipping_code', 'shipping_name', 'shipping_order']);

            foreach ($arr as $k => $v) {
                $tidList = $v['tid'];
                unset($v['tid']);

                $id = BaseRepository::getFlatten($v);
                $id = BaseRepository::getArrayUnique($id);

                $shipping_list = [];
                if ($id) {
                    $sql = [
                        'whereIn' => [
                            [
                                'name' => 'shipping_id',
                                'value' => $id
                            ]
                        ]
                    ];
                    $shipping_list = BaseRepository::getArraySqlGet($shippingList, $sql);
                }

                $shipping_list = $this->cartShippingAllList($shipping_list, $customExpressList, $shippingTypeList);
                $list[$k]['shipping_list'] = BaseRepository::getSortBy($shipping_list, 'shipping_order');
                $list[$k]['shipping_tid_list'] = $tidList;
            }
        }

        return $list;
    }

    /**
     * 配送方式划分运费模板Id
     *
     * @param array $shipping_list
     * @param array $customExpressList
     * @param array $shippingTypeList
     * @return array
     */
    private function cartShippingAllList($shipping_list = [], $customExpressList = [], $shippingTypeList = [])
    {
        /* 二维数组转为一维数组 */
        $shippingTypeList = ArrRepository::getArrCollapse($shippingTypeList);

        if ($shipping_list) {
            foreach ($shipping_list as $idx => $row) {
                $shipping_list[$idx]['tid'] = [];
                foreach ($customExpressList as $custom) {
                    if (!empty($custom['shipping_id']) && in_array($row['shipping_id'], $custom['shipping_id'])) {
                        $shipping_list[$idx]['tid'][] = $custom['tid'];
                        $shipping_list[$idx]['free_money'][$custom['tid']]['tid'] = $custom['tid'];
                        $shipping_list[$idx]['free_money'][$custom['tid']]['free'] = $custom['free_money'];
                        $shipping_list[$idx]['free_money'][$custom['tid']]['type'] = $custom['type'];
                        $shipping_list[$idx]['free_money'][$custom['tid']]['freight_type'] = $custom['freight_type'];
                    }
                }

                foreach ($shippingTypeList as $type) {
                    if ($row['shipping_id'] == $type['shipping_id']) {
                        $shipping_list[$idx]['tid'][] = $type['tid'];
                    }
                }
            }
        }

        return $shipping_list;
    }

    /**
     * 查询运费模板自定义地区获取信息[支持收货地址]
     *
     * @param array $customExtendList
     * @param array $consignee
     * @return array
     */
    private function customExtendList($customExtendList = [], $consignee = [])
    {
        if ($customExtendList) {
            foreach ($customExtendList as $key => $val) {

                $top_area_id = BaseRepository::getExplode($val['top_area_id']); //省
                $area_id = BaseRepository::getExplode($val['area_id']); //市

                $consignee['province'] = $consignee['province'] ?? 0;
                $consignee['city'] = $consignee['city'] ?? 0;
                $isProvince = in_array($consignee['province'], $top_area_id);
                $isCity = in_array($consignee['city'], $area_id);

                if (!($isProvince && $isCity)) {
                    unset($customExtendList[$key]);
                } else {
                    $customExtendList[$key]['area_id'] = $area_id;
                    $customExtendList[$key]['top_area_id'] = $top_area_id;
                }
            }

            $customExtendList = $customExtendList ? array_values($customExtendList) : [];
        }

        return $customExtendList;
    }

    /**
     * 运费模板自定义快递方式列表
     *
     * @param array $customExpressList
     * @param array $goodsTransportList
     * @return array
     */
    private function customExpressList($customExpressList = [], $goodsTransportList = [])
    {
        $list = [];
        if ($customExpressList) {
            foreach ($customExpressList as $key => $val) {
                $shipping_id = BaseRepository::getKeyPluck($val, 'shipping_id');
                $shipping_id = BaseRepository::getExplode($shipping_id);
                $shipping_id = BaseRepository::getImplode($shipping_id);
                $shipping_id = BaseRepository::getExplode($shipping_id);

                $list[$key]['shipping_id'] = $shipping_id;
                $list[$key]['tid'] = $key;
                $list[$key]['freight_type'] = $goodsTransportList[$key]['freight_type']; //运费模板类型
                $list[$key]['free_money'] = $goodsTransportList[$key]['free_money']; //免费运费金额
                $list[$key]['type'] = $goodsTransportList[$key]['type']; //计算方式
                $list[$key]['ru_id'] = $val[0]['ru_id'];
            }
        }

        return $list;
    }

    /**
     * 扩展运费转换字符串为数组
     *
     * @param array $customExpressShippingList
     * @return array
     */
    private function customExpressShippingList($customExpressShippingList = [])
    {
        if ($customExpressShippingList) {
            foreach ($customExpressShippingList as $key => $list) {
                foreach ($list as $k => $v) {
                    $customExpressShippingList[$key][$k]['shipping_id'] = BaseRepository::getExplode($v['shipping_id']);
                }
            }
        }

        return $customExpressShippingList;
    }

    /**
     * 配送方式[支持收货地址]
     *
     * @param $shippingTypeList
     * @param array $consignee
     * @return mixed
     */
    private function shippingTypeDataList($shippingTypeList, $consignee = [])
    {
        $consignee['province'] = $consignee['province'] ?? 0;
        $consignee['city'] = $consignee['city'] ?? 0;
        $consignee['district'] = $consignee['district'] ?? 0;
        $consignee['street'] = $consignee['street'] ?? 0;
        if ($shippingTypeList) {
            foreach ($shippingTypeList as $key => $val) {

                $region_id = BaseRepository::getExplode($val['region_id']);

                $isProvince = in_array($consignee['province'], $region_id);
                $isCity = in_array($consignee['city'], $region_id);
                $isDistrict = in_array($consignee['district'], $region_id);
                $isStreet = in_array($consignee['street'], $region_id);

                if (!($isProvince || $isCity || $isDistrict || $isStreet)) {
                    unset($shippingTypeList[$key]);
                }
            }
        }

        return $shippingTypeList;
    }

    /**
     * 获取购物车运费模板运费金额
     *
     * @param $shippingTypeList
     * @param $cart_goods
     * @return array
     */
    private function shippingFeeList($shippingTypeList, $cart_goods)
    {
        $sql = [
            'where' => [
                [
                    'name' => 'is_shipping',
                    'value' => 0
                ]
            ]
        ];
        $cart_goods = BaseRepository::getArraySqlGet($cart_goods, $sql);

        /* 购物车商品为空时，跳出计算运费，默认运费0元 */
        if (empty($cart_goods)) {
            return [];
        }

        $goodsList = [];
        if ($cart_goods) {
            $cart_goods = BaseRepository::getGroupBy($cart_goods, 'ru_id');

            foreach ($cart_goods as $key => $val) {
                $goodsList[$key] = BaseRepository::getGroupBy($val, 'tid');
            }
        }

        $arr = [];

        $shippingIdList = [];
        foreach ($shippingTypeList as $k => $v) {
            $shippingIdList[] = BaseRepository::getKeyPluck($v, 'shipping_id');
        }

        $shippingIdList = BaseRepository::getFlatten($shippingIdList);
        $shippingIdList = BaseRepository::getArrayUnique($shippingIdList);

        $shippingList = ShippingDataHandleService::getShippingDataList($shippingIdList, 1, ['shipping_id', 'shipping_code', 'shipping_name', 'shipping_order']);

        foreach ($shippingTypeList as $k => $v) {
            $goods = $goodsList[$k] ?? [];

            foreach ($v as $idx => $item) {

                $TidGoodsList = $goods[$item['tid']] ?? [];

                if (!empty($TidGoodsList)) {
                    $tidInfo['id'] = $item['id'];
                    $tidInfo['ru_id'] = $k;
                    $tidInfo['tid'] = $item['tid'];
                    $tidInfo['goods_amount'] = BaseRepository::getArraySum($TidGoodsList, ['goods_price', 'goods_number']);
                    $tidInfo['total_number'] = BaseRepository::getArraySum($TidGoodsList, 'goods_number');

                    $total_weight = 0;

                    foreach ($TidGoodsList as $h => $t) {

                        $sku_weight = $t['sku_weight'] ?? 0; // 重新根据sku信息计算重量
                        $goods_weight = $t['get_goods']['goods_weight'] ?? 0;

                        // sku重量大于商品重量 使用sku重量 否则使用商品重量
                        $total_weight += $sku_weight > $goods_weight ? $sku_weight * $t['goods_number'] : $goods_weight * $t['goods_number'];
                    }

                    $tidInfo['total_weight'] = $total_weight;
                    $tidInfo['shipping_id'] = $item['shipping_id'];

                    $configure = $this->unserializeConfig($item['configure']);

                    $shipping = $shippingList[$item['shipping_id']] ?? [];
                    $tidInfo['shipping_name'] = $shipping['shipping_name'] ?? '';
                    $tidInfo['free_money'] = isset($configure['free_money']) && !empty($configure['free_money']) ? $configure['free_money'] : 0;

                    $shipping_code = $shipping['shipping_code'] ?? '';
                    $tidInfo['shipping_fee'] = $this->dscRepository->shippingFee($shipping_code, $item['configure'], $total_weight, $tidInfo['goods_amount'], $tidInfo['total_number']);

                    $arr[] = $tidInfo;
                }
            }
        }

        return $arr;
    }

    /**
     * 获取购物车商品固定运费金额[仅合算未免运费购物车商品固定运费金额]
     *
     * @param array $cart_goods
     * @param int $user_rank
     * @param array $couList
     * @param array $couponsRegionList
     * @param array $consignee
     * @return array
     */
    private function goodsFixedFreight($cart_goods = [], $user_rank = 0, $couList = [], $couponsRegionList = [], $consignee = [])
    {
        $arr = [];
        if ($cart_goods) {
            $sql = [
                'where' => [
                    [
                        'name' => 'is_shipping',
                        'value' => 0
                    ],
                    [
                        'name' => 'freight',
                        'value' => 1
                    ]
                ]
            ];
            $cart_goods = BaseRepository::getArraySqlGet($cart_goods, $sql);
            $cart_goods = BaseRepository::getGroupBy($cart_goods, 'ru_id');

            if ($cart_goods) {
                foreach ($cart_goods as $key => $goodsList) {

                    $arr[$key]['shipping_fee'] = $arr[$key]['shipping_fee'] ?? 0;
                    $arr[$key]['old_shipping_fee'] = BaseRepository::getArraySum($goodsList, ['goods_number', 'shipping_fee']);

                    if (!empty($couList)) {
                        foreach ($goodsList as $k => $row) {
                            //处理免邮券
                            $couInfo = $this->couInfo($couList, $couponsRegionList, $consignee, $key, [$row], $user_rank);

                            if (empty($couInfo)) {
                                $shipping_fee = $row['goods_number'] * $row['shipping_fee'];
                            } else {
                                $shipping_fee = 0;
                            }

                            $arr[$key]['shipping_fee'] += $shipping_fee;
                        }
                    } else {
                        $arr[$key]['shipping_fee'] = BaseRepository::getArraySum($goodsList, ['goods_number', 'shipping_fee']);
                    }
                }
            }
        }

        return $arr;
    }

    /**
     * 购物流程配送方式选择运费总额
     *
     * @param array $cart_goods
     * @param array $ru_id
     * @param array $shippingList
     * @param array $tmp_shipping_id
     * @return array
     */
    public function orderFeeShipping($cart_goods, $ru_id = [], $shippingList = [], $tmp_shipping_id = [])
    {
        $arr = [
            'ru_shipping_fee_list' => [],
            'shipping_fee' => 0
        ];

        // 过滤免邮商品
        $sql = [
            'where' => [
                [
                    'name' => 'is_shipping',
                    'value' => 0
                ]
            ]
        ];
        $cart_goods = BaseRepository::getArraySqlGet($cart_goods, $sql);

        if (!empty($cart_goods)) {
            foreach ($ru_id as $key => $seller_id) {
                $shipping = $shippingList[$seller_id]['shipping'];

                $default_shipping_id = $shippingList[$seller_id]['tmp_shipping_id'] ?? 0;
                $ru_shipping_id = $tmp_shipping_id[$seller_id] ?? $default_shipping_id;

                if ($ru_shipping_id) {
                    $sql = [
                        'where' => [
                            [
                                'name' => 'shipping_id',
                                'value' => $ru_shipping_id
                            ]
                        ]
                    ];

                    $shippingInfo = BaseRepository::getArraySqlFirst($shipping, $sql);

                    $shipping_fee = $shippingInfo && !empty($shippingInfo['shipping_fee']) ? $shippingInfo['shipping_fee'] : 0;

                    $arr['ru_shipping_fee_list'][$seller_id] = $this->dscRepository->changeFloat($shipping_fee);
                    $arr['shipping_fee'] += $shipping_fee;
                }
            }
        }

        return $arr;
    }

    /**
     * 购物车商品不支持配送
     *
     * @param array $shippingList
     * @return array
     */
    public function orderNotShippingCartGoodsList($shippingList = [])
    {
        $notShippingRecList = [];
        if ($shippingList) {
            foreach ($shippingList as $ru_id => $value) {
                if ($value['shipping_rec']) {
                    $notShippingRecList[$ru_id] = $value['shipping_rec'];
                }
            }

            if ($notShippingRecList) {
                $notShippingRecList = ArrRepository::getArrCollapse($notShippingRecList);
            }
        }

        return $notShippingRecList;
    }

    /**
     * 获取优惠券信息
     *
     * @param array $couList
     * @param array $couponsRegionList
     * @param array $consignee
     * @param int $ru_id
     * @param array $cart_goods
     * @param int $user_rank
     * @return array
     */
    private function couInfo($couList = [], $couponsRegionList = [], $consignee = [], $ru_id = 0, $cart_goods = [], $user_rank = 0)
    {
        $couInfo = [];
        if ($couList) {
            $sql = [
                'where' => [
                    [
                        'name' => 'ru_id',
                        'value' => $ru_id
                    ]
                ]
            ];
            $cartGoodsList = BaseRepository::getArraySqlGet($cart_goods, $sql);

            $sql = [
                'where' => [
                    [
                        'name' => 'ru_id',
                        'value' => $ru_id
                    ]
                ]
            ];
            $couInfo = BaseRepository::getArraySqlFirst($couList, $sql);

            $cou_ok_user = $couInfo['cou_ok_user'] ?? ''; //会员ID
            $cou_ok_user = BaseRepository::getExplode($cou_ok_user);

            $cou_goods = $couInfo['cou_goods'] ?? ''; //可使用商品ID
            $cou_goods = BaseRepository::getExplode($cou_goods);
            $cou_goods = BaseRepository::getSort($cou_goods);

            $spec_cat = $couInfo['spec_cat'] ?? ''; //可使用商品分类ID
            $spec_cat = BaseRepository::getExplode($spec_cat);

            $catList = [];
            if ($spec_cat) {
                foreach ($spec_cat as $ck => $cat) {
                    $catList[] = app(CategoryService::class)->getCatListChildren($cat);
                }

                $catList = ArrRepository::getArrCollapse($catList);
                $catList = BaseRepository::getSort($catList);
                $cat_id = BaseRepository::getKeyPluck($cartGoodsList, 'cat_id');
                $couCatList = $catList ? BaseRepository::getArrayIntersect($cat_id, $catList) : [];

                /* 判断当前购物车商品分类ID是否支持可使用 */
                if (empty($couCatList)) {
                    $couInfo = [];
                }
            }

            /* 判断当前会员是否支持会员等级 */
            if (empty($cou_ok_user) || !(!empty($cou_ok_user) && in_array($user_rank, $cou_ok_user))) {
                $couInfo = [];
            }

            $goods_id = BaseRepository::getKeyPluck($cartGoodsList, 'goods_id');
            $cou_goods_id = $cou_goods ? BaseRepository::getArrayIntersect($goods_id, $cou_goods) : [];

            /* 判断当前购物车商品是否支持可使用 */
            if (!empty($cou_goods) && empty($cou_goods_id)) {
                $couInfo = [];
            }

            $cou_id = $couInfo['cou_id'] ?? 0;
            $couponsRegion = $couponsRegionList[$cou_id] ?? [];
            $userCouponsRegion = BaseRepository::getExplode($couponsRegion['region_list']);
            $userCouponsRegion = BaseRepository::getSort($userCouponsRegion);

            /* 判断当前优惠券是否支持当前收货地址地区包邮 */
            if (!empty($couInfo) && !empty($userCouponsRegion)) {

                $province = $consignee['province'] ?? 0;
                $is_area = $province && in_array($province, $userCouponsRegion) ? 1 : 0;

                if ($is_area == 1) {
                    $couInfo = [];
                }
            }
        }

        return $couInfo;
    }

    /**
     * 根据配送方式列表结果计算运费总额
     *
     * @param array $shippingList
     * @return int
     */
    public function cartShippingListTotal($shippingList = [])
    {
        $shipping_fee = 0;

        if ($shippingList) {
            foreach ($shippingList as $key => $shipping) {
                $shipping_fee += $shipping['default_shipping']['old_shipping_fee'] ?? 0;
            }
        }

        return $shipping_fee;
    }

    /**
     * 处理序列化的支付、配送的配置参数
     * 返回一个以name为索引的数组
     *
     * @access  public
     * @param string $cfg
     * @return  array|bool
     */
    public function unserializeConfig($cfg)
    {
        if (is_string($cfg) && ($arr = unserialize($cfg)) !== false) {
            $config = [];

            foreach ($arr as $key => $val) {
                $config[$val['name']] = $val['value'];
            }

            return $config;
        } else {
            return false;
        }
    }

    /**
     * 订单配送方式
     *
     * @return mixed
     */
    public function orderShippingList()
    {
        $res = Shipping::where('enabled', 1);
        $res = BaseRepository::getToArrayGet($res);

        return $res;
    }
}
