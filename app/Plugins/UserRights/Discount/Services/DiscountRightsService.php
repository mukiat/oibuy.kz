<?php

namespace App\Plugins\UserRights\Discount\Services;

use App\Models\OrderInfoMembershipCard;
use App\Models\PayLog;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\StrRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Goods\GoodsAttrService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Goods\GoodsProdutsService;
use App\Services\UserRights\UserRightsService;

/**
 * 特价权益
 * Class DiscountRightsService
 * @package App\Plugins\UserRights\Discount\Services
 */
class DiscountRightsService
{
    protected $dscRepository;
    protected $userRightsService;

    public function __construct(
        DscRepository $dscRepository,
        UserRightsService $userRightsService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->userRightsService = $userRightsService;
    }

    /**
     * 商品详情计算分销权益卡绑定的会员特价权益（取最低折扣）
     * @param string $code
     * @param array $goods
     * @param int $goods_num
     * @param array $attr_str
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @return bool|float|int|mixed|string
     */
    public function membershipCardDiscount($code = '', $goods = [], $goods_num = 1, $attr_str = [], $warehouse_id = 0, $area_id = 0, $area_city = 0)
    {
        if (empty($code)) {
            return 0;
        }

        // 会员特价权益
        $discountRights = $this->userRightsService->userRightsInfo($code);

        // 商家商品禁用会员权益折扣
        if (isset($goods['user_id']) && $goods['user_id'] > 0 && isset($goods['is_discount']) && $goods['is_discount'] == 0) {
            $discount = 1;
        } else {
            $discount = 0;
            if (!empty($discountRights)) {
                if (isset($discountRights['enable']) && isset($discountRights['install']) && $discountRights['enable'] == 1 && $discountRights['install'] == 1) {
                    $rightsList = $this->userRightsService->getCardRights($discountRights['id']);
                    if ($rightsList) {

                        // 获取最低折扣
                        $discount_value_list = $this->getDiscountValue($rightsList);

                        $configure = collect($discount_value_list)->firstWhere('name', 'user_discount');
                        $membership_card_discount = $configure['value'] ?? 100;

                        /**
                         * 计算折扣价格
                         */
                        $discount = $membership_card_discount / 100;
                    }
                }
            }
        }

        if ($discount > 0) {

            // 计算折扣后价格
            $new_goods = $this->getGoodsMembershipCardPrice($goods, $discount);
            $shop_price = $new_goods['shop_price'] ?? 0;
            $goods['promote_price'] = $new_goods['promote_price'] ?? 0;

            if (!empty($attr_str)) {
                $shop_price = $this->getFinalMembershipCardPrice($goods, $discount, $goods_num, $attr_str, $warehouse_id, $area_id, $area_city);
            }

            return $membership_card_discount_price = $shop_price > 0 ? StrRepository::priceFormat($shop_price) : 0;
        }

        return 0;
    }

