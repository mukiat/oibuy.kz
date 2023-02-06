<?php

namespace App\Services\Sale;

use App\Models\Category;
use App\Models\Goods;
use App\Models\OrderGoods;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Category\CategoryDataHandleService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;

class SaleGeneralManageService
{
    protected $commonService;
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

    public function getDataList()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getDataList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 过滤信息 */
        $filter['keyword'] = !isset($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
        if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
            $filter['keyword'] = !empty($filter['keyword']) ? json_str_iconv($filter['keyword']) : '';
        }

        $filter['goods_sn'] = !isset($_REQUEST['goods_sn']) ? '' : trim($_REQUEST['goods_sn']);
        if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
            $filter['goods_sn'] = !empty($filter['goods_sn']) ? json_str_iconv($filter['goods_sn']) : '';
        }

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'goods_number' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter['time_type'] = isset($_REQUEST['time_type']) ? intval($_REQUEST['time_type']) : 1;
        $filter['date_start_time'] = !empty($_REQUEST['start_date']) ? trim($_REQUEST['start_date']) : '';
        $filter['date_end_time'] = !empty($_REQUEST['end_date']) ? trim($_REQUEST['end_date']) : '';

        $filter['cat_name'] = !empty($_REQUEST['cat_name']) ? trim($_REQUEST['cat_name']) : '';

        $filter['order_status'] = isset($_REQUEST['order_status']) ? $_REQUEST['order_status'] : -1;
        $filter['shipping_status'] = isset($_REQUEST['shipping_status']) ? $_REQUEST['shipping_status'] : -1;

        $cat_id = 0;
        if (!empty($filter['cat_name'])) {
            $cat_id = Category::where('cat_name', $filter['cat_name'])->value('cat_id');
            $cat_id = $cat_id ? $cat_id : 0;
        }

        if ($filter['date_start_time'] == '' && $filter['date_end_time'] == '') {
            $start_time = TimeRepository::getLocalMktime(0, 0, 0, TimeRepository::getLocalDate('m'), 1, TimeRepository::getLocalDate('Y')); //本月第一天
            $end_time = TimeRepository::getLocalMktime(0, 0, 0, TimeRepository::getLocalDate('m'), TimeRepository::getLocalDate('t'), TimeRepository::getLocalDate('Y')) + 24 * 60 * 60 - 1; //本月最后一天
        } else {
            $start_time = TimeRepository::getLocalStrtoTime($filter['date_start_time']);
            $end_time = TimeRepository::getLocalStrtoTime($filter['date_end_time']);
        }

        $og_res = OrderGoods::query();

        if (!empty($filter['goods_sn'])) {
            $og_res = $og_res->where('goods_sn', 'like', '%' . $filter['goods_sn'] . '%');
        }

        if (!empty($filter['cat_name'])) {
            $og_res = $og_res->where(function ($query) use ($cat_id) {
                $query->whereHasIn('getGoods', function ($query) use ($cat_id) {
                    $query->where('cat_id', $cat_id);
                });
            });
        }
        $og_res = $og_res->whereHasIn('getOrder', function ($query) use ($start_time, $end_time, $filter) {
            $query = $query->where('extension_code', '');

            if ($filter['time_type'] == 1) {
                $query = $query->where('add_time', '>=', $start_time)
                    ->where('add_time', '<=', $end_time);
            } else {
                $query = $query->where('shipping_time', '>=', $start_time)
                    ->where('shipping_time', '<=', $end_time);
            }
            if ($filter['order_status'] > -1) { //多选
                $order_status = BaseRepository::getExplode($filter['order_status']);
                $query = $query->whereIn('order_status', $order_status);
            }
            if ($filter['shipping_status'] > -1) { //多选
                $shipping_status = BaseRepository::getExplode($filter['shipping_status']);
                $query = $query->whereIn('shipping_status', $shipping_status);
            }

            //主订单下有子订单时，则主订单不显示
            $query->where('main_count', 0);
        });

        $goods_id = $og_res->pluck('goods_id');
        $goods_id = BaseRepository::getToArray($goods_id);
        $goods_id = array_unique($goods_id);

        /* 记录总数 */
        $filter['record_count'] = Goods::whereIn('goods_id', $goods_id)->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $og_res = Goods::select('goods_id', 'user_id as ru_id', 'goods_name', 'goods_sn', 'cat_id')
            ->whereIn('goods_id', $goods_id);

        $og_res = $og_res->orderBy($filter['sort_by'], $filter['sort_order'])
            ->offset(($filter['page'] - 1) * $filter['page_size'])
            ->limit($filter['page_size']);
        $data_list = BaseRepository::getToArrayGet($og_res);

        if ($data_list) {

            $ru_id = BaseRepository::getKeyPluck($data_list, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            $cat_id = BaseRepository::getKeyPluck($data_list, 'cat_id');
            $catList = CategoryDataHandleService::getCategoryDataList($cat_id, ['cat_id', 'cat_name']);

            $goods_id = BaseRepository::getKeyPluck($data_list, 'goods_id');
            $saleOrderGoodsList = $this->saleOrderGoodsList($goods_id, $start_time, $end_time, $filter);

            for ($i = 0; $i < count($data_list); $i++) {

                $orderGoods = $saleOrderGoodsList[$data_list[$i]['goods_id']];

                $data_list[$i]['goods_price'] = $orderGoods['goods_price'] ?? 0;
                $data_list[$i]['total_fee'] = $orderGoods['goods_price'] * $orderGoods['goods_number'];
                $data_list[$i]['goods_number'] = $orderGoods['goods_number'] ?? 0;

                $data_list[$i]['shop_name'] = $merchantList[$data_list[$i]['ru_id']]['shop_name'] ?? '';
                $data_list[$i]['cat_name'] = $catList[$data_list[$i]['cat_id']]['cat_name'] ?? '';
            }
        }

        $arr = ['data_list' => $data_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /**
     * 订单商品列表
     *
     * @param array $goods_id
     * @param int $start_time
     * @param int $end_time
     * @param array $filter
     * @return array
     */
    public function saleOrderGoodsList($goods_id = [], $start_time = 0, $end_time = 0, $filter = [])
    {
        $orderGoods = OrderGoods::selectRaw('goods_id, order_id, goods_name, ru_id, goods_sn, goods_price, goods_number')
            ->whereIn('goods_id', $goods_id)
            ->whereHasIn('getOrder', function ($query) use ($start_time, $end_time, $filter) {
                $query = $query->where('extension_code', '');

                if ($filter['time_type'] == 1) {
                    $query = $query->where('add_time', '>=', $start_time)
                        ->where('add_time', '<=', $end_time);
                } else {
                    $query = $query->where('shipping_time', '>=', $start_time)
                        ->where('shipping_time', '<=', $end_time);
                }
                if ($filter['order_status'] > -1) { //多选
                    $order_status = BaseRepository::getExplode($filter['order_status']);
                    $query = $query->whereIn('order_status', $order_status);
                }
                if ($filter['shipping_status'] > -1) { //多选
                    $shipping_status = BaseRepository::getExplode($filter['shipping_status']);
                    $query = $query->whereIn('shipping_status', $shipping_status);
                }

                //主订单下有子订单时，则主订单不显示
                $query->where('main_count', 0);
            });

        $orderGoods = BaseRepository::getToArrayGet($orderGoods);

        $arr = [];
        if ($orderGoods) {
            foreach ($orderGoods as $key => $row) {
                $arr[$row['goods_id']] = $row;
            }
        }

        return $arr;
    }

    /**
     * 取得状态列表
     *
     * @param string $type
     * @return array
     */
    public function getStatusList($type = 'all')
    {
        $list = [];

        if ($type == 'all' || $type == 'order') {
            $pre = $type == 'all' ? 'os_' : '';
            foreach ($GLOBALS['_LANG']['os'] as $key => $value) {
                $list[$pre . $key] = $value;
            }
        }

        if ($type == 'all' || $type == 'shipping') {
            $pre = $type == 'all' ? 'ss_' : '';
            foreach ($GLOBALS['_LANG']['ss'] as $key => $value) {
                $list[$pre . $key] = $value;
            }
        }

        if ($type == 'all' || $type == 'payment') {
            $pre = $type == 'all' ? 'ps_' : '';
            foreach ($GLOBALS['_LANG']['ps'] as $key => $value) {
                $list[$pre . $key] = $value;
            }
        }
        return $list;
    }
}
