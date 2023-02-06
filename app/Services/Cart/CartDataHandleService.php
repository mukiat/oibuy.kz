<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\CartCombo;
use App\Models\Coupons;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\TimeRepository;

class CartDataHandleService
{
    /**
     * 购物车商品列表
     *
     * @param array $rec_id
     * @param array $data
     * @return array
     */
    public static function CartDataList($rec_id = [], $data = [])
    {
        $rec_id = BaseRepository::getExplode($rec_id);

        if (empty($rec_id)) {
            return $rec_id;
        }

        $rec_id = $rec_id ? array_unique($rec_id) : [];

        $data = $data ? $data : '*';

        $res = Cart::select($data)->whereIn('rec_id', $rec_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['rec_id']] = $row;
            }
        }

        return $arr;
    }

    /**
     * 购物车组合商品列表
     *
     * @param array $goods_id
     * @param array $data
     * @return array
     */
    public static function getCartComboDataList($goods_id = [], $data = [])
    {
        $goods_id = BaseRepository::getExplode($goods_id);

        if (empty($goods_id)) {
            return $goods_id;
        }

        $goods_id = $goods_id ? array_unique($goods_id) : [];

        $data = $data ? $data : '*';

        $res = CartCombo::select($data)->whereIn('goods_id', $goods_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['goods_id']] = $row;
            }
        }

        return $arr;
    }

    /**
     * 重新组合购物车活动商品
     *
     * @param $merchant_goods
     * @return array
     */
    public static function merchantGoods($merchant_goods)
    {
        $list = [];
        foreach ($merchant_goods as $idx => $val) {

            $goods_list = [];
            if (isset($val['goods_list'])) {
                $goods_list = $val['goods_list'] ?? [];
            } elseif (isset($val['goods'])) {
                $goods_list = $val['goods'] ?? [];
            }

            if ($goods_list) {
                foreach ($goods_list as $k => $v) {
                    $list[] = [
                        'rec_id' => $v['rec_id'],
                        'act_id' => $v['act_id'],
                        'goods_number' => $v['goods_number'],
                        'goods_price' => $v['goods_price'],
                        'is_checked' => $v['is_checked'],
                        'dis_amount' =>  $v['dis_amount']
                    ];
                }
            }
        }

        return $list;
    }

    /**
     * 获取商家优惠券
     *
     * @param array $ru_id
     * @param array $data
     * @return array
     */
    public static function getCartRuCoupunsList($ru_id = [], $data = [])
    {
        $time = TimeRepository::getGmTime();

        $ru_id = BaseRepository::getExplode($ru_id);

        if (empty($ru_id)) {
            return $ru_id;
        }

        $ru_id = $ru_id ? array_unique($ru_id) : [];

        $data = $data ? $data : '*';

        $res = Coupons::select($data)
            ->where('review_status', 3)
            ->whereIn('ru_id', $ru_id)
            ->whereRaw("IF(valid_type > 1, receive_start_time <= '$time' and receive_end_time >= '$time', cou_start_time <= '$time' and cou_end_time >= '$time')")
            ->whereIn('cou_type', [VOUCHER_ALL, VOUCHER_USER])
            ->where('status', COUPON_STATUS_EFFECTIVE);

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['ru_id']][] = $row;
            }
        }

        return $arr;
    }

    /**
     * 优惠活动赠品商品列表
     *
     * @param int $act_id
     * @param int $user_id
     * @return array
     */
    public static function getCartGiftDataList($act_id = 0, $user_id = 0)
    {
        $res = Cart::where('is_gift', $act_id);

        if (!empty($user_id)) {
            $res = $res->where('user_id', $user_id);
        } else {
            $session_id = app(SessionRepository::class)->realCartMacIp();
            $res = $res->where('session_id', $session_id);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['goods_id']] = $row;
            }
        }

        return $arr;
    }
}
