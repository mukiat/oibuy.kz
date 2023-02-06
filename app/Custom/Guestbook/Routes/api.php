<?php

use Illuminate\Support\Facades\Route;

// api
Route::namespace('Api')->prefix('api/guestbook')->group(function () {

    Route::get('/', 'ApiController@index')->name('api.guestbook.index');
    Route::get('add', 'ApiController@add')->name('api.guestbook.add');
    Route::post('save', 'ApiController@save')->name('api.guestbook.save');

});

