<?php

namespace App\Repositories\Order;

use App\Models\DeliveryOrder;
use App\Models\Goods;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\PackageGoods;
use App\Models\Products;
use App\Models\ProductsArea;
use App\Models\ProductsWarehouse;
use App\Models\StoreGoods;
use App\Models\StoreProducts;
use App\Models\WarehouseAreaGoods;
use App\Models\WarehouseGoods;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Seller\SellerShopinfoRepository;
use Illuminate\Support\Facades\DB;

/**
 * Class OrderRepository
 * @package App\Repositories\Order
 */
class OrderRepository
{

    /**
     * 获取订单信息
     * @param int $order_id
     * @param array $columns
     * @return string
     */
    public static function getOrderInfo($order_id = 0, $columns = ['*'])
    {
        $model = OrderInfo::select($columns)->where('order_id', $order_id);

        $model = $model->with([
            'goods',
        ]);

        $order = $model->first();

        $order = $order ? $order->toArray() : [];

        return $order;
    }

    /**
     * 获取订单商品信息
     *
     * @param int $order_id
     * @param string[] $columns
     * @return array
     */
    public static function orderGoodsList($order_id = 0, $columns = ['*'])
    {
        if (empty($order_id)) {
            return [];
        }

        $model = OrderGoods::select($columns)->where('order_id', $order_id);

        $list = $model->get();

        return $list ? $list->toArray() : [];
    }

    /**
     * 拆分主订单数据 类型格式  如 1|申通快递,9|申通快递
     *
     * @param string $string
     * @return array
     */
    public static function spliptMainOrderData($string = '')
    {
        if (empty($string)) {
            return [];
        }

        $arr = explode(',', $string);

        $new = [];
        foreach ($arr as $val) {
            [$ru_id, $val] = explode('|', $val);
            $new[$ru_id] = $val;
        }

        return $new;
    }

    /**
     * 用户订单详情
     * @param int $order_id
     * @param int $user_id
     * @return array|mixed
     */
    public static function userOrderDetail($order_id = 0, $user_id = 0, $columns = ['*'])
    {
        $model = OrderInfo::where('user_id', $user_id)
            ->where('order_id', $order_id);

        $model = $model->with([
            'goods' => function ($query) {
                $query->with([
                    'getGoods' => function ($query) {
                        $query->select('goods_id', 'goods_thumb', 'goods_img');
                    }
                ]);
            },
        ]);

        $order = $model->select($columns)->first();

        return $order ? $order->toArray() : [];
    }


    /**
     * 插入订单表
     * @param array $order
     * @return int
     */
    public static function createOrder($order = [])
    {
        if (empty($order)) {
            return 0;
        }

        /* 过滤表字段 */
        $new_order = BaseRepository::getArrayfilterTable($order, 'order_info');

        $count = DB::table('order_info')->where('order_sn', $order['order_sn'])->count('order_id');
        $count = $count ?? 0;

        if (empty($count)) {
            $order_id = DB::table('order_info')->insertGetId($new_order);

            return $order_id;
        }

        return 0;
    }

    /**
     * 订单操作记录
     * @param int $order_id
     * @param int $order_status
     * @param int $shipping_status
     * @param int $pay_status
     * @param string $note
     * @param string $username
     * @param int $place
     * @param int $confirm_take_time
     * @return bool|int
     */
    public static function order_action($order_id = 0, $order_status = 0, $shipping_status = 0, $pay_status = 0, $note = '', $username = '', $place = 0, $confirm_take_time = 0)
    {
        if (empty($order_id)) {
            return false;
        }

        $log_time = $confirm_take_time > 0 ? $confirm_take_time : TimeRepository::getGmTime();

        // 同一订单 订单状态不同
        $where = [
            'order_id' => $order_id,
            'order_status' => $order_status,
            'shipping_status' => $shipping_status,
            'pay_status' => $pay_status,
        ];

        $values = [
            'action_user' => $username,
            'action_place' => $place,
            'action_note' => $note,
            'log_time' => $log_time
        ];
        return DB::table('order_action')->updateOrInsert($where, $values);
    }

