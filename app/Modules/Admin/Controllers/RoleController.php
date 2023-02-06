<?php

namespace App\Modules\Admin\Controllers;

use App\Models\AdminAction;
use App\Models\AdminUser;
use App\Models\Role;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Services\Role\RoleManageService;

/**
 * 角色管理信息以及权限管理程序
 */
class RoleController extends InitController
{
    protected $dscRepository;

    protected $roleManageService;
    protected $sessionRepository;

    public function __construct(
        DscRepository $dscRepository,
        RoleManageService $roleManageService,
        SessionRepository $sessionRepository
    ) {
        $this->dscRepository = $dscRepository;

        $this->roleManageService = $roleManageService;
        $this->sessionRepository = $sessionRepository;
    }

    public function index()
    {

        /* act操作项的初始化 */
        $act = e(request()->input('act', 'login'));

        /*------------------------------------------------------ */
        //-- 退出登录
        /*------------------------------------------------------ */
        if ($act == 'logout') {
            /* 清除cookie */
            $list = [
                'ecscp_admin_id',
                'ecscp_admin_pass'
            ];

            $this->sessionRepository->deleteCookie($list);

            session()->flush();

            $act = 'login';
        }

        /*------------------------------------------------------ */
        //-- 登陆界面
        /*------------------------------------------------------ */
        if ($act == 'login') {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Cache-Control: no-cache, must-revalidate");
            header("Pragma: no-cache");

            if ((intval($GLOBALS['_CFG']['captcha']) & CAPTCHA_ADMIN) && gd_version() > 0) {
                $this->smarty->assign('gd_version', gd_version());
                $this->smarty->assign('random', mt_rand());
            }

            return $this->smarty->display('login.dwt');
        }


        /*------------------------------------------------------ */
        //-- 角色列表页面
        /*------------------------------------------------------ */
        elseif ($act == 'list') {
            /* 模板赋值 */
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['admin_list']);
            $this->smarty->assign('action_link', ['href' => 'role.php?act=add', 'text' => $GLOBALS['_LANG']['admin_add_role']]);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('admin_list', $this->roleManageService->getRoleList());

            /* 显示页面 */
            return $this->smarty->display('role_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 查询
        /*------------------------------------------------------ */
        elseif ($act == 'query') {
            $this->smarty->assign('admin_list', $this->roleManageService->getRoleList());

            return make_json_result($this->smarty->fetch('role_list.dwt'));
        }

        /*------------------------------------------------------ */
        //-- 添加角色页面
        /*------------------------------------------------------ */
        elseif ($act == 'add') {
            /* 检查权限 */
            admin_priv('admin_manage');

            $this->dscRepository->helpersLang('priv_action', 'admin');

            $priv_str = '';

            /* 获取权限的分组数据 */
            $res = AdminAction::where('parent_id', 0);
            $res = BaseRepository::getToArrayGet($res);

            foreach ($res as $rows) {
                //卖场 start
                if ($rows['action_code'] == 'region_store') {
                    continue;
                }
                //卖场 end

                //店铺后台 小商店 start
                if ($rows['action_code'] == 'seller_wxshop') {
                    continue;
                }
                //店铺后台 小商店 end

                //批发 start
                if (!file_exists(SUPPLIERS) && $rows['seller_show'] == 2) {
                    continue;
                }
                //批发 end

                // 微信通
                if (!file_exists(MOBILE_WECHAT) && $rows['action_code'] == 'wechat') {
                    continue;
                }
                // 微分销
                if (!file_exists(MOBILE_DRP) && $rows['action_code'] == 'drp') {
                    continue;
                }

                // 微信小程序
                if (!file_exists(MOBILE_WXAPP) && $rows['action_code'] == 'wxapp') {
                    continue;
                }

                // 拼团
                if (!file_exists(MOBILE_TEAM) && $rows['action_code'] == 'team') {
                    continue;
                }

                // 砍价
                if (!file_exists(MOBILE_BARGAIN) && $rows['action_code'] == 'bargain_manage') {
                    continue;
                }

                $priv_arr[$rows['action_id']] = $rows;
            }

            if ($priv_arr) {
                /* 按权限组查询底级的权限名称 */
                $res = AdminAction::whereIn('parent_id', array_keys($priv_arr));
                $result = BaseRepository::getToArrayGet($res);

                foreach ($result as $priv) {
                    $priv_arr[$priv["parent_id"]]["priv"][$priv["action_code"]] = $priv;
                }

                // 将同一组的权限使用 "," 连接起来，供JS全选
                foreach ($priv_arr as $action_id => $action_group) {
                    if (isset($action_group['priv']) && $action_group['priv']) {
                        $priv = @array_keys($action_group['priv']);
                        $priv_arr[$action_id]['priv_list'] = implode(',', $priv);

                        foreach ($action_group['priv'] as $key => $val) {
                            if (!empty(trim($priv_str)) && !empty($val['action_code'])) {
                                $true = (strpos($priv_str, $val['action_code']) !== false || $priv_str == 'all') ? 1 : 0;
                            } else {
                                $true = 0;
                            }

                            $priv_arr[$action_id]['priv'][$key]['cando'] = $true;
                        }
                    }
                }
            }

            /* 模板赋值 */
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['admin_add_role']);
            $this->smarty->assign('action_link', ['href' => 'role.php?act=list', 'text' => $GLOBALS['_LANG']['admin_list_role']]);
            $this->smarty->assign('form_act', 'insert');
            $this->smarty->assign('action', 'add');
            $this->smarty->assign('lang', $GLOBALS['_LANG']);
            $this->smarty->assign('priv_arr', $priv_arr);

            /* 显示页面 */

            return $this->smarty->display('role_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 添加角色的处理
        /*------------------------------------------------------ */
        elseif ($act == 'insert') {
            admin_priv('admin_manage');
            $user_name = isset($_POST['user_name']) ? trim($_POST['user_name']) : '';
            $action_code = isset($_POST['action_code']) ? $_POST['action_code'] : [];
            $role_describe = isset($_POST['role_describe']) ? trim($_POST['role_describe']) : '';

            /* 转入权限分配列表 */
            $act_list = @join(",", $action_code);
            $data = [
                'role_name' => $user_name,
                'action_list' => $act_list,
                'role_describe' => $role_describe
            ];
            Role::insert($data);

            /* 记录管理员操作 */
            admin_log($user_name, 'add', 'role');

            /*添加链接*/
            $link[0]['text'] = $GLOBALS['_LANG']['admin_list_role'];
            $link[0]['href'] = 'role.php?act=list';

            return sys_msg($GLOBALS['_LANG']['add'] . "&nbsp;" . $user_name . "&nbsp;" . $GLOBALS['_LANG']['action_succeed'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 编辑角色信息
        /*------------------------------------------------------ */
        elseif ($act == 'edit') {
            $this->dscRepository->helpersLang('priv_action', 'admin');

            $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

            /* 获得该管理员的权限 */
            $priv_str = Role::where('role_id', $id)->value('action_list');
            $priv_str = $priv_str ? $priv_str : '';

            /* 查看是否有权限编辑其他管理员的信息 */
            if (session('admin_id') != $id) {
                admin_priv('admin_manage');
            }

            /* 获取角色信息 */
            $res = Role::where('role_id', $id);
            $user_info = BaseRepository::getToArrayFirst($res);

            /* 获取权限的分组数据 */
            $res = AdminAction::where('parent_id', 0);
            $res = BaseRepository::getToArrayGet($res);

            foreach ($res as $rows) {
                //卖场 start
                if ($rows['action_code'] == 'region_store') {
                    continue;
                }
                //卖场 end

                //店铺后台 小商店 start
                if ($rows['action_code'] == 'seller_wxshop') {
                    continue;
                }
                //店铺后台 小商店 end

                //批发 start
                if (!file_exists(SUPPLIERS) && $rows['seller_show'] == 2) {
                    continue;
                }
                //批发 end

                // 微信通
                if (!file_exists(MOBILE_WECHAT) && $rows['action_code'] == 'wechat') {
                    continue;
                }
                // 微分销
                if (!file_exists(MOBILE_DRP) && $rows['action_code'] == 'drp') {
                    continue;
                }

                // 微信小程序
                if (!file_exists(MOBILE_WXAPP) && $rows['action_code'] == 'wxapp') {
                    continue;
                }

                // 拼团
                if (!file_exists(MOBILE_TEAM) && $rows['action_code'] == 'team') {
                    continue;
                }

                // 砍价
                if (!file_exists(MOBILE_BARGAIN) && $rows['action_code'] == 'bargain_manage') {
                    continue;
                }

                $priv_arr[$rows['action_id']] = $rows;
            }

            /* 按权限组查询底级的权限名称 */
            $res = AdminAction::whereIn('parent_id', array_keys($priv_arr));
            $result = BaseRepository::getToArrayGet($res);

            foreach ($result as $priv) {
                $priv_arr[$priv["parent_id"]]["priv"][$priv["action_code"]] = $priv;
            }

            // 将同一组的权限使用 "," 连接起来，供JS全选
            foreach ($priv_arr as $action_id => $action_group) {
                if (isset($action_group['priv']) && $action_group['priv']) {
                    $priv = @array_keys($action_group['priv']);
                    $priv_arr[$action_id]['priv_list'] = implode(',', $priv);

                    foreach ($action_group['priv'] as $key => $val) {
                        if (!empty(trim($priv_str)) && !empty($val['action_code'])) {
                            $true = (strpos($priv_str, $val['action_code']) !== false || $priv_str == 'all') ? 1 : 0;
                        } else {
                            $true = 0;
                        }

                        $priv_arr[$action_id]['priv'][$key]['cando'] = $true;
                    }
                }
            }

            /* 模板赋值 */

            $this->smarty->assign('user', $user_info);
            $this->smarty->assign('form_act', 'update');
            $this->smarty->assign('action', 'edit');
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['admin_edit_role']);
            $this->smarty->assign('action_link', ['href' => 'role.php?act=list', 'text' => $GLOBALS['_LANG']['admin_list_role']]);
            $this->smarty->assign('lang', $GLOBALS['_LANG']);
            $this->smarty->assign('priv_arr', $priv_arr);
            $this->smarty->assign('user_id', $id);

            return $this->smarty->display('role_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 更新角色信息
        /*------------------------------------------------------ */
        elseif ($act == 'update') {
            /* 更新管理员的权限 */
            $user_name = isset($_POST['user_name']) ? trim($_POST['user_name']) : '';
            $action_code = isset($_POST['action_code']) ? $_POST['action_code'] : [];
            $role_describe = isset($_POST['role_describe']) ? trim($_POST['role_describe']) : '';
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

            $act_list = @join(",", $action_code);

            $data = [
                'role_name' => $user_name,
                'action_list' => $act_list,
                'role_describe' => $role_describe
            ];
            Role::where('role_id', $id)->update($data);

            $data = ['action_list' => $act_list];
            AdminUser::where('role_id', $id)->update($data);

            /* 记录管理员操作 */
            admin_log($user_name, 'edit', 'role');

            /* 提示信息 */
            $link[] = ['text' => $GLOBALS['_LANG']['back_admin_list'], 'href' => 'role.php?act=list'];
            return sys_msg($GLOBALS['_LANG']['edit'] . "&nbsp;" . $user_name . "&nbsp;" . $GLOBALS['_LANG']['action_succeed'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 删除一个角色
        /*------------------------------------------------------ */
        elseif ($act == 'remove') {
            $check_auth = check_authz_json('admin_drop');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_GET['id']);

            $remove_num = AdminUser::where('role_id', $id)->count();

            if ($remove_num > 0) {
                return make_json_error($GLOBALS['_LANG']['remove_cannot_user']);
            }

            $role = Role::where('role_id', $id)->first();

            /* 记录管理员操作 */
            admin_log($role->user_name, 'remove', 'role');

            $role->delete();
            $url = 'role.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));

            return dsc_header("Location: $url\n");
        }
    }
}
