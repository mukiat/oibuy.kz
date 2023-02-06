<?php

namespace App\Services\Order;

use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Order\PayLogRepository;
use App\Repositories\Payment\PaymentRepository;
use App\Repositories\Seller\SellerShopinfoRepository;


class PayLogService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 支付单列表
     * @param int $order_type
     * @param array $offset
     * @param array $filter
     * @return array
     */
    public function getList($order_type = PAY_ORDER, $offset = [], $filter = [])
    {
        $result = PayLogRepository::list($order_type, $offset, $filter);

        if (!empty($result['list'])) {
            foreach ($result['list'] as $key => $value) {
                $result['list'][$key] = $value = collect($value)->merge($value['order_info'])->except('order_info')->all();

                $result['list'][$key]['pay_time_formated'] = TimeRepository::getLocalDate(config('shop.time_format'), $value['pay_time'] ?? '');
                $result['list'][$key]['order_amount_formated'] = $this->dscRepository->getPriceFormat($value['order_amount']);
                $result['list'][$key]['trans_status_formated'] = trans('admin/pay_log.trans_status_' . $value['is_paid']);
                $result['list'][$key]['divide_channel_formated'] = trans('admin/pay_log.divide_channel_' . $value['divide_channel']);
                $result['list'][$key]['shop_name'] = SellerShopinfoRepository::getShopName($value['ru_id'] ?? 0);

                // 交易数据
                if (!empty($value['pay_trade_data'])) {
                    $value['pay_trade_data'] = json_decode($value['pay_trade_data'], true);
                    $result['list'][$key]['transid'] = $value['pay_trade_data']['transaction_id'] ?? '';
                }
            }
        }

        return $result;
    }

    /**
     * 商家名称列表
     * @param int $limit
     * @return mixed
     */
    public function seller_list($limit = 100)
    {
        $result = cache()->remember('seller_list', config('shop.cache_time', 3600), function () use ($limit) {
            return SellerShopinfoRepository::getShopList($limit);
        });

        return $result;
    }

    /**
     * 支付方式列表 for 支付单列表筛选
     * @return mixed
     */
    public function online_payment_list()
    {
        return PaymentRepository::online_payment_list();
    }


    /**
     *
     * @param int $log_id
     * @param array $columns
     * @return array
     */
    public function detail($log_id = 0, $columns = [])
    {
        $info = PayLogRepository::getInfo($log_id, $columns);

        if (!empty($info)) {
            // 订单信息
            $info = collect($info)->merge($info['order_info'])->except('order_info')->all();
            $info['pay_time_formated'] = TimeRepository::getLocalDate(config('shop.time_format'), $info['pay_time'] ?? '');
            // 交易记录
            $info['order_amount_formated'] = $this->dscRepository->getPriceFormat($info['order_amount']);
            $info['pay_trade_data'] = !empty($info['pay_trade_data']) ? json_decode($info['pay_trade_data'], true) : [];
        }

        return $info;
    }
}