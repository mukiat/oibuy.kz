<?php

namespace App\Proxy;

use App\Repositories\Common\ArrRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * 身份证实名认证
 * Class IdentityAuthProxy
 * @package App\Proxy
 */
class IdentityAuthProxy
{
    public $resultCode = null;
    public $resultMessage = '';

    protected $config;

    /**
     * IdentityAuthProxy constructor.
     */
    public function __construct()
    {
        $this->config = config('services.alicloud');
    }

    public static function getApi($val = 0)
    {
        $list = [
//            [
//                'name' => '阿里云自营',
//                'value' => 1,
//                'url' => 'https://safrvcert.market.alicloudapi.com/safrv_2meta_id_name',
//                'web_site' => 'https://market.aliyun.com/products/57000002/cmapi029454.html#sku=yuncode2345400002',
//            ],
            [
                'name' => '上海懿夕',
                'value' => 2,
                'url' => 'https://yxidcard.market.alicloudapi.com/idcard',
                'web_site' => 'https://market.aliyun.com/products/57000002/cmapi031844.html#sku=yuncode2584400001',
            ],
            [
                'name' => '昆明秀派',
                'value' => 3,
                'url' => 'http://idcard3.market.alicloudapi.com/idcardAudit',
                'web_site' => 'https://market.aliyun.com/products/57000002/cmapi015837.html#sku=yuncode983700004',
            ],
            [
                'name' => '四川涪擎',
                'value' => 4,
                'url' => 'https://naidcard.market.alicloudapi.com/nidCard',
                'web_site' => 'https://market.aliyun.com/products/57000002/cmapi028649.html#sku=yuncode2264900001'
            ]
        ];

        if ($val > 0) {
            $list = Arr::where($list, function ($value, $key) use ($val) {
                return $value['value'] == $val;
            });
            return Arr::collapse($list);
        }

        return $list;
    }

