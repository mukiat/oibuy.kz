<?php

use App\Models\Users;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Cart\CartCommonService;
use App\Services\User\UserCommonService;
use App\Services\User\UserCouponsService;
use App\Services\User\UserService;

/**
 * 用户注册，登录函数
 *
 * @access  public
 * @param string $username 注册用户名
 * @param string $password 用户密码
 * @param string $email 注册email
 * @param array $other 注册的其他信息
 *
 * @return  bool         $bool
 */
function register($username, $password, $email, $other = [], $register_mode = 0)
{
    /* 检查注册是否关闭 */
    if (!empty(config('shop.shop_reg_closed', 0))) {
        $GLOBALS['err']->add(trans('common.shop_register_closed'));
    }
    /* 检查username */
    if (empty($username)) {
        $GLOBALS['err']->add(lang('user.username_empty'));
    } else {
        if (preg_match('/\'\/^\\s*$|^c:\\\\con\\\\con$|[%,\\*\\"\\s\\t\\<\\>\\&\'\\\\]/', $username)) {
            $GLOBALS['err']->add(sprintf(lang('user.username_invalid'), htmlspecialchars($username)));
        }
    }

    /* 检查email */
    if (!empty($email)) {
        if (!CommonRepository::getMatchEmail($email)) {
            $GLOBALS['err']->add(sprintf(lang('user.email_invalid'), htmlspecialchars($email)));
        }
    }

    /* 检查mobile_phone */
    if ($register_mode == 1) {
        if (empty($other['mobile_phone'])) {
            $GLOBALS['err']->add(lang('user.msg_mobile_code_blank'));
        } else {
            if ($GLOBALS['_CFG']['sms_signin'] == 1) {
                if ($other['mobile_phone'] != session('sms_mobile') || $other['mobile_code'] != session('sms_mobile_code')) {
                    $GLOBALS['err']->add(lang('user.msg_mobile_mobile_code'));
                }
            }

            if (!is_phone_number($other['mobile_phone'])) {
                $GLOBALS['err']->add(sprintf(lang('user.msg_mobile_invalid'), htmlspecialchars($other['mobile_phone'])));
            }
        }
    }

    if ($GLOBALS['err']->error_no() > 0) {
        return false;
    }

    /* 如果不以手机验证则不保存手机号 */
    if ($register_mode != 1) {
        // $other['mobile_phone'] = '';
    }

    $other['is_validated'] = isset($other['is_validated']) ? $other['is_validated'] : 0;

    //用户注册方式信息
    $user_registerMode = ['email' => $email, 'mobile_phone' => $other['mobile_phone'], 'is_validated' => $other['is_validated'], 'register_mode' => $register_mode];
    if (!$GLOBALS['user']->add_user($username, $password, $user_registerMode)) {
        if ($GLOBALS['user']->error == ERR_INVALID_USERNAME) {
            $GLOBALS['err']->add(sprintf(lang('user.username_invalid'), $username));
        } elseif ($GLOBALS['user']->error == ERR_USERNAME_NOT_ALLOW) {
            $GLOBALS['err']->add(sprintf(lang('user.username_not_allow'), $username));
        } elseif ($GLOBALS['user']->error == ERR_USERNAME_EXISTS) {
            $GLOBALS['err']->add(sprintf(lang('user.username_exist'), $username));
        } elseif ($GLOBALS['user']->error == ERR_INVALID_EMAIL) {
            $GLOBALS['err']->add(sprintf(lang('user.email_invalid'), $email));
        } elseif ($GLOBALS['user']->error == ERR_EMAIL_NOT_ALLOW) {
            $GLOBALS['err']->add(sprintf(lang('user.email_not_allow'), $email));
        } elseif ($GLOBALS['user']->error == ERR_EMAIL_EXISTS) {
            $GLOBALS['err']->add(sprintf(lang('user.email_exist'), $email));
        } elseif ($GLOBALS['user']->error == ERR_PASSWORD_NOT_ALLOW) {
            $GLOBALS['err']->add(lang('user.user_pass_same'));
        } else {
            $GLOBALS['err']->add('UNKNOWN ERROR!');
        }

        //注册失败
        return false;
    } else {
        //注册成功

        /* 设置成登录状态 */
        $GLOBALS['user']->set_session($username);
        $GLOBALS['user']->set_cookie($username);

        $user_id = session('user_id');

        $user = Users::where('user_id', $user_id)->first();
        $user = $user ? $user->toArray() : [];

        // 用户注册事件
        $extendParam = [];

        /* 推荐分成处理 */
        $extendParam['parent_id'] = $other['parent_id'] ?? 0;

        /* 推荐分销商处理 */
        if (file_exists(MOBILE_DRP)) {
            $extendParam['drp_parent_id'] = $other['drp_parent_id'] ?? 0;
        }

        $extendParam['shop_config'] = config('shop');
        event(new \App\Events\UserRegisterEvent($user, $extendParam));

        //定义other合法的变量数组
        $other_key_array = ['msn', 'qq', 'office_phone', 'home_phone', 'nick_name', 'parent_id'];

        $time = TimeRepository::getGmTime();
        $update_data['reg_time'] = $time;
        if ($other) {
            $date = $other;
            foreach ($date as $key => $val) {
                //删除非法key值
                if (!in_array($key, $other_key_array)) {
                    unset($date[$key]);
                } else {
                    $date[$key] = htmlspecialchars(trim($val)); //防止用户输入javascript代码
                }
            }
            $update_data = array_merge($update_data, $date);
        }

        $update_data['nick_name'] = !empty($update_data['nick_name']) ? $update_data['nick_name'] : str_random(6) . "-" . mt_rand(1, 999999);

        $update_data['user_picture'] = $other['user_picture'] ?? '';
        if (request()->isSecure() && !empty($update_data['user_picture'])) {
            $update_data['user_picture'] = str_replace('http://', 'https://', $update_data['user_picture']);
        }

        Users::where('user_id', $user_id)->update($update_data);

        app(UserCommonService::class)->updateUserInfo();      // 更新用户信息
        app(CartCommonService::class)->recalculatePriceCart();     // 重新计算购物车中的商品价格

        return true;
    }
}

