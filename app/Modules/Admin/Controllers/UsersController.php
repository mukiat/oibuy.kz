<?php

namespace App\Modules\Admin\Controllers;

use App\Jobs\Export\ProcessUserExport;
use App\Models\UserMembershipCard;
use App\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Rules\PasswordRule;
use App\Rules\UserName;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Order\OrderService;
use App\Services\Store\StoreCommonService;
use App\Services\User\UserCommonService;
use App\Services\User\UserManageService;
use App\Services\User\UserRankService;
use App\Services\User\UserService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * 会员管理程序
 */
class UsersController extends InitController
{
    protected $orderService;
    protected $merchantCommonService;
    protected $dscRepository;
    protected $storeCommonService;
    protected $userRankService;
    protected $userCommonService;
    protected $userService;
    protected $commonRepository;
    protected $userManageService;

    public function __construct(
        OrderService $orderService,
        MerchantCommonService $merchantCommonService,
        DscRepository $dscRepository,
        StoreCommonService $storeCommonService,
        UserRankService $userRankService,
        UserCommonService $userCommonService,
        UserService $userService,
        CommonRepository $commonRepository,
        UserManageService $userManageService
    )
    {
        $this->orderService = $orderService;
        $this->merchantCommonService = $merchantCommonService;
        $this->dscRepository = $dscRepository;
        $this->storeCommonService = $storeCommonService;
        $this->userRankService = $userRankService;
        $this->userCommonService = $userCommonService;
        $this->userService = $userService;
        $this->commonRepository = $commonRepository;
        $this->userManageService = $userManageService;
    }

    public function index()
    {

        //ecmoban模板堂 --zhuo start
        $adminru = get_admin_ru_id();
        if ($adminru['ru_id'] == 0) {
            $this->smarty->assign('priv_ru', 1);
        } else {
            $this->smarty->assign('priv_ru', 0);
        }
        //ecmoban模板堂 --zhuo end

        $act = e(request()->input('act', ''));

        /*------------------------------------------------------ */
        //-- 用户帐号列表
        /*------------------------------------------------------ */

        if ($act == 'list') {
            /* 检查权限 */
            admin_priv('users_manage');

            $this->smarty->assign('menu_select', ['action' => '08_members', 'current' => '03_users_list']);

            $rs = $this->userRankService->getUserRankList();

            $ranks = [];
            foreach ($rs as $row) {
                $ranks[$row['rank_id']] = $row['rank_name'];
            }
            $this->smarty->assign('user_ranks', $ranks);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['03_users_list']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['04_users_add'], 'href' => 'users.php?act=add']);

            // 会员导出（队列）
            $current_url = urlencode(request()->getRequestUri());
            $this->smarty->assign('action_link2', ['text' => $GLOBALS['_LANG']['12_users_export'], 'href' => 'javascript:void(0);']);
            $this->smarty->assign('action_link3', ['text' => trans('admin/order_export.view_export_records'), 'href' => route('admin/export_history', ['type' => 'user', 'callback' => $current_url])]);

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            $user_list = $this->userManageService->user_list($adminru);

            $this->smarty->assign('user_list', $user_list['user_list']);
            $this->smarty->assign('filter', $user_list['filter']);
            $this->smarty->assign('record_count', $user_list['record_count']);
            $this->smarty->assign('page_count', $user_list['page_count']);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('sort_user_id', '<img src="' . __TPL__ . '/images/sort_desc.gif">');

            $this->smarty->assign('current_url', $current_url);

            return $this->smarty->display('users_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- ajax返回用户列表
        /*------------------------------------------------------ */
        elseif ($act == 'query') {

            $user_list = $this->userManageService->user_list($adminru);

            $this->smarty->assign('user_list', $user_list['user_list']);
            $this->smarty->assign('filter', $user_list['filter']);
            $this->smarty->assign('record_count', $user_list['record_count']);
            $this->smarty->assign('page_count', $user_list['page_count']);

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            $sort_flag = sort_flag($user_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result($this->smarty->fetch('users_list.dwt'), '', ['filter' => $user_list['filter'], 'page_count' => $user_list['page_count']]);
        }

        /*------------------------------------------------------ */
        //-- 添加会员帐号
        /*------------------------------------------------------ */
        elseif ($act == 'add') {
            /* 检查权限 */
            admin_priv('users_manage');

            $user = ['rank_points' => $GLOBALS['_CFG']['register_points'],
                'pay_points' => $GLOBALS['_CFG']['register_points'],
                'sex' => 0,
                'credit_line' => 0
            ];
            /* 取出注册扩展字段 */
            $sql = 'SELECT * FROM ' . $this->dsc->table('reg_fields') . ' WHERE type < 2 AND display = 1 ORDER BY dis_order, id';
            $extend_info_list = $this->db->getAll($sql);
            $this->smarty->assign('extend_info_list', $extend_info_list);
            /* 密码提示问题 */
            $this->smarty->assign('passwd_questions', $GLOBALS['_LANG']['passwd_questions']);

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['04_users_add']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['11_users_add'], 'href' => 'mc_user.php']);
            $this->smarty->assign('action_link2', ['text' => $GLOBALS['_LANG']['03_users_list'], 'href' => 'users.php?act=list' . '&' . list_link_postfix()]);
            $this->smarty->assign('form_action', 'insert');
            $this->smarty->assign('user', $user);
            $this->smarty->assign('special_ranks', $this->userRankService->getUserRank(['special_rank' => 1, 'membership_card_display' => 'hide']));

            /*获取从1956年到先前的年月日数组*/
            $select_date = [];
            $select_date['year'] = range(1956, date('Y'));
            $select_date['month'] = range(1, 12);
            $select_date['day'] = range(1, 31);
            $this->smarty->assign("select_date", $select_date);

            return $this->smarty->display('user_add.dwt');
        }

