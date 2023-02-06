<?php

/**
 * DSCMALL 常量
 */
define('APPNAME', 'Dscmall');
define('VERSION', 'v2.7.2');
define('RELEASE', '20220808');
define('CHARSET', 'utf-8');
define('EC_CHARSET', 'utf-8');
define('ADMIN_PATH', 'admin');
define('SELLER_PATH', 'seller');
define('STORES_PATH', 'stores');
define('SUPPLLY_PATH', 'suppliers');
define('AUTH_KEY', 'this is a key');
define('OLD_AUTH_KEY', '');
define('API_TIME', '');
define('EC_TEMPLATE', 'ecmoban_dsc2017');
define('CHANGE_PRICE', true);
define('IPDATA', '2019070314');

define('MODULES_PC', file_exists(dirname(__DIR__) . '/Modules/Web/WebServiceProvider.php')); // PC模块
define('MOBILE_WECHAT', dirname(__DIR__) . '/Modules/Wechat/WechatServiceProvider.php'); //微信通模块
define('MOBILE_DRP', dirname(__DIR__) . '/Modules/Drp/DrpServiceProvider.php'); //微分销模块
define('MOBILE_WXAPP', dirname(__DIR__) . '/Modules/Wxapp/WxappServiceProvider.php'); // 小程序模块
define('WXAPP_MEDIA', dirname(__DIR__) . '/Modules/WxMedia/Controllers/WxMedia/MediaSettingController.php'); //小程序视频号
define('WXAPP_MEDIA_CONCISE', dirname(__DIR__) . '/Modules/WxMedia/Models/WxappMediaGoodsQrcod.php'); //小程序视频号简洁版
define('WXAPP_MEDIA_PROMOTER', dirname(__DIR__) . '/Modules/WxMedia/Models/WxappPromoterList.php'); //小程序视频号推广员
define('MOBILE_TEAM', dirname(__DIR__) . '/Modules/Admin/Controllers/TeamController.php'); //拼团模块
define('MOBILE_BARGAIN', dirname(__DIR__) . '/Modules/Admin/Controllers/BargainController.php'); //砍价模块
define('MOBILE_KEFU', dirname(__DIR__) . '/Modules/Chat/ChatServiceProvider.php'); //客服模块
define('MOBILE_APP', dirname(__DIR__) . '/Modules/Admin/Controllers/AppController.php'); //APP模块
define('MOBILE_GROUPBUY', dirname(__DIR__) . '/Modules/Cgroup/Controllers/Platform/LeaderController.php'); //社区团购模块
define('SUPPLIERS', dirname(__DIR__) . '/Modules/Suppliers/SupplierServiceProvider.php'); //供应链模块
define('MOBILE_WXSHOP', dirname(__DIR__) . '/Modules/Wxshop/Controllers/Admin/WxshopController.php'); //微信小商店模块
define('STORE_MANAGE', dirname(__DIR__) . '/Modules/Stores/Controllers/Api/StoreManageController.php'); // 门店核销

define('MODULES_DIVIDE', dirname(__DIR__) . '/Manager/DivideTrace/'); // 收付通分账模块目录
define('MODULES_SHOUQIANBA', dirname(__DIR__) . '/Custom/Shouqianba/'); // 收钱吧电子发票模块目录

/* 处理并发事件 */
define('DEALCONCURRENT_PAY_ORDER', 0); // 会员前台下单

/* 退换货状态 */
define('RF_RETURNMON', -1); // 仅退款
define('RF_APPLICATION', 0); // 申请
define('RF_RECEIVE', 1); // 收到退换货
define('RF_SWAPPED_OUT_SINGLE', 2); // 换出商品寄出 分单
define('RF_SWAPPED_OUT', 3); // 换出商品寄出
define('RF_COMPLETE', 4); // 完成退换货
define('RF_AGREE_APPLY', 5); // 同意申请
define('REFUSE_APPLY', 6); // 拒绝申请

define('FF_NOREFOUND', 0);//未退款
define('FF_REFOUND', 1);//已退款
define('FF_EXCHANGE', 2);//已换货
define('FF_MAINTENANCE', 3);//已维修
define('FF_NOEXCHANGE', 4);//未换货
define('FF_NOMAINTENANCE', 5);//未维修

/* 图片处理相关常数 */
define('ERR_INVALID_IMAGE', 1);
define('ERR_NO_GD', 2);
define('ERR_IMAGE_NOT_EXISTS', 3);
define('ERR_DIRECTORY_READONLY', 4);
define('ERR_UPLOAD_FAILURE', 5);
define('ERR_INVALID_PARAM', 6);
define('ERR_INVALID_IMAGE_TYPE', 7);

