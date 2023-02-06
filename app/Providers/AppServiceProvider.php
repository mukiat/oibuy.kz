<?php

namespace App\Providers;

use App\Macros\Builder\DBExtend;
use App\Services\Common\ConfigService;
use Illuminate\Database\Query\Builder as DB;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @throws \Exception
     */
    public function boot()
    {
        error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

        Carbon::setLocale('zh');

        // 设置 nginx 反向代理模式下 Scheme 参数
        if (substr(config('app.url'), 0, 5) === 'https') {
            URL::forceScheme('https');
        }

        // 自动生成key
        if (blank(config('app.key'))) {
            config(['app.key' => 'base64:' . base64_encode(
                    Encrypter::generateKey(config('app.cipher'))
                )]);
        }

        require_once __DIR__ . '/../../app/Support/constant.php';
        require_once __DIR__ . '/../../app/Support/helpers.php';

        /* 定义商店配置信息 */
        $config = ConfigService::getConfig();
        config(['shop' => $config]);

        /* 定义全局语言包类型 */
        Config::set('app.locale', $config['lang'] ?? 'zh-CN');

        /* 定义前端视图目录 */
        View::addNamespace('frontend', resource_path('views/themes/' . $config['template']));

        /*
         * DB 宏扩展 判断表是否存在索引
         *
         * use Illuminate\Database\Query\Builder as DB
         *
         * exp:  DB::table('users')->hasIndex('key_name');
         */
        DB::macro('hasIndex', function ($key_name) {
            return with(new DBExtend($this, $key_name))->hasIndex();
        });

        /*
         * DB 宏扩展 添加表注释
         *
         * use Illuminate\Database\Query\Builder as DB
         *
         * exp:  DB::table('users')->comments('table comment');
         */
        DB::macro('comments', function ($content) {
            return with(new DBExtend($this, $content))->comments();
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
