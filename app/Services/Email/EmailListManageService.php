<?php

namespace App\Services\Email;

use App\Models\EmailList;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;

/**
 * Class EmailListManageService
 * @package App\Services\Email
 */
class EmailListManageService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    public function getEmailList()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getEmailList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'stat' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'ASC' : trim($_REQUEST['sort_order']);

        $filter['record_count'] = EmailList::count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 查询 */
        $res = EmailList::orderBy($filter['sort_by'], $filter['sort_order'])
            ->offset($filter['start'])->limit($filter['page_size']);

        $emaildb = BaseRepository::getToArrayGet($res);

        if ($emaildb) {
            foreach ($emaildb as $key => $val) {
                if (config('shop.show_mobile') == 0) {
                    $emaildb[$key]['email'] = $this->dscRepository->stringToStar($val['email']);
                }
            }
        }

        $arr = ['emaildb' => $emaildb, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }
}
