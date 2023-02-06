<?php

return [

	// 标题
	'goods_services_label' => '服务标签',
	'goods_label_list' => '服务标签列表',
	'goods' => '商品',

	// 列表
	'label_name' => '标签名称',
	'bind_goods_number' => '打标商品数',
	'label_image' => '标签样式',
	'merchant_use' => '商家可用',
	'status' => '标签状态',
	'sort' => '排序',
	'label_url' => '标签链接',
	'add_label' => '添加服务标签',
	'use' => '启用',
	'no_use' => '禁用',
	'batch_use' => '批量启用',
	'batch_no_use' => '批量禁用',
	'batch_drop' => '批量删除',
	'see_url' => '查看链接',
	'label_type' => '标签类型',

	// 添加 编辑
	'add' => '添加',
	'batch_add' => '批量添加',
	'save_success' => '保存成功',
	'upload_file' => '上传文件',
    'label_explain' => '标签说明',

	// 提示
	'label_name_notice' => '显⽰在前端服务模块，1-15字，请谨慎填写',
    'label_explain_notice' => '显⽰在移动端标签名称下的补充说明，60字，留空则不显⽰',
	'sort_notice' => '同⼀个商品存在多个标签时，排序数字⼩的优先显⽰',
	'merchant_use_notice' => '设置商家是否可使⽤该标签，为否时该标签仅⾃营商品可选，⾮⾃营商家不可见',
	'label_image_notice' => '标签icon图⽚，建议尺⼨⽐例1：1，⽀持png、jpg格式',
	'status_notice' => '禁⽤状态标签不在前端显⽰',
	'success_notice' => '添加服务标签成功',
	'failed_notice' => '添加服务标签失败',
	'edit_success_notice' => '编辑服务标签成功',
	'edit_failed_notice' => '编辑服务标签失败',
	'success_update' => '更新成功',
	'failed_update' => '更新失败',
	'success_drop_notice' => '删除成功',
	'failed_drop_notice' => '删除失败',
	'batch_success_notice' => '批量操作成功',

	'label_name_not_null' => '标签名称不能为空',
	'label_image_not_null' => '标签图片不能为空',
	'sort_not_null' => '标签排序不能为空',
	'label_name_exists' => '标签名称已存在',
    'services_label_list_notice' => [
        '⻚⾯内可设置商品服务标签，并配置打标商品。',
        '标签内容将在前端显示，请谨慎配置。',
    ],
	'services_label_notice' => [
	    '标识“*”的选项为必填项，其余为选填项。',
        '请按提示⽂案填写信息，以免出错。',
    ],

	// js提示
	'drop_js_notice' => '当前标签已有绑定商品，删除标时会同步删除绑定商品中的标签，确定删除？',
	'batch_drop_notice' => '删除标签将删除所有已经添加的商品活动标签，确定删除？',

    'no_records' => '没有找到任何记录',
    'goods_add_label' => '商品打标',

    'services_add_label' => [
        '选择商品进⾏打标，打标后前端商品详情服务模块显示该标签。',
        '请根据实际情况选择打标商品。'
    ],

    'label_show_time' => '显示时间',
    'label_show_time_notice' => '商品标签显示时间段',
    'start_time_required' => '请选择开始时间',
    'end_time_required' => '请选择结束时间',
    'label_image_notice_1' => '推荐尺寸：保持与商品图片尺寸一致， 上传格式 png, jpg',
    'start_time' => '显示开始时间',
    'end_time' => '显示结束时间',
    'bind_goods' => '商品打标',
    'bind_goods_tips' => [
        '选择商品进行打标，打标后前端商品主图显示该标签。',
        '请根据实际情况选择打标商品。'
    ],
    'shop_keywords' => '搜索商家名称',
    'goods_keywords' => '搜索商品名称',
    'label_id_required' => '标签id不能为空',

    'label_notice_seller' => [
        '⻚⾯展示商城所有商品悬浮标签，可对商品进⾏打标操作；',
        '打标后前端商品主图显示该标签，每个商品最多显示⼀个悬浮标签，商品存在多个悬浮标签时，优先显示排序靠前的悬浮标签；'
    ],
];
