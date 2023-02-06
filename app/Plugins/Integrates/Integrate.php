<?php

namespace App\Plugins\Integrates;

use App\Models\AccountLog;
use App\Models\AffiliateLog;
use App\Models\BackGoods;
use App\Models\BackOrder;
use App\Models\Baitiao;
use App\Models\BaitiaoLog;
use App\Models\BaitiaoPayLog;
use App\Models\BookingGoods;
use App\Models\Cart;
use App\Models\CollectBrand;
use App\Models\CollectGoods;
use App\Models\CollectStore;
use App\Models\Comment;
use App\Models\CommentImg;
use App\Models\CommentSeller;
use App\Models\Complaint;
use App\Models\ComplaintImg;
use App\Models\ConnectUser;
use App\Models\CouponsUser;
use App\Models\DeliveryGoods;
use App\Models\DeliveryOrder;
use App\Models\DiscussCircle;
use App\Models\Feedback;
use App\Models\GoodsHistory;
use App\Models\GoodsReport;
use App\Models\GoodsReportImg;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\OrderReturn;
use App\Models\OrderReturnExtend;
use App\Models\ReturnGoods;
use App\Models\ReturnImages;
use App\Models\Tag;
use App\Models\UserAccount;
use App\Models\UserAddress;
use App\Models\UserBank;
use App\Models\UserBonus;
use App\Models\UserOrderNum;
use App\Models\Users;
use App\Models\UsersPaypwd;
use App\Models\UsersReal;
use App\Models\UsersVatInvoicesInfo;
use App\Models\ValueCard;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscEncryptRepository;
use App\Repositories\Common\SessionRepository;
use Illuminate\Support\Facades\Hash;

/**
 * 用户整合插件基类
 * Class Integrate
 * @package App\Plugins\Integrates
 */
class Integrate
{
    /* 整合对象使用的数据库主机 */
    public $db_host = '';

    /* 整合对象使用的数据库名 */
    public $db_name = '';

    /* 整合对象使用的数据库用户名 */
    public $db_user = '';

    /* 整合对象使用的数据库密码 */
    public $db_pass = '';

    /* 整合对象数据表前缀 */
    public $prefix = '';

    /* 数据库所使用编码 */
    public $charset = '';

    /* 整合对象使用的cookie的domain */
    public $cookie_domain = '';

    /* 整合对象使用的cookie的path */
    public $cookie_path = '/';

    /* 整合对象会员表名 */
    public $user_table = '';

    /* 会员ID的字段名 */
    public $field_id = '';

    /* 会员名称的字段名 */
    public $field_name = '';

    /* 会员密码的字段名 */
    public $field_pass = '';

    /* 会员邮箱的字段名 */
    public $field_email = '';

    /* 会员手机的字段名 ecmoban模板堂 --zhuo */
    public $field_phone = '';

    /* 会员性别 */
    public $field_gender = '';

    /* 会员生日 */
    public $field_bday = '';

    /* 注册日期的字段名 */
    public $field_reg_date = '';

    /* 是否需要同步数据到商城 */
    public $need_sync = true;

    public $error = 0;

    protected $db;

    /**
     * Integrate constructor.
     * @param array $cfg
     */
    public function __construct($cfg = [])
    {
        $this->charset = isset($cfg['db_charset']) ? $cfg['db_charset'] : 'UTF8';
        $this->prefix = isset($cfg['prefix']) ? $cfg['prefix'] : '';
        $this->db_name = isset($cfg['db_name']) ? $cfg['db_name'] : '';
        $this->cookie_domain = isset($cfg['cookie_domain']) ? $cfg['cookie_domain'] : '';
        $this->cookie_path = isset($cfg['cookie_path']) ? $cfg['cookie_path'] : '/';
        $this->need_sync = true;

        /* 初始化数据库 */
        if (empty($cfg['db_host'])) {
            $this->db_name = $GLOBALS['dsc']->db_name;
            $this->prefix = $GLOBALS['dsc']->prefix;
            $this->db = &$GLOBALS['db'];
        }
    }