    /**
     * 验证
     *
     * @param string $userName
     * @param string $identifyNum
     * @param array $extend
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function checkIdentity($userName = '', $identifyNum = '', $extend = [])
    {
        if (!empty($this->config)) {
            $identity_auth_provider = config('shop.identity_auth_provider', 2);
            $api = self::getApi($identity_auth_provider);
            $api_url = $api['url'] ?? '';

            if (empty($api_url)) {
                return false;
            }

            if ($identity_auth_provider == 1) {
                // 官方自营
                return $this->apiQuery($api_url, $userName, $identifyNum, $extend);
            } elseif ($identity_auth_provider == 2) {
                // 上海懿夕
                return $this->apiQueryYx($api_url, $userName, $identifyNum, $extend);
            } elseif ($identity_auth_provider == 3) {
                // 昆明秀派
                return $this->apiQueryXiupai($api_url, $userName, $identifyNum, $extend);
            } elseif ($identity_auth_provider == 4) {
                // 四川涪擎
                return $this->apiQueryFegine($api_url, $userName, $identifyNum, $extend);
            }
        }

        return false;
    }

    /**
     * API query
     * 服务商：上海懿夕网络科技有限公司 接口之家 身份证实名认证_身份证二要素一致性验证_身份证实名核验
     * https://market.aliyun.com/products/57000002/cmapi031844.html#sku=yuncode2584400002
     *
     * @param string $api_url
     * @param string $userName
     * @param string $identifyNum
     * @param array $extend
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function apiQueryYx($api_url = '', $userName = '', $identifyNum = '', $extend = [])
    {
        if (empty($api_url)) {
            $api_url = 'https://yxidcard.market.alicloudapi.com/idcard';
        }

        if (empty($userName) || empty($identifyNum)) {
            return false;
        }

        $method = 'GET';

        $request['realname'] = $userName;
        $request['idcard'] = $identifyNum;

        // 合并请求参数
        if (!empty($extend)) {
            $request = array_merge($request, $extend);
        }

        $respond = self::request($api_url, $request, $method, $this->config['appCode']);
        if ($respond) {
            if (config('app.debug')) {
                Log::info($respond);
            }
            if ($respond['code'] == '200') {
                // 实名认证通过
                return true;
            }
        }

        return false;
    }

    /**
     * API query
     * 服务商：昆明秀派科技有限公司 身份证实名校验-身份证二要素核验身份证一身份证查询API
     * https://market.aliyun.com/products/57000002/cmapi015837.html?#sku=yuncode983700006
     *
     * @param string $api_url
     * @param string $userName
     * @param string $identifyNum
     * @param array $extend
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function apiQueryXiupai($api_url = '', $userName = '', $identifyNum = '', $extend = [])
    {
        if (empty($api_url)) {
            $api_url = 'http://idcard3.market.alicloudapi.com/idcardAudit';
        }

        if (empty($userName) || empty($identifyNum)) {
            return false;
        }

        $method = 'GET';

        $request['name'] = $userName;
        $request['idcard'] = $identifyNum;

        // 合并请求参数
        if (!empty($extend)) {
            $request = array_merge($request, $extend);
        }

        $respond = self::request($api_url, $request, $method, $this->config['appCode']);
        if ($respond) {
            if (config('app.debug')) {
                Log::info($respond);
            }
            if (isset($respond['showapi_res_code']) && $respond['showapi_res_code'] == '0') {
                // 实名认证通过
                $code = $respond['showapi_res_body']['code'] ?? 1; // 0 匹配、 1 不匹配、 2 无此身份证号
                if ($code == 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * API query 参数
     * 服务商：阿里云盾身份认证
     * https://market.aliyun.com/products/57000002/cmapi029454.html?#sku=yuncode23454000014
     *
     * @param string $api_url
     * @param string $userName
     * @param string $identifyNum
     * @param array $extend
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function apiQuery($api_url = '', $userName = '', $identifyNum = '', $extend = [])
    {
        if (empty($api_url)) {
            $api_url = 'https://safrvcert.market.alicloudapi.com/safrv_2meta_id_name';
        }

        if (empty($userName) || empty($identifyNum)) {
            return false;
        }

        $method = 'GET';

        $request['userName'] = $userName;
        $request['identifyNum'] = $identifyNum;

        // 请求参数
        $extend = [
            '__userId' => 'userId',
            'customerID' => $customerID ?? '123', // 可选 客户自己的userid，只做透传
            'verifyKey' => 'verifyKey',
        ];

        // 合并请求参数
        if (!empty($extend)) {
            $request = array_merge($request, $extend);
        }

        $respond = self::request($api_url, $request, $method, $this->config['appCode']);
        if ($respond) {
            if (config('app.debug')) {
                Log::info($respond);
            }
            if ($respond['code'] == '200') {
                // 实名认证通过
                return true;
            }
        }

        return false;
    }

    /**
     * API query 参数
     * 服务商：四川涪擎大数据技术有限公司 身份证认证-身份证二要素核验-身份证一致性验证-身份证实名认证
     * https://market.aliyun.com/products/57000002/cmapi028649.html?#sku=yuncode2264900001
     *
     * @param string $api_url
     * @param string $userName
     * @param string $identifyNum
     * @param array $extend
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function apiQueryFegine($api_url = '', $userName = '', $identifyNum = '', $extend = [])
    {
        if (empty($api_url)) {
            $api_url = 'https://naidcard.market.alicloudapi.com/nidCard';
        }

        if (empty($userName) || empty($identifyNum)) {
            return false;
        }

        $method = 'GET';

        $request = [
            'name' => $userName,
            'idCard' => $identifyNum,
        ];

        // 合并请求参数
        if (!empty($extend)) {
            $request = array_merge($request, $extend);
        }

        $respond = self::request($api_url, $request, $method, $this->config['appCode']);
        if ($respond) {
            if (config('app.debug')) {
                Log::info($respond);
            }
            if ($respond['status'] == '01') {
                // 实名认证通过
                return true;
            }
        }

        return false;
    }

    /**
     * 使用 AppCode 调用（简单身份认证）
     *
     * @param string $api
     * @param array $request
     * @param string $method
     * @param string $appcode
     * @return bool|\mix|mixed|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($api = '', $request = [], $method = 'POST', $appcode = '')
    {
        if (empty($request)) {
            return false;
        }

        // 自定义header
        $configs['headers'] = [
            // 使用 AppCode 调用（简单身份认证）请求Header中添加的Authorization字段；配置Authorization字段的值为“APPCODE ＋ 半角空格 ＋APPCODE值”。
            'Authorization' => 'APPCODE ' . $appcode,
        ];
        // 跳过证书检查
        $configs['verify'] = false;

        $respond = self::httpRequest($api, $request, $method, $configs, 'json');

        if ($respond) {
            if ($respond['status'] === false || $respond['status'] == 'error') {
                $this->resultCode = $respond['errorCode'];
                $this->resultMessage = $respond['errorMessage'];
                return false;
            } else {
                $result = $respond['data'];
                // 状态码，200为成功，其余为失败
                if (isset($result['code']) && $result['code'] != '200') {
                    $this->resultCode = $result['code'];

                    if (isset($result['msg'])) {
                        $this->resultMessage = $result['msg'] ?? '';
                    } else {
                        $this->resultMessage = $result['message'] ?? '';
                    }
                    Log::info($result);
                    return false;
                }
            }

            return is_string($result) ? json_decode($result, true) : $result;
        }

        return false;
    }

    /**
     * 发送请求  GET / POST
     *
     * https://guzzle-cn.readthedocs.io/zh_CN/latest/quickstart.html
     *
     * @param string $url
     * @param array $params
     * @param string $method
     * @param array $configs
     * @param string $contentType form_params | json
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function httpRequest(string $url, array $params = [], string $method = 'POST', array $configs = [], string $contentType = 'form_params')
    {
        try {
            $configs['timeout'] = ArrRepository::get($configs, 'timeout', 60);

            $method = strtoupper($method);
            $params = $method == 'GET' ? ['query' => $params] : [$contentType => $params];

            $client = new Client($configs);

            $resp = $client->request($method, $url, $params);
        } catch (RequestException $exception) {
            $errorCode = $exception->getCode();
            $errorMessage = $exception->getMessage();

            return [
                'status' => false,
                'errorCode' => $errorCode,
                'errorMessage' => $errorMessage,
            ];
        }

        $httpCode = $resp->getStatusCode();
        $return = $resp->getBody()->getContents();

        $success = $httpCode == 200 ? 'success' : 'error';

        if ($httpCode != 200) {
            return [
                'status' => $success,
                'errorCode' => $httpCode,
                'errorMessage' => '',
            ];
        }

        $response = json_decode($return, true, 512, JSON_BIGINT_AS_STRING); //JSON_BIGINT_AS_STRING 用于将大整数转为字符串而非默认的float类型

        // Laravel 开启 debug
        if (config('app.debug')) {
            // 记录日志
            Log::info("请求url：", ['url' => $url]);
            Log::info("请求应用参数：", ['params' => $params]);
            Log::info("返回：", ['response' => $response]);
        }

        return [
            'status' => $success,
            'data' => $response
        ];
    }
}
