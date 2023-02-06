<?php

namespace App\Repositories\Activity;

class ActivityRepository
{
    /**
     * 计算拍卖活动状态（注意参数一定是原始信息）
     *
     * @param $auction
     * @param int $time
     * @return int
     */
    public static function getAuctionStatus($auction, $time = 0)
    {
        //默认时间为当前时间的时间戳
        if ($time == 0) {
            $time = gmtime();
        }

        if (isset($auction['is_finished'])) {
            if ($auction['is_finished'] == 0) {
                if ($time < $auction['start_time']) {
                    return PRE_START; // 未开始
                } elseif ($time > $auction['end_time']) {
                    return FINISHED; // 已结束，未处理
                } else {
                    return UNDER_WAY; // 进行中
                }
            } elseif ($auction['is_finished'] == 1) {
                return FINISHED; // 已结束，未处理
            } else {
                return SETTLED; // 已结束，已处理
            }
        } else {
            return SETTLED; // 已结束，已处理
        }
    }

    /**
     * 活动订单类型
     *
     * @param array $order
     * @return array|\Illuminate\Contracts\Translation\Translator|null|string
     * @throws \Exception
     */
    public static function activityLang($order = [])
    {
        $activity_lang = '';
        if ($order['extension_code'] == 'team_buy') {
            $activity_lang = lang('user.order_is_team');
        }

        if ($order['extension_code'] == 'package_buy') {
            $activity_lang = lang('user.order_is_package');
        }

        if ($order['extension_code'] == 'presale') {
            $activity_lang = lang('user.order_is_presale');
        }

        if ($order['extension_code'] == 'group_buy') {
            $activity_lang = lang('user.order_is_group_buy');
        }

        if ($order['extension_code'] == 'exchange_goods') {
            $activity_lang = lang('user.order_is_exchange');
        }

        if ($order['extension_code'] == 'auction') {
            $activity_lang = lang('user.order_is_auction');
        }

        if ($order['extension_code'] == 'seckill') {
            $activity_lang = lang('user.order_is_seckill');
        }

        if ($order['extension_code'] == 'bargain_buy') {
            $activity_lang = lang('user.order_is_bargain_buy');
        }

        if ($order['extension_code'] == 'snatch') {
            $activity_lang = lang('user.order_is_snatch');
        }

        $activity_lang = $activity_lang ? str_replace(['[', ']'], '', $activity_lang) : '';

        return $activity_lang;
    }
}