    /**
     * 统一处理商品价格
     *
     * @param array $goods
     * @param int $discount
     * @return mixed
     */
    public function getGoodsMembershipCardPrice($goods = [], $discount = 1)
    {
        if (empty($goods)) {
            return 0;
        }

        // 优先取商品原价
        if (isset($goods['shop_price_original']) && $goods['shop_price_original']) {
            $goods['shop_price'] = $goods['shop_price_original'];
        }
        // 不带格式 商品促销价
        if (isset($goods['promote_price_org']) && $goods['promote_price_org']) {
            $goods['promote_price'] = $goods['promote_price_org'];
        }

        $price = [
            'model_price' => isset($goods['model_price']) ? $goods['model_price'] : 0,
            'user_price' => isset($goods['get_member_price']['user_price']) ? $goods['get_member_price']['user_price'] : 0,
            'percentage' => isset($goods['get_member_price']['percentage']) ? $goods['get_member_price']['percentage'] : 0,
            'warehouse_price' => isset($goods['get_warehouse_goods']['warehouse_price']) ? $goods['get_warehouse_goods']['warehouse_price'] : 0,
            'region_price' => isset($goods['get_warehouse_area_goods']['region_price']) ? $goods['get_warehouse_area_goods']['region_price'] : 0,
            'shop_price' => isset($goods['shop_price']) ? $goods['shop_price'] : 0,
            'warehouse_promote_price' => isset($goods['get_warehouse_goods']['warehouse_promote_price']) ? $goods['get_warehouse_goods']['warehouse_promote_price'] : 0,
            'region_promote_price' => isset($goods['get_warehouse_area_goods']['region_promote_price']) ? $goods['get_warehouse_area_goods']['region_promote_price'] : 0,
            'promote_price' => isset($goods['promote_price']) ? $goods['promote_price'] : 0,
            'integral' => isset($goods['integral']) ? $goods['integral'] : 0,
            'wpay_integral' => isset($goods['get_warehouse_goods']['pay_integral']) ? $goods['get_warehouse_goods']['pay_integral'] : 0,
            'apay_integral' => isset($goods['get_warehouse_area_goods']['pay_integral']) ? $goods['get_warehouse_area_goods']['pay_integral'] : 0,
            'goods_number' => isset($goods['goods_number']) ? $goods['goods_number'] : 0,
            'wg_number' => isset($goods['get_warehouse_goods']['region_number']) ? $goods['get_warehouse_goods']['region_number'] : 0,
            'wag_number' => isset($goods['get_warehouse_area_goods']['region_number']) ? $goods['get_warehouse_area_goods']['region_number'] : 0,
        ];

        // 商家商品禁用会员权益折扣
        if (isset($goods['user_id']) && $goods['user_id'] > 0 && isset($goods['is_discount']) && $goods['is_discount'] == 0) {
            $discount = 1;
        }

        /* 会员等级价格 */
        if (isset($price['user_price']) && $price['user_price'] > 0) {
            if (isset($price['percentage']) && $price['percentage'] == 1) {
                $shop_price = $price['shop_price'] * $price['user_price'] / 100;
            } else {
                $shop_price = $price['user_price'];
            }
        } else {
            // 仓库价格
            if (isset($price['warehouse_price']) && $price['model_price'] == 1) {
                $shop_price = $price['warehouse_price'] * $discount;
            } elseif (isset($price['region_price']) && $price['model_price'] == 2) {
                // 地区价格
                $shop_price = $price['region_price'] * $discount;
            } else {
                $shop_price = $price['shop_price'] * $discount;
            }
        }

        $new_goods['shop_price'] = number_format($shop_price, 2, '.', '');

        /* 促销价 */
        if (isset($price['warehouse_promote_price']) && $price['model_price'] == 1) {
            $promote_price = $price['warehouse_promote_price'];
        } elseif (isset($price['region_promote_price']) && $price['model_price'] == 2) {
            $promote_price = $price['region_promote_price'];
        } else {
            $promote_price = $price['promote_price'] ?? 0;
        }

        $new_goods['promote_price'] = number_format($promote_price, 2, '.', '');

        /* 消费积分 */
        if (isset($price['wpay_integral']) && $price['model_price'] == 1) {
            $integral = $price['wpay_integral'];
        } elseif (isset($price['apay_integral']) && $price['model_price'] == 2) {
            $integral = $price['apay_integral'];
        } else {
            $integral = $price['integral'] ?? 0;
        }

        $new_goods['integral'] = intval($integral);

        /* 库存 */
        if (isset($price['wg_number']) && $price['model_price'] == 1) {
            $goods_number = $price['wg_number'];
        } elseif (isset($price['wag_number']) && $price['model_price'] == 2) {
            $goods_number = $price['wag_number'];
        } else {
            $goods_number = $price['goods_number'] ?? 0;
        }

        $new_goods['goods_number'] = intval($goods_number);

        // 商品原信息
        $new_goods['model_price'] = $price['model_price'] ?? 0;
        $new_goods['percentage'] = $price['percentage'] ?? 0;

        return $new_goods;
    }

    /**
     * 获取会员权益折扣
     * @param array $list
     * @return array
     */
    protected function getDiscountValue($list = [])
    {
        $value_list = [];
        if (!empty($list)) {
            foreach ($list as $key => $val) {

                // 取权益配置
                if (!empty($val['rights_configure'])) {
                    foreach ($val['rights_configure'] as $k => $item) {
                        $val['discount_value'][$k]['type'] = $item['type'];
                        $val['discount_value'][$k]['name'] = $item['name'];
                        // 会员特价权益折扣
                        if ($item['name'] == 'user_discount') {
                            $val['discount_value'][$k]['value'] = floatval($item['value']);
                        }
                    }

                    $value_list[] = $val['discount_value'];
                }
            }
        }

        return self::transformDiscountValue($value_list);
    }

