<?php

namespace App\Services\Message;

use App\Models\SellerFollowList;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;

class SellerFollowManageService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 店铺二维码关注
     *
     * @return array
     */
    public function getSellerFollowList($adminru = [])
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'get_seller_follow_list' . $adminru['ru_id'];
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $filter = array();
        $filter['sort_by'] = !empty($_REQUEST['sort_by']) ? trim($_REQUEST['sort_by']) : 'id';
        $filter['sort_order'] = !empty($_REQUEST['sort_order']) ? trim($_REQUEST['sort_order']) : 'DESC';
        $filter['keywords'] = !empty($_REQUEST['keywords']) ? trim($_REQUEST['keywords']) : '';

        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        $row = SellerFollowList::where('seller_id', $adminru['ru_id']);

        $res = $record_count = $row;

        /* 获得总记录数据 */
        $filter['record_count'] = $record_count->count();

        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $res = $res->orderBy($filter['sort_by'], $filter['sort_order']);

        if ($filter['start'] > 0) {
            $res = $res->skip($filter['start']);
        }

        if ($filter['page_size'] > 0) {
            $res = $res->take($filter['page_size']);
        }

        $res = BaseRepository::getToArrayGet($res);

        $list = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $row['cover_pic'] = $this->dscRepository->getImagePath($row['cover_pic']);
                $row['qr_code'] = $this->dscRepository->getImagePath($row['qr_code']);
                $row['desc'] = str_limit($row['desc'], 50);
                $list[] = $row;
            }
        }

        return [
            'list' => $list,
            'filter' => $filter,
            'page_count' => $filter['page_count'],
            'record_count' => $filter['record_count']
        ];
    }

    /**
     * 新增
     * @param array $values
     * @return bool
     */
    public function insertSellerFollow($values = [])
    {
        if (empty($values)) {
            return false;
        }

        $values = BaseRepository::getArrayfilterTable($values, 'seller_follow_list');

        return SellerFollowList::query()->insert($values);
    }

    /**
     * 更新
     * @param array $where
     * @param array $values
     * @return false|int
     */
    public function updateSellerFollow($where = [], $values = [])
    {
        if (empty($values)) {
            return false;
        }

        $values = BaseRepository::getArrayfilterTable($values, 'seller_follow_list');

        return SellerFollowList::query()->where($where)->update($values);
    }

    /**
     * 详情
     * @param int $id
     * @return array
     */
    public function getSellerFollowInfo($id = 0)
    {
        if (empty($id)) {
            return [];
        }

        $info = SellerFollowList::query()->where('id', $id)->first();
        $row = $info ? $info->toArray() : [];

        if ($row) {
            $row['cover_pic'] = $this->dscRepository->getImagePath($row['cover_pic']);
            $row['qr_code'] = $this->dscRepository->getImagePath($row['qr_code']);
        }

        return $row;
    }
}
