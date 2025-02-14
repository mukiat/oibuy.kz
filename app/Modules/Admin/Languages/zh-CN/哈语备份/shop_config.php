<?php

$_LANG['cfg_name']['cloud_storage'] = '云存储';
$_LANG['cfg_range']['cloud_storage']['0'] = '阿里云';
$_LANG['cfg_range']['cloud_storage']['1'] = '华为云';
$_LANG['cfg_range']['cloud_storage']['2'] = '腾讯云';
$_LANG['cfg_desc']['cloud_storage'] = '选择云存储类型，目前提供：阿里云、华为云等类型';

$_LANG['cfg_name']['tp_api'] = '电子面单';
$_LANG['cfg_range']['tp_api']['0'] = '否';
$_LANG['cfg_range']['tp_api']['1'] = '是';
$_LANG['cfg_desc']['tp_api'] = '设置是否启用电子面单【快递鸟】';

$_LANG['cfg_name']['auto_mobile'] = '识别自动跳转H5';
$_LANG['cfg_range']['auto_mobile']['0'] = '否';
$_LANG['cfg_range']['auto_mobile']['1'] = '是';
$_LANG['cfg_desc']['auto_mobile'] = '设置访问PC地址是否自动跳转H5页面';

$_LANG['cfg_name']['header_region'] = '商城头部推荐地区';
$_LANG['cfg_desc']['header_region'] = '用于商城头部地区便于用户快速进入相应热门地区（格式：英文逗号）';

$_LANG['cfg_name']['area_pricetype'] = '商品设置地区模式时';
$_LANG['cfg_range']['area_pricetype']['0'] = '精确到省/直辖市';
$_LANG['cfg_range']['area_pricetype']['1'] = '精确到市/县';
$_LANG['cfg_desc']['area_pricetype'] = '商品【选择地区模式】，商品价格按地区模式显示';

$_LANG['cfg_name']['floor_nav_type'] = '首页楼层左侧导航样式';
$_LANG['cfg_range']['floor_nav_type']['1'] = '样式一';
$_LANG['cfg_range']['floor_nav_type']['2'] = '样式二';
$_LANG['cfg_range']['floor_nav_type']['3'] = '样式三';
$_LANG['cfg_range']['floor_nav_type']['4'] = '样式四';
$_LANG['cfg_desc']['floor_nav_type'] = '<a href="../storage/images/floor_nav_type_icon.jpg" class="nyroModal"><i class="icon icon-picture" onmouseover="toolTip(\'<img src=../storage/images/floor_nav_type_icon.jpg>\')" onmouseout="toolTip()"></i></a>&nbsp;&nbsp;多项设置首页楼层左侧导航定位不同样式';

$_LANG['cfg_name']['ip_type'] = '站点IP定位类型';
$_LANG['cfg_range']['ip_type']['0'] = 'IP库';
$_LANG['cfg_range']['ip_type']['1'] = '腾讯';
$_LANG['cfg_desc']['ip_type'] = 'IP定位主要用于(PC)网站头部和商品详情地区定位，建议选择（腾讯或IP库接口），测试清浏览器缓存';

$_LANG['js_languages']['reduce_num_not_null'] = '请输入类型\n(说明：类型与发票内容一致)\n';
$_LANG['js_languages']['reduce_price_not_null'] = '请输入税率';

$_LANG['cfg_name']['tengxun_key'] = '腾讯地图密钥';
$_LANG['cfg_desc']['tengxun_key'] = '<a href="http://lbs.qq.com/console/user_info.html" target="_blank">获取密钥</a>';

$_LANG['cfg_name']['kuaidi100_key'] = '快递100密钥';
$_LANG['cfg_desc']['kuaidi100_key'] = '请输入<a href="https://www.kuaidi100.com/openapi/applyapi.shtml" target="_blank">快递100密钥</a>';

$_LANG['cfg_name']['use_coupons'] = '是否启用优惠券';
$_LANG['cfg_range']['use_coupons']['0'] = '关闭';
$_LANG['cfg_range']['use_coupons']['1'] = '启用';

$_LANG['cfg_name']['use_value_card'] = '是否启用储值卡';
$_LANG['cfg_range']['use_value_card']['0'] = '关闭';
$_LANG['cfg_range']['use_value_card']['1'] = '启用';

$_LANG['cfg_name']['add_shop_price'] = '商品SKU价格模式';
$_LANG['cfg_range']['add_shop_price']['0'] = 'SKU价格（属性货品价格）';
$_LANG['cfg_range']['add_shop_price']['1'] = 'SKU价格（商品价格 + 属性货品价格）';

$_LANG['cfg_name']['goods_attr_price'] = '商品属性价格模式';
$_LANG['cfg_range']['goods_attr_price']['0'] = '单一模式';
$_LANG['cfg_range']['goods_attr_price']['1'] = '货品模式';
$_LANG['cfg_desc']['goods_attr_price'] = '商品属性价格分两种模式，单一模式是系统原先单个属性设置价格，货品模式是以货品属性一组设置价格';

$_LANG['cfg_name']['user_login_register'] = '会员登录/注册邮箱验证是否开启';
$_LANG['cfg_range']['user_login_register']['0'] = '关闭';
$_LANG['cfg_range']['user_login_register']['1'] = '开启';
$_LANG['cfg_desc']['user_login_register'] = '如果开启邮箱验证，用户在使用邮箱注册会员成功后必须邮箱验证才能进入会员中心';

$_LANG['cfg_name']['user_phone'] = '会员账号安全验证级别';
$_LANG['cfg_range']['user_phone']['0'] = '邮箱验证';
$_LANG['cfg_range']['user_phone']['1'] = '手机验证';
$_LANG['cfg_desc']['user_phone'] = '如果开启手机验证并关闭《会员登录/注册邮箱验证是否开启》情况下，以手机验证为最高级别';

$_LANG['cfg_name']['server_model'] = '服务器运行方式';
$_LANG['cfg_range']['server_model']['0'] = '单台服务器';
$_LANG['cfg_range']['server_model']['1'] = '多台服务器负载均衡';
$_LANG['cfg_desc']['server_model'] = '如果商城没有采用负载均衡方式运行(比如阿里云的负载均衡)，请设置单台服务器方式';

$_LANG['cfg_name']['exchange_size'] = '积分商品列表页的数量';

$_LANG['cfg_name']['site_domain'] = '网站域名';
$_LANG['cfg_desc']['site_domain'] = '请输入您当前网站的域名，避免启用商家二级域名出现问题（如：http://www.xxxx.com/）';

//by li start
$_LANG['cfg_name']['sms_price_notice'] = '商品降价时是否发送短信';
$_LANG['cfg_range']['sms_price_notice']['1'] = '发短信';
$_LANG['cfg_range']['sms_price_notice']['0'] = '不发短信';
$_LANG['cfg_name']['sms_ecmoban_user'] = '短信接口用户名';
$_LANG['cfg_name']['sms_ecmoban_password'] = '短信接口密码';
//by li end

//退换货 start
$_LANG['cfg_name']['sign'] = '发货日期起可退换货时间';
$_LANG['cfg_desc']['sign'] = '以天数为单位';
//退换货 end

//ecmoban模板堂 --zhuo start

$_LANG['cfg_name']['sms_type'] = '短信类型';
$_LANG['cfg_range']['sms_type']['0'] = '互亿无线';
$_LANG['cfg_range']['sms_type']['1'] = '阿里大于';
$_LANG['cfg_range']['sms_type']['2'] = '阿里云短信';
$_LANG['cfg_range']['sms_type']['3'] = "模板堂短信 <font class='red'>[推荐]</font>";
$_LANG['cfg_range']['sms_type']['4'] = '华为云短信';
$_LANG['cfg_range']['sms_type']['5'] = '创蓝253云短信';

$_LANG['cfg_name']['ali_appkey'] = '阿里大鱼(appKey)';
$_LANG['cfg_name']['ali_secretkey'] = '阿里大鱼(secretKey)';

$_LANG['cfg_name']['access_key_id'] = 'AccessKeyID';
$_LANG['cfg_name']['access_key_secret'] = 'AccessKeySecret';

$_LANG['cfg_name']['dsc_appkey'] = '模板堂(appKey)';
$_LANG['cfg_name']['dsc_appsecret'] = '模板堂(appSecret)';

$_LANG['cfg_name']['huawei_sms_key'] = '华为云(appKey)';
$_LANG['cfg_name']['huawei_sms_secret'] = '华为云(appSecret)';

$_LANG['cfg_name']['chuanglan_account'] = '创蓝253用户名';
$_LANG['cfg_name']['chuanglan_password'] = '创蓝253密码';
$_LANG['cfg_name']['chuanglan_api_url'] = '创蓝253发送地址';
$_LANG['cfg_name']['chuanglan_signa'] = '创蓝253签名';

$_LANG['cfg_name']['sms_find_signin'] = '客户密码找回时';
$_LANG['cfg_range']['sms_find_signin']['0'] = '不发短信';
$_LANG['cfg_range']['sms_find_signin']['1'] = '发短信';

$_LANG['cfg_name']['sms_code'] = '发送验证码';
$_LANG['cfg_range']['sms_code']['0'] = '不发短信';
$_LANG['cfg_range']['sms_code']['1'] = '发短信';
$_LANG['cfg_desc']['sms_code'] = '用于单个参数验证码（用户实名验证、商家实名验证等）短信发送';

$_LANG['cfg_name']['sms_validity'] = '短信验证码有效期';
$_LANG['cfg_desc']['sms_validity'] = '设置短信内验证码有效期，单位为：分钟；超过有效期验证码无法使用，留空则默认10分钟';

$_LANG['cfg_name']['user_account_code'] = '会员充值/提现时是否发送短信';
$_LANG['cfg_range']['user_account_code']['0'] = '不发短信';
$_LANG['cfg_range']['user_account_code']['1'] = '发短信';

$_LANG['cfg_name']['open_oss'] = '是否开启云存储';
$_LANG['cfg_range']['open_oss']['0'] = '关闭';
$_LANG['cfg_range']['open_oss']['1'] = '开启';

$_LANG['cfg_name']['open_memcached'] = 'Memcached是否启用';
$_LANG['cfg_range']['open_memcached']['0'] = '关闭';
$_LANG['cfg_range']['open_memcached']['1'] = '开启';
$_LANG['cfg_desc']['open_memcached'] = '温馨提示：启用前必须确认系统服务器Memcached服务一切正常';

$_LANG['cfg_name']['ad_reminder'] = '广告位提示设置';
$_LANG['cfg_range']['ad_reminder']['0'] = '关闭';
$_LANG['cfg_range']['ad_reminder']['1'] = '开启';
$_LANG['cfg_desc']['ad_reminder'] = '启用后可以查看页面广告位是否设置广告';

$_LANG['cfg_name']['open_study'] = '查看教程开关设置';
$_LANG['cfg_range']['open_study']['0'] = '关闭';
$_LANG['cfg_range']['open_study']['1'] = '开启';
$_LANG['cfg_desc']['open_study'] = '启用后可以查看官网教程';

