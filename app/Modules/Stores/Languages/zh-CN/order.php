<?php

$_LANG['update_shipping'] = "将【%s】配送方式修改为【%s】";
$_LANG['self_motion_goods'] = "自动确认收货";

$_LANG['order_ship_delivery'] = "发货单流水号：【%s】";
$_LANG['order_ship_invoice'] = "发货单号：【%s】";
$_LANG['edit_order_invoice'] = "将发货单号【%s】修改为：【%s】";

$_LANG['add_order_info'] = "【%s】手工添加订单";
$_LANG['shipping_refund'] = "编辑订单导致退款：%s";

$_LANG['01_stores_pick_goods'] = "订单未付款，无法提货";
$_LANG['02_stores_pick_goods'] = "操作成功，提货完成";
$_LANG['03_stores_pick_goods'] = "提货码不正确，请重新输入";
$_LANG['04_stores_pick_goods'] = "请输入6位提货码";

$_LANG['order_label'] = '信息标签';
$_LANG['amount_label'] = '金额标签';

$_LANG['bar_code'] = '条形码';
$_LANG['label_bar_code'] = '条形码：';

$_LANG['region'] = '配送区域';
$_LANG['order_shipped_sms'] = '您的订单%s已于%s发货'; //wang

$_LANG['setorder_nopay'] = '设置为未付款';
$_LANG['setorder_cancel'] = '设置为未付款';

//退换货 start
$_LANG['rf'][RF_APPLICATION] = '由用户寄回';
$_LANG['rf'][RF_RECEIVE] = '收到退换货';
$_LANG['rf'][RF_SWAPPED_OUT_SINGLE] = '换出商品寄出【分单】';
$_LANG['rf'][RF_SWAPPED_OUT] = '换出商品寄出';
$_LANG['rf'][RF_COMPLETE] = '完成';
$_LANG['rf'][REFUSE_APPLY] = '申请被拒';
$_LANG['ff'][FF_NOMAINTENANCE] = '未维修';
$_LANG['ff'][FF_MAINTENANCE] = '已维修';
$_LANG['ff'][FF_REFOUND] = '已退款';
$_LANG['ff'][FF_NOREFOUND] = '未退款';
$_LANG['ff'][FF_NOEXCHANGE] = '未换货';
$_LANG['ff'][FF_EXCHANGE] = '已换货';
$_LANG['refund_money'] = '退款金额';
$_LANG['money'] = '金额';
$_LANG['shipping_money'] = '元&nbsp;&nbsp;运费';
$_LANG['is_shipping_money'] = '退运费';
$_LANG['no_shipping_money'] = '不退运费';
$_LANG['return_user_line'] = '线下退款';
$_LANG['return_reason'] = '退换货原因';
$_LANG['whether_display'] = '是否显示';
$_LANG['return_baitiao'] = '退回白条余额(如果是余额与白条混合支付,余额部分退回余额)';//bylu;
//退换货 end

//ecmoban模板堂 --zhuo start
$_LANG['auto_delivery_time'] = '自动确认收货时间：';
$_LANG['dateType'][0] = '天';
//ecmoban模板堂 --zhuo end

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
$_LANG['cs'][OS_SHIPPED_PART] = '待收货';

/* 订单状态 */
$_LANG['os'][OS_UNCONFIRMED] = '未确认';
$_LANG['os'][OS_CONFIRMED] = '已确认';
$_LANG['os'][OS_CANCELED] = '<font color="red"> 取消</font>';
$_LANG['os'][OS_INVALID] = '<font color="red">无效</font>';
$_LANG['os'][OS_RETURNED] = '<font color="red">退货</font>';
$_LANG['os'][OS_SPLITED] = '已分单';
$_LANG['os'][OS_SPLITING_PART] = '部分分单';
$_LANG['os'][OS_RETURNED_PART] = '<font color="red">部分已退货</font>';
$_LANG['os'][OS_ONLY_REFOUND] = '<font color="red">仅退款</font>';

$_LANG['ss'][SS_UNSHIPPED] = '未发货';
$_LANG['ss'][SS_PREPARING] = '配货中';
$_LANG['ss'][SS_SHIPPED] = '已发货';
$_LANG['ss'][SS_RECEIVED] = '收货确认';
$_LANG['ss'][SS_SHIPPED_PART] = '已发货(部分商品)';
$_LANG['ss'][SS_SHIPPED_ING] = '发货中';

$_LANG['ps'][PS_UNPAYED] = '未付款';
$_LANG['ps'][PS_PAYING] = '付款中';
$_LANG['ps'][PS_PAYED] = '已付款';
$_LANG['ps'][PS_PAYED_PART] = '部分付款(定金)';
$_LANG['ps'][PS_REFOUND] = '已退款';
$_LANG['ps'][PS_REFOUND_PART] = '部分退款';

