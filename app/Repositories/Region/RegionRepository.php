<?php

namespace App\Repositories\Region;

use Illuminate\Support\Facades\DB;


class RegionRepository
{
    /**
     * 返回地区详细名称 如 xx省xx市xx区xx街道
     * @param array $region
     * @return string
     */
    public static function get_area_region_info($region = [])
    {
        if (empty($region)) {
            return '';
        }

        $province_name = '';
        if (isset($region['province']) && $region['province']) {
            $province_name = DB::table('region')->where('region_id', $region['province'])->value('region_name');
        }

        $city_name = '';
        if (isset($region['city']) && $region['city']) {
            $city_name = DB::table('region')->where('region_id', $region['city'])->value('region_name');
        }

        $district_name = '';
        if (isset($region['district']) && $region['district']) {
            $district_name = DB::table('region')->where('region_id', $region['district'])->value('region_name');
        }

        $street_name = '';
        if (isset($region['street']) && $region['street']) {
            $street_name = DB::table('region')->where('region_id', $region['street'])->value('region_name');
        }

        $province_name = $province_name ?? '';
        $city_name = $city_name ?? '';
        $district_name = $district_name ?? '';
        $street_name = $street_name ?? '';

        $region = $province_name . " " . $city_name . " " . $district_name . " " . $street_name;
        return trim($region);
    }
}