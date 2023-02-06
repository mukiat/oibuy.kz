<?php

namespace App\Services\Merchant;

use App\Models\SellerShopinfo;
use App\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;

class MerchantAccountManageService
{
    protected $merchantCommonService;

    public function __construct(
        MerchantCommonService $merchantCommonService
    )
    {
        $this->merchantCommonService = $merchantCommonService;
    }

    /**
     * 商家资金列表
     */
    public function getMerchantsSellerAccount()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getMerchantsSellerAccount';
        $get_filter = app(DscRepository::class)->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $adminru = get_admin_ru_id();

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'ru_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);

        $res = SellerShopinfo::whereRaw(1);
        if ($filter['keywords']) {
            $keywords = $filter['keywords'];
            $u_res = Users::select('user_id')
                ->where(function ($query) use ($keywords) {
                    $query->where('user_name', 'LIKE', '%' . mysql_like_quote($keywords) . '%')
                        ->orWhere('nick_name', 'LIKE', '%' . mysql_like_quote($keywords) . '%');
                });
            $user_id = BaseRepository::getToArrayGet($u_res);
            $user_id = BaseRepository::getFlatten($user_id);
            if ($user_id) {
                $res = $res->whereIn('ru_id', $user_id);
            }
        }
        //管理员查询的权限 -- 店铺查询 start
        $filter['store_search'] = empty($_REQUEST['store_search']) ? 0 : intval($_REQUEST['store_search']);
        $filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
        $filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';


        $ru_id = $adminru['ru_id'];
        $store_type = isset($_REQUEST['store_type']) && !empty($_REQUEST['store_type']) ? intval($_REQUEST['store_type']) : 0;

        $res = $res->whereHasIn('getMerchantsShopInformation', function ($query) use ($filter, $store_type, $ru_id) {
            $query = $query->where('merchants_audit', 1);

            if ($filter['store_search'] != 0) {
                if ($ru_id == 0) {
                    if ($filter['store_search'] == 1) {
                        $query = $query->where('user_id', $filter['merchant_id']);
                    } elseif ($filter['store_search'] == 2) {
                        $query = $query->where('rz_shop_name', 'LIKE', '%' . mysql_like_quote($filter['store_keyword']) . '%');
                    } elseif ($filter['store_search'] == 3) {
                        $query = $query->where('shoprz_brand_name', 'LIKE', '%' . mysql_like_quote($filter['store_keyword']) . '%');
                        if ($store_type) {
                            $query = $query->where('shop_name_suffix', $store_type);
                        }
                    }

                    if ($filter['store_search'] > 1) {
                        CommonRepository::constantMaxId($query, 'user_id');
                    }
                }
            }
        });

        //管理员查询的权限 -- 店铺查询 end
        $filter['record_count'] = $res->count();
        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        app(DscRepository::class)->setSessionFilter($filter, $param_str);

        $res = $res->orderBy($filter['sort_by'], $filter['sort_order'])
            ->offset($filter['start'])
            ->limit($filter['page_size']);
        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $ru_id = BaseRepository::getKeyPluck($res, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            for ($i = 0; $i < count($res); $i++) {
                $res[$i]['shop_name'] = $merchantList[$res[$i]['ru_id']]['shop_name'] ?? '';
            }
        }

        $arr = ['log_list' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }
}
