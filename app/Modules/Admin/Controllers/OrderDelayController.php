<?php

namespace App\Modules\Admin\Controllers;

use App\Models\OrderDelayed;
use App\Models\OrderInfo;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Common\ConfigManageService;

/**
 * 延迟收货
 */
class OrderDelayController extends InitController
{
    protected $configManageService;
    protected $dscRepository;

    public function __construct(
        ConfigManageService $configManageService,
        DscRepository $dscRepository
    ) {
        $this->configManageService = $configManageService;
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {
        /*------------------------------------------------------ */
        //-- 延迟收货申请列表
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'list') {
            /* 检查权限 */
            admin_priv('order_delayed');

            $order_delay_list = get_order_delayed_list();
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['order_delay_apply']);
            $this->smarty->assign('order_delay_list', $order_delay_list['order_delay_list']);
            $this->smarty->assign('filter', $order_delay_list['filter']);
            $this->smarty->assign('record_count', $order_delay_list['record_count']);
            $this->smarty->assign('page_count', $order_delay_list['page_count']);
            $this->smarty->assign('full_page', 1);

            return $this->smarty->display('order_delayed_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- ajax
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'query') {
            //检查权限
            $check_auth = check_authz_json('order_delayed');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $order_delay_list = get_order_delayed_list();

            $this->smarty->assign('order_delay_list', $order_delay_list['order_delay_list']);
            $this->smarty->assign('filter', $order_delay_list['filter']);
            $this->smarty->assign('record_count', $order_delay_list['record_count']);
            $this->smarty->assign('page_count', $order_delay_list['page_count']);

            $sort_flag = sort_flag($order_delay_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result($this->smarty->fetch('order_delayed_list.dwt'), '', ['filter' => $order_delay_list['filter'], 'page_count' => $order_delay_list['page_count']]);
        }
        /*------------------------------------------------------ */
        //-- 批量操作 延迟收货
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'batch') {
            /* 检查权限 */
            admin_priv('order_delayed');

            if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes'])) {
                return sys_msg($GLOBALS['_LANG']['not_select_data'], 1);
            }
            $delay_id_arr = !empty($_POST['checkboxes']) ? $_POST['checkboxes'] : [];
            $review_status = !empty($_POST['review_status']) ? intval($_POST['review_status']) : 0;

            if (isset($_POST['type'])) {
                // 删除
                if ($_POST['type'] == 'batch_remove') {
                    $delay_id_arr = BaseRepository::getExplode($delay_id_arr);
                    $res = OrderDelayed::whereIn('delayed_id', $delay_id_arr)->delete();

                    if ($res > 0) {
                        /* 记录日志 */
                        admin_log('', 'batch_remove', 'order_delayed');

                        $lnk[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'order_delay.php?act=list'];
                        return sys_msg($GLOBALS['_LANG']['remove_delay_info_success'], 0, $lnk);
                    }
                } // 审核
                elseif ($_POST['type'] == 'review_to') {
                    // review_status = 0未审核 1审核通过 2审核未通过

                    // 查询是否有已审核的订单
                    $delay_id_arr = BaseRepository::getExplode($delay_id_arr);

                    $res = OrderDelayed::whereIn('delayed_id', $delay_id_arr);
                    $res = $res->with([
                        'getOrder' => function ($query) {
                            $query->select('order_id', 'order_sn');
                        }
                    ]);
                    $ald_review = BaseRepository::getToArrayGet($res);

                    if (empty($ald_review)) {
                        return sys_msg($GLOBALS['_LANG']['not_select_data'], 1);
                    }

                    $msj_order = '';
                    foreach ($ald_review as $key => $value) {
                        $value['order_sn'] = $value['get_order']['order_sn'] ?? '';

                        //判断是否审核通过
                        if ($value['review_status'] > 0) {
                            $id_key = array_search($value['delayed_id'], $delay_id_arr);
                            unset($delay_id_arr[$id_key]);

                            return sys_msg($GLOBALS['_LANG']['please_select_no_audit'], 1);
                        }
                        //判断是否设置天数
                        if ($value['apply_day'] == 0 && $review_status == 1) {
                            $msj_order .= !empty($value['order_sn']) ? "," . $value['order_sn'] : $value['order_sn'];

                            $id_key = array_search($value['delayed_id'], $delay_id_arr);
                            unset($delay_id_arr[$id_key]);
                        }
                    }

                    $time = gmtime();
                    $data = [
                        'review_status' => $review_status,
                        'review_time' => $time,
                        'review_admin' => session('admin_id')
                    ];
                    $delay_id_arr = BaseRepository::getExplode($delay_id_arr);
                    $res = OrderDelayed::whereIn('delayed_id', $delay_id_arr)->update($data);

                    if ($res > 0) {
                        // 更新订单表的确认收货天数
                        $res = OrderDelayed::whereIn('delayed_id', $delay_id_arr);
                        $order_id_list = BaseRepository::getToArrayGet($res);

                        foreach ($order_id_list as $key => $value) {
                            OrderInfo::where('order_id', $value['order_id'])->increment('auto_delivery_time', $value['apply_day']);
                        }

                        /* 记录日志 */
                        admin_log('', 'batch_edit', 'order_delayed');

                        $lnk[] = ['text' => $GLOBALS['_LANG']['back'], 'href' => 'order_delay.php?act=list'];
                        $message = $GLOBALS['_LANG']['order_delay_set_success'];
                        if ($msj_order) {
                            $message = $GLOBALS['_LANG']['order_set_info_one'] . $msj_order . $GLOBALS['_LANG']['order_set_info_two'];
                            return sys_msg($message, 1, $lnk);
                        }
                        return sys_msg($message, 0, $lnk);
                    }
                }
            }
        }
        /*------------------------------------------------------ */
        //-- 修改申请天数
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'edit_apply_day') {
            $check_auth = check_authz_json('order_delayed');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_POST['id']);
            $val = intval($_POST['val']);

            if (empty($val)) {
                return make_json_error($GLOBALS['_LANG']['order_delay_conf'] . $GLOBALS['_LANG']['order_set_info_two']);
            }

            $res = OrderDelayed::where('delayed_id', $id)->update([
                'apply_day' => $val
            ]);

            if ($res > 0) {
                clear_cache_files();
                return make_json_result(stripslashes($val));
            }
        }
        /*------------------------------------------------------ */
        //-- 投诉设置
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'complaint_conf') {
            admin_priv('order_delayed');
            $this->dscRepository->helpersLang('shop_config', 'admin');

            $this->smarty->assign("ur_here", $GLOBALS['_LANG']['order_delay_conf']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['order_delay_apply'], 'href' => 'order_delay.php?act=list']);
            $this->smarty->assign('action_link2', ['text' => $GLOBALS['_LANG']['order_delay_conf'], 'href' => 'order_delay.php?act=complaint_conf']);

            $order_delay = $this->configManageService->getUpSettings('order_delay');
            $this->smarty->assign('report_conf', $order_delay);

            $this->smarty->assign("act_type", $_REQUEST['act']);
            $this->smarty->assign('conf_type', 'order_delay');

            return $this->smarty->display('goods_report_conf.dwt');
        }
    }
}