/* 插件相关常数 */
define('ERR_COPYFILE_FAILED', 1);
define('ERR_CREATETABLE_FAILED', 2);
define('ERR_DELETEFILE_FAILED', 3);

/* 商品属性类型常数 */
define('ATTR_TEXT', 0);
define('ATTR_OPTIONAL', 1);
define('ATTR_TEXTAREA', 2);
define('ATTR_URL', 3);

/* 会员整合相关常数 */
define('ERR_USERNAME_EXISTS', 1); // 用户名已经存在
define('ERR_EMAIL_EXISTS', 2); // Email已经存在
define('ERR_INVALID_USERID', 3); // 无效的user_id
define('ERR_INVALID_USERNAME', 4); // 无效的用户名
define('ERR_INVALID_PASSWORD', 5); // 密码错误
define('ERR_INVALID_EMAIL', 6); // email错误
define('ERR_USERNAME_NOT_ALLOW', 7); // 用户名不允许注册
define('ERR_EMAIL_NOT_ALLOW', 8); // EMAIL不允许注册
define('ERR_PHONE_EXISTS', 9); // 手机号码已经存在 ecmoban模板堂 --zhuo
define('ERR_PASSWORD_NOT_ALLOW', 10); // 密码不能与用户名相同

/* 加入购物车失败的错误代码 */
define('ERR_NOT_EXISTS', 1); // 商品不存在
define('ERR_OUT_OF_STOCK', 2); // 商品缺货
define('ERR_NOT_ON_SALE', 3); // 商品已下架
define('ERR_CANNT_ALONE_SALE', 4); // 商品不能单独销售
define('ERR_NO_BASIC_GOODS', 5); // 没有基本件
define('ERR_NEED_SELECT_ATTR', 6); // 需要用户选择属性

/* 购物车商品类型 */
define('CART_GENERAL_GOODS', 0); // 普通商品
define('CART_GROUP_BUY_GOODS', 1); // 团购商品
define('CART_AUCTION_GOODS', 2); // 拍卖商品
define('CART_SNATCH_GOODS', 3); // 夺宝奇兵
define('CART_EXCHANGE_GOODS', 4); // 积分商城
define('CART_PRESALE_GOODS', 5); // 预售商品
define('CART_SECKILL_GOODS', 6); // 秒杀商品
define('CART_TEAM_GOODS', 7); // 拼团商品
define('CART_BARGAIN_GOODS', 8); // 砍价商品
define('CART_ONESTEP_GOODS', 10); // 立即购买-普通商品
define('CART_PACKAGE_GOODS', 11); // 超值礼包
define('CART_OFFLINE_GOODS', 12); // 门店自提商品

/* 订单状态 */
define('OS_UNCONFIRMED', 0); // 未确认
define('OS_CONFIRMED', 1); // 已确认
define('OS_CANCELED', 2); // 已取消
define('OS_INVALID', 3); // 无效
define('OS_RETURNED', 4); // 退货
define('OS_SPLITED', 5); // 已分单
define('OS_SPLITING_PART', 6); // 部分分单
define('OS_RETURNED_PART', 7); // 部分已退货
define('OS_ONLY_REFOUND', 8); // 仅退款

/* 订单状态(mobile) */
define('STATUS_CREATED', 0); // 待付款
define('STATUS_PAID', 1); // 已付款
define('STATUS_DELIVERING', 2); // 发货中
define('STATUS_DELIVERIED', 3); // 已收货，待评价
define('STATUS_FINISHED', 4); // 已完成
define('STATUS_CANCELLED', 5); // 已取消


/* 支付类型 */
define('PAY_ORDER', 0); // 订单支付
define('PAY_SURPLUS', 1); // 会员预付款
define('PAY_APPLYGRADE', 2); // 商家升级付款  by kong grade
define('PAY_TOPUP', 3); // 商家账户充值
define('PAY_APPLYTEMP', 4); // 商家购买模板付款  by kong grade
define('PAY_WHOLESALE', 5); // 批发支付
define('PAY_REGISTERED', 6); // 购买成为分销商
define('PAY_TEAM_ORDER', 7); // 拼团购买
define('PAY_GROUPBUY_ORDER', 8); // 社区团购


/* 配送状态 */
define('SS_UNSHIPPED', 0); // 未发货
define('SS_SHIPPED', 1); // 已发货
define('SS_RECEIVED', 2); // 已收货
define('SS_PREPARING', 3); // 备货中
define('SS_SHIPPED_PART', 4); // 已发货(部分商品)
define('SS_SHIPPED_ING', 5); // 发货中(处理分单)
define('OS_SHIPPED_PART', 6); // 已发货(部分商品)
define('SS_PART_RECEIVED', 7); // 部分已收货
define('SS_TO_BE_SHIPPED', 8); // 待发货

