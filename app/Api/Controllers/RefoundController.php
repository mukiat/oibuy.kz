<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Exceptions\HttpException;
use App\Services\User\RefoundService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class RefoundController
 * @package App\Api\Controllers
 */
class RefoundController extends Controller
{
    protected $refoundService;

    /**
     * RefoundController constructor.
     * @param RefoundService $refoundService
     */
    public function __construct(
        RefoundService $refoundService
    )
    {
        $this->refoundService = $refoundService;
    }

    /**
     * 退换货列表
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function index(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'page' => 'required|integer',
            'size' => 'required|integer',
        ]);
        $order_id = $request->input('order_id', 0);
        $page = $request->input('page', 1);
        $size = $request->input('size', 10);

        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        //退换货列表。列表显示全部退换货订单，角标显示的是未完成的退换货订单数量  包含待同意未退款  已同意未退款这两种状态
        $list = $this->refoundService->getRefoundList($user_id, $order_id, $page, $size);

        return $this->succeed($list);
    }

    /**
     * 退换货商品列表
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function returngoods(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'order_id' => 'required|integer',
        ]);

        $order_id = $request->input('order_id');

        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        try {
            $list = $this->refoundService->getGoodsOrder($order_id, $user_id);
        } catch (HttpException $httpException) {
            return $this->setErrorCode(422)->failed($httpException->getMessage());
        }

        return $this->succeed($list);
    }

    /**
     * 退换货申请
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function applyreturn(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'order_id' => 'required|integer',
            'rec_id' => 'required|string',
        ]);

        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $order_id = $request->input('order_id', 0);
        $rec_id = $request->input('rec_id', '');
        $rec_id = !empty($rec_id) ? explode(',', $rec_id) : [];

        $data = $this->refoundService->applyReturn($user_id, $order_id, $rec_id);

        return $this->succeed($data);
    }

    /**
     * 退换货详情
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function returndetail(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'ret_id' => 'required|integer',
        ]);
        $ret_id = $request->input('ret_id');

        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        //退换货详情
        $info = $this->refoundService->returnDetail($user_id, $ret_id);

        return $this->succeed($info);
    }

    /**
     * 提交退换货申请
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function submit_return(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'rec_id' => 'required|string',
            'last_option' => 'required|string',
            'return_brief' => 'required|string',
            'chargeoff_status' => 'required|string',
            'return_type' => 'required|integer',
        ]);

        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(trans('user.not_login'));
        }

        $request_info = $request->all();

        // 提交退换货
        $result = $this->refoundService->submitReturn($user_id, $request_info);

        return $this->succeed($result);
    }

    /**
     * 编辑退换货快递信息
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function edit_express(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'ret_id' => 'required|integer',
            'order_id' => 'required|integer',
            'shipping_id' => 'required|integer',
            'express_name' => 'required|string',
            'express_sn' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->setErrorCode(422)->failed($validator->errors()->first());
        }

        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        //编辑退换货
        $ret_id = (int)$request->input('ret_id', 0);
        $order_id = (int)$request->input('order_id', 0);
        $back_shipping_name = $request->input('shipping_id', 0);
        $back_other_shipping = $request->input('express_name', '');
        $back_invoice_no = $request->input('express_sn', '');

        $result = $this->refoundService->editExpress($user_id, $ret_id, $back_shipping_name, $back_other_shipping, $back_invoice_no);

        return $this->succeed($result);
    }

    /**
     * 取消退换货申请
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function cancel(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'ret_id' => 'required|integer'
        ]);

        $ret_id = $request->input('ret_id', 0);

        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $result = $this->refoundService->cancel_return($user_id, $ret_id);

        return $this->succeed($result);
    }

    /**
     * 退换货订单确认收货
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function affirm_receive(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'ret_id' => 'required|integer'
        ]);

        $ret_id = $request->input('ret_id', 0);

        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $result = $this->refoundService->affirmReceivedOrderReturn($user_id, $ret_id);

        return $this->succeed($result);
    }

    /**
     * 激活退换货订单
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function active_return_order(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'ret_id' => 'required|integer'
        ]);

        $ret_id = $request->input('ret_id', 0);

        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $result = $this->refoundService->activationReturnOrder($user_id, $ret_id);

        return $this->succeed($result);
    }

    /**
     * 删除已完成退换货订单
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function delete_return_order(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'ret_id' => 'required|integer'
        ]);

        $ret_id = $request->input('ret_id', 0);

        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $result = $this->refoundService->deleteReturnOrder($user_id, $ret_id);

        return $this->succeed($result);
    }
}
