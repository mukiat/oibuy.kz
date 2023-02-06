<?php

/* 订单搜索 */
$_LANG['order_sn'] = '订单号';
$_LANG['consignee'] = '收货人';
$_LANG['all_status'] = '订单状态';

$_LANG['cs'][OS_UNCONFIRMED] = '待确认';
$_LANG['cs'][CS_AWAIT_PAY] = '待付款';
$_LANG['cs'][CS_AWAIT_SHIP] = '待发货';
$_LANG['cs'][CS_FINISHED] = '已完成';
$_LANG['cs'][PS_PAYING] = '付款中';
$_LANG['cs'][OS_CANCELED] = '取消';
$_LANG['cs'][OS_INVALID] = '无效';
$_LANG['cs'][OS_RETURNED] = '退货';
$_LANG['cs'][OS_SHIPPED_PART] = '部分发货';

/* 订单状态 */
$_LANG['os'][OS_UNCONFIRMED] = '未确认';
$_LANG['os'][OS_CONFIRMED] = '已确认';
$_LANG['os'][OS_CANCELED] = '<font color="red"> 取消</font>';
$_LANG['os'][OS_INVALID] = '<font color="red">无效</font>';
$_LANG['os'][OS_RETURNED] = '<font color="red">退货</font>';
$_LANG['os'][OS_SPLITED] = '已分单';
$_LANG['os'][OS_SPLITING_PART] = '部分分单';
$_LANG['os'][OS_RETURNED_PART] = '部分已退货';
$_LANG['os'][OS_ONLY_REFOUND] = '仅退款';

$_LANG['ss'][SS_UNSHIPPED] = '未发货';
$_LANG['ss'][SS_PREPARING] = '配货中';
$_LANG['ss'][SS_SHIPPED] = '已发货';
$_LANG['ss'][SS_RECEIVED] = '收货确认';
$_LANG['ss'][SS_SHIPPED_PART] = '已发货(部分商品)';
$_LANG['ss'][SS_SHIPPED_ING] = '发货中';

$_LANG['ps'][PS_UNPAYED] = '未付款';
$_LANG['ps'][PS_PAYING] = '付款中';
$_LANG['ps'][PS_PAYED] = '已付款';

$_LANG['sale_statistics'] = '销售统计';
$_LANG['start_end_date'] = '起止时间';
$_LANG['goods_sn'] = '货号';
$_LANG['order_date_type'] = '订单时间类型';
$_LANG['label_update_time'] = '发货时间';
$_LANG['label_add_time'] = '下单时间';
$_LANG['order_status'] = '订单状态';
$_LANG['shipping_status'] = '发货状态';
$_LANG['cat_name'] = '分类名称';
$_LANG['query'] = '查询';
$_LANG['export_list'] = '导出列表';
$_LANG['export_data'] = '导出数据';
$_LANG['goods_number'] = '数量';
$_LANG['sell_price'] = '售价';
$_LANG['total_fee'] = '总金额';
$_LANG['from_order'] = '订单来源';
$_LANG['cashdesk'] = '收银台';
$_LANG['sell_date'] = '售出日期';

/* 页面顶部操作提示 */
$_LANG['operation_prompt_content']['list'][0] = '统计所有销售统计信息。';
$_LANG['operation_prompt_content']['list'][1] = '根据订单时间、订单类型、发货状态等筛选出某个时间段的订单信息。';

return $_LANG;
