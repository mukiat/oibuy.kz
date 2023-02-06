<?php

namespace App\Modules\Web\Controllers;

use App\Models\Cart;
use App\Models\UserBonus;
use App\Models\Users;
use App\Models\ValueCard;
use App\Repositories\Cart\CartRepository;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscEncryptRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Flow\FlowRepository;
use App\Services\Activity\GroupBuyService;
use App\Services\Activity\ValueCardService;
use App\Services\Cart\CartCommonService;
use App\Services\Cart\CartGoodsService;
use App\Services\Common\AreaService;
use App\Services\Coupon\CouponDataHandleService;
use App\Services\Coupon\CouponsUserService;
use App\Services\CrossBorder\CrossBorderService;
use App\Services\Flow\FlowActivityService;
use App\Services\Flow\FlowOrderService;
use App\Services\Flow\FlowService;
use App\Services\Flow\FlowUserService;
use App\Services\Goods\GoodsFittingService;
use App\Services\Goods\GoodsGuessService;
use App\Services\Goods\GoodsService;

class AjaxFlowActivityController extends InitController
{
    protected $dscRepository;
    protected $sessionRepository;
    protected $cartCommonService;
    protected $flowUserService;
    protected $flowService;
    protected $goodsService;
    protected $areaService;
    protected $cartRepository;
    protected $cartGoodsService;
    protected $flowActivityService;
    protected $goodsGuessService;
    protected $couponsUserService;
    protected $goodsFittingService;
    protected $flowOrderService;
    protected $valueCardService;

    public function __construct(
        DscRepository $dscRepository,
        SessionRepository $sessionRepository,
        CartCommonService $cartCommonService,
        FlowUserService $flowUserService,
        FlowService $flowService,
        GoodsService $goodsService,
        AreaService $areaService,
        CartRepository $cartRepository,
        CartGoodsService $cartGoodsService,
        FlowActivityService $flowActivityService,
        GoodsGuessService $goodsGuessService,
        CouponsUserService $couponsUserService,
        GoodsFittingService $goodsFittingService,
        FlowOrderService $flowOrderService,
        ValueCardService $valueCardService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->sessionRepository = $sessionRepository;
        $this->cartCommonService = $cartCommonService;
        $this->flowUserService = $flowUserService;
        $this->flowService = $flowService;
        $this->goodsService = $goodsService;
        $this->areaService = $areaService;
        $this->cartRepository = $cartRepository;
        $this->cartGoodsService = $cartGoodsService;
        $this->flowActivityService = $flowActivityService;
        $this->goodsGuessService = $goodsGuessService;
        $this->couponsUserService = $couponsUserService;
        $this->goodsFittingService = $goodsFittingService;
        $this->flowOrderService = $flowOrderService;
        $this->valueCardService = $valueCardService;
    }

    public function index()
    {
        if ($this->checkReferer() === false) {
            return response()->json(['error' => 1, 'message' => 'referer error']);
        }

        load_helper('order');

        /* 载入语言文件 */
        $this->dscRepository->helpersLang(['flow', 'user', 'shopping_flow']);

        /**
         * Start
         *
         * @param $warehouse_id 仓库ID
         * @param $area_id 省份ID
         * @param $area_city 城市ID
         */
        $warehouse_id = $this->warehouseId();
        $area_id = $this->areaId();
        $area_city = $this->areaCity();
        /* End */

        $flow_region = request()->cookie('flow_region', '');

        $session_id = $this->sessionRepository->realCartMacIp();
        $user_id = session('user_id', 0);

        $cart_value = $this->cartCommonService->getCartValue();

        $this->smarty->assign('lang', $GLOBALS['_LANG']);

        $step = addslashes(trim(request()->input('step', '')));
        /*------------------------------------------------------ */
        //-- 显示赠品商品
        /*------------------------------------------------------ */
        if ($step == 'show_gift_div') {
            $favourable_id = intval(request()->input('favourable_id', 0));
            // 被选中的商品商家ID
            $ru_id = (int)request()->input('ru_id', 0);
            // 被选中的优惠活动商品
            $act_sel_id = addslashes(request()->input('sel_id', ''));
            // 标志flag
            $sel_flag = addslashes(request()->input('sel_flag', ''));
            $act_sel = ['act_sel_id' => $act_sel_id, 'act_sel' => $sel_flag];

            $favourable = favourable_list(session('user_rank'), -1, $favourable_id, $act_sel, $ru_id, $user_id);
            $activity = $favourable[0];

            $activity['act_type_ext'] = intval($activity['act_type_ext']); // 允许最大数量

            // 已加入购物车的活动赠品数量
            $cartFavourableGiftList = $this->cartCommonService->cartFavourableGiftList($user_id, $favourable_id);
            $cart_favourable_num = $cartFavourableGiftList[$favourable_id]['gift_num'] ?? 0;

            $activity['cart_favourable_gift_num'] = $cart_favourable_num;
            $activity['favourable_used'] = favourable_used($activity, $cart_favourable_num);

            $act_type_ext = intval($activity['act_type_ext']);

            if ($act_type_ext >= $cart_favourable_num) {
                $activity['left_gift_num'] = $act_type_ext - $cart_favourable_num;
            } else {
                $activity['left_gift_num'] = 0;
            }

            foreach ($activity['gift'] as $key => $row) {
                $activity['act_gift_list'][$key] = $row;
                $activity['act_gift_list'][$key]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $row['id']], $row['name']);
            }

