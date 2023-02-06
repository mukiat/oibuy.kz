<?php

namespace App\Services\User;

use App\Models\ConnectUser;
use App\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\TimeRepository;

/**
 * 社会化登录用户
 * Class ConnectUser
 * @package App\Services
 */
class ConnectUserService
{
    /**
     * 根据 user_id 获取用户绑定信息
     * @param int $user_id
     * @param string $connect_type
     * @return array|bool
     */
    public function getUserInfo($user_id = 0, $connect_type = '')
    {
        if (empty($user_id) || empty($connect_type)) {
            return false;
        }

        $connect_type = ($connect_type == 'weixin') ? 'wechat' : $connect_type;// 统一PC与H5参数

        $model = Users::where('user_id', $user_id)->select('user_id', 'user_name', 'nick_name', 'mobile_phone', 'user_picture');
        $result = BaseRepository::getToArrayFirst($model);

        $user_connect = [];
        if ($result) {
            $user_connect = ConnectUser::where('user_id', $result['user_id'])
                ->where('connect_code', 'sns_' . $connect_type);
            $user_connect = BaseRepository::getToArrayFirst($user_connect);
        }

        $user = [];
        if ($result && $user_connect) {
            $user = BaseRepository::getArrayMerge($result, $user_connect);
        }

        return $user;
    }

    /**
     * 查询社会化登录用户信息
     *
     * @param string $union_id
     * @param string $connect_type
     * @param array $columns
     * @return array|bool
     */
    public function getConnectUserinfo($union_id = '', $connect_type = '', $columns = [])
    {
        if (empty($union_id) || empty($connect_type)) {
            return false;
        }

        $connect_type = ($connect_type == 'weixin') ? 'wechat' : $connect_type; // 统一PC与H5参数

        $columns = !empty($columns) ? $columns : ['user_id', 'id', 'open_id'];

        $user_connect = ConnectUser::select($columns)
            ->where('open_id', $union_id)
            ->where('connect_code', 'sns_' . $connect_type);
        $user_connect = BaseRepository::getToArrayFirst($user_connect);

        $result = [];
        if ($user_connect) {
            $model = Users::where('user_id', $user_connect['user_id'])->select('user_id', 'user_name', 'nick_name', 'mobile_phone', 'user_picture');
            $result = BaseRepository::getToArrayFirst($model);
        }

        $user = [];
        if ($result && $user_connect) {
            $user = BaseRepository::getArrayMerge($result, $user_connect);
        }

        return $user;
    }

    /**
     * 查询同一微信开放平台下 绑定的授权用户
     *
     * 用于 微信公众号、小程序、社区团购小程序 unionid 相同 绑定同一会员的情况
     *
     * @param string $union_id
     * @return array|bool
     */
    public function getConnectUserWeixin($union_id = '')
    {
        if (empty($union_id)) {
            return false;
        }

        $user_connect = ConnectUser::select('user_id', 'id', 'open_id')
            ->where('open_id', $union_id)
            ->where(function ($query) {
                $query->where('connect_code', 'sns_wechat')->orWhere('connect_code', 'like', 'sns_wxapp%');
            })->orderBy('id', 'DESC');
        $user_connect = BaseRepository::getToArrayFirst($user_connect);

        $result = [];
        if ($user_connect) {
            $model = Users::where('user_id', $user_connect['user_id'])->select('user_id', 'user_name', 'nick_name', 'mobile_phone', 'user_picture');
            $result = BaseRepository::getToArrayFirst($model);
        }

        $user = [];
        if ($result && $user_connect) {
            $user = BaseRepository::getArrayMerge($result, $user_connect);
        }

        return $user;
    }

    /**
     * 验证手机号是否被绑定 兼容用户名为手机号
     * @param string $mobile
     * @param string $connect_type
     * @return array
     */
    public function checkMobileBind($mobile = '', $connect_type = '')
    {
        if (empty($mobile)) {
            return [];
        }

        $model = Users::select('user_id', 'user_name', 'mobile_phone')->where('user_name', $mobile)
            ->orWhere(function ($query) use ($mobile) {
                $query->where('mobile_phone', $mobile)->where('mobile_phone', '<>', '');
            });
        $user = BaseRepository::getToArrayFirst($model);

        $user_connect = [];
        if (!empty($user)) {
            if ($connect_type == 'wechat' || $connect_type == 'wxapp') {
                $model = ConnectUser::select('id', 'user_id', 'open_id')->where('user_id', $user['user_id'])->where(function ($query) {
                    $query->where('connect_code', 'sns_wechat')->orWhere('connect_code', 'like', 'sns_wxapp%');
                });
            } else {
                $model = ConnectUser::select('id', 'user_id', 'open_id')->where('user_id', $user['user_id'])->where('connect_code', 'sns_' . $connect_type);
            }

            $user_connect = BaseRepository::getToArrayFirst($model);
        }

        return $user_connect;
    }

