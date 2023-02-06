<?php

namespace App\Services\Order;

use App\Models\DeliveryOrder;
use App\Models\Goods;
use App\Models\GoodsInventoryLogs;
use App\Models\Products;
use App\Models\ProductsArea;
use App\Models\ProductsWarehouse;
use App\Models\Users;
use App\Models\VirtualCard;
use App\Models\WarehouseAreaGoods;
use App\Models\WarehouseGoods;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Goods\GoodsWarehouseService;
use App\Services\Merchant\MerchantCommonService;
use Illuminate\Support\Facades\Log;

/**
 * 订单发货
 * Class OrderDeliveryService
 * @package App\Services\Order
 */
class OrderDeliveryService extends OrderManageService
{
    /**
     * 根据订单查询发货单
     * @param $order_id
     * @return mixed
     */
    public function getDeliveryOrderByOrderId($order_id)
    {
        return DeliveryOrder::select('delivery_sn', 'invoice_no', 'shipping_name')->where('order_id', $order_id)->get();
    }

    /**
     * 根据发货单号查询发货单
     * @param $delivery_sn
     * @return array
     */
    public function getDeliveryOrderBySn($delivery_sn)
    {
        $result = DeliveryOrder::where('delivery_sn', $delivery_sn)->first();

        return collect($result)->toArray();
    }

    /**
     * 待发货订单列表 按订单商品数量展示订单
     *
     * @param int $page
     * @param int $ru_id
     * @return array
     * @throws \Exception
     */
    public function order_delivery_list($page = 0, $ru_id = 0)
    {
        $adminru['ru_id'] = $ru_id;
        // 待发货订单
        $_REQUEST['serch_type'] = 8;

        $arr = parent::orderList($page, $adminru);

        $order = [];
        if (!empty($arr['orders'])) {
            // 订单商品是否生成发货单
            foreach ($arr['orders'] as $key => $val) {

                $order_goods_list = $val['goods_list'] ?? [];
                if (!empty($order_goods_list)) {
                    $order_goods_list = collect($order_goods_list)->map(function ($item, $key) {

                        $goods_id = $item['goods_id'];
                        $goods_attr = $item['goods_attr'] ?? '';
                        $product_id = $item['product_id'] ?? '';
                        $model = DeliveryOrder::where('order_id', $item['order_id']);
                        $model = $model->whereHasIn('getDeliveryGoods', function ($query) use ($goods_id, $goods_attr, $product_id) {
                            $query->where('goods_id', $goods_id)->where(function ($query) use ($goods_id, $goods_attr, $product_id) {
                                $query->where('goods_attr', $goods_attr)->orWhere('product_id', $product_id);
                            });
                        });
                        $model = $model->select('delivery_id', 'shipping_name', 'invoice_no');

                        $item['delivery'] = BaseRepository::getToArrayFirst($model);

                        return $item;
                    });

                    $order_goods_list = $order_goods_list->all();

                    $arr['orders'][$key]['goods_list'] = $order_goods_list;
                }
            }

            // 按订单商品数量展示订单列表
            $order = $this->transformOrderGoods($arr['orders']);
        }

        $arr['orders'] = $order;

        return $arr;
    }

    /**
     * 格式化处理订单商品详单（按订单商品数量展示订单列表）
     * @param array $row
     * @return array
     */
    protected function transformOrderGoods($row = [])
    {
        if (empty($row)) {
            return [];
        }

        $order = [];
        foreach ($row as $key => $value) {

            // 订单商品
            foreach ($value['goods_list'] as $k => $val) {
                $order[] = array_merge($value, $val);
            }
            unset($row[$key]['goods_list']);
        }

        // 按 order_id 重新排序
        $order = collect($order)->sortBy('order_id')->values()->all();

        return $order;
    }

    /**
     * 格式化需要导出Excel的 订单商品数据
     * @param array $orders
     * @return array
     */
    public function transformOrderForExcel($orders = [])
    {
        if (empty($orders)) {
            return [];
        }

        $order_list = [];

        foreach ($orders as $key => $item) {

            // 订单商品信息
            $row['rec_id'] = $item['rec_id']; // 订单商品编号ID
            $goods_sn = rtrim($item['goods_sn'] ?? ''); // 去除最末位换行符
            $row['goods_sn'] = $goods_sn; // 商品货号
            $row['goods_number'] = $item['goods_number'] ?? 1;
            $row['goods_price'] = $item['goods_price'] ?? 0;
            $row['cost_price'] = $item['cost_price'] ?? 0;
            if (file_exists(MOBILE_DRP)) {
                $row['drp_money'] = $item['drp_money'] ?? 0;
            }

            $goods_info = !empty($item['goods_attr']) ? rtrim($item['goods_name'] ?? '') . "(" . rtrim($item['goods_attr']) . ")" : rtrim($item['goods_name'] ?? ''); // 去除最末位换行符
            $row['goods_info'] = "\"$goods_info\""; // 转义字符是关键 不然不是表格内换行
            $row['goods_info'] = $goods_info; // 商品信息

            // 订单信息
            $row['postscript'] = $item['postscript'];
            if (!empty($row['postscript'])) {
                $postscript = explode('|', $row['postscript']);
                $row['postscript'] = $postscript[1] ?? '';
            }
            $row['order_sn'] = $item['order_sn']; //订单号前加'#',避免被四舍五入 by wu
            $row['buyer'] = $item['buyer'];
            $row['add_time'] = $item['short_order_time'];
            $row['consignee'] = $item['consignee'];
            $row['mobile'] = !empty($item['mobile']) ? $item['mobile'] : $item['tel'];
            $row['address'] = addslashes(str_replace(",", "，", "[" . $item['region'] . "] " . $item['address']));
            $row['order_amount'] = $item['order_amount'];
            $row['goods_amount'] = $item['goods_amount'];
            $row['shipping_fee'] = $item['old_shipping_fee'];//配送费用
            $row['insure_fee'] = $item['insure_fee'];//保价费用
            $row['pay_fee'] = $item['pay_fee'];//支付费用
            if (CROSS_BORDER === true) { // 跨境多商户
                $row['rate_fee'] = $item['rate_fee'];//综合税费
                $row['rel_name'] = $item['rel_name'];//真实姓名
                $row['id_num'] = $item['id_num'];//身份证号
            }
            $row['surplus'] = $item['surplus'];//余额费用
            $row['money_paid'] = $item['money_paid'];//已付款金额
            $row['integral_money'] = $item['integral_money'];//积分金额
            $row['bonus'] = $item['bonus'];//红包金额
            $row['tax'] = $item['tax'];//发票税额
            $row['discount'] = $item['discount'];//折扣金额
            $row['coupons'] = $item['coupons'];//优惠券金额
            $row['value_card'] = $item['value_card']; // 储值卡
            $row['seller_name'] = $item['shop_name'] ?? ''; //商家名称
            $row['order_status'] = lang('admin/order.os.' . $item['order_status']);
            $row['pay_status'] = lang('admin/order.ps.' . $item['pay_status']);
            $row['shipping_status'] = lang('admin/order.ss.' . $item['shipping_status']);
            // 订单信息
            $row['pay_time'] = empty($item['pay_time']) ? '' : TimeRepository::getLocalDate(config('shop.time_format'), $item['pay_time']);
            $froms = $item['referer'];

            if ($froms == 'touch') {
                $row['froms'] = "WAP";
            } elseif ($froms == 'mobile' || $froms == 'app') {
                $row['froms'] = "APP";
            } elseif ($froms == 'H5') {
                $row['froms'] = "H5";
            } elseif ($froms == 'wxapp') {
                $row['froms'] = lang('admin/order.wxapp');
            } elseif ($froms == 'ecjia-cashdesk') {
                $row['froms'] = lang('admin/order.cashdesk');
            } else {
                $row['froms'] = "PC";
            }

            $row['pay_name'] = $item['pay_name'];
            $row['total_fee'] = $item['total_fee']; // 总金额
            $row['total_fee_order'] = $item['total_fee_order']; // 订单总金额

            // 导出字段 订单商品编号ID,订单号,商品名称,商品货号,商品规格,商品结算价格,商品数量,结算价小计,买家备注,商家名称,下单会员,下单时间,付款时间,收货人,联系电话,收货地址,确认状态,付款状态,发货状态,订单来源,支付方式
            // 增加 物流公司,物流运单号,已发货数量,发货数量
            $row['send_number'] = 0; // 订单商品已发货数量
            $row['sending_number'] = 0; // 发货数量
            $row['shipping_name'] = $item['shipping_name'];
            $row['invoice_no'] = '';
            if (isset($item['delivery']) && !empty($item['delivery'])) {
                $row['send_number'] = $item['send_number'] ?? 0; // 订单商品已发货数量
                $row['shipping_name'] = $item['delivery']['shipping_name'] ?? '';
                $row['invoice_no'] = $item['delivery']['invoice_no'] ?? '';
            }

            $order_list[] = $row;
        }

        return $order_list;
    }

