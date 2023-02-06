<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\CartCombo;
use App\Models\Goods;
use App\Models\GroupGoods;
use App\Models\Products;
use App\Models\ProductsArea;
use App\Models\ProductsWarehouse;
use App\Models\StoreGoods;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscEncryptRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\StrRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Flow\FlowRepository;
use App\Services\Activity\DiscountService;
use App\Services\Activity\PackageService;
use App\Services\Category\CategoryService;
use App\Services\Common\ConfigService;
use App\Services\CrossBorder\CrossBorderService;
use App\Services\Goods\GoodsAttrService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Goods\GoodsMobileService;
use App\Services\Goods\GoodsWarehouseService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\OfflineStore\OfflineStoreDataHandleService;
use App\Services\Order\OrderGoodsService;
use App\Services\Package\PackageGoodsService;
use App\Services\Store\StoreService;
use App\Services\User\UserCommonService;

/**
 * 商城商品订单
 * Class CrowdFund
 * @package App\Services
 */
class CartMobileService
{
    protected $goodsMobileService;
    protected $goodsAttrService;
    protected $commonService;
    protected $packageService;
    protected $dscRepository;
    protected $goodsCommonService;
    protected $goodsWarehouseService;
    protected $sessionRepository;
    protected $userCommonService;
    protected $cartCommonService;
    protected $orderGoodsService;
    protected $packageGoodsService;
    protected $merchantCommonService;
    protected $storeService;
    protected $categoryService;

    public function __construct(
        GoodsMobileService $goodsMobileService,
        GoodsAttrService $goodsAttrService,
        PackageService $packageService,
        DscRepository $dscRepository,
        GoodsCommonService $goodsCommonService,
        GoodsWarehouseService $goodsWarehouseService,
        SessionRepository $sessionRepository,
        UserCommonService $userCommonService,
        CartCommonService $cartCommonService,
        OrderGoodsService $orderGoodsService,
        PackageGoodsService $packageGoodsService,
        MerchantCommonService $merchantCommonService,
        StoreService $storeService,
        CategoryService $categoryService
    )
    {
        $files = [
            'clips',
            'common',
            'time',
            'main',
            'order',
            'function',
            'base',
            'goods',
            'ecmoban'
        ];
        load_helper($files);
        $this->goodsMobileService = $goodsMobileService;
        $this->goodsAttrService = $goodsAttrService;
        $this->packageService = $packageService;
        $this->dscRepository = $dscRepository;
        $this->goodsCommonService = $goodsCommonService;
        $this->goodsWarehouseService = $goodsWarehouseService;
        $this->sessionRepository = $sessionRepository;
        $this->userCommonService = $userCommonService;
        $this->cartCommonService = $cartCommonService;
        $this->orderGoodsService = $orderGoodsService;
        $this->packageGoodsService = $packageGoodsService;
        $this->merchantCommonService = $merchantCommonService;
        $this->storeService = $storeService;
        $this->categoryService = $categoryService;
    }

    /**
     * 添加商品到购物车
     *
     * @param int $uid
     * @param int $goods_id 商品编号
     * @param int $num 商品数量
     * @param array $spec 规格值对应的id数组
     * @param int $parent 基本件
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param int $store_id
     * @param string $take_time
     * @param int $store_mobile
     * @param int $rec_type
     * @param string $stages_qishu
     * @return bool|array
     * @throws \Exception
     */
    public function addToCartMobile($uid = 0, $goods_id = 0, $num = 1, $spec = [], $parent = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0, $store_id = 0, $take_time = '', $store_mobile = 0, $rec_type = CART_GENERAL_GOODS, $stages_qishu = '-1')
    {
        $uid = $uid ?? 0;

        $store_id = isset($store_id) ? $store_id : 0;
        $_parent_id = $parent;
        $stages_qishu = isset($stages_qishu) ? $stages_qishu : -1;

        /* 取得商品信息 */
        $where = [
            'goods_id' => $goods_id,
            'warehouse_id' => $warehouse_id,
            'area_id' => $area_id,
            'area_city' => $area_city,
            'uid' => $uid,
            'spec' => $spec
        ];

        //门店商品加入购物车是先清除购物车
        if ($store_id > 0) {
            $this->cartCommonService->clearStoreGoods($uid);
            if ($rec_type != CART_OFFLINE_GOODS) {
                $msg['error'] = '1';
                $msg['msg'] = lang('cart.join_cart_failed');
                return $msg;
            }
        }

        //分期购清除购物车
        if ($stages_qishu > 0) {
            $this->clearQishuGoods($uid);
        }

        //清除立即购买的其他商品
        if ($rec_type == CART_ONESTEP_GOODS) {
            $this->cartCommonService->clearCart($uid, $rec_type);
        }

        $goods = $this->goodsMobileService->getGoodsInfo($where);

        if (empty($goods)) {
            return false;
        }

        $drpUserAudit = cache('drp_user_audit_' . $uid) ?? 0;

        $drp_show_price = config('shop.drp_show_price') ?? 0;
        if (empty($drpUserAudit) && $goods['user_id'] > 0 && $drp_show_price == 1) {
            $msg['error'] = '1';
            $msg['uc_id'] = 0;
            $msg['msg'] = lang('cart.qualification_buy');
            return $msg;
        }

        //查询购物车id是否存在
        $is_spec = $this->goodsAttrService->is_spec($spec);
        if ($is_spec == true) {
            $rec_id = Cart::where('goods_id', $goods['goods_id'])->where('user_id', $uid)->where('rec_type', $rec_type);
            foreach ($spec as $key => $val) {
                $rec_id = $rec_id->whereRaw("FIND_IN_SET('$val', REPLACE(goods_attr_id, '|', ','))");
            }
            $rec_id = $rec_id->value('rec_id');
            $rec_id = $rec_id ?? 0;
        } else {
            $rec_id = Cart::where('goods_id', $goods['goods_id'])->where('user_id', $uid)->where('rec_type', $rec_type)->value('rec_id');
        }

        /* 检查商品单品限购 start */
        $xiangou = $this->xiangou_checked($goods['goods_id'], $num, $uid, 0, $rec_type, $rec_id);
        if ($xiangou['error'] == 1) {
            $msg['error'] = '1';
            $msg['msg'] = sprintf(lang('cart.xiangou_num_beyond'), $goods['xiangou_num']);
            return $msg;
        } elseif ($xiangou['error'] == 2) {
            $msg['error'] = '1';
            $msg['msg'] = sprintf(lang('cart.xiangou_num_beyond_cumulative'), $goods['xiangou_num']);
            return $msg;
        }
        /* 检查商品单品限购 end */

        // 最小起订量
        if ($goods['is_minimum'] == 1) {
            if ($goods['minimum'] > $num) {
                $msg['error'] = '1';
                $msg['msg'] = sprintf(lang('cart.is_minimum_number'), $goods['minimum']);
                return $msg;
            }
        }

        /* 如果是门店一步购物，获取门店库存 */
        if ($store_id > 0 && $rec_type == CART_OFFLINE_GOODS) {
            $goods['goods_number'] = StoreGoods::where('goods_id', $goods_id)->where('store_id', $store_id)->value('goods_number');
        }

        /* 如果是作为配件添加到购物车的，需要先检查购物车里面是否已经有基本件 */
        if ($parent > 0) {
            $cart = Cart::where('goods_id', $parent)->where('extension_code', '<>', 'package_buy');

            if (!empty($uid)) {
                $cart = $cart->where('user_id', $uid);
            }

            $cart = $cart->count();

            if ($cart == 0) {
                return false;
            }
        }

        /* 是否正在销售 */
        if (file_exists(MOBILE_DRP)) {
            // 指定购买分销权益商品
            if ($goods['membership_card_id'] == 0 && $goods['is_on_sale'] == 0) {
                return ['error' => 1, 'msg' => lang('flow.shelves_goods')];
            }
        } else {
            if ($goods['is_on_sale'] == 0) {
                return ['error' => 1, 'msg' => lang('flow.shelves_goods')];
            }
        }

        /* 不是配件时检查是否允许单独销售 */
        if (empty($parent) && $goods['is_alone_sale'] == 0) {
            return false;
        }

        /* 如果商品有规格则取规格商品信息 配件除外 */

        /* 商品仓库货品 */
        $product_info = [];
        if ($is_spec == true) {
            $product_info = $this->goodsAttrService->getProductsInfo($goods_id, $spec, $warehouse_id, $area_id, $area_city, $store_id);
        }

        /* 检查：库存 */
        if (config('shop.use_storage') == 1) {
            //商品存在规格 是货品
            if ($is_spec == true && !empty($product_info)) {
                /* 取规格的货品库存 */
                $product_info['product_number'] = $product_info['product_number'] ?? 0;
                if ($num > $product_info['product_number']) {
                    return ['error' => 1, 'msg' => lang('cart.stock_goods_null')];
                }
            } else {
                //检查：商品购买数量是否大于总库存
                if ($num > $goods['goods_number']) {
                    return ['error' => 1, 'msg' => lang('cart.number_greater_inventory')];
                }
            }
        }

        /* 计算商品的促销价格 */
        $warehouse_area['warehouse_id'] = $warehouse_id;
        $warehouse_area['area_id'] = $area_id;

        $goods_price = $this->goodsMobileService->getFinalPrice($uid, $goods_id, $num, true, $spec, $warehouse_id, $area_id, $area_city);

        $spec_price = $this->goodsAttrService->specPrice($spec, $goods_id, $warehouse_area);
        $goods_attr = $this->goodsAttrService->getGoodsAttrInfo($spec, 'pice', $warehouse_id, $area_id, $area_city);

        if ($spec) {
            $goods_attr_id = is_array($spec) ? BaseRepository::getImplode($spec) : $spec;
        } else {
            $goods_attr_id = '';
        }

        // 属性成本价、属性货号
        if ($is_spec == true && !empty($product_info)) {
            $cost_price = $product_info['product_cost_price'] ?? 0;
            $goods_sn = $product_info['product_sn'] ?? '';
        } else {
            $cost_price = $goods['cost_price'] ?? 0;
            $goods_sn = $goods['goods_sn'] ?? '';
        }

        //加入购物车
        $session_id = '';
        if (empty($uid)) {
            $session_id = $this->sessionRepository->realCartMacIp();
        }

        $time = TimeRepository::getGmTime();

        /* 初始化要插入购物车的基本件数据 */
        $parent = [
            'user_id' => $uid,
            'session_id' => $session_id,
            'goods_id' => $goods_id,
            'goods_sn' => addslashes($goods_sn),
            'product_id' => $product_info['product_id'] ?? 0,
            'goods_name' => addslashes($goods['goods_name']),
            'market_price' => $goods['market_price'],
            'goods_attr' => addslashes($goods_attr),
            'goods_attr_id' => $goods_attr_id,
            'is_real' => $goods['is_real'],
            'model_attr' => $goods['model_attr'],
            'warehouse_id' => $warehouse_id,
            'area_id' => $area_id,
            'area_city' => $area_city,
            'ru_id' => $goods['user_id'],
            'extension_code' => !is_null($goods['extension_code']) ? $goods['extension_code'] : '',
            'is_gift' => 0,
            'is_shipping' => $goods['is_shipping'],
            'rec_type' => $rec_type,
            'add_time' => $time,
            'freight' => $goods['freight'],
            'tid' => $goods['tid'],
            'shipping_fee' => $goods['shipping_fee'],
            'commission_rate' => $goods['commission_rate'],
            'store_id' => $rec_type == CART_OFFLINE_GOODS ? $store_id : 0,  // 门店id(1.4.2更新门店购物类型)
            'store_mobile' => $store_mobile,
            'cost_price' => $cost_price
        ];

        if ($take_time) {
            $parent['take_time'] = $take_time;
        }

        /* 如果该配件在添加为基本件的配件时，所设置的“配件价格”比原价低，即此配件在价格上提供了优惠， */
        /* 则按照该配件的优惠价格卖，但是每一个基本件只能购买一个优惠价格的“该配件”，多买的“该配件”不享 */
        /* 受此优惠 */
        $res = GroupGoods::where('goods_id', $goods_id)
            ->where('goods_price', $goods_price)
            ->where('parent_id', $_parent_id)
            ->orderBy('goods_price');

        $res = BaseRepository::getToArrayGet($res);

        $basic_list = [];
        if ($res) {
            foreach ($res as $row) {
                $basic_list[$row['parent_id']] = $row['goods_price'];
            }
        }

        /* 取得购物车中该商品每个基本件的数量 */
        $basic_count_list = [];
        if ($basic_list) {
            $res = Cart::select('goods_id', 'goods_number')->where('parent_id')->where('extension_code', '<>', 'package_buy');

            if (!empty($uid)) {
                $res = $res->where('user_id', $uid);
            } else {
                $res = $res->where('session_id', $session_id);
            }

            $basic_goods_id = array_keys($basic_list);
            $basic_goods_id = array_unique($basic_goods_id);

            $res = $res->whereIn('goods_id', $basic_goods_id);

            $res = BaseRepository::getToArrayGet($res);

            if ($res) {
                $goodsRes = [];
                foreach ($res as $k => $v) {
                    $goodsRes[$v['goods_id']][] = $v;
                }

                $res = BaseRepository::getArrayUnique($res, 'goods_id');

                foreach ($res as $row) {
                    $basic_count_list[$row['goods_id']] = BaseRepository::getArraySum($goodsRes[$row['goods_id']], 'goods_number');
                }
            }
        }

        /* 取得购物车中该商品每个基本件已有该商品配件数量，计算出每个基本件还能有几个该商品配件 */
        /* 一个基本件对应一个该商品配件 */
        if ($basic_count_list) {
            $res = Cart::select('parent_id', 'goods_number')->where('parent_id')->where('extension_code', '<>', 'package_buy');

            if (!empty($uid)) {
                $res = $res->where('user_id', $uid);
            } else {
                $res = $res->where('session_id', $session_id);
            }

            $basic_parent_id = array_keys($basic_count_list);
            $basic_parent_id = array_unique($basic_parent_id);

            $res = $res->whereIn('parent_id', $basic_parent_id);

            $res = BaseRepository::getToArrayGet($res);

            if ($res) {
                $goodsParentRes = [];
                foreach ($res as $k => $v) {
                    $goodsParentRes[$v['parent_id']][] = $v;
                }

                $res = BaseRepository::getArrayUnique($res, 'parent_id');

                foreach ($res as $row) {
                    $row['count'] = BaseRepository::getArraySum($goodsParentRes[$row['parent_id']], 'goods_number');
                    $basic_count_list[$row['parent_id']] -= $row['count'];
                }
            }
        }

        /* 循环插入配件 如果是配件则用其添加数量依次为购物车中所有属于其的基本件添加足够数量的该配件 */
        if ($basic_list) {
            foreach ($basic_list as $parent_id => $fitting_price) {
                /* 如果已全部插入，退出 */
                if ($num <= 0) {
                    break;
                }

                /* 如果该基本件不再购物车中，执行下一个 */
                if (!isset($basic_count_list[$parent_id])) {
                    continue;
                }

                /* 如果该基本件的配件数量已满，执行下一个基本件 */
                if ($basic_count_list[$parent_id] <= 0) {
                    continue;
                }

                /* 作为该基本件的配件插入 */
                $parent['goods_price'] = max($fitting_price, 0) + $spec_price; //允许该配件优惠价格为0
                $parent['goods_number'] = min($num, $basic_count_list[$parent_id]);
                $parent['parent_id'] = $parent_id;

                /* 添加 */
                Cart::insert($parent);

                /* 改变数量 */
                $num -= $parent['goods_number'];
            }
        }

        $new_rec_id = 0;
        $act_id = 0;

        /* 如果数量不为0，作为基本件插入 */
        if ($num > 0) {

            /* 检查该商品是否已经存在在购物车中 */
            $row = Cart::select('user_id', 'goods_number', 'stages_qishu', 'rec_id', 'extension_code', 'act_id', 'is_gift', 'goods_id', 'ru_id')
                ->where('goods_id', $goods_id)
                ->where('parent_id', 0)
                ->where('goods_attr', $goods_attr)
                ->where('extension_code', '<>', 'package_buy')
                ->where('rec_type', $rec_type)
                ->where('group_id', '');

            if ($store_id > 0) {
                $row = $row->where('store_id', $store_id);
            }
            if ($warehouse_id > 0) {
                $row = $row->where('warehouse_id', $warehouse_id);
            }

            if (!empty($uid)) {
                $row = $row->where('user_id', $uid);
            } else {
                $row = $row->where('session_id', $session_id);
            }

            $row = $row->first();

            $row = $row ? $row->toArray() : [];

            //记录购物车ID
            $new_rec_id = $row['rec_id'] ?? 0;
            $act_id = $row['act_id'] ?? 0;

            /*立即购买*/
            if ($rec_type == CART_ONESTEP_GOODS) {
                $parent['goods_price'] = $goods_price;
                $parent['goods_number'] = $num;
                $parent['parent_id'] = 0;

                $parent['rec_type'] = $rec_type;

                // 会员等级
                $user_rank = $this->userCommonService->getUserRankByUid($uid);
                $user_rank['rank_id'] = $user_rank['rank_id'] ?? 0;
                $parent['act_id'] = $this->goodsCommonService->getBestFavourableId($goods_id, $goods_price * $num, $user_rank['rank_id']); // 返回最优活动ID

                if ($new_rec_id == 0) {
                    $new_rec_id = Cart::insertGetId($parent);
                }

                /* 计算折扣 */
                $discount = compute_discount(3, [$new_rec_id], 0, 0, $uid, $user_rank['rank_id'], $rec_type);

                if (isset($discount['favourable']['act_id']) && $discount['favourable']['act_id']) {
                    Cart::where('rec_id', $new_rec_id)->update(['act_id' => $discount['favourable']['act_id']]);
                }

                if ($new_rec_id) {
                    return true;
                } else {
                    return false;
                }
            }

            if ($row) {
                //如果购物车已经有此物品，则更新
                if (!($row['stages_qishu'] != '-1' && $stages_qishu != '-1') && !($row['stages_qishu'] != '-1' && $stages_qishu == '-1') && !($row['stages_qishu'] == '-1' && $stages_qishu != '-1')) {
                    $num += $row['goods_number']; //这里是普通商品,数量进行累加;bylu
                }

                if ($is_spec == true && !empty($product_info)) {
                    $goods_storage = $product_info['product_number'] ?? 0;
                } else {
                    $goods_storage = $goods['goods_number'];
                }

                if (config('shop.use_storage') == 0 || $num <= $goods_storage) {
                    $cartOther = [
                        'goods_number' => $num,
                        'stages_qishu' => $stages_qishu,
                        'goods_price' => $goods_price,
                        'commission_rate' => $goods['commission_rate'],
                        'area_id' => $area_id,
                        'freight' => $goods['freight'],
                        'tid' => $goods['tid']
                    ];

                    $res = Cart::where('goods_id', $goods_id)
                        ->where('parent_id', 0)
                        ->where('goods_attr', $goods_attr)
                        ->where('extension_code', '<>', 'package_buy')
                        ->where('rec_type', $rec_type)
                        ->where('group_id', 0);

                    if ($warehouse_id > 0) {
                        $res = $res->where('warehouse_id', $warehouse_id);
                    }

                    if (!empty($uid)) {
                        $res = $res->where('user_id', $uid);
                    } else {
                        $res = $res->where('session_id', $session_id);
                    }

                    $res->update($cartOther);
                } else {
                    return ['error' => 1, 'msg' => lang('cart.stock_goods_null')];
                }
            } else { //购物车没有此物品，则插入
                $parent['goods_price'] = max($goods_price, 0);
                $parent['goods_number'] = $num;
                $parent['parent_id'] = 0;

                //如果分期期数不为 -1,那么即为分期付款商品;
                $parent['stages_qishu'] = $stages_qishu;

                $new_rec_id = Cart::insertGetId($parent);
            }
        }

        // 更新购物车优惠活动 start
        $fav = [];
        $is_fav = 0;

        if ($act_id == 0) {
            if ($parent['extension_code'] != 'package_buy' && $parent['is_gift'] == 0) {
                $fav = $this->getFavourable($parent['user_id'], $parent['goods_id'], $parent['ru_id'], 0, true);

                if ($fav) {
                    $is_fav = 1;
                }
            }
        }

        if ($is_fav == 1 && $new_rec_id > 0) {
            $this->cartCommonService->updateFavourableCartGoods($new_rec_id, $fav['act_id'], $uid);
        }
        // 更新购物车优惠活动 end

        return true;
    }

