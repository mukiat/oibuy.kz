<?php

namespace App\Services\Activity;

use App\Models\Cart;
use App\Models\CollectGoods;
use App\Models\Seckill;
use App\Models\SeckillGoods;
use App\Models\SeckillGoodsAttr;
use App\Models\SeckillTimeBucket;
use App\Models\TouchAdPosition;
use App\Repositories\Activity\SeckillRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Ads\AdsService;
use App\Services\Cart\CartCommonService;
use App\Services\Common\ConfigService;
use App\Services\Goods\GoodsAttrService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Goods\GoodsGalleryService;
use App\Services\Goods\GoodsService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Order\OrderGoodsService;
use App\Services\Seckill\SeckillGoodsService;


/**
 * Class SeckillService
 * @package App\Services\Activity
 */
class SeckillService
{
    protected $goodsAttrService;
    protected $adsService;
    protected $dscRepository;
    protected $merchantCommonService;
    protected $goodsGalleryService;
    protected $orderGoodsService;
    protected $cartCommonService;
    protected $seckillGoodsService;

    public function __construct(
        GoodsAttrService $goodsAttrService,
        AdsService $adsService,
        DscRepository $dscRepository,
        MerchantCommonService $merchantCommonService,
        GoodsGalleryService $goodsGalleryService,
        OrderGoodsService $orderGoodsService,
        CartCommonService $cartCommonService,
        SeckillGoodsService $seckillGoodsService
    )
    {
        $this->goodsAttrService = $goodsAttrService;
        $this->adsService = $adsService;
        $this->dscRepository = $dscRepository;
        $this->merchantCommonService = $merchantCommonService;
        $this->goodsGalleryService = $goodsGalleryService;
        $this->orderGoodsService = $orderGoodsService;
        $this->cartCommonService = $cartCommonService;
        $this->seckillGoodsService = $seckillGoodsService;
    }

    /**
     * 取得秒杀活动商品详情
     *
     * @param int $uid
     * @param int $seckill_id
     * @param int $tomorrow
     * @return array
     * @throws \Exception
     */
    public function seckill_detail_mobile($uid = 0, $seckill_id = 0, $tomorrow = 0)
    {
        $seckill = $this->seckill_info_mobile($seckill_id, $tomorrow);

        if ($seckill) {
            $seckill['goods_thumb'] = $this->dscRepository->getImagePath($seckill['goods_thumb']);
            $seckill['goods_img'] = $this->dscRepository->getImagePath($seckill['goods_img']);

            // 商品相册
            $seckill['pictures'] = $this->goodsGalleryService->getGoodsGallery($seckill['goods_id']);

            /*获取商品规格与属性*/
            $properties = $this->goodsAttrService->getGoodsProperties($seckill['goods_id']);

            // 获得商品的属性
            $seckill['attr'] = $properties['spe'] ?? [];
            $seckill['attr_name'] = '';
            $seckill['goods_attr_id'] = '';
            if (!empty($seckill['attr'])) {
                foreach ($seckill['attr'] as $k => $v) {
                    // 不显示规格
                    if ($v['attr_type'] == 0) {
                        unset($seckill['attr'][$k]);
                        continue;
                    }

                    $select_key = 0;
                    foreach ($v['values'] as $key => $val) {
                        if ($val['attr_checked'] == 1) {
                            $select_key = $key;
                            break;
                        }
                    }

                    //默认选择第一个属性为checked
                    if ($select_key == 0) {
                        $seckill['attr'][$k]['values'][0]['attr_checked'] = 1;
                    }
                    if ($seckill['attr_name']) {
                        $seckill['attr_name'] = $seckill['attr_name'] . '' . $v['values'][$select_key]['attr_value'];
                        $seckill['goods_attr_id'] = $seckill['goods_attr_id'] . ',' . $v['values'][$select_key]['goods_attr_id'];
                    } else {
                        $seckill['attr_name'] = $v['values'][$select_key]['attr_value'];
                        $seckill['goods_attr_id'] = $v['values'][$select_key]['goods_attr_id'];
                    }
                    $attr_str[] = $v['values'][$select_key]['goods_attr_id'];
                }

                $seckill['attr'] = collect($seckill['attr'])->values()->all();

                foreach ($seckill['attr'] as $key => $value) {
                    sort($value['values']);
                    $seckill['attr'][$key]['attr_key'] = $value['values'];
                    unset($seckill['attr'][$key]['values']);
                }
            }

            /* 获取商品规格参数 */
            $seckill['attr_parameter'] = [];
            if (!empty($properties['pro'])) {
                $properties['pro'] = array_values($properties['pro']);
                $properties['pro'] = BaseRepository::getArrayCollapse($properties['pro']);

                foreach ($properties['pro'] as $key => $val) {
                    $seckill['attr_parameter'][$key]['attr_name'] = $val['name'];
                    $seckill['attr_parameter'][$key]['attr_value'] = $val['value'];
                }
            }

            if (empty($seckill['desc_mobile']) && !empty($seckill['goods_desc'])) {
                $desc_preg = $this->dscRepository->descImagesPreg($seckill['goods_desc']);
                $seckill['goods_desc'] = $desc_preg['goods_desc'];
            }
            if (!empty($seckill['desc_mobile'])) {
                // 处理手机端商品详情 图片（手机相册图） data/gallery_album/
                $desc_preg = $this->dscRepository->descImagesPreg($seckill['desc_mobile'], 'desc_mobile', 1);
                $seckill['goods_desc'] = $desc_preg['desc_mobile'];
            }

            $start_date = TimeRepository::getLocalStrtoTime($seckill['begin_time']);
            $end_date = TimeRepository::getLocalStrtoTime($seckill['end_time']);
            $order_goods = $this->orderGoodsService->getForPurchasingGoods($start_date, $end_date, $seckill['goods_id'], $uid, 'seckill');

            if ($order_goods) {
                $seckill['order_number'] = $order_goods['goods_number'] ?? 0;
            }

            if ($uid > 0) {
                /*会员关注状态*/
                $collect_goods = CollectGoods::where('user_id', $uid)
                    ->where('goods_id', $seckill['goods_id'])
                    ->count();
                if ($collect_goods > 0) {
                    $seckill['is_collect'] = 1;
                } else {
                    $seckill['is_collect'] = 0;
                }
            } else {
                $seckill['is_collect'] = 0;
            }
        }

        return $seckill;
    }

