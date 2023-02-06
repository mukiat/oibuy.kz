<?php

$_LANG = array(
    'presale_list' => 'Pre-sale activity list',
    'add_presale' => 'Add pre-sale activity',
    'edit_presale' => 'Editing pre-sale',
    'add_presale_cat' => 'Add pre-sale categories',
    'presale_cat_list' => 'Pre-sale category list',
    'goods_name' => 'Name of commodity',
    'start_date' => 'The start time',
    'end_date' => 'The end of time',
    'deposit' => 'The deposit',
    'restrict_amount' => 'For purchasing',
    'gift_integral' => 'Present integral',
    'valid_order' => 'The order',
    'valid_goods' => 'To order goods',
    'current_price' => 'The current price',
    'current_status' => 'state',
    'view_order' => 'To view the order',
    'select_advance_goods' => 'Please select search for pre-sale items',
    'goods_cat' => 'Classification of goods',
    'all_cat' => 'All categories',
    'goods_brand' => 'Commodity brand',
    'all_brand' => 'All brands',
    'label_goods_name' => 'Pre-sale goods:',
    'label_act_name' => 'Activity name:',
    'label_presale_cat' => 'Category of activities:',
    'select_option' => 'Please select...',
    'notice_goods_name' => 'Please search for items first and generate a list of options here...',
    'label_start_date' => 'Start time:',
    'label_end_date' => 'End time:',
    'label_start_end_date' => 'Time to pay the deposit:',
    'notice_datetime' => '(year, month, day - hour)',
    'label_deposit' => 'Deposit:',
    'label_restrict_amount' => 'Purchase quota:',
    'notice_restrict_amount' => 'Reach this quantity, presale activity closes automatically. Zero means there is no quantity limit.',
    'label_gift_integral' => 'Bonus points:',
    'label_price_ladder' => 'Price ladder:',
    'notice_ladder_amount' => 'The number of',
    'notice_ladder_price' => 'Enjoy the price',
    'label_desc' => 'Activity description:',
    'label_status' => 'Current status of activity:',
    'gbs' =>
        array(
            0 => 'Not at the',
            1 => 'ongoing',
            2 => 'End unprocessed',
            3 => 'Successful end',
            4 => 'Failure to end',
        ),
    'label_order_qty' => 'Order number/valid order number:',
    'label_goods_qty' => 'Number of goods/valid goods:',
    'label_cur_price' => 'Current price:',
    'label_end_price' => 'The final price:',
    'label_handler' => 'Operation:',
    'error_presale' => 'The pre-sale activity you want to operate on does not exist',
    'error_status' => 'The operation cannot be performed in the current state!',
    'button_finish' => 'The end of the event',
    'notice_finish' => '(modify the end time of the activity to be the current time)',
    'button_succeed' => 'Activity success',
    'notice_succeed' => '(update order price)',
    'button_fail' => 'Activity of failure',
    'notice_fail' => '(cancellation of order, margin refund of account balance, failure reasons can be written in the activity description)',
    'cancel_order_reason' => 'Open to booking a failure',
    'js_languages' =>
        array(
            'succeed_confirm' => 'This operation is irreversible. Are you sure that you want to set up the pre-sale successfully?',
            'fail_confirm' => 'This operation is irreversible. Are you sure that you want to set the pre-sale activity to fail?',
            'error_goods_null' => 'You did not choose the pre-sale goods!',
            'error_deposit' => 'The deposit you entered is not a number!',
            'error_restrict_amount' => 'The limit you entered is not an integer!',
            'error_gift_integral' => 'The number of bonus points you entered is not an integer!',
            'search_is_null' => 'No items were found. Please search again',
            'select_cat_null' => 'Please select activity category',
            'pay_start_time_null' => 'Payment balance payment start time cannot be empty',
            'pay_start_time_cw' => 'The time to pay balance payment must be longer than the time to pay down payment',
            'pay_end_time_null' => 'Payment balance payment end time cannot be empty',
            'pay_end_time_cw' => 'The end time to pay balance payment must be greater than the start time to pay balance payment',
            'batch_drop_confirm' => 'Are you sure you want to delete the selected pre-sale activity?',
        ),
    'button_mail' => 'Send E-mail',
    'notice_mail' => '(inform the customer to pay the balance so as to facilitate shipment)',
    'mail_result' => 'This pre-sale activity has a total of %s valid orders, and %s email was successfully sent.',
    'invalid_time' => 'You entered an invalid pre-order time.',
    'add_success' => 'Add pre-sale activity successfully.',
    'edit_success' => 'Editing pre-sale successfully.',
    'back_list' => 'Returns the list of pre-sale activities.',
    'continue_add' => 'Continue adding pre-sale activity.',
    'PS' => 'Pre-sale goods must be removed from shelves first',
    'pay_start_end' => 'Time to pay balance payment:',
    'shop_price' => 'Our price:',
    'ess_info' => 'The basic information',
    'act_help' => 'Activities that',
    'order' => 'The order',
    'select_search_presale_goods' => 'Please select search for pre-sale items',
    'integral_goods_adopt_status_success' => 'The audit status of integral goods was set successfully',
    'error_goods_null' => 'You did not choose the pre-sale goods!',
    'error_goods_exist' => 'There is a pre-sale activity for the goods you selected!',
    'error_price_ladder' => 'You did not enter a valid price ladder!',
    'error_restrict_amount' => 'The limit cannot be less than the maximum number on the price ladder',
    'error_exist_order' => 'The pre-sale activity has already had the order, cannot delete!',
    'batch_drop_success' => '%s pre-sale activity record was successfully deleted (pre-sale activity with existing order cannot be deleted).',
    'no_select_presale' => 'You have no record of pre-sale activity!',
    'tutorials_bonus_list_one' => 'Instructions for third-party login plug-in',
    'log_action' =>
        array(
            'group_buy' => 'Open to booking commodity',
        ),
    'notice_act_name' => 'If left blank, take the name of the auction item (this name is only used in the background and is not displayed in the foreground)',
    'operation_prompt_content' =>
        array(
            'info' =>
                array(
                    0 => 'Note that pre-sale goods need to be removed from the shelves first.',
                    1 => 'Note that the time to pay balance payment is after the time to pay the deposit.',
                    2 => 'The front desk in the pre-sale channel can view the added pre-sale activity information.',
                    3 => 'Note that if the pre-sale "activity description" is not filled in, the product details will be called by default.',
                ),
            'list' =>
                array(
                    0 => 'Shows a list of pre-sale activities.',
                    1 => 'The specific pre-sale activity information can be found by searching the keyword product name and filtering the store name.',
                    2 => 'Can be added, edit, modify, delete and other operations, to view the pre-sale orders.',
                ),
        ),
);


return $_LANG;