    /**
     * 添加超值礼包到购物车
     *
     * @param int $user_id
     * @param int $package_id
     * @param int $num
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @return mixed
     * @throws \Exception
     */
    public function addPackageToCartMobile($user_id = 0, $package_id = 0, $num = 1, $warehouse_id = 0, $area_id = 0, $area_city = 0)
    {
        $lang = lang('flow');

        /* 取得礼包信息 */
        $package = $this->packageService->getPackageInfo($package_id);
        if (empty($package)) {
            $result['error'] = 1;
            $result['message'] = $lang['goods_not_exists'];
            return $result;
        }

        if (!is_numeric($num) || intval($num) <= 0) {
            $result['error'] = 1;
            $result['message'] = $lang['invalid_number'];
        }

        /* 是否正在销售 */
        if ($package['is_on_sale'] == 0) {
            $result['error'] = 1;
            $result['message'] = $lang['not_on_sale'];
            return $result;
        }

        /* 现有库存是否还能凑齐一个礼包 */
        if (config('shop.use_storage') == '1' && $this->packageGoodsService->judgePackageStock($package_id)) {
            $result['error'] = 1;
            $result['message'] = $lang['shortage'];
            return $result;
        }

        //加入购物车
        $session_id = 0;
        if ($user_id > 0) {
            Cart::where('user_id', $user_id)->where('extension_code', 'package_buy')->delete();
        } else {
            $session_id = $this->sessionRepository->realCartMacIp();
            Cart::where('session_id', $session_id)->where('extension_code', 'package_buy')->delete();
        }

        $time = TimeRepository::getGmTime();

        $parent = [
            'user_id' => $user_id,
            'session_id' => $session_id,
            'goods_id' => $package_id,
            'goods_sn' => '',
            'goods_name' => addslashes($package['act_name']),
            'market_price' => $package['market_package'],
            'goods_price' => $package['package_price'],
            'goods_number' => $num,
            'goods_attr' => '',
            'goods_attr_id' => '',
            'warehouse_id' => $warehouse_id, // 仓库
            'area_id' => $area_id, // 仓库地区
            'area_city' => $area_city, // 仓库地区城市
            'ru_id' => $package['user_id'],
            'is_real' => $package['is_real'],
            'extension_code' => 'package_buy',
            'is_gift' => 0,
            'rec_type' => CART_PACKAGE_GOODS,
            'add_time' => $time,
            'is_checked' => 1
        ];

        /* 如果数量不为0，作为基本件插入 */
        if ($num > 0) {
            /* 检查该商品是否已经存在在购物车中 */
            $row = Cart::where('goods_id', $package_id)
                ->where('parent_id', 0)
                ->where('extension_code', 'package_buy')
                ->where('rec_type', CART_PACKAGE_GOODS)
                ->where('stages_qishu', '-1')
                ->where('store_id', 0);

            $row = $user_id ? $row->where('user_id', $user_id)->first() : $row->where('session_id', $session_id)->first();
            $row = $row ? $row->toArray() : [];

            if ($row) { //如果购物车已经有此物品，则更新
                $num += $row['goods_number'];
                if (config('shop.use_storage') == 0 || $num > 0) {
                    Cart::where('user_id', $user_id)
                        ->where('goods_id', $package_id)
                        ->where('parent_id', 0)
                        ->where('extension_code', 'package_buy')
                        ->where('rec_type', CART_PACKAGE_GOODS)
                        ->update(['goods_number' => $num]);

                    $result['error'] = 0;
                    $result['message'] = $lang['add_package_cart_success'];
                    return $result;
                } else {
                    $result['error'] = 1;
                    $result['message'] = $lang['shortage'];
                    return $result;
                }
            } else { //购物车没有此物品，则插入
                Cart::insertGetId($parent);
                $result['error'] = 0;
                $result['message'] = $lang['add_package_cart_success'];
                return $result;
            }
        }
    }

    /**
     * 添加优惠活动（赠品）到购物车
     *
     * @param array $params
     * @param int $uid
     * @return array
     * @throws \Exception
     */
    public function addGiftCart($params = [], $uid = 0)
    {
        $result = [
            'error' => 0,
            'message' => lang('cart.is_join_cart')
        ];

        $select_gift = isset($params['select_gift']) ? $params['select_gift'] : '';  //选中赠品id

        $select_gift = BaseRepository::getExplode($select_gift);
        $select_gift = BaseRepository::getArrayUnique($select_gift);
        $select_gift = DscEncryptRepository::filterValInt($select_gift);

        /* 检查是否选择了赠品 */
        if (empty($select_gift)) {
            $result['error'] = 1;
            $result['message'] = lang('shopping_flow.pls_select_gift');
            return $result;
        }

        /** 取得优惠活动信息 */
        $favourable = app(DiscountService::class)->activityInfo($params['act_id']);

        if (!empty($favourable)) {
            if ($favourable['act_type'] == FAT_GOODS) {
                $favourable['act_type_ext'] = round($favourable['act_type_ext']);
            }
        } else {
            $result['error'] = 1;
            $result['message'] = lang('cart.discount_null_exist');
            return $result;
        }

        /** 判断用户能否享受该优惠 */
        if (!$this->favourableAvailable($uid, $favourable)) {
            $result['error'] = 1;
            $result['message'] = lang('cart.not_enjoy_discount');
            return $result;
        }

        if (!empty($select_gift)) {

            /* 删除用户原有的活动商品 */
            $delCart = Cart::where('is_gift', $params['act_id']);

            if ($uid > 0) {
                $delCart = $delCart->where('user_id', $uid);
            } else {
                $session_id = $this->sessionRepository->realCartMacIp();
                $delCart = $delCart->where('session_id', $session_id);
            }

            $delCart->delete();

            /* 赠品（特惠品）优惠 */
            if ($favourable['act_type'] == FAT_GOODS) {
                /* 检查是否选择了赠品 */
                if (empty($params['select_gift'])) {
                    $result['error'] = 1;
                    $result['message'] = lang('cart.select_gift');
                    return $result;
                }

                /* 检查是否已在购物车 */
                $gift_name = [];

                $goodsname = $this->getGiftCart($uid, $select_gift, $params['act_id']);
                foreach ($goodsname as $key => $value) {
                    $gift_name[$key] = $value['goods_name'];
                }
                if (!empty($gift_name)) {
                    $result['error'] = 1;
                    $result['message'] = sprintf(lang('cart.select_gift_has_cart'), join(',', $gift_name));
                    return $result;
                }

                /* 检查数量是否超过上限 */
                $giftCartNum = count($select_gift);
                if ($favourable['act_type_ext'] <= 0 || $giftCartNum > $favourable['act_type_ext']) {
                    $result['error'] = 1;
                    $result['message'] = lang('cart.gift_number_upper_limit');
                    return $result;
                }

                $success = false;

                /* 添加赠品到购物车 */
                $goods_id = BaseRepository::getKeyPluck($favourable['gift'], 'id');
                $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'goods_sn', 'goods_name', 'market_price', 'is_real', 'is_shipping', 'model_attr', 'user_id', 'freight', 'tid', 'shipping_fee', 'cost_price']);
                foreach ($favourable['gift'] as $gift) {
                    if (in_array($gift['id'], $select_gift)) {
                        $goods = $goodsList[$gift['id']] ?? [];

                        if (!empty($goods)) {
                            // 添加参数
                            $arguments = [
                                'goods_id' => $gift['id'],
                                'user_id' => $uid,
                                'goods_sn' => $goods['goods_sn'],
                                'product_id' => !empty($product['id']) ? $product['id'] : 0,
                                'group_id' => '',
                                'goods_name' => $goods['goods_name'],
                                'market_price' => $goods['market_price'],
                                'goods_price' => $gift['price'],
                                'goods_number' => 1,
                                'goods_attr' => '',
                                'is_real' => $goods['is_real'],
                                'extension_code' => CART_GENERAL_GOODS,
                                'parent_id' => 0,
                                'rec_type' => 0,  // 普通商品
                                'is_gift' => $params['act_id'],
                                'is_shipping' => $goods['is_shipping'],
                                'can_handsel' => '',
                                'model_attr' => $goods['model_attr'],
                                'goods_attr_id' => '',
                                'ru_id' => $goods['user_id'],
                                'shopping_fee' => '',
                                'warehouse_id' => '',
                                'area_id' => '',
                                'add_time' => TimeRepository::getGmTime(),
                                'freight' => $goods['freight'],
                                'tid' => $goods['tid'],
                                'shipping_fee' => $goods['shipping_fee'],
                                'store_id' => 0,
                                'store_mobile' => '',
                                'take_time' => '',
                                'cost_price' => $goods['cost_price'],
                                'is_checked' => 1,
                            ];
                            Cart::insertGetId($arguments);
                        }
                        $success = true;
                    }
                }

                if ($success == true) {
                    $result['act_id'] = $params['act_id'];
                    $result['ru_id'] = $params['ru_id'];
                    $result['message'] = lang('cart.is_join_cart');
                    return $result;
                } else {
                    $result['error'] = 1;
                    $result['message'] = lang('cart.join_cart_failed');
                    return $result;
                }
            }

