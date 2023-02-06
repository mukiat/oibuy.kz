<?php

namespace App\Services\Seckill;

use App\Models\Seckill;
use App\Models\SeckillGoods;
use App\Models\SeckillTimeBucket;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;

class SeckillInsertService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    ) {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 首页秒杀活动
     *
     * @param array $seckillid
     * @param string $temp
     * @return bool
     */
    public function insertIndexSeckillGoods($seckillid = [], $temp = '')
    {
        $need_cache = $GLOBALS['smarty']->caching;
        $need_compile = $GLOBALS['smarty']->force_compile;

        $GLOBALS['smarty']->caching = false;
        $GLOBALS['smarty']->force_compile = true;

        $seckill_goods = $this->getIndexSeckillGoods($seckillid);

        if ($seckill_goods) {
            $GLOBALS['smarty']->assign('seckill_goods', $seckill_goods);       //秒杀活动
            $GLOBALS['smarty']->assign('url_seckill', $this->dscRepository->setRewriteUrl('seckill.php'));
        }

        //可视化标识
        if ($temp) {
            $GLOBALS['smarty']->assign('ajax_seckill', 1);
        }

        $val = $GLOBALS['smarty']->fetch('library/seckill_goods_list.lbi');

        $GLOBALS['smarty']->caching = $need_cache;
        $GLOBALS['smarty']->force_compile = $need_compile;

        return $val;
    }


