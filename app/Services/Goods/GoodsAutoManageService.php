<?php

namespace App\Services\Goods;

use App\Models\Goods;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;

class GoodsAutoManageService
{
    protected $merchantCommonService;

    public function __construct(
        MerchantCommonService $merchantCommonService
    )
    {
        $this->merchantCommonService = $merchantCommonService;
    }

    public function getAutoGoods($ru_id)
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getAutoGoods';
        $get_filter = app(DscRepository::class)->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $res = Goods::where('is_delete', '<>', 1);

        if ($ru_id > 0) {
            $res = $res->where('user_id', $ru_id);
        }

        $goods_name = e(request()->input('goods_name', ''));
        if (!empty($goods_name)) {
            $goods_name = trim($goods_name);
            $res = $res->where('goods_name', 'LIKE', '%' . $goods_name . '%');
            $filter['goods_name'] = $goods_name;
        }

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'last_update' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter['record_count'] = $res->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        app(DscRepository::class)->setSessionFilter($filter, $param_str);

        /* 查询 */
        $res = $res->with(['getAutoManage' => function ($query) {
            $query->select('item_id', 'starttime', 'endtime')->where('type', 'goods');
        }]);
        $res = $res->orderBy('goods_id')->orderBy($filter['sort_by'], $filter['sort_order']);
        $res = $res->offset($filter['start'])->limit($filter['page_size']);
        $query = BaseRepository::getToArrayGet($res);

        $goodsdb = [];

        if ($query) {

            $ru_id = BaseRepository::getKeyPluck($query, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($query as $rt) {
                $rt['starttime'] = '';
                $rt['endtime'] = '';
                if (isset($rt['get_auto_manage']) && !empty($rt['get_auto_manage'])) {
                    $rt['starttime'] = $rt['get_auto_manage']['starttime'];
                    $rt['endtime'] = $rt['get_auto_manage']['endtime'];
                }

                if (!empty($rt['starttime'])) {
                    $rt['starttime'] = TimeRepository::getLocalDate('Y-m-d', $rt['starttime']);
                }

                if (!empty($rt['endtime'])) {
                    $rt['endtime'] = TimeRepository::getLocalDate('Y-m-d', $rt['endtime']);
                }

                $rt['user_name'] = $merchantList[$rt['user_id']]['shop_name'] ?? '';

                $goodsdb[] = $rt;
            }
        }

        $arr = ['goodsdb' => $goodsdb, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }
}