    /**
     * 用户登录函数
     *
     * @param $username
     * @param $password
     * @param null $remember
     * @return bool
     */
    public function login($username, $password, $remember = null)
    {
        if ($this->check_user($username, $password) > 0) {
            if ($this->need_sync) {
                $this->sync($username, $password);
            }

            $this->set_session($username);
            $this->set_cookie($username);

            return true;
        } else {
            return false;
        }
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        $this->set_cookie(); //清除cookie
        $this->set_session(); //清除session
    }

    /**
     * 添加一个新用户
     * @param $username
     * @param $password
     * @param $registerMode_info
     * @param int $gender
     * @param int $bday
     * @param int $reg_date
     * @param string $md5password
     * @return bool
     */
    public function add_user($username, $password, $registerMode_info, $gender = -1, $bday = 0, $reg_date = 0, $md5password = '')
    {
        // 检查用户名是否与密码相同
        if ($username == $password) {
            $this->error = ERR_PASSWORD_NOT_ALLOW;
            return false;
        }

        /* 将用户添加到整合方 */
        if ($this->check_user($username) > 0) {
            $this->error = ERR_USERNAME_EXISTS;
            return false;
        }

        /* 检查email是否重复 */
        if (empty($registerMode_info['register_mode']) && !empty($registerMode_info['email'])) {

            $count = Users::where('email', e($registerMode_info['email']))->count('user_id');
            if ($count > 0) {
                $this->error = ERR_EMAIL_EXISTS;
                return false;
            }
        } else {
            if (!empty($registerMode_info['mobile_phone'])) {
                $count = Users::where('mobile_phone', e($registerMode_info['mobile_phone']))->count('user_id');
                if ($count > 0) {
                    $this->error = ERR_PHONE_EXISTS;
                    return false;
                }
            }
        }

        $post_password = $this->hash_password($password);

        $other = [
            'user_name' => $username,
            'password' => $post_password,
            'mobile_phone' => $registerMode_info['mobile_phone'],
            'email' => $registerMode_info['email']
        ];
        $user_id = Users::insertGetId($other);

        if ($user_id > 0) {
            if ($this->need_sync) {
                $this->sync($username, $password);
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * 编辑用户信息($password, $email, $gender, $bday)
     * @param $cfg
     * @param string $forget_pwd
     * @return bool
     */
    public function edit_user($cfg, $forget_pwd = '0')
    {
        if (empty($cfg['username'])) {
            return false;
        } else {
            $cfg['post_username'] = $cfg['username'];
        }

        // 检查用户名是否与密码相同
        if ($cfg['username'] == $cfg['password']) {
            $this->error = ERR_PASSWORD_NOT_ALLOW;
            return false;
        }

        $values = [];
        if ((!empty($cfg['password'])) && $this->field_pass != 'NULL') {
            $values['password'] = $this->hash_password($cfg['password']);
            $values['ec_salt'] = 0;
        }

        if ((!empty($cfg['email'])) && $this->field_email != 'NULL') {
            /* 检查email是否重复 */
            $count = Users::where('email', e($cfg['email']))
                ->where('user_id', '<>', e($cfg['user_id']))
                ->count('user_id');

            if ($count > 0) {
                $this->error = ERR_EMAIL_EXISTS;

                return false;
            }

            // 检查是否为新E-mail
            $emailCount = Users::where('email', e($cfg['email']))->count('user_id');

            if ($emailCount == 0) {
                // 新的E-mail
                Users::where('user_name', e($cfg['post_username']))->update([
                    'is_validated' => 0
                ]);
            }
            $values['email'] = e($cfg['email']);
        }

        if ((!empty($cfg['mobile_phone'])) && $this->field_phone != 'NULL') {
            /* 检查mobile_phone是否重复 */
            $count = Users::where('mobile_phone', e($cfg['mobile_phone']))
                ->where('user_id', '<>', e($cfg['user_id']))
                ->count('user_id');

            if ($count > 0) {
                $this->error = ERR_PHONE_EXISTS;

                return false;
            }

            $values['mobile_phone'] = e($cfg['mobile_phone']);
        }

        if (isset($cfg['gender']) && $this->field_gender != 'NULL') {
            $values['sex'] = e($cfg['gender']);
        }

        if ((!empty($cfg['bday'])) && $this->field_bday != 'NULL') {
            $values['birthday'] = e($cfg['bday']);
        }

        if ($values) {
            Users::where('user_id', e($cfg['user_id']))->update($values);

            if ($this->need_sync) {
                $this->sync($cfg['username'], $cfg['password']);
            }
        }

        /* 判断是否检验原始密码 */
        if (isset($cfg['old_password']) && !empty($cfg['old_password']) && !empty($cfg['post_username'])) {
            $count = Users::where('user_name', e($cfg['post_username']))
                ->where('password', e($cfg['old_password']))
                ->count('user_id');

            if ($count > 0) {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * 删除用户
     * @param $post_id
     */
    public function remove_user($post_id)
    {
        $post_id = BaseRepository::getExplode($post_id);
        $post_id = DscEncryptRepository::filterValInt($post_id, false);

        if ($this->need_sync || (isset($this->is_dscmall) && $this->is_dscmall)) {

            /* 如果需要同步或是dscmall插件执行这部分代码 */
            $col = Users::query()->select('user_id')->whereIn('user_name', $post_id)->pluck('user_id');
            $col = BaseRepository::getToArray($col);

            if ($col) {

                Users::whereIn('parent_id', $col)->update(['parent_id' => 0]);

                Users::whereIn('user_id', $col)->delete();
                UsersReal::whereIn('user_id', $col)->delete();

                /* 删除用户订单 */
                OrderInfo::whereIn('user_id', $col)->delete();
                OrderGoods::whereIn('user_id', $col)->delete();

                // 删除用户退换货订单
                $col_ret_id = OrderReturn::whereIn('user_id', $col)->pluck('ret_id');
                $col_ret_id = BaseRepository::getToArray($col_ret_id);
                if ($col_ret_id) {
                    OrderReturn::whereIn('ret_id', $col_ret_id)->delete();
                    OrderReturnExtend::whereIn('ret_id', $col_ret_id)->delete();
                    ReturnGoods::whereIn('ret_id', $col_ret_id)->delete();
                }
                ReturnImages::whereIn('user_id', $col)->delete();

                // 删除用户订单统计信息
                UserOrderNum::whereIn('user_id', $col)->delete();

                // 删除用户发货单
                $col_delivery_id = DeliveryOrder::whereIn('user_id', $col)->pluck('delivery_id');
                $col_delivery_id = BaseRepository::getToArray($col_delivery_id);
                if ($col_delivery_id) {
                    DeliveryOrder::whereIn('delivery_id', $col_delivery_id)->delete();
                    DeliveryGoods::whereIn('delivery_id', $col_delivery_id)->delete();
                }

                // 删除用户退货单
                $col_back_id = BackOrder::whereIn('user_id', $col)->pluck('back_id');
                $col_back_id = BaseRepository::getToArray($col_back_id);
                if ($col_back_id) {
                    BackOrder::whereIn('back_id', $col_back_id)->delete();
                    BackGoods::whereIn('back_id', $col_back_id)->delete();
                }
                // 删除用户购物车信息
                if (!empty($col)) {
                    Cart::whereIn('user_id', $col)->delete();
                }

                /* 删除用户白条 */
                $col_baitiao_id = Baitiao::query()->select('baitiao_id')->whereIn('user_id', $col)->pluck('baitiao_id');
                $col_baitiao_id = BaseRepository::getToArray($col_baitiao_id);

                if ($col_baitiao_id) {
                    BaitiaoLog::whereIn('baitiao_id', $col_baitiao_id)->delete();
                    BaitiaoPayLog::whereIn('baitiao_id', $col_baitiao_id)->delete();
                }

                BookingGoods::whereIn('user_id', $col)->delete(); //删除缺货登记
                CollectGoods::whereIn('user_id', $col)->delete(); //删除会员收藏商品
                CollectStore::whereIn('user_id', $col)->delete(); //删除会员收藏店铺
                CollectBrand::whereIn('user_id', $col)->delete(); //删除会员收藏品牌
                Feedback::whereIn('user_id', $col)->delete(); //删除用户留言
                UserAddress::whereIn('user_id', $col)->delete(); //删除用户地址
                UserBonus::whereIn('user_id', $col)->delete(); //删除用户红包
                UserAccount::whereIn('user_id', $col)->delete(); //删除用户帐号金额
                Tag::whereIn('user_id', $col)->delete(); //删除用户标记
                AccountLog::whereIn('user_id', $col)->delete(); //删除用户日志
                ValueCard::whereIn('user_id', $col)->delete(); //删除用户储值卡
                CouponsUser::where('is_delete', 0)->whereIn('user_id', $col)->delete(); //删除用户优惠券
                UsersVatInvoicesInfo::whereIn('user_id', $col)->delete(); //删除用户发票信息
                UserBank::whereIn('user_id', $col)->delete(); //删除用户银行卡
                UsersPaypwd::whereIn('user_id', $col)->delete(); //删除用户支付密码
                UsersReal::whereIn('user_id', $col)->delete(); //删除用户实名认证信息
                AffiliateLog::whereIn('user_id', $col)->delete(); //删除用户推荐信息
                GoodsHistory::whereIn('user_id', $col)->delete(); //删除用户访问商品记录信息
                //删除用户评论信息
                Comment::whereIn('user_id', $col)->delete();
                CommentImg::whereIn('user_id', $col)->delete(); //删除用户评论图片信息
                CommentSeller::whereIn('user_id', $col)->delete(); //删除用户评论星级信息
                DiscussCircle::whereIn('user_id', $col)->delete(); //删除用户评论星级信息
                // 删除用户交易纠纷信息
                Complaint::whereIn('user_id', $col)->delete();
                ComplaintImg::whereIn('user_id', $col)->delete();
                // 删除用户商品举报
                GoodsReport::whereIn('user_id', $col)->delete();
                GoodsReportImg::whereIn('user_id', $col)->delete();

                // 用户删除事件
                event(new \App\Events\UserRemoveEvent($col));

                //删除第三方会员绑定表
                ConnectUser::whereIn('user_id', $col)->delete();
            }
        }

        if (isset($this->dscmall) && $this->dscmall) {
            /* 如果是dscmall插件直接退出 */
            return;
        }

        Users::whereIn('user_name', $post_id)->delete();
    }

    /**
     * 获取指定用户的信息
     * @param $username
     * @return mixed
     */
    public function get_profile_by_name($username = '')
    {
        if (empty($username)) {
            return [];
        }

        $username = addslashes($username);

        $user = Users::select('user_id', 'user_name', 'email', 'sex', 'birthday', 'reg_time', 'password')
            ->where('user_name', $username);
        $user = BaseRepository::getToArrayFirst($user);

        return $user;
    }

    /**
     * 获取指定用户的信息
     * @param $id
     * @return mixed
     */
    public function get_profile_by_id($id = 0)
    {
        $id = (int)$id;

        $row = Users::select('user_id', 'user_name', 'email', 'sex', 'birthday', 'reg_time', 'password')
            ->where('user_id', $id);
        $row = BaseRepository::getToArrayFirst($row);

        return $row;
    }

    /**
     * 根据登录状态设置cookie
     * @return bool
     */
    public function get_cookie()
    {
        $id = $this->check_cookie();
        if ($id) {
            if ($this->need_sync) {
                $this->sync($id);
            }
            $this->set_session($id);

            return true;
        } else {
            return false;
        }
    }

    /**
     * 检查指定用户是否存在及密码是否正确
     * @param $username
     * @param string $password
     * @return mixed
     */
    public function check_user($username, $password = '')
    {
        /* 是否邮箱 */
        $is_email = CommonRepository::getMatchEmail($username);

        /* 是否手机 */
        $is_phone = CommonRepository::getMatchPhone($username);

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
            return false;
        }

        // 兼容原md5密码
        if (isset($row['password']) && strlen($row['password']) == 32) {
            if ($row['password'] == $this->compile_password(['password' => $password])) {
                return true;
            }
        } else {
            // 验证hash密码
            return $this->verify_password($password, $row['password'] ?? '');
        }

        return false;
    }

    /**
     * 检查指定邮箱是否存在
     * @param $email
     * @return bool
     */
    public function check_email($email = '')
    {
        if (!empty($email)) {

            $email = addslashes($email);

            /* 检查email是否重复 */
            $count = Users::where('email', e($email))->count('user_id');
            if ($count > 0) {
                $this->error = ERR_EMAIL_EXISTS;
                return true;
            }
            return false;
        }
    }

    /**
     * 检查指定手机是否存在
     * @param $phone
     * @return bool
     */
    public function check_mobile_phone($phone = '')
    {
        if (!empty($phone)) {

            $phone = (string)$phone;

            /* 检查mobile_phone是否重复 */
            $count = Users::where('mobile_phone', $phone)->count('user_id');

            if ($count > 0) {
                $this->error = ERR_PHONE_EXISTS;
                return true;
            }

            return false;
        }
    }

    /**
     * 检查cookie是正确，返回用户名
     * @return string
     */
    public function check_cookie()
    {
        return '';
    }

    /**
     * 设置cookie
     *
     * @param string $username
     * @return array|null|string
     */
    public function set_cookie($username = '')
    {
        //防止user_name一样，造成串号

        if (empty($username)) {
            /* 摧毁cookie */
            $cookieList = [
                'user_id',
                'username',
                'nick_name',
                'password'
            ];
            app(SessionRepository::class)->destroy_cookie($cookieList);
        } else {
            /* 是否邮箱 */
            $is_email = CommonRepository::getMatchEmail($username);

            /* 是否手机 */
            $is_phone = CommonRepository::getMatchPhone($username);

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

            if ($row) {
                return request()->cookie();
            }
        }
    }

    /**
     * 设置指定用户SESSION
     *
     * @param string $username
     */
    public function set_session($username = '')
    {
        if (empty($username)) {
            $sessionList = [
                'user_id',
                'user_name',
                'nick_name',
                'email',
                'user_rank',
                'discount'
            ];
            app(SessionRepository::class)->destroy_session($sessionList);
        } else {
            /* 是否邮箱 */
            $is_email = CommonRepository::getMatchEmail($username);

            /* 是否手机 */
            $is_phone = CommonRepository::getMatchPhone($username);

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

            if ($row) {
                session([
                    'user_id' => $row['user_id'],
                    'user_name' => stripslashes($row['user_name']),
                    'nick_name' => stripslashes($row['nick_name']),
                    'email' => $row['email']
                ]);
            }
        }
    }

    /**
     * 在给定的表名前加上数据库名以及前缀
     * @param $str
     * @return string
     */
    public function table($str)
    {
        return '`' . $this->db_name . '`.`' . $this->prefix . $str . '`';
    }

    /**
     * 编译密码函数
     * @param array $cfg 包含参数为 $password, $md5password, $salt, $type
     * @return string
     */
    public function compile_password($cfg)
    {
        if (isset($cfg['password'])) {
            $cfg['md5password'] = md5($cfg['password']);
        }
        if (empty($cfg['type'])) {
            $cfg['type'] = PWD_MD5;
        }

        switch ($cfg['type']) {
            case PWD_MD5:
                if (!empty($cfg['ec_salt'])) {
                    return md5($cfg['md5password'] . $cfg['ec_salt']);
                } else {
                    return $cfg['md5password'];
                }

            // no break
            case PWD_PRE_SALT:
                if (empty($cfg['salt'])) {
                    $cfg['salt'] = '';
                }

                return md5($cfg['salt'] . $cfg['md5password']);

            case PWD_SUF_SALT:
                if (empty($cfg['salt'])) {
                    $cfg['salt'] = '';
                }

                return md5($cfg['md5password'] . $cfg['salt']);

            default:
                return '';
        }
    }

    /**
     * 会员同步
     * @param $username
     * @param string $password
     * @param string $md5password
     * @return bool
     */
    public function sync($username, $password = '', $md5password = '')
    {
        if ((!empty($password)) && empty($md5password)) {
            $md5password = $this->hash_password($password);
        }

        $main_profile = $this->get_profile_by_name($username);

        if (empty($main_profile)) {
            return false;
        }

        $profile = Users::select('user_name', 'email', 'password', 'sex', 'birthday')
            ->where('user_name', e($username));
        $profile = BaseRepository::getToArrayFirst($profile);

        if (empty($profile)) {
            /* 向商城表插入一条新记录 */
            if (empty($md5password)) {
                $other = [
                    'user_name' => e($username),
                    'email' => $main_profile['email'],
                    'sex' => $main_profile['sex'],
                    'birthday' => $main_profile['birthday'],
                    'reg_time' => $main_profile['reg_time']
                ];
            } else {
                $other = [
                    'user_name' => e($username),
                    'email' => $main_profile['email'],
                    'sex' => $main_profile['sex'],
                    'birthday' => $main_profile['birthday'],
                    'reg_time' => $main_profile['reg_time'],
                    'password' => $md5password
                ];
            }

            Users::insert($other);

            return true;
        } else {
            $values = [];
            if ($main_profile['email'] != $profile['email']) {
                $values['email'] = $main_profile['email'];
            }
            if ($main_profile['sex'] != $profile['sex']) {
                $values['sex'] = $main_profile['sex'];
            }
            if ($main_profile['birthday'] != $profile['birthday']) {
                $values['birthday'] = $main_profile['birthday'];
            }
            if ((!empty($md5password)) && ($md5password != $profile['password'])) {
                $values['password'] = $md5password;
            }

            if (empty($values)) {
                return true;
            } else {

                Users::where('user_name', e($username))->update($values);

                return true;
            }
        }
    }

    /**
     * 获取论坛有效积分及单位
     * @return array
     */
    public function get_points_name()
    {
        return [];
    }

    /**
     * 获取用户积分
     * @param $username
     * @return bool
     */
    public function get_points($username)
    {
        $credits = $this->get_points_name();
        $fileds = array_keys($credits);
        if ($fileds) {

            $fileds = BaseRepository::getArrayPush($fileds, 'user_id');

            $row = Users::select($fileds)->where('user_name', e($username));
            $row = BaseRepository::getToArrayFirst($row);

            return $row;
        } else {
            return false;
        }
    }

    /**
     * 设置用户积分
     * @param $username
     * @param $credits
     * @return bool
     */
    public function set_points($username, $credits)
    {
        $user_set = array_keys($credits);
        $points_set = array_keys($this->get_points_name());

        $set = array_intersect($user_set, $points_set);

        if ($set) {
            $tmp = [];
            foreach ($set as $credit) {
                $tmp[$credit] = $credit . '+' . $credits[$credit];
            }

            $tmp = BaseRepository::getDbRaw($tmp);
            Users::where('user_name', e($username))->update($tmp);
        }

        return true;
    }

    /**
     * 获取用户信息
     * @param $username
     * @return mixed
     */
    public function get_user_info($username)
    {
        return $this->get_profile_by_name($username);
    }

    /**
     * 检查有无重名用户，有则返回重名用户
     * @param $user_list
     * @return array
     */
    public function test_conflict($user_list)
    {
        if (empty($user_list)) {
            return [];
        }

        $user_list = BaseRepository::getExplode($user_list);

        $list = Users::query()->select('user_name')->whereIn('user_name', $user_list)
            ->pluck('user_name');
        $list = BaseRepository::getToArray($list);

        return $list;
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

    /**
     * 密码hash
     * @param string $password
     * @return string
     */
    public static function hash_password($password = '')
    {
        return Hash::make($password);
    }

    /**
     * 验证 hash
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function verify_password($password = '', $hash = '')
    {
        if (empty($password) || empty($hash)) {
            return false;
        }

        if (Hash::check($password, $hash)) {
            // 密码匹配
            return true;
        }

        return false;
    }
}
