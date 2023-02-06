<?php

namespace App\Modules\Web\Controllers;

use App\Models\CartCombo;
use App\Models\Goods;
use App\Models\OfflineStore;
use App\Models\OrderGoods;
use App\Models\Region;
use App\Models\SellerShopinfo;
use App\Models\StoreProducts;
use App\Models\Users;
use App\Plugins\UserRights\Discount\Services\DiscountRightsService;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Article\ArticleCommonService;
use App\Services\Comment\CommentService;
use App\Services\Common\AreaService;
use App\Services\Erp\JigonManageService;
use App\Services\Goods\GoodsAttrService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Goods\GoodsFittingService;
use App\Services\Goods\GoodsGalleryService;
use App\Services\Goods\GoodsService;
use App\Services\Goods\GoodsWarehouseService;
use App\Services\History\HistoryService;
use App\Services\Merchant\MerchantCategoryService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Order\OrderGoodsService;
use App\Services\User\UserCommonService;

/**
 * 商品详情
 */
class GoodsController extends InitController
{
    protected $areaService;
    protected $goodsService;
    protected $dscRepository;
    protected $goodsAttrService;
    protected $goodsCommonService;
    protected $goodsWarehouseService;
    protected $merchantCommonService;
    protected $commentService;
    protected $goodsGalleryService;
    protected $userCommonService;
    protected $sessionRepository;
    protected $articleCommonService;
    protected $orderGoodsService;
    protected $goodsFittingService;
    protected $historyService;
    protected $merchantCategoryService;

    public function __construct(
        AreaService $areaService,
        GoodsService $goodsService,
        DscRepository $dscRepository,
        GoodsAttrService $goodsAttrService,
        GoodsCommonService $goodsCommonService,
        GoodsWarehouseService $goodsWarehouseService,
        MerchantCommonService $merchantCommonService,
        CommentService $commentService,
        GoodsGalleryService $goodsGalleryService,
        UserCommonService $userCommonService,
        SessionRepository $sessionRepository,
        ArticleCommonService $articleCommonService,
        OrderGoodsService $orderGoodsService,
        GoodsFittingService $goodsFittingService,
        HistoryService $historyService,
        MerchantCategoryService $merchantCategoryService
    )
    {
        $this->areaService = $areaService;
        $this->goodsService = $goodsService;
        $this->dscRepository = $dscRepository;
        $this->goodsAttrService = $goodsAttrService;
        $this->goodsCommonService = $goodsCommonService;
        $this->goodsWarehouseService = $goodsWarehouseService;
        $this->merchantCommonService = $merchantCommonService;
        $this->commentService = $commentService;
        $this->goodsGalleryService = $goodsGalleryService;
        $this->userCommonService = $userCommonService;
        $this->sessionRepository = $sessionRepository;
        $this->articleCommonService = $articleCommonService;
        $this->orderGoodsService = $orderGoodsService;
        $this->goodsFittingService = $goodsFittingService;
        $this->historyService = $historyService;
        $this->merchantCategoryService = $merchantCategoryService;
    }