$_LANG['cfg_name']['show_warehouse'] = '商品详情开启可选仓库';
$_LANG['cfg_range']['show_warehouse']['0'] = '关闭';
$_LANG['cfg_range']['show_warehouse']['1'] = '开启';

$_LANG['cfg_name']['seller_email'] = '下订单时是否给入驻商客服发邮件';
$_LANG['cfg_range']['seller_email']['0'] = '关闭';
$_LANG['cfg_range']['seller_email']['1'] = '开启';
$_LANG['cfg_desc']['seller_email'] = '店铺基本信息设置的客服邮件地址不为空时，该选项有效。';

$_LANG['cfg_name']['user_helpart'] = '会员帮助文章';
$_LANG['cfg_desc']['user_helpart'] = '此设置为会员中心默认页面右侧帮助文章（格式：1,2,3 注：逗号为英文逗号）';

$_LANG['cfg_name']['single_thumb_width'] = '晒单缩略图宽度';
$_LANG['cfg_name']['single_thumb_height'] = '晒单缩略图高度';

$_LANG['cfg_name']['return_pictures'] = '退换货上传图片凭证';
$_LANG['cfg_desc']['return_pictures'] = '设置退换货上传图片凭证数量限制';

$_LANG['cfg_name']['auction_ad'] = '限制显示广告数量';

$_LANG['cfg_name']['marticle'] = '店铺入驻文章列表';
$_LANG['cfg_desc']['marticle'] = '设置店铺入驻首页左侧文章ID,文本框中填写文章分类ID';

$_LANG['cfg_name']['marticle_id'] = '店铺入驻文章默认内容';
$_LANG['cfg_desc']['marticle_id'] = '设置默认显示店铺入驻文章内容ID';

$_LANG['cfg_name']['open_delivery_time'] = '是否开启自动确认收货';
$_LANG['cfg_range']['open_delivery_time']['0'] = '关闭';
$_LANG['cfg_range']['open_delivery_time']['1'] = '开启';

$_LANG['cfg_name']['open_area_goods'] = '是否开启页面区分地区商品';
$_LANG['cfg_range']['open_area_goods']['0'] = '关闭';
$_LANG['cfg_range']['open_area_goods']['1'] = '开启';

$_LANG['cfg_name']['choose_process'] = '入驻申请必填功能';
$_LANG['cfg_range']['choose_process']['0'] = '关闭';
$_LANG['cfg_range']['choose_process']['1'] = '开启';

$_LANG['cfg_name']['freight_model'] = '商品运费模式';
$_LANG['cfg_range']['freight_model']['0'] = '系统默认';
$_LANG['cfg_range']['freight_model']['1'] = '仓库模式';
$_LANG['cfg_desc']['freight_model'] = '系统默认：按配送方式设置运费，即普通的运费模式，如发往苏州、上海等的运费设置；<br>仓库模式：使用仓库设置运费，如从上海仓库发往苏州的运费设置。';

$_LANG['cfg_name']['customer_service'] = '店铺客服设置';
$_LANG['cfg_range']['customer_service']['0'] = '默认统一客服';
$_LANG['cfg_range']['customer_service']['1'] = '商家自行客服';
$_LANG['cfg_desc']['customer_service'] = '设置客服模式是由管理员设置统一店铺客服或者是由商家自行设置店铺客服';

$_LANG['cfg_name']['review_goods'] = '审核店铺商品';
$_LANG['cfg_range']['review_goods']['0'] = '关闭';
$_LANG['cfg_range']['review_goods']['1'] = '开启';
$_LANG['cfg_desc']['review_goods'] = '设置是否需要审核商家添加的商品，如果开启则所有商家添加的商品都需要审核之后才能显示';

$_LANG['cfg_name']['group_goods'] = '商品配件组名称';
$_LANG['cfg_desc']['group_goods'] = '以英文逗号分隔字符串抒写，例：推荐配件,人气组合';

$_LANG['cfg_desc']['add_shop_price'] = '选择第二种方式的时候，设置促销活动会无效，比如分期、促销价格等；会默认按商品价格来计算促销活动';

$_LANG['cfg_name']['attr_set_up'] = '商品属性权限';
$_LANG['cfg_range']['attr_set_up'][0] = '由网站统一设置';
$_LANG['cfg_range']['attr_set_up'][1] = '商家可自行设置';
$_LANG['cfg_desc']['attr_set_up'] = '设置商品属性商家是否有权限添加';

$_LANG['cfg_name']['goods_file'] = '商品编辑审核字段';
$_LANG['cfg_desc']['goods_file'] = '设置商品编辑时需要审核的字段，空值为审核已通过，否则未审核' . "<br/>" . '默认字段：goods_name,shop_price,market_price,promote_price,goods_sn' . "<br/>" . '仓库或地区模式时：-warehouse_price,warehouse_promote_price,region_price,region_promote_price';

$_LANG['cfg_name']['delete_seller'] = '是否删除商家所有信息';
$_LANG['cfg_range']['delete_seller']['0'] = '否';
$_LANG['cfg_range']['delete_seller']['1'] = '是';
$_LANG['cfg_desc']['delete_seller'] = '在删除商家的同时删除商家的所有信息，请慎重选择.';
//ecmoban模板堂 --zhuo end

//Powered by ecmoban.com (mike) start
$_LANG['cfg_name']['editing_tools'] = '内容编辑器';
$_LANG['cfg_range']['editing_tools']['fckeditor'] = '默认Fckeditor';
$_LANG['cfg_range']['editing_tools']['ueditor'] = '百度Ueditor(推荐)';

//wang
$_LANG['cfg_name']['sms_signin'] = '客户注册时是否发送短信验证码';
$_LANG['cfg_range']['sms_signin']['1'] = '发短信';
$_LANG['cfg_range']['sms_signin']['0'] = '不发短信';
$_LANG['cfg_name']['sms_ecmoban_user'] = '互亿在线短信接口用户名';
$_LANG['cfg_name']['sms_ecmoban_password'] = '互亿在线短信接口密码';
//Powered by ecmoban.com (mike) end

$_LANG['cfg_name']['sms_seller_signin'] = '修改入驻商权限时是否发送短信';
$_LANG['cfg_range']['sms_seller_signin']['0'] = '不发短信';
$_LANG['cfg_range']['sms_seller_signin']['1'] = '发短信';

$_LANG['cfg_name']['ectouch_qrcode'] = '底部二维码右边';
$_LANG['cfg_name']['ecjia_qrcode'] = '底部二维码左边';
$_LANG['cfg_name']['index_down_logo'] = '首页下滑弹出LOGO图片';
$_LANG['cfg_name']['site_commitment'] = '头部右侧翻转效果图片';
$_LANG['cfg_name']['user_login_logo'] = '用户登录与注册页面LOGO';
$_LANG['cfg_name']['login_logo_pic'] = '用户登录与注册页面LOGO右侧图';
$_LANG['cfg_name']['business_logo'] = '批发Logo';

$_LANG['cfg_name']['admin_login_logo'] = '平台后台登录页LOGO';
$_LANG['cfg_name']['kefu_login_log'] = '客服后台登录页LOGO';
$_LANG['cfg_name']['admin_logo'] = '平台后台LOGO';
$_LANG['cfg_name']['seller_login_logo'] = '商家后台登录页LOGO';
$_LANG['cfg_name']['seller_logo'] = '商家后台LOGO';
$_LANG['cfg_name']['stores_login_logo'] = '门店后台登录页LOGO';
$_LANG['cfg_name']['stores_logo'] = '门店后台LOGO';
$_LANG['cfg_name']['order_print_logo'] = '订单打印LOGO';


$_LANG['cfg_name']['basic_logo'] = 'LOGO设置';
$_LANG['cfg_name']['extend_basic'] = '扩展信息';
$_LANG['cfg_name']['basic'] = '基本设置';
$_LANG['cfg_name']['display'] = '显示设置';
$_LANG['cfg_name']['shop_info'] = '平台信息';
$_LANG['cfg_name']['shopping_flow'] = '购物流程';
$_LANG['cfg_name']['pay'] = '支付模块';
$_LANG['cfg_name']['copyright_set'] = '版权设置';
$_LANG['cfg_name']['download'] = '下载页设置';
$_LANG['cfg_name']['system_customer_service'] = '客服设置';
$_LANG['cfg_name']['return'] = '退款设置';
$_LANG['cfg_name']['pc_config'] = '基本设置';
$_LANG['cfg_name']['pc_goods_config'] = '商品设置';
$_LANG['cfg_name']['show_copyright'] = '版权显示';
$_LANG['cfg_name']['copyright_text'] = '底部版权文字';
$_LANG['cfg_name']['copyright_text_mobile'] = '移动端底部版权文字';
$_LANG['cfg_name']['copyright_link'] = '底部版权链接';
$_LANG['cfg_name']['copyright_img'] = '底部版权图标';
$_LANG['cfg_name']['smtp'] = '邮件服务器设置';
$_LANG['cfg_name']['goods'] = '商品显示设置';
$_LANG['cfg_name']['lang'] = '系统语言';
$_LANG['cfg_name']['shop_closed'] = '暂时关闭网站';
$_LANG['cfg_name']['icp_file'] = 'ICP 备案证书文件';
$_LANG['cfg_name']['watermark'] = '水印文件';
$_LANG['cfg_name']['watermark_place'] = '水印位置';
$_LANG['cfg_name']['use_storage'] = '是否启用库存管理';
$_LANG['cfg_name']['market_price_rate'] = '市场价格比例';
$_LANG['cfg_name']['rewrite'] = 'URL重写';
$_LANG['cfg_name']['integral_name'] = '消费积分名称';
$_LANG['cfg_name']['integral_scale'] = '积分换算比例';
$_LANG['cfg_name']['integral_percent'] = '积分支付比例';
$_LANG['cfg_name']['enable_order_check'] = '是否开启新订单提醒';
$_LANG['cfg_name']['default_storage'] = '默认库存';
$_LANG['cfg_name']['date_format'] = '日期格式';
$_LANG['cfg_name']['time_format'] = '时间格式';
$_LANG['cfg_name']['currency_format'] = '货币格式';

$_LANG['cfg_name']['is_show_currency_format'] = '是否显示货币格式';
$_LANG['cfg_range']['is_show_currency_format']['0'] = '否';
$_LANG['cfg_range']['is_show_currency_format']['1'] = '是';
$_LANG['cfg_desc']['is_show_currency_format'] = '设置前端页面金额货币格式是否显示，为否时前端金额不显示货币格式';
$_LANG['cfg_name']['price_style'] = '金额显示样式';
$_LANG['cfg_range']['price_style']['1'] = '样式一';
$_LANG['cfg_range']['price_style']['2'] = '样式二';
$_LANG['cfg_range']['price_style']['3'] = '样式三';
$_LANG['cfg_range']['price_style']['4'] = '样式四';
$_LANG['cfg_desc']['price_style'] = '可设置前端页面金额显示样式，设置后所选金额显示样式应用于前端所有金额显示位置';
$_LANG['cfg_desc']['price_format'] = '设置后，商城后台添加或编辑商品时，商品价格即按照此设置项填写。如设置不支持小数，则商品价格仅支持填写整数';

