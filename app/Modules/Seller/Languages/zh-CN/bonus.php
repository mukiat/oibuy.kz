<?php

/* 红包类型字段信息 */
$_LANG['bonus_type'] = '红包类型';
$_LANG['bonus_list'] = '红包列表';
$_LANG['user_bonus_send_time'] = '使用时间类型';
$_LANG['type_name'] = '类型名称';
$_LANG['valid_period'] = '红包有效期';
$_LANG['type_money'] = '红包金额';
$_LANG['min_goods_amount'] = '最小订单金额';
$_LANG['notice_min_goods_amount'] = '只有商品总金额达到这个数的订单才能使用这种红包';
$_LANG['min_amount'] = '订单下限';
$_LANG['max_amount'] = '订单上限';
$_LANG['send_startdate'] = '发放起始日期';
$_LANG['send_enddate'] = '发放结束日期';
$_LANG['add_bonus_type'] = '添加红包类型';

$_LANG['use_startdate'] = '使用起始日期';
$_LANG['use_enddate'] = '使用结束日期';
$_LANG['send_count'] = '发放量';
$_LANG['use_count'] = '使用量';
$_LANG['send_method'] = '如何发放此类型红包';
$_LANG['send_type'] = '发放类型';
$_LANG['param'] = '参数';
$_LANG['no_use'] = '未使用';
$_LANG['yuan'] = '元';
$_LANG['user_list'] = '会员列表';
$_LANG['type_name_empty'] = '红包类型名称不能为空！';
$_LANG['type_money_empty'] = '红包金额不能为空！';
$_LANG['min_amount_empty'] = '红包类型的订单下限不能为空！';
$_LANG['max_amount_empty'] = '红包类型的订单上限不能为空！';
$_LANG['send_count_empty'] = '红包类型的发放数量不能为空！';
$_LANG['not_select_any_data'] = '没有选择任何数据';
$_LANG['bonus_audited_type_set_ok'] = '红包类型审核状态设置成功';

$_LANG['send_by'][SEND_BY_USER] = '按用户发放';
$_LANG['send_by'][SEND_BY_GOODS] = '按商品发放';
$_LANG['send_by'][SEND_BY_ORDER] = '按订单金额发放';
$_LANG['send_by'][SEND_BY_PRINT] = '线下发放的红包';
$_LANG['send_by'][SEND_BY_GET] = '自行领取';
$_LANG['report_form'] = '报表下载';
$_LANG['send'] = '发放';
$_LANG['bonus_excel_file'] = '线下红包信息列表';
$_LANG['batch_drop_ok'] = '批量删除成功';

$_LANG['goods_cat'] = '选择商品分类';
$_LANG['goods_brand'] = '商品品牌';
$_LANG['goods_key'] = '商品关键字';
$_LANG['all_goods'] = '可选商品';
$_LANG['send_bouns_goods'] = '发放此类型红包的商品';
$_LANG['remove_bouns'] = '移除红包';
$_LANG['all_remove_bouns'] = '全部移除';
$_LANG['goods_already_bouns'] = '该商品已经发放过其它类型的红包了!';
$_LANG['send_user_empty'] = '您没有选择需要发放红包的会员，请返回!';
$_LANG['batch_drop_success'] = '成功删除了 %d 个用户红包';
$_LANG['sendbonus_count'] = '共发送了 %d 个红包。';
$_LANG['send_bouns_error'] = '发送会员红包出错, 请返回重试！';
$_LANG['no_select_bonus'] = '您没有选择需要删除的用户红包';
$_LANG['bonustype_edit'] = '编辑红包类型';
$_LANG['bonustype_view'] = '查看详情';
$_LANG['drop_bonus'] = '删除红包';
$_LANG['send_bonus'] = '发放红包';
$_LANG['continus_add'] = '继续添加红包类型';
$_LANG['back_list'] = '返回红包类型列表';
$_LANG['continue_add'] = '继续添加红包';
$_LANG['back_bonus_list'] = '返回红包列表';
$_LANG['validated_email'] = '只给通过邮件验证的用户发放红包';
$_LANG['attradd_succed'] = '操作成功!';

/* js提示信息 */
$_LANG['js_languages']['type_name_empty'] = '请输入红包类型名称';
$_LANG['js_languages']['valid_period_empty'] = '请输入红包有效期';
$_LANG['js_languages']['type_money_empty'] = '请输入红包类型价格';
$_LANG['js_languages']['order_money_empty'] = '请输入订单金额';
$_LANG['js_languages']['type_money_isnumber'] = '类型金额必须为数字格式';
$_LANG['js_languages']['order_money_isnumber'] = '订单金额必须为数字格式';
$_LANG['js_languages']['bonus_sn_empty'] = '请输入红包的序列号';
$_LANG['js_languages']['bonus_sn_number'] = '红包的序列号必须是数字';
$_LANG['js_languages']['bonus_sum_empty'] = '请输入您要发放的红包数量';
$_LANG['js_languages']['bonus_sum_number'] = '红包的发放数量必须是一个整数';
$_LANG['js_languages']['bonus_type_empty'] = '请选择红包的类型金额';
$_LANG['js_languages']['user_rank_empty'] = '您没有指定会员等级';
$_LANG['js_languages']['user_name_empty'] = '您至少需要选择一个会员';
$_LANG['js_languages']['invalid_min_amount'] = '请输入订单下限（大于0的数字）';
$_LANG['js_languages']['send_start_lt_end'] = '红包发放开始日期不能大于结束日期';
$_LANG['js_languages']['use_start_lt_end'] = '红包使用开始日期不能大于结束日期';
$_LANG['js_languages']['min_order_total'] = '请输入最小订单金额';
$_LANG['send_count_error'] = '红包的发放数量必须是一个整数';

