<?php

namespace App\Repositories\Flow;

use App\Models\Shipping;
use App\Repositories\Common\BaseRepository;

class FlowRepository
{
    /**
     * 重新组合购物流程商品数组
     *
     * @param array $cart_list
     * @return array
     */
    public static function getNewGroupCartGoods($cart_list = [])
    {
        $cart_goods = [];
        if ($cart_list) {
            foreach ($cart_list as $key => $goods) {
                $goods_list = isset($goods['goods_list']) ? $goods['goods_list'] : $goods['goods'] ?? [];
                foreach ($goods_list as $k => $list) {
                    $cart_goods[] = $list;
                }
            }
        }

        return $cart_goods;
    }

    /**
     * 订单 分单配送方式
     * @param array $shipping
     * @param array $shippingCode
     * @param array $shippingType
     * @param array $ru_id
     * @return array
     */
    public static function get_order_post_shipping($shipping = [], $shippingCode = [], $shippingType = [], $ru_id = [])
    {
        $shipping_list = [];
        if ($shipping) {
            $shipping_id = '';
            $shipping_name = '';
            $shipping_code = '';
            $shipping_type = '';
            $support_cod = '';
            foreach ($shipping as $k1 => $v1) {
                $v1 = !empty($v1) ? intval($v1) : 0;
                $shippingCode[$k1] = !empty($shippingCode[$k1]) ? addslashes($shippingCode[$k1]) : '';
                $shippingType[$k1] = empty($shippingType[$k1]) ? 0 : intval($shippingType[$k1]);

                $shippingInfo = self::shipping_info($v1);

                foreach ($ru_id as $k2 => $v2) {
                    if ($k1 == $k2) {
                        $shipping_id .= $v2 . "|" . $v1 . ",";  //商家ID + 配送ID
                        $shipping_name .= $v2 . "|" . ($shippingInfo['shipping_name'] ?? '') . ",";  //商家ID + 配送名称
                        $shipping_code .= $v2 . "|" . $shippingCode[$k1] . ",";  //商家ID + 配送code
                        $shipping_type .= $v2 . "|" . $shippingType[$k1] . ",";  //商家ID + （配送或自提）
                        $support_cod = $shippingInfo['support_cod'] ?? '';
                    }
                }
            }

            $shipping_id = substr($shipping_id, 0, -1);
            $shipping_name = substr($shipping_name, 0, -1);
            $shipping_code = substr($shipping_code, 0, -1);
            $shipping_type = substr($shipping_type, 0, -1);
            $shipping_list = [
                'shipping_id' => $shipping_id,
                'shipping_name' => $shipping_name,
                'shipping_code' => $shipping_code,
                'shipping_type' => $shipping_type,
                'support_cod' => $support_cod
            ];
        }
        return $shipping_list;
    }

    /**
     * 取得配送方式信息
     *
     * @param $shipping
     * @param array $select
     * @return mixed
     */
    public static function shipping_info($shipping, $select = [])
    {
        $row = Shipping::where('enabled', 1);

        if (!empty($select)) {
            $row = $row->select($select);
        }

        if (is_array($shipping)) {
            if (isset($shipping['shipping_code'])) {
                $row = $row->where('shipping_code', $shipping['shipping_code']);
            } elseif (isset($shipping['shipping_id'])) {
                $row = $row->where('shipping_id', $shipping['shipping_id']);
            }
        } else {
            $row = $row->where('shipping_id', $shipping);
        }

        $row = BaseRepository::getToArrayFirst($row);

        if (!empty($row)) {
            $row['pay_fee'] = 0.00;
        }

        return $row;
    }

    /**
     * 订单分单 留言
     * @param $postscript
     * @param array $ru_ids
     * @return string
     */
    public static function get_order_post_postscript($postscript, $ru_ids = [])
    {
        $postscript_value = '';
        if ($postscript && count($ru_ids) > 1) {
            foreach ($postscript as $k1 => $post) {
                $post = !empty($post) ? trim($post) : '';
                foreach ($ru_ids as $k2 => $ru_id) {
                    if ($k1 == $k2) {
                        $postscript_value .= $ru_id . "|" . $post . ",";  //商家ID + 留言内容
                    }
                }
            }

            $postscript_value = substr($postscript_value, 0, -1);
        }

        return $postscript_value;
    }

    /**
     * 获取购物车普通商品ID和超值礼包ID
     *
     * @param array $cart_goods
     * @return array
     */
    public static function cartGoodsAndPackage($cart_goods = [])
    {
        $goods_id = [];
        $package_goods_id = [];
        if ($cart_goods) {
            /* 购物车普通商品ID */
            $sql = [
                'where' => [
                    [
                        'name' => 'extension_code',
                        'value' => 'package_buy',
                        'condition' => '<>' //条件查询
                    ]
                ]
            ];
            $goods_id = BaseRepository::getArraySqlGet($cart_goods, $sql);
            $goods_id = BaseRepository::getKeyPluck($goods_id, 'goods_id');

            /* 购物车超值礼包商品ID */
            $sql = [
                'where' => [
                    [
                        'name' => 'extension_code',
                        'value' => 'package_buy'
                    ]
                ]
            ];
            $package_goods_id = BaseRepository::getArraySqlGet($cart_goods, $sql);
            $package_goods_id = BaseRepository::getKeyPluck($package_goods_id, 'goods_id');
        }

        return [
            'goods_id' => $goods_id,
            'package_goods_id' => $package_goods_id
        ];
    }
}
