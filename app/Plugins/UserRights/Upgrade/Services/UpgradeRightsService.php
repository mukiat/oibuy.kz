<?php

namespace App\Plugins\UserRights\Upgrade\Services;

use App\Exceptions\HttpException;
use App\Modules\Drp\Repositories\Distribute\AccountLogRepository;
use App\Repositories\Common\DscRepository;
use App\Services\UserRights\UserRightsService;

class UpgradeRightsService
{
    protected $dscRepository;
    protected $userRightsService;

    public function __construct(
        DscRepository $dscRepository,
        UserRightsService $userRightsService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->userRightsService = $userRightsService;
    }

    /**
     * 升级有礼权益 赠送积分
     *
     * @param string $code
     * @param array $drp_shop
     * @return bool
     * @throws HttpException
     */
    public function sendIntegral(string $code = 'upgrade', array $drp_shop = [])
    {
        if (empty($drp_shop) || empty($drp_shop['user_id'])) {
            throw new HttpException('parameters required', 1001);
        }

        $user_id = $drp_shop['user_id'] ?? 0;

        // 升级有礼权益
        $userRights = $this->userRightsService->userRightsInfo($code);

        if (!empty($userRights) && isset($userRights['enable']) && isset($userRights['install']) && $userRights['enable'] == 1 && $userRights['install'] == 1) {
            $rights_configure = $userRights['rights_configure'] ?? [];
            if (empty($rights_configure)) {
                throw new HttpException(lang('user_rights.rights_no_configure'), 1005);
            }

            // 当前分销商绑定的会员权益卡信息
            $cardRights = $this->userRightsService->membershipCardInfoByUserId($user_id, $userRights['id']);
            if (empty($cardRights)) {
                throw new HttpException(lang('user_rights.card_nobind_rights'), 1006);
            }

            // 获取会员权益卡 升级有礼权益配置
            $user_rights_configure = $cardRights['0']['rights_configure'] ?? [];
            $configure = collect($user_rights_configure)->firstWhere('name', 'send_integral');
            $send_integral = (int)abs($configure['value'] ?? 0); // 赠送消费积分值

            // 赠送消费积分
            $user_note = trans('drp::drp.user_right_upgrade_notice');
            AccountLogRepository::log_account_change($user_id, 0, 0, 0, $send_integral, $user_note);

            return true;
        }

        throw new HttpException(lang('user_rights.rights_no_install'), 1002);
    }

}
