<?php

namespace App\Services\Cart;

use App\Exceptions\HttpException;
use App\Models\Cart;
use App\Models\Goods;
use App\Models\GoodsConshipping;
use App\Models\GoodsConsumption;
use App\Models\Products;
use App\Models\ProductsArea;
use App\Models\ProductsWarehouse;
use App\Models\StoreGoods;
use App\Models\VirtualCard;
use App\Repositories\Activity\SeckillRepository;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CalculateRepository;
use App\Repositories\Common\DscEncryptRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\StrRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Erp\JigonManageService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Goods\GoodsWarehouseService;
use App\Services\Order\OrderGoodsService;
use App\Services\Package\PackageGoodsService;
use App\Services\User\UserCommonService;
use Illuminate\Support\Str;

class CartCommonService
{
    protected $sessionRepository;
    protected $goodsCommonService;
    protected $orderGoodsService;
    protected $dscRepository;

    public function __construct(
        SessionRepository $sessionRepository,
        GoodsCommonService $goodsCommonService,
        OrderGoodsService $orderGoodsService,
        DscRepository $dscRepository
    )
    {
        $this->sessionRepository = $sessionRepository;
        $this->goodsCommonService = $goodsCommonService;
        $this->orderGoodsService = $orderGoodsService;
        $this->dscRepository = $dscRepository;
    }

    /**
     * 单品设置阶梯促销最终金额
     *
     * @param int $goods_amount
     * @param int $goods_id
     * @param int $type
     * @param int $shipping_fee
     * @param int $parent_id
     * @return array
     */
    public function getConGoodsAmount($goods_amount = 0, $goods_id = 0, $type = 0, $shipping_fee = 0, $parent_id = 0)
    {
        $arr = [];

        $table = '';
        if ($type == 0) {
            $table = 'goods_consumption';
        } elseif ($type == 1) {
            $table = 'goods_conshipping';

            if (empty($shipping_fee)) {
                $shipping_fee = 0;
            }
        }

        if ($parent_id == 0 && $table) {
            $res = $this->getGoodsConList($goods_id, $table, $type);

            if ($res) {
                $arr = [];
                $arr['amount'] = '';
                foreach ($res as $key => $row) {
                    if ($type == 0) {
                        if ($goods_amount >= $row['cfull']) {
                            $arr[$key]['cfull'] = $row['cfull'];
                            $arr[$key]['creduce'] = $row['creduce'];
                            $arr[$key]['goods_amount'] = $goods_amount - $row['creduce'];
                            $arr['amount'] .= $arr[$key]['goods_amount'] . ',';
                        }
                    } elseif ($type == 1) {
                        if ($goods_amount >= $row['sfull']) {
                            $arr[$key]['sfull'] = $row['sfull'];
                            $arr[$key]['sreduce'] = $row['sreduce'];
                            if ($shipping_fee > 0) { //运费要大于0时才参加商品促销活动
                                $arr[$key]['shipping_fee'] = $shipping_fee - $row['sreduce'];
                                $arr['amount'] .= $arr[$key]['shipping_fee'] . ',';
                            } else {
                                $arr['amount'] = '0' . ',';
                            }
                        }
                    }
                }

                if ($type == 0) {
                    if (!empty($arr['amount'])) {
                        $arr['amount'] = substr($arr['amount'], 0, -1);
                    } else {
                        $arr['amount'] = $goods_amount;
                    }
                } elseif ($type == 1) {
                    if (!empty($arr['amount'])) {
                        $arr['amount'] = substr($arr['amount'], 0, -1);
                    } else {
                        $arr['amount'] = $shipping_fee;
                    }
                }
            } else {
                if ($type == 0) {
                    $arr['amount'] = $goods_amount;
                } elseif ($type == 1) {
                    $arr['amount'] = $shipping_fee;
                }
            }

            //消费满最大金额免运费
            if ($type == 1) {
                $largest_amount = Goods::where('goods_id', $goods_id)->value('largest_amount');

                if ($largest_amount > 0 && $goods_amount > $largest_amount) {
                    $arr['amount'] = 0;
                }
            }
        } else {
            if ($type == 0) {
                $arr['amount'] = $goods_amount;
            } elseif ($type == 1) {
                $arr['amount'] = $shipping_fee;
            }
        }

        return $arr;
    }

    /**
     * 查询商品满减促销信息
     *
     * @param int $goods_id
     * @param string $table
     * @param int $type
     * @return array|\Illuminate\Support\Collection
     */
    public function getGoodsConList($goods_id = 0, $table = '', $type = 0)
    {
        if ($table == 'goods_consumption') {
            $res = GoodsConsumption::where('goods_id', $goods_id);
        } else {
            $res = GoodsConshipping::where('goods_id', $goods_id);
        }

        $res = $res->get();

        $res = $res ? $res->toArray() : [];

        $arr = [];
        if ($res) {
            $string = '';
            foreach ($res as $key => $row) {
                $arr[$key]['id'] = $row['id'];
                if ($type == 0) {
                    $arr[$key]['cfull'] = $row['cfull'];
                    $arr[$key]['creduce'] = $row['creduce'];
                } elseif ($type == 1) {
                    $arr[$key]['sfull'] = $row['sfull'];
                    $arr[$key]['sreduce'] = $row['sreduce'];
                }
            }

            if ($type == 0) {
                $string = "cfull";
            } elseif ($type == 1) {
                $string = "sfull";
            }

            if ($string) {
                $arr = BaseRepository::getSortBy($arr, $string);
            }
        }

        return $arr;
    }

    /**
     * 重新计算购物车中信息
     *
     * mobile使用
     *
     * @param int $user_id
     */
    public function recalculatePriceMobileCart($user_id = 0)
    {
        if ($user_id > 0) {
            $session_id = $this->sessionRepository->realCartMacIp();

            if ($session_id) {
                /* 取得有可能改变价格的商品：除配件和赠品之外的商品 */
                $res = Cart::where('session_id', $session_id)
                    ->where('is_gift', 0)
                    ->where('rec_type', CART_GENERAL_GOODS)
                    ->get();
                $res = $res ? $res->toArray() : [];

                if ($res) {
                    foreach ($res as $row) {
                        $rec_id = Cart::where('goods_id', $row['goods_id'])
                            ->where('user_id', $user_id)
                            ->where('goods_attr_id', $row['goods_attr_id'])
                            ->where('is_real', 1)
                            ->value('rec_id');

                        if ($rec_id > 0) {
                            //更新数量
                            Cart::where('rec_id', $rec_id)->increment('goods_number', $row['goods_number']);
                            Cart::where('rec_id', $row['rec_id'])->delete();
                        } else {
                            $act_id = 0;
                            if ($row['act_id'] == 0) {
                                if ($row['extension_code'] != 'package_buy' && $row['is_gift'] == 0) {
                                    $fav = app(CartMobileService::class)->getFavourable($user_id, $row['goods_id'], $row['ru_id'], 0, true);
                                    if ($fav) {
                                        $act_id = $fav['act_id'] ?? 0;
                                    }
                                }
                            }
                            $cartOther = [
                                'user_id' => $user_id,
                                'session_id' => '',
                                'act_id' => $act_id,
                            ];
                            Cart::where('rec_id', $row['rec_id'])->update($cartOther);
                        }
                    }
                }

                /* 删除赠品，重新选择 */
                Cart::where('session_id', $session_id)->where('is_gift', '>', 0)->delete();

                // 供应链更新购物车
                if (file_exists(SUPPLIERS)) {
                    $wholesale = \App\Modules\Suppliers\Models\WholesaleCart::where('session_id', $session_id)
                        ->where('rec_type', CART_GENERAL_GOODS)
                        ->get();
                    $wholesale = $wholesale ? $wholesale->toArray() : [];

                    if ($wholesale) {
                        $cartOther = [
                            'user_id' => $user_id,
                            'session_id' => '',
                        ];
                        foreach ($wholesale as $row) {
                            \App\Modules\Suppliers\Models\WholesaleCart::where('rec_id', $row['rec_id'])->update($cartOther);
                        }
                    }
                }
            }
        }
    }

