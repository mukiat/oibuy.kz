<?php

namespace App\Repositories\Order;

use App\Models\PayLog;
use App\Repositories\Common\BaseRepository;


class PayLogRepository
{
    /**
     * info
     * @param int $log_id
     * @param array $columns
     * @return array
     */
    public static function getInfo($log_id = 0, $columns = [])
    {
        if (empty($log_id)) {
            return [];
        }

        $model = PayLog::where('log_id', $log_id);

        $model = $model->with([
            'orderInfo' => function ($query) {
                $query->where('main_order_id', 0)->select('order_id', 'order_sn', 'pay_time', 'pay_id', 'pay_name', 'ru_id');
            }
        ]);

        if (!empty($columns)) {
            $model = $model->select($columns);
        }

        $model = $model->first();

        return $model ? $model->toArray() : [];
    }

    /**
     * list
     * @param int $order_type
     * @param array $offset
     * @param array $filter
     * @return array
     */
    public static function list($order_type = PAY_ORDER, $offset = [], $filter = [])
    {
        // 已支付 金额大于0
        $model = PayLog::query()->where('is_paid', 1)->where('order_id', '>', 0)->where('order_amount', '>', 0)->where('order_type', $order_type);

        $keywords = $filter['keywords'] ?? '';
        $ru_id = $filter['ru_id'] ?? '-1';
        $pay_id = $filter['pay_id'] ?? 0;

        // 有分单 仅显示主订单
        $model = $model->whereHasIn('orderInfo', function ($query) use ($ru_id, $keywords, $pay_id) {
            $query = $query->where('main_order_id', 0);
            $query = $query->whereHasIn('getPayment', function ($query) {
                $query->whereNotIn('pay_code', ['cod', 'balance', 'bank', 'chunsejinrong']); // 不显示货到付款、余额支付、银行转账 支付方式
            });

            if ($ru_id >= 0) {
                $query = $query->where('ru_id', $ru_id);
            }
            if (!empty($keywords)) {
                // 订单号
                $query = $query->where('order_sn', 'like', '%' . $keywords . '%');
            }

            if ($pay_id > 0) {
                // 支付方式
                $query = $query->where('pay_id', $pay_id);
            }
        });

        if (!empty($keywords)) {
            // 交易单号
            $model = $model->orWhere(function ($query) use ($keywords) {
                $query = $query->where('transid', 'like', '%' . $keywords . '%');
                $query->orWhere('pay_trade_data', '<>', '')->where('pay_trade_data->transaction_id', $keywords);
            });
        }

        $model = $model->with([
            'orderInfo' => function ($query) {
                $query->where('main_order_id', 0)->select('order_id', 'order_sn', 'pay_time', 'pay_id', 'pay_name', 'ru_id');
            }
        ]);

        $total = $model->count();

        if (!empty($offset)) {
            $model = $model->offset($offset['start'])->limit($offset['limit']);
        }

        if (!empty($columns)) {
            $model = $model->select($columns);
        }

        $model = $model->orderBy('log_id', 'DESC')->get();

        $list = $model ? $model->toArray() : [];

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 将支付LOG插入数据表
     *
     * @param int $id
     * @param int $amount
     * @param int $type 支付类型
     * @param int $is_paid 是否已支付
     * @return bool
     */
    public static function insert_pay_log($id = 0, $amount = 0, $type = PAY_SURPLUS, $is_paid = 0)
    {
        if ($id) {
            $pay_log = [
                'order_id' => $id,
                'order_amount' => $amount,
                'order_type' => $type,
                'is_paid' => $is_paid
            ];

            return self::insertGetId($pay_log);
        } else {
            return 0;
        }
    }

    /**
     * insertGetId
     * @param array $data
     * @return bool
     */
    public static function insertGetId($data = [])
    {
        if (empty($data)) {
            return false;
        }

        $data = BaseRepository::getArrayfilterTable($data, 'pay_log');

        return PayLog::insertGetId($data);
    }

    /**
     * insert
     * @param array $data
     * @return bool
     */
    public static function insert($data = [])
    {
        if (empty($data)) {
            return false;
        }

        $data = BaseRepository::getArrayfilterTable($data, 'pay_log');

        return PayLog::insert($data);
    }

    /**
     * updateWhere
     * @param int $log_id
     * @param array $where
     * @param array $data
     * @return bool
     */
    public static function updateWhere($log_id = 0, $where = [], $data = [])
    {
        if (empty($log_id) || empty($where) || empty($data)) {
            return false;
        }

        $model = PayLog::query();

        if ($model->where($where)->exists()) {

            return $model->where($where)->update($data);
        }
        return false;
    }

    /**
     * updateOrInsert
     * @param array $where
     * @param array $data
     * @return bool
     */
    public static function updateOrInsert($where = [], $data = [])
    {
        if (empty($where) || empty($data)) {
            return false;
        }

        $model = PayLog::query();

        if (!$model->where($where)->exists()) {

            return $model->insert(array_merge($where, $data));
        }

        return $model->where($where)->update($data);
    }

    /**
     * delete
     * @param int $log_id
     * @return bool
     */
    public static function delete($log_id = 0)
    {
        if (empty($log_id)) {
            return false;
        }

        return PayLog::where('log_id', $log_id)->delete();
    }

}