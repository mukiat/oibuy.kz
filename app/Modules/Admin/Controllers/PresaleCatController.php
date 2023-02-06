<?php

namespace App\Modules\Admin\Controllers;

use App\Models\PresaleActivity;
use App\Models\PresaleCat;
use App\Repositories\Common\BaseRepository;
use App\Services\PresaleCat\PresaleCatManageService;

/**
 * 商品分类管理程序
 */
class PresaleCatController extends InitController
{
    protected $presaleCatManageService;

    public function __construct(
        PresaleCatManageService $presaleCatManageService
    )
    {
        $this->presaleCatManageService = $presaleCatManageService;
    }

    public function index()
    {
        /* act操作项的初始化 */
        if (empty($_REQUEST['act'])) {
            $_REQUEST['act'] = 'list';
        } else {
            $_REQUEST['act'] = trim($_REQUEST['act']);
        }

        /*------------------------------------------------------ */
        //-- 商品分类列表
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'list') {
            $parent_id = !isset($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']);

            //返回上一页 start
            if (isset($_REQUEST['back_level']) && $_REQUEST['back_level'] > 0) {
                $level = $_REQUEST['back_level'] - 1;

                $parent_id = PresaleCat::where('cat_id', $parent_id)->value('parent_id');
                $parent_id = $parent_id ? $parent_id : 0;
            } else {
                $level = isset($_REQUEST['level']) ? $_REQUEST['level'] + 1 : 0;
            }
            //返回上一页 end

            $this->smarty->assign('level', $level);
            $this->smarty->assign('parent_id', $parent_id);

            $cat_list = $this->presaleCatManageService->getCatLevel($parent_id, $level);

            $adminru = get_admin_ru_id();
            $this->smarty->assign('ru_id', $adminru['ru_id']);

            if ($adminru['ru_id'] == 0) {
                $this->smarty->assign('action_link', ['href' => 'presale_cat.php?act=add', 'text' => $GLOBALS['_LANG']['add_presale_cat']]);
            }

            /* 模板赋值 */
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['presale_cat']);
            $this->smarty->assign('full_page', 1);

            $this->smarty->assign('cat_info', $cat_list);

            $cat_level = [$GLOBALS['_LANG']['num_1'], $GLOBALS['_LANG']['num_2'], $GLOBALS['_LANG']['num_3'], $GLOBALS['_LANG']['num_4'], $GLOBALS['_LANG']['num_5'], $GLOBALS['_LANG']['num_6'], $GLOBALS['_LANG']['num_7'], $GLOBALS['_LANG']['num_8'], $GLOBALS['_LANG']['num_9'], $GLOBALS['_LANG']['num_10']];
            $this->smarty->assign('cat_level', $cat_level[$level]);

            /* 列表页面 */
            return $this->smarty->display('presale_cat_list.dwt');
        }

        elseif ($_REQUEST['act'] == 'query') {
            $parent_id = !isset($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']);

            //返回上一页 start
            if (isset($_REQUEST['back_level']) && $_REQUEST['back_level'] > 0) {
                $level = $_REQUEST['back_level'] - 1;

                $parent_id = PresaleCat::query()->where('cat_id', $parent_id)->value('parent_id');
                $parent_id = $parent_id ? $parent_id : 0;
            } else {
                $level = isset($_REQUEST['level']) ? $_REQUEST['level'] + 1 : 0;
            }

            $this->smarty->assign('level', $level);
            $this->smarty->assign('parent_id', $parent_id);
            $cat_list = $this->presaleCatManageService->getCatLevel($parent_id, $level);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['presale_cat']);
            $this->smarty->assign('cat_info', $cat_list);

            $cat_level = [$GLOBALS['_LANG']['num_1'], $GLOBALS['_LANG']['num_2'], $GLOBALS['_LANG']['num_3'], $GLOBALS['_LANG']['num_4'], $GLOBALS['_LANG']['num_5'], $GLOBALS['_LANG']['num_6'], $GLOBALS['_LANG']['num_7'], $GLOBALS['_LANG']['num_8'], $GLOBALS['_LANG']['num_9'], $GLOBALS['_LANG']['num_10']];
            $this->smarty->assign('cat_level', $cat_level[$level]);

            return make_json_result($this->smarty->fetch('presale_cat_list.dwt'));
        }

