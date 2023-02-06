<?php

namespace App\Manager\LBS;

use Illuminate\Support\ServiceProvider;

/**
 * Class LbsServiceProvider
 * @package App\Manager\LBS;
 */
class LbsServiceProvider extends ServiceProvider
{
    /**
     * 服务注册
     */
    public function register()
    {
        $this->app->singleton('lbs', LbsManager::class);
    }

    /**
     * 服务启动
     */
    public function boot()
    {
        //
    }
}
