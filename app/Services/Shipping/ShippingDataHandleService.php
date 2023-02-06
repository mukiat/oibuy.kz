<?php

namespace App\Services\Shipping;

use App\Models\Shipping;
use App\Models\ShippingArea;
use App\Repositories\Common\BaseRepository;

class ShippingDataHandleService
{
    /**
     * 配送列表
     *
     * @param array $shipping_id
     * @param null $enabled
     * @param array $data
     * @param int $limit
     * @return array|string
     */
    public static function getShippingDataList($shipping_id = [], $enabled = null, $data = [], $limit = 0)
    {
        $shipping_id = BaseRepository::getExplode($shipping_id);

        if (empty($shipping_id)) {
            return $shipping_id;
        }

        $shipping_id = $shipping_id ? array_unique($shipping_id) : [];

        $data = $data ? $data : '*';

        $res = Shipping::select($data)->whereIn('shipping_id', $shipping_id);

        if (!is_null($enabled)) {
            $res = $res->where('enabled', $enabled);
        }

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['shipping_id']] = $row;
            }
        }

        return $arr;
    }

    /**
     * 配送列表
     *
     * @param array $shipping_area_id
     * @param null $ru_id
     * @param array $data
     * @param int $limit
     * @return array
     */
    public static function getShippingAreaDataList($shipping_area_id = [], $ru_id = null, $data = [], $limit = 0)
    {
        if (empty($shipping_id) && is_null($ru_id)) {
            return $shipping_area_id;
        }
        
        $shipping_area_id = BaseRepository::getExplode($shipping_area_id);
        $ru_id = BaseRepository::getExplode($ru_id);

        $shipping_area_id = $shipping_area_id ? array_unique($shipping_area_id) : [];

        $data = $data ? $data : '*';

        $res = ShippingArea::select($data);

        if ($shipping_area_id) {
            $res = $res->whereIn('shipping_area_id', $shipping_area_id);
        }

        if ($ru_id) {
            $res = $res->whereIn('ru_id', $ru_id);
        }

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['shipping_area_id']] = $row;
            }
        }

        return $arr;
    }
}