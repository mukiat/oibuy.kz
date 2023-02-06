<?php

namespace App\Modules\Stores\Services;

use App\Models\Article;
use App\Models\OrderInfo;
use App\Modules\Stores\Exceptions\StoreOrderException;
use App\Modules\Stores\Repositories\StoreOrderRepository;
use App\Modules\Stores\Repositories\StoreUserRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Order\OrderCommonService;

/**
 * 门店管理
 * Class StoreManageService
 * @package App\Modules\Stores\Services
 */
class StoreManageService
{
    protected $dscRepository;
    protected $storeUserRepository;
    protected $storeOrderRepository;

    public function __construct(
        DscRepository $dscRepository,
        StoreUserRepository $storeUserRepository,
        StoreOrderRepository $storeOrderRepository
    )
    {
        $this->dscRepository = $dscRepository;
        $this->storeUserRepository = $storeUserRepository;
        $this->storeOrderRepository = $storeOrderRepository;
    }

    /**
     * 登录查询门店会员
     *
     * @param string $username
     * @return array|mixed
     */
    public function storeUser($username = '')
    {
        $condition = ['field' => 'stores_user', 'value' => $username];
        if (CommonRepository::getMatchEmail($username)) {
            $condition['field'] = 'email';
        } elseif (CommonRepository::getMatchPhone($username)) {
            $condition['field'] = 'tel';
        }

        return $this->storeUserRepository->storeUser($username, $condition);
    }

    /**
     * 更新门店会员最后登录时间与IP
     *
     * @param int $store_user_id
     * @return bool
     */
    public function updateStoreUser($store_user_id = 0)
    {
        $data = [
            'last_login' => TimeRepository::getGmTime(),
            'last_ip' => $this->dscRepository->dscIp()
        ];

        return $this->storeUserRepository->update($store_user_id, $data);
    }

    /**
     * 个人中心信息
     *
     * @param int $store_user_id
     * @param array $columns
     * @return array
     */
    public function storeUserInfo($store_user_id = 0, $columns = [])
    {
        $info = $this->storeUserRepository->info($store_user_id, $columns);

        if (!empty($info)) {
            $info['store_user_img'] = $this->dscRepository->getImagePath($info['store_user_img']);
            $info['last_login'] = TimeRepository::getLocalDate(config('shop.time_format'), $info['last_login']);
        }

        return $info;
    }

    /**
     * 获取用户密码
     * @param int $store_user_id
     * @return array
     */
    public function storeUserPass($store_user_id = 0)
    {
        return $this->storeUserRepository->info($store_user_id, ['stores_pwd', 'ec_salt']);
    }

    /**
     * 获得门店隐私协议内容
     *
     * @return array
     */
    public function privacyAgreement()
    {
        /* 获得文章的信息 */
        $row = Article::where('cat_id', '-3')->first();

        $row = $row ? $row->toArray() : [];

        if ($row) {
            if (!empty($row['content'])) {
                // 过滤样式 手机自适应
                $row['content'] = $this->dscRepository->contentStyleReplace($row['content']);
                // 显示文章详情图片 （本地或OSS）
                $row['content'] = $this->dscRepository->getContentImgReplace($row['content']);
            }
        }

        return $row;
    }

    /**
     * 更新
     *
     * @param int $store_user_id
     * @param array $data
     * @return bool
     */
    public function updateUserInfo($store_user_id = 0, $data = [])
    {
        return $this->storeUserRepository->update($store_user_id, $data);
    }

    /**
     * 检测手机号是否被其他门店会员绑定
     *
     * @param int $store_user_id
     * @param string $mobile
     * @return mixed
     */
    public function checkUserMobile($store_user_id = 0, $mobile = '')
    {
        return $this->storeUserRepository->checkUserMobile($store_user_id, $mobile);
    }

    /**
     * 门店核销订单数量
     *
     * @param int $store_user_id
     * @return array
     */
    public function storeOrderStatistics($store_user_id = 0)
    {
        if (empty($store_user_id)) {
            return [];
        }

        $info = [];
        $user = $this->storeUserRepository->info($store_user_id, ['store_id', 'store_user_img']);
        if (!empty($user)) {
            $store_id = $user['store_id'] ?? 0;

            $info['store_id'] = $store_id;
            $info['store_user_img'] = $this->dscRepository->getImagePath($user['store_user_img']);

            // 门店核销订单数量
            $cache_haved = 'after_verify' . $store_id . 'haved';
            $info['haved_store_order'] = cache()->remember($cache_haved, config('shop.cache_time'), function () use ($store_id) {
                return $this->storeOrderRepository->takeOrderStatistics($store_id, 'haved');
            });

            $cache_today = 'after_verify' . $store_id . 'today';
            $info['today_store_order'] = cache()->remember($cache_today, config('shop.cache_time'), function () use ($store_id) {
                return $this->storeOrderRepository->takeOrderStatistics($store_id, 'today');
            });

            $cache_wait = 'after_verify' . $store_id . 'wait';
            $info['wait_store_order'] = cache()->remember($cache_wait, config('shop.cache_time'), function () use ($store_id) {
                return $this->storeOrderRepository->takeOrderStatistics($store_id, 'wait');
            });
        }

        return $info;
    }

