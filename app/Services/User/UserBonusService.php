<?php

namespace App\Services\User;

use App\Models\BonusType;
use App\Models\UserBonus;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\TimeRepository;

class UserBonusService
{
    /**
     * 查询会员的红包金额
     *
     * @param int $user_id
     * @return array
     */
    public function getUserBonus($user_id = 0)
    {
        $day = TimeRepository::getLocalGetDate();
        $cur_date = TimeRepository::getLocalMktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

        $bonus_type_list = BonusType::select('type_id')->where('use_start_date', '<', $cur_date)
            ->where('use_end_date', '>', $cur_date);
        $bonus_type_list = BaseRepository::getToArrayGet($bonus_type_list);
        $bonus_type_id = BaseRepository::getKeyPluck($bonus_type_list, 'type_id');

        if (empty($bonus_type_id)) {
            return [
                'bonus_type_id' => [],
                'bonus_count' => 0,
                'bonus_value' => 0
            ];
        }

        $row = UserBonus::select('bonus_id', 'bonus_type_id')
            ->where('user_id', $user_id)
            ->where('order_id', 0);

        $row = $row->whereIn('bonus_type_id', $bonus_type_id);
        $row = BaseRepository::getToArrayGet($row);

        $type_id = BaseRepository::getKeyPluck($row, 'bonus_type_id');

        $type_money = BonusType::whereIn('type_id', $type_id)->sum('type_money');
        $type_money = $type_money ? $type_money : 0;

        $arr['bonus_type_id'] = $type_id;
        $arr['bonus_count'] = BaseRepository::getArrayCount($row);
        $arr['bonus_value'] = $type_money;

        return $arr;
    }
}
