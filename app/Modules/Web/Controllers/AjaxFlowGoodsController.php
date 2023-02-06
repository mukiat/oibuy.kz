<?php

namespace App\Modules\Web\Controllers;

use App\Models\Cart;
use App\Models\Goods;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Services\Cart\CartCommonService;
use App\Services\Cart\CarthandleService;
use App\Services\Cart\CartService;
use App\Services\Common\CommonService;
use App\Services\CrossBorder\CrossBorderService;
use App\Services\Goods\GoodsAttrService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Order\OrderGoodsService;
use App\Services\User\UserBaitiaoService;
use App\Services\User\UserCommonService;

class AjaxFlowGoodsController extends InitController
{
    protected $cartService;
    protected $sessionRepository;
    protected $dscRepository;
    protected $userCommonService;
    protected $goodsCommonService;
    protected $orderGoodsService;
    protected $userBaitiaoService;
    protected $cartCommonService;
    protected $carthandleService;
    protected $commonService;
    protected $goodsAttrService;

    public function __construct(
        CartService $cartService,
        SessionRepository $sessionRepository,
        DscRepository $dscRepository,
        UserCommonService $userCommonService,
        GoodsCommonService $goodsCommonService,
        OrderGoodsService $orderGoodsService,
        UserBaitiaoService $userBaitiaoService,
        CartCommonService $cartCommonService,
        CarthandleService $carthandleService,
        CommonService $commonService,
        GoodsAttrService $goodsAttrService
    )
    {
        $this->cartService = $cartService;
        $this->sessionRepository = $sessionRepository;
        $this->dscRepository = $dscRepository;
        $this->userCommonService = $userCommonService;
        $this->goodsCommonService = $goodsCommonService;
        $this->orderGoodsService = $orderGoodsService;
        $this->userBaitiaoService = $userBaitiaoService;
        $this->cartCommonService = $cartCommonService;
        $this->carthandleService = $carthandleService;
        $this->commonService = $commonService;
        $this->goodsAttrService = $goodsAttrService;
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

        $session_id = $this->sessionRepository->realCartMacIp();
        $user_id = session('user_id', 0);

        $this->smarty->assign('lang', $GLOBALS['_LANG']);

        $step = addslashes(trim(request()->input('step', '')));
        /*------------------------------------------------------ */
        //-- 异步获取商品总金额
        /*------------------------------------------------------ */
        if ($step == 'ajax_cart_goods_amount') {
            $result = ['error' => 0, 'message' => ''];

            get_request_filter();

            // 被选中的商家ID
            $ru_id = intval(request()->input('ru_id', 0));

            //字符串
            $rec_id = addslashes(request()->input('rec_id', ''));
            // 被选中的优惠活动商品
            $act_sel_id = addslashes(request()->input('sel_id', ''));
            // 标志flag
            $sel_flag = addslashes(request()->input('sel_flag', ''));
            $act_sel = ['act_sel_id' => $act_sel_id, 'act_sel' => $sel_flag, 'ru_id' => $ru_id];

            $result['ru_id'] = $ru_id;

            /* 更新选中状态 */
            $this->cartService->cartUpdateGoodsChecked($user_id, $rec_id);

            /* 计算折扣 */
            $discount = compute_discount(3, $rec_id);

            $flow_type = intval(session('flow_type', CART_GENERAL_GOODS));
            $cart_goods = cart_goods($flow_type, $rec_id); // 取得商品列表
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

            if (CROSS_BORDER === true) { // 跨境多商户
                $cbec = app(CrossBorderService::class)->cbecExists();

                if (!empty($cbec)) {
                    $arr = $cbec->get_total_rate2($cart_goods);
                    $result['cart_total_rate'] = $arr['cart_total_rate'];
                    $result['can_buy'] = $arr['can_buy'];
                    $goods_amount['subtotal_amount'] = str_replace('<em>¥</em>', '', $goods_amount['subtotal_amount'] + $arr['cart_total_rate']);
                } else {
                    return response()->json(['error' => 1, 'message' => 'service not exists']);
                }
            }

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

            $result['discount'] = $discount['discount'];

            $is_gift = 0;
            $act_id = intval(request()->input('favourable_id', 0));

            if ($act_id) {
                $where = [
                    'is_gift' => $act_id
                ];
                $is_gift = $this->cartService->getCartCount($where);

                //删除赠品
                $res = Cart::where('is_gift', $act_id);

                if (!empty($user_id)) {
                    $res = $res->where('user_id', $user_id);
                } else {
                    $res = $res->where('session_id', $session_id);
                }

                $res->delete();

                $user_rank = $this->userCommonService->getUserRankByUid($user_id);

                // 局部更新优惠活动
                $cart_fav_box = cart_favourable_box($act_id, $act_sel, $user_id, $user_rank['rank_id'], $warehouse_id, $area_id, $area_city);

                $this->smarty->assign('activity', $cart_fav_box);
                $result['favourable_box_content'] = $this->smarty->fetch("library/cart_favourable_box.lbi");
            }

            $result['act_id'] = $act_id;
            $result['is_gift'] = $is_gift;

            if (CROSS_BORDER === true) { // 跨境多商户
                $cbec = app(CrossBorderService::class)->cbecExists();
                if (!empty($cbec)) {
                    $result['can_buy'] = $cbec->check_kj_price2($rec_id, $warehouse_id, $area_id, $area_city);
                } else {
                    return response()->json(['error' => 1, 'message' => 'service not exists']);
                }
            }

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 更新购物车
        /*------------------------------------------------------ */
        elseif ($step == 'ajax_update_cart') {
            $result = $this->commonService->ajaxUpdateCart($user_id, $session_id, $warehouse_id, $area_id, $area_city);

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 添加商品到购物车
        /*------------------------------------------------------ */
        elseif ($step == 'add_to_cart_showDiv') {
            if (request()->exists('goods')) {
                $goods = strip_tags(urldecode(request()->input('goods', '')));
                $goods = json_str_iconv($goods);
            } else {
                return redirect("/");
            }
            $goods_id = intval(request()->input('goods_id', 0));
            if (!empty($goods_id) && empty($goods)) {
                if (!is_numeric($goods_id) || intval($goods_id) <= 0) {
                    return redirect("/");
                }
            }

            $result = ['error' => 0, 'message' => '', 'content' => '', 'goods_id' => '', 'goods_number' => '', 'subtotal' => '', 'script_name' => '', 'goods_recommend' => ''];

            if (empty($goods)) {
                $result['error'] = 1;
                return response()->json($result);
            }

            $goods = dsc_decode($goods);

            $goods->stages_qishu = isset($goods->stages_qishu) && !empty($goods->stages_qishu) ? intval($goods->stages_qishu) : -1;

            //@author-bylu 检测当前用户白条相关权限(是否逾期,逾期不能下单);
            $bt_status = $this->userBaitiaoService->btAuthCheck($goods->stages_qishu);

            switch ($bt_status) {
                case 1:
                    $result['error'] = 1;
                    $result['message'] = $GLOBALS['_LANG']['bt_noll_impower'];
                    return response()->json($result);
                    break;

                case 2:
                    $result['error'] = 1;
                    $result['message'] = $GLOBALS['_LANG']['bt_noll_balance'];
                    return response()->json($result);
                    break;

                case 3:
                    $result['error'] = 1;
                    $result['message'] = $GLOBALS['_LANG']['bt_forbid_pay'];
                    return response()->json($result);
                    break;

                case 4:
                    $result['error'] = 1;
                    $result['message'] = $GLOBALS['_LANG']['bt_forbid_pay'];
                    return response()->json($result);
                    break;

                case 5:
                    $result['error'] = 1;
                    $result['message'] = $GLOBALS['_LANG']['bt_overdue'];
                    return response()->json($result);
                    break;

                case 6:
                    $result['error'] = 1;
                    $result['message'] = $GLOBALS['_LANG']['bt_overdue'];
                    return response()->json($result);
                    break;
            }

            if (!empty($goods->script_name)) {
                $result['script_name'] = $goods->script_name;
            } else {
                $result['script_name'] = 0;
            }

            if (!empty($goods->goods_recommend)) {
                $result['goods_recommend'] = $goods->goods_recommend;
            } else {
                $result['goods_recommend'] = '';
            }

            $warehouse_id = isset($goods->warehouse_id) ? intval($goods->warehouse_id) : 0;
            $area_id = isset($goods->area_id) ? intval($goods->area_id) : 0;
            /* 检查：如果商品有规格，而post的数据没有规格，把商品的规格属性通过JSON传到前台 */
            if (empty($goods->spec) && empty($goods->quick)) {

                $properties = $this->goodsAttrService->getGoodsProperties($goods->goods_id, $warehouse_id, $area_id, $area_city);
                $spe_array = $properties['spe'];

                if (!empty($spe_array)) {
                    $result['error'] = ERR_NEED_SELECT_ATTR;
                    $result['goods_id'] = $goods->goods_id;
                    $result['parent'] = $goods->parent;
                    $result['message'] = $spe_array;
                    if (!empty($goods->script_name)) {
                        $result['script_name'] = $goods->script_name;
                    } else {
                        $result['script_name'] = 0;
                    }
                    return response()->json($result);
                }
            }

            /* 更新：如果是一步购物，先清空购物车 */
            if (session('one_step_buy') == '1') {
                $this->cartCommonService->clearCart();
            }

            /* 检查：商品数量是否合法 */
            if (!is_numeric($goods->number) || intval($goods->number) <= 0) {
                $result['error'] = 1;
                $result['message'] = $GLOBALS['_LANG']['invalid_number'];
            } /* 更新：购物车 */
            else {
                //ecmoban模板堂 --zhuo start 限购
                $nowTime = gmtime();
                $xiangouInfo = $this->goodsCommonService->getPurchasingGoodsInfo($goods->goods_id);

                $start_date = $xiangouInfo['xiangou_start_date'];
                $end_date = $xiangouInfo['xiangou_end_date'];

                if ($xiangouInfo['is_xiangou'] == 1 && $nowTime >= $start_date && $nowTime < $end_date) {
                    $cart_number = Cart::where('goods_id', $goods->goods_id)->where('rec_type', '<>', CART_ONESTEP_GOODS);

                    if (!empty($user_id)) {
                        $cart_number = $cart_number->where('user_id', $user_id);
                    } else {
                        $cart_number = $cart_number->where('session_id', $session_id);
                    }

                    $cart_number = $cart_number->sum('goods_number');

                    $extension_code = $xiangouInfo['is_real'] == 0 ? 'virtual_card' : '';
                    $orderGoods = $this->orderGoodsService->getForPurchasingGoods($start_date, $end_date, $goods->goods_id, $user_id, $extension_code);
                    if ($orderGoods['goods_number'] >= $xiangouInfo['xiangou_num']) {
                        $result['error'] = 1;
                        $result['message'] = sprintf($GLOBALS['_LANG']['purchasing_prompt'], get_table_date('goods', "goods_id='" . $goods->goods_id . "'", ['goods_name'], 2));
                        $result['show_info'] = '';
                        return response()->json($result);
                    } else {
                        if ($xiangouInfo['xiangou_num'] > 0) {
                            if ($cart_number + $orderGoods['goods_number'] + $goods->number > $xiangouInfo['xiangou_num']) {
                                $result['error'] = 1;
                                $result['message'] = $GLOBALS['_LANG']['purchasing_prompt_two'];
                                $result['show_info'] = '';
                                return response()->json($result);
                            }
                        }
                    }
                }
                //ecmoban模板堂 --zhuo end 限购

                // 最小起订量
                $goods_info = Goods::select('goods_id', 'user_id', 'is_minimum', 'minimum', 'minimum_start_date', 'minimum_end_date', 'goods_name', 'cat_id', 'brand_id')
                    ->where('goods_id', $goods->goods_id);
                $goods_info = BaseRepository::getToArrayFirst($goods_info);

                $drpUserAudit = cache('drp_user_audit_' . $user_id) ?? 0;

                $drp_show_price = config('shop.drp_show_price') ?? 0;
                if (empty($drpUserAudit) && $goods_info['user_id'] > 0 && $drp_show_price == 1) {
                    $result['error'] = 1;
                    $result['message'] = lang('cart.qualification_buy');
                    return response()->json($result);
                }

                if ($goods_info['is_minimum'] == 1 && $nowTime > $goods_info['minimum_start_date'] && $nowTime < $goods_info['minimum_end_date']) {
                    if ($goods_info['minimum'] > $goods->number) {
                        $result['error'] = 1;
                        $result['message'] = sprintf($GLOBALS['_LANG']['purchasing_minimum'], $goods_info['goods_name']);
                        $result['show_info'] = '';
                        return response()->json($result);
                    }
                }

                $favourable_info = get_favourable_info($goods->goods_id, $goods_info['user_id'], $goods_info);
                $favourable_info = $favourable_info ? array_values($favourable_info) : [];
                $act_id = $favourable_info[0]['act_id'] ?? 0;

                $add_res = $this->carthandleService->addtoCart($goods->goods_id, $goods->number, $goods->spec, $goods->parent, $warehouse_id, $area_id, $area_city, $goods->stages_qishu, 0, '', '', $act_id);

                // 更新：添加到购物车
                if ($add_res['error'] == 0) {
                    if (config('shop.cart_confirm') > 2) {
                        $result['message'] = '';
                    } else {
                        $result['message'] = config('shop.cart_confirm') == 1 ? $GLOBALS['_LANG']['addto_cart_success_1'] : $GLOBALS['_LANG']['addto_cart_success_2'];
                    }

                    $result['content'] = insert_cart_info(4);
                    $result['one_step_buy'] = session('one_step_buy', 0);
                } else {
                    $result['message'] = $add_res['message'];
                    $result['error'] = $add_res['error'];
                    $result['goods_id'] = stripslashes($goods->goods_id);

                    if (is_array($goods->spec)) {
                        $result['product_spec'] = implode(',', $goods->spec);
                    } else {
                        $result['product_spec'] = $goods->spec;
                    }
                }
            }

            $result['confirm_type'] = !empty(config('shop.cart_confirm')) ? config('shop.cart_confirm') : 2;

            $result['goods_id'] = $goods->goods_id;

            /* 计算合计 */
            $cart_goods = get_cart_goods();

            $result['goods_number'] = 0;
            if ($cart_goods['goods_list']) {
                foreach ($cart_goods['goods_list'] as $val) {
                    $result['goods_number'] += $val['goods_number'];
                }
            }

            $result['show_info'] = insert_show_div_info($result['goods_number'], $result['script_name'], $result['goods_id'], $result['goods_recommend'], $cart_goods['total']['goods_amount'], $cart_goods['total']['real_goods_count']);

            $result['cart_num'] = $result['goods_number'];
            $cart_info = ['goods_list' => $cart_goods['goods_list'], 'number' => $result['goods_number'], 'amount' => $cart_goods['total']['goods_amount']];
            $this->smarty->assign('cart_info', $cart_info);

            $this->smarty->assign('goods', []);
            $result['cart_content'] = $this->smarty->fetch('library/cart_menu_info.lbi');

            /*  @author-bylu 如果是点击"分期购"进来的就获取到分期购商品在购物车中的ID start */
            if (!empty($goods->stages_qishu)) {

                //判断 有无商品属性传入,如果有商品属性就将商品属性加入条件;
                if (!empty($goods->spec)) {
                    $goods_attr_ids = !is_array($goods->spec) ? explode(",", $goods->spec) : $goods->spec;
                } else {
                    $goods_attr_ids = '';
                }

                $cart_value = Cart::where('goods_id', $goods->goods_id)
                    ->where('user_id', $user_id);

                if ($goods_attr_ids) {
                    $cart_value = $cart_value->where('goods_attr_id', $goods_attr_ids);
                }

                if (isset($goods->store_id) && $goods->store_id > 0) {
                    $cart_value = $cart_value->where('store_id', $goods->store_id);

                    $result['store_id'] = $goods->store_id;
                }

                $result['cart_value'] = $cart_value;
            }
            /*  @author-bylu  end */

            return response()->json($result);
        }
    }
}