    /**
     * 重新计算购物车中的商品价格：目的是当用户登录时享受会员价格，当用户退出登录时不享受会员价格
     * 如果商品有促销，价格不变
     *
     * @throws \Exception
     */
    public function recalculatePriceCart()
    {
        $user_id = session('user_id');
        $session_id = $this->sessionRepository->realCartMacIp();

        if ($user_id > 0) {
            /* 取得有可能改变价格的商品：除配件和赠品之外的商品 */
            $res = Cart::where('session_id', $session_id)
                ->where('is_gift', 0)
                ->where('rec_type', CART_GENERAL_GOODS);

            $res = BaseRepository::getToArrayGet($res);

            if (config('shop.add_shop_price') == 1) {
                $add_tocart = 1;
            } else {
                $add_tocart = 0;
            }

            $nowTime = TimeRepository::getGmTime();

            if ($res) {
                foreach ($res as $row) {
                    $attr_id = empty($row['goods_attr_id']) ? [] : explode(',', $row['goods_attr_id']);

                    if ($row['extension_code'] != 'package_buy') {
                        $presale = 0;
                    } else {
                        $presale = CART_PACKAGE_GOODS;
                    }

                    $goods_price = $this->goodsCommonService->getFinalPrice($row['goods_id'], $row['goods_number'], true, $attr_id, $row['warehouse_id'], $row['area_id'], $row['area_city'], 0, $presale, $add_tocart);

                    $rec_id = Cart::where('goods_id', $row['goods_id'])
                        ->where('user_id', $user_id)
                        ->where('extension_code', '<>', 'package_buy')
                        ->where('goods_attr_id', $row['goods_attr_id'])
                        ->where('warehouse_id', $row['warehouse_id'])
                        ->where('is_real', 1)
                        ->where('group_id', '');

                    $rec_id = $rec_id->value('rec_id');

                    $error = 0;
                    if ($row['extension_code'] != 'package_buy') {
                        $xiangouInfo = $this->goodsCommonService->getPurchasingGoodsInfo($row['goods_id']);
                        if ($xiangouInfo) {
                            $start_date = $xiangouInfo['xiangou_start_date'];
                            $end_date = $xiangouInfo['xiangou_end_date'];

                            if ($xiangouInfo['is_xiangou'] == 1 && $nowTime > $start_date && $nowTime < $end_date) {
                                $orderGoods = $this->orderGoodsService->getForPurchasingGoods($start_date, $end_date, $row['goods_id'], $user_id);
                                $cart_number = $orderGoods['goods_number'] + $row['goods_number'];

                                if ($orderGoods['goods_number'] >= $xiangouInfo['xiangou_num']) {
                                    $row['goods_number'] = 0;
                                    $error = 1;
                                } elseif ($cart_number >= $xiangouInfo['xiangou_num']) {
                                    $row['goods_number'] = $xiangouInfo['xiangou_num'] - $orderGoods['goods_number'];
                                    $error = 2;
                                } else {
                                    $error = 0;
                                }
                            } else {
                                $error = 0;
                            }
                        }
                    }

                    if ($error == 1) {
                        Cart::where('goods_id', $row['goods_id'])
                            ->where('rec_id', $row['rec_id'])
                            ->where('warehouse_id', $row['warehouse_id'])
                            ->delete();
                    } else {
                        if ($rec_id > 0) {
                            if ($error == 2) {
                                $cartOther = [
                                    'goods_number' => $row['goods_number']
                                ];

                                Cart::where('rec_id', $rec_id)->update($cartOther);
                            } else {
                                Cart::where('rec_id', $rec_id)->increment('goods_number', $row['goods_number']);
                            }

                            Cart::where('rec_id', $row['rec_id'])->delete();
                        } else {
                            $cartOther = [
                                'user_id' => $user_id,
                                'session_id' => '',
                                'goods_number' => $row['goods_number'],
                            ];

                            if ($row['extension_code'] != 'package_buy') {
                                if ($row['parent_id'] == 0 && $goods_price > 0) {
                                    $cartOther['goods_price'] = $goods_price;
                                }

                                Cart::where('goods_id', $row['goods_id'])
                                    ->where('rec_id', $row['rec_id'])
                                    ->where('warehouse_id', $row['warehouse_id'])
                                    ->update($cartOther);
                            } else {
                                Cart::where('rec_id', $row['rec_id'])->update($cartOther);
                            }
                        }
                    }
                }
            }

            /* 删除赠品，重新选择 */
            $session_id = $this->sessionRepository->realCartMacIp();
            Cart::where('session_id', $session_id)->where('is_gift', '>', 0)->delete();
        }
    }

    /**
     * 复制购物车商品
     *
     * @param array $rec_id
     * @param int $user_id
     * @param int $store_id
     * @param string $take_time
     * @param string $store_mobile
     * @return array|bool
     */
    public function copyCartToOfflineStore($rec_id = [], $user_id = 0, $store_id = 0, $take_time = '', $store_mobile = '')
    {
        if (empty($store_id)) {
            return false;
        }
        if (empty($user_id)) {
            $user_id = session()->exists('user_id') ? session('user_id', 0) : $user_id;
        }

        if (!is_array($rec_id)) {
            $rec_id = explode(',', $rec_id);
        }

        $cart = Cart::whereIn('rec_id', $rec_id);

        if (!empty($user_id)) {
            $cart = $cart->where('user_id', $user_id);
        } else {
            $real_ip = $this->sessionRepository->realCartMacIp();
            $cart = $cart->where('session_id', $real_ip);
        }

        $cart_list = $cart->get();
        $rec_id = [];
        if ($cart_list) {
            foreach ($cart_list as $item) {
                unset($item['rec_id']);
                $item['rec_type'] = CART_OFFLINE_GOODS;
                $item['store_id'] = $store_id;
                $item['take_time'] = $take_time;
                $item['store_mobile'] = $store_mobile;
                $rec_id[] = Cart::insertGetId($item->toArray());
            }
        }
        return $rec_id;
    }

    /**
     * 清空购物车门店商品
     *
     * @param int $user_id
     */
    public function clearStoreGoods($user_id = 0)
    {
        if (empty($user_id)) {
            $user_id = session()->exists('user_id') ? session('user_id', 0) : $user_id;
        }

        /*->where('rec_type', CART_OFFLINE_GOODS)*/
        $res = Cart::where('store_id', '>', 0);

        if (!empty($user_id)) {
            $res = $res->where('user_id', $user_id);
        } else {
            $session_id = $this->sessionRepository->realCartMacIp();

            $res = $res->where('session_id', $session_id);
        }

        $res->delete();
    }

    /**
     * 清空购物车
     *
     * @param int $user_id
     * @param int $type
     * @return mixed
     */
    public function clearCart($user_id = 0, $type = CART_GENERAL_GOODS)
    {
        if (empty($user_id)) {
            $user_id = session()->exists('user_id') ? session('user_id', 0) : $user_id;
        }

        $cart = Cart::where('rec_type', $type);

        if (!empty($user_id)) {
            $cart = $cart->where('user_id', $user_id);
        } else {
            $real_ip = $this->sessionRepository->realCartMacIp();
            $cart = $cart->where('session_id', $real_ip);
        }

        return $cart->delete();
    }

