<?php

$_LANG['bill_over'] = '账单已结束';
$_LANG['anonymous'] = '匿名用户';
$_LANG['seller_commission'] = '店铺结算';
$_LANG['commission_setup'] = '结算设置';

$_LANG['seller_bill_account'] = '【账单】%s';
$_LANG['seller_bill_settlement'] = '【%s】商家账单结算';
$_LANG['seller_bill_unfreeze'] = '【%s】商家账单解冻金额';

$_LANG['update_bill_failure'] = '账单更新失败';
$_LANG['update_bill_success'] = '账单更新成功';

$_LANG['apply_for_failure_time'] = '账单未出账，无法申请账单';
$_LANG['apply_for_failure'] = '账单申请失败';
$_LANG['apply_for_success'] = '账单申请成功';
$_LANG['trash_goods_confirm'] = '您确实要删除账单吗？';

$_LANG['commission_bill_detail'] = '账单明细';
$_LANG['commission_bill'] = '账单列表';
$_LANG['commission_model'] = '结算模式';

$_LANG['settlement_cycle'] = '账单结算周期';
$_LANG['cfg_range']['settlement_cycle']['0'] = '每天';
$_LANG['cfg_range']['settlement_cycle']['1'] = '1周（七天）';
$_LANG['cfg_range']['settlement_cycle']['2'] = '15天（半个月）';
$_LANG['cfg_range']['settlement_cycle']['3'] = '1个月';
$_LANG['cfg_range']['settlement_cycle']['4'] = '1个季度';
$_LANG['cfg_range']['settlement_cycle']['5'] = '6个月';
$_LANG['cfg_range']['settlement_cycle']['6'] = '1年';
$_LANG['cfg_range']['settlement_cycle']['7'] = '按天数';

$_LANG['label_press_day_number'] = '按天数';

$_LANG['01_admin_settlement'] = "【%s】平台操作商家应结金额，订单号：%s";
$_LANG['effective_favorable'] = '有效优惠';
$_LANG['freeze_status'] = '冻结状态';

$_LANG['commission'] = '结算';
$_LANG['category_model'] = '按分类比例';
$_LANG['seller_model'] = '按店铺比例';
$_LANG['commission_channel_menu'] = [
    '默认账单',
    '收付通账单',
];
$_LANG['negative_channel_menu'] = [
    '默认负账单',
    '收付通负账单',
];
$_LANG['divide_channel_notice'] = '同意申请后可以发起收付通分账操作，商家结算金额将解冻，解冻后商家可将账户金额直接提现，请谨慎操作';
$_LANG['order_divide'] = '订单分账';
$_LANG['order_divide_query'] = '查询分账';
$_LANG['order_divide_batch'] = '批量分账';
$_LANG['order_divide_limit'] = '超过单次最大可分账订单数量，请减少数量再操作';
$_LANG['order_divide_confirm'] = '确定要分账吗？分账成功后商家结算金额将解冻，请谨慎操作';

/* 菜单 */
$_LANG['search_user'] = '搜索会员';
$_LANG['order_valid_total'] = '有效金额';
$_LANG['order_refund_total'] = '退款';
$_LANG['order_total'] = '订单有效总额';
$_LANG['order_start_time'] = '开始时间';
$_LANG['order_end_time'] = '结束时间';
$_LANG['is_settlement_amount'] = '已结算订单金额';
$_LANG['no_settlement_amount'] = '未结算订单金额';
$_LANG['effective_amount_into'] = '有效结算';

$_LANG['brokerage_amount_list'] = '店铺结算';
$_LANG['brokerage_order_list'] = '店铺订单列表';

$_LANG['add_suppliers_server'] = '设置商家结算';
$_LANG['edit_suppliers_server'] = '编辑商家结算';
$_LANG['suppliers_list_server'] = '店铺结算';
$_LANG['suppliers_bacth'] = '结算批量设置';

$_LANG['please_choose'] = '请选择...';
$_LANG['batch_remove'] = '批量删除';
$_LANG['batch_closed'] = '批量结算';

$_LANG['commission_total'] = '佣金总金额：';

/* 列表页 */
$_LANG['suppliers_name'] = '会员名称';
$_LANG['suppliers_store'] = '店铺名称';
$_LANG['suppliers_company'] = '公司名称';
$_LANG['suppliers_address'] = '公司地址';
$_LANG['suppliers_contact'] = '联系方式';