    /**
     * 获取首页秒杀活动商品
     *
     * @param array $seckillid
     * @return array
     */
    public function getIndexSeckillGoods($seckillid = [])
    {
        $begin_time_format = '';
        $end_time_format = '';
        $now = TimeRepository::getGmTime();
        $date_begin = TimeRepository::getLocalStrtoTime(TimeRepository::getLocalDate('Ymd'));

        $seckill = Seckill::selectRaw('GROUP_CONCAT(sec_id) AS sec_ids')
            ->where('begin_time', '<=', $date_begin)
            ->where('acti_time', '>=', $date_begin)
            ->where('review_status', 3);
        $seckill = BaseRepository::getToArrayFirst($seckill);

        $sec_id = $seckill ? explode(",", $seckill['sec_ids']) : [];

        $seckillWhere = [
            'date_begin' => $date_begin,
            'sec_id' => $sec_id
        ];
        $res = SeckillGoods::whereHasIn('getSeckillTimeBucket')
            ->whereHasIn('getGoods')
            ->whereHasIn('getSeckill', function ($query) use ($seckillWhere) {
                $query->where('is_putaway', 1)
                    ->where('review_status', 3)
                    ->where('begin_time', '<=', $seckillWhere['date_begin'])
                    ->where('acti_time', '>=', $seckillWhere['date_begin'])
                    ->whereIn('sec_id', $seckillWhere['sec_id']);
            });

        $res = $res->with([
            'getSeckillTimeBucket' => function ($query) {
                $query->selectRaw('id, begin_time, end_time, id AS stb_id');
            },
            'getSeckill' => function ($query) {
                $query->selectRaw('sec_id, acti_title, acti_time');
            },
            'getGoods' => function ($query) {
                $query->selectRaw('goods_id, goods_thumb, shop_price, market_price, goods_name');
            },
            'getSeckillGoodsAttr' => function ($query) {
                $query->select('id', 'seckill_goods_id', 'product_id', 'sec_price', 'sec_num', 'sec_limit');
            }
        ]);

        $res = BaseRepository::getToArrayGet($res);

        $soon = [];
        if ($res) {
            foreach ($res as $key => $row) {
                unset($row['get_seckill_time_bucket']['id']);
                $row = $row['get_seckill_time_bucket'] ? array_merge($row, $row['get_seckill_time_bucket']) : $row;
                $row = $row['get_seckill'] ? array_merge($row, $row['get_seckill']) : $row;
                $row = $row['get_goods'] ? array_merge($row, $row['get_goods']) : $row;

                $get_seckill_goods_attr = $row['get_seckill_goods_attr'] ?? [];
                unset($seckill['get_seckill_goods_attr']);
                if (!empty($get_seckill_goods_attr)) {
                    // 有秒杀属性取最小属性价、数量
                    $get_seckill_goods_attr = collect($get_seckill_goods_attr)->sortBy('sec_price')->first();
                    $row['sec_price'] = $get_seckill_goods_attr['sec_price'] ?? 0;
                    $row['sec_num'] = $get_seckill_goods_attr['sec_num'] ?? 0;
                    $row['sec_limit'] = $get_seckill_goods_attr['sec_limit'] ?? 0;
                }

                $res[$key] = $row;
            }

            $res = BaseRepository::getSortBy($res, 'begin_time');
        }

        $time = SeckillTimeBucket::selectRaw('MIN(begin_time) AS begin_time, MAX(end_time) AS end_time')->whereRaw(1);
        $time = BaseRepository::getToArrayFirst($time);

        $min_time = $time ? $time['begin_time'] : '';
        $max_time = $time ? $time['end_time'] : '';

        if ($res) {
            foreach ($res as $k => $v) {
                $begin_time = TimeRepository::getLocalStrtoTime($v['begin_time']);
                $end_time = TimeRepository::getLocalStrtoTime($v['end_time']);
                if (($begin_time > $now) || ($end_time < $now)) {
                    if ($min_time && $max_time && $v['begin_time'] == $min_time && $max_time > $now) {
                        $soon[$k] = $res[$k];
                        $begin_time_format = TimeRepository::getLocalDate("Y-m-d H:i:s", $begin_time);
                    }
                    unset($res[$k]);
                } else {
                    $end_time_format = TimeRepository::getLocalDate("Y-m-d H:i:s", $end_time);
                }
            }
        }

        if (empty($end_time_format)) {
            $GLOBALS['smarty']->assign('sec_begin_time', $begin_time_format);
        } else {
            $GLOBALS['smarty']->assign('sec_end_time', $end_time_format);
        }

        if ($res) {
            foreach ($res as $k => $v) {
                $i = true;
                $goods_ids = isset($seckillid[$v['stb_id']]) ? $seckillid[$v['stb_id']] : '';
                if ($goods_ids) {
                    $goods_ids = explode(',', $goods_ids);
                    if (!empty($goods_ids)) {
                        if (!in_array($v['id'], $goods_ids)) {
                            $i = false;
                            unset($res[$k]);
                        }
                    }
                }

                if ($i) {
                    $res[$k]['sec_price'] = $this->dscRepository->getPriceFormat($v['sec_price']);
                    $res[$k]['market_price'] = $this->dscRepository->getPriceFormat($v['market_price']);
                    $res[$k]['url'] = $this->dscRepository->buildUri('seckill', ['act' => "view", 'secid' => $v['id']], $v['goods_name']);
                    $res[$k]['list_url'] = $this->dscRepository->buildUri('seckill', ['act' => "list", 'secid' => $v['id']], $v['goods_name']);
                    $res[$k]['goods_thumb'] = $this->dscRepository->getImagePath($v['goods_thumb']);
                }
            }
            return $res;
        } else {
            if ($soon) {
                foreach ($soon as $k => $v) {
                    $i = true;
                    $goods_ids = $seckillid[$v['stb_id']];
                    if ($goods_ids) {
                        $goods_ids = explode(',', $goods_ids);
                        if (!empty($goods_ids)) {
                            if (!in_array($v['id'], $goods_ids)) {
                                $i = false;
                                unset($res[$k]);
                            }
                        }
                    }
                    if ($i) {
                        $soon[$k]['sec_price'] = $this->dscRepository->getPriceFormat($v['sec_price']);
                        $soon[$k]['market_price'] = $this->dscRepository->getPriceFormat($v['market_price']);
                        $soon[$k]['url'] = $this->dscRepository->buildUri('seckill', ['act' => "view", 'secid' => $v['id']], $v['goods_name']);
                        $res[$k]['list_url'] = $this->dscRepository->buildUri('seckill', ['act' => "list", 'secid' => $v['id']], $v['goods_name']);
                        $res[$k]['goods_thumb'] = $this->dscRepository->getImagePath($v['goods_thumb']);
                    }
                }
            }
            return $soon;
        }
    }
}
