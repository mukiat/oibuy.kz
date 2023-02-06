<?php

namespace App\Console\Commands;

use App\Models\OrderInfo;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Order\OrderRefoundService;
use App\Services\Team\TeamService;
use App\Services\User\AccountService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TeamOrderServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:team:order {action=refund} {team_id?} {order_id?} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Team failed refund order command';

    protected $orderRefoundService;

    public function __construct(
        OrderRefoundService $orderRefoundService
    )
    {
        parent::__construct();
        $this->orderRefoundService = $orderRefoundService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $action = $this->argument('action');

        if ($action == 'refund') {
            $team_id = $this->argument('team_id');
            $team_id = $team_id ?? 0;

            $order_id = $this->argument('order_id');
            $order_id = $order_id ?? 0;

            $this->orderRefund($team_id, $order_id);
        }
    }

    /**
     * 原路退回拼团订单金额
     *
     * @param int $team_id
     * @param int $order_id
     * @return void
     */
    public function orderRefund($team_id = 0, $order_id = 0)
    {
        // 已支付 未退款 已过期、失败拼团订单
        $order_list = OrderInfo::query()->where('main_count', 0)
            ->where('extension_code', 'team_buy')
            ->where('pay_status', PS_PAYED)
            ->where('pay_status', '<>', PS_REFOUND);

        if ($team_id > 0) {
            $order_list = $order_list->where('team_id', $team_id);
        }

        if ($order_id > 0) {
            $order_list = $order_list->where('order_id', $order_id);
        }

        $time = TimeRepository::getGmTime();

        // 已过期、失败拼团订单
        $order_list = $order_list->whereHasIn('getTeamLog', function ($query) use ($time) {
            $query->whereHasIn('getTeamGoods', function ($query) use ($time) {
                $query = $query->where('status', 0)->where('is_show', 1);
                $query->where(function ($query) use ($time) {
                    $query->whereRaw("$time > (start_time + validity_time * 3600)")->orWhere('is_team', '<>', 1);
                });
            });
        });

        $order_list = $order_list->with([
            'getTeamLog' => function ($query) {
                $query = $query->select('team_id', 'goods_id', 't_id', 'start_time', 'status');
                $query->with([
                    'getTeamGoods' => function ($query) {
                        $query->select('id', 'validity_time', 'team_num', 'team_price', 'limit_num');
                    },
                    'getGoods' => function ($query) {
                        $query->select('goods_id', 'goods_name');
                    }
                ]);
            }
        ]);

        $order_list->select('order_id', 'order_sn', 'user_id', 'pay_id', 'pay_status', 'shipping_status', 'order_amount', 'surplus', 'money_paid', 'referer', 'ru_id', 'divide_channel', 'team_id')->orderBy('order_id')->chunk(10, function ($list) {
            foreach ($list as $key => $val) {
                $val = $val ? $val->toArray() : [];
                if (!empty($val)) {
                    // 在线退款 状态
                    $is_paid = false;
                    // 余额退款 状态
                    $surplus_is_paid = false;
                    $order = [
                        'order_amount' => $val['order_amount']
                    ];
                    // - 订单如果使用了余额 退余额
                    $surplus = empty($val['surplus']) ? 0 : $val['surplus'];
                    if ($surplus > 0) {
                        $order['surplus'] = 0;
                        $money_paid = $val['money_paid'] ?? 0;
                        $order['money_paid'] = ($money_paid > 0 && $money_paid >= $surplus) ? $money_paid - $surplus : 0;
                        $order['order_amount'] = $order['order_amount'] + $surplus;
                        // 退款到账户余额 并记录会员账目明细
                        $change_desc = trans('team.team_order_fail_refound') . $val['order_sn'] . '，' . trans('team.team_money') . '：' . $surplus;
                        $surplus_is_paid = AccountService::logAccountChange($val['user_id'], $surplus, 0, 0, 0, $change_desc);
                    }

                    // - 订单在线支付部分 原路退款
                    $money_paid = empty($val['money_paid']) ? 0 : $val['money_paid'];
                    if ($money_paid > 0) {
                        // 原路退款
                        $return_order = [
                            'order_id' => $val['order_id'],
                            'pay_id' => $val['pay_id'],
                            'pay_status' => $val['pay_status'],
                            'referer' => $val['referer'],
                            'return_sn' => $val['order_sn'],
                            'ru_id' => $val['ru_id'],
                        ];
                        $is_paid = OrderRefoundService::refoundPay($return_order, $money_paid);
                    }

                    if ($surplus_is_paid == true || $is_paid == true) {
                        // - 订单在线支付部分 原路退款
                        if ($money_paid > 0) {
                            $order['money_paid'] = 0;
                            $order['order_amount'] = $order['order_amount'] + $money_paid;
                        }

                        // - 订单使用了储值卡 退储值卡
                        $use_val = OrderRefoundService::returnValueCardMoney($val['order_id']);
                        if ($use_val > 0) {
                            $order['order_amount'] = $order['order_amount'] + $use_val;
                        }

                        //记录订单操作记录
                        $action_note = trans('team.team_order_fail_refound');

                        // 修改订单状态为已取消，付款状态为未付款
                        $order['order_status'] = OS_CANCELED;
                        $order['to_buyer'] = trans('team.cancel_order_reason'); // 拼团失败
                        $order['pay_status'] = PS_REFOUND;
                        $order['pay_time'] = 0;
                        $order['shipping_status'] = $val['shipping_status'];
                        TeamService::orderActionChange($val['order_id'], 'admin', $order, $action_note);

                        /* 更新会员拼团订单信息 */
                        DB::table('user_order_num')->where('user_id', $val['user_id'])->where('order_team_num', '>', 0)->decrement('order_team_num', 1);

                        //--库存管理 use_storage 1为开启 0为未启用-- stock_dec_time：0发货时,  1 SDT_PLACE 为下单时, 2 SDT_PAID 为付款时
                        if (config('shop.use_storage') == '1' && (config('shop.stock_dec_time') == SDT_PLACE || config('shop.stock_dec_time') == SDT_PAID)) {
                            // 退还商品库存
                            TeamService::changeOrderGoodsStorage($val['order_id'], false, SDT_PLACE);
                        }

                        // 拼团失败退款通知
                        if (file_exists(MOBILE_WECHAT)) {
                            $get_team_log = $val['get_team_log'] ?? [];
                            $get_team_goods = $get_team_log['get_team_goods'] ?? [];
                            $get_goods = $get_team_log['get_goods'] ?? [];

                            $get_goods['goods_name'] = $get_goods['goods_name'] ?? '';
                            $get_team_goods['team_price'] = $get_team_goods['team_price'] ?? 0;
                            $pushData = [
                                'keyword1' => ['value' => $val['order_sn'], 'color' => '#173177'],
                                'keyword2' => ['value' => $get_goods['goods_name'], 'color' => '#173177'],
                                'keyword3' => ['value' => trans('team.team_order_fail_refound'), 'color' => '#173177'],
                                'keyword4' => ['value' => app(DscRepository::class)->getPriceFormat($get_team_goods['team_price']), 'color' => '#173177']
                            ];
                            $url = dsc_url('/#/user/orderDetail/' . $val['order_id']);
                            app(\App\Modules\Wechat\Services\WechatService::class)->push_template('OPENTM400940587', $pushData, $url, $val['user_id']);
                        }
                    }

                }
            }
        });
    }
}