$_LANG['suppliers_percent'] = '奖励金额';
$_LANG['suppliers_check'] = '状态';
$_LANG['suppliers_percent_list'] = '结算百分比列表';
$_LANG['export_all_suppliers'] = '导出表格'; //liu
$_LANG['export_merchant_commission'] = '导出商家结算表格'; //liu
$_LANG['is_settlement_amount'] = '已结算';//liu
$_LANG['no_settlement_amount'] = '未结算';//liu

/*批量操作*/
$_LANG['is not supported'] = '暂不支持此功能';
$_LANG['no_order'] = '无可操作的订单!';
$_LANG['batch_closed_success'] = '批量结算成功';
$_LANG['choose_batch'] = '请选择您的操作!';

/* 详情页 */
$_LANG['label_suppliers_server_desc'] = '结算描述：';
$_LANG['label_suppliers_percent'] = '应结百分比：';

/* 系统提示 */
$_LANG['continue_add_server_suppliers'] = '继续设置商家结算';
$_LANG['back_suppliers_server_list'] = '返回结算设置';
$_LANG['suppliers_server_ok'] = '设置商家结算成功';
$_LANG['batch_drop_ok'] = '批量删除成功';
$_LANG['batch_drop_no'] = '批量删除失败';
$_LANG['suppliers_edit_fail'] = '名称修改失败';
$_LANG['no_record_selected'] = '没有选择任何记录';

$_LANG['toggle_on_settlement_prompt_one'] = '该订单已被冻结，不能进行此操作！';

/* JS提示 */
$_LANG['js_languages']['no_suppliers_server_name'] = '没有设置商家结算';
$_LANG['js_languages']['select_user_name'] = '请选择会员名称';
$_LANG['js_languages']['no_suppliers_percent'] = '请选择结算比例';

//商家订单列表
$_LANG['order_sn'] = '订单编号';
$_LANG['order_time'] = '下单时间';
$_LANG['consignee'] = '收货人';
$_LANG['total_fee'] = '总金额';
$_LANG['order_amount'] = '应付金额';
$_LANG['return_amount'] = '退款金额';
$_LANG['return_shippingfee'] = '退运费金额';
$_LANG['return_rate_price'] = '退跨境税费金额';
$_LANG['not_contain_rate'] = '不含跨境税费';
$_LANG['all_status'] = '订单状态';
$_LANG['brokerage_amount'] = '应结金额';
$_LANG['all_brokerage_amount'] = '应结总金额';
$_LANG['all_drp_amount'] = '分销结算总额';
$_LANG['drp_comm'] = '分销结算';
$_LANG['is_brokerage_amount_money'] = '已结算金额';
$_LANG['no_brokerage_amount_money'] = '未结算金额';
$_LANG['all_order'] = '所有订单';
$_LANG['is_settlement'] = '已结算';
$_LANG['is_brokerage_amount'] = '已结';
$_LANG['no_brokerage_amount'] = '未结';
$_LANG['gain_is_settlement'] = '已收';
$_LANG['gain_no_settlement'] = '未收';
$_LANG['no_settlement'] = '未结算';
$_LANG['effective_settlement_amount'] = '有效结算金额';

$_LANG['settlement_state'] = '结算状态';
$_LANG['percent_value'] = '结算百分比';

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
$_LANG['os'][OS_RETURNED_PART] = '<font color="red">部分已退货</font>';
$_LANG['os'][OS_ONLY_REFOUND] = '<font color="red">仅退款</font>';

$_LANG['ss'][SS_UNSHIPPED] = '未发货';
$_LANG['ss'][SS_PREPARING] = '配货中';
$_LANG['ss'][SS_SHIPPED] = '已发货';
$_LANG['ss'][SS_RECEIVED] = '收货确认';
$_LANG['ss'][SS_SHIPPED_PART] = '已发货(部分商品)';
$_LANG['ss'][SS_SHIPPED_ING] = '发货中';
$_LANG['ss'][SS_PART_RECEIVED] = '部分已收货';

$_LANG['ps'][PS_UNPAYED] = '未付款';
$_LANG['ps'][PS_PAYING] = '付款中';
$_LANG['ps'][PS_PAYED] = '已付款';
$_LANG['ps'][PS_PAYED_PART] = '部分付款(定金)';
$_LANG['ps'][PS_REFOUND] = '已退款';
$_LANG['ps'][PS_REFOUND_PART] = '部分退款';
$_LANG['ps'][PS_MAIN_PAYED_PART] = '部分已付款'; //主订单

$_LANG['refund'] = '退款';

//  by kong
$_LANG['handle_log'] = '操作日志';
$_LANG['admin_log'] = '操作人';
$_LANG['addtime'] = '操作时间';

