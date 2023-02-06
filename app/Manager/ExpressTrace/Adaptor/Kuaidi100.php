<?php

namespace App\Manager\ExpressTrace\Adaptor;

use App\Manager\ExpressTrace\Contract\TraceInterface;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;

/**
 * 快递100接口
 * Class Kuaidi100
 * @package App\Manager\ExpressTrace\Adaptor
 */
class Kuaidi100 implements TraceInterface
{
    /**
     * @link https://api.kuaidi100.com/document/5f0ffa8f2977d50a94e1023c.html
     * @var string
     */
    private $gateway = 'https://poll.kuaidi100.com/poll/';

    /**
     * @var array
     */
    protected $config = [
        'customer' => '',
        'key' => '',
    ];

    /**
     * Kuaidi100 constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 实时快递查询接口
     * @link https://api.kuaidi100.com/document/5f0ffb5ebc8da837cbd8aefc.html
     * @param string $com
     * @param string $num
     * @param array $payload
     * @return array
     * @throws Exception
     */
    public function query($com = '', $num = '', $payload = [])
    {
        $result = $this->request('query.do', $com, $num, $payload);

        // 返回数据
        if (isset($result['status']) && $result['status'] === '200') {
            return [
                'error' => 0,
                'data' => [
                    'state' => $this->getState($result['state']),
                    'traces' => $result['data'],
                ]
            ];
        } else {
            return ['error' => 1, 'data' => $result['message']];
        }
    }

    /**
     * 快递查询地图轨迹
     * @link https://api.kuaidi100.com/document/5ff2c3e7ba1bf00302f5612e.html
     * @param string $com
     * @param string $num
     * @param array $payload
     * @return array
     * @throws Exception
     */
    public function mapTrack($com = '', $num = '', $payload = [])
    {
        $result = $this->request('maptrack.do', $com, $num, $payload);

        if (isset($result['status']) && $result['status'] == 200) {
            return ['error' => 0, 'data' => $result['trailUrl']];
        } else {
            return ['error' => 1, 'data' => $result['message']];
        }
    }

    /**
     * @param $uri
     * @param string $com
     * @param string $num
     * @param array $payload
     * @return array
     * @throws Exception
     */
    protected function request($uri, $com = '', $num = '', $payload = [])
    {
        $com = $this->expressAdaptor($com);

        $payload = array_merge(['com' => $com, 'num' => $num], $payload);

        // 请求参数
        $post_data["customer"] = $this->config['customer'];
        $post_data["param"] = json_encode($payload);
        $post_data["sign"] = strtoupper(md5($post_data["param"] . $this->config['key'] . $post_data["customer"]));

        // 缓存优先，否则请求API获取数据
        $cache_id = md5($this->gateway . $uri . http_build_query($post_data, '', '&'));
        $result = cache($cache_id);
        if (is_null($result)) {
            $client = new Client();
            $response = $client->post($this->gateway . $uri, ['form_params' => $post_data]);
            $content = str_replace("\"", '"', $response->getBody()->getContents());
            $result = json_decode($content, true);
            cache([$cache_id => $result], Carbon::now()->addHours(1));
        }

        return $result;
    }

    /**
     * 快递编码适配
     * @see https://api.kuaidi100.com/document/5f0ffb5ebc8da837cbd8aefc.html
     * @param string $code
     * @return mixed|string
     */
    protected static function expressAdaptor($code = '')
    {
        $express = [
            'sto_express' => 'shentong',
            'deppon' => 'debangwuliu', // 德邦物流，非德邦快递
            'ems' => 'ems',
            'sf_express' => 'shunfeng',
            'zto' => 'zhongtong',
            'huitong' => 'huitongkuaidi',
            'post_express' => 'youzhengguonei',
            'post_mail' => 'youzhengguonei',
            'presswork' => 'youzhengguonei',
            'quanfeng' => 'quanfengkuaidi',
            'zjs' => 'zhaijisong',
            'yto' => 'yuantong',
        ];

        return isset($express[$code]) ? $express[$code] : $code;
    }

    /**
     * 获取快递单在途状态
     * @param null $state
     * @return string
     */
    protected static function getState($state = null)
    {
        $states = [
            0 => '在途',
            1 => '揽收',
            2 => '疑难',
            3 => '签收',
            4 => '退签',
            5 => '派件',
            6 => '退回',
            7 => '转单',
            10 => '待清关',
            11 => '清关中',
            12 => '已清关',
            13 => '清关异常',
            14 => '收件人拒签',
        ];

        return isset($states[$state]) ? $states[$state] : 'Unknown';
    }
}