    /**
     * 格式化处理 导入商品详单信息 （按订单号展示订单商品信息）
     * @param array $result
     * @return array
     */
    public function transformImportOrderGoods($result = [])
    {
        if (empty($result)) {
            return [];
        }

        $order = [];
        foreach ($result as $key => $value) {
            // 去除 #
            $value['order_sn'] = trim(str_replace(["#"], '', $value['order_sn']));

            //$order[$value['order_sn']][] = $value;  //根据 order_sn 进行数组重新赋值

            // 合并订单
            $order[$value['order_sn']]['order_sn'] = $value['order_sn'];
            $order[$value['order_sn']]['seller_name'] = $value['seller_name'];
            $order[$value['order_sn']]['buyer'] = $value['buyer'];
            $order[$value['order_sn']]['add_time'] = $value['add_time'];
            $order[$value['order_sn']]['consignee'] = $value['consignee'];
            $order[$value['order_sn']]['mobile'] = $value['mobile'];
            $order[$value['order_sn']]['address'] = $value['address'];
            $order[$value['order_sn']]['order_status'] = $value['order_status'];
            $order[$value['order_sn']]['pay_status'] = $value['pay_status'];
            $order[$value['order_sn']]['shipping_status'] = $value['shipping_status'];

            // 合并订单商品
            $order[$value['order_sn']]['goods_list'][] = $value;
        }

        if (!empty($order)) {
            $order = collect($order)->values()->all();
        }

        return $order;
    }

    /**
     * 取得订单商品
     * @param array $order
     * @return array
     */
    public function getNewOrderGoods($order = [])
    {
        $result = $this->getOrderGoods($order);

        if (!empty($result['goods_list'])) {
            foreach ($result['goods_list'] as $k => $value) {
                // 订单商品发货单信息
                $result['goods_list'][$k]['delivery'] = OrderDeliveryHandleService::get_delivery($order['order_sn'], $value['goods_id'], $value['goods_attr'], $value['product_id']);
            }
        }

        return $result; // ['goods_list' => $goods_list, 'attr' => $attr];
    }

    // 去生成发货单 发货
    public function toDelivery($step = '', $order = [], $item_goods = [], $send_number = [], $action_user = '', $admin_id = 0)
    {
        $action_status = [];

        if (!empty($item_goods['sending_number'])) {

            // 一件订单商品
            if ($step == 'one-4') {
                // 一件订单商品运单号 为空 ===== 不处理
            } elseif ($step == 'one-1') {
                // 一件订单商品运单号 有值 且发货数量不为空
                $delivery_status = $this->splitDeliveryShip($order, $item_goods, $send_number, $action_user, $admin_id);
            }

            // 多件订单商品
            if ($step == 'many-1') {
                // 订单商品 运单号一致
                $delivery_status = $this->oneKeyDelivery($order, $item_goods, $send_number, $action_user, $admin_id);

            } elseif ($step == 'many-2') {
                // 订单商品 运单号不一致  生成不同的发货单
                $delivery_status = $this->oneKeyDeliveryInovice($order, $item_goods, $send_number, $action_user, $admin_id);

            } elseif ($step == 'many-3-0') {
                // 订单商品 运单号部分为空 空值部分  ===== 不处理

            } elseif ($step == 'many-3-1') {
                // 订单商品 运单号部分为空 有值部分
                $delivery_status = $this->splitDeliveryShip($order, $item_goods, $send_number, $action_user, $admin_id);

            } elseif ($step == 'many-4') {
                // 订单商品 运单号全部为空 ===== 不处理
            }

        } else {

            $delivery_status[$item_goods['rec_id']] = [
                'error' => 1,
                'msg' => lang('admin/order.sending_number_invoice_no_empty'),
                'rec_id' => $item_goods['rec_id'],
                'order_sn' => $order['order_sn']
            ];
        }

        if (isset($delivery_status['error']) && $delivery_status['error'] >= 1) {
            $action_status[$item_goods['rec_id']] = $delivery_status;
        }

        return $action_status;
    }

