<?php

$_LANG['shipping_area_name'] = '配送区域名称';
$_LANG['shipping_area_districts'] = '地区列表';
$_LANG['shipping_area_regions'] = '所辖地区';
$_LANG['shipping_area_assign'] = '配送方式';
$_LANG['area_region'] = '地区';
$_LANG['area_shipping'] = '配送方式';
$_LANG['fee_compute_mode'] = '费用计算方式';
$_LANG['fee_by_weight'] = '按重量计算';
$_LANG['fee_by_number'] = '按商品件数计算';
$_LANG['new_area'] = '新建配送区域';
$_LANG['label_country'] = '国家：';
$_LANG['label_province'] = '省份：';
$_LANG['label_city'] = '城市：';
$_LANG['label_district'] = '区/县：';
$_LANG['label_street'] = '乡镇/街道：';
$_LANG['shipping_area_addPoint'] = '添加自提点信息';
$_LANG['shipping_area_point'] = '自提点名称';
$_LANG['shipping_area_pointInfo'] = '自提点信息';
$_LANG['shipping_area_pointName'] = '名称';
$_LANG['shipping_area_username'] = '联系人';
$_LANG['shipping_area_pointMobile'] = '手机';
$_LANG['shipping_area_addBtn'] = '继续添加';
$_LANG['shipping_area_pointAddress'] = '请输入自提点街道地址';

$_LANG['delete_selected'] = '移除选定的配送区域';
$_LANG['btn_add_region'] = '添加选定地区';
$_LANG['free_money'] = '免费额度';
$_LANG['pay_fee'] = '货到付款支付费用';
$_LANG['edit_area'] = '编辑配送区域';

$_LANG['add_continue'] = '继续添加配送区域';
$_LANG['back_list'] = '返回列表页';
$_LANG['empty_regions'] = '当前区域下没有任何关联地区';
$_LANG['region_search'] = '请先搜索地区';
$_LANG['select_region'] = '请选择地区';
$_LANG['select_jurisdiction_region'] = '请选择所辖区域！';
$_LANG['continue_name_notic'] = '配送区域名称不能为空！';

/* 提示信息 */
$_LANG['repeat_area_name'] = '已经存在一个同名的配送区域。';
$_LANG['not_find_plugin'] = '没有找到指定的配送方式的插件。';
$_LANG['remove_confirm'] = '您确定要删除选定的配送区域吗？';
$_LANG['remove_success'] = '指定的配送区域已经删除成功！';
$_LANG['no_shippings'] = '没有找到任何可用的配送方式。';
$_LANG['add_area_success'] = '添加配送区域成功。';
$_LANG['edit_area_success'] = '编辑配送区域成功。';
$_LANG['disable_shipping_success'] = '指定的配送方式已经从该配送区域中移除。';

/* 需要用到的JS语言项 */
$_LANG['js_languages']['no_area_name'] = '配送区域名称不能为空。';
$_LANG['js_languages']['del_shipping_area'] = '请先删除该配送区域，然后重新添加。';
$_LANG['js_languages']['invalid_free_mondy'] = '免费额度不能为空且必须是一个整数。';
$_LANG['js_languages']['blank_shipping_area'] = '配送区域的所辖区域不能为空。';
$_LANG['js_languages']['lang_remove'] = '移除';
$_LANG['js_languages']['lang_remove_confirm'] = '您确定要移除该地区吗？';
$_LANG['js_languages']['lang_disabled'] = '禁用';
$_LANG['js_languages']['lang_enabled'] = '启用';
$_LANG['js_languages']['lang_setup'] = '设置';
$_LANG['js_languages']['lang_region'] = '地区';
$_LANG['js_languages']['lang_shipping'] = '配送方式';
$_LANG['js_languages']['region_exists'] = '选定的地区已经存在。';

$_LANG['js_languages']['no_point_name'] = '自提点名称不能为空。';
$_LANG['js_languages']['no_point_username'] = '自提点联系人不能为空。';
$_LANG['js_languages']['no_point_mobile'] = '自提点手机不能为空。';
$_LANG['js_languages']['error_point_mobile'] = '自提点手机格式不正确。';
$_LANG['js_languages']['no_point_address'] = '自提点地址不能为空。';
$_LANG['js_languages']['line_required'] = '请选择到达路线';
$_LANG['js_languages']['file_ditu_img'] = '请上传地图图片';
$_LANG['js_languages']['point_addBtn'] = '继续添加';
$_LANG['js_languages']['point_delBtn'] = '不想添加';
$_LANG['js_languages']['please_upload_file'] = '请您上传批量csv文件';
$_LANG['js_languages']['jl_label_name'] = '名　　称：';
$_LANG['js_languages']['jl_label_contact'] = '联 系 人：';
$_LANG['js_languages']['jl_label_phone'] = '电　　话：';
$_LANG['js_languages']['jl_label_map_pic'] = '地图图片：';
$_LANG['js_languages']['jl_label_anchor_keywords'] = '锚点关键词：';
$_LANG['js_languages']['jl_label_address'] = '地　　址：';
$_LANG['js_languages']['jl_label_name'] = '到达路线：';

//大商创1.5版本新增 sunle
$_LANG['since_name'] = '名　　称';
$_LANG['since_contacts'] = '联 系 人';
$_LANG['since_tel'] = '电　　话';
$_LANG['since_map_image'] = '地图图片';
$_LANG['anchor_keyword'] = '锚点关键词';
$_LANG['since_address'] = '地　　址';
$_LANG['arrival_route'] = '到达路线';

$_LANG['set_cac_open'] = '启用上门取货';
$_LANG['set_cac_tips'] = [
    '该页面为上门取货设置页面，可设置是否安装启用上门取货',
    '启用后需设置配送区域方可使用'
];
$_LANG['set_cac_notice'] = '启用上门取货，需设置配送区域与自提时间段，配置完成前端下单时可使用上门取货提货方式';

$_LANG['operation_prompt_content']['list'][0] = '配送区域列表是展示所有配送区域的范围和所在地区。';
$_LANG['operation_prompt_content']['list'][1] = '其中上门自提是展示某范围区域的自提点。';

$_LANG['operation_prompt_content']['info'][0] = '搜索地区列表，选择搜索的地区添加到配送区域。';

return $_LANG;