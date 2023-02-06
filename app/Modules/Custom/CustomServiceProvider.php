<?php

namespace App\Modules\Custom;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Finder\Finder;

class CustomServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/custom.php', 'custom');
    }

    /**
     * Bootstrap services.
     *
     * @param Router $router
     * @return void
     */
    public function boot(Router $router)
    {
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'custom'); // exp: return view('custom::admin.index.index');
        $this->loadTranslationsFrom(__DIR__ . '/Resources/lang', 'custom'); // exp:  trans('custom::common.test')
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations'); // exp: php artisan migrate

        // 加载自定义辅助函数
        $this->loadHelperFrom(__DIR__ . '/Support');

        // 命令
        if ($this->app->runningInConsole()) {

            // 发布配置文件
            $this->publishes([
                __DIR__ . '/config/custom.php' => config_path('custom.php'),
            ]);

            // 发布Seeder文件
            // php artisan vendor:publish --provider="App\Modules\Wxapp\WxappServiceProvider" --tag=seeds --force
            $this->publishes([
                __DIR__ . '/Database/Seeds' => $this->app->databasePath('seeds')
            ], 'seeds');

            // 公用 Assets 资源文件JavaScript、CSS 和图片等文件
            // php artisan vendor:publish --provider="App\Modules\Wxapp\WxappServiceProvider" --tag=public --force
            $this->publishes([
                __DIR__ . '/public/assets/mobile/css' => public_path('assets/mobile/css'),
            ], 'public');
        }

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
            $router->middleware('api')->namespace(__NAMESPACE__ . '\Controllers')->group(__DIR__ . '/Routes/api.php');
        }
    }

    /**
     * load helpers file
     *
     * @param $paths
     */
    protected function loadHelperFrom($paths)
    {
        $finder = Finder::create()->files()->name('*.php')->depth('== 0')->in($paths);

        foreach ($finder as $file) {
            //the absolute path
            $helper = $file->getRealPath();
            if (file_exists($helper)) {
                require_once $helper;
            }
        }
    }
}
