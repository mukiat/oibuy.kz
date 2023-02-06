<?php

namespace App\Listeners;

use App\Events\UserRegisterEvent;
use App\Repositories\User\UsersRepository;
use App\Services\User\UserCouponsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

/**
 * Class UserRegisterListener
 * @package App\Listeners
 */
class UserRegisterListener implements ShouldQueue
{
    use InteractsWithQueue;

    protected $userCouponsService;

    /**
     * Create the event listener.
     * @param UserCouponsService $userCouponsService
     *
     * @return void
     */
    public function __construct(UserCouponsService $userCouponsService)
    {
        $this->userCouponsService = $userCouponsService;
    }

    /**
     * Handle the event.
     *
     * @param \App\Events\UserRegisterEvent $event
     * @return bool|mixed
     */
    public function handle(UserRegisterEvent $event)
    {
        $user = $event->user ?? [];
        $user_id = $user['user_id'] ?? 0;

        if (empty($user) || empty($user_id)) {
            return false;
        }

        // 扩展参数
        $extendParam = $event->extendParam ?? [];

        // 开启异步队列 获取最新缓存配置
        $queue_connection = config('queue.default');
        if ($queue_connection != 'sync' && !empty($extendParam['shop_config'])) {
            config(['shop' => $extendParam['shop_config']]);
        }

        /**
         * 注册送积分
         */
        $pay_points = config('shop.register_points', 0);
        if (!empty($pay_points)) {
            UsersRepository::log_account_change($user_id, 0, 0, 0, $pay_points, trans('user.register_points'));
        }

        /**
         * 注册送优惠券
         */
        $this->userCouponsService->registerCoupons($user_id);

        /**
         * 推荐分成处理
         */
        $parent_id = $extendParam['parent_id'] ?? 0;
        if ($parent_id > 0) {
            $affiliate = config('shop.affiliate', []);
            $affiliate = empty($affiliate) ? [] : unserialize($affiliate);
            if (isset($affiliate['on']) && $affiliate['on'] == 1) {
                // 推荐开关开启
                $up_uid = UsersRepository::getAffiliate($parent_id);
                if ($up_uid > 0 && $up_uid != $user_id) {
                    $affiliate['config']['level_register_all'] = intval($affiliate['config']['level_register_all']);
                    $affiliate['config']['level_register_up'] = intval($affiliate['config']['level_register_up']);

                    if (!empty($affiliate['config']['level_register_all'])) {
                        if (!empty($affiliate['config']['level_register_up'])) {
                            $rank_points = DB::table('users')->where('user_id', $up_uid)->value('rank_points');

                            if ($rank_points + $affiliate['config']['level_register_all'] <= $affiliate['config']['level_register_up']) {
                                UsersRepository::log_account_change($up_uid, 0, 0, $affiliate['config']['level_register_all'], 0, sprintf(trans('user.register_affiliate'), $user_id, $user['user_name']));
                            }
                        } else {
                            UsersRepository::log_account_change($up_uid, 0, 0, $affiliate['config']['level_register_all'], 0, sprintf(trans('user.register_affiliate'), $user_id, $user['user_name']));
                        }
                    }

                    //设置推荐人
                    DB::table('users')->where('user_id', $user_id)->update(['parent_id' => $up_uid]);
                }
            }


            // 推荐注册赠送优惠券
            \App\Modules\Admin\Services\AffiliateCouponsService::userRegisterSendCoupons($user_id, $parent_id);

        }
    }
}
