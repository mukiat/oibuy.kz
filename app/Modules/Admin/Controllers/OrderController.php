<?php

namespace App\Modules\Admin\Controllers;

use App\Exceptions\HttpException;
use App\Models\Ad;
use App\Models\AdminUser;
use App\Models\Agency;
use App\Models\BackGoods;
use App\Models\BackOrder;
use App\Models\BaitiaoLog;
use App\Models\BonusType;
use App\Models\Comment;
use App\Models\Coupons;
use App\Models\CouponsUser;
use App\Models\DeliveryGoods;
use App\Models\DeliveryOrder;
use App\Models\Goods;
use App\Models\GoodsAttr;
use App\Models\GoodsInventoryLogs;
use App\Models\GoodsTransportExpress;
use App\Models\GoodsTransportExtend;
use App\Models\MemberPrice;
use App\Models\OfflineStore;
use App\Models\OrderAction;
use App\Models\OrderCloud;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\OrderReturn;
use App\Models\OrderReturnExtend;
use App\Models\PayLog;
use App\Models\PresaleActivity;
use App\Models\Products;
use App\Models\ProductsArea;
use App\Models\ProductsWarehouse;
use App\Models\Region;
use App\Models\RegionWarehouse;
use App\Models\ReturnCause;
use App\Models\ReturnGoods;
use App\Models\SellerBillOrder;
use App\Models\SellerDomain;
use App\Models\SellerNegativeOrder;
use App\Models\SellerShopinfo;
use App\Models\Shipping;
use App\Models\ShippingPoint;
use App\Models\ShippingTpl;
use App\Models\Stages;
use App\Models\StoreOrder;
use App\Models\UserAddress;
use App\Models\UserBonus;
use App\Models\UserOrderNum;
use App\Models\UserRank;
use App\Models\Users;
use App\Models\UsersVatInvoicesInfo;
use App\Models\ValueCardRecord;
use App\Models\VirtualCard;
use App\Models\WarehouseAreaGoods;
use App\Models\WarehouseGoods;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\FileSystemsRepository;
use App\Repositories\Common\StrRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\Order\OrderReturnRepository;
use App\Services\Activity\BonusService;
use App\Services\Cart\CartCommonService;
use App\Services\Commission\CommissionManageService;
use App\Services\Commission\CommissionService;
use App\Services\Common\OfficeService;
use App\Services\CrossBorder\CrossBorderService;
use App\Services\CrowdFund\CrowdRefoundService;
use App\Services\Erp\JigonManageService;
use App\Services\ExpressManage\ExpressManageService;
use App\Services\Flow\FlowUserService;
use App\Services\Goods\GoodsAttrService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Goods\GoodsWarehouseService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Order\OrderCommonService;
use App\Services\Order\OrderDataHandleService;
use App\Services\Order\OrderDeliveryHandleService;
use App\Services\Order\OrderManageService;
use App\Services\Order\OrderRefoundService;
use App\Services\Order\OrderService;
use App\Services\Order\OrderTransportService;
use App\Services\Shipping\ShippingService;
use App\Services\Store\StoreCommonService;
use App\Services\Team\TeamService;
use App\Services\User\UserDataHandleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * 订单管理
 * Class OrderController
 * @package App\Modules\Admin\Controllers
 */
class OrderController extends InitController
{
    protected $orderService;
    protected $commonRepository;
    protected $commissionService;
    protected $goodsWarehouseService;
    protected $merchantCommonService;
    protected $dscRepository;
    protected $orderManageService;
    protected $orderRefoundService;
    protected $storeCommonService;
    protected $orderCommonService;
    protected $cartCommonService;
    protected $goodsAttrService;
    protected $flowUserService;
    protected $orderTransportService;
    protected $expressManageService;
    protected $shippingService;

    public function __construct(
        OrderService $orderService,
        CommonRepository $commonRepository,
        CommissionService $commissionService,
        GoodsWarehouseService $goodsWarehouseService,
        MerchantCommonService $merchantCommonService,
        DscRepository $dscRepository,
        OrderManageService $orderManageService,
        OrderRefoundService $orderRefoundService,
        StoreCommonService $storeCommonService,
        OrderCommonService $orderCommonService,
        CartCommonService $cartCommonService,
        GoodsAttrService $goodsAttrService,
        FlowUserService $flowUserService,
        OrderTransportService $orderTransportService,
        ExpressManageService $expressManageService,
        ShippingService $shippingService
    )
    {
        $this->orderService = $orderService;
        $this->commonRepository = $commonRepository;
        $this->commissionService = $commissionService;
        $this->goodsWarehouseService = $goodsWarehouseService;
        $this->merchantCommonService = $merchantCommonService;
        $this->dscRepository = $dscRepository;
        $this->orderManageService = $orderManageService;
        $this->orderRefoundService = $orderRefoundService;
        $this->storeCommonService = $storeCommonService;
        $this->orderCommonService = $orderCommonService;
        $this->cartCommonService = $cartCommonService;
        $this->goodsAttrService = $goodsAttrService;
        $this->flowUserService = $flowUserService;
        $this->orderTransportService = $orderTransportService;
        $this->expressManageService = $expressManageService;
        $this->shippingService = $shippingService;
    }

    public function index()
    {
        load_helper(['order', 'goods']);

        $order_back_apply = get_merchants_permissions($this->action_list, 'order_back_apply');
        $this->smarty->assign('order_back_apply', $order_back_apply); //退换货权限

        $order_os_remove = get_merchants_permissions($this->action_list, 'order_os_remove');
        $this->smarty->assign('order_os_remove', $order_os_remove); //订单删除

        $admin_id = get_admin_id();
        $adminru = get_admin_ru_id();

        if ($adminru['ru_id'] == 0) {
            $this->smarty->assign('priv_ru', 1);
        } else {
            $this->smarty->assign('priv_ru', 0);
        }

        $act = e(request()->input('act', ''));

        $this->smarty->assign('cfg', config('shop'));
        $this->smarty->assign('lang', array_merge($GLOBALS['_LANG'], trans('manage/order')));

        /*------------------------------------------------------ */
        //-- 订单查询
        /*------------------------------------------------------ */

        if ($act == 'order_query') {
            /* 检查权限 */
            admin_priv('order_view');

            $seller_list = (int)request()->input('seller_list', 0); //商家和自营订单标识
            $seller_list = "&seller_list=" . $seller_list;

            /* 载入配送方式 */
            $this->smarty->assign('shipping_list', shipping_list());

            /* 载入支付方式 */
            $this->smarty->assign('pay_list', payment_list());

            /* 载入国家 */
            $this->smarty->assign('country_list', get_regions());

            /* 载入订单状态、付款状态、发货状态 */
            $this->smarty->assign('os_list', $this->orderManageService->getStatusList('order'));
            $this->smarty->assign('ps_list', $this->orderManageService->getStatusList('payment'));
            $this->smarty->assign('ss_list', $this->orderManageService->getStatusList('shipping'));

            //ecmoban模板堂 --zhuo start
            /* 获得该管理员的权限 */
            $priv_str = AdminUser::where('user_id', session('admin_id'))->value('action_list');
            $priv_str = $priv_str ? $priv_str : '';

            /* 如果被编辑的管理员拥有了all这个权限，将不能编辑 */
            if ($priv_str == 'all') {
                $this->smarty->assign('priv_str', $priv_str);
            } else {
                $this->smarty->assign('priv_str', $priv_str);
            }
            //ecmoban模板堂 --zhuo end

            $this->smarty->assign('seller_list', $seller_list);

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            /* 模板赋值 */
            $this->smarty->assign('ur_here', lang('admin/common.03_order_query'));
            $this->smarty->assign('action_link', ['href' => 'order.php?act=list' . $seller_list, 'text' => lang('admin/common.02_order_list')]);

            /* 显示模板 */
            return $this->smarty->display('order_query.dwt');
        }

        /*------------------------------------------------------ */
        //-- 订单列表
        /*------------------------------------------------------ */

        elseif ($act == 'list') {
            /* 检查权限 */
            admin_priv('order_view');

            cache()->forget('order_download_content_' . $admin_id);

            $user_id = (int)request()->input('user_id', 0);
            $seller_list = (int)request()->input('seller_list', 0);
            $store_id = (int)request()->input('store_id', 0);
            if ($store_id) {
                $this->smarty->assign('from_store', true);
            }

            $this->smarty->assign('user_id', $user_id);

            //主订单选项
            $this->smarty->assign('order_type', 1);

            //区分自营和店铺
            self_seller(basename(request()->getRequestUri()));

            /* 模板赋值 */
            $this->smarty->assign('ur_here', lang('admin/common.02_order_list'));
            $this->smarty->assign('action_link', ['href' => 'order.php?act=order_query', 'text' => lang('admin/common.03_order_query')]);

            if ($user_id > 0) {
                $this->smarty->assign('action_link2', ['href' => 'users.php?act=list', 'text' => lang('admin/common.02_order_list')]);
            }

            $this->smarty->assign('action_link_export', ['href' => route('admin/order/export'), 'text' => lang('admin/common.11_order_export')]);

            // 导出待发货订单商品详单 Excel
            $this->smarty->assign('action_link_delivery', ['href' => 'javascript:download_order_delivery_list();', 'text' => lang('admin/order.order_delivery_download')]);
            $this->smarty->assign('action_link_delivery_batch', ['href' => route('admin/order_delivery_import'), 'text' => lang('admin/order.order_delivery_import')]);

            $this->smarty->assign('status_list', trans('admin/order.cs'));   // 订单状态

            $this->smarty->assign('os_unconfirmed', OS_UNCONFIRMED);
            $this->smarty->assign('cs_await_pay', CS_AWAIT_PAY);
            $this->smarty->assign('cs_await_ship', CS_AWAIT_SHIP);
            $this->smarty->assign('full_page', 1);

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            $is_zc = (int)request()->input('is_zc', 0);
            $this->smarty->assign('is_zc', $is_zc);

            $serch_type = request()->input('serch_type', -1);
            $this->smarty->assign('serch_type', $serch_type);

            $order_list = $this->orderManageService->orderList(0, $adminru);

            $this->smarty->assign('order_list', $order_list['orders']);
            $this->smarty->assign('filter', $order_list['filter']);
            $this->smarty->assign('record_count', $order_list['record_count']);
            $this->smarty->assign('page_count', $order_list['page_count']);
            $this->smarty->assign('sort_order_time', '<img src="' . __TPL__ . '/images/sort_desc.gif">');
            $this->smarty->assign('express_list', $this->expressManageService->getExpressCompany(1));

            /* 订单内平台、店铺区分 */
            $this->smarty->assign('common_tabs', ['info' => $seller_list, 'url' => 'order.php?act=list']);

            /* 显示模板 */
            return $this->smarty->display('order_list.dwt');

        } elseif ($act == 'return_list') {
            /**
             * 退换货订单
             */
            /* 检查权限 */
            admin_priv('order_back_apply');

            $seller_order = (int)request()->input('seller_list', 0);
            $seller_order = !empty($seller_order) ? 1 : 0;  //商家和自营订单标识

            /* 模板赋值 */
            $this->smarty->assign('ur_here', lang('admin/common.12_back_apply'));
            $this->smarty->assign('action_link', ['href' => 'order.php?act=order_query', 'text' => lang('admin/common.03_order_query')]);

            $this->smarty->assign('full_page', 1);
            $order_list = return_order_list();
            $this->smarty->assign('order_list', $order_list['orders']);
            $this->smarty->assign('filter', $order_list['filter']);
            $this->smarty->assign('record_count', $order_list['record_count']);
            $this->smarty->assign('page_count', $order_list['page_count']);

            /* 订单内平台、店铺区分 */
            $this->smarty->assign('common_tabs', ['info' => $seller_order, 'url' => 'order.php?act=return_list']);

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            return $this->smarty->display('return_list.dwt');

        } elseif ($act == 'return_list_query') {
            /**
             * 退换货分页
             */
            /* 检查权限 */
            admin_priv('order_back_apply');

            /* 模板赋值 */
            $order_list = return_order_list();
            $this->smarty->assign('order_list', $order_list['orders']);
            $this->smarty->assign('filter', $order_list['filter']);
            $this->smarty->assign('record_count', $order_list['record_count']);
            $this->smarty->assign('page_count', $order_list['page_count']);

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            return make_json_result($this->smarty->fetch('return_list.dwt'), '', ['filter' => $order_list['filter'], 'page_count' => $order_list['page_count']]);
        }

        /* ------------------------------------------------------ */
        //--Excel文件下载数组处理
        /* ------------------------------------------------------ */
        elseif ($act == 'ajax_download') {
            $result = ['is_stop' => 0];

            $page = (int)request()->input('page_down', 0); //处理的页数
            $page_count = (int)request()->input('page_count', 0); //总页数

            $order_list = $this->orderManageService->orderList($page, $adminru);//获取订单数组
            if (isset($order_list['orders']) && !empty($order_list['orders'])) {
                foreach ($order_list['orders'] as $key => $value) {
                    $value['order_return_count'] = OrderReturn::where('order_id', $value['order_id'])
                        ->where('refound_status', FF_REFOUND)
                        ->count();
                    $order_list['orders'][$key] = $value;
                }
            }

            $merchants_download_content = cache("order_download_content_" . $page . "_" . $admin_id);
            $merchants_download_content = !is_null($merchants_download_content) ? $merchants_download_content : [];

            $merchants_download_content = $order_list;

            cache()->forever("order_download_content_" . $page . "_" . $admin_id, $merchants_download_content);

            $result['page'] = $page;
            $result['page_count'] = $page_count;
            if ($page < $page_count) {
                $result['is_stop'] = 1;//未结算标识
                $result['next_page'] = $page + 1;
            }
            return response()->json($result);
        }
        /* ------------------------------------------------------ */
        //--导出当前分页订单csv文件
        /* ------------------------------------------------------ */
        elseif ($act == 'download_list_csv') {

            $page = (int)request()->input('page_down', 0); //处理的页数
            $page_count = (int)request()->input('page_count', 0); //总页数

            // 获取所有商家的下载数据 按照商家分组
            $order_list = cache("order_download_content_" . $page . "_" . $admin_id);
            $order_list = !is_null($order_list) ? $order_list : [];

            if (!empty($order_list)) {
                // 需要导出字段名称
                $head = [
                    ['column_name' => lang('admin/common.download.order_sn')],
                    ['column_name' => lang('admin/common.download.seller_name')],
                    ['column_name' => lang('admin/common.download.order_user')],
                    ['column_name' => lang('admin/common.download.order_time')],
                    ['column_name' => lang('admin/common.download.consignee')],
                    ['column_name' => lang('admin/common.download.tel')],
                    ['column_name' => lang('admin/common.download.address')],
                    ['column_name' => lang('admin/common.download.goods_info')],
                    ['column_name' => lang('admin/common.download.goods_sn')],
                    ['column_name' => lang('admin/common.download.goods_price'), 'num' => 1],
                    ['column_name' => lang('admin/common.download.goods_number'), 'num' => 1],
                    ['column_name' => lang('admin/common.download.cost_price'), 'num' => 1],
                    ['column_name' => lang('admin/common.download.goods_amount'), 'num' => 1],
                    ['column_name' => lang('admin/common.download.tax'), 'num' => 1],
                    ['column_name' => lang('admin/common.download.shipping_fee'), 'num' => 1],
                    ['column_name' => lang('admin/common.download.insure_fee'), 'num' => 1],
                    ['column_name' => lang('admin/common.download.pay_fee'), 'num' => 1],
                    ['column_name' => lang('admin/common.download.rate_fee'), 'num' => 1],
                    ['column_name' => lang('admin/common.download.total_fee'), 'num' => 1],
                    ['column_name' => lang('admin/common.download.discount'), 'num' => 1],
                    ['column_name' => lang('admin/common.download.total_fee_order'), 'num' => 1],
                    ['column_name' => lang('admin/common.download.surplus'), 'num' => 1],
                    ['column_name' => lang('admin/common.download.integral_money'), 'num' => 1],
                    ['column_name' => lang('admin/common.download.bonus'), 'num' => 1],
                    ['column_name' => lang('admin/common.download.coupons'), 'num' => 1],
                    ['column_name' => lang('admin/common.download.value_card'), 'num' => 1],
                    ['column_name' => lang('admin/common.download.money_paid'), 'num' => 1],
                    ['column_name' => lang('admin/common.download.order_amount'), 'num' => 1],
                    ['column_name' => lang('admin/common.download.order_status')],
                    ['column_name' => lang('admin/common.download.pay_status')],
                    ['column_name' => lang('admin/common.download.shipping_status')],
                    ['column_name' => lang('admin/common.download.froms')],
                    ['column_name' => lang('admin/common.download.pay_name')],
                    ['column_name' => lang('admin/common.download.postscript')],
                ];

                if (file_exists(MOBILE_DRP)) {
                    $head[]['column_name'] = lang('admin/common.download.drp_money');
                }

                if (CROSS_BORDER === true) {
                    // 跨境多商户
                    $head[]['column_name'] = lang('admin/common.download.rel_name');
                    $head[]['column_name'] = lang('admin/common.download.id_num');
                }

                // 需要导出字段 须和查询数据里的字段名保持一致
                $fields = [
                    'order_sn',
                    'seller_name',
                    'order_user',
                    'order_time',
                    'consignee',
                    'tel',
                    'address',
                    'goods_info',
                    'goods_sn',
                    'goods_price',
                    'goods_number',
                    'cost_price',
                    'goods_amount',
                    'tax',
                    'shipping_fee',
                    'insure_fee',
                    'pay_fee',
                    'rate_fee',
                    'total_fee',
                    'discount',
                    'total_fee_order',
                    'surplus',
                    'integral_money',
                    'bonus',
                    'coupons',
                    'value_card',
                    'money_paid',
                    'order_amount',
                    'order_status',
                    'pay_status',
                    'shipping_status',
                    'froms',
                    'pay_name',
                    'postscript'
                ];

                if (file_exists(MOBILE_DRP)) {
                    $fields[] = 'drp_money';
                }

                if (CROSS_BORDER === true) {
                    // 跨境多商户
                    $fields[] = 'rel_name';
                    $fields[] = 'id_num';
                }

                $list = $this->orderManageService->downloadOrderListContent($order_list['orders']);

                // 文件名
                $title = date('YmdHis');

                $spreadsheet = new OfficeService();

                // 文件下载目录
                $dir = 'data/attached/file/';
                $file_path = storage_public($dir);
                if (!is_dir($file_path)) {
                    Storage::disk('public')->makeDirectory($dir);
                }

                $options = [
                    'savePath' => $file_path, // 指定文件下载目录
                    'except_merge_column' => [
                        lang('admin/common.download.goods_info'),
                        lang('admin/common.download.goods_sn'),
                        lang('admin/common.download.goods_price'),
                        lang('admin/common.download.goods_number'),
                        lang('admin/common.download.cost_price'),
                        lang('admin/common.download.drp_money')
                    ], // 不进行单元格合并的字段 对应 column_name
                ];

                // 默认样式
                $spreadsheet->setDefaultStyle();

                // 文件名按分页命名
                $out_title = $title . '-' . $page;

                if ($list) {
                    $spreadsheet->exportExcel($out_title, $head, $fields, $list, $options);
                }
                // 关闭
                $spreadsheet->disconnect();
            }
            /* 清除缓存 */
            cache()->forget('order_download_content_' . $page . "_" . $admin_id);

            if ($page < $page_count) {
                $result['is_stop'] = 1;//未结算标识
            } else {
                $result['is_stop'] = 0;
            }
            $result['error'] = 1;
            $result['page'] = $page;

            return response()->json($result);
        }
        /* ------------------------------------------------------ */
        //--Excel文件下载 订单下载压缩文件
        /* ------------------------------------------------------ */
        elseif ($act == 'order_download') {

            // 文件下载目录
            $dir = 'data/attached/file/';
            $zip_name = lang('admin/common.order_export_alt') . date('YmdHis') . ".zip";

            $zip_file = FileSystemsRepository::download_zip($dir, $zip_name);

            if ($zip_file) {
                return response()->download($zip_file)->deleteFileAfterSend(); // 下载完成删除zip压缩包
            }

            return back()->withInput(); // 返回
        }

        /*------------------------------------------------------ */
        //-- 排序、分页、查询
        /*------------------------------------------------------ */
        elseif ($act == 'query') {
            /* 检查权限 */
            admin_priv('order_view');

            $seller_order = (int)request()->input('seller_list', 0);
            $seller_order = !empty($seller_order) ? 1 : 0;  //商家和自营订单标识

            $serch_type = request()->input('serch_type', -1);
            $this->smarty->assign('serch_type', $serch_type);
            $this->smarty->assign('seller_order', $seller_order);
            $this->smarty->assign('action_link', ['href' => 'order.php?act=order_query', 'text' => lang('admin/common.03_order_query')]);

            $this->smarty->assign('action_link_export', ['href' => route('admin/order/export'), 'text' => lang('admin/common.11_order_export')]);

            // 导出待发货订单商品详单 Excel
            $this->smarty->assign('action_link_delivery', ['href' => 'javascript:download_order_delivery_list();', 'text' => lang('admin/order.order_delivery_download')]);
            $this->smarty->assign('action_link_delivery_batch', ['href' => route('admin/order_delivery_import'), 'text' => lang('admin/order.order_delivery_import')]);

            $order_list = $this->orderManageService->orderList(0, $adminru);

            $this->smarty->assign('order_list', $order_list['orders']);
            $this->smarty->assign('filter', $order_list['filter']);
            $this->smarty->assign('record_count', $order_list['record_count']);
            $this->smarty->assign('page_count', $order_list['page_count']);
            $sort_flag = sort_flag($order_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);
            $this->smarty->assign('express_list', $this->expressManageService->getExpressCompany(1));

            return make_json_result($this->smarty->fetch('order_list.dwt'), '', ['filter' => $order_list['filter'], 'page_count' => $order_list['page_count']]);
        }

        /*------------------------------------------------------ */
        //-- 订单详情页面
        /*------------------------------------------------------ */

        elseif ($act == 'info') {

            /* 根据订单id或订单号查询订单信息 */
            $order_id = request()->input('order_id');
            $order_sn = request()->input('order_sn');

            if (isset($order_id)) {
                $order_id = intval($order_id);
                $order = order_info($order_id);
            } elseif (isset($order_sn)) {
                $order_sn = trim($order_sn);
                $order = order_info(0, $order_sn);
            } else {
                /* 如果参数不存在，退出 */
                return 'invalid parameter';
            }

            /* 如果订单不存在，退出 */
            if (empty($order)) {
                return 'order does not exist';
            }

            /* 根据订单是否完成检查权限 */
            if (order_finished($order)) {
                admin_priv('order_view_finished');
            } else {
                admin_priv('order_view');
            }

            $payment = payment_info($order['pay_id']);

            /* 未支付 查询更新支付状态 start */
            if (in_array($order['order_status'], [OS_CONFIRMED, OS_SPLITED]) && $order['pay_status'] == PS_UNPAYED) {
                if ($payment && strpos($payment['pay_code'], 'pay_') === false && $payment['is_online'] == 1) {
                    /* 取得在线支付方式的支付按钮 */
                    $payObj = CommonRepository::paymentInstance($payment['pay_code']);
                    if (!is_null($payObj) && is_callable([$payObj, 'orderQuery'])) {
                        $log_id = PayLog::where('order_id', $order['order_id'])->where('order_type', PAY_ORDER)->value('log_id');
                        /* 判断类对象方法是否存在 */
                        $order_other = [
                            'order_sn' => $order['order_sn'],
                            'log_id' => $log_id ?? 0,
                            'order_amount' => $order['order_amount'],
                        ];
                        $respond = $payObj->orderQuery($order_other);
                        if ($respond) {
                            $res = OrderInfo::where('order_id', $order['order_id'])->select('order_status', 'shipping_status', 'pay_status', 'pay_time');
                            $order_info = BaseRepository::getToArrayFirst($res);
                            if ($order_info) {
                                $order['order_status'] = $order_info['order_status'];
                                $order['shipping_status'] = $order_info['shipping_status'];
                                $order['pay_status'] = $order_info['pay_status'];
                                $order['pay_time'] = $order_info['pay_time'];
                            }
                        }
                    }
                }
            }
            /* 未支付 查询更新支付状态 end */

            /* 订单拼团信息 */
            if ($order['team_id'] > 0) {
                $teamInfo = $this->orderManageService->teamOrderUserNum($order['team_id']);
                $order['is_team'] = $teamInfo['is_team'];

                if ($order['is_team'] == 1) {
                    $team_content = sprintf($GLOBALS['_LANG']['order_team_ok'], $teamInfo['team_order_user_num']);
                } else {
                    $team_content = sprintf($GLOBALS['_LANG']['order_team_false'], $teamInfo['team_order_user_num'], $teamInfo['team_false_user_num']);
                    // 拼团失败
                    if ($order['pay_status'] == PS_PAYED && $order['main_count'] == 0) {
                        app(TeamService::class)->checkRefund($order['team_id']);
                    }
                }

                $order['team_content'] = $team_content;
            } else {
                /* 默认 */
                $order['is_team'] = 1;
            }

            $commented = Comment::where('order_id', $order['order_id'])->count();
            $order['is_commented'] = !empty($commented) ? 1 : 0;

            //获取支付方式code
            $pay_code = $payment['pay_code'] ?? '';

            if ($pay_code == "cod" || $pay_code == "bank" || $pay_code == 'balance') {
                $this->smarty->assign('pay_code', 1);
            } else {
                $this->smarty->assign('pay_code', 0);
            }

            $order['delivery_id'] = DeliveryOrder::where('order_sn', $order['order_sn'])->value('delivery_id');
            $order['delivery_id'] = $order['delivery_id'] ? $order['delivery_id'] : 0;

            if ($order['is_zc_order'] > 0) {
                // 众筹信息 by wu start
                $zc_goods_info = get_zc_goods_info($order_id);
                $this->smarty->assign('zc_goods_info', $zc_goods_info);
                // 众筹失败且已支付订单 原路退款
                if (in_array($order['order_status'], [OS_CONFIRMED, OS_SPLITED]) && $order['pay_status'] == PS_PAYED && $order['main_count'] == 0) {
                    $zc_project = $zc_goods_info['get_zc_project'] ?? [];
                    CrowdRefoundService::zcRefund($order, $zc_project);
                }
            }

            /* 处理确认收货时间 start */
            if (config('shop.open_delivery_time') == 1) {

                /* 查询订单信息，检查状态 */
                $res = OrderInfo::where('order_id', $order['order_id']);

                $res = $res->with([
                    'getSellerNegativeOrder'
                ]);

                $orderInfo = BaseRepository::getToArrayFirst($res);

                $orderDelivery = CommonRepository::orderDeliveryCondition($orderInfo['order_status'], $orderInfo['shipping_status'], $orderInfo['pay_status']);

                //发货状态
                if ($orderDelivery) {

                    $auto_delivery_time = config('shop.auto_delivery_time') ?? 0;
                    $auto_delivery_time = $auto_delivery_time > 0 && $auto_delivery_time > $orderInfo['auto_delivery_time'] ? $auto_delivery_time : $orderInfo['auto_delivery_time'];
                    $auto_delivery_time = $auto_delivery_time * 24 * 3600;

                    $delivery_time = $orderInfo['shipping_time'] + $auto_delivery_time;

                    $confirm_take_time = TimeRepository::getGmTime();

                    if ($confirm_take_time >= $delivery_time) { //自动确认收货操作

                        // 订单是否全部发货 全部发货 => 全部收货、部分发货 => 部分收货
                        $order_finish = OrderRepository::getAllDeliveryFinish($order['order_id']);
                        $shipping_status = ($order_finish == 1) ? SS_RECEIVED : SS_PART_RECEIVED;

                        $data = [
                            'order_status' => $orderInfo['order_status'],
                            'shipping_status' => $shipping_status,
                            'pay_status' => $orderInfo['pay_status'],
                            'confirm_take_time' => $confirm_take_time
                        ];
                        $upOrder = OrderInfo::where('order_id', $order['order_id'])->update($data);

                        if ($upOrder > 0) {
                            if ($shipping_status == SS_RECEIVED) {
                                $order_nogoods = UserOrderNum::where('user_id', $orderInfo['user_id'])->value('order_nogoods');
                                $order_nogoods = $order_nogoods ? $order_nogoods : 0;

                                /* 更新会员订单信息 */
                                $dbRaw = [
                                    'order_isfinished' => "order_isfinished + 1"
                                ];

                                if ($order_nogoods > 0) {
                                    $dbRaw['order_nogoods'] = "order_nogoods - 1";
                                }

                                $dbRaw = BaseRepository::getDbRaw($dbRaw);
                                UserOrderNum::where('user_id', $orderInfo['user_id'])->update($dbRaw);
                            }

                            // 订单收货事件监听
                            $extendParam = [
                                'shipping_status' => $shipping_status,
                                'note' => lang('admin/order.self_motion_goods'),
                                'action_name' => lang('admin/common.system_handle')
                            ];
                            event(new \App\Events\OrderReceiveEvent($orderInfo));
                        }
                    }
                } else {
                    if (empty($order['confirm_take_time'])) {
                        $bill_order_count = SellerBillOrder::where('order_id', $order['order_id'])->count();

                        if ($bill_order_count > 0 && $order['shipping_status'] == SS_RECEIVED) {
                            $confirm_take_time = OrderAction::where('order_id', $order['order_id'])
                                ->where('shipping_status', SS_RECEIVED)
                                ->max('log_time');

                            if (empty($confirm_take_time)) {
                                $confirm_take_time = TimeRepository::getGmTime();

                                $note = lang('admin/order.admin_order_list_motion');
                                order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], $note, lang('admin/common.system_handle'), 0, $confirm_take_time);

                                $order['confirm_take_time'] = $confirm_take_time;
                            }

                            $log_other = array(
                                'confirm_take_time' => $confirm_take_time
                            );

                            OrderInfo::where('order_id', $order['order_id'])->update($log_other);
                            SellerBillOrder::where('order_id', $order['order_id'])->update($log_other);
                        }
                    }
                }
            }
            /* 处理确认收货时间 end */

            /* 对发货号处理 */
            if (!empty($order['invoice_no'])) {
                $shipping_code = Shipping::where('shipping_id', $order['shipping_id'])->value('shipping_code');

                $shippingObject = CommonRepository::shippingInstance($shipping_code);
                if (!is_null($shippingObject)) {
                    $order['shipping_code_name'] = $shippingObject->get_code_name();
                }
            }

            /* 如果管理员属于某个办事处，检查该订单是否也属于这个办事处 */
            $agency_id = AdminUser::where('user_id', session('admin_id'))->value('agency_id');
            $agency_id = $agency_id ? $agency_id : 0;
            if ($agency_id > 0 && !empty($order['agency_id']) && $order['agency_id'] != $agency_id) {
                return sys_msg(lang('admin/common.priv_error'), 1);
            }

            /* 取得上一个、下一个订单号 */
            $max_res = OrderInfo::where('order_id', '<', $order['order_id']);
            $min_res = OrderInfo::where('order_id', '>', $order['order_id']);

            $lastfilter = request()->cookie('lastfilter');
            if (isset($lastfilter) && !empty($lastfilter)) {
                $filter = unserialize(urldecode($lastfilter));
                if (!empty($filter['composite_status'])) {

                    //综合状态
                    switch ($filter['composite_status']) {
                        case CS_AWAIT_PAY:
                            $await_pay = preg_replace('/ AND/', '', $this->orderService->orderQuerySql('await_pay'), 1);
                            $max_res = $max_res->whereRaw($await_pay);
                            $min_res = $min_res->whereRaw($await_pay);
                            break;

                        case CS_AWAIT_SHIP:
                            $await_pay = preg_replace('/ AND/', '', $this->orderService->orderQuerySql('await_ship'), 1);
                            $max_res = $max_res->whereRaw($await_pay);
                            $min_res = $min_res->whereRaw($await_pay);
                            break;

                        case CS_FINISHED:
                            $await_pay = preg_replace('/ AND/', '', $this->orderService->orderQuerySql('finished'), 1);
                            $max_res = $max_res->whereRaw($await_pay);
                            $min_res = $min_res->whereRaw($await_pay);
                            break;

                        default:
                            if ($filter['composite_status'] != -1) {
                                $max_res = $max_res->where('order_status', $filter['composite_status']);
                                $min_res = $min_res->where('order_status', $filter['composite_status']);
                            }
                    }
                }
            }

            if ($agency_id > 0) {
                $max_res = $max_res->where('agency_id', $agency_id);
                $min_res = $min_res->where('agency_id', $agency_id);
            }

            $this->smarty->assign('prev_id', $max_res->max('order_id'));
            $this->smarty->assign('next_id', $min_res->min('order_id'));

            /* 取得用户名 */
            if ($order['user_id'] > 0) {
                $user_info = Users::where('user_id', $order['user_id']);
                $user_info = BaseRepository::getToArrayFirst($user_info);

                if (!empty($user_info)) {
                    $order['user_name'] = $user_info['user_name'];

                    if (config('shop.show_mobile', 0) == 0) {
                        $order['user_name'] = $this->dscRepository->stringToStar($order['user_name']);
                    }
                }
            }

            /* 取得所有办事处 */
            $res = Agency::query()->select('agency_id', 'agency_name');
            $agency_list = BaseRepository::getToArrayGet($res);
            $this->smarty->assign('agency_list', $agency_list);

            /* 格式化金额 */
            if ($order['order_amount'] < 0) {
                $order['money_refund'] = abs($order['order_amount']);
                $order['formated_money_refund'] = $this->dscRepository->getPriceFormat(abs($order['order_amount']));
            }

            /* 其他处理 */
            $order['order_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $order['add_time']);
            $order['pay_time'] = $order['pay_time'] > 0 ? TimeRepository::getLocalDate(config('shop.time_format'), $order['pay_time']) : $GLOBALS['_LANG']['ps'][PS_UNPAYED];
            $order['shipping_time'] = $order['shipping_time'] > 0 ? TimeRepository::getLocalDate(config('shop.time_format'), $order['shipping_time']) : $GLOBALS['_LANG']['ss'][SS_UNSHIPPED];
            $order['confirm_take_time'] = $order['confirm_take_time'] > 0 ? TimeRepository::getLocalDate(config('shop.time_format'), $order['confirm_take_time']) : $GLOBALS['_LANG']['ss'][SS_UNSHIPPED];
            $order['status'] = $GLOBALS['_LANG']['os'][$order['order_status']] . ',' . $GLOBALS['_LANG']['ps'][$order['pay_status']] . ',' . $GLOBALS['_LANG']['ss'][$order['shipping_status']];

            /* 取得订单的来源 */
            if ($order['from_ad'] == 0) {
                $order['referer'] = empty($order['referer']) ? $GLOBALS['_LANG']['from_self_site'] : $order['referer'];
            } elseif ($order['from_ad'] == -1) {
                $order['referer'] = $GLOBALS['_LANG']['from_goods_js'] . ' (' . $GLOBALS['_LANG']['from'] . $order['referer'] . ')';
            } else {
                /* 查询广告的名称 */
                $ad_name = Ad::where('ad_id', $order['from_ad'])->value('ad_name');
                $ad_name = $ad_name ? $ad_name : '';
                $order['referer'] = $GLOBALS['_LANG']['from_ad_js'] . $ad_name . ' (' . $GLOBALS['_LANG']['from'] . $order['referer'] . ')';
            }

            /* 此订单的发货备注(此订单的最后一条操作记录) */
            $order['invoice_note'] = OrderAction::where('order_id', $order['order_id'])
                ->where('shipping_status', SS_SHIPPED)
                ->orderBy('log_time', 'DESC')
                ->value('action_note');
            $order['invoice_note'] = $order['invoice_note'] ? $order['invoice_note'] : '';

            /* 自提点信息 */
            $shipping_code = Shipping::where('shipping_id', $order['shipping_id'])->value('shipping_code');
            $shipping_code = $shipping_code ? $shipping_code : '';
            if ($shipping_code == 'cac') {
                $g_order_id = $order['order_id'];
                $res = ShippingPoint::whereHasIn('getOrderInfo', function ($query) use ($g_order_id) {
                    $query->where('order_id', $g_order_id);
                });
                $order['point'] = BaseRepository::getToArrayFirst($res);
            }

            /* 判断当前订单是否是白条分期付订单 bylu */
            $res = BaitiaoLog::where('order_id', $order_id)->select('stages_total', 'stages_one_price', 'is_stages');
            $baitiao_info = BaseRepository::getToArrayFirst($res);
            if (!empty($baitiao_info) && $baitiao_info['is_stages'] == 1) {
                $order['is_stages'] = 1;
                $order['stages_total'] = $baitiao_info['stages_total'];
                $order['stages_one_price'] = $baitiao_info['stages_one_price'];
            }

            /*增值发票 start*/
            if ($order['invoice_type'] == 1) {
                $user_id = $order['user_id'];
                $res = UsersVatInvoicesInfo::where('user_id', $user_id);
                $res = BaseRepository::getToArrayFirst($res);
                $region = ['province' => $res['province'], 'city' => $res['city'], 'district' => $res['district']];
                $res['region'] = get_area_region_info($region);
                $this->smarty->assign('vat_info', $res);
            }
            /*增值发票 end*/

            /* 取得订单商品总重量 */
            $weight_price = order_weight_price($order['order_id']);
            $order['total_weight'] = $weight_price['formated_weight'];

            $date = ['order_id'];

            $order_child = count(get_table_date('order_info', "main_order_id='" . $order['order_id'] . "'", $date, 1));
            $order['order_child'] = $order_child;

            //获取免邮券 start
            $cou_id = CouponsUser::where('is_delete', 0)->where('user_id', $order['user_id'])
                ->where('uc_id', $order['uc_id'])
                ->value('cou_id');
            $cou_id = $cou_id ? $cou_id : 0;

            $cou_type = Coupons::where('cou_id', $cou_id)->value('cou_type');
            $cou_type = $cou_type ? $cou_type : 0;
            $this->smarty->assign('cou_type', $cou_type);
            //获取免邮券 end

            /* 取得用户信息 */
            if ($order['user_id'] > 0 && !empty($user_info)) {

                /*用户发票地址信息*/
                $vat_invoices_info = UsersVatInvoicesInfo::where('user_id', $user_info['user_id'])->first();
                $vat_invoices_info = $vat_invoices_info ? $vat_invoices_info->toArray() : [];
                $this->smarty->assign('vat_invoices_info', $vat_invoices_info);

                /* 用户等级 */
                $res = UserRank::whereRaw(1);
                if ($user_info['user_rank'] > 0) {
                    $res = $res->where('rank_id', $user_info['user_rank']);
                } else {
                    $res = $res->where('min_points', '<=', intval($user_info['rank_points']))->orderBy('min_points', 'DESC');
                }

                $user_info['rank_name'] = $res->value('rank_name');
                $user_info['rank_name'] = $user_info['rank_name'] ? $user_info['rank_name'] : '';

                // 用户红包数量
                $day = getdate();
                $today = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
                $o_user_id = $order['user_id'];
                $user_info['bonus_count'] = BonusType::where('use_start_date', '<=', $today)
                    ->where('use_end_date', '>=', $today)
                    ->whereHasIn('getUserBonus', function ($query) use ($o_user_id) {
                        $query->where('order_id', 0)->where('user_id', $o_user_id);
                    })
                    ->count();

                $user_info['formated_user_money'] = $this->dscRepository->getPriceFormat($user_info['user_money']);

                $this->smarty->assign('user', $user_info);

                // 地址信息
                $res = UserAddress::where('user_id', $order['user_id'])
                    ->where('address_id', $user_info['address_id']);
                $address_list = BaseRepository::getToArrayGet($res);

                if ($address_list) {
                    foreach ($address_list as $key => $row) {
                        if (config('shop.show_mobile', 0) == 0) {
                            $address_list[$key]['mobile'] = $this->dscRepository->stringToStar($row['mobile']);
                        }
                    }
                }

                $this->smarty->assign('address_list', $address_list);
            }

            /* 获取订单门店信息 */
            $res = StoreOrder::where('order_id', $order['order_id'])->select('store_id', 'pick_code', 'take_time');
            $stores = BaseRepository::getToArrayFirst($res);
            $order['store_id'] = $stores['store_id'] ?? 0;

            $offline_store = [];
            if ($order['store_id'] > 0) {
                $res = OfflineStore::where('id', $order['store_id']);
                $res = $res->with([
                    'getRegionProvince',
                    'getRegionCity',
                    'getRegionDistrict'
                ]);
                $offline_store = BaseRepository::getToArrayFirst($res);

                if (!empty($offline_store)) {
                    $offline_store['province'] = $offline_store['get_region_province']['region_name'] ?? '';
                    $offline_store['city'] = $offline_store['get_region_city']['region_name'] ?? '';
                    $offline_store['district'] = $offline_store['get_region_district']['region_name'] ?? '';
                }

                $offline_store['pick_code'] = $stores['pick_code'] ?? '';
                $offline_store['take_time'] = $stores['take_time'] ?? '';
                $offline_store['stores_img'] = $this->dscRepository->getImagePath($offline_store['stores_img']);
            }

            $this->smarty->assign('offline_store', $offline_store);

            /* 已支付未发货 返回门店抢单列表 */
            if ($order['pay_status'] == PS_PAYED && $order['shipping_status'] == SS_UNSHIPPED) {
                $have_store_order = StoreOrder::where('order_id', $order['order_id'])
                    ->where('store_id', '>', 0)
                    ->count();

                if ($have_store_order == 0) {
                    $this->smarty->assign('can_set_grab_order', 1);
                }
            }

            /* 取得订单商品及货品 */
            $goods_list = [];
            $goods_attr = [];

            $res = OrderGoods::where('order_id', $order['order_id']);
            $res = $res->with(['getProducts', 'getOrder', 'getGoods' => function ($query) {
                $query->with(['getGoodsCategory']);
            }]);

            $res = BaseRepository::getToArrayGet($res);

            $is_cloud_order = 0;

            if ($res) {

                $orderGoodsAttrIdList = BaseRepository::getKeyPluck($res, 'goods_attr_id');
                $orderGoodsAttrIdList = BaseRepository::getArrayUnique($orderGoodsAttrIdList);
                $orderGoodsAttrIdList = ArrRepository::getArrayUnset($orderGoodsAttrIdList);

                $productsGoodsAttrList = [];
                if ($orderGoodsAttrIdList) {
                    $orderGoodsAttrIdList = BaseRepository::getImplode($orderGoodsAttrIdList);
                    $productsGoodsAttrList = GoodsDataHandleService::getGoodsAttrDataList($orderGoodsAttrIdList, ['goods_attr_id', 'attr_img_flie', 'attr_gallery_flie']);
                }

                $delivery_order_id = BaseRepository::getKeyPluck($res, 'order_id');
                $orderDeliveryList = OrderDeliveryHandleService::getDeliveryOrderByOrderIdDataList($delivery_order_id, ['delivery_id', 'goods_id', 'product_id']);

                foreach ($res as $row) {
                    $row['product_sn'] = '';
                    if (isset($row['get_products']) && !empty($row['get_products'])) {
                        $row['product_sn'] = $row['get_products']['product_sn'];
                    }

                    $row['model_inventory'] = '';
                    $row['model_attr'] = '';
                    $row['suppliers_id'] = '';
                    $row['storage'] = '';
                    $row['give_integral'] = '';
                    $row['ru_id'] = '';
                    $row['brand_id'] = '';
                    $row['goods_thumb'] = '';
                    $row['bar_code'] = '';

                    $row['measure_unit'] = '';

                    if (isset($row['get_goods']) && !empty($row['get_goods'])) {
                        $row['model_inventory'] = $row['get_goods']['model_inventory'];
                        $row['model_attr'] = $row['get_goods']['model_attr'];
                        $row['suppliers_id'] = $row['get_goods']['suppliers_id'];
                        $row['storage'] = $row['get_goods']['goods_number'];
                        $row['give_integral'] = $row['get_goods']['give_integral'];
                        $row['ru_id'] = $row['get_goods']['user_id'];
                        $row['brand_id'] = $row['get_goods']['brand_id'];
                        $row['goods_thumb'] = $row['get_goods']['goods_thumb'];
                        $row['bar_code'] = $row['get_goods']['bar_code'];

                        if (isset($row['get_goods']['get_goods_category']) && !empty($row['get_goods']['get_goods_category'])) {
                            $row['measure_unit'] = $row['get_goods']['get_goods_category']['measure_unit'];
                        }
                    }

                    if ($row['give_integral'] == -1) {
                        $integral_order = [
                            'extension_code' => $row['extension_code'] ?? '',
                            'extension_id' => $row['extension_id'] ?? 0,
                            'order_id' => $row['order_id']
                        ];
                        $integral = integral_to_give($integral_order, $row['rec_id']);
                        $row['give_integral'] = intval($integral['custom_points']);
                    }

                    $row["IF(oi.extension_code != '', oi.extension_code, o.extension_code)"] = '';
                    $row['extension_id'] = '';

                    if (isset($row['get_order']) && !empty($row['get_order'])) {
                        $row['extension_id'] = $row['get_order']['extension_id'];
                        if (!empty($row['get_order']['extension_code'])) {
                            $row["IF(oi.extension_code != '', oi.extension_code, o.extension_code)"] = $row['get_order']['extension_code'];
                        } else {
                            $row["IF(oi.extension_code != '', oi.extension_code, o.extension_code)"] = $row['extension_code'];
                        }
                    }

                    /* 虚拟商品支持 */
                    if ($row['is_real'] == 0) {
                        /* 取得语言项 */
                        $filename = app_path('Plugins/' . $order['extension_code'] . '/Languages/common_' . config('shop.lang') . '.php');
                        if (file_exists($filename)) {
                            include_once($filename);
                            if (!empty($GLOBALS['_LANG'][$order['extension_code'] . '_link'])) {
                                $row['goods_name'] = $row['goods_name'] . sprintf($GLOBALS['_LANG'][$order['extension_code'] . '_link'], $row['goods_id'], $order['order_sn']);
                            }
                        }
                    }

                    if ($row['model_inventory'] == 1) {
                        $row['storage'] = get_warehouse_area_goods($row['warehouse_id'], $row['goods_id'], 'warehouse_goods');
                    } elseif ($row['model_inventory'] == 2) {
                        $row['storage'] = get_warehouse_area_goods($row['area_id'], $row['goods_id'], 'warehouse_area_goods');
                    }

                    $row['discount_amount'] = $this->dscRepository->getPriceFormat($row['dis_amount'], false); //单品阶梯满减金额

                    $row['goods_amount'] = $row['goods_price'] * $row['goods_number'];
                    $row['amount'] = $row['goods_amount'] - $row['dis_amount'];

                    //ecmoban模板堂 --zhuo start //库存查询
                    $products = $this->goodsWarehouseService->getWarehouseAttrNumber($row['goods_id'], $row['goods_attr_id'], $row['warehouse_id'], $row['area_id'], $row['area_city'], $row['model_attr']);
                    $row['goods_storage'] = isset($products['product_number']) ? $products['product_number'] : 0;

                    if ($row['product_id']) {
                        $row['bar_code'] = $products['bar_code'] ?? '';
                    }

                    $t_res = Products::whereRaw(1);
                    if ($row['model_attr'] == 1) {
                        $t_res = ProductsWarehouse::where('warehouse_id', $row['warehouse_id']);
                    } elseif ($row['model_attr'] == 2) {
                        $t_res = ProductsArea::where('area_id', $row['area_id']);
                    }

                    $t_res = $t_res->where('goods_id', $row['goods_id']);
                    $prod = BaseRepository::getToArrayFirst($t_res);

                    if (empty($prod)) { //当商品没有属性库存时
                        $row['goods_storage'] = $row['storage'];
                    }
                    $row['goods_storage'] = !empty($row['goods_storage']) ? $row['goods_storage'] : 0;
                    $row['storage'] = $row['goods_storage'];
                    $row['product_sn'] = isset($products['product_sn']) ? $products['product_sn'] : '';
                    //ecmoban模板堂 --zhuo end //库存查询

                    $brand = get_goods_brand_info($row['brand_id']);
                    $row['brand_name'] = $brand['brand_name'];

                    $_goods_thumb = $this->dscRepository->getImagePath($row['goods_thumb']);
                    $row['goods_thumb'] = $_goods_thumb;

                    $row['formated_subtotal'] = $this->dscRepository->getPriceFormat($row['amount']);
                    $row['formated_goods_price'] = $this->dscRepository->getPriceFormat($row['goods_price']);
                    //获取预售ID
                    $row['act_id'] = PresaleActivity::where('goods_id', $row['goods_id'])->value('act_id');
                    $row['act_id'] = $row['act_id'] ? $row['act_id'] : 0;

                    $row['warehouse_name'] = RegionWarehouse::where('region_id', $row['warehouse_id'])->value('region_name');
                    $row['warehouse_name'] = $row['warehouse_name'] ? $row['warehouse_name'] : '';

                    //将商品属性拆分为一个数组
                    $goods_attr = BaseRepository::getExplode($row['goods_attr'], ' ');

                    if ($row['extension_code'] == 'package_buy') {
                        $row['package_goods_list'] = $this->orderManageService->getPackageGoodsList($row['goods_id']);

                        $activity = get_goods_activity_info($row['goods_id'], ['act_id', 'activity_thumb']);
                        $row['goods_thumb'] = $activity['goods_thumb'] ?? '';
                    }

                    $goods_attr_id = $row['goods_attr_id'] ?? '';
                    $goods_attr_id = BaseRepository::getExplode($goods_attr_id);
                    $row['goods_thumb'] = $this->goodsAttrService->cartGoodsAttrImage($goods_attr_id, $productsGoodsAttrList, $row['goods_thumb']);

                    //仅退款申通通过则限制生成发货单 start
                    $ot_res = OrderReturn::where('rec_id', $row['rec_id'])->where('return_type', 3)->select('ret_id', 'agree_apply', 'return_status', 'refound_status');
                    $rec_info = BaseRepository::getToArrayFirst($ot_res);
                    if ($rec_info) {
                        $row = array_merge($row, $rec_info);
                    }
                    //仅退款申通通过则限制生成发货单 end

                    //判断是否是贡云订单
                    $cloud_count = OrderCloud::where('rec_id', $row['rec_id'])->count();

                    $row['is_cloud_order'] = 0;
                    if ($cloud_count > 0) {
                        $row['is_cloud_order'] = 1;
                        $is_cloud_order = 1;
                    }

                    $sql = [
                        'where' => [
                            [
                                'name' => 'goods_id',
                                'value' => $row['goods_id']
                            ],
                            [
                                'name' => 'order_id',
                                'value' => $row['order_id']
                            ],
                            [
                                'name' => 'product_id',
                                'value' => $row['product_id']
                            ]
                        ]
                    ];
                    $row['delivery'] = BaseRepository::getArraySqlGet($orderDeliveryList, $sql);
                    $row['delivery'] = $row['delivery'] ?? [];

                    $row['goods_bonus'] = $row['goods_bonus'] ?? 0;

                    $row['formated_goods_coupons'] = sprintf($GLOBALS['_LANG']['average_coupons'], $this->dscRepository->getPriceFormat($row['goods_coupons']));
                    $row['formated_goods_bonus'] = sprintf($GLOBALS['_LANG']['average_bonus'], $this->dscRepository->getPriceFormat($row['goods_bonus']));
                    $row['formated_goods_favourable'] = sprintf($GLOBALS['_LANG']['average_favourable'], $this->dscRepository->getPriceFormat($row['goods_favourable']));
                    $row['formated_goods_value_card'] = sprintf($GLOBALS['_LANG']['average_value_card'], $this->dscRepository->getPriceFormat($row['goods_value_card']));
                    $row['formated_value_card_discount'] = sprintf($GLOBALS['_LANG']['average_card_discount'], $this->dscRepository->getPriceFormat($row['value_card_discount']));
                    $row['formated_goods_integral_money'] = sprintf($GLOBALS['_LANG']['average_goods_integral_money'], $this->dscRepository->getPriceFormat($row['goods_integral_money']));

                    $goods_list[] = $row;
                }
            }

            $this->smarty->assign('is_cloud_order', $is_cloud_order);

            $attr = [];
            $arr = [];

            if (!empty($goods_attr)) {
                foreach ($goods_attr as $index => $array_val) {
                    $array_val = BaseRepository::getExplode($array_val);
                    if ($array_val) {
                        foreach ($array_val as $value) {
                            $arr = explode(':', $value);//以 : 号将属性拆开
                            $attr[$index][] = @['name' => $arr[0], 'value' => $arr[1]];
                        }
                    }
                }
            }
            $this->smarty->assign('goods_attr', $attr);
            $this->smarty->assign('goods_list', $goods_list);

            /* 取得能执行的操作列表 */
            $operable_list = $this->orderManageService->operableList($order, session('action_list'), $payment);
            $this->smarty->assign('operable_list', $operable_list);

            /* 判断退换货订单申请是否通过 */
            $is_apply = OrderReturn::where('order_id', $order['order_id'])->value('agree_apply');
            $this->smarty->assign('is_apply', $is_apply);
            /* 判断退换货订单申请是否通过 end */

            /**
             * 取得用户收货时间 以快物流信息显示为准，目前先用用户收货时间为准，后期修改TODO by Leah S
             */
            $log_time = OrderAction::where('order_id', $order['order_id'])->value('log_time');
            $log_time = $log_time ? $log_time : '';
            $res_time = TimeRepository::getLocalDate(config('shop.time_format'), $log_time);
            $this->smarty->assign('res_time', $res_time);


            /* 取得订单操作记录 */
            $act_list = [];
            $oa_res = OrderAction::where('order_id', $order['order_id'])
                ->orderBy('log_time', 'DESC')
                ->orderBy('action_id', 'DESC');
            $res = BaseRepository::getToArrayGet($oa_res);

            foreach ($res as $row) {
                $row['order_status'] = $GLOBALS['_LANG']['os'][$row['order_status']];
                $row['pay_status'] = $GLOBALS['_LANG']['ps'][$row['pay_status']];
                $row['shipping_status'] = $GLOBALS['_LANG']['ss'][$row['shipping_status']];
                $row['action_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['log_time']);
                $act_list[] = $row;
            }
            $this->smarty->assign('action_list', $act_list);

            /* 取得是否存在实体商品 */
            if (isset($order['is_zc_order']) && $order['is_zc_order']) {
                $this->smarty->assign('exist_real_goods', true);
            } else {
                $this->smarty->assign('exist_real_goods', $this->flowUserService->existRealGoods($order['order_id']));
            }

            /* 参数赋值：订单 */
            $this->smarty->assign('order', $order);

            //商家店铺信息打印到订单和快递单上
            $res = SellerShopinfo::where('ru_id', $order['ru_id'])->select('shop_name', 'country', 'province', 'city', 'district', 'shop_address', 'kf_tel');
            $store = BaseRepository::getToArrayFirst($res);

            $store['shop_name'] = $this->merchantCommonService->getShopName($order['ru_id'], 1);

            /* 是否打印订单，分别赋值 */
            if (isset($_GET['print'])) {

                $this->assign('order', $order);

                $this->assign('goods_attr', $attr);
                $this->assign('goods_list', $goods_list);

                $this->assign('shop_name', $store['shop_name']);
                $this->assign('shop_url', $this->dsc->url());
                $this->assign('shop_address', $store['shop_address']);
                $this->assign('service_phone', $store['kf_tel']);
                $this->assign('print_time', TimeRepository::getLocalDate(config('shop.time_format')));
                $this->assign('action_user', session('admin_name'));

                return $this->display('order_print_preview');

            } /* 打印快递单 */
            elseif (isset($_GET['shipping_print'])) {
                //快递鸟、电子面单 start
                if (get_print_type($adminru['ru_id'])) {
                    $url = 'tp_api.php?act=kdniao_print&order_id=' . $order_id;
                    return dsc_header("Location: $url\n");
                }
                //快递鸟、电子面单 end

                //发货地址所在地
                $region_array = [];
                //打印快递单地区 by wu
                $res = Region::whereRaw(1)->select('region_id', 'region_name');
                $region = BaseRepository::getToArrayGet($res);

                if (!empty($region)) {
                    foreach ($region as $region_data) {
                        $region_array[$region_data['region_id']] = $region_data['region_name'];
                    }
                }

                $province = isset($region_array[$store['province']]) ? $region_array[$store['province']] : 0;
                $city = isset($region_array[$store['city']]) ? $region_array[$store['city']] : 0;
                $district = isset($region_array[$store['district']]) ? $region_array[$store['district']] : 0;

                $this->smarty->assign('shop_name', $store['shop_name']);
                $this->smarty->assign('order_id', $order_id);
                $this->smarty->assign('province', $province);
                $this->smarty->assign('city', $city);
                $this->smarty->assign('district', $district);
                $this->smarty->assign('shop_address', $store['shop_address']);
                $this->smarty->assign('service_phone', $store['kf_tel']);

                $res = ShippingTpl::where('shipping_id', $order['shipping_id'])->where('ru_id', $order['ru_id']);
                $shipping = BaseRepository::getToArrayFirst($res);
                //打印单模式
                if (!empty($shipping) && $shipping['print_model'] == 2) {
                    /* 可视化 */
                    /* 快递单 */
                    $shipping['print_bg'] = empty($shipping['print_bg']) ? '' : $this->dscRepository->getImagePath($shipping['print_bg']);

                    /* 取快递单背景宽高 */
                    if (!empty($shipping['print_bg'])) {
                        $_size = @getimagesize($shipping['print_bg']);

                        if ($_size != false) {
                            $shipping['print_bg_size'] = ['width' => $_size[0], 'height' => $_size[1]];
                        }
                    }

                    if (empty($shipping['print_bg_size'])) {
                        $shipping['print_bg_size'] = ['width' => '1024', 'height' => '600'];
                    }

                    /* 标签信息 */
                    $lable_box = [];
                    $lable_box['t_shop_country'] = $region_array[$store['country']]; //网店-国家
                    $lable_box['t_shop_city'] = $region_array[$store['city']]; //网店-城市
                    $lable_box['t_shop_province'] = $region_array[$store['province']]; //网店-省份
                    if ($order['ru_id']) {
                        $lable_box['t_shop_name'] = $this->merchantCommonService->getShopName($order['ru_id'], 1);
                    } else {
                        $lable_box['t_shop_name'] = config('shop.shop_name', ''); //网店-名称
                    }
                    $lable_box['t_shop_district'] = $region_array[$store['district']]; //网店-区/县
                    $lable_box['t_shop_tel'] = $store['kf_tel']; //网店-联系电话
                    $lable_box['t_shop_address'] = $store['shop_address']; //网店-地址
                    $lable_box['t_customer_country'] = $region_array[$order['country']]; //收件人-国家
                    $lable_box['t_customer_province'] = $region_array[$order['province']]; //收件人-省份
                    $lable_box['t_customer_city'] = $region_array[$order['city']]; //收件人-城市
                    $lable_box['t_customer_district'] = $region_array[$order['district']]; //收件人-区/县
                    $lable_box['t_customer_tel'] = $order['tel']; //收件人-电话
                    $lable_box['t_customer_mobel'] = $order['mobile']; //收件人-手机
                    $lable_box['t_customer_post'] = $order['zipcode']; //收件人-邮编
                    $lable_box['t_customer_address'] = $order['address']; //收件人-详细地址
                    $lable_box['t_customer_name'] = $order['consignee']; //收件人-姓名

                    $gmtime_utc_temp = TimeRepository::getGmTime(); //获取 UTC 时间戳
                    $lable_box['t_year'] = TimeRepository::getLocalDate('Y', $gmtime_utc_temp); //年-当日日期
                    $lable_box['t_months'] = TimeRepository::getLocalDate('m', $gmtime_utc_temp); //月-当日日期
                    $lable_box['t_day'] = TimeRepository::getLocalDate('d', $gmtime_utc_temp); //日-当日日期

                    $lable_box['t_order_no'] = $order['order_sn']; //订单号-订单
                    $lable_box['t_order_postscript'] = $order['postscript']; //备注-订单
                    $lable_box['t_order_best_time'] = $order['best_time']; //送货时间-订单
                    $lable_box['t_pigeon'] = '√'; //√-对号
                    $lable_box['t_custom_content'] = ''; //自定义内容

                    //标签替换
                    $temp_config_lable = explode('||,||', $shipping['config_lable']);
                    if (!is_array($temp_config_lable)) {
                        $temp_config_lable[] = $shipping['config_lable'];
                    }
                    foreach ($temp_config_lable as $temp_key => $temp_lable) {
                        $temp_info = explode(',', $temp_lable);
                        if (is_array($temp_info)) {
                            $temp_info[1] = $lable_box[$temp_info[0]];
                        }
                        $temp_config_lable[$temp_key] = implode(',', $temp_info);
                    }
                    $shipping['config_lable'] = implode('||,||', $temp_config_lable);

                    $this->smarty->assign('shipping', $shipping);

                    return $this->smarty->display('print.dwt');
                } elseif (!empty($shipping) && !empty($shipping['shipping_print'])) {
                    /* 代码 */
                    echo $this->smarty->fetch("str:" . $shipping['shipping_print']);
                } else {
                    $shipping_code = Shipping::where('shipping_id', $order['shipping_id'])->value('shipping_code');
                    $shipping_code = $shipping_code ? $shipping_code : '';

                    /* 处理app */
                    if ($order['referer'] == 'mobile') {
                        $shipping_code = str_replace('ship_', '', $shipping_code);
                    }

                    if ($shipping_code) {
                        $shipping_name = StrRepository::studly($shipping_code);
                        $modules = plugin_path('Shipping/' . $shipping_name . '/config.php');

                        if (file_exists($modules)) {
                            $modules = include_once($modules);
                        }
                    }

                    $this->smarty->assign('modules', $modules);

                    if (!empty($GLOBALS['_LANG']['shipping_print'])) {
                        echo $this->smarty->fetch("str:{$GLOBALS['_LANG']['shipping_print']}");
                    } else {
                        echo $GLOBALS['_LANG']['no_print_shipping'];
                    }
                }
            } else {
                /* 模板赋值 */
                $this->smarty->assign('ur_here', lang('admin/order.order_info'));
                $this->smarty->assign('action_link', ['href' => 'order.php?act=list&' . list_link_postfix(), 'text' => lang('admin/common.02_order_list')]);

                /* 显示模板 */

                if (CROSS_BORDER === true) { // 跨境多商户
                    $admin = app(CrossBorderService::class)->adminExists();

                    if (!empty($admin)) {
                        $admin->smartyAssign();
                    }
                }

                $express_list = $this->expressManageService->getExpressCompany(1);

                $this->smarty->assign('express_list', $express_list);

                return $this->smarty->display('order_info.dwt');
            }
        }

        /* ------------------------------------------------------ */
        //-- 退货单详情
        /* ------------------------------------------------------ */

        elseif ($act == 'return_info') {
            /* 检查权限 */
            admin_priv('order_back_apply');

            $ret_id = (int)request()->input('ret_id', 0);
            $rec_id = (int)request()->input('rec_id', 0);

            /* 根据id查询退货单信息 */
            if (!empty($ret_id) || !empty($rec_id)) {
                $back_order = return_order_info($ret_id);
            } else {
                return 'order does not exist';
            }

            if (empty($back_order)) {
                return sys_msg(lang('admin/common.no_records')); // 没有找到任何记录
            }

            /* 如果管理员属于某个办事处，检查该订单是否也属于这个办事处 */
            $agency_id = AdminUser::where('user_id', session('admin_id'))->value('agency_id');
            $agency_id = $agency_id ? $agency_id : 0;
            if ($agency_id > 0) {
                if (!empty($back_order['agency_id']) && $back_order['agency_id'] != $agency_id) {
                    return sys_msg(lang('admin/common.priv_error'));
                }

                /* 取当前办事处信息 */
                $agency_name = Agency::where('agency_id', $agency_id)->value('agency_name');
                $back_order['agency_name'] = $agency_name ?? '';
            }

            /* 取得用户名 */
            if ($back_order['user_id'] > 0) {
                $user_info = Users::where('user_id', $back_order['user_id']);
                $user_info = BaseRepository::getToArrayFirst($user_info);

                if (!empty($user_info)) {
                    $back_order['user_name'] = $user_info['user_name'];

                    if (config('shop.show_mobile', 0) == 0) {
                        $back_order['user_name'] = $this->dscRepository->stringToStar($back_order['user_name']);
                    }
                }
            }

            if (config('shop.show_mobile', 0) == 0) {
                $back_order['phone'] = $this->dscRepository->stringToStar($back_order['phone']);
            }

            /* 取得区域名 */
            $back_order['region'] = $back_order['address_detail'];

            /* 是否保价 */
            $back_order['insure_yn'] = empty($back_order['insure_fee']) ? 0 : 1;/* 取得发货单商品 */;
            $goods_list = get_return_order_goods($rec_id);

            /**
             * 取的退换货订单商品
             */
            $return_list = get_return_goods($ret_id);

            //退换货可用配送列表
            $shipping_list = app(ShippingService::class)->returnShippingList();

            /* 取得配送费用 */
            $total = order_weight_price($back_order['order_id']);
            foreach ($shipping_list as $key => $shipping) {
                $shipping_fee = $this->dscRepository->shippingFee($shipping['shipping_code'], unserialize($shipping['configure']), $total['weight'], $total['amount'], $total['number']); //计算运费
                $free_price = free_price($shipping['configure']);   //免费额度
                $shipping_list[$key]['shipping_fee'] = $shipping_fee;
                $shipping_list[$key]['format_shipping_fee'] = $this->dscRepository->getPriceFormat($shipping_fee);
                $shipping_list[$key]['free_money'] = $this->dscRepository->getPriceFormat($free_price['configure']['free_money']);
            }
            $this->smarty->assign('shipping_list', $shipping_list);

            /* 取得退货订单操作记录 */
            $action_list = get_return_action($ret_id);
            $this->smarty->assign('action_list', $action_list);

            if ($back_order['is_zc_order']) {
                $this->smarty->assign('exist_real_goods', true);
            } else {
                /* 查询：是否存在实体商品 */
                $exist_real_goods = $this->flowUserService->existRealGoods($back_order['order_id']);
                $this->smarty->assign('exist_real_goods', $exist_real_goods);
            }

            $this->smarty->assign('goods_list', $goods_list);
            $this->smarty->assign('return_list', $return_list);

            $back_order['delivery_id'] = isset($back_order['delivery_id']) ? $back_order['delivery_id'] : 0;
            $this->smarty->assign('back_id', $back_order['delivery_id']); // 发货单id

            /* 模板赋值 */
            $this->smarty->assign('back_order', $back_order);

            if (CROSS_BORDER === true) { // 跨境多商户
                $admin = app(CrossBorderService::class)->adminExists();

                if (!empty($admin)) {
                    $admin->smartyAssign();
                }
            }

            /* 显示模板 */
            $this->smarty->assign('ur_here', lang('admin/order.back_operate') . lang('admin/order.detail'));
            $this->smarty->assign('action_link', ['href' => 'order.php?act=return_list&' . list_link_postfix(), 'text' => lang('admin/common.10_back_order')]);

            return $this->smarty->display('return_order_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 发货单列表
        /*------------------------------------------------------ */
        elseif ($act == 'delivery_list') {
            /* 检查权限 */
            admin_priv('delivery_view');

            //区分自营和店铺
            self_seller(basename(request()->getRequestUri()), 'delivery_list');

            /* 查询 */
            $result = $this->orderManageService->deliveryList();

            /* 模板赋值 */
            $this->smarty->assign('ur_here', lang('admin/common.09_delivery_order'));
            $this->smarty->assign('os_unconfirmed', OS_UNCONFIRMED);
            $this->smarty->assign('cs_await_pay', CS_AWAIT_PAY);
            $this->smarty->assign('cs_await_ship', CS_AWAIT_SHIP);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('delivery_list', $result['delivery']);
            $this->smarty->assign('filter', $result['filter']);
            $this->smarty->assign('record_count', $result['record_count']);
            $this->smarty->assign('page_count', $result['page_count']);
            $this->smarty->assign('sort_update_time', '<img src="' . __TPL__ . '/images/sort_desc.gif">');

            /* 显示模板 */

            return $this->smarty->display('delivery_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 搜索、排序、分页
        /*------------------------------------------------------ */
        elseif ($act == 'delivery_query') {
            /* 检查权限 */
            admin_priv('delivery_view');

            $result = $this->orderManageService->deliveryList();

            $this->smarty->assign('delivery_list', $result['delivery']);
            $this->smarty->assign('filter', $result['filter']);
            $this->smarty->assign('record_count', $result['record_count']);
            $this->smarty->assign('page_count', $result['page_count']);

            $sort_flag = sort_flag($result['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);
            return make_json_result($this->smarty->fetch('delivery_list.dwt'), '', ['filter' => $result['filter'], 'page_count' => $result['page_count']]);
        }

        /*------------------------------------------------------ */
        //-- 发货单详细
        /*------------------------------------------------------ */
        elseif ($act == 'delivery_info') {
            /* 检查权限 */
            admin_priv('delivery_view');

            $delivery_id = intval(trim($_REQUEST['delivery_id']));

            /* 根据发货单id查询发货单信息 */
            if (!empty($delivery_id)) {
                $delivery_order = $this->orderManageService->deliveryOrderInfo($delivery_id);
            } else {
                return 'order does not exist';
            }

            /* 如果管理员属于某个办事处，检查该订单是否也属于这个办事处 */
            $agency_id = AdminUser::where('user_id', session('admin_id'))->value('agency_id');
            $agency_id = $agency_id ? $agency_id : 0;

            if ($agency_id > 0) {
                if (!empty($delivery_order['agency_id']) && $delivery_order['agency_id'] != $agency_id) {
                    return sys_msg(lang('admin/common.priv_error'));
                }

                /* 取当前办事处信息 */
                $agency_name = Agency::where('agency_id', $agency_id)->value('agency_name');
                $delivery_order['agency_name'] = $agency_name ?? '';
            }

            /* 取得用户名 */
            if ($delivery_order['user_id'] > 0) {
                $user_info = Users::where('user_id', $delivery_order['user_id']);
                $user_info = BaseRepository::getToArrayFirst($user_info);

                if (!empty($user_info)) {
                    $delivery_order['user_name'] = $user_info['user_name'];

                    if (config('shop.show_mobile', 0) == 0) {
                        $delivery_order['user_name'] = $this->dscRepository->stringToStar($delivery_order['user_name']);
                        $delivery_order['mobile'] = $this->dscRepository->stringToStar($delivery_order['mobile']);
                    }
                }
            }

            /* 是否保价 */
            $order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;

            /* 取得发货单商品 */
            $res = DeliveryGoods::where('delivery_id', $delivery_order['delivery_id']);
            $res = $res->with(['getGoods' => function ($query) {
                $query->select('goods_id', 'brand_id', 'goods_thumb');
            }]);
            $goods_list = BaseRepository::getToArrayGet($res);

            if ($goods_list) {

                $orderGoodsList = OrderDataHandleService::orderGoodsDataList($delivery_order['order_id'], ['order_id', 'goods_attr_id', 'goods_id', 'product_id', 'goods_attr'], 1);
                $orderGoodsList = BaseRepository::getArrayCollapse($orderGoodsList);

                $productsGoodsAttrList = [];
                if ($orderGoodsList) {
                    $productsGoodsAttr = BaseRepository::getKeyPluck($orderGoodsList, 'goods_attr_id');
                    $productsGoodsAttrList = GoodsDataHandleService::getGoodsAttrDataList($productsGoodsAttr, ['goods_attr_id', 'attr_img_flie', 'attr_gallery_flie']);
                }

                foreach ($goods_list as $key => $row) {
                    $row['brand_id'] = '';
                    $row['goods_thumb'] = '';

                    //图片显示
                    $row['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);

                    $sql = [
                        'where' => [
                            [
                                'name' => 'goods_id',
                                'value' => $row['goods_id']
                            ],
                            [
                                'name' => 'product_id',
                                'value' => $row['product_id']
                            ],
                            [
                                'name' => 'goods_attr',
                                'value' => $row['goods_attr']
                            ]
                        ]
                    ];
                    $orderGoods = BaseRepository::getArraySqlFirst($orderGoodsList, $sql);

                    $goods_attr_id = $orderGoods['goods_attr_id'] ?? '';
                    $goods_attr_id = BaseRepository::getExplode($goods_attr_id, ',');
                    $row['goods_thumb'] = $this->goodsAttrService->cartGoodsAttrImage($goods_attr_id, $productsGoodsAttrList, $row['goods_thumb']);

                    $goods_list[$key] = $row;
                }
            }

            /* 是否存在实体商品 */
            $exist_real_goods = 0;
            if ($goods_list) {
                foreach ($goods_list as $value) {
                    if ($value['is_real']) {
                        $exist_real_goods++;
                    }
                }
            }
            $orderInfo = OrderInfo::where('order_id', $delivery_order['order_id']);
            $orderInfo = BaseRepository::getToArrayFirst($orderInfo);
            $this->smarty->assign('order_info', $orderInfo);

            /* 取得订单操作记录 */
            $act_list = [];
            $res = OrderAction::where('order_id', $delivery_order['order_id'])
                ->where('action_place', 1)
                ->orderBy('log_time', 'DESC')
                ->orderBy('action_id', 'DESC');
            $res = BaseRepository::getToArrayGet($res);

            foreach ($res as $row) {
                $row['order_status'] = $GLOBALS['_LANG']['os'][$row['order_status']];
                $row['pay_status'] = $GLOBALS['_LANG']['ps'][$row['pay_status']];
                $row['shipping_status'] = ($row['shipping_status'] == SS_SHIPPED_ING) ? $GLOBALS['_LANG']['ss_admin'][SS_SHIPPED_ING] : $GLOBALS['_LANG']['ss'][$row['shipping_status']];
                $row['action_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['log_time']);
                $act_list[] = $row;
            }
            $this->smarty->assign('action_list', $act_list);

            /* 模板赋值 */
            $this->smarty->assign('delivery_order', $delivery_order);
            if ($delivery_order['is_zc_order']) {
                $this->smarty->assign('exist_real_goods', true);
            } else {
                $this->smarty->assign('exist_real_goods', $exist_real_goods);
            }
            $this->smarty->assign('goods_list', $goods_list);
            $this->smarty->assign('delivery_id', $delivery_id); // 发货单id

            /* 显示模板 */
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['delivery_operate'] . lang('admin/order.detail'));
            $this->smarty->assign('action_link', ['href' => 'order.php?act=delivery_list&' . list_link_postfix(), 'text' => lang('admin/common.09_delivery_order')]);
            $this->smarty->assign('action_act', ($delivery_order['status'] == DELIVERY_CREATE) ? 'delivery_ship' : 'delivery_cancel_ship');
            $this->smarty->assign('express_list', $this->expressManageService->getExpressCompany(1));

            return $this->smarty->display('delivery_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 发货单发货确认
        /*------------------------------------------------------ */
        elseif ($act == 'delivery_ship') {
            /* 检查权限 */
            admin_priv('delivery_view');

            /* 定义当前时间 */
            define('GMTIME_UTC', TimeRepository::getGmTime()); // 获取 UTC 时间戳

            /* 取得参数 */
            $delivery = [];
            $order_id = intval(trim($_REQUEST['order_id']));        // 订单id
            $delivery_id = intval(trim($_REQUEST['delivery_id']));        // 发货单id
            $invoice_no = isset($_REQUEST['invoice_no']) ? trim($_REQUEST['invoice_no']) : '';
            $express_code = isset($_REQUEST['express_code']) ? trim($_REQUEST['express_code']) : '';
            $express_info = $this->expressManageService->getExpressCompanyByCode($express_code);

            $delivery['invoice_no'] = $invoice_no;
            $delivery['express_code'] = $express_code;
            $action_note = isset($_REQUEST['action_note']) ? trim($_REQUEST['action_note']) : '';
            if (empty($invoice_no)) {
                /* 操作失败 */
                $links[] = ['text' => lang('admin/order.delivery_info'), 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id];
                return sys_msg(lang('admin/order.invoice_no_sms'), 1, $links);
            }

            /* 根据发货单id查询发货单信息 */
            if (!empty($delivery_id)) {
                $delivery_order = $this->orderManageService->deliveryOrderInfo($delivery_id);
            } else {
                return 'order does not exist';
            }

            /* 查询订单信息 */
            $order = order_info($order_id);
            /* 检查此单发货商品库存缺货情况  ecmoban模板堂 --zhuo start 下单减库存*/
            $delivery_stock_sql = "SELECT DG.rec_id AS dg_rec_id, OG.rec_id AS og_rec_id, G.model_attr, G.model_inventory, DG.goods_id, DG.delivery_id, DG.is_real, DG.send_number AS sums, G.goods_number AS storage, G.goods_name, DG.send_number," .
                " OG.goods_attr_id, OG.warehouse_id, OG.area_id, OG.area_city, OG.ru_id, OG.order_id, OG.product_id FROM " . $this->dsc->table('delivery_goods') . " AS DG, " .
                $this->dsc->table('goods') . " AS G, " .
                $this->dsc->table('delivery_order') . " AS D, " .
                $this->dsc->table('order_goods') . " AS OG " .
                " WHERE DG.goods_id = G.goods_id AND DG.delivery_id = D.delivery_id AND D.order_id = OG.order_id AND DG.goods_sn = OG.goods_sn AND DG.product_id = OG.product_id AND DG.delivery_id = '$delivery_id' GROUP BY OG.rec_id ";

            $delivery_stock_result = $this->db->getAll($delivery_stock_sql);

            $virtual_goods = [];
            for ($i = 0; $i < count($delivery_stock_result); $i++) {
                $res = Products::whereRaw(1);
                if ($delivery_stock_result[$i]['model_attr'] == 1) {
                    $res = ProductsWarehouse::where('warehouse_id', $delivery_stock_result[$i]['warehouse_id']);
                } elseif ($delivery_stock_result[$i]['model_attr'] == 2) {
                    $res = ProductsArea::where('area_id', $delivery_stock_result[$i]['area_id']);
                }

                $res = $res->where('goods_id', $delivery_stock_result[$i]['goods_id']);
                $prod = BaseRepository::getToArrayFirst($res);

                /* 如果商品存在规格就查询规格，如果不存在规格按商品库存查询 */
                if (empty($prod)) {
                    if ($delivery_stock_result[$i]['model_inventory'] == 1) {
                        $delivery_stock_result[$i]['storage'] = get_warehouse_area_goods($delivery_stock_result[$i]['warehouse_id'], $delivery_stock_result[$i]['goods_id'], 'warehouse_goods');
                    } elseif ($delivery_stock_result[$i]['model_inventory'] == 2) {
                        $delivery_stock_result[$i]['storage'] = get_warehouse_area_goods($delivery_stock_result[$i]['area_id'], $delivery_stock_result[$i]['goods_id'], 'warehouse_area_goods');
                    }
                } else {
                    $products = $this->goodsWarehouseService->getWarehouseAttrNumber($delivery_stock_result[$i]['goods_id'], $delivery_stock_result[$i]['goods_attr_id'], $delivery_stock_result[$i]['warehouse_id'], $delivery_stock_result[$i]['area_id'], $delivery_stock_result[$i]['area_city'], $delivery_stock_result[$i]['model_attr']);
                    $delivery_stock_result[$i]['storage'] = $products['product_number'];
                }

                if (($delivery_stock_result[$i]['sums'] > $delivery_stock_result[$i]['storage'] || $delivery_stock_result[$i]['storage'] <= 0) && ((config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_SHIP) || (config('shop.use_storage') == '0' && $delivery_stock_result[$i]['is_real'] == 0))) {
                    /* 操作失败 */
                    $links[] = ['text' => lang('admin/order.order_info'), 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id];
                    return sys_msg(sprintf(lang('admin/order.act_good_vacancy'), $delivery_stock_result[$i]['goods_name']), 1, $links);
                    break;
                }

                /* 虚拟商品列表 virtual_card*/
                if ($delivery_stock_result[$i]['is_real'] == 0) {
                    $virtual_goods[] = [
                        'goods_id' => $delivery_stock_result[$i]['goods_id'],
                        'goods_name' => $delivery_stock_result[$i]['goods_name'],
                        'num' => $delivery_stock_result[$i]['send_number']
                    ];
                }
            }

            //ecmoban模板堂 --zhuo end 下单减库存

            /* 发货 */
            /* 处理虚拟卡 商品（虚货） */
            if ($virtual_goods && is_array($virtual_goods) && count($virtual_goods) > 0) {
                foreach ($virtual_goods as $virtual_value) {
                    virtual_card_shipping($virtual_value, $order['order_sn'], $msg, 'split');
                }

                //虚拟卡缺货
                if (!empty($msg)) {
                    $links[] = ['text' => lang('admin/order.delivery_sn') . lang('admin/order.detail'), 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id];
                    return sys_msg($msg, 1, $links);
                }
            }

            /* 如果使用库存，且发货时减库存，则修改库存 */
            if (config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_SHIP) {
                foreach ($delivery_stock_result as $value) {

                    /* 商品（实货）、超级礼包（实货） ecmoban模板堂 --zhuo */
                    if ($value['is_real'] != 0) {
                        //（货品）
                        if (!empty($value['product_id'])) {
                            if ($value['model_attr'] == 1) {
                                ProductsWarehouse::where('product_id', $value['product_id'])
                                    ->decrement('product_number', $value['sums']);
                            } elseif ($value['model_attr'] == 2) {
                                ProductsArea::where('product_id', $value['product_id'])
                                    ->decrement('product_number', $value['sums']);
                            } else {
                                Products::where('product_id', $value['product_id'])
                                    ->decrement('product_number', $value['sums']);
                            }
                        } else {
                            if ($value['model_inventory'] == 1) {
                                WarehouseGoods::where('goods_id', $value['goods_id'])
                                    ->where('region_id', $value['warehouse_id'])
                                    ->decrement('region_number', $value['sums']);
                            } elseif ($value['model_inventory'] == 2) {
                                WarehouseAreaGoods::where('goods_id', $value['goods_id'])
                                    ->where('region_id', $value['area_id'])
                                    ->decrement('region_number', $value['sums']);
                            } else {
                                Goods::where('goods_id', $value['goods_id'])
                                    ->decrement('goods_number', $value['sums']);
                            }
                        }

                        //库存日志
                        $logs_other = [
                            'goods_id' => $value['goods_id'],
                            'order_id' => $value['order_id'],
                            'use_storage' => config('shop.stock_dec_time'),
                            'admin_id' => session('admin_id'),
                            'number' => "- " . $value['sums'],
                            'model_inventory' => $value['model_inventory'],
                            'model_attr' => $value['model_attr'],
                            'product_id' => $value['product_id'],
                            'warehouse_id' => $value['warehouse_id'],
                            'area_id' => $value['area_id'],
                            'add_time' => TimeRepository::getGmTime()
                        ];

                        GoodsInventoryLogs::insert($logs_other);
                    }
                }
            }

            /* 修改发货单信息 */
            $invoice_no = str_replace(',', ',', $delivery['invoice_no']);
            $invoice_no = trim($invoice_no, ',');
            $_delivery['invoice_no'] = $invoice_no;
            $_delivery['express_code'] = $express_code;
            $_delivery['status'] = DELIVERY_SHIPPED; // 0，为已发货
            if (isset($express_info['name'])) {
                $_delivery['shipping_name'] = $express_info['name'];
            }
            $query = DeliveryOrder::where('delivery_id', $delivery_id)->update($_delivery);
            if (!$query) {
                /* 操作失败 */
                $links[] = ['text' => lang('admin/order.delivery_sn') . lang('admin/order.detail'), 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id];
                return sys_msg(lang('admin/order.act_false'), 1, $links);
            }

            /* 标记订单为已确认 “已发货” */
            /* 更新发货时间 */
            $order_finish = $this->orderManageService->getAllDeliveryFinish($order_id);
            $shipping_status = ($order_finish == 1) ? SS_SHIPPED : SS_SHIPPED_PART;
            $arr['shipping_status'] = $shipping_status;
            $arr['shipping_time'] = GMTIME_UTC; // 发货时间
            $arr['invoice_no'] = trim($order['invoice_no'] . ',' . $invoice_no, ',');
            $arr['express_code'] = $express_code;
            if (isset($express_info['name'])) {
                $arr['shipping_name'] = $express_info['name'];
            }
            update_order($order_id, $arr);

            // 记录物流信息
            $this->expressManageService->saveExpressHistory($order, $delivery_order, $invoice_no, $express_code);

            $this->orderCommonService->updateMainOrder($order, 3, $action_note, session('admin_name'));

            /* 发货单发货记录log */
            order_action($order['order_sn'], OS_CONFIRMED, $shipping_status, $order['pay_status'], $action_note, session('admin_name'), 1);

            /* 如果当前订单已经全部发货 */
            if ($order_finish) {
                /* 如果订单用户不为空，计算积分，并发给用户；发红包 */
                if ($order['user_id'] > 0) {

                    /* 计算并发放积分 */
                    $integral = integral_to_give($order);
                    /*如果已配送子订单的赠送积分大于0   减去已配送子订单积分*/
                    if (!empty($child_order)) {
                        $integral['custom_points'] = $integral['custom_points'] - $child_order['custom_points'];
                        $integral['rank_points'] = $integral['rank_points'] - $child_order['rank_points'];
                    }
                    log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf(lang('admin/order.order_gift_integral'), $order['order_sn']));

                    /* 发放红包 */
                    send_order_bonus($order_id);

                    /* 发放优惠券 bylu */
                    send_order_coupons($order_id);
                }

                /* 更新商品销量 按照步骤发货 */
                get_goods_sale($order_id);

                /* 更新会员订单信息 */
                $this->orderCommonService->getUserOrderNumServer($order['user_id'] ?? 0);

                /* 发送邮件 */
                if (config('shop.send_ship_email', 0) == '1') {
                    $order['invoice_no'] = $invoice_no;
                    $tpl = get_mail_template('deliver_notice');
                    $this->smarty->assign('order', $order);
                    $this->smarty->assign('send_time', TimeRepository::getLocalDate(config('shop.time_format')));
                    $this->smarty->assign('shop_name', config('shop.shop_name', ''));
                    $this->smarty->assign('send_date', TimeRepository::getLocalDate(config('shop.time_format'), TimeRepository::getGmTime()));
                    $this->smarty->assign('sent_date', TimeRepository::getLocalDate(config('shop.time_format'), TimeRepository::getGmTime()));
                    $this->smarty->assign('confirm_url', $this->dsc->url() . '/user_order.php?act=order_detail&order_id=' . $order['order_id']); //by wu
                    $this->smarty->assign('send_msg_url', $this->dsc->url() . '/user_message.php?act=message_list&order_id=' . $order['order_id']);
                    $content = $this->smarty->fetch('str:' . $tpl['template_content']);
                    if (!CommonRepository::sendEmail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html'])) {
                        $msg = lang('admin/order.send_mail_fail');
                    }
                }

                /* 如果需要，发短信 */
                if (config('shop.sms_order_shipped', 0) == '1' && $order['mobile'] != '') {

                    //短信接口参数
                    if (is_numeric($order['ru_id'])) {
                        $shop_name = $this->merchantCommonService->getShopName($order['ru_id'], 1);
                    } else {
                        $shop_name = "";
                    }

                    $user_info = get_admin_user_info($order['user_id']);

                    $smsParams = [
                        'shop_name' => $shop_name,
                        'shopname' => $shop_name,
                        'user_name' => $user_info['user_name'],
                        'username' => $user_info['user_name'],
                        'consignee' => $order['consignee'],
                        'order_sn' => $order['order_sn'],
                        'ordersn' => $order['order_sn'],
                        'invoice_no' => $order['invoice_no'],
                        'invoiceno' => $order['invoice_no'],
                        'mobile_phone' => $order['mobile'],
                        'mobilephone' => $order['mobile']
                    ];
                    $this->commonRepository->smsSend($order['mobile'], $smsParams, 'sms_order_shipped', false);
                }

                // 微信通模板消息 发货通知
                if (file_exists(MOBILE_WECHAT)) {
                    $pushData = [
                        'first' => ['value' => lang('wechat::wechat.order_shipping_first')], // 标题
                        'keyword1' => ['value' => $order['order_sn']], //订单
                        'keyword2' => ['value' => $order['shipping_name']], //物流服务
                        'keyword3' => ['value' => $invoice_no], //快递单号
                        'keyword4' => ['value' => $order['consignee']], // 收货信息
                        'remark' => ['value' => lang('wechat::wechat.order_shipping_remark')]
                    ];
                    $shop_url = url('/') . '/'; // 根域名 兼容商家后台
                    $order_url = dsc_url('/#/user/orderDetail/' . $order_id);

                    push_template_curl('OPENTM202243318', $pushData, $order_url, $order['user_id'], $shop_url);
                }
            }

            /* 操作成功 */
            $links[] = ['text' => lang('admin/common.09_delivery_order'), 'href' => 'order.php?act=delivery_list'];
            $links[] = ['text' => lang('admin/order.delivery_sn') . lang('admin/order.detail'), 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id];
            return sys_msg(lang('admin/order.act_ok'), 0, $links);
        }

        /*------------------------------------------------------ */
        //-- 检测确认收货订单 ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'order_detection') {
            //区分自营和店铺
            self_seller(basename(request()->getRequestUri()), 'order_detection');

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['11_order_detection']);
            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            $order_list = get_order_detection_list();
            $this->smarty->assign('order_list', $order_list['orders']);
            $this->smarty->assign('filter', $order_list['filter']);
            $this->smarty->assign('record_count', $order_list['record_count']);
            $this->smarty->assign('page_count', $order_list['page_count']);
            $this->smarty->assign('sort_order_time', '<img src="' . __TPL__ . '/images/sort_desc.gif">');

            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('is_detection', 1);

            return $this->smarty->display('order_detection_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 发货中订单列表排序、分页、查询
        /*------------------------------------------------------ */
        elseif ($act == 'detection_query') {
            /* 检查权限 */
            admin_priv('order_detection');
            $order_list = get_order_detection_list();

            $this->smarty->assign('order_list', $order_list['orders']);
            $this->smarty->assign('filter', $order_list['filter']);
            $this->smarty->assign('record_count', $order_list['record_count']);
            $this->smarty->assign('page_count', $order_list['page_count']);
            $sort_flag = sort_flag($order_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            $this->smarty->assign('is_detection', 1);

            return make_json_result($this->smarty->fetch('order_detection_list.dwt'), '', ['filter' => $order_list['filter'], 'page_count' => $order_list['page_count']]);
        }

        /*------------------------------------------------------ */
        //-- 处理系统设置订单自动确认收货订单
        /*------------------------------------------------------ */
        elseif ($act == 'auto_order_detection') {
            /* 检查权限 */
            admin_priv('order_detection');

            //区分自营和店铺
            self_seller(basename(request()->getRequestUri()), 'order_detection');

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['11_order_detection']);

            $order_list = get_order_detection_list(3, 1);

            if ($order_list['orders']) {
                session([
                    'is_ajax_detection' => 1
                ]);
            } else {
                session([
                    'is_ajax_detection' => 0
                ]);
            }

            $this->smarty->assign('filter', $order_list['filter']);
            $this->smarty->assign('is_detection', 2);
            $this->smarty->assign('full_page', 1);
            return $this->smarty->display('order_detection_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 处理系统设置订单自动确认收货订单
        /*------------------------------------------------------ */
        elseif ($act == 'ajax_order_detection') {
            /* 检查权限 */
            admin_priv('order_detection');

            /* 设置最长执行时间为5分钟 */
            @set_time_limit(300);
            if (session()->has('is_ajax_detection') && session('is_ajax_detection') == 1) {
                $order_list = get_order_detection_list(3, 1);

                $result['page'] = $order_list['filter']['page'];
                $result['page_size'] = $order_list['filter']['page_size'];
                $result['record_count'] = $order_list['filter']['record_count'];
                $result['page_count'] = $order_list['filter']['page_count'];

                $result['order'] = $order_list['orders'][0] ?? [];

                $orderDelivery = !empty($result['order']) ? CommonRepository::orderDeliveryCondition($result['order']['order_status'], $result['order']['shipping_status'], $result['order']['pay_status']) : false;

                if ($result['order'] && $orderDelivery && $result['order']['shipping_status'] != SS_RECEIVED) {

                    // 订单是否全部发货 全部发货 => 全部收货、部分发货 => 部分收货
                    $order_finish = OrderRepository::getAllDeliveryFinish($result['order']['order_id']);
                    $shipping_status = ($order_finish == 1) ? SS_RECEIVED : SS_PART_RECEIVED;

                    $confirm_take_time = TimeRepository::getGmTime();
                    $operator = lang('admin/common.system_handle');

                    // 记录日志
                    $note = lang('admin/order.self_motion_goods');
                    order_action($result['order']['order_sn'], $result['order']['order_status'], $shipping_status, $result['order']['pay_status'], $note, $operator, 0, $confirm_take_time);

                    $data = [
                        'confirm_take_time' => $confirm_take_time,
                        'order_status' => $result['order']['order_status'],
                        'shipping_status' => $shipping_status,
                        'pay_status' => $result['order']['pay_status']
                    ];
                    $updateRes = OrderInfo::where('order_id', $result['order']['order_id'])->update($data);

                    if ($updateRes > 0) {
                        $result['order']['new_shipping_status'] = $GLOBALS['_LANG']['ss'][$data['shipping_status']];

                        /* 更新会员订单信息 */
                        $dbRaw = [
                            'order_isfinished' => "order_isfinished + 1"
                        ];

                        $order_nogoods = UserOrderNum::where('user_id', $result['order']['user_id'])->value('order_nogoods');
                        $order_nogoods = $order_nogoods ? $order_nogoods : 0;

                        if ($order_nogoods > 0) {
                            $dbRaw['order_nogoods'] = "order_nogoods - 1";
                        }

                        $dbRaw = BaseRepository::getDbRaw($dbRaw);
                        UserOrderNum::where('user_id', $result['order']['user_id'])->update($dbRaw);

                        // 订单收货事件监听
                        event(new \App\Events\OrderReceiveEvent($result['order']));
                    }
                }

                if ($order_list['filter']['page'] >= $order_list['filter']['page_count']) {
                    $result['stop_ajax'] = 0;
                    session([
                        'is_ajax_detection' => 0
                    ]);
                } else {
                    $result['stop_ajax'] = 1;
                }
            } else {
                $result['order'] = '';
                $result['stop_ajax'] = 0;
            }

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 发货单取消发货
        /*------------------------------------------------------ */
        elseif ($act == 'delivery_cancel_ship') {
            /* 检查权限 */
            admin_priv('delivery_view');

            /* 取得参数 */
            $delivery = [];
            $order_id = intval(trim($_REQUEST['order_id']));        // 订单id
            $delivery_id = intval(trim($_REQUEST['delivery_id']));        // 发货单id
            $delivery['invoice_no'] = isset($_REQUEST['invoice_no']) ? trim($_REQUEST['invoice_no']) : '';
            $action_note = isset($_REQUEST['action_note']) ? trim($_REQUEST['action_note']) : '';

            /* 根据发货单id查询发货单信息 */
            if (!empty($delivery_id)) {
                $delivery_order = $this->orderManageService->deliveryOrderInfo($delivery_id);
            } else {
                return 'order does not exist';
            }

            /* 查询订单信息 */
            $order = order_info($order_id);

            /* 取消当前发货单物流单号 */
            $_delivery['invoice_no'] = '';
            $_delivery['status'] = DELIVERY_CREATE;
            $query = DeliveryOrder::where('delivery_id', $delivery_id)->update($_delivery);
            if (!$query) {
                /* 操作失败 */
                $links[] = ['text' => lang('admin/order.delivery_sn') . lang('admin/order.detail'), 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id];
                return sys_msg(lang('admin/order.act_false'), 1, $links);
            }

            /* 修改定单发货单号 */
            if ($order['invoice_no']) {
                $invoice_no_order = explode(',', $order['invoice_no']);
                $invoice_no_delivery = explode(',', $delivery_order['invoice_no']);
                foreach ($invoice_no_order as $key => $value) {
                    $delivery_key = array_search($value, $invoice_no_delivery);
                    if ($delivery_key !== false) {
                        unset($invoice_no_order[$key], $invoice_no_delivery[$delivery_key]);
                        if (count($invoice_no_delivery) == 0) {
                            break;
                        }
                    }
                }
                $_order['invoice_no'] = implode(',', $invoice_no_order);
            } else {
                $_order['invoice_no'] = '';
            }

            /* 更新配送状态 */
            $order_finish = $this->orderManageService->getAllDeliveryFinish($order_id);
            $shipping_status = ($order_finish == -1) ? SS_SHIPPED_PART : SS_SHIPPED_ING;
            $arr['shipping_status'] = $shipping_status;
            if ($shipping_status == SS_SHIPPED_ING) {
                $arr['shipping_time'] = ''; // 发货时间
            }
            $arr['invoice_no'] = $_order['invoice_no'];
            update_order($order_id, $arr);

            /* 发货单取消发货记录log */
            order_action($order['order_sn'], $order['order_status'], $shipping_status, $order['pay_status'], $action_note, session('admin_name'), 1);

            /* 如果使用库存，则增加库存 */
            if (config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_SHIP) {
                // 检查此单发货商品数量
                $virtual_goods = [];
                $res = DeliveryGoods::selectRaw('goods_id,product_id,is_real,SUM(send_number) AS sums');
                $res = $res->where('delivery_id', $delivery_id)->groupBy('goods_id');

                $delivery_stock_result = BaseRepository::getToArrayGet($res);
                if (!empty($delivery_stock_result)) {
                    foreach ($delivery_stock_result as $key => $value) {
                        /* 虚拟商品 */
                        if ($value['is_real'] == 0) {
                            continue;
                        }

                        //（货品）
                        if (!empty($value['product_id'])) {
                            Products::where('product_id', $value['product_id'])
                                ->increment('product_number', $value['sums']);
                        }
                        Goods::where('goods_id', $value['goods_id'])->increment('goods_number', $value['sums']);
                    }
                }
            }

            /* 发货单全退回时，退回其它 */
            if ($order['order_status'] == SS_SHIPPED_ING) {
                /* 如果订单用户不为空，计算积分，并退回 */
                if ($order['user_id'] > 0) {
                    /* 计算并退回积分 */
                    $integral = integral_to_give($order);
                    log_account_change($order['user_id'], 0, 0, (-1) * intval($integral['rank_points']), (-1) * intval($integral['custom_points']), sprintf(lang('admin/order.return_order_gift_integral'), $order['order_sn']));

                    /* 计算并退回红包 */
                    return_order_bonus($order_id);
                }
            }

            // 删除物流记录
            $this->expressManageService->removeExpressHistory($delivery_id);

            /* 清除缓存 */
            clear_cache_files();

            /* 操作成功 */
            $links[] = ['text' => lang('admin/order.delivery_sn') . lang('admin/order.detail'), 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id];
            return sys_msg(lang('admin/order.act_ok'), 0, $links);
        }

        /*------------------------------------------------------ */
        //-- 退货单列表
        /*------------------------------------------------------ */
        elseif ($act == 'back_list') {
            /* 检查权限 */
            admin_priv('back_view');

            $seller_order = (int)request()->input('seller_list', 0);
            $seller_order = !empty($seller_order) ? 1 : 0;  //商家和自营订单标识

            /* 查询 */
            $result = $this->orderManageService->backList();

            /* 模板赋值 */
            $this->smarty->assign('ur_here', lang('admin/common.10_back_order'));

            /* 订单内平台、店铺区分 */
            $this->smarty->assign('common_tabs', ['info' => $seller_order, 'url' => 'order.php?act=back_list']);
            $this->smarty->assign('os_unconfirmed', OS_UNCONFIRMED);
            $this->smarty->assign('cs_await_pay', CS_AWAIT_PAY);
            $this->smarty->assign('cs_await_ship', CS_AWAIT_SHIP);
            $this->smarty->assign('full_page', 1);

            $this->smarty->assign('back_list', $result['back']);
            $this->smarty->assign('filter', $result['filter']);
            $this->smarty->assign('record_count', $result['record_count']);
            $this->smarty->assign('page_count', $result['page_count']);
            $this->smarty->assign('sort_update_time', '<img src="' . __TPL__ . '/images/sort_desc.gif">');

            /* 显示模板 */
            return $this->smarty->display('back_list.dwt');
        } /**
         * 退换货原因列表  by Leah
         */
        elseif ($act == 'back_cause_list') {

            /* 检查权限 */
            admin_priv('order_back_cause');

            /* 查询 */
            $parent_id = isset($_GET['parent_id']) ? intval($_GET['parent_id']) : 0;
            if ($parent_id > 0) {
                $result = $this->orderManageService->getCauseCatLevel($parent_id);
            } else {
                $result = cause_list(0, 0, false);
            }

            $this->smarty->assign('parent_id', $parent_id);

            /* 模板赋值 */
            $this->smarty->assign('ur_here', lang('admin/common.10_back_order'));
            $this->smarty->assign('action_link', ['href' => 'order.php?act=add_return_cause', 'text' => $GLOBALS['_LANG']['add_return_cause']]);

            $this->smarty->assign('os_unconfirmed', OS_UNCONFIRMED);
            $this->smarty->assign('cs_await_pay', CS_AWAIT_PAY);
            $this->smarty->assign('cs_await_ship', CS_AWAIT_SHIP);
            $this->smarty->assign('full_page', 1);

            $this->smarty->assign('cause_list', $result);
            $this->smarty->assign('sort_update_time', '<img src="' . __TPL__ . '/images/sort_desc.gif">');

            /* 显示模板 */

            return $this->smarty->display('back_cause_list.dwt');
        } elseif ($act == 'edit_bank_sort_order') {
            $check_auth = check_authz_json('order_back_cause');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_POST['id']);
            $order = intval($_POST['val']);

            $data = ['sort_order' => $order];
            $res = ReturnCause::where('cause_id', $id)->update($data);
            if ($res > 0) {
                return make_json_result($order);
            } else {
                return make_json_error($GLOBALS['_LANG']['system_error']);
            }
        } /**
         * 添加退货原因
         * by Leah
         */
        elseif ($act == 'add_return_cause') {

            /* 检查权限 */
            admin_priv('order_back_cause');
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['add_return_cause']);
            $this->smarty->assign('action_link', ['href' => 'order.php?act=back_cause_list', 'text' => $GLOBALS['_LANG']['return_cause_list']]);

            $parent_id = isset($_GET['parent_id']) ? intval($_GET['parent_id']) : 0;
            if ($parent_id) {
                $cause_info = [];
                $cause_list = cause_list($parent_id, 0, false, 1);
                foreach ($cause_list as $v) {
                    $cause_info['cause_name'] = $v['cause_name'];
                    $cause_info['cause_id'] = $v['cause_id'];
                }
                $this->smarty->assign('parent_cat', $cause_info);
            }

            $cause_select = cause_list(0, 0, false);
            /* 简单处理缩进 */
            foreach ($cause_select as $k => $v) {
                if ($v['level']) {
                    $level = str_repeat('&nbsp;', $v['level'] * 4);
                    $cause_select[$k]['name'] = $level . $v['name'];
                }
            }
            $this->smarty->assign('cause_list', $cause_select);
            $this->smarty->assign('form_act', 'inser_cause');
            return $this->smarty->display('back_cause_info.dwt');
        } /**
         * 添加退换货原因  by Leah
         *
         */
        elseif ($act == 'inser_cause') {
            $cause['cause_name'] = !empty($_REQUEST['cause_name']) ? $_REQUEST['cause_name'] : '';
            $cause['parent_id'] = !empty($_REQUEST['parent_id']) ? intval($_REQUEST['parent_id']) : 0;
            $cause['sort_order'] = !empty($_REQUEST['sort_order']) ? intval($_REQUEST['sort_order']) : 50;
            $cause['is_show'] = !empty($_REQUEST['is_show']) ? intval($_REQUEST['is_show']) : 0;

            if (cause_exists($cause['cause_name'], $cause['parent_id'])) {
                /* 同级别下不能有重复的分类名称 */
                $link[] = ['text' => lang('admin/common.go_back'), 'href' => 'javascript:history.back(-1)'];
                return sys_msg($GLOBALS['_LANG']['cause_repeat'], 0, $link);
            }

            $res = ReturnCause::insert($cause);
            if ($res > 0) {
                /* 添加链接 */
                $link[0]['text'] = lang('admin/common.continue_add');
                $link[0]['href'] = 'order.php?act=add_return_cause';

                $link[1]['text'] = lang('admin/common.back_list');
                $link[1]['href'] = 'order.php?act=back_cause_list';

                return sys_msg($GLOBALS['_LANG']['add_success'], 0, $link);
            }
        } /**
         * 编辑退换货原因
         * by  Leah
         */
        elseif ($act == 'edit_cause') {


            /* 检查权限 */
            admin_priv('order_back_cause');
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['edit_return_reason']);
            $this->smarty->assign('action_link', ['href' => 'order.php?act=back_cause_list', 'text' => $GLOBALS['_LANG']['return_cause_list']]);
            $c_id = !empty($_REQUEST['c_id']) ? intval($_REQUEST['c_id']) : 0;
            $cause_info = $this->orderManageService->causeInfo($c_id);

            $cause_list = cause_list(0, $cause_info['parent_id'], false);
            /* 简单处理缩进 */
            foreach ($cause_list as $k => $v) {
                if ($v['level']) {
                    $level = '';
                    for ($i = 0; $i < $v['level']; $i++) {
                        $level .= "&nbsp;&nbsp;&nbsp;&nbsp;";
                    }
                    $cause_list[$k]['name'] = $level . $v['name'];
                }
            }
            $this->smarty->assign('c_id', $c_id);
            $this->smarty->assign('cause_info', $cause_info);
            $this->smarty->assign('cause_list', $cause_list);
            $this->smarty->assign('form_act', 'edit_cause_info');
            return $this->smarty->display('back_cause_info.dwt');
        } /**
         * 修改
         * by leah
         */
        elseif ($act == 'edit_cause_info') {
            $c_id = !empty($_REQUEST['c_id']) ? $_REQUEST['c_id'] : 0;

            $cause['cause_name'] = !empty($_REQUEST['cause_name']) ? $_REQUEST['cause_name'] : '';
            $cause['parent_id'] = !empty($_REQUEST['parent_id']) ? intval($_REQUEST['parent_id']) : 0;
            $cause['sort_order'] = !empty($_REQUEST['sort_order']) ? intval($_REQUEST['sort_order']) : 50;
            $cause['is_show'] = !empty($_REQUEST['is_show']) ? intval($_REQUEST['is_show']) : 0;

            if (cause_exists($cause['cause_name'], $c_id)) {
                /* 同级别下不能有重复的分类名称 */
                $link[] = ['text' => lang('admin/common.go_back'), 'href' => 'javascript:history.back(-1)'];
                return sys_msg($GLOBALS['_LANG']['cause_repeat'], 0, $link);
            }
            if ($cause['parent_id'] == $c_id) {
                /* 不能将原因分类设置为自己 by wu */
                $link[] = ['text' => lang('admin/common.go_back'), 'href' => 'javascript:history.back(-1)'];
                return sys_msg($GLOBALS['_LANG']['cause_set_self'], 0, $link);
            }

            $res = ReturnCause::where('cause_id', $c_id)->update($cause);
            if ($res > 0) {
                /* 添加链接 */

                $link[0]['text'] = $GLOBALS['_LANG']['back_return_cause_list'];
                $link[0]['href'] = 'order.php?act=back_cause_list';

                return sys_msg($GLOBALS['_LANG']['edit_success'], 0, $link);
            }
        } /**
         * 删除退换货原因
         * by Leah
         */
        elseif ($act == 'remove_cause') {
            $c_id = $_REQUEST['id'];
            /* 删除退货单 */

            /* 查询是否存在子原因 by wu */
            $res = ReturnCause::where('parent_id', $c_id)->count();
            if ($res > 0) {
                return make_json_error($GLOBALS['_LANG']['cannot_delete']);
            }

            ReturnCause::where('cause_id', $c_id)->delete();

            $url = 'order.php?act=cause_query&' . str_replace('act=remove_cause', '', request()->server('QUERY_STRING'));

            return dsc_header("Location: $url\n");
        } /**
         * 退换货 搜索、排序、分页  by Leah
         */
        elseif ($act == 'cause_query') {
            $result = cause_list(0, 0, false);
            $this->smarty->assign('cause_list', $result);

            return make_json_result($this->smarty->fetch('back_cause_list.dwt'));
        }

        /*------------------------------------------------------ */
        //-- 搜索、排序、分页
        /*------------------------------------------------------ */
        elseif ($act == 'back_query') {
            /* 检查权限 */
            admin_priv('back_view');

            $result = $this->orderManageService->backList();

            $this->smarty->assign('back_list', $result['back']);
            $this->smarty->assign('filter', $result['filter']);
            $this->smarty->assign('record_count', $result['record_count']);
            $this->smarty->assign('page_count', $result['page_count']);

            $sort_flag = sort_flag($result['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);
            return make_json_result($this->smarty->fetch('back_list.dwt'), '', ['filter' => $result['filter'], 'page_count' => $result['page_count']]);
        }

        /*------------------------------------------------------ */
        //-- 退货单详细
        /*------------------------------------------------------ */
        elseif ($act == 'back_info') {
            /* 检查权限 */
            admin_priv('back_view');

            $back_id = intval(trim($_REQUEST['back_id']));

            /* 退货单详细 */
            if (!empty($back_id)) {
                $back_order = $this->orderManageService->backOrderInfo($back_id);
            } else {
                return 'order does not exist';
            }

            /* 如果管理员属于某个办事处，检查该订单是否也属于这个办事处 */
            $agency_id = AdminUser::where('user_id', session('admin_id'))->value('agency_id');
            $agency_id = $agency_id ? $agency_id : 0;
            if ($agency_id > 0) {
                if (!empty($back_order['agency_id']) && $back_order['agency_id'] != $agency_id) {
                    return sys_msg(lang('admin/common.priv_error'));
                }

                /* 取当前办事处信息*/
                $agency_name = Agency::where('agency_id', $agency_id)->value('agency_name');
                $back_order['agency_name'] = $agency_name ?? '';
            }

            /* 取得用户名 */
            if ($back_order['user_id'] > 0) {
                $user_info = Users::where('user_id', $back_order['user_id']);
                $user_info = BaseRepository::getToArrayFirst($user_info);

                if (!empty($user_info)) {
                    $back_order['user_name'] = $user_info['user_name'];

                    if (config('shop.show_mobile', 0) == 0) {
                        $back_order['user_name'] = $this->dscRepository->stringToStar($back_order['user_name']);
                        $back_order['mobile'] = $this->dscRepository->stringToStar($back_order['mobile']);
                    }
                }
            }

            /* 是否保价 */
            $order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;

            /* 取得发货单商品 */
            $res = BackGoods::where('back_id', $back_order['back_id']);
            $res = $res->with(['getGoods' => function ($query) {
                $query->select('goods_id', 'brand_id', 'goods_thumb');
            }]);
            $goods_list = BaseRepository::getToArrayGet($res);

            if (!empty($goods_list)) {
                foreach ($goods_list as $key => $row) {
                    $row['brand_id'] = '';
                    $row['goods_thumb'] = '';
                    if (isset($row['get_goods']) && !empty($row['get_goods'])) {
                        $row['brand_id'] = $row['get_goods']['brand_id'];
                        $row['goods_thumb'] = $row['get_goods']['goods_thumb'];
                    }

                    $brand = get_goods_brand_info($row['brand_id']);
                    $row['brand_name'] = $brand['brand_name'];

                    //图片显示
                    $row['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);

                    $goods_list[$key] = $row;
                }
            }
            /* 是否存在实体商品 */
            $exist_real_goods = 0;
            if ($goods_list) {
                foreach ($goods_list as $value) {
                    if ($value['is_real']) {
                        $exist_real_goods++;
                    }
                }
            }

            /* 模板赋值 */
            $this->smarty->assign('back_order', $back_order);
            if ($back_order['is_zc_order']) {
                $this->smarty->assign('exist_real_goods', true);
            } else {
                $this->smarty->assign('exist_real_goods', $exist_real_goods);
            }
            $this->smarty->assign('goods_list', $goods_list);
            $this->smarty->assign('back_id', $back_id); // 发货单id

            /* 显示模板 */
            $this->smarty->assign('ur_here', lang('admin/order.back_operate') . lang('admin/order.detail'));
            $this->smarty->assign('action_link', ['href' => 'order.php?act=back_list&' . list_link_postfix(), 'text' => lang('admin/common.10_back_order')]);

            return $this->smarty->display('back_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 修改退换金额 --zhuo
        /*------------------------------------------------------ */
        elseif ($act == 'edit_refound_amount') {
            /* 检查权限 */
            $check_auth = check_authz_json('order_edit');
            if ($check_auth !== true) {
                return $check_auth;
            }
            $type = empty($_REQUEST['type']) ? 0 : intval($_REQUEST['type']);
            $refound_amount = empty($_REQUEST['refound_amount']) ? 0 : floatval($_REQUEST['refound_amount']);
            $refound_pay_points = empty($_REQUEST['refound_pay_points']) ? 0 : floatval($_REQUEST['refound_pay_points']);
            $order_id = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
            $rec_id = empty($_REQUEST['rec_id']) ? 0 : intval($_REQUEST['rec_id']);
            $ret_id = empty($_REQUEST['ret_d']) ? 0 : intval($_REQUEST['ret_d']);

            if ($refound_amount < 0) {
                $data = [
                    'should_return' => 0, //订单金额
                    'refound_amount' => 0, //退款订单金额
                    'order_shipping_fee' => 0, //订单运费
                    'shipping_fee' => 0, //可退运费
                    'type' => $type
                ];
                return make_json_result($data);
            }

            $should_return = 0;
            $order_shipping_fee = 0;
            $shipping_fee = 0;

            if ($type == 1) {

                if ($ret_id > 0) {
                    $order_return = return_order_info($ret_id);        //退换货订单
                    $should_return = $order_return['pay_goods_amount'];
                    $should_return = $this->dscRepository->changeFloat($should_return);

                    if ($refound_pay_points > 0) {
                        if ($order_return['should_integral_money'] >= $refound_pay_points) {
                            $refound_pay_points = $order_return['should_integral_money'] - $refound_pay_points;
                        } else {
                            $refound_pay_points = $order_return['should_integral_money'];
                        }

                        /* 累加积分金额 */
                        $should_return = $should_return + $refound_pay_points;
                    }

                    if ($refound_amount > $should_return) {
                        $refound_amount = $should_return;
                    }

                    $should_return = $this->dscRepository->changeFloat($should_return);

                    $data = [
                        'should_return' => !empty($should_return) ? $should_return : 0, //订单金额
                        'refound_amount' => $refound_amount, //退款订单金额
                        'type' => $type
                    ];

                    return make_json_result($data);
                } else {

                    $order = order_info($order_id);

                    $paid_amount = $order['money_paid'] + $order['surplus'];
                    if ($paid_amount > 0 && $paid_amount >= $order['shipping_fee']) {
                        $paid_amount = $paid_amount - $order['shipping_fee'];
                    }

                    /**
                     * 整单退款
                     */
                    $should_return = $refound_amount;
                }

                if ($should_return > $paid_amount) {
                    $should_return = $paid_amount;
                }

                $data = [
                    'should_return' => !empty($should_return) ? $should_return : 0, //订单金额
                    'refound_amount' => $refound_amount, //退款订单金额
                    'type' => $type
                ];

                return make_json_result($data);
            } elseif ($type == 2) {
                $should_return = 0;

                if ($ret_id > 0) {
                    $order_return = return_order_info($ret_id);        //退换货订单
                    $order_shipping_fee = $order_return['pay_shipping_fee'] ?? 0;
                } else {
                    $order = order_info($order_id);
                    $order_shipping_fee = $order['shipping_fee'] ?? 0;
                }

                if ($refound_amount > $order_shipping_fee) {
                    $shipping_fee = $order_shipping_fee;
                } else {
                    $shipping_fee = $refound_amount;
                }

                $refound_amount = 0;
            }

            $should_return = number_format($should_return, 2, '.', '');

            $data = [
                'should_return' => !empty($should_return) ? $should_return : 0, //订单金额
                'refound_amount' => $refound_amount, //退款订单金额
                'order_shipping_fee' => $order_shipping_fee, //订单运费
                'shipping_fee' => $shipping_fee, //可退运费
                'type' => $type
            ];

            clear_cache_files();
            return make_json_result($data);
        }

        /*------------------------------------------------------ */
        //-- 修改订单退货退储值卡金额
        /*------------------------------------------------------ */
        elseif ($act == 'edit_refound_value_card') {
            /* 检查权限 */
            $check_auth = check_authz_json('order_edit');
            if ($check_auth !== true) {
                return $check_auth;
            }
            $vc_id = empty($_REQUEST['vc_id']) ? 0 : intval($_REQUEST['vc_id']);
            $order_id = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
            $ret_id = empty($_REQUEST['ret_id']) ? 0 : intval($_REQUEST['ret_id']);
            $refound_vcard = empty($_REQUEST['refound_vcard']) ? 0 : floatval($_REQUEST['refound_vcard']);

            if ($ret_id > 0) {
                $return_order = return_order_info($ret_id);
                $should_return = $return_order['pay_value_card'];
            } else {
                $order = order_info($order_id);
                $order_amount = $order['money_paid'] + $order['surplus'];

                if ($order_amount > 0 && $order_amount > $order['shipping_fee']) {
                    $order_amount = $order_amount - $order['shipping_fee'];
                } else {
                    $should_return = $order['total_fee'];
                }
            }

            $res = ValueCardRecord::where('vc_id', $vc_id)->where('order_id', $order_id)->select('vc_id', 'use_val');
            $value_card = BaseRepository::getToArrayFirst($res);

            $shipping_del = false;
            /* 如果快递费小于余额或者在线支付，退款金额减去快递费 */
            if ($order['surplus'] > $order['shipping_fee'] || $order['money_paid'] > $order['shipping_fee']) {
                $shipping_del = true;
            }

            if (!empty($value_card)) {
                /* 快递费从储值卡抵扣 */
                if ($value_card['use_val'] > $order['shipping_fee'] && $shipping_del === false) {
                    $value_card['use_val'] -= $order['shipping_fee'];
                }
            }

            if ($value_card) {
                if ($value_card['use_val'] > $should_return) {
                    $value_card['use_val'] = $should_return;
                }

                if ($refound_vcard > $value_card['use_val']) {
                    $refound_vcard = $value_card['use_val'];
                }
            }

            $data = [
                'refound_vcard' => !empty($refound_vcard) ? $refound_vcard : 0, //储值卡金额
            ];

            clear_cache_files();
            return make_json_result($data);
        }

        /*------------------------------------------------------ */
        //-- 修改订单（处理提交）
        /*------------------------------------------------------ */

        elseif ($act == 'step_post') {
            /* 检查权限 */
            admin_priv('order_edit');

            /* 取得参数 step */
            $step_list = ['user', 'edit_goods', 'add_goods', 'goods', 'consignee', 'shipping', 'payment', 'other', 'money', 'invoice'];
            $step = isset($_REQUEST['step']) && in_array($_REQUEST['step'], $step_list) ? $_REQUEST['step'] : 'user';

            /* 取得参数 order_id */
            $order_id = isset($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0;
            if ($order_id > 0) {
                $old_order = order_info($order_id);
            }

            /* 取得参数 step_act 添加还是编辑 */
            $step_act = isset($_REQUEST['step_act']) ? $_REQUEST['step_act'] : 'add';

            /* 插入订单信息 */
            if ('user' == $step) {
                /* 取得参数：user_id */
                $user_id = ($_POST['anonymous'] == 1) ? 0 : intval($_POST['user']);

                /* 插入新订单，状态为无效 */
                $order = [
                    'user_id' => $user_id,
                    'add_time' => TimeRepository::getGmTime(),
                    'order_status' => OS_INVALID,
                    'shipping_status' => SS_UNSHIPPED,
                    'pay_status' => PS_UNPAYED,
                    'from_ad' => 0,
                    'referer' => $GLOBALS['_LANG']['admin'],
                    'is_delete' => 2
                ];

                $order_id = 0;
                do {
                    $order['order_sn'] = get_order_sn();
                    $order_id = OrderInfo::insertGetId($order);
                    if ($order_id > 0) {
                        break;
                    } else {
                        if ($this->db->errno() != 1062) {
                            return $this->db->error();
                        }
                    }
                } while (true); // 防止订单号重复


                /* 记录日志 */
                admin_log($order['order_sn'], 'add', 'order');

                /* 记录log */
                $action_note = sprintf(lang('admin/order.add_order_info'), session('admin_name'));
                order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], $action_note, session('admin_name'));

                /* 插入 pay_log */
                $data = [
                    'order_id' => $order_id,
                    'order_amount' => 0,
                    'order_type' => PAY_ORDER,
                    'is_paid' => 0
                ];
                PayLog::insert($data);

                /* 下一步 */
                return dsc_header("Location: order.php?act=" . $step_act . "&order_id=" . $order_id . "&step=goods\n");
            } /* 编辑商品信息 */
            elseif ('edit_goods' == $step) {
                if (isset($_POST['rec_id'])) {
                    foreach ($_POST['rec_id'] as $key => $rec_id) {
                        $res = OrderGoods::where('rec_id', $rec_id);
                        $order_goods = BaseRepository::getToArrayFirst($res);

                        $goods_number_all = Goods::where('goods_id', $_POST['goods_id'][$key])
                            ->value('goods_number');
                        /* 取得参数 */
                        $goods_price = floatval($_POST['goods_price'][$key]);
                        $goods_number = intval($_POST['goods_number'][$key]);
                        $goods_attr = $_POST['goods_attr'][$key];
                        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id'][$key]) : '';
                        if ($product_id) {
                            $goods_number_all = Products::where('product_id', $_POST['product_id'][$key])
                                ->value('product_number');
                        }

                        $goods_number_all = $goods_number_all ? $goods_number_all : 0;

                        if ($goods_number_all >= $goods_number) {
                            /* 修改 */
                            $data = [
                                'goods_price' => $goods_price,
                                'goods_number' => $goods_number,
                                'goods_attr' => $goods_attr,
                                'warehouse_id' => $order_goods['warehouse_id'],
                                'area_id' => $order_goods['area_id']
                            ];
                            OrderGoods::where('rec_id', $rec_id)->update($data);
                        } else {
                            return sys_msg($GLOBALS['_LANG']['goods_num_err']);
                        }
                    }

                    /* 更新商品总金额和订单总金额 */
                    $goods_amount = order_amount($order_id);
                    update_order($order_id, ['goods_amount' => $goods_amount]);
                    $this->orderManageService->updateOrderAmount($order_id);
                    // 有主订单更新主订单总价
                    $parent_id = OrderInfo::where('order_id', $order_id)->value('main_order_id');
                    $parent_id = $parent_id ? $parent_id : 0;

                    if ($parent_id) {
                        $res = OrderInfo::where('main_order_id', $parent_id);
                        $order_ids = BaseRepository::getToArrayGet($res);

                        if ($order_ids) {
                            $main_goods_amount = 0;
                            foreach ($order_ids as $val) {
                                $main_goods_amount += order_amount($val['order_id']);
                            }
                        }
                        update_order($parent_id, ['goods_amount' => $main_goods_amount]);
                        $this->orderManageService->updateOrderAmount($parent_id);
                    }

                    /* 更新 pay_log */
                    $this->orderCommonService->updateOrderPayLog($order_id);

                    /* 记录日志 */
                    $sn = $old_order['order_sn'];
                    $new_order = order_info($order_id);
                    if ($old_order['total_fee'] != $new_order['total_fee']) {
                        $sn .= ',' . sprintf($GLOBALS['_LANG']['order_amount_change'], $old_order['total_fee'], $new_order['total_fee']);
                    }
                    admin_log($sn, 'edit', 'order');
                }

                /* 跳回订单商品 */
                return dsc_header("Location: order.php?act=" . $step_act . "&order_id=" . $order_id . "&step=goods\n");
            } /* 添加商品 */
            elseif ('add_goods' == $step) {
                /* 取得参数 */
                $goods_id = isset($_POST['goodslist']) && !empty($_POST['goodslist']) ? intval($_POST['goodslist']) : 0;
                $warehouse_id = isset($_POST['warehouse_id']) && !empty($_POST['warehouse_id']) ? intval($_POST['warehouse_id']) : 0;
                $area_id = isset($_POST['area_id']) && !empty($_POST['area_id']) ? intval($_POST['area_id']) : 0;
                $model_attr = isset($_POST['model_attr']) && !empty($_POST['model_attr']) ? intval($_POST['model_attr']) : 0;
                $attr_price = isset($_POST['attr_price']) && $_POST['attr_price'] < 0 ? floatval($_POST['attr_price']) : 0;

                $input_price = isset($_POST['input_price']) ? floatval($_POST['input_price']) : 0;
                $add_price = isset($_POST['add_price']) ? floatval($_POST['add_price']) : 0;

                $spec_count = isset($_POST['spec_count']) ? intval($_POST['spec_count']) : 0;

                $goods_price = $add_price != 'user_input' ? $add_price : $input_price;
                $goods_price = $goods_price + $attr_price;

                $goods_info = Goods::select('goods_id', 'goods_name', 'user_id', 'goods_sn', 'market_price', 'is_real', 'extension_code', 'freight', 'tid')
                    ->where('goods_id', $goods_id);
                $goods_info = BaseRepository::getToArrayFirst($goods_info);

                $goods_attr = '0';
                for ($i = 0; $i < $spec_count; $i++) {
                    if (isset($_POST['spec_' . $i]) && is_array($_POST['spec_' . $i])) {
                        $temp_array = $_POST['spec_' . $i];
                        $temp_array_count = count($_POST['spec_' . $i]);
                        for ($j = 0; $j < $temp_array_count; $j++) {
                            if ($temp_array[$j] !== null) {
                                $goods_attr .= ',' . $temp_array[$j];
                            }
                        }
                    } else {
                        if (isset($_POST['spec_' . $i]) && $_POST['spec_' . $i] !== null) {
                            $goods_attr .= ',' . $_POST['spec_' . $i];
                        }
                    }
                }
                $goods_number = isset($_POST['add_number']) && !empty($_POST['add_number']) ? $_POST['add_number'] : 0;
                $attr_list = $goods_attr;

                $goods_attr = explode(',', $goods_attr);
                $k = array_search(0, $goods_attr);
                unset($goods_attr[$k]);

                //ecmoban模板堂 --zhuo start
                $res = GoodsAttr::whereRaw(1);

                if ($model_attr == 1) {
                    $res = $res->with(['getGoodsWarehouseAttr' => function ($query) use ($warehouse_id) {
                        $query->where('warehouse_id', $warehouse_id);
                    }]);
                } elseif ($model_attr == 2) {
                    $res = $res->with(['getGoodsWarehouseAreaAttr' => function ($query) use ($area_id) {
                        $query->where('area_id', $area_id);
                    }]);
                }

                $goods_attr_value = '';
                $attr_value = [];

                if ($attr_list) {
                    $attr_list = BaseRepository::getExplode($attr_list);
                    $res = $res->whereIn('goods_attr_id', $attr_list);
                    $res = $res->orderBy('goods_attr_id');
                    $res = BaseRepository::getToArrayGet($res);

                    foreach ($res as $row) {
                        if ($model_attr == 1) {
                            $row['attr_price'] = '';
                            if (isset($row['get_goods_warehouse_attr']) && !empty($row['get_goods_warehouse_attr'])) {
                                $row['attr_price'] = $row['get_goods_warehouse_attr']['attr_price'];
                            }
                        } elseif ($model_attr == 2) {
                            $row['attr_price'] = '';
                            if (isset($row['get_goods_warehouse_area_attr']) && !empty($row['get_goods_warehouse_area_attr'])) {
                                $row['attr_price'] = $row['get_goods_warehouse_area_attr']['attr_price'];
                            }
                        } else {
                            $row['attr_price'] = $row['attr_price'];
                        }

                        $attr_price = '';
                        if ($row['attr_price'] > 0) {
                            $attr_price = ":[" . $this->dscRepository->getPriceFormat($row['attr_price']) . "]";
                        }
                        $attr_value[] = $row['attr_value'] . $attr_price;
                    }

                    if ($attr_value) {
                        $goods_attr_value = implode(",", $attr_value);
                    }
                }
                //ecmoban模板堂 --zhuo end

                //ecmoban模板堂 --zhuo start
                $res = Products::whereRaw(1);
                if ($model_attr == 1) {
                    $res = ProductsWarehouse::where('warehouse_id', $warehouse_id);
                } elseif ($model_attr == 2) {
                    $res = ProductsArea::where('area_id', $area_id);
                }
                $res = $res->where('goods_id', $goods_id);
                $prod = BaseRepository::getToArrayFirst($res);
                //ecmoban模板堂 --zhuo end

                if (is_spec($goods_attr) && !empty($prod)) {
                    $product_info = $this->goodsAttrService->getProductsInfo($goods_id, $goods_attr, $warehouse_id, $area_id); //ecmoban模板堂 --zhuo
                }

                //商品存在规格 是货品 检查该货品库存
                if (is_spec($goods_attr) && !empty($prod)) {
                    if (!empty($goods_attr)) {
                        /* 取规格的货品库存 */
                        if ($goods_number > $product_info['product_number']) {
                            $url = "order.php?act=" . $step_act . "&order_id=" . $order_id . "&step=goods";

                            echo '<a href="' . $url . '">' . $GLOBALS['_LANG']['goods_num_err'] . '</a>';


                            return false;
                        }
                    }
                }

                $other = [
                    'order_id' => $order_id,
                    'goods_id' => $goods_info['goods_id'],
                    'goods_name' => $goods_info['goods_name'],
                    'goods_sn' => $goods_info['goods_sn'],
                    'goods_number' => $goods_number,
                    'market_price' => $goods_info['market_price'],
                    'goods_price' => $goods_price,
                    'is_real' => $goods_info['is_real'],
                    'extension_code' => $goods_info['extension_code'],
                    'parent_id' => 0,
                    'is_gift' => 0,
                    'model_attr' => $model_attr,
                    'warehouse_id' => $warehouse_id,
                    'area_id' => $area_id,
                    'ru_id' => $goods_info['user_id'],
                    'freight' => $goods_info['freight'],
                    'tid' => $goods_info['tid']
                ];

                if ($goods_attr && is_spec($goods_attr) && !empty($prod)) {
                    /* 插入订单商品 */
                    $other['goods_attr'] = $goods_attr_value;
                    $other['product_id'] = $product_info['product_id'];
                    $other['goods_attr_id'] = implode(',', $goods_attr);
                }

                OrderGoods::insert($other);

                /* 如果使用库存，且下订单时减库存，则修改库存 */
                if (config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_PLACE) {
                    //ecmoban模板堂 --zhuo start
                    $model_inventory = get_table_date("goods", "goods_id = '$goods_id'", ['model_inventory'], 2);

                    //（货品）
                    if (!empty($product_info['product_id'])) {
                        if ($model_attr == 1) {
                            ProductsWarehouse::where('product_id', $product_info['product_id'])
                                ->decrement('product_number', $goods_number);
                        } elseif ($model_attr == 2) {
                            ProductsArea::where('product_id', $product_info['product_id'])
                                ->decrement('product_number', $goods_number);
                        } else {
                            Products::where('product_id', $product_info['product_id'])
                                ->decrement('product_number', $goods_number);
                        }
                    } else {
                        if ($model_inventory == 1) {
                            WarehouseGoods::where('goods_id', $goods_id)
                                ->where('region_id', $warehouse_id)
                                ->decrement('region_number', $goods_number);
                        } elseif ($model_inventory == 2) {
                            WarehouseAreaGoods::where('goods_id', $goods_id)
                                ->where('region_id', $area_id)
                                ->decrement('region_number', $goods_number);
                        } else {
                            Goods::where('goods_id', $goods_id)
                                ->decrement('goods_number', $goods_number);
                        }
                    }
                    //ecmoban模板堂 --zhuo end
                }

                /* 更新商品总金额和订单总金额 */
                update_order($order_id, ['goods_amount' => order_amount($order_id)]);
                $this->orderManageService->updateOrderAmount($order_id);
                // 有主订单更新主订单总价
                $parent_id = OrderInfo::where('order_id', $order_id)->value('main_order_id');
                $parent_id = $parent_id ? $parent_id : 0;
                if ($parent_id) {
                    $res = OrderInfo::where('main_order_id', $parent_id);
                    $order_ids = BaseRepository::getToArrayGet($res);
                    if ($order_ids) {
                        $main_goods_amount = 0;
                        foreach ($order_ids as $val) {
                            $main_goods_amount += order_amount($val['order_id']);
                        }
                    }
                    update_order($parent_id, ['goods_amount' => $main_goods_amount]);
                    $this->orderManageService->updateOrderAmount($parent_id);
                }

                /* 更新 pay_log */
                $this->orderCommonService->updateOrderPayLog($order_id);

                /* 记录日志 */
                $sn = $old_order['order_sn'];
                $new_order = order_info($order_id);
                if ($old_order['total_fee'] != $new_order['total_fee']) {
                    $sn .= ',' . sprintf($GLOBALS['_LANG']['order_amount_change'], $old_order['total_fee'], $new_order['total_fee']);
                }
                admin_log($sn, 'edit', 'order');

                /* 跳回订单商品 */
                return dsc_header("Location: order.php?act=" . $step_act . "&order_id=" . $order_id . "&step=goods\n");
            } /* 商品 */
            elseif ('goods' == $step) {
                /* 下一步 */
                if (isset($_POST['next'])) {
                    return dsc_header("Location: order.php?act=" . $step_act . "&order_id=" . $order_id . "&step=consignee\n");
                } /* 完成 */
                elseif (isset($_POST['finish'])) {
                    /* 初始化提示信息和链接 */
                    $msgs = [];
                    $links = [];

                    /* 如果已付款，检查金额是否变动，并执行相应操作 */
                    $order = order_info($order_id);
                    $this->orderManageService->handleOrderMoneyChange($order, $msgs, $links);

                    /* 显示提示信息 */
                    if (!empty($msgs)) {
                        return sys_msg(join(chr(13), $msgs), 0, $links);
                    } else {
                        /* 跳转到订单详情 */
                        return dsc_header("Location: order.php?act=info&order_id=" . $order_id . "\n");
                    }
                }
            } /* 保存收货人信息 */
            elseif ('consignee' == $step) {
                /* 保存订单 */
                $order = $_POST;

                $order['agency_id'] = 0;
                if (isset($order['country']) && !empty($order['country'])) {
                    $order['agency_id'] = get_agency_by_regions([$order['country'], $order['province'], $order['city'], $order['district']]);
                    update_order($order_id, $order);
                }

                /* 该订单所属办事处是否变化 */
                $agency_changed = $old_order['agency_id'] != $order['agency_id'];

                /* 记录日志 */
                $sn = $old_order['order_sn'];
                admin_log($sn, 'edit', 'order');

                if (isset($_POST['next'])) {
                    /* 下一步 */
                    if ($this->flowUserService->existRealGoods($order_id)) {
                        /* 存在实体商品，去配送方式 */
                        return dsc_header("Location: order.php?act=" . $step_act . "&order_id=" . $order_id . "&step=shipping\n");
                    } else {
                        /* 不存在实体商品，去支付方式 */
                        return dsc_header("Location: order.php?act=" . $step_act . "&order_id=" . $order_id . "&step=payment\n");
                    }
                } elseif (isset($_POST['finish'])) {
                    /* 如果是编辑且存在实体商品，检查收货人地区的改变是否影响原来选的配送 */
                    if ('edit' == $step_act && $this->flowUserService->existRealGoods($order_id)) {
                        $order = order_info($order_id);

                        $where = [
                            'order_id' => $order_id
                        ];
                        $shippinOrderInfo = $this->orderService->getOrderGoodsInfo($where);

                        /* 取得可用配送方式 */
                        $region_id_list = [
                            $order['country'], $order['province'], $order['city'], $order['district']
                        ];
                        $shipping_list = available_shipping_list($region_id_list, $shippinOrderInfo);
                        /* 判断订单的配送是否在可用配送之内 */
                        $exist = false;
                        foreach ($shipping_list as $shipping) {
                            if ($shipping['shipping_id'] == $order['shipping_id']) {
                                $exist = true;
                                break;
                            }
                        }

                        /* 如果不在可用配送之内，提示用户去修改配送 */
                        if (!$exist) {
                            // 原修改配送为空，配送费和保价费为0

                            // 修改配送为默认方式
                            $shipping_id = SellerShopinfo::where('ru_id', $order['ru_id'])->value('shipping_id');
                            $shipping_id = $shipping_id ?? 0;

                            $shipping = Shipping::where('shipping_id', $shipping_id)->select('shipping_name', 'shipping_code');
                            $shipping = BaseRepository::getToArrayFirst($shipping);

                            update_order($order_id, ['shipping_id' => $shipping_id, 'shipping_name' => $shipping['shipping_name'] ?? '', 'shipping_code' => $shipping['shipping_code'] ?? '']);
                            $links[] = ['text' => $GLOBALS['_LANG']['step']['shipping'], 'href' => 'order.php?act=edit&order_id=' . $order_id . '&step=shipping'];
                            return sys_msg($GLOBALS['_LANG']['continue_shipping'], 1, $links);
                        }
                    }

                    /* 完成 */
                    if ($agency_changed) {
                        return dsc_header("Location: order.php?act=list\n");
                    } else {
                        return dsc_header("Location: order.php?act=info&order_id=" . $order_id . "\n");
                    }
                }
            } /* 保存配送信息 */
            elseif ('shipping' == $step) {
                /* 如果不存在实体商品，退出 */
                if (!$old_order['is_zc_order']) {
                    if (!$this->flowUserService->existRealGoods($order_id)) {
                        return sys_msg('Hacking attempt', 1);
                    }
                }

                /* 取得订单信息 */
                $order_info = order_info($order_id);

                /* 保存订单 */
                $shipping_id = isset($_POST['shipping']) && !empty($_POST['shipping']) ? intval($_POST['shipping']) : 0;
                $shipping = shipping_info($shipping_id);
                $shipping_name = $shipping['shipping_name'] ?? '';
                $shipping_code = $shipping['shipping_code'] ?? '';

                $order = [
                    'shipping_id' => $shipping_id,
                    'shipping_name' => $shipping_name ? addslashes($shipping_name) : '',
                    'shipping_code' => $shipping_code
                ];

                update_order($order_id, $order);

                /* 清除首页缓存：发货单查询 */
                clear_cache_files('index.dwt');

                if (isset($_POST['next'])) {
                    /* 下一步 */
                    return dsc_header("Location: order.php?act=" . $step_act . "&order_id=" . $order_id . "&step=payment\n");
                } elseif (isset($_POST['finish'])) {
                    /* 初始化提示信息和链接 */
                    $msgs = [];
                    $links = [];

                    /* 如果已付款，检查金额是否变动，并执行相应操作 */
                    $order = order_info($order_id);

                    /* 如果是编辑且配送不支持货到付款且原支付方式是货到付款 */
                    if ('edit' == $step_act && $shipping['support_cod'] == 0) {
                        $payment = payment_info($order['pay_id']);
                        if ($payment['is_cod'] == 1) {
                            /* 修改支付为空 */
                            update_order($order_id, ['pay_id' => 0, 'pay_name' => '']);
                            $msgs[] = $GLOBALS['_LANG']['continue_payment'];
                            $links[] = ['text' => $GLOBALS['_LANG']['step']['payment'], 'href' => 'order.php?act=' . $step_act . '&order_id=' . $order_id . '&step=payment'];
                        }
                    }

                    /* 显示提示信息 */
                    if (!empty($msgs)) {
                        return sys_msg(join(chr(13), $msgs), 0, $links);
                    } else {
                        if ($shipping_id != $order['shipping_id']) {
                            $order_note = sprintf($GLOBALS['_LANG']['update_shipping'], $order['shipping_name'], $shipping_name);
                            order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], $order_note, session('admin_name'), 0, TimeRepository::getGmTime());
                        }

                        /* 完成 */
                        return dsc_header("Location: order.php?act=info&order_id=" . $order_id . "\n");
                    }
                }
            } /* 保存支付信息 */
            elseif ('payment' == $step) {
                /* 取得支付信息 */
                $pay_id = $_POST['payment'];
                $payment = payment_info($pay_id);

                /* 计算支付费用 */
                $order_amount = order_amount($order_id);
                if ($payment['is_cod'] == 1) {
                    $order = order_info($order_id);
                    $region_id_list = [
                        $order['country'], $order['province'], $order['city'], $order['district']
                    ];
                    $shipping = shipping_info($order['shipping_id']);
                    $shipping['pay_fee'] = isset($shipping['pay_fee']) ? $shipping['pay_fee'] : 0;
                    $pay_fee = pay_fee($pay_id, $order_amount, $shipping['pay_fee']);
                } else {
                    $pay_fee = pay_fee($pay_id, $order_amount);
                }

                // 记录修改支付方式
                $orderInfo = OrderInfo::select('order_sn', 'order_status', 'shipping_status', 'pay_status', 'pay_name')->where('order_id', $order_id);
                $orderInfo = BaseRepository::getToArrayFirst($orderInfo);

                $this->orderCommonService->orderAction($orderInfo['order_sn'], $orderInfo['order_status'], $orderInfo['shipping_status'], $orderInfo['pay_status'], sprintf(lang('order.edit_pay_status'), $orderInfo['pay_name']), session('admin_name'));

                /* 保存订单 */
                $order = [
                    'pay_id' => $pay_id,
                    'pay_name' => addslashes($payment['pay_name']),
                    'pay_fee' => $pay_fee
                ];
                update_order($order_id, $order);
                $this->orderManageService->updateOrderAmount($order_id);

                /* 更新 pay_log */
                $this->orderCommonService->updateOrderPayLog($order_id);

                /* 记录日志 */
                $sn = $old_order['order_sn'];
                $new_order = order_info($order_id);
                if ($old_order['total_fee'] != $new_order['total_fee']) {
                    $sn .= ',' . sprintf($GLOBALS['_LANG']['order_amount_change'], $old_order['total_fee'], $new_order['total_fee']);
                }
                admin_log($sn, 'edit', 'order');

                if (isset($_POST['next'])) {
                    /* 下一步 */
                    return dsc_header("Location: order.php?act=" . $step_act . "&order_id=" . $order_id . "&step=other\n");
                } elseif (isset($_POST['finish'])) {
                    /* 初始化提示信息和链接 */
                    $msgs = [];
                    $links = [];

                    /* 如果已付款，检查金额是否变动，并执行相应操作 */
                    $order = order_info($order_id);
                    $this->orderManageService->handleOrderMoneyChange($order, $msgs, $links);

                    /* 显示提示信息 */
                    if (!empty($msgs)) {
                        return sys_msg(join(chr(13), $msgs), 0, $links);
                    } else {
                        /* 完成 */
                        return dsc_header("Location: order.php?act=info&order_id=" . $order_id . "\n");
                    }
                }
            } elseif ('other' == $step) {
                /* 保存订单 */
                $order = [];
                if (isset($_POST['pack']) && $_POST['pack'] > 0) {
                    $pack = pack_info($_POST['pack']);
                    $order['pack_id'] = $pack['pack_id'];
                    $order['pack_name'] = addslashes($pack['pack_name']);
                    $order['pack_fee'] = $pack['pack_fee'];
                } else {
                    $order['pack_id'] = 0;
                    $order['pack_name'] = '';
                    $order['pack_fee'] = 0;
                }
                if (isset($_POST['card']) && $_POST['card'] > 0) {
                    $card = card_info($_POST['card']);
                    $order['card_id'] = $card['card_id'];
                    $order['card_name'] = addslashes($card['card_name']);
                    $order['card_fee'] = $card['card_fee'];
                    $order['card_message'] = $_POST['card_message'];
                } else {
                    $order['card_id'] = 0;
                    $order['card_name'] = '';
                    $order['card_fee'] = 0;
                    $order['card_message'] = '';
                }
                $order['inv_type'] = $_POST['inv_type'] ?? 0;
                $order['inv_payee'] = $_POST['inv_payee'] ?? '';
                $order['inv_content'] = $_POST['inv_content'] ?? '';
                $order['how_oos'] = $_POST['how_oos'] ?? '';
                $order['postscript'] = $_POST['postscript'] ?? '';
                $order['to_buyer'] = $_POST['to_buyer'] ?? '';

                update_order($order_id, $order);

                $this->orderManageService->updateOrderAmount($order_id);

                /* 更新 pay_log */
                $this->orderCommonService->updateOrderPayLog($order_id);

                /* 记录日志 */
                $sn = $old_order['order_sn'];
                admin_log($sn, 'edit', 'order');

                if (isset($_POST['next'])) {
                    /* 下一步 */
                    return dsc_header("Location: order.php?act=" . $step_act . "&order_id=" . $order_id . "&step=money\n");
                } elseif (isset($_POST['finish'])) {
                    /* 完成 */
                    return dsc_header("Location: order.php?act=info&order_id=" . $order_id . "\n");
                }
            } elseif ('money' == $step) {

                /* 取得订单信息 */
                $old_order = order_info($order_id);

                if (empty($old_order)) {
                    return dsc_header("Location: order.php?act=list" . "\n");
                }

                if ($old_order['user_id'] > 0) {
                    /* 取得用户信息 */
                    $user_info = Users::where('user_id', $old_order['user_id']);
                    $user_info = BaseRepository::getToArrayFirst($user_info);
                }

                /* 保存信息 */
                $order['goods_amount'] = $old_order['goods_amount'];
                $order['discount'] = isset($_POST['discount']) && floatval($_POST['discount']) >= 0 ? round(floatval($_POST['discount']), 2) : 0;
                $order['tax'] = isset($_POST['tax']) ? round(floatval($_POST['tax']), 2) : 0;
                $order['shipping_fee'] = isset($_POST['shipping_fee']) && floatval($_POST['shipping_fee']) >= 0 ? round(floatval($_POST['shipping_fee']), 2) : 0;
                $order['insure_fee'] = isset($_POST['insure_fee']) && floatval($_POST['insure_fee']) >= 0 ? round(floatval($_POST['insure_fee']), 2) : 0;
                $order['pay_fee'] = isset($_POST['pay_fee']) && floatval($_POST['pay_fee']) >= 0 ? round(floatval($_POST['pay_fee']), 2) : 0;
                $order['pack_fee'] = isset($_POST['pack_fee']) && floatval($_POST['pack_fee']) >= 0 ? round(floatval($_POST['pack_fee']), 2) : 0;
                $order['card_fee'] = isset($_POST['card_fee']) && floatval($_POST['card_fee']) >= 0 ? round(floatval($_POST['card_fee']), 2) : 0;
                $order['coupons'] = isset($_POST['coupons']) && floatval($_POST['coupons']) >= 0 ? round(floatval($_POST['coupons']), 2) : 0;

                $order['money_paid'] = $old_order['money_paid'];
                $order['surplus'] = 0;
                $order['integral'] = isset($_POST['integral']) && intval($_POST['integral']) >= 0 ? intval($_POST['integral']) : 0;
                $order['integral_money'] = 0;
                $order['bonus'] = isset($_POST['bonus']) && floatval($_POST['bonus']) >= 0 ? round(floatval($_POST['bonus']), 2) : 0;
                $order['bonus_id'] = 0;
                $order['bonus'] = isset($_POST['bonus']) && floatval($_POST['bonus']) >= 0 ? round(floatval($_POST['bonus']), 2) : 0;
                $_POST['bonus_id'] = isset($_POST['bonus_id']) && !empty($_POST['bonus_id']) ? intval($_POST['bonus_id']) : 0;

                /* 计算待付款金额 */
                $order['order_amount'] = $order['goods_amount']
                    + $order['tax']
                    + $order['shipping_fee']
                    + $order['insure_fee']
                    + $order['pay_fee']
                    + $order['pack_fee']
                    + $order['card_fee']
                    - $order['discount'];

                $money_paid = 0;
                $order_amount = 0;
                if ($order['order_amount'] > 0) { //0
                    $is_coupons = 0;
                    /* 检测优惠券金额是否大于待付款金额 start */
                    if ($order['coupons'] > $order['order_amount']) {
                        $order['coupons'] = $order['order_amount'];

                        $is_coupons = 1;
                    }
                    /* 检测优惠券金额是否大于待付款金额 end */

                    $order['order_amount'] = $order['order_amount'] - ($order['coupons'] + $old_order['use_val']);
                    $order_amount = $order['order_amount'];

                    if ($order['order_amount'] > 0) { //3
                        $order['order_amount'] -= $order['money_paid'];

                        if ($order['order_amount'] > 0) { //2

                            //1
                            if ($old_order['user_id'] > 0) {
                                /* 如果选择了红包，先使用红包支付 */
                                if ($_POST['bonus_id'] > 0 && !isset($_POST['bonus'])) {
                                    /* 检查红包是否可用 */
                                    $order['bonus_id'] = $_POST['bonus_id'];
                                    $bonus = bonus_info($_POST['bonus_id']);
                                    $order['bonus'] = $bonus['type_money'];

                                    $order['order_amount'] -= $order['bonus'];
                                }

                                /* 使用红包之后待付款金额仍大于0 */
                                if ($order['order_amount'] > 0) {
                                    if ($old_order['extension_code'] != 'exchange_goods') {
                                        /* 如果设置了积分，再使用积分支付 */
                                        if (isset($_POST['integral']) && intval($_POST['integral']) > 0) {
                                            /* 检查积分是否足够 */
                                            $order['integral'] = intval($_POST['integral']);
                                            $order['integral_money'] = $this->dscRepository->valueOfIntegral($order['integral']);
                                            if ($old_order['integral'] + $user_info['pay_points'] < $order['integral']) {
                                                return sys_msg($GLOBALS['_LANG']['pay_points_not_enough']);
                                            }

                                            $order['order_amount'] -= $order['integral_money'];
                                        }
                                    } else {
                                        if (intval($_POST['integral']) > $user_info['pay_points'] + $old_order['integral']) {
                                            return sys_msg($GLOBALS['_LANG']['pay_points_not_enough']);
                                        }
                                    }
                                    if ($order['order_amount'] > 0) {
                                        /* 如果设置了余额，再使用余额支付 */
                                        if (isset($_POST['surplus']) && floatval($_POST['surplus']) >= 0) {
                                            /* 检查余额是否足够 */
                                            $order['surplus'] = round(floatval($_POST['surplus']), 2);
                                            if ($old_order['surplus'] + $user_info['user_money'] + $user_info['credit_line'] < $order['surplus']) {
                                                return sys_msg($GLOBALS['_LANG']['user_money_not_enough']);
                                            }

                                            /* 如果红包和积分和余额足以支付，把待付款金额改为0，退回部分积分余额 */
                                            $order['order_amount'] -= $order['surplus'];
                                            if ($order['order_amount'] < 0) {
                                                $order['surplus'] += $order['order_amount'];
                                                $order['order_amount'] = 0;
                                            }
                                        }
                                    } else {
                                        /* 如果红包和积分足以支付，把待付款金额改为0，退回部分积分 */
                                        $order['integral_money'] += $order['order_amount'];
                                        $order['integral'] = $this->dscRepository->integralOfValue($order['integral_money']);
                                        $order['order_amount'] = 0;
                                    }
                                } else {
                                    /* 如果红包足以支付，把待付款金额设为0 */
                                    $order['order_amount'] = 0;
                                }
                            }

                            $return_type = 1;
                        } else {
                            if ($order['money_paid'] > 0) {
                                $money_paid = $order_amount - $old_order['integral_money'];
                                $order_amount = $order['money_paid'] - $money_paid;

                                if ($order_amount >= 0) {
                                    $order_amount += $old_order['surplus'];
                                    $order['surplus'] = 0;
                                } else {
                                    $order['surplus'] = $old_order['surplus'];
                                }

                                $order['integral'] = $old_order['integral'];
                                $order['integral_money'] = $old_order['integral_money'];
                            } else {
                                $order['coupons'] = $old_order['coupons'];
                            }

                            $return_type = 2;
                        }
                    } else {
                        if ($is_coupons == 1) {
                            $order['order_amount'] = (-1) * ($old_order['surplus'] + $old_order['money_paid']);
                            $order['surplus'] = 0;
                            $order['money_paid'] = 0;
                            $order['integral'] = 0;
                            $order['integral_money'] = 0;
                        } else {
                            $order['order_amount'] = (-1) * ($old_order['surplus'] + $old_order['money_paid'] + $order['order_amount']);
                            $order['surplus'] = $order['surplus'] + $order['order_amount'];

                            if ($order['coupons'] > 0 && $order['coupons'] < $old_order['coupons']) {
                                $order['coupons'] = $old_order['coupons'] - $order['coupons'];
                            }

                            $order['integral_money'] = $old_order['integral_money'];
                        }

                        $return_type = 3;
                    }
                } else {
                    $return_type = 0;
                }

                if ($order['order_amount'] <= 0) {
                    if (($old_order['surplus'] + $old_order['money_paid']) > 0) {
                        if ($return_type == 1) {
                            if ($old_order['surplus'] - $order['surplus'] > 0) {
                                $order['order_amount'] = (-1) * ($old_order['surplus'] - $order['surplus']);
                            } else {
                                $order['order_amount'] = 0;
                            }
                        } elseif ($return_type == 2) {
                            if ($order_amount > 0) {
                                $order['order_amount'] = (-1) * $order_amount;
                            } else {
                                $order['order_amount'] = 0;
                            }

                            $order['money_paid'] = $money_paid;
                            $order['bonus'] = $old_order['bonus'];
                        } elseif ($return_type == 3) {
                            $order['bonus'] = $old_order['bonus'];
                        } else {
                            $order['order_amount'] = (-1) * ($old_order['surplus'] + $old_order['money_paid'] - $old_order['coupons'] - $old_order['use_val'] - $old_order['integral_money'] - $old_order['bonus']);
                            $order['surplus'] = 0;
                            $order['money_paid'] = 0;

                            $order['coupons'] = 0;
                            $order['bonus'] = 0;
                            $order['integral'] = 0;
                            $order['integral_money'] = 0;
                        }
                    } else {
                        if ($order['integral_money'] <= 0) {
                            $order['integral'] = 0;
                        }
                    }
                }

                if ($order['bonus_id'] == 0) {
                    $order['bonus'] = 0;
                }

                if ($order['order_amount'] == 0) {
                    $order['order_amount'] = 0;

                    $order['order_status'] = OS_CONFIRMED;
                    $order['shipping_status'] = !empty($old_order['shipping_status']) ? $old_order['shipping_status'] : SS_UNSHIPPED;
                    $order['pay_status'] = PS_PAYED;
                    $order['is_delete'] = 0;
                }

                $order_amount = $order['goods_amount'] + $order['tax'] + $order['shipping_fee'] + $order['insure_fee'] + $order['pay_fee'] + $order['pack_fee'] + $order['card_fee'];

                $activity_amount = ($order['money_paid']) + $order['surplus'] + $order['coupons'] + $order['integral_money'] + $old_order['use_val'] + $old_order['discount'] + $order['bonus'];

                if ($activity_amount > $order_amount && ($order['bonus'] > 0 && $order['bonus'] > $order['order_amount'] && $order['order_amount'] < 0)) {
                    $order['bonus'] = $order['bonus'] + $order['order_amount'];
                }

                $stages_qishu = $old_order['get_order_goods']['stages_qishu'] ?? 0;
                if ($stages_qishu > 0) {
                    $stagesInfo = Stages::where('order_sn', $old_order['order_sn']);
                    $stagesInfo = BaseRepository::getToArrayGet($stagesInfo);

                    //获取该白条分期订单的总价;
                    $shop_price_total = $order['order_amount'];

                    if ($stages_qishu == 1) {
                        $stages_one_price = $shop_price_total;
                    } else {
                        //计算每期价格(每期价格=总价*费率+总价/期数);
                        $stages_one_price = round(($shop_price_total * ($stagesInfo['stages_rate'] / 100)) + ($shop_price_total / $stages_qishu), 2);
                    }

                    Stages::where('order_sn', $old_order['order_sn'])
                        ->where('yes_num', 0)
                        ->update([
                            'stages_one_price' => $stages_one_price
                        ]);

                    BaitiaoLog::where('order_id', $old_order['order_id'])
                        ->where('is_repay', 0)
                        ->update([
                            'stages_one_price' => $stages_one_price
                        ]);
                }

                $is_update = update_order($order_id, $order);

                if ($is_update) {
                    $this->orderManageService->updateOrderMoneyLog($old_order, $order, session('admin_name'));
                }

                /* 更新 pay_log */
                $this->orderCommonService->updateOrderPayLog($order_id);

                /* 如果余额、积分、红包有变化，做相应更新 */
                if ($old_order['user_id'] > 0) {
                    $user_money_change = $old_order['surplus'] - $order['surplus'];
                    if ($user_money_change != 0) {
                        log_account_change($user_info['user_id'], $user_money_change, 0, 0, 0, sprintf($GLOBALS['_LANG']['change_use_surplus'], $old_order['order_sn']));
                    }

                    $pay_points_change = $old_order['integral'] - $order['integral'];
                    if ($pay_points_change != 0) {
                        log_account_change($user_info['user_id'], 0, 0, 0, $pay_points_change, sprintf($GLOBALS['_LANG']['change_use_integral'], $old_order['order_sn']));
                    }

                    if ($old_order['bonus_id'] != $order['bonus_id']) {
                        if ($old_order['bonus_id'] > 0) {
                            $data = ['used_time' => 0, 'order_id' => 0];
                            UserBonus::where('bonus_id', $old_order['bonus_id'])->update($data);
                        }

                        if ($order['bonus_id'] > 0) {
                            $data = ['used_time' => TimeRepository::getGmTime(), 'order_id' => $order_id];
                            UserBonus::where('bonus_id', $order['bonus_id'])->update($data);
                        }
                    }
                }

                if (isset($_POST['finish'])) {
                    /* 完成 */
                    if ($step_act == 'add') {
                        /* 订单改为已确认，（已付款） */
                        $arr['order_status'] = OS_CONFIRMED;
                        $arr['confirm_time'] = TimeRepository::getGmTime();
                        $arr['is_delete'] = 0;
                        if ($order['order_amount'] <= 0) {
                            $arr['pay_status'] = PS_PAYED;
                            $arr['pay_time'] = TimeRepository::getGmTime();
                        }
                        update_order($order_id, $arr);
                    }

                    /* 初始化提示信息和链接 */
                    $msgs = [];
                    $links = [];

                    /* 如果已付款，检查金额是否变动，并执行相应操作 */
                    $order = order_info($order_id);
                    $this->handle_order_money_change($order, $msgs, $links);

                    if ($step_act == 'add') {
                        /* 记录log */
                        $action_note = sprintf(lang('admin/order.add_order_info'), session('admin_name'));
                        order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], $action_note, session('admin_name'));

                        /* 如果使用库存，且下订单时减库存，则减少库存 */
                        if (config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_PLACE) {
                            change_order_goods_storage($order_id, true, SDT_PLACE, config('shop.stock_dec_time'), session('admin_id'));
                        }

                        /* 如果使用库存，且付款时减库存，且订单金额为0，则减少库存 */
                        if (config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_PAID && $order['order_amount'] <= 0) {
                            change_order_goods_storage($order_id, true, SDT_PAID, 15, session('admin_id'));
                        }
                    }

                    /* 显示提示信息 */
                    if (!empty($msgs)) {
                        return sys_msg(join(chr(13), $msgs), 0, $links);
                    } else {
                        return dsc_header("Location: order.php?act=info&order_id=" . $order_id . "\n");
                    }
                }
            } /* 保存发货后的配送方式和发货单号 */
            elseif ('invoice' == $step) {
                /* 如果不存在实体商品，退出 */
                if (!$old_order['is_zc_order']) {
                    if (!$this->flowUserService->existRealGoods($order_id)) {
                        return sys_msg('Hacking attempt', 1);
                    }
                }

                /* 保存订单 */
                $shipping_id = intval($_POST['shipping']);
                $shipping = shipping_info($shipping_id);
                $invoice_no = trim($_POST['invoice_no']);
                $invoice_no = str_replace(',', ',', $invoice_no);
                $order = [
                    'shipping_id' => $shipping_id,
                    'shipping_name' => addslashes($shipping['shipping_name']),
                    'invoice_no' => $invoice_no
                ];
                update_order($order_id, $order);

                /* 记录日志 */
                $sn = $old_order['order_sn'];
                admin_log($sn, 'edit', 'order');

                if (isset($_POST['finish'])) {
                    return dsc_header("Location: order.php?act=info&order_id=" . $order_id . "\n");
                }
            }
        } /**
         * 修改退换货订单  by Leah
         */
        elseif ($act == 'return_edit') {
            load_helper('transaction');
            /* 检查权限 */
            admin_priv('order_edit');
            $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
            $ret_id = isset($_GET['ret_id']) ? intval($_GET['ret_id']) : 0;


            /* 取得参数 step */
            $step_list = ['user', 'goods', 'consignee', 'back_shipping', 'payment', 'other', 'money'];
            $step = isset($_GET['step']) && in_array($_GET['step'], $step_list) ? $_GET['step'] : 'user';
            $this->smarty->assign('step', $step);

            /* 取得参数 act */
            $act = $_GET['act'];
            $this->smarty->assign('ur_here', lang('admin/order.add_order'));
            $this->smarty->assign('step_act', $act);
            $this->smarty->assign('step', $act);

            $order = order_info($order_id);

            /* 取得订单信息 */
            if ($order_id > 0) {
                $return = get_return_detail($ret_id);
                $this->smarty->assign('return', $return);
                $this->smarty->assign('order', $order);
            }
            // 选择配送方式
            if ('back_shipping' == $step) {
                $where = [
                    'order_id' => $order_id
                ];
                $shippinOrderInfo = $this->orderService->getOrderGoodsInfo($where);

                /* 取得可用的配送方式列表 */
                $region_id_list = [
                    $order['country'], $order['province'], $order['city'], $order['district']
                ];
                $shipping_list = available_shipping_list($region_id_list, $shippinOrderInfo);

                /* 取得配送费用 */
                $total = order_weight_price($order_id);
                foreach ($shipping_list as $key => $shipping) {
                    $shipping_fee = $this->dscRepository->shippingFee($shipping['shipping_code'], unserialize($shipping['configure']), $total['weight'], $total['amount'], $total['number']); //计算运费
                    $free_price = free_price($shipping['configure']);   //免费额度
                    $shipping_list[$key]['shipping_fee'] = $shipping_fee;
                    $shipping_list[$key]['format_shipping_fee'] = $this->dscRepository->getPriceFormat($shipping_fee);
                    $shipping_list[$key]['free_money'] = $this->dscRepository->getPriceFormat($free_price['configure']['free_money']);
                }
                $this->smarty->assign('shipping_list', $shipping_list);
            }

            /* 显示模板 */

            return $this->smarty->display('order_step.dwt');
        } /**
         * 修改退换货订单快递信息
         * by leah
         */
        elseif ($act == 'edit_shipping') {
            $order_id = isset($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0;
            $ret_id = isset($_REQUEST['ret_id']) ? intval($_REQUEST['ret_id']) : 0;
            $rec_id = isset($_REQUEST['rec_id']) ? intval($_REQUEST['rec_id']) : 0;

            $shipping_id = isset($_REQUEST['shipping']) ? intval($_REQUEST['shipping']) : 0;
            $invoice_no = isset($_REQUEST['invoice_no']) ? $_REQUEST['invoice_no'] : '';


            $data = [
                'out_shipping_name' => $shipping_id,
                'out_invoice_no' => $invoice_no
            ];
            OrderReturn::where('ret_id', $ret_id)->update($data);

            $links[] = ['text' => lang('admin/order.return_sn') . lang('admin/order.detail'), 'href' => 'order.php?act=return_info&ret_id=' . $ret_id . '&rec_id=' . $rec_id];
            return sys_msg(lang('admin/order.act_ok'), 0, $links);
        }

        /*------------------------------------------------------ */
        //-- 修改订单（载入页面）
        /*------------------------------------------------------ */

        elseif ($act == 'add' || $act == 'edit') {
            /* 检查权限 */
            admin_priv('order_edit');

            /* 取得参数 order_id */
            $order_id = intval(request()->input('order_id', 0));

            /* 取得参数 step */
            $step_list = ['user', 'goods', 'consignee', 'shipping', 'payment', 'other', 'money'];
            $step = e(request()->input('step', 'user'));
            $step = isset($step) && in_array($step, $step_list) ? $step : 'user';

            $warehouse_list = get_warehouse_list_goods();
            $this->smarty->assign('warehouse_list', $warehouse_list); //仓库列表

            /* 取得参数 act */
            $act = e(request()->input('act', ''));

            if ($act == 'add') {
                $this->smarty->assign('ur_here', lang('admin/order.add_order'));
            } else {
                $this->smarty->assign('ur_here', $GLOBALS['_LANG']['edit_order']);
            }

            $this->smarty->assign('step_act', $act);

            /* 取得订单信息 */
            if ($order_id > 0) {
                $order = order_info($order_id);

                /* 发货单格式化 */
                $order['invoice_no'] = str_replace('<br>', ',', $order['invoice_no']);

                /* 如果已发货，就不能修改订单了（配送方式和发货单号除外） */
                if ($order['shipping_status'] == SS_SHIPPED || $order['shipping_status'] == SS_RECEIVED) {
                    if ($step != 'shipping') {
                        return sys_msg($GLOBALS['_LANG']['cannot_edit_order_shipped'], 1);
                    } else {
                        if ($order['invoice_no']) {
                            return sys_msg($GLOBALS['_LANG']['cannot_edit_order_shipped'], 1);
                        }

                        $step = 'invoice';
                    }
                }
            } else {
                if ($act != 'add' || $step != 'user') {
                    return sys_msg('invalid params', 1);
                }
            }

            $this->smarty->assign('order_id', $order_id);
            $this->smarty->assign('step', $step);
            $this->smarty->assign('order', $order ?? []);

            /* 选择会员 */
            if ('user' == $step) {
                // 无操作
            } /* 增删改商品 */
            elseif ('goods' == $step) {
                /* 取得订单商品 */
                $goods_list = order_goods($order_id);
                if (!empty($goods_list)) {
                    foreach ($goods_list as $key => $goods) {
                        /* 计算属性数 */
                        $attr = $goods['goods_attr'];
                        if ($attr == '') {
                            $goods_list[$key]['rows'] = 1;
                        } else {
                            $goods_list[$key]['rows'] = count(explode(chr(13), $attr));
                        }
                    }
                }

                $this->smarty->assign('goods_list', $goods_list);

                /* 取得商品总金额 */
                $this->smarty->assign('goods_amount', order_amount($order_id));
            } // 设置收货人
            elseif ('consignee' == $step) {
                /* 查询是否存在实体商品 */
                $exist_real_goods = $this->flowUserService->existRealGoods($order_id);
                if ($order['is_zc_order']) {
                    $this->smarty->assign('exist_real_goods', true);
                } else {
                    $this->smarty->assign('exist_real_goods', $exist_real_goods);
                }

                /* 取得收货地址列表 */
                if ($order['user_id'] > 0) {
                    $this->smarty->assign('address_list', address_list($order['user_id']));

                    $address_id = isset($_REQUEST['address_id']) ? intval($_REQUEST['address_id']) : 0;
                    /* 获得用户收货人信息 */
                    $consignee = $this->orderManageService->getConsigneeLog($address_id, $order['user_id']);

                    $country_list = $this->orderManageService->getRegionsLog(0, 0);

                    if (!empty($consignee)) {
                        $province_list = $this->orderManageService->getRegionsLog(1, $consignee['country']);
                        $city_list = $this->orderManageService->getRegionsLog(2, $consignee['province']);
                        $district_list = $this->orderManageService->getRegionsLog(3, $consignee['city']);
                    }
                    if ($address_id > 0) {
                        $address = address_info($address_id);
                        if ($address) {
                            $order['consignee'] = $address['consignee'];
                            $order['country'] = $address['country'];
                            $order['province'] = $address['province'];
                            $order['city'] = $address['city'];
                            $order['district'] = $address['district'];
                            $order['street'] = $address['street'];
                            $order['email'] = $address['email'];
                            $order['address'] = $address['address'];
                            $order['zipcode'] = $address['zipcode'];
                            $order['tel'] = $address['tel'];
                            $order['mobile'] = $address['mobile'];
                            $order['sign_building'] = $address['sign_building'];
                            $order['best_time'] = $address['best_time'];
                            $this->smarty->assign('order', $order);
                        }
                    }
                }

                if ($exist_real_goods) {
                    /* 取得国家 */
                    $this->smarty->assign('country_list', get_regions());
                    if ($order['country'] > 0) {
                        /* 取得省份 */
                        $this->smarty->assign('province_list', get_regions(1, $order['country']));
                        if ($order['province'] > 0) {
                            /* 取得城市 */
                            $this->smarty->assign('city_list', get_regions(2, $order['province']));
                            if ($order['city'] > 0) {
                                /* 取得区域 */
                                $this->smarty->assign('district_list', get_regions(3, $order['city']));
                                if ($order['district'] > 0) {
                                    /* 取得街道 */
                                    $this->smarty->assign('street_list', get_regions(4, $order['district']));
                                }
                            }
                        }
                    }
                }
            } // 选择配送方式
            elseif ('shipping' == $step) {
                /* 如果不存在实体商品 */
                if (!$order['is_zc_order']) {
                    if (!$this->flowUserService->existRealGoods($order_id)) {
                        return sys_msg('Hacking attempt', 1);
                    }
                }

                $where = [
                    'order_id' => $order_id
                ];
                $shippinOrderInfo = $this->orderService->getOrderGoodsInfo($where);

                if (empty($shippinOrderInfo)) {
                    return sys_msg('Hacking attempt', 1);
                }

                /* 取得可用的配送方式列表 */
                $region_id_list = [
                    $order['country'], $order['province'], $order['city'], $order['district'], $order['street']
                ];
                $shipping_list = available_shipping_list($region_id_list, $shippinOrderInfo);

                if ($order['shipping_code'] == 'express') {
                    $shipping_list = $this->shippingService->orderShippingList();

                    $sql = [
                        'whereNotIn' => [
                            [
                                'name' => 'shipping_code',
                                'value' => ['express', 'cac', 'fpd']
                            ]
                        ]
                    ];
                    $shipping_list = BaseRepository::getArraySqlGet($shipping_list, $sql);
                }

                $consignee = [
                    'country' => $order['country'],
                    'province' => $order['province'],
                    'city' => $order['city'],
                    'district' => $order['district'],
                    'street' => $order['street']
                ];

                $goods_list = order_goods($order_id);
                $cart_goods = $goods_list;

                $shipping_fee = 0;
                /* 取得配送费用 */
                foreach ($shipping_list as $key => $val) {
                    if (substr($val['shipping_code'], 0, 5) != 'ship_') {
                        if (config('shop.freight_model') == 0) {
                            $configure_value = '';
                            /* 商品单独设置运费价格 start */
                            if ($cart_goods) {
                                if (count($cart_goods) == 1) {
                                    $cart_goods = array_values($cart_goods);

                                    if (!empty($cart_goods[0]['freight']) && $cart_goods[0]['is_shipping'] == 0) {
                                        if ($cart_goods[0]['freight'] == 1) {
                                            $configure_value = $cart_goods[0]['shipping_fee'] * $cart_goods[0]['goods_number'];
                                        } else {
                                            $trow = get_goods_transport($cart_goods[0]['tid']);

                                            if ($trow['freight_type']) {
                                                $region = [$consignee['country'], $consignee['province'], $consignee['city'], $consignee['district'], $consignee['street']];

                                                $cart_goods[0]['user_id'] = $cart_goods[0]['ru_id'];
                                                $transport_tpl = get_goods_transport_tpl($cart_goods[0], $region, $val, $cart_goods[0]['goods_number']);

                                                $configure_value = isset($transport_tpl['shippingFee']) ? $transport_tpl['shippingFee'] : 0;
                                            } else {

                                                /**
                                                 * 商品运费模板
                                                 * 自定义
                                                 */
                                                $custom_shipping = $this->orderTransportService->getGoodsCustomShipping($cart_goods);

                                                /* 运费模板配送方式 start */
                                                $transport = ['top_area_id', 'area_id', 'tid', 'ru_id', 'sprice'];
                                                $goods_transport = GoodsTransportExtend::select($transport)
                                                    ->where('ru_id', $cart_goods[0]['ru_id'])
                                                    ->where('tid', $cart_goods[0]['tid']);

                                                $goods_transport = $goods_transport->whereRaw("(FIND_IN_SET('" . $consignee['city'] . "', area_id))");

                                                $goods_transport = $goods_transport->first();

                                                $goods_transport = $goods_transport ? $goods_transport->toArray() : [];
                                                /* 运费模板配送方式 end */

                                                /* 运费模板配送方式 start */
                                                $ship_transport = ['tid', 'ru_id', 'shipping_fee'];
                                                $goods_ship_transport = GoodsTransportExpress::select($ship_transport)
                                                    ->where('ru_id', $cart_goods[0]['ru_id'])
                                                    ->where('tid', $cart_goods[0]['tid']);

                                                $goods_ship_transport = $goods_ship_transport->whereRaw("(FIND_IN_SET('" . $val['shipping_id'] . "', shipping_id))");

                                                $goods_ship_transport = $goods_ship_transport->first();

                                                $goods_ship_transport = $goods_ship_transport ? $goods_ship_transport->toArray() : [];
                                                /* 运费模板配送方式 end */

                                                $goods_transport['sprice'] = isset($goods_transport['sprice']) ? $goods_transport['sprice'] : 0;
                                                $goods_ship_transport['shipping_fee'] = isset($goods_ship_transport['shipping_fee']) ? $goods_ship_transport['shipping_fee'] : 0;

                                                /* 是否免运费 start */
                                                if ($custom_shipping && $custom_shipping[$cart_goods[0]['tid']]['amount'] >= $trow['free_money'] && $trow['free_money'] > 0) {
                                                    $is_shipping = 1; /* 免运费 */
                                                } else {
                                                    $is_shipping = 0; /* 有运费 */
                                                }
                                                /* 是否免运费 end */

                                                if ($is_shipping == 0) {
                                                    if ($trow['type'] == 1) {
                                                        $configure_value = $goods_transport['sprice'] * $cart_goods[0]['goods_number'] + $goods_ship_transport['shipping_fee'] * $cart_goods[0]['goods_number'];
                                                    } else {
                                                        $configure_value = $goods_transport['sprice'] + $goods_ship_transport['shipping_fee'];
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                        /* 有配送按配送区域计算运费 */
                                        $configure_type = 1;
                                    }
                                } else {
                                    $order_transpor = $this->orderTransportService->getOrderTransport($cart_goods, $consignee, $val['shipping_id'], $val['shipping_code']);

                                    if ($order_transpor['freight']) {
                                        /* 有配送按配送区域计算运费 */
                                        $configure_type = 1;
                                    }

                                    $configure_value = isset($order_transpor['sprice']) ? $order_transpor['sprice'] : 0;
                                }
                            }
                            /* 商品单独设置运费价格 end */

                            $shipping_fee = $configure_value;
                        }

                        $shipping_cfg = unserialize_config($val['configure']);
                        $val['insure'] = isset($val['insure']) ? $val['insure'] : 0;
                        $val['support_cod'] = isset($val['support_cod']) ? $val['support_cod'] : 0;

                        $shipping_list[$key]['shipping_id'] = $val['shipping_id'];
                        $shipping_list[$key]['shipping_name'] = $val['shipping_name'];
                        $shipping_list[$key]['shipping_code'] = $val['shipping_code'];
                        $shipping_list[$key]['format_shipping_fee'] = $this->dscRepository->getPriceFormat($shipping_fee, false);
                        $shipping_list[$key]['shipping_fee'] = $shipping_fee;
                        $shipping_list[$key]['insure_formated'] = strpos($val['insure'], '%') === false ? $this->dscRepository->getPriceFormat($val['insure'], false) : $val['insure'];
                        $shipping_list[$key]['format_free_money'] = $this->dscRepository->getPriceFormat($shipping_cfg['free_money'], false);
                        $shipping_list[$key]['free_money'] = $shipping_cfg['free_money'];

                        /* 当前的配送方式是否支持保价 */
                        $insure_disabled = false;
                        $cod_disabled = false;
                        if ($val['shipping_id'] == $order['shipping_id']) {
                            $insure_disabled = ($val['insure'] == 0);
                            $cod_disabled = ($val['support_cod'] == 0);
                        }

                        $shipping_list[$key]['insure_disabled'] = $insure_disabled;
                        $shipping_list[$key]['cod_disabled'] = $cod_disabled;
                    }

                    // 兼容过滤ecjia配送方式
                    if (substr($val['shipping_code'], 0, 5) == 'ship_') {
                        unset($shipping_list[$key]);
                    }
                }

                $this->smarty->assign('shipping_list', $shipping_list);
            } // 选择支付方式
            elseif ('payment' == $step) {

                /* 如果已发货，就不能修改订单了（配送方式和发货单号除外） */
                if ($order['pay_status'] == PS_PAYED || $order['pay_status'] == PS_PAYED_PART || $order['shipping_status'] == SS_SHIPPED || $order['shipping_status'] == SS_RECEIVED) {
                    return sys_msg($GLOBALS['_LANG']['cannot_edit_order_payed'], 1);
                }

                /* 取得可用的支付方式列表 */
                if ($this->flowUserService->existRealGoods($order_id)) {
                    /* 存在实体商品 */
                    $region_id_list = [
                        $order['country'], $order['province'], $order['city'], $order['district'], $order['street']
                    ];
                    $shipping_area = shipping_info($order['shipping_id']);

                    $shipping_area['support_cod'] = isset($shipping_area['support_cod']) ? $shipping_area['support_cod'] : 0;
                    $shipping_area['pay_fee'] = isset($shipping_area['pay_fee']) ? $shipping_area['pay_fee'] : 0;
                    $pay_fee = ($shipping_area['support_cod'] == 1) ? $shipping_area['pay_fee'] : 0;

                    $payment_list = available_payment_list($shipping_area['support_cod'], $pay_fee);
                } else {
                    /* 不存在实体商品 */
                    $payment_list = available_payment_list(false);
                }

                /* 过滤掉使用余额支付 */
                foreach ($payment_list as $key => $payment) {
                    if ($payment['pay_code'] == 'balance') {
                        unset($payment_list[$key]);
                    }
                }
                $this->smarty->assign('payment_list', $payment_list);
            } // 选择包装、贺卡
            elseif ('other' == $step) {
                /* 查询是否存在实体商品 */
                $exist_real_goods = $this->flowUserService->existRealGoods($order_id);
                if ($order['is_zc_order']) {
                    $this->smarty->assign('exist_real_goods', true);
                } else {
                    $this->smarty->assign('exist_real_goods', $exist_real_goods);
                }

                if ($exist_real_goods) {
                    /* 取得包装列表 */
                    $this->smarty->assign('pack_list', pack_list());

                    /* 取得贺卡列表 */
                    $this->smarty->assign('card_list', card_list());
                }
            } // 费用
            elseif ('money' == $step) {

                // 已支付 已发货 不可修改订单金额
                if ($order['pay_status'] == PS_PAYED || $order['pay_status'] == PS_PAYED_PART || $order['shipping_status'] == SS_SHIPPED || $order['shipping_status'] == SS_RECEIVED) {
                    return sys_msg($GLOBALS['_LANG']['cannot_edit_order_payed'], 1);
                }

                /* 查询是否存在实体商品 */
                $exist_real_goods = $this->flowUserService->existRealGoods($order_id);
                if ($order['is_zc_order']) {
                    $this->smarty->assign('exist_real_goods', true);
                } else {
                    $this->smarty->assign('exist_real_goods', $exist_real_goods);
                }

                /* 取得用户信息 */
                if ($order['user_id'] > 0) {
                    $user_info = Users::where('user_id', $order['user_id'])->select('user_money', 'pay_points');
                    $user_info = BaseRepository::getToArrayFirst($user_info);

                    /* 计算可用余额 */
                    $this->smarty->assign('available_user_money', $order['surplus'] + $user_info['user_money']);

                    /* 计算可用积分 */
                    $this->smarty->assign('available_pay_points', $order['integral'] + $user_info['pay_points']);

                    /* 取得用户可用红包 */
                    $user_bonus = app(BonusService::class)->getUserBonusInfo($order['user_id'], $order['goods_amount']);
                    if ($order['bonus_id'] > 0) {
                        $bonus = bonus_info($order['bonus_id']);
                        $user_bonus[] = $bonus;
                    }
                    $this->smarty->assign('available_bonus', $user_bonus);
                }
            } // 发货后修改配送方式和发货单号
            elseif ('invoice' == $step) {

                // 未支付 或未发货 不能修改配送方式
                if ($order['pay_status'] == PS_UNPAYED || $order['shipping_status'] == SS_UNSHIPPED) {
                    return sys_msg($GLOBALS['_LANG']['cannot_edit_order_shipped'], 1);
                }

                /* 如果不存在实体商品 */
                if (!$order['is_zc_order']) {
                    if (!$this->flowUserService->existRealGoods($order_id)) {
                        return sys_msg('Hacking attempt', 1);
                    }
                }

                $where = [
                    'order_id' => $order_id
                ];
                $shippinOrderInfo = $this->orderService->getOrderGoodsInfo($where);

                /* 取得可用的配送方式列表 */
                $region_id_list = [
                    $order['country'], $order['province'], $order['city'], $order['district']
                ];

                $shipping_list = available_shipping_list($region_id_list, $shippinOrderInfo);
                $this->smarty->assign('shipping_list', $shipping_list);
            }

            // 商品金额输入
            $price_format = config('shop.price_format', 0);
            $this->smarty->assign('price_format', $price_format);

            /* 显示模板 */

            return $this->smarty->display('order_step.dwt');
        }

        /*------------------------------------------------------ */
        //-- 查询仓库地区
        /*------------------------------------------------------ */
        elseif ($act == 'search_area') {
            $check_auth = check_authz_json('order_view');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $warehouse_id = intval($_REQUEST['warehouse_id']);

            $res = RegionWarehouse::where('region_type', 1)->where('parent_id', $warehouse_id);
            $region_list = BaseRepository::getToArrayGet($res);

            $select = '<select name="area_id">';
            $select .= '<option value="0">' . lang('admin/common.please_select') . '</option>';
            if ($region_list) {
                foreach ($region_list as $key => $row) {
                    $select .= '<option value="' . $row['region_id'] . '">' . $row['region_name'] . '</option>';
                }
            }
            $select .= '</select>';

            $result = $select;

            return make_json_result($result);
        }

        /*------------------------------------------------------ */
        //-- 处理
        /*------------------------------------------------------ */

        elseif ($act == 'process') {
            /* 取得参数 func */
            $func = isset($_GET['func']) ? $_GET['func'] : '';

            /* 删除订单商品 */
            if ('drop_order_goods' == $func) {
                /* 检查权限 */
                admin_priv('order_edit');

                /* 取得参数 */
                $rec_id = intval($_GET['rec_id']);
                $step_act = $_GET['step_act'];
                $order_id = intval($_GET['order_id']);

                /* 如果使用库存，且下订单时减库存，则修改库存 */
                if (config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_PLACE) {
                    $res = OrderGoods::where('rec_id', $rec_id)->select('goods_id', 'goods_number');
                    $goods = BaseRepository::getToArrayFirst($res);
                    if (!empty($goods)) {
                        Goods::where('goods_id', $goods['goods_id'])->increment('goods_number', $goods['goods_number']);
                    }
                }

                /* 删除 */
                OrderGoods::where('rec_id', $rec_id)->delete();

                /* 更新商品总金额和订单总金额 */
                update_order($order_id, ['goods_amount' => order_amount($order_id)]);
                $this->orderManageService->updateOrderAmount($order_id);
                // 有主订单更新主订单总价
                $parent_id = OrderInfo::where('order_id', $order_id)->value('main_order_id');
                $parent_id = $parent_id ? $parent_id : 0;
                if ($parent_id) {
                    $res = OrderInfo::where('main_order_id', $parent_id);
                    $order_ids = BaseRepository::getToArrayGet($res);

                    if ($order_ids) {
                        $main_goods_amount = 0;
                        foreach ($order_ids as $val) {
                            $main_goods_amount += order_amount($val['order_id']);
                        }
                    }
                    update_order($parent_id, ['goods_amount' => $main_goods_amount]);
                    $this->orderManageService->updateOrderAmount($parent_id);
                }

                /* 跳回订单商品 */
                return dsc_header("Location: order.php?act=" . $step_act . "&order_id=" . $order_id . "&step=goods\n");
            } /* 取消刚添加或编辑的订单 */
            elseif ('cancel_order' == $func) {
                $step_act = $_GET['step_act'];
                $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
                if ($step_act == 'add') {
                    /* 如果是添加，删除订单，返回订单列表 */
                    if ($order_id > 0) {
                        OrderInfo::where('order_id', $order_id)->delete();
                    }
                    return dsc_header("Location: order.php?act=list\n");
                } else {
                    /* 如果是编辑，返回订单信息 */
                    return dsc_header("Location: order.php?act=info&order_id=" . $order_id . "\n");
                }
            } /* 编辑订单时由于订单已付款且金额减少而退款 */
            elseif ('refund' == $func) {
                /* 处理退款 */
                $order_id = intval($_REQUEST['order_id']);
                $refund_type = intval($_REQUEST['refund']);
                $refund_note = $_REQUEST['refund_note'];
                $refund_amount = $_REQUEST['refund_amount'];
                $order = order_info($order_id);

                $refund_order_amount = $order['order_amount'] < 0 ? $order['order_amount'] * -1 : $order['order_amount'];

                if ($order['order_amount'] < 0 && $refund_amount > $refund_order_amount) {
                    $link[] = ['text' => lang('admin/common.go_back'), 'href' => 'order.php?act=process&func=load_refund&anonymous=0&order_id=' . $order_id . '&refund_amount=' . $refund_amount];
                    return sys_msg(lang('admin/order.refund_type_notic_one'), 1, $link);
                }

                $is_ok = order_refund($order, $refund_type, "【" . $order['order_sn'] . "】" . $refund_note, $refund_amount);

                if ($is_ok == false) {
                    $links[] = ['href' => 'order.php?act=info&order_id=' . $order_id, 'text' => lang('admin/order.return_order_info')];
                    return sys_msg(lang('admin/order.refund_type_notic_two'), 1, $links);
                }

                if ($order['order_amount'] < 0) {
                    $update_order['order_amount'] = $order['order_amount'] + $refund_amount;
                }

                /* 修改应付款金额为0，已付款金额减少 $refund_amount */
                update_order($order_id, $update_order);

                if ($refund_type == 1) {
                    $refund_note = "【" . lang('admin/order.return_user_money') . "】" . lang('admin/order.shipping_refund') . "，" . $refund_note;
                } elseif ($refund_type == 2) {
                    $refund_note = "【" . lang('admin/order.create_user_account') . "】" . lang('admin/order.shipping_refund') . "，" . $refund_note;
                }

                /* 记录log */
                $action_note = sprintf($refund_note, $this->dscRepository->getPriceFormat($refund_amount));
                order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], $action_note, session('admin_name'));

                /* 返回订单详情 */
                return dsc_header("Location: order.php?act=info&order_id=" . $order_id . "\n");
            } /* 载入退款页面 */
            elseif ('load_refund' == $func) {
                $refund_amount = floatval($_REQUEST['refund_amount']);
                $this->smarty->assign('refund_amount', $refund_amount);
                $this->smarty->assign('formated_refund_amount', $this->dscRepository->getPriceFormat($refund_amount));

                $anonymous = $_REQUEST['anonymous'];
                $this->smarty->assign('anonymous', $anonymous); // 是否匿名

                $order_id = intval($_REQUEST['order_id']);
                $this->smarty->assign('order_id', $order_id); // 订单id

                /* 显示模板 */
                $this->smarty->assign('ur_here', lang('admin/order.refund'));

                return $this->smarty->display('order_refund.dwt');
            } else {
                return 'invalid params';
            }
        }

        /*------------------------------------------------------ */
        //-- 操作订单状态（载入页面）
        /*------------------------------------------------------ */
        elseif ($act == 'operate') {

            /* 检查权限 */
            admin_priv('order_os_edit');

            /* 取得订单id（可能是多个，多个sn） */
            $order_id = request()->input('order_id', 0);
            $order_id = !empty($order_id) ? addslashes_deep($order_id) : 0;

            $rec_id = intval(request()->input('rec_id', ''));
            $ret_id = intval(request()->input('ret_id', ''));

            $batch = e(request()->input('batch')); // 是否批处理
            $action_note = e(request()->input('action_note', ''));

            /*------------------------------------------------------ */
            //-- 确认
            /*------------------------------------------------------ */
            if (isset($_POST['confirm'])) {
                $require_note = false;
                $action = lang('admin/order.op_confirm');
                $operation = 'confirm';
            }

            /*------------------------------------------------------ */
            //-- 一键发货 订单内页底部
            /*------------------------------------------------------ */
            elseif (isset($_POST['to_shipping'])) {
                $invoice_no = empty($_REQUEST['invoice_no']) ? '' : trim($_REQUEST['invoice_no']);  //快递单号
                $express_code = empty($_REQUEST['express_code']) ? '' : trim($_REQUEST['express_code']);  //快递公司编码

                if (empty($order_id)) {
                    return sys_msg(lang('order does not exist'), 1);
                }

                if (empty($invoice_no)) {
                    /* 操作失败 */
                    $links[] = ['text' => $GLOBALS['_LANG']['not_invoice_no'], 'href' => 'order.php?act=info&order_id=' . $order_id];
                    return sys_msg(lang('admin/order.act_false'), 0, $links);
                }

                /* 定义当前时间 */
                define('GMTIME_UTC', TimeRepository::getGmTime()); // 获取 UTC 时间戳

                $data[0]['order_id'] = $order_id;
                $data[0]['invoice_no'] = $invoice_no;
                $data[0]['express_code'] = $express_code;

                $action_note = $action_note ?? '';
                $res = $this->orderManageService->oneClickShipping($data, $action_note, 'one', session('admin_id'), session('admin_name'));
                if (!empty($res['delivery_id'])) {
                    /* 操作成功 */
                    $seller_list = !empty($res['ru_id']) ? "&seller_list=1" : "&seller_list=0";
                    $links[] = ['text' => lang('admin/common.09_delivery_order'), 'href' => 'order.php?act=delivery_list' . $seller_list];
                    $links[] = ['text' => lang('admin/order.delivery_sn') . lang('admin/order.detail'), 'href' => 'order.php?act=delivery_info&delivery_id=' . $res['delivery_id']];
                    return sys_msg($res['msg'] ?? '', $res['error_code'] ?? 0, $links);
                }

                $links[] = ['text' => lang('admin/order.order_info'), 'href' => 'order.php?act=info&order_id=' . $order_id];
                return sys_msg($res['msg'] ?? '', $res['error_code'] ?? 0, $links);
            }

            /* ------------------------------------------------------ */
            //-- 付款
            /* ------------------------------------------------------ */
            elseif (isset($_POST['pay'])) {
                /* 检查权限 */
                admin_priv('order_ps_edit');
                $require_note = config('shop.order_pay_note') == 1;
                $action = $GLOBALS['_LANG']['op_pay'];
                $operation = 'pay';
            }

            /* ------------------------------------------------------ */
            //-- 未付款
            /* ------------------------------------------------------ */
            elseif (isset($_POST['unpay'])) {
                /* 检查权限 */
                admin_priv('order_ps_edit');

                $require_note = config('shop.order_unpay_note') == 1;
                $order = order_info($order_id);
                if ($order['pay_status'] > 0) {
                    $show_refund = true;
                }
                $anonymous = $order['user_id'] == 0;
                $action = $GLOBALS['_LANG']['op_unpay'];
                $operation = 'unpay';

                //退还积分
                if ($order['integral'] && $order['integral_money']) {
                    $this->smarty->assign('refound_pay_points', $order['integral']);
                }

                $refound_amount = $order['total_fee'] - $order['shipping_fee'] - $order['use_val'] - $order['integral_money'] - $order['vc_dis_money'] - $order['discount'] - $order['coupons'] - $order['bonus'];
                $refound_amount = $this->dscRepository->getPriceFormat($refound_amount, true, false);
                $refound_amount = $refound_amount > 0 ? $refound_amount : 0;

                //退还已使用储值卡
                $res = ValueCardRecord::where('order_id', $order['order_id']);
                $value_card = BaseRepository::getToArrayFirst($res);
                $shipping_del = false;
                /* 如果快递费小于余额或者在线支付，退款金额减去快递费 */
                if ($order['surplus'] > $order['shipping_fee'] || $order['money_paid'] > $order['shipping_fee']) {
                    $this->smarty->assign('shipping_fee_from', 1);//快递费由余额/在线支付抵扣
                    $shipping_del = true;
                }

                if (!empty($value_card)) {
                    /* 快递费从储值卡抵扣 */
                    if ($value_card['use_val'] > $order['shipping_fee'] && $shipping_del === false) {
                        $value_card['use_val'] -= $order['shipping_fee'];
                        $shipping_del = true;
                        $this->smarty->assign('shipping_fee_from', 2);//快递费由储值卡抵扣
                    }
                    $this->smarty->assign('value_card', $value_card);
                }

                $this->smarty->assign('refound_amount', $refound_amount);
                $this->smarty->assign('shipping_fee', $order['shipping_fee']);

            }

            /* ------------------------------------------------------ */
            //-- 配货
            /* ------------------------------------------------------ */
            elseif (isset($_POST['prepare'])) {
                $require_note = false;
                $action = $GLOBALS['_LANG']['op_prepare'];
                $operation = 'prepare';
            }

            /* ------------------------------------------------------ */
            //-- 分单
            /* ------------------------------------------------------ */
            elseif (isset($_POST['ship'])) {
                /* 查询：检查权限 */
                admin_priv('order_ss_edit');

                $order_id = intval(trim($order_id));
                $action_note = trim($action_note);

                /* 查询：根据订单id查询订单信息 */
                if (!empty($order_id)) {
                    $order = order_info($order_id);
                } else {
                    return 'order does not exist';
                }

                /* 查询：根据订单是否完成 检查权限 */
                if (order_finished($order)) {
                    admin_priv('order_view_finished');
                } else {
                    admin_priv('order_view');
                }

                /* 查询：如果管理员属于某个办事处，检查该订单是否也属于这个办事处 */
                $agency_id = AdminUser::where('user_id', session('admin_id'))->value('agency_id');
                $agency_id = $agency_id ? $agency_id : 0;
                if ($agency_id > 0) {
                    if (!empty($order['agency_id']) && $order['agency_id'] != $agency_id) {
                        return sys_msg(lang('admin/common.priv_error'), 0);
                    }
                }

                /* 查询：取得用户名 */
                if ($order['user_id'] > 0) {
                    $user_info = Users::where('user_id', $order['user_id']);
                    $user_info = BaseRepository::getToArrayFirst($user_info);

                    if (!empty($user_info)) {
                        $order['user_name'] = $user_info['user_name'];

                        if (config('shop.show_mobile', 0) == 0) {
                            $order['user_name'] = $this->dscRepository->stringToStar($order['user_name']);
                        }
                    }
                }

                /* 查询：其他处理 */
                $order['order_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $order['add_time']);
                $order['invoice_no'] = $order['shipping_status'] == SS_UNSHIPPED || $order['shipping_status'] == SS_PREPARING ? $GLOBALS['_LANG']['ss'][SS_UNSHIPPED] : $order['invoice_no'];

                /* 查询：是否保价 */
                $order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;

                /* 查询：是否存在实体商品 */
                $exist_real_goods = $this->flowUserService->existRealGoods($order_id);

                /* 查询：取得订单商品 */
                $_goods = $this->orderManageService->getOrderGoods(['order_id' => $order['order_id'], 'order_sn' => $order['order_sn']]);

                $attr = $_goods['attr'];
                $goods_list = $_goods['goods_list'];
                unset($_goods);

                /* 查询：商品已发货数量 此单可发货数量 */
                if ($goods_list) {
                    foreach ($goods_list as $key => $goods_value) {
                        if (!$goods_value['goods_id']) {
                            continue;
                        }

                        /* 超级礼包 */
                        if (($goods_value['extension_code'] == 'package_buy') && (count($goods_value['package_goods_list']) > 0)) {
                            $goods_list[$key]['package_goods_list'] = $this->orderManageService->packageGoods($goods_value['package_goods_list'], $goods_value['goods_number'], $goods_value['order_id'], $goods_value['extension_code'], $goods_value['goods_id']);

                            foreach ($goods_list[$key]['package_goods_list'] as $pg_key => $pg_value) {
                                $goods_list[$key]['package_goods_list'][$pg_key]['readonly'] = '';
                                /* 使用库存 是否缺货 */
                                if ($pg_value['storage'] <= 0 && config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_SHIP) {
                                    //$goods_list[$key]['package_goods_list'][$pg_key]['send'] = lang('admin/order.act_good_vacancy');
                                    $goods_list[$key]['package_goods_list'][$pg_key]['readonly'] = 'readonly="readonly"';
                                } /* 将已经全部发货的商品设置为只读 */
                                elseif ($pg_value['send'] <= 0) {
                                    //$goods_list[$key]['package_goods_list'][$pg_key]['send'] = lang('admin/order.act_good_delivery');
                                    $goods_list[$key]['package_goods_list'][$pg_key]['readonly'] = 'readonly="readonly"';
                                }
                            }
                        } else {
                            $goods_list[$key]['sended'] = $goods_value['send_number'];
                            $goods_list[$key]['send'] = $goods_value['goods_number'] - $goods_value['send_number'];

                            $goods_list[$key]['readonly'] = '';
                            /* 是否缺货 */
                            if ($goods_value['storage'] <= 0 && config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_SHIP) {
                                $goods_list[$key]['send'] = lang('admin/order.act_good_vacancy');
                                $goods_list[$key]['readonly'] = 'readonly="readonly"';
                            } elseif ($goods_list[$key]['send'] <= 0) {
                                $goods_list[$key]['send'] = lang('admin/order.act_good_delivery');
                                $goods_list[$key]['readonly'] = 'readonly="readonly"';
                            }

                            //仅退款申通通过则限制生成发货单 start
                            $res = OrderReturn::where('rec_id', $goods_value['rec_id'])->where('return_type', 3);
                            $rec_info = BaseRepository::getToArrayFirst($res);
                            if ($rec_info) {
                                $goods_list[$key] = array_merge($goods_list[$key], $rec_info);
                            }
                            //仅退款申通通过则限制生成发货单 end
                        }
                    }
                }

                /* 模板赋值 */
                $this->smarty->assign('order', $order);

                if ($order['is_zc_order']) {
                    $this->smarty->assign('exist_real_goods', true);
                } else {
                    $this->smarty->assign('exist_real_goods', $exist_real_goods);
                }

                $this->smarty->assign('goods_attr', $attr);
                $this->smarty->assign('goods_list', $goods_list);
                $this->smarty->assign('order_id', $order_id); // 订单id
                $this->smarty->assign('operation', 'split'); // 订单id
                $this->smarty->assign('action_note', $action_note); // 发货操作信息

                $suppliers_list = $this->orderManageService->getSuppliersList();
                $suppliers_list_count = count($suppliers_list);
                $this->smarty->assign('suppliers_name', suppliers_list_name()); // 取供货商名
                $this->smarty->assign('suppliers_list', ($suppliers_list_count == 0 ? 0 : $suppliers_list)); // 取供货商列表

                /* 显示模板 */
                $this->smarty->assign('ur_here', lang('admin/order.order_operate') . $GLOBALS['_LANG']['op_split']);

                /* 取得订单操作记录 */
                $act_list = [];
                $res = OrderAction::where('order_id', $order_id)->orderBy('log_time', 'DESC')->orderBy('action_id', 'DESC');
                $res = BaseRepository::getToArrayGet($res);
                foreach ($res as $row) {
                    $row['order_status'] = $GLOBALS['_LANG']['os'][$row['order_status']];
                    $row['pay_status'] = $GLOBALS['_LANG']['ps'][$row['pay_status']];
                    $row['shipping_status'] = $GLOBALS['_LANG']['ss'][$row['shipping_status']];
                    $row['action_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['log_time']);
                    $act_list[] = $row;
                }
                $this->smarty->assign('action_list', $act_list);

                return $this->smarty->display('order_delivery_info.dwt');
            }

            /* ------------------------------------------------------ */
            //-- 未发货
            /* ------------------------------------------------------ */
            elseif (isset($_POST['unship'])) {
                /* 检查权限 */
                admin_priv('order_ss_edit');

                $require_note = config('shop.order_unship_note') == 1;
                $action = $GLOBALS['_LANG']['op_unship'];
                $operation = 'unship';
            }

            /* ------------------------------------------------------ */
            //-- 收货确认
            /* ------------------------------------------------------ */
            elseif (isset($_POST['receive'])) {
                $require_note = config('shop.order_receive_note') == 1;
                $action = $GLOBALS['_LANG']['op_receive'];
                $operation = 'receive';
            }

            /* ------------------------------------------------------ */
            //-- 取消
            /* ------------------------------------------------------ */
            elseif (isset($_POST['cancel'])) {
                $require_note = config('shop.order_cancel_note') == 1;
                $action = $GLOBALS['_LANG']['op_cancel'];
                $operation = 'cancel';
                $show_cancel_note = true;
                $order = order_info($order_id);
                if (isset($order['pay_status']) && $order['pay_status'] > 0) {
                    $show_refund = true;
                }

                $order['user_id'] = $order['user_id'] ?? 0;

                $anonymous = $order['user_id'] == 0;
            }

            /* ------------------------------------------------------ */
            //-- 无效
            /* ------------------------------------------------------ */
            elseif (isset($_POST['invalid'])) {
                $require_note = config('shop.order_invalid_note') == 1;
                $action = $GLOBALS['_LANG']['op_invalid'];
                $operation = 'invalid';
            }

            /* ------------------------------------------------------ */
            //-- 发货
            /* ------------------------------------------------------ */
            if (isset($_POST['shipping'])) {
                $require_note = false;
                $action = $GLOBALS['_LANG']['op_ship'];
                $operation = 'shipping';
                $op_shipping = true;
            }

            /* ------------------------------------------------------ */
            //-- 售后
            /* ------------------------------------------------------ */
            elseif (isset($_POST['after_service'])) {
                $require_note = true;
                $action = lang('admin/order.op_after_service');
                $operation = 'after_service';
            }

            /*------------------------------------------------------ */
            //-- 退货/退款 - 订单详情退款
            /*------------------------------------------------------ */
            elseif (isset($_POST['return'])) {

                $ret_id = OrderReturn::where('order_id', $order_id)->value('ret_id');
                $ret_id = $ret_id ? $ret_id : 0;
                if ($ret_id > 0) {
                    $links[] = ['text' => lang('admin/common.go_back'), 'href' => 'order.php?act=info&order_id=' . $order_id];
                    return sys_msg($GLOBALS['_LANG']['refund_type_notic_three'], 1, $links);
                } else {
                    $require_note = config('shop.order_return_note') == 1;

                    $order = OrderInfo::where('order_id', $order_id);
                    $order = BaseRepository::getToArrayFirst($order);

                    if ($order['pay_status'] > 0) {
                        $show_refund = true;
                    }
                    $anonymous = $order['user_id'] == 0;
                    $action = $GLOBALS['_LANG']['op_return'];
                    $operation = 'return';
                }

                //退还积分
                if ($order['integral'] && $order['integral_money']) {
                    $this->smarty->assign('refound_pay_points', $order['integral']);
                }

                $paid_amount = $order['money_paid'] + $order['surplus'];

                if ($paid_amount > 0 && $order['shipping_fee'] > 0 && $paid_amount >= $order['shipping_fee']) {
                    $refound_amount = $paid_amount - $order['shipping_fee'];
                } else {
                    $refound_amount = $paid_amount;
                }

                $refound_amount = $this->dscRepository->changeFloat($refound_amount);

                $shipping_del = false;
                /* 如果快递费小于余额或者在线支付，退款金额减去快递费 */
                if ($order['surplus'] > $order['shipping_fee'] || $order['money_paid'] > $order['shipping_fee']) {
                    $this->smarty->assign('shipping_fee_from', 1);//快递费由余额/在线支付抵扣
                    $shipping_del = true;
                }

                //退还已使用储值卡
                $res = ValueCardRecord::where('order_id', $order['order_id']);
                $value_card = BaseRepository::getToArrayFirst($res);

                if (!empty($value_card)) {
                    /* 快递费从储值卡抵扣 */
                    if ($value_card['use_val'] > $order['shipping_fee'] && $shipping_del === false) {
                        $value_card['use_val'] -= $order['shipping_fee'];
                        $shipping_del = true;
                        $this->smarty->assign('shipping_fee_from', 2);//快递费由储值卡抵扣
                    }

                    $this->smarty->assign('value_card', $value_card);
                }

                $this->smarty->assign('refound_amount', $refound_amount);
                $this->smarty->assign('shipping_fee', $order['shipping_fee']);

                // 是否支持原路退款
                $show_return_online = OrderReturnRepository::showReturnOnline($order['pay_id']);
                $this->smarty->assign('show_return_online', $show_return_online);

                // 退换货原因
                $parent_cause = OrderReturnRepository::getReturnCause(0, 1);
                $this->smarty->assign('parent_cause', $parent_cause);

                // 优先退款方式 1 退回会员余额, 2 线下退款, 6 原路退款
                $precedence_return_type = config('shop.precedence_return_type', 1);
                if ($show_return_online == false) {
                    $precedence_return_type = 1;
                }
                $this->smarty->assign('precedence_return_type', $precedence_return_type);

                // 商家退款是否由平台审批 => 是 则由平台操作退款
                if (config('shop.seller_return_check', 0) == 1 && $order['ru_id'] > 0) {
                    $this->smarty->assign('action', trans('admin::order.label_return_check'));
                }

                $this->smarty->assign('order', $order); // 订单信息
            }

            /* ------------------------------------------------------ */
            //-- 同意申请
            /* ------------------------------------------------------ */
            elseif (isset($_POST['agree_apply'])) {

                $require_note = false;
                $action = lang('admin/order.op_confirm');
                $operation = 'agree_apply';
            }

            /*------------------------------------------------------ */
            //-- 订单单品退款 - 退货单页面退款
            /*------------------------------------------------------ */
            elseif (isset($_POST['refound'])) {

                $return_order = return_order_info($ret_id);
                $refund_amount = $return_order['pay_goods_amount']; //退款金额

                $shippingFee = $return_order['pay_shipping_fee'] ?? 0; //退款运费金额

                $shipping_fee_from = (int)request()->input('shipping_fee_from', 1); // 快递费从哪种支付方式扣除 1.由余额/在线支付抵扣  2.由储值卡抵扣

                if (CROSS_BORDER === true) {
                    // 跨境多商户
                    $return_rate_price = request()->input('return_rate_price', 0);
                    $return_rate_price = !empty($return_rate_price) ? floatval($return_rate_price) : 0;
                    $this->smarty->assign('rate_price', $return_rate_price);

                    $admin = app(CrossBorderService::class)->adminExists();
                    if (!empty($admin)) {
                        $admin->smartyAssign();
                    }
                }

                $order = order_info($order_id);

                // 操作信息
                $require_note = config('shop.order_return_note') == 1;

                $anonymous = $order['user_id'] == 0;
                $action = $GLOBALS['_LANG']['op_return'];
                $operation = 'refound';

                //判断运费退款是否大于实际运费退款金额
                $is_refound_shippfee = order_refound_shipping_fee($order_id, $ret_id);
                $is_refound_shippfee_amount = $is_refound_shippfee + $shippingFee;

                if (($is_refound_shippfee_amount > $order['shipping_fee']) || ($shippingFee == 0 && $is_refound_shippfee > 0)) {
                    $shippingFee = $order['shipping_fee'] - $is_refound_shippfee;
                }

                // 判断退货单订单中是否只有一个商品   如果只有一个则退订单的全部积分   如果多个则按商品积分的比例来退  by kong
                $res = OrderGoods::where('order_id', $order_id)->select('rec_id', 'goods_id');
                $count_goods = BaseRepository::getToArrayGet($res);

                if (count($count_goods) > 1) {
                    foreach ($count_goods as $k => $v) {
                        $all_goods_id[] = $v['goods_id'];
                    }
                    //获取该订单的全部可用积分
                    $count_integral = Goods::whereIn('goods_id', $all_goods_id)->sum('integral');
                    $count_integral = $count_integral ? $count_integral : 0;

                    //退货商品的可用积分
                    $return_integral = Goods::whereHasIn('getOrderReturn', function ($query) use ($ret_id) {
                        $query->where('ret_id', $ret_id);
                    })->value('integral');

                    $count_integral = !empty($count_integral) ? $count_integral : 1;
                    $return_ratio = $return_integral / $count_integral; //退还积分比例
                    $return_price = (empty($order['pay_points']) ? 0 : $order['pay_points']) * $return_ratio; //那比例最多返还的积分
                } else {
                    $return_price = empty($order['pay_points']) ? 0 : $order['pay_points']; //by kong 赋值支付积分
                }

                //获取该商品的订单数量
                $goods_number = OrderGoods::where('rec_id', $rec_id)->value('goods_number');
                $goods_number = $goods_number ? $goods_number : 0;

                //获取退货数量
                $return_number = OrderReturnExtend::where('ret_id', $ret_id)->value('return_number');
                $return_number = $return_number ? $return_number : 0;

                $refound_pay_points = intval($return_order['should_integral']);

                if ($order['pay_status'] > 0) {
                    $show_refund1 = true;
                }

                if (empty($return_order['goods_value_card'])) {

                    $should_return = isset($return_order['should_return']) ? $return_order['should_return'] - $return_order['discount_amount'] : 0;

                    $paid_amount = $order['money_paid'] + $order['surplus'];
                    if ($paid_amount > 0 && $paid_amount >= $return_order['return_rate_price']) {
                        $paid_amount = $paid_amount - $return_order['return_rate_price'];
                    }

                    if ($paid_amount > 0 && $paid_amount >= $order['shipping_fee']) {
                        $paid_amount = $paid_amount - $order['shipping_fee'];
                    }

                    if ($refound_amount > $paid_amount) {
                        $refound_amount = $paid_amount;
                    }

                    $value_card = $this->orderRefoundService->judgeValueCardMoney($order['order_id']); //获取储值卡数据
                } else {
                    $value_card['use_val'] = $return_order['pay_value_card'];
                    $value_card['vc_id'] = $return_order['vc_id'];
                }

                // 是否显示原路退款 在线支付方式 微信、支付宝
                $show_return_online = OrderReturnRepository::showReturnOnline($order['pay_id']);
                $this->smarty->assign('show_return_online', $show_return_online);
                $this->smarty->assign('order', $order); // 订单信息

                $this->smarty->assign('refound_pay_points', $refound_pay_points);
                $this->smarty->assign('refound_amount', $refund_amount);
                $this->smarty->assign('shipping_fee', $shippingFee);
                $this->smarty->assign('value_card', $value_card);

                /* 检测订单是否只有一个退货商品的订单 start */
                $is_whole = 0;
                $is_diff = get_order_return_rec($order['order_id']);
                if ($is_diff) {
                    //整单退换货
                    $return_count = return_order_info_byId($order['order_id'], 0);
                    if ($return_count == 1) {
                        $is_whole = 1;
                    }
                }
                $this->smarty->assign('is_whole', $is_whole);
                /* 检测订单是否只有一个退货商品的订单 end */

                // 商家退款是否由平台审批 => 是 则由平台操作退款
                if (config('shop.seller_return_check', 0) == 1 && $order['ru_id'] > 0) {
                    $this->smarty->assign('action', trans('admin::order.label_return_check'));
                }

            }

            /* ------------------------------------------------------ */
            //-- 收到退换货商品
            /* ------------------------------------------------------ */
            elseif (isset($_POST['receive_goods'])) {

                $require_note = false;
                $action = lang('admin/order.op_confirm');
                $operation = 'receive_goods';
            }

            /* ------------------------------------------------------ */
            //-- 换出商品 --  快递信息
            /* ------------------------------------------------------ */
            elseif (isset($_POST['send_submit'])) {

                $shipping_id = $_POST['shipping_name'];
                $invoice_no = $_POST['invoice_no'];
                $action_note = $_POST['action_note'];

                $shipping_name = Shipping::where('shipping_id', $shipping_id)->value('shipping_name');
                $shipping_name = $shipping_name ?? '';

                $require_note = false;
                $action = lang('admin/order.op_confirm');
                $operation = 'receive_goods';

                $data = [
                    'out_shipping_name' => $shipping_id,
                    'out_invoice_no' => $invoice_no
                ];
                OrderReturn::where('rec_id', $rec_id)->update($data);
            }

            /* ------------------------------------------------------ */
            //-- 商品分单寄出
            /* ------------------------------------------------------ */
            elseif (isset($_POST['swapped_out'])) {

                $require_note = false;
                $action = lang('admin/order.op_confirm');
                $operation = 'swapped_out';
            }

            /* ------------------------------------------------------ */
            //-- 商品分单寄出  分单
            /* ------------------------------------------------------ */
            elseif (isset($_POST['swapped_out_single'])) {

                $require_note = false;
                $action = lang('admin/order.op_confirm');
                $operation = 'swapped_out_single';
            }

            /* ------------------------------------------------------ */
            //-- 完成退换货
            /* ------------------------------------------------------ */
            elseif (isset($_POST['complete'])) {

                $require_note = false;
                $action = lang('admin/order.op_confirm');
                $operation = 'complete';
            }

            /* ------------------------------------------------------ */
            //-- 拒绝申请
            /* ------------------------------------------------------ */
            elseif (isset($_POST['refuse_apply'])) {
                $require_note = true;
                $action = lang('admin/order.refuse_apply');
                $operation = 'refuse_apply';
            }

            /* ------------------------------------------------------ */
            //-- 指派
            /* ------------------------------------------------------ */
            elseif (isset($_POST['assign'])) {
                /* 取得参数 */
                $new_agency_id = isset($_POST['agency_id']) ? intval($_POST['agency_id']) : 0;
                if ($new_agency_id == 0) {
                    return sys_msg($GLOBALS['_LANG']['js_languages']['pls_select_agency']);
                }

                /* 查询订单信息 */
                $order = order_info($order_id);

                /* 如果管理员属于某个办事处，检查该订单是否也属于这个办事处 */
                $agency_id = AdminUser::where('user_id', session('admin_id'))->value('agency_id');
                $agency_id = $agency_id ? $agency_id : 0;

                if ($agency_id > 0) {
                    if (!empty($order['agency_id']) && $order['agency_id'] != $agency_id) {
                        return sys_msg(lang('admin/common.priv_error'));
                    }
                }

                /* 修改订单相关所属的办事处 */
                if ($new_agency_id != $order['agency_id']) {
                    $data = ['agency_id' => $new_agency_id];
                    // 更改订单表的供货商ID
                    OrderInfo::where('order_id', $order_id)->update($data);
                    // 更改订单的发货单供货商ID
                    DeliveryOrder::where('order_id', $order_id)->update($data);
                    // 更改订单的退货单供货商ID
                    BackOrder::where('order_id', $order_id)->update($data);
                }

                /* 操作成功 */
                $links[] = ['href' => 'order.php?act=list&' . list_link_postfix(), 'text' => lang('admin/common.02_order_list')];
                return sys_msg(lang('admin/order.act_ok'), 0, $links);
            }

            /* ------------------------------------------------------ */
            //-- 订单删除
            /* ------------------------------------------------------ */
            elseif (isset($_POST['remove'])) {
                $require_note = false;
                $operation = 'remove';
                if (!$batch) {
                    /* 检查能否操作 */
                    $order = OrderInfo::query()->where('order_id', $order_id)->first();
                    $order = $order ? $order->toArray() : [];
                    if (empty($order)) {
                        return sys_msg('Hacking attempt', 1);
                    }

                    $operable_list = $this->orderManageService->operableList($order, session('action_list'));
                    if (!isset($operable_list['remove'])) {
                        return sys_msg('Hacking attempt', 1);
                    }

                    $return_order = OrderReturn::query()->where('order_id', $order_id)->count();
                    if ($return_order) {
                        return sys_msg(sprintf(trans('admin/order.order_remove_failure'), $order['order_sn']), 1, [['href' => 'order.php?act=list&' . list_link_postfix(), 'text' => trans('admin/order.return_list')]]);
                    }

                    // 检查订单是否有未处理投诉
                    $complaint_order = DB::table('complaint')->where('order_id', $order_id)->where('complaint_state', '<>', 4)->count('complaint_id');
                    if ($complaint_order) {
                        return sys_msg(sprintf(trans('admin/order.complaint_order_remove_failure'), $order['order_sn']), 1);
                    }

                    /* 删除订单 */
                    $res = OrderInfo::where('order_id', $order_id)->delete();
                    if ($res) {
                        $this->orderManageService->delDelivery($order_id, ['delivery', 'back']);

                        // 订单删除事件
                        event(new \App\Events\OrderRemoveEvent($order));

                        /* 记录日志 */
                        admin_log($order['order_sn'], 'remove', 'order');

                        /* 返回 */
                        return sys_msg(lang('admin/order.order_removed'), 0, [['href' => 'order.php?act=list&' . list_link_postfix(), 'text' => trans('admin/order.return_list')]]);
                    }

                }
            }

            /* ------------------------------------------------------ */
            //-- 发货单删除
            /* ------------------------------------------------------ */
            elseif (isset($_REQUEST['remove_invoice'])) {
                // 删除发货单
                $delivery_ids = isset($_REQUEST['delivery_id']) ? $_REQUEST['delivery_id'] : $_REQUEST['checkboxes'];

                if (empty($delivery_ids)) {
                    return sys_msg(lang('admin/common.attradd_failed'));
                }

                $delivery_ids = is_array($delivery_ids) ? $delivery_ids : [$delivery_ids];

                foreach ($delivery_ids as $delivery_id) {
                    $delivery_id = intval($delivery_id);

                    // 查询：发货单信息
                    $delivery_order = DeliveryOrder::where('delivery_id', $delivery_id)->select('delivery_id', 'order_id', 'invoice_no', 'status');
                    $delivery_order = BaseRepository::getToArrayFirst($delivery_order);

                    // 如果status不是退货
                    if (isset($delivery_order['status']) && $delivery_order['status'] != DELIVERY_REFOUND) {
                        /* 处理退货 */
                        $this->orderManageService->deliveryReturnGoods($delivery_order);
                    }

                    // 如果status是已发货并且发货单号不为空
                    if (isset($delivery_order['status']) && $delivery_order['status'] == DELIVERY_SHIPPED && !empty($delivery_order['invoice_no'])) {
                        /* 更新：删除订单中的发货单号 */
                        $this->orderManageService->delOrderInvoiceNo($delivery_order['order_id'], $delivery_order['invoice_no']);
                    }

                    // 删除物流记录
                    $this->expressManageService->removeExpressHistory($delivery_id);

                    // 更新：删除发货单
                    DeliveryOrder::where('delivery_id', $delivery_id)->delete();
                }

                /* 返回 */
                return sys_msg(lang('admin/order.tips_delivery_del'), 0, [['href' => 'order.php?act=delivery_list', 'text' => lang('admin/order.return_list')]]);
            }

            /* ------------------------------------------------------ */
            //-- 退货单删除
            /* ------------------------------------------------------ */
            elseif (isset($_REQUEST['remove_back'])) {
                $back_id = isset($_REQUEST['back_id']) ? $_REQUEST['back_id'] : $_POST['checkboxes'];
                /* 删除退货单 */
                if (is_array($back_id)) {
                    BackOrder::whereIn('back_id', $back_id)->delete();
                } else {
                    BackOrder::where('back_id', $back_id)->delete();
                }
                /* 返回 */
                return sys_msg(lang('admin/order.tips_back_del'), 0, [['href' => 'order.php?act=back_list', 'text' => lang('admin/order.return_list')]]);
            }

            /* ------------------------------------------------------ */
            //-- 批量打印订单
            /* ------------------------------------------------------ */
            elseif (isset($_POST['print'])) {
                if (empty($_POST['order_id'])) {
                    return sys_msg(lang('admin/order.pls_select_order'));
                }

                if (config('shop.tp_api')) {
                    //快递鸟、电子面单 start
                    $url = 'tp_api.php?act=order_print&order_sn=' . $_POST['order_id'] . '&order_type=order';
                    return dsc_header("Location: $url\n");
                    //快递鸟、电子面单 end
                }

                /* 赋值公用信息 */
                $this->assign('print_time', TimeRepository::getLocalDate(config('shop.time_format')));
                $this->assign('action_user', session('admin_name'));

                $html = '';
                $order_sn_list = explode(',', $_POST['order_id']);
                foreach ($order_sn_list as $order_sn) {
                    /* 取得订单信息 */
                    $order = order_info(0, $order_sn);

                    /*判断是否是商家商品  by kong*/
                    if ($order['ru_id'] > 0) {
                        //判断是否是主订单
                        $date = ['order_id'];
                        $order_child = count(get_table_date('order_info', "main_order_id = '$order[order_id]'", $date, 1));
                        if ($order_child > 0) {
                            $this->assign('shop_url', $this->dsc->url());
                            $this->assign('shop_name', config('shop.shop_name', ''));
                            $this->assign('shop_address', config('shop.shop_address'));
                            $this->assign('service_phone', config('shop.service_phone'));
                        } else {
                            $this->assign('shop_url', $this->dsc->url());

                            //获取商家域名
                            $domain_name = SellerDomain::where('ru_id', $order['ru_id'])
                                ->where('is_enable', 1)
                                ->value('domain_name');
                            $domain_name = $domain_name ? $domain_name : '';

                            $this->assign('domain_name', $domain_name);
                            $this->assign('shop_name', $this->merchantCommonService->getShopName($order['ru_id'], 1));

                            //获取商家地址，电话
                            $res = SellerShopinfo::where('ru_id', $order['ru_id']);
                            $seller_shopinfo = BaseRepository::getToArrayFirst($res);

                            $this->assign('shop_address', $seller_shopinfo['shop_address']);
                            $this->assign('service_phone', $seller_shopinfo['kf_tel']);
                        }
                    } else {
                        $this->assign('shop_url', $this->dsc->url());
                        $this->assign('shop_name', config('shop.shop_name', ''));
                        $this->assign('shop_address', config('shop.shop_address'));
                        $this->assign('service_phone', config('shop.service_phone'));
                    }
                    // by kong end
                    if (empty($order)) {
                        continue;
                    }

                    /* 根据订单是否完成检查权限 */
                    if (order_finished($order)) {
                        if (!admin_priv('order_view_finished', '', false)) {
                            continue;
                        }
                    } else {
                        if (!admin_priv('order_view', '', false)) {
                            continue;
                        }
                    }

                    /* 如果管理员属于某个办事处，检查该订单是否也属于这个办事处 */
                    $agency_id = AdminUser::where('user_id', session('admin_id'))->value('agency_id');
                    $agency_id = $agency_id ? $agency_id : 0;

                    if ($agency_id > 0) {
                        if (!empty($order['agency_id']) && $order['agency_id'] != $agency_id) {
                            continue;
                        }
                    }

                    /* 取得用户名 */
                    if ($order['user_id'] > 0) {
                        $user_info = Users::where('user_id', $order['user_id']);
                        $user_info = BaseRepository::getToArrayFirst($user_info);

                        if (!empty($user_info)) {
                            $order['user_name'] = $user_info['user_name'];

                            if (config('shop.show_mobile') == 0) {
                                $order['user_name'] = $this->dscRepository->stringToStar($order['user_name']);
                            }
                        }
                    }

                    /* 其他处理 */
                    $order['order_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $order['add_time']);
                    $order['pay_time'] = $order['pay_time'] > 0 ?
                        TimeRepository::getLocalDate(config('shop.time_format'), $order['pay_time']) : $GLOBALS['_LANG']['ps'][PS_UNPAYED];
                    $order['shipping_time'] = $order['shipping_time'] > 0 ?
                        TimeRepository::getLocalDate(config('shop.time_format'), $order['shipping_time']) : $GLOBALS['_LANG']['ss'][SS_UNSHIPPED];
                    $order['status'] = $GLOBALS['_LANG']['os'][$order['order_status']] . ',' . $GLOBALS['_LANG']['ps'][$order['pay_status']] . ',' . $GLOBALS['_LANG']['ss'][$order['shipping_status']];
                    $order['invoice_no'] = $order['shipping_status'] == SS_UNSHIPPED || $order['shipping_status'] == SS_PREPARING ? $GLOBALS['_LANG']['ss'][SS_UNSHIPPED] : $order['invoice_no'];

                    /* 此订单的发货备注(此订单的最后一条操作记录) */
                    $order['invoice_note'] = OrderAction::where('order_id', $order['order_id'])
                        ->where('shipping_status', 1)
                        ->orderBy('log_time', 'DESC')
                        ->value('action_note');
                    $order['invoice_note'] = $order['invoice_note'] ? $order['invoice_note'] : '';

                    /* 参数赋值：订单 */
                    $this->assign('order', $order);

                    /* 取得订单商品 */
                    $goods_list = [];
                    $goods_attr = [];

                    $res = OrderGoods::where('order_id', $order['order_id']);

                    $res = $res->with(['getGoods' => function ($query) {
                        $query = $query->with(['getBrand', 'getCategory']);
                    }]);

                    $res = BaseRepository::getToArrayGet($res);

                    foreach ($res as $row) {
                        $row['goods_unit'] = '';
                        $row['storage'] = '';
                        $row['bar_code'] = '';

                        $row['brand_name'] = '';

                        $row['measure_unit'] = '';
                        if (isset($row['get_goods']) && !empty($row['get_goods'])) {
                            $row['goods_unit'] = $row['get_goods']['goods_unit'];
                            $row['storage'] = $row['get_goods']['goods_number'];
                            $row['bar_code'] = $row['get_goods']['bar_code'];
                            if (isset($row['get_goods']['get_brand']) && !empty($row['get_goods']['get_brand'])) {
                                $row['brand_name'] = $row['get_goods']['get_brand']['brand_name'];
                            }
                            if (isset($row['get_goods']['get_category']) && !empty($row['get_goods']['get_category'])) {
                                $row['measure_unit'] = $row['get_goods']['get_category']['measure_unit'];
                            }
                        }

                        $products = $this->goodsWarehouseService->getWarehouseAttrNumber($row['goods_id'], $row['goods_attr_id'], $row['warehouse_id'], $row['area_id'], $row['area_city'], $row['model_attr']);
                        if ($row['product_id']) {
                            $row['bar_code'] = $products['bar_code'] ?? '';
                        }

                        /* 虚拟商品支持 */
                        if ($row['is_real'] == 0) {
                            /* 取得语言项 */
                            $filename = app_path('Plugins/' . $row['extension_code'] . '/Languages/common_' . config('shop.lang') . '.php');
                            if (file_exists($filename)) {
                                include_once($filename);
                                if (!empty($GLOBALS['_LANG'][$row['extension_code'] . '_link'])) {
                                    $row['goods_name'] = $row['goods_name'] . sprintf($GLOBALS['_LANG'][$row['extension_code'] . '_link'], $row['goods_id'], $order['order_sn']);
                                }
                            }
                        }

                        $row['formated_subtotal'] = price_format($row['goods_price'] * $row['goods_number']);
                        $row['formated_goods_price'] = price_format($row['goods_price']);
                        $row['measure_unit'] = $row['goods_unit'] ? $row['goods_unit'] : $row['measure_unit'];

                        $goods_attr[] = explode(' ', trim($row['goods_attr'])); //将商品属性拆分为一个数组
                        $goods_list[] = $row;
                    }

                    $attr = [];
                    $arr = [];
                    if ($goods_attr) {
                        foreach ($goods_attr as $index => $array_val) {
                            $array_val = BaseRepository::getExplode($array_val);

                            if ($array_val) {
                                foreach ($array_val as $value) {
                                    $arr = explode(':', $value);//以 : 号将属性拆开
                                    $attr[$index][] = @['name' => $arr[0], 'value' => $arr[1]];
                                }
                            }
                        }
                    }

                    $this->assign('goods_attr', $attr);
                    $this->assign('goods_list', $goods_list);
                    $html .= $this->fetch('order_print_preview') . '<div style="PAGE-BREAK-AFTER:always"></div>';
                }

                return $html;
            }

            /* ------------------------------------------------------ */
            //-- 去发货
            /* ------------------------------------------------------ */
            elseif (isset($_POST['to_delivery'])) {
                $order_id = $_REQUEST['order_id'];
                $user_id = Goods::whereHasIn('getOrderGoods', function ($query) use ($order_id) {
                    $query->where('order_id', $order_id);
                })->value('user_id');
                $user_id = $user_id ? $user_id : 0;

                $seller_list = $user_id > 0 ? "&seller_list=1" : "&seller_list=0";
                $url = 'order.php?act=delivery_list&order_sn=' . $_REQUEST['order_sn'] . $seller_list;

                return dsc_header("Location: $url\n");
            }

            /* ------------------------------------------------------ */
            //-- 批量发货 by wu
            /* ------------------------------------------------------ */
            elseif (isset($_REQUEST['batch_delivery'])) {
                /* 检查权限 */
                admin_priv('delivery_view');
                /* 定义当前时间 */
                define('GMTIME_UTC', TimeRepository::getGmTime()); // 获取 UTC 时间戳

                $delivery_id = isset($_REQUEST['delivery_id']) ? $_REQUEST['delivery_id'] : $_REQUEST['checkboxes'];
                $delivery_id = is_array($delivery_id) ? $delivery_id : [$delivery_id];
                $invoice_nos = isset($_REQUEST['invoice_no']) ? $_REQUEST['invoice_no'] : [];
                $action_note = isset($_REQUEST['action_note']) ? trim($_REQUEST['action_note']) : '';

                $no_invoice = 0;
                foreach ($delivery_id as $value_is) {
                    if (array_key_exists($value_is, $invoice_nos) && empty($invoice_nos[$value_is])) {
                        $no_invoice += 1;
                    }
                }

                if ($no_invoice > 0) {
                    return sys_msg($GLOBALS['_LANG']['batch_delivery_failed'], 1);
                }

                foreach ($delivery_id as $value_is) {
                    $msg = '';
                    $value_is = intval($value_is);
                    $delivery_info = get_table_date('delivery_order', "delivery_id='$value_is'", ['order_id', 'status']);

                    //跳过已发货、退货订单
                    if ($delivery_info['status'] != DELIVERY_CREATE || !isset($invoice_nos[$value_is])) {
                        continue;
                    }

                    /* 取得参数 */
                    $delivery = [];
                    $order_id = $delivery_info['order_id'];        // 订单id
                    $delivery_id = $value_is;        // 发货单id
                    $delivery['invoice_no'] = $invoice_nos[$value_is];

                    /* 根据发货单id查询发货单信息 */
                    if (!empty($delivery_id)) {
                        $delivery_order = $this->orderManageService->deliveryOrderInfo($delivery_id);
                    } else {
                        return 'order does not exist';
                    }

                    /* 查询订单信息 */
                    $order = order_info($order_id);
                    /* 检查此单发货商品库存缺货情况  ecmoban模板堂 --zhuo start 下单减库存 */
                    $delivery_stock_sql = "SELECT DG.rec_id AS dg_rec_id, OG.rec_id AS og_rec_id, G.model_attr, G.model_inventory, DG.goods_id, DG.delivery_id, DG.is_real, DG.send_number AS sums, G.goods_number AS storage, G.goods_name, DG.send_number," .
                        " OG.goods_attr_id, OG.warehouse_id, OG.area_id, OG.area_city, OG.ru_id, OG.order_id, OG.product_id FROM " . $this->dsc->table('delivery_goods') . " AS DG, " .
                        $this->dsc->table('goods') . " AS G, " .
                        $this->dsc->table('delivery_order') . " AS D, " .
                        $this->dsc->table('order_goods') . " AS OG " .
                        " WHERE DG.goods_id = G.goods_id AND DG.delivery_id = D.delivery_id AND D.order_id = OG.order_id AND DG.goods_sn = OG.goods_sn AND DG.product_id = OG.product_id AND DG.delivery_id = '$delivery_id' GROUP BY OG.rec_id ";

                    $delivery_stock_result = $this->db->getAll($delivery_stock_sql);

                    $virtual_goods = [];
                    for ($i = 0; $i < count($delivery_stock_result); $i++) {
                        $res = Products::whereRaw(1);
                        if ($delivery_stock_result[$i]['model_attr'] == 1) {
                            $res = ProductsWarehouse::where('warehouse_id', $delivery_stock_result[$i]['warehouse_id']);
                        } elseif ($delivery_stock_result[$i]['model_attr'] == 2) {
                            $res = ProductsArea::where('area_id', $delivery_stock_result[$i]['area_id']);
                        }

                        $res = $res->where('goods_id', $delivery_stock_result[$i]['goods_id']);
                        $prod = BaseRepository::getToArrayFirst($res);

                        /* 如果商品存在规格就查询规格，如果不存在规格按商品库存查询 */
                        if (empty($prod)) {
                            if ($delivery_stock_result[$i]['model_inventory'] == 1) {
                                $delivery_stock_result[$i]['storage'] = get_warehouse_area_goods($delivery_stock_result[$i]['warehouse_id'], $delivery_stock_result[$i]['goods_id'], 'warehouse_goods');
                            } elseif ($delivery_stock_result[$i]['model_inventory'] == 2) {
                                $delivery_stock_result[$i]['storage'] = get_warehouse_area_goods($delivery_stock_result[$i]['area_id'], $delivery_stock_result[$i]['goods_id'], 'warehouse_area_goods');
                            }
                        } else {
                            $products = $this->goodsWarehouseService->getWarehouseAttrNumber($delivery_stock_result[$i]['goods_id'], $delivery_stock_result[$i]['goods_attr_id'], $delivery_stock_result[$i]['warehouse_id'], $delivery_stock_result[$i]['area_id'], $delivery_stock_result[$i]['area_city'], $delivery_stock_result[$i]['model_attr']);
                            $delivery_stock_result[$i]['storage'] = $products['product_number'];
                        }

                        if (($delivery_stock_result[$i]['sums'] > $delivery_stock_result[$i]['storage'] || $delivery_stock_result[$i]['storage'] <= 0) && ((config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_SHIP) || (config('shop.use_storage') == '0' && $delivery_stock_result[$i]['is_real'] == 0))) {
                            /* 操作失败 */
                            $links[] = ['text' => lang('admin/order.order_info'), 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id];
                            //return sys_msg(sprintf(lang('admin/order.act_good_vacancy'), $value['goods_name']), 1, $links);
                            break;
                        }

                        /* 虚拟商品列表 virtual_card */
                        if ($delivery_stock_result[$i]['is_real'] == 0) {
                            $virtual_goods[] = [
                                'goods_id' => $delivery_stock_result[$i]['goods_id'],
                                'goods_name' => $delivery_stock_result[$i]['goods_name'],
                                'num' => $delivery_stock_result[$i]['send_number']
                            ];
                        }
                    }
                    //ecmoban模板堂 --zhuo end 下单减库存

                    /* 发货 */
                    /* 处理虚拟卡 商品（虚货） */
                    if ($virtual_goods && is_array($virtual_goods) && count($virtual_goods) > 0) {
                        foreach ($virtual_goods as $virtual_value) {
                            virtual_card_shipping($virtual_value, $order['order_sn'], $msg, 'split');
                        }

                        //虚拟卡缺货
                        if (!empty($msg)) {
                            continue;
                        }
                    }

                    /* 如果使用库存，且发货时减库存，则修改库存 */
                    if (config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_SHIP) {
                        foreach ($delivery_stock_result as $value) {

                            /* 商品（实货）、超级礼包（实货） ecmoban模板堂 --zhuo */
                            if ($value['is_real'] != 0) {
                                //（货品）
                                if (!empty($value['product_id'])) {
                                    if ($value['model_attr'] == 1) {
                                        ProductsWarehouse::where('product_id', $value['product_id'])
                                            ->decrement('product_number', $value['sums']);
                                    } elseif ($value['model_attr'] == 2) {
                                        ProductsArea::where('product_id', $value['product_id'])
                                            ->decrement('product_number', $value['sums']);
                                    } else {
                                        Products::where('product_id', $value['product_id'])
                                            ->decrement('product_number', $value['sums']);
                                    }
                                } else {
                                    if ($value['model_inventory'] == 1) {
                                        WarehouseGoods::where('goods_id', $value['goods_id'])
                                            ->where('region_id', $value['warehouse_id'])
                                            ->decrement('region_number', $value['sums']);
                                    } elseif ($value['model_inventory'] == 2) {
                                        WarehouseAreaGoods::where('goods_id', $value['goods_id'])
                                            ->where('region_id', $value['area_id'])
                                            ->decrement('region_number', $value['sums']);
                                    } else {
                                        Goods::where('goods_id', $value['goods_id'])
                                            ->decrement('goods_number', $value['sums']);
                                    }
                                }

                                //库存日志
                                $logs_other = [
                                    'goods_id' => $value['goods_id'],
                                    'order_id' => $value['order_id'],
                                    'use_storage' => config('shop.stock_dec_time'),
                                    'admin_id' => session('admin_id'),
                                    'number' => "- " . $value['sums'],
                                    'model_inventory' => $value['model_inventory'],
                                    'model_attr' => $value['model_attr'],
                                    'product_id' => $value['product_id'],
                                    'warehouse_id' => $value['warehouse_id'],
                                    'area_id' => $value['area_id'],
                                    'add_time' => TimeRepository::getGmTime()
                                ];

                                GoodsInventoryLogs::insert($logs_other);
                            }
                        }
                    }

                    /* 修改发货单信息 */
                    $invoice_no = str_replace(',', ',', $delivery['invoice_no']);
                    $invoice_no = trim($invoice_no, ',');
                    $_delivery['invoice_no'] = $invoice_no;
                    $_delivery['status'] = DELIVERY_SHIPPED; // 0，为已发货
                    $query = DeliveryOrder::where('delivery_id', $delivery_id)->update($_delivery);
                    if (!$query) {
                        /* 操作失败 */
                        $links[] = ['text' => lang('admin/order.delivery_sn') . lang('admin/order.detail'), 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id];
                        //return sys_msg(lang('admin/order.act_false'), 1, $links);
                        continue;
                    }

                    /* 标记订单为已确认 “已发货” */
                    /* 更新发货时间 */
                    $order_finish = $this->orderManageService->getAllDeliveryFinish($order_id);
                    $shipping_status = ($order_finish == 1) ? SS_SHIPPED : SS_SHIPPED_PART;
                    $arr['shipping_status'] = $shipping_status;
                    $arr['shipping_time'] = GMTIME_UTC; // 发货时间
                    $arr['invoice_no'] = trim($order['invoice_no'] . ',' . $invoice_no, ',');
                    update_order($order_id, $arr);

                    if (empty($_delivery['invoice_no'])) {
                        $_delivery['invoice_no'] = "N/A";
                    }

                    $_note = sprintf(lang('admin/order.order_ship_delivery'), $delivery_order['delivery_sn']) . "<br/>" . sprintf(lang('admin/order.order_ship_invoice'), $_delivery['invoice_no']);

                    /* 发货单发货记录log */
                    order_action($order['order_sn'], OS_CONFIRMED, $shipping_status, $order['pay_status'], $action_note, session('admin_name'), 1);

                    /* 如果当前订单已经全部发货 */
                    if ($order_finish) {
                        /* 如果订单用户不为空，计算积分，并发给用户；发红包 */
                        if ($order['user_id'] > 0) {

                            /* 计算并发放积分 */
                            $integral = integral_to_give($order);
                            /* 如果已配送子订单的赠送积分大于0   减去已配送子订单积分 */
                            if (!empty($child_order)) {
                                $integral['custom_points'] = $integral['custom_points'] - $child_order['custom_points'];
                                $integral['rank_points'] = $integral['rank_points'] - $child_order['rank_points'];
                            }
                            log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf(lang('admin/order.order_gift_integral'), $order['order_sn']));

                            /* 发放红包 */
                            send_order_bonus($order_id);

                            /* 发放优惠券 bylu */
                            send_order_coupons($order_id);

                            /* 更新商品销量 批量发货 */
                            get_goods_sale($order_id);

                            /* 更新会员订单信息 */
                            $this->orderCommonService->getUserOrderNumServer($order['user_id']);
                        }

                        /* 发送邮件 */
                        if (config('shop.send_ship_email', 0) == '1') {
                            $order['invoice_no'] = $invoice_no;
                            $tpl = get_mail_template('deliver_notice');
                            $this->smarty->assign('order', $order);
                            $this->smarty->assign('send_time', TimeRepository::getLocalDate(config('shop.time_format')));
                            $this->smarty->assign('shop_name', config('shop.shop_name', ''));
                            $this->smarty->assign('send_date', TimeRepository::getLocalDate(config('shop.time_format'), TimeRepository::getGmTime()));
                            $this->smarty->assign('sent_date', TimeRepository::getLocalDate(config('shop.time_format'), TimeRepository::getGmTime()));
                            $this->smarty->assign('confirm_url', $this->dsc->url() . '/user_order.php?act=order_detail&order_id=' . $order['order_id']); //by wu
                            $this->smarty->assign('send_msg_url', $this->dsc->url() . '/user_message.php?act=message_list&order_id=' . $order['order_id']);
                            $content = $this->smarty->fetch('str:' . $tpl['template_content']);
                            if (!CommonRepository::sendEmail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html'])) {
                                $msg = lang('admin/order.send_mail_fail');
                            }
                        }

                        /* 如果需要，发短信 */
                        if (config('shop.sms_order_shipped') == '1' && $order['mobile'] != '') {

                            //短信接口参数
                            if ($order['ru_id']) {
                                $shop_name = $this->merchantCommonService->getShopName($order['ru_id'], 1);
                            } else {
                                $shop_name = "";
                            }

                            $user_info = get_admin_user_info($order['user_id']);

                            $smsParams = [
                                'shop_name' => $shop_name,
                                'shopname' => $shop_name,
                                'user_name' => $user_info['user_name'],
                                'username' => $user_info['user_name'],
                                'consignee' => $order['consignee'],
                                'order_sn' => $order['order_sn'],
                                'ordersn' => $order['order_sn'],
                                'invoice_no' => $invoice_no,
                                'invoiceno' => $invoice_no,
                                'mobile_phone' => $order['mobile'],
                                'mobilephone' => $order['mobile']
                            ];

                            $this->commonRepository->smsSend($order['mobile'], $smsParams, 'sms_order_shipped', false);
                        }

                        // 微信通模板消息 发货通知
                        if (file_exists(MOBILE_WECHAT)) {
                            $pushData = [
                                'first' => ['value' => lang('wechat::wechat.order_shipping_first')], // 标题
                                'keyword1' => ['value' => $order['order_sn']], //订单
                                'keyword2' => ['value' => $order['shipping_name']], //物流服务
                                'keyword3' => ['value' => $invoice_no], //快递单号
                                'keyword4' => ['value' => $order['consignee']], // 收货信息
                                'remark' => ['value' => lang('wechat::wechat.order_shipping_remark')]
                            ];
                            $shop_url = url('/') . '/'; // 根域名 兼容商家后台
                            $order_url = dsc_url('/#/user/orderDetail/' . $order_id);

                            push_template_curl('OPENTM202243318', $pushData, $order_url, $order['user_id'], $shop_url);
                        }
                    }

                    /* 操作成功 */
                    $links[] = ['text' => lang('admin/common.09_delivery_order'), 'href' => 'order.php?act=delivery_list'];
                    $links[] = ['text' => lang('admin/order.delivery_sn') . lang('admin/order.detail'), 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id];

                    continue;
                }

                /* 返回 */
                return sys_msg(lang('admin/order.batch_delivery_success'), 0, [['href' => 'order.php?act=delivery_list', 'text' => lang('admin/order.return_list')]]);
            }


            /*  @bylu 判断当前退款订单是否为白条支付订单(白条支付订单退款只能退到白条额度) start */
            $baitiao = BaitiaoLog::where('order_id', $order_id)->value('log_id');
            $baitiao = $baitiao ? $baitiao : 0;

            if ($baitiao) {
                $this->smarty->assign('is_baitiao', $baitiao);
            }
            /*  @bylu  end */

            $oid_array = [];
            /* 直接处理还是跳到详细页面 ecmoban模板堂 --zhuo ($require_note && $action_note == '')*/
            if ((isset($require_note) && $require_note) || isset($show_invoice_no) || isset($show_refund) || isset($op_shipping)) {
                //处理批量发货时处理订单状态问题
                if (isset($op_shipping)) {
                    $oid_array = explode(',', $order_id);
                    foreach ($oid_array as $k => $v) {
                        $res = OrderInfo::where('order_sn', $v);
                        $row = BaseRepository::getToArrayFirst($res);

                        if (!empty($row)) {
                            if ($row['shipping_status'] != 0 || ($row['order_status'] != 1 && $row['order_status'] != 5) || $row['pay_status'] != 2) {
                                return sys_msg(lang('admin/order.inspect_order_type'), 1);
                            }
                        }
                    }
                }

                /* 模板赋值 */
                $this->smarty->assign('require_note', $require_note); // 是否要求填写备注
                $this->smarty->assign('action_note', $action_note);   // 备注
                $this->smarty->assign('show_cancel_note', $show_cancel_note ?? false); // 是否显示取消原因
                $this->smarty->assign('show_invoice_no', $show_invoice_no ?? false); // 是否显示发货单号
                $this->smarty->assign('show_refund', $show_refund ?? false); // 是否显示退款
                $this->smarty->assign('show_refund1', $show_refund1 ?? false); // 是否显示退款 // by Leah
                $this->smarty->assign('anonymous', isset($anonymous) ? $anonymous : true); // 是否匿名
                $this->smarty->assign('order_id', $order_id); // 订单id
                $this->smarty->assign('oid_array', $oid_array); // 订单id数组
                $this->smarty->assign('rec_id', $rec_id); // 订单商品id    //by Leah
                $this->smarty->assign('ret_id', $ret_id); // 订单商品id   // by Leah
                $this->smarty->assign('batch', $batch);   // 是否批处理
                $this->smarty->assign('operation', $operation); // 操作
                $this->smarty->assign('show_shipping_sn', $op_shipping ?? false); // 操作

                /* 显示模板 */
                $this->smarty->assign('ur_here', lang('admin/order.order_operate') . $action);

                return $this->smarty->display('order_operate.dwt');
            } else {
                $operation = $operation ?? '';

                /* 直接处理 */
                if (!$batch) {
                    // by　Leah S
                    if (!empty($ret_id)) {
                        return dsc_header("Location: order.php?act=operate_post&order_id=" . $order_id . "&operation=" . $operation . "&action_note=" . urlencode($action_note) . "&rec_id=" . $rec_id . "&ret_id=" . $ret_id . "\n");
                    } else {

                        /* 一个订单 */
                        return dsc_header("Location: order.php?act=operate_post&order_id=" . $order_id . "&operation=" . $operation . "&action_note=" . urlencode($action_note) . "\n");
                    }
                    //by Leah E
                } else {
                    /* 多个订单 */
                    return dsc_header("Location: order.php?act=batch_operate_post&order_id=" . $order_id . "&operation=" . $operation . "&action_note=" . urlencode($action_note) . "\n");
                }
            }
        }

        /*------------------------------------------------------ */
        //-- 操作订单状态（处理批量提交）
        /*------------------------------------------------------ */

        elseif ($act == 'batch_operate_post') {
            /* 检查权限 */
            admin_priv('order_os_edit');

            /* 取得参数 */
            $order_id = $_REQUEST['order_id'];        // 订单id（逗号格开的多个订单id）
            $operation = $_REQUEST['operation'];       // 订单操作
            $action_note = $_REQUEST['action_note'];     // 操作备注

            if ($order_id) {
                $order_id = $this->dscRepository->delStrComma($order_id);
                $order_id_list = explode(',', $order_id);
            } else {
                $order_id_list = [];
            }

            /* 初始化处理的订单sn */
            $sn_list = [];
            $sn_not_list = [];

            /*------------------------------------------------------ */
            //-- 确认（批量）
            /*------------------------------------------------------ */
            if ('confirm' == $operation) {
                foreach ($order_id_list as $id_order) {
                    $res = OrderInfo::where('order_sn', $id_order)->where('order_status', OS_UNCONFIRMED);
                    $order = BaseRepository::getToArrayFirst($res);

                    if ($order) {
                        /* 检查能否操作 */
                        $operable_list = $this->orderManageService->operableList($order, session('action_list'));
                        if (!isset($operable_list[$operation])) {
                            $sn_not_list[] = $id_order;
                            continue;
                        }

                        if ($order['order_status'] == OS_RETURNED || $order['order_status'] == OS_RETURNED_PART) {
                            continue;
                        }

                        $order_id = $order['order_id'];

                        /* 标记订单为已确认 */
                        update_order($order_id, ['order_status' => OS_CONFIRMED, 'confirm_time' => TimeRepository::getGmTime()]);
                        $this->orderManageService->updateOrderAmount($order_id);

                        /* 记录log */
                        order_action($order['order_sn'], OS_CONFIRMED, SS_UNSHIPPED, PS_UNPAYED, $action_note, session('admin_name'));

                        $this->orderCommonService->getUserOrderNumServer($order['user_id'] ?? 0);

                        /* 发送邮件 */
                        if (config('shop.send_confirm_email', 0) == '1') {
                            $tpl = get_mail_template('order_confirm');
                            $order['formated_add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $order['add_time']);
                            $this->smarty->assign('order', $order);
                            $this->smarty->assign('shop_name', config('shop.shop_name', ''));
                            $this->smarty->assign('send_date', TimeRepository::getLocalDate(config('shop.time_format'), TimeRepository::getGmTime()));
                            $this->smarty->assign('sent_date', TimeRepository::getLocalDate(config('shop.time_format'), TimeRepository::getGmTime()));
                            $content = $this->smarty->fetch('str:' . $tpl['template_content']);
                            CommonRepository::sendEmail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html']);
                        }

                        $sn_list[] = $order['order_sn'];
                    } else {
                        $sn_not_list[] = $id_order;
                    }
                }

                $sn_str = lang('admin/order.confirm_order');
            }
            /*------------------------------------------------------ */
            //-- 无效（批量）
            /*------------------------------------------------------ */
            elseif ('invalid' == $operation) {
                foreach ($order_id_list as $id_order) {
                    $res = OrderInfo::where('order_sn', $id_order)->whereRaw('1 ' . $this->orderService->orderQuerySql('unpay_unship'));
                    $order = BaseRepository::getToArrayFirst($res);

                    /*判断门店订单，获取门店id by kong */
                    if ($order) {
                        /* 检查能否操作 */
                        $operable_list = $this->orderManageService->operableList($order, session('action_list'));
                        if (!isset($operable_list[$operation])) {
                            $sn_not_list[] = $id_order;
                            continue;
                        }

                        if ($order['order_status'] == OS_INVALID) {
                            continue;
                        }

                        $order_id = $order['order_id'];

                        /* 标记订单为“无效” */
                        update_order($order_id, ['order_status' => OS_INVALID]);

                        /* 记录log */
                        order_action($order['order_sn'], OS_INVALID, SS_UNSHIPPED, PS_UNPAYED, $action_note, session('admin_name'));

                        /* 如果使用库存，且下订单时减库存，则增加库存 */
                        if (config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_PLACE) {
                            $store_id = get_store_id($order['order_id']);
                            $store_id = $store_id ?? 0;
                            change_order_goods_storage($order_id, false, SDT_PLACE, 2, session('admin_id'), $store_id);
                        }

                        /* 退还用户余额、积分、红包 */
                        return_user_surplus_integral_bonus($order);

                        $this->orderCommonService->getUserOrderNumServer($order['user_id'] ?? 0);

                        OrderInfo::where('order_id', $order['order_id'])->update(['order_amount' => $order['order_amount']]);

                        /* 发送邮件 */
                        if (config('shop.send_invalid_email', 0) == '1') {
                            $tpl = get_mail_template('order_invalid');
                            $this->smarty->assign('order', $order);
                            $this->smarty->assign('shop_name', config('shop.shop_name', ''));
                            $this->smarty->assign('send_date', TimeRepository::getLocalDate(config('shop.time_format'), TimeRepository::getGmTime()));
                            $this->smarty->assign('sent_date', TimeRepository::getLocalDate(config('shop.time_format'), TimeRepository::getGmTime()));
                            $content = $this->smarty->fetch('str:' . $tpl['template_content']);
                            CommonRepository::sendEmail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html']);
                        }

                        $sn_list[] = $order['order_sn'];
                    } else {
                        $sn_not_list[] = $id_order;
                    }
                }

                $sn_str = $GLOBALS['_LANG']['invalid_order'];
            }
            /*------------------------------------------------------ */
            //-- 取消（批量）
            /*------------------------------------------------------ */
            elseif ('cancel' == $operation) {
                foreach ($order_id_list as $id_order) {
                    $res = OrderInfo::where('order_sn', $id_order)->whereRaw('1 ' . $this->orderService->orderQuerySql('unpay_unship'));
                    $order = BaseRepository::getToArrayFirst($res);

                    /*判断门店订单，获取门店id by kong */
                    if ($order) {
                        /* 检查能否操作 */
                        $operable_list = $this->orderManageService->operableList($order, session('action_list'));
                        if (!isset($operable_list[$operation])) {
                            $sn_not_list[] = $id_order;
                            continue;
                        }

                        if ($order['order_status'] == OS_CANCELED) {
                            continue;
                        }

                        $order_id = $order['order_id'];

                        /* 标记订单为“取消”，记录取消原因 */
                        $cancel_note = trim($_REQUEST['cancel_note']);
                        update_order($order_id, ['order_status' => OS_CANCELED, 'to_buyer' => $cancel_note, 'money_paid' => 0, 'pay_status' => PS_UNPAYED, 'pay_time' => 0]);

                        /* 记录log */
                        order_action($order['order_sn'], OS_CANCELED, $order['shipping_status'], PS_UNPAYED, $action_note, session('admin_name'));

                        /* 如果使用库存，且下订单时减库存，则增加库存 */
                        if (config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_PLACE) {
                            $store_id = get_store_id($order['order_id']);
                            $store_id = $store_id ?? 0;
                            change_order_goods_storage($order_id, false, SDT_PLACE, 3, session('admin_id'), $store_id);
                        }

                        /* 退还用户余额、积分、红包 */
                        return_user_surplus_integral_bonus($order);

                        $this->orderCommonService->getUserOrderNumServer($order['user_id'] ?? 0);

                        /* 获取原应付金额 */
                        OrderInfo::where('order_id', $order['order_id'])->update([
                            'order_amount' => $order['order_amount']
                        ]);

                        /* 发送邮件 */
                        if (config('shop.send_cancel_email', 0) == '1') {
                            $tpl = get_mail_template('order_cancel');
                            $this->smarty->assign('order', $order);
                            $this->smarty->assign('shop_name', config('shop.shop_name', ''));
                            $this->smarty->assign('send_date', TimeRepository::getLocalDate(config('shop.time_format'), TimeRepository::getGmTime()));
                            $this->smarty->assign('sent_date', TimeRepository::getLocalDate(config('shop.time_format'), TimeRepository::getGmTime()));
                            $content = $this->smarty->fetch('str:' . $tpl['template_content']);
                            CommonRepository::sendEmail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html']);
                        }

                        $sn_list[] = $order['order_sn'];
                    } else {
                        $sn_not_list[] = $id_order;
                    }
                }

                $sn_str = lang('admin/order.cancel_order');
            }
            /*------------------------------------------------------ */
            //-- 删除（批量）
            /*------------------------------------------------------ */
            elseif ('remove' == $operation) {
                foreach ($order_id_list as $order_sn) {
                    /* 检查能否操作 */
                    $order = OrderInfo::query()->where('order_sn', $order_sn)->first();
                    $order = $order ? $order->toArray() : [];
                    if (empty($order)) {
                        $sn_not_list[] = $order_sn;
                        continue;
                    }

                    $operable_list = $this->orderManageService->operableList($order, session('action_list'));
                    if (!isset($operable_list['remove'])) {
                        $sn_not_list[] = $order_sn;
                        continue;
                    }

                    $order_id = $order['order_id'] ?? 0;
                    $return_order = OrderReturn::query()->where('order_id', $order_id)->count();
                    if ($return_order) {
                        $sn_not_list[] = $order_sn;
                        continue;
                    }

                    // 检查订单是否有未处理投诉
                    $complaint_order = DB::table('complaint')->where('order_id', $order_id)->where('complaint_state', '<>', 4)->count('complaint_id');
                    if ($complaint_order) {
                        $sn_not_list[] = $order_sn;
                        continue;
                    }

                    /* 删除订单 */
                    $res = OrderInfo::where('order_id', $order['order_id'])->delete();
                    if ($res) {

                        $this->orderManageService->delDelivery($order['order_id'], ['delivery', 'back']);

                        // 订单删除事件
                        event(new \App\Events\OrderRemoveEvent($order));

                        /* 记录日志 */
                        admin_log($order['order_sn'], 'remove', 'order');

                        $sn_list[] = $order['order_sn'];
                    }
                }

                $sn_str = lang('admin/order.remove_order');
            } else {
                return 'invalid params';
            }

            if (empty($sn_not_list)) {
                $sn_list = empty($sn_list) ? '' : $GLOBALS['_LANG']['updated_order'] . join($sn_list, ',');
                $msg = $sn_list;
                $links[] = ['text' => lang('admin/order.return_list'), 'href' => 'order.php?act=list&' . list_link_postfix()];
                return sys_msg($msg, 0, $links);
            } else {
                $order_list_no_fail = [];
                $sn_not_list = BaseRepository::getExplode($sn_not_list);
                $res = OrderInfo::whereIn('order_sn', $sn_not_list);
                $res = BaseRepository::getToArrayGet($res);

                foreach ($res as $row) {
                    $order_list_no_fail[$row['order_id']]['order_id'] = $row['order_id'];
                    $order_list_no_fail[$row['order_id']]['order_sn'] = $row['order_sn'];
                    $order_list_no_fail[$row['order_id']]['order_status'] = $row['order_status'];
                    $order_list_no_fail[$row['order_id']]['shipping_status'] = $row['shipping_status'];
                    $order_list_no_fail[$row['order_id']]['pay_status'] = $row['pay_status'];

                    $order_list_fail = '';
                    $operable_list = $this->orderManageService->operableList($row, session('action_list'));
                    foreach ($operable_list as $key => $value) {
                        if ($key != $operation) {
                            $order_list_fail .= isset($GLOBALS['_LANG']['op_' . $key]) ? $GLOBALS['_LANG']['op_' . $key] . ',' : '';
                        }
                    }
                    $order_list_no_fail[$row['order_id']]['operable'] = $order_list_fail;
                }

                /* 模板赋值 */
                $this->smarty->assign('order_info', $sn_str);
                $this->smarty->assign('action_link', ['href' => 'order.php?act=list', 'text' => lang('admin/common.02_order_list')]);
                $this->smarty->assign('order_list', $order_list_no_fail);

                /* 显示模板 */

                return $this->smarty->display('order_operate_info.dwt');
            }
        }

        /*------------------------------------------------------ */
        //-- 操作订单状态（处理提交）
        /*------------------------------------------------------ */

        elseif ($act == 'operate_post') {
            /* 检查权限 */
            admin_priv('order_os_edit');

            /* 取得参数 */
            $order_id = (int)request()->input('order_id', 0); // 订单id

            $rec_id = request()->input('rec_id', 0);
            $ret_id = request()->input('ret_id', 0);
            $goods_delivery = request()->input('delivery', []);// 订单操作

            $operation = request()->input('operation', '');// 订单操作

            /* 查询订单信息 */
            $order = order_info($order_id);

            /*判断门店订单，获取门店id by kong */
            $store_id = get_store_id($order_id);
            $store_id = $store_id ?? 0;

            /* 订单单商品发货操作不检测 */
            if (empty($goods_delivery)) {
                /* 检查能否操作 */
                $operable_list = $this->orderManageService->operableList($order, session('action_list'));
                if (!isset($operable_list[$operation])) {
                    return sys_msg('Hacking attempt', 1);
                }
            }

            /* 取得备注信息 */
            $action_note = e(request()->input('action_note', ''));

            /* 初始化提示信息 */
            $msg = '';

            /*------------------------------------------------------ */
            //-- 确认
            /*------------------------------------------------------ */
            if ('confirm' == $operation) {
                /* 标记订单为已确认 */
                update_order($order_id, ['order_status' => OS_CONFIRMED, 'confirm_time' => TimeRepository::getGmTime()]);
                $this->orderManageService->updateOrderAmount($order_id);

                /* 记录log */
                order_action($order['order_sn'], OS_CONFIRMED, SS_UNSHIPPED, PS_UNPAYED, $action_note, session('admin_name'));

                /* 如果原来状态不是“未确认”，且使用库存，且下订单时减库存，则减少库存 */
                if ($order['order_status'] != OS_UNCONFIRMED && config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_PLACE) {
                    change_order_goods_storage($order_id, true, SDT_PLACE, 4, session('admin_id'), $store_id);
                }

                $this->orderCommonService->getUserOrderNumServer($order['user_id'] ?? 0);

                /* 发送邮件 */
                if (config('shop.send_confirm_email', 0) == '1') {
                    $tpl = get_mail_template('order_confirm');
                    $this->smarty->assign('order', $order);
                    $this->smarty->assign('shop_name', config('shop.shop_name', ''));
                    $this->smarty->assign('send_date', TimeRepository::getLocalDate(config('shop.time_format'), TimeRepository::getGmTime()));
                    $this->smarty->assign('sent_date', TimeRepository::getLocalDate(config('shop.time_format'), TimeRepository::getGmTime()));
                    $content = $this->smarty->fetch('str:' . $tpl['template_content']);
                    if (!CommonRepository::sendEmail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html'])) {
                        $msg = lang('admin/order.send_mail_fail');
                    }
                }

            }

            /*------------------------------------------------------ */
            //-- 付款
            /*------------------------------------------------------ */
            elseif ('pay' == $operation) {
                /* 付款 */
                admin_priv('order_ps_edit');

                $time = TimeRepository::getGmTime();

                /* 标记订单为已确认、已付款，更新付款时间和已支付金额，如果是货到付款，同时修改订单为“收货确认” */
                if ($order['order_status'] == OS_UNCONFIRMED) {
                    $arr['order_status'] = OS_CONFIRMED;
                    $arr['confirm_time'] = $time;
                }
                $arr['pay_time'] = $time;
                //预售定金处理
                if ($order['extension_code'] == 'presale' && $order['pay_status'] == PS_UNPAYED) {
                    $arr['pay_status'] = PS_PAYED_PART;
                    $arr['money_paid'] = $order['money_paid'] + $order['order_amount'];
                    $arr['order_amount'] = $order['goods_amount'] + $order['shipping_fee'] + $order['insure_fee'] + $order['pay_fee'] + $order['tax'] + $order['pack_fee'] + $order['card_fee'] - $order['surplus'] - $order['money_paid'] - $order['integral_money'] - $order['bonus'] - $order['order_amount'] - $order['discount'];
                } else {
                    $arr['pay_status'] = PS_PAYED;
                    $arr['money_paid'] = $order['money_paid'] + $order['order_amount'];
                    $arr['order_amount'] = 0;
                }

                $payment = payment_info($order['pay_id'], 0, ['is_cod', 'pay_code']);
                // 货到付款
                if (isset($payment['is_cod']) && $payment['is_cod'] == 1) {
                    $arr['shipping_status'] = SS_RECEIVED;
                    $order['shipping_status'] = SS_RECEIVED;
                }

                app(JigonManageService::class)->jigonConfirmOrder($order_id); // 贡云确认订单

                // 银行转账支付 确认 虚拟商品发货
                if ($arr['order_amount'] == 0 && isset($payment['pay_code']) && $payment['pay_code'] == 'bank') {
                    $virtual_goods = get_virtual_goods($order_id);
                    if ($virtual_goods) {
                        /* 虚拟卡发货 */
                        $error_msg = '';
                        if (virtual_goods_ship($virtual_goods, $error_msg, $order['order_sn'], true)) {
                            /* 如果没有实体商品，修改发货状态，送积分和红包 */
                            $num = OrderGoods::where('order_id', $order['order_id'])->where('is_real', 1)->count();
                            if ($num <= 0) {
                                /* 修改订单状态 OS_CONFIRMED 1，OS_SPLITED 5  */
                                update_order($order['order_id'], ['order_status' => OS_CONFIRMED, 'shipping_status' => SS_SHIPPED, 'shipping_time' => $time]);

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
                            }
                        }
                    }
                }

                update_order($order_id, $arr);

                /* 更新主订单付款状态 */
                $this->orderCommonService->updateMainOrder($order, 2, $action_note, session('admin_name'));

                /* 众筹状态的更改 by wu */
                update_zc_project($order_id);

                //付款成功创建快照
                create_snapshot($order_id);

                /* 更新商品销量 ecmoban模板堂 --zhuo */
                get_goods_sale($order_id);

                //门店处理 付款成功发送短信
                $res = StoreOrder::where('order_id', $order_id)->select('store_id', 'pick_code', 'order_id');
                $stores_order = BaseRepository::getToArrayFirst($res);
                $store_id = $stores_order['store_id'] ?? 0;

                /* 如果使用库存，且付款时减库存，且订单金额为0，则减少库存 */
                if (config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_PAID) {
                    change_order_goods_storage($order_id, true, SDT_PAID, 15, session('admin_id'), $store_id);
                }

                if (!empty($stores_order) && $store_id > 0) {

                    $res = Users::where('user_id', $order['user_id'])->select('mobile_phone', 'user_name');
                    $orderUsers = BaseRepository::getToArrayFirst($res);

                    if ($order['mobile']) {
                        $user_mobile_phone = $order['mobile'];
                    } else {
                        $user_mobile_phone = $orderUsers['mobile_phone'] ?? '';
                    }
                    if (!empty($user_mobile_phone)) {
                        //门店短信处理
                        $user_name = !empty($orderUsers['user_name']) ? $orderUsers['user_name'] : '';
                        //门店订单->短信接口参数
                        $store_smsParams = [
                            'user_name' => $user_name,
                            'username' => $user_name,
                            'order_sn' => $order['order_sn'],
                            'ordersn' => $order['order_sn'],
                            'code' => $stores_order['pick_code']
                        ];

                        $this->commonRepository->smsSend($user_mobile_phone, $store_smsParams, 'store_order_code');
                    }
                }

                $confirm_take_time = TimeRepository::getGmTime();

                $arr['order_status'] = $arr['order_status'] ?? $order['order_status'];
                $arr['shipping_status'] = $arr['shipping_status'] ?? $order['shipping_status'];

                if ((in_array($arr['order_status'], [OS_CONFIRMED, OS_SPLITED, OS_RETURNED_PART, OS_ONLY_REFOUND])) && $arr['shipping_status'] == SS_RECEIVED && (isset($arr['pay_status'])) && $arr['pay_status'] == PS_PAYED) {

                    /* 查询订单信息，检查状态 */
                    $res = OrderInfo::where('order_id', $order_id);

                    $res = $res->with([
                        'getSellerNegativeOrder'
                    ]);

                    $bill_order = BaseRepository::getToArrayFirst($res);

                    $value_card = ValueCardRecord::where('order_id', $order_id)
                        ->where('add_val', 0)
                        ->value('use_val');
                    $value_card = $value_card ? $value_card : 0;

                    if (!empty($bill_order)) {
                        if (empty($bill_order['get_seller_negative_order'])) {
                            $return_amount_info = $this->orderRefoundService->orderReturnAmount($order_id);
                        } else {
                            $return_amount_info['return_amount'] = 0;
                            $return_amount_info['return_rate_price'] = 0;
                            $return_amount_info['ret_id'] = [];
                        }

                        if ($bill_order['order_amount'] > 0 && $bill_order['order_amount'] > $bill_order['rate_fee']) {
                            $order_amount = $bill_order['order_amount'] - $bill_order['rate_fee'];
                        } else {
                            $order_amount = $bill_order['order_amount'];
                        }

                        $other = [
                            'user_id' => $bill_order['user_id'],
                            'seller_id' => $bill_order['ru_id'],
                            'order_id' => $bill_order['order_id'],
                            'order_sn' => $bill_order['order_sn'],
                            'order_status' => $bill_order['order_status'],
                            'shipping_status' => $bill_order['shipping_status'],
                            'pay_status' => $bill_order['pay_status'],
                            'order_amount' => $order_amount,
                            'return_amount' => $return_amount_info['return_amount'],
                            'goods_amount' => $bill_order['goods_amount'],
                            'tax' => $bill_order['tax'],
                            'shipping_fee' => $bill_order['shipping_fee'],
                            'insure_fee' => $bill_order['insure_fee'],
                            'pay_fee' => $bill_order['pay_fee'],
                            'pack_fee' => $bill_order['pack_fee'],
                            'card_fee' => $bill_order['card_fee'],
                            'bonus' => $bill_order['bonus'],
                            'integral_money' => $bill_order['integral_money'],
                            'coupons' => $bill_order['coupons'],
                            'discount' => $bill_order['discount'],
                            'dis_amount' => $bill_order['dis_amount'],
                            'vc_dis_money' => $bill_order['vc_dis_money'],
                            'value_card' => $value_card,
                            'money_paid' => $bill_order['money_paid'],
                            'surplus' => $bill_order['surplus'],
                            'confirm_take_time' => $confirm_take_time,
                            'rate_fee' => $bill_order['rate_fee'] ?? 0,
                            'return_rate_fee' => $return_amount_info['return_rate_price'] ?? 0,
                            'divide_channel' => $bill_order['divide_channel'] ?? 0,
                        ];

                        if ($bill_order['ru_id'] > 0 && $bill_order['main_count'] == 0) {
                            $this->commissionService->getOrderBillLog($other);
                            $this->commissionService->setBillOrderReturn($return_amount_info['ret_id'], $other['order_id']);
                        }

                        // 确认收货 购买成为分销商商品 绑定权益卡
                        if (file_exists(MOBILE_DRP) && $bill_order['pay_status'] == PS_PAYED) {
                            app(\App\Modules\Drp\Services\Drp\DrpService::class)->buyGoodsUpdateDrpShop($bill_order);
                        }
                    }
                }

                if (isset($arr['pay_status'])) {
                    SellerBillOrder::where('order_id', $order_id)->update([
                        'pay_status' => $arr['pay_status']
                    ]);
                }

                /* 记录log */
                if ($order['extension_code'] == 'presale' && $order['pay_status'] == 0) {
                    order_action($order['order_sn'], $arr['order_status'], $order['shipping_status'], PS_PAYED_PART, $action_note, session('admin_name'));
                    /* 更新 pay_log */
                    $this->orderCommonService->updateOrderPayLog($order_id);
                } else {
                    order_action($order['order_sn'], $arr['order_status'], $order['shipping_status'], PS_PAYED, $action_note, session('admin_name'), 0, $confirm_take_time);
                }

                /* 更新会员订单信息 */
                $this->orderCommonService->getUserOrderNumServer($order['user_id'] ?? 0);
            }

            /*------------------------------------------------------ */
            //-- 未付款
            /*------------------------------------------------------ */
            elseif ('unpay' == $operation) {
                /* 设为未付款 */
                admin_priv('order_ps_edit');

                $return_amount = !empty($_REQUEST['refound_amount']) ? floatval($_REQUEST['refound_amount']) : 0; //退款金额
                $is_shipping = isset($_REQUEST['is_shipping']) && !empty($_REQUEST['is_shipping']) ? intval($_REQUEST['is_shipping']) : 0; //是否退运费
                $shippingFee = !empty($is_shipping) ? floatval($_REQUEST['shipping']) : 0; //退款运费金额
                $vc_id = !empty($_REQUEST['vc_id']) ? intval($_REQUEST['vc_id']) : 0;
                $refound_vcard = !empty($_REQUEST['refound_vcard']) ? floatval($_REQUEST['refound_vcard']) : 0;
                $shipping_fee_from = !empty($_REQUEST['shipping_fee_from']) ? intval($_REQUEST['shipping_fee_from']) : 1;//快递费从哪种支付方式扣除 1.由余额/在线支付抵扣  2.由储值卡抵扣

                $refund_amount = ($shipping_fee_from == 1 || $shipping_fee_from == 0) ? $return_amount + $shippingFee : $return_amount;
                $refound_vcard = $shipping_fee_from == 2 ? $refound_vcard + $shippingFee : $refound_vcard;

                if ($shippingFee > $order['shipping_fee']) {
                    $shippingFee = $order['shipping_fee'];
                }

                $shipping_fee = $order['shipping_fee'] - $shippingFee;

                /* 退款金额 start */
                $order_amount = $order['money_paid'] + $order['surplus'];
                if ($return_amount > $order_amount) {
                    $return_amount = $order_amount;
                }
                /* 退款金额 end */

                $order_amount = $order['money_paid'] + $order['surplus'] - $return_amount;

                /* 标记订单为未付款，更新付款时间和已付款金额 */
                $arr = [
                    'pay_status' => PS_UNPAYED,
                    'pay_time' => 0,
                    'money_paid' => $order_amount,
                    'surplus' => 0,
                    'shipping_fee' => $shipping_fee,
                    'order_amount' => $return_amount + $refound_vcard,
                    'vc_dis_money' => 0,//储值卡折扣初始化
                ];
                update_order($order_id, $arr);

                /* 退回订单消费储值卡金额 */
                if ($vc_id && $refound_vcard > 0) {
                    $this->orderManageService->unpayOrderReturnVcard($order_id, $vc_id, $refound_vcard, $order['user_id'], $order['order_sn']);
                }

                // 减去商品销量
                decrement_goods_sale($order_id);

                /* 如果使用库存，且付款时减库存，则增加库存 */
                if (config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_PAID) {
                    change_order_goods_storage($order_id, false, SDT_PAID, 15, session('admin_id'));
                }

                /* 处理退款 */
                $refund_type = isset($_REQUEST['refund']) && !empty($_REQUEST['refund']) ? trim($_REQUEST['refund']) : '';
                $refund_note = isset($_REQUEST['refund_note']) && !empty($_REQUEST['refund_note']) ? trim($_REQUEST['refund_note']) : '';

                if ($refund_note) {
                    $refund_note = "【" . lang('admin/order.setorder_nopay') . "】【" . $order['order_sn'] . "】" . $refund_note;
                }

                order_refund($order, $refund_type, $refund_note, $refund_amount);

                /* 记录log */
                order_action($order['order_sn'], OS_CONFIRMED, SS_UNSHIPPED, PS_UNPAYED, $action_note, session('admin_name'));

                // 添加跳转链接 至 用户提现申请
                $link[] = [
                    'text' => lang('admin::user_account.put_forward_apply'), 'href' => 'user_account.php?act=list&process_type=1'
                ];

            }

            /*------------------------------------------------------ */
            //-- 确认
            /*------------------------------------------------------ */
            elseif ('prepare' == $operation) {
                /* 标记订单为已确认，配货中 */
                if ($order['order_status'] != OS_CONFIRMED) {
                    $arr['order_status'] = OS_CONFIRMED;
                    $arr['confirm_time'] = TimeRepository::getGmTime();
                }
                $arr['shipping_status'] = SS_PREPARING;
                update_order($order_id, $arr);

                /* 记录log */
                order_action($order['order_sn'], OS_CONFIRMED, SS_PREPARING, $order['pay_status'], $action_note, session('admin_name'));

                /* 清除缓存 */
                clear_cache_files();
            }

            /*------------------------------------------------------ */
            //-- 分单确认
            /*------------------------------------------------------ */
            elseif ('split' == $operation) {
                /* 分单确认 */
                admin_priv('order_ss_edit');

                /* 定义当前时间 */
                define('GMTIME_UTC', TimeRepository::getGmTime()); // 获取 UTC 时间戳

                $delivery_info = get_delivery_info($order_id);
                if (true) {
                    /* 获取表单提交数据 */
                    $_REQUEST['send_number'] = isset($_REQUEST['send_number']) ? $_REQUEST['send_number'] : []; //众筹发货 by wu
                    $suppliers_id = isset($_REQUEST['suppliers_id']) ? intval(trim($_REQUEST['suppliers_id'])) : '0';
                    array_walk($_REQUEST['delivery'], [$this, "trimArrayWalk"]);
                    $delivery = $_REQUEST['delivery'];
                    $action_note = isset($_REQUEST['action_note']) ? trim($_REQUEST['action_note']) : '';
                    $delivery['user_id'] = intval($delivery['user_id']);
                    $delivery['country'] = intval($delivery['country']);
                    $delivery['province'] = intval($delivery['province']);
                    $delivery['city'] = intval($delivery['city']);
                    $delivery['district'] = intval($delivery['district']);
                    $delivery['agency_id'] = intval($delivery['agency_id']);
                    $delivery['insure_fee'] = floatval($delivery['insure_fee']);
                    $delivery['shipping_fee'] = floatval($delivery['shipping_fee']);

                    /*超值礼包商品是否已经缺货*/
                    if ($_REQUEST['send_number']) {
                        foreach ($_REQUEST['send_number'] as $key => $val) {
                            if ($val && is_array($val)) {
                                foreach ($val as $k => $v) {
                                    if (intval($v) <= 0) {
                                        $msg = $_REQUEST['send_number'][$key][$k];
                                        /* 操作失败 */
                                        $links[] = ['text' => lang('admin/order.order_info'), 'href' => 'order.php?act=info&order_id=' . $order_id];
                                        return sys_msg($msg, 1, $links);
                                    }
                                }
                            }
                        }
                    }

                    array_walk($_REQUEST['send_number'], [$this, "trimArrayWalk"]);
                    array_walk($_REQUEST['send_number'], [$this, "intvalArrayWalk"]);
                    $send_number = $_REQUEST['send_number'];

                    /* 订单是否已全部分单检查 */
                    if ($order['order_status'] == OS_SPLITED) {
                        /* 操作失败 */
                        $links[] = ['text' => lang('admin/order.order_info'), 'href' => 'order.php?act=info&order_id=' . $order_id];
                        return sys_msg(sprintf(lang('admin/order.order_splited_sms'), $order['order_sn'], $GLOBALS['_LANG']['os'][OS_SPLITED], $GLOBALS['_LANG']['ss'][SS_SHIPPED_ING], config('shop.shop_name', '')), 1, $links);
                    }

                    /* 取得订单商品 */
                    $orderInfo = OrderInfo::where('order_id', $order_id)->select('is_zc_order', 'add_time');
                    $orderInfo = BaseRepository::getToArrayFirst($orderInfo);

                    $_goods = $this->orderManageService->getOrderGoods(['order_id' => $order_id, 'order_sn' => $delivery['order_sn']]);
                    $goods_list = $_goods['goods_list'];

                    /* 检查此单发货数量填写是否正确 合并计算相同商品和货品 */
                    if (!empty($send_number) && !empty($goods_list)) {
                        $goods_no_package = [];
                        foreach ($goods_list as $key => $value) {
                            /* 去除 此单发货数量 等于 0 的商品 */
                            if (!isset($value['package_goods_list']) || !is_array($value['package_goods_list'])) {
                                // 如果是货品则键值为商品ID与货品ID的组合
                                $_key = empty($value['product_id']) ? $value['goods_id'] : ($value['goods_id'] . '_' . $value['product_id']);

                                // 统计此单商品总发货数 合并计算相同ID商品或货品的发货数
                                if (!isset($goods_no_package[$_key]) || empty($goods_no_package[$_key])) {
                                    if (isset($send_number[$value['rec_id']]) && $send_number[$value['rec_id']] > 0) {
                                        $goods_no_package[$_key] = $send_number[$value['rec_id']];
                                    }
                                } else {
                                    if (isset($send_number[$value['rec_id']]) && $send_number[$value['rec_id']] > 0) {
                                        $goods_no_package[$_key] += $send_number[$value['rec_id']];
                                    }
                                }

                                //去除
                                if (!isset($send_number[$value['rec_id']]) || $send_number[$value['rec_id']] <= 0) {
                                    unset($send_number[$value['rec_id']], $goods_list[$key]);
                                    continue;
                                }
                            } else {
                                /* 组合超值礼包信息 */
                                $goods_list[$key]['package_goods_list'] = $this->orderManageService->packageGoods($value['package_goods_list'], $value['goods_number'], $value['order_id'], $value['extension_code'], $value['goods_id']);

                                /* 超值礼包 */
                                foreach ($value['package_goods_list'] as $pg_key => $pg_value) {
                                    // 如果是货品则键值为商品ID与货品ID的组合
                                    $_key = empty($pg_value['product_id']) ? $pg_value['goods_id'] : ($pg_value['goods_id'] . '_' . $pg_value['product_id']);

                                    //统计此单商品总发货数 合并计算相同ID产品的发货数
                                    if (isset($send_number[$value['rec_id']]) && $send_number[$value['rec_id']] > 0) {
                                        if (empty($goods_no_package[$_key])) {
                                            $goods_no_package[$_key] = $send_number[$value['rec_id']][$pg_value['g_p']];
                                        } //否则已经存在此键值
                                        else {
                                            $goods_no_package[$_key] += $send_number[$value['rec_id']][$pg_value['g_p']];
                                        }
                                    }

                                    //去除
                                    if (!isset($send_number[$value['rec_id']]) || $send_number[$value['rec_id']][$pg_value['g_p']] <= 0) {
                                        unset($send_number[$value['rec_id']][$pg_value['g_p']], $goods_list[$key]['package_goods_list'][$pg_key]);
                                    }
                                }

                                if (count($goods_list[$key]['package_goods_list']) <= 0) {
                                    unset($send_number[$value['rec_id']], $goods_list[$key]);
                                    continue;
                                }
                            }

                            /* 发货数量与总量不符 */
                            if (!isset($value['package_goods_list']) || !is_array($value['package_goods_list'])) {
                                $sended = $this->orderManageService->orderDeliveryNum($order_id, $value['goods_id'], $value['product_id']);
                                if (($value['goods_number'] - $sended - $send_number[$value['rec_id']]) < 0) {
                                    /* 操作失败 */
                                    $links[] = ['text' => lang('admin/order.order_info'), 'href' => 'order.php?act=info&order_id=' . $order_id];
                                    return sys_msg(lang('admin/order.act_ship_num'), 1, $links);
                                }
                            } else {
                                /* 超值礼包 */
                                foreach ($goods_list[$key]['package_goods_list'] as $pg_key => $pg_value) {
                                    if (($pg_value['order_send_number'] - $pg_value['sended'] - $send_number[$value['rec_id']][$pg_value['g_p']]) < 0) {
                                        /* 操作失败 */
                                        $links[] = ['text' => lang('admin/order.order_info'), 'href' => 'order.php?act=info&order_id=' . $order_id];
                                        return sys_msg(lang('admin/order.act_ship_num'), 1, $links);
                                    }
                                }
                            }
                        }
                    }

                    // 众筹发货
                    $is_zc_order = $orderInfo['is_zc_order'] ?? 0;

                    /* 对上一步处理结果进行判断 兼容 上一步判断为假情况的处理 */
                    if ($is_zc_order == 0 && (empty($send_number) || empty($goods_list))) {
                        /* 操作失败 */
                        $links[] = ['text' => lang('admin/order.order_info'), 'href' => 'order.php?act=info&order_id=' . $order_id];
                        return sys_msg(lang('admin/order.act_false'), 1, $links);
                    }

                    /* 检查此单发货商品库存缺货情况 */
                    /* $goods_list已经过处理 超值礼包中商品库存已取得 */
                    $virtual_goods = [];
                    $package_virtual_goods = [];

                    foreach ($goods_list as $key => $value) {
                        // 商品（超值礼包）
                        if ($value['extension_code'] == 'package_buy') {
                            foreach ($value['package_goods_list'] as $pg_key => $pg_value) {
                                if ($pg_value['goods_number'] < $goods_no_package[$pg_value['g_p']] && ((config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_SHIP) || (config('shop.use_storage') == '0' && $pg_value['is_real'] == 0))) {
                                    /* 操作失败 */
                                    $links[] = ['text' => lang('admin/order.order_info'), 'href' => 'order.php?act=info&order_id=' . $order_id];
                                    return sys_msg(sprintf(lang('admin/order.act_good_vacancy'), $pg_value['goods_name']), 1, $links);
                                }

                                /* 商品（超值礼包） 虚拟商品列表 package_virtual_goods*/
                                if ($pg_value['is_real'] == 0) {
                                    $package_virtual_goods[] = [
                                        'goods_id' => $pg_value['goods_id'],
                                        'goods_name' => $pg_value['goods_name'],
                                        'num' => $send_number[$value['rec_id']][$pg_value['g_p']]
                                    ];
                                }
                            }
                        } // 商品（虚货）
                        elseif ($value['extension_code'] == 'virtual_card' || $value['is_real'] == 0) {
                            $num = VirtualCard::where('goods_id', $value['goods_id'])
                                ->where('is_saled', 0)
                                ->count();

                            if (($num < $goods_no_package[$value['goods_id']]) && !(config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_PLACE)) {
                                /* 操作失败 */
                                $links[] = ['text' => lang('admin/order.order_info'), 'href' => 'order.php?act=info&order_id=' . $order_id];
                                return sys_msg(sprintf(lang('admin/common.virtual_card_oos') . '【' . $value['goods_name'] . '】'), 1, $links);
                            }

                            /* 虚拟商品列表 virtual_card*/
                            if ($value['extension_code'] == 'virtual_card') {
                                $virtual_goods[$value['extension_code']][] = ['goods_id' => $value['goods_id'], 'goods_name' => $value['goods_name'], 'num' => $send_number[$value['rec_id']]];
                            }
                        } // 商品（实货）、（货品）
                        else {
                            //如果是货品则键值为商品ID与货品ID的组合
                            $_key = empty($value['product_id']) ? $value['goods_id'] : ($value['goods_id'] . '_' . $value['product_id']);
                            $num = $value['storage']; //ecmoban模板堂 --zhuo

                            if (($num < $goods_no_package[$_key]) && config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_SHIP) {
                                /* 操作失败 */
                                $links[] = ['text' => lang('admin/order.order_info'), 'href' => 'order.php?act=info&order_id=' . $order_id];
                                return sys_msg(sprintf(lang('admin/order.act_good_vacancy'), $value['goods_name']), 1, $links);
                            }
                        }
                    }

                    /* 生成发货单 */
                    /* 获取发货单号和流水号 */
                    $delivery['delivery_sn'] = get_delivery_sn();
                    $delivery_sn = $delivery['delivery_sn'];
                    /* 获取当前操作员 */
                    $delivery['action_user'] = session('admin_name');
                    /* 获取发货单生成时间 */
                    $delivery['update_time'] = GMTIME_UTC;
                    $delivery_time = $delivery['update_time'];
                    $delivery['add_time'] = $orderInfo['add_time'] ?? GMTIME_UTC;

                    /* 获取发货单所属供应商 */
                    $delivery['suppliers_id'] = $suppliers_id;
                    /* 设置默认值 */
                    $delivery['status'] = DELIVERY_CREATE; // 正常
                    $delivery['order_id'] = $order_id;
                    /* 设置众筹标识 */
                    $delivery['is_zc_order'] = $is_zc_order;
                    /* 过滤字段项 */
                    $filter_fileds = [
                        'order_sn', 'add_time', 'user_id', 'how_oos', 'shipping_id', 'shipping_fee',
                        'consignee', 'address', 'country', 'province', 'city', 'district', 'sign_building',
                        'email', 'zipcode', 'tel', 'mobile', 'best_time', 'postscript', 'insure_fee',
                        'agency_id', 'delivery_sn', 'action_user', 'update_time',
                        'suppliers_id', 'status', 'order_id', 'shipping_name', 'is_zc_order'
                    ];
                    $_delivery = [];
                    foreach ($filter_fileds as $value) {
                        $_delivery[$value] = $delivery[$value];
                    }

                    /* 发货单入库 */
                    $delivery_id = DeliveryOrder::insertGetId($_delivery);

                    if ($delivery_id) {
                        $delivery_goods = [];

                        //发货单商品入库
                        if (!empty($goods_list)) {
                            //分单操作
                            $split_action_note = "";

                            foreach ($goods_list as $value) {
                                // 商品（实货）（虚货）
                                if (empty($value['extension_code']) || in_array($value['extension_code'], ['virtual_card', 'presale', 'bargain_buy', 'bargain']) || substr($value['extension_code'], 0, 7) == 'seckill') {
                                    $delivery_goods = ['delivery_id' => $delivery_id,
                                        'goods_id' => $value['goods_id'],
                                        'product_id' => $value['product_id'],
                                        'product_sn' => $value['product_sn'],
                                        'goods_name' => addslashes($value['goods_name']),
                                        'brand_name' => addslashes($value['brand_name']),
                                        'goods_sn' => $value['goods_sn'],
                                        'send_number' => $send_number[$value['rec_id']],
                                        'parent_id' => 0,
                                        'is_real' => $value['is_real'],
                                        'goods_attr' => addslashes($value['goods_attr'])
                                    ];

                                    /* 如果是货品 */
                                    if (!empty($value['product_id'])) {
                                        $delivery_goods['product_id'] = $value['product_id'];
                                    }

                                    $query = DeliveryGoods::insert($delivery_goods);

                                    //分单操作
                                    $split_action_note .= sprintf(lang('admin/order.split_action_note'), $value['goods_sn'], $send_number[$value['rec_id']]) . "<br/>";
                                } // 商品（超值礼包）
                                elseif ($value['extension_code'] == 'package_buy') {
                                    foreach ($value['package_goods_list'] as $pg_key => $pg_value) {
                                        $delivery_pg_goods = [
                                            'delivery_id' => $delivery_id,
                                            'goods_id' => $pg_value['goods_id'],
                                            'product_id' => $pg_value['product_id'],
                                            'product_sn' => $pg_value['product_sn'],
                                            'goods_name' => $pg_value['goods_name'],
                                            'brand_name' => '',
                                            'goods_sn' => $pg_value['goods_sn'],
                                            'send_number' => $send_number[$value['rec_id']][$pg_value['g_p']],
                                            'parent_id' => $value['goods_id'], // 礼包ID
                                            'extension_code' => $value['extension_code'], // 礼包
                                            'is_real' => $pg_value['is_real']
                                        ];
                                        $query = DeliveryGoods::insert($delivery_pg_goods);
                                    }

                                    //分单操作
                                    $split_action_note .= sprintf(lang('admin/order.split_action_note'), lang('admin/common.14_package_list'), 1) . "<br/>";
                                }
                            }
                        }
                    } else {
                        /* 操作失败 */
                        $links[] = ['text' => lang('admin/order.order_info'), 'href' => 'order.php?act=info&order_id=' . $order_id];
                        return sys_msg(lang('admin/order.act_false'), 1, $links);
                    }

                    $_note = sprintf(lang('admin/order.order_ship_delivery'), $delivery['delivery_sn']) . "<br/>";

                    unset($filter_fileds, $delivery, $_delivery, $order_finish);

                    /* 定单信息 */
                    $_sended = &$send_number;
                    foreach ($_goods['goods_list'] as $key => $value) {
                        if ($value['extension_code'] != 'package_buy') {
                            unset($_goods['goods_list'][$key]);
                        }
                    }
                    foreach ($goods_list as $key => $value) {
                        if ($value['extension_code'] == 'package_buy') {
                            unset($goods_list[$key]);
                        }
                    }
                    $_goods['goods_list'] = $goods_list + $_goods['goods_list'];
                    unset($goods_list);

                    /* 更新订单的虚拟卡 商品（虚货） */
                    $_virtual_goods = isset($virtual_goods['virtual_card']) ? $virtual_goods['virtual_card'] : '';
                    $this->orderManageService->updateOrderVirtualGoods($order_id, $_sended, $_virtual_goods);

                    /* 更新订单的非虚拟商品信息 即：商品（实货）（货品）、商品（超值礼包）*/
                    $this->orderManageService->updateOrderGoods($order_id, $_sended, $_goods['goods_list']);

                    /* 标记订单为已确认 “发货中” */
                    /* 更新发货时间 */
                    $order_finish = $this->orderManageService->getOrderFinish($order_id);
                    $shipping_status = SS_SHIPPED_ING;
                    if ($order['order_status'] != OS_CONFIRMED && $order['order_status'] != OS_SPLITED && $order['order_status'] != OS_SPLITING_PART) {
                        $arr['order_status'] = OS_CONFIRMED;
                        $arr['confirm_time'] = GMTIME_UTC;
                    }
                    $arr['order_status'] = $order_finish ? OS_SPLITED : OS_SPLITING_PART; // 全部分单、部分分单
                    $arr['shipping_status'] = $shipping_status;
                    update_order($order_id, $arr);

                    $this->orderCommonService->updateMainOrder($order, 1, $action_note, session('admin_name'));

                    /* 分单操作 */
                    $action_note = $split_action_note . $action_note;

                    /* 记录log */
                    order_action($order['order_sn'], $arr['order_status'], $shipping_status, $order['pay_status'], $_note . $action_note, session('admin_name'));

                    /* 清除缓存 */
                    clear_cache_files();
                }
            }

            /*------------------------------------------------------ */
            //-- 未发货
            /*------------------------------------------------------ */
            elseif ('unship' == $operation) {
                /* 设为未发货 */
                admin_priv('order_ss_edit');

                /* 标记订单为“未发货”，更新发货时间, 订单状态为“确认” */
                update_order($order_id, ['shipping_status' => SS_UNSHIPPED, 'shipping_time' => 0, 'invoice_no' => '', 'order_status' => OS_CONFIRMED]);

                /* 记录log */
                order_action($order['order_sn'], $order['order_status'], SS_UNSHIPPED, $order['pay_status'], $action_note, session('admin_name'));

                /* 如果订单用户不为空，计算积分，并退回 */
                if ($order['user_id'] > 0) {
                    /* 计算并退回积分 */
                    $integral = integral_to_give($order);
                    log_account_change($order['user_id'], 0, 0, (-1) * intval($integral['rank_points']), (-1) * intval($integral['custom_points']), sprintf(lang('admin/order.return_order_gift_integral'), $order['order_sn']));

                    /* 计算并退回红包 */
                    return_order_bonus($order_id);
                }

                // 减去商品销量
                decrement_goods_sale($order_id);

                /* 如果使用库存，则增加库存 */
                if (config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_SHIP) {
                    change_order_goods_storage($order['order_id'], false, SDT_SHIP, 5, session('admin_id'), $store_id);
                }

                /* 删除发货单 */
                $this->orderManageService->delOrderDelivery($order_id);

                // 删除物流记录
                $delivery_id = DeliveryOrder::where('order_id', $order_id)->where('status', 0)->value('delivery_id');
                $this->expressManageService->removeExpressHistory($delivery_id);

                /* 将订单的商品发货数量更新为 0 */
                OrderGoods::where('order_id', $order_id)->update(['send_number' => 0]);

                /* 清除缓存 */
                clear_cache_files();
            }

            /*------------------------------------------------------ */
            //-- 收货确认
            /*------------------------------------------------------ */
            elseif ('receive' == $operation) {
                /* 收货确认 */
                $confirm_take_time = TimeRepository::getGmTime();

                /* 标记订单为“收货确认”，如果是货到付款，同时修改订单为已付款 */
                $arr = ['shipping_status' => SS_RECEIVED, 'confirm_take_time' => $confirm_take_time];
                $payment = payment_info($order['pay_id']);
                if (isset($payment['is_cod']) && $payment['is_cod']) {
                    $arr['pay_status'] = PS_PAYED;
                    $order['pay_status'] = PS_PAYED;
                }
                update_order($order_id, $arr);

                $this->orderCommonService->updateMainOrder($order, 4, $action_note, session('admin_name'));

                if (config('shop.sms_order_received') == '1') {
                    //阿里大鱼短信接口参数
                    if (isset($order['mobile']) && $order['mobile']) {
                        //获得会员信息与店铺信息
                        $user_name = Users::where('user_id', $order['user_id'])->value('user_name');
                        $user_name = $user_name ?? '';

                        $shopinfo = SellerShopinfo::where('ru_id', $order['ru_id'])->select('shop_name');
                        $shopinfo = BaseRepository::getToArrayFirst($shopinfo);
                        $smsParams = [
                            'ordersn' => $order['order_sn'],
                            'shop_name' => $shopinfo['shop_name'],
                            'username' => $user_name
                        ];

                        $this->commonRepository->smsSend($order['mobile'], $smsParams, 'sms_shop_order_received');
                    }
                }

                /* 更新会员订单信息 */
                $dbRaw = [
                    'order_isfinished' => "order_isfinished + 1"
                ];

                $order_nogoods = UserOrderNum::where('user_id', $order['user_id'])->value('order_nogoods');
                $order_nogoods = $order_nogoods ? $order_nogoods : 0;

                if ($order_nogoods > 0) {
                    $dbRaw['order_nogoods'] = "order_nogoods - 1";
                }

                $dbRaw = BaseRepository::getDbRaw($dbRaw);
                UserOrderNum::where('user_id', $order['user_id'])->update($dbRaw);

                //更改智能权重里的商品统计数量
                $res = OrderGoods::where('order_id', $order_id)->select('goods_id', 'goods_number');
                $res = BaseRepository::getToArrayGet($res);

                foreach ($res as $val) {
                    $user_number = OrderGoods::where('goods_id', $val['goods_id'])->select('user_id')->groupBy('user_id');
                    $user_number = BaseRepository::getToArrayGet($user_number);
                    $user_number = count($user_number);

                    $num = ['goods_number' => $val['goods_number'], 'goods_id' => $val['goods_id'], 'user_number' => $user_number];
                    update_manual($val['goods_id'], $num);
                }

                /* 记录log */
                order_action($order['order_sn'], $order['order_status'], SS_RECEIVED, $order['pay_status'], $action_note, session('admin_name'), 0, $confirm_take_time);

                $bill_order = $this->commissionService->getBillOrder($order['order_id']);

                if (!$bill_order) {
                    $value_card = ValueCardRecord::where('order_id', $order['order_id'])
                        ->where('add_val', 0)
                        ->value('use_val');
                    $value_card = $value_card ? $value_card : 0;

                    if (empty($order['get_seller_negative_order'])) {
                        $return_amount_info = $this->orderRefoundService->orderReturnAmount($order['order_id']);
                    } else {
                        $return_amount_info['return_amount'] = 0;
                        $return_amount_info['return_rate_price'] = 0;
                        $return_amount_info['ret_id'] = [];
                    }

                    if ($order['order_amount'] > 0 && $order['order_amount'] > $order['rate_fee']) {
                        $order_amount = $order['order_amount'] - $order['rate_fee'];
                    } else {
                        $order_amount = $order['order_amount'];
                    }

                    $other = [
                        'user_id' => $order['user_id'],
                        'seller_id' => $order['ru_id'],
                        'order_id' => $order['order_id'],
                        'order_sn' => $order['order_sn'],
                        'order_status' => $order['order_status'],
                        'shipping_status' => SS_RECEIVED,
                        'pay_status' => $order['pay_status'],
                        'order_amount' => $order_amount,
                        'return_amount' => $return_amount_info['return_amount'],
                        'goods_amount' => $order['goods_amount'],
                        'tax' => $order['tax'],
                        'shipping_fee' => $order['shipping_fee'],
                        'insure_fee' => $order['insure_fee'],
                        'pay_fee' => $order['pay_fee'],
                        'pack_fee' => $order['pack_fee'],
                        'card_fee' => $order['card_fee'],
                        'bonus' => $order['bonus'],
                        'integral_money' => $order['integral_money'],
                        'coupons' => $order['coupons'],
                        'discount' => $order['discount'],
                        'dis_amount' => $order['dis_amount'],
                        'vc_dis_money' => $order['vc_dis_money'],
                        'value_card' => $value_card,
                        'money_paid' => $order['money_paid'],
                        'surplus' => $order['surplus'],
                        'confirm_take_time' => $confirm_take_time,
                        'rate_fee' => $order['rate_fee'] ?? 0,
                        'return_rate_fee' => $return_amount_info['return_rate_price'] ?? 0,
                        'divide_channel' => $order['divide_channel'] ?? 0,
                    ];

                    if ($order['ru_id'] && $order['main_count'] == 0) {
                        $this->commissionService->getOrderBillLog($other);
                        $this->commissionService->setBillOrderReturn($return_amount_info['ret_id'], $other['order_id']);
                    }

                    // 确认收货 购买成为分销商商品 绑定权益卡
                    if (file_exists(MOBILE_DRP) && $order['pay_status'] == PS_PAYED) {
                        app(\App\Modules\Drp\Services\Drp\DrpService::class)->buyGoodsUpdateDrpShop($order);
                    }
                }
            }

            /*------------------------------------------------------ */
            //-- 订单单品退换货同意申请
            /*------------------------------------------------------ */
            elseif ('agree_apply' == $operation) {

                try {
                    $this->orderRefoundService->operatePostAgreeApply($rec_id, $ret_id, $action_note);
                } catch (HttpException $httpException) {
                    return sys_msg($httpException->getMessage(), 1);
                }

                /* 操作成功 */
                $links[] = [
                    'text' => lang('seller/order.order_info'), 'href' => 'order.php?act=return_info&ret_id=' . $ret_id . '&rec_id=' . $rec_id
                ];
                return sys_msg(lang('seller/order.act_ok'), 0, $links);

            }

            /*------------------------------------------------------ */
            //-- 收到用户退回商品
            /*------------------------------------------------------ */
            elseif ('receive_goods' == $operation) {

                if (empty($rec_id)) {
                    return sys_msg('Hacking attempt', 1);
                }

                $arr = ['return_status' => RF_RECEIVE];
                $res = OrderReturn::where('rec_id', $rec_id)->update($arr);
                if ($res) {
                    //获取退换货商品ID
                    $return = ReturnGoods::where('rec_id', $rec_id)->select('goods_id', 'return_number');
                    $return = BaseRepository::getToArrayFirst($return);
                    if (!empty($return)) {
                        $return_num = ['return_number' => $return['return_number'], 'goods_id' => $return['goods_id']];
                        update_return_num($return['goods_id'], $return_num);
                    }

                    $arr['pay_status'] = PS_PAYED;
                    $order['pay_status'] = PS_PAYED;

                    /* 记录log */
                    $this->orderCommonService->returnAction($ret_id, RF_RECEIVE, '', $action_note);
                }
            }

            /*------------------------------------------------------ */
            //-- 换出商品寄出 ---- 分单
            /*------------------------------------------------------ */
            elseif ('swapped_out_single' == $operation) {

                if (empty($rec_id)) {
                    return sys_msg('Hacking attempt', 1);
                }

                $arr = ['return_status' => RF_SWAPPED_OUT_SINGLE]; //换出商品寄出
                $res = OrderReturn::where('rec_id', $rec_id)->update($arr);
                if ($res) {
                    $this->orderCommonService->returnAction($ret_id, RF_SWAPPED_OUT_SINGLE, '', $action_note);
                }
            }

            /*------------------------------------------------------ */
            //-- 换出商品寄出
            /*------------------------------------------------------ */
            elseif ('swapped_out' == $operation) {

                if (empty($rec_id)) {
                    return sys_msg('Hacking attempt', 1);
                }

                $arr = ['return_status' => RF_SWAPPED_OUT]; //换出商品寄出
                $res = OrderReturn::where('rec_id', $rec_id)->update($arr);
                if ($res) {
                    $this->orderCommonService->returnAction($ret_id, RF_SWAPPED_OUT, '', $action_note);
                }
            }

            /*------------------------------------------------------ */
            //-- 拒绝申请
            /*------------------------------------------------------ */
            elseif ('refuse_apply' == $operation) {

                if (empty($rec_id)) {
                    return sys_msg('Hacking attempt', 1);
                }

                $arr = ['return_status' => REFUSE_APPLY];
                $res = OrderReturn::where('rec_id', $rec_id)->update($arr);
                if ($res) {

                    if (file_exists(WXAPP_MEDIA)) {
                        //订单售后操作事件
                        event(new \App\Modules\WxMedia\Events\MediaOrderRefoundOperateEvent($rec_id, ['status' => 5]));
                    }

                    $this->orderCommonService->returnAction($ret_id, REFUSE_APPLY, '', $action_note, session('admin_name'));
                }
            }

            /*------------------------------------------------------ */
            //-- 完成退换货
            /*------------------------------------------------------ */
            elseif ('complete' == $operation) {

                if (empty($rec_id)) {
                    return sys_msg('Hacking attempt', 1);
                }

                $arr = ['return_status' => RF_COMPLETE]; //完成退换货
                $return_type = OrderReturn::where('rec_id', $rec_id)->value('return_type');
                $return_type = $return_type ? $return_type : 0;

                if ($return_type == 0) {
                    $refound_status = FF_MAINTENANCE;
                } elseif ($return_type == 1) {
                    $refound_status = FF_REFOUND;
                } elseif ($return_type == 2) {
                    $refound_status = FF_EXCHANGE;
                } elseif ($return_type == 3) {
                    $refound_status = FF_REFOUND;
                }

                $res = OrderReturn::where('rec_id', $rec_id)->update($arr);
                if ($res) {
                    $this->orderCommonService->returnAction($ret_id, RF_COMPLETE, $refound_status, $action_note, session('admin_name'));
                }
            }

            /*------------------------------------------------------ */
            //-- 取消
            /*------------------------------------------------------ */
            elseif ('cancel' == $operation) {
                /* 标记订单为“取消”，记录取消原因 */
                if ($order['order_status'] == OS_CANCELED) {
                    // 已取消 返回错误信息
                    $links[] = ['href' => 'order.php?act=list', 'text' => trans('admin/common.02_order_list')];
                    return sys_msg(lang('admin/order.operation_error'), 1, $links);
                }

                $cancel_note = isset($_REQUEST['cancel_note']) ? trim($_REQUEST['cancel_note']) : '';
                $arr = [
                    'order_status' => OS_CANCELED,
                    'to_buyer' => $cancel_note,
                    'pay_status' => PS_UNPAYED,
                    'pay_time' => 0,
                    'money_paid' => 0,
                    'order_amount' => $order['money_paid']
                ];
                update_order($order_id, $arr);

                /* 处理退款 */
                if ($order['money_paid'] > 0 && $order['money_paid'] != PS_UNPAYED) {
                    $refund_type = isset($_REQUEST['refund']) && !empty($_REQUEST['refund']) ? trim($_REQUEST['refund']) : 1;
                    $refund_note = isset($_REQUEST['refund_note']) && !empty($_REQUEST['refund_note']) ? trim($_REQUEST['refund_note']) : '';

                    if ($refund_note) {
                        $refund_note = "【" . $GLOBALS['_LANG']['setorder_cancel'] . "】【" . $order['order_sn'] . "】" . $refund_note;
                    }

                    order_refund($order, $refund_type, $refund_note);
                }

                /* 记录log */
                order_action($order['order_sn'], OS_CANCELED, $order['shipping_status'], PS_UNPAYED, $action_note, session('admin_name'));

                /* 如果使用库存，且下订单时减库存，则增加库存 */
                if (config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_PLACE) {
                    change_order_goods_storage($order_id, false, SDT_PLACE, 3, session('admin_id'), $store_id);
                }

                /* 退还用户余额、积分、红包 */
                return_user_surplus_integral_bonus($order);

                $this->orderCommonService->getUserOrderNumServer($order['user_id'] ?? 0);

                /* 获取原应付金额 */
                OrderInfo::where('order_id', $order['order_id'])->update([
                    'order_amount' => $order['order_amount']
                ]);

                /* 发送邮件 */
                if (config('shop.send_cancel_email', 0) == '1') {
                    $tpl = get_mail_template('order_cancel');
                    $this->smarty->assign('order', $order);
                    $this->smarty->assign('shop_name', config('shop.shop_name', ''));
                    $this->smarty->assign('send_date', TimeRepository::getLocalDate(config('shop.time_format'), TimeRepository::getGmTime()));
                    $this->smarty->assign('sent_date', TimeRepository::getLocalDate(config('shop.time_format'), TimeRepository::getGmTime()));
                    $content = $this->smarty->fetch('str:' . $tpl['template_content']);
                    if (!CommonRepository::sendEmail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html'])) {
                        $msg = lang('admin/order.send_mail_fail');
                    }
                }

            }

            /*------------------------------------------------------ */
            //-- 无效
            /*------------------------------------------------------ */
            elseif ('invalid' == $operation) {
                /* 设为无效 */
                if ($order['order_status'] == OS_INVALID) {
                    // 已无效 返回错误信息
                    $links[] = ['href' => 'order.php?act=list', 'text' => trans('admin/common.02_order_list')];
                    return sys_msg(lang('admin/order.operation_error'), 1, $links);
                }

                /* 标记订单为“无效”、“未付款” */
                update_order($order_id, ['order_status' => OS_INVALID]);

                /* 记录log */
                order_action($order['order_sn'], OS_INVALID, $order['shipping_status'], PS_UNPAYED, $action_note, session('admin_name'));

                /* 如果使用库存，且下订单时减库存，则增加库存 */
                if (config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_PLACE) {
                    change_order_goods_storage($order_id, false, SDT_PLACE, 2, session('admin_id'), $store_id);
                }

                /* 退货用户余额、积分、红包 */
                return_user_surplus_integral_bonus($order);

                $this->orderCommonService->getUserOrderNumServer($order['user_id'] ?? 0);

                /* 获取原应付金额 */
                OrderInfo::where('order_id', $order['order_id'])->update([
                    'order_amount' => $order['order_amount']
                ]);

                /* 发送邮件 */
                if (config('shop.send_invalid_email', 0) == '1') {
                    $tpl = get_mail_template('order_invalid');
                    $this->smarty->assign('order', $order);
                    $this->smarty->assign('shop_name', config('shop.shop_name', ''));
                    $this->smarty->assign('send_date', TimeRepository::getLocalDate(config('shop.time_format'), TimeRepository::getGmTime()));
                    $this->smarty->assign('sent_date', TimeRepository::getLocalDate(config('shop.time_format'), TimeRepository::getGmTime()));
                    $content = $this->smarty->fetch('str:' . $tpl['template_content']);
                    if (!CommonRepository::sendEmail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html'])) {
                        $msg = lang('admin/order.send_mail_fail');
                    }
                }

            }

            /*------------------------------------------------------ */
            //-- 订单单品退款 - 退货单页面退款
            /*------------------------------------------------------ */
            elseif ('refound' == $operation) {
                /**
                 * 订单单品退款 - 退货单页面退款
                 */
                load_helper('transaction');

                /* 过滤数据 */
                $refund_type = (int)request()->input('refund', 0); // 退款类型

                $refund_amount = request()->input('refound_amount', 0);
                $refund_amount = !empty($refund_amount) ? floatval($refund_amount) : 0; //退款金额

                $refound_pay_points = (int)request()->input('refound_pay_points', 0); //退回积分

                $is_shipping = (int)request()->input('is_shipping', 0); //是否退运费
                $shipping = request()->input('shipping', 0);
                $shippingFee = !empty($is_shipping) ? floatval($shipping) : 0; //退款运费金额

                $shipping_fee_from = (int)request()->input('shipping_fee_from', 1); // 快递费从哪种支付方式扣除 1.由余额/在线支付抵扣  2.由储值卡抵扣

                $return_info = return_order_info($ret_id);        //退换货订单
                $order['return_sn'] = $return_info['return_sn'];

                /* 储值卡退款 start */
                $vc_id = (int)$return_info['vc_id'];//储值卡id
                $refound_vcard = request()->input('refound_vcard', 0);
                $refound_vcard = !empty($refound_vcard) ? floatval($refound_vcard) : 0; //储值卡金额

                /* 可退储值卡金额 */
                $pay_card_money = $return_info['pay_card_money'] ?? 0;

                if ($pay_card_money > 0) {
                    $refoundCardTotal = $refound_vcard + $shippingFee;
                } else {
                    $refoundCardTotal = 0;
                }

                if ($refoundCardTotal > $pay_card_money) {
                    $refund_amount += $refoundCardTotal - $pay_card_money; //剩余部分计入已付款的退款金额中
                    $refound_vcard = $pay_card_money;
                } else {
                    $refound_vcard = $refoundCardTotal;
                }
                /* 储值卡退款 end */

                //判断商品退款是否大于实际商品退款金额
                $refound_fee = $this->orderRefoundService->orderRefoundFee($order_id, $ret_id); //已退金额
                $paid_amount = $order['money_paid'] + $order['surplus'] - $refound_fee; //剩余可退的已付款金额

                if ($refound_vcard == 0) {

                    $refundAmount = $refund_amount + $shippingFee;

                    if ($refundAmount > $paid_amount) {
                        $refund_amount = $paid_amount;
                    } else {
                        $refund_amount = $refundAmount;
                    }
                } else {
                    if ($refund_amount > $paid_amount) {
                        $refund_amount = $paid_amount;
                    }
                }

                $rate_price = 0;
                if (CROSS_BORDER === true) { // 跨境多商户
                    $rate_price = request()->input('rate_price', '');
                    $rate_price = !empty($rate_price) ? floatval($rate_price) : 0; //税费
                    if ($rate_price > $order['rate_fee']) {
                        $rate_price = $order['rate_fee'];
                    }
                    $refund_amount = $rate_price > 0 ? $refund_amount + $rate_price : $refund_amount;
                }

                $refund_note = e(request()->input('refund_note', ''));

                if (empty($action_note) && config('shop.order_return_note') == 1) {
                    return sys_msg(trans('admin/order.operation_notes_required'), 1);
                }

                // 返回链接
                $links[] = ['text' => lang('admin/order.back_return_delivery_handle'), 'href' => 'order.php?act=return_info&ret_id=' . $ret_id . '&rec_id=' . $rec_id];

                if ($order['order_status'] == OS_RETURNED || $order['order_status'] == OS_ONLY_REFOUND) {
                    // 已退货、仅退款 返回错误信息
                    return sys_msg(lang('admin/order.operation_error'), 1, $links);
                }

                $return_goods = get_return_order_goods1($rec_id); //退换货商品

                $order_id = $order['order_id'];

                $refound_status = $return_info['refound_status'] ?? 0;
                if ($refound_status == FF_REFOUND) {
                    // 已退款 返回错误信息
                    return sys_msg(lang('admin/order.operation_error'), 1);
                }

                /* 处理退款 */
                if ($order['pay_status'] != PS_UNPAYED) {

                    // 获取退款后的订单状态
                    $order_goods = OrderGoods::where('order_id', $order['order_id'])->where('extension_code', '<>', 'virtual_card')->select('rec_id', 'order_id', 'goods_id', 'goods_number', 'send_number');
                    $order_goods = BaseRepository::getToArrayGet($order_goods);//订单商品

                    $get_order_arr = get_order_arr($return_info['return_number'], $return_info['rec_id'], $order_goods);

                    foreach ($order_goods as $k => $v) {
                        $res = OrderReturn::where('rec_id', $v['rec_id'])
                            ->where('order_id', $v['order_id'])
                            ->value('return_type');
                        $res = $res ? $res : 0;
                        // 仅退款
                        if ($res == 3 && $get_order_arr['order_status'] == OS_RETURNED) {
                            $get_order_arr['order_status'] = OS_ONLY_REFOUND;
                        }
                    }

                    // 订单应退款
                    $order['should_return'] = $return_info['should_return'];

                    // 在线原路退款
                    if ($refund_type == 6) {
                        try {
                            $refound_result = $this->orderRefoundService->refundApply($return_info['return_sn'], $refund_amount);
                        } catch (HttpException $httpException) {
                            /* 提示信息 */
                            return sys_msg(lang('admin/order.refund_type_notic_six') . ',' . $httpException->getMessage(), 1, $links);
                        }

                        if ($refound_result === false) {
                            /* 提示信息 */
                            return sys_msg(lang('admin/order.refund_type_notic_six'), 1, $links);
                        }
                    } else {

                        // 1, 2, 3, 5  等
                        $is_ok = order_refound($order, $refund_type, $refund_note, $refund_amount, $operation);

                        if ($is_ok == 2 && $refund_type == 1) {
                            /* 提示信息 */
                            return sys_msg(lang('admin/order.refund_type_notic_two'), 1, $links);
                        }

                        /* 余额已放入冻结资金 */
                        $order['surplus'] = 0;
                    }

                    $actual_integral_money = $this->dscRepository->valueOfIntegral($refound_pay_points);

                    //标记order_return 表
                    $return_status = [
                        'refound_status' => FF_REFOUND,
                        'agree_apply' => 1,
                        'actual_return' => $refund_amount,
                        'actual_value_card' => $refound_vcard,
                        'return_shipping_fee' => $shippingFee,
                        'return_rate_price' => $rate_price ?? 0,
                        'actual_integral_money' => $actual_integral_money ?? 0,
                        'refund_type' => $refund_type,
                        'return_time' => TimeRepository::getGmTime()
                    ];

                    // 商家订单 平台开启审批 平台管理员操作则通过审批
                    if (config('shop.seller_return_check', 0) == 1 && $order['ru_id'] > 0) {
                        $return_status['is_check'] = 1;
                    }

                    OrderReturn::where('ret_id', $ret_id)->update($return_status);

                    if (file_exists(WXAPP_MEDIA)) { // 小程序视频号推广员管理
                        app(\App\Modules\WxMedia\Services\WxappShopOrderService::class)->promoterCommissionOrderRefound($return_info['order_id'], $return_info['rec_id'], $refund_amount, session('admin_name'));
                    }

                    if ($shippingFee > 0 && $return_status['actual_return'] >= $shippingFee) {
                        if ($refound_vcard == 0) {
                            $return_status['actual_return'] -= $shippingFee;
                        } else {
                            if ($refound_vcard < $shippingFee) {
                                $return_status['actual_return'] = $return_status['actual_return'] - ($shippingFee - $refound_vcard);
                            }
                        }
                    }

                    /* 负账单订单 start */
                    if ($order['ru_id'] > 0) {
                        $negativeCount = SellerNegativeOrder::where('ret_id', $ret_id)->count();

                        if ($negativeCount == 0) {
                            $negative_time = TimeRepository::getGmTime();

                            $negative_refund_amount = $return_status['actual_return'] - $rate_price;
                            $negative_refund_amount = $negative_refund_amount > 0 ? $negative_refund_amount : 0;

                            $commission = app(CommissionManageService::class)->commissionNegativeOrderList($ret_id, $negative_refund_amount);

                            $other = [
                                'order_id' => $order['order_id'],
                                'order_sn' => $order['order_sn'],
                                'ret_id' => $ret_id,
                                'return_sn' => $return_info['return_sn'],
                                'seller_id' => $order['ru_id'],
                                'return_amount' => $negative_refund_amount,
                                'return_shippingfee' => $shippingFee,
                                'return_rate_price' => $rate_price,
                                'add_time' => $negative_time,
                                'seller_proportion' => $commission['seller_proportion'],
                                'cat_proportion' => $commission['cat_proportion'],
                                'commission_rate' => $commission['commission_rate'],
                                'gain_commission' => $commission['gain_commission'],
                                'should_amount' => $commission['should_amount'],
                                'divide_channel' => $order['divide_channel'] ?? 0
                            ];

                            // 记录分销金额
                            if (file_exists(MOBILE_DRP)) {
                                if ($commission['should_amount'] >= $return_goods['drp_money']) {
                                    $drp_money = $return_goods['drp_money'];
                                } else {
                                    $drp_money = $commission['should_amount'];
                                }

                                $other['drp_money'] = $drp_money;
                            }

                            SellerNegativeOrder::insert($other);
                        }
                    }
                    /* 负账单订单 end */

                    /**
                     * 将未结算的负账单作废
                     */
                    if ($get_order_arr['pay_status'] == PS_REFOUND) {
                        SellerNegativeOrder::where('order_id', $order['order_id'])
                            ->where('negative_id', 0)
                            ->whereHasIn('getSellerBillOrder', function ($query) {
                                $query->where('bill_id', 0);
                            })
                            ->update([
                                'settle_accounts' => 2
                            ]);
                    }

                    /**
                     * 更新订单商品状态
                     */
                    if ($get_order_arr['order_status'] == OS_RETURNED || $get_order_arr['order_status'] == OS_RETURNED_PART || $get_order_arr['order_status'] == OS_ONLY_REFOUND) {
                        OrderGoods::where('rec_id', $return_info['rec_id'])->update(['is_received' => 1]);
                    }

                    update_order($order_id, $get_order_arr);

                    // 收银台代码 推送核销状态到收银台
                    event(new \App\Events\PickupOrdersUpdateAfterDoSomething(['order_id' => $order_id]));

                    /**
                     * 更新账单订单状态
                     */
                    SellerBillOrder::where('order_id', $order_id)->update([
                        'order_status' => $get_order_arr['order_status'],
                        'pay_status' => $get_order_arr['pay_status']
                    ]);

                    // 减回商品销量
                    $sales_volume = Goods::where('goods_id', $return_goods['goods_id'])->value('sales_volume');
                    $sales_volume = $sales_volume ?? 0;
                    if ($sales_volume >= $return_info['return_number']) {
                        Goods::where('goods_id', $return_goods['goods_id'])->decrement('sales_volume', $return_info['return_number']);
                    }

                    // 更新订单操作记录log
                    order_action($order['order_sn'], $get_order_arr['order_status'], $order['shipping_status'], $order['pay_status'], $action_note, session('admin_name'));

                    if (file_exists(WXAPP_MEDIA) && $order['media_type'] == 1) {
                        //订单售后操作事件
                        event(new \App\Modules\WxMedia\Events\MediaOrderRefoundOperateEvent($rec_id, ['status' => 13]));
                    }
                } elseif (in_array($order['pay_code'], ['bank', 'cod'])) {

                    //标记order_return 表
                    $return_status = [
                        'refound_status' => FF_REFOUND,
                        'agree_apply' => 1,
                        'refund_type' => 2,
                        'return_time' => TimeRepository::getGmTime()
                    ];

                    // 商家订单 平台开启审批 平台管理员操作则通过审批
                    if (config('shop.seller_return_check', 0) == 1 && $order['ru_id'] > 0) {
                        $return_status['is_check'] = 1;
                    }

                    OrderReturn::where('ret_id', $ret_id)->update($return_status);

                    $refoundStatusCount = OrderReturn::where('order_id', $order['order_id'])->where('refound_status', 1)->count('ret_id');
                    $recGoodsCount = OrderGoods::where('order_id', $order['order_id'])->count('rec_id');

                    if ($refoundStatusCount == $recGoodsCount) {
                        OrderInfo::where('order_id', $order['order_id'])->update([
                            'pay_status' => PS_REFOUND
                        ]);
                    } else {
                        OrderInfo::where('order_id', $order['order_id'])->update([
                            'pay_status' => PS_REFOUND_PART
                        ]);
                    }
                }

                /* 1:只有退款|退货,并且红包类型为:按订单金额发放红包(2)|按商品发放红包(1) 如红包尚未使用，则删除该红包*/
                if ($order['user_id'] > 0 && ($return_info['return_type'] == 1 || $return_info['return_type'] == 3)) {
                    $this->orderRefoundService->return_order_delete_bonus($return_info['order_id'], $return_info['user_id'], $return_goods['goods_id'], $order['total_fee'], $refund_amount);
                }

                // 退货退款(含部分退款) 重新计算待分成分销佣金
                if (file_exists(MOBILE_DRP)) {
                    \App\Modules\Drp\Services\Drp\DrpRefoundService::return_drp_order($ret_id, $order);
                }

                /* 判断是否需要退还积分  如果需要 则跟新退还日志 */
                if ($refound_pay_points > 0) {
                    // 退还订单使用的积分
                    log_account_change($order['user_id'], 0, 0, 0, $refound_pay_points, lang('order.order_return_prompt') . $order['order_sn'] . lang('order.buy_integral'));
                }

                // 原路退款同时使用余额 退回订单使用的余额
                if ($refund_type == 6 && $refund_amount > 0 && $return_info['surplus'] > 0) {

                    $returnMoneyPaid = $this->orderRefoundService->orderReturnMoneyPaid($return_info['ret_id']); //获取当前可退余额

                    if ($refund_amount > $returnMoneyPaid['refund_surplus']) {
                        $refound_surplus = $returnMoneyPaid['refund_surplus'];
                    } else {
                        $refound_surplus = $refund_amount;
                    }

                    $refound_surplus = $refound_surplus > $return_info['surplus'] ? $return_info['surplus'] : $refound_surplus;

                    if ($refound_surplus > 0) {
                        return_user_surplus($order, $refound_surplus);
                    }
                }

                //普通订单已支付(部分退款)且已收货, 货到付款订单 已发货 才会退回订单所赠送积分
                if ((isset($order['pay_code']) && $order['pay_code'] == 'cod' && $order['shipping_status'] == SS_SHIPPED) || (in_array($order['pay_status'], [PS_PAYED, PS_REFOUND_PART]) && $order['shipping_status'] == SS_RECEIVED)) {
                    /* 退回订单赠送的积分 */
                    return_integral_rank($ret_id, $order['user_id'], $order['order_sn'], $rec_id);
                }

                $is_diff = get_order_return_rec($order_id, true);
                if ($is_diff) {
                    //整单退换货
                    $return_count = return_order_info_byId($order_id, false, true);
                    if ($return_count == 1) {
                        //退还红包
                        $bonus = $order['bonus'];
                        UserBonus::where('order_id', $order_id)->update(['used_time' => '', 'order_id' => '']);

                        /* 退还优惠券 start */
                        unuse_coupons($order_id, $order['uc_id']);
                    }
                }

                /* 退回订单消费储值卡金额 */
                get_return_vcard($order_id, $vc_id, $refound_vcard, $return_info['return_sn'], $ret_id);

                /* 记录log */
                $this->orderCommonService->returnAction($ret_id, RF_COMPLETE, FF_REFOUND, $action_note, session('admin_name'));

                /* 如果使用库存，则增加库存（不论何时减库存都需要） */
                if (config('shop.use_storage') == '1') {
                    if (config('shop.stock_dec_time') == SDT_SHIP) {
                        change_goods_storage($return_goods['goods_id'], $return_goods['product_id'], $return_info['return_number'], $return_goods['warehouse_id'], $return_goods['area_id'], $return_goods['area_city'], $order_id, 6, session('admin_id'), $store_id);
                    } elseif (config('shop.stock_dec_time') == SDT_PLACE) {
                        change_goods_storage($return_goods['goods_id'], $return_goods['product_id'], $return_info['return_number'], $return_goods['warehouse_id'], $return_goods['area_id'], $return_goods['area_city'], $order_id, 6, session('admin_id'), $store_id);
                    } elseif (config('shop.stock_dec_time') == SDT_PAID) {
                        change_goods_storage($return_goods['goods_id'], $return_goods['product_id'], $return_info['return_number'], $return_goods['warehouse_id'], $return_goods['area_id'], $return_goods['area_city'], $order_id, 6, session('admin_id'), $store_id);
                    }
                }

                $this->orderCommonService->getUserOrderNumServer($order['user_id'] ?? 0);

            }

            /*------------------------------------------------------ */
            //-- 退货/退款 - 订单详情退款
            /*------------------------------------------------------ */
            elseif ('return' == $operation) {

                /* 定义当前时间 */
                define('GMTIME_UTC', TimeRepository::getGmTime()); // 获取 UTC 时间戳

                // 过滤数据
                $refund_type = (int)request()->input('refund', 0); // 退款类型

                $refund_amount = request()->input('refound_amount', 0);
                $return_amount = !empty($refund_amount) ? floatval($refund_amount) : 0; //退款金额

                $is_shipping = request()->input('is_shipping', 0);
                $is_shipping = !empty($is_shipping) ? intval($is_shipping) : 0; //是否退运费
                $shipping = request()->input('shipping', 0);
                $shippingFee = !empty($is_shipping) ? floatval($shipping) : 0; //退款运费金额

                $shipping_fee_from = !empty($_REQUEST['shipping_fee_from']) ? intval($_REQUEST['shipping_fee_from']) : 1;//快递费从哪种支付方式扣除 1.由余额/在线支付抵扣  2.由储值卡抵扣
                $refound_vcard = request()->input('refound_vcard', 0);
                $refound_vcard = !empty($refound_vcard) ? floatval($refound_vcard) : 0; //储值卡金额

                $vc_id = request()->input('vc_id', 0);
                $vc_id = !empty($vc_id) ? intval($vc_id) : 0; //储值卡id

                $order['integral'] = isset($_REQUEST['refound_pay_points']) ? intval($_REQUEST['refound_pay_points']) : $order['integral'];//可退积分

                $action_note = e(request()->input('action_note', ''));
                $refund_note = isset($action_note) ? addslashes(trim($action_note)) : '';

                if (empty($action_note) && config('shop.order_return_note') == 1) {
                    return sys_msg(lang('admin/order.operation_notes_required'), 1);
                }

                // 退款总金额 包含快递费
                $refound_vcard = $shipping_fee_from == 2 ? $refound_vcard + $shippingFee : $refound_vcard;
                $order_return_amount = $refund_amount = $shipping_fee_from == 1 ? $return_amount + $shippingFee : $return_amount;

                $res = ValueCardRecord::where('vc_id', $vc_id)->where('order_id', $order_id)->select('vc_id', 'use_val');
                $value_card = BaseRepository::getToArrayFirst($res);

                if ($shipping_fee_from == 2) {
                    $shippingFee = 0;
                }

                // 返回链接
                $links[] = ['href' => 'order.php?act=info&order_id=' . $order_id, 'text' => lang('admin/order.return_order_info')];

                if ($order['order_status'] == OS_RETURNED || $order['order_status'] == OS_ONLY_REFOUND) {
                    // 已退货、仅退款 返回错误信息
                    return sys_msg(lang('admin/order.operation_error'), 1, $links);
                }

                /* 处理退款 */
                if ($order['pay_status'] != PS_UNPAYED) {
                    $order['order_status'] = OS_RETURNED;
                    $order['pay_status'] = PS_UNPAYED;
                    $order['shipping_status'] = SS_UNSHIPPED;

                    $refund_note = "【" . lang('admin/order.refund') . "】" . "【" . $order['order_sn'] . "】" . $action_note;

                    try {
                        // 提交退换货申请
                        $request_info = request()->post();

                        $return_brief = e(request()->input('refund_note', ''));
                        if (empty($return_brief)) {
                            // 请填写原因描述
                            return sys_msg(lang('admin/order.return_brief_required'), 1, $links);
                        }

                        //订单商品
                        $model = OrderGoods::where('order_id', $order['order_id'])->select('rec_id', 'order_id', 'goods_id', 'goods_number');
                        $get_order_goods = BaseRepository::getToArrayGet($model);

                        if (!empty($get_order_goods)) {
                            $rec_ids = ArrRepository::pluck($get_order_goods, 'rec_id');
                            $goods_number = ArrRepository::pluck($get_order_goods, 'goods_number');
                            $return_number = !empty($goods_number) ? array_sum($goods_number) : 0;
                        }

                        $request_info['rec_id'] = $rec_ids ?? [];
                        $request_info['return_remark'] = $action_note;
                        $request_info['return_brief'] = $return_brief;
                        $request_info['chargeoff_status'] = $order['chargeoff_status'] ?? 0;
                        $request_info['return_number'] = $return_number ?? 0;

                        $this->orderRefoundService->submitReturn($order['user_id'], $request_info, session('admin_name'));
                    } catch (HttpException $httpException) {
                        /* 提示信息 */
                        return sys_msg(lang('admin/order.operation_error') . ',' . $httpException->getMessage(), 1, $links);
                    }

                    // 退换货订单列表
                    $return_list = OrderReturnRepository::orderReturnList($order['order_id']);

                    if (!empty($return_list)) {
                        foreach ($return_list as $order_return) {
                            // 更新 order_return 表
                            $return_status = [
                                'agree_apply' => 1
                            ];
                            // 商家订单 平台开启审批 平台管理员操作则通过审批
                            if (config('shop.seller_return_check', 0) == 1 && $order['ru_id'] > 0) {
                                $return_status['is_check'] = 1;
                            }

                            $r = OrderReturn::where('ret_id', $order_return['ret_id'])->update($return_status);
                            if ($r) {
                                // 记录log 同意申请
                                $this->orderCommonService->returnAction($order_return['ret_id'], RF_AGREE_APPLY, '', $action_note, session('admin_name'));
                            }
                        }
                    }

                    // 扣除使用余额部分
                    if (isset($order['surplus']) && $order['surplus'] > 0) {
                        $refound_surplus = $order['surplus'];
                    } else {
                        $refound_surplus = 0;
                    }

                    // 在线原路退款
                    if ($refund_type == 6) {

                        try {

                            $refound_result = [];
                            if (!empty($return_list)) {

                                $orderAllreturnList = $this->orderCommonService->orderAllreturnGoods($order['order_id']);

                                foreach ($return_list as $order_return) {

                                    $return_info = $orderAllreturnList[$order_return['rec_id']] ?? [];

                                    $respond = $this->orderRefoundService->refundApply($order_return['return_sn'], $return_info['pay_money_paid']);
                                    if ($respond == true) {
                                        $refound_result[] = $order_return['ret_id'] ?? '';

                                        // 更新 order_return 表
                                        $return_status = [
                                            'refound_status' => FF_REFOUND,
                                            'actual_return' => $return_info['pay_goods_amount'] ?? 0,
                                            'return_shipping_fee' => $return_info['pay_shipping_fee'] ?? 0,
                                            'refund_type' => $refund_type,
                                            'actual_value_card' => $return_info['goods_value_card'] ?? 0,
                                            'actual_integral_money' => $return_info['should_integral_money'] ?? 0,
                                            'return_time' => TimeRepository::getGmTime()
                                        ];
                                        $r = OrderReturn::where('ret_id', $order_return['ret_id'])->update($return_status);

                                        if ($r) {
                                            /* 记录log 完成退换货 */
                                            $this->orderCommonService->returnAction($order_return['ret_id'], RF_COMPLETE, FF_REFOUND, $action_note);
                                        }
                                    }
                                }
                            }

                        } catch (HttpException $httpException) {
                            /* 提示信息 */
                            return sys_msg(lang('admin/order.refund_type_notic_six') . ',' . $httpException->getMessage(), 1, $links);
                        }

                        if (empty($refound_result)) {
                            /* 提示信息 */
                            return sys_msg(lang('admin/order.refund_type_notic_six'), 1, $links);
                        }
                    } else {

                        $orderAllreturnList = $this->orderCommonService->orderAllreturnGoods($order['order_id']);
                        $return_amount = BaseRepository::getArraySum($orderAllreturnList, 'pay_goods_amount');

                        // 1, 2, 3, 5  等
                        $is_ok = order_refund($order, $refund_type, $refund_note, $return_amount);

                        if ($is_ok == false) {
                            /* 提示信息 */
                            return sys_msg(lang('admin/order.refund_type_notic_two'), 1, $links);
                        }

                        if ($is_ok == true && !empty($return_list)) {
                            foreach ($return_list as $order_return) {

                                $return_info = $orderAllreturnList[$order_return['rec_id']] ?? [];

                                // 更新 order_return 表
                                $return_status = [
                                    'refound_status' => FF_REFOUND,
                                    'actual_return' => $return_info['pay_goods_amount'] ?? 0,
                                    'return_shipping_fee' => $return_info['pay_shipping_fee'] ?? 0,
                                    'refund_type' => $refund_type,
                                    'actual_value_card' => $return_info['goods_value_card'] ?? 0,
                                    'actual_integral_money' => $return_info['should_integral_money'] ?? 0,
                                    'return_time' => TimeRepository::getGmTime()
                                ];

                                $r = OrderReturn::where('ret_id', $order_return['ret_id'])->update($return_status);

                                if ($r) {
                                    /* 记录log 完成退换货 */
                                    $this->orderCommonService->returnAction($order_return['ret_id'], RF_COMPLETE, FF_REFOUND, $action_note);
                                }
                            }
                        }

                        /* 余额已放入冻结资金 */
                        $order['surplus'] = 0;
                    }
                }

                /* 标记订单为“退货”、“未付款”、“未发货” */
                $arr = [
                    'order_status' => OS_RETURNED,
                    'pay_status' => PS_REFOUND,
                    'shipping_status' => SS_UNSHIPPED,
                    'money_paid' => 0,
                    'invoice_no' => '',
                    'return_amount' => $order_return_amount,
                    'order_amount' => $order_return_amount
                ];
                update_order($order_id, $arr);

                /* 记录log */
                order_action($order['order_sn'], OS_RETURNED, SS_UNSHIPPED, PS_UNPAYED, $action_note, session('admin_name'));

                /* 如果订单用户不为空，计算积分，并退回 */
                if ($order['user_id'] > 0) {
                    $res = OrderGoods::where('order_id', $order['order_id'])->select('goods_number', 'send_number');
                    $goods_num = BaseRepository::getToArrayFirst($res);

                    if ($goods_num && $goods_num['goods_number'] == $goods_num['send_number']) {
                        /* 计算并退回订单赠送的积分 */
                        $integral = integral_to_give($order);

                        log_account_change($order['user_id'], 0, 0, (-1) * intval($integral['rank_points']), (-1) * intval($integral['custom_points']), sprintf(lang('admin/order.return_order_gift_integral'), $order['order_sn']));
                    }
                    /* 计算并退回订单发放的红包 */
                    return_order_bonus($order_id);
                }

                /* 如果使用库存，则增加库存（不论何时减库存都需要） */
                if (config('shop.use_storage') == '1') {
                    if (config('shop.stock_dec_time') == SDT_SHIP) {
                        change_order_goods_storage($order['order_id'], false, SDT_SHIP, 6, session('admin_id'), $store_id);
                    } elseif (config('shop.stock_dec_time') == SDT_PLACE) {
                        change_order_goods_storage($order['order_id'], false, SDT_PLACE, 6, session('admin_id'), $store_id);
                    } elseif (config('shop.stock_dec_time') == SDT_PAID) {
                        change_order_goods_storage($order['order_id'], false, SDT_PAID, 6, session('admin_id'), $store_id);
                    }
                }

                /* 退回订单消费储值卡金额 */
                $this->orderManageService->unpayOrderReturnVcard($order_id, $vc_id, $refound_vcard, $order['user_id'], $order['order_sn']);

                /* 退货用户余额、积分、红包 */
                if ($refund_type == 6 && isset($refound_surplus) && $refound_surplus > 0) {
                    return_user_surplus_integral_bonus($order, $refound_surplus);
                } else {
                    return_user_surplus_integral_bonus($order);
                }

                // 已发货
                if ($order['shipping_status'] == SS_SHIPPED) {
                    /* 获取当前操作员 */
                    $delivery['action_user'] = session('admin_name');

                    /* 添加退货记录 */
                    $res = DeliveryOrder::whereIn('status', [DELIVERY_SHIPPED, DELIVERY_CREATE])->where('order_id', $order['order_id']);
                    $delivery_list = BaseRepository::getToArrayGet($res);

                    if ($delivery_list) {
                        foreach ($delivery_list as $list) {
                            $data = [
                                'delivery_sn' => $list['delivery_sn'],
                                'order_sn' => $list['order_sn'],
                                'order_id' => $list['order_id'],
                                'add_time' => $list['add_time'],
                                'shipping_id' => $list['shipping_id'],
                                'user_id' => $list['user_id'],
                                'action_user' => $delivery['action_user'],
                                'consignee' => $list['consignee'],
                                'address' => $list['address'],
                                'country' => $list['country'],
                                'province' => $list['province'],
                                'city' => $list['city'],
                                'district' => $list['district'],
                                'sign_building' => $list['sign_building'],
                                'email' => $list['email'],
                                'zipcode' => $list['zipcode'],
                                'tel' => $list['tel'],
                                'mobile' => $list['mobile'],
                                'best_time' => $list['best_time'],
                                'postscript' => $list['postscript'],
                                'how_oos' => $list['how_oos'],
                                'insure_fee' => $list['insure_fee'],
                                'shipping_fee' => $list['shipping_fee'],
                                'update_time' => $list['update_time'],
                                'suppliers_id' => $list['suppliers_id'],
                                'return_time' => GMTIME_UTC,
                                'agency_id' => $list['agency_id'],
                                'invoice_no' => $list['invoice_no']
                            ];
                            $back_id = BackOrder::insertGetId($data);

                            $deliveryGoodsList = DeliveryGoods::select('goods_id', 'product_id', 'product_sn', 'goods_name', 'brand_name', 'goods_sn', 'is_real', 'send_number', 'goods_attr')
                                ->where('delivery_id', $list['delivery_id']);
                            $deliveryGoodsList = BaseRepository::getToArrayGet($deliveryGoodsList);

                            $is_insert = 0;
                            if (!empty($deliveryGoodsList)) {
                                $deliveryGoodsOther = [];
                                foreach ($deliveryGoodsList as $key => $value) {
                                    $deliveryGoodsOther['back_id'] = $back_id;
                                    $deliveryGoodsOther['goods_id'] = $value['goods_id'];
                                    $deliveryGoodsOther['product_id'] = $value['product_id'];
                                    $deliveryGoodsOther['product_sn'] = $value['product_sn'];
                                    $deliveryGoodsOther['goods_name'] = $value['goods_name'];
                                    $deliveryGoodsOther['brand_name'] = $value['brand_name'];
                                    $deliveryGoodsOther['goods_sn'] = $value['goods_sn'];
                                    $deliveryGoodsOther['is_real'] = $value['is_real'];
                                    $deliveryGoodsOther['send_number'] = $value['send_number'];
                                    $deliveryGoodsOther['goods_attr'] = $value['goods_attr'];

                                    $back_rec_id = BackGoods::insertGetId($deliveryGoodsOther);

                                    if ($back_rec_id > 0) {
                                        $is_insert++;
                                    }
                                }
                            }

                            if ($is_insert) {
                                //获取退换货商品ID
                                $backGoodsList = BackGoods::where('back_id', $back_id)->select('goods_id', 'send_number');
                                $backGoodsList = BaseRepository::getToArrayFirst($backGoodsList);

                                if ($backGoodsList) {
                                    $return_num = ['return_number' => $backGoodsList['send_number'], 'goods_id' => $backGoodsList['goods_id']];
                                    update_return_num($backGoodsList['goods_id'], $return_num);
                                }
                            }
                        }
                    }

                    /* 修改订单的发货单状态为退货 */
                    DeliveryOrder::whereIn('status', [DELIVERY_SHIPPED, DELIVERY_CREATE])
                        ->where('order_id', $order['order_id'])
                        ->update(['status' => DELIVERY_REFOUND]);

                    $res = OrderGoods::where('order_id', $order['order_id'])
                        ->where('extension_code', '<>', 'virtual_card')
                        ->where('send_number', '>=', 1)
                        ->select('goods_id', 'send_number');
                    $goods_list = BaseRepository::getToArrayGet($res);

                    if ($goods_list) {
                        foreach ($goods_list as $key => $row) {
                            Goods::where('goods_id', $row['goods_id'])->where('sales_volume', '>=', $row['send_number'])->decrement('sales_volume', $row['send_number']);
                        }
                    }

                    /* 将订单的商品发货数量更新为 0 */
                    OrderGoods::where('order_id', $order_id)->update(['send_number' => 0]);
                }

                $this->orderCommonService->getUserOrderNumServer($order['user_id']);

                /* 清除缓存 */
                clear_cache_files();

                // 退货完成 增加跳转链接至 退货单列表
                $link[] = [
                    'text' => lang('admin::common.10_back_order'), 'href' => 'order.php?act=return_list'
                ];

            }

            /*------------------------------------------------------ */
            //-- 售后
            /*------------------------------------------------------ */
            elseif ('after_service' == $operation) {

                /* 记录log */
                order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], '[' . lang('admin/order.op_after_service') . '] ' . $action_note, session('admin_name'));
            } else {
                return sys_msg('invalid params', 1);
            }

            /* 操作成功 */
            if ($rec_id > 0) {
                $links[] = [
                    'text' => lang('admin/order.order_info'), 'href' => 'order.php?act=return_info&ret_id=' . $ret_id . '&rec_id=' . $rec_id
                ];
            } else {

                $links[] = [
                    'text' => lang('admin/order.order_info'), 'href' => 'order.php?act=info&order_id=' . $order_id
                ];

                $links = !empty($link) ? array_merge($links, $link) : $links;
            }

            return sys_msg(lang('admin/order.act_ok') . $msg, 0, $links);

        } elseif ($act == 'json') {
            $goods_id = isset($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
            //ecmoban模板堂 --zhuo start
            $warehouse_id = isset($_REQUEST['warehouse_id']) ? intval($_REQUEST['warehouse_id']) : 0;
            $area_id = isset($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : 0;
            $area_city = isset($_REQUEST['area_city']) ? intval($_REQUEST['area_city']) : 0;
            $user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
            $model_attr = isset($_REQUEST['model_attr']) ? intval($_REQUEST['model_attr']) : 0;
            $goods_number = isset($_REQUEST['goods_number']) ? intval($_REQUEST['goods_number']) : 0;
            //ecmoban模板堂 --zhuo end

            $get_attr_id = request()->input('attr', '');
            $attr_id = $get_attr_id ? explode(',', $get_attr_id) : [];

            $func = $_REQUEST['func'];
            if ($func == 'get_goods_info') {
                /* 取得商品信息 */

                $res = Goods::where('goods_id', $goods_id);

                $res = $res->with(['getWarehouseGoods' => function ($query) use ($warehouse_id) {
                    $query->select('goods_id', 'region_number', 'warehouse_price', 'warehouse_promote_price')
                        ->where('region_id', $warehouse_id);
                }]);

                $res = $res->with(['getWarehouseAreaGoods' => function ($query) use ($area_id) {
                    $query->select('goods_id', 'region_number', 'region_price', 'region_promote_price')
                        ->where('region_id', $area_id);
                }]);

                $res = $res->with(['getBrand', 'getGoodsCategory']);

                $goods = BaseRepository::getToArrayFirst($res);

                $goods['cat_name'] = '';
                if (isset($goods['get_goods_category']) && !empty('get_goods_category')) {
                    $goods['cat_name'] = $goods['get_goods_category']['cat_name'];
                }
                $goods['brand_name'] = '';
                if (isset($goods['get_brand']) && !empty('get_brand')) {
                    $goods['brand_name'] = $goods['get_brand']['brand_name'];
                }

                if ($goods['model_price'] < 1) {
                    $goods['goods_number'] = $goods['goods_number'];
                    $goods['shop_price'] = $goods['shop_price'];
                    $goods['promote_price'] = $goods['promote_price'];
                } elseif ($goods['model_price'] < 2) {
                    $goods['goods_number'] = 0;
                    $goods['shop_price'] = 0;
                    $goods['promote_price'] = 0;
                    if (isset($goods['get_warehouse_goods']) && !empty('get_warehouse_goods')) {
                        $goods['goods_number'] = $goods['get_warehouse_goods']['region_number'];
                        $goods['shop_price'] = $goods['get_warehouse_goods']['warehouse_price'];
                        $goods['promote_price'] = $goods['get_warehouse_goods']['warehouse_promote_price'];
                    }
                } else {
                    $goods['goods_number'] = 0;
                    $goods['shop_price'] = 0;
                    $goods['promote_price'] = 0;
                    if (isset($goods['get_warehouse_area_goods']) && !empty('get_warehouse_area_goods')) {
                        $goods['goods_number'] = $goods['get_warehouse_area_goods']['region_number'];
                        $goods['shop_price'] = $goods['get_warehouse_goods']['region_price'];
                        $goods['promote_price'] = $goods['get_warehouse_goods']['region_promote_price'];
                    }
                }

                $today = TimeRepository::getGmTime();
                $goods['goods_price'] = ($goods['is_promote'] == 1 && $goods['promote_start_date'] <= $today && $goods['promote_end_date'] >= $today) ? $goods['promote_price'] : $goods['shop_price'];

                $goods['warehouse_id'] = $warehouse_id;
                $goods['area_id'] = $area_id;

                /* 取得会员价格 */
                $res = MemberPrice::where('goods_id', $goods_id);
                $res = $res->with(['getUserRank']);
                $goods['user_price'] = BaseRepository::getToArrayGet($res);

                if (!empty($goods['user_price'])) {
                    $shop_price_original = $goods['shop_price'];
                    foreach ($goods['user_price'] as $key => $value) {
                        $value['rank_name'] = '';
                        if (isset($value['get_user_rank']) && !empty($value['get_user_rank'])) {
                            $value['rank_name'] = $value['get_user_rank']['rank_name'];
                        }

                        // 会员价格
                        if (isset($value['percentage']) && $value['percentage'] == 1) {
                            $shop_price = $shop_price_original * $value['user_price'] / 100; // 百分比
                        } else {
                            $shop_price = $value['user_price']; // 固定价格
                        }

                        $value['user_price'] = $shop_price;

                        $goods['user_price'][$key] = $value;
                    }
                }

                /* 取得商品属性 */
                $goods['attr_list'] = [];

                $res = GoodsAttr::where('goods_id', $goods_id)->orderBy('goods_attr_id');
                $res = $res->with(['getGoodsAttribute']);
                //ecmoban模板堂 --zhuo satrt

                if ($goods['model_attr'] == 1) {
                    $res = $res->with(['getGoodsWarehouseAttr' => function ($query) use ($warehouse_id) {
                        $query->where('warehouse_id', $warehouse_id);
                    }]);
                } elseif ($goods['model_attr'] == 2) {
                    $res = $res->with(['getGoodsWarehouseAreaAttr' => function ($query) use ($area_id) {
                        $query->where('area_id', $area_id);
                    }]);
                }
                //ecmoban模板堂 --zhuo end

                $res = BaseRepository::getToArrayGet($res);

                foreach ($res as $row) {
                    $row['attr_name'] = '';
                    $row['attr_input_type'] = '';
                    $row['attr_type'] = '';
                    if (isset($row['get_goods_attribute']) && !empty($row['get_goods_attribute'])) {
                        $row['attr_name'] = $row['get_goods_attribute']['attr_name'];
                        $row['attr_input_type'] = $row['get_goods_attribute']['attr_input_type'];
                        $row['attr_type'] = $row['get_goods_attribute']['attr_type'];
                    }

                    $row['warehouse_attr_price'] = 0;
                    $row['area_attr_price'] = 0;
                    if (isset($row['get_goods_warehouse_attr']) && !empty($row['get_goods_warehouse_attr'])) {
                        $row['warehouse_attr_price'] = $row['get_goods_warehouse_attr']['attr_price'];
                    }
                    if (isset($row['get_goods_warehouse_area_attr']) && !empty($row['get_goods_warehouse_area_attr'])) {
                        $row['area_attr_price'] = $row['get_goods_warehouse_area_attr']['attr_price'];
                    }

                    //ecmoban模板堂 --zhuo satrt
                    if ($goods['model_attr'] == 1) {
                        $row['attr_price'] = $row['warehouse_attr_price'];
                    } elseif ($goods['model_attr'] == 2) {
                        $row['attr_price'] = $row['area_attr_price'];
                    } else {
                        $row['attr_price'] = $row['attr_price'];
                    }
                    //ecmoban模板堂 --zhuo end
                    $goods['attr_list'][$row['attr_id']][] = $row;
                }

                $goods['attr_list'] = array_values($goods['attr_list']);

                $goods_attr_id = '';
                $attr_price = 0;
                //ecmoban模板堂 --zhuo start
                if ($goods['attr_list']) {
                    foreach ($goods['attr_list'] as $attr_key => $attr_row) {
                        $goods_attr_id .= $attr_row[0]['goods_attr_id'] . ",";
                        $attr_price += floatval($attr_row[0]['attr_price']);
                    }

                    $goods_attr_id = substr($goods_attr_id, 0, -1);
                    $goods['attr_price'] = $attr_price;
                }

                $products = $this->goodsWarehouseService->getWarehouseAttrNumber($goods_id, $goods_attr_id, $warehouse_id, $area_id, $area_city);
                $attr_number = $products ? $products['product_number'] : 0;
                $product_promote_price = $products['product_promote_price'] ?? 0;

                if (isset($products['product_price']) && $products['product_price']) {
                    $goods['goods_price'] = $products['product_price'];
                }

                $goods['goods_price'] = $product_promote_price > 0 ? $product_promote_price : $goods['goods_price'];

                $res = Products::whereRaw(1);
                if ($goods['model_attr'] == 1) {
                    $res = ProductsWarehouse::where('warehouse_id', $warehouse_id);
                } elseif ($goods['model_attr'] == 2) {
                    $res = ProductsArea::where('area_id', $area_id);
                }

                $res = $res->where('goods_id', $goods_id);
                $prod = BaseRepository::getToArrayFirst($res);

                if (empty($prod)) { //当商品没有属性库存时
                    $attr_number = $goods['goods_number'];
                }

                $attr_number = !empty($attr_number) ? $attr_number : 0;

                $goods['goods_storage'] = $attr_number;
                //ecmoban模板堂 --zhuo end

                return response()->json($goods);
            } elseif ($func == 'get_goods_attr_number') {
                $products = $this->goodsWarehouseService->getWarehouseAttrNumber($goods_id, $get_attr_id, $warehouse_id, $area_id, $area_city);
                $attr_number = $products ? $products['product_number'] : 0;
                $product_promote_price = $products['product_promote_price'] ?? 0;

                if (isset($products['product_price']) && $products['product_price']) {
                    $goods['goods_price'] = $products['product_price'];
                }

                $goods['goods_price'] = $product_promote_price > 0 ? $product_promote_price : $goods['goods_price'];

                $res = Products::whereRaw(1);
                if ($model_attr == 1) {
                    $res = ProductsWarehouse::where('warehouse_id', $warehouse_id);
                } elseif ($model_attr == 2) {
                    $res = ProductsArea::where('area_id', $area_id);
                }

                $res = $res->where('goods_id', $goods_id);
                $prod = BaseRepository::getToArrayFirst($res);

                if (empty($prod)) { //当商品没有属性库存时
                    $attr_number = $goods_number;
                }

                $attr_number = !empty($attr_number) ? $attr_number : 0;

                /* 取得商品属性 */
                $goods['attr_list'] = [];

                $res = GoodsAttr::where('goods_id', $goods_id)->orderBy('goods_attr_id');
                if (isset($_REQUEST['attr']) && !empty($_REQUEST['attr'])) {
                    $attr_arr = BaseRepository::getExplode($_REQUEST['attr']);
                    $res = $res->whereIn('goods_attr_id', $attr_arr);
                }
                $res = $res->with(['getGoodsAttribute']);
                //ecmoban模板堂 --zhuo satrt

                if ($model_attr == 1) {
                    $res = $res->with(['getGoodsWarehouseAttr' => function ($query) use ($warehouse_id) {
                        $query->where('warehouse_id', $warehouse_id);
                    }]);
                } elseif ($model_attr == 2) {
                    $res = $res->with(['getGoodsWarehouseAreaAttr' => function ($query) use ($area_id) {
                        $query->where('area_id', $area_id);
                    }]);
                }
                //ecmoban模板堂 --zhuo end

                $res = BaseRepository::getToArrayGet($res);

                foreach ($res as $row) {
                    $row['attr_name'] = '';
                    $row['attr_input_type'] = '';
                    $row['attr_type'] = '';
                    if (isset($row['get_goods_attribute']) && !empty($row['get_goods_attribute'])) {
                        $row['attr_name'] = $row['get_goods_attribute']['attr_name'];
                        $row['attr_input_type'] = $row['get_goods_attribute']['attr_input_type'];
                        $row['attr_type'] = $row['get_goods_attribute']['attr_type'];
                    }

                    $row['warehouse_attr_price'] = 0;
                    $row['area_attr_price'] = 0;
                    if (isset($row['get_goods_warehouse_attr']) && !empty($row['get_goods_warehouse_attr'])) {
                        $row['warehouse_attr_price'] = $row['get_goods_warehouse_attr']['attr_price'];
                    }
                    if (isset($row['get_goods_warehouse_area_attr']) && !empty($row['get_goods_warehouse_area_attr'])) {
                        $row['area_attr_price'] = $row['get_goods_warehouse_area_attr']['attr_price'];
                    }

                    if ($model_attr == 1) {
                        $row['attr_price'] = $row['warehouse_attr_price'];
                    } elseif ($model_attr == 2) {
                        $row['attr_price'] = $row['area_attr_price'];
                    } else {
                        $row['attr_price'] = $row['attr_price'];
                    }

                    $goods['attr_list'][$row['attr_id']][] = $row;
                }

                $goods['attr_list'] = array_values($goods['attr_list']);

                $goods['attr_price'] = 0;
                $attr_price = 0;
                if ($goods['attr_list']) {
                    foreach ($goods['attr_list'] as $attr_key => $attr_row) {
                        $attr_price += $attr_row[0]['attr_price'];
                    }

                    $goods['attr_price'] = $attr_price;
                }

                $goods['goods_id'] = $goods_id;
                $goods['warehouse_id'] = $warehouse_id;
                $goods['area_id'] = $area_id;
                $goods['user_id'] = $user_id;
                $goods['attr'] = $get_attr_id;
                $goods['model_attr'] = $model_attr;
                $goods['goods_number'] = $goods_number;
                $goods['goods_storage'] = $attr_number;

                return response()->json($goods);
            }
        }

        /*------------------------------------------------------ */
        //-- 合并订单 页面
        /*------------------------------------------------------ */
        elseif ($act == 'merge') {
            /* 检查权限 */
            admin_priv('order_os_edit');

            /* 模板赋值 */
            $this->smarty->assign('ur_here', lang('admin/common.04_merge_order'));
            $this->smarty->assign('action_link', ['href' => 'order.php?act=list', 'text' => lang('admin/common.02_order_list')]);

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            /* 显示模板 */
            return $this->smarty->display('merge_order.dwt');
        }

        /*------------------------------------------------------ */
        //-- 异步加载合并订单弹框
        /*------------------------------------------------------ */
        elseif ($act == 'merge_order_list') {
            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            return make_json_result($this->smarty->fetch('library/merge_order_list.lbi'));
        }

        /*------------------------------------------------------ */
        //-- 合并订单查询现有订单列表
        /*------------------------------------------------------ */
        elseif ($act == 'ajax_merge_order_list') {

            /* 检查权限 */
            admin_priv('order_os_edit');

            $merchant_id = empty($_REQUEST['merchant_id']) ? '' : intval($_REQUEST['merchant_id']);
            $store_search = empty($_REQUEST['store_search']) ? -1 : intval($_REQUEST['store_search']);
            $user_name = empty($_REQUEST['user_name']) ? '' : trim($_REQUEST['user_name']);
            $order_sn = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
            $pay_status = empty($_REQUEST['pay_status']) ? 0 : trim($_REQUEST['pay_status']);

            if ($store_search != 1) {
                $merchant_id = 0;
            }

            $res = OrderInfo::query();

            if (!empty($order_sn)) {
                $res = $res->where('order_sn', 'LIKE', '%' . $order_sn . '%');
            }

            /* 取得满足条件的订单 */
            // 1. 合并订单条件：未分单的普通订单或子订单
            // 2. 订单状态：已确认 未发货 已付款或未付款
            $res = $res->where('ru_id', $merchant_id)
                ->where('order_status', OS_CONFIRMED)
                ->where('pay_status', $pay_status)
                ->where('shipping_status', SS_UNSHIPPED)
                ->where('main_count', 0)
                ->where('extension_code', '');

            $res = CommonRepository::constantMaxId($res, 'user_id');

            if (!empty($user_name)) {

                $userId = Users::query()->select('user_id')
                    ->where('user_name', 'like', '%' . $user_name . '%')
                    ->pluck('user_id');
                $userId = BaseRepository::getToArray($userId);

                if (empty($userId)) {
                    $userId = [-1]; //设置查询失败条件
                }

                $res = $res->whereIn('user_id', $userId);
            }

            $res = $res->select('order_sn', 'user_id');

            $order_list = BaseRepository::getToArrayGet($res);

            $html = '';
            if (!empty($order_list)) {

                $userId = BaseRepository::getKeyPluck($order_list, 'user_id');
                $userList = UserDataHandleService::userDataList($userId, ['user_id', 'user_name']);

                foreach ($order_list as $k => $v) {

                    $v['user_name'] = $userList[$v['user_id']]['user_name'] ?? '';

                    $html .= "<li><a href='javascript:;' data-value = '" . $v['order_sn'] . "'>" . $v['order_sn'] . "[" . $v['user_name'] . "]</a></li>";
                }
            }

            return make_json_result($html);
        }

        /*------------------------------------------------------ */
        //-- 合并订单 提交
        /*------------------------------------------------------ */
        elseif ($act == 'ajax_merge_order') {
            /* 检查权限 */
            admin_priv('order_os_edit');

            // from_order_sn: ["2020011709402479351"]  to_order_sn: o2018051518208788790
            $to_order_sn = request()->input('to_order_sn', '');
            $from_order_sn = request()->input('from_order_sn', '');

            $to_order_sn = empty($to_order_sn) ? '' : json_str_iconv(substr($to_order_sn, 1));
            if (!empty($from_order_sn)) {
                $from_order_sn = dsc_decode($from_order_sn);
                if (!is_array($from_order_sn)) {
                    $from_order_sn = explode(' ', $from_order_sn);
                }
                $from_order_sn = array_unique($from_order_sn);
            }

            $m_result = merge_order($from_order_sn, $to_order_sn);
            $result = ['error' => 0, 'content' => ''];
            if ($m_result === true) {
                $result['message'] = lang('admin/order.act_ok');
            } else {
                $result['error'] = 1;
                $result['message'] = $m_result;
            }
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 删除订单
        /*------------------------------------------------------ */
        elseif ($act == 'remove_order') {
            /* 检查权限 */
            admin_priv('order_edit');

            $order_id = (int)request()->input('id', 0);

            /* 检查权限 */
            $check_auth = check_authz_json('order_edit');
            if ($check_auth !== true) {
                return $check_auth;
            }

            /* 检查订单是否允许删除操作 */
            $order = OrderInfo::query()->where('order_id', $order_id)->first();
            $order = $order ? $order->toArray() : [];
            if (empty($order)) {
                return make_json_error('Hacking attempt');
            }

            $operable_list = $this->orderManageService->operableList($order, session('action_list'));
            if (!isset($operable_list['remove'])) {
                return make_json_error('Hacking attempt');
            }

            $return_order = OrderReturn::query()->where('order_id', $order_id)->count();
            if ($return_order) {
                return make_json_error(sprintf(trans('admin/order.order_remove_failure'), $order['order_sn']));
            }

            // 检查订单是否有未处理投诉
            $complaint_order = DB::table('complaint')->where('order_id', $order_id)->where('complaint_state', '<>', 4)->count('complaint_id');
            if ($complaint_order) {
                return make_json_error(sprintf(trans('admin/order.complaint_order_remove_failure'), $order['order_sn']));
            }

            $res = OrderInfo::where('order_id', $order_id)->delete();
            if ($res) {
                $this->orderManageService->delDelivery($order_id, ['delivery', 'back']);

                // 订单删除事件
                event(new \App\Events\OrderRemoveEvent($order));

                /* 记录日志 */
                admin_log($order['order_sn'], 'remove', 'order');

                $url = 'order.php?act=query&' . str_replace('act=remove_order', '', request()->server('QUERY_STRING'));
                return dsc_header("Location: $url\n");
            } else {
                return make_json_error('error');
            }
        }

        /*------------------------------------------------------ */
        //-- 根据关键字和id搜索用户
        /*------------------------------------------------------ */
        elseif ($act == 'search_users') {

            $id_name = request()->input('id_name', '');
            $id_name = empty($id_name) ? '' : json_str_iconv(trim($id_name));

            $result = ['error' => 0, 'message' => '', 'content' => ''];
            if (!empty($id_name)) {
                $res = Users::where('user_id', 'LIKE', '%' . mysql_like_quote($id_name) . '%')
                    ->orWhere('user_name', 'LIKE', '%' . mysql_like_quote($id_name) . '%')
                    ->select('user_id', 'user_name');
                $res = BaseRepository::getToArrayGet($res);

                $result['userlist'] = [];
                foreach ($res as $row) {
                    if (config('shop.show_mobile', 0) == 0) {
                        $row['user_name'] = $this->dscRepository->stringToStar($row['user_name']);
                    }

                    $result['userlist'][] = ['user_id' => $row['user_id'], 'user_name' => $row['user_name']];
                }
            } else {
                $result['error'] = 1;
                $result['message'] = 'NO KEYWORDS!';
            }

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 根据关键字搜索商品
        /*------------------------------------------------------ */
        elseif ($act == 'search_goods') {
            $keyword = empty($_GET['keyword']) ? '' : json_str_iconv(trim($_GET['keyword']));
            $order_id = empty($_GET['order_id']) ? '' : intval($_GET['order_id']);
            $warehouse_id = empty($_GET['warehouse_id']) ? '' : intval($_GET['warehouse_id']);
            $area_id = empty($_GET['area_id']) ? '' : intval($_GET['area_id']);
            $goods_count = isset($goods_count) ? $goods_count : 0;

            $result = ['error' => 0, 'message' => '', 'content' => ''];

            if (empty($keyword)) {
                $result['error'] = 1;
                $result['message'] = 'NO KEYWORDS';
                return response()->json($result);
            } else {
                if ($goods_count) {
                    $ru_id = OrderGoods::query()->where('order_id', $order_id)->pluck('ru_id')->toArray();
                } else {
                    $ru_id = ['0' => ['ru_id' => $adminru['ru_id']]];
                }

                $res = Goods::query();
                if ($ru_id) {
                    $ru_id = BaseRepository::getExplode($ru_id);
                    $res = $res->whereIn('user_id', $ru_id);
                }

                $res = $res->where('is_delete', 0)
                    ->where('is_on_sale', 1)
                    ->where('is_alone_sale', 1)
                    ->where(function ($query) use ($keyword) {
                        $query->where('goods_id', 'LIKE', '%' . mysql_like_quote($keyword) . '%')
                            ->orWhere('goods_name', 'LIKE', '%' . mysql_like_quote($keyword) . '%')
                            ->orWhere('goods_sn', 'LIKE', '%' . mysql_like_quote($keyword) . '%');
                    })->limit(20);
                $res = BaseRepository::getToArrayGet($res);

                $result['goodslist'] = [];
                foreach ($res as $row) {
                    $result['warehouse_id'] = $warehouse_id;
                    $result['area_id'] = $area_id;
                    $result['goodslist'][] = ['goods_id' => $row['goods_id'], 'name' => $row['goods_id'] . '  ' . $row['goods_name'] . '  ' . $row['goods_sn'], 'user_id' => $row['user_id']];
                }
            }

            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 编辑收货单号
        /* ------------------------------------------------------ */
        elseif ($act == 'edit_invoice_no') {
            /* 检查权限 */
            $check_auth = check_authz_json('order_edit');
            if ($check_auth !== true) {
                return $check_auth;
            }

            // 数据验证
            $messages = [
                'id.required' => trans('admin/order.order_id_is_empty'),
                'val.required' => trans('admin::order.invoice_no_sms'),
                'val.regex' => trans('admin::order.invoice_no_notice')
            ];
            $validator = Validator::make(request()->all(), [
                'id' => 'required|integer',
                'val' => ['required', 'regex:/^[A-Za-z0-9|\,]*$/'], //发货单号仅支持字母或数字 或, 例如：222,333
            ], $messages);

            // 返回错误
            if ($validator->fails()) {
                return make_json_error($validator->errors()->first());
            }

            $order_id = (int)request()->input('id');

            $invoice_no = e(request()->input('val', ''));
            $invoice_no = trim($invoice_no);

            $res = OrderInfo::where('order_id', $order_id)->select('order_sn', 'order_status', 'shipping_status', 'pay_status', 'invoice_no');
            $order = BaseRepository::getToArrayFirst($res);

            $data = ['invoice_no' => $invoice_no];
            $res = OrderInfo::where('order_id', $order_id)->update($data);
            if ($res > 0) {
                if (!empty($invoice_no) && $invoice_no != $order['invoice_no']) {
                    $up = DeliveryOrder::where('order_id', $order_id)->update($data);

                    if ($up) {
                        if (empty($order['invoice_no'])) {
                            $order['invoice_no'] = 'N/A';
                        }

                        $note = sprintf(lang('admin/order.edit_order_invoice'), $order['invoice_no'], $invoice_no);
                        order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], $note, session('admin_name'));
                    }
                }

                if (empty($invoice_no)) {
                    return make_json_result('N/A');
                } else {
                    return make_json_result(stripcslashes($invoice_no));
                }
            } else {
                return make_json_error('');
            }
        }

        /*------------------------------------------------------ */
        //-- 编辑付款备注
        /*------------------------------------------------------ */
        elseif ($act == 'edit_pay_note') {
            /* 检查权限 */
            $check_auth = check_authz_json('order_edit');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $order_id = (int)request()->input('id');

            if ($order_id == 0) {
                return make_json_error('NO ORDER ID');
            }

            $val = request()->input('val');
            $no = empty($val) ? '' : json_str_iconv(trim($val));

            $data = ['pay_note' => $no];
            $res = OrderInfo::where('order_id', $order_id)->update($data);

            if ($res > 0) {
                if (empty($no)) {
                    return make_json_result('N/A');
                } else {
                    return make_json_result(stripcslashes($no));
                }
            } else {
                return make_json_error('fail');
            }
        }

        /*------------------------------------------------------ */
        //-- 获取订单商品信息
        /*------------------------------------------------------ */
        elseif ($act == 'get_goods_info') {
            /* 取得订单商品 */
            $order_id = (int)request()->input('order_id');
            if (empty($order_id)) {
                return make_json_response('', 1, $GLOBALS['_LANG']['error_get_goods_info']);
            }
            $goods_list = [];
            $goods_attr = [];

            $res = OrderGoods::where('order_id', $order_id);

            $res = $res->with(['getGoods' => function ($query) {
                $query->select('goods_id', 'goods_thumb', 'brand_id', 'user_id', 'goods_number', 'model_inventory');
            }]);

            $res = $res->with(['getOrder' => function ($query) {
                $query->select('order_id', 'extension_id', 'extension_code', 'order_sn');
            }]);

            $res = BaseRepository::getToArrayGet($res);

            foreach ($res as $row) {
                $row['o_extension_code'] = $row['extension_code'];

                $row['goods_thumb'] = '';
                $row['brand_id'] = '';
                $row['ru_id'] = '';
                $row['storage'] = '';
                $row['model_inventory'] = '';

                if (isset($row['get_goods']) && !empty($row['get_goods'])) {
                    $row['goods_thumb'] = $row['get_goods']['goods_thumb'];
                    $row['brand_id'] = $row['get_goods']['brand_id'];
                    $row['ru_id'] = $row['get_goods']['user_id'];
                    $row['storage'] = $row['get_goods']['goods_number'];
                    $row['model_inventory'] = $row['get_goods']['model_inventory'];
                }

                $row['extension_id'] = '';
                $row['order_sn'] = '';
                $row['oi_extension_code'] = '';

                if (isset($row['get_order']) && !empty($row['get_order'])) {
                    $row['extension_id'] = $row['get_order']['extension_id'];
                    $row['order_sn'] = $row['get_order']['order_sn'];
                    $row['oi_extension_code'] = $row['get_order']['extension_code'];
                }

                $tp_res = Products::whereRaw(1);
                if ($row['model_inventory'] == 1) {
                    $row['storage'] = get_warehouse_area_goods($row['warehouse_id'], $row['goods_id'], 'warehouse_goods');
                } elseif ($row['model_inventory'] == 2) {
                    $row['storage'] = get_warehouse_area_goods($row['area_id'], $row['goods_id'], 'warehouse_area_goods');
                }
                if ($row['model_attr'] == 1) {
                    $tp_res = ProductsWarehouse::where('warehouse_id', $row['warehouse_id']);
                } elseif ($row['model_attr'] == 2) {
                    $tp_res = ProductsArea::where('area_id', $row['area_id']);
                }

                $tp_res = $tp_res->where('goods_id', $row['goods_id']);
                $prod = BaseRepository::getToArrayFirst($tp_res);

                $products = $this->goodsWarehouseService->getWarehouseAttrNumber($row['goods_id'], $row['goods_attr_id'], $row['warehouse_id'], $row['area_id'], $row['area_city'], $row['model_attr']);
                $row['goods_storage'] = isset($products['product_number']) ? $products['product_number'] : '';

                if (empty($prod)) { //当商品没有属性库存时
                    $row['goods_storage'] = $row['storage'];
                }
                $row['extension_id'] = $row['extension_id'];
                $row['storage'] = !empty($row['goods_storage']) ? $row['goods_storage'] : 0;

                $brand = get_goods_brand_info($row['brand_id']);
                $row['brand_name'] = $brand['brand_name'];

                if ($row['o_extension_code'] == 'package_buy') {
                    $row['goods_thumb'] = $this->dscRepository->getImagePath('images/common/package_goods_default.png');
                }

                $subtotal = $row['goods_price'] * $row['goods_number'];
                if ($row['extension_code'] == 'bulk') {
                    $subtotal = $subtotal / 1000;
                }
                $row['formated_subtotal'] = price_format($subtotal);
                $row['formated_goods_price'] = price_format($row['goods_price']);

                //图片显示
                $row['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);

                $trade_id = $this->orderCommonService->getFindSnapshot($row['order_sn'], $row['goods_id']);
                if ($trade_id) {
                    $row['trade_url'] = $this->dscRepository->dscUrl("trade_snapshot.php?act=trade&user_id=" . $row['user_id'] . "&tradeId=" . $trade_id . "&snapshot=true");
                }

                $goods_attr[] = explode(' ', trim($row['goods_attr'])); //将商品属性拆分为一个数组
                $goods_list[] = $row;
            }

            $attr = [];
            $arr = [];
            if ($goods_attr) {
                foreach ($goods_attr as $index => $array_val) {
                    $array_val = BaseRepository::getExplode($array_val);

                    if ($array_val) {
                        foreach ($array_val as $value) {
                            $arr = explode(':', $value);//以 : 号将属性拆开
                            $attr[$index][] = @['name' => $arr[0], 'value' => $arr[1]];
                        }
                    }
                }
            }

            $this->smarty->assign('goods_attr', $attr);
            $this->smarty->assign('goods_list', $goods_list);
            $str = $this->smarty->fetch('show_order_goods.dwt');
            $goods[] = ['order_id' => $order_id, 'str' => $str];
            return make_json_result($goods);
        } /**
         * 修改收货时间
         * by Leah
         */
        elseif ($act == 'update_info') {

            $order_id = (int)request()->input('order_id');
            $sign_time = request()->input('time');

            if (empty($order_id) || empty($sign_time)) {
                return 'invalid parameter';
            }

            $sign_time = local_strtotime($sign_time);

            $data = ['sign_time' => $sign_time];
            OrderInfo::where('order_id', $order_id)->update($data);
        }

        /*------------------------------------------------------ */
        //-- 设置抢单页面
        /*------------------------------------------------------ */
        elseif ($act == 'set_grab_order') {
            $order_id = (int)request()->input('order_id');
            $result = ['error' => 0, 'message' => '', 'content' => ''];

            $this->smarty->assign('order_id', $order_id);
            $store_list = get_store_list($order_id);
            $store_order_info = get_store_order_info($order_id, 'order_id');
            if (!empty($store_order_info)) {
                $grab_store_arr = explode(',', $store_order_info['grab_store_list']);
                foreach ($store_list as $key => $val) {
                    $store_list[$key]['is_check'] = 0;
                    if (in_array($val['id'], $grab_store_arr)) {
                        $store_list[$key]['is_check'] = 1;
                    }
                }
            }

            $this->smarty->assign('store_list', $store_list);
            $result['content'] = $this->smarty->fetch('library/set_grab_order.lbi');
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 设置抢单
        /*------------------------------------------------------ */
        elseif ($act == 'set_grab') {

            $order_id = (int)request()->input('order_id');

            $ru_id = get_ru_id($order_id);

            if (isset($_REQUEST['checkboxes']) && count($_REQUEST['checkboxes']) > 0) {
                $grab_store_list = implode(',', $_REQUEST['checkboxes']);
                $store_order_info = get_store_order_info($order_id, 'order_id');
                if (empty($store_order_info)) {
                    $data = [
                        'order_id' => $order_id,
                        'store_id' => 0,
                        'ru_id' => $ru_id,
                        'is_grab_order' => 1,
                        'grab_store_list' => $grab_store_list
                    ];
                    StoreOrder::insert($data);
                } else {
                    $data = ['grab_store_list' => $grab_store_list];
                    StoreOrder::where('order_id', $order_id)->update($data);
                }
            } else {
                StoreOrder::where('order_id', $order_id)
                    ->where('ru_id', $ru_id)
                    ->where('store_id', 0)
                    ->delete();
            }

            return sys_msg($GLOBALS['_LANG']['set_success']);
        }

        /*------------------------------------------------------ */
        //-- 部分发货弹窗 by wu
        /*------------------------------------------------------ */
        elseif ($act == 'part_ship') {
            $check_auth = check_authz_json('order_ss_edit');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $rec_id = (int)request()->input('rec_id');

            //查询数据
            $res = OrderGoods::where('rec_id', $rec_id);

            $res = $res->with(['getProducts' => function ($query) {
                $query->select('product_sn', 'product_id');
            }]);

            $res = $res->with(['getGoods' => function ($query) {
                $query->select('brand_id', 'goods_id', 'model_inventory', 'model_attr', 'suppliers_id', 'goods_number', 'goods_thumb', 'bar_code');

                $query = $query->with(['getBrand' => function ($query) {
                    $query->select('brand_id', 'brand_name');
                }]);
            }]);

            $row = BaseRepository::getToArrayFirst($res);
            if (!empty($row)) {
                $row['product_sn'] = '';
                if (isset($row['get_products']) && !empty($row['get_products'])) {
                    $row['product_sn'] = $row['get_products']['product_sn'];
                }

                $row['model_inventory'] = '';
                $row['model_attr'] = '';
                $row['suppliers_id'] = '';
                $row['storage'] = '';
                $row['goods_thumb'] = '';
                $row['bar_code'] = '';

                $row['brand_name'] = '';

                if (isset($row['get_goods']) && !empty($row['get_goods'])) {
                    $row['model_inventory'] = $row['get_goods']['model_inventory'];
                    $row['model_attr'] = $row['get_goods']['model_attr'];
                    $row['suppliers_id'] = $row['get_goods']['suppliers_id'];
                    $row['storage'] = $row['get_goods']['goods_number'];
                    $row['goods_thumb'] = $row['get_goods']['goods_thumb'];
                    $row['bar_code'] = $row['get_goods']['bar_code'];

                    if (isset($row['get_goods']['get_brand']) && !empty($row['get_goods']['get_brand'])) {
                        $row['brand_name'] = $row['get_goods']['get_brand']['brand_name'];
                    }
                }
            }

            //剩余发货数量
            $row['left_number'] = $row['goods_number'] - $row['send_number'];
            //ecmoban模板堂 --zhuo start
            if ($row['product_id'] > 0) {
                $products = $this->goodsWarehouseService->getWarehouseAttrNumber($row['goods_id'], $row['goods_attr_id'], $row['warehouse_id'], $row['area_id'], $row['area_city'], $row['model_attr']);
                $row['storage'] = $products['product_number'] ?? 0;
            } else {
                if ($row['model_inventory'] == 1) {
                    $row['storage'] = get_warehouse_area_goods($row['warehouse_id'], $row['goods_id'], 'warehouse_goods');
                } elseif ($row['model_inventory'] == 2) {
                    $row['storage'] = get_warehouse_area_goods($row['area_id'], $row['goods_id'], 'warehouse_area_goods');
                }
            }
            //ecmoban模板堂 --zhuo end
            if ($row['extension_code'] == 'package_buy') {
                $row['storage'] = '';
            }
            //订单商品
            $order_goods = $row;
            $this->smarty->assign('order_goods', $order_goods);
            //订单详情
            $order = order_info($order_goods['order_id']);
            $this->smarty->assign('order', $order);
            $this->smarty->assign('operation', 'split');
            $content = $this->smarty->fetch('library/order_part_ship.lbi');
            return make_json_result($content);
        }

        /*------------------------------------------------------ */
        //-- 批量发货弹窗 by wu
        /*------------------------------------------------------ */
        elseif ($act == 'batch_ship') {
            $check_auth = check_authz_json('order_ss_edit');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $delivery_ids = request()->input('delivery_ids', '');
            if (empty($delivery_ids)) {
                $result = ['error' => 1, 'message' => lang('seller/common.illegal_operate'), 'content' => ''];
                return response()->json($result);
            }

            $delivery_ids = BaseRepository::getExplode($delivery_ids);
            $res = DeliveryOrder::whereIn('delivery_id', $delivery_ids)->where('status', 2);
            $res = $res->with([
                'getRegionProvince' => function ($query) {
                    $query->select('region_id', 'region_name');
                },
                'getRegionCity' => function ($query) {
                    $query->select('region_id', 'region_name');
                },
                'getRegionDistrict' => function ($query) {
                    $query->select('region_id', 'region_name');
                },
                'getRegionStreet' => function ($query) {
                    $query->select('region_id', 'region_name');
                }
            ]);
            $delivery_orders = BaseRepository::getToArrayGet($res);

            //按快递分组
            $new_array = [];
            foreach ($delivery_orders as $key => $val) {
                /* 取得区域名 */
                $province = $val['get_region_province']['region_name'] ?? '';
                $city = $val['get_region_city']['region_name'] ?? '';
                $district = $val['get_region_district']['region_name'] ?? '';
                $street = $val['get_region_street']['region_name'] ?? '';
                $val['region'] = $province . ' ' . $city . ' ' . $district . ' ' . $street;

                $new_array[$val['shipping_name']][] = $val;
            }
            //统计同快递各发货单数量
            foreach ($new_array as $key => $val) {
                $arr = [];
                $arr['count'] = count($val);
                $arr['list'] = $val;
                $new_array[$key] = $arr;
            }

            $this->smarty->assign('delivery_orders', $new_array);
            $content = $this->smarty->fetch('library/order_batch_ship.lbi');
            return make_json_result($content);
        }

        /*------------------------------------------------------ */
        //-- 更新数据表
        /*------------------------------------------------------ */
        elseif ($act == 'chang_order') {
            admin_priv('order_view');

            $this->smarty->assign('ur_here', lang('admin/order.update_shop_id'));

            $order_list = BaseRepository::getDiskForeverData('chang_order_list');

            if ($order_list === false) {
                //主订单下有子订单时，则主订单不显示
                $res = OrderInfo::where('ru_id', 0);
                $res = $res->where(function ($query) {
                    $query->whereHasIn('getOrderGoods', function ($query) {
                        $query->where('ru_id', '>', 0);
                    });
                });

                $res = $res->doesntHaveIn('getMainOrderId');
                $order_list = BaseRepository::getToArrayGet($res);

                BaseRepository::setDiskForever('chang_order_list', $order_list);
            }

            $record_count = $order_list ? count($order_list) : 0;

            $this->smarty->assign('record_count', $record_count);
            $this->smarty->assign('page', 1);

            return $this->smarty->display('order_chang.dwt');
        }

        /*------------------------------------------------------ */
        //-- 更新订单商家ID
        /*------------------------------------------------------ */
        elseif ($act == 'chang_order_list') {

            $page = (int)request()->input('page', 1);
            $page_size = (int)request()->input('page_size', 10);

            $order_list = BaseRepository::getDiskForeverData('chang_order_list');

            if ($order_list) {
                foreach ($order_list as $key => $row) {
                    $ru_id = OrderGoods::where('order_id', $row['order_id'])->value('ru_id');
                    $ru_id = $ru_id ? $ru_id : 0;

                    $order_list[$key]['ru_id'] = $ru_id;
                }
            }

            $order_list = $this->dsc->page_array($page_size, $page, $order_list);
            $result['list'] = $order_list['list'][0] ?? [];

            if ($result['list']) {
                $data = ['ru_id' => $result['list']['ru_id']];
                OrderInfo::where('order_id', $result['list']['order_id'])->update($data);
            }

            $result['page'] = $order_list ? $order_list['filter']['page'] + 1 : 1;
            $result['page_size'] = $order_list ? $order_list['filter']['page_size'] : 1;
            $result['record_count'] = $order_list ? $order_list['filter']['record_count'] : 0;
            $result['page_count'] = $order_list ? $order_list['filter']['page_count'] : 0;

            $result['is_stop'] = 1;
            if ($page > $result['page_count']) {
                $result['is_stop'] = 0;
            } else {
                $result['filter_page'] = $order_list['filter']['page'] ?? 0;
            }

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 更新数据表
        /*------------------------------------------------------ */
        elseif ($act == 'chang_order_main') {
            admin_priv('order_view');

            $this->smarty->assign('ur_here', lang('admin/order.order_count'));

            $order_list = BaseRepository::getDiskForeverData('chang_order_main_list');

            if ($order_list === false) {
                $res = OrderInfo::withCount(['getMainOrderId as main_count']);
                $res = $res->having('main_count', '>', 0);
                $order_list = BaseRepository::getToArrayGet($res);

                BaseRepository::setDiskForever('chang_order_main_list', $order_list);
            }

            $record_count = $order_list ? count($order_list) : 0;

            $this->smarty->assign('record_count', $record_count);
            $this->smarty->assign('page', 1);

            return $this->smarty->display('order_chang_main.dwt');
        }

        /*------------------------------------------------------ */
        //-- 更新主订单子的订单数量
        /*------------------------------------------------------ */
        elseif ($act == 'chang_order_main_list') {
            $page = (int)request()->input('page', 1);
            $page_size = (int)request()->input('page_size', 10);

            $order_list = BaseRepository::getDiskForeverData('chang_order_main_list');

            $order_list = $this->dsc->page_array($page_size, $page, $order_list);
            $result['list'] = $order_list['list'][0] ?? [];

            if ($result['list']) {
                $data = ['main_count' => $result['list']['main_count']];
                OrderInfo::where('order_id', $result['list']['order_id'])->update($data);
            }

            $result['page'] = $order_list ? $order_list['filter']['page'] + 1 : 1;
            $result['page_size'] = $order_list ? $order_list['filter']['page_size'] : 1;
            $result['record_count'] = $order_list ? $order_list['filter']['record_count'] : 0;
            $result['page_count'] = $order_list ? $order_list['filter']['page_count'] : 0;

            $result['is_stop'] = 1;
            if ($page > $result['page_count']) {
                $result['is_stop'] = 0;
            } else {
                $result['filter_page'] = $order_list['filter']['page'] ?? 0;
            }

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 一键发货 异步 订单列表
        /*------------------------------------------------------ */
        elseif ($act == 'key_delivery') {
            $invoice_no = empty($_REQUEST['invoice_no']) ? '' : trim($_REQUEST['invoice_no']);  //快递单号
            $order_id = empty($_REQUEST['order_id']) ? '' : trim($_REQUEST['order_id']);  //订单id
            $express_code = empty($_REQUEST['express_code']) ? '' : trim($_REQUEST['express_code']);  //快递公司编码
            $invoice_company = !isset($_REQUEST['invoice_company']) || empty($_REQUEST['invoice_company']) ? '' : trim($_REQUEST['invoice_company']);  //快递公司

            if (empty($invoice_no) || empty($order_id)) {
                /* 操作失败 */
                $result['error'] = 1;
                $result['msg'] = lang('admin/order.act_false');
                return response()->json($result);
            }

            /* 定义当前时间 */
            define('GMTIME_UTC', TimeRepository::getGmTime()); // 获取 UTC 时间戳

            $order_arr[0]['order_id'] = $order_id;
            $order_arr[0]['invoice_no'] = $invoice_no;
            $order_arr[0]['express_code'] = $express_code;

            $action_note = $action_note ?? '';
            $res = $this->orderManageService->oneClickShipping($order_arr, $action_note, 'one', session('admin_id'), session('admin_name'));

            if (!empty($res['delivery_id'])) {

                if (file_exists(WXAPP_MEDIA)) {
                    $extendParam = [
                        'invoice_company' => $invoice_company,
                        'invoice_no' => $invoice_no,
                    ];

                    //订单发货事件
                    event(new \App\Modules\WxMedia\Events\MediaOrderShipmentsEvent(['order_id' => $order_id], $extendParam));
                }
            }

            /* 操作成功 */
            $result['error'] = $res['error_code'] ?? 0;
            $result['msg'] = $res['msg'] ?? '';
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 一键发货 批量 弹窗
        /*------------------------------------------------------ */
        elseif ($act == 'key_delivery_many') {

            $order_sn = e(request()->input('order_sn', '')); //订单号
            if (empty($order_sn)) {
                /* 操作失败 */
                $result['error'] = 1;
                $result['msg'] = lang('admin/order.act_false');
                return response()->json($result);
            }

            $order_sn = explode(',', $order_sn);
            $res = $this->orderManageService->getShippingInfo($order_sn);

            $this->smarty->assign('res', $res);
            $this->smarty->assign('express_list', $this->expressManageService->getExpressCompany(1));
            $result['content'] = $this->smarty->fetch('library/key_delivery_many.lbi');

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 一键发货 批量 提交
        /*------------------------------------------------------ */
        elseif ($act == 'key_delivery_many_order') {
            $order_arr = request()->input('orderlist', []); //订单发货号数组
            if (empty($order_arr)) {
                /* 操作失败 */
                $result['error'] = 1;
                $result['msg'] = lang('admin/order.act_false');
                return response()->json($result);
            }

            /* 定义当前时间 */
            define('GMTIME_UTC', TimeRepository::getGmTime()); // 获取 UTC 时间戳

            $action_note = $action_note ?? 'batch';
            $res = $this->orderManageService->oneClickShipping($order_arr, $action_note, 'many', session('admin_id'), session('admin_name'));
            $result['error'] = $res['error_code'] ?? 0;
            $result['msg'] = $res['msg'] ?? '';
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 修改设置自动确认收货的时间（天为单位）
        /*------------------------------------------------------ */
        elseif ($act == 'edit_auto_delivery_time') {
            return $this->edit_auto_delivery_time();
        }

        return;
    }

    /**
     * 修改设置自动确认收货的时间（天为单位）
     *
     * @return mixed
     */
    public function edit_auto_delivery_time()
    {
        $check_auth = check_authz_json('order_edit');
        if ($check_auth !== true) {
            return $check_auth;
        }

        $order_id = (int)request()->input('id', 0);
        $delivery_time = request()->input('val', '');
        $delivery_time = json_str_iconv(trim($delivery_time));

        /* 删除数据 */
        $data = ['auto_delivery_time' => $delivery_time];
        OrderInfo::where('order_id', $order_id)->update($data);

        clear_cache_files();
        return make_json_result($delivery_time);
    }

    public function trimArrayWalk(&$array_value)
    {
        if (is_array($array_value)) {
            array_walk($array_value, [$this, "trimArrayWalk"]);
        } else {
            $array_value = trim($array_value);
        }
    }

    public function intvalArrayWalk(&$array_value)
    {
        if (is_array($array_value)) {
            array_walk($array_value, [$this, "intvalArrayWalk"]);
        } else {
            $array_value = intval($array_value);
        }
    }

    /**
     * 处理编辑订单时订单金额变动
     *
     * @param array $order 订单信息
     * @param array $msgs 提示信息
     * @param array $links 链接信息
     * @return mixed|void
     * @throws \Exception
     */
    private function handle_order_money_change($order, &$msgs, &$links)
    {
        $order_id = $order['order_id'];
        if ($order['pay_status'] == PS_PAYED || $order['pay_status'] == PS_PAYING) {
            /* 应付款金额 */
            $money_dues = $order['order_amount'];
            if ($money_dues > 0) {
                /* 修改订单为未付款 */
                update_order($order_id, ['pay_status' => PS_UNPAYED, 'pay_time' => 0]);
                $msgs[] = $GLOBALS['_LANG']['amount_increase'];
                $links[] = ['text' => lang('admin/order.order_info'), 'href' => 'order.php?act=info&order_id=' . $order_id];
                return sys_msg(lang('admin/order.refund_type_notic_two'), 1, $links);
            } elseif ($money_dues < 0) {
                $anonymous = $order['user_id'] > 0 ? 0 : 1;
                $msgs[] = $GLOBALS['_LANG']['amount_decrease'];
                $links[] = ['text' => lang('admin/order.refund'), 'href' => 'order.php?act=process&func=load_refund&anonymous=' .
                    $anonymous . '&order_id=' . $order_id . '&refund_amount=' . abs($money_dues)];
            }
        }
    }
}
