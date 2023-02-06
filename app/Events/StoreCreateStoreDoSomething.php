<?php


namespace App\Events;


use Illuminate\Queue\SerializesModels;

class StoreCreateStoreDoSomething
{
    use SerializesModels;

    public $store;

    /**
     * 创建一个事件实例
     *
     * @param  array  $order
     */
    public function __construct(array $store)
    {
        $this->store = $store;
    }


}
