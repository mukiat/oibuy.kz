<?php

namespace App\Services\Common;

use App\Libraries\Http;
use App\Models\MerchantsRegionInfo;
use App\Models\Region;
use App\Models\RegionWarehouse;
use App\Repositories\Common\BaseRepository;
use ipip\datx\City;
use ipip\db\City as DbCity;

/**
 * 地区
 * Class Area
 * @package App\Services
 */
class AreaService
{
    /**
     * @var int 地区ID（省份）
     */
    protected $province_id = 0;

    /**
     * @var int 市
     */
    protected $city_id = 0;

    /**
     * @var int 区
     */
    protected $district_id = 0;

    /**
     * @var int 类型
     */
    protected $type = 0;

    /**
     * 查询地区ID和名称
     *
     * @param array $default
     * @param int $uid 手机登录会员ID
     * @return array|bool|\Illuminate\Cache\CacheManager|mixed
     * @throws \Exception
     */
    public function getAreaInfo($default = [], $uid = 0)
    {
        if (empty($default)) {
            $area_cookie = $this->areaCookie();

            $this->province_id = $area_cookie['province'];
            $this->city_id = $area_cookie['city'];
        } else {
            $this->province_id = $default['province_id'];
            $this->city_id = $default['city_id'];
        }

        $cache_name = $this->getCacheName('area_info', $uid);
        $list = cache($cache_name);

        if (is_null($list)) {
            if ($this->type == 1) {
                $region = RegionWarehouse::where('region_id', $this->province_id);
            } else {
                $region = RegionWarehouse::where('regionId', $this->province_id);
            }

            $row = BaseRepository::getToArrayFirst($region);

            if ($this->type == 1 && $row && $row['parent_id'] != 0) {
                $warehouse_name = RegionWarehouse::where('region_id', $row['parent_id'])->value('region_name');

                $row['area_name'] = $row['region_name'];
                $row['region_name'] = $warehouse_name;
            }

            $region_id = RegionWarehouse::where('regionId', $this->province_id)->value('parent_id');
            $region_id = $region_id ? $region_id : 0;

            $city_id = RegionWarehouse::where('regionId', $this->city_id)->value('region_id');
            $city_id = $city_id ? $city_id : 0;

            /* 商品选择仓库 */
            $goods_warehouse = $this->getGoodsSelectWarehouse();

            if ($goods_warehouse > 0) {
                $region_id = $goods_warehouse;
            }

            $arr = [
                'warehouse_id' => $region_id,
                'area_id' => isset($row['region_id']) ? $row['region_id'] : 0,
                'city_id' => $city_id ? $city_id : 0
            ];

            $list = [
                'row' => $row,
                'area' => $arr
            ];

            cache()->forever($cache_name, $list);
        }

        return $list;
    }

    /**
     * 查询仓库下的省、直辖市区
     * @param string $type
     * @param int $ra_id
     * @return array
     */
    public function getWarehouseProvince($type = 'root', $ra_id = 0)
    {
        $res = Region::selectRaw('region_id, region_name')->where('region_type', 1)->orderBy('region_id')->get();
        $res = $res ? $res->toArray() : [];

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$key]['region_id'] = $row['region_id'];
                $arr[$key]['region_name'] = $row['region_name'];

