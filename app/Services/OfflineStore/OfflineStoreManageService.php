<?php

namespace App\Services\OfflineStore;

use App\Libraries\Image;
use App\Models\OfflineStore;
use App\Models\OrderGoods;
use App\Models\Region;
use App\Models\StoreUser;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Commission\CommissionService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Order\OrderService;
use App\Services\Region\RegionDataHandleService;

class OfflineStoreManageService
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

    /* 上传文件 */
    public function uploadArticleFile($upload)
    {
        $file_dir = storage_public(DATA_DIR . "/offline_store");
        if (!file_exists($file_dir)) {
            if (!make_dir($file_dir)) {
                /* 创建目录失败 */
                return false;
            }
        }

        $image = new Image();
        $filename = $image->random_filename() . substr($upload['name'], strpos($upload['name'], '.'));
        $path = storage_public(DATA_DIR . "/offline_store/" . $filename);

        if (move_upload_file($upload['tmp_name'], $path)) {
            return DATA_DIR . "/offline_store/" . $filename;
        } else {
            return false;
        }
    }

    /*获取门店列表*/
    public function getOfflineStoreList()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getOfflineStoreList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $adminru = get_admin_ru_id();

        /* 筛选信息 */
        $filter['type'] = isset($_REQUEST['type']) && !empty($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
        $filter['stores_user'] = empty($_REQUEST['stores_user']) ? '' : trim($_REQUEST['stores_user']);
        $filter['stores_name'] = empty($_REQUEST['stores_name']) ? '' : trim($_REQUEST['stores_name']);
        $filter['is_confirm'] = isset($_REQUEST['is_confirm']) ? intval($_REQUEST['is_confirm']) : -1;

        /* 拼装筛选 */
        $res = OfflineStore::whereRaw(1);
        if (isset($filter['stores_user']) && $filter['stores_user']) {
            $store_id = StoreUser::where('stores_user', 'LIKE', '%' . mysql_like_quote($filter['stores_user']) . '%')
                ->where('parent_id', 0)
                ->value('store_id');
            $store_id = $store_id ? $store_id : 0;

            $res = $res->where('id', $store_id);
        }
        if (isset($filter['stores_name']) && $filter['stores_name']) {
            $res = $res->where('stores_name', 'LIKE', '%' . mysql_like_quote($filter['stores_name']) . '%');
        }
        if (isset($filter['is_confirm']) && $filter['is_confirm'] != -1) {
            $res = $res->where('is_confirm', $filter['is_confirm']);
        }

        if (isset($filter['type']) && $filter['type'] == 1) {
            //管理员查询的权限 -- 店铺查询 start
            $filter['store_search'] = empty($_REQUEST['store_search']) ? 0 : intval($_REQUEST['store_search']);
            $filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
            $filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';

            if ($filter['store_search'] != 0) {
                if ($adminru['ru_id'] == 0) {
                    $store_type = isset($_REQUEST['store_type']) && !empty($_REQUEST['store_type']) ? intval($_REQUEST['store_type']) : 0;

                    if ($filter['store_search'] == 1) {
                        $res = $res->where('ru_id', $filter['merchant_id']);
                    }

                    if ($filter['store_search'] > 1 && $filter['store_search'] != 4) {
                        $res = $res->where(function ($query) use ($filter, $store_type) {
                            $query->whereHasIn('getMerchantsShopInformation', function ($query) use ($filter, $store_type) {
                                if ($filter['store_search'] == 2) {
                                    $query->where('rz_shop_name', 'LIKE', '%' . mysql_like_quote($filter['store_keyword']) . '%');
                                } elseif ($filter['store_search'] == 3) {
                                    $query = $query->where('shoprz_brand_name', 'LIKE', '%' . mysql_like_quote($filter['store_keyword']) . '%');
                                    if ($store_type) {
                                        $query->where('shop_name_suffix', $store_type);
                                    }
                                }
                            });
                        });
                    } else {
                        if ($filter['store_search'] == 4) {
                            $res = $res->where('ru_id', 0);
                        }
                    }
                }
            } else {
                $res = $res->where('ru_id', '>', 0);
            }
            //管理员查询的权限 -- 店铺查询 end
        } else {
            $res = $res->where('ru_id', 0);
        }

        $filter['record_count'] = $res->count();
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 获活动数据 */
        $res = $res->orderBy('id')
            ->offset($filter['start'])
            ->limit($filter['page_size']);
        $row = BaseRepository::getToArrayGet($res);

        if ($row) {

            $ru_id = BaseRepository::getKeyPluck($row, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            $country = BaseRepository::getKeyPluck($row, 'country');
            $province = BaseRepository::getKeyPluck($row, 'province');
            $city = BaseRepository::getKeyPluck($row, 'city');
            $district = BaseRepository::getKeyPluck($row, 'district');

            $regionIdList = BaseRepository::getArrayMerge($country, $province);
            $regionIdList = BaseRepository::getArrayMerge($regionIdList, $city);
            $regionIdList = BaseRepository::getArrayMerge($regionIdList, $district);
            $regionIdList = BaseRepository::getArrayUnique($regionIdList);

            $regionList = RegionDataHandleService::getRegionDataList($regionIdList, ['region_id', 'region_name']);

            $store_id = BaseRepository::getKeyPluck($row, 'id');
            $storeUserList = OfflineStoreDataHandleService::getStoreUserDataList($store_id, ['id', 'store_id', 'parent_id', 'stores_user']);

            foreach ($row as $k => $v) {
                $v['country'] = $regionList[$v['country']]['region_name'] ?? '';
                $v['province'] = $regionList[$v['province']]['region_name'] ?? '';
                $v['city'] = $regionList[$v['city']]['region_name'] ?? '';
                $v['district'] = $regionList[$v['city']]['district'] ?? '';

                $v['shop_name'] = $merchantList[$v['ru_id']]['shop_name'] ?? '';

                $sql = [
                    'where' => [
                        [
                            'name' => 'store_id',
                            'value' => $v['id']
                        ],
                        [
                            'name' => 'parent_id',
                            'value' => 0
                        ]
                    ]
                ];
                $storeUser = BaseRepository::getArraySqlFirst($storeUserList, $sql);

                $v['stores_user'] = $storeUser['stores_user'] ?? '';
                $v['stores_img'] = $this->dscRepository->getImagePath($v['stores_img']);

                if (isset($GLOBALS['_CFG']['show_mobile']) && $GLOBALS['_CFG']['show_mobile'] == 0) {
                    $v['stores_tel'] = $this->dscRepository->stringToStar($v['stores_tel']);
                }

                $row[$k] = $v;
            }
        }

        $arr = ['pzd_list' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
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

    public function getDataList($type = 0)
    {
        $adminru = get_admin_ru_id();

        //主订单下有子订单时，则主订单不显示
        if ($type != 0) {
            $res = OrderGoods::whereRaw(1);

            $filter['type'] = isset($_REQUEST['type']) && !empty($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
            $filter['order_type'] = !empty($_REQUEST['order_type']) ? intval($_REQUEST['order_type']) : 0;
            $filter['date_start_time'] = !empty($_REQUEST['date_start_time']) ? trim($_REQUEST['date_start_time']) : '';
            $filter['date_end_time'] = !empty($_REQUEST['date_end_time']) ? trim($_REQUEST['date_end_time']) : '';
            $filter['store_name'] = !empty($_REQUEST['store_name']) ? trim($_REQUEST['store_name']) : '';
            $filter['shop_name'] = !empty($_REQUEST['shop_name']) ? trim($_REQUEST['shop_name']) : '';
            $filter['order_status'] = isset($_REQUEST['order_status']) ? explode(',', $_REQUEST['order_status']) : '-1';
            $filter['shipping_status'] = isset($_REQUEST['shipping_status']) ? explode(',', $_REQUEST['shipping_status']) : '-1';
            $filter['store_id'] = isset($_REQUEST['store_id']) && !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;

            if ($filter['date_start_time'] == '' && $filter['date_end_time'] == '') {
                $start_time = TimeRepository::getLocalMktime(0, 0, 0, TimeRepository::getLocalDate('m'), 1, TimeRepository::getLocalDate('Y')); //本月第一天
                $end_time = TimeRepository::getLocalMktime(0, 0, 0, TimeRepository::getLocalDate('m'), TimeRepository::getLocalDate('t'), TimeRepository::getLocalDate('Y')) + 24 * 60 * 60 - 1; //本月最后一天
            } else {
                $start_time = TimeRepository::getLocalStrtoTime($filter['date_start_time']);
                $end_time = TimeRepository::getLocalStrtoTime($filter['date_end_time']);
            }
            $res = $res->whereHasIn('getOrder', function ($query) use ($start_time, $end_time) {
                $query->where('main_count', 0)
                    ->where('add_time', '>', $start_time)
                    ->where('add_time', '<', $end_time);
            });


            if (isset($filter['store_name']) && $filter['store_name']) {
                $filter['store_id'] = OfflineStore::where('stores_name', 'LIKE', '%' . mysql_like_quote($filter['store_name']) . '%')->value('id');
                $filter['store_id'] = $filter['store_id'] ? $filter['store_id'] : 0;

                $store_id = $filter['store_id'];
                $res = $res->whereHasIn('getOrder', function ($query) use ($store_id) {
                    $query->whereHasIn('getStoreOrder', function ($query) use ($store_id) {
                        $query->where('store_id', $store_id);
                    });
                });
            }

            if (isset($filter['type']) && $filter['type']) {
                //管理员查询的权限 -- 店铺查询 start
                $filter['store_search'] = empty($_REQUEST['store_search']) ? 0 : intval($_REQUEST['store_search']);
                $filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
                $filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';

                if ($filter['store_search'] != 0) {
                    if ($adminru['ru_id'] == 0) {
                        $store_type = isset($_REQUEST['store_type']) && !empty($_REQUEST['store_type']) ? intval($_REQUEST['store_type']) : 0;

                        if ($filter['store_search'] == 1) {
                            $res = $res->where('ru_id', $filter['merchant_id']);
                        }

                        if ($filter['store_search'] > 1 && $filter['store_search'] != 4) {
                            $res = $res->where(function ($query) use ($filter, $store_type) {
                                $query->whereHasIn('getMerchantsShopInformation', function ($query) use ($filter, $store_type) {
                                    if ($filter['store_search'] == 2) {
                                        $query->where('rz_shop_name', 'LIKE', '%' . mysql_like_quote($filter['store_keyword']) . '%');
                                    } elseif ($filter['store_search'] == 3) {
                                        $query = $query->where('shoprz_brand_name', 'LIKE', '%' . mysql_like_quote($filter['store_keyword']) . '%');
                                        if ($store_type) {
                                            $query->where('shop_name_suffix', $store_type);
                                        }
                                    }
                                });
                            });
                        }
                    }
                } else {
                    $res = $res->where('ru_id', '>', 0);
                }
                //管理员查询的权限 -- 店铺查询 end
            } else {
                $res = $res->where('ru_id', 0);

                if ($filter['store_id']) {
                    $store_id = $filter['store_id'];
                    $res = $res->whereHasIn('getOrder', function ($query) use ($store_id) {
                        $query->whereHasIn('getStoreOrder', function ($query) use ($store_id) {
                            $query->where('store_id', $store_id);
                        });
                    });
                }
            }

            /* 分页大小 */
            $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

            $page_size = request()->cookie('dsccp_page_size');
            if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
                $filter['page_size'] = intval($_REQUEST['page_size']);
            } elseif (intval($page_size) > 0) {
                $filter['page_size'] = intval($page_size);
            } else {
                $filter['page_size'] = 15;
            }

            if ($filter['order_status'] != '-1') { //多选
                $order_status = implode(',', $filter['order_status']);

                if ($order_status != '') {
                    $order_status = BaseRepository::getExplode($order_status);
                    $res = $res->whereHasIn('getOrder', function ($query) use ($order_status) {
                        $query->whereIn('order_status', $order_status);
                    });
                }
            }

            if ($filter['shipping_status'] != '-1') { //多选
                $shipping_status = implode(',', $filter['shipping_status']);
                if ($shipping_status != '') {
                    $shipping_status = BaseRepository::getExplode($shipping_status);
                    $res = $res->whereHasIn('getOrder', function ($query) use ($shipping_status) {
                        $query->whereIn('shipping_status', $shipping_status);
                    });
                }
            }

            if ($filter['order_type'] > 0) {
                $res = $res->whereHasIn('getOrder', function ($query) {
                    $query->whereHasIn('getStoreOrder', function ($query) {
                        $query->where('is_grab_order', 1);
                    });
                });
            }

            $res = $res->whereHasIn('getOrder', function ($query) {
                $query = $query->whereHasIn('getStoreOrder', function ($query) {
                    $query->where('store_id', '>', 0);
                });
                $query->groupBy('order_id');
            });

            $res = $res->with(['getOrder' => function ($query) {
                $query = $query->selectRaw("order_id,add_time,(" . $this->orderService->orderAmountField('') . ") AS total_fee");
                $query->with(['getStoreOrder' => function ($query) {
                    $query->select('order_id', 'store_id');
                }]);
            }]);

            $res = $res->orderBy('goods_id', 'DESC')
                ->offset(($filter['page'] - 1) * $filter['page_size'])
                ->limit($filter['page_size']);
            $data_list = BaseRepository::getToArrayGet($res);
        } else {
            $data_list = [];
        }
        /* 记录总数 */
        $filter['record_count'] = count($data_list);
        $filter['page_count'] = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;
        $store_total = 0;
        if ($type != 0) {
            if (!empty($data_list)) {

                $ru_id = BaseRepository::getKeyPluck($data_list, 'ru_id');
                $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

                for ($i = 0; $i < count($data_list); $i++) {
                    $data_list[$i]['total_fee'] = 0;
                    $data_list[$i]['store_id'] = 0;
                    $data_list[$i]['add_time'] = '';
                    if (isset($data_list[$i]['get_order']) && !empty($data_list[$i]['get_order'])) {
                        $data_list[$i]['total_fee'] = $data_list[$i]['get_order']['total_fee'];
                        $data_list[$i]['add_time'] = $data_list[$i]['get_order']['add_time'];

                        if (isset($data_list[$i]['get_order']['get_store_order']) && !empty($data_list[$i]['get_order']['get_store_order'])) {
                            $data_list[$i]['store_id'] = $data_list[$i]['get_order']['get_store_order']['store_id'];
                        }
                    }

                    $data_list[$i]['shop_name'] = $merchantList[$data_list[$i]['ru_id']]['shop_name'] ?? '';
                    $store_total += $data_list[$i]['total_fee'] = $data_list[$i]['goods_number'] * $data_list[$i]['goods_price'];

                    $data_list[$i]['stores_name'] = OfflineStore::where('id', $data_list[$i]['store_id'])->value('stores_name');
                    $data_list[$i]['stores_name'] = $data_list[$i]['stores_name'] ? $data_list[$i]['stores_name'] : '';

                    $data_list[$i]['add_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $data_list[$i]['add_time']);
                }
            }
            if (isset($filter['sort_by']) && $filter['sort_by'] == 'goods_number') {
                $data_list = get_array_sort($data_list, 'goods_number', 'DESC');
            }
            $arr = [
                'data_list' => $data_list,
                'filter' => $filter,
                'page_count' => $filter['page_count'],
                'record_count' => $filter['record_count'],
                'store_total' => $this->dscRepository->getPriceFormat($store_total)
            ];

            return $arr;
        }
    }

    public function statFilter()
    {
        $start_time = $_REQUEST['start_time'] ?? '';
        $end_time = $_REQUEST['end_time'] ?? '';
        $type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
        $start_time = TimeRepository::getLocalStrtoTime($start_time);
        $end_time = TimeRepository::getLocalStrtoTime($end_time);

        $res = OrderGoods::whereRaw(1);
        //主订单下有子订单时，则主订单不显示
        $res = $res->whereHasIn('getOrder', function ($query) use ($start_time, $end_time) {
            $query = $query->where('main_count', 0)
                ->where('add_time', '>', $start_time)
                ->where('add_time', '<', $end_time);
            $query = $query->whereHasIn('getStoreOrder', function ($query) {
                $query->where('store_id', '>', 0);
            });
            $query->groupBy('order_id');
        });
        if ($type) {
            $res = $res->where('ru_id', '>', 0);
        } else {
            $res = $res->where('ru_id', 0);
        }
        $res = $res->select('order_id');
        $res = $res->withCount('getOrder as order_num');
        $res = $res->with(['getOrder' => function ($query) {
            $query->selectRaw('order_id,sum(goods_amount) as total_fee');
        }]);

        $list = BaseRepository::getToArrayGet($res);

        $result = ['order_num' => '', 'total_fee' => '', 'goods_list' => []];
        foreach ($list as $v) {
            $result['order_num'] = intval($result['order_num']);
            $result['total_fee'] = intval($result['total_fee']);
            $v['total_fee'] = 0;
            if (isset($v['get_order']) && !empty($v['get_order'])) {
                $v['total_fee'] = $v['get_order']['total_fee'];
            }
            $result['order_num'] += $v['order_num'];
            $result['total_fee'] += $v['total_fee'];
        }

        $res = $res->selectRaw('order_id,goods_name,goods_price, SUM(goods_number) AS sales_num ');
        $res = $res->with(['getOrder' => function ($query) {
            $query = $query->with(['getStoreOrder' => function ($query) {
                $query->select('store_id', 'order_id');
            }]);
            $query->selectRaw('order_id,MAX(add_time) AS last_sale_time');
        }]);

        $goods_list = BaseRepository::getToArrayGet($res);
        if (!empty($goods_list)) {
            foreach ($goods_list as $index => $item) {
                $item['last_sale_time'] = '';
                $item['store_id'] = 0;
                if (isset($item['get_order']) && !empty($item['get_order'])) {
                    $item['last_sale_time'] = $item['get_order']['last_sale_time'];

                    if (isset($item['get_order']['get_store_order']) && !empty($item['get_order']['get_store_order'])) {
                        $item['store_id'] = $item['get_order']['get_store_order']['store_id'];
                    }
                }

                $goods_list[$index] = $item;
            }
        }
        $result['goods_list'] = $goods_list;
        return $result;
    }
}
