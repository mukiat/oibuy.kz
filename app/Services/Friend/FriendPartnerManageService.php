<?php

namespace App\Services\Friend;

use App\Models\PartnerList;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;

/**
 *
 * Class FriendPartnerManageService
 * @package App\Services\Friend
 */
class FriendPartnerManageService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    ) {
        $this->dscRepository = $dscRepository;
    }

    /* 获取合作伙伴数据列表 */
    public function getLinksList()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getLinksList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;
        
        $filter = [];
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'link_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        /* 获得总记录数据 */
        $filter['record_count'] = PartnerList::count();

        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 获取数据 */
        $res = PartnerList::orderBy($filter['sort_by'], $filter['sort_order'])->offset($filter['start'])->limit($filter['page_size']);
        $res = BaseRepository::getToArrayGet($res);

        $list = [];
        if ($res) {
            foreach ($res as $rows) {
                if (empty($rows['link_logo'])) {
                    $rows['link_logo'] = '';
                } else {
                    if ((strpos($rows['link_logo'], 'http://') === false) && (strpos($rows['link_logo'], 'https://') === false)) {
                        $rows['link_logo'] = $this->dscRepository->getImagePath($rows['link_logo']);
                    }
                }

                $list[] = $rows;
            }
        }

        return ['list' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }
}
