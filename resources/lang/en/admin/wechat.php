<?php

$_LANG = array (
  'num_order' => 'Serial number',
  'wechat' => 'The public platform',
  'wechat_number' => 'WeChat ID',
  'errcode' => 'Error code:',
  'errmsg' => 'Error message:',
  'control_center' => 'Management center',
  'empty' => 'Can\'t be empty',
  'handler' => 'operation',
  'enabled' => 'To enable the',
  'disabled' => 'disable',
  'already_enabled' => 'Is enabled',
  'already_disabled' => 'Has been disabled',
  'to_enabled' => 'Click on the enable',
  'to_disabled' => 'Click on the disable',
  'wechat_num' => 'Public platform account',
  'wechat_name' => 'Name of public account',
  'wechat_type' => 'Public number type',
  'wechat_add_time' => 'Add the time',
  'add' => 'add',
  'yes' => 'is',
  'no' => 'no',
  'button_submit' => 'submit',
  'button_reset' => 'reset',
    'button_revoke' => 'revoke',
  'wechat_status' => 'state',
  'wechat_sequence' => 'The sorting',
  'wechat_operating' => 'operation',
  'wechat_manage' => 'Functional management',
  'wechat_type0' => 'Unverified public number',
  'wechat_type1' => 'Subscribe to the no.',
  'wechat_type2' => 'Service no.',
  'wechat_type3' => 'Authentication service number',
  'wechat_open' => 'open',
  'wechat_close' => 'Shut down',
  'wechat_editor' => 'edit',
  'wechat_see' => 'To view',
  'uninstall' => 'uninstall',
  'drop' => 'delete',
  'disabled_drop' => 'Prohibit to delete',
  'confirm_delete' => 'Are you sure to delete?',
  'button_save' => 'save',
  'wechat_register' => 'There is no public account yet. Please try %s to add a public account.',
  'edit_wechat' => 'Setting of official account',
  'edit_wechat_tips' => 
  array (
    0 => 'First, before configuration, you need to apply for a WeChat service number and pass WeChat certification. (the authentication service number needs to be noted that the WeChat official needs to be re-authenticated every year. If the authentication expires, the interface function will not be used. For details, please log in the WeChat public account platform.)',
    1 => 'Ii. The domain name of the website needs to be put on record by ICP and properly resolved to the space server. The temporary domain name and IP address cannot be configured.',
    2 => 'Three, login <a href = "https://mp.weixin.qq.com/" target = "_blank" / > WeChat public platform </a>, to get and fill in turn
Public ID name, public ID original ID, Appid, Appsecret, token value.',
    3 => 'Four, custom Token value, must be English or number (length of 3-32 characters), such as weixintoken, and keep the background and public number platform filled in the same.',
    4 => 'V. copy the interface address, fill in the WeChat public account platform development => basic configuration, URL address under server configuration, verify and submit, and enable. (note that only port 80 is supported)',
  ),
  'wechat_id' => 'The original id of the public number',
  'token' => 'Token',
  'appid' => 'AppID',
  'appsecret' => 'AppSecret',
  'aeskey' => 'EncodingAESKey',
  'wechat_add' => 'new',
  'wechat_help1' => 'Such as: ecmoban',
  'wechat_help2' => 'Please fill in carefully, for example: gh_845581623321',
  'wechat_help3' => 'Custom Token value, ensure that the background and public number platform filled in the same',
  'wechat_help4' => 'Please note that AppID needs to be consistent with WeChat public account platform, and pay attention to information security, do not disclose to third parties',
  'wechat_help5' => 'The AppSecret key needs to be consistent with the WeChat public account platform, and should not be modified or disclosed to any third party without special reasons',
  'wechat_help6' => 'After it is opened, the micro mall will automatically authorize login; otherwise, it will not authorize login and only provide ordinary micro mall functions. On by default.',
  'wechat_help7' => 'Manually filled or generated by the developer, it will be used as the body encryption and decryption key',
  'must_name' => 'Please fill in the name of the public number',
  'must_id' => 'Please fill in the original ID of the public number',
  'must_token' => 'Please fill in the public number Token',
  'wechat_empty' => 'Public number does not exist',
  'open_wechat' => 'Please open the public account',
  'on_login' => 'Enable authorized login',
  'wechat_oauth' => 'WeChat OAuth name',
  'wechat_oauth_callback' => 'WeChat OAuth callback address',
  'wechat_oauth_push' => 'WeChat OAuth push amount',
  'wechat_api_url' => 'WeChat URL interface address',
  'make_token' => 'To generate the Token',
  'copy_token' => 'Copying the Token',
  'copy_url' => 'Copy the URL',
  'support_off' => 'Did not open',
  'wechat_menu' => 'Micro mail tunnels',
  'mass_message' => 'Group-sent message',
  'mass_history' => 'Mass record',
  'mass_message_tips' => 
  array (
    0 => '1. Group messages are only provided to the service number authenticated by WeChat for the time being',
    1 => '2. Users can only receive 4 group messages per month, and more than 4 group messages will fail to send to the user.',
    2 => '3. The upper limit of the title of group text message is 64 bytes, and the upper limit of the word count of group message is 1200 characters or 600 Chinese characters.',
    3 => 'When the message is returned successfully, it means that the group task has been submitted successfully, but it does not mean that the group task has been completed at this time. Therefore, it is still possible that the user did not receive the message due to abnormal conditions in the subsequent sending process, such as the message may be audited and the server is unstable. In addition, it usually takes a long time to send all the group tasks. Please be patient and wait.',
  ),
  'select_tags' => 'Select tag group',
  'mass_tags_notice' => 'Before each group post, you need to update your followers',
  'mass_article_news_notice' => 'One text message supports 1 to 8 text messages, more than 8 will send failure',
  'please_select_massage' => 'Select the user or select the message to send',
  'mass_sending_wait' => 'The group sending task has started, but it usually takes a long time to complete the sending, please be patient',
  'massage_not_exist' => 'Message does not exist',
  'mass_history_tips' => 
  array (
    0 => '1. Only messages that have been sent successfully can be deleted',
    1 => '2. Deleting a message is to invalidate the graphic details page of the message. Users who have received the message can still see the message card locally.',
    2 => '3, if multiple group send is a text message, then delete one of the group, it will delete the text message, resulting in all the group are invalid',
    3 => '★ send the number of people, that is, exclude when sending filtering, user rejection, receive has reached 4 users',
  ),
  'mass_list' => 'Send the record',
  'mass_title' => 'The title',
  'mass_status' => 'Delivery status',
  'mass_send_time' => 'Send time',
  'mass_totalcount' => 'The number of sending',
  'mass_sentcount' => 'The number of successful',
  'mass_filtercount' => 'The number of filter',
  'mass_errorcount' => 'The number of failures',
  'autoreply_manage' => 'Auto reply',
  'subscribe_autoreply' => 'Focus on autoresponders',
  'msg_autoreply' => 'Automatic message reply',
  'keywords_autoreply' => 'Key words automatic reply',
  'autoreply_manage_tips' => 
  array (
    0 => 'There are three types of auto-reply: attention auto-reply, message auto-reply and keyword auto-reply. Reply content can be set to text, picture, voice, video. Text message reply content can be directly filled in, the length limit is 1024 bytes (about 200 words, including punctuation and other special characters), other material needs to be added in the material management first.',
    1 => 'I. follow the automatic reply: that is, users follow the messages automatically replied by WeChat official account (including re-follow). For example: welcome to pay attention to WeChat official account!',
    2 => '1.1 ★ pay attention to automatic reply, do not support graphic message material.',
    3 => 'Second, automatic message reply: when the user enters any message and cannot match the existing keywords in the system or does not add keywords in the automatic keyword reply, a message prompt will be replied by default. For example: sorry! The keyword that you input does not exist, suggest you consult relevant customer service. You can also type help to see how to use help!',
    4 => '2.1 ★ message automatic reply, can cooperate with the keyword automatic reply flexible use. The reply prompts the user to enter a keyword already in the system.',
    5 => 'Three, keyword automatic reply: that is to add the rule keyword automatic reply.',
    6 => '3.1 word limit: WeChat public platform authentication and non-authentication users\' automatic keyword reply set the upper limit of 200 rules (each rule name can set up to 60 Chinese characters), each rule can set up to 10 keywords (each keyword can set up to 30 Chinese characters)',
    7 => '3.2 rule setting: a rule you can set more than one keyword, it is recommended to use common keywords, such as the keywords: help, help. A combination of Chinese and English is best. If the user sends a message that contains one of the keywords you set, the system matches the automatic response.',
    8 => '3.3 precautions: keywords should not be set for existing keywords in the system, such as hot, best, news, etc.',
  ),
  'text' => 'The text',
  'rule_add' => 'Add rules',
  'rule_name' => 'Rule name',
  'rule_keywords' => 'The keyword',
  'rule_keywords_notice' => 'Add multiple keywords, separated by English commas ","',
  'rule_content' => 'Reply content',
  'rule_name_empty' => 'Please fill in the name of the rule',
  'rule_keywords_empty' => 'Please fill in at least 1 key word',
  'rule_content_empty' => 'Please fill in or select the reply',
  'rule_name_length_limit' => 'The rule name is 60 words at most',
  'rule_keywords_exit' => 'Key words :val already exists, please fill in again!',
  'wechat_article' => 'Material management',
  'article_tips' => 
  array (
    0 => 'Graphic material: divided into single graphic material, multi graphic material. Support picture, voice, video material.',
    1 => 'After the addition of single graphic material, you can combine multiple single graphic material into a multi-graphic material.',
    2 => '★ note: if the single graphics and text material after modification, the original add good graphics and text material needs to be recombined',
  ),
  'article' => 'Graphic material',
  'article_edit' => 'Graphic material editing',
  'article_edit_news' => 'Multi - graphic material editing',
  'article_add' => 'By adding',
  'article_add_news' => 'Multitext addition',
  'article_news_tips' => 
  array (
    0 => 'Limit the number of graphic messages to 8. If the number of graphic messages exceeds 8, WeChat will have no response.',
    1 => 'It is recommended that the maximum number of multi-graphic message bars in the practical application is 4, and the display effect after sending is the best (exactly the height of a mobile phone screen).',
  ),
  'article_news_select' => 'Select graphic information',
  'article_news_reset' => 'Empty the re-election',
  'article_news' => 'Graphic information',
  'article_news_notice' => 'The maximum limit of graphic messages is 8, and it is recommended to have 4 single graphic messages (just enough to display a mobile phone screen).',
  'article_select' => 'Select material',
  'article_title' => 'The title',
  'article_author' => 'The author',
  'article_file' => 'The cover',
  'article_file_notice' => 'Recommended size: 900 pixels * 500 pixels, support JPG, PNG format',
  'select_gallery_album' => 'Image library selection',
  'gallery_album' => 'The gallery',
  'article_file_show_whether' => 'Whether to display cover image',
  'article_digest' => 'Abstract',
  'article_content' => 'The body of the',
  'article_link' => 'The original link',
  'article_link_notice' => 'Current material link, fill in can be used as an external link. Please bring HTTP or HTTPS before the link',
  'article_sort' => 'The sorting',
  'please_upload' => 'Please upload pictures',
  'button_upload' => 'upload',
  'button_download' => 'download',
  'not_file_type' => 'File upload type not allowed',
  'file_size_limit_5' => 'Image size cannot exceed 5MB!',
  'please_add_again' => 'Please readd',
  'content' => 'The body of the',
  'link_err' => 'Incorrect link format',
  'determine' => 'determine',
  'reset' => 'reset',
  'picture' => 'The picture',
  'picture_edit' => 'Photo editors',
  'picture_name' => 'Image name',
  'picture_tips' => 
  array (
    0 => 'Image size: no more than 1M, format: JPG.',
  ),
  'voice' => 'voice',
  'voice_tips' => 
  array (
    0 => 'Size of speech material: not exceeding 2M, length: not exceeding 60s, format: mp3, amr',
  ),
  'video' => 'video',
  'video_tips' => 
  array (
    0 => 'Video material size: recommended under 2MB, format: MP4',
    1 => 'It is recommended to use the video address of third-party video websites such as youku directly. Advantages: no server resources, support larger, more format video material.',
  ),
  'video_add' => 'Video to add',
  'upload_video_file' => 'Upload video files',
  'video_title' => 'The title',
  'video_desc' => 'Introduction to the',
  'voice_empty' => 'Please upload voice',
  'video_empty' => 'Please upload video',
  'upload_video' => 'Upload video',
  'file_size_limit' => 'File size exceeds maximum limit, please re-upload',
  'file_not_exist' => 'File does not exist',
    'config_wxt' => 'Please contact the administrator to configure the platform wechat',
    'graphic_message' => 'By the message',
    'small_procedures' => 'Small program',
    'fail_message' => 'Failed to send! Only fans who have sent messages to official accounts within 48 hours will receive them',
  'menu' => 'Custom menu',
  'menu_tips' => 
  array (
    0 => 'WeChat custom menu can add up to 3 first level menus, 5 second level menus.',
    1 => 'WeChat custom menu is divided into two types: keyword click, url view. Click is a response to the keyword command, view is a direct jump URL address (fill in the absolute path).',
    2 => 'After each modification of the custom menu, due to WeChat client cache, it takes about 24 hours for the WeChat client to display and take effect. You can try to refocus the WeChat public account, or clear the WeChat cache.',
  ),
  'menu_add' => 'Menu to add',
  'menu_edit' => 'The menu editor',
  'menu_name' => 'The name of the menu',
  'menu_type' => 'The menu type',
  'menu_parent' => 'The parent menu',
  'menu_select' => 'Please select menu',
  'menu_click' => 'Click on the',
  'menu_view' => 'Web page',
  'menu_miniprogram' => 'Small program',
  'menu_keyword' => 'Menu keywords',
  'menu_url' => 'Outside the chain of the URL',
  'menu_mini_appid' => 'AppID',
  'menu_mini_page' => 'Applet page',
  'menu_create' => 'Generate custom menus',
  'menu_show' => 'According to',
  'menu_url_length' => 'Url links exceed the limit',
  'menu_empty' => 'Please add at least one custom menu',
  'menu_select_del' => 'Select the menu you want to delete',
  'menu_not_exit' => 'Menu does not exist or has been deleted',
  'menu_help1' => 'If you don\'t have any special needs, please don\'t fill in here (if you want to dial with one button, please fill in "tel: your telephone number ").',
  'sub_title' => 'Fans management',
  'sub_list' => 'Focus on the user',
  'sub_search' => 'Can enter nickname, city, country, province keyword search',
  'search_for' => 'search',
  'sub_headimg' => 'Head portrait',
  'sub_openid' => 'WeChat unique user id (openid)',
  'sub_nickname' => 'nickname',
  'sub_area' => 'region',
  'sub_sex' => 'gender',
  'sub_province' => 'Province (municipality directly under the central government)',
  'sub_city' => 'city',
  'sub_time' => 'Pay close attention to time',
  'sub_move' => 'transfer',
  'sub_move_sucess' => 'Transfer group success',
  'sub_group' => 'In the group',
  'sub_update_user' => 'Update the fans',
  'send_custom_message' => 'Send customer service message',
  'custom_message_list' => 'Customer service message list',
  'message_content' => 'The message content',
  'message_time' => 'Send time',
  'button_send' => 'send',
  'select_openid' => 'Please select WeChat user',
  'sub_help1' => 'PS: only fans who interact with WeChat official account within 48 hours can receive information, which can be used to inform fans of winning prizes and other application scenarios in some activities',
  'sub_binduser' => 'Bind the user',
  'interactive_user' => 'The interactive user',
  'official' => 'The public,',
  'share_list' => 'Share statistics',
  'sub_list_tips' => 
  array (
    0 => 'Fan management: show the user information that has been concerned about WeChat public number, not show those that have not been concerned.',
    1 => '1. Search function supports searching by user nickname, city, country and province.',
    2 => '2. Label management, a public number, up to 100 labels can be created. Each official account can tag users up to 20 times',
    3 => '3. To send customer service messages, you can send WeChat messages to WeChat users alone (only fans who have interaction with the public account within 48 hours can receive the messages, otherwise it will fail to send).',
    4 => '★ notes: before sending messages to users, labeling and other operations, please click the update button in time, so as to synchronize the user group (labels) and quantity of WeChat public account platform.',
  ),
  'sub_from' => 'Source of fans',
  'sub_user' => 'Check the members',
  'keywords_empty' => 'Search keywords cannot be empty',
  'update_complete' => 'Update complete',
  'group_sys' => 'Sync group',
  'group_title' => 'Group management',
  'group_num' => 'Number in the public platform',
  'group_name' => 'Group name',
  'group_fans' => 'Amount of fans',
  'group_add' => 'Add a group',
  'group_edit' => 'Editing group',
  'group_update' => 'update',
  'group_move' => 'Move the selected fans into groups',
  'select_please' => 'Please choose your fans first',
  'tag_sys' => 'Synchronous label',
  'tag_title' => 'Label management',
  'tag_num' => 'Number in the public platform',
  'tag_name' => 'Tag name',
  'tag_fans' => 'Amount of fans',
  'tag_add' => 'Add tags',
  'tag_edit' => 'Edit the label',
  'tag_update' => 'update',
  'tag_join' => 'join',
  'tag_move' => 'Fans will be selected to add tags',
  'tag_move_sucess' => 'Tag added successfully',
  'tag_move_fail' => 'Tag added failed',
  'tag_move_exit' => 'This tag has been added',
  'tag_move_three' => 'A user can only add up to three labels',
  'user_tag' => 'User label',
  'tag_delete' => 'Remove the label',
  'tag_delete_sucess' => 'Tag deleted successfully',
  'tag_delete_fail' => 'Tag deletion failed',
  'batch_tagging_limit' => 'Batch label quantity cannot exceed 50 at a time',
  'edit_remark_name' => 'Modify remark name',
  'remark_name' => 'Note the name',
  'input_remark_name' => 'Please enter remarks name',
  'remark_name_empty' => 'The remark name cannot be empty',
  'edit_remark_name_success' => 'Modified remark name successfully',
  'confirm_delete_tag' => 'Are you sure you want to remove this label?',
  'tag_empty' => 'Please select label',
  'qrcode_manage' => 'Qrcode management',
  'qrcode_list' => 'Qrcode list',
  'qrcode_list_tips' => 
  array (
    0 => 'Channel qrcode. Temporary qrcode or permanent qrcode can be generated for offline display of some scenes, allowing users to scan and follow, similar effect to follow WeChat public account.',
    1 => 'Temporary two-dimensional code, there is an expiration time, the maximum can be set to the two-dimensional code generated after 30 days (i.e. 2592000 seconds) expired, but can generate a large number. Temporary qrcode is mainly used for account binding and other business scenarios that do not require the qrcode to be permanently saved',
    2 => 'Permanent qrcodes, which have no expiration time, are small (currently up to 100,000). Permanent qrcode is mainly used for account binding, user source statistics and other scenarios.',
    3 => 'The value ID of the application scene is 32-bit non-zero integer (100001-4294967295) for temporary two-dimensional code, and the maximum value of permanent two-dimensional code is 100000 (currently the parameters only support 1-100000), please fill in from small to large',
  ),
  'qrcode_edit' => 'Qrcode editing',
  'qrcode_edit_tips' => 
  array (
    0 => 'Fill in the system\'s own keywords (function extension) or automatic reply in the keyword custom response rules keywords',
    1 => 'Application scenario values: fill in non-0 integer values from small to large',
  ),
  'qrcode' => 'Channel qrcode',
  'qrcode_scene' => 'Application scenarios',
  'qrcode_scene_value' => 'Apply scenario value ID',
  'qrcode_scene_limit' => 'The user\'s qrcode already exists',
  'qrcode_scene_value_limit' => 'The application scenario value ID already exists. Please replace it',
  'qrcode_type' => 'Type of qrcode',
  'qrcode_function' => 'keywords',
  'qrcode_function_desc' => 'Select keywords',
  'qrcode_short' => 'Temporary qrcode',
  'qrcode_forever' => 'Permanent qrcode',
  'qrcode_get' => 'Get qrcode',
  'qrcode_isdisabled' => 'Qrcode disabled, please re-enable',
  'qrcode_islosed' => 'Recommender does not exist, qrcode is invalid, please add again',
  'qrcode_valid_time' => 'Qrcode valid time',
  'minute' => 'minutes',
  'hour' => 'hours',
  'day' => 'day',
  'qrcode_help1' => 'The default time is 30 minutes (1800 seconds), the maximum time of temporary qrcode is 30 days (2592000 seconds), and the permanent qrcode does not need to be filled in',
  'qrcode_help2' => 'Temporary qrcode value between 100001-4294967295, permanent qrcode value between 1-100000, do not repeat',
  'qrcode_short_limit' => 'Temporary qrcode maximum not more than 30 days',
  'qrcode_short_range' => 'The value range of temporary two-dimensional code is 100001-4294967295',
  'qrcode_forever_range' => 'Permanent qrcode value range 1-100000',
  'goto_reply_keywords' => 'To add custom keywords',
  'qrcode_get_notice' => 'Copy the qrcode link, can share to the circle of friends',
  'qrcode_valid_time_placeholder' => 'Default 30 minutes (1800 seconds)',
  'share' => 'Sweep the code to introduce',
  'share_qrcode' => 'Recommended qrcode',
  'share_name' => 'referees',
  'share_userid' => 'Referrer user ID',
  'share_userid_desc' => 'Membership number (1-100000 only)',
  'share_account' => 'Cash into',
  'scan_num' => 'Amount of scanning',
  'expire_seconds' => 'Valid time',
  'share_help' => 'Do not fill in the unlimited, if filled in the two-dimensional code has expired time, expired!',
  'no_limit' => 'unlimited',
  'users_not_exit' => 'The user does not exist or has been deleted',
    'share_tips' => [
        'Sweep the code to introduce：That is, the administrator can use the existing members of the website to generate the qrcode with recommendation function (the default permanent qrcode), so that new users can scan the code and pay attention to it, that is, to form a superior and subordinate relationship with referees.',
        'The scanning amount of the recommended qrcode is the first scanning amount of a new user, that is, every registration of a new user is counted once',
        'Need to open the website to recommend the use of registration sharing function.。<a href="../affiliate.php?act=list">To turn on the recommended Settings</a>'
    ],

  'templates' => 'Message to remind',
  'template_title' => 'Template title',
  'template_id' => 'Template ID',
  'template_code' => 'The template number',
  'template_content' => 'Content of the template',
  'template_remark' => 'note',
  'template_edit_fail' => 'Edit failure',
  'please_select_industry' => 'Please select primary and secondary industries',
  'please_apply_template' => 'Please log in WeChat public account platform and apply for opening template message',
    'templates_tips' => [
        'Message reminder, that is, WeChat template message, you need to log in WeChat public account platform first, add plug-ins, apply for opening template message.',
        'Then choose the industry: IT technology/Internet | e-commerce, if there are other industries, choose IT technology/electronic technology. You can change the selected industry once a month',
        'Enable the list of required template messages to trigger the template message in the corresponding event; Edit template message notes, can be added to display custom prompt message content',
        'Each public account can use 25 templates at the same time, beyond which the template message function will not be available.'
    ],
    'industry' => 'Industry',
    'main_industry' => 'The main industry',
    'deputy_industry' => 'Deputy battalion industry',
    'edit_industry' => 'Modify the industry',
    'choose_main_industry' => 'Select main industry',
    'main_industry_01' => 'IT technology/Internet | e-commerce',
    'choose_deputy_industry' => 'Select sub-industry',
    'deputy_industry_01'=> 'IT technology/electronics technology',
    'confirm_reset' => 'Are you sure you want to reset the template message?',

    'wechat_extend' => 'Function extension',
  'wechat_extend_tips' =>
  array (
    0 => 'Function expansion, for the system default keyword automatic reply function, combined with the website products, members, orders and other functions to provide interactive query service, with WeChat custom menu.',
    1 => 'Install and fill in the configuration information if necessary, copy the corresponding keywords and add them to WeChat custom menu for use.',
    2 => 'For example, the keyword for boutique is best (you can also use the corresponding extension word), add a menu to the custom menu, and select the keyword click for menu type.',
  ),
  'extend_edit' => 'Install the extension',
  'confirm_uninstall' => 'Are you sure you want to uninstall this plug-in?',
  'export' => 'export',
  'must_keywords' => 'Please fill in the extension words',
  'extend_is_enabled' => 'Plug-in installed',
  'please_select_commond' => 'Select the function to operate on',
    'extend_name' => 'Name of the extension',
    'extend_command' => 'Command of the extension',
    'extend_keywords' => 'Keywords of the extension',
    'extend_keywords_notice' => 'Please separate multiple extension words with English ","',
    'point_status' => 'Whether to open bonus points',
    'point_value' => 'Integral value',
    'point_num' => 'Effective number of',
    'point_num_notice' => 'The number of valid times in the interval',
    'point_interval' => 'Time interval',
    'people_num' => 'Number of participants',
    'people_num_notice' => 'Count the number of WeChat users who have participated in this activity (including those who have not won the prize and those who have won the prize)',
    'period_time' => 'Starting and Ending time',
    'prize_num' => 'Lottery number',
    'prize_num_notice' => 'That is, within the time period, users can draw a total number of times',
    'prize_list' => 'The prize list',
    'prize_level' => 'The Awards',
    'prize_count' => 'The prize amount',
    'prize_prob' => 'The Probability(A total of 100%)',
    'for_example' => 'for example',
    'activity_rules' => 'Activity rules',
    'media_info' => 'Material information',
    'edit_media' => 'Edit material',
    'media_info_notice' => 'The corresponding material in the material management, please be careful not to delete',
    'prize_level_0' => 'Losing lottery',
    'prize_level_1' => 'The first prize',
    'prize_level_2' => 'The second prize',
    'prize_level_3' => 'The third prize',
    'prize_level_4' => 'The four prize',
    'prize_level_5' => 'The five prize',
    'prize_level_6' => 'The six prize',
    'wechat_js_languages' => [
        'prize_level_limited_6' => 'No more than six awards',
        'prize_count_not_minus' => 'The quantity cannot be negative',
        'prize_prob_not_minus' => 'The probability of winning cannot be negative',
        'prize_prob_max_limited_100' => 'The probability of winning cannot exceed 100',
        'prize_prob_sum_max_limited_100' => 'The sum of the winning probabilities cannot exceed 100',
    ],
  'winner_list' => 'The winning record',
  'winner_list_tips' => 
  array (
    0 => 'Winning name display all winning user information, did not win, did not pay attention to WeChat public number does not show.',
    1 => 'According to the contact information filled in by the user, the user can be contacted offline about receiving awards. Click notification to push a WeChat notification message. (this message requires users to follow WeChat official account and interact with it within 48 hours before it can be sent successfully)',
    2 => 'After the relevant prizes are sent and confirmed with the user, click to release immediately to indicate that the user has received the prize.',
  ),
  'prize_name' => 'The prize',
  'issue_status' => 'Whether to issue',
  'winner_info' => 'Winning user information',
  'prize_time' => 'The winning time',
  'please_select_prize' => 'Please select the winning record',
  'unset_issue_status' => 'Cancel out',
  'set_issue_status' => 'Offering prizes',
  'send_message' => 'Inform the user',
  'already_issue' => 'issued',
  'no_issue' => 'Not to issue',
  'user_name' => 'The name',
  'user_mobile' => 'Mobile phone no.',
  'user_address' => 'Contact address',
  'sign_list' => 'Sign in record',
  'sign_list_tips' => 
  array (
    0 => 'Show all fans check in records, did not pay attention to the public WeChat do not show.',
  ),
  'sign_time' => 'Check-in time',
  'sign_prize' => 'Sign in to reward',
  'sign_remark' => 'Sign in to comment',
    'cou_man_0' => 'no threshold',
    'cou_man_1' => 'Shopping with :cou_man Yuan usable',
  'wechat_market' => 'Marketing center',
  'wechat_market_tips' => 
  array (
    0 => 'Marketing center, click management, enter the create activity page, configure parameters.',
  ),
  'wechat_wall' => 'WeChat wall',
  'wechat_zjd' => 'Hit a golden eggs',
  'wechat_dzp' => 'Big wheel',
  'wechat_ggk' => 'Scratch CARDS',
  'wechat_redpack' => 'Cash bonus',
  'no_start' => 'Not at the',
  'start' => 'ongoing',
  'over' => 'Has ended',
  'market_list' => 'List of activities',
  'market_name' => 'The name of the event',
  'market_edit' => 'Editing activity',
  'market_add' => 'Add activities',
  'market_qrcode' => 'Mobile qrcode',
  'market_delete' => 'Delete activity',
  'data_list' => 'Data list',
  'is_checked' => 'The approved',
  'no_check' => 'Not audit',
  'check' => 'audit',
  'is_sended' => 'issued',
  'cancle_send' => 'Cancel out',
  'no_send' => 'Not to issue',
  'send' => 'Immediately issued',
  'normal_redpack' => 'Ordinary red envelopes',
  'group_redpack' => 'Fission a red envelope',
  'share_title' => 'Share the title',
  'share_description' => 'Share the description',
  'activity_title' => 'Activity page title',
  'advertice_content' => 'The contents of advertisements',
  'data_null' => 'No exportable data',
);


return $_LANG;