        /*------------------------------------------------------ */
        //-- 添加会员帐号
        /*------------------------------------------------------ */
        elseif ($act == 'insert') {
            /* 检查权限 */
            admin_priv('users_manage');

            $username = empty($_POST['username']) ? '' : trim($_POST['username']);
            $password = empty($_POST['password']) ? '' : trim($_POST['password']);
            $email = empty($_POST['email']) ? '' : trim($_POST['email']);
            $sex = empty($_POST['sex']) ? 0 : intval($_POST['sex']);
            $sex = in_array($sex, [0, 1, 2]) ? $sex : 0;
            $birthday = $_POST['birthdayYear'] . '-' . $_POST['birthdayMonth'] . '-' . $_POST['birthdayDay'];
            $rank = empty($_POST['user_rank']) ? 0 : intval($_POST['user_rank']);
            $credit_line = empty($_POST['credit_line']) ? 0 : floatval($_POST['credit_line']);
            $extend_field5 = empty($_POST['extend_field5']) ? 0 : htmlspecialchars(trim($_POST['extend_field5']));
            $user_registerMode = [
                'email' => $email,
                'register_mode' => 0,
                'mobile_phone' => isset($extend_field5) ? $extend_field5 : '',
            ];
            $sel_question = empty($_POST['sel_question']) ? '' : compile_str($_POST['sel_question']);
            $passwd_answer = isset($_POST['passwd_answer']) ? compile_str(trim($_POST['passwd_answer'])) : '';

            // 数据验证
            $validator = Validator::make(request()->all(), [
                'username' => ['required', 'string', new UserName()],
                'password' => ['required', 'different:username', new PasswordRule()], // 密码
            ], [
                'password.required' => lang('user.user_pass_empty'),
                'password.different' => lang('user.user_pass_same')
            ]);

            // 返回错误
            if ($validator->fails()) {
                return sys_msg($validator->errors()->first(), 1);
            }

            //验证手机号是否存在用户名
            $useRow = [];
            if (!empty($username)) {
                $useRow = $this->userManageService->exitUser($username);
            }

            if (!empty($useRow)) {
                return sys_msg($GLOBALS['_LANG']['user_name_exits'], 1);
            }

            //验证手机号是否存在
            if (!empty($user_registerMode['mobile_phone'])) {
                $useRow = $this->userManageService->exitUser($user_registerMode['mobile_phone']);
            }

            if (!empty($useRow)) {
                return sys_msg(lang('admin/users.iphone_exist'), 1);
            }

            $users = init_users();

            if (!$users->add_user($username, $password, $user_registerMode)) {

                /* 插入会员数据失败 */
                if ($users->error == ERR_INVALID_USERNAME) {
                    $msg = $GLOBALS['_LANG']['username_invalid'];
                } elseif ($users->error == ERR_USERNAME_NOT_ALLOW) {
                    $msg = $GLOBALS['_LANG']['username_not_allow'];
                } elseif ($users->error == ERR_USERNAME_EXISTS) {
                    $msg = $GLOBALS['_LANG']['username_exists'];
                } elseif ($users->error == ERR_INVALID_EMAIL) {
                    $msg = $GLOBALS['_LANG']['email_invalid'];
                } elseif ($users->error == ERR_EMAIL_NOT_ALLOW) {
                    $msg = $GLOBALS['_LANG']['email_not_allow'];
                } elseif ($users->error == ERR_EMAIL_EXISTS) {
                    $msg = $GLOBALS['_LANG']['email_exists'];
                } elseif ($users->error == ERR_PASSWORD_NOT_ALLOW) {
                    $msg = lang('user.user_pass_same');
                } else {
                    $msg = 'UNKNOWN ERROR!';
                }
                return sys_msg($msg, 1);
            }

            /* 注册送积分 */
            if (!empty($GLOBALS['_CFG']['register_points'])) {
                log_account_change(session('user_id'), 0, 0, $GLOBALS['_CFG']['register_points'], $GLOBALS['_CFG']['register_points'], $GLOBALS['_LANG']['register_points']);
            }

            /*把新注册用户的扩展信息插入数据库*/
            $sql = 'SELECT id FROM ' . $this->dsc->table('reg_fields') . ' WHERE type = 0 AND display = 1 ORDER BY dis_order, id';   //读出所有扩展字段的id
            $fields_arr = $this->db->getAll($sql);

            $extend_field_str = '';    //生成扩展字段的内容字符串
            $user_id_arr = $users->get_profile_by_name($username);
            foreach ($fields_arr as $val) {
                $extend_field_index = 'extend_field' . $val['id'];
                if (!empty($_POST[$extend_field_index])) {
                    $temp_field_content = strlen($_POST[$extend_field_index]) > 100 ? mb_substr($_POST[$extend_field_index], 0, 99) : $_POST[$extend_field_index];
                    $extend_field_str .= " ('" . $user_id_arr['user_id'] . "', '" . $val['id'] . "', '" . $temp_field_content . "'),";
                }
            }
            $extend_field_str = substr($extend_field_str, 0, -1);

            if ($extend_field_str) {      //插入注册扩展数据
                $sql = 'INSERT INTO ' . $this->dsc->table('reg_extend_info') . ' (`user_id`, `reg_field_id`, `content`) VALUES' . $extend_field_str;
                $this->db->query($sql);
            }

            /* 更新会员的其它信息 */
            $other = [];
            $other['credit_line'] = $credit_line;
            $other['user_rank'] = $rank;
            $other['sex'] = $sex;
            $other['birthday'] = $birthday;
            $other['reg_time'] = TimeRepository::getLocalStrtoTime(TimeRepository::getLocalDate('Y-m-d H:i:s'));
            $other['nick_name'] = str_random(6) . "-" . mt_rand(1, 999999);

            $other['msn'] = isset($_POST['extend_field1']) ? htmlspecialchars(trim($_POST['extend_field1'])) : '';
            $other['qq'] = isset($_POST['extend_field2']) ? htmlspecialchars(trim($_POST['extend_field2'])) : '';
            $other['office_phone'] = isset($_POST['extend_field3']) ? htmlspecialchars(trim($_POST['extend_field3'])) : '';
            $other['home_phone'] = isset($_POST['extend_field4']) ? htmlspecialchars(trim($_POST['extend_field4'])) : '';
            $other['mobile_phone'] = isset($_POST['extend_field5']) ? htmlspecialchars(trim($_POST['extend_field5'])) : '';

            $other['passwd_question'] = $sel_question;
            $other['passwd_answer'] = $passwd_answer;

            $this->db->autoExecute($this->dsc->table('users'), $other, 'UPDATE', "user_name = '$username'");

            /* 记录管理员操作 */
            admin_log($_POST['username'], 'add', 'users');

            /* 提示信息 */
            $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'users.php?act=list' . '&' . list_link_postfix()];
            return sys_msg(sprintf($GLOBALS['_LANG']['add_success'], htmlspecialchars(stripslashes($_POST['username']))), 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 编辑用户帐号
        /*------------------------------------------------------ */

        elseif ($act == 'edit') {
            /* 检查权限 */
            admin_priv('users_manage');

            $user_id = isset($_GET['id']) && !empty($_GET['id']) ? intval($_GET['id']) : 0;

            $sql = "SELECT u.user_name, u.sex, u.birthday, u.pay_points, u.rank_points, u.user_rank , " .
                "u.user_money, u.frozen_money, u.credit_line, u.parent_id, u2.user_name as parent_username, u.qq, u.msn, u.office_phone, u.home_phone, u.mobile_phone, " .
                "u.question, u.answer, r.front_of_id_card, r.real_id, r.reverse_of_id_card " .
                " FROM " . $this->dsc->table('users') . " u LEFT JOIN " . $this->dsc->table('users') . " u2 ON u.parent_id = u2.user_id " .
                " LEFT JOIN " . $this->dsc->table('users_real') . " r ON u.user_id = r.user_id " .
                " WHERE u.user_id = '$user_id'";

            $row = $this->db->GetRow($sql);
            $row['user_name'] = addslashes($row['user_name']);
            $users = init_users();
            $user = $users->get_user_info($row['user_name']);

            $sql = "SELECT u.user_id, u.sex, u.birthday, u.pay_points, u.rank_points, u.user_rank , u.user_money, u.frozen_money, u.credit_line, u.parent_id, u2.user_name as parent_username, u.qq, u.msn,
            u.office_phone, u.home_phone, u.mobile_phone, u.email," .
                "u.passwd_question, u.passwd_answer, r.front_of_id_card, r.real_id, r.reverse_of_id_card" .
                " FROM " . $this->dsc->table('users') . " u LEFT JOIN " . $this->dsc->table('users') . " u2 ON u.parent_id = u2.user_id " .
                " LEFT JOIN " . $this->dsc->table('users_real') . " r ON u.user_id = r.user_id " .
                " WHERE u.user_id = '$user_id'";

            $row = $this->db->GetRow($sql);

            if ($row) {
                if (isset($GLOBALS['_CFG']['show_mobile']) && $GLOBALS['_CFG']['show_mobile'] == 0) {
                    $user['mobile_phone_format'] = $this->dscRepository->stringToStar($row['mobile_phone']);
                    $user['user_name_format'] = $this->dscRepository->stringToStar($user['user_name']);
                    $user['email_format'] = $this->dscRepository->stringToStar($user['email']);
                } else {
                    $user['mobile_phone_format'] = $row['mobile_phone'];
                    $user['user_name_format'] = $user['user_name'];
                    $user['email_format'] = $user['email'];
                }

                $user['user_id'] = $row['user_id'];
                $user['sex'] = $row['sex'];
                $user['birthday'] = TimeRepository::getLocalDate($row['birthday']);
                if ($user['birthday']) {
                    $birthday = explode("-", $user['birthday']);
                    $user['year'] = intval($birthday[0]);
                    $user['month'] = intval($birthday[1]);
                    $user['day'] = intval($birthday[2]);
                }
                $user['pay_points'] = $row['pay_points'];
                $user['rank_points'] = $row['rank_points'];
                $user['user_rank'] = $row['user_rank'];
                $user['user_money'] = $row['user_money'];
                $user['frozen_money'] = $row['frozen_money'];
                $user['credit_line'] = $row['credit_line'];
                $user['formated_user_money'] = price_format($row['user_money']);
                $user['formated_frozen_money'] = price_format($row['frozen_money']);
                $user['parent_id'] = $row['parent_id'];
                $user['parent_username'] = $row['parent_username'];
                $user['qq'] = $row['qq'];
                $user['msn'] = $row['msn'];
                $user['office_phone'] = $row['office_phone'];
                $user['home_phone'] = $row['home_phone'];
                $user['mobile_phone'] = $row['mobile_phone'];
                $user['passwd_question'] = $row['passwd_question'];
                $user['passwd_answer'] = $row['passwd_answer'];
                $user['front_of_id_card'] = $this->dscRepository->getImagePath($row['front_of_id_card']);
                $user['reverse_of_id_card'] = $this->dscRepository->getImagePath($row['reverse_of_id_card']);
                $user['real_id'] = $row['real_id'];
                $user['reg_time_format'] = TimeRepository::getLocalDate("Y-m-d H:i:s", $user['reg_time']);
            } else {
                $links[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'users.php?act=list' . '&' . list_link_postfix()];
                return sys_msg($GLOBALS['_LANG']['username_invalid'], 0, $links);
            }

            /* 密码提示问题 */
            $this->smarty->assign('passwd_questions', $GLOBALS['_LANG']['passwd_questions']);

            /* 取出注册扩展字段 */
            $sql = 'SELECT * FROM ' . $this->dsc->table('reg_fields') . ' WHERE type < 2 AND display = 1 ORDER BY dis_order, id';
            $extend_info_list = $this->db->getAll($sql);

            $sql = 'SELECT reg_field_id, content ' .
                'FROM ' . $this->dsc->table('reg_extend_info') .
                " WHERE user_id = $user[user_id]";
            $extend_info_arr = $this->db->getAll($sql);

            $temp_arr = [];
            foreach ($extend_info_arr as $val) {
                $temp_arr[$val['reg_field_id']] = $val['content'];
            }

            foreach ($extend_info_list as $key => $val) {
                switch ($val['id']) {
                    case 1:
                        $extend_info_list[$key]['content'] = $user['msn'];
                        break;
                    case 2:
                        $extend_info_list[$key]['content'] = $user['qq'];
                        break;
                    case 3:
                        $extend_info_list[$key]['content'] = $user['office_phone'];
                        break;
                    case 4:
                        $extend_info_list[$key]['content'] = $user['home_phone'];
                        break;
                    case 5:
                        $extend_info_list[$key]['content'] = $user['mobile_phone_format'];
                        break;
                    default:
                        $extend_info_list[$key]['content'] = empty($temp_arr[$val['id']]) ? '' : $temp_arr[$val['id']];
                }
            }

            $this->smarty->assign('extend_info_list', $extend_info_list);

            /* 当前会员推荐信息 */
            $affiliate = $GLOBALS['_CFG']['affiliate'] ? unserialize($GLOBALS['_CFG']['affiliate']) : [];
            $this->smarty->assign('affiliate', $affiliate);

            empty($affiliate) && $affiliate = [];

            if ($affiliate) {
                if (empty($affiliate['config']['separate_by'])) {

                    //推荐注册分成
                    $affdb = [];
                    $num = $affiliate['item'] ? count($affiliate['item']) : 0;
                    $up_uid = "'$user_id'";

                    if ($num) {
                        for ($i = 1; $i <= $num; $i++) {
                            $count = 0;
                            if ($up_uid) {
                                $sql = "SELECT user_id FROM " . $this->dsc->table('users') . " WHERE parent_id IN($up_uid)";
                                $query = $this->db->query($sql);
                                $up_uid = '';
                                foreach ($query as $rt) {
                                    $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
                                    $count++;
                                }
                            }
                            $affdb[$i]['num'] = $count;
                        }

                        if (isset($affdb[1]['num']) && $affdb[1]['num'] > 0) {
                            $this->smarty->assign('affdb', $affdb);
                        }
                    }
                }
            }

            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('action_link2', ['text' => $GLOBALS['_LANG']['03_users_list'], 'href' => 'users.php?act=list' . '&' . list_link_postfix()]);
            /*获取从1956年到先前的年月日数组*/
            $select_date = [];
            $select_date['year'] = range(1956, date('Y'));
            $select_date['month'] = range(1, 12);
            $select_date['day'] = range(1, 31);
            $this->smarty->assign("select_date", $select_date);
            $this->smarty->assign("user_id", $user['user_id']);

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['users_edit']);
            $this->smarty->assign('form_action', 'update');

            $ship_card = [];
            if (file_exists(MOBILE_DRP)) {
                //是否显示关联分销权益卡等级 1.5
                $ship_card = UserMembershipCard::where('user_rank_id', $user['user_rank'])->select('name')->first();
                $ship_card = $ship_card ? $ship_card->toArray() : [];
                $this->smarty->assign('ship_card', $ship_card);
            }

            $special_ranks = [];
            if (empty($ship_card)) {
                $special_ranks = $this->userRankService->getUserRank(['special_rank' => 1, 'membership_card_display' => 'hide']);
                if ($user['user_rank'] > 0) {
                    if (!empty($special_ranks[$user['user_rank']])) {
                        $user['user_rank_name'] = $special_ranks[$user['user_rank']];
                    } else {
                        $user['user_rank_name'] = DB::table('user_rank')->where('rank_id', $user['user_rank'])->value('rank_name');
                    }
                } else {
                    $user['user_rank_name'] = trans('admin::users.not_special_rank');
                }
            }

            $this->smarty->assign('special_ranks', $special_ranks);
            $this->smarty->assign('user', $user);

            return $this->smarty->display('user_list_edit.dwt');
        }

