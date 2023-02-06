<?php

namespace App\Modules\Stores\Repositories;

use App\Models\Payment;
use App\Models\StoreOrder;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\TimeRepository;

/**
 * Class StoreOrderRepository
 * @package App\Modules\Stores\Repositories
 */
class StoreOrderRepository
{
    /**
     * 新增
     * @param $data
     * @return bool
     */
    public static function create($data)
    {
        if (empty($data)) {
            return false;
        }

        return StoreOrder::insert($data);
    }

    /**
     * 修改
     * @param int $id
     * @param array $data
     * @return bool
     */
    public static function update($id = 0, $data = [])
    {
        if (empty($id) || empty($data)) {
            return false;
        }

        $data = BaseRepository::getArrayfilterTable($data, 'store_order');

        return StoreOrder::where('id', $id)->update($data);
    }

    /**
     * 删除
     * @param int $id
     * @return bool
     */
    public static function delete($id = 0)
    {
        if (empty($id)) {
            return false;
        }

        return StoreOrder::where('id', $id)->delete();
    }

    /**
     * 查询信息
     * @param int $store_id
     * @param int $order_id
     * @param array $columns
     * @return array
     */
    public function storeOrderInfo($store_id = 0, $order_id = 0, $columns = [])
    {
        if (empty($store_id) || empty($order_id)) {
            return [];
        }

        $model = StoreOrder::query()->where('store_id', $store_id)->where('order_id', $order_id)->where('is_grab_order', 0);

        $model = $model->with([
            'orderInfo' => function ($query) {
                $query = $query->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART]);
                $query->select('order_id', 'order_sn', 'user_id', 'order_status', 'pay_status', 'shipping_status', 'pay_id', 'is_update_sale');
            }
        ]);

        if (!empty($columns)) {
            $model = $model->select($columns);
        }

        $result = $model->first();

        return $result ? $result->toArray() : [];
    }

    /**
     * 支付方式code
     * @param int $pay_id
     * @return string
     */
    public function payment_code($pay_id = 0)
    {
        if (empty($pay_id)) {
            return '';
        }

        return Payment::where('pay_id', $pay_id)->value('pay_code');
    }

    /**
     * 核销订单数量
     *
     * @param int $store_id
     * @param string $type
     * @return array|int
     */
    public function takeOrderStatistics($store_id = 0, $type = '')
    {
        if (empty($store_id)) {
            return [];
        }

        /**
         * 核销订单
         * 1. 条件 is_grab_order = 0 store_id > 0
         * 2. 状态 已核销：已确认 已付款 收货确认; 未核销：已确认 已付款 未发货;
         */
        $model = StoreOrder::query()->where('store_id', $store_id)->where('is_grab_order', 0);

        if ($type === 'haved') {
            // 已核销：订单状态 已确认、已支付、已收货订单
            $model = $model->whereHasIn('orderInfo', function ($query) {
                $query->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART])->where('pay_status', PS_PAYED)->where('shipping_status', SS_RECEIVED);
            });

        } elseif ($type === 'today') {
            // 今日已核销：订单状态 已确认、已支付、已收货订单
            $model = $model->whereHasIn('orderInfo', function ($query) {
                $query = $query->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART])->where('pay_status', PS_PAYED)->where('shipping_status', SS_RECEIVED);
                $query->where('confirm_take_time', '>=', TimeRepository::getLocalMktime(0, 0, 0, date('m'), date('d'), date('Y')));
            });

        } elseif ($type === 'wait') {
            // 待核销： 订单状态 已确认、已支付、未收货
            $model = $model->whereHasIn('orderInfo', function ($query) {
                $query->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART])->where('pay_status', PS_PAYED)->where('shipping_status', '<>', SS_RECEIVED);
            });
        }

        return $model->count();
    }

    /**
     * 查询核销订单信息
     *
     * @param int $store_id
     * @param string $pick_code
     * @param array $columns
     * @return array
     */
    public static function searchStoreOrder($store_id = 0, $pick_code = '', $columns = [])
    {
        if (empty($store_id) || empty($pick_code)) {
            return [];
        }

        /**
         * 核销订单
         * 1. 条件 is_grab_order = 0 store_id > 0
         * 2. 订单 已支付，提货码匹配
         */
        $model = StoreOrder::query()->where('store_id', $store_id)->where('is_grab_order', 0);

        $model = $model->where(function ($query) use ($pick_code) {
            $query = $query->where('pick_code', $pick_code)->orWhereHasIn('orderInfo', function ($query) use ($pick_code) {
                $query->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART])->where('pay_status', PS_PAYED)->where('order_sn', $pick_code);
            });
        });

        $model = $model->with([
            'orderInfo' => function ($query) {
                $query = $query->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART])->where('pay_status', PS_PAYED);
                $query = $query->select('order_id', 'order_sn', 'user_id', 'mobile', 'shipping_status', 'confirm_take_time', 'postscript', 'consignee', 'country', 'province', 'city', 'district', 'street', 'address');

                $query = $query->with([
                    // 订单商品
                    'goods' => function ($query) {
                        $query = $query->select('order_id', 'goods_id', 'goods_price', 'goods_number', 'goods_name', 'goods_sn', 'goods_attr');
                        $query->with([
                            'getGoods' => function ($query) {
                                $query->select('goods_id', 'goods_thumb');
                            }
                        ]);
                    },
                    // 订单用户
                    'getUsers' => function ($query) {
                        $query->select('user_id', 'user_name');
                    },
                    // 订单收货地址
                    'getRegionProvince' => function ($query) {
                        $query->select('region_id', 'region_name');
                    },
                    'getRegionCity' => function ($query) {
                        $query->select('region_id', 'region_name');
                    },
                    'getRegionDistrict' => function ($query) {
                        $query->select('region_id', 'region_name');
                    },
                    'getRegionStreet' => function ($query) {
                        $query->select('region_id', 'region_name');
                    }
                ]);
            }
        ]);

        if (!empty($columns)) {
            $model = $model->select($columns);
        }

        $result = $model->first();

        return $result ? $result->toArray() : [];
    }

    /**
     * 核销订单列表
     *
     * @param int $store_id
     * @param string $type
     * @param array $offset
     * @return array
     */
    public function storeOrderList($store_id = 0, $type = '', $offset = [])
    {
        if (empty($store_id)) {
            return [];
        }

        /**
         * 核销订单
         * 1. 条件 is_grab_order = 0 store_id > 0
         * 2. 状态 已核销：已确认 已付款 收货确认; 未核销：已确认 已付款 未发货;
         */
        $model = StoreOrder::query()->from('store_order as so')->where('so.store_id', $store_id)->where('so.is_grab_order', 0);

        $model = $model->whereIn('o.order_status', [OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART])->where('o.pay_status', PS_PAYED);

        if ($type === 'haved') {
            // 已核销：订单状态 已确认、已支付、已收货订单
            $model = $model->where('o.shipping_status', SS_RECEIVED);

        } elseif ($type === 'today') {
            // 今日已核销：订单状态 已确认、已支付、已收货订单
            $model = $model->where('o.shipping_status', SS_RECEIVED)->where('o.confirm_take_time', '>=', TimeRepository::getLocalMktime(0, 0, 0, date('m'), date('d'), date('Y')));
        } elseif ($type === 'wait') {
            // 待核销： 订单状态 已确认、已支付、未收货
            $model = $model->where('o.shipping_status', '<>', SS_RECEIVED);
        }

        $model = $model->leftJoin('order_info as o', 'o.order_id', '=', 'so.order_id');

        if (!empty($offset)) {
            $model = $model->offset($offset['start'])->limit($offset['limit']);
        }

        $model = $model->select('so.store_id', 'so.order_id', 'so.take_time', 'o.order_sn', 'o.user_id', 'o.mobile', 'o.shipping_status', 'o.confirm_take_time')
            ->orderBy('o.confirm_take_time', 'DESC')
            ->orderBy('so.order_id', 'DESC')
            ->get();

        return $model ? $model->toArray() : [];
    }

    /**
     * 核销订单详情
     *
     * @param int $store_id
     * @param int $order_id
     * @param array $columns
     * @return array
     */
    public function storeOrderDetail($store_id = 0, $order_id = 0, $columns = [])
    {
        if (empty($store_id) || empty($order_id)) {
            return [];
        }

        /**
         * 核销订单
         * 1. 条件 is_grab_order = 0 store_id > 0
         * 2. 订单 已支付
         */
        $model = StoreOrder::query()->where('store_id', $store_id)->where('is_grab_order', 0);

        $model = $model->where('order_id', $order_id);

        $model = $model->with([
            'orderInfo' => function ($query) {
                $query = $query->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART])->where('pay_status', PS_PAYED);
                $query = $query->select('order_id', 'order_sn', 'user_id', 'mobile', 'shipping_status', 'confirm_take_time', 'postscript', 'consignee', 'country', 'province', 'city', 'district', 'street', 'address');

                $query = $query->with([
                    // 订单商品
                    'goods' => function ($query) {
                        $query = $query->select('order_id', 'goods_id', 'goods_price', 'goods_number', 'goods_name', 'goods_sn', 'goods_attr');
                        $query->with([
                            'getGoods' => function ($query) {
                                $query->select('goods_id', 'goods_thumb');
                            }
                        ]);
                    },
                    // 订单用户
                    'getUsers' => function ($query) {
                        $query->select('user_id', 'user_name');
                    },
                    // 订单收货地址
                    'getRegionProvince' => function ($query) {
                        $query->select('region_id', 'region_name');
                    },
                    'getRegionCity' => function ($query) {
                        $query->select('region_id', 'region_name');
                    },
                    'getRegionDistrict' => function ($query) {
                        $query->select('region_id', 'region_name');
                    },
                    'getRegionStreet' => function ($query) {
                        $query->select('region_id', 'region_name');
                    }
                ]);
            }
        ]);

        if (!empty($columns)) {
            $model = $model->select($columns);
        }

        $result = $model->first();

        return $result ? $result->toArray() : [];
    }
}
