<?php

/**
 * 管理中心菜单数组
 */

//$modules['01_system']['user_keywords_list'] = 'keywords_manage.php?act=list';
$modules['01_system']['order_print_setting'] = 'tp_api.php?act=order_print_setting';
$modules['01_system']['01_shop_config'] = 'shop_config.php?act=list_edit';
$modules['01_system']['02_payment_list'] = 'payment.php?act=list';
$modules['01_system']['03_area_shipping'] = 'shipping.php?act=list';
$modules['01_system']['07_cron_schcron'] = 'cron.php?act=list';
//$modules['01_system']['08_friendlink_list'] = 'friend_link.php?act=list';
//$modules['01_system']['09_partnerlink_list'] = 'friend_partner.php?act=list';
//$modules['01_system']['sitemap'] = 'sitemap.php';
//$modules['01_system']['check_file_priv'] = 'check_file_priv.php?act=check';
$modules['01_system']['captcha_manage'] = 'captcha_manage.php?act=main';
$modules['01_system']['ucenter_setup'] = 'integrate.php?act=setup&code=ucenter';
//$modules['01_system']['navigator'] = 'navigator.php?act=list';
$modules['01_system']['seo'] = 'seo.php?act=index';
$modules['01_system']['10_filter_words'] = 'filter'; // 过滤词
$modules['01_system']['express_manage'] = 'express_manage.php?act=company';
$modules['01_system']['address_manage'] = 'address_manage';

if (CROSS_BORDER === true) { // 跨境多商户
    $modules['01_system']['04_country_list'] = 'country.php?act=list';
    $modules['01_system']['05_cross_warehouse_list'] = 'cross_warehouse.php?act=list';
}

$modules['02_cat_and_goods']['discuss_circle'] = 'discuss_circle.php?act=list';
$modules['02_cat_and_goods']['001_goods_setting'] = 'goods.php?act=step_up'; // 商品设置
$modules['02_cat_and_goods']['01_goods_list'] = 'goods.php?act=list';         // 商品列表
$modules['02_cat_and_goods']['03_category_manage'] = 'category.php?act=list';
$modules['02_cat_and_goods']['05_comment_manage'] = 'comment_manage.php?act=list';
$modules['02_cat_and_goods']['06_goods_brand'] = 'brand.php?act=list';
$modules['02_cat_and_goods']['08_goods_type'] = 'goods_type.php?act=manage';
$modules['02_cat_and_goods']['15_batch_edit'] = 'goods_batch.php?act=select'; // 商品批量修改
$modules['02_cat_and_goods']['gallery_album'] = 'gallery_album.php?act=list';//by kong
//$modules['02_cat_and_goods']['goods_report'] = 'goods_report.php?act=report_conf';//by kong 商品举报
$modules['02_cat_and_goods']['20_goods_lib'] = 'goods_lib.php?act=list';//by liu
$modules['02_cat_and_goods']['21_goods_lib_cat'] = 'goods_lib_cat.php?act=list';//by liu
$modules['02_cat_and_goods']['22_goods_keyword'] = 'goods_keyword.php?act=list';

$modules['02_promotion']['01_favourable_setting'] = 'favourable.php?act=step_up';
$modules['02_promotion']['02_marketing_center'] = 'favourable.php?act=marketing_center';
$modules['02_promotion']['02_snatch_list'] = 'snatch.php?act=list';
$modules['02_promotion']['03_seckill_list'] = 'seckill.php?act=list';
$modules['02_promotion']['04_bonustype_list'] = 'bonus.php?act=list';
$modules['02_promotion']['08_group_buy'] = 'group_buy.php?act=list';
//$modules['02_promotion']['09_topic'] = 'topic.php?act=list';
$modules['02_promotion']['09_crowdfunding'] = 'zc_project.php?act=list'; // 众筹
$modules['02_promotion']['10_auction'] = 'auction.php?act=list';
$modules['02_promotion']['12_favourable'] = 'favourable.php?act=list';
$modules['02_promotion']['14_package_list'] = 'package.php?act=list';
$modules['02_promotion']['15_exchange_goods'] = 'exchange_goods.php?act=list';
$modules['02_promotion']['16_presale'] = 'presale.php?act=list';
$modules['02_promotion']['17_coupons'] = 'coupons.php?act=list';
$modules['02_promotion']['18_value_card'] = 'value_card.php?act=list';// 储值卡 VC liu
$modules['02_promotion']['gift_gard_list'] = 'gift_gard.php?act=list';