    /**
     * 取得秒杀活动详情
     *
     * @param int $seckill_goods_id
     * @param int $tomorrow
     * @return array
     * @throws \Exception
     */
    public function seckill_info_mobile($seckill_goods_id = 0, $tomorrow = 0)
    {
        $seckill = SeckillRepository::seckill_detail($seckill_goods_id);

        if (empty($seckill)) {
            return [];
        }

        $source_domestic = ConfigService::searchSourceDomestic();

        if (isset($seckill['get_goods'])) {
            unset($seckill['get_goods']['sales_volume']);
            $seckill = collect($seckill)->merge($seckill['get_goods'])->except('get_goods')->all();
        }
        if (isset($seckill['get_seckill'])) {
            $seckill = collect($seckill)->merge($seckill['get_seckill'])->except('get_seckill')->all();
        }
        if (isset($seckill['get_seckill_time_bucket'])) {
            $seckill = collect($seckill)->merge($seckill['get_seckill_time_bucket'])->except('get_seckill_time_bucket')->all();
        }

        $merchantList = MerchantDataHandleService::getMerchantInfoDataList([$seckill['user_id']]);
        $merchant = $merchantList[$seckill['user_id']] ?? [];

        $seckill['country_name'] = $merchant['country_name'] ?? '';
        $seckill['country_icon'] = $merchant['country_icon'] ?? '';
        $seckill['cross_warehouse_name'] = $merchant['cross_warehouse_name'] ?? '';

        $now = $time = TimeRepository::getGmTime();
        $tmr = 0;
        if ($tomorrow == 1) {
            $tmr = 86400;
        }
        $begin_time = TimeRepository::getLocalStrtoTime($seckill['begin_time']) + $tmr;
        $end_time = TimeRepository::getLocalStrtoTime($seckill['end_time']) + $tmr;

        if ($begin_time < $now && $end_time > $now) {
            $seckill['status'] = true;
        } else {
            $seckill['status'] = false;
        }

        $seckill['is_end'] = $now > $end_time ? 1 : 0;

        $seckill['rz_shop_name'] = $merchant['shop_name'] ?? ''; //店铺名称
        $seckill['rz_shopName'] = $seckill['rz_shop_name'];

        // 格式化时间 如果活动没有开始那么计算的时间是按照开始时间来计算
        if (!$seckill['is_end'] && !$seckill['status']) {
            $end_time = $begin_time;
        }

        /* 格式化时间 */
        $seckill['formated_start_date'] = $begin_time;
        $seckill['formated_end_date'] = $end_time;
        $seckill['current_time'] = $now;

        //秒杀活动已超过结束时间
        if ($seckill['acti_time'] < $now) {
            $seckill['status'] = false;
            $seckill['is_end'] = 1;
        }

        $get_seckill_goods_attr = $seckill['get_seckill_goods_attr'] ?? [];
        unset($seckill['get_seckill_goods_attr']);
        if (!empty($get_seckill_goods_attr)) {
            // 有秒杀属性取最小属性价、数量
            $get_seckill_goods_attr = collect($get_seckill_goods_attr)->sortBy('sec_price')->first();
            $seckill['sec_price'] = $get_seckill_goods_attr['sec_price'] ?? 0;
            $seckill['sec_num'] = $get_seckill_goods_attr['sec_num'] ?? 0;
            $seckill['sec_limit'] = $get_seckill_goods_attr['sec_limit'] ?? 0;
        }

        $seckill['sec_price_format'] = $this->dscRepository->getPriceFormat($seckill['sec_price']);

        $seckill['is_kj'] = 0;
        $seckill['goods_rate'] = 0;
        if (CROSS_BORDER === true) {
            $stepsFieldsList = MerchantDataHandleService::getMerchantsStepsFieldsDataList([$seckill['user_id']], ['user_id', 'source']);
            $source = $stepsFieldsList[$seckill['user_id']]['source'] ?? '';

            if ($source && !in_array($source, [$source_domestic])) {
                $seckill['is_kj'] = 1;
            }

            $seckill['cross_source'] = $source;

            // 跨境多商户
            $cbec = app(\App\Services\CrossBorder\CrossBorderService::class)->cbecExists();

            if (!empty($cbec)) {
                $seckill['goods_rate'] = $cbec->get_goods_rate($seckill['goods_id'], $seckill['sec_price']);
                $seckill['formated_goods_rate'] = $this->dscRepository->getPriceFormat($seckill['goods_rate']);
            }
        }

        return $seckill;
    }

