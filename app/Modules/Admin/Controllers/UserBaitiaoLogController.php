<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Baitiao;
use App\Models\BaitiaoLog;
use App\Models\BiaotiaoLogChange;
use App\Models\Payment;
use App\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\StrRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Payment\PaymentManageService;
use App\Services\User\UserBaitiaoService;
use Illuminate\Support\Facades\Validator;

/**
 * 白条管理程序
 */
class UserBaitiaoLogController extends InitController
{
    protected $userBaitiaoService;
    protected $dscRepository;

    public function __construct(
        UserBaitiaoService $userBaitiaoService,
        DscRepository $dscRepository
    )
    {
        $this->userBaitiaoService = $userBaitiaoService;
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {
        $act = e(request()->input('act', ''));
        
        /* ------------------------------------------------------ */
        //-- 会员白条列表
        /* ------------------------------------------------------ */
        if ($act == 'list') {
            /* 检查权限 */
            admin_priv('baitiao_manage');

            $baitiao_list = $this->baitiao_list();

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['bt_list']);
            $this->smarty->assign('baitiao_list', $baitiao_list['baitiao_list']);
            $this->smarty->assign('filter', $baitiao_list['filter']);
            $this->smarty->assign('record_count', $baitiao_list['record_count']);
            $this->smarty->assign('page_count', $baitiao_list['page_count']);
            $this->smarty->assign('full_page', 1);

            $this->smarty->assign('action_link', ['href' => 'user_baitiao_log.php?act=bt_add_tp', 'text' => $GLOBALS['_LANG']['baitiao_add']]);
            return $this->smarty->display('baitiao_list.dwt');
        }

        /* ------------------------------------------------------ */
        //-- 白条消费记录
        /* ------------------------------------------------------ */
        elseif ($act == 'log_list') {
            /* 检查权限 */
            admin_priv('baitiao_manage');

            $user_id = (int)request()->input('user_id', 0);

            //会员白条信息
            $bt_other = array(
                'user_id' => $user_id
            );
            $bt_info = $this->userBaitiaoService->getBaitiaoInfo($bt_other);
            $bt_info['format_amount'] = isset($bt_info['amount']) ? price_format($bt_info['amount'], false) : 0;

            //所有待还款白条的总额和条数
            $sql = "SELECT SUM(b.stages_one_price * (b.stages_total - b.yes_num)) AS total_amount, COUNT(log_id) AS numbers , SUM(b.stages_one_price * b.yes_num) AS already_amount FROM " . $this->dsc->table('baitiao_log') . " AS b " .
                " WHERE b.user_id = '$user_id' AND b.is_repay = 0 AND b.is_refund = 0 LIMIT 1";
            $repay_bt = $this->db->getRow($sql);

            $repay_bt['format_total_amount'] = price_format($repay_bt['total_amount'], false);
            $repay_bt['format_already_amount'] = price_format($repay_bt['already_amount'], false);

            $bt_log = $this->get_baitiao_list($user_id);

            $remain_amount = isset($bt_info['amount']) ? floatval($bt_info['amount']) - floatval($repay_bt['total_amount']) : 0 - floatval($repay_bt['total_amount']);
            $format_remain_amount = price_format($remain_amount, false);

            $this->smarty->assign('remain_amount', $remain_amount);
            $this->smarty->assign('format_remain_amount', $format_remain_amount);
            $this->smarty->assign('bt_info', $bt_info);
            $this->smarty->assign('repay_bt', $repay_bt);

            $bt_amount = isset($bt_amount) ? $bt_amount : '';
            $this->smarty->assign('bt_amount', $bt_amount);

            $this->smarty->assign('bt_logs', $bt_log['bt_log']);
            $this->smarty->assign('filter', $bt_log['filter']);
            $this->smarty->assign('record_count', $bt_log['record_count']);
            $this->smarty->assign('page_count', $bt_log['page_count']);
            $this->smarty->assign('full_page', 1);

            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['bt_list'], 'href' => 'user_baitiao_log.php?act=list']);
            $this->smarty->assign('baitiao_page', ['total' => isset($total) ? $total : 0, 'page_total' => isset($page_total) ? $page_total : 0, 'page_one_num' => isset($page_one_num) ? $page_one_num : 0]);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['bt_details']);

            return $this->smarty->display('baitiao_log_list.dwt');
        }

        /* ------------------------------------------------------ */
        //-- 会员白条列表
        /* ------------------------------------------------------ */
        elseif ($act == 'log_list_query') {
            /* 检查权限 */
            admin_priv('baitiao_manage');

            $baitiao_id = (int)request()->input('bt_id', 0);
            $user_id = (int)request()->input('user_id', 0);
            //会员白条信息
            $bt = "SELECT * FROM " . $this->dsc->table('baitiao') . " WHERE user_id='$user_id'";

            $bt_info = $this->db->getRow($bt);
            $bt_info['format_amount'] = isset($bt_info['amount']) ? price_format($bt_info['amount'], false) : 0;
            //待还款总额(不含分期金额)
            $bt_sun = "SELECT SUM(o.order_amount) FROM " . $this->dsc->table('baitiao_log') . " AS b LEFT JOIN " . $this->dsc->table('order_info') . "  AS o ON b.order_id=o.order_id WHERE b.user_id='$user_id' AND b.is_repay=0 AND  b.is_stages<>1  AND is_refund=0 ";
            $repay_sun_amount = $this->db->getOne($bt_sun);

            //所有待还款白条的总额和条数
            $bt_repay = "SELECT SUM(o.order_amount) AS total_amount,COUNT(log_id) AS numbers , SUM(b.stages_one_price * b.yes_num) AS already_amount FROM " . $this->dsc->table('baitiao_log') . " AS b " .
                "LEFT JOIN " . $this->dsc->table('order_info') . "  AS o ON b.order_id=o.order_id " .
                " WHERE b.user_id='$user_id' AND b.is_repay=0 AND is_refund=0";
            $repay_bt = $this->db->getRow($bt_repay);

            $repay_bt['format_total_amount'] = price_format($repay_bt['total_amount'], false);
            $repay_bt['format_already_amount'] = price_format($repay_bt['already_amount'], false);

            $bt_log = $this->get_baitiao_list($user_id);

            $remain_amount = isset($bt_info['amount']) ? floatval($bt_info['amount']) - floatval($repay_bt['total_amount']) : 0 - floatval($repay_bt['total_amount']);
            $format_remain_amount = price_format($remain_amount, false);
            $this->smarty->assign('remain_amount', $remain_amount);
            $this->smarty->assign('format_remain_amount', $format_remain_amount);
            $this->smarty->assign('bt_info', $bt_info);
            $this->smarty->assign('repay_sun_amount', $repay_sun_amount);
            $this->smarty->assign('repay_bt', $repay_bt);

            $bt_amount = isset($bt_amount) ? $bt_amount : '';
            $this->smarty->assign('bt_amount', $bt_amount);

            $this->smarty->assign('bt_logs', $bt_log['bt_log']);
            $this->smarty->assign('filter', $bt_log['filter']);
            $this->smarty->assign('record_count', $bt_log['record_count']);
            $this->smarty->assign('page_count', $bt_log['page_count']);

            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['bt_list'], 'href' => 'user_baitiao_log.php?act=list']);
            $this->smarty->assign('baitiao_page', ['total' => isset($total) ? $total : 0, 'page_total' => isset($page_total) ? $page_total : 0, 'page_one_num' => isset($page_one_num) ? $page_one_num : 0]);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['bt_details']);
            return make_json_result($this->smarty->fetch('baitiao_log_list.dwt'), '', ['filter' => $bt_log['filter'], 'page_count' => $bt_log['page_count']]);
        }

        /* ------------------------------------------------------ */
        //-- 会员白条开通或编辑
        /* ------------------------------------------------------ */

        elseif ($act == 'bt_add_tp') {

            /* 检查权限 */
            admin_priv('baitiao_manage');

            $user_id = (int)request()->input('user_id', 0);
            if ($user_id > 0) {
                //会员信息
                $user_sql = "SELECT user_name,user_id FROM " . $this->dsc->table('users') . " WHERE user_id='$user_id'";
                $user_info = $this->db->getRow($user_sql);

                if (isset($GLOBALS['_CFG']['show_mobile']) && $GLOBALS['_CFG']['show_mobile'] == 0 && isset($user_info['user_name'])) {
                    $user_info['user_name'] = $this->dscRepository->stringToStar($user_info['user_name']);
                }

                $bt_sql = "SELECT b.*,u.user_name,u.user_id FROM " . $this->dsc->table('baitiao') . " AS b LEFT JOIN " . $this->dsc->table('users') . " AS u ON u.user_id=b.user_id WHERE b.user_id='$user_id'";
                $bt_info = $this->db->getRow($bt_sql);

                if (isset($GLOBALS['_CFG']['show_mobile']) && $GLOBALS['_CFG']['show_mobile'] == 0 && isset($bt_info['user_name'])) {
                    $bt_info['user_name'] = $this->dscRepository->stringToStar($bt_info['user_name']);
                }

                $this->assign('action_link2', ['text' => $GLOBALS['_LANG']['03_users_list'], 'href' => 'users.php?act=list']);
                $this->assign('ur_here', $GLOBALS['_LANG']['bt_ur_here']);
            } else {
                // 开通会员白条
                $this->assign('action_link2', ['text' => $GLOBALS['_LANG']['bt_list'], 'href' => 'user_baitiao_log.php?act=list']);
                $this->assign('ur_here', $GLOBALS['_LANG']['baitiao_add']);
            }

            $this->assign('action_link', ['text' => $GLOBALS['_LANG']['baitiao_batch_set'], 'href' => 'baitiao_batch.php?act=add']);
            $this->assign('user_id', $user_id);
            $this->assign('user_info', $user_info ?? []);
            $this->assign('bt_info', $bt_info ?? []);
            return $this->display('admin.user_baitiao_log.baitiao_edit');
        }

        /* ------------------------------------------------------ */
        //-- 设置会员白条金额 提交
        /* ------------------------------------------------------ */
        elseif ($act == 'bt_edit') {
            /* 检查权限 */
            admin_priv('baitiao_manage');

            $user_id = (int)request()->input('user_id', 0);
            $amount = (int)request()->input('amount', 0);
            $amount = empty($amount) ? 0 : floatval($amount);

            $repay_term = (int)request()->input('repay_term', 0);
            $over_repay_trem = (int)request()->input('over_repay_trem', 0);

            if (empty($user_id)) {
                return sys_msg(trans('admin::user_baitiao_log.user_name_required'), 1);
            }

            $data = [
                'amount' => $amount,
                'repay_term' => $repay_term,
                'over_repay_trem' => $over_repay_trem,
                'add_time' => TimeRepository::getGmTime(),
            ];

            $bt_info = Baitiao::query()->where('user_id', $user_id)->select('baitiao_id')->first();

            if ($bt_info) {
                $up_ok = Baitiao::query()->where('baitiao_id', $bt_info->baitiao_id)->update($data);
                if ($up_ok) {
                    $links[] = ['text' => trans('admin/common.go_back'), 'href' => 'user_baitiao_log.php?act=bt_add_tp&user_id=' . $user_id];
                    return sys_msg(trans('admin::user_baitiao_log.baitiao_update_success'), 0, $links);
                }
            } else {
                $data['user_id'] = $user_id;
                $in_ok = Baitiao::query()->insertGetId($data);
                if ($in_ok) {
                    $links[] = ['text' => trans('admin/common.go_back'), 'href' => 'user_baitiao_log.php?act=bt_add_tp&user_id=' . $user_id];
                    return sys_msg(trans('admin::user_baitiao_log.baitiao_set_success'), 0, $links);
                }
            }

            return sys_msg(trans('admin/common.attradd_failed'), 1);
        }

        /* ------------------------------------------------------ */
        //-- 白条列表搜索会员
        /* ------------------------------------------------------ */
        elseif ($act == 'search_user') {

            $user_name = e(request()->input('user_name', ''));

            // 数据验证
            $messages = [
                'user_name.required' => trans('admin::user_baitiao_log.user_name_required'),
            ];
            $validator = Validator::make(request()->all(), [
                'user_name' => 'required'
            ], $messages);

            // 返回错误
            if ($validator->fails()) {
                return response()->json(['error' => 1, 'msg' => $validator->errors()->first()]);
            }

            $model = Users::query()->where('user_name', $user_name)->orWhere('mobile_phone', $user_name);

            $model = $model->select('user_id', 'user_name', 'nick_name', 'user_picture', 'mobile_phone')->first();

            $user = $model ? $model->toArray() : [];

            if (empty($user)) {
                return response()->json(['error' => 1, 'msg' => trans('admin::user_baitiao_log.user_name_not_exist')]);
            }

            $user_baitiao = Baitiao::where('user_id', $user['user_id'])->count();
            if (!empty($user_baitiao)) {
                return response()->json(['error' => 1, 'msg' => trans('admin::user_baitiao_log.user_baitiao_log_exist')]);
            }

            if (!empty($user)) {
                $user['user_picture'] = $this->dscRepository->getImagePath($user['user_picture']);
            }

            return response()->json(['error' => 0, 'msg' => 'success', 'data' => $user]);
        }

        /* ------------------------------------------------------ */
        //-- 白条设置 安装或卸载
        /* ------------------------------------------------------ */
        elseif ($act == 'setting') {

            $pay_code = 'chunsejinrong';

            // 提交
            if (request()->isMethod('POST')) {
                $enabled = intval(request()->input('enabled', 0)); // 0 卸载 1 安装

                /* 调用相应的支付方式文件 */
                $pay_name = StrRepository::studly($pay_code);
                $modules = plugin_path('Payment/' . $pay_name . '/config.php');

                $data = [];
                if (file_exists($modules)) {
                    $data = include_once($modules);
                }

                $pay_name = $GLOBALS['_LANG'][$pay_code];
                $pay_desc = $GLOBALS['_LANG'][$data['desc']];
                $is_cod = $data['is_cod'] ?? 0;
                $pay_fee = $data['pay_fee'] ?? 0;
                $is_online = $data['is_online'] ?? 0;
                $pay_config = [];
                $pay_config = empty($pay_config) ? '' : serialize($pay_config);

                $paymentManageService = app(PaymentManageService::class);

                /* 安装，检查该支付方式是否曾经安装过 */
                $count = $paymentManageService->checkPaymentCount($pay_code);
                if ($count > 0) {
                    /* 该支付方式已经安装过, 将该支付方式的状态设置为 enable */
                    $updata = [
                        'pay_name' => $pay_name,
                        'pay_desc' => $pay_desc,
                        'pay_config' => $pay_config,
                        'pay_fee' => $pay_fee,
                        'enabled' => $enabled,
                        'is_online' => $is_online
                    ];
                    $paymentManageService->updatePayment($pay_code, $updata);
                } else {
                    /* 该支付方式没有安装过, 将该支付方式的信息添加到数据库 */
                    $other = [
                        'pay_code' => $pay_code,
                        'pay_name' => $pay_name,
                        'pay_desc' => $pay_desc,
                        'pay_config' => $pay_config,
                        'is_cod' => $is_cod,
                        'pay_fee' => $pay_fee,
                        'enabled' => 1,
                        'is_online' => $is_online
                    ];
                    $paymentManageService->createPayment($other);
                }

                return response()->json(['error' => 0, 'msg' => 'success']);
            }

            $this->assign('ur_here', trans('admin::user_baitiao_log.set_baitiao'));

            // 是否安装白条支付
            $payment = Payment::where('pay_code', $pay_code)->select('pay_name', 'enabled')->first();
            $payment = $payment ? $payment->toArray() : [];

            $enabled = $payment['enabled'] ?? 0;
            $this->assign('enabled', $enabled);
            return $this->display('admin.user_baitiao_log.baitiao_setting');
        }

        /* ------------------------------------------------------ */
        //-- ajax返回白条列表
        /* ------------------------------------------------------ */
        elseif ($act == 'query') {
            //检查权限
            $check_auth = check_authz_json('baitiao_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $baitiao_list = $this->baitiao_list();

            $this->smarty->assign('baitiao_list', $baitiao_list['baitiao_list']);
            $this->smarty->assign('filter', $baitiao_list['filter']);
            $this->smarty->assign('record_count', $baitiao_list['record_count']);
            $this->smarty->assign('page_count', $baitiao_list['page_count']);

            $sort_flag = sort_flag($baitiao_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result($this->smarty->fetch('baitiao_list.dwt'), '', ['filter' => $baitiao_list['filter'], 'page_count' => $baitiao_list['page_count']]);
        }
        /* ------------------------------------------------------ */
        //-- 批量删除白条
        /* ------------------------------------------------------ */
        elseif ($act == 'batch_remove') {
            /* 检查权限 */
            admin_priv('baitiao_manage');

            $baitiao_id_arr = request()->input('checkboxes', []);

            if (empty($baitiao_id_arr) || !is_array($baitiao_id_arr)) {
                return sys_msg(trans('admin::common.not_select_data'), 1);
            }

            $del_ok = Baitiao::whereIn('baitiao_id', $baitiao_id_arr)->delete();
            if ($del_ok) {
                $lnk[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'user_baitiao_log.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['baitiao_remove_success'], 0, $lnk);
            }
        }
        /* ------------------------------------------------------ */
        //-- 批量删除白条消费记录
        /* ------------------------------------------------------ */
        elseif ($act == 'batch_remove_log') {
            /* 检查权限 */
            admin_priv('baitiao_manage');

            $baitiao_id_arr = request()->input('checkboxes', []);

            if (empty($baitiao_id_arr) || !is_array($baitiao_id_arr)) {
                return sys_msg(trans('admin::common.not_select_data'), 1);
            }

            $sql = "SELECT log_id,is_repay,baitiao_id,user_id FROM " . $this->dsc->table('baitiao_log') . " WHERE log_id " . db_create_in(join(',', $baitiao_id_arr));
            $bt_log = $this->db->getAll($sql);

            if ($bt_log) {
                $no_del_num = 0;
                foreach ($bt_log as $key => $val) {
                    if ($val['is_repay']) {
                        $del_ok = BaitiaoLog::where('is_repay', 1)->where('log_id', $val['log_id'])->delete();
                    } else {
                        $no_del_num++;
                    }
                }

                if ($no_del_num > 0) {
                    $lnk[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'user_baitiao_log.php?act=log_list&bt_id=' . $bt_log[0]['baitiao_id'] . '&user_id=' . $bt_log[0]['user_id']];
                    return sys_msg($GLOBALS['_LANG']['you'] . $no_del_num . $GLOBALS['_LANG']['no_del_num_notic'], 0, $lnk);
                } else {
                    $lnk[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'user_baitiao_log.php?act=log_list&bt_id=' . $bt_log[0]['baitiao_id'] . '&user_id=' . $bt_log[0]['user_id']];
                    return sys_msg($GLOBALS['_LANG']['remove_consume_success'], 0, $lnk);
                }
            } else {
                return sys_msg(trans('admin::common.not_select_data'), 1);
            }
        }
        /* ------------------------------------------------------ */
        //-- 删除会员白条
        /* ------------------------------------------------------ */
        elseif ($act == 'remove') {
            /* 检查权限 */
            admin_priv('baitiao_manage');

            $baitiao_id = (int)request()->input('baitiao_id', 0);

            if (empty($baitiao_id)) {
                return sys_msg(trans('admin::common.not_select_data'), 1);
            }

            $del_ok = Baitiao::where('baitiao_id', $baitiao_id)->delete();
            if ($del_ok) {
                /* 记录管理员操作 */
                admin_log($baitiao_id, 'remove', 'baitiao');

                /* 提示信息 */
                $link[] = ['text' => trans('admin/common.go_back'), 'href' => 'user_baitiao_log.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['baitiao_remove_success'], 0, $link);
            }
        }

        /*------------------------------------------------------ */
        //-- 修改白条分期金额
        /*------------------------------------------------------ */
        elseif ($act == 'edit_stages_one_price') {
            $check_auth = check_authz_json('baitiao_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $log_id = (int)request()->input('id', 0);
            $stages_one_price = request()->input('val', 0);

            if (empty($log_id)) {
                return sys_msg(trans('admin::common.not_select_data'), 1);
            }

            $stages_one_price = floatval($stages_one_price);
            $stages_one_price = number_format($stages_one_price, 2, '.', '');

            $original_price = BaitiaoLog::where('log_id', $log_id)->value('stages_one_price');
            $original_price = $original_price ? $original_price : 0;

            BaitiaoLog::where('log_id', $log_id)->update(['stages_one_price' => $stages_one_price]);

            if ($stages_one_price != $original_price) {
                BiaotiaoLogChange::insert([
                    'log_id' => $log_id,
                    'original_price' => $original_price,
                    'chang_price' => $stages_one_price,
                    'add_time' => TimeRepository::getGmTime()
                ]);
            }

            return make_json_result($stages_one_price);
        }

        /* ------------------------------------------------------ */
        //-- 删除会员白条消费记录
        /* ------------------------------------------------------ */
        elseif ($act == 'remove_log') {
            /* 检查权限 */
            admin_priv('baitiao_manage');

            $sql = "SELECT log_id,is_repay,baitiao_id,user_id,is_refund FROM " . $this->dsc->table('baitiao_log') . " WHERE log_id = '" . $_GET['log_id'] . "'";
            $bt_log = $this->db->getRow($sql);
            if ($bt_log['log_id'] > 0) {
                if ($bt_log['is_repay'] || $bt_log['is_refund']) {//已退款白条订单页可以删除;
                    $sql = "DELETE FROM " . $this->dsc->table('baitiao_log') . " WHERE is_repay=1 OR is_refund=1 AND log_id = '" . $bt_log['log_id'] . "'";
                    $del_ok = $this->db->query($sql);
                    $log_id = $this->db->insert_id();

                    if ($del_ok) {
                        /* 记录管理员操作 */
                        admin_log(addslashes($log_id), 'remove', 'baitiao_log');

                        /* 提示信息 */
                        $link[] = ['text' => trans('admin/common.go_back'), 'href' => 'user_baitiao_log.php?act=log_list&bt_id=' . $bt_log['baitiao_id'] . '&user_id=' . $bt_log['user_id']];
                        return sys_msg($GLOBALS['_LANG']['biao_remove_consume_success'], 0, $link);
                    }
                } else {
                    /* 提示信息 */
                    $link[] = ['text' => trans('admin/common.go_back'), 'href' => 'user_baitiao_log.php?act=log_list&bt_id=' . $bt_log['baitiao_id'] . '&user_id=' . $bt_log['user_id']];
                    return sys_msg($GLOBALS['_LANG']['pay_not_consume_remove'], 0, $link);
                }
            }
        }
    }

    /**
     *  返回会员白条列表数据
     *
     * @access  public
     * @param
     *
     * @return array
     */
    private function baitiao_list()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'baitiao_list';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 过滤条件 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : e($_REQUEST['keywords']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'baitiao_id' : e($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : e($_REQUEST['sort_order']);

        $ex_where = ' WHERE 1 ';
        if ($filter['keywords']) {
            $ex_where .= " AND u.user_name LIKE '%" . mysql_like_quote($filter['keywords']) . "%'";
        }
        $filter['record_count'] = $this->db->getOne("SELECT COUNT(*) FROM " . $this->dsc->table('baitiao') . "AS b LEFT JOIN " . $this->dsc->table('users') . " AS u ON b.user_id=u.user_id " . $ex_where);

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $sql = "SELECT b.*,u.user_name " . " FROM " . $this->dsc->table('baitiao') . "as b left join " . $this->dsc->table('users') . " as u on b.user_id=u.user_id " . $ex_where .
            " ORDER by " . $filter['sort_by'] . ' ' . $filter['sort_order'] .
            " LIMIT " . $filter['start'] . ',' . $filter['page_size'];

        $baitiao_list = $this->db->getAll($sql);

        if ($baitiao_list) {
            foreach ($baitiao_list as $key => $row) {
                if (isset($GLOBALS['_CFG']['show_mobile']) && $GLOBALS['_CFG']['show_mobile'] == 0) {
                    $baitiao_list[$key]['user_name'] = $this->dscRepository->stringToStar($row['user_name']);
                }
            }
        }

        $arr = ['baitiao_list' => $baitiao_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /**
     * @param int $user_id
     * @return array
     */
    private function get_baitiao_list($user_id = 0)
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'get_baitiao_list';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : e($_REQUEST['keywords']);
        if ($user_id > 0) {
            $filter['user_id'] = $user_id;
        }

        $ex_where = ' WHERE 1 ';
        if ($filter['user_id'] > 0) {
            $ex_where .= " AND b.user_id = '" . mysql_like_quote($filter['user_id']) . "'";
        }
        $filter['record_count'] = $this->db->getOne("SELECT COUNT(b.log_id) AS total FROM " . $this->dsc->table('baitiao_log') . " AS b LEFT JOIN " . $this->dsc->table('order_info') . "  AS o ON b.order_id=o.order_id  $ex_where ORDER BY b.log_id DESC");

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $sql = "SELECT b.*,o.order_sn, o.money_paid AS order_amount FROM " . $this->dsc->table('baitiao_log') . " AS b LEFT JOIN " . $this->dsc->table('order_info') . "  AS o ON b.order_id=o.order_id  $ex_where ORDER BY b.log_id DESC" .
            " LIMIT " . $filter['start'] . ',' . $filter['page_size'];

        $bt_log = $this->db->getAll($sql);
        if ($bt_log) {
            foreach ($bt_log as $key => $val) {
                $bt_log[$key]['use_date'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $bt_log[$key]['use_date']);
                //如果是白条分期付款商品;
                if ($val['is_stages'] == 1) {
                    //分期付款的还款日期;
                    $bt_log[$key]['repay_date'] = unserialize($bt_log[$key]['repay_date']);
                } else {
                    $bt_log[$key]['repay_date'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $bt_log[$key]['repay_date']);
                }
                if ($bt_log[$key]['repayed_date']) {
                    $bt_log[$key]['repayed_date'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $bt_log[$key]['repayed_date']);
                }
            }
        }
        $arr = ['bt_log' => $bt_log, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }
}