$_LANG['ss_admin'][SS_SHIPPED_ING] = '发货中（前台状态：未发货）';
/* 订单操作 */
$_LANG['label_operable_act'] = '当前可执行操作：';
$_LANG['label_action_note'] = '操作备注：';
$_LANG['label_invoice_note'] = '发货备注：';
$_LANG['label_invoice_no'] = '发货单号：';
$_LANG['label_cancel_note'] = '取消原因：';
$_LANG['notice_cancel_note'] = '（会记录在商家给客户的留言中）';
$_LANG['op_confirm'] = '确认';
$_LANG['refuse'] = '拒绝';
$_LANG['refuse_apply'] = '拒绝申请';
$_LANG['is_refuse_apply'] = '已拒绝申请';
$_LANG['op_pay'] = '付款';
$_LANG['op_prepare'] = '配货';
$_LANG['op_ship'] = '发货';
$_LANG['op_cancel'] = '取消';
$_LANG['op_invalid'] = '无效';
$_LANG['op_return'] = '退货';
$_LANG['op_unpay'] = '设为未付款';
$_LANG['op_unship'] = '未发货';
$_LANG['op_cancel_ship'] = '取消发货';
$_LANG['op_receive'] = '已收货';
$_LANG['op_assign'] = '指派给';
$_LANG['op_after_service'] = '售后';
$_LANG['act_ok'] = '操作成功';
$_LANG['act_false'] = '操作失败';
$_LANG['act_ship_num'] = '此单发货数量不能超出订单商品数量';
$_LANG['act_good_vacancy'] = '商品已缺货';
$_LANG['act_good_delivery'] = '货已发完';
$_LANG['notice_gb_ship'] = '备注：团购活动未处理为成功前，不能发货';
$_LANG['back_list'] = '返回列表页';
$_LANG['op_remove'] = '删除';
$_LANG['op_you_can'] = '您可进行的操作';
$_LANG['op_split'] = '生成发货单';
$_LANG['op_to_delivery'] = '去发货';
$_LANG['op_swapped_out_single'] = '换出商品寄出【分单】';
$_LANG['op_swapped_out'] = '换出商品寄出';
$_LANG['op_complete'] = '完成退换货';
$_LANG['op_refuse_apply'] = '拒绝申请';
$_LANG['baitiao_by_stages'] = '白条分期';
$_LANG['expired'] = '已到期';
$_LANG['unable_to_click'] = '无法点击';

/* 订单列表 */
$_LANG['order_amount'] = '应付金额';
$_LANG['total_fee'] = '总金额';
$_LANG['shipping_name'] = '配送方式';
$_LANG['pay_name'] = '支付方式';
$_LANG['address'] = '地址';
$_LANG['order_time'] = '下单时间';
$_LANG['trade_snapshot'] = '交易快照';
$_LANG['detail'] = '查看';
$_LANG['phone'] = '电话';
$_LANG['group_buy'] = '（团购）';
$_LANG['error_get_goods_info'] = '获取订单商品信息错误';
$_LANG['exchange_goods'] = '（积分兑换）';
$_LANG['auction'] = '（拍卖活动）';
$_LANG['snatch'] = '（夺宝奇兵）';
$_LANG['presale'] = '（预售）';
$_LANG['store_order_list'] = '门店订单列表';

$_LANG['js_languages']['remove_confirm'] = '删除订单将清除该订单的所有信息。您确定要这么做吗？';

/* 订单搜索 */
$_LANG['label_buyer'] = '购货人：';
$_LANG['label_order_sn'] = '订单号：';
$_LANG['label_all_status'] = '订单状态：';
$_LANG['label_user_name'] = '购货人：';
$_LANG['label_consignee'] = '收货人：';
$_LANG['label_email'] = '电子邮件：';
$_LANG['label_address'] = '收货地址：';
$_LANG['label_zipcode'] = '邮政编码：';
$_LANG['label_tel'] = '电话号码：';
$_LANG['label_mobile'] = '手机号码：';
$_LANG['label_shipping'] = '配送方式：';
$_LANG['label_payment'] = '支付方式：';
$_LANG['label_order_status'] = '订单状态：';
$_LANG['label_pay_status'] = '付款状态：';
$_LANG['label_shipping_status'] = '发货状态：';
$_LANG['label_area'] = '所在地区：';
$_LANG['label_time'] = '下单时间：';

/* 订单详情 */
$_LANG['prev'] = '前一个订单';
$_LANG['next'] = '后一个订单';
$_LANG['print_order'] = '打印订单';
$_LANG['print_shipping'] = '打印快递单';
$_LANG['print_order_sn'] = '订单编号：';
$_LANG['print_buy_name'] = '购 货 人：';
$_LANG['label_consignee_address'] = '收货地址：';
$_LANG['no_print_shipping'] = '很抱歉,目前您还没有设置打印快递单模板.不能进行打印';
$_LANG['suppliers_no'] = '不指定供货商本店自行处理';
$_LANG['restaurant'] = '本店';

$_LANG['order_info'] = '订单信息';
$_LANG['base_info'] = '基本信息';
$_LANG['other_info'] = '其他信息';
$_LANG['consignee_info'] = '收货人信息';
$_LANG['fee_info'] = '费用信息';
$_LANG['action_info'] = '操作信息';
$_LANG['shipping_info'] = '配送信息';

$_LANG['label_how_oos'] = '缺货处理：';
$_LANG['label_how_surplus'] = '余额处理：';
$_LANG['label_pack'] = '包装：';
$_LANG['label_card'] = '贺卡：';
$_LANG['label_card_message'] = '贺卡祝福语：';
$_LANG['label_order_time'] = '下单时间：';
$_LANG['label_pay_time'] = '付款时间：';
$_LANG['label_shipping_time'] = '发货时间：';
$_LANG['label_sign_building'] = '地址别名：';
$_LANG['label_best_time'] = '送货时间：';
$_LANG['label_inv_type'] = '发票类型：';
$_LANG['label_inv_payee'] = '发票抬头：';
$_LANG['label_inv_content'] = '发票内容：';
$_LANG['label_postscript'] = '买家留言：';
$_LANG['label_region'] = '所在地区：';

$_LANG['label_shop_url'] = '网址：';
$_LANG['label_shop_address'] = '地址：';
$_LANG['label_service_phone'] = '电话：';
$_LANG['label_print_time'] = '打印时间：';