    /**
     * 购买秒杀商品详情
     * @param int $seckill_goods_id
     * @param int $tomorrow
     * @param array $spec
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @return array
     */
    public function buy_seckill_info($seckill_goods_id = 0, $tomorrow = 0, $spec = [], $warehouse_id = 0, $area_id = 0, $area_city = 0)
    {
        $seckill_goods_id = intval($seckill_goods_id);

        if (empty($seckill_goods_id)) {
            return [];
        }

        $seckill = SeckillGoods::where('id', $seckill_goods_id);

        $seckill = $seckill->whereHasIn('getSeckill', function ($query) {
            $query->where('is_putaway', 1)->where('review_status', 3);
        });

        $seckill = $seckill->with([
            'getSeckill',
            'getSeckillTimeBucket' => function ($query) {
                $query->select('id', 'begin_time', 'end_time');
            }
        ]);

        $seckill = BaseRepository::getToArrayFirst($seckill);

        $goodsId = $seckill['goods_id'] ?? 0;
        $goodsList = GoodsDataHandleService::GoodsDataList($goodsId, ['goods_id', 'goods_sn', 'goods_name', 'is_delete', 'goods_number', 'is_on_sale', 'shop_price', 'market_price', 'is_real', 'model_attr', 'user_id', 'is_shipping', 'cost_price', 'freight', 'tid', 'shipping_fee']);

        $sql = [
            'whereIn' => [
                [
                    'name' => 'goods_id',
                    'value' => $goodsId
                ]
            ],
            'where' => [
                [
                    'name' => 'is_delete',
                    'value' => 0
                ],
                [
                    'name' => 'is_on_sale',
                    'value' => 1
                ]
            ]
        ];
        $goods = BaseRepository::getArraySqlFirst($goodsList, $sql);

        if (empty($seckill) || empty($goods)) {
            return [];
        }

        $seckill = BaseRepository::getArrayMerge($seckill, $goods);
        $seckill = BaseRepository::getArrayMerge($seckill, $seckill['get_seckill']);
        $seckill = BaseRepository::getArrayMerge($seckill, $seckill['get_seckill_time_bucket']);
        $seckill = BaseRepository::getArrayExcept($seckill, ['get_seckill', 'get_seckill_time_bucket']);

        $now = $time = TimeRepository::getGmTime();

        $tmr = $tomorrow == 1 ? 86400 : 0;
        $begin_time = TimeRepository::getLocalStrtoTime($seckill['begin_time']) + $tmr;
        $end_time = TimeRepository::getLocalStrtoTime($seckill['end_time']) + $tmr;

        if ($begin_time < $now && $end_time > $now) {
            $seckill['status'] = true;
        } else {
            $seckill['status'] = false;
        }

        $seckill['is_end'] = $now > $end_time ? 1 : 0;
        // 格式化时间 如果活动没有开始那么计算的时间是按照开始时间来计算
        if (!$seckill['is_end'] && !$seckill['status']) {
            $end_time = $begin_time;
        }
        //秒杀活动已超过结束时间
        if ($seckill['acti_time'] < $now) {
            $seckill['status'] = false;
            $seckill['is_end'] = 1;
        }

        /* 格式化时间 */
        $seckill['formated_start_date'] = $begin_time;
        $seckill['formated_end_date'] = $end_time;
        $seckill['current_time'] = $now;

        $seckillCountId = SeckillGoodsAttr::where('seckill_goods_id', $seckill_goods_id)->count('id');

        // 秒杀属性
        $seckill['spec_disable'] = 0;
        if (!empty($spec) && !empty($seckillCountId)) {
            $model_attr = $seckill['model_attr'] ?? 0;
            $products = SeckillRepository::getProduct($seckill['goods_id'], $spec, $warehouse_id, $area_id, $area_city, $model_attr);
            $product_id = $products['product_id'] ?? 0;
            $get_seckill_goods_attr = SeckillRepository::getSeckillGoodsAttrByProductId($seckill_goods_id, $seckill['goods_id'], $product_id);
            if (!empty($get_seckill_goods_attr)) {
                // 有秒杀属性取属性价、数量
                $seckill['sec_price'] = $get_seckill_goods_attr['sec_price'] ?? 0;
                $seckill['sec_num'] = $get_seckill_goods_attr['sec_num'] ?? 0;
                $seckill['sec_limit'] = $get_seckill_goods_attr['sec_limit'] ?? 0;

                if ($products['product_number'] < $seckill['sec_num']) {
                    $seckill['sec_num'] = $products['product_number'];
                }
            } else {
                // 无秒杀属性 禁止下单
                $seckill['spec_disable'] = 1;
            }
        } else {
            // 无属性 秒杀数量大于商品总库存
            if ($goods['goods_number'] < $seckill['sec_num']) {
                $seckill['sec_num'] = $goods['goods_number'];
            }
        }

        $seckill['sec_price_format'] = $this->dscRepository->getPriceFormat($seckill['sec_price']);

        return $seckill;
    }

