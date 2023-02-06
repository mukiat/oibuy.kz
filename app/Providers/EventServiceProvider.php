<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [

    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }

    /**
     * 确定是否应自动发现事件和侦听器。
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return true;
    }

    /**
     * 获取应该用于发现事件的监听器目录
     *
     * @return array
     */
    protected function discoverEventsWithin()
    {
        $listeners = glob($this->app->path('Modules/*/Listeners'));

        $customListeners = glob($this->app->path('Custom/*/Listeners'));

        return array_merge_recursive(
            [$this->app->path('Listeners')],
            $listeners,
            $customListeners
        );
    }
}
