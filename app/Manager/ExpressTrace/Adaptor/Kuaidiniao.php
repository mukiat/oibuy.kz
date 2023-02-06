<?php

namespace App\Manager\ExpressTrace\Adaptor;

use App\Manager\ExpressTrace\Contract\TraceInterface;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;

/**
 * Class Kuaidiniao
 * @package App\Manager\ExpressTrace\Adaptor
 */
class Kuaidiniao implements TraceInterface
{
    /**
     * @link http://www.kdniao.com/api-all
     * @var string
     */
    private $gateway = 'https://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx';
    private $sandbox_gateway = 'http://sandboxapi.kdniao.com:8080/kdniaosandbox/gateway/exterfaceInvoke.json';

    /**
     * @var array
     */
    protected $config = [
        'customer' => '',
        'key' => '',
        'sandbox' => false
    ];

    /**
     * Kuaidiniao constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 即时查询API
     * @link http://www.kdniao.com/api-track
     * @param string $com
     * @param string $num
     * @param array $payload
     * @return array
     * @throws Exception
     */
    public function query($com = '', $num = '', $payload = [])
    {
        $requestData = $this->format_request($com, $num, $payload);
        if (!$requestData) {
            return ['error' => 1, 'data' => lang('admin/common.parameter_error')];
        }

        $params = [
            'RequestData' => urlencode($requestData),
            'EBusinessID' => $this->config['customer'],
            'RequestType' => '1002', //免费即时查询接口指令1002
            'DataType' => '2'
        ];
        $params['DataSign'] = $this->encrypt($requestData, $this->config['key']);

        $result = $this->request($params);

        // 返回数据
        if (isset($result['Success']) && $result['Success']) {
            $result = $this->format_return($result);
            return [
                'error' => 0,
                'data' => [
                    'state' => $this->getState($result['state']),
                    'traces' => $result['data'],
                ]
            ];
        } else {
            return ['error' => 1, 'data' => $result['Reason'] ?? ''];
        }
    }

    /**
     * 快递查询API
     * @link http://www.kdniao.com/api-trackexpress
     * @param string $com
     * @param string $num
     * @param array $payload
     */
    public function expressTrack($com = '', $num = '', $payload = [])
    {
        $requestData = $this->format_request($com, $num, $payload);
        if (!$requestData) {
            return ['error' => 1, 'data' => lang('admin/common.parameter_error')];
        }

        $params = [
            'RequestData' => urlencode($requestData),
            'EBusinessID' => $this->config['customer'],
            'RequestType' => '8002', // 快递查询API接口指令8002
            'DataType' => '2'
        ];
        $params['DataSign'] = $this->encrypt($requestData, $this->config['key']);

        $result = $this->request($params);

        // 返回数据
        if (isset($result['Success']) && $result['Success']) {
            $result = $this->format_return($result);
            return [
                'error' => 0,
                'data' => [
                    'state' => $this->getState($result['state']),
                    'traces' => $result['data'],
                ]
            ];
        } else {
            return ['error' => 1, 'data' => $result['Reason'] ?? ''];
        }
    }

    /**
     * 物流轨迹地图API
     * @link http://www.kdniao.com/api-MapTrack
     * @param string $com 快递公司
     * @param string $num 快递单号
     * @param array $payload 附加数据
     * @return array
     * @throws Exception
     */
    public function mapTrack($com = '', $num = '', $payload = [])
    {
        $shipperCode = $this->get_express_code($com);
        if (is_null($shipperCode)) {
            $shipperCode = strtoupper($com);
        }

        $requestData = [
            'ShipperCode' => $shipperCode, // 快递公司编码
            'LogisticCode' => $num, // 物流单号
            'SenderCityName' => $payload['from'], // 发件城市
            'ReceiverCityName' => $payload['to'], // 收件城市
            'IsReturnRouteMap' => 1, // 是否返回轨迹地图 1 返回 2 不返回 默认1
        ];

        if ($shipperCode == 'SF') {
            $requestData['CustomerName'] = isset($payload['mobile']) ? substr($payload['mobile'], -4) : '';
        }

        $requestData = json_encode($requestData);

        $params = [
            'RequestData' => urlencode($requestData), // 请求内容需进行URL(utf-8)编码。请求内容JSON格式，须和DataType一致。
            'EBusinessID' => $this->config['customer'], // 商户ID，请在我的服务页面查看。
            'RequestType' => '8003', // 地图版即时查询接口指令8003
            'DataType' => '2',
        ];

        $params['DataSign'] = $this->encrypt($requestData, $this->config['key']);

        $result = $this->request($params);

        // 返回数据
        if (isset($result['Success']) && $result['Success']) {
            return ['error' => 0, 'data' => $result['RouteMapUrl'] ?? ''];
        } else {
            return ['error' => 1, 'data' => $result['Reason'] ?? ''];
        }
    }