        /*------------------------------------------------------ */
        //-- 更新用户帐号
        /*------------------------------------------------------ */

        elseif ($act == 'update') {
            /* 检查权限 */
            admin_priv('users_manage');

            $username = empty($_POST['username']) ? '' : trim($_POST['username']);
            $password = empty($_POST['password']) ? '' : trim($_POST['password']);
            $email = empty($_POST['email']) ? '' : trim($_POST['email']);
            $sex = empty($_POST['sex']) ? 0 : intval($_POST['sex']);
            $sex = in_array($sex, [0, 1, 2]) ? $sex : 0;
            $birthdayDay = isset($_POST['birthdayDay']) ? intval($_POST['birthdayDay']) : 00;
            $birthdayDay = (strlen($birthdayDay) == 1) ? "0" . $birthdayDay : $birthdayDay;

            $birthdayMonth = isset($_POST['birthdayMonth']) ? intval($_POST['birthdayMonth']) : 00;
            $birthdayMonth = (strlen($birthdayMonth) == 1) ? "0" . $birthdayMonth : $birthdayMonth;

            $birthday = $_POST['birthdayYear'] . '-' . $birthdayMonth . '-' . $birthdayDay;
            $rank = empty($_POST['user_rank']) ? 0 : intval($_POST['user_rank']);
            $credit_line = empty($_POST['credit_line']) ? 0 : floatval($_POST['credit_line']);
            $user_id = empty($_POST['id']) ? 0 : intval($_POST['id']);

            $sel_question = empty($_POST['sel_question']) ? '' : compile_str($_POST['sel_question']);
            $passwd_answer = isset($_POST['passwd_answer']) ? compile_str(trim($_POST['passwd_answer'])) : '';

            $users = init_users();

            if (!empty($password)) {
                // 数据验证
                $validator = Validator::make(request()->all(), [
                    'password' => ['filled', 'different:username', new PasswordRule()], // 密码
                ], [
                    'password.filled' => lang('user.user_pass_empty'),
                    'password.different' => lang('user.user_pass_same')
                ]);

                // 返回错误
                if ($validator->fails()) {
                    return sys_msg($validator->errors()->first(), 1);
                }
            }

            // 包含“***”的参数保持数据库值
            if (stripos($email, '***') !== false) {
                $email = trim($_POST['old_email']);
            }

            if (!$users->edit_user(['user_id' => $user_id, 'username' => $username, 'password' => $password, 'email' => $email, 'gender' => $sex, 'bday' => $birthday], 1)) {
                if ($users->error == ERR_EMAIL_EXISTS) {
                    $msg = $GLOBALS['_LANG']['email_exists'];
                } else {
                    $msg = $GLOBALS['_LANG']['edit_user_failed'];
                }
                return sys_msg($msg, 1);
            }
            if (!empty($password)) {
                $sql = "UPDATE " . $this->dsc->table('users') . "SET `ec_salt`='0' WHERE user_name= '" . $username . "'";
                $this->db->query($sql);
            }
            /* 更新用户扩展字段的数据 */
            $sql = 'SELECT id FROM ' . $this->dsc->table('reg_fields') . ' WHERE type = 0 AND display = 1 ORDER BY dis_order, id';   //读出所有扩展字段的id
            $fields_arr = $this->db->getAll($sql);

            foreach ($fields_arr as $val) {       //循环更新扩展用户信息
                $extend_field_index = 'extend_field' . $val['id'];
                if (request()->has($extend_field_index)) {
                    $temp_field_content = strlen($_POST[$extend_field_index]) > 100 ? mb_substr($_POST[$extend_field_index], 0, 99) : $_POST[$extend_field_index];

                    // 包含“***”的参数保持数据库值
                    if (stripos($temp_field_content, '***') !== false) {
                        continue;
                    }

                    $sql = 'SELECT * FROM ' . $this->dsc->table('reg_extend_info') . "  WHERE reg_field_id = '$val[id]' AND user_id = '$user_id'";
                    if ($this->db->getOne($sql)) {      //如果之前没有记录，则插入
                        $sql = 'UPDATE ' . $this->dsc->table('reg_extend_info') . " SET content = '$temp_field_content' WHERE reg_field_id = '$val[id]' AND user_id = '$user_id'";
                    } else {
                        $sql = 'INSERT INTO ' . $this->dsc->table('reg_extend_info') . " (`user_id`, `reg_field_id`, `content`) VALUES ('$user_id', '$val[id]', '$temp_field_content')";
                    }
                    $this->db->query($sql);
                }
            }

            /* 更新会员的其它信息 */
            $other = [];
            $other['credit_line'] = $credit_line;
            $other['user_rank'] = $rank;

            $other['msn'] = isset($_POST['extend_field1']) ? htmlspecialchars(trim($_POST['extend_field1'])) : '';
            $other['qq'] = isset($_POST['extend_field2']) ? htmlspecialchars(trim($_POST['extend_field2'])) : '';
            $other['office_phone'] = isset($_POST['extend_field3']) ? htmlspecialchars(trim($_POST['extend_field3'])) : '';
            $other['home_phone'] = isset($_POST['extend_field4']) ? htmlspecialchars(trim($_POST['extend_field4'])) : '';
            if (isset($_POST['extend_field5']) && !empty($_POST['extend_field5'])) {
                $mobile_phone = trim($_POST['extend_field5']);
                // 包含“***”的参数保持数据库值
                if (stripos($mobile_phone, '***') === false) {
                    $other['mobile_phone'] = $mobile_phone;
                }
            }
            $other['passwd_question'] = $sel_question;
            $other['passwd_answer'] = $passwd_answer;

            //验证手机号是否存在于用户名或号码
            if (!empty($other['mobile_phone'])) {
                $useRow = $this->userManageService->exitUser($other['mobile_phone'], $user_id);
                if (!empty($useRow)) {
                    return sys_msg(lang('admin/users.iphone_exist'), 1);
                }
            }

            //获取旧的数据
            $old_user['old_email'] = empty($_POST['old_email']) ? '' : trim($_POST['old_email']);
            $old_user['old_user_rank'] = empty($_POST['old_user_rank']) ? 0 : intval($_POST['old_user_rank']);
            $old_user['old_sex'] = empty($_POST['old_sex']) ? 0 : intval($_POST['old_sex']);
            $old_user['old_birthday'] = empty($_POST['old_birthday']) ? '' : trim($_POST['old_birthday']);
            $old_user['old_credit_line'] = empty($_POST['old_credit_line']) ? 0 : floatval($_POST['old_credit_line']);
            $old_user['old_msn'] = isset($_POST['old_extend_field1']) ? htmlspecialchars(trim($_POST['old_extend_field1'])) : '';
            $old_user['old_qq'] = isset($_POST['old_extend_field2']) ? htmlspecialchars(trim($_POST['old_extend_field2'])) : '';
            $old_user['old_office_phone'] = isset($_POST['old_extend_field3']) ? htmlspecialchars(trim($_POST['old_extend_field3'])) : '';
            $old_user['old_home_phone'] = isset($_POST['old_extend_field4']) ? htmlspecialchars(trim($_POST['old_extend_field4'])) : '';
            $old_user['old_mobile_phone'] = isset($_POST['old_extend_field5']) ? htmlspecialchars(trim($_POST['old_extend_field5'])) : '';
            $old_user['old_passwd_answer'] = isset($_POST['old_passwd_answer']) ? compile_str(trim($_POST['old_passwd_answer'])) : '';
            $old_user['old_sel_question'] = empty($_POST['old_sel_question']) ? '' : compile_str($_POST['old_sel_question']);
            $old_user['password'] = $password;
            load_helper('ipCity');
            $new_user = $other;
            $new_user['email'] = $email;
            $new_user['sex'] = $sex;
            $new_user['birthday'] = $birthday;
            $this->userCommonService->usersLogChangeType($old_user, $new_user, $user_id);

            if ($user_id > 0) {
                $this->db->autoExecute($this->dsc->table('users'), $other, 'UPDATE', "user_id = '$user_id'");
            } else {
                $this->db->autoExecute($this->dsc->table('users'), $other, 'UPDATE', "user_name = '$username'");
            }

            /* 记录管理员操作 */
            admin_log($username, 'edit', 'users');

            /* 提示信息 */
            $links[0]['text'] = $GLOBALS['_LANG']['goto_list'];
            $links[0]['href'] = 'users.php?act=list&' . list_link_postfix();
            $links[1]['text'] = $GLOBALS['_LANG']['go_back'];
            $links[1]['href'] = 'users.php?act=edit&id=' . $user_id;

            return sys_msg($GLOBALS['_LANG']['update_success'], 0, $links);
        }

