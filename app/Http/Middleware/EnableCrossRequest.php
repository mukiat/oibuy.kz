<?php

namespace App\Http\Middleware;

use Closure;

class EnableCrossRequest
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $origin = $request->server('HTTP_ORIGIN') ? $request->server('HTTP_ORIGIN') : '';

        // 测试白名单 可加入这段 填写域名或IP
        $host = parse_url($origin, PHP_URL_HOST);
        $allow_domian = ltrim(config('session.domain'), '.');

        if (stripos($host, $allow_domian)) {
            if (!method_exists($response, 'header')) {
                $response->headers->set('Access-Control-Allow-Origin', $origin);
                $response->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, Cookie, X-CSRF-TOKEN, Accept, Authorization, X-XSRF-TOKEN');
                $response->headers->set('Access-Control-Expose-Headers', 'Authorization, authenticated');
                $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS, DELETE');
                $response->headers->set('Access-Control-Allow-Credentials', 'true');
            } else {
                $response->header('Access-Control-Allow-Origin', $origin);
                $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Cookie, X-CSRF-TOKEN, Accept, Authorization, X-XSRF-TOKEN');
                $response->header('Access-Control-Expose-Headers', 'Authorization, authenticated');
                $response->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS, DELETE');
                $response->header('Access-Control-Allow-Credentials', 'true');
            }
        }

        return $response;
    }
}