$_LANG['not_settlement'] = '您已结算，钱已进入商家账户，不允许二次操作';
$_LANG['edit_order'] = '订单';

/* 微分销 */
$_LANG['all_drp_amount'] = '分销结算总额';
$_LANG['drp_comm'] = '分销结算';
$_LANG['drp_money'] = '分销金额';
/* 微分销 end */

$_LANG['suppliers_name_exist'] = '该商家已经设置结算';

/* 佣金订单导出 */
$_LANG['down']['order_sn'] = '订单编号';
$_LANG['down']['short_order_time'] = '下单时间';
$_LANG['down']['consignee_address'] = '收货人';
$_LANG['down']['total_fee'] = '总金额';
$_LANG['down']['shipping_fee'] = '运费';
$_LANG['down']['discount'] = '折扣';
$_LANG['down']['coupons'] = '优惠券';
$_LANG['down']['integral_money'] = '积分金额';
$_LANG['down']['bonus'] = '红包';
$_LANG['down']['dis_amount'] = '商品满减';
$_LANG['down']['return_amount_price'] = '退款金额';
$_LANG['down']['brokerage_amount_price'] = '有效分成金额';
$_LANG['down']['effective_amount_price'] = '订单状态';
$_LANG['down']['settlement_status'] = '应结金额';
$_LANG['down']['ordersTatus'] = '结算状态';

$_LANG['proportion'] = '收取比例';
$_LANG['is_receive_commissions'] = '已收取佣金';
$_LANG['receive_commissions'] = '收取佣金';
$_LANG['proportions'] = '应结比例';
$_LANG['settlement_commission'] = '已结算佣金';
$_LANG['period_checkout'] = '本期应结';
$_LANG['total'] = '总额';
$_LANG['knot'] = '已结';
$_LANG['real'] = '实结';
$_LANG['is_frozen_amount'] = '已冻结金额';
$_LANG['frozen_days_set'] = '已设置冻结天数';
$_LANG['reduce'] = '减少';
$_LANG['number_days'] = '天数';
$_LANG['frozen'] = '冻结';
$_LANG['frozen_amount'] = '冻结金额';
$_LANG['frozen_amount_notic'] = '可输入需要冻结账单的部分金额';
$_LANG['frozen_time'] = '冻结时间';
$_LANG['frozen_time_notic'] = '备注：可输入冻结时间，时间以账单结束时间 + 冻结天数 = 冻结资金解冻';
$_LANG['frozen_money'] = '解冻金额';
$_LANG['frozen_money_notic'] = '备注：解冻账单金额，解冻金额将直接汇入商家可用余额；解冻金额等于已冻结金额，冻结时间将清零。';
$_LANG['bill_number'] = '账单编号';
$_LANG['bill_time'] = '账单时间';
$_LANG['bill_money'] = '订单金额';
$_LANG['freight_money'] = '运费金额';
$_LANG['back_money'] = '退单金额';
$_LANG['back_money_notic'] = '（备注：退单金额 + 退运费 = 退单金额）';
$_LANG['out_of_account_state'] = '出账状态';
$_LANG['chargeoff_status'][0] = '未出账';
$_LANG['chargeoff_status'][1] = '已出账';
$_LANG['chargeoff_status'][2] = '账单结束';
$_LANG['chargeoff_status'][3] = '关闭账单';
$_LANG['chargeoff_time'] = '出账时间';
$_LANG['application_status'] = '申请状态';
$_LANG['application_yes'] = '已申请';
$_LANG['application_no'] = '未申请';
$_LANG['application_time'] = '申请时间';
$_LANG['chargeoff_desc'] = '账单描述';
$_LANG['bill_adopt_status'][0] = '待处理';
$_LANG['bill_adopt_status'][1] = '同意';
$_LANG['bill_adopt_status'][2] = '拒绝';
$_LANG['bill_status'][0] = '同意结账';
$_LANG['bill_status'][1] = '拒绝结账';
$_LANG['bill_status_no_notic'] = '拒绝账单理由';

$_LANG['bill_commission_model'] = '账单佣金模式';
$_LANG['cata_commission_rate'] = '分类佣金比例';
$_LANG['shop_commission_rate'] = '店铺佣金比例';

$_LANG['chargeoff_front'] = '出账前';
$_LANG['chargeoff_after'] = '出账后';
$_LANG['manual_settlement'] = '手动结算';
$_LANG['freight_charges'] = '含运费';
$_LANG['freight_charges_not'] = '不含运费';
$_LANG['order_detailed'] = '订单明细';
$_LANG['correct_rate'] = '纠正比例';
$_LANG['shop_rate'] = '店铺比例';
$_LANG['part_goods_rate'] = '部分商品比例';

