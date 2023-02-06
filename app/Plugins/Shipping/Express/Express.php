<?php

namespace App\Plugins\Shipping\Express;

/**
 * 配送方式插件
 */
class Express
{
    /**
     * 配置信息参数
     */
    public $configure;

    /**
     * 构造函数
     *
     * @param: $configure[array]    配送方式的参数的数组
     *
     * @return null
     */
    public function __construct($cfg = [])
    {
        if ($cfg) {
            foreach ($cfg as $key => $val) {
                $this->configure[$val['name']] = $val['value'];
            }
        }
    }

    /**
     * 计算订单的配送费用的函数
     *
     * @param float $goods_weight 商品重量
     * @param float $goods_amount 商品金额
     * @param float $goods_number 商品数量
     * @return  decimal
     */
    public function calculate($goods_weight, $goods_amount, $goods_number)
    {
        if ($this->configure['free_money'] > 0 && $goods_amount >= $this->configure['free_money']) {
            return 0;
        } else {
            $fee = $this->configure['base_fee'] ?? 0;
            $this->configure['fee_compute_mode'] = !empty($this->configure['fee_compute_mode']) ? $this->configure['fee_compute_mode'] : 'by_weight';

            if ($this->configure['fee_compute_mode'] == 'by_number') {
                $fee = $goods_number * ($this->configure['item_fee'] ?? 0);
            } else {
                if ($goods_weight > 1) {
                    $fee += (ceil(($goods_weight - 1))) * ($this->configure['step_fee'] ?? 0);
                }
            }

            return $fee;
        }
    }

    /**
     * 查询快递状态
     *
     * @access  public
     * @return  string  查询窗口的链接地址
     */
    public function query($invoice_sn)
    {
        $form_str = '<a href="http://www.express.com/Default.aspx" target="_blank">' . $invoice_sn . '</a>';
        return $form_str;
    }

    /**
     * 快递100 CODE
     */
    public function get_code_name()
    {
        return 'express';
    }
}