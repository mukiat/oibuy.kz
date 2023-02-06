<?php

namespace App\Services\Order;

use App\Models\Goods;
use App\Models\GoodsActivity;
use App\Models\OrderGoods;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Brand\BrandDataHandleService;

class OrderGoodsDataHandleService
{
    protected $dscRepository;
    protected $brandDataHandleService;

    public function __construct(
        DscRepository $dscRepository,
        BrandDataHandleService $brandDataHandleService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->brandDataHandleService = $brandDataHandleService;
    }

    /**
     * 订单商品列表
     *
     * @param array $order_id
     * @return array
     * @throws \Exception
     */
    public function manageOrderGoods($order_id = [])
    {
        if (empty($order_id)) {
            return [];
        }

        $order_id = array_unique($order_id);

        $res = OrderGoods::whereIn('order_id', $order_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            $goods_id = BaseRepository::getColumn($res, 'extension_code', 'goods_id');
            $goods = $this->getGoods($goods_id);

            $rec_id = BaseRepository::getKeyPluck($res, 'rec_id');
            $orderReturn = OrderReturnDataHandleService::OrderReturn($rec_id);

            foreach ($res as $key => $val) {
                if ($val['extension_code'] == 'package_buy') {
                    $package = $goods['package'][$val['goods_id']] ?? [];
                    $val['goods_thumb'] = $package['activity_thumb'] ?? '';
                    $val['goods_img'] = $val['goods_thumb'];
                    $val['brand_name'] = '';
                } else {
                    $goodsInfo = $goods['goods'][$val['goods_id']] ?? [];
                    $val['goods_thumb'] = $goodsInfo['goods_thumb'] ?? '';
                    $val['goods_img'] = $goodsInfo['goods_img'] ?? '';
                    $val['brand_name'] = $goodsInfo['brand_name'] ?? '';
                }

                $returnGoods = $orderReturn[$val['rec_id']] ?? [];

                if ($returnGoods) {
                    $val['ret_id'] = $returnGoods['ret_id'];
                } else {
                    $val['ret_id'] = 0;
                }

                $arr[$val['order_id']][$key] = $val;
            }

            foreach ($arr as $k => $v) {
                $arr[$k] = array_values($v);
            }
        }

        return $arr;
    }

    /**
     * 获取商品信息
     *
     * @param array $list
     * @return array
     * @throws \Exception
     */
    private function getGoods($list = [])
    {
        if (empty($list)) {
            return [];
        }

        /**
         * 活动商品定义
         */
        $extension_code = [
            'package_buy', //超值礼包
        ];

        /**
         * 普通商品
         */
        $goods_list = [];
        $idx = 0;
        foreach ($list as $key => $val) {
            if (!in_array($val, $extension_code)) {
                $goods_list[$key] = empty($val) ? $idx++ : $val;
            }
        }

        /**
         * 超值礼包
         */
        $package_goods = [];
        $jdx = 0;
        foreach ($list as $key => $val) {
            if ($val == 'package_buy') {
                $package_goods[$key] = $jdx++;
            }
        }

        $arr = [];

        /* 普通商品 */
        if ($goods_list) {
            $goods_id = BaseRepository::getArrayKeys($goods_list);
            $goods_id = $goods_id ? array_unique($goods_id) : $goods_id;
            $goods_id = $goods_id ? array_values($goods_id) : $goods_id;

            $res = Goods::whereIn('goods_id', $goods_id);
            $res = BaseRepository::getToArrayGet($res);

            if ($res) {
                $brand_id = BaseRepository::getKeyPluck($res, 'brand_id');
                $brand = $this->brandDataHandleService->goodsBrand($brand_id);

                foreach ($res as $key => $val) {
                    $val['goods_thumb'] = $val['goods_thumb'] ? $this->dscRepository->getImagePath($val['goods_thumb']) : '';
                    $val['goods_img'] = $val['goods_img'] ? $this->dscRepository->getImagePath($val['goods_img']) : '';
                    $val['brand_name'] = $brand[$val['brand_id']]['brand_name'] ?? '';
                    $arr['goods'][$val['goods_id']] = $val;
                }
            }
        }

        /* 超值礼包 */
        if ($package_goods) {
            $act_id = BaseRepository::getArrayKeys($package_goods);
            $act_id = $act_id ? array_unique($act_id) : $act_id;
            $act_id = $act_id ? array_values($act_id) : $act_id;

            $res = GoodsActivity::select('act_id', 'activity_thumb')->whereIn('act_id', $act_id);
            $res = BaseRepository::getToArrayGet($res);

            if ($res) {
                foreach ($res as $key => $val) {
                    $activity_thumb = $val['activity_thumb'] ? $this->dscRepository->getImagePath($val['activity_thumb']) : $this->dscRepository->dscUrl('themes/ecmoban_dsc2017/images/17184624079016pa.jpg');
                    $arr['package'][$val['act_id']]['activity_thumb'] = $activity_thumb;
                }
            }
        }

        return $arr;
    }

    /**
     * 订单商品信息
     *
     * @param array $rec_id
     * @param array $data
     * @param int $limit
     * @param array $order_id
     * @return array
     */
    public static function orderGoodsDataList($rec_id = [], $data = [], $limit = 0, $order_id = [])
    {
        $rec_id = BaseRepository::getExplode($rec_id);
        $order_id = BaseRepository::getExplode($order_id);

        if (empty($rec_id) && empty($order_id)) {
            return [];
        }

        $rec_id = array_unique($rec_id);
        $order_id = array_unique($order_id);

        $data = empty($data) ? "*" : $data;

        $rec_id = array_unique($rec_id);

        $res = OrderGoods::select($data);

        if (!empty($rec_id)) {
            $res = $res->whereIn('rec_id', $rec_id);
        }

        if (!empty($order_id)) {
            $res = $res->whereIn('rec_id', $order_id);
        }

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['rec_id']] = $val;
            }
        }

        return $arr;
    }

    /**
     * 订单商品信息
     *
     * @param array $goods_id
     * @param array $data
     * @param int $limit
     * @return array
     */
    public static function orderGoodsDataInfo($goods_id = [], $data = [], $limit = 0)
    {
        $goods_id = BaseRepository::getExplode($goods_id);

        if (empty($goods_id)) {
            return [];
        }

        $goods_id = array_unique($goods_id);

        $data = empty($data) ? "*" : $data;

        $goods_id = array_unique($goods_id);

        $res = Goods::select($data)
            ->whereIn('goods_id', $goods_id);

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['goods_id']] = $val;
            }
        }

        return $arr;
    }
}
