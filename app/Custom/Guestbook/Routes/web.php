<?php

use Illuminate\Support\Facades\Route;

// PC
Route::prefix('guestbook')->group(function () {
    Route::get('/', 'IndexController@index')->name('guestbook.index');
    Route::get('add', 'IndexController@add')->name('guestbook.add');
    Route::post('save', 'IndexController@save')->name('guestbook.save');

    Route::get('demo', 'DemoController@demo')->name('guestbook.demo');
});

// mobile
Route::prefix('guestbook/mobile')->group(function () {
    Route::get('/', 'MobileController@index')->name('guestbook.mobile.index');
    Route::get('add', 'MobileController@add')->name('guestbook.mobile.add');
    Route::post('save', 'MobileController@save')->name('guestbook.mobile.save');
});

/**
 * PC平台后台开发
 */
Route::namespace('Admin')->prefix(ADMIN_PATH . '/')->group(function () {
    Route::any('guestbook', 'TestAdminController@index')->name('admin/guestbook');
});

/**
 * PC商家后台开发
 */
Route::namespace('Seller')->prefix(SELLER_PATH . '/')->group(function () {
    Route::any('guestbook', 'TestSellerController@index')->name('seller/guestbook');
});
