<?php

namespace App\Modules\Admin\Controllers;

use App\Jobs\Export\ProcessOrderExport;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Order\OrderExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * 订单导出
 * Class OrderExportController
 * @package App\Modules\Admin\Controllers
 */
class OrderExportController extends BaseController
{
    protected $dscRepository;
    protected $merchantCommonService;

    public function __construct(
        DscRepository $dscRepository,
        MerchantCommonService $merchantCommonService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->merchantCommonService = $merchantCommonService;
    }

    protected function initialize()
    {
        parent::initialize();
    }

    /**
     * 订单导出
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function export(Request $request)
    {
        $this->assign('ur_here', lang('admin/common.14_export_list'));

        // 订单状态
        $order_status = OrderExportService::orderStatus();
        $this->assign('order_status', $order_status);

        // 订单类型
        $extension_code = OrderExportService::extensionCode();
        $this->assign('extension_code', $extension_code);

        // 订单来源
        $order_referer = OrderExportService::orderReferer();
        $this->assign('order_referer', $order_referer);

        // 导出订单内容 字段
        $fieldName = OrderExportService::fieldName();
        $this->assign('field_name', $fieldName);

        // 商家信息
        $ru_id = $request->get('ru_id', 0);
        $shopName = $this->merchantCommonService->getShopName($ru_id, 1);

        $this->assign('ru_id', $ru_id);
        $this->assign('shop_name', $shopName ?? '');

        return $this->display();
    }

    /**
     * 订单列表导出推入任务队列
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportHandler(Request $request)
    {
        $filter['order_status'] = $request->input('order_status', 0); // 订单状态
        $filter['extension_code'] = $request->input('extension_code', 'extension_all'); // 订单类型
        $filter['order_referer'] = $request->input('order_referer', 'all_referer'); //订单来源
        $filter['order_sn'] = $request->input('order_sn', ''); // 订单号
        $filter['user_name'] = $request->input('user_name', ''); // 下单会员
        $filter['consignee'] = $request->input('consignee', ''); //收货人
        $filter['ru_id'] = $request->input('ru_id', 0); //商家id
        $filter['start_time'] = $request->input('start_time', ''); // 开始时间
        $filter['end_time'] = $request->input('end_time', ''); // 结束时间
        $filter['start_time'] = $filter['end_time'] ? TimeRepository::getLocalStrtoTime($filter['start_time']) : '';
        $filter['end_time'] = $filter['end_time'] ? TimeRepository::getLocalStrtoTime($filter['end_time']) : '';
        $filter['field_name'] = $request->input('field_name', ''); // 导出内容
        $filter['page'] = 1;
        $filter['page_size'] = 100;
        $filter['file_name'] = date('YmdHis') . mt_rand(1000, 9999); // 导出文件名

        $filter['admin_id'] = $this->ru_id; // 导出操作管理员id

        // 插入导出记录表
        $count = DB::table('export_history')->where('ru_id', $filter['admin_id'])->where('type', 'order')->where('file_name', $filter['file_name'] . '_' . 1)->count();
        if (empty($count)) {
            $filter['request_id'] = DB::table('export_history')->insertGetId([
                'ru_id' => $filter['admin_id'] ?? 0,
                'type' => 'order',
                'file_name' => $filter['file_name'] . '_' . 1,
                'file_type' => 'xls',
                'download_params' => json_encode($filter),
                'created_at' => Carbon::now(),
            ]);
        }

        // 推入任务队列
        ProcessOrderExport::dispatch($filter);

        return $this->succeed('ok');
    }
}