    /**
     * 秒杀日期内的商品
     * @param int $id
     * @param int $page
     * @param int $size
     * @param int $tomorrow
     * @return mixed
     */
    public function seckill_goods_results($id = 0, $page = 1, $size = 10, $tomorrow = 0)
    {
        if (empty($id)) {
            return [];
        }

        $day = 24 * 60 * 60;
        $date_begin = ($tomorrow == 1) ? TimeRepository::getLocalStrtoTime(TimeRepository::getLocalDate('Ymd')) + $day : TimeRepository::getLocalStrtoTime(TimeRepository::getLocalDate('Ymd'));

        $sec_ids = Seckill::query()->where('begin_time', '<=', $date_begin)->where('acti_time', '>', $date_begin)->pluck('sec_id')->toArray();

        $res = SeckillGoods::select('id', 'tb_id', 'sec_id', 'goods_id', 'sec_price', 'sec_num', 'sec_limit', 'sales_volume')
            ->whereHasIn('getSeckillTimeBucket', function ($query) use ($id) {
                $query->where('id', $id);
            });

        $res = $res->whereHasIn('getSeckill', function ($query) use ($date_begin, $sec_ids) {
            $query->where('is_putaway', 1)
                ->where('review_status', 3)
                ->where('begin_time', '<=', $date_begin)
                ->whereIn('sec_id', $sec_ids);
        });

        $res = $res->whereHasIn('getGoods');

        $res = $res->with([
            'getSeckillTimeBucket' => function ($query) {
                $query->select('id', 'begin_time', 'end_time');
            },
            'getSeckill' => function ($query) {
                $query->select('sec_id', 'acti_title', 'acti_time');
            },
            'getGoods' => function ($query) {
                $query->select('goods_id', 'goods_thumb', 'shop_price', 'market_price', 'goods_name');
            },
            'getSeckillGoodsAttr' => function ($query) {
                $query->select('id', 'seckill_goods_id', 'product_id', 'sec_price', 'sec_num', 'sec_limit');
            }
        ]);

        $res = $res->withCount([
            'getSeckill as begin_time' => function ($query) {
                $query->select('begin_time');
            }
        ]);

        $start = ($page - 1) * $size;
        $res = $res->offset($start)
            ->limit($size)
            ->orderBy('goods_id', 'DESC')
            ->orderBy('begin_time', 'ASC');

        $res = BaseRepository::getToArrayGet($res);

        $now = $time = TimeRepository::getGmTime();
        $tmr = 86400;

        if ($res) {
            foreach ($res as $k => $v) {

                /* 删除冲突ID */
                if ($v['get_seckill_time_bucket']) {
                    unset($v['get_seckill_time_bucket']['id']);
                }

                $v = BaseRepository::getArrayCollapse([$v, $v['get_seckill_time_bucket'], $v['get_seckill'], $v['get_goods']]);
                $v = BaseRepository::getArrayExcept($v, ['get_seckill_time_bucket', 'get_seckill', 'get_goods']);

                $res[$k] = $v;
                $res[$k]['current_time'] = $now;

                if ($tomorrow == 1) {
                    $res[$k]['begin_time'] = TimeRepository::getLocalStrtoTime($v['begin_time']) + $tmr;
                    $res[$k]['end_time'] = TimeRepository::getLocalStrtoTime($v['end_time']) + $tmr;
                } else {
                    $res[$k]['begin_time'] = TimeRepository::getLocalStrtoTime($v['begin_time']);
                    $res[$k]['end_time'] = TimeRepository::getLocalStrtoTime($v['end_time']);
                }

                if ($res[$k]['begin_time'] < $now && $res[$k]['end_time'] > $now) {
                    $res[$k]['status'] = true;
                }
                if ($res[$k]['end_time'] < $now) {
                    $res[$k]['is_end'] = true;
                }
                if ($res[$k]['begin_time'] > $now) {
                    $res[$k]['soon'] = true;
                }

                $get_seckill_goods_attr = $v['get_seckill_goods_attr'] ?? [];
                unset($res[$k]['get_seckill_goods_attr']);
                if (!empty($get_seckill_goods_attr)) {
                    // 有秒杀属性取最小属性价、数量
                    $get_seckill_goods_attr = collect($get_seckill_goods_attr)->sortBy('sec_price')->first();
                    $res[$k]['sec_price'] = $v['sec_price'] = $get_seckill_goods_attr['sec_price'] ?? 0;
                    $res[$k]['sec_num'] = $v['sec_num'] = $get_seckill_goods_attr['sec_num'] ?? 0;
                    $res[$k]['sec_limit'] = $v['sec_limit'] = $get_seckill_goods_attr['sec_limit'] ?? 0;
                }

                $res[$k]['data_end_time'] = TimeRepository::getLocalDate('H:i:s', $res[$k]['begin_time']);
                $res[$k]['sec_price_formated'] = $this->dscRepository->getPriceFormat($v['sec_price']);
                $res[$k]['market_price_formated'] = $this->dscRepository->getPriceFormat($v['market_price']);
                // 秒杀进度
                $res[$k]['percent'] = ($v['sec_num'] > 0) ? ceil($v['sales_volume'] / $v['sec_num'] * 100) : 100;
                $res[$k]['goods_thumb'] = $this->dscRepository->getImagePath($v['goods_thumb']);
                $res[$k]['url'] = dsc_url('/#/seckill/detail') . '?' . http_build_query(['seckill_id' => $v['id'], 'tomorrow' => $tomorrow], '', '&');
                $res[$k]['app_page'] = config('route.seckill.detail') . $v['id'] . '&tomorrow=' . $tomorrow;
            }
        }

        return $res;
    }