    // 生成发货单 + 发货单发货确认(单个发货)  相当于 原后台操作 operation == 'split' + act == 'delivery_ship'
    protected function splitDeliveryShip($order = [], $item_goods = [], $send_number = [], $action_user = '', $admin_id = 0)
    {
        if (empty($order) || empty($item_goods)) {
            return ['error' => 1, 'msg' => 'data is empty'];
        }

        $delivery_status = ['error' => 0, 'msg' => '', 'order_sn' => ''];

        if (!empty($order)) {

            // 数据库订单商品列表
            $_goods = $order['goods_list'];
            $goods_list = $_goods['goods_list'];

            $_lang = lang('order');

            /* 订单状态是否已全部分单检查 */
            if ($order['order_status'] == OS_SPLITED) {
                $delivery_status['error'] = 1;
                $delivery_status['msg'] = sprintf(lang('admin/order.develop_order_status_format'), $order['order_sn'], $_lang['os'][OS_SPLITED], $_lang['ss'][SS_SHIPPED_ING]);
                $delivery_status['rec_id'] = $item_goods['rec_id'];
                $delivery_status['order_sn'] = $order['order_sn'];
                return $delivery_status;
            } else {

                /**
                 * 生成发货单
                 */

                /* 检查此单发货数量填写是否正确 合并计算相同商品和货品 */
                if (!empty($send_number) && !empty($goods_list)) {
                    $goods_no_package = [];
                    $new_send_number = [];
                    $new_goods_list = [];
                    foreach ($goods_list as $key => $value) {

                        /* 去除 此单发货数量 等于 0 的商品 */
                        if (!isset($value['package_goods_list']) || !is_array($value['package_goods_list'])) {

                            // 虚拟商品不生成发货单
                            if ($value['extension_code'] == 'virtual_card' || $value['is_real'] == 0) {
                                unset($send_number[$value['rec_id']], $goods_list[$key]);
                                continue;
                            }

                            // 如果是货品则键值为商品ID与货品ID的组合
                            $_key = empty($value['product_id']) ? $value['goods_id'] : ($value['goods_id'] . '_' . $value['product_id']);

                            // 统计此单商品总发货数 合并计算相同ID商品或货品的发货数
                            if (isset($send_number[$value['rec_id']]) && $send_number[$value['rec_id']] > 0) {
                                if (empty($goods_no_package[$_key])) {
                                    $goods_no_package[$_key] = $send_number[$value['rec_id']];
                                } else {
                                    $goods_no_package[$_key] += $send_number[$value['rec_id']];
                                }
                            }

                            //去除
                            if (!isset($send_number[$value['rec_id']]) || $send_number[$value['rec_id']] <= 0) {
                                unset($send_number[$value['rec_id']], $goods_list[$key]);
                                continue;
                            }
                        } else {
                            /* 组合超值礼包信息 */
                            $goods_list[$key]['package_goods_list'] = $this->packageGoods($value['package_goods_list'], $value['goods_number'], $value['order_id'], $value['extension_code'], $value['goods_id']);

                            /* 超值礼包 */
                            foreach ($value['package_goods_list'] as $pg_key => $pg_value) {
                                // 如果是货品则键值为商品ID与货品ID的组合
                                $_key = empty($pg_value['product_id']) ? $pg_value['goods_id'] : ($pg_value['goods_id'] . '_' . $pg_value['product_id']);

                                //统计此单商品总发货数 合并计算相同ID产品的发货数
                                if (isset($send_number[$value['rec_id']]) && $send_number[$value['rec_id']] > 0) {
                                    if (empty($goods_no_package[$_key])) {
                                        $goods_no_package[$_key] = $send_number[$value['rec_id']][$pg_value['g_p']];
                                    } else {
                                        //否则已经存在此键值
                                        $goods_no_package[$_key] += $send_number[$value['rec_id']][$pg_value['g_p']];
                                    }
                                }

                                //去除
                                if (!isset($send_number[$value['rec_id']]) || $send_number[$value['rec_id']][$pg_value['g_p']] <= 0) {
                                    unset($send_number[$value['rec_id']][$pg_value['g_p']], $goods_list[$key]['package_goods_list'][$pg_key]);
                                }
                            }

                            if (count($goods_list[$key]['package_goods_list']) <= 0) {
                                unset($send_number[$value['rec_id']], $goods_list[$key]);
                                continue;
                            }
                        }

                        /* 发货数量与总量不符 */
                        if (!isset($value['package_goods_list']) || !is_array($value['package_goods_list'])) {

                            // 虚拟商品不生成发货单
                            if ($value['extension_code'] == 'virtual_card' || $value['is_real'] == 0) {
                                $delivery_status['error'] = 1;
                                $delivery_status['msg'] = lang('admin/order.act_ship_num');
                                $delivery_status['rec_id'] = $value['rec_id'];
                                $delivery_status['order_sn'] = $order['order_sn'];
                                continue;
                            }

                            $sended = $this->orderDeliveryNum($order['order_id'], $value['goods_id'], $value['product_id']);

                            if (($value['goods_number'] - $sended - $send_number[$value['rec_id']]) < 0) {

                                // 此单发货数量不能超出订单商品数量
                                //Log::info('rec_id:' . $value['rec_id'] . '此单发货数量不能超出订单商品（普通商品）数量');
                                $delivery_status['error'] = 1;
                                $delivery_status['msg'] = lang('admin/order.act_ship_num');
                                $delivery_status['rec_id'] = $value['rec_id'];
                                $delivery_status['order_sn'] = $order['order_sn'];
                                continue;
                            }
                        } else {
                            /* 超值礼包 */
                            foreach ($goods_list[$key]['package_goods_list'] as $pg_key => $pg_value) {
                                if (($pg_value['order_send_number'] - $pg_value['sended'] - $send_number[$value['rec_id']][$pg_value['g_p']]) < 0) {

                                    // 此单发货数量不能超出订单商品数量
                                    //Log::info('rec_id:' . $value['rec_id'] . '此单发货数量不能超出订单商品（超值礼包）数量');
                                    $delivery_status['error'] = 1;
                                    $delivery_status['msg'] = lang('admin/order.act_ship_num');
                                    $delivery_status['rec_id'] = $value['rec_id'];
                                    $delivery_status['order_sn'] = $order['order_sn'];
                                    continue;
                                }
                            }
                        }

                        // 去除 已经生成发货单的商品
                        $delivery = OrderDeliveryHandleService::get_delivery($order['order_sn'], $value['goods_id'], $value['goods_attr'], $value['product_id']);
                        if (!empty($delivery)) {
                            if ($delivery['status'] == DELIVERY_CREATE) {
                                // 判断 发货数量不能为空
                                if (isset($send_number[$value['rec_id']]) && $send_number[$value['rec_id']] > 0) {

                                    // 已生成发货单 可以去发货（含部分发货）
                                    //Log::info('rec_id:' . $value['rec_id'] . '已生成发货单 可以去发货（含部分发货）' . $delivery['delivery_sn']);
                                    //$delivery_status = $this->partToDelivery($order, $delivery['delivery_id'], $item_goods['invoice_no'], $action_user);

                                    $new_send_number[$value['rec_id']] = $send_number[$value['rec_id']];
                                    $delivery_invoice_no = $item_goods['invoice_no'];
                                    $shipping_name = $item_goods['shipping_name'];
                                    $new_goods_list[$key] = $goods_list[$key];

                                    // 更新发货单信息 + 更新发货单商品 发货数量
                                    $delivery_status = $this->saveOneKeyDelivery($order, $delivery, $new_goods_list, $new_send_number, $delivery_invoice_no, $shipping_name, $action_user);
                                }

                                unset($send_number[$value['rec_id']], $goods_list[$key]);
                                continue;
                            } elseif ($order['shipping_status'] != SS_SHIPPED && isset($order['order_child']) && $order['order_child'] == 0) {
                                if ($value['goods_number'] > $value['send_number']) {

                                    // 判断 发货数量不能为空
                                    if (isset($send_number[$value['rec_id']]) && $send_number[$value['rec_id']] > 0) {
                                        // 已生成发货单  部分发货 已发 $value['send_number'] 件
                                        //Log::info('rec_id:' . $value['rec_id'] . '已生成发货单 部分发货' . $delivery['delivery_sn']);

                                        $new_send_number[$value['rec_id']] = $send_number[$value['rec_id']];
                                        $delivery_invoice_no = $item_goods['invoice_no'];
                                        $shipping_name = $item_goods['shipping_name'];
                                        $new_goods_list[$key] = $goods_list[$key];

                                        // 更新发货单信息 + 更新发货单商品 发货数量
                                        $delivery_status = $this->saveOneKeyDelivery($order, $delivery, $new_goods_list, $new_send_number, $delivery_invoice_no, $shipping_name, $action_user);
                                    }

                                    unset($send_number[$value['rec_id']], $goods_list[$key]);
                                    continue;
                                }

                            } elseif ($delivery['status'] == DELIVERY_SHIPPED && $value['send_number'] > 0) {
                                // 发货单部分已生成
                                // 判断 发货数量不能为空
                                if (isset($send_number[$value['rec_id']]) && $send_number[$value['rec_id']] > 0) {
                                    //Log::info('rec_id:' . $value['rec_id'] . '发货单部分已生成 只能查看' . $delivery['delivery_sn']);
                                }

                                unset($send_number[$value['rec_id']], $goods_list[$key]);
                                continue;

                            } elseif ($value['goods_number'] == $value['send_number']) {
                                // 发货单全部已生成
                                if (isset($send_number[$value['rec_id']]) && $send_number[$value['rec_id']] > 0) {
                                    //Log::info('rec_id:' . $value['rec_id'] . '发货单全部已生成' . $delivery['delivery_sn']);
                                }

                                unset($send_number[$value['rec_id']], $goods_list[$key]);
                                continue;
                            }
                        }

                    }

                    /* 对上一步处理结果进行判断 兼容 上一步判断为假情况的处理 */
                    if ($order['is_zc_order'] == 0 && (empty($send_number) || empty($goods_list))) {
                        /* 操作失败 */
                        $delivery_status['error'] = 1;
                        $delivery_status['msg'] = lang('admin/order.order_goods_import_empty'); // 无符合条件数据 不处理
                        $delivery_status['order_id'] = $order['order_id'];
                        $delivery_status['order_sn'] = $order['order_sn'];
                        return $delivery_status;
                    }


                    /* 检查此单发货商品库存缺货情况 */
                    /* $goods_list已经过处理 超值礼包中商品库存已取得 */
                    $virtual_goods = [];
                    $package_virtual_goods = [];

                    foreach ($goods_list as $key => $value) {
                        // 商品（超值礼包）
                        if ($value['extension_code'] == 'package_buy') {
                            foreach ($value['package_goods_list'] as $pg_key => $pg_value) {
                                if ($pg_value['goods_number'] < $goods_no_package[$pg_value['g_p']] && ((config('shop.use_storage') == 1 && config('shop.stock_dec_time') == SDT_SHIP) || (config('shop.use_storage') == 0 && $pg_value['is_real'] == 0))) {

                                    // 超值礼包 商品已缺货
                                    //Log::info('rec_id:' . $value['rec_id'] . '商品已缺货（超值礼包）');
                                    $delivery_status['error'] = 1;
                                    $delivery_status['msg'] = sprintf(lang('admin/order.act_good_vacancy'), $pg_value['goods_name']);
                                    $delivery_status['rec_id'] = $value['rec_id'];
                                    $delivery_status['order_sn'] = $order['order_sn'];
                                    continue;
                                }

                                /* 商品（超值礼包） 虚拟商品列表 package_virtual_goods*/
                                if ($pg_value['is_real'] == 0) {
                                    $package_virtual_goods[] = [
                                        'goods_id' => $pg_value['goods_id'],
                                        'goods_name' => $pg_value['goods_name'],
                                        'num' => $send_number[$value['rec_id']][$pg_value['g_p']]
                                    ];
                                }
                            }
                        } elseif ($value['extension_code'] == 'virtual_card' || $value['is_real'] == 0) {

                            /* 虚拟商品列表 virtual_card*/
                            if ($value['extension_code'] == 'virtual_card') {
                                $virtual_goods[$value['extension_code']][] = ['goods_id' => $value['goods_id'], 'goods_name' => $value['goods_name'], 'num' => $send_number[$value['rec_id']]];
                            }

                            // 商品（虚货）
                            $num = VirtualCard::where('goods_id', $value['goods_id'])
                                ->where('is_saled', 0)
                                ->count();

                            if (($num < $goods_no_package[$value['goods_id']]) && !(config('shop.use_storage') == 1 && config('shop.stock_dec_time') == SDT_PLACE)) {
                                // 虚拟卡已缺货
                                //Log::info('rec_id:' . $value['rec_id'] . '商品已缺货（虚拟卡）');
                                $delivery_status['error'] = 1;
                                $delivery_status['msg'] = sprintf(lang('admin/common.virtual_card_oos') . '【' . $value['goods_name'] . '】');
                                $delivery_status['rec_id'] = $value['rec_id'];
                                $delivery_status['order_sn'] = $order['order_sn'];
                                continue;
                            }

                        } else {
                            // 商品（实货）、（货品）
                            // 如果是货品则键值为商品ID与货品ID的组合
                            $_key = empty($value['product_id']) ? $value['goods_id'] : ($value['goods_id'] . '_' . $value['product_id']);
                            $num = $value['storage']; //ecmoban模板堂 --zhuo

                            if (($num < $goods_no_package[$_key]) && config('shop.use_storage') == 1 && config('shop.stock_dec_time') == SDT_SHIP) {
                                // 商品已缺货
                                //Log::info('rec_id:' . $value['rec_id'] . '商品已缺货（普通商品）');
                                $delivery_status['error'] = 1;
                                $delivery_status['msg'] = sprintf(lang('admin/order.act_good_vacancy'), $value['goods_name']);
                                $delivery_status['rec_id'] = $value['rec_id'];
                                $delivery_status['order_sn'] = $order['order_sn'];
                                continue;
                            }
                        }
                    }

                    if ($delivery_status['error'] >= 1) {
                        return $delivery_status;
                    }

                    // 订单商品 全部为 虚拟商品 不生成发货单
                    if (!empty($virtual_goods) && !empty($goods_list) && count($virtual_goods) == count($goods_list)) {
                        /* 操作失败 */
                        $delivery_status['error'] = 1;
                        $delivery_status['msg'] = lang('admin/order.order_goods_import_empty'); // 无符合条件数据 不处理
                        $delivery_status['order_id'] = $order['order_id'];
                        $delivery_status['order_sn'] = $order['order_sn'];
                        return $delivery_status;
                    }

                    // 全部发货 （新增发货单、并修改订单状态 发货中）
                    $delivery_id = $this->makeDelivery($order, $goods_list, $send_number, $action_user);

                    // 返回结果
                    if ($delivery_id && $delivery_id > 0) {
                        $delivery_status['error'] = 0;
                        $delivery_status['msg'] = 'create delivery success';
                        $delivery_status['delivery_id'] = $delivery_id;
                        $delivery_status['order_sn'] = $order['order_sn'];
                    }
                }
            }


            /**
             * 发货
             */

            /* 订单配送状态是否发货检查 不等于已发货 */
            if ($order['shipping_status'] != SS_SHIPPED) {

                $delivery_invoice_no = empty($item_goods['invoice_no']) ? '' : trim($item_goods['invoice_no']);  //快递单号
                $shipping_name = empty($item_goods['shipping_name']) ? '' : trim($item_goods['shipping_name']);  //快递公司

                $delivery_info = get_delivery_info($order['order_id']);
                if (!empty($delivery_invoice_no) && !empty($delivery_info) && isset($delivery_id) && $delivery_id > 0) {

                    $delivery_status = $this->partToDelivery($order, $delivery_id, $delivery_invoice_no, $shipping_name, $action_user, $admin_id);
                }
            } else {
                // 已发货
                $delivery_status['error'] = 1;
                $delivery_status['msg'] = sprintf(lang('admin/order.develop_order_status_format'), $order['order_sn'], $_lang['os'][$order['order_status']], $_lang['ss'][$order['shipping_status']]);
                $delivery_status['rec_id'] = $item_goods['rec_id'];
                $delivery_status['order_sn'] = $order['order_sn'];
            }
        }

        return $delivery_status;
    }