$modules['03_goods_storage']['01_goods_storage_put'] = 'goods_inventory_logs.php?act=list&step=put';
$modules['03_goods_storage']['02_goods_storage_out'] = 'goods_inventory_logs.php?act=list&step=out';
$modules['04_order']['02_order_list'] = 'order.php?act=list';
//$modules['04_order']['06_undispose_booking'] = 'goods_booking.php?act=list_all';
$modules['04_order']['08_add_order'] = 'order.php?act=add';
$modules['04_order']['09_delivery_order'] = 'order.php?act=delivery_list';
$modules['04_order']['10_back_order'] = 'order.php?act=back_list';
//$modules['04_order']['13_complaint'] = 'complaint.php?act=complaint_conf';
$modules['04_order']['11_order_detection'] = 'order.php?act=order_detection';
$modules['04_order']['11_add_order'] = 'mc_order.php';
$modules['04_order']['11_return_config'] = 'shop_config.php?act=return_config'; // 退款设置
$modules['04_order']['12_back_apply'] = 'order.php?act=return_list';
$modules['04_order']['11_order_delayed'] = 'order_delay.php?act=list'; //延迟收货
$modules['04_order']['14_export_list'] = 'order/export'; // 订单导出export
$modules['04_order']['pay_log_list'] = 'pay_log'; // 支付单列表

//$modules['05_banner']['ad_position'] = 'ad_position.php?act=list';
//$modules['05_banner']['ad_list'] = 'ads.php?act=list';

/*统计*/
$modules['06_stats']['report_guest'] = 'guest_stats.php?act=list';           //客户统计
$modules['06_stats']['report_order'] = 'order_stats.php?act=list';           //订单统计
$modules['06_stats']['sale_list'] = 'sale_list.php?act=list';             //销售明细
$modules['06_stats']['report_users'] = 'users_order.php?act=order_num';      //会员排行
$modules['06_stats']['visit_buy_per'] = 'visit_sold.php?act=list';            //访问购买率
$modules['06_stats']['exchange_count'] = 'exchange_detail.php?act=detail';     //积分明细

$modules['06_stats']['01_shop_stats'] = 'shop_stats.php?act=new';     //店铺统计
$modules['06_stats']['02_user_stats'] = 'user_stats.php?act=new';     //会员统计
$modules['06_stats']['03_sell_analysis'] = 'sell_analysis.php?act=sales_volume';     //销售分析
$modules['06_stats']['04_industry_analysis'] = 'industry_analysis.php?act=list';     //行业分析

/*资金*/
$modules['31_fund']['01_summary_of_money'] = 'fund_stats.php?act=summary_of_money';
$modules['31_fund']['02_member_account'] = 'fund_stats.php?act=member_account';
$modules['31_fund']['05_finance_analysis'] = 'finance_analysis.php?act=settlement_stats';     //财务统计

$modules['07_content']['03_article_list'] = 'article.php?act=list';
$modules['07_content']['02_articlecat_list'] = 'articlecat.php?act=list';
//$modules['07_content']['article_auto'] = 'article_auto.php?act=list'; // 文章自动发布
//$modules['07_content']['03_visualnews'] = 'visualnews.php?act=visual'; //cms频道可视化

