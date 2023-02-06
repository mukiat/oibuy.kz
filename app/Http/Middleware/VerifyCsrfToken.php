<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'install',
        'wechat',
        ADMIN_PATH . '/touch_visual/*',
        SELLER_PATH . '/touch_visual/*',
        'oss.php',
        'obs.php',
        'api.php',
        'kefu/*',
        'respond.php', //支付同步通知
        'notify/*', // 支付异步通知
        'notify_refound/*', // 退款异步通知
    ];
}