$_LANG['goods_sn'] = '商品编号';
$_LANG['category_name'] = '分类名称';
$_LANG['goods_price'] = '商品价格';
$_LANG['goods_number'] = '商品数量';
$_LANG['commission_rate'] = '佣金比例';
$_LANG['return_goods'] = '已退货';
$_LANG['curr_mode'] = '当前模式';
$_LANG['uncollected_goods_order'] = '未收货订单(个)';
$_LANG['audit_bill'] = '审核账单';
$_LANG['thawing_bill'] = '解冻账单';
$_LANG['frozen_bill'] = '冻结账单';
$_LANG['delete_bill'] = '删除账单';
$_LANG['account_credit_line'] = '账户信用额度';
$_LANG['start_account_bill_time'] = '开始生产账单时间';
$_LANG['frozen_bill_time'] = '账单冻结时间';
$_LANG['part_goods'] = '部分【商品】';
$_LANG['current_commission_model'] = '当前佣金模式';
$_LANG['admin_commission'] = '平台佣金';
$_LANG['all_shops'] = '所有店铺';

$_LANG['total_amount'] = '累积销售总额';
$_LANG['no_settlement'] = '待结算金额';
$_LANG['is_settlement'] = '已结算金额';
$_LANG['store_num'] = '店铺商家';

/*高级搜索 订单筛选*/
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

$_LANG['account_credit_line_notic'] = '（备注：用于限制商家订单退款允许操作的金额）';
$_LANG['label_suppliers_percent_notic'] = '%（应结给商家订单金额的百分比）';
$_LANG['settlement_cycle_notic'] = '按账单结算周期生成账单进行结算，比如1个月，则账单是（2017-01-01至2017-01-31）';
$_LANG['start_account_bill_time_notic'] = '（备注：设置天数按10天，记账日期设定为2017-01-01，则出账时间为2017-01-11， 以此类推，下一个出账日期为2017-01-21）';
$_LANG['frozen_bill_time_notic'] = '天 （备注：设置账单冻结时间，可控制账单出账后多少天内无法申请结算账单，方便平台控制现金流）';

$_LANG['batch_confirm_settlement'] = '确定批量结算？';
$_LANG['merchant_download_notic'] = '订单结算导出弹窗';

$_LANG['commission_download_title_not'] = '商家名称,店铺名称,公司名称,公司地址,联系方式,订单有效总金额,订单退款总金额,已结算订单金额,未结算订单金额';

/* 页面顶部操作提示 */
$_LANG['operation_prompt_content']['apply_for'][0] = '涉及资金管理请谨慎处理信息。';

$_LANG['operation_prompt_content']['detail'][0] = '账单结算明细。';
$_LANG['operation_prompt_content']['detail'][1] = '当前页面仅显示确认收货订单。';
$_LANG['operation_prompt_content']['detail'][2] = '运费金额将不计入收取佣金。';
$_LANG['operation_prompt_content']['detail'][3] = '退单金额将不计入收取佣金。';
$_LANG['operation_prompt_content']['detail'][4] = '【订单】：表示是按店铺比例或者分类比例。';
$_LANG['operation_prompt_content']['detail'][5] = '【商品】：表示是商品单独设置比例结算。';

$_LANG['operation_prompt_content']['bill_goods'][0] = '账单结算明细。';
$_LANG['operation_prompt_content']['bill_goods'][1] = '当前页面仅显示订单商品。';
$_LANG['operation_prompt_content']['bill_goods'][2] = '【订单】：表示是按店铺佣金比例或者分类佣金比例。';
$_LANG['operation_prompt_content']['bill_goods'][3] = '【商品】：表示是商品单独设置佣金比例。';

$_LANG['operation_prompt_content']['bill'][0] = '账单结算列表。';
$_LANG['operation_prompt_content']['bill'][1] = '账单时间范围内的所有订单确认收货，方可出账。';
$_LANG['operation_prompt_content']['bill'][2] = '以用户确认收货订单时间为准。';
$_LANG['operation_prompt_content']['bill'][3] = '账单出账以账单到期的第二天出账。';
$_LANG['operation_prompt_content']['bill'][4] = '具体说明如下：';
$_LANG['operation_prompt_content']['bill'][5] = '（比如账单结算周期为每天，以2017年01月01日为例，2017年01月02日份出账单，以每隔1天出一次账单，即账单到期第二天出账）';
$_LANG['operation_prompt_content']['bill'][6] = '（比如账单结算周期为1周，2017.01.01 ~ 2017.01.08为周期，下个周期账单2017.01.09 ~ 2017.01.16）';
$_LANG['operation_prompt_content']['bill'][7] = '（比如账单结算周期为15天(半个月)，2017.01.01 ~ 2017.01.15为周期，下个周期账单2017.01.16 ~ 2017.01.31）';

