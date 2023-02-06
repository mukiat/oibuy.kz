<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Models\Cart;
use App\Models\Goods;
use App\Models\StoreGoods;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscEncryptRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Flow\FlowRepository;
use App\Services\Cart\CartCommonService;
use App\Services\Common\AreaService;
use App\Services\Flow\FlowUserService;
use App\Services\Goods\GoodsAttrService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Goods\GoodsMobileService;
use App\Services\OfflineStore\OfflineStoreService;
use App\Services\Shipping\ShippingService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class ShippingController
 * @package App\Api\Controllers
 */
class ShippingController extends Controller
{
    protected $areaService;
    protected $shippingService;
    protected $dscRepository;
    protected $flowUserService;
    protected $cartCommonService;
    protected $offlineStoreService;

    public function __construct(
        ShippingService $shippingService,
        AreaService $areaService,
        DscRepository $dscRepository,
        FlowUserService $flowUserService,
        CartCommonService $cartCommonService,
        OfflineStoreService $offlineStoreService
    )
    {
        //加载配置文件
        $this->shippingService = $shippingService;
        $this->areaService = $areaService;
        $this->dscRepository = $dscRepository;
        $this->flowUserService = $flowUserService;
        $this->cartCommonService = $cartCommonService;
        $this->offlineStoreService = $offlineStoreService;
    }

    protected function initialize()
    {
        parent::initialize();

        //加载外部类
        $files = [
            'common',
            'time',
            'order',
            'function',
            'ecmoban',
            'goods',
            'base',
        ];

        load_helper($files);

        //加载语言包
        $this->dscRepository->helpersLang('user');
    }

    /**
     * 配送列表
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function index(Request $request)
    {
        $user_id = $this->authorization();

        $rec_ids = $request->get('rec_ids', '');
        $rec_ids = DscEncryptRepository::filterValInt($rec_ids);
        $ru_id = (int)$request->get('ru_id', 0);
        $consignee = $request->get('consignee', '');
        $consignee = dsc_decode($consignee, true);
        $whereCart = [];
        $whereCart['flow_type'] = $request->get('flow_type', 100);
        $whereCart['flow_consignee'] = $consignee;

        if (empty($user_id) || empty($rec_ids)) {
            $arr['shipping'] = 0;
            $arr['is_freight'] = 0;
            $arr['shipping_rec'] = 0;
            $arr['tmp_shipping_id'] = 0;
        }

        $rec_ids = BaseRepository::getExplode($rec_ids);

        $cart_goods = Cart::select('rec_id', 'goods_id', 'model_attr', 'ru_id', 'tid', 'freight', 'shipping_fee', 'goods_number', 'goods_id', 'goods_price', 'model_attr', 'product_id')
            ->where('user_id', $user_id)
            ->whereIn('rec_id', $rec_ids);
        $cart_goods = $cart_goods->with([
            'getGoods' => function ($query) {
                $query->select();
            }
        ]);
        $cart_goods = BaseRepository::getToArrayGet($cart_goods);

        if ($cart_goods) {

            $cartIdList = FlowRepository::cartGoodsAndPackage($cart_goods);
            $goods_id = $cartIdList['goods_id']; //普通商品ID

            $goodsWhere = [
                'goods_select' => [
                    'goods_id', 'free_rate', 'goods_weight', 'freight', 'tid', 'shipping_fee'
                ]
            ];
            $goodsList = GoodsDataHandleService::GoodsCartDataList($goods_id, $goodsWhere);

            $recGoodsModelList = BaseRepository::getColumn($cart_goods, 'model_attr', 'rec_id');
            $recGoodsModelList = $recGoodsModelList ? array_unique($recGoodsModelList) : [];

            $isModel = 0;
            if (in_array(1, $recGoodsModelList) || in_array(2, $recGoodsModelList)) {
                $isModel = 1;
            }

            $product_id = BaseRepository::getKeyPluck($cart_goods, 'product_id');
            $productsList = GoodsDataHandleService::getProductsDataList($product_id, '*', 'product_id');

            if ($isModel == 1) {
                $productsWarehouseList = GoodsDataHandleService::getProductsWarehouseDataList($product_id, 0, '*', 'product_id');
                $productsAreaList = GoodsDataHandleService::getProductsAreaDataList($product_id, 0, '*', 'product_id');
            } else {
                $productsWarehouseList = [];
                $productsAreaList = [];
            }

            foreach ($cart_goods as $key => $val) {

                $cart_goods[$key]['get_goods'] = $goodsList[$val['goods_id']] ?? [];

                $cart_goods[$key]['tid'] = $cart_goods[$key]['get_goods']['tid'] ?? 0;

                if ($val['model_attr'] == 1) {
                    $sku_weight = $productsWarehouseList[$val['product_id']]['sku_weight'] ?? 0; //货品重量
                } elseif ($val['model_attr'] == 2) {
                    $sku_weight = $productsAreaList[$val['product_id']]['sku_weight'] ?? 0; //货品重量
                } else {
                    $sku_weight = $productsList[$val['product_id']]['sku_weight'] ?? 0; //货品重量
                }

                $cart_goods[$key]['sku_weight'] = $sku_weight;
            }
        }

        $shippingList = $this->shippingService->goodsShippingTransport($cart_goods, $consignee);

        $ru_shipping = $shippingList[$ru_id] ?? [];

        $arr['shipping'] = $ru_shipping['shipping'] ?? [];
        $arr['is_freight'] = $ru_shipping['is_freight'] ?? 0;
        $arr['shipping_rec'] = $ru_shipping['shipping_rec'] ?? [];
        $arr['shipping_count'] = $ru_shipping['shipping_count'] ?? 0;
        $arr['tmp_shipping_id'] = $ru_shipping['tmp_shipping_id'] ?? 0;

        return $this->succeed($arr);
    }

    /**
     * 配送价格
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function amount(Request $request)
    {
        $result = ['error' => 0, 'massage' => '', 'content' => '', 'need_insure' => 0, 'payment' => 1];

        $user_id = $this->authorization();
        $ru_id = $request->get('ru_id', 0);
        $shipping_type = $request->get('type', 0);
        $tmp_shipping_id = $request->get('shipping_id', []);
        $rec_ids = $request->get('rec_ids', '');
        $order['shipping_type'] = $request->get('shipping_type', 0);
        $order['shipping_code'] = $request->get('shipping_code', '');
        $store_id = $request->get('store_id', 0);

        if (empty($user_id)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        /* 取得购物类型 */
        $flow_type = intval(session('flow_type', CART_GENERAL_GOODS));

