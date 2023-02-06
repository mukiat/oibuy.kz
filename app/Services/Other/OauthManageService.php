<?php

namespace App\Services\Other;

use App\Models\TouchAuth;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\StrRepository;

/**
 * 授权登录后台管理
 * Class OauthManageService
 * @package App\Services\Other
 */
class OauthManageService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    ) {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 授权登录列表
     * @return array
     */
    public function oauthList()
    {
        $modules = $this->dscRepository->readModules(plugin_path('Connect'));

        if (empty($modules)) {
            return [];
        }

        foreach ($modules as $key => $value) {
            $type = StrRepository::studly($value['type']);

            $this->dscRepository->helpersLang($value['type'], 'Connect/' . $type . '/Languages/' . config('shop.lang'), 1);

            $modules[$key]['name'] = $GLOBALS['_LANG'][$value['type']];
            $modules[$key]['desc'] = $GLOBALS['_LANG'][$value['desc']];

            $modules[$key]['install'] = TouchAuth::where('type', $value['type'])->count();

            /* 检查原PC插件是否已经安装 */
            $install_file = plugin_path('Connect/' . $type . '/install.php');

            if (file_exists($install_file) && empty($modules[$key]['install'])) {
                // 插件已经安装了 兼容安装至数据库
                $this->transferWebOauth($value);
            }
        }

        return $modules;
    }

    /**
     * 授权登录信息
     * @param string $type
     * @return array
     */
    public function getOauthInfo($type = '')
    {
        $info = TouchAuth::where('type', $type)->first();

        $info = $info ? $info->toArray() : [];

        return $info;
    }

    /**
     * 授权登录配置
     * @param string $type
     * @return array
     */
    public function getOauthConfig($type = '')
    {
        $info = $this->getOauthInfo($type);

        if (!empty($info)) {
            $info['auth_config'] = empty($info['auth_config']) ? [] : unserialize($info['auth_config']);
        }

        return $info;
    }

    /**
     * 初始化社会化登录插件配置
     * @param array $data
     * @param array $info
     * @return array
     */
    public function transformOauthConfig($data = [], $info = [])
    {
        if (empty($data)) {
            return [];
        }

        if (!empty($info)) {
            // 编辑配置信息
            if (!empty($info['auth_config']) && is_array($info['auth_config'])) {
                /* 取出已经设置属性的code */
                $code_list = [];
                foreach ($info['auth_config'] as $key => $value) {
                    $code_list[$value['name']] = $value['value'];

                    $code_list[$value['name'] . '_range'] = $value['range'] ?? [];
                }

                $info['auth_config'] = [];

                if (isset($data['config']) && $data['config']) {
                    foreach ($data['config'] as $key => $value) {

                        //$info['config'][$key]['desc'] = $GLOBALS['_LANG'][$value['name'] . '_desc'] ?? '';
                        $info['config'][$key]['help'] = $GLOBALS['_LANG']['help'][$value['name']] ?? '';
                        $info['config'][$key]['label'] = $GLOBALS['_LANG'][$value['name']] ?? '';
                        $info['config'][$key]['name'] = $value['name'];
                        $info['config'][$key]['type'] = $value['type'];

                        // 是否加密处理
                        $info['config'][$key]['encrypt'] = $value['encrypt'] ?? false;

                        if (isset($code_list[$value['name']])) {
                            $info['config'][$key]['value'] = $code_list[$value['name']];
                        } else {
                            $info['config'][$key]['value'] = $GLOBALS['_LANG'][$value['name'] . '_value'] ?? $value['value'];
                        }

                        if (isset($code_list[$value['name'] . '_range'])) {
                            if ($info['config'][$key]['type'] == 'select' || $info['config'][$key]['type'] == 'radiobox') {
                                $info['config'][$key]['range'] = $code_list[$value['name'] . '_range'];
                            }
                        } else {
                            if ($info['config'][$key]['type'] == 'select' || $info['config'][$key]['type'] == 'radiobox') {
                                $info['config'][$key]['range'] = $GLOBALS['_LANG'][$info['config'][$key]['name'] . '_range'] ?? [];
                            }
                        }
                    }
                }
            }

            $data = array_merge($data, $info);
        } else {
            // 安装

            // 取得默认配置信息
            if (isset($data['config']) && $data['config']) {
                foreach ($data['config'] as $key => $value) {
                    $data['config'][$key]['help'] = $GLOBALS['_LANG']['help'][$value['name']] ?? '';
                    $data['config'][$key]['label'] = $GLOBALS['_LANG'][$value['name']] ?? '';
                    $data['config'][$key]['name'] = $value['name'];
                    $data['config'][$key]['type'] = $value['type'];

                    // 是否加密处理
                    $data['config'][$key]['encrypt'] = $value['encrypt'] ?? false;

                    $data['config'][$key]['value'] = $GLOBALS['_LANG'][$value['name'] . '_value'] ?? $value['value'];

                    if ($data['config'][$key]['type'] == 'select' || $data['config'][$key]['type'] == 'radiobox') {
                        $data['config'][$key]['range'] = $GLOBALS['_LANG'][$data['config'][$key]['name'] . '_range'] ?? [];
                    }
                }
            }
        }

        $data['name'] = $GLOBALS['_LANG'][$data['type']];
        $data['desc'] = $GLOBALS['_LANG'][$data['desc']];

        return $data;
    }

