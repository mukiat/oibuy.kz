<?php

namespace App\Services\Keywords;

use App\Models\SearchKeyword;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\TimeRepository;

class KeywordsManageService
{
    protected $commonRepository;

    public function __construct(
        CommonRepository $commonRepository
    )
    {
        $this->commonRepository = $commonRepository;
    }

    /* 获取用户检索记录数据列表 */
    public function getKeywordsList()
    {
        $filter = [];
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'addtime' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        /* 获得总记录数据  by kong  */
        $res = SearchKeyword::groupBy('keyword')->orderBy('addtime', 'DESC');

        $res = BaseRepository::getToArrayGet($res);
        $filter['record_count'] = count($res);

        $filter = page_and_size($filter);

        $res = SearchKeyword::groupBy('keyword')->orderBy('addtime', 'DESC');
        $res = $res->offset($filter['start'])->limit($filter['page_size']);
        $res = BaseRepository::getToArrayGet($res);

        $time = TimeRepository::getGmTime();
        $month_time = TimeRepository::getLocalDate("Y-m", $time);

        $list = [];
        foreach ($res as $rows) {
            if (empty($rows['keyword'])) {
                continue;
            }

            //本月
            $rows['month_count'] = SearchKeyword::where('keyword', $rows['keyword'])->where('addtime', 'like', '%' . $month_time . '%')->count();

            //本周
            $rows['week_count'] = SearchKeyword::where('keyword', $rows['keyword'])
                ->whereRaw('date_sub(curdate(), INTERVAL 7 DAY) <= date(addtime)')
                ->count();

            //今天
            $rows['day_count'] = SearchKeyword::where('keyword', $rows['keyword'])
                ->whereRaw('to_days(addtime) = to_days(now())')
                ->count();

            $rows['result_count'] = SearchKeyword::where('keyword', $rows['keyword'])->max('result_count');
            $rows['result_count'] = $rows['result_count'] ? $rows['result_count'] : 0;

            $rows['count'] = SearchKeyword::where('keyword', $rows['keyword'])->count();
            if ($this->commonRepository->isBase64($rows['keyword'])) {
                $rows['keyword'] = base64_decode($rows['keyword']);
            }

            $list[] = $rows;
        }
        $lists = [];
        if ($list) {
            $lists = $this->arrayUniqueFb($list);
            $lists = dimensional_array_sort($lists, $filter['sort_by'], $filter['sort_order']);
        }

        $filter['record_count'] = isset($filter['record_count']) ? $filter['record_count'] : '';

        return ['list' => $lists, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }

    public function arrayUniqueFb($array2D)
    {
        foreach ($array2D as $k => $v) {
            $temp[$v['keyword']] = $v;
        }

        return $temp;
    }
}
