<?php

namespace App\Modules\Stores\Controllers;

use App\Models\StoreUser;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\TimeRepository;

/**
 * 找回管理员密码
 */
class GetPasswordController extends InitController
{
    protected $commonRepository;

    public function __construct(
        CommonRepository $commonRepository
    ) {
        $this->commonRepository = $commonRepository;
    }

    public function index()
    {
        /* 操作项的初始化 */
        if (!request()->server('REQUEST_METHOD')) {
            $method = 'GET';
        } else {
            $method = trim(request()->server('REQUEST_METHOD'));
        }

        /*------------------------------------------------------ */
        //-- 填写管理员帐号和email页面
        /*------------------------------------------------------ */
        if ($method == 'GET') {
            //验证从邮件地址过来的链接
            $act = request()->input('act', '');
            if (!empty($act) && $act == 'reset_pwd') {

                $code = trim(request()->input('code', ''));
                $adminid = (int)request()->input('uid', 0);

                /* 以用户的原密码，与code的值匹配 */
                $admin_info = StoreUser::where('id', $adminid);
                $admin_info = BaseRepository::getToArrayFirst($admin_info);

                if (empty($admin_info) || $adminid == 0 || empty($code)) {
                    return dsc_header("Location: privilege.php?act=login\n");
                }

                if (md5($adminid . $admin_info['stores_pwd'] . $admin_info['add_time']) != $code) {
                    //此链接不合法
                    $link[0]['text'] = lang('common.back');
                    $link[0]['href'] = 'privilege.php?act=login';

                    return sys_msg(lang('admin/get_password.code_param_error'), 0, $link);
                } else {
                    $this->smarty->assign('adminid', $adminid);
                    $this->smarty->assign('code', $code);
                    $this->smarty->assign('form_act', 'reset_pwd');
                }
            } elseif (!empty($act) && $act == 'forget_pwd') {
                $this->smarty->assign('form_act', 'forget_pwd');
            }

            $this->smarty->assign('ur_here', lang('admin/get_password.get_newpassword'));

            return $this->smarty->display('get_pwd.dwt');
        }

        /*------------------------------------------------------ */
        //-- 验证管理员帐号和email, 发送邮件
        /*------------------------------------------------------ */
        else {
            /* 发送找回密码确认邮件 */
            $action = request()->input('action', '');
            if (!empty($action) && $action == 'get_pwd') {
                $admin_username = trim(request()->input('user_name', ''));
                $admin_email = trim(request()->input('email', ''));

                if (empty($admin_username)) {
                    return sys_msg(lang('admin/get_password.js_languages.user_name_empty'), 1);
                } elseif (empty($admin_email)) {
                    return sys_msg(lang('admin/get_password.js_languages.email_empty'), 1);
                }

                /* 管理员用户名和邮件地址是否匹配，并取得原密码 */
                $admin_info = StoreUser::where('stores_user', $admin_username)
                    ->where('email', $admin_email);
                $admin_info = BaseRepository::getToArrayFirst($admin_info);

                if (!empty($admin_info)) {
                    /* 生成验证的code */
                    $admin_id = $admin_info['id'];
                    $code = md5($admin_id . $admin_info['stores_pwd'] . $admin_info['add_time']);

                    /* 设置重置邮件模板所需要的内容信息 */
                    $template = get_mail_template('send_password');
                    $reset_email = $this->dsc->stores_url() . STORES_PATH . '/get_password.php?act=reset_pwd&uid=' . $admin_id . '&code=' . $code;

                    $this->smarty->assign('user_name', $admin_username);
                    $this->smarty->assign('reset_email', $reset_email);
                    $this->smarty->assign('shop_name', $GLOBALS['_CFG']['shop_name']);
                    $this->smarty->assign('send_date', TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], TimeRepository::getGmTime()));
                    $this->smarty->assign('sent_date', TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], TimeRepository::getGmTime()));

                    $content = $this->smarty->fetch('str:' . $template['template_content']);

                    /* 发送确认重置密码的确认邮件 */
                    if (CommonRepository::sendEmail($admin_username, $admin_email, $template['template_subject'], $content, $template['is_html'])) {
                        //提示信息
                        $link[0]['text'] = lang('common.back');
                        $link[0]['href'] = 'privilege.php?act=login';

                        return sys_msg(lang('admin/get_password.send_success') . $admin_email, 0, $link);
                    } else {
                        return sys_msg(lang('admin/get_password.send_mail_error'), 1);
                    }
                } else {
                    /* 提示信息 */
                    return sys_msg(lang('admin/get_password.email_username_error'), 1);
                }
            } /* 验证新密码，更新管理员密码 */
            elseif (!empty($action) && $action == 'reset_pwd') {

                $new_password = trim(request()->input('password', ''));
                $adminid = intval(request()->input('adminid', 0));
                $code = trim(request()->input('code', ''));

                /* 以用户的原密码，与code的值匹配 */
                $admin_info = StoreUser::where('id', $adminid);
                $admin_info = BaseRepository::getToArrayFirst($admin_info);

                if (empty($admin_info) || empty($new_password) || empty($code) || $adminid == 0) {
                    return dsc_header("Location: privilege.php?act=login\n");
                }

                if (md5($adminid . $admin_info['password'] . $admin_info['add_time']) != $code) {
                    //此链接不合法
                    $link[0]['text'] = lang('common.back');
                    $link[0]['href'] = 'privilege.php?act=login';

                    return sys_msg(lang('admin/get_password.code_param_error'), 0, $link);
                }

                //更新管理员的密码
                $GLOBALS['user'] = init_users();
                $new_password = $GLOBALS['user']->hash_password($new_password);

                $ec_salt = rand(1, 9999);
                $data = [
                    'stores_pwd' => $new_password,
                    'ec_salt' => $ec_salt
                ];
                $result = StoreUser::where('id', $adminid)->update($data);

                if ($result > 0) {
                    $link[0]['text'] = lang('admin/get_password.login_now');
                    $link[0]['href'] = 'privilege.php?act=login';

                    return sys_msg(lang('admin/get_password.update_pwd_success'), 0, $link);
                } else {
                    return sys_msg(lang('admin/get_password.update_pwd_failed'), 1);
                }
            }
        }
    }
}
