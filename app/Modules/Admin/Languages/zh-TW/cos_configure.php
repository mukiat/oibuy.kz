<?php

$_LANG['bucket'] = 'Bucket名稱'; //從COS獲得的AccessKeyId
$_LANG['app_id'] = 'APP ID';
$_LANG['secret_id'] = 'SECRET ID';
$_LANG['secret_key'] = 'SECRET KEY';
$_LANG['endpoint'] = 'EndPoint'; //您選定的COS數據中心訪問域名，例如oss-cn-hangzhou.aliyuncs.com
$_LANG['is_cname'] = '域名綁定'; //是否對Bucket做了域名綁定，並且Endpoint參數填寫的是自己的域名
$_LANG['regional'] = 'Bucket地域'; //COS Bucket所在區域
$_LANG['port'] = '埠號'; //COS Bucket所在區域

$_LANG['outside_site'] = '外網域名'; //COS外網域名
$_LANG['inside_site'] = '內網域名'; //COS外網域名

$_LANG['01_oss_list'] = '返回Bucket列表';
$_LANG['02_oss_add'] = '添加Bucket';

$_LANG['on'] = '開啟';
$_LANG['off'] = '關閉';

$_LANG['add_success'] = 'Bucket添加成功';
$_LANG['add_failure'] = 'Bucket已存在';
$_LANG['button_remove'] = '刪除Bucket';
$_LANG['edit_success'] = 'Bucket編輯成功';
$_LANG['remove_success'] = '刪除Bucket成功';
$_LANG['file_management'] = '文件管理';

$_LANG['no_select_user'] = '您現在沒有需要刪除的bucket！';
$_LANG['remove_confirm'] = '您確定要刪除該bucket嗎？';
$_LANG['is_use'] = '是否使用';

$_LANG['ap-beijing-1'] = '北京一區';
$_LANG['ap-beijing'] = '北京';
$_LANG['ap-nanjing'] = '南京';
$_LANG['ap-shanghai'] = '上海';
$_LANG['ap-guangzhou'] = '廣州';
$_LANG['ap-chengdu'] = '成都';
$_LANG['ap-chongqing'] = '重慶';

$_LANG['ap-shenzhen-fsi'] = '深圳金融';
$_LANG['ap-shanghai-fsi'] = '上海金融';
$_LANG['ap-beijing-fsi'] = '北京金融';

$_LANG['http'] = '域名';
$_LANG['delimg'] = '是否刪除圖片';

$_LANG['delete_Bucket'] = '確定刪除該Bucket嗎?';

$_LANG['bucket_oss_notic'] = '與腾讯雲COS開通對象名稱一致';
$_LANG['bucket_keyid_notic'] = '填寫腾讯雲Access Key管理的(ID)';
$_LANG['bucket_secret_notic'] = '填寫腾讯雲Access Key管理的(Secret)';
$_LANG['bucket_url_notic'] = '默認選關閉，官方建議開啟綁定域名，域名格式：http(s)://xx.xxxx.com/（不可綁定當前網站域名，建議新開二級域名）';

/*js語言項*/
$_LANG['js_languages']['oss_bucket_null'] = 'Bucket不能為空';
$_LANG['js_languages']['oss_keyid_null'] = 'KeyId不能為空';
$_LANG['js_languages']['oss_keysecret_null'] = 'KeySecret不能為空';

/* 教程名稱 */
$_LANG['tutorials_bonus_list_one'] = '腾讯雲COS配置使用說明';

/* 頁面頂部操作提示 */
$_LANG['operation_prompt_content']['info'][0] = '填寫在腾讯雲創建的COS信息。';
$_LANG['operation_prompt_content']['info'][1] = '需要在系統設置->商品設置->擴展信息中開啟該功能即可使用。';

$_LANG['operation_prompt_content']['list'][0] = '該頁面展示COS配置的列表信息。';
$_LANG['operation_prompt_content']['list'][1] = '可以直接在列表頁面進行編輯和刪除。';
$_LANG['operation_prompt_content']['list'][2] = 'COS可用於圖片、音視頻、日誌等海量文件的存儲。';

return $_LANG;
