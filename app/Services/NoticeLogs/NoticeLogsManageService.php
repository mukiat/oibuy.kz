<?php

namespace App\Services\NoticeLogs;

use App\Models\NoticeLog;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Commission\CommissionService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Order\OrderService;

class NoticeLogsManageService
{
    protected $orderService;
    protected $commissionService;
    protected $dscRepository;
    protected $merchantCommonService;

    public function __construct(
        OrderService $orderService,
        CommissionService $commissionService,
        DscRepository $dscRepository,
        MerchantCommonService $merchantCommonService
    )
    {
        $this->orderService = $orderService;
        $this->commissionService = $commissionService;
        $this->dscRepository = $dscRepository;
        $this->merchantCommonService = $merchantCommonService;
    }


    /* 获取管理员操作记录 */
    public function getNoticeLogs($ru_id)
    {
        $filter = [];
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? 1 : 0;  //商家和自营订单标识

        //查询条件
        $res = NoticeLog::whereRaw(1);

        //区分商家和自营
        $seller_list = $filter['seller_list'];
        $res = $res->whereHasIn('getGoods', function ($query) use ($ru_id, $seller_list) {
            if ($ru_id > 0) {
                $query = $query->where('user_id', $ru_id);
            }
            if (!empty($seller_list)) {
                CommonRepository::constantMaxId($query, 'user_id');
            } else {
                $query->where('user_id', 0);
            }
        });
        /* 获得总记录数据 */
        $filter['record_count'] = $res->count();

        $filter = page_and_size($filter);

        $res = $res->orderBy($filter['sort_by'], $filter['sort_order'])
            ->offset($filter['start'])
            ->limit($filter['page_size']);
        $res = BaseRepository::getToArrayGet($res);

        /* 获取管理员日志记录 */
        $list = [];
        if ($res) {

            $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'user_id', 'goods_name']);

            $ru_id = BaseRepository::getKeyPluck($goodsList, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($res as $rows) {

                $goods = $goodsList[$rows['goods_id']] ?? [];

                $rows['user_id'] = $goods['user_id'] ?? 0;
                $rows['goods_name'] = $goods['goods_name'] ?? '';

                $rows['send_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $rows['send_time']);
                $rows['shop_name'] = $merchantList[$rows['user_id']]['shop_name'] ?? '';

                $list[] = $rows;
            }
        }

        return ['list' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }
}
