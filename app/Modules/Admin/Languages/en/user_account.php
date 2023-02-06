<?php

$_LANG = array(
    'please_take_handle' => 'Select the action item',
    'through' => 'through',
    'user_name' => 'members',
    'real_name' => 'Real name',
    'bank_name' => 'Bank name',
    'bank_card' => 'Bank card number',
    'self_num' => 'User id',
    'user_surplus' => 'Advance payment',
    'surplus_id' => 'Serial number',
    'user_id' => 'Member name',
    'surplus_amount' => 'The amount of',
    'add_date' => 'Operation date',
    'pay_mothed' => 'Method of payment',
    'pay_state' => 'Payment status',
    'process_type' => 'type',
    'confirm_date' => 'To date,',
    'surplus_notic' => 'Administrator remarks',
    'surplus_desc' => 'Members to describe',
    'surplus_type' => 'Operation type',
    'bank_number' => 'The bank account',
    'contact' => 'contact',
    'no_user' => 'Anonymous purchase',
    'surplus_type_0' => 'top-up',
    'surplus_type_1' => 'withdrawal',
    'admin_user' => 'The operator',
    'offline_transfer' => 'Offline transfer',
    'surplus_time' => 'Withdrawal time',
    'recharge_time' => 'Recharge time',
    'export_handler' => 'Export',
    'view_export_records' => 'View Export Records',
    'latest_7days' => 'latest 7 days',
    'latest_30days' => 'latest 30 days',
    'latest_3months' => 'latest 3 months',
    'latest_1years' => 'latest 1 years',
    'status' => 'To state',
    'is_confirm' => 'Has been completed',
    'confirm' => 'It has been completed. The balance has been received',
    'unconfirm' => 'unconfirmed',
    'cancel' => 'cancel',
    'confirm_nopay' => 'It has been completed. The balance received to the account will not be processed',
    'complaint_details' => 'A complaint that',
    'complaint_imges' => 'Appeal the credentials',
    'please_select' => 'Please select...',
    'surplus_info' => 'Member amount information',
    'add_info' => 'Operational information',
    'check' => 'To audit',
    'deposit_fee' => 'poundage',
    'deposit_fee_s' => 'Fees:',
    'done' => 'complete',
    'money_type' => 'currency',
    'surplus_add' => 'Add application',
    'surplus_edit' => 'Editor application',
    'attradd_succed' => 'Your operation has been successful!',
    'attradd_failed' => 'You failed this operation!',
    'username_not_exist' => 'The member name you entered does not exist!',
    'cancel_surplus' => 'Are you sure you want to cancel this record?',
    'surplus_frozen_error' => 'The amount to be withdrawn exceeds the frozen balance of this member\'s account, this operation will not be allowed!',
    'surplus_amount_error' => 'The amount to be withdrawn exceeds the balance of the member\'s account.',
    'edit_surplus_notic' => 'The status is now complete. If you want to modify it, set it to unconfirmed',
    'back_list' => 'Return recharge and withdrawal application',
    'continue_add' => 'Continue adding applications',
    'recharge_apply' => 'Top-up application',
    'put_forward_apply' => 'Withdrawal application',
    'keywords_notic' => 'Member name/mobile phone number/email address',
    'js_languages' =>
        array(
            'user_id_empty' => 'Member name cannot be empty!',
            'deposit_amount_empty' => 'Please enter the recharge amount!',
            'pay_code_empty' => 'Please select payment method',
            'deposit_amount_error' => 'Please enter the recharge amount in the correct format!',
            'deposit_type_empty' => 'Please fill in the type!',
            'deposit_notic_empty' => 'Please fill in administrator\'s notes!',
            'deposit_desc_empty' => 'Please fill in the membership description!',
        ),
    'user_account_confirm' => 'Are you sure you\'re done? This operation is irreversible, please be careful!',
    'operation_prompt_content' =>
        array(
            'check' =>
                array(
                    0 => 'Please check the membership recharge or cash withdrawal information carefully.',
                    1 => 'Please fill in administrator\'s notes after checking the payment status.',
                ),
            'info' =>
                array(
                    0 => 'Edit type of membership recharge and payment status can not be modified.',
                ),
            'list' =>
                array(
                    0 => 'You can enter the member name keyword to search, and the sidebar for advanced search.',
                ),
        ),
);

$_LANG['withdraw_user_number'] = 'withdraw number';
$_LANG['withdraw_type_0'] = 'bank';
$_LANG['withdraw_type_1'] = 'weixin';
$_LANG['withdraw_type_2'] = 'alipay';

$_LANG['withdraw_number_notice'] = 'If you choose the withdrawal method of bank card, it is optional. If you do not fill in the bank card number authenticated with the members real name by default; Otherwise mandatory.';
$_LANG['withdraw_number_empty'] = 'please input withdraw number';

return $_LANG;
