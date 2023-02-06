<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * 订单下单事件
 * Class OrderDoneEvent
 * @package App\Events
 */
class OrderDoneEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $extendParam;

    /**
     * Create a new event instance.
     *
     * @param array $order
     * @param array $extendParam
     * @return void|mixed
     */
    public function __construct($order, $extendParam = [])
    {
        $this->order = $order;
        $this->extendParam = $extendParam;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
