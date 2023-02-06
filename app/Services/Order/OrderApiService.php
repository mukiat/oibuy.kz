<?php

namespace App\Services\Order;

use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\OrderReturn;
use App\Models\Payment;
use App\Models\UserOrderNum;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Store\StoreService;

/**
 * 会员订单API
 * Class Order
 * @package App\Services
 */
class OrderApiService
{
    protected $storeService;
    protected $dscRepository;

    public function __construct(
        StoreService $storeService,
        DscRepository $dscRepository
    )
    {
        $this->storeService = $storeService;
        $this->dscRepository = $dscRepository;
    }

    /**
     * 根据用户ID查询待同意状态退换货申请数量
     * @param $uid
     * @return mixed
     */
    public function getUserOrdersReturnCount($uid = 0)
    {
        $count = OrderReturn::where('user_id', $uid)->where('refound_status', 0);

        // 检测商品是否存在
        $count = $count->whereHasIn('getGoods', function ($query) {
            $query->select('goods_id')->where('goods_id', '>', 0);
        });

        return $count->count();
    }

    /**
     * 根据用户ID查询订单
     *
     * @param $uid
     * @param int $status
     * @param string $type
     * @param int $page
     * @param int $size
     * @return array|\stdClass
     */
    public function getUserOrders($uid, $status = 0, $type = '', $page = 1, $size = 10, $keywords = '')
    {
        $model = OrderInfo::orderSelectCondition();

        $model = $model->where('user_id', $uid)
            ->where('is_zc_order', 0); //排除众筹订单

        if (!empty($keywords)) {
            $model = $model->where(function ($query) use ($keywords) {
                $query->where('order_sn', 'LIKE', '%' . $keywords . '%')
                    ->orWhere(function ($query) use ($keywords) {
                        $query->whereHasIn('getOrderGoods', function ($query) use ($keywords) {
                            $query->where('goods_name', 'LIKE', '%' . $keywords . '%')->orWhere('goods_sn', 'LIKE', '%' . $keywords . '%');
                        });
                    });
            });
        }

        if ($status == 1) {
            // 待付款
            $order = new \stdClass;
            $order->type = 'toBe_pay';
            $order->idTxt = 'payId';
            $order->keyword = CS_AWAIT_PAY;
            $model = $model->searchKeyword($order);
        } elseif ($status == 2) {
            // 待收货
            $order = new \stdClass;
            $order->type = 'toBe_confirmed';
            $order->idTxt = 'to_confirm_order';
            $order->keyword = CS_TO_CONFIRM;
            $model = $model->searchKeyword($order);
        } elseif ($status == 3) {
            // 已完成
            $order = new \stdClass;
            $order->type = 'toBe_finished';
            $order->idTxt = 'to_finished';
            $order->keyword = CS_FINISHED;
            $model = $model->searchKeyword($order);
        }

        if ($status == 4) {
            //回收站订单
            $model = $model->where('is_delete', 1);
        } else {
            //待收货订单兼容货到付款
            if ($status == 2) {
                $cod = Payment::where('pay_code', 'cod')->where('enabled', 1)->value('pay_id');
                $data['cod'] = $cod;
                $data['user_id'] = $uid;
                if ($cod) {
                    $model = $model->orWhere(function ($query) use ($data, $keywords) {
                        if (!empty($keywords)) {
                            $query = $query->where(function ($query) use ($keywords) {
                                $query->where('order_sn', 'LIKE', '%' . $keywords . '%')
                                    ->orWhere(function ($query) use ($keywords) {
                                        $query->whereHasIn('getOrderGoods', function ($query) use ($keywords) {
                                            $query->where('goods_name', 'LIKE', '%' . $keywords . '%')->orWhere('goods_sn', 'LIKE', '%' . $keywords . '%');
                                        });
                                    });
                            });
                        }

                        if ($data['cod']) {
                            $query->where('pay_id', $data['cod']);
                        }

                        if ($data['user_id']) {
                            $query->where('user_id', $data['user_id']);
                        }

                        $query->where('main_order_id', 0)
                            ->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART])
                            ->whereIn('shipping_status', [SS_UNSHIPPED, SS_SHIPPED, SS_SHIPPED_PART])
                            ->where('pay_status', PS_UNPAYED);
                    });
                }
            }

