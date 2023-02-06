<?php

namespace App\Listeners;

use App\Events\UserRemoveEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class UserRemoveListener
 * @package App\Listeners
 */
class UserRemoveListener
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param UserRemoveEvent $event
     * @return bool|mixed
     */
    public function handle(UserRemoveEvent $event)
    {
        $col = $event->user ?? [];
        // 扩展参数
        $extendParam = $event->extendParam ?? [];

        if (empty($col)) {
            return false;
        }

        if (file_exists(MOBILE_BARGAIN)) {
            // 删除砍价关联表
            \App\Models\BargainStatistics::whereIn('user_id', $col)->delete(); //删除会员参与砍价活动
            \App\Models\BargainStatisticsLog::whereIn('user_id', $col)->delete(); //删除砍价活动日志
        }

        if (config('app.debug')) {
            Log::info('remove_user:', $col);
        }

        return true;
    }
}