    /**
     * 取得秒杀活动的时间表
     * @return array
     */
    public function getSeckillTime()
    {
        $now = $time = TimeRepository::getGmTime();
        $day = 24 * 60 * 60;

        $localData = TimeRepository::getLocalDate('Ymd');
        $date_begin = TimeRepository::getLocalStrtoTime($localData);
        $date_next = TimeRepository::getLocalStrtoTime($localData) + $day;

        $stb = SeckillTimeBucket::select('id', 'title', 'begin_time', 'end_time')
            ->orderBy('begin_time', 'ASC')
            ->get();
        $stb = $stb ? $stb->toArray() : [];

        $sec_id_today = Seckill::selectRaw('GROUP_CONCAT(sec_id) AS sec_id')
            ->where('begin_time', '<=', $date_begin)
            ->where('acti_time', '>', $date_begin)
            ->where('is_putaway', 1)
            ->where('review_status', 3)
            ->orderBy('acti_time', 'ASC')
            ->first();
        $sec_id_today = $sec_id_today ? $sec_id_today->toArray() : [];
        // 秒杀时间段列表
        $time_bucket = [];
        if ($stb) {
            foreach ($stb as $k => $v) {
                $v['local_end_time'] = TimeRepository::getLocalStrtoTime($v['end_time']);
                if ($v['local_end_time'] > $now && $sec_id_today) {
                    $time_bucket[$k]['id'] = $v['id'];
                    $time_bucket[$k]['title'] = $v['title'];
                    $time_bucket[$k]['status'] = false;
                    $time_bucket[$k]['is_end'] = false;
                    $time_bucket[$k]['soon'] = false;
                    $time_bucket[$k]['begin_time'] = $begin_time = TimeRepository::getLocalStrtoTime($v['begin_time']);
                    $time_bucket[$k]['end_time'] = $end_time = TimeRepository::getLocalStrtoTime($v['end_time']);
                    $time_bucket[$k]['frist_end_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', TimeRepository::getLocalStrtoTime($v['end_time']));
                    if ($begin_time < $now && $end_time > $now) {
                        $time_bucket[$k]['status'] = true;
                    }
                    if ($end_time < $now) {
                        $time_bucket[$k]['is_end'] = true;
                    }
                    if ($begin_time > $now) {
                        $time_bucket[$k]['soon'] = true;
                    }
                }
            }
            $sec_id_tomorrow = Seckill::selectRaw('GROUP_CONCAT(sec_id) AS sec_id')
                ->where('begin_time', '<=', $date_next)
                ->where('acti_time', '>', $date_next)
                ->where('is_putaway', 1)
                ->where('review_status', 3)
                ->orderBy('acti_time', 'ASC')
                ->first();
            $sec_id_tomorrow = $sec_id_tomorrow ? $sec_id_tomorrow->toArray() : [];

            $time_bucket_count = count($time_bucket);

            $limit = 4;
            if ($time_bucket_count > $limit) {
                $time_bucket = array_slice($time_bucket, 0, $limit);
            }

            if ($time_bucket_count < $limit) {
//                if ($time_bucket_count == 0) {
//                    $stb = array_slice($stb, 0, 4);
//                }
//                if ($time_bucket_count == 1) {
//                    $stb = array_slice($stb, 0, 3);
//                }
//                if ($time_bucket_count == 2) {
//                    $stb = array_slice($stb, 0, 2);
//                }
//                if ($time_bucket_count == 3) {
//                    $stb = array_slice($stb, 0, 1);
//                }

                $length = $limit - $time_bucket_count;
                $stb = array_slice($stb, 0, $length);

                foreach ($stb as $k => $v) {
                    if ($sec_id_tomorrow) {
                        $time_bucket['tmr' . $k]['id'] = $v['id'];
                        $time_bucket['tmr' . $k]['title'] = $v['title'];
                        $time_bucket['tmr' . $k]['status'] = false;
                        $time_bucket['tmr' . $k]['is_end'] = false;
                        $time_bucket['tmr' . $k]['soon'] = true;
                        $time_bucket['tmr' . $k]['begin_time'] = TimeRepository::getLocalStrtoTime($v['begin_time']) + $day;
                        $time_bucket['tmr' . $k]['end_time'] = TimeRepository::getLocalStrtoTime($v['end_time']) + $day;
                        $time_bucket['tmr' . $k]['frist_end_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', TimeRepository::getLocalStrtoTime($v['end_time']) + $day);
                        $time_bucket['tmr' . $k]['tomorrow'] = 1;
                    }
                }
            }

            $time_bucket = collect($time_bucket)->values()->all();
        }
        return $time_bucket;
    }

