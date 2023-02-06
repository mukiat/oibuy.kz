<?php

namespace App\Plugins\UserRights\Discount;

use App\Http\Controllers\PluginController;
use App\Plugins\UserRights\Discount\Services\DiscountRightsService;

class Discount extends PluginController
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
    protected $discountRightsService;

    public function __construct()
    {
        parent::__construct();

        $this->discountRightsService = app(DiscountRightsService::class);
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

    /**
     * 执行方法 权益卡最低折扣
     * @param array $price
     * @return mixed
     */
    public function actionMembershipCardDiscount($price = [])
    {
        return $this->discountRightsService->membershipCardDiscount($this->code, $price);
    }

    public function index()
    {
    }
}
