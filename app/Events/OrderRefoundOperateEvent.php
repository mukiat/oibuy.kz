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
 * 后台处理售后事件
 * Class OrderDoneEvent
 * @package App\Events
 */
class OrderRefoundOperateEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $rec_id;
    public $extendParam;

    /**
     * Create a new event instance.
     * OrderRefoundOperateEvent constructor.
     * @param $rec_id
     * @param array $extendParam
     */
    public function __construct($rec_id, $extendParam = [])
    {
        $this->rec_id = $rec_id;
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
