<?php

namespace App\Services\Order;

use App\Models\DeliveryGoods;
use App\Models\DeliveryOrder;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\Shipping;
use App\Repositories\Common\BaseRepository;


class OrderDeliveryHandleService
{
    /**
     * 根据订单ID获取发货单商品信息
     *
     * @param array $order_id
     * @param array $data
     * @param int $limit
     * @return array
     */
    public static function getDeliveryOrderByOrderIdDataList($order_id = [], $data = [], $limit = 0)
    {
        $order_id = BaseRepository::getExplode($order_id);

        if (empty($order_id)) {
            return [];
        }

        $order_id = array_unique($order_id);

        $deliveryList = DeliveryOrder::select('delivery_id', 'order_id', 'order_sn', 'status')->whereIn('order_id', $order_id);
        $deliveryList = BaseRepository::getToArrayGet($deliveryList);

        if (empty($deliveryList)) {
            return [];
        }

        $delivery_id = BaseRepository::getKeyPluck($deliveryList, 'delivery_id');

        $data = empty($data) ? "*" : $data;

        $res = DeliveryGoods::select($data)
            ->whereIn('delivery_id', $delivery_id);

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $key => $row) {

                $sql = [
                    'where' => [
                        [
                            'name' => 'delivery_id',
                            'value' => $row['delivery_id']
                        ]
                    ]
                ];
                $delivery = BaseRepository::getArraySqlFirst($deliveryList, $sql);

                $res[$key]['order_id'] = $delivery['order_id'] ?? 0;
                $res[$key]['status'] = $delivery['status'] ?? 0;
            }
        }

        return $res;
    }


    /**
     * 查询订单
     * @param string $order_sn
     * @param array $columns
     * @return array
     */
    public static function getOrder($order_sn = '', $columns = [])
    {
        if (empty($order_sn)) {
            return [];
        }

        $model = OrderInfo::where('main_count', 0)->where('order_sn', $order_sn);

//        $model = $model->with([
//            'goods',
//        ]);

        if (!empty($columns)) {
            $model = $model->select($columns);
        }

        $order = $model->first();

        $order = $order ? $order->toArray() : [];

        if (!empty($order)) {

            // 子订单
            $order_child = OrderInfo::where('main_order_id', $order['order_id'])->count('order_id');
            $order['order_child'] = $order_child;
        }

        return $order;
    }

    /**
     * 发货单详情
     * @param string $order_sn
     * @param int $goods_id
     * @param string $goods_attr
     * @param int $product_id
     * @return mixed
     */
    public static function get_delivery($order_sn = '', $goods_id = 0, $goods_attr = '', $product_id = 0)
    {
        if (empty($order_sn)) {
            return [];
        }

        // 获得发货单数据
        $model = DeliveryOrder::where('order_sn', $order_sn);

        $model = $model->whereHasIn('getDeliveryGoods', function ($query) use ($goods_id, $goods_attr, $product_id) {
            $query->where('goods_id', $goods_id)->where(function ($query) use ($goods_id, $goods_attr, $product_id) {
                $query->where('goods_attr', $goods_attr)->orWhere('product_id', $product_id);
            });
        });

        $model = $model->with([
            'getDeliveryGoods' => function ($query) {
                $query->select('delivery_id');
            }
        ]);

        $res = BaseRepository::getToArrayFirst($model);

        return $res;
    }

    /**
     * 查询配送方式是否安装
     * @param string $shipping_name
     * @return string
     */
    public static function check_shipping_name($shipping_name = '')
    {
        if (empty($shipping_name)) {
            return '';
        }

        $count = Shipping::where('shipping_name', $shipping_name)->where('enabled', 1)->count();
        if (!empty($count)) {
            return $shipping_name;
        }

        return '';
    }

    /**
     * 更新订单商品
     * @param int $order_id
     * @param int $goods_id
     * @param array $data
     * @return bool
     */
    public static function update_order_goods($order_id = 0, $goods_id = 0, $data = [])
    {
        if (empty($data)) {
            return false;
        }

        /* 过滤表字段 */
        $data = BaseRepository::getArrayfilterTable($data, 'order_goods');

        return OrderGoods::where('order_id', $order_id)->where('goods_id', $goods_id)->update($data);
    }

    /**
     * 生成发货单
     * @param array $data
     * @return bool
     */
    public static function create_delivery_order($data = [])
    {
        if (empty($data)) {
            return false;
        }

        /* 过滤表字段 */
        $data = BaseRepository::getArrayfilterTable($data, 'delivery_order');

        $delivery_id = DeliveryOrder::insertGetId($data);

        return $delivery_id;
    }

    /**
     * 发货单商品入库
     * @param array $data
     * @return bool
     */
    public static function create_delivery_goods($data = [])
    {
        if (empty($data)) {
            return false;
        }

        /* 过滤表字段 */
        $data = BaseRepository::getArrayfilterTable($data, 'delivery_goods');

        return DeliveryGoods::insert($data);
    }

    /**
     * 更新发货单信息
     * @param int $delivery_id
     * @param array $data
     * @return bool
     */
    public static function update_delivery_order($delivery_id = 0, $data = [])
    {
        if (empty($delivery_id) || empty($data)) {
            return false;
        }

        /* 过滤表字段 */
        $data = BaseRepository::getArrayfilterTable($data, 'delivery_order');

        return DeliveryOrder::where('delivery_id', $delivery_id)->update($data);
    }

    /**
     * 更新发货单商品 发货数量(自增)
     * @param int $delivery_id
     * @param int $goods_id
     * @param int $send_number
     * @return bool
     */
    public static function update_delivery_goods($delivery_id = 0, $goods_id = 0, $send_number = 0)
    {
        if (empty($delivery_id) || empty($goods_id) || empty($send_number)) {
            return false;
        }

        // 发货数量自增
        return DeliveryGoods::where('delivery_id', $delivery_id)->where('goods_id', $goods_id)->increment('send_number', $send_number);
    }

    /**
     * 更新订单商品 发货数量(自增)
     * @param int $order_id
     * @param int $goods_id
     * @param int $send_number
     * @return bool
     */
    public static function updateOrderGoodsSendNumber($order_id = 0, $goods_id = 0, $send_number = 0)
    {
        if (empty($order_id) || empty($goods_id) || empty($send_number)) {
            return false;
        }

        // 发货数量自增
        return OrderGoods::where('order_id', $order_id)->where('goods_id', $goods_id)->increment('send_number', $send_number);
    }
}