$_LANG['label_suppliers'] = '选择供货商：';
$_LANG['label_agency'] = '办事处：';
$_LANG['suppliers_name'] = '供货商';

$_LANG['product_sn'] = '货品号';
$_LANG['goods_info'] = '商品信息';
$_LANG['goods_name'] = '商品名称';
$_LANG['goods_name_brand'] = '商品名称 [ 品牌 ]';
$_LANG['goods_sn'] = '货号';
$_LANG['goods_price'] = '价格';
$_LANG['give_integral'] = '商品赠送积分';
$_LANG['goods_number'] = '数量';
$_LANG['goods_attr'] = '属性';
$_LANG['goods_delivery'] = '已发货数量';
$_LANG['goods_delivery_curr'] = '此单发货数量';
$_LANG['storage'] = '库存';
$_LANG['subtotal'] = '小计';
$_LANG['amount_return'] = '商品退款';
$_LANG['label_total'] = '合计：';
$_LANG['label_total_weight'] = '商品总重量：';
$_LANG['label_total_cost'] = '合计商品成本：';
$_LANG['measure_unit'] = '单位';
$_LANG['contain_content'] = '包含内容';
$_LANG['application_refund'] = '申请退款';

$_LANG['return_discount'] = '享受折扣';

$_LANG['label_goods_amount'] = '商品总金额：';
$_LANG['label_discount'] = '折扣：';
$_LANG['label_tax'] = '发票税额：';
$_LANG['label_shipping_fee'] = '配送费用：';
$_LANG['label_insure_fee'] = '保价费用：';
$_LANG['label_insure_yn'] = '是否保价：';
$_LANG['label_pay_fee'] = '支付费用：';
$_LANG['label_pack_fee'] = '包装费用：';
$_LANG['label_card_fee'] = '贺卡费用：';
$_LANG['label_money_paid'] = '已付款金额：';
$_LANG['label_surplus'] = '使用余额：';
$_LANG['label_integral'] = '使用积分：';
$_LANG['label_bonus'] = '使用红包：';
$_LANG['label_value_card'] = '使用储值卡：';
$_LANG['label_coupons'] = '使用优惠券：';
$_LANG['label_order_amount'] = '订单总金额：';
$_LANG['label_money_dues'] = '应付款金额：';
$_LANG['label_money_refund'] = '应退款金额：';
$_LANG['label_to_buyer'] = '商家给客户的留言：';
$_LANG['save_order'] = '保存订单';
$_LANG['notice_gb_order_amount'] = '（备注：团购如果有保证金，第一次只需支付保证金和相应的支付费用）';
$_LANG['formated_order_amount'] = '订单总金额';
$_LANG['stores_info'] = '门店信息';

$_LANG['action_user'] = '操作者';
$_LANG['action_time'] = '操作时间';
$_LANG['return_status'] = '操作';
$_LANG['refound_status'] = '状态';
$_LANG['order_status'] = '订单状态';
$_LANG['pay_status'] = '付款状态';
$_LANG['shipping_status'] = '发货状态';
$_LANG['action_note'] = '备注';
$_LANG['pay_note'] = '支付备注：';
$_LANG['action_jilu'] = '操作记录';
$_LANG['not_action_jilu'] = '当前还没有操作记录';

$_LANG['sms_time_format'] = 'm月j日G时';
$_LANG['order_splited_sms'] = '您的订单%s,%s正在%s [%s]';
$_LANG['order_removed'] = '订单删除成功。';
$_LANG['return_list'] = '返回订单列表';
$_LANG['order_remove_failure'] = '%s 存在单商品退换货订单，订单删除失败。';

/* 订单处理提示 */
$_LANG['surplus_not_enough'] = '该订单使用 %s 余额支付，现在用户余额不足';
$_LANG['integral_not_enough'] = '该订单使用 %s 积分支付，现在用户积分不足';
$_LANG['bonus_not_available'] = '该订单使用红包支付，现在红包不可用';

/* 购货人信息 */
$_LANG['display_buyer'] = '显示购货人信息';
$_LANG['buyer_info'] = '购货人信息';
$_LANG['pay_points'] = '消费积分';
$_LANG['rank_points'] = '成长值';
$_LANG['user_money'] = '账户余额';
$_LANG['email'] = '电子邮件';
$_LANG['rank_name'] = '会员等级';
$_LANG['bonus_count'] = '红包数量';
$_LANG['zipcode'] = '邮编';
$_LANG['tel'] = '电话';
$_LANG['mobile'] = '手机号码';
$_LANG['leaving_message'] = '留言';

/*增值税发票信息*/
$_LANG['vat_info'] = '增值税发票信息';
$_LANG['vat_name'] = '单位名称';
$_LANG['vat_taxid'] = '纳税人识别码';
$_LANG['vat_company_address'] = '注册地址';
$_LANG['vat_company_telephone'] = '注册电话';
$_LANG['vat_bank_of_deposit'] = '开户银行';
$_LANG['vat_bank_account'] = '银行账户';

/* 合并订单 */
$_LANG['seller_order_sn_same'] = '要合并的两个订单所属商家必须相同';
$_LANG['merge_order_main_count'] = '要合并的订单不能为主订单';
$_LANG['order_sn_not_null'] = '请填写要合并的订单号';
$_LANG['two_order_sn_same'] = '要合并的两个订单号不能相同';
$_LANG['order_not_exist'] = '定单 %s 不存在';
$_LANG['os_not_unconfirmed_or_confirmed'] = '%s 的订单状态不是“未确认”或“已确认”';
$_LANG['ps_not_unpayed'] = '订单 %s 的付款状态不是“未付款”';
$_LANG['ss_not_unshipped'] = '订单 %s 的发货状态不是“未发货”';
$_LANG['order_user_not_same'] = '要合并的两个订单不是同一个用户下的';
$_LANG['merge_invalid_order'] = '对不起，您选择合并的订单不允许进行合并的操作。';

