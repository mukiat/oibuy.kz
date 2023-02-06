<?php

namespace App\Services\Activity;

use App\Models\CollectStore;
use App\Models\Coupons;
use App\Models\CouponsRegion;
use App\Models\CouponsUser;
use App\Models\Goods;
use App\Models\OrderInfo;
use App\Models\Region;
use App\Models\UserRank;
use App\Models\Users;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Category\CategoryService;
use App\Services\Coupon\CouponDataHandleService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Order\OrderDataHandleService;
use App\Services\User\UserDataHandleService;

/**
 * 活动 ->【优惠券】
 */
class CouponsService
{
    protected $merchantCommonService;
    protected $dscRepository;

    public function __construct(
        MerchantCommonService $merchantCommonService,
        DscRepository $dscRepository
    )
    {
        $this->merchantCommonService = $merchantCommonService;
        $this->dscRepository = $dscRepository;
    }

    /**
     * 格式化优惠券数据(注册送、购物送除外)
     *
     * @param array $cou_data
     * @param int $user_id
     * @return array
     * @throws \Exception
     */
    public function getFromatCoupons($cou_data = [], $user_id = 0)
    {

        //当前时间;
        $time = TimeRepository::getGmTime();

        if ($cou_data) {

            $ru_id = BaseRepository::getKeyPluck($cou_data, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            $cou_id = BaseRepository::getKeyPluck($cou_data, 'cou_id');
            $couponsUserList = CouponDataHandleService::getCouponsUserDataList([], $cou_id, $user_id, ['uc_id', 'cou_id', 'user_id', 'is_use', 'is_delete']);

            $cou_goods = BaseRepository::getKeyPluck($cou_data, 'cou_goods');
            $cou_goods = BaseRepository::getImplode($cou_goods);
            $cou_goods = BaseRepository::getExplode($cou_goods);
            $cou_goods = BaseRepository::getArrayUnique($cou_goods);
            $cou_goods = ArrRepository::getArrayUnset($cou_goods);
            $goodsList = GoodsDataHandleService::GoodsDataList($cou_goods, ['goods_id', 'goods_name', 'goods_thumb']);

            foreach ($cou_data as $k => $v) {

                //优惠券剩余量
                if (!isset($v['cou_surplus'])) {
                    $cou_data[$k]['cou_surplus'] = 100;
                }

                //可使用优惠券的商品; bylu
                if (!empty($v['cou_goods'])) {

                    $goods_ids = BaseRepository::getExplode($v['cou_goods']);

                    $sql = [
                        'whereIn' => [
                            [
                                'name' => 'goods_id',
                                'value' => $goods_ids
                            ]
                        ]
                    ];
                    $goods = BaseRepository::getArraySqlGet($goodsList, $sql);

                    $v['cou_goods'] = $goods_ids;

                    if (!empty($goods)) {
                        foreach ($goods as $g_key => $g_val) {
                            if ($g_val['goods_thumb']) {
                                $goods[$g_key]['goods_thumb'] = $this->dscRepository->getImagePath($g_val['goods_thumb']);
                            }
                        }
                    }

                    $cou_data[$k]['cou_goods_name'] = $goods;
                }

                //可领券的会员等级;
                if (!empty($v['cou_ok_user'])) {
                    $v['cou_ok_user'] = !is_array($v['cou_ok_user']) ? explode(",", $v['cou_ok_user']) : $v['cou_ok_user'];

                    $name = UserRank::selectRaw('GROUP_CONCAT(rank_name) AS rank_name')->whereIn('rank_id', $v['cou_ok_user'])->first();

                    $name = $name ? $name->toArray() : [];

                    $cou_data[$k]['cou_ok_user_name'] = $name ? $name['rank_name'] : '';
                }

                //可使用的店铺;
                $cou_data[$k]['store_name'] = sprintf($GLOBALS['_LANG']['use_limit'], $merchantList[$v['ru_id']]['shop_name'] ?? '');


                //时间戳转时间;
                $cou_data[$k]['cou_start_time_format'] = TimeRepository::getLocalDate('Y/m/d', $v['cou_start_time']);
                $cou_data[$k]['cou_end_time_format'] = TimeRepository::getLocalDate('Y/m/d', $v['cou_end_time']);

                //判断是否已过期;
                if ($v['cou_end_time'] < $time) {
                    $cou_data[$k]['is_overdue'] = 1;
                } else {
                    $cou_data[$k]['is_overdue'] = 0;
                }

                if (!empty($v['cou_goods'])) {
                    $goodstype = lang('common.spec_goods');
                } elseif (!empty($v['spec_cat'])) {
                    $goodstype = lang('common.spec_cat');
                } else {
                    $goodstype = lang('common.all_goods');
                }

                if ($v['cou_type'] == VOUCHER_ALL) {
                    $cou_type_name = lang('coupons.vouchers_all');
                } elseif ($v['cou_type'] == VOUCHER_USER) {
                    $cou_type_name = lang('coupons.vouchers_user');
                } elseif ($v['cou_type'] == VOUCHER_SHIPPING) {
                    $cou_type_name = lang('coupons.vouchers_shipping');
                } else {
                    $cou_type_name = lang('coupons.unknown');
                }

                //优惠券种类;
                $cou_data[$k]['cou_type_name'] = $cou_type_name . "[$goodstype]";

                //是否已经领取过了
                if ($user_id > 0) {
                    $sql = [
                        'where' => [
                            [
                                'name' => 'is_delete',
                                'value' => 0
                            ],
                            [
                                'name' => 'cou_id',
                                'value' => $v['cou_id']
                            ],
                            [
                                'name' => 'user_id',
                                'value' => $user_id
                            ]
                        ]
                    ];
                    $coupon = BaseRepository::getArraySqlGet($couponsUserList, $sql);
                    $cou_user_num = BaseRepository::getArrayCount($coupon);

                    if ($v['cou_user_num'] <= $cou_user_num) {
                        $cou_data[$k]['cou_is_receive'] = 1;
                    } else {
                        $cou_data[$k]['cou_is_receive'] = 0;
                    }
                }
            }
        }

        return $cou_data;
    }

    /**
     * //取出各条优惠券剩余总数(注册送、购物送除外)
     * @param array $cou_type
     * @return  array
     */
    public function getCouponsSurplus($cou_type = [], $num = 0)
    {

        //当前时间;
        $time = TimeRepository::getGmTime();

        $res = Coupons::whereNotIn('cou_type', $cou_type)
            ->where('review_status', 3)
            ->where('cou_type', '<>', VOUCHER_GROUPBUY)
            ->whereRaw("IF(valid_type > 1, receive_start_time <= '$time' and receive_end_time >= '$time', cou_start_time <= '$time' and cou_end_time >= '$time')")
            ->where('status', COUPON_STATUS_EFFECTIVE);

        if ($num) {
            $res = $res->take($num);
        }

        $res = $res->orderBy('cou_id', 'desc');

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $couIdList = BaseRepository::getKeyPluck($res, 'cou_id');
            $couUserList = CouponDataHandleService::getCouponsUserDataList($couIdList, ['uc_id', 'cou_id']);

            foreach ($res as $key => $row) {

                $cou = $couUserList[$row['cou_id']] ?? [];
                $row = BaseRepository::getArrayMerge($row, $cou);

                $row['use_num'] = BaseRepository::getArrayCount($cou);

                $res[$key] = $row;
                $res[$key]['cou_surplus'] = ($row['cou_total'] > $row['use_num']) ? floor(($row['cou_total'] - $row['use_num']) / $row['cou_total'] * 100) : 0;
            }
        }

        return $res;
    }

