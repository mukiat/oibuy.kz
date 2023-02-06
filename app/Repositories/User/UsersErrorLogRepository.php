<?php

namespace App\Repositories\User;

use App\Models\UsersErrorLog;

class UsersErrorLogRepository
{
    /**
     * 添加
     * @param array $data
     * @return bool
     */
    public function insertGetId($data = [])
    {
        if (empty($data)) {
            return false;
        }

        return UsersErrorLog::insertGetId($data);
    }

    /**
     * 删除
     * @param int $log_id
     * @return bool
     */
    public function delete($log_id = 0)
    {
        if (empty($log_id)) {
            return false;
        }

        return UsersErrorLog::where('log_id', $log_id)->delete();
    }

    /**
     * 更新
     * @param string $user_name
     * @param int $time
     * @return bool
     */
    public function updateExpired($user_name = '', $time = 0)
    {
        if (empty($user_name) || empty($time)) {
            return false;
        }

        $data = [
            'expired' => 1
        ];
        return UsersErrorLog::where('user_name', $user_name)->where('create_time', '<', $time)->update($data);
    }

    /**
     * 查询数量
     * @param string $user_name
     * @param int $expired_time
     * @return mixed
     */
    public function count($user_name = '', $expired_time = 0)
    {
        if (empty($user_name) || empty($expired_time)) {
            return 0;
        }

        // 设置的过期时间内 默认5分钟
        $count = UsersErrorLog::query()->where('user_name', $user_name)
            ->where('create_time', '>', $expired_time)
            ->where('expired', '<>', 1)
            ->count();

        return $count;
    }
}