    /**
     * 获取选中的购物车商品rec_id
     *
     * @param int $is_select 获取类型：0|session , 1|查询表数据
     * @param int $user_id 会员ID
     * @param int $flow_type
     * @return \Illuminate\Session\SessionManager|\Illuminate\Session\Store|int|mixed
     */
    public function getCartValue($is_select = 0, $user_id = 0, $flow_type = 0)
    {
        if ($is_select || (session()->has('user_id') == false && $user_id > 0)) {
            if ($user_id > 0) {
                $list = Cart::where('user_id', $user_id);
            } else {
                $session_id = $this->sessionRepository->realCartMacIp();
                $list = Cart::where('session_id', $session_id);
            }

            if ($flow_type == CART_PACKAGE_GOODS) {
                $list = $list->where('extension_code', 'package_buy');
            }

            $list = $list->where('is_checked', 1);

            $list = BaseRepository::getToArrayGet($list);
            $cart_value = BaseRepository::getKeyPluck($list, 'rec_id');
            $cart_value = $cart_value ? $cart_value : 0;
        } else {
            $cart_value = 0;
            if (session()->exists('cart_value')) {
                $cart_value = DscEncryptRepository::filterValInt(session('cart_value'));
            }
        }

        return $cart_value;
    }

    /**
     * 更新商品最新价格
     *
     * @param int $goods_price
     * @param int $rec_id
     */
    public function updateCartPrice($goods_price = 0, $rec_id = 0)
    {
        if ($goods_price > 0 && $rec_id > 0) {
            Cart::where('rec_id', $rec_id)->where('parent_id', 0)
                ->update([
                    'goods_price' => $goods_price
                ]);
        }
    }

    /**
     * 取得购物车总金额
     *
     * @param array $cartWhere
     * @return float
     */
    public function getCartAmount($cartWhere = [])
    {
        /**
         * @param int $user_id 会员ID
         * @param string $cart_value 购物车ID
         * @param bool $include_gift 是否包括赠品
         * @param int $type 类型：默认普通商品
         * @return float
         */
        $user_id = $cartWhere['user_id'] ?? 0;
        $session_id = $cartWhere['session_id'] ?? 0;
        $cart_value = $cartWhere['cart_value'] ?? 0;
        $include_gift = $cartWhere['include_gift'] ?? true;
        $type = $cartWhere['rec_type'] ?? [CART_GENERAL_GOODS, CART_PACKAGE_GOODS, CART_ONESTEP_GOODS];

        $res = Cart::selectRaw("SUM(goods_price * goods_number) as total");

        if (is_array($type)) {
            $res = $res->whereIn('rec_type', $type);
        } else {
            $res = $res->where('rec_type', $type);
        }

        if (!empty($user_id)) {
            $res = $res->where('user_id', $user_id);
        } elseif (isset($cartWhere['session_id'])) {
            $res = $res->where('session_id', $session_id);
        }

        if (!$include_gift) {
            $res = $res->where('is_gift', 0)
                ->where('goods_id', '>', 0);
        }

        if ($cart_value) {
            $cart_value = BaseRepository::getExplode($cart_value);
            $res = $res->whereIn('rec_id', $cart_value);
        }

        $res = BaseRepository::getToArrayFirst($res);

        $total = $res ? $res['total'] : 0;

        return floatval($total);
    }

    /**
     * 查询购买N件商品
     *
     * @param int $type
     * @param string $cart_value
     * @param int $user_id
     * @return int
     */
    public function getBuyCartGoodsNumber($type = CART_GENERAL_GOODS, $cart_value = '', $user_id = 0)
    {
        if (empty($user_id)) {
            $user_id = session()->has('user_id') && session()->get('user_id') ? session('user_id') : $user_id;
        }

        $session_id = $this->sessionRepository->realCartMacIp();

        $whereType = [
            'type' => $type,
            'cart_presale' => CART_PRESALE_GOODS
        ];

        /* 促销活动 start */
        $goods_number = Cart::where('rec_type', $type)->where('extension_code', '<>', 'package_buy');

        $goods_number = $goods_number->whereHasIn('getGoods', function ($query) use ($whereType) {
            if ($whereType['type'] == $whereType['cart_presale']) {
                $query->where('is_on_sale', 0)->where('is_delete', 0);
            } else {
                $query->where('is_on_sale', 1)->where('is_delete', 0);
            }
        });

        if (!empty($user_id)) {
            $goods_number = $goods_number->where('user_id', $user_id);
        } else {
            $goods_number = $goods_number->where('session_id', $session_id);
        }

        if (!empty($cart_value)) {
            $cart_value = !is_array($cart_value) ? explode(",", $cart_value) : $cart_value;

            $goods_number = $goods_number->whereIn('rec_id', $cart_value);
        }

        $goods_number = $goods_number->selectRaw('SUM(goods_number) AS goods_number')->value('goods_number');
        $goods_number = $goods_number ? $goods_number : 0;
        /* 促销活动 end */

        /* 促销活动 start */
        $activity_number = Cart::where('rec_type', $type)->where('extension_code', '<>', 'package_buy');

        $activity_number = $activity_number->whereHasIn('getGoodsActivity', function ($query) {
            $query->where('review_status', 3);
        });

        if (!empty($user_id)) {
            $activity_number = $activity_number->where('user_id', $user_id);
        } else {
            $activity_number = $activity_number->where('session_id', $session_id);
        }

        if (!empty($cart_value)) {
            $cart_value = !is_array($cart_value) ? explode(",", $cart_value) : $cart_value;

            $activity_number = $activity_number->whereIn('rec_id', $cart_value);
        }

        $activity_number = $activity_number->count();
        /* 促销活动 end */

        /*超值礼包*/
        $package_num = Cart::where('rec_type', $type)->where('extension_code', 'package_buy');
        $package_num = $package_num->whereHasIn('getPackageGoods');
        if (!empty($cart_value)) {
            $cart_value = !is_array($cart_value) ? explode(",", $cart_value) : $cart_value;
            $package_num = $package_num->whereIn('rec_id', $cart_value);
        }
        $package_num = $package_num->selectRaw('SUM(goods_number) AS goods_number')->value('goods_number');

        return ($goods_number + $activity_number + $package_num);
    }

    /**
     * 更新购物车价格
     * @param int $goods_price
     * @param int $rec_id
     * @return bool
     */
    public static function getUpdateCartPrice($goods_price = 0, $rec_id = 0)
    {
        if (empty($rec_id)) {
            return false;
        }

        return Cart::where('rec_id', $rec_id)->update(['goods_price' => $goods_price]);
    }