    /**
     * @param $params
     * @return array|mixed|null
     * @throws Exception
     */
    protected function request($params)
    {
        if ($this->config['sandbox'] === true) {
            $url = $this->sandbox_gateway;
        } else {
            $url = $this->gateway;
        }

        // 缓存优先，否则请求API获取数据
        $cache_id = md5($url . http_build_query($params, '', '&'));
        $result = cache($cache_id);
        if (is_null($result)) {
            try {
                $client = new Client();
                $response = $client->post($url, ['verify' => false, 'form_params' => $params]);
            } catch (Exception $exception) {
                return ['Success' => false, 'Reason' => $exception->getMessage() ?? ''];
            }

            $content = str_replace("\"", '"', $response->getBody()->getContents());
            $result = json_decode($content, true);

            cache([$cache_id => $result], Carbon::now()->addHours(1));
        }

        return $result;
    }

    /**
     * 格式化返回数据
     * @param array $data 数据
     * @return array data
     */
    protected function format_return($data = [])
    {
        if ($data['Success'] === true) {
            $Traces_format = [];
            if ($data['Traces']) {
                foreach ($data['Traces'] as $trace) {
                    $Traces_format[] = [
                        'time' => $trace['AcceptTime'] ?? '',
                        'ftime' => $trace['AcceptTime'] ?? '',
                        'context' => $trace['AcceptStation'] ?? '',
                    ];
                }
            }
            return [
                'nu' => $data['LogisticCode'],
                'com' => $this->get_express_code($data['ShipperCode'], 1),
                'status' => '200',
                'state' => $data['State'],
                'data' => $Traces_format,
            ];
        }

        return $data;
    }

    /**
     * 格式化请求参数
     * @param string $com 快递公司
     * @param string $num 快递单号
     * @param array $payload 附加数据
     * @return false|string
     */
    protected function format_request($com = '', $num = '', $payload = [])
    {
        if (!isset($com) || !isset($num)) {
            return false;
        }

        $shipperCode = $this->get_express_code($com);
        if (is_null($shipperCode)) {
            $shipperCode = strtoupper($com);
        }

        $data = [
            "OrderCode" => '',
            "ShipperCode" => $shipperCode,
            "LogisticCode" => $num
        ];

        if ($shipperCode == 'SF') {
            $data['CustomerName'] = isset($payload['mobile']) ? substr($payload['mobile'], -4) : '';
        }

        return json_encode($data);
    }

    /**
     * 电商Sign签名生成
     */
    protected static function encrypt($data, $appkey)
    {
        return urlencode(base64_encode(md5($data . $appkey)));
    }

    /**
     * 获取快递公司简称
     * @param string $shipper_company
     * @param int $is_standard 是否获取标准快递代码，反转数组
     * @return mixed
     */
    protected static function get_express_code($shipper_company, $is_standard = 0)
    {
        $express_code_kdniao = array(
            'huitongkuaidi' => 'HTKY',//百世快递百世汇通
            'ems' => 'EMS',
            'shentong' => 'STO', //申通
            'shunfeng' => 'SF', //顺丰
            'tiantian' => 'HHTT',//天天快递 海航天天
            'yuantong' => 'YTO', //圆通
            'yunda' => 'YD',//韵达
            'zhongtong' => 'ZTO',//中通
            'quanfengkuaidi' => 'QFKD',// 全峰
            'guotongkuaidi' => 'GTO', //国通
            'youshuwuliu' => 'UC',//优速快递
            'debangwuliu' => 'DBL',//德邦
            'kuaijiesudi' => 'FAST',//快捷快递
            'zhaijisong' => 'ZJS',//宅急送
            'jingdong' => 'JD',//京东
            'youzhengguonei' => 'YZPY',//邮政包裹，邮政平邮
            'yangbaoguo' => 'YBG',//洋包裹
            'anneng' => 'ANE',//安能物流
            'badatong' => 'BDT',//八达通
        );

        if ($is_standard) {
            $express_code_kdniao = array_flip($express_code_kdniao);
        }

        return $express_code_kdniao[$shipper_company];
    }

    /**
     * @param null $state
     * @return string
     */
    protected static function getState($state = null)
    {
        $states = [
            0 => '暂无轨迹信息',
            1 => '已揽收',
            2 => '在途中',
            3 => '签收',
            4 => '问题件',
        ];

        return isset($states[$state]) ? $states[$state] : 'Unknown';
    }
}
