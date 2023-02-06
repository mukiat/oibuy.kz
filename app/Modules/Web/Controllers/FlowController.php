<?php

namespace App\Modules\Web\Controllers;

use App\Events\PickupOrdersPayedAfterDoSomething;
use App\Exceptions\HttpException;
use App\Jobs\ProcessSeparateBuyOrder;
use App\Libraries\CaptchaVerify;
use App\Models\AutoSms;
use App\Models\BaitiaoLog;
use App\Models\BaitiaoPayLog;
use App\Models\Cart;
use App\Models\CollectGoods;
use App\Models\Goods;
use App\Models\GoodsActivity;
use App\Models\OfflineStore;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\PayLog;
use App\Models\Payment;
use App\Models\Region;
use App\Models\SellerShopinfo;
use App\Models\SolveDealconcurrent;
use App\Models\Stages;
use App\Models\StoreOrder;
use App\Models\UserBonus;
use App\Models\UserOrderNum;
use App\Models\Users;
use App\Models\ValueCard;
use App\Repositories\Cart\CartRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscEncryptRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Flow\FlowRepository;
use App\Services\Activity\BonusService;
use App\Services\Activity\CouponsService;
use App\Services\Activity\GroupBuyService;
use App\Services\Activity\ValueCardService;
use App\Services\Article\ArticleCommonService;
use App\Services\Article\ArticleService;
use App\Services\Cart\CartCommonService;
use App\Services\Cart\CarthandleService;
use App\Services\Cart\CartService;
use App\Services\Cgroup\CgroupService;
use App\Services\Common\AreaService;
use App\Services\Coupon\CouponsUserService;
use App\Services\Cron\CronService;
use App\Services\CrossBorder\CrossBorderService;
use App\Services\Erp\JigonManageService;
use App\Services\Flow\FlowActivityService;
use App\Services\Flow\FlowOrderService;
use App\Services\Flow\FlowService;
use App\Services\Flow\FlowUserService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Goods\GoodsGuessService;
use App\Services\Goods\GoodsService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Order\OrderGoodsService;
use App\Services\Order\OrderService;
use App\Services\Payment\PaymentService;
use App\Services\Region\RegionService;
use App\Services\Store\StoreCommonService;
use App\Services\User\UserAddressService;
use App\Services\User\UserBaitiaoService;
use App\Services\User\UserCommonService;

/**
 * DSC 购物流程
 */
class FlowController extends InitController
{
    protected $areaService;
    protected $bonusService;
    protected $couponsService;
    protected $groupBuyService;
    protected $valueCardService;
    protected $cartService;
    protected $flowService;
    protected $goodsService;
    protected $orderService;
    protected $paymentService;
    protected $commonRepository;
    protected $dscRepository;
    protected $jigonManageService;
    protected $goodsCommonService;
    protected $merchantCommonService;
    protected $userBaitiaoService;
    protected $sessionRepository;
    protected $articleCommonService;
    protected $articleService;
    protected $cronService;
    protected $userCommonService;
    protected $userAddressService;
    protected $orderGoodsService;
    protected $cartCommonService;
    protected $carthandleService;
    protected $flowUserService;
    protected $cartRepository;
    protected $storeCommonService;
    protected $flowActivityService;
    protected $goodsGuessService;
    protected $flowOrderService;
    protected $couponsUserService;
    protected $regionService;

    public function __construct(
        AreaService $areaService,
        BonusService $bonusService,
        CouponsService $couponsService,
        GroupBuyService $groupBuyService,
        ValueCardService $valueCardService,
        CartService $cartService,
        FlowService $flowService,
        GoodsService $goodsService,
        OrderService $orderService,
        PaymentService $paymentService,
        CommonRepository $commonRepository,
        DscRepository $dscRepository,
        JigonManageService $jigonManageService,
        GoodsCommonService $goodsCommonService,
        MerchantCommonService $merchantCommonService,
        UserBaitiaoService $userBaitiaoService,
        SessionRepository $sessionRepository,
        ArticleCommonService $articleCommonService,
        ArticleService $articleService,
        CronService $cronService,
        UserCommonService $userCommonService,
        UserAddressService $userAddressService,
        OrderGoodsService $orderGoodsService,
        CartCommonService $cartCommonService,
        CarthandleService $carthandleService,
        FlowUserService $flowUserService,
        CartRepository $cartRepository,
        StoreCommonService $storeCommonService,
        FlowActivityService $flowActivityService,
        GoodsGuessService $goodsGuessService,
        FlowOrderService $flowOrderService,
        CouponsUserService $couponsUserService,
        RegionService $regionService
    )
    {
        $this->areaService = $areaService;
        $this->bonusService = $bonusService;
        $this->couponsService = $couponsService;
        $this->groupBuyService = $groupBuyService;
        $this->valueCardService = $valueCardService;
        $this->cartService = $cartService;
        $this->flowService = $flowService;
        $this->goodsService = $goodsService;
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
        $this->commonRepository = $commonRepository;
        $this->dscRepository = $dscRepository;
        $this->jigonManageService = $jigonManageService;
        $this->goodsCommonService = $goodsCommonService;
        $this->merchantCommonService = $merchantCommonService;
        $this->userBaitiaoService = $userBaitiaoService;
        $this->sessionRepository = $sessionRepository;
        $this->articleCommonService = $articleCommonService;
        $this->articleService = $articleService;
        $this->cronService = $cronService;
        $this->userCommonService = $userCommonService;
        $this->userAddressService = $userAddressService;
        $this->orderGoodsService = $orderGoodsService;
        $this->cartCommonService = $cartCommonService;
        $this->carthandleService = $carthandleService;
        $this->flowUserService = $flowUserService;
        $this->cartRepository = $cartRepository;
        $this->storeCommonService = $storeCommonService;
        $this->flowActivityService = $flowActivityService;
        $this->goodsGuessService = $goodsGuessService;
        $this->flowOrderService = $flowOrderService;
        $this->couponsUserService = $couponsUserService;
        $this->regionService = $regionService;
    }

