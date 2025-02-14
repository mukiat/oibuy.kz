<?php

$_LANG['vc_type_list'] = '储值卡类型列表';
$_LANG['value_card_list'] = '储值卡列表';
$_LANG['pc_type_list'] = '充值卡类型列表';
$_LANG['pay_card_list'] = '充值卡列表';
$_LANG['pc_type_add'] = '添加充值卡类型';
$_LANG['pc_type_edit'] = '编辑充值卡类型';
$_LANG['pay_card_send'] = '充值卡发放';
$_LANG['notice_remove_type_error'] = '此类型下存在已使用的充值卡，请逐个删除！';

/* 充值卡类型字段信息 */
$_LANG['bonus_type'] = '充值卡类型';
$_LANG['bonus_list'] = '充值卡列表';
$_LANG['type_name'] = '类型名称';
$_LANG['type_money'] = '充值卡金额';
$_LANG['type_prefix'] = '生成充值卡前缀';
$_LANG['send_count'] = '发放数量';
$_LANG['use_count'] = '使用数量';
$_LANG['handler'] = '操作';
$_LANG['send'] = '发放';
$_LANG['type_money_notic'] = '此类型充值卡可以充值的金额，例如：100';
$_LANG['use_enddate'] = '有效期';
$_LANG['use_enddate'] = '有效期';
$_LANG['back_list'] = '返回充值卡管理列表';
$_LANG['continus_add'] = '继续添加充值卡管理';
$_LANG['send_bonus'] = '生成充值卡';
$_LANG['creat_bonus'] = '生成了 ';
$_LANG['creat_bonus_num'] = ' 个充值卡';
$_LANG['back_bonus_list'] = '返回充值卡列表';
$_LANG['bonus_sn'] = '充值卡卡号';
$_LANG['bonus_psd'] = '充值卡密码';
$_LANG['batch_drop_success'] = '成功删除了 %d 个充值卡';
$_LANG['back_bonus_list'] = '返回充值卡列表';
$_LANG['no_select_bonus'] = '您没有选择需要删除的充值卡';
$_LANG['attradd_succed'] = '操作成功!';
$_LANG['used_time'] = '使用时间';
$_LANG['user_id'] = '使用会员';
$_LANG['bonus_excel_file'] = '充值卡信息列表';
$_LANG['type_name_exist'] = '充值卡类型名称已存在';
$_LANG['notice_prefix'] = '支持英文或者数字';

/* 充值卡发放 */
$_LANG['send_num'] = '发放数量';
$_LANG['card_type'] = '生成卡号类型';
$_LANG['password_type'] = '生成密码类型';
$_LANG['notice_send_num'] = '张';
$_LANG['eight'] = '8位纯数字卡号';
$_LANG['twelve'] = '12位纯数字卡号';
$_LANG['sixteen'] = '16位纯数字卡号';
$_LANG['eight_psd'] = '8位数字字母混合密码';
$_LANG['twelve_psd'] = '12位数字字母混合密码';
$_LANG['sixteen_psd'] = '16位数字字母混合密码';
$_LANG['creat_value_card'] = '生成了';
$_LANG['pay_card_num'] = '个充值卡序列号';

//导出
$_LANG['export_pc_list'] = "导出充值卡";
$_LANG['no_use'] = 'N/A';

/* JS提示信息 */
$_LANG['js_languages']['send_num_null'] = '请输入发放张数';
$_LANG['js_languages']['send_num_number'] = '请输入正确的数字';

$_LANG['js_languages']['type_name_null'] = '请输入充值卡类型名称';
$_LANG['js_languages']['type_money_null'] = '请输入充值卡面值';
$_LANG['js_languages']['type_money_number'] = '请输入正确的数字';
$_LANG['js_languages']['type_prefix_null'] = '请输入生成充值卡前缀';
$_LANG['js_languages']['type_prefix_gs'] = '输入格式不正确';
$_LANG['js_languages']['use_end_date_null'] = '请选择有效期';

/* 页面顶部操作提示 */
$_LANG['operation_prompt_content']['pc_type_list'][0] = '充值卡可给储值卡充值。';
$_LANG['operation_prompt_content']['pc_type_list'][1] = '储值卡充值后可循环使用。';
$_LANG['operation_prompt_content']['pc_type_list'][2] = '充值卡也可以循环使用。';

$_LANG['operation_prompt_content']['pc_type_info'][0] = '充值卡类型类似于储值卡。';

$_LANG['operation_prompt_content']['info'][0] = '生成充值卡的数量。';
$_LANG['operation_prompt_content']['info'][1] = '具体生成数量根据需求设定。';
$_LANG['operation_prompt_content']['info'][2] = '注意卡号类型和密码类型的生成位数，越多数量的密码记忆更加困难。';

$_LANG['operation_prompt_content']['view'][0] = '一种类型下的充值卡列表。';
$_LANG['operation_prompt_content']['view'][1] = '显示每个充值卡的卡号和密码。';

return $_LANG;