        /* 获得收货人信息 */
        $consignee = $this->flowUserService->getConsignee($user_id);

        /* 对商品信息赋值 */
        $cart_goods_list = cart_goods($flow_type, $rec_ids, 1, $consignee, $store_id, $user_id);
        if (empty($cart_goods_list) || !$this->flowUserService->checkConsigneeInfo($consignee, $flow_type, $user_id)) {
            if (empty($cart_goods_list)) {
                $result['error'] = 1;
            } elseif (!$this->flowUserService->checkConsigneeInfo($consignee, $flow_type)) {
                $result['error'] = 2;
            }
        } else {

            /* 取得订单信息 */
            $order = flow_order_info($user_id);
            /* 保存 session */
            $order['shipping_id'] = $tmp_shipping_id;

            if ($shipping_type == 1) {
                if (session('shipping_type_ru_id') && is_array(session('shipping_type_ru_id'))) {
                    session()->put('shipping_type_ru_id.' . $ru_id, $ru_id);
                }
            } else {
                if (session()->has('shipping_type_ru_id.' . $ru_id)) {
                    session()->forget('shipping_type_ru_id.' . $ru_id, $ru_id);
                }
            }

            if ($tmp_shipping_id) {
                $tmp_shipping_id_arr = $tmp_shipping_id;
            } else {
                $tmp_shipping_id_arr = [];
            }

            //ecmoban模板堂 --zhuo start
            $cart_goods_number = $this->cartCommonService->getBuyCartGoodsNumber($flow_type, $rec_ids, $user_id);

            $this->assign('cart_goods_number', $cart_goods_number);
            $consignee['province_name'] = get_goods_region_name($consignee['province']);
            $consignee['city_name'] = get_goods_region_name($consignee['city']);
            $consignee['district_name'] = get_goods_region_name($consignee['district']);
            $consignee['street'] = get_goods_region_name($consignee['street']);//街道
            $consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'] . $consignee['street'];

            $this->assign('consignee', $consignee);

            //切换配送方式 by kong
            foreach ($cart_goods_list as $key => $val) {
                foreach ($tmp_shipping_id_arr as $k => $v) {
                    if ($v[1] > 0 && $val['ru_id'] == $v[0]) {
                        $cart_goods_list[$key]['tmp_shipping_id'] = $v[1];
                    }
                }
            }

            /* 计算订单的费用 */
            $cart_goods = $this->shippingService->get_new_group_cart_goods($cart_goods_list); // 取得商品列表，计算合计

            $total = order_fee($order, $cart_goods, $consignee, $rec_ids, $cart_goods_list, $store_id, '', $user_id);

            /* 团购标志 */
            if ($flow_type == CART_GROUP_BUY_GOODS) {
                $result['is_group_buy'] = 1;
            }
            $result['amount'] = $total['amount_formated'];
            $result['order'] = $order;
            $result['total'] = $total;
        }

