<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [
        'dsccp_page_size',
        'dscActionParam',
        'admin_type',
        'dscmall_affiliate_uid',
        'dscmall_affiliate_drp_id'
    ];
}