    public function index()
    {
        load_helper('order');

        /* 载入语言文件 */
        $this->dscRepository->helpersLang(['user', 'shopping_flow']);

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

        $this->smarty->assign('flow_region', $flow_region);

        $this->smarty->assign('keywords', htmlspecialchars(config('shop.shop_keywords')));
        $this->smarty->assign('description', htmlspecialchars(config('shop.shop_desc')));
        if (CROSS_BORDER === true) { // 跨境多商户
            $web = app(CrossBorderService::class)->webExists();

            if (!empty($web)) {
                $web->smartyAssign();
            }
        }

        $user_id = session('user_id', 0);
        $user_name = session('user_name', '');

        $cart_value = $this->cartCommonService->getCartValue();

        /* 跳转H5 start */
        $Loaction = dsc_url('/#/cart');
        $uachar = $this->dscRepository->getReturnMobile($Loaction);

        if ($uachar) {
            return $uachar;
        }
        /* 跳转H5 end */
        $step = addslashes(request()->input('step', 'cart'));
        $session_id = $this->sessionRepository->realCartMacIp();

        assign_template();
        $position = assign_ur_here(0, $GLOBALS['_LANG']['shopping_flow']);
        $this->smarty->assign('page_title', $position['title']);    // 页面标题
        $this->smarty->assign('ur_here', $position['ur_here']);  // 当前位置

        $helps = $this->articleCommonService->getShopHelp();
        $this->smarty->assign('helps', $helps);       // 网店帮助

        $this->smarty->assign('lang', $GLOBALS['_LANG']);

        /* ------------------------------------------------------ */
        //-- 商品购买
        /* ------------------------------------------------------ */
        if ($step == 'link_buy') {
            $goods_id = (int)request()->input('goods_id');

            if (!cart_goods_exists($goods_id, [])) {
                $this->carthandleService->addtoCart($goods_id, 1, [], 0, $warehouse_id, $area_id, $area_city);
            }

            return dsc_header("Location:./flow.php\n");
        }

        /* ------------------------------------------------------ */
        //-- 登录
        /* ------------------------------------------------------ */
        elseif ($step == 'login') {

            //第三方登录判断
            if ($user_id > 0) {
                return dsc_header("Location:./flow.php?step=consignee\n");
            }

            /*
             * 用户登录注册
             */
            if (request()->server('REQUEST_METHOD') == 'GET') {
                $this->smarty->assign('anonymous_buy', config('shop.anonymous_buy'));

                /* 检查是否有赠品，如果有提示登录后重新选择赠品 */
                $count = Cart::where('is_gift', '>', 0);

                if (!empty($user_id)) {
                    $count = $count->where('user_id', $user_id);
                } else {
                    $count = $count->where('session_id', $session_id);
                }

                $count = $count->count();

                if ($count > 0) {
                    $this->smarty->assign('need_rechoose_gift', 1);
                }

                /* 检查是否需要注册码 */
                $captcha = intval(config('shop.captcha'));
                if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && session('login_fail') > 2)) && gd_version() > 0) {
                    $this->smarty->assign('enabled_login_captcha', 1);
                    $this->smarty->assign('rand', mt_rand());
                }
                if ($captcha & CAPTCHA_REGISTER) {
                    $this->smarty->assign('enabled_register_captcha', 1);
                    $this->smarty->assign('rand', mt_rand());
                }
            } else {
                $act = addslashes(request()->input('act', ''));
                $post_captcha = addslashes(request()->input('captcha', ''));
                $username = addslashes(request()->input('username', ''));
                $password = addslashes(request()->input('password', ''));

                load_helper('passport');
                if ($act == 'signin') {
                    $captcha = intval(config('shop.captcha'));
                    if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && session('login_fail') > 2)) && gd_version() > 0) {
                        if (empty($post_captcha)) {
                            return show_message($GLOBALS['_LANG']['invalid_captcha']);
                        }

                        /* 检查验证码 */
                        $verify = app(CaptchaVerify::class);
                        $seKey = 'captcha_login';
                        if (!$verify->check($post_captcha, $seKey)) {
                            return show_message($GLOBALS['_LANG']['invalid_captcha']);
                        }
                    }

                    if ($GLOBALS['user']->login($username, $password, request()->exists('remember'))) {
                        $this->userCommonService->updateUserInfo();  //更新用户信息
                        $this->cartCommonService->recalculatePriceCart(); // 重新计算购物车中的商品价格

                        /* 检查购物车中是否有商品 没有商品则跳转到首页 */
                        if (!empty($user_id)) {
                            $count = Cart::where('user_id', $user_id);
                        } else {
                            $count = Cart::where('session_id', $session_id);
                        }

                        $count = $count->count();

                        if ($count > 0) {
                            return dsc_header("Location: flow.php\n");
                        } else {
                            return redirect('/');
                        }
                    } else {
                        session()->increment('login_fail');
                        return show_message($GLOBALS['_LANG']['signin_failed'], '', 'user.php');
                    }
                } elseif ($act == 'signup') {
                    $email = addslashes(trim(request()->input('email', '')));
                    if ((intval(config('shop.captcha')) & CAPTCHA_REGISTER) && gd_version() > 0) {
                        if (empty($post_captcha)) {
                            return show_message($GLOBALS['_LANG']['invalid_captcha']);
                        }

                        /* 检查验证码 */
                        $verify = app(CaptchaVerify::class);

                        if (!$verify->check($post_captcha)) {
                            return show_message($GLOBALS['_LANG']['invalid_captcha']);
                        }
                    }

                    if (register(trim($username), trim($password), $email)) {
                        /* 用户注册成功 */
                        return dsc_header("Location: flow.php?step=consignee\n");
                    } else {
                        $this->err->show();
                    }
                } else {
                    // TODO: 非法访问的处理
                }
            }
        }

        /* ------------------------------------------------------ */
        //-- 收货人信息
        /* ------------------------------------------------------ */
        elseif ($step == 'consignee') {
            load_helper('transaction');

            if (request()->server('REQUEST_METHOD') == 'GET') {

                /*
                 * 收货人信息填写界面
                 */

                if (request()->exists('direct_shopping')) {
                    session([
                        'direct_shopping' => 1
                    ]);
                }

                /* 取得购物类型 */
                $flow_type = intval(session('flow_type', CART_GENERAL_GOODS));

                /* 取得国家列表、商店所在国家、商店所在国家的省列表 */
                $this->smarty->assign('country_list', get_regions());
                $this->smarty->assign('shop_country', config('shop.shop_country'));
                $this->smarty->assign('shop_province_list', get_regions(1, config('shop.shop_country')));

                /* 获得用户所有的收货人信息 */
                if (session('user_id') > 0) {
                    $consignee_list = $this->userAddressService->getUserAddressList($user_id);
                } else {
                    if (session()->has('flow_consignee')) {
                        $consignee_list = [session('flow_consignee')];
                    } else {
                        $consignee_list[] = [
                            'country' => config('shop.shop_country'),
                            'province' => $this->province_id,
                            'city' => $this->city_id,
                            'district' => $this->district_id
                        ];
                    }
                }
                $this->smarty->assign('name_of_region', [config('shop.name_of_region_1'), config('shop.name_of_region_2'), config('shop.name_of_region_3'), config('shop.name_of_region_4')]);
                $this->smarty->assign('consignee_list', $consignee_list);

                /* 取得每个收货地址的省市区列表 */
                $province_list = [];
                $city_list = [];
                $district_list = [];
                foreach ($consignee_list as $key => $consignee) {
                    $consignee['country'] = isset($consignee['country']) ? intval($consignee['country']) : 1;
                    $consignee['province'] = isset($consignee['province']) ? intval($consignee['province']) : $this->province_id;
                    $consignee['city'] = isset($consignee['city']) ? intval($consignee['city']) : $this->city_id;

                    $province_list[$key] = get_regions(1, $consignee['country']);
                    $city_list[$key] = get_regions(2, $consignee['province']);
                    $district_list[$key] = get_regions(3, $consignee['city']);
                }
                $this->smarty->assign('province_list', $province_list);
                $this->smarty->assign('city_list', $city_list);
                $this->smarty->assign('district_list', $district_list);

                /* 返回收货人页面代码 */
                $this->smarty->assign('real_goods_count', $this->flowUserService->existRealGoods(0, $flow_type) ? 1 : 0);
            } else {
                /*
                 * 保存收货人信息
                 */
                $consignee = [
                    'address_id' => (int)request()->input('address_id', 0),
                    'consignee' => compile_str(request()->input('consignee', '')),
                    'country' => (int)request()->input('country', 0),
                    'province' => (int)request()->input('province', 0),
                    'city' => (int)request()->input('city', 0),
                    'district' => (int)request()->input('district', 0),
                    'street' => (int)request()->input('street', 0),
                    'email' => compile_str(request()->input('email', '')),
                    'address' => compile_str(request()->input('address', '')),
                    'zipcode' => compile_str(make_semiangle(trim(request()->input('zipcode', '')))),
                    'tel' => compile_str(make_semiangle(trim(request()->input('tel', '')))),
                    'mobile' => compile_str(make_semiangle(trim(request()->input('mobile', '')))),
                    'sign_building' => compile_str(request()->input('sign_building', '')),
                    'best_time' => compile_str(request()->input('best_time', '')),
                ];

                if ($user_id > 0) {
                    /* 如果用户已经登录，则保存收货人信息 */
                    $consignee['user_id'] = $user_id;
                    $this->userAddressService->saveConsignee($consignee);
                }

                /* 保存到session */
                session([
                    'flow_consignee' => stripslashes_deep($consignee)
                ]);

                return dsc_header("Location: flow.php?step=checkout&direct_shopping=1\n");
            }
        }

        /* ------------------------------------------------------ */
        //-- 删除收货人信息
        /* ------------------------------------------------------ */
        elseif ($step == 'drop_consignee') {
            $consignee_id = (int)request()->input('id', 0);

            if ($this->userAddressService->dropConsignee($consignee_id, $user_id)) {
                return dsc_header("Location: flow.php?step=consignee\n");
            } else {
                return show_message($GLOBALS['_LANG']['not_fount_consignee']);
            }
        }

        /* ------------------------------------------------------ */
        //-- 确认提交订单页面
        /* ------------------------------------------------------ */
        elseif ($step == 'checkout') {

            /**
             * 初始化红包、优惠券、储值卡
             */
            session()->forget('flow_order.bonus_id');
            session()->forget('flow_order.uc_id');
            session()->forget('flow_order.vc_id');

            //@author-bylu 检测当前用户白条相关权限(是否逾期,逾期不能下单);
            //这里主要是为了防止用户在逾期前购物车中已存在商品,之后逾期通过购物车"结算"入口下单;
            $bt_status = $this->userBaitiaoService->btAuthCheck();

            switch ($bt_status) {
                case 1:
                    return show_message($GLOBALS['_LANG']['bt_noll_impower'], $GLOBALS['_LANG']['bt_go_refund'], 'user_baitiao.php?act=baitiao');
                    break;

                case 2:
                    return show_message($GLOBALS['_LANG']['bt_noll_balance'], $GLOBALS['_LANG']['bt_go_refund'], 'user_baitiao.php?act=baitiao');
                    break;

                case 3:
                    return show_message($GLOBALS['_LANG']['bt_forbid_pay'], $GLOBALS['_LANG']['bt_go_refund'], 'user_baitiao.php?act=baitiao');
                    break;

                case 4:
                    return show_message($GLOBALS['_LANG']['bt_forbid_pay'], $GLOBALS['_LANG']['bt_go_refund'], 'user_baitiao.php?act=baitiao');
                    break;

                case 5:
                    return show_message($GLOBALS['_LANG']['bt_overdue'], $GLOBALS['_LANG']['bt_go_refund'], 'user_baitiao.php?act=baitiao');
                    break;

                case 6:
                    return show_message($GLOBALS['_LANG']['bt_overdue'], $GLOBALS['_LANG']['bt_go_refund'], 'user_baitiao.php?act=baitiao');
                    break;
            }

            /* 取得购物类型 */
            $flow_type = session('one_step_buy') == 1 ? CART_ONESTEP_GOODS : intval(session('flow_type', CART_GENERAL_GOODS));

            //配送方式--自提点标识
            session([
                'merchants_shipping' => []
            ]);

            //ecmoban模板堂 --zhuo
            $direct_shopping = (int)request()->input('direct_shopping', 0);
            // by kong 20160721 门店标识
            $store_seller = addslashes(request()->input('store_seller', ''));
            // by kong 20160721 门店id
            $store_id = (int)request()->input('store_id', 0);

            //判断是否为门店自提订单，过滤非门店商品 start
            $store_order = request()->input('store_order', 0);
            $store_order = !empty($store_order) ? 1 : 0;

            if ($store_order) {
                $store_ids_set = [];

                if (request()->exists('cart_shipping_prompt') || request()->exists('goods_stock_exhausted')) {
                    $cart_value = addslashes(request()->input('cart_value', ''));
                    $cart_value = DscEncryptRepository::filterValInt($cart_value);
                    $cart_value_array = BaseRepository::getExplode($cart_value);
                } else {
                    $cart_value_array = $this->cartCommonService->getCartValue(1, $user_id);
                }

                $cart_store_goods = Cart::select('rec_id', 'goods_id')->whereIn('rec_id', $cart_value_array);
                $cart_store_goods = BaseRepository::getToArrayGet($cart_store_goods);
                $cat_goods_id = BaseRepository::getKeyPluck($cart_store_goods, 'goods_id');

                $cart_value_array = BaseRepository::getColumn($cart_store_goods, 'rec_id', 'goods_id');

                $judgeStoreGoods = $this->storeCommonService->judgeStoreGoods($cat_goods_id);

                $store_goods_value = [];
                $store_products_value = [];
                foreach ($cart_value_array as $key => $val) {
                    $judgeGoods = $judgeStoreGoods['goods'] ?? [];

                    if ($judgeGoods) {
                        foreach ($judgeGoods as $k => $v) {
                            $store_goods_value[$key] = $cart_value_array[$k] ?? 0;

                            if (isset($judgeGoods[$key])) {
                                $store_ids_set[] = $judgeGoods[$key];
                            }
                        }
                    }

                    $judgeProducts = $judgeStoreGoods['products'] ?? [];

                    if ($judgeProducts) {
                        foreach ($judgeProducts as $k => $v) {
                            $store_products_value[$key] = $cart_value_array[$k] ?? 0;

                            if (isset($judgeProducts[$key])) {
                                $store_ids_set[] = $judgeProducts[$key];
                            }
                        }
                    }
                }

                $store_goods_value = $store_goods_value ? array_unique($store_goods_value) : [];
                $store_products_value = $store_products_value ? array_unique($store_products_value) : [];

                $cart_value_array = BaseRepository::getArrayMerge($store_goods_value, $store_products_value);
                $store_ids_set = BaseRepository::getFlatten($store_ids_set);
                $store_ids_set = $store_ids_set ? array_unique($store_ids_set) : [];

                $cart_value = implode(',', $cart_value_array);

                //取一个默认store_id，并更新购物车(1.4.2修改自提逻辑 rec_type = CART_OFFLINE_GOODS)
                if (!empty($store_ids_set)) {
                    $store_id = reset($store_ids_set);
                    $rec_id = BaseRepository::getExplode($cart_value);

                    //1.4.2修改自提逻辑 rec_type = CART_OFFLINE_GOODS
                    //清空门店购物车
                    $this->cartCommonService->clearStoreGoods($user_id);
                    $rec_id_store = $this->cartCommonService->copyCartToOfflineStore($rec_id, $user_id, $store_id);
                    $cart_value = implode(',', $rec_id_store);
                } else {
                    return show_message($GLOBALS['_LANG']['have_no_store_goods'], $GLOBALS['_LANG']['back'], '', 'error');
                }

                $this->cartRepository->pushCartValue($cart_value);
                $flow_type = CART_OFFLINE_GOODS;//门店自提
                session([
                    'flow_type' => $flow_type
                ]);
            } else {
                if (request()->exists('cart_shipping_prompt') || request()->exists('goods_stock_exhausted')) {
                    $cart_value = request()->input('cart_value', '');
                    $cart_value = DscEncryptRepository::filterValInt($cart_value);
                    $cart_value = BaseRepository::getExplode($cart_value);
                } else {
                    $cart_value = request()->exists('cart_value') ? request()->get('cart_value', '') : '';
                    $cart_value = DscEncryptRepository::filterValInt($cart_value);
                    $cart_value = BaseRepository::getExplode($cart_value);

                    if (empty($cart_value)) {
                        $is_select = 0;
                        /**
                         * 超值礼包
                         */
                        if ($flow_type == CART_PACKAGE_GOODS) {
                            $is_select = 1;
                        }

                        $cart_value = $this->cartCommonService->getCartValue($is_select, $user_id, $flow_type);
                    }
                }
            }
            //判断是否为门店自提订单，过滤非门店商品 end

            if ($cart_value && is_array($cart_value)) {
                $cart_value = BaseRepository::getImplode($cart_value);
            }

            /* 重新存入session */
            $this->cartRepository->pushCartValue($cart_value);

            $this->smarty->assign('cart_value', $cart_value);

            /* 跳转购物车页面 */
            if (empty($cart_value)) {
                session([
                    'flow_type' => CART_GENERAL_GOODS
                ]);

                return redirect()->route('flow');
            }

            /*------------------------------------------------------ */
            //-- 订单确认
            /*------------------------------------------------------ */

            /* 团购标志 */
            $deposit = 0;
            if ($flow_type == CART_GROUP_BUY_GOODS) {
                $this->smarty->assign('is_group_buy', 1);
                // 团购支付保证金标识
                $list['is_group_deposit'] = 0;
                $group_buy_id = session('extension_id', 0);

                $group_buy = $this->groupBuyService->groupBuy($group_buy_id);
                $deposit = $group_buy['deposit'] ?? 0;

                if ($deposit > 0) {
                    $this->smarty->assign('is_group_deposit', 1);
                }

                $group_count = GoodsActivity::where('act_type', GAT_GROUP_BUY)
                    ->where('act_id', $group_buy_id)
                    ->count('act_id');

                if (empty($group_count)) {
                    return show_message('group buy not exists: ' . $group_buy_id);
                }
            } /* 积分兑换商品 */
            elseif ($flow_type == CART_EXCHANGE_GOODS) {
                //ecmoban模板堂 --zhuo
                $this->smarty->assign('is_exchange_goods', 1);
            } /* 预售商品 */
            elseif ($flow_type == CART_PRESALE_GOODS) {
                $this->smarty->assign('is_presale_goods', 1);
            } /*门店购物*/
            elseif ($flow_type == CART_OFFLINE_GOODS) {
                $this->smarty->assign('is_offline_goods', 1);
            } else {
                //正常购物流程  清空其他购物流程情况
                session()->put('flow_order.extension_code', '');
            }

            /*
             * 检查用户是否已经登录
             * 如果用户已经登录了则检查是否有默认的收货地址
             * 如果没有登录则跳转到登录和注册页面
             */
            if (empty($direct_shopping) && $user_id == 0) {
                /* 用户没有登录且没有选定匿名购物，转向到登录页面 */
                return dsc_header("Location: user.php\n");
            }

            $this->smarty->assign('warehouse_id', $warehouse_id);
            $this->smarty->assign('area_id', $area_id);
            $this->smarty->assign('area_city', $area_city);

            $user_address = $this->userAddressService->getUserAddressList($user_id);

            if ($direct_shopping != 1 && !empty($user_id)) {
                $browse_trace = "flow.php";
            } else {
                $browse_trace = "flow.php?step=checkout";
            }

            session([
                'browse_trace' => $browse_trace
            ]);

            $consignee = $this->flowUserService->getConsignee($user_id);

            if ($consignee['province'] > 0) {
                $province = $this->regionService->getRegionInfo($consignee['province']);
                $city = $this->regionService->getRegionInfo($consignee['city']);
                $district = $this->regionService->getRegionInfo($consignee['district']);
                $street = $this->regionService->getRegionInfo($consignee['street']);

                $consignee['province_name'] = $province['region_name'] ?? '';
                $consignee['city_name'] = $city['region_name'] ?? '';
                $consignee['district_name'] = $district['region_name'] ?? '';
                $consignee['street_name'] = $street['region_name'] ?? '';
                $consignee['address'] = $consignee['address'] ?? '';
                $consignee['region'] = $consignee['province_name'] . "&nbsp;" . $consignee['city_name'] . "&nbsp;" . $consignee['district_name'] . "&nbsp;" . $consignee['street_name'];
                $consignee['consignee_address'] = $consignee['region'] . "&nbsp;" . $consignee['address'];
            }

            if (!$user_address && $consignee['province'] > 0) {
                $user_address = [$consignee];
            }

            $this->smarty->assign('user_address', $user_address);

            /**
             * 有存在虚拟和实体商品
             */
            $goods_flow_type = $this->flowUserService->getGoodsFlowType($cart_value);
            $this->smarty->assign('goods_flow_type', $goods_flow_type);

            $this->smarty->assign('user_id', $user_id);

            /* 初始化地区ID */
            session([
                'flow_consignee' => $consignee
            ]);

            $this->smarty->assign('consignee', $consignee);

            /* 对商品信息赋值 */
            $cartGoodsList = cart_goods($flow_type, $cart_value, 1, $consignee, $store_id, 0, 1); // 取得商品列表，计算合计

            $is_kj = 0;
            if (CROSS_BORDER === true) { // 跨境多商户
                $web = app(CrossBorderService::class)->webExists();

                if (!empty($web)) {
                    $is_kj = $web->assignIsKj($cartGoodsList);
                    if (!is_numeric($is_kj)) {
                        return $is_kj;
                    }
                }
            }

            $virtual_count = $cartGoodsList['virtual'];
            $cart_goods_list = $cartGoodsList['goodslist'];

            /* 检查购物车中是否有商品 */
            if (empty($cart_goods_list)) {
                return show_message($GLOBALS['_LANG']['no_goods_in_cart'], '', '', 'warning');
            }

            $cart_goods_list_new = $this->flowActivityService->getFavourableCartGoodsList($cart_goods_list, $user_id);
            $cart_goods_list_new = $this->flowActivityService->merchantActivityCartGoodsList($cart_goods_list_new);

            $this->smarty->assign('goods_list', $cart_goods_list_new);

            // 取得商品列表，计算合计
            $cart_goods = FlowRepository::getNewGroupCartGoods($cart_goods_list_new);

            $drpUserAudit = cache('drp_user_audit_' . $user_id) ?? 0;
            $countRu = BaseRepository::getArraySum($cart_goods, 'ru_id');

            $drp_show_price = config('shop.drp_show_price') ?? 0;
            if ($drp_show_price == 1 && empty($drpUserAudit) && $countRu > 0) {
                return show_message(lang('cart.qualification_buy'), lang('flow.back_checkout'), 'flow.php');
            }

            $is_virtual = 0;
            $cart_goods_count = BaseRepository::getArrayCount($cart_goods);

            if ($cart_goods_count == $virtual_count) {
                $is_virtual = 1;
            }

            $this->smarty->assign('is_virtual', $is_virtual);

            $seckill_id = session('extension_id', 0);
            $this->smarty->assign('seckill_id', $seckill_id);//输出秒杀id

            /*获取门店信息  by kong 20160721 start*/
            $this->smarty->assign('country_list', get_regions());
            $this->smarty->assign('provinces', get_regions(1, 1));

            if ($store_id > 0) {
                /*获取该商品有货门店*/
                $cartStoreWhere = [
                    'rec_id' => $cart_value,
                    'store_id' => $store_id,
                    'rec_type' => CART_OFFLINE_GOODS
                ];

                $seller_store = $this->cartService->getCartOfflineStoreGoods($cartStoreWhere);
                $this->smarty->assign("seller_store", $seller_store);

                $store_info = $this->cartService->getCartInfo($cartStoreWhere);
                if (!$store_info['store_mobile']) {
                    $store_info['store_mobile'] = Users::where('user_id', $user_id)->value('mobile_phone');
                }
                if ($store_info['take_time'] == 0) {
                    $store_info['take_time'] = TimeRepository::getLocalDate("Y-m-d H:i:s", strtotime("+1 day"));
                }

                $now_time = TimeRepository::getLocalDate("Y-m-d H:i:s", TimeRepository::getGmTime());
                $this->smarty->assign("now_time", $now_time);
                $this->smarty->assign('store_info', $store_info);
            }

            $this->smarty->assign('store_id', $store_id);
            $this->smarty->assign('cart_value', $cart_value);
            $this->smarty->assign('store_seller', $store_seller);
            /*获取门店信息  by kong 20160721 end*/

            $cart_goods_number = $this->cartCommonService->getBuyCartGoodsNumber($flow_type, $cart_value);
            $this->smarty->assign('cart_goods_number', $cart_goods_number);

            /* 对是否允许修改购物车赋值 */
            if ($flow_type != CART_GENERAL_GOODS || session('one_step_buy') == '1') {
                $this->smarty->assign('allow_edit_cart', 0);
            } else {
                $this->smarty->assign('allow_edit_cart', 1);
            }

            /*
             * 取得购物流程设置
             */
            $this->smarty->assign('config', config('shop'));

            /*
             * 取得订单信息
             */
            $order = flow_order_info();

            $this->smarty->assign('order', $order);

            /* 如果能开发票，取得发票内容列表 */
            if ((config('shop.can_invoice') == null || config('shop.can_invoice') == 1) && config('shop.invoice_content') != '' && $flow_type != CART_EXCHANGE_GOODS
            ) {
                $inv_content_list = explode("\n", str_replace("\r", '', config('shop.invoice_content')));
                $this->smarty->assign('inv_content', $inv_content_list[0]);
                //默认发票计算
                $order['need_inv'] = 1;
                $order['inv_type'] = '';
                $order['inv_payee'] = $GLOBALS['_LANG']['personal'];
                $order['inv_content'] = $inv_content_list[0];
            }

            /* 计算折扣 */
            if ($flow_type != CART_EXCHANGE_GOODS && $flow_type != CART_GROUP_BUY_GOODS) {
                $discount = compute_discount(3, $cart_value);
                $this->smarty->assign('discount', $discount['discount']);
                $favour_name = empty($discount['name']) ? '' : join(',', $discount['name']);
                $this->smarty->assign('your_discount', sprintf($GLOBALS['_LANG']['your_discount'], $favour_name, $this->dscRepository->getPriceFormat($discount['discount'])));
            }

            if (!$user_address) {
                $consignee = [
                    'province' => 0,
                    'city' => 0
                ];
                // 取得国家列表、商店所在国家、商店所在国家的省列表
                $this->smarty->assign('please_select', $GLOBALS['_LANG']['please_select']);

                $country_list = $this->areaService->getRegionsLog();
                $province_list = $this->areaService->getRegionsLog(1, 1);
                $city_list = $this->areaService->getRegionsLog(2, $consignee['province']);
                $district_list = $this->areaService->getRegionsLog(3, $consignee['city']);

                $this->smarty->assign('country_list', $country_list);
                $this->smarty->assign('province_list', $province_list);
                $this->smarty->assign('city_list', $city_list);
                $this->smarty->assign('district_list', $district_list);
                $this->smarty->assign('consignee', $consignee);
            }

            /*
             * 计算订单的费用
             */
            $total = order_fee($order, $cart_goods, $consignee, $cart_value, $cart_goods_list, $store_id, $store_seller, $user_id, 0, $flow_type);

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
                    return show_message('service not exists');
                }
            }

            $this->smarty->assign('total', $total);
            $this->smarty->assign('shopping_money', sprintf($GLOBALS['_LANG']['shopping_money'], $total['formated_goods_price']));
            $this->smarty->assign('market_price_desc', sprintf($GLOBALS['_LANG']['than_market_price'], $total['formated_market_price'], $total['formated_saving'], $total['save_rate']));

            /* 取得支付列表 */
            if ($order['shipping_id'] == 0) {
                $cod = true;
                $cod_fee = 0;
            } else {
                $shipping = shipping_info($order['shipping_id']);
                $cod = $shipping && $shipping['support_cod'] ? $shipping['support_cod'] : 0;

                if ($cod) {
                    /* 如果是团购，且保证金大于0，不能使用货到付款 */
                    if ($flow_type == CART_GROUP_BUY_GOODS) {
                        $group_buy_id = session('extension_id');
                        if ($group_buy_id <= 0) {
                            return show_message('error group_buy_id');
                        }

                        if ($deposit > 0) {
                            $cod = false;
                            $cod_fee = 0;

                            /* 赋值保证金 */
                            $this->smarty->assign('gb_deposit', $deposit);
                        }
                    }

                    if ($cod) {
                        $shipping_area_info = shipping_info($order['shipping_id']);
                        $cod_fee = isset($shipping_area_info['pay_fee']) ? $shipping_area_info['pay_fee'] : 0;
                    }
                } else {
                    $cod_fee = 0;
                }
            }

            // 给货到付款的手续费加<span id>，以便改变配送的时候动态显示
            $payment_list = available_payment_list(1, $cod_fee);

            if (!empty($payment_list)) {
                foreach ($payment_list as $key => $payment) {
                    if ($payment['is_cod'] == '1') {
                        $payment_list[$key]['format_pay_fee'] = '<span id="ECS_CODFEE">' . $payment['format_pay_fee'] . '</span>';
                    }

                    /* 如果有余额支付 */
                    if ($payment['pay_code'] == 'balance') {
                        /* 如果未登录，不显示 */
                        if (session('user_id') == 0 || $is_kj == 1) {
                            unset($payment_list[$key]);
                        } else {
                            if (session('flow_order.pay_id') == $payment['pay_id']) {
                                $this->smarty->assign('disable_surplus', 1);
                            }
                        }
                    }

                    /* 如果积分商城商品、拼团商品、门店自提商品、虚拟商品、商家商品不显示货到付款  */
                    if (in_array($flow_type, [CART_EXCHANGE_GOODS, CART_TEAM_GOODS, CART_OFFLINE_GOODS]) || $total['real_goods_count'] == 0 || $total['seller_goods_count'] > 0) {
                        if ($payment ['pay_code'] == 'cod') {
                            unset($payment_list[$key]);
                        }
                    }

                    // 过滤掉在线支付的方法(余额支付,支付宝等等),因为订单结算页只允许显示一个在线支付按钮 start
                    if ($payment['is_online'] == 1) {
                        unset($payment_list[$key]);
                    }
                }
            }

            /* 如果是分期购，则只显示在线支付 */
            if ($cart_goods && count($cart_goods) == 1 && $cart_goods[0]['stages_qishu'] > 0) {
                foreach ($payment_list as $k => $v) {
                    if ($v['pay_code'] != 'onlinepay') {
                        unset($payment_list[$k]);
                    }
                }

                $this->smarty->assign('is_stages', 0);
            } else {
                $this->smarty->assign('is_stages', 1);
            }

            $this->smarty->assign('payment_list', $payment_list);

            /* 取得包装与贺卡 */
            if ($total['real_goods_count'] > 0) {
                /* 只有有实体商品,才要判断包装和贺卡 */
                if (config('shop.use_package') == 1) {
                    /* 如果使用包装，取得包装列表及用户选择的包装 */
                    $this->smarty->assign('pack_list', pack_list());
                }

                /* 如果使用贺卡，取得贺卡列表及用户选择的贺卡 */
                if (config('shop.use_card') == 1) {
                    $this->smarty->assign('card_list', card_list());
                }
            }

            $user_info = Users::select('user_id', 'user_money', 'pay_points')->where('user_id', $user_id);
            $user_info = BaseRepository::getToArrayFirst($user_info);

            $user_info['user_money'] = $user_info['user_money'] ?? 0;
            $user_info['pay_points'] = $user_info['pay_points'] ?? 0;

            $balance_enabled = Payment::where('pay_code', 'balance')->value('enabled');

            /* 如果使用余额，取得用户余额 */
            $allow_use_surplus = 0;
            if (config('shop.use_surplus') == 1 && session('user_id') > 0 && $user_info['user_money'] > 0) {
                if ($balance_enabled && $is_kj == 0) { // 安装了"余额支付",才显示余额支付输入框 bylu;
                    // 能使用余额
                    $allow_use_surplus = 1;
                    $this->smarty->assign('your_surplus', $user_info['user_money']);
                }
            }
            $this->smarty->assign('allow_use_surplus', $allow_use_surplus);

            $allow_use_integral = 0;
            /* 如果使用积分，取得用户可用积分及本订单最多可以使用的积分 */
            if (config('shop.use_integral') == 1 && $user_id > 0 && $user_info['pay_points'] > 0 && ($flow_type == CART_GENERAL_GOODS || $flow_type == CART_ONESTEP_GOODS)
            ) {
                // 能使用积分
                $order_max_integral = $this->flowActivityService->getFlowAvailablePoints($cart_goods);
                if ($order_max_integral > 0 && $user_info['pay_points'] >= $order_max_integral) {

                    $this->smarty->assign('order_max_integral', $order_max_integral);  // 可用积分
                    $this->smarty->assign('your_integral', $user_info['pay_points']); // 用户积分

                    $allow_use_integral = 1;
                }
            }

            $this->smarty->assign('allow_use_integral', $allow_use_integral);

            $cart_ru_id = BaseRepository::getKeyPluck($cart_goods, 'ru_id');

            // 社区驿站
            if (file_exists(MOBILE_GROUPBUY) && !empty(config('shop.open_community_post'))) {
                $post = app(CgroupService::class)->postExists();
                $is_support = 1; // 订单所有商家支持社区驿站

                if (!empty($consignee['leader_id'])) {
                    $leader_address = $consignee['province_name'] . '&nbsp;' . $consignee['city_name'] . '&nbsp;' . $consignee['district_name'] . '&nbsp;' . $consignee['address'] . '&nbsp;' . '（电话：' . $consignee['post_mobile'] . '）&nbsp;' . $consignee['consignee'] . '&nbsp;' . $consignee['mobile'];

                    $this->smarty->assign('leader_id', $consignee['leader_id']);
                    $this->smarty->assign('leader_address', $leader_address);
                }

                foreach ($cart_ru_id as $key => $value) {
                    if (!empty($post)) {
                        $is_support = $is_support > 0 ? $post->supportPost($value) : 0;
                    }
                }

                if (!empty($post) && $is_support == 1) {
                    $address = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['consignee'];
                    $location = $post->addressChange($address);
                    $nearleader['count'] = $post->nearleader($consignee['address_id'], 0);
                    $nearleader['lat'] = $location['lat'] ?? 0;
                    $nearleader['lng'] = $location['lng'] ?? 0;

                    $this->smarty->assign('nearleader', $nearleader);
                }
            }

            $cart_ru_id = BaseRepository::getImplode($cart_ru_id);

            /* 如果使用红包，取得用户可以使用的红包及用户选择的红包 */
            if (config('shop.use_bonus') == 1 && in_array($flow_type, [CART_GENERAL_GOODS, CART_ONESTEP_GOODS, CART_OFFLINE_GOODS])) {
                // 取得用户可用红包
                $user_bonus = $this->bonusService->getUserBonusInfo($user_id, $total['bonus_goods_price'], $total['seller_amount']);
                if (!empty($user_bonus)) {
                    foreach ($user_bonus as $key => $val) {
                        $user_bonus[$key]['bonus_money_formated'] = $this->dscRepository->getPriceFormat($val['type_money'], false);
                        $user_bonus[$key]['min_goods_amount_formated'] = $this->dscRepository->getPriceFormat($val['min_goods_amount'], false);
                        $bonus_ids[] = $val['bonus_id'];

                        // $user_bonus[$key]['use_end_date'] = TimeRepository::getLocalDate(config('shop.time_format'), $val['end_time']);
                        $user_bonus[$key]['use_start_date'] = TimeRepository::getLocalDate(config('shop.time_format'), $val['use_start_date']);
                        $user_bonus[$key]['use_end_date'] = TimeRepository::getLocalDate(config('shop.time_format'), $val['use_end_date']);
                    }

                    $this->smarty->assign('bonus_list', $user_bonus);
                }

                $bonus = $this->bonusService->getUserBounsNewList($user_id, 1, 0, 'bouns_available_gotoPage', 0, 20, $cart_ru_id); //获取用户全部的红包列表，显示20
                //获取不能使用的红包数组
                if (!empty($bonus['available_list'])) {
                    foreach ($bonus['available_list'] as $k => $v) {
                        foreach ($user_bonus as $bk => $br) {
                            if ($br['bonus_id'] == $v['bonus_id']) {
                                unset($bonus['available_list'][$k]);
                                continue;
                            }
                        }
                    }

                    $no_bonuslist = !empty($bonus['available_list']) ? $bonus['available_list'] : [];
                    $this->smarty->assign('no_bonuslist', $no_bonuslist);
                }

                // 能使用红包
                $this->smarty->assign('allow_use_bonus', 1);
            }

            /* 储值卡 begin */
            $allow_use_value_card = 0;
            if (config('shop.use_value_card') == 1 && $flow_type != CART_EXCHANGE_GOODS) {
                // 取得用户可用储值卡
                $value_card = $this->valueCardService->getUserValueCard($user_id, $cart_goods);

                if ((!empty($value_card) && isset($value_card['is_value_cart'])) || $is_kj == 1) {
                    $value_card = [];
                    $this->smarty->assign('is_value_cart', 0);
                } else {
                    $this->smarty->assign('is_value_cart', 1);
                }

                $this->smarty->assign('value_card_list', $value_card);

                // 能使用储值卡
                $allow_use_value_card = empty($value_card) ? 0 : 1;
            }

            $this->smarty->assign('allow_use_value_card', $allow_use_value_card);

            // 如果开启用户支付密码, 可使用余额，且用户有余额 或  能使用储值卡  显示支付密码
            if (config('shop.use_paypwd') == 1 && ($allow_use_surplus == 1 || $allow_use_value_card == 1)) {
                $this->smarty->assign('open_pay_password', 1);
                $this->smarty->assign('pay_pwd_error', 1);
            }

            /* 优惠券 start */
            if (config('shop.use_coupons') == 1 && ($flow_type == CART_GENERAL_GOODS || $flow_type == CART_ONESTEP_GOODS || $flow_type == CART_OFFLINE_GOODS)) {
                $coupons_list_all = $this->couponsService->flowUserCoupons($user_id, $cart_goods, true, $consignee, $total['shipping_fee']);

                // 取得用户可用优惠券
                $user_coupons = $coupons_list_all['coupons_list'] ?? [];
                // 获取不能使用的优惠券
                $coupons_list_disabled = $coupons_list_all['coupons_list_disabled'] ?? [];

                if (!empty($coupons_list_disabled)) {
                    foreach ($coupons_list_disabled as $k => $v) {
                        if (isset($v['is_use']) && $v['is_use'] == 0) {

                            $cou_end_time = $v['cou_end_time'];
                            if ($v['valid_type'] == 2) {
                                $cou_end_time = $v['valid_time'];
                            }

                            $coupons_list_disabled[$k]['cou_end_time'] = TimeRepository::getLocalDate('Y-m-d', $cou_end_time);
                            $coupons_list_disabled[$k]['cou_type_name'] = CommonRepository::couTypeFormat($v['cou_type']);
                            $coupons_list_disabled[$k]['cou_money_formated'] = $this->dscRepository->getPriceFormat($v['cou_money']);
                            $coupons_list_disabled[$k]['uc_money_formated'] = $this->dscRepository->getPriceFormat($v['uc_money']);

                            if ($v['spec_cat']) {
                                $coupons_list_disabled[$k]['cou_goods_name'] = lang('common.lang_goods_coupons.is_cate');
                            } elseif ($v['cou_goods']) {
                                $coupons_list_disabled[$k]['cou_goods_name'] = lang('common.lang_goods_coupons.is_goods');
                            } else {
                                $coupons_list_disabled[$k]['cou_goods_name'] = lang('common.lang_goods_coupons.is_all');
                            }
                        } else {
                            unset($coupons_list_disabled[$k]);
                        }
                    }
                }

                if (!empty($user_coupons)) {
                    foreach ($user_coupons as $k => $v) {

                        $cou_end_time = $v['cou_end_time'];
                        if ($v['valid_type'] == 2) {
                            $cou_end_time = $v['valid_time'];
                        }

                        $user_coupons[$k]['cou_end_time'] = TimeRepository::getLocalDate('Y-m-d', $cou_end_time);
                        $user_coupons[$k]['cou_type_name'] = CommonRepository::couTypeFormat($v['cou_type']);
                        $user_coupons[$k]['cou_money_formated'] = $this->dscRepository->getPriceFormat($v['cou_money']);
                        $user_coupons[$k]['uc_money_formated'] = $this->dscRepository->getPriceFormat($v['uc_money']);

                        if (!empty($v['spec_cat'])) {
                            $user_coupons[$k]['cou_goods_name'] = lang('common.lang_goods_coupons.is_cate');
                        } elseif (!empty($v['cou_goods'])) {
                            $user_coupons[$k]['cou_goods_name'] = lang('common.lang_goods_coupons.is_goods');
                        } else {
                            $user_coupons[$k]['cou_goods_name'] = lang('common.lang_goods_coupons.is_all');
                        }
                    }
                }

                // 没有满足条件的优惠券数组
                $this->smarty->assign('coupons_list', $coupons_list_disabled);
                // 用户可用优惠券列表
                $this->smarty->assign('user_coupons', $user_coupons);
            }

            /* 如果使用缺货处理，取得缺货处理列表 */
            if (config('shop.use_how_oos') == 1) {
                if (is_array($GLOBALS['_LANG']['oos']) && !empty($GLOBALS['_LANG']['oos'])) {
                    $this->smarty->assign('how_oos_list', $GLOBALS['_LANG']['oos']);
                }
            }

            $this->smarty->assign('flow_type', $flow_type);

            /* 保存 session */
            session([
                'flow_order' => $order
            ]);
        }

        /*------------------------------------------------------ */
        //-- 完成所有订单操作，提交到数据库
        /*------------------------------------------------------ */
        elseif ($step == 'done') {
            load_helper('clips');
            load_helper('payment');

            //门店id
            $store_id = (int)request()->input('store_id', 0);
            $warehouse_id = (int)request()->input('warehouse_id', 0);
            $area_id = (int)request()->input('area_id', 0);

            $act = addslashes(trim(request()->input('act', '')));

            /* 取得购物类型 */
            $flow_type = intval(session('flow_type', CART_GENERAL_GOODS));

            if ($act == 'balance') { //余额支付

                //取出"余额支付"的支付信息;
                $where = [
                    'pay_code' => 'balance'
                ];
                $balance_info = $this->paymentService->getPaymentInfo($where);

                $order_sn = addslashes_deep(request()->input('order_sn', ''));
                //通过订单号查询出该订单的总价;
                $where = [
                    'order_sn' => $order_sn,
                    'user_id' => $user_id
                ];
                $order_info = $this->orderService->getOrderInfo($where);

                $order_amount = floatval($order_info['order_amount']);

                //查询出当前用户的剩余余额
                $user_info = Users::where('user_id', $user_id);
                $user_info = BaseRepository::getToArrayFirst($user_info);

                if (empty($user_info)) {
                    return redirect('/');
                }

                $user_money = $user_info ? $user_info['user_money'] : 0;

                // 检查有没有用户有开启支付密码
                $pay_pwd = addslashes(trim(request()->input('pay_pwd', '')));
                $pay_pwd_error = (int)request()->input('pay_pwd_error', 0);

                // 开启配置支付密码 且余额支付 验证支付密码
                if (config('shop.use_paypwd') == 1) {
                    try {
                        $this->flowService->check_user_paypwd($user_id, $pay_pwd, $pay_pwd_error);
                    } catch (HttpException $httpException) {
                        $back_url = $httpException->getCode() == 1 ? 'user.php?act=account_safe' : '';
                        return show_message($httpException->getMessage(), $GLOBALS['_LANG']['back'], $back_url, 'error');
                    }
                }

                //如果用户余额足够支付订单;
                if ($order_info && $user_money >= $order_amount) {
                    if ($order_info['order_id'] > 0) {

                        //判断该订单是否拥有子订单;
                        $where = [
                            'main_order_id' => $order_info['order_id'],
                            'user_id' => $user_id
                        ];
                        $child_info = $this->orderService->getOrderInfo($where);

                        $child_ids = isset($child_info['order_id']) && !empty($child_info['order_id']) ? $child_info['order_id'] : '';
                        $child_sn = isset($child_info['order_sn']) && !empty($child_info['order_sn']) ? explode(",", $child_info['order_sn']) : '';
                    } else {
                        $child_ids = [];
                        $child_sn = [];
                    }

                    if (!empty($child_ids)) {
                        $order_ids = $order_info['order_id'] . ',' . $child_ids;
                    } else {
                        $order_ids = $order_info['order_id'];
                    }

                    $order_ids = !is_array($order_ids) ? explode(",", $order_ids) : $order_ids;

                    /* 扣除余额(记录到"账户日志"表中) */
                    if ($order_info['user_id'] > 0) {
                        log_account_change($order_info['user_id'], $order_amount * (-1), 0, 0, 0, sprintf($GLOBALS['_LANG']['pay_order'], $order_info['order_sn']));

                        //扣款成功,修改订单为,已确认,已支付;
                        $order['order_status'] = OS_CONFIRMED;
                        $order['confirm_time'] = gmtime();
                        if ($order_info['extension_code'] == 'presale') {//liu
                            $order['pay_status'] = PS_PAYED_PART; //部分付款
                            $order['surplus'] = $order_info['order_amount'];
                            $order['order_amount'] = $order_info['goods_amount'] + $order_info['shipping_fee'] + $order_info['insure_fee'] + $order_info['tax'] - $order_info['discount'] - $order['surplus'];
                        } else {
                            $order['pay_status'] = PS_PAYED;
                            $order['surplus'] = $order_amount + $order_info['surplus']; //该字段记录当前订单使用了多少余额支付的;
                            $order['order_amount'] = 0;
                        }

                        if (config('shop.sales_volume_time') == SALES_PAY) {
                            $order['is_update_sale'] = 1;
                        }

                        $order['pay_time'] = gmtime();
                        $order['pay_name'] = $balance_info['pay_name'];
                        $order['pay_id'] = $balance_info['pay_id'];

                        /* 记录订单操作记录 */
                        order_action($order_sn, OS_CONFIRMED, SS_UNSHIPPED, PS_PAYED, $GLOBALS['_LANG']['flow_surplus_pay'], $GLOBALS['_LANG']['buyer']);

                        if ($child_sn) {
                            /* 记录订单操作记录 */
                            foreach ($child_sn as $key => $row) {
                                order_action($row, OS_CONFIRMED, SS_UNSHIPPED, PS_PAYED, $GLOBALS['_LANG']['flow_surplus_pay'], $GLOBALS['_LANG']['buyer']);
                            }
                        }

                        $order['money_paid'] = 0;
                        //如果有子订单,处理子订单付款金额;
                        if (!empty($child_ids)) {
                            $where = [
                                'order_id' => $order_ids
                            ];
                            $child_order_amounts = $this->orderService->getOrderList($where);

                            if ($child_order_amounts) {
                                foreach ($child_order_amounts as $k => $v) {
                                    $order['surplus'] = $v['order_amount'] + $v['surplus'];
                                    OrderInfo::where('order_id', $v['order_id'])->update($order);
                                }
                            }
                        } else {
                            OrderInfo::whereIn('order_id', $order_ids)->update($order);
                        }

                        /* 修改"支付日志"中该订单为已支付 */
                        PayLog::whereIn('order_id', $order_ids)->update(['is_paid' => 1]);

                        /* 如果订单金额为0 处理虚拟卡 by wu start */
                        foreach ($order_ids as $order_one) {
                            if ($order['order_amount'] <= 0) {
                                $where = [
                                    'order_id' => $order_one,
                                    'user_id' => $user_id
                                ];
                                $orderInfo = $this->orderService->getOrderInfo($where);

                                $where = [
                                    'order_id' => $orderInfo['order_id'],
                                    'is_real' => 0,
                                    'extension_code' => 'virtual_card'
                                ];
                                $res = $this->orderGoodsService->getOrderGoodsList($where);

                                $virtual_goods = [];
                                if ($res) {
                                    foreach ($res as $row) {
                                        $virtual_goods['virtual_card'][] = ['goods_id' => $row['goods_id'], 'goods_name' => $row['goods_name'], 'num' => $row['num']];
                                    }
                                }

                                if ($virtual_goods && $flow_type != CART_GROUP_BUY_GOODS) {
                                    /* 虚拟卡发货 */
                                    $error_msg = '';
                                    if (virtual_goods_ship($virtual_goods, $error_msg, $orderInfo['order_sn'], true)) {
                                        /* 如果没有实体商品，修改发货状态，送积分和红包 */
                                        $where = [
                                            'order_id' => $orderInfo['order_id'],
                                            'is_real' => 1
                                        ];
                                        $count = $this->orderService->getOrderGoodsCount($where);

                                        if ($count <= 0) {
                                            /* 修改订单状态 */
                                            update_order($orderInfo['order_id'], ['shipping_status' => SS_RECEIVED, 'shipping_time' => gmtime()]);

                                            /* 如果订单用户不为空，计算积分，并发给用户；发红包 */
                                            if ($orderInfo['user_id'] > 0) {

                                                /* 计算并发放积分 */
                                                $integral = integral_to_give($orderInfo);
                                                log_account_change($orderInfo['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($GLOBALS['_LANG']['order_gift_integral'], $orderInfo['order_sn']));

                                                /* 发放红包 */
                                                send_order_bonus($orderInfo['order_id']);

                                                /* 发放优惠券 bylu */
                                                send_order_coupons($orderInfo['order_id']);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        /* 如果订单金额为0 处理虚拟卡 by wu end */

                        /* 众筹状态的更改 by wu */
                        update_zc_project($order_info['order_id']);

                        //付款成功创建快照
                        create_snapshot($order_info['order_id']);

                        if ($order_info['extension_code'] == 'presale') {
                            //付款成功后增加预售人数
                            get_presale_num($order_info['order_id']);
                        }

                        /* 更新商品销量 */
                        get_goods_sale($order_info['order_id']);

                        /* 如果需要，发短信 */
                        $ru_id = ordergoods::where('order_id', $order_info['order_id'])->value('ru_id');

                        $shop_name = $this->merchantCommonService->getShopName($ru_id, 1);

                        if ($ru_id == 0) {
                            $sms_shop_mobile = config('shop.sms_shop_mobile');
                        } else {
                            $sms_shop_mobile = SellerShopinfo::where('ru_id', $ru_id)->value('mobile');
                        }

                        if (config('shop.sms_order_payed') == '1' && $sms_shop_mobile) {
                            $order_region = $this->orderService->getOrderUserRegion($order_info['order_id']);

                            //阿里大鱼短信接口参数
                            $smsParams = [
                                'shop_name' => $shop_name,
                                'shopname' => $shop_name,
                                'order_sn' => $order_info['order_sn'],
                                'ordersn' => $order_info['order_sn'],
                                'consignee' => $order_info['consignee'],
                                'order_region' => $order_region,
                                'orderregion' => $order_region,
                                'address' => $order_info['address'],
                                'order_mobile' => $order_info['mobile'],
                                'ordermobile' => $order_info['mobile'],
                                'mobile_phone' => $sms_shop_mobile,
                                'mobilephone' => $sms_shop_mobile
                            ];

                            $this->commonRepository->smsSend($sms_shop_mobile, $smsParams, 'sms_order_payed');
                        }

                        //门店处理
                        $stores_order = StoreOrder::where('order_id', $order_info['order_id'])->first();
                        $stores_order = $stores_order ? $stores_order->toArray() : [];

                        if ($stores_order && $stores_order['store_id'] > 0) {
                            if ($order_info['mobile']) {
                                $user_mobile_phone = $order_info['mobile'];
                            } else {
                                $user_mobile_phone = Users::where('user_id', $user_id)->value('mobile_phone');
                            }

                            if ($user_mobile_phone) {
                                $pick_code = substr($order['order_sn'], -3) . mt_rand(100, 999);

                                StoreOrder::where('id', $stores_order['id'])->update(['pick_code' => $pick_code]);

                                //门店订单->短信接口参数
                                $store_smsParams = [
                                    'user_name' => $user_name,
                                    'username' => $user_name,
                                    'order_sn' => $order_info['order_sn'],
                                    'ordersn' => $order_info['order_sn'],
                                    'code' => $pick_code
                                ];

                                $this->commonRepository->smsSend($user_mobile_phone, $store_smsParams, 'store_order_code');
                            }
                        }

                        //付款成功,跳转到 支付成功页;
                        return dsc_header("location:flow.php?step=pay_success&order_id=" . $order_info['order_id'] . "&store_id=$store_id");
                    }
                } else {
                    //余额不足;
                    return show_message($GLOBALS["_LANG"]['balance_not_enough'], $GLOBALS["_LANG"]['go_pay'], '');
                }
            } elseif ($act == 'chunsejinrong') { //白条支付
                $order_sn = addslashes(trim(request()->input('order_sn')));

                //取出"白条支付"的支付信息;
                $where = [
                    'pay_code' => 'chunsejinrong'
                ];
                $bt_payment_info = $this->paymentService->getPaymentInfo($where);

                //通过订单号查询出该订单的总价;
                $where = [
                    'order_sn' => $order_sn,
                    'user_id' => $user_id
                ];
                $order_info = $this->orderService->getOrderInfo($where);

                $order_id = $order_info['order_id'] ?? 0;

                $logCont = BaitiaoLog::where('user_id', $user_id)->where('order_id', $order_id)->count();
                if (empty($order_info) || $logCont > 0) {
                    if ($logCont > 0) {
                        //付款成功,跳转到 支付成功页;
                        return dsc_header("location:flow.php?step=pay_success&order_id=" . $order_id . "&store_id=" . $store_id);
                    } else {
                        return dsc_header("Location:" . url('/') . "\n");
                    }
                }

                //检查会员白条余额是否足够
                $bt_other = [
                    'user_id' => $user_id
                ];
                $bt_info = $this->userBaitiaoService->getBaitiaoInfo($bt_other);

                $repay_bt = BaitiaoLog::selectRaw("SUM(stages_one_price * (stages_total - yes_num)) AS total_amount")
                    ->where('user_id', $user_id)
                    ->where('is_repay')
                    ->where('is_refund')
                    ->first();

                $repay_bt = $repay_bt ? $repay_bt->toArray() : [];

                $repay_bt = $repay_bt ? $repay_bt['total_amount'] : 0;

                if (!$bt_info) {
                    return show_message($GLOBALS['_LANG']['Ious_error_one'], $GLOBALS['_LANG']['back_up_page'], '');
                }

                $remain_amount = floatval($bt_info['amount']) - floatval($repay_bt);

                //如果当前订单价格,大于可用白条余额;
                if ($order_info['order_amount'] > $remain_amount) {
                    return show_message($GLOBALS['_LANG']['Ious_error_two'], $GLOBALS['_LANG']['back_up_page'], '');
                } else {
                    if ($order_info && $order_info['order_id'] > 0) {

                        //先取出当前用户的白条日志信息 bylu;
                        $user_baitiao_info = BaitiaoLog::where('is_repay', 0)->where('user_id', $user_id)->get();
                        $user_baitiao_info = $user_baitiao_info ? $user_baitiao_info->toArray() : [];

                        if ($user_baitiao_info) {
                            foreach ($user_baitiao_info as $k => $v) {
                                if ($user_baitiao_info[$k]['is_stages'] == 1) {
                                    $repay_date = unserialize($user_baitiao_info[$k]['repay_date']);
                                    $strtotime_repay_date = $repay_date[$user_baitiao_info[$k]['yes_num'] + 1] ?? $repay_date[$user_baitiao_info[$k]['yes_num']];
                                    $over_date[] = strtotime($strtotime_repay_date);
                                } else {
                                    $over_date[] = $user_baitiao_info[$k]['repay_date'];
                                }
                                if (TimeRepository::getGmTime() >= $over_date[$k]) {
                                    return show_message($GLOBALS['_LANG']['Ious_error_Three'], $GLOBALS['_LANG']['back_up_page'], '');
                                }
                            }
                        }

                        //更新订单状态为已支付;
                        $order['order_status'] = OS_CONFIRMED;
                        $order['pay_time'] = TimeRepository::getGmTime();
                        $order['pay_name'] = $bt_payment_info['pay_name'];
                        $order['pay_id'] = $bt_payment_info['pay_id'];
                        $order['money_paid'] = floatval($order_info['order_amount']);
                        $order['confirm_time'] = TimeRepository::getGmTime();
                        $order['pay_status'] = PS_PAYED;
                        $order['order_amount'] = 0;

                        OrderInfo::where('order_id', $order_info['order_id'])->update($order);

                        /* 记录订单操作记录 */
                        $note = $GLOBALS['_LANG']['user_baitiao_pay'];
                        order_action($order_sn, OS_CONFIRMED, SS_UNSHIPPED, PS_PAYED, $note, trans('common.buyer'));

                        /* 修改"支付日志"中该订单为已支付 */
                        PayLog::where('order_id', $order_info['order_id'])->update(['is_paid' => 1]);

                        //判断该订单是否拥有子订单;
                        $where = [
                            'main_order_id' => $order_info['order_id'],
                            'user_id' => $user_id
                        ];
                        $child_ids = $this->orderService->getOrderInfo($where);
                        $child_ids = $child_ids ? $child_ids['order_id'] : '';

                        //处理子订单的已付款金额;
                        if (!empty($child_ids)) {
                            $where = [
                                'order_id' => $child_ids
                            ];
                            $child_order_amounts = $this->orderService->getOrderList($where);

                            if ($child_order_amounts) {
                                foreach ($child_order_amounts as $k => $v) {
                                    $child_order_other['order_status'] = OS_CONFIRMED;
                                    $child_order_other['pay_time'] = TimeRepository::getGmTime();
                                    $child_order_other['pay_name'] = $bt_payment_info['pay_name'];
                                    $child_order_other['pay_code'] = $bt_payment_info['pay_code'];
                                    $child_order_other['pay_id'] = $bt_payment_info['pay_id'];
                                    $child_order_other['money_paid'] = floatval($v['order_amount']);
                                    $child_order_other['confirm_time'] = TimeRepository::getGmTime();
                                    $child_order_other['pay_status'] = PS_PAYED;
                                    $child_order_other['order_amount'] = 0;

                                    OrderInfo::where('order_id', $v['order_id'])->update($order);

                                    //贡云确认订单
                                    $this->jigonManageService->jigonConfirmOrder($v['order_id']);

                                    order_action($v['order_sn'], OS_CONFIRMED, SS_UNSHIPPED, PS_PAYED, $note, trans('common.buyer'));

                                    /* 修改"支付日志"中该订单为已支付 */
                                    PayLog::where('order_id', $v['order_id'])->update(['is_paid' => 1]);
                                }
                            }
                        }

                        $stages_total = 0;
                        $stages_one_price = 0;

                        //如果是白条分期商品;
                        $stages_other = [
                            'order_sn' => $order_sn
                        ];

                        $stages_info = $this->userBaitiaoService->getStagesInfo($stages_other);

                        $is_stages = 0;
                        $repay_date = [];
                        if ($stages_info) {
                            $is_stages = 1; //是否分期;
                            $stages_total = $stages_info['stages_total']; //分期总期数;
                            $stages_one_price = $stages_info['stages_one_price']; //每期还款额;
                            $repay_date = $stages_info['repay_date']; //白条分期的还款日期;
                        }

                        //将白条消费记录插入白条日志表;
                        $bt_insert_other = [
                            'baitiao_id' => $bt_info['baitiao_id'],
                            'user_id' => $user_id,
                            'use_date' => TimeRepository::getGmTime(),
                            'repay_date' => $repay_date,
                            'order_id' => $order_info['order_id'],
                            'is_repay' => 0,
                            'is_stages' => $is_stages,
                            'stages_total' => $stages_total,
                            'stages_one_price' => $stages_one_price,
                            'yes_num' => 0
                        ];

                        $bt_log_id = BaitiaoLog::insertGetId($bt_insert_other);

                        if ($stages_total > 0 && $is_stages == 1) {
                            for ($i = 1; $i <= $stages_total; $i++) {
                                $id = BaitiaoPayLog::where('log_id', $bt_log_id)
                                    ->where('baitiao_id', $bt_info['baitiao_id'])
                                    ->where('stages_num', $i)
                                    ->value('id');

                                if (!$id) {
                                    $pay_log_other = [
                                        'log_id' => $bt_log_id,
                                        'baitiao_id' => $bt_info['baitiao_id'],
                                        'stages_num' => $i,
                                        'stages_price' => $stages_one_price,
                                        'add_time' => TimeRepository::getGmTime()
                                    ];

                                    BaitiaoPayLog::insert($pay_log_other);
                                }
                            }

                            $bt_pay_count = BaitiaoPayLog::where('log_id', $bt_log_id)->count();
                            if ($stages_total == $bt_pay_count) {
                                $baitiao_log_other['pay_num'] = 1;

                                BaitiaoLog::where('log_id', $bt_log_id)->update($baitiao_log_other);
                            }
                        }

                        //付款成功创建快照
                        create_snapshot($order_info['order_id']);

                        /* 更新商品销量 */
                        get_goods_sale($order_info['order_id']);

                        /* 如果使用库存，且付款时减库存，且订单金额为0，则减少库存 */
                        if (config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_PAID && $order['order_amount'] <= 0) {
                            change_order_goods_storage($order_info['order_id'], true, SDT_PAID, 15, 0, $store_id);
                        }

                        if ($order_info['extension_code'] == 'presale') {
                            //付款成功后增加预售人数
                            get_presale_num($order_info['order_id']);
                        }
                    }

                    //付款成功,跳转到 支付成功页;
                    return dsc_header("location:flow.php?step=pay_success&order_id=" . $order_info['order_id'] . "&store_id=$store_id");
                }
                //@模板堂-bylu 白条支付 end
            } else {
                $where_flow = '';
                //取得购买购物车商品ID
                $done_cart_value = addslashes(request()->input('done_cart_value', 0));

                if (empty($done_cart_value)) {
                    return redirect('/');
                }

                /* 检查购物车中是否有商品 */
                $where = [
                    'rec_id' => $done_cart_value,
                    'rec_type' => $flow_type
                ];
                $count = $this->cartService->getCartCount($where);
                if (!$count) {
                    return redirect('/');
                }

                /* 检查商品、货品库存 start */
                /* 如果使用库存，且下订单时减库存，则减少库存 */
                //--库存管理use_storage 1为开启 0为未启用-------  SDT_PLACE：0为发货时 1为下订单时
                if (config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_PLACE) {
                    try {
                        $_cart_goods_stock = $this->cartCommonService->cartGoodsNumber($done_cart_value, $user_id, $area_id, $area_city, [$flow_type]);

                        $this->cartCommonService->getFlowCartStock($_cart_goods_stock, $store_id, $warehouse_id, $area_id, $area_city);

                    } catch (HttpException $httpException) {
                        return show_message($httpException->getMessage(), null, '', 'error');
                    }
                }
                /* 检查商品库存 end */

                /* 订单队列 先进先出 */
                $time = TimeRepository::getGmTime();
                DscEncryptRepository::SolveDealInsert($user_id, $done_cart_value, $time);

                $order_fifo = DscEncryptRepository::order_fifo($user_id, $done_cart_value, $time);
                if ($order_fifo['error'] == 1) {
                    return show_message(lang('flow.flow_salve_error'), lang('flow.back_checkout'), 'flow.php?step=checkout');
                } elseif ($order_fifo['error'] == 2) {
                    return show_message(sprintf(lang('shopping_flow.stock_insufficiency'), $order_fifo['msg'], $order_fifo['number'], $order_fifo['number']), lang('flow.again_select_goods'), 'flow.php');
                }

                /*
                 * 检查用户是否已经登录
                 * 如果用户已经登录了则检查是否有默认的收货地址
                 * 如果没有登录则跳转到登录和注册页面
                 */
                if (empty(session('direct_shopping')) && session('user_id') == 0) {
                    /* 用户没有登录且没有选定匿名购物，转向到登录页面 */
                    return dsc_header("Location: user.php\n");
                }

                $consignee = $this->flowUserService->getConsignee(session('user_id'));
                /* 检查收货人信息是否完整 */
                if (!$this->flowUserService->checkConsigneeInfo($consignee, $flow_type) && $store_id == 0) {
                    /* 如果不完整则转向到收货人信息填写界面 */
                    return dsc_header("Location: user_address.php?act=address_list\n"); //by wu
                }
                $how_oos = (int)request()->input('how_oos', 0);
                $card_message = compile_str(request()->input('card_message', ''));
                $inv_payee = trim(compile_str(request()->input('inv_payee', '')));
                $tax_id = trim(compile_str(request()->input('tax_id', '')));
                $inv_content = trim(compile_str(request()->input('inv_content', '')));
                //插入订单信息 ecmoban模板堂 --zhuo

                $post_shipping = addslashes_deep(request()->input('shipping', []));

                $shipping_code = addslashes_deep(request()->input('shipping_code', []));
                $shipping_type = addslashes_deep(request()->input('shipping_type', []));
                $post_ru_id = addslashes_deep(request()->input('ru_id', []));

                // 订单 分单配送方式
                $shipping = [];
                if ($post_shipping && count($post_ru_id) == 1) {
                    $shipping['shipping_id'] = $post_shipping['0'] ?? 0;
                    $shipping['shipping_type'] = $shipping_type['0'] ?? 0;
                    $shipping['shipping_code'] = $shipping_code['0'] ?? 0;
                } else {
                    $shipping = FlowRepository::get_order_post_shipping($post_shipping, $shipping_code, $shipping_type, $post_ru_id);
                }

                // 分单买家留言
                $postscript = request()->input('postscript', '');
                if ($postscript && count($post_ru_id) == 1) {
                    $postscript = array_values($postscript);
                    $postscript = $postscript['0'] ?? '';
                } else {
                    $postscript = FlowRepository::get_order_post_postscript($postscript, $post_ru_id);
                }

                // 自提点
                $point_id = (int)request()->input('point_id', 0);
                $shipping_dateStr = e(request()->input('shipping_dateStr', ''));

                $order = [
                    'shipping_id' => isset($shipping['shipping_id']) ? $shipping['shipping_id'] : 0, //ecmoban模板堂 --zhuo
                    'shipping_type' => isset($shipping['shipping_type']) ? $shipping['shipping_type'] : 0, //ecmoban模板堂 --zhuo
                    'pay_id' => (int)request()->input('payment', 0),
                    'pay_code' => addslashes(trim(request()->input('pay_code', ''))),
                    'pack_id' => (int)request()->input('pack', 0),
                    'card_id' => (int)request()->input('card', 0),
                    'card_message' => trim($card_message),
                    'surplus' => floatval(request()->input('surplus', 0.00)),
                    'integral' => (int)request()->input('integral', 0),
                    'bonus_id' => (int)request()->input('bonus', 0),
                    'uc_id' => request()->input('uc_id', 0), //优惠券id bylu
                    'not_freightfree' => (int)request()->input('not_freightfree', 0), //优惠券是否含有地区免邮条件  0：支持 1：不支持
                    'vc_id' => (int)request()->input('vc_id', 0), //储值卡ID
                    'need_inv' => isset($_POST['inv_payee']) ? 1 : 0,
                    'inv_type' => config('shop.invoice_type')['type'][0] ?? '',
                    'inv_payee' => $inv_payee,
                    'tax_id' => $tax_id, //纳税人识别号
                    'inv_content' => $inv_content,
                    'postscript' => $postscript ?? '',
                    'how_oos' => isset($GLOBALS['_LANG']['oos'][$how_oos]) ? addslashes($GLOBALS['_LANG']['oos'][$how_oos]) : '',
                    'need_insure' => (int)request()->input('need_insure', 0),
                    'user_id' => session('user_id'),
                    'add_time' => $time,
                    'order_status' => OS_CONFIRMED,
                    'shipping_status' => SS_UNSHIPPED,
                    'mobile' => addslashes(trim(request()->input('store_mobile', ''))),
                    'pay_status' => PS_UNPAYED,
                    'agency_id' => !empty($consignee['province']) && !empty($consignee['city']) ? get_agency_by_regions([$consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']]) : '0',
                    'point_id' => $point_id,
                    'shipping_date_str' => $shipping_dateStr,
                    'invoice_type' => (int)request()->input('invoice_type', 0),
                ];

                $order['uc_id'] = DscEncryptRepository::filterValInt($order['uc_id']);

                if (CROSS_BORDER === true) { // 跨境多商户
                    $order['rel_name'] = addslashes(trim(request()->input('rel_name', '')));
                    $order['id_num'] = addslashes(trim(request()->input('id_num', '')));

                    if (!empty($order['rel_name']) && !empty($order['id_num'])) {
                        // 实名认证验证
                        $cbecService = app(CrossBorderService::class)->cbecExists();
                        // 保存实名信息
                        $real_data = [
                            'rel_name' => $order['rel_name'],
                            'id_num' => $order['id_num'],
                        ];
                        // 开启验证
                        $config['identity_auth_status'] = config('shop.identity_auth_status') ?? 0;
                        if ($config['identity_auth_status'] == 1) {
                            // 未通过或编辑时必验证
                            $check = false;
                            // 判断修改实名信息时
                            if (empty($consignee['rel_name']) || empty($consignee['id_num']) || $consignee['rel_name'] != $order['rel_name'] || $consignee['id_num'] != $order['id_num']) {
                                $check = true;
                            }

                            // 验证
                            if ((isset($consignee['real_status']) && $consignee['real_status'] == 0) || $check == true) {
                                // 请求接口
                                $res = $cbecService->checkIdentity($order['rel_name'], $order['id_num']);
                                if ($res == true) {
                                    // 保存为已实名认证
                                    $real_data['real_status'] = 1;
                                } else {
                                    // 实名认证信息不匹配
                                    $real_data['real_status'] = 0;
                                    $cbecService->updateUserAddress($user_id, $consignee['address_id'], $real_data);
                                    return show_message($GLOBALS['_LANG']['user_real_info_error'], $GLOBALS['_LANG']['back'], '', 'error');
                                }
                            }
                        }
                        // 保存实名信息与状态
                        $cbecService->updateUserAddress($user_id, $consignee['address_id'], $real_data);
                        // 保存订单
                        $consignee['rel_name'] = $order['rel_name'];
                        $consignee['id_num'] = $order['id_num'];
                    }
                }

                if (file_exists(MOBILE_GROUPBUY) && !empty(config('shop.open_community_post'))) {
                    $leader_id = $consignee['leader_id'] ?? 0;
                    if (!empty($leader_id)) {
                        $order['use_community_post'] = 1; // 订单使用社区驿站
                        $order['leader_id'] = $leader_id; //社区驿站的团长ID
                    }
                }

                $cart_goods_list = cart_goods($flow_type, $done_cart_value, 1); // 取得商品列表，计算合计

                /* 订单中的商品 */
                $cart_goods = $this->dscRepository->turnPluckFlattenOne($cart_goods_list);
                $cart_goods = $this->cartCommonService->shippingCartGoodsList($cart_goods);

                $user_info = Users::select('user_id', 'user_name', 'nick_name', 'pay_points', 'user_money', 'credit_line', 'email', 'mobile_phone')->where('user_id', $user_id);
                $user_info = BaseRepository::getToArrayFirst($user_info);
                /* 检查积分余额是否合法 */
                if ($user_id > 0) {
                    $order['surplus'] = min($order['surplus'], $user_info['user_money'] + $user_info['credit_line']);
                    if ($order['surplus'] < 0) {
                        $order['surplus'] = 0;
                    }

                    // 查询用户有多少积分
                    $flow_points = $this->flowActivityService->getFlowAvailablePoints($cart_goods);  // 该订单允许使用的积分
                    $user_points = $user_info['pay_points']; // 用户的积分总数

                    $order['integral'] = min($order['integral'], $user_points, $flow_points);
                    if ($order['integral'] < 0) {
                        $order['integral'] = 0;
                    }
                } else {
                    $order['surplus'] = 0;
                    $order['integral'] = 0;
                }

                $all_ru_id = BaseRepository::getKeyPluck($cart_goods, 'ru_id');

                if (empty($cart_goods)) {
                    return show_message($GLOBALS['_LANG']['no_goods_in_cart'], $GLOBALS['_LANG']['back_home'], './', 'warning');
                }

                /* 订单商品总额 */
                $cart_total = BaseRepository::getArraySum($cart_goods, ['goods_price', 'goods_number']);

                /* 检查优惠券是否存在 */
                if (!empty($order['uc_id'])) {
                    $couponsUserList = $this->couponsUserService->getCouponsUserSerList($order['uc_id'], $user_id, $cart_goods);

                    $sql = [
                        'where' => [
                            [
                                'name' => 'is_use',
                                'value' => 0
                            ]
                        ]
                    ];
                    $couponsUserList = BaseRepository::getArraySqlGet($couponsUserList, $sql);
                    $order['uc_id'] = BaseRepository::getKeyPluck($couponsUserList, 'uc_id');
                }

                $order['uc_id'] = !empty($order['uc_id']) ? BaseRepository::getImplode($order['uc_id']) : '';

                /* 检查红包是否存在 */
                if ($order['bonus_id'] > 0) {
                    $bonus = bonus_info($order['bonus_id']);
                    if (empty($bonus) || $bonus['user_id'] != $user_id || $bonus['order_id'] > 0 || $bonus['min_goods_amount'] > $cart_total) {
                        $order['bonus_id'] = 0;
                    }
                } elseif (request()->exists('bonus_psd')) {
                    $bonus_psd = trim(request()->input('bonus_psd'));

                    $bonus = bonus_info(0, $bonus_psd);

                    if (empty($bonus) || $bonus['user_id'] > 0 || $bonus['order_id'] > 0 || $bonus['min_goods_amount'] > $cart_total || $time > $bonus['use_end_date']) {
                    } else {
                        if ($user_id > 0) {
                            UserBonus::where('bonus_id', $bonus['bonus_id'])->update(['user_id' => $user_id]);
                        }
                        $order['bonus_id'] = $bonus['bonus_id'];
                        $order['bonus_psd'] = $bonus_psd;
                    }
                }

                /* 检查储值卡ID是否存在 */
                if ($order['vc_id'] > 0) {
                    $value_card = $this->valueCardService->orderValueCardInfo($order['vc_id'], '', $user_id);

                    if (empty($value_card) || $value_card['user_id'] != $user_id) {
                        $order['vc_id'] = 0;
                    }
                } elseif (request()->exists('value_card_psd')) {
                    $value_card_psd = trim(request()->input('value_card_psd'));
                    $value_card = $this->valueCardService->orderValueCardInfo(0, $value_card_psd);
                    if (!(empty($value_card) || $value_card['user_id'] > 0)) {
                        if ($user_id > 0 && empty($value_card['end_time'])) {
                            $end_time = TimeRepository::getLocalStrtoTime("+" . $value_card['vc_indate'] . " months ");
                            $other = [
                                'user_id' => $user_id,
                                'bind_time' => $time,
                                'end_time' => $end_time
                            ];
                            ValueCard::where('vid', $value_card['vid'])->update($other);

                            $order['vc_id'] = $value_card['vid'];
                            $order['vc_psd'] = $value_card_psd;
                        } elseif ($time > $value_card['end_time']) {
                            $order['vc_id'] = 0;
                        }
                    }
                }

                /* 记录扩展信息 */
                if ($flow_type != CART_GENERAL_GOODS && $flow_type != CART_ONESTEP_GOODS) {

                    $order['extension_code'] = session('extension_code', '');
                    $order['extension_id'] = session('extension_id', 0);
                    if (count($cart_goods) == 1) {
                        $extensionList = BaseRepository::getColumn($cart_goods, 'extension_code', 'goods_id');
                        $extension_id = BaseRepository::getArrayKeys($extensionList);
                        $extension_id = BaseRepository::getImplode($extension_id);
                        $goods_extension_code = BaseRepository::getImplode($extensionList);

                        if ($goods_extension_code == 'package_buy') {
                            $order['extension_code'] = $goods_extension_code;
                            $order['extension_id'] = $extension_id;
                        }
                    }
                } else {
                    $order['extension_code'] = '';
                    $order['extension_id'] = 0;
                }

                /* 检查商品总额是否达到最低限购金额 */
                if (($flow_type == CART_GENERAL_GOODS || $flow_type == CART_ONESTEP_GOODS) && $cart_total < config('shop.min_goods_amount')) {
                    return show_message(sprintf($GLOBALS['_LANG']['goods_amount_not_enough'], $this->dscRepository->getPriceFormat(config('shop.min_goods_amount'), false)));
                }

                /* 收货人信息 */
                foreach ($consignee as $key => $value) {
                    if ($key == 'mobile' && !empty($order['mobile'])) {
                        $order[$key] = $order['mobile'];  //门店取货手机号
                    } else {
                        $order[$key] = addslashes_deep($value);
                    }
                }

                $order['email'] = !empty($order['email']) ? $order['email'] : ($user_info['email'] ?? '');

                $ruShipping = BaseRepository::getArrayCombine($post_ru_id, $post_shipping);

                //切换配送方式
                if ($cart_goods_list) {
                    foreach ($cart_goods_list as $key => $val) {
                        $cart_goods_list[$key]['tmp_shipping_id'] = $ruShipping[$val['ru_id']] ?? 0;
                    }
                }

                // 上门取货 有自提点 免运费
                $store_type = !empty($point_id) ? 1 : 0;

                /* 订单中的总额 */
                $total = order_fee($order, $cart_goods, $consignee, $done_cart_value, $cart_goods_list, $store_id, $store_type, $user_id, 0, $flow_type);

                if (CROSS_BORDER === true) { // 跨境多商户
                    $web = app(CrossBorderService::class)->webExists();

                    if (!empty($web)) {
                        $arr = [
                            'consignee' => $consignee ?? '',
                            'rec_type' => $flow_type ?? 0,
                            'store_id' => $store_id ?? 0,
                            'cart_value' => $cart_value ?? '',
                            'type' => $type ?? 1,
                            'uc_id' => $order['uc_id'] ?? 0
                        ];
                        $amount = $web->assignNewRatePrice($cart_goods_list, $total['amount'], $arr);
                        $total['amount'] = $amount['amount'];
                        $total['amount_formated'] = $amount['amount_formated'];
                        $order['rate_fee'] = $amount['rate_price'];
                        $order['format_rate_fee'] = $amount['format_rate_fee'];
                        $rate = $amount;
                    } else {
                        return show_message('service not exists');
                    }
                }
                $order['bonus'] = $total['bonus'] ?? 0;
                $order['coupons'] = $total['coupons'] ?? 0; //优惠券金额
                $order['use_value_card'] = $total['use_value_card'] ?? 0; //储值卡使用金额
                $order['vc_rec_list'] = $total['vc_rec_list'] ?? []; //储值卡支持的购物商品
                $order['vc_dis_money'] = $total['vc_dis_money'] ?? 0; //储值卡使用折扣金额
                $order['goods_amount'] = $total['goods_price'] ?? 0;
                $order['cost_amount'] = $total['cost_price'] ?? 0;
                $order['discount'] = $total['discount'] ?? 0;
                $order['surplus'] = $total['surplus'] ?? 0;
                $order['tax'] = $total['tax'] ?? 0;
                $order['dis_amount'] = $total['dis_amount'] ?? 0;
                $order['ru_shipping_fee_list'] = $total['ru_shipping_fee_list'] ?? [];

                // 购物车中的商品能享受红包支付的总额
                $discount_amout = compute_discount_amount($done_cart_value);
                // 红包和积分最多能支付的金额为商品总额
                $temp_amout = $order['goods_amount'] - $discount_amout;
                if ($temp_amout <= 0) {
                    $order['bonus_id'] = 0;
                }

                /* 配送方式 ecmoban模板堂 --zhuo */
                if (!empty($order['shipping_id'])) {
                    if (count($post_shipping) == 1) {
                        $shipping = shipping_info($order['shipping_id']);
                    }

                    $order['shipping_code'] = isset($shipping['shipping_code']) ? addslashes($shipping['shipping_code']) : '';

                    $order['shipping_isarr'] = 0;
                    $order['shipping_name'] = addslashes($shipping['shipping_name']);
                    $shipping_name = !empty($order['shipping_name']) ? explode(",", $order['shipping_name']) : '';
                    if ($shipping_name && count($shipping_name) > 1) {
                        $order['shipping_isarr'] = 1;
                    }
                }

                $order['shipping_fee'] = $total['shipping_fee'];
                $order['insure_fee'] = $total['shipping_insure'];

                // 必段选择支付方式
                if (empty($order['pay_id'])) {
                    return show_message(lang('flow.please_checked_pay'), $GLOBALS['_LANG']['back'], 'flow.php?step=checkout', 'error');
                }

                $payment = [];
                if ($order['pay_id'] > 0) {
                    $payment = payment_info($order['pay_id']);
                    $order['pay_name'] = addslashes($payment['pay_name']);
                    $order['pay_code'] = addslashes($payment['pay_code']);
                }

                // 检查有没有用户有开启支付密码
                $pay_pwd = addslashes(trim(request()->input('pay_pwd', '')));
                $pay_pwd_error = (int)request()->input('pay_pwd_error', 0);

                // 开启配置支付密码 且使用余额 或 使用储值卡、余额支付 验证支付密码
                if (config('shop.use_paypwd') == 1 && ($order['surplus'] > 0 || $order['vc_id'] > 0 || ($payment && $payment['pay_code'] == 'balance'))) {
                    try {
                        $this->flowService->check_user_paypwd($user_id, $pay_pwd, $pay_pwd_error);
                    } catch (HttpException $httpException) {
                        $back_url = $httpException->getCode() == 1 ? 'user.php?act=account_safe' : '';
                        return show_message($httpException->getMessage(), $GLOBALS['_LANG']['back'], $back_url, 'error');
                    }
                }

                $order['pay_fee'] = $total['pay_fee'];
                $order['cod_fee'] = $total['cod_fee'];

                /* 商品包装 */
                if ($order['pack_id'] > 0) {
                    $pack = pack_info($order['pack_id']);
                    $order['pack_name'] = addslashes($pack['pack_name']);
                }
                $order['pack_fee'] = $total['pack_fee'];

                /* 祝福贺卡 */
                if ($order['card_id'] > 0) {
                    $card = card_info($order['card_id']);
                    $order['card_name'] = addslashes($card['card_name']);
                }
                $order['card_fee'] = $total['card_fee'];

                $order['order_amount'] = number_format($total['amount'], 2, '.', '');

                //ecmoban模板堂 --zhuo
                if (session()->has('direct_shopping') && !empty(session('direct_shopping'))) {
                    $where_flow = "?step=checkout&direct_shopping=" . session('direct_shopping');
                }

                $snapshot = false;
                /* 如果全部使用余额支付，检查余额是否足够 */
                if ($payment['pay_code'] == 'balance' && $order['order_amount'] > 0) {
                    if ($order['surplus'] > 0) { //余额支付里如果输入了一个金额
                        $order['order_amount'] = $order['order_amount'] + $order['surplus'];
                        $order['surplus'] = 0;
                    }
                    if ($order['order_amount'] > ($user_info['user_money'] + $user_info['credit_line'])) {
                        $location = "flow.php";
                        if ($flow_type == CART_PRESALE_GOODS) {
                            $location = "presale.php";
                            $where_flow = "?id=" . $order['extension_id'] . "&act=view";
                        } elseif ($flow_type == CART_GROUP_BUY_GOODS) {
                            $location = "group_buy.php";
                            $where_flow = "?act=view&id=" . $order['extension_id'];
                        }
                        return show_message($GLOBALS['_LANG']['balance_not_enough'], $GLOBALS['_LANG']['back_up_page'], $location . $where_flow);
                    } else {
                        if ($flow_type && ($flow_type == CART_PRESALE_GOODS || $flow_type == CART_GROUP_BUY_GOODS)) {
                            //余额支付 预售、团购-- 首次付定金
                            $order['surplus'] = $order['order_amount'];
                            $order['pay_status'] = PS_PAYED_PART; //部分付款
                            $order['order_status'] = OS_CONFIRMED; //已确认
                            $order['order_amount'] = $order['goods_amount'] + $order['shipping_fee'] + $order['insure_fee'] + $order['tax'] - $order['discount'] - $order['surplus'];
                        } else {
                            $order['surplus'] = $order['order_amount'];
                            $order['order_amount'] = 0;
                        }
                    }
                }

                $stores_sms = 0;//门店提货码是否发送信息 0不发送  1发送

                /* 如果订单金额为0（使用余额或积分或红包支付），修改订单状态为已确认、已付款 */
                if ($order['order_amount'] <= 0) {
                    $order['order_status'] = OS_CONFIRMED;
                    $order['confirm_time'] = $time;
                    $order['pay_status'] = PS_PAYED;
                    $order['pay_time'] = $time;
                    $order['order_amount'] = 0;
                    $stores_sms = 1;

                    $snapshot = true;
                }

                /* 预售订单已付款且金额为0, 使用储值卡付款、使用余额付款 首次付定金, 预售状态更改成部分付款*/
                if ($order['order_amount'] <= 0 && $flow_type == CART_PRESALE_GOODS && (!empty($order['use_value_card']) || $order['surplus'] > 0)) {
                    $order['pay_status'] = PS_PAYED_PART;
                    $order['order_amount'] = $order['goods_amount'] + $order['shipping_fee'] + $order['insure_fee'] + $order['tax'] - $order['discount'] - $order['surplus'] - $order['use_value_card'];
                }

                $order['integral_money'] = $total['integral_money'];
                $order['integral'] = $total['integral'];

                if ($order['extension_code'] == 'exchange_goods') {
                    $order['integral_money'] = $this->dscRepository->valueOfIntegral($total['exchange_integral']);
                    $order['integral'] = $total['exchange_integral'];
                    $order['goods_amount'] = 0;
                }

                $order['from_ad'] = session('from_ad', 0);
                $order['referer'] = !empty(session('referer')) ? addslashes(session('referer')) : '';

                $affiliate = config('shop.affiliate') ? unserialize(config('shop.affiliate')) : [];
                if (isset($affiliate['on']) && $affiliate['on'] == 1 && $affiliate['config']['separate_by'] == 1) {
                    //推荐订单分成
                    $parent_id = CommonRepository::getUserAffiliate();
                    if ($user_id == $parent_id) {
                        $parent_id = 0;
                    }
                } elseif (isset($affiliate['on']) && $affiliate['on'] == 1 && $affiliate['config']['separate_by'] == 0) {
                    //推荐注册分成
                    $parent_id = 0;
                } else {
                    //分成功能关闭
                    $parent_id = 0;
                }

                // 微分销
                $is_distribution = 0;
                if (file_exists(MOBILE_DRP) && $order['extension_code'] == '') {
                    // 订单分销条件
                    $affiliate_drp_id = CommonRepository::getDrpAffiliate();
                    $result = app(\App\Modules\Drp\Services\Drp\DrpService::class)->orderAffiliate($user_id, $affiliate_drp_id);

                    // 是否分销
                    $is_distribution = $result['is_distribution'] ?? 0;
                    $parent_id = $result['parent_id'] ?? 0; // 推荐人id
                    // 是否分销订单
                    $order['is_drp'] = $is_distribution;
                }

                $order['parent_id'] = $parent_id;

                $new_order_id = 0;
                $cartValue = !empty($done_cart_value) ? $done_cart_value : 0;

                //获取新订单号
                $order['order_sn'] = get_order_sn();
                if ($cartValue) {
                    $new_order_id = $this->flowOrderService->AddToOrder($order, $cartValue);

                    $cartWhere = [
                        'user_id' => $user_id,
                        'order_id' => $new_order_id,
                        'is_distribution' => $is_distribution
                    ];

                    if (CROSS_BORDER === true) { // 跨境多商户
                        $cbec = app(CrossBorderService::class)->cbecExists();

                        if (!empty($cbec)) {
                            $cartWhere['rate_arr'] = isset($rate) && isset($rate['rate_arr']) ? $rate['rate_arr'] : [];
                        }
                    }

                    $order['dis_amount_list'] = BaseRepository::getColumn($cart_goods, 'dis_amount', 'rec_id');

                    $rec_list = $this->flowOrderService->AddToOrderGoods($cartWhere, $cart_goods, $order);
                }

                if (empty($rec_list)) {
                    return show_message('service not exists', $GLOBALS['_LANG']['back_to_cart'], 'flow.php');
                }

                $order['order_id'] = $new_order_id;

                $this->jigonManageService->pushJigonOrderGoods($cart_goods, $order); //推送贡云订单

                /* 插入支付日志 */
                if ($new_order_id > 0) {
                    $order['log_id'] = insert_pay_log($new_order_id, $order['order_amount'], PAY_ORDER);
                }

                $good_ru_id = addslashes_deep(request()->input('ru_id', []));

                if (!is_array($good_ru_id) && $good_ru_id == 0) {
                    $good_ru_id = explode(',', $good_ru_id);
                } else {
                    $good_ru_id = BaseRepository::getExplode($good_ru_id);
                }

                if (count($good_ru_id) > 0 && $new_order_id > 0 && $store_id > 0) {
                    $take_time = addslashes(trim(request()->input('take_time', '')));

                    foreach ($good_ru_id as $v) {
                        if ($stores_sms != 1) {
                            $pick_code = '';
                        } else {
                            $pick_code = substr($order['order_sn'], -3) . mt_rand(100, 999);
                        }

                        $other = [
                            'order_id' => $new_order_id,
                            'store_id' => $store_id,
                            'ru_id' => $v,
                            'pick_code' => $pick_code,
                            'take_time' => $take_time
                        ];
                        StoreOrder::insert($other);
                    }
                }

                /* 修改拍卖活动状态 */
                if ($new_order_id > 0 && $order['extension_code'] == 'auction') {
                    $is_finished = 2; //完成状态默认为2(已完成已处理);

                    $activity_ext_info = GoodsActivity::where('act_id', $order['extension_id'])->value('ext_info');

                    //判断是否存在保证金
                    if ($activity_ext_info) {
                        $activity_ext_info = unserialize($activity_ext_info);
                        //存在保证金状态为1（已完成未处理）
                        if ($activity_ext_info['deposit'] > 0) {
                            $is_finished = 1;
                        }
                    }

                    GoodsActivity::where('act_id', $order['extension_id'])->update(['is_finished' => $is_finished]);
                }

                /* 处理余额、积分、红包 */
                if ($new_order_id > 0 && $order['user_id'] > 0 && ($order['surplus'] > 0 || $order['integral'] > 0)) {
                    if ($order['surplus'] > 0) {
                        $order_surplus = $order['surplus'] * (-1);
                    } else {
                        $order_surplus = 0;
                    }

                    if ($order['integral'] > 0) {
                        $order_integral = $order['integral'] * (-1);
                    } else {
                        $order_integral = 0;
                    }

                    log_account_change($order['user_id'], $order_surplus, 0, 0, $order_integral, sprintf($GLOBALS['_LANG']['pay_order'], $order['order_sn']));
                }

                if ($new_order_id > 0 && $order['bonus_id'] > 0 && $temp_amout > 0) {
                    $this->flowOrderService->useBonus($order['bonus_id'], $new_order_id);
                }

                /* 处理储值卡 */
                if ($new_order_id > 0 && $order['vc_id'] > 0) {
                    $this->flowOrderService->useValueCard($order['vc_id'], $new_order_id, $order['order_sn'], $order['use_value_card'], $order['vc_dis_money']);
                }

                /* 记录优惠券使用 bylu */
                if ($new_order_id > 0 && !empty($order['uc_id'])) {
                    $this->flowOrderService->orderUseCoupons($order['uc_id'], $new_order_id, $user_id);
                }

                // 收银台代码 调用事件 门店非分单 已付款 推单
                if (count($good_ru_id) == 1 && $order['order_amount'] <= 0) {
                    event(new PickupOrdersPayedAfterDoSomething($order));
                }

                /** 如果使用库存，且下订单时减库存，则减少库存 */
                if (config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_PLACE) {
                    change_order_goods_storage($new_order_id, true, SDT_PLACE, 1, 0, $store_id);
                }

                /* 如果使用库存，且付款时减库存，且订单金额为0，则减少库存 */
                if ($new_order_id > 0 && config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_PAID && $order['order_amount'] <= 0) {
                    change_order_goods_storage($new_order_id, true, SDT_PAID, 15, 0, $store_id);
                }

                /* 取得支付信息，生成支付代码 */
                if ($new_order_id > 0 && $order['order_amount'] > 0) {

                    /*  @author-bylu 白条分期信息 start */
                    /* 分期数据插入白条分期表 by lu */
                    $cart_info = OrderGoods::select('stages_qishu', 'goods_id', 'goods_number')
                        ->where('order_id', $new_order_id);
                    $cart_info = BaseRepository::getToArrayFirst($cart_info);

                    //如果当前商品是白条分期商品; -1表示普通商品;
                    if (isset($cart_info['stages_qishu']) && $cart_info['stages_qishu'] > 0) {

                        //获取到当前商品的费率;
                        $stages_rate = Goods::where('goods_id', $cart_info['goods_id'])->value('stages_rate');
                        $stages_rate = $stages_rate ? $stages_rate : 0;

                        $order_sn = $order['order_sn']; //订单编号;
                        $stages_qishu = $cart_info['stages_qishu']; //选择的期数;
                        $goods_number = $cart_info['goods_number']; //购买的数量;

                        //获取该白条分期订单的总价;
                        $shop_price_total = $order['order_amount'];
                        //计算每期价格(每期价格=总价*费率+总价/期数);
                        $stages_one_price = round(($shop_price_total * ($stages_rate / 100)) + ($shop_price_total / $stages_qishu), 2);

                        //计算还款日期;
                        $repay_datee = [];
                        if ($stages_qishu == 1) {
                            //这里是30天免息,还款日期直接就是下个月的今天,且不计算费率;
                            $repay_datee[1] = TimeRepository::getLocalDate('Y-m-d', local_strtotime("+1 month"));
                            $stages_one_price = round($shop_price_total, 2); //30天免息,还款金额直接为应付总价;
                        } else {
                            //检查会员白条余额是否足够
                            $bt_other = [
                                'user_id' => $user_id
                            ];
                            $bt_info = $this->userBaitiaoService->getBaitiaoInfo($bt_other);

                            $repay_term = $bt_info['repay_term'] ?? 0;

                            for ($i = 1; $i <= $stages_qishu; $i++) {
                                $value = $i * $repay_term;
                                $repay_datee[$i] = TimeRepository::getLocalDate('Y-m-d', local_strtotime("+$value day"));
                            }
                        }

                        $repay_date = empty($repay_datee) ? [] : serialize($repay_datee);//还款日期;

                        //将数据插入 白条分期表;
                        $stages_other = [
                            'order_sn' => $order_sn,
                            'stages_total' => $stages_qishu,
                            'stages_one_price' => $stages_one_price,
                            'yes_num' => 0,
                            'create_date' => TimeRepository::getGmTime(),
                            'repay_date' => $repay_date,
                            'stages_rate' => $stages_rate
                        ];
                        Stages::insert($stages_other);

                        //将白条分期付款信息注入到前台显示;
                        $stages_intro = [
                            'stages_qishu' => $stages_qishu,
                            'goods_number' => $goods_number,
                            'stages_one_price' => $stages_one_price,
                            'repay_date' => $repay_datee[1],
                            'baitiao' => !empty($baitiao_balance['balance']) ? $baitiao_balance['balance'] : 0
                        ];

                        if ($stages_intro) {
                            //白条分期信息
                            session()->put('stages_intro.' . $user_id . $new_order_id, $stages_intro);
                        }
                    }
                    /*  @author-bylu 白条分期信息 end */
                }

                /*如果是门店商品*/
                if ($store_id > 0) {
                    $stores_info = OfflineStore::where('id', $store_id);
                    $stores_info = BaseRepository::getToArrayFirst($stores_info);
                    $this->smarty->assign('stores_info', $stores_info);
                }

                if (!empty($order['shipping_name'])) {
                    $order['shipping_name'] = trim(stripcslashes($order['shipping_name']));
                }

                if (session()->has('direct_shopping')) {
                    $this->smarty->assign('direct_shopping', session('direct_shopping'));
                }

                //处理价格显示
                $order['format_shipping_fee'] = $this->dscRepository->getPriceFormat($order['shipping_fee']);
                $order['format_order_amount'] = $this->dscRepository->getPriceFormat($order['order_amount']);

                /* 订单信息 */
                $this->smarty->assign('order', $order);
                $this->smarty->assign('total', $total);
                $this->smarty->assign('order_submit_back', sprintf(lang('flow.order_submit_back'), lang('flow.flow_back_home'), lang('flow.goto_user_center'))); // 返回提示

                /* 清除缓存，否则买了商品，但是前台页面读取缓存，商品数量不减少 */
                clear_all_files();

                session()->forget('flow_consignee');
                session()->forget('flow_order');
                session()->forget('direct_shopping');

                //订单分子订单 分单 start
                $order_id = $order['order_id'];

                //获取订单是否存在商家无白条支付权限 staert
                $seller_grade = 1;
                if ($all_ru_id) {
                    if (count($all_ru_id) > 1) {
                        $is_payment = get_payment_code(); //是否安装白条支付
                        if ($is_payment) {
                            $sg_ru_id = get_array_flip(0, $all_ru_id);
                            $seller_grade = get_seller_grade($sg_ru_id, 1);
                        }

                        $this->smarty->assign('seller_grade', $seller_grade);
                    } else {
                        if ($all_ru_id[0] == 0) {
                            $this->smarty->assign('seller_grade', $seller_grade);
                        } else {
                            $is_payment = get_payment_code(); //是否安装白条支付
                            if ($is_payment) {
                                $seller_grade = get_seller_grade($all_ru_id, 1);
                            }

                            $this->smarty->assign('seller_grade', $seller_grade);
                        }
                    }
                }
                //获取订单是否存在商家无白条支付权限 end

                $ru_number = count($all_ru_id);

                $userOrderNumCount = UserOrderNum::where('user_id', $user_id)->count();
                if ($userOrderNumCount == 0) {
                    UserOrderNum::insert([
                        'user_id' => $user_id
                    ]);
                }

                /* 更新会员订单信息 */
                //兼容货到付款订单放到未收货订单中
                if ($order['order_amount'] <= 0 || $payment['pay_code'] == 'cod') {
                    $dbRaw = [
                        'order_all_num' => "order_all_num + " . $ru_number,
                        'order_nogoods' => "order_nogoods + " . $ru_number
                    ];
                    $dbRaw = BaseRepository::getDbRaw($dbRaw);

                    UserOrderNum::where('user_id', $user_id)->update($dbRaw);
                } else {
                    $dbRaw = [
                        'order_all_num' => "order_all_num + " . $ru_number,
                        'order_nopay' => "order_nopay + " . $ru_number
                    ];
                    $dbRaw = BaseRepository::getDbRaw($dbRaw);

                    UserOrderNum::where('user_id', $user_id)->update($dbRaw);
                }

                if ($order_id > 0 && $ru_number > 1) {

                    if ($order['order_amount'] <= 0 || $payment['pay_code'] == 'cod' || $payment['pay_code'] == 'bank') {
                        /* 队列分单 */
                        $filter = [
                            'order_id' => $order_id,
                            'user_id' => $user_id
                        ];
                        ProcessSeparateBuyOrder::dispatch($filter);
                    }

                    $main_pay = 1;
                    if ($order['order_amount'] <= 0) {
                        $main_pay = 2;
                    } elseif ($payment['pay_code'] == 'cod' || $payment['pay_code'] == 'bank') {
                        $main_pay = 0;
                    }

                    $updateOrder = [
                        'main_count' => $ru_number, //更新主订单商品标识
                        'main_pay' => $main_pay
                    ];

                    $child_order_info = $this->flowOrderService->getChildOrderInfo($order['order_id']);
                } else {
                    $updateOrder = [
                        'ru_id' => $all_ru_id[0] ?? 0
                    ];

                    $child_order_info = [];
                }
                $this->smarty->assign('child_order_info', $child_order_info);

                OrderInfo::where('order_id', $new_order_id)->where('user_id', $user_id)->update($updateOrder);
                //订单分子订单 分单 end

                // 事件监听扩展参数
                $extendParam = [];

                if ($new_order_id > 0) {
                    //门店发送短信
                    if ($stores_sms == 1 && $store_id > 0) {
                        /* 门店下单时未填写手机号码 则用会员绑定号码 */
                        if ($order['mobile']) {
                            $user_mobile_phone = $order['mobile'];
                        } else {
                            $user_mobile_phone = Users::where('user_id', $user_id)->value('mobile_phone');
                        }

                        //门店订单->短信接口参数
                        $store_smsParams = [
                            'user_name' => $user_name,
                            'username' => $user_name,
                            'order_sn' => $order['order_sn'],
                            'ordersn' => $order['order_sn'],
                            'code' => $pick_code ?? ''
                        ];

                        $this->commonRepository->smsSend($user_mobile_phone, $store_smsParams, 'store_order_code');
                    }

                    //对单商家下单
                    if ($order_id > 0 && count($all_ru_id) == 1) {
                        /* 如果需要，发短信 */
                        $sellerId = $all_ru_id[0] ?? 0;
                        //商家客服手机号获取
                        $seller_shopinfo = SellerShopinfo::where('ru_id', $sellerId)->select('mobile', 'seller_email');
                        $seller_shopinfo = BaseRepository::getToArrayFirst($seller_shopinfo);

                        $sms_shop_mobile = $seller_shopinfo['mobile'] ?? '';

                        // 下单或付款发短信
                        if (!empty($sms_shop_mobile) && (config('shop.sms_order_placed') == 1 || config('shop.sms_order_payed') == 1)) {
                            //是否开启下单自动发短信、邮件 by wu start
                            $auto_sms = $this->cronService->getSmsOpen();
                            if (!empty($auto_sms)) {
                                $other = [
                                    'item_type' => 1,
                                    'user_id' => $order['user_id'],
                                    'ru_id' => $sellerId,
                                    'order_id' => $order['order_id'],
                                    'add_time' => TimeRepository::getGmTime()
                                ];
                                AutoSms::insert($other);
                            } else {
                                $shop_name = $this->merchantCommonService->getShopName($sellerId, 1);
                                $order_region = $this->orderService->getOrderUserRegion($order_id);
                                //普通订单->短信接口参数
                                $pt_smsParams = [
                                    'shop_name' => $shop_name,
                                    'shopname' => $shop_name,
                                    'order_sn' => $order['order_sn'],
                                    'ordersn' => $order['order_sn'],
                                    'consignee' => $order['consignee'] ?? '',
                                    'order_region' => $order_region,
                                    'orderregion' => $order_region,
                                    'address' => $order['address'] ?? '',
                                    'order_mobile' => $order['mobile'],
                                    'ordermobile' => $order['mobile'],
                                    'mobile_phone' => $sms_shop_mobile,
                                    'mobilephone' => $sms_shop_mobile
                                ];

                                // 下单发短信
                                if (config('shop.sms_order_placed') == '1') {
                                    $this->commonRepository->smsSend($sms_shop_mobile, $pt_smsParams, 'sms_order_placed');
                                }

                                // 下单与付款发送短信 若同时开启 须间隔1s
                                if (config('shop.sms_order_placed') == '1' && config('shop.sms_order_payed') == '1') {
                                    sleep(1);
                                }
                                // 余额支付等金额为0的订单 付款发短信
                                if ($stores_sms == 1 && config('shop.sms_order_payed') == '1') {
                                    $this->commonRepository->smsSend($sms_shop_mobile, $pt_smsParams, 'sms_order_payed');
                                }
                            }
                        }
                    }

                    /* 删除队列 */
                    SolveDealconcurrent::where('user_id', $user_id)->delete();

                    // 分成插入drp_log
                    if (file_exists(MOBILE_DRP)) {
                        $affiliate_drp_id = CommonRepository::getDrpAffiliate(); // 推荐人
                        $extendParam['affiliate_drp_id'] = $affiliate_drp_id;
                    }

                    // 下单事件监听
                    $extendParam['shop_config'] = config('shop');
                    event(new \App\Events\OrderDoneEvent($order, $extendParam));

                    //下单推送消息给多商户商家掌柜事件
                    event(new \App\Events\PushMerchantOrderPlaceEvent($order['order_sn']));

                    // 下单支付成功
                    $extendParam = [];
                    if ($order['order_amount'] <= 0 && $order['pay_status'] == PS_PAYED) {

                        //付款成功创建快照
                        if ($snapshot) {
                            $extendParam['create_snapshot'] = 1;
                        }

                        // 使用储值卡支付、使用余额 支付订单(含分单) 记录操作日志
                        if (!empty($order['use_value_card']) || ($order['surplus'] > 0)) {
                            /* 记录主订单操作记录 */
                            $note = $order['use_value_card'] > 0 ? trans('shopping_flow.flow_value_card_pay') : trans('shopping_flow.flow_surplus_pay');

                            order_action($order['order_sn'], OS_CONFIRMED, SS_UNSHIPPED, PS_PAYED, $note, trans('common.buyer'));

                            if (!empty($child_order_info)) {
                                /* 记录子订单操作记录 */
                                foreach ($child_order_info as $key => $child) {
                                    if (isset($child['order_sn']) && $child['order_sn']) {
                                        order_action($child['order_sn'], OS_CONFIRMED, SS_UNSHIPPED, PS_PAYED, $note, trans('common.buyer'));
                                    }
                                }
                            }
                        }

                        // 订单支付事件监听
                        $extendParam['shop_config'] = config('shop');
                        event(new \App\Events\OrderPaidEvent($order, $extendParam));

                        //订单支付推送消息给多商户商家掌柜事件
                        event(new \App\Events\PushMerchantOrderPayedEvent($order['order_sn']));
                    }

                    /* 如果订单金额为0 处理虚拟卡 */
                    if ($new_order_id > 0 && $order['order_amount'] <= 0 && count($all_ru_id) == 1) {
                        $this->jigonManageService->jigonConfirmOrder($new_order_id); //贡云确认订单

                        /* 取得未发货虚拟商品 */
                        $virtual_goods = get_virtual_goods($new_order_id);
                        if ($virtual_goods && $flow_type != CART_GROUP_BUY_GOODS) {
                            /* 虚拟卡发货 */
                            $error_msg = '';
                            if (virtual_goods_ship($virtual_goods, $error_msg, $order['order_sn'], true)) {
                                /* 如果没有实体商品，修改发货状态，送积分和红包 */
                                $count = OrderGoods::where('order_id', $order['order_id'])->where('is_real', 1)->count();
                                if ($count <= 0) {
                                    /* 修改订单状态 */
                                    update_order($order['order_id'], ['shipping_status' => SS_SHIPPED, 'shipping_time' => $time]);

                                    /* 如果订单用户不为空，计算积分，并发给用户；发红包 */
                                    if ($order['user_id'] > 0) {
                                        /* 计算并发放积分 */
                                        $integral = integral_to_give($order);
                                        log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf(lang('payment.order_gift_integral'), $order['order_sn']));

                                        /* 发放红包 */
                                        send_order_bonus($order['order_id']);

                                        /* 发放优惠券 bylu */
                                        send_order_coupons($order['order_id']);
                                    }

                                    if (config('shop.sales_volume_time') == SALES_SHIP) {
                                        // 发货更新商品销量
                                        \App\Repositories\Order\OrderRepository::increment_goods_sale_ship($order['order_id'], ['order_id' => $order['order_id'], 'pay_status' => $order['pay_status'], 'shipping_status' => SS_SHIPPED]);
                                    }
                                }
                            }
                        }
                    }

                }

                // 跳转至收银台
                return dsc_header("Location:flow.php?step=order_reload&order_id=" . $new_order_id . "&store_id=" . $store_id . "\n");
            }
        } elseif ($step == 'update_cart') {
            $goods_number = request()->input('goods_number');
            if (is_array($goods_number)) {
                $this->cartService->getFlowUpdateCart($goods_number);
            }

            return show_message($GLOBALS['_LANG']['update_cart_notice'], $GLOBALS['_LANG']['back_to_cart'], 'flow.php');
        }

        /*------------------------------------------------------ */
        //-- 打印并下载订单 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($step == 'pdf') {
            $order_id = (int)request()->input('order', 0);

            if (!is_dir(storage_path('fonts'))) {
                make_dir(storage_path('fonts'));
            }

            /* 跳转首页 会员未登录 */
            if (!$user_id) {
                return redirect("user.php");
            }

            /* 跳转首页 订单ID为0 */
            if (!$order_id) {
                return redirect('/');
            }

            $consignee = $this->flowUserService->getConsignee($user_id);
            $userinfo = get_user_info($consignee['user_id']);
            $consignee['user_name'] = $userinfo['user_name'];

            /* 初始化地区ID */
            $consignee['country'] = !isset($consignee['country']) && empty($consignee['country']) ? 0 : intval($consignee['country']);
            $consignee['province'] = !isset($consignee['province']) && empty($consignee['province']) ? 0 : intval($consignee['province']);
            $consignee['city'] = !isset($consignee['city']) && empty($consignee['city']) ? 0 : intval($consignee['city']);
            $consignee['district'] = !isset($consignee['district']) && empty($consignee['district']) ? 0 : intval($consignee['district']);
            $consignee['street'] = !isset($consignee['street']) && empty($consignee['street']) ? 0 : intval($consignee['street']);

            session([
                'flow_consignee' => $consignee
            ]);

            $this->smarty->assign('consignee', $consignee);

            $order_info = order_info($order_id);

            if (empty($order_info['order_id'])) {
                /* 跳转首页 订单信息为空 */
                return get_go_index(1, false);
            } elseif ($order_info['user_id'] != $user_id) {
                /* 跳转首页 订单会员不等于当前登录会员 */
                return get_go_index(1, false);
            }

            $order_info['add_time'] = TimeRepository::getLocalDate("Y-m-d H:i:s", $order_info['add_time']);

            /* 过滤不同商家商品主订单的配送方式显示 start */
            if ($order_info['shipping_name']) {
                $shipping_list = explode(",", $order_info['shipping_name']);
                if (count($shipping_list) > 1) {
                    foreach ($shipping_list as $key => $row) {
                        if ($row) {
                            $shipping = explode("|", $row);
                            $shipping_list[$key] = $shipping[1];
                        }
                    }

                    $shipping_list = array_unique($shipping_list);
                    $order_info['shipping_name'] = implode(",", $shipping_list);
                }
            }
            /* 过滤不同商家商品主订单的配送方式显示 end */

            $order_goods = $this->orderGoodsService->getOrderPdfGoods($order_id); /* 订单中的商品 */

            $shop_info = SellerShopinfo::where('ru_id', $order_info['ru_id']);
            $shop_info = BaseRepository::getToArrayFirst($shop_info);

            $chat = $this->dscRepository->chatQq($shop_info);
            $shop_info['kf_qq'] = $chat['kf_qq'];
            $shop_info['kf_ww'] = $chat['kf_ww'];

            $data = [
                'shop_info' => $shop_info,
                'consignee' => $consignee,
                'order_info' => $order_info,
                'order_goods' => $order_goods,
                'url' => url('/') . '/'
            ];

            $time = TimeRepository::getLocalDate("YmdHis", TimeRepository::getGmTime());

            return app('dompdf.wrapper')->loadView('pdfs.order', $data)
                ->setPaper('a4', 'landscape')
                ->setWarnings(false)
                ->setOptions(['dpi' => 96])
                ->stream('order_' . $time . '.pdf');
        }

        /*------------------------------------------------------ */
        //-- 删除购物车中的商品
        /*------------------------------------------------------ */
        elseif ($step == 'drop_goods') {
            $sig = request()->input('sig', '');
            if (!empty($sig)) {
                $n_id = request()->input('id');
                $n_id = explode('@', $n_id);
                foreach ($n_id as $val) {
                    flow_drop_cart_goods($val);

                    /* 删除队列 */
                    SolveDealconcurrent::where('user_id', $user_id)
                        ->whereRaw("FIND_IN_SET('$val', REPLACE(orec_id, '|', ','))")
                        ->delete();
                }
            } else {
                $rec_id = (int)request()->input('id', 0);
                flow_drop_cart_goods($rec_id);

                /* 删除队列 */
                SolveDealconcurrent::where('user_id', $user_id)
                    ->whereRaw("FIND_IN_SET('$rec_id', REPLACE(orec_id, '|', ','))")
                    ->delete();
            }


            return dsc_header("Location: flow.php\n");
        }

        /*------------------------------------------------------ */
        //-- 清除购物车
        /*------------------------------------------------------ */
        elseif ($step == 'clear') {
            if (!empty($user_id)) {
                $res = Cart::where('user_id', $user_id);
            } else {
                $res = Cart::where('session_id', $session_id);
            }

            $res->delete();

            return redirect("/");
        }

        /*------------------------------------------------------ */
        //-- 删除收藏并收藏
        /*------------------------------------------------------ */
        elseif ($step == 'drop_to_collect') {
            $rec_id = (int)request()->input('id', 0);
            if ($user_id > 0) {

                /* 获取商品ID start */
                if (!empty($user_id)) {
                    $goods_id = Cart::where('user_id', $user_id);
                } else {
                    $goods_id = Cart::where('session_id', $session_id);
                }

                $goods_id = $goods_id->value('goods_id');
                /* 获取商品ID end */

                $count = CollectGoods::where('user_id', $user_id)->where('goods_id', $goods_id)->count();
                if (empty($count)) {
                    $time = TimeRepository::getGmTime();
                    $other = [
                        'user_id' => $user_id,
                        'goods_id' => $goods_id,
                        'add_time' => $time
                    ];
                    CollectGoods::insert($other);
                }
            }

            flow_drop_cart_goods($rec_id, $step);
            return dsc_header("Location: flow.php\n");
        }

        /*------------------------------------------------------ */
        //-- 订单收银台
        /*------------------------------------------------------ */
        elseif ($step == 'order_reload') {
            load_helper('clips');

            $order_id = (int)request()->input('order_id', 0);
            $store_id = (int)request()->input('store_id', 0);

            //取的订单信息;
            $where = [
                'order_id' => $order_id
            ];
            $order = $this->orderService->getOrderInfo($where);

            if (empty($order) || $order['user_id'] != $user_id) {
                return redirect('/');
            }

            if ($order['pay_status'] == PS_PAYED) {
                // 支付成功页
                return redirect("flow.php?step=pay_success&order_id=" . $order_id . "&store_id=" . $store_id);
            }

            //获取log_id
            $order['log_id'] = PayLog::where('order_id', $order_id)->where('order_type', PAY_ORDER)->value('log_id');

            /* 取得支付信息，生成支付代码 */
            if ($order['order_amount'] > 0) {
                $pay_online = '';
                //查询"在线支付"的pay_id;

                $where = [
                    'pay_code' => 'onlinepay'
                ];
                $payment_info = $this->paymentService->getPaymentInfo($where);
                $onlinepay_pay_id = $payment_info ? $payment_info['pay_id'] : 0;

                //@模板堂-bylu 如果选择的是在线支付,循环出商家启用的所有在线支付方法 start
                if ($order['pay_id'] == $onlinepay_pay_id) {

                    //用于判断当前用户是否拥有白条支付授权(有的话才显示"白条支付按钮");
                    $baitiao_balance = $this->userBaitiaoService->getBaitiaoBalance($user_id);

                    $payment_list = available_payment_list(0, 0, 1);

                    if (!empty($payment_list)) {
                        foreach ($payment_list as $key => $payment) {
                            // 过滤非在线支付
                            if ($payment['is_online'] == 0) {
                                unset($payment_list[$key]);
                            }

                            if ($payment['pay_code'] == 'chunsejinrong') {
                                unset($payment_list[$key]);
                            }
                        }
                    }

                    $this->smarty->assign('payment_list', $payment_list); //在线支付按钮数组;
                    $this->smarty->assign('is_onlinepay', true); //在线支付标记 by lu;

                    //判断当前用户是否拥有白条支付授权(有的话才显示"白条支付按钮");
                    if ($baitiao_balance && $baitiao_balance['balance'] > 0) {
                        // 白条订单条件
                        $baitiao_order = Stages::where('order_sn', $order['order_sn'])->count();
                        $is_chunsejinrong = $baitiao_order > 0 ? true : false;
                        $this->smarty->assign('is_chunsejinrong', $is_chunsejinrong);
                    }

                    if (session('flow_type') == 5) {//预售商品标记 by liu
                        $this->smarty->assign('is_presale_goods', true);
                    }
                } else {
                    $where = [
                        'pay_id' => $order['pay_id']
                    ];
                    $payment = $this->paymentService->getPaymentInfo($where);

                    $pay_online = '';
                    if ($payment && strpos($payment['pay_code'], 'pay_') === false) {
                        $payObject = CommonRepository::paymentInstance($payment['pay_code']);
                        if (!is_null($payObject)) {
                            $pay_online = $payObject->get_code($order, unserialize_config($payment['pay_config']));
                        }
                    }

                    $order['pay_desc'] = $payment['pay_desc'];
                    $order['pay_code'] = addslashes($payment['pay_code']);
                    // 银行转账信息
                    if ($payment['pay_code'] == 'bank') {
                        $order['bank_message'] = sprintf(lang('flow.pay_code_notic_two'), $payment['pay_name']);
                        $plugin = app(PaymentService::class)->getPayment($payment['pay_code']);
                        $pay_config = [];
                        if ($plugin) {
                            $pay_config = $plugin->getConfig($payment['pay_code'], unserialize($payment['pay_config']));
                        }
                        $order['pay_config'] = $pay_config;
                    }
                }
                $this->smarty->assign('pay_online', $pay_online);
            } else {
                $where = [
                    'pay_id' => $order['pay_id']
                ];
                $payment = $this->paymentService->getPaymentInfo($where);
                $order['pay_code'] = $payment['pay_code'] ?? '';
            }

            //处理价格显示
            $order['format_shipping_fee'] = $this->dscRepository->getPriceFormat($order['shipping_fee']);
            $order['format_order_amount'] = $this->dscRepository->getPriceFormat($order['order_amount']);

            //判断当前订单是否是"白条分期"订单,是的话显示分期信息;
            $stages_intro = session()->get('stages_intro.' . $user_id . $order_id, []);
            if (!empty($stages_intro) && !empty($stages_intro['stages_qishu'])) {
                $stages_intro['baitiao'] = !empty($baitiao_balance['balance']) ? $baitiao_balance['balance'] : 0;
                $this->smarty->assign('stages_info', $stages_intro);
            }

            $region = [
                'province' => $order['province'],
                'city' => $order['city'],
                'district' => $order['district'],
                'street' => $order['street']
            ];
            $order['region'] = $address_info = get_area_region_info($region);
            $this->smarty->assign('address_info', $address_info); //收货地址
            $this->smarty->assign('order', $order);

            $child_order_info = $this->flowOrderService->getChildOrderInfo($order['order_id']);
            $this->smarty->assign('child_order_info', $child_order_info);

            $child_order = BaseRepository::getArrayCount($child_order_info);
            $this->smarty->assign('child_order', $child_order);

            // 买了又买
            $goods_buy_list = get_order_goods_buy_list($warehouse_id, $area_id, $area_city);
            $this->smarty->assign('goods_buy_list', $goods_buy_list);
        }

        /*------------------------------------------------------ */
        //-- 支付成功页
        /*------------------------------------------------------ */
        elseif ($step == 'pay_success') {

            /* 取得购物类型 */
            $flow_type = intval(session('flow_type', CART_GENERAL_GOODS));

            /* 团购标志 */
            if ($flow_type == CART_GROUP_BUY_GOODS) {
                $this->smarty->assign('is_group_buy', 1);
            }

            $order_id = (int)trim(request()->input('order_id', 0));

            //判断该订单是否真的支付成功;
            //取的订单信息;
            $where = [
                'order_id' => $order_id
            ];
            $order = $this->orderService->getOrderInfo($where);

            if (empty($order)) {
                return redirect('/');
            }

            $pay_status = $order ? $order['pay_status'] : 0;
            if ($order['user_id'] != $user_id || ($pay_status != 2 && $pay_status != 3)) {
                return redirect('/');
            }

            /*门店id*/
            $store_id = (int)request()->input('store_id', 0);

            /*如果是门店商品*/
            if ($store_id > 0) {
                $offline_store = OfflineStore::where('id', $store_id)->first();
                $offline_store = $offline_store ? $offline_store->toArray() : [];

                $this->smarty->assign('stores_info', $offline_store);
            }

            $order['order_amount'] = $order['money_paid'] + $order['surplus'];

            if ($order['main_count'] > 0) {
                $where = [
                    'main_order_id' => $order_id
                ];
                $child_order_info = $this->orderService->getOrderList($where);

                if ($child_order_info) {
                    foreach ($child_order_info as $k => $v) {
                        $child_order_info[$k]['order_amount'] = $this->dscRepository->getPriceFormat($v['money_paid'] + $v['surplus']);
                    }
                }

                $this->smarty->assign('child_order_info', $child_order_info);//子订单信息;
            }

            $region = [
                'province' => $order['province'],
                'city' => $order['city'],
                'district' => $order['district'],
                'street' => $order['street']
            ];
            $address_info = get_area_region_info($region);
            $this->smarty->assign('address_info', $address_info); //收货地址

            // 买了又买
            $goods_buy_list = get_order_goods_buy_list($warehouse_id, $area_id, $area_city);
            $this->smarty->assign('goods_buy_list', $goods_buy_list);

            $this->smarty->assign('child_order', $order['main_count']);//子订单个数;
            $this->smarty->assign('order', $order);//主订单信息;
            $this->smarty->assign('is_zc_order', $order['is_zc_order']);//是否为众筹订单;
            $this->smarty->assign('pay_success', true);

            // 清空白条分期订单 session
            session()->forget('stages_intro.' . $user_id . $order_id);
        }

        /*------------------------------------------------------ */
        //-- 购物车起始页
        /*------------------------------------------------------ */
        else {

            $one_step_buy = session('one_step_buy', 0);
            $store_id = request()->input('store_id', 0);

            if (session()->exists('flow_type') && session('flow_type') == CART_OFFLINE_GOODS && $store_id > 0) {
                $flow_type = CART_OFFLINE_GOODS;
            } else {

                if (count(request()->all()) == 1 && session('flow_type') == CART_PACKAGE_GOODS) {
                    $one_step_buy = 0;
                } else {
                    /**
                     * 超值礼包
                     */
                    if (session('flow_type') == CART_PACKAGE_GOODS) {
                        $one_step_buy = 1;
                    }
                }

                /* 开启一步购物 */
                if ($one_step_buy == 1) {
                    if (!(session('flow_type') == CART_PACKAGE_GOODS)) {
                        if (session('flow_stages_qishu') == 1) {
                            $flow_type = CART_GENERAL_GOODS;
                        } else {
                            $flow_type = CART_ONESTEP_GOODS;
                        }
                    } else {
                        $flow_type = session('flow_type');
                    }
                } else {
                    /* 标记购物流程为普通商品 */
                    $flow_type = CART_GENERAL_GOODS;
                }
            }

            session([
                'flow_type' => $flow_type
            ]);

            /* 如果是一步购物，跳到结算中心 */
            if ($one_step_buy == 1) {
                $param = '';
                if ($store_id > 0) {
                    $param = '&store_id=' . $store_id;
                }
                return dsc_header("Location: flow.php?step=checkout" . $param . "\n");
            }

            $this->smarty->assign('area_id', $area_id); //省下级市
            $this->smarty->assign('flow_region', $flow_region); //省下级市

            /* 取得商品列表，计算合计 */
            $cart_goods = get_cart_goods('', 1, 0, 0, $this->district_id);

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
            $merchant_goods_list = $this->flowActivityService->getFavourableCartGoodsList($merchant_goods, $user_id, 'store_id');
            $merchant_goods_list = $this->flowActivityService->merchantActivityCartGoodsList($merchant_goods_list);

            $this->smarty->assign('goods_list', $merchant_goods_list);
            $this->smarty->assign('total', $cart_goods['total']);

            $cart_value = $this->cartService->getSatrtCartVlaue($cart_goods['cart_value']);
            $this->smarty->assign('cart_value', $cart_value);

            //购物车的描述的格式化
            $this->smarty->assign('shopping_money', sprintf($GLOBALS['_LANG']['shopping_money'], $cart_goods['total']['goods_price']));
            $this->smarty->assign('market_price_desc', sprintf(
                $GLOBALS['_LANG']['than_market_price'],
                $cart_goods['total']['market_price'],
                $cart_goods['total']['saving'],
                $cart_goods['total']['save_rate']
            ));

            /* 计算折扣 */
            $discount = compute_discount();
            $this->smarty->assign('discount', $discount['discount']);
            $favour_name = empty($discount['name']) ? '' : join(',', $discount['name']);
            $this->smarty->assign('your_discount', sprintf($GLOBALS['_LANG']['your_discount'], $favour_name, $this->dscRepository->getPriceFormat($discount['discount'])));

            /* 增加是否在购物车里显示商品图 */
            $this->smarty->assign('show_goods_thumb', config('shop.show_goods_in_cart'));

            /* 增加是否在购物车里显示商品属性 */
            $this->smarty->assign('show_goods_attribute', config('shop.show_attr_in_cart'));


            /**
             * Start
             *
             * 猜你喜欢商品
             */
            $where = [
                'warehouse_id' => $warehouse_id,
                'area_id' => $area_id,
                'user_id' => $user_id,
                'history' => 1,
                'page' => 1,
                'limit' => 18,
                'area_city' => $area_city
            ];
            $guess_goods = $this->goodsGuessService->getGuessGoods($where);

            $this->smarty->assign('guess_goods', $guess_goods);
            $this->smarty->assign('guessGoods_count', count($guess_goods));
            /* End */

            /**
             * Start
             *
             * 商品推荐
             * 【'best' ：精品, 'new' ：新品, 'hot'：热销】
             */
            $where = [
                'warehouse_id' => $warehouse_id,
                'area_id' => $area_id,
                'area_city' => $area_city
            ];

            /* 推荐商品 */
            $where['type'] = 'best';
            $best_goods = $this->goodsService->getRecommendGoods($where);

            $this->smarty->assign('best_goods', $best_goods);
            $this->smarty->assign('bestGoods_count', count($best_goods));
            /* End */


            // 购物车选择配送地区
            $province_id = $this->province_id;
            $city_id = $this->city_id;
            $district_id = $this->district_id;
            $street_id = $this->street_id;

            $province_list = Region::select('region_id', 'region_name', 'parent_id')->where('parent_id', 1)->where('region_type', 1)->orderBy('region_id')->get();
            $province_list = $province_list ? $province_list->toArray() : [];

            $city_list = Region::select('region_id', 'region_name', 'parent_id')->where('parent_id', $province_id)->orderBy('region_id')->get();
            $city_list = $city_list ? $city_list->toArray() : [];

            $district_list = Region::select('region_id', 'region_name', 'parent_id')->where('parent_id', $city_id)->orderBy('region_id')->get();
            $district_list = $district_list ? $district_list->toArray() : [];

            $street_list_two = Region::select('region_id', 'region_name', 'parent_id')->where('parent_id', $district_id)->orderBy('region_id')->get();
            $street_list_two = $street_list_two ? $street_list_two->toArray() : [];

            // 当前省市区或默认收货地址省市区
            $province_row = [];
            $city_row = [];
            $district_row = [];
            $street_row = [];
            foreach ($province_list as $k => $v) {
                $province_list[$k]['choosable'] = true;

                // 当前省份
                if ($province_id > 0 && $v['region_id'] == $province_id) {
                    $province_row = $v;
                }
            }
            foreach ($city_list as $k => $v) {
                $city_list[$k]['choosable'] = true;

                // 当前市
                if ($city_id > 0 && $v['region_id'] == $city_id) {
                    $city_row = $v;
                }
            }
            foreach ($district_list as $k => $v) {
                $district_list[$k]['choosable'] = true;

                // 当前区
                if ($district_id > 0 && $v['region_id'] == $district_id) {
                    $district_row = $v;
                }
            }
            foreach ($street_list_two as $k => $v) {
                $street_list_two[$k]['choosable'] = true;

                // 当前街道
                if ($street_id > 0 && $v['region_id'] == $street_id) {
                    $street_row = $v;
                }
            }

            $this->smarty->assign('province_row', $province_row);
            $this->smarty->assign('city_row', $city_row);
            $this->smarty->assign('district_row', $district_row);
            $this->smarty->assign('street_row', $street_row);

            $this->smarty->assign('province_list', $province_list); //省、直辖市
            $this->smarty->assign('city_list', $city_list); //省下级市
            $this->smarty->assign('district_list', $district_list);//市下级县
            $this->smarty->assign('street_list', $street_list_two);//街道 镇
        }

        $this->smarty->assign('currency_format', config('shop.currency_format'));
        $this->smarty->assign('integral_scale', $this->dscRepository->getPriceFormat(config('shop.integral_scale')));
        $this->smarty->assign('step', $step);
        assign_dynamic('shopping_flow');

        return $this->smarty->display('flow.dwt');
    }
}
