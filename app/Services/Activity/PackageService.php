<?php

namespace App\Services\Activity;

use App\Models\Cart;
use App\Models\GoodsActivity;
use App\Models\PackageGoods;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Goods\GoodsActivityService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\User\UserCommonService;

class PackageService
{
    protected $userCommonService;
    protected $goodsActivityService;
    protected $dscRepository;

    public function __construct(
        UserCommonService $userCommonService,
        GoodsActivityService $goodsActivityService,
        DscRepository $dscRepository
    )
    {
        $this->userCommonService = $userCommonService;
        $this->goodsActivityService = $goodsActivityService;
        $this->dscRepository = $dscRepository;
    }

    /**
     * 获得超值礼包列表
     *
     * @param array $where
     * @param int $page
     * @param int $pageSize
     * @return array
     * @throws \Exception
     */
    public function getPackageList($where = [], $page = 1, $pageSize = 10)
    {
        $now = TimeRepository::getGmTime();

        /**
         * 兼容API & Web TODO
         */
        if (isset($where['user_id'])) {
            $rank = $this->userCommonService->getUserRankByUid($where['user_id']);
            if ($rank) {
                $user_rank = $rank['rank_id'];
                $discount = $rank['discount'];
            } else {
                $user_rank = 1;
                $discount = 100;
            }
        } else {
            $user_rank = session('user_rank', 1);
            $discount = session('discount', 1) * 100;
        }

        $res = GoodsActivity::where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->where('act_type', GAT_PACKAGE)
            ->where('review_status', 3)
            ->orderBy('end_time')
            ->offset($pageSize * ($page - 1))
            ->limit($pageSize);

        $res = BaseRepository::getToArrayGet($res);

        $list = [];
        if ($res) {
            foreach ($res as $row) {
                $row['activity_thumb'] = !empty($row['activity_thumb']) ? $this->dscRepository->getImagePath($row['activity_thumb']) : $this->dscRepository->dscUrl('themes/ecmoban_dsc2017/images/17184624079016pa.jpg');
                $row['start_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $row['start_time']);
                $row['end_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $row['end_time']);
                $ext_arr = unserialize($row['ext_info']);
                unset($row['ext_info']);
                if ($ext_arr) {
                    foreach ($ext_arr as $key => $val) {
                        $row[$key] = $val;
                    }
                }

                $goods_res = PackageGoods::from('package_goods as pg')
                    ->select('pg.package_id', 'pg.goods_id', 'pg.goods_number', 'pg.admin_id', 'g.goods_sn', 'g.goods_name', 'g.market_price', 'g.goods_thumb', 'mp.user_price', 'g.shop_price', 'p.product_price')
                    ->leftJoin('products as p', function ($join) {
                        $join->on('pg.goods_id', '=', 'p.goods_id')->on('pg.product_id', '=', 'p.product_id');
                    })
                    ->leftJoin('goods as g', 'g.goods_id', '=', 'pg.goods_id')
                    ->leftJoin('member_price as mp', function ($query) use ($user_rank) {
                        $query->on('mp.goods_id', '=', 'g.goods_id')->where('mp.user_rank', $user_rank);
                    })
                    ->where('pg.package_id', $row['act_id'])
                    ->where('pg.goods_id', '<>', 0)
                    ->orderBy('pg.goods_id');

                $goods_res = BaseRepository::getToArrayGet($goods_res);

                $package_price = $row['package_price'];

                $subtotal = 0;
                $goods_number = 0;
                if ($goods_res) {
                    foreach ($goods_res as $key => $val) {
                        if (isset($val['user_price']) && $val['user_price'] > 0) {
                            $val['rank_price'] = $val['user_price'];
                        } else {
                            $val['rank_price'] = ($val['product_price'] > 0 ? $val['product_price'] : $val['shop_price']) * $discount / 100;
                        }
                        $goods_res[$key]['goods_thumb'] = $this->dscRepository->getImagePath($val['goods_thumb']);
                        $goods_res[$key]['market_price_formatted'] = $this->dscRepository->getPriceFormat($val['market_price']);
                        $goods_res[$key]['shop_price_formatted'] = $this->dscRepository->getPriceFormat($val['shop_price']);
                        $goods_res[$key]['rank_price'] = number_format($val['rank_price'], 2, '.', '');
                        $goods_res[$key]['rank_price_formatted'] = $this->dscRepository->getPriceFormat($val['rank_price']);
                        $goods_res[$key]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $val['goods_id']], $val['goods_name']);
                        $goods_res[$key]['package_amount'] = $val['shop_price'] * $val['goods_number'] - $package_price;
                        $goods_res[$key]['package_amount_formatted'] = $this->dscRepository->getPriceFormat($val['shop_price'] * $val['goods_number'] - $package_price);
                        $subtotal += $val['rank_price'] * $val['goods_number'];
                        $goods_number += $val['goods_number'];
                    }
                }

                $saving = 0;
                if ($subtotal > $package_price) {
                    $saving = $subtotal - $package_price;
                }

                $row['goods_list'] = $goods_res;
                $row['subtotal'] = $subtotal;
                $row['subtotal_formatted'] = $this->dscRepository->getPriceFormat($subtotal);
                $row['saving'] = $saving;
                $row['saving_formatted'] = $this->dscRepository->getPriceFormat($saving);
                $row['package_price'] = $package_price;
                $row['package_price_formatted'] = $this->dscRepository->getPriceFormat($package_price);
                $row['package_amount'] = $saving;
                $row['package_amount_formatted'] = $this->dscRepository->getPriceFormat($saving);
                $row['package_number'] = $goods_number;

                $list[] = $row;
            }
        }

        return $list;
    }