        return $this->succeed($result);
    }

    /**
     * 配送价格
     *
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    public function goodsShippingFee(Request $request)
    {
        $goods_id = (int)$request->get('goods_id', 0);
        $attr_id = $request->get('goods_attr_id', ''); //商品详情点击触发
        $attr_id = DscEncryptRepository::filterValInt($attr_id);
        $is_price = $request->get('is_price', 0); //商品详情点击触发
        $position = $request->get('position', '');
        $position = dsc_decode($position, true);
        $province_id = isset($position['province_id']) ? intval($position['province_id']) : 0;
        $city_id = isset($position['city_id']) ? intval($position['city_id']) : 0;
        $district_id = isset($position['district_id']) ? intval($position['district_id']) : 0;
        $street = isset($position['street']) ? intval($position['street']) : 0;

        /* 生成仓库地区缓存 */
        $warehouseCache = $this->areaService->setWarehouseCache($this->uid, $province_id, $city_id, $district_id, $street);

        if ($goods_id > 0) {

            $seller_id = Goods::where('goods_id', $goods_id)->value('user_id');
            $seller_id = $seller_id ? $seller_id : 0;

            $region = [1, $province_id, $city_id, $district_id, $street];
            $data = goodsShippingFee($goods_id, $warehouseCache['warehouse_id'], $warehouseCache['area_id'], $warehouseCache['area_city'], $region, '', $attr_id);

            if ($is_price == 1) {
                $data['goods'] = app(GoodsMobileService::class)->goodsPropertiesPrice($this->uid, $goods_id, $attr_id, 1, $warehouseCache['warehouse_id'], $warehouseCache['area_id'], $warehouseCache['area_city']);
            }

            /*判断该地区是否存在自提门店，并且有库存*/
            $store = $this->offlineStoreService->offilineStoreIsSet($goods_id, $seller_id, $province_id, $city_id, $district_id);

            $data['store_count'] = 0;
            if (!empty($store)) {
                if (!empty($attr_id)) {
                    $storeProduct = app(GoodsAttrService::class)->getProductsInfo($goods_id, $attr_id, $warehouseCache['warehouse_id'], $warehouseCache['area_id'], $warehouseCache['area_city'], $store['id']);
                    $stock_number = $storeProduct['product_number'] ?? 0;
                } else {
                    $stock_number = StoreGoods::where('goods_id', $goods_id)->where('store_id', $store['id'])->value('goods_number');
                    $stock_number = $stock_number ? $stock_number : 0;
                }

                if ($stock_number > 0) {
                    $data['store_count'] = 1;
                    $data['store_info'] = $store;
                }
            }
        } else {
            $data = [
                'free_money' => '',
                'is_shipping' => 0,
                'shipping_code' => '',
                'shipping_fee' => 0,
                'shipping_fee_formated' => $this->dscRepository->getPriceFormat(0),
                'shipping_id' => 0,
                'shipping_name' => '',
                'shipping_title' => '',
                'shipping_type' => '',
                'store_count' => 0
            ];
        }

        return $this->succeed($data);
    }
}
