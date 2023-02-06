<?php

namespace App\Services\Team;

use App\Models\OrderInfo;
use App\Models\TeamCategory;
use App\Models\TeamGoods;
use App\Models\TeamLog;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Order\OrderGoodsDataHandleService;

/**
 * 拼团
 * Class CrowdFund
 * @package App\Services
 */
class TeamManageService
{
    protected $dscRepository;
    protected $merchantCommonService;

    public function __construct(
        DscRepository $dscRepository,
        MerchantCommonService $merchantCommonService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->merchantCommonService = $merchantCommonService;
    }

    /**
     * 拼团商品列表
     *
     * @param array $filter
     * @param array $offset
     * @return array
     * @throws \Exception
     */
    public function getTeamGoodsList($filter = [], $offset = [])
    {
        if ($filter['type'] == 'list') {
            $model = TeamGoods::where('is_team', 1);
        } else {
            $model = TeamGoods::where('is_team', 0);  // 回收站
        }

        if (!empty($filter['goods_name'])) {
            $model = $model->whereHasIn('getGoods', function ($query) use ($filter) {
                $goods_name = $filter['goods_name'];
                $query->where('goods_name', 'like', '%' . $goods_name . '%')
                    ->orWhere('goods_sn', 'like', '%' . $goods_name . '%')
                    ->orWhere('keywords', 'like', '%' . $goods_name . '%');
            });
        }

        if (isset($filter['audit']) && $filter['audit'] < 3) {
            $model = $model->where('is_audit', $filter['audit']);
        }

        if (isset($filter['tc_id']) && $filter['tc_id'] > 0) {
            $type = $this->getCategroyId($filter['tc_id']);
            $model = $model->whereIn('tc_id', $type);
        }

        // 检测商品是否存在
        $model = $model->whereHasIn('getGoods', function ($query) {
            $query = $query->where('is_alone_sale', 1)
                ->where('is_on_sale', 1)
                ->where('is_delete', 0);

            if (config('shop.review_goods') == 1) {
                $query->whereIn('review_status', [3, 4, 5]);
            }
        });

        $total = $model->count();

        $list = $model->offset($offset['start'])
            ->limit($offset['limit'])
            ->orderBy('id', 'DESC');

        $list = BaseRepository::getToArrayGet($list);

        if ($list) {

            $goods_id = BaseRepository::getKeyPluck($list, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'user_id', 'goods_sn', 'goods_name', 'shop_price', 'market_price', 'goods_number', 'sales_volume', 'goods_img', 'goods_thumb', 'is_best', 'is_new', 'is_hot']);

            $ru_id = BaseRepository::getKeyPluck($goodsList, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($list as $key => $val) {

                $goods = $goodsList[$val['goods_id']] ?? [];
                $val = BaseRepository::getArrayMerge($val, $goods);

                $list[$key] = $val;
                $list[$key]['user_name'] = $merchantList[$val['user_id']]['shop_name'] ?? '';
                $list[$key]['shop_price'] = $this->dscRepository->getPriceFormat($val['shop_price']);
                $list[$key]['market_price'] = $this->dscRepository->getPriceFormat($val['market_price']);
                $list[$key]['goods_img'] = $this->dscRepository->getImagePath($val['goods_img']);
                $list[$key]['goods_thumb'] = $this->dscRepository->getImagePath($val['goods_thumb']);
                $list[$key]['team_price'] = $this->dscRepository->getPriceFormat($val['team_price']);
                $list[$key]['team_num'] = $val['team_num'];
                if ($val['is_audit'] == 1) {
                    $is_audit = lang('admin/team.refuse_audit');
                } elseif ($val['is_audit'] == 2) {
                    $is_audit = lang('admin/team.already_audit');
                } else {
                    $is_audit = lang('admin/team.no_audit');
                }
                $list[$key]['is_audit'] = $is_audit;
                $list[$key]['limit_num'] = $val['limit_num'];
                if (config('shop.virtual_limit_nim') == 1) {
                    $list[$key]['limit_num'] = $val['limit_num'] + $val['virtual_num'];
                }
            }
        }

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 团队信息列表
     *
     * @param array $offset
     * @param array $where
     * @return array
     * @throws \Exception
     */
    public function getTeamInfo($offset = [], $where = [])
    {
        $time = TimeRepository::getGmTime();

        $model = TeamLog::where('is_show', 1);

        $model = $model->whereHasIn(
            'getTeamGoods',
            function ($query) use ($where) {
                $time = $where['time'];
                switch ($where['status']) {
                    case '2'://拼团中
                        $query = $query->whereRaw("$time < (start_time + validity_time * 3600)")
                            ->where('is_show', 1)
                            ->where('status', 0)
                            ->where('is_team', 1);
                        break;
                    case '3'://成功团
                        $query = $query->where('status', 1)->where('is_show', 1);
                        break;
                    case '4'://失败团
                        $query = $query->where('status', 0)->where('is_show', 1);
                        $query->where(function ($query) use ($time) {
                            $query->whereRaw("$time > (start_time + validity_time * 3600)")->orWhere('is_team', '<>', 1);
                        });
                        break;
                }
                if (!empty($where['goods_name'])) {
                    $query->whereHasIn('getGoods', function ($query) use ($where) {
                        $query->where('goods_name', 'like', '%' . $where['goods_name'] . '%');
                    });
                }
            }
        );

        $model = $model->with([
            'getTeamGoods' => function ($query) {
                $query->select('id', 'validity_time', 'team_num', 'team_price', 'limit_num', 'is_team');
            }
        ]);

        $total = $model->count();

        $list = $model->offset($offset['start'])
            ->limit($offset['limit'])
            ->orderBy('start_time', 'DESC');

        $list = BaseRepository::getToArrayGet($list);

        $timeFormat = config('shop.time_format');
        if ($list) {

            $goods_id = BaseRepository::getKeyPluck($list, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'user_id', 'goods_sn', 'goods_name', 'shop_price', 'market_price', 'goods_number', 'sales_volume', 'goods_img', 'goods_thumb', 'is_best', 'is_new', 'is_hot']);

            $ru_id = BaseRepository::getKeyPluck($goodsList, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($list as $key => $val) {
                $val = array_merge($val, $val['get_team_goods']);

                $goods = $goodsList[$val['goods_id']] ?? [];
                $val = BaseRepository::getArrayMerge($val, $goods);

                $list[$key]['start_time'] = TimeRepository::getLocalDate($timeFormat, $val['start_time']);
                $list[$key]['shop_price'] = $this->dscRepository->getPriceFormat($val['shop_price']);
                $list[$key]['goods_name'] = $val['goods_name'];
                $list[$key]['goods_thumb'] = $this->dscRepository->getImagePath($val['goods_thumb']);
                $list[$key]['user_name'] = $merchantList[$val['user_id']]['shop_name'] ?? '';
                $list[$key]['surplus'] = $val['team_num'] - $this->getSurplusNum($val['team_id']);//还差几人

                //团状态
                if ($val['status'] != 1 && $time < ($val['start_time'] + ($val['validity_time'] * 3600)) && $val['is_team'] == 1) {//进项中
                    $list[$key]['status'] = lang('admin/team.team_ing');
                } elseif ($val['status'] != 1 && ($time > ($val['start_time'] + ($val['validity_time'] * 3600)) || $val['is_team'] != 1)) {//失败
                    $list[$key]['status'] = lang('admin/team.team_fail');
                } elseif ($val['status'] == 1) {//成功
                    $list[$key]['status'] = lang('admin/team.team_success');
                }
                //剩余时间
                $endtime = $val['start_time'] + $val['validity_time'] * 3600;
                $cle = $endtime - $time; //得出时间戳差值
                $d = floor($cle / 3600 / 24);
                $h = floor(($cle % (3600 * 24)) / 3600);
                $m = floor((($cle % (3600 * 24)) % 3600) / 60);
                $list[$key]['time'] = $d . lang('admin/team.day') . $h . lang('admin/team.hour') . $m . lang('admin/team.minute');
                $list[$key]['cle'] = $cle;
                unset($list[$key]['get_team_goods']);
                unset($list[$key]['get_goods']);
            }
        }


        return ['list' => $list, 'total' => $total];
    }

    /**
     * 团队订单列表
     *
     * @param array $offset
     * @param int $team_id
     * @return array
     * @throws \Exception
     */
    public function getTeamOrder($offset = [], $team_id = 0)
    {
        $os = lang('admin/team.os');
        $ps = lang('admin/team.ps');
        $ss = lang('admin/team.ss');

        $list = OrderInfo::where('extension_code', 'team_buy')
            ->where('team_id', $team_id);

        $list = $list->with([
            'getRegionProvince' => function ($query) {
                $query->select('region_id', 'region_name');
            },
            'getRegionCity' => function ($query) {
                $query->select('region_id', 'region_name');
            },
            'getRegionDistrict' => function ($query) {
                $query->select('region_id', 'region_name');
            },
            'getRegionStreet' => function ($query) {
                $query->select('region_id', 'region_name');
            }
        ]);

        $total = $list->count();

        $list = $list->offset($offset['start'])
            ->limit($offset['limit'])
            ->orderBy('order_id', 'DESC');

        $list = BaseRepository::getToArrayGet($list);

        $timeFormat = config('shop.time_format');
        if ($list) {

            $order_id = BaseRepository::getKeyPluck($list, 'order_id');
            $orderGoodsList = OrderGoodsDataHandleService::orderGoodsDataList([], ['rec_id', 'order_id', 'goods_id', 'goods_name', 'ru_id'], 0, $order_id);

            $ru_id = BaseRepository::getKeyPluck($list, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($list as $key => $val) {

                $sql = [
                    'where' => [
                        [
                            'name' => 'order_id',
                            'value' => $val['order_id']
                        ]
                    ]
                ];
                $orderGoods = BaseRepository::getArraySqlFirst($orderGoodsList, $sql);

                $val = BaseRepository::getArrayMerge($val, $orderGoods);
                $list[$key] = $val;

                /* 取得区域名 */
                $province = $val['get_region_province']['region_name'] ?? '';
                $city = $val['get_region_city']['region_name'] ?? '';
                $district = $val['get_region_district']['region_name'] ?? '';
                $street = $val['get_region_street']['region_name'] ?? '';
                $list[$key]['region'] = $province . ' ' . $city . ' ' . $district . ' ' . $street;
                $list[$key]['add_time'] = TimeRepository::getLocalDate($timeFormat, $val['add_time']);
                $list[$key]['formated_order_amount'] = $this->dscRepository->getPriceFormat($val['order_amount']);
                $list[$key]['formated_total_fee'] = $this->dscRepository->getPriceFormat($val['goods_amount']);
                $list[$key]['user_name'] = $merchantList[$val['ru_id']]['shop_name'] ?? '';
                $list[$key]['status'] = $os[$val['order_status']] . ',' . $ps[$val['pay_status']] . ',' . $ss[$val['shipping_status']];

                if (config('shop.show_mobile') == 0) {
                    $list[$key]['mobile'] = $this->dscRepository->stringToStar($list[$key]['mobile']);
                    $list[$key]['tel'] = $this->dscRepository->stringToStar($list[$key]['tel']);
                    $list[$key]['email'] = $this->dscRepository->stringToStar($list[$key]['email']);
                    $list[$key]['user_name'] = $this->dscRepository->stringToStar($list[$key]['user_name']);
                }
            }
        }

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 统计该拼团已参与人数
     * @param int $team_id
     * @param int $failure 0 正在拼团中，2 失败团
     * @return int
     */
    public function getSurplusNum($team_id = 0, $failure = 0)
    {
        $res = OrderInfo::where('team_id', $team_id)
            ->where('extension_code', 'team_buy');

        if ($failure == 2) {
            $res = $res->where(function ($query) {
                $query->where('pay_status', PS_PAYED)
                    ->orWhere('order_status', 4);
            });
        }

        return $res->count();
    }

    /**
     * 获取频道下商品数量
     * @param int $tc_id
     * @return int
     */
    public function getCategroyNumber($tc_id = 0)
    {
        $model = TeamGoods::where('is_team', 1);

        if ($tc_id > 0) {
            $tc_arr = $this->getCategroyId($tc_id);
            $model = $model->whereIn('tc_id', $tc_arr);
        }
        $goods_number = $model->count();

        return $goods_number;
    }

    /**
     * 获取频道id
     * @param int $tc_id
     * @return array
     */
    public function getCategroyId($tc_id = 0)
    {
        $res = TeamCategory::select('id')
            ->where('id', $tc_id)
            ->orWhere('parent_id', $tc_id);

        $res = BaseRepository::getToArrayGet($res);

        $categroy = [];
        if ($res) {
            $categroy = BaseRepository::getFlatten($res);
        } else {
            $categroy[] = $tc_id;
        }

        return $categroy;
    }

    /**
     * 获取拼团频道树形
     * @param int $tree_id
     * @return array
     */
    public function teamGetTree($tree_id = 0)
    {
        $three_arr = [];

        $count = TeamCategory::where('parent_id', 0)->where('status', 1)->count();
        if ($count > 0 || $tree_id == 0) {
            $res = TeamCategory::where('parent_id', $tree_id)
                ->where('status', 1)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC');

            $res = BaseRepository::getToArrayGet($res);

            if ($res) {
                foreach ($res as $k => $row) {
                    $three_arr[$k]['tc_id'] = $row['id'];
                    $three_arr[$k]['name'] = $row['name'];
                    $child_tree = $this->teamGetTree($row['id']);
                    if ($child_tree) {
                        $three_arr[$k]['id'] = $child_tree;
                    }
                }
            }
        }

        return $three_arr;
    }
}