        /*------------------------------------------------------ */
        //-- 切换是否验证
        /*------------------------------------------------------ */

        if ($act == 'toggle_is_validated') {
            $check_auth = check_authz_json('users_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_POST['id']);
            $val = intval($_POST['val']);

            $up = DB::table('users')->where('user_id', $id)->update(['is_validated' => $val]);

            if ($up) {
                return make_json_result($val);
            } else {
                return make_json_error('error');
            }
        }

        /*------------------------------------------------------ */
        //-- 批量删除会员帐号
        /*------------------------------------------------------ */

        elseif ($act == 'batch_remove') {
            /* 检查权限 */
            admin_priv('users_drop');

            if (isset($_POST['checkboxes'])) {
                /* 只有超级管理员才可以删除商家会员 by wu */
                $priv_str = $this->db->getOne("SELECT action_list FROM " . $this->dsc->table('admin_user') . " WHERE user_id = '" . session('admin_id') . "'");
                if ($priv_str != 'all') {
                    foreach ($_POST['checkboxes'] as $key => $val) {
                        $sql = "SELECT id FROM " . $this->dsc->table('seller_shopinfo') . " WHERE ru_id = '$val'";
                        $shopinfo = $this->db->getOne($sql);
                        if (!empty($shopinfo)) {
                            unset($_POST['checkboxes'][$key]);
                        }
                    }
                }

                /* 是否是供货商用户 */
                if (is_dir(SUPPLIERS)) {
                    $sql = "SELECT user_id FROM " . $this->dsc->table('suppliers') . " WHERE user_id " . db_create_in($_POST['checkboxes']);
                    if ($user_id = $this->db->getOne($sql)) {
                        $sql = "SELECT user_name FROM " . $this->dsc->table('users') . " WHERE user_id = '$user_id'";
                        $username = $this->db->getOne($sql);

                        $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'users.php?act=list' . '&' . list_link_postfix()];
                        return sys_msg(sprintf($GLOBALS['_LANG']['remove_supplier_fail'], $username), 1, $link);
                    }
                }

                $sql = "SELECT user_name FROM " . $this->dsc->table('users') . " WHERE user_id " . db_create_in($_POST['checkboxes']);
                $col = $this->db->getCol($sql);
                $usernames = implode(',', addslashes_deep($col));
                $count = count($col);
                /* 通过插件来删除用户 */
                $users = init_users();
                $users->remove_user($col);

                admin_log($usernames, 'batch_remove', 'users');

                $lnk[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'users.php?act=list' . '&' . list_link_postfix()];
                return sys_msg(sprintf($GLOBALS['_LANG']['batch_remove_success'], $count), 0, $lnk);
            } else {
                $lnk[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'users.php?act=list' . '&' . list_link_postfix()];
                return sys_msg($GLOBALS['_LANG']['no_select_user'], 0, $lnk);
            }
        } elseif ($act == 'main_user') {

        }

