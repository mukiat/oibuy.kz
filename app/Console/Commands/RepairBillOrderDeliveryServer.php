<?php

namespace App\Console\Commands;

use App\Models\OrderAction;
use App\Models\OrderInfo;
use App\Models\SellerBillOrder;
use App\Models\ValueCardRecord;
use App\Services\Commission\CommissionService;
use App\Services\Order\OrderRefoundService;
use Illuminate\Console\Command;

class RepairBillOrderDeliveryServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:repair:delivery';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Repair bill Order delivery select status command';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle()
    {
        OrderInfo::query()->where('ru_id', '>', 0)
            ->where('main_count', 0)
            ->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED])
            ->whereIn('pay_status', [PS_PAYED, PS_REFOUND_PART])
            ->where('shipping_status', SS_RECEIVED)
            ->where('confirm_take_time', 0)
            ->chunkById(5, function ($list) {

                if ($list) {
                    $list = collect($list)->toArray();

                    foreach ($list as $key => $val) {

                        $val = collect($val)->toArray();

                        $orderAction = OrderAction::where('order_id', $val['order_id'])
                            ->where('order_status', $val['order_status'])
                            ->where('pay_status', $val['pay_status'])
                            ->where('shipping_status', $val['shipping_status'])
                            ->orderBy('log_time', 'desc');
                        $orderAction = $orderAction->first();
                        $orderAction = $orderAction ? $orderAction->toArray() : [];

                        $bill_order_info = SellerBillOrder::where('order_id', $val['order_id'])->count();

                        if ($bill_order_info <= 0 && !empty($orderAction)) {

                            $confirm_take_time = $orderAction['log_time'];

                            $value_card = ValueCardRecord::where('order_id', $val['order_id'])
                                ->where('add_val', 0)
                                ->value('use_val');
                            $value_card = $value_card ? $value_card : 0;

                            if (empty($val['get_seller_negative_order'])) {
                                $return_amount_info = app(OrderRefoundService::class)->orderReturnAmount($val['order_id']);
                            } else {
                                $return_amount_info['return_amount'] = 0;
                                $return_amount_info['return_rate_price'] = 0;
                                $return_amount_info['ret_id'] = [];
                            }

                            if ($val['order_amount'] > 0 && $val['order_amount'] > $val['rate_fee']) {
                                $order_amount = $val['order_amount'] - $val['rate_fee'];
                            } else {
                                $order_amount = $val['order_amount'];
                            }

                            $other = array(
                                'user_id' => $val['user_id'],
                                'seller_id' => $val['ru_id'],
                                'order_id' => $val['order_id'],
                                'order_sn' => $val['order_sn'],
                                'order_status' => $val['order_status'],
                                'shipping_status' => $val['shipping_status'],
                                'pay_status' => $val['pay_status'],
                                'order_amount' => $order_amount,
                                'return_amount' => $return_amount_info['return_amount'],
                                'goods_amount' => $val['goods_amount'],
                                'tax' => $val['tax'],
                                'shipping_fee' => $val['shipping_fee'],
                                'insure_fee' => $val['insure_fee'],
                                'pay_fee' => $val['pay_fee'] ?? 0,
                                'pack_fee' => $val['pack_fee'] ?? 0,
                                'card_fee' => $val['card_fee'] ?? 0,
                                'bonus' => $val['bonus'],
                                'integral_money' => $val['integral_money'] ?? 0,
                                'coupons' => $val['coupons'],
                                'discount' => $val['discount'],
                                'dis_amount' => $val['dis_amount'],
                                'vc_dis_money' => $val['vc_dis_money'],
                                'value_card' => $value_card ? $value_card : 0,
                                'money_paid' => $val['money_paid'],
                                'surplus' => $val['surplus'],
                                'confirm_take_time' => $confirm_take_time,
                                'rate_fee' => $val['rate_fee'],
                                'return_rate_fee' => $return_amount_info['return_rate_price']
                            );

                            if ($val['ru_id'] > 0 && $val['main_count'] == 0) {
                                app(CommissionService::class)->getOrderBillLog($other);
                                app(CommissionService::class)->setBillOrderReturn($return_amount_info['ret_id'], $other['order_id']);

                                OrderInfo::where('order_id', $val['order_id'])->update([
                                    'confirm_take_time' => $confirm_take_time
                                ]);

                                sleep(1);
                            }
                        }
                    }
                }
            });
    }
}