// 起始页客户服务显示设置项
$_LANG['cfg_name']['enable_customer_service'] = '起始页客户服务显示';
$_LANG['cfg_range']['enable_customer_service']['0'] = '关闭';
$_LANG['cfg_range']['enable_customer_service']['1'] = '开启';
$_LANG['cfg_desc']['enable_customer_service'] = '平台后台起始页客户服务模块显示控制项，默认开启，关闭后客户服务模块不显示';

$_LANG['cfg_name']['thumb_width'] = '缩略图宽度';
$_LANG['cfg_name']['thumb_height'] = '缩略图高度';
$_LANG['cfg_name']['image_width'] = '商品图片宽度';
$_LANG['cfg_name']['image_height'] = '商品图片高度';
$_LANG['cfg_name']['best_number'] = '精品推荐数量';
$_LANG['cfg_name']['new_number'] = '新品推荐数量';
$_LANG['cfg_name']['hot_number'] = '热销商品数量';
$_LANG['cfg_name']['promote_number'] = '特价商品的数量';
$_LANG['cfg_name']['group_goods_number'] = '团购商品的数量';
$_LANG['cfg_name']['top_number'] = '销量排行数量';
$_LANG['cfg_name']['history_number'] = '浏览历史数量';
$_LANG['cfg_name']['comments_number'] = '评论数量';
$_LANG['cfg_name']['bought_goods'] = '相关商品数量';
$_LANG['cfg_name']['article_number'] = '最新文章显示数量';
$_LANG['cfg_name']['order_number'] = '订单显示数量';
$_LANG['cfg_name']['shop_name'] = '商店名称';
$_LANG['cfg_name']['shop_title'] = '商店标题';
$_LANG['cfg_name']['shop_website'] = '商店网址';
$_LANG['cfg_name']['shop_desc'] = '商店描述';
$_LANG['cfg_name']['shop_keywords'] = '商店关键字';
$_LANG['cfg_name']['shop_country'] = '所在国家';
$_LANG['cfg_name']['shop_province'] = '所在省份';
$_LANG['cfg_name']['shop_city'] = '所在城市';
$_LANG['cfg_name']['shop_district'] = '所在区域';
$_LANG['cfg_name']['shop_address'] = '详细地址';
$_LANG['cfg_name']['licensed'] = '是否显示 Licensed';
$_LANG['cfg_name']['customer_service_type'] = '在线客服';
$_LANG['cfg_name']['qq'] = 'QQ客服号码';
$_LANG['cfg_name']['qq_name'] = 'QQ客服名称';
$_LANG['cfg_name']['ww'] = '淘宝旺旺';
$_LANG['cfg_name']['service_email'] = '客服邮件地址';
$_LANG['cfg_name']['service_url'] = '自定义客服链接';
$_LANG['cfg_name']['service_phone'] = '客服电话';
$_LANG['cfg_name']['can_invoice'] = '能否开发票';
$_LANG['cfg_name']['user_notice'] = '用户中心公告';
$_LANG['cfg_name']['shop_notice'] = '商店公告';
$_LANG['cfg_name']['shop_reg_closed'] = '是否关闭注册';
$_LANG['cfg_name']['send_mail_on'] = '是否开启自动发送邮件';
$_LANG['cfg_name']['auto_generate_gallery'] = '上传商品是否自动生成相册图';
$_LANG['cfg_name']['retain_original_img'] = '上传商品时是否保留原图';
$_LANG['cfg_name']['member_email_validate'] = '是否开启会员邮件验证';
$_LANG['cfg_name']['send_verify_email'] = '用户注册时自动发送验证邮件';
$_LANG['cfg_name']['message_board'] = '是否启用留言板功能';
$_LANG['cfg_name']['message_check'] = '用户留言是否需要审核';
//$_LANG['cfg_name']['use_package'] = '是否使用包装';
//$_LANG['cfg_name']['use_card'] = '是否使用贺卡';
$_LANG['cfg_name']['use_integral'] = '是否使用积分';
$_LANG['cfg_name']['use_bonus'] = '是否使用红包';
$_LANG['cfg_name']['use_value_card'] = '是否使用储值卡';
$_LANG['cfg_name']['use_surplus'] = '是否使用余额';
$_LANG['cfg_name']['use_how_oos'] = '是否使用缺货处理';
$_LANG['cfg_name']['use_paypwd'] = '是否验证支付密码';
$_LANG['cfg_name']['use_pay_fee'] = '是否启用支付手续费';
$_LANG['cfg_name']['send_confirm_email'] = '确认订单时';
$_LANG['cfg_name']['order_pay_note'] = '设置订单为“已付款”时';
$_LANG['cfg_name']['order_unpay_note'] = '设置订单为“未付款”时';
$_LANG['cfg_name']['order_ship_note'] = '设置订单为“已发货”时';
$_LANG['cfg_name']['order_unship_note'] = '设置订单为“未发货”时';
$_LANG['cfg_name']['when_dec_storage'] = '什么时候减少库存';
$_LANG['cfg_name']['send_ship_email'] = '发货时';
$_LANG['cfg_name']['order_receive_note'] = '设置订单为“收货确认”时';
$_LANG['cfg_name']['order_cancel_note'] = '取消订单时';
$_LANG['cfg_name']['send_cancel_email'] = '取消订单时';
$_LANG['cfg_name']['order_return_note'] = '退货时';
$_LANG['cfg_name']['order_invalid_note'] = '把订单设为无效时';
$_LANG['cfg_name']['send_invalid_email'] = '把订单设为无效时';
$_LANG['cfg_name']['sn_prefix'] = '商品货号前缀';
$_LANG['cfg_name']['close_comment'] = '关闭网店的原因';
$_LANG['cfg_name']['watermark_alpha'] = '水印透明度';
$_LANG['cfg_name']['icp_number'] = 'ICP证书或ICP备案证书号';
$_LANG['cfg_name']['invoice_content'] = '发票内容';
$_LANG['cfg_name']['invoice_type'] = '发票类型及税率';
$_LANG['cfg_name']['stock_dec_time'] = '减库存的时机';
$_LANG['cfg_name']['sales_volume_time'] = '增加销量的时机';
$_LANG['cfg_name']['cross_border_article_id'] = '结算页告知文章栏目';
$_LANG['cfg_name']['comment_check'] = '用户评论是否需要审核';
$_LANG['cfg_name']['comment_factor'] = '商品评论的条件';
$_LANG['cfg_name']['no_picture'] = '商品的默认图片';
$_LANG['cfg_name']['no_brand'] = '品牌的默认图片';
$_LANG['cfg_name']['stats_code'] = '统计代码';
$_LANG['cfg_name']['cache_time'] = '缓存存活时间（秒）';
$_LANG['cfg_name']['page_size'] = '商品分类页列表的数量';
$_LANG['cfg_name']['article_page_size'] = '文章分类页列表的数量';
$_LANG['cfg_name']['page_style'] = '分页样式';
$_LANG['cfg_name']['sort_order_type'] = '商品分类页默认排序类型';
$_LANG['cfg_name']['sort_order_method'] = '商品分类页默认排序方式';
$_LANG['cfg_name']['show_order_type'] = '商品分类页默认显示方式';
$_LANG['cfg_name']['goods_name_length'] = '商品名称的长度';
$_LANG['cfg_name']['price_format'] = '商品金额输入';
$_LANG['cfg_name']['register_points'] = '会员注册赠送积分';
$_LANG['cfg_name']['shop_logo'] = '商店 Logo';
$_LANG['cfg_name']['anonymous_buy'] = '是否允许未登录用户购物';
$_LANG['cfg_name']['min_goods_amount'] = '最小购物金额';
$_LANG['cfg_name']['one_step_buy'] = '是否一步购物';
$_LANG['cfg_name']['show_goodssn'] = '是否显示货号';
$_LANG['cfg_name']['show_brand'] = '是否显示品牌';
$_LANG['cfg_name']['show_goodsweight'] = '是否显示重量';
$_LANG['cfg_name']['show_goodsnumber'] = '是否显示库存';
$_LANG['cfg_name']['show_addtime'] = '是否显示上架时间';
$_LANG['cfg_name']['show_rank_price'] = '是否显示等级价格';
$_LANG['cfg_name']['show_give_integral'] = '是否赠送消费积分';
$_LANG['cfg_name']['show_marketprice'] = '是否显示市场价格';
$_LANG['cfg_name']['goodsattr_style'] = '商品属性显示样式';
$_LANG['cfg_name']['test_mail_address'] = '邮件地址';
$_LANG['cfg_name']['send'] = '发送测试邮件';
$_LANG['cfg_name']['send_service_email'] = '下订单时是否给客服发邮件';
$_LANG['cfg_name']['show_goods_in_cart'] = '购物车里显示商品方式';
$_LANG['cfg_name']['show_attr_in_cart'] = '购物车里是否显示商品属性';
$_LANG['test_mail_title'] = '测试邮件';
$_LANG['cfg_name']['email_content'] = '您好！这是一封检测邮件服务器设置的测试邮件。收到此邮件，意味着您的邮件服务器设置正确！您可以进行其它邮件发送的操作了！';
$_LANG['cfg_name']['sms'] = '短信设置';
$_LANG['cfg_name']['sms_shop_mobile'] = '商家的手机号码';
$_LANG['cfg_name']['sms_order_placed'] = '客户下订单时是否给商家发短信';
$_LANG['cfg_name']['sms_change_user_money'] = '用户余额改变时是否给用户发短信';
$_LANG['cfg_name']['sms_order_payed'] = '客户付款时是否给商家发短信';
$_LANG['cfg_name']['sms_order_shipped'] = '商家发货时是否给客户发短信';
$_LANG['cfg_name']['sms_order_received'] = '客户确认收货时是否给商家发短信';
$_LANG['cfg_name']['sms_shop_order_received'] = '商家确认收货时是否给买家发短信';
$_LANG['cfg_name']['attr_related_number'] = '属性关联的商品数量';
$_LANG['cfg_name']['top10_time'] = '排行统计的时间';
$_LANG['cfg_name']['goods_gallery_number'] = '商品详情页相册图片数量';
$_LANG['cfg_name']['article_title_length'] = '文章标题的长度';
$_LANG['cfg_name']['timezone'] = '默认时区';
$_LANG['cfg_name']['upload_size_limit'] = '附件上传大小';
$_LANG['cfg_name']['search_keywords'] = '首页搜索的关键字';
$_LANG['cfg_name']['cart_confirm'] = '购物车确定提示';
$_LANG['cfg_name']['bgcolor'] = '缩略图背景色';
$_LANG['cfg_name']['name_of_region_1'] = '一级配送区域名称';
$_LANG['cfg_name']['name_of_region_2'] = '二级配送区域名称';
$_LANG['cfg_name']['name_of_region_3'] = '三级配送区域名称';
$_LANG['cfg_name']['name_of_region_4'] = '四级配送区域名称';
$_LANG['cfg_name']['related_goods_number'] = '关联商品显示数量';
$_LANG['cfg_name']['visit_stats'] = '站点访问统计';
$_LANG['cfg_name']['help_open'] = '用户帮助是否打开';
$_LANG['cfg_name']['privacy'] = '隐私声明';

