<?php

namespace App\Services\SellerDomain;

use App\Models\SellerDomain;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Common\CommonManageService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;

class SellerDomainManageService
{
    protected $commonManageService;
    protected $merchantCommonService;
    protected $dscRepository;

    public function __construct(
        CommonManageService $commonManageService,
        MerchantCommonService $merchantCommonService,
        DscRepository $dscRepository
    ) {
        $this->commonManageService = $commonManageService;
        $this->merchantCommonService = $merchantCommonService;
        $this->dscRepository = $dscRepository;
    }

    /*分页*/
    public function sellerDomainList()
    {
        $adminru = get_admin_ru_id();

        $res = SellerDomain::whereRaw(1);
        if ($adminru['ru_id'] > 0) {
            $res = $res->where('ru_id', $adminru['ru_id']);
        }

        $filter['record_count'] = $res->count();
        $filter = page_and_size($filter);
        /* 获活动数据 */

        $filter['keywords'] = isset($filter['keywords']) ? stripslashes($filter['keywords']) : '';

        $res = $res->orderBy('id')
            ->offset($filter['start'])
            ->limit($filter['page_size']);
        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $ru_id = BaseRepository::getKeyPluck($res, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($res as $key => $row) {
                $res[$key]['domain_name'] = $row['domain_name'] ? $row['domain_name'] . '.' . $this->dscRepository->hostDomain() : lang('common.temporary_no');
                $res[$key]['shop_name'] = $merchantList[$row['ru_id']]['shop_name'] ?? '';
                $res[$key]['validity_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $row['validity_time']);
            }
        }

        $arr = ['domain_list' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
        return $arr;
    }
}
