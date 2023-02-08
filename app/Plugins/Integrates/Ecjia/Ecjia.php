<?php

namespace App\Plugins\Integrates\Ecjia;

use App\Models\ConnectUser;
use App\Models\Users;
use App\Plugins\Integrates\Ucenter\Ucenter;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use Binaryoung\Ucenter\Facades\Ucenter as Api;
use Illuminate\Support\Facades\DB;

class Ecjia extends Ucenter
{

    /**
     * 用户登录函数
     * @param string $username
     * @param string $password
     * @param null $remember
     * @param int $is_oath
     * @return bool|void
     */
    public function login($username, $password, $remember = null, $is_oath = 1)
    {
        /**
         * 一、用户账号存在情况
         * 1、dscmall存在，ecjia不存在
         * 2、dscmall存在，ecjia存在
         * 3、dscmall不存在，ecjia不存在
         * 4、dscmall不存在，ecjia存在
         * 二、账号处理方案
         * 1、
         */

        /* 是否邮箱 */
        $is_email = CommonRepository::getMatchEmail($username);

        /* 是否手机 */
        $is_phone = is_phone_number($username);

        $username = e($username);

        $field = [];
        $fieldVal = [
            $username
        ];

        $local_user = Users::select('user_id', 'user_name', 'password', 'email', 'mobile_phone', 'ec_salt');

        if ($is_email) {
            $is_name = 0;

            $local_user = $local_user->where('email', $username);
        } elseif ($is_phone) {
            $is_name = 1;

            $local_user = $local_user->where('mobile_phone', $username);

            $field = [
                'user_name'
            ];
        } else {
            $is_name = 2;

            $local_user = $local_user->where('user_name', $username);

            $field = [
                'mobile_phone'
            ];
        }

        $local_user = BaseRepository::getToArrayFirst($local_user);

        if (empty($local_user)) {
            if ($is_name > 0 && $field) {
                $local_user = $this->check_field_name($field, $fieldVal);
            }
        }

        $mobile = $local_user['mobile_phone'] ?? '';
        $isuid = 6; // 增加 ecjia ucenter 手机号登录

        // uc远程登录失败后同步本地用户到uc
        list($uid, $uname, $pwd, $email, $repeat) = Api::uc_user_login($mobile, $password, $isuid);

        // $uid = -1 uc远程用户不存在,或者被删除
        if ($uid == -1 && !is_null($local_user)) {
            // 校验本地用户密码
            $ec_salt = collect($local_user)->get('ec_salt');
            $other = collect($local_user)->all();
            $local_password = ['password' => $password, 'ec_salt' => $ec_salt];

            $user_password = collect($local_user)->get('password');

            $verify = false;
            // 兼容原md5密码
            if (isset($user_password) && strlen($user_password) == 32) {
                // 密码匹配之后同步到uc
                if ($user_password == $this->compile_password($local_password)) {
                    $verify = true;
                }
            } else {
                // 验证hash密码
                $verify = $this->verify_password($password, $user_password);
            }

            if ($verify == true) {
                if ($this->add_user($mobile, $password, $other)) {
                    list($uid, $uname, $pwd, $email, $repeat) = Api::uc_user_login($mobile, $password, $isuid);
                }
            }
        }

        if (!empty($uid) && is_string($uid) && strlen($uid) == 32) {
            // 优先兼容 connect_user 表
            $user_id = ConnectUser::where('open_id', $uid)
                ->where('connect_code', $this->connectType())
                ->value('user_id');
            $user_id = $user_id ? $user_id : 0;

            $connect_user = Users::select('user_id', 'user_name')->where('user_id', $user_id);
            $connect_user = BaseRepository::getToArrayFirst($connect_user);

            $ec_salt = rand(1000, 9999);
            // 生成hash密码
            $password = $this->hash_password($password);

            // 首次登录或其他应用用户
            if (empty($connect_user)) {
                if (empty($local_user)) {
                    $last_ip = app(DscRepository::class)->dscIp();

                    $user_id = Users::insertGetId(
                        [
                            'user_name' => $uname,
                            'password' => $password,
                            'ec_salt' => $ec_salt,
                            'email' => $email,
                            'mobile_phone' => $mobile,
                            'reg_time' => gmtime(),
                            'last_login' => gmtime(),
                            'last_ip' => $last_ip
                        ]
                    );
                } else {
                    $user_id = $local_user['user_id'] ?? 0;
                }

                ConnectUser::insert([
                    'connect_code' => $this->connectType(),
                    'user_id' => $user_id,
                    'open_id' => $uid,
                    'create_at' => TimeRepository::getGmTime()
                ]);

            } else {

                $user_id = $local_user['user_id'] ?? 0;

                Users::where('user_id', $user_id)->update([
                    'password' => $password,
                    'ec_salt' => $ec_salt
                ]);

                $uname = $local_user['user_name'] ?? '';
            }

            if ($is_oath == 1) {
                $this->set_session($uname);
                $this->set_cookie($uname);
            }

            $this->ucdata = Api::uc_user_synlogin($uid);
            return true;
        } elseif ($uid == -1) {
            $this->error = ERR_INVALID_USERNAME;
            return false;
        } elseif ($uid == -2) {
            $this->error = ERR_INVALID_PASSWORD;
            return false;
        } else {
            return false;
        }
    }

