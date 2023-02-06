<?php

namespace App\Modules\Mobile\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;

/**
 * Class IndexController
 * @package App\Http\Controllers\Mobile
 */
class IndexController extends BaseController
{
    /**
     * 微商城
     * @return Factory|View
     */
    public function mobile()
    {
        return view('mobile::mobile');
    }

    /**
     * 微商城授权登录回调
     * @param Request $request
     * @return RedirectResponse|Redirector
     */
    public function callback(Request $request)
    {
        $args = $request->all();

        unset($args[0]);

        if (blank(config('app.mobile_domain'))) {
            $url = '/mobile/#/callback?' . http_build_query($args, '', '&');
        } else {
            $url = '/#/callback?' . http_build_query($args, '', '&');
        }

        return redirect($url);
    }

    /**
     * 支付同步回调
     * @param Request $request
     * @return RedirectResponse|Redirector
     */
    public function respond(Request $request)
    {
        $args = $request->all();

        unset($args[0]);

        if (blank(config('app.mobile_domain'))) {
            $url = '/mobile/#/respond?' . http_build_query($args, '', '&');
        } else {
            $url = '/#/respond?' . http_build_query($args, '', '&');
        }

        return redirect($url);
    }
}
