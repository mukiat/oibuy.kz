<?php

namespace App\Modules\Admin\Services\AdminUser;

use App\Models\AdminUser;
use App\Repositories\Common\BaseRepository;

class AdminUserDataHandleService
{
    /**
     * 管理员列表
     *
     * @param array $admin_id
     * @param array $data
     * @return array
     */
    public static function getAdminUserDataList($admin_id = [], $data = [])
    {
        $admin_id = BaseRepository::getExplode($admin_id);

        if (empty($admin_id)) {
            return [];
        }

        $admin_id = $admin_id ? array_unique($admin_id) : [];

        $data = $data ? $data : '*';

        $res = AdminUser::select($data)->whereIn('user_id', $admin_id);

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['user_id']] = $row;
            }
        }

        return $arr;
    }
}