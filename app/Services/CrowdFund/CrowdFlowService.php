<?php

namespace App\Services\CrowdFund;

use App\Models\OrderInfo;
use App\Models\Region;
use App\Models\SellerShopinfo;
use App\Models\Shipping;
use App\Models\ZcGoods;
use App\Models\ZcProject;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;

/**
 * 众筹订单提交
 * Class CrowdFund
 * @package App\Services
 */
class CrowdFlowService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 购物车商品
     *
     * @param int $pid
     * @param int $id
     * @param int $number
     * @return array
     */
    public function getCartGoods($pid = 0, $id = 0, $number = 0)
    {
        $gmtime = TimeRepository::getGmTime();

        $res = ZcProject::query();

        $res = $res->whereHasIn('getZcGoods', function ($query) use ($id, $pid) {
            $query->where('id', $id)->where('pid', $pid);
        });

        $res = $res->with(['getZcGoods' => function ($query) use ($id, $pid) {
            $query->where('id', $id)->where('pid', $pid);
        }]);

        $res = BaseRepository::getToArrayFirst($res);

        $timeFormat = config('shop.time_format');

        $list = [];
        if ($res) {
            $zc_goods = $res['get_zc_goods'] ?? [];
            $list['price'] = $zc_goods['price'] ?? 0;
            $list['formated_price'] = $this->dscRepository->getPriceFormat($list['price'], false);
            $list['content'] = $zc_goods['content'] ?? '';
            $list['limit'] = $zc_goods['limit'] ?? 0;
            $list['backer_num'] = $zc_goods['backer_num'] ?? 0;
            $list['shipping_fee'] = $zc_goods['shipping_fee'] ?? 0;

            $list['id'] = $res['id'];
            $list['title'] = $res['title'];
            $list['start_time'] = TimeRepository::getLocalDate($timeFormat, $res['start_time']);
            $list['end_time'] = TimeRepository::getLocalDate($timeFormat, $res['end_time']);
            $list['amount'] = $res['amount'];
            $list['formated_amount'] = $this->dscRepository->getPriceFormat($res['amount'], false);
            $list['join_money'] = $res['join_money'];
            $list['formated_join_money'] = $this->dscRepository->getPriceFormat($res['join_money'], false);
            $list['join_num'] = $res['join_num'];
            $shenyu_time = $res['end_time'] - $gmtime;
            $list['shenyu_time'] = ceil($shenyu_time / 3600 / 24);
            $list['baifen_bi'] = round($res['join_money'] / $res['amount'], 2) * 100;
            $list['title_img'] = $this->dscRepository->getImagePath($res['title_img']);

            $list['number'] = $number;
            $list['end_status'] = $gmtime >= $res['end_time'] ? 1 : 0;
        }

        return $list;
    }

    /**
     * 众筹活动默认配送方式
     *
     * @param int $ru_id
     * @return mixed
     */
    public static function getSellerShopinfoShipping($ru_id = 0)
    {
        return SellerShopinfo::where('ru_id', $ru_id)->value('shipping_id');
    }


    /**
     * 配送方式信息
     *
     * @param int $shipping_id
     * @return array
     */
    public static function getShippingInfo($shipping_id = 0)
    {
        $shipping = Shipping::select('shipping_id', 'shipping_name', 'support_cod', 'shipping_code')->where('shipping_id', $shipping_id)->first();

        return $shipping ? $shipping->toArray() : [];
    }


    /**
     * 计算订单的费用
     *
     * @param array $goods
     * @return array
     */
    public function getOrderFee($goods = [])
    {
        $total = [
            'goods_price' => 0,
            'shipping_fee' => 0,
            'amount' => 0
        ];

        /* 商品总价 */
        $cat_goods = [0 => $goods];
        foreach ($cat_goods as $val) {
            $total['goods_price'] += $val['price'] * $val['number'];
        }

        // 配送费用
        $total['shipping_fee'] = $goods['shipping_fee'] && $goods['shipping_fee'] > 0 ? $goods['shipping_fee'] : 0;

        // 计算订单总额
        $total['amount'] = $total['goods_price'] + $total['shipping_fee'];

        $total['shipping_fee'] = $goods['shipping_fee'] ? $this->dscRepository->getPriceFormat($goods['shipping_fee'], false) : 0;
        // 格式化订单总额
        $total['amount_formated'] = $this->dscRepository->getPriceFormat($total['amount'], false);

        return $total;
    }


    /**
     * 判断重复商品订单 是否支付
     *
     * @param int $user_id
     * @param int $id
     * @return mixed
     */
    public function getZcOrderNum($user_id = 0, $id = 0)
    {
        $count = OrderInfo::where('user_id', $user_id)
            ->where('zc_goods_id', $id)
            ->where('is_zc_order', 1)
            ->where('is_delete', 0)
            ->where('pay_status', PS_UNPAYED)
            ->whereNotIn('order_status', [OS_CANCELED, OS_INVALID]);
        $count = $count->count();

        return $count;
    }


    /**
     * 付款更新众筹信息
     *
     * @param int $order_id
     */
    public function updateZcProject($order_id = 0)
    {
        //取得订单信息
        $order_info = OrderInfo::select('user_id', 'is_zc_order', 'zc_goods_id')->where('order_id', $order_id)->first();
        $order_info = $order_info ? $order_info->toArray() : [];
        if ($order_info) {
            $user_id = $order_info['user_id'];
            $is_zc_order = $order_info['is_zc_order'];
            $zc_goods_id = $order_info['zc_goods_id'];

            if ($is_zc_order == 1 && $zc_goods_id > 0) {
                //获取众筹商品信息
                $zc_goods_info = ZcGoods::where('id', $zc_goods_id)->first();
                $zc_goods_info = $zc_goods_info ? $zc_goods_info->toArray() : [];
                $pid = $zc_goods_info['pid'];
                $goods_price = $zc_goods_info['price'];
                $backer_list = $zc_goods_info['backer_list'];

                if (empty($backer_list)) {
                    $backer_list = $user_id;
                } else {
                    $backer_list = $backer_list . ',' . $user_id;
                }

                //增加众筹商品支持的用户数量,支持的用户id
                ZcGoods::where('id', $zc_goods_id)
                    ->update(['backer_num' => $zc_goods_info['backer_num'] + 1, 'backer_list' => $backer_list]);

                //增加众筹项目的支持用户总数量、增加众筹项目总金额
                $zc_project = ZcProject::where('id', $pid)
                    ->first()
                    ->toArray();

                ZcProject::where('id', $pid)
                    ->update(['join_num' => $zc_project['join_num'] + 1, 'join_money' => $zc_project['join_money'] + $goods_price]);
            }
        }
    }

    /**
     * 插入众筹订单
     *
     * @param array $order
     * @return int
     */
    public function addOrderInfo($order = [])
    {
        if (empty($order)) {
            return 0;
        }

        $order_id = OrderInfo::insertGetId($order);

        return $order_id;
    }

    /**
     * 检查众筹下单流程是否有收货地址 区别普通购物车流程
     * @param array $consignee
     * @return bool
     */
    public function checkConsigneeInfo($consignee = [])
    {
        $res = (isset($consignee['consignee']) && !empty($consignee['consignee'])) &&
            ((isset($consignee['tel']) && !empty($consignee['tel'])) || (isset($consignee['mobile']) && !empty($consignee['mobile'])));

        if ($res) {
            if (isset($consignee['province']) && empty($consignee['province'])) {
                /* 没有设置省份，检查当前国家下面有没有设置省份 */
                $pro = Region::where('region_type', 1)->where('parent_id', $consignee['country'])->count('region_id');
                $pro = $pro ?? 0;

                $res = empty($pro);
            } elseif (isset($consignee['city']) && empty($consignee['city'])) {
                /* 没有设置城市，检查当前省下面有没有城市 */
                $city = Region::where('region_type', 2)->where('parent_id', $consignee['province'])->count('region_id');
                $city = $city ?? 0;

                $res = empty($city);
            } elseif (isset($consignee['district']) && empty($consignee['district'])) {
                $dist = Region::where('region_type', 3)->where('parent_id', $consignee['city'])->count('region_id');
                $dist = $dist ?? 0;

                $res = empty($dist);
            }
        }

        return $res;
    }
}
