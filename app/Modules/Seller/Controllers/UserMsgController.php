<?php

namespace App\Modules\Seller\Controllers;

use App\Libraries\Exchange;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\TimeRepository;

/**
 * 客户留言
 */
class UserMsgController extends InitController
{
    protected $commonRepository;

    public function __construct(
        CommonRepository $commonRepository
    ) {
        $this->commonRepository = $commonRepository;
    }

    public function index()
    {

        /* 权限判断 */
        admin_priv('feedback_priv');
        /*初始化数据交换对象 */
        $exc = new Exchange($this->dsc->table("feedback"), $this->db, 'msg_id', 'msg_title');
        $menus = session('menus', '');
        $this->smarty->assign('menus', $menus);
        $this->smarty->assign('action_type', "users");
        /*------------------------------------------------------ */
        //-- 发送留言
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'add') {
            $this->smarty->assign('menu_select', ['action' => '04_order', 'current' => '02_order_list']);

            $user_id = empty($_GET['user_id']) ? 0 : intval($_GET['user_id']);
            $order_id = empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
            $order_sn = $this->db->getOne("SELECT order_sn FROM " . $this->dsc->table('order_info') . " WHERE order_id = '$order_id'");

            /* 获取关于订单所有信息 */
            $sql = "SELECT msg_id, user_name, msg_title, msg_type, msg_time, msg_content" .
                " FROM " . $this->dsc->table('feedback') .
                " WHERE user_id ='$user_id' AND order_id = '$order_id'";

            $msg_list = $this->db->getAll($sql);
            foreach ($msg_list as $key => $val) {
                $msg_list[$key]['msg_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $val['msg_time']);
            }


            $this->smarty->assign('ur_here', sprintf($GLOBALS['_LANG']['msg_for_order'], $order_sn));
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['order_detail'], 'href' => 'order.php?act=info&order_id=' . $order_id]);
            $this->smarty->assign('msg_list', $msg_list);
            $this->smarty->assign('order_id', $order_id);
            $this->smarty->assign('user_id', $user_id);
            return $this->smarty->display('msg_add.dwt');
        }

        if ($_REQUEST['act'] == 'insert') {
            $sql = "INSERT INTO " . $this->dsc->table('feedback') . "(parent_id, user_id, user_name, user_email, msg_title, msg_type, msg_content, msg_time, message_img, order_id)" .
                " VALUES (0, '$_POST[user_id]', '" . session('seller_name') . "', ' ', " .
                " '$_POST[msg_title]', 5, '$_POST[msg_content]', '" . gmtime() . "', '', '$_POST[order_id]')";

            $this->db->query($sql);

            return dsc_header("Location: user_msg.php?act=add&order_id=$_POST[order_id]&user_id=$_POST[user_id]\n");
        }

        if ($_REQUEST['act'] == 'remove_msg') {
            $msg_id = empty($_GET['msg_id']) ? 0 : intval($_GET['msg_id']);
            $order_id = empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
            $user_id = empty($_GET['user_id']) ? 0 : intval($_GET['user_id']);
            $sql = "SELECT user_id, order_id, message_img FROM " . $this->dsc->table('feedback') . " WHERE msg_id='$msg_id'";
            $row = $this->db->getRow($sql);
            if ($row) {
                if ($row['user_id'] == $user_id && $row['order_id'] == $order_id) {
                    if ($row['message_img']) {
                        @unlink(storage_public(DATA_DIR . '/feedbackimg/' . $row['message_img']));
                    }
                    $sql = "DELETE FROM " . $this->dsc->table('feedback') . " WHERE msg_id=$msg_id LIMIT 1";
                    $this->db->query($sql);
                }
            }

            return dsc_header("Location: user_msg.php?act=add&order_id=$_GET[order_id]&user_id=$_GET[user_id]\n");
        }
        /*------------------------------------------------------ */
        //-- 更新留言的状态为显示或者        禁止
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'check') {
            if ($_REQUEST['check'] == 'allow') {
                /* 允许留言显示 */
                $sql = "UPDATE " . $this->dsc->table('feedback') . " SET msg_status = 1 WHERE msg_id = '$_REQUEST[id]'";
                $this->db->query($sql);

                /* 清除缓存 */
                clear_cache_files();

                return dsc_header("Location: user_msg.php?act=view&id=$_REQUEST[id]\n");
            } else {
                /* 禁止留言显示 */
                $sql = "UPDATE " . $this->dsc->table('feedback') . " SET msg_status = 0 WHERE msg_id = '$_REQUEST[id]'";
                $this->db->query($sql);

                /* 清除缓存 */
                clear_cache_files();

                return dsc_header("Location: user_msg.php?act=view&id=$_REQUEST[id]\n");
            }
        }
        /*------------------------------------------------------ */
        //-- 列出所有留言
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'list_all') {
            $this->smarty->assign('menu_select', ['action' => '08_members', 'current' => '08_unreply_msg']);

            $msg_list = $this->msg_list();

            $page = isset($_REQUEST['page']) && !empty(intval($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;
            $page_count_arr = seller_page($msg_list, $page);

            $this->smarty->assign('page_count_arr', $page_count_arr);
            $this->smarty->assign('msg_list', $msg_list['msg_list']);
            $this->smarty->assign('filter', $msg_list['filter']);
            $this->smarty->assign('record_count', $msg_list['record_count']);
            $this->smarty->assign('page_count', $msg_list['page_count']);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('sort_msg_id', '<img src="__TPL__/images/sort_desc.gif">');

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['08_unreply_msg']);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('current', 'user_msg');
            return $this->smarty->display('msg_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- ajax显示留言列表
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'query') {
            $msg_list = $this->msg_list();

            $page = isset($_REQUEST['page']) && !empty(intval($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;
            $page_count_arr = seller_page($msg_list, $page);

            $this->smarty->assign('page_count_arr', $page_count_arr);
            $this->smarty->assign('msg_list', $msg_list['msg_list']);
            $this->smarty->assign('filter', $msg_list['filter']);
            $this->smarty->assign('record_count', $msg_list['record_count']);
            $this->smarty->assign('page_count', $msg_list['page_count']);

            $sort_flag = sort_flag($msg_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);
            $this->smarty->assign('current', 'user_msg');
            return make_json_result($this->smarty->fetch('msg_list.dwt'), '', ['filter' => $msg_list['filter'], 'page_count' => $msg_list['page_count']]);
        }
        /*------------------------------------------------------ */
        //-- ajax 删除留言
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'remove') {
            $msg_id = intval($_REQUEST['id']);

            /* 检查权限 */
            $check_auth = check_authz_json('feedback_priv');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $msg_title = $exc->get_name($msg_id);
            $img = $exc->get_name($msg_id, 'message_img');
            if ($exc->drop($msg_id)) {
                /* 删除图片 */
                if (!empty($img)) {
                    @unlink(storage_public(DATA_DIR . '/feedbackimg/' . $img));
                }
                $sql = "DELETE FROM " . $this->dsc->table('feedback') . " WHERE parent_id = '$msg_id' LIMIT 1";
                $this->db->query($sql, 'SILENT');

                admin_log(addslashes($msg_title), 'remove', 'message');
                $url = 'user_msg.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));
                return dsc_header("Location: $url\n");
            } else {
                return make_json_error($this->db->error());
            }
        }

        /*------------------------------------------------------ */
        //-- 批量操作删除、�        �许显示、禁止显示用户评论
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'batch') {
            admin_priv('feedback_priv');
            $action = isset($_POST['sel_action']) ? trim($_POST['sel_action']) : 'def';

            if (isset($_POST['checkboxes'])) {
                switch ($action) {
                    case 'remove':
                        $this->db->query("DELETE FROM " . $this->dsc->table('feedback') . " WHERE " . db_create_in($_POST['checkboxes'], 'msg_id'));
                        $this->db->query("DELETE FROM " . $this->dsc->table('feedback') . " WHERE " . db_create_in($_POST['checkboxes'], 'parent_id'));
                        break;

                    case 'allow':
                        $this->db->query("UPDATE " . $this->dsc->table('feedback') . " SET msg_status = 1  WHERE " . db_create_in($_POST['checkboxes'], 'msg_id'));
                        break;

                    case 'deny':
                        $this->db->query("UPDATE " . $this->dsc->table('feedback') . " SET msg_status = 0,msg_area =1  WHERE " . db_create_in($_POST['checkboxes'], 'msg_id'));
                        break;

                    default:
                        break;
                }

                clear_cache_files();
                $action = ($action == 'remove') ? 'remove' : 'edit';
                admin_log('', $action, 'adminlog');

                $link[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'user_msg.php?act=list_all'];
                return sys_msg(sprintf($GLOBALS['_LANG']['batch_drop_success'], count($_POST['checkboxes'])), 0, $link);
            } else {
                /* 提示信息 */
                $link[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'user_msg.php?act=list_all'];
                return sys_msg($GLOBALS['_LANG']['no_select_comment'], 0, $link);
            }
        }


        /*------------------------------------------------------ */
        //-- 回复留言
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'view') {
            $this->smarty->assign('menu_select', ['action' => '08_members', 'current' => '08_unreply_msg']);
            $this->smarty->assign('send_fail', !empty($_REQUEST['send_ok']));
            $this->smarty->assign('msg', $this->get_feedback_detail(intval($_REQUEST['id'])));
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['reply']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['08_unreply_msg'], 'href' => 'user_msg.php?act=list_all']);


            $this->smarty->assign('current', 'user_msg');
            return $this->smarty->display('msg_info.dwt');
        } elseif ($_REQUEST['act'] == 'action') {
            if (empty($_REQUEST['parent_id'])) {
                $sql = "INSERT INTO " . $this->dsc->table('feedback') . " (msg_title, msg_time, user_id, user_name , " .
                    "user_email, parent_id, msg_content) " .
                    "VALUES ('reply', '" . gmtime() . "', '" . session('seller_id') . "', " .
                    "'" . session('seller_name') . "', '" . $_POST['user_email'] . "', " .
                    "'" . $_REQUEST['msg_id'] . "', '" . $_POST['msg_content'] . "') ";
                $this->db->query($sql);
            } else {
                $sql = "UPDATE " . $this->dsc->table('feedback') . " SET user_email = '" . $_POST['user_email'] . "', msg_content='" . $_POST['msg_content'] . "', msg_time = '" . gmtime() . "' WHERE msg_id = '" . $_REQUEST['parent_id'] . "'";
                $this->db->query($sql);
            }

            /* 邮件通知处理流程 */
            if (!empty($_POST['send_email_notice']) or isset($_POST['remail'])) {
                //获取邮件中的必要内容
                $sql = 'SELECT user_name, user_email, msg_title, msg_content ' .
                    'FROM ' . $this->dsc->table('feedback') .
                    " WHERE msg_id ='$_REQUEST[msg_id]'";
                $message_info = $this->db->getRow($sql);

                /* 设置留言回复模板所需要的内容信息 */
                $template = get_mail_template('user_message');
                $message_content = $message_info['msg_title'] . "\r\n" . $message_info['msg_content'];

                $this->smarty->assign('user_name', $message_info['user_name']);
                $this->smarty->assign('message_note', $_POST['msg_content']);
                $this->smarty->assign('message_content', $message_content);
                $this->smarty->assign('shop_name', "<a href='" . $this->dsc->seller_url() . "'>" . $GLOBALS['_CFG']['shop_name'] . '</a>');
                $this->smarty->assign('send_date', date('Y-m-d'));

                $content = $this->smarty->fetch('str:' . $template['template_content']);

                /* 发送邮件 */
                if (CommonRepository::sendEmail($message_info['user_name'], $message_info['user_email'], $template['template_subject'], $content, $template['is_html'])) {
                    $send_ok = 0;
                } else {
                    $send_ok = 1;
                }
            }

            return dsc_header("Location: ?act=view&id=" . $_REQUEST['msg_id'] . "&send_ok=$send_ok\n");
        }

        /*------------------------------------------------------ */
        //-- 删除会员上传的文件
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'drop_file') {
            /* 删除上传的文件 */
            $file = $_GET['file'];
            @unlink('../' . DATA_DIR . '/feedbackimg/' . $file);

            /* 更新数据库 */
            $this->db->query("UPDATE " . $this->dsc->table('feedback') . " SET message_img = '' WHERE msg_id = '$_GET[id]'");

            return dsc_header("Location: user_msg.php?act=view&amp;id=" . $_GET['id'] . "\n");
        }
    }

    /**
     *
     *
     * @access  public
     * @param
     *
     * @return void
     */
    private function msg_list()
    {
        /* 过滤条件 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }
        $filter['msg_type'] = isset($_REQUEST['msg_type']) ? intval($_REQUEST['msg_type']) : -1;
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'f.msg_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = '';
        if ($filter['keywords']) {
            $where .= " AND f.msg_title LIKE '%" . mysql_like_quote($filter['keywords']) . "%' ";
        }
        if ($filter['msg_type'] != -1) {
            $where .= " AND f.msg_type = '$filter[msg_type]' ";
        }

        $sql = "SELECT count(*) FROM " . $this->dsc->table('feedback') . " AS f" .
            " WHERE parent_id = '0' " . $where;
        $filter['record_count'] = $this->db->getOne($sql);

        /* 分页大小 */
        $filter = page_and_size($filter);

        $sql = "SELECT f.msg_id, f.user_name, f.msg_title, f.msg_type, f.order_id, f.msg_status, f.msg_time, f.msg_area, COUNT(r.msg_id) AS reply " .
            "FROM " . $this->dsc->table('feedback') . " AS f " .
            "LEFT JOIN " . $this->dsc->table('feedback') . " AS r ON r.parent_id=f.msg_id " .
            "WHERE f.parent_id = 0 $where " .
            "GROUP BY f.msg_id " .
            "ORDER by $filter[sort_by] $filter[sort_order] " .
            "LIMIT " . $filter['start'] . ', ' . $filter['page_size'];

        $msg_list = $this->db->getAll($sql);
        foreach ($msg_list as $key => $value) {
            if ($value['order_id'] > 0) {
                $msg_list[$key]['order_sn'] = $this->db->getOne("SELECT order_sn FROM " . $this->dsc->table('order_info') . " WHERE order_id= " . $value['order_id']);
            }
            $msg_list[$key]['msg_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $value['msg_time']);
            $msg_list[$key]['msg_type'] = $GLOBALS['_LANG']['type'][$value['msg_type']];
        }
        $filter['keywords'] = stripslashes($filter['keywords']);
        $arr = ['msg_list' => $msg_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /**
     * 获得留言的详细信息
     *
     * @param integer $id
     *
     * @return  array
     */
    private function get_feedback_detail($id)
    {
        $sql = "SELECT T1.*, T2.msg_id AS reply_id, T2.user_name  AS reply_name, u.email AS reply_email, " .
            "T2.msg_content AS reply_content , T2.msg_time AS reply_time, T2.user_name AS reply_name " .
            "FROM " . $this->dsc->table('feedback') . " AS T1 " .
            "LEFT JOIN " . $this->dsc->table('admin_user') . " AS u ON u.user_id='" . session('seller_id') . "' " .
            "LEFT JOIN " . $this->dsc->table('feedback') . " AS T2 ON T2.parent_id=T1.msg_id " .
            "WHERE T1.msg_id = '$id'";
        $msg = $this->db->GetRow($sql);

        if ($msg) {
            $msg['msg_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $msg['msg_time']);
            $msg['reply_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $msg['reply_time']);
        }

        return $msg;
    }
}
