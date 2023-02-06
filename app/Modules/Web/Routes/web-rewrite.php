<?php

use Illuminate\Support\Facades\Route;

/**
 * PC端 开启伪静态 路由配置  对应 web.php
 */
Route::group(['prefix' => '/'], function () {

    // index.php
    Route::any('index.html', 'IndexController@index');
    // activity.php
    Route::any('activity.html', 'ActivityController@index');
    // article.php
    Route::any('article-{id}.html', 'ArticleController@index')->where('id', '[0-9]+');

    // article_cat.php
    Route::any('article_cat-{id}-{page}-{sort}-{order}.html', 'ArticleCatController@index')
        ->where(['id' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('article_cat-{id}-{page}-{keywords}.html', 'ArticleCatController@index')
        ->where(['id' => '[0-9]+', 'page' => '[0-9]+', 'keywords' => '.+']);
    Route::any('article_cat-{id}-{page}.html', 'ArticleCatController@index')->where(['id' => '[0-9]+', 'page' => '[0-9]+']);
    Route::any('article_cat-{id}.html', 'ArticleCatController@index')->where('id', '[0-9]+');

    // auction.php
    Route::any('auction.html', 'AuctionController@index');
    Route::any('auction-{id}.html', 'AuctionController@index')->defaults('act', 'view')->where('id', '[0-9]+');

    // brand.php
    Route::any('brand.html', 'BrandController@index');
    Route::any('brand-{id}-d{display}-c{cat}-min{price_min}-max{price_max}-ship{ship}-self{self}-{page}-{sort}-{order}.html', 'BrandController@index')
        ->where(['id' => '[0-9]+', 'display' => '[a-zA-Z]+', 'cat' => '[0-9]+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'ship' => '[0-9]+', 'self' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('brand-{id}-d{display}-c{cat}-ship{ship}-self{self}-{page}-{sort}-{order}.html', 'BrandController@index')
        ->where(['id' => '[0-9]+', 'display' => '[a-zA-Z]+', 'cat' => '[0-9]+', 'ship' => '[0-9]+', 'self' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('brand-{id}-d{display}-c{cat}-min{price_min}-max{price_max}-self{self}-{page}-{sort}-{order}.html', 'BrandController@index')
        ->where(['id' => '[0-9]+', 'display' => '[a-zA-Z]+', 'cat' => '[0-9]+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'self' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('brand-{id}-d{display}-c{cat}-self{self}-{page}-{sort}-{order}.html', 'BrandController@index')
        ->where(['id' => '[0-9]+', 'display' => '[a-zA-Z]+', 'cat' => '[0-9]+', 'self' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('brand-{id}-d{display}-c{cat}-min{price_min}-max{price_max}-ship{ship}-{page}-{sort}-{order}.html', 'BrandController@index')
        ->where(['id' => '[0-9]+', 'display' => '[a-zA-Z]+', 'cat' => '[0-9]+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'ship' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('brand-{id}-d{display}-c{cat}-ship{ship}-{page}-{sort}-{order}.html', 'BrandController@index')
        ->where(['id' => '[0-9]+', 'display' => '[a-zA-Z]+', 'cat' => '[0-9]+', 'ship' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('brand-{id}-d{display}-c{cat}-min{price_min}-max{price_max}-{page}-{sort}-{order}.html', 'BrandController@index')
        ->where(['id' => '[0-9]+', 'display' => '[a-zA-Z]+', 'cat' => '[0-9]+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('brand-{id}-d{display}-c{cat}-min{price_min}-max{price_max}-{page}.html', 'BrandController@index')
        ->where(['id' => '[0-9]+', 'display' => '[a-zA-Z]+', 'cat' => '[0-9]+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'page' => '[0-9]+']);
    Route::any('brand-{id}-d{display}-c{cat}-min{price_min}-max{price_max}.html', 'BrandController@index')
        ->where(['id' => '[0-9]+', 'display' => '[a-zA-Z]+', 'cat' => '[0-9]+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+']);
    Route::any('brand-{id}-d{display}-mbid{mbid}-min{price_min}-max{price_max}-ship{ship}-self{self}-{page}-{sort}-{order}.html', 'BrandController@index')
        ->where(['id' => '[0-9]+', 'display' => '[a-zA-Z]+', 'mbid' => '[0-9]+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'ship' => '[0-9]+', 'self' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('brand-{id}-d{display}-mbid{mbid}-min{price_min}-max{price_max}-self{self}-{page}-{sort}-{order}.html', 'BrandController@index')
        ->where(['id' => '[0-9]+', 'display' => '[a-zA-Z]+', 'mbid' => '[0-9]+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'self' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('brand-{id}-d{display}-mbid{mbid}-min{price_min}-max{price_max}-ship{ship}-{page}-{sort}-{order}.html', 'BrandController@index')
        ->where(['id' => '[0-9]+', 'display' => '[a-zA-Z]+', 'mbid' => '[0-9]+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'ship' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('brand-{id}-d{display}-mbid{mbid}-min{price_min}-max{price_max}-{page}-{sort}-{order}.html', 'BrandController@index')
        ->where(['id' => '[0-9]+', 'display' => '[a-zA-Z]+', 'mbid' => '[0-9]+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('brand-{id}-d{display}-mbid{mbid}-ship{ship}-self{self}-{page}-{sort}-{order}.html', 'BrandController@index')
        ->where(['id' => '[0-9]+', 'display' => '[a-zA-Z]+', 'mbid' => '[0-9]+', 'ship' => '[0-9]+', 'self' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('brand-{id}-d{display}-mbid{mbid}-self{self}-{page}-{sort}-{order}.html', 'BrandController@index')
        ->where(['id' => '[0-9]+', 'display' => '[a-zA-Z]+', 'mbid' => '[0-9]+', 'self' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('brand-{id}-d{display}-mbid{mbid}-ship{ship}-{page}-{sort}-{order}.html', 'BrandController@index')
        ->where(['id' => '[0-9]+', 'display' => '[a-zA-Z]+', 'mbid' => '[0-9]+', 'ship' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('brand-{id}-d{display}-mbid{mbid}-{page}-{sort}-{order}.html', 'BrandController@index')
        ->where(['id' => '[0-9]+', 'display' => '[a-zA-Z]+', 'mbid' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('brand-{id}-d{display}-c{cat}-{page}-{sort}-{order}.html', 'BrandController@index')
        ->where(['id' => '[0-9]+', 'display' => '[a-zA-Z]+', 'cat' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('brand-{id}-d{display}-{page}-{sort}-{order}.html', 'BrandController@index')
        ->where(['id' => '[0-9]+', 'display' => '[a-zA-Z]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('brand-{id}-mbid{mbid}.html', 'BrandController@index')->where(['id' => '[0-9]+', 'mbid' => '[0-9]+']);
    Route::any('brand-{id}-c{cat}.html', 'BrandController@index')->where(['id' => '[0-9]+', 'cat' => '[0-9]+']);
    Route::any('brand-{id}.html', 'BrandController@index')->where(['id' => '[0-9]+']);


    // brandn.php
    Route::any('brandn-{id}-c{cat}-{page}-{sort}-{order}-{act}.html', 'BrandnController@index')
        ->where(['id' => '[0-9]+', 'cat' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+', 'act' => '([a-zA-Z])+([^-]*)']);
    Route::any('brandn-{id}-{page}-{sort}-{order}-{act}.html', 'BrandnController@index')
        ->where(['id' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+', 'act' => '([a-zA-Z])+([^-]*)']);
    Route::any('brandn-{id}-c{cat}-{page}-{act}.html', 'BrandnController@index')
        ->where(['id' => '[0-9]+', 'cat' => '[0-9]+', 'page' => '[0-9]+', 'act' => '([a-zA-Z])+([^-]*)']);
    Route::any('brandn-{id}-c{cat}-{act}.html', 'BrandnController@index')
        ->where(['id' => '[0-9]+', 'cat' => '[0-9]+', 'act' => '([a-zA-Z])+([^-]*)']);
    Route::any('brandn-{id}-{cat}.html', 'BrandnController@index')
        ->where(['id' => '[0-9]+', 'cat' => '([a-zA-Z])+([^-]*)']);
    Route::any('brandn-{id}.html', 'BrandnController@index')->where(['id' => '[0-9]+']);


    // category.php 商品分类
    // 商品分类筛选：品牌、价格区间、属性、包邮、自营、是否有货、大小图、分页、排序
    Route::any('category-{id}-b{brand?}-ubrand{ubrand?}-min{price_min?}-max{price_max?}-attr{filter_attr?}-ship{ship?}-self{self?}-have{have?}-d{display?}-{page?}-{sort?}-{order?}.html', 'CategoryController@index');
    // 商品分类筛选：品牌、价格区间、属性、大小图、分页、排序
    Route::any('category-{id}-b{brand?}-ubrand{ubrand?}-min{price_min?}-max{price_max?}-attr{filter_attr?}-d{display?}-{page?}-{sort?}-{order?}.html', 'CategoryController@index');
    // 商品分类筛选：包邮、自营、是否有货、大小图、分页、排序
    Route::any('category-{id}-ship{ship?}-self{self?}-have{have?}-d{display?}-{page?}-{sort?}-{order?}.html', 'CategoryController@index')
        ->where(['id' => '[0-9]+', 'ship' => '[0-9]+', 'self' => '[0-9]+', 'have' => '[0-9]+', 'display' => '[a-zA-Z]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    // 商品分类筛选：大小图、分页、排序
    Route::any('category-{id}-d{display?}-{page?}-{sort?}-{order?}.html', 'CategoryController@index')
        ->where(['id' => '[0-9]+', 'display' => '[a-zA-Z]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);

    Route::any('category-{id}-b{brand}.html', 'CategoryController@index')->where(['id' => '[0-9]+', 'brand' => '[^-]*']);
    Route::any('category-{id}.html', 'CategoryController@index')->where(['id' => '[0-9]+']);

    // categoryall.php
    Route::any('categoryall.html', 'CategoryallController@index');
    Route::any('categoryall-{id}.html', 'CategoryallController@index')->where('id', '[0-9]+');

    // exchange.php
    Route::any('exchange.html', 'ExchangeController@index');
    Route::any('exchange-id{id}.html', 'ExchangeController@index')->defaults('act', 'view')->where(['id' => '[0-9]+']);
    Route::any('exchange-{cat_id}-min{integral_min}-max{integral_max}-{page}-{sort}-{order}.html', 'ExchangeController@index')
        ->where(['cat_id' => '[0-9]+', 'integral_min' => '[0-9]+', 'integral_max' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('exchange-{cat_id}-{page}-{sort}-{order}.html', 'ExchangeController@index')
        ->where(['cat_id' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('exchange-{cat_id}-{page}.html', 'ExchangeController@index')->where(['cat_id' => '[0-9]+', 'page' => '[0-9]+']);
    Route::any('exchange-{cat_id}.html', 'ExchangeController@index')->where(['cat_id' => '[0-9]+']);

    // feed.php
    Route::any('feed.xml', 'FeedController@index');
    Route::any('feed-c{cat}.html', 'FeedController@index')->where('cat', '[0-9]+');
    Route::any('feed-b{brand}.html', 'FeedController@index')->where('brand', '[0-9]+');
    Route::any('feed-type{type}.html', 'FeedController@index')->where('type', '[^-]+');

    // flow.php
    Route::any('flow.html', 'FlowController@index')->name('flow');

    // gift_gard.php
    Route::any('gift_gard.html', 'GiftGardController@index');
    Route::any('gift_gard-{id}-{page}-{sort}-{order}.html', 'GiftGardController@index')
        ->where(['id' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('gift_gard-{id}-{page}.html', 'GiftGardController@index')->where(['id' => '[0-9]+', 'page' => '[0-9]+']);
    Route::any('gift_gard-{id}.html', 'GiftGardController@index')->where(['id' => '[0-9]+']);

    // goods.php
    Route::any('goods-{id}.html', 'GoodsController@index')->where('id', '[0-9]+');

    // group_buy.php
    Route::any('group_buy.html', 'GroupBuyController@index');
    Route::any('group_buy_list.html', 'GroupBuyController@index')->defaults('act', 'list');
    Route::any('group_buy-{id}.html', 'GroupBuyController@index')->defaults('act', 'view')->where(['id' => '[0-9]+']);

    // history_list.php 浏览历史
    Route::any('history_list.html', 'HistoryListController@index');
    Route::any('history_list-{page?}-{ship?}-{self?}-{have?}.html', 'HistoryListController@index');

    // merchants.php
    Route::any('merchants.html', 'MerchantsController@index');
    Route::any('merchants-{id}.html', 'MerchantsController@index')->where('id', '[0-9]+');

    // merchants_steps.php
    Route::any('merchants_steps.html', 'MerchantsStepsController@index');
    // merchants_steps_site.php
    Route::any('merchants_steps_site.html', 'MerchantsStepsSiteController@index');

    // merchants_store.php
    Route::any('merchants_store.html', 'MerchantsStoreController@index');
    Route::any('merchants_store-{merchant_id}-c{id}-b{brand}-min{price_min}-max{price_max}-attr{filter_attr}-{page}-{sort}-{order}.html', 'MerchantsStoreController@index')
        ->where(['merchant_id' => '[0-9]+', 'id' => '[0-9]+', 'brand' => '[0-9]+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'filter_attr' => '[^-]*', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('merchants_store-{merchant_id}-c{id}-keyword{keyword}-min{price_min}-max{price_max}-attr{filter_attr}-{page}-{sort}-{order}.html', 'MerchantsStoreController@index')
        ->where(['merchant_id' => '[0-9]+', 'id' => '[0-9]+', 'keyword' => '.+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'filter_attr' => '[^-]*', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('merchants_store-{merchant_id}-c{id}-min{price_min}-max{price_max}-attr{filter_attr}-{page}-{sort}-{order}.html', 'MerchantsStoreController@index')
        ->where(['merchant_id' => '[0-9]+', 'id' => '[0-9]+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'filter_attr' => '[^-]*', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('merchants_store-{merchant_id}-keyword{keyword}-min{price_min}-max{price_max}-attr{filter_attr}-{page}-{sort}-{order}.html', 'MerchantsStoreController@index')
        ->where(['merchant_id' => '[0-9]+', 'keyword' => '.+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'filter_attr' => '[^-]*', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('merchants_store-{merchant_id}-min{price_min}-max{price_max}-attr{filter_attr}-{page}-{sort}-{order}.html', 'MerchantsStoreController@index')
        ->where(['merchant_id' => '[0-9]+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'filter_attr' => '[^-]*', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('merchants_store-{merchant_id}-c{id}-b{brand}-min{price_min}-max{price_max}-attr{filter_attr}.html', 'MerchantsStoreController@index')
        ->where(['merchant_id' => '[0-9]+', 'id' => '[0-9]+', 'brand' => '[0-9]+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'filter_attr' => '[^-]*']);
    Route::any('merchants_store-{merchant_id}-c{id}-b{brand}-{page}-{sort}-{order}.html', 'MerchantsStoreController@index')
        ->where(['merchant_id' => '[0-9]+', 'id' => '[0-9]+', 'brand' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('merchants_store-{merchant_id}-c{id}-{page}-{sort}-{order}.html', 'MerchantsStoreController@index')
        ->where(['merchant_id' => '[0-9]+', 'id' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('merchants_store-{merchant_id}-c{id}-min{price_min}-max{price_max}-attr{filter_attr}.html', 'MerchantsStoreController@index')
        ->where(['merchant_id' => '[0-9]+', 'id' => '[0-9]+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'filter_attr' => '[^-]*']);
    Route::any('merchants_store-{merchant_id}-c{id}-b{brand}-{page}.html', 'MerchantsStoreController@index')
        ->where(['merchant_id' => '[0-9]+', 'id' => '[0-9]+', 'brand' => '[0-9]+', 'page' => '[0-9]+']);
    Route::any('merchants_store-{merchant_id}-c{id}-b{brand}.html', 'MerchantsStoreController@index')
        ->where(['merchant_id' => '[0-9]+', 'id' => '[0-9]+', 'brand' => '[0-9]+']);
    Route::any('merchants_store-{merchant_id}-c{id}.html', 'MerchantsStoreController@index')
        ->where(['merchant_id' => '[0-9]+', 'id' => '[0-9]+']);
    Route::any('merchants_store-{merchant_id}.html', 'MerchantsStoreController@index')->where(['merchant_id' => '[0-9]+']);

    // merchants_store_shop.php
    Route::any('merchants_store_shop-{id}-{page}-{sort}-{order}.html', 'MerchantsStoreShopController@index')
        ->where(['id' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('merchants_store_shop-{id}.html', 'MerchantsStoreShopController@index')->where(['id' => '[0-9]+']);

    // message.php
    Route::any('message.html', 'MessageController@index');

    // news.php
    Route::any('news.html', 'NewsController@index');

    // package.php
    Route::any('package.html', 'PackageController@index');

    // presale.php
    Route::any('presale.html', 'PresaleController@index');
    Route::any('presale-c{cat_id}-status{status}-{act}.html', 'PresaleController@index')
        ->where(['cat_id' => '[0-9]+', 'status' => '[0-9]+', 'act' => '([a-zA-Z])+([^-]*)']);
    Route::any('presale-status{status}-{act}.html', 'PresaleController@index')
        ->where(['status' => '[0-9]+', 'act' => '([a-zA-Z])+([^-]*)']);
    Route::any('presale-c{cat_id}-{act}.html', 'PresaleController@index')
        ->where(['cat_id' => '[0-9]+', 'act' => '([a-zA-Z])+([^-]*)']);
    Route::any('presale-{id}-{act}.html', 'PresaleController@index')
        ->where(['id' => '[0-9]+', 'act' => '([a-zA-Z])+([^-]*)']);
    Route::any('presale-{act}.html', 'PresaleController@index')->where('act', '([a-zA-Z])+([^-]*)');

    // search.php
    Route::any('tag-{keywords}.html', 'SearchController@index')->where(['keywords' => '.*']);

    // seckill.php
    Route::any('seckill.html', 'SeckillController@index');
    Route::any('seckill-{id}-{act}.html', 'SeckillController@index')->where(['id' => '[0-9]+', 'act' => '([a-zA-Z])+([^-]*)']);
    Route::any('seckill-{cat_id}.html', 'SeckillController@index')->where(['cat_id' => '[0-9]+']);

    // snatch.php
    Route::any('snatch.html', 'SnatchController@index');
    Route::any('snatch-{id}.html', 'SnatchController@index')->where(['id' => '[0-9]+']);

    // store_street.php
    Route::any('store_street.html', 'StoreStreetController@index');

    // suggestions.php
    Route::any('suggestions.html', 'SuggestionsController@index')->name('suggestions');
    // topic.php
    Route::any('topic.html', 'TopicController@index')->name('topic');

});

// 会员模块
Route::group(['namespace' => 'User', 'prefix' => '/'], function () {

    // user.php
    Route::any('user.html', 'UserController@index')->name('user');
    Route::any('user-{act}.html', 'UserController@index')->where('act', '([a-zA-Z])+([^-]*)');

    // user_order.php
    Route::any('user_order-{act}.html', 'UserOrderController@index')->name('user_order')->where('act', '([a-zA-Z])+([^-]*)');

    // user_address.php
    Route::any('user_address-{act}.html', 'UserAddressController@index')->name('user_address')->where('act', '([a-zA-Z])+([^-]*)');

    // user_collect.php
    Route::any('user_collect-{act}.html', 'UserCollectController@index')->where('act', '([a-zA-Z])+([^-]*)');

    // user_message.php
    Route::any('user_message-{act}.html', 'UserMessageController@index')->where('act', '([a-zA-Z])+([^-]*)');

    // user_crowdfund.php
    Route::any('user_crowdfund-{act}.html', 'UserCrowdfundController@index')->where('act', '([a-zA-Z])+([^-]*)');

    // user_activity.php
    Route::any('user_activity-{act}.html', 'UserActivityController@index')->where('act', '([a-zA-Z])+([^-]*)');

    // user_baitiao.php
    Route::any('user_baitiao-{act}.html', 'UserBaitiaoController@index')->where('act', '([a-zA-Z])+([^-]*)');

});
