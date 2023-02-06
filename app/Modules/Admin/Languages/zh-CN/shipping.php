<?php

$_LANG['shipping_transport'] = '运费管理';

$_LANG['shipping_name'] = '配送方式名称';
$_LANG['shipping_version'] = '插件版本';
$_LANG['shipping_desc'] = '配送方式描述';
$_LANG['shipping_url'] = '网址 (仅供参考)';
$_LANG['shipping_author'] = '插件作者';
$_LANG['insure'] = '保价费用';
$_LANG['support_kdn_print'] = '支持快递鸟打印';
$_LANG['support_cod'] = '货到付款';
$_LANG['shipping_area'] = '设置区域';
$_LANG['shipping_print_edit'] = '编辑打印模板';
$_LANG['shipping_print_template'] = '快递单模板';
$_LANG['shipping_template_info'] = '订单模板变量说明:<br/>{$shop_name}表示网店名称<br/>{$province}表示网店所属省份<br/>{$city}表示网店所属城市<br/>{$shop_address}表示网店地址<br/>{$service_phone}表示网店联系电话<br/>{$order.order_amount}表示订单金额<br/>{$order.region}表示收件人地区<br/>{$order.tel}表示收件人电话<br/>{$order.mobile}表示收件人手机<br/>{$order.zipcode}表示收件人邮编<br/>{$order.address}表示收件人详细地址<br/>{$order.consignee}表示收件人名称<br/>{$order.order_sn}表示订单号';

/* 表单部分 */
$_LANG['shipping_install'] = '安装配送方式';
$_LANG['install_succeess'] = '配送方式 %s 安装成功！';
$_LANG['del_lable'] = '删除标签';
$_LANG['upload_shipping_bg'] = '上传打印单图片';
$_LANG['del_shipping_bg'] = '删除打印单图片';
$_LANG['save_setting'] = '保存设置';
$_LANG['recovery_default'] = '恢复默认';
$_LANG['enabled'] = '已启用';

/* 快递单部分 */
$_LANG['lable_select_notice'] = '--选择插入标签--';
$_LANG['lable_box']['shop_country'] = '网店-国家';
$_LANG['lable_box']['shop_province'] = '网店-省份';
$_LANG['lable_box']['shop_city'] = '网店-城市';
$_LANG['lable_box']['shop_name'] = '网店-名称';
$_LANG['lable_box']['shop_district'] = '网店-区/县';
$_LANG['lable_box']['shop_tel'] = '网店-联系电话';
$_LANG['lable_box']['shop_address'] = '网店-地址';
$_LANG['lable_box']['customer_country'] = '收件人-国家';
$_LANG['lable_box']['customer_province'] = '收件人-省份';
$_LANG['lable_box']['customer_city'] = '收件人-城市';
$_LANG['lable_box']['customer_district'] = '收件人-区/县';
$_LANG['lable_box']['customer_tel'] = '收件人-电话';
$_LANG['lable_box']['customer_mobel'] = '收件人-手机';
$_LANG['lable_box']['customer_post'] = '收件人-邮编';
$_LANG['lable_box']['customer_address'] = '收件人-详细地址';
$_LANG['lable_box']['customer_name'] = '收件人-姓名';
$_LANG['lable_box']['year'] = '年-当日日期';
$_LANG['lable_box']['months'] = '月-当日日期';
$_LANG['lable_box']['day'] = '日-当日日期';
$_LANG['lable_box']['order_no'] = '订单号-订单';
$_LANG['lable_box']['order_postscript'] = '备注-订单';
$_LANG['lable_box']['order_best_time'] = '送货时间-订单';
$_LANG['lable_box']['pigeon'] = '√-对号';
//$_LANG['lable_box']['custom_content'] = '自定义内容';

/* 提示信息 */
$_LANG['no_shipping_name'] = '对不起，配送方式名称不能为空。';
$_LANG['no_shipping_desc'] = '对不起，配送方式描述内容不能为空。';
$_LANG['repeat_shipping_name'] = '对不起，已经存在一个同名的配送方式。';
$_LANG['uninstall_success'] = '配送方式 %s 已经成功卸载。';
$_LANG['add_shipping_area'] = '为该配送方式新建配送区域';
$_LANG['no_shipping_insure'] = '对不起，保价费用不能为空，不想使用请将其设置为0';
$_LANG['not_support_insure'] = '该配送方式不支持保价,保价费用设置失败';
$_LANG['invalid_insure'] = '配送保价费用不是一个合法价格';
$_LANG['no_shipping_install'] = '您的配送方式尚未安装，暂不能编辑模板';
$_LANG['edit_template_success'] = '快递模板已经成功编辑。';

