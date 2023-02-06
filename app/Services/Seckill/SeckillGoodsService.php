<?php

namespace App\Services\Seckill;

use App\Models\OrderGoods;
use App\Models\Seckill;
use App\Models\SeckillGoods;
use App\Models\SeckillGoodsRemind;
use App\Models\SeckillTimeBucket;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Category\CategoryService;
use App\Services\Goods\GoodsDataHandleService;

class SeckillGoodsService
{
    protected $categoryService;
    protected $dscRepository;

    public function __construct(
        CategoryService $categoryService,
        DscRepository $dscRepository
    )
    {
        $this->categoryService = $categoryService;
        $this->dscRepository = $dscRepository;
    }

    /**
     * 秒杀活动列表页
     *
     * @return array
     * @throws \Exception
     */
    public function seckillGoodsList()
    {
        $now = TimeRepository::getGmTime();
        $day = 24 * 60 * 60;
        $sec_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        /* 取得秒杀活动商品ID */
        $goods_id = SeckillGoods::where('id', $sec_id)->value('goods_id');

        /* 取得秒杀活动用户设置提醒商品ID */
        $user_id = session('user_id');
        $beginYesterday = TimeRepository::getLocalMktime(0, 0, 0, TimeRepository::getLocalDate('m'), TimeRepository::getLocalDate('d') - 1, TimeRepository::getLocalDate('Y'));

        $row = SeckillGoodsRemind::select('sec_goods_id')
            ->where('user_id', $user_id)
            ->where('add_time', '>', $beginYesterday);

        $row = BaseRepository::getToArrayGet($row);
        $sec_goods_ids = BaseRepository::getKeyPluck($row, 'sec_goods_id');

        $date_begin = TimeRepository::getLocalStrtoTime(TimeRepository::getLocalDate('Ymd'));
        $date_next = TimeRepository::getLocalStrtoTime(TimeRepository::getLocalDate('Ymd')) + $day;

        $cat_id = isset($_GET['cat_id']) && !empty($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;

        $res = $this->seckillGoodsResults($date_begin, $cat_id);
        $res_tmr = $this->seckillGoodsResults($date_next, $cat_id, $day);

        $stb = SeckillTimeBucket::whereRaw(1)
            ->orderBy('begin_time');

        $stb = BaseRepository::getToArrayGet($stb);

        $sec_id_today = Seckill::select('sec_id')->where('begin_time', '<=', $date_begin)
            ->where('acti_time', '>', $date_begin)
            ->where('is_putaway', 1);
        $sec_id_today = BaseRepository::getToArrayGet($sec_id_today);
        $sec_id_today = BaseRepository::getKeyPluck($sec_id_today, 'sec_id');

        $arr = [];
        if ($stb) {
            foreach ($stb as $k => $v) {
                $v['local_end_time'] = TimeRepository::getLocalStrtoTime($v['end_time']);
                if ($v['local_end_time'] > $now && $sec_id_today) {
                    $arr[$k]['title'] = $v['title'];
                    $arr[$k]['status'] = false;
                    $arr[$k]['is_end'] = false;
                    $arr[$k]['soon'] = false;
                    $arr[$k]['begin_time'] = $begin_time = TimeRepository::getLocalStrtoTime($v['begin_time']);
                    $arr[$k]['end_time'] = $end_time = TimeRepository::getLocalStrtoTime($v['end_time']);
                    if ($begin_time < $now && $end_time > $now) {
                        $arr[$k]['status'] = true;
                    }
                    if ($end_time < $now) {
                        $arr[$k]['is_end'] = true;
                    }
                    if ($begin_time > $now) {
                        $arr[$k]['soon'] = true;
                    }
                }
            }

            if (count($arr) < 4 && count($res_tmr) > 0) {
                foreach ($stb as $k => $v) {
                    $arr['tmr' . $k]['title'] = $v['title'];
                    $arr['tmr' . $k]['status'] = false;
                    $arr['tmr' . $k]['is_end'] = false;
                    $arr['tmr' . $k]['soon'] = true;
                    $arr['tmr' . $k]['begin_time'] = TimeRepository::getLocalStrtoTime($v['begin_time']) + $day;
                    $arr['tmr' . $k]['end_time'] = TimeRepository::getLocalStrtoTime($v['end_time']) + $day;
                }
            }
        }

        if ($arr) {
            foreach ($arr as $k => $v) {
                if ($res) {
                    $arr1 = $arr2 = [];
                    foreach ($res as $val) {
                        if ($v['end_time'] > $now && $val['begin_time'] == $v['begin_time']) {
                            if ($goods_id == $val['goods_id'] || in_array($val['id'], $sec_goods_ids)) {//把设置提醒的商品筛选出来
                                $arr1[$val['goods_id']] = $val;
                                if (in_array($val['id'], $sec_goods_ids)) {//设置过提醒的商品标识
                                    $arr1[$val['goods_id']]['is_remind'] = 1;
                                }
                            } else {
                                $arr2[$val['goods_id']] = $val;
                            }
                        }
                    }

                    if ($arr1) {
                        $arr[$k]['goods'] = array_merge($arr1, $arr2);
                    } else {
                        $arr[$k]['goods'] = $arr2;
                    }

                    unset($arr1, $arr2);
                }

                if (substr($k, 0, 3) == 'tmr') {
                    if ($res_tmr) {
                        $arr1 = $arr2 = [];
                        foreach ($res_tmr as $val) {
                            if ($val['begin_time'] == $v['begin_time']) {
                                if (in_array($val['id'], $sec_goods_ids)) {//把设置提醒的商品筛选出来
                                    $arr1[$val['goods_id']] = $val;
                                    $arr1[$val['goods_id']]['is_remind'] = 1;
                                } else {
                                    $arr2[$val['goods_id']] = $val;
                                }
                                $arr[$k]['tomorrow'] = 1;
                            }
                        }
                        if ($arr1) {
                            $arr[$k]['goods'] = array_merge($arr1, $arr2);
                        } else {
                            $arr[$k]['goods'] = $arr2;
                        }
                        unset($arr1, $arr2);
                    }
                }
            }
        }

        return $arr;
    }

    /**
     * 秒杀日期内的商品
     *
     * @param string $date
     * @param int $cat_id
     * @param int $day
     * @return mixed
     * @throws \Exception
     */
    public function seckillGoodsResults($date = '', $cat_id = 0, $day = 0)
    {
        $date_begin = TimeRepository::getLocalDate('Ymd');
        $date_begin = TimeRepository::getLocalStrtoTime($date_begin) + $day;

        $seckill = Seckill::select("sec_id")
            ->where('begin_time', '<=', $date_begin)
            ->where('acti_time', '>', $date_begin);

        $seckill = BaseRepository::getToArrayGet($seckill);
        $seckill = BaseRepository::getKeyPluck($seckill, 'sec_id');

        $res = SeckillGoods::select('id', 'goods_id', 'tb_id', 'sec_id', 'sec_price', 'sec_num', 'sec_limit', 'sales_volume');
        $res = $res->with([
            'getSeckillGoodsAttr' => function ($query) {
                $query->select('id', 'seckill_goods_id', 'product_id', 'sec_price', 'sec_num', 'sec_limit');
            }
        ]);
        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $sec_id = BaseRepository::getKeyPluck($res, 'sec_id');

            $seckillRes = Seckill::where('is_putaway', 1)
                ->where('review_status', 3)
                ->where('begin_time', '<=', $date_begin)
                ->where('acti_time', '>=', $date);

            if ($seckill) {
                $seckillRes = $seckillRes->whereIn('sec_id', $seckill);
            }

            $seckillRes = $seckillRes->whereIn('sec_id', $sec_id);

            $seckillRes = BaseRepository::getToArrayGet($seckillRes);
            $sec_id = BaseRepository::getKeyPluck($seckillRes, 'sec_id');

            if ($sec_id) {
                $sql = [
                    'whereIn' => [
                        [
                            'name' => 'sec_id',
                            'value' => $sec_id
                        ]
                    ]
                ];
                $res = BaseRepository::getArraySqlGet($res, $sql);
            } else {
                $res = [];
            }

            if ($res) {
                $sec_id = BaseRepository::getKeyPluck($seckillRes, 'sec_id');
                $seckillList = SeckillDataHandleService::getSeckillDataList($sec_id, ['sec_id', 'acti_title', 'acti_time']);

                $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');
                $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'cat_id', 'goods_thumb', 'shop_price', 'market_price', 'goods_name']);

                if ($cat_id) {
                    $children = $this->categoryService->getCatListChildren($cat_id);
                    if ($children) {
                        $sql = [
                            'whereIn' => [
                                [
                                    'name' => 'cat_id',
                                    'value' => $children
                                ]
                            ]
                        ];
                        $goodsList = BaseRepository::getArraySqlGet($goodsList, $sql, 1);
                    }
                }

                $goods_id = BaseRepository::getKeyPluck($goodsList, 'goods_id');
                if ($goods_id) {
                    $sql = [
                        'whereIn' => [
                            [
                                'name' => 'goods_id',
                                'value' => $goods_id
                            ]
                        ]
                    ];
                    $res = BaseRepository::getArraySqlGet($res, $sql);
                } else {
                    $res = [];
                }
            }

