<?php

namespace App\Services\ExpressManage;

use App\Services\Merchant\MerchantCommonService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Class ExpressManageService
 * @package App\Services\ExpressManage
 */
class ExpressManageService
{
    /**
     * @var MerchantCommonService
     */
    protected $merchantCommonService;

    /**
     * ExpressManageService constructor.
     * @param MerchantCommonService $merchantCommonService
     */
    public function __construct(MerchantCommonService $merchantCommonService)
    {
        $this->merchantCommonService = $merchantCommonService;
    }

    /**
     * 获取快递公司列表
     * @param null|integer $status
     * @return array
     */
    public function getExpressCompany($status = null)
    {
        $express = $this->getExpressChannel();

        if (empty($express)) {
            return $express;
        }

        $result = DB::table('express_company')->where('type', $express['code']);
        if (!is_null($result)) {
            $result = $result->where('status', $status);
        }
        $result = $result->get();

        $list = [];
        foreach ($result as $key => $item) {
            $list[$key] = collect($item)->toArray();
        }

        return $list;
    }

    /**
     * 获取单个快递公司信息
     * @param string $code
     * @param string|null $channel
     * @return array
     */
    public function getExpressCompanyByCode($code = '', $channel = null)
    {
        if (empty($code)) {
            return [];
        }

        if (is_null($channel)) {
            $express = $this->getExpressChannel();

            if (empty($express)) {
                return $express;
            }

            $channel = $express['code'];
        }

        $result = DB::table('express_company')->where('type', $channel)->where('code', $code)->first();

        return collect($result)->toArray();
    }

    /**
     * 保存快递配送记录
     *
     * @param array $order_info 订单表信息
     * @param array $delivery_info 发货单表信息
     * @param string $express_number  快递单号
     * @param string $express_code 快递公司编码
     * @return int
     * @throws \Exception
     */
    public function saveExpressHistory($order_info = [], $delivery_info = [], $express_number = '', $express_code = '')
    {
        // 快递配送
        if (isset($order_info['shipping_code']) && $order_info['shipping_code'] == 'express') {
            $expressChannel = $this->getExpressChannel();
            $express = $this->getExpressCompanyByCode($express_code, $expressChannel['code']);

            $express_type = $expressChannel['code'] ?? ''; //第三方快递聚合服务平台
            $express_name = $express['name'] ?? ''; //快递公司名称
        } else {
            $express_type = '';
            $express_code = $order_info['shipping_code'] ?? '';
            $express_name = $order_info['shipping_name'] ?? '';
        }

        $expressHistory = [
            'shop_id' => $order_info['ru_id'], // 商家店铺ID
            'shop_name' => $this->merchantCommonService->getShopName($order_info['ru_id'], 1), //商家店铺名称
            'order_sn' => $order_info['order_sn'], //订单号
            'ship_sn' => $delivery_info['delivery_sn'], //发货订单号
            'delivery_id' => $delivery_info['delivery_id'],
            'express_type' => $express_type,
            'express_code' => $express_code, //快递公司编码
            'express_name' => $express_name, //快递公司名称
            'express_sn' => $express_number, //快递单号
            'created_at' => Carbon::now(),
        ];

        return DB::table('express_history')->insertGetId($expressHistory);
    }

    /**
     * 删除快递配送记录
     * @param $delivery_id
     * @return int
     */
    public function removeExpressHistory($delivery_id)
    {
        return DB::table('express_history')->where('delivery_id', $delivery_id)->delete();
    }

    /**
     * 获取默认的三方快递通道
     * @return array
     */
    public function getExpressChannel()
    {
        $express = DB::table('express')->where('default', 1)->first();

        return collect($express)->toArray();
    }
}
