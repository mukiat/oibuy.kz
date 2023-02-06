<?php

namespace App\Services\Activity;


use App\Models\AuctionLog;
use App\Repositories\Common\BaseRepository;

class AuctionDataHandleService
{
    /**
     * 拍卖记录列表
     *
     * @param array $id
     * @param array $data
     * @return array
     */
    public static function AuctionLogDataList($id = [], $data = [])
    {
        $id = BaseRepository::getExplode($id);

        if (empty($id)) {
            return [];
        }

        $id = $id ? array_unique($id) : [];

        $data = $data ? $data : '*';

        $res = AuctionLog::select($data)->whereIn('act_id', $id);

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['act_id']] = $row;
            }
        }

        return $arr;
    }
}