<?php

namespace App\Services\Goods;

use App\Models\GoodsTransport;
use App\Models\GoodsTransportExpress;
use App\Models\GoodsTransportExtend;
use App\Models\Region;
use App\Models\Shipping;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Common\CommonManageService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;

class GoodsTransportManageService
{
    protected $merchantCommonService;
    protected $commonManageService;
    protected $dscRepository;

    public function __construct(
        MerchantCommonService $merchantCommonService,
        CommonManageService $commonManageService,
        DscRepository $dscRepository
    )
    {
        $this->merchantCommonService = $merchantCommonService;
        $this->commonManageService = $commonManageService;
        $this->dscRepository = $dscRepository;
    }

    /* 快递列表 */

    public function getTransportExpress($tid = 0)
    {
        $res = GoodsTransportExpress::where('tid', $tid);
        if ($tid == 0) {
            $admin_id = $this->commonManageService->getAdminId();
            $res = $res->where('admin_id', $admin_id);
        }
        $res = $res->orderBy('id', 'DESC');
        $transport_express = BaseRepository::getToArrayGet($res);

        foreach ($transport_express as $key => $val) {
            $transport_express[$key]['express_list'] = $this->getExpressList($val['shipping_id']);
        }
        return $transport_express;
    }

    /**
     * 获取快递
     *
     * @param string $shipping_id
     * @return string
     */
    public function getExpressList($shipping_id = '')
    {
        $express_list = '';
        if (!empty($shipping_id)) {
            $shipping_id = BaseRepository::getExplode($shipping_id);
            $res = Shipping::select('shipping_name')->whereIn('shipping_id', $shipping_id);
            $express_list = BaseRepository::getToArrayGet($res);
            $express_list = BaseRepository::getFlatten($express_list);
            $express_list = implode(',', $express_list);
        }
        return $express_list;
    }

    /**
     * 获取地区
     *
     * @param string $area_id
     * @return string
     */
    public function getAreaList($area_id = '')
    {
        $area_list = '';
        if (!empty($area_id)) {
            $area_id = BaseRepository::getExplode($area_id);
            $res = Region::select('region_name')->whereIn('region_id', $area_id);
            $area_list = BaseRepository::getToArrayGet($res);
            $area_list = BaseRepository::getFlatten($area_list);
            $area_list = implode(',', $area_list);
        }
        return $area_list;
    }

    /**
     * 地区列表
     *
     * @param int $tid
     * @return mixed
     */
    public function getTransportArea($tid = 0)
    {
        $res = GoodsTransportExtend::where('tid', $tid);
        if ($tid == 0) {
            $admin_id = $this->commonManageService->getAdminId();
            $res = $res->where('admin_id', $admin_id);
        }

        $res = $res->orderBy('id', 'DESC');
        $transport_area = BaseRepository::getToArrayGet($res);

        foreach ($transport_area as $key => $val) {
            if (!empty($val['top_area_id']) && !empty($val['area_id'])) {
                $area_map = [];
                $top_area_arr = explode(',', $val['top_area_id']);
                foreach ($top_area_arr as $k => $v) {
                    $top_area = Region::where('region_id', $v)->value('region_name');
                    $top_area = $top_area ? $top_area : '';

                    $area_id_attr = BaseRepository::getExplode($val['area_id']);
                    $res = Region::select('region_name')->where('parent_id', $v)->whereIn('region_id', $area_id_attr);
                    $area_arr = BaseRepository::getToArrayGet($res);
                    $area_arr = BaseRepository::getFlatten($area_arr);

                    $area_list = $area_arr ? implode(',', $area_arr) : '';

                    $area_map[$k]['top_area'] = $top_area;
                    $area_map[$k]['area_list'] = $area_list;
                }
                $transport_area[$key]['area_map'] = $area_map;
            }
        }
        return $transport_area;
    }

    /**
     * 模板列表
     *
     * @return array
     * @throws \Exception
     */
    public function getTransportList()
    {
        $seller = $this->commonManageService->getAdminIdSeller();

        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getTransportList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $filter['store_search'] = !isset($_REQUEST['store_search']) ? -1 : intval($_REQUEST['store_search']);
        $filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
        $filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
        $filter['store_type'] = isset($_REQUEST['store_type']) ? trim($_REQUEST['store_type']) : '';

        $res = GoodsTransport::whereRaw(1);

        if ($filter['store_search'] > -1) {
            if ($seller['ru_id'] == 0) {
                if ($filter['store_search'] > 0) {
                    if ($filter['store_search'] == 1) {
                        $res = $res->where('user_id', $filter['merchant_id']);
                    }

                    if ($filter['store_search'] > 1 && $filter['store_search'] < 4) {
                        $res = $res->where(function ($query) use ($filter) {
                            $query->whereHasIn('getMerchantsShopInformation', function ($query) use ($filter) {
                                if ($filter['store_search'] == 2) {
                                    $query->where('rz_shop_name', 'LIKE', '%' . $this->dscRepository->mysqlLikeQuote($filter['store_keyword']) . '%');
                                } elseif ($filter['store_search'] == 3) {
                                    $query = $query->where('shoprz_brand_name', 'LIKE', '%' . $this->dscRepository->mysqlLikeQuote($filter['store_keyword']) . '%');
                                    if ($filter['store_type']) {
                                        $query->where('shop_name_suffix', $filter['store_type']);
                                    }
                                }
                            });
                        });
                    } elseif ($filter['store_search'] == 4) {
                        $res = $res->where('user_id', 0);
                    }
                }
            }
        }

        /* 初始化分页参数 */
        $filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
        }
        if (!empty($filter['keyword'])) {
            $res = $res->where('title', 'LIKE', '%' . mysql_like_quote($filter['keyword']) . '%');
        }
        $filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? 1 : 0;  //商家和自营订单标识
        $filter['keyword'] = stripslashes($filter['keyword']);
        //区分商家和自营
        if (!empty($filter['seller_list'])) {
            $res = $res->where('ru_id', '>', 0);
        } else {
            $res = $res->where('ru_id', 0);
        }

        /* 查询记录总数，计算分页数 */
        $filter['record_count'] = $res->count();
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 查询记录 */
        $res = $res->orderBy('tid', 'DESC')->offset($filter['start'])->limit($filter['page_size']);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {

            $ru_id = BaseRepository::getKeyPluck($res, 'ru_id');
            $sellerList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($res as $row) {

                $shop = $sellerList[$row['ru_id']];

                $row['update_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['update_time']);
                $row['shop_name'] = $shop['shop_name'] ?? '';
                $arr[] = $row;
            }
        }

        return ['list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }
}
