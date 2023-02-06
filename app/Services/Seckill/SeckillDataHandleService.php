<?php

namespace App\Services\Seckill;

use App\Models\Seckill;
use App\Models\SeckillTimeBucket;
use App\Repositories\Common\BaseRepository;

class SeckillDataHandleService
{
    /**
     * 秒杀列表
     *
     * @param array $sec_id
     * @param array $data
     * @param int $limit
     * @return array
     */
    public static function getSeckillDataList($sec_id = [], $data = [], $limit = 0)
    {
        $sec_id = BaseRepository::getExplode($sec_id);

        if (empty($sec_id)) {
            return [];
        }

        $sec_id = $sec_id ? array_unique($sec_id) : [];

        $data = $data ? $data : '*';

        $res = Seckill::select($data);

        $res = $res->whereIn('sec_id', $sec_id);

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['sec_id']] = $row;
            }
        }

        return $arr;
    }

    /**
     * 秒杀时间段列表
     *
     * @param array $id
     * @param array $data
     * @param int $limit
     * @return array
     */
    public static function getSeckillTimeBucketDataList($id = [], $data = [], $limit = 0)
    {
        $id = BaseRepository::getExplode($id);

        if (empty($id)) {
            return [];
        }

        $id = $id ? array_unique($id) : [];

        $data = $data ? $data : '*';

        $res = SeckillTimeBucket::select($data);

        $res = $res->whereIn('id', $id);

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['id']] = $row;
            }
        }

        return $arr;
    }
}