    // 一键发货 （生成发货单、更新发货单、更新订单发货状态） 相当于 原后台操作 act == 'operate' name == to_shipping
    public function oneKeyDelivery($order = [], $item_goods = [], $send_number = [], $action_user = '', $admin_id = 0)
    {
        if (empty($order) || empty($item_goods)) {
            return ['error' => 1, 'msg' => 'data is empty'];
        }

        $delivery_status = ['error' => 0, 'msg' => '', 'order_sn' => ''];

        if (!empty($order)) {

            $_lang = lang('order');

            $delivery_invoice_no = empty($item_goods['invoice_no']) ? '' : trim($item_goods['invoice_no']);  //快递单号
            $shipping_name = empty($item_goods['shipping_name']) ? '' : trim($item_goods['shipping_name']);  //快递公司

            /* 订单配送状态是否发货检查 不等于已发货 */
            if ($order['shipping_status'] != SS_SHIPPED && !empty($delivery_invoice_no)) {

                // 数据库订单商品列表
                $_goods = $order['goods_list'] ?? [];

                $attr = $_goods['attr'];
                $goods_list = $_goods['goods_list'];
                unset($_goods);

                /* 查询：商品已发货数量 此单可发货数量 */
                if (!empty($goods_list)) {
                    foreach ($goods_list as $key => $goods_value) {
                        if (!$goods_value['goods_id']) {
                            continue;
                        }

                        /* 超级礼包 */
                        if (($goods_value['extension_code'] == 'package_buy') && (count($goods_value['package_goods_list']) > 0)) {
                            $goods_list[$key]['package_goods_list'] = $this->packageGoods($goods_value['package_goods_list'], $goods_value['goods_number'], $goods_value['order_id'], $goods_value['extension_code'], $goods_value['goods_id']);
                            foreach ($goods_list[$key]['package_goods_list'] as $pg_key => $pg_value) {
                                $goods_list[$key]['package_goods_list'][$pg_key]['readonly'] = '';
                                /* 使用库存 是否缺货 */
                                if ($pg_value['storage'] <= 0 && config('shop.use_storage') == 1 && config('shop.stock_dec_time') == SDT_SHIP) {
                                    $goods_list[$key]['package_goods_list'][$pg_key]['send'] = lang('admin/order.act_good_vacancy');
                                    $goods_list[$key]['package_goods_list'][$pg_key]['readonly'] = 'readonly="readonly"';
                                    unset($send_number[$goods_value['rec_id']], $goods_list[$key]);

                                } /* 将已经全部发货的商品设置为只读 */
                                elseif ($pg_value['send'] <= 0) {
                                    $goods_list[$key]['package_goods_list'][$pg_key]['send'] = lang('admin/order.act_good_delivery');
                                    $goods_list[$key]['package_goods_list'][$pg_key]['readonly'] = 'readonly="readonly"';

                                    unset($send_number[$goods_value['rec_id']], $goods_list[$key]);
                                }
                            }
                        } elseif ($goods_value['extension_code'] == 'virtual_card' || $goods_value['is_real'] == 0) {

                            /* 虚拟商品列表 virtual_card*/
                            if ($goods_value['extension_code'] == 'virtual_card') {
                                $virtual_goods[$goods_value['extension_code']][] = ['goods_id' => $goods_value['goods_id']];
                            }

                            unset($send_number[$goods_value['rec_id']], $goods_list[$key]);

                        } else {
                            $goods_list[$key]['sended'] = $goods_value['send_number'];
                            $goods_list[$key]['sended'] = $goods_value['goods_number'];
                            $goods_list[$key]['send'] = $goods_value['goods_number'] - $goods_value['send_number'];
                            $goods_list[$key]['readonly'] = '';
                            /* 是否缺货 */
                            if ($goods_value['storage'] <= 0 && config('shop.use_storage') == 1 && config('shop.stock_dec_time') == SDT_SHIP) {
                                $goods_list[$key]['send'] = lang('admin/order.act_good_vacancy');
                                $goods_list[$key]['readonly'] = 'readonly="readonly"';

                                unset($send_number[$goods_value['rec_id']], $goods_list[$key]);

                            } elseif ($goods_list[$key]['send'] <= 0) {
                                $goods_list[$key]['send'] = lang('admin/order.act_good_delivery');
                                $goods_list[$key]['readonly'] = 'readonly="readonly"';

                                unset($send_number[$goods_value['rec_id']], $goods_list[$key]);
                            }
                        }
                    }
                }

                // 订单商品 全部为 虚拟商品 不生成发货单
                if (!empty($virtual_goods) && !empty($goods_list) && count($virtual_goods) == count($goods_list)) {
                    /* 操作失败 */
                    $delivery_status['error'] = 1;
                    $delivery_status['msg'] = lang('admin/order.order_goods_import_empty'); // 无符合条件数据 不处理
                    $delivery_status['rec_id'] = $item_goods['rec_id'];
                    $delivery_status['order_sn'] = $order['order_sn'];
                    return $delivery_status;
                }

                $delivery_info = get_delivery_info($order['order_id']);
                if (!$delivery_info) {

                    // 一键发货 之 生成发货单、生成发货单商品
                    $delivery_id = $this->makeOneKeyDelivery($order, $goods_list, $send_number, $shipping_name, $action_user);
                    // 去发货
                    if ($delivery_id && $delivery_id > 0) {

                        $delivery_status = $this->partToDelivery($order, $delivery_id, $delivery_invoice_no, $shipping_name, $action_user, $admin_id);
                    }
                } else {

                    // 一键发货 之 更新发货单信息 + 更新发货单商品 发货数量
                    $delivery_status = $this->saveOneKeyDelivery($order, $delivery_info, $goods_list, $send_number, $delivery_invoice_no, $shipping_name, $action_user);
                }
            } else {

                if (empty($delivery_invoice_no)) {
                    // 运单号为空
                    $delivery_status['error'] = 1;
                    $delivery_status['msg'] = sprintf(lang('admin/order.delivery_invoice_no_empty'), $order['order_sn']);
                    $delivery_status['rec_id'] = $item_goods['rec_id'];
                    $delivery_status['order_sn'] = $order['order_sn'];
                } else {
                    // 已发货
                    $delivery_status['error'] = 1;
                    $delivery_status['msg'] = sprintf(lang('admin/order.develop_order_status_format'), $order['order_sn'], $_lang['os'][$order['order_status']], $_lang['ss'][$order['shipping_status']]);
                    $delivery_status['rec_id'] = $item_goods['rec_id'];
                    $delivery_status['order_sn'] = $order['order_sn'];
                }

            }
        }

        return $delivery_status;
    }

