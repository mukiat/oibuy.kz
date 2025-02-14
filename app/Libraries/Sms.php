<?php

namespace App\Libraries;

use App\Repositories\Common\CommonRepository;

/**
 * 短信模块 之 模型（类库）
 */
class Sms
{
    public $sms_name = null; //用户名
    public $sms_password = null; //密码

    public function __construct()
    {
        /* 由于要包含init.php，所以这两个对象一定是存在的，因此直接赋值 */
        $this->sms_name = $GLOBALS['_CFG']['sms_ecmoban_user'];
        $this->sms_password = $GLOBALS['_CFG']['sms_ecmoban_password'];
    }

    // 发送短消息
    public function send($phones, $msg = '', $send_date = '', $send_num = 1, $sms_type = '', $version = '1.0', &$sms_error = '', $mobile_code = '')
    {
        if ($GLOBALS['_CFG']['sms_type'] == 0) {
            /* 检查发送信息的合法性 */
            $contents = $this->get_contents($phones, $msg);
            if (!$contents) {
                return false;
            }

            /* 获取API URL */
            $sms_url = "http://106.ihuyi.com/webservice/sms.php?method=Submit&ismbt=1";

            if (count($contents) > 1) {
                foreach ($contents as $key => $val) {
                    $post_data = "account=" . $this->sms_name . "&password=" . $this->sms_password . "&mobile=" . $val['phones'] . "&content=" . rawurlencode($val['content']); //密码可以使用明文密码或使用32位MD5加密

                    $get = $this->Post($post_data, $sms_url);
                    $gets = $this->xml_to_array($get);
                    sleep(1);
                }
            } else {
                $post_data = "account=" . $this->sms_name . "&password=" . $this->sms_password . "&mobile=" . $contents[0]['phones'] . "&content=" . rawurlencode($contents[0]['content']); //密码可以使用明文密码或使用32位MD5加密
                $get = $this->Post($post_data, $sms_url);
                $gets = $this->xml_to_array($get);
            }

            //print_r($gets); //开启调试模式
            if ($gets['SubmitResult']['code'] == 2) {
                return true;
            } else {
                $sms_error = $phones . $gets['SubmitResult']['msg'];
                $this->logResult($sms_error);
                return false;
            }
        } elseif ($GLOBALS['_CFG']['sms_type'] >= 1) {
            $msg = $this->get_usser_sms_msg($msg);

            //阿里大鱼短信接口
            if (!empty($msg['sms_value'])) {
                $smsParams = [
                    'mobile_phone' => $phones,
                    'mobilephone' => $phones,
                    'code' => $msg['code']
                ];

                $send_time = $msg['sms_value'];
            } else {
                $smsParams = [
                    'mobile_phone' => $phones,
                    'mobilephone' => $phones,
                    'code' => $msg['code'],
                    'product' => $msg['product']
                ];

                $send_time = 'sms_signin';
            }

            $result = sms_ali($smsParams, $send_time); //阿里大鱼短信变量传值，发送时机传值
            $resp = $GLOBALS['dsc']->ali_yu($result);

            $code = 0;
            if (isset($resp->code)) {
                $code = ($resp->code == 0) ? 0 : 1;
            } elseif (isset($resp->Code)) {
                $code = ($resp->Code == 0) ? 0 : 1;
            }

            $sub_msg = $resp->sub_msg ?? '';

            if ($code == 0) {
                return true;
            } else {
                if ($resp->sub_msg) {
                    $sms_error = $phones . $sub_msg;
                } else {
                    $sms_error = $phones . ":" . $sub_msg;
                }

                //$this->logResult($sms_error);
                return false;
            }
        }
    }

    public function Post($curlPost, $url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        $return_str = curl_exec($curl);
        curl_close($curl);
        return $return_str;
    }

    public function xml_to_array($xml)
    {
        $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
        if (preg_match_all($reg, $xml, $matches)) {
            $count = count($matches[0]);
            for ($i = 0; $i < $count; $i++) {
                $subxml = $matches[2][$i];
                $key = $matches[1][$i];
                if (preg_match($reg, $subxml)) {
                    $arr[$key] = $this->xml_to_array($subxml);
                } else {
                    $arr[$key] = $subxml;
                }
            }
        }
        return $arr;
    }

    //检查手机号和发送的内容并生成生成短信队列
    public function get_contents($phones, $msg)
    {
        if (empty($phones) || empty($msg)) {
            return false;
        }
        //$msg.='【'. $GLOBALS['_CFG']['shop_name'].'】'; //by wanganlin delete
        $phone_key = 0;
        $i = 0;
        $phones = explode(',', $phones);
        foreach ($phones as $key => $value) {
            if ($i < 200) {
                $i++;
            } else {
                $i = 0;
                $phone_key++;
            }

            if (is_phone_number($value)) {
                $phone[$phone_key][] = $value;
            } else {
                $i--;
            }
        }

        if (!empty($phone)) {
            foreach ($phone as $phone_key => $val) {
                if (EC_CHARSET != 'utf-8') {
                    $phone_array[$phone_key]['phones'] = implode(',', $val);
                    $phone_array[$phone_key]['content'] = $this->auto_charset($msg);
                } else {
                    $phone_array[$phone_key]['phones'] = implode(',', $val);
                    $phone_array[$phone_key]['content'] = $msg;
                }
            }
            return $phone_array;
        } else {
            return false;
        }
    }

    // 自动转换字符集 支持数组转换
    public function auto_charset($fContents, $from = 'gbk', $to = 'utf-8')
    {
        $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
        $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
        if (strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents))) {
            //如果编码相同或者非字符串标量则不转换
            return $fContents;
        }
        if (is_string($fContents)) {
            if (function_exists('mb_convert_encoding')) {
                return mb_convert_encoding($fContents, $to, $from);
            } elseif (function_exists('iconv')) {
                return iconv($from, $to, $fContents);
            } else {
                return $fContents;
            }
        } elseif (is_array($fContents)) {
            foreach ($fContents as $key => $val) {
                $_key = auto_charset($key, $from, $to);
                $fContents[$_key] = auto_charset($val, $from, $to);
                if ($key != $_key) {
                    unset($fContents[$key]);
                }
            }
            return $fContents;
        } else {
            return $fContents;
        }
    }

    //打印日志
    private function logResult($word = '')
    {
        $fp = fopen(storage_path('logs/smserrlog.txt'), "a");
        flock($fp, LOCK_EX);
        fwrite($fp, "执行日期：" . strftime("%Y%m%d%H%M%S", time()) . "\n" . $word . "\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * mobile_code    转换为 ${code}
     * user_name        转换为 ${product}
     */
    public function get_usser_sms_msg($msg)
    {
        $arr['code'] = $msg['mobile_code'];
        $arr['product'] = $msg['user_name'];
        $arr['sms_value'] = $msg['sms_value'];

        return $arr;
    }
}