    /**
     * 取出各条优惠券剩余总数(注册送、购物送除外)
     *
     * @param array $cou_type
     * @param int $num
     * @param array $cou_surplus
     * @return mixed
     */
    public function getCouponsData($cou_type = [], $num = 0, $cou_surplus = [])
    {
        //当前时间;
        $time = TimeRepository::getGmTime();

        $res = Coupons::whereNotIn('cou_type', $cou_type)
            ->where('review_status', 3)
            ->where('cou_type', '<>', VOUCHER_GROUPBUY)
            ->whereRaw("IF(valid_type > 1, receive_start_time <= '$time' and receive_end_time >= '$time', cou_start_time <= '$time' and cou_end_time >= '$time')")
            ->where('status', COUPON_STATUS_EFFECTIVE);

        $res = $res->orderBy('cou_id', 'desc');

        if ($num) {
            $res = $res->take($num);
        }

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $couIdList = BaseRepository::getKeyPluck($res, 'cou_id');
            $couUserList = CouponDataHandleService::getCouponsUserDataList($couIdList, ['uc_id', 'user_id', 'is_use', 'cou_id']);

            foreach ($res as $key => $row) {

                $couUser = $couUserList[$row['cou_id']] ?? [];

                $row = BaseRepository::getArrayMerge($row, $couUser);
                $res[$key] = $row;

                if ($cou_surplus) {
                    //格式化各优惠券剩余总数
                    foreach ($cou_surplus as $m => $n) {
                        if ($row['cou_id'] == $n['cou_id']) {
                            $res[$key]['cou_surplus'] = $n['cou_surplus'];
                        }
                    }
                }
            }
        }