    /**
     * 分销权益卡绑定的会员特价权益（取最低折扣）
     * @param array $discount_value
     * @return array
     */
    protected static function transformDiscountValue($discount_value = [])
    {
        if (empty($discount_value)) {
            return [];
        }

        // 多维合并成一唯
        $collapsed = collect($discount_value)->collapse();

        // 按折扣name值分组
        $group = $collapsed->mapToGroups(function ($item, $key) {
            return [$item['name'] => $item['value']];
        })->toArray();

        $list = [];
        if (!empty($group)) {
            // 取折扣name分组里的 最小值
            foreach ($group as $name => $value) {
                $min_value = collect($value)->min();
                $list[] = [
                    'name' => $name,
                    'value' => $min_value
                ];
            }
        }

        return $list;
    }

    /**
     * 取得商品权益卡最终优惠价格
     *
     * @param array $goods
     * @param int $discount
     * @param int $goods_num
     * @param array $property
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @return int|mixed
     */
    public function getFinalMembershipCardPrice($goods = [], $discount = 1, $goods_num = 1, $property = [], $warehouse_id = 0, $area_id = 0, $area_city = 0)
    {
        if (empty($goods)) {
            return 0;
        }

        $final_price = 0; //商品最终购买价格
        $volume_price = 0; //商品优惠价格
        $promote_price = 0; //商品促销价格
        $user_price = 0; //商品会员价格
        $spec_price = 0;

        //如果需要加入规格价格
        if (!empty($property)) {
            $warehouse_area['warehouse_id'] = $warehouse_id;
            $warehouse_area['area_id'] = $area_id;
            $warehouse_area['area_city'] = $area_city;
            $spec_price = app(GoodsAttrService::class)->specPrice($property, $goods['goods_id'], $warehouse_area);
        }

        //取得商品优惠价格列表
        $price_list = app(GoodsCommonService::class)->getVolumePriceList($goods['goods_id']);
        if (!empty($price_list)) {
            foreach ($price_list as $value) {
                if ($goods_num >= $value['number']) {
                    $volume_price = $value['price'];
                }
            }
        }

        $time = TimeRepository::getGmTime();
        $now_promote = 0;

        //当前商品正在促销时间内
        if ($time >= $goods['promote_start_date'] && $time <= $goods['promote_end_date'] && $goods['is_promote']) {
            $now_promote = 1;
        }

        /* 计算商品的属性促销价格 */
        if ($property && config('shop.add_shop_price', 0) == 0) {
            $goods['product_promote_price'] = app(GoodsProdutsService::class)->goodsPropertyPrice($goods['goods_id'], $property, $warehouse_id, $area_id, $area_city, 'product_promote_price');
        }

        /* 计算商品的促销价格 */
        if (isset($goods['product_promote_price']) && $goods['product_promote_price'] > 0) {
            $promote_price = $goods['product_promote_price'];
        } else {
            $promote_price = $goods['promote_price'] ?? 0;
        }

        //取得商品会员价格列表
        if (!empty($spec_price) && config('shop.add_shop_price', 0) == 0) {

            /**
             * 会员等级价格与属性价关系
             * 1. 开启会员价格后 有会员等级价 优先取会员等级价; 若设置 百分比, 取属性价*会员等级百分比后价格
             * 2. 开启会员价格后 有会员等级价 取会员等级价与属性价 最小值
             * 3. 开启会员价格后 无会员等级价 取属性价*会员等级折扣
             * 4. 禁用会员价格后 取 属性价，有属性促销价格 则优先取 属性促销价
             */
            // 商家商品禁用会员权益折扣
            if (isset($goods['user_id']) && $goods['user_id'] > 0 && isset($goods['is_discount']) && $goods['is_discount'] == 0) {
                $discount = 1;
            } else {
                $price = [];
                $price['user_price'] = $goods['get_member_price']['user_price'] ?? 0;
                $price['percentage'] = $goods['get_member_price']['percentage'] ?? 0;

                if (isset($price['user_price']) && $price['user_price'] > 0) {
                    // 会员价格
                    if (isset($price['percentage']) && $price['percentage'] == 1) {
                        $price_user_price = $spec_price * $price['user_price'] / 100; // 百分比
                    } else {
                        $price_user_price = $price['user_price']; // 固定价格
                    }

                    /* 取 会员等级价 与 属性价 取小值*/
                    $price_user_price = min($price_user_price, $spec_price);
                }
            }

            if (isset($price_user_price) && !empty($price_user_price)) {
                $user_price = $price_user_price;
            } else {
                // 无会员等级价 有属性促销价格 则优先取 属性促销价
                if ($now_promote == 1) {
                    $user_price = $promote_price;
                } else {
                    // 无会员等级价 取 属性价 * 会员等级折扣
                    $user_price = $spec_price * $discount;
                }
            }

        } else {
            $user_price = $goods['shop_price'];
        }

        //比较商品的促销价格，会员价格，优惠价格
        if (empty($volume_price) && $now_promote == 0) {
            //如果优惠价格，促销价格都为空则取会员价格
            $final_price = $user_price;
        } elseif (!empty($volume_price) && $now_promote == 0) {
            //如果优惠价格为空时不参加这个比较。
            $final_price = min($volume_price, $user_price);
        } elseif (empty($volume_price) && $now_promote == 1) {
            //如果促销价格为空时不参加这个比较。
            $final_price = min($promote_price, $user_price);
        } elseif (!empty($volume_price) && $now_promote == 1) {
            //取促销价格，会员价格，优惠价格最小值
            $final_price = min($volume_price, $promote_price, $user_price);
        } else {
            $final_price = $user_price;
        }

        // 商品设置SKU价格模式：商品价格 + 属性货品价格  然后再计算折扣
        if (!empty($property) && config('shop.add_shop_price', 0) == 1) {
            $final_price += $spec_price;

            // 商家商品禁用会员权益折扣
            if (isset($goods['user_id']) && $goods['user_id'] > 0 && isset($goods['is_discount']) && $goods['is_discount'] == 0) {
                $discount = 1;
            }

            $final_price = $final_price * $discount;
        }

        // 返回商品最终价格
        return $final_price;
    }

