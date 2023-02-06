<?php

namespace App\Console\Commands;

use App\Models\OrderAction;
use App\Models\OrderInfo;
use App\Models\SellerBillOrder;
use App\Models\UserOrderNum;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Order\OrderRepository;
use App\Services\Commission\CommissionService;
use App\Services\Order\OrderCommonService;
use App\Services\Order\OrderDataHandleService;
use App\Services\Order\OrderRefoundService;
use Illuminate\Console\Command;

class OrderDeliveryServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:order:delivery';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Order delivery select status command';

    protected $commonRepository;
    protected $orderCommonService;
    protected $orderRefoundService;
    protected $commissionService;

    public function __construct(
        CommonRepository $commonRepository,
        OrderCommonService $orderCommonService,
        OrderRefoundService $orderRefoundService,
        CommissionService $commissionService
    )
    {
        parent::__construct();
        $this->commonRepository = $commonRepository;
        $this->orderCommonService = $orderCommonService;
        $this->orderRefoundService = $orderRefoundService;
        $this->commissionService = $commissionService;
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $confirm_take_time = TimeRepository::getGmTime();

        // 自动确认收货操作
        $order_status = [
            OS_CONFIRMED,
            OS_RETURNED,
            OS_SPLITED,
            OS_SPLITING_PART,
            OS_RETURNED_PART,
            OS_ONLY_REFOUND
        ];

        $pay_status = [
            PS_PAYED,
            PS_REFOUND_PART
        ];

        $shipping_status = [
            SS_SHIPPED,
            SS_SHIPPED_PART,
            OS_SHIPPED_PART
        ];

        $auto_delivery_time = config('shop.auto_delivery_time') ?? 0;
        $res = OrderInfo::whereIn('order_status', $order_status)
            ->whereIn('pay_status', $pay_status)
            ->whereIn('shipping_status', $shipping_status)
            ->whereRaw("$confirm_take_time >= (shipping_time + 24 * 3600 * (IF($auto_delivery_time > 0 AND $auto_delivery_time > auto_delivery_time, $auto_delivery_time, auto_delivery_time)))")
            ->where('chargeoff_status', 0);

        $res = $res->with([
            'getValueCardRecord' => function ($query) {
                $query->where('add_val', 0);
            },
            'getSellerNegativeOrder'
        ]);

        $res->chunk(5, function ($list) use ($confirm_take_time) {

            $orderIdList = BaseRepository::getKeyPluck($list, 'order_id');
            $sellerBillOrderList = OrderDataHandleService::getBillOrderDataList($orderIdList, ['order_id', 'seller_id']);

            foreach ($list as $key => $value) {

                $value = collect($value)->toArray();

                // 订单是否全部发货 全部发货 => 全部收货、部分发货 => 部分收货
                $order_finish = OrderRepository::getAllDeliveryFinish($value['order_id']);
                $shipping_status = ($order_finish == 1) ? SS_RECEIVED : SS_PART_RECEIVED;

                //自动确认收货操作
                $data = [
                    'order_status' => $value['order_status'],
                    'shipping_status' => $shipping_status,
                    'pay_status' => $value['pay_status']
                ];

                if ($shipping_status == SS_RECEIVED) {
                    $data['confirm_take_time'] = $confirm_take_time;
                }

                OrderInfo::where('order_id', $value['order_id'])->update($data);

                if ($shipping_status == SS_RECEIVED) {
                    $order_nogoods = UserOrderNum::where('user_id', $value['user_id'])->value('order_nogoods');
                    $order_nogoods = $order_nogoods ? $order_nogoods : 0;

                    /* 更新会员订单信息 */
                    $dbRaw = [
                        'order_isfinished' => "order_isfinished + 1"
                    ];

                    if ($order_nogoods > 0) {
                        $dbRaw['order_nogoods'] = "order_nogoods - 1";
                    }

                    $dbRaw = BaseRepository::getDbRaw($dbRaw);
                    UserOrderNum::where('user_id', $value['user_id'])->update($dbRaw);
                }

                // 订单收货事件监听
                $extendParam = [
                    'shipping_status' => $shipping_status,
                    'note' => lang('admin/order.self_motion_goods'),
                    'action_name' => lang('admin/common.system_handle')
                ];
                event(new \App\Events\OrderReceiveEvent($value, $extendParam));

                if (empty($value['confirm_take_time']) && $value['main_count'] == 0) {

                    $billOrder = $sellerBillOrderList[$value['order_id']] ?? [];

                    if (!empty($billOrder) && $shipping_status == SS_RECEIVED) {
                        $confirm_take_time = OrderAction::where('order_id', $value['order_id'])
                            ->where('shipping_status', $shipping_status)
                            ->max('log_time');
                        $confirm_take_time = $confirm_take_time ? $confirm_take_time : '';

                        if (empty($confirm_take_time)) {
                            $confirm_take_time = TimeRepository::getGmTime();

                            $note = lang('admin/order.admin_order_list_motion');
                            $this->orderCommonService->orderAction($value['order_sn'], $value['order_status'], $shipping_status, $value['pay_status'], $note, lang('admin/common.system_handle'), 0, $confirm_take_time);
                        }

                        $log_other = array(
                            'confirm_take_time' => $confirm_take_time
                        );

                        OrderInfo::where('order_id', $value['order_id'])->update($log_other);

                        SellerBillOrder::where('order_id', $value['order_id'])->update($log_other);
                    }
                }
            }
        });
    }
}
