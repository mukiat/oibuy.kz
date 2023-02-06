<?php

namespace App\Services\Region;

use App\Models\AdminUser;
use App\Models\Region;
use App\Models\RegionStore;
use App\Models\RsRegion;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Merchant\MerchantCommonService;

class RegionStoreManageService
{
    protected $merchantCommonService;

    public function __construct(
        MerchantCommonService $merchantCommonService
    ) {
        $this->merchantCommonService = $merchantCommonService;
    }

    /* 获取卖场列表 */
    public function regionStoreList()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'regionStoreList';
        $get_filter = app(DscRepository::class)->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 过滤查询 */
        $filter = [];

        $filter['keyword'] = !empty($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
        }

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'rs_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $res = RegionStore::whereRaw(1);
        /* 关键字 */
        if (!empty($filter['keyword'])) {
            $res = $res->where(function ($query) use ($filter) {
                $query->where('rs_name', 'LIKE', '%' . mysql_like_quote($filter['keyword']) . '%');
            });
        }

        /* 获得总记录数据 */
        $filter['record_count'] = $res->count();

        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        app(DscRepository::class)->setSessionFilter($filter, $param_str);

        /* 获得数据 */
        $arr = [];
        $res = $res->orderBy($filter['sort_by'], $filter['sort_order'])
            ->offset($filter['start'])
            ->limit($filter['page_size']);
        $res = BaseRepository::getToArrayGet($res);

        foreach ($res as $rows) {
            //地区
            $region_id = get_table_date('rs_region', "rs_id='$rows[rs_id]'", ['region_id'], 2);
            if ($region_id) {
                $rows['region_name'] = get_table_date('region', "region_id='$region_id'", ['region_name'], 2);
            }

            //管理员
            $rows['user_name'] = get_table_date('admin_user', "rs_id='$rows[rs_id]'", ['user_name'], 2);

            $arr[] = $rows;
        }

        return ['list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }

    /* 获取卖场信息 */
    public function getRegionStoreInfo($rs_id = 0)
    {
        $region_store = get_table_date('region_store', "rs_id='$rs_id'", ['*']);
        if ($region_store) {
            //区域
            $region_id = RsRegion::where('rs_id', $rs_id)->value('region_id');
            $region_id = $region_id ? $region_id : 0;

            //管理员
            $user_id = AdminUser::where('rs_id', $rs_id)->value('user_id');
            $user_id = $user_id ? $user_id : 0;

            //整合数据
            $region_store['region_id'] = $region_id;
            $region_store['user_id'] = $user_id;
        }

        return $region_store;
    }

    /* 获取管理员列表 */
    public function getRegionAdmin()
    {
        $super_admin_id = get_table_date('admin_user', "action_list='all'", ['user_id'], 2);

        $res = AdminUser::where('action_list', '<>', 'all')
            ->where('ru_id', 0)
            ->where('parent_id', $super_admin_id)
            ->orderBy('user_id', 'DESC');
        $region_admin = BaseRepository::getToArrayGet($res);

        return $region_admin;
    }

    /**
     * 接收多个地址的ID 返回地址的数据信息
     * @param array $all_region
     * @return array
     */
    public function getRegionForArray($all_region = [])
    {
        if (empty($all_region)) {
            return [];
        }
        $all_region_id = ArrRepository::flatten($all_region);
        $res = Region::whereIn('region_id', $all_region_id);
        $res = BaseRepository::getToArrayGet($res);
        if (empty($res)) {
            return [];
        }
        $result = [];
        foreach ($res as $key => $val) {
            $region_key = array_search($val['region_id'], $all_region);
            if ($region_key) {
                $result[$region_key] = $val;
            }
        }
        return $result;
    }
}
