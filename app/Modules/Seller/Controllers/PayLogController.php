<?php

namespace App\Modules\Seller\Controllers;

use App\Repositories\Common\DscRepository;
use App\Services\Order\PayLogService;
use Illuminate\Http\Request;

/**
 * 支付日志管理
 * Class PayLogController
 * @package App\Modules\Seller\Controllers
 */
class PayLogController extends BaseController
{
    protected $ru_id;

    protected $dscRepository;
    protected $payLogService;

    public function __construct(
        DscRepository $dscRepository,
        PayLogService $payLogService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->payLogService = $payLogService;
    }

    protected function initialize()
    {
        parent::initialize();

        // 初始化 每页分页数量
        $this->init_params();

        $left_menu = $GLOBALS['modules'];

        // 匹配选择的菜单列表
        $uri = request()->getRequestUri();
        $uri = ltrim($uri, '/');

        $menu_select = $this->get_menu_arr($uri, $left_menu);
        $this->assign('menu_select', $menu_select);

        // 当前位置
        $postion = ['ur_here' => $menu_select['label'] ?? ''];
        $this->assign('postion', $postion);
    }

    /**
     * 支付单列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $ru_id = $this->ru_id;
        $order_type = $request->input('order_type', PAY_ORDER); // 支付类型 默认 0
        $pay_id = $request->input('pay_id', 0); // 支付方式id 默认 0

        // 搜索
        $keywords = e($request->input('keywords', ''));

        $filter['keywords'] = $keywords;
        $filter['ru_id'] = $ru_id;
        $filter['pay_id'] = $pay_id;
        $offset = $this->pageLimit(route('seller/pay_log/index', $filter), $this->page_num);

        // 列表
        $result = $this->payLogService->getList($order_type, $offset, $filter);

        $total = $result['total'] ?? 0;
        $list = $result['list'] ?? [];

        $page = $this->pageShow($total);
        $this->assign('page', $page);
        $this->assign('list', $list);
        $this->assign('filter', $filter);

        // 筛选商家列表
        $seller_list = $this->payLogService->seller_list();
        $this->assign('seller_list', $seller_list);
        // 筛选支付列表
        $payment_list = $this->payLogService->online_payment_list();
        $this->assign('payment_list', $payment_list);

        $is_ajax = $request->input('is_ajax');
        if ($is_ajax == 1) {
            $respond['content'] = $this->fetch('seller.pay_log.library.index_query');
            $respond['filter'] = $filter;
            $respond['page_count'] = $page['page_count'] ?? 1;
            return response()->json($respond);
        }

        return $this->display('seller.pay_log.index');
    }

    /**
     * 支付单详情
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function detail(Request $request)
    {
        if ($request->isMethod('POST')) {
            $log_id = $request->input('log_id', 0);
            $columns = e($request->input('field', ''));

            if (empty($log_id)) {
                return response()->json(['error' => 1, 'msg' => trans('admin/pay_log.log_id_required')]);
            }

            $content = $this->payLogService->detail($log_id, $columns);

            return response()->json(['status' => 1, 'content' => $content]);
        }

        $log_id = $request->input('log_id', 0);
        $content = $this->payLogService->detail($log_id);

        $this->assign('info', $content);
        return $this->display('seller.pay_log.detail');
    }
}
