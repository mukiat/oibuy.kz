<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Extensions\File;
use App\Models\UserOrderNum;
use App\Proxy\ShippingProxy;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Order\OrderMobileService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class OrderController
 * @package App\Api\Controllers
 */
class OrderController extends Controller
{
    /**
     * @var ShippingProxy
     */
    protected $shippingProxy;

    /**
     * @var OrderMobileService
     */
    protected $orderMobileService;

    protected $dscRepository;

    /**
     * OrderController constructor.
     * @param OrderMobileService $orderMobileService
     * @param ShippingProxy $shippingProxy
     * @param DscRepository $dscRepository
     */
    public function __construct(
        OrderMobileService $orderMobileService,
        ShippingProxy $shippingProxy,
        DscRepository $dscRepository
    )
    {
        $this->orderMobileService = $orderMobileService;
        $this->shippingProxy = $shippingProxy;
        $this->dscRepository = $dscRepository;
    }

    /**
     * 订单列表
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function List(Request $request)
    {
        load_helper(['order', 'publicfunc']);

        //数据验证
        $this->validate($request, [
            'page' => "required|integer",
            'size' => "required|integer",
            'status' => "required|integer",
            'type' => "required|string"
        ]);

        $status = $request->input('status', 0); // 0 全部订单，1 待付款， 2 待收货， 3 已完成， 4 回收站
        $type = $request->input('type', '');
        $page = $request->input('page', 1);
        $size = $request->input('size', 10);
        $keywords = $request->input('keywords', '');

        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(trans('user.not_login'));
        }

        $res = $this->orderMobileService->orderList($user_id, $status, $type, $page, $size, $keywords);

        $orderNum = UserOrderNum::where('user_id', $this->uid);
        $orderNum = BaseRepository::getToArrayFirst($orderNum);

        $orderCount = [
            'all' => $orderNum['order_all_num'] ?? 0, //订单数量
            'nopay' => $orderNum['order_nopay'] ?? 0, //待付款订单数量
            'nogoods' => $orderNum['order_nogoods'] ?? 0, //待收货订单数量
            'isfinished' => $orderNum['order_isfinished'] ?? 0, //已完成订单数量
            'isdelete' => $orderNum['order_isdelete'] ?? 0, //回收站订单数量
            'team_num' => $orderNum['order_team_num'] ?? 0, //拼团订单数量
            'not_comment' => $orderNum['order_not_comment'] ?? 0,  //待评价订单数量
            'return_count' => $orderNum['order_return_count'] ?? 0 //待同意状态退换货申请数量
        ];

        $info = [];
        if (file_exists(MOBILE_DRP)) {
            $info['isRegisterDrpShop'] = app(\App\Modules\Drp\Services\Drp\DrpShopService::class)->isRegisterDrpShop($this->uid);
            $info['is_drp'] = 1;
        } else {
            $info['isRegisterDrpShop'] = 0;
            $info['is_drp'] = 0;
        }

        return $this->succeed(['list' => $res, 'count' => $orderCount, 'info' => $info]);
    }

    /**
     * 订单详情
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function Detail(Request $request)
    {
        load_helper(['order', 'publicfunc']);

        //数据验证
        $this->validate($request, [
            'order_id' => "required|integer"
        ]);

        $args['order_id'] = $request->get('order_id');
        $user_id = $args['uid'] = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(trans('user.not_login'));
        }

        checked_pay_Invalid_order($args['order_id'], lang('common.buyer'));

        $order = $this->orderMobileService->orderDetail($args);

        return $this->succeed($order);
    }

    /**
     * 订单确认收货
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function Confirm(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'order_id' => "required|integer"
        ]);

        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(trans('user.not_login'));
        }

        $result = $this->orderMobileService->orderConfirm($user_id, $request->get('order_id'));

        return $this->succeed($result);
    }

    /**
     * 延迟收货申请
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function Delay(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'order_id' => "required|integer"
        ]);

        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(trans('user.not_login'));
        }

        $info = $this->orderMobileService->orderDelay($user_id, $request->get('order_id'));

        return $this->succeed($info);
    }


    /**
     * 订单取消
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function Cancel(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'order_id' => "required|integer"
        ]);

        $args['order_id'] = $request->get('order_id');
        $user_id = $args['uid'] = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(trans('user.not_login'));
        }

        $order = $this->orderMobileService->orderCancel($args);

        return $this->succeed($order);
    }

    /**
     * 订单跟踪
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function tracker(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'type' => "required|string",
            'postid' => "required|string"
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors()->first());
        }

        $type = $request->get('type');
        $post_id = $request->get('postid');
        $order_id = $request->get('order_id', 0);
        $payload = $request->get('payload', []);

        if (!empty($order_id)) {
            $payload['order_id'] = $order_id;
            $mobile = DB::table('order_info')->where('order_id', $payload['order_id'])->value('mobile');
            $payload['mobile'] = !empty($mobile) ? $mobile : '';
        }

        $res = $this->shippingProxy->getExpress($type, $post_id, $payload);

        if ($res['error']) {
            return $this->failed($res['data']);
        } else {
            return $this->succeed($res['data']);
        }
    }

    /**
     * 地图跟踪
     * @param Request $request
     * @return JsonResponse
     * @throws \App\Manager\ExpressTrace\Exception\InvalidArgumentException
     */
    public function mapTrack(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'type' => "required|string",
            'postid' => "required|string",
            'from' => "required|string",
            'to' => "required|string",
            'mobile' => "required|string",
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors()->first());
        }

        $type = $request->get('type');
        $post_id = $request->get('postid');
        $payload = [
            'from' => $request->get('from'),
            'to' => $request->get('to'),
            'mobile' => $request->get('mobile', ''),
        ];

        $res = $this->shippingProxy->mapTrack($type, $post_id, $payload);

        if ($res['error']) {
            return $this->failed($res['data']);
        } else {
            return $this->succeed($res['data']);
        }
    }

    /**
     * 发货单信息查询接口
     * @param Request $request
     * @return JsonResponse
     */
    public function tracker_order(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'delivery_sn' => "required|string",
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors()->first());
        }

        $delivery_sn = $request->get('delivery_sn', '');

        $res = $this->orderMobileService->getTrackerOrderInfo($delivery_sn);

        return $this->succeed($res);
    }

    /**
     * 订单删除
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function Delete(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'order_id' => "required|integer"
        ]);

        $args['order_id'] = $request->get('order_id');
        $user_id = $args['uid'] = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(trans('user.not_login'));
        }

        $order = $this->orderMobileService->orderDelete($args);

        return $this->succeed($order);
    }

    /**
     * 还原订单
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function restore(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'order_id' => "required|integer"
        ]);

        $args['order_id'] = $request->get('order_id');
        $user_id = $args['uid'] = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(trans('user.not_login'));
        }

        $order = $this->orderMobileService->orderRestore($args);

        return $this->succeed($order);
    }

    /**
     * 上传支付凭证
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function upload_pay_document(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer',
            'pay_document' => 'required|string',
        ]);

        // 返回错误
        if ($validator->fails()) {
            return $this->setErrorCode(422)->failed($validator->errors()->first());
        }

        $order_id = $request->input('order_id', 0);
        $upload_pay_document = $request->input('pay_document', '');

        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(trans('user.not_login'));
        }

        $order = DB::table('order_info')->where('order_id', $order_id)->select('user_id', 'order_id')->first();

        if (empty($order)) {
            return $this->setErrorCode(12)->failed(trans('user.order_exist'));
        }

        if ($order->user_id != $user_id) {
            return $this->setErrorCode(12)->failed(trans('user.unauthorized_access'));
        }

        $file_path = DB::table('order_info_bank_transfer')->where('order_id', $order_id)->value('pay_document');

        if ($upload_pay_document) {
            $file_arr = $this->dscRepository->transformOssFile(['pay_document' => $upload_pay_document]);

            $pay_document = $file_arr['pay_document'];

            $where = [
                'user_id' => $user_id,
                'order_id' => $order_id
            ];
            DB::table('order_info_bank_transfer')->updateOrInsert($where, ['pay_document' => $pay_document]);

            if ($file_path) {
                // 删除原图片
                if ($pay_document && $file_path && $file_path != $pay_document) {
                    $file_path = (stripos($file_path, 'no_image') !== false || stripos($file_path, 'assets') !== false) ? '' : $file_path; // 不删除默认空图片
                    File::remove($file_path);
                }
            }
        } else {
            $pay_document = $file_path ?? '';
        }

        $data['pay_document'] = $this->dscRepository->getImagePath($pay_document);

        return $this->succeed($data);
    }


}