    // 全部发货 （新增发货单、并修改订单状态 发货中） - OrderController.php 'split' == $operation
    public function makeDelivery($order = [], $goods_list = [], $send_number = [], $action_user = '')
    {
        if (empty($order) || empty($goods_list) || empty($send_number)) {
            return false;
        }

        // 分单操作记录
        $split_action_note = "";

        $now = TimeRepository::getGmTime();

        // 生成发货单
        $delivery = [
            'delivery_sn' => get_delivery_sn(),
            'update_time' => $now,
            'status' => DELIVERY_CREATE, // 2 生成发货单
            'action_user' => $action_user,
            'add_time' => $order['add_time'],
            'suppliers_id' => 0,
            'order_id' => $order['order_id'],
            'order_sn' => $order['order_sn'],
            'user_id' => $order['user_id'],
            'shipping_id' => $order['shipping_id'],
            'shipping_fee' => $order['shipping_fee'],
            'consignee' => $order['consignee'],
            'address' => $order['address'],
            'country' => $order['country'],
            'province' => $order['province'],
            'city' => $order['city'],
            'district' => $order['district'],
            'sign_building' => $order['sign_building'],
            'email' => $order['email'],
            'zipcode' => $order['zipcode'],
            'tel' => $order['tel'],
            'mobile' => $order['mobile'],
            'best_time' => $order['best_time'],
            'postscript' => $order['postscript'],
            'how_oos' => $order['how_oos'],
            'insure_fee' => $order['insure_fee'],
            'agency_id' => $order['agency_id'],
            'shipping_name' => $order['shipping_name'],
        ];

        $virtual_goods = [];

        $delivery_id = OrderDeliveryHandleService::create_delivery_order($delivery);
        if ($delivery_id > 0) {

            // 发货单商品入库
            if (!empty($goods_list)) {
                foreach ($goods_list as $val) {
                    // 实物商品+活动商品  排除虚拟商品
                    if (empty($val['extension_code']) || in_array($val['extension_code'], ['presale', 'bargain_buy', 'bargain']) || substr($val['extension_code'], 0, 7) == 'seckill') {
                        $delivery_goods = [
                            'delivery_id' => $delivery_id,
                            'goods_id' => $val['goods_id'],
                            'product_id' => $val['product_id'],
                            'product_sn' => $val['product_sn'],
                            'goods_name' => addslashes($val['goods_name']),
                            'brand_name' => addslashes($val['brand_name']),
                            'goods_sn' => $val['goods_sn'],
                            'send_number' => $send_number[$val['rec_id']],
                            'parent_id' => 0,
                            'extension_code' => '',
                            'is_real' => $val['is_real'],
                            'goods_attr' => addslashes($val['goods_attr'])
                        ];

                        /* 如果是货品 */
                        if (!empty($val['product_id'])) {
                            $delivery_goods['product_id'] = $val['product_id'];
                        }

                        OrderDeliveryHandleService::create_delivery_goods($delivery_goods);

                        // 分单操作
                        $split_action_note .= sprintf(lang('admin/order.split_action_note'), $val['goods_sn'], $send_number[$val['rec_id']]) . "<br/>";

                    } elseif ($val['extension_code'] == 'package_buy') {
                        // 商品（超值礼包）
                        foreach ($val['package_goods_list'] as $pg_key => $pg_value) {
                            $delivery_pg_goods = [
                                'delivery_id' => $delivery_id,
                                'goods_id' => $pg_value['goods_id'],
                                'product_id' => $pg_value['product_id'],
                                'product_sn' => $pg_value['product_sn'],
                                'goods_name' => $pg_value['goods_name'],
                                'brand_name' => '',
                                'goods_sn' => $pg_value['goods_sn'],
                                'send_number' => $send_number[$val['rec_id']],
                                'parent_id' => $val['goods_id'], // 礼包ID
                                'extension_code' => $val['extension_code'], // 礼包
                                'is_real' => $pg_value['is_real']
                            ];

                            OrderDeliveryHandleService::create_delivery_goods($delivery_pg_goods);
                        }

                        //分单操作
                        $split_action_note .= sprintf(lang('admin/order.split_action_note'), lang('admin/common.14_package_list'), 1) . "<br/>";
                    } elseif ($val['extension_code'] == 'virtual_card' || $val['is_real'] == 0) {

                        /* 虚拟商品列表 virtual_card*/
                        if ($val['extension_code'] == 'virtual_card') {
                            $virtual_goods[$val['extension_code']][] = ['goods_id' => $val['goods_id']];
                        }
                    }
                }
            }

            // 更新订单信息
            $_goods = $order['goods_list'];

            $_note = sprintf(lang('admin/order.order_ship_delivery'), $delivery['delivery_sn']) . "<br/>";

            $_sended = &$send_number;
            foreach ($_goods['goods_list'] as $key => $value) {
                if ($value['extension_code'] != 'package_buy') {
                    unset($_goods['goods_list'][$key]);
                }
            }
            foreach ($goods_list as $key => $value) {
                if ($value['extension_code'] == 'package_buy') {
                    unset($goods_list[$key]);
                }
            }
            $_goods['goods_list'] = $goods_list + $_goods['goods_list'];
            unset($goods_list);

            /* 更新订单的虚拟卡 商品（虚货） */
            $_virtual_goods = isset($virtual_goods['virtual_card']) ? $virtual_goods['virtual_card'] : '';
            app(OrderManageService::class)->updateOrderVirtualGoods($order['order_id'], $_sended, $_virtual_goods);

            /* 更新订单的非虚拟商品信息 即：商品（实货）（货品）、商品（超值礼包）*/
            $this->updateOrderGoods($order['order_id'], $_sended, $_goods['goods_list']);

            if (true) {
                /* 标记订单为已确认 “发货中” */
                /* 更新发货时间 */
                $order_finish = $this->getOrderFinish($order['order_id']);
                $shipping_status = SS_SHIPPED_ING;
                if ($order['order_status'] != OS_CONFIRMED && $order['order_status'] != OS_SPLITED && $order['order_status'] != OS_SPLITING_PART) {
                    $arr['order_status'] = OS_CONFIRMED;
                    $arr['confirm_time'] = $now;
                }
                $arr['order_status'] = $order_finish ? OS_SPLITED : OS_SPLITING_PART; // 全部分单、部分分单
                $arr['shipping_status'] = $shipping_status;
                Log::info($order['order_sn'] . ':' . '更新订单发货状态：发货中');
                update_order($order['order_id'], $arr);

                /* 分单操作 */
                $action_note = $split_action_note . 'batch';

                /* 记录log */
                order_action($order['order_sn'], $arr['order_status'], $shipping_status, $order['pay_status'], $_note . $action_note, $action_user);
            }

            return $delivery_id;
        }

        return 0;
    }

