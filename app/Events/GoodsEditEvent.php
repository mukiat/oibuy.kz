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
 * 商品编辑事件
 * Class GoodsEditEvent
 * @package App\Events
 */
class GoodsEditEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $handler;
    public $goods_info;
    public $extendParam;

    /**
     * Create a new event instance.
     *
     * @param string $handler
     * @param array $goods_info
     * @param array $extendParam
     * @return void|mixed
     */
    public function __construct($handler = '', $goods_info = [], $extendParam = [])
    {
        $this->handler = $handler;
        $this->goods_info = $goods_info;
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