        /*------------------------------------------------------ */
        //-- 删除会员帐号
        /*------------------------------------------------------ */

        elseif ($act == 'remove') {
            /* 检查权限 */
            admin_priv('users_drop');

            $user_id = intval($_GET['id']);
            $type = isset($_GET['type']) ? $_GET['type'] : 0;

            $sql = "SELECT user_name FROM " . $this->dsc->table('users') . " WHERE user_id = '$user_id'";
            $username = $this->db->getOne($sql);

            if ($type == 0) {
                $account = $this->userService->getUserAccount($user_id);
                if ($account['type']) {
                    $link[] = ['text' => $GLOBALS['_LANG']['goto_assets_list'], 'href' => 'users.php?act=virtual_assets&id=' . $user_id];
                    return sys_msg(sprintf($GLOBALS['_LANG']['user_have_account'], $username), 0, $link);
                }
            }

            /* 只有超级管理员才可以删除商家会员 by wu */
            $sql = "SELECT shop_id FROM " . $this->dsc->table('merchants_shop_information') . " WHERE user_id = '$user_id'";

            if ($this->db->getOne($sql)) {
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'users.php?act=list' . '&' . list_link_postfix()];
                return sys_msg(sprintf($GLOBALS['_LANG']['remove_seller_fail'], $username, $user_id), 1, $link);
            }