$_LANG['merge_order'] = '合并订单';
$_LANG['from_order_sn'] = '从订单：';
$_LANG['to_order_sn'] = '主订单：';
$_LANG['merge'] = '合并';
$_LANG['notice_order_sn'] = '当两个订单不一致时，合并后的订单信息（如：支付方式、配送方式、包装、贺卡、红包等）以主订单为准。';
$_LANG['js_languages']['confirm_merge'] = '您确实要合并这两个订单吗？';

/* 批处理 */
$_LANG['pls_select_order'] = '请选择您要操作的订单';
$_LANG['no_fulfilled_order'] = '没有满足操作条件的订单。';
$_LANG['updated_order'] = '更新的订单：';
$_LANG['order'] = '订单：';
$_LANG['confirm_order'] = '以下订单无法设置为确认状态';
$_LANG['invalid_order'] = '以下订单无法设置为无效';
$_LANG['cancel_order'] = '以下订单无法取消';
$_LANG['remove_order'] = '以下订单无法被移除';

/* 编辑订单打印模板 */
$_LANG['edit_order_templates'] = '编辑订单打印模板';
$_LANG['template_resetore'] = '还原模板';
$_LANG['edit_template_success'] = '编辑订单打印模板操作成功!';
$_LANG['remark_fittings'] = '（配件）';
$_LANG['remark_gift'] = '（赠品）';
$_LANG['remark_favourable'] = '（特惠品）';
$_LANG['remark_package'] = '（礼包）';
$_LANG['remark_package_goods'] = '（礼包内产品）';

/* 订单来源统计 */
$_LANG['from_order'] = '订单来源';
$_LANG['referer'] = '来源';
$_LANG['from_ad_js'] = '广告：';
$_LANG['from_goods_js'] = '商品站外JS投放';
$_LANG['from_self_site'] = '来自本站';
$_LANG['from'] = '来自站点：';

/* 添加、编辑订单 */
$_LANG['add_order'] = '添加订单';
$_LANG['edit_order'] = '编辑订单';
$_LANG['step']['user'] = '请选择您要为哪个会员下订单';
$_LANG['step']['goods'] = '选择商品';
$_LANG['step']['consignee'] = '设置收货人信息';
$_LANG['step']['shipping'] = '选择配送方式';
$_LANG['step']['payment'] = '选择支付方式';
$_LANG['step']['other'] = '设置其他信息';
$_LANG['step']['money'] = '设置费用';
$_LANG['anonymous'] = '匿名用户';
$_LANG['by_useridname'] = '按会员编号或会员名搜索';
$_LANG['button_prev'] = '上一步';
$_LANG['button_next'] = '下一步';
$_LANG['button_finish'] = '完成';
$_LANG['button_cancel'] = '取消';
$_LANG['name'] = '名称';
$_LANG['desc'] = '描述';
$_LANG['shipping_fee'] = '配送费';
$_LANG['free_money'] = '免费额度';
$_LANG['insure'] = '保价费';
$_LANG['pay_fee'] = '手续费';
$_LANG['pack_fee'] = '包装费';
$_LANG['card_fee'] = '贺卡费';
$_LANG['no_pack'] = '不要包装';
$_LANG['no_card'] = '不要贺卡';
$_LANG['add_to_order'] = '加入订单';
$_LANG['calc_order_amount'] = '计算订单金额';
$_LANG['available_surplus'] = '可用余额：';
$_LANG['available_integral'] = '可用积分：';
$_LANG['available_bonus'] = '可用红包：';
$_LANG['admin'] = '管理员添加';
$_LANG['search_goods'] = '按商品编号或商品名称或商品货号搜索';
$_LANG['category'] = '分类';
$_LANG['order_category'] = '订单分类';
$_LANG['brand'] = '品牌';
$_LANG['user_money_not_enough'] = '用户余额不足';
$_LANG['pay_points_not_enough'] = '用户积分不足';
$_LANG['money_paid_enough'] = '已付款金额比商品总金额和各种费用之和还多，请先退款';
$_LANG['price_note'] = '备注：商品价格中已包含属性加价';
$_LANG['select_pack'] = '选择包装';
$_LANG['select_card'] = '选择贺卡';
$_LANG['select_shipping'] = '请先选择配送方式';
$_LANG['want_insure'] = '我要保价';
$_LANG['update_goods'] = '更新商品';
$_LANG['notice_user'] = '<strong>注意：</strong>搜索结果只显示前20条记录，如果没有找到相' .
    '应会员，请更精确地查找。另外，如果该会员是从论坛注册的且没有在商城登录过，' .
    '也无法找到，需要先在商城登录。';
