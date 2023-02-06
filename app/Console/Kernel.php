<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /**
         * 服务器添加：* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
         */

        // 每小时执行一次任务
        $schedule->command('app:cron')->hourly();

        /* 每小时执行一次，查询已付款订单，改变付款状态为未付款的订单 */
        $schedule->command('app:order:pay')->hourly();

        /* 每天午夜执行一次任务（译者注：每天零点），查询已付款已发货订单，改变未收货的订单 */
        $schedule->command('app:order:delivery')->twiceDaily(1, 23);

        /* 每天午夜执行一次任务（译者注：每天零点），查询账单订单确认收货时间是零订单，并更新相应时间 */
        $schedule->command('app:commission confirmTakeTime')->twiceDaily(1, 23);

        /* 每天午夜执行一次任务（译者注：每天零点），自动删除三天前的导出记录数据 */
        $schedule->command('app:export:clear')->daily();

        /* 每天午夜执行一次任务（译者注：每天零点），查询账单订单数据是否存在 */
        $schedule->command('app:commission sorder')->daily();

        /* 每天 1 点 和 2 点分别执行一次任务，执行为生成账单 */
        $schedule->command('app:commission')->twiceDaily(1, 2);

        /* 每天 1 点 和 2 点分别执行一次任务，执行为生成账单详细数据 */
        $schedule->command('app:commission charge')->twiceDaily(1, 2);

        /* 每天午夜执行一次任务（译者注：每天零点），执行账单订单佣金插入数据 */
        $schedule->command('app:commission settlement')->daily();

        /* 每天凌晨2点，执行清除商品浏览记录超过70天数据 */
        $schedule->command('app:goods:history:clear')->dailyAt('2:00');

        /* 每天每两个小时执行一次任务，执行订单失效操作 */
        $schedule->command('app:timeout')->cron('0 */2 * * *');

        /* 每天凌晨4点钟执行，账单订单补单 */
        $schedule->command('app:commission replacement')->dailyAt('4:00');

        /* 每天 7 点 和 23 点分别执行一次任务， 自动发送邮件队列中未发送的邮件 */
        $schedule->command('app:email:send')->twiceDaily(7, 23);

        // 每天 5 点 和 22 点 订单自动评价
        $schedule->command('app:order:evaluation')->twiceDaily(5, 22);


        /**
         * 拼团模块计划任务
         */
        if (file_exists(MOBILE_TEAM)) {
            // 每天 23 点 拼团失败订单原路退款
            $schedule->command('app:team:order')->dailyAt('23:00');
        }

        /**
         * 分销模块计划任务
         */
        if (file_exists(MOBILE_DRP)) {
            /* 每天 3 点 和 20 点执行一次任务，解除过期的分销客户关系 */
            $schedule->command('app:drp children')->twiceDaily(3, 20);
            /* 每天 4 点 和 21 点执行一次任务，执行检查更新分销商权益过期时间 */
            $schedule->command('app:drp check_expiry_time')->twiceDaily(4, 21);
            /* 每天 5 点、 22 点执行一次任务，自动佣金分成 */
            $schedule->command('app:drp separate')->twiceDaily(5, 22);
        }

        /**
         * 视频号相关任务
         */
        if (file_exists(WXAPP_MEDIA)) {

            /* 每天凌晨4点钟执行，推广员订单分成 */
            $schedule->command('app:media:order')->dailyAt('4:00');
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
