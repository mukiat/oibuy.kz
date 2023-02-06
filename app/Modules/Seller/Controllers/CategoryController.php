<?php

namespace App\Modules\Seller\Controllers;

use App\Libraries\Pinyin;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\CatRecommend;
use App\Models\Goods;
use App\Models\MerchantsCategory;
use App\Models\MerchantsDocumenttitle;
use App\Models\Nav;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Category\CategoryService;

/**
 * 商品分类管理程序
 */
class CategoryController extends InitController
{
    protected $categoryService;
    protected $dscRepository;

    public function __construct(
        CategoryService $categoryService,
        DscRepository $dscRepository
    ) {
        $this->categoryService = $categoryService;
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {
        $menus = session('menus', '');
        $this->smarty->assign('menus', $menus);
        $this->smarty->assign('action_type', "goods");
        /* act操作项的初始化 */

        $act = addslashes(trim(request()->input('act', 'list')));
        $act = $act ? $act : 'list';

        $adminru = get_admin_ru_id();

        $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => '03_store_category_list']);
        /*------------------------------------------------------ */
        //-- 商品分类列表
        /*------------------------------------------------------ */
        if ($act == 'list') {
            $this->smarty->assign('current', 'category_list');
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_cat_and_goods']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['03_category_list']);

            $parent_id = (int)request()->input('parent_id', 0);
            $back_level = (int)request()->input('back_level', 0);

            //页面分菜单 by wu start
            $tab_menu = [];
            $tab_menu[] = ['curr' => 0, 'text' => $GLOBALS['_LANG']['03_store_category_list'], 'href' => 'category_store.php?act=list'];
            $tab_menu[] = ['curr' => 1, 'text' => $GLOBALS['_LANG']['03_category_list'], 'href' => 'category.php?act=list'];
            $this->smarty->assign('tab_menu', $tab_menu);
            //页面分菜单 by wu end

            //返回上一页 start
            if ($back_level > 0) {
                $level = $back_level - 1;
                $parent_id = Category::where('cat_id', $parent_id)->value('parent_id');
                $parent_id = $parent_id ?? 0;

            } else {
                $level = request()->exists('level') ? (int)request()->input('level', 0) + 1 : 0;
            }
            //返回上一页 end

            $this->smarty->assign('level', $level);
            $this->smarty->assign('parent_id', $parent_id);

            /* 获取分类列表 */
            $cat_list = $this->get_cat_level($parent_id, $level, $adminru['ru_id']);
            $this->smarty->assign('cat_info', $cat_list);
            $this->smarty->assign('ru_id', $adminru['ru_id']);

            if ($adminru['ru_id'] == 0) {
                $this->smarty->assign('action_link', ['href' => 'category.php?act=add', 'text' => $GLOBALS['_LANG']['04_category_add']]);
            }

            /* 模板赋值 */
            $this->smarty->assign('full_page', 1);

            /* 列表页面 */

            return $this->smarty->display('category_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 添加商品分类
        /*------------------------------------------------------ */
        if ($act == 'add') {
            /* 权限检查 */
            admin_priv('cat_manage');

            //获取下拉列表 by wu start
            $select_category_html = '';
            $select_category_html .= insert_select_category();
            $this->smarty->assign('select_category_html', $select_category_html);
            //获取下拉列表 by wu end

            /* 模板赋值 */
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_cat_and_goods']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['04_category_add']);
            $this->smarty->assign('action_link', ['href' => 'category.php?act=list', 'text' => $GLOBALS['_LANG']['03_category_list']]);

            $this->smarty->assign('goods_type_list', goods_type_list(0)); // 取得商品类型
            $this->smarty->assign('attr_list', $this->get_attr_list()); // 取得商品属性

            $this->smarty->assign('form_act', 'insert');
            $this->smarty->assign('cat_info', ['is_show' => 1]);

            $this->smarty->assign('ru_id', $adminru['ru_id']);

            /* 显示页面 */

            return $this->smarty->display('category_info.htm');
        }

        /*------------------------------------------------------ */
        //-- 删除分类菜单图片 by wu
        /*------------------------------------------------------ */
        if ($act == 'delete_icon') {
            /* 权限检查 */
            admin_priv('cat_manage');

            $result = ['error' => 0, 'msg' => ''];

            $cat_id = (int)request()->input('cat_id', 0);

            $cat_info = Category::catInfo($cat_id)->first();
            $cat_info = $cat_info ? $cat_info->toArray() : [];

            if (!empty($cat_info)) {
                $update = Category::where('cat_id', $cat_id)->update(['cat_icon' => '']);
                if ($update > 0) {
                    @unlink(storage_public($cat_info['cat_icon']));
                    $result = ['error' => 1, 'msg' => $GLOBALS['_LANG']['delete_success']];
                }
            }
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 商品分类添加时的处理
        /*------------------------------------------------------ */
        if ($act == 'insert') {
            /* 权限检查 */
            admin_priv('cat_manage');

            /* 初始化变量 */
            $cat['cat_id'] = (int)request()->input('cat_id', 0);
            $cat['parent_id'] = addslashes(trim(request()->input('parent_id', '0_-1')));

            //ecmoban模板堂 --zhuo start
            $parent_id = explode('_', $cat['parent_id']);
            $cat['parent_id'] = intval($parent_id[0]);
            $cat['level'] = intval($parent_id[1]);

            if ($cat['level'] < 2 && $adminru['ru_id'] > 0) {
                $link[0]['text'] = $GLOBALS['_LANG']['go_back'];

                if ($cat['cat_id'] > 0) {
                    $link[0]['href'] = 'category.php?act=edit&cat_id=' . $cat['cat_id'];
                } else {
                    $link[0]['href'] = 'category.php?act=add';
                }

                return sys_msg($GLOBALS['_LANG']['you_power_add_level4_cate'], 0, $link);
            }
            //ecmoban模板堂 --zhuo end
            //上传分类菜单图标 by wu start
            if (!empty($_FILES['cat_icon']['name'])) {
                $link[0]['text'] = $GLOBALS['_LANG']['continue_add'];
                $link[0]['href'] = 'category.php?act=add';

                if ($_FILES["cat_icon"]["size"] > 200000) {
                    return sys_msg($GLOBALS['_LANG']['upload_img_max_200kb'], 0, $link);
                }

                $type = explode('.', $_FILES['cat_icon']['name']);
                $type = end($type);
                if ($type != 'jpg' && $type != 'png' && $type != 'gif') {
                    return sys_msg($GLOBALS['_LANG']['please_upload_jpg_gif_png'], 0, $link);
                }
                $imgNamePrefix = time() . mt_rand(1001, 9999);
                //文件目录
                $imgDir = storage_public(DATA_DIR . "/cat_icon");
                if (!file_exists($imgDir)) {
                    mkdir($imgDir);
                }
                //保存文件
                $imgName = $imgDir . "/" . $imgNamePrefix . '.' . $type;
                $saveDir = "images/cat_icon" . "/" . $imgNamePrefix . '.' . $type;
                move_uploaded_file($_FILES["cat_icon"]["tmp_name"], $imgName);
                $cat['cat_icon'] = $saveDir;
            }
            //上传分类菜单图标 by wu end

            $cat['sort_order'] = (int)request()->input('sort_order', 0);
            $cat['keywords'] = addslashes(trim(request()->input('keywords', '')));
            $cat['cat_desc'] = addslashes(trim(request()->input('cat_desc', '')));
            $cat['measure_unit'] = addslashes(trim(request()->input('measure_unit', '')));
            $cat['cat_name'] = addslashes(trim(request()->input('cat_name', '')));
            $cat['cat_alias_name'] = addslashes(trim(request()->input('cat_alias_name', '')));

            //by guan start
            $pin = new Pinyin();
            $pinyin = $pin->Pinyin($cat['cat_name'], 'UTF8');
            $cat['pinyin_keyword'] = $pinyin;
            //by guan end

            $cat['show_in_nav'] = (int)request()->input('show_in_nav', 0);
            $cat['style'] = addslashes(trim(request()->input('style', '')));
            $cat['is_show'] = (int)request()->input('is_show', 0);

            /* by zhou */
            $cat['is_top_show'] = (int)request()->input('is_top_show', 0);
            $cat['is_top_style'] = (int)request()->input('is_top_style', 0);
            //顶级分类页模板 by wu
            $cat['top_style_tpl'] = request()->input('top_style_tpl', 0);

            /* by zhou */
            $cat['grade'] = (int)request()->input('grade', 0);
            $cat['filter_attr'] = request()->input('filter_attr', 0);
            $cat['filter_attr'] = $cat['filter_attr'] ? implode(',', array_unique(array_diff($cat['filter_attr'], [0]))) : 0;
            $cat['cat_recommend'] = request()->input('cat_recommend', []);

            if (cat_exists($cat['cat_name'], $cat['parent_id'])) {
                /* 同级别下不能有重复的分类名称 */
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                return sys_msg($GLOBALS['_LANG']['catname_exist'], 0, $link);
            }

            if ($cat['grade'] > 10 || $cat['grade'] < 0) {
                /* 价格区间数超过范围 */
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                return sys_msg($GLOBALS['_LANG']['grade_error'], 0, $link);
            }

            /* 入库的操作 */
            $cat_name = explode(',', $cat['cat_name']);

            if (count($cat_name) > 1) {
                $cat['is_show_merchants'] = (int)request()->input('is_show_merchants', 0);

                get_bacth_category($cat_name, $cat, $adminru['ru_id']);

                clear_cache_files();    // 清除缓存

                /* 添加链接 */
                $link[0]['text'] = $GLOBALS['_LANG']['continue_add'];
                $link[0]['href'] = 'category.php?act=add';

                $link[1]['text'] = $GLOBALS['_LANG']['back_list'];
                $link[1]['href'] = 'category.php?act=list';

                return sys_msg($GLOBALS['_LANG']['catadd_succed'], 0, $link);
            } else {
                $cat_id = Category::insertGetId($cat);
                if ($cat_id) {
                    if ($cat['show_in_nav'] == 1) {
                        $vieworder = Nav::where('type', 'middle')->max('vieworder');
                        $vieworder += 2;
                        //显示在自定义导航栏中
                        $data = [
                            'name' => $cat['cat_name'],
                            'ctype' => 'c',
                            'cid' => $cat_id,
                            'ifshow' => 1,
                            'vieworder' => $vieworder,
                            'opennew' => 0,
                            'url' => $this->dscRepository->buildUri('category', ['cid' => $cat_id], $cat['cat_name']),
                            'type' => 'middle',
                        ];
                        Nav::insert($data);
                    }

                    admin_log($cat['cat_name'], 'add', 'category');   // 记录管理员操作
                    //ecmoban模板堂 --zhuo start
                    $dt_list = request()->input('document_title', []);
                    $dt_id = request()->input('dt_id', []);
                    get_documentTitle_insert_update($dt_list, $cat_id, $dt_id);

                    if ($adminru['ru_id'] > 0) {
                        $parent = [
                            'cat_id' => $cat_id,
                            'user_id' => $adminru['ru_id'],
                            'is_show' => (int)request()->input('is_show_merchants', 0),
                            'add_titme' => gmtime()
                        ];
                        MerchantsCategory::insert($parent);
                    }
                    //ecmoban模板堂 --zhuo end

                    clear_cache_files();    // 清除缓存

                    /* 添加链接 */
                    $link[0]['text'] = $GLOBALS['_LANG']['continue_add'];
                    $link[0]['href'] = 'category.php?act=add';

                    $link[1]['text'] = $GLOBALS['_LANG']['back_list'];
                    $link[1]['href'] = 'category.php?act=list';

                    return sys_msg($GLOBALS['_LANG']['catadd_succed'], 0, $link);
                }
            }
        }

        /*------------------------------------------------------ */
        //-- 编辑商品分类信息
        /*------------------------------------------------------ */
        if ($act == 'edit') {
            admin_priv('cat_manage');   // 权限检查
            $cat_id = (int)request()->input('cat_id', 0);
            $cat_info = Category::catInfo($cat_id)->first();
            $cat_info = $cat_info ? $cat_info->toArray() : [];

            $attr_list = $this->get_attr_list();
            $filter_attr_list = [];

            //获取下拉列表 by wu start
            $select_category_html = '';
            $parent_cat_list = get_select_category($cat_id, 1, false);
            for ($i = 0; $i < count($parent_cat_list); $i++) {
                $select_category_html .= insert_select_category(pos($parent_cat_list), next($parent_cat_list), $i);
            }
            $this->smarty->assign('select_category_html', $select_category_html);
            $parent_and_rank = empty($cat_info['parent_id']) ? '0_0' : $cat_info['parent_id'] . '_' . (count($parent_cat_list) - 2);
            $this->smarty->assign('parent_and_rank', $parent_and_rank);
            //获取下拉列表 by wu end

            if ($cat_info['filter_attr']) {
                $filter_attr = explode(",", $cat_info['filter_attr']);  //把多个筛选属性放到数组中

                foreach ($filter_attr as $k => $v) {
                    $attr_cat_id = Attribute::where('attr_id', intval($v))->value('cat_id');
                    $attr_cat_id = $attr_cat_id ?? 0;
                    $filter_attr_list[$k]['goods_type_list'] = goods_type_list($attr_cat_id);  //取得每个属性的商品类型
                    $filter_attr_list[$k]['filter_attr'] = $v;
                    $attr_option = [];

                    if (isset($attr_list[$attr_cat_id]) && $attr_list[$attr_cat_id]) {
                        foreach ($attr_list[$attr_cat_id] as $val) {
                            $attr_option[key($val)] = current($val);
                        }
                    }

                    $filter_attr_list[$k]['option'] = $attr_option;
                }

                $this->smarty->assign('filter_attr_list', $filter_attr_list);
            } else {
                $attr_cat_id = 0;
            }

            /* 模板赋值 */

            //by guan start
            if ($cat_info['parent_id'] == 0) {
                $cat_name_arr = explode('、', $cat_info['cat_name']);
                $this->smarty->assign('cat_name_arr', $cat_name_arr); // 取得商品属性
            }
            //by guan end

            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_cat_and_goods']);
            $this->smarty->assign('attr_list', $attr_list); // 取得商品属性
            $this->smarty->assign('attr_cat_id', $attr_cat_id);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['category_edit']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['03_category_list'], 'href' => 'category.php?act=list']);

            //分类是否存在首页推荐
            $res = CatRecommend::select('recommend_type')->where('cat_id', $cat_id);
            $res = BaseRepository::getToArrayGet($res);

            if (!empty($res)) {
                $cat_recommend = [];
                foreach ($res as $data) {
                    $cat_recommend[$data['recommend_type']] = 1;
                }
                $this->smarty->assign('cat_recommend', $cat_recommend);
            }

            //ecmoban模板堂 --zhuo start
            $title_list = MerchantsDocumenttitle::select('dt_id', 'dt_title')->where('cat_id', $cat_id);
            $title_list = BaseRepository::getToArrayGet($title_list);

            $this->smarty->assign('title_list', $title_list);
            $this->smarty->assign('cat_id', $cat_id);

            $this->smarty->assign('ru_id', $adminru['ru_id']);

            $cat_info['cat_icon'] = $this->dscRepository->getImagePath($cat_info['cat_icon']);
            $cat_info['touch_icon'] = $this->dscRepository->getImagePath($cat_info['touch_icon']);

            //ecmoban模板堂 --zhuo end
            $this->smarty->assign('cat_info', $cat_info);
            $this->smarty->assign('form_act', 'update');
            $this->smarty->assign('goods_type_list', goods_type_list(0)); // 取得商品类型

            /* 显示页面 */

            return $this->smarty->display('category_info.htm');
        } //ecmoban模板堂 --zhuo start
        elseif ($act == 'titleFileView') {
            $cat_id = (int)request()->input('cat_id', 0);
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_cat_and_goods']);

            $title_list = MerchantsDocumenttitle::select('dt_id', 'dt_title')->where('cat_id', $cat_id);
            $title_list = BaseRepository::getToArrayGet($title_list);

            $this->smarty->assign('title_list', $title_list);
            $this->smarty->assign('cat_id', $cat_id);
            $this->smarty->assign('form_act', 'title_update');

            $cat_name = Category::where('cat_id', $cat_id)->value('cat_name');
            $cat_name = $cat_name ?? '';
            $this->smarty->assign('cat_name', $cat_name);

            $this->smarty->assign('action_link', ['href' => 'category.php?act=edit&cat_id=' . $cat_id, 'text' => $GLOBALS['_LANG']['go_back']]);

            /* 显示页面 */

            return $this->smarty->display('category_titleFileView.htm');
        } elseif ($act == 'title_update') {
            $cat_id = (int)request()->input('cat_id', 0);
            $dt_list = request()->input('document_title', []);
            $dt_id = request()->input('dt_id', []);

            get_documentTitle_insert_update($dt_list, $cat_id, $dt_id);

            clear_cache_files(); // 清除缓存

            /* 提示信息 */
            $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'category.php?act=titleFileView&cat_id=' . $cat_id];
            return sys_msg($GLOBALS['_LANG']['title_catedit_succed'], 0, $link);
        } //ecmoban模板堂 --zhuo end
        elseif ($act == 'add_category') {
            $parent_id = (int)request()->input('parent_id', 0);
            $category = json_str_iconv(trim(request()->input('cat', '')));

            if (cat_exists($category, $parent_id)) {
                return make_json_error($GLOBALS['_LANG']['catname_exist']);
            } else {
                $data = [
                    'cat_name' => $category,
                    'parent_id' => $parent_id,
                    'is_show' => 1,
                ];
                $category_id = Category::insertGetId($data);

                $arr = ["parent_id" => $parent_id, "id" => $category_id, "cat" => $category];

                clear_cache_files();    // 清除缓存

                //by wu
                $select_category_html = '';
                $parent_cat_list = get_select_category($parent_id, 1, true);
                for ($i = 0; $i < count($parent_cat_list); $i++) {
                    $select_category_html .= insert_select_category(pos($parent_cat_list), next($parent_cat_list), $i, 'cat_id');
                }
                $this->smarty->assign('select_category_html', $select_category_html);
                $parent_and_rank = empty($parent_id) ? '0_0' : $parent_id . '_' . (count($parent_cat_list) - 2);
                $this->smarty->assign('parent_and_rank', $parent_and_rank);
                $arr['detail'] = $select_category_html;
                return response()->json($arr);
            }
        }

