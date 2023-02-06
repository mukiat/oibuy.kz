<?php

namespace App\Console\Commands;

use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\PayLog;
use App\Repositories\Common\BaseRepository;
use App\Services\Erp\JigonManageService;
use App\Services\Flow\FlowOrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OrderSeparateBillServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:order:separate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Order separate command';

    private $flowOrderService;

    public function __construct(
        FlowOrderService $flowOrderService
    )
    {
        parent::__construct();
        $this->flowOrderService = $flowOrderService;

        $load_helper = [
            'time', 'base', 'common', 'ecmoban', 'function'
        ];

        load_helper($load_helper);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $order_sn = '';
        if ($order_sn) {
            $res = OrderInfo::select('order_sn', 'main_order_id')->where('order_sn', $order_sn)->doesntHaveIn('getOrderGoods');
            $res = BaseRepository::getToArrayGet($res);

            if (!empty($res)) {
                $orderSnList = BaseRepository::getKeyPluck($res, 'order_sn');
                OrderInfo::whereIn('order_sn', $orderSnList)->delete();
            }
        }

        $order_sn = '';
        if ($order_sn) {
            $res = OrderInfo::select('order_sn', 'order_id')
                ->where('order_sn', $order_sn)
                ->where('main_order_id', 0)
                ->where('main_count', 0);
            $res = BaseRepository::getToArrayGet($res);
            $orderIdList = BaseRepository::getKeyPluck($res, 'order_id');

            OrderInfo::query()->select('order_id', 'pay_status', 'shipping_status', 'order_status', 'confirm_time', 'pay_time')
                ->whereIn('order_id', $orderIdList)
                ->where('main_count', 0)
                ->chunkById(1, function ($list) {
                    foreach ($list as $key => $row) {
                        $row = collect($row)->toArray();
                        $this->flowOrderService->OrderSeparateBill($row['order_id']);

                        $ru_number = OrderGoods::query()->where('order_id', $row['order_id'])->pluck('ru_id');
                        $ru_number = BaseRepository::getToArray($ru_number);
                        $ru_number = BaseRepository::getArrayUnique($ru_number);
                        $ru_number = count($ru_number);

                        OrderInfo::where('order_id', $row['order_id'])->update([
                            'main_count' => $ru_number
                        ]);

                        $child_order = OrderInfo::where('main_order_id', $row['order_id'])
                            ->select('order_id', 'order_sn');
                        $child_order = BaseRepository::getToArrayGet($child_order);

                        if ($child_order) {
                            foreach ($child_order as $childrow) {
                                $other = [
                                    'order_status' => $row['order_status'],
                                    'confirm_time' => $row['confirm_time'],
                                    'pay_status' => $row['pay_status'],
                                    'pay_time' => $row['pay_time'],
                                    'money_paid' => DB::raw("order_amount"),
                                    'order_amount' => 0
                                ];
                                OrderInfo::where('order_id', $childrow['order_id'])->update($other);

                                if ($row['pay_status'] == PS_PAYED) {
                                    app(JigonManageService::class)->jigonConfirmOrder($childrow['order_id']); // 贡云确认订单

                                    // 修改子订单 pay_log 状态
                                    PayLog::where('order_id', '>', 0)->where('order_id', $childrow['order_id'])->where('order_type', PAY_ORDER)->update(['is_paid' => 1]);
                                }

                                /* 记录订单操作记录 */
                                order_action($childrow['order_sn'], $row['order_status'], $row['shipping_status'], $row['order_status'], '', lang('payment.buyer'));
                            }
                        }
                    }
                });
        }


        dd(3333);
    }
}