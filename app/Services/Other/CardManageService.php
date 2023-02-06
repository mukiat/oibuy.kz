<?php

namespace App\Services\Other;

use App\Models\Card;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Common\CommonManageService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;

class CardManageService
{
    protected $commonManageService;

    protected $merchantCommonService;

    public function __construct(
        CommonManageService $commonManageService,
        MerchantCommonService $merchantCommonService
    )
    {
        $this->commonManageService = $commonManageService;

        $this->merchantCommonService = $merchantCommonService;
    }

    /**
     * 包装列表
     *
     * @return array
     */
    public function cardsList()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'cardsList';
        $get_filter = app(DscRepository::class)->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $seller = $this->commonManageService->getAdminIdSeller();

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'card_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $row = Card::whereRaw(1);

        if ($seller['ru_id'] > 0) {
            $row = $row->where('user_id', $seller['ru_id']);
        }

        $res = $record_count = $row;

        /* 分页大小 */
        $filter['record_count'] = $record_count->count();

        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        app(DscRepository::class)->setSessionFilter($filter, $param_str);

        /* 查询 */
        $res = $res->orderBy($filter['sort_by'], $filter['sort_order']);

        if ($filter['start'] > 0) {
            $res = $res->skip($filter['start']);
        }

        if ($filter['page_size'] > 0) {
            $res = $res->take($filter['page_size']);
        }

        $card_list = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($card_list) {

            $ru_id = BaseRepository::getKeyPluck($card_list, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($card_list as $key => $row) {
                $arr[$key] = $row;
                $arr[$key]['ru_name'] = $merchantList[$row['user_id']]['shop_name'] ?? '';
            }
        }

        $arr = ['card_list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /**
     * 包装信息
     *
     * @param $card_id
     * @return array
     */
    public function cardInfo($card_id)
    {
        $card = Card::where('card_id', $card_id);
        $card = BaseRepository::getToArrayFirst($card);

        return $card;
    }
}
