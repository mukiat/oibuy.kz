<?php

use App\Models\SellerBillOrder;
use App\Models\SellerBillGoods;
use App\Models\SellerCommissionBill;
use Illuminate\Database\Seeder;
use App\Console\Commands\CommissionServer;

class CommissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->settlement();
        //$this->getCommissionNegativeAmount();
        //$this->Commission();
    }

    public function settlement()
    {
        $file = Storage::disk('local')->exists('seeder/commission_order.lock.php');
        if (!$file) {
            app(CommissionServer::class)->commissionOrderSettlement(1);

            $data = '大商创x https://www.dscmall.cn/';
            Storage::disk('local')->put('seeder/commission_order.lock.php', $data);
        }
    }

    /**
     * 操作删除账单主订单
     *
     * @param int $type [0：未出账，1：已出账]
     * @param int $is_settlement [大于1：已结算，小于等于1：未结算]
     */
    private function Commission($type = 0, $is_settlement = 0)
    {
        $res = SellerBillOrder::whereHasIn('getOrder', function ($query) {
            $query->where('main_count', '>', 0);
        });

        if ($type > 0) {

            /* 已出账的账单 */
            $res = $res->where('bill_id', '>', 0);

            $res = $res->with([
                'getSellerCommissionBill' => function ($query) {
                    $query->select('id', 'bill_sn', 'chargeoff_status');
                }
            ]);
        } else {

            /* 未出账的账单 */
            $res = $res->where('bill_id', 0);
        }

        if ($is_settlement > 0) {
            /* 已结算 */
            $res = $res->where('chargeoff_status', '>', 1);
        } else {
            /* 未结算 */
            $res = $res->where('chargeoff_status', '<=', 1);
        }

        $res = $res->get();

        $res = $res ? $res->toArray() : [];

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $val['bill_sn'] = $val['get_seller_commission_bill']['bill_sn'] ?? '';
                $arr[$key]['bill_id'] = $val['bill_id'];
                $arr[$key]['bill_sn'] = $val['bill_sn'];
                $arr[$key]['order_id'] = $val['order_id'];
                $arr[$key]['order_sn'] = $val['order_sn'];
                $arr[$key]['seller_id'] = $val['seller_id'];

                /*SellerBillOrder::where('order_id', $val['order_id'])->delete();
                SellerBillGoods::where('order_id', $val['order_id'])->delete();

                dump('正在执行删除操作...');*/
            }
        }
    }

    /**
     * 更新无效的账单里面负账单金额
     */
    private function getCommissionNegativeAmount()
    {
        $res = SellerCommissionBill::where('negative_amount', '>', 0);
        $res = $res->with([
            'getSellerNegativeBillList'
        ]);

        $res = $res->get();

        $res = $res ? $res->toArray() : [];

        if ($res) {
            foreach ($res as $key => $val) {
                $negative = $val['get_seller_negative_bill_list'] ?? [];

                $collection = collect($negative);
                $return_amount = $collection->sum('return_amount');

                $collection = collect($negative);
                $return_shippingfee = $collection->sum('return_shippingfee');

                $negative_amount = $return_amount + $return_shippingfee;
                $negative_amount = floatval($negative_amount);

                $val['negative_amount'] = floatval($val['negative_amount']);

                if ($val['negative_amount'] > 0 && $val['negative_amount'] != $negative_amount) {
                    SellerCommissionBill::where('id', $val['id'])->update([
                        'negative_amount' => 0
                    ]);
                }
            }
        }
    }
}
