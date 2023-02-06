<?php

namespace App\Services\Ads;

use App\Models\TeamCategory;
use App\Models\TouchAd;
use App\Models\TouchAdPosition;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Common\CommonManageService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Order\OrderDataHandleService;

class TouchAdsManageService
{
    protected $commonManageService;
    protected $dscRepository;
    protected $merchantCommonService;

    public function __construct(
        CommonManageService $commonManageService,
        MerchantCommonService $merchantCommonService,
        DscRepository $dscRepository
    )
    {
        $this->commonManageService = $commonManageService;
        $this->merchantCommonService = $merchantCommonService;
        $this->dscRepository = $dscRepository;
    }

    /**
     * 获取touch广告位置列表
     *
     * @param int $ru_id
     * @param string $ad_type
     * @return array
     * @throws \Exception
     */
    public function touchAdPositionList($ru_id = 0, $ad_type = '')
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'touchAdPositionList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $filter = [];

        $filter['keyword'] = !empty($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
        }

        $filter['ru_id'] = isset($_REQUEST['ru_id']) && !empty($_REQUEST['ru_id']) ? intval($_REQUEST['ru_id']) : $ru_id;

        //管理员查询的权限 -- 店铺查询 start
        $filter['store_search'] = empty($_REQUEST['store_search']) ? 0 : intval($_REQUEST['store_search']);
        $filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
        $filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';

        $row = TouchAdPosition::query();

        if ($filter['ru_id'] > 0) {
            $row = $row->where(function ($query) use ($filter) {
                $query->where('user_id', $filter['ru_id'])
                    ->orWhere('is_public', 1);
            });
        }

        if ($filter['store_search'] != 0) {
            if ($filter['ru_id'] == 0) {
                $store_type = isset($_REQUEST['store_type']) && !empty($_REQUEST['store_type']) ? intval($_REQUEST['store_type']) : 0;
                $filter['store_type'] = $store_type;

                if ($filter['store_search'] == 1) {
                    $row = $row->where('user_id', $filter['merchant_id']);
                }

                if ($filter['store_search'] > 1) {
                    $row = $row->whereHasIn('getMerchantsShopInformation', function ($query) use ($filter) {
                        if ($filter['store_search'] == 2) {
                            $query->where('rz_shop_name', 'like', '%' . mysql_like_quote($filter['store_keyword']) . '%');
                        } elseif ($filter['store_search'] == 3) {
                            $query = $query->where('shoprz_brand_name', 'like', '%' . mysql_like_quote($filter['store_keyword']) . '%');

                            if ($filter['store_type']) {
                                $query->where('shop_name_suffix', $filter['store_type']);
                            }
                        }
                    });
                }
            }
        }

        /* 关键字 */
        if (!empty($filter['keyword'])) {
            $row = $row->where('position_name', 'like', '%' . mysql_like_quote($filter['keyword']) . '%');
        }
        if (!empty($ad_type)) {
            $row = $row->where('ad_type', $ad_type);
        } else {
            $row = $row->where(function ($query) {
                $query->where('ad_type', '')->orWhere('ad_type', 'seckill')->orWhere('ad_type', 'supplier');
            });
        }

        $res = $record_count = $row;

        /* 记录总数以及页数 */
        $filter['record_count'] = $record_count->count();

        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 查询数据 */
        if ($filter['start'] > 0) {
            $res = $res->skip($filter['start']);
        }

        if ($filter['page_size'] > 0) {
            $res = $res->take($filter['page_size']);
        }

        $res = $res->orderBy('position_id', 'desc');

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $ru_id = BaseRepository::getKeyPluck($res, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($res as $key => $rows) {
                $position_desc = !empty($rows['position_desc']) ? $this->dscRepository->subStr($rows['position_desc'], 50, true) : '';
                $res[$key]['position_desc'] = nl2br(htmlspecialchars($position_desc));
                $res[$key]['user_name'] = $merchantList[$rows['user_id']]['shop_name'] ?? '';
            }
        }

        return ['position' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }

    /**
     * 广告位置信息
     *
     * @param int $position_id
     * @return array
     */
    public function getPositionInfo($position_id = 0)
    {
        $row = TouchAdPosition::where('position_id', $position_id);
        $row = BaseRepository::getToArrayFirst($row);

        return $row;
    }

    /**
     * 获取所有拼团主频道
     * @return array
     */
    public function getTeamCategoryList()
    {
        $model = TeamCategory::query()->where('parent_id', 0)->where('status', 1)->get();

        $res = $model ? $model->toArray() : [];

        return $res;
    }

    /**
     * 广告位置列表
     * @param int $ru_id
     * @param string $ad_type
     * @return array
     */
    public function getTouchPositionList($ru_id = 0, $ad_type = '')
    {
        $res = TouchAdPosition::query();

        if ($ru_id > 0) {
            $res = $res->where('is_public', 1);
        }

        if (!empty($ad_type)) {
            $res = $res->where('ad_type', $ad_type);
        } else {
            $res = $res->where('ad_type', '')->orWhere('ad_type', 'seckill')->orWhere('ad_type', 'supplier');
        }

        $res = $res->orderBy('position_id', 'DESC');

        $res = BaseRepository::getToArrayGet($res);

        return $res;
    }

    /**
     * 格式化后的广告位列表
     * @param int $ru_id
     * @param string $ad_type
     * @return array
     */
    public function getTouchPositionListFormat($ru_id = 0, $ad_type = '')
    {
        $res = $this->getTouchPositionList($ru_id, $ad_type);

        $position_list = [];
        if ($res) {
            foreach ($res as $row) {
                $position_list[$row['position_id']] = addslashes($row['position_name']) . ' [' . $row['ad_width'] . 'x' . $row['ad_height'] . ']';
            }
        }

        return $position_list;
    }

    /**
     * 获取广告数据列表
     *
     * @param int $ru_id
     * @param string $ad_type
     * @return array
     * @throws \Exception
     */
    public function getTouchAdsList($ru_id = 0, $ad_type = '')
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getTouchAdsList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 过滤查询 */
        $filter = [];

        //ecmoban模板堂 --zhuo start
        $filter['keyword'] = !empty($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';
        $filter['adName'] = !empty($_REQUEST['adName']) ? trim($_REQUEST['adName']) : '';
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
            $filter['adName'] = json_str_iconv($filter['adName']);
        }
        //ecmoban模板堂 --zhuo end

        $filter['ru_id'] = isset($_REQUEST['ru_id']) && !empty($_REQUEST['ru_id']) ? intval($_REQUEST['ru_id']) : $ru_id;
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'ad_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['pid'] = empty($_REQUEST['pid']) ? 0 : intval($_REQUEST['pid']);


        $row = TouchAd::query();

        if (!empty($filter['pid'])) {
            $row = $row->where('position_id', $filter['pid']);
        }

        if ($filter['ru_id'] > 0) {
            $row = $row->where(function ($query) use ($filter) {
                $query = $query->where('is_public', 1)
                    ->where('public_ruid', $filter['ru_id']);

                $query->orWhere(function ($query) use ($filter) {
                    $query->whereHasIn('getTouchAdPosition', function ($query) use ($filter) {
                        $query->where('user_id', $filter['ru_id']);
                    });
                });
            });
        }

        $ad_type = $ad_type ?? '';
        $row = $row->whereHasIn('getTouchAdPosition', function ($query) use ($ad_type) {
            if (!empty($ad_type)) {
                $query->where('ad_type', $ad_type);
            } else {
                $query->where('ad_type', '')->orWhere('ad_type', 'seckill')->orWhere('ad_type', 'supplier');
            }
        });

