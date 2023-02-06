<?php

namespace App\Modules\Admin\Controllers;

use App\Jobs\Export\ProcessUserAccountExport;
use App\Modules\Drp\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\User\UserAccountService;
use App\Services\User\UserDataHandleService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * 会员帐目管理(包括预付款，余额)
 */
class UserAccountController extends InitController
{
    protected $commonRepository;
    protected $dscRepository;
    protected $userAccountService;

    public function __construct(
        CommonRepository $commonRepository,
        DscRepository $dscRepository,
        UserAccountService $userAccountService
    )
    {
        $this->commonRepository = $commonRepository;
        $this->dscRepository = $dscRepository;
        $this->userAccountService = $userAccountService;
    }

    public function index()
    {
        /* act操作项的初始化 */
        $act = e(request()->input('act', 'list'));

        /*------------------------------------------------------ */
        //-- 会员余额记录列表
        /*------------------------------------------------------ */
        if ($act == 'list') {
            /* 权限判断 */
            admin_priv('surplus_manage');

            /* 指定会员的ID为查询条件 */
            $user_id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
            $process_type = isset($_REQUEST['process_type']) && !empty($_REQUEST['process_type']) ? intval($_REQUEST['process_type']) : 0;
            $is_paid = !empty($_REQUEST['is_paid']) ? intval($_REQUEST['is_paid']) : 0;

            /* 获得支付方式列表 */
            $payment_list = $this->userAccountService->getPaymentList();

            /* 模板赋值 */
            $this->smarty->assign('process_type_' . $process_type, 'selected="selected"');
            $this->smarty->assign('process_type', $process_type);

            if (isset($_REQUEST['is_paid'])) {
                $this->smarty->assign('is_paid_' . $is_paid, 'selected="selected"');
            }
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['09_user_account']);
            $this->smarty->assign('id', $user_id);
            $this->smarty->assign('payment_list', $payment_list);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['surplus_add'], 'href' => 'user_account.php?act=add&process_type=' . $process_type]);

            $list = $this->account_list();
            $this->smarty->assign('list', $list['list']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('sort_add_time', '<img src="' . __TPL__ . '/images/sort_desc.gif">');

            $this->smarty->assign('current_time', TimeRepository::getLocalDate('Y-m-d H:i:s'));
            $this->smarty->assign('current_url', urlencode(request()->getRequestUri()));
            $this->smarty->assign('lang', array_merge($GLOBALS['_LANG'], trans('admin/order_export')));
            return $this->smarty->display('user_account_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 添加/编辑会员余额页面
        /*------------------------------------------------------ */
        elseif ($act == 'add' || $act == 'edit') {
            admin_priv('surplus_manage'); //权限判断

            $process_type = isset($_REQUEST['process_type']) && !empty($_REQUEST['process_type']) ? intval($_REQUEST['process_type']) : 0;

            $ur_here = ($act == 'add') ? $GLOBALS['_LANG']['surplus_add'] : $GLOBALS['_LANG']['surplus_edit'];
            $form_act = ($act == 'add') ? 'insert' : 'update';
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

            /* 获得支付方式列表, 不包括“货到付款” */
            $user_account = [];
            $payment_list = $this->userAccountService->getPaymentList();

            if ($act == 'edit') {
                /* 取得余额信息 */
                $user_account = $this->db->getRow("SELECT * FROM " . $this->dsc->table('user_account') . " WHERE id = '$id'");

                // 如果是负数，去掉前面的符号
                $user_account['amount'] = str_replace('-', '', $user_account['amount']);

                /* 取得会员名称 */
                $sql = "SELECT user_name FROM " . $this->dsc->table('users') . " WHERE user_id = '$user_account[user_id]'";
                $user_name = $this->db->getOne($sql);
            } else {
                $user_name = '';
            }

            if ($user_account && $user_account['pay_id'] == 0 && $user_account['payment']) {
                $sql = 'SELECT pay_id FROM ' . $this->dsc->table('payment') .
                    " WHERE pay_name = '" . $user_account['payment'] . "' AND enabled = 1";
                $pay_id = $this->db->getOne($sql, true);

                $user_account['pay_id'] = $pay_id;
            }

            if ($act == 'add') {
                $user_account['process_type'] = $process_type;
            }

            if (isset($GLOBALS['_CFG']['show_mobile']) && $GLOBALS['_CFG']['show_mobile'] == 0) {
                $user_name = $this->dscRepository->stringToStar($user_name);
            }

            /* 模板赋值 */
            $this->smarty->assign('ur_here', $ur_here);
            $this->smarty->assign('form_act', $form_act);
            $this->smarty->assign('payment_list', $payment_list);
            $this->smarty->assign('action', $act);
            $this->smarty->assign('user_surplus', $user_account);
            $this->smarty->assign('user_name', $user_name);

            if ($act == 'add') {
                $href = 'user_account.php?act=list&process_type=' . $process_type;
            } else {
                $href = 'user_account.php?act=list&process_type=' . $process_type . '&' . list_link_postfix();
            }
            $this->smarty->assign('action_link', ['href' => $href, 'text' => $GLOBALS['_LANG']['09_user_account']]);


            return $this->smarty->display('user_account_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 添加/编辑会员余额的处理部分
        /*------------------------------------------------------ */
        elseif ($act == 'insert' || $act == 'update') {
            /* 权限判断 */
            admin_priv('surplus_manage');

            /* 初始化变量 */
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $is_paid = !empty($_POST['is_paid']) ? intval($_POST['is_paid']) : 0;
            $amount = !empty($_POST['amount']) ? floatval($_POST['amount']) : 0;
            $process_type = !empty($_POST['process_type']) ? intval($_POST['process_type']) : 0;
            $user_name = !empty($_POST['user_id']) ? addslashes(trim($_POST['user_id'])) : '';// 会员名称
            $admin_note = !empty($_POST['admin_note']) ? trim($_POST['admin_note']) : '';
            $user_note = !empty($_POST['user_note']) ? trim($_POST['user_note']) : '';
            $pay_id = !empty($_POST['pay_id']) ? intval($_POST['pay_id']) : 0;

            $withdraw_type = (int)request()->input('withdraw_type', 0);// 提现类型：0为银行卡，1为微信 2为支付宝
            $withdraw_number = e(request()->input('bank_number', ''));// 提现账号

            $user_id = DB::table('users')->where('user_name', $user_name)->value('user_id');

            /* 此会员是否存在 */
            if (empty($user_id) || $user_id == 0) {
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                return sys_msg($GLOBALS['_LANG']['username_not_exist'], 1, $link);
            }

            /* 提现 检查余额是否足够 */
            if ($process_type == 1) {
                $user_surplus = $this->get_user_surplus($user_id);

                /* 如果扣除的余额多于此会员拥有的余额，提示 */
                $sur_amount = $user_surplus['user_money'] ?? 0;
                if ($amount > $sur_amount) {
                    $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                    return sys_msg($GLOBALS['_LANG']['surplus_amount_error'], 1, $link);
                }
            }

            /* 取支付方式信息 */
            $payment_info = [];
            if ($process_type == 0) {
                $payment_info = DB::table('payment')->where('pay_id', $pay_id)->select('pay_name', 'pay_id')->first();
                $payment_info = $payment_info ? collect($payment_info)->toArray() : [];
            }

            /* 入库的操作 */
            if ($act == 'insert') {
                $other = [
                    'user_id' => $user_id,
                    'admin_user' => session('admin_name'),
                    'amount' => $amount,
                    'add_time' => TimeRepository::getGmTime(),
                    'admin_note' => $admin_note,
                    'user_note' => $user_note,
                    'process_type' => $process_type,
                    'is_paid' => $is_paid
                ];

                // 充值
                if ($process_type == 0) {
                    $other['payment'] = $payment_info['pay_name'] ?? '';
                    $other['pay_id'] = $pay_id;
                }
                // 提现
                if ($process_type == 1) {
                    if ($withdraw_type > 0) {
                        $withdraw_type = $withdraw_type - 1; // 提现类型：0为银行卡，1为微信 2为支付宝
                    }
                    // 提现到银行卡
                    if ($withdraw_type == 0) {
                        $users_real = DB::table('users_real')->where('user_id', $user_id)->where('user_type', 0)->where('review_status', 1)->select('bank_card', 'real_name')->first();
                        $users_real = $users_real ? collect($users_real)->toArray() : [];
                        // 优先取输入的账号，未输入取用户实名账号信息
                        $bank_number = !empty($withdraw_number) ? $withdraw_number : $users_real['bank_card'] ?? '';
                        $real_name = $users_real['real_name'] ?? '';// 提现会员实名
                    } else {
                        $real_name = $user_name;
                        $bank_number = $withdraw_number;
                    }

                    // 请输入提现账号
                    if (empty($bank_number)) {
                        $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                        return sys_msg(trans('admin::user_account.withdraw_number_empty'), 1, $link);
                    }

                    $amount = (-1) * $amount;
                }

                if ($is_paid == 1) {
                    $other['paid_time'] = TimeRepository::getGmTime();
                }

                $id = DB::table('user_account')->insertGetId($other);
                // 提现记录扩展信息
                if ($process_type == 1 && $id) {
                    $user_account_fields = [
                        'user_id' => $user_id,
                        'account_id' => $id,
                        'bank_number' => $bank_number ?? '',
                        'real_name' => $real_name ?? '',
                        'withdraw_type' => $withdraw_type
                    ];
                    UserAccountService::insert_user_account_fields($user_account_fields);

                    // 余额变动
                    $change_desc = $amount > 0 ? $GLOBALS['_LANG']['surplus_type_0'] : $GLOBALS['_LANG']['surplus_type_1'];
                    $change_type = $amount > 0 ? ACT_SAVING : ACT_DRAWING;
                    log_account_change($user_id, $amount, -$amount, 0, 0, $change_desc, $change_type);
                }
            } else {
                /* 更新数据表 */
                $update = [
                    'admin_note' => $admin_note,
                    'user_note' => $user_note,
                ];
                if ($process_type == 0) {
                    $update['payment'] = $payment_info['pay_name'] ?? '';
                    $update['pay_id'] = $pay_id;
                }
                DB::table('user_account')->where('id', $id)->update($update);
            }

            // 更新会员余额数量
            if ($is_paid == 1) {
                $change_desc = $amount > 0 ? $GLOBALS['_LANG']['surplus_type_0'] : $GLOBALS['_LANG']['surplus_type_1'];
                $change_type = $amount > 0 ? ACT_SAVING : ACT_DRAWING;
                log_account_change($user_id, $amount, 0, 0, 0, $change_desc, $change_type);
            }

            //如果是预付款并且未确认，向pay_log插入一条记录
            if ($process_type == 0 && $is_paid == 0) {
                load_helper('order');

                //计算支付手续费用
                $pay_fee = pay_fee($payment_info['pay_id'], $amount, 0);
                $total_fee = $pay_fee + $amount;

                /* 插入 pay_log */
                $sql = 'INSERT INTO ' . $this->dsc->table('pay_log') . " (order_id, order_amount, order_type, is_paid)" .
                    " VALUES ('$id', '$total_fee', '" . PAY_SURPLUS . "', 0)";
                $this->db->query($sql);
            }

            /* 记录管理员操作 */
            $content = 'process_type_' . $process_type; // 操作内容
            if ($act == 'update') {
                admin_log($user_name, 'edit', $content);
            } else {
                admin_log($user_name, 'add', $content);
            }

            /* 提示信息 */
            if ($act == 'insert') {
                $href = 'user_account.php?act=list&process_type=' . $process_type;
            } else {
                $href = 'user_account.php?act=list&process_type=' . $process_type . '&' . list_link_postfix();
            }

            $link[0]['text'] = $GLOBALS['_LANG']['back_list'];
            $link[0]['href'] = $href;

            $link[1]['text'] = $GLOBALS['_LANG']['continue_add'];
            $link[1]['href'] = 'user_account.php?act=add&process_type=' . $process_type;

            return sys_msg($GLOBALS['_LANG']['attradd_succed'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 审核会员余额页面
        /*------------------------------------------------------ */
        elseif ($act == 'check') {
            /* 检查权限 */
            admin_priv('surplus_manage');

            /* 初始化 */
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

            /* 如果参数不合法，返回 */
            if ($id == 0) {
                return dsc_header("Location: user_account.php?act=list\n");
            }

            /* 查询当前的预付款信息 */
            $account = $this->db->getRow("SELECT * FROM " . $this->dsc->table('user_account') . " WHERE id = '$id'");
            if (empty($account)) {
                return dsc_header("Location: user_account.php?act=list\n");
            }

            $process_type = $account['process_type'];

            $account['add_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $account['add_time']);
            $account['complaint_imges'] = $account['complaint_imges'] ? $this->dscRepository->getImagePath($account['complaint_imges']) : '';

            $users_real = DB::table('users_real')->where('user_id', $account['user_id'])->where('user_type', 0)->where('review_status', 1)->first();
            $users_real = $users_real ? collect($users_real)->toArray() : [];

            if ($process_type == 1 || $account['payment'] == lang('admin/user_account.remittance')) {
                $account['real'] = $users_real;

                $account['processType'] = 1;
            }

            //by wang获得用户账目的扩展信息
            $account['fields'] = $this->db->getRow("SELECT * FROM " . $this->dsc->table('user_account_fields') . " WHERE user_id = '$account[user_id]' and account_id='$id'");

            //余额类型:预付款，退款申请，购买商品，取消订单
            if ($process_type == 0) {
                $process_type_format = $GLOBALS['_LANG']['surplus_type_0'];
            } elseif ($process_type == 1) {
                $process_type_format = $GLOBALS['_LANG']['surplus_type_1'];
            } elseif ($process_type == 2) {
                $process_type_format = $GLOBALS['_LANG']['surplus_type_2'];
            } else {
                $process_type_format = $GLOBALS['_LANG']['surplus_type_3'];
            }

            $sql = "SELECT user_name, mobile_phone FROM " . $this->dsc->table('users') . " WHERE user_id = '" . $account['user_id'] . "' LIMIT 1";
            $user_info = $this->db->getRow($sql);

            if (isset($GLOBALS['_CFG']['show_mobile']) && $GLOBALS['_CFG']['show_mobile'] == 0 && $user_info) {
                $user_info['user_name'] = $this->dscRepository->stringToStar($user_info['user_name']);
                $user_info['mobile_phone'] = $this->dscRepository->stringToStar($user_info['mobile_phone']);
            }

            if (isset($GLOBALS['_CFG']['show_mobile']) && $GLOBALS['_CFG']['show_mobile'] == 0 && $users_real) {
                $users_real['bank_mobile'] = $this->dscRepository->stringToStar($users_real['bank_mobile']);
            }

            if ($process_type == 1) {
                $account['fields']['withdraw_type_name'] = $GLOBALS['_LANG']['withdraw_type_' . $account['fields']['withdraw_type'] ?? 0];
            }

            $account['user_note'] = htmlspecialchars($account['user_note']);

            /* 模板赋值 */
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['check']);
            $this->smarty->assign('surplus', $account);
            $this->smarty->assign('users_real', $users_real);
            $this->smarty->assign('process_type', $process_type_format);
            $this->smarty->assign('user_info', $user_info);
            $this->smarty->assign('id', $id);

            $text = $process_type == 1 ? trans('admin::user_account.put_forward_apply') : trans('admin::user_account.recharge_apply');
            $this->smarty->assign('action_link', ['text' => $text, 'href' => 'user_account.php?act=list&process_type=' . $process_type . '&' . list_link_postfix()]);

            /* 页面显示 */

            return $this->smarty->display('user_account_check.dwt');
        }

        /*------------------------------------------------------ */
        //-- 到款审核提交 更新会员余额的状态
        /*------------------------------------------------------ */
        elseif ($act == 'action') {
            /* 检查权限 */
            admin_priv('surplus_manage');

            /* 初始化 */
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $is_paid = isset($_POST['is_paid']) ? intval($_POST['is_paid']) : 0;
            $admin_note = isset($_POST['admin_note']) ? trim($_POST['admin_note']) : '';

            /* 如果参数不合法，返回 */
            if ($id == 0 || empty($admin_note)) {
                return dsc_header("Location: user_account.php?act=list\n");
            }

            /* 查询当前的预付款信息 */
            $account = $this->db->getRow("SELECT * FROM " . $this->dsc->table('user_account') . " WHERE id = '$id'");
            $amount = $account['amount'];

            $user_surplus = $this->get_user_surplus($account['user_id']);

            //如果状态为未确认
            if ($account['is_paid'] == 0) {
                //获取申请会员信息
                $user_info = [];
                if (intval($account['user_id']) > 0) {
                    $sql = "SELECT mobile_phone, email,user_name FROM" . $this->dsc->table('users') . " WHERE user_id = '" . $account['user_id'] . "' LIMIT 1";
                    $user_info = $this->db->getRow($sql);
                }

                $process_type = $account['process_type'];

                //短信接口参数
                $smsParams = [
                    'user_name' => $user_info['user_name'],
                    'username' => $user_info['user_name'],
                    'user_money' => $user_surplus['user_money'] + $amount,
                    'usermoney' => $user_surplus['user_money'] + $amount,
                    'op_time' => TimeRepository::getLocalDate('Y-m-d H:i:s'),
                    'optime' => TimeRepository::getLocalDate('Y-m-d H:i:s'),
                    'add_time' => TimeRepository::getLocalDate('Y-m-d H:i:s', $account['add_time']),
                    'addtime' => TimeRepository::getLocalDate('Y-m-d H:i:s', $account['add_time']),
                    'examine' => "通过",
                    'fmt_amount' => $amount,
                    'fmtamount' => $amount,
                    'mobile_phone' => $user_info['mobile_phone'] ? $user_info['mobile_phone'] : '',
                    'mobilephone' => $user_info['mobile_phone'] ? $user_info['mobile_phone'] : ''
                ];
                //如果是退款申请, 并且已完成,更新此条记录,扣除相应的余额
                if ($is_paid == 1 && $account['process_type'] == 1) {
                    $fmt_amount = str_replace('-', '', $amount);

                    //如果扣除的余额多于此会员拥有的余额，提示
                    if ($fmt_amount > $user_surplus['frozen_money']) {
                        $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                        return sys_msg($GLOBALS['_LANG']['surplus_frozen_error'], 0, $link);
                    }

                    $this->update_user_account($id, $amount, $admin_note, $is_paid);

                    //更新会员余额数量
                    log_account_change($account['user_id'], 0, $amount + $account['deposit_fee'], 0, 0, "【" . $GLOBALS['_LANG']['surplus_type_1'] . "-" . $GLOBALS['_LANG']['offline_transfer'] . "】" . $admin_note, ACT_DRAWING);

                    $order_note = !empty($account['admin_note']) ? explode("：", $account['admin_note']) : [];

                    if ($order_note && isset($order_note[1]) && $order_note[1]) {
                        load_helper('order');

                        $order = order_info(0, $order_note[1]);

                        if ($order['ru_id']) {
                            $adminru = get_admin_ru_id();

                            $change_desc = $GLOBALS['_LANG']['operator'] . $adminru['user_name'] . $GLOBALS['_LANG']['operator_two'];
                            $log = [
                                'user_id' => $order['ru_id'],
                                'user_money' => $amount,
                                'change_time' => TimeRepository::getGmTime(),
                                'change_desc' => $change_desc,
                                'change_type' => 2
                            ];

                            $this->db->autoExecute($this->dsc->table('merchants_account_log'), $log, 'INSERT');

                            $sql = "UPDATE " . $this->dsc->table('seller_shopinfo') . " SET seller_money = seller_money + '" . $log['user_money'] . "' WHERE ru_id = '" . $order['ru_id'] . "'";
                            $this->db->query($sql);
                        }
                    }

                    /* 如果需要，发短信 */
                    $smsParams['user_money'] = $user_surplus['user_money'];
                    $smsParams['usermoney'] = $user_surplus['user_money'];
                    $smsParams['process_type'] = $GLOBALS['_LANG']['surplus_type_1'];
                    $smsParams['processtype'] = $GLOBALS['_LANG']['surplus_type_1'];

                    if (isset($GLOBALS['_CFG']['user_account_code']) && $GLOBALS['_CFG']['user_account_code'] == '1' && $user_info['mobile_phone'] != '') {
                        $this->commonRepository->smsSend($user_info['mobile_phone'], $smsParams, 'user_account_code', false);
                    }

                    if ($user_info['email'] != '') {
                        $tpl = get_mail_template('user_account_code');

                        $this->smarty->assign('smsParams', $smsParams);
                        $content = $this->smarty->fetch('str:' . $tpl['template_content']);
                        CommonRepository::sendEmail($GLOBALS['_CFG']['shop_name'], $user_info['email'], $tpl['template_subject'], $content, $tpl['is_html']);
                    }
                } elseif ($is_paid == 1 && $account['process_type'] == 0) {
                    //如果是预付款，并且已完成, 更新此条记录，增加相应的余额
                    $this->update_user_account($id, $amount, $admin_note, $is_paid);

                    //更新会员余额数量
                    log_account_change($account['user_id'], $amount, 0, 0, 0, "【" . $GLOBALS['_LANG']['user_name'] . $GLOBALS['_LANG']['surplus_type_0'] . "】" . $admin_note, ACT_SAVING);

                    /* 如果需要，发短信 */
                    //获取完成后的会员余额
                    $smsParams['user_money'] = $user_surplus['user_money'] + $amount;
                    $smsParams['usermoney'] = $user_surplus['user_money'] + $amount;
                    $smsParams['process_type'] = $GLOBALS['_LANG']['surplus_type_0'];
                    $smsParams['processtype'] = $GLOBALS['_LANG']['surplus_type_0'];

                    if (isset($GLOBALS['_CFG']['user_account_code']) && $GLOBALS['_CFG']['user_account_code'] == '1' && $user_info['mobile_phone'] != '') { //添加条件 by wu
                        $this->commonRepository->smsSend($user_info['mobile_phone'], $smsParams, 'user_account_code', false);
                    }

                    if ($user_info['email'] != '') {
                        $tpl = get_mail_template('user_account_code');

                        $this->smarty->assign('smsParams', $smsParams);
                        $content = $this->smarty->fetch('str:' . $tpl['template_content']);
                        CommonRepository::sendEmail($GLOBALS['_CFG']['shop_name'], $user_info['email'], $tpl['template_subject'], $content, $tpl['is_html']);
                    }
                } elseif ($is_paid == 0 || $is_paid == 2) {
                    if ($is_paid == 2) {
                        $set = "is_paid = 2";
                    } else {
                        $set = "is_paid = 0";
                    }

                    /* 否则更新信息 */
                    $sql = "UPDATE " . $this->dsc->table('user_account') . " SET " .
                        "admin_user    = '" . session('admin_name') . "', " .
                        "admin_note    = '$admin_note', " . $set . " WHERE id = '$id'";
                    $this->db->query($sql);
                }

                /* 记录管理员日志 */
                $content = 'process_type_' . $process_type; // 操作内容
                admin_log('(' . addslashes($GLOBALS['_LANG']['check']) . ')' . $admin_note, 'edit', $content);

                /* 提示信息 */
                $link[0]['text'] = $GLOBALS['_LANG']['back_list'];
                $link[0]['href'] = 'user_account.php?act=list&' . list_link_postfix();

                return sys_msg($GLOBALS['_LANG']['attradd_succed'], 0, $link);
            } else {
                return sys_msg($GLOBALS['_LANG']['attradd_failed'], 1);
            }
        }

        /*------------------------------------------------------ */
        //-- ajax帐户信息列表
        /*------------------------------------------------------ */
        elseif ($act == 'query') {

            $list = $this->account_list();
            $this->smarty->assign('list', $list['list']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);
            $this->smarty->assign('sort_add_time', '<img src="' . __TPL__ . '/images/sort_' . strtolower($list['filter']['sort_order']) . '.gif">');

            return make_json_result($this->smarty->fetch('user_account_list.dwt'), '', ['filter' => $list['filter'], 'page_count' => $list['page_count']]);
        }
        /*------------------------------------------------------ */
        //-- ajax删除一条信息
        /*------------------------------------------------------ */
        elseif ($act == 'remove') {
            /* 检查权限 */
            $check_auth = check_authz_json('surplus_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = request()->input('id', 0);

            if (empty($id)) {
                return make_json_error('Hacking attempt');
            }

            $sql = "SELECT u.user_name, u.user_id, ua.process_type, ua.amount, ua.deposit_fee FROM " . $this->dsc->table('users') . " AS u, " .
                $this->dsc->table('user_account') . " AS ua " .
                " WHERE u.user_id = ua.user_id AND ua.id = '$id' AND ua.is_paid = 0 ";
            $account = $this->db->getRow($sql);

            if (empty($account)) {
                return make_json_error('Hacking attempt');
            }

            $sql = "DELETE FROM " . $this->dsc->table('user_account') . " WHERE id = '$id'";
            if ($this->db->query($sql, 'SILENT')) {
                $amount = $account['amount'] ?? 0;
                $process_type = $account['process_type'] ?? 0;
                if ($account['process_type'] == 1) {
                    // 关联删除提现记录扩展信息
                    DB::table('user_account_fields')->where('account_id', $id)->delete();

                    // 删除提现申请 退回用户余额、冻结资金 + 手续费
                    $amount = abs($amount) + abs($account['deposit_fee']);// 取绝对值
                    log_account_change($account['user_id'], $amount, -$amount, 0, 0, lang('admin/common.delete') . $GLOBALS['_LANG']['surplus_type_1'], ACT_DRAWING);
                }

                // 记录操作日志
                $content = 'process_type_' . $process_type; // 操作内容
                admin_log(addslashes($account['user_name']), 'remove', $content);

                $url = 'user_account.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));
                return dsc_header("Location: $url\n");
            } else {
                return make_json_error($this->db->error());
            }
        }
        /*------------------------------------------------------ */
        //-- 会员批量审核
        /*------------------------------------------------------ */
        elseif ($act == 'batch') {
            /* 检查权限 */
            admin_priv('surplus_manage');
            $checkboxes = !empty($_REQUEST['checkboxes']) ? $_REQUEST['checkboxes'] : '';
            $admin_note = "";
            if ($checkboxes) {
                foreach ($checkboxes as $id) {
                    /* 初始化 */
                    $is_paid = 1;

                    /* 查询当前的预付款信息 */
                    $account = $this->db->getRow("SELECT * FROM " . $this->dsc->table('user_account') . " WHERE id = '$id'");

                    $user_surplus = $this->get_user_surplus($account['user_id']);

                    $amount = $account['amount'];
                    $process_type = $account['process_type'];

                    //如果状态为未确认
                    if ($account['is_paid'] == 0) {
                        //获取申请会员信息
                        $sql = "SELECT mobile_phone, email,user_name FROM" . $this->dsc->table('users') . " WHERE user_id = '" . $account['user_id'] . "'";
                        $user_info = $this->db->getRow($sql);

                        //短信接口参数
                        $smsParams = [
                            'user_name' => $user_info['user_name'],
                            'username' => $user_info['user_name'],
                            'user_money' => $user_surplus['user_money'],
                            'usermoney' => $user_surplus['user_money'],
                            'op_time' => TimeRepository::getLocalDate('Y-m-d H:i:s'),
                            'optime' => TimeRepository::getLocalDate('Y-m-d H:i:s'),
                            'add_time' => TimeRepository::getLocalDate('Y-m-d H:i:s', $account['add_time']),
                            'addtime' => TimeRepository::getLocalDate('Y-m-d H:i:s', $account['add_time']),
                            'examine' => $GLOBALS['_LANG']['through'],
                            'fmt_amount' => $amount,
                            'fmtamount' => $amount,
                            'mobile_phone' => $user_info['mobile_phone'] ? $user_info['mobile_phone'] : '',
                            'mobilephone' => $user_info['mobile_phone'] ? $user_info['mobile_phone'] : ''
                        ];
                        //如果是退款申请, 并且已完成,更新此条记录,扣除相应的余额
                        if ($account['process_type'] == '1') {
                            $fmt_amount = str_replace('-', '', $amount);

                            //如果扣除的余额多于此会员拥有的余额，提示
                            if ($fmt_amount > $user_surplus['frozen_money']) {
                                continue;
                            }

                            $this->update_user_account($id, $amount, $admin_note, $is_paid);

                            //更新会员余额数量
                            log_account_change($account['user_id'], 0, $amount + $account['deposit_fee'], 0, 0, $GLOBALS['_LANG']['surplus_type_1'], ACT_DRAWING);
                            /* 如果需要，发短信 */
                            //获取完成后的会员余额
                            $smsParams['user_money'] = $user_surplus['user_money'];
                            $smsParams['usermoney'] = $user_surplus['user_money'];
                            $smsParams['process_type'] = $GLOBALS['_LANG']['surplus_type_1'];
                            $smsParams['processtype'] = $GLOBALS['_LANG']['surplus_type_1'];

                            if (isset($GLOBALS['_CFG']['user_account_code']) && $GLOBALS['_CFG']['user_account_code'] == '1' && $user_info['mobile_phone'] != '') {
                                $this->commonRepository->smsSend($user_info['mobile_phone'], $smsParams, 'user_account_code', false);
                            }
                            if ($user_info['email'] != '') {
                                $tpl = get_mail_template('user_account_code');

                                $this->smarty->assign('smsParams', $smsParams);
                                $content = $this->smarty->fetch('str:' . $tpl['template_content']);
                                CommonRepository::sendEmail($GLOBALS['_CFG']['shop_name'], $user_info['email'], $tpl['template_subject'], $content, $tpl['is_html']);
                            }
                        } elseif ($account['process_type'] == '0') {
                            //如果是预付款，并且已完成, 更新此条记录，增加相应的余额
                            $this->update_user_account($id, $amount, $admin_note, $is_paid);

                            //更新会员余额数量
                            log_account_change($account['user_id'], $amount, 0, 0, 0, $GLOBALS['_LANG']['surplus_type_0'], ACT_SAVING);

                            /* 如果需要，发短信 */
                            //获取完成后的会员余额
                            $smsParams['user_money'] = $user_surplus['user_money'];
                            $smsParams['usermoney'] = $user_surplus['user_money'];
                            $smsParams['process_type'] = $GLOBALS['_LANG']['surplus_type_0'];
                            $smsParams['processtype'] = $GLOBALS['_LANG']['surplus_type_0'];

                            if (isset($GLOBALS['_CFG']['user_account_code']) && $GLOBALS['_CFG']['user_account_code'] == '1' && $user_info['mobile_phone'] != '') {
                                $this->commonRepository->smsSend($user_info['mobile_phone'], $smsParams, 'user_account_code');
                            }

                            if ($user_info['email'] != '') {
                                $tpl = get_mail_template('user_account_code');

                                $this->smarty->assign('smsParams', $smsParams);
                                $content = $this->smarty->fetch('str:' . $tpl['template_content']);
                                CommonRepository::sendEmail($GLOBALS['_CFG']['shop_name'], $user_info['email'], $tpl['template_subject'], $content, $tpl['is_html']);
                            }
                        }

                        /* 记录管理员日志 */
                        $content = 'process_type_' . $process_type; // 操作内容
                        admin_log('(' . addslashes($GLOBALS['_LANG']['check']) . ')' . $admin_note, 'edit', $content);
                    }
                }

                /* 提示信息 */
                $link[0]['text'] = $GLOBALS['_LANG']['back_list'];
                $link[0]['href'] = 'user_account.php?act=list&' . list_link_postfix();

                return sys_msg($GLOBALS['_LANG']['attradd_succed'], 0, $link);
            } else {
                /* 提示信息 */
                $link[0]['text'] = $GLOBALS['_LANG']['back_list'];
                $link[0]['href'] = 'user_account.php?act=list&' . list_link_postfix();

                return sys_msg($GLOBALS['_LANG']['please_take_handle'], 0, $link);
            }
        }
        /*------------------------------------------------------ */
        //-- 导出充值或提现申请（队列）
        /*------------------------------------------------------ */
        elseif ($act == 'export') {
            $filter = request()->post();

            $filter['page_size'] = 100;
            $filter['file_name'] = date('YmdHis') . mt_rand(1000, 9999);
            $type = $filter['process_type'] == 0 ? 'recharge' : 'deposit';
            $adminru = get_admin_ru_id();
            $filter['admin_id'] = $adminru['ru_id'] ?? 0; // 导出操作管理员id

            // 插入导出记录表
            $filter['request_id'] = DB::table('export_history')->insertGetId([
                'ru_id' => $filter['admin_id'],
                'type' => $type,
                'file_name' => $filter['file_name'],
                'file_type' => 'xls',
                'download_params' => json_encode($filter),
                'created_at' => Carbon::now(),
            ]);

            ProcessUserAccountExport::dispatch($filter);

            return make_json_result($type);
        }
    }
    /*------------------------------------------------------ */
    //-- 会员余额函数部分
    /*------------------------------------------------------ */
    /**
     * 查询会员余额的数量
     * @access  public
     * @param int $user_id 会员ID
     * @return  int
     */
    private function get_user_surplus($user_id = 0)
    {
        $sql = "SELECT user_money, frozen_money FROM " . $this->dsc->table('users') .
            " WHERE user_id = '$user_id'";
        $res = $this->db->getRow($sql);

        if (!$res) {
            $res['user_money'] = 0;
            $res['frozen_money'] = 0;
        }

        return $res;
    }


    /**
     * 更新会员账目明细
     *
     * @access  public
     * @param array $id 帐目ID
     * @param array $admin_note 管理员描述
     * @param array $amount 操作的金额
     * @param array $is_paid 是否已完成
     *
     * @return  int
     */
    private function update_user_account($id, $amount, $admin_note, $is_paid)
    {
        $sql = "UPDATE " . $this->dsc->table('user_account') . " SET " .
            "admin_user  = '" . session('admin_name') . "', " .
            "amount      = '$amount', " .
            "paid_time   = '" . TimeRepository::getGmTime() . "', " .
            "admin_note  = '$admin_note', " .
            "is_paid     = '$is_paid' WHERE id = '$id'";
        return $this->db->query($sql);
    }

    /**
     * 账户列表
     *
     * @return array
     */
    private function account_list()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'account_list';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 过滤列表 */
        $filter['user_id'] = !empty($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        $filter['process_type'] = isset($_REQUEST['process_type']) ? intval($_REQUEST['process_type']) : 0;
        $filter['pay_id'] = empty($_REQUEST['pay_id']) ? 0 : intval($_REQUEST['pay_id']);
        $filter['withdraw_type'] = empty($_REQUEST['withdraw_type']) ? 0 : intval($_REQUEST['withdraw_type']);
        $filter['is_paid'] = isset($_REQUEST['is_paid']) ? intval($_REQUEST['is_paid']) : -1;
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['start_date'] = empty($_REQUEST['start_date']) ? '' : local_strtotime($_REQUEST['start_date']);
        $filter['end_date'] = empty($_REQUEST['end_date']) ? '' : (local_strtotime($_REQUEST['end_date']) + 86400);
        $filter['add_start_date'] = empty($_REQUEST['add_start_date']) ? '' : (strpos($_REQUEST['add_start_date'], '-') > 0 ? local_strtotime($_REQUEST['add_start_date']) : $_REQUEST['add_start_date']);
        $filter['add_end_date'] = empty($_REQUEST['add_end_date']) ? '' : (strpos($_REQUEST['add_end_date'], '-') > 0 ? local_strtotime($_REQUEST['add_end_date']) : $_REQUEST['add_end_date']);

        $where = " WHERE 1 ";
        if ($filter['user_id'] > 0) {
            $where .= " AND ua.user_id = '$filter[user_id]' ";
        }
        if ($filter['process_type'] != -1) {
            $where .= " AND ua.process_type = '$filter[process_type]' ";
        } else {
            $where .= " AND ua.process_type " . db_create_in([SURPLUS_SAVE, SURPLUS_RETURN]);
        }
        if ($filter['pay_id'] > 0) {
            $where .= " AND ua.pay_id = '$filter[pay_id]' ";
        }

        if ($filter['withdraw_type'] > 0) {
            $withdraw_type = $filter['withdraw_type'] - 1;
            $where .= " AND uaf.withdraw_type = '$withdraw_type' ";
        }

        if ($filter['is_paid'] != -1) {
            $where .= " AND ua.is_paid = '$filter[is_paid]' ";
        }

        if ($filter['add_start_date']) {
            $where .= " AND ua.add_time >= '$filter[add_start_date]'";
        }
        if ($filter['add_end_date']) {
            $where .= " AND ua.add_time <= '$filter[add_end_date]'";
        }

        if ($filter['keywords']) {
            $keywords = mysql_like_quote($filter['keywords']);

            $user_id = Users::query()->select('user_id')->where(function ($query) use ($keywords) {
                $query->where('user_name', 'like', '%' . $keywords . '%')
                    ->orWhere('email', 'like', '%' . $keywords . '%')
                    ->orWhere('mobile_phone', 'like', '%' . $keywords . '%');
            });
            $user_id = $user_id->pluck('user_id');
            $user_id = BaseRepository::getToArray($user_id);

            $where .= " AND ua.user_id " . db_create_in($user_id);
        }

        /* 　时间过滤　 */
        if (!empty($filter['start_date']) && !empty($filter['end_date'])) {
            $where .= "AND paid_time >= " . $filter['start_date'] . " AND paid_time < '" . $filter['end_date'] . "'";
        }

        $sql = "SELECT COUNT(*) FROM " . $this->dsc->table('user_account') . "AS ua LEFT JOIN " . $this->dsc->table('user_account_fields') . " AS uaf ON ua.id = uaf.account_id" . $where;
        $filter['record_count'] = $this->db->getOne($sql);

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 查询数据 */
        $sql = 'SELECT ua.*,uaf.account_id,uaf.bank_number,uaf.real_name,uaf.withdraw_type FROM ' .
            $this->dsc->table('user_account') . ' AS ua LEFT JOIN ' . $this->dsc->table('user_account_fields') . ' AS uaf ON ua.id = uaf.account_id' .
            $where . "ORDER BY " . $filter['sort_by'] . " " . $filter['sort_order'] . " LIMIT " . $filter['start'] . ", " . $filter['page_size'];

        $filter['keywords'] = stripslashes($filter['keywords']);

        $list = $this->db->getAll($sql);

        if ($list) {

            $user_id = BaseRepository::getKeyPluck($list, 'user_id');
            $userList = UserDataHandleService::userDataList($user_id, ['user_id', 'user_name']);
            $realList = UserDataHandleService::getUsersRealDataList($user_id, ['user_id', 'real_name']);

            foreach ($list as $key => $value) {

                $user = $userList[$value['user_id']] ?? [];
                $value['user_name'] = $user['user_name'] ?? '';

                $real = $realList[$value['user_id']] ?? [];

                $list[$key]['user_name'] = $value['user_name'];

                if (empty($value['real_name'])) {
                    // 兼容旧数据 无提现扩展信息
                    $value['withdraw_type'] = $value['withdraw_type'] ?? 0;
                    if ($value['withdraw_type'] == 0) {
                        $list[$key]['real_name'] = $real['real_name'] ?? '';
                    } else {
                        $list[$key]['real_name'] = $value['user_name'];
                    }
                } else {
                    $list[$key]['real_name'] = $value['real_name'];
                }

                $list[$key]['surplus_amount'] = price_format(abs($value['amount']), false);
                $list[$key]['add_date'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $value['add_time']);
                $list[$key]['process_type_name'] = trans('admin::user_account.surplus_type_' . $value['process_type']);
                $list[$key]['withdraw_type_name'] = trans('admin::user_account.withdraw_type_' . $value['withdraw_type']);

                if (isset($GLOBALS['_CFG']['show_mobile']) && $GLOBALS['_CFG']['show_mobile'] == 0) {
                    $list[$key]['user_name'] = $this->dscRepository->stringToStar($value['user_name']);
                }
            }
        }

        return ['list' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }
}
