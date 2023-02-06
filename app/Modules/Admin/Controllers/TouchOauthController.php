<?php

namespace App\Modules\Admin\Controllers;

use App\Repositories\Common\StrRepository;
use App\Services\Other\OauthManageService;

/**
 * 社会化授权登录管理程序
 */
class TouchOauthController extends BaseController
{
    protected $oauthManageService;

    public function __construct(
        OauthManageService $oauthManageService
    )
    {
        $this->oauthManageService = $oauthManageService;
    }

    protected function initialize()
    {
        parent::initialize();

        L(lang('admin/touch_oauth'));
        $this->assign('lang', L());
    }

    /**
     * 授权列表
     */
    public function index()
    {
        $modules = $this->oauthManageService->oauthList();

        $this->assign('page_title', lang('admin/touch_oauth.plug_list'));
        $this->assign('modules', $modules);
        return $this->display();
    }

    /**
     * 安装授权登录
     */
    public function install()
    {
        if (request()->isMethod('POST')) {
            $data['type'] = request()->input('type', '');
            $data['status'] = request()->input('status', '');
            $data['sort'] = request()->input('sort', '');
            $cfg_value = request()->input('cfg_value', '');
            $cfg_name = request()->input('cfg_name', '');
            $cfg_type = request()->input('cfg_type', '');
            $cfg_label = request()->input('cfg_label', '');

            // 取得配置信息
            $auth_config = [];
            if (isset($cfg_value) && is_array($cfg_value)) {
                for ($i = 0; $i < count($cfg_value); $i++) {
                    $auth_config[] = [
                        'name' => trim($cfg_name[$i]),
                        'type' => trim($cfg_type[$i]),
                        'value' => trim($cfg_value[$i])
                    ];
                }
            }
            $data['auth_config'] = empty($auth_config) ? [] : serialize($auth_config);

            // 插入配置信息
            $res = $this->oauthManageService->createOauth($data);

            if ($res == true) {
                return $this->message(lang('admin/touch_oauth.msg_ins_success'), route('admin/touch_oauth/index'));
            }

            return $this->message(lang('admin/touch_oauth.fail'), route('admin/touch_oauth/index'));
        }

        // 安装
        $type = request()->input('type', '');

        $oauth_config = $this->oauthManageService->getOauthConfig($type);
        // 安装过跳转到列表页面
        if (!empty($oauth_config)) {
            return redirect()->route('admin/touch_oauth/index');
        }

        $connect = StrRepository::studly($type);
        $file = plugin_path('Connect/' . $connect . '/' . $connect . '.php');

        $info = [];
        if (file_exists($file)) {
            $data = include_once(plugin_path('Connect/' . $connect . '/config.php'));

            $info = $this->oauthManageService->transformOauthConfig($data);

            // 循环配置插件中所有属性
            foreach ($info['config'] as $key => $value) {
                // 配置项 encrypt 以*加密处理
                if (isset($value['encrypt']) && $value['encrypt'] == true) {
                    $info['config'][$key]['value'] = StrRepository::stringToStar($value['value']);
                } else {
                    $info['config'][$key]['value'] = $value['value'];
                }
            }
        }

        // 回调地址
        $callback = $this->oauthManageService->callbackUrl($type);

        $callback_notice = "<li><em class=\"red\">" . lang('admin/touch_oauth.callback_web_help') . $callback['web'] . " </em></li>
        <li><em class=\"red\">" . lang('admin/touch_oauth.callback_h5_help') . $callback['h5'] . "</em></li>";
        $this->assign('callback_notice', $callback_notice);

        $this->assign('info', $info);

        $this->assign('page_title', lang('admin/touch_oauth.plug_install'));
        $this->assign('ur_here', lang('admin/touch_oauth.plug_install'));
        return $this->display();
    }

    /**
     * 编辑授权
     */
    public function edit()
    {
        // 提交
        if (request()->isMethod('POST')) {
            $data['type'] = request()->input('type', '');
            $data['status'] = request()->input('status', '');
            $data['sort'] = request()->input('sort', '');
            $cfg_value = request()->input('cfg_value', '');
            $cfg_name = request()->input('cfg_name', '');
            $cfg_type = request()->input('cfg_type', '');
            $cfg_label = request()->input('cfg_label', '');

            // 取得配置信息
            $auth_config = [];
            if (isset($cfg_value) && is_array($cfg_value)) {
                for ($i = 0; $i < count($cfg_value); $i++) {
                    // 判断 cfg_value[1]是否修改,若没修改取原值存入config
                    if (strpos($cfg_value[$i], '*') == true) {
                        $old_oauth_config = $this->oauthManageService->getOldOauthInfo($data['type']);

                        $cfg_value[$i] = $old_oauth_config[$i];
                    }

                    $auth_config[] = [
                        'name' => $cfg_name[$i],
                        'type' => $cfg_type[$i],
                        'value' => $cfg_value[$i]
                    ];
                }
            }
            $data['auth_config'] = empty($auth_config) ? [] : serialize($auth_config);

            // 更新配置信息
            $res = $this->oauthManageService->updateOauth($data);

            return $this->message(lang('admin/touch_oauth.edit_success'), route('admin/touch_oauth/index'));
        }

        // 编辑
        $type = request()->input('type', '');

        $oauth_config = $this->oauthManageService->getOauthConfig($type);
        // 没有安装过跳转到列表页面
        if (empty($oauth_config)) {
            return redirect()->route('admin/touch_oauth/index');
        }

        $connect = StrRepository::studly($type);
        $file = plugin_path('Connect/' . $connect . '/' . $connect . '.php');

        $info = [];
        if (file_exists($file)) {
            $data = include_once(plugin_path('Connect/' . $connect . '/config.php'));

            $info = $this->oauthManageService->transformOauthConfig($data, $oauth_config);

            // 循环配置插件中所有属性
            foreach ($info['config'] as $key => $value) {
                // 配置项 encrypt 以*加密处理
                if (isset($value['encrypt']) && $value['encrypt'] == true) {
                    $info['config'][$key]['value'] = StrRepository::stringToStar($value['value']);
                } else {
                    $info['config'][$key]['value'] = $value['value'];
                }
            }
        }

        // 回调地址
        $callback = $this->oauthManageService->callbackUrl($type);

        $callback_notice = "<li><em class=\"red\">" . lang('admin/touch_oauth.callback_web_help') . $callback['web'] . " </em></li>
        <li><em class=\"red\">" . lang('admin/touch_oauth.callback_h5_help') . $callback['h5'] . "</em></li>";
        $this->assign('callback_notice', $callback_notice);

        $this->assign('info', $info);

        $this->assign('page_title', lang('admin/touch_oauth.edit_plug'));
        $this->assign('ur_here', lang('admin/touch_oauth.edit_plug'));
        return $this->display();
    }

    /**
     * 卸载授权
     */
    public function uninstall()
    {
        $type = request()->input('type');

        $res = $this->oauthManageService->uninstallOauth($type);

        if ($res == true) {
            return $this->message(lang('admin/touch_oauth.upload_success'), route('admin/touch_oauth/index'));
        }

        return $this->message(lang('admin/touch_oauth.fail'), route('admin/touch_oauth/index'));
    }
}
