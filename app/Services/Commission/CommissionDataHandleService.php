<?php

namespace App\Services\Commission;

use App\Models\SellerBillOrder;
use App\Models\SellerCommissionBill;
use App\Repositories\Common\BaseRepository;

class CommissionDataHandleService
{
    /***
     * 获取订单的账单信息
     *
     * @param array $order_id
     * @return array
     */
    public function orderCommissionBill($order_id = [])
    {
        if (empty($order_id)) {
            return [];
        }

        $order_id = array_unique($order_id);

        $order = SellerBillOrder::distinct()->select('order_id', 'bill_id')
            ->whereIn('order_id', $order_id);
        $order = BaseRepository::getToArrayGet($order);

        $bill_id = BaseRepository::getKeyPluck($order, 'bill_id');
        $bill_id = $bill_id ? array_unique($bill_id) : [];

        $arr = [];
        if ($bill_id) {
            $bill = SellerCommissionBill::whereIn('id', $bill_id);
            $bill = BaseRepository::getToArrayGet($bill);

            if ($bill) {
                foreach ($bill as $key => $val) {
                    foreach ($order as $k => $v) {
                        if ($v['bill_id'] == $val['id']) {
                            $arr[$v['order_id']] = $val;
                        }
                    }
                }
            }
        }

        return $arr;
    }
}