    // 部分发货 （更新发货单、并修改订单状态 已发货） - OrderController.php act = delivery_ship
    public function partToDelivery($order = [], $delivery_id = 0, $delivery_invoice_no = '', $shipping_name = '', $action_user = '', $admin_id = 0)
    {
        $now = TimeRepository::getGmTime();

        $delivery_status = ['error' => 0, 'msg' => '', 'order_sn' => ''];

        /* 根据发货单id查询发货单信息 */
        if (!empty($delivery_invoice_no) && !empty($delivery_id)) {
            //$delivery_order = $this->publicOrderService->get_delivery_order($delivery_id);
        } else {
            $delivery_status['error'] = 1;
            $delivery_status['msg'] = lang('admin.order.delivery_status_info_empty');
            $delivery_status['order_id'] = $order['order_id'];
            $delivery_status['order_sn'] = $order['order_sn'];
            return $delivery_status;
        }

        /* 检查此单发货商品库存缺货情况  ecmoban模板堂 --zhuo start 下单减库存*/
        $delivery_stock_sql = "SELECT DG.rec_id AS dg_rec_id, OG.rec_id AS og_rec_id, G.model_attr, G.model_inventory, DG.goods_id, DG.delivery_id, DG.is_real, DG.send_number AS sums, G.goods_number AS storage, G.goods_name, DG.send_number," .
            " OG.goods_attr_id, OG.warehouse_id, OG.area_id, OG.area_city, OG.ru_id, OG.order_id, OG.product_id FROM " . $GLOBALS['dsc']->table('delivery_goods') . " AS DG, " .
            $GLOBALS['dsc']->table('goods') . " AS G, " .
            $GLOBALS['dsc']->table('delivery_order') . " AS D, " .
            $GLOBALS['dsc']->table('order_goods') . " AS OG " .
            " WHERE DG.goods_id = G.goods_id AND DG.delivery_id = D.delivery_id AND D.order_id = OG.order_id AND DG.goods_sn = OG.goods_sn AND DG.product_id = OG.product_id AND DG.delivery_id = '$delivery_id' GROUP BY OG.rec_id ";

        $delivery_stock_result = $GLOBALS['db']->getAll($delivery_stock_sql);

        $virtual_goods = [];
        for ($i = 0; $i < count($delivery_stock_result); $i++) {
            $res = Products::whereRaw(1);
            if ($delivery_stock_result[$i]['model_attr'] == 1) {
                $res = ProductsWarehouse::where('warehouse_id', $delivery_stock_result[$i]['warehouse_id']);
            } elseif ($delivery_stock_result[$i]['model_attr'] == 2) {
                $res = ProductsArea::where('area_id', $delivery_stock_result[$i]['area_id']);
            }

            $res = $res->where('goods_id', $delivery_stock_result[$i]['goods_id']);
            $prod = BaseRepository::getToArrayFirst($res);

            /* 如果商品存在规格就查询规格，如果不存在规格按商品库存查询 */
            if (empty($prod)) {
                if ($delivery_stock_result[$i]['model_inventory'] == 1) {
                    $delivery_stock_result[$i]['storage'] = get_warehouse_area_goods($delivery_stock_result[$i]['warehouse_id'], $delivery_stock_result[$i]['goods_id'], 'warehouse_goods');
                } elseif ($delivery_stock_result[$i]['model_inventory'] == 2) {
                    $delivery_stock_result[$i]['storage'] = get_warehouse_area_goods($delivery_stock_result[$i]['area_id'], $delivery_stock_result[$i]['goods_id'], 'warehouse_area_goods');
                }
            } else {
                $products = app(GoodsWarehouseService::class)->getWarehouseAttrNumber($delivery_stock_result[$i]['goods_id'], $delivery_stock_result[$i]['goods_attr_id'], $delivery_stock_result[$i]['warehouse_id'], $delivery_stock_result[$i]['area_id'], $delivery_stock_result[$i]['area_city'], $delivery_stock_result[$i]['model_attr']);
                $delivery_stock_result[$i]['storage'] = $products['product_number'];
            }

            if (($delivery_stock_result[$i]['sums'] > $delivery_stock_result[$i]['storage'] || $delivery_stock_result[$i]['storage'] <= 0) && ((config('shop.use_storage') == 1 && config('shop.stock_dec_time') == SDT_SHIP) || (config('shop.use_storage') == 0 && $delivery_stock_result[$i]['is_real'] == 0))) {
                /* 操作失败 */
                $delivery_status['error'] = 1;
                $delivery_status['msg'] = sprintf(lang('admin/order.act_good_vacancy'), $delivery_stock_result[$i]['goods_name']);
                $delivery_status['delivery_id'] = $delivery_id;
                return $delivery_status;
            }

            /* 虚拟商品列表 virtual_card*/
            if ($delivery_stock_result[$i]['is_real'] == 0) {
                $virtual_goods[] = [
                    'goods_id' => $delivery_stock_result[$i]['goods_id'],
                    'goods_name' => $delivery_stock_result[$i]['goods_name'],
                    'num' => $delivery_stock_result[$i]['send_number']
                ];
            }
        }
        //ecmoban模板堂 --zhuo end 下单减库存

        /* 发货 */
        /* 处理虚拟卡 商品（虚货） */
        if ($virtual_goods && is_array($virtual_goods) && count($virtual_goods) > 0) {
            foreach ($virtual_goods as $virtual_value) {
                virtual_card_shipping($virtual_value, $order['order_sn'], $msg, 'split');
            }

            //虚拟卡缺货
            if (!empty($msg)) {
                $delivery_status['error'] = 1;
                $delivery_status['msg'] = lang('admin/order.delivery_status_virtual_goods_stockout') . ',' . lang('admin/order.act_false');
                $delivery_status['order_id'] = $order['order_id'];
                return $delivery_status;
            }
        }

        /* 如果使用库存，且发货时减库存，则修改库存 */
        if (config('shop.use_storage') == 1 && config('shop.stock_dec_time') == SDT_SHIP) {
            foreach ($delivery_stock_result as $value) {

                /* 商品（实货）、超级礼包（实货） ecmoban模板堂 --zhuo */
                if ($value['is_real'] != 0) {
                    //（货品）
                    if (!empty($value['product_id'])) {
                        if ($value['model_attr'] == 1) {
                            ProductsWarehouse::where('product_id', $value['product_id'])
                                ->decrement('product_number', $value['sums']);
                        } elseif ($value['model_attr'] == 2) {
                            ProductsArea::where('product_id', $value['product_id'])
                                ->decrement('product_number', $value['sums']);
                        } else {
                            Products::where('product_id', $value['product_id'])
                                ->decrement('product_number', $value['sums']);
                        }
                    } else {
                        if ($value['model_inventory'] == 1) {
                            WarehouseGoods::where('goods_id', $value['goods_id'])
                                ->where('region_id', $value['warehouse_id'])
                                ->decrement('region_number', $value['sums']);
                        } elseif ($value['model_inventory'] == 2) {
                            WarehouseAreaGoods::where('goods_id', $value['goods_id'])
                                ->where('region_id', $value['area_id'])
                                ->decrement('region_number', $value['sums']);
                        } else {
                            Goods::where('goods_id', $value['goods_id'])
                                ->decrement('goods_number', $value['sums']);
                        }
                    }

                    //库存日志
                    $logs_other = [
                        'goods_id' => $value['goods_id'],
                        'order_id' => $value['order_id'],
                        'use_storage' => $GLOBALS['_CFG']['stock_dec_time'],
                        'admin_id' => session('admin_id'),
                        'number' => "- " . $value['sums'],
                        'model_inventory' => $value['model_inventory'],
                        'model_attr' => $value['model_attr'],
                        'product_id' => $value['product_id'],
                        'warehouse_id' => $value['warehouse_id'],
                        'area_id' => $value['area_id'],
                        'add_time' => $now
                    ];

                    GoodsInventoryLogs::insert($logs_other);
                }
            }
        }

        /* 修改发货单信息 */
        $invoice_no = str_replace(['|', ','], ',', $delivery_invoice_no);
        $invoice_no = trim($invoice_no, ',');
        $_delivery['invoice_no'] = $invoice_no;
        $_delivery['shipping_name'] = $shipping_name;
        $_delivery['status'] = DELIVERY_SHIPPED; // 0，为已发货
        $query = OrderDeliveryHandleService::update_delivery_order($delivery_id, $_delivery);
        if ($query) {

            /* 标记订单为已确认 “已发货” */
            /* 更新发货时间 */
            $order_finish = $this->getAllDeliveryFinish($order['order_id']);
            $shipping_status = ($order_finish == 1) ? SS_SHIPPED : SS_SHIPPED_PART;
            $arr['shipping_status'] = $shipping_status;
            $arr['shipping_time'] = $now; // 发货时间
            $arr['invoice_no'] = !empty($order['invoice_no']) ? trim($order['invoice_no'] . ',') . ',' . $invoice_no : $invoice_no;
            Log::info($order['order_sn'] . ':' . '更新订单发货状态：已发货');
            update_order($order['order_id'], $arr);

            $action_note = 'shipping batch';

            /* 发货单发货记录log */
            order_action($order['order_sn'], OS_CONFIRMED, $shipping_status, $order['pay_status'], $action_note, $action_user, 1);

            /* 如果当前订单已经全部发货 */
            if ($order_finish) {
                $this->order_finish($order);
            }

            $delivery_status['error'] = 0;
            $delivery_status['msg'] = 'update delivery status success';
            $delivery_status['delivery_id'] = $delivery_id;
            $delivery_status['order_sn'] = $order['order_sn'];
        }

        return $delivery_status;
    }

