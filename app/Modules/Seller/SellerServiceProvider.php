<?php

namespace App\Modules\Seller;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class SellerServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Seller';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'seller';

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
        // 配置
        //$this->mergeConfigFrom(__DIR__ . '/config/config.php', $this->moduleNameLower); // exp:  config('seller.name')
        // 视图
        //$this->loadViewsFrom(__DIR__ . '/Resources/views', $this->moduleNameLower); // exp: return view('seller::seller.index.index');
        // 语言包
        $this->loadTranslationsFrom(__DIR__ . '/Languages', 'seller'); // exp:  trans('seller::common.test')
        // 数据库迁移
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations'); // exp: php artisan migrate

        $this->registerRoute($router);
    }

    /**
     * Register routes.
     *
     * @param $router
     */
    protected function registerRoute($router)
    {
        if (!$this->app->routesAreCached()) {
            $router->middleware('web')->namespace(__NAMESPACE__ . '\Controllers')->group(__DIR__ . '/route.php');
        }
    }
}