    /**
     * 订单结算页 已绑定会员特价权益的会员卡（最低优惠）  领取类型 免费领取、在线支付
     *
     * @param string $code
     * @param int $user_id
     * @param array $cart_goods
     * @return array
     */
    public function orderMembershipCardInfo($code = '', $user_id = 0, $cart_goods = [])
    {
        if (empty($code) || $code != 'discount') {
            return [];
        }

        // 条件1. 未购买或已过期权益卡的会员
        $drp_shop = \App\Modules\Drp\Models\DrpShop::query()->where('user_id', $user_id)->where('membership_card_id', '>', 0)->select('id', 'membership_status', 'membership_card_id')->first();
        $drp_shop = $drp_shop ? $drp_shop->toArray() : [];

        if (empty($drp_shop) || empty($drp_shop['membership_status'])) {
        } else {
            return [];
        }

        // 条件3. 会员权益卡已绑定【会员特价】权益
        $discountRights = $this->userRightsService->userRightsInfo($code);
        if (empty($discountRights)) {
            return [];
        }

        $discount = 0;

        $membership_card_info = [];

        if (!empty($discountRights)) {
            if (isset($discountRights['enable']) && isset($discountRights['install']) && $discountRights['enable'] == 1 && $discountRights['install'] == 1) {
                // 条件4. 已绑定会员特价权益的会员卡（最低折扣优惠）  领取类型 免费领取、在线支付
                $receive_type_arr = ['free', 'buy'];
                $rightsList = $this->userRightsService->getCardRightsByReceiveType($discountRights['id'], $receive_type_arr);

                if (empty($rightsList)) {
                    return [];
                }

                if ($rightsList) {
                    // 获取最低折扣
                    $discount_value_list = $this->getDiscountValue($rightsList);

                    // 二维数组取最低折扣
                    $configure = collect($discount_value_list)->firstWhere('name', 'user_discount');
                    $membership_card_discount = $configure['value'] ?? 100;

                    /**
                     * 计算折扣价格
                     */
                    $discount = $membership_card_discount / 100;

                    $membership_card = [];
                    foreach ($rightsList as $k => $val) {

                        // 取权益配置
                        $rights_configure = [];
                        if (!empty($val['rights_configure'])) {
                            foreach ($val['rights_configure'] as $j => $item) {
                                // 会员特价权益折扣
                                if ($item['name'] == 'user_discount') {
                                    $rights_configure[] = floatval($item['value']);
                                }
                            }
                        }

                        // 取最小折扣权益卡详细信息
                        if ($membership_card_discount && in_array($membership_card_discount, $rights_configure)) {
                            $membership_card[] = $val['user_membership_card'];
                        }
                    }

                    if (!empty($membership_card)) {
                        // 二维数组转一维 获取取一张权益卡信息
                        $membership_card = collect($membership_card)->collapse()->all();
                        $membership_card_info['name'] = $membership_card['name'] ?? '';
                        $membership_card_info['order_membership_card_id'] = $membership_card['id'] ?? 0;

                        /**
                         * 免费领取、在线支付同时设置 优先取免费领取
                         */
                        // 购买权益卡金额
                        $membership_card_buy_money = null;
                        if (!empty($membership_card['receive_value'])) {
                            if (isset($membership_card['receive_value']['free'])) {
                                $membership_card_buy_money = 0;
                            } elseif (isset($membership_card['receive_value']['buy'])) {
                                $membership_card_buy_money = $membership_card['receive_value']['buy']['value'];
                            }
                        }

                        if (is_null($membership_card_buy_money)) {
                            return [];
                        }

                        // 购买会员权益卡金额
                        $membership_card_info['membership_card_buy_money'] = floatval($membership_card_buy_money);
                        $membership_card_info['membership_card_buy_money_formated'] = $this->dscRepository->getPriceFormat($membership_card_buy_money);

                        // 已绑定的权益列表
                        $membership_card_rights_list = app(\App\Modules\Drp\Services\RightsCardService::class)->cardInfo($membership_card['id']);
                        $membership_card_info['user_membership_card_rights_list'] = $membership_card_rights_list['user_membership_card_rights_list'] ?? [];
                    }
                }
            }
        }

        if ($discount > 0 && !empty($membership_card_info)) {

            // 订单商品当前价格总和
            $shop_price_amount = 0;
            // 订单商品折扣后价格总和
            $membership_card_price_amount = 0;

            // 订单商品分别权益折扣
            $membership_card_order_goods = [];

            // 权益卡购买指定商品
            $goods_membership_card_id = [];

            // 条件2. 判断订单商品必须支持会员特价权益（商家商品未禁用参与会员特价权益即可）
            if (isset($cart_goods['get_goods_list']) && !empty($cart_goods['get_goods_list'])) {
                foreach ($cart_goods['get_goods_list'] as $k => $item) {

                    // 权益卡购买指定商品
                    if (isset($item['membership_card_id']) && $item['membership_card_id'] > 0) {
                        $goods_membership_card_id[] = $item['membership_card_id'];
                    }

                    // 商家商品禁用会员权益折扣
                    if (isset($item['ru_id']) && $item['ru_id'] > 0 && isset($item['is_discount']) && $item['is_discount'] == 0) {
                        continue;
                    }

                    // 购物车商品信息
                    $goods_num = $item['goods_number']; // 商品数量
                    $warehouse_id = $item['warehouse_id'] ?? 0;
                    $area_id = $item['area_id'] ?? 0;
                    $area_city = $item['area_city'] ?? 0;
                    $attr_str = $item['goods_attr_id'] ?? 0;

                    /**
                     * 计算订单商品价格 有属性价优先用属性价等
                     */

                    // 1. 商品原信息（价格）
                    $goods = $item['get_goods'] ?? [];

                    // 2. 开通会员权益卡后 商品信息（价格）
                    $new_goods = $this->getGoodsMembershipCardPrice($goods, $discount);
                    $shop_price = $new_goods['shop_price'] ?? 0;
                    $goods['promote_price'] = $new_goods['promote_price'] ?? 0;

                    if (!empty($attr_str)) {
                        // 开通会员权益卡后 商品属性价
                        $shop_price = $this->getFinalMembershipCardPrice($goods, $discount, $goods_num, $attr_str, $warehouse_id, $area_id, $area_city);
                    }
                    $shop_price = round($shop_price, 2);

                    // 订单商品当前价格总和（含会员等级价格）
                    $shop_price_amount += $item['goods_price'] * $goods_num;
                    // 订单商品折扣后价格总和（含高级会员等级价格）
                    $membership_card_price_amount += $shop_price * $goods_num;

                    // 订单商品分别计算权益再减折扣
                    $membership_card_order_goods[$k]['goods_id'] = $item['goods_id'];
                    $membership_card_order_goods[$k]['membership_card_discount_price'] = round($item['goods_price'] * $goods_num - $shop_price * $goods_num, 2);
                }
            } else {
                return [];
            }

            if (!empty($goods_membership_card_id)) {
                // 购买指定商品结算页不需要显示开通高级会员
                return [];
            }

            // 开通购买会员权益卡 再减价格 = 订单商品当前价格总和 - 订单商品折扣后价格总和
            $membership_card_discount_price = $membership_card_price_amount > 0 ? round($shop_price_amount - $membership_card_price_amount, 2) : 0;

            // 订单商品分别权益折扣
            $order_membership_card_id = $membership_card_info['order_membership_card_id'] ?? 0;
            $cache_id = 'membership_card_order_goods' . $user_id . $order_membership_card_id;
            if (!empty($membership_card_order_goods)) {
                $membership_card_info['membership_card_order_goods'] = $membership_card_order_goods;
                cache()->forever($cache_id, $membership_card_order_goods);
            } else {
                cache()->forget($cache_id);
            }

            $membership_card_info['membership_card_discount'] = $discount * 10; // 几折
            $membership_card_info['shop_price_amount'] = $shop_price_amount;
            $membership_card_info['membership_card_price_amount'] = $membership_card_price_amount;
            $membership_card_info['membership_card_discount_price'] = floatval($membership_card_discount_price);
            $membership_card_info['membership_card_discount_price_formated'] = $this->dscRepository->getPriceFormat($membership_card_discount_price);
        }

        return $membership_card_info;
    }

