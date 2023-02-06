<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Models\OrderInfo;
use App\Models\PayLog;
use App\Models\Payment;
use App\Repositories\Common\BaseRepository;
use App\Services\Payment\PaymentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class PaymentController
 * @package App\Api\Controllers
 */
class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(
        PaymentService $paymentService
    )
    {
        $this->paymentService = $paymentService;
    }

    /**
     * 支付列表
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function index(Request $request)
    {
        $user_id = $this->authorization();

        if (empty($user_id)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $order_id = (int)$request->input('order_id', 0);
        $support_cod = (int)$request->input('support_cod', 0);
        $cod_fee = $request->input('cod_fee');
        $cod_fee = $cod_fee ? addslashes($cod_fee) : '';
        $is_online = (int)$request->input('is_online', 0);
        $pay_code = $request->input('pay_code', '');
        $pay_code = $pay_code ? addslashes($pay_code) : '';

        $is_online = ($pay_code == 'onlinepay' && $is_online == 0) ? 1 : $is_online;

        $paymentList = $this->paymentService->getPaymentList($order_id, $support_cod, $cod_fee, $is_online);

        return $this->succeed($paymentList);
    }

    /**
     * 支付按钮
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function code(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'payment_id' => 'required|integer',
            'order_sn' => 'required|integer',
            'log_id' => 'required|integer',
            'order_amount' => 'required',
        ]);

        $user_id = $this->authorization();

        if (empty($user_id)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $paymentid = $request->input('payment_id');
        $order = [
            'order_sn' => $request->input('order_sn'),
            'log_id' => $request->input('log_id'),
            'order_amount' => $request->input('order_amount'),
        ];

        $paymentCode = $this->paymentService->getPaymentCode($user_id, $paymentid, $order);

        return $this->succeed($paymentCode);
    }

    /**
     * 支付同步回调
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function callback(Request $request)
    {
        $code = $request->input('code');

        if (empty($code)) {
            return $this->setErrorCode(404)->failed('not allowd');
        }

        $info = $request->all();

        $paymentCode = $this->paymentService->getCallback($info, $code);

        return $this->succeed($paymentCode);
    }

    /**
     * 支付异步通知
     *
     * @param string $code
     * @param string $type 支付类型 公众号支付、小程序、APP
     * @return array|JsonResponse
     */
    public function notify($code = '', $type = '')
    {
        if (empty($code)) {
            return $this->setErrorCode(404)->failed('not allowd');
        }

        $refer = 'mobile';
        return $this->paymentService->getNotify($code, $refer, $type);
    }

    /**
     * 合单支付异步通知
     *
     * @param string $code
     * @param string $type 支付类型 公众号支付、小程序、APP
     * @return array|JsonResponse
     */
    public function notify_combine($code = '', $type = '')
    {
        if (empty($code)) {
            return $this->setErrorCode(404)->failed('not allowd');
        }

        $refer = 'mobile';
        return $this->paymentService->getNotifyCombine($code, $refer, $type);
    }

    /**
     * 退款异步通知
     *
     * @param string $code
     * @return array|JsonResponse
     */
    public function notify_refound($code = '')
    {
        if (empty($code)) {
            return $this->setErrorCode(404)->failed('not allowd');
        }

        $refer = 'mobile';
        return $this->paymentService->getNotifyRefound($code, $refer);
    }

    /**
     * 合单支付退款异步通知
     *
     * @param string $code
     * @return array|JsonResponse
     */
    public function notify_combine_refound($code = '')
    {
        if (empty($code)) {
            return $this->setErrorCode(404)->failed('not allowd');
        }

        $refer = 'mobile';
        return $this->paymentService->getNotifyCombineRefound($code, $refer);
    }

    /**
     * h5 切换支付方式
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function change_payment(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'pay_id' => 'required|integer',
            'order_id' => 'required|integer',
        ]);

        $user_id = $this->authorization();
        if (empty($user_id)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $order_id = $request->input('order_id', 0);
        $pay_id = $request->input('pay_id', 0);

        $result = $this->paymentService->change_payment($order_id, $pay_id, $user_id);

        return $this->succeed($result);
    }

    /**
     * App收银台切换支付
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function changeAppPayment(Request $request)
    {
        $this->validate($request, [
            'order_sn' => 'required', // 业务订单号
            'pay_code' => 'required' // 支付方式代码，如wxpay，alipay
        ]);

        $user_id = $this->authorization();

        if (empty($user_id)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $order_id = OrderInfo::where('order_sn', $request->get('order_sn'))->value('order_id');

        $pay_id = Payment::where('pay_code', $request->get('pay_code'))->value('pay_id');

        if (empty($order_id) || empty($pay_id)) {
            return $this->failed(lang('common.data_empty'));
        }

        $result = $this->paymentService->change_payment($order_id, $pay_id, $user_id);

        return $this->succeed($result);
    }

    /**
     * wxapp收银台切换支付方式
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function wxappChangeAppPayment(Request $request)
    {
        $this->validate($request, [
            'order_sn' => 'required', // 业务订单号
            'pay_code' => 'required' // 支付方式代码，如wxpay，alipay
        ]);

        $user_id = $this->authorization();

        if (empty($user_id)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $order_amount = $request->get('order_amount');
        $order_sn = $request->get('order_sn');
        $pay_code = $request->get('pay_code');

        $payment_info = $this->paymentService->getPaymentInfo(['pay_code' => $pay_code, 'enabled' => 1]);

        $payment = $this->paymentService->getPayment($payment_info['pay_code']);

        if ($payment === false) {
            return $this->failed(lang('common.data_empty'));
        }

        $orderInfo = OrderInfo::select('order_id', 'media_type')->where('order_sn', $order_sn)->where('user_id', $user_id);
        $orderInfo = BaseRepository::getToArrayFirst($orderInfo);

        $order_id = $orderInfo['order_id'] ?? 0;
        $media_type = $orderInfo['media_type'] ?? 0;

        //获取log_id
        $log_id = PayLog::where('order_id', $order_id)->where('order_type', PAY_ORDER)->orderBy('log_id', 'DESC')->value('log_id');

        $order['order_sn'] = $order_sn;
        $order['log_id'] = $log_id ?? 0;
        $order['order_amount'] = $order_amount ?? 0;
        $order['media_type'] = $media_type;
        $order['user_id'] = $user_id;

        $paymentCode = $payment->get_code($order, unserialize_config($payment_info['pay_config']), $user_id);

        return $this->succeed($paymentCode);
    }
}
