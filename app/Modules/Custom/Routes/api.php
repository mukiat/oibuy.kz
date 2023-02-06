<?php

use Illuminate\Support\Facades\Route;

$customRoute = function () {
    // 小程序api
    Route::prefix('custom/user')->group(function () {
        // 注销文章
        Route::post('article', 'CustomUserController@article')->name('api.custom.user.article');
        // 注销原因
        Route::post('reason', 'CustomUserController@reason')->name('api.custom.user.reason');
        // 注销
        Route::post('logout', 'CustomUserController@logout')->name('api.custom.user.logout');
    });
};

Route::namespace('Api')->prefix('api')->group($customRoute);

Route::namespace('Api')->prefix('api/v4')->group($customRoute);