$_LANG['amount_increase'] = '由于您修改了订单，导致订单总金额增加，需要再次付款';
$_LANG['amount_decrease'] = '由于您修改了订单，导致订单总金额减少，需要退款';
$_LANG['continue_shipping'] = '由于您修改了收货人所在地区，导致原来的配送方式不再可用，请重新选择配送方式';
$_LANG['continue_payment'] = '由于您修改了配送方式，导致原来的支付方式不再可用，请重新选择配送方式';
$_LANG['refund'] = '退款';
$_LANG['cannot_edit_order_shipped'] = '您不能修改已发货的订单';
$_LANG['cannot_edit_order_payed'] = '您不能修改已付款的订单';
$_LANG['address_list'] = '从已有收货地址中选择：';
$_LANG['order_amount_change'] = '订单总金额由 %s 变为 %s';
$_LANG['shipping_note'] = '说明：因为订单已发货，修改配送方式将不会改变配送费和保价费。';
$_LANG['change_use_surplus'] = '编辑订单 %s ，改变使用预付款支付的金额';
$_LANG['change_use_integral'] = '编辑订单 %s ，改变使用积分支付的数量';
$_LANG['return_order_surplus'] = '由于取消、无效或退货操作，退回支付订单 %s 时使用的预付款';
$_LANG['return_order_integral'] = '由于取消、无效或退货操作，退回支付订单 %s 时使用的积分';
$_LANG['order_gift_integral'] = '订单 %s 赠送的积分';
$_LANG['return_order_gift_integral'] = '由于退货或未发货操作，退回订单 %s 赠送的积分';
$_LANG['invoice_no_mall'] = '多个发货单号，请用英文逗号（“,”）隔开。';

$_LANG['js_languages']['input_price'] = '自定义价格';
$_LANG['js_languages']['pls_search_user'] = '请搜索并选择会员';
$_LANG['js_languages']['confirm_drop'] = '确认要删除该商品吗？';
$_LANG['js_languages']['invalid_goods_number'] = '商品数量不正确';
$_LANG['js_languages']['pls_search_goods'] = '请搜索并选择商品';
$_LANG['js_languages']['pls_select_area'] = '请完整选择所在地区';
$_LANG['js_languages']['pls_select_shipping'] = '请选择配送方式';
$_LANG['js_languages']['pls_select_payment'] = '请选择支付方式';
$_LANG['js_languages']['pls_select_pack'] = '请选择包装';
$_LANG['js_languages']['pls_select_card'] = '请选择贺卡';
$_LANG['js_languages']['pls_input_note'] = '请您填写备注！';
$_LANG['js_languages']['pls_input_cancel'] = '请您填写取消原因！';
$_LANG['js_languages']['pls_select_refund'] = '请选择退款方式！';
$_LANG['js_languages']['pls_select_agency'] = '请选择办事处！';
$_LANG['js_languages']['pls_select_other_agency'] = '该订单现在就属于这个办事处，请选择其他办事处！';
$_LANG['js_languages']['loading'] = '加载中...';

/* 订单操作 */
$_LANG['order_operate'] = '订单操作：';
$_LANG['label_refund_amount'] = '退款金额：';
$_LANG['label_handle_refund'] = '退款方式：';
$_LANG['label_refund_note'] = '退款说明：';
$_LANG['return_user_money'] = '退回用户余额';
$_LANG['create_user_account'] = '生成退款申请';
$_LANG['not_handle'] = '不处理，误操作时选择此项';

$_LANG['order_refund'] = '订单退款：%s';
$_LANG['order_pay'] = '订单支付：%s';

$_LANG['send_mail_fail'] = '发送邮件失败';

$_LANG['send_message'] = '发送/查看留言';

/* 发货单操作 */
$_LANG['delivery_operate'] = '发货单操作：';
$_LANG['delivery_sn_number'] = '流水号：';
$_LANG['invoice_no_sms'] = '请填写发货单号！';

/* 发货单搜索 */
$_LANG['delivery_sn'] = '发货单';

/* 发货单状态 */
$_LANG['delivery_status_dt'] = '发货单状态';
$_LANG['delivery_status'][0] = '已发货';
$_LANG['delivery_status'][1] = '退货';
$_LANG['delivery_status'][2] = '正常';

/* 发货单标签 */
$_LANG['label_delivery_status'] = '状态';
$_LANG['label_suppliers_name'] = '供货商';
$_LANG['label_delivery_time'] = '生成时间';
$_LANG['label_delivery_sn'] = '发货单流水号';
$_LANG['label_add_time'] = '下单时间';
$_LANG['label_update_time'] = '发货时间';
$_LANG['label_send_number'] = '发货数量';
$_LANG['batch_delivery'] = '批量发货';

/* 发货单提示 */
$_LANG['tips_delivery_del'] = '发货单删除成功！';

/* 退货单操作 */
$_LANG['back_operate'] = '退货单操作：';

/* 退货单标签 */
$_LANG['return_time'] = '退货时间：';
$_LANG['label_return_time'] = '退货时间';
$_LANG['label_apply_time'] = '退换货申请时间';
$_LANG['label_back_shipping'] = '退换货配送方式';
$_LANG['label_back_invoice_no'] = '退换货发货单号';
$_LANG['back_order_info'] = '退货单信息';

/* 退货单提示 */
$_LANG['tips_back_del'] = '退货单删除成功！';

$_LANG['goods_num_err'] = '库存不足，请重新选择！';

/*大商创1.5后台新增*/
/*退换货列表*/
$_LANG['problem_desc'] = '问题描述';
$_LANG['product_repair'] = '商品维修';
$_LANG['product_return'] = '商品退货';
$_LANG['product_change'] = '商品换货';
$_LANG['product_price'] = '商品价格';

$_LANG['return_sn'] = '流水号';
$_LANG['return_change_sn'] = '流水号';
$_LANG['repair'] = '维修';
$_LANG['return_goods'] = '退货';
$_LANG['change'] = '换货';
$_LANG['only_return_money'] = '仅退款';
$_LANG['already_repair'] = '已维修';
$_LANG['refunded'] = '已退款';
$_LANG['already_change'] = '已换货';
$_LANG['return_change_type'] = '类型';
$_LANG['apply_time'] = '申请时间';
$_LANG['y_amount'] = '应退金额';
$_LANG['s_amount'] = '实退金额';
$_LANG['actual_return'] = '合计已退款';
$_LANG['return_change_num'] = '退换数量';
$_LANG['receipt_time'] = '签收时间';
$_LANG['applicant'] = '申请人';
$_LANG['to_order_sn2'] = '主订单';
$_LANG['to_order_sn3'] = '主订单';
$_LANG['sub_order_sn'] = '子订单';
$_LANG['sub_order_sn2'] = '子订单';