                if ($type == 'admin') {
                    $ms = MerchantsRegionInfo::where('region_id', $row['region_id']);

                    if ($ra_id > 0) {
                        $ms = $ms->where('ra_id', '<>', $ra_id);
                    }

                    $cant_region_id = $ms->value('region_id');

                    if ($cant_region_id > 0) {
                        $arr[$key]['disabled'] = 1;
                    } else {
                        $arr[$key]['disabled'] = 0;
                    }
                    $is_checked = MerchantsRegionInfo::where('region_id', $row['region_id'])->value('region_id');

                    if ($ra_id && $is_checked > 0) {
                        $arr[$key]['checked'] = 1;
                    } else {
                        $arr[$key]['checked'] = 0;
                    }
                }
            }
        }

        return $arr;
    }

    /**
     * 地区
     * @param int $parent_id
     * @return array
     */
    public function getRegionCityCounty($parent_id = 0)
    {
        $res = Region::where('parent_id', $parent_id)
            ->orderBy('region_id');

        $res = $res->get();

        $res = $res ? $res->toArray() : [];

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$key]['region_id'] = $row['region_id'];
                $arr[$key]['region_name'] = $row['region_name'];
            }
        }

        return $arr;
    }

    /**
     * 获得指定国家的所有省份
     * @param int $type
     * @param int $parent
     * @return array
     */
    public function getRegionsLog($type = 0, $parent = 0)
    {
        $region_list = Region::where('region_type', $type)->where('parent_id', $parent)->orderBy('region_id')->get();

        $region_list = $region_list ? $region_list->toArray() : [];

        return $region_list;
    }

    /**
     * 获得地区数量
     * @param array $where
     * @return mixed
     */
    public function getRegionParentCount($where = [])
    {
        $res = Region::whereRaw(1);

        if (isset($where['city'])) {
            $res = $res->where('parent_id', $where['city']);
        }

        if (isset($where['district'])) {
            $res = $res->where('parent_id', $where['district']);
        }

        $count = $res->count();

        return $count;
    }

    /**
     * 根据IP获取用户所在地区
     * @param int $ip
     * @return array
     * @throws \Exception
     */
    public function ipAreaName($ip = 0)
    {
        if (empty($ip)) {
            /* 获取当前用户的ip */
            $ip = request()->getClientIp();
        }

        $arrIp = $ip ? explode('.', $ip) : [];

        if (count($arrIp) != 4) {
            $ip = '58.24.104.222';
        }

        if (config('shop.ip_type') == 1) {
            $area = $this->qqApi($ip);
        } else {
            $area = $this->ipNet($ip);
        }

        return $area;
    }

    /**
     * 腾讯API
     * @param int $ip
     * @return array
     */
    private function qqApi($ip = 0)
    {
        $key = !empty(config('shop.tengxun_key')) ? config('shop.tengxun_key') : 0;
        $url = "https://apis.map.qq.com/ws/location/v1/ip?ip=" . $ip . "&key=" . $key;
        $data = Http::doGet($url);

        if (empty($data)) {
            $data = file_get_contents($url);
        }

        $str = dsc_decode($data, true);

        $null = 0;

        if (!is_array($str) || $ip == '127.0.0.1' || $str['status'] == 110 || $str['status'] == 311) {
            $null = 1;
        }

        if ((!isset($str['result']['ad_info']['province']) || empty($str['result']['ad_info']['province'])) || (!isset($str['result']['ad_info']['city']) || empty($str['result']['ad_info']['city']))) {
            $null = 2;
        }

        if ($null > 0) {
            if ($null == 3 && !empty(config('shop.shop_city'))) {
                $ip_province = $this->shopAddress(config('shop.shop_province'));
                $ip_city = $this->shopAddress(config('shop.shop_city'));

                $str['result']['ad_info'] = ['province' => $ip_province, 'city' => $ip_city];
            } else {
                $str['result']['ad_info'] = ['province' => "上海市", 'city' => "上海市"];
            }
        }

        $county = $str['result']['ad_info']['nation'] ?? '中国';
        $province = $str['result']['ad_info']['province'] ?? '';
        $city = $str['result']['ad_info']['city'] ?? '';

        $area = [
            'county' => $county,
            'province' => $province,
            'city' => $city,
        ];

        $area = $this->regroupArea($area);

        return $area;
    }

    /**
     * 解析IP
     *
     * @param string $ip
     * @return array
     * @throws \Exception
     */
    private function parseIp($ip = '255.255.255.0')
    {
        $ipType = config('app.ip_type');

        if (!empty($ipType)) {
            $data = $this->parseIpDb($ip);
        } else {
            $data = $this->parseIpDat($ip);
        }

        $arr = [];
        if ($data) {
            if (isset($data[2]) && !empty($data[2])) {
                $arr['province'] = $data[1];
                $arr['city'] = $data[2];
            } elseif (isset($data[1]) && !empty($data[1])) {
                $arr['province'] = $data[1];
                $arr['city'] = 0;
            }

            $arr['county'] = $data[0] ?? '中国';
        }

        return $arr;
    }

    /**
     * IP数据库 dat 类型
     *
     * @param $ip
     * @return array
     * @throws \Exception
     */
    private function parseIpDat($ip)
    {
        $path = resource_path('codetable/ipdata.dat');

        $bs = new City($path); // 城市级数据库

        $data = $bs->find($ip);

        return $data;
    }

    /**
     * IP数据库 db 类型
     *
     * @param $ip
     * @return array
     * @throws \Exception
     */
    private function parseIpDb($ip)
    {
        $path = resource_path('codetable/ipdata.ipdb');

        $bs = new DbCity($path); // 城市级数据库

        $data = $bs->find($ip, 'CN');

        return $data;
    }

    /**
     * 本地ip库
     * @param $ip
     * @return array
     * @throws \Exception
     */
    private function ipNet($ip)
    {
        $arr = $this->parseIp($ip);
        $area = $this->regroupArea($arr);

        return $area;
    }

    /**
     * 重组地区
     * @param $arr
     * @return array
     */
    public function regroupArea($arr)
    {
        $city = '';
        if ($arr && $arr['county'] == '中国') {
            $county = $arr['county'];
            // 四大直辖市、两大特别行政区
            $provinceList1 = [
                '北京',
                '天津',
                '上海',
                '重庆',
                '香港',
                '澳门'
            ];
            // 五大自治区
            $provinceList2 = [
                '西藏',
                '宁夏',
                '新疆',
                '内蒙古',
                '广西'
            ];

            $provinceList = array_merge($provinceList1, $provinceList2);

            //省级或特别行政区
            if (!strstr($arr['province'], '省') && !in_array($arr['province'], $provinceList)) {
                $province = $arr['province'] . "省";
            } else {
                if (in_array($arr['province'], $provinceList2)) {
                    $province = $arr['province'] . "自治区";
                } else {
                    if (!strstr($arr['province'], '市')) {
                        $province = $arr['province'] . "市";
                    }
                }
            }

            //市/县级
            if (!strstr($arr['city'], '市')) {
                $city = $arr['city'] . "市";
            } else {
                $city = $arr['city'];
            }
        }

        if (empty($city)) {
            $county = '中国';
            $province = '上海市';
            $city = '上海市';
        }

        $area = [
            'county' => $county ?? '',
            'province' => $province ?? '',
            'city' => $city,
        ];

        return $area;
    }

    /**
     * 获取商店地区
     * @param $region_id
     * @return mixed
     */
    private function shopAddress($region_id)
    {
        return Region::where('region_id', $region_id)->value('region_name');
    }

    /**
     * 根据定位信息查询ID值
     * @param int $ip
     * @return array
     * @throws \Exception
     */
    public function selectAreaInfo($ip = 0)
    {
        /* IP定位 */
        $areaInfo = $this->ipAreaName($ip);

        $province_id = Region::where('region_type', 1);

        $province_type = 0;
        if ($areaInfo['province']) {
            if (strpos($areaInfo['province'], '新疆') !== false) {
                $province = '新疆';
            } elseif (strpos($areaInfo['province'], '西藏') !== false) {
                $province = '西藏';
            } elseif (strpos($areaInfo['province'], '青海') !== false) {
                $province = '青海';
            } elseif (strpos($areaInfo['province'], '内蒙古') !== false) {
                $province = '内蒙古';
            } elseif (strpos($areaInfo['province'], '宁夏') !== false) {
                $province = '宁夏';
            } elseif (strpos($areaInfo['province'], '广西') !== false) {
                $province = '广西';
            } else {
                $province = str_replace(['省', '市'], '', $areaInfo['province']);
            }

            $where = [
                'areaInfo' => $areaInfo,
                'province' => $province
            ];
            $province_id = $province_id->where(function ($query) use ($where) {
                $query->where('region_name', $where['areaInfo']['province'])
                    ->orWhere('region_name', $where['province']);
            });
        } else {
            $province_id = $province_id->where('parent_id', 0);
            $province_type = 1;
        }

        $province_id = $province_id->value('region_id');
        $province_id = $province_id ? $province_id : 0;

        $city_id = Region::where('region_type', 2);

        $city = $areaInfo['city'] ? str_replace('市', '', $areaInfo['city']) : '';

        if (!empty($city) && $province_type == 0) {
            $where = [
                'areaInfo' => $areaInfo,
                'city' => $city
            ];

            $city_id = $city_id->where(function ($query) use ($where) {
                $query->where('region_name', $where['areaInfo']['city'])
                    ->orWhere('region_name', $where['city']);
            });
        } else {
            $city_id = $city_id->where('parent_id', $province_id);
        }

        $city_id = $city_id->value('region_id');
        $city_id = $city_id ? $city_id : 0;

        $district_id = Region::where('parent_id', $city_id)
            ->where('region_type', 3)
            ->value('region_id');
        $district_id = $district_id ? $district_id : 0;

        $arr = [
            'province_id' => $province_id,
            'city_id' => $city_id,
            'district_id' => $district_id,
        ];

        return $arr;
    }

    /**
     * 获取-浏览用户/会员-地区缓存名称
     *
     * @param string $name
     * @param int $uid
     * @return string
     * @throws \Exception
     */
    public function getCacheName($name = '', $uid = 0)
    {
        $user_id = session()->has('user_id') && session('user_id') ? intval(session('user_id')) : $uid;

        // 客户端设备唯一ID
        $session_id = request()->header('X-Client-Hash');

        if (is_null($session_id) || empty($session_id)) {
            $session_id = session()->getId();
        }

        $full_name = $name . "_";

        if ($user_id) {
            if ($name == 'area_cookie') {
                // 会员登录后 取登录前cache 存至新cache
                $area_cookie_cache = cache($full_name . $session_id);
                if (!is_null($area_cookie_cache)) {
                    cache()->forever($full_name . $user_id, $area_cookie_cache);
                }
            }

            $cache_name = $full_name . $user_id;
            cache()->forget($full_name . $session_id);
        } else {
            $cache_name = $full_name . $session_id;
        }

        return $cache_name;
    }

    /**
     * 删除-浏览用户/会员-地区缓存名称
     *
     * @param string $name
     * @param int $uid
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getCacheNameForget($name = '', $uid = 0)
    {
        $user_id = session()->has('user_id') && session('user_id') ? intval(session('user_id')) : $uid;

        // 客户端设备唯一ID
        $session_id = request()->header('X-Client-Hash');

        if (is_null($session_id) || empty($session_id)) {
            $session_id = session()->getId();
        }

        $name = $name . "_";

        if ($user_id) {
            $cache_name = $name . $user_id;
        } else {
            $cache_name = $name . $session_id;
        }

        if (cache()->has($cache_name)) {
            cache()->forget($cache_name);
        }

        return true;
    }

    /**
     * 获取地区地位缓存ID信息
     *
     * @return array|\Illuminate\Cache\CacheManager|mixed
     * @throws \Exception
     */
    public function areaCookie()
    {
        $area_cache_name = $this->getCacheName('area_cookie');
        $area_cookie = cache($area_cache_name);
        $area_cookie = !is_null($area_cookie) ? $area_cookie : [];

        if (!empty($area_cookie)) {
            $area_cookie['province'] = $area_cookie['province'] ?? 0;
            $area_cookie['city'] = $area_cookie['city_id'] ?? 0;
            $area_cookie['district'] = $area_cookie['district'] ?? 0;
            $area_cookie['street'] = $area_cookie['street'] ?? 0;
            $area_cookie['street_list'] = $area_cookie['street_list'] ?? 0;
        }

        return $area_cookie;
    }

    /**
     * 商品详情选择仓库
     *
     * @return \Illuminate\Cache\CacheManager|int|mixed
     * @throws \Exception
     */
    public function getGoodsSelectWarehouse()
    {
        $cache_name = $this->getCacheName('warehouse_id');
        $warehouse_id = cache($cache_name);
        $warehouse_id = !is_null($warehouse_id) ? $warehouse_id : 0;

        return $warehouse_id;
    }

    /**
     * 重新生成仓库地区信息缓存
     *
     * @param int $uid
     * @param int $province
     * @param int $city
     * @param int $district
     * @param int $street
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setWarehouseCache($uid = 0, $province = 0, $city = 0, $district = 0, $street = 0)
    {
        /* 删除缓存 */
        $this->getCacheNameForget('area_cookie', $uid);
        $this->getCacheNameForget('area_info', $uid);

        /* 缓存地区 */
        $area_cache_name = $this->getCacheName('area_cookie', $uid);
        $area_cookie_cache = [
            'province' => $province,
            'city_id' => $city,
            'city' => $city,
            'district' => $district,
            'street' => $street
        ];
        cache()->forever($area_cache_name, $area_cookie_cache);

        /**
         * @param $warehouse_id 仓库ID
         * @param $area_id 省份ID
         * @param $area_city 城市ID
         */
        $areaOther = [
            'province_id' => $province,
            'city_id' => $city,
        ];
        $areaInfo = $this->getAreaInfo($areaOther, $uid);

        $warehouse_id = $areaInfo['area']['warehouse_id'];
        $area_id = $areaInfo['area']['area_id'];
        $area_city = $areaInfo['area']['city_id'];

        /* 缓存仓库信息 */
        $warehouse_cache_name = $this->getCacheName('warehouse_cookie', $uid);
        $warehouse_cookie_cache = [
            'warehouse_id' => $warehouse_id,
            'area_id' => $area_id,
            'area_city' => $area_city
        ];

        cache()->forever($warehouse_cache_name, $warehouse_cookie_cache);

        return $warehouse_cookie_cache;
    }

    /**
     * IP库的版本时间
     *
     * @param string $ip
     * @return mixed|string
     * @throws \Exception
     */
    public function dscIpdate($ip = '')
    {
        $ipType = config('app.ip_type');

        if (!empty($ipType)) {
            $data = $this->parseIpDb($ip);
        } else {
            $data = $this->parseIpDat($ip);
        }

        $time = $data[1] ?? IPDATA;

        return $time;
    }
}