/**
 *
 *
 * @access  public
 * @param
 *
 * @return void
 */
function logout()
{
    /* todo */
}

/**
 *  将指定user_id的密码修改为new_password。可以通过旧密码和验证字串验证修改。
 *
 * @access  public
 * @param int $user_id 用户ID
 * @param string $new_password 用户新密码
 * @param string $old_password 用户旧密码
 * @param string $code 验证码（md5($user_id . md5($password))）
 *
 * @return  boolen  $bool
 */
function edit_password($user_id, $old_password, $new_password = '', $code = '')
{
    if (empty($user_id)) {
        $GLOBALS['err']->add(lang('user.not_login'));
    }

    if ($GLOBALS['user']->edit_password($user_id, $old_password, $new_password, $code)) {
        return true;
    } else {
        $GLOBALS['err']->add(lang('user.edit_password_failure'));

        return false;
    }
}

/**
 *  会员找回密码时，对输入的用户名和邮件地址匹配
 *
 * @access  public
 * @param string $user_name 用户帐号
 * @param string $email 用户Email
 *
 * @return  boolen
 */
function check_userinfo($user_name, $email)
{
    if (empty($user_name) || empty($email)) {
        return dsc_header("Location: user.php?act=get_password\n");
    }

    /* 检测用户名和邮件地址是否匹配 */
    $user_info = $GLOBALS['user']->check_pwd_info($user_name, $email);
    if (!empty($user_info)) {
        return $user_info;
    } else {
        return false;
    }
}

/**
 *  用户进行密码找回操作时，发送一封确认邮件
 *
 * @access  public
 * @param string $uid 用户ID
 * @param string $user_name 用户帐号
 * @param string $email 用户Email
 * @param string $code key
 *
 * @return  bool|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|string
 */
function send_pwd_email($uid, $user_name, $email, $code)
{
    if (empty($uid) || empty($user_name) || empty($email) || empty($code)) {
        return dsc_header("Location: user.php?act=get_password\n");
    }

    /* 设置重置邮件模板所需要的内容信息 */
    $template = get_mail_template('send_password');
    $reset_email = $GLOBALS['dsc']->url() . 'user.php?act=get_password&uid=' . $uid . '&code=' . $code;

    $GLOBALS['smarty']->assign('user_name', $user_name);
    $GLOBALS['smarty']->assign('reset_email', $reset_email);
    $GLOBALS['smarty']->assign('shop_name', $GLOBALS['_CFG']['shop_name']);
    $GLOBALS['smarty']->assign('send_date', date('Y-m-d'));
    $GLOBALS['smarty']->assign('sent_date', date('Y-m-d'));

    $content = $GLOBALS['smarty']->fetch('str:' . $template['template_content']);

    /* 发送确认重置密码的确认邮件 */
    if (CommonRepository::sendEmail($user_name, $email, $template['template_subject'], $content, $template['is_html'])) {
        return true;
    } else {
        return false;
    }
}

/**
 * 发送激活验证邮件
 *
 * @param $user_id
 * @return bool
 */
