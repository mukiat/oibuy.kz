<?php

namespace App\Modules\Web;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;


class WebServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap services.
     *
     * @param Router $router
     * @return void
     */
    public function boot(Router $router)
    {
        // 视图
        //$this->loadViewsFrom(__DIR__ . '/Resources/views', 'web'); // exp: return view('web::index');
        // 语言包
        //$this->loadTranslationsFrom(__DIR__ . '/Resources/lang', 'web'); // exp:  trans('web::common.test')
        // 数据库迁移
        //$this->loadMigrationsFrom(__DIR__ . '/Database/Migrations'); // exp: php artisan migrate

        // 路由
        $this->registerRoute($router);
    }

    /**
     * Register routes.
     *
     * @param $router
     */
    protected function registerRoute(Router $router)
    {
        if (!$this->app->routesAreCached()) {
            $router->middleware('web')->namespace(__NAMESPACE__ . '\Controllers')->group(__DIR__ . '/Routes/web.php');
            // 开启伪静态路由
            if (config('shop.rewrite') == 1) {
                $router->middleware('web')->namespace(__NAMESPACE__ . '\Controllers')->group(__DIR__ . '/Routes/web-rewrite.php');
            }
        }
    }
}
