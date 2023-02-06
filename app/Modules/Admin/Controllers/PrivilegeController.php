<?php

namespace App\Modules\Admin\Controllers;

use App\Extensions\AdvancedThrottlesLogins;
use App\Libraries\CaptchaVerify;
use App\Models\AdminAction;
use App\Models\AdminUser;
use App\Models\Agency;
use App\Models\MerchantsStepsFields;
use App\Models\Plugins;
use App\Models\Role;
use App\Models\SellerShopinfo;
use App\Models\ShopConfig;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\StrRepository;
use App\Repositories\Common\TimeRepository;
use App\Rules\PasswordRule;
use App\Rules\UserName;
use App\Services\Common\CommonManageService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Privilege\PrivilegeManageService;
use App\Services\Store\StoreCommonService;
use Illuminate\Support\Facades\Validator;

/**
 * 管理员信息以及权限管理程序
 */
class PrivilegeController extends InitController
{
    use AdvancedThrottlesLogins;

    protected $commonManageService;
    protected $commonRepository;
    protected $merchantCommonService;
    protected $dscRepository;
    protected $privilegeManageService;
    protected $sessionRepository;
    protected $storeCommonService;

    public function __construct(
        CommonManageService $commonManageService,
        CommonRepository $commonRepository,
        MerchantCommonService $merchantCommonService,
        DscRepository $dscRepository,
        PrivilegeManageService $privilegeManageService,
        SessionRepository $sessionRepository,
        StoreCommonService $storeCommonService
    )
    {
        $this->commonManageService = $commonManageService;
        $this->commonRepository = $commonRepository;
        $this->merchantCommonService = $merchantCommonService;
        $this->dscRepository = $dscRepository;
        $this->privilegeManageService = $privilegeManageService;
        $this->sessionRepository = $sessionRepository;
        $this->storeCommonService = $storeCommonService;
    }