$_LANG['cfg_desc']['invoice_type'] = '税率填写整数值，例如（税率：12，商品金额：35.70）则公式：35.70 * （12 / 100） = 4.28元';
$_LANG['cfg_desc']['smtp'] = '设置邮件服务器基本参数';
$_LANG['cfg_desc']['market_price_rate'] = '输入商品售价时将自动根据该比例计算市场价格';
$_LANG['cfg_desc']['rewrite'] = 'URL重写是一种搜索引擎优化技术，可以将动态的地址模拟成静态的HTML文件。';
$_LANG['cfg_desc']['integral_name'] = '您可以将消费积分重新命名。例如：商豆';
$_LANG['cfg_desc']['integral_scale'] = '每100积分可抵多少现金';
$_LANG['cfg_desc']['integral_percent'] = '每100元商品最多可以使用多少元积分';
$_LANG['cfg_desc']['comments_number'] = '显示在商品详情页的用户评论数量。';
$_LANG['cfg_desc']['shop_title'] = '商店的标题，将显示在浏览器的标题栏Title';
$_LANG['cfg_desc']['shop_desc'] = '商店描述内容，将显示在浏览器的Description';
$_LANG['cfg_desc']['shop_keywords'] = '商店的关键字，将显示在浏览器的Keywords';
$_LANG['cfg_desc']['shop_address'] = '商店详细地址，不显示在前台';
$_LANG['cfg_desc']['smtp_host'] = '邮件服务器主机地址。如果本机可以发送邮件则设置为localhost';
$_LANG['cfg_desc']['smtp_user'] = '发送邮件所需的认证帐号，如果没有就为空着';
$_LANG['cfg_desc']['bought_goods'] = '显示多少个购买此商品的人还买过哪些商品';
$_LANG['cfg_desc']['currency_format'] = '填写后商城所有金额显示部位均显示此货币格式，默认为￥';
$_LANG['cfg_desc']['image_height'] = '如果您的服务器支持GD，在您上传商品图片的时候将自动将图片缩小到指定的尺寸。';
$_LANG['cfg_desc']['watermark'] = '水印文件须为gif格式才可支持透明度设置。';
$_LANG['cfg_desc']['watermark_place'] = '水印显示在图片上的位置，"无"将不显示水印。';
$_LANG['cfg_desc']['watermark_alpha'] = '水印的透明度，可选值为0-100。当设置为100时则为不透明。';
$_LANG['cfg_desc']['invoice_content'] = '客户要求开发票时可以选择的内容。例如：办公用品。每一行代表一个选项。';
$_LANG['cfg_desc']['stats_code'] = '您可以将其他访问统计服务商提供的代码添加到每一个页面。';
$_LANG['cfg_desc']['cache_time'] = '前台页面缓存的存活时间，以秒为单位。';
$_LANG['cfg_desc']['service_email'] = '商店客服邮箱地址，接收发送的信息';
$_LANG['cfg_desc']['service_url'] = '客服自定义链接，填写后点击客服将调用此链接地址';
$_LANG['cfg_desc']['service_phone'] = '商店客服电话，联系平台电话，例：入驻页面导航显示的电话';
$_LANG['cfg_desc']['customer_service_type'] = '商城启用的客服模式选择';
$_LANG['cfg_desc']['qq_name'] = '显示于前端前端的QQ客服昵称';
$_LANG['cfg_desc']['ww'] = '旺旺客服名称和号码请用“|”隔开（如：客服2|654321），如果您有多个客服的旺旺号码，请换行。';
$_LANG['cfg_desc']['shop_logo'] = '上传图片格式必须是gif,jpg,jpeg,png;图片大小在200kb之内，建议尺寸：159*100';
$_LANG['cfg_desc']['business_logo'] = '上传图片格式必须是gif,jpg,jpeg,png;图片大小在200kb之内';
$_LANG['cfg_desc']['attr_related_number'] = '在商品详情页面显示多少个属性关联的商品。';
$_LANG['cfg_desc']['user_notice'] = '该信息将在用户中心欢迎页面显示';
$_LANG['cfg_desc']['comment_factor'] = '选取较高的评论条件可以有效的减少垃圾评论的产生。只有用户订单完成后才认为该用户有购买行为';
$_LANG['cfg_desc']['min_goods_amount'] = '达到此购物金额，才能提交订单。';
$_LANG['cfg_desc']['search_keywords'] = '首页显示的搜索关键字,请用半角逗号(,)分隔多个关键字';
$_LANG['cfg_desc']['shop_notice'] = '以上内容将显示在首页商店公告中,注意控制公告内容长度不要超过公告显示区域大小。';
$_LANG['cfg_desc']['bgcolor'] = '颜色请以#FFFFFF格式填写';
$_LANG['cfg_desc']['cart_confirm'] = '允许您设置用户点击“加入购物车”后是否提示以及随后的动作。';
$_LANG['cfg_desc']['cross_border_article_id'] = '仅跨境订单结算页显示，请填写文章ID，多个文章使用半角逗号（,）分隔';
$_LANG['cfg_desc']['use_how_oos'] = '使用缺货处理时前台订单确认页面允许用户选择缺货时处理方法。';
$_LANG['cfg_desc']['send_service_email'] = '网店信息中的客服邮件地址不为空时，该选项有效。';
$_LANG['cfg_desc']['send_mail_on'] = '启用该选项时，会自动发送邮件队列中尚未发送的邮件（须开启计划任务）';
$_LANG['cfg_desc']['sms_shop_mobile'] = '请先注册手机短信服务再填写手机号码';
$_LANG['cfg_desc']['send_verify_email'] = '“是否开启会员邮件验证”设为开启时才可使用此功能';

$_LANG['cfg_desc']['shop_closed'] = '商店需升级或者其他原因临时关闭网站';
$_LANG['cfg_desc']['close_comment'] = '商店临时关闭网站说明原因';
$_LANG['cfg_desc']['shop_reg_closed'] = '商店是否关闭前台会员注册';
$_LANG['cfg_desc']['privacy'] = '隐私声明使用的文章id';

$_LANG['cfg_desc']['lang'] = '商店系统使用的语言，zh-CN 是简体中文，zh-TW 是繁体中文，en 是英文';
$_LANG['cfg_desc']['icp_number'] = 'ICP证书号，显示在网站前台底部';
$_LANG['cfg_desc']['register_points'] = '前台会员注册赠送的系统积分数';

