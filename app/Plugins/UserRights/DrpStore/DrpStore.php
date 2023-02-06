<?php

namespace App\Plugins\UserRights\DrpStore;

use App\Http\Controllers\PluginController;
use App\Modules\Drp\Models\DrpConfig;
use App\Plugins\UserRights\DrpStore\Services\DrpStoreRightsService;

class DrpStore extends PluginController
{
    // 插件名称
    public $plugin_name = '';
    public $code = '';

    // 配置
    protected $cfg = [];

    /**
     * service
     * @var \Illuminate\Contracts\Foundation\Application|mixed
     */
    protected $drpStoreRightsService;

    public function __construct()
    {
        parent::__construct();

        $this->drpStoreRightsService = app(DrpStoreRightsService::class);
    }

    /**
     * @param $value
     * @return $this
     */
    public function setPluginInfo($value)
    {
        $this->cfg = $value;
        return $this;
    }

    /**
     * 查询信息
     * @return array
     */
    public function getPluginInfo()
    {
        return $this->cfg;
    }

    /**
     * 安装
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function install()
    {
        $cfg = $this->getPluginInfo();

        if (isset($cfg['handler']) && $cfg['handler'] == 'install') {
            $cfg = self::defaultStoreConfig($cfg);
        }

        $this->plugin_assign('cfg', $cfg);
        $this->assign('page_title', $this->plugin_name);
        return $this->plugin_display('install');
    }

    /**
     * 插件安装 执行方法
     * @return mixed
     */
    public function actionInstall()
    {
        // $this->code
        return true;
    }

    /**
     * 插件安装 执行方法
     * @return mixed
     */
    public function actionEdit()
    {
        // $this->code
        return true;
    }

    public function index()
    {
    }

    /**
     * 兼容原分销配置
     * @param array $cfg
     * @return array
     */
    protected static function defaultStoreConfig($cfg = [])
    {
        $status_check = DrpConfig::where('code', 'ischeck')->value('value');

        // 原分销商审核 ischeck 0 不需要审核 1 需要审核
        $cfg['rights_configure'] = collect($cfg['rights_configure'])->map(function ($item, $key) use ($status_check) {
            // 默认开店审核 1 开启
            if ($item['name'] == 'store_audit' && empty($item['value'])) {
                $item['value'] = $status_check ? 1 : 0;
            }

            return $item;
        })->all();

        return $cfg;
    }
}