    /**
     * 获得超值礼包列表
     *
     * @param int $package_id
     * @return array
     * @throws \Exception
     */
    public function getPackageInfo($package_id = 0)
    {
        // 超值礼包详情
        $package = $this->goodsActivityService->getGoodsActivity($package_id, GAT_PACKAGE);
        if ($package) {
            /* 将时间转成可阅读格式 */
            $now = TimeRepository::getGmTime();

            if ($package['start_time'] <= $now && $package['end_time'] >= $now) {
                $package['is_on_sale'] = 1;
            } else {
                $package['is_on_sale'] = 0;
            }

            $package['start_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $package['start_time']);
            $package['end_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $package['end_time']);
            $row = unserialize($package['ext_info']);

            unset($package['ext_info']);
            if ($row) {
                foreach ($row as $key => $val) {
                    $package[$key] = $val;
                }
            }

            $goods_res = PackageGoods::select('package_id', 'goods_id', 'goods_number', 'admin_id')
                ->where('package_id', $package_id)
                ->orderby('package_id', 'desc');

            $goods_res = BaseRepository::getToArrayGet($goods_res);

            $market_price = 0;
            $real_goods_count = 0;
            $virtual_goods_count = 0;

            if ($goods_res) {

                $goods_id = BaseRepository::getKeyPluck($goods_res, 'goods_id');

                $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'goods_sn', 'goods_name', 'goods_number', 'market_price', 'goods_thumb', 'is_real', 'shop_price']);

                foreach ($goods_res as $key => $val) {

                    $goods = $goodsList[$val['goods_id']];

                    $val['goods_sn'] = $goods['goods_sn'] ?? '';
                    $val['goods_name'] = $goods['goods_name'] ?? '';
                    $val['product_number'] = $goods['goods_number'] ?? '';
                    $val['market_price'] = $goods['market_price'] ?? 0;
                    $val['goods_thumb'] = $goods['goods_thumb'] ?? '';
                    $val['is_real'] = $goods['is_real'] ?? 0;
                    $val['shop_price'] = $goods['shop_price'] ?? 0;

                    $goods_res[$key]['goods_thumb'] = $this->dscRepository->getImagePath($val['goods_thumb']);
                    $goods_res[$key]['market_price_format'] = $this->dscRepository->getPriceFormat($val['market_price']);
                    $goods_res[$key]['rank_price_format'] = $this->dscRepository->getPriceFormat($val['shop_price']);
                    $market_price += $val['market_price'] * $val['goods_number'];
                    /* 统计实体商品和虚拟商品的个数 */
                    if ($val['is_real']) {
                        $real_goods_count++;
                    } else {
                        $virtual_goods_count++;
                    }
                }
            }

            if ($real_goods_count > 0) {
                $package['is_real'] = 1;
            } else {
                $package['is_real'] = 0;
            }

            $package['goods_list'] = $goods_res;
            $package['market_package'] = $market_price;
            $package['market_package_format'] = $this->dscRepository->getPriceFormat($market_price);
            $package['package_price_format'] = $this->dscRepository->getPriceFormat($package['package_price']);
        } else {
            //移除购物车中的无效超值礼包
            if ($package_id) {
                Cart::where('goods_id', $package_id)->where('extension_code', 'package_buy')->delete();
            }
        }

        return $package;
    }
}
