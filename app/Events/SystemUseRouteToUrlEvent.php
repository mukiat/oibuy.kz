<?php


namespace App\Events;


use Illuminate\Queue\SerializesModels;

class SystemUseRouteToUrlEvent
{
    use SerializesModels;

    public $route;

    /**
     * 创建一个事件实例
     *
     * @param  array  $route
     */
    public function __construct(array $route)
    {
        $this->route = $route;
    }


}
