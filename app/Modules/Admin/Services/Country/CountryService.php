<?php

namespace App\Modules\Admin\Services\Country;


use App\Models\Country;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;

class CountryService
{
    private $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 国家列表
     *
     * @return array
     */
    public function getCountryList()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getCountryList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 过滤查询 */
        $filter = [];

        $filter['keyword'] = !empty($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
        }

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['pid'] = empty($_REQUEST['pid']) ? 0 : intval($_REQUEST['pid']);

        $row = Country::whereRaw(1);

        if (!empty($filter['keyword'])) {
            $row = $row->where(function ($query) use ($filter) {
                $keyword = $this->dscRepository->mysqlLikeQuote($filter['keyword']);
                $query->where('country_name', 'like', '%' . $keyword . '%');
            });
        }

        $res = $record_count = $row;

        /* 获得总记录数据 */
        $filter['record_count'] = $record_count->count();

        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        if ($filter['start'] > 0) {
            $res = $res->skip($filter['start']);
        }

        if ($filter['page_size'] > 0) {
            $res = $res->take($filter['page_size']);
        }

        $res = $res->orderBy($filter['sort_by'], $filter['sort_order']);

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $key => $rows) {
                $res[$key]['add_time'] = TimeRepository::getLocalDate("Y-m-d H:i:s", $rows['add_time']);
                $res[$key]['country_icon'] = $rows['country_icon'] ? $this->dscRepository->getImagePath($rows['country_icon']) : '';
            }
        }

        return ['list' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }

    /**
     * 国家信息
     *
     * @param int $id
     * @return mixed
     */
    public function country_info($id = 0)
    {
        $country = Country::where('id', $id);
        $country = BaseRepository::getToArrayFirst($country);


        if ($country) {
            $country['icon'] = $country['country_icon'];
            $country['country_icon'] = $country['country_icon'] ? $this->dscRepository->getImagePath($country['country_icon']) : '';
        }

        return $country;
    }
}