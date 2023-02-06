<?php

namespace App\Services\User;

use App\Libraries\Error;
use App\Models\Users;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Cart\CartCommonService;

/**
 * Class UserLoginRegisterService
 * @package App\Services\User
 */
class UserLoginRegisterService
{
    protected $cartCommonService;
    protected $userCommonService;
    protected $error;

    public function __construct(
        CartCommonService $cartCommonService,
        UserCommonService $userCommonService,
        Error $error
    )
    {
        $this->cartCommonService = $cartCommonService;
        $this->userCommonService = $userCommonService;
        $this->error = $error;
    }

    /**
     * 用户注册
     *
     * @param string $username 注册用户名
     * @param string $password 用户密码
     * @param string $email 注册email
     * @param array $other 注册的其他信息
     * @return bool
     * @throws \Exception
     */
    public function register($username = '', $password = '', $email = '', $other = [])
    {
        /* 检查注册是否关闭 */
        if (!empty(config('shop.shop_reg_closed', 0))) {
            $this->error->add(lang('common.shop_register_closed'));
            return false;
        }
        /* 检查username */
        if (empty($username)) {
            $this->error->add(lang('user.username_empty'));
            return false;
        } else {
            if (preg_match('/\'\/^\\s*$|^c:\\\\con\\\\con$|[%,\\*\\"\\s\\t\\<\\>\\&\'\\\\]/', $username)) {
                $this->error->add(sprintf(lang('user.username_invalid'), htmlspecialchars($username)));
                return false;
            }
        }

        /* 检查email */
        if (!empty($email)) {
            if (!CommonRepository::getMatchEmail($email)) {
                $this->error->add(sprintf(lang('user.email_invalid'), htmlspecialchars($email)));
                return false;
            }
        }

        if ($GLOBALS['err']->error_no() > 0) {
            return false;
        }

        $other['is_validated'] = isset($other['is_validated']) ? $other['is_validated'] : 0;

        //用户注册方式信息
        $user_registerMode = ['email' => $email, 'mobile_phone' => $other['mobile_phone'], 'is_validated' => $other['is_validated']];

        if (!$GLOBALS['user']->add_user($username, $password, $user_registerMode)) {
            if ($GLOBALS['user']->error == ERR_INVALID_USERNAME) {
                $this->error->add(sprintf(lang('user.username_invalid'), $username));
            } elseif ($GLOBALS['user']->error == ERR_USERNAME_NOT_ALLOW) {
                $this->error->add(sprintf(lang('user.username_not_allow'), $username));
            } elseif ($GLOBALS['user']->error == ERR_USERNAME_EXISTS) {
                $this->error->add(sprintf(lang('user.username_exist'), $username));
            } elseif ($GLOBALS['user']->error == ERR_INVALID_EMAIL) {
                $this->error->add(sprintf(lang('user.email_invalid'), $email));
            } elseif ($GLOBALS['user']->error == ERR_EMAIL_NOT_ALLOW) {
                $this->error->add(sprintf(lang('user.email_not_allow'), $email));
            } elseif ($GLOBALS['user']->error == ERR_EMAIL_EXISTS) {
                $this->error->add(sprintf(lang('user.email_exist'), $email));
            } elseif ($GLOBALS['user']->error == ERR_PASSWORD_NOT_ALLOW) {
                $this->error->add(lang('user.user_pass_same'));
            } elseif ($GLOBALS['user']->error == ERR_PHONE_EXISTS) {
                $this->error->add(lang('user.msg_phone_registered'));
            } else {
                $this->error->add('UNKNOWN ERROR!');
            }

            // 注册失败
            return false;
        } else {
            // 注册成功
            $user = $this->userCommonService->getUserByName($username);
            $user = $user ? $user->toArray() : [];

            $user_id = $user['user_id'];

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
            $update_data['reg_time'] = TimeRepository::getLocalStrtoTime(TimeRepository::getLocalDate('Y-m-d H:i:s'));
            if ($other) {
                $date = $other;
                foreach ($date as $key => $val) {
                    //删除非法key值
                    if (!in_array($key, $other_key_array)) {
                        unset($date[$key]);
                    } else {
                        $date[$key] = e(trim($val)); //防止用户输入javascript代码
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

            $this->userCommonService->updateUserInfo($user_id, 'mobile');      // 更新用户信息
            $this->cartCommonService->recalculatePriceMobileCart($user_id);     // 重新计算购物车中的商品价格

            return true;
        }
    }

    /**
     * 返回错误
     * @return Error
     */
    public function getError()
    {
        return $this->error;
    }
}
