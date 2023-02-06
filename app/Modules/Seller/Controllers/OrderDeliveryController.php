<?php

namespace App\Modules\Seller\Controllers;

use App\Repositories\Common\DscRepository;
use App\Repositories\Common\FileSystemsRepository;
use App\Services\Common\OfficeService;
use App\Services\Order\OrderDeliveryHandleService;
use App\Services\Order\OrderDeliveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/**
 * Class OrderDeliveryController
 * @package App\Modules\Seller\Controllers
 */
class OrderDeliveryController extends BaseController
{
    protected $ru_id = 0;

    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    protected function initialize()
    {
        parent::initialize();

        load_helper('order');
    }

    /**
     * 开始下载 分页查询数据 保存至缓存
     * @param Request $request
     * @param OrderDeliveryService $orderDeliveryService
     * @return \Illuminate\Http\JsonResponse
     */
    public function order_delivery_download(Request $request, OrderDeliveryService $orderDeliveryService)
    {
        $act = $request->input('act', '');
        if ($act == 'ajax_download') {

            $result = ['is_stop' => 0];

            // 查询数据 按分页保存至缓存
            $page = (int)$request->input('page_down', 0); //处理的页数
            $page_count = (int)$request->input('page_count', 0); //总页数

            $admin_id = session('seller_id', 0);

            $cache_id = "order_delivery_download_content_" . $page . "_" . $admin_id;

            // 清除旧缓存
            cache()->forget($cache_id);

            // 获取缓存
            $order_list = cache($cache_id);
            if (is_null($order_list)) {
                $order_list = $orderDeliveryService->order_delivery_list($page, $this->ru_id);//获取订单商品详单数组

                // 保存缓存
                cache()->forever($cache_id, $order_list);
            }

            $result['page'] = $page;
            $result['page_count'] = $page_count;
            if ($page < $page_count) {
                $result['is_stop'] = 1;//未结算标识
                $result['next_page'] = $page + 1;
            }
            return response()->json($result);
        }


        // 开始下载 显示进度页面
        $result = ['content' => ''];

        $page_count = (int)$request->input('page_count', 0); //总页数
        $this->assign('page_count', $page_count);

        $result['content'] = $this->fetch('seller/orderdelivery.library.order_delivery_download');

        return response()->json($result);
    }

