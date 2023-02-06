<?php

use App\Models\SellerNegativeBill;
use App\Models\SellerNegativeOrder;
use App\Services\Commission\CommissionManageService;
use Illuminate\Database\Seeder;

class NegativeOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->negativeOrder();
        $this->getNegativeBill();
    }

    /**
     * 更新负账单订单
     */
    private function negativeOrder()
    {
        $commonService = app(CommissionManageService::class);

        $order_list = $commonService->commissionNegativeOrderList();

        if ($order_list) {
            $count = count($order_list);

            $order_list = collect($order_list);
            $order_list = $order_list->chunk(5);
            $order_list = $order_list->toArray();

            $k = 0;
            foreach ($order_list as $key => $val) {
                foreach ($val as $idx => $order) {
                    $bill_goods = $order['get_order_return']['get_seller_bill_goods'] ?? [];
                    $cat_proportion = $bill_goods['proportion'] ?? 0;
                    $commission_rate = $bill_goods['commission_rate'] ?? 0;

                    $commission = $commonService->commissionNegative($order, $cat_proportion, $commission_rate);

                    $other = [
                        'seller_proportion' => $commission['seller_proportion'],
                        'cat_proportion' => $cat_proportion,
                        'commission_rate' => $commission_rate,
                        'gain_commission' => $commission['gain_commission'],
                        'should_amount' => $commission['should_amount']
                    ];

                    SellerNegativeOrder::where('id', $order['id'])->update($other);

                    $k++;
                    dump("负账单订单：【" . $order['id'] . "】正在执行sql更新数据，请稍等.");
                    sleep(0.5);
                }
            }

            if ($count == $k) {
                dump("负账单订单sql执行更新数据完毕.");
            }
        }
    }

    /**
     * 更新负账单
     */
    private function getNegativeBill()
    {
        $bill_list = SellerNegativeBill::query()
            ->where('return_amount', '>', 0);

        $bill_list = $bill_list->with([
            'getSellerNegativeOrder'
        ]);

        $bill_list = $bill_list->get();

        $bill_list = $bill_list ? $bill_list->toArray() : [];

        if ($bill_list) {
            $count = count($bill_list);

            $bill_list = collect($bill_list);
            $bill_list = $bill_list->chunk(5);
            $bill_list = $bill_list->toArray();

            $k = 0;
            foreach ($bill_list as $key => $val) {
                foreach ($val as $idx => $order) {
                    $list = $order['get_seller_negative_order'] ?? [];

                    $should_amount = collect($list)->sum('should_amount');
                    $return_shippingfee = collect($list)->sum('return_shippingfee');
                    $actual_deducted = $should_amount + $return_shippingfee;

                    SellerNegativeBill::where('id', $order['id'])->update([
                        'actual_deducted' => $actual_deducted
                    ]);

                    $k++;
                    dump("负账单：【" . $order['id'] . "】正在执行sql更新数据，请稍等.");
                    sleep(0.5);
                }
            }

            if ($count == $k) {
                dump("负账单sql执行更新数据完毕.");
            }
        }
    }
}
