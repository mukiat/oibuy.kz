<?php

namespace App\Services\Order;

use App\Models\GoodsActivity;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\OrderReturn;
use App\Models\Users;
use App\Models\ZcGoods;
use App\Models\ZcProject;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\StrRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\User\UserDataHandleService;

/**
 * 订单导出
 * Class OrderExportService
 * @package App\Services\Order
 */
class OrderExportService
{
    protected $dscRepository;
    protected $orderGoodsDataHandleService;
    protected $userDataHandleService;
    protected $merchantCommonService;

    public function __construct(
        DscRepository $dscRepository,
        OrderGoodsDataHandleService $orderGoodsDataHandleService,
        UserDataHandleService $userDataHandleService,
        MerchantCommonService $merchantCommonService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->orderGoodsDataHandleService = $orderGoodsDataHandleService;
        $this->userDataHandleService = $userDataHandleService;
        $this->merchantCommonService = $merchantCommonService;
    }

    /**
     * 导出订单内容 字段
     * @return array
     */
    public static function fieldName()
    {
        $fieldName = [
            'order_sn', 'shop_name', 'user_name', 'nickname', 'add_time',
            'goods_name', 'goods_attr', 'goods_sn', 'goods_price', 'cost_price', 'goods_number', 'goods_amount',
            'tax', 'shipping_fee', 'pay_fee', 'discount', 'surplus', 'integral', 'integral_money', 'bonus', 'coupons', 'value_card',
            'total_fee_order', 'money_paid', 'order_amount', 'total_cost_price',
            'consignee', 'mobile', 'address', 'invoice_type', 'inv_payee', 'inv_content', 'tax_id',
            'order_status', 'pay_name', 'pay_status', 'pay_time', 'shipping_status', 'shipping_time', 'shipping_name', 'invoice_no', 'referer', 'postscript'
        ];

        //供应链
        if (file_exists(SUPPLIERS)) {
            array_splice($fieldName, 6, 0, ['suppliers_name']);//供货商名称 增加至商品名称字段后
        }

        // 分销
        if (file_exists(MOBILE_DRP)) {
            $fieldName[] = 'drp_money';
        }

        // 跨境多商户
        if (CROSS_BORDER === true) {
            $fieldName[] = 'rate_fee';//综合税费
            $fieldName[] = 'rel_name';//真实姓名
            $fieldName[] = 'id_num';//身份证号
        }

        return $fieldName;
    }

    /**
     * 订单状态
     * @return array
     */
    public static function orderStatus()
    {
        return [
            'export_order_status_0',
            'export_order_status_01',
            'export_order_status_02',
            'export_order_status_03',
            'export_order_status_04',
            'export_order_status_05',
            'export_order_status_06',
            'export_order_status_07',
            'export_order_status_08',
            'export_order_status_09',
        ];
    }

    /**
     * 订单类型
     * @return array
     */
    public static function extensionCode()
    {
        return [
            'extension_all',
            'extension_pt',
            'extension_team_buy',
            'extension_bargain_buy',
            'extension_seckill',
            'extension_store_order',
            'extension_other', // 促销
            'extension_group_buy',
            'extension_auction',
            'extension_exchange_goods',
            'extension_presale',
            'extension_snatch',
            'extension_baitiao_order',
            'extension_zc',
        ];
    }

    /**
     * 订单来源
     * @return array
     */
    public static function orderReferer()
    {
        return [
            'all_referer',
            'PC',
            'H5',
            'wxapp',
            'app',
        ];
    }

