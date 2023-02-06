<?php

use Illuminate\Support\Facades\Route;

/**
 * PC端
 */
Route::group(['prefix' => '/'], function () {

    Route::any('/', 'IndexController@index')->name('home');
    Route::any('index.php', 'IndexController@index')->name('index');
    Route::any('homeindex.php', 'HomeIndexController@index')->name('homeindex');

    Route::any('activity.php', 'ActivityController@index')->name('activity');

    Route::any('affiche.php', 'AfficheController@index')->name('affiche');
    Route::any('affiliate.php', 'AffiliateController@index')->name('affiliate');
    Route::any('ajax_dialog.php', 'AjaxDialogController@index')->name('ajax_dialog');
    Route::any('ajax_user.php', 'AjaxUserController@index')->name('ajax_user');
    Route::any('ajax_category.php', 'AjaxCategoryController@index')->name('ajax_category');
    Route::any('ajax_goods.php', 'AjaxGoodsController@index')->name('ajax_goods');
    Route::any('ajax_article.php', 'AjaxArticleController@index')->name('ajax_article');
    Route::any('ajax_shop.php', 'AjaxShopController@index')->name('ajax_shop');
    Route::any('ajax_flow.php', 'AjaxFlowController@index')->name('ajax_flow');
    Route::any('ajax_flow_address.php', 'AjaxFlowAddressController@index')->name('ajax_flow_address');
    Route::any('ajax_flow_activity.php', 'AjaxFlowActivityController@index')->name('ajax_flow_activity');
    Route::any('ajax_flow_goods.php', 'AjaxFlowGoodsController@index')->name('ajax_flow_goods');
    Route::any('ajax_flow_user.php', 'AjaxFlowUserController@index')->name('ajax_flow_user');
    Route::any('ajax_flow_pay.php', 'AjaxFlowPayController@index')->name('ajax_flow_pay');
    Route::any('ajax_flow_shipping.php', 'AjaxFlowShippingController@index')->name('ajax_flow_shipping');
    Route::any('ajax_cart.php', 'AjaxCartController@index')->name('ajax_cart');

    Route::any('article.php', 'ArticleController@index')->name('article');
    Route::any('article_cat.php', 'ArticleCatController@index')->name('article_cat');

    Route::any('auction.php', 'AuctionController@index')->name('auction');

    // app扫码登录
    Route::prefix('appqrcode')->group(function () {
        Route::any('/', 'AppQrcodeController@index')->name('appqrcode.index');           // 生成二维码
        Route::any('getqrcode', 'AppQrcodeController@getqrcode')->name('appqrcode.getqrcode'); // 二维码路径
        Route::any('getting', 'AppQrcodeController@getting')->name('appqrcode.getting'); // 轮询前端探查
    });


    Route::any('bonus.php', 'BonusController@index')->name('bonus');
    Route::any('bouns_available.php', 'BounsAvailableController@index')->name('bouns_available');
    Route::any('bouns_expire.php', 'BounsExpireController@index')->name('bouns_expire');
    Route::any('bouns_useup.php', 'BounsUseupController@index')->name('bouns_useup');

    // 品牌
    Route::any('brand.php', 'BrandController@index')->name('brand');

    Route::any('brandn.php', 'BrandnController@index')->name('brandn');

    // 商品分类
    Route::any('catalog.php', 'CategoryallController@index')->name('catalog');
    Route::any('category.php', 'CategoryController@index')->name('category');
    Route::any('category_compare.php', 'CategoryCompareController@index')->name('category_compare');
    Route::any('category_discuss.php', 'CategoryDiscussController@index')->name('category_discuss');

    Route::any('categoryall.php', 'CategoryallController@index')->name('categoryall');

    Route::any('collection_brands.php', 'CollectionBrandsController@index')->name('collection_brands');
    Route::any('collection_goods.php', 'CollectionGoodsController@index')->name('collection_goods');
    Route::any('collection_store.php', 'CollectionStoreController@index')->name('collection_store');
    Route::any('comment.php', 'CommentController@index')->name('comment');
    Route::any('comment_discuss.php', 'CommentDiscussController@index')->name('comment_discuss');
    Route::any('comment_repay.php', 'CommentRepayController@index')->name('comment_repay');
    Route::any('comment_reply.php', 'CommentReplyController@index')->name('comment_reply');
    Route::any('comment_reply_single.php', 'CommentReplySingleController@index')->name('comment_reply_single');
    Route::any('comment_single.php', 'CommentSingleController@index')->name('comment_single');

    Route::any('coudan.php', 'CoudanController@index')->name('coudan');
    Route::any('coupons.php', 'CouponsController@index')->name('coupons');
    Route::any('crowdfunding.php', 'CrowdfundingController@index')->name('crowdfunding');
    Route::any('cycle_image.php', 'CycleImageController@index')->name('cycle_image');
    // 计划任务
    Route::any('cron.php', 'CronController@index')->name('cron');

    Route::any('delete_cart_goods.php', 'DeleteCartGoodsController@index')->name('delete_cart_goods');

    Route::any('exchange.php', 'ExchangeController@index')->name('exchange');

    // Feed
    Route::any('feed.php', 'FeedController@index')->name('feed');

    Route::any('flow.php', 'FlowController@index')->name('flow');
    Route::any('flow_consignee.php', 'FlowConsigneeController@index')->name('flow_consignee');

    // 过滤词
    Route::prefix('filter')->name('filter.')->group(function () {
        // 记录
        Route::any('updatelogs', 'FilterWordsController@updatelogs')->name('updatelogs');
    });

    Route::any('gallery.php', 'GalleryController@index')->name('gallery');

    Route::any('gift_gard.php', 'GiftGardController@index')->name('gift_gard');

    Route::any('goods.php', 'GoodsController@index')->name('goods');

    Route::any('goods_script.php', 'GoodsScriptController@index')->name('goods_script');

    Route::any('group_buy.php', 'GroupBuyController@index')->name('group_buy');

    Route::any('help.php', 'HelpController@index')->name('help');
    // 浏览历史
    Route::any('history_list.php', 'HistoryListController@index')->name('history_list');

    // 商家入驻
    Route::any('merchants.php', 'MerchantsController@index')->name('merchants');

    // 商家入驻流程
    Route::any('merchants_steps.php', 'MerchantsStepsController@index')->name('merchants_steps');

    Route::any('merchants_steps_action.php', 'MerchantsStepsActionController@index')->name('merchants_steps_action');
    Route::any('merchants_steps_site.php', 'MerchantsStepsSiteController@index')->name('merchants_steps_site');

    Route::any('merchants_store.php', 'MerchantsStoreController@index')->name('merchants_store');

    Route::any('merchants_store_shop.php', 'MerchantsStoreShopController@index')->name('merchants_store_shop');

    // 在线留言
    Route::any('message.php', 'MessageController@index')->name('message');

    Route::any('news.php', 'NewsController@index')->name('news');

    // 授权登录
    Route::any('oauth', 'OauthController@index')->name('oauth');
    Route::any('oauth/bind_register', 'OauthController@bind_register')->name('oauth.bind_register')->middleware('limit_form_repeat');

    Route::any('online.php', 'OnlineController@index')->name('online');

    Route::any('package.php', 'PackageController@index')->name('package');

    // 预售
    Route::any('presale.php', 'PresaleController@index')->name('presale');

    Route::any('preview.php', 'PreviewController@index')->name('preview');

    Route::any('receive.php', 'ReceiveController@index')->name('receive');
    Route::any('return_images.php', 'ReturnImagesController@index')->name('return_images');

    Route::any('search.php', 'SearchController@index')->name('search');

    // 秒杀
    Route::any('seckill.php', 'SeckillController@index')->name('seckill');

    Route::any('single_sun.php', 'SingleSunController@index')->name('single_sun');
    Route::any('single_sun_images.php', 'SingleSunImagesController@index')->name('single_sun_images');

    Route::any('sitemaps.php', 'SitemapsController@index')->name('sitemaps');
    // 夺宝奇兵
    Route::any('snatch.php', 'SnatchController@index')->name('snatch');

    // 短信
    Route::any('sms.php', 'SmsController@index')->name('sms');
    // 店铺街
    Route::any('store_street.php', 'StoreStreetController@index')->name('store_street');

    Route::any('suggestions.php', 'SuggestionsController@index')->name('suggestions');

    Route::any('tag_cloud.php', 'TagCloudController@index')->name('tag_cloud');
    Route::any('topic.php', 'TopicController@index')->name('topic');

    Route::any('trade_snapshot.php', 'TradeSnapshotController@index')->name('trade_snapshot');

    Route::any('vote.php', 'VoteController@index')->name('vote');
});


// 会员模块
Route::group(['namespace' => 'User', 'prefix' => '/'], function () {

    Route::any('user.php', 'UserController@index')->name('user');

    Route::any('user_order.php', 'UserOrderController@index')->name('user_order');

    Route::any('user_address.php', 'UserAddressController@index')->name('user_address');

    Route::any('user_collect.php', 'UserCollectController@index')->name('user_collect');

    Route::any('user_message.php', 'UserMessageController@index')->name('user_message');

    Route::any('user_crowdfund.php', 'UserCrowdfundController@index')->name('user_crowdfund');

    Route::any('user_activity.php', 'UserActivityController@index')->name('user_activity');

    Route::any('user_baitiao.php', 'UserBaitiaoController@index')->name('user_baitiao');

});