        /*------------------------------------------------------ */
        //-- 编辑商品分类信息
        /*------------------------------------------------------ */
        if ($act == 'update') {
            /* 权限检查 */
            admin_priv('cat_manage');

            /* 初始化变量 */
            $cat_id = (int)request()->input('cat_id', 0);
            $old_cat_name = request()->input('old_cat_name', '');
            $cat['parent_id'] = request()->input('parent_id', '0_-1');

            //ecmoban模板堂 --zhuo start
            $parent_id = explode('_', $cat['parent_id']);
            $cat['parent_id'] = intval($parent_id[0]);
            $cat['level'] = intval($parent_id[1]);

            $link[0]['text'] = $GLOBALS['_LANG']['go_back'];

            if ($cat['cat_id'] > 0) {
                $link[0]['href'] = 'category.php?act=edit&cat_id=' . $cat['cat_id'];
            } else {
                $link[0]['href'] = 'category.php?act=add';
            }

            $reject_cat = $this->categoryService->catList($cat_id, 1, 1);
            $reject_cat = arr_foreach($reject_cat);//获取当前分类相关分类数组

            if ($cat['parent_id'] == $cat_id || in_array($cat['parent_id'], $reject_cat)) {
                return sys_msg($GLOBALS['_LANG']['cate_cant_parent'], 0, $link);
            }

            if ($cat['level'] < 2 && $adminru['ru_id'] > 0) {
                return sys_msg($GLOBALS['_LANG']['you_power_add_level4_cate'], 0, $link);
            }
            //ecmoban模板堂 --zhuo end

            //上传分类菜单图标 by wu start
            if (!empty($_FILES['cat_icon']['name'])) {
                if ($_FILES["cat_icon"]["size"] > 200000) {
                    return sys_msg($GLOBALS['_LANG']['upload_img_max_200kb'], 0, $link);
                }

                $type = explode('.', $_FILES['cat_icon']['name']);
                $type = end($type);
                if ($type != 'jpg' && $type != 'png' && $type != 'gif') {
                    return sys_msg($GLOBALS['_LANG']['please_upload_jpg_gif_png'], 0, $link);
                }
                $imgNamePrefix = time() . mt_rand(1001, 9999);
                //文件目录
                $imgDir = storage_public(DATA_DIR . '/cat_icon');
                if (!file_exists($imgDir)) {
                    mkdir($imgDir);
                }
                //保存文件
                $imgName = $imgDir . "/" . $imgNamePrefix . '.' . $type;
                $saveDir = "images/cat_icon" . "/" . $imgNamePrefix . '.' . $type;
                move_uploaded_file($_FILES["cat_icon"]["tmp_name"], $imgName);
                $cat['cat_icon'] = $saveDir;
                //删除文件
                if (!empty($cat_id)) {
                    $cat_info = Category::catInfo($cat_id)->first();
                    $cat_info = $cat_info ? $cat_info->toArray() : [];

                    @unlink(storage_public($cat_info['cat_icon']));
                }
            }
            //上传分类菜单图标 by wu end


            $cat['sort_order'] = (int)request()->input('sort_order', 0);
            $cat['keywords'] = addslashes(trim(request()->input('keywords', '')));
            $cat['cat_desc'] = addslashes(trim(request()->input('cat_desc', '')));
            $cat['measure_unit'] = addslashes(trim(request()->input('measure_unit', '')));
            $cat['cat_name'] = addslashes(trim(request()->input('cat_name', '')));
            $cat['cat_alias_name'] = addslashes(trim(request()->input('cat_alias_name', '')));
            $cat['category_links'] = addslashes(trim(request()->input('category_links', '')));
            $cat['category_topic'] = addslashes(trim(request()->input('category_topic', '')));

            //by guan start
            $pin = new Pinyin();
            $pinyin = $pin->Pinyin($cat['cat_name'], 'UTF8');

            $cat['pinyin_keyword'] = $pinyin;
            //by guan end

            $cat['show_in_nav'] = (int)request()->input('show_in_nav', 0);
            $cat['style'] = addslashes(trim(request()->input('style', '')));
            $cat['is_show'] = (int)request()->input('is_show', 0);

            /* by zhou */
            $cat['is_top_show'] = (int)request()->input('is_top_show', 0);
            $cat['is_top_style'] = (int)request()->input('is_top_style', 0);
            //顶级分类页模板 by wu
            $cat['top_style_tpl'] = request()->input('top_style_tpl', 0);

            /* by zhou */
            $cat['grade'] = (int)request()->input('grade', 0);
            $cat['filter_attr'] = request()->input('filter_attr', 0);
            $cat['filter_attr'] = $cat['filter_attr'] ? implode(',', array_unique(array_diff($cat['filter_attr'], [0]))) : 0;
            $cat['cat_recommend'] = request()->input('cat_recommend', []);
            /*by zhou*/

            /* 判断分类名是否重复 */
            if ($cat['cat_name'] != $old_cat_name) {
                if (cat_exists($cat['cat_name'], $cat['parent_id'], $cat_id)) {
                    $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                    return sys_msg($GLOBALS['_LANG']['catname_exist'], 0, $link);
                }
            }

            /* 判断上级目录是否合法 */
            $children = $this->categoryService->getArrayKeysCat($cat_id);     // 获得当前分类的所有下级分类
            if (in_array($cat['parent_id'], $children)) {
                /* 选定的父类是当前分类或当前分类的下级分类 */
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                return sys_msg($GLOBALS['_LANG']["is_leaf_error"], 0, $link);
            }

            if ($cat['grade'] > 10 || $cat['grade'] < 0) {
                /* 价格区间数超过范围 */
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                return sys_msg($GLOBALS['_LANG']['grade_error'], 0, $link);
            }

            $dat = Category::select('cat_name', 'show_in_nav')->where('cat_id', $cat_id);
            $dat = BaseRepository::getToArrayFirst($dat);

            $update = Category::where('cat_id', $cat_id)->update($cat);
            if ($update > 0) {
                if ($cat['cat_name'] != $dat['cat_name']) {
                    //如果分类名称发生了改变
                    Nav::where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->update(['name' => $cat['cat_name']]);
                }
                if ($cat['show_in_nav'] != $dat['show_in_nav']) {
                    //是否显示于导航栏发生了变化
                    if ($cat['show_in_nav'] == 1) {
                        //显示
                        $nid = Nav::where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->value('id');
                        $nid = $nid ?? 0;

                        if (empty($nid)) {
                            //不存在
                            $vieworder = Nav::where('type', 'middle')->max('vieworder');
                            $vieworder += 2;
                            $uri = $this->dscRepository->buildUri('category', ['cid' => $cat_id], $cat['cat_name']);

                            $data = [
                                'name' => $cat['cat_name'],
                                'ctype' => 'c',
                                'cid' => $cat_id,
                                'ifshow' => 1,
                                'vieworder' => $vieworder,
                                'opennew' => 0,
                                'url' => $uri,
                                'type' => 'middle',
                            ];

                            Nav::insert($data);
                        } else {
                            Nav::where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->update(['ifshow' => 1]);
                        }
                    } else {
                        //去除
                        Nav::where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->update(['ifshow' => 0]);
                    }
                }

                /* 更新分类信息成功 */

                //ecmoban模板堂 --zhuo start
                $dt_list = request()->input('document_title', []);
                $dt_id = request()->input('dt_id', []);
                get_documentTitle_insert_update($dt_list, $cat_id, $dt_id);
                $is_show_merchants = (int)request()->input('is_show_merchants', 0);

                MerchantsCategory::where('cat_id', $cat_id)->update(['is_show' => $is_show_merchants]);
                //ecmoban模板堂 --zhuo end

                clear_cache_files(); // 清除缓存
                admin_log($cat['cat_name'], 'edit', 'category'); // 记录管理员操作

                /* 提示信息 */
                $link[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'category.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['catedit_succed'], 0, $link);
            }
        }

        /*------------------------------------------------------ */
        //-- 批量转移商品分类页面
        /*------------------------------------------------------ */
        if ($act == 'move') {
            $check_auth = check_authz_json('cat_drop');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $cat_id = (int)request()->input('cat_id', 0);

            //获取下拉列表 by wu start
            $this->smarty->assign('parent_id', $cat_id); //上级分类
            $this->smarty->assign('parent_category', get_every_category($cat_id)); //上级分类导航
            set_default_filter(0, $cat_id, $adminru['ru_id']); //设置默认筛选
            //获取下拉列表 by wu end

            /* 模板赋值 */
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['move_goods']);
            $this->smarty->assign('action_link', ['href' => 'category.php?act=list', 'text' => $GLOBALS['_LANG']['03_category_list']]);
            $this->smarty->assign('file_name', 'category');
            $this->smarty->assign('form_act', 'move_cat');
            $this->smarty->assign('is_platform', '1');

            $html = $this->smarty->fetch("category_move.dwt");

            clear_cache_files();
            return make_json_result($html);
        }

