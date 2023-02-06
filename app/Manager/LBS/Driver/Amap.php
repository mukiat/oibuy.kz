<?php

namespace App\Manager\LBS\Driver;

use App\Manager\LBS\Contract\LbsContract;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use EasyWeChat\Kernel\Traits\HasHttpRequests;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

/**
 * Class Amap
 * @package App\Manager\LBS\Driver
 */
class Amap implements LbsContract
{
    use HasHttpRequests;

    /**
     * @link https://lbs.amap.com/api/webservice/summary
     * @var string
     */
    protected $baseUri = 'https://restapi.amap.com/v3/';


    /**
     * @var array
     */
    protected $defaultOptions = [
        'headers' => [
            'Host' => 'restapi.amap.com',
            'Referer' => 'https://lbs.amap.com/',
        ],
        'verify' => false, // 跳过证书检查
    ];

    /**
     * @var array
     */
    protected $config = [
        'key' => '',
    ];

    /**
     * @var string
     */
    protected $response_type = 'array';

    /**
     * TencentStore constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * @param string $ip
     * @return array
     * @throws GuzzleException
     * @throws InvalidConfigException
     */
    public function ip(string $ip): array
    {
        $query = empty($ip) ? [] : ['ip' => $ip];

        $response = $this->httpGet('ip', $query);
        if (!empty($response)) {
            // 左下右上对标对 120.8397067,30.77980118;122.1137989,31.66889673
            $rectangle = explode(';', $response['rectangle']);
            // 返回坐标中心点
            $center = self::getCenter($rectangle);

            return [
                'code' => (string)$response['adcode'],
                'province' => $response['province'],
                'city' => $response['city'],
                'district' => $response['district'] ?? '',
                'lat' => $center['lat'] ?? '',
                'lng' => $center['lng'] ?? '',
            ];
        }

        return [];
    }

    /**
     * @param string $lat
     * @param string $lng
     * @return array
     * @throws GuzzleException
     * @throws InvalidConfigException
     */
    public function location2address(string $lat = '', string $lng = ''): array
    {
        // 经度在前，纬度在后
        $response = $this->httpGet('geocode/regeo', ['location' => $lng . ',' . $lat]);

        if (!empty($response)) {
            return [
                'code' => $response['regeocode']['addressComponent']['adcode'],
                'province' => $response['regeocode']['addressComponent']['province'],
                'city' => $response['regeocode']['addressComponent']['province'],
                'district' => $response['regeocode']['addressComponent']['district'],
                'address' => $response['regeocode']['formatted_address'],
            ];
        }

        return [];
    }

    /**
     * @param string $address
     * @return array
     * @throws GuzzleException
     * @throws InvalidConfigException
     */
    public function address2location(string $address = ''): array
    {
        $response = $this->httpGet('geocode/geo', ['address' => $address]);

        if (!empty($response)) {
            list($lng, $lat) = explode(',', $response['geocodes'][0]['location']);
            return [
                'code' => $response['geocodes'][0]['adcode'],
                'province' => $response['geocodes'][0]['province'],
                'city' => $response['geocodes'][0]['city'],
                'district' => $response['geocodes'][0]['district'],
                'lat' => $lat ?? '',
                'lng' => $lng ?? '',
            ];
        }

        return [];
    }

    /**
     * @param string $keywords
     * @return mixed
     * @throws GuzzleException
     * @throws InvalidConfigException
     */
    public function district(string $keywords = ''): array
    {
        $query = empty($keywords) ? [] : ['keywords' => $keywords];

        $response = $this->httpGet('config/district', $query);

        $result = [];
        if (!empty($response)) {
            $districts = $response['districts']['0']['districts'] ?? [];
            foreach ($districts as $item) {
                array_push($result, [
                    'code' => $item['adcode'],
                    'name' => $item['name'],
                ]);
            }
        }

        return $result;
    }

    /**
     * 获取一组坐标的中心点
     *
     * 经纬度是地理坐标系，如果这些坐标点之间距离不超过400公里，可以用简单的用经度平均值和纬度平均值作为中心点，有微小误差。
     * 如果要精确计算，需要把经纬度转换为三维坐标xyz，然后计算中点（xyz的平均值）。
     *
     * @param array $rectangle 坐标集合 每一项为每一个点的：纬度,经度
     * array:2 [▼
     * 0 => "120.8397067,30.77980118"
     * 1 => "122.1137989,31.66889673"
     * ]
     * @returns array 中心点
     */
    public static function getCenter($rectangle = [])
    {
        if (empty($rectangle)) {
            return [];
        }

        $points = [];

        foreach ($rectangle as $k => $item) {
            // 经度，纬度
            list($lng, $lat) = explode(',', $item);

            $points[$k]['lng'] = $lng;
            $points[$k]['lat'] = $lat;
        }

        return self::precisionCoords($points);
    }

    /**
     * 获取一组坐标的中心点  精确计算，取经纬度转换为三维坐标xyz，然后计算中点（xyz的平均值）
     * @param array $points
     * For Example:
     * $points = array
     * (
     *   0 = > array(45.849382, 76.322333),
     *   1 = > array(45.843543, 75.324143),
     *   2 = > array(45.765744, 76.543223),
     *   3 = > array(45.784234, 74.542335)
     * );
     * @return array
     */
    protected static function precisionCoords($points = [])
    {
        if (!is_array($points)) return [];

        $num_coords = count($points);

        $X = 0.0;
        $Y = 0.0;
        $Z = 0.0;

        foreach ($points as $coord) {

            $lat = $coord['lat'] * pi() / 180;
            $lon = $coord['lng'] * pi() / 180;

            $a = cos($lat) * cos($lon);
            $b = cos($lat) * sin($lon);
            $c = sin($lat);

            $X += $a;
            $Y += $b;
            $Z += $c;
        }

        $X /= $num_coords;
        $Y /= $num_coords;
        $Z /= $num_coords;

        $lon = atan2($Y, $X);
        $lat = atan2($Z, sqrt($X * $X + $Y * $Y));

        $center['lng'] = round($lon * 180 / pi(), 6);
        $center['lat'] = round($lat * 180 / pi(), 6);

        return $center;
    }

    /**
     * GET request.
     *
     * @param string $url
     * @param array $query
     * @return array
     * @throws GuzzleException
     * @throws InvalidConfigException
     */
    protected function httpGet(string $url, array $query = []): array
    {
        $query['key'] = $this->config['key'];
        $options = array_merge(self::getDefaultOptions(), $this->defaultOptions);
        $options = array_merge($options, ['query' => $query]);
        $responseRaw = $this->request($url, 'GET', $options);
        $response = $this->castResponseToType($responseRaw, $this->response_type);

        if ($response['status'] == 1) {
            return $response;
        }

        Log::error($response['info']);

        return [];
    }
}
