<?php

namespace App\Services\Region;

use App\Models\Region;
use App\Repositories\Common\BaseRepository;

class RegionService
{
    /**
     * 根据类型获取地区
     *
     * @param int $type
     * @param null $parent
     * @return mixed
     */
    public function getRegionsList($type = 0, $parent = null)
    {
        $res = Region::where('region_type', $type);

        if (!is_null($parent)) {
            $res = $res->where('parent_id', $parent);
        }

        $res = BaseRepository::getToArrayGet($res);

        return $res;
    }

    /**
     * 地区信息
     *
     * @param int $region_id
     * @return mixed
     */
    public function getRegionInfo($region_id = 0)
    {
        $row = Region::where('region_id', $region_id);
        $row = BaseRepository::getToArrayFirst($row);

        return $row;
    }

    /**
     * 头部地区
     *
     * @param string $region_name
     * @return array
     * @throws \Exception
     */
    public function headerRegionName($region_name = '')
    {
        $result = ['error' => 0, 'message' => '', 'content' => ''];

        if (session('user_id') > 0) {
            if (session()->exists('nick_name')) {
                $result['nick_name'] = e(session('nick_name'));
            } else {
                $result['nick_name'] = e(session('user_name'));
            }
        } else {
            $result['nick_name'] = lang('common.please_login');
        }

        $result['content'] = $region_name;

        return $result;
    }

    /**
     * 获取父级地区列表
     *
     * @param int $region_id
     * @return array
     */
    public function getRegionParentList($region_id = 0)
    {
        $array = [];

        if ($region_id > 0) {
            $region = [
                'region_id' => intval($region_id),
                'region_name' => '',
            ];

            while (!empty($region)) {

                $region_id = $region['parent_id'] ?? $region_id;

                $region = Region::select('parent_id', 'region_id', 'region_name', 'region_type')
                    ->where('region_id', $region_id);
                $region = BaseRepository::getToArrayFirst($region);

                if (!empty($region)) {
                    $array[] = [
                        'region_id' => intval($region['region_id']),
                        'region_name' => $region['region_name'] ?? ''
                    ];
                }
            }

            $array = array_reverse($array);
        }

        return $array;
    }
}
