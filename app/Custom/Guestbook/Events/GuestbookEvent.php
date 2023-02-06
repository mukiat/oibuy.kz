<?php

namespace App\Custom\Guestbook\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class GuestbookEvent
 */
class GuestbookEvent
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
    public function __construct($param, $extendParam = [])
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