    /**
     * 添加用户
     *
     * @param string $username
     * @param string $password
     * @param array $other
     * @param int $gender
     * @param int $bday
     * @param int $reg_date
     * @param string $md5password
     * @return bool|int
     */
    public function add_user($username, $password, $other, $gender = -1, $bday = 0, $reg_date = 0, $md5password = '')
    {
        if (empty($username)) {
            $username = e($other['mobile_phone'] ?? '');
        }

        /* 用户不能为空 */
        if (empty($username)) {
            $this->error = ERR_INVALID_USERNAME;
            return false;
        }

        // 检查用户名是否与密码相同
        if ($username == $password) {
            $this->error = ERR_PASSWORD_NOT_ALLOW;
            return false;
        }

        /* 检测用户名 */
        if ($this->check_user($username) == false) {
            $this->error = ERR_USERNAME_EXISTS;
            return false;
        }

        $uid = Api::uc_user_register($username, $password, $other['email']);
        if ($uid <= 0) {
            if ($uid == -1) {
                $this->error = ERR_INVALID_USERNAME;
                return false;
            } elseif ($uid == -2) {
                $this->error = ERR_USERNAME_NOT_ALLOW;
                return false;
            } elseif ($uid == -3) {
                $this->error = ERR_USERNAME_EXISTS;
                return false;
            } elseif ($uid == -4) {
                $this->error = ERR_INVALID_EMAIL;
                return false;
            } elseif ($uid == -5) {
                $this->error = ERR_EMAIL_NOT_ALLOW;
                return false;
            } elseif ($uid == -6) {
                $this->error = ERR_EMAIL_EXISTS;
                return false;
            } else {
                return false;
            }
        } else {

            /* 是否邮箱 */
            $is_email = CommonRepository::getMatchEmail($username);

            /* 是否手机 */
            $is_phone = is_phone_number($username);

            $field = [];
            $fieldVal = [
                $username
            ];

            $local_user = Users::whereRaw(1);

            if ($is_email) {
                $is_name = 0;

                $local_user = $local_user->where('email', $username);
            } elseif ($is_phone) {
                $is_name = 1;

                $local_user = $local_user->where('mobile_phone', $username);

                $field = [
                    'user_name'
                ];
            } else {
                $is_name = 2;

                $local_user = $local_user->where('user_name', $username);

                $field = [
                    'mobile_phone'
                ];
            }

            $local_user = BaseRepository::getToArrayFirst($local_user);

            if (empty($local_user)) {
                if ($is_name > 0 && $field) {
                    $local_user = $this->check_field_name($field, $fieldVal);
                }
            }

            if (!empty($uid) && is_string($uid) && strlen($uid) == 32) {
                // 优先兼容 connect_user 表
                $user_id = ConnectUser::where('open_id', $uid)
                    ->where('connect_code', $this->connectType())
                    ->value('user_id');
                $user_id = $user_id ? $user_id : 0;

                $connect_user = Users::select('user_id', 'user_name')->where('user_id', $user_id);
                $connect_user = BaseRepository::getToArrayFirst($connect_user);

                $ec_salt = rand(1000, 9999);
                // 生成hash密码
                $password = $this->hash_password($password);

                // 首次注册或其他应用用户
                if (empty($connect_user)) {
                    if (empty($local_user)) {
                        $last_ip = app(DscRepository::class)->dscIp();
                        $time = TimeRepository::getGmTime();

                        $user_id = Users::insertGetId(
                            [
                                'user_name' => $username,
                                'password' => $password,
                                'ec_salt' => $ec_salt,
                                'mobile_phone' => $other['mobile_phone'],
                                'email' => $other['email'],
                                'reg_time' => $time,
                                'last_login' => $time,
                                'last_ip' => $last_ip
                            ]
                        );
                    } else {
                        $user_id = $local_user['user_id'] ?? 0;
                    }

                    ConnectUser::insert([
                        'connect_code' => $this->connectType(),
                        'user_id' => $user_id,
                        'open_id' => $uid,
                        'create_at' => $time
                    ]);
                } else {

                    $user_id = $local_user['user_id'] ?? 0;

                    Users::where('user_id', $user_id)->update([
                        'password' => $password,
                        'ec_salt' => $ec_salt
                    ]);
                }

                return true;
            }

            return false;
        }
    }

