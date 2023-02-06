<?php

namespace App\Repositories\Order;

use App\Models\Brand;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\OrderReturn;
use App\Models\Payment;
use App\Models\ReturnCause;
use App\Models\ReturnImages;
use App\Repositories\Common\BaseRepository;

/**
 * 订单退换货
 * Class OrderReturnRepository
 * @package App\Repositories\Order
 */
class OrderReturnRepository
{
    /**
     * 获取退换货图片列表
     *
     * @param array $where
     * @return mixed
     */
    public static function getReturnImagesList($where = [])
    {
        $res = ReturnImages::query();

        if (isset($where['user_id'])) {
            $res = $res->where('user_id', $where['user_id']);
        }

        if (isset($where['rec_id'])) {
            $res = $res->where('rec_id', $where['rec_id']);
        }

        $res = $res->orderBy('id', 'desc');

        return BaseRepository::getToArrayGet($res);
    }

    /**
     * 是否显示原路退款
     *
     * @param int $pay_id
     * @return bool
     */
    public static function showReturnOnline($pay_id = 0)
    {
        if (empty($pay_id)) {
            return false;
        }

        $pay_arr = ['alipay', 'wxpay'];
        $pay_arr_not = ['balance', 'chunsejinrong'];
        $pay_code = Payment::where('pay_id', $pay_id)->where('enabled', 1)->where('is_online', 1)->value('pay_code');
        $pay_code = $pay_code ? $pay_code : '';

        if (in_array($pay_code, $pay_arr) && !in_array($pay_code, $pay_arr_not)) {
            return true;
        }

        return false;
    }

    /**
     * 退换货原因
     * @param int $parent_id
     * @param int $level
     * @return array
     */
    public static function getReturnCause($parent_id = 0, $level = 0)
    {
        $res = ReturnCause::where('parent_id', $parent_id)
            ->where('is_show', 1)
            ->orderBy('sort_order');

        $res = BaseRepository::getToArrayGet($res);

        $three_arr = [];
        if ($res) {
            foreach ($res as $k => $row) {
                $three_arr[$k]['cause_id'] = $row['cause_id'];
                $three_arr[$k]['cause_name'] = $row['cause_name'];
                $three_arr[$k]['parent_id'] = $row['parent_id'];
                $three_arr[$k]['haschild'] = 0;

                $three_arr[$k]['level'] = $level;
                //$three_arr[$k]['select'] = str_repeat('&nbsp;', $three_arr[$k]['level'] * 4);

                if (isset($row['cause_id']) && $level > 0) {
                    $child_tree = self::getReturnCause($row['cause_id'], $level + 1);
                    if ($child_tree) {
                        $three_arr[$k]['child_tree'] = $child_tree;
                        $three_arr[$k]['haschild'] = 1;
                    }
                }
            }
        }

        return $three_arr;
    }

    /**
     * 退换货订单列表
     *
     * @param int $order_id
     * @return array
     */
    public static function orderReturnList($order_id = 0)
    {
        if (empty($order_id)) {
            return [];
        }

        $model = OrderReturn::where('order_id', $order_id);
        $model = BaseRepository::getToArrayGet($model);

        return $model;
    }

    /**
     * 退换货订单商品
     *
     * @param int $rec_id
     * @param int $user_id
     * @return mixed
     */
    public static function getReturnOrderGoods($rec_id = 0, $user_id = 0)
    {
        $model = OrderGoods::where('rec_id', $rec_id);

        if ($user_id > 0) {
            $model = $model->where('user_id', $user_id);
        }

        $model = $model->with([
            'getGoods' => function ($query) {
                $query->select('goods_id', 'goods_name', 'goods_sn', 'brand_id');
            },
            'getOrder' => function ($query) {
                $query = $query->select('order_id', 'order_sn', 'user_id', 'consignee', 'mobile', 'country', 'province', 'city', 'district', 'street', 'shipping_status', 'divide_channel');
                $query->with([
                    'getRegionProvince' => function ($query) {
                        $query->select('region_id', 'region_name');
                    },
                    'getRegionCity' => function ($query) {
                        $query->select('region_id', 'region_name');
                    },
                    'getRegionDistrict' => function ($query) {
                        $query->select('region_id', 'region_name');
                    },
                    'getRegionStreet' => function ($query) {
                        $query->select('region_id', 'region_name');
                    }
                ]);
            }
        ]);

        $res = BaseRepository::getToArrayFirst($model);

        if ($res) {

            /* 取得区域名 */
            $province = $res['get_order']['get_region_province']['region_name'] ?? '';
            $city = $res['get_order']['get_region_city']['region_name'] ?? '';
            $district = $res['get_order']['get_region_district']['region_name'] ?? '';
            $street = $res['get_order']['get_region_street']['region_name'] ?? '';
            $res['region'] = $province . ' ' . $city . ' ' . $district . ' ' . $street;

            $res = collect($res)->merge($res['get_goods'])->except('get_goods')->all();
            $res = collect($res)->merge($res['get_order'])->except('get_order')->all();

            $res['brand_name'] = isset($res['brand_id']) ? Brand::where('brand_id', $res['brand_id'])->value('brand_name') : '';
        }

        return $res;
    }

