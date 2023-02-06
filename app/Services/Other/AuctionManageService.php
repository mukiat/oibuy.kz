<?php

namespace App\Services\Other;

use App\Models\AuctionLog;
use App\Models\GoodsActivity;
use App\Models\OrderInfo;
use App\Repositories\Activity\ActivityRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Common\CommonManageService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;

class AuctionManageService
{
    protected $commonManageService;
    protected $merchantCommonService;
    protected $dscRepository;

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
     * 取得拍卖活动列表
     *
     * @return array
     * @throws \Exception
     */
    public function getAuctionList()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getAuctionList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $seller = $this->commonManageService->getAdminIdSeller();

        /* 过滤条件 */
        $filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
        }
        $filter['is_going'] = empty($_REQUEST['is_going']) ? 0 : 1;
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'act_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? 1 : 0;  //商家和自营订单标识
        $filter['review_status'] = empty($_REQUEST['review_status']) ? 0 : intval($_REQUEST['review_status']);

        $row = GoodsActivity::where('act_type', GAT_AUCTION);

        //卖场 start
        $filter['rs_id'] = empty($_REQUEST['rs_id']) ? 0 : intval($_REQUEST['rs_id']);
        $adminru = get_admin_ru_id();

        if ($adminru['rs_id'] > 0) {
            $filter['rs_id'] = $adminru['rs_id'];
        }
        //卖场 end

        if (!empty($filter['keyword'])) {
            $keyword = mysql_like_quote($filter['keyword']);
            $row = $row->where(function ($query) use ($keyword) {
                $query->where('goods_name', 'like', '%' . $keyword . '%')
                    ->orWhere('act_name', 'like', '%' . $keyword . '%');
            });
        }

        $time = TimeRepository::getGmTime();
        if ($filter['is_going']) {
            $row = $row->where('is_finished', 0)
                ->where('start_time', '<=', $time)
                ->where('end_time', '>=', $time);
        }

        //卖场
        if ($seller['ru_id'] > 0) {
            $row = $row->where('user_id', $seller['ru_id']);
        }

        if ($filter['review_status']) {
            $row = $row->where('review_status', $filter['review_status']);
        }

        //管理员查询的权限 -- 店铺查询 start
        $filter['store_search'] = !isset($_REQUEST['store_search']) ? -1 : intval($_REQUEST['store_search']);
        $filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
        $filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';

        if ($filter['store_search'] > -1) {
            if ($seller['ru_id'] == 0) {
                if ($filter['store_search'] > 0) {
                    $filter['store_type'] = isset($_REQUEST['store_type']) && !empty($_REQUEST['store_type']) ? intval($_REQUEST['store_type']) : 0;

                    if ($filter['store_search'] == 1) {
                        $row = $row->where('user_id', $filter['merchant_id']);
                    }

                    if ($filter['store_search'] > 1) {
                        $row = $row->whereHasIn('getMerchantsShopInformation', function ($query) use ($filter) {
                            if ($filter['store_type']) {
                                $query = $query->where('shop_name_suffix', $filter['store_type']);
                            }

                            if ($filter['store_search'] == 2) {
                                $query->where('rz_shop_name', 'like', '%' . $filter['store_keyword'] . '%');
                            } elseif ($filter['store_search'] == 3) {
                                $query->where('shoprz_brand_name', 'like', '%' . $filter['store_keyword'] . '%');
                            }
                        });
                    }
                } else {
                    $row = $row->where('user_id', 0);
                }
            }
        }
        //管理员查询的权限 -- 店铺查询 end

        if (!empty($filter['seller_list'])) {
            $row = CommonRepository::constantMaxId($row, 'user_id');
        } elseif (empty($filter['seller_list']) && $seller['ru_id'] == 0) {
            $row = $row->where('user_id', 0);
        }

        $res = $record_count = $row;

        $filter['record_count'] = $record_count->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 查询 */
        $res = $res->orderBy($filter['sort_by'], $filter['sort_order']);

        if ($filter['start'] > 0) {
            $res = $res->skip($filter['start']);
        }

        if ($filter['page_size'] > 0) {
            $res = $res->take($filter['page_size']);
        }

        $filter['keyword'] = stripslashes($filter['keyword']);

        $res = BaseRepository::getToArrayGet($res);

        $list = [];
        if ($res) {

            $ru_id = BaseRepository::getKeyPluck($res, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($res as $row) {
                $ext_info = unserialize($row['ext_info']);
                $arr = array_merge($row, $ext_info);

                $arr['start_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $arr['start_time']);
                $arr['end_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $arr['end_time']);
                $arr['ru_name'] = $merchantList[$arr['user_id']]['shop_name'] ?? '';
                $arr['status_no'] = ActivityRepository::getAuctionStatus($row, $time);
                $arr['status'] = $GLOBALS['_LANG']['auction_status'][$arr['status_no']];
                $list[] = $arr;
            }
        }

        $arr = ['item' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /**
     * 列表链接
     * @param bool $is_add 是否添加（插入）
     * @param string $text 文字
     * @return  array('href' => $href, 'text' => $text)
     */
    public function getListLink($is_add = true, $text = '')
    {
        $href = 'auction.php?act=list';
        if (!$is_add) {
            $href .= '&uselastfilter=1';
        }
        if ($text == '') {
            $text = $GLOBALS['_LANG']['auction_list'];
        }

        return ['href' => $href, 'text' => $text];
    }

    /**
     * 取得拍卖活动信息
     *
     * @param int $act_id
     * @param bool $config
     * @param string $path
     * @return array
     */
    public function getAuctionInfo($act_id = 0, $config = false, $path = '')
    {
        $time = TimeRepository::getGmTime();
        $auction = GoodsActivity::where('act_id', $act_id);

        if (empty($path)) {
            $auction = $auction->where('review_status', 3);
        }

        $auction = $auction->first();

        $auction = $auction ? $auction->toArray() : [];

        if (!$auction) {
            return [];
        }

        $auction['endTime'] = $auction['end_time'];
        $auction['startTime'] = $auction['start_time'];
        if (isset($auction['act_type']) && $auction['act_type'] != GAT_AUCTION) {
            return [];
        }

        $timeFormat = config('shop.time_format');
        $auction['status_no'] = ActivityRepository::getAuctionStatus($auction, $time);
        if ($config == true) {
            $auction['start_time'] = TimeRepository::getLocalDate($timeFormat, $auction['start_time']);
            $auction['end_time'] = TimeRepository::getLocalDate($timeFormat, $auction['end_time']);
        } else {
            $auction['start_time'] = TimeRepository::getLocalDate($timeFormat, $auction['start_time']);
            $auction['end_time'] = TimeRepository::getLocalDate($timeFormat, $auction['end_time']);
        }
        $ext_info = unserialize($auction['ext_info']);

        $auction = array_merge($auction, $ext_info);
        $auction['formated_start_price'] = $this->dscRepository->getPriceFormat($auction['start_price']);
        $auction['formated_end_price'] = $this->dscRepository->getPriceFormat($auction['end_price']);
        $auction['formated_amplitude'] = $this->dscRepository->getPriceFormat($auction['amplitude']);
        $auction['formated_deposit'] = $this->dscRepository->getPriceFormat($auction['deposit']);

        /* 查询出价用户数和最后出价 */
        $auction['bid_user_count'] = AuctionLog::where('act_id', $act_id)->count();

        if ($auction['bid_user_count'] > 0) {
            $row = AuctionLog::where('act_id', $act_id);

            $row = $row->whereHasIn('getUsers');

            $row = $row->with([
                'getUsers' => function ($query) {
                    $query->select('user_id', 'user_name');
                }
            ]);

            $row = $row->orderBy('log_id', 'desc');

            $row = BaseRepository::getToArrayFirst($row);

            if (!empty($row)) {
                if ($row['get_users']) {
                    $row = array_merge($row, $row['get_users']);
                }

                $row['formated_bid_price'] = $this->dscRepository->getPriceFormat($row['bid_price'], false);
                $row['bid_time'] = TimeRepository::getLocalDate($timeFormat, $row['bid_time']);
                $auction['last_bid'] = $row;
                $auction['bid_time'] = $row['bid_time'];
            }
        } else {
            $row['bid_time'] = $auction['end_time'];
        }


        /* 查询已确认订单数 */
        if ($auction['status_no'] > 1) {
            $auction['order_count'] = OrderInfo::where('extension_code', 'auction')
                ->where('extension_id', $act_id)
                ->whereIn('order_status', [OS_CONFIRMED, OS_UNCONFIRMED])
                ->count();
        } else {
            $auction['order_count'] = 0;
        }

        /* 当前价 */
        $auction['current_price'] = isset($auction['last_bid']) ? $auction['last_bid']['bid_price'] : $auction['start_price'];
        $auction['current_price_int'] = intval($auction['current_price']);
        $auction['formated_current_price'] = $this->dscRepository->getPriceFormat($auction['current_price'], false);

        return $auction;
    }
}
