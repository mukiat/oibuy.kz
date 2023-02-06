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
 * 订单删除操作事件
 * Class OrderRemoveEvent
 * @package App\Events
 */
class OrderRemoveEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $param;
    public $extendParam;

    /**
     * Create a new event instance.
     *
     * @param array $param
     * @param array $extendParam
     * @return void|mixed
     */
    public function __construct($param = [], $extendParam = [])
    {
        $this->param = $param;
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