$modules['08_members']['03_users_list'] = 'users.php?act=list';
$modules['08_members']['05_user_rights'] = 'user_rights'; // 会员权益
$modules['08_members']['05_user_rank_list'] = 'user_rank.php?act=list';//会员等级
$modules['08_members']['06_list_integrate'] = 'integrate.php?act=list';
$modules['08_members']['08_unreply_msg'] = 'user_msg.php?act=list_all';
$modules['08_members']['09_user_account'] = 'user_account.php?act=list';
$modules['08_members']['10_user_account_manage'] = 'user_account_manage.php?act=list';
//$modules['08_members']['13_user_baitiao_info'] = 'user_baitiao_log.php?act=list'; // 会员白条
$modules['08_members']['15_user_vat_info'] = 'user_vat.php?act=list'; //liu
//$modules['08_members']['16_reg_fields'] = 'reg_fields.php?act=list';
$modules['08_members']['12_user_address_list'] = 'user_address_log.php?act=list';
//$modules['08_members']['17_mass_sms'] = 'mass_sms.php?act=list'; // 短信群发

$modules['10_priv_admin']['admin_logs'] = 'admin_logs.php?act=list';
$modules['10_priv_admin']['01_admin_list'] = 'privilege.php?act=list';
$modules['10_priv_admin']['admin_role'] = 'role.php?act=list';
$modules['10_priv_admin']['agency_list'] = 'agency.php?act=list';

if (file_exists(MOBILE_KEFU)) {
    $modules['10_priv_admin']['services_list'] = 'services.php?act=list'; // 客服管理
}
$modules['10_priv_admin']['admin_message'] = 'message.php?act=list';   //管理员留言

$modules['15_rec']['affiliate'] = 'affiliate.php?act=list';
$modules['15_rec']['affiliate_ck'] = 'affiliate_ck.php?act=list';
// 推荐注册送优惠券
$modules['15_rec']['affiliate_coupons'] = 'affiliate_coupons/';

$modules['24_sms']['01_sms_setting'] = 'sms_setting.php?act=step_up';          // 短信设置
$modules['24_sms']['01_sms_list'] = 'sms';        // 短信插件
$modules['24_sms']['dscsms_configure'] = 'dscsms_configure.php?act=list';          // 短信模板
if (config('app.distributor') != 'huawei') {
    $modules['25_file']['oss_configure'] = 'oss_configure.php?act=list';          // 阿里OSS
}
if (config('app.distributor') != 'aliyun') {
    $modules['25_file']['obs_configure'] = 'obs_configure.php?act=list';          // 华为OBS
}
$modules['25_file']['cos_configure'] = 'cos_configure.php?act=list'; // 腾讯云COS
$modules['25_file']['01_cloud_setting'] = 'cloud_setting.php?act=step_up';    // 文件存储设置
$modules['26_login']['website'] = 'touch_oauth';         // 社会化登录管理
$modules['21_cloud']['01_cloud_services'] = 'index.php?act=cloud_services';

// PC 菜单
$modules['pc_01_setting']['01_pc_shop_config'] = 'shop_config.php?act=pc_shop_config';
$modules['pc_01_setting']['02_pc_goods_config'] = 'shop_config.php?act=pc_shop_config&shop_group=pc_goods_config';
$modules['pc_02_function']['01_ad_position'] = 'ad_position.php?act=list';// PC 广告位
$modules['pc_02_function']['01_merchants_steps_list'] = 'merchants_steps.php?act=list';// 商家入驻流程
$modules['pc_02_function']['06_undispose_booking'] = 'goods_booking.php?act=list_all';// 缺货登记
$modules['pc_02_function']['08_friendlink_list'] = 'friend_link.php?act=list'; // 友情链接
$modules['pc_02_function']['09_partnerlink_list'] = 'friend_partner.php?act=list';// 合作伙伴
$modules['pc_02_function']['goods_report'] = 'goods_report.php?act=report_conf';// 商品举报
$modules['pc_02_function']['13_complaint'] = 'complaint.php?act=complaint_conf'; // 投诉
$modules['pc_02_function']['sale_notice'] = 'sale_notice.php?act=list'; //降价通知
$modules['pc_02_function']['navigator'] = 'navigator.php?act=list';// 自定义导航栏
$modules['pc_02_function']['user_keywords_list'] = 'keywords_manage.php?act=list'; //用户检索记录
$modules['pc_02_function']['13_user_baitiao_info'] = 'user_baitiao_log.php?act=list'; // 会员白条
$modules['pc_02_function']['16_reg_fields'] = 'reg_fields.php?act=list'; // 会员注册项
$modules['pc_02_function']['17_shipping_cac'] = 'shipping_area.php?act=list&shipping_code=cac'; // 上门取货