//$_LANG['cfg_range']['cart_confirm'][1] = '提示用户，点击“确定”进购物车';
//$_LANG['cfg_range']['cart_confirm'][2] = '提示用户，点击“取消”进购物车';
//$_LANG['cfg_range']['cart_confirm'][3] = '直接进入购物车';
$_LANG['cfg_range']['show_copyright']['1'] = '是';
$_LANG['cfg_range']['show_copyright']['0'] = '否';
$_LANG['cfg_desc']['show_copyright'] = '可设置网站底部是否显示版权信息';
$_LANG['cfg_range']['cart_confirm'][4] = '不提示并停留在当前页面';
$_LANG['cfg_range']['shop_closed']['0'] = '否';
$_LANG['cfg_range']['shop_closed']['1'] = '是';
$_LANG['cfg_range']['licensed']['0'] = '否';
$_LANG['cfg_range']['licensed']['1'] = '是';
$_LANG['cfg_range']['send_mail_on']['on'] = '开启';
$_LANG['cfg_range']['send_mail_on']['off'] = '关闭';
$_LANG['cfg_range']['member_email_validate']['1'] = '开启';
$_LANG['cfg_range']['member_email_validate']['0'] = '关闭';
$_LANG['cfg_range']['send_verify_email']['1'] = '开启';
$_LANG['cfg_range']['send_verify_email']['0'] = '关闭';
$_LANG['cfg_range']['message_board']['1'] = '开启';
$_LANG['cfg_range']['message_board']['0'] = '关闭';
$_LANG['cfg_range']['auto_generate_gallery']['1'] = '是';
$_LANG['cfg_range']['auto_generate_gallery']['0'] = '否';
$_LANG['cfg_range']['retain_original_img']['1'] = '是';
$_LANG['cfg_range']['retain_original_img']['0'] = '否';
$_LANG['cfg_range']['watermark_place']['0'] = '无';
$_LANG['cfg_range']['watermark_place']['1'] = '左上';
$_LANG['cfg_range']['watermark_place']['2'] = '右上';
$_LANG['cfg_range']['watermark_place']['3'] = '居中';
$_LANG['cfg_range']['watermark_place']['4'] = '左下';
$_LANG['cfg_range']['watermark_place']['5'] = '右下';
$_LANG['cfg_range']['use_storage']['1'] = '是';
$_LANG['cfg_range']['use_storage']['0'] = '否';
$_LANG['cfg_range']['rewrite']['0'] = '禁用';
$_LANG['cfg_range']['rewrite']['1'] = '简单重写';
$_LANG['cfg_range']['rewrite']['2'] = '复杂重写';
$_LANG['cfg_range']['can_invoice']['0'] = '不能';
$_LANG['cfg_range']['can_invoice']['1'] = '能';
$_LANG['cfg_range']['top10_time']['0'] = '所有';
$_LANG['cfg_range']['top10_time']['1'] = '一年';
$_LANG['cfg_range']['top10_time']['2'] = '半年';
$_LANG['cfg_range']['top10_time']['3'] = '三个月';
$_LANG['cfg_range']['top10_time']['4'] = '一个月';
$_LANG['cfg_range']['use_integral']['1'] = '使用';
$_LANG['cfg_range']['use_integral']['0'] = '不使用';
$_LANG['cfg_range']['use_bonus']['0'] = '不使用';
$_LANG['cfg_range']['use_bonus']['1'] = '使用';
$_LANG['cfg_range']['use_value_card']['1'] = '使用';
$_LANG['cfg_range']['use_value_card']['0'] = '不使用';
$_LANG['cfg_range']['use_paypwd']['1'] = '是';
$_LANG['cfg_range']['use_paypwd']['0'] = '否';
$_LANG['cfg_desc']['use_paypwd'] = '启用验证支付密码，则购物下单时必须验证会员支付密码，增强支付安全';
$_LANG['cfg_range']['use_pay_fee']['1'] = '是';
$_LANG['cfg_range']['use_pay_fee']['0'] = '否';
$_LANG['cfg_desc']['use_pay_fee'] = '启用支付手续费，则购物下单时按支付方式设置，支付相应手续费';
$_LANG['cfg_range']['use_surplus']['1'] = '使用';
$_LANG['cfg_range']['use_surplus']['0'] = '不使用';
$_LANG['cfg_range']['use_how_oos']['1'] = '使用';
$_LANG['cfg_range']['use_how_oos']['0'] = '不使用';
$_LANG['cfg_range']['send_confirm_email']['1'] = '发送邮件';
$_LANG['cfg_range']['send_confirm_email']['0'] = '不发送邮件';
$_LANG['cfg_range']['order_pay_note']['1'] = '必须填写备注';
$_LANG['cfg_range']['order_pay_note']['0'] = '无需填写备注';
$_LANG['cfg_range']['order_unpay_note']['1'] = '必须填写备注';
$_LANG['cfg_range']['order_unpay_note']['0'] = '无需填写备注';
$_LANG['cfg_range']['order_ship_note']['1'] = '必须填写备注';
$_LANG['cfg_range']['order_ship_note']['0'] = '无需填写备注';
$_LANG['cfg_range']['order_unship_note']['1'] = '必须填写备注';
$_LANG['cfg_range']['order_unship_note']['0'] = '无需填写备注';
$_LANG['cfg_range']['order_receive_note']['1'] = '必须填写备注';
$_LANG['cfg_range']['order_receive_note']['0'] = '无需填写备注';
$_LANG['cfg_range']['order_cancel_note']['1'] = '必须填写备注';
$_LANG['cfg_range']['order_cancel_note']['0'] = '无需填写备注';
$_LANG['cfg_range']['order_return_note']['1'] = '必须填写备注';
$_LANG['cfg_range']['order_return_note']['0'] = '无需填写备注';
$_LANG['cfg_range']['order_invalid_note']['1'] = '必须填写备注';
$_LANG['cfg_range']['order_invalid_note']['0'] = '无需填写备注';
$_LANG['cfg_range']['when_dec_storage']['0'] = '下定单时';
$_LANG['cfg_range']['when_dec_storage']['1'] = '发货时';
$_LANG['cfg_range']['send_ship_email']['1'] = '发送邮件';
$_LANG['cfg_range']['send_ship_email']['0'] = '不发送邮件';
$_LANG['cfg_range']['send_cancel_email']['1'] = '发送邮件';
$_LANG['cfg_range']['send_cancel_email']['0'] = '不发送邮件';
$_LANG['cfg_range']['send_invalid_email']['1'] = '发送邮件';
$_LANG['cfg_range']['send_invalid_email']['0'] = '不发送邮件';
$_LANG['cfg_range']['mail_charset']['UTF8'] = '国际化编码（utf8）';
$_LANG['cfg_range']['mail_charset']['GB2312'] = '简体中文';
$_LANG['cfg_range']['mail_charset']['BIG5'] = '繁体中文';
$_LANG['cfg_range']['comment_check']['0'] = '不需要审核';
$_LANG['cfg_range']['comment_check']['1'] = '需要审核';
$_LANG['cfg_range']['message_check']['0'] = '不需要审核';
$_LANG['cfg_range']['message_check']['1'] = '需要审核';
$_LANG['cfg_range']['comment_factor']['0'] = '所有用户';
$_LANG['cfg_range']['comment_factor']['1'] = '仅登录用户';
$_LANG['cfg_range']['comment_factor']['2'] = '有过一次以上购买行为用户';
$_LANG['cfg_range']['comment_factor']['3'] = '仅购买过该商品用户';
//$_LANG['cfg_range']['price_format']['0'] = '不处理';
//$_LANG['cfg_range']['price_format']['1'] = '保留不为 0 的尾数';
//$_LANG['cfg_range']['price_format']['2'] = '不四舍五入，保留一位小数';
//$_LANG['cfg_range']['price_format']['3'] = '不四舍五入，不保留小数';
//$_LANG['cfg_range']['price_format']['4'] = '先四舍五入，保留一位小数';
//$_LANG['cfg_range']['price_format']['5'] = '先四舍五入，不保留小数 ';
$_LANG['cfg_range']['price_format']['3'] = '不支持小数';
$_LANG['cfg_range']['price_format']['2'] = '支持1位小数';
$_LANG['cfg_range']['price_format']['0'] = '支持2位小数';
$_LANG['cfg_range']['sort_order_type']['0'] = '按上架时间';
$_LANG['cfg_range']['sort_order_type']['1'] = '按商品价格';
$_LANG['cfg_range']['sort_order_type']['2'] = '按最后更新时间';
$_LANG['cfg_range']['sort_order_method']['0'] = '降序排列';
$_LANG['cfg_range']['sort_order_method']['1'] = '升序排列';
$_LANG['cfg_range']['show_order_type'][0] = '列表显示';
$_LANG['cfg_range']['show_order_type'][1] = '表格显示';
$_LANG['cfg_range']['show_order_type'][2] = '文本显示';
$_LANG['cfg_range']['help_open'][0] = '关闭';
$_LANG['cfg_range']['help_open'][1] = '打开';
$_LANG['cfg_range']['page_style'][0] = '默认经典';
$_LANG['cfg_range']['page_style'][1] = '流行页码';

$_LANG['cfg_range']['anonymous_buy']['0'] = '不允许';
$_LANG['cfg_range']['anonymous_buy']['1'] = '允许';
$_LANG['cfg_range']['one_step_buy']['0'] = '否';
$_LANG['cfg_range']['one_step_buy']['1'] = '是';
$_LANG['cfg_range']['show_goodssn']['1'] = '显示';
$_LANG['cfg_range']['show_goodssn']['0'] = '不显示';
$_LANG['cfg_range']['show_brand']['1'] = '显示';
$_LANG['cfg_range']['show_brand']['0'] = '不显示';
$_LANG['cfg_range']['show_goodsweight']['1'] = '显示';
$_LANG['cfg_range']['show_goodsweight']['0'] = '不显示';
$_LANG['cfg_range']['show_goodsnumber']['1'] = '显示';
$_LANG['cfg_range']['show_goodsnumber']['0'] = '不显示';
$_LANG['cfg_range']['show_addtime']['1'] = '显示';
$_LANG['cfg_range']['show_addtime']['0'] = '不显示';
$_LANG['cfg_range']['show_rank_price']['1'] = '显示';
$_LANG['cfg_range']['show_rank_price']['0'] = '不显示';

$_LANG['cfg_range']['show_give_integral']['1'] = '显示';
$_LANG['cfg_range']['show_give_integral']['0'] = '不显示';

$_LANG['cfg_range']['goodsattr_style']['1'] = '单选按钮';
$_LANG['cfg_range']['goodsattr_style']['0'] = '下拉列表';
$_LANG['cfg_range']['show_marketprice']['1'] = '显示';
$_LANG['cfg_range']['show_marketprice']['0'] = '不显示';
$_LANG['cfg_range']['sms_order_placed']['1'] = '发短信';
$_LANG['cfg_range']['sms_order_placed']['0'] = '不发短信';
$_LANG['cfg_range']['sms_change_user_money']['1'] = '发短信';
$_LANG['cfg_range']['sms_change_user_money']['0'] = '不发短信';
$_LANG['cfg_range']['sms_order_payed']['1'] = '发短信';
$_LANG['cfg_range']['sms_order_payed']['0'] = '不发短信';
$_LANG['cfg_range']['sms_order_shipped']['1'] = '发短信';
$_LANG['cfg_range']['sms_order_shipped']['0'] = '不发短信';
$_LANG['cfg_range']['sms_order_received']['1'] = '发短信';
$_LANG['cfg_range']['sms_order_received']['0'] = '不发短信';
$_LANG['cfg_range']['sms_shop_order_received']['1'] = '发短信';
$_LANG['cfg_range']['sms_shop_order_received']['0'] = '不发短信';
$_LANG['cfg_range']['enable_order_check']['0'] = '否';
$_LANG['cfg_range']['enable_order_check']['1'] = '是';
$_LANG['cfg_range']['enable_order_check']['0'] = '否';
$_LANG['cfg_range']['enable_order_check']['1'] = '是';
$_LANG['cfg_range']['stock_dec_time']['0'] = '发货时';
$_LANG['cfg_range']['stock_dec_time']['1'] = '下订单时';
$_LANG['cfg_range']['stock_dec_time']['2'] = '付款时';

$_LANG['cfg_range']['sales_volume_time']['0'] = '付款时';
$_LANG['cfg_range']['sales_volume_time']['1'] = '发货时';

$_LANG['cfg_range']['send_service_email']['0'] = '否';
$_LANG['cfg_range']['send_service_email']['1'] = '是';
$_LANG['cfg_range']['show_goods_in_cart']['1'] = '只显示文字';
$_LANG['cfg_range']['show_goods_in_cart']['2'] = '只显示图片';
$_LANG['cfg_range']['show_goods_in_cart']['3'] = '显示文字与图片';
$_LANG['cfg_range']['show_attr_in_cart']['0'] = '否';
$_LANG['cfg_range']['show_attr_in_cart']['1'] = '是';
$_LANG['cfg_range']['shop_reg_closed']['0'] = '否';
$_LANG['cfg_range']['shop_reg_closed']['1'] = '是';
$_LANG['cfg_range']['timezone']['-12'] = '(GMT -12:00) Eniwetok, Kwajalein';
$_LANG['cfg_range']['timezone']['-11'] = '(GMT -11:00) Midway Island, Samoa';
$_LANG['cfg_range']['timezone']['-10'] = '(GMT -10:00) Hawaii';
$_LANG['cfg_range']['timezone']['-9'] = '(GMT -09:00) Alaska';
$_LANG['cfg_range']['timezone']['-8'] = '(GMT -08:00) Pacific Time (US &amp; Canada), Tijuana';
$_LANG['cfg_range']['timezone']['-7'] = '(GMT -07:00) Mountain Time (US &amp; Canada), Arizona';
$_LANG['cfg_range']['timezone']['-6'] = '(GMT -06:00) Central Time (US &amp; Canada), Mexico City';
$_LANG['cfg_range']['timezone']['-5'] = '(GMT -05:00) Eastern Time (US &amp; Canada), Bogota, Lima, Quito';
$_LANG['cfg_range']['timezone']['-4'] = '(GMT -04:00) Atlantic Time (Canada), Caracas, La Paz';
$_LANG['cfg_range']['timezone']['-3.5'] = '(GMT -03:30) Newfoundland';
$_LANG['cfg_range']['timezone']['-3'] = '(GMT -03:00) Brassila, Buenos Aires, Georgetown, Falkland Is';
$_LANG['cfg_range']['timezone']['-2'] = '(GMT -02:00) Mid-Atlantic, Ascension Is., St. Helena';
$_LANG['cfg_range']['timezone']['-1'] = '(GMT -01:00) Azores, Cape Verde Islands';
$_LANG['cfg_range']['timezone']['0'] = '(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia';
$_LANG['cfg_range']['timezone']['1'] = '(GMT +01:00) Amsterdam, Berlin, Brussels, Madrid, Paris, Rome';
$_LANG['cfg_range']['timezone']['2'] = '(GMT +02:00) Cairo, Helsinki, Kaliningrad, South Africa';
$_LANG['cfg_range']['timezone']['3'] = '(GMT +03:00) Baghdad, Riyadh, Moscow, Nairobi';
$_LANG['cfg_range']['timezone']['3.5'] = '(GMT +03:30) Tehran';
$_LANG['cfg_range']['timezone']['4'] = '(GMT +04:00) Abu Dhabi, Baku, Muscat, Tbilisi';
$_LANG['cfg_range']['timezone']['4.5'] = '(GMT +04:30) Kabul';
$_LANG['cfg_range']['timezone']['5'] = '(GMT +05:00) Ekaterinburg, Islamabad, Karachi, Tashkent';
$_LANG['cfg_range']['timezone']['5.5'] = '(GMT +05:30) Bombay, Calcutta, Madras, New Delhi';
$_LANG['cfg_range']['timezone']['5.75'] = '(GMT +05:45) Katmandu';
$_LANG['cfg_range']['timezone']['6'] = '(GMT +06:00) Almaty, Colombo, Dhaka, Novosibirsk';
$_LANG['cfg_range']['timezone']['6.5'] = '(GMT +06:30) Rangoon';
$_LANG['cfg_range']['timezone']['7'] = '(GMT +07:00) Bangkok, Hanoi, Jakarta';
$_LANG['cfg_range']['timezone']['8'] = '(GMT +08:00) Beijing, Hong Kong, Perth, Singapore, Taipei';
$_LANG['cfg_range']['timezone']['9'] = '(GMT +09:00) Osaka, Sapporo, Seoul, Tokyo, Yakutsk';
$_LANG['cfg_range']['timezone']['9.5'] = '(GMT +09:30) Adelaide, Darwin';
$_LANG['cfg_range']['timezone']['10'] = '(GMT +10:00) Canberra, Guam, Melbourne, Sydney, Vladivostok';
$_LANG['cfg_range']['timezone']['11'] = '(GMT +11:00) Magadan, New Caledonia, Solomon Islands';
$_LANG['cfg_range']['timezone']['12'] = '(GMT +12:00) Auckland, Wellington, Fiji, Marshall Island';