    /**
     * 按分页获取缓存数据 导出excel文件, 下载压缩文件
     * @param Request $request
     * @param OrderDeliveryService $orderDeliveryService
     * @return mixed
     */
    public function download_excel(Request $request, OrderDeliveryService $orderDeliveryService)
    {
        $act = $request->input('act', '');
        // 下载压缩文件
        if ($act == 'download_zip') {

            // 文件下载目录
            $dir = 'data/attached/file/';
            $zip_name = lang('admin/common.order_delivery_export_alt') . date('YmdHis') . ".zip";

            $zip_file = FileSystemsRepository::download_zip($dir, $zip_name);

            if ($zip_file) {
                return response()->download($zip_file)->deleteFileAfterSend(); // 下载完成删除zip压缩包
            }

            return back()->withInput(); // 返回
        }


        // 按分页获取缓存数据 导出excel文件
        $page = (int)$request->input('page_down', 0); //处理的页数
        $page_count = (int)$request->input('page_count', 0); //总页数

        $admin_id = session('seller_id', 0);

        $cache_id = "order_delivery_download_content_" . $page . "_" . $admin_id;

        $order_list = cache($cache_id);
        $order_list = !is_null($order_list) ? $order_list : [];

        if (!empty($order_list)) {

            $list = $orderDeliveryService->transformOrderForExcel($order_list['orders']);

            if ($list) {
                // 需要导出字段名称
                $head = [
                    ['column_name' => lang('admin/common.download.rec_id')],
                    ['column_name' => lang('admin/common.download.order_sn'), 'width' => '25'],
                    ['column_name' => lang('admin/common.download.goods_info'), 'width' => '35'],
                    ['column_name' => lang('admin/common.download.goods_sn')],
                    ['column_name' => lang('admin/common.download.goods_price')],
                    ['column_name' => lang('admin/common.download.goods_number')],
                    ['column_name' => lang('admin/common.download.postscript')],
                    ['column_name' => lang('admin/common.download.seller_name')],
                    ['column_name' => lang('admin/common.download.order_user')],
                    ['column_name' => lang('admin/common.download.order_time')],
                    ['column_name' => lang('admin/common.download.pay_time')],
                    ['column_name' => lang('admin/common.download.consignee')],
                    ['column_name' => lang('admin/common.download.tel')],
                    ['column_name' => lang('admin/common.download.address'), 'width' => '35'],
                    ['column_name' => lang('admin/common.download.froms')],
                    ['column_name' => lang('admin/common.download.pay_name')],
                    ['column_name' => lang('admin/common.download.goods_amount')],
                    ['column_name' => lang('admin/common.download.money_paid')],
                    ['column_name' => lang('admin/common.download.order_amount')],
                    ['column_name' => lang('admin/common.download.order_status')],
                    ['column_name' => lang('admin/common.download.pay_status')],
                    ['column_name' => lang('admin/common.download.shipping_status')]
                ];

                $head[]['column_name'] = lang('admin/common.download.shipping_name');
                $head[]['column_name'] = lang('admin/common.download.invoice_no');
                $head[]['column_name'] = lang('admin/common.download.send_number');
                $head[]['column_name'] = lang('admin/common.download.sending_number');

                // 需要导出字段 须和查询数据里的字段名保持一致

                // 订单商品编号ID,订单号,商品名称,商品货号,商品价格,商品数量,买家备注,商家名称,下单会员,下单时间,付款时间,收货人,联系电话,收货地址,订单来源,支付方式,确认状态,付款状态,发货状态,物流公司,物流运单号,已发货数量,发货数量

                $fields = [
                    'rec_id', // 订单商品编号ID
                    'order_sn', // 订单号
                    'goods_info', // 商品名称
                    'goods_sn', // 商品货号
                    'goods_price', // 商品价格
                    'goods_number', // 商品数量
                    'postscript', // 买家留言
                    'seller_name', // 商家名称
                    'buyer', // 下单会员
                    'add_time', // 下单时间
                    'pay_time', // 付款时间
                    'consignee', // 收货人
                    'mobile', // 联系电话
                    'address', // 收货地址
                    'froms', // 订单来源
                    'pay_name', // 支付方式
                    'goods_amount', // 商品金额
                    'money_paid', // 已付款金额
                    'order_amount', // 应付金额
                    'order_status', // 确认状态
                    'pay_status', // 付款状态
                    'shipping_status', // 发货状态
                    'shipping_name', // 物流公司
                    'invoice_no', // 物流运单号
                    'send_number', // 已发货数量
                    'sending_number', // 发货数量
                ];

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
                ];

                // 默认样式
                $spreadsheet->setDefaultStyle();

                // 文件名按分页命名
                $out_title = $title . '-' . $page;

                $spreadsheet->exportExcel($out_title, $head, $fields, $list, $options);

                // 关闭
                $spreadsheet->disconnect();
            }

        }

        /* 清除缓存 */
        cache()->forget($cache_id);

        if ($page < $page_count) {
            $result['is_stop'] = 1;//未结算标识
        } else {
            $result['is_stop'] = 0;
        }
        $result['error'] = 1;
        $result['page'] = $page;

