<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use Closure;
use Illuminate\Support\Carbon;

class SellerAdminPriv
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null  $priv_str 权限名称code
     * @return \Illuminate\Http\RedirectResponse|mixed
     * @throws \Exception
     */
    public function handle($request, Closure $next, $priv_str = null)
    {
        $seller_id = $request->session()->get('seller_id', 0);

        $action_list = cache()->remember('admin_action'. $seller_id, Carbon::now()->addDay(), function () use ($seller_id) {
            return AdminUser::where('user_id', $seller_id)->value('action_list');
        });

        if ($action_list && $action_list == 'all') {
            return $next($request);
        }

        if (strpos(',' . $action_list . ',', ',' . $priv_str . ',') === false) {
            return redirect()->route('seller/base/message')->with('msg', lang('admin/common.priv_error'))->with('type', 2);
        }

        return $next($request);
    }
}