$_LANG['cfg_range']['upload_size_limit']['-1'] = '服务默认设置';
$_LANG['cfg_range']['upload_size_limit']['0'] = '0KB';
$_LANG['cfg_range']['upload_size_limit']['64'] = '64KB';
$_LANG['cfg_range']['upload_size_limit']['128'] = '128KB';
$_LANG['cfg_range']['upload_size_limit']['256'] = '256KB';
$_LANG['cfg_range']['upload_size_limit']['512'] = '512KB';
$_LANG['cfg_range']['upload_size_limit']['1024'] = '1MB';
$_LANG['cfg_range']['upload_size_limit']['2048'] = '2MB';
$_LANG['cfg_range']['upload_size_limit']['4096'] = '4MB';
$_LANG['cfg_range']['visit_stats']['on'] = '开启';
$_LANG['cfg_range']['visit_stats']['off'] = '关闭';
$_LANG['cfg_range']['customer_service_type']['1'] = 'IM客服';
$_LANG['cfg_range']['customer_service_type']['2'] = 'QQ客服';
$_LANG['cfg_range']['customer_service_type']['3'] = '自定义客服';

$_LANG['rewrite_confirm_apache'] = "URL Rewrite 功能要求您的 Web Server 必须是 Apache，\\n并且起用了 rewrite 模块。\\n同时请您确认是否已经将htaccess.txt文件重命名为.htaccess。\\n如果服务器上还有其他的重写规则请去掉注释,请将RewriteBase行的注释去掉,并将路径设置为服务器请求的绝对路径";
$_LANG['rewrite_confirm_iis'] = "URL Rewrite 功能要求您的 Web Server 必须安装IIS，\\n并且起用了 ISAPI Rewrite 模块。\\n如果您使用的是ISAPI Rewrite商业版，请您确认是否已经将httpd.txt文件重命名为httpd.ini。如果您使用的是ISAPI Rewrite免费版，请您确认是否已经将httpd.txt文件内的内容复制到ISAPI Rewrite安装目录中httpd.ini里。";
$_LANG['retain_original_confirm'] = "如果您不保留商品原图，在“图片批量处理”的时候，\\n将不会重新生成不包含原图的商品图片。请慎重使用该功能！";
$_LANG['msg_invalid_file'] = '您上传了一个非法的文件类型。该文件名为：%s';
$_LANG['msg_upload_failed'] = '上传文件 %s 失败，请检查 %s 目录是否可写。';
$_LANG['smtp_ssl_confirm'] = '此功能要求您的php必须支持OpenSSL模块, 如果您要使用此功能，请联系您的空间商确认支持此模块';

/* 邮件设置语言项 */
$_LANG['cfg_name']['mail_service'] = '邮件服务';
$_LANG['cfg_desc']['mail_service'] = '如果您选择了采用服务器内置的 Mail 服务，您不需要填写下面的内容。';
$_LANG['cfg_range']['mail_service'][0] = '采用服务器内置的 Mail 服务';
$_LANG['cfg_range']['mail_service'][1] = '采用其他的 SMTP 服务';

$_LANG['cfg_name']['smtp_host'] = '发送邮件服务器地址(SMTP)';
$_LANG['cfg_name']['smtp_port'] = '服务器端口';
$_LANG['cfg_name']['smtp_user'] = '邮件发送帐号';
$_LANG['cfg_name']['smtp_pass'] = '帐号密码';
$_LANG['cfg_name']['smtp_mail'] = '邮件回复地址';
$_LANG['cfg_name']['mail_charset'] = '邮件编码';
$_LANG['cfg_name']['smtp_ssl'] = '邮件服务器是否要求加密连接(SSL)';
$_LANG['cfg_range']['smtp_ssl'][0] = '否';
$_LANG['cfg_range']['smtp_ssl'][1] = '是';

$_LANG['mail_settings_note'] = '<li>如果您的服务器支持 Mail 函数（具体信息请咨询您的空间提供商）。我们建议您使用系统的 Mail 函数。</li><li>当您的服务器不支持 Mail 函数的时候您也可以选用 SMTP 作为邮件服务器。</li>';

$_LANG['save_success'] = '保存商店设置成功。';
$_LANG['mail_save_success'] = '邮件服务器设置成功。';
$_LANG['sendemail_success'] = '恭喜！测试邮件已成功发送到 ';
$_LANG['sendemail_false'] = '邮件发送失败，请检查您的邮件服务器设置！';
$_LANG['seller_save_success'] = '店铺设置成功。';

$_LANG['js_languages']['smtp_host_empty'] = '您没有填写邮件服务器地址!';
$_LANG['js_languages']['smtp_port_empty'] = '您没有填写服务器端口!';
$_LANG['js_languages']['reply_email_empty'] = '您没有填写邮件回复地址!';
$_LANG['js_languages']['test_email_empty'] = '您没有填写发送测试邮件的地址!';
$_LANG['js_languages']['email_address_same'] = '邮件回复地址与发送测试邮件的地址不能相同!';

$_LANG['cfg_name']['wap'] = 'H5设置';
$_LANG['cfg_name']['wap_config'] = '是否使用WAP功能';
$_LANG['cfg_range']['wap_config'][0] = '关闭';
$_LANG['cfg_range']['wap_config'][1] = '开启';
$_LANG['cfg_name']['wap_logo'] = '微信自定义分享LOGO图片';
$_LANG['cfg_desc']['wap_logo'] = '推荐尺寸 800*800，png格式';

//手机端 补丁 start
$_LANG['cfg_name']['wap_index_pro'] = 'H5首页头部广告位';
$_LANG['cfg_range']['wap_index_pro'][1] = '开启';
$_LANG['cfg_range']['wap_index_pro'][0] = '关闭';
$_LANG['cfg_desc']['wap_index_pro'] = '可设置是否开启H5首页头部广告位APP推荐广告位';

// H5首页顶部推荐广告位设置
$_LANG['cfg_name']['h5_index_pro_image'] = 'H5首页头部广告图片';
$_LANG['cfg_desc']['h5_index_pro_image'] = '推荐尺寸 300*300，png格式';

$_LANG['cfg_name']['h5_index_pro_title'] = '广告语主标题';
$_LANG['cfg_name']['h5_index_pro_small_title'] = '广告语小标题';


$_LANG['cfg_name']['wap_category'] = '分类模板';
$_LANG['cfg_range']['wap_category'][0] = '默认模式';
$_LANG['cfg_range']['wap_category'][1] = '简单模式';

$_LANG['cfg_name']['use_lbs'] = '城市定位';
$_LANG['cfg_range']['use_lbs'][0] = '关闭';
$_LANG['cfg_range']['use_lbs'][1] = '开启';
$_LANG['cfg_desc']['use_lbs'] = '是否开启首页城市定位功能';

$_LANG['cfg_name']['wap_app_ios'] = '首页APP客户端(ios)下载地址';
$_LANG['cfg_desc']['wap_app_ios'] = '推荐填写发布在第三方应用市场的下载链接';
$_LANG['cfg_name']['wap_app_android'] = '首页APP客户端(android)下载地址';
$_LANG['cfg_desc']['wap_app_android'] = '推荐填写发布在第三方应用市场的下载链接';
//手机端 补丁 end

$_LANG['cfg_desc']['wap_config'] = '此功能只支持简体中文且只在中国大陆区有效';
$_LANG['cfg_name']['recommend_order'] = '推荐商品排序';
$_LANG['cfg_desc']['recommend_order'] = '推荐排序适合少量推荐，随机显示大量推荐';
$_LANG['cfg_range']['recommend_order'][0] = '推荐排序';
$_LANG['cfg_range']['recommend_order'][1] = '随机显示';

$_LANG['invoice_type'] = '类型';
$_LANG['invoice_rate'] = '税率（％）';
$_LANG['back_shop_config'] = '返回商店设置';
$_LANG['back_mail_settings'] = '返回邮件服务器设置';
$_LANG['mail_settings'] = '邮件服务器设置';

$_LANG['back_seller_settings'] = '返回店铺设置';

$_LANG['sms_success'] = '短信设置成功。';
$_LANG['back_sms_settings'] = '返回短信设置';

$_LANG['cloud_success'] = '文件存储设置成功。';
$_LANG['back_cloud_settings'] = '返回文件存储设置';