    /**
     * 订单结算页 会员权益卡开通使用切换
     *
     * @param int $order_membership_card_id
     * @param int $user_id
     * @param array $total
     * @return array
     */
    public function changeMembershipCard($order_membership_card_id = 0, $user_id = 0, $total = [])
    {
        if (empty($user_id)) {
            return $total;
        }

        $total['success_type'] = 0;

        $total['bonus_money'] = $total['bonus_money'] ?? 0;
        $total['coupons_money'] = $total['coupons_money'] ?? 0;
        $total['vc_dis'] = isset($total['vc_dis']) ? round($total['vc_dis'] / 10, 2) : 1;
        $total['integral_money'] = isset($total['integral_money']) ? round($total['integral_money'], 2) : 0;
        $total['card'] = isset($total['card']) && $total['card'] ? floatval($total['card']) : 0;
        $total['card_money'] = isset($total['card_money']) && $total['card_money'] ? floatval($total['card_money']) : 0;
        $total['discount'] = $total['discount'] ?? 0;
        $total['bonus_id'] = isset($total['bonus_id']) && $total['bonus_id'] ? $total['bonus_id'] : 0; //红包id
        $total['coupons_id'] = $total['coupons_id'] ?? 0; //优惠券id
        $total['value_card_id'] = $total['value_card_id'] ?? 0; //储值卡id
        $total['surplus'] = $total['surplus'] ?? 0; //余额

        $amount = $total['goods_price'] - $total['bonus_money'] - $total['discount'] - $total['integral_money'] - $total['coupons_money'] - $total['card_money'] - $total['surplus'];
        $amount = $amount > 0 ? $amount : 0;

        if (CROSS_BORDER === true) {
            // 跨境多商户
            $total['amount'] -= $total['rate_price'];
        }

        // 开通购买会员权益卡 再减价格
        $total['membership_card_discount_price'] = $total['membership_card_discount_price'] ?? 0;

        // 选中
        if ($order_membership_card_id > 0 && $total['membership_card_discount_price'] > 0) {

            // 条件1. 未购买或已过期权益卡的会员
            $drp_shop = \App\Modules\Drp\Models\DrpShop::query()->where('user_id', $user_id)->where('membership_card_id', '>', 0)->select('id', 'membership_status', 'membership_card_id')->first();
            $drp_shop = $drp_shop ? $drp_shop->toArray() : [];
            if (empty($drp_shop) || empty($drp_shop['membership_status'])) {

                // 已绑定的权益列表
                $membership_card_rights_list = app(\App\Modules\Drp\Services\RightsCardService::class)->cardInfo($order_membership_card_id);

                if ($membership_card_rights_list) {
                    $total['success_type'] = 1;

                    /**
                     * 会员权益卡购买金额+（订单商品当前价格*购买数量-会员等级折扣金额-促销折扣-优惠券-红包-积分抵扣+运费-储值卡金额-余额抵扣金额）= 应付总额
                     */

                    // 购买权益卡金额
                    $membership_card_buy_money = null;

                    $code_arr = [];
                    $receive_type_arr = ['free', 'buy'];
                    if (isset($membership_card_rights_list['receive_value']) && !empty($membership_card_rights_list['receive_value'])) {
                        foreach ($membership_card_rights_list['receive_value'] as $k => $item) {
                            if (in_array($item['type'], $receive_type_arr)) {
                                if ($item['type'] == 'buy') {
                                    $item['value'] = floatval($item['value']);
                                }
                                $code_arr[$item['type']] = $item;
                            }
                        }
                    }

                    if (isset($code_arr['free'])) {
                        $membership_card_buy_money = 0;
                    } elseif (isset($code_arr['buy'])) {
                        $membership_card_buy_money = $code_arr['buy']['value'];
                    }

                    if (is_null($membership_card_buy_money)) {
                        return $total;
                    }

                    $total['membership_card_buy_money'] = $membership_card_buy_money;

                    $total['amount'] = $amount > $total['membership_card_discount_price'] ? $amount - $total['membership_card_discount_price'] : 0;
                    $total['amount'] += $total['membership_card_buy_money'];

                    $total['order_membership_card_id'] = $order_membership_card_id;
                }
            } else {
                $total['order_membership_card_id'] = 0;
            }
        } else {
            // 取消
            $total['amount'] = $amount;

            $total['order_membership_card_id'] = 0;
        }

        if (CROSS_BORDER === true) {
            // 跨境多商户
            $total['amount'] += $total['rate_price'];
        }

        $total['amount'] = round($total['amount'], 2);

        $total['membership_card_buy_money_formated'] = empty($total['membership_card_buy_money']) ? null : $this->dscRepository->getPriceFormat($total['membership_card_buy_money']);
        $total['membership_card_discount_price_formated'] = $this->dscRepository->getPriceFormat($total['membership_card_discount_price']);

        $total['amount_formated'] = $this->dscRepository->getPriceFormat($total['amount']);

        $total['card_formated'] = $this->dscRepository->getPriceFormat($total['card_money']);
        $total['card_money_formated'] = $this->dscRepository->getPriceFormat($total['card_money']);

        return $total;
    }

