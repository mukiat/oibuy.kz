<?php

/* 获取传值 */
$seller_id = isset($_REQUEST['seller_id']) ? $base->get_intval($_REQUEST['seller_id']) : -1;                    //商家ID
$order_id = isset($_REQUEST['order_id']) ? $base->get_intval($_REQUEST['order_id']) : -1;                       //订单ID
$order_sn = isset($_REQUEST['order_sn']) ? $base->get_addslashes($_REQUEST['order_sn']) : -1;                   //订单编号
$user_id = isset($_REQUEST['user_id']) ? $base->get_intval($_REQUEST['user_id']) : -1;                          //会员ID
$mobile = isset($_REQUEST['mobile']) ? $base->get_addslashes($_REQUEST['mobile']) : -1;                         //订单联系手机号码
$rec_id = isset($_REQUEST['rec_id']) ? $base->get_intval($_REQUEST['rec_id']) : -1;                             //订单商品ID
$goods_id = isset($_REQUEST['goods_id']) ? $base->get_intval($_REQUEST['goods_id']) : -1;                       //商品ID
$goods_sn = isset($_REQUEST['goods_sn']) ? $base->get_addslashes($_REQUEST['goods_sn']) : -1;                   //商品货号

$start_add_time = isset($_REQUEST['start_add_time']) ? $base->get_addslashes($_REQUEST['start_add_time']) : -1;                     //添加开始时间
$end_add_time = isset($_REQUEST['end_add_time']) ? $base->get_addslashes($_REQUEST['end_add_time']) : -1;                           //添加结束时间
$start_confirm_time = isset($_REQUEST['start_confirm_time']) ? $base->get_addslashes($_REQUEST['start_confirm_time']) : -1;         //订单确认开始时间
$end_confirm_time = isset($_REQUEST['end_confirm_time']) ? $base->get_addslashes($_REQUEST['end_confirm_time']) : -1;               //订单确认结束时间
$start_pay_time = isset($_REQUEST['start_pay_time']) ? $base->get_addslashes($_REQUEST['start_pay_time']) : -1;                     //支付开始时间
$end_pay_time = isset($_REQUEST['end_pay_time']) ? $base->get_addslashes($_REQUEST['end_pay_time']) : -1;                           //支付结束时间
$start_shipping_time = isset($_REQUEST['start_shipping_time']) ? $base->get_addslashes($_REQUEST['start_shipping_time']) : -1;      //配送开始时间
$end_shipping_time = isset($_REQUEST['end_shipping_time']) ? $base->get_addslashes($_REQUEST['end_shipping_time']) : -1;            //配送结束时间
$start_take_time = isset($_REQUEST['start_take_time']) ? $base->get_addslashes($_REQUEST['start_take_time']) : -1;                  //确认收货开始时间
$end_take_time = isset($_REQUEST['end_take_time']) ? $base->get_addslashes($_REQUEST['end_take_time']) : -1;                        //确认收货结束时间

$order_status = isset($_REQUEST['order_status']) ? $base->get_intval($_REQUEST['order_status']) : -1;               //订单状态
$shipping_status = isset($_REQUEST['shipping_status']) ? $base->get_intval($_REQUEST['shipping_status']) : -1;      //配送状态
$pay_status = isset($_REQUEST['pay_status']) ? $base->get_intval($_REQUEST['pay_status']) : -1;                     //付款状态

$open_api = $open_api ?? [];

/* 判断是否为指定商家接口 */
$ru_id = $open_api['ru_id'] ?? 0;
if ($ru_id > 0) {
    $seller_id = $ru_id;
}

/* 判断是否为指定会员接口 */
if ($open_api && $open_api['user_id'] > 0) {
    $user_id = $open_api['user_id'];
}

$val = array(
    'method' => $method,
    'seller_id' => $seller_id,
    'order_id' => $order_id,
    'order_sn' => $order_sn,
    'user_id' => $user_id,
    'start_add_time' => $start_add_time,
    'end_add_time' => $end_add_time,
    'start_confirm_time' => $start_confirm_time,
    'end_confirm_time' => $end_confirm_time,
    'start_pay_time' => $start_pay_time,
    'end_pay_time' => $end_pay_time,
    'start_shipping_time' => $start_shipping_time,
    'end_shipping_time' => $end_shipping_time,
    'start_take_time' => $start_take_time,
    'end_take_time' => $end_take_time,
    'order_status' => $order_status,
    'shipping_status' => $shipping_status,
    'pay_status' => $pay_status,
    'mobile' => $mobile,
    'rec_id' => $rec_id,
    'goods_id' => $goods_id,
    'goods_sn' => $goods_sn,
    'order_select' => $data,
    'page_size' => $page_size,
    'page' => $page,
    'sort_by' => $sort_by,
    'sort_order' => $sort_order,
    'format' => $format
);

/* 初始化商品类 */
$order = new \App\Plugins\Dscapi\app\controller\order($val);

switch ($method) {

    /**
     * 获取订单列表
     */
    case 'dsc.order.list.get':

        $table = array(
            'order' => 'order_info'
        );

        $result = $order->get_order_list($table);

        die($result);
        break;

    /**
     * 获取单条订单信息
     */
    case 'dsc.order.info.get':

        $table = array(
            'order' => 'order_info'
        );

        $result = $order->get_order_info($table);

        die($result);
        break;

    /**
     * 插入订单信息
     */
    case 'dsc.order.insert.post':

        $table = array(
            'order' => 'order_info'
        );

        $result = $order->get_order_insert($table);

        die($result);
        break;

    /**
     * 更新订单信息
     */
    case 'dsc.order.update.post':

        $table = array(
            'order' => 'order_info'
        );

        $result = $order->get_order_update($table);

        die($result);
        break;

    /**
     * 删除订单信息
     */
    case 'dsc.order.del.get':

        $table = array(
            'order' => 'order_info'
        );

        $result = $order->get_order_delete($table);

        die($result);
        break;

    /**
     * 获取订单商品列表
     */
    case 'dsc.order.goods.list.get':

        $table = array(
            'goods' => 'order_goods',
            'order' => 'order_info'
        );

        $result = $order->get_order_goods_list($table);

        die($result);
        break;

    /**
     * 获取单条订单商品信息
     */
    case 'dsc.order.goods.info.get':

        $table = array(
            'goods' => 'order_goods',
            'order' => 'order_info'
        );

        $result = $order->get_order_goods_info($table);

        die($result);
        break;

    /**
     * 插入订单商品信息
     */
    case 'dsc.order.goods.insert.post':

        $table = array(
            'goods' => 'order_goods',
            'order' => 'order_info'
        );

        $result = $order->get_order_goods_insert($table);

        die($result);
        break;

    /**
     * 更新订单商品信息
     */
    case 'dsc.order.goods.update.post':

        $table = array(
            'goods' => 'order_goods',
            'order' => 'order_info'
        );

        $result = $order->get_order_goods_update($table);

        die($result);
        break;

    /**
     * 删除订单商品信息
     */
    case 'dsc.order.goods.del.get':

        $table = array(
            'goods' => 'order_goods',
            'order' => 'order_info'
        );

        $result = $order->get_order_goods_delete($table);

        die($result);
        break;

    /**
     * 订单发货
     */
    case 'dsc.order.confirmorder.post':

        $table = array(
            'order' => 'order_info'
        );

        $result = $order->get_order_confirmorder($table);

        die($result);
        break;

    default:

        echo "非法接口连接";
        break;
}
