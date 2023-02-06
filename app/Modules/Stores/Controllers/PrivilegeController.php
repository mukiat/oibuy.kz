<?php

namespace App\Modules\Stores\Controllers;

use App\Extensions\AdvancedThrottlesLogins;
use App\Libraries\CaptchaVerify;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Common\CommonManageService;
use App\Services\Store\StoreManageService;
// 收银台代码
use App\Events\StoreLoginSuccessAfterEvent;

/**
 * 管理员信息以及权限管理程序
 */
class PrivilegeController extends InitController
{
    use AdvancedThrottlesLogins;

    protected $commonManageService;
    protected $storeManageService;
    protected $sessionRepository;
    protected $dscRepository;

    public function __construct(
        CommonManageService $commonManageService,
        StoreManageService $storeManageService,
        SessionRepository $sessionRepository,
        DscRepository $dscRepository
    )
    {
        $this->commonManageService = $commonManageService;
        $this->storeManageService = $storeManageService;
        $this->sessionRepository = $sessionRepository;
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {
        /* act操作项的初始化 */
        $act = e(request()->input('act', 'login'));

        $this->smarty->assign('seller', 1);
        $php_self = $this->commonManageService->getPhpSelf(1);
        $this->smarty->assign('php_self', $php_self);

        // 门店logo
        $stores_login_logo = empty(config('shop.stores_login_logo')) ? '' : strstr(config('shop.stores_login_logo'), "images");
        if (!empty($stores_login_logo)) {
            $stores_login_logo = $this->dscRepository->getImagePath('assets/stores/' . $stores_login_logo);
        } else {
            $stores_login_logo = __TPL__ . '/images/stores_login_logo.png';
        }
        $this->smarty->assign('stores_login_logo', $stores_login_logo);

        /*------------------------------------------------------ */
        //-- 登陆界面
        /*------------------------------------------------------ */
        if ($act == 'login') {

            $dsc_token = get_dsc_token();
            $this->smarty->assign('dsc_token', $dsc_token);

            $step = request()->input('step', '');

            if (!empty($step) && $step == 'captcha') {
                $captcha_width = config('shop.captcha_width') ?? 120;
                $captcha_height = config('shop.captcha_height') ?? 36;
                $captcha_font_size = config('shop.captcha_font_size') ?? 18;
                $captcha_length = config('shop.captcha_length') ?? 4;

                $code_config = [
                    'imageW' => $captcha_width, //验证码图片宽度
                    'imageH' => $captcha_height, //验证码图片高度
                    'fontSize' => $captcha_font_size, //验证码字体大小
                    'length' => $captcha_length, //验证码位数
                    'useNoise' => false, //关闭验证码杂点
                ];

                $code_config['seKey'] = 'admin_login';
                $verify = new CaptchaVerify($code_config);
                return $verify->entry();
            }

            if ((intval(config('shop.captcha')) & CAPTCHA_ADMIN) && gd_version() > 0) {
                $this->smarty->assign('gd_version', gd_version());
                $this->smarty->assign('random', mt_rand());
            }

            return $this->smarty->display('login.dwt');
        }

        /*------------------------------------------------------ */
        //-- 验证登陆信息
        /*------------------------------------------------------ */
        elseif ($act == 'signin') {

            $username = e(request()->input('stores_user', ''));
            $password = e(request()->input('stores_pwd', ''));

            $type = e(request()->input('type', ''));

            if ((intval(config('shop.captcha')) & CAPTCHA_ADMIN) && gd_version() > 0) {

                /* 检查验证码是否正确 */
                $captcha = e(request()->input('captcha', ''));

                $verify = new CaptchaVerify();

                if (isset($type) && $type == 'captcha') {
                    $captcha_code = $verify->check($captcha, 'admin_login', '', 'ajax');
                    if (!$captcha_code) {
                        return 'false';
                    } else {
                        return 'true';
                    }
                } else {
                    $captcha_code = $verify->check($captcha, 'admin_login');

                    if (!$captcha_code) {
                        return make_json_response('', 0, $GLOBALS['_LANG']['captcha_error']);
                    }
                }
            }

            $GLOBALS['user'] = init_users();

            // 检查门店会员
            $row = $this->storeManageService->storeUser($username);

            $ec_salt = $row['ec_salt'] ?? '';

            /* 检查密码是否正确(验证码正确后才验证密码) */
            if (isset($type) && $type == 'password') {
                if (empty($row)) {
                    return $this->setErrorCode(102)->failed(lang('user.user_not_exist'));
                }

                request()->offsetSet('username', $row['stores_user']);

                // 登录锁定1: 判断登录失败次数超过限制则返回true 触发锁定操作, 返回false 不会触发
                request()->offsetSet('store_user_id', $row['id']);
                if ($this->hasTooManyLoginAttempts(request())) {
                    $respond = $this->sendLockoutResponse(request());

                    return $this->failed($respond);
                }

                // 兼容原md5密码
                if (isset($row['stores_pwd']) && strlen($row['stores_pwd']) == 32) {
                    if (!empty($ec_salt)) {
                        if ($row['stores_pwd'] == md5(md5($password) . $ec_salt)) {
                            return $this->succeed('true');
                        }
                    } else {
                        if ($row['stores_pwd'] == md5($password)) {
                            return $this->succeed('true');
                        }
                    }
                } else {
                    // 验证hash密码
                    $verify = $GLOBALS['user']->verify_password($password, $row['stores_pwd'] ?? '');
                    if ($verify === true) {
                        return $this->succeed('true');
                    }
                }

                // 登录锁定2: 如果登录尝试不成功，增加尝试次数
                $respond = $this->incrementLoginAttempts(request());

                return $this->failed($respond);
            }

            if (!empty($row)) {
                if (isset($row['stores_pwd']) && strlen($row['stores_pwd']) == 32) {
                    if (!empty($ec_salt)) {
                        $stores_pwd = md5(md5($password) . $ec_salt);
                    } else {
                        $stores_pwd = md5($password);
                    }

                    if ($row['stores_pwd'] !== $stores_pwd) {
                        return sys_msg($GLOBALS['_LANG']['login_faild'], 1);
                    }
                } else {
                    // 验证hash密码
                    $verify = $GLOBALS['user']->verify_password($password, $row['stores_pwd']);
                    if ($verify === false) {
                        return sys_msg($GLOBALS['_LANG']['login_faild'], 1);
                    }
                }

                // 登录锁定3: 在用户通过身份验证后 清除计数器
                $this->clearLoginAttempts(request());

                // 登录成功
                set_admin_session($row['id'], $row['stores_user'], $row['store_id']);

                // 收银台代码
                event(new StoreLoginSuccessAfterEvent($row));

                $this->commonManageService->updateLoginStatus(session('store_login_hash'), 'store'); // 插入登录状态

                $store_user_id = session('store_user_id');

                // 更新门店会员信息
                $updata = [
                    'last_login' => TimeRepository::getGmTime(),
                    'last_ip' => $this->dscRepository->dscIp()
                ];

                // 生成hash密码
                $new_stores_pwd = $GLOBALS['user']->hash_password($password);

                if (empty($row['ec_salt'])) {
                    $ec_salt = rand(1, 9999);

                    $updata['ec_salt'] = $ec_salt;
                    $updata['stores_pwd'] = $new_stores_pwd;
                }

                if (isset($row['stores_pwd']) && strlen($row['stores_pwd']) == 32) {
                    $updata['stores_pwd'] = $new_stores_pwd;
                }

                $this->storeManageService->updateStoreUser($store_user_id, $updata);

                // 清除购物车中过期的数据
                $this->clear_cart();

                return dsc_header("Location: index.php\n");
            } else {
                return sys_msg($GLOBALS['_LANG']['login_faild'], 1);
            }
        }
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
}