    /**
     * 更新社会化登录用户信息
     * @param array $user
     * @param string $connect_type : qq、weibo、wechat
     * @return bool
     */
    public function updateConnectUser($user = [], $connect_type = '')
    {
        if (empty($user) || empty($connect_type)) {
            return false;
        }

        $connect_type = ($connect_type == 'weixin') ? 'wechat' : $connect_type;// 统一PC与H5参数

        $unionid = $user['unionid'] ?? '';
        $user_id = $user['user_id'] ?? 0;
        if (empty($unionid) || empty($user_id)) {
            return false;
        }

        // 组合数据
        $profile = [
            'openid' => $user['openid'],
            'nickname' => $user['nickname'],
            'sex' => $user['sex'] ?? '',
            'province' => $user['province'] ?? '',
            'city' => $user['city'] ?? '',
            'country' => $user['country'] ?? '',
            'headimgurl' => $user['headimgurl'] ?? '',
        ];
        $data = [
            'profile' => serialize($profile)
        ];

        if (config('shop.json_field') == 1) {
            $data['profile_json'] = json_encode($profile);
        }

        if (!empty($unionid)) {
            // 查询
            $connect_userinfo = $this->getConnectUserinfo($unionid, $connect_type);
            if (empty($connect_userinfo)) {
                // 新增记录
                $data['create_at'] = TimeRepository::getGmTime();
                $data['connect_code'] = 'sns_' . $connect_type;
                $data['user_id'] = $user_id;
                $data['open_id'] = $unionid;
                ConnectUser::create($data);
            } else {
                // 更新记录
                ConnectUser::where(['open_id' => $unionid, 'connect_code' => 'sns_' . $connect_type])->update($data);
            }

            return true;
        }

        return false;
    }

    /**
     * 重新绑定社会化登录用户
     *
     * @param int $user_id
     * @param array $user
     * @param string $connect_type
     * @return bool
     */
    public function rebindConnectUser($user_id = 0, $user = [], $connect_type = '')
    {
        if (empty($user_id) || empty($user) || empty($connect_type)) {
            return false;
        }

        $connect_type = ($connect_type == 'weixin') ? 'wechat' : $connect_type;// 统一PC与H5参数

        if ($connect_type == 'wechat' || $connect_type == 'wxapp') {
            $model = ConnectUser::where('user_id', $user_id)->where(function ($query) {
                $query->where('connect_code', 'sns_wechat')->orWhere('connect_code', 'like', 'sns_wxapp%');
            });
        } else {
            $model = ConnectUser::where('user_id', $user_id)->where('connect_code', 'sns_' . $connect_type);
        }

        // 更新记录
        return $model->update($user);
    }

    /**
     * 授权登录用户列表
     * @param int $user_id
     * @return bool|array
     */
    public function connectUserList($user_id = 0)
    {
        if (empty($user_id)) {
            return false;
        }

        $model = ConnectUser::where('user_id', $user_id)->where('connect_code', '<>', '');
        $result = BaseRepository::getToArrayGet($model);

        $userList = BaseRepository::getKeyPluck($result);
        $user_id = UserDataHandleService::userDataList($userList, ['user_id', 'user_name']);

        $sql = [
            'whereIn' => [
                [
                    'name' => 'user_id',
                    'value' => $user_id
                ]
            ]
        ];
        $result = BaseRepository::getArraySqlGet($result, $sql);

        return $result;
    }

    /**
     * 查询是否绑定
     * @param int $id
     * @param int $user_id
     * @return array|bool
     */
    public function connectUserById($id = 0, $user_id = 0)
    {
        if (empty($id) || empty($user_id)) {
            return false;
        }

        $model = ConnectUser::where('id', $id)
            ->where('user_id', $user_id)
            ->where('connect_code', '<>', '');

        $result = BaseRepository::getToArrayFirst($model);

        $user_connect = [];
        if ($result) {
            $user_connect = UserDataHandleService::userDataList($result['user_id'], ['user_id', 'mobile_phone']);
        }

        $connect = $user_connect[$result['user_id']] ?? [];

        if (!empty($result) && $connect) {
            $result = BaseRepository::getArrayMerge($result, $connect);
        }

        return $result;
    }

    /**
     * 删除授权登录记录
     * @param string $union_id
     * @param int $user_id
     * @return bool
     */
    public function connectUserDelete($union_id = '', $user_id = 0)
    {
        if (empty($union_id) || empty($user_id)) {
            return false;
        }

        return ConnectUser::where('open_id', $union_id)->where('user_id', $user_id)->delete();
    }

    /**
     * 查询users用户是否被其他人绑定
     * @param string $union_id
     * @param string $connect_type
     * @return int
     */
    public function checkConnectUserId($union_id = '', $connect_type = '')
    {
        if (empty($union_id) || empty($connect_type)) {
            return 0;
        }

        return ConnectUser::where('open_id', $union_id)->where('connect_code', 'sns_' . $connect_type)->value('user_id');
    }

    /**
     * 手机号或用户名是否注册
     * @param string $mobile
     * @return array
     */
    public function checkMobileRegister($mobile = '')
    {
        if (empty($mobile)) {
            return [];
        }

        $model = Users::select('user_id', 'user_name', 'mobile_phone')->where('user_name', $mobile)->orWhere('mobile_phone', $mobile);
        return BaseRepository::getToArrayFirst($model);
    }
}
