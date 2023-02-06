<?php

namespace App\Modules\Stores\Repositories;

use App\Models\StoreUser;
use App\Repositories\Common\BaseRepository;

/**
 * Class StoreUserRepository
 * @package App\Modules\Stores\Repositories
 */
class StoreUserRepository
{
    /**
     * 修改
     *
     * @param int $store_user_id
     * @param array $data
     * @return bool
     */
    public static function update($store_user_id = 0, $data = [])
    {
        if (empty($store_user_id) || empty($data)) {
            return false;
        }

        $data = BaseRepository::getArrayfilterTable($data, 'store_user');

        return StoreUser::where('id', $store_user_id)->update($data);
    }

    /**
     * 查询信息
     *
     * @param int $store_user_id
     * @param array $columns
     * @return array
     */
    public static function info($store_user_id = 0, $columns = [])
    {
        if (empty($store_user_id)) {
            return [];
        }

        $model = StoreUser::where('id', $store_user_id);

        if (!empty($columns)) {
            $model = $model->select($columns);
        }

        $result = $model->first();

        return $result ? $result->toArray() : [];
    }

    /**
     * 登录查询门店会员
     *
     * @param string $username
     * @param array $condition
     * @return array
     */
    public static function storeUser($username = '', $condition = [])
    {
        if (empty($username)) {
            return [];
        }

        return StoreUser::where('stores_user', $username)->orWhere($condition['field'], $condition['value'])->first();
    }

    /**
     * 检查门店管理员 权限
     *
     * @param string $priv_str
     * @param string $store_action
     * @return bool
     */
    public static function store_priv($priv_str = '', $store_action = '')
    {
        if (!empty($store_action)) {
            if ($store_action == 'all') {
                return true;
            } elseif (in_array($priv_str, explode(',', $store_action))) {
                return true;
            }
        }

        return false;
    }

    /**
     * 检测手机号是否被其他人绑定
     *
     * @param int $store_user_id
     * @param string $mobile
     * @return mixed
     */
    public function checkUserMobile($store_user_id = 0, $mobile = '')
    {
        if (empty($store_user_id) || empty($mobile)) {
            return false;
        }

        $count = StoreUser::where('id', '<>', $store_user_id)->where('tel', $mobile)->count();

        return $count;
    }
}
