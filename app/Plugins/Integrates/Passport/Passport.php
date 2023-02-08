<?php

namespace App\Plugins\Integrates\Passport;

use App\Models\Users;
use App\Plugins\Integrates\Integrate;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;

/**
 * Class Passport
 * @package App\Plugins\Integrates\Passport
 */
class Passport extends Integrate
{
    public $is_dscmall = 1;

    /**
     * Passport constructor.
     * @param array $cfg
     */
    public function __construct($cfg = [])
    {
        parent::__construct();
        $this->user_table = 'users';
        $this->field_id = 'user_id';
        $this->ec_salt = 'ec_salt';
        $this->field_name = 'user_name';
        $this->field_pass = 'password';
        $this->field_email = 'email';
        $this->field_phone = 'mobile_phone';
        $this->field_gender = 'sex';
        $this->field_bday = 'birthday';
        $this->field_reg_date = 'reg_time';
        $this->need_sync = false;
        $this->is_dscmall = 1;
    }

    /**
     * 检查指定用户是否存在及密码是否正确(重载基类check_user函数，支持zc加密方法)
     * @param $username
     * @param string $password
     * @return int|mixed
     */
    public function check_user($username, $password = '')
    {
        if ($this->charset != 'UTF8') {
            $username = dsc_iconv('UTF8', $this->charset, $username);
        }

        /* 是否邮箱 */
        $is_email = CommonRepository::getMatchEmail($username);

        /* 是否手机 */
        $is_phone = is_phone_number($username);

        $username = e($username);

        $field = [];
        $fieldVal = [
            $username
        ];

        $row = Users::whereRaw(1);

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

        if (empty($row)) {
            return 0;
        }

        // 兼容原md5密码
        if (isset($row['password']) && strlen($row['password']) == 32) {
            if (empty($row['salt'])) {
                if (!empty($password) && ($row['password'] != $this->compile_password(['password' => $password, 'ec_salt' => $row['ec_salt']]))) {
                    return 0;
                } else {

                    if (empty($row['ec_salt'])) {
                        $ec_salt = rand(1, 9999);
                        $new_password = $this->hash_password($password);

                        Users::where('user_id', $row['user_id'])->update([
                            'password' => $new_password,
                            'ec_salt' => $ec_salt
                        ]);
                    }

                    return $row['user_id'];
                }
            } else {
                /* 如果salt存在，使用salt方式加密验证，验证通过洗白用户密码 */
                $encrypt_type = substr($row['salt'], 0, 1);
                $encrypt_salt = substr($row['salt'], 1);

                /* 计算加密后密码 */
                switch ($encrypt_type) {
                    case ENCRYPT_ZC:
                        $encrypt_password = md5($encrypt_salt . $password);
                        break;

                    case ENCRYPT_UC:
                        $encrypt_password = md5(md5($password) . $encrypt_salt);
                        break;

                    default:
                        $encrypt_password = '';
                }

                if (!empty($password) && ($row['password'] != $encrypt_password)) {
                    return 0;
                }

                $new_password = $this->hash_password($password);
                Users::where('user_id', $row['user_id'])->update([
                    'password' => $new_password,
                    'salt' => ''
                ]);

                return $row['user_id'];
            }
        } else {

            // 验证hash 密码
            $verify = $this->verify_password($password, $row['password']);

            if ($verify == true) {
                return $row['user_id'];
            }

            return 0;
        }
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
