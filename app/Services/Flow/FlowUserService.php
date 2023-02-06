<?php

namespace App\Services\Flow;

use App\Models\Cart;
use App\Models\OrderGoods;
use App\Models\Region;
use App\Models\UserAddress;
use App\Repositories\Common\SessionRepository;
use App\Services\User\UserAddressService;
use App\Services\Cgroup\CgroupService;

class FlowUserService
{
    protected $userAddressService;
    protected $sessionRepository;

    public function __construct(
        UserAddressService $userAddressService,
        SessionRepository $sessionRepository
    )
    {
        $this->userAddressService = $userAddressService;
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * 取得收货人信息
     *
     * @param int $user_id
     * @param int $leader_id
     * @return array|\Illuminate\Session\SessionManager|\Illuminate\Session\Store|mixed
     */
    public function getConsignee($user_id = 0, $leader_id = 0)
    {
        if (empty($user_id)) {
            $user_id = session('user_id', 0);
        }

        if (empty($user_id)) {
            return [];
        }

        if (session()->has('flow_consignee') && !empty(session('flow_consignee'))) {
            /* 如果存在session，则直接返回session中的收货人信息 */
            if (session('flow_consignee.user_id') != $user_id) {
                session([
                    'flow_consignee' => ''
                ]);

                return [];
            }


            if ($leader_id > 0) {
                // 社区驿站
                if (file_exists(MOBILE_GROUPBUY)) {
                    $post = app(CgroupService::class)->postExists();
                    if (!empty($post)) {
                        $consignee = session('flow_consignee');
                        $consignee = $post->getPostConsignee($consignee, $leader_id);
                        session(['flow_consignee' => $consignee]);
                    }
                }
            }

            $flow_consignee = session('flow_consignee');
            $address_id = $flow_consignee['address_id'] ?? 0;

            $count = UserAddress::where('user_id', $user_id)->where('address_id', $address_id)->count();

            if ($count > 0) {
                return session('flow_consignee');
            } else {
                return [];
            }
        } else {
            /* 如果不存在，则取得用户的默认收货人信息 */
            $consignee = $this->userAddressService->getUserAddressInfo(0, $user_id);

            return $consignee;
        }
    }

    /**
     * 检查收货人信息是否完整
     *
     * @param array $consignee
     * @param int $flow_type
     * @param int $user_id
     * @return bool
     * @throws \Exception
     */
    public function checkConsigneeInfo($consignee = [], $flow_type = 0, $user_id = 0)
    {
        if (empty($user_id)) {
            $user_id = session('user_id', 0);
        }

        if ($this->existRealGoods(0, $flow_type, '', $user_id)) {
            /* 如果存在实体商品 */
            $res = (isset($consignee['consignee']) && !empty($consignee['consignee'])) &&
                ((isset($consignee['tel']) && !empty($consignee['tel'])) || (isset($consignee['mobile']) && !empty($consignee['mobile'])));

            if ($res) {
                if (isset($consignee['province']) && empty($consignee['province'])) {
                    /* 没有设置省份，检查当前国家下面有没有设置省份 */
                    $pro = Region::where('region_type', 1)->where('parent_id', $consignee['country'])->count('region_id');
                    $pro = $pro ?? 0;

                    $res = empty($pro);
                } elseif (isset($consignee['city']) && empty($consignee['city'])) {
                    /* 没有设置城市，检查当前省下面有没有城市 */
                    $city = Region::where('region_type', 2)->where('parent_id', $consignee['province'])->count('region_id');
                    $city = $city ?? 0;

                    $res = empty($city);
                } elseif (isset($consignee['district']) && empty($consignee['district'])) {
                    $dist = Region::where('region_type', 3)->where('parent_id', $consignee['city'])->count('region_id');
                    $dist = $dist ?? 0;

                    $res = empty($dist);
                }
            }

            return $res;
        } else {
            /* 如果不存在实体商品 */
            return true;
        }
    }

    /**
     * 查询购物车（订单id为0）或订单中是否有实体商品
     *
     * @param int $order_id
     * @param int $flow_type 购物流程类型
     * @param string $cart_value
     * @param int $user_id
     * @return bool
     */
    public function existRealGoods($order_id = 0, $flow_type = CART_GENERAL_GOODS, $cart_value = '', $user_id = 0)
    {
        if (empty($user_id)) {
            $user_id = session('user_id', 0);
        }

        if ($order_id <= 0) {
            $res = Cart::where('is_real', 1)->where('rec_type', $flow_type);

            if ($cart_value) {
                $cart_value = !is_array($cart_value) ? explode(",", $cart_value) : $cart_value;

                $res = $res->whereIn('rec_id', $cart_value);
            }

            if ($user_id) {
                $res = $res->where('user_id', $user_id);
            } else {
                $session_id = $this->sessionRepository->realCartMacIp();

                $res = $res->where('session_id', $session_id);
            }
        } else {
            $res = OrderGoods::where('order_id', $order_id)->where('is_real', 1);
        }

        $count = $res->count();

        return $count > 0;
    }

    /**
     * 有存在虚拟和实体商品
     *
     * @param array $cart_value
     * @return int
     */
    public function getGoodsFlowType($cart_value = [])
    {
        $flow_type = intval(session('flow_type', CART_GENERAL_GOODS));

        if ($this->existRealGoods(0, $flow_type, $cart_value)) {
            $goods_flow_type = 101; //实体商品
        } else {
            $goods_flow_type = 100; //虚拟商品
        }

        return $goods_flow_type;
    }
}
