<?php

namespace App\Services\Coupon;

use App\Models\Coupons;
use App\Models\CouponsUser;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\TimeRepository;

class CouponsUserService
{
    /**
     * 通过 用户优惠券ID 获取该条优惠券详情 bylu
     *
     * @param int $uc_id 用户优惠券ID
     * @param int $seller_id
     * @param int $user_id
     * @return array
     */
    public function getCoupons($uc_id = 0, $seller_id = -1, $user_id = -1)
    {
        if ($user_id == -1) {
            $user_id = session('user_id', 0);
        }

        $time = TimeRepository::getGmTime();

        $row = CouponsUser::where('is_delete', 0)->where('uc_id', $uc_id)
            ->where('user_id', $user_id)
            ->where('is_delete', 0);

        $row = $row->whereHasIn('getCoupons', function ($query) use ($time, $seller_id) {
            $query = $query->whereRaw("IF(valid_type = 1, cou_start_time <= '$time' and cou_end_time >= '$time', 1)")
                ->where('status', COUPON_STATUS_EFFECTIVE);

            if ($seller_id > -1) {
                $query->where('ru_id', $seller_id);
            }
        });

        $row = $row->with([
            'getCoupons' => function ($query) {
                $query->select('cou_id', 'cou_type', 'cou_money', 'ru_id', 'cou_man');
            }
        ]);

        $row = BaseRepository::getToArrayFirst($row);

        if ($row) {
            $coupons = $row['get_coupons'] ?? [];

            if ($coupons) {

                $row['uc_money'] = $row['cou_money'];
                unset($row['cou_money']);
                unset($row['get_coupons']);

                $row = $coupons ? array_merge($row, $coupons) : $row;
            } else {
                $row = [];
            }
        }

        return $row;
    }

    /**
     * 用户优惠券列表
     *
     * @param array $uc_id
     * @param int $user_id
     * @param array $cart_goods
     * @return array
     */
    public function getCouponsUserSerList($uc_id = [], $user_id = 0, $cart_goods = [])
    {
        $uc_id = BaseRepository::getExplode($uc_id);

        if (empty($uc_id)) {
            return [];
        }

        if (empty($user_id)) {
            $user_id = session('user_id', 0);
        }

        $time = TimeRepository::getGmTime();

        $res = CouponsUser::whereIn('uc_id', $uc_id)
            ->where('user_id', $user_id);

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {

            $cou_id = BaseRepository::getKeyPluck($res, 'cou_id');
            $couponsList = CouponDataHandleService::getCouponsDataList($cou_id);

            if ($cart_goods) {
                $arrCouponsList = [];
                foreach ($couponsList as $k => $v) {
                    $sql = [
                        'where' => [
                            [
                                'name' => 'ru_id',
                                'value' => $v['ru_id']
                            ]
                        ]
                    ];
                    $cartList = BaseRepository::getArraySqlGet($cart_goods, $sql);
                    $goods_amount = BaseRepository::getArraySum($cartList, ['goods_price', 'goods_number']);

                    if ($goods_amount >= $v['cou_man']) {
                        $arrCouponsList[$k] = $v;
                    }
                }
            } else {
                $arrCouponsList = $couponsList;
            }

            if ($arrCouponsList) {
                $couIdList = BaseRepository::getKeyPluck($arrCouponsList, 'cou_id');
                $sql = [
                    'whereIn' => [
                        [
                            'name' => 'cou_id',
                            'value' => $couIdList
                        ]
                    ]
                ];
                $res = BaseRepository::getArraySqlGet($res, $sql, 1);

                if ($res) {
                    foreach ($res as $key => $row) {
                        $row['uc_money'] = $row['cou_money'];
                        $coupons = $arrCouponsList[$row['cou_id']] ?? [];

                        $valid_time = $row['valid_time'] ?? 0;

                        if ($coupons) {

                            $valid_type = $coupons['valid_type'] ?? 1;

                            $is_valid = 1; // 显示可用优惠券
                            if ($valid_type == 2) {
                                $is_valid = $time > $valid_time ? 0 : $is_valid;
                            } else {
                                $cou_start_time = $coupons['cou_start_time'] ?? 0;
                                $cou_end_time = $coupons['cou_end_time'] ?? 0;
                                $is_valid = $time >= $cou_start_time && $time <= $cou_end_time ? $is_valid : 0;
                            }

                            if ($is_valid) {
                                $cou_money = 0;
                                if ($coupons['cou_type'] != VOUCHER_SHIPPING) {
                                    $cou_money = $row['uc_money'] > 0 ? $row['uc_money'] : $coupons['cou_money'];
                                }

                                $row['uc_money'] = $cou_money;
                                $coupons['cou_money'] = $cou_money;
                                unset($row['cou_money']);

                                $arr[$key] = BaseRepository::getArrayMerge($row, $coupons);
                            }
                        }
                    }
                }
            }

            $arr = array_values($arr);
        }

        return $arr;
    }
}
