<?php

$_LANG['bucket'] = 'Bucket名称'; //从获得的AccessKeyId
$_LANG['app_id'] = 'APP ID';
$_LANG['secret_id'] = 'SECRET ID';
$_LANG['secret_key'] = 'SECRET KEY';
$_LANG['endpoint'] = 'EndPoint'; //您选定的数据中心访问域名，
$_LANG['is_cname'] = '域名绑定'; //是否对Bucket做了域名绑定，并且Endpoint参数填写的是自己的域名
$_LANG['regional'] = 'Bucket地域'; //Bucket所在区域
$_LANG['port'] = '端口号'; //Bucket所在区域

$_LANG['outside_site'] = '外网域名'; //外网域名
$_LANG['inside_site'] = '内网域名'; //外网域名

$_LANG['01_oss_list'] = '返回Bucket列表';
$_LANG['02_oss_add'] = '添加Bucket';

$_LANG['on'] = '开启';
$_LANG['off'] = '关闭';

$_LANG['add_success'] = 'Bucket添加成功';
$_LANG['add_failure'] = 'Bucket已存在';
$_LANG['button_remove'] = '删除Bucket';
$_LANG['edit_success'] = 'Bucket编辑成功';
$_LANG['remove_success'] = '删除Bucket成功';
$_LANG['file_management'] = '文件管理';

$_LANG['no_select_user'] = '您现在没有需要删除的bucket！';
$_LANG['remove_confirm'] = '您确定要删除该bucket吗？';
$_LANG['is_use'] = '是否使用';

$_LANG['ap-beijing-1'] = '北京一区';
$_LANG['ap-beijing'] = '北京';
$_LANG['ap-nanjing'] = '南京';
$_LANG['ap-shanghai'] = '上海';
$_LANG['ap-guangzhou'] = '广州';
$_LANG['ap-chengdu'] = '成都';
$_LANG['ap-chongqing'] = '重庆';

$_LANG['ap-shenzhen-fsi'] = '深圳金融';
$_LANG['ap-shanghai-fsi'] = '上海金融';
$_LANG['ap-beijing-fsi'] = '北京金融';

$_LANG['http'] = '域名';
$_LANG['delimg'] = '是否删除图片';

$_LANG['delete_Bucket'] = '确定删除该Bucket吗?';

$_LANG['bucket_oss_notic'] = '与COS开通对象名称一致';
$_LANG['bucket_keyid_notic'] = '填写 SECRET ID 管理的(ID)';
$_LANG['bucket_secret_notic'] = '填写 SECRET Key 管理的(Secret)';
$_LANG['bucket_url_notic'] = '默认选关闭，官方建议开启绑定域名，域名格式：http(s)://xx.xxxx.com/（不可绑定当前网站域名，建议新开二级域名）';

/*js语言项*/
$_LANG['js_languages']['oss_bucket_null'] = 'Bucket不能为空';
$_LANG['js_languages']['oss_keyid_null'] = 'KeyId不能为空';
$_LANG['js_languages']['oss_keysecret_null'] = 'KeySecret不能为空';

/* 教程名称 */
$_LANG['tutorials_bonus_list_one'] = 'COS配置使用说明';

/* 页面顶部操作提示 */
$_LANG['operation_prompt_content']['info'][0] = '填写在腾讯云创建的COS信息。';
$_LANG['operation_prompt_content']['info'][1] = '需要在系统设置->商品设置->扩展信息中开启该功能即可使用。';

$_LANG['operation_prompt_content']['list'][0] = '该页面展示COS配置的列表信息。';
$_LANG['operation_prompt_content']['list'][1] = '可以直接在列表页面进行编辑和删除。';
$_LANG['operation_prompt_content']['list'][2] = 'COS可用于图片、音视频、日志等海量文件的存储。';

return $_LANG;
