<?php

namespace App\Modules\Admin\Controllers;

use App\Models\CosConfigure;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;

/**
 * CosConfigureController
 */
class CosConfigureController extends InitController
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    ) {
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {
        /* act操作项的初始化 */
        if (empty($_REQUEST['act'])) {
            $_REQUEST['act'] = 'list';
        } else {
            $_REQUEST['act'] = trim($_REQUEST['act']);
        }

        /* 检查权限 */
        admin_priv('cos_configure');

        $this->smarty->assign('menu_select', ['action' => '01_system', 'current' => 'cos_configure']);
        /*------------------------------------------------------ */
        //-- OSS Bucket列表
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'list') {
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['02_oss_add'], 'href' => 'cos_configure.php?act=add']);

            /* 模板赋值 */
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['cos_configure']);
            $this->smarty->assign('form_act', 'insert');

            $bucket_list = $this->bucketList();

            $this->smarty->assign('bucket_list', $bucket_list['bucket_list']);
            $this->smarty->assign('filter', $bucket_list['filter']);
            $this->smarty->assign('record_count', $bucket_list['record_count']);
            $this->smarty->assign('page_count', $bucket_list['page_count']);
            $this->smarty->assign('full_page', 1);

            /* 列表页面 */

            return $this->smarty->display('cos_configure_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- ajax返回Bucket列表
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'query') {
            $bucket_list = $this->bucketList();

            $this->smarty->assign('bucket_list', $bucket_list['bucket_list']);
            $this->smarty->assign('filter', $bucket_list['filter']);
            $this->smarty->assign('record_count', $bucket_list['record_count']);
            $this->smarty->assign('page_count', $bucket_list['page_count']);

            $sort_flag = sort_flag($bucket_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result($this->smarty->fetch('cos_configure_list.dwt'), '', ['filter' => $bucket_list['filter'], 'page_count' => $bucket_list['page_count']]);
        }

        /*------------------------------------------------------ */
        //-- OSS 添加Bucket
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'add') {
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['01_oss_list'], 'href' => 'cos_configure.php?act=list']);

            $bucket['regional'] = 'shanghai';
            $this->smarty->assign('bucket', $bucket);

            /* 模板赋值 */
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['cos_configure']);
            $this->smarty->assign('form_act', 'insert');

            /* 列表页面 */

            return $this->smarty->display('cos_configure_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- OSS 编辑Bucket
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'edit') {
            $id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);

            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['01_oss_list'], 'href' => 'cos_configure.php?act=list']);

            $bucket_info = CosConfigure::where('id', $id);
            $bucket_info = BaseRepository::getToArrayFirst($bucket_info);

            $this->smarty->assign('bucket', $bucket_info);

            /* 模板赋值 */
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['cos_configure']);
            $this->smarty->assign('form_act', 'update');

            /* 列表页面 */

            return $this->smarty->display('cos_configure_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- OSS 添加Bucket
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update') {
            $id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);


            $other['bucket'] = empty($_POST['bucket']) ? '' : trim($_POST['bucket']);
            $other['app_id'] = empty($_POST['app_id']) ? '' : trim($_POST['app_id']);
            $other['secret_id'] = empty($_POST['secret_id']) ? '' : trim($_POST['secret_id']);
            $other['secret_key'] = empty($_POST['secret_key']) ? '' : trim($_POST['secret_key']);
            $other['is_cname'] = empty($_POST['is_cname']) ? '' : intval($_POST['is_cname']);
            $other['endpoint'] = empty($_POST['endpoint']) ? '' : trim($_POST['endpoint']);
            $other['regional'] = empty($_POST['regional']) ? '' : trim($_POST['regional']);
            $other['port'] = empty($_POST['port']) ? '' : trim($_POST['port']);
            $other['is_use'] = empty($_POST['is_use']) ? '' : intval($_POST['is_use']);

            $count = CosConfigure::where('bucket', $other['bucket']);

            if ($id > 0) {
                $count = $count->where('id', '<>', $id);
            }

            $count = $count->count();

            if ($count > 0) {
                return sys_msg($GLOBALS['_LANG']['add_failure'], 1);
            }

            if ($other['is_use'] == 1) {
                CosConfigure::whereRaw(1)->update([
                    'is_use' => 0
                ]);
            }

            if (cache()->has('cos_bucket_info')) {
                cache()->forget('cos_bucket_info');
            }

            if ($id) {
                CosConfigure::where('id', $id)->update($other);
                $href = 'cos_configure.php?act=edit&id=' . $id;

                $lang_name = $GLOBALS['_LANG']['edit_success'];
            } else {
                CosConfigure::insert($other);
                $href = 'cos_configure.php?act=list';
                $lang_name = $GLOBALS['_LANG']['add_success'];
            }

            /* 提示信息 */
            $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => $href];
            return sys_msg(sprintf($lang_name, htmlspecialchars(stripslashes($other['bucket']))), 0, $link);
        }

        /*------------------------------------------------------ */
        //-- OSS 批量删除Bucket
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'batch_remove') {
            if (isset($_REQUEST['checkboxes'])) {
                $checkboxes = BaseRepository::getExplode($_REQUEST['checkboxes']);
                CosConfigure::whereIn('id', $checkboxes)->delete();

                /* 提示信息 */
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'cos_configure.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['remove_success'], 0, $link);
            } else {

                /* 提示信息 */
                $lnk[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'cos_configure.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['no_select_user'], 0, $lnk);
            }
        }

        /*------------------------------------------------------ */
        //-- OSS 删除Bucket
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'remove') {
            $id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);

            $bucket = CosConfigure::where('id', $id)->value('bucket');
            $bucket = $bucket ? $bucket : '';

            CosConfigure::where('id', $id)->delete();

            /* 提示信息 */
            $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'cos_configure.php?act=list'];
            return sys_msg(sprintf($GLOBALS['_LANG']['remove_success'], $bucket), 0, $link);
        }
    }

    /**
     *  返回bucket列表数据
     *
     * @access  public
     * @param
     *
     * @return void
     */
    public function bucketList()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'bucketList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 过滤条件 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter['record_count'] = CosConfigure::count();
        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $res = CosConfigure::orderBy($filter['sort_by'], $filter['sort_order'])
            ->offset($filter['start'])
            ->limit($filter['page_size']);
        $bucket_list = BaseRepository::getToArrayGet($res);

        $count = count($bucket_list);

        for ($i = 0; $i < $count; $i++) {
            $bucket_list[$i]['port'] = $bucket_list[$i]['port'] ?? '';

            if ($bucket_list[$i]['port']) {
                $port = ':' . $bucket_list[$i]['port'];
            } else {
                $port = '/';
            }

            $bucket_list[$i]['endpoint'] = $this->dsc->http() . $bucket_list[$i]['bucket'] . '.cos.' . $bucket_list[$i]['regional'] . '.myqcloud.com' . $port;
            $bucket_list[$i]['regional_name'] = $GLOBALS['_LANG'][$bucket_list[$i]['regional']];
        }

        $arr = ['bucket_list' => $bucket_list, 'filter' => $filter,
            'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }
}