    /**
     * 获得原授权信息 用于编辑
     * @param string $type
     * @return array
     */
    public function getOldOauthInfo($type = '')
    {
        $info = $this->getOauthInfo($type);

        $config = [];

        if (!empty($info)) {
            $auth_config = empty($info['auth_config']) ? [] : unserialize($info['auth_config']);

            if (!empty($auth_config)) {
                foreach ($auth_config as $key => $value) {
                    $config[$key] = $value['value'];
                }
            }
        }

        return $config;
    }

    /**
     * 安装
     * @param array $data
     * @return bool
     */
    public function createOauth($data = [])
    {
        if (empty($data)) {
            return false;
        }

        // 插入配置信息
        return TouchAuth::updateOrInsert(['type' => $data['type']], $data);
    }

    /**
     * 更新
     * @param array $data
     * @return bool
     */
    public function updateOauth($data = [])
    {
        if (empty($data)) {
            return false;
        }

        return TouchAuth::where('type', $data['type'])->update($data);
    }

    /**
     * web安装信息
     * @param string $type
     * @return array
     */
    public function getOauthConfigWeb($type = '')
    {
        $config = [];

        $file_path = plugin_path('Connect/' . StrRepository::studly($type) . '/install.php');
        if (file_exists($file_path)) {
            require_once($file_path);
        }

        return $config;
    }

    /**
     * 卸载
     * @param string $type
     * @return bool
     */
    public function uninstallOauth($type = '')
    {
        if (empty($type)) {
            return false;
        }

        $res = TouchAuth::where('type', $type)->delete();

        return $res;
    }

    /**
     * 返回回调地址
     * @param string $type qq、wechat、weibo
     * @return string
     */
    public function callbackUrl($type = '')
    {
        if (empty($type)) {
            return '';
        }

        $type = $type == 'weixin' ? 'wechat' : $type;

        $result = [
            $type => [
                'web' => url('oauth'),
                'h5' => url('mobile')
            ],
            'qq' => [
                'web' => url('oauth'),
                'h5' => url('mobile/oauth/callback')
            ],
            'wechat' => [
                'web' => url('oauth'),
                'h5' => url('/')
            ],
            'weibo' => [
                'web' => url('oauth'),
                'h5' => url('mobile')
            ]
        ];

        return isset($result[$type]) ? $result[$type] : [];
    }

    /**
     * 迁移原PC安装配置
     * @param array $value
     * @return bool
     */
    public function transferWebOauth($value = [])
    {
        if (empty($value)) {
            return false;
        }

        $webOauth = $this->getOauthConfigWeb($value['type']);

        if (!empty($webOauth)) {
            $data['type'] = $value['type'];
            $data['status'] = 1;
            $data['sort'] = 10;
            // 取得配置信息
            foreach ($webOauth as $k => $item) {
                $auth_config[] = [
                    'name' => trim($k),
                    'type' => 'text',
                    'value' => trim($item)
                ];
            }
            $data['auth_config'] = empty($auth_config) ? [] : serialize($auth_config);
            // 插入配置信息
            $this->createOauth($data);

            // 删除原安装配置文件
            $install_file = plugin_path('Connect/' . StrRepository::studly($value['type'])) . '/install.php';

            if (file_exists($install_file)) {
                @unlink($install_file);
            }
            return true;
        }

        return false;
    }
}