    // 一键发货 之 生成发货单  OrderController.php  $act == 'operate' post = to_shipping
    public function makeOneKeyDelivery($order = [], $goods_list = [], $send_number = [], $shipping_name = '', $action_user = '')
    {
        if (empty($order) || empty($goods_list) || empty($send_number)) {
            return false;
        }

        $now = TimeRepository::getGmTime();

        // 生成发货单
        $delivery = [
            'delivery_sn' => get_delivery_sn(),
            'update_time' => $now,
            'status' => DELIVERY_CREATE, // 2 生成发货单
            'action_user' => $action_user,
            'add_time' => $order['add_time'],
            'suppliers_id' => 0,
            'order_id' => $order['order_id'],
            'order_sn' => $order['order_sn'],
            'user_id' => $order['user_id'],
            'shipping_id' => $order['shipping_id'],
            'shipping_fee' => $order['shipping_fee'],
            'consignee' => $order['consignee'],
            'address' => $order['address'],
            'country' => $order['country'],
            'province' => $order['province'],
            'city' => $order['city'],
            'district' => $order['district'],
            'sign_building' => $order['sign_building'],
            'email' => $order['email'],
            'zipcode' => $order['zipcode'],
            'tel' => $order['tel'],
            'mobile' => $order['mobile'],
            'best_time' => $order['best_time'],
            'postscript' => $order['postscript'],
            'how_oos' => $order['how_oos'],
            'insure_fee' => $order['insure_fee'],
            'agency_id' => $order['agency_id'],
            'shipping_name' => $shipping_name ? $shipping_name : $order['shipping_name'],
        ];
        //Log::info($delivery);
        $delivery_id = OrderDeliveryHandleService::create_delivery_order($delivery);
        if ($delivery_id > 0) {

            // 发货单商品入库
            if (!empty($goods_list)) {
                foreach ($goods_list as $val) {
                    // 实物商品+活动商品  排除虚拟商品
                    if (empty($val['extension_code']) || in_array($val['extension_code'], ['presale', 'bargain_buy', 'bargain']) || substr($val['extension_code'], 0, 7) == 'seckill') {
                        $delivery_goods = [
                            'delivery_id' => $delivery_id,
                            'goods_id' => $val['goods_id'],
                            'product_id' => $val['product_id'],
                            'product_sn' => $val['product_sn'],
                            'goods_name' => addslashes($val['goods_name']),
                            'brand_name' => addslashes($val['brand_name']),
                            'goods_sn' => $val['goods_sn'],
                            'send_number' => $send_number[$val['rec_id']],
                            'parent_id' => 0,
                            'extension_code' => '',
                            'is_real' => $val['is_real'],
                            'goods_attr' => addslashes($val['goods_attr'])
                        ];

                        /* 如果是货品 */
                        if (!empty($val['product_id'])) {
                            $delivery_goods['product_id'] = $val['product_id'];
                        }

                        //Log::info($delivery_goods);
                        $query = OrderDeliveryHandleService::create_delivery_goods($delivery_goods);

                        // 更新订单商品 已发货数量
                        $data = [
                            'send_number' => $send_number[$val['rec_id']] ?? $val['goods_number']
                        ];
                        OrderDeliveryHandleService::update_order_goods($order['order_id'], $val['goods_id'], $data);

                    } elseif ($val['extension_code'] == 'package_buy') {
                        // 商品（超值礼包）
                        foreach ($val['package_goods_list'] as $pg_key => $pg_value) {
                            $delivery_pg_goods = [
                                'delivery_id' => $delivery_id,
                                'goods_id' => $pg_value['goods_id'],
                                'product_id' => $pg_value['product_id'],
                                'product_sn' => $pg_value['product_sn'],
                                'goods_name' => $pg_value['goods_name'],
                                'brand_name' => '',
                                'goods_sn' => $pg_value['goods_sn'],
                                'send_number' => $send_number[$val['rec_id']][$pg_value['g_p']],
                                'parent_id' => $val['goods_id'], // 礼包ID
                                'extension_code' => $val['extension_code'], // 礼包
                                'is_real' => $pg_value['is_real']
                            ];
                            //Log::info($delivery_pg_goods);
                            $query = OrderDeliveryHandleService::create_delivery_goods($delivery_pg_goods);

                            // 更新订单商品 已发货数量
                            $data = [
                                'send_number' => $send_number[$val['rec_id']][$pg_value['g_p']] ?? $val['goods_number']
                            ];
                            OrderDeliveryHandleService::update_order_goods($order['order_id'], $val['goods_id'], $data);
                        }

                    }
                }

                /* 订单信息更新处理 */
                /* 标记订单为已确认 “发货中” */
                /* 更新发货时间 */
                $order_finish = $this->getOrderFinish($order['order_id']);
                $shipping_status = SS_SHIPPED_ING;
                if ($order['order_status'] != OS_CONFIRMED && $order['order_status'] != OS_SPLITED && $order['order_status'] != OS_SPLITING_PART) {
                    $arr['order_status'] = OS_CONFIRMED;
                    $arr['confirm_time'] = $now;
                }
                $arr['order_status'] = $order_finish ? OS_SPLITED : OS_SPLITING_PART; // 全部分单、部分分单
                $arr['shipping_status'] = $shipping_status;

                update_order($order['order_id'], $arr);

                /* 发货单发货记录log */
                $action_note = 'batch add';
                //Log::info($order['order_sn'] . ':' . $action_note);

                order_action($order['order_sn'], $arr['order_status'], $shipping_status, $order['pay_status'], $action_note, $action_user);

                /* 如果当前订单已经全部发货 */
                if ($order_finish) {
                    $this->order_finish($order);
                }
            }

            return $delivery_id;
        }

        return false;
    }

    // 一键发货 之 更新发货单、发货单商品发货数量
    public function saveOneKeyDelivery($order = [], $delivery_info = [], $goods_list = [], $send_number = [], $delivery_invoice_no = '', $shipping_name = '', $action_user = '')
    {
        if (empty($order) || empty($delivery_info) || empty($goods_list) || empty($send_number)) {
            return false;
        }

        $delivery_status = ['error' => 0, 'msg' => '', 'order_sn' => ''];

        $now = TimeRepository::getGmTime();

        $delivery_id = $delivery_info['delivery_id'];

        /* 修改发货单信息 */
        $delivery_invoice_no = trim($delivery_invoice_no);
        $invoice_no = str_replace(['|', ' '], ',', $delivery_invoice_no); // 多个发货单号 以 空格 或 ‘|’ 分隔
        $invoice_no = trim($invoice_no, ',');
        $_delivery['invoice_no'] = !empty($delivery_info['invoice_no']) ? $delivery_info['invoice_no'] . ',' . $invoice_no : $invoice_no;
        $_delivery['shipping_name'] = $shipping_name;
        $_delivery['status'] = DELIVERY_SHIPPED; // 0，为已发货
        $query = OrderDeliveryHandleService::update_delivery_order($delivery_id, $_delivery);
        if ($query) {

            // 发货单商品入库
            if (!empty($goods_list)) {
                foreach ($goods_list as $val) {
                    // 实物商品+活动商品  排除虚拟商品
                    if (empty($val['extension_code']) || in_array($val['extension_code'], ['presale', 'bargain_buy', 'bargain']) || substr($val['extension_code'], 0, 7) == 'seckill') {

                        $delivery_goods_send_number = $send_number[$val['rec_id']];

                        $query = OrderDeliveryHandleService::update_delivery_goods($delivery_id, $val['goods_id'], $delivery_goods_send_number);

                        // 更新订单商品 已发货数量
                        //Log::info('rec_id:' . $val['rec_id'] . '发货数量' . $delivery_goods_send_number);
                        OrderDeliveryHandleService::updateOrderGoodsSendNumber($order['order_id'], $val['goods_id'], $delivery_goods_send_number);

                    } elseif ($val['extension_code'] == 'package_buy') {
                        // 商品（超值礼包）
                        foreach ($val['package_goods_list'] as $pg_key => $pg_value) {

                            $delivery_goods_send_number = $send_number[$val['rec_id']][$pg_value['g_p']];

                            $query = OrderDeliveryHandleService::update_delivery_goods($delivery_id, $val['goods_id'], $delivery_goods_send_number);

                            // 更新订单商品 已发货数量
                            //Log::info('rec_id:' . $val['rec_id'] . '发货数量' . $delivery_goods_send_number);
                            OrderDeliveryHandleService::updateOrderGoodsSendNumber($order['order_id'], $val['goods_id'], $delivery_goods_send_number);
                        }

                    }
                }

                /* 订单信息更新处理 */
                /* 标记订单为已确认 “已发货” */
                /* 更新发货时间 */
                $order_finish = $this->getAllDeliveryFinish($order['order_id']);
                $shipping_status = ($order_finish == 1) ? SS_SHIPPED : SS_SHIPPED_PART;
                $arr['shipping_status'] = $shipping_status;
                $arr['shipping_time'] = $now; // 发货时间
                $arr['invoice_no'] = !empty($order['invoice_no']) ? trim($order['invoice_no'] . ',') . ',' . $invoice_no : $invoice_no;

                if (empty($order['pay_time'])) {
                    $arr['pay_time'] = $now;
                }
                update_order($order['order_id'], $arr);

                /* 发货单发货记录log */
                $action_note = 'batch update';
                //Log::info($order['order_sn'] . ':' . $action_note);

                order_action($order['order_sn'], OS_CONFIRMED, $shipping_status, $order['pay_status'], $action_note, $action_user, 1);

                /* 如果当前订单已经全部发货 */
                if ($order_finish) {
                    $this->order_finish($order);
                }
            }

            $delivery_status['error'] = 0;
            $delivery_status['msg'] = 'update delivery goods success';
            $delivery_status['delivery_id'] = $delivery_id;
            $delivery_status['order_sn'] = $order['order_sn'];
        }

        return $delivery_status;
    }


