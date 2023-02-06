<?php

namespace App\Services\Bonus;

use App\Models\BonusType;
use App\Models\UserBonus;
use App\Repositories\Common\BaseRepository;

class BonusDataHandleService
{
    /**
     * 红包类型列表
     *
     * @param array $bonus_type_id
     * @param array $data
     * @param array $where
     * @param int $limit
     * @return array
     */
    public static function getBonusTypeDataList($bonus_type_id = [], $data = [], $where = [], $limit = 0)
    {
        $bonus_type_id = BaseRepository::getExplode($bonus_type_id);

        if (empty($bonus_type_id)) {
            return $bonus_type_id;
        }

        $bonus_type_id = $bonus_type_id ? array_unique($bonus_type_id) : [];

        $data = $data ? $data : '*';

        $res = BonusType::select($data)->whereIn('type_id', $bonus_type_id);

        if (isset($where['today'])) {
            $res = $res->whereRaw("IF(date_type < 1, use_start_date < '" . $where['today'] . "' AND use_end_date > '" . $where['today'] . "', 1)");
        }

        if (isset($where['ru_id']) && isset($where['goods_amount']) && isset($where['self_amount']) && $where['ru_id'] == 0) {
            $res = $res->whereRaw("IF(usebonus_type > 0, usebonus_type = 1 AND " . 'min_goods_amount <= ' . $where['goods_amount'] . ", usebonus_type = 0 AND " . 'min_goods_amount <= ' . $where['self_amount'] . " )");
        }

        if (isset($where['ru_id']) && $where['ru_id'] > -1) {
            $res = $res->where('user_id', $where['ru_id']);
        }

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['type_id']] = $row;
            }
        }

        return $arr;
    }

    /**
     * 订单列表
     *
     * @param array $bonus_type_id
     * @param array $data
     * @param int $limit
     * @return array
     */
    public static function getUserBonusDataList($bonus_type_id = [], $data = [], $limit = 0)
    {
        if (empty($bonus_type_id)) {
            return [];
        }

        $bonus_type_id = BaseRepository::getExplode($bonus_type_id);

        $bonus_type_id = array_unique($bonus_type_id);

        $data = empty($data) ? "*" : $data;

        $res = UserBonus::select($data)->whereIn('bonus_type_id', $bonus_type_id);

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['bonus_type_id']] = $val;
            }
        }

        return $arr;
    }
}