            /* 是否是供货商用户 */
            if (is_dir(SUPPLIERS)) {
                $sql = "SELECT user_id FROM " . $this->dsc->table('suppliers') . " WHERE user_id = '$user_id'";
                if ($this->db->getOne($sql)) {
                    $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'users.php?act=list' . '&' . list_link_postfix()];
                    return sys_msg(sprintf($GLOBALS['_LANG']['remove_supplier_fail'], $username), 1, $link);
                }
            }

            /* 通过插件来删除用户 */
            $users = init_users();
            $users->remove_user($username); //已经删除用户所有数据

            /* 记录管理员操作 */
            admin_log(addslashes($username), 'remove', 'users');

            /* 提示信息 */
            $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'users.php?act=list' . '&' . list_link_postfix()];
            return sys_msg(sprintf($GLOBALS['_LANG']['remove_success'], $username), 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 虚拟资产列表
        /*------------------------------------------------------ */
        elseif ($act == 'virtual_assets') {
            /* 检查权限 */
            admin_priv('users_drop');
            $user_id = intval($_GET['id']);
            //获取虚拟资产列表
            $account = $this->userService->getUserAccount($user_id);

            $this->smarty->assign('menu_select', ['action' => '08_members', 'current' => '03_users_list']);

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['virtual_assets_list']);
            $this->smarty->assign('user_id', $user_id);

            $this->smarty->assign('account', $account);
            $this->smarty->assign('full_page', 1);

            return $this->smarty->display('user_assets_list.dwt');
        }

        /*------------------------------------------------------ */
        //--  收货地址查看
        /*------------------------------------------------------ */
        elseif ($act == 'address_list') {
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $sql = "SELECT a.*, c.region_name AS country_name, p.region_name AS province, ct.region_name AS city_name, d.region_name AS district_name " .
                " FROM " . $this->dsc->table('user_address') . " as a " .
                " LEFT JOIN " . $this->dsc->table('region') . " AS c ON c.region_id = a.country " .
                " LEFT JOIN " . $this->dsc->table('region') . " AS p ON p.region_id = a.province " .
                " LEFT JOIN " . $this->dsc->table('region') . " AS ct ON ct.region_id = a.city " .
                " LEFT JOIN " . $this->dsc->table('region') . " AS d ON d.region_id = a.district " .
                " WHERE user_id='$id'";
            $address = $this->db->getAll($sql);

            if ($address) {
                foreach ($address as $key => $row) {
                    if (isset($GLOBALS['_CFG']['show_mobile']) && $GLOBALS['_CFG']['show_mobile'] == 0) {
                        $address[$key]['tel'] = $this->dscRepository->stringToStar($row['tel']);
                        $address[$key]['mobile'] = $this->dscRepository->stringToStar($row['mobile']);
                    }
                }
            }

            $this->smarty->assign('address', $address);
            $this->smarty->assign("user_id", $id);
            $this->smarty->assign('form_action', 'address_list');
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['address_list']);
            if ($id > 0) {
                $this->smarty->assign('action_link2', ['text' => $GLOBALS['_LANG']['address_list'], 'href' => 'users.php?act=list' . '&' . list_link_postfix()]);
            }


            return $this->smarty->display('user_list_edit.dwt');
        }

        /*------------------------------------------------------ */
        //-- 脱离推荐关系
        /*------------------------------------------------------ */

        elseif ($act == 'remove_parent') {
            /* 检查权限 */
            admin_priv('users_manage');

            $user_id = (int)request()->input('id', 0);

            if (empty($user_id)) {
                return sys_msg('invalid param', 1);
            }

            Users::where('user_id', $user_id)->update(['parent_id' => 0]);

            if (file_exists(MOBILE_DRP)) {
                Users::where('user_id', $user_id)->update(['drp_parent_id' => 0]);
            }

            /* 记录管理员操作 */
            $sql = "SELECT user_name FROM " . $this->dsc->table('users') . " WHERE user_id = '" . $user_id . "'";
            $username = $this->db->getOne($sql);
            admin_log(addslashes($username), 'edit', 'users');

            /* 提示信息 */
            $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'users.php?act=list' . '&' . list_link_postfix()];
            return sys_msg(sprintf($GLOBALS['_LANG']['update_success'], $username), 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 查看用户推荐会员列表
        /*------------------------------------------------------ */

        elseif ($act == 'aff_list') {
            /* 检查权限 */
            admin_priv('users_manage');
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['03_users_list']);

            $auid = isset($_GET['auid']) && !empty($_GET['auid']) ? intval($_GET['auid']) : 0;
            $user_list['user_list'] = [];

            $affiliate = $GLOBALS['_CFG']['affiliate'] ? unserialize($GLOBALS['_CFG']['affiliate']) : [];
            $this->smarty->assign('affiliate', $affiliate);

            empty($affiliate) && $affiliate = [];

            $num = count($affiliate['item']);
            $up_uid = "'$auid'";
            $all_count = 0;
            for ($i = 1; $i <= $num; $i++) {
                $count = 0;
                if ($up_uid) {
                    $sql = "SELECT user_id FROM " . $this->dsc->table('users') . " WHERE parent_id IN($up_uid)";
                    $query = $this->db->query($sql);
                    $up_uid = '';
                    foreach ($query as $rt) {
                        $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
                        $count++;
                    }
                }
                $all_count += $count;

                if ($count) {
                    $sql = "SELECT user_id, user_name, '$i' AS level, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time " .
                        " FROM " . $this->dsc->table('users') . " WHERE user_id IN($up_uid)" .
                        " ORDER by level, user_id";
                    $user_list['user_list'] = array_merge($user_list['user_list'], $this->db->getAll($sql));
                }
            }

            $temp_count = count($user_list['user_list']);
            for ($i = 0; $i < $temp_count; $i++) {
                $user_list['user_list'][$i]['reg_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['date_format'], $user_list['user_list'][$i]['reg_time']);
            }

            $user_list['record_count'] = $all_count;

            $this->smarty->assign('user_list', $user_list['user_list']);
            $this->smarty->assign('record_count', $user_list['record_count']);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['back_note'], 'href' => "users.php?act=edit&id=$auid"]);


            return $this->smarty->display('affiliate_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 导出会员列表（队列）
        /*------------------------------------------------------ */
        elseif ($act == 'export_user') {
            $filter = request()->post();

            $filter['page_size'] = 100;
            $filter['file_name'] = date('YmdHis') . mt_rand(1000, 9999);
            $filter['type'] = $type = 'user';
            $filter['admin_id'] = $adminru['ru_id'] ?? 0; // 导出操作管理员id

            $filter['start_date'] = !empty($filter['start_date']) ? (strpos($filter['start_date'], '-') > 0 ? TimeRepository::getLocalStrtoTime($filter['start_date']) : $filter['start_date']) : '';
            $filter['end_date'] = !empty($filter['end_date']) ? (strpos($filter['end_date'], '-') > 0 ? TimeRepository::getLocalStrtoTime($filter['end_date']) : $filter['end_date']) : '';

            // 插入导出记录表
            $filter['request_id'] = DB::table('export_history')->insertGetId([
                'ru_id' => $filter['admin_id'],
                'type' => $type,
                'file_name' => $filter['file_name'] . '_' . 1,
                'file_type' => 'xls',
                'download_params' => json_encode($filter),
                'created_at' => Carbon::now(),
            ]);

            ProcessUserExport::dispatch($filter);

            return make_json_result($type);
        } //会员操作日志
        elseif ($act == 'users_log') {
            //页面赋值
            $this->smarty->assign("ur_here", $GLOBALS['_LANG']['users_log']);
            $user_id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

            $user_log = $this->get_user_log();
            $this->smarty->assign('user_log', $user_log['list']);
            $this->smarty->assign('filter', $user_log['filter']);
            $this->smarty->assign('record_count', $user_log['record_count']);
            $this->smarty->assign('page_count', $user_log['page_count']);

            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('user_id', $user_id);

            return $this->smarty->display("users_log.dwt");
        } //会员操作日志ajax
        elseif ($act == 'users_log_query') {
            $user_id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

            $user_log = $this->get_user_log();
            $this->smarty->assign('user_log', $user_log['list']);
            $this->smarty->assign('filter', $user_log['filter']);
            $this->smarty->assign('record_count', $user_log['record_count']);
            $this->smarty->assign('page_count', $user_log['page_count']);
            $this->smarty->assign('user_id', $user_id);
            return make_json_result(
                $this->smarty->fetch('users_log.dwt'),
                '',
                ['filter' => $user_log['filter'], 'page_count' => $user_log['page_count']]
            );
        } //删除会员日志
        elseif ($act == 'batch_log') {
            $user_id = !empty($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
            /* 取得要操作的记录编号 */
            if (empty($_POST['checkboxes'])) {
                return sys_msg($GLOBALS['_LANG']['no_record_selected']);
            } else {
                $ids = $_POST['checkboxes'];
                /* 删除记录 */
                $sql = "DELETE FROM " . $this->dsc->table('users_log') .
                    " WHERE log_id " . db_create_in($ids) . " AND user_id = '$user_id'";
                $this->db->query($sql);

                /* 清除缓存 */
                clear_cache_files();
                $link[] = ['text' => $GLOBALS['_LANG']['back'], 'href' => 'users.php?act=users_log&id=' . $user_id];
                return sys_msg($GLOBALS['_LANG']['batch_drop_ok'], '', $link);
            }
        } //会员详情
        elseif ($act == 'user_detail') {
            /* 检查权限 */
            admin_priv('users_manage');
            $this->dscRepository->helpersLang(['order'], 'admin');
            $goback = !empty($_GET['goback']) ? true : false;
            $user_id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
            $user_info = $this->userService->getUserDetail($user_id);
            $this->smarty->assign('goback', $goback);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['04_users_add'], 'href' => 'users.php?act=list' . '&' . list_link_postfix()]);
            $this->smarty->assign('lang', $GLOBALS['_LANG']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['13_users_detail']);
            $this->smarty->assign('user_info', $user_info);
            return $this->smarty->display('user_detail.dwt');
        } //会员储值卡列表
        elseif ($act == 'user_value_card_list') {
            /* 检查权限 */
            admin_priv('users_manage');
            $this->dscRepository->helpersLang(['value_card'], 'admin');
            $user_id = !empty($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;

            $list = $this->userService->getUserValueCardList();

            $this->smarty->assign('user_id', $user_id);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['14_users_name']);
            $this->smarty->assign('lang', $GLOBALS['_LANG']);
            $this->smarty->assign('list', $list['user_list']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);
            $this->smarty->assign('full_page', 1);

            return $this->smarty->display('user_value_card_list.dwt');
        } //会员储值卡列表 ajax
        elseif ($act == 'user_value_card_list_query') {
            /* 检查权限 */
            admin_priv('users_manage');
            $this->dscRepository->helpersLang(['value_card'], 'admin');
            $list = $this->userService->getUserValueCardList();

            $user_id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

            $this->smarty->assign('lang', $GLOBALS['_LANG']);
            $this->smarty->assign('user_id', $user_id);
            $this->smarty->assign('list', $list['user_list']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            return make_json_result($this->smarty->fetch('user_value_card_list.dwt'), '', ['filter' => $list['filter'], 'page_count' => $list['page_count']]);
        } //会员优惠券列表
        elseif ($act == 'user_coupon_list') {
            /* 检查权限 */
            admin_priv('users_manage');
            $this->dscRepository->helpersLang(['coupons'], 'admin');
            $user_id = !empty($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            $list = $this->userService->getUserCouponList();

            $this->smarty->assign('user_id', $user_id);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['14_users_name']);
            $this->smarty->assign('lang', $GLOBALS['_LANG']);
            $this->smarty->assign('list', $list['user_list']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);
            $this->smarty->assign('full_page', 1);

            return $this->smarty->display('user_coupon_list.dwt');
        } //会员优惠券列表 ajax
        elseif ($act == 'user_coupon_list_query') {
            /* 检查权限 */

            admin_priv('users_manage');
            $this->dscRepository->helpersLang(['value_card'], 'admin');
            $list = $this->userService->getUserCouponList();

            $user_id = !empty($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;

            $this->smarty->assign('lang', $GLOBALS['_LANG']);
            $this->smarty->assign('user_id', $user_id);
            $this->smarty->assign('list', $list['user_list']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            return make_json_result($this->smarty->fetch('user_coupon_list.dwt'), '', ['filter' => $list['filter'], 'page_count' => $list['page_count']]);
        }//移除优惠券
        elseif ($act == 'remove_user_coupon_list') {
            /* 检查权限 */
            admin_priv('users_manage');
            $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
            $uc_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $use_type = $_REQUEST['use_type'] ?? 0;
            $page = $_REQUEST['page'] ?? 0;
            $ru_id = $_REQUEST['ru_id'] ?? 0;
            $keywords = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
            if ($uc_id) {
                $this->userService->removeUserCoupon($uc_id, $user_id);
                $url = "users.php?act=user_coupon_list_query&use_type=" . $use_type . '&user_id=' . $user_id . '&page=' . $page . '&ru_id=' . $ru_id . '&keywords=' . $keywords;
                return dsc_header("Location: $url\n");
            }
        } //会员红包列表
        elseif ($act == 'user_bonus_list') {
            /* 检查权限 */
            admin_priv('users_manage');
            $this->dscRepository->helpersLang(['bonus'], 'admin');

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['14_users_name']);
            $user_id = !empty($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            $bonus_list = $this->userService->getUserBonusList();

            $this->smarty->assign('lang', $GLOBALS['_LANG']);
            $this->smarty->assign('user_id', $user_id);
            $this->smarty->assign('list', $bonus_list['user_list']);
            $this->smarty->assign('filter', $bonus_list['filter']);
            $this->smarty->assign('record_count', $bonus_list['record_count']);
            $this->smarty->assign('page_count', $bonus_list['page_count']);
            $this->smarty->assign('full_page', 1);

            return $this->smarty->display('user_bonus_list.dwt');
        } //会员红包列表 ajax
        elseif ($act == 'user_bonus_list_query') {
            $this->dscRepository->helpersLang(['bonus'], 'admin');
            $bonus_list = $this->userService->getUserBonusList();

            $this->smarty->assign('lang', $GLOBALS['_LANG']);
            $this->smarty->assign('list', $bonus_list['user_list']);
            $this->smarty->assign('filter', $bonus_list['filter']);
            $this->smarty->assign('record_count', $bonus_list['record_count']);
            $this->smarty->assign('page_count', $bonus_list['page_count']);

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            $sort_flag = sort_flag($bonus_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result($this->smarty->fetch('user_bonus_list.dwt'), '', ['filter' => $bonus_list['filter'], 'page_count' => $bonus_list['page_count']]);
        }//移除红包
        elseif ($act == 'remove_user_bonus_list') {
            /* 检查权限 */
            admin_priv('users_manage');

            $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
            $bonus_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $use_type = $_REQUEST['use_type'] ?? 0;
            $page = $_REQUEST['page'] ?? 0;
            $ru_id = $_REQUEST['ru_id'] ?? 0;
            $keywords = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
            if ($bonus_id) {
                $this->userService->removeUserBonus($bonus_id, $user_id);
                $url = "users.php?act=user_bonus_list_query&use_type=" . $use_type . '&user_id=' . $user_id . '&page=' . $page . '&ru_id=' . $ru_id . '&keywords=' . $keywords;
                return dsc_header("Location: $url\n");
            }
        }
    }

    /**
     * 会员操作日志
     * @return  array
     */
    private function get_user_log()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'get_user_log';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $where = ' WHERE 1 ';
        /* 初始化分页参数 */
        $filter = [];
        $filter['id'] = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '0';

        $where .= " AND user_id = '" . $filter['id'] . "'";
        /* 查询记录总数，计算分页数 */
        $sql = "SELECT COUNT(*) FROM " . $this->dsc->table('users_log') . $where;
        $filter['record_count'] = $this->db->getOne($sql);
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 查询记录 */
        $sql = "SELECT log_id,user_id,change_time,change_type,ip_address,change_city,logon_service,admin_id FROM" . $this->dsc->table('users_log')
            . "$where  ORDER BY change_time DESC";

        $res = $this->db->selectLimit($sql, $filter['page_size'], $filter['start']);

        $arr = [];
        foreach ($res as $rows) {
            if ($rows['change_time'] > 0) {
                $rows['change_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $rows['change_time']);
            }
            if ($rows['admin_id'] > 0) {
                $sql = 'SELECT user_name FROM' . $this->dsc->table('admin_user') . " WHERE user_id = '" . $rows['admin_id'] . "'";
                $rows['admin_name'] = $GLOBALS['_LANG']['manage_alt'] . $this->db->getOne($sql);
            } else {
                $rows['admin_name'] = $GLOBALS['_LANG']['user_handle'];
            }
            $arr[] = $rows;
        }
        return ['list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }
}
