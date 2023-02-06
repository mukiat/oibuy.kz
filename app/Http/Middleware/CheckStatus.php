<?php

namespace App\Http\Middleware;

use App\Api\Foundation\Components\HttpResponse;
use Closure;
use Illuminate\Http\Request;

class CheckStatus
{
    use HttpResponse;

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->is(ADMIN_PATH . '*', 'calendar.php*', 'ueditor*') && config('shop.shop_closed') == 1) {
            if ($request->is('api/*')) {
                return $this->failed(config('shop.close_comment'));
            } else {
                return response()->view('closed', ['close_comment' => config('shop.close_comment')]);
            }
        }

        return $next($request);
    }
}
