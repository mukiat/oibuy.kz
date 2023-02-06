<?php


namespace App\Events;


use Illuminate\Queue\SerializesModels;

class PushMerchantOrderPayedEvent
{
    use SerializesModels;

    public $order_sn;

    /**
     * 创建一个事件实例
     *
     * @param  string  $order_sn
     */
    public function __construct($order_sn)
    {
        $this->order_sn = $order_sn;
    }


}
