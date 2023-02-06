<?php

namespace App\Custom\Guestbook;

use App\Custom\Guestbook\Commands\GuestbookCommand;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;


class GuestbookServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Guestbook';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'guestbook';

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        // 配置
        $this->mergeConfigFrom(__DIR__ . '/Config/config.php', $this->moduleNameLower); // exp:  config('guestbook.name')
        // 视图
        $this->loadViewsFrom(__DIR__ . '/Resources/views', $this->moduleNameLower); // exp: return view('guestbook::admin.index.index');
        // 语言包
        $this->loadTranslationsFrom(__DIR__ . '/Resources/lang', $this->moduleNameLower); // exp:  trans('guestbook::common.test')
        // 数据库迁移
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations'); // exp: php artisan migrate

        // 路由
        $this->registerRoute($router);

        // 加载自定义辅助函数
        $this->loadHelperFrom();

        // 按需加载 command
        $this->commands([
            GuestbookCommand::class
        ]);

        // 命令终端下执行
        if ($this->app->runningInConsole()) {
            // $this->commands([
            // ]);

            // 发布Seeder文件
            // php artisan vendor:publish --provider="App\Custom\Guestbook\GuestbookServiceProvider" --tag=seeds --force
            $this->publishes([
                __DIR__ . '/Database/Seeds' => $this->app->databasePath('seeds')
            ], 'seeds');

            // 公用 Assets 资源文件JavaScript、CSS 和图片等文件
            // php artisan vendor:publish --provider="App\Custom\Guestbook\GuestbookServiceProvider" --tag=public --force
            $this->publishes([
                __DIR__ . '/public/assets/css' => public_path('guestbook/assets/css'),
                __DIR__ . '/public/assets/js' => public_path('guestbook/assets/js'),
                __DIR__ . '/public/assets/images' => public_path('guestbook/assets/images'),
            ], 'public');
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
            $router->middleware('web')->namespace(__NAMESPACE__ . '\Controllers')->group(__DIR__ . '/Routes/web.php');
            $router->middleware('api')->namespace(__NAMESPACE__ . '\Controllers')->group(__DIR__ . '/Routes/api.php');
        }
    }

    /**
     * load helpers file
     *
     */
    protected function loadHelperFrom()
    {
        if (file_exists($constant = __DIR__ . '/Support/constant.php')) {
            require_once $constant;
        }
        if (file_exists($helper = __DIR__ . '/Support/helpers.php')) {
            require_once $helper;
        }
    }
}
