<?php

namespace App\Modules\Stores;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class StoreServiceProvider extends ServiceProvider
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
        //$this->loadViewsFrom(__DIR__ . '/Resources/views', 'stores'); // exp: return view('stores::admin.index.index');
        // 语言包
        $this->loadTranslationsFrom(__DIR__ . '/Languages', 'stores'); // exp:  trans('stores::common.test')
        // 数据库迁移
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations'); // exp: php artisan migrate

        // 路由
        $this->registerRoute($router);

        // 命令
        if ($this->app->runningInConsole()) {

            // 发布Seeder文件
            // php artisan vendor:publish --provider="App\Modules\Stores\StoreServiceProvider" --tag=seeds --force
            $this->publishes([
                __DIR__ . '/Database/Seeds' => $this->app->databasePath('seeds')
            ], 'seeds');

        }
    }

    /**
     * Register routes.
     *
     * @param $router
     */
    protected function registerRoute(Router $router)
    {
        if (!$this->app->routesAreCached()) {

            if (file_exists(STORE_MANAGE)) {
                $router->middleware('api')->namespace(__NAMESPACE__ . '\Controllers')->group(__DIR__ . '/Routes/api.php');
            }

            $router->middleware('web')->namespace(__NAMESPACE__ . '\Controllers')->group(__DIR__ . '/Routes/web.php');
        }
    }
}
