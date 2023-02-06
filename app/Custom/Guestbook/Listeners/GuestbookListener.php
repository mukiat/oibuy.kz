<?php

namespace App\Custom\Guestbook\Listeners;

use App\Custom\Guestbook\Events\GuestbookEvent;

/**
 * Class GuestbookListener
 */
class GuestbookListener
{

    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param  GuestbookEvent $event
     * @return bool|mixed
     */
    public function handle(GuestbookEvent $event)
    {
        $param = $event->param ?? [];

        // 扩展参数
        $extendParam = $event->extendParam ?? [];

        //
        $data = [
            '1' => '123',
        ];

        return $data;
    }
}
