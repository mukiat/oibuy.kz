<?php

namespace App\Modules\Seller\Controllers;

use App\Models\AdminMessage;
use App\Models\AdminUser;
use App\Modules\Admin\Services\AdminUser\AdminUserDataHandleService;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Message\MessageManageService;

/**
 *  管理中心管理员留言程序
 */
class MessageController extends InitController
{
    protected $messageManageService;

    public function __construct(
        MessageManageService $messageManageService
    )
    {
        $this->messageManageService = $messageManageService;
    }

    public function index()
    {

        /* act操作项的初始化 */
        $_REQUEST['act'] = trim($_REQUEST['act']);
        if (empty($_REQUEST['act'])) {
            $_REQUEST['act'] = 'list';
        }
        $adminru = get_admin_ru_id();

        $this->smarty->assign('menu_select', ['action' => '10_priv_admin', 'current' => 'admin_message']);

        /*------------------------------------------------------ */
        //-- 留言列表页面
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'list') {
            /* 权限的判断 */
            admin_priv('admin_message');

            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['msg_list']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['send_msg'], 'href' => 'message.php?act=send', 'class' => 'icon-plus']);

            $list = $this->messageManageService->getMessageList();

            $this->smarty->assign('message_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);
            $this->smarty->assign('seller_name', session('seller_name'));

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);


            return $this->smarty->display('message_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 翻页、排序
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'query') {

            $list = $this->messageManageService->getMessageList();

            $this->smarty->assign('message_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);
            $this->smarty->assign('seller_name', session('seller_name'));

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result(
                $this->smarty->fetch('message_list.dwt'),
                '',
                ['filter' => $list['filter'], 'page_count' => $list['page_count']]
            );
        }

        /*------------------------------------------------------ */
        //-- 留言发送页面
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'send') {
            /* 权限的判断 */
            admin_priv('admin_message');

            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['10_priv_admin']);
            /* 获取管理员列表 */
            $admin_list = $this->db->getAll('SELECT user_id, user_name FROM ' . $this->dsc->table('admin_user') . " WHERE parent_id > 0 AND ru_id = '" . $adminru['ru_id'] . "' OR action_list = 'all'");

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['send_msg']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['msg_list'], 'href' => 'message.php?act=list', 'class' => 'icon-reply']);
            $this->smarty->assign('action', 'add');
            $this->smarty->assign('form_act', 'insert');
            $this->smarty->assign('admin_list', $admin_list);


            return $this->smarty->display('message_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 处理留言的发送
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'insert') {
            /* 权限的判断 */
            admin_priv('admin_message');

            $rec_arr = $_POST['receiver_id'];

            /* 向所有管理员发送留言 */
            if ($rec_arr[0] == 0) {
                /* 获取管理员信息 */
                $result = $this->db->query('SELECT user_id FROM ' . $this->dsc->table('admin_user') . 'WHERE user_id !=' . intval(session('seller_id')));
                foreach ($result as $rows) {
                    $sql = "INSERT INTO " . $this->dsc->table('admin_message') . " (sender_id, receiver_id, sent_time, " .
                        "read_time, readed, deleted, title, message) " .
                        "VALUES ('" . session('seller_id') . "', '" . $rows['user_id'] . "', '" . gmtime() . "', " .
                        "0, '0', '0', '$_POST[title]', '$_POST[message]')";
                    $this->db->query($sql);
                }

                /*添加链接*/
                $link[0]['text'] = $GLOBALS['_LANG']['back_list'];
                $link[0]['href'] = 'message.php?act=list';

                $link[1]['text'] = $GLOBALS['_LANG']['continue_send_msg'];
                $link[1]['href'] = 'message.php?act=send';

                return sys_msg($GLOBALS['_LANG']['send_msg'] . "&nbsp;" . $GLOBALS['_LANG']['action_succeed'], 0, $link);

                /* 记录管理员操作 */
                admin_log(admin_log($GLOBALS['_LANG']['send_msg']), 'add', 'admin_message');
            } else {
                /* 如果是发送给指定的管理员 */
                foreach ($rec_arr as $key => $id) {
                    $sql = "INSERT INTO " . $this->dsc->table('admin_message') . " (sender_id, receiver_id, " .
                        "sent_time, read_time, readed, deleted, title, message) " .
                        "VALUES ('" . session('seller_id') . "', '$id', '" . gmtime() . "', " .
                        "'0', '0', '0', '$_POST[title]', '$_POST[message]')";
                    $this->db->query($sql);
                }
                admin_log(addslashes($GLOBALS['_LANG']['send_msg']), 'add', 'admin_message');

                $link[0]['text'] = $GLOBALS['_LANG']['back_list'];
                $link[0]['href'] = 'message.php?act=list';
                $link[1]['text'] = $GLOBALS['_LANG']['continue_send_msg'];
                $link[1]['href'] = 'message.php?act=send';

                return sys_msg($GLOBALS['_LANG']['send_msg'] . "&nbsp;" . $GLOBALS['_LANG']['action_succeed'], 0, $link);
            }
        }
        /*------------------------------------------------------ */
        //-- 留言编辑页面
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'edit') {
            /* 权限的判断 */
            admin_priv('admin_message');

            $id = intval($_REQUEST['id']);

            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_promotion']);

            /* 获取管理员列表 */
            $admin_list = $this->db->getAll('SELECT user_id, user_name FROM ' . $this->dsc->table('admin_user'));

            /* 获得留言数据*/
            $sql = 'SELECT message_id, receiver_id, title, message' .
                'FROM ' . $this->dsc->table('admin_message') . " WHERE message_id='$id'";
            $msg_arr = $this->db->getRow($sql);

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['edit_msg']);
            $this->smarty->assign('action_link', ['href' => 'message.php?act=list', 'text' => $GLOBALS['_LANG']['msg_list']]);
            $this->smarty->assign('form_act', 'update');
            $this->smarty->assign('admin_list', $admin_list);
            $this->smarty->assign('msg_arr', $msg_arr);


            return $this->smarty->display('message_info.dwt');
        } elseif ($_REQUEST['act'] == 'update') {
            /* 权限的判断 */
            admin_priv('admin_message');

            /* 获得留言数据*/
            $msg_arr = $this->db->getRow('SELECT * FROM ' . $this->dsc->table('admin_message') . " WHERE message_id='$_POST[id]'");

            $sql = "UPDATE " . $this->dsc->table('admin_message') . " SET " .
                "title = '$_POST[title]'," .
                "message = '$_POST[message]'" .
                "WHERE sender_id = '$msg_arr[sender_id]' AND sent_time='$msg_arr[send_time]'";
            $this->db->query($sql);

            $link[0]['text'] = $GLOBALS['_LANG']['back_list'];
            $link[0]['href'] = 'message.php?act=list';

            return sys_msg($GLOBALS['_LANG']['edit_msg'] . ' ' . $GLOBALS['_LANG']['action_succeed'], 0, $link);

            /* 记录管理员操作 */
            admin_log(addslashes($GLOBALS['_LANG']['edit_msg']), 'edit', 'admin_message');
        }

        /*------------------------------------------------------ */
        //-- 留言查看页面
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'view') {
            /* 权限的判断 */
            admin_priv('admin_message');

            $msg_id = intval($_REQUEST['id']);

            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['10_priv_admin']);

            /* 获得管理员留言数据 */
            $res = AdminMessage::where('message_id', $msg_id);
            $msg_arr = BaseRepository::getToArrayFirst($res);

            $userIdList = [$msg_arr['sender_id'] ?? 0, $msg_arr['receiver_id'] ?? 0];
            $adminList = AdminUserDataHandleService::getAdminUserDataList($userIdList, ['user_id', 'user_name']);

            $msg_arr['sender_name'] = $adminList[$msg_arr['sender_id']]['user_name'] ?? '';
            $msg_arr['receiver_name'] = $adminList[$msg_arr['receiver_id']]['user_name'] ?? '';

            $msg_arr['title'] = nl2br(htmlspecialchars($msg_arr['title']));
            $msg_arr['message'] = nl2br(htmlspecialchars($msg_arr['message']));
            $msg_arr['sent_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $msg_arr['sent_time']);
            $msg_arr['read_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $msg_arr['read_time']);

            /* 如果还未阅读 */
            if ($msg_arr['readed'] == 0) {
                $msg_arr['read_time'] = gmtime(); //阅读日期为当前日期
                //更新阅读日期和阅读状态
                $sql = "UPDATE " . $this->dsc->table('admin_message') . " SET " .
                    "read_time = '" . $msg_arr['read_time'] . "', " .
                    "readed = '1' " .
                    "WHERE message_id = '$msg_id'";
                $this->db->query($sql);

                $msg_arr['read_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $msg_arr['read_time']);
            }

            //模板赋值，显示
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['view_msg']);
            $this->smarty->assign('action_link', ['href' => 'message.php?act=list', 'text' => $GLOBALS['_LANG']['msg_list']]);
            $this->smarty->assign('admin_user', session('admin_name'));
            $this->smarty->assign('msg_arr', $msg_arr);

            $sender_id = session('seller_id');
            $this->smarty->assign('sender_id', $sender_id);
            $this->smarty->assign('seller_name', session('seller_name'));

            return $this->smarty->display('message_view.dwt');
        }

        /*------------------------------------------------------ */
        //--留言回复页面
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'reply') {
            /* 权限的判断 */
            admin_priv('admin_message');

            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['10_priv_admin']);

            $msg_id = intval($_REQUEST['id']);
            $sender_id = intval($_REQUEST['sender_id']);

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['reply_msg']);
            $this->smarty->assign('action_link', ['href' => 'message.php?act=list', 'text' => $GLOBALS['_LANG']['msg_list']]);

            $this->smarty->assign('action', 'reply');
            $this->smarty->assign('form_act', 're_msg');

            /* 获得留言数据 */
            $res = AdminMessage::where('message_id', $msg_id);
            $msg_arr = BaseRepository::getToArrayFirst($res);

            if ($msg_arr && $sender_id == $msg_arr['sender_id']) {
                $receiver_id = $msg_arr['receiver_id'];
            } else {
                $receiver_id = $msg_arr['sender_id'];
            }

            $receiverInfo = AdminUser::select('user_id', 'user_name')->where('user_id', $receiver_id);
            $receiverInfo = BaseRepository::getToArrayFirst($receiverInfo);

            $msg_arr['user_id'] = $receiverInfo['user_id'] ?? 0;
            $msg_arr['user_name'] = $receiverInfo['user_name'] ?? '';
            $this->smarty->assign('msg_val', $msg_arr);

            return $this->smarty->display('message_info.dwt');
        }

        /*------------------------------------------------------ */
        //--留言回复的处理
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 're_msg') {
            /* 权限的判断 */
            admin_priv('admin_message');

            $sql = "INSERT INTO " . $this->dsc->table('admin_message') . " (sender_id, receiver_id, sent_time, " .
                "read_time, readed, deleted, title, message) " .
                "VALUES ('" . session('seller_id') . "', '$_POST[receiver_id]', '" . gmtime() . "', " .
                "0, '0', '0', '$_POST[title]', '$_POST[message]')";
            $this->db->query($sql);

            $link[0]['text'] = $GLOBALS['_LANG']['back_list'];
            $link[0]['href'] = 'message.php?act=list';

            return sys_msg($GLOBALS['_LANG']['send_msg'] . ' ' . $GLOBALS['_LANG']['action_succeed'], 0, $link);

            /* 记录管理员操作 */
            admin_log(addslashes($GLOBALS['_LANG']['send_msg']), 'add', 'admin_message');
        }

        /*------------------------------------------------------ */
        //-- 批量删除留言记录
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'drop_msg') {
            /* 权限的判断 */
            admin_priv('admin_message');

            if (isset($_POST['checkboxes'])) {
                $count = 0;
                foreach ($_POST['checkboxes'] as $key => $id) {
                    $sql = "UPDATE " . $this->dsc->table('admin_message') . " SET " .
                        "deleted = '1'" .
                        "WHERE message_id = '$id' AND (sender_id='" . session('seller_id') . "' OR receiver_id='" . session('seller_id') . "')";
                    $this->db->query($sql);

                    $count++;
                }

                admin_log('', 'remove', 'admin_message');
                $link[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'message.php?act=list'];
                return sys_msg(sprintf($GLOBALS['_LANG']['batch_drop_success'], $count), 0, $link);
            } else {
                return sys_msg($GLOBALS['_LANG']['no_select_msg'], 1);
            }
        }

        /*------------------------------------------------------ */
        //-- 删除留言
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'remove') {
            /* 权限的判断 */
            admin_priv('admin_message');

            $id = intval($_GET['id']);

            $sql = "UPDATE " . $this->dsc->table('admin_message') . " SET deleted=1 " .
                " WHERE message_id=$id AND (sender_id='" . session('seller_id') . "' OR receiver_id='" . session('seller_id') . "')";
            $this->db->query($sql);

            $url = 'message.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));

            return dsc_header("Location: $url\n");
        }
    }
}
