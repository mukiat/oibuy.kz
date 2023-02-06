<?php

namespace App\Plugins\UserRights\DrpStore\Services;

use App\Modules\Drp\Exceptions\DrpStoreException;
use App\Modules\Drp\Repositories\Store\DrpStoreRepository;
use App\Modules\Drp\Services\Drp\DrpConfigService;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\UserRights\UserRightsService;

/**
 * 微店权益
 * Class DrpStoreRightsService
 * @package App\Plugins\UserRights\DrpStore\Services
 */
class DrpStoreRightsService
{
    protected $dscRepository;
    protected $drpStoreRepository;
    protected $userRightsService;
    protected $drpConfigService;

    public function __construct(
        DscRepository $dscRepository,
        DrpStoreRepository $drpStoreRepository,
        UserRightsService $userRightsService,
        DrpConfigService $drpConfigService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->drpStoreRepository = $drpStoreRepository;
        $this->userRightsService = $userRightsService;
        $this->drpConfigService = $drpConfigService;
    }

    /**
     * 生成分销店铺
     * @param string $code
     * @param array $data
     * @param int $status_check
     * @return bool
     * @throws DrpStoreException
     */
    public function createDrpStore($code = 'drp_store', $data = [], $status_check = null)
    {
        if (empty($data) || empty($data['user_id']) || empty($data['drp_shop_id'])) {
            throw new DrpStoreException(lang('drp::drp_store.data_is_empty'), 1001);
        }

        $user_id = $data['user_id'] ?? 0;
        $now = TimeRepository::getGmTime();

        $count = DrpStoreRepository::checkDrpStore('user_id', $user_id);
        if ($count == true) {
            // 店铺已经申请
            throw new DrpStoreException(lang('drp::drp_store.store_is_exist'), 1002);
        }

        $check = $this->checkDrpStoreName($data['store_name']);
        if ($check == true) {
            // 店铺名称已经被使用
            throw new DrpStoreException(lang('drp::drp_store.store_name_is_exist'), 1003);
        }

        // 微店权益
        $userRights = $this->userRightsService->userRightsInfo($code);

        if (!empty($userRights) && isset($userRights['enable']) && isset($userRights['install']) && $userRights['enable'] == 1 && $userRights['install'] == 1) {
            $rights_configure = $userRights['rights_configure'] ?? [];
            if (empty($rights_configure)) {
                throw new DrpStoreException(lang('user_rights.rights_no_configure'), 1005);
            }

            // 当前分销商绑定的会员权益卡信息
            $cardRights = $this->userRightsService->membershipCardInfoByUserId($user_id, $userRights['id']);
            if (empty($cardRights)) {
                throw new DrpStoreException(lang('user_rights.card_nobind_rights'), 1006);
            }

            // 获取会员权限卡 微店权益配置 开店审核
            $user_rights_configure = $cardRights['0']['rights_configure'] ?? [];
            $configure = collect($user_rights_configure)->firstWhere('name', 'store_audit');
            $store_audit = $configure['value'] ?? 0;

            if ($store_audit == 1) {
                // 需要审核
                $data['review_status'] = 0;
                $data['open_status'] = 0;
            } else {
                // 不需要审核
                $data['review_status'] = 1;
                $data['open_status'] = 1; // 开店状态
                $data['open_time'] = $now; // 开店时间
            }

            $data['apply_time'] = $now; // 申请时间

            return DrpStoreRepository::insertGetId($data);
        } else {

            /**
             * 未安装微店权益 兼容原分销配置 微店是否自动开启
             */

            if (is_null($status_check)) {
                // 获取配置
                $drp_config = $this->drpConfigService->getDrpConfig();
                // 微店是否自动开启
                $status_check = $drp_config['register']['value'] ?? 0;
            }

            if ($status_check == 1) {
                // 微店自动开启 不需要审核
                $data['review_status'] = 1;
                $data['open_status'] = 1;
                $data['open_time'] = $now; // 开店时间
            } else {
                // 微店默认关闭 需要审核
                $data['review_status'] = 0;
                $data['open_status'] = 0; // 开店状态
            }

            $data['apply_time'] = $now; // 申请时间

            return DrpStoreRepository::insertGetId($data);
        }
    }

    /**
     * 检查店铺名称是否重复
     * @param string $store_name
     * @param int $drp_store_id
     * @return bool
     */
    protected function checkDrpStoreName($store_name = '', $drp_store_id = 0)
    {
        return DrpStoreRepository::checkDrpStore('store_name', $store_name, $drp_store_id);
    }

    /**
     * 更新微店
     *
     * @param string $code
     * @param array $drp_store
     * @param null $status_check
     * @return bool|int
     * @throws DrpStoreException
     */
    public function updateDrpStore($code = 'drp_store', $drp_store = [], $status_check = null)
    {
        $user_id = $drp_store['user_id'] ?? 0;
        $drp_shop_id = $drp_store['drp_shop_id'] ?? 0;
        if (empty($user_id) || empty($drp_shop_id)) {
            throw new DrpStoreException(trans('drp::drp_store.data_is_empty'), 1001);
        }

        // 微店权益
        $userRights = $this->userRightsService->userRightsInfo($code);

        if (!empty($userRights) && isset($userRights['enable']) && isset($userRights['install']) && $userRights['enable'] == 1 && $userRights['install'] == 1) {
            $rights_configure = $userRights['rights_configure'] ?? [];
            if (empty($rights_configure)) {
                throw new DrpStoreException(trans('user_rights.rights_no_configure'), 1005);
            }

            // 当前分销商绑定的会员权益卡信息
            $cardRights = $this->userRightsService->membershipCardInfoByUserId($user_id, $userRights['id']);
            if (empty($cardRights)) {
                throw new DrpStoreException(trans('user_rights.card_nobind_rights'), 1006);
            }

            // 获取会员权限卡 微店权益配置 开店审核
            $user_rights_configure = $cardRights['0']['rights_configure'] ?? [];
            $configure = collect($user_rights_configure)->firstWhere('name', 'store_audit');
            $store_audit = $configure['value'] ?? 0;
            if ($store_audit == 1) {
                // 需要审核
                $drp_store['review_status'] = 0;
                $drp_store['open_status'] = 0;
            } else {
                // 不需要审核
                $drp_store['review_status'] = 1;
                $drp_store['open_status'] = 1; // 开店状态
            }

            return DrpStoreRepository::updateOrInsert(['user_id' => $user_id, 'drp_shop_id' => $drp_shop_id], $drp_store);
        } else {

            /**
             * 未安装微店权益 兼容原分销配置 微店是否自动开启
             */

            if (is_null($status_check)) {
                // 获取配置
                $drp_config = $this->drpConfigService->getDrpConfig();
                // 微店是否自动开启
                $status_check = $drp_config['register']['value'] ?? 0;
            }

            if ($status_check == 1) {
                // 微店自动开启 不需要审核
                $drp_store['review_status'] = 1;
                $drp_store['open_status'] = 1;
            } else {
                // 微店默认关闭 需要审核
                $drp_store['review_status'] = 0;
                $drp_store['open_status'] = 0; // 开店状态
            }

            return DrpStoreRepository::updateOrInsert(['user_id' => $user_id, 'drp_shop_id' => $drp_shop_id], $drp_store);
        }
    }
}