$_LANG['return_reason'] = '退换原因';
$_LANG['reason_cate'] = '原因分类';
$_LANG['top_cate'] = '顶级分类';

$_LANG['since_some_info'] = '自提点信息';
$_LANG['since_some_name'] = '自提点名称';
$_LANG['contacts'] = '联系人';
$_LANG['tpnd_time'] = '提货时间';
$_LANG['warehouse_name'] = '仓库名称';
$_LANG['ciscount'] = '优惠';
$_LANG['notice_delete_order'] = '(用户会员中心：已删除该订单)';
$_LANG['notice_trash_order'] = '用户已处理进入回收站';
$_LANG['order_not_operable'] = '（订单不可操作）';

$_LANG['region'] = '地区';
$_LANG['seller_mail'] = '卖家寄出';
$_LANG['courier_sz'] = '快递单号';
$_LANG['select_courier'] = '请选择快递公司';
$_LANG['fillin_courier_number'] = '请填写快递单号';
$_LANG['edit_return_reason'] = '编辑退换货原因';
$_LANG['buyers_return_reason'] = '买家退换货原因';
$_LANG['user_file_image'] = '用户上传图片凭证';
$_LANG['operation_notes'] = '操作备注';
$_LANG['agree_apply'] = '同意申请';
$_LANG['receive_goods'] = '收到退回商品';
$_LANG['current_executable_operation'] = '当前可执行操作';
$_LANG['refound'] = '去退款';
$_LANG['swapped_out_single'] = '换出商品寄出【分单】';
$_LANG['swapped_out'] = '换出商品寄出';
$_LANG['complete'] = '完成退换货';
$_LANG['after_service'] = '售后';
$_LANG['complete'] = '完成退换货';
$_LANG['wu'] = '无';
$_LANG['not_filled'] = '未填写';
$_LANG['seller_message'] = '卖家留言';
$_LANG['buyer_message'] = '买家留言';
$_LANG['total_stage'] = '总分期数';
$_LANG['stage'] = '期';
$_LANG['by_stage'] = '分期金额';
$_LANG['yuan_stage'] = '元/期';
$_LANG['submit_order'] = '提交订单';
$_LANG['payment_order'] = '支付订单';
$_LANG['seller_shipping'] = '商家发货';
$_LANG['confirm_shipping'] = '确认收货';
$_LANG['evaluate'] = '评价';
$_LANG['logistics_tracking'] = '物流跟踪';
$_LANG['cashdesk'] = '收银台';
$_LANG['info'] = '信息';
$_LANG['general_invoice'] = '普通发票';
$_LANG['personal_general_invoice'] = '个人普通发票';
$_LANG['enterprise_general_invoice'] = '企业普通发票';
$_LANG['VAT_invoice'] = '增值税发票';
$_LANG['id_code'] = '识别码';
$_LANG['has_been_issued'] = '已发';
$_LANG['invoice_generated'] = '发货单已生成';
$_LANG['has_benn_refund'] = '已退款';
$_LANG['net_profit'] = '净利润约';
$_LANG['one_key_delivery'] = '一键发货';
$_LANG['goods_delivery'] = '商品发货';
$_LANG['search_logistics_info'] = '正在查询物流信息，请稍后...';
$_LANG['consignee_address'] = '收货地址';
$_LANG['view_order'] = '查看订单';
$_LANG['set_baitiao'] = '设置白条';
$_LANG['account_details'] = '账目明细';
$_LANG['goods_sku'] = '商品编号';
$_LANG['all_order'] = '全部订单';
$_LANG['order_status_01'] = '待确认';
$_LANG['order_status_02'] = '待付款';
$_LANG['order_status_03'] = '待发货';
$_LANG['order_status_04'] = '已完成';
$_LANG['order_status_05'] = '待收货';
$_LANG['order_status_06'] = '付款中';
$_LANG['search_keywords_placeholder'] = '订单编号/商品编号/商品关键字';
$_LANG['search_keywords_placeholder2'] = '商品编号/商品关键字';
$_LANG['is_reality'] = '正品保证';
$_LANG['is_return'] = '包退服务';
$_LANG['is_fast'] = '闪速配送';

$_LANG['order_category'] = '订单分类';
$_LANG['baitiao_order'] = '白条订单';
$_LANG['zc_order'] = '众筹订单';
$_LANG['so_order'] = '门店订单';
$_LANG['fx_order'] = '分销订单';
$_LANG['team_order'] = '拼团';
$_LANG['bargain_order'] = '砍价';
$_LANG['wholesale_order'] = '批发';
$_LANG['package_order'] = '超值礼包';
$_LANG['xn_order'] = '虚拟商品订单';
$_LANG['pt_order'] = '普通订单';
$_LANG['return_order'] = '退换货订单';
$_LANG['other_order'] = '促销订单';
$_LANG['db_order'] = '夺宝订单';
$_LANG['ms_order'] = '秒杀订单';
$_LANG['tg_order'] = '团购订单';
$_LANG['pm_order'] = '拍卖订单';
$_LANG['jf_order'] = '积分订单';
$_LANG['ys_order'] = '预售订单';

