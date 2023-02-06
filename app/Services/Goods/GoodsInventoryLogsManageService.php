<?php

namespace App\Services\Goods;

use App\Models\GoodsInventoryLogs;
use App\Models\RegionWarehouse;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Brand\BrandDataHandleService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Order\OrderDataHandleService;
use App\Services\Region\RegionDataHandleService;

class GoodsInventoryLogsManageService
{
    protected $merchantCommonService;
    protected $goodsAttrService;
    protected $dscRepository;

    public function __construct(
        MerchantCommonService $merchantCommonService,
        DscRepository $dscRepository,
        GoodsAttrService $goodsAttrService
    )
    {
        $this->merchantCommonService = $merchantCommonService;
        $this->goodsAttrService = $goodsAttrService;
        $this->dscRepository = $dscRepository;
    }

    public function getInventoryRegion($region_id)
    {
        $region_name = RegionWarehouse::where('region_id', $region_id)->value('region_name');
        $region_name = $region_name ?? '';
        return $region_name;
    }

    /**
     * 获取管理员操作记录
     *
     * @param $ru_id
     * @return array
     * @throws \Exception
     */
    public function getGoodsInventoryLogs($ru_id)
    {
        load_helper('order');

        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getGoodsInventoryLogs';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
        $filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
            $filter['order_sn'] = json_str_iconv($filter['order_sn']);
        }

        $filter['goods_id'] = empty($_REQUEST['goods_id']) ? 0 : intval($_REQUEST['goods_id']);
        $filter['start_time'] = empty($_REQUEST['start_time']) ? '' : trim($_REQUEST['start_time']);
        $filter['end_time'] = empty($_REQUEST['end_time']) ? '' : trim($_REQUEST['end_time']);
        $filter['warehouse_id'] = !isset($_REQUEST['warehouse_id']) ? 0 : intval($_REQUEST['warehouse_id']);
        $filter['area_id'] = !isset($_REQUEST['end_time']) ? 0 : intval($_REQUEST['area_id']);

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter['step'] = empty($_REQUEST['step']) ? '' : trim($_REQUEST['step']);
        $filter['operation_type'] = !isset($_REQUEST['operation_type']) ? -1 : intval($_REQUEST['operation_type']);

        $filter['store_search'] = empty($_REQUEST['store_search']) ? 0 : intval($_REQUEST['store_search']);
        $filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
        $filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
        $filter['store_type'] = isset($_REQUEST['store_type']) ? trim($_REQUEST['store_type']) : '';

