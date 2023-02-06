<?php

namespace App\Dsctrait\Modules\Suppliers;

use App\Libraries\Error;
use App\Libraries\Mysql;
use App\Libraries\Shop;
use App\Libraries\Template;
use App\Models\AdminUser;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\StrRepository;
use App\Modules\Suppliers\Services\Wholesale\CommonManageService;

/**
 * 管理中心公用文件
 */
define('ECS_SUPPLIER', true);

trait IniTrait
{
    protected function initialize()
    {
        $php_self = StrRepository::snake(basename($this->getCurrentControllerName(), 'Controller'));
        defined('PHP_SELF') or define('PHP_SELF', '/' . SUPPLLY_PATH . '/' . $php_self . '.php');

        $_GET = request()->query() + request()->route()->parameters();
        $_POST = request()->post();
        $_REQUEST = $_GET + $_POST;
        $_REQUEST['act'] = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';

        load_helper([
            'time', 'base', 'common', 'main', 'scws', 'ecmoban',
            'function', 'publicfunc', 'commission', 'wholesale', 'suppliers',
            'visual'
        ]);

        load_helper(['main'], 'suppliers');

        /* 对用户传入的变量进行转义操作。*/
        if (!empty($_GET)) {
            $_GET = addslashes_deep($_GET);
        }
        if (!empty($_POST)) {
            $_POST = addslashes_deep($_POST);
        }

        $_REQUEST = addslashes_deep($_REQUEST);

        /* 创建 SHOP 对象 */
        $this->dsc = $GLOBALS['dsc'] = new Shop();
        define('DATA_DIR', $this->dsc->data_dir());
        define('IMAGE_DIR', $this->dsc->image_dir());

        /* 初始化数据库类 */
        $this->db = $GLOBALS['db'] = new Mysql();

        /* 创建错误处理对象 */
        $this->err = $GLOBALS['err'] = new Error();

        /* 载入系统参数 */
        $GLOBALS['_CFG'] = config('shop');

        /* 初始化session */
        app(SessionRepository::class)->sessionRepy('ECSCP_SUPPLY_ID');

        /* 初始化 action */
        if (!isset($_REQUEST['act'])) {
            $_REQUEST['act'] = '';
        } elseif (($_REQUEST['act'] == 'login' || $_REQUEST['act'] == 'logout' || $_REQUEST['act'] == 'signin') && strpos(PHP_SELF, 'privilege.php') === false) {
            $_REQUEST['act'] = '';
        } elseif (($_REQUEST['act'] == 'forget_pwd' || $_REQUEST['act'] == 'reset_pwd' || $_REQUEST['act'] == 'get_pwd') && strpos(PHP_SELF, 'get_password.php') === false) {
            $_REQUEST['act'] = '';
        }

        load_lang(['common_suppliers', 'log_action', basename(PHP_SELF, '.php')], 'suppliers');

        clearstatcache();

        /* 如果有新版本，升级 */
        if (!isset($GLOBALS['_CFG']['dsc_version'])) {
            $GLOBALS['_CFG']['dsc_version'] = 'v1.0';
        }

        define('__ROOT__', rtrim(config('app.url'), '/') . '/');
        define('__PUBLIC__', asset('/assets'));
        define('__TPL__', asset('/assets/suppliers'));
        define('__STORAGE__', __ROOT__ . "storage");

        /* 创建 Smarty 对象。 */
        $this->smarty = $GLOBALS['smarty'] = new Template();

        $template_dir = app_path('Modules/' . StrRepository::studly(SUPPLLY_PATH)) . '/Views';
        $this->smarty->template_dir = $template_dir;
        $this->smarty->compile_dir = storage_path('framework/temp/compiled/' . SUPPLLY_PATH);
        if (config('app.debug')) {
            $this->smarty->force_compile = true;
        }
        $this->smarty->assign('copyright', DscRepository::copyright());//底部版权信息展示
        $this->smarty->assign('lang', $GLOBALS['_LANG']);
        $this->smarty->assign('help_open', $GLOBALS['_CFG']['help_open']);

        if (isset($GLOBALS['_CFG']['enable_order_check'])) {  // 为了从旧版本顺利升级到2.5.0
            $this->smarty->assign('enable_order_check', $GLOBALS['_CFG']['enable_order_check']);
        } else {
            $this->smarty->assign('enable_order_check', 0);
        }

        /* 验证管理员身份 */
        if ((!session()->has('supply_id') || intval(session('supply_id')) <= 0) &&
            $_REQUEST['act'] != 'login' && $_REQUEST['act'] != 'signin' &&
            $_REQUEST['act'] != 'check_user_name' && $_REQUEST['act'] != 'check_user_password' && //by wu
            $_REQUEST['act'] != 'forget_pwd' && $_REQUEST['act'] != 'reset_pwd' && $_REQUEST['act'] != 'check_order'
        ) {
            if (!empty($_REQUEST['is_ajax'])) {
                return make_json_error($GLOBALS['_LANG']['priv_error']);
            } else {
                return dsc_header("Location: privilege.php?act=login\n");
            }
        }

        $this->smarty->assign('token', $GLOBALS['_CFG']['token']);

        if ($_REQUEST['act'] != 'login' && $_REQUEST['act'] != 'signin' &&
            $_REQUEST['act'] != 'forget_pwd' && $_REQUEST['act'] != 'reset_pwd' && $_REQUEST['act'] != 'check_order'
        ) {
            $admin_path = preg_replace('/:\d+/', '', $this->dsc->seller_url(SUPPLLY_PATH)) . SUPPLLY_PATH; //重置路径

            if (request()->server('HTTP_REFERER') && strpos(preg_replace('/:\d+/', '', request()->server('HTTP_REFERER')), $admin_path) === false) {
                if (!empty($_REQUEST['is_ajax'])) {
                    return make_json_error($GLOBALS['_LANG']['priv_error']);
                } else {
                    return redirect()->route('supplier.privilege', ['act' => 'login']);
                }
            }
        }

        if (session()->has('supply_name')) {
            $uid = AdminUser::where('user_name', addslashes(session('supply_name')))
                ->value('user_id');
            $uid = $uid ? $uid : 0;

            if (session('supply_id') > 0 && session('supply_id') != $uid) {
                $uname = AdminUser::where('user_id', intval(session('supply_id')))
                    ->value('user_name');
                $uname = $uname ? $uname : '';

                session([
                    'supply_name' => $uname
                ]);
            }
        }

        header('content-type: text/html; charset=' . EC_CHARSET);
        header('Expires: Fri, 14 Mar 1980 20:53:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');

        $adminru = get_admin_ru_id();

        //页面导航相关 start
        load_helper(['menu', 'priv'], 'suppliers');

        //供应链后台LOGO
        $suppliers_admin_log = '';
        if (config('shop.suppliers_admin_log')) {
            $suppliers_admin_log = app(DscRepository::class)->getImagePath('assets/' . config('shop.suppliers_admin_log'));
        }
        $this->smarty->assign('suppliers_admin_log', $suppliers_admin_log);

        $sellerMenu = app(CommonManageService::class)->setSellerMenu($GLOBALS['modules'], $GLOBALS['purview']); //顶部菜单
        $this->smarty->assign('seller_menu', $sellerMenu['menu']);
        $this->smarty->assign('seller_logo', $sellerMenu['logo']);
        $this->smarty->assign('privilege_seller', $sellerMenu['privilege']);

        $menu_arr = app(CommonManageService::class)->getMenuName($GLOBALS['modules']); //当前页面
        $this->smarty->assign('menu_select', $menu_arr);

        //快捷菜单
        $user_menu_pro = app(CommonManageService::class)->getUserMenuPro($GLOBALS['modules']);
        $this->smarty->assign('user_menu_pro', $user_menu_pro);

        //用完后清空，避免影响其他功能
        unset($modules, $purview);
        //页面导航相关 end

        $supply_id = session()->has('supply_id') ? intval(session('supply_id')) : 0;

        $this->smarty->assign('ru_id', $adminru['ru_id'] ?? 0);
        $this->smarty->assign('admin_id', $supply_id);

        $this->smarty->assign('supply_id', session('supply_id', 0));
        $this->smarty->assign('supply_name', session('supply_name', ''));

        //管理员信息 by wu
        $admin_info = AdminUser::where('user_id', $supply_id)->first();
        $admin_info = $admin_info ? $admin_info->toArray() : [];

        if ($admin_info) {
            $admin_info['admin_user_img'] = app(DscRepository::class)->getImagePath($admin_info['admin_user_img']);
        }

        $this->smarty->assign('admin_info', $admin_info);

        $this->smarty->assign('site_url', str_replace(['http://', 'https://'], "", $this->dsc->get_domain()));

        // 分配字母 by zhang start
        $letter = range('A', 'Z');
        $this->smarty->assign('letter', $letter);

        if (!session()->has('menus')) {
            session([
                'menus' => []
            ]);
        }

        /* 过滤上传php文件 */
        app(DscRepository::class)->filterFilePhp();

        /* 禁止域名地址跨浏览器访问【防止攻击】 */
        $check_manage = config('app.check_manage');
        if (!is_null($check_manage) && $check_manage == 1) {
            $checkReferer = CommonRepository::dscCheckReferer();
            if ($checkReferer === false) {
                $link[] = ['href' => 'privilege.php?act=logout', 'text' => lang('user.relogin_lnk')];
                return sys_msg(lang('user.Illegal_access'), 0, $link);
            }
        }

        /* 下载云存储品牌缓存文件 */
        app(DscRepository::class)->foreverDownFile('pin_brands');
    }
}