    /**
     * 导出订单筛选条件
     *
     * @param array $filter
     * @return mixed
     */
    public static function exportOrderModel($filter = [])
    {
        /* 过滤信息 */
        $filter['user_name'] = empty($filter['user_name']) ? '' : trim($filter['user_name']);
        $filter['consignee'] = empty($filter['consignee']) ? '' : trim($filter['consignee']);


        $filter['start_time'] = empty($filter['start_time']) ? '' : (strpos($filter['start_time'], '-') > 0 ? TimeRepository::getLocalStrtoTime($filter['start_time']) : $filter['start_time']);
        $filter['end_time'] = empty($filter['end_time']) ? '' : (strpos($filter['end_time'], '-') > 0 ? TimeRepository::getLocalStrtoTime($filter['end_time']) : $filter['end_time']);
        $filter['order_status'] = !isset($filter['order_status']) ? 0 : intval($filter['order_status']);
        $filter['extension_code'] = !isset($filter['extension_code']) ? 'extension_all' : trim($filter['extension_code']);
        $filter['order_referer'] = !isset($filter['order_referer']) ? 'all_referer' : trim($filter['order_referer']);

        /* 分页大小 */
        $filter['page'] = empty($filter['page']) || (intval($filter['page']) <= 0) ? 1 : intval($filter['page']);

        $res = OrderInfo::query()->where('main_count', 0);

        $res = $res->where('ru_id', $filter['ru_id']);

        if (isset($filter['store_id']) && $filter['store_id'] > 0) {
            $res = $res->where(function ($query) use ($filter) {
                $query->whereHasIn('getStoreOrder', function ($query) use ($filter) {
                    $query->where('store_id', $filter['store_id']);
                });
            });
        }

        if ($filter['order_sn']) {
            $orderGoods = OrderGoods::distinct()->select('order_id')->where(function ($query) use ($filter) {
                $query->where('goods_name', 'LIKE', '%' . $filter['order_sn'] . '%')
                    ->orWhere('goods_sn', $filter['order_sn']);
            });

            $orderGoods = $orderGoods->pluck('order_id');
            $order_id = BaseRepository::getToArray($orderGoods);

            $res = $res->where(function ($query) use ($filter, $order_id) {
                $query = $query->where('order_sn', $filter['order_sn']);
                if ($order_id) {
                    $query->orWhere(function ($query) use ($order_id) {
                        $query->whereIn('order_id', $order_id);
                    });
                }
            });
        }

        if ($filter['consignee']) {
            $res = $res->where('consignee', 'LIKE', '%' . mysql_like_quote($filter['consignee']) . '%');
        }

        if ($filter['user_name']) {
            $user = Users::distinct()->select('user_id')->where('user_name', 'LIKE', '%' . mysql_like_quote($filter['user_name']) . '%');
            $user = $user->pluck('user_id');
            $user_id = BaseRepository::getToArray($user);

            $res = $user_id ? $res->whereIn('user_id', $user_id) : $res;
        }

        if ($filter['start_time']) {
            $res = $res->where('add_time', '>=', $filter['start_time']);
        }
        if ($filter['end_time']) {
            $res = $res->where('add_time', '<=', $filter['end_time']);
        }
        // 订单来源
        if (isset($filter['order_referer']) && $filter['order_referer']) {
            if ($filter['order_referer'] != 'all_referer') {
                if ($filter['order_referer'] == 'PC') {
                    $res = $res->whereNotIn('referer', ['mobile', 'touch', 'H5', 'app', 'wxapp', 'ecjia-cashdesk']);
                } else {
                    $res = $res->where('referer', $filter['order_referer']);
                }
            }
        }

        // 订单类型
        if ($filter['extension_code']) {
            switch ($filter['extension_code']) {
                case 'extension_all':

                    break;
                case 'extension_pt':
                    $res = $res->where('extension_code', '');
                    break;
                case 'extension_team_buy':
                    $res = $res->where('extension_code', 'team_buy');
                    break;
                case 'extension_bargain_buy':
                    $res = $res->where('extension_code', 'bargain_buy');
                    break;
                case 'extension_seckill':
                    $res = $res->where('extension_code', 'seckill');
                    break;
                case 'extension_store_order':
                    $res = $res->where(function ($query) {
                        $query->whereHasIn('getStoreOrder');
                    });
                    break;
                case 'extension_other':
                    $res = $res->where('extension_code', '<>', '');
                    break;
                case 'extension_group_buy':
                    $res = $res->where('extension_code', 'group_buy');
                    break;
                case 'extension_auction':
                    $res = $res->where('extension_code', 'auction');
                    break;
                case 'extension_exchange_goods':  // 积分
                    $res = $res->where('extension_code', 'exchange_goods');
                    break;
                case 'extension_presale':
                    $res = $res->where('extension_code', 'presale');
                    break;
                case 'extension_snatch':
                    $res = $res->where('extension_code', 'snatch');
                    break;
                case 'extension_baitiao_order':
                    $res = $res->where(function ($query) {
                        $query->whereHasIn('getBaitiaoLog');
                    });
                    break;
                case 'extension_zc':
                    $res = $res->where('is_zc_order', 1);
                    break;
                default:
            }
        }

        /* 团购订单 */
        if (isset($filter['group_buy_id']) && $filter['group_buy_id']) {
            $res = $res->where('extension_code', 'group_buy')->where('extension_id', $filter['group_buy_id']);
        }

        /* 预售订单 */
        if (isset($filter['presale_id']) && $filter['presale_id']) {
            $res = $res->where('extension_code', 'presale')->where('extension_id', $filter['presale_id']);
        }

        /* 众筹订单 by wu */
        if (isset($filter['pid']) && $filter['pid']) {
            $zg_res = ZcGoods::select('id')->where('pid', $filter['pid']);
            $zg_res = BaseRepository::getToArrayGet($zg_res);
            $goods_ids = BaseRepository::getFlatten($zg_res);

            $res = $res->where('is_zc_order', 1)->whereIn('zc_goods_id', $goods_ids);
        }

        if (isset($filter['gid']) && $filter['gid']) {
            $res = $res->where('is_zc_order', 1)->where('zc_goods_id', $filter['gid']);
        }

        $page_size = request()->cookie('dsccp_page_size');
        if (isset($filter['page_size']) && intval($filter['page_size']) > 0) {
            $filter['page_size'] = intval($filter['page_size']);
        } elseif (intval($page_size) > 0) {
            $filter['page_size'] = intval($page_size);
        } else {
            $filter['page_size'] = 15;
        }

        //判断订单筛选条件
        switch ($filter['order_status']) {
            case 0:
                //全部
                break;
            case 1:
                //待确认
                $res = $res->where('order_status', OS_UNCONFIRMED);
                break;
            case 2:
                //待付款
                $res = $res->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED])->where('pay_status', PS_UNPAYED);
                break;
            case 3:
                //待发货,--191012 update：后台部分发货加入待发货列表，前台部分发货加入待收货列表
                $res = app(OrderCommonService::class)->orderQuerySelect($res, 'await_ship');
                break;
            case 4:
                //已完成
                $res = $res->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED])->where('pay_status', PS_PAYED)->where('shipping_status', SS_RECEIVED);
                break;
            case 5:
                //付款中
                $res = $res->where('pay_status', PS_PAYING);
                break;
            case 6:
                //取消
                $res = $res->where('order_status', OS_CANCELED);
                break;
            case 7:
                //无效
                $res = $res->where('order_status', OS_INVALID);
                break;
            case 8:
                //退货
                $res = $res->whereNotIn('order_status', ["'" . OS_CANCELED . "', '" . OS_INVALID . "', '" . OS_RETURNED . "', '" . OS_RETURNED_PART . "', '" . OS_ONLY_REFOUND . "'"]);
                $res = $res->where(function ($query) {
                    $query->whereHasIn('getOrderReturn', function ($query) {
                        $query->whereIn('return_type', [1, 3])->where('refound_status', 0)->where('return_status', '<>', 6);
                    });
                });
                break;
            case 9:
                //待收货
                $res = $res->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED])->where('shipping_status', SS_SHIPPED);
                break;
        }

        return $res;
    }

    /**
     * 导出订单总数
     * @param array $filter
     * @return int
     */
    public function exportOrderTotal($filter = [])
    {
        $model = self::exportOrderModel($filter);

        $total = $model->count('order_id');

        return $total ?? 0;
    }

    /**
     * 导出订单列表数据 按页条件查询
     *
     * @param array $filter
     * @param int $start
     * @param int $limit
     * @return array
     * @throws \Exception
     */
    public function exportOrderList($filter = [], $start = 0, $limit = 100)
    {
        $model = self::exportOrderModel($filter);

        /* 查询 */
        $model = $model->selectRaw("*, extension_code AS oi_extension_code, extension_code AS o_extension_code, " .
            "(" . self::orderTotalField() . ") AS total_fee, (goods_amount + tax + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee - discount) AS total_fee_order ");

        // 分页查询
        $model = $model->skip($start);
        if ($limit > 0) {
            $model = $model->take($limit);
        }

        $model = $model->orderBy('add_time', 'DESC');

        $row = BaseRepository::getToArrayGet($model);

        if ($row) {

            /**
             * 当前订单ID
             */
            $order_id = BaseRepository::getKeyPluck($row, 'order_id');

            $drpOrder = [];
            if (file_exists(MOBILE_DRP)) {
                $drpOrder = OrderDataHandleService::isDrpOrder($order_id);
            }

            $childOrderList = OrderDataHandleService::orderDataList($order_id, ['order_id', 'main_order_id', 'order_sn'], 1);
            $storeOrder = OrderDataHandleService::isStoreOrder($order_id);

            $valueCard = OrderDataHandleService::orderValueCardRecord($order_id);
            $orderGoodsList = $this->orderGoodsDataHandleService->manageOrderGoods($order_id);
            $stages = OrderDataHandleService::isStagesBaiTiao($order_id);

            /**
             * 当前会员ID
             */
            $user_id = BaseRepository::getKeyPluck($row, 'user_id');
            $user = $this->userDataHandleService->orderUser($user_id);

            /**
             * 当前商家ID
             */
            $ru_id = BaseRepository::getKeyPluck($row, 'ru_id');
            $shopInformation = MerchantDataHandleService::MerchantsShopInformationDataList($ru_id);
            $sellerShopinfo = MerchantDataHandleService::SellerShopinfoDataList($ru_id);
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id, 0, $sellerShopinfo, $shopInformation);

            $countryRegion = BaseRepository::getColumn($row, 'country', 'order_id');
            $provinceRegion = BaseRepository::getColumn($row, 'province', 'order_id');
            $cityRegion = BaseRepository::getColumn($row, 'city', 'order_id');
            $districtRegion = BaseRepository::getColumn($row, 'district', 'order_id');
            $streetRegion = BaseRepository::getColumn($row, 'street', 'order_id');

            $orderRegion = OrderDataHandleService::orderRegionAddress($countryRegion, $provinceRegion, $cityRegion, $districtRegion, $streetRegion);

            foreach ($row as $key => $value) {

                $goodsSuppliersList = [];
                $suppliersList = [];
                //供应链
                if (file_exists(SUPPLIERS)) {
                    $goodsIdList = BaseRepository::getKeyPluck($orderGoodsList[$value['order_id']], 'goods_id');
                    $goodsSuppliersList = GoodsDataHandleService::GoodsDataList($goodsIdList, ['goods_id', 'suppliers_id']);
                    $goodsSuppliersId = BaseRepository::getKeyPluck($goodsSuppliersList, 'suppliers_id');
                    $suppliersList = $this->suppliersList($goodsSuppliersId);
                }

                $shop_information = $merchantList[$value['ru_id']] ?? []; //通过ru_id获取到店铺信息;

                //判断是否分销订单
                $row[$key]['is_drp_order'] = $drpOrder[$value['order_id']]['is_drp'] ?? 0;

                //判断是否为门店订单
                $row[$key]['is_store_order'] = $storeOrder[$value['order_id']]['is_store'] ?? 0;

                $is_stages = $stages[$value['order_id']]['is_stages'] ?? 0;
                $row[$key]['is_stages'] = $is_stages;

                //查会员名称
                $value['buyer'] = $user[$value['user_id']]['user_name'] ?? '';
                $value['nickname'] = $user[$value['user_id']]['nick_name'] ?? '';
                $row[$key]['user_name'] = !empty($value['buyer']) ? $value['buyer'] : lang('admin/order.anonymous');
                $row[$key]['nickname'] = !empty($value['nickname']) ? $value['nickname'] : lang('admin/order.anonymous');

                $row[$key]['ru_id'] = $value['ru_id'];

                $row[$key]['formated_order_amount'] = $this->dscRepository->getPriceFormat($value['order_amount']);
                $row[$key]['formated_money_paid'] = $this->dscRepository->getPriceFormat($value['money_paid']);
                $row[$key]['formated_total_fee'] = $this->dscRepository->getPriceFormat($value['total_fee']);
                $row[$key]['old_shipping_fee'] = $value['shipping_fee'];
                $row[$key]['shipping_fee'] = $this->dscRepository->getPriceFormat($value['shipping_fee']);
                $row[$key]['add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $value['add_time']);
                $row[$key]['pay_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $value['pay_time']);
                $row[$key]['shipping_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $value['shipping_time']);

                $value_card = $valueCard[$value['order_id']]['use_val'] ?? 0;
                $row[$key]['value_card'] = $value_card;
                $row[$key]['formated_value_card'] = $this->dscRepository->getPriceFormat($value_card);

                $row[$key]['formated_total_fee_order'] = $this->dscRepository->getPriceFormat($value['total_fee_order']);

                /* 取得区域名 */
                $country = $orderRegion['country'][$value['order_id']]['region_name'] ?? '';
                $province = $orderRegion['province'][$value['order_id']]['region_name'] ?? '';
                $city = $orderRegion['city'][$value['order_id']]['region_name'] ?? '';
                $district = $orderRegion['district'][$value['order_id']]['region_name'] ?? '';
                $street = $orderRegion['street'][$value['order_id']]['region_name'] ?? '';

                $row[$key]['region'] = OrderDataHandleService::orderRegionCombination($country, $province, $city, $district, $street);

                //查询子订单
                $row[$key]['child_list'] = $childOrderList[$value['order_id']] ?? '';
                $row[$key]['order_child'] = $value['main_count'];

                $row[$key]['suppliers_name'] = '';
                $row[$key]['is_order_return'] = 0;
                $total_cost_price = 0;
                if (isset($value['is_zc_order']) && $value['is_zc_order'] == 1) {
                    $zc_goods = ZcGoods::where('id', $value['zc_goods_id']);
                    $zc_goods = BaseRepository::getToArrayFirst($zc_goods);
                    $project_id = !empty($zc_goods) ? $zc_goods['pid'] : 0;
                    $zc_project = ZcProject::where('id', $project_id);
                    $zc_project = BaseRepository::getToArrayFirst($zc_project);
                    $zcg = [
                        'goods_name' => $zc_project['title'],
                        'goods_thumb' => $this->dscRepository->getImagePath($zc_project['title_img']),
                        'goods_price' => !empty($zc_goods) ? $zc_goods['price'] : 0,
                        'goods_number' => 1
                    ];
                    $row[$key]['goods_list'][] = $zcg;
                    $row[$key]['shop_name'] = $shop_information['shop_name'] ?? '';
                } else {
                    $goods_list = $orderGoodsList[$value['order_id']] ?? [];

                    if ($goods_list) {
                        $iog_extension_codes = [];
                        foreach ($goods_list as $idx => $goods) {

                            $goods_list[$idx]['suppliers_name'] = '';
                            //供应链
                            if (file_exists(SUPPLIERS)) {
                                $goodsSuppliers = $goodsSuppliersList[$goods['goods_id']] ?? [];
                                $suppliers = $suppliersList[$goodsSuppliers['suppliers_id']] ?? [];
                                $goods_list[$idx]['suppliers_name'] = $suppliers['suppliers_name'] ?? '';
                            }

                            if ($goods['extension_code']) {
                                $iog_extension_codes[$idx] = $goods['extension_code'];
                            }

                            $total_cost_price += $goods['cost_price'];

                            $goods_list[$idx]['ret_id'] = $goods['ret_id'] ?? 0;
                            if ($goods_list[$idx]['ret_id'] > 0) {
                                $return_status = OrderReturn::where('ret_id', $goods_list[$idx]['ret_id'])->value('return_status');

                                if (!is_null($return_status) && $return_status < 0) {
                                    $return_status = trans('user.only_return_money');
                                } else {
                                    $return_status = trans('user.rf.' . $return_status);
                                }

                                //判断是否为退换货订单
                                $row[$key]['is_order_return'] = 1;
                                $goods_list[$idx]['return_status_format'] = $return_status; // 退换货状态
                            }

                            //超值礼包图片
                            if ($goods['extension_code'] == 'package_buy') {
                                $activity = GoodsActivity::select('activity_thumb', 'ext_info')->where('act_id', $goods['goods_id']);
                                $activity = BaseRepository::getToArrayFirst($activity);

                                $activity_thumb = $activity['activity_thumb'] ?? '';
                                $goods_list[$idx]['goods_thumb'] = $activity_thumb ? $this->dscRepository->getImagePath($activity_thumb) : $this->dscRepository->dscUrl('themes/ecmoban_dsc2017/images/17184624079016pa.jpg');
                                $goods_list[$idx]['goods_img'] = $goods_list[$idx]['goods_thumb'];

                                $goods_list[$idx]['package_goods_list'] = app(OrderManageService::class)->getPackageGoodsList($goods['goods_id']);
                            }

                            $goods_list[$idx]['format_goods_price'] = $this->dscRepository->getPriceFormat($goods['goods_price']);
                        }

                        $iog_extension_codes = $iog_extension_codes ? array_unique($iog_extension_codes) : [];
                        $row[$key]['iog_extension_codes'] = $iog_extension_codes ? implode(',', $iog_extension_codes) : '';
                    }

                    $row[$key]['goods_list'] = $goods_list;

                    $row[$key]['self_run'] = $shop_information['self_run'] ?? 0;
                    $row[$key]['shop_name'] = $shop_information['shop_name'] ?? '';

                    if (!empty($child_list)) {
                        $row[$key]['shop_name'] = trans('manage/order.to_order_sn2');
                    }
                }

                $row[$key]['total_cost_price'] = $total_cost_price;
            }

            $export = self::exportDownloadOrderListContent($row);
        }

        return $export ?? [];
    }

    /**
     * 生成查询订单总金额的字段
     *
     * @return string
     */
    protected static function orderTotalField()
    {
        return " goods_amount + tax + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee ";
    }

    /**
     * 输出导入数据
     * @param array $order
     * @return array
     */
    public static function exportDownloadOrderListContent($order = [])
    {
        $order_list = [];

        $count = count($order);
        for ($i = 0; $i < $count; $i++) {
            // 订单商品信息
            $goods_count = 0;
            if (!empty($order[$i]['goods_list'])) {
                foreach ($order[$i]['goods_list'] as $j => $goods) {
                    // 订单商品信息
                    $goods_name = !empty($goods['goods_name']) ? rtrim($goods['goods_name']) : '';
                    $goods_attr = !empty($goods['goods_attr']) ? rtrim($goods['goods_attr']) : '';

                    if (isset($goods['return_status_format']) && !empty($goods['return_status_format'])) {
                        $goods_name .= '(' . $goods['return_status_format'] . ')';
                    }

                    $row['goods_name'] = "\"$goods_name\""; // 转义字符是关键 不然不是表格内换行
                    $row['goods_attr'] = "\"$goods_attr\""; // 转义字符是关键 不然不是表格内换行
                    // 商品货号
                    $goods_sn = !empty($goods['product_sn']) ? $goods['product_sn'] : $goods['goods_sn'] ?? '';
                    $goods_sn = rtrim($goods_sn); // 去除最末位换行符
                    $row['goods_sn'] = "\"$goods_sn\""; // 转义字符是关键 不然不是表格内换行
                    $row['goods_number'] = $goods['goods_number'] ?? 1;
                    $row['goods_price'] = $goods['goods_price'] ?? 0;
                    $row['cost_price'] = $goods['cost_price'] ?? 0;

                    if (file_exists(SUPPLIERS)) {
                        $row['suppliers_name'] = $goods['suppliers_name'] ?? '';
                    }

                    if (file_exists(MOBILE_DRP)) {
                        $row['drp_money'] = $goods['drp_money'] ?? 0; // 分销佣金
                    }

                    // 订单信息
                    $row['postscript'] = rtrim($order[$i]['postscript'] ?? '', '|');
                    $row['postscript'] = StrRepository::filterSpecialCharacters($row['postscript']);// 订单留言
                    $row['order_sn'] = $order[$i]['order_sn']; //订单号
                    $row['user_name'] = $order[$i]['user_name'];
                    $row['nickname'] = $order[$i]['nickname'];
                    $row['nickname'] = StrRepository::filterSpecialCharacters($order[$i]['nickname']); // 过滤微信昵称特殊字符
                    $row['add_time'] = $order[$i]['add_time'];
                    $row['pay_time'] = $order[$i]['pay_time'];
                    $row['shipping_time'] = $order[$i]['shipping_time'];
                    $row['consignee'] = StrRepository::filterSpecialCharacters($order[$i]['consignee']);// 收货人
                    $row['mobile'] = !empty($order[$i]['mobile']) ? $order[$i]['mobile'] : $order[$i]['tel'];
                    $row['address'] = addslashes(str_replace(",", "，", "[" . $order[$i]['region'] . "] " . $order[$i]['address']));
                    // 订单发票类型
                    $invoice_type = $order[$i]['invoice_type'] ?? 0;
                    if ($invoice_type == 0) {
                        $row['invoice_type'] = !empty($order[$i]['tax_id']) ? trans('admin/order.enterprise_general_invoice') : trans('admin/order.personal_general_invoice');
                    } elseif ($invoice_type == 1) {
                        $row['invoice_type'] = trans('admin/order.VAT_invoice');
                    }
                    $row['inv_payee'] = $order[$i]['inv_payee'] ?? '';// 订单发票抬头
                    $row['inv_content'] = $order[$i]['inv_content'] ?? ''; //订单发票内容
                    $row['tax_id'] = $order[$i]['tax_id'] ?? ''; //订单发票纳税识别号
                    $row['total_cost_price'] = $order[$i]['total_cost_price'] ?? 0;// 订单总成本价
                    $row['shipping_fee'] = $order[$i]['old_shipping_fee'];//配送费用
                    $row['insure_fee'] = $order[$i]['insure_fee'];//保价费用
                    $row['pay_fee'] = $order[$i]['pay_fee'];//支付费用
                    if (CROSS_BORDER === true) { // 跨境多商户
                        $row['rate_fee'] = $order[$i]['rate_fee'];//综合税费
                        $row['rel_name'] = $order[$i]['rel_name'];//真实姓名
                        $row['id_num'] = $order[$i]['id_num'];//身份证号
                    }
                    $row['surplus'] = $order[$i]['surplus'];//余额费用
                    $row['money_paid'] = $order[$i]['money_paid'];//已付款金额
                    $row['integral'] = $order[$i]['integral'] ?? 0;//使用积分
                    $row['integral_money'] = $order[$i]['integral_money'];//积分抵扣金额
                    $row['bonus'] = $order[$i]['bonus'];//红包金额
                    $row['tax'] = $order[$i]['tax'];//发票税额
                    $row['discount'] = $order[$i]['discount'];//折扣金额
                    $row['coupons'] = $order[$i]['coupons'];//优惠券金额
                    $row['value_card'] = $order[$i]['value_card']; // 储值卡
                    $row['order_status'] = trans('admin/order.os.' . $order[$i]['order_status']);
                    $row['shop_name'] = $order[$i]['shop_name'] ?? ''; //商家名称
                    $row['pay_status'] = trans('admin/order.ps.' . $order[$i]['pay_status']);
                    $row['shipping_status'] = trans('admin/order.ss.' . $order[$i]['shipping_status']);
                    // 订单来源
                    $referer = $order[$i]['referer'] ?? '';
                    if ($referer == 'touch') {
                        $row['referer'] = "WAP";
                    } elseif ($referer == 'mobile' || $referer == 'app') {
                        $row['referer'] = "APP";
                    } elseif ($referer == 'H5') {
                        $row['referer'] = "H5";
                    } elseif ($referer == 'wxapp') {
                        $row['referer'] = trans('admin/order.wxapp');
                    } elseif ($referer == 'ecjia-cashdesk') {
                        $row['referer'] = trans('admin/order.cashdesk');
                    } else {
                        $row['referer'] = "PC";
                    }

                    $row['invoice_no'] = $order[$i]['invoice_no'] ?? '';
                    $row['pay_name'] = $order[$i]['pay_name'] ?? '';
                    $row['shipping_name'] = $order[$i]['shipping_name'] ?? '';
                    $row['goods_amount'] = $order[$i]['goods_amount'] ?? 0;
                    $row['order_amount'] = $order[$i]['order_amount'] ?? 0;
                    $row['money_paid'] = $order[$i]['money_paid'] ?? 0;
                    $row['total_fee'] = $order[$i]['total_fee'] ?? 0; // 总金额
                    $row['total_fee_order'] = $order[$i]['total_fee_order'] ?? 0; // 订单总金额
                    // 用于导出Excel合并单元格
                    $goods_count += 1;
                    $row['goods_count'] = $goods_count;

                    $order_list[] = $row;
                }
            }
        }

        return $order_list;
    }

    /**
     * 供货商
     *
     * @param array $suppliersIdList
     * @return array
     */
    private function suppliersList($suppliersIdList = [])
    {
        $suppliersList = \App\Modules\Suppliers\Models\Suppliers::select('suppliers_id', 'suppliers_name')->whereIn('suppliers_id', $suppliersIdList);
        $suppliersList = BaseRepository::getToArrayGet($suppliersList);

        $arr = [];
        if ($suppliersList) {
            foreach ($suppliersList as $key => $row) {
                $arr[$row['suppliers_id']] = $row;
            }
        }

        return $arr;
    }
}
