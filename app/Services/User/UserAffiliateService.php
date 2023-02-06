<?php

namespace App\Services\User;

use App\Models\AffiliateLog;
use App\Models\OrderGoods;
use App\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;

/**
 * 会员推荐
 * Class UserAffiliateService
 * @package App\Services\User
 */
class UserAffiliateService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 获取推荐
     *
     * @param int $parent_id
     * @return int
     */
    public function getAffiliate($parent_id = 0)
    {
        if ($parent_id > 0) {
            $user_id = Users::where('user_id', $parent_id)->value('user_id');
            return $user_id ?? 0;
        }

        return 0;
    }

    /**
     * 推荐下线会员数量
     * @param  $user_id
     * @return  int
     */
    public function getUserChildNum($user_id = 0)
    {
        if (blank($user_id)) {
            return 0;
        }

        return Users::query()->where('parent_id', $user_id)->count('user_id');
    }

    /**
     * 推荐下线会员列表
     *
     * @param int $user_id 会员id
     * @param int $page
     * @param int $size
     * @return mixed
     */
    public function userChildList($user_id = 0, $page = 1, $size = 10)
    {
        if (blank($user_id)) {
            return [];
        }

        $start = ($page - 1) * $size;

        $model = Users::query()->select('user_id', 'user_name', 'nick_name', 'user_picture', 'reg_time')
            ->where('parent_id', $user_id)
            ->offset($start)
            ->limit($size)
            ->orderBy('reg_time', 'desc');

        $list = BaseRepository::getToArrayGet($model);

        return $list;
    }

    /**
     * 推荐注册分成奖励
     *
     * @param int $user_id
     * @return int
     */
    public function getUserParentAffiliate($user_id = 0)
    {
        // 推荐下级会员
        $model = Users::where('parent_id', $user_id)->pluck('user_id');
        $all_uid = $model ? $model->toArray() : [];

        if (blank($all_uid)) {
            return 0;
        }

        // 推荐注册分成
        $model = AffiliateLog::where('user_id', $user_id)->where('separate_type', 0);

        $model = $model->whereHasIn('getUsers', function ($query) use ($all_uid) {
            $query->whereIn('parent_id', $all_uid);
        });

        $model = $model->whereHasIn('getOrder', function ($query) {
            $query->where('main_count', 0)->where('parent_id', 0)->where('is_separate', 1);
        });

        $affiliate_money = $model->sum('money');

        return $affiliate_money;
    }

    /**
     * 推荐订单分成奖励
     *
     * @param int $user_id
     * @return array
     */
    public function getUserOrderAffiliate($user_id = 0)
    {
        // 推荐订单分成
        $model = AffiliateLog::where('user_id', $user_id)->where('separate_type', 1);

        $model = $model->whereHasIn('getOrder', function ($query) use ($user_id) {
            $query = $query->where('main_count', 0)->where('parent_id', $user_id)->where('is_separate', 1);
            if (file_exists(MOBILE_DRP)) {
                $query->where('is_drp', 0)->whereDoesntHaveIn('getDrpLog');
            }
        });

        $affiliate_money = $model->sum('money');

        return $affiliate_money;
    }


    /**
     * 推荐分成奖励 (注册分成+订单分成)
     *
     * @param int $user_id
     * @param string $type
     * @return int
     */
    public function getUserTotalAffiliate($user_id = 0, $type = '')
    {
        if (blank($user_id)) {
            return 0;
        }

        $model = AffiliateLog::where('user_id', $user_id)->whereIn('separate_type', [0, 1]);

        if (!blank($type) && $type == 'today') {
            // 今日分成收入
            $model = $model->where('time', '>=', TimeRepository::getLocalMktime(0, 0, 0, date('m'), date('d'), date('Y')));
        }

        $affiliate_money = $model->sum('money');

        return $affiliate_money;
    }

    /**
     * 推荐分成 销售总金额
     *
     * @param int $user_id
     * @return int
     */
    public function getUserTotalOrderAmount($user_id = 0)
    {
        if (blank($user_id)) {
            return 0;
        }

        // 总销售额
        $model = OrderGoods::query();

        $model = $model->whereHasIn('affiliateLog', function ($query) use ($user_id) {
            $query->where('order_id', '>', 0)->where('user_id', $user_id)->whereIn('separate_type', [0, 1]); // 注册分成+订单分成
        });

        $total = $model->sum('goods_price');

        return $total;
    }
}
