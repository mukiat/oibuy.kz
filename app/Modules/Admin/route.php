<?php

use Illuminate\Support\Facades\Route;

Route::prefix(ADMIN_PATH)->group(function () {
    Route::redirect('/', '/' . ADMIN_PATH . '/index.php');
    Route::any('account_log.php', 'AccountLogController@index');
    Route::any('ad_position.php', 'AdPositionController@index');
    Route::any('admin_logs.php', 'AdminLogsController@index');
    Route::any('ads.php', 'AdsController@index');
    Route::any('adsense.php', 'AdsenseController@index');
    Route::any('affiliate.php', 'AffiliateController@index');
    Route::any('affiliate_ck.php', 'AffiliateCkController@index');
    Route::any('agency.php', 'AgencyController@index');
    Route::any('area_manage.php', 'AreaManageController@index');
    Route::any('article.php', 'ArticleController@index');
    Route::any('article_auto.php', 'ArticleAutoController@index');
    Route::any('articlecat.php', 'ArticlecatController@index');
    Route::any('attention_list.php', 'AttentionListController@index');
    Route::any('attribute.php', 'AttributeController@index');
    Route::any('auction.php', 'AuctionController@index');
    Route::any('baitiao_batch.php', 'BaitiaoBatchController@index');
    Route::any('express_manage.php', 'ExpressManageController@index')->name('admin.express_manage');
    Route::resource('address_manage', 'AddressManageController');
    Route::post('address_manage/delete', 'AddressManageController@destroy')->name('address_manage.delete');;

    // 砍价模块
    Route::prefix('bargain')->name('admin/bargain/')->middleware('admin_priv:bargain_manage')->group(function () {
        Route::any('/', 'BargainController@index')->name('index');
        Route::any('addgoods', 'BargainController@addgoods')->name('addgoods');
        Route::any('searchgoods', 'BargainController@searchgoods')->name('searchgoods');
        Route::any('goodsinfo', 'BargainController@goodsinfo')->name('goodsinfo');
        Route::any('setattributetable', 'BargainController@setattributetable')->name('setattributetable');
        Route::any('editgoods', 'BargainController@editgoods')->name('editgoods');
        Route::any('removegoods', 'BargainController@removegoods')->name('removegoods');
        Route::any('bargainlog', 'BargainController@bargainlog')->name('bargainlog');
        Route::any('bargain_statistics', 'BargainController@bargain_statistics')->name('bargain_statistics');
    });

    // 推荐注册送优惠券
    Route::any('affiliate_coupons', 'ConfigController@affiliate_coupons')->name('admin.affiliate_coupons')->middleware('admin_priv:affiliate_coupons');

    Route::any('bonus.php', 'BonusController@index');
    Route::any('brand.php', 'BrandController@index');
    Route::any('captcha_manage.php', 'CaptchaManageController@index');
    Route::any('card.php', 'CardController@index');
    Route::any('category.php', 'CategoryController@index');
    Route::any('category_store.php', 'CategoryStoreController@index');
    Route::any('check_file_priv.php', 'CheckFilePrivController@index');
    Route::any('cloud.php', 'CloudController@index');
    Route::any('comment_manage.php', 'CommentManageController@index');
    Route::any('comment_seller.php', 'CommentSellerController@index');
    Route::any('complaint.php', 'ComplaintController@index');
    Route::any('coupons.php', 'CouponsController@index');
    Route::any('cron.php', 'CronController@index');
    Route::any('dialog.php', 'DialogController@index');
    Route::any('discuss_circle.php', 'DiscussCircleController@index');
    Route::any('ecjia_config.php', 'EcjiaConfigController@index');
    Route::any('edit_languages.php', 'EditLanguagesController@index');
    Route::any('email_list.php', 'EmailListController@index');
    Route::any('entry_criteria.php', 'EntryCriteriaController@index');
    Route::any('exchange_detail.php', 'ExchangeDetailController@index');
    Route::any('exchange_goods.php', 'ExchangeGoodsController@index');
    Route::any('favourable.php', 'FavourableController@index');
    Route::any('flow_stats.php', 'FlowStatsController@index');
    Route::any('friend_link.php', 'FriendLinkController@index');
    Route::any('friend_partner.php', 'FriendPartnerController@index');
    Route::any('gallery_album.php', 'GalleryAlbumController@index');
    Route::any('gen_goods_script.php', 'GenGoodsScriptController@index');
    Route::any('get_ajax_content.php', 'GetAjaxContentController@index');
    Route::any('get_password.php', 'GetPasswordController@index');
    Route::any('gift_gard.php', 'GiftGardController@index');
    Route::any('goods.php', 'GoodsController@index');
    Route::any('goods_area_attr.php', 'GoodsAreaAttrController@index');
    Route::any('goods_area_attr_batch.php', 'GoodsAreaAttrBatchController@index');
    Route::any('goods_area_batch.php', 'GoodsAreaBatchController@index');
    Route::any('goods_attr_price.php', 'GoodsAttrPriceController@index');
    Route::any('goods_auto.php', 'GoodsAutoController@index');
    Route::any('goods_batch.php', 'GoodsBatchController@index');
    Route::any('goods_booking.php', 'GoodsBookingController@index');
    Route::any('goods_export.php', 'GoodsExportController@index');
    Route::any('goods_inventory_logs.php', 'GoodsInventoryLogsController@index');
    Route::any('goods_lib.php', 'GoodsLibController@index');
    Route::any('goods_lib_batch.php', 'GoodsLibBatchController@index');
    Route::any('goods_lib_cat.php', 'GoodsLibCatController@index');
    Route::any('goods_produts_area_batch.php', 'GoodsProdutsAreaBatchController@index');
    Route::any('goods_produts_batch.php', 'GoodsProdutsBatchController@index');
    Route::any('goods_produts_warehouse_batch.php', 'GoodsProdutsWarehouseBatchController@index');
    Route::any('goods_psi.php', 'GoodsPsiController@index');
    Route::any('goods_report.php', 'GoodsReportController@index');
    Route::any('goods_transport.php', 'GoodsTransportController@index');
    Route::any('goods_type.php', 'GoodsTypeController@index');
    Route::any('goods_warehouse_attr.php', 'GoodsWarehouseAttrController@index');
    Route::any('goods_warehouse_attr_batch.php', 'GoodsWarehouseAttrBatchController@index');
    Route::any('goods_warehouse_batch.php', 'GoodsWarehouseBatchController@index');
    Route::any('goods_keyword.php', 'GoodsKeywordController@index');
    Route::any('group_buy.php', 'GroupBuyController@index');
    Route::any('guest_stats.php', 'GuestStatsController@index');
    Route::any('help.php', 'HelpController@index');
    Route::any('index.php', 'IndexController@index')->name('admin.home');
    Route::any('integrate.php', 'IntegrateController@index');
    Route::any('keywords_manage.php', 'KeywordsManageController@index');
    Route::any('lottery.php', 'LotteryController@index');
    Route::any('magazine_list.php', 'MagazineListController@index');
    Route::any('mail_template.php', 'MailTemplateController@index');
    Route::any('mass_sms.php', 'MassSmsController@index');
    Route::any('mc_order.php', 'McOrderController@index');
    Route::any('mc_user.php', 'McUserController@index');
    Route::any('merchants_account.php', 'MerchantsAccountController@index');
    Route::any('merchants_brand.php', 'MerchantsBrandController@index');
    Route::any('merchants_commission.php', 'MerchantsCommissionController@index');
    Route::any('merchants_custom.php', 'MerchantsCustomController@index');
    Route::any('merchants_navigator.php', 'MerchantsNavigatorController@index');
    Route::any('merchants_percent.php', 'MerchantsPercentController@index');
    Route::any('merchants_privilege.php', 'MerchantsPrivilegeController@index');
    Route::any('merchants_steps.php', 'MerchantsStepsController@index');
    Route::any('merchants_template.php', 'MerchantsTemplateController@index');
    Route::any('merchants_upgrade.php', 'MerchantsUpgradeController@index');
    Route::any('merchants_users_list.php', 'MerchantsUsersListController@index');
    Route::any('merchants_window.php', 'MerchantsWindowController@index');
    Route::any('message.php', 'MessageController@index');
    Route::any('navigator.php', 'NavigatorController@index');
    Route::any('notice_logs.php', 'NoticeLogsController@index');
    Route::any('offline_store.php', 'OfflineStoreController@index');
    Route::any('open_api.php', 'OpenApiController@index');
    Route::any('order.php', 'OrderController@index');
    // 批量发货
    Route::any('order_delivery_download', 'OrderDeliveryController@order_delivery_download')->name('admin/order_delivery_download');
    Route::any('download_excel', 'OrderDeliveryController@download_excel')->name('admin/download_excel');
    Route::any('order_delivery_import', 'OrderDeliveryController@order_delivery_import')->name('admin/order_delivery_import');

    // 新增订单导出
    Route::get('order/export', 'OrderExportController@export')->name('admin/order/export')->middleware('admin_priv:order_export');
    Route::post('order/export', 'OrderExportController@exportHandler')->name('admin/order/export')->middleware('admin_priv:order_export');

    // 导出历史
    Route::get('export/history', 'ExportHistoryController@index')->name('admin/export_history');
    Route::post('export/download', 'ExportHistoryController@download')->name('admin/export_history/download');
    Route::post('export/delete', 'ExportHistoryController@delete')->name('admin/export_history/delete');

    /**
     * 支付单列表
     */
    Route::prefix('pay_log')->name('admin/pay_log/')->middleware('admin_priv:pay_log')->group(function () {
        Route::any('/', 'PayLogController@index')->name('index');
        Route::any('detail', 'PayLogController@detail')->name('detail');
    });

    Route::any('order_delay.php', 'OrderDelayController@index');
    Route::any('order_stats.php', 'OrderStatsController@index');
    Route::any('oss_configure.php', 'OssConfigureController@index');
    Route::any('cos_configure.php', 'CosConfigureController@index');
    Route::any('obs_configure.php', 'ObsConfigureController@index');
    Route::any('cloud_setting.php', 'CloudSettingController@index');
    Route::any('pack.php', 'PackController@index');
    Route::any('package.php', 'PackageController@index');
    Route::any('pay_card.php', 'PayCardController@index');
    Route::any('payment.php', 'PaymentController@index');
    Route::any('picture_batch.php', 'PictureBatchController@index');
    Route::any('presale.php', 'PresaleController@index');
    Route::any('presale_cat.php', 'PresaleCatController@index');
    Route::any('print_batch.php', 'PrintBatchController@index');
    Route::any('privilege.php', 'PrivilegeController@index');
    Route::any('reg_fields.php', 'RegFieldsController@index');
    Route::any('region.php', 'RegionController@index');
    Route::any('region_area.php', 'RegionAreaController@index');
    Route::any('region_store.php', 'RegionStoreController@index');
    Route::any('role.php', 'RoleController@index');
    Route::any('shop_stats.php', 'ShopStatsController@index');
    Route::any('user_stats.php', 'UserStatsController@index');
    Route::any('sell_analysis.php', 'SellAnalysisController@index');
    Route::any('industry_analysis.php', 'IndustryAnalysisController@index');
    Route::any('sale_general.php', 'SaleGeneralController@index');
    Route::any('sale_list.php', 'SaleListController@index');
    Route::any('sale_notice.php', 'SaleNoticeController@index');
    Route::any('sale_order.php', 'SaleOrderController@index');
    Route::any('search_log.php', 'SearchLogController@index');
    Route::any('searchengine_stats.php', 'SearchengineStatsController@index');
    Route::any('seckill.php', 'SeckillController@index');
    Route::any('seller_apply.php', 'SellerApplyController@index');
    Route::any('seller_domain.php', 'SellerDomainController@index');
    Route::any('seller_grade.php', 'SellerGradeController@index');
    Route::any('seller_shop_bg.php', 'SellerShopBgController@index');
    Route::any('seller_shop_slide.php', 'SellerShopSlideController@index');
    Route::any('seo.php', 'SeoController@index');
    Route::any('services.php', 'ServicesController@index');
    Route::any('set_floor_brand.php', 'SetFloorBrandController@index');
    Route::any('shipping.php', 'ShippingController@index');
    Route::any('shipping_area.php', 'ShippingAreaController@index');
    Route::any('shop_config.php', 'ShopConfigController@index');
    Route::any('shophelp.php', 'ShophelpController@index');
    Route::any('shopinfo.php', 'ShopinfoController@index');
    //Route::any('sitemap.php', 'SitemapController@index');
    Route::any('snatch.php', 'SnatchController@index');
    Route::any('table_prefix.php', 'TablePrefixController@index');
    Route::any('tag_manage.php', 'TagManageController@index');

    /**
     * 商家二级商户
     */
    Route::prefix('seller_divide')->name('admin/seller_divide/')->group(function () {
        // 二级商户号管理与进件申请
        Route::any('/', 'SellerDivideController@index')->name('index')->middleware('admin_priv:seller_divide');
        Route::any('apply', 'SellerDivideController@apply')->name('apply')->middleware('admin_priv:seller_divide_apply', 'limit_form_repeat');
        Route::any('update', 'SellerDivideController@update')->name('update')->middleware('admin_priv:seller_divide');
        Route::any('apply_list', 'SellerDivideController@apply_list')->name('apply_list')->middleware('admin_priv:seller_divide_apply');
        Route::any('apply_detail', 'SellerDivideController@apply_detail')->name('apply_detail')->middleware('admin_priv:seller_divide_apply');
        Route::any('apply_verify', 'SellerDivideController@apply_verify')->name('apply_verify')->middleware('admin_priv:seller_divide_apply');
        Route::any('sync_apply', 'SellerDivideController@sync_apply')->name('sync_apply')->middleware('admin_priv:seller_divide_apply');
        // 订单分账
        Route::any('order_divide', 'SellerDivideController@order_divide')->name('order_divide')->middleware('admin_priv:order_divide');
        Route::any('order_divide_detail', 'SellerDivideController@order_divide_detail')->name('order_divide_detail')->middleware('admin_priv:order_divide');
        // 资金管理
        Route::any('account_log', 'SellerDivideController@account_log')->name('account_log')->middleware('admin_priv:seller_divide_account');
        Route::any('account_log_update', 'SellerDivideController@account_log_update')->name('account_log_update')->middleware('admin_priv:seller_divide_account');
        Route::any('account_log_detail', 'SellerDivideController@account_log_detail')->name('account_log_detail')->middleware('admin_priv:seller_divide_account');
        Route::any('sync_account_log', 'SellerDivideController@sync_account_log')->name('sync_account_log')->middleware('admin_priv:seller_divide_account');
    });

    /**
     * 短信插件
     */
    Route::prefix('sms')->name('admin.sms.')->middleware('admin_priv:sms_setting')->group(function () {
        Route::get('/', 'SmsController@index')->name('index');
        Route::any('edit', 'SmsController@edit')->name('edit');
        Route::any('uninstall', 'SmsController@uninstall')->name('uninstall');
    });
    Route::any('sms_setting.php', 'SmsSettingController@index'); // 短信设置
    Route::any('dscsms_configure.php', 'DscsmsConfigureController@index'); // 短信模板

    /**
     * 快递跟踪插件
     */
    Route::prefix('express')->name('admin.express.')->middleware('admin_priv:express_setting')->group(function () {
        Route::get('/', 'ExpressController@index')->name('index');
        Route::any('edit', 'ExpressController@edit')->name('edit');
        Route::post('update', 'ExpressController@update')->name('update');
        Route::any('uninstall', 'ExpressController@uninstall')->name('uninstall');
    });

    /* 批发 start */
    Route::any('suppliers.php', 'SuppliersController@index');
    Route::any('suppliers_account.php', 'SuppliersAccountController@index');
    Route::any('suppliers_commission.php', 'SuppliersCommissionController@index');
    Route::any('suppliers_sale_list.php', 'SuppliersSaleListController@index');
    Route::any('suppliers_stats.php', 'SuppliersStatsController@index');
    Route::middleware('web')->prefix('suppliers')->group(function () {
        Route::any('basic_set', 'SuppliersBasicSettingController@index')->name('basic_set');
        Route::any('basic_receive', 'SuppliersBasicSettingController@receive')->name('basic_receive');
        Route::any('basic_del', 'SuppliersBasicSettingController@del')->name('basic_del');
    });


    Route::any('wholesale.php', 'WholesaleController@index');
    Route::any('wholesale_cat.php', 'WholesaleCatController@index');
    Route::any('wholesale_goods_produts_batch.php', 'WholesaleGoodsProdutsBatchController@index');
    Route::any('wholesale_order.php', 'WholesaleOrderController@index');
    Route::any('wholesale_purchase.php', 'WholesalePurchaseController@index');
    /* 批发 end */

    // 贡云 后台配置路由
    Route::middleware('web')->prefix('cloudapi')->group(function () {
        Route::any('/', 'CloudApiController@index')->name('cloudapi.index');
        Route::any('update', 'CloudApiController@update')->name('cloudapi.update');
    });

    // 拼团模块
    Route::prefix('team')->name('admin/team/')->middleware('admin_priv:team_manage')->group(function () {
        Route::any('/', 'TeamController@index')->name('index');
        Route::any('addgoods', 'TeamController@addgoods')->name('addgoods');
        Route::any('searchgoods', 'TeamController@searchgoods')->name('searchgoods');
        Route::any('editgoods', 'TeamController@editgoods')->name('editgoods');
        Route::any('removegoods', 'TeamController@removegoods')->name('removegoods');
        Route::any('category', 'TeamController@category')->name('category');
        Route::any('addcategory', 'TeamController@addcategory')->name('addcategory');
        Route::any('removecategory', 'TeamController@removecategory')->name('removecategory');
        Route::any('editstatus', 'TeamController@editstatus')->name('editstatus');
        Route::any('teaminfo', 'TeamController@teaminfo')->name('teaminfo');
        Route::any('teamorder', 'TeamController@teamorder')->name('teamorder');
        Route::any('removeteam', 'TeamController@removeteam')->name('removeteam');
        Route::any('teamrecycle', 'TeamController@teamrecycle')->name('teamrecycle');
        Route::any('recycleegoods', 'TeamController@recycleegoods')->name('recycleegoods');
    });

    Route::any('template.php', 'TemplateController@index');
    Route::any('template_mall.php', 'TemplateMallController@index');
    Route::any('topic.php', 'TopicController@index');
    // 手机端授权登录
    Route::prefix('touch_oauth')->name('admin/touch_oauth/')->middleware('admin_priv:oauth_admin')->group(function () {
        Route::any('/', 'TouchOauthController@index')->name('index');
        Route::any('edit', 'TouchOauthController@edit')->name('edit');
        Route::any('install', 'TouchOauthController@install')->name('install');
        Route::any('uninstall', 'TouchOauthController@uninstall')->name('uninstall');
    });
    // 自定义工具栏
    Route::prefix('touch_nav')->name('admin/touch_nav/')->group(function () {
        Route::any('/', 'TouchNavController@index')->name('index');
        Route::any('edit', 'TouchNavController@edit')->name('edit');
        Route::post('update', 'TouchNavController@update')->name('update');
        Route::post('delete', 'TouchNavController@delete')->name('delete');
        Route::any('nav_parent', 'TouchNavController@nav_parent')->name('nav_parent');
        Route::any('nav_parent_edit', 'TouchNavController@nav_parent_edit')->name('nav_parent_edit');
    });

    // 手机端可视化
    Route::prefix('touch_visual')->name('admin/touch_visual/')->group(function () {
        Route::any('/', 'TouchVisualController@index')->name('index');
        Route::post('view', 'TouchVisualController@view')->name('view');
        Route::post('article', 'TouchVisualController@article')->name('article');
        Route::get('article_list', 'TouchVisualController@article_list')->name('article_list');
        Route::post('product', 'TouchVisualController@product')->name('product');
        Route::post('checked', 'TouchVisualController@checked')->name('checked');
        Route::post('category', 'TouchVisualController@category')->name('category');
        Route::post('brand', 'TouchVisualController@brand')->name('brand');
        Route::post('thumb', 'TouchVisualController@thumb')->name('thumb');
        Route::post('geturl', 'TouchVisualController@geturl')->name('geturl');
        Route::post('seckill', 'TouchVisualController@seckill')->name('seckill');
        Route::post('store', 'TouchVisualController@store')->name('store');
        Route::post('storeIn', 'TouchVisualController@storeIn')->name('storeIn');
        Route::post('storeDown', 'TouchVisualController@storeDown')->name('storeDown');
        Route::post('default_index', 'TouchVisualController@default_index')->name('default_index');
        Route::post('previewModule', 'TouchVisualController@previewModule')->name('previewModule');
        Route::post('saveModule', 'TouchVisualController@saveModule')->name('saveModule');
        Route::post('cleanModule', 'TouchVisualController@cleanModule')->name('cleanModule');
        Route::post('restore', 'TouchVisualController@restore')->name('restore');
        Route::post('clean', 'TouchVisualController@clean')->name('clean');
        Route::post('save', 'TouchVisualController@save')->name('save');
        Route::post('del', 'TouchVisualController@del')->name('del');
        Route::post('make_gallery', 'TouchVisualController@make_gallery')->name('make_gallery');
        Route::post('picture', 'TouchVisualController@picture')->name('picture');
        Route::post('remove_picture', 'TouchVisualController@remove_picture')->name('remove_picture');
        Route::post('pic_upload', 'TouchVisualController@pic_upload')->name('pic_upload');
        Route::post('title', 'TouchVisualController@title')->name('title');
        Route::post('search', 'TouchVisualController@search')->name('search');
    });

    // 发现页管理
    Route::prefix('touch_page_nav')->name('admin/touch_page_nav/')->group(function () {
        Route::get('/', 'TouchPageNavController@index')->name('index');
        Route::get('modules', 'TouchPageNavController@modules')->name('modules');
        Route::post('update', 'TouchPageNavController@update')->name('update');
        Route::post('resort', 'TouchPageNavController@resort')->name('resort');
    });

    // 首页咨询设置
    Route::any('consult_set', 'ConsultSetController@consult_set')->name('admin/consult_set');

    // 消息提示
    Route::any('base/message', 'BaseController@message')->name('admin/base/message');
    // 修改分页
    Route::post('base/set_page', 'BaseController@set_page')->name('admin/base/set_page');
    // 搜索商品 - 获取下级分类列表
    Route::any('base/select_category', 'BaseController@select_category')->name('admin/base/select_category');
    // 搜索商品 - 获取品牌列表
    Route::any('base/search_brand', 'BaseController@search_brand')->name('admin/base/search_brand');

    Route::any('touch_ad_position.php', 'TouchAdPositionController@index');
    Route::any('touch_ads.php', 'TouchAdsController@index');
    Route::any('touch_topic.php', 'TouchTopicController@index');
    Route::any('tp_api.php', 'TpApiController@index');
    Route::any('transfer_manage.php', 'TransferManageController@index');
    Route::any('user_account.php', 'UserAccountController@index');
    Route::any('user_account_manage.php', 'UserAccountManageController@index');
    Route::any('user_address_log.php', 'UserAddressLogController@index');
    Route::any('user_baitiao_log.php', 'UserBaitiaoLogController@index');
    Route::any('user_msg.php', 'UserMsgController@index');
    Route::any('user_rank.php', 'UserRankController@index');
    Route::any('user_real.php', 'UserRealController@index');
    Route::any('user_vat.php', 'UserVatController@index');
    Route::any('users.php', 'UsersController@index');
    Route::any('users_order.php', 'UsersOrderController@index');
    Route::any('value_card.php', 'ValueCardController@index');
    Route::any('view_sendlist.php', 'ViewSendlistController@index');
    Route::any('virtual_card.php', 'VirtualCardController@index');
    Route::any('visit_sold.php', 'VisitSoldController@index');
    Route::any('visual_editing.php', 'VisualEditingController@index');
    Route::any('visualhome.php', 'VisualhomeController@index');
    Route::any('visualnews.php', 'VisualnewsController@index');
    Route::any('vote.php', 'VoteController@index');
    Route::any('warehouse.php', 'WarehouseController@index');
    Route::any('warehouse_order.php', 'WarehouseOrderController@index');
    Route::any('warehouse_shipping_mode.php', 'WarehouseShippingModeController@index');
    Route::any('zc_category.php', 'ZcCategoryController@index');
    Route::any('zc_initiator.php', 'ZcInitiatorController@index');
    Route::any('zc_project.php', 'ZcProjectController@index');
    Route::any('zc_topic.php', 'ZcTopicController@index');
    Route::any('editor.php', 'EditorController@index');
    Route::any('country.php', 'CountryController@index');

    if (CROSS_BORDER === true) { // 跨境多商户
        Route::any('cross_warehouse.php', 'CrossWarehouseController@index');
    }
    // Route::any('upgrade.php', 'UpgradeController@index')->name('admin.upgrade');

    // APP管理
    Route::prefix('app')->name('admin/app/')->middleware('web')->group(function () {
        // APP配置
        Route::any('/', 'AppController@index')->name('index');
        // 广告位列表
        Route::any('ad_position_list', 'AppController@ad_position_list')->name('ad_position_list');
        // 添加或更新广告位
        Route::post('update_position', 'AppController@update_position')->name('update_position');
        // 广告位信息
        Route::get('ad_position_info', 'AppController@ad_position_info')->name('ad_position_info');
        // 删除广告位
        Route::post('delete_position', 'AppController@delete_position')->name('delete_position');
        // 广告列表
        Route::any('ads_list', 'AppController@ads_list')->name('ads_list');
        // 添加或更新广告
        Route::post('update_ads', 'AppController@update_ads')->name('update_ads');
        // 广告信息
        Route::get('ads_info', 'AppController@ads_info')->name('ads_info');
        // 修改广告状态
        Route::get('change_ad_status', 'AppController@change_ad_status')->name('change_ad_status');
        // 删除广告
        Route::post('delete_ad', 'AppController@delete_ad')->name('delete_ad');
        // 客户端管理
        Route::any('client_manage', 'AppController@client_manage')->name('client_manage');
        // 添加编辑客户端
        Route::any('client_product', 'AppController@client_product')->name('client_product');
        // 产品管理列表
        Route::any('client_product_list', 'AppController@client_product_list')->name('client_product_list');
        // 产品管理详情
        Route::any('client_product_info', 'AppController@client_product_info')->name('client_product_info');
        // 删除客户端
        Route::any('del_client', 'AppController@del_client')->name('del_client');
        // 删除客户端产品
        Route::any('del_client_product', 'AppController@del_client_product')->name('del_client_product');
        // 客户端管理
        Route::any('client_setting', 'AppController@setting')->name('setting');
    });

    // 过滤词
    Route::prefix('filter')->name('admin/filter/')->group(function () {
        // 配置
        Route::any('/', 'FilterWordsController@index')->name('index');
        Route::any('update', 'FilterWordsController@update')->name('update');

        // 关键词
        Route::any('words', 'FilterWordsController@words')->name('words');
        Route::any('wordsupdate/{id?}', 'FilterWordsController@wordsupdate')->name('wordsupdate');
        Route::any('wordsdrop', 'FilterWordsController@wordsdrop')->name('wordsdrop');
        Route::any('batchdrop', 'FilterWordsController@batchdrop')->name('batchdrop');
        Route::any('batch', 'FilterWordsController@batch')->name('batch');
        Route::any('batchlist', 'FilterWordsController@batchlist')->name('batchlist');
        Route::any('download', 'FilterWordsController@download')->name('download');
        Route::any('error', 'FilterWordsController@error')->name('error');

        // 等级
        Route::any('ranks', 'FilterWordsController@ranks')->name('ranks');
        Route::any('ranksupdate/{id?}', 'FilterWordsController@ranksupdate')->name('ranksupdate');

        // 记录
        Route::any('logs', 'FilterWordsController@logs')->name('logs');
        Route::any('updatelogs', 'FilterWordsController@updatelogs')->name('updatelogs');
        Route::any('logsdrop', 'FilterWordsController@logsdrop')->name('logsdrop');
        // 统计
        Route::any('stats', 'FilterWordsController@stats')->name('stats');
    });

    // 会员权益管理
    Route::prefix('user_rights')->name('admin/user_rights/')->middleware('admin_priv:user_rights')->group(function () {
        Route::any('/', 'UserRightsController@index')->name('index');
        Route::any('list', 'UserRightsController@list')->name('list');
        Route::any('edit', 'UserRightsController@edit')->name('edit');
        Route::any('uninstall', 'UserRightsController@uninstall')->name('uninstall');
    });

    // 会员等级
    Route::prefix('user_rank')->name('admin/user_rank/')->group(function () {
        Route::any('/', 'UserRankController@index')->name('index');
        Route::any('index', 'UserRankController@index')->name('index');
        Route::any('edit', 'UserRankNewController@edit')->name('edit');
        Route::any('delete', 'UserRankNewController@delete')->name('delete');
        Route::any('bind_rights', 'UserRankNewController@bind_rights')->name('bind_rights');
        Route::any('edit_rights', 'UserRankNewController@edit_rights')->name('edit_rights');
        Route::any('unbind_rights', 'UserRankNewController@unbind_rights')->name('unbind_rights');
    });

    // 商品标签
    Route::prefix('goodslabel')->name('admin/goodslabel/')->middleware('admin_priv:goods_label')->group(function () {
        // 商品标签列表
        Route::any('/', 'GoodsLabelController@index')->name('list');
        Route::any('update', 'GoodsLabelController@update')->name('update');
        Route::any('batch', 'GoodsLabelController@batch')->name('batch');
        Route::any('update_status', 'GoodsLabelController@update_status')->name('update_status');
        Route::any('drop', 'GoodsLabelController@drop')->name('drop');
        Route::any('bind_goods', 'GoodsLabelController@bind_goods')->name('bind_goods');
        Route::any('unbind_goods', 'GoodsLabelController@unbind_goods')->name('unbind_goods');
        Route::any('select_goods', 'GoodsLabelController@select_goods')->name('select_goods');
    });

    // 商品服务标签
    Route::prefix('goodsserviceslabel')->name('admin/goodsserviceslabel/')->middleware('admin_priv:goods_services_label')->group(function () {
        // 商品标签列表
        Route::any('/', 'GoodsServicesLabelController@index')->name('list');
        Route::any('update', 'GoodsServicesLabelController@update')->name('update');
        Route::any('batch', 'GoodsServicesLabelController@batch')->name('batch');
        Route::any('update_status', 'GoodsServicesLabelController@update_status')->name('update_status');
        Route::any('drop', 'GoodsServicesLabelController@drop')->name('drop');
        Route::any('bind_goods', 'GoodsServicesLabelController@bind_goods')->name('bind_goods');
        Route::any('unbind_goods', 'GoodsServicesLabelController@unbind_goods')->name('unbind_goods');
        Route::any('select_goods', 'GoodsServicesLabelController@select_goods')->name('select_goods');
    });
});
