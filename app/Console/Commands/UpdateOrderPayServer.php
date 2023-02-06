<?php

namespace App\Console\Commands;

use App\Models\OrderInfo;
use App\Models\PayLog;
use App\Models\Payment;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use Illuminate\Console\Command;

class UpdateOrderPayServer extends Command
{
    /**
     * 主订单部分付款状态时，实际已微信支付或者支付宝支付完成付款。
     *
     * @var string
     */
    protected $signature = 'app:order:pay:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Order pay select status command';

    protected $commonRepository;

    public function __construct(
        CommonRepository $commonRepository
    )
    {
        parent::__construct();
        $this->commonRepository = $commonRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $load_helper = [
            'time', 'base', 'common', 'main', 'insert', 'goods', 'article',
            'ecmoban', 'function', 'seller_store', 'scws', 'wholesale'
        ];
        load_helper($load_helper);

        OrderInfo::query()->select('order_id', 'pay_status', 'main_count')->where('pay_status', PS_MAIN_PAYED_PART)->where('main_count', '>', 0)
            ->chunkById(5, function ($list) {
                foreach ($list as $key => $row) {
                    $row = collect($row)->toArray();

                    $pay_log = PayLog::where('order_id', $row['order_id'])
                        ->where('order_type', PAY_ORDER)
                        ->first();
                    $pay_log = $pay_log ? $pay_log->toArray() : [];

                    if ($pay_log['is_paid'] == 1) {

                        OrderInfo::where('order_id', $row['order_id'])->update([
                            'pay_status' => PS_UNPAYED
                        ]);

                        PayLog::where('order_id', $row['order_id'])
                            ->where('order_type', PAY_ORDER)->update([
                                'is_paid' => 0
                            ]);

                        $this->updateOrder($row['order_id']);
                    }
                }

                sleep(0.5);
            });
    }

    private function updateOrder($order_id = 0)
    {

        $res = OrderInfo::where('order_id', $order_id);

        $res->chunk(5, function ($list) {
            foreach ($list as $key => $value) {

                $value = collect($value)->toArray();

                $pay_log = PayLog::where('order_id', $value['order_id'])->where('is_paid', 0)
                    ->where('order_type', PAY_ORDER);

                $pay_log = BaseRepository::getToArrayFirst($pay_log);

                if ($pay_log && $pay_log['is_paid'] == 0) {
                    $payment = Payment::where('pay_id', $value['pay_id'])->first();
                    $payment = $payment ? $payment->toArray() : [];

                    if ($payment && strpos($payment['pay_code'], 'pay_') === false) {
                        $payObj = CommonRepository::paymentInstance($payment['pay_code']);

                        if (!is_null($payObj)) {
                            /* 判断类对象方法是否存在 */
                            if (is_callable([$payObj, 'orderQuery'])) {
                                $order_other = [
                                    'order_sn' => $value['order_sn'],
                                    'log_id' => $pay_log['log_id'],
                                    'order_amount' => $value['order_amount'],
                                ];

                                $return = $payObj->orderQuery($order_other);

                                if ($return) {
                                    dump($value['order_sn'] . "---更新成功");

                                    info($value['order_sn'] . "---更新成功");
                                }
                            }
                        }
                    }
                }
            }
        });
    }
}
