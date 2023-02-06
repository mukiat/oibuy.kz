<?php

namespace App\Services\Order;


use App\Models\OrderInfo;
use App\Models\ValueCard;
use App\Models\ValueCardRecord;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\TimeRepository;

class OrderActivityService
{
    private $orderCommonService;

    public function __construct(
        OrderCommonService $orderCommonService
    )
    {
        $this->orderCommonService = $orderCommonService;
    }

    /**
     * 订单退款[储值卡金额]
     *
     * @param int $order_id 订单ID
     * @param int $vc_id 储值卡ID
     * @param int $refund_vcard
     * @param string $return_sn 储值卡金额
     * @param int $ret_id 单品退货单ID
     * @param string $handle_name 操作员
     * @throws \Exception
     */
    public function getReturnVcard($order_id = 0, $vc_id = 0, $refund_vcard = 0, $return_sn = '', $ret_id = 0, $handle_name = '')
    {
        if ($vc_id && $refund_vcard > 0) {
            $time = TimeRepository::getGmTime();
            $order_info = OrderInfo::select('order_id', 'user_id', 'order_sn', 'order_status', 'shipping_status', 'pay_status')->where('order_id', $order_id);
            $order_info = BaseRepository::getToArrayFirst($order_info);

            $refund_vcard = empty($refund_vcard) ? 0 : $refund_vcard;

            /* 更新储值卡金额 */
            ValueCard::where('vid', $vc_id)->where('user_id', $order_info['user_id'])->increment('card_money', $refund_vcard);

            /* 更新订单使用储值卡金额 */
            $log = [
                'vc_id' => $vc_id,
                'order_id' => $order_id,
                'use_val' => $refund_vcard,
                'vc_dis' => 1,
                'add_val' => $refund_vcard,
                'record_time' => $time,
                'change_desc' => sprintf(lang('admin/order.return_card_record'), $order_info['order_sn']),
                'ret_id' => $ret_id
            ];

            ValueCardRecord::insert($log);

            if ($return_sn) {
                $return_sn = "<br/>" .lang('order.return_sn'). "：" . $return_sn;
            }

            $note = sprintf(lang('user.order_vcard_return') . $return_sn, $refund_vcard);
            $this->orderCommonService->orderAction($order_info['order_sn'], $order_info['order_status'], $order_info['shipping_status'], $order_info['pay_status'], $note, $handle_name, 0, $time);

            $return_note = sprintf(lang('user.order_vcard_return'), $refund_vcard);
            $this->orderCommonService->returnAction($ret_id, RF_AGREE_APPLY, FF_REFOUND, $return_note, $handle_name);
        }
    }
}