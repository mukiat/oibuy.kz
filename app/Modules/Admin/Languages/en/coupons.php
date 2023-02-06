<?php

$_LANG = array(
    'coupons_type' => 'Coupon type',
    'coupons_list' => 'Coupon list',
    'type_name' => 'Type the name',
    'type_money' => 'Coupon amount',
    'min_goods_amount' => 'Minimum order amount',
    'notice_min_goods_amount' => 'The coupon can only be used for orders that amount',
    'min_amount' => 'Order minimum',
    'max_amount' => 'Upper limit of the order',
    'send_startdate' => 'The start time',
    'send_enddate' => 'The end of time',
    'use_startdate' => 'Use start date',
    'use_enddate' => 'End of use date',
    'send_count' => 'The number of',
    'use_count' => 'Use the number',
    'send_method' => 'How to issue this type of coupon',
    'send_type' => 'Distributing type',
    'param' => 'parameter',
    'no_use' => 'Don\'t use',
    'no_overdue' => 'No overdue',
    'yuan' => 'yuan',
    'user_list' => 'The member list',
    'type_name_empty' => 'Coupon type name cannot be empty!',
    'type_money_empty' => 'Coupon amount cannot be empty!',
    'min_amount_empty' => 'Coupon type order floor cannot be empty!',
    'max_amount_empty' => 'Coupon type order caps cannot be empty!',
    'send_count_empty' => 'The number of coupon types issued cannot be empty!',
    'send_by' =>
        array(
            0 => 'Distributed by user',
            1 => 'Distribution by commodity',
            2 => 'According to the amount of the order',
            3 => 'Offline coupons',
        ),
    'report_form' => 'Report to download',
    'send' => 'issue',
    'coupons_excel_file' => 'Offline coupon information list',
    'goods_cat' => 'Select commodity classification',
    'goods_brand' => 'Commodity brand',
    'goods_key' => 'Commodity keyword',
    'all_goods' => 'Optional items',
    'send_bouns_goods' => 'Issue this type of coupon for goods',
    'remove_bouns' => 'Remove coupons',
    'all_remove_bouns' => 'Remove all',
    'goods_already_bouns' => 'Other types of coupons have been issued for this item!',
    'send_user_empty' => 'You have not selected any members who need to issue coupons, please return!',
    'batch_drop_success' => '%d user coupons were successfully deleted',
    'sendcoupons_count' => 'A total of %d coupons were sent.',
    'send_bouns_error' => 'Error sending member coupon, please try again!',
    'no_select_coupons' => 'You did not select the user coupons to delete',
    'couponstype_edit' => 'Edit coupon types',
    'couponstype_view' => 'Check the details',
    'drop_coupons' => 'Delete coupons',
    'send_coupons' => 'Issue coupons',
    'continus_add' => 'Add coupons',
    'back_list' => 'Returns a list of coupon types',
    'continue_add' => 'Continue to add coupons',
    'back_coupons_list' => 'Return coupon list',
    'validated_email' => 'Coupons will only be issued to users who are authenticated by mail',
    'coupons_adopt_status_set_success' => 'Coupon review status set successfully',
    'coupons_name' => 'Name of coupon',
    'goods_steps_name' => 'Vendor name',
    'use_limit' => 'Using threshold',
    'coupons_value' => 'Face value',
    'give_out_amount' => 'Total circulation',
    'valid_date' => 'Effective range',
    'is_overdue' => 'Status',
    'attradd_succed' => 'Operation successful!',
    'js_languages' =>
        array(
            'type_name_empty' => 'Please enter coupon type name!',
            'type_money_empty' => 'Please enter coupon type price!',
            'order_money_empty' => 'Please enter the order amount!',
            'type_money_isnumber' => 'Type amount must be in digital format!',
            'order_money_isnumber' => 'Order amount must be in digital format!',
            'coupons_sn_empty' => 'Please enter the coupon serial number!',
            'coupons_sn_number' => 'Coupon serial number must be a number!',
            'coupons_sum_empty' => 'Please enter the number of coupons you want to issue!',
            'coupons_sum_number' => 'The number of coupons issued must be an integer!',
            'coupons_type_empty' => 'Please select the coupon type amount!',
            'user_rank_empty' => 'You have not specified the membership level!',
            'user_name_empty' => 'You need to choose at least one member!',
            'invalid_min_amount' => 'Please enter order floor (number greater than 0)',
            'send_start_lt_end' => 'The start date of coupon issuance cannot be longer than the end date',
            'use_start_lt_end' => 'The beginning date of coupon use cannot be longer than the end date',
            'range_exists' => 'This option already exists',
            'allow_user_rank' => 'Membership levels that allow participation. An unselected membership means that no member can participate',
            'coupons_title_empty' => 'Please enter coupon title',
            'coupons_zhang_empty' => 'Please enter the number of coupons',
            'coupons_face_empty' => 'Please enter coupon face value',
            'coupons_total_isnumber' => 'The amount must be in digital format',
            'coupons_total_min' => 'The amount must be greater than 0',
            'coupons_threshold_empty' => 'Please enter the threshold for coupon usage',
            'coupons_data_empty' => 'The effective start time cannot be empty',
            'coupons_data_invalid_gt' => 'The effective end time must be greater than the effective start time',
            'coupons_cat_exist' => 'Categories already exist',
            'coupons_set_goods' => 'Please set the goods',
            'continus_add' => 'Add coupons',
        ),
    'send_count_error' => 'The number of coupons issued must be an integer!',
    'order_money_notic' => 'As long as the order amount reaches this value, a coupon will be issued to users',
    'type_money_notic' => 'This type of coupon can offset the amount',
    'send_startdate_notic' => 'This type of coupon can only be issued if the current time is between the start and end date',
    'use_startdate_notic' => 'This type of coupon can only be used if the current time is between the start and end date',
    'type_name_exist' => 'The name of this type already exists!',
    'type_money_beyond' => 'Coupon amount shall not be greater than the minimum order amount!',
    'type_money_error' => 'The amount must be a number and cannot be less than 0!',
    'coupons_sn_notic' => 'Tip: the coupon serial number consists of six serial number seeds plus four random digits',
    'creat_coupons' => 'To generate the',
    'creat_coupons_num' => 'Coupon serial Numbers',
    'coupons_sn_error' => 'Coupon serial Numbers must be Numbers!',
    'send_user_notice' => 'When issuing coupons to specified users, please enter user name here, and separate multiple users with comma (,) <br />, such as :liry, WJZ, ZWJ',
    'allow_level_no_select_no_join' => 'Membership levels that allow participation. An unselected membership means that no member can participate',
    'coupons_id' => 'Serial number',
    'coupons_type_id' => 'Type the amount of',
    'send_coupons_count' => 'Coupon quantity',
    'start_coupons_sn' => 'Initial sequence number',
    'coupons_sn' => 'Coupon number',
    'user_name' => 'Its member',
    'user_id' => 'Using the membership',
    'used_time' => 'Use your time',
    'order_id' => 'The order number',
    'send_mail' => 'email',
    'emailed' => 'Email notification',
    'mail_status' =>
        array(
            0 => 'Did not send',
            2 => 'Has failed in the test',
            1 => 'Already sent successfully',
        ),
    'sendtouser' => 'Issue coupons to selected users',
    'senduserrank' => 'Offer coupons at user level',
    'userrank' => 'User level',
    'select_rank' => 'Select membership level...',
    'keywords' => 'Key words:',
    'userlist' => 'Membership list:',
    'send_to_user' => 'Issue coupons to the following users',
    'search_users' => 'Search for members',
    'confirm_send_coupons' => 'Make sure to send coupons',
    'coupons_not_exist' => 'The coupon does not exist',
    'success_send_mail' => '%d messages have been added to the mailing list',
    'send_continue' => 'Continue to issue coupons',
    'please_input_coupons_name' => 'Please enter the coupon name',
    'allow_user_rank' => 'Membership levels that allow participation. An unselected membership means that no member can participate',
    'use_threshold_money' => 'Threshold (yuan)',
    'face_value_money' => 'Face value ($)',
    'total_send_num' => 'Total issue (sheets)',
    'expiry_date' => 'The period of validity',
    'expired' => 'expired',
    'start_enddate' => 'Issue start and end dates',
    'use_start_enddate' => 'Valid time',
    'bind_password' => 'Bind password',
    'receive_start_enddate' => 'Collection time',
    'coupons_default_title' => 'Select create a coupon',
    'coupons_default_tit' => 'Increase user engagement by issuing coupons',
    'coupons_add_title' => 'Please select the coupon you want to add',
    'coupons_type_01' => 'Registered coupon',
    'coupons_type_02' => 'Shopping coupon',
    'coupons_type_03' => 'All coupons',
    'coupons_type_04' => 'Members\'',
    'coupons_type_05' => 'Free mail stamps',
    'coupons_type_06' => 'Get store coupons',
    'coupons_type_07' => 'Groupbuy coupons',
    'not_free_shipping' => 'No delivery area',
    'set_free_shipping' => 'Do not set up free postal area',
    'cou_man_desc' => 'If there is no threshold, please set it to 0',
    'coupons_desc_title_01' => 'Newly registered users can receive a specified coupon',
    'coupons_desc_span_01' => 'Increase users\' registration enthusiasm by registering coupons',
    'coupons_desc_title_02' => 'Shopping full amount can get the specified coupon',
    'coupons_desc_span_02' => 'Shopping coupons encourage users to increase the amount of orders',
    'coupons_desc_title_03' => 'Coupons can be collected from the product details page',
    'coupons_desc_span_03' => 'To attract more members to place an order in the store through the free coupon',
    'coupons_desc_title_04' => 'Designated members receive coupons',
    'coupons_desc_span_04' => 'Provide personalized promotion for different levels of members through designated member coupons',
    'activity_rule' => 'Activity rules',
    'worth' => 'Value (yuan)',
    'receive_limit' => 'Get the limit',
    'activity_state' => 'Active state',
    'already_receive' => 'Have to receive',
    'already_used' => 'Has been used',
    'handle' => 'operation',
    'coupons_number' => 'Total number of coupons',
    'coupons_man' => 'Using threshold',
    'coupons_money' => 'Face value',
    'coupons_intro' => 'note',
    'cou_get_man' => 'To obtain the threshold',
    'cou_list' => 'Coupon list',
    'cou_edit' => 'Coupon editor',
    'full_shopping' => 'Shopping with',
    'deductible' => 'deductible',
    'each_purchase' => 'Each limit get',
    'use_goods' => 'Usable commodity',
    'buy_goods_deduction' => 'Discount coupons can be used to deduct the amount of the following goods',
    'goods_all' => 'All the goods',
    'goods_appoint' => 'Specify the commodity',
    'spec_cat' => 'Specify the classification',
    'label_search_and_add' => 'Search and add deals',
    'full_delivery' => 'Send full amount',
    'desc_yuan' => 'Yuan can get coupons',
    'give_quan_goods' => 'Coupon merchandise',
    'buy_has_coupon' => 'Coupons are available for purchases of the following items for a certain amount',
    'come_user' => 'To attend the member',
    'all_checkbox' => 'Future generations',
    'label_coupons_title' => 'Coupon title:',
    'coupons_title_example' => 'For example: can only purchase the mobile phone category specified goods or the whole category general',
    'coupons_title_tip' => 'It should correspond to the "usable goods" setting and be worthy of the name',
    'coupons_total_send_num' => 'Total number of coupons issued',
    'name_notice' => 'Name of coupon',
    'coupons_title' => 'Coupon title',
    'title_notice' => 'Need to be \'available goods\' set up corresponding to the goods, to achieve the name',
    'total_notice' => 'Total number of coupons issued: one',
    'money_notice' => 'Unit:',
    'one' => '1 piece',
    'add_goods' => 'Add the goods',
    'add_cat' => 'Add the classification',
    'title_exist' => 'Coupon %s already exists',
    'coupon_store_exist' => 'Store coupon %s already exists',
    'copy_url' => 'Copy the link',
    'tutorials_bonus_list_one' => 'Store coupon function description',
    'operation_prompt_content' =>
        array(
            'view' =>
                array(
                    0 => 'Check coupon issuance or collection records.',
                    1 => 'Deletion is possible.',
                ),
            'list' =>
                array(
                    0 => 'A list of information about coupons is shown.',
                    1 => 'Specific coupon information is searched through coupon name keywords and filter usage types.',
                ),
            'info' =>
                array(
                    0 => 'Registration coupon: the new user will be released to the user account upon successful registration;',
                    1 => 'Shopping coupon: shopping will be released to the user account after a certain amount is reached (after background delivery);',
                    2 => 'Full-court coupon: users can click on the coupon home page or list page or the product details page or the sidebar to get it;',
                    3 => 'Coupon for members: the same coupon for all members (membership level can be limited).',
                ),
        ),
);

$_LANG['js_languages']['coupons_receive_empty'] = 'Collection start time cannot be empty';
$_LANG['js_languages']['coupons_receive_invalid_gt'] = 'The collection end time must be greater than the effective start time';

$_LANG['not_effective'] = 'Not effective';
$_LANG['effective'] = 'Come into force';
$_LANG['nullify'] = 'Voided';

$_LANG['valid_day_num'] = 'Days of validity';
$_LANG['valid_day_num_desc'] = 'The effective time calculated after the coupon is successfully collected by the user. For example, the collection time is 2021-12-08 09:00:00. If it is valid for 5 days, it will be available before 2021-12-13 09:00:00';
$_LANG['js_languages']['coupons_valid_day_num_empty'] = 'Valid days of collection cannot be blank';
$_LANG['js_languages']['valid_day_num_min'] = 'The minimum number of days is 1 day';

$_LANG['valid_end_time'] = 'Available deadline';
$_LANG['valid_type'] = 'Validity period type';
$_LANG['valid_type_desc'][1] = 'By valid interval';
$_LANG['valid_type_desc'][2] = 'By valid days';

return $_LANG;