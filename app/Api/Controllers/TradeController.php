<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Plugins\UserRights\Discount\Services\DiscountRightsService;
use App\Repositories\Common\DscEncryptRepository;
use App\Services\Article\ArticleService;
use App\Services\CrossBorder\CrossBorderService;
use App\Services\Flow\FlowMobileService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class TradeController
 * @package App\Api\Controllers
 */
class TradeController extends Controller
{
    protected $flowMobileService;
    protected $articleService;

    public function __construct(
        FlowMobileService $flowMobileService,
        ArticleService $articleService
    )
    {
        $this->flowMobileService = $flowMobileService;
        $this->articleService = $articleService;
    }

    /**
     * 订单信息确认
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function orderinfo(Request $request)
    {
        $rec_type = intval($request->input('rec_type', 0)); // 购物车类型
        $type_id = intval($request->input('type_id', 0)); // 购物车类型
        $t_id = intval($request->input('t_id', 0));  // 拼团活动id
        $team_id = intval($request->input('team_id', 0));  // 拼团开团id
        $bs_id = intval($request->input('bs_id', 0));  // 砍价id
        $store_id = intval($request->input('store_id', 0));  // 门店id
        $leader_id = intval($request->input('leader_id', 0));  // 门店id

        // 用户id
        $uid = $this->authorization();

        if (empty($uid)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        // 用户不存在返回
        if (!$this->checkUserExist($uid)) {
            return $this->setErrorCode(102)->failed(lang('user.user_not_exist'));
        }

        $data = $this->flowMobileService->orderInfo($uid, $rec_type, $t_id, $team_id, $bs_id, $store_id, $type_id, $leader_id);

        if (!empty($data)) {

            // 跨境多商户
            if (CROSS_BORDER === true) {
                if (isset($data['is_kj']) && $data['is_kj'] > 0) {
                    $data['cross_border'] = true;
                }
                $data['article_list'] = $this->articleService->getCrossBorderArticleList();
            }

            // 开通购买会员特价权益卡
            $data['use_membership_card'] = 0;
            if (file_exists(MOBILE_DRP)) {
                /**
                 * 1. 未购买或已过期权益卡的会员
                 * 2. 订单商品设置会员特价
                 * 3. 已绑定会员特价权益的会员卡（最低优惠）  领取类型 免费领取、在线支付
                 */
                $data['membership_card_info'] = app(DiscountRightsService::class)->orderMembershipCardInfo('discount', $uid, $data);
                if (!empty($data['membership_card_info'])) {
                    $membership_card_info = $data['membership_card_info'];
                    if (isset($membership_card_info['membership_card_discount_price']) && $membership_card_info['membership_card_discount_price'] > 0) {
                        $data['use_membership_card'] = 1;
                    }
                    $data['total']['membership_card_buy_money'] = $membership_card_info['membership_card_buy_money'];
                    $data['total']['membership_card_buy_money_formated'] = $membership_card_info['membership_card_buy_money_formated'];
                    $data['total']['membership_card_discount_price'] = $membership_card_info['membership_card_discount_price'];
                    $data['total']['membership_card_discount_price_formated'] = $membership_card_info['membership_card_discount_price_formated'];
                }
            }
        }

        return $this->succeed($data);
    }

    /**
     * 结算页选择收货地址
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function change_consignee(Request $request)
    {
        // 用户id
        $uid = $this->authorization();

        if (empty($uid)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $address_id = $request->input('address_id', 0);
        $leader_id = $request->input('leader_id', 0);

        $data = $this->flowMobileService->change_consignee($uid, $address_id, $leader_id);

        return $this->succeed($data);
    }

    /**
     * 优惠券使用
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function changecou(Request $request)
    {
        $uc_id = $request->has('uc_id') ? $request->input('uc_id', []) : [];
        $uc_id = DscEncryptRepository::filterValInt($uc_id);
        $total = $request->input('total');
        $shipping_id = $request->input('shipping_id', []);
        $shipping_id = DscEncryptRepository::filterValInt($shipping_id);

        $uid = $this->authorization();

        if (empty($uid)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $data = $this->flowMobileService->ChangeCou($uc_id, $uid, $total, $shipping_id);

        $data['dis_type'] = 'coupons';

        if ($uc_id > 0) {
            if ($data['amount'] == 0) {
                $data['check_type'] = 1;
            }
        } else {
            $data['check_type'] = 0;
        }


        return $this->succeed($data);
    }

    /**
     * 红包使用
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function changebon(Request $request)
    {
        $this->validate($request, [
            'bonus_id' => 'required'
        ]);

        $bonus_id = $request->input('bonus_id', 0);
        $total = $request->input('total');
        $shipping_id = $request->input('shipping_id', []);
        $shipping_id = DscEncryptRepository::filterValInt($shipping_id);

        $uid = $this->authorization();

        if (empty($uid)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $data = $this->flowMobileService->ChangeBon($bonus_id, $uid, $total, $shipping_id);

        $data['dis_type'] = 'bonus';

        if ($bonus_id > 0) {
            if ($data['amount'] == 0) {
                $data['check_type'] = 1;
            }
        } else {
            $data['check_type'] = 0;
        }


        return $this->succeed($data);
    }

    /**
     * 积分使用
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function changeint(Request $request)
    {
        $this->validate($request, [
            'integral_type' => 'required'
        ]);

        $integral_type = $request->input('integral_type', 0);
        $cart_value = $request->input('cart_value', []);
        $flow_type = $request->input('flow_type', 0);
        $total = $request->input('total');
        $shipping_id = $request->input('shipping_id', []);
        $shipping_id = DscEncryptRepository::filterValInt($shipping_id);

        $uid = $this->authorization();

        if (empty($uid)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $data = $this->flowMobileService->ChangeInt($uid, $total, $integral_type, $shipping_id);

        return $this->succeed($data);
    }

    /**
     * 余额使用
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function changesurplus(Request $request)
    {
        $surplus = $request->input('surplus', 0);
        $surplus = $surplus ? $surplus : 0;
        $shipping_id = $request->input('shipping_id', []);
        $shipping_id = DscEncryptRepository::filterValInt($shipping_id);
        $total = $request->input('total');

        $uid = $this->authorization();

        if (empty($uid)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $data = $this->flowMobileService->ChangeSurplus($uid, $total, $surplus, $shipping_id);

        return $this->succeed($data);
    }

    /**
     * 余额使用
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function changetax(Request $request)
    {
        $total = $request->input('total');
        $total['inv_content'] = $request->input('inv_content');
        $shipping_id = $request->input('shipping_id', []);
        $shipping_id = DscEncryptRepository::filterValInt($shipping_id);

        $uid = $this->authorization();

        if (empty($uid)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $data = $this->flowMobileService->ChangeTax($uid, $total, $shipping_id);

        return $this->succeed($data);
    }

    /**
     * 储值卡使用
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function changecard(Request $request)
    {
        $this->validate($request, [
            'vid' => 'required'
        ]);

        $vid = $request->input('vid', 0);
        $total = $request->input('total');
        $shipping_id = $request->input('shipping_id', []);
        $shipping_id = DscEncryptRepository::filterValInt($shipping_id);

        $uid = $this->authorization();

        if (empty($uid)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $data = $this->flowMobileService->ChangeCard($vid, $uid, $total, $shipping_id);

        return $this->succeed($data);
    }

    /**
     * 会员权益卡开通使用切换
     *
     * @param Request $request
     * @param DiscountRightsService $discountRightsService
     * @return JsonResponse
     * @throws ValidationException
     */
    public function changeMembershipCard(Request $request, DiscountRightsService $discountRightsService)
    {
        $this->validate($request, [
            'order_membership_card_id' => 'required|integer'
        ]);

        $order_membership_card_id = $request->input('order_membership_card_id', 0);
        $membership_card_discount_price = $request->input('membership_card_discount_price', 0);
        $total = $request->input('total');

        $uid = $this->authorization();

        if (empty($uid)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $total['membership_card_discount_price'] = $membership_card_discount_price;
        $data = $discountRightsService->changeMembershipCard($order_membership_card_id, $uid, $total);

        return $this->succeed($data);
    }

    /**
     * 提交
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function done(Request $request)
    {
        $uid = $this->authorization();

        if (empty($uid)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $total = [
            'cart_value' => $request->post('cart_value', []),
            'flow_type' => (int)$request->post('flow_type', 0),
            'store_id' => (int)$request->post('store_id', 0),
            'store_type' => $request->post('store_type', ''),
            'pay_type' => $request->post('pay_type', 0),
            'how_oos' => $request->post('how_oos', 0),
            'card_message' => e($request->post('card_message', '')),
            'inv_type' => $request->post('inv_type', 0),
            'inv_payee' => $request->post('inv_payee', ''),
            'tax_id' => $request->post('tax_id', 0),
            'inv_content' => e($request->post('inv_content', '')),
            'postscript' => $request->post('postscript', []),
            'ru_id' => $request->post('ru_id', []),
            'shipping' => $request->post('shipping', []),
            'shipping_type' => $request->post('shipping_type', []),
            'shipping_code' => $request->post('shipping_code', []),
            'point_id' => $request->post('point_id', 0),
            'flow_points' => $request->post('flow_points', 0),
            'shipping_date_str' => $request->post('shipping_dateStr', 0),
            'pay_id' => (int)$request->post('pay_id', 0),
            'surplus' => $request->post('surplus', 0),
            'use_integral' => $request->post('use_integral', 0),
            'integral' => $request->post('integral', 0),
            'is_surplus' => $request->post('is_surplus', 0),
            'uc_id' => $request->post('uc_id', 0),
            'vc_id' => $request->post('vc_id', 0),
            'need_inv' => $request->post('need_inv', 0),
            'invoice' => $request->post('invoice', 0),
            'vat_id' => $request->post('vat_id', 0),
            'need_insure' => $request->post('need_insure', 0),
            'bonus_id' => $request->post('bonus_id', 0),
            'bonus' => $request->post('bonus', 0),
            'bonus_sn' => $request->post('bonus_sn', 0),
            'coupons' => $request->post('coupons', 0),
            'use_value_card' => $request->post('use_value_card', 0),
            'vc_dis_money' => $request->post('vc_dis_money', 0),
            'extension_code' => $request->post('extension_code', ''),
            'extension_id' => $request->post('extension_id', 0),
            'bs_id' => $request->post('bs_id', 0),
            'team_id' => $request->post('team_id', 0),
            't_id' => $request->post('t_id', 0),
            'store_mobile' => $request->post('store_mobile', 0),
            'take_time' => $request->post('take_time', 0),
            'address_id' => $request->post('address_id', ''),
            'pay_pwd' => e($request->post('pay_pwd', '')),
            'referer' => $request->post('referer', 'H5'),
            'warehouse_id' => $this->warehouse_id,
            'area_id' => $this->area_id,
            'area_city' => $this->area_city,
            'media_type' => $request->post('media_type', 0)
        ];

        if (CROSS_BORDER === true) { // 跨境多商户
            if (!empty($total['ru_id'])) {
                $cbec = app(CrossBorderService::class)->cbecExists();
                $is_kj = 0;
                if (!empty($cbec)) {
                    foreach ($total['ru_id'] as $ru_id) {
                        if ($cbec->isKj($ru_id)) {
                            $is_kj = 1;
                        }
                    }
                }

                if ($is_kj > 0) {
                    $total['rel_name'] = $request->post('rel_name', '');
                    $total['id_num'] = $request->post('id_num', '');
                }
            }
        }

        if (file_exists(MOBILE_GROUPBUY)) {
            $total['leader_id'] = $request->post('leader_id', 0);
        }

        // 开通购买会员特价权益卡
        if (file_exists(MOBILE_DRP)) {
            $total['order_membership_card_id'] = $request->input('order_membership_card_id', 0);
            $total['membership_card_discount_price'] = $request->input('membership_card_discount_price', 0);
        }

        $data = $this->flowMobileService->done($uid, $total);

        if (!is_array($data) && $data === 'order_failure') {
            return $this->setErrorCode(502)->failed(lang('order.order_failure'));
        }

        return $this->succeed($data);
    }

    /**
     * 收银台支付
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function paycheck(Request $request)
    {
        $this->validate($request, [
            'order_sn' => 'required'
        ]);

        $order_sn = $request->input('order_sn', '');
        $store_id = $request->input('store_id', 0);

        $uid = $this->authorization();

        if (empty($uid)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $data = $this->flowMobileService->PayCheck($uid, $order_sn, $store_id);

        return $this->succeed($data);
    }

    /**
     * 使用余额支付
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function balance(Request $request)
    {
        $this->validate($request, [
            'order_sn' => 'required'
        ]);

        $order_sn = $request->input('order_sn', '');
        $store_id = $request->input('store_id', 0);

        $uid = $this->authorization();

        if (empty($uid)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $data = $this->flowMobileService->Balance($uid, $order_sn);

        return $this->succeed($data);
    }

    /**
     * 再次购买
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function buyagain(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer'
        ]);

        $order_id = $request->input('id', 0);

        $uid = $this->authorization();

        if (empty($uid)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $data = $this->flowMobileService->BuyAgain($uid, $order_id);

        return $this->succeed($data);
    }
}
