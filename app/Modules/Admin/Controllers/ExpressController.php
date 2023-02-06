<?php

namespace App\Modules\Admin\Controllers;


use App\Repositories\Common\DscRepository;
use App\Services\Common\PluginManageService;
use App\Services\Express\ExpressService;
use Illuminate\Http\Request;
use App\Repositories\Common\StrRepository;

/**
 * 快递跟踪插件管理
 * Class ExpressController
 * @package App\Modules\Admin\Controllers
 */
class ExpressController extends BaseController
{
    protected $dscRepository;
    protected $expressManageService;

    public function __construct(
        DscRepository $dscRepository,
        ExpressService $expressManageService
    ) {
        $this->dscRepository = $dscRepository;
        $this->expressManageService = $expressManageService;
    }

    protected function initialize()
    {
        parent::initialize();
    }

    /**
     * 全部列表
     * @param Request $request
     * @param PluginManageService $pluginManageService
     * @return mixed
     */
    public function index(Request $request, PluginManageService $pluginManageService)
    {
        // 数据库
        $list = $this->expressManageService->expressList();
        $code_arr = [];
        if (!empty($list)) {
            foreach ($list as $value) {
                $code_arr[$value['code']] = $value;
            }
        }

        // 所有列表
        $new_plugins = [];
        $plugins = $pluginManageService->readPlugins('Express');

        if (!empty($plugins)) {
            foreach ($plugins as $k => $v) {

                $plugins[$k]['name'] = $GLOBALS['_LANG'][$v['code']];
                $plugins[$k]['description'] = $GLOBALS['_LANG'][$v['description']];

                // 数据库中存在，用数据库的数据
                if (isset($code_arr[$v['code']])) {
                    $new_plugins[] = array_merge($plugins[$k], $code_arr[$v['code']]);
                } else {
                    $new_plugins[] = $plugins[$k];
                }
            }
        }


        if (!empty($new_plugins)) {
            // 按sort排序
            $collection = collect($new_plugins)->sortBy('sort')->values();

            $new_plugins = $collection->toArray();
        }

        if (empty($list)) {
            // 同步旧快递跟踪配置
            return $this->syncOldExpressConfig($request, $pluginManageService);
        }

        $this->assign('plugins', $new_plugins);
        return $this->display();
    }

    /**
     * 安装、编辑
     *
     * @param Request $request
     * @param PluginManageService $pluginManageService
     * @return mixed
     * @throws \Exception
     */
    public function edit(Request $request, PluginManageService $pluginManageService)
    {
        if ($request->isMethod('POST')) {
            $code = $request->input('code', '');
            $handler = $request->input('handler', ''); // edit\install

            $data = $request->input('data', []); // 基本配置

            if (empty($code) || empty($handler)) {
                return $this->message(lang('admin/express.code_empty'), route('admin.express.index'));
            }

            // 保存配置
            $cfg_value = $request->input('cfg_value', []);
            $cfg_label = $request->input('cfg_label', []);
            $cfg_name = $request->input('cfg_name', []);
            $cfg_type = $request->input('cfg_type', []);
            $cfg_range = $request->input('cfg_range', []);

            $express_configure = [];

            if (!empty($cfg_value) && is_array($cfg_value)) {
                for ($i = 0; $i < count($cfg_value); $i++) {

                    // 判断 cfg_value[1] 是否修改,若没修改取原值存入config
                    if (stripos($cfg_value[$i], '*') !== false) {
                        $old_sms_config = $this->expressManageService->getExpressConfigure($code);
                        $cfg_value[$i] = $old_sms_config[$i]['value'];
                    }

                    $express_configure[] = [
                        'name' => trim($cfg_name[$i]),
                        'type' => trim($cfg_type[$i]),
                        'value' => trim($cfg_value[$i]),
                        'label' => $cfg_label[$i] ?? '',
                    ];
                }
            }

            $data['express_configure'] = empty($express_configure) ? '' : \Opis\Closure\serialize($express_configure);
            $data['enable'] = 1;

            // 设置默认
            if ($data['default'] == 1) {
                $this->expressManageService->setDefault($code);
            }

            if (!empty($handler) && $handler == 'edit') {
                // 编辑
                $res = $this->expressManageService->update($code, $data);
                if ($res) {
                    // 清除快递配置缓存
                    cache()->forget('express_config');
                    return $this->message(lang('admin/common.edit') . lang('admin/common.success'), route('admin.express.index'));
                }
            } elseif (!empty($handler) && $handler == 'install') {
                // 安装
                $res = $this->expressManageService->create($code, $data);
                if ($res) {
                    return $this->message(lang('admin/common.install') . lang('admin/common.success'), route('admin.express.index'));
                }
            }

            return $this->message(lang('admin/common.handler') . lang('admin/common.fail'), route('admin.express.index'));
        }

        // 操作
        $code = $request->input('code', '');
        $handler = $request->input('handler', 'install'); // edit\install

        if (empty($code)) {
            return $this->message(lang('admin/express.code_empty'), route('admin.express.index'));
        }

        // 获取配置信息
        $info = [];
        if (!empty($handler) && $handler == 'edit') {
            // 获取配置信息
            $info = $this->expressManageService->expressInfo($code);
        }

        // 插件实例
        $obj = $pluginManageService->pluginInstance($code, 'Express');

        if (!is_null($obj)) {
            // 插件配置
            $cfg = $pluginManageService->getPluginConfig($code, 'Express', $info);

            if (isset($cfg['express_configure']) && !empty($cfg['express_configure'])) {
                // 循环配置插件中所有属性
                foreach ($cfg['express_configure'] as $k => $val) {
                    // 配置项 encrypt 以*加密处理
                    if (isset($val['encrypt']) && $val['encrypt'] == true) {
                        $cfg['express_configure'][$k]['value'] = StrRepository::stringToStar($val['value']);
                    }
                }
            }

            $obj->setPluginInfo($cfg);

            return $obj->install();
        }
    }

    /**
     * 卸载插件
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function uninstall(Request $request)
    {
        $code = $request->input('code', '');

        if (empty($code)) {
            return response()->json(['error' => 1, 'msg' => lang('admin/sms.code_empty')]);
        }

        // 卸载
        $res = [];//$this->smsManageService->uninstall($code);
        if ($res) {
            return response()->json(['error' => 0, 'msg' => lang('admin/common.uninstall') . lang('admin/common.success')]);
        }

        return response()->json(['error' => 1, 'msg' => lang('admin/common.uninstall') . lang('admin/common.fail')]);
    }

    /**
     * 修改 ajx异步
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        if ($request->isMethod('POST')) {

            $code = $request->input('code', '');

            // 是否显示
            $default = $request->input('default');
            if (isset($default)) {
                $data = [
                    'default' => $default
                ];
                // 设置默认
                if ($data['default'] == 1) {
                    $this->expressManageService->setDefault($code);
                }
                // 编辑
                $res = $this->expressManageService->update($code, $data);
                if ($res) {
                    // 清除快递配置缓存
                    cache()->forget('express_config');
                    return response()->json(['error' => 0, 'msg' => lang('admin/common.edit') . lang('admin/common.success')]);
                }

                return response()->json(['error' => 0, 'msg' => trans('admin/common.success')]);
            }
        }
    }

    /**
     * 同步兼容旧快递跟踪配置
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function syncOldExpressConfig()
    {
         $this->expressManageService->oldExpressConfig();

        return redirect()->route('admin.express.index');
    }
}
