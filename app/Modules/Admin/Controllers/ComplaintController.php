<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Complaint;
use App\Models\ComplainTitle;
use App\Models\ComplaintTalk;
use App\Models\OrderInfo;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Common\ConfigManageService;
use App\Services\Complaint\ComplaintManageService;

/**
 * 投诉管理
 */
class ComplaintController extends InitController
{
    protected $complaintManageService;
    protected $configManageService;
    protected $dscRepository;

    public function __construct(
        ComplaintManageService $complaintManageService,
        ConfigManageService $configManageService,
        DscRepository $dscRepository
    )
    {
        $this->complaintManageService = $complaintManageService;
        $this->configManageService = $configManageService;
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {

        /* act操作项的初始化 */
        $act = request()->get('act', 'list');
        $adminru = get_admin_ru_id();

        /*------------------------------------------------------ */
        //-- 列表
        /*------------------------------------------------------ */
        if ($act == 'list') {
            admin_priv('complaint');
            //页面赋值
            $this->smarty->assign("ur_here", __('admin::common.13_complaint'));
            $this->smarty->assign('action_link', ['text' => __('admin::common.13_complaint'), 'href' => 'complaint.php?act=list']);
            $this->smarty->assign('action_link1', ['text' => __('admin::complaint.complain_title'), 'href' => 'complaint.php?act=complaint_headline']);
            $this->smarty->assign('action_link2', ['text' => __('admin::common.complain_conf'), 'href' => 'complaint.php?act=complaint_conf']);
            $complaint_list = $this->complaintManageService->get_complaint_list();
            $this->smarty->assign('complaint_list', $complaint_list['list']);
            $this->smarty->assign('filter', $complaint_list['filter']);
            $this->smarty->assign('record_count', $complaint_list['record_count']);
            $this->smarty->assign('page_count', $complaint_list['page_count']);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign("act_type", $act);

            return $this->smarty->display("complaint.dwt");
        }
        /*------------------------------------------------------ */
        //-- Ajax投诉内容
        /*------------------------------------------------------ */
        elseif ($act == 'query') {
            $check_auth = check_authz_json('complaint');
            if ($check_auth !== true) {
                return $check_auth;
            }
            $complaint_list = $this->complaintManageService->get_complaint_list();
            $this->smarty->assign('complaint_list', $complaint_list['list']);
            $this->smarty->assign('filter', $complaint_list['filter']);
            $this->smarty->assign('record_count', $complaint_list['record_count']);
            $this->smarty->assign('page_count', $complaint_list['page_count']);

            return make_json_result(
                $this->smarty->fetch('complaint.dwt'),
                '',
                ['filter' => $complaint_list['filter'], 'page_count' => $complaint_list['page_count']]
            );
        }
        /*------------------------------------------------------ */
        //-- 处理投诉
        /*------------------------------------------------------ */
        elseif ($act == 'view') {
            admin_priv('complaint');
            load_helper('order');
            $complaint_id = request()->get('complaint_id', 0);
            $this->smarty->assign("ur_here", __('admin::complaint.complaint_view'));
            $this->smarty->assign('action_link', ['text' => __('admin::common.13_complaint'), 'href' => 'complaint.php?act=list']);
            $complaint_info = get_complaint_info($complaint_id);

            if (config('shop.show_mobile') == 0 && $complaint_info) {
                $complaint_info['user_name'] = $this->dscRepository->stringToStar($complaint_info['user_name']);
            }

            //获取订单详情
            $order_info = order_info($complaint_info['order_id']);

            if ($order_info) {
                $os = __('admin::complaint.os');
                $ps = __('admin::complaint.ps');
                $ss = __('admin::complaint.ss');
                $order_info['order_goods'] = get_order_goods_toInfo($order_info['order_id']);
                $order_info['status'] = $os[$order_info['order_status']] . ',' . $ps[$order_info['pay_status']] . ',' . $ss[$order_info['shipping_status']];
                //获取聊天记录
                $talk_list = checkTalkView($complaint_id);

                $this->smarty->assign('talk_list', $talk_list);
                $this->smarty->assign("complaint_info", $complaint_info);
            } else {
                return redirect(ADMIN_PATH . '/complaint.php?act=list');
            }

            $this->smarty->assign("order_info", $order_info);
            return $this->smarty->display('complaint_view.dwt');
        }
        /*------------------------------------------------------ */
        //-- 投诉处理
        /*------------------------------------------------------ */
        elseif ($act == 'handle') {
            admin_priv('complaint');

            $complaint_id = request()->get('complaint_id', 0);
            $complaint_state = request()->get('complaint_state', 0);
            $end_handle_messg = request()->get('end_handle_messg', '');

            $ru_id = Complaint::where('complaint_id', $complaint_id)->value('ru_id');
            $ru_id = $ru_id ? $ru_id : 0;

            $time = TimeRepository::getGmTime();

            //投诉通过进行下一步
            if (request()->has('abopt_comp')) {
                if ($complaint_state == 0 && $ru_id == 0) {
                    $order_id = Complaint::where('complaint_id', $complaint_id)->value('order_id');
                    $order_id = $order_id ? $order_id : 0;
                    //冻结订单
                    OrderInfo::where('order_id', $order_id)->update(['is_frozen' => '1']);
                    $complaint_state = 2;
                } else {
                    $complaint_state = $complaint_state + 1;
                }
                $other = [
                    'complaint_state' => $complaint_state,
                    'complaint_handle_time' => $time,
                    'complaint_active' => '1',
                    'admin_id' => session('admin_id')
                ];
                Complaint::where('complaint_id', $complaint_id)->update($other);
            } //关闭交易
            elseif (request()->has('close_comp')) {
                $other = [
                    'complaint_state' => '4',
                    'end_handle_time' => $time,
                    'end_admin_id' => session('admin_id'),
                    'end_handle_messg' => $end_handle_messg
                ];
                Complaint::where('complaint_id', $complaint_id)->update($other);
            }
            $link[0]['text'] = __('admin::complaint.back_info');
            $link[0]['href'] = 'complaint.php?act=view&complaint_id=' . $complaint_id;
            return sys_msg(__('admin::complaint.handle_success'), 0, $link);
        }
        /*------------------------------------------------------ */
        //-- 发布聊天
        /*------------------------------------------------------ */
        elseif ($act == 'talk_release') {
            $check_auth = check_authz_json('complaint');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $result = ['error' => '', 'message' => ''];
            $talk_id = request()->get('talk_id', 0);
            $complaint_id = request()->get('complaint_id', 0);
            $talk_content = request()->get('talk_content', '');
            $type = request()->get('type', 0);

            //执行操作类型  1、刷新，0入库,2隐藏，3显示
            if ($type == 0) {
                $complaint_talk = [
                    'complaint_id' => $complaint_id,
                    'talk_member_id' => $adminru['ru_id'],
                    'talk_member_name' => session('admin_name'),
                    'talk_member_type' => 3,
                    'talk_content' => $talk_content,
                    'talk_time' => TimeRepository::getGmTime(),
                    'view_state' => 'admin'
                ];
                ComplaintTalk::insert($complaint_talk);
            } elseif ($type == 2 || $type == 3) {
                $talk_state = 2;
                if ($type == 3) {
                    $talk_state = 1;
                }
                $complaint_talk = [
                    'talk_state' => $talk_state,
                    'admin_id' => session('admin_id')
                ];
                ComplaintTalk::where('complaint_id', $complaint_id)->where('talk_id', $talk_id)->update($complaint_talk);
            }
            $talk_list = checkTalkView($complaint_id);
            $this->smarty->assign('talk_list', $talk_list);
            $result['content'] = $this->smarty->fetch("library/talk_list.lbi");
            return response()->json($result);
        }
        /*------------------------------------------------------ */
        //-- 删除投诉
        /*------------------------------------------------------ */
        elseif ($act == 'remove') {
            $check_auth = check_authz_json('complaint');
            if ($check_auth !== true) {
                return $check_auth;
            }
            $id = request()->get('id', 0);
            //删除相关图片
            del_complaint_img($id);
            del_complaint_img($id, 'appeal_img');
            //删除相关聊天
            del_complaint_talk($id);
            Complaint::where('complaint_id', $id)->delete();
            $url = 'complaint.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));
            return dsc_header("Location: $url\n");
        }
        /*------------------------------------------------------ */
        //-- 投诉类型
        /*------------------------------------------------------ */
        elseif ($act == 'complaint_headline') {
            admin_priv('complaint');
            $this->smarty->assign("ur_here", __('admin::complaint.complain_title'));
            $this->smarty->assign('action_link', ['text' => __('admin::common.13_complaint'), 'href' => 'complaint.php?act=list']);
            $this->smarty->assign('action_link1', ['text' => __('admin::complaint.complain_title'), 'href' => 'complaint.php?act=complaint_headline']);
            $this->smarty->assign('action_link2', ['text' => __('admin::common.complain_conf'), 'href' => 'complaint.php?act=complaint_conf']);
            $this->smarty->assign('action_link3', ['text' => __('admin::complaint.title_add'), 'href' => 'complaint.php?act=add']);

            $title = $this->complaintManageService->get_complaint_title_list();

            $this->smarty->assign('title_info', $title['list']);
            $this->smarty->assign('filter', $title['filter']);
            $this->smarty->assign('record_count', $title['record_count']);
            $this->smarty->assign('page_count', $title['page_count']);

            $this->smarty->assign('full_page', 1);
            $this->smarty->assign("act_type", $act);

            return $this->smarty->display("complaint_title.dwt");
        }
        /*------------------------------------------------------ */
        //-- AJAX返回
        /*------------------------------------------------------ */
        elseif ($act == 'title_query') {
            $check_auth = check_authz_json('complaint');
            if ($check_auth !== true) {
                return $check_auth;
            }
            $title = $this->complaintManageService->get_complaint_title_list();
            $this->smarty->assign('title_info', $title['list']);
            $this->smarty->assign('filter', $title['filter']);
            $this->smarty->assign('record_count', $title['record_count']);
            $this->smarty->assign('page_count', $title['page_count']);

            return make_json_result(
                $this->smarty->fetch('complaint_title.dwt'),
                '',
                ['filter' => $title['filter'], 'page_count' => $title['page_count']]
            );
        }
        /*------------------------------------------------------ */
        //-- 添加投诉类型
        /*------------------------------------------------------ */
        elseif ($act == 'add' || $act == 'edit') {
            admin_priv('complaint');
            $this->smarty->assign("ur_here", __('admin::complaint.title_add'));
            $this->smarty->assign('action_link', ['text' => __('admin::complaint.complain_title'), 'href' => 'complaint.php?act=complaint_headline']);
            //处理接收数据
            $title_id = request()->get('title_id', 0);

            //初始化处理入口
            if ($act == 'add') {
                $form_action = "insert";
            } else {
                $form_action = "update";
                $complaint_title_info = ComplainTitle::where('title_id', $title_id)->first();
                $this->smarty->assign('complaint_title_info', $complaint_title_info);
            }
            $this->smarty->assign("form_action", $form_action);
            return $this->smarty->display("complaint_title_info.dwt");
        }
        /*------------------------------------------------------ */
        //-- 投诉类型修改/添加
        /*------------------------------------------------------ */
        elseif ($act == 'insert' || $act == 'update') {
            admin_priv('complaint');
            $title_name = request()->get('title_name', '');
            $title_id = request()->get('title_id', 0);
            $title_desc = request()->get('title_desc', '');
            $is_show = quest()->get('is_show', 0);
            if (empty($title_name)) {
                return sys_msg(__('admin::complaint.title_name_null'), 1);
            }
            if (empty($title_desc)) {
                return sys_msg(__('admin::complaint.title_desc_null'), 1);
            }

            if ($act == 'insert') {
                /*检查是否重复*/
                $is_only = ComplainTitle::where('title_name', $title_name)->count();
                if ($is_only > 0) {
                    return sys_msg(sprintf(__('admin::complaint.title_exist'), stripslashes($title_name)), 1);
                }
                $other = [
                    'title_name' => $title_name,
                    'title_desc' => $title_desc,
                    'is_show' => $is_show
                ];
                ComplainTitle::insert($other);
                $link[0]['text'] = __('admin::complaint.continue_add');
                $link[0]['href'] = 'complaint.php?act=add';

                $link[1]['text'] = __('admin::complaint.back_list');
                $link[1]['href'] = 'complaint.php?act=complaint_headline';

                return sys_msg(__('admin::complaint.add_succeed'), 0, $link);
            } else {
                /*检查是否重复*/
                $is_only = ComplainTitle::where('title_name', $title_name)->where('title_id', '!=', $title_id)->count();
                if ($is_only > 0) {
                    return sys_msg(sprintf(__('admin::complaint.title_exist'), stripslashes($title_name)), 1);
                }
                $other = [
                    'title_name' => $title_name,
                    'title_desc' => $title_desc,
                    'is_show' => $is_show
                ];
                ComplainTitle::where('title_id', $title_id)->update($other);
                $link[0]['text'] = __('admin::complaint.back_list');
                $link[0]['href'] = 'complaint.php?act=complaint_headline';

                return sys_msg(__('admin::complaint.edit_succeed'), 0, $link);
            }
        }
        /*------------------------------------------------------ */
        //-- 删除类型
        /*------------------------------------------------------ */
        elseif ($act == 'remove_title') {
            $check_auth = check_authz_json('complaint');
            if ($check_auth !== true) {
                return $check_auth;
            }
            $id = request()->get('id', 0);
            ComplainTitle::where('title_id', $id)->delete();
            $url = 'complaint.php?act=complaint_headline&' . str_replace('act=remove_title', '', request()->server('QUERY_STRING'));
            return dsc_header("Location: $url\n");
        }
        /*------------------------------------------------------ */
        //-- 投诉设置
        /*------------------------------------------------------ */
        elseif ($act == 'complaint_conf') {
            admin_priv('complaint');

            //卖场 start
            if ($adminru['rs_id'] > 0) {
                $url = "complaint.php?act=list";
                return dsc_header("Location: $url\n");
            }
            //卖场 end

            $this->dscRepository->helpersLang('shop_config', 'admin');

            $this->smarty->assign('ur_here', __('admin::common.complain_conf'));
            $this->smarty->assign('action_link', ['text' => __('admin::common.13_complaint'), 'href' => 'complaint.php?act=list']);
            $this->smarty->assign('action_link1', ['text' => __('admin::complaint.complain_title'), 'href' => 'complaint.php?act=complaint_headline']);
            $this->smarty->assign('action_link2', ['text' => __('admin::common.complain_conf'), 'href' => 'complaint.php?act=complaint_conf']);

            $complaint_conf = $this->configManageService->getUpSettings('complaint_conf');
            $this->smarty->assign('report_conf', $complaint_conf);

            $this->smarty->assign("act_type", $act);
            $this->smarty->assign('conf_type', 'complaint_conf');

            return $this->smarty->display('goods_report_conf.dwt');
        }
        /*------------------------------------------------------ */
        //-- 切换是否显示
        /*------------------------------------------------------ */
        elseif ($act == 'toggle_show') {
            $check_auth = check_authz_json('complaint');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = request()->get('id', 0);
            $val = request()->get('val', 0);
            ComplainTitle::where('title_id', $id)->update(['is_show' => $val]);
            clear_cache_files();

            return make_json_result($val);
        }
    }
}