        // 搜索广告位名称 keyword 、广告名称 adName
        if (empty($filter['keyword']) && !empty($filter['adName'])) {
            $row = $row->where(function ($query) use ($filter) {
                $query->where('ad_name', 'like', '%' . mysql_like_quote($filter['adName']) . '%');
            });
        } elseif (!empty($filter['keyword'])) {
            $position_id = TouchAdPosition::query()->select('position_id')
                ->where('position_name', 'like', '%' . mysql_like_quote($filter['keyword']) . '%')
                ->pluck('position_id');
            $position_id = BaseRepository::getToArray($position_id);

            $row = $row->where(function ($query) use ($filter, $position_id) {
                $query = $query->where('ad_name', 'like', '%' . mysql_like_quote($filter['adName']) . '%');

                if ($position_id) {
                    $query->orWhere(function ($query) use ($position_id) {
                        $query->whereIn('position_id', $position_id);
                    });
                }
            });
        }

        $time = TimeRepository::getGmTime();

        $filter['advance_date'] = isset($_REQUEST['advance_date']) ? intval($_REQUEST['advance_date']) : 0;
        if ($filter['advance_date'] == 1) {
            $row = $row->whereRaw($time . " BETWEEN (end_time - 3600*24*3) AND end_time");
        } elseif ($filter['advance_date'] == 2) {
            $row = $row->where('end_time', '<', $time);
        }

        $res = $record_count = $row;

        /* 获得总记录数据 */
        $filter['record_count'] = $record_count->count();

        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $res = $res->with('getTouchAdPosition');

        if ($filter['start'] > 0) {
            $res = $res->skip($filter['start']);
        }

        if ($filter['page_size'] > 0) {
            $res = $res->take($filter['page_size']);
        }

        $res = $res->orderBy($filter['sort_by'], $filter['sort_order']);

        $res = BaseRepository::getToArrayGet($res);

        /* 获得广告数据 */
        $idx = 0;
        $arr = [];
        if ($res) {

            $ad_id = BaseRepository::getKeyPluck($res, 'ad_id');
            $orderList = OrderDataHandleService::fromAdOrderList($ad_id, ['from_ad', 'order_id']);

            $user_id = BaseRepository::getKeyPluck($res, 'user_id');
            $userMerchant = MerchantDataHandleService::getMerchantInfoDataList($user_id);

            $public_ruid = BaseRepository::getKeyPluck($res, 'public_ruid');
            $ruidMerchant = MerchantDataHandleService::getMerchantInfoDataList($public_ruid);

            foreach ($res as $key => $rows) {

                $from_ad = $orderList[$rows['ad_id']] ?? [];
                $rows['ad_stats'] = BaseRepository::getArrayCount($from_ad);

                $rows['position_name'] = $rows['get_touch_ad_position']['position_name'] ?? '';
                $rows['user_id'] = $rows['get_touch_ad_position']['user_id'] ?? 0;

                /* 广告类型的名称 */
                $rows['type'] = ($rows['media_type'] == 0) ? $GLOBALS['_LANG']['ad_img'] : '';
                $rows['type'] .= ($rows['media_type'] == 1) ? $GLOBALS['_LANG']['ad_flash'] : '';
                $rows['type'] .= ($rows['media_type'] == 2) ? $GLOBALS['_LANG']['ad_html'] : '';
                $rows['type'] .= ($rows['media_type'] == 3) ? $GLOBALS['_LANG']['ad_text'] : '';

                /* 格式化日期 */
                $rows['start_date'] = TimeRepository::getLocalDate(config('shop.time_format'), $rows['start_time']);
                $rows['end_date'] = TimeRepository::getLocalDate(config('shop.time_format'), $rows['end_time']);

                if ($time > ($rows['end_time'] - 24 * 3600 * 3) && $time < $rows['end_time']) {
                    $rows['advance_date'] = 1;
                } elseif ($time > $rows['end_time']) {
                    $rows['advance_date'] = 2;
                }

                if ($rows['public_ruid'] == 0) {
                    $rows['user_name'] = $userMerchant[$rows['user_id']]['shop_name'] ?? '';
                } else {
                    $rows['user_name'] = $ruidMerchant[$rows['public_ruid']]['shop_name'] ?? '';
                }

                $rows['ad_code'] = $this->dscRepository->getImagePath(DATA_DIR . '/afficheimg/' . $rows['ad_code']);

                $arr[$idx] = $rows;

                $idx++;
            }
        }

        return ['ads' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }
}
