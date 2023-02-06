<?php

namespace App\Services\Sale;

use App\Models\SaleNotice;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\User\UserDataHandleService;

class SaleNoticeManageService
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

    /**
     * 获取列表
     *
     * @param int $ru_id
     * @return array
     * @throws \Exception
     */
    public function saleNoticeList($ru_id = 0)
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'saleNoticeList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 查询条件 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? 0 : trim($_REQUEST['keywords']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['send_status'] = empty($_REQUEST['send_status']) ? '' : intval($_REQUEST['send_status']);
        $filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? 1 : 0;  //商家和自营订单标识

        $res = SaleNotice::whereRaw(1);
        if (!empty($filter['keywords'])) {
            $res = $res->where('email', 'LIKE', '%' . mysql_like_quote($filter['keywords']) . '%');
        }

        if (!empty($filter['send_status'])) {
            $res = $res->where('status', $filter['send_status']);
        }

        $res = $res->whereHasIn('getGoods', function ($query) use ($ru_id, $filter) {
            if ($ru_id > 0) {
                $query = $query->where('user_id', $ru_id);
            }

            //区分商家和自营
            if (!empty($filter['seller_list'])) {
                CommonRepository::constantMaxId($query, 'user_id');
            } else {
                $query->where('user_id', 0);
            }
        });
        //修复记录与数量不一致
        $filter['record_count'] = $res->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $res = $res->orderBy($filter['sort_by'], $filter['sort_order'])
            ->offset($filter['start'])
            ->limit($filter['page_size']);
        $res = BaseRepository::getToArrayGet($res);

        $statusArr = [1 => $GLOBALS['_LANG']['has_been_sent'], 2 => $GLOBALS['_LANG']['unsent'], 3 => $GLOBALS['_LANG']['system_send_fail']];
        $send_typeArr = [1 => $GLOBALS['_LANG']['mail'], 2 => $GLOBALS['_LANG']['short_message']];

        $arr = [];
        if (!empty($res)) {

            $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'user_id', 'goods_name', 'shop_price']);

            $user_id = BaseRepository::getKeyPluck($res, 'user_id');
            $userList = UserDataHandleService::userDataList($user_id, ['user_id', 'user_name']);

            $ru_id = BaseRepository::getKeyPluck($goodsList, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($res as $row) {
                $row['user_name'] = $userList[$row['user_id']]['user_name'] ?? '';

                if (config('shop.show_mobile') == 0) {
                    $row['user_name'] = $this->dscRepository->stringToStar($row['user_name']);
                    $row['cellphone'] = $this->dscRepository->stringToStar($row['cellphone']);
                    $row['email'] = $this->dscRepository->stringToStar($row['email']);
                }

                $goods = $goodsList[$row['goods_id']] ?? [];

                $row['goods_name'] = $goods['goods_name'] ?? '';
                $row['shop_price'] = $goods['shop_price'] ?? 0;
                $row['ru_id'] = $goods['user_id'] ?? 0;

                $row['status'] = $statusArr[$row['status']];
                $row['send_type'] = isset($send_typeArr[$row['send_type']]) ? $send_typeArr[$row['send_type']] : '';
                $row['add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['add_time']);
                $row['shop_name'] = $merchantList[$row['ru_id']]['shop_name'] ?? '';

                $arr[] = $row;
            }
        }

        $filter['keywords'] = stripslashes($filter['keywords']);

        return ['item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }
}
