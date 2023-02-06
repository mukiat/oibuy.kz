<?php

namespace App\Services\User;

use App\Models\AdminUser;
use App\Models\Region;
use App\Models\Users;
use App\Models\UsersReal;
use App\Repositories\Common\BaseRepository;

class UserDataHandleService
{
    /**
     * 会员列表信息
     *
     * @param array $user_id
     * @param array $data
     * @param int $limit
     * @return array
     */
    public static function userDataList($user_id = [], $data = [], $limit = 0)
    {
        $user_id = BaseRepository::getExplode($user_id);

        if (empty($user_id)) {
            return [];
        }

        $user_id = array_unique($user_id);

        $data = empty($data) ? "*" : $data;

        $user_id = array_unique($user_id);

        $res = Users::select($data)
            ->whereIn('user_id', $user_id);

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['user_id']] = $val;
            }
        }

        return $arr;
    }

    /**
     * 获取订单会员
     *
     * @param array $user_id
     * @return array
     */
    public static function orderUser($user_id = [])
    {
        $user_id = BaseRepository::getExplode($user_id);

        if (empty($user_id)) {
            return [];
        }

        $user_id = array_unique($user_id);

        $res = Users::whereIn('user_id', $user_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['user_id']] = $val;
            }
        }

        return $arr;
    }

    /**
     * 省份地区信息
     *
     * @param array $province_id
     * @return array
     */
    public static function provinceDataList($province_id = [])
    {

        $province_id = BaseRepository::getExplode($province_id);

        if (empty($province_id)) {
            return [];
        }

        $province_id = array_unique($province_id);

        $res = Region::select('region_id', 'region_name')
            ->whereIn('region_id', $province_id);
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
     * 直辖市地区信息
     *
     * @param array $city_id
     * @return array
     */
    public static function cityDataList($city_id = [])
    {
        $city_id = BaseRepository::getExplode($city_id);

        if (empty($city_id)) {
            return [];
        }

        $city_id = array_unique($city_id);

        $res = Region::select('region_id', 'region_name')
            ->whereIn('region_id', $city_id);
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
     * 区域地区信息
     *
     * @param array $district_id
     * @return array
     */
    public static function districtDataList($district_id = [])
    {
        $district_id = BaseRepository::getExplode($district_id);

        if (empty($district_id)) {
            return [];
        }

        $district_id = array_unique($district_id);

        $res = Region::select('region_id', 'region_name')
            ->whereIn('region_id', $district_id);
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
     * 街道地区信息
     *
     * @param array $street_id
     * @return array
     */
    public static function streetDataList($street_id = [])
    {
        $street_id = BaseRepository::getExplode($street_id);

        if (empty($street_id)) {
            return [];
        }

        $street_id = array_unique($street_id);

        $res = Region::select('region_id', 'region_name')
            ->whereIn('region_id', $street_id);
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
     * 管理员信息
     * @param array $user_id
     * @param array $data
     * @param int $limit
     * @return array
     */
    public static function adminUserDataList($user_id = [], $data = [], $limit = 0)
    {
        $user_id = BaseRepository::getExplode($user_id);

        if (empty($user_id)) {
            return [];
        }

        $user_id = array_unique($user_id);

        $data = empty($data) ? "*" : $data;

        $user_id = array_unique($user_id);

        $res = AdminUser::select($data)
            ->whereIn('user_id', $user_id);

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['user_id']] = $val;
            }
        }

        return $arr;
    }

    /**
     * 会员实名认证信息
     *
     * @param array $user_id
     * @param array $data
     * @param int $limit
     * @return array
     */
    public static function getUsersRealDataList($user_id = [], $data = [], $limit = 0)
    {
        $user_id = BaseRepository::getExplode($user_id);

        if (empty($user_id)) {
            return [];
        }

        $user_id = array_unique($user_id);

        $data = empty($data) ? "*" : $data;

        $user_id = array_unique($user_id);

        $res = UsersReal::select($data)
            ->whereIn('user_id', $user_id);

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['user_id']] = $val;
            }
        }

        return $arr;
    }
}
