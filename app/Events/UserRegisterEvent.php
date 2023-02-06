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
 * 用户注册
 * Class UserRegisterEvent
 * @package App\Events
 */
class UserRegisterEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $extendParam;

    /**
     * Create a new event instance.
     *
     * @param array $user
     * @param array $extendParam
     * @return void|mixed
     */
    public function __construct($user, $extendParam = [])
    {
        $this->user = $user;
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