    /**
     * 查询核销订单信息
     *
     * @param int $store_user_id
     * @param string $pick_code
     * @return array
     */
    public function searchStoreOrder($store_user_id = 0, $pick_code = '')
    {
        $user = $this->storeUserRepository->info($store_user_id, ['store_id']);
        $store_id = $user['store_id'] ?? 0;
        if (empty($store_id)) {
            return [];
        }

        return $this->storeOrderRepository->searchStoreOrder($store_id, $pick_code, ['store_id', 'order_id', 'take_time', 'pick_code']);
    }

    /**
     * 核销提交
     *
     * @param int $store_id
     * @param int $order_id
     * @param string $pick_code
     * @return bool
     * @throws StoreOrderException
     */
    public function takeSubmit($store_id = 0, $order_id = 0, $pick_code = '')
    {
        if (empty($pick_code)) {
            throw new StoreOrderException(trans('stores::order.04_stores_pick_goods'), 422);
        }

        $store_order_info = $this->storeOrderRepository->storeOrderInfo($store_id, $order_id, ['store_id', 'order_id', 'pick_code']);
        $order_info = $store_order_info['order_info'] ?? [];
        if (empty($store_order_info) || empty($order_info)) {
            throw new StoreOrderException(trans('stores::order.store_order_empty'), 422);
        }

        // 订单未支付且支付方式不为银行汇款或货到付款
        $pay_code = $this->storeOrderRepository->payment_code($order_info['pay_id']);
        if ($order_info['pay_status'] != PS_PAYED && !in_array($pay_code, ['bank', 'cod'])) {
            throw new StoreOrderException(trans('stores::order.01_stores_pick_goods'), 422);
        }

        // 输入的提货码 是否与门店订单中的提货码 相同
        if (!empty($store_order_info['pick_code']) && $store_order_info['pick_code'] == $pick_code) {

            if (isset($order_info['shipping_status']) && $order_info['shipping_status'] == SS_RECEIVED) {
                // 已收货 已提货
                throw new StoreOrderException(trans('stores::order.store_order_picked_up'), 422);
            }

            // 修改 订单已发货 更改发货时间 收货时间
            $now = TimeRepository::getGmTime();
            $data = [
                'order_status' => OS_SPLITED,
                'pay_status' => PS_PAYED,
                'shipping_status' => SS_RECEIVED,
                'shipping_time' => $now,
                'confirm_take_time' => $now,
            ];
            $result = OrderInfo::where('order_id', $order_id)->update($data);

            if ($result) {
                /* 更新商品销量 */
                get_goods_sale($order_id, $order_info);

                /* 如果使用库存，且发货时减库存，则修改库存 */
                if (config('shop.use_storage') == '1' && config('shop.stock_dec_time') == SDT_SHIP) {
                    load_helper('order');
                    change_order_goods_storage($order_id, true, SDT_PAID, 2, 0, $store_id);
                }

                /* 记录日志 - 收货确认（核销）*/
                order_action($order_info['order_sn'], $order_info['order_status'], SS_RECEIVED, $order_info['pay_status'], trans('stores::order.pick_up_status_1'), trans('stores::order.store_manage'), 0, $now);

                app(OrderCommonService::class)->getUserOrderNumServer($order_info['user_id']);

                // 清除核销订单统计缓存
                $cache_haved = 'after_verify' . $store_id . 'haved';
                $cache_today = 'after_verify' . $store_id . 'today';
                $cache_wait = 'after_verify' . $store_id . 'wait';

                cache()->forget($cache_haved);
                cache()->forget($cache_today);
                cache()->forget($cache_wait);

                return true;
            }

            throw new StoreOrderException(trans('stores::common.modify_failure'), 422);
        } else {
            throw new StoreOrderException(trans('stores::order.03_stores_pick_goods'), 422);
        }
    }

    /**
     * 核销订单列表
     *
     * @param int $store_id
     * @param string $type
     * @param array $offset
     * @return array
     */
    public function storeOrderList($store_id = 0, $type = '', $offset = [])
    {
        $result = $this->storeOrderRepository->storeOrderList($store_id, $type, $offset);

        $list = [];
        if (!empty($result)) {
            foreach ($result as $key => $value) {

                $list[$key]['order_sn'] = $value['order_sn'] ?? '';
                $list[$key]['confirm_take_time'] = $value['confirm_take_time'] ?? '';
                $list[$key]['order_id'] = $value['order_id'] ?? 0;

                // 门店订单信息
                $list[$key]['store_id'] = $value['store_id'] ?? 0;
                $list[$key]['take_time'] = $value['take_time'] ?? ''; // 自提时间

                // 提货状态
                $list[$key]['pick_up_status'] = !empty($value['shipping_status']) && $value['shipping_status'] == SS_RECEIVED ? 1 : 0;
                $list[$key]['pick_up_status_format'] = !empty($list[$key]['pick_up_status']) && $list[$key]['pick_up_status'] == 1 ? trans('stores::order.pick_up_status_1') : trans('stores::order.pick_up_status_0');

                $list[$key]['confirm_take_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $list[$key]['confirm_take_time']);
            }
        }

        return $list;
    }

    /**
     * 核销订单详情
     *
     * @param int $store_id
     * @param int $order_id
     * @return array
     */
    public function storeOrderDetail($store_id = 0, $order_id = 0)
    {
        return $this->storeOrderRepository->storeOrderDetail($store_id, $order_id, ['store_id', 'order_id', 'take_time', 'pick_code']);
    }


}