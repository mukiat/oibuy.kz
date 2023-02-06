<?php

namespace App\Channels\Sms\Driver;

use App\Models\SmsTemplate;
use Illuminate\Support\Facades\Log;
use Mobizon\MobizonApi;

/**
 * Class Mobizon
 * @package App\Channels\Sms\Driver
 * @see https://mobizon.kz/help/send-sms/how-to-send-sms
 */
class Mobizon
{
    /**
     * 短信类配置
     * @var array
     */
    protected $config = [
        'sms_api_key' => '',
    ];

    /**
     * @var objcet 短信对象
     */
    protected $sms_api = "api.mobizon.kz";
    protected $content = null;
    protected $errorInfo = null;

    /**
     * 构建函数
     * @param array $config 短信配置
     */
    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 设置短信信息
     * @param string $title
     * @param array $content
     * @param array $data
     * @return $this
     */
    public function setSms($title = '', $content = [], $data = [])
    {
        $msg = SmsTemplate::where('send_time', $title)->first();
        $msg = $msg ? $msg->toArray() : [];

        if (isset($data['temp_content']) && !empty($data['temp_content'])) {
            $msg['temp_content'] = $data['temp_content'];
        } else {
            $msg['temp_content'] = $msg['temp_content'] ?? '';
        }

        // 替换消息变量
        preg_match_all('/\$\{(.*?)\}/', $msg['temp_content'], $matches);
        foreach ($matches[1] as $vo) {
            $msg['temp_content'] = str_replace('${' . $vo . '}', $content[$vo], $msg['temp_content']);
        }
        $this->content = $msg['temp_content'];

        return $this;
    }

    /**
     * 发送短信
     * @param string $mobile 收件人
     * @return boolean
     */
    public function sendSms($mobile)
    {
        $api = new MobizonApi($this->config['sms_api_key'], $this->sms_api);
        $data = array('recipient' => '+7' . $mobile, 'text' => $this->content);
        Log::info($data);
        // echo 'Send message...' . PHP_EOL;
        if ($api->call('message', 'sendSMSMessage', $data)) {
            return true;
        } else {
            $this->errorInfo = var_export(array($api->getCode(), $api->getData(), $api->getMessage()), true);
            Log::error($this->errorInfo);
            return false;
        }
    }

    /**
     * 返回错误信息
     * @return string
     */
    public function getError()
    {
        return $this->errorInfo;
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        unset($this->sms);
    }
}
