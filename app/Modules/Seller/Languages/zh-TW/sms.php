<?php

/* 導航條 */
$_LANG['register_sms'] = '注冊或啟用簡訊賬號';

/* 注冊和啟用簡訊功能 */
$_LANG['email'] = '電子郵箱';
$_LANG['password'] = '登錄密碼';
$_LANG['domain'] = '網店域名';
$_LANG['register_new'] = '注冊新賬號';
$_LANG['error_tips'] = '請在商店設置->簡訊設置，先注冊簡訊服務並正確配置簡訊服務！';
$_LANG['enable_old'] = '啟用已有賬號';

/* 簡訊特服信息 */
$_LANG['sms_user_name'] = '用戶名：';
$_LANG['sms_password'] = '密碼：';
$_LANG['sms_domain'] = '域名：';
$_LANG['sms_num'] = '簡訊特服號：';
$_LANG['sms_count'] = '發送簡訊條數：';
$_LANG['sms_total_money'] = '總共沖值金額：';
$_LANG['sms_balance'] = '余額：';
$_LANG['sms_last_request'] = '最後一次請求時間：';
$_LANG['disable'] = '注銷簡訊服務';

/* 發送簡訊 */
$_LANG['phone'] = '接收手機號碼';
$_LANG['user_rand'] = '按用戶等級發送短消息';
$_LANG['phone_notice'] = '多個手機號碼用半形逗號分開';
$_LANG['msg'] = '消息內容';
$_LANG['msg_notice'] = '最長70字元';
$_LANG['send_date'] = '定時發送時間';
$_LANG['send_date_notice'] = '格式為YYYY-MM-DD HH:II。為空表示立即發送。';
$_LANG['back_send_history'] = '返回發送歷史列表';
$_LANG['back_charge_history'] = '返回充值歷史列表';

/* 記錄查詢界面 */
$_LANG['start_date'] = '開始日期';
$_LANG['date_notice'] = '格式為YYYY-MM-DD。可為空。';
$_LANG['end_date'] = '結束日期';
$_LANG['page_size'] = '每頁顯示數量';
$_LANG['page_size_notice'] = '可為空，表示每頁顯示20條記錄';
$_LANG['page'] = '頁數';
$_LANG['page_notice'] = '可為空，表示顯示1頁';
$_LANG['charge'] = '請輸入您想要充值的金額';

/* 動作確認信息 */
$_LANG['history_query_error'] = '對不起，在查詢過程中發生錯誤。';
$_LANG['enable_ok'] = '恭喜，您已成功啟用簡訊服務！';
$_LANG['enable_error'] = '對不起，您啟用簡訊服務失敗。';
$_LANG['disable_ok'] = '您已經成功注銷簡訊服務。';
$_LANG['disable_error'] = '注銷簡訊服務失敗。';
$_LANG['register_ok'] = '恭喜，您已成功注冊簡訊服務！';
$_LANG['register_error'] = '對不起，您注冊簡訊服務失敗。';
$_LANG['send_ok'] = '恭喜，您的簡訊已經成功發送！';
$_LANG['send_error'] = '對不起，在發送簡訊過程中發生錯誤。';
$_LANG['error_no'] = '錯誤標識';
$_LANG['error_msg'] = '錯誤描述';
$_LANG['empty_info'] = '您的簡訊特服信息為空。';

/* 充值記錄 */
$_LANG['order_id'] = '訂單號';
$_LANG['money'] = '充值金額';
$_LANG['log_date'] = '充值日期';

/* 發送記錄 */
$_LANG['sent_phones'] = '發送手機號碼';
$_LANG['content'] = '發送內容';
$_LANG['charge_num'] = '計費條數';
$_LANG['sent_date'] = '發送日期';
$_LANG['send_status'] = '發送狀態';
$_LANG['status'][0] = '失敗';
$_LANG['status'][1] = '成功';
$_LANG['user_list'] = '全體會員';
$_LANG['please_select'] = '請選擇會員等級';

/* 提示 */
$_LANG['test_now'] = '<span style="color:red;"></span>';
$_LANG['msg_price'] = '<span style="color:green;">簡訊每條0.1元(RMB)</span>';

