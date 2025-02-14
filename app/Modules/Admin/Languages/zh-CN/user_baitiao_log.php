<?php

$_LANG['bt_ur_here'] = '设置会员白条额度';
$_LANG['bt_list'] = '白条列表';
$_LANG['bt_details'] = '白条详情';
$_LANG['user_name'] = "会员名称";
$_LANG['financial_credit'] = "金融额度";
$_LANG['Credit_payment_days'] = '信用账期';
$_LANG['Suspended_term'] = "信用账期缓期期限";
$_LANG['user_baitiao_credit'] = "会员白条额度";
$_LANG['yes_delete_biaotiao'] = "你确定要删除该会员的会员白条吗？";
$_LANG['yes_delete_record'] = "你确定要删除该消费记录吗？";

$_LANG['total_amount'] = "总额度";
$_LANG['residual_amount'] = "剩余额度";
$_LANG['repayments_amount'] = "已还款总额";
$_LANG['pending_repayment_amount'] = "待还款总额";
$_LANG['baitiao_number'] = "白条数";

$_LANG['consumption_money'] = "消费记录";
$_LANG['billing_day'] = "消费记账日";
$_LANG['repayment_data'] = "客户还款日";
$_LANG['repayment_cycle'] = "还款周期";
$_LANG['order_amount'] = '应付金额';
$_LANG['conf_pay'] = "支付状态";

$_LANG['baitiao_by_stage'] = "白条分期";
$_LANG['order_refund'] = "已失效,订单已退款";
$_LANG['yuan_stage'] = "元/期";
$_LANG['stage'] = "期";
$_LANG['is_pay'] = '已付款';
$_LANG['dai_pay'] = '待付款';

$_LANG['users_note'] = "基本信息";
$_LANG['address_list'] = '收货地址';
$_LANG['view_order'] = '查看订单';
$_LANG['set_baitiao'] = "白条设置";
$_LANG['account_log'] = '账目明细';

$_LANG['baitiao_add'] = '开通会员白条';
$_LANG['search_user_name'] = '输入会员名或手机号搜索，查询会员信息，开通会员白条';
$_LANG['user_name_required'] = '会员名或手机号不能为空！';
$_LANG['user_name_not_exist'] = '会员名或手机号不存在';
$_LANG['user_baitiao_log_exist'] = '该会员已经开通过白条了！';
$_LANG['user_info'] = '会员信息';

$_LANG['set_baitiao_tips'] = [
    '该页面为白条支付设置页面，可设置是否安装启用白条支付',
    '启用后，用户购物即可使用白条支付，请谨慎设置'
];
$_LANG['set_baitiao_open'] = '启用白条支付';
$_LANG['set_baitiao_notice'] = '启用白条支付后，需配置会员白条额度，配置完成指定会员可使用白条支付下单购物';

$_LANG['baitiao_batch_set'] = '白条批量设置';
$_LANG['baitiao_update_success'] = '会员白条更新成功！';
$_LANG['baitiao_set_success'] = '会员白条设置成功！';
$_LANG['baitiao_remove_success'] = '删除白条成功';
$_LANG['no_del_num_notic'] = '条待付款记录不可删除';
$_LANG['remove_consume_success'] = '删除消费记录成功';
$_LANG['biao_remove_consume_success'] = '删除白条记录成功';
$_LANG['pay_not_consume_remove'] = '待付款的记录不可删除';

/*提示*/
$_LANG['notice_financial_credit'] = '元 如:3000元　(用户可支配的信用款额度)';
$_LANG['notice_Credit_payment_days'] = '天 如:30天　(会根据此值自动生成还款日期,到期未还款,用户将不能够用白条方式支付)';
$_LANG['notice_Suspended_term'] = "天 如:10天　(超过信用期限+该文本设置的天数总和后，用户将不能够下单)";

/* js 验证提示 */
$_LANG['confirm_bath'] = '你确定要删除所选的会员白条吗？';
$_LANG['js_languages']['amount_not_null'] = '金融额度不能为空';
$_LANG['js_languages']['repay_term_not_null'] = '信用账期不能为空';
$_LANG['js_languages']['over_repay_trem_not_null'] = '信用账期缓期期限不能为空';

/* 页面顶部操作提示 */
$_LANG['operation_prompt_content']['baitiao_list'][0] = '该页面展示了会员白条相关信息。';
$_LANG['operation_prompt_content']['baitiao_list'][1] = '可查看白条消费的订单信息，可设置白条额度等操作。';
$_LANG['operation_prompt_content']['baitiao_list'][2] = '可以输入会员名称关键字进行搜索。';

$_LANG['operation_prompt_content']['baitiao_log_list'][0] = '该页面展示白条消费订单信息。';
$_LANG['operation_prompt_content']['baitiao_log_list'][1] = '请谨慎操作白条信息。';

return $_LANG;