/* 支付状态 */
define('PS_UNPAYED', 0); // 未付款
define('PS_PAYING', 1); // 付款中
define('PS_PAYED', 2); // 已付款
define('PS_PAYED_PART', 3); // 部分付款--预售定金
define('PS_REFOUND', 4); // 已退款
define('PS_REFOUND_PART', 5); // 部分退款
define('PS_MAIN_PAYED_PART', 6); // 部分已付款 //主订单

/* 综合状态 */
define('CS_AWAIT_PAY', 100); // 待付款：货到付款且已发货且未付款，非货到付款且未付款
define('CS_AWAIT_SHIP', 101); // 待发货：货到付款且未发货，非货到付款且已付款且未发货
define('CS_FINISHED', 102); // 已完成：已确认、已付款、已收货
define('CS_TO_CONFIRM', 103); // 待确认收货：已确认、已付款、已发货（待用户确认收货）
define('CS_CONFIRM_TAKE', 104); // 已确认收货：已确认、已付款、已发货 用户已收货
define('CS_ORDER_BACK', 105); // 未处理退换货
define('CS_NEW_ORDER', 106); // 新订单
define('CS_NEW_PAID_ORDER', 107); // 新付款订单
define('CS_WAIT_GOODS', 108); // 已完成：已确认、已付款、已收货

/* 发货单状态 */
define('DELIVERY_SHIPPED', 0); // 已发货
define('DELIVERY_REFOUND', 1); // 退款
define('DELIVERY_CREATE', 2); // 生成发货单

/* 缺货处理 */
define('OOS_WAIT', 0); // 等待货物备齐后再发
define('OOS_CANCEL', 1); // 取消订单
define('OOS_CONSULT', 2); // 与店主协商

/* 帐户明细类型 */
define('SURPLUS_SAVE', 0); // 为帐户冲值
define('SURPLUS_RETURN', 1); // 从帐户提款

/* 评论状态 */
define('COMMENT_UNCHECKED', 0); // 未审核
define('COMMENT_CHECKED', 1); // 已审核或已回复(允许显示)
define('COMMENT_REPLYED', 2); // 该评论的内容属于回复

/* 红包发放的方式 */
define('SEND_BY_USER', 0); // 按用户发放
define('SEND_BY_GOODS', 1); // 按商品发放
define('SEND_BY_ORDER', 2); // 按订单发放
define('SEND_BY_PRINT', 3); // 线下发放
define('SEND_BY_GET', 4); // 自行领取

/* 广告的类型 */
define('IMG_AD', 0); // 图片广告
define('FALSH_AD', 1); // flash广告
define('CODE_AD', 2); // 代码广告
define('TEXT_AD', 3); // 文字广告

/* 是否需要用户选择属性 */
define('ATTR_NOT_NEED_SELECT', 0); // 不需要选择
define('ATTR_NEED_SELECT', 1); // 需要选择

/* 用户中心留言类型 */
define('M_MESSAGE', 0); // 留言
define('M_COMPLAINT', 1); // 投诉
define('M_ENQUIRY', 2); // 询问
define('M_CUSTOME', 3); // 售后
define('M_BUY', 4); // 求购
define('M_BUSINESS', 5); // 商家
define('M_COMMENT', 6); // 评论

/* 团购活动状态 */
define('GBS_PRE_START', 0); // 未开始
define('GBS_UNDER_WAY', 1); // 进行中
define('GBS_FINISHED', 2); // 已结束
define('GBS_SUCCEED', 3); // 团购成功（可以发货了）
define('GBS_FAIL', 4); // 团购失败

/* 红包是否发送邮件 */
define('BONUS_NOT_MAIL', 0);
define('BONUS_MAIL_SUCCEED', 1);
define('BONUS_MAIL_FAIL', 2);

/* 商品活动类型 */
define('GAT_SNATCH', 0); // 夺宝奇兵
define('GAT_GROUP_BUY', 1); // 商品团购
define('GAT_AUCTION', 2); // 拍卖活动
define('GAT_POINT_BUY', 3);
define('GAT_PACKAGE', 4); // 超值礼包

/* 帐号变动类型 */
define('ACT_SAVING', 0);     // 帐户冲值
define('ACT_DRAWING', 1);     // 帐户提款
define('ACT_ADJUSTING', 2);     // 调节帐户
define('ACT_SEPARATE', 3);     // 分销分成
define('ACT_TRANSFERRED', 4);     // 佣金转到余额
define('ACT_OTHER', 99);     // 其他类型

