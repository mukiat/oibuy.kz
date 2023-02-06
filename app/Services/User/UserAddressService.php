<?php

namespace App\Services\User;

use App\Models\OrderInfo;
use App\Models\OrderReturn;
use App\Models\Region;
use App\Models\UserAddress;
use App\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Repositories\User\UserAddressRepository;
use Illuminate\Support\Arr;

class UserAddressService
{
    protected $userDataHandleService;

    public function __construct(
        UserDataHandleService $userDataHandleService
    )
    {
        $this->userDataHandleService = $userDataHandleService;
    }

    /**
     * 获取会员收货地址信息
     *
     * @access  public
     * @param int $address_id
     * @param int $user_id
     * @return array
     */
    public function getUserAddressInfo($address_id = 0, $user_id = 0)
    {
        if ($address_id > 0) {
            $consignee = UserAddress::where('user_id', $user_id)->where('address_id', $address_id);
        } else {
            $address_id = Users::where('user_id', $user_id)->value('address_id');
            $consignee = UserAddress::where('user_id', $user_id)->where('address_id', $address_id);
        }

        $consignee = $consignee->with([
            'getRegionProvince' => function ($query) {
                $query->select('region_id', 'region_name');
            },
            'getRegionCity' => function ($query) {
                $query->select('region_id', 'region_name');
            },
            'getRegionDistrict' => function ($query) {
                $query->select('region_id', 'region_name');
            },
            'getRegionStreet' => function ($query) {
                $query->select('region_id', 'region_name');
            }
        ]);

        $consignee = BaseRepository::getToArrayFirst($consignee);

        if ($consignee) {
            if ($consignee['get_region_province']) {
                $province_name = $consignee['get_region_province']['region_name'];
                $consignee = BaseRepository::getArrayExcept($consignee, 'get_region_province');
            } else {
                $province_name = '';
            }

            $consignee['province_name'] = $province_name;

            if ($consignee['get_region_city']) {
                $city_name = $consignee['get_region_city']['region_name'];
                $consignee = BaseRepository::getArrayExcept($consignee, 'get_region_city');
            } else {
                $city_name = '';
            }

            $consignee['city_name'] = $city_name;

            if ($consignee['get_region_district']) {
                $district_name = $consignee['get_region_district']['region_name'];
                $consignee = BaseRepository::getArrayExcept($consignee, 'get_region_district');
            } else {
                $district_name = '';
            }

            $consignee['district_name'] = $district_name;

            if ($consignee['get_region_street']) {
                $street_name = $consignee['get_region_street']['region_name'];
                $consignee = BaseRepository::getArrayExcept($consignee, 'get_region_street');
            } else {
                $street_name = '';
            }

            $consignee['street_name'] = $street_name;

            $region = $province_name . " " . $city_name . " " . $district_name . " " . $street_name;
            $consignee['region'] = trim($region);
        }

        return $consignee;
    }

    /**
     * 获取收货地址的数量
     *
     * @param int $user_id
     * @param array $consignee
     * @return mixed
     */
    public function getUserAddressCount($user_id = 0, $consignee = [])
    {
        $res = UserAddress::where('user_id', $user_id);

        if ($consignee) {
            if (isset($consignee['consignee']) && $consignee['consignee']) {
                $res = $res->where('consignee', $consignee['consignee']);
            }

            if (isset($consignee['country']) && $consignee['country']) {
                $res = $res->where('country', $consignee['country']);
            }

            if (isset($consignee['province']) && $consignee['province']) {
                $res = $res->where('province', $consignee['province']);
            }

            if (isset($consignee['city']) && $consignee['city']) {
                $res = $res->where('city', $consignee['city']);
            }

            if (isset($consignee['district']) && $consignee['district']) {
                $res = $res->where('district', $consignee['district']);
            }

            if (isset($consignee['street']) && $consignee['street']) {
                $res = $res->where('street', $consignee['street']);
            }

            if ($consignee['address_id'] > 0) {
                $res = $res->where('address_id', '<>', $consignee['address_id']);
            }
        }

        return $res->count();
    }