    public function index()
    {

        /**
         * Start
         *
         * @param int $warehouse_id 仓库ID
         * @param int $area_id 省份ID
         * @param int $area_city 城市ID
         */
        $warehouse_id = $this->warehouseId();
        $area_id = $this->areaId();
        $area_city = $this->areaCity();

        $affiliate = config('shop.affiliate') ? unserialize(config('shop.affiliate')) : [];
        $this->smarty->assign('affiliate', $affiliate);
        $factor = intval(config('shop.comment_factor'));
        $this->smarty->assign('factor', $factor);

        $now = TimeRepository::getGmTime();

        /* 过滤 XSS 攻击和SQL注入 */
        get_request_filter();

        $act = addslashes(request()->input('act', ''));
        $goods_id = (int)request()->input('id', 0);
        $pid = (int)request()->input('pid', 0);

        if (CROSS_BORDER === true) { // 跨境多商户
            $web = app(\App\Services\CrossBorder\CrossBorderService::class)->webExists();
            if (!empty($web)) {
                if ($web->merchantSource($goods_id)) {// 是否是跨境店铺
                    $web->smartyAssign();
                }
            }
        }

        $user_id = session('user_id', 0);
        $parent_id = (int)request()->input('u', 0);

        /* 跳转H5 start */
        $Loaction = dsc_url('/#/goods/' . $goods_id);
        $uachar = $this->dscRepository->getReturnMobile($Loaction);

        if ($uachar) {
            return $uachar;
        }
        /* 跳转H5 end */

        $this->smarty->assign('category', $goods_id);

        //参数不存在则跳转回首页
        if (empty($goods_id)) {
            return redirect("/");
        }

        /* 查看是否秒杀商品 */
        $sec_goods_id = $this->goodsService->getIsSeckill($goods_id);
        if ($sec_goods_id) {
            $seckill_url = $this->dscRepository->buildUri('seckill', array('act' => "view", 'secid' => $sec_goods_id));
            return dsc_header("Location: $seckill_url\n");
        }

        $where = [
            'goods_id' => $goods_id,
            'warehouse_id' => $warehouse_id,
            'area_id' => $area_id,
            'area_city' => $area_city,
            'is_delete' => 0
        ];
        $goods = $this->goodsService->getGoodsInfo($where);

        /*------------------------------------------------------ */
        //-- 改变属性、数量时重新计算商品价格
        /*------------------------------------------------------ */

        if (!empty($act) && $act == 'price') {
            if ($this->checkReferer() === false) {
                return response()->json(['err_no' => 1, 'err_msg' => 'referer error']);
            }

            $res = ['err_msg' => '', 'err_no' => 0, 'result' => '', 'qty' => 1];

            $get_attr_id = request()->input('attr', '');
            $attr_id = $get_attr_id ? explode(',', $get_attr_id) : [];

            $number = (int)request()->input('number', 1);
            $warehouse_id = (int)request()->input('warehouse_id', 0);
            //仓库管理的地区ID
            $area_id = (int)request()->input('area_id', 0);
            //加载类型
            $type = (int)request()->input('type', 0);

            $goods_attr = request()->input('goods_attr', '');
            $goods_attr = $goods_attr ? explode(',', $goods_attr) : [];

            $province_id = intval(request()->get('province_id', 0));
            $city_id = intval(request()->get('city_id', 0));
            $district_id = intval(request()->get('district_id', 0));
            $street = intval(request()->get('street', 0));

            $attr_ajax = $this->goodsService->getGoodsAttrAjax($goods_id, $goods_attr, $attr_id);

            if ($goods_id == 0) {
                $res['err_msg'] = $GLOBALS['_LANG']['err_change_attr'];
                $res['err_no'] = 1;
            } else {
                if ($number == 0) {
                    $res['qty'] = $number = 1;
                } else {
                    $res['qty'] = $number;
                }

                //ecmoban模板堂 --zhuo start
                $products = $this->goodsWarehouseService->getWarehouseAttrNumber($goods_id, $get_attr_id, $warehouse_id, $area_id, $area_city);
                $attr_number = isset($products['product_number']) ? $products['product_number'] : 0;
                $product_promote_price = isset($products['product_promote_price']) ? $products['product_promote_price'] : 0;
                /* 判断属性货品是否存在 */

                $prod = $this->goodsWarehouseService->getGoodsProductsProd($goods_id, $warehouse_id, $area_id, $area_city, $goods['model_attr']);

                //贡云商品 获取库存
                if ($goods['cloud_id'] > 0 && isset($products['product_id'])) {
                    $attr_number = !empty($attr_id) ? app(JigonManageService::class)->jigonGoodsNumber(['product_id' => $products['product_id']]) : 0;
                } else {
                    if ($goods['goods_type'] == 0) {
                        $attr_number = $goods['goods_number'];
                    } else {
                        if (empty($prod)) { //当商品没有属性库存时
                            $attr_number = $goods['goods_number'];
                        }
                    }
                }

                if (empty($prod)) { //当商品没有属性库存时
                    $res['bar_code'] = $goods['bar_code'];
                } else {
                    $res['bar_code'] = $products['bar_code'] ?? '';
                }

                $attr_number = !empty($attr_number) ? $attr_number : 0;

                $res['attr_number'] = $attr_number;
                //ecmoban模板堂 --zhuo end

                $res['show_goods'] = 0;
                if ($goods_attr && config('shop.add_shop_price') == 0) {
                    if (count($goods_attr) == count($attr_ajax['attr_id'])) {
                        $res['show_goods'] = 1;
                    }
                }

                $goodsSelf = false;
                if ($goods['user_id'] == 0) {
                    $goodsSelf = true;
                }

                $shop_price = $this->goodsCommonService->getFinalPrice($goods_id, $number, true, $attr_id, $warehouse_id, $area_id, $area_city);
                $res['shop_price'] = $this->dscRepository->getPriceFormat($shop_price, true, true, $goodsSelf);



                //属性价格
                if ($attr_id) {
                    $spec_price = $this->goodsCommonService->getFinalPrice($goods_id, $number, true, $attr_id, $warehouse_id, $area_id, $area_city, 1, 0, 0, $res['show_goods'], $product_promote_price);
                } else {
                    $spec_price = 0;
                }

                /* 开启仓库地区模式 */
                if ($goods['model_price'] > 0 && empty($attr_id) && $shop_price <= 0) {
                    $time = TimeRepository::getGmTime();
                    //当前商品正在促销时间内
                    if ($time >= $goods['promote_start_date'] && $time <= $goods['promote_end_date'] && $goods['is_promote']) {
                        $shop_price = $goods['promote_price_org'];
                    } else {
                        $shop_price = $goods['shop_price'];
                    }
                }

                $res['goods_rank_prices'] = '';
                if (config('shop.add_shop_price') == 0 && empty($spec_price) && empty($prod)) {
                    $spec_price = $shop_price;
                }

                if (config('shop.add_shop_price') == 0) {
                    if ($attr_id) {
                        $res['result'] = $this->dscRepository->getPriceFormat($spec_price, true, true, $goodsSelf);
                    } else {
                        $res['result'] = $this->dscRepository->getPriceFormat($shop_price, true, true, $goodsSelf);
                    }
                    if ($products && $products['product_price'] > 0) {
                        $rank_prices = $this->goodsCommonService->getUserRankPrices($goods, $products['product_price'], session('user_rank'));

                        if (!empty($rank_prices)) {
                            $this->smarty->assign('act', 'goods_rank_prices');
                            $this->smarty->assign('rank_prices', $rank_prices);
                            $res['goods_rank_prices'] = $GLOBALS['smarty']->fetch('library/dialog.lbi');
                        }
                    }
                } else {
                    $res['result'] = $this->dscRepository->getPriceFormat($shop_price, true, true, $goodsSelf);
                }

                if (CROSS_BORDER === true) { // 跨境多商户
                    $web = app(\App\Services\CrossBorder\CrossBorderService::class)->webExists();
                    if (!empty($web)) {
                        // 税率计算 有属性按属性价
                        $goods_price = !empty($attr_id) ? $spec_price : $shop_price;
                        $res['goods_rate'] = $web->getGoodsRate($goods_id, $goods_price);
                        $res['formated_goods_rate'] = $this->dscRepository->getPriceFormat($res['goods_rate'], true, true, $goodsSelf);
                    }
                }

                $region = [1, $province_id, $city_id, $district_id, $street];
                $res['shipping_fee'] = goodsShippingFee($goods_id, $warehouse_id, $area_id, $area_city, $region, '', $attr_id);

                $res['spec_price'] = $this->dscRepository->getPriceFormat($spec_price, true, true, $goodsSelf);
                $res['original_shop_price'] = $shop_price;
                $res['original_spec_price'] = $spec_price;
                $res['marketPrice_amount'] = $this->dscRepository->getPriceFormat($goods['marketPrice'] + $spec_price, true, true, $goodsSelf);

                if (config('shop.add_shop_price') == 0) {
                    $goods['marketPrice'] = isset($products['product_market_price']) && !empty($products['product_market_price']) ? $products['product_market_price'] : $goods['marketPrice'];
                    $res['result_market'] = $this->dscRepository->getPriceFormat($goods['marketPrice'], true, true, $goodsSelf); // * $number
                } else {
                    $res['result_market'] = $this->dscRepository->getPriceFormat($goods['marketPrice'] + $spec_price, true, true, $goodsSelf); // * $number
                }

                if ($goods['is_promote'] > 0) {
                    $result_market = $products && $products['product_price'] ? $products['product_price'] : $goods['shop_price'];
                    $res['result_market'] = $this->dscRepository->getPriceFormat($result_market, true, true, $goodsSelf);
                }

                //@author-bylu 当点击了数量加减后 重新计算白条分期 每期的价格 start
                if ($goods['stages']) {
                    if (!is_array($goods['stages'])) {
                        $stages = unserialize($goods['stages']);
                    } else {
                        $stages = $goods['stages'];
                    }

                    $total = floatval(strip_tags(str_replace('¥', '', $res['result']))); //总价+运费*数量;
                    foreach ($stages as $K => $v) {
                        $res['stages'][$v] = round($total * ($goods['stages_rate'] / 100) + $total / $v, 2);
                    }
                }
                //@author-bylu 当点击了数量加减后 重新计算白条分期 每期的价格 end
            }

            $fittings_list = $this->goodsFittingService->getGoodsFittings([$goods_id], $warehouse_id, $area_id, $area_city);

            if ($fittings_list) {
                $fittings_attr = $attr_id;

                $goods_fittings = $this->goodsFittingService->getGoodsFittingsInfo($goods_id, $warehouse_id, $area_id, $area_city, '', 1, '', $fittings_attr);

                if (is_array($fittings_list)) {
                    foreach ($fittings_list as $vo) {
                        $fittings_index[$vo['group_id']] = $vo['group_id'];//关联数组
                    }
                }
                ksort($fittings_index);//重新排序

                $merge_fittings = $this->goodsFittingService->getMergeFittingsArray($fittings_index, $fittings_list); //配件商品重新分组
                $fitts = $this->goodsFittingService->getFittingsArrayList($merge_fittings, $goods_fittings);

                if ($fitts) {
                    for ($i = 0; $i < count($fitts); $i++) {
                        $fittings_interval = $fitts[$i]['fittings_interval'];

                        $res['fittings_interval'][$i]['fittings_minMax'] = $this->dscRepository->getPriceFormat($fittings_interval['fittings_min'], true, true, $goodsSelf) . "-" . number_format($fittings_interval['fittings_max'], 2, '.', '');
                        $res['fittings_interval'][$i]['market_minMax'] = $this->dscRepository->getPriceFormat($fittings_interval['market_min'], true, true, $goodsSelf) . "-" . number_format($fittings_interval['market_max'], 2, '.', '');

                        if ($fittings_interval['save_minPrice'] == $fittings_interval['save_maxPrice']) {
                            $res['fittings_interval'][$i]['save_minMaxPrice'] = $this->dscRepository->getPriceFormat($fittings_interval['save_minPrice'], true, true, $goodsSelf);
                        } else {
                            $res['fittings_interval'][$i]['save_minMaxPrice'] = $this->dscRepository->getPriceFormat($fittings_interval['save_minPrice'], true, true, $goodsSelf) . "-" . number_format($fittings_interval['save_maxPrice'], 2, '.', '');
                        }

                        $res['fittings_interval'][$i]['groupId'] = $fittings_interval['groupId'];
                    }
                } else {
                    $res['fittings_interval'] = [
                        0 => [
                            'fittings_minMax' => 0,
                            'save_minMaxPrice' => 0,
                            'groupId' => ''
                        ]
                    ];
                }
            }


            if (config('shop.open_area_goods') == 1) {
                $area_count = $this->goodsService->getHasLinkAreaGods($goods_id, $area_id, $area_city);
                if ($area_count < 1) {
                    $res['err_no'] = 2;
                }
            }

            // 开启限购 显示已购买数量
            $order_goods = [];
            if (isset($goods['is_xiangou']) && $goods['is_xiangou'] == 1) {
                $start_date = $goods['xiangou_start_date'];
                $end_date = $goods['xiangou_end_date'];
                $extension_code = $goods['is_real'] == 0 ? 'virtual_card' : '';
                $order_goods = $this->orderGoodsService->getForPurchasingGoods($start_date, $end_date, $goods_id, $user_id, $extension_code, $get_attr_id);
            }

            $res['orderG_number'] = $order_goods['goods_number'] ?? 0;
            $res['type'] = $type;

            $limit = 1;
            $area_position_list = get_goods_user_area_position($goods['user_id'], $warehouse_id, $area_id, $area_city, 0, $get_attr_id, $goods_id, 0, $district_id, 1, 0, $limit);

            if (count($area_position_list) > 0) {
                $res['store_type'] = 1;
            } else {
                $res['store_type'] = 0;
            }

            /**
             * 商品详情页 显示开通购买权益卡 条件 drp_shop 0 显示 1 不显示
             * 1. 无分销模块 不显示 is_drp = 0
             * 2. 商家商品且禁用会员权益折扣 不显示 user_id > 0 && is_discount = 0
             * 3. 会员未登录或普通会员且不是分销商 显示立即开通 drp_shop_membership_card_id = 0
             * 4. 分销商权益已过期 显示重新购买 drp_shop_membership_card_id > 0
             * 5. 禁用会员特价权益 不显示
             */
            if (file_exists(MOBILE_DRP)) {
                $res['is_drp'] = 1;// 有分销模块

                $drp_config = app(\App\Modules\Drp\Services\Drp\DrpService::class)->drpConfig();
                // 是否显示分销，控制前端页面是否显示分销模块
                $res['is_show_drp'] = (int)$drp_config['isdrp']['value'] ?? 0;

                // 商家商品且禁用会员权益折扣 不显示开通购买权益卡
                if (isset($goods['user_id']) && $goods['user_id'] > 0 && isset($goods['is_discount']) && $goods['is_discount'] == 0) {
                    $res['drp_shop'] = 1;
                } else {
                    $drp_shop = app(\App\Modules\Drp\Services\Drp\DrpShopService::class)->getDrpShop($user_id);

                    // 显示分销权益卡绑定的会员特价权益（最低折扣）; 非分销商显示开通购买权益卡，已过期分销商显示重新购买
                    if (empty($drp_shop) || ($drp_shop && $drp_shop['membership_status'] == 0)) {
                        $res['drp_shop'] = 0;
                        if ($drp_shop && $drp_shop['membership_status'] == 0) {
                            $res['drp_shop_membership_card_id'] = $drp_shop['membership_card_id'];
                        }

                        $res['membership_card_discount_price'] = app(DiscountRightsService::class)->membershipCardDiscount('discount', $goods, 1, $attr_id, $where['warehouse_id'], $where['area_id'], $where['area_city']);
                        $res['membership_card_discount_price_formated'] = $this->dscRepository->getPriceFormat($res['membership_card_discount_price'], true, true, $goodsSelf);
                        // 禁用会员特价权益 不显示开通购买
                        if (empty($res['membership_card_discount_price']) || $res['membership_card_discount_price'] == 0) {
                            $res['drp_shop'] = 1;
                        }
                    }
                }
            } else {
                $res['drp_shop'] = 1; // 不显示开通购买权益卡
                $res['is_drp'] = 0;// 没有分销模块
                $res['is_show_drp'] = 0;
            }

            return response()->json($res);
        }

        if (!empty($act) && $act == 'in_stock') {
            $res = ['err_msg' => '', 'result' => '', 'qty' => 1];

            $area_cookie = $this->areaService->areaCookie();

            $goods_id = (int)request()->input('id', 0);
            $province = (int)request()->input('province', $area_cookie['province'] ?? 0);
            $city = (int)request()->input('city', $area_cookie['city'] ?? 0);
            $district = (int)request()->input('district', $area_cookie['district'] ?? 0);
            $street = (int)request()->input('street', $area_cookie['street'] ?? 0);
            $d_null = (int)request()->input('d_null', 0);

            if (!empty($goods_id)) {
                $user_address = get_user_address_region($user_id);
                $user_address = $user_address && $user_address['region_address'] ? explode(",", $user_address['region_address']) : [];

                $street_info = Region::select('region_id')->where('parent_id', $district);
                $street_info = BaseRepository::getToArrayGet($street_info);
                $street_info = BaseRepository::getFlatten($street_info);

                $street_list = 0;
                $this->street_id = 0;

                if ($street_info) {
                    $this->street_id = $street_info[0];
                    $street_list = implode(",", $street_info);
                }

                //清空
                $time = 60 * 24 * 30;
                cookie()->queue('type_province', 0, $time);
                cookie()->queue('type_city', 0, $time);
                cookie()->queue('type_district', 0, $time);

                $res['d_null'] = $d_null;

                if ($d_null == 0) {
                    if (in_array($district, $user_address)) {
                        $res['isRegion'] = 1;
                    } else {
                        $res['message'] = $GLOBALS['_LANG']['Distribution_message'];
                        $res['isRegion'] = 88; //原为0
                    }
                } else {
                    $district = '';
                }

                /* 删除缓存 */
                $this->areaService->getCacheNameForget('area_cookie');
                $this->areaService->getCacheNameForget('area_info');
                $this->areaService->getCacheNameForget('warehouse_id');

                $area_cache_name = $this->areaService->getCacheName('area_cookie');

                $area_cookie_cache = [
                    'province' => $province,
                    'city_id' => $city,
                    'city' => $city,
                    'district' => $district,
                    'street' => $street,
                    'street_area' => $street_list
                ];
                cache()->forever($area_cache_name, $area_cookie_cache);

                $res['goods_id'] = $goods_id;

                $flow_warehouse = get_warehouse_goods_region($province);
                cookie()->queue('flow_region', $flow_warehouse['region_id'] ?? 0, 60 * 24 * 30);
            }

            return response()->json($res);
        }

        /*------------------------------------------------------ */
        //-- 切换仓库
        /*------------------------------------------------------ */
        if (!empty($act) && $act == 'in_warehouse') {
            $res = ['err_msg' => '', 'result' => '', 'qty' => 1];
            $res['warehouse_type'] = addslashes(request()->input('warehouse_type', ''));

            $warehouse_cache_name = $this->areaService->getCacheName('warehouse_id');
            cache()->forever($warehouse_cache_name, $pid);

            /* 删除缓存 */
            $this->areaService->getCacheNameForget('area_info');

            $area_region = 0;
            cookie()->queue('area_region', $area_region, 60 * 24 * 30);

            $res['goods_id'] = $goods_id;

            return response()->json($res);
        }

        /*------------------------------------------------------ */
        //-- 商品购买记录ajax处理
        /*------------------------------------------------------ */

        if (!empty($act) && $act == 'gotopage') {
            $res = ['err_msg' => '', 'result' => ''];

            $goods_id = (int)request()->input('id', 0);
            $page = (int)request()->input('page', 1);

            if (!empty($goods_id)) {
                $need_cache = $this->smarty->caching;
                $need_compile = $this->smarty->force_compile;

                $this->smarty->caching = false;
                $this->smarty->force_compile = true;

                /* 商品购买记录 */
                $where = [
                    'now' => $now,
                    'goods_id' => $goods_id
                ];

                $bought_notes = OrderGoods::select('goods_number')
                    ->whereHasIn('getOrder', function ($query) use ($where) {
                        $query = $query->where('main_count', 0);
                        $query->whereRaw("'" . $where['now'] . "' - oi.add_time < 2592000 AND og.goods_id = '" . $where['goods_id'] . "'");
                    });

                $bought_notes = $bought_notes->with([
                    'getOrder' => function ($query) {
                        $query->selectRaw("order_id, add_time, IF(order_status IN (2, 3, 4), 0, 1) AS order_status");
                    }
                ]);

                $start = (($page > 1) ? ($page - 1) : 0) * 5;
                if ($start > 0) {
                    $bought_notes = $bought_notes->skip($start);
                }

                $size = 5;
                if ($size > 0) {
                    $bought_notes = $bought_notes->take($size);
                }

                $bought_notes = BaseRepository::getToArrayGet($bought_notes);

                if ($bought_notes) {
                    foreach ($bought_notes as $key => $val) {
                        $val = $val['get_order'] ? array_merge($val, $val['get_order']) : $val;
                        $val['add_time'] = TimeRepository::getLocalDate("Y-m-d H:i:s", $val['add_time']);

                        $val['user_name'] = Users::where('user_id', $val['user_id'])->value('user_name');

                        $bought_notes[$key] = $val;
                    }
                }

                $bought_notes = OrderGoods::select('goods_number')
                    ->whereHasIn('getOrder', function ($query) use ($where) {
                        $query = $query->where('main_count', 0);
                        $query->whereRaw("'" . $where['now'] . "' - oi.add_time < 2592000 AND og.goods_id = '" . $where['goods_id'] . "'");
                    });

                $count = $bought_notes->count();

                /* 商品购买记录分页样式 */
                $pager = [];
                $pager['page'] = $page;
                $pager['size'] = $size;
                $pager['record_count'] = $count;
                $pager['page_count'] = $page_count = ($count > 0) ? intval(ceil($count / $size)) : 1;
                $pager['page_first'] = "javascript:gotoBuyPage(1,$goods_id)";
                $pager['page_prev'] = $page > 1 ? "javascript:gotoBuyPage(" . ($page - 1) . ",$goods_id)" : 'javascript:;';
                $pager['page_next'] = $page < $page_count ? 'javascript:gotoBuyPage(' . ($page + 1) . ",$goods_id)" : 'javascript:;';
                $pager['page_last'] = $page < $page_count ? 'javascript:gotoBuyPage(' . $page_count . ",$goods_id)" : 'javascript:;';

                $this->smarty->assign('notes', $bought_notes);
                $this->smarty->assign('pager', $pager);


                $res['result'] = $this->smarty->fetch('library/bought_notes.lbi');

                $this->smarty->caching = $need_cache;
                $this->smarty->force_compile = $need_compile;
            }

            return response()->json($res);
        }

        /*------------------------------------------------------ */
        //-- PROCESSOR
        /*------------------------------------------------------ */

        $area = [
            'region_id' => $warehouse_id, //仓库ID
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'district_id' => $this->district_id,
            'street_id' => $this->street_id,
            'street_list' => $this->street_list,
            'goods_id' => $goods_id,
            'user_id' => $user_id,
            'area_id' => $area_id,
            'area_city' => $area_city,
            'merchant_id' => $goods['user_id'] ?? 0,
        ];
        $this->smarty->assign('area', $area);

        if (empty($goods)) {
            /* 如果没有找到任何记录则跳回到首页 */
            return dsc_header("Location: ./\n");
        }

        assign_template('c');

        /* meta */
        $this->smarty->assign('keywords', !empty($goods['keywords']) ? htmlspecialchars($goods['keywords']) : htmlspecialchars(config('shop.shop_keywords')));
        $this->smarty->assign('description', !empty($goods['goods_brief']) ? htmlspecialchars($goods['goods_brief']) : htmlspecialchars(config('shop.shop_desc')));

        $position = assign_ur_here($goods['cat_id'], $goods['goods_name'], [], '', $goods['user_id']);

        /* current position */
        $this->smarty->assign('ur_here', $position['ur_here']);                  // 当前位置

        if ($goods['user_id'] == 0) {
            $this->smarty->assign('see_more_goods', 1);
        } else {
            $this->smarty->assign('see_more_goods', 0);
        }

        $this->smarty->assign('image_width', config('shop.image_width'));
        $this->smarty->assign('image_height', config('shop.image_height'));
        $this->smarty->assign('helps', $this->articleCommonService->getShopHelp()); // 网店帮助
        $this->smarty->assign('id', $goods_id);
        $this->smarty->assign('type', 0);
        $this->smarty->assign('cfg', $GLOBALS['_CFG']);

        $promotion = get_promotion_info($goods_id, $goods['user_id']);
        $this->smarty->assign('promotion', $promotion); //促销信息

        $consumption_count = 0;
        if ($goods['consumption']) {
            $consumption_count = 1;
        }

        $promo_count = count($promotion) + $consumption_count;
        $this->smarty->assign('promo_count', $promo_count); //促销数量

        //ecmoban模板堂 --zhuo start 限购
        $start_date = $goods['xiangou_start_date'];
        $end_date = $goods['xiangou_end_date'];

        if ($now > $start_date && $now < $end_date) {
            $xiangou = 1;
        } else {
            $xiangou = 0;
        }

        $order_goods = $this->orderGoodsService->getForPurchasingGoods($start_date, $end_date, $goods_id, $user_id);
        $this->smarty->assign('xiangou', $xiangou);
        $this->smarty->assign('orderG_number', $order_goods['goods_number']);
        //ecmoban模板堂 --zhuo end 限购

        // 最小起订量
        if ($now > $goods['minimum_start_date'] && $now < $goods['minimum_end_date']) {
            $goods['is_minimum'] = 1;
        } else {
            $goods['is_minimum'] = 0;
        }

        //ecmoban模板堂 --zhuo start
        $shop_info = get_merchants_shop_info($goods['user_id']);
        $license_comp_adress = $shop_info['license_comp_adress'] ?? '';
        $adress = get_license_comp_adress($license_comp_adress);

        $this->smarty->assign('shop_info', $shop_info);
        $this->smarty->assign('adress', $adress);
        //ecmoban模板堂 --zhuo end

        //by wang 获得商品扩展信息
        $goods['goods_extends'] = get_goods_extends($goods_id);

        //判断是否支持退货服务
        $is_return_service = 0;
        if (isset($goods['return_type']) && $goods['return_type']) {
            $fruit1 = [1, 2, 3]; //退货，换货，仅退款
            $intersection = array_intersect($fruit1, $goods['return_type']); //判断商品是否设置退货相关
            if (!empty($intersection)) {
                $is_return_service = 1;
            }
        }
        //判断是否设置包退服务  如果设置了退换货标识，没有设置包退服务  那么修正包退服务为已选择
        if ($is_return_service == 1 && isset($data['goods_extends']['is_return']) && !$goods['goods_extends']['is_return']) {
            $goods['goods_extends']['is_return'] = 1;
        }

        $linked_goods = $this->goodsService->getLinkedGoods($goods_id, $warehouse_id, $area_id, $area_city);

        $goods['goods_style_name'] = $this->goodsCommonService->addStyle($goods['goods_name'], $goods['goods_name_style']);

        //商品标签 liu
        if ($goods['goods_tag']) {
            $goods['goods_tag'] = BaseRepository::getExplode($goods['goods_tag'], ',');
        }

        /**
         * 店铺二维码
         */
        if ($goods['shopinfo']['ru_id'] > 0) {
            $shop_qrcode = $this->goodsService->getShopQrcode($goods['shopinfo']['ru_id']);
            $this->smarty->assign('shop_qrcode', $shop_qrcode);
        }

        // 商品二维码
        if (config('shop.two_code') == 1) {
            $goods_qrcode = $this->goodsService->getGoodsQrcode($goods);
            $this->smarty->assign('weixin_img_url', $goods_qrcode['url']);
            $this->smarty->assign('weixin_img_text', trim(config('shop.two_code_mouse')));
            $this->smarty->assign('two_code', config('shop.two_code'));
        }

        /*获取可用门店数量 by kong 20160721*/
        $goods['store_count'] = 0;

        $store_goods = OfflineStore::where('is_confirm', 1);
        $store_goods = $store_goods->whereHasIn('getStoreGoods', function ($query) use ($goods_id) {
            $query->where('goods_id', $goods_id);
        });
        $store_goods = $store_goods->count();

        $store_products = StoreProducts::where('goods_id', $goods_id);
        $store_products = $store_products->whereHasIn('getOfflineStore', function ($query) {
            $query->where('is_confirm', 1);
        });

        $store_products = $store_products->count();

        if ($store_goods > 0 || $store_products > 0) {
            $goods['store_count'] = 1;
        }

        /**
         * 商品分销 显示推荐可分成佣金
         */
        if (file_exists(MOBILE_DRP)) {
            $drpService = app(\App\Modules\Drp\Services\Drp\DrpService::class);

            $goods['is_drp'] = 1;// 是否有分销模块

            $drp_config = $drpService->drpConfig();
            // 是否显示分销，控制前端页面是否显示分销模块
            $goods['is_show_drp'] = $drp_config['isdrp']['value'] ?? 0;

            // 记录推荐人id
            if ($parent_id > 0) {
                $drp_affiliate = $drp_config['drp_affiliate_on']['value'] ?? 0;
                // 开启分销
                if ($drp_affiliate == 1) {
                    // 分销内购模式
                    $isdistribution = $drp_config['isdistribution']['value'] ?? 0;
                    if ($isdistribution == 2) {
                        /**
                         *  2. 自动模式
                         *  mode 1: 业绩归属 上级分销商 + 自己（条件：推荐自己微店内商品或自己推荐的链接）
                         *  mode 0：业绩归属 推荐人或上级分销商 + 自己（条件：推荐自己微店内商品或自己推荐的链接）
                         */
                        // 推荐自己微店内商品或自己推荐的链接
                        $is_drp_type = $drpService->isDrpTypeGoods($user_id, $goods['goods_id'], $goods['cat_id']);

                        // 分销业绩归属模式
                        $drp_affiliate_mode = $drp_config['drp_affiliate_mode']['value'] ?? 0;
                        if ($drp_affiliate_mode == 1) {
                            if ($is_drp_type === true || $parent_id == $user_id) {
                                CommonRepository::setDrpAffiliate($parent_id);
                            }
                        } else {
                            if ($parent_id > 0 || $is_drp_type === true || $parent_id == $user_id) {
                                CommonRepository::setDrpAffiliate($parent_id);
                            }
                        }
                    } else {
                        CommonRepository::setDrpAffiliate($parent_id);
                    }
                }

                //如有上级推荐人（分销商），且关系在有效期内，更新推荐时间 1.4.3
                $drpService->updateBindTime($user_id, $parent_id);
            }

            //计算商品的佣金显示前台
            if ($goods['is_distribution'] > 0 || $goods['membership_card_id'] > 0) {
                $goods['commission_money'] = app(\App\Modules\Drp\Services\Distribute\DistributeGoodsService::class)->calculate_goods_commossion($user_id, $goods);

                $goods['commission_money_formated'] = empty($goods['commission_money']) ? 0 : $this->dscRepository->getPriceFormat($goods['commission_money']);
            }
        } else {
            $goods['is_drp'] = 0;// 是否有分销模块
            $goods['is_show_drp'] = 0;
        }

        $this->smarty->assign('goods', $goods);

        $this->smarty->assign('goods_name', $goods['goods_name']);
        $this->smarty->assign('goods_id', $goods['goods_id']);
        $this->smarty->assign('promote_end_time', $goods['gmt_end_time']);

        //获得商品的规格和属性
        $properties = $this->goodsAttrService->getGoodsProperties($goods_id, $warehouse_id, $area_id, $area_city);

        $this->smarty->assign('properties', $properties['pro']);                              // 商品规格
        $this->smarty->assign('specification', $properties['spe']);                              // 商品属性

        $this->smarty->assign('related_goods', $linked_goods);                                   // 关联商品
        $this->smarty->assign('goods_article_list', $this->goodsService->getLinkedArticles($goods_id));                  // 关联文章

        // 会员等级价格
        $rank_prices = $this->goodsCommonService->getUserRankPrices($goods, $goods['shop_price_original'], session('user_rank'));
        $this->smarty->assign('rank_prices', $rank_prices);

        $this->smarty->assign('pictures', $this->goodsGalleryService->getGoodsGallery($goods_id));                    // 商品相册

        /**
         * 店铺分类
         */
        if ($goods['user_id']) {

            $goods_store_cat = $this->merchantCategoryService->merGoodsCatList($goods['user_id']);

            if ($goods_store_cat) {
                $goods_store_cat = array_values($goods_store_cat);
            }
            $this->smarty->assign('goods_store_cat', $goods_store_cat);
        }

        $group_count = get_group_goods_count($goods_id);
        if ($group_count) {

            //组合套餐名
            $comboTabIndex = $this->goodsFittingService->getCfgGroupGoods();
            $this->smarty->assign('comboTab', $comboTabIndex);

            //组合套餐组
            $fittings_list = $this->goodsFittingService->getGoodsFittings([$goods_id], $warehouse_id, $area_id, $area_city);

            $fittings_index = [];
            if (is_array($fittings_list)) {
                foreach ($fittings_list as $vo) {
                    $fittings_index[$vo['group_id']] = $vo['group_id']; //关联数组
                }
            }
            ksort($fittings_index); //重新排序
            $this->smarty->assign('fittings_tab_index', $fittings_index); //套餐数量

            $this->smarty->assign('fittings', $fittings_list);                   // 配件
        }

        assign_dynamic('goods');
        $volume_price_list = $this->goodsCommonService->getVolumePriceList($goods['goods_id'], 1, 1);
        $this->smarty->assign('volume_price_list', $volume_price_list);    // 商品优惠价格区间

        $discuss_list = get_discuss_all_list($goods_id, 0, 1, 10);
        $this->smarty->assign('discuss_list', $discuss_list);
        $this->smarty->assign('all_count', $discuss_list['record_count']);

        //同类其他品牌
        $goods_brand = $this->goodsService->getGoodsSimilarBrand($goods['cat_id']);
        $this->smarty->assign('goods_brand', $goods_brand);

        //相关分类
        $goods_related_cat = $this->goodsService->getGoodsRelatedCat($goods['cat_id']);
        $this->smarty->assign('goods_related_cat', $goods_related_cat);

        //评分 start
        $comment_all = $this->commentService->getCommentsPercent($goods_id);
        if ($goods['user_id'] > 0) {
            $merchants_goods_comment = $this->commentService->getMerchantsGoodsComment($goods['user_id']); //商家所有商品评分类型汇总
            $this->smarty->assign('merch_cmt', $merchants_goods_comment);
        }
        //评分 end

        $this->smarty->assign('comment_all', $comment_all);

        $goods_area = 1;
        if (config('shop.open_area_goods') == 1) {
            $area_count = $this->goodsService->getHasLinkAreaGods($goods_id, $area_id, $area_city);
            if ($area_count > 0) {
                $goods_area = 1;
            } else {
                $goods_area = 0;
            }
        }

        $this->smarty->assign('goods_area', $goods_area);

        //默认统一客服
        if (config('shop.customer_service') == 0) {
            $goods['user_id'] = 0;
            $shop_information = $this->merchantCommonService->getShopName($goods['user_id']);
        } else {
            $shop_information = $goods['shop_information'];
        }

        /*  @author-bylu 判断当前商家是否允许"在线客服" start */
        if ($shop_information) {
            //判断当前商家是平台,还是入驻商家 bylu
            if ($goods['user_id'] == 0) {
                //判断平台是否开启了IM在线客服
                $kf_im_switch = SellerShopinfo::where('ru_id', 0)->value('kf_im_switch');
                if ($kf_im_switch) {
                    $shop_information['is_dsc'] = true;
                } else {
                    $shop_information['is_dsc'] = false;
                }
            } else {
                $shop_information['is_dsc'] = false;
            }

            $this->smarty->assign('shop_information', $shop_information);

            $this->smarty->assign('shop_close', $shop_information['shop_close']);

            $this->smarty->assign('kf_appkey', $shop_information['kf_appkey']); //应用appkey;

            $shop_information['province'] = Region::where('region_id', $shop_information['province'])->value('region_name');
            $shop_information['province'] = $shop_information['province'] ? $shop_information['province'] : '';

            $shop_information['city'] = Region::where('region_id', $shop_information['city'])->value('region_name');
            $shop_information['city'] = $shop_information['city'] ? $shop_information['city'] : '';

            $this->smarty->assign('basic_info', $shop_information);
        }

        $shop_can_comment = config('shop.shop_can_comment') == 1 && $shop_information && $shop_information['shop_can_comment'] == 1 ? 1 : 0;
        $this->smarty->assign('shop_can_comment', $shop_can_comment);

        $this->smarty->assign('im_user_id', 'dsc' . $user_id); //登入用户ID;
        /*  @author-bylu  end */

        if ($rank = get_rank_info()) {
            $this->smarty->assign('rank_name', $rank['rank_name']);
        }

        $this->smarty->assign('info', $this->userCommonService->getUserDefault($user_id));

        //@author-bylu 获取当前商品白条分期数据 start
        if ($goods['stages']) {
            //计算每期价格[默认,当js失效商品详情页才会显示这里的结果](每期价格=((总价+运费)*费率)+((总价+运费)/期数));
            $stages_arr = [];
            foreach ($goods['stages'] as $k => $v) {
                $stages_arr[$v]['stages_one_price'] = round(($goods['shop_price']) * ($goods['stages_rate'] / 100) + ($goods['shop_price']) / $v, 2);
            }
            $this->smarty->assign('stages', $stages_arr);
        }
        //@author-bylu  end

        //@author-bylu 获取当前商品可使用的优惠券信息 start
        $goods_coupons = get_new_coup($user_id, $goods_id, $goods['user_id']);
        $this->smarty->assign('goods_coupons', $goods_coupons);
        //@author-bylu  end

        $this->smarty->assign('extend_info', get_goods_extend_info($goods_id)); //扩展信息 by wu

        $this->smarty->assign('goods_id', $goods_id); //商品ID
        $this->smarty->assign('region_id', $warehouse_id); //商品仓库region_id
        $this->smarty->assign('user_id', $user_id);
        $this->smarty->assign('area_id', $area_id); //地区ID
        $this->smarty->assign('area_city', $area_city); //市级地区ID

        $site_http = $this->dsc->http();
        if ($site_http == 'http://') {
            $is_http = 1;
        } elseif ($site_http == 'https://') {
            $is_http = 2;
        } else {
            $is_http = 0;
        }

        $this->smarty->assign('url', url('/') . '/');
        $this->smarty->assign('is_http', $is_http);


        $this->smarty->assign('freight_model', config('shop.freight_model'));
        $this->smarty->assign('one_step_buy', session('one_step_buy', 0));
        $this->smarty->assign('now_time', $now);           // 当前系统时间

        //获取seo start
        $seo = get_seo_words('goods');

        if ($seo) {
            foreach ($seo as $key => $value) {
                $seo[$key] = str_replace(['{sitename}', '{key}', '{name}', '{description}'], [config('shop.shop_name'), $goods['keywords'], $goods['goods_name'], $goods['goods_brief']], $value);
            }
        }

        if (isset($seo['keywords']) && !empty($seo['keywords'])) {
            $this->smarty->assign('keywords', htmlspecialchars($seo['keywords']));
        } else {
            $this->smarty->assign('keywords', htmlspecialchars(config('shop.shop_keywords')));
        }

        if (isset($seo['description']) && !empty($seo['description'])) {
            $this->smarty->assign('description', htmlspecialchars($seo['description']));
        } else {
            $this->smarty->assign('description', htmlspecialchars(config('shop.shop_desc')));
        }

        if (isset($seo['title']) && !empty($seo['title'])) {
            $this->smarty->assign('page_title', htmlspecialchars($seo['title']));
        } else {
            $this->smarty->assign('page_title', $position['title']);
        }
        //获取seo end

        $this->smarty->assign('area_htmlType', 'goods');

        /* 更新点击次数 */
        Goods::where('goods_id', $goods_id)->increment('click_count', 1);

        /* 浏览历史列表 */
        $this->historyService->goodsHistoryList($user_id, $goods['goods_id']);

        session([
            'goods_equal' => ''
        ]);

        /* 删除配件 start */
        $res = CartCombo::where(function ($query) use ($goods_id) {
            $query->where('parent_id', 0)
                ->where('goods_id', $goods_id)
                ->orWhere('parent_id', $goods_id);
        });

        if (!empty($user_id)) {
            $res = $res->where('user_id', $user_id);
        } else {
            $session_id = $this->sessionRepository->realCartMacIp();
            $res = $res->where('session_id', $session_id);
        }

        $res->delete();
        /* 删除配件 end */

        return $this->smarty->display('goods.dwt');
    }
}