function send_regiter_hash($user_id)
{
    /* 设置验证邮件模板所需要的内容信息 */
    $template = get_mail_template('register_validate');
    $hash = register_hash('encode', $user_id);
    $validate_email = $GLOBALS['dsc']->url() . 'user.php?act=validate_email&hash=' . $hash;

    $row = Users::where('user_id', $user_id)->select('user_name', 'email')->first();
    $row = $row ? $row->toArray() : [];

    $GLOBALS['smarty']->assign('user_name', $row['user_name']);
    $GLOBALS['smarty']->assign('validate_email', $validate_email);
    $GLOBALS['smarty']->assign('shop_name', $GLOBALS['_CFG']['shop_name']);
    $GLOBALS['smarty']->assign('send_date', TimeRepository::getLocalDate($GLOBALS['_CFG']['date_format']));

    $content = $GLOBALS['smarty']->fetch('str:' . $template['template_content']);

    /* 发送激活验证邮件 */
    if (CommonRepository::sendEmail($row['user_name'], $row['email'], $template['template_subject'], $content, $template['is_html'])) {
        return true;
    } else {
        return false;
    }
}

/**
 * 发送更改密码验证邮件
 *
 * @param $user_id
 * @param $type
 * @param int $validated
 * @return bool
 */
function send_account_safe_hash($user_id, $type, $validated = 0)
{
    /* 设置验证邮件模板所需要的内容信息 */
    $template = get_mail_template('register_validate');
    $hash = register_hash('encode', $user_id);
    $validate_email = $GLOBALS['dsc']->url() . 'user.php?act=account_safe&type=validated_email&hash=' . $hash;

    $UserRep = app(UserService::class);

    $where = [
        'user_id' => $user_id
    ];
    $row = $UserRep->userInfo($where);

    $email = $row['email'];
    switch ($type) {
        case 'change_pwd':
            $validate_email = $GLOBALS['dsc']->url() . 'user.php?act=account_safe&type=validated_email&mail_type=change_pwd&hash=' . $hash;
            break;
        case 'change_mail':
            $validate_email = $GLOBALS['dsc']->url() . 'user.php?act=account_safe&type=validated_email&mail_type=change_mail&hash=' . $hash;
            break;
        case 'change_mobile':
            $validate_email = $GLOBALS['dsc']->url() . 'user.php?act=account_safe&type=validated_email&mail_type=change_mobile&hash=' . $hash;
            break;
        case 'change_paypwd':
            $validate_email = $GLOBALS['dsc']->url() . 'user.php?act=account_safe&type=validated_email&mail_type=change_paypwd&hash=' . $hash;
            break;

        case 'editmail':

            if (!empty($validated)) {
                $validated = "&validated=1";
            } else {
                $validated = '';
            }

            $validate_email = $GLOBALS['dsc']->url() . 'user.php?act=account_safe&type=validated_email&mail_type=editmail' . $validated . '&hash=' . $hash;
            $email = session('new_email' . $user_id);
            break;

        default:
            break;
    }

    $GLOBALS['smarty']->assign('user_name', $row['user_name']);
    $GLOBALS['smarty']->assign('validate_email', $validate_email);
    $GLOBALS['smarty']->assign('shop_name', $GLOBALS['_CFG']['shop_name']);
    $GLOBALS['smarty']->assign('send_date', date($GLOBALS['_CFG']['date_format']));

    $content = $GLOBALS['smarty']->fetch('str:' . $template['template_content']);

    /* 发送激活验证邮件 */
    if (CommonRepository::sendEmail($row['user_name'], $email, $template['template_subject'], $content, $template['is_html'])) {
        return true;
    } else {
        return false;
    }
}

/**
 *  生成邮件验证hash
 *
 * @access  public
 * @param
 *
 * @return void
 */
function register_hash($operation, $key)
{
    if ($operation == 'encode') {
        $user_id = intval($key);
        $sql = "SELECT reg_time " .
            " FROM " . $GLOBALS['dsc']->table('users') .
            " WHERE user_id = '$user_id' LIMIT 1";
        $reg_time = $GLOBALS['db']->getOne($sql);

        $hash = substr(md5($user_id . $GLOBALS['_CFG']['hash_code'] . $reg_time), 16, 4);

        return base64_encode($user_id . ',' . $hash);
    } else {
        $hash = base64_decode(trim($key));
        $row = explode(',', $hash);
        if (count($row) != 2) {
            return 0;
        }
        $user_id = intval($row[0]);
        $salt = trim($row[1]);

        if ($user_id <= 0 || strlen($salt) != 4) {
            return 0;
        }

        $sql = "SELECT reg_time " .
            " FROM " . $GLOBALS['dsc']->table('users') .
            " WHERE user_id = '$user_id' LIMIT 1";
        $reg_time = $GLOBALS['db']->getOne($sql);

        $pre_salt = substr(md5($user_id . $GLOBALS['_CFG']['hash_code'] . $reg_time), 16, 4);

        if ($pre_salt == $salt) {
            return $user_id;
        } else {
            return 0;
        }
    }
}
