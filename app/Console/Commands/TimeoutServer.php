<?php

namespace App\Console\Commands;

use App\Models\AccountLog;
use App\Models\CouponsUser;
use App\Models\OrderAction;
use App\Models\OrderInfo;
use App\Models\ReturnAction;
use App\Models\UserBonus;
use App\Models\UserOrderNum;
use App\Models\Users;
use App\Models\ValueCard;
use App\Models\ValueCardRecord;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Order\OrderRepository;
use App\Services\User\UserRankService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class TimeoutServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:timeout {action=pay} {order_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Timeout order pay select status command';

    protected $dscRepository;
    protected $userRankService;

    public function __construct(
        DscRepository $dscRepository,
        UserRankService $userRankService
    )
    {
        parent::__construct();
        $this->dscRepository = $dscRepository;
        $this->userRankService = $userRankService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $action = $this->argument('action');
        $order_id = $this->argument('order_id');

        if ($action == 'pay') {
            //订单时效
            $pay_effective_time = config('shop.pay_effective_time') > 0 ? intval(config('shop.pay_effective_time')) : 0;
            $order_id = $order_id ?? 0;
            $this->timeoutOrderPay($pay_effective_time, $order_id);
        }
    }

    //处理支付超时订单
    private function timeoutOrderPay($pay_effective_time = 0, $order_id = 0)
    {
        if ($pay_effective_time > 0) {
            $pay_effective_time = $pay_effective_time * 60;

            $time = TimeRepository::getGmTime();

            $list = OrderInfo::where('main_count', 0);

            if ($order_id > 0) {
                $list = $list->where('order_id', $order_id);
            }

            $list = $list->whereHas('getPayment', function ($query) {
                $query->whereNotIn('pay_code', ['cod', 'bank']);
            });

            $list = $list->whereRaw("($time - add_time) > $pay_effective_time")
                ->whereIn('order_status', [OS_UNCONFIRMED, OS_CONFIRMED])
                ->whereIn('shipping_status', [SS_UNSHIPPED, SS_PREPARING])
                ->where('pay_status', PS_UNPAYED);

            $list = $list->with([
                'getStoreOrder'
            ]);

            $list->chunk(10, function ($list) {
                foreach ($list as $k => $v) {
                    if ($v) {
                        $v = collect($v)->toArray();

                        if ($v['order_status'] != OS_INVALID) {
                            $store_id = $v['get_store_order']['store_id'] ?? 0;

                            /* 标记订单为“无效” */
                            OrderInfo::where('order_id', $v['order_id'])->update(['order_status' => OS_INVALID]);

                            /* 记录log */
                            $this->orderAction($v['order_id'], OS_INVALID, SS_UNSHIPPED, PS_UNPAYED, lang('order.order_pay_timeout'));

                            /* 如果使用库存，且下订单时减库存，则增加库存 */
                            if (config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_PLACE) {
                                OrderRepository::change_order_goods_storage($v['order_id'], false, SDT_PLACE, 2, 0, $store_id);
                            }

                            /* 退还用户余额、积分、红包 */
                            $this->returnUserSurplusIntegralBonus($v);

                            /* 更新会员订单数量 */
                            if (isset($v['user_id']) && !empty($v['user_id'])) {
                                $order_nopay = UserOrderNum::where('user_id', $v['user_id'])->value('order_nopay');
                                $order_nopay = $order_nopay ? intval($order_nopay) : 0;

                                if ($order_nopay > 0) {
                                    $dbRaw = [
                                        'order_nopay' => "order_nopay - 1",
                                    ];
                                    $dbRaw = BaseRepository::getDbRaw($dbRaw);
                                    UserOrderNum::where('user_id', $v['user_id'])->update($dbRaw);
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    /**
     * 生成日志
     *
     * @param int $order_id
     * @param int $order_status
     * @param int $shipping_status
     * @param int $pay_status
     * @param string $note
     * @throws \Exception
     */
    private function orderAction($order_id = 0, $order_status = 0, $shipping_status = 0, $pay_status = 0, $note = '')
    {
        $log_time = TimeRepository::getGmTime();

        if ($order_id > 0) {
            $other = [
                'order_id' => $order_id,
                'action_user' => lang('order.order_action_user'),
                'order_status' => $order_status,
                'shipping_status' => $shipping_status,
                'pay_status' => $pay_status,
                'action_note' => $note,
                'log_time' => $log_time
            ];
            OrderAction::insert($other);
        }
    }

    /**
     * 退回余额、积分、红包（取消、无效、退货时），把订单使用余额、积分、红包、优惠券设为0
     *
     * @param array $order
     * @throws \Exception
     */
    private function returnUserSurplusIntegralBonus($order = [])
    {
        $surplus = isset($order['surplus']) && $order['surplus'] > 0 ? $order['surplus'] : 0;

        /* 处理余额、积分、红包 */
        if ($order['user_id'] > 0 && $surplus > 0) {
            $surplus = $order['surplus'] > 0 ? $order['surplus'] : 0;
            $money_count = AccountLog::where('user_id', $order['user_id'])->where('user_money', "-" . $order['surplus'])->where('change_desc', 'like', '%' . $order['order_sn'] . '%')->count();

            if ($money_count > 0) {
                $this->logAccountChange($order['user_id'], $surplus, 0, 0, 0, sprintf(lang('admin/order.return_order_surplus'), $order['order_sn']), ACT_OTHER, 1);
            }

            OrderInfo::where('order_id', $order['order_id'])
                ->update(['order_amount' => 0]);
        }

        $integral = isset($order['integral']) && $order['integral'] > 0 ? $order['integral'] : 0;

        if ($order['user_id'] > 0 && $integral > 0) {
            $integral_count = AccountLog::where('user_id', $order['user_id'])->where('pay_points', "-" . $integral)->where('change_desc', 'like', '%' . $order['order_sn'] . '%')->count();
            if ($integral_count > 0) {
                $this->logAccountChange($order['user_id'], 0, 0, 0, $integral, sprintf(lang('admin/order.return_order_integral'), $order['order_sn']), ACT_OTHER, 1);
            }
        }

        if ($order['bonus_id'] > 0) {
            $other = [
                'order_id' => 0,
                'used_time' => 0
            ];
            UserBonus::where('bonus_id', $order['bonus_id'])->update($other);
        }

        /* 退优惠券 */
        if ($order['order_id'] > 0) {
            $coupons = OrderInfo::where('order_id', $order['order_id'])->value('coupons');
            //使用了优惠券才退券
            if ($coupons) {
                // 判断当前订单是否满足了返券要求
                $other = [
                    'order_id' => 0,
                    'is_use_time' => 0,
                    'is_use' => 0
                ];
                CouponsUser::where('is_delete', 0)->where('order_id', $order['order_id'])->update($other);
            }
        }

        /* 退储值卡 start */
        if ($order['order_id'] > 0) {
            $this->returnCardMoney($order['order_id']);
        }
        /* 退储值卡 end */

        /* 修改订单 */
        $arr = [
            'bonus_id' => 0,
            'bonus' => 0,
            'integral' => 0,
            'integral_money' => 0,
            'surplus' => 0
        ];

        OrderInfo::where('order_id', $order['order_id'])->update($arr);
    }

    /**
     * 记录帐户变动
     * @param int $user_id 用户id
     * @param int $user_money 可用余额变动[float]
     * @param int $frozen_money 冻结余额变动[float]
     * @param int $rank_points 等级积分变动
     * @param int $pay_points 消费积分变动
     * @param string $change_desc 变动说明
     * @param int $change_type 变动类型：参见常量文件
     * @param int $order_type
     * @param int $deposit_fee
     * @throws \Exception
     */
    private function logAccountChange($user_id, $user_money = 0, $frozen_money = 0, $rank_points = 0, $pay_points = 0, $change_desc = '', $change_type = ACT_OTHER, $order_type = 0, $deposit_fee = 0)
    {
        $is_go = true;
        $is_user_money = 0;
        $is_pay_points = 0;
        $order_sn = '';

        //控制只有后台执行，前台不操作以下程序
        if ($change_desc && $order_type) {
            $change_desc_arr = $change_desc ? explode(" ", $change_desc) : [];

            if (count($change_desc_arr) >= 2) {
                $order_sn = !empty($change_desc_arr[1]) ? $change_desc_arr[1] : '';

                if (!empty($order_sn)) {
                    $order_res = OrderInfo::select(['order_id', 'main_order_id'])->where('order_sn', $order_sn);
                    $order_res = BaseRepository::getToArrayFirst($order_res);
                } else {
                    $order_res = [];
                }

                if (empty($order_res)) {
                    $is_go = false;
                }

                if ($order_res) {
                    if ($order_res['main_order_id'] > 0) {  //操作无效或取消订单时，先查询该订单是否有主订单

                        $ordor_main = OrderInfo::select('order_sn')->where('order_id', $order_res['main_order_id']);
                        $ordor_main = BaseRepository::getToArrayFirst($ordor_main);

                        if ($ordor_main) {
                            $order_surplus_desc = sprintf(lang('user.return_order_surplus'), $ordor_main['order_sn']);
                            $order_integral_desc = sprintf(lang('user.return_order_integral'), $ordor_main['order_sn']);
                        } else {
                            $order_surplus_desc = '';
                            $order_integral_desc = '';
                        }

                        //查询该订单的主订单是否已操作过无效或取消订单
                        $change_desc = [$order_surplus_desc, $order_integral_desc];

                        $log_res = [];
                        if ($change_desc) {
                            $log_res = AccountLog::select('log_id')->whereIn('change_desc', $change_desc);
                            $log_res = BaseRepository::getToArrayGet($log_res);
                        }

                        if ($log_res) {
                            $is_go = false;
                        }
                    } else {
                        if ($order_res && $order_res['order_id'] > 0) {
                            $main_order_res = OrderInfo::select('order_id', 'order_sn')->where('main_order_id', $order_res['order_id']);
                            $main_order_res = BaseRepository::getToArrayGet($main_order_res);

                            if ($main_order_res > 0) {
                                foreach ($main_order_res as $key => $row) {
                                    $order_surplus_desc = sprintf(lang('user.return_order_surplus'), $row['order_sn']);
                                    $order_integral_desc = sprintf(lang('user.return_order_integral'), $row['order_sn']);

                                    $main_change_desc = [$order_surplus_desc, $order_integral_desc];
                                    $parent_account_log = AccountLog::select(['user_money', 'pay_points'])->whereIn('change_desc', $main_change_desc);
                                    $parent_account_log = BaseRepository::getToArrayGet($parent_account_log);

                                    if ($parent_account_log) {
                                        if ($user_money) {
                                            $is_user_money += $parent_account_log[0]['user_money'];
                                        }

                                        if ($pay_points) {
                                            $is_pay_points += $parent_account_log[1]['pay_points'];
                                        }
                                    }
                                }
                            }
                        }

                        if ($user_money) {
                            $user_money -= $is_user_money;
                        }

                        if ($pay_points) {
                            $pay_points -= $is_pay_points;
                        }
                    }
                }
            }
        } /**
         * 判断是否是支付订单操作
         * 【订单号不能为空】
         *
         */
        elseif ($change_desc) {
            if (strpos($change_desc, '：') !== false) {
                $change_desc_arr = explode("：", $change_desc);
            } else {
                $change_desc_arr = explode(" ", $change_desc);
            }

            if (count($change_desc_arr) >= 2) {
                if (!empty($change_desc_arr[0]) && ($change_desc_arr[0] == '支付订单' || $change_desc_arr[0] == '追加使用余额支付订单')) {
                    if (!empty($change_desc_arr[1])) {
                        $change_desc_arr[1] = trim($change_desc_arr[1]);
                    }

                    $order_sn = !empty($change_desc_arr[1]) ? $change_desc_arr[1] : '';

                    if ($order_sn) {
                        $order_res = OrderInfo::where('order_sn', $order_sn);
                        $order_res = BaseRepository::getToArrayFirst($order_res);
                    } else {
                        $order_res = [];
                    }

                    if (empty($order_res)) {
                        $is_go = false;
                    }
                }
            }
        }

        if (!empty($order_sn)) {
            $is_go = $this->dscRepository->filterAccountChangeOrder($order_sn, $user_id, $user_money, $pay_points, $is_go);
        }

        if ($is_go && ($user_money || $frozen_money || $rank_points || $pay_points)) {
            if (is_array($change_desc)) {
                $change_desc = implode('<br/>', $change_desc);
            }

            /* 插入帐户变动记录 */
            $account_log = [
                'user_id' => $user_id,
                'user_money' => $user_money,
                'frozen_money' => $frozen_money,
                'rank_points' => $rank_points,
                'pay_points' => $pay_points,
                'change_time' => TimeRepository::getGmTime(),
                'change_desc' => $change_desc,
                'change_type' => $change_type,
                'deposit_fee' => $deposit_fee
            ];

            AccountLog::insert($account_log);

            /* 更新用户信息 */
            $user_money = $user_money + $deposit_fee;
            $update_log = [
                'frozen_money' => DB::raw("frozen_money  + ('$frozen_money')"),
                'pay_points' => DB::raw("pay_points  + ('$pay_points')"),
                'rank_points' => DB::raw("rank_points  + ('$rank_points')")
            ];

            Users::where('user_id', $user_id)->increment('user_money', $user_money, $update_log);

            if (!$this->userRankService->judgeUserSpecialRank($user_id)) {

                /* 更新会员当前等级 start */
                $user_rank_points = Users::where('user_id', $user_id)->value('rank_points');
                $user_rank_points = $user_rank_points ? $user_rank_points : 0;

                $rank_row = [];
                if ($user_rank_points >= 0) {
                    //1.4.3 会员等级修改（成长值只有下限）
                    $rank_row = $this->userRankService->getUserRankByPoint($user_rank_points);
                }

                if ($rank_row) {
                    $rank_row['discount'] = $rank_row['discount'] / 100;
                } else {
                    $rank_row['discount'] = 1;
                    $rank_row['rank_id'] = 0;
                }

                /* 更新会员当前等级 end */
                Users::where('user_id', $user_id)->update([
                    'user_rank' => $rank_row['rank_id']
                ]);
            }
        }
    }

    /**
     * 退还订单使用的储值卡消费金额
     *
     * @param int $order_id
     * @param int $ret_id
     * @param string $return_sn
     * @throws \Exception
     */
    private function returnCardMoney($order_id = 0, $ret_id = 0, $return_sn = '')
    {
        $row = ValueCardRecord::where('order_id', $order_id);
        $row = BaseRepository::getToArrayFirst($row);

        if ($row) {
            $order_info = OrderInfo::where('order_id', $order_id);
            $order_info = BaseRepository::getToArrayFirst($order_info);

            /* 更新储值卡金额 */
            ValueCard::where('vid', $row['vc_id'])->increment('card_money', $row['use_val']);

            /* 更新储值卡金额使用日志 */
            $log = [
                'vc_id' => $row['vc_id'],
                'order_id' => $order_id,
                'use_val' => $row['use_val'],
                'vc_dis' => 1,
                'add_val' => $row['use_val'],
                'record_time' => TimeRepository::getGmTime()
            ];

            ValueCardRecord::insert($log);

            if ($return_sn) {
                $return_note = sprintf(lang('user.order_vcard_return'), $row['use_val']);
                $this->returnAction($ret_id, RF_AGREE_APPLY, FF_REFOUND, $return_note);

                $return_sn = "<br/>" . lang('order.order_return_running_number') . "：" . $return_sn;
            }

            $note = sprintf(lang('user.order_vcard_return') . $return_sn, $row['use_val']);
            $this->orderAction($order_info['order_sn'], $order_info['order_status'], $order_info['shipping_status'], $order_info['pay_status'], $note);
        }
    }

    /**
     * 记录订单操作记录
     *
     * @param $ret_id
     * @param $return_status
     * @param $refound_status
     * @param string $note
     */
    private function returnAction($ret_id, $return_status, $refound_status, $note = '')
    {
        if ($ret_id) {
            $other = [
                'ret_id' => $ret_id,
                'return_status' => $return_status,
                'refound_status' => $refound_status,
                'action_note' => $note,
                'log_time' => TimeRepository::getGmTime()
            ];
            ReturnAction::insert($other);
        }
    }
}
