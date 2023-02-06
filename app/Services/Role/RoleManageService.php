<?php

namespace App\Services\Role;

use App\Models\Role;
use App\Repositories\Common\BaseRepository;
use App\Services\Merchant\MerchantCommonService;

class RoleManageService
{
    protected $merchantCommonService;

    public function __construct(
        MerchantCommonService $merchantCommonService
    ) {
        $this->merchantCommonService = $merchantCommonService;
    }

    /* 获取角色列表 */
    public function getRoleList()
    {
        $res = Role::orderBy('role_id', 'DESC');
        $list = BaseRepository::getToArrayGet($res);

        return $list;
    }
}