        $filter['ru_id'] = $ru_id;
        $row = GoodsInventoryLogs::whereHasIn('getGoods', function ($query) use ($filter) {
            if ($filter['ru_id'] > 0) {
                $query = $query->where('user_id', $filter['ru_id']);
            }

            /* 关键字 */
            if (!empty($filter['keyword'])) {
                $query->where('goods_name', 'like', '%' . $this->dscRepository->mysqlLikeQuote($filter['ru_id']) . '%');
            }

            if ($filter['store_search'] > -1) {
                if ($filter['ru_id'] == 0) {
                    if ($filter['store_search'] > 0) {
                        if ($filter['store_search'] == 1) {
                            $query = $query->where('user_id', $filter['merchant_id']);
                        }

                        if ($filter['store_search'] > 1) {
                            $query->where(function ($query) use ($filter) {
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
                        }
                    }
                }
            }
        });

        if ($filter['goods_id'] > 0) {
            $row = $row->where('goods_id', $filter['goods_id']);
        }

        if (!empty($filter['order_sn'])) {
            $row = $row->whereHasIn('getOrderInfo', function ($query) use ($filter) {
                /* 订单号 */
                if (!empty($filter['order_sn'])) {
                    $query->where('order_sn', $filter['order_sn']);
                }
            });
        }

        /* 操作时间 */
        if (!empty($filter['start_time']) || !empty($filter['end_time'])) {
            $start_time = TimeRepository::getLocalStrtoTime($filter['start_time']);
            $end_time = TimeRepository::getLocalStrtoTime($filter['end_time']);
            $row = $row->where('add_time', '>', $start_time)
                ->where('add_time', '<', $end_time);
        }

        /*仓库*/
        if ($filter['warehouse_id'] && empty($filter['area_id'])) {
            $row = $row->where(function ($query) {
                $query->where('model_inventory', 1)->orWhere('model_attr', 1);
            });

            $row = $row->where('warehouse_id', $filter['warehouse_id']);
        }


        if ($filter['area_id'] && $filter['warehouse_id']) {
            $row = $row->where(function ($query) {
                $query->where('model_inventory', 2)->orWhere('model_attr', 2);
            });

            $row = $row->where('area_id', $filter['area_id']);
        }

        if ($filter['operation_type'] == -1) {
            //出库
            if ($filter['step'] == 'out') {
                $row = $row->whereIn('use_storage', [0, 1, 4, 8, 10, 15]);
            }

            //入库
            if ($filter['step'] == 'put') {
                $row = $row->whereIn('use_storage', [2, 3, 5, 6, 7, 9, 11, 13]);
            }
        } else {
            $row = $row->where('use_storage', $filter['operation_type']);
        }

        $res = $record_count = $row;

        $filter['record_count'] = $record_count->count();

        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $res = $res->with([
            'getAdminUser' => function ($query) {
                $query->select('user_id', 'user_name as admin_name');
            },
            'getProductsWarehouse' => function ($query) {
                $query->select('product_id', 'goods_attr');
            },
            'getProductsArea' => function ($query) {
                $query->select('product_id', 'goods_attr');
            },
            'getProducts' => function ($query) {
                $query->select('product_id', 'goods_attr');
            }
        ]);

        $res = $res->orderBy($filter['sort_by'], $filter['sort_order']);

        if ($filter['start'] > 0) {
            $res = $res->skip($filter['start']);
        }

        if ($filter['page_size'] > 0) {
            $res = $res->take($filter['page_size']);
        }

        $res = BaseRepository::getToArrayGet($res);

        $list = [];
        if ($res) {

            $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'user_id', 'goods_thumb', 'brand_id', 'goods_name']);

            $order_id = BaseRepository::getKeyPluck($res, 'order_id');
            $orderList = OrderDataHandleService::orderDataList($order_id, ['order_id', 'order_sn']);

            $brand_id = BaseRepository::getKeyPluck($res, 'brand_id');
            $brandList = BrandDataHandleService::goodsBrand($brand_id, ['brand_id', 'brand_name']);

            $ru_id = BaseRepository::getKeyPluck($res, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            $warehouseId = BaseRepository::getKeyPluck($res, 'warehouse_id');
            $warehouseList = RegionDataHandleService::regionWarehouseDataList($warehouseId, ['region_id', 'region_name']);

            $areaId = BaseRepository::getKeyPluck($res, 'area_id');
            $areaList = RegionDataHandleService::regionWarehouseDataList($areaId, ['region_id', 'region_name']);

            foreach ($res as $rows) {

                $goods = $goodsList[$rows['goods_id']] ?? [];
                $rows = BaseRepository::getArrayMerge($rows, $goods);

                $order = $orderList[$rows['order_id']] ?? [];
                $rows = BaseRepository::getArrayMerge($rows, $order);

                $brand = $brandList[$rows['brand_id']] ?? [];
                $rows = BaseRepository::getArrayMerge($rows, $brand);

                $rows['admin_name'] = $rows['get_admin_user']['admin_name'] ?? '';
                $rows = BaseRepository::getArrayExcept($rows, ['get_admin_user']);

                $rows['add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $rows['add_time']);
                $rows['shop_name'] = $merchantList[$rows['user_id']]['shop_name'] ?? '';
                $rows['warehouse_name'] = $warehouseList[$rows['warehouse_id']]['region_name'] ?? '';
                $rows['area_name'] = $areaList[$rows['area_id']]['region_name'] ?? '';

                if (empty($rows['admin_name'])) {
                    $rows['admin_name'] = $GLOBALS['_LANG']['reception_user_place_order'];
                }

                if ($rows['product_id']) {
                    if ($rows['model_attr'] == 1) {
                        $goods_attr = $rows['get_products_warehouse']['goods_attr'] ?? '';
                    } elseif ($rows['model_attr'] == 2) {
                        $goods_attr = $rows['get_products_area']['goods_attr'] ?? '';
                    } else {
                        $goods_attr = $rows['get_products']['goods_attr'] ?? '';
                    }

                    $goods_attr = BaseRepository::getExplode($goods_attr, '|');
                    $rows['goods_attr'] = $this->goodsAttrService->getGoodsAttrInfo($goods_attr, 'pice', $rows['warehouse_id'], $rows['area_id']);
                }

                //图片显示
                $rows['goods_thumb'] = $this->dscRepository->getImagePath($rows['goods_thumb']);

                $list[] = $rows;
            }
        }

        return ['list' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }
}
