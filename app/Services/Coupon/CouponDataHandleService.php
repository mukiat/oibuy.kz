<?php

namespace App\Services\Coupon;

use App\Models\Coupons;
use App\Models\CouponsRegion;
use App\Models\CouponsUser;
use App\Repositories\Common\BaseRepository;

class CouponDataHandleService
{
    /**
     * 优惠券列表
     *
     * @param array $cou_id
     * @param array $data
     * @param int $limit
     * @return array
     */
    public static function getCouponsDataList($cou_id = [], $data = [], $limit = 0)
    {
        $cou_id = BaseRepository::getExplode($cou_id);

        if (empty($cou_id)) {
            return [];
        }

        $cou_id = $cou_id ? array_unique($cou_id) : [];

        $data = $data ? $data : '*';

        $res = Coupons::select($data)->whereIn('cou_id', $cou_id)
            ->where('status', COUPON_STATUS_EFFECTIVE);

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['cou_id']] = $row;
            }
        }

        return $arr;
    }

    /**
     * 用户优惠券列表
     *
     * @param array $uc_id
     * @param array $cou_id
     * @param array $user_id
     * @param array $data
     * @param int $limit
     * @return array
     */
    public static function getCouponsUserDataList($uc_id = [], $cou_id = [], $user_id = [], $data = [], $limit = 0)
    {
        if ((empty($uc_id) && empty($cou_id)) || empty($user_id)) {
            return [];
        }

        $uc_id = BaseRepository::getExplode($uc_id);
        $cou_id = BaseRepository::getExplode($cou_id);
        $user_id = BaseRepository::getExplode($user_id);

        $cou_id = $cou_id ? array_unique($cou_id) : [];

        $data = $data ? $data : '*';

        $res = CouponsUser::select($data)->whereIn('user_id', $user_id);

        if ($uc_id) {
            $res = $res->whereIn('uc_id', $uc_id);
        }

        if ($cou_id) {
            $res = $res->whereIn('cou_id', $cou_id);
        }

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['uc_id']] = $row;
            }
        }

        return $arr;
    }

    /**
     * 优惠券不包邮地区列表
     *
     * @param array $cou_id
     * @param array $data
     * @param int $limit
     * @return array
     */
    public static function getCouponsRegionDataList($cou_id = [], $data = [], $limit = 0)
    {
        if (empty($cou_id)) {
            return [];
        }

        $cou_id = BaseRepository::getExplode($cou_id);

        $cou_id = $cou_id ? array_unique($cou_id) : [];

        $data = $data ? $data : '*';

        $res = CouponsRegion::select($data)
            ->whereIn('cou_id', $cou_id);

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['cou_id']] = $row;
            }
        }

        return $arr;
    }
}