$_LANG['order_min_money_notic'] = '只要订单金额达到该数值，就会发放红包给用户';
$_LANG['valid_period_notic'] = '（单位：天，整数大于0)';
$_LANG['order_max_money_notic'] = '0表示没有上限';
$_LANG['bonus_send_time_beyond'] = '有效期必须大于0';
$_LANG['type_money_notic'] = '此类型的红包可以抵销的金额';
$_LANG['send_startdate_notic'] = '只有当前时间介于起始日期和截止日期之间时，此类型的红包才可以发放';
$_LANG['use_startdate_notic'] = '只有当前时间介于起始日期和截止日期之间时，此类型的红包才可以使用';
$_LANG['type_name_exist'] = '此类型的名称已经存在!';
$_LANG['type_money_beyond'] = '红包金额不得大于最小订单金额!';
$_LANG['type_money_error'] = '金额必须是数字并且不能小于 0 !';
$_LANG['bonus_sn_notic'] = '提示：红包序列号由六位序列号种子加上四位随机数字组成';
$_LANG['creat_bonus'] = '生成了 ';
$_LANG['creat_bonus_num'] = ' 个红包序列号';
$_LANG['bonus_sn_error'] = '红包序列号必须是数字!';
$_LANG['send_user_notice'] = '给指定的用户发放红包时,请在此输入用户名, 多个用户之间请用逗号(,)分隔开<br />如:liry, wjz, zwj';

/* 红包信息字段 */
$_LANG['bonus_id'] = '编号';
$_LANG['bonus_type_id'] = '类型金额';
$_LANG['send_bonus_count'] = '红包数量';
$_LANG['start_bonus_sn'] = '起始序列号';
$_LANG['bonus_sn'] = '红包序列号';
$_LANG['user_id'] = '使用会员';
$_LANG['used_time'] = '使用时间';
$_LANG['order_id'] = '订单号';
$_LANG['send_mail'] = '发邮件';
$_LANG['emailed'] = '邮件通知';
$_LANG['mail_status'][BONUS_NOT_MAIL] = '未发';
$_LANG['mail_status'][BONUS_MAIL_FAIL] = '已发失败';
$_LANG['mail_status'][BONUS_MAIL_SUCCEED] = '已发成功';

$_LANG['sendtouser'] = '给指定用户发放红包';
$_LANG['senduserrank'] = '按用户等级发放红包';
$_LANG['select_user_grade'] = '请选择会员等级';
$_LANG['userrank'] = '用户等级';
$_LANG['select_rank'] = '选择会员等级...';
$_LANG['keywords'] = '关键字：';
$_LANG['userlist'] = '会员列表';
$_LANG['send_to_user'] = '给下列用户发放红包';
$_LANG['search_users'] = '搜索会员';
$_LANG['confirm_send_bonus'] = '确定发送红包';
$_LANG['bonus_not_exist'] = '该红包不存在';
$_LANG['success_send_mail'] = '%d 封邮件已被加入邮件列表';
$_LANG['send_continue'] = '继续发放红包';

$_LANG['start_enddate'] = '发放起止日期';
$_LANG['use_start_enddate'] = '使用起止日期';
$_LANG['send_continue'] = '继续发放红包';
$_LANG['bind_password'] = '绑定密码';
$_LANG['copy_url'] = '复制链接';
$_LANG['bonus_qrcode'] = '获取二维码';
$_LANG['user_name_send'] = '根据会员名称发放红包';
$_LANG['user_grade_send'] = '根据会员等级发放红包';
$_LANG['no_offline_send_bonus'] = '（非线下发放红包）';
$_LANG['bonus_name'] = '红包名称';

/* 教程名称 */
$_LANG['tutorials_bonus_list_one'] = '商城红包使用说明';

/* 页面顶部操作提示 */
$_LANG['operation_prompt_content']['send'][0] = '根据会员等级发放红包，也可搜索具体会员发放红包。';
$_LANG['operation_prompt_content']['send'][1] = '请合理发放红包。';
$_LANG['operation_prompt_content']['send'][2] = '被发红包的会员可在个人主页中的账户中心查看红包信息，如果红包类型是线下发放的红包需要输入卡号和密码。';

$_LANG['operation_prompt_content']['info'][0] = '点击添加/编辑红包类型进入红包信息页面，填写信息有：类型名称、红包金额、最小订单金额等。';
$_LANG['operation_prompt_content']['info'][1] = '点击确定完成添加/编辑红包，即可生成/编辑一条红包列表。';

$_LANG['operation_prompt_content']['list'][0] = '展示所有红包类型相关信息。';
$_LANG['operation_prompt_content']['list'][1] = '可进行使用类型、活动名称搜索相关信息。';
$_LANG['operation_prompt_content']['list'][2] = '只有自行领取的红包类型，才可以复制链接。';

$_LANG['operation_prompt_content']['view'][0] = '如果红包类型是线下发放的红包，则查看的是发放红包的个数列表，展示生成所有红包的序列号和密码。';
$_LANG['operation_prompt_content']['view'][1] = '除此红包类型查看的是红包发放的情况，如发放的会员的使用情况，可进行发送邮件或删除等操作。';

return $_LANG;
