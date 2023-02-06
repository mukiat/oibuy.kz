<?php

namespace App\Services\Sale;

use App\Models\OrderGoods;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Order\OrderDataHandleService;

class SaleListManageService
{
    protected $merchantCommonService;
    protected $dscRepository;

    public function __construct(
        MerchantCommonService $merchantCommonService,
        DscRepository $dscRepository
    )
    {
        $this->merchantCommonService = $merchantCommonService;
        $this->dscRepository = $dscRepository;
    }

    /* ------------------------------------------------------ */
    //--获取销售明细需要的函数
    /* ------------------------------------------------------ */

    /**
     * 取得销售明细数据信息
     *
     * @param bool $is_pagination
     * @return array
     * @throws \Exception
     */
    public function getSaleList($is_pagination = true)
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getSaleList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 时间参数 */
        $filter['start_date'] = empty($_REQUEST['start_date']) ? TimeRepository::getLocalStrtoTime('-7 days') : TimeRepository::getLocalStrtoTime($_REQUEST['start_date']);
        $filter['end_date'] = empty($_REQUEST['end_date']) ? TimeRepository::getLocalStrtoTime('today') : TimeRepository::getLocalStrtoTime($_REQUEST['end_date']);
        $filter['goods_sn'] = empty($_REQUEST['goods_sn']) ? '' : trim($_REQUEST['goods_sn']);
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'goods_number' : trim($_REQUEST['sort_by']);

        $filter['order_status'] = isset($_REQUEST['order_status']) && !($_REQUEST['order_status'] == '') ? explode(',', $_REQUEST['order_status']) : '';
        $filter['shipping_status'] = isset($_REQUEST['shipping_status']) && !($_REQUEST['shipping_status'] == '') ? explode(',', $_REQUEST['shipping_status']) : '';
        $filter['time_type'] = !empty($_REQUEST['time_type']) ? intval($_REQUEST['time_type']) : 0;
        $filter['order_referer'] = empty($_REQUEST['order_referer']) ? '' : trim($_REQUEST['order_referer']);

        /* 查询数据的条件 */
        $res = OrderGoods::whereRaw(1);

        $adminru = get_admin_ru_id();

        if ($adminru['ru_id'] > 0) {
            $res = $res->where('ru_id', $adminru['ru_id']);
        }

        if ($filter['goods_sn']) {
            $res = $res->where('goods_sn', $filter['goods_sn']);
        }

        $res = $res->whereHasIn('getOrder', function ($query) use ($filter) {
            if ($filter['time_type'] == 1) {
                $query = $query->where('add_time', '>=', $filter['start_date'])
                    ->where('add_time', '<=', $filter['end_date']);
            } else {
                $query = $query->where('shipping_time', '>=', $filter['start_date'])
                    ->where('shipping_time', '<=', $filter['end_date']);
            }
            if (!empty($filter['order_status'])) {
                $order_status = BaseRepository::getExplode($filter['order_status']);
                $query = $query->whereIn('order_status', $order_status);
            }
            if (!empty($filter['shipping_status'])) {
                $shipping_status = BaseRepository::getExplode($filter['shipping_status']);
                //待发货
                if (in_array(SS_TO_BE_SHIPPED, $shipping_status)) {
                    unset($shipping_status[SS_TO_BE_SHIPPED]);
                    $status = [
                        defined(SS_UNSHIPPED) ? SS_UNSHIPPED : 0,
                        defined(SS_PREPARING) ? SS_PREPARING : 3,
                        defined(SS_SHIPPED_PART) ? SS_SHIPPED_PART : 4,
                        defined(SS_SHIPPED_ING) ? SS_SHIPPED_ING : 5
                    ];
                    $shipping_status = array_merge($status, $shipping_status);
                }
                $query = $query->whereIn('shipping_status', array_unique($shipping_status));
            }
            //主订单下有子订单时，则主订单不显示
            $query = $query->where('main_count', 0);

            if ($filter['order_referer']) {
                if ($filter['order_referer'] == 'pc') {
                    $query->whereNotIn('referer', ['mobile', 'touch', 'H5', 'app', 'wxapp', 'ecjia-cashdesk']);
                } else {
                    $query->where('referer', $filter['order_referer']);
                }
            }
        });

        $filter['record_count'] = $res->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $res = $res->selectRaw('goods_id,order_id,goods_sn,goods_name,goods_number AS goods_num,ru_id,goods_price as sales_price');
        $res = $res->orderBy($filter['sort_by'], 'DESC');

        if ($is_pagination) {
            $res = $res->offset($filter['start'])->limit($filter['page_size']);
        }

        $sale_list_data = BaseRepository::getToArrayGet($res);

        if (!empty($sale_list_data)) {

            $ru_id = BaseRepository::getKeyPluck($sale_list_data, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            $order_id = BaseRepository::getKeyPluck($sale_list_data, 'order_id');
            $orderList = OrderDataHandleService::orderDataList($order_id, ['order_id', 'add_time AS sales_time', 'order_sn', 'order_status', 'shipping_status']);

            foreach ($sale_list_data as $key => $item) {

                $order = $orderList[$item['order_id']] ?? [];

                $item['sales_time'] = $order['sales_time'] ?? '';
                $item['order_sn'] = $order['order_sn'] ?? '';
                $item['order_status'] = $order['order_status'] ?? '';

                $item['shipping_status'] = $order['shipping_status'] ?? 0;
                $item['shipping_status'] = $item['shipping_status'] ? intval($item['shipping_status']) : 0;

                $item['total_fee'] = $item['goods_num'] * $item['sales_price'];

                $item['total_fee'] = $this->dscRepository->getPriceFormat($item['total_fee'], true, false);
                $item['sales_price'] = $this->dscRepository->getPriceFormat($item['sales_price'], true, false);

                $item['shop_name'] = $merchantList[$item['ru_id']]['shop_name'] ?? '';
                $item['sales_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $item['sales_time']);

                $item['order_status_format'] = trim(strip_tags($GLOBALS['_LANG']['os'][$item['order_status']]));
                $item['shipping_status_format'] = trim(strip_tags($GLOBALS['_LANG']['ss'][$item['shipping_status']]));

                $sale_list_data[$key] = $item;
            }
        }
        $arr = ['sale_list_data' => $sale_list_data, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
        return $arr;
    }
}