            $this->smarty->assign('activity', $activity);
            $this->smarty->assign('ru_id', $ru_id);
            $this->smarty->assign('act_id', $favourable_id);
            $result['content'] = $this->smarty->fetch("library/cart_gift_box.lbi");
            $result['act_id'] = $favourable_id;
            $result['ru_id'] = $ru_id;

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 添加礼包到购物车
        /*------------------------------------------------------ */
        elseif ($step == 'add_package_to_cart') {
            $package_info = json_str_iconv(request()->input('package_info', ''));

            $result = ['error' => 0, 'message' => '', 'content' => '', 'package_id' => ''];

            if (empty($package_info)) {
                $result['error'] = 1;
                return response()->json($result);
            }

            $package = dsc_decode($package_info);

            $package->type = isset($package->type) && !empty($package->type) ? $package->type : 0;

            /* 商品数量是否合法 */
            if (!is_numeric($package->number) || intval($package->number) <= 0) {
                $result['error'] = 1;
                $result['message'] = $GLOBALS['_LANG']['invalid_number'];
            } else {

                /**
                 * 购买前删除会员的超值礼包
                 */
                if ($user_id > 0) {
                    Cart::where('user_id', $user_id)->where('extension_code', 'package_buy')->delete();
                } else {
                    Cart::where('session_id', $session_id)->where('extension_code', 'package_buy')->delete();
                }

                $package->warehouse_id = $package->warehouse_id ?? 0;
                $package->area_id = $package->area_id ?? 0;
                $package->area_city = $package->area_city ?? 0;

                /* 添加到购物车 */
                $res = add_package_to_cart($package->package_id, $package->number, $package->warehouse_id, $package->area_id, $package->area_city, $package->type);

                if (!is_array($res) && $res) {
                    if (config('shop.cart_confirm') > 2) {
                        $result['message'] = '';
                    } else {
                        $result['message'] = config('shop.cart_confirm') == 1 ? $GLOBALS['_LANG']['addto_cart_success_1'] : $GLOBALS['_LANG']['addto_cart_success_2'];
                    }

                    $result['content'] = insert_cart_info(4);
                    $result['one_step_buy'] = session('one_step_buy', 0);
                } else {
                    if (is_array($res)) {
                        $result['message'] = sprintf($GLOBALS['_LANG']['package_null'], $res['goods_name']);
                        $result['error'] = $res['error'];
                    } else {
                        $result['message'] = $this->err->last_message();
                        $result['error'] = $this->err->error_no();
                    }
                }

                $result['package_id'] = intval($package->package_id);
            }

            $confirm_type = isset($package->confirm_type) ? $package->confirm_type : 0;

            if ($confirm_type > 0) {
                $result['confirm_type'] = $confirm_type;
            } else {
                $result['confirm_type'] = !empty(config('shop.cart_confirm')) ? config('shop.cart_confirm') : 2;
            }

            session([
                'flow_type' => CART_PACKAGE_GOODS
            ]);

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 验证储值卡密码
        /*------------------------------------------------------ */
        elseif ($step == 'validate_value_card') {
            $vc_psd = addslashes(trim(request()->input('vc_psd', '')));

            if (!empty($vc_psd)) {
                $value_card = $this->valueCardService->orderValueCardInfo(0, $vc_psd);
            } else {
                $value_card = [];
            }

            $result = ['error' => '', 'content' => ''];


            /* 取得购物类型 */
            $flow_type = intval(session('flow_type', CART_GENERAL_GOODS));

            $warehouse_id = (int)request()->input('warehouse_id', 0);
            $area_id = (int)request()->input('area_id', 0);

            $shipping_id = strip_tags(urldecode(request()->input('shipping_id', '')));
            $tmp_shipping_id_arr = dsc_decode($shipping_id, true);

            $this->smarty->assign('warehouse_id', $warehouse_id);
            $this->smarty->assign('area_id', $area_id);

            /* 获得收货人信息 */
            $consignee = $this->flowUserService->getConsignee(session('user_id'));

            /* 对商品信息赋值 */
            $cart_goods = cart_goods($flow_type, $cart_value); // 取得商品列表，计算合计

            if (!empty($value_card)) {
                if ($value_card['use_condition'] == 1 && !comparison_cat($cart_goods, $value_card['spec_cat'])) {
                    $value_card['error'] = true;
                }
                if ($value_card['use_condition'] == 2 && !comparison_goods($cart_goods, $value_card['spec_goods'])) {
                    $value_card['error'] = true;
                }
            }

            if (empty($cart_goods) || !$this->flowUserService->checkConsigneeInfo($consignee, $flow_type)) {
                $result['error'] = $GLOBALS['_LANG']['no_goods_in_cart'];
            } else {
                /* 取得购物流程设置 */
                $this->smarty->assign('config', $GLOBALS['_CFG']);

                /* 取得订单信息 */
                $order = flow_order_info();

                if (!empty($value_card) && ($value_card['card_money'] > 0 && $value_card['user_id'] == 0) && empty($value_card['error'])) {
                    $now = gmtime();
                    if ($now > $value_card['end_time'] && $value_card['end_time'] > 0) {
                        $order['vc_id'] = '';
                        $result['error'] = $GLOBALS['_LANG']['vc_use_expire'];
                    } else {
                        $count = ValueCard::where('user_id', $user_id)->where('tid', $value_card['tid'])->count();

                        if ($count >= $value_card['vc_limit']) {
                            $order['vc_id'] = '';
                            $result['error'] = $GLOBALS['_LANG']['over_bind_limit'];
                        } else {
                            $order['vc_id'] = $value_card['vid'];
                            $order['vc_psd'] = $vc_psd;
                        }
                    }
                } elseif ($value_card['error']) {
                    $result['error'] = $GLOBALS['_LANG']['vc_no_use_order'];
                    $order['vid'] = '';
                } else {
                    $order['vid'] = '';
                    $result['error'] = $GLOBALS['_LANG']['vc_not_exist'];
                }

                $cart_goods_number = $this->cartCommonService->getBuyCartGoodsNumber($flow_type, $cart_value);
                $this->smarty->assign('cart_goods_number', $cart_goods_number);

                if (!empty($consignee)) {
                    $consignee['province_name'] = get_goods_region_name($consignee['province']);
                    $consignee['city_name'] = get_goods_region_name($consignee['city']);
                    $consignee['district_name'] = get_goods_region_name($consignee['district']);
                    $consignee['street_name'] = get_goods_region_name($consignee['street']);
                    $consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
                }
                $this->smarty->assign('consignee', $consignee);

                $cart_goods_list = cart_goods($flow_type, $cart_value, 1); // 取得商品列表，计算合计
                $this->smarty->assign('goods_list', $cart_goods_list);

                //切换配送方式
                $cart_goods_list = get_flowdone_goods_list($cart_goods_list, $tmp_shipping_id_arr);

                /* 计算订单的费用 */
                $total = order_fee($order, $cart_goods, $consignee, $cart_value, $cart_goods_list, 0, '', 0, 0, $flow_type);
                $this->smarty->assign('total', $total);

                /* 团购标志 */
                if ($flow_type == CART_GROUP_BUY_GOODS) {
                    $this->smarty->assign('is_group_buy', 1);
                } elseif ($flow_type == CART_EXCHANGE_GOODS) {
                    // 积分兑换 qin
                    $this->smarty->assign('is_exchange_goods', 1);
                }

                $result['content'] = $this->smarty->fetch('library/order_total.lbi');
            }

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 验证红包序列号
        /*------------------------------------------------------ */
        elseif ($step == 'validate_bonus') {
            $bonus_psd = addslashes(trim(request()->input('bonus_psd', '')));

            if (!empty($bonus_psd)) {
                $bonus = bonus_info(0, $bonus_psd, $cart_value);
            } else {
                $bonus = [];
            }

            $result = ['error' => '', 'content' => ''];

            /* 取得购物类型 */
            $flow_type = intval(session('flow_type', CART_GENERAL_GOODS));
            $shipping_id = strip_tags(urldecode(request()->input('shipping_id', '')));
            $tmp_shipping_id_arr = dsc_decode($shipping_id, true);

            $warehouse_id = (int)request()->input('warehouse_id', 0);
            $area_id = (int)request()->input('area_id', 0);

            $this->smarty->assign('warehouse_id', $warehouse_id);
            $this->smarty->assign('area_id', $area_id);

            /* 获得收货人信息 */
            $consignee = $this->flowUserService->getConsignee(session('user_id'));

            /* 对商品信息赋值 */
            $cart_goods = cart_goods($flow_type, $cart_value); // 取得商品列表，计算合计

            if (empty($cart_goods) || !$this->flowUserService->checkConsigneeInfo($consignee, $flow_type)) {
                $result['error'] = $GLOBALS['_LANG']['no_goods_in_cart'];
            } else {
                /* 取得购物流程设置 */
                $this->smarty->assign('config', $GLOBALS['_CFG']);

                /* 取得订单信息 */
                $order = flow_order_info();


                if (((!empty($bonus) && $bonus['user_id'] == session('user_id')) || ($bonus['type_money'] > 0 && empty($bonus['user_id']))) && $bonus['order_id'] <= 0) {
                    $now = gmtime();
                    if ($now > $bonus['use_end_date']) {
                        $order['bonus_id'] = '';
                        $result['error'] = $GLOBALS['_LANG']['bonus_use_expire'];
                    } else {
                        $order['bonus_id'] = $bonus['bonus_id'];
                        $order['bonus_psd'] = $bonus_psd;
                    }
                } else {
                    $order['bonus_id'] = '';
                    $result['error'] = $GLOBALS['_LANG']['invalid_bonus'];
                }

                $cart_goods_number = $this->cartCommonService->getBuyCartGoodsNumber($flow_type, $cart_value);
                $this->smarty->assign('cart_goods_number', $cart_goods_number);

                if (!empty($consignee)) {
                    $consignee['province_name'] = get_goods_region_name($consignee['province']);
                    $consignee['city_name'] = get_goods_region_name($consignee['city']);
                    $consignee['district_name'] = get_goods_region_name($consignee['district']);
                    $consignee['street_name'] = get_goods_region_name($consignee['street']);
                    $consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
                }
                $this->smarty->assign('consignee', $consignee);

                $cart_goods_list = cart_goods($flow_type, $cart_value, 1); // 取得商品列表，计算合计
                $this->smarty->assign('goods_list', $cart_goods_list);

                //切换配送方式
                $cart_goods_list = get_flowdone_goods_list($cart_goods_list, $tmp_shipping_id_arr);

                $total = order_fee($order, $cart_goods, $consignee, $cart_value, $cart_goods_list, 0, '', 0, 0, $flow_type);
                $this->smarty->assign('total', $total);

                if ($total['goods_price'] < $bonus['min_goods_amount']) {
                    $order['bonus_id'] = '';
                    /* 重新计算订单 */
                    $result['error'] = sprintf($GLOBALS['_LANG']['bonus_min_amount_error'], $bonus['min_goods_amount']);
                }

                /* 团购标志 */
                if ($flow_type == CART_GROUP_BUY_GOODS) {
                    $this->smarty->assign('is_group_buy', 1);
                } elseif ($flow_type == CART_EXCHANGE_GOODS) {
                    // 积分兑换 qin
                    $this->smarty->assign('is_exchange_goods', 1);
                }

                $result['content'] = $this->smarty->fetch('library/order_total.lbi');
            }

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 把优惠活动加入购物车
        /*------------------------------------------------------ */
        elseif ($step == 'add_favourable') {

            $ru_id = (int)request()->input('ru_id', 0);
            // 被选中的优惠活动商品
            $act_sel_id = addslashes(request()->input('sel_id', ''));
            // 标志flag
            $sel_flag = addslashes(request()->input('sel_flag', ''));
            $act_sel = ['act_sel_id' => $act_sel_id, 'act_sel' => $sel_flag];

            // 获取要添加到购物车的赠品
            $select_gift = request()->input('select_gift', '');
            $select_gift = BaseRepository::getExplode($select_gift);
            $select_gift = DscEncryptRepository::filterValInt($select_gift);

            /* 取得优惠活动信息 */
            $act_id = intval(request()->input('act_id', 0));
            $favourable = favourable_info($act_id);
            if (empty($favourable)) {
                $result['error'] = 1;
                $result['message'] = $GLOBALS['_LANG']['favourable_not_exist'];
                return response()->json($result);
            }

            /* 判断用户能否享受该优惠 */
            if (!favourable_available($favourable, $act_sel)) {
                $result['error'] = 2;
                $result['message'] = $GLOBALS['_LANG']['favourable_not_available'];
                return response()->json($result);
            }

            /* 赠品（特惠品）优惠 */
            if ($favourable['act_type'] == FAT_GOODS) {
                /* 检查是否选择了赠品 */
                if (empty($select_gift)) {
                    $result['error'] = 4;
                    $result['message'] = $GLOBALS['_LANG']['pls_select_gift'];
                    return response()->json($result);
                }

                /* 检查数量是否超过上限 */
                $giftCartNum = count($select_gift);
                if ($favourable['act_type_ext'] > 0 && $giftCartNum > $favourable['act_type_ext']) {
                    $result['error'] = 6;
                    $result['message'] = $GLOBALS['_LANG']['gift_count_exceed'];
                    return response()->json($result);
                }

                /* 添加赠品到购物车 */
                if ($favourable['gift']) {

                    /* 删除用户原有的活动商品 */
                    $delCart = Cart::where('is_gift', $act_id);

                    if ($user_id > 0) {
                        $delCart = $delCart->where('user_id', $user_id);
                    } else {
                        $session_id = $this->sessionRepository->realCartMacIp();
                        $delCart = $delCart->where('session_id', $session_id);
                    }

                    $delCart->delete();

                    foreach ($favourable['gift'] as $gift) {
                        if (in_array($gift['id'], $select_gift)) {
                            $this->flowService->getAddGiftToCart($act_id, $gift['id'], $gift['price'], $user_id);
                        }
                    }
                }

                // 返回优惠活动
                $favourable_box = $this->flowService->getCartAddFavourableBox($act_id, $act_sel, $ru_id, $user_id);

                $cart_value = BaseRepository::getExplode($cart_value);

                $cart_values = [];
                if ($act_sel_id) {
                    $cart_values = explode(',', $act_sel_id);
                }
                $cart_value = array_unique(array_merge($cart_value, $cart_values));

                if (!empty($favourable_box['act_cart_gift'])) {
                    foreach ($favourable_box['act_cart_gift'] as $kk => $vv) {
                        $cart_value[] = $vv['rec_id'];
                    }
                }

                if ($cart_value) {
                    $new_cart_value = implode(',', $cart_value);

                    if (!empty($new_cart_value)) {
                        $session_value = $this->cartRepository->pushCartValue();
                        $session_value = empty($session_value) ? $new_cart_value : BaseRepository::getImplode($session_value) . ',' . BaseRepository::getImplode($new_cart_value);
                        $session_value = BaseRepository::getExplode($session_value);

                        $this->cartRepository->pushCartValue($session_value);
                    }

                    $result['cart_value'] = implode(',', $cart_value);
                }

                /* 计算折扣 */
                $discount = compute_discount(3, $result['cart_value'] ?? '');
                $flow_type = intval(session('flow_type', CART_GENERAL_GOODS));
                $cart_goods = cart_goods($flow_type, $result['cart_value']); // 取得商品列表

                $goods_amount = get_cart_check_goods($cart_goods);

                /* 商品阶梯优惠 + 优惠活动金额 ($goods_amount['save_amount'] + ) */
                $fav_amount = $discount['discount'];

                //节省
                $save_total_amount = $this->dscRepository->getPriceFormat($fav_amount + $goods_amount['save_amount']);
                $result['save_total_amount'] = $save_total_amount;

                //商品阶梯优惠
                $result['dis_amount'] = $goods_amount['save_amount'];

                $result['error'] = 0;
                $result['message'] = '';

                if ($goods_amount['subtotal_amount'] > 0) {
                    if ($goods_amount['subtotal_amount'] > $fav_amount) {
                        $goods_amount['subtotal_amount'] = $goods_amount['subtotal_amount'] - $fav_amount;
                    } else {
                        $goods_amount['subtotal_amount'] = 0;
                    }
                } else {
                    $result['subtotal_amount'] = 0;
                }

                $result['goods_amount'] = $this->dscRepository->getPriceFormat($goods_amount['subtotal_amount'], false);
                $result['subtotal_number'] = $goods_amount['subtotal_number'];

                $this->smarty->assign('activity', $favourable_box);
                $this->smarty->assign('ru_id', $ru_id);
                $result['content'] = $this->smarty->fetch("library/cart_favourable_box.lbi");
                $result['act_id'] = $act_id;
            } elseif ($favourable['act_type'] == FAT_DISCOUNT) {
                $this->flowService->getAddFavourableToCart($act_id, $favourable['act_name'], cart_favourable_amount($favourable) * (100 - $favourable['act_type_ext']) / 100);
            } elseif ($favourable['act_type'] == FAT_PRICE) {
                $this->flowService->getAddFavourableToCart($act_id, $favourable['act_name'], $favourable['act_type_ext']);
            }

            $result['ru_id'] = $ru_id;
            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 购物车切换商品优惠活动
        /* ------------------------------------------------------ */
        elseif ($step == 'cart_change_fav') {
            $result = ['error' => 0, 'message' => ''];

            get_request_filter();

            //促销活动ID
            $act_id = intval(request()->input('aid', 0));
            //购物车ID
            $rec_id = intval(request()->input('rid', 0));

            //删除原商品促销活动的赠品
            $res = Cart::where('rec_id', $rec_id);

            if (!empty($user_id)) {
                $res = $res->where('user_id', $user_id);
            } else {
                $res = $res->where('session_id', $session_id);
            }

            $old_act_id = $res->value('act_id');

            if ($old_act_id) {
                $del = Cart::where('is_gift', $old_act_id);

                if (!empty($user_id)) {
                    $del = $del->where('user_id', $user_id);
                } else {
                    $del = $del->where('session_id', $session_id);
                }

                $del->delete();
            }

            //更新购物车指定商品促销活动ID
            $this->cartCommonService->updateFavourableCartGoods($rec_id, $act_id, $user_id);

            $this->smarty->assign('area_id', $area_id); //省下级市
            $this->smarty->assign('flow_region', $flow_region); //省下级市

            /* 取得商品列表，计算合计 */
            $cart_goods = get_cart_goods('', 1);

            if (CROSS_BORDER === true) { // 跨境多商户
                $web = app(CrossBorderService::class)->webExists();

                if (!empty($web)) {
                    $result['can_buy'] = $web->assignThree($cart_goods);
                } else {
                    return show_message('service not exists', $GLOBALS['_LANG']['back_to_cart'], 'flow.php');
                }
            }

            // 对同一商家商品按照活动分组
            $merchant_goods = $cart_goods['goods_list'];
            $merchant_goods_list = $this->flowActivityService->getFavourableCartGoodsList($merchant_goods, $user_id);
            $merchant_goods_list = $this->flowActivityService->merchantActivityCartGoodsList($merchant_goods_list);

            $this->smarty->assign('goods_list', $merchant_goods_list);
            $this->smarty->assign('total', $cart_goods['total']);

            //购物车的描述的格式化
            $this->smarty->assign('shopping_money', sprintf($GLOBALS['_LANG']['shopping_money'], $cart_goods['total']['goods_price']));
            $this->smarty->assign('market_price_desc', sprintf($GLOBALS['_LANG']['than_market_price'], $cart_goods['total']['market_price'], $cart_goods['total']['saving'], $cart_goods['total']['save_rate']));

            /* 计算折扣 */
            $discount = compute_discount();
            $this->smarty->assign('discount', $discount['discount']);
            $favour_name = empty($discount['name']) ? '' : join(',', $discount['name']);
            $this->smarty->assign('your_discount', sprintf($GLOBALS['_LANG']['your_discount'], $favour_name, $this->dscRepository->getPriceFormat($discount['discount'])));

            /* 增加是否在购物车里显示商品图 */
            $this->smarty->assign('show_goods_thumb', config('shop.show_goods_in_cart'));

            /* 增加是否在购物车里显示商品属性 */
            $this->smarty->assign('show_goods_attribute', config('shop.show_attr_in_cart'));

            //取得购物车中基本件ID

            $parent_list = Cart::select('goods_id')->where('rec_type', 'CART_GENERAL_GOODS')
                ->where('is_gift', 0)
                ->where('extension_code', 'package_buy')
                ->where('parent_id', 0);

            if (!empty($user_id)) {
                $parent_list = $parent_list->where('user_id', $user_id);
            } else {
                $parent_list = $parent_list->where('session_id', $session_id);
            }

            $parent_list = BaseRepository::getToArrayGet($parent_list);
            $parent_list = BaseRepository::getKeyPluck($parent_list, 'goods_id');

            $fittings_list = $this->goodsFittingService->getGoodsFittings($parent_list);

            $this->smarty->assign('fittings_list', $fittings_list);

            /**
             * Start
             *
             * 猜你喜欢商品
             */
            $where = [
                'warehouse_id' => $warehouse_id,
                'area_id' => $area_id,
                'area_city' => $area_city,
                'user_id' => $user_id,
                'history' => 1,
                'page' => 1,
                'limit' => 18
            ];
            $guess_goods = $this->goodsGuessService->getGuessGoods($where);

            // 推荐商品
            $where = [
                'warehouse_id' => $warehouse_id,
                'area_id' => $area_id,
                'area_city' => $area_city
            ];

            /* 最新商品 */
            $where['type'] = 'best';
            $best_goods = $this->goodsService->getRecommendGoods($where);

            $this->smarty->assign('guess_goods', $guess_goods);
            $this->smarty->assign('guessGoods_count', count($guess_goods));
            $this->smarty->assign('best_goods', $best_goods);
            $this->smarty->assign('bestGoods_count', count($best_goods));

            $this->smarty->assign('province_row', get_region_info($this->province_id));
            $this->smarty->assign('city_row', get_region_info($this->city_id));
            $this->smarty->assign('district_row', get_region_info($this->district_id));

            $result['cart_value'] = $this->cartCommonService->getCartValue();
            $result['cart_value'] = BaseRepository::getImplode($result['cart_value']);

            $province_list = $this->areaService->getWarehouseProvince();
            $city_list = $this->areaService->getRegionCityCounty($this->province_id);
            $district_list = $this->areaService->getRegionCityCounty($this->city_id);

            foreach ($province_list as $k => $v) {
                $province_list[$k]['choosable'] = true;
            }
            foreach ($city_list as $k => $v) {
                $city_list[$k]['choosable'] = true;
            }
            foreach ($district_list as $k => $v) {
                $district_list[$k]['choosable'] = true;
            }

            $this->smarty->assign('province_list', $province_list); //省、直辖市
            $this->smarty->assign('city_list', $city_list); //省下级市
            $this->smarty->assign('district_list', $district_list); //市下级县


            $result['content'] = $this->smarty->fetch("library/cart_box.lbi");

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 改变优惠券
        /*------------------------------------------------------ */
        elseif ($step == 'change_coupons') {
            $result = ['error' => 0, 'content' => ''];

            $warehouse_id = (int)request()->input('warehouse_id', 0);
            $area_id = (int)request()->input('area_id', 0);
            $store_id = (int)request()->input('store_id', 0);

            $shipping_id = strip_tags(urldecode(request()->input('shipping_id', '')));
            $tmp_shipping_id_arr = dsc_decode($shipping_id, true);

            /* 优惠券ID */
            $uc_id = request()->input('uc_id', 0);
            $uc_id = DscEncryptRepository::filterValInt($uc_id);
            $uc_id = BaseRepository::getExplode($uc_id);
            $uc_id = ArrRepository::getArrayUnset($uc_id);

            $this->smarty->assign('warehouse_id', $warehouse_id);
            $this->smarty->assign('area_id', $area_id);
            $this->smarty->assign('store_id', $store_id);

            /* 取得购物类型 */
            $flow_type = intval(session('flow_type', CART_GENERAL_GOODS));

            /* 获得收货人信息 */
            $consignee = $this->flowUserService->getConsignee(session('user_id'));

            /* 对商品信息赋值 */
            $cart_goods_list = cart_goods($flow_type, $cart_value, 1); // 取得商品列表，计算合计
            $cart_goods = FlowRepository::getNewGroupCartGoods($cart_goods_list);

            if (empty($cart_goods) || !$this->flowUserService->checkConsigneeInfo($consignee, $flow_type)) {
                if (empty($cart_goods)) {
                    $result['error'] = 1;
                } else {
                    $result['error'] = 2;
                }
            } else {

                /* 取得购物流程设置 */
                $this->smarty->assign('config', $GLOBALS['_CFG']);

                /* 取得订单信息 */
                $order = flow_order_info();
                session([
                    'flow_order' => []
                ]);

                $order['user_id'] = $user_id;

                $cart_goods_number = $this->cartCommonService->getBuyCartGoodsNumber($flow_type, $cart_value);
                $this->smarty->assign('cart_goods_number', $cart_goods_number);

                if (!empty($consignee)) {
                    $consignee['province_name'] = get_goods_region_name($consignee['province']);
                    $consignee['city_name'] = get_goods_region_name($consignee['city']);
                    $consignee['district_name'] = get_goods_region_name($consignee['district']);
                    $consignee['street_name'] = get_goods_region_name($consignee['street']);
                    $consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
                }
                $this->smarty->assign('consignee', $consignee);
                $this->smarty->assign('goods_list', $cart_goods_list);

                /* 获取优惠券信息 */
                $couponsList = $this->couponsUserService->getCouponsUserSerList($uc_id, $user_id, $cart_goods);
                $uc_id = BaseRepository::getKeyPluck($couponsList, 'uc_id');
                $cou_id = BaseRepository::getKeyPluck($couponsList, 'cou_id');

                if ((!empty($couponsList)) || !empty($uc_id)) {
                    $order['uc_id'] = $uc_id;
                } else {
                    $order['uc_id'] = 0;
                }

                session([
                    'flow_order' => [
                        'uc_id' => $order['uc_id']
                    ]
                ]);

                /* 优惠券 免邮 start */
                $not_freightfree = 0; //是否含有不支持免邮的地区
                if (!empty($couponsList) && $cart_goods) {

                    $couponsList = BaseRepository::getGroupBy($couponsList, 'ru_id');

                    $couponsRegionList = CouponDataHandleService::getCouponsRegionDataList($cou_id);
                    foreach ($couponsList as $key => $row) {
                        $sql = [
                            'where' => [
                                [
                                    'name' => 'ru_id',
                                    'value' => $key
                                ]
                            ]
                        ];
                        $ru_cart_goods = BaseRepository::getArraySqlGet($cart_goods, $sql);
                        $goods_amount = BaseRepository::getArraySum($ru_cart_goods, ['goods_price', 'goods_number']);

                        $coupons_info = BaseRepository::getArrayFirst($row);

                        if ($coupons_info['cou_type'] == VOUCHER_SHIPPING) {
                            if ($goods_amount >= $coupons_info['cou_man'] || $coupons_info['cou_man'] == 0) {
                                $cou_region = $couponsRegionList[$coupons_info['cou_id']]['region_list'] ?? '';
                                $cou_region = BaseRepository::getExplode($cou_region);

                                if ($cou_region && in_array($consignee['province'], $cou_region)) {
                                    $not_freightfree += 1;
                                }
                            }
                        }
                    }
                }
                /* 优惠券 免邮 start */

                $result['not_freightfree'] = $not_freightfree;

                //切换配送方式
                $cart_goods_list = get_flowdone_goods_list($cart_goods_list, $tmp_shipping_id_arr);

                /* 重新赋值 */
                $cart_goods = $this->flowOrderService->couponsBonusFlowShareEqually($cart_goods, $order, $couponsList, 1);

                /* 计算订单的费用 */
                $total = order_fee($order, $cart_goods, $consignee, $cart_value, $cart_goods_list, $store_id, '', 0, 0, $flow_type);

                $result['dis_type'] = 'coupons';

                $result['check_type'] = 0;

                if (CROSS_BORDER === true) { // 跨境多商户
                    $web = app(CrossBorderService::class)->webExists();

                    if (!empty($web)) {
                        $arr = [
                            'consignee' => $consignee ?? '',
                            'rec_type' => $flow_type ?? 0,
                            'store_id' => $store_id ?? 0,
                            'cart_value' => $cart_value ?? '',
                            'type' => $type ?? 0,
                            'uc_id' => $order['uc_id'] ?? 0
                        ];
                        $amount = $web->assignNewRatePrice($cart_goods_list, $total['amount'], $arr);
                        $total['amount'] = $amount['amount'];
                        $total['amount_formated'] = $amount['amount_formated'];
                    } else {
                        return response()->json(['error' => 1, 'message' => 'service not exists']);
                    }
                }
                $this->smarty->assign('total', $total);

                /* 团购标志 */
                if ($flow_type == CART_GROUP_BUY_GOODS) {
                    $this->smarty->assign('is_group_buy', 1);
                } elseif ($flow_type == CART_EXCHANGE_GOODS) {
                    $this->smarty->assign('is_exchange_goods', 1);
                }

                $result['content'] = $this->smarty->fetch('library/order_total.lbi');
            }

            $result['uc_id'] = isset($order['uc_id']) && !empty($order['uc_id']) ? BaseRepository::getImplode($order['uc_id'] ?? []) : 0;
            $result['uc_id'] = empty($result['uc_id']) ? 0 : $result['uc_id'];

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 改变储值卡
        /*------------------------------------------------------ */
        elseif ($step == 'change_value_card') {
            $result = ['error' => '', 'content' => ''];
            /* 取得购物类型 */
            $flow_type = intval(session('flow_type', CART_GENERAL_GOODS));

            $shipping_id = strip_tags(urldecode(request()->input('shipping_id', '')));
            $tmp_shipping_id_arr = dsc_decode($shipping_id, true);

            $warehouse_id = (int)request()->input('warehouse_id', 0);
            $area_id = (int)request()->input('area_id', 0);
            $store_id = (int)request()->input('store_id', 0);

            $this->smarty->assign('warehouse_id', $warehouse_id);
            $this->smarty->assign('area_id', $area_id);
            $this->smarty->assign('store_id', $store_id);

            /* 获得收货人信息 */
            $consignee = $this->flowUserService->getConsignee(session('user_id'));

            /* 对商品信息赋值 */
            $cart_goods = cart_goods($flow_type, $cart_value, 0, '', $store_id); // 取得商品列表，计算合计
            if (empty($cart_goods) || !$this->flowUserService->checkConsigneeInfo($consignee, $flow_type)) {
                if (empty($cart_goods)) {
                    $result['error'] = 1;
                } else {
                    $result['error'] = 2;
                }
            } else {
                /* 取得购物流程设置 */
                $this->smarty->assign('config', $GLOBALS['_CFG']);

                /* 取得订单信息 */
                $order = flow_order_info();

                $vc_id = intval(request()->input('value_card', 0));
                $value_card = $this->valueCardService->orderValueCardInfo($vc_id, '', $user_id);

                if ((!empty($value_card) && isset($value_card['user_id']) == session('user_id'))) {
                    $order['vc_id'] = intval($vc_id);
                } else {
                    $order['vc_id'] = 0;
                }

                $cart_goods_number = $this->cartCommonService->getBuyCartGoodsNumber($flow_type, $cart_value);
                $this->smarty->assign('cart_goods_number', $cart_goods_number);

                if (!empty($consignee)) {
                    $consignee['province_name'] = get_goods_region_name($consignee['province']);
                    $consignee['city_name'] = get_goods_region_name($consignee['city']);
                    $consignee['district_name'] = get_goods_region_name($consignee['district']);
                    $consignee['street_name'] = get_goods_region_name($consignee['street']);
                    $consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
                }
                $this->smarty->assign('consignee', $consignee);

                $cart_goods_list = cart_goods($flow_type, $cart_value, 1, '', $store_id); // 取得商品列表，计算合计
                $this->smarty->assign('goods_list', $cart_goods_list);

                //切换配送方式 by kong
                $cart_goods_list = get_flowdone_goods_list($cart_goods_list, $tmp_shipping_id_arr);

                /* 计算订单的费用 */
                $total = order_fee($order, $cart_goods, $consignee, $cart_value, $cart_goods_list, $store_id, '', 0, 0, $flow_type);

                if (CROSS_BORDER === true) { // 跨境多商户
                    $web = app(CrossBorderService::class)->webExists();

                    if (!empty($web)) {
                        $arr = [
                            'consignee' => $consignee ?? '',
                            'rec_type' => $flow_type ?? 0,
                            'store_id' => $store_id ?? 0,
                            'cart_value' => $cart_value ?? '',
                            'type' => $type ?? 0,
                            'uc_id' => $order['uc_id'] ?? 0
                        ];
                        $amount = $web->assignNewRatePrice($cart_goods_list, $total['amount'], $arr);
                        $total['amount'] = $amount['amount'];
                        $total['amount_formated'] = $amount['amount_formated'];
                    } else {
                        return response()->json(['error' => 1, 'message' => 'service not exists']);
                    }
                }
                $this->smarty->assign('total', $total);

                /* 团购标志 */
                if ($flow_type == CART_GROUP_BUY_GOODS) {
                    $this->smarty->assign('is_group_buy', 1);
                    $list['is_group_deposit'] = 0;
                    $group_buy_id = session('extension_id', 0);
                    $group_buy = app(GroupBuyService::class)->getGroupBuyInfo(['group_buy_id' => $group_buy_id]);
                    if (isset($group_buy) && $group_buy['deposit'] > 0) {
                        $this->smarty->assign('is_group_deposit', 1);
                    }
                } elseif ($flow_type == CART_EXCHANGE_GOODS) {
                    // 积分兑换 qin
                    $this->smarty->assign('is_exchange_goods', 1);
                }

                $result['content'] = $this->smarty->fetch('library/order_total.lbi');
            }

            $result['vc_id'] = $order['vc_id'] ?? 0;

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 改变红包
        /*------------------------------------------------------ */
        elseif ($step == 'change_bonus') {
            $result = ['error' => '', 'content' => ''];
            /* 取得购物类型 */
            $flow_type = intval(session('flow_type', CART_GENERAL_GOODS));

            $shipping_id = strip_tags(urldecode(request()->input('shipping_id', '')));
            $tmp_shipping_id_arr = dsc_decode($shipping_id, true);

            $warehouse_id = (int)request()->input('warehouse_id', 0);
            $area_id = (int)request()->input('area_id', 0);

            $this->smarty->assign('warehouse_id', $warehouse_id);
            $this->smarty->assign('area_id', $area_id);

            /* 获得收货人信息 */
            $consignee = $this->flowUserService->getConsignee($user_id);

            $cart_goods_list = cart_goods($flow_type, $cart_value, 1); // 取得商品列表，计算合计
            $cart_goods = FlowRepository::getNewGroupCartGoods($cart_goods_list);

            if (empty($cart_goods) || !$this->flowUserService->checkConsigneeInfo($consignee, $flow_type)) {
                if (empty($cart_goods)) {
                    $result['error'] = 1;
                } else {
                    $result['error'] = 2;
                }
            } else {
                /* 取得购物流程设置 */
                $this->smarty->assign('config', $GLOBALS['_CFG']);

                /* 取得订单信息 */
                $order = flow_order_info();

                $order['user_id'] = $user_id;

                $bonus_id = intval(request()->input('bonus', 0));
                $bonus = bonus_info($bonus_id);

                if ((!empty($bonus) && $bonus['user_id'] == session('user_id')) || $bonus_id == 0) {
                    $order['bonus_id'] = $bonus_id;
                } else {
                    $order['bonus_id'] = 0;
                    $result['error'] = $GLOBALS['_LANG']['invalid_bonus'];
                }

                $order['bonus'] = $bonus['type_money'] ?? 0;
                $cart_goods = $this->flowOrderService->couponsBonusFlowShareEqually($cart_goods, $order, [], 2); //重新赋值

                $cart_goods_number = $this->cartCommonService->getBuyCartGoodsNumber($flow_type, $cart_value);
                $this->smarty->assign('cart_goods_number', $cart_goods_number);

                if (!empty($consignee)) {
                    $consignee['province_name'] = get_goods_region_name($consignee['province']);
                    $consignee['city_name'] = get_goods_region_name($consignee['city']);
                    $consignee['district_name'] = get_goods_region_name($consignee['district']);
                    $consignee['street_name'] = get_goods_region_name($consignee['street']);
                    $consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
                }
                $this->smarty->assign('consignee', $consignee);


                $this->smarty->assign('goods_list', $cart_goods_list);

                //切换配送方式 by kong
                $cart_goods_list = get_flowdone_goods_list($cart_goods_list, $tmp_shipping_id_arr);

                /* 计算订单的费用 */
                $total = order_fee($order, $cart_goods, $consignee, $cart_value, $cart_goods_list, 0, '', 0, 0, $flow_type);

                $result['dis_type'] = 'bonus';

                $result['check_type'] = 0;


                if (CROSS_BORDER === true) { // 跨境多商户
                    $web = app(CrossBorderService::class)->webExists();

                    if (!empty($web)) {
                        $arr = [
                            'consignee' => $consignee ?? '',
                            'rec_type' => $flow_type ?? 0,
                            'store_id' => $store_id ?? 0,
                            'cart_value' => $cart_value ?? '',
                            'type' => $type ?? 0,
                            'uc_id' => $order['uc_id'] ?? 0
                        ];
                        $amount = $web->assignNewRatePrice($cart_goods_list, $total['amount'], $arr);
                        $total['amount'] = $amount['amount'];
                        $total['amount_formated'] = $amount['amount_formated'];
                    } else {
                        return response()->json(['error' => 1, 'message' => 'service not exists']);
                    }
                }

                $this->smarty->assign('total', $total);

                /* 团购标志 */
                if ($flow_type == CART_GROUP_BUY_GOODS) {
                    $this->smarty->assign('is_group_buy', 1);
                } elseif ($flow_type == CART_EXCHANGE_GOODS) {
                    // 积分兑换 qin
                    $this->smarty->assign('is_exchange_goods', 1);
                }

                $result['content'] = $this->smarty->fetch('library/order_total.lbi');
            }

            $result['bonus_id'] = $order['bonus_id'] ?? 0;

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 改变积分
        /*------------------------------------------------------ */
        elseif ($step == 'change_integral') {
            $points = floatval(request()->input('points', 0));

            $user_info = Users::where('user_id', $user_id);
            $user_info = BaseRepository::getToArrayFirst($user_info);

            if (empty($user_info)) {
                return response()->json([]);
            }
            $shipping_id = strip_tags(urldecode(request()->input('shipping_id', '')));
            $tmp_shipping_id_arr = dsc_decode($shipping_id, true);

            /* 取得购物类型 */
            $flow_type = intval(session('flow_type', CART_GENERAL_GOODS));

            $store_id = (int)request()->input('store_id', 0);

            /* 获得收货人信息 */
            $consignee = $this->flowUserService->getConsignee(session('user_id'));

            /* 对商品信息赋值 */
            $cart_goods = cart_goods($flow_type, $cart_value, 0, $consignee, $store_id); // 取得商品列表，计算合计

            /* 取得订单信息 */
            $order = flow_order_info();
            $flow_points = $this->flowActivityService->getFlowAvailablePoints($cart_goods);  // 该订单允许使用的积分
            $user_points = $user_info['pay_points']; // 用户的积分总数

            $this->smarty->assign('warehouse_id', $warehouse_id);
            $this->smarty->assign('area_id', $area_id);
            $this->smarty->assign('store_id', $store_id);

            if ($points > $user_points) {
                $result['error'] = $GLOBALS['_LANG']['integral_not_enough'];
            } else {

                if ($points > $flow_points) {
                    $points = $flow_points;
                }

                $order['integral'] = $points;
                $order['is_integral'] = 1;

                if (empty($cart_goods)) {
                    $result['error'] = $GLOBALS['_LANG']['no_goods_in_cart'];
                } elseif (!$this->flowUserService->checkConsigneeInfo($consignee, $flow_type)) {
                    $result['error'] = $GLOBALS['_LANG']['no_consignee'];
                } else {
                    $cart_goods_number = $this->cartCommonService->getBuyCartGoodsNumber($flow_type, $cart_value);
                    $this->smarty->assign('cart_goods_number', $cart_goods_number);

                    $consignee['province_name'] = get_goods_region_name($consignee['province']);
                    $consignee['city_name'] = get_goods_region_name($consignee['city']);
                    $consignee['district_name'] = get_goods_region_name($consignee['district']);
                    $consignee['street_name'] = get_goods_region_name($consignee['street']);
                    $consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
                    $this->smarty->assign('consignee', $consignee);

                    $cart_goods_list = cart_goods($flow_type, $cart_value, 1, '', $store_id); // 取得商品列表，计算合计
                    $this->smarty->assign('goods_list', $cart_goods_list);

                    //切换配送方式 by kong
                    $cart_goods_list = get_flowdone_goods_list($cart_goods_list, $tmp_shipping_id_arr);

                    /* 计算订单的费用 */
                    $total = order_fee($order, $cart_goods, $consignee, $cart_value, $cart_goods_list, 0, '', 0, 0, $flow_type);
                    if (CROSS_BORDER === true) { // 跨境多商户
                        $web = app(CrossBorderService::class)->webExists();

                        if (!empty($web)) {
                            $arr = [
                                'consignee' => $consignee ?? '',
                                'rec_type' => $flow_type ?? 0,
                                'store_id' => $store_id ?? 0,
                                'cart_value' => $cart_value ?? '',
                                'type' => $type ?? 0,
                                'uc_id' => $order['uc_id'] ?? 0
                            ];
                            $amount = $web->assignNewRatePrice($cart_goods_list, $total['amount'], $arr);
                            $total['amount'] = $amount['amount'];
                            $total['amount_formated'] = $amount['amount_formated'];
                        } else {
                            return response()->json(['error' => 1, 'message' => 'service not exists']);
                        }
                    }
                    $this->smarty->assign('total', $total);

                    $this->smarty->assign('config', $GLOBALS['_CFG']);

                    /* 团购标志 */
                    if ($flow_type == CART_GROUP_BUY_GOODS) {
                        $this->smarty->assign('is_group_buy', 1);
                    } elseif ($flow_type == CART_EXCHANGE_GOODS) {
                        // 积分兑换 qin
                        $this->smarty->assign('is_exchange_goods', 1);
                    }

                    $result['content'] = $this->smarty->fetch('library/order_total.lbi');
                    $result['error'] = '';
                }
            }

            $result['integral'] = $order['integral'] ?? 0;

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 改变贺卡
        /*------------------------------------------------------ */
        elseif ($step == 'select_card') {
            $result = ['error' => '', 'content' => '', 'need_insure' => 0];

            $warehouse_id = (int)request()->input('warehouse_id', 0);
            $area_id = (int)request()->input('area_id', 0);

            $this->smarty->assign('warehouse_id', $warehouse_id);
            $this->smarty->assign('area_id', $area_id);

            /* 取得购物类型 */
            $flow_type = intval(session('flow_type', CART_GENERAL_GOODS));

            /* 获得收货人信息 */
            $consignee = $this->flowUserService->getConsignee($user_id);

            $cart_goods_list = cart_goods($flow_type, $cart_value, 1); // 取得商品列表，计算合计
            $cart_goods = FlowRepository::getNewGroupCartGoods($cart_goods_list);
            $this->smarty->assign('goods_list', $cart_goods_list);

            if (empty($cart_goods) || !$this->flowUserService->checkConsigneeInfo($consignee, $flow_type)) {
                $result['error'] = $GLOBALS['_LANG']['no_goods_in_cart'];
            } else {
                /* 取得购物流程设置 */
                $this->smarty->assign('config', $GLOBALS['_CFG']);

                /* 取得订单信息 */
                $order = flow_order_info();

                $order['card_id'] = intval(request()->input('card', 0));

                /* 保存 session */
                session([
                    'flow_order' => $order
                ]);

                $cart_goods_number = $this->cartCommonService->getBuyCartGoodsNumber($flow_type, $cart_value);
                $this->smarty->assign('cart_goods_number', $cart_goods_number);
                if (!empty($consignee)) {
                    $consignee['province_name'] = get_goods_region_name($consignee['province']);
                    $consignee['city_name'] = get_goods_region_name($consignee['city']);
                    $consignee['district_name'] = get_goods_region_name($consignee['district']);
                    $consignee['street_name'] = get_goods_region_name($consignee['street']);
                    $consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
                }
                $this->smarty->assign('consignee', $consignee);

                /* 计算订单的费用 */
                $total = order_fee($order, $cart_goods, $consignee, $cart_value, $cart_goods_list, 0, '', 0, 0, $flow_type);

                if (CROSS_BORDER === true) { // 跨境多商户
                    $web = app(CrossBorderService::class)->webExists();

                    if (!empty($web)) {
                        $arr = [
                            'consignee' => $consignee ?? '',
                            'rec_type' => $flow_type ?? 0,
                            'store_id' => $store_id ?? 0,
                            'cart_value' => $cart_value ?? '',
                            'type' => $type ?? 0,
                            'uc_id' => $order['uc_id'] ?? 0
                        ];
                        $amount = $web->assignNewRatePrice($cart_goods_list, $total['amount'], $arr);
                        $total['amount'] = $amount['amount'];
                        $total['amount_formated'] = $amount['amount_formated'];
                    } else {
                        return response()->json(['error' => 1, 'message' => 'service not exists']);
                    }
                }
                $this->smarty->assign('total', $total);

                /* 取得可以得到的积分和红包 */
                $cart_total = BaseRepository::getArraySum($cart_goods, ['goods_price', 'goods_number']);
                $this->smarty->assign('total_integral', $cart_total - $order['bonus'] - $total['integral_money']);

                $total_bonus = $this->flowActivityService->getTotalBonus();
                $this->smarty->assign('total_bonus', $this->dscRepository->getPriceFormat($total_bonus, false));

                /* 团购标志 */
                if ($flow_type == CART_GROUP_BUY_GOODS) {
                    $this->smarty->assign('is_group_buy', 1);
                } elseif ($flow_type == CART_EXCHANGE_GOODS) {
                    // 积分兑换 qin
                    $this->smarty->assign('is_exchange_goods', 1);
                }

                $result['content'] = $this->smarty->fetch('library/order_total.lbi');
            }

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 改变商品包装
        /*------------------------------------------------------ */
        elseif ($step == 'select_pack') {
            $result = ['error' => '', 'content' => '', 'need_insure' => 0];

            $warehouse_id = (int)request()->input('warehouse_id', 0);
            $area_id = (int)request()->input('area_id', 0);

            $this->smarty->assign('warehouse_id', $warehouse_id);
            $this->smarty->assign('area_id', $area_id);

            /* 取得购物类型 */
            $flow_type = intval(session('flow_type', CART_GENERAL_GOODS));

            /* 获得收货人信息 */
            $consignee = $this->flowUserService->getConsignee(session('user_id'));

            /* 对商品信息赋值 */
            $cart_goods_list = cart_goods($flow_type, $cart_value, 1); // 取得商品列表，计算合计
            $cart_goods = FlowRepository::getNewGroupCartGoods($cart_goods_list);
            $this->smarty->assign('goods_list', $cart_goods_list);

            if (empty($cart_goods) || !$this->flowUserService->checkConsigneeInfo($consignee, $flow_type)) {
                $result['error'] = $GLOBALS['_LANG']['no_goods_in_cart'];
            } else {
                /* 取得购物流程设置 */
                $this->smarty->assign('config', $GLOBALS['_CFG']);

                /* 取得订单信息 */
                $order = flow_order_info();

                $order['pack_id'] = intval(request()->input('pack', 0));

                /* 保存 session */
                session([
                    'flow_order' => $order
                ]);

                $cart_goods_number = $this->cartCommonService->getBuyCartGoodsNumber($flow_type, $cart_value);
                $this->smarty->assign('cart_goods_number', $cart_goods_number);

                if (!empty($consignee)) {
                    $consignee['province_name'] = get_goods_region_name($consignee['province']);
                    $consignee['city_name'] = get_goods_region_name($consignee['city']);
                    $consignee['district_name'] = get_goods_region_name($consignee['district']);
                    $consignee['street_name'] = get_goods_region_name($consignee['street']);
                    $consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['street_name'] . $consignee['address'];
                }
                $this->smarty->assign('consignee', $consignee);

                /* 计算订单的费用 */
                $total = order_fee($order, $cart_goods, $consignee, $cart_value, $cart_goods_list, 0, '', 0, 0, $flow_type);
                $this->smarty->assign('total', $total);

                if (CROSS_BORDER === true) { // 跨境多商户
                    $web = app(CrossBorderService::class)->webExists();

                    if (!empty($web)) {
                        $arr = [
                            'consignee' => $consignee ?? '',
                            'rec_type' => $flow_type ?? 0,
                            'store_id' => $store_id ?? 0,
                            'cart_value' => $cart_value ?? '',
                            'type' => $type ?? 0,
                            'uc_id' => $order['uc_id'] ?? 0
                        ];
                        $amount = $web->assignNewRatePrice($cart_goods_list, $total['amount'], $arr);
                        $total['amount'] = $amount['amount'];
                        $total['amount_formated'] = $amount['amount_formated'];
                    } else {
                        return response()->json(['error' => 1, 'message' => 'service not exists']);
                    }
                }
                /* 取得可以得到的积分和红包 */
                $cart_total = BaseRepository::getArraySum($cart_goods, ['goods_price', 'goods_number']);

                $this->smarty->assign('total_integral', $cart_total - $total['bonus'] - $total['integral_money']);

                $total_bonus = $this->flowActivityService->getTotalBonus();
                $this->smarty->assign('total_bonus', $this->dscRepository->getPriceFormat($total_bonus, false));

                /* 团购标志 */
                if ($flow_type == CART_GROUP_BUY_GOODS) {
                    $this->smarty->assign('is_group_buy', 1);
                } elseif ($flow_type == CART_EXCHANGE_GOODS) {
                    // 积分兑换 qin
                    $this->smarty->assign('is_exchange_goods', 1);
                }

                $result['content'] = $this->smarty->fetch('library/order_total.lbi');
            }

            return response()->json($result);
        }
    }
}