    /**
     * 保存用户的收货人信息
     * 如果收货人信息中的 id 为 0 则新增一个收货人信息
     *
     * @access  public
     * @param array $consignee
     * @param boolean $default 是否将该收货人信息设置为默认收货人信息
     * @return array
     */
    public function saveConsignee($consignee = [], $default = false)
    {
        $user_id = session('user_id', 0);

        $res = false;
        if ($consignee['address_id'] > 0) {
            /* 修改地址 */
            $res = UserAddress::where('address_id', $consignee['address_id'])->where('user_id', $consignee['user_id'])->update($consignee);
        } else {
            /* 添加地址 */
            $new_address = BaseRepository::getArrayfilterTable($consignee, 'user_address');
            try {
                $address_id = UserAddress::insertGetId($new_address);

                $count = UserAddress::where('user_id', $user_id)->count('address_id');

                /* 会员一条收货地址时，设置为默认收货地址 */
                if ($count == 1) {
                    $default = true;
                }
            } catch (\Exception $e) {
                $error_no = (stripos($e->getMessage(), '1062 Duplicate entry') !== false) ? 1062 : $e->getCode();

                if ($error_no > 0 && $error_no != 1062) {
                    die($e->getMessage());
                }
            }
            $consignee['address_id'] = $address_id ?? 0;
        }

        if ($default) {
            /* 保存为用户的默认收货地址 */
            $res = Users::where('user_id', $user_id)->update(['address_id' => $consignee['address_id']]);
        }

        $arr = [
            'user_consignee' => $consignee
        ];

        if ($res > 0) {
            $arr['error'] = true;
        } else {
            $arr['error'] = false;
        }

        return $arr;
    }

    /**
     * 获取会员收货地址列表
     * @param int $user_id
     * @param array $offset
     * @return array
     */
    public function getUserAddressList($user_id = 0, $offset = [])
    {
        if (empty($user_id)) {
            return [];
        }

        $model = UserAddress::where('user_id', $user_id);

        if (!empty($offset)) {
            $model = $model->offset($offset['start'])->limit($offset['limit']);
        } else {
            $model = $model->limit(config('app.address_count', 50));
        }

        $model = $model->orderBy('address_id', 'DESC');

        $res = BaseRepository::getToArrayGet($model);

        $arr = [];
        if ($res) {
            $user_id = BaseRepository::getKeyPluck($res, 'user_id');
            $userList = $this->userDataHandleService->userDataList($user_id);

            $province_id = BaseRepository::getKeyPluck($res, 'province');
            $city_id = BaseRepository::getKeyPluck($res, 'city');
            $district_id = BaseRepository::getKeyPluck($res, 'district');
            $street_id = BaseRepository::getKeyPluck($res, 'street');

            $provinceList = $this->userDataHandleService->provinceDataList($province_id);
            $cityList = $this->userDataHandleService->cityDataList($city_id);
            $districtList = $this->userDataHandleService->districtDataList($district_id);
            $streetList = $this->userDataHandleService->streetDataList($street_id);

            foreach ($res as $row) {
                $province_name = $provinceList[$row['province']]['region_name'] ?? '';
                $city_name = $cityList[$row['city']]['region_name'] ?? '';
                $district_name = $districtList[$row['district']]['region_name'] ?? '';
                $street_name = $streetList[$row['street']]['region_name'] ?? '';

                $row['province_name'] = $province_name;
                $row['city_name'] = $city_name;
                $row['district_name'] = $district_name;
                $row['street_name'] = $street_name;

                // 四个直辖市
                if ($row['province_name'] == $row['city_name'] || in_array($row['province'], ['110000', '120000', '310000', '500000'])) {
                    $region = $city_name . ' ' . $district_name . ' ' . $street_name;
                } else {
                    $region = $province_name . ' ' . $city_name . ' ' . $district_name . ' ' . $street_name;
                }

                $row['region'] = trim($region);

                // 默认用户收货地址id
                $row['is_checked'] = 0;

                $address_id = $userList[$row['user_id']]['address_id'] ?? 0;

                if ($row['address_id'] == $address_id) {
                    $row['is_checked'] = 1;
                }

                $arr[] = $row;
            }
        }

        return $arr;
    }

    /**
     * 删除会员收货地址
     * @param int $id
     * @param int $user_id
     * @return bool
     */
    public function dropConsignee($id = 0, $user_id = 0)
    {
        $userAddress = UserAddress::where('address_id', $id)->first();

        $uid = $userAddress->user_id ?? 0;

        if ($uid != $user_id) {
            return false;
        }

        return $userAddress->delete();
    }