    // 同一订单 运单号不一致 生成不同的发货单
    public function oneKeyDeliveryInovice($order = [], $item_goods = [], $send_number = [], $action_user = '', $admin_id = 0)
    {
        if (empty($order) || empty($item_goods)) {
            return ['error' => 1, 'msg' => 'data is empty'];
        }

        $delivery_status = ['error' => 0, 'msg' => '', 'order_sn' => ''];

        if (!empty($order)) {

            $delivery_invoice_no = empty($item_goods['invoice_no']) ? '' : trim($item_goods['invoice_no']);  //快递单号
            $shipping_name = empty($item_goods['shipping_name']) ? '' : trim($item_goods['shipping_name']);  //快递公司

            if (!empty($delivery_invoice_no)) {

                $_lang = lang('order');

                /* 订单状态是否已全部分单检查 */
                if ($order['order_status'] == OS_SPLITED) {
                    $delivery_status['error'] = 1;
                    $delivery_status['msg'] = sprintf(lang('admin/order.develop_order_status_format'), $order['order_sn'], $_lang['os'][OS_SPLITED], $_lang['ss'][SS_SHIPPED_ING]);
                    $delivery_status['rec_id'] = $item_goods['rec_id'];
                    $delivery_status['order_sn'] = $order['order_sn'];
                    return $delivery_status;
                }

                // 数据库订单商品列表
                $_goods = $order['goods_list'] ?? [];

                $attr = $_goods['attr'];
                $goods_list = $_goods['goods_list'];
                unset($_goods);

                // 合并处理 Excel导入商品信息与数据库订单商品信息
                $new_goods_list = [];
                if (!empty($goods_list)) {
                    foreach ($goods_list as $k => $val) {
                        if (in_array($item_goods['rec_id'], $val)) {
                            $new_goods_list[] = $val;
                        }
                    }
                    unset($goods_list);
                }

                $goods_list = $new_goods_list;


                /* 查询：商品已发货数量 此单可发货数量 */
                if (!empty($goods_list)) {
                    foreach ($goods_list as $key => $goods_value) {
                        if (!$goods_value['goods_id']) {
                            continue;
                        }

                        /* 超级礼包 */
                        if (($goods_value['extension_code'] == 'package_buy') && (count($goods_value['package_goods_list']) > 0)) {
                            $goods_list[$key]['package_goods_list'] = $this->packageGoods($goods_value['package_goods_list'], $goods_value['goods_number'], $goods_value['order_id'], $goods_value['extension_code'], $goods_value['goods_id']);
                            foreach ($goods_list[$key]['package_goods_list'] as $pg_key => $pg_value) {
                                $goods_list[$key]['package_goods_list'][$pg_key]['readonly'] = '';
                                /* 使用库存 是否缺货 */
                                if ($pg_value['storage'] <= 0 && config('shop.use_storage') == 1 && config('shop.stock_dec_time') == SDT_SHIP) {
                                    $goods_list[$key]['package_goods_list'][$pg_key]['send'] = lang('admin/order.act_good_vacancy');
                                    $goods_list[$key]['package_goods_list'][$pg_key]['readonly'] = 'readonly="readonly"';
                                    unset($send_number[$goods_value['rec_id']], $goods_list[$key]);

                                } /* 将已经全部发货的商品设置为只读 */
                                elseif ($pg_value['send'] <= 0) {
                                    $goods_list[$key]['package_goods_list'][$pg_key]['send'] = lang('admin/order.act_good_delivery');
                                    $goods_list[$key]['package_goods_list'][$pg_key]['readonly'] = 'readonly="readonly"';

                                    unset($send_number[$goods_value['rec_id']], $goods_list[$key]);
                                }
                            }
                        } elseif ($goods_value['extension_code'] == 'virtual_card' || $goods_value['is_real'] == 0) {
                            /* 虚拟商品列表 virtual_card*/
                            if ($goods_value['extension_code'] == 'virtual_card') {
                                $virtual_goods[$goods_value['extension_code']][] = ['goods_id' => $goods_value['goods_id']];
                            }

                            unset($send_number[$goods_value['rec_id']], $goods_list[$key]);

                        } else {
                            $goods_list[$key]['sended'] = $goods_value['send_number'];
                            $goods_list[$key]['sended'] = $goods_value['goods_number'];
                            $goods_list[$key]['send'] = $goods_value['goods_number'] - $goods_value['send_number'];
                            $goods_list[$key]['readonly'] = '';
                            /* 是否缺货 */
                            if ($goods_value['storage'] <= 0 && config('shop.use_storage') == 1 && config('shop.stock_dec_time') == SDT_SHIP) {
                                $goods_list[$key]['send'] = lang('admin/order.act_good_vacancy');
                                $goods_list[$key]['readonly'] = 'readonly="readonly"';

                                unset($send_number[$goods_value['rec_id']], $goods_list[$key]);

                            } elseif ($goods_list[$key]['send'] <= 0) {
                                $goods_list[$key]['send'] = lang('admin/order.act_good_delivery');
                                $goods_list[$key]['readonly'] = 'readonly="readonly"';

                                unset($send_number[$goods_value['rec_id']], $goods_list[$key]);
                            }
                        }
                    }
                }

                // 订单商品 全部为 虚拟商品 不生成发货单
                if (!empty($virtual_goods) && !empty($goods_list) && count($virtual_goods) == count($goods_list)) {
                    /* 操作失败 */
                    $delivery_status['error'] = 1;
                    $delivery_status['msg'] = lang('admin/order.order_goods_import_empty'); // 无符合条件数据 不处理
                    $delivery_status['order_id'] = $order['order_id'];
                    $delivery_status['order_sn'] = $order['order_sn'];
                    return $delivery_status;
                }

                // 一键发货 之 生成发货单、生成发货单商品
                $delivery_id = $this->makeOneKeyDelivery($order, $goods_list, $send_number, $shipping_name, $action_user);
                // 去发货
                if ($delivery_id && $delivery_id > 0) {

                    $delivery_status = $this->partToDelivery($order, $delivery_id, $delivery_invoice_no, $shipping_name, $action_user, $admin_id);
                }

            }
        }

        return $delivery_status;
    }

    /**
     * 订单全部完成后续操作
     * @param array $order
     * @return bool
     */
    protected function order_finish($order = [])
    {
        if (empty($order)) {
            return false;
        }

        /* 如果订单用户不为空，计算积分，并发给用户；发红包 */
        if ($order['user_id'] > 0) {

            /* 计算并发放积分 */
            $integral = integral_to_give($order);
            /*如果已配送子订单的赠送积分大于0   减去已配送子订单积分*/
            if (!empty($child_order)) {
                $integral['custom_points'] = $integral['custom_points'] - $child_order['custom_points'];
                $integral['rank_points'] = $integral['rank_points'] - $child_order['rank_points'];
            }
            log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf(lang('admin/order.order_gift_integral'), $order['order_sn']));

            /* 发放红包 */
            send_order_bonus($order['order_id']);

            /* 发放优惠券 bylu */
            send_order_coupons($order['order_id']);
        }

        /* 更新商品销量 按照步骤发货 */
        get_goods_sale($order['order_id']);

        // 发送邮件、短信、微信模板消息通知
        //$this->order_message($order);

        return true;
    }

    /**
     * 发送邮件、短信、微信模板消息通知
     *
     * @param array $order
     * @throws \Exception
     */
    protected function order_message($order = [])
    {
        $now = TimeRepository::getGmTime();

        /* 发送邮件 */
        $cfg = config('shop.send_ship_email') ?? 0;
        if ($cfg == '1') {

            $tpl = get_mail_template('deliver_notice');
            $GLOBALS['smarty']->assign('order', $order);
            $GLOBALS['smarty']->assign('send_time', TimeRepository::getLocalDate(config('shop.time_format')));
            $GLOBALS['smarty']->assign('shop_name', config('shop.shop_name'));
            $GLOBALS['smarty']->assign('send_date', TimeRepository::getLocalDate(config('shop.time_format'), $now));
            $GLOBALS['smarty']->assign('sent_date', TimeRepository::getLocalDate(config('shop.time_format'), $now));
            $GLOBALS['smarty']->assign('confirm_url', url('/') . '/user_order.php?act=order_detail&order_id=' . $order['order_id']); //by wu
            $GLOBALS['smarty']->assign('send_msg_url', url('/') . '/user_message.php?act=message_list&order_id=' . $order['order_id']);

            $content = $GLOBALS['smarty']->fetch('str:' . $tpl['template_content']);
            CommonRepository::sendEmail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html']);
        }

        /* 如果需要，发短信 */
        if (config('shop.sms_order_shipped') == '1' && $order['mobile'] != '') {

            //短信接口参数
            if ($order['ru_id']) {
                $shop_name = app(MerchantCommonService::class)->getShopName($order['ru_id'], 1);
            } else {
                $shop_name = "";
            }

            $user_name = Users::where('user_id', $order['user_id'])->value('user_name');

            $smsParams = [
                'shop_name' => $shop_name,
                'shopname' => $shop_name,
                'user_name' => $user_name,
                'username' => $user_name,
                'consignee' => $order['consignee'],
                'order_sn' => $order['order_sn'],
                'ordersn' => $order['order_sn'],
                'invoice_no' => $order['invoice_no'],
                'invoiceno' => $order['invoice_no'],
                'mobile_phone' => $order['mobile'],
                'mobilephone' => $order['mobile']
            ];

            app(CommonRepository::class)->smsSend($order['mobile'], $smsParams, 'sms_order_shipped', false);
        }

        // 微信通模板消息 发货通知
        if (file_exists(MOBILE_WECHAT)) {
            $pushData = [
                'first' => ['value' => lang('wechat::wechat.order_shipping_first')], // 标题
                'keyword1' => ['value' => $order['order_sn']], //订单
                'keyword2' => ['value' => $order['shipping_name']], //物流服务
                'keyword3' => ['value' => $order['invoice_no']], //快递单号
                'keyword4' => ['value' => $order['consignee']], // 收货信息
                'remark' => ['value' => lang('wechat::wechat.order_shipping_remark')]
            ];
            $shop_url = url('/') . '/'; // 根域名 兼容商家后台
            $order_url = dsc_url('/#/user/orderDetail/' . $order['order_id']);

            push_template_curl('OPENTM202243318', $pushData, $order_url, $order['user_id'], $shop_url);
        }
    }

}
