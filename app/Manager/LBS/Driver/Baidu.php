<?php

namespace App\Manager\LBS\Driver;

use App\Manager\LBS\Contract\LbsContract;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use EasyWeChat\Kernel\Traits\HasHttpRequests;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

/**
 * Class Baidu
 * @package App\Manager\LBS\Driver
 */
class Baidu implements LbsContract
{
    use HasHttpRequests;

    /**
     *
     * @link http://lbsyun.baidu.com/index.php?title=webapi
     * @var string
     */
    protected $baseUri = 'https://api.map.baidu.com/';

    /**
     * @var array
     */
    protected $defaultOptions = [
        'headers' => [
            'Host' => 'api.map.baidu.com',
            'Referer' => 'https://lbsyun.baidu.com/',
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

        // 经纬度的坐标类型
        $query['coor'] = 'bd09ll';
        $response = $this->httpGet('location/ip', $query);

        if (!empty($response)) {
            return [
                'code' => (string)$response['content']['address_detail']['city_code'] ?? '',
                'province' => $response['content']['address_detail']['province'] ?? '',
                'city' => $response['content']['address_detail']['city'] ?? '',
                'district' => $response['content']['address_detail']['district'] ?? '',
                'lat' => $response['content']['point']['y'],
                'lng' => $response['content']['point']['x'],
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
        $response = $this->httpGet('reverse_geocoding/v3', ['location' => $lat . ',' . $lng, 'ret_coordtype' => 'bd09ll', 'output' => 'json']);

        if (!empty($response)) {
            return [
                'code' => $response['result']['cityCode'] ?? '',
                'province' => $response['result']['addressComponent']['province'] ?? '',
                'city' => $response['result']['addressComponent']['city'] ?? '',
                'district' => $response['result']['addressComponent']['district'] ?? '',
                'address' => $response['result']['formatted_address'] ?? '',
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
        $response = $this->httpGet('geocoding/v3', ['address' => $address, 'ret_coordtype' => 'bd09ll', 'output' => 'json']);

        if (!empty($response)) {
            return [
                'code' => $response['result']['adcode'] ?? '',
                'province' => $response['result']['province'] ?? '',
                'city' => $response['result']['city'] ?? '',
                'district' => $response['result']['district'] ?? '',
                'lat' => $response['result']['location']['lat'],
                'lng' => $response['result']['location']['lng'],
            ];
        }

        return [];
    }

    /**
     * @param string $id
     * @return mixed
     * @throws GuzzleException
     * @throws InvalidConfigException
     */
    public function district(string $id = ''): array
    {
        $query = empty($id) ? [] : ['id' => $id];

        $response = [];//$this->httpGet('district/v1/getchildren', $query);

        $result = [];
        if (!empty($response)) {
            foreach (end($response) as $item) {
                array_push($result, [
                    'code' => $item['id'] ?? '',
                    'name' => $item['fullname'] ?? '',
                ]);
            }
        }

        return $result;
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
        $query['ak'] = $this->config['key'];

        $options = array_merge(self::getDefaultOptions(), $this->defaultOptions);
        $options = array_merge($options, ['query' => $query]);

        $responseRaw = $this->request($url, 'GET', $options);
        $response = $this->castResponseToType($responseRaw, $this->response_type);

        if ($response['status'] == 0) {
            return $response;
        }

        Log::error($response);

        return [];
    }
}