if ($GLOBALS['_CFG']['openvisual'] == 1) {
    $modules['pc_03_visual']['01_visualhome'] = 'visualhome.php?act=list';//PC可视化装修首页
}
$modules['pc_03_visual']['03_visualnews'] = 'visualnews.php?act=visual'; //cms频道可视化
$modules['pc_03_visual']['09_topic'] = 'topic.php?act=list'; // 专题管理


// 手机端菜单
$modules['20_ectouch']['02_touch_nav_admin'] = 'touch_nav?device=h5'; // 导航管理
$modules['20_ectouch']['03_touch_ads'] = 'touch_ads.php?act=list';
$modules['20_ectouch']['04_touch_ad_position'] = 'touch_ad_position.php?act=list';
$modules['20_ectouch']['05_touch_dashboard'] = 'touch_visual?device=h5'; // 手机端可视化
$modules['20_ectouch']['05_touch_page_nav'] = 'touch_page_nav?device=h5';
$modules['20_ectouch']['06_consult_set'] = 'consult_set'; // 首页咨询设置

if (file_exists(MOBILE_APP)) {
    // 混合App菜单
    //$modules['25_app']['01_app_config'] = 'app/index';
    $modules['25_app']['02_app_ad_position'] = 'app/ad_position_list';
    $modules['25_app']['05_app_client_manage'] = 'app/client_manage';
    $modules['25_app']['06_app_touch_visual'] = 'touch_visual?device=app';
    $modules['25_app']['06_app_touch_page_nav'] = 'touch_page_nav?device=app';
    $modules['25_app']['07_app_touch_nav'] = 'touch_nav?device=app'; // app导航管理
    $modules['25_app']['08_app_client_setting'] = 'app/client_setting';
}

if (file_exists(MOBILE_TEAM)) {
    $modules['02_promotion']['18_team'] = 'team';   //拼团
}

if (file_exists(MOBILE_BARGAIN)) {
    $modules['02_promotion']['19_bargain'] = 'bargain';   //砍价
}

/* 邮件管理 */
$modules['16_email_manage']['01_mail_settings'] = 'shop_config.php?act=mail_settings';
$modules['16_email_manage']['02_attention_list'] = 'attention_list.php?act=list';
$modules['16_email_manage']['03_email_list'] = 'email_list.php?act=list';
$modules['16_email_manage']['04_magazine_list'] = 'magazine_list.php?act=list';
$modules['16_email_manage']['05_view_sendlist'] = 'view_sendlist.php?act=list';
$modules['16_email_manage']['06_mail_template_manage'] = 'mail_template.php?act=list';

// 店铺管理
$modules['17_merchants']['01_seller_stepup'] = 'merchants_steps.php?act=step_up';         // 店铺设置
$modules['17_merchants']['02_merchants_users_list'] = 'merchants_users_list.php?act=list';    // 入驻商家列表
$modules['17_merchants']['03_merchants_commission'] = 'merchants_commission.php?act=list';    // 商家商品佣金结算
$modules['17_merchants']['09_seller_domain'] = 'seller_domain.php?act=list';         // 二级域名列表
$modules['17_merchants']['12_seller_account'] = 'merchants_account.php?act=list&act_type=merchants_seller_account'; //商家账户管理
$modules['17_merchants']['13_comment_seller_rank'] = 'comment_seller.php?act=list';
$modules['17_merchants']['12_seller_store'] = 'offline_store.php?act=list&type=1';
$modules['17_merchants']['16_seller_users_real'] = 'user_real.php?act=list&user_type=1';
if (file_exists(MODULES_DIVIDE)) {
    $modules['17_merchants']['17_seller_divide'] = 'seller_divide'; // 二级商户号管理
    $modules['17_merchants']['18_seller_divide_account'] = 'seller_divide/account_log'; // 二级商户资金管理
}