    /**
     * 退换货订单操作记录
     * @param int $ret_id 退换货编号
     * @param string $return_status 退货状态
     * @param string $refound_status 退款状态
     * @param string $note 备注
     * @param string $username 用户名，用户自己的操作则为 buyer
     * @param int $place
     * @param int $confirm_take_time
     * @return bool|int
     */
    public static function return_action($ret_id = 0, $return_status = '', $refound_status = '', $note = '', $username = '', $place = 0, $confirm_take_time = 0)
    {
        if (empty($ret_id)) {
            return false;
        }

        $log_time = $confirm_take_time > 0 ? $confirm_take_time : TimeRepository::getGmTime();

        // 同一退换货订单 退款状态不同
        $where = [
            'ret_id' => $ret_id,
            'return_status' => $return_status,
            'refound_status' => $refound_status,
        ];

        $values = [
            'action_user' => $username,
            'action_place' => $place,
            'action_note' => $note,
            'log_time' => $log_time
        ];
        return DB::table('return_action')->updateOrInsert($where, $values);
    }

    /**
     * 创建已付款订单快照信息
     * @param int $order_id
     * @return string
     */
    public static function create_snapshot($order_id = 0)
    {
        if (empty($order_id)) {
            return false;
        }

        /**
         * 一件订单商品 一条快照
         */

        // 是否有子订单
        $child_order = OrderInfo::query()->select('order_id', 'order_sn', 'user_id')->where('main_order_id', $order_id)->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED]);
        $child_order = $child_order->with([
            'goods' => function ($query) {
                $query->with([
                    'getGoods' => function ($query) {
                        $query->select('goods_id', 'goods_weight', 'add_time', 'goods_desc', 'goods_img');
                    }
                ]);
            }
        ]);
        $child_order = $child_order->select('order_id', 'order_sn', 'ru_id')->get();
        $child_order = $child_order ? $child_order->toArray() : [];
        if (!empty($child_order)) {
            // 分单 获取子订单订单与商品信息
            foreach ($child_order as $child) {
                $order_goods = $child['goods'] ?? [];
                $snapshot_info = [];
                if (!empty($order_goods)) {
                    foreach ($order_goods as $k => $item) {

                        $item = collect($item)->merge($item['get_goods'])->except('get_goods')->all(); // 合并且移除get_goods

                        $count = DB::table('trade_snapshot')->where('order_id', $item['order_id'])->where('goods_id', $item['goods_id'])->count('trade_id');
                        $count = $count ?? 0;
                        if (empty($count)) {
                            // 订单商品快照信息
                            $snapshot['order_id'] = $item['order_id'];
                            $snapshot['order_sn'] = $child['order_sn'];
                            $snapshot['user_id'] = $item['user_id'];
                            $snapshot['goods_id'] = $item['goods_id'];
                            $snapshot['goods_name'] = addslashes($item['goods_name']);
                            $snapshot['goods_sn'] = $item['goods_sn'];
                            $snapshot['shop_price'] = $item['goods_price'];
                            $snapshot['goods_number'] = $item['goods_number'];
                            $snapshot['shipping_fee'] = $item['shipping_fee'] ?? 0;
                            $snapshot['rz_shop_name'] = SellerShopinfoRepository::getShopName($item['ru_id']);
                            $snapshot['goods_weight'] = $item['goods_weight'] ?? 0;
                            $snapshot['add_time'] = $item['add_time'];
                            $snapshot['goods_attr'] = $item['goods_attr'] ?? '';
                            $snapshot['goods_attr_id'] = $item['goods_attr_id'] ?? 0;
                            $snapshot['ru_id'] = $item['ru_id'] ?? '';
                            $snapshot['goods_desc'] = $item['goods_desc'] ?? '';
                            $snapshot['goods_img'] = $item['goods_img'] ?? '';
                            $snapshot['snapshot_time'] = TimeRepository::getGmTime();
                            $snapshot_info[$k] = $snapshot;
                        }
                    }

                    // 批量插入订单商品快照
                    DB::table('trade_snapshot')->insert($snapshot_info);
                }
            }

        } else {
            // 无分单 获取订单与商品信息
            $order = OrderInfo::query()->select('order_id', 'order_sn', 'user_id')->where('order_id', $order_id)->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED]);
            $order = $order->with([
                'goods' => function ($query) {
                    $query->with([
                        'getGoods' => function ($query) {
                            $query->select('goods_id', 'goods_weight', 'add_time', 'goods_desc', 'goods_img');
                        }
                    ]);
                }
            ]);
            $order = $order->first();
            $order = $order ? $order->toArray() : [];

            if (!empty($order)) {
                $order_goods = $order['goods'] ?? [];
                $snapshot_info = [];
                if (!empty($order_goods)) {
                    foreach ($order_goods as $k => $item) {
                        $item = collect($item)->merge($item['get_goods'])->except('get_goods')->all(); // 合并且移除get_goods

                        $count = DB::table('trade_snapshot')->where('order_id', $item['order_id'])->where('goods_id', $item['goods_id'])->count('trade_id');
                        $count = $count ?? 0;
                        if (empty($count)) {
                            // 订单商品快照信息
                            $snapshot['order_id'] = $item['order_id'];
                            $snapshot['order_sn'] = $order['order_sn'];
                            $snapshot['user_id'] = $item['user_id'];
                            $snapshot['goods_id'] = $item['goods_id'];
                            $snapshot['goods_name'] = addslashes($item['goods_name']);
                            $snapshot['goods_sn'] = $item['goods_sn'];
                            $snapshot['shop_price'] = $item['goods_price'];
                            $snapshot['goods_number'] = $item['goods_number'];
                            $snapshot['shipping_fee'] = $item['shipping_fee'] ?? 0;
                            $snapshot['rz_shop_name'] = SellerShopinfoRepository::getShopName($item['ru_id']);
                            $snapshot['goods_weight'] = $item['goods_weight'] ?? 0;
                            $snapshot['add_time'] = $item['add_time'];
                            $snapshot['goods_attr'] = $item['goods_attr'] ?? '';
                            $snapshot['goods_attr_id'] = $item['goods_attr_id'] ?? 0;
                            $snapshot['ru_id'] = $item['ru_id'] ?? '';
                            $snapshot['goods_desc'] = $item['goods_desc'] ?? '';
                            $snapshot['goods_img'] = $item['goods_img'] ?? '';
                            $snapshot['snapshot_time'] = TimeRepository::getGmTime();

                            $snapshot_info[$k] = $snapshot;
                        }
                    }

                    // 批量插入订单商品快照
                    DB::table('trade_snapshot')->insert($snapshot_info);
                }
            }
        }

        return true;
    }

    /**
     * 付款更新商品销量
     *
     * @param int $order_id
     * @param array $order
     * @return bool|int
     */
    public static function increment_goods_sale_pay($order_id = 0, $order = [])
    {
        // 增加销量时机 - 付款
        if (empty($order_id)) {
            return false;
        }

        if (empty($order)) {
            $order = DB::table('order_info')->select('order_id', 'pay_status', 'is_update_sale', 'extension_code')->where('order_id', $order_id)->first();
            $order = collect($order)->toArray() ?? [];
        }

        if (!empty($order)) {
            $is_update_sale = $order['is_update_sale'] ?? 0; // 订单是否已更新销量
            if ($order['pay_status'] == PS_PAYED && $is_update_sale == 0) {
                // 订单已支付  修改订单销量更新状态 已更新
                $up = DB::table('order_info')->where('order_id', $order_id)->where('is_update_sale', 0)->update(['is_update_sale' => 1]);
                if ($up) {
                    $goodsList = DB::table('order_goods')->select('goods_id', 'goods_number')->where('order_id', $order_id)->get();
                    if ($goodsList) {
                        foreach ($goodsList as $goods) {

                            if ($order['extension_code'] == 'exchange_goods') {
                                DB::table('exchange_goods')->where('goods_id', $goods->goods_id)->increment('sales_volume', $goods->goods_number);
                            }

                            DB::table('goods')->where('goods_id', $goods->goods_id)->increment('sales_volume', $goods->goods_number);

                        }
                    }
                }

                return $up;
            }
        }

        return false;
    }

    /**
     * 发货更新商品销量
     *
     * @param int $order_id
     * @param array $order
     * @return bool|int
     */
    public static function increment_goods_sale_ship($order_id = 0, $order = [])
    {
        // 增加销量时机 - 发货
        if (empty($order_id)) {
            return false;
        }

        if (empty($order)) {
            $order = DB::table('order_info')->select('order_id', 'shipping_status', 'is_update_sale', 'extension_code')->where('order_id', $order_id)->first();
            $order = collect($order)->toArray() ?? [];
        }

        if (!empty($order)) {
            $is_update_sale = $order['is_update_sale'] ?? 0; // 订单是否已更新销量
            if ($order['shipping_status'] == SS_SHIPPED && $is_update_sale == 0) {
                // 订单已发货  修改订单销量更新状态 已更新
                $up = DB::table('order_info')->where('order_id', $order_id)->where('is_update_sale', 0)->update(['is_update_sale' => 1]);
                if ($up) {
                    $goodsList = DB::table('order_goods')->select('goods_id', 'send_number')->where('order_id', $order_id)->get();
                    if ($goodsList) {
                        foreach ($goodsList as $goods) {
                            DB::table('goods')->where('goods_id', $goods->goods_id)->increment('sales_volume', $goods->send_number);

                            if ($order['extension_code'] == 'exchange_goods') {
                                DB::table('exchange_goods')->where('goods_id', $goods->goods_id)->increment('sales_volume', $goods->goods_number);
                            }
                        }
                    }
                }

                return $up;
            }
        }

        return false;
    }

    /**
     * 设置订单未付款 退回商品销量
     *
     * @param int $order_id
     * @param array $order
     * @return bool|int
     */
    public static function decrement_goods_sale_unpaid($order_id = 0, $order = [])
    {
        // 退回商品销量时机 - 付款

        if (empty($order_id)) {
            return false;
        }

        if (empty($order)) {
            $order = DB::table('order_info')->select('order_id', 'pay_status', 'is_update_sale')->where('order_id', $order_id)->first();
            $order = collect($order)->toArray() ?? [];
        }

        if (!empty($order)) {
            $is_update_sale = $order['is_update_sale'] ?? 0; // 订单是否已更新销量
            if ($order['pay_status'] == PS_UNPAYED && $is_update_sale == 1) {
                // 订单未支付  修改订单销量更新状态 未更新
                $up = DB::table('order_info')->where('order_id', $order_id)->where('is_update_sale', 1)->update(['is_update_sale' => 0]);
                if ($up) {
                    $goodsList = DB::table('order_goods')->select('goods_id', 'goods_number')->where('order_id', $order_id)->where('goods_number', '>', 0)->get();
                    if ($goodsList) {
                        foreach ($goodsList as $goods) {
                            DB::table('goods')->where('goods_id', $goods->goods_id)->where('sales_volume', '>=', $goods->goods_number)->decrement('sales_volume', $goods->goods_number);
                        }
                    }
                }

                return $up;
            }
        }

        return false;
    }

    /**
     * 设置订单未发货 退回商品销量
     *
     * @param int $order_id
     * @param array $order
     * @return bool|int
     */
    public static function decrement_goods_sale_unshipped($order_id = 0, $order = [])
    {
        // 退回商品销量时机 - 发货
        if (empty($order_id)) {
            return false;
        }

        if (empty($order)) {
            $order = DB::table('order_info')->select('order_id', 'shipping_status', 'is_update_sale')->where('order_id', $order_id)->first();
            $order = collect($order)->toArray() ?? [];
        }

        if (!empty($order)) {
            $is_update_sale = $order['is_update_sale'] ?? 0; // 订单是否已更新销量
            if ($order['shipping_status'] == SS_UNSHIPPED && $is_update_sale == 1) {
                // 订单未发货  修改订单销量更新状态 未更新
                $up = DB::table('order_info')->where('order_id', $order_id)->where('is_update_sale', 1)->update(['is_update_sale' => 0]);
                if ($up) {
                    $goodsList = DB::table('order_goods')->select('goods_id', 'send_number')->where('order_id', $order_id)->where('send_number', '>', 0)->get();
                    if ($goodsList) {
                        foreach ($goodsList as $goods) {
                            DB::table('goods')->where('goods_id', $goods->goods_id)->where('sales_volume', '>=', $goods->send_number)->decrement('sales_volume', $goods->send_number);
                        }
                    }
                }

                return $up;
            }
        }

        return false;
    }

    /**
     * 订单中的商品是否已经全部发货
     * @param int $order_id 订单 id
     * @return  int     1，全部发货；0，未全部发货
     */
    public static function getOrderFinish($order_id = 0)
    {
        if (empty($order_id)) {
            return 0;
        }

        $sum = OrderGoods::where('order_id', $order_id)
            ->whereColumn('goods_number', '>', 'send_number')
            ->count('rec_id');

        $sum = $sum ? $sum : 0;

        if (empty($sum)) {
            return 1;
        }

        return 0;
    }

    /**
     * 判断订单的发货单是否全部发货
     * @param int $order_id 订单 id
     * @return  int     1，全部发货；0，未全部发货；-1，部分发货；-2，完全没发货；
     */
    public static function getAllDeliveryFinish($order_id = 0)
    {
        if (empty($order_id)) {
            return 0;
        }

        /* 未全部分单 */
        if (!self::getOrderFinish($order_id)) {
            return 0;
        } else {
            /* 已全部分单 */

            // 是否全部发货
            $sum = DeliveryOrder::where('order_id', $order_id)->where('status', 2)->count();
            $sum = $sum ? $sum : 0;

            // 全部发货
            if (empty($sum)) {
                return 1;
            } else {
                // 未全部发货
                $goodsCount = OrderGoods::where('order_id', $order_id)->count('order_id');
                $deliveryStatus = DeliveryOrder::where('order_id', $order_id)->where('status', 0)->count('delivery_id');
                if ($deliveryStatus == 0 && $goodsCount == 1) {
                    return 1;
                } else {
                    /* 订单全部发货中时：当前发货单总数 */
                    $_sum = DeliveryOrder::where('order_id', $order_id)->where('status', '<>', 1)->count();
                    $_sum = $_sum ? $_sum : 0;

                    if ($_sum == $sum) {
                        return -2; // 完全没发货
                    } else {
                        return -1; // 部分发货
                    }
                }
            }
        }
    }

    /**
     * 获取未支付,有效订单信息
     *
     * @param int $order_id
     * @param int $main_order_id
     * @return  array
     */
    public static function getUnPayedOrderInfo($order_id = 0, $main_order_id = 0)
    {
        if ($main_order_id > 0) {
            $model = OrderInfo::selectRaw("GROUP_CONCAT(order_id) AS order_id, GROUP_CONCAT(order_sn) AS order_sn")
                ->where('main_order_id', $main_order_id);
        } else {
            $model = OrderInfo::query()->where('order_id', $order_id);
        }

        // 订单状态 未确认，已确认、已分单
        $order_status = [
            OS_UNCONFIRMED,
            OS_CONFIRMED,
            OS_SPLITED
        ];

        // 支付状态 未支付
        $pay_status = [
            PS_PAYING,
            PS_UNPAYED,
            PS_PAYED_PART, // 部分付款--预售定金
            PS_MAIN_PAYED_PART //部分付款
        ];

        $model = $model->whereIn('order_status', $order_status)
            ->whereIn('pay_status', $pay_status);

        $model = $model->with([
            'getPayment',
            'getSellerNegativeOrder'
        ]);

        $model = $model->first();

        $res = $model ? $model->toArray() : [];

        return $res;
    }

    /**
     * 改变订单中商品库存
     * @param int $order_id 订单号
     * @param bool $is_dec 是否减少库存
     * @param int $storage 减库存的时机，2，付款时； 1，下订单时；0，发货时；
     * @param int $use_storage 出库（0,1）、入库(2,3,5)
     * @param int $admin_id 管理员id
     * @param int $store_id 门店id
     * @return mixed
     */
    public static function change_order_goods_storage($order_id = 0, $is_dec = true, $storage = 0, $use_storage = 0, $admin_id = 0, $store_id = 0)
    {
        $select = '';

        /* 查询订单商品信息 */
        switch ($storage) {
            case 0:
                $select = "goods_id, send_number AS num, extension_code, product_id, warehouse_id, area_id, area_city";
                break;

            case 1:
            case 2:
                $select = "goods_id, goods_number AS num, extension_code, product_id, warehouse_id, area_id, area_city";
                break;
        }

        $res = [];
        if ($select) {
            $res = OrderGoods::selectRaw($select)->where('order_id', $order_id)->where('is_real', 1);
            $res = BaseRepository::getToArrayGet($res);
        }

        if ($res) {
            foreach ($res as $row) {
                if ($row['extension_code'] != "package_buy") {
                    if ($is_dec) {
                        self::change_goods_storage($row['goods_id'], $row['product_id'], -$row['num'], $row['warehouse_id'], $row['area_id'], $row['area_city'], $order_id, $use_storage, $admin_id, $store_id);
                    } else {
                        self::change_goods_storage($row['goods_id'], $row['product_id'], $row['num'], $row['warehouse_id'], $row['area_id'], $row['area_city'], $order_id, $use_storage, $admin_id, $store_id);
                    }
                } else {
                    $res_goods = PackageGoods::select('goods_id', 'goods_number')->where('package_id', $row['goods_id']);
                    $res_goods = $res_goods->with('getGoods');
                    $res_goods = BaseRepository::getToArrayGet($res_goods);

                    if ($res_goods) {
                        foreach ($res_goods as $row_goods) {
                            $is_goods = $row_goods['get_goods'] ? $row_goods['get_goods'] : [];
                            if ($is_dec) {
                                self::change_goods_storage($row_goods['goods_id'], $row['product_id'], -($row['num'] * $row_goods['goods_number']), $row['warehouse_id'], $row['area_id'], $row['area_city'], $order_id, $use_storage, $admin_id);
                            } elseif ($is_goods && $is_goods['is_real']) {
                                self::change_goods_storage($row_goods['goods_id'], $row['product_id'], ($row['num'] * $row_goods['goods_number']), $row['warehouse_id'], $row['area_id'], $row['area_city'], $order_id, $use_storage, $admin_id);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * 商品库存增与减 货品库存增与减
     *
     * @param int $goods_id 商品ID
     * @param int $product_id 货品ID
     * @param int $number 增减数量，默认0；
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param int $order_id
     * @param int $use_storage
     * @param int $admin_id
     * @param int $store_id
     * @return bool
     */
    public static function change_goods_storage($goods_id = 0, $product_id = 0, $number = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0, $order_id = 0, $use_storage = 0, $admin_id = 0, $store_id = 0)
    {
        if ($number == 0) {
            return true; // 值为0即不做、增减操作，返回true
        }

        if (empty($goods_id) || empty($number)) {
            return false;
        }
        $number = ($number > 0) ? '+ ' . $number : $number;

        $goods = Goods::select('model_inventory', 'model_attr')->where('goods_id', $goods_id);
        $goods = BaseRepository::getToArrayFirst($goods);

        /* 秒杀活动扩展信息 */
        $extension_code = OrderGoods::where('order_id', $order_id)->value('extension_code');

        $sec_goods_id = 0;
        $seckill_update = '';
        if ($extension_code && stripos($extension_code, 'seckill') !== false) {
            $is_seckill = true;
            $sec_goods_id = (int)substr($extension_code, 7);
        } else {
            $is_seckill = false;
        }

        /* 处理货品库存 */
        $abs_number = abs($number);
        if (!empty($product_id)) {
            if (isset($store_id) && $store_id > 0) {
                $res = StoreProducts::where('store_id', $store_id);
            } else {
                if ($goods['model_attr'] == 1) {
                    $res = ProductsWarehouse::whereRaw(1);
                } elseif ($goods['model_attr'] == 2) {
                    $res = ProductsArea::whereRaw(1);
                } else {
                    $res = Products::whereRaw(1);
                }
            }

            if ($number < 0) {
                $set_update = "IF(product_number >= $abs_number, product_number $number, 0)";
            } else {
                $set_update = "product_number $number";
            }

            if ($is_seckill) {
                $seckill_update = "IF(sec_num >= $abs_number, sec_num $number, 0)";
                // 更新秒杀属性库存
                \App\Repositories\Activity\SeckillRepository::update_seckill_goods_stock($sec_goods_id, $goods_id, $product_id, $seckill_update);
            }

            $other = [
                'product_number' => DB::raw($set_update)
            ];
            $res->where('goods_id', $goods_id)->where('product_id', $product_id)->update($other);
        } else {
            if ($number < 0) {
                if ($store_id > 0) {
                    $set_update = "IF(goods_number >= $abs_number, goods_number $number, 0)";
                } else {
                    if ($is_seckill) {
                        $seckill_update = "IF(sec_num >= $abs_number, sec_num $number, 0)";
                    }

                    if ($goods['model_inventory'] == 1 || $goods['model_inventory'] == 2) {
                        $set_update = "IF(region_number >= $abs_number, region_number $number, 0)";
                    } else {
                        $set_update = "IF(goods_number >= $abs_number, goods_number $number, 0)";
                    }
                }
            } else {
                if ($store_id > 0) {
                    $set_update = "goods_number $number";
                } else {
                    if ($goods['model_inventory'] == 1 || $goods['model_inventory'] == 2) {
                        $set_update = "region_number $number";
                    } else {
                        $set_update = "goods_number $number";
                    }

                    if ($is_seckill) {
                        $seckill_update = " sec_num $number ";
                    }
                }
            }

            /* 处理商品库存 */
            if ($store_id > 0) {
                $other = [
                    'goods_number' => DB::raw($set_update)
                ];
                StoreGoods::where('goods_id', $goods_id)->where('store_id', $store_id)->update($other);
            } else {
                if ($goods['model_inventory'] == 1 && !$is_seckill) {
                    $other = [
                        'region_number' => DB::raw($set_update)
                    ];
                    WarehouseGoods::where('goods_id', $goods_id)->where('region_id', $warehouse_id)->update($other);
                } elseif ($goods['model_inventory'] == 2 && !$is_seckill) {
                    $other = [
                        'region_number' => DB::raw($set_update)
                    ];
                    $update = WarehouseAreaGoods::where('goods_id', $goods_id)->where('region_id', $area_id);

                    if (config('shop.area_pricetype', 0) == 1) {
                        $update = $update->where('city_id', $area_city);
                    }

                    $update->update($other);
                } else {

                    if ($is_seckill) {
                        // 更新秒杀库存
                        \App\Repositories\Activity\SeckillRepository::update_seckill_goods_stock($sec_goods_id, $goods_id, 0, $seckill_update);
                    }

                    $other = [
                        'goods_number' => DB::raw($set_update)
                    ];
                    Goods::where('goods_id', $goods_id)->update($other);
                }
            }
        }

        //库存日志
        $logs_other = [
            'goods_id' => $goods_id,
            'order_id' => $order_id,
            'use_storage' => $use_storage,
            'admin_id' => $admin_id,
            'number' => $number,
            'model_inventory' => $goods['model_inventory'],
            'model_attr' => $goods['model_attr'],
            'product_id' => $product_id,
            'warehouse_id' => $warehouse_id,
            'area_id' => $area_id,
            'add_time' => TimeRepository::getGmTime()
        ];
        DB::table('goods_inventory_logs')->insert($logs_other);
        return true;
    }
}
