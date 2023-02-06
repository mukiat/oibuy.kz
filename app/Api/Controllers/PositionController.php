<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Manager\LBS\Facades\Lbs;
use App\Models\Region;
use App\Repositories\Common\BaseRepository;
use App\Services\Common\AreaService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class PositionController
 * @package App\Api\Controllers
 */
class PositionController extends Controller
{
    private $areaService;

    public function __construct(
        AreaService $areaService
    )
    {
        $this->areaService = $areaService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function index(Request $request)
    {
        // $lat = ''; //$request->get('lat', '31.22928');
        // $lng = ''; //$request->get('lng', '121.40966');

        $lat = $request->get('lat', '');
        $lng = $request->get('lng', '');

        $region = [];
        $city_region = [];
        if (!empty($lat) && !empty($lng)) {
            $key = config('shop.tengxun_key', 'XSYBZ-P2G34-3K7UB-XPFZS-TBGHT-CXB4U');
            $data = geocoder($lat, $lng, $key);
            /**
             * 查询区县地区ID
             */
            $region = Region::where('region_name', $data['district'])->where('region_type', 3);
            $region = BaseRepository::getToArrayFirst($region);

            if (empty($region)) {
                $city_region = Region::where('region_name', $data['city'])->where('region_type', 2);
                $city_region = BaseRepository::getToArrayFirst($city_region);
            }
        }

        /**
         * 组装地区ID
         */
        if ($region || $city_region) {
            if (!empty($region)) {
                $province_id = Region::where('region_id', $region['parent_id'])->value('parent_id');

                $data['province_id'] = $province_id ? $province_id : 0;
                $data['city_id'] = $region['parent_id'];
                $data['district_id'] = $region['region_id'];
            } else {
                $data['province_id'] = $city_region['parent_id'];
                $data['city_id'] = $city_region['region_id'];
                $data['district_id'] = Region::where('parent_id', $city_region['region_id'])->where('region_type', 3)->value('region_id');
            }
        } else {
            $district = (int)config('shop.shop_district', 310107);
            $region = Region::where('region_id', $district);
            $region = BaseRepository::getToArrayFirst($region);

            $city_id = $region['parent_id'] ?? 0;
            $province_id = Region::where('region_id', $city_id)->value('parent_id');

            $province_id = $province_id ? $province_id : 0;
            $province = Region::where('region_id', $province_id)->value('region_name');
            $province = $province ? $province : '';

            $city = Region::where('region_id', $city_id)->value('region_name');
            $city = $city ? $city : '';
            $district_id = $region['region_id'];
            $district = Region::where('region_id', $district_id)->value('region_name');
            $district = $district ? $district : '';
            $street = Region::select('region_id', 'region_name')->where('parent_id', $district_id);
            $street = BaseRepository::getToArrayFirst($street);
            $street_number = '';

            $data = [
                'city' => $city,
                'city_id' => $city_id,
                'district' => $district,
                'district_id' => $district_id,
                'nation' => '中国',
                'province' => $province,
                'province_id' => $province_id,
                'street' => $street['region_name'] ?? '',
                'street_id' => $street['region_id'] ?? 0,
                'street_number' => $street_number,
            ];
        }

        return $this->succeed($data);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function ip(Request $request)
    {
        $ip = $request->get('ip', $request->getClientIp()); // 默认上海地区

        $data = $this->areaService->ipAreaName($ip);

        /**
         * 查询区县地区ID
         */
        $region = Region::where('region_name', $data['city']);
        $region = BaseRepository::getToArrayFirst($region);

        /**
         * 组装地区ID
         */
        if ($region) {
            $province_id = $region['parent_id'] ?? 0;
            $city_id = $region['region_id'] ?? 0;
            $district_id = Region::where('parent_id', $city_id)->value('region_id');
            $data['province_id'] = $province_id ?? 0;
            $data['city_id'] = $city_id ?? 0;
            $data['district_id'] = $district_id ?? 0;
        } else {
            $shop_city = (int)config('shop.shop_city', 659009504);

            $region = Region::where('region_id', $shop_city);
            $region = BaseRepository::getToArrayFirst($region);

            $province_id = $region['parent_id'] ?? 0;
            $province = Region::where('region_id', $province_id)->value('region_name');
            $province = $province ? $province : '';

            $city_id = $region['region_id'] ?? 0;
            $city = Region::where('region_id', $city_id)->value('region_name');
            $city = $city ? $city : '';

            $district_id = Region::where('parent_id', $city_id)->value('region_id');
            $district_id = $district_id ? $district_id : 0;
            $district = Region::where('region_id', $district_id)->value('region_name');
            $district = $district ? $district : '';
            $street = Region::where('parent_id', $district_id)->value('region_name');
            $street = $street ? $street : '';
            $street_number = '';

            $data = [
                'city' => $city,
                'city_id' => $city_id,
                'district' => $district,
                'district_id' => $district_id,
                'nation' => '中国',
                'province' => $province,
                'province_id' => $province_id,
                'street' => $street,
                'street_number' => $street_number,
            ];
        }

        return $this->succeed($data);
    }

    /**
     * 根据地址获取坐标
     * @param Request $request
     * @return JsonResponse
     */
    public function address2location(Request $request)
    {
        $address = $request->get('address', '');
        $location = Lbs::address2location($address);
        return $this->succeed($location);
    }
}
