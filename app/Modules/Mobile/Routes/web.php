<?php

use Illuminate\Support\Facades\Route;

/**
 * 微商城
 */
Route::group(['prefix' => 'mobile'], function () {
    Route::get('/', 'IndexController@mobile')->name('mobile');
    Route::get('oauth/callback', 'IndexController@callback')->name('mobile.oauth.callback');
    Route::get('respond', 'IndexController@respond')->name('mobile.pay.respond');
});