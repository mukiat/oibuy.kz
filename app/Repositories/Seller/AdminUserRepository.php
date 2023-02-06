<?php

namespace App\Repositories\Seller;

use App\Models\AdminUser;

/**
 * Class SellerShopinfoRepository
 * @package App\Repositories\Seller
 */
class AdminUserRepository
{
    /**
     * 查询店铺信息
     * @param int $ru_id
     * @return bool
     */
    public function find_user_message($ru_id = 0)
    {
        if (empty($ru_id)) {
            return false;
        }
        $model = AdminUser::where('ru_id', $ru_id);
        $model = $model->with([
            'getSellerShopinfo'
        ]);
        $result = $model->first();
        return $result ? $result->toArray() : [];
    }

    /**
     * 更新店铺信息
     * @param int $ru_id
     * @param array $data
     * @return bool
     */
    public function update_user_message($ru_id = 0, $data = [])
    {
        if (empty($ru_id) || empty($data)) {
            return false;
        }
        return AdminUser::where('ru_id', $ru_id)->update($data);
    }
}