/* 密码加密方法 */
define('PWD_MD5', 1);  //md5加密方式
define('PWD_PRE_SALT', 2);  //前置验证串的加密方式
define('PWD_SUF_SALT', 3);  //后置验证串的加密方式

/* 文章分类类型 */
define('COMMON_CAT', 1); //普通分类
define('SYSTEM_CAT', 2); //系统默认分类
define('INFO_CAT', 3); //网店信息分类
define('UPHELP_CAT', 4); //网店帮助分类分类
define('HELP_CAT', 5); //网店帮助分类

/* 活动状态 */
define('PRE_START', 0); // 未开始
define('UNDER_WAY', 1); // 进行中
define('FINISHED', 2); // 已结束
define('SETTLED', 3); // 已处理

/* 验证码 */
define('CAPTCHA_REGISTER', 1); //注册时使用验证码
define('CAPTCHA_LOGIN', 2); //登录时使用验证码
define('CAPTCHA_COMMENT', 4); //评论时使用验证码
define('CAPTCHA_ADMIN', 8); //后台登录时使用验证码
define('CAPTCHA_LOGIN_FAIL', 16); //登录失败后显示验证码
define('CAPTCHA_MESSAGE', 32); //留言时使用验证码
define('CAPTCHA_SAFETY', 64); //会员安全认证验证码

/* 优惠活动的优惠范围 */
define('FAR_ALL', 0); // 全部商品
define('FAR_CATEGORY', 1); // 按分类选择
define('FAR_BRAND', 2); // 按品牌选择
define('FAR_GOODS', 3); // 按商品选择
define('AUTONOMOUS_USE', 0); // 自主使用  卖场优惠活动 liu
define('GENERAL_AUDIENCE', 1); // 全场通用  卖场优惠活动 liu

/* 优惠活动的优惠方式 */
define('FAT_GOODS', 0); // 送赠品或优惠购买
define('FAT_PRICE', 1); // 现金减免
define('FAT_DISCOUNT', 2); // 价格打折优惠

/* 评论条件 */
define('COMMENT_LOGIN', 1); //只有登录用户可以评论
define('COMMENT_CUSTOM', 2); //只有有过一次以上购买行为的用户可以评论
define('COMMENT_BOUGHT', 3); //只有购买够该商品的人可以评论

/* 减库存时机 */
define('SDT_SHIP', 0); // 发货时
define('SDT_PLACE', 1); // 下订单时
define('SDT_PAID', 2); // 付款时

/* 增加销量时机 */
define('SALES_PAY', 0); // 付款时
define('SALES_SHIP', 1); // 发货时

/* 加密方式 */
define('ENCRYPT_ZC', 1); //zc加密方式
define('ENCRYPT_UC', 2); //uc加密方式

/* 商品类别 */
define('G_REAL', 1); //实体商品
define('G_CARD', 0); //虚拟卡

/* 积分兑换 */
define('TO_P', 0); //兑换到商城消费积分
define('FROM_P', 1); //用商城消费积分兑换
define('TO_R', 2); //兑换到商城成长值
define('FROM_R', 3); //用商城成长值兑换

/* 支付宝商家账户 */
define('ALIPAY_AUTH', 'gh0bis45h89m5mwcoe85us4qrwispes0');
define('ALIPAY_ID', '2088002052150939');

/* 添加feed事件到UC的TYPE*/
define('BUY_GOODS', 1); //购买商品
define('COMMENT_GOODS', 2); //添加商品评论

/* 邮件发送用户 */
define('SEND_LIST', 0);
define('SEND_USER', 1);
define('SEND_RANK', 2);

/* license接口 */
define('LICENSE_VERSION', '1.0');

/* 配送方式 */
define('SHIP_LIST', 'cac|city_express|ems|flat|fpd|post_express|post_mail|presswork|sf_express|sto_express|yto|zto');

/* 会员操作类型 */
define('USER_LOGIN', 1);     // 会员登录
define('USER_PICT', 2);     // 修改会员头像
define('USER_INFO', 3);     // 修改会员信息
define('USER_REAL', 4);     // 会员实名认证
define('USER_PPASS', 5);     // 会员支付密码
define('USER_PHONE', 6);     // 修改会员手机
define('USER_EMAIL', 7);     // 修改会员邮箱
define('USER_LPASS', 8);     // 修改会员登录密码
define('USER_LINE', 9);     // 修改会员信用额度

define('PC_USER', 'pc');     // 电脑
define('MOBILE_USER', 'mobile');    //手机

