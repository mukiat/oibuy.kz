<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * 订单收货事件
 * Class OrderReceiveEvent
 * @package App\Events
 */
class OrderReceiveEvent
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