    /**
     * 检查指定用户是否存在及密码是否正确
     * @param string $username
     * @param null $password
     * @return bool|int
     */
    public function check_user($username, $password = null)
    {
        $ucresult = Api::uc_user_checkname($username);
        if ($ucresult > 0) {
            // 用户名可用
            return true;
        } elseif ($ucresult == -1) {
            //echo '用户名不合法';
            $this->error = ERR_INVALID_USERNAME;
            return false;
        } elseif ($ucresult == -2) {
            //echo '包含要允许注册的词语';
            $this->error = ERR_INVALID_USERNAME;
            return false;
        } elseif ($ucresult == -3) {
            //echo '用户名已经存在';
            $this->error = ERR_USERNAME_EXISTS;
            return false;
        }
    }

    /**
     * 编辑用户信息
     * @param array $cfg
     * @param string $forget_pwd
     * @return bool|void
     */
    public function edit_user($cfg, $forget_pwd = '0')
    {
        if (isset($cfg['mobile_phone']) && $cfg['mobile_phone']) {
            $cfg['username'] = $cfg['mobile_phone'];
        }

        return parent::edit_user($cfg, $forget_pwd);
    }

    /**
     * 获取指定用户的信息
     *
     * @param string $username
     * @return array|mixed
     */
    public function get_profile_by_name($username = '')
    {
        /* 是否邮箱 */
        $is_email = CommonRepository::getMatchEmail($username);

        /* 是否手机 */
        $is_phone = is_phone_number($username);

        $username = e($username);

        $field = [];
        $fieldVal = [
            $username
        ];

        $row = Users::select('user_id', 'user_name', 'password', 'email', 'mobile_phone', 'sex', 'reg_time', 'ec_salt');

        if ($is_email) {
            $is_name = 0;

            $row = $row->where('email', $username);
        } elseif ($is_phone) {
            $is_name = 1;

            $row = $row->where('mobile_phone', $username);

            $field = [
                'user_name'
            ];
        } else {
            $is_name = 2;

            $row = $row->where('user_name', $username);

            $field = [
                'mobile_phone'
            ];
        }

        $row = BaseRepository::getToArrayFirst($row);

        if (empty($row)) {
            if ($is_name > 0 && $field) {
                $row = $this->check_field_name($field, $fieldVal);
            }
        }

        return $row;
    }

    public function get_user_info($username)
    {
        return $this->get_profile_by_name($username);
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        return parent::logout();
    }

    /**
     * 插件类型
     * @return string
     */
    protected function connectType()
    {
        return 'ecjiauc';
    }

    /**
     * 检查指定用户是否存在及密码是否正确(重载基类check_user函数，支持zc加密方法)
     *
     * @param array $field
     * @param array $val
     * @return mixed
     */
    private function check_field_name($field = [], $val = [])
    {
        $row = Users::whereRaw(1);

        foreach ($field as $k => $v) {
            $row = $row->where($v, $val[$k]);
        }

        $row = BaseRepository::getToArrayFirst($row);

        return $row;
    }
}