    /**
     * 删除购物车中不能单独销售的商品
     *
     * @param int $user_id
     */
    public function flowClearCartAlone($user_id = 0)
    {
        $user_id = !empty($user_id) ? $user_id : session('user_id', 0);
        $session_id = app(SessionRepository::class)->realCartMacIp();

        /* 查询：购物车中所有不可以单独销售的配件 */
        $res = Cart::select('parent_id', 'rec_id')
            ->where('extension_code', '<>', 'package_buy')
            ->whereHasIn('getGoods', function ($query) {
                $query->where('is_alone_sale', 0);
            })
            ->whereHasIn('getGroupGoods', function ($query) {
                $query->where('parent_id', '>', 0);
            });

        if (!empty($user_id)) {
            $res = $res->where('user_id', $user_id);
        } else {
            $res = $res->where('session_id', $session_id);
        }

        $res = $res->with([
            'getGroupGoods' => function ($query) {
                $query->select('goods_id', 'parent_id');
            }
        ]);

        $res = BaseRepository::getToArrayGet($res);

        $rec_id = [];
        if ($res) {
            foreach ($res as $row) {
                $row = $row['get_group_goods'] ? array_merge($row, $row['get_group_goods']) : $row;
                $rec_id[$row['rec_id']][] = $row['parent_id'];
            }
        }

        if (empty($rec_id)) {
            return;
        }

        /* 查询：购物车中所有商品 */
        $res = Cart::query()->select('goods_id')
            ->where('extension_code', '<>', 'package_buy');

        if (!empty($user_id)) {
            $res = $res->where('user_id', $user_id);
        } else {
            $res = $res->where('session_id', $session_id);
        }

        $res = $res->pluck('goods_id');
        $cart_good = BaseRepository::getToArray($res);

        if (empty($cart_good)) {
            return;
        }

        /* 如果购物车中不可以单独销售配件的基本件不存在则删除该配件 */
        $del_rec_id = '';
        if ($rec_id) {
            foreach ($rec_id as $key => $value) {
                foreach ($value as $v) {
                    if (in_array($v, $cart_good)) {
                        continue 2;
                    }
                }

                $del_rec_id = $key . ',';
            }

            $del_rec_id = trim($del_rec_id, ',');
        }

        if ($del_rec_id == '') {
            return;
        }

        /* 删除 */
        $del_rec_id = !is_array($del_rec_id) ? explode(",", $del_rec_id) : $del_rec_id;
        $res = Cart::whereIn('rec_id', $del_rec_id);

        if (!empty($user_id)) {
            $res = $res->where('user_id', $user_id);
        } else {
            $res = $res->where('session_id', $session_id);
        }

        $res->delete();
    }

