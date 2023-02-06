<?php

namespace App\Services\User;

use App\Exceptions\HttpException;
use App\Libraries\Pager;
use App\Models\DeliveryOrder;
use App\Models\OrderAction;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\OrderReturn;
use App\Models\Payment;
use App\Models\SellerBillOrder;
use App\Models\SellerShopinfo;
use App\Models\StoreOrder;
use App\Models\UserOrderNum;
use App\Repositories\Activity\ActivityRepository;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Order\OrderRepository;
use App\Services\Goods\GoodsActivityService;
use App\Services\Goods\GoodsAttrService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Order\OrderCommonService;
use App\Services\Order\OrderDataHandleService;
use App\Services\Order\OrderService;
use App\Services\Order\OrderStatusService;

/**
 * Class UserOrderService
 * @package App\Services\User
 */
class UserOrderService
{
    protected $dscRepository;
    protected $merchantCommonService;
    protected $goodsAttrService;

    public function __construct(
        DscRepository $dscRepository,
        MerchantCommonService $merchantCommonService,
        GoodsAttrService $goodsAttrService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->merchantCommonService = $merchantCommonService;
        $this->goodsAttrService = $goodsAttrService;
    }

    /**
     * 订单列表获取订单数量
     *
     * @param array $where
     * @return mixed
     */
    public function getOrderWhereCount($where = [])
    {
        $res = OrderInfo::where(function ($query) {
            $query->whereRaw("IF(pay_status < 2, main_order_id = 0 AND main_count = 0, main_count = 0)")
                ->orWhere(function ($query) {
                    $query->where('main_count', '>', 0)
                        ->where('main_pay', 1)
                        ->where('pay_status', '<', 2);
                });
        });

        /* 会员ID */
        if (isset($where['user_id'])) {
            $res = $res->where('user_id', $where['user_id']);
        }

        /* 订单删除状态 0 否， 1 是 */
        if (isset($where['show_type'])) {
            $res = $res->where('is_delete', $where['show_type']);
        }

        /* 是否众筹订单 0 否， 1是 */
        if (isset($where['is_zc_order'])) {
            $res = $res->where('is_zc_order', $where['is_zc_order']);
        }

        /* 订单状态 */
        if (isset($where['order_status'])) {
            if (is_array($where['order_status'])) {
                $res = $res->whereIn('order_status', $where['order_status']);
            } else {
                $res = $res->where('order_status', $where['order_status']);
            }
        }

        /* 订单支付状态 */
        if (isset($where['pay_status'])) {
            if (is_array($where['pay_status'])) {
                $res = $res->whereIn('pay_status', $where['pay_status']);
            } else {
                $res = $res->where('pay_status', $where['pay_status']);
            }
        }

        /* 订单配送状态 */
        if (isset($where['shipping_status']) && isset($where['pay_id'])) {
            $res = $res->where(function ($query) use ($where) {
                if (is_array($where['shipping_status'])) {
                    $query = $query->whereIn('shipping_status', $where['shipping_status']);
                } else {
                    $query = $query->where('shipping_status', $where['shipping_status']);
                }

                if (is_array($where['pay_id'])) {
                    $query->orWhereIn('pay_id', $where['pay_id']);
                } else {
                    $query->orWhere('pay_id', $where['pay_id']);
                }
            });
        } else {
            if (isset($where['shipping_status'])) {
                if (is_array($where['shipping_status'])) {
                    $res = $res->whereIn('shipping_status', $where['shipping_status']);
                } else {
                    $res = $res->where('shipping_status', $where['shipping_status']);
                }
            }
        }

        /* 订单类型：夺宝骑兵、积分商城、团购等 */
        if (isset($where['action'])) {
            $res = $res->where('extension_code', $where['action']);
        }

        /*未收货订单兼容货到付款*/
        if (isset($where['is_cob']) && $where['is_cob'] > 0) {
            $data['cod'] = $where['is_cob'];
            $data['user_id'] = $where['user_id'];
            $res = $res->orWhere(function ($query) use ($data) {
                $query->where('main_order_id', 0)
                    ->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART])
                    ->whereIn('shipping_status', [SS_UNSHIPPED, SS_SHIPPED, SS_SHIPPED_PART])
                    ->where('pay_status', PS_UNPAYED);
                if ($data['cod']) {
                    $query->where('pay_id', $data['cod']);
                }

                if ($data['user_id']) {
                    $query->where('user_id', $data['user_id']);
                }
            });
        }

        $res = $res->count();

        return $res;
    }

    /**
     * 处理主订单配送方式显示
     *
     * @param array $order
     * @return array
     */
    public function mainShipping($order = [])
    {
        if ($order['main_count'] > 0 && $order['shipping_name']) {
            $order['shipping_name'] = '';
        }

        return $order;
    }

    /**
     * 获取会员订单数量
     *
     * @param array $where
     * @param $order
     * @return mixed
     */
    public function getUserOrdersCount($where = [], $order = '')
    {
        $user_id = isset($where['user_id']) ? $where['user_id'] : 0;

        $is_delete = isset($where['show_type']) ? $where['show_type'] : 0;

        $action = '';
        if ($order && is_object($order)) {
            $action = isset($order->action) ? $order->action : '';
            $type = isset($order->type) ? $order->type : '';
        }

        $res = OrderInfo::orderSelectCondition();

        $res = $res->where('is_delete', $is_delete);

        $type = $type ?? '';
        $res = $res->where(function ($query) use ($order, $type) {
            if ($order && is_object($order)) {

                $data = [
                    'order' => $order,
                    'type' => $type
                ];

                $query = $query->searchKeyword($data['order']);
                if ($type == 'toBe_confirmed') {
                    $cod = Payment::where('pay_code', 'cod')->where('enabled', 1)->value('pay_id');

                    $data['cod'] = $cod;

                    if ($cod) {
                        $query->orWhere(function ($query) use ($data) {
                            if ($data['cod']) {
                                $query->where('pay_id', $data['cod']);
                            }

                            $query->where('main_order_id', 0)
                                ->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART])
                                ->whereIn('shipping_status', [SS_UNSHIPPED, SS_SHIPPED, SS_SHIPPED_PART])
                                ->where('pay_status', PS_UNPAYED);
                        });
                    }
                }
            }
        });

        $res = $res->where('user_id', $user_id);

        if (isset($where['is_zc_order'])) {
            $res = $res->where('is_zc_order', $where['is_zc_order']);
        }

        if (isset($where['page']) && $where['size']) {
            if (($order && !is_object($order) && $order == 'auction' || $order == 'auction_order_recycle') || $action == 'auction') {
                //拍卖订单
                if ($order == 'auction') {
                    $res = $res->where('extension_code', $order);
                } else {
                    $ext = isset($order->action) ? $order->action : '';
                    $res = $res->where('extension_code', $ext);
                }
            }
        }

        if (isset($order->type) && $order->type == 'text') { //订单编号、商品编号、商品名称模糊查询
            if ($order->keyword == $GLOBALS['_LANG']['user_keyword']) {
                $order->keyword = '';
            }

            if (isset($order->keyword) && !empty($order->keyword)) {
                $keyword = $this->dscRepository->mysqlLikeQuote($order->keyword);

                $order_id = OrderGoods::query()->select('order_id')->distinct()->where('goods_name', 'like', "%$keyword%")
                    ->orWhere('goods_sn', 'like', "%$keyword%")
                    ->pluck('order_id');
                $order_id = BaseRepository::getToArray($order_id);

                $res = $res->where(function ($query) use ($keyword, $order_id) {
                    $query->where('order_sn', 'like', "%$keyword%")
                        ->orWhere(function ($query) use ($keyword, $order_id) {
                            $query->whereIn('order_id', $order_id);
                        });
                });
            }
        }

        $count = $res->count('order_id');

        return $count;
    }

    /**
     * 获取用户指定范围的订单列表
     *
     * @param array $where
     * @param string $order
     * @return array
     * @throws \Exception
     */
    public function getUserOrdersList($where = [], $order = '')
    {
        $user_id = isset($where['user_id']) ? $where['user_id'] : 0;

        $record_count = isset($where['record_count']) ? $where['record_count'] : 0;
        $is_delete = isset($where['show_type']) ? $where['show_type'] : 0;

        $action = '';
        if ($order && is_object($order)) {
            $action = isset($order->action) ? $order->action : '';
            $type = isset($order->type) ? $order->type : '';
        }

        $pager = $this->orderPager($record_count, $user_id, $is_delete, $where, $order);

        $res = OrderInfo::orderSelectCondition();

        $res = $res->where('is_delete', $is_delete);

        $type = $type ?? '';
        $res = $res->where(function ($query) use ($order, $type) {
            if ($order && is_object($order)) {

                $data = [
                    'order' => $order,
                    'type' => $type
                ];

                $query = $query->searchKeyword($data['order']);

                if ($type == 'toBe_confirmed') {
                    $cod = Payment::where('pay_code', 'cod')->where('enabled', 1)->value('pay_id');

                    $data['cod'] = $cod;

                    if ($cod) {
                        $query->orWhere(function ($query) use ($data) {
                            if ($data['cod']) {
                                $query->where('pay_id', $data['cod']);
                            }

                            $query->where('main_order_id', 0)
                                ->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART])
                                ->whereIn('shipping_status', [SS_UNSHIPPED, SS_SHIPPED, SS_SHIPPED_PART])
                                ->where('pay_status', PS_UNPAYED);
                        });
                    }
                }
            }
        });

        $res = $res->where('user_id', $user_id);

        if (isset($where['is_zc_order'])) {
            $res = $res->where('is_zc_order', $where['is_zc_order']);
        }

        if (isset($where['page']) && $where['size']) {
            if (($order && !is_object($order) && $order == 'auction' || $order == 'auction_order_recycle') || $action == 'auction') {
                //拍卖订单
                if ($order == 'auction') {
                    $res = $res->where('extension_code', $order);
                } else {
                    $ext = isset($order->action) ? $order->action : '';
                    $res = $res->where('extension_code', $ext);
                }
            }
        }

        //订单编号、商品编号、商品名称模糊查询
        if (isset($order->type) && $order->type == 'text') {
            if (isset($order->keyword) && !empty($order->keyword)) {
                $keyword = $this->dscRepository->mysqlLikeQuote($order->keyword);

                $order_id = OrderGoods::query()->select('order_id')->distinct()->where('goods_name', 'like', "%$keyword%")
                    ->orWhere('goods_sn', 'like', "%$keyword%")
                    ->pluck('order_id');
                $order_id = BaseRepository::getToArray($order_id);

                $res = $res->where(function ($query) use ($keyword, $order_id) {
                    $query->where('order_sn', 'like', "%$keyword%")
                        ->orWhere(function ($query) use ($keyword, $order_id) {
                            $query->whereIn('order_id', $order_id);
                        });
                });
            }
        }

        $res = $res->with([
            'getPayment',
            'getBaitiaoLog'
        ]);

        $res = $res->withCount([
            'getStoreOrder as is_store_order'
        ]);

        $res = $res->orderBy('add_time', 'desc');

        if (isset($where['page']) && $where['size']) {
            $start = ($where['page'] - 1) * $where['size'];

            if ($start > 0) {
                $res = $res->skip($start);
            }
        }

        if (isset($where['size']) && $where['size'] > 0) {
            $res = $res->take($where['size']);
        }

        $res = BaseRepository::getToArrayGet($res);

        /* 取得订单列表 */
        $arr = [];

        //发货日期起可退换货时间
        $sign_time = config('shop.sign') ?? '';

        $time = TimeRepository::getGmTime();

        if ($res) {

            $order_id = BaseRepository::getKeyPluck($res, 'order_id');
            $returnList = OrderDataHandleService::getOrderReturnDataList($order_id, ['ret_id', 'rec_id', 'order_id'], 'order_id');

            $orderGoodsList = OrderDataHandleService::orderGoodsDataList($order_id, '*', 1);

            $actIdList = BaseRepository::getKeyPluck($orderGoodsList, 'goods_id');
            $actGoodsList = GoodsActivityService::goodsActivityDataList($actIdList, GAT_PACKAGE, ['act_id', 'activity_thumb']);

            $productOrderGoodsList = ArrRepository::getArrCollapse($orderGoodsList);
            $orderGoodsAttrIdList = BaseRepository::getKeyPluck($productOrderGoodsList, 'goods_attr_id');
            $orderGoodsAttrIdList = BaseRepository::getArrayUnique($orderGoodsAttrIdList);
            $orderGoodsAttrIdList = ArrRepository::getArrayUnset($orderGoodsAttrIdList);

            $productsGoodsAttrList = [];
            if ($orderGoodsAttrIdList) {
                $orderGoodsAttrIdList = BaseRepository::getImplode($orderGoodsAttrIdList);
                $productsGoodsAttrList = GoodsDataHandleService::getGoodsAttrDataList($orderGoodsAttrIdList, ['goods_attr_id', 'attr_img_flie', 'attr_gallery_flie']);
            }

            if (file_exists(MOBILE_TEAM)) {
                $teamIdList = BaseRepository::getColumn($res, 'team_id', 'order_id');
                $orderTeamList = OrderDataHandleService::getOrderTeamList($teamIdList);
            }

            $ru_id = BaseRepository::getKeyPluck($res, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($res as $key => $row) {

                $shop_information = $merchantList[$row['ru_id']] ?? [];

                if ($row['pay_status'] == PS_PAYED) {
                    $row['total_fee'] = $row['money_paid'] + $row['surplus'];
                    $row['is_pay'] = 1;
                } else {
                    $amount = $row['goods_amount'] + $row['insure_fee'] + $row['pay_fee'] + $row['pack_fee'] + $row['card_fee'] + $row['tax'] - $row['discount'] - $row['vc_dis_money'];

                    if ($amount > $row['bonus']) {
                        $amount -= $row['bonus'];
                    } else {
                        $amount = 0;
                    }

                    if ($amount > $row['coupons']) {
                        $amount -= $row['coupons'];
                    } else {
                        $amount = 0;
                    }

                    if ($amount > $row['integral_money']) {
                        $amount -= $row['integral_money'];
                    } else {
                        $amount = 0;
                    }

                    $row['total_fee'] = $amount + $row['shipping_fee'];

                    //扣除预售定金
                    if ($row['pay_status'] == PS_PAYED_PART) {
                        $row['total_fee'] = $row['total_fee'] - $row['money_paid'] - $row['surplus'];
                    }

                    $row['is_pay'] = 0;
                }

                $row['original_handler'] = '';

                $is_stages = $row['get_baitiao_log']['is_stages'] ?? 0;
                if ($is_stages) {
                    $row['is_stages'] = $is_stages;
                } else {
                    $row['is_stages'] = 0;
                }

                $goodsList = $orderGoodsList[$row['order_id']] ?? [];

                if ($goodsList) {

                    $goodsIdList = BaseRepository::getKeyPluck($goodsList, 'goods_id');
                    $goodsInfo = GoodsDataHandleService::GoodsDataList($goodsIdList, ['goods_id', 'goods_thumb', 'goods_cause']);

                    foreach ($goodsList as $goodsKey => $goodsRow) {
                        $goodsList[$goodsKey]['order_id'] = $row['order_id'];
                        $goodsList[$goodsKey]['order_sn'] = $row['order_sn'];
                        $goodsList[$goodsKey]['oi_extension_code'] = $row['extension_code'];
                        $goodsList[$goodsKey]['extension_id'] = $row['extension_id'];

                        $goodsRow['goods_thumb'] = $goodsInfo[$goodsRow['goods_id']]['goods_thumb'] ?? '';
                        $goodsList[$goodsKey]['goods_cause'] = $goodsInfo[$goodsRow['goods_id']]['goods_cause'] ?? '';

                        $goodsList[$goodsKey]['country_icon'] = $shop_information['country_icon'] ?? '';

                        //超值礼包图片
                        if ($goodsRow['og_extension_code'] == 'package_buy') {
                            $goodsRow['goods_name'] = $goodsRow['extension_name'];

                            $activity = $actGoodsList[$goodsRow['goods_id']] ?? [];

                            if ($activity) {
                                $goodsRow['goods_thumb'] = $activity['activity_thumb'];
                            }
                        }

                        $goodsList[$goodsKey]['goods_name'] = $goodsRow['goods_name'];
                        $goodsList[$goodsKey]['goods_number'] = $goodsRow['goods_number'];
                        $goodsList[$goodsKey]['og_extension_code'] = $goodsRow['og_extension_code'];
                        $goodsList[$goodsKey]['goods_price'] = $this->dscRepository->getPriceFormat($goodsRow['goods_price'], false);
                        $goodsList[$goodsKey]['goods_thumb'] = $this->dscRepository->getImagePath($goodsRow['goods_thumb']);
                        $goodsList[$goodsKey]['goods_attr'] = $goodsRow['goods_attr'];

                        $goods_attr_id = $goodsRow['goods_attr_id'] ?? '';
                        $goods_attr_id = BaseRepository::getExplode($goods_attr_id);
                        $goodsList[$goodsKey]['goods_thumb'] = $this->goodsAttrService->cartGoodsAttrImage($goods_attr_id, $productsGoodsAttrList, $goodsList[$goodsKey]['goods_thumb']);

                        if ($goodsRow['og_extension_code'] == 'presale') {
                            $goodsList[$goodsKey]['url'] = $this->dscRepository->buildUri('presale', ['act' => 'view', 'presaleid' => $goodsRow['extension_id']], $goodsRow['goods_name']);
                        } elseif ($goodsRow['oi_extension_code'] == 'group_buy') {
                            $goodsList[$goodsKey]['url'] = $this->dscRepository->buildUri('group_buy', ['gbid' => $goodsRow['extension_id']]);
                        } elseif ($goodsRow['oi_extension_code'] == 'snatch') {
                            $goodsList[$goodsKey]['url'] = $this->dscRepository->buildUri('snatch', ['sid' => $goodsRow['extension_id']]);
                        } elseif ($goodsRow['oi_extension_code'] == 'seckill') {
                            $goodsList[$goodsKey]['url'] = $this->dscRepository->buildUri('seckill', ['act' => "view", 'secid' => $goodsRow['extension_id']]);
                        } elseif ($goodsRow['oi_extension_code'] == 'auction') {
                            $goodsList[$goodsKey]['url'] = $this->dscRepository->buildUri('auction', ['auid' => $goodsRow['extension_id']]);
                        } elseif ($goodsRow['oi_extension_code'] == 'exchange_goods') {
                            $goodsList[$goodsKey]['url'] = $this->dscRepository->buildUri('exchange_goods', ['gid' => $goodsRow['extension_id']]);
                        } else {
                            $goodsList[$goodsKey]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $goodsRow['goods_id']], $goodsRow['goods_name']);
                        }

                        $goodsList[$goodsKey]['trade_id'] = app(OrderCommonService::class)->getFindSnapshot($goodsRow['order_sn'], $goodsRow['goods_id']);
                        $goodsList[$goodsKey]['main_count'] = $goodsRow['main_count'] ?? 0;
                        $goodsList[$goodsKey]['is_comment'] = $goodsRow['is_comment'] ?? 0;
                        $goodsList[$goodsKey]['is_received'] = $goodsRow['is_received'] ?? 0;
                    }
                }

                $row['order_goods'] = $goodsList;
                $row['order_goods_count'] = BaseRepository::getArrayCount($goodsList);

                $row['goods_id'] = $row['order_goods'][0]['goods_id'] ?? 0;
                $extension_code = $row['order_goods'][0]['extension_code'] ?? '';
                $row['extension_code'] = empty($row['extension_code']) ? $extension_code : $row['extension_code'];

                $order_goods_list = [];
                $goods_return_support = []; // 订单商品是否支持退换货
                $goods_comment_support = []; // 订单商品是否支持评价
                if (!empty($row['order_goods'])) {
                    foreach ($row['order_goods'] as $val) {

                        // 订单商品退换货标识
                        $order_goods_cause = app(GoodsCommonService::class)->getGoodsCause($val['goods_cause'], $row, $val);
                        if (!empty($order_goods_cause)) {
                            $goods_return_support[$val['goods_id']] = 1;
                        }

                        // 未评价 is_comment 0
                        if ($val['main_count'] == 0 && $val['is_received'] == 0 && $val['is_comment'] == 0) {
                            $goods_comment_support[$val['goods_id']] = 1;
                        }

                        $order_goods_list[] = $val;
                    }
                }

                $row['order_goods'] = $order_goods_list;

                //处理支付超时订单
                $pay_effective_time = config('shop.pay_effective_time') > 0 ? intval(config('shop.pay_effective_time')) : 0; //订单时效
                //订单时效大于零及开始时效性  且订单未付款未发货  支付方式为线上支付

                $pay_code = $row['get_payment']['pay_code'] ?? '';

                if ($pay_effective_time > 0 && $row['pay_status'] == PS_UNPAYED && in_array($row['order_status'], [OS_UNCONFIRMED, OS_CONFIRMED]) && in_array($row['shipping_status'], [SS_UNSHIPPED, SS_PREPARING]) && !in_array($pay_code, ['cod', 'bank'])) {
                    if ($row['order_status'] != OS_INVALID) {
                        //计算时效性时间戳
                        $pay_effective_time = $pay_effective_time * 60;

                        //如果订单超出时间设为无效
                        if (($time - $row['add_time']) > $pay_effective_time) {
                            $store_order_id = StoreOrder::where('order_id', $row['order_id'])->value('store_id');

                            $store_id = ($store_order_id > 0) ? $store_order_id : 0;

                            /* 标记订单为“无效” */
                            update_order($row['order_id'], ['order_status' => OS_INVALID]);

                            $row['order_status'] = OS_INVALID;

                            /* 记录log */
                            order_action($row['order_sn'], OS_INVALID, SS_UNSHIPPED, PS_UNPAYED, $GLOBALS['_LANG']['pay_effective_Invalid'], lang('common.buyer'));

                            /* 如果使用库存，且下订单时减库存，则增加库存 */
                            if (config('shop.use_storage') == 1 && config('shop.stock_dec_time') == SDT_PLACE) {
                                change_order_goods_storage($row['order_id'], false, SDT_PLACE, 2, 0, $store_id);
                            }

                            /* 退还用户余额、积分、红包 */
                            return_user_surplus_integral_bonus($row);

                            /* 更新会员订单数量 */
                            if (isset($row['user_id']) && !empty($row['user_id'])) {
                                $order_nopay = UserOrderNum::where('user_id', $row['user_id'])->value('order_nopay');
                                $order_nopay = $order_nopay ? intval($order_nopay) : 0;

                                if ($order_nopay > 0) {
                                    $dbRaw = [
                                        'order_nopay' => "order_nopay - 1",
                                    ];
                                    $dbRaw = BaseRepository::getDbRaw($dbRaw);
                                    UserOrderNum::where('user_id', $row['user_id'])->update($dbRaw);
                                }
                            }

                            sleep(1);
                        }
                    }
                }

                $shop_can_comment = 0;
                if (config('shop.shop_can_comment') == 1 && $row['is_delete'] == 0) {
                    $shop_can_comment = $shop_information && $shop_information['shop_can_comment'] == 1 ? 1 : 0;
                }

                $chat = $this->dscRepository->chatQq($shop_information);

                // 延迟收货
                $row['delay_day_time'] = '';
                $row['allow_order_delay'] = 0;
                $row['allow_order_delay_handler'] = '';

                $auto_delivery_time = 0;
                if ($row['shipping_status'] == SS_SHIPPED) {
                    $auto_delivery_time = $row['shipping_time'] + $row['auto_delivery_time'] * 86400; // 延迟收货截止天数
                    $order_delay_day = config('shop.order_delay_day') > 0 ? intval(config('shop.order_delay_day')) : 3;

                    if (($auto_delivery_time - $time) / 86400 < $order_delay_day) {
                        $row['allow_order_delay'] = 1;
                    }

                    $row['auto_delivery_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $auto_delivery_time);
                }

                $row['order_over'] = 0;
                if (config('shop.open_delivery_time') == 1 && $row['order_status'] == OS_SPLITED && $row['shipping_status'] == SS_SHIPPED && $row['pay_status'] == PS_PAYED) { //发货状态
                    if ($time >= $auto_delivery_time) { //自动确认发货操作
                        $row['order_over'] = 1;
                    }
                }

                if (in_array($row['order_status'], [OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART, OS_RETURNED_PART, OS_ONLY_REFOUND])) {
                    // 是否显示确认收货 handler_receive 0 不显示 1 显示
                    $row['handler_receive'] = OrderStatusService::can_receive($row);
                    if ($row['handler_receive'] == 1) {
                        //延迟收货
                        $row['allow_order_delay_handler'] = lang('user.allow_order_delay');

                        $row['remind'] = lang('user.confirm_received');
                        $row['original_handler'] = lang('user.received');
                        $row['handler_act'] = 'affirm_received';
                        $row['handler'] = "<a href=\"user_order.php?act=affirm_received&order_id=" . $row['order_id'] . "\" onclick=\"if (!confirm('" . lang('user.confirm_received') . "')) return false;\">" . lang('user.received') . "</a>"; //确认收货

                    } elseif ($row['shipping_status'] == SS_RECEIVED) {
                        $row['original_handler'] = lang('user.ss_received');
                        $row['handler'] = '<span style="color:red">' . lang('user.ss_received') . '</span>';
                    } else {
                        if ($row['pay_status'] == PS_UNPAYED || $row['pay_status'] == PS_PAYED_PART) {
                            if ($order == 'auction') {
                                $row['handler_act'] = 'auction_order_detail';
                            } else {
                                $row['handler_act'] = 'order_detail';
                            }

                            $row['handler'] = "<a href=\"user_order.php?act=order_detail&order_id=" . $row['order_id'] . '">' . lang('user.pay_money') . '</a>';
                        } else {
                            $row['original_handler'] = lang('user.view_order');
                            if ($order == 'auction') {
                                $row['handler_act'] = 'auction_order_detail';
                            } else {
                                $row['handler_act'] = '';
                            }
                            $row['handler'] = "<a href=\"user_order.php?act=order_detail&order_id=" . $row['order_id'] . '">' . lang('user.view_order') . '</a>';
                        }
                    }
                } else {
                    $row['handler_act'] = '';
                    $row['original_handler'] = trans('order.os.' . $row['order_status']);
                    $row['handler'] = '<span style="color:red">' . lang('order.os.' . $row['order_status']) . '</span>';
                }

                $row['user_order'] = $row['order_status'];
                $row['user_shipping'] = $row['shipping_status'];
                $row['user_pay'] = $row['pay_status'];

                // 是否显示取消订单  handler_cancel 0 不显示 1 显示
                $row['handler_cancel'] = OrderStatusService::can_cancel($row);

                // 是否显示删除订单 order_del： 0 可删除 1 不可删除
                $row['order_del'] = OrderStatusService::can_delete($row);

                /* 退换货显示处理 start */
                $row['return_url'] = '';
                $row['handler_return'] = OrderStatusService::can_return($row);

                //判断发货日期起可退换货时间
                if ($sign_time > 0) {

                    $order_status = [OS_CANCELED, OS_INVALID, OS_RETURNED, OS_ONLY_REFOUND]; // 订单状态 已取消、已失效、已退款
                    if (in_array($row['user_order'], $order_status) || in_array($row['user_pay'], [PS_REFOUND])) {
                        $row['handler_return'] = 0;
                    } else {

                        $log_time = 0;
                        if ($row['user_pay'] == PS_UNPAYED && $row['user_shipping'] == SS_RECEIVED) {
                            // 未付款、已确认收货 [银行转账 货到付款]
                            $log_time = $row['confirm_take_time'];
                        } elseif ($row['user_pay'] == PS_PAYED) {
                            // 已付款
                            if (in_array($row['user_shipping'], [SS_SHIPPED, SS_SHIPPED_PART, OS_SHIPPED_PART])) {
                                // 已发货[含部分发货]
                                $log_time = $row['shipping_time'];
                            } elseif (in_array($row['user_shipping'], [SS_RECEIVED, SS_PART_RECEIVED])) {
                                // 已收货[含部分收货]
                                $log_time = $row['confirm_take_time'];
                            }
                        }

                        $row['handler_return'] = 0;
                        if($row['user_shipping'] == SS_UNSHIPPED){
                            // 未发货 可申请售后
                            $row['handler_return'] = 1;
                        }else{
                            $return_time = $log_time + $sign_time * 3600 * 24;
                            if (!empty($log_time) && $time < $return_time) {
                                // 可申请售后
                                $row['handler_return'] = 1;
                            }
                        }
                    }
                }

                // 订单商品 其中一个支持 即显示申请售后
                $goods_handler_return = 0;
                if (!empty($goods_return_support) && !empty($order_goods_list)) {
                    if (count($order_goods_list) >= count($goods_return_support)) {
                        $goods_handler_return = 1;
                    }
                }

                // 订单商品是否已申请退款(部分退款1，全退款0)
                if ($goods_handler_return == 1) {
                    $order_goods_count = BaseRepository::getArrayCount($order_goods_list);

                    $return_goods = $returnList[$row['order_id']] ?? [];
                    $return_goods = BaseRepository::getArrayCount($return_goods);

                    if ($order_goods_count == $return_goods) {
                        $row['handler_return'] = 0;
                    }
                }

                // 订单商品支持退换货显示申请售后, 回收站订单 不显示申请售后
                if ($row['handler_return'] == 1 && $goods_handler_return == 1 && $row['is_delete'] == 0) {
                    $row['handler_return'] = 1;
                } else {
                    $row['handler_return'] = 0;
                }

                if ($row['handler_return'] == 1) {
                    // 退换货按钮
                    $row['return_url'] = "user_order.php?act=goods_order&order_id=" . $row['order_id'];
                }
                /* 退换货显示处理 end */

                // 订单是否可以评价  handler_comment 0 不可评价 1 可评价
                $row['handler_comment'] = OrderStatusService::can_comment($row, $shop_can_comment);
                // 订单商品全部已评价 其中一个未评价 可显示评价按钮
                if ($row['handler_comment'] == 1 && !empty($goods_comment_support)) {
                    $row['handler_comment'] = 1;
                } else {
                    $row['handler_comment'] = 0;
                }

                // 格式化 订单综合状态
                if (($row['order_status'] == OS_CONFIRMED || $row['order_status'] == OS_SPLITED) && $row['pay_status'] == PS_PAYED && $row['shipping_status'] == SS_RECEIVED) {
                    $row['order_status'] = trans('user.ss_received'); // 已完成
                } elseif ($row['order_status'] == OS_RETURNED) {
                    $row['order_status'] = trans('order.os.' . $row['order_status']) . '，' . trans('order.ps.' . $row['pay_status']);
                } elseif ($row['order_status'] == OS_ONLY_REFOUND) {
                    $row['order_status'] = trans('order.os.' . $row['order_status']) . '，' . trans('order.ps.' . $row['pay_status']);
                } else {
                    $row['shipping_status'] = ($row['shipping_status'] == SS_SHIPPED_ING) ? SS_PREPARING : $row['shipping_status'];

                    $row['order_status'] = trans('order.os.' . $row['order_status']) . '，' . trans('order.ps.' . $row['pay_status']) . '，' . trans('order.ss.' . $row['shipping_status']);
                }

                $order_child = [];
                if ($row['main_count'] > 0) {
                    $order_child = OrderInfo::select('order_id')->where('main_order_id', $row['order_id']);
                    $order_child = BaseRepository::getToArrayGet($order_child);
                }

                $delivery = DeliveryOrder::where('order_id', $row['order_id'])->first();
                $delivery = $delivery ? $delivery->toArray() : [];

                if (isset($delivery['update_time'])) {
                    $delivery['delivery_time'] = $delivery ? TimeRepository::getLocalDate(config('shop.time_format'), $delivery['update_time']) : '';
                }

                $province = get_order_region_name($row['province']);
                $city = get_order_region_name($row['city']);
                $district = get_order_region_name($row['district']);

                $province_name = $province ? $province['region_name'] : '';
                $city_name = $city ? $city['region_name'] : '';
                $district_name = $district ? $district['region_name'] : '';

                $address_detail = $province_name . "&nbsp;" . $city_name . "市" . "&nbsp;" . $district_name;

                //通过ru_id获取到店铺信息
                $row['shop_name'] = $shop_information['shop_name'] ?? '';

                $build_uri = [
                    'urid' => $row['main_count'] > 0 ? 0 : $row['ru_id'],
                    'append' => $row['shop_name']
                ];
                $domain_url = $this->merchantCommonService->getSellerDomainUrl($row['ru_id'], $build_uri);
                $row['shop_url'] = $domain_url['domain_name'];

                /*  @author-bylu 判断当前商家是否允许"在线客服" start */
                if (config('shop.customer_service') == 0) {
                    $seller_id = 0;
                } else {
                    $seller_id = $row['ru_id'];
                }

                //判断当前商家是平台,还是入驻商家 bylu
                if ($seller_id == 0) {
                    //判断平台是否开启了IM在线客服
                    $kf_im_switch = $shop_information['kf_im_switch'] ?? 0;
                    $row['is_dsc'] = $kf_im_switch ? true : false;
                } else {
                    $row['is_dsc'] = false;
                }
                /*  @author-bylu  end */

                if (!empty($row['invoice_no'])) {
                    $invoice_no_arr = explode(',', $row['invoice_no']);
                    $row['invoice_no'] = reset($invoice_no_arr);
                }

                //超值礼包是否存在
                $row['is_package'] = 0;
                if ($row['extension_code'] == 'package_buy') {
                    $activity = get_goods_activity_info($row['goods_id'], ['act_id']);
                    if ($activity) {
                        $row['is_package'] = $activity['act_id'];
                    }
                }

                //验证拼团订单是否失败
                $is_team = 0;
                if (file_exists(MOBILE_TEAM) && $row['team_id'] > 0) {
                    $is_team = $orderTeamList[$row['team_id']]['failure'] ?? 0;
                }

                /* 活动语包 */
                $activity_lang = ActivityRepository::activityLang($row);

                $arr[] = [
                    'order_id' => $row['order_id'],
                    'order_sn' => $row['order_sn'],
                    'is_team' => $is_team,
                    'activity_lang' => $activity_lang,
                    'order_time' => TimeRepository::getLocalDate(config('shop.time_format'), $row['add_time']),
                    'is_im' => isset($shop_information['is_im']) ? $shop_information['is_im'] : '', //平台是否允许商家使用"在线客服";
                    'is_dsc' => $row['is_dsc'],
                    'order_status' => $row['order_status'],
                    'ru_id' => $row['ru_id'],
                    'consignee' => $row['consignee'],
                    'main_order_id' => $row['main_order_id'],
                    'shop_name' => $row['main_count'] > 0 ? config('shop.shop_name') : $row['shop_name'], //店铺名称
                    'shop_url' => $row['shop_url'], //店铺名称	,
                    'order_goods' => $row['order_goods'],
                    'order_goods_count' => $row['order_goods_count'],
                    'order_child' => $order_child,
                    'no_picture' => config('shop.no_picture'),
                    'invoice_no' => $row['invoice_no'],
                    'shipping_name' => $row['shipping_name'],
                    'pay_name' => $row['pay_name'],
                    'email' => $row['email'],
                    'address' => $row['address'],
                    'address_detail' => $address_detail,
                    'tel' => $row['tel'],
                    'delivery_time' => $delivery['delivery_time'],
                    'order_count' => $row['main_count'],
                    'kf_type' => $chat['kf_type'],
                    'kf_ww' => $chat['kf_ww'],
                    'kf_qq' => $chat['kf_qq'],
                    'total_fee' => $this->dscRepository->getPriceFormat($row['total_fee'], false),
                    'handler' => $row['handler'],
                    'original_handler' => $row['original_handler'] ?? '',
                    'handler_act' => isset($row['handler_act']) ? $row['handler_act'] : '',
                    'order_del' => $row['order_del'] ?? 0, // 是否可删除订单
                    'handler_cancel' => $row['handler_cancel'] ?? 0, // 是否可取消订单
                    'handler_return' => $row['handler_return'] ?? 0, // 是否可申请售后
                    'handler_comment' => $row['handler_comment'] ?? 0, // 是否可评价
                    'return_url' => $row['return_url'] ?? '',
                    'remind' => isset($row['remind']) && $row['remind'] ? $row['remind'] : '',
                    //@模板堂-bylu 是否为白条分期订单
                    'is_stages' => $row['is_stages'] ?? 0,
                    'order_over' => $row['order_over'],
                    'delay_day_time' => $row['delay_day_time'],
                    'allow_order_delay' => $row['allow_order_delay'],
                    'auto_delivery_time' => $row['auto_delivery_time'],
                    'allow_order_delay_handler' => $row['allow_order_delay_handler'],
                    'is_package' => $row['is_package'] ?? 0,
                    'is_pay' => $row['is_pay'],
                    'is_store_order' => $row['is_store_order'],
                    'order_confirm' => $row['order_status'] === lang('user.is_confirmed') ? 1 : 0,
                    'is_delete' => $row['is_delete'],
                    'service_url' => DscRepository::getServiceUrl($row['ru_id']), // 店铺客服链接
                ];
            }
        }

        return ['order_list' => $arr, 'pager' => $pager, 'record_count' => $record_count];
    }

    /**
     * @param int $record_count
     * @param int $user_id
     * @param int $is_delete
     * @param array $where
     * @param string $order
     * @return array|string
     */
    public function orderPager($record_count = 0, $user_id = 0, $is_delete = 0, $where = [], $order = '')
    {
        $action = '';
        if ($order && is_object($order)) {
            $idTxt = isset($order->idTxt) ? $order->idTxt : '';
            $keyword = isset($order->keyword) ? $order->keyword : '';
            $action = isset($order->action) ? $order->action : '';
            $type = isset($order->type) ? $order->type : '';
            $status_keyword = isset($order->status_keyword) ? $order->status_keyword : '';
            $date_keyword = isset($order->date_keyword) ? $order->date_keyword : '';

            $id = '"';
            $id .= $user_id . "=";
            $id .= "idTxt@" . $idTxt . "|";
            $id .= "keyword@" . $keyword . "|";
            $id .= "action@" . $action . "|";
            $id .= "type@" . $type . "|";

            if ($status_keyword) {
                $id .= "status_keyword@" . $status_keyword . "|";
            }

            if ($date_keyword) {
                $id .= "date_keyword@" . $date_keyword;
            }

            $substr = substr($id, -1);
            if ($substr == "|") {
                $id = substr($id, 0, -1);
            }

            $id .= '"';
        } else {
            $id = $user_id;
        }

        $config = ['header' => $GLOBALS['_LANG']['pager_2'], "prev" => "<i><<</i>" . $GLOBALS['_LANG']['page_prev'], "next" => "" . $GLOBALS['_LANG']['page_next'] . "<i>>></i>", "first" => $GLOBALS['_LANG']['page_first'], "last" => $GLOBALS['_LANG']['page_last']];

        $pagerParams = [];
        if (isset($where['page']) && $where['size']) {
            $pagerParams = [
                'total' => $record_count,
                'listRows' => $where['size'],
                'type' => $is_delete,
                'act' => $is_delete,
                'id' => $id,
                'page' => $where['page'],
                'pageType' => 1,
                'config_zn' => $config
            ];

            if (($order && !is_object($order) && $order == 'auction' || $order == 'auction_order_recycle') || $action == 'auction') {
                $pagerParams['act'] = $order;

                //拍卖订单
                $pagerParams['funName'] = 'user_auction_order_gotoPage';
            } else {
                //所有订单
                $pagerParams['funName'] = 'user_order_gotoPage';
            }
        }

        $pager = [];
        if ($pagerParams) {
            $user_order = new Pager($pagerParams);
            $pager = $user_order->fpage([0, 4, 5, 6, 9]);
        }

        return $pager;
    }

    /**
     * 获取用户指定范围的订单列表
     *
     * @param int $user_id 会员ID
     * @param int $is_zc_order 是否众筹 0|否  1|是
     * @return array
     * @throws \Exception
     */
    public function getDefaultUserOrders($user_id = 0, $is_zc_order = 0)
    {
        /* 取得订单列表 */
        $res = OrderInfo::orderSelectCondition()->selectRaw("*, (goods_amount + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee + tax - discount) AS total_fee, extension_code as oi_extension_code");

        $res = $res->where('user_id', $user_id);

        $res = $res->where('is_zc_order', $is_zc_order);

        $res = $res->where('is_delete', 0);

        $res = $res->with([
            'getOrderGoodsList' => function ($query) {
                $query = $query->select('order_id', 'goods_id', 'goods_name', 'extension_code as og_extension_code');

                $query->with([
                    'getGoods' => function ($query) {
                        $query->select('goods_id', 'goods_thumb');
                    }
                ]);
            }
        ]);

        $res = $res->orderBy('order_id', 'DESC');

        $res = $res->take(5);

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            load_helper('order');
            foreach ($res as $key => $row) {
                $row['total_fee'] = $row['goods_amount'] + $row['shipping_fee'] + $row['insure_fee'] + $row['pay_fee'] + $row['pack_fee'] + $row['card_fee'] + $row['tax'] - $row['discount'];

                $arr[$key] = $row;

                if ($row['order_status'] == OS_RETURNED) {
                    $ret_id = OrderReturn::where('order_id', $row['order_id'])->value('ret_id');
                    $order = return_order_info($ret_id);
                    if ($order) {
                        $order['return_status'] = isset($order['return_status']) ? $order['return_status'] : ($order['return_status1'] < 0 ? $GLOBALS['_LANG']['only_return_money'] : $GLOBALS['_LANG']['rf'][RF_RECEIVE]);
                        $row['order_status'] = trans('order.os.' . $row['order_status']) . ',' . $order['return_status'] . ',' . $order['refound_status'];
                    } else {
                        $order['return_status'] = $GLOBALS['_LANG']['rf'][RF_RECEIVE];
                        $row['order_status'] = trans('order.os.' . $row['order_status']);
                    }
                } else {
                    $row['order_status'] = trans('order.os.' . $row['order_status']) . ',' . trans('order.ps.' . $row['pay_status']) . ',' . trans('order.ss.' . $row['shipping_status']);
                }

                $arr[$key]['order_id'] = $row['order_id'];
                $arr[$key]['order_sn'] = $row['order_sn'];
                $arr[$key]['oi_extension_code'] = $row['oi_extension_code'];
                $arr[$key]['consignee'] = $row['consignee'];
                $arr[$key]['total_fee'] = $this->dscRepository->getPriceFormat($row['total_fee'], false);
                $arr[$key]['order_status'] = $row['order_status'];
                $arr[$key]['order_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['add_time']);

                if ($row['get_order_goods_list']) {
                    foreach ($row['get_order_goods_list'] as $idx => $order_goods) {
                        $arr[$key]['goods'][$idx]['goods_id'] = $order_goods['goods_id'];
                        $arr[$key]['goods'][$idx]['goods_name'] = $order_goods['goods_name'];
                        $arr[$key]['goods'][$idx]['og_extension_code'] = $order_goods['og_extension_code'];

                        $goods = $order_goods['get_goods'] ?? [];
                        if ($goods) {
                            $arr[$key]['goods'][$idx]['goods_thumb'] = $this->dscRepository->getImagePath($goods['goods_thumb']);
                        }

                        $arr[$key]['goods'][$idx]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $order_goods['goods_id']], $order_goods['goods_name']);
                    }
                }
            }
        }

        return $arr;
    }

    /**
     * 获取用户可以和并的订单数组
     *
     * @param int $user_id
     * @return array
     */
    public function getUserMerge($user_id = 0)
    {
        $list = OrderInfo::query()->select('order_sn')
            ->whereRaw("1 " . app(OrderService::class)->orderQuerySql('unprocessed'))
            ->where('extension_code', '')
            ->whereNotIn('order_status', [OS_INVALID, OS_RETURNED, OS_RETURNED_PART, OS_ONLY_REFOUND])
            ->where('pay_status', '<>', PS_UNPAYED)
            ->where('shipping_status', SS_UNSHIPPED)
            ->where('main_count', 0)
            ->where('user_id', $user_id);

        $list = $list->pluck('order_sn');
        $list = BaseRepository::getToArray($list);

        $merge = [];
        if ($list) {
            foreach ($list as $val) {
                $merge[$val] = $val;
            }
        }

        return $merge;
    }

    /**
     * 获得虚拟商品的卡号密码
     *
     * @param int $rec_id
     * @return array
     */
    public function get_virtual_goods_info($rec_id = 0)
    {
        load_helper('code');

        $virtual_info = OrderGoods::from('order_goods as og')
            ->select('vc.*')
            ->leftjoin('order_info as oi', 'oi.order_id', 'og.order_id')
            ->leftjoin('virtual_card as vc', 'vc.order_sn', 'oi.order_sn')
            ->whereColumn('og.goods_id', 'vc.goods_id')
            ->where('vc.is_saled', 1)
            ->where('og.rec_id', $rec_id)
            ->get();
        $virtual_info = $virtual_info ? $virtual_info->toArray() : [];

        $res = [];
        $virtual = [];
        if ($virtual_info) {
            foreach ($virtual_info as $row) {
                $res['card_sn'] = $row['card_sn'];
                $res['card_password'] = dsc_decrypt($row['card_password']);
                $res['end_date'] = TimeRepository::getLocalDate(config('shop.date_format'), $row['end_date']);
                $virtual[] = $res;
            }
        }

        return $virtual;
    }

    /**
     * 订单自动确认收货
     *
     * @param array $orderInfo
     * @return array
     * @throws \Exception
     */
    public function OrderDeliveryTime($orderInfo = [])
    {
        $noTime = TimeRepository::getGmTime();

        $data = [];
        if (!empty($orderInfo) && config('shop.open_delivery_time') == 1) {
            $orderDelivery = CommonRepository::orderDeliveryCondition($orderInfo['order_status'], $orderInfo['shipping_status'], $orderInfo['pay_status']);

            //发货状态
            if ($orderDelivery) {
                $delivery_time = $orderInfo['shipping_time'] + 24 * 3600 * $orderInfo['auto_delivery_time'];

                if ($noTime >= $delivery_time) { //自动确认发货操作

                    // 订单是否全部发货 全部发货 => 全部收货、部分发货 => 部分收货
                    $order_finish = OrderRepository::getAllDeliveryFinish($orderInfo['order_id']);
                    $shipping_status = ($order_finish == 1) ? SS_RECEIVED : SS_PART_RECEIVED;

                    $confirm_take_time = $noTime;
                    $other = [
                        'order_status' => $orderInfo['order_status'],
                        'shipping_status' => $shipping_status,
                        'pay_status' => $orderInfo['pay_status']
                    ];

                    if ($shipping_status == SS_RECEIVED) {
                        $other['confirm_take_time'] = $confirm_take_time;
                    }

                    $res = OrderInfo::where('order_id', $orderInfo['order_id'])->update($other);

                    $data = [
                        'shipping_status' => $other['shipping_status']
                    ];

                    if ($res) {

                        if ($shipping_status == SS_RECEIVED) {
                            $order_nogoods = UserOrderNum::where('user_id', $orderInfo['user_id'])->value('order_nogoods');
                            $order_nogoods = $order_nogoods ? $order_nogoods : 0;

                            /* 更新会员订单信息 */
                            $dbRaw = [
                                'order_isfinished' => "order_isfinished + 1"
                            ];

                            if ($order_nogoods > 0) {
                                $dbRaw['order_nogoods'] = "order_nogoods - 1";
                            }

                            $dbRaw = BaseRepository::getDbRaw($dbRaw);
                            UserOrderNum::where('user_id', $orderInfo['user_id'])->update($dbRaw);
                        }

                        // 订单收货事件监听
                        $extendParam = [
                            'shipping_status' => $other['shipping_status'],
                            'note' => trans('common.self_motion_goods'), // 自动确认收货
                        ];
                        event(new \App\Events\OrderReceiveEvent($orderInfo, $extendParam));

                        //订单签收推送消息给多商户商家掌柜事件
                        event(new \App\Events\PushMerchantOrderAffirmReceivedEvent($orderInfo['order_sn']));
                    }
                }
            }
        }

        return $data;
    }

    /**
     * 订单确认收货（含部分收货）
     * @param int $user_id
     * @param int $order_id
     * @return bool
     * @throws HttpException
     */
    public function orderReceive($user_id = 0, $order_id = 0)
    {
        if (empty($user_id) || empty($order_id)) {
            throw new HttpException('parameters of illegal.', 1);
        }

        $order = OrderInfo::where('user_id', $user_id)
            ->where('order_id', $order_id)
            ->with([
                'getPayment'
            ]);
        $order = BaseRepository::getToArrayFirst($order);

        if (empty($order)) {
            throw new HttpException(trans('user.order_exist'), 1);
        }

        if ($order['shipping_status'] == SS_UNSHIPPED) {
            throw new HttpException(trans('user.current_os_not_shipping'), 1);
        }

        if ($order['shipping_status'] == SS_RECEIVED) {
            throw new HttpException(trans('common.order_already_received'), 1);
        }

        if ($order['shipping_status'] != SS_SHIPPED && $order['shipping_status'] != SS_SHIPPED_PART) {
            throw new HttpException(trans('user.current_os_not_shipping'), 1);
        }

        /* 修改订单发货状态为“确认收货” */
        $confirm_take_time = TimeRepository::getGmTime();

        // 订单是否全部发货 全部发货 => 全部收货、部分发货 => 部分收货
        $order_finish = OrderRepository::getAllDeliveryFinish($order_id);
        $shipping_status = ($order_finish == 1) ? SS_RECEIVED : SS_PART_RECEIVED;

        $other = [
            'shipping_status' => $shipping_status
        ];

        if ($shipping_status == SS_RECEIVED) {
            $other['confirm_take_time'] = $confirm_take_time;
        }

        $up = OrderInfo::where('order_id', $order_id)->where('user_id', $user_id)->update($other);

        if ($up) {

            if ($shipping_status == SS_RECEIVED) {
                SellerBillOrder::where('order_id', $order_id)
                    ->where('user_id', $user_id)
                    ->where('shipping_status', SS_PART_RECEIVED)
                    ->update($other);
            }

            if ($shipping_status == SS_RECEIVED) {
                $order_nogoods = UserOrderNum::where('user_id', $user_id)->value('order_nogoods');
                $order_nogoods = $order_nogoods ? $order_nogoods : 0;

                /* 更新会员订单信息 */
                $dbRaw = [
                    'order_isfinished' => "order_isfinished + 1"
                ];

                if ($order_nogoods > 0) {
                    $dbRaw['order_nogoods'] = "order_nogoods - 1";
                }

                $dbRaw = BaseRepository::getDbRaw($dbRaw);
                UserOrderNum::where('user_id', $user_id)->update($dbRaw);
            }

            // 订单收货事件监听
            $extendParam = [
                'shipping_status' => $shipping_status,
                'note' => trans('common.self_motion_goods'), // 自动确认收货
            ];
            event(new \App\Events\OrderReceiveEvent($order, $extendParam));

            //订单签收推送消息给多商户商家掌柜事件
            event(new \App\Events\PushMerchantOrderAffirmReceivedEvent($order['order_sn']));

            //更改智能权重里的商品统计数量 权重
            $res = OrderGoods::select('goods_id', 'goods_number')->where('user_id', $user_id)->where('order_id', $order_id)->get();
            $res = $res ? $res->toArray() : [];

            if ($res) {
                foreach ($res as $val) {
                    $user_number = OrderGoods::where('goods_id', $val['goods_id'])->groupBy('user_id')->get();
                    $user_number = $user_number ? $user_number->toArray() : [];
                    $user_number = $user_number ? count($user_number) : 0;

                    $num = ['goods_number' => $val['goods_number'], 'goods_id' => $val['goods_id'], 'user_number' => $user_number];
                    update_manual($val['goods_id'], $num);
                }
            }

            if (config('shop.sms_order_received', 0) == '1') {
                //获取店铺客服电话
                $seller_shop_info = SellerShopinfo::where('ru_id', $order['ru_id'])->select('mobile');
                $seller_shop_info = BaseRepository::getToArrayFirst($seller_shop_info);
                //阿里大鱼短信接口参数
                if (isset($seller_shop_info['mobile']) && $seller_shop_info['mobile']) {
                    $smsParams = [
                        'ordersn' => $order['order_sn'],
                        'consignee' => $order['consignee'],
                        'ordermobile' => $order['mobile']
                    ];
                    app(CommonRepository::class)->smsSend($seller_shop_info['mobile'], $smsParams, 'sms_order_received');
                }
            }

            return true;
        } else {
            throw new HttpException(trans('admin/common.fail'), 1);
        }
    }
}
