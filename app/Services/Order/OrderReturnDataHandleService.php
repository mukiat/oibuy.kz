<?php

namespace App\Services\Order;

use App\Models\OrderReturn;
use App\Models\ReturnGoods;
use App\Repositories\Common\BaseRepository;

class OrderReturnDataHandleService
{
    /**
     * 是否退换货订单
     *
     * @param array $rec_id
     * @return array
     */
    public static function OrderReturn($rec_id = [])
    {
        if (empty($rec_id)) {
            return [];
        }

        $rec_id = array_unique($rec_id);

        $res = OrderReturn::distinct()->select('ret_id', 'rec_id', 'order_id')
            ->whereIn('rec_id', $rec_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['rec_id']] = $val;
            }
        }

        return $arr;
    }

    /**
     * 退换货商品列表
     *
     * @param array $ret_id
     * @param array $data
     * @return array
     */
    public static function returnGoodsRetIdDataList($ret_id = [], $data = [])
    {
        $id = BaseRepository::getExplode($ret_id);

        if (empty($id)) {
            return [];
        }

        $id = $id ? array_unique($id) : [];

        $data = $data ? $data : '*';

        $res = ReturnGoods::select($data)->whereIn('ret_id', $id);

        if (!empty($where)) {
            $res = $res->where($where);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['rg_id']] = $row;
            }
        }

        return $arr;
    }
}