/* ecjia验证登录key */
define('APP_KEY', '123456');

if (!defined('CAL_GREGORIAN')) {
    define('CAL_GREGORIAN', 0);
}

if (!defined('CAL_JULIAN')) {
    define('CAL_JULIAN', 1);
}

if (!defined('CAL_JEWISH')) {
    define('CAL_JEWISH', 2);
}

if (!defined('CAL_FRENCH')) {
    define('CAL_FRENCH', 3);
}

if (!defined('CAL_NUM_CALS')) {
    define('CAL_NUM_CALS', 4);
}

if (!defined('CAL_DOW_DAYNO')) {
    define('CAL_DOW_DAYNO', 0);
}

if (!defined('CAL_DOW_SHORT')) {
    define('CAL_DOW_SHORT', 1);
}

if (!defined('CAL_DOW_LONG')) {
    define('CAL_DOW_LONG', 2);
}

if (!defined('CAL_MONTH_GREGORIAN_SHORT')) {
    define('CAL_MONTH_GREGORIAN_SHORT', 0);
}

if (!defined('CAL_MONTH_GREGORIAN_LONG')) {
    define('CAL_MONTH_GREGORIAN_LONG', 1);
}

if (!defined('CAL_MONTH_JULIAN_SHORT')) {
    define('CAL_MONTH_JULIAN_SHORT', 2);
}

if (!defined('CAL_MONTH_JULIAN_LONG')) {
    define('CAL_MONTH_JULIAN_LONG', 3);
}

if (!defined('CAL_MONTH_JEWISH')) {
    define('CAL_MONTH_JEWISH', 4);
}

if (!defined('CAL_MONTH_FRENCH')) {
    define('CAL_MONTH_FRENCH', 5);
}

if (!defined('CAL_EASTER_DEFAULT')) {
    define('CAL_EASTER_DEFAULT', 0);
}

if (!defined('CAL_EASTER_ROMAN')) {
    define('CAL_EASTER_ROMAN', 1);
}

if (!defined('CAL_EASTER_ALWAYS_GREGORIAN')) {
    define('CAL_EASTER_ALWAYS_GREGORIAN', 2);
}

if (!defined('CAL_EASTER_ALWAYS_JULIAN')) {
    define('CAL_EASTER_ALWAYS_JULIAN', 3);
}

if (!defined('CAL_JEWISH_ADD_ALAFIM_GERESH')) {
    define('CAL_JEWISH_ADD_ALAFIM_GERESH', 2);
}

if (!defined('CAL_JEWISH_ADD_ALAFIM')) {
    define('CAL_JEWISH_ADD_ALAFIM', 4);
}

if (!defined('CAL_JEWISH_ADD_GERESHAYIM')) {
    define('CAL_JEWISH_ADD_GERESHAYIM', 8);
}

if (!defined('CROSS_BORDER')) {
    define('CROSS_BORDER', file_exists(dirname(__DIR__) . '/Custom/CrossBorder/Services/CbecService.php'));
}

if (!defined('PERSONAL_MERCHANTS')) {
    define('PERSONAL_MERCHANTS', file_exists(dirname(__DIR__) . '/Modules/Seller/Services/PermerService.php'));
}

if (!defined('GROUPBUY_LEADER')) {
    define('GROUPBUY_LEADER', file_exists(dirname(__DIR__) . '/Modules/Cgroup'));// 社区团购
    define('NOT_OUT_OF', 0);// 未出账
    define('IS_OUT_OF', 1);// 已出账
}

define('POST_ORDER_UNSIGN', 0); // 待签收
define('POST_ORDER_SIGNED', 1); // 已签收

/* 优惠券类型 */
define('VOUCHER_LOGIN', 1);     //注册券
define('VOUCHER_SHOPING', 2);     //购物券
define('VOUCHER_ALL', 3);     //全场券
define('VOUCHER_USER', 4);     //会员券
define('VOUCHER_SHIPPING', 5);     //免邮券
define('VOUCHER_SHOP_CONLLENT', 6);     //店铺关注赠券
define('VOUCHER_GROUPBUY', 7);     //社团券

/* 跨境货源 */
define('SOURCE_DOMESTIC', '国内仓库');
define('SOURCE_FTA', '自贸区');
define('SOURCE_ABROAD', '海外直邮');

/* 优惠券状态 */
define('COUPON_STATUS_EDIT', 1); //未生效，编辑中
define('COUPON_STATUS_EFFECTIVE', 2); //生效
define('COUPON_STATUS_OVERDUE', 3); //已过期
define('COUPON_STATUS_NULLIFY', 4); //已作废
