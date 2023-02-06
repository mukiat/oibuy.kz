<?php

namespace App\Dsctrait\Modules\Stores;

use App\Libraries\Error;
use App\Libraries\Mysql;
use App\Libraries\Shop;
use App\Libraries\Template;
use App\Models\StoreUser;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\StrRepository;
// 收银台代码
use App\Events\StoreTopMenuExtendEvent;

/**
 * 管理中心公用文件
 */
define('ECS_STORE', true);

trait IniTrait
{
    protected function initialize()
    {
        $php_self = StrRepository::snake(basename($this->getCurrentControllerName(), 'Controller'));
        defined('PHP_SELF') or define('PHP_SELF', $php_self . '.php');

        $_GET = request()->query() + request()->route()->parameters();
        $_POST = request()->post();
        $_REQUEST = $_GET + $_POST;
        $_REQUEST['act'] = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';

        load_helper(['time', 'base', 'common', 'main', 'scws', 'ecmoban', 'function']);
        load_helper(['main'], STORES_PATH);

        /* 对用户传入的变量进行转义操作。*/
        if (!empty($_GET)) {
            $_GET = addslashes_deep($_GET);
        }
        if (!empty($_POST)) {
            $_POST = addslashes_deep($_POST);
        }

        $_REQUEST = addslashes_deep($_REQUEST);

        /* 创建 DSCMALL 对象 */
        $this->dsc = $GLOBALS['dsc'] = new Shop();
        define('DATA_DIR', $this->dsc->data_dir());
        define('IMAGE_DIR', $this->dsc->image_dir());

        /* 初始化数据库类 */
        $this->db = $GLOBALS['db'] = new Mysql();

        /* 创建错误处理对象 */
        $this->err = $GLOBALS['err'] = new Error();

        /* 载入系统参数 */
        $GLOBALS['_CFG'] = config('shop');

        config('shop.editing_tools', 'seller_ueditor'); //修改编辑器目录 by wu

        /* 初始化session */
        app(SessionRepository::class)->sessionRepy('ECSCP_STORES_ID');

        /* 初始化 action */
        if (!isset($_REQUEST['act'])) {
            $_REQUEST['act'] = '';
        } elseif (($_REQUEST['act'] == 'login' || $_REQUEST['act'] == 'logout' || $_REQUEST['act'] == 'signin') && strpos(PHP_SELF, 'privilege.php') === false) {
            $_REQUEST['act'] = '';
        } elseif (($_REQUEST['act'] == 'forget_pwd' || $_REQUEST['act'] == 'reset_pwd' || $_REQUEST['act'] == 'get_pwd') && strpos(PHP_SELF, 'get_password.php') === false) {
            $_REQUEST['act'] = '';
        }

        load_lang(['common_merchants', 'common_stores', 'log_action', basename(PHP_SELF, '.php')], STORES_PATH);

        clearstatcache();

        /* 如果有新版本，升级 */
        if (!isset($GLOBALS['_CFG']['dsc_version'])) {
            $GLOBALS['_CFG']['dsc_version'] = 'v3.0';
        }

        define('__ROOT__', rtrim(config('app.url'), '/') . '/');
        define('__PUBLIC__', asset('/assets'));
        define('__TPL__', asset('/assets/stores'));
        define('__STORAGE__', __ROOT__ . "storage");

        /* 创建 Smarty 对象。 */
        $this->smarty = $GLOBALS['smarty'] = new Template();

        $template_dir = app_path('Modules/' . StrRepository::studly(STORES_PATH)) . '/Views';

        $this->smarty->template_dir = $template_dir;
        $this->smarty->compile_dir = storage_path('framework/temp/compiled/' . STORES_PATH);
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

        //门店后台LOGO
        $stores_logo = config('shop.stores_logo', '');
        if (!empty($stores_logo)) {
            $stores_logo = strstr($stores_logo, "images");
            $stores_logo = app(DscRepository::class)->getImagePath('assets/stores/' . $stores_logo);
        } else {
            $stores_logo = __TPL__ . '/images/stores_logo.png';
        }

        $this->smarty->assign('stores_logo', $stores_logo);

        // 收银台代码
        $event = new StoreTopMenuExtendEvent();
        event($event);

        $this->smarty->assign('store_top_menu_extend', $event->store_top_menu_extend);
        
        /* 验证管理员身份 */
        if ((!session()->has('store_user_id') || intval(session('store_user_id')) <= 0) &&
            $_REQUEST['act'] != 'login' && $_REQUEST['act'] != 'signin' &&
            $_REQUEST['act'] != 'check_user_name' && $_REQUEST['act'] != 'check_user_password' && $_REQUEST['act'] != 'operate' && //by wu
            $_REQUEST['act'] != 'forget_pwd' && $_REQUEST['act'] != 'reset_pwd' && $_REQUEST['act'] != 'check_order') {
            if (!empty($_REQUEST['is_ajax'])) {
                return make_json_error($GLOBALS['_LANG']['priv_error']);
            } else {
                return dsc_header("Location: privilege.php?act=login\n");
            }
        }

        $this->smarty->assign('token', $GLOBALS['_CFG']['token']);

        if ($_REQUEST['act'] != 'login' && $_REQUEST['act'] != 'signin' &&
            $_REQUEST['act'] != 'forget_pwd' && $_REQUEST['act'] != 'reset_pwd' && $_REQUEST['act'] != 'check_order') {
            $admin_path = preg_replace('/:\d+/', '', $this->dsc->stores_url()) . STORES_PATH; //重置路径
            if (request()->server('HTTP_REFERER') && strpos(preg_replace('/:\d+/', '', request()->server('HTTP_REFERER')), $admin_path) === false) {
                if (!empty($_REQUEST['is_ajax'])) {
                    return make_json_error($GLOBALS['_LANG']['priv_error']);
                } else {
                    return dsc_header("Location: privilege.php?act=login\n");
                }
            }
        }

        if (session()->has('stores_name')) {
            $uid = StoreUser::where('stores_user', addslashes(session('stores_name')))->value('id');
            $uid = $uid ? $uid : 0;

            if (session('store_user_id') > 0 && session('store_user_id') != $uid) {
                $uname = StoreUser::where('id', intval(session('store_user_id')))->value('stores_user');
                $uname = $uname ? $uname : 0;

                session([
                    'stores_name' => $uname
                ]);
            }
        }

        /* 管理员登录后可在任何页面使用 act=phpinfo 显示 phpinfo() 信息 */
        if ($_REQUEST['act'] == 'phpinfo' && function_exists('phpinfo')) {
            phpinfo();
        }

        //header('Cache-control: private');
        header('content-type: text/html; charset=' . EC_CHARSET);
        header('Expires: Fri, 14 Mar 1980 20:53:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');


        if (session()->has('admin_ru_id') && session('admin_ru_id')) {
            $this->smarty->assign('ru_id', session('admin_ru_id'));
        }

        if (!empty(session('store_user_id'))) {
            $store_user_info = StoreUser::where('id', intval(session('store_user_id')))->first();
            $store_user_info = $store_user_info ? $store_user_info->toArray() : [];

            $store_user_info['store_user_img'] = $store_user_info && $store_user_info['store_user_img'] ? app(DscRepository::class)->getImagePath($store_user_info['store_user_img']) : '';
            $this->smarty->assign('store_user_info', $store_user_info);
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