    /**
     * 计算订单退款金额
     *
     * @param int $order_id
     * @param int $rec_id
     * @param int $return_number
     * @return array
     */
    public static function getOrderReturnFee($order_id = 0, $rec_id = 0, $return_number = 0)
    {
        $orders = OrderInfo::select('money_paid', 'goods_amount', 'surplus', 'shipping_fee')->where('order_id', $order_id);
        $orders = BaseRepository::getToArrayFirst($orders);

        $return_shipping_fee = OrderReturn::selectRaw("SUM(return_shipping_fee) AS return_shipping_fee")
            ->where('order_id', $order_id)
            ->whereIn('return_type', [1, 3])
            ->value('return_shipping_fee');
        $return_shipping_fee = $return_shipping_fee ?? 0;

        $res = OrderGoods::selectRaw("goods_number, goods_price, (goods_number * goods_price) AS goods_amount")->where('rec_id', $rec_id);
        $res = BaseRepository::getToArrayFirst($res);

        if ($res && $return_number > $res['goods_number'] || empty($return_number)) {
            $return_number = $res['goods_number'];
        }

        $return_price = $return_number * $res['goods_price'];
        $return_shipping_fee = $orders['shipping_fee'] - $return_shipping_fee;

        if ($return_price > 0) {
            $return_price = number_format($return_price, 2, '.', '');
        }

        if ($return_shipping_fee > 0) {
            $return_shipping_fee = number_format($return_shipping_fee, 2, '.', '');
        }

        return [
            'return_price' => $return_price,
            'return_shipping_fee' => $return_shipping_fee
        ];
    }

    /**
     * 退换货订单 已退金额、运费、税费
     * @param int $order_id
     * @param int $ret_id
     * @return array
     */
    public static function orderRefoundFee($order_id = 0, $ret_id = 0)
    {
        if (empty($order_id)) {
            return [];
        }

        $price = OrderReturn::selectRaw('SUM(return_shipping_fee) AS return_shipping_fee, SUM(actual_return) AS actual_return, SUM(return_rate_price) AS return_rate_price')
            ->where('order_id', $order_id)
            ->whereIn('refund_type', [1, 3, 6])
            ->where('refound_status', FF_REFOUND); // 已退款

        if ($ret_id > 0) {
            $price = $price->where('ret_id', '<>', $ret_id);
        }

        $fee = $price->select('return_shipping_fee', 'actual_return', 'return_rate_price');
        $fee = BaseRepository::getToArrayFirst($fee);

        return $fee;
    }

    /**
     * 获取退货单最终退款金额
     *
     * @param array $order_info
     * @param array $order_return
     * @param int $refund_type
     * @return float|int|mixed
     */
    public static function getOrderReturnAmount($order_info = [], $order_return = [], $refund_type = 0)
    {
        if (empty($order_info) || empty($order_return)) {
            return 0;
        }

        // 退换货单申请时记录的 应退金额
        $should_return = $order_return['should_return'] ?? 0;

        //已退金额
        $order_id = $order_info['order_id'] ?? 0;
        $ret_id = $order_return['ret_id'] ?? 0;
        $refoundFee = self::orderRefoundFee($order_id, $ret_id);
        $actual_return = $refoundFee['actual_return'] ?? 0;

        if ($actual_return > 0 && $should_return > $actual_return) {
            // 订单实际已支付金额（含使用余额）
            $paid_amount = $order_info['money_paid'] + $order_info['surplus'];

            if ($refund_type == 6 && $order_info['surplus'] > 0) {
                // 原路退回 订单实际已支付金额 须扣除使用余额部分
                $paid_amount -= $order_info['surplus'];
            }
            // 订单实际已支付金额 扣除运费
            if ($paid_amount > 0 && $paid_amount >= $order_info['shipping_fee']) {
                $paid_amount = $paid_amount - $order_info['shipping_fee'];
            }
            $paid_amount = $paid_amount - $actual_return;
            $paid_amount = round($paid_amount, 2);
            // 按金额 从小到大退款
            if ($should_return > $paid_amount) {
                $should_return = $paid_amount;
            }
        }

        // 应退运费
        $should_return += $order_return['return_shipping_fee'] ?? 0;

        if (CROSS_BORDER === true) {
            // 跨境 应退税率
            $should_return += $order_return['return_rate_price'] ?? 0;
        }

        // 应退优惠券
        if (isset($order_return['goods_coupons']) && $order_return['goods_coupons'] > 0) {
            $should_return -= $order_return['goods_coupons'];
        }

        // 应退红包
        if (isset($order_return['goods_bonus']) && $order_return['goods_bonus'] > 0) {
            $should_return -= $order_return['goods_bonus'];
        }

        // 应退折扣
        if (isset($order_return['goods_favourable']) && $order_return['goods_favourable'] > 0) {
            $should_return -= $order_return['goods_favourable'];
        }

        // 应退储值卡折扣
        if (isset($order_return['value_card_discount']) && $order_return['value_card_discount'] > 0) {
            $should_return -= $order_return['value_card_discount'];
        }

        return $should_return;
    }
}