$_LANG['have_commission_bill'] = '已出佣金账单';
$_LANG['knot_commission_bill'] = '已结佣金账单';
$_LANG['view_commission_bill'] = '查看账单';

$_LANG['order_export_dialog'] = '订单导出弹窗';
$_LANG['operation_error'] = '操作错误';

$_LANG['confirmation_receipt_time'] = '确认收货时间';

$_LANG['fill_user'] = '填写用户';
$_LANG['batch_add_order'] = '批量添加订单';
$_LANG['search_user_placeholder'] = '会员名称/编号';
$_LANG['username'] = '会员名称';
$_LANG['search_user_name_not'] = '请先搜索会员名称';
$_LANG['search_user_name_notic'] = '<strong>注意：</strong>搜索结果只显示前20条记录，如果没有找到相应会员，请更精确地查找。<br>另外，如果该会员是从论坛注册的且没有在商城登录过，也无法找到，需要先在商城登录。';

$_LANG['search_number_placeholder'] = '编号/名称/货号';
$_LANG['select_warehouse'] = '请先选择仓库';
$_LANG['receipt_info'] = '请填写收货信息';
$_LANG['add_distribution_mode'] = '添加配送方式';
$_LANG['select_payment_method'] = '选择支付方式';
$_LANG['join_order'] = '加入订单';
$_LANG['add_invoice'] = '添加发票';
$_LANG['search_goods_first'] = '请先搜索商品';

$_LANG['order_step_notic_01'] = '请查看地区信息是否输入正确';
/*大商创1.5后台新增end*/

/*众筹相关 by wu*/
$_LANG['zc_goods_info'] = '众筹方案信息';
$_LANG['zc_project_name'] = '众筹项目名称';
$_LANG['zc_project_raise_money'] = '众筹金额';
$_LANG['zc_goods_price'] = '方案价格';
$_LANG['zc_shipping_fee'] = '配送费用';
$_LANG['zc_return_time'] = '预计回报时间';
$_LANG['zc_return_content'] = '回报内容';
$_LANG['zc_return_detail'] = '项目成功结束后%s天内';

$_LANG['set_grab_order'] = '设为抢单';
$_LANG['set_success'] = '设置完成';

//by wu
$_LANG['cannot_delete'] = '存在子退换原因，无法删除';
$_LANG['invoice_no_null'] = '快递单号不能为空';

$_LANG['seckill'] = '秒杀订单';

//分单操作
$_LANG['split_action_note'] = "【商品货号：%s，发货：%s件】";

/* 退换货原因 */
$_LANG['add_return_cause'] = "添加退换货原因";
$_LANG['return_cause_list'] = "退换货原因列表";
$_LANG['back_return_cause_list'] = "返回退换货原因列表";
$_LANG['return_order_info'] = "返回订单信息";
$_LANG['back_return_delivery_handle'] = "返回退货单操作";

$_LANG['detection_list_notic'] = "只能操作-订单应收货时间-小于当前系统时间的订单";

$_LANG['refund_type_notic_one'] = "退款失败，退款金额大于实际退款金额";
$_LANG['refund_type_notic_two'] = "退款失败，您的账户资金负金额大于信用额度，请您进行账户资金充值或者联系客服";
$_LANG['refund_type_notic_three'] = "该订单存在退换货商品，不能退货";

$_LANG['batch_delivery_success'] = "批量发货成功";
$_LANG['inspect_order_type'] = "请检查订单状态";

$_LANG['order_return_prompt'] = "订单退款，退回订单";
$_LANG['buy_integral'] = "购买的积分";

/* js 验证提示 */
$_LANG['js_languages']['not_back_cause'] = '请输出退换货原因';
$_LANG['js_languages']['no_confirmation_delivery_info'] = '暂无确认发货信息';

/* order_step js*/
$_LANG['order_step_js_notic_01'] = '请填写收货人名称';
$_LANG['order_step_js_notic_02'] = '请选择国家';
$_LANG['order_step_js_notic_03'] = '请选择省/直辖市';
$_LANG['order_step_js_notic_04'] = '请选择城市';
$_LANG['order_step_js_notic_05'] = '请选择区/县';
$_LANG['order_step_js_notic_06'] = '请填写收货地址';
$_LANG['order_step_js_notic_07'] = '请填写手机号码';
$_LANG['order_step_js_notic_08'] = '手机号码不正确';
$_LANG['order_step_js_notic_09'] = '商品库存不足';
$_LANG['order_step_js_notic_10'] = '请先添加商品';
$_LANG['order_step_js_notic_11'] = '您修改的费用信息折扣不能大于实际订单总金额';
$_LANG['order_step_js_notic_12'] = '，会产生退款，一旦退款则将扣除您账户里面的资金';
$_LANG['order_step_js_notic_13'] = '您修改的费用信息产生了负数（';
$_LANG['order_step_js_notic_14'] = '，会是否继续？';

$_LANG['lab_bar_shop_price'] = '本店售价';
$_LANG['lab_bar_market_price'] = '市场价';

$_LANG['refund_way'] = '退款方式';
$_LANG['return_balance'] = '退回到余额';

/* 页面顶部操作提示 */
$_LANG['operation_prompt_content']['return_cause_list'][0] = '商城退货原因信息列表管理。';
$_LANG['operation_prompt_content']['return_cause_list'][1] = '可进行删除或修改退换货原因。';

$_LANG['operation_prompt_content']['return_cause_info'][0] = '可选择已有的顶级原因分类。';