    public function index()
    {
        /* act操作项的初始化 */
        $act = e(request()->input('act', 'login'));

        $adminru = get_admin_ru_id();

        //ecmoban模板堂 --zhuo start
        if (isset($adminru['ru_id']) && $adminru['ru_id'] == 0) {
            $this->smarty->assign('priv_ru', 1);
        } else {
            $this->smarty->assign('priv_ru', 0);
        }

        grade_expire();// 商家等级到期处理
        $this->smarty->assign('seller', 1);

        $php_self = $this->commonManageService->getPhpSelf(1);
        $this->smarty->assign('php_self', $php_self);
        //ecmoban模板堂 --zhuo end

        $GLOBALS['user'] = init_users();

        /*------------------------------------------------------ */
        //-- 退出登录
        /*------------------------------------------------------ */
        if ($act == 'logout') {

            $admin_id = session('admin_id');

            /* 清除cookie */
            $cookieList = [
                'ecscp_admin_id',
                'ecscp_admin_pass'
            ];

            $this->sessionRepository->destroy_cookie($cookieList);

            /* 清除session */
            $sessionList = [
                'admin_id',
                'admin_name',
                'action_list',
                'last_check',
                'admin_login_hash'
            ];
            $this->sessionRepository->destroy_session($sessionList);

            if (empty(session('admin_id', 0))) {
                admin_log("", '', 'admin_logout', $admin_id);//记录退出日志
            }

            return dsc_header("Location: privilege.php?act=login\n");
        }

        /*------------------------------------------------------ */
        //-- 登陆界面
        /*------------------------------------------------------ */
        if ($act == 'login') {
            cookie()->queue('dscActionParam', '', 0);

            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Cache-Control: no-cache, must-revalidate");
            header("Pragma: no-cache");

            if (isset($_REQUEST['step'])) {
                if ($_REQUEST['step'] == 'captcha') {
                    $captcha_width = isset($GLOBALS['_CFG']['captcha_width']) ? $GLOBALS['_CFG']['captcha_width'] : 120;
                    $captcha_height = isset($GLOBALS['_CFG']['captcha_height']) ? $GLOBALS['_CFG']['captcha_height'] : 36;
                    $captcha_font_size = isset($GLOBALS['_CFG']['captcha_font_size']) ? $GLOBALS['_CFG']['captcha_font_size'] : 18;
                    $captcha_length = isset($GLOBALS['_CFG']['captcha_length']) ? $GLOBALS['_CFG']['captcha_length'] : 4;

                    $code_config = [
                        'imageW' => $captcha_width, //验证码图片宽度
                        'imageH' => $captcha_height, //验证码图片高度
                        'fontSize' => $captcha_font_size, //验证码字体大小
                        'length' => $captcha_length, //验证码位数
                        'useNoise' => false, //关闭验证码杂点
                    ];

                    $code_config['seKey'] = 'admin_login';
                    $img = new CaptchaVerify($code_config);
                    return $img->entry();
                }
            }

            $admin_login_logo = ShopConfig::where('code', 'admin_login_logo')->value('value');
            $admin_login_logo = empty($admin_login_logo) ? '' : strstr($admin_login_logo, "images");

            if (!empty($admin_login_logo)) {
                $admin_login_logo = $this->dscRepository->getImagePath('assets/admin/' . $admin_login_logo);
            } else {
                $admin_login_logo = __TPL__ . '/' . 'images/admin_login_logo.png';
            }

            $this->smarty->assign('admin_login_logo', $admin_login_logo);

            if ((intval($GLOBALS['_CFG']['captcha']) & CAPTCHA_ADMIN) && gd_version() > 0) {
                $this->smarty->assign('gd_version', gd_version());
                $this->smarty->assign('random', mt_rand());
            }

            return $this->smarty->display('login.dwt');
        }

        /*------------------------------------------------------ */
        //-- 验证登陆信息
        /*------------------------------------------------------ */
        elseif ($act == 'signin') {
            $now = gmtime();

            setcookie('dscActionParam', 'index|main', $now); //登录后添加默认cookie信息
            setcookie("admin_type", 0, $now);

            $_POST = get_request_filter($_POST, 1);

            $username = request()->input('username', '');
            $password = request()->input('password', '');

            $username = !empty($username) ? str_replace(["=", " "], '', $username) : '';
            $username = !empty($username) ? e(trim($username)) : '';
            $password = !empty($password) ? e(trim($password)) : '';

            /* 检查验证码是否正确 */
            if (gd_version() > 0 && (intval($GLOBALS['_CFG']['captcha']) & CAPTCHA_ADMIN)) {
                $captcha = isset($_POST['captcha']) ? trim($_POST['captcha']) : '';

                $verify = app(CaptchaVerify::class);
                if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'captcha') {
                    $captcha_code = $verify->check($captcha, 'admin_login', '', 'ajax');

                    if (!$captcha_code) {
                        return 'false';
                    } else {
                        return 'true';
                    }
                } else {
                    $captcha_code = $verify->check($captcha, 'admin_login');
                    if (!$captcha_code) {
                        return sys_msg($GLOBALS['_LANG']['captcha_error'], 1);
                    }
                }
            }

            $row = AdminUser::query()->where('user_name', $username)
                ->where('ru_id', 0);

            $row = $row->select('user_id', 'user_name', 'password', 'ec_salt', 'suppliers_id', 'action_list', 'last_login');

            $row = BaseRepository::getToArrayFirst($row);

            $ec_salt = $row['ec_salt'] ?? '';

            /* 检查密码是否正确(验证码正确后才验证密码) */
            if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'password') {
                if (empty($row)) {
                    return $this->setErrorCode(102)->failed(lang('user.user_not_exist'));
                }

                // 登录锁定1: 判断登录失败次数超过限制则返回true 触发锁定操作, 返回false 不会触发
                request()->offsetSet('admin_id', $row['user_id']);
                if ($this->hasTooManyLoginAttempts(request())) {
                    $respond = $this->sendLockoutResponse(request());

                    return $this->failed($respond);
                }

                // 兼容原md5密码
                if (isset($row['password']) && strlen($row['password']) == 32) {
                    if (!empty($ec_salt)) {
                        if ($row['password'] == md5(md5($password) . $ec_salt)) {
                            return $this->succeed('true');
                        }
                    } else {
                        if ($row['password'] == md5($password)) {
                            return $this->succeed('true');
                        }
                    }
                } else {
                    // 验证hash密码
                    $verify = $GLOBALS['user']->verify_password($password, $row['password'] ?? '');
                    if ($verify === true) {
                        return $this->succeed('true');
                    }
                }

                // 登录锁定2: 如果登录尝试不成功，增加尝试次数
                $respond = $this->incrementLoginAttempts(request());

                return $this->failed($respond);
            }

