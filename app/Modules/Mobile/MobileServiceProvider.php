<?php

namespace App\Modules\Mobile;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;


class MobileServiceProvider extends ServiceProvider
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
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'mobile'); // exp: return view('mobile::index');
        // 语言包
        //$this->loadTranslationsFrom(__DIR__ . '/Resources/lang', 'mobile'); // exp:  trans('mobile::common.test')
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
        }
    }
}
