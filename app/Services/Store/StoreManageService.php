<?php

namespace App\Services\Store;

use App\Models\StoreUser;
use App\Repositories\Common\TimeRepository;

/**
 * 门店后台管理 service
 * Class StoreManageService
 * @package App\Services\Wechat
 */
class StoreManageService
{
    /**
     * 查询门店会员
     * @param string $user_name
     * @return array
     */
    public function storeUser($user_name = '')
    {
        if (empty($user_name)) {
            return [];
        }

        $row = StoreUser::where('stores_user', $user_name)->first();

        return $row ? $row->toArray() : [];
    }

    /**
     * 更新门店会员信息
     * @param int $store_user_id
     * @param array $updata
     * @return bool
     */
    public function updateStoreUser($store_user_id = 0, $updata = [])
    {
        if (empty($store_user_id) || empty($updata)) {
            return false;
        }

        $up = StoreUser::where('id', $store_user_id)->update($updata);

        return $up;
    }
}
