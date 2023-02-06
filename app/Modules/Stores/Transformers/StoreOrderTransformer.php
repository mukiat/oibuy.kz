<?php

namespace App\Modules\Stores\Transformers;

use App\Api\Foundation\Transformer\Transformer;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;

class StoreOrderTransformer extends Transformer
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    public function transform($item = [])
    {
        return [

        ];
    }

    /**
     * 统一转换门店订单信息
     * @param array $item
     * @return array
     */
    public function transformOrder($item = [])
    {
        $store_order = [];

        if (!empty($item)) {
            // 订单信息
            $order = $item['order_info'] ?? [];

            $store_order['order_id'] = $order['order_id'] ?? 0;
            $store_order['order_sn'] = $order['order_sn'] ?? '';
            $store_order['user_name'] = $order['get_users']['user_name'] ?? '';
            $store_order['mobile'] = $this->dscRepository->stringToStar($order['mobile'] ?? '', 4);
            $store_order['postscript'] = $order['postscript'] ?? '';
            // 提货状态
            $store_order['pick_up_status'] = !empty($order['shipping_status']) && $order['shipping_status'] == SS_RECEIVED ? 1 : 0;
            $store_order['pick_up_status_format'] = !empty($store_order['pick_up_status']) && $store_order['pick_up_status'] == 1 ? trans('stores::order.pick_up_status_1') : trans('stores::order.pick_up_status_0');
            $store_order['confirm_take_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $order['confirm_take_time'] ?? '');

            /* 订单收货地址 */
            $province = $order['get_region_province']['region_name'] ?? '';
            $city = $order['get_region_city']['region_name'] ?? '';
            $district = $order['get_region_district']['region_name'] ?? '';
            $street = $order['get_region_street']['region_name'] ?? '';
            $store_order['shipping_address'] = $province . ' ' . $city . ' ' . $district . ' ' . $street . $order['address'] ?? '';

            // 门店订单信息
            $store_order['store_id'] = $item['store_id'] ?? 0;
            $store_order['take_time'] = $item['take_time'] ?? ''; // 自提时间
            $store_order['pick_code'] = $item['pick_code'] ?? ''; // 自提码

            // 订单商品
            $order_goods = $order['goods'] ?? [];
            if (!empty($order_goods)) {
                foreach ($order_goods as $key => $value) {
                    $order_goods[$key]['goods_price_format'] = $this->dscRepository->getPriceFormat($value['goods_price'] ?? 0);
                    $order_goods[$key]['goods_thumb'] = $this->dscRepository->getImagePath($value['get_goods']['goods_thumb'] ?? '');
                    unset($order_goods[$key]['get_goods']);
                }
                $store_order['goods'] = $order_goods;
            }
        }

        return $store_order;
    }

}