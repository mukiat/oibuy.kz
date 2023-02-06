<?php

namespace App\Services\User;

use App\Models\TouchAuth;

class UserOauthService
{
    /**
     * 查询社会化登录插件
     *
     * @param int $status
     * @param $columns
     * @return array
     */
    public function getOauthList($status = 0, $columns = [])
    {
        // 显示社会化登录插件
        $model = TouchAuth::query();

        if ($status == 1) {
            // 已安装
            $model = $model->where('status', $status);
        }

        if (!empty($columns)) {
            $model = $model->select($columns);
        }

        $model = $model->orderBy('sort', 'ASC')
            ->orderBy('id', 'DESC')->get();

        $oauth_list = $model ? $model->toArray() : [];

        return $oauth_list;
    }

    /**
     * PC端授权登录列表
     * @param int $status
     * @param array $columns
     * @return array
     */
    public function webOauthList($status = 0, $columns = [])
    {
        $list = $this->getOauthList($status, $columns);

        if (!empty($list)) {
            foreach ($list as $key => $vo) {
                $list[$key]['install'] = $vo['status'];

                if ($vo['type'] == 'wechat') {
                    unset($list[$key]);
                }
            }
            // 重新索引
            $list = collect($list)->values()->all();
        }

        return $list;
    }

    /**
     * h5端授权登录列表
     * @param int $status
     * @param array $columns
     * @return array
     */
    public function mobileOauthList($status = 0, $columns = [])
    {
        $list = $this->getOauthList($status, $columns);

        if (!empty($list)) {
            foreach ($list as $key => $vo) {
                if ($vo['type'] == 'wechat' && !is_wechat_browser()) {
                    unset($list[$key]);
                }

                if ($vo['type'] == 'weixin') {
                    unset($list[$key]);
                }
            }
            // 重新索引
            $list = collect($list)->values()->all();
        }

        return $list;
    }

    /**
     * 授权登录插件是否安装
     * @param string $type
     * @return bool
     */
    public function oauthStatus($type = '')
    {
        if (empty($type)) {
            return false;
        }

        // 是否安装
        $count = TouchAuth::where('type', $type)->where('status', 1)->count();

        return $count > 0 ? true : false;
    }

    /**
     * 已安装授权登录配置
     * @param string $type
     * @return array|bool
     */
    public function oauthConfig($type = '')
    {
        if (empty($type)) {
            return false;
        }

        // 授权登录配置
        $auth_config = TouchAuth::where('type', $type)
            ->where('status', 1)
            ->value('auth_config');

        $auth_config = empty($auth_config) ? [] : unserialize($auth_config);

        $config = [];
        if ($auth_config) {
            foreach ($auth_config as $item) {
                $config[$item['name']] = $item['value'];
            }
        }

        return $config;
    }
}
