<?php

namespace App\Services\ValueCard;

use App\Models\ValueCard;
use App\Models\ValueCardRecord;
use App\Models\ValueCardType;
use App\Repositories\Common\BaseRepository;

class ValueCardDataHandleService
{
    /**
     * 储值卡类型列表
     *
     * @param array $card_id
     * @param array $data
     * @param int $limit
     * @return array
     */
    public static function getValueCardTypeDataList($card_id = [], $data = [], $limit = 0)
    {
        $card_id = BaseRepository::getExplode($card_id);

        if (empty($card_id)) {
            return $card_id;
        }

        $card_id = $card_id ? array_unique($card_id) : [];

        $data = $data ? $data : '*';

        $res = ValueCardType::select($data)->whereIn('id', $card_id);

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

    /**
     * 发放储值卡列表
     *
     * @param array $vid
     * @param array $data
     * @param int $limit
     * @return array|string
     */
    public static function getValueCardDataList($vid = [], $data = [], $limit = 0)
    {
        $vid = BaseRepository::getExplode($vid);

        if (empty($vid)) {
            return $vid;
        }

        $vid = $vid ? array_unique($vid) : [];

        $data = $data ? $data : '*';

        $res = ValueCard::select($data)->whereIn('vid', $vid);

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['vid']] = $row;
            }
        }

        return $arr;
    }

    /**
     * 订单使用储值卡列表
     *
     * @param array $order_id
     * @param array $data
     * @param int $limit
     * @return array|string
     */
    public static function getOrderValueCardRecordDataList($order_id = [], $data = [], $limit = 0)
    {
        $order_id = BaseRepository::getExplode($order_id);

        if (empty($order_id)) {
            return $order_id;
        }

        $order_id = $order_id ? array_unique($order_id) : [];

        $data = $data ? $data : '*';

        $res = ValueCardRecord::select($data)->whereIn('order_id', $order_id)
            ->where('ret_id', 0)
            ->where('add_val', 0)
            ->where('use_val', '>', 0);

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['order_id']] = $row;
            }
        }

        return $arr;
    }
}