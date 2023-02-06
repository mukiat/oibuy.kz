<?php

namespace App\Services\Region;

use App\Models\Region;
use App\Models\RegionWarehouse;
use App\Models\ShippingPoint;
use App\Repositories\Common\BaseRepository;

class RegionDataHandleService
{
    /**
     * 地区列表信息
     *
     * @param array $region_id
     * @param array $data
     * @param int $parent
     * @return array|string
     */
    public static function getRegionDataList($region_id = [], $data = [], $parent = 0)
    {
        $region_id = BaseRepository::getExplode($region_id);

        if (empty($region_id)) {
            return $region_id;
        }

        $region_id = $region_id ? array_unique($region_id) : [];

        $data = $data ? $data : '*';

        $res = Region::select($data);

        if (!empty($parent)) {
            $res = $res->where('parent_id', $region_id);
        } else {
            $res = $res->whereIn('region_id', $region_id);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                if (!empty($parent)) {
                    $arr[$row['parent_id']][] = $row;
                } else {
                    $arr[$row['region_id']] = $row;
                }
            }
        }

        return $arr;
    }

    /**
     * 仓库地区列表信息
     *
     * @param array $id
     * @param array $data
     * @param int $limit
     * @return array
     */
    public static function regionWarehouseDataList($id = [], $data = [], $limit = 0)
    {
        $id = BaseRepository::getExplode($id);

        if (empty($id)) {
            return [];
        }

        $id = array_unique($id);

        $data = $data ? $data : '*';

        $res = RegionWarehouse::select($data)
            ->whereIn('region_id', $id);

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['region_id']] = $val;
            }
        }

        return $arr;
    }

    /**
     * 自提点信息
     *
     * @param array $shipping_area_id
     * @param string $data
     * @return array
     */
    public function getShippingPointDataList($shipping_area_id = [], $data = '*')
    {
        $shipping_area_id = BaseRepository::getExplode($shipping_area_id);

        if (empty($shipping_area_id)) {
            return [];
        }

        $shipping_area_id = array_unique($shipping_area_id);

        $data = $data ? $data : '*';

        $res = ShippingPoint::select($data)->whereIn('shipping_area_id', $shipping_area_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['shipping_area_id']] = $val;
            }
        }

        return $arr;
    }
}