            if ($row) {
                /* 检查密码是否正确 */

                // 兼容原md5密码
                if (isset($row['password']) && strlen($row['password']) == 32) {
                    if (!empty($ec_salt)) {
                        if ($row['password'] != md5(md5($password) . $ec_salt)) {
                            return sys_msg($GLOBALS['_LANG']['login_faild'], 1);
                        }
                    } else {
                        if ($row['password'] != md5($password)) {
                            return sys_msg($GLOBALS['_LANG']['login_faild'], 1);
                        }
                    }
                } else {
                    // 验证hash密码
                    $verify = $GLOBALS['user']->verify_password($password, $row['password']);
                    if ($verify == false) {
                        return sys_msg($GLOBALS['_LANG']['login_faild'], 1);
                    }
                }

                // 检查是否为供货商的管理员 所属供货商是否有效
                if (!empty($row['suppliers_id'])) {
                    $supplier_is_check = suppliers_list_info(' is_check = 1 AND suppliers_id = ' . $row['suppliers_id']);
                    if (empty($supplier_is_check)) {
                        return sys_msg($GLOBALS['_LANG']['login_disable'], 1);
                    }
                }

                // 登录锁定3: 在用户通过身份验证后 清除计数器
                $this->clearLoginAttempts(request());

                // 登录成功
                set_admin_session($row['user_id'], $row['user_name'], $row['action_list'], $row['last_login']);

                $this->commonManageService->updateLoginStatus(session('admin_login_hash')); // 插入登录状态

                session([
                    'suppliers_id' => $row['suppliers_id']
                ]);

                if ($row['action_list'] == 'all' && empty($row['last_login'])) {
                    session(['shop_guide' => true]);
                }

                $new_password = $GLOBALS['user']->hash_password($password);

                // 更新最后登录时间和IP
                $data = [
                    'last_login' => $now,
                    'last_ip' => $this->dscRepository->dscIp()
                ];

                if (empty($row['ec_salt'])) {
                    $ec_salt = rand(1, 9999);

                    $data['ec_salt'] = $ec_salt;
                    $data['password'] = $new_password;
                }

                // 兼容原md5密码
                if (isset($row['password']) && strlen($row['password']) == 32) {
                    $data['password'] = $new_password;
                }

                AdminUser::where('user_id', session('admin_id'))->update($data);

                admin_log("", '', 'admin_login', session('admin_id'), $now);//记录登陆日志

                return dsc_header("Location: ./index.php\n");
            } else {
                return sys_msg($GLOBALS['_LANG']['login_faild'], 1);
            }
        }

        /*------------------------------------------------------ */
        //-- 管理员列表页面
        /*------------------------------------------------------ */
        elseif ($act == 'list') {
            /* 模板赋值 */
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['01_admin_list']);
            $this->smarty->assign('action_link', ['href' => 'privilege.php?act=add', 'text' => $GLOBALS['_LANG']['admin_add']]);
            $this->smarty->assign('full_page', 1);

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            $admin_list = $this->privilegeManageService->getAdminUserList($adminru['ru_id']);

            $this->smarty->assign('admin_list', $admin_list['list']);
            $this->smarty->assign('filter', $admin_list['filter']);
            $this->smarty->assign('record_count', $admin_list['record_count']);
            $this->smarty->assign('page_count', $admin_list['page_count']);
            /* 显示页面 */

            return $this->smarty->display('privilege_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 查询
        /*------------------------------------------------------ */
        elseif ($act == 'query') {
            $admin_list = $this->privilegeManageService->getAdminUserList($adminru['ru_id']);

            $this->smarty->assign('admin_list', $admin_list['list']);
            $this->smarty->assign('filter', $admin_list['filter']);
            $this->smarty->assign('record_count', $admin_list['record_count']);
            $this->smarty->assign('page_count', $admin_list['page_count']);
            return make_json_result($this->smarty->fetch('privilege_list.dwt'), '', ['filter' => $admin_list['filter'], 'page_count' => $admin_list['page_count']]);
        }

        /*------------------------------------------------------ */
        //-- 添加管理员页面
        /*------------------------------------------------------ */
        elseif ($act == 'add') {
            /* 检查权限 */
            admin_priv('admin_manage');

            /* 模板赋值 */
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['admin_add']);
            $this->smarty->assign('action_link', ['href' => 'privilege.php?act=list', 'text' => $GLOBALS['_LANG']['01_admin_list']]);
            $this->smarty->assign('form_act', 'insert');
            $this->smarty->assign('action', 'add');
            $this->smarty->assign('select_role', $this->privilegeManageService->getRoleList());
            $this->smarty->assign('role_manage', admin_priv('role_manage', '', false));

            /* 显示页面 */

            return $this->smarty->display('privilege_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 添加管理员的处理
        /*------------------------------------------------------ */
        elseif ($act == 'insert') {
            admin_priv('admin_manage');

            $user_name = isset($_POST['user_name']) && !empty($_POST['user_name']) ? addslashes(trim($_POST['user_name'])) : '';
            $email = isset($_POST['email']) && !empty($_POST['email']) ? addslashes(trim($_POST['email'])) : '';

            if ($user_name == trans('common.buyer') || $user_name == $GLOBALS['_LANG']['seller_alt']) {
                /* 提示信息 */
                $link[] = ['text' => $GLOBALS['_LANG']['invalid_name_notic'], 'href' => "privilege.php?act=modif"];
                return sys_msg($GLOBALS['_LANG']['add_fail'], 1, $link);
            }

            /* 判断管理员是否已经存在 */
            if (!empty($user_name)) {
                $is_only = AdminUser::where('user_name', stripslashes($user_name))->count();

                if ($is_only > 0) {
                    return sys_msg(sprintf($GLOBALS['_LANG']['user_name_exist'], stripslashes($user_name)), 1);
                }
            }

            /* Email地址是否有重复 */
            if (!empty($email)) {
                $is_only = AdminUser::where('email', stripslashes($email))->count();

                if ($is_only > 0) {
                    return sys_msg(sprintf($GLOBALS['_LANG']['email_exist'], stripslashes($email)), 1);
                }
            }

            $password = isset($_POST['password']) ? trim($_POST['password']) : '';

            // 数据验证
            $validator = Validator::make(request()->all(), [
                'user_name' => ['required', 'string', new UserName()],
                'password' => ['required', 'different:user_name', new PasswordRule()], // 密码
            ], [
                'password.required' => lang('user.user_pass_empty'),
                'password.different' => lang('user.user_pass_same')
            ]);

            // 返回错误
            if ($validator->fails()) {
                return sys_msg($validator->errors()->first(), 1);
            }

            /* 获取添加日期及密码 */
            $add_time = gmtime();

            $new_password = $GLOBALS['user']->hash_password($password);

            $role_id = '';
            $action_list = '';
            if (!empty($_POST['select_role'])) {
                $role_id = $_POST['select_role'];

                $action_list = Role::where('role_id', $role_id)->value('action_list');
                $action_list = $action_list ?? '';
            }

            $nav_list = AdminUser::where('action_list', 'all')->value('nav_list');
            $nav_list = $nav_list ?? '';

            $admin_id = get_admin_id();

            /* 转入权限分配列表 */
            $data = [
                'user_name' => $user_name,
                'email' => $email,
                'password' => $new_password,
                'add_time' => $add_time,
                'nav_list' => $nav_list,
                'action_list' => $action_list,
                'role_id' => $role_id,
                'parent_id' => $admin_id,
                'rs_id' => $adminru['rs_id']
            ];
            $new_id = AdminUser::insertGetId($data);

            /*添加链接*/
            $link[0]['text'] = $GLOBALS['_LANG']['go_allot_priv'];
            $link[0]['href'] = 'privilege.php?act=allot&id=' . $new_id . '&user=' . $user_name;

            $link[1]['text'] = $GLOBALS['_LANG']['continue_add'];
            $link[1]['href'] = 'privilege.php?act=add';

            /* 记录管理员操作 */
            admin_log($user_name, 'add', 'privilege');

            return sys_msg($GLOBALS['_LANG']['add'] . "&nbsp;" . $user_name . "&nbsp;" . $GLOBALS['_LANG']['action_succeed'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 编辑管理员信息
        /*------------------------------------------------------ */
        elseif ($act == 'edit') {
            /* 不能编辑demo这个管理员 */
            if (session('admin_name') == 'demo') {
                $link[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'privilege.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['edit_admininfo_cannot'], 0, $link);
            }

            $id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

            /* 查看是否有权限编辑其他管理员的信息 */
            if (session('admin_id') != $id) {
                admin_priv('admin_manage');
            }

            /* 获取管理员信息 */
            $res = AdminUser::where('user_id', $id);
            $user_info = BaseRepository::getToArrayFirst($res);

            /* 取得该管理员负责的办事处名称 */
            if ($user_info['agency_id'] > 0) {
                $user_info['agency_name'] = Agency::where('agency_id', $user_info['agency_id'])->value('agency_name');
                $user_info['agency_name'] = $user_info['agency_name'] ? $user_info['agency_name'] : '';
            }

            /* 模板赋值 */
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['admin_edit']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['01_admin_list'], 'href' => 'privilege.php?act=list']);
            $this->smarty->assign('user', $user_info);

            /* 获得该管理员的权限 */
            $priv_str = AdminUser::where('user_id', $id)->value('action_list');
            $priv_str = $priv_str ? $priv_str : '';

            /* 如果被编辑的管理员拥有了all这个权限，将不能编辑 */
            if ($priv_str != 'all') {
                $this->smarty->assign('select_role', $this->privilegeManageService->getRoleList());
            }
            $this->smarty->assign('form_act', 'update');
            $this->smarty->assign('action', 'edit');
            $this->smarty->assign('role_manage', admin_priv('role_manage', '', false));


            return $this->smarty->display('privilege_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 更新管理员信息
        /*------------------------------------------------------ */
        elseif ($act == 'update' || $act == 'update_self') {
            /* 变量初始化 */
            $admin_id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
            $admin_name = !empty($_REQUEST['user_name']) ? trim($_REQUEST['user_name']) : '';
            $admin_email = !empty($_REQUEST['email']) ? trim($_REQUEST['email']) : '';

            $_POST['new_password'] = !empty($_POST['new_password']) ? trim($_POST['new_password']) : '';

            if ($admin_name == trans('common.buyer') || $admin_name == $GLOBALS['_LANG']['seller_alt']) {
                /* 提示信息 */
                $link[] = ['text' => $GLOBALS['_LANG']['invalid_name_notic'], 'href' => "privilege.php?act=modif"];
                return sys_msg($GLOBALS['_LANG']['edit_fail'], 0, $link);
            }

            if ($act == 'update') {
                /* 查看是否有权限编辑其他管理员的信息 */
                if (session('admin_id') != $_REQUEST['id']) {
                    admin_priv('admin_manage');
                }
                $g_link = 'privilege.php?act=list';
                $nav_list = '';
            } else {
                $nav_list = !empty($_POST['nav_list']) ? implode(",", $_POST['nav_list']) : '';
                $admin_id = session('admin_id');
                $g_link = 'privilege.php?act=modif';
            }
            /* 判断管理员是否已经存在 */
            if (!empty($admin_name)) {
                $is_only = AdminUser::where('user_name', $admin_name)
                    ->where('user_id', '<>', $admin_id)
                    ->count();
                if ($is_only == 1) {
                    return sys_msg(sprintf($GLOBALS['_LANG']['user_name_exist'], stripslashes($admin_name)), 1);
                }
            }

            /* Email地址是否有重复 */
            if (!empty($admin_email)) {
                $is_only = AdminUser::where('email', $admin_email)
                    ->where('user_id', '<>', $admin_id)
                    ->count();

                if ($is_only == 1) {
                    return sys_msg(sprintf($GLOBALS['_LANG']['email_exist'], stripslashes($admin_email)), 1);
                }
            }

            //如果要修改密码
            $pwd_modified = false;

            if (!empty($_POST['new_password'])) {

                /* 比较新密码和确认密码是否相同 */
                if ($_POST['new_password'] <> $_POST['pwd_confirm']) {
                    $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                    return sys_msg($GLOBALS['_LANG']['js_languages']['password_error'], 1, $link);
                } else {
                    $pwd_modified = true;
                }

                // 数据验证
                $validator = Validator::make(request()->all(), [
                    'new_password' => ['filled', 'different:user_name', new PasswordRule()], // 密码
                ], [
                    'new_password.filled' => lang('user.user_pass_empty'),
                    'new_password.different' => lang('user.user_pass_same')
                ]);

                // 返回错误
                if ($validator->fails()) {
                    $error = $validator->errors()->first();
                    return sys_msg($error, 1);
                }
            }

            $role_id = '';
            $action_list = '';
            if (!empty($_POST['select_role'])) {
                $role_id = $_POST['select_role'];

                $action_list = Role::where('role_id', $role_id)->value('action_list');
                $action_list = $action_list ?? '';
            }

            //给商家发短信，邮件
            $ru_id = AdminUser::where('user_id', $admin_id)->value('ru_id');
            $ru_id = $ru_id ? $ru_id : 0;

            if ($ru_id && $GLOBALS['_CFG']['sms_seller_signin'] == '1') {
                //商家名称
                $shop_name = $this->merchantCommonService->getShopName($ru_id, 1);

                $res = SellerShopinfo::where('ru_id', $ru_id);
                $shopinfo = BaseRepository::getToArrayFirst($res);

                if (empty($shopinfo['mobile'])) {
                    $field = get_table_file_name($this->dsc->table('merchants_steps_fields'), 'contactPhone');

                    if ($field['bool']) {
                        $res = MerchantsStepsFields::where('user_id', $ru_id);
                        $stepsinfo = BaseRepository::getToArrayFirst($res);
                        $stepsinfo['mobile'] = $stepsinfo['contactPhone'] ?? '';

                        $shopinfo['mobile'] = $stepsinfo['mobile'];
                    }
                }

                if ($shopinfo && !empty($shopinfo['mobile'])) {
                    //短信接口参数
                    $smsParams = [
                        'seller_name' => $admin_name ? htmlspecialchars($admin_name) : '',
                        'sellername' => $admin_name ? htmlspecialchars($admin_name) : '',
                        'login_name' => $shop_name ? $shop_name : '',
                        'loginname' => $shop_name ? $shop_name : '',
                        'password' => isset($_POST['new_password']) ? htmlspecialchars($_POST['new_password']) : '',
                        'admin_name' => session('admin_name'),
                        'adminname' => session('admin_name'),
                        'edit_time' => TimeRepository::getLocalDate('Y-m-d H:i:s', gmtime()),
                        'edittime' => TimeRepository::getLocalDate('Y-m-d H:i:s', gmtime()),
                        'mobile_phone' => $shopinfo['mobile'] ? $shopinfo['mobile'] : '',
                        'mobilephone' => $shopinfo['mobile'] ? $shopinfo['mobile'] : ''
                    ];

                    $this->commonRepository->smsSend($shopinfo['mobile'], $smsParams, 'sms_seller_signin', false);
                }

                /* 发送邮件 */
                $template = get_mail_template('seller_signin');
                if ($adminru['ru_id'] == 0 && $template['template_content'] != '') {
                    $field = get_table_file_name($this->dsc->table('merchants_steps_fields'), 'contactEmail');

                    if ($field['bool']) {
                        if (empty($shopinfo['seller_email'])) {
                            $res = MerchantsStepsFields::where('user_id', $ru_id);
                            $stepsinfo = BaseRepository::getToArrayFirst($res);
                            $stepsinfo['seller_email'] = $stepsinfo['contactEmail'] ?? '';


                            $shopinfo['seller_email'] = $stepsinfo['seller_email'];
                        }
                    }

                    if ($shopinfo['seller_email'] && ($admin_name != '' || $_POST['new_password'] != '')) {
                        $this->smarty->assign('shop_name', $shop_name);
                        $this->smarty->assign('seller_name', $admin_name);
                        $this->smarty->assign('seller_psw', trim($_POST['new_password']));
                        $this->smarty->assign('site_name', $GLOBALS['_CFG']['shop_name']);
                        $this->smarty->assign('send_date', TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], gmtime()));
                        $content = $this->smarty->fetch('str:' . $template['template_content']);

                        CommonRepository::sendEmail($admin_name, $shopinfo['seller_email'], $template['template_subject'], $content, $template['is_html']);
                    }
                }
            }

            //更新管理员信息
            if ($pwd_modified) {
                $ec_salt = rand(1000, 9999);
                // 生成hash密码
                $new_password = $GLOBALS['user']->hash_password($_POST['new_password']);

                $data = [
                    'user_name' => $admin_name,
                    'email' => $admin_email,
                    'ec_salt' => $ec_salt,
                    'role_id' => $role_id,
                    'password' => $new_password,
                ];
            } else {
                $data = [
                    'user_name' => $admin_name,
                    'email' => $admin_email,
                    'role_id' => $role_id
                ];
            }

            if ($action_list) {
                $data['action_list'] = $action_list;
            }

            if ($nav_list) {
                $data['nav_list'] = $nav_list;
            }

            AdminUser::where('user_id', $admin_id)->update($data);

            /* 取得当前管理员用户名 */
            $current_admin_name = AdminUser::where('user_id', session('admin_id'))->value('user_name');
            $current_admin_name = $current_admin_name ? $current_admin_name : '';

            /* 记录管理员操作 */
            admin_log($current_admin_name, 'edit', 'privilege');

            /* 如果修改了密码，则需要将session中该管理员的数据清空 */
            if ($pwd_modified && session('admin_name') == $admin_name) {
                AdminUser::where('user_id', $admin_id)->update(['login_status' => '']); // 更新改密码字段

                /* 清除cookie */
                $cookieList = [
                    'ecscp_admin_id',
                    'ecscp_admin_pass'
                ];

                $this->sessionRepository->destroy_cookie($cookieList);

                /* 清除session */
                $sessionList = [
                    'admin_id',
                    'admin_name',
                    'action_list',
                    'last_check'
                ];
                $this->sessionRepository->destroy_session($sessionList);

                $g_link = "privilege.php?act=login";

                if (config('session.driver') === 'database') {
                    $this->sessionRepository->delete_spec_admin_session(session('admin_id'));
                }

                $msg = $GLOBALS['_LANG']['edit_password_succeed'] . "<script>if (window != top)top.location.href = location.href;</script>";
            } else {
                $msg = $GLOBALS['_LANG']['edit_profile_succeed'];
            }

            /* 提示信息 */
            $link[] = ['text' => strpos($g_link, 'list') ? $GLOBALS['_LANG']['back_admin_list'] : $GLOBALS['_LANG']['modif_info'], 'href' => $g_link];
            return sys_msg($msg, 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 编辑个人资料
        /*------------------------------------------------------ */
        elseif ($act == 'modif') {
            /* 不能编辑demo这个管理员 */
            if (session('admin_name') == 'demo') {
                $link[] = ['text' => $GLOBALS['_LANG']['back_admin_list'], 'href' => 'privilege.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['edit_admininfo_cannot'], 0, $link);
            }

            $id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

            load_helper(['menu', 'priv'], 'admin');

            $modules = $GLOBALS['modules'];
            $purview = $GLOBALS['purview'];

            /* 包含插件菜单语言项 */
            $res = Plugins::whereRaw(1);
            $rs = BaseRepository::getToArrayGet($res);

            if ($rs) {
                foreach ($rs as $row) {
                    /* 取得语言项 */
                    if (file_exists(app_path('Plugins/' . StrRepository::studly($row['code']) . '/Languages/common_' . $GLOBALS['_CFG']['lang'] . '.php'))) {
                        include_once(app_path('Plugins/' . StrRepository::studly($row['code']) . '/Languages/common_' . $GLOBALS['_CFG']['lang'] . '.php'));
                    }

                    /* 插件的菜单项 */
                    if (file_exists(app_path('Plugins/' . StrRepository::studly($row['code']) . '/Languages/inc_menu.php'))) {
                        include_once(app_path('Plugins/' . StrRepository::studly($row['code']) . '/Languages/inc_menu.php'));
                    }
                }
            }


            if ($modules) {
                foreach ($modules as $key => $value) {
                    ksort($modules[$key]);
                }
                ksort($modules);

                foreach ($modules as $key => $val) {
                    if (is_array($val)) {
                        foreach ($val as $k => $v) {
                            if ($purview) {
                                if (isset($purview[$k]) && is_array($purview[$k])) {
                                    $boole = false;
                                    foreach ($purview[$k] as $action) {
                                        $boole = $boole || admin_priv($action, '', false);
                                    }
                                    if (!$boole) {
                                        unset($modules[$key][$k]);
                                    }
                                } elseif (isset($purview[$k]) && !admin_priv($purview[$k], '', false)) {
                                    unset($modules[$key][$k]);
                                }
                            }
                        }
                    }
                }
            }

            /* 获得当前管理员数据信息 */
            $res = AdminUser::where('user_id', session('admin_id'));
            $user_info = BaseRepository::getToArrayFirst($res);

            /* 获取导航条 */
            $nav_arr = (trim($user_info['nav_list']) == '') ? [] : explode(",", $user_info['nav_list']);
            $nav_lst = [];
            foreach ($nav_arr as $val) {
                $arr = explode('|', $val);
                $nav_lst[$arr[1]] = $arr[0];
            }

            /* 模板赋值 */
            $this->smarty->assign('lang', $GLOBALS['_LANG']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['modif_info']);

            if ($user_info['ru_id'] == 0) {
                $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['01_admin_list'], 'href' => 'privilege.php?act=list']);
            }

            $this->smarty->assign('user', $user_info);
            $this->smarty->assign('menus', $modules);
            $this->smarty->assign('nav_arr', $nav_lst);

            $this->smarty->assign('form_act', 'update_self');
            $this->smarty->assign('action', 'modif');

            /* 获得该管理员的权限 ecmoban模板堂 --zhuo*/
            $priv_str = AdminUser::where('user_id', $id)->value('action_list');
            $priv_str = $priv_str ? $priv_str : '';

            /* 如果被编辑的管理员拥有了all这个权限，将不能编辑 */
            if ($priv_str == 'all') {
                $this->smarty->assign('priv_str', 1);
            }

            /* 显示页面 */

            return $this->smarty->display('privilege_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 为管理员分配权限
        /*------------------------------------------------------ */
        elseif ($act == 'allot') {
            $this->dscRepository->helpersLang('priv_action', 'admin');

            admin_priv('allot_priv');

            $id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

            if (session('admin_id') == $id) {
                admin_priv('all');
            }

            /* 获得该管理员的权限 */
            $priv_str = AdminUser::where('user_id', $id)->value('action_list');
            $priv_str = $priv_str ? $priv_str : '';

            /* 如果被编辑的管理员拥有了all这个权限，将不能编辑 */
            if ($priv_str == 'all') {
                $link[] = ['text' => $GLOBALS['_LANG']['back_admin_list'], 'href' => 'privilege.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['edit_admininfo_cannot'], 0, $link);
            }

            //子管理员 start
            $admin_id = get_admin_id();
            $action_list = get_table_date('admin_user', "user_id='$admin_id'", ['action_list'], 2);
            $action_array = explode(',', $action_list);
            //子管理员 end

            /* 获取权限的分组数据 */
            $res = AdminAction::where('parent_id', 0);
            $res = BaseRepository::getToArrayGet($res);

            $priv_arr = [];
            if ($res) {
                foreach ($res as $rows) {
                    //卖场 start
                    if ($rows['action_code'] == 'region_store') {
                        continue;
                    }
                    //卖场 end

                    //店铺后台 小商店 start
                    if ($rows['action_code'] == 'seller_wxshop') {
                        continue;
                    }
                    //店铺后台 小商店 end

                    //批发 start
                    if (!file_exists(SUPPLIERS) && $rows['seller_show'] == 2) {
                        continue;
                    }
                    //批发 end

                    // 微信通
                    if (!file_exists(MOBILE_WECHAT) && $rows['action_code'] == 'wechat') {
                        continue;
                    }
                    // 微分销
                    if (!file_exists(MOBILE_DRP) && $rows['action_code'] == 'drp') {
                        continue;
                    }

                    // 微信小程序
                    if (!file_exists(MOBILE_WXAPP) && $rows['action_code'] == 'wxapp') {
                        continue;
                    }

                    // 拼团
                    if (!file_exists(MOBILE_TEAM) && $rows['action_code'] == 'team') {
                        continue;
                    }

                    // 砍价
                    if (!file_exists(MOBILE_BARGAIN) && $rows['action_code'] == 'bargain_manage') {
                        continue;
                    }

                    $priv_arr[$rows['action_id']] = $rows;
                }
            }

            if ($priv_arr) {
                /* 按权限组查询底级的权限名称 */
                $res = AdminAction::whereIn('parent_id', array_keys($priv_arr));
                $result = BaseRepository::getToArrayGet($res);

                foreach ($result as $priv) {
                    //子管理员 start
                    if (!empty($action_list) && $action_list != 'all' && !in_array($priv['action_code'], $action_array)) {
                        continue;
                    }
                    //子管理员 end

                    //批发 start
                    if (!file_exists(SUPPLIERS) && $priv['action_code'] == 'supplier_apply') {
                        continue;
                    }
                    //批发 end

                    $priv_arr[$priv["parent_id"]]["priv"][$priv["action_code"]] = $priv;
                }

                // 将同一组的权限使用 "," 连接起来，供JS全选 ecmoban模板堂 --zhuo
                $priv_str_arr = !empty($priv_str) ? explode(',', trim($priv_str)) : [];
                foreach ($priv_arr as $action_id => $action_group) {
                    if (isset($action_group['priv']) && $action_group['priv']) {
                        $priv = @array_keys($action_group['priv']);
                        $priv_arr[$action_id]['priv_list'] = implode(',', $priv);

                        foreach ($action_group['priv'] as $key => $val) {
                            if (!empty($priv_str_arr) && !empty($val['action_code'])) {
                                $true = (in_array($val['action_code'], $priv_str_arr) || $priv_str == 'all') ? 1 : 0;
                            } else {
                                $true = 0;
                            }

                            $priv_arr[$action_id]['priv'][$key]['cando'] = $true;
                        }
                    }
                }
            }

            /* 赋值 */
            $this->smarty->assign('lang', $GLOBALS['_LANG']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['allot_priv'] . ' [ ' . $_GET['user'] . ' ] ');
            $this->smarty->assign('action_link', ['href' => 'privilege.php?act=list', 'text' => $GLOBALS['_LANG']['01_admin_list']]);
            $this->smarty->assign('priv_arr', $priv_arr);
            $this->smarty->assign('form_act', 'update_allot');
            $this->smarty->assign('user_id', $id);

            /* 显示页面 */

            return $this->smarty->display('privilege_allot.dwt');
        }

        /*------------------------------------------------------ */
        //-- 更新管理员的权限
        /*------------------------------------------------------ */
        elseif ($act == 'update_allot') {
            admin_priv('admin_manage');

            $id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

            /* 取得当前管理员用户名 */
            $admin_name = AdminUser::where('user_id', $id)->value('user_name');
            $admin_name = $admin_name ? addslashes($admin_name) : '';

            /* 更新管理员的权限 */
            $act_list = '';
            if (!empty($_POST['action_code'])) {
                $act_list = implode(",", $_POST['action_code']);

                $data = [
                    'action_list' => $act_list,
                    'role_id' => ''
                ];
                AdminUser::where('user_id', $_POST['id'])->update($data);
            }

            /* 动态更新管理员的SESSION */
            if (session('admin_id') == $id) {
                session([
                    'action_list' => $act_list
                ]);
            }

            /* 记录管理员操作 */
            admin_log($admin_name, 'edit', 'privilege');

            /* 提示信息 */
            $link[] = ['text' => $GLOBALS['_LANG']['back_admin_list'], 'href' => 'privilege.php?act=list'];
            return sys_msg($GLOBALS['_LANG']['edit'] . "&nbsp;" . $admin_name . "&nbsp;" . $GLOBALS['_LANG']['action_succeed'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 删除一个管理员
        /*------------------------------------------------------ */
        elseif ($act == 'remove') {
            $check_auth = check_authz_json('admin_drop');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

            /* 获得管理员用户名 */
            $admin_name = AdminUser::where('user_id', $id)->value('user_name');
            $admin_name = $admin_name ? $admin_name : '';
            /* ID为1的不允许删除 */
            if ($id == 1) {
                return make_json_error($GLOBALS['_LANG']['remove_cannot']);
            }

            /* 管理员不能删除自己 */
            if ($id == session('admin_id')) {
                return make_json_error($GLOBALS['_LANG']['remove_self_cannot']);
            }

            $res = AdminUser::where('user_id', $id)->delete();
            if ($res > 0) {
                $this->sessionRepository->delete_spec_admin_session($id); // 删除session中该管理员的记录

                admin_log(addslashes($admin_name), 'remove', 'privilege');
                clear_cache_files();
            }

            $url = 'privilege.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));

            return dsc_header("Location: $url\n");
        }
    }
}
