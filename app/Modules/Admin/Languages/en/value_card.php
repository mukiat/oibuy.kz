<?php

$_LANG = array(
    'view_export_records' => 'Export Records',
    'vc_type_list' => 'List of stored value card types',
    'value_card_list' => 'List of stored value CARDS',
    'pc_type_list' => 'List of recharge card types',
    'pay_card_list' => 'Top-up card list',
    'vc_type_add' => 'Add the stored value card type',
    'vc_type_edit' => 'Edit the stored value card type',
    'value_card_send' => 'Stored value CARDS issued',
    'notice_remove_type_error' => 'There are bound stored value CARDS under this type, please delete them one by one!',
    'notice_remove_vc_error' => 'Cannot be deleted for bound user',
    'back_list' => 'Returns a list of stored value card types',
    'batch_drop_ok' => 'Batch deletion successful',
    'select_merchants' => 'Select a designated store',
    'please_selected_merchants' => 'Please select the designated store',
    'send' => 'issue',
    'send_amount' => 'The number of',
    'use_amount' => 'Use the number',
    'vc_name' => 'Name of stored value card',
    'vc_desc' => 'Stored value card description',
    'vc_limit' => 'Limit the number of bound CARDS',
    'vc_value' => 'Stored value card face value',
    'vc_prefix' => 'Specifies the stored value card prefix',
    'vc_dis' => 'The discount',
    'use_condition' => 'Conditions of use',
    'all_goods' => 'All the goods',
    'spec_cat' => 'Specify the classification',
    'spec_goods' => 'Specify the commodity',
    'add_goods' => 'Add the goods',
    'add_cat' => 'Add the classification',
    'begin_time' => 'The start time',
    'end_time' => 'The end of time',
    'indate' => 'The period of validity',
    'is_rec' => 'Would you please top-up',
    'vc_desc_info' => 'Stored value card description information',
    'notice_value' => 'Unit:',
    'notice_limit' => 'Unit: zhang',
    'notice_prefix' => 'Support English or Numbers',
    'notice_dis' => 'Please enter the discount rate (%) and fill in the integer between 1-99',
    'months' => 'months',
    'notice_indate' => 'A natural month',
    'use_merchants' => 'Use the store',
    'all_merchants' => 'All the shops',
    'self_merchants' => 'Proprietary stores',
    'assign_merchants' => 'Specify the store',
    'choice_merchant' => 'Select the store',
    'percent' => 'Discount',
    'send_num' => 'The number of',
    'card_type' => 'Generate the card number type',
    'password_type' => 'Generate password type',
    'notice_send_num' => 'zhang',
    'eight' => '8-digit pure digital card number',
    'twelve' => '12 digit pure digital card number',
    'sixteen' => '16-digit pure digital card number',
    'eight_psd' => '8 digit alphanumeric combination password',
    'twelve_psd' => '12 digit alphanumeric combination password',
    'sixteen_psd' => '16-digit alphanumeric combination password',
    'creat_value_card' => 'To generate the',
    'value_card_num' => 'Three stored value card serial Numbers',
    'value_card_sn' => 'The serial number',
    'value_card_password' => 'password',
    'value_card_type' => 'Stored value card type',
    'value_card_money' => 'balance',
    'value_card_value' => 'Face value',
    'bind_user' => 'Bind the user',
    'bind_time' => 'The binding time',
    'no_use' => 'N/A',
    'export_vc_list' => 'Export the stored value card',
    'js_languages' =>
        array(
            'send_num_null' => 'Please enter the number of CARDS issued',
            'send_num_number' => 'Please enter the correct number',
            'card_name_null' => 'Please enter user name',
            'card_limit_null' => 'Please enter the limited number of stored value CARDS',
            'card_vaule_number' => 'Please enter the correct number',
            'card_vaule_null' => 'Please enter the number of coupons',
            'card_prefix_null' => 'Please enter the stored value card prefix',
            'card_prefix_duplicate' => 'Duplicate stored value card prefix',
            'card_en_number' => 'English letters or Numbers are supported only',
            'card_goodscat_exits' => 'Categories already exist',
        ),
    'tutorials_bonus_list_one' => 'Store store value card function description',
    'operation_prompt_content' =>
        array(
            'send' =>
                array(
                    0 => 'The number of stored value CARDS issued is the number of times they are used.',
                    1 => 'Note the number of generated digits for card number types and password types, the larger the number, the more difficult it is to remember the password.',
                ),
            'view' =>
                array(
                    0 => 'A list of stored value CARDS under one type.',
                    1 => 'Displays the serial number and password of each stored value card.',
                    2 => 'In the member center, my stored value card can be bound.',
                ),
            'info' =>
                array(
                    0 => 'After the type of stored value card is added, the stored value card shall be issued, and the number of issued CARDS shall be filled in, then the stored value card of this type can be used for many times.',
                    1 => 'The discount rate of the prepaid card will affect the final payment amount of the order. Please set it carefully'
                ),
            'list' =>
                array(
                    0 => 'If the function of stored value card needs to be opened for members, the stored value card should be turned on in the platform-store setting-shopping process, and the default is not turned on.',
                    1 => 'Multiple stored value CARDS can be issued for one type of stored value card, the specific number of which shall be determined according to the target users.',
                ),
        ),
);

$_LANG['card_use_status'] = 'status';
$_LANG['use_status']['invalid'] = 'Set to invalid';
$_LANG['use_status']['invalid_desc'] = 'Are you sure to set the stored value card as invalid？';
$_LANG['use_status']['use'] = 'Set to normal';
$_LANG['use_status']['use_desc'] = 'Are you sure to set the stored value card to normal？';

$_LANG['card_use'][0] = 'invalid';
$_LANG['card_use'][1] = 'normal';
$_LANG['card_use'][2] = 'be overdue';
$_LANG['card_use'][3] = 'delete';
$_LANG['batch_handle_fail'] = 'Batch operation failed';
$_LANG['batch_handle_succeed'] = 'Batch operation successful';

$_LANG['vc_log_list'] = 'List of stored value card usage records';
$_LANG['use_log'] = 'Usage record';
$_LANG['order_sn'] = 'order number';
$_LANG['use_amount'] = 'Amount used';
$_LANG['add_amount'] = 'Recharge amount';
$_LANG['handler_time'] = 'Operation time';

return $_LANG;