        return $res;
    }

    /**
     * 任务集市(限购物券(购物满额返券))
     *
     * @param array $cou_type
     * @param int $num
     * @return array
     * @throws \Exception
     */
    public function getCouponsGoods($cou_type = [], $num = 0)
    {
        $time = TimeRepository::getGmTime();

        $res = Coupons::whereIn('cou_type', $cou_type)
            ->where('review_status', 3)
            ->whereRaw("IF(valid_type > 1, receive_start_time <= '$time' and receive_end_time >= '$time', cou_start_time <= '$time' and cou_end_time >= '$time')")
            ->where('status', COUPON_STATUS_EFFECTIVE);

        if ($num) {
            $res = $res->take($num);
        }

        $cou_goods = BaseRepository::getToArrayGet($res);

        if ($cou_goods) {

            $ru_id = BaseRepository::getKeyPluck($cou_goods, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            $cou_ok_goods = BaseRepository::getKeyPluck($cou_goods, 'cou_ok_goods');
            $cou_ok_goods = $cou_ok_goods ? implode(',', $cou_ok_goods) : '';
            $cou_ok_goods = BaseRepository::getExplode($cou_ok_goods);
            $cou_ok_goods = BaseRepository::getArrayUnique($cou_ok_goods);
            $cou_ok_goods = ArrRepository::getArrayUnset($cou_ok_goods);
            $goodsList = GoodsDataHandleService::GoodsDataList($cou_ok_goods, ['goods_id', 'goods_name', 'goods_thumb']);

            foreach ($cou_goods as $k => $v) {

                //商品图片(没有指定商品时为默认图片)
                if ($v['cou_ok_goods']) {
                    $v['cou_ok_goods'] = $this->dscRepository->delStrComma($v['cou_ok_goods']);

                    $v['cou_ok_goods'] = BaseRepository::getExplode($v['cou_ok_goods']);

                    $sql = [
                        'whereIn' => [
                            [
                                'name' => 'goods_id',
                                'value' => $v['cou_ok_goods']
                            ]
                        ]
                    ];
                    $cou_goods_arr = BaseRepository::getArraySqlGet($goodsList, $sql);

                    if (!empty($cou_goods_arr)) {
                        foreach ($cou_goods_arr as $g_key => $g_val) {
                            if ($g_val['goods_thumb']) {
                                $cou_goods_arr[$g_key]['goods_thumb'] = $this->dscRepository->getImagePath($g_val['goods_thumb']);
                            }
                        }
                    }
                    $cou_goods[$k]['cou_ok_goods_name'] = $cou_goods_arr;
                } else {
                    $cou_goods[$k]['cou_ok_goods_name'][0]['goods_thumb'] = $this->dscRepository->getImagePath("images/coupons_default.png");
                }
                //可使用的店铺;
                $cou_goods[$k]['store_name'] = sprintf($GLOBALS['_LANG']['use_limit'], $merchantList[$v['ru_id']]['shop_name'] ?? '');
                $cou_goods[$k]['cou_end_time_format'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $v['cou_end_time']);
            }
        }

        return $cou_goods;
    }

    /**
     * 免邮神券
     *
     * @param array $cou_type
     * @param int $num
     * @param array $cou_surplus
     * @return array
     */
    public function getCouponsShipping($cou_type = [], $num = 0, $cou_surplus = [])
    {
        $time = TimeRepository::getGmTime();

        $res = Coupons::whereIn('cou_type', $cou_type)
            ->where('review_status', 3)
            ->whereRaw("IF(valid_type > 1, receive_start_time <= '$time' and receive_end_time >= '$time', cou_start_time <= '$time' and cou_end_time >= '$time')")
            ->where('status', COUPON_STATUS_EFFECTIVE);

        if ($num) {
            $res = $res->take($num);
        }

        $cou_shipping = BaseRepository::getToArrayGet($res);

        //格式化各优惠券剩余总数
        if ($cou_shipping) {
            foreach ($cou_shipping as $k => $v) {
                if ($cou_surplus) {
                    foreach ($cou_surplus as $m => $n) {
                        if ($v['cou_id'] == $n['cou_id']) {
                            $cou_shipping[$k]['cou_surplus'] = $n['cou_surplus'];
                        }
                    }
                }
            }
        }

        return $cou_shipping;
    }

    /**
     * 优惠券总数
     *
     * @param array $cou_type
     * @param string $type
     * @return int
     */
    public function getCouponsCount($cou_type = [], $type = '')
    {
        $time = TimeRepository::getGmTime();

        $res = Coupons::where('review_status', 3)
            ->where('cou_type', '<>', VOUCHER_GROUPBUY)
            ->whereRaw("IF(valid_type > 1, receive_start_time <= '$time' and receive_end_time >= '$time', cou_start_time <= '$time' and cou_end_time >= '$time')")
            ->where('status', COUPON_STATUS_EFFECTIVE);

        if ($type == 'all') {
            $res = $res->where('cou_type', VOUCHER_ALL);
        } elseif ($type == 'member') {
            $res = $res->where('cou_type', VOUCHER_USER);
        } elseif ($type == 'shipping') {
            $res = $res->where('cou_type', VOUCHER_SHIPPING);
        } elseif ($type == 'goods') {
            $res = $res->whereIn('cou_type', $cou_type);
        } else {
            $res = $res->whereNotIn('cou_type', $cou_type);
        }

        return $res->count();
    }

    /**
     * 优惠券列表
     *
     * @param array $cou_type
     * @param string $type
     * @param string $sort
     * @param string $order
     * @param int $start
     * @param int $size
     * @param array $cou_surplus
     * @return mixed
     * @throws \Exception
     */
    public function getCouponsList($cou_type = [], $type = '', $sort = 'cou_id', $order = 'desc', $start = 0, $size = 10, $cou_surplus = [])
    {
        $order = in_array($order, ['asc', 'desc']) ? $order : 'desc';
        $time = TimeRepository::getGmTime();

        $res = Coupons::where('review_status', 3)
            ->where('cou_type', '<>', VOUCHER_GROUPBUY)
            ->whereRaw("IF(valid_type > 1, receive_start_time <= '$time' and receive_end_time >= '$time', cou_start_time <= '$time' and cou_end_time >= '$time')")
            ->where('status', COUPON_STATUS_EFFECTIVE);

        if ($cou_type) {
            $res = $res->whereNotIn('cou_type', $cou_type);
        }

        if (is_numeric($type)) {
            $res = $res->where('cou_type', $type);
        } else {
            if ($type == 'all') {
                $res = $res->where('cou_type', VOUCHER_ALL);
            } elseif ($type == 'member') {
                $res = $res->where('cou_type', VOUCHER_USER);
            } elseif ($type == 'shipping') {
                $res = $res->where('cou_type', VOUCHER_SHIPPING);
            }
        }

        $res = $res->orderBy($sort, $order);

        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($size > 0) {
            $res = $res->take($size);
        }

        $cou_data = BaseRepository::getToArrayGet($res);

        //格式化各优惠券剩余总数
        if ($cou_data) {

            $couIdList = BaseRepository::getKeyPluck($cou_data, 'cou_id');
            $couUserList = CouponDataHandleService::getCouponsUserDataList($couIdList, ['cou_id', 'user_id', 'is_use']);

            $ru_id = BaseRepository::getKeyPluck($cou_data, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($cou_data as $k => $v) {

                $couUser = $couUserList[$v['cou_id']] ?? [];

                $v = BaseRepository::getArrayMerge($v, $couUser);

                if ($cou_surplus) {
                    foreach ($cou_surplus as $m => $n) {
                        if ($v['cou_id'] == $n['cou_id']) {
                            $cou_data[$k]['cou_surplus'] = $n['cou_surplus'];
                        }
                    }
                } else {

                    //商品图片(没有指定商品时为默认图片)
                    if ($v['cou_ok_goods']) {
                        $v['cou_ok_goods'] = $this->dscRepository->delStrComma($v['cou_ok_goods']);

                        $v['cou_ok_goods'] = !is_array($v['cou_ok_goods']) ? explode(",", $v['cou_ok_goods']) : $v['cou_ok_goods'];

                        $cou_goods_arr = Goods::select(['goods_id', 'goods_name', 'goods_thumb'])->whereIn('goods_id', $v['cou_ok_goods'])->get();

                        $cou_goods_arr = $cou_goods_arr ? $cou_goods_arr->toArray() : [];

                        if (!empty($cou_goods_arr)) {
                            foreach ($cou_goods_arr as $g_key => $g_val) {
                                if ($g_val['goods_thumb']) {
                                    $cou_goods_arr[$g_key]['goods_thumb'] = $this->dscRepository->getImagePath($g_val['goods_thumb']);
                                }
                            }
                        }
                        $cou_data[$k]['cou_ok_goods_name'] = $cou_goods_arr;
                    } else {
                        $cou_data[$k]['cou_ok_goods_name'][0]['goods_thumb'] = $this->dscRepository->getImagePath("images/coupons_default.png");
                    }
                    $cou_data[$k]['cou_end_time_format'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $v['cou_end_time']);

                    //判断是否已过期,0过期，1未过期 by yanxin;
                    if ($v['cou_end_time'] < $time) {
                        $cou_data[$k]['is_overtime'] = 0;
                    } else {
                        $cou_data[$k]['is_overtime'] = 1;
                    }

                    $v['cou_money'] = $v['cou_money'] ?? 0;
                    $cou_data[$k]['format_cou_money'] = $this->dscRepository->getPriceFormat($v['cou_money']);

                    //可使用的店铺;
                    $cou_data[$k]['store_name'] = sprintf($GLOBALS['_LANG']['use_limit'], $merchantList[$v['ru_id']]['shop_name'] ?? '');
                }
            }
        }

        return $cou_data;
    }

    /**
     * //取出当前优惠券信息(未过期,剩余总数大于0)
     * @param int $cou_id
     * @return  array
     */
    public function getCouponsHaving($cou_id = 0)
    {

        //当前时间;
        $time = TimeRepository::getGmTime();

        $count = CouponsUser::where('is_delete', 0)->where('cou_id', $cou_id)->count();

        $res = Coupons::where('cou_id', $cou_id)
            ->where('review_status', 3)
            ->where('cou_type', '<>', VOUCHER_GROUPBUY)
            ->whereRaw("IF(valid_type > 1, receive_start_time <= '$time' and receive_end_time >= '$time', cou_start_time <= '$time' and cou_end_time >= '$time')")
            ->where('status', COUPON_STATUS_EFFECTIVE);

        $res = $res->orderBy('cou_id');

        $res = BaseRepository::getToArrayFirst($res);

        if ($res) {
            $res['cou_surplus'] = $res['cou_total'] - $count;
        } else {
            $res['cou_surplus'] = 0;
        }

        return $res['cou_surplus'] > 0 ? $res : [];
    }

    /**
     * 获取免邮券不包邮地区
     *
     * @param int $cou_id
     * @return  array
     */
    public function getCouponsRegionList($cou_id = 0)
    {
        $arr = ['free_value' => '', 'free_value_name' => ''];

        $arr['free_value'] = CouponsRegion::where('cou_id', $cou_id)->value('region_list');

        if ($arr['free_value']) {
            $free_value = !is_array($arr['free_value']) ? explode(",", $arr['free_value']) : $arr['free_value'];

            $region_list = Region::selectRaw('GROUP_CONCAT(region_name) as region_name')->whereIn('region_id', $free_value);
            $region_list = BaseRepository::getToArrayFirst($region_list);

            $arr['free_value_name'] = $region_list;
            $arr['free_value_name'] = implode(',', $arr['free_value_name']);
        }

        return $arr;
    }

    /**
     * 检查优惠券是否可领
     *
     * @param int $cou_id
     * @return  array
     */
    public function getRemainingNumber($cou_id = 0)
    {
        $total = CouponsUser::where('is_delete', 0)->where('cou_id', $cou_id)->count();
        $user_num = $total ? $total : 0;

        $count = Coupons::where('cou_id', $cou_id)
            ->where('status', COUPON_STATUS_EFFECTIVE)
            ->where('cou_total', '>', $user_num)
            ->count();

        return $count;
    }

    /**
     * 订单结算使用优惠券
     *
     * @param int $user_id
     * @param array $cart_goods
     * @param bool $can_use
     * @param array $consignee
     * @param int $shipping_fee
     * @return array
     * @throws \Exception
     */
    public function flowUserCoupons($user_id = 0, $cart_goods = [], $can_use = true, $consignee = [], $shipping_fee = 0)
    {
        if (empty($user_id) || empty($cart_goods)) {
            return [];
        }

        $time = TimeRepository::getGmTime();

        // 生成商家数据结构
        $cart_goods_group = [];
        if (!empty($cart_goods)) {
            foreach ($cart_goods as $k => $v) {
                //过滤虚拟商品
                $is_real = $v['get_goods']['is_real'] ?? 0;
                $extension_code = $v['get_goods']['extension_code'] ?? '';
                if ($is_real == 0 && $extension_code == 'virtual_card') {
                    continue;
                }
                // 初始化
                $cart_goods_group[$v['ru_id']]['order_total'] = $cart_goods_group[$v['ru_id']]['order_total'] ?? 0;
                $cart_goods_group[$v['ru_id']]['seller_id'] = $cart_goods_group[$v['ru_id']]['seller_id'] ?? 0;
                $cart_goods_group[$v['ru_id']]['goods_id'] = $cart_goods_group[$v['ru_id']]['goods_id'] ?? [];
                $cart_goods_group[$v['ru_id']]['cat_id'] = $cart_goods_group[$v['ru_id']]['cat_id'] ?? [];
                $cart_goods_group[$v['ru_id']]['goods'] = $cart_goods_group[$v['ru_id']]['goods'] ?? [];

                /* 扣除参与活动金额【商品满减、红包、折扣、储值卡折扣】 */
                $v['subtotal'] = ($v['goods_price'] * $v['goods_number']) - ($v['dis_amount'] + $v['goods_bonus'] + $v['goods_favourable']);

                $cart_goods_group[$v['ru_id']]['order_total'] += $v['subtotal'];
                $cart_goods_group[$v['ru_id']]['seller_id'] = $v['ru_id'] ?? 0;

                $goods_id = $v['goods_id'] ?? [];
                $cart_goods_group[$v['ru_id']]['goods_id'][] = !empty($v['product_id']) ? $goods_id . '_' . $v['product_id'] : $goods_id;//属性商品 数组被重新赋值
                $cart_goods_group[$v['ru_id']]['cat_id'][] = $v['cat_id'] ?? [];

                if ($v['product_id'] > 0) {
                    $cart_goods_group[$v['ru_id']]['goods'][$v['goods_id'] . '_' . $v['product_id']] = $v;
                } else {
                    $cart_goods_group[$v['ru_id']]['goods'][$v['goods_id']] = $v;
                }

            }
        }

        $coupons_user_all = []; // 所有优惠券
        $coupons_list = []; // 可使用优惠券
        if (!empty($cart_goods_group)) {

            $model = CouponsUser::select('uc_id', 'cou_id', 'cou_money AS uc_money', 'is_use', 'valid_time')
                ->where('order_id', 0)
                ->where('user_id', $user_id);

            if ($can_use == true) {
                $model = $model->where('is_use', 0);
            }

            $model = BaseRepository::getToArrayGet($model);
            $model = BaseRepository::getArrayUnique($model, 'uc_id');
            $cou_id = BaseRepository::getKeyPluck($model, 'cou_id');

            $list = CouponDataHandleService::getCouponsDataList($cou_id);

            foreach ($cart_goods_group as $key => $row) {

                $ru_id = $row['seller_id'];
                $order_total = $row['order_total'];

                $sql = [
                    'where' => [
                        [
                            'name' => 'ru_id',
                            'value' => $ru_id
                        ],
                        [
                            'name' => 'review_status',
                            'value' => 3
                        ],
                        [
                            'name' => 'cou_type',
                            'value' => VOUCHER_GROUPBUY,
                            'condition' => '<>'
                        ]
                    ]
                ];

                $couponsList = BaseRepository::getArraySqlGet($list, $sql, 1);

                $cou_id = BaseRepository::getKeyPluck($couponsList, 'cou_id');

                if (!empty($cou_id)) {
                    $sql = [
                        'whereIn' => [
                            [
                                'name' => 'cou_id',
                                'value' => $cou_id
                            ]
                        ]
                    ];
                    $coupons_user = BaseRepository::getArraySqlGet($model, $sql);
                } else {
                    $coupons_user = [];
                }

                if (!empty($coupons_user)) {

                    $ru_id = BaseRepository::getKeyPluck($couponsList, 'ru_id');
                    $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

                    foreach ($coupons_user as $k => $item) {

                        $valid_time = $item['valid_time'] ?? 0;

                        $coupons = $couponsList[$item['cou_id']] ?? [];
                        $valid_type = $coupons['valid_type'] ?? 1;


                        $is_valid = 1; // 显示可用优惠券
                        if ($valid_type == 2) {
                            $is_valid = $time > $valid_time ? 0 : $is_valid;
                        } else {
                            $cou_start_time = $coupons['cou_start_time'] ?? 0;
                            $cou_end_time = $coupons['cou_end_time'] ?? 0;
                            $is_valid = $time >= $cou_start_time && $time <= $cou_end_time ? $is_valid : 0;
                        }

                        if ($is_valid == 1) {
                            $item = collect($item)->merge($coupons)->all();
                            $item['shop_name'] = $merchantList[$coupons['ru_id']]['shop_name'] ?? '';

                            $item['valid_type'] = $valid_type;
                            $coupons_user_all[] = $item;

                            // 免邮优惠券
                            if ($item['cou_type'] == VOUCHER_SHIPPING) {
                                if ($order_total >= $item['cou_man'] || $item['cou_man'] == 0) {
                                    $region_arr = $this->getCouponsRegionList($item['cou_id']);
                                    $cou_region = $region_arr['free_value'] ?? [];
                                    $cou_region = BaseRepository::getExplode($cou_region);

                                    /* 是否含有不支持免邮的地区 */
                                    if (($cou_region && isset($consignee['province']) && in_array($consignee['province'], $cou_region)) || $shipping_fee == 0) {
                                        unset($item);
                                        continue;
                                    }
                                }
                            }

                            // 可使用商品
                            $goods_ids = $row['goods_id'] ?? [];
                            $goods_ids = array_unique($goods_ids);

                            // 可使用分类
                            $goods_cats = $row['cat_id'] ?? [];
                            $goods_cats = array_unique($goods_cats);

                            if (!empty($goods_ids) && !empty($item['cou_goods'])) {
                                /**
                                 * 可使用商品 且商品总价 大于等于 优惠券使用门槛
                                 */
                                $cou_goods = BaseRepository::getExplode($item['cou_goods']);

                                $goodsIdList = [];
                                $couGoodsIdList = [];
                                foreach ($goods_ids as $goods_id) {
                                    //属性商品 数组被重新赋值
                                    $ex_arr = explode('_', $goods_id);
                                    $goodsIdList[] = $ex_arr[0] ?? 0;

                                    $couGoodsIdList[$goods_id]['goods_id'] = $ex_arr[0] ?? 0;
                                    $couGoodsIdList[$goods_id]['subtotal'] = $row['goods'][$goods_id]['subtotal'];
                                }

                                $intersectGoodsList = [];
                                if ($couGoodsIdList) {
                                    $sql = [
                                        'whereIn' => [
                                            [
                                                'name' => 'goods_id',
                                                'value' => $cou_goods
                                            ]
                                        ]
                                    ];
                                    $intersectGoodsList = BaseRepository::getArraySqlGet($couGoodsIdList, $sql);
                                    $cou_goods_prices = BaseRepository::getArraySum($intersectGoodsList, 'subtotal');
                                    $cou_goods_prices = $this->dscRepository->changeFloat($cou_goods_prices);
                                } else {
                                    $cou_goods_prices = 0;
                                }

                                if (!empty($goodsIdList) && !empty($intersectGoodsList)) {
                                    if ($cou_goods_prices >= $item['cou_man']) {
                                        $coupons_list[] = $item;
                                    }
                                }
                            } elseif (!empty($goods_cats) && !empty($item['spec_cat'])) {
                                /**
                                 * 可使用分类 且分类商品总价 大于等于 优惠券使用门槛
                                 */
                                $spec_cat = $this->getCouChildren($item['spec_cat']);
                                $spec_cat = BaseRepository::getExplode($spec_cat);
                                $cou_goods_prices = 0;
                                foreach ($goods_cats as $cat_id) {
                                    if (in_array($cat_id, $spec_cat)) {
                                        foreach ($row['goods'] as $key => $val) {
                                            if ($cat_id == $val['cat_id']) {
                                                $cou_goods_prices += $val['subtotal'];
                                            }
                                        }

                                        $cou_goods_prices = $this->dscRepository->changeFloat($cou_goods_prices);

                                        if ($cou_goods_prices >= $item['cou_man']) {
                                            $coupons_list[] = $item;
                                        }
                                    }
                                }
                            } else {
                                // 全部商品 且总价 大于等于 优惠券使用门槛
                                if ($order_total >= $item['cou_man']) {
                                    $coupons_list[] = $item;
                                }
                            }
                        }
                    }
                }
            }

            $coupons_list = empty($coupons_list) ? [] : BaseRepository::getArrayUnique($coupons_list);
        }

        // 不可使用优惠券： 取全部、可使用优惠券 数组差值
        $diff = ArrRepository::getDiffArrayByFilter($coupons_user_all, $coupons_list);

        $coupons_list_disabled = empty($diff) ? [] : array_values($diff);

        return [
            'coupons_user_all' => $coupons_user_all,
            'coupons_list' => $coupons_list,
            'coupons_list_disabled' => $coupons_list_disabled,
        ];
    }

    /**
     * 获取用户拥有的优惠券 默认返回所有用户所拥有的优惠券
     *
     * @param string $user_id 用户ID
     * @param bool $is_use 找出当前用户可以使用的
     * @param string $total 订单总价
     * @param array $cart_goods 商品信息
     * @param bool $user 用于区分是否会员中心里取数据(会员中心里的优惠券不能分组)
     * @param int $cart_ru_id
     * @param string $act_type
     * @param int $province
     * @return array
     * @throws \Exception
     */
    public function getUserCouponsList($user_id = '', $is_use = false, $total = '', $cart_goods = [], $user = true, $cart_ru_id = -1, $act_type = 'user', $province = 0)
    {
        $time = TimeRepository::getGmTime();

        //可使用的(平台用平台发的,商家用商家发的,当订单中混合了平台与商家的商品时,各自计算各自的商品总价是否达到各自发放的优惠券门槛,达到的话当前整个订单即可使用该优惠券)
        if ($is_use && isset($total) && $cart_goods) {
            $res = [];
            // 生成商家数据结构
            foreach ($cart_goods as $k => $v) {
                //过滤虚拟商品
                if (($v['get_goods']['is_real'] ?? 0) == 0 && ($v['get_goods']['extension_code'] ?? '') == 'virtual_card') {
                    continue;
                }
                $res[$v['ru_id']] = [
                    'order_total' => 0,
                    'seller_id' => null,
                    'goods_id' => '',
                    'cat_id' => '',
                    'goods' => [],
                ];
            }

            // 统计数据
            foreach ($cart_goods as $k => $v) {
                //过滤虚拟商品
                if (($v['get_goods']['is_real'] ?? 0) == 0 && ($v['get_goods']['extension_code'] ?? '') == 'virtual_card') {
                    continue;
                }

                $v['cat_id'] = $v['cat_id'] ?? 0;

                $v['subtotal'] = $v['goods_price'] * $v['goods_number'];
                $res[$v['ru_id']]['order_total'] += $v['goods_price'] * $v['goods_number'] - $v['dis_amount'];
                $res[$v['ru_id']]['seller_id'] = $v['ru_id'];
                $res[$v['ru_id']]['goods_id'] .= $v['goods_id'] . ",";
                $res[$v['ru_id']]['cat_id'] .= $v['cat_id'] . ",";
                $res[$v['ru_id']]['goods'][$v['goods_id']] = $v;
            }

            $arr = [];
            $couarr = [];

            if ($res) {
                foreach ($res as $key => $row) {
                    $row['goods_id'] = $this->dscRepository->delStrComma($row['goods_id']);
                    $row['cat_id'] = $this->dscRepository->delStrComma($row['cat_id']);

                    $coupons_user = CouponsUser::select('uc_id', 'cou_id', 'cou_money AS uc_money')
                        ->where('order_id', 0)
                        ->where('user_id', $user_id);

                    if ($cart_ru_id != -1) {
                        $coupons_user = $coupons_user->where('is_use', 0);
                    }

                    $where = [
                        'ru_id' => $row['seller_id'],
                        'order_total' => $row['order_total'],
                        'time' => $time
                    ];
                    $coupons_user = $coupons_user->whereHasIn('getCoupons', function ($query) use ($where) {

                        $whereTime = $where['time'];

                        $query->where('ru_id', $where['ru_id'])
                            ->where('cou_man', '<=', $where['order_total'])
                            ->where('review_status', 3)
                            ->where('cou_type', '<>', VOUCHER_GROUPBUY)
                            ->whereRaw("IF(valid_type > 1, receive_start_time <= '$whereTime' and receive_end_time >= '$whereTime', cou_start_time <= '$whereTime' and cou_end_time >= '$whereTime')")
                            ->where('status', COUPON_STATUS_EFFECTIVE);
                    });

                    $coupons_user = $coupons_user->groupBy('uc_id');
                    $coupons_user = BaseRepository::getToArrayGet($coupons_user);

                    $couarr[$key] = $coupons_user;

                    if ($couarr[$key]) {

                        $cou_id = BaseRepository::getKeyPluck($couarr[$key], 'cou_id');
                        $couponList = CouponDataHandleService::getCouponsDataList($cou_id);

                        $ru_id = BaseRepository::getKeyPluck($couponList, 'ru_id');
                        $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

                        foreach ($couarr[$key] as $ckey => $crow) {

                            $crow['get_coupons'] = $couponList[$crow['cou_id']] ?? [];

                            $crow = BaseRepository::getArrayMerge($crow, $crow['get_coupons']);
                            $couarr[$key][$ckey] = $crow;

                            $couarr[$key][$ckey]['shop_name'] = $merchantList[$crow['ru_id'] ?? 0]['shop_name'] ?? '';

                            if ($crow['cou_type'] == VOUCHER_SHIPPING) {
                                if ($province > 0) {
                                    $region_list = CouponsRegion::where('cou_id', $crow['cou_id'])->whereRaw("!FIND_IN_SET('$province', region_list)")->value('region_list');
                                } else {
                                    $region_list = CouponsRegion::where('cou_id', $crow['cou_id'])->value('region_list');
                                }

                                if ($region_list) {
                                    $region_list = !is_array($region_list) ? explode(",", $region_list) : $region_list;
                                    $region_list = Region::select('region_name')->whereIn('region_id', $region_list)->get();
                                    $region_list = $region_list ? collect($region_list)->flatten()->all() : [];

                                    $couarr[$key][$ckey]['region_list'] = $region_list ? implode(",", $region_list) : '';
                                } else {
                                    $couarr[$key][$ckey]['region_list'] = '';
                                }
                            }
                        }
                    }

                    $goods_ids = [];
                    if (isset($row['goods_id']) && $row['goods_id'] && !is_array($row['goods_id'])) {
                        $goods_ids = explode(",", $row['goods_id']);
                        $goods_ids = array_unique($goods_ids);
                    }

                    $goods_cats = [];
                    if (isset($row['cat_id']) && $row['cat_id'] && !is_array($row['cat_id'])) {
                        $goods_cats = explode(",", $row['cat_id']);
                        $goods_cats = array_unique($goods_cats);
                    }

                    if (($goods_ids || $goods_cats) && $couarr[$key]) {
                        foreach ($couarr[$key] as $rk => $rrow) {
                            if ($rrow['cou_goods']) {
                                $cou_goods = explode(",", $rrow['cou_goods']); //可使用优惠券商品
                                $cou_goods_prices = 0;
                                foreach ($goods_ids as $m => $n) {
                                    if (in_array($n, $cou_goods)) {
                                        $cou_goods_prices += $row['goods'][$n]['subtotal'];
                                        if ($cou_goods_prices >= $rrow['cou_man']) {
                                            $arr[] = $rrow;
                                            break;
                                        }
                                    }
                                }
                            } elseif ($rrow['spec_cat']) {
                                $spec_cat = $this->getCouChildren($rrow['spec_cat']);
                                $spec_cat = BaseRepository::getExplode($spec_cat);
                                $cou_goods_prices = 0;
                                foreach ($goods_cats as $m => $n) {
                                    if (in_array($n, $spec_cat)) {
                                        foreach ($row['goods'] as $key => $val) {
                                            if ($n == $val['cat_id']) {
                                                $cou_goods_prices += $val['subtotal'];
                                            }
                                        }
                                        if ($cou_goods_prices >= $rrow['cou_man']) {
                                            $arr[] = $rrow;
                                            continue;
                                        }
                                    }
                                }
                            } else {
                                $arr[] = $rrow;
                            }
                        }
                    }
                }
            }

            /* 去除重复 */
            $arr = BaseRepository::getArrayUnique($arr);
            return $arr;
        } else {
            if (!empty($user_id) && $user) {
                $user_id = !is_array($user_id) ? explode(",", $user_id) : $user_id;
                $couponsRes = CouponsUser::selectRaw("*, cou_money AS uc_money")->whereIn('user_id', $user_id);
            } elseif (!empty($user_id)) {
                $user_id = !is_array($user_id) ? explode(",", $user_id) : $user_id;
                $couponsRes = CouponsUser::selectRaw("*, cou_money AS uc_money")->whereIn('user_id', $user_id);
            } else {
                return [];
            }

            $where = [
                'act_type' => $act_type,
                'time' => $time
            ];
            $couponsRes = $couponsRes->whereHasIn('getCoupons', function ($query) use ($where) {

                $whereTime = $where['time'];

                $query = $query->where('review_status', 3)
                    ->where('cou_type', '<>', VOUCHER_GROUPBUY)
                    ->where('status', COUPON_STATUS_EFFECTIVE);
                if ($where['act_type'] == 'cart') {
                    $query->whereRaw("IF(valid_type > 1, receive_start_time <= '$whereTime' and receive_end_time >= '$whereTime', cou_start_time <= '$whereTime' and cou_end_time >= '$whereTime')");
                }
            });

            $couponsRes = $couponsRes->groupBy('uc_id');

            $couponsRes = BaseRepository::getToArrayGet($couponsRes);

            if ($couponsRes) {

                $cou_id = BaseRepository::getKeyPluck($couponsRes, 'cou_id');
                $couponList = CouponDataHandleService::getCouponsDataList($cou_id);

                $ru_id = BaseRepository::getKeyPluck($couponList, 'ru_id');
                $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

                foreach ($couponsRes as $key => $row) {

                    $add_time = $row['add_time'];
                    $row['get_coupons'] = $couponList[$row['cou_id']] ?? [];

                    $valid_type = $row['get_coupons']['valid_type'] ?? 1;

                    $row = $row['get_coupons'] ? array_merge($row, $row['get_coupons']) : $row;

                    if ($act_type != 'cart') {
                        $order = OrderInfo::select('order_sn', 'add_time', 'coupons AS order_coupons')->where('order_id', $row['order_id'])->first();
                        $order = $order ? $order->toArray() : [];
                        $row['order_sn'] = $order['order_sn'] ?? '';
                        $row['add_time'] = $order['add_time'] ?? '';
                        $row['order_coupons'] = $order['order_coupons'] ?? '';
                    }

                    $couponsRes[$key] = $row;

                    if ($valid_type == 2) {
                        $couponsRes[$key]['add_time'] = $add_time;
                    }

                    // 处理价格显示整数
                    $couponsRes[$key]['cou_money'] = intval($couponsRes[$key]['cou_money']);
                    $couponsRes[$key]['uc_money'] = intval($couponsRes[$key]['uc_money']);
                    $couponsRes[$key]['order_coupons'] = isset($couponsRes[$key]['order_coupons']) ? intval($couponsRes[$key]['order_coupons']) : '';

                    $couponsRes[$key]['shop_name'] = $merchantList[$row['ru_id'] ?? 0]['shop_name'] ?? '';

                    if ($row['cou_type'] == VOUCHER_SHIPPING) {
                        if ($province > 0) {
                            $region_list = CouponsRegion::where('cou_id', $row['cou_id'])->whereRaw("!FIND_IN_SET('$province', region_list)")->value('region_list');
                        } else {
                            $region_list = CouponsRegion::where('cou_id', $row['cou_id'])->value('region_list');
                        }

                        if ($region_list) {
                            $region_list = !is_array($region_list) ? explode(",", $region_list) : $region_list;
                            $region_list = Region::select('region_name')->whereIn('region_id', $region_list)->get();
                            $region_list = $region_list ? collect($region_list)->flatten()->all() : [];

                            $couponsRes[$key]['region_list'] = $region_list ? implode(",", $region_list) : '';
                        } else {
                            $couponsRes[$key]['region_list'] = '';
                        }
                    }
                }
            }

            /* 去除重复 */
            $couponsRes = BaseRepository::getArrayUnique($couponsRes);
            return $couponsRes;
        }
    }

    /**
     * 优惠券分类
     * @param string $cat
     * @return array
     */
    public static function getCouChildren($cat = '')
    {
        $cat = BaseRepository::getExplode($cat);
        $child_cat = app(CategoryService::class)->getCatListChildren($cat);
        $child_cat = array_unique($child_cat);

        return $child_cat;
    }

    /**
     * 领取优惠券
     *
     * @param int $cou_id
     * @param int $user_id
     * @return array
     * @throws \Exception
     */
    public function getCouponsReceive($cou_id = 0, $user_id = 0)
    {
        $result = [];

        $result['is_over'] = 0;

        //取出当前优惠券信息(未过期,剩余总数大于0)
        $cou_data = $this->getCouponsHaving($cou_id);

        //判断券是不是被领取完了
        if (!$cou_data) {
            return [
                'status' => 'error',
                'msg' => lang('common.lang_coupons_receive_failure')
            ];
        }

        //判断是否已经领取了,并且还没有使用(根据创建优惠券时设定的每人可以领取的总张数为准,防止超额领取)
        $cou_user_num = CouponsUser::where('is_delete', 0)->where('user_id', $user_id)->where('cou_id', $cou_id)->count();

        if ($cou_data['cou_user_num'] <= $cou_user_num) {
            return [
                'status' => 'error',
                'msg' => sprintf(lang('common.lang_coupons_user_receive'), $cou_data['cou_user_num'])
            ];
        } else {
            $result['is_over'] = 1;
        }

        //判断当前会员是否已经关注店铺
        if ($cou_data['cou_type'] == VOUCHER_SHOP_CONLLENT) {
            $rec_id = CollectStore::where('user_id', $user_id)->where('ru_id', $cou_data['ru_id'])->value('rec_id');
            if (empty($rec_id)) {
                //关注店铺
                $other = [
                    'user_id' => $user_id,
                    'ru_id' => $cou_data['ru_id'],
                    'add_time' => TimeRepository::getGmTime(),
                    'is_attention' => 1
                ];
                CollectStore::insert($other);
            }
        }
        //领券
        $userData = [
            'user_id' => $user_id,
            'cou_money' => $cou_data['cou_money'],
            'cou_id' => $cou_id,
            'uc_sn' => CommonRepository::couponSn()
        ];

        $uc_id = CouponsUser::insertGetId($userData);

        if ($uc_id) {
            return [
                'status' => 'ok',
                'msg' => lang('common.lang_coupons_receive_succeed')
            ];
        }
    }

    /**
     * 获取用户优惠券发放领取情况
     * @param int $cou_id 优惠券ID
     * @return array
     */
    public function get_coupons_info2($cou_id = 0)
    {
        $filter['record_count'] = CouponsUser::where('is_delete', 0)->where('cou_id', $cou_id)->count();
        /* 分页大小 */
        $filter = page_and_size($filter);

        $row = CouponsUser::selectRaw("*, cou_money AS uc_money")->where('cou_id', $cou_id)->where('is_delete', 0);

        $row = $row->with([
            'getCoupons' => function ($query) {
                $query->select('cou_id', 'cou_money');
            }
        ]);

        $row = $row->orderByDesc('uc_id');

        if ($filter['start'] > 0) {
            $row = $row->skip($filter['start']);
        }

        if ($filter['page_size'] > 0) {
            $row = $row->take($filter['page_size']);
        }

        $row = BaseRepository::getToArrayGet($row);
        if ($row) {

            $couIdList = BaseRepository::getKeyPluck($row, 'cou_id');
            $couList = CouponDataHandleService::getCouponsDataList($couIdList, ['cou_id', 'cou_money']);

            $orderIdList = BaseRepository::getKeyPluck($row, 'order_id');
            $orderList = OrderDataHandleService::orderDataList($orderIdList, ['order_id', 'order_sn']);

            $userIdList = BaseRepository::getKeyPluck($row, 'user_id');
            $userList = UserDataHandleService::userDataList($userIdList, ['user_id', 'user_name']);

            foreach ($row as $key => $val) {

                $cou = $couList[$val['cou_id']] ?? [];

                $val = BaseRepository::getArrayMerge($val, $cou);
                $row[$key]['cou_money'] = !empty($val['uc_money']) ? $val['uc_money'] : $val['cou_money'];

                $row[$key]['valid_end_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $val['valid_time']);

                //使用时间
                if ($val['is_use_time']) {
                    $row[$key]['is_use_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $val['is_use_time']);
                } else {
                    $row[$key]['is_use_time'] = '';
                }

                //订单号
                if ($val['order_id']) {
                    $row[$key]['order_sn'] = $orderList[$val['order_id']]['order_sn'];
                }

                //所属会员
                if ($val['user_id']) {
                    $row[$key]['user_name'] = $userList[$val['user_id']]['user_name'];

                    $show_mobile = config('shop.show_mobile') ?? 0;
                    if ($show_mobile) {
                        $row[$key]['user_name'] = $this->dscRepository->stringToStar($row[$key]['user_name']);
                    }
                }
            }
        }
        return ['item' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }

    /**
     * 等级重组
     *
     * @param $rank_list
     * @param string $cou_ok_user
     * @return array
     */
    public function get_rank_arr($rank_list, $cou_ok_user = '')
    {
        $cou_ok_user = !empty($cou_ok_user) ? explode(",", $cou_ok_user) : [];

        $arr = [];
        if ($rank_list) {
            foreach ($rank_list as $key => $row) {
                $arr[$key]['rank_id'] = $key;
                $arr[$key]['rank_name'] = $row;

                if ($cou_ok_user && in_array($key, $cou_ok_user)) {
                    $arr[$key]['is_checked'] = 1;
                } else {
                    $arr[$key]['is_checked'] = 0;
                }
            }
        }

        return $arr;
    }

    /**
     * 获取优惠券类型信息(不带分页)
     *
     * @param string $cou_type 优惠券类型 1:注册送,2:购物送,3:全场送,4:会员送  默认返回所有类型数据
     * @return array
     */
    public function getCouponsTypeInfoNoPage($cou_type = '1,2,3,4')
    {
        if (empty($cou_type)) {
            return [];
        }

        $cou_type = BaseRepository::getExplode($cou_type);

        //获取格林尼治时间戳(用于判断优惠券是否已过期)
        $time = TimeRepository::getGmTime();

        $arr = Coupons::where('review_status', 3)
            ->whereIn('cou_type', $cou_type)
            ->whereRaw("IF(valid_type > 1, receive_start_time <= '$time' and receive_end_time >= '$time', cou_start_time <= '$time' and cou_end_time >= '$time')")
            ->where('status', COUPON_STATUS_EFFECTIVE);

        $arr = BaseRepository::getToArrayGet($arr);

        //生成优惠券编号
        if ($arr) {
            foreach ($arr as $k => $v) {
                $arr[$k]['uc_sn'] = CommonRepository::couponSn();
            }
        }

        return $arr;
    }
}