            $model = $model->where('is_delete', 0);
        }

        //普通订单
        if (empty($type)) {
            $model = $model->where('extension_code', '');
        }
        //订单类型
        if (!empty($type)) {
            switch ($type) {
                case 'bargain':
                    $model = $model->where('extension_code', 'bargain_buy');  //砍价订单
                    break;
                case 'team':
                    $model = $model->where('extension_code', 'team_buy');    //拼团订单
                    break;
            }
        }

        $model = $model->withCount('getOrderReturn as is_return');

        $start = ($page - 1) * $size;

        $order = $model->offset($start)
            ->limit($size)
            ->orderBy('add_time', 'DESC')
            ->get();

        $order = $order ? $order->toArray() : [];

        return $order;
    }

    /**
     * 订单数量
     * @param $uid
     * @param int $status
     * @return mixed
     */
    public function getOrderCount($uid, $status = 0)
    {
        $model = OrderInfo::orderSelectCondition();

        $model = $model->where('user_id', $uid)
            ->where('is_zc_order', 0); //排除众筹订单

        if ($status == 1) {
            // 待付款
            $order = new \stdClass;
            $order->type = 'toBe_pay';
            $order->idTxt = 'payId';
            $order->keyword = CS_AWAIT_PAY;
            $model = $model->searchKeyword($order);
        } elseif ($status == 2) {
            // 待收货
            $order = new \stdClass;
            $order->type = 'toBe_confirmed';
            $order->idTxt = 'to_confirm_order';
            $order->keyword = CS_TO_CONFIRM;
            $model = $model->searchKeyword($order);
        } elseif ($status == 3) {
            // 已收货
            $order = new \stdClass;
            $order->type = 'toBe_finished';
            $order->idTxt = 'to_finished';
            $order->keyword = CS_FINISHED;
            $model = $model->searchKeyword($order);
        }

        if ($status == 4) {
            //回收站订单
            $model = $model->where('is_delete', 1);
        } else {
            //待收货订单兼容货到付款
            if ($status == 2) {
                $cod = Payment::where('pay_code', 'cod')->where('enabled', 1)->value('pay_id');
                $data['cod'] = $cod;
                $data['user_id'] = $uid;
                if ($cod) {
                    $model = $model->orWhere(function ($query) use ($data) {
                        if ($data['cod']) {
                            $query->where('pay_id', $data['cod']);
                        }

                        if ($data['user_id']) {
                            $query->where('user_id', $data['user_id']);
                        }

                        $query->where('main_order_id', 0)
                            ->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART])
                            ->whereIn('shipping_status', [SS_UNSHIPPED, SS_SHIPPED, SS_SHIPPED_PART])
                            ->where('pay_status', PS_UNPAYED);
                    });
                }
            }
            $model = $model->where('is_delete', 0);
        }

        $order_count = $model->count();

        return $order_count;
    }

    /**
     * 获取订单所属的店铺信息
     * @param $orderId
     * @return mixed
     */
    public function getOrderStore($orderId)
    {
        $store = OrderGoods::from('order_goods as og')
            ->select('og.ru_id', 'ss.shop_title', 'ss.shop_name', 'ss.kf_qq', 'ss.kf_ww', 'ss.kf_type', 'ss.shop_can_comment')
            ->join('seller_shopinfo as ss', 'ss.ru_id', 'og.ru_id')
            ->where('og.order_id', $orderId)
            ->first();

        if ($store == null) {
            return [];
        }
        return $store->toArray();
    }

    /**
     * 获取订单所属店铺名称
     * @param $orderId
     * @return mixed
     */
    public function getShopInfo($orderId)
    {
        $ru_id = OrderGoods::where('order_id', $orderId)->value('ru_id');
        if ($ru_id > 0) {
            $shop = $this->storeService->getMerchantsStoreInfo($ru_id, 1);
            $res['shop_id'] = $shop['id'];
            $res['shop_name'] = $shop['check_sellername'] == 0 ? $shop['shoprz_brand_name'] : ($shop['check_sellername'] == 1 ? $shop['rz_shop_name'] : $shop['shop_name']);
        } else {
            $res['shop_id'] = 0;
            $res['shop_name'] = lang('common.self_run');
        }
        return $res;
    }

    /**
     * 取消订单
     *
     * @param array $order
     * @return array|bool
     * @throws \Exception
     */
    public function orderCancel($order = [])
    {
        $uid = $order['user_id'];
        $order_id = $order['order_id'];

        if (empty($order)) {
            return [];
        }

        $data = [
            'order_status' => OS_CANCELED,
        ];
        $up = OrderInfo::where('user_id', $uid)
            ->where('pay_status', '<>', PS_PAYED)
            ->where('order_id', $order_id)
            ->update($data);

        if ($up) {
            load_helper(['common', 'ecmoban', 'order']);

            /* 记录log */
            order_action($order['order_sn'], OS_CANCELED, $order['shipping_status'], PS_UNPAYED, lang('user.buyer_cancel'), lang('common.buyer'));

            /**
             * 更新子订单
             */
            if ($order['main_count'] > 0) {
                OrderInfo::select('order_id', 'order_sn', 'shipping_status')->where('user_id', $order['user_id'])->where('main_order_id', $order_id)->orderBy('order_id')->chunk(1, function ($child) use ($data, $order) {
                    foreach ($child as $k => $v) {
                        $child_up = OrderInfo::where('order_id', $v['order_id'])
                            ->where('user_id', $order['user_id'])
                            ->where('pay_status', '<>', PS_PAYED)
                            ->update($data);

                        if ($child_up) {
                            order_action($v['order_sn'], OS_CANCELED, $v['shipping_status'], PS_UNPAYED, lang('user.buyer_cancel'), lang('common.buyer'));
                        }
                    }
                });
            }

            /* 退货用户余额、积分、红包 */
            if ($order['user_id'] > 0 && $order['surplus'] > 0) {
                $change_desc = sprintf(lang('user.return_surplus_on_cancel'), $order['order_sn']);
                log_account_change($order['user_id'], $order['surplus'], 0, 0, 0, $change_desc);

                if ($order['main_count'] > 0) {
                    OrderInfo::where('main_order_id', $order_id)->update([
                        'surplus' => 0
                    ]);
                }
            }
            if ($order['user_id'] > 0 && $order['integral'] > 0) {
                $change_desc = sprintf(lang('user.return_integral_on_cancel'), $order['order_sn']);
                log_account_change($order['user_id'], 0, 0, 0, $order['integral'], $change_desc);

                if ($order['main_count'] > 0) {
                    OrderInfo::where('main_order_id', $order_id)->update([
                        'integral' => 0,
                        'integral_money' => 0
                    ]);
                }
            }
            // 使用红包退回红包
            if ($order['user_id'] > 0 && $order['bonus_id'] > 0) {
                change_user_bonus($order['bonus_id'], $order['order_id'], false);
            }

            // 使用优惠券退回优惠券
            if ($order['user_id'] > 0 && $order['uc_id'] > 0) {
                unuse_coupons($order['order_id'], $order['uc_id']);
            }

            /* 退回订单消费储值卡金额 */
            return_card_money($order_id);

            /* 如果使用库存，且下订单时减库存，则增加库存 */
            if (config('shop.use_storage') == 1 && config('shop.stock_dec_time') == SDT_PLACE) {
                change_order_goods_storage($order['order_id'], false, 1, 3);
            }

            /* 修改订单 */
            $arr = [
                'bonus_id' => 0,
                'bonus' => 0,
                'uc_id' => 0,
                'coupons' => 0,
                'integral' => 0,
                'integral_money' => 0,
                'surplus' => 0
            ];
            update_order($order['order_id'], $arr);

            $order_nopay = UserOrderNum::where('user_id', $uid)->value('order_nopay');
            $order_nopay = $order_nopay ? intval($order_nopay) : 0;

            /* 更新会员订单信息 */
            if ($order_nopay > 0) {
                $dbRaw = [
                    'order_nopay' => "order_nopay - 1",
                ];
                $dbRaw = BaseRepository::getDbRaw($dbRaw);
                UserOrderNum::where('user_id', $uid)->where('order_nopay', '>', 0)->update($dbRaw);
            }

            return true;
        }
        return false;
    }

}
