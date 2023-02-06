<?php

use Illuminate\Support\Facades\Route;

/**
 * 微商城二级域名
 */
if (!empty(config('app.mobile_domain'))) {
    Route::domain(config('app.mobile_domain'))->group(function () {
        Route::get('/', 'IndexController@mobile')->name('mobile');
        Route::get('oauth/callback', 'IndexController@callback')->name('mobile.oauth.callback');
        Route::get('respond', 'IndexController@respond')->name('mobile.pay.respond');
    });
}