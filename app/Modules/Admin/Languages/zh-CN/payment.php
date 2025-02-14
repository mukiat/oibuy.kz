<?php

$_LANG['payment'] = '支付方式';
$_LANG['payment_name'] = '支付方式名称';
$_LANG['version'] = '插件版本';
$_LANG['payment_desc'] = '支付方式描述';
$_LANG['short_pay_fee'] = '费用';
$_LANG['payment_author'] = '插件作者';
$_LANG['payment_is_cod'] = '货到付款';
$_LANG['payment_is_online'] = '在线支付';

$_LANG['name_is_null'] = '您没有输入支付方式名称！';
$_LANG['name_exists'] = '该支付方式名称已存在！';

$_LANG['pay_fee'] = '支付手续费';
$_LANG['back_list'] = '返回支付方式列表';
$_LANG['install_ok'] = '安装成功';
$_LANG['edit_ok'] = '编辑成功';
$_LANG['uninstall_ok'] = '卸载成功';

$_LANG['invalid_pay_fee'] = '支付费用不是一个合法的价格';
$_LANG['decide_by_ship'] = '配送决定';

$_LANG['edit_after_install'] = '该支付方式尚未安装，请你安装后再编辑';
$_LANG['payment_not_available'] = '该支付插件不存在或尚未安装';

$_LANG['js_languages']['lang_removeconfirm'] = '您确定要卸载该支付方式吗？';
$_LANG['js_languages']['pay_name_null'] = '支付方式名称不能为空';

$_LANG['ctenpay'] = '立即注册财付通商户号';
$_LANG['ctenpay_url'] = 'http://union.tenpay.com/mch/mch_register_b2c.shtml?sp_suggestuser=542554970';
$_LANG['ctenpayc2c_url'] = 'https://www.tenpay.com/mchhelper/mch_register_c2c.shtml?sp_suggestuser=542554970';
$_LANG['tenpay'] = '即时到账';
$_LANG['tenpayc2c'] = '中介担保';

/* 教程名称 */
$_LANG['tutorials_bonus_list_one'] = '商城支付方式设置';

/* 页面顶部操作提示 */
$_LANG['operation_prompt_content']['list'][0] = '该页面展示了所有平台支付方式的相关信息列表。';
$_LANG['operation_prompt_content']['list'][1] = '可进行卸载或安装相应的支付方式。';
$_LANG['operation_prompt_content']['list'][2] = '安装相应支付方式后，用户购物时便可使用相应的支付方式，请谨慎卸载。';

$_LANG['operation_prompt_content']['info'][0] = '请谨慎安装支付方式，填写相关信息。';

$_LANG['pay_fee_desc'] = '支持百分比，例如 10%, 不填写则指定金额';
$_LANG['wxpay_sslcert_is_empty'] = '微信支付证书cert、key不能为空';
$_LANG['wxpay_divide_key_empty'] = '服务商商户号、APIv3密钥、微信支付平台序列号不能为空';

return $_LANG;