//@author guan start
$_LANG['cfg_name']['two_code_logo'] = '二维码中间LOGO';
$_LANG['cfg_name']['two_code_links'] = '网站域名';
$_LANG['cfg_name']['two_code_mouse'] = '提示文字';
$_LANG['cfg_desc']['two_code_mouse'] = '提示文字为鼠标放在二维码上面的出现的文字提示';
$_LANG['cfg_desc']['two_code_logo'] = '如果不上传logo将使用每个商品的图片作为logo';
$_LANG['cfg_desc']['two_code_links'] = '域名为商品跳转的地址，系统会自动匹配每个商品的id。域名格式：http(s)://www.xxxxx.com';
$_LANG['cfg_name']['two_code'] = '商品二维码';
$_LANG['cfg_range']['two_code']['1'] = '开启';
$_LANG['cfg_range']['two_code']['0'] = '关闭';
$_LANG['delete_two_logo'] = '你确认要删除LOGO图片吗？';
$_LANG['delete_two_text'] = '删除LOGO图片';
//@author guan end


//$_LANG['sms_url'] = '<a href="'.$url.'" target="_blank">点此注册手机短信服务</a>';

//分类异步加载商品设置 by wu
$_LANG['cfg_name']['category_load_type'] = '商品加载方式';
$_LANG['cfg_range']['category_load_type']['0'] = '默认分页';
$_LANG['cfg_range']['category_load_type']['1'] = 'AJAX瀑布流';
$_LANG['cfg_desc']['category_load_type'] = '设置分类/搜索/促销活动（团购活动、积分商城、夺宝奇兵、拍卖活动等）页下商品加载的方式';

$_LANG['cfg_name']['grade_apply_time'] = '店铺等级/模板未支付申请时效性';//by kong grade
$_LANG['cfg_desc']['grade_apply_time'] = '店铺等级订单到期后状态修改为无效，模板订单到期后直接删除。默认（一）。单位（天）';//by kong grade
$_LANG['cfg_name']['apply_options'] = '店铺升级剩余预付款处理';
$_LANG['cfg_range']['apply_options']['1'] = '补差价，退款';
$_LANG['cfg_range']['apply_options']['2'] = '补差价，不退款';

//佣金模式 by wu
$_LANG['cfg_name']['commission_model'] = '店铺佣金模式';
$_LANG['cfg_range']['commission_model']['0'] = '店铺佣金比例';
$_LANG['cfg_range']['commission_model']['1'] = '分类佣金比例';

$_LANG['cfg_name']['commission_percent'] = '固定佣金百分比';
$_LANG['cfg_desc']['commission_percent'] = '% （店铺统一佣金比例）';

//商家入驻默认密码前缀
$_LANG['cfg_name']['merchants_prefix'] = '店铺密码前缀';
$_LANG['cfg_desc']['merchants_prefix'] = '店铺入驻成功后，默认密码前缀';

//登录错误限制
$_LANG['cfg_name']['login_limited_num'] = '登录错误限制次数';

//导航分类模式 by wu
$_LANG['cfg_name']['nav_cat_model'] = '导航分类模式';
$_LANG['cfg_range']['nav_cat_model']['1'] = '只显示顶级分类';
$_LANG['cfg_range']['nav_cat_model']['0'] = '显示顶级分类和次级分类';

//首页文章分类
$_LANG['cfg_name']['index_article_cat'] = '首页文章栏目';
$_LANG['cfg_desc']['index_article_cat'] = '首页轮播图右侧文章栏目，请填写文章分类ID，多个分类使用半角逗号（,）分隔（注：2017模板使用）';

$_LANG['cfg_name']['marticle_index'] = '入驻首页底部文章';
$_LANG['cfg_desc']['marticle_index'] = '入驻首页底部文章，文本框中填写文章ID（格式：1,2,3 注：逗号为英文逗号）';

//可视化开关
$_LANG['cfg_name']['openvisual'] = '是否启用首页可视化';
$_LANG['cfg_range']['openvisual']['0'] = '关闭';
$_LANG['cfg_range']['openvisual']['1'] = '启用';
$_LANG['cfg_desc']['openvisual'] = "开启首页可视化后前台首页将调用可视化模板，如果没有可视化模板则继续调用默认模板";

//首页弹出广告
$_LANG['cfg_name']['bonus_adv'] = '是否开启首页弹出广告';
$_LANG['cfg_range']['bonus_adv']['0'] = '关闭';
$_LANG['cfg_range']['bonus_adv']['1'] = '启用';
$_LANG['cfg_desc']['bonus_adv'] = "可视化首页弹出广告在可视化装修中设置，默认模板弹出在广告位‘首页天降红包’中添加";

//会员是否开启批发求购单
$_LANG['cfg_name']['wholesale_user_rank'] = '是否支持注册会员使用批发功能';
$_LANG['cfg_range']['wholesale_user_rank']['0'] = '否';
$_LANG['cfg_range']['wholesale_user_rank']['1'] = '是';

//是否开启举报功能
$_LANG['cfg_name']['is_illegal'] = '是否开启举报功能';
$_LANG['cfg_range']['is_illegal']['0'] = '关闭';
$_LANG['cfg_range']['is_illegal']['1'] = '启用';

//举报配置
$_LANG['cfg_name']['report_handle'] = '恶意举报处罚措施';
$_LANG['cfg_range']['report_handle']['0'] = '关闭';
$_LANG['cfg_range']['report_handle']['1'] = '开启';
$_LANG['cfg_desc']['report_handle'] = '开启该配置后，恶意举报的会员一定时间内将剥夺举报权利！';

$_LANG['cfg_name']['report_time'] = '举报时效';
$_LANG['cfg_desc']['report_time'] = '开启该配置后，举报将在到期后将修改状态为无效举报（单位：天）';

$_LANG['cfg_name']['report_handle_time'] = '恶意举报处罚时间';
$_LANG['cfg_desc']['report_handle_time'] = '审核为恶意举报后，该会员将冻结举报权限天数（单位：天）';

$_LANG['cfg_name']['receipt_time'] = '订单投诉时效性';
$_LANG['cfg_desc']['receipt_time'] = '确认收货后多少天内可申请交易纠纷，默认为15天（单位：天）';

//拼团
$_LANG['cfg_name']['virtual_order'] = '是否显示拼团首页订单提示轮播';
$_LANG['cfg_range']['virtual_order']['0'] = '不显示';
$_LANG['cfg_range']['virtual_order']['1'] = '显示';

$_LANG['cfg_name']['virtual_limit_nim'] = '是否显示写入虚拟已参团人数';
$_LANG['cfg_range']['virtual_limit_nim']['0'] = '不显示';
$_LANG['cfg_range']['virtual_limit_nim']['1'] = '显示';

// 会员余额提现设置项
$_LANG['cfg_name']['user_balance_withdrawal'] = '会员余额提现';
$_LANG['cfg_range']['user_balance_withdrawal']['0'] = '不支持';
$_LANG['cfg_range']['user_balance_withdrawal']['1'] = '支持';
$_LANG['cfg_desc']['user_balance_withdrawal'] = '默认支持，修改为不支持时，整站会员余额不可提现';

// 会员余额充值设置项
$_LANG['cfg_name']['user_balance_recharge'] = '会员余额充值';
$_LANG['cfg_range']['user_balance_recharge']['0'] = '不支持';
$_LANG['cfg_range']['user_balance_recharge']['1'] = '支持';
$_LANG['cfg_desc']['user_balance_recharge'] = '默认支持，修改为不支持时，整站会员余额不可充值';

//b2b 批发首页文章分类
$_LANG['cfg_name']['wholesale_article_cat'] = '批发首页文章栏目';
$_LANG['cfg_desc']['wholesale_article_cat'] = '批发首页轮播图右侧文章栏目，请填写文章分类ID，多个分类使用半角逗号（,）分隔（注：2017模板使用）';

//商品设置
$_LANG['cfg_name']['goods_base'] = '基本设置';
$_LANG['cfg_name']['goods_display'] = '显示设置';
$_LANG['cfg_name']['goods_page'] = '页面设置';
$_LANG['cfg_name']['goods_picture'] = '图片设置';
$_LANG['cfg_name']['goods_comment'] = '评价设置';

$_LANG['cfg_name']['template_pay_type'] = '重复付费设置';
$_LANG['cfg_range']['template_pay_type']['0'] = '开启';
$_LANG['cfg_range']['template_pay_type']['1'] = '关闭';
$_LANG['cfg_desc']['template_pay_type'] = '开启后，重复购买模板时需要再次付费。关闭后，重复购买的则直接上传为商家模板，无需再次付费';

//会员提现手续费
$_LANG['cfg_name']['deposit_fee'] = '会员提现手续';
$_LANG['cfg_desc']['deposit_fee'] = '%';

//退换货设置
$_LANG['cfg_name']['activation_number_type'] = '被拒退换货激活设置';
$_LANG['cfg_desc']['activation_number_type'] = '退换货如果被拒，会员可以重新激活次数，默认为：2次';
$_LANG['cfg_name']['seller_return_check'] = '商家退款是否审批';
$_LANG['cfg_range']['seller_return_check']['0'] = '否';
$_LANG['cfg_range']['seller_return_check']['1'] = '是';
$_LANG['cfg_desc']['seller_return_check'] = '设置为是时，商家退款操作需平台审批后生效；为否时需设置优先退款方式';
$_LANG['cfg_name']['precedence_return_type'] = '优先退款方式';
$_LANG['cfg_range']['precedence_return_type']['1'] = '退回余额';
$_LANG['cfg_range']['precedence_return_type']['2'] = '线下退款';
$_LANG['cfg_range']['precedence_return_type']['6'] = '在线原路退回';
$_LANG['cfg_desc']['precedence_return_type'] = '商家退款申请无需审批时，自动退款优先使用的退款方式，支持原路退回、退回余额、线下退款';

$_LANG['cfg_name']['register_article_id'] = '会员注册协议文章ID';

//商家后台首页文章（商家帮助）
$_LANG['cfg_name']['seller_index_article'] = '商家后台首页文章分类';
$_LANG['cfg_desc']['seller_index_article'] = '商家帮助文章分类ID ';

//会员提现、充值限额
$_LANG['cfg_name']['buyer_cash'] = '买家提现最低金额';
$_LANG['cfg_desc']['buyer_cash'] = '设置买家提现最低金额，0表示不限';

//会员提现、充值限额
$_LANG['cfg_name']['buyer_recharge'] = '买家充值最低金额';
$_LANG['cfg_desc']['buyer_recharge'] = '设置买家充值最低金额，0表示不限';

//首页登录右侧设置
$_LANG['cfg_name']['login_right'] = '首页登录右侧';
$_LANG['cfg_name']['login_right_link'] = '首页登录右侧链接';