$_LANG['operation_prompt_content']['notake_order'][0] = '佣金账单明细。';
$_LANG['operation_prompt_content']['notake_order'][1] = '当前页面仅显示未确认收货订单。';

$_LANG['operation_prompt_content']['info'][0] = '请选择应结百分比。';
$_LANG['operation_prompt_content']['info'][1] = '请设定结算周期。';
$_LANG['operation_prompt_content']['info'][2] = '说明：一旦运营设置结算周期，请不要随意更变结算周期，以避免出现账单错乱，要更换结算周期最好在每年年初（后三天）一次进行更换。';

$_LANG['operation_prompt_content']['list'][0] = '商城所有店铺结算订单相关信息管理。';
$_LANG['operation_prompt_content']['list'][1] = '可查看店铺所有的结算订单，并进行相关操作。';
$_LANG['operation_prompt_content']['list'][2] = '【订单】：表示是按店铺结算比例或者分类结算比例';
$_LANG['operation_prompt_content']['list'][3] = '【商品】：表示是商品单独设置结算比例';
$_LANG['operation_prompt_content']['list'][4] = '累积销售总额：表示是订单商品已完成：已确认、已付款、已收货';

$_LANG['operation_prompt_content']['order_list'][0] = '商家结算的所有已付款已确认收货，并且未退款的订单列表。';
$_LANG['operation_prompt_content']['order_list'][1] = '可进行查看操作日志。';
$_LANG['operation_prompt_content']['order_list'][2] = '导出是按照分页数导出，为避免导出卡顿，超时报错，每页订单数量不要太大。';
$_LANG['operation_prompt_content']['order_list'][3] = '结算百分比数值越大，商家获取的结算金额就越大。';
$_LANG['operation_prompt_content']['order_list'][4] = '总金额 - 运费 = 有效结算。';
$_LANG['operation_prompt_content']['order_list'][5] = '有效结算 * 结算百分比 + 运费 = 应结金额。';
$_LANG['operation_prompt_content']['order_list'][6] = '从2017年5月17日起，有效结算不包含运费金额。';
$_LANG['operation_prompt_content']['order_list'][7] = '结算百分比数值越大，商家获取的结算金额就越大。';

$_LANG['operation_prompt_content']['log'][0] = '查看订单操作日志记录列表，可根据记录列表查看订单具体操作步骤。';

$_LANG['01_monthly_checkout_bill'] = '月结账单';
$_LANG['02_weekly_bill'] = '周结账单';
$_LANG['03_daily_checkout_bill'] = '日结账单';
$_LANG['04_3_day_checkout_bill'] = '3日结账单';

$_LANG['label_start_product_bill_time'] = '开始生产账单时间：';

$_LANG['negative_number'] = '负账单编号';
$_LANG['negative_status'] = '状态';
$_LANG['negative_chargeoff_status'][0] = '未结算';
$_LANG['negative_chargeoff_status'][1] = '已结算';
$_LANG['negative_binding'] = '未绑定';
$_LANG['negative_bill_detail'] = '负账单明细';
$_LANG['negative_return_time'] = '退款时间';
$_LANG['return_sn'] = '退款流水号';
$_LANG['return_total'] = '退款总金额';
$_LANG['return_shipping_total'] = '退运费总金额';
$_LANG['negative_order_list'] = '负账单订单列表';
$_LANG['negative_total'] = '负账单金额';
$_LANG['actual_deducted'] = '实际扣除金额';
$_LANG['actual_deducted_total'] = '实际扣除总金额';

$_LANG['operation_prompt_content']['negative_order_list'][0] = '商家负账单的所有退款订单列表。';

$_LANG['statistics_profit'] = '统计利润';
$_LANG['self_no_profit'] = '平台未结算利润';
$_LANG['seller_no_profit'] = '店铺未结算利润';
$_LANG['self_is_profit'] = '平台已结算利润';
$_LANG['seller_is_profit'] = '店铺已结算利润';
$_LANG['commission_self_data'] = '平台数据';
$_LANG['commission_seller_data'] = '店铺数据';

$_LANG['goods_mount'] = '商品小计';

return $_LANG;
