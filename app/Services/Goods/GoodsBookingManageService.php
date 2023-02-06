<?php

namespace App\Services\Goods;

use App\Models\BookingGoods;
use App\Models\Goods;
use App\Models\MerchantsShopInformation;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;

class GoodsBookingManageService
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
     * 获取订购信息
     *
     * @param int $seller_id
     * @return array
     * @throws \Exception
     */
    public function getBooKingList($seller_id = -1)
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getBooKingList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 查询条件 */
        $filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
        }

        $filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? 1 : 0;  //商家和自营订单标识
        $filter['dispose'] = empty($_REQUEST['dispose']) ? 0 : intval($_REQUEST['dispose']);
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'rec_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['seller_id'] = empty($_REQUEST['seller_id']) ? $seller_id : (int)$_REQUEST['seller_id'];

        $res = BookingGoods::whereRaw(1);
        if (!empty($_REQUEST['dispose'])) {
            $res = $res->where('is_dispose', $filter['dispose']);
        }

        if ($filter['seller_id'] > -1) {
            $res = $res->where('ru_id', $filter['seller_id']);
        } else {
            if ($filter['seller_list']) {
                $res = $res->where('ru_id', '>', 0);
            } else {
                $res = $res->where('ru_id', 0);
            }
        }

        if (!empty($filter['keyword'])) {

            $goods_id = Goods::query()->select('goods_id')
                ->where('goods_name', 'LIKE', '%' . mysql_like_quote($filter['keyword']) . '%')
                ->pluck('goods_id');
            $goods_id = BaseRepository::getToArray($goods_id);

            $goods_id = $goods_id ? $goods_id : [-1];

            $res = $res->whereIn('goods_id', $goods_id);
        }

        $filter['record_count'] = $res->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 获取活动数据 */
        $res = $res->select('rec_id', 'link_man', 'goods_number', 'booking_time', 'is_dispose', 'goods_id', 'ru_id');
        $res = $res->offset($filter['start'])->limit($filter['page_size']);

        $res = $res->orderBy($filter['sort_by'], $filter['sort_order']);

        $row = BaseRepository::getToArrayGet($res);

        if ($row) {

            $goods_id = BaseRepository::getKeyPluck($row, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'goods_name']);

            $ru_id = BaseRepository::getKeyPluck($row, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($row as $key => $val) {

                $goods = $goodsList[$val['goods_id']] ?? [];

                $val['goods_name'] = $goods['goods_name'] ?? '';
                $val['booking_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $val['booking_time']);
                $val['user_name'] = $merchantList[$val['ru_id']]['shop_name'] ?? '';
                $row[$key] = $val;
            }
        }

        $filter['keyword'] = stripslashes($filter['keyword']);
        $arr = ['item' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /**
     * 获得缺货登记的详细信息
     *
     * @param integer $id
     *
     * @return  array
     */
    public function getBooKingInfo($id)
    {
        $res = BookingGoods::where('rec_id', $id);
        $res = $res->with(['getGoods' => function ($query) {
            $query->select('goods_id', 'goods_name');
        }]);
        $res = $res->with(['getUsers' => function ($query) {
            $query->select('user_id', 'user_name');
        }]);
        $res = BaseRepository::getToArrayFirst($res);

        $res['goods_name'] = '';
        $res['user_name'] = $GLOBALS['_LANG']['guest_user'];
        if (isset($res['get_goods']) && !empty($res['get_goods'])) {
            $res['goods_name'] = $res['get_goods']['goods_name'];
        }
        if (isset($res['get_users']) && !empty($res['get_users'])) {
            $res['user_name'] = $res['get_users']['user_name'];

            if (config('shop.show_mobile') == 0) {
                $res['user_name'] = $this->dscRepository->stringToStar($res['user_name']);
            }
        }

        if (config('shop.show_mobile') == 0) {
            $res['email'] = $this->dscRepository->stringToStar($res['email']);
            $res['tel'] = $this->dscRepository->stringToStar($res['tel']);
        }

        /* 格式化时间 */
        $res['booking_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $res['booking_time']);
        if (!empty($res['dispose_time'])) {
            $res['dispose_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $res['dispose_time']);
        }

        return $res;
    }
}
