<?php

namespace App\Services\Presale;

use App\Models\Goods;
use App\Models\PresaleActivity;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Activity\PresaleService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;

class PresaleManageService
{
    protected $presaleService;
    protected $merchantCommonService;
    protected $dscRepository;

    public function __construct(
        PresaleService $presaleService,
        MerchantCommonService $merchantCommonService,
        DscRepository $dscRepository
    )
    {
        $this->presaleService = $presaleService;
        $this->merchantCommonService = $merchantCommonService;
        $this->dscRepository = $dscRepository;
    }


    /*
     * 取得预售活动列表
     * @return   array
     */
    public function presaleList($ru_id)
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'presaleList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 过滤条件 */
        $filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
        }
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'act_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? 1 : 0;  //商家和自营订单标识
        $filter['review_status'] = empty($_REQUEST['review_status']) ? 0 : intval($_REQUEST['review_status']);

        //卖场 start
        $filter['rs_id'] = empty($_REQUEST['rs_id']) ? 0 : intval($_REQUEST['rs_id']);
        $adminru = get_admin_ru_id();
        if ($adminru['rs_id'] > 0) {
            $filter['rs_id'] = $adminru['rs_id'];
        }
        //卖场 end
        $res = PresaleActivity::whereRaw(1);
        if ((!empty($filter['keyword']))) {
            $res = $res->where(function ($query) use ($filter) {
                $query->where('goods_name', 'LIKE', '%' . mysql_like_quote($filter['keyword']) . '%');
            });
        }

        if ($filter['review_status']) {
            $res = $res->where('review_status', $filter['review_status']);
        }

        //管理员查询的权限 -- 店铺查询 start
        $filter['store_search'] = !isset($_REQUEST['store_search']) ? -1 : intval($_REQUEST['store_search']);
        $filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
        $filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';

        //ecmoban模板堂 --zhuo start
        if ($ru_id > 0) {
            $res = $res->where('user_id', $ru_id);
        }
        //ecmoban模板堂 --zhuo end

        if ($filter['store_search'] > -1) {
            if ($ru_id == 0) {
                if ($filter['store_search'] > 0) {
                    $store_type = isset($_REQUEST['store_type']) && !empty($_REQUEST['store_type']) ? intval($_REQUEST['store_type']) : 0;

                    if ($filter['store_search'] == 1) {
                        $res = $res->where('user_id', $filter['merchant_id']);
                    }

                    if ($filter['store_search'] > 1) {
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
                } else {
                    $res = $res->where('user_id', 0);
                }
            }
        }
        //管理员查询的权限 -- 店铺查询 end

        //区分商家和自营
        if (!empty($filter['seller_list'])) {
            $res = CommonRepository::constantMaxId($res, 'user_id');
        } else {
            $res = $res->where('user_id', 0);
        }

        $filter['record_count'] = $res->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 查询 */
        $res = $res->orderBy($filter['sort_by'], $filter['sort_order'])
            ->offset($filter['start'])
            ->limit($filter['page_size']);
        $res = BaseRepository::getToArrayGet($res);

        $list = [];
        if ($res) {

            $ru_id = BaseRepository::getKeyPluck($res, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($res as $row) {
                $stat = $this->presaleService->presaleStat($row['act_id'], $row['deposit']);
                $arr = array_merge($row, $stat);

                $status = $this->presaleService->presaleStatus($arr);

                $arr['start_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['date_format'], $arr['start_time']);
                $arr['end_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['date_format'], $arr['end_time']);
                $arr['pay_start_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['date_format'], $arr['pay_start_time']);
                $arr['pay_end_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['date_format'], $arr['pay_end_time']);
                $arr['cur_status'] = $GLOBALS['_LANG']['gbs'][$status];

                $arr['act_name'] = !empty($arr['act_name']) ? $arr['act_name'] : $arr['goods_name'];
                $arr['shop_name'] = $merchantList[$row['user_id']]['shop_name'] ?? '';

                $list[] = $arr;
            }
        }

        $arr = ['item' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /**
     * 取得某商品的预售活动
     * @param int $goods_id 商品id
     * @return  array
     */
    public function goodsPresale($goods_id)
    {
        $time = TimeRepository::getGmTime();

        $res = PresaleActivity::where('goods_id', $goods_id)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time);
        $row = BaseRepository::getToArrayFirst($res);

        return $row;
    }

    /**
     * 列表链接
     * @param bool $is_add 是否添加（插入）
     * @return  array('href' => $href, 'text' => $text)
     */
    public function listLink($is_add = true)
    {
        $href = 'presale.php?act=list';
        if (!$is_add) {
            $href .= '&' . list_link_postfix();
        }

        return ['href' => $href, 'text' => $GLOBALS['_LANG']['presale_list']];
    }

    /*
    * 获取商品的本店售价，供参考预售商品定金参考比对
    */
    public function getShopPrice($goods_id)
    {
        $shop_price = Goods::where('goods_id', $goods_id)->value('shop_price');
        $shop_price = $shop_price ? $shop_price : 0;

        return $shop_price;
    }
}