    /**
     * 添加或更新指定用户收货地址
     *
     * @param array $address
     * @param int $default 指定默认收货地址
     * @return bool
     * @throws \Exception
     */
    public function updateAddress($address = [], $default = 0)
    {
        if (empty($address)) {
            return false;
        }

        $address_id = intval($address['address_id']);

        if ($address_id > 0) {
            /* 更新指定记录 */
            UserAddress::where('address_id', $address_id)->where('user_id', $address['user_id'])->update($address);
        } else {
            if (isset($address['address_id'])) {
                unset($address['address_id']);
            }

            /* 键值交换 */
            $flipData = BaseRepository::getArrayFlip($address);

            $count = 0;
            if (in_array('consignee', $flipData) && in_array('mobile', $flipData) && in_array('address', $flipData)) {
                $count = UserAddress::select('address_id')->where('consignee', $address['consignee'])
                    ->where('mobile', $address['mobile'])
                    ->where('address', $address['address'])
                    ->where('user_id', $address['user_id'])
                    ->count();
            }

            /* 插入一条新记录 */
            if ($count == 0) {
                $address_id = UserAddress::insertGetId($address);
            }
        }

        cache()->forget('flow_consignee_' . $address['user_id']);

        if ($address_id > 0) {
            $res_count = UserAddress::where('user_id', $address['user_id'])->count();

            if ($res_count == 1) {
                // 添加第一个收货地址 设置为默认并保存session
                Users::where('user_id', $address['user_id'])->update(['address_id' => $address_id]);
                session([
                    'flow_consignee' => $address
                ]);
            }
        }

        if ($default > 0 && !empty($address['user_id'])) {
            Users::where('user_id', $address['user_id'])->update([
                'address_id' => $address_id
            ]);
            return true;
        }

        return true;
    }

    /**
     * 获取指定用户收货地址
     *
     * @param int $address_id
     * @param int $user_id
     * @return bool|array
     */
    public function getUpdateFlowConsignee($address_id = 0, $user_id = 0)
    {
        if (empty($user_id) || empty($address_id)) {
            return [];
        }

        $consignee = UserAddress::where('address_id', $address_id)->where('user_id', $user_id);
        $consignee = BaseRepository::getToArrayFirst($consignee);

        return $consignee;
    }

    /**
     * 查询用户地址信息
     *
     * @param int $order_id
     * @param string $address
     * @param int $type
     * @return string
     */
    public function getUserRegionAddress($order_id = 0, $address = '', $type = 0)
    {
        /* 取得区域名 */
        if ($type == 1) {
            $res = OrderReturn::where('ret_id', $order_id);
        } elseif ($type == 2) {
            if (!file_exists(SUPPLIERS)) {
                return '';
            }
            $res = \App\Modules\Suppliers\Models\WholesaleOrderInfo::where('order_id', $order_id);
        } else {
            $res = OrderInfo::where('order_id', $order_id);
        }

        $res = $res->with([
            'getRegionProvince' => function ($query) {
                $query->select('region_id', 'region_name');
            },
            'getRegionCity' => function ($query) {
                $query->select('region_id', 'region_name');
            },
            'getRegionDistrict' => function ($query) {
                $query->select('region_id', 'region_name');
            },
            'getRegionStreet' => function ($query) {
                $query->select('region_id', 'region_name');
            }
        ]);

        $res = BaseRepository::getToArrayFirst($res);

        $region = '';
        if ($res) {
            if ($res['get_region_province']) {
                $province_name = $res['get_region_province']['region_name'];
            } else {
                $province_name = '';
            }

            if ($res['get_region_city']) {
                $city_name = $res['get_region_city']['region_name'];
            } else {
                $city_name = '';
            }

            if ($res['get_region_district']) {
                $district_name = $res['get_region_district']['region_name'];
            } else {
                $district_name = '';
            }

            if ($res['get_region_street']) {
                $street_name = $res['get_region_street']['region_name'];
            } else {
                $street_name = '';
            }

            $region = $province_name . " " . $city_name . " " . $district_name . " " . $street_name;
            $region = trim($region);
            if ($address) {
                $region = $region . " " . $address;
            }
        }

        return $region;
    }

