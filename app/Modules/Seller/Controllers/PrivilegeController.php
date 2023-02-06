<?php

namespace App\Modules\Seller\Controllers;

use App\Extensions\AdvancedThrottlesLogins;
use App\Libraries\CaptchaVerify;
use App\Libraries\Exchange;
use App\Models\AdminUser;
use App\Models\Role;
use App\Models\SellerShopinfo;
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
    protected $sessionRepository;
    protected $storeCommonService;

    public function __construct(
        CommonManageService $commonManageService,
        CommonRepository $commonRepository,
        MerchantCommonService $merchantCommonService,
        DscRepository $dscRepository,
        SessionRepository $sessionRepository,
        StoreCommonService $storeCommonService
    )
    {
        $this->commonManageService = $commonManageService;
        $this->commonRepository = $commonRepository;
        $this->merchantCommonService = $merchantCommonService;
        $this->dscRepository = $dscRepository;
        $this->sessionRepository = $sessionRepository;
        $this->storeCommonService = $storeCommonService;
    }

    public function index()
    {
        /* act操作项的初始化 */
        $act = e(request('act', 'login'));

        $menus = session()->has('menus') ? session('menus') : '';
        $this->smarty->assign('menus', $menus);

        /* 初始化 $exc 对象 */
        $exc = new Exchange($this->dsc->table("admin_user"), $this->db, 'user_id', 'user_name');

        $adminru = get_admin_ru_id();

        //ecmoban模板堂 --zhuo start
        if (isset($adminru['ru_id']) && $adminru['ru_id'] == 0) {
            $this->smarty->assign('priv_ru', 1);
        } else {
            $this->smarty->assign('priv_ru', 0);
        }

        $this->smarty->assign('seller', 1);
        $php_self = $this->commonManageService->getPhpSelf(1);
        $this->smarty->assign('php_self', $php_self);
        //ecmoban模板堂 --zhuo end

        $GLOBALS['user'] = init_users();

        /*------------------------------------------------------ */
        //-- 退出登录
        /*------------------------------------------------------ */
        if ($act == 'logout') {
            /* 清除cookie */
            $cookieList = [
                'ecscp_seller_id',
                'ecscp_seller_pass'
            ];

            $this->sessionRepository->destroy_cookie($cookieList);

            /* 清除session */
            $sessionList = [
                'seller_id',
                'seller_name',
                'seller_action_list',
                'seller_last_check',
                'seller_login_hash'
            ];
            $this->sessionRepository->destroy_session($sessionList);

            return dsc_header("Location: privilege.php?act=login\n");
        }

        /*------------------------------------------------------ */
        //-- 登陆界面
        /*------------------------------------------------------ */
        if ($act == 'login') {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Cache-Control: no-cache, must-revalidate");
            header("Pragma: no-cache");

            $sql = "SELECT value FROM " . $this->dsc->table('shop_config') . " WHERE code = 'seller_login_logo'";
            $seller_login_logo = $this->db->getOne($sql);

            $seller_login_logo = empty($seller_login_logo) ? '' : strstr($seller_login_logo, "images");

            if (!empty($seller_login_logo)) {
                $seller_login_logo = $this->dscRepository->getImagePath('assets/seller/' . $seller_login_logo);
            } else {
                $seller_login_logo = __TPL__ . '/images/seller_login_logo.png';
            }

            $this->smarty->assign('seller_login_logo', $seller_login_logo);

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
            $_POST = get_request_filter($_POST, 1);

            $username = request()->input('username', '');
            $password = request()->input('password', '');

            $username = !empty($username) ? str_replace(["=", " "], '', $username) : '';
            $username = !empty($username) ? addslashes(trim($username)) : '';
            $password = !empty($password) ? addslashes(trim($password)) : '';

            /* 检查验证码是否正确 */
            if (gd_version() > 0 && intval($GLOBALS['_CFG']['captcha']) & CAPTCHA_ADMIN) {

                /* 检查验证码是否正确 */
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
                ->where('ru_id', '>', 0)
                ->where('suppliers_id', 0);

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

                $this->commonManageService->updateLoginStatus(session('seller_login_hash')); // 插入登录状态

                session([
                    'suppliers_id' => $row['suppliers_id']
                ]);

                if ($row['action_list'] == 'all' && empty($row['last_login'])) {
                    session([
                        'shop_guide' => true
                    ]);
                }

                $new_password = $GLOBALS['user']->hash_password($password);

                // 更新最后登录时间和IP
                $data = [
                    'last_login' => gmtime(),
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

                AdminUser::where('user_id', session('seller_id'))->update($data);

                admin_log("", '', 'admin_login');//记录登陆日志

                // 清除购物车中过期的数据
                session([
                    'verify_time' => true
                ]);

                return dsc_header("Location: index.php\n");
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

            $admin_list = $this->get_admin_userlist($adminru['ru_id']);

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
            $admin_list = $this->get_admin_userlist($adminru['ru_id']);

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
            admin_priv('seller_manage');

            /* 模板赋值 */
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['admin_add']);
            $this->smarty->assign('action_link', ['href' => 'privilege.php?act=list', 'text' => $GLOBALS['_LANG']['01_admin_list']]);
            $this->smarty->assign('form_act', 'insert');
            $this->smarty->assign('action', 'add');
            $this->smarty->assign('select_role', $this->get_role_list());

            /* 显示页面 */

            return $this->smarty->display('privilege_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 添加管理员的处理
        /*------------------------------------------------------ */
        elseif ($act == 'insert') {
            admin_priv('seller_manage');

            $_POST['user_name'] = trim($_POST['user_name']);

            if ($_POST['user_name'] == trans('common.buyer') || $_POST['user_name'] == $GLOBALS['_LANG']['saler']) {
                /* 提示信息 */
                $link[] = ['text' => $GLOBALS['_LANG']['invalid_name_cant_use'], 'href' => "privilege.php?act=modif"];
                return sys_msg($GLOBALS['_LANG']['add_faile'], 0, $link);
            }

            /* 判断管理员是否已经存在 */
            if (!empty($_POST['user_name'])) {
                $is_only = $exc->is_only('user_name', stripslashes($_POST['user_name']));

                if (!$is_only) {
                    return sys_msg(sprintf($GLOBALS['_LANG']['user_name_exist'], stripslashes($_POST['user_name'])), 1);
                }
            }

            /* Email地址是否有重复 */
            if (!empty($_POST['email'])) {
                $is_only = $exc->is_only('email', stripslashes($_POST['email']));

                if (!$is_only) {
                    return sys_msg(sprintf($GLOBALS['_LANG']['email_exist'], stripslashes($_POST['email'])), 1);
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
                $error = $validator->errors()->first();
                return sys_msg($error, 1);
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

            /* 转入权限分配列表 */
            $data = [
                'user_name' => trim($_POST['user_name']),
                'email' => trim($_POST['email']),
                'password' => $new_password,
                'add_time' => $add_time,
                'nav_list' => $nav_list,
                'action_list' => $action_list,
                'role_id' => $role_id
            ];
            $new_id = AdminUser::insertGetId($data);

            /*添加链接*/
            $link[0]['text'] = $GLOBALS['_LANG']['go_allot_priv'];
            $link[0]['href'] = 'privilege.php?act=allot&id=' . $new_id . '&user=' . $_POST['user_name'] . '';

            $link[1]['text'] = $GLOBALS['_LANG']['continue_add'];
            $link[1]['href'] = 'privilege.php?act=add';

            /* 记录管理员操作 */
            admin_log($_POST['user_name'], 'add', 'privilege');

            return sys_msg($GLOBALS['_LANG']['add'] . "&nbsp;" . $_POST['user_name'] . "&nbsp;" . $GLOBALS['_LANG']['action_succeed'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 编辑管理员信息
        /*------------------------------------------------------ */
        elseif ($act == 'edit') {
            $id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

            /* 不能编辑demo这个管理员 */
            if (session('seller_name') == 'demo') {
                $link[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'privilege.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['edit_admininfo_cannot'], 0, $link);
            }

            /* 查看是否有权限编辑其他管理员的信息 */
            if (session('seller_id') != $id) {
                admin_priv('seller_manage');
            }

            /* 获取管理员信息 */
            $sql = "SELECT user_id, user_name, email, password, agency_id, role_id FROM " . $this->dsc->table('admin_user') .
                " WHERE user_id = '$id'";
            $user_info = $this->db->getRow($sql);


            /* 取得该管理员负责的办事处名称 */
            if ($user_info['agency_id'] > 0) {
                $sql = "SELECT agency_name FROM " . $this->dsc->table('agency') . " WHERE agency_id = '$user_info[agency_id]'";
                $user_info['agency_name'] = $this->db->getOne($sql);
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
                $this->smarty->assign('select_role', $this->get_role_list());
            }
            $this->smarty->assign('form_act', 'update');
            $this->smarty->assign('action', 'edit');

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

            if ($admin_name == trans('common.buyer') || $admin_name == $GLOBALS['_LANG']['saler']) {
                /* 提示信息 */
                $link[] = ['text' => $GLOBALS['_LANG']['invalid_name_cant_use'], 'href' => "privilege.php?act=modif"];
                return sys_msg($GLOBALS['_LANG']['edit_fail'], 0, $link);
            }

            if ($act == 'update') {
                /* 查看是否有权限编辑其他管理员的信息 */
                if (session('seller_id') != $_REQUEST['id']) {
                    admin_priv('seller_manage');
                }
                $g_link = 'privilege.php?act=list';
                $nav_list = '';
            } else {
                $nav_list = !empty($_POST['nav_list']) ? implode(",", $_POST['nav_list']) : '';
                $admin_id = session('seller_id');
                $g_link = 'privilege.php?act=modif';
            }
            /* 判断管理员是否已经存在 */
            if (!empty($admin_name)) {
                $is_only = $exc->num('user_name', $admin_name, $admin_id);
                if ($is_only == 1) {
                    return sys_msg(sprintf($GLOBALS['_LANG']['user_name_exist'], stripslashes($admin_name)), 1);
                }
            }

            /* Email地址是否有重复 */
            if (!empty($admin_email)) {
                $is_only = $exc->num('email', $admin_email, $admin_id);

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
            $password = $_POST['new_password'] ?? '';
            $password = trim($password);
            if ($pwd_modified == true && !empty($password) && $GLOBALS['_CFG']['sms_seller_signin'] == '1') {

                $ru_id = AdminUser::where('user_id', $admin_id)->value('ru_id');
                $ru_id = $ru_id ? $ru_id : 0;

                //商家名称
                $shop_name = $this->merchantCommonService->getShopName($ru_id, 1);

                $res = SellerShopinfo::select('mobile', 'seller_email')->where('ru_id', $ru_id);
                $shopinfo = BaseRepository::getToArrayFirst($res);

                if (!empty($shopinfo['mobile'])) {
                    //短信接口参数
                    $smsParams = [
                        'seller_name' => $admin_name ? htmlspecialchars($admin_name) : '',
                        'sellername' => $admin_name ? htmlspecialchars($admin_name) : '',
                        'seller_password' => htmlspecialchars($password),
                        'sellerpassword' => htmlspecialchars($password),
                        'current_admin_name' => session('seller_name'),
                        'currentadminname' => session('seller_name'),
                        'shop_name' => $shop_name,
                        'shopname' => $shop_name,
                        'edit_time' => TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], gmtime()),
                        'edittime' => TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], gmtime()),
                        'mobile_phone' => $shopinfo['mobile'] ? $shopinfo['mobile'] : '',
                        'mobilephone' => $shopinfo['mobile'] ? $shopinfo['mobile'] : ''
                    ];

                    $this->commonRepository->smsSend($shopinfo['mobile'], $smsParams, 'sms_seller_signin', false);
                }

                /* 发送邮件 */
                $seller_step_email = config('shop.seller_step_email') ?? 0;
                $template = get_mail_template('seller_signin');
                if ($seller_step_email == 1 && $template['template_content'] != '') {
                    if ($shopinfo['seller_email'] && ($admin_name != '' || $_POST['new_password'] != '') && $shop_name != '') {
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
                $ec_salt = rand(1, 9999);
                // 生成hash密码
                $new_password = $GLOBALS['user']->hash_password($_POST['new_password']);

                $data = [
                    'user_name' => $admin_name,
                    'email' => $admin_email,
                    'ec_salt' => $ec_salt,
                    'role_id' => $role_id,
                    'password' => $new_password
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

            $seller_shopinfo = [
                'seller_email' => $admin_email
            ];

            $this->db->autoExecute($this->dsc->table('seller_shopinfo'), $seller_shopinfo, 'UPDATE', "ru_id = '" . $adminru['ru_id'] . "'");

            /* 记录管理员操作 */
            admin_log($_POST['user_name'], 'edit', 'privilege');

            /* 如果修改了密码，则需要将session中该管理员的数据清空 */
            if ($pwd_modified && $act == 'update_self') {
                AdminUser::where('user_id', $admin_id)->update(['login_status' => '']); // 更新改密码字段

                /* 清除cookie */
                $cookieList = [
                    'ecscp_seller_id',
                    'ecscp_seller_pass'
                ];

                $this->sessionRepository->destroy_cookie($cookieList);

                /* 清除session */
                $sessionList = [
                    'seller_id',
                    'seller_name',
                    'seller_action_list',
                    'seller_last_check'
                ];
                $this->sessionRepository->destroy_session($sessionList);

                $g_link = "privilege.php?act=login";

                $msg = $GLOBALS['_LANG']['edit_password_succeed'];
            } else {
                $msg = $GLOBALS['_LANG']['edit_profile_succeed'];
            }

            /* 提示信息 */
            $link[] = ['text' => strpos($g_link, 'list') ? $GLOBALS['_LANG']['back_admin_list'] : $GLOBALS['_LANG']['return_login'], 'href' => $g_link];
            return sys_msg("$msg<script>parent.document.getElementById('header-frame').contentWindow.document.location.reload();</script>", 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 编辑个人资料
        /*------------------------------------------------------ */
        elseif ($act == 'modif') {
            /* 检查权限 */
            admin_priv('privilege_seller');
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['modif_info']);
            $this->smarty->assign('menu_select', ['action' => '10_priv_admin', 'current' => 'privilege_seller']);
            $this->smarty->assign('action_url', 'privilege.php');

            /* 不能编辑demo这个管理员 */
            if (session('seller_name') == 'demo') {
                $link[] = ['text' => $GLOBALS['_LANG']['back_admin_list'], 'href' => 'privilege.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['edit_admininfo_cannot'], 0, $link);
            }

            load_helper(['menu', 'priv'], 'seller');

            $modules = $GLOBALS['modules'];
            $purview = $GLOBALS['purview'];

            /* 包含插件菜单语言项 */
            $sql = "SELECT code FROM " . $this->dsc->table('plugins');
            $rs = $this->db->query($sql);

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

            /* 获得当前管理员数据信息 */
            $sql = "SELECT user_id, user_name, email, nav_list, ru_id " .
                "FROM " . $this->dsc->table('admin_user') . " WHERE user_id = '" . session('seller_id') . "'";
            $user_info = $this->db->getRow($sql);

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
            $seller_id = isset($_GET['id']) ? intval($_GET['id']) : session('seller_id');
            $priv_str = $this->db->getOne("SELECT action_list FROM " . $this->dsc->table('admin_user') . " WHERE user_id = '$seller_id'");

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
            $this->dscRepository->helpersLang('priv_action', 'seller');

            $id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

            admin_priv('allot_priv');
            if (session('seller_id') == $id) {
                admin_priv('all');
            }

            /* 获得该管理员的权限 */
            $priv_str = $this->db->getOne("SELECT action_list FROM " . $this->dsc->table('admin_user') . " WHERE user_id = '$id'");

            /* 如果被编辑的管理员拥有了all这个权限，将不能编辑 */
            if ($priv_str == 'all') {
                $link[] = ['text' => $GLOBALS['_LANG']['back_admin_list'], 'href' => 'privilege.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['edit_admininfo_cannot'], 0, $link);
            }

            /* 获取权限的分组数据 */
            $sql_query = "SELECT action_id, parent_id, action_code,relevance FROM " . $this->dsc->table('admin_action') .
                " WHERE parent_id = 0";
            $res = $this->db->query($sql_query);
            foreach ($res as $rows) {
                $priv_arr[$rows['action_id']] = $rows;
            }

            if ($priv_arr) {
                /* 按权限组查询底级的权限名称 */
                $sql = "SELECT action_id, parent_id, action_code,relevance FROM " . $this->dsc->table('admin_action') .
                    " WHERE parent_id " . db_create_in(array_keys($priv_arr));
                $result = $this->db->query($sql);
                foreach ($result as $priv) {
                    $priv_arr[$priv["parent_id"]]["priv"][$priv["action_code"]] = $priv;
                }

                // 将同一组的权限使用 "," 连接起来，供JS全选 ecmoban模板堂 --zhuo
                $priv_str_arr = !empty($priv_str) ? explode(',', trim($priv_str)) : [];
                foreach ($priv_arr as $action_id => $action_group) {
                    if (!empty($action_group['priv'])) {
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
            admin_priv('seller_manage');

            $id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

            /* 取得当前管理员用户名 */
            $admin_name = $this->db->getOne("SELECT user_name FROM " . $this->dsc->table('admin_user') . " WHERE user_id = '$id'");

            /* 更新管理员的权限 */
            $act_list = '';
            if (!empty($_POST['action_code'])) {
                $act_list = implode(",", $_POST['action_code']);
                $sql = "UPDATE " . $this->dsc->table('admin_user') . " SET action_list = '$act_list', role_id = '' " .
                    "WHERE user_id = '$id'";

                $this->db->query($sql);
            }

            /* 动态更新管理员的SESSION */
            if (session('seller_id') == $id) {
                session([
                    'action_list' => $act_list
                ]);
            }

            /* 记录管理员操作 */
            admin_log(addslashes($admin_name), 'edit', 'privilege');

            /* 提示信息 */
            $link[] = ['text' => $GLOBALS['_LANG']['back_admin_list'], 'href' => 'privilege.php?act=list'];
            return sys_msg($GLOBALS['_LANG']['edit'] . "&nbsp;" . $admin_name . "&nbsp;" . $GLOBALS['_LANG']['action_succeed'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 删除一个管理员
        /*------------------------------------------------------ */
        elseif ($act == 'remove') {
            $check_auth = check_authz_json('seller_drop');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

            /* 获得管理员用户名 */
            $admin_name = $this->db->getOne('SELECT user_name FROM ' . $this->dsc->table('admin_user') . " WHERE user_id = '$id'");

            /* ID为1的不允许删除 */
            if ($id == 1) {
                return make_json_error($GLOBALS['_LANG']['remove_cannot']);
            }

            /* 管理员不能删除自己 */
            if ($id == session('seller_id')) {
                return make_json_error($GLOBALS['_LANG']['remove_self_cannot']);
            }

            if ($exc->drop($id)) {
                if (config('session.driver') === 'database') {
                    $this->sessionRepository->delete_spec_admin_session($id); // 删除session中该管理员的记录
                }

                admin_log(addslashes($admin_name), 'remove', 'privilege');
                clear_cache_files();
            }

            $url = 'privilege.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));

            return dsc_header("Location: $url\n");
        }

        /*------------------------------------------------------ */
        //-- 验证用户名 by wu
        /*------------------------------------------------------ */
        elseif ($act == 'check_user_name') {
            $result = ['error' => 0, 'message' => '', 'content' => ''];
            $user_name = empty($_REQUEST['user_name']) ? '' : trim($_REQUEST['user_name']);

            if ($user_name) {
                $sql = " SELECT user_id FROM " . $this->dsc->table('admin_user') . " WHERE user_name = '$user_name' LIMIT 1";
                if ($this->db->getOne($sql)) {
                    $result['error'] = 1;
                }
            }
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 验证密码 by wu
        /*------------------------------------------------------ */
        elseif ($act == 'check_user_password') {
            $result = ['error' => 0, 'message' => '', 'content' => ''];
            $user_name = empty($_REQUEST['user_name']) ? '' : trim($_REQUEST['user_name']);
            $user_password = empty($_REQUEST['user_password']) ? '' : trim($_REQUEST['user_password']);

            $sql = "SELECT `ec_salt` FROM " . $this->dsc->table('admin_user') . " WHERE user_name = '" . $user_name . "'";
            $ec_salt = $this->db->getOne($sql);

            if (!empty($ec_salt)) {
                /* 检查密码是否正确 */
                $sql = "SELECT user_id,ru_id, user_name, password, last_login, action_list, last_login,suppliers_id,ec_salt" .
                    " FROM " . $this->dsc->table('admin_user') .
                    " WHERE user_name = '" . $user_name . "' AND password = '" . md5(md5($user_password) . $ec_salt) . "'";
            } else {
                /* 检查密码是否正确 */
                $sql = "SELECT user_id,ru_id, user_name, password, last_login, action_list, last_login,suppliers_id,ec_salt" .
                    " FROM " . $this->dsc->table('admin_user') .
                    " WHERE user_name = '" . $user_name . "' AND password = '" . md5($user_password) . "'";
            }

            $row = $this->db->getRow($sql);

            if ($row) {
                $result['error'] = 1;
            }

            return response()->json($result);
        }
    }

    /* 获取管理员列表 */
    private function get_admin_userlist($ru_id)
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'get_admin_userlist';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 过滤信息 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);

        /* 分页大小 */
        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

        $page_size = request()->cookie('dsccp_page_size');
        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        } elseif (intval($page_size) > 0) {
            $filter['page_size'] = intval($page_size);
        } else {
            $filter['page_size'] = 15;
        }

        $where = '';
        if ($filter['keywords']) {
            $where .= " AND (user_name LIKE '%" . mysql_like_quote($filter['keywords']) . "%')";
        }

        //管理员查询的权限 -- 店铺查询 start
        $filter['store_search'] = empty($_REQUEST['store_search']) ? 0 : intval($_REQUEST['store_search']);
        $filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
        $filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';

        $store_where = '';
        $store_search_where = '';
        if ($filter['store_search'] != 0) {
            if ($ru_id == 0) {
                $store_type = isset($_REQUEST['store_type']) && !empty($_REQUEST['store_type']) ? intval($_REQUEST['store_type']) : 0;

                if ($store_type) {
                    $store_search_where = "AND msi.shop_name_suffix = '$store_type'";
                }

                if ($filter['store_search'] == 1) {
                    $where .= " AND au.ru_id = '" . $filter['merchant_id'] . "' ";
                } elseif ($filter['store_search'] == 2) {
                    $store_where .= " AND msi.rz_shop_name LIKE '%" . mysql_like_quote($filter['store_keyword']) . "%'";
                } elseif ($filter['store_search'] == 3) {
                    $store_where .= " AND msi.shoprz_brand_name LIKE '%" . mysql_like_quote($filter['store_keyword']) . "%' " . $store_search_where;
                }

                if ($filter['store_search'] > 1) {
                    $where .= " AND (SELECT msi.user_id FROM " . $this->dsc->table('merchants_shop_information') . ' as msi ' .
                        " WHERE msi.user_id = au.ru_id $store_where) > 0 ";
                }
            }
        }
        //管理员查询的权限 -- 店铺查询 end

        /* 记录总数 */
        $sql = 'SELECT COUNT(*) FROM ' . $this->dsc->table('admin_user') . " AS au " . " WHERE 1 AND parent_id = 0 $where";
        $record_count = $this->db->getOne($sql);

        $filter['record_count'] = $record_count;
        $filter['page_count'] = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $sql = 'SELECT user_id, user_name, au.ru_id, email, add_time, last_login ' .
            'FROM ' . $this->dsc->table('admin_user') . ' AS au ' .
            'WHERE 1 AND parent_id = 0 ' . $where . ' ORDER BY user_id DESC ' .
            "LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ",$filter[page_size]";

        $list = $this->db->getAll($sql);

        if ($list) {
            foreach ($list as $key => $val) {
                $list[$key]['ru_name'] = $this->merchantCommonService->getShopName($val['ru_id'], 1);
                $list[$key]['add_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $val['add_time']);
                $list[$key]['last_login'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $val['last_login']);
            }
        }

        $arr = ['list' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /* 清除购物车中过期的数据 */
    private function clear_cart()
    {
        /* 取得有效的session */
        $sql = "SELECT DISTINCT session_id " .
            "FROM " . $this->dsc->table('cart') . " AS c, " .
            $this->dsc->table('sessions') . " AS s " .
            "WHERE c.session_id = s.sesskey ";
        $valid_sess = $this->db->getCol($sql);

        // 删除cart中无效的数据
        $sql = "DELETE FROM " . $this->dsc->table('cart') .
            " WHERE session_id NOT " . db_create_in($valid_sess);
        $this->db->query($sql);
        // 删除cart_combo中无效的数据 by mike
        $sql = "DELETE FROM " . $this->dsc->table('cart_combo') .
            " WHERE session_id NOT " . db_create_in($valid_sess);
        $this->db->query($sql);
    }

    /* 获取角色列表 */
    private function get_role_list()
    {
        $list = [];
        $sql = 'SELECT role_id, role_name, action_list ' .
            'FROM ' . $this->dsc->table('role');
        $list = $this->db->getAll($sql);
        return $list;
    }
}
