<?php

namespace App\Custom;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;


class CustomServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerProviders();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }


    protected function registerProviders()
    {
        $providers = glob($this->app->path('Custom/*/*ServiceProvider.php'));

        $namespace = $this->app->getNamespace(); // APP

        foreach ($providers as $provider) {
            $providerClass = $namespace . str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    Str::after($provider, realpath(app_path()) . DIRECTORY_SEPARATOR)
                );

            $this->app->register($providerClass);
        }
    }
}
