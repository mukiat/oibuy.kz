<?php

namespace App\Modules\Admin\Controllers;

use App\Custom\CrossBorder\Services\CrossWarehouseService;
use App\Libraries\Image;
use App\Models\CrossWarehouse;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;

class CrossWarehouseController extends InitController
{
    private $crossWarehouseService;
    private $dscRepository;

    public function __construct(
        CrossWarehouseService $crossWarehouseService,
        DscRepository $dscRepository
    )
    {
        $this->crossWarehouseService = $crossWarehouseService;
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {
        $image = new Image(['bgcolor' => $GLOBALS['_CFG']['bgcolor']]);

        $act = isset($_REQUEST['act']) && !empty($_REQUEST['act']) ? addslashes($_REQUEST['act']) : 'list';

        /*------------------------------------------------------ */
        //-- 国家列表页面
        /*------------------------------------------------------ */
        if ($act == 'list') {

            admin_priv('cross_warehouse_manage');

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['cross_warehouse_list']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['add_cross_warehouse'], 'href' => 'cross_warehouse.php?act=add']);
            $this->smarty->assign('full_page', 1);

            $cross_warehouse_list = $this->crossWarehouseService->getCrossWarehouseList();
            $this->smarty->assign('cross_warehouse_list', $cross_warehouse_list['list']);
            $this->smarty->assign('filter', $cross_warehouse_list['filter']);
            $this->smarty->assign('record_count', $cross_warehouse_list['record_count']);
            $this->smarty->assign('page_count', $cross_warehouse_list['page_count']);

            $sort_flag = sort_flag($cross_warehouse_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return $this->smarty->display('cross_warehouse_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 排序、分页、查询
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'query') {

            $cross_warehouse_list = $this->crossWarehouseService->getCrossWarehouseList();

            $this->smarty->assign('cross_warehouse_list', $cross_warehouse_list['list']);
            $this->smarty->assign('filter', $cross_warehouse_list['filter']);
            $this->smarty->assign('record_count', $cross_warehouse_list['record_count']);
            $this->smarty->assign('page_count', $cross_warehouse_list['page_count']);

            $sort_flag = sort_flag($cross_warehouse_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result(
                $this->smarty->fetch('cross_warehouse_list.dwt'),
                '',
                ['filter' => $cross_warehouse_list['filter'], 'page_count' => $cross_warehouse_list['page_count']]
            );
        }

        /*------------------------------------------------------ */
        //-- 添加跨境仓库页面
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'add') {
            admin_priv('cross_warehouse_manage');

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['add_cross_warehouse']);
            $this->smarty->assign('action_link', ['href' => 'cross_warehouse.php?act=list', 'text' => $GLOBALS['_LANG']['cross_warehouse_list']]);

            $this->smarty->assign('form_act', 'insert');
            $this->smarty->assign('action', 'add');
            $this->smarty->assign('cfg_lang', $GLOBALS['_CFG']['lang']);

            return $this->smarty->display('cross_warehouse_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 新跨境仓库信息的处理
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'insert') {
            admin_priv('cross_warehouse_manage');

            $name = !empty($_POST['name']) ? trim($_POST['name']) : '';

            if (empty($name)) {
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                return sys_msg($GLOBALS['_LANG']['cross_warehouse_name_empty'], 0, $link);
            }

            /* 查看广告名称是否有重复 */
            $count = CrossWarehouse::where('name', $name)->count();
            if ($count > 0) {
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                return sys_msg($GLOBALS['_LANG']['cross_warehouse_name_exist'], 0, $link);
            }

            CrossWarehouse::insert([
                'name' => $name,
                'add_time' => TimeRepository::getGmTime()
            ]);

            /* 记录管理员操作 */
            admin_log($name, 'add', 'cross_warehouse');

            /* 提示信息 */
            $link[0]['text'] = $GLOBALS['_LANG']['back_cross_warehouse_list'];
            $link[0]['href'] = 'cross_warehouse.php?act=list';

            $link[1]['text'] = $GLOBALS['_LANG']['add_cross_warehouse'];
            $link[1]['href'] = 'cross_warehouse.php?act=add';
            return sys_msg($GLOBALS['_LANG']['add'] . "&nbsp;" . $name . "&nbsp;" . $GLOBALS['_LANG']['attradd_succed'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 编辑新广告页面
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'edit') {
            admin_priv('cross_warehouse_manage');

            $id = !empty($_REQUEST['id']) ? intval($_POST['id']) : 0;

            $cross_warehouse = $this->crossWarehouseService->cross_warehouse_info($id);
            $this->smarty->assign('cross_warehouse', $cross_warehouse);

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['edit_cross_warehouse']);
            $this->smarty->assign('action_link', ['href' => 'cross_warehouse.php?act=list', 'text' => $GLOBALS['_LANG']['cross_warehouse_list']]);

            $this->smarty->assign('form_act', 'update');
            $this->smarty->assign('action', 'edit');
            $this->smarty->assign('cfg_lang', $GLOBALS['_CFG']['lang']);

            return $this->smarty->display('cross_warehouse_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 跨境仓库信息编辑处理
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'update') {
            admin_priv('cross_warehouse_manage');

            $id = !empty($_REQUEST['id']) ? intval($_POST['id']) : 0;
            $name = !empty($_POST['name']) ? trim($_POST['name']) : '';

            /* 查看广告名称是否有重复 */
            $count = CrossWarehouse::where('name', $name)->where('id', '<>', $id)->count();
            if ($count > 0) {
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                return sys_msg($GLOBALS['_LANG']['cross_warehouse_name_exist'], 0, $link);
            }

            $other = [
                'name' => $name
            ];

            CrossWarehouse::where('id', $id)->update($other);

            /* 记录管理员操作 */
            admin_log($name, 'edit', 'cross_warehouse');

            /* 提示信息 */
            $link[0]['text'] = $GLOBALS['_LANG']['back_cross_warehouse_list'];
            $link[0]['href'] = 'cross_warehouse.php?act=list';

            $link[1]['text'] = $GLOBALS['_LANG']['edit_cross_warehouse'];
            $link[1]['href'] = 'cross_warehouse.php?act=edit' . '&id=' . $id;
            return sys_msg($GLOBALS['_LANG']['edit'] . "&nbsp;" . $name . "&nbsp;" . $GLOBALS['_LANG']['attradd_succed'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 删除跨境仓库信息
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'remove') {
            $check_auth = check_authz_json('cross_warehouse_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_GET['id'] ?? 0);

            CrossWarehouse::where('id', $id)->delete();

            admin_log('', 'remove', 'cross_warehouse');

            $url = 'cross_warehouse.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));

            return dsc_header("Location: $url\n");
        }
    }
}