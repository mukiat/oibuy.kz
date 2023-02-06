<?php

namespace App\Services\Other;

use App\Models\CollectGoods;
use App\Models\Goods;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;

class AttentionListManageService
{
    public function getAttenTion()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getAttenTion';
        $get_filter = app(DscRepository::class)->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;
        
        $filter['goods_name'] = isset($_REQUEST['goods_name']) && !empty($_REQUEST['goods_name']) ? trim($_REQUEST['goods_name']) : '';
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'last_update' : trim($_REQUEST['sort_by']);

        $row = Goods::where('is_delete', 0);

        if (!empty($filter['goods_name'])) {
            $row = $row->where('goods_name', 'like', '%' . $filter['goods_name'] . '%');
        }

        $row = $row->whereHasIn('getCollectGoods', function ($query) {
            $query->where('is_attention', 1);
        });

        $res = $record_count = $row;

        $filter['record_count'] = $record_count->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        app(DscRepository::class)->setSessionFilter($filter, $param_str);

        $res = $res->orderBy($filter['sort_by'], $filter['sort_order']);

        if ($filter['start'] > 0) {
            $res = $res->skip($filter['start']);
        }

        if ($filter['page_size'] > 0) {
            $res = $res->take($filter['page_size']);
        }

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $k => $v) {
                $res[$k]['last_update'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $v['last_update']);
            }
        }

        $arr = ['goodsdb' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
        return $arr;
    }

    /**
     * 会员关注商品的数量
     *
     * @param int $goods_id
     * @return array
     */
    public function getUserCollectGoodsCount($goods_id = 0, $date = 0)
    {
        $count = CollectGoods::where('is_attention', 1);

        if ($goods_id) {
            $count = $count->where('goods_id', $goods_id);
        }

        $count = $count->whereHasIn('getUsers');

        $where = [
            'date' => $date
        ];
        $count = $count->whereHasIn('getGoods', function ($query) use ($where) {
            $query->where('is_delete', 0);

            if ($where['date'] > 0) {
                $query->where('last_update', '>=', $where['date']);
            }
        });

        $count = $count->count();

        return $count;
    }

    /**
     * 会员关注商品的列表
     *
     * @param int $goods_id
     * @return array
     */
    public function getUserCollectGoodsList($goods_id = 0, $date = 0, $start = 0, $size = 10)
    {
        $row = CollectGoods::where('is_attention', 1);

        if ($goods_id) {
            $row = $row->where('goods_id', $goods_id);
        }

        $row = $row->whereHasIn('getUsers');

        $where = [
            'date' => $date
        ];

        $row = $row->whereHasIn('getGoods', function ($query) use ($where) {
            $query = $query->where('is_delete', 0);

            if ($where['date'] > 0) {
                $query->where('last_update', '>=', $where['date']);
            }
        });

        $row = $row->with([
            'getUsers',
            'getGoods'
        ]);

        if ($start > 0) {
            $row = $row->skip($start);
        }

        if ($size > 0) {
            $row = $row->take($size);
        }

        $row = BaseRepository::getToArrayGet($row);

        return $row;
    }
}
