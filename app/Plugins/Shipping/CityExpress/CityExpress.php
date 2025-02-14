<?php

namespace App\Plugins\Shipping\CityExpress;

/**
 * 城际快递插件
 * Class CityExpress
 * @package App\Plugins\Shipping
 */
class CityExpress
{
    /**
     * 配置信息
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
     * @return  decimal
     */
    public function calculate($goods_weight, $goods_amount)
    {
        if ($this->configure['free_money'] > 0 && $goods_amount >= $this->configure['free_money']) {
            return 0;
        } else {
            return $this->configure['base_fee'];
        }
    }

    /**
     * 查询发货状态
     * 该配送方式不支持查询发货状态
     *
     * @access  public
     * @param string $invoice_sn 发货单号
     * @return  string
     */
    public function query($invoice_sn)
    {
        return $invoice_sn;
    }

    /**
     * 快递100 CODE
     */
    public function get_code_name()
    {
        return '';
    }
}
