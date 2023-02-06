<?php

use Illuminate\Support\Facades\Route;

Route::prefix(SELLER_PATH)->group(function () {
    Route::redirect('/', '/' . SELLER_PATH . '/index.php');
    Route::any('ad_position.php', 'AdPositionController@index');
    Route::any('admin_logs.php', 'AdminLogsController@index');
    Route::any('ads.php', 'AdsController@index');
    Route::any('adsense.php', 'AdsenseController@index');
    Route::any('attribute.php', 'AttributeController@index');
    Route::any('auction.php', 'AuctionController@index');
    Route::any('bargain.php', 'BargainController@index');
    Route::any('bonus.php', 'BonusController@index');
    Route::any('card.php', 'CardController@index');
    Route::any('category.php', 'CategoryController@index');
    Route::any('category_store.php', 'CategoryStoreController@index');
    Route::any('comment_manage.php', 'CommentManageController@index');
    Route::any('complaint.php', 'ComplaintController@index');
    Route::any('coupons.php', 'CouponsController@index');
    Route::any('dialog.php', 'DialogController@index');
    Route::any('discuss_circle.php', 'DiscussCircleController@index');
    Route::any('exchange_goods.php', 'ExchangeGoodsController@index');
    Route::any('favourable.php', 'FavourableController@index');
    Route::any('gallery_album.php', 'GalleryAlbumController@index');
    Route::any('get_ajax_content.php', 'GetAjaxContentController@index');
    Route::any('get_password.php', 'GetPasswordController@index');
    Route::any('gift_gard.php', 'GiftGardController@index');
    Route::any('goods.php', 'GoodsController@index');
    Route::any('goods_area_attr.php', 'GoodsAreaAttrController@index');
    Route::any('goods_area_attr_batch.php', 'GoodsAreaAttrBatchController@index');
    Route::any('goods_area_batch.php', 'GoodsAreaBatchController@index');
    Route::any('goods_auto.php', 'GoodsAutoController@index');
    Route::any('goods_batch.php', 'GoodsBatchController@index');
    Route::any('goods_booking.php', 'GoodsBookingController@index');
    Route::any('goods_export.php', 'GoodsExportController@index');
    Route::any('goods_inventory_logs.php', 'GoodsInventoryLogsController@index');
    Route::any('goods_lib.php', 'GoodsLibController@index');
    Route::any('goods_produts_area_batch.php', 'GoodsProdutsAreaBatchController@index');
    Route::any('goods_produts_batch.php', 'GoodsProdutsBatchController@index');
    Route::any('goods_produts_warehouse_batch.php', 'GoodsProdutsWarehouseBatchController@index');
    Route::any('goods_transport.php', 'GoodsTransportController@index');
    Route::any('goods_type.php', 'GoodsTypeController@index');
    Route::any('goods_warehouse_attr.php', 'GoodsWarehouseAttrController@index');
    Route::any('goods_warehouse_attr_batch.php', 'GoodsWarehouseAttrBatchController@index');
    Route::any('goods_warehouse_batch.php', 'GoodsWarehouseBatchController@index');
    Route::any('goods_keyword.php', 'GoodsKeywordController@index');
    Route::any('group_buy.php', 'GroupBuyController@index');
    Route::any('index.php', 'IndexController@index')->name('seller.home');
    Route::any('merchants_account.php', 'MerchantsAccountController@index');
    Route::any('merchants_brand.php', 'MerchantsBrandController@index');
    Route::any('merchants_commission.php', 'MerchantsCommissionController@index');
    Route::any('merchants_custom.php', 'MerchantsCustomController@index');
    Route::any('merchants_navigator.php', 'MerchantsNavigatorController@index');
    Route::any('merchants_template.php', 'MerchantsTemplateController@index');
    Route::any('merchants_upgrade.php', 'MerchantsUpgradeController@index');
    Route::any('merchants_window.php', 'MerchantsWindowController@index');
    Route::any('message.php', 'MessageController@index');
    Route::any('offline_store.php', 'OfflineStoreController@index');
    Route::any('order.php', 'OrderController@index');
    // 批量发货
    Route::any('order_delivery_download', 'OrderDeliveryController@order_delivery_download')->name('seller/order_delivery_download');
    Route::any('download_excel', 'OrderDeliveryController@download_excel')->name('seller/download_excel');
    Route::any('order_delivery_import', 'OrderDeliveryController@order_delivery_import')->name('seller/order_delivery_import');

    // 新增订单导出
    Route::get('order/export', 'OrderExportController@export')->name('seller/order/export')->middleware('seller_admin_priv:order_export');
    Route::post('order/export', 'OrderExportController@exportHandler')->name('seller/order/export')->middleware('seller_admin_priv:order_export');

    // 导出历史
    Route::get('export/history', 'ExportHistoryController@index')->name('seller/export_history');
    Route::post('export/download', 'ExportHistoryController@download')->name('seller/export_history/download');
    Route::post('export/delete', 'ExportHistoryController@delete')->name('seller/export_history/delete');

    Route::any('address_manage', 'AddressManageController@index')->name('seller/address_manage');

    /**
     * 支付单列表
     */
    Route::prefix('pay_log')->name('seller/pay_log/')->middleware('seller_admin_priv:pay_log')->group(function () {
        Route::any('/', 'PayLogController@index')->name('index');
        Route::any('detail', 'PayLogController@detail')->name('detail');
    });

    Route::any('order_delay.php', 'OrderDelayController@index');
    Route::any('order_stats.php', 'OrderStatsController@index');
    Route::any('pack.php', 'PackController@index');
    Route::any('package.php', 'PackageController@index');
    Route::any('presale.php', 'PresaleController@index');
    Route::any('print_batch.php', 'PrintBatchController@index');
    Route::any('privilege.php', 'PrivilegeController@index');
    Route::any('privilege_kefu.php', 'PrivilegeKefuController@index');
    Route::any('privilege_seller.php', 'PrivilegeSellerController@index');
    Route::any('region.php', 'RegionController@index');
    Route::any('sale_general.php', 'SaleGeneralController@index');
    Route::any('sale_list.php', 'SaleListController@index');
    Route::any('sale_order.php', 'SaleOrderController@index');
    Route::any('seckill.php', 'SeckillController@index');
    Route::any('seller_apply.php', 'SellerApplyController@index');
    Route::any('seller_shop_bg.php', 'SellerShopBgController@index');
    Route::any('seller_shop_slide.php', 'SellerShopSlideController@index');
    Route::any('shipping.php', 'ShippingController@index');
    Route::any('shipping_area.php', 'ShippingAreaController@index');
    Route::any('sms.php', 'SmsController@index');
    Route::any('snatch.php', 'SnatchController@index');
    Route::any('tag_manage.php', 'TagManageController@index');
    Route::any('team.php', 'TeamController@index');
    Route::any('topic.php', 'TopicController@index');

    /**
     * 商家二级商户
     */
    Route::prefix('seller_divide')->name('seller/seller_divide/')->group(function () {
        // 二级商户号管理与进件申请
        Route::any('/', 'SellerDivideController@index')->name('index')->middleware('seller_admin_priv:seller_divide');
        Route::any('apply', 'SellerDivideController@apply')->name('apply')->middleware('seller_admin_priv:seller_divide_apply', 'limit_form_repeat');
        Route::any('update', 'SellerDivideController@update')->name('update')->middleware('seller_admin_priv:seller_divide');
        Route::any('apply_list', 'SellerDivideController@apply_list')->name('apply_list')->middleware('seller_admin_priv:seller_divide_apply');
        Route::any('apply_detail', 'SellerDivideController@apply_detail')->name('apply_detail')->middleware('seller_admin_priv:seller_divide_apply');
        Route::any('apply_verify', 'SellerDivideController@apply_verify')->name('apply_verify')->middleware('seller_admin_priv:seller_divide_apply');
        Route::any('sync_apply', 'SellerDivideController@sync_apply')->name('sync_apply')->middleware('seller_admin_priv:seller_divide_apply');
        // 资金管理
        Route::any('account_log', 'SellerDivideController@account_log')->name('account_log')->middleware('seller_admin_priv:seller_divide_account');
        Route::any('account_log_query', 'SellerDivideController@account_log_query')->name('account_log_query')->middleware('seller_admin_priv:seller_divide_account');
        Route::any('account_log_apply', 'SellerDivideController@account_log_apply')->name('account_log_apply')->middleware('seller_admin_priv:seller_divide_account', 'limit_form_repeat');
        Route::any('account_log_detail', 'SellerDivideController@account_log_detail')->name('account_log_detail')->middleware('seller_admin_priv:seller_divide_account');
        Route::any('sync_account_log', 'SellerDivideController@sync_account_log')->name('sync_account_log')->middleware('seller_admin_priv:seller_divide_account');
    });

    // 手机端可视化
    Route::prefix('touch_visual')->name('seller/touch_visual/')->middleware('seller_admin_priv:touch_dashboard')->group(function () {
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

    // 消息提示
    Route::any('base/message', 'BaseController@message')->name('seller/base/message');

    Route::any('touch_topic.php', 'TouchTopicController@index');
    Route::any('tp_api.php', 'TpApiController@index');
    Route::any('user_msg.php', 'UserMsgController@index');
    Route::any('virtual_card.php', 'VirtualCardController@index');
    Route::any('visual_editing.php', 'VisualEditingController@index');
    Route::any('warehouse.php', 'WarehouseController@index');
    Route::any('warehouse_shipping_mode.php', 'WarehouseShippingModeController@index');
    Route::any('editor.php', 'EditorController@index');

    Route::any('seller_follow.php', 'SellerFollowController@index');

    // 过滤词
    Route::prefix('filter')->name('seller/filter/')->group(function () {
        // 记录
        Route::any('updatelogs', 'FilterWordsController@updatelogs')->name('updatelogs');
    });

    // 商品标签
    Route::prefix('goodslabel')->name('seller/goodslabel/')->middleware('seller_admin_priv:goods_label')->group(function () {
        // 商品标签列表
        Route::any('/', 'GoodsLabelController@index')->name('list');
        Route::any('bind_goods', 'GoodsLabelController@bind_goods')->name('bind_goods');
        Route::any('unbind_goods', 'GoodsLabelController@unbind_goods')->name('unbind_goods');
        Route::any('select_goods', 'GoodsLabelController@select_goods')->name('select_goods');
    });

    // 商品服务标签
    Route::prefix('goodsserviceslabel')->name('seller/goodsserviceslabel/')->middleware('seller_admin_priv:goods_services_label')->group(function () {
        // 商品标签列表
        Route::any('/', 'GoodsServicesLabelController@index')->name('list');
        Route::any('bind_goods', 'GoodsServicesLabelController@bind_goods')->name('bind_goods');
        Route::any('unbind_goods', 'GoodsServicesLabelController@unbind_goods')->name('unbind_goods');
        Route::any('select_goods', 'GoodsServicesLabelController@select_goods')->name('select_goods');
    });
});
