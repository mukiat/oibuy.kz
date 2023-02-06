<?php

namespace App\Services\User;

use App\Models\ConnectUser;
use App\Models\Users;
use App\Models\UsersPaypwd;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\StrRepository;

/**
 * 会员账户安全
 * Class AccountSafeService
 * @package App\Services\User
 */
class AccountSafeService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 用户信息
     * @param int $user_id
     * @param array $columns 列表
     * @return array
     */
    public function users($user_id = 0, $columns = [])
    {
        $user = Users::where('user_id', $user_id);

        if (!empty($columns)) {
            $user = $user->select($columns);
        }

        $user = $user->first();

        return $user ? $user->toArray() : [];
    }

    /**
     * 是否验证邮箱
     * @param int $user_id
     * @return int
     */
    public function userValidateStatus($user_id = 0)
    {
        $user = $this->users($user_id, ['is_validated']);

        return $user['is_validated'] ?? 0;
    }

    /**
     * 是否启用支付密码
     * @param int $user_id
     * @return int
     */
    public function userPaypwdCount($user_id = 0)
    {
        $users_paypwd = UsersPaypwd::where('user_id', $user_id)->count();

        return $users_paypwd > 0 ? 1 : 0;
    }

    /**
     * 判断是否授权登录用户
     * @param int $user_id
     * @return boolean |int
     */
    public function isConnectUser($user_id = 0)
    {
        $is_connect_user = ConnectUser::where('user_id', $user_id)->count();

        return $is_connect_user > 0 ? 1 : 0;
    }

    /**
     * 返回验证类型
     * @return string
     */
    public function validateType()
    {
        $sms_signin = config('shop.sms_signin', 0);
        if ($sms_signin == 0) {
            $type = 'email';
        } else {
            $type = 'phone';
        }

        return $type;
    }

    /**
     * 查询启用支付密码
     * @param int $user_id
     * @return array
     */
    public function userPaypwd($user_id = 0)
    {
        $result = UsersPaypwd::where('user_id', $user_id)->first();

        return $result ? $result->toArray() : [];
    }

    /**
     * 添加支付密码
     * @param array $data
     * @return bool
     */
    public function addUsersPaypwd($data = [])
    {
        if (empty($data)) {
            return false;
        }

        $data = BaseRepository::getArrayfilterTable($data, 'users_paypwd');

        $count = $this->userPaypwd($data['user_id']);

        if (empty($count)) {
            return UsersPaypwd::insert($data);
        }

        return false;
    }

    /**
     * 更新
     * @param int $user_id
     * @param int $paypwd_id
     * @param array $data
     * @return bool
     */
    public function updateUsersPaypwd($user_id = 0, $paypwd_id = 0, $data = [])
    {
        if (empty($paypwd_id) || empty($data)) {
            return false;
        }

        $data = BaseRepository::getArrayfilterTable($data, 'users_paypwd');

        return UsersPaypwd::where('user_id', $user_id)->where('paypwd_id', $paypwd_id)->update($data);
    }

    /**
     * 查询支付密码
     * @param int $paypwd_id
     * @return array
     */
    public function getUsersPaypwd($paypwd_id = 0)
    {
        $result = UsersPaypwd::where('paypwd_id', $paypwd_id)->first();

        return $result ? $result->toArray() : [];
    }

    /**
     * 会员绑定手机号
     *
     * @param int $user_id
     * @param string $mobile
     * @return bool
     */
    public function updateUserMobile($user_id = 0, $mobile = '')
    {
        if (empty($user_id) || empty($mobile)) {
            return false;
        }

        $model = Users::where('user_id', $user_id)->first();

        // 原用户名与手机号相同时 修改手机号的同时修改用户名
        if ($model && $model->user_name == $model->mobile_phone) {
            $data['user_name'] = StrRepository::generate_username();
        }

        $data['mobile_phone'] = $mobile;

        return $model->update($data);
    }

    /**
     * 检测手机号是否被注册
     * @param int $user_id
     * @param string $mobile
     * @return mixed
     */
    public function checkUserMobile($user_id = 0, $mobile = '')
    {
        if (empty($user_id) || empty($mobile)) {
            return false;
        }

        $count = Users::where('user_id', '<>', $user_id)->where('mobile_phone', $mobile)->count();

        return $count;
    }
}
