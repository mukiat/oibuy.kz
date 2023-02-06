<?php

return [

	// 标题
	'goods_label' => 'Active label',
	'goods_label_list' => 'Active labels list',
	'goods' => 'goods',

	// 列表
	'label_name' => 'label name',
	'bind_goods_number' => 'Number of goods marked',
	'label_image' => 'label image',
	'merchant_use' => 'Businesses are available',
	'status' => 'label status',
	'sort' => 'sort',
	'label_url' => 'label link',
	'add_label' => 'add label ',
	'use' => 'enabled',
	'no_use' => 'disabled',
	'batch_use' => 'Batch enabled',
	'batch_no_use' => 'Batch disabled',
	'batch_drop' => 'Batch drop',
	'see_url' => 'To view links',
	'label_type' => 'label type',
	'label_type_0' => 'general label ',
	'label_type_1' => 'suspension label ',

	// 添加 编辑
	'add' => 'add ',
	'batch_add' => 'Batch add',
	'save_success' => 'save success',
	'upload_file' => 'upload file',

	// 提示
	'label_name_notice' => 'Used for background administrator differentiation only for label, Not in front display',
	'sort_notice' => 'There are more than one item label, The smaller the number, the higher it is',
	'merchant_use_notice' => 'Sets whether the merchant can use this label, When no the label Only self-run goods are optional, non-self-run merchants are not visible',
	'label_image_notice' => 'Display in front of the front product name, the height is 40px',
	'status_notice' => 'Disabled state label Not in front display',
	'label_url_notice' => 'add label link, click the label Jump to the link page',
	'success_notice' => 'add label success',
	'failed_notice' => 'add label fail',
	'edit_success_notice' => 'edit label success',
	'edit_failed_notice' => 'edit label fail',
	'success_update' => 'update success',
	'failed_update' => 'update fail',
	'success_drop_notice' => 'drop success',
	'failed_drop_notice' => 'drop fail ',
	'batch_success_notice' => 'batch success',

	'label_name_not_null' => ' The label name cannot be empty',
	'label_image_not_null' => 'The label image cannot be empty',
	'sort_not_null' => 'The sort cannot be empty',
	'label_name_exists' => 'The label name exist',
	'label_notice_0' => [
        'The page shows the common label of all commodities in the mall, and the label can be edited and modified;',
        'Universal Label enabled will be displayed on the front page, please add label according to the actual requirements;',
        'If the label is not enabled, it will not be displayed in the front page. Deleting the label will synchronously delete the bound label in the product;',
        'Open label available to merchants will be displayed in the background of merchants. Once entered merchants have the option, label binding goods available to merchants will be opened;'
    ],
    'label_notice_0_seller' => [
        'The page displays the generic product label available to the merchant, and the active label can be bound to the product on the edit product page'
    ],

	// js提示
	'drop_js_notice' => 'The current label already has a bound commodity. When deleting the label, the label in the bound commodity will be deleted synchronously. Is it ok to delete?',
	'batch_drop_notice' => 'Delete Label Will delete all the added merchandise activity labels. Sure to delete?',

    'no_records' => 'no records',

    // 悬浮label 提示
    'label_notice_1' => [
        'The page shows all goods in the mall, and labels can be edited and modified.',
        'If the floating label is enabled, it will be displayed on the front page. Please add the label according to the actual requirements. After the creation of the label, the product can be marked. Not enabled Label is not displayed on the front page. Deleting a label will synchronously delete the label display of the bound item',
        'Suspended label can be set to support the business. The suspended label that supports the use of the business can be viewed and selected in the background of the business for marking.',
        'Suspended label is the label style suspended above the commodity thumbnail and the main image. It supports setting the start and end time of label display',
        'The suspended label will only be displayed within the validity period and will not be displayed after expiration. You can choose the goods to be marked or check the list of marked goods.',
        'A maximum of 1 suspended label is displayed for each commodity. When a single commodity is repeatedly marked, only the suspended label in front of the ranking number of labels is displayed in the period of validity.',
    ],
    'label_notice_1_seller' => [
        'The page shows the goods available to merchants, and the labels can be selected for marking'
    ],
    'goods_add_label' => 'bind goods',
    'label_show_time' => 'display time',
    'label_show_time_notice' => 'goods label display time period',
    'start_time_required' => 'please select start time',
    'end_time_required' => 'please select end time',
    'label_image_notice_1' => 'Recommended size: keep the same size with the product picture, upload in PNG, JPG format',
    'start_time' => 'Show start time',
    'end_time' => 'Show end time',
    'bind_goods' => 'bind goods',
    'bind_goods_tips' => [
        'Select the product for marking, and the main diagram of the front-end product will display the label after marking.',
        'Please select the marked goods according to the actual situation'
    ],
    'shop_keywords' => 'search the shop name',
    'goods_keywords' => 'search the goods name',
    'label_id_required' => 'label id required',
];
