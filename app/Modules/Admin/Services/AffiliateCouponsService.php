<?php

namespace App\Modules\Admin\Services;

use App\Models\Coupons;
use App\Models\CouponsUser;
use App\Models\ShopConfig;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Coupon\CouponService;


class AffiliateCouponsService
{

    /**
     * 查询配置
     * @param string $code
     * @return array
     */
    public static function getShopConfig(string $code = 'affiliate_coupons')
    {
        if (empty($code)) {
            return [];
        }

        $result = ShopConfig::where('code', $code)->select('code', 'value')->first();
        $result = $result ? $result->toArray() : [];

        if (!empty($result)) {
            $result['value'] = $result['value'] ? json_decode($result['value'], true) : [];
        }

        return $result['value'] ?? [];
    }

    /**
     * 保存配置
     * @param string $code
     * @param array $value
     * @return false
     */
    public static function saveShopConfig(string $code = 'affiliate_coupons', array $value = [])
    {
        if (empty($code) || empty($value)) {
            return false;
        }

        $value = json_encode($value);

        return ShopConfig::where('code', $code)->update(['value' => $value]);
    }

    /**
     * 优惠券列表 类型支持全场券、会员券、免邮券
     * @param int $page
     * @param int $size
     * @return array
     */
    public static function selectCouponsList(int $page = 1, int $size = 10)
    {
        $time = TimeRepository::getGmTime();
        $start = ($page - 1) * $size;

        /**
         * 显示可选择的优惠券
         * 1. 有效期未开始，可以显示选择。
         * 2. 有效期已过期，不显示且不可选择。
         * 3. 有效期正常，领取时间正常，可以选择
         */
        $model = Coupons::query()->where('review_status', 3)->whereIn('cou_type', [VOUCHER_ALL, VOUCHER_USER, VOUCHER_SHIPPING])
            ->where('cou_end_time', '>=', $time)
            ->where('receive_start_time', '<=', $time)->where('receive_end_time', '>=', $time)
            ->where('status', COUPON_STATUS_EFFECTIVE);

        $model = $model->select('cou_id', 'cou_name', 'cou_title', 'cou_type');

        $model = $model->orderBy('cou_id', 'desc')->offset($start)->limit($size)->get();

        $list = $model ? $model->toArray() : [];

        foreach ($list as $k => $v) {
            $list[$k]['cou_type_name'] = CouponService::cou_type_name($v['cou_type']);
        }

        return $list;
    }

    /**
     * 推荐注册赠送优惠券
     * @param int $user_id
     * @param int $parent_id
     * @return bool
     */
    public static function userRegisterSendCoupons(int $user_id = 0, int $parent_id = 0)
    {
        $affiliate_coupons_config = config('shop.affiliate_coupons', '');

        if (empty($affiliate_coupons_config) || empty($user_id) || empty($parent_id)) {
            return false;
        }

        $affiliate_coupons_config = json_decode($affiliate_coupons_config, true);

        $give_parent = $affiliate_coupons_config['give_parent'] ?? 0; // 上级是否可获得优惠券 0 否，1 是
        $give_register = $affiliate_coupons_config['give_register'] ?? 0; // 注册人是否赠送优惠券 0 否，1 是
        $give_coupons_id = $affiliate_coupons_config['give_coupons_id'] ?? 0; // 选择可赠送的优惠券id

        if ($give_parent == 1 || $give_register == 1) {
            // 有效期内或提前 可领取
            $coupons = self::getCouponsById($give_coupons_id, ['cou_id', 'cou_type', 'cou_ok_user', 'cou_total', 'cou_end_time', 'cou_money', 'cou_user_num']);

            if (empty($coupons)) {
                return false;
            }

            $time = TimeRepository::getGmTime();

            // 领取有效时间
            $valid_time = $coupons['cou_end_time'];

            if ($give_parent == 1) {
                // 验证 优惠券领取限制条件
                if (self::checkCoupons($parent_id, $coupons) === false) {
                    return false;
                }

                $value = [
                    'user_id' => $parent_id,
                    'cou_id' => $give_coupons_id,
                    'cou_money' => $coupons['cou_money'],
                    'uc_sn' => CommonRepository::couponSn(),
                    'valid_time' => $valid_time,
                    'add_time' => $time
                ];
                CouponsUser::query()->insert($value);
            }

            if ($give_register == 1) {
                // 验证 优惠券领取限制条件
                if (self::checkCoupons($user_id, $coupons) === false) {
                    return false;
                }

                $value = [
                    'user_id' => $user_id,
                    'cou_id' => $give_coupons_id,
                    'cou_money' => $coupons['cou_money'],
                    'uc_sn' => CommonRepository::couponSn(),
                    'valid_time' => $valid_time,
                    'add_time' => $time
                ];
                CouponsUser::query()->insert($value);
            }

            return true;
        }

        return false;
    }

    /**
     * 获取可领取的优惠券 （有效期内或提前 可领取）
     * @param int $cou_id
     * @param array $columns
     * @return array
     */
    public static function getCouponsById(int $cou_id = 0, array $columns = [])
    {
        if (empty($cou_id)) {
            return [];
        }

        $time = TimeRepository::getGmTime();

        $model = Coupons::query()->where('cou_id', $cou_id)
            ->where('review_status', 3)
            ->where('cou_end_time', '>=', $time)
            ->where('receive_start_time', '<=', $time)->where('receive_end_time', '>=', $time)
            ->where('status', COUPON_STATUS_EFFECTIVE);

        if (!empty($columns)) {
            $model = $model->select($columns);
        }

        $model = $model->first();

        return $model ? $model->toArray() : [];
    }

    /**
     * 优惠券领取限制条件
     *
     * @param int $user_id
     * @param array $coupons
     * @return bool
     */
    public static function checkCoupons(int $user_id = 0, array $coupons = [])
    {
        if (empty($user_id) || empty($coupons)) {
            return false;
        }

        /**
         * 1. 所有优惠券类型 领取限制条件：总计限领、每人限领
         * 2. 会员券、免邮券 同1 增加领取限制条件：参与会员等级
         * 3. 全场券 同1 无额外条件
         * 4. 购物券 同1 增加领取限制条件：参与会员等级、可赠商品、购物满足指定金额
         */

        // 领取限制条件：总计限领。获取当前的优惠券已被发放的数量(防止发放数量超过设定发放数量)
        $total_num = CouponsUser::query()->where('is_delete', 0)->where('cou_id', $coupons['cou_id'])->count('cou_id');
        if (empty($coupons['cou_total']) || $coupons['cou_total'] <= $total_num) {
            return false;
        }

        // 领取限制条件：每人限领。获取每个用户已领取数量
        $cou_user_num = CouponsUser::query()->where('is_delete', 0)->where('user_id', $user_id)->where('cou_id', $coupons['cou_id'])->count('cou_id');
        if ($coupons['cou_user_num'] <= $cou_user_num) {
            return false;
        }

        // 会员券、免邮券  领取限制条件：参与会员等级 TODO
        // if (in_array($coupons['cou_type'], [VOUCHER_USER, VOUCHER_SHIPPING])) {
        //     $cou_ok_user = !empty($coupons['cou_ok_user']) ? explode(',', $coupons['cou_ok_user']) : [];
        //     if ($cou_ok_user) {
        //         $user_rank = app(UserCommonService::class)->getUserRankByUid($user_id);
        //         $rank_id = $user_rank['rank_id'] ?? 0;
        //
        //         if ($rank_id == 0 || !in_array($rank_id, $cou_ok_user)) {
        //             return false;
        //         }
        //     }
        // }

        return true;
    }

}
