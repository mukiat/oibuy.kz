<?php

namespace App\Services\OpenApi;

use App\Models\MerchantsShopInformation;
use App\Models\OpenApi;
use App\Models\SellerShopinfo;
use App\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Commission\CommissionService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Order\OrderService;

class OpenApiManageService
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


    /**
     * 对外开发接口列表数据
     *
     * @return array
     * @throws \Exception
     */
    public function openApiList()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'openApiList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 过滤条件 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        $filter['type'] = empty($_REQUEST['type']) ? 0 : intval($_REQUEST['type']);
        $filter['user_id'] = empty($_REQUEST['user_id']) ? 0 : intval($_REQUEST['user_id']);

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        if ($filter['type'] == 1) {
            $record_count = OpenApi::query();
            $record_count = CommonRepository::constantMaxId($record_count, 'ru_id');
        } elseif ($filter['type'] == 2) {
            $record_count = OpenApi::query();
            $record_count = CommonRepository::constantMaxId($record_count, 'user_id');
        } else {
            $record_count = OpenApi::where('ru_id', 0)->where('user_id', 0);
        }

        $filter['record_count'] = $record_count->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $res = OpenApi::select('*');

        if ($filter['type'] == 1) {
            $res = $res->where('ru_id', '>', 0);
        } elseif ($filter['type'] == 2) {
            $res = $res->where('user_id', '>', $filter['user_id']);
        } else {
            $res = $res->where('ru_id', 0)
                ->where('user_id', 0);
        }

        $res = $res->orderBy($filter['sort_by'], $filter['sort_order'])
            ->offset($filter['start'])
            ->limit($filter['page_size']);
        $open_api_list = BaseRepository::getToArrayGet($res);

        $count = count($open_api_list);

        $seller_id = BaseRepository::getKeyPluck($open_api_list, 'ru_id');
        $sellerList = MerchantDataHandleService::getMerchantInfoDataList($seller_id);

        for ($i = 0; $i < $count; $i++) {

            if ($open_api_list[$i]['ru_id'] > 0) {
                $open_api_list[$i]['name'] = $sellerList[$open_api_list[$i]['ru_id'] ?? 0]['shop_name'] ?? $open_api_list[$i]['name'];
            }

            $open_api_list[$i]['add_time'] = TimeRepository::getLocalDate("Y-m-d H:i:s", $open_api_list[$i]['add_time']);
        }

        $arr = ['open_api_list' => $open_api_list, 'filter' => $filter,
            'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /**
     * 接口列表
     *
     * @param array $api_data
     * @param string $action_code
     * @return array
     */
    public function getApiData($api_data = [], $action_code = '')
    {
        for ($i = 0; $i < count($api_data); $i++) {
            for ($j = 0; $j < count($api_data[$i]['list']); $j++) {
                $api_data[$i]['list'][$j]['is_check'] = 0;

                if ($action_code) {
                    if (in_array($api_data[$i]['list'][$j]['val'], $action_code)) {
                        $api_data[$i]['list'][$j]['is_check'] = 1;
                    }
                }
            }
        }

        return $api_data;
    }

    /**
     * 店铺列表
     *
     * @return mixed
     * @throws \Exception
     */
    public function getSellerList()
    {
        $userList = [];
        /* 过滤条件 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);

            $userList = MerchantsShopInformation::select('user_id')
                ->where('rz_shop_name', 'like', '%' . $filter['keywords'] . '%')
                ->where('shoprz_brand_name', 'like', '%' . $filter['keywords'] . '%')
                ->pluck('user_id');
            $userList = BaseRepository::getToArray($userList);
        }

        $res = SellerShopinfo::select('ru_id')
            ->where('ru_id', '>', 0)
            ->where('shop_close', 1)
            ->where('review_status', 3);

        if (empty($filter['keywords'])) {
            $res = $res->take(20);
        } else {
            $res = $res->where(function ($query) use ($userList, $filter) {

                if ($userList) {
                    $query = $query->whereIn('ru_id', $userList);
                }

                $query->orWhere('shop_name', 'like', '%' . $filter['keywords'] . '%');
            });
        }

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            $seller_id = BaseRepository::getKeyPluck($res, 'ru_id');
            $sellerList = MerchantDataHandleService::getMerchantInfoDataList($seller_id);
            foreach ($res as $key => $row) {
                $res[$key]['shop_name'] = $sellerList[$row['ru_id']]['shop_name'] ?? '';
            }
        }

        return $res;
    }

    /**
     * 会员列表
     *
     * @return mixed
     * @throws \Exception
     */
    public function getUserList()
    {
        /* 过滤条件 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        $res = Users::select('user_id', 'user_name');

        if (empty($filter['keywords'])) {
            $res = $res->take(20);
        } else {
            $res = $res->where('user_name', 'like', '%' . $filter['keywords'] . '%')
                ->orWhere('mobile_phone', 'like', '%' . $filter['keywords'] . '%');
        }

        $res = BaseRepository::getToArrayGet($res);

        return $res;
    }
}
