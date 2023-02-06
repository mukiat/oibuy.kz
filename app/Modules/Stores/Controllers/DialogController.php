<?php

namespace App\Modules\Stores\Controllers;

/**
 * 弹窗管理
 */
class DialogController extends InitController
{
    public function index()
    {

        /*------------------------------------------------------ */
        //-- 删除
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'operate') {
            $result = ['dialog_type' => '', 'app' => '', 'content' => ''];

            $page = isset($_REQUEST['page']) && !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
            $dialog_type = empty($_REQUEST['dialog_type']) ? '' : trim($_REQUEST['dialog_type']);
            $app = empty($_REQUEST['app']) ? '' : trim($_REQUEST['app']);
            $message = empty($_REQUEST['message']) ? '' : trim($_REQUEST['message']);

            $this->smarty->assign("dialog_type", $dialog_type);
            $this->smarty->assign("app", $app);
            $this->smarty->assign("message", $message);
            $this->smarty->assign("page", $page);

            $result['page'] = $page;
            $result['dialog_type'] = $dialog_type;
            $result['app'] = $app;
            $result['content'] = $GLOBALS['smarty']->fetch('dialog.dwt');
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 地图弹出窗口  by kong
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'getmap_html') {
            $result = ['content' => '', 'sgs' => ''];
            $this->smarty->assign("temp", $_REQUEST['act']);
            $result['sgs'] = $_REQUEST['act'];
            $result['content'] = $this->smarty->fetch('library/dialog.lbi');
            return response()->json($result);
        }
    }
}