            if ($res) {

                $tb_id = BaseRepository::getKeyPluck($res, 'tb_id');
                $timeBucketList = SeckillDataHandleService::getSeckillTimeBucketDataList($tb_id, ['id', 'begin_time', 'end_time']);

                foreach ($res as $k => $v) {

                    $seckill = $seckillList[$v['sec_id']] ?? [];
                    $goods = $goodsList[$v['goods_id']] ?? [];

                    $time_bucket = $timeBucketList[$v['tb_id']] ?? [];

                    $v = BaseRepository::getArrayMerge($v, $goods);
                    $v = BaseRepository::getArrayMerge($v, $seckill);

                    $v['begin_time'] = $time_bucket['begin_time'] ?? '';
                    $v['end_time'] = $time_bucket['end_time'] ?? '';

                    $res[$k] = $v;

                    $get_seckill_goods_attr = $v['get_seckill_goods_attr'] ?? [];
                    unset($res[$k]['get_seckill_goods_attr']);
                    if (!empty($get_seckill_goods_attr)) {
                        // 有秒杀属性取最小属性价、数量
                        $get_seckill_goods_attr = collect($get_seckill_goods_attr)->sortBy('sec_price')->first();
                        $res[$k]['sec_price'] = $v['sec_price'] = $get_seckill_goods_attr['sec_price'] ?? 0;
                        $res[$k]['sec_num'] = $v['sec_num'] = $get_seckill_goods_attr['sec_num'] ?? 0;
                        $res[$k]['sec_limit'] = $v['sec_limit'] = $get_seckill_goods_attr['sec_limit'] ?? 0;
                    }

                    $res[$k]['begin_time'] = TimeRepository::getLocalStrtoTime($v['begin_time']) + $day;
                    $res[$k]['end_time'] = TimeRepository::getLocalStrtoTime($v['end_time']) + $day;
                    $res[$k]['sec_price_formated'] = $this->dscRepository->getPriceFormat($v['sec_price']);
                    $res[$k]['market_price_formated'] = $this->dscRepository->getPriceFormat($v['market_price']);
                    // 秒杀进度
                    $percent = 100; // 初始化
                    if ($v['sec_num'] > 0) {
                        $percent = ($v['sales_volume'] < $v['sec_num']) ? ceil($v['sales_volume'] / $v['sec_num'] * 100) : 100;
                    }
                    $res[$k]['percent'] = $percent;

                    if ($day > 0) {
                        $res[$k]['url'] = $this->dscRepository->buildUri('seckill', ['act' => "view", 'secid' => $v['id'], 'tmr' => 1], $v['goods_name']);
                        $res[$k]['percent'] = 0;
                    } else {
                        $res[$k]['url'] = $this->dscRepository->buildUri('seckill', ['act' => "view", 'secid' => $v['id']], $v['goods_name']);
                    }

                    $res[$k]['goods_thumb'] = $this->dscRepository->getImagePath($v['goods_thumb']);
                }
            }

