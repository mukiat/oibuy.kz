<?php

namespace App\Plugins\Payment\Bank;

/**
 * 银行转账支付插件
 *
 * Class Bank
 * @package App\Plugins\Payment\Bank
 */
class Bank
{
    /**
     * 支付配置信息
     */
    public function getConfig($pay_code = '', $pay_config = [])
    {
        $pay_code = ucfirst($pay_code); // 首字母大写
        $modules = plugin_path('Payment/' . $pay_code . '/Languages/' . config('shop.lang') . '.php');
        $payconfig = [];
        if (file_exists($modules)) {
            $data = include_once($modules);
            foreach ($pay_config as $key => $val) {
                if ($val['name'] == 'bank_explain' && empty($val['value'])) {
                    continue;
                }
                $arr['value'] = $val['value'];
                $arr['name'] = $data[$val['name']];
                $payconfig[] = $arr;
            }
        }

        return $payconfig;
    }

    /**
     * 提交函数
     */
    public function get_code()
    {
        return '';
    }

    /**
     * 处理函数
     */
    public function respond()
    {
        return;
    }

}
