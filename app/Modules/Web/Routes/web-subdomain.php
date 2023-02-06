<?php

use Illuminate\Support\Facades\Route;

/**
 * 主域名
 */
Route::domain('dscmall.zhuo')->group(function () {
    // 包含已有的 web.php 文件中全部的路由配置
});

/**
 * 店铺二级域名
 */
Route::domain('{shop}.dscmall.zhuo')->group(function () {
    Route::any('c{id}-b{brand}-min{price_min}-max{price_max}-attr{filter_attr}-{page}-{sort}-{order}.html', 'MerchantsStoreController@index')
        ->where(['id' => '[0-9]+', 'brand' => '[0-9]+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'filter_attr' => '[^-]*', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('c{id}-keyword{keyword}-min{price_min}-max{price_max}-attr{filter_attr}-{page}-{sort}-{order}.html', 'MerchantsStoreController@index')
        ->where(['id' => '[0-9]+', 'keyword' => '.+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'filter_attr' => '[^-]*', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('c{id}-min{price_min}-max{price_max}-attr{filter_attr}-{page}-{sort}-{order}.html', 'MerchantsStoreController@index')
        ->where(['id' => '[0-9]+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'filter_attr' => '[^-]*', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('keyword{keyword}-min{price_min}-max{price_max}-attr{filter_attr}-{page}-{sort}-{order}.html', 'MerchantsStoreController@index')
        ->where(['keyword' => '.+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'filter_attr' => '[^-]*', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('min{price_min}-max{price_max}-attr{filter_attr}-{page}-{sort}-{order}.html', 'MerchantsStoreController@index')
        ->where(['price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'filter_attr' => '[^-]*', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('c{id}-min{price_min}-max{price_max}-attr{filter_attr}-{page}-{sort}-{order}.html', 'MerchantsStoreController@index')
        ->where(['id' => '[0-9]+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'filter_attr' => '[^-]*', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('c{id}-b{brand}-min{price_min}-max{price_max}-attr{filter_attr}.html', 'MerchantsStoreController@index')
        ->where(['id' => '[0-9]+', 'brand' => '[0-9]+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'filter_attr' => '[^-]*']);
    Route::any('c{id}-b{brand}-{page}-{sort}-{order}.html', 'MerchantsStoreController@index')
        ->where(['id' => '[0-9]+', 'brand' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('c{id}-min{price_min}-max{price_max}-attr{filter_attr}.html', 'MerchantsStoreController@index')
        ->where(['id' => '[0-9]+', 'price_min' => '[0-9]+', 'price_max' => '[0-9]+', 'filter_attr' => '[^-]*']);
    Route::any('c{id}-b{brand}-{page}.html', 'MerchantsStoreController@index')
        ->where(['id' => '[0-9]+', 'brand' => '[0-9]+', 'page' => '[0-9]+']);
    Route::any('c{id}-b{brand}.html', 'MerchantsStoreController@index')
        ->where(['id' => '[0-9]+', 'brand' => '[0-9]+']);
    Route::any('c{id}.html', 'MerchantsStoreController@index')
        ->where(['id' => '[0-9]+']);
    Route::any('/' . config('app.store_param'), 'MerchantsStoreController@index')->name('merchants_store');
    Route::any('/', 'MerchantsStoreController@index')->name('merchants_store');
    Route::any('merchants_store_shop.php', 'MerchantsStoreShopController@index')->name('merchants_store_shop');
    Route::any('merchants_store_shop-{id}-{page}-{sort}-{order}.html', 'MerchantsStoreShopController@index')
        ->where(['id' => '[0-9]+', 'page' => '[0-9]+', 'sort' => '.+', 'order' => '[a-zA-Z]+']);
    Route::any('merchants_store_shop-{id}.html', 'MerchantsStoreShopController@index')
        ->where(['id' => '[0-9]+']);

    /* 解决跨域问题 */
    Route::any('crossDomain', 'MerchantsStoreController@crossDomain')->name('cross_domain');
});