    /**
     * 同步微信收货地址
     * @param array $wximport
     * @return array
     */
    public function wximportInfo($wximport = [])
    {
        if (empty($wximport)) {
            return [];
        }

        $info = [];

        $info['consignee'] = $wximport['userName'] ?? '';
        $info['mobile'] = $wximport['telNumber'] ?? '';
        $info['address'] = $wximport['detailInfo'] ?? '';

        $province = $wximport['provinceName'] ?? '';
        $city = $wximport['cityName'] ?? '';
        $district = $wximport['countyName'] ?? '';

        //取得省的ID
        $region = $this->getRegion(['region_type' => 1, 'region_name' => $province]);
        $info['province'] = $region['region_id'] ?? 0;//省id
        $province_name = $region['region_name'] ?? '';

        //取得市的ID
        $region = $this->getRegion(['region_type' => 2, 'region_name' => $city]);
        $info['city'] = $region['region_id'] ?? 0;//市id
        $city_name = $region['region_name'] ?? '';

        //取得地区ID
        $region = $this->getRegion(['region_type' => 3, 'region_name' => $district]);
        $info['district'] = $region['region_id'] ?? 0;//区id
        $district_name = $region['region_name'] ?? '';

        // 默认取得地区下首个街道ID
        $street = $this->getRegion(['region_type' => 4, 'parent_id' => $info['district']]);
        $info['street'] = $street['region_id'] ?? 0;//街道id
        $info['street_name'] = $street_name = $street['region_name'] ?? ''; // 街道名称

        $info['region'] = trim($province_name . " " . $city_name . " " . $district_name . " " . $street_name);

        return $info;
    }

    /**
     * 获取地区
     * @param array $where
     * @return array
     */
    public static function getRegion($where = [])
    {
        if (empty($where)) {
            return [];
        }

        $region = Region::where($where);
        return BaseRepository::getToArrayFirst($region);
    }

    /**
     * 设置用户默认收货地址
     * @param int $user_id
     * @param int $address_id
     * @return bool
     */
    public function setDefaultAddress($user_id = 0, $address_id = 0)
    {
        if (empty($user_id) || empty($address_id)) {
            return false;
        }

        $count = UserAddress::where('address_id', $address_id)->where('user_id', $user_id)->count();

        if (empty($count)) {
            return false;
        }

        return Users::where('user_id', $user_id)->update(['address_id' => $address_id]);
    }

    /**
     * 获取用户默认收货地址
     *
     * @param int $user_id
     * @return array
     */
    public static function getDefaultByUserId($user_id = 0)
    {
        return UserAddressRepository::getDefaultAddress($user_id);
    }

    /**
     * 匹配用户收货地址
     *
     * @param int $user_id
     * @param array $area_cookie
     * @return array
     */
    public function match_user_consignee($user_id = 0, $area_cookie = [])
    {
        $result = ['code' => 0, 'msg' => ''];

        // 匹配地址
        $match_address = [];
        $default_address = [];
        // 用户收货地址列表
        $user_address = $this->getUserAddressList($user_id);
        if (!empty($user_address)) {
            // 是否匹配用户收货地址(如果有匹配至街道)，如果匹配有多个 取最新添加一条
            foreach ($user_address as $k => $address) {
                $province_id = $address['province'] ?? 0;
                $city_id = $address['city'] ?? 0;
                $district_id = $address['district'] ?? 0;
                $street_id = $address['street'] ?? 0;

                // 默认收货地址id
                if ($address['is_checked'] == 1) {
                    $default_address = $address;
                }

                if ($province_id && $city_id && $district_id && $street_id) {
                    if (in_array($province_id, $area_cookie) && in_array($city_id, $area_cookie) && in_array($district_id, $area_cookie) && in_array($street_id, $area_cookie)) {
                        $match_address[$address['address_id']] = $address;
                    }
                } elseif ($province_id && $city_id && $district_id && empty($street_id)) {
                    if (in_array($province_id, $area_cookie) && in_array($city_id, $area_cookie) && in_array($district_id, $area_cookie)) {
                        $match_address[$address['address_id']] = $address;
                    }
                }
            }

            if (!empty($match_address)) {
                // 有匹配地址
                $address_ids = Arr::pluck($match_address, 'address_id');
                $address_id = max($address_ids);

                $consignee = $match_address[$address_id];

                $result['code'] = 0;
                $result['consignee'] = $consignee;
            } else {
                // 无匹配地址 选中默认收货地址
                $consignee = $default_address ?? [];

                $result['code'] = 2;
                $result['msg'] = trans('flow.match_user_consignee_tips');
                $result['consignee'] = $consignee;
            }

            $result['user_address'] = $user_address;
        }

        return $result;
    }
}
