<?php

namespace App\Repositories\User;

use App\Models\AccountLog;
use App\Models\MerchantsShopInformation;
use App\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\TimeRepository;
use App\Support\Abstracts\Repository;
use Illuminate\Support\Facades\DB;

/**
 * Class UsersRepository
 * @package App\Repositories\User
 */
class UsersRepository extends Repository
{
    public function __construct(Users $model)
    {
        parent::__construct();

        $this->model = $model;
    }

    /**
     * 获取推荐会员id
     *
     * @param int $parent_id
     * @return int
     */
    public static function getAffiliate($parent_id = 0)
    {
        if ($parent_id > 0) {
            $user_id = Users::where('user_id', $parent_id)->value('user_id');
            return $user_id ?? 0;
        }

        return 0;
    }

    /**
     * 记录会员帐户变动
     *
     * @param int $user_id
     * @param int $user_money
     * @param int $frozen_money
     * @param int $rank_points
     * @param int $pay_points
     * @param string $change_desc
     * @param int $change_type
     * @param int $deposit_fee
     * @return bool
     */
    public static function log_account_change($user_id = 0, $user_money = 0, $frozen_money = 0, $rank_points = 0, $pay_points = 0, $change_desc = '', $change_type = ACT_OTHER, $deposit_fee = 0)
    {
        if (empty($user_id)) {
            return false;
        }

        /* 插入帐户变动记录 */
        $account_log = [
            'user_id' => $user_id,
            'user_money' => $user_money,
            'frozen_money' => $frozen_money,
            'rank_points' => $rank_points,
            'pay_points' => $pay_points,
            'change_time' => TimeRepository::getGmTime(),
            'change_desc' => $change_desc,
            'change_type' => $change_type,
            'deposit_fee' => $deposit_fee
        ];
        AccountLog::insert($account_log);

        /* 更新用户信息 */
        $user_money = $user_money + $deposit_fee;
        $update_log = [
            'frozen_money' => DB::raw("frozen_money  + ('$frozen_money')"),
            'pay_points' => DB::raw("pay_points  + ('$pay_points')"),
            'rank_points' => DB::raw("rank_points  + ('$rank_points')")
        ];
        Users::where('user_id', $user_id)->increment('user_money', $user_money, $update_log);
        return true;
    }

    /**
     * @param array $filter
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function userModelFilter($filter = [])
    {
        $model = Users::query();

        if ($filter['start_date'] && $filter['end_date']) {
            $model = $model->whereBetween('reg_time', [$filter['start_date'], $filter['end_date']]);
        }

        //按店铺查询 start
        $filter['store_search'] = empty($filter['store_search']) ? 0 : intval($filter['store_search']);
        $filter['merchant_id'] = isset($filter['merchant_id']) ? intval($filter['merchant_id']) : 0;
        $filter['store_keyword'] = isset($filter['store_keyword']) ? trim($filter['store_keyword']) : '';

        if ($filter['store_search'] != 0) {
            if (isset($filter['ru_id']) && $filter['ru_id'] == 0) {
                if ($filter['store_search'] == 1) {
                    $model = $model->where('user_id', $filter['merchant_id']);
                } else if ($filter['store_search'] > 1) {
                    if ($filter['store_search'] == 2) {
                        $userIdList = MerchantsShopInformation::where('rz_shop_name', 'like', '%' . $filter['store_keyword'] . '%')->pluck('user_id');
                    } elseif ($filter['store_search'] == 3) {
                        $query = MerchantsShopInformation::where('shoprz_brand_name', 'like', '%' . $filter['store_keyword'] . '%');
                        $store_type = $filter['store_type'] ?? 0;
                        if ($store_type) {
                            $query->where('shop_name_suffix', $store_type);
                        }
                        $userIdList = $query->pluck('user_id');
                    }

                    if ($userIdList) {
                        $model = $model->whereIn('user_id', $userIdList);
                    }
                }
            }
        }
        //按店铺查询 end

        if ($filter['keywords']) {
            $keywords = e($filter['keywords']);
            $model->where(function ($query) use ($keywords) {
                $query->where('user_name', 'like', '%' . $keywords . '%')->orWhere('nick_name', 'like', '%' . $keywords . '%')->orWhere('mobile_phone', 'like', '%' . $keywords . '%');
            });
        }

        if ($filter['mobile_phone']) {
            $model = $model->where('mobile_phone', $filter['mobile_phone']);
        }

        if ($filter['email']) {
            $model = $model->where('email', $filter['email']);
        }

        if ($filter['rank']) {
            $model = $model->where('user_rank', $filter['rank']);
        }
        if ($filter['rank_id']) {
            $model = $model->where('user_rank', $filter['rank_id']);
        }

        if ($filter['pay_points_gt'] && $filter['pay_points_lt']) {
            $model = $model->whereBetween('pay_points', [$filter['pay_points_lt'], $filter['pay_points_gt']]);
        }

        if ($filter['checkboxes']) {
            $checkboxes = !is_array($filter['checkboxes']) ? explode(",", $filter['checkboxes']) : $filter['checkboxes'];
            $model = $model->whereIn('user_id', $checkboxes);
        }

        return $model;
    }

    /**
     * @param array $filter
     * @return int
     */
    public function user_total($filter = [])
    {
        $model = self::userModelFilter($filter);

        $total = $model->count();

        return $total ?? 0;
    }

    /**
     * @param array $filter
     * @param int $start
     * @param int $limit
     * @return mixed
     */
    public function user_list($filter = [], $start = 0, $limit = 15)
    {
        $model = self::userModelFilter($filter);

        // 分页查询
        $model = $model->skip($start);
        if ($limit > 0) {
            $model = $model->take($limit);
        }

        $model = $model->orderBy($filter['sort_by'], $filter['sort_order']);

        return BaseRepository::getToArrayGet($model);
    }

}
