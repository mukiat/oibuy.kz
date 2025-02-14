<?php

$_LANG = array(
    'register_sms' => 'Sign up or enable an SMS account',
    'email' => 'email',
    'password' => 'The login password',
    'domain' => 'Shop domain name',
    'register_new' => 'Sign up for a new account',
    'error_tips' => 'Please set up in the store -> SMS Settings, first register SMS service and correct configuration SMS service!',
    'enable_old' => 'Enable existing account',
    'sms_user_name' => 'User name:',
    'sms_password' => 'Password:',
    'sms_domain' => 'Domain name:',
    'sms_num' => 'Special service number:',
    'sms_count' => 'Send short creed number:',
    'sms_total_money' => 'Total amount of deduction:',
    'sms_balance' => 'Balance:',
    'sms_last_request' => 'Last request time:',
    'disable' => 'Unsubscribe from SMS service',
    'phone' => 'Receive mobile phone number',
    'user_rand' => 'Send short messages by user level',
    'phone_notice' => 'Multiple phone Numbers are separated by a half-corner comma',
    'msg' => 'The message content',
    'msg_notice' => 'Maximum 70 characters',
    'send_date' => 'Timed transmission time',
    'send_date_notice' => 'The format is yyyy-mm-dd HH:II. Null means send immediately.',
    'back_send_history' => 'Returns the send history list',
    'back_charge_history' => 'Returns the recharge history list',
    'start_date' => 'Start date',
    'date_notice' => 'The format is yyyy-mm-dd. Can be null.',
    'end_date' => 'End date',
    'page_size' => 'Number per page',
    'page_size_notice' => 'Can be empty, meaning each page displays 20 records',
    'page' => 'Number of pages',
    'page_notice' => 'Can be empty, indicating that 1 page is displayed',
    'charge' => 'Please enter the amount you want to recharge',
    'history_query_error' => 'Sorry, an error occurred during the query.',
    'enable_ok' => 'Congratulations, you have successfully enabled SMS service!',
    'enable_error' => 'Sorry, you failed to enable SMS service.',
    'disable_ok' => 'You have successfully logged out of SMS service.',
    'disable_error' => 'Failed to log out SMS service.',
    'register_ok' => 'Congratulations, you have successfully signed up for SMS service!',
    'register_error' => 'Sorry, you failed to register for SMS service.',
    'send_ok' => 'Congratulations, your message has been successfully sent!',
    'send_error' => 'Sorry, an error occurred while sending a text message.',
    'error_no' => 'Error identification',
    'error_msg' => 'Error description',
    'empty_info' => 'Your special message is empty.',
    'order_id' => 'The order number',
    'money' => 'Top-up amount',
    'log_date' => 'Top-up date',
    'sent_phones' => 'Send mobile phone number',
    'content' => 'Send content',
    'charge_num' => 'Article billing number',
    'sent_date' => 'Sending date',
    'send_status' => 'Delivery status',
    'status' =>
        array(
            0 => 'failure',
            1 => 'successful',
        ),
    'user_list' => 'All the members',
    'please_select' => 'Please select the membership level',
    'test_now' => '<span style="color:red;"></span>',
    'msg_price' => '<span style = "color: green;" > SMS 0.1 yuan (RMB) per message </span>',
    'api_errors' =>
        array(
            'register' =>
                array(
                    1 => 'Domain name cannot be empty.',
                    2 => 'Incorrect email address.',
                    3 => 'The user name already exists.',
                    4 => 'Unknown error.',
                    5 => 'Interface error.',
                ),
            'get_balance' =>
                array(
                    1 => 'Incorrect username and password.',
                    2 => 'The user is disabled.',
                ),
            'send' =>
                array(
                    1 => 'Incorrect username and password.',
                    2 => 'Text messages are too long.',
                    3 => 'The sending date should be greater than the current time.',
                    4 => 'Wrong number.',
                    5 => 'Insufficient account balance.',
                    6 => 'The account has been deactivated.',
                    7 => 'Interface error.',
                ),
            'get_history' =>
                array(
                    1 => 'Incorrect username and password.',
                    2 => 'No record.',
                ),
            'auth' =>
                array(
                    1 => 'Password error.',
                    2 => 'The user does not exist.',
                ),
        ),
    'server_errors' =>
        array(
            1 => 'Invalid registration information.',
            2 => 'Enable information invalid.',
            3 => 'Wrong message sent.',
            4 => 'The query information filled in is incorrect.',
            5 => 'Invalid identity information.',
            6 => 'The URL is wrong.',
            7 => 'The HTTP response body is empty.',
            8 => 'Invalid XML file.',
            9 => 'Invalid node name.',
            10 => 'Storage failed.',
            11 => 'The SMS function has not been activated.',
        ),
    'js_languages' =>
        array(
            'password_empty_error' => 'The password cannot be empty.',
            'username_empty_error' => 'The user name cannot be empty.',
            'username_format_error' => 'Wrong user name format.',
            'domain_empty_error' => 'Domain name cannot be empty.',
            'domain_format_error' => 'Wrong domain name format.',
            'send_empty_error' => 'Send mobile phone number and send level fill in at least one item!',
            'phone_empty_error' => 'Please fill in your mobile phone number.',
            'phone_format_error' => 'Wrong phone number format.',
            'msg_empty_error' => 'Please fill in the message content.',
            'send_date_format_error' => 'Timing send time format is not correct.',
            'start_date_format_error' => 'Wrong start date format.',
            'end_date_format_error' => 'Wrong end date format.',
            'money_empty_error' => 'Please enter the amount you want to recharge.',
            'money_format_error' => 'Wrong amount format.',
        ),
);


return $_LANG;