//logo设置
$_LANG['cfg_desc']['index_down_logo'] = '建议图标尺寸130*33，png格式（1.0老模板使用）';
$_LANG['cfg_desc']['user_login_logo'] = '建议图标尺寸159*57，png格式';
$_LANG['cfg_desc']['login_logo_pic'] = '上传图片格式必须是gif,jpg,jpeg,png;图片大小在200kb之内';
$_LANG['cfg_desc']['admin_login_logo'] = '建议图标尺寸130*43，png格式';
$_LANG['cfg_desc']['admin_logo'] = '建议图标尺寸174*48，png格式';
$_LANG['cfg_desc']['seller_login_logo'] = '建议图标尺寸172*51，png格式';
$_LANG['cfg_desc']['seller_logo'] = '建议图标尺寸84*29，png格式';
$_LANG['cfg_desc']['stores_login_logo'] = '建议图标尺寸140*82，png格式';
$_LANG['cfg_desc']['stores_logo'] = '建议图标尺寸90*32，png格式';
$_LANG['cfg_desc']['order_print_logo'] = '建议图标尺寸159*57，png格式';
$_LANG['cfg_desc']['kefu_login_log'] = '建议图标尺寸172*51，png格式';

//延迟收货申请
$_LANG['cfg_name']['open_order_delay'] = '是否开启延迟收货';
$_LANG['cfg_range']['open_order_delay']['0'] = '关闭';
$_LANG['cfg_range']['open_order_delay']['1'] = '启用';

$_LANG['cfg_name']['order_delay_num'] = '延迟收货申请次数';
$_LANG['cfg_desc']['order_delay_num'] = '单个订单可申请延迟收货次数';

$_LANG['cfg_name']['order_delay_day'] = '申请延迟收货时间设置';
$_LANG['cfg_desc']['order_delay_day'] = '自动确认收货前多少天可申请延迟收货';

$_LANG['cfg_name']['pay_effective_time'] = "支付有效时间（分钟）";
$_LANG['cfg_desc']['pay_effective_time'] = '下单支付有效时间,过时订单失效,为0或小于0则不限时间（小数点后无效）';

$_LANG['cfg_name']['auto_delivery_time'] = "自动确认收货（天数）";
$_LANG['cfg_desc']['auto_delivery_time'] = '已发货订单的时间累加设置天数时间系统进行自动确认收货（需配置脚本）';

//商家设置商品分期
$_LANG['cfg_name']['seller_stages'] = '商家设置商品分期';
$_LANG['cfg_range']['seller_stages']['0'] = '关闭';
$_LANG['cfg_range']['seller_stages']['1'] = '开启';

$_LANG['js_languages']['seller_info_notic'] = '商店名称不能为空';
$_LANG['js_languages']['integral_percent_notic'] = '积分支付比例不能大于100';
$_LANG['btnSubmit_notice'] = '商店设置 -> 购物流程 -> 发票税率重复：';
$_LANG['type_already_exists'] = '类型已存在';
$_LANG['type_taxrate_not_notic'] = '类型和税率不能为空';
$_LANG['taxrate_number'] = '税率必须为数字';

$_LANG['tutorials_bonus_list_one'] = '<a href="http://help.ecmoban.com/article-6273.html" target="_blank">商店设置---平台基本信息设置</a>
                            <a href="http://help.ecmoban.com/article-6274.html" target="_blank">商店设置---客服设置</a>
                            <a href="http://help.ecmoban.com/article-6268.html" target="_blank">商店设置---市场价格比例及积分比例设置</a>
                            <a href="http://help.ecmoban.com/article-6271.html" target="_blank">商店设置---设置区分地区商品</a>
                            <a href="http://help.ecmoban.com/article-6266.html" target="_blank">商店设置---短信设置</a>
                            <a href="http://help.ecmoban.com/article-6267.html" target="_blank">商店设置---WAP设置</a>
                            <a href="http://help.ecmoban.com/article-6265.html" target="_blank">商店设置---商品属性价格模式设置</a>';

$_LANG['tutorials_bonus_list_two'] = '商城邮件服务器设置';

$_LANG['operation_prompt_content']['shop_config'][0] = '商店相关信息设置，请谨慎填写信息。';

$_LANG['operation_prompt_content']['shop_config_return'][0] = '设置商家退款是否需要审核，无需审核时，商家同意退款后，订单即使用优先退款方式退款;需要审核则商家同意退款后，需管理员手动操作退款。';
$_LANG['operation_prompt_content']['shop_config_return'][1] = '优先退款方式设置支持微信/支付宝原路退回、退回余额、线下退款。';

$_LANG['cfg_name']['shop_can_comment'] = '开启评论';
$_LANG['cfg_range']['shop_can_comment']['0'] = '否';
$_LANG['cfg_range']['shop_can_comment']['1'] = '是';
$_LANG['cfg_desc']['shop_can_comment'] = '整站开启或者关闭评论';

$_LANG['cfg_name']['auto_evaluate'] = '自动评价';
$_LANG['cfg_range']['auto_evaluate']['0'] = '否';
$_LANG['cfg_range']['auto_evaluate']['1'] = '是';
$_LANG['cfg_desc']['auto_evaluate'] = '已完成订单会员未评价时系统自动生成评价。须开启计划任务';

$_LANG['cfg_name']['auto_evaluate_time'] = '自动评价时间';
$_LANG['cfg_desc']['auto_evaluate_time'] = '自动评价生成时间，单位：天。订单完成后，未评价订单将在X天后生成自动评价';

$_LANG['cfg_name']['auto_evaluate_content'] = '自动评价内容';
$_LANG['cfg_desc']['auto_evaluate_content'] = '生成自动评价的内容模板，此内容将显示在前端商品自动评价中，请谨慎添加；最多输入60个字';

$_LANG['cfg_name']['add_evaluate'] = '追评';
$_LANG['cfg_range']['add_evaluate']['0'] = '否';
$_LANG['cfg_range']['add_evaluate']['1'] = '是';
$_LANG['cfg_desc']['add_evaluate'] = '已评价订单发起追加评价';

$_LANG['cfg_name']['add_evaluate_time'] = '可追评时间';
$_LANG['cfg_desc']['add_evaluate_time'] = '可发起追评时间，单位：天。订单完成后，X天内可发起追评';

$_LANG['seller_commission'] = '商家订单支付手续费将在结算时全额结算给商家，请谨慎选择开启支付手续费！';

$_LANG['universal_amount_style'] = '通用金额样式';
$_LANG['tianmao_amount_style'] = '天猫商城金额样式';
$_LANG['whole_bold'] = '整体加粗显示';
$_LANG['highlight_price'] = '凸显商品价格';
$_LANG['jingdong_amount_style'] = '京东商城金额样式';
$_LANG['part_of_the_bold'] = '金额整数部分加粗';
$_LANG['weaken_currency_symbol_decimal'] = '弱化货币符号和小数';
$_LANG['pindd_amount_style'] = '拼多多商城金额样式';
$_LANG['amount_part_of_the_bold'] = '金额部分加粗显示';
$_LANG['weaken_currency_symbol'] = '弱化货币符号';

$_LANG['cfg_name']['goods_stock_model'] = '商品[价格/库存]模式';
$_LANG['cfg_range']['goods_stock_model']['0'] = '关闭';
$_LANG['cfg_range']['goods_stock_model']['1'] = '开启';
$_LANG['cfg_desc']['goods_stock_model'] = '主要用于添加商品时能按仓库模式、地区模式设置相关库存、价格等方式';

// v2.2.2
$_LANG['cfg_name']['upload_use_original_name'] = '商品相册图片是否保留原名称';
$_LANG['cfg_range']['upload_use_original_name']['0'] = '否';
$_LANG['cfg_range']['upload_use_original_name']['1'] = '是';
$_LANG['cfg_desc']['upload_use_original_name'] = '商品相册图片名称是否使用上传时图片原名称';

// 是否开启下载页
$_LANG['cfg_name']['pc_download_open'] = '是否开启下载页';
$_LANG['cfg_range']['pc_download_open']['0'] = '关闭';
$_LANG['cfg_range']['pc_download_open']['1'] = '开启';
$_LANG['cfg_desc']['pc_download_open'] = '可选启用下载页，下载页地址：/download，启用后可上传下载页图片，前端可访问下载页';

$_LANG['cfg_name']['pc_download_img'] = '下载页图片';
$_LANG['cfg_desc']['pc_download_img'] = '推荐宽度：1920px';

$_LANG['cfg_name']['app_field'] = "提高查询<em class='red'>（Between）</em>性能";
$_LANG['cfg_range']['app_field']['0'] = '[0]按大于零条件';
$_LANG['cfg_range']['app_field']['1'] = '[1]按之间（Between）条件';
$_LANG['cfg_desc']['app_field'] = "<p>选[0]时 where('字段名称', '>', 0) ，数据量（20W以上比较明显）大时会有所影响查询性能</p><p>选[1]时 whereBetween('字段名称', [1, config('app.seller_user')]) ，数据量（20W以上比较明显）大时会有所提升查询性能</p><p>备注：选[1]时须注意<em class='red'>config\app.php</em>文件配置的<em class='red'>seller_user、order_id、rec_id</em>等相关表自增ID值</p>";

$_LANG['cfg_name']['seller_review'] = '审核商家信息';
$_LANG['cfg_range']['seller_review']['0'] = '无需审核';
$_LANG['cfg_range']['seller_review']['1'] = '仅审核入驻流程';
$_LANG['cfg_range']['seller_review']['2'] = '仅审核店铺基本信息';
$_LANG['cfg_range']['seller_review']['3'] = '审核入驻流程和店铺基本信息';
$_LANG['cfg_desc']['seller_review'] = '审核商家信息，可选择设置入驻流程和店铺信息审核';

$_LANG['cfg_name']['seller_step_email'] = '入驻商账号密码邮件通知';
$_LANG['cfg_range']['seller_step_email']['0'] = '关闭';
$_LANG['cfg_range']['seller_step_email']['1'] = '开启';
$_LANG['cfg_desc']['seller_step_email'] = '审核入驻商登录账号密码是否发送邮件通知';

$_LANG['cfg_name']['wxapp_chat'] = '微信小程序端客服';
$_LANG['cfg_range']['wxapp_chat']['0'] = '使用商城系统IM客服';
$_LANG['cfg_range']['wxapp_chat']['1'] = '使用小程序自带客服';
$_LANG['cfg_desc']['wxapp_chat'] = '主要用于控制微信小程序端的客服使用，影响商城整体（含商家客服）';

$_LANG['cfg_name']['cloud_file_ip'] = '负载服务器IP';
$_LANG['cfg_desc']['cloud_file_ip'] = '开启<em class="red">多台服务器负载均衡状态</em>时， 请求输入多台服务器IP，输入以换行格式';

$_LANG['cfg_name']['cross_source'] = '跨境货源';
$_LANG['cfg_desc']['cross_source'] = '主要用于商家入驻选择跨境货源使用，辨别跨境店铺类型，默认值：国内仓库,自贸区,海外直邮';

$_LANG['cfg_name']['favourable_show'] = '显示设置';

$_LANG['cfg_name']['drp_show_price'] = '店铺商品价格显示';
$_LANG['cfg_range']['drp_show_price']['0'] = '关闭';
$_LANG['cfg_range']['drp_show_price']['1'] = '开启';
$_LANG['cfg_desc']['drp_show_price'] = '主要用于是否开启会员需购买权益卡才能显示店铺商品价格和购买店铺商品';

return $_LANG;
