<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class PickupOrdersUpdateAfterDoSomething
{
    use SerializesModels;

    public $order;

    /**
     * 创建一个事件实例
     *
     * @param  array  $order
     */
    public function __construct(array $order)
    {
        $this->order = $order;
    }
}