    /**
     * 订单提交 开通购买会员权益卡验证
     *
     * @param int $user_id
     * @param int $order_membership_card_id
     * @return array
     * @throws \Exception
     */
    public function getMemberCardInfo($user_id = 0, $order_membership_card_id = 0)
    {
        if (empty($user_id) || empty($order_membership_card_id)) {
            return [];
        }

        // 条件1. 未购买或已过期权益卡的会员
        $drp_shop = \App\Modules\Drp\Models\DrpShop::query()->where('user_id', $user_id)->where('membership_card_id', '>', 0)->select('id', 'membership_status', 'membership_card_id')->first();
        $drp_shop = $drp_shop ? $drp_shop->toArray() : [];

        if (empty($drp_shop) || empty($drp_shop['membership_status'])) {
        } else {
            return [];
        }

        $membership_card_rights_list = app(\App\Modules\Drp\Services\RightsCardService::class)->cardInfo($order_membership_card_id);

        if ($membership_card_rights_list) {

            // 购买权益卡金额
            $membership_card_buy_money = null;

            $code_arr = [];
            $receive_type_arr = ['free', 'buy'];
            if (isset($membership_card_rights_list['receive_value']) && !empty($membership_card_rights_list['receive_value'])) {
                foreach ($membership_card_rights_list['receive_value'] as $k => $item) {
                    if (in_array($item['type'], $receive_type_arr)) {
                        if ($item['type'] == 'buy') {
                            $item['value'] = floatval($item['value']);
                        }
                        $code_arr[$item['type']] = $item;
                    }
                }
            }

            if (isset($code_arr['free'])) {
                $membership_card_buy_money = 0;
            } elseif (isset($code_arr['buy'])) {
                $membership_card_buy_money = $code_arr['buy']['value'];
            }

            if (is_null($membership_card_buy_money)) {
                return [];
            }

            $cache_id = 'membership_card_order_goods' . $user_id . $order_membership_card_id;
            $membership_card_order_goods = cache($cache_id);
            if (!is_null($membership_card_order_goods)) {
                $membership_card_info['membership_card_order_goods'] = $membership_card_order_goods;
            }

            $membership_card_info['membership_card_buy_money'] = $membership_card_buy_money;
            $membership_card_info['membership_card_rights_list'] = $membership_card_rights_list;

            return $membership_card_info;
        }

        return [];
    }