        /*------------------------------------------------------ */
        //-- 处理批量转移商品分类的处理程序
        /*------------------------------------------------------ */
        if ($act == 'move_cat') {
            /* 权限检查 */
            admin_priv('cat_drop');

            $cat_id = (int)request()->input('cat_id', 0);
            $target_cat_id = (int)request()->input('target_cat_id', 0);

            /* 商品分类不允许为空 */
            if ($cat_id == 0 || $target_cat_id == 0) {
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'category.php?act=move'];
                return sys_msg($GLOBALS['_LANG']['cat_move_empty'], 0, $link);
            }
            /* 更新商品分类 */
            $children = get_children($cat_id, 0, 0, 'category', 2);
            $children = BaseRepository::getExplode($children);
            $update = Goods::where('user_id', $adminru['ru_id'])->whereIn('cat_id', $children)->update(['cat_id' => $target_cat_id]);
            if ($update > 0) {
                /* 清除缓存 */
                clear_cache_files();

                /* 提示信息 */
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'category.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['move_cat_success'], 0, $link);
            }
        }

        /*------------------------------------------------------ */
        //-- 编辑排序序号
        /*------------------------------------------------------ */

        if ($act == 'edit_sort_order') {
            $check_auth = check_authz_json('cat_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = (int)request()->input('id', 0);
            $val = (int)request()->input('val', 0);

            if ($this->cat_update($id, ['sort_order' => $val])) {
                clear_cache_files(); // 清除缓存
                return make_json_result($val);
            } else {
                return make_json_error($this->db->error());
            }
        }

        /*------------------------------------------------------ */
        //-- 编辑数量单位
        /*------------------------------------------------------ */

        if ($act == 'edit_measure_unit') {
            $check_auth = check_authz_json('cat_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = (int)request()->input('id', 0);
            $val = json_str_iconv(request()->input('val', ''));

            if ($this->cat_update($id, ['measure_unit' => $val])) {
                clear_cache_files(); // 清除缓存
                return make_json_result($val);
            } else {
                return make_json_error($this->db->error());
            }
        }

        /*------------------------------------------------------ */
        //-- 编辑排序序号
        /*------------------------------------------------------ */

        if ($act == 'edit_grade') {
            $check_auth = check_authz_json('cat_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = (int)request()->input('id', 0);
            $val = (int)request()->input('val', 0);

            if ($val > 10 || $val < 0) {
                /* 价格区间数超过范围 */
                return make_json_error($GLOBALS['_LANG']['grade_error']);
            }

            if ($this->cat_update($id, ['grade' => $val])) {
                clear_cache_files(); // 清除缓存
                return make_json_result($val);
            } else {
                return make_json_error($this->db->error());
            }
        }

        /*------------------------------------------------------ */
        //-- 切换是否显示在导航栏
        /*------------------------------------------------------ */

        if ($act == 'toggle_show_in_nav') {
            $check_auth = check_authz_json('cat_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = (int)request()->input('id', 0);
            $val = (int)request()->input('val', 0);

            if ($this->cat_update($id, ['show_in_nav' => $val]) != false) {
                if ($val == 1) {
                    //显示
                    $vieworder = Nav::where('type', 'middle')->max('vieworder');
                    $vieworder += 2;
                    $catname = Category::where('cat_id', $id)->value('cat_name');
                    //显示在自定义导航栏中
                    $GLOBALS['_CFG']['rewrite'] = 0;
                    $uri = $this->dscRepository->buildUri('category', ['cid' => $id], $catname);

                    $nid = Nav::where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->value('id');
                    $nid = $nid ?? 0;
                    if (empty($nid)) {
                        //不存在
                        $data = [
                            'name' => $catname,
                            'ctype' => 'c',
                            'cid' => $id,
                            'ifshow' => 1,
                            'vieworder' => $vieworder,
                            'opennew' => 0,
                            'url' => $uri,
                            'type' => 'middle',
                        ];

                        Nav::insert($data);
                    } else {
                        Nav::where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->update(['ifshow' => 1]);
                    }
                } else {
                    //去除
                    Nav::where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->update(['ifshow' => 0]);
                }
                clear_cache_files();
                return make_json_result($val);
            } else {
                return make_json_error($this->db->error());
            }
        }

        /*------------------------------------------------------ */
        //-- 切换是否显示
        /*------------------------------------------------------ */

        if ($act == 'toggle_is_show') {
            $check_auth = check_authz_json('cat_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = (int)request()->input('id', 0);
            $val = (int)request()->input('val', 0);

            if ($this->cat_update($id, ['is_show' => $val]) != false) {
                clear_cache_files();
                return make_json_result($val);
            } else {
                return make_json_error($this->db->error());
            }
        }

        /*------------------------------------------------------ */
        //-- 删除类目证件标题 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        if ($act == 'title_remove') {
            $check_auth = check_authz_json('cat_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $dt_id = (int)request()->input('dt_id', 0);
            $cat_id = (int)request()->input('cat_id', 0);

            MerchantsDocumenttitle::where('dt_id', $dt_id)->delete();

            $url = 'category.php?act=titleFileView&cat_id=' . $cat_id;

            return dsc_header("Location: $url\n");
        }

        /*------------------------------------------------------ */
        //-- ajax实现删除分类后页面不刷新 //ecmoban模板堂 --kong
        /*------------------------------------------------------ */
        elseif ($act == 'remove_cat') {
            $check_auth = check_authz_json('cat_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $result = ['error' => 0, 'massege' => '', 'level' => ''];
            /* 初始化分类ID并取得分类名称 */
            $result['level'] = request()->input('level', '');
            $cat_id = (int)request()->input('cat_id', 0);

            $result['cat_id'] = $cat_id;
            $cat_name = Category::where('cat_id', $cat_id)->value('cat_name');
            $cat_name = $cat_name ?? '';

            /* 当前分类下是否有子分类 */
            $cat_count = Category::where('parent_id', $cat_id)->count();

            /* 当前分类下是否存在商品 */
            $goods_count = Goods::where('cat_id', $cat_id)->count();

            /* 如果不存在下级子分类和商品，则删除之 */
            if ($cat_count == 0 && $goods_count == 0) {
                /* 删除分类 */
                if (Category::where('cat_id', $cat_id)->delete()) {
                    Nav::where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->delete();

                    clear_cache_files();
                    admin_log($cat_name, 'remove', 'category');
                    $result['error'] = 1;
                }

                MerchantsDocumenttitle::where('cat_id', $cat_id)->delete();
                MerchantsCategory::where('cat_id', $cat_id)->delete();
            } else {
                $result['error'] = 2;
                $result['massege'] = $cat_name . ' ' . $GLOBALS['_LANG']['cat_isleaf'];
            }
            return response()->json($result);
        }
    }
    /*------------------------------------------------------ */
    //-- PRIVATE FUNCTIONS
    /*------------------------------------------------------ */

    /**
     * 添加商品分类
     *
     * @param integer $cat_id
     * @param array $args
     *
     * @return  mix
     */
    private function cat_update($cat_id, $args)
    {
        if (empty($args) || empty($cat_id)) {
            return false;
        }

        return Category::where('cat_id', $cat_id)->update($args);
    }


    /**
     * 获取属性列表
     *
     * @access  public
     * @param
     *
     * @return void
     */
    private function get_attr_list()
    {
        $arr = Attribute::select('attr_id', 'cat_id', 'attr_name')
                        ->with(['goodsType' => function ($query) {
                            $query->where('enabled', '1');
                        }])
                        ->orderBy('cat_id')
                        ->orderBy('sort_order');

        $arr = BaseRepository::getToArrayGet($arr);

        $list = [];

        foreach ($arr as $val) {
            $list[$val['cat_id']][] = [$val['attr_id'] => $val['attr_name']];
        }

        return $list;
    }

    //ajax分类列表
    private function get_cat_level($parent_id = 0, $level = 0, $ru_id = 0)
    {
        $seller_mainshop_cat = get_seller_mainshop_cat($ru_id);

        if (!$seller_mainshop_cat) {
            return [];
        }

        $seller_cat = explode('-', $seller_mainshop_cat);

        $sarr = [];
        $parent = '';
        $child = '';
        foreach ($seller_cat as $skey => $srow) {
            $seller_main_cat = explode(':', $srow);
            if ($seller_main_cat[0]) {
                $sarr[$skey]['parent_id'] = $seller_main_cat[0];
                $sarr[$skey]['child'] = $seller_main_cat[1];
                $parent .= $sarr[$skey]['parent_id'] . ",";

                if ($sarr[$skey]['parent_id'] == $parent_id) {
                    $child = $sarr[$skey]['child'];
                }
            }
        }

        $res = Category::select('cat_id', 'cat_name', 'measure_unit', 'parent_id', 'is_show', 'show_in_nav', 'grade', 'sort_order');

        if ($level == 2) {
            $res = $res->where('parent_id', $parent_id);
        } else {
            if ($level == 1) {
                $child = BaseRepository::getExplode($child);
                $res = $res->where('parent_id', $parent_id)->whereIn('cat_id', $child);
            } else {
                $parent_id = $this->dscRepository->delStrComma($parent);
                $parent_id = BaseRepository::getExplode($parent_id);
                $res = $res->where('parent_id', 0)->whereIn('cat_id', $parent_id);
            }
        }

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $k => $row) {

                //ecmoban模板堂 --zhuo 查询服分类下子分类下的商品数量 start
                $cat_id_str = get_class_nav($res[$k]['cat_id']);
                $res[$k]['cat_child'] = substr($cat_id_str['catId'], 0, -1);
                if (empty($cat_id_str['catId'])) {
                    $res[$k]['cat_child'] = substr($res[$k]['cat_id'], 0, -1);
                }

                $res[$k]['cat_child'] = isset($res[$k]['cat_child']) && !empty($res[$k]['cat_child']) ? $this->dscRepository->delStrComma($res[$k]['cat_child']) : '';

                $cat_child = BaseRepository::getExplode($res[$k]['cat_child']);
                $goodsNums = Goods::where('is_delete', 0)->where('user_id', $ru_id);

                if ($cat_child) {
                    $goodsNums = $goodsNums->whereIn('cat_id', $cat_child);
                }

                $goodsNums = BaseRepository::getToArrayGet($goodsNums);

                $goods_ids = [];
                foreach ($goodsNums as $num_key => $num_val) {
                    $goods_ids[] = $num_val['goods_id'];
                }

                $goodsCat = get_goodsCat_num($res[$k]['cat_child'], $goods_ids, $ru_id);

                $res[$k]['goods_num'] = count($goodsNums) + $goodsCat;

                $res[$k]['goodsCat'] = $goodsCat; //扩展商品数量
                // $res[$k]['goodsNum'] = $goodsNum; //本身以及子分类的商品数量
                //ecmoban模板堂 --zhuo 查询服分类下子分类下的商品数量 end
                $res[$k]['level'] = $level;
            }
        }

        return $res;
    }
}
