<?php

namespace App\Services\Cron;

use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use Illuminate\Support\Carbon;

/**
 * 后台计划任务
 * Class Comment
 *
 * @package App\Services
 */
class CronManageService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    ) {
        $this->dscRepository = $dscRepository;
    }

    public function get_next_time($cron)
    {
        // 当前的日期
        $currentTime = Carbon::now('UTC');
        $currentDay = $currentTime->format('d');
        $currentWeek = $currentTime->format('w') - 1; // Start on Sunday, index 0
        $currentHour = $currentTime->format('H');
        $currentTime = $currentTime->hour(0)->minute(0)->second(0);

        if ($cron['day']) {
            // 如果当天在设置的日期之后为本月，否则为下月日期
            if ($cron['day'] > $currentDay) {
                $currentTime = $currentTime->days($cron['day']);
            } else {
                $currentTime = $currentTime->days($cron['day'])->addMonth();
            }
        } elseif ($cron['week']) {
            $weekIndex = $cron['week'] - 1;
            if ($weekIndex > $currentWeek) {
                $currentTime = $currentTime->weekday($weekIndex);
            } else {
                $currentTime = $currentTime->weekday($weekIndex)->addWeek();
            }
        } elseif ($cron['hour']) {
            if ($cron['hour'] > intval($currentHour)) {
                $currentTime = $currentTime->hour($cron['hour']);
            } else {
                $currentTime = $currentTime->hour($cron['hour'])->addDays();
            }

            $currentTime = $currentTime->subHours(config('shop.timezone'));
        }

        return strtotime($currentTime);
    }

    public function get_minute($cron_minute)
    {
        $cron_minute = explode(',', $cron_minute);
        $cron_minute = array_unique($cron_minute);
        foreach ($cron_minute as $key => $val) {
            if ($val) {
                $val = intval($val);
                $val < 0 && $val = 0;
                $val > 59 && $val = 59;
                $cron_minute[$key] = $val;
            }
        }
        return trim(implode(',', $cron_minute));
    }

    public function get_dwh()
    {
        $days = $week = $hours = [];
        for ($i = 1; $i <= 31; $i++) {
            $days[$i] = str_pad($i, 2, '0', STR_PAD_LEFT);
        }
        for ($i = 1; $i < 8; $i++) {
            $week[$i] = $GLOBALS['_LANG']['week'][$i];
        }
        for ($i = 0; $i < 24; $i++) {
            $hours[$i] = str_pad($i, 2, '0', STR_PAD_LEFT);
        }

        return [$days, $week, $hours];
    }
}
