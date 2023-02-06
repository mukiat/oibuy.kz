<?php

namespace App\Services\UserRights;

use App\Models\UserMembershipRights;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;

class UserRightsCommonService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }


    /**
     * 查询信息
     * @param string $code
     * @return array|mixed
     */
    public function userRightsInfo($code = '')
    {
        if (empty($code)) {
            return false;
        }

        $code = addslashes($code);

        $info = UserMembershipRights::where('code', $code);
        $info = BaseRepository::getToArrayFirst($info);

        if (!empty($info)) {
            $info['install'] = 1;
            $info['rights_configure'] = empty($info['rights_configure']) ? '' : unserialize($info['rights_configure']);
            $info['icon'] = empty($info['icon']) ? '' : ((stripos($info['icon'], 'assets') !== false) ? asset($info['icon']) : $this->dscRepository->getImagePath($info['icon']));
        }

        return $info;
    }
}