$_LANG['operation_prompt_content']['back_list'][0] = '商城所有退货订单列表管理。';
$_LANG['operation_prompt_content']['back_list'][1] = '可通过订单号进行查询，侧边栏进行高级搜索。';

$_LANG['operation_prompt_content']['delivery_list'][0] = '商城和平台所有已发货的订单列表管理。';
$_LANG['operation_prompt_content']['delivery_list'][1] = '可通过订单号进行查询，侧边栏进行高级搜索。';
$_LANG['operation_prompt_content']['delivery_list'][2] = '可进入查看取消发货。';

$_LANG['operation_prompt_content']['detection_list'][0] = '检测商城所有已发货订单。';
$_LANG['operation_prompt_content']['detection_list'][1] = '可通过订单号进行查询，侧边栏进行高级搜索。';
$_LANG['operation_prompt_content']['detection_list'][2] = '一键确认收货功能说明：使用一键确认收货功能，只能针对当前页面的订单条数进行操作处理，如需一次操作更多的订单条数，可修改每页显示的条数进行操作处理。';

$_LANG['operation_prompt_content']['list'][0] = '商城所有的订单列表，包括平台自营和入驻商家的订单。';
$_LANG['operation_prompt_content']['list'][1] = '点击订单号即可进入详情页面对订单进行操作。';
$_LANG['operation_prompt_content']['list'][2] = 'Tab切换不同状态下的订单，便于分类订单。';

$_LANG['operation_prompt_content']['search'][0] = '订单查询提供多个条件进行精准查询。';

$_LANG['operation_prompt_content']['step'][0] = '添加订单流程为：选择商城已有会员-选择商品加入订单-确认订单金额-填写收货信息-添加配送方式-选择支付方式-添加发票-查看费用信息-完成。';

$_LANG['operation_prompt_content']['return_list'][0] = '买家提交的退换货申请信息管理。';
$_LANG['operation_prompt_content']['return_list'][1] = '可通过订单号进行查询，侧边栏进行高级搜索。';

$_LANG['operation_prompt_content']['templates'][0] = '下单成功后，可打印订单详情。';
$_LANG['operation_prompt_content']['templates'][1] = '请不要轻易修改模板变量，否则会导致数据无法正常显示。';
$_LANG['operation_prompt_content']['templates'][2] = '变量说明：
                    <p>$lang.***：语言项，修改路径：根目录/languages/zh_cn/admin/order.php</p>
                    <p>$order.***：订单数据，请参考dsc_order数据表</p>
                    <p>$goods.***：商品数据，请参考dsc_goods数据表</p>';

$_LANG['stores_name'] = '门店名称：';
$_LANG['stores_address'] = '门店地址：';
$_LANG['stores_tel'] = '联系方式：';
$_LANG['stores_opening_hours'] = '营业时间：';
$_LANG['stores_traffic_line'] = '交通线路：';
$_LANG['stores_img'] = '实景图片：';
$_LANG['pick_code'] = '提货码：';


// 商家后台
$_LANG['label_sure_collect_goods_time'] = '确认收货时间：';
$_LANG['label_order_cate'] = '订单分类：';
$_LANG['label_id_code'] = '识别码：';
$_LANG['label_get_post_address'] = '收票地址：';

$_LANG['js_languages']['jl_merge_order'] = '合并订单';
$_LANG['js_languages']['jl_merge'] = '合并';
$_LANG['js_languages']['jl_sure_merge_order'] = '您确定合并这两个订单么？';
$_LANG['js_languages']['jl_order_step_js_notic_11'] = '您修改的费用信息折扣不能大于实际订单总金额';
$_LANG['js_languages']['jl_order_step_js_notic_12'] = '，会产生退款，一旦退款则将扣除您账户里面的资金';
$_LANG['js_languages']['jl_order_step_js_notic_13'] = '您修改的费用信息产生了负数（';
$_LANG['js_languages']['jl_order_step_js_notic_14'] = '，是否继续？';
$_LANG['js_languages']['jl_order_export_dialog'] = '订单导出弹窗';
$_LANG['js_languages']['jl_set_rob_order'] = '设置抢单';
$_LANG['js_languages']['jl_vat_info'] = '增值税发票信息';
$_LANG['js_languages']['jl_search_logistics_info'] = '正在查询物流信息，请稍后...';
$_LANG['js_languages']['jl_goods_delivery'] = '商品发货';

$_LANG['add_order_step'][0] = '选择下单用户';
$_LANG['add_order_step'][1] = '选择订单商品';
$_LANG['add_order_step'][2] = '填写收货地址';
$_LANG['add_order_step'][3] = '选择配送方式';
$_LANG['add_order_step'][4] = '选择支付方式';
$_LANG['add_order_step'][5] = '填写发票信息';
$_LANG['add_order_step'][6] = '填写费用信息';

$_LANG['print_shipping_form'] = '打印发货单';
$_LANG['current'] = '当前';
$_LANG['store_info'] = '门店信息';

$_LANG['this_order_return_no_continue'] = '此订单已确认为退换货订单，无法继续订单操作！';
$_LANG['no_info_fill_express_number'] = '暂无信息,请填写快递单号';

// 门店核销
$_LANG['store_id_required'] = "门店id不能为空";
$_LANG['order_id_required'] = "订单id不能为空";
$_LANG['pick_code_required'] = "请输入提货码或订单号";
$_LANG['store_order_empty'] = "未找到符合条件门店订单";
$_LANG['store_order_picked_up'] = "门店订单已提货";
// 提货状态
$_LANG['pick_up_status_0'] = "待提货";
$_LANG['pick_up_status_1'] = "已提货";
$_LANG['store_manage'] = "门店管理员";

return $_LANG;
