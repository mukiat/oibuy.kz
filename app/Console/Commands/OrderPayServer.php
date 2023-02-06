<?php

namespace App\Console\Commands;

use App\Models\OrderInfo;
use App\Models\PayLog;
use App\Models\Payment;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\TimeRepository;
use Illuminate\Console\Command;

class OrderPayServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:order:pay';

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

        /* 查询前三天订单 */
        $threeDay = TimeRepository::timePeriod(9, '-', 3);

        $order_status = [
            OS_UNCONFIRMED,
            OS_CONFIRMED,
            OS_SPLITED
        ];

        $pay_status = [
            PS_PAYING,
            PS_UNPAYED
        ];

        $res = OrderInfo::whereIn('order_status', $order_status)
            ->whereIn('pay_status', $pay_status)
            ->where('add_time', '>=', $threeDay);

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

                                $payObj->orderQuery($order_other);
                            }
                        }
                    }
                }
            }
        });
    }
}
