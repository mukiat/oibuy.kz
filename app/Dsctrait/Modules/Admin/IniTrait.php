<?php

namespace App\Dsctrait\Modules\Admin;

use App\Libraries\Error;
use App\Libraries\Mysql;
use App\Libraries\Shop;
use App\Libraries\Template;
use App\Models\AdminUser;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\StrRepository;
use Illuminate\Support\Facades\Storage;

/**
 * 管理中心公用文件
 */
define('ECS_ADMIN', true);

trait IniTrait
{
    public $action_list = '';
    public $last_login = 0;

    protected function initialize()
    {
        $php_self = StrRepository::snake(basename($this->getCurrentControllerName(), 'Controller'));
        defined('PHP_SELF') or define('PHP_SELF', '/' . ADMIN_PATH . '/' . $php_self . '.php');

        $_GET = request()->query() + request()->route()->parameters();
        $_POST = request()->post();
        $_REQUEST = $_GET + $_POST;
        $_REQUEST['act'] = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';

        load_helper(['time', 'base', 'common', 'main', 'scws', 'ecmoban', 'function', 'publicfunc', 'commission']);
        load_helper(['main'], 'admin');

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
        defined('DATA_DIR') or define('DATA_DIR', $this->dsc->data_dir());
        defined('IMAGE_DIR') or define('IMAGE_DIR', $this->dsc->image_dir());

        /* 初始化数据库类 */
        $this->db = $GLOBALS['db'] = new Mysql();

        /* 创建错误处理对象 */
        $this->err = $GLOBALS['err'] = new Error();

        /* 载入系统参数 */
        $GLOBALS['_CFG'] = config('shop');

        /* 初始化session */
        app(SessionRepository::class)->sessionRepy('ECSCP_ID');

        /* 初始化 action */
        if (!isset($_REQUEST['act'])) {
            $_REQUEST['act'] = '';
        } elseif (($_REQUEST['act'] == 'login' || $_REQUEST['act'] == 'logout' || $_REQUEST['act'] == 'signin') && strpos(PHP_SELF, 'privilege.php') === false) {
            $_REQUEST['act'] = '';
        } elseif (($_REQUEST['act'] == 'forget_pwd' || $_REQUEST['act'] == 'reset_pwd' || $_REQUEST['act'] == 'get_pwd') && strpos(PHP_SELF, 'get_password.php') === false) {
            $_REQUEST['act'] = '';
        }

        load_lang(['common', 'log_action', basename(PHP_SELF, '.php')], 'admin');

        clearstatcache();

        /* 如果有新版本，升级 */
        if (!isset($GLOBALS['_CFG']['dsc_version'])) {
            $GLOBALS['_CFG']['dsc_version'] = 'v1.0';
        }

        defined('__ROOT__') or define('__ROOT__', rtrim(config('app.url'), '/') . '/');
        defined('__PUBLIC__') or define('__PUBLIC__', asset('/assets'));
        defined('__TPL__') or define('__TPL__', asset('/assets/admin'));
        defined('__STORAGE__') or define('__STORAGE__', __ROOT__ . "storage");

        /* 创建 Smarty 对象。 */
        $this->smarty = $GLOBALS['smarty'] = new Template();

        $template_dir = app_path('Modules/Admin/Views');
        $this->smarty->template_dir = $template_dir;
        $this->smarty->compile_dir = storage_path('framework/temp/compiled/admin');
        if (config('app.debug')) {
            $this->smarty->force_compile = true;
        }
        $this->smarty->assign('copyright', DscRepository::copyright());//底部版权信息展示
        $this->smarty->assign('lang', $GLOBALS['_LANG']);
        $this->smarty->assign('cfg_lang', $GLOBALS['_CFG']['lang']);
        $this->smarty->assign('help_open', $GLOBALS['_CFG']['help_open']);
        $this->smarty->assign('mini_program', file_exists(MOBILE_WXAPP) ? true : false);
        $this->smarty->assign('hybrid_app', file_exists(MOBILE_APP) ? true : false);

        if (isset($GLOBALS['_CFG']['enable_order_check'])) {  // 为了从旧版本顺利升级到2.5.0
            $this->smarty->assign('enable_order_check', $GLOBALS['_CFG']['enable_order_check']);
        } else {
            $this->smarty->assign('enable_order_check', 0);
        }

        /* 验证管理员身份 */
        if ((!session()->has('admin_id') || intval(session('admin_id')) <= 0) &&
            $_REQUEST['act'] != 'login' && $_REQUEST['act'] != 'signin' &&
            $_REQUEST['act'] != 'forget_pwd' && $_REQUEST['act'] != 'reset_pwd' && $_REQUEST['act'] != 'check_order'
        ) {
            if (!empty($_REQUEST['is_ajax'])) {
                return make_json_error($GLOBALS['_LANG']['priv_error']);
            } else {
                return dsc_header("Location: privilege.php?act=login\n")->send();
            }
        }

        $this->smarty->assign('token', $GLOBALS['_CFG']['token']);

        if ($_REQUEST['act'] != 'login' && $_REQUEST['act'] != 'signin' &&
            $_REQUEST['act'] != 'forget_pwd' && $_REQUEST['act'] != 'reset_pwd' && $_REQUEST['act'] != 'check_order'
        ) {
            $admin_path = preg_replace('/:\d+/', '', $this->dsc->url()) . ADMIN_PATH;
            if (request()->server('HTTP_REFERER') && strpos(preg_replace('/:\d+/', '', request()->server('HTTP_REFERER')), $admin_path) === false) {
                if (!empty($_REQUEST['is_ajax'])) {
                    return make_json_error($GLOBALS['_LANG']['priv_error']);
                } else {
                    return dsc_header("Location: privilege.php?act=login\n")->send();
                }
            }
        }

        $admin_info = AdminUser::where('user_id', session('admin_id'))->select('rs_id', 'action_list', 'last_login');
        $admin_info = BaseRepository::getToArrayFirst($admin_info);

        $rs_id = $admin_info['rs_id'] ?? 0;
        $this->action_list = $admin_info['action_list'] ?? '';
        $this->last_login = $admin_info['last_login'] ?? 0;

        set_current_page();

        $letter = range('A', 'Z');
        $this->smarty->assign('letter', $letter);

        $this->smarty->assign('cat_belongs', $GLOBALS['_CFG']['cat_belongs']);
        $this->smarty->assign('brand_belongs', $GLOBALS['_CFG']['brand_belongs']);
        $this->smarty->assign('ecs_version', VERSION);
        // 查看教程开关
        $this->smarty->assign('open', config('shop.open_study', 0));

        $this->smarty->assign('admin_id', session('admin_id', 0));
        $this->smarty->assign('admin_name', session('admin_name', ''));

        if ($rs_id > 0) {
            $this->smarty->assign('rs_id', $rs_id);
        }

        //判断供应链是否可用
        $supplierEnabled = CommonRepository::judgeSupplierEnabled();
        $this->smarty->assign('supplier_enabled', $supplierEnabled);

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