        return response()->json($result);
    }


    // 导入订单商品详单
    public function order_delivery_import(Request $request, OrderDeliveryService $orderDeliveryService)
    {
        // 提交
        if ($request->isMethod('POST')) {

            $form_token = $request->input('form_token'); // form表单隐藏 form_token
            if (!empty($form_token)) {
                $key = Route::currentRouteAction();
                if (cache()->has($form_token)) {
                    // 重复请求
                    return $this->message('form repeat submit', null, 2, true);
                }

                // 正常请求一次记录 form_token 5s后过期
                cache()->put($form_token, $key, Carbon::now()->addSeconds(5));
            }

            // 上传文件
            $file = $request->file('file');

            if ($file && $file->isValid()) {
                // 验证文件格式
                if (!in_array($file->getClientMimeType(), ['application/vnd.ms-excel', 'text/plain'])) {
                    return $this->message(lang('admin/order.not_file_type'), null, 2, true);
                }

                $data['file'] = $file->storeAs('data/attached/file', $file->getClientOriginalName(), 'public');
            }

            if (empty($data['file'])) {
                return $this->message(lang('admin/order.please_upload_excel'), null, 2, true);
            }

            $filename = storage_public($data['file']);

            if (file_exists($filename)) {

                $office = new OfficeService();

                // 订单商品编号ID,订单号,商品名称,商品货号,商品价格,商品数量,买家备注,商家名称,下单会员,下单时间,付款时间,收货人,联系电话,收货地址,订单来源,支付方式,确认状态,付款状态,发货状态,物流公司,物流运单号,已发货数量,发货数量

                $head = [
                    'rec_id', // 订单商品编号ID
                    'order_sn', // 订单号
                    'goods_info', // 商品名称
                    'goods_sn', // 商品货号
                    'goods_price', // 商品价格
                    'goods_number', // 商品数量
                    'postscript', // 买家留言
                    'seller_name', // 商家名称
                    'buyer', // 下单会员
                    'add_time', // 下单时间
                    'pay_time', // 付款时间
                    'consignee', // 收货人
                    'mobile', // 联系电话
                    'address', // 收货地址
                    'froms', // 订单来源
                    'pay_name', // 支付方式
                    'goods_amount', // 商品金额
                    'money_paid', // 已付款金额
                    'order_amount', // 应付金额
                    'order_status', // 确认状态
                    'pay_status', // 付款状态
                    'shipping_status', // 发货状态
                    'shipping_name', // 物流公司
                    'invoice_no', // 物流运单号
                    'send_number', // 已发货数量
                    'sending_number', // 发货数量
                ];

                // 转换格式 ['goods_id'] => ['goods_id' => '12']
                $format = $office->formatHeaderChar($head);

                $res = $office->import($filename, $format);

                if (!empty($res)) {
                    array_shift($res); // 删除第一行数据

                    // 循环处理 返回结果
                    $error_order_record = []; // 记录发货异常订单号
                    $action_status = [];

                    $action_user = session('seller_name', '');
                    $admin_id = session('seller_id', 0);

                    // 格式化处理 导入商品详单信息 （按订单号展示订单商品信息）
                    $result = $orderDeliveryService->transformImportOrderGoods($res);
                    foreach ($result as $k => $item) {

                        if (empty($item)) {
                            $action_status['error'] = 1;
                            continue;
                        }

                        // 1. 订单的所有商品均填写了物流公司与运单号，并且运单号一致，发货数量与订购数量一致，订单即生成一个发货单并为已发货状态

                        // 2. 订单商品中所有商品均填写了物流公司与运单号，发货数量与订购数量也一致，但是运单号有所不同时，按填写的运单号的数量生成对应的发货单，并均为已发货状态

                        // 3. 订单中部分商品未填写运单号，或者填写了但是发货数量小于订购数量，将按实际发货数量生成发货单，并且订单状态修改为部分发货状态
                        $order = OrderDeliveryHandleService::getOrder($item['order_sn']);

                        // 已确认、已分单订单 （含已支付或货到付款订单）
                        if (!empty($order) && in_array($order['order_status'], [OS_CONFIRMED, OS_SPLITED])) {
                            $order_id = $order['order_id'];

                            // 数据库订单商品列表
                            $order['goods_list'] = $orderDeliveryService->getNewOrderGoods(['order_id' => $order_id, 'order_sn' => $order['order_sn']]);

                            // Excel文件 订单商品列表 包含填写的发货数量与物流单号
                            $excel_goods_list = $item['goods_list'] ?? [];

                            $send_number = []; // 发货数量
                            $invoice_no = []; // 物流单号

                            if (!empty($excel_goods_list)) {
                                // Excel 订单商品数量
                                $excel_goods_num = count($excel_goods_list);

                                $invoice_no_number = 0;
                                foreach ($excel_goods_list as $val) {
                                    $send_number[$val['rec_id']] = $val['sending_number'];
                                    $invoice_no[$val['rec_id']] = $val['invoice_no'];
                                    if (!empty($val['invoice_no'])) {
                                        $invoice_no_number++;
                                    }
                                }

                                // 已处理订单商品数组
                                $new_goods_order = [];
                                foreach ($excel_goods_list as $j => $item_goods) {

                                    // 检测物流公司是否与平台安装的物流公司匹配
                                    $item_goods['shipping_name'] = OrderDeliveryHandleService::check_shipping_name($item_goods['shipping_name']);

                                    // 发货数量 且 物流单号、物流公司 不能为空
                                    if (empty($item_goods['sending_number']) || empty($item_goods['invoice_no']) || empty($item_goods['shipping_name'])) {
                                        unset($item_goods[$j]);
                                    }

                                    // 一件订单商品
                                    if ($excel_goods_num == 1) {
                                        // 订单商品运单号 为空
                                        if (empty($item_goods['invoice_no'])) {
                                            $step = 'one-4';
                                            $new_goods_order[$item_goods['rec_id']] = $item_goods['order_sn'];

                                        } else {
                                            // 订单商品运单号 有值
                                            $step = 'one-1';
                                            $new_goods_order[$item_goods['rec_id']] = $item_goods['order_sn'];

                                            $action_status = $orderDeliveryService->toDelivery($step, $order, $item_goods, $send_number, $action_user, $admin_id);
                                        }

                                    } else {
                                        // 二件以上订单商品
                                        /**
                                         * 3. 订单商品运单号 部分为空
                                         * 4. 订单商品运单号 全部为空
                                         */

                                        // 取数组中所有值出现的次数 （运单号）
                                        $invoice_no_count = array_count_values($invoice_no);

                                        if (empty($item_goods['invoice_no'])) {

                                            if (count($invoice_no_count) == 1) {
                                                // 订单商品运单号 全部为空
                                                $step = 'many-4';
                                                $new_goods_order[$item_goods['rec_id']] = $item_goods['order_sn'];

                                                $action_status = $orderDeliveryService->toDelivery($step, $order, $item_goods, $send_number, $action_user, $admin_id);

                                            } else {
                                                // 订单商品运单号 部分为空 空值部分
                                                $step = 'many-3-0';
                                                $new_goods_order[$item_goods['rec_id']] = $item_goods['order_sn'];
                                                $action_status = $orderDeliveryService->toDelivery($step, $order, $item_goods, $send_number, $action_user, $admin_id);

                                            }
                                        } else {

                                            // 订单商品运单号 全部填写
                                            /**
                                             * 1. 订单商品运单号 全部填写 运单号一致
                                             * 2. 订单商品运单号 全部填写 运单号不一致
                                             */
                                            if ($invoice_no_number == $excel_goods_num) {

                                                if (count($invoice_no_count) == 1) {

                                                    // 订单商品 运单号一致
                                                    $step = 'many-1';
                                                    $new_goods_order[$item_goods['rec_id']] = $item_goods['order_sn'];

                                                    $action_status = $orderDeliveryService->toDelivery($step, $order, $item_goods, $send_number, $action_user, $admin_id);
                                                } else {
                                                    // 订单商品 运单号不一致
                                                    $step = 'many-2';
                                                    $new_goods_order[$item_goods['rec_id']] = $item_goods['order_sn'];

                                                    $action_status = $orderDeliveryService->toDelivery($step, $order, $item_goods, $send_number, $action_user, $admin_id);
                                                }
                                            } else {
                                                // 订单商品 运单号 部分为空 有值部分
                                                $step = 'many-3-1';
                                                $new_goods_order[$item_goods['rec_id']] = $item_goods['order_sn'];

                                                $action_status = $orderDeliveryService->toDelivery($step, $order, $item_goods, $send_number, $action_user, $admin_id);
                                            }
                                        }
                                    }

                                    if (isset($action_status[$item_goods['rec_id']]['error']) && $action_status[$item_goods['rec_id']]['error'] >= 1) {
                                        $error_order_record[$item_goods['rec_id']] = $action_status[$item_goods['rec_id']];
                                    }
                                }

                            }

                        }

                    }

                    // 导入成功删除上传文件
                    $this->remove_file($data['file']);

                    if (empty($error_order_record)) {
                        return $this->message(lang('admin/order.order_delivery_import_batch') . lang('admin/common.success'), null, 1, true);
                    } else {
                        $first_record = Arr::first($error_order_record);

                        $msg = !empty($first_record) ? lang('admin/order.order_sn') . ':' . $first_record['order_sn'] . ' ' . $first_record['msg'] . '...' : '';

                        if (config('app.debug')) {
                            //info('error_order_record', $error_order_record);
                        }

                        //return $this->import_message($error_str, null);
                        return $this->message(lang('admin/order.order_delivery_import_batch') . lang('admin/common.fail') . '！' . $msg, null, 2, true);
                    }
                }

            }

            return $this->message(lang('admin/order.order_delivery_import_batch') . lang('admin/common.fail'), null, 2, true);
        }


        // 商家后台当前模块左侧选择菜单（子菜单）
        $child_menu = [
            '04_order' => [
                '02_order_list_01' => 'seller/order_delivery_import',
            ]
        ];

        // 商家后台子菜单语言包 用于当前位置显示
        $child_menu_lang = [
            '02_order_list_01' => lang('admin/order.order_delivery_import'),
        ];
        // 合并菜单语言包
        $GLOBALS['_LANG'] = array_merge($GLOBALS['_LANG'], $child_menu_lang);

        // 合并左侧菜单
        $left_menu = array_merge($GLOBALS['modules'], $child_menu);;

        // 匹配当前选择的菜单列表
        $uri = request()->getRequestUri();
        $uri = ltrim($uri, '/');
        $menu_select = $this->get_menu_arr($uri, $left_menu);

        $this->menu_select = $menu_select;
        $this->assign('menu_select', $menu_select);

        // 当前位置
        $postion = ['ur_here' => $this->menu_select['label'] ?? ''];
        $this->assign('postion', $postion);

        //页面分菜单 by wu start
        $tab_menu = [];
        $tab_menu[] = ['curr' => 0, 'text' => lang('seller/common.02_order_list'), 'href' => 'order.php?act=list'];
        $tab_menu[] = ['curr' => 0, 'text' => lang('seller/common.03_order_query'), 'href' => 'order.php?act=order_query'];
        $tab_menu[] = ['curr' => 1, 'text' => lang('admin/order.order_delivery_import'), 'href' => route('seller/order_delivery_import')];
        $this->assign('tab_menu', $tab_menu);

        // 显示
        return $this->display('seller/orderdelivery.order_delivery_import');
    }

    /**
     * 删除本地文件 （不受开启oss影响）
     * @param string $file 相对路径 data/attached/article/pOFEQJ3wSab1vhsrCVr5k6eU2m7e1bQ7W16dcc14.jpeg
     * @return bool
     */
    protected function remove_file($file = '')
    {
        if (empty($file) || in_array($file, ['/', '\\'])) {
            return false;
        }

        $disk = 'public';

        $exists = Storage::disk($disk)->exists($file);
        if ($exists) {
            return Storage::disk($disk)->delete($file);
        }
        return false;
    }

}
