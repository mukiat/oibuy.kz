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
 * 订单发起售后事件
 * Class OrderDoneEvent
 * @package App\Events
 */
class OrderRefoundEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $request_info;
    public $extendParam;

    /**
     * Create a new event instance.
     *
     * OrderRefoundEvent constructor.
     * @param $request_info
     * @param array $extendParam
     */
    public function __construct($request_info, $extendParam = [])
    {
        $this->request_info = $request_info;
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
