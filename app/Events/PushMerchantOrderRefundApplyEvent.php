<?php


namespace App\Events;


use Illuminate\Queue\SerializesModels;

class PushMerchantOrderRefundApplyEvent
{
    use SerializesModels;

    public $return_sn;

    /**
     * 创建一个事件实例
     *
     * @param  string  $return_sn
     */
    public function __construct($return_sn)
    {
        $this->return_sn = $return_sn;
    }


}