            $res = BaseRepository::getSortBy($res, 'goods_id', 'DESC');
            $res = BaseRepository::getSortBy($res, 'begin_time');
        }

        return $res;
    }

    /**
     * 取得秒杀活动商品统计信息
     *
     * @param int $sec_goods_id
     * @return mixed
     */
    public function secGoodsStats($sec_goods_id = 0)
    {
        $sec_goods_id = intval($sec_goods_id);

        /* 取得秒杀活动商品ID */
        $goods_id = SeckillGoods::where('id', $sec_goods_id)->value('goods_id');

        /* 取得总订单数和总商品数 */
        $stat = OrderGoods::selectRaw("COUNT(*) AS total_order, SUM(goods_number) AS total_goods")
            ->where('extension_code', 'seckill' . $sec_goods_id)
            ->where('goods_id', $goods_id);

        $where = [
            'order_status' => [OS_UNCONFIRMED, OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART]
        ];
        $stat = $stat->whereHasIn('getOrder', function ($query) use ($where) {
            $query->whereIn('order_status', $where['order_status'])
                ->where('pay_status', PS_PAYED);
        });

        $stat = BaseRepository::getToArrayFirst($stat);

        if ($stat['total_order'] == 0) {
            $stat['total_goods'] = 0;
        }

        $stat['valid_order'] = $stat['total_order'];
        $stat['valid_goods'] = $stat['total_goods'];

        return $stat;
    }
}