/* 自营信息 */
$modules['19_self_support']['01_self_offline_store'] = 'offline_store.php?act=list';
$modules['19_self_support']['02_self_order_stats'] = 'offline_store.php?act=order_stats';
$modules['19_self_support']['03_self_support_info'] = 'index.php?act=merchants_first'; // 自营设置

//模板
//$modules['12_template']['02_template_select'] = 'template.php?act=list';
//$modules['12_template']['04_template_library'] = 'template.php?act=library';
//$modules['12_template']['06_template_backup'] = 'template.php?act=backup_setting';

//快递鸟、电子面单
$modules['27_interface']['express_list'] = 'express';
$modules['27_interface']['open_api'] = 'open_api.php?act=list';          // 开放接口
$modules['27_interface']['cloud_api'] = 'cloudapi';//贡云接口

//首页
$modules['00_home']['01_admin_core'] = 'index.php?act=main';
$modules['00_home']['02_operation_flow'] = 'index.php?act=operation_flow';
$modules['00_home']['03_novice_guide'] = 'index.php?act=novice_guide';

// 商城-商品-商品标签
$modules['02_cat_and_goods']['25_goods_label'] = 'goodslabel'; // 2.2.1 标签

// 商城-商品-商品服务标签
$modules['02_cat_and_goods']['28_goods_services_label'] = 'goodsserviceslabel'; // 2.5.2 服务标签

// 左侧菜单分类
$menu_top['home'] = '00_home';//首页
$menu_top['menuplatform'] = '01_system,05_banner,07_content,08_members,10_priv_admin,16_email_manage,19_self_support';//平台
$menu_top['menushopping'] = '02_cat_and_goods,02_promotion,04_order,15_rec,17_merchants,18_batch_manage,03_goods_storage,supply_and_demand,18_region_store';//商城
if (MODULES_PC) {
    $menu_top['pc'] = 'pc_01_setting,pc_02_function,pc_03_visual'; // PC
}
/* 供应商 */
if (file_exists(SUPPLIERS)) {
    $menu_top['suppliers'] = '18_suppliers,19_suppliers_goods,20_suppliers_promotion,21_suppliers_order,22_suppliers_stats,23_suppliers_set';//供应商
}

$menu_top['finance'] = '06_stats,06_seo';//财务
$menu_top['third_party'] = '24_sms,25_file,26_login,27_interface';//第三方服务

// 左侧菜单分类
$menu_top['ectouch'] = '20_ectouch,22_wechat';//手机

//微分销
if (file_exists(MOBILE_DRP)) {
    $menu_top['ectouch'] .= ',23_drp,23_drp_store';
}

//小程序
if (file_exists(MOBILE_WXAPP)) {
    $menu_top['ectouch'] .= ',24_wxapp';

    if (file_exists(WXAPP_MEDIA)) {
        $menu_top['ectouch'] .= ',28_wxapp_media';
    }
}

//APP
if (file_exists(MOBILE_APP)) {
    $menu_top['ectouch'] .= ',25_app';
}

//微信小商店
if (file_exists(MOBILE_WXSHOP)) {
    $menu_top['ectouch'] .= ',26_wxshop';
}

// 加载Modules模块自定义菜单
$modules_list = glob(app_path('Modules/*/menu.php'));
foreach ($modules_list as $m) {
    require $m;
}

// 加载开发模块自定义菜单
$list = glob(app_path('Custom/*/menu.php'));
foreach ($list as $m) {
    require $m;
}

$menu_top['menuinformation'] = '21_cloud';//资源

$GLOBALS['modules'] = $modules;
$GLOBALS['menu_top'] = $menu_top;