/* JS 语言 */
$_LANG['js_languages']['lang_removeconfirm'] = '您确定要卸载该配送方式吗？';
$_LANG['js_languages']['shipping_area'] = '设置区域';
$_LANG['js_languages']['upload_falid'] = '错误：文件类型不正确。请上传“%s”类型的文件！';
$_LANG['js_languages']['upload_del_falid'] = '错误：删除失败！';
$_LANG['js_languages']['upload_del_confirm'] = "提示：您确认删除打印单图片吗？";
$_LANG['js_languages']['no_select_upload'] = "错误：您还没有选择打印单图片。请使用“浏览...”按钮选择！";
$_LANG['js_languages']['no_select_lable'] = "操作终止！您未选择任何标签。";
$_LANG['js_languages']['no_add_repeat_lable'] = "操作失败！不允许添加重复标签。";
$_LANG['js_languages']['no_select_lable_del'] = "删除失败！您没有选中任何标签。";
$_LANG['js_languages']['recovery_default_suer'] = "您确认恢复默认吗？恢复默认后将显示安装时的内容。";
$_LANG['js_languages']['enter_start_time'] = "请填写开始时间";
$_LANG['js_languages']['enter_end_time'] = "请填写结束时间";
$_LANG['js_languages']['zhanghu_set'] = "帐号设置";
$_LANG['js_languages']['set_success'] = "设置成功";
$_LANG['js_languages']['customer_apply'] = "客户号申请";
$_LANG['js_languages']['subimt_apply_fail'] = "提交申请失败";
$_LANG['js_languages']['subimt_apply_success'] = "提交申请成功";
$_LANG['enter_start_time'] = "请填写开始时间";
$_LANG['enter_end_time'] = "请填写结束时间";

//大商创1.5版本新增 sunle
$_LANG['shipping_time'] = '配送时间';
$_LANG['optional_start_time'] = '可选开始日期';
$_LANG['optional_start_notice'] = '可选开始日期为今天的n天之后，如今天是2013-1-1，填写（3），则可选的配送日期为2013-1-4号之后（不包含2013-1-4）';
$_LANG['shop_time_notice'] = '例：09:00-19:00';
$_LANG['time_interval'] = '时段';
$_LANG['select_template_pattern'] = '请选择模板的模式';
$_LANG['code_pattern'] = '代码模式';
$_LANG['what_you_see_pattern'] = '所见即所得模式';
$_LANG['edit_template'] = '编辑模板';
$_LANG['select_template_pattern'] = '请选择模板的模式';
$_LANG['kuaidiniao_dayin'] = '快递鸟打印';
$_LANG['kuaidiniao_set'] = '快递鸟帐号设置';
$_LANG['zhanghu_set'] = '帐号设置';
$_LANG['default_mode'] = '默认方式';
$_LANG['set_success'] = '默认方式';
$_LANG['set_no'] = '未填写内容';
$_LANG['customer_apply'] = '客户号申请';
$_LANG['subimt_apply_fail'] = '提交申请失败';
$_LANG['subimt_apply_success'] = '提交申请成功';
$_LANG['not_enabled'] = '未启用';
$_LANG['please_complete'] = '请您完善';

$_LANG['self_lift_time'] = '自提时间段';
$_LANG['edit_self_lift_time'] = '自提时间段';
$_LANG['add_self_lift_time'] = '添加自提时间段';
$_LANG['self_lift_time_list'] = '自提时间段列表';
$_LANG['back_continue_add'] = '返回继续添加';
$_LANG['back_again_add'] = '返回重新添加';
$_LANG['back_list_page'] = '返回列表页';
$_LANG['back_again_edit'] = '返回重新编辑';

/* 教程名称 */
$_LANG['tutorials_bonus_list_one'] = '配送方式使用说明';

$_LANG['operation_prompt_content']['data_list'][0] = '自提点时间段列表。';
$_LANG['operation_prompt_content']['data_list'][1] = '在前台结算页面上门自取时间选择中会使用到。';

$_LANG['operation_prompt_content']['list'][0] = '该页面展示了平台所有配送方式的信息列表。';
$_LANG['operation_prompt_content']['list'][1] = '安装配送方式后需设置区域方可使用。';
$_LANG['operation_prompt_content']['list'][2] = '支持快递鸟打印的快递，部分还需要申请帐号才能正常使用，请按照如下提示进行设置
                        <p><strong class="red">无需申请帐号：</strong>EMS、邮政快递包裹、顺丰、宅急送</p>
                        <p><strong class="red">线上（快递鸟后台）申请账号：</strong>韵达、圆通</p>
                        <p><strong class="red">线下（网点）申请账号：</strong>百世汇通、全峰、申通、天天、中通</p>';

$_LANG['operation_prompt_content']['temp'][0] = '编辑模板分为代码模式和自定义模式两种方式，请根据实际情况选择编辑打印模板。';
$_LANG['operation_prompt_content']['temp'][1] = '打印快递单模板会在订单详情里面会使用到。';
$_LANG['operation_prompt_content']['temp'][2] = '选择“代码模式”可以切换到以前版本。建议您使用“所见即所得模式”。所有模式选择后，同样在打印模板中生效。';

return $_LANG;
