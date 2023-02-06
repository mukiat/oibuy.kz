<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| common Routes
|--------------------------------------------------------------------------
|
| Here is where you can register common routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 默认首页自动识别
Route::any((MODULES_PC ? 'download' : '/'), 'DownloadController@index')->name('download');

// API
Route::any('api.php', 'ApiController@index')->name('api');
// 条形码
Route::any('barcodegen.php', 'BarcodegenController@index')->name('barcodegen');
// 日历
Route::any('calendar.php', 'CalendarController@index')->name('calendar');
// 验证码
Route::any('captcha_verify.php', 'CaptchaVerifyController@index')->name('captcha_verify');

Route::any('editor.php', 'EditorController@index')->name('editor');

Route::any('get_ajax_content.php', 'GetAjaxContentController@index')->name('get_ajax_content');

Route::any('pm.php', 'PmController@index')->name('pm');

Route::any('region.php', 'RegionController@index')->name('region');
Route::any('region_goods.php', 'RegionGoodsController@index')->name('region_goods');

// 支付同步通知
Route::any('respond.php', 'RespondController@index')->name('respond');
// 支付异步通知
Route::any('notify/{code?}', 'RespondController@notify')->name('notify');
// 退款异步通知
Route::any('notify_refound/{code?}', 'RespondController@notify_refound')->name('notify_refound');
// 输出二维码
Route::any('qrcode.php', 'QrcodeController@index')->name('qrcode');

Route::any('sdcms.cn.php', 'SdController@index')->name('sdcms.cn');
// 物流跟踪
Route::any('tracker', 'TrackerController@mobile')->name('tracker');
Route::any('tracker/query', 'TrackerController@query')->name('tracker.query');
Route::any('tracker_shipping', 'TrackerController@index');
