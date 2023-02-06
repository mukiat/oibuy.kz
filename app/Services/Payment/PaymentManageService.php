<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;

/**
 * Class PaymentManageService
 * @package App\Services\Payment
 */
class PaymentManageService
{
    protected $commonRepository;

    public function __construct(
        CommonRepository $commonRepository
    )
    {
        $this->commonRepository = $commonRepository;
    }

    /**
     * 获取支付信息
     * @param array $where
     * @return array
     */
    public function getPaymentInfo($where = [])
    {
        if (empty($where)) {
            return [];
        }

        $res = Payment::whereRaw(1);

        if (isset($where['pay_id'])) {
            $res = $res->where('pay_id', $where['pay_id']);
        }

        if (isset($where['pay_name'])) {
            $res = $res->where('pay_name', $where['pay_name']);
        }

        if (isset($where['pay_code'])) {
            $res = $res->where('pay_code', $where['pay_code']);
        }

        if (isset($where['enabled'])) {
            $res = $res->where('enabled', $where['enabled']);
        }

        $res = BaseRepository::getToArrayFirst($res);

        return $res;
    }

    /**
     * 支付方式列表
     * @return array
     */
    public function paymentList()
    {
        $res = Payment::where('enabled', 1)
            ->orderBy('pay_order', 'ASC')
            ->get();

        $list = $res ? $res->toArray() : [];

        return $list;
    }

    /**
     * 检测支付方式是否重复安装
     * @param string $pay_code
     * @param int $pay_id
     * @return mixed
     */
    public function checkPaymentRepeat($pay_code = '', $pay_id = 0)
    {
        $count = Payment::where('enabled', 1)
            ->where('pay_code', $pay_code)
            ->where('pay_id', '<>', $pay_id)
            ->count();

        return $count;
    }

    /**
     * 检测支付方式是否曾经安装过
     * @param string $pay_code
     * @return mixed
     */
    public function checkPaymentCount($pay_code = '')
    {
        $count = Payment::where('pay_code', $pay_code)
            ->count();

        return $count;
    }

    /**
     * 更新支付方式
     * @param string $pay_code
     * @param array $data
     * @return bool
     */
    public function updatePayment($pay_code = '', $data = [])
    {
        if (empty($data)) {
            return false;
        }

        // 过滤表字段
        $data = BaseRepository::getArrayfilterTable($data, 'payment');

        $res = Payment::where('pay_code', $pay_code)->update($data);

        return $res;
    }

    /**
     * 新增支付方式
     * @param array $data
     * @return mixed
     */
    public function createPayment($data = [])
    {
        if (empty($data)) {
            return false;
        }

        // 过滤表字段
        $data = BaseRepository::getArrayfilterTable($data, 'payment');

        return Payment::create($data);
    }

    /**
     * 获取支付配置
     * @param string $code
     * @return array|mixed
     */
    public function getPayConfig($code = '')
    {
        if (empty($code)) {
            return [];
        }

        $pay_config = Payment::query()->where('enabled', 1)
            ->where('pay_code', $code)
            ->value('pay_config');

        if (!empty($pay_config)) {
            $pay_config = unserialize($pay_config);
        }

        return $pay_config;
    }
}