    /**
     * 购物车商品配件
     *
     * @param array $res
     * @return array
     */
    public function cartGoodsGroupList($res = [])
    {
        $grouped = collect($res)->groupBy('group_id')->toArray();
        $gr_arr = [];
        if ($grouped) {
            foreach ($grouped as $key => $rgroup) {
                if (empty($key)) {
                    continue;
                }
                if ($rgroup && !empty($key)) {
                    $group_arr = [];
                    foreach ($rgroup as $rg) {
                        if (empty($rg['group_id'])) {
                            continue;
                        }
                        if ($rg['parent_id']) {
                            $group_arr['g_parent_id'] = $rg['parent_id'];
                            $group_arr['g_parent_rec_id'][] = $rg['rec_id'];
                        } else {
                            $group_arr['rec_id'] = $rg['rec_id'];
                        }
                        $group_arr['group_id'] = $rg['group_id'];
                    }
                }
                //对套餐的商品分组
                if (!empty($group_arr)) {
                    $gr_arr[] = $group_arr ?? [];
                }
            }

            if ($gr_arr) {
                $new_res = [];
                foreach ($res as $row) {
                    $new_res[$row['rec_id']] = $row;
                }
                foreach ($gr_arr as $gr_row) {
                    if ($new_res) {
                        foreach ($new_res as $key => $new_row) {
                            if (!empty($gr_row['rec_id']) && $gr_row['rec_id'] == $key) {
                                if (isset($gr_row['g_parent_rec_id']) && $gr_row['g_parent_rec_id']) {
                                    foreach ($gr_row['g_parent_rec_id'] as $g_parent_rec_id) {
                                        //配件商品数组转移到配件主商品中。并移除原配件商品数组
                                        $new_res[$key]['parts'][] = $new_res[$g_parent_rec_id];
                                        unset($new_res[$g_parent_rec_id]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return !empty($new_res) ? $new_res : $res;
    }

    /**
     * 获取购物车商品数量
     *
     * @param array $rec_id
     * @param int $uid
     * @param int $area_id
     * @param int $area_city
     * @param array $flow_type
     * @return array
     */
    public function cartGoodsNumber($rec_id = [], $uid = 0, $area_id = 0, $area_city = 0, $flow_type = [CART_GENERAL_GOODS, CART_ONESTEP_GOODS])
    {
        $flow_type = BaseRepository::getExplode($flow_type);

        $list = Cart::select('rec_id', 'goods_number')
            ->where('user_id', $uid)
            ->whereIn('rec_type', $flow_type);

        $rec_id = BaseRepository::getExplode($rec_id);

        $list = $list->whereIn('rec_id', $rec_id);

        $list = BaseRepository::getToArrayGet($list);

        if (config('shop.open_area_goods') == 1) {

            $goods_id = BaseRepository::getKeyPluck($list, 'goods_id');

            $goods = Goods::select('goods_id')->whereIn('goods_id', $goods_id);

            $goods = $this->dscRepository->getAreaLinkGoods($goods, $area_id, $area_city);

            $goods_id = BaseRepository::getKeyPluck($goods, 'goods_id');

            $sql = [
                'whereIn' => [
                    [
                        'name' => 'goods_id',
                        'value' => $goods_id
                    ]
                ]
            ];

            $list = BaseRepository::getArraySqlGet($list, $sql);
        }

        return $list ? array_column($list, 'goods_number', 'rec_id') : [];
    }

    /**
     * 检查订单中商品库存
     *
     * @param array $_cart_goods_stock
     * @param int $store_id
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @throws HttpException
     */
    public function getFlowCartStock($_cart_goods_stock = [], $store_id = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0)
    {
        if (empty($_cart_goods_stock)) {
            throw new HttpException(lang('flow.cart_empty_goods'), 1);
        }

        $_cart_goods_stock = BaseRepository::getArrayFlip($_cart_goods_stock);
        $cart_list = CartDataHandleService::CartDataList($_cart_goods_stock, ['rec_id', 'goods_id', 'goods_name', 'extension_code', 'product_id', 'model_attr', 'goods_attr_id', 'is_real']);

        $goods_id = BaseRepository::getKeyPluck($cart_list, 'goods_id');
        $goods_list = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'goods_number', 'cloud_id']);

        foreach ($_cart_goods_stock as $val => $rec_id) {
            $val = intval(make_semiangle($val));
            if ($val <= 0 || !is_numeric($rec_id)) {
                continue;
            }

            $goods = $cart_list[$rec_id];

            if (empty($goods)) {
                continue;
            }

            if ($store_id > 0) {
                $goods_info['goods_number'] = StoreGoods::where('goods_id', $goods['goods_id'])->where('store_id', $store_id)->value('goods_number');
            } else {
                $goods_info = $goods_list[$goods['goods_id']] ?? [];

                if ($goods_info) {
                    if ($goods['model_attr'] == 1) {
                        $warehouse_goods = GoodsDataHandleService::getWarehouseGoodsDataList($goods['goods_id'], $warehouse_id);
                        $goods_info['goods_number'] = $warehouse_goods[$goods['goods_id']]['region_number'] ?? 0;
                    } elseif ($goods['model_attr'] == 2) {
                        $warehouse_area_goods = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods['goods_id'], $area_id, $area_city);
                        $goods_info['goods_number'] = $warehouse_area_goods[$goods['goods_id']]['region_number'] ?? 0;
                    }
                }
            }

            $goods_info['goods_number'] = $goods_info['goods_number'] ?? 0;
            // 截取商品名称长度 无论中英文一律按一个字
            $goods['goods_name'] = !empty($goods['goods_name']) ? Str::substr($goods['goods_name'], 0, 10) . '...' : '';

            //系统启用了库存，检查输入的商品数量是否有效
            if (intval(config('shop.use_storage')) > 0 && $goods['extension_code'] != 'package_buy' && $store_id == 0) {

                if ($goods['extension_code'] == 'virtual_card' || $goods['is_real'] == 0) {
                    // 虚拟商品检查库存
                    $num = VirtualCard::where('goods_id', $goods['goods_id'])->where('is_saled', 0)->count();
                    if ($num < $val) {
                        // 虚拟卡已缺货
                        $msg = $goods['goods_name'] . ' ' . lang('admin/common.virtual_card_oos');
                        throw new HttpException($msg, 1);
                    }
                } elseif (stripos($goods['extension_code'], 'seckill') !== false) {
                    // 秒杀商品检查库存
                    $seckill_goods_id = (int)substr($goods['extension_code'], 7);
                    $product_id = $goods['product_id'] ?? 0;
                    $seckill_goods = SeckillRepository::seckill_goods_stock($seckill_goods_id, $goods['goods_id'], $product_id);

                    $seckill_goods['sec_num'] = $seckill_goods['sec_num'] ?? 0;
                    if ($seckill_goods['sec_num'] < $val || $goods_info['goods_number'] < $val) {
                        // 秒杀商品库存不足
                        $msg = sprintf(lang('shopping_flow.stock_insufficiency'), $goods['goods_name'], $seckill_goods['sec_num'], $seckill_goods['sec_num']);
                        throw new HttpException($msg, 1);
                    }

                } else {
                    /* 是货品 */
                    $goods['product_id'] = trim($goods['product_id']);
                    if (!empty($goods['product_id'])) {
                        if ($goods['model_attr'] == 1) {
                            $prod = ProductsWarehouse::where('goods_id', $goods['goods_id'])->where('product_id', $goods['product_id']);
                        } elseif ($goods['model_attr'] == 2) {
                            $prod = ProductsArea::where('goods_id', $goods['goods_id'])->where('product_id', $goods['product_id']);

                            if (config('shop.area_pricetype') == 1) {
                                $prod = $prod->where('city_id', $area_city);
                            }
                        } else {
                            $prod = Products::where('goods_id', $goods['goods_id'])->where('product_id', $goods['product_id']);
                        }

                        $product_number = $prod->value('product_number');

                        if ($product_number < $val) {
                            $msg = sprintf(lang('shopping_flow.stock_insufficiency'), $goods['goods_name'] ?? '', $product_number, $product_number);
                            throw new HttpException($msg, 1);
                        }
                    } else {
                        if ($goods_info['goods_number'] < $val) {
                            $msg = sprintf(lang('shopping_flow.stock_insufficiency'), $goods['goods_name'], $goods_info['goods_number'], $goods_info['goods_number']);
                            throw new HttpException($msg, 1);
                        }
                    }
                }

            } elseif (intval(config('shop.use_storage')) > 0 && $store_id > 0) {
                $storeGoods = StoreGoods::where('store_id', $store_id)->where('goods_id', $goods['goods_id']);
                $storeGoods = BaseRepository::getToArrayFirst($storeGoods);

                $products = app(GoodsWarehouseService::class)->getWarehouseAttrNumber($goods['goods_id'], $goods['goods_attr_id'], $warehouse_id, $area_id, $area_city, '', $store_id); //获取属性库存
                $attr_number = $products ? $products['product_number'] : 0;
                if ($goods['goods_attr_id']) { //当商品没有属性库存时
                    $goods_info['goods_number'] = $attr_number;
                } else {
                    $goods_info['goods_number'] = $storeGoods['goods_number'] ?? 0;
                }

                if ($goods_info['goods_number'] < $val) {
                    $msg = sprintf(lang('shopping_flow.stock_store_shortage'), $goods['goods_name'], $goods_info['goods_number'], $goods_info['goods_number']);
                    throw new HttpException($msg, 1);
                }
            } elseif (intval(config('shop.use_storage')) > 0 && $goods['extension_code'] == 'package_buy') {
                if (app(PackageGoodsService::class)->judgePackageStock($goods['goods_id'], $val)) {
                    throw new HttpException(lang('shopping_flow.package_stock_insufficiency'), 1);
                }
            } elseif (isset($goods_info['cloud_id']) && $goods_info['cloud_id'] > 0) {
                $cloud_number = app(JigonManageService::class)->jigonGoodsNumber(['product_id' => $goods['product_id']]);

                if ($cloud_number < $val) {
                    $msg = sprintf(lang('shopping_flow.stock_insufficiency'), $goods['goods_name'], $cloud_number, $cloud_number);

                    throw new HttpException($msg, 1);
                }
            }
        }
    }

    /**
     * 右上角购物车
     *
     * @param null $user_id
     * @return array
     */
    public function cartNumber($user_id = null)
    {
        if (is_null($user_id)) {
            $user_id = session('user_id', 0);
        }

        $row = Cart::selectRaw("SUM(goods_number) AS number, SUM(goods_price * goods_number) AS amount")
            ->where('rec_type', CART_GENERAL_GOODS);

        $row = $row->where('stages_qishu', -1);

        if (!empty($user_id)) {
            $row = $row->where('user_id', $user_id);
        } else {
            $session_id = $this->sessionRepository->realCartMacIp();
            $row = $row->where('session_id', $session_id);
        }

        $row = BaseRepository::getToArrayFirst($row);

        if ($row) {
            $number = intval($row['number']);
            $amount = floatval($row['amount']);
        } else {
            $number = 0;
            $amount = 0;
        }

        $result = [
            'number' => $number,
            'amount' => $amount
        ];

        return $result;
    }

    /**
     * 重新组合购物车活动商品
     *
     * @param array $merchant_goods
     * @return array
     * @throws \Exception
     */
    public function merchantGoodsListData($merchant_goods = [])
    {
        $arr = [];
        if ($merchant_goods) {

            $list = CartDataHandleService::merchantGoods($merchant_goods);

            $mathArr = [];
            foreach ($merchant_goods as $key => $row) {
                $arr[$key] = $row;

                $new_list = $row['new_list'] ?? [];

                unset($arr[$key]['new_list']);

                if ($new_list) {

                    foreach ($new_list as $k => $v) {

                        $act_type = $v['act_type'] ?? 0; //活动类型 0 满赠 1 满减 2 折扣

                        if ($act_type > 0) {

                            $act_type_ext_format = $v['act_type_ext_format'];

                            $act_goods_list = $v['act_goods_list'] ?? [];
                            $act_goods_list = $act_goods_list ? array_values($act_goods_list) : [];

                            $sql = [
                                'where' => [
                                    [
                                        'name' => 'is_checked',
                                        'value' => 1
                                    ]
                                ]
                            ];
                            $act_goods_list = BaseRepository::getArraySqlGet($act_goods_list, $sql);

                            $goods_amount = BaseRepository::getArraySum($act_goods_list, ['goods_number', 'goods_price']);

                            /* 扣除商品优惠金额 */
                            $disAmountAll = BaseRepository::getArraySum($act_goods_list, 'dis_amount');
                            $goods_amount = $goods_amount - $disAmountAll;

                            $sql = [
                                'whereIn' => [
                                    [
                                        'name' => 'act_id',
                                        'value' => $v['act_id']
                                    ]
                                ],
                                'where' => [
                                    [
                                        'name' => 'is_checked',
                                        'value' => 1
                                    ]
                                ]
                            ];
                            $goods_list = BaseRepository::getArraySqlGet($list, $sql);
                            $amount = BaseRepository::getArraySum($goods_list, ['goods_number', 'goods_price']);

                            /* 扣除商品优惠金额 */
                            $actDisAmountAll = BaseRepository::getArraySum($list, 'dis_amount');
                            $amount = $amount - $actDisAmountAll;

                            $goodsListId = BaseRepository::getKeyPluck($goods_list, 'rec_id');
                            $actGoodsListId = BaseRepository::getKeyPluck($v['act_goods_list'], 'rec_id');
                            $is_intersect = BaseRepository::getArrayIntersect($goodsListId, $actGoodsListId);

                            if ($amount >= $v['min_amount'] && !empty($is_intersect)) {
                                $v['available'] = true;
                            }

                            $math_div = CalculateRepository::math_div($goods_amount, $amount);
                            $math_div = $this->dscRepository->changeFloat($math_div);

                            $mathArr['newMathReduce_' . $v['act_id']] = $mathArr['newMathReduce_' . $v['act_id']] ?? 1;

                            /* 处理误差比例 */
                            $mathArr['newMathReduce_' . $v['act_id']] -= $math_div;
                            $mathArr['newMathReduce_' . $v['act_id']] = $this->dscRepository->changeFloat($mathArr['newMathReduce_' . $v['act_id']]);

                            if ($mathArr['newMathReduce_' . $v['act_id']] < 0) {
                                $math_div += $mathArr['newMathReduce_' . $v['act_id']];
                            }

                            $v['act_name'] = "[" . lang('common.general_audience') . "]" . $v['act_name'];

                            if ($act_type == 1) {
                                if ($math_div > 0) {
                                    $act_type_ext_format = $v['act_type_ext_format'] * $math_div;
                                } else {
                                    $act_type_ext_format = 0;
                                }
                            }

                            /* 均摊商品优惠活动金额 */
                            $differenceList = [];
                            if ($act_type_ext_format > 0) {
                                if (isset($v['act_goods_list']) && $v['act_goods_list']) {

                                    $mathReduce = 1;
                                    $goodsFavAmount = $v['goods_fav_amount'] ?? 0;
                                    $goodsFavAmount = $this->dscRepository->changeFloat($goodsFavAmount);
                                    $v['goods_fav_amount'] = $goodsFavAmount;

                                    $act_type_ext_format = $this->dscRepository->changeFloat($act_type_ext_format);

                                    $actGoodsCount = count($v['act_goods_list']);
                                    $idx = 0;
                                    foreach ($v['act_goods_list'] as $i => $m) {

                                        $act_goods_amoun = $m['goods_number'] * $m['goods_price'] - $m['dis_amount'];

                                        $act_math_div = CalculateRepository::math_div($act_goods_amoun, $goods_amount);
                                        $act_math_div = $this->dscRepository->changeFloat($act_math_div);

                                        /* 处理误差比例 */
                                        $mathReduce -= $act_math_div;

                                        $mathReduce = $this->dscRepository->changeFloat($mathReduce);

                                        if (($idx + 1) == $actGoodsCount) {
                                            $act_math_div += $mathReduce;
                                        }

                                        if ($act_type == 1) {
                                            $differenceList[$v['act_id']]['differenceTotal'] = $act_type_ext_format;
                                            $m['goods_favourable'] = $act_math_div * $act_type_ext_format;
                                        } else {

                                            $differenceList[$v['act_id']]['differenceTotal'] = $v['goods_fav_amount'] ?? 0;

                                            if (isset($v['goods_fav_amount'])) {
                                                $m['goods_favourable'] = $act_math_div * $v['goods_fav_amount'];
                                            } else {
                                                $m['goods_favourable'] = 0;
                                            }
                                        }

                                        $m['goods_favourable'] = $this->dscRepository->changeFloat($m['goods_favourable']);

                                        if ($v['available'] == false) {
                                            $m['goods_favourable'] = 0;
                                            $differenceList[$v['act_id']]['differenceTotal'] = 0;
                                        }

                                        $differenceList[$v['act_id']]['difference_list'][$idx] = [
                                            'rec_id' => $m['rec_id'],
                                            'goods_favourable' => $m['goods_favourable']
                                        ];

                                        $v['act_goods_list'][$i] = $m;

                                        $idx++;
                                    }

                                    if ($differenceList) {
                                        foreach ($differenceList as $dk => $dv) {
                                            $difference_list = BaseRepository::valueErrorArray($dv['difference_list'], 'goods_favourable', 'rec_id', $dv['differenceTotal']);

                                            foreach ($difference_list as $up_key => $up_val) {
                                                Cart::where('rec_id', $up_val['rec_id'])->update([
                                                    'goods_favourable' => $up_val['goods_favourable']
                                                ]);
                                            }
                                        }
                                    }
                                }
                            }

                            if ($act_type == 1) {
                                $v['act_type_ext_format'] = app(DscRepository::class)->getPriceFormat($act_type_ext_format);
                            }

                            $cart_fav_amount = $v['cart_fav_amount'] ?? 0;
                            $v['cart_fav_amount_format'] = app(DscRepository::class)->getPriceFormat($cart_fav_amount);
                            $v['min_amount'] = app(DscRepository::class)->getPriceFormat($v['min_amount']);

                            /* 优惠活动[满减、折扣]金额 */
                            $v['goods_fav_total'] = $differenceList[$v['act_id']]['differenceTotal'] ?? 0;

                            $arr[$key]['new_list'][$k] = $v;

                        } else {

                            /* 更新赠品活动商品的原始金额为0 start */
                            $act_goods_list = $v['act_goods_list'] ?? [];

                            $sql = [
                                'where' => [
                                    [
                                        'name' => 'goods_favourable',
                                        'value' => 0,
                                        'condition' => '>' //条件查询
                                    ]
                                ]
                            ];
                            $act_goods_list = BaseRepository::getArraySqlGet($act_goods_list, $sql);

                            $actGoodsListId = BaseRepository::getKeyPluck($act_goods_list, 'rec_id');
                            if ($actGoodsListId) {
                                Cart::where('rec_id', $actGoodsListId)->update([
                                    'goods_favourable' => 0
                                ]);
                            }
                            /* 更新赠品活动商品的原始金额为0 end */

                            $v['goods_fav_total'] = 0; //赠品活动金额为0元
                            $arr[$key]['new_list'][$k] = $v;
                        }
                    }
                }
            }
        }

        return $arr;
    }

    /**
     * 获取购物车商品的最终价格
     *
     * @param int $uid
     * @param array $cart_goods
     * @param array $goodsList
     * @param array $warehouseGoodsList
     * @param array $warehouseAreaGoodsList
     * @param array $productsList
     * @param array $productsWarehouseList
     * @param array $productsAreaList
     * @return array
     * @throws \Exception
     */
    public function cartFinalPrice($uid = 0, $cart_goods = [], $goodsList = [], $warehouseGoodsList = [], $warehouseAreaGoodsList = [], $productsList = [], $productsWarehouseList = [], $productsAreaList = [])
    {
        /* 处理更新价格 start */
        $recList = BaseRepository::getKeyPluck($cart_goods, 'rec_id');
        $recGoodsList = BaseRepository::getColumn($cart_goods, 'goods_id', 'rec_id');
        $recNumberList = BaseRepository::getColumn($cart_goods, 'goods_number', 'rec_id');
        $recGoodsAttrIdList = BaseRepository::getColumn($cart_goods, 'goods_attr_id', 'rec_id');
        $recWarehouseIdList = BaseRepository::getColumn($cart_goods, 'warehouse_id', 'rec_id');
        $recAreaIdList = BaseRepository::getColumn($cart_goods, 'area_id', 'rec_id');
        $recAreaCityList = BaseRepository::getColumn($cart_goods, 'area_city', 'rec_id');
        $recPackageBuyList = BaseRepository::getColumn($cart_goods, 'package_buy', 'rec_id');
        $recProductIdList = BaseRepository::getColumn($cart_goods, 'product_id', 'rec_id');
        $recGoodsModelList = BaseRepository::getColumn($cart_goods, 'model_attr', 'rec_id');

        $priceList = [
            'goods' => [
                'goods_list' => $goodsList,
                'warehouse_goods_list' => $warehouseGoodsList,
                'warehouse_area_goods_list' => $warehouseAreaGoodsList
            ],
            'product' => [
                'products_list' => $productsList,
                'products_warehouse_list' => $productsWarehouseList,
                'products_area_list' => $productsAreaList,
            ]
        ];

        $list = $this->combinedArray($recList, $recGoodsList, $recGoodsModelList, $recNumberList, $recGoodsAttrIdList, $recWarehouseIdList, $recAreaIdList, $recAreaCityList, $recPackageBuyList, $recProductIdList);

        $arr = [];
        if ($list) {
            if ($uid > 0) {
                $rank = app(UserCommonService::class)->getUserRankByUid($uid);
                $user_rank = $rank['rank_id'];
                $user_discount = isset($rank['discount']) ? $rank['discount'] : 100;
            } else {
                $user_rank = session('user_rank', 0);
                $user_discount = 100;
            }

            $time = TimeRepository::getGmTime();

            $goods_id = BaseRepository::getKeyPluck($list, 'goods_id');
            $volumeList = GoodsDataHandleService::getVolumePriceDataList($goods_id);
            $memberPriceList = GoodsDataHandleService::goodsMemberPrice($goods_id, $user_rank);
            $checkGoodsAttrList = $this->cartGoodsAttrPriceList($recGoodsAttrIdList);

            foreach ($list as $key => $row) {
                if (empty($row['package_buy'])) {

                    $goods = $priceList['goods']['goods_list'][$row['goods_id']] ?? [];
                    $warehouseGoodsList = $priceList['goods']['warehouse_goods_list'][$row['goods_id']] ?? [];
                    $warehouseAreaGoodsList = $priceList['goods']['warehouse_area_goods_list'][$row['goods_id']] ?? [];

                    $products = $priceList['product']['products_list'][$row['product_id']] ?? [];
                    $productsWarehouse = $priceList['product']['products_warehouse_list'][$row['product_id']] ?? [];
                    $productsArea = $priceList['product']['products_area_list'][$row['product_id']] ?? [];

                    if ($row['goods_model'] == 1) {

                        $sql = [
                            'where' => [
                                [
                                    'name' => 'region_id',
                                    'value' => $row['warehouse_id']
                                ]
                            ]
                        ];
                        $warehouseGoods = BaseRepository::getArraySqlFirst($warehouseGoodsList, $sql);

                        $shop_price = $warehouseGoods['warehouse_price'] ?? 0;
                        $promote_price = $warehouseGoods['warehouse_promote_price'] ?? 0;

                        $spec_price = $productsWarehouse['product_price'] ?? 0;
                        $spec_promote_price = $productsWarehouseList['product_promote_price'] ?? 0;

                        $goodsAttrList = $checkGoodsAttrList['warehouseAttrList'] ?? [];
                    } else if ($row['goods_model'] == 2) {

                        $sql = [
                            'where' => [
                                [
                                    'name' => 'region_id',
                                    'value' => $row['area_id']
                                ],
                                [
                                    'name' => 'city_id',
                                    'value' => $row['area_city']
                                ]
                            ]
                        ];
                        $warehouseAreaGoods = BaseRepository::getArraySqlFirst($warehouseAreaGoodsList, $sql);
                        $shop_price = $warehouseAreaGoods['warehouse_price'] ?? 0;
                        $promote_price = $warehouseAreaGoods['warehouse_promote_price'] ?? 0;

                        $spec_price = $productsArea['product_price'] ?? 0;
                        $spec_promote_price = $productsAreaList['product_promote_price'] ?? 0;

                        $goodsAttrList = $checkGoodsAttrList['areaAttrList'] ?? [];
                    } else {

                        $shop_price = $goods['rec_shop_price'] ?? 0;
                        $promote_price = $goods['rec_promote_price'] ?? 0;

                        $spec_price = $products['product_price'] ?? 0;
                        $spec_promote_price = $products['product_promote_price'] ?? 0;

                        $goodsAttrList = $checkGoodsAttrList['goodsAttrList'] ?? [];
                    }

                    $attr_price = 0; //复选属性价格
                    if ($row['goods_attr_id']) {
                        $sql = [
                            'whereIn' => [
                                [
                                    'name' => 'goods_attr_id',
                                    'value' => BaseRepository::getExplode($row['goods_attr_id'])
                                ]
                            ],
                            'where' => [
                                [
                                    'name' => 'attr_type',
                                    'value' => 2
                                ]
                            ]
                        ];
                        $goodsAttrList = BaseRepository::getArraySqlGet($goodsAttrList, $sql);
                        $attr_price = BaseRepository::getArraySum($goodsAttrList, 'attr_price');
                    }

                    /* 累加复选属性价格 */
                    $spec_price = $spec_price + $attr_price;
                    $spec_promote_price = $spec_promote_price + $attr_price;

                    $arr[$row['rec_id']]['rec_id'] = $row['rec_id'];
                    $arr[$row['rec_id']]['goods_id'] = $row['goods_id'];
                    $arr[$row['rec_id']]['product_id'] = $row['product_id'];
                    $arr[$row['rec_id']]['goods_attr_id'] = $row['goods_attr_id'];
                    $arr[$row['rec_id']]['shop_price'] = $shop_price;
                    $arr[$row['rec_id']]['promote_price'] = $promote_price;
                    $arr[$row['rec_id']]['spec_price'] = $spec_price;
                    $arr[$row['rec_id']]['spec_promote_price'] = $spec_promote_price;
                    $arr[$row['rec_id']]['is_promote'] = $goods['is_promote'] ?? 0;
                    $arr[$row['rec_id']]['promote_start_date'] = $goods['promote_start_date'] ?? '';
                    $arr[$row['rec_id']]['promote_end_date'] = $goods['promote_end_date'] ?? '';

                    /* 处理最终价格 start */
                    if ($goods && $goods['is_promote'] > 0 && $goods['promote_start_date'] < $time && $goods['promote_end_date'] > $time) {
                        $now_promote = 1;
                    } else {
                        $now_promote = 0;
                    }

                    $price_list = $volumeList[$row['goods_id']] ?? [];

                    $volume_price = 0; //商品优惠价格
                    $goods_num = $row['goods_number'];
                    if (!empty($price_list)) {
                        foreach ($price_list as $value) {
                            if ($goods_num >= $value['number']) {
                                $volume_price = $value['price'];
                            }
                        }
                    }
                    /* 处理最终价格 end */

                    // 商品原价不含会员折扣
                    $goods['shop_price_original'] = $goods['shop_price'] ?? 0;

                    $price = [
                        'model_price' => $row['goods_model'] ?? 0,
                        'user_price' => $memberPriceList[$row['goods_id']]['user_price'] ?? 0,
                        'percentage' => $memberPriceList[$row['goods_id']]['percentage'] ?? 0,
                        'warehouse_price' => $warehouseGoods['warehouse_price'] ?? 0,
                        'region_price' => $warehouseAreaGoods['region_price'] ?? 0,
                        'shop_price' => $goods['rec_shop_price'] ?? 0,
                        'warehouse_promote_price' => $warehouseGoods['warehouse_promote_price'] ?? 0,
                        'region_promote_price' => $warehouseAreaGoods['region_promote_price'] ?? 0,
                        'promote_price' => $goods['rec_promote_price'] ?? 0,
                        'wg_number' => $warehouseGoods['region_number'] ?? 0,
                        'wag_number' => $warehouseAreaGoods['region_number'] ?? 0,
                        'goods_number' => $goods['product_number'] ?? 0
                    ];

                    $price = $this->goodsCommonService->getGoodsPrice($price, $user_discount / 100, $goods);

                    $goods['user_price'] = $price['user_price'];
                    $goods['shop_price'] = $price['shop_price'];
                    $goods['promote_price'] = $price['promote_price'];
                    $goods['goods_number'] = $price['goods_number'];

                    /* 计算商品的属性促销价格 */
                    if ($row['product_id'] > 0 && config('shop.add_shop_price') == 0) {
                        $goods['promote_price'] = $spec_promote_price;
                    }

                    /* 计算商品的促销价格 */
                    if (isset($goods['promote_price']) && $goods['promote_price'] > 0) {
                        $promote_price = $this->goodsCommonService->getBargainPrice($goods['promote_price'], $goods['promote_start_date'], $goods['promote_end_date']);
                    } else {
                        $promote_price = 0;
                    }

                    //取得商品会员价格列表
                    if (!empty($spec_price) && config('shop.add_shop_price') == 0) {

                        /**
                         * 会员等级价格与属性价关系
                         * 1. 开启会员价格后 有会员等级价 优先取会员等级价; 若设置 百分比, 取属性价*会员等级百分比后价格
                         * 2. 开启会员价格后 有会员等级价 取会员等级价与属性价 最小值
                         * 3. 开启会员价格后 无会员等级价 取属性价*会员等级折扣
                         * 4. 禁用会员价格后 取 属性价，有属性促销价格 则优先取 属性促销价
                         */
                        // 商家商品禁用会员权益折扣
                        if (isset($goods['user_id']) && $goods['user_id'] > 0 && isset($goods['is_discount']) && $goods['is_discount'] == 0) {
                            $user_discount = 100;

                        } else {
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
                                $user_price = $spec_price * $user_discount / 100;
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

                    //如果需要加入规格价格[普通价格+属性价格]
                    if (!empty($row['product_id']) > 0 && config('shop.add_shop_price') == 1) {
                        $final_price += $spec_price;
                    }

                    $arr[$row['rec_id']]['pay_price'] = $final_price;
                }
            }
        }

        return $arr;
    }

    /**
     * 获取复选属性信息
     *
     * @param $recGoodsAttrIdList
     * @return array
     */
    private function cartGoodsAttrPriceList($recGoodsAttrIdList)
    {
        $goods_attr_id = BaseRepository::getImplode($recGoodsAttrIdList);
        $goods_attr_id = $this->dscRepository->delStrComma($goods_attr_id);
        $goods_attr_id = $goods_attr_id ? trim($goods_attr_id, ',') : '';

        $goodsAttrList = GoodsDataHandleService::getGoodsAttrDataList($goods_attr_id);
        $attrList = BaseRepository::getKeyPluck($goodsAttrList, 'attr_id');
        $attrList = GoodsDataHandleService::getAttributeDataList($attrList, 2);
        $attrList = BaseRepository::getKeyPluck($attrList, 'attr_id');

        if ($attrList) {
            $sql = [
                'whereIn' => [
                    [
                        'name' => 'attr_id',
                        'value' => $attrList
                    ]
                ]
            ];
            $goodsAttrList = BaseRepository::getArraySqlGet($goodsAttrList, $sql);
        } else {
            $goodsAttrList = [];
        }

        $goods_attr_id = BaseRepository::getKeyPluck($goodsAttrList, 'goods_attr_id');

        $warehouseAttrList = GoodsDataHandleService::getWarehouseAttrDataList($goods_attr_id);
        $areaAttrList = GoodsDataHandleService::getWarehouseAreaAttrDataList($goods_attr_id);

        return [
            'goodsAttrList' => $goodsAttrList,
            'warehouseAttrList' => $warehouseAttrList,
            'areaAttrList' => $areaAttrList
        ];
    }

    /**
     * 处理购物车商品数据组合
     *
     * @param $recList
     * @param $recGoodsList
     * @param $recGoodsModelList
     * @param $recNumberList
     * @param $recGoodsAttrIdList
     * @param $recWarehouseIdList
     * @param $recAreaIdList
     * @param $recAreaCityList
     * @param $recPackageBuyList
     * @param $recProductIdList
     * @return array
     */
    private function combinedArray($recList, $recGoodsList, $recGoodsModelList, $recNumberList, $recGoodsAttrIdList, $recWarehouseIdList, $recAreaIdList, $recAreaCityList, $recPackageBuyList, $recProductIdList)
    {
        $arr = [];
        if ($recList) {
            foreach ($recList as $key => $rec_id) {
                $arr[$key]['rec_id'] = $rec_id;
                $arr[$key]['goods_id'] = $recGoodsList[$rec_id] ?? 0;
                $arr[$key]['goods_number'] = $recNumberList[$rec_id] ?? 0;
                $arr[$key]['goods_attr_id'] = $recGoodsAttrIdList[$rec_id] ?? 0;
                $arr[$key]['warehouse_id'] = $recWarehouseIdList[$rec_id] ?? 0;
                $arr[$key]['area_id'] = $recAreaIdList[$rec_id] ?? 0;
                $arr[$key]['area_city'] = $recAreaCityList[$rec_id] ?? 0;
                $arr[$key]['package_buy'] = $recPackageBuyList[$rec_id] ?? '';
                $arr[$key]['product_id'] = $recProductIdList[$rec_id] ?? '';
                $arr[$key]['goods_model'] = $recGoodsModelList[$rec_id] ?? '';
            }
        }

        return $arr;
    }

    /**
     * 更新购物车活动ID
     *
     * @param int $rec_id
     * @param int $act_id
     * @param int $user_id
     */
    public function updateFavourableCartGoods($rec_id = 0, $act_id = 0, $user_id = 0)
    {
        if ($rec_id > 0 && $act_id > 0) {
            $update = Cart::where('rec_id', $rec_id)
                ->where('parent_id', 0);

            if ($user_id > 0) {
                $update = $update->where('user_id', $user_id);
            } else {
                $session_id = $this->sessionRepository->realCartMacIp();
                $update = $update->where('session_id', $session_id);
            }

            $update->update([
                'act_id' => $act_id
            ]);
        }
    }

    /**
     * 取得购物车中已有的优惠活动赠品数量
     *
     * @param int $user_id
     * @param int $act_id
     * @return array
     */
    public function cartFavourableGiftList($user_id = 0, $act_id = 0)
    {
        $res = Cart::select('rec_id', 'is_gift', 'goods_id', 'ru_id')->where('user_id', $user_id)
            ->where('rec_type', CART_GENERAL_GOODS);

        if ($act_id > 0) {
            $res = $res->where('is_gift', $act_id);
        } else {
            $res = $res->where('is_gift', '>', 0);
        }

        $res = BaseRepository::getToArrayGet($res);

        $list = [];
        if ($res) {
            $res = BaseRepository::getGroupBy($res, 'is_gift');
            foreach ($res as $key => $row) {
                $gift_num = BaseRepository::getArrayCount($row);
                $goods_list = BaseRepository::getKeyPluck($row, 'goods_id'); //赠品商品条数
                $list[$key]['goods_list'] = BaseRepository::getImplode($goods_list);
                $list[$key]['gift_num'] = $gift_num; //赠品商品条数
                $list[$key]['act_id'] = $key; //活动ID
                $list[$key]['ru_id'] = BaseRepository::getKeyPluck($row, 'ru_id'); //商品店铺ID列表
            }
        }

        return $list;
    }

    /**
     * 处理组合购买商品
     *
     * @param array $cart_goods
     * @return array
     */
    public function shippingCartGoodsList($cart_goods = [])
    {
        $group_id = BaseRepository::getKeyPluck($cart_goods, 'group_id');
        $group_id = ArrRepository::getArrayUnset($group_id);

        if ($group_id) {

            $arr = [];
            foreach ($cart_goods as $key => $value) {
                $parts = $value['parts'] ?? [];
                if ($parts) {
                    $arr[$key] = ArrRepository::getArrCollapse([[$value], $parts]);
                }

                unset($cart_goods[$key]['parts']);
            }

            $arr = ArrRepository::getArrCollapse($arr);
            $cart_goods = BaseRepository::getArrayMerge($cart_goods, $arr);
            $cart_goods = BaseRepository::getArrayUnique($cart_goods, 'rec_id');
        }

        return $cart_goods;
    }
}