    /**
     * 订单提交 开通购买会员权益卡记录
     *
     * @param array $order
     * @param array $order_membership_card
     * @return bool
     * @throws \Exception
     */
    public static function orderBuyMembershipCard($order = [], $order_membership_card = [])
    {
        if (empty($order)) {
            return false;
        }

        $user_id = $order['user_id'] ?? 0;
        $order_id = $order['order_id'] ?? 0;
        $parent_id = $order['parent_id'] ?? 0;

        if (empty($user_id) || empty($order_id) || empty($order_membership_card)) {
            return false;
        }

        $count = OrderInfoMembershipCard::where('order_id', $order_id)->where('user_id', $user_id)->count();

        if (empty($count)) {
            if (empty($order_membership_card['membership_card_id'])) {
                return false;
            }

            $cache_id = 'membership_card_order_goods' . $user_id . $order_membership_card['membership_card_id'];
            cache()->forget($cache_id);

            // 插入订单表
            $data = [
                'order_id' => $order_id,
                'user_id' => $user_id,
                'order_amount' => $order_membership_card['order_amount'],
                'membership_card_id' => $order_membership_card['membership_card_id'],
                'membership_card_buy_money' => $order_membership_card['membership_card_buy_money'] ?? 0,
                'membership_card_discount_price' => $order_membership_card['membership_card_discount_price'] ?? 0,
                'add_time' => TimeRepository::getGmTime(),
            ];
            OrderInfoMembershipCard::create($data);

            // 记录log初始值
            $amount = $order_membership_card['membership_card_buy_money'] ?? 0;
            $pay_point = 0;

            // 生成分销权益卡订单
            $account_log = [
                'amount' => $amount,
                'pay_points' => $pay_point,
                'user_note' => trans('admin/drpcard.receive_type_order_buy'),
                'pay_id' => $order['pay_id'] ?? 0,
                'payment' => $order['pay_code'] ?? '', // pay_code
                'receive_type' => 'order_buy', // 订单购买成为分销商
                'membership_card_id' => $order_membership_card['membership_card_id'],
                'trade_sn' => self::get_bill_sn(),
                'parent_id' => $parent_id
            ];
            $drp_account_log_id = app(\App\Modules\Drp\Services\Distribute\DistributeService::class)->insert_drp_account_log($user_id, $account_log);

            $log_id = $order['log_id'] ?? 0; // 订单支付日志id
            PayLog::where('log_id', $log_id)->update(['drp_account_log_id' => $drp_account_log_id]);

            return true;
        }

        return false;
    }

    /**
     * 生成分销权益卡订单单号
     * @return string
     */
    protected static function get_bill_sn()
    {
        /* 选择一个随机的方案 */
        mt_srand((double)microtime() * 1000000);

        if (!empty($str)) {
            return TimeRepository::getLocalDate('YmdHis') . $str . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        }

        return TimeRepository::getLocalDate('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    /**
     * 获取购买权益卡订单信息
     *
     * @param int $order_id
     * @param int $user_id
     * @return array|bool
     */
    public static function getOrderInfoMembershipCard($order_id = 0, $user_id = 0)
    {
        if (empty($user_id) || empty($order_id)) {
            return false;
        }

        $model = OrderInfoMembershipCard::where('order_id', $order_id)
            ->where('user_id', $user_id)
            ->first();

        $order_info = $model ? $model->toArray() : [];

        return $order_info;
    }
}
