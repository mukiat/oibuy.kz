<?php

use Illuminate\Support\Facades\Route;

/**
 * 后台模块
 */
Route::group(['namespace' => 'Admin', 'prefix' => ADMIN_PATH], function () {
    //
    Route::prefix('custom')->name('admin/custom/')->group(function () {
        Route::any('users/index', 'CustomUsersController@index')->name('users/index')->middleware('admin_priv:custom_logout');
        Route::any('users/reason', 'CustomUsersController@reason')->name('users/reason')->middleware('admin_priv:custom_logout');
        Route::any('users/reason_edit', 'CustomUsersController@reason_edit')->name('users/reason_edit')->middleware('admin_priv:custom_logout');
        Route::any('users/reason_delete', 'CustomUsersController@reason_delete')->name('users/reason_delete')->middleware('admin_priv:custom_logout');
        Route::any('users/logout', 'CustomUsersController@logout')->name('users/logout')->middleware('admin_priv:custom_logout');
    });
});