    /**
     * 获取秒杀商品
     *
     * @return array
     */
    public function getTopSeckillGoods()
    {
        $date_begin = TimeRepository::getLocalStrtoTime(TimeRepository::getLocalDate('Ymd'));
        $res = SeckillGoods::whereHasIn('getGoods')
            ->whereHasIn('getSeckill', function ($query) use ($date_begin) {
                $query->where('acti_time', '>=', $date_begin);
            })
            ->with('getSeckillTimeBucket');

        $res = $res->take(5);

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $goodsIdList = BaseRepository::getKeyPluck($res, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goodsIdList,['goods_id', 'goods_name', 'shop_price', 'sales_volume', 'goods_thumb']);

            foreach ($res as $key => $look_top) {

                $goods = $goodsList[$look_top['goods_id']] ?? [];

                $look_top['goods_name'] = $goods['goods_name'] ?? '';
                $look_top['shop_price'] = $goods['shop_price'] ?? 0;
                $look_top['sales_volume'] = $goods['sales_volume'] ?? 0;
                $look_top['goods_thumb'] = $goods['goods_thumb'] ?? '';

                $look_top['goods_thumb'] = $this->dscRepository->getImagePath($look_top['goods_thumb']);
                $look_top['url'] = $this->dscRepository->buildUri('seckill', ['act' => "view", 'secid' => $look_top['id']], $look_top['goods_name']);
                $look_top['shop_price'] = $this->dscRepository->getPriceFormat($look_top['shop_price']);

                $res[$key] = $look_top;
            }
        }