            $result['error'] = 1;
            $result['message'] = lang('cart.join_cart_failed');
        }

        return $result;
    }

    /**
     * 积分团购等添加商品到购物车
     *
     * @param int $uid
     * @param int $goods_id
     * @param int $num
     * @param array $spec
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param int $store_id
     * @param int $rec_type
     * @return bool
     * @throws \Exception
     */
    public function addEspeciallyToCartMobile($uid = 0, $goods_id = 0, $num = 1, $spec = [], $warehouse_id = 0, $area_id = 0, $area_city = 0, $store_id = 0, $rec_type = 0)
    {
        /* 取得商品信息 */
        $where = [
            'goods_id' => $goods_id,
            'warehouse_id' => $warehouse_id,
            'area_id' => $area_id,
            'area_city' => $area_city,
            'uid' => $uid
        ];
        $goods = $this->goodsMobileService->getGoodsInfo($where);

        if (empty($goods)) {
            return false;
        }

        /* 是否正在销售 */
        if ($goods['is_on_sale'] == 0) {
            return false;
        }

        /* 如果商品有规格则取规格商品信息 配件除外 */

        /* 商品仓库货品 */
        $is_spec = $this->goodsAttrService->is_spec($spec);
        $product_info = [];
        if ($is_spec == true) {
            $product_info = $this->goodsAttrService->getProductsInfo($goods_id, $spec, $warehouse_id, $area_id, $area_city, $store_id);
        }

        /* 检查：库存 */
        if (config('shop.use_storage') == 1) {
            //商品存在规格 是货品
            if ($is_spec == true && !empty($product_info)) {
                /* 取规格的货品库存 */
                $product_info['product_number'] = $product_info['product_number'] ?? 0;
                if ($num > $product_info['product_number']) {
                    return false;
                }
            } else {
                //检查：商品购买数量是否大于总库存
                if ($num > $goods['goods_number']) {
                    return false;
                }
            }
        }

        /* 计算商品的促销价格 */
        $warehouse_area['warehouse_id'] = $warehouse_id;
        $warehouse_area['area_id'] = $area_id;

        $goods_attr = $this->goodsAttrService->getGoodsAttrInfo($spec, 'pice', $warehouse_id, $area_id, $area_city);

        if ($spec) {
            $goods_attr_id = is_array($spec) ? BaseRepository::getImplode($spec) : $spec;
        } else {
            $goods_attr_id = '';
        }

        // 属性成本价
        if ($is_spec == true && !empty($product_info)) {
            $cost_price = $product_info['product_cost_price'] ?? 0;
            $goods_sn = $product_info['product_sn'] ?? '';
        } else {
            $cost_price = $goods['cost_price'] ?? 0;
            $goods_sn = $goods['goods_sn'] ?? '';
        }

        /* 初始化要插入购物车的基本件数据 */
        $parent = [
            'user_id' => $uid,
            'goods_id' => $goods_id,
            'goods_sn' => addslashes($goods_sn),
            'product_id' => $product_info['product_id'] ?? 0,
            'goods_name' => addslashes($goods['goods_name']),
            'market_price' => $goods['marketPrice'],
            'goods_attr' => addslashes($goods_attr),
            'goods_attr_id' => $goods_attr_id,
            'is_real' => $goods['is_real'],
            'model_attr' => $goods['model_attr'],
            'warehouse_id' => $warehouse_id,
            'area_id' => $area_id,
            'ru_id' => $goods['user_id'],
            'extension_code' => $goods['extension_code'],
            'is_gift' => 0,
            'is_shipping' => $goods['is_shipping'],
            'rec_type' => $rec_type,
            'add_time' => TimeRepository::getGmTime(),
            'freight' => $goods['freight'],
            'tid' => $goods['tid'],
            'shipping_fee' => $goods['shipping_fee'],
            'commission_rate' => $goods['commission_rate'] ?? 0,
            'store_id' => $store_id,
            'cost_price' => $cost_price,
        ];

        /* 如果数量不为0，作为基本件插入 */
        if ($num > 0) {
            /* 检查该商品是否已经存在在购物车中 */

            $row = Cart::select('goods_number', 'stages_qishu', 'rec_id')
                ->where('goods_id', $goods_id)
                ->where('parent_id', 0)
                ->where('goods_attr', $goods_attr)
                ->where('extension_code', '<>', 'package_buy')
                ->where('rec_type', $rec_type)
                ->where('group_id', '');

            if ($store_id > 0) {
                $row = $row->where('store_id', $store_id);
            }

            if (!empty($uid)) {
                $row = $row->where('user_id', $uid);
            }

            $row = $row->first();

            $row = $row ? $row->toArray() : [];

            //购物车没有此物品，则插入
            $goods_price = $this->goodsMobileService->getFinalPrice($uid, $goods_id, $num, true, $spec, $warehouse_id, $area_id, $area_city);
            $parent['goods_price'] = max($goods_price, 0);
            $parent['goods_number'] = $num;
            $parent['parent_id'] = 0;

            //如果分期期数不为 -1,那么即为分期付款商品;bylu
            $parent['stages_qishu'] = -1;

            if ($row) {
                Cart::where('rec_id', $row['rec_id'])->update($parent);
            } else {
                Cart::insertGetId($parent);
            }
        }

        return true;
    }

    /**
     * 购物车商品
     *
     * @param array $where
     * @return array
     * @throws \Exception
     */
    public function getGoodsCartListMobile($where = [])
    {
        $source_domestic = ConfigService::searchSourceDomestic();

        $user_id = isset($where['uid']) && !empty($where['uid']) ? intval($where['uid']) : 0;

        $where['rec_type'] = isset($where['rec_type']) ? $where['rec_type'] : CART_GENERAL_GOODS;

        $res = Cart::where('rec_type', $where['rec_type']);

        $sess = $this->sessionRepository->realCartMacIp();

        if (empty($sess)) {
            // 首次访问购物车，此时不允许显示购物车的商品
            $res = $res->where('user_id', -1);
        } else {
            if ($user_id > 0) {
                // 登录用户优先使用user_id条件筛选商品
                $res = $res->where('user_id', $user_id);
            } else {
                $res = $res->where('session_id', $sess);
            }
        }
        $rec_id = 0;
        /* 附加查询条件 start */
        if (isset($where['rec_id']) && $where['rec_id']) {
            $where['rec_id'] = !is_array($where['rec_id']) ? explode(",", $where['rec_id']) : $where['rec_id'];
            $res = $res->whereIn('rec_id', $where['rec_id']);
            $rec_id = $where['rec_id'];
        }

        if (isset($where['goods_id']) && $where['goods_id']) {
            $where['goods_id'] = !is_array($where['goods_id']) ? explode(",", $where['goods_id']) : $where['goods_id'];

            $res = $res->whereIn('goods_id', $where['goods_id']);
        }

        $where['stages_qishu'] = $where['stages_qishu'] ?? -1;
        $res = $res->where('stages_qishu', $where['stages_qishu']);

        $where['store_id'] = $where['store_id'] ?? 0;
        $res = $res->where('store_id', $where['store_id']);

        if (isset($where['extension_code'])) {
            $res = $res->where('extension_code', '<>', $where['extension_code']);
        }

        if (isset($where['parent_id'])) {
            $res = $res->where('parent_id', $where['parent_id']);
        }

        if (isset($where['is_gift'])) {
            $res = $res->where('is_gift', $where['is_gift']);
        }
        /* 附加查询条件 end */

        if (isset($where['limit']) && $where['limit']) {
            $res = $res->take($where['limit']);
        }

        $res = $res->orderByRaw("group_id DESC, parent_id ASC, rec_id DESC");

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {

            $cartIdList = FlowRepository::cartGoodsAndPackage($res);
            $goods_id = $cartIdList['goods_id']; //普通商品ID

            $goodsOther = [
                'goods_id',
                'goods_thumb',
                'model_price',
                'integral',
                'is_on_sale',
                'cat_id',
                'brand_id',
                'goods_number as product_number',
                'shop_price as rec_shop_price',
                'promote_price as rec_promote_price',
                'promote_start_date',
                'promote_end_date',
                'is_promote',
                'model_attr',
                'is_delete',
                'is_minimum',
                'minimum_start_date',
                'minimum_end_date',
                'minimum',
                'free_rate',
                'is_discount',
                'user_id',
                'xiangou_start_date',
                'xiangou_end_date',
                'is_xiangou',
                'xiangou_num'
            ];
            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, $goodsOther);
            $collectGoodsList = GoodsDataHandleService::CollectGoodsDataList($goods_id, ['goods_id', 'is_attention', 'user_id']);

            $consumptionList = GoodsDataHandleService::GoodsConsumptionDataList($goods_id);

            $recGoodsModelList = BaseRepository::getColumn($res, 'model_attr', 'rec_id');
            $recGoodsModelList = $recGoodsModelList ? array_unique($recGoodsModelList) : [];

            $isModel = 0;
            if (in_array(1, $recGoodsModelList) || in_array(2, $recGoodsModelList)) {
                $isModel = 1;
            }

            if ($isModel == 1) {
                $warehouseGoodsList = GoodsDataHandleService::getWarehouseGoodsDataList($goods_id);
                $warehouseAreaGoodsList = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods_id);
            } else {
                $warehouseGoodsList = [];
                $warehouseAreaGoodsList = [];
            }

            // 商品活动标签
            $merchantUseGoodsLabelList = GoodsDataHandleService::gettMerchantUseGoodsLabelDataList($goods_id, 1);
            $merchantNoUseGoodsLabelList = GoodsDataHandleService::getMerchantNoUseGoodsLabelDataList($goods_id, 1);

            $product_id = BaseRepository::getKeyPluck($res, 'product_id');
            $productsList = GoodsDataHandleService::getProductsDataList($product_id, '*', 'product_id');

            $whereStore = [
                'goods_id' => $goods_id,
                'is_confirm' => 1,
                'district' => $where['district_id'] ?? 0
            ];
            $storeGoodsCount = OfflineStoreDataHandleService::getStoreGoodsCount($whereStore);
            $storeGoodsProductCount = OfflineStoreDataHandleService::getStoreGoodsProductCount($whereStore);

            if ($isModel == 1) {
                $productsWarehouseList = GoodsDataHandleService::getProductsWarehouseDataList($product_id, 0, '*', 'product_id');
                $productsAreaList = GoodsDataHandleService::getProductsAreaDataList($product_id, 0, '*', 'product_id');
            } else {
                $productsWarehouseList = [];
                $productsAreaList = [];
            }

            $productsGoodsAttrList = [];
            if ($productsList || $productsWarehouseList || $productsAreaList) {
                $productsGoodsAttr = BaseRepository::getKeyPluck($productsList, 'goods_attr');
                $productsGoodsAttr = BaseRepository::getArrayUnique($productsGoodsAttr);

                $productsWarehouseGoodsAttr = BaseRepository::getKeyPluck($productsList, 'goods_attr');
                $productsWarehouseGoodsAttr = BaseRepository::getArrayUnique($productsWarehouseGoodsAttr);

                $productsAreaGoodsAttr = BaseRepository::getKeyPluck($productsList, 'goods_attr');
                $productsAreaGoodsAttr = BaseRepository::getArrayUnique($productsAreaGoodsAttr);

                $productsGoodsAttr = BaseRepository::getArrayMerge($productsGoodsAttr, $productsWarehouseGoodsAttr);
                $productsGoodsAttr = BaseRepository::getArrayMerge($productsGoodsAttr, $productsAreaGoodsAttr);

                $productsGoodsAttrList = GoodsDataHandleService::getGoodsAttrDataList($productsGoodsAttr, ['goods_attr_id', 'attr_img_flie', 'attr_gallery_flie']);
            }

            $time = TimeRepository::getGmTime();

            /* 处理更新价格 */
            $payPriceList = $this->cartCommonService->cartFinalPrice($user_id, $res, $goodsList, $warehouseGoodsList, $warehouseAreaGoodsList, $productsList, $productsWarehouseList, $productsAreaList);

            $seller_id = BaseRepository::getKeyPluck($res, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($seller_id);

            $stepsFieldsList = [];
            if (CROSS_BORDER === true) { // 跨境多商户
                $stepsFieldsList = MerchantDataHandleService::getMerchantsStepsFieldsDataList($seller_id, ['user_id', 'source']);
            }

            foreach ($res as $k => $v) {

                $cart_user_id = $v['user_id'];

                $shop_information = $merchantList[$v['ru_id']] ?? []; //通过ru_id获取到店铺信息;

                $is_kj = 0;
                if (CROSS_BORDER === true) { // 跨境多商户
                    $source = $stepsFieldsList[$v['ru_id']]['source'] ?? '';
                    if (!empty($source) && !in_array($source, [$source_domestic])) {
                        $is_kj = 1;
                    }
                }

                $res[$k]['subtotal'] = $v['goods_price'] * $v['goods_number'];

                $goods = $goodsList[$v['goods_id']] ?? [];

                if (isset($goods['user_id'])) {
                    $goods['ru_id'] = $goods['user_id'];
                    unset($goods['user_id']);
                }

                $v = BaseRepository::getArrayMerge($v, $goods);

                $res[$k]['is_kj'] = $is_kj;
                $res[$k]['rate_price'] = 0;
                $res[$k]['format_rate_price'] = $this->dscRepository->getPriceFormat($res[$k]['rate_price']);

                $res[$k]['free_rate'] = $v['free_rate'] ?? 0;
                $res[$k]['product_number'] = $v['product_number'] ?? 0;
                if ($v['extension_code'] != 'package_buy') {

                    $warehouseGoods = $warehouseGoodsList[$v['goods_id']] ?? [];
                    $warehouseAreaGoods = $warehouseAreaGoodsList[$v['goods_id']] ?? [];

                    //重新查询商品的限购数量限制
                    $xiangou = $this->xiangou_checked($v['goods_id'], $v['goods_number'], $user_id, 1, CART_GENERAL_GOODS, $rec_id);
                    $res[$k]['xiangou_error'] = 0;

                    $res[$k]['xiangou_start_date'] = $v['xiangou_start_date'];
                    $res[$k]['xiangou_end_date'] = $v['xiangou_end_date'];
                    $res[$k]['is_xiangou'] = $v['is_xiangou'];
                    $res[$k]['xiangou_num'] = $v['xiangou_num'];
                    $res[$k]['current_time'] = TimeRepository::getGmTime();

                    if ($xiangou['error'] == 1) {
                        $res[$k]['xiangou_error'] = 1;
                        $res[$k]['xiangou_can_buy_num'] = $xiangou['can_buy_num'] ?? 0;
                    } elseif ($xiangou['error'] == 2) {
                        $res[$k]['xiangou_error'] = 1;
                        $res[$k]['xiangou_can_buy_num'] = $xiangou['can_buy_num'] ?? 0;
                    }

                    // 最小起订量
                    if ($time >= $v['minimum_start_date'] && $time <= $v['minimum_end_date'] && $v['is_minimum']) {
                        $res[$k]['is_minimum'] = 1;
                        $res[$k]['minimum'] = $v['minimum'];
                    } else {
                        $res[$k]['is_minimum'] = 0;
                        $res[$k]['minimum'] = 0;
                    }

                    /* 商品仓库消费积分 start */
                    if (isset($v['model_price'])) {
                        if (isset($where['warehouse_id']) && $v['model_price'] == 1 && $warehouseGoods) {
                            $warehouseWhere = [
                                'where' => [
                                    [
                                        'name' => 'region_id',
                                        'value' => $v['warehouse_id']
                                    ]
                                ]
                            ];

                            $warehouseGoods = BaseRepository::getArraySqlFirst($warehouseGoods, $warehouseWhere);

                            $v['integral'] = $warehouseGoods['pay_integral'] ?? 0;
                        } elseif ($v['model_price'] == 2 && $warehouseAreaGoods) {
                            $warehouseAreaWhere = [
                                'where' => [
                                    [
                                        'name' => 'region_id',
                                        'value' => $v['area_id']
                                    ]
                                ]
                            ];

                            $warehouseAreaGoods = BaseRepository::getArraySqlFirst($warehouseAreaGoods, $warehouseAreaWhere);

                            $v['integral'] = $warehouseAreaGoods['pay_integral'] ?? 0;
                        }
                    }
                    /* 商品仓库消费积分 end */

                    // 获取库存
                    if (!empty($v['product_id'])) { // 属性库存
                        if ($v['model_attr'] == 1) {
                            $prod = $productsWarehouseList[$v['product_id']] ?? [];
                            $res[$k]['product_number'] = $prod['product_number'] ?? 0;
                            $res[$k]['product_goods_attr'] = $prod['goods_attr'] ?? 0;
                        } elseif ($v['model_attr'] == 2) {
                            $prod = $productsAreaList[$v['product_id']] ?? [];
                            $res[$k]['product_number'] = $prod['product_number'] ?? 0;
                            $res[$k]['product_goods_attr'] = $prod['goods_attr'] ?? 0;
                        } else {
                            $prod = $productsList[$v['product_id']] ?? [];
                            $res[$k]['product_number'] = $prod['product_number'] ?? 0;
                            $res[$k]['product_goods_attr'] = $prod['goods_attr'] ?? 0;
                        }
                    }

                    // 更新购物车商品价格 - 普通商品
                    if (in_array($v['rec_type'], [CART_GENERAL_GOODS, CART_ONESTEP_GOODS]) && $v['extension_code'] != 'package_buy' && $v['is_gift'] == 0 && $v['parent_id'] == 0) {
                        $goods_price = $payPriceList[$v['rec_id']]['pay_price'] ?? 0;
                        if ($v['goods_price'] != $goods_price) {
                            CartCommonService::getUpdateCartPrice($goods_price, $v['rec_id']);
                            $v['goods_price'] = $goods_price;
                        }
                    }

                    /* 商品关注度 */
                    $sql = [
                        'where' => [
                            [
                                'name' => 'goods_id',
                                'value' => $v['goods_id'],
                            ],
                            [
                                'name' => 'is_attention',
                                'value' => 1
                            ],
                            [
                                'name' => 'user_id',
                                'value' => $user_id
                            ]
                        ]
                    ];

                    $collect_list = BaseRepository::getArraySqlGet($collectGoodsList, $sql, 1);
                    $collect_count = BaseRepository::getArrayCount($collect_list);
                    $res[$k]['is_collect'] = $collect_count > 0 ? 1 : 0;
                } else {
                    $res[$k]['is_collect'] = 0;
                }

                $res[$k]['activity'] = [];

                //判断商品类型，如果是超值礼包则修改链接和缩略图
                if ($v['extension_code'] == 'package_buy') {
                    /* 取得礼包信息 */
                    $package = $this->packageService->getPackageInfo($v['goods_id']);

                    if (empty($package)) {
                        unset($res[$k]);
                        continue;
                    }

                    $v['goods_thumb'] = $package['activity_thumb'] ?? '';
                    $package_goods_list = $package['goods_list'] ?? [];
                    if ($package_goods_list) {
                        $res[$k]['product_number'] = min(array_column($package_goods_list, 'product_number'));
                    }
                    $res[$k]['package_goods_list'] = $package_goods_list;
                }

                $res[$k]['integral_total'] = isset($v['integral']) ? $v['integral'] * $v['goods_number'] : 0;

                $res[$k]['goods_thumb'] = $this->dscRepository->getImagePath($v['goods_thumb']);

                $goods_attr_id = $res[$k]['product_goods_attr'] ?? '';
                $goods_attr_id = BaseRepository::getExplode($goods_attr_id, '|');
                $res[$k]['goods_thumb'] = $this->goodsAttrService->cartGoodsAttrImage($goods_attr_id, $productsGoodsAttrList, $res[$k]['goods_thumb']);

                $res[$k]['cat_id'] = $v['cat_id'] ?? 0;
                $res[$k]['brand_id'] = $v['brand_id'] ?? 0;

                $res[$k]['short_name'] = config('shop.goods_name_length') > 0 ? $this->dscRepository->subStr($v['goods_name'], config('shop.goods_name_length')) : $v['goods_name'];
                $res[$k]['goods_number'] = $v['goods_number'];
                $res[$k]['goods_name'] = $v['goods_name'];
                $res[$k]['goods_price'] = StrRepository::priceFormat($v['goods_price']);

                $goodsSelf = false;
                if ($v['ru_id'] == 0) {
                    $goodsSelf = true;
                }

                $res[$k]['goods_price'] = $this->dscRepository->getPriceFormat($res[$k]['goods_price'], true, false, $goodsSelf);
                $res[$k]['market_price'] = $this->dscRepository->getPriceFormat($v['market_price'], true, false, $goodsSelf);

                $res[$k]['goods_price_format'] = $this->dscRepository->getPriceFormat($v['goods_price'], true, true, $goodsSelf);
                $res[$k]['market_price_format'] = $this->dscRepository->getPriceFormat($v['market_price'], true, true, $goodsSelf);
                $res[$k]['warehouse_id'] = $v['warehouse_id'];
                $res[$k]['area_id'] = $v['area_id'];
                $res[$k]['rec_id'] = $v['rec_id'];
                $res[$k]['is_checked'] = ($v['is_checked'] == 1) ? 1 : 0;
                $res[$k]['extension_code'] = $v['extension_code'];
                $res[$k]['is_gift'] = $v['is_gift'];
                $res[$k]['parent_id'] = $v['parent_id'];
                $res[$k]['is_on_sale'] = isset($v['is_on_sale']) ? $v['is_on_sale'] : 0;
                $res[$k]['is_invalid'] = $v['is_invalid'];
                if (isset($v['is_delete']) && $v['is_delete'] == 1) {
                    $res[$k]['is_invalid'] = 1;
                }

                $res[$k]['store_id'] = $v['ru_id'];
                $res[$k]['store_name'] = $shop_information['shop_name'] ?? '';

                //门店自提
                $res[$k]['store_count'] = 0;
                if ($v['extension_code'] != 'package_buy') {
                    $storeCount = $storeGoodsCount[$v['goods_id']] ?? [];
                    $storeProductCount = $storeGoodsProductCount[$v['goods_id']] ?? [];
                    $res[$k]['store_count'] = isset($storeCount['store_count']) && !empty($storeCount['store_count']) ? $storeCount['store_count'] : $storeProductCount['store_count'] ?? 0;

                    $goods_consumption = $consumptionList[$v['goods_id']] ?? [];
                    if (!empty($goods_consumption)) {
                        $res[$k]['amount'] = $this->dscRepository->getGoodsConsumptionPrice($goods_consumption, $res[$k]['subtotal']);
                    } else {
                        $res[$k]['amount'] = $res[$k]['subtotal'];
                    }

                    $res[$k]['dis_amount'] = $res[$k]['subtotal'] - $res[$k]['amount'];
                    $res[$k]['dis_amount'] = number_format($res[$k]['dis_amount'], 2, '.', '');
                    $res[$k]['discount_amount'] = $this->dscRepository->getPriceFormat($res[$k]['dis_amount'], false);
                } else {
                    $res[$k]['dis_amount'] = 0;
                    $res[$k]['dis_amount'] = number_format($res[$k]['dis_amount'], 2, '.', '');
                    $res[$k]['discount_amount'] = $this->dscRepository->getPriceFormat($res[$k]['dis_amount'], false);
                }

                /* 购物车会员ID */
                $res[$k]['user_id'] = $cart_user_id;

                // 活动标签
                $shop_information = $merchantList[$v['ru_id']] ?? []; //通过ru_id获取到店铺信息;
                $where = [
                    'user_id' => $v['ru_id'],
                    'goods_id' => $v['goods_id'],
                    'self_run' => $shop_information['self_run'] ?? 0,
                ];
                $goods_label_all = $this->goodsCommonService->getListGoodsLabelList($merchantUseGoodsLabelList, $merchantNoUseGoodsLabelList, $where);

                $res[$k]['goods_label'] = $goods_label_all['goods_label'] ?? [];
                $res[$k]['goods_label_suspension'] = $goods_label_all['goods_label_suspension'] ?? [];

                $res[$k]['country_icon'] = $shop_information['country_icon'] ?? '';
                $res[$k]['cross_warehouse_name'] = $shop_information['cross_warehouse_name'] ?? '';
            }

            /* 检查是否含有配件 */
            $grouplist = BaseRepository::getKeyPluck($res, 'group_id');
            $grouplist = ArrRepository::getArrayUnset($grouplist);

            if ($grouplist) {
                //过滤商品的配件 配件的数组新增到配件主商品的商品数组中
                $res = $this->cartCommonService->cartGoodsGroupList($res);
            }

            $result = [];
            foreach ($res as $key => $value) {
                $result[$value['store_id']][] = $value;
            }

            $ret = array();

            foreach ($result as $key => $value) {
                array_push($ret, $value);
            }

            $ruList = BaseRepository::getKeyPluck($res, 'ru_id');
            $ruCoupunsList = CartDataHandleService::getCartRuCoupunsList($ruList, ['cou_id', 'ru_id']);

            foreach ($ret as $k => $v) {
                $arr[$k]['store_name'] = $v[0]['store_name']; //店铺名称
                $arr[$k]['store_id'] = $v[0]['store_id']; //店铺ID

                $checked_num = BaseRepository::getArraySum($v, 'is_checked');
                $goods_num = BaseRepository::getArrayCount($v);

                if ($goods_num == $checked_num) {
                    $arr[$k]['checked'] = true;
                } else {
                    $arr[$k]['checked'] = false;
                }

                $arr[$k]['coupuns_num'] = BaseRepository::getArrayCount($ruCoupunsList[$v[0]['store_id']] ?? []); // 店铺下优惠券数量
                $arr[$k]['user_id'] = $user_id; //会员ID
                $arr[$k]['goods'] = $v;

                if (CROSS_BORDER === true) { // 跨境多商户
                    $cbec = app(CrossBorderService::class)->cbecExists();

                    $arr[$k]['rate_arr'] = [];
                    if (!empty($cbec)) {
                        $arr[$k]['rate_arr'] = $cbec->get_rec_rate_arr($arr[$k]);
                    }

                    if ($arr[$k]['rate_arr']) {
                        foreach ($v as $item => $rate) {

                            if ($arr) {
                                $sql = [
                                    'where' => [
                                        [
                                            'name' => 'rec_id',
                                            'value' => $rate['rec_id']
                                        ]
                                    ]
                                ];
                                $rateGoods = BaseRepository::getArraySqlFirst($arr[$k]['rate_arr'], $sql);

                                $v[$item]['rate_price'] = $rateGoods['rate_price'] ?? 0;
                                $v[$item]['format_rate_price'] = $this->dscRepository->getPriceFormat($v[$item]['rate_price']);
                            }
                        }
                    }


                    $arr[$k]['goods'] = $v;
                }
            }
        }

        return $arr;
    }

    /**
     * 根据购物车判断是否可以享受某优惠活动
     *
     * @param $user_id
     * @param $favourable
     * @param array $act_sel_id
     * @param int $ru_id
     * @return bool
     * @throws \Exception
     */
    private function favourableAvailable($user_id, $favourable, $act_sel_id = array(), $ru_id = -1)
    {
        /* 会员等级是否符合 */
        $user_rank = $this->userCommonService->getUserRankByUid($user_id);
        if (!$user_rank) {
            $user_rank['rank_id'] = 0;
        }
        if (strpos(',' . $favourable['user_rank'] . ',', ',' . $user_rank['rank_id'] . ',') === false) {
            return false;
        }

        /* 优惠范围内的商品总额 */
        $amount = $this->cartFavourableAmount($user_id, $favourable, $act_sel_id, $ru_id);

        /* 金额上限为0表示没有上限 */
        return $amount >= $favourable['min_amount'] && ($amount <= $favourable['max_amount'] || $favourable['max_amount'] == 0);
    }

    /**
     * 购物车中是否已经有某优惠
     *
     * @param $favourable
     * @param $cart_favourable
     * @return bool
     */
    public function favourableUsed($favourable, $cart_favourable)
    {
        if ($favourable['act_type'] == FAT_GOODS) {
            return isset($cart_favourable[$favourable['act_id']]) && $cart_favourable[$favourable['act_id']] >= $favourable['act_type_ext'] && $favourable['act_type_ext'] > 0;
        } else {
            return isset($cart_favourable[$favourable['act_id']]);
        }
    }

    /**
     * 查询购物车商赠品数量
     *
     * @param int $id
     * @param int $goods_id
     * @return mixed
     */
    public function goodsNumInCartGift($id = 0, $goods_id = 0)
    {
        $cart_list = Cart::where('user_id', $id)
            ->where('goods_id', $goods_id)
            ->where('is_gift', '>', 0)
            ->sum('goods_number');

        return $cart_list;
    }

    /**
     * 优惠范围内的商品总额
     *
     * @param int $user_id
     * @param array $favourable
     * @param array $act_sel_id
     * @param int $ru_id
     * @return int
     * @throws \Exception
     */
    public function cartFavourableAmount($user_id = 0, $favourable = [], $act_sel_id = ['act_sel_id' => '', 'act_pro_sel_id' => '', 'act_sel' => ''], $ru_id = -1)
    {
        $res = Cart::select('goods_price', 'goods_number', 'goods_id')->where('user_id', $user_id)
            ->whereIn('rec_type', [CART_GENERAL_GOODS, CART_ONESTEP_GOODS])
            ->where('is_gift', 0)
            ->where('is_checked', 1);

        if ($favourable['userFav_type'] == 0) {
            $res = $res->where('ru_id', $favourable['user_id']);
        } else {
            if ($ru_id > -1) {
                $res = $res->where('ru_id', $ru_id);
            }
        }
        if (!empty($act_sel_id['act_sel']) && ($act_sel_id['act_sel'] == 'cart_sel_flag')) {
            $sel_id_list = BaseRepository::getExplode($act_sel_id['act_sel_id']);
            $res = $res->whereIn('rec_id', $sel_id_list);
        }

        $res = BaseRepository::getToArrayGet($res);

        $id_list = [];
        $favourable_goods_id = [];
        if ($favourable) {

            $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');
            $goodsList = Goods::select('goods_id')
                ->whereIn('goods_id', $goods_id);

            /* 根据优惠范围修正sql */
            if ($favourable['act_range'] == FAR_ALL) {
                // sql do not change
                $goodsList = BaseRepository::getToArrayGet($goodsList);
                $favourable_goods_id = BaseRepository::getKeyPluck($goodsList, 'goods_id');
            } elseif ($favourable['act_range'] == FAR_CATEGORY) {

                /* 取得优惠范围分类的所有下级分类 */
                $cat_list = explode(',', $favourable['act_range_ext']);
                if ($cat_list) {
                    foreach ($cat_list as $id) {

                        $id = (int)$id;

                        $id_list = $this->categoryService->getCatListChildren($id);
                    }

                    $id_list = BaseRepository::getExplode($id_list);
                    $goodsList = $goodsList->whereIn('cat_id', $id_list);
                }

                $goodsList = BaseRepository::getToArrayGet($goodsList);
                $favourable_goods_id = BaseRepository::getKeyPluck($goodsList, 'goods_id');

            } elseif ($favourable['act_range'] == FAR_BRAND) {

                $id_list = $favourable['act_range_ext'];
                if ($id_list) {
                    $id_list = BaseRepository::getExplode($id_list);
                    $goodsList = $goodsList->whereIn('brand_id', $id_list);
                }

                $goodsList = BaseRepository::getToArrayGet($goodsList);
                $favourable_goods_id = BaseRepository::getKeyPluck($goodsList, 'goods_id');

            } elseif ($favourable['act_range'] == FAR_GOODS) {
                $id_list = $favourable['act_range_ext'];

                if ($id_list) {
                    $id_list = BaseRepository::getExplode($id_list);
                    $goodsList = $goodsList->whereIn('goods_id', $id_list);
                }

                $goodsList = BaseRepository::getToArrayGet($goodsList);
                $favourable_goods_id = BaseRepository::getKeyPluck($goodsList, 'goods_id');
            }
        }

        if ($favourable_goods_id) {
            $sql = [
                'whereIn' => [
                    [
                        'name' => 'goods_id',
                        'value' => $favourable_goods_id
                    ]
                ]
            ];
            $res = BaseRepository::getArraySqlGet($res, $sql);
            $amount = BaseRepository::getArraySum($res, ['goods_price', 'goods_number']);
        } else {
            $amount = 0;
        }

        return $amount;
    }

    /**
     * 检查是否已在购物车
     *
     * @param int $user_id
     * @param array $is_gift_cart
     * @param int $act_id
     * @return mixed
     */
    public function getGiftCart($user_id = 0, $is_gift_cart = [], $act_id = 0)
    {
        $cart = Cart::select('goods_name')
            ->where('user_id', $user_id)
            ->wherein('goods_id', $is_gift_cart)
            ->where('is_gift', $act_id)
            ->where('rec_type', CART_GENERAL_GOODS)
            ->get()
            ->toArray();

        return $cart;
    }

    /**
     * 检查该项是否为基本件 以及是否存在配件
     * 此处配件是指添加商品时附加的并且是设置了优惠价格的配件 此类配件都有parent_idgoods_number为1
     *
     * @param array $where
     * @return  array
     */
    public function getOffersAccessoriesListMobile($where = [])
    {
        $session_id = $this->sessionRepository->realCartMacIp();
        $user_id = isset($uid) && !empty($uid) ? intval($uid) : 0;

        $res = Cart::select('goods_id');

        if (isset($where['rec_id'])) {
            $res = $res->where('rec_id', $where['rec_id']);
        }

        if (isset($where['extension_code'])) {
            if (is_array($where['extension_code'])) {
                $res = $res->where('extension_code', $where['extension_code'][0], $where['extension_code'][1]);
            } else {
                $res = $res->where('extension_code', $where['extension_code']);
            }
        }

        if (!empty($user_id)) {
            $res = $res->where('user_id', $user_id);
        } else {
            $res = $res->where('session_id', $session_id);
        }

        $where = [
            'user_id' => $user_id,
            'session_id' => $session_id
        ];
        $res = $res->whereHasIn('getCartParentGoods', function ($query) use ($where) {
            if (!empty($where['user_id'])) {
                $query->where('user_id', $where['user_id']);
            } else {
                $query->where('session_id', $where['session_id']);
            }
        });

        $res = $res->with(['getCartParentGoods']);

        $res = $res->get();

        $res = $res ? $res->toArray() : [];

        if ($res) {
            foreach ($res as $key => $row) {
                $row = $row['get_cart_parent_goods'] ? array_merge($row, $row['get_cart_parent_goods']) : $row;

                $res[$key] = $row;
            }
        }

        return $res;
    }

    /**
     * 删除购物车商品
     * @param int $user_id
     * @param int $rec_id
     * @return bool
     */
    public function deleteCartGoods($user_id = 0, $rec_id = 0)
    {
        if (empty($rec_id)) {
            return false;
        }

        $row = Cart::where('rec_id', $rec_id);

        if ($user_id > 0) {
            // 登录用户优先使用user_id条件筛选商品
            $row = $row->where('user_id', $user_id);
        } else {
            $session_id = $this->sessionRepository->realCartMacIp();
            $row = $row->where('session_id', $session_id);
        }

        $row = $row->first();

        if ($row) {

            $this->cartCommonService->flowClearCartAlone($user_id);

            //如果是超值礼包
            if ($row->extension_code == 'package_buy') {
                $res = Cart::where('rec_id', $rec_id);
            } // 如果是普通商品，同时删除所有赠品及其配件
            elseif ($row->parent_id == 0 && $row->is_gift == 0) {
                /* 检查购物车中该普通商品的不可单独销售的配件并删除 */
                $goods_id = $row->goods_id;
                $CartRes = Cart::query()->select('rec_id')
                    ->where('parent_id', $goods_id)
                    ->where('extension_code', '<>', 'package_buy')
                    ->where('group_id', $row->group_id)
                    ->whereHasIn('getGoods', function ($query) {
                        $query->where('is_alone_sale', 0);
                    })->whereHasIn('getGroupGoods', function ($query) use ($goods_id) {
                        $query->where('parent_id', $goods_id);
                    });

                $CartRes = $CartRes->pluck('rec_id');
                $_del_str = BaseRepository::getToArray($CartRes);
                $_del_str = BaseRepository::getArrayPush($_del_str, $rec_id);

                $where = [
                    'rec_id' => $_del_str,
                    'parent_id' => $goods_id,
                    'group_id' => $row->group_id
                ];
                $res = Cart::where(function ($query) use ($where) {
                    $query->whereIn('rec_id', $where['rec_id'])
                        ->orWhere(function ($query) use ($where) {
                            $query->where('parent_id', $where['parent_id'])
                                ->where('group_id', $where['group_id']);
                        })->orWhere('is_gift', '<>', 0);
                });

                if (!empty($row->group_id)) {
                    $res = $res->where('group_id', $row->group_id);
                }

            } else {
                $res = Cart::where('rec_id', $rec_id);
            }

            return $res->delete();
        }

        return false;
    }

    /**
     * 选择购物车商品
     *
     * @param int $uid
     * @param array $rec_id
     * @return bool
     */
    public function checked($uid = 0, $rec_id = [])
    {
        $session_id = $this->sessionRepository->realCartMacIp();
        if ($uid > 0) {
            $res = Cart::where('user_id', $uid);
        } else {
            $res = Cart::where('session_id', $session_id);
        }

        $res->update([
            'is_checked' => 0
        ]);

        if (!empty($rec_id) && is_array($rec_id)) {
            $model = Cart::whereIn('rec_id', $rec_id);

            if ($uid > 0) {
                $model = $model->where('user_id', $uid);
            } else {
                $model = $model->where('session_id', $session_id);
            }

            $res = $model->update([
                'is_checked' => 1
            ]);

            if ($res) {

                $carts = $model->select('group_id', 'goods_id')->get();
                if (!empty($carts)) {
                    foreach ($carts as $item) {
                        if ($item->group_id) {

                            /* 更新商品配件 */
                            $groupCart = Cart::where('group_id', $item->group_id)->where('parent_id', $item->goods_id);

                            if ($uid > 0) {
                                $groupCart = $groupCart->where('user_id', $uid);
                            } else {
                                $groupCart = $groupCart->where('session_id', $session_id);
                            }

                            $groupCart->update([
                                'is_checked' => 1
                            ]);
                        }
                    }
                }
            }
        }

        return $res;
    }

    /**
     * 更改购物车数量
     *
     * @param int $num
     * @param array $rec_id
     * @param int $uid
     * @return bool
     */
    public function update($num = 0, $rec_id = [], $uid = 0)
    {
        if ($num > 0) {
            $res = Cart::where('rec_id', $rec_id)
                ->where('user_id', $uid)
                ->update(['goods_number' => $num]);

            return $res;
        }

        return false;
    }

    /**
     * 更新购物车商品配件数量
     * @param int $num
     * @param string $group_id
     * @param int $parent_id
     * @param int $uid
     * @return bool
     */
    public function updateGroupNum($num = 0, $group_id = '', $parent_id = 0, $uid = 0)
    {
        if ($num > 0) {
            $cart = Cart::where('group_id', $group_id)
                ->where('parent_id', $parent_id);

            if (!empty($uid)) {
                $cart = $cart->where('user_id', $uid);
            } else {
                $real_ip = $this->sessionRepository->realCartMacIp();
                $cart = $cart->where('session_id', $real_ip);
            }
            $res = $cart->update(['goods_number' => $num]);

            return $res;
        }

        return false;
    }

    /**
     * 购物车商品
     *
     * @param int $user_id
     * @param int $type
     * @param string $cart_value
     * @param bool $is_checked
     * @return mixed
     * @throws \Exception
     */
    public function getCartGoods($user_id = 0, $type = CART_GENERAL_GOODS, $cart_value = '', $is_checked = true)
    {
        $sess = $this->sessionRepository->realCartMacIp();

        $time = TimeRepository::getGmTime();

        $user_id = isset($user_id) && !empty($user_id) ? intval($user_id) : 0;

        $res = Cart::where('rec_type', $type)
            ->where('stages_qishu', '-1')
            ->where('store_id', 0);

        if ($user_id) {
            $res = $res->where('user_id', $user_id);
            Cart::where('session_id', $sess)->update(['user_id' => $user_id, 'session_id' => 0]);
        } else {
            $res = $res->where('session_id', $sess);
        }

        if ($is_checked) {
            $res = $res->where('is_checked', 1);
        }

        $cart_value = BaseRepository::getExplode($cart_value);

        if (isset($cart_value) && $cart_value) {
            $res = $res->whereIn('rec_id', $cart_value);
        }

        $res = $res->orderBy('parent_id', 'ASC')
            ->orderBy('rec_id', 'DESC');

        $res = BaseRepository::getToArrayGet($res);

        if (!$is_checked) {
            $res = BaseRepository::getArrayUnique($res, 'act_id');
        }

        if (empty($res)) {
            return [];
        }

        $groupList = BaseRepository::getKeyPluck($res, 'group_id');
        $goodsList = BaseRepository::getKeyPluck($res, 'goods_id');

        $goodsOther = [
            'goods_id',
            'goods_thumb',
            'model_price',
            'integral',
            'is_on_sale',
            'cat_id',
            'brand_id',
            'goods_number as product_number',
            'shop_price as rec_shop_price',
            'promote_price as rec_promote_price',
            'promote_start_date',
            'promote_end_date',
            'is_promote',
            'model_attr',
            'is_delete',
            'is_minimum',
            'minimum_start_date',
            'minimum_end_date',
            'minimum',
            'free_rate',
            'is_discount',
            'user_id',
            'xiangou_start_date',
            'xiangou_end_date',
            'is_xiangou',
            'xiangou_num'
        ];

        if ($cart_value) {

            if ($groupList) {
                if ($user_id > 0) {
                    $cartGroup = Cart::where('user_id', $user_id)->whereIn('group_id', $groupList)->where('parent_id', $goodsList);
                } else {
                    $cartGroup = Cart::where('session_id', $sess)->whereIn('group_id', $groupList)->where('parent_id', $goodsList);
                }
                $cartGroup = BaseRepository::getToArrayGet($cartGroup);
                $res = BaseRepository::getArrayMerge($res, $cartGroup);
            }
        }

        $cartIdList = FlowRepository::cartGoodsAndPackage($res);
        $goods_id = $cartIdList['goods_id']; //普通商品ID

        $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, $goodsOther);

        $consumptionList = GoodsDataHandleService::GoodsConsumptionDataList($goods_id);

        $recGoodsModelList = BaseRepository::getColumn($res, 'model_attr', 'rec_id');
        $recGoodsModelList = $recGoodsModelList ? array_unique($recGoodsModelList) : [];

        $isModel = 0;
        if (in_array(1, $recGoodsModelList) || in_array(2, $recGoodsModelList)) {
            $isModel = 1;
        }

        if ($isModel == 1) {
            $warehouseGoodsList = GoodsDataHandleService::getWarehouseGoodsDataList($goods_id);
            $warehouseAreaGoodsList = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods_id);
        } else {
            $warehouseGoodsList = [];
            $warehouseAreaGoodsList = [];
        }

        $product_id = BaseRepository::getKeyPluck($res, 'product_id');
        $productsList = GoodsDataHandleService::getProductsDataList($product_id, '*', 'product_id');

        if ($isModel == 1) {
            $productsWarehouseList = GoodsDataHandleService::getProductsWarehouseDataList($product_id, 0, '*', 'product_id');
            $productsAreaList = GoodsDataHandleService::getProductsAreaDataList($product_id, 0, '*', 'product_id');
        } else {
            $productsWarehouseList = [];
            $productsAreaList = [];
        }

        /* 处理更新价格 */
        $payPriceList = $this->cartCommonService->cartFinalPrice($user_id, $res, $goodsList, $warehouseGoodsList, $warehouseAreaGoodsList, $productsList, $productsWarehouseList, $productsAreaList);

        foreach ($res as $k => $v) {

            $goods = $goodsList[$v['goods_id']] ?? [];
            $v['get_goods'] = $goods;

            unset($goods['user_id']);

            $v = $goods ? array_merge($v, $goods) : $v;

            //判断商品类型，如果是超值礼包则修改链接和缩略图
            if ($v['extension_code'] == 'package_buy') {
                /* 取得礼包信息 */
                $package = $this->packageService->getPackageInfo($v['goods_id']);
                $v['goods_thumb'] = $package['activity_thumb'];
                $res[$k]['package_goods_list'] = $package['goods_list'];
            }
            $res[$k]['goods_thumb'] = $this->dscRepository->getImagePath($v['goods_thumb']);
            $res[$k]['cat_id'] = $v['cat_id'] ?? 0;
            $res[$k]['brand_id'] = $v['brand_id'] ?? 0;
            $res[$k]['short_name'] = config('shop.goods_name_length') > 0 ? $this->dscRepository->subStr($v['goods_name'], config('shop.goods_name_length')) : $v['goods_name'];

            // 当前商品正在最小起订量
            if ($v['extension_code'] != 'package_buy') {
                if ($time >= $v['minimum_start_date'] && $time <= $v['minimum_end_date'] && $v['is_minimum']) {
                    $v['is_minimum'] = 1;
                } else {
                    $v['is_minimum'] = 0;
                }
            }

            if (!empty($v['is_minimum']) && $v['is_minimum'] == 1 && $v['goods_number'] < $v['minimum']) {
                $v['goods_number'] = $v['minimum'];
            }

            $res[$k]['goods_number'] = $v['goods_number'];
            $res[$k]['goods_name'] = $v['goods_name'];
            $res[$k]['market_price_format'] = $this->dscRepository->getPriceFormat($v['market_price']);
            $res[$k]['warehouse_id'] = $v['warehouse_id'];
            $res[$k]['area_id'] = $v['area_id'];
            $res[$k]['rec_id'] = $v['rec_id'];
            $res[$k]['is_checked'] = ($v['is_checked'] == 1) ? true : false;
            $res[$k]['extension_code'] = $v['extension_code'];
            $res[$k]['is_gift'] = $v['is_gift'];
            $res[$k]['is_on_sale'] = isset($v['is_on_sale']) ? $v['is_on_sale'] : 1;

            // 更新购物车商品价格 - 普通商品
            if (in_array($type, [CART_GENERAL_GOODS, CART_ONESTEP_GOODS]) && $v['extension_code'] != 'package_buy' && $v['is_gift'] == 0 && $v['parent_id'] == 0) {
                $goods_price = $payPriceList[$v['rec_id']]['pay_price'] ?? 0;
                if ($v['goods_price'] != $goods_price) {
                    CartCommonService::getUpdateCartPrice($goods_price, $v['rec_id']);
                    $v['goods_price'] = $goods_price;
                }
            }

            $res[$k]['goods_price'] = $v['goods_price'];

            $res[$k]['goods_price_format'] = $this->dscRepository->getPriceFormat($res[$k]['goods_price']);

            if (CROSS_BORDER === true) { // 跨境多商户
                $res[$k]['free_rate'] = $v['free_rate'] ?? 0;
            }

            $row['goods_amount'] = $res[$k]['goods_price'] * $v['goods_number'];

            $goods_consumption = $consumptionList[$v['goods_id']] ?? [];
            if (!empty($goods_consumption)) {
                $res[$k]['amount'] = $this->dscRepository->getGoodsConsumptionPrice($goods_consumption, $row['goods_amount']);
            } else {
                $res[$k]['amount'] = $row['goods_amount'];
            }

            $res[$k]['subtotal'] = $row['goods_amount'];
            $res[$k]['formated_subtotal'] = $this->dscRepository->getPriceFormat($row['goods_amount'], false);
            $res[$k]['dis_amount'] = $row['goods_amount'] - $res[$k]['amount'];
            $res[$k]['dis_amount'] = number_format($res[$k]['dis_amount'], 2, '.', '');
            $res[$k]['discount_amount'] = $this->dscRepository->getPriceFormat($res[$k]['dis_amount'], false);
        }

        return $res;
    }

    /**
     * 获取活动购物车ID
     *
     * @param int $user_id
     * @param int $act_id
     * @param int $ru_id
     * @return array|string
     */
    public function getCartRecId($user_id = 0, $act_id = 0, $ru_id = 0)
    {
        $sess = $this->sessionRepository->realCartMacIp();

        $user_id = isset($user_id) && !empty($user_id) ? intval($user_id) : 0;

        $res = Cart::select('rec_id')->where('parent_id', 0)
            ->where('act_id', $act_id)
            ->where('ru_id', $ru_id)
            ->where('rec_type', CART_GENERAL_GOODS);

        if ($user_id) {
            $res = $res->where('user_id', $user_id);
            Cart::where('session_id', $sess)->update(['user_id' => $user_id, 'session_id' => 0]);
        } else {
            $res = $res->where('session_id', $sess);
        }

        $res = $res->orderBy('parent_id', 'ASC')->orderBy('rec_id', 'DESC')
            ->get();
        $res = $res ? $res->toArray() : [];
        $rec_id = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $rec_id[] = $val['rec_id'];
            }
            $rec_id = implode(',', $rec_id);
        }
        return $rec_id;
    }


    /**
     * 检查是否已在购物车
     * @return mixed
     */
    public function getCartInfo($user_id = 0, $rec_id = 0)
    {
        $time = TimeRepository::getGmTime();
        $sess = $this->sessionRepository->realCartMacIp();
        $cart = Cart::where('user_id', $user_id)
            ->where('rec_id', $rec_id)
            ->where('rec_type', CART_GENERAL_GOODS);

        if ($user_id) {
            $cart = $cart->where('user_id', $user_id);
            Cart::where('session_id', $sess)->update(['user_id' => $user_id, 'session_id' => 0]);
        } else {
            $cart = $cart->where('session_id', $sess);
        }

        $cart = $cart->with([
            'getGoods' => function ($query) {
                $query->select(
                    'goods_id',
                    'goods_number',
                    'goods_thumb',
                    'model_price',
                    'integral',
                    'user_id',
                    'is_on_sale',
                    'cat_id',
                    'brand_id',
                    'group_number',
                    'model_attr',
                    'xiangou_num',
                    'is_minimum',
                    'minimum_start_date',
                    'minimum_end_date',
                    'minimum'
                );
            }
        ]);

        $cart = $cart->first();
        if ($cart === null) {
            return [];
        }
        $cart = $cart->toArray();
        $cart['buy_number'] = $cart['goods_number'] ?? 0;
        $cart = $cart['get_goods'] ? array_merge($cart, $cart['get_goods']) : $cart;
        // 当前商品正在最小起订量
        if ($cart['extension_code'] != 'package_buy') {
            if ($time >= $cart['minimum_start_date'] && $time <= $cart['minimum_end_date'] && $cart['is_minimum']) {
                $cart['is_minimum'] = 1;
            } else {
                $cart['is_minimum'] = 0;
            }
        }

        return $cart;
    }


    /**
     * 购物车选择促销活动
     *
     * @param int $user_id
     * @param int $goods_id
     * @param int $ru_id
     * @param int $act_id
     * @param bool $type
     * @return array
     * @throws \Exception
     */
    public function getFavourable($user_id = 0, $goods_id = 0, $ru_id = 0, $act_id = 0, $type = false)
    {
        $res = app(DiscountService::class)->activityListAll($user_id, $ru_id);

        $favourable = [];
        $fav_actid = [];
        if (empty($goods_id)) {
            foreach ($res as $rows) {
                if ($rows['userFav_type'] == 1) {
                    $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
                } else {
                    $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                }
                $favourable[$rows['act_id']]['url'] = $favourable[$rows['act_id']]['url'] = route('api.activity.show', ['act_id' => $rows['act_id']]);
                $favourable[$rows['act_id']]['time'] = sprintf(L('promotion_time'), TimeRepository::getLocalDate('Y-m-d', $rows['start_time']), TimeRepository::getLocalDate('Y-m-d', $rows['end_time']));
                $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                $favourable[$rows['act_id']]['type'] = 'favourable';
                $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
            }
        } else {
            // 商品信息
            $goods = Goods::select('cat_id', 'brand_id')->where('goods_id', $goods_id)->first();
            $goods = $goods ? $goods->toArray() : [];

            $category_id = $goods['cat_id'] ?? 0;
            $brand_id = $goods['brand_id'] ?? 0;

            foreach ($res as $rows) {
                if ($rows['act_range'] == FAR_ALL) {
                    $favourable[$rows['act_id']]['act_id'] = $rows['act_id'];
                    if ($rows['userFav_type'] == 1) {
                        $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
                    } else {
                        $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                    }
                    $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                    $favourable[$rows['act_id']]['type'] = 'favourable';
                    $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
                    if ($type) {
                        $fav_actid['act_id'] = $rows['act_id'];
                        break;
                    }
                } elseif ($rows['act_range'] == FAR_CATEGORY) {
                    /* 找出分类id的子分类id */
                    $id_list = [];
                    $raw_id_list = explode(',', $rows['act_range_ext']);

                    foreach ($raw_id_list as $id) {
                        /**
                         * 当前分类下的所有子分类
                         * 返回一维数组
                         */
                        $cat_list = $this->categoryService->getCatListChildren($id);
                        $id_list = array_merge($id_list, $cat_list);
                        array_unshift($id_list, $id);
                    }
                    $ids = join(',', array_unique($id_list));
                    if (strpos(',' . $ids . ',', ',' . $category_id . ',') !== false) {
                        $favourable[$rows['act_id']]['act_id'] = $rows['act_id'];
                        if ($rows['userFav_type'] == 1) {
                            $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
                        } else {
                            $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                        }
                        $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                        $favourable[$rows['act_id']]['type'] = 'favourable';
                        $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
                        if ($type) {
                            $fav_actid['act_id'] = $rows['act_id'];
                            break;
                        }
                    }
                } elseif ($rows['act_range'] == FAR_BRAND) {
                    if (strpos(',' . $rows['act_range_ext'] . ',', ',' . $brand_id . ',') !== false) {
                        $favourable[$rows['act_id']]['act_id'] = $rows['act_id'];
                        if ($rows['userFav_type'] == 1) {
                            $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
                        } else {
                            $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                        }
                        $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                        $favourable[$rows['act_id']]['type'] = 'favourable';
                        $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
                        if ($type) {
                            $fav_actid['act_id'] = $rows['act_id'];
                            break;
                        }
                    }
                } elseif ($rows['act_range'] == FAR_GOODS) {
                    if (strpos(',' . $rows['act_range_ext'] . ',', ',' . $goods_id . ',') !== false) {
                        $favourable[$rows['act_id']]['act_id'] = $rows['act_id'];
                        if ($rows['userFav_type'] == 1) {
                            $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
                        } else {
                            $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                        }
                        $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                        $favourable[$rows['act_id']]['type'] = 'favourable';
                        $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
                        if ($type) {
                            $fav_actid['act_id'] = $rows['act_id'];
                            break;
                        }
                    }
                }
            }
        }

        if ($type) {
            return $fav_actid;
        } else {
            if ($favourable) {
                foreach ($favourable as $key => $val) {
                    if ($key == $act_id) {
                        $favourable[$key]['is_checked'] = true;
                    } else {
                        $favourable[$key]['is_checked'] = false;
                    }
                }
                $favourable = collect($favourable)->values()->all();
            }

            return $favourable;
        }
    }

    /**
     * 添加套餐组合商品（配件）到购物车（临时）
     * $goods_id 当前商品id
     * $number
     * $spec =  当前商品属性
     * $parent_attr  主件商品属性
     * $warehouse_id
     * $area_id
     * $area_city
     * $parent 主件商品id
     * $group_id
     * $add_group
     */

    /**
     * 添加套餐组合商品（配件）到购物车（临时）
     *
     * @param int $uid 会员ID
     * @param array $args
     * @return mixed
     * @throws \Exception
     */
    public function addToCartCombo($uid = 0, $args = [])
    {
        // 首次添加配件时，查看主件是否存在，否则添加主件
        $ok_arr = $this->getInsertGroupMain($args['parent'], $args['number'], $args['parent_attr'], 0, $args['group_id'], $args['warehouse_id'], $args['area_id'], $args['area_city'], $uid);

        if ($ok_arr) {
            if ($ok_arr['is_ok'] == 1) {
                $msg['error'] = 1;
                $msg['msg'] = lang('flow.goods_not_exists');
                return $msg;
            }
            if ($ok_arr['is_ok'] == 2) { // 商品已下架
                $msg['error'] = 1;
                $msg['msg'] = lang('flow.shelves_goods');
                return $msg;
            }
            if ($ok_arr['is_ok'] == 3 || $ok_arr['is_ok'] == 4) { //
                $msg['error'] = 1;
                $msg['msg'] = lang('flow.goods_null_number');
                return $msg;
            }
        }

        $_parent_id = $args['parent'];

        /* 取得配件商品信息 */
        $where = [
            'goods_id' => $args['goods_id'],
            'warehouse_id' => $args['warehouse_id'],
            'area_id' => $args['area_id'],
            'area_city' => $args['area_city'],
            'uid' => $uid
        ];
        $goods = $this->goodsMobileService->getGoodsInfo($where);

        if (empty($goods)) {   // 商品不存在
            $msg['error'] = 1;
            $msg['msg'] = lang('flow.fittings_goods_null');
            return $msg;
        }

        /* 是否正在销售 */
        if ($goods['is_on_sale'] == 0) {// 是否正在销售
            $msg['error'] = 1;
            $msg['msg'] = lang('flow.fittings_goods_null_sold');
            return $msg;
        }

        /* 不是配件时检查是否允许单独销售 */
        if (empty($args['parent']) && $goods['is_alone_sale'] == 0) {
            $msg['error'] = 1;
            $msg['msg'] = lang('flow.goods_oneself_sold');
            return $msg;
        }

        /* 商品仓库货品 */
        $product_info = [];
        $is_spec = $this->goodsAttrService->is_spec($args['spec']);
        if ($is_spec == true) {
            $product_info = $this->goodsAttrService->getProductsInfo($args['goods_id'], $args['spec'], $args['warehouse_id'], $args['area_id'], $args['area_city']);
        }

        /* 检查：库存 */
        if (config('shop.use_storage') == 1) {
            $is_product = 0;
            //商品存在规格 是货品
            if ($is_spec == true && !empty($product_info)) {
                /* 取规格的货品库存 */
                $product_info['product_number'] = $product_info['product_number'] ?? 0;
                if ($args['number'] > $product_info['product_number']) {
                    $msg['error'] = 1;
                    $msg['msg'] = lang('cart.stock_goods_null');
                    return $msg;
                }
            } else {
                $is_product = 1;
            }

            if ($is_product == 1) {
                //检查：商品购买数量是否大于总库存
                if ($args['number'] > $goods['goods_number']) {
                    $msg['error'] = 1;
                    $msg['msg'] = lang('cart.stock_goods_null');
                    return $msg;
                }
            }
        }

        /* 计算商品的促销价格 */
        $warehouse_area['warehouse_id'] = $args['warehouse_id'];
        $warehouse_area['area_id'] = $args['area_id'];
        $warehouse_area['area_city'] = $args['area_city'];

        // 属性价格
        $spec_price = $this->goodsAttrService->specPrice($args['spec'], $args['goods_id'], $warehouse_area);

        $goods['marketPrice'] += $spec_price;

        $goods_attr = $this->goodsAttrService->getGoodsAttrInfo($args['spec'], 'pice', $args['warehouse_id'], $args['area_id'], $args['area_city']);

        $goods_attr_id = isset($spec) ? join(',', $spec) : '';

        // 属性成本价、属性货号
        if ($is_spec == true && !empty($product_info)) {
            $cost_price = $product_info['product_cost_price'] ?? 0;
            $goods_sn = $product_info['product_sn'] ?? '';
        } else {
            $cost_price = $goods['cost_price'] ?? 0;
            $goods_sn = $goods['goods_sn'] ?? '';
        }

        $session_id = 0;
        if (empty($uid)) {
            $session_id = $this->sessionRepository->realCartMacIp();
        }

        /* 初始化要插入购物车的基本件数据 */
        $parent = [
            'user_id' => $uid,
            'session_id' => $session_id,
            'goods_id' => $args['goods_id'],
            'goods_sn' => addslashes($goods_sn),
            'product_id' => $product_info['product_id'] ?? 0,
            'goods_name' => addslashes($goods['goods_name']),
            'market_price' => $goods['marketPrice'],
            'goods_attr' => addslashes($goods_attr),
            'goods_attr_id' => $goods_attr_id,
            'is_real' => $goods['is_real'],
            'model_attr' => $goods['model_attr'], //ecmoban模板堂 --zhuo 属性方式
            'warehouse_id' => $args['warehouse_id'], //ecmoban模板堂 --zhuo 仓库
            'area_id' => $args['area_id'], //ecmoban模板堂 --zhuo 仓库地区
            'area_city' => $args['area_city'],
            'ru_id' => $goods['user_id'], //ecmoban模板堂 --zhuo 商家ID
            'extension_code' => $goods['extension_code'],
            'is_gift' => 0,
            'commission_rate' => $goods['commission_rate'] ?? 0,
            'is_shipping' => $goods['is_shipping'],
            'rec_type' => CART_GENERAL_GOODS,
            'add_time' => TimeRepository::getGmTime(),
            'group_id' => $args['group_id'] ?? 0
        ];

        /* 如果该配件在添加为基本件的配件时，所设置的“配件价格”比原价低，即此配件在价格上提供了优惠， */
        /* 则按照该配件的优惠价格卖，但是每一个基本件只能购买一个优惠价格的“该配件”，多买的“该配件”不享 */
        /* 受此优惠 */
        $basic_list = GroupGoods::select('parent_id', 'goods_price')
            ->where('goods_id', $args['goods_id'])
            ->where('parent_id', $_parent_id)
            ->orderBy('goods_price');
        $basic_list = BaseRepository::getToArrayGet($basic_list);

        if ($basic_list) {
            /* 循环插入配件 如果是配件则用其添加数量依次为购物车中所有属于其的基本件添加足够数量的该配件 */
            foreach ($basic_list as $key => $value) {

                if (config('shop.add_shop_price') == 1) {
                    $value['goods_price'] = $value['goods_price'] + $spec_price;
                }

                $attr_info = $this->goodsAttrService->getGoodsAttrInfo($args['spec'], 'pice', $args['warehouse_id'], $args['area_id'], $args['area_city']);

                /* 检查该商品是否已经存在在购物车中 */
                $row = CartCombo::where('goods_id', $args['goods_id'])
                    ->where('parent_id', $value['parent_id'])
                    ->where('extension_code', '<>', 'package_buy')
                    ->where('rec_type', CART_GENERAL_GOODS)
                    ->where('group_id', $args['group_id']);

                if (!empty($uid)) {
                    $row = $row->where('user_id', $uid);
                } else {
                    $row = $row->where('session_id', $session_id);
                }

                $row = $row->count();

                if ($row) {
                    //如果购物车已经有此物品，则更新
                    $num = 1; //临时保存到数据库，无数量限制
                    if ($is_spec == true && !empty($product_info)) {
                        $goods_storage = $product_info['product_number'] ?? 0;
                    } else {
                        $goods_storage = $goods['goods_number'];
                    }

                    if (config('shop.use_storage') == 0 || $num <= $goods_storage) {
                        $CartComboOther = [
                            'goods_number' => $num,
                            'commission_rate' => $goods['commission_rate'],
                            'goods_price' => $value['goods_price'],
                            'product_id' => $product_info['product_id'] ?? 0,
                            'goods_attr' => $attr_info,
                            'goods_attr_id' => $goods_attr_id,
                            'market_price' => $goods['marketPrice'],
                            'warehouse_id' => $args['warehouse_id'],
                            'area_id' => $args['area_id'],
                            'area_city' => $args['area_city']
                        ];
                        $res = CartCombo::where('goods_id', $args['goods_id'])
                            ->where('parent_id', $value['parent_id'])
                            ->where('extension_code', '<>', 'package_buy')
                            ->where('rec_type', CART_GENERAL_GOODS)
                            ->where('group_id', $args['group_id']);

                        if (!empty($uid)) {
                            $res = $res->where('user_id', $uid);
                        } else {
                            $res = $res->where('session_id', $session_id);
                        }

                        $res->update($CartComboOther);
                    } else {
                        $msg['error'] = 1;
                        $msg['msg'] = lang('cart.stock_goods_null');
                        return $msg;
                    }
                } //购物车没有此物品，则插入
                else {
                    /* 作为该基本件的配件插入 */
                    $parent['goods_price'] = $value['goods_price'];
                    $parent['goods_number'] = 1; //临时保存到数据库，无数量限制
                    $parent['parent_id'] = $value['parent_id'];

                    /* 添加 */
                    CartCombo::insert($parent);
                }
            }
        }

        $msg['error'] = 0;
        $msg['msg'] = lang('flow.add_package_cart_success');
        return $msg;
    }


    /**
     * 首次添加配件时，查看主件是否存在，否则添加主件
     *
     * @param $goods_id
     * @param int $num
     * @param array $spec
     * @param int $parent
     * @param string $group
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param int $user_id
     * @return mixed
     * @throws \Exception
     */
    public function getInsertGroupMain($goods_id, $num = 1, $spec = [], $parent = 0, $group = '', $warehouse_id = 0, $area_id = 0, $area_city = 0, $user_id = 0)
    {
        $ok_arr['is_ok'] = 0;

        $spec = BaseRepository::getExplode($spec);

        /* 取得商品信息 */
        $where = [
            'goods_id' => $goods_id,
            'warehouse_id' => $warehouse_id,
            'area_id' => $area_id,
            'area_city' => $area_city,
            'uid' => $user_id
        ];
        $goods = $this->goodsMobileService->getGoodsInfo($where);

        if (empty($goods)) {
            $ok_arr['is_ok'] = 1;
            return $ok_arr;
        }

        /* 是否正在销售 */
        if ($goods['is_on_sale'] == 0) {
            $ok_arr['is_ok'] = 2;
            return $ok_arr;
        }

        /* 商品仓库货品 */
        $is_spec = $this->goodsAttrService->is_spec($spec);
        $product_info = [];
        if ($is_spec == true) {
            $product_info = $this->goodsAttrService->getProductsInfo($goods_id, $spec, $warehouse_id, $area_id, $area_city);
        }

        /* 检查：库存 */
        if (config('shop.use_storage') == 1) {
            //商品存在规格 是货品
            if ($is_spec == true && !empty($product_info)) {
                /* 取规格的货品库存 */
                $product_info['product_number'] = $product_info['product_number'] ?? 0;
                if ($num > $product_info['product_number']) {
                    $ok_arr['is_ok'] = 3;
                    return $ok_arr;
                }
            } else {
                //检查：商品购买数量是否大于总库存
                if ($num > $goods['goods_number']) {
                    $ok_arr['is_ok'] = 4;
                    return $ok_arr;
                }
            }
        }

        /* 计算商品的促销价格 */
        $warehouse_area['warehouse_id'] = $warehouse_id;
        $warehouse_area['area_id'] = $area_id;
        $warehouse_area['area_city'] = $area_city;

        // 属性价格
        $spec_price = $this->goodsAttrService->specPrice($spec, $goods_id, $warehouse_area);

        // 最终价格
        $goods_price = $this->goodsMobileService->getFinalPrice($user_id, $goods_id, $num, true, $spec, $warehouse_id, $area_id, $area_city);

        $goods['marketPrice'] += $spec_price;

        $goods_attr = $this->goodsAttrService->getGoodsAttrInfo($spec, 'pice', $warehouse_id, $area_id, $area_city);
        $goods_attr_id = isset($spec) ? join(',', $spec) : '';

        // 属性成本价、属性货号
        if ($is_spec == true && !empty($product_info)) {
            $cost_price = $product_info['product_cost_price'] ?? 0;
            $goods_sn = $product_info['product_sn'] ?? '';
        } else {
            $cost_price = $goods['cost_price'] ?? 0;
            $goods_sn = $goods['goods_sn'] ?? '';
        }

        $session_id = 0;
        if (empty($user_id)) {
            $session_id = $this->sessionRepository->realCartMacIp();
        }

        /* 初始化要插入购物车的基本件数据 */
        $parent = [
            'user_id' => $user_id,
            'session_id' => $session_id,
            'goods_id' => $goods_id,
            'goods_sn' => addslashes($goods_sn),
            'product_id' => $product_info['product_id'] ?? 0,
            'goods_name' => addslashes($goods['goods_name']),
            'market_price' => $goods['marketPrice'],
            'goods_attr' => addslashes($goods_attr),
            'goods_attr_id' => $goods_attr_id,
            'is_real' => $goods['is_real'],
            'model_attr' => $goods['model_attr'], //ecmoban模板堂 --zhuo 属性方式
            'warehouse_id' => $warehouse_id, //ecmoban模板堂 --zhuo 仓库
            'area_id' => $area_id, //ecmoban模板堂 --zhuo 仓库地区
            'area_city' => $area_city,
            'ru_id' => $goods['user_id'], //ecmoban模板堂 --zhuo 商家ID
            'extension_code' => $goods['extension_code'],
            'is_gift' => 0,
            'is_shipping' => $goods['is_shipping'],
            'rec_type' => CART_GENERAL_GOODS,
            'add_time' => TimeRepository::getGmTime(),
            'group_id' => $group
        ];

        /* 检查该套餐主件商品是否已经存在在购物车中 */
        $row = CartCombo::where('goods_id', $goods_id)
            ->where('parent_id', 0)
            ->where('extension_code', '<>', 'package_buy')
            ->where('rec_type', CART_GENERAL_GOODS)
            ->where('group_id', $group);

        if (!empty($user_id)) {
            $row = $row->where('user_id', $user_id);
        } else {
            $row = $row->where('session_id', $session_id);
        }

        $row = $row->where('warehouse_id', $warehouse_id);

        $row = $row->count();

        if ($row) {
            $CartComboOther = [
                'goods_number' => $num,
                'goods_price' => $goods_price,
                'product_id' => $product_info['product_id'] ?? 0,
                'goods_attr' => addslashes($goods_attr),
                'goods_attr_id' => $goods_attr_id,
                'market_price' => $goods['marketPrice'],
                'warehouse_id' => $warehouse_id,
                'area_id' => $area_id
            ];
            $res = CartCombo::where('goods_id', $goods_id)
                ->where('parent_id', 0)
                ->where('extension_code', '<>', 'package_buy')
                ->where('rec_type', CART_GENERAL_GOODS)
                ->where('group_id', $group);

            if (!empty($user_id)) {
                $res = $res->where('user_id', $user_id);
            } else {
                $res = $res->where('session_id', $session_id);
            }

            $res->update($CartComboOther);
        } else {
            $parent['goods_price'] = max($goods_price, 0);
            $parent['goods_number'] = $num;
            $parent['parent_id'] = 0;

            CartCombo::insert($parent);
        }
    }

    /**
     * 更新临时购物车（删除配件）
     * @param int $goods_id
     * @return string
     */
    public function deleteGroupGoods($user_id = 0, $goods_id = 0, $group_id = 0)
    {
        $user_id = isset($user_id) && !empty($user_id) ? intval($user_id) : 0;
        $cart = CartCombo::where('goods_id', $goods_id)
            ->where('group_id', $group_id);

        if (!empty($user_id)) {
            $cart = $cart->where('user_id', $user_id);
        } else {
            $real_ip = $this->sessionRepository->realCartMacIp();
            $cart = $cart->where('session_id', $real_ip);
        }

        return $cart->delete();
    }

    /**
     * 统计购物车配件数量
     * @param int $goods_id
     * @return string
     */
    public function countGroupGoods($user_id = 0, $parent_id = 0, $group_id = 0)
    {
        $user_id = isset($user_id) && !empty($user_id) ? intval($user_id) : 0;
        $cart = CartCombo::where('group_id', $group_id);

        if ($parent_id) {
            $cart = $cart->where('parent_id', $parent_id);
        }

        if (!empty($user_id)) {
            $cart = $cart->where('user_id', $user_id);
        } else {
            $real_ip = $this->sessionRepository->realCartMacIp();
            $cart = $cart->where('session_id', $real_ip);
        }

        return $cart->count();
    }


    /**
     * 删除临时购物车主件配件购物车
     * @param int $goods_id
     * @return string
     */
    public function deleteParentGoods($user_id = 0, $parent_id = 0, $group_id = 0)
    {
        $user_id = isset($user_id) && !empty($user_id) ? intval($user_id) : 0;
        $cart = CartCombo::where('group_id', $group_id);

        if ($parent_id) {
            $cart = $cart->where('goods_id', $parent_id)
                ->where('parent_id', 0);
        }

        if (!empty($user_id)) {
            $cart = $cart->where('user_id', $user_id);
        } else {
            $real_ip = $this->sessionRepository->realCartMacIp();
            $cart = $cart->where('session_id', $real_ip);
        }

        return $cart->delete();
    }


    /**
     * 清空配件购物车配件
     * @param int $goods_id
     * @return string
     */
    public function deleteCartGroupGoods($user_id = 0, $group_id = 0)
    {
        $user_id = isset($user_id) && !empty($user_id) ? intval($user_id) : 0;
        $cart = Cart::where('group_id', $group_id);

        if (!empty($user_id)) {
            $cart = $cart->where('user_id', $user_id);
        } else {
            $real_ip = $this->sessionRepository->realCartMacIp();
            $cart = $cart->where('session_id', $real_ip);
        }

        return $cart->delete();
    }


    /**
     * 查询临时购物车中的组合套餐
     * @param int $user_id
     * @param int $group_id
     * @return array
     */
    public function selectGroupGoods($user_id = 0, $group_id = 0)
    {
        $user_id = isset($user_id) && !empty($user_id) ? intval($user_id) : 0;
        $cart = CartCombo::where('group_id', $group_id);

        if (!empty($user_id)) {
            $cart = $cart->where('user_id', $user_id);
        } else {
            $real_ip = $this->sessionRepository->realCartMacIp();
            $cart = $cart->where('session_id', $real_ip);
        }

        $cart = $cart->orderby('parent_id', 'asc')
            ->get();

        return $cart ? $cart->toArray() : [];
    }


    /**
     * 查询购物车中的组合套餐配件
     *
     * @param int $uid
     * @param int $rec_id
     * @return array
     */
    public function getCartGroupList($uid = 0, $rec_id = 0)
    {
        $cart = Cart::from('cart as a')
            ->select('b.goods_number', 'b.rec_id')
            ->leftjoin('cart as b', 'b.parent_id', '=', 'a.goods_id')
            ->where('a.rec_id', $rec_id)
            ->where('a.user_id', $uid)
            ->where('a.extension_code', '!=', 'package_buy')
            ->where('b.user_id', $uid)
            ->get();

        return $cart ? $cart->toArray() : [];
    }


    /**
     * 获取属性库存
     * @param int $goods_id
     * @return string
     */
    public function getProductNumber($goods_id = 0, $product_id = 0, $model_attr = 0)
    {
        /* 如果商品有规格则取规格商品信息 */
        if ($model_attr == 1) {
            $prod = ProductsWarehouse::where('goods_id', $goods_id)
                ->where('product_id', $product_id);
        } elseif ($model_attr == 2) {
            $prod = ProductsArea::where('goods_id', $goods_id)
                ->where('product_id', $product_id);
        } else {
            $prod = Products::where('goods_id', $goods_id)
                ->where('product_id', $product_id);
        }

        $prod = $prod->first();

        return $prod ? $prod->toArray() : [];
    }

    /*
    * 检查商品单品限购
    * @param int is_cart 0 列表，1 更新
    */
    public function xiangou_checked($goods_id = 0, $num = 0, $user_id = 0, $is_cart = 0, $rec_type = CART_GENERAL_GOODS, $rec_id = 0)
    {
        $nowTime = TimeRepository::getGmTime();

        $xiangouInfo = $this->goodsCommonService->getPurchasingGoodsInfo($goods_id);
        $start_date = $xiangouInfo['xiangou_start_date'];
        $end_date = $xiangouInfo['xiangou_end_date'];

        $result = ['error' => 0];
        if ($xiangouInfo['is_xiangou'] == 1 && $nowTime >= $start_date && $nowTime < $end_date) {
            $cart_number = Cart::where('goods_id', $goods_id)->where('rec_type', $rec_type);

            if (!empty($user_id)) {
                $cart_number = $cart_number->where('user_id', $user_id);
            } else {
                $session_id = $this->sessionRepository->realCartMacIp();
                $cart_number = $cart_number->where('session_id', $session_id);
            }

            $cart_number = $cart_number->value('goods_number');
            $cart_number = $cart_number ?? 0;

            $extension_code = $xiangouInfo['is_real'] == 0 ? 'virtual_card' : '';
            $orderGoods = $this->orderGoodsService->getForPurchasingGoods($start_date, $end_date, $goods_id, $user_id, $extension_code);

            if ($orderGoods['goods_number'] >= $xiangouInfo['xiangou_num']) {
                $result['error'] = 2;
                $result['cart_number'] = $cart_number;
                return $result;//该商品购买已达到限购条件,无法再购买
            } else {
                if ($xiangouInfo['xiangou_num'] > 0) {
                    $otherGoods = Cart::where('goods_id', $goods_id);
                    if ($user_id) {
                        $otherGoods = $otherGoods->where('user_id', $user_id);
                    } else {
                        $session_id = $this->sessionRepository->realCartMacIp();
                        $otherGoods = $otherGoods->where('session_id', $session_id);
                    }

                    if ($is_cart == 0) {
                        $otherGoods = $otherGoods->sum('goods_number');

                        if ($otherGoods + $orderGoods['goods_number'] + $num > $xiangouInfo['xiangou_num']) {
                            $result['error'] = 1;
                            $result['cart_number'] = $xiangouInfo['xiangou_num'] - $otherGoods - $orderGoods['goods_number'];
                            $result['can_buy_num'] = $xiangouInfo['xiangou_num'] - $orderGoods['goods_number'];
                            return $result;//对不起，该商品已经累计超过限购数量
                        }
                    } else {
                        $otherGoods = $otherGoods->where('rec_id', '<>', $rec_id);
                        $otherGoods = $otherGoods->sum('goods_number');

                        //如果$rec_id 为0 怎都是其他商品或属性数量
                        if ($rec_id == 0) {
                            $num = 0;
                        }

                        if ($orderGoods['goods_number'] + $num + $otherGoods > $xiangouInfo['xiangou_num']) {
                            $result['error'] = 1;
                            $result['cart_number'] = $num - 1;
                            return $result;//对不起，该商品已经累计超过限购数量
                        }
                    }
                }
            }
        }

        return $result;//未满足限购条件
    }

    /**
     * 清空购物车分期商品
     * @param
     */
    protected function clearQishuGoods($uid = 0)
    {
        $user_id = $uid > 0 ? intval($uid) : 0;

        if ($user_id > 0) {
            Cart::where('stages_qishu', '>', 0)->where('user_id', $user_id)->delete();
        }
    }

    /**
     * 购物车商品数量
     *
     * @param int $uid
     * @return mixed
     */
    public function cartNum($uid = 0)
    {
        if ($uid) {
            $row['cart_number'] = Cart::where('user_id', $uid)
                ->where('rec_type', 0)
                ->where('store_id', 0)
                ->sum('goods_number');
        } else {
            $realip = request()->header('X-Client-Hash');
            if (empty($realip)) {
                $row['cart_number'] = 0;
            } else {
                $session_id = $this->sessionRepository->realCartMacIp();
                $row['cart_number'] = Cart::where('session_id', $session_id)
                    ->where('rec_type', 0)
                    ->where('store_id', 0)
                    ->sum('goods_number');
            }
        }
        return $row;
    }
}
