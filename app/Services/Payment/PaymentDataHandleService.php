<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Repositories\Common\BaseRepository;

class PaymentDataHandleService
{
    /**
     * 支付方式列表
     *
     * @param array $pay_id
     * @param array $data
     * @return array
     */
    public static function getPaymentDataList($pay_id = [], $data = [])
    {
        $pay_id = BaseRepository::getExplode($pay_id);

        if (empty($pay_id)) {
            return [];
        }

        $pay_id = $pay_id ? array_unique($pay_id) : [];

        $data = $data ? $data : '*';

        $res = Payment::select($data)->whereIn('pay_id', $pay_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['pay_id']] = $row;
            }
        }

        return $arr;
    }
}