        return $res;
    }

    /**
     * 获取商家秒杀商品
     *
     * @param int $sec_goods_id
     * @param int $ru_id
     * @return array
     */
    public function getMerchantSeckillGoods($sec_goods_id = 0, $ru_id = 0)
    {
        $date_begin = TimeRepository::getLocalStrtoTime(TimeRepository::getLocalDate('Ymd'));
        $res = SeckillGoods::whereHasIn('getGoods', function ($query) use ($ru_id) {
            $query->where('user_id', $ru_id);
        })
            ->whereHasIn('getSeckill', function ($query) use ($date_begin) {
                $query->where('acti_time', '>=', $date_begin);
            })
            ->with('getSeckillTimeBucket');

        $res = $res->take(4);

        $res = $res->with([
            'getGoods' => function ($query) {
                $query->select('goods_id', 'goods_name', 'shop_price', 'sales_volume', 'goods_thumb');
            }
        ]);

        $res = $res->get();

        $res = $res ? $res->toArray() : [];

        foreach ($res as $key => $row) {
            $row = collect($row)->merge($row['get_goods'])->except('get_goods')->all();

            $row['shop_price'] = $this->dscRepository->getPriceFormat($row['sec_price'], false);
            $row['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
            $row['url'] = $this->dscRepository->buildUri('seckill', ['act' => "view", 'secid' => $row['id']], $row['goods_name']);

            $res[$key] = $row;
        }

        return $res;
    }


    /**
     * 秒杀商品加入购物车
     *
     * @param int $uid
     * @param int $seckill_goods_id
     * @param int $number
     * @param array $spec
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @return array
     * @throws \Exception
     */
    public function getSeckillBuy($uid = 0, $seckill_goods_id = 0, $number = 1, $spec = [], $warehouse_id = 0, $area_id = 0, $area_city = 0)
    {
        /* 查询：取得秒杀活动信息 */
        $seckill = $this->buy_seckill_info($seckill_goods_id, 0, $spec, $warehouse_id, $area_id, $area_city);

        // 有属性但秒杀商品未设置参与
        if ($spec && $seckill['spec_disable'] == 1) {
            return ['error' => 1, 'mesg' => trans('seckill.spec_disable_notice')];
        }

        /* 查询：检查秒杀活动是否是进行中 */
        if (!$seckill['status']) {
            return ['error' => 1, 'mesg' => lang('seckill.gb_error_status')];
        }

        if ($spec) {
            $goods_attr_id = is_array($spec) ? BaseRepository::getImplode($spec) : $spec;
        } else {
            $goods_attr_id = '';
        }

        $start_date = TimeRepository::getLocalStrtoTime($seckill['begin_time']);
        $end_date = TimeRepository::getLocalStrtoTime($seckill['end_time']);
        $order_goods = $this->orderGoodsService->getForPurchasingGoods($start_date, $end_date, $seckill['goods_id'], $uid, 'seckill', $goods_attr_id);

        /* 秒杀限购 start */
        $sec_limit = $seckill['sec_limit']; // 限购数量
        $sec_num = $seckill['sec_num']; // 秒杀库存数量

        $order_number = $order_goods['goods_number'] + $number;
        if ($sec_limit > 0 && $order_goods['goods_number'] > 0 && $order_goods['goods_number'] >= $sec_limit) {
            $result = [
                'error' => 1,
                'mesg' => lang('js_languages.js_languages.common.Already_buy') . $order_goods['goods_number'] . lang('js_languages.js_languages.common.Already_buy_two')
            ];
            return $result;
        } elseif ($sec_limit > 0 && $order_goods['goods_number'] > 0 && $order_number > $sec_limit) {
            $buy_num = $sec_limit - $order_goods['goods_number'];
            $result = [
                'error' => 1,
                'mesg' => lang('js_languages.js_languages.common.Already_buy') . $buy_num . lang('js_languages.js_languages.common.jian')
            ];
            return $result;
        } elseif ($sec_limit > 0 && $number > $sec_limit) {
            // 超过限购数量
            return ['error' => 1, 'mesg' => lang('js_languages.js_languages.common.Purchase_quantity')];
        } elseif ($number > $sec_num) {
            // 秒杀商品数量或库存不足
            return ['error' => 1, 'mesg' => lang('common.gb_error_goods_lacking')];
        }
        /* 秒杀限购 end */

        $product_info = [];
        if ($spec) {
            $product_info = $this->goodsAttrService->getProductsInfo($seckill['goods_id'], $spec, $warehouse_id, $area_id, $area_city);
        }

        /* 查询：查询规格名称和值，不考虑价格 */
        $goods_attr = app(GoodsService::class)->getGoodsAttrList($spec);

        /* 更新：清空购物车中所有秒杀商品 */
        $this->cartCommonService->clearCart($uid, CART_SECKILL_GOODS);

        $time = TimeRepository::getGmTime();

        $goods_price = isset($seckill['sec_price']) && $seckill['sec_price'] > 0 ? $seckill['sec_price'] : $seckill['shop_price'];



        // 属性成本价、属性货号
        if (!empty($product_info)) {
            $cost_price = $product_info['product_cost_price'] ?? 0;
            $goods_sn = $product_info['product_sn'] ?? '';
        } else {
            $cost_price = $seckill['cost_price'] ?? 0;
            $goods_sn = $seckill['goods_sn'] ?? '';
        }

        $cart = [
            'user_id' => $uid,
            'goods_id' => $seckill['goods_id'],
            'product_id' => $product_info['product_id'] ?? 0,
            'goods_sn' => addslashes($goods_sn),
            'goods_name' => addslashes($seckill['goods_name']),
            'market_price' => $seckill['market_price'],
            'goods_price' => $goods_price,
            'goods_number' => $number,
            'goods_attr' => addslashes($goods_attr),
            'goods_attr_id' => $goods_attr_id,
            'ru_id' => $seckill['user_id'],
            'warehouse_id' => $warehouse_id,
            'area_id' => $area_id,
            'area_city' => $area_city,
            'is_real' => $seckill['is_real'],
            'extension_code' => 'seckill' . $seckill_goods_id,
            'parent_id' => 0,
            'rec_type' => CART_SECKILL_GOODS,
            'is_gift' => 0,
            'add_time' => $time,
            'freight' => $seckill['freight'],
            'tid' => $seckill['tid'],
            'shipping_fee' => $seckill['shipping_fee'],
            'is_shipping' => $seckill['is_shipping'] ?? 0,
            'cost_price' => $cost_price,
        ];

        Cart::insertGetId($cart);

        /* 更新：记录购物流程类型：秒杀 */
        $result = [
            'flow_type' => CART_SECKILL_GOODS,
            'extension_code' => 'seckill',
            'extension_id' => $seckill_goods_id
        ];

        return $result;
    }

    /**
     * 秒杀广告位
     * @param string $ad_type
     * @param int $num
     * @return array|string
     */
    public function seckill_ads($ad_type = 'seckill', $num = 6)
    {
        $position = TouchAdPosition::where(['ad_type' => $ad_type, 'tc_type' => 'banner'])->orderBy('position_id', 'desc')->first();
        $banner_ads = [];
        if (!empty($position)) {
            $banner_ads = $this->adsService->getTouchAds($position->position_id, $num);
        }

        return $banner_ads;
    }
}