        /*------------------------------------------------------ */
        //-- 添加商品分类
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'add') {
            /* 权限检查 */
            admin_priv('cat_manage');

            /* 模板赋值 */
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['add_presale_cat']);
            $this->smarty->assign('action_link', ['href' => 'presale_cat.php?act=list', 'text' => $GLOBALS['_LANG']['presale_cat_list']]);

            $parent_id = empty($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']);
            if (!empty($parent_id)) {
                set_default_filter(0, $parent_id, 0, 0, 'presale_cat'); //设置默认筛选
                $this->smarty->assign('parent_category', get_every_category($parent_id, 'presale_cat')); //上级分类
                $this->smarty->assign('parent_id', $parent_id); //上级分类
            } else {
                set_default_filter(0, 0, 0, 0, 'presale_cat'); //设置默认筛选
            }

            $this->smarty->assign('form_act', 'insert');
            $this->smarty->assign('cat_info', ['is_show' => 1, 'parent_id' => $parent_id]);

            $adminru = get_admin_ru_id();
            $this->smarty->assign('ru_id', $adminru['ru_id']);

            /* 显示页面 */
            return $this->smarty->display('presale_cat_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 商品分类添加时的处理
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'insert') {
            /* 权限检查 */
            admin_priv('cat_manage');

            /* 初始化变量 */
            $cat['parent_id'] = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
            $cat['sort_order'] = !empty($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
            $cat['cat_name'] = !empty($_POST['cat_name']) ? trim($_POST['cat_name']) : '';

            if ($this->presaleCatManageService->cnameExists($cat['cat_name'], $cat['parent_id'])) {
                /* 同级别下不能有重复的分类名称 */
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                return sys_msg($GLOBALS['_LANG']['catname_exist'], 0, $link);
            }

            $cat_id = PresaleCat::insertGetId($cat);

            /* 入库的操作 */
            if ($cat_id > 0) {
                $list = [
                    'presale_cat_releate',
                    'presale_cat_option_static'
                ];
                BaseRepository::getCacheForgetlist($list);

                admin_log($_POST['cat_name'], 'add', 'presale_cat');   // 记录管理员操作
                clear_cache_files();    // 清除缓存

                /*添加链接*/
                $link[0]['text'] = $GLOBALS['_LANG']['continue_add'];
                $link[0]['href'] = 'presale_cat.php?act=add';

                $link[1]['text'] = $GLOBALS['_LANG']['back_list'];
                $link[1]['href'] = 'presale_cat.php?act=list';

                return sys_msg($GLOBALS['_LANG']['catadd_succed'], 0, $link);
            }
        }

        /*------------------------------------------------------ */
        //-- 编辑商品分类信息
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'edit') {
            admin_priv('cat_manage');   // 权限检查

            $cat_id = intval($_REQUEST['cat_id']);

            $cat_info = PresaleCat::catInfo($cat_id);
            $cat_info = BaseRepository::getToArrayFirst($cat_info);

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['category_edit']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['presale_cat_list'], 'href' => 'presale_cat.php?act=list']);

            $this->smarty->assign('cat_id', $cat_id);

            $adminru = get_admin_ru_id();
            $this->smarty->assign('ru_id', $adminru['ru_id']);

            $this->smarty->assign('cat_info', $cat_info);
            $this->smarty->assign('form_act', 'update');

            //获取下拉列表 by wu start
            $this->smarty->assign('parent_id', $cat_info['parent_id']); //上级分类
            $this->smarty->assign('parent_category', get_every_category($cat_info['parent_id'], 'presale_cat')); //上级分类导航
            set_default_filter(0, $cat_info['parent_id'], 0, 0, 'presale_cat'); //设置默认筛选
            //获取下拉列表 by wu end

            /* 显示页面 */
            return $this->smarty->display('presale_cat_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 编辑商品分类信息
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'update') {
            /* 权限检查 */
            admin_priv('cat_manage');

            /* 初始化变量 */
            $cat_id = !empty($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;
            $old_cat_name = $_POST['old_cat_name'];
            $cat['parent_id'] = isset($_POST['parent_id']) ? trim($_POST['parent_id']) : 0;
            $cat['sort_order'] = !empty($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
            $cat['cat_name'] = !empty($_POST['cat_name']) ? trim($_POST['cat_name']) : '';

            /* 判断分类名是否重复 */

            if ($cat['cat_name'] != $old_cat_name) {
                if ($this->presaleCatManageService->presaleCatExists($cat['cat_name'], $cat['parent_id'], $cat_id)) {
                    $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                    return sys_msg($GLOBALS['_LANG']['catname_exist'], 0, $link);
                }
            }

            PresaleCat::where('cat_id', $cat_id)->update($cat);

            // 清除缓存
            $list = [
                'presale_cat_releate',
                'presale_cat_option_static'
            ];
            BaseRepository::getCacheForgetlist($list);

            admin_log($cat['cat_name'], 'edit', 'presale_cat'); // 记录管理员操作

            /* 提示信息 */
            $link[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'presale_cat.php?act=list'];
            return sys_msg($GLOBALS['_LANG']['catedit_succed'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 编辑排序序号
        /*------------------------------------------------------ */

        if ($_REQUEST['act'] == 'edit_sort_order') {
            $check_auth = check_authz_json('cat_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_POST['id']);
            $val = intval($_POST['val']);

            if ($this->presaleCatManageService->catUpdate($id, ['sort_order' => $val]) >= 0) {
                clear_cache_files(); // 清除缓存
                return make_json_result($val);
            } else {
                return make_json_error($this->db->error());
            }
        }

        /*------------------------------------------------------ */
        //-- 删除商品分类
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'remove') {
            $check_auth = check_authz_json('cat_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            /* 初始化分类ID并取得分类名称 */
            $cat_id = intval($_GET['id']);

            $cat_name = PresaleCat::where('cat_id', $cat_id)->value('cat_name');
            $cat_name = $cat_name ? $cat_name : '';

            /* 当前分类下是否有子分类 */
            $cat_count = PresaleCat::where('parent_id', $cat_id)->count();

            /* 当前分类下是否存在商品 */
            $goods_count = PresaleActivity::where('cat_id', $cat_id)->count();

            /* 如果不存在下级子分类和商品，则删除之 */
            if ($cat_count == 0 && $goods_count == 0) {
                /* 删除分类 */

                PresaleCat::where('cat_id', $cat_id)->delete();

                $list = [
                    'presale_cat_releate',
                    'presale_cat_option_static'
                ];
                BaseRepository::getCacheForgetlist($list);

                admin_log($cat_name, 'remove', 'presale_cat');
            } else {
                return make_json_error($cat_name . ' ' . $GLOBALS['_LANG']['cat_isleaf']);
            }

            $url = 'presale_cat.php?act=query';

            return dsc_header("Location: $url\n");
        }
    }

    /*------------------------------------------------------ */
    //-- PRIVATE FUNCTIONS
    /*------------------------------------------------------ */
}