/* API返回的錯誤信息 */
//--注冊
$_LANG['api_errors']['register'][1] = '域名不能為空。';
$_LANG['api_errors']['register'][2] = '郵箱填寫不正確。';
$_LANG['api_errors']['register'][3] = '用戶名已存在。';
$_LANG['api_errors']['register'][4] = '未知錯誤。';
$_LANG['api_errors']['register'][5] = '介面錯誤。';
//--獲取余額
$_LANG['api_errors']['get_balance'][1] = '用戶名密碼不正確。';
$_LANG['api_errors']['get_balance'][2] = '用戶被禁用。';
//--發送簡訊
$_LANG['api_errors']['send'][1] = '用戶名密碼不正確。';
$_LANG['api_errors']['send'][2] = '簡訊內容過長。';
$_LANG['api_errors']['send'][3] = '發送日期應大於當前時間。';
$_LANG['api_errors']['send'][4] = '錯誤的號碼。';
$_LANG['api_errors']['send'][5] = '賬戶余額不足。';
$_LANG['api_errors']['send'][6] = '賬戶已被停用。';
$_LANG['api_errors']['send'][7] = '介面錯誤。';
//--歷史記錄
$_LANG['api_errors']['get_history'][1] = '用戶名密碼不正確。';
$_LANG['api_errors']['get_history'][2] = '查無記錄。';
//--用戶驗證
$_LANG['api_errors']['auth'][1] = '密碼錯誤。';
$_LANG['api_errors']['auth'][2] = '用戶不存在。';

/* 用戶伺服器檢測到的錯誤信息 */
$_LANG['server_errors'][1] = '注冊信息無效。';//ERROR_INVALID_REGISTER_INFO
$_LANG['server_errors'][2] = '啟用信息無效。';//ERROR_INVALID_ENABLE_INFO
$_LANG['server_errors'][3] = '發送的信息有誤。';//ERROR_INVALID_SEND_INFO
$_LANG['server_errors'][4] = '填寫的查詢信息有誤。';//ERROR_INVALID_HISTORY_QUERY
$_LANG['server_errors'][5] = '無效的身份信息。';//ERROR_INVALID_PASSPORT
$_LANG['server_errors'][6] = 'URL不對。';//ERROR_INVALID_URL
$_LANG['server_errors'][7] = 'HTTP響應體為空。';//ERROR_EMPTY_RESPONSE
$_LANG['server_errors'][8] = '無效的XML文件。';//ERROR_INVALID_XML_FILE
$_LANG['server_errors'][9] = '無效的節點名字。';//ERROR_INVALID_NODE_NAME
$_LANG['server_errors'][10] = '存儲失敗。';//ERROR_CANT_STORE
$_LANG['server_errors'][11] = '簡訊功能尚未激活。';//ERROR_INVALID_PASSPORT

/* 客戶端JS語言項 */
//--注冊或啟用
$_LANG['js_languages']['password_empty_error'] = '密碼不能為空。';
$_LANG['js_languages']['username_empty_error'] = '用戶名不能為空。';
$_LANG['js_languages']['username_format_error'] = '用戶名格式不對。';
$_LANG['js_languages']['domain_empty_error'] = '域名不能為空。';
$_LANG['js_languages']['domain_format_error'] = '域名格式不對。';
$_LANG['js_languages']['send_empty_error'] = '發送手機號與發送等級至少填寫一項！';
//--發送
$_LANG['js_languages']['phone_empty_error'] = '請填寫手機號。';
$_LANG['js_languages']['phone_format_error'] = '手機號碼格式不對。';
$_LANG['js_languages']['msg_empty_error'] = '請填寫消息內容。';
$_LANG['js_languages']['send_date_format_error'] = '定時發送時間格式不對。';
//--歷史記錄
$_LANG['js_languages']['start_date_format_error'] = '開始日期格式不對。';
$_LANG['js_languages']['end_date_format_error'] = '結束日期格式不對。';
//--充值
$_LANG['js_languages']['money_empty_error'] = '請輸入您要充值的金額。';
$_LANG['js_languages']['money_format_error'] = '金額格式不對。';

return $_LANG;
