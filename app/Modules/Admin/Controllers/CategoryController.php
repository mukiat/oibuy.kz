<?php

namespace App\Modules\Admin\Controllers;

use App\Libraries\Pinyin;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Goods;
use App\Models\MerchantsDocumenttitle;
use App\Models\Nav;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Category\CategoryManageService;
use App\Services\Category\CategoryService;
use App\Services\CrossBorder\CrossBorderService;

/**
 * 商品分类管理程序
 */
class CategoryController extends InitController
{
    protected $categoryService;
    protected $categoryManageService;
    protected $pinyin;
    protected $dscRepository;

    public function __construct(
        CategoryService $categoryService,
        CategoryManageService $categoryManageService,
        Pinyin $pinyin,
        DscRepository $dscRepository
    ) {
        $this->categoryService = $categoryService;
        $this->categoryManageService = $categoryManageService;
        $this->pinyin = $pinyin;
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {

        /* act操作项的初始化 */
        $act = request()->get('act', 'list');

        $adminru = get_admin_ru_id();

        if (CROSS_BORDER === true) { // 跨境多商户
            $admin = app(CrossBorderService::class)->adminExists();

            if (!empty($admin)) {
                $admin->smartyAssign();
            }
        }

        $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => '03_category_list']);

        /*佣金模式 by wu*/
        $this->smarty->assign('commission_model', config('shop.commission_model'));
        /*------------------------------------------------------ */
        //-- 商品分类列表
        /*------------------------------------------------------ */
        if ($act == 'list') {
            $parent_id = request()->get('parent_id', 0);

            //返回上一页 start
            $back_level = request()->get('back_level', 0);
            if ($back_level > 0) {
                $level = $back_level - 1;
                $parent_id = Category::where('cat_id', $parent_id)->value('parent_id');
                $parent_id = $parent_id ? $parent_id : 0;
            } else {
                $level = request()->has('level') ? request()->get('level', 0) + 1 : 0;
            }
            //返回上一页 end

            $this->smarty->assign('level', $level);
            $this->smarty->assign('parent_id', $parent_id);

            /* 获取分类列表 */
            $cat_list = $this->categoryManageService->getCatLevel($parent_id, $level);

            $this->smarty->assign('cat_info', $cat_list);
            $this->smarty->assign('ru_id', $adminru['ru_id']);

            if ($adminru['ru_id'] == 0) {
                $this->smarty->assign('action_link', ['href' => 'category.php?act=add', 'text' => __('admin::common.04_category_add')]);
            }

            /* 模板赋值 */
            $this->smarty->assign('ur_here', __('admin::common.03_category_list'));
            $this->smarty->assign('full_page', 1);

            $cat_level = [__('admin::common.num_1'), __('admin::common.num_2'), __('admin::common.num_3'), __('admin::common.num_4'), __('admin::common.num_5'), __('admin::common.num_6'), __('admin::common.num_7'), __('admin::common.num_8'), __('admin::common.num_9'), __('admin::common.num_10')];
            $this->smarty->assign('cat_level', $cat_level[$level]);

            /* 佣金模式 */
            $this->smarty->assign('commission_model', config('shop.commission_model'));

            /* 列表页面 */

            return $this->smarty->display('category_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 排序、分页、查询
        /*------------------------------------------------------ */
        elseif ($act == 'query') {

            /* 获取分类列表 */
            $cat_list = $this->categoryManageService->getCatLevel();
            $this->smarty->assign('cat_info', $cat_list);
            $this->smarty->assign('ru_id', $adminru['ru_id']);

            return make_json_result($this->smarty->fetch('category_list.dwt'));
        }

        /*------------------------------------------------------ */
        //-- 添加商品分类
        /*------------------------------------------------------ */
        if ($act == 'add') {
            /* 权限检查 */
            admin_priv('cat_manage');

            $parent_id = request()->get('parent_id', 0);
            if (!empty($parent_id)) {
                set_default_filter(0, $parent_id); //设置默认筛选
                $this->smarty->assign('parent_category', get_every_category($parent_id)); //上级分类
                $this->smarty->assign('parent_id', $parent_id); //上级分类
            } else {
                set_default_filter(); //设置默认筛选
            }
            //属性分类
            $type_level = get_type_cat_arr(0, 0, 0, $adminru['ru_id']);
            $this->smarty->assign('type_level', $type_level);

            /* 模板赋值 */
            $this->smarty->assign('ur_here', __('admin::common.04_category_add'));
            $this->smarty->assign('action_link', ['href' => 'category.php?act=list', 'text' => __('admin::common.03_category_list')]);

            $this->smarty->assign('goods_type_list', goods_type_list(0)); // 取得商品类型
            $this->smarty->assign('attr_list', $this->categoryManageService->getAttrList()); // 取得商品属性
            $this->smarty->assign('form_act', 'insert');
            $this->smarty->assign('cat_info', ['is_show' => 1]);

            $this->smarty->assign('ru_id', $adminru['ru_id']);

            /* 显示页面 */

            return $this->smarty->display('category_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 删除分类菜单图片 by wu
        /*------------------------------------------------------ */
        if ($act == 'delete_icon') {
            /* 权限检查 */
            admin_priv('cat_manage');

            $result = ['error' => 0, 'msg' => ''];
            $cat_id = request()->get('cat_id', 0);

            $cat_info = Category::catInfo($cat_id)->first();
            $cat_info = $cat_info ? $cat_info->toArray() : [];

            if (!empty($cat_info)) {
                Category::where('cat_id', $cat_id)->update([
                    'cat_icon' => ''
                ]);

                dsc_unlink(storage_public($cat_info['cat_icon']));
                $result = ['error' => 1, 'msg' => __('admin::category.delete_ok')];
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
            $cat['cat_id'] = request()->get('cat_id', 0);
            $cat['parent_id'] = request()->get('parent_id', 0);
            $cat['level'] = count(get_select_category($cat['parent_id'], 1, true)) - 2;

            $link[0]['text'] = __('admin::common.go_back');

            if ($cat['cat_id'] > 0) {
                $link[0]['href'] = 'category.php?act=edit&cat_id=' . $cat['cat_id'];
            } else {
                $link[0]['href'] = 'category.php?act=add&parent_id=' . $cat['parent_id'];
            }

            if ($cat['level'] > 1 && $adminru['ru_id'] == 0) {
                return sys_msg(__('admin::common.cat_prompt_notic_one'), 0, $link);
            }

            if ($cat['level'] < 2 && $adminru['ru_id'] > 0) {
                return sys_msg(__('admin::common.cat_prompt_notic_two'), 0, $link);
            }

            $cat_icon = request()->file('cat_icon');
            if ($cat_icon && $cat_icon->isValid()) {
                $cat_icon_path = [
                    storage_public(DATA_DIR . "/cat_icon"),
                    DATA_DIR . "/cat_icon/"
                ];
                $cat['cat_icon'] = $this->categoryManageService->getTouchImagesUpload($cat_icon->getClientOriginalName(), $cat_icon->getRealPath(), $cat_icon->getClientSize(), $cat_icon_path, $link);

                if (is_array($cat['cat_icon']) && $cat['cat_icon']['error'] == 1) {
                    return sys_msg($cat['cat_icon']['msg'], 0, $link);
                }
            }

            $touch_icon = request()->file('touch_icon');
            if ($touch_icon && $touch_icon->isValid()) {
                $touch_icon_path = [
                    storage_public(DATA_DIR . "/touch_icon"),
                    DATA_DIR . "/touch_icon/"
                ];
                $cat['touch_icon'] = $this->categoryManageService->getTouchImagesUpload($touch_icon->getClientOriginalName(), $touch_icon->getRealPath(), $touch_icon->getClientSize(), $touch_icon_path, $link);

                if (is_array($cat['touch_icon']) && $cat['touch_icon']['error'] == 1) {
                    return sys_msg($cat['touch_icon']['msg'], 0, $link);
                }
            }
            
            $touch_catads = request()->file('touch_catads');
            if ($touch_catads && $touch_catads->isValid()) {
                $touch_catads_path = [
                    storage_public(DATA_DIR . "/touch_catads"),
                    DATA_DIR . "/touch_catads/"
                ];
                $cat['touch_catads'] = $this->categoryManageService->getTouchImagesUpload($touch_catads->getClientOriginalName(), $touch_catads->getRealPath(), $touch_catads->getClientSize(), $touch_catads_path, $link);

                if (is_array($cat['touch_catads']) && $cat['touch_catads']['error'] == 1) {
                    return sys_msg($cat['touch_catads']['msg'], 0, $link);
                }
            }

            //手机分类列表广告图
            $cat['touch_catads_url'] = request()->get('touch_catads_url', '');
            $cat['touch_catads_url'] = $cat['touch_catads_url'] ?? '';
            
            //app分类列表广告图
            $cat['app_touch_catads_url'] = request()->get('app_touch_catads_url', '');
            $cat['app_touch_catads_url'] = $cat['app_touch_catads_url'] ?? '';
            //小程序分类列表广告图
            $cat['wxapp_touch_catads_url'] = request()->get('wxapp_touch_catads_url', '');
            $cat['wxapp_touch_catads_url'] = $cat['wxapp_touch_catads_url'] ?? '';

            //佣金比率 by wu
            $cat['commission_rate'] = request()->get('commission_rate', 0);
            $cat['commission_rate'] = $cat['commission_rate'] ?? 0;

            if ($cat['commission_rate'] > 100 || $cat['commission_rate'] < 0) {
                return sys_msg(__('admin::common.commission_rate_prompt'), 0, $link);
            }

            $cat['style_icon'] = request()->get('style_icon', 'other'); //分类菜单图标
            $cat['sort_order'] = request()->get('sort_order', 0);
            $cat['keywords'] = request()->get('keywords', '');
            $cat['keywords'] = $cat['keywords'] ?? '';
            $cat['cat_desc'] = request()->get('cat_desc', '');
            $cat['cat_desc'] = $cat['cat_desc'] ?? '';
            $cat['measure_unit'] = request()->get('measure_unit', '');
            $cat['measure_unit'] = $cat['measure_unit'] ?? '';
            $cat['cat_name'] = request()->get('cat_name', '');
            $cat['cat_alias_name'] = request()->get('cat_alias_name', '');
            $cat['cat_alias_name'] = $cat['cat_alias_name'] ?? '';

            $pinyin = $this->pinyin->Pinyin($cat['cat_name'], 'UTF8');
            $cat['pinyin_keyword'] = $pinyin;

            $cat['show_in_nav'] = request()->get('show_in_nav', 0);
            $cat['is_show'] = request()->get('is_show', 0);

            /* by zhou */
            $cat['is_top_show'] = request()->get('is_top_show', 0);
            $cat['is_top_style'] = request()->get('is_top_style', 0);
            $cat['top_style_tpl'] = request()->get('top_style_tpl', 0); //顶级分类页模板 by wu
            $cat['top_style_tpl'] = $cat['top_style_tpl'] ?? 0;

            /* by zhou */
            $cat['grade'] = request()->get('grade', 0);
            $filter_attr = request()->get('filter_attr', []);
            $cat['filter_attr'] = !empty($filter_attr) ? implode(',', array_unique(array_diff($filter_attr, [0]))) : 0;

            $cat['cat_recommend'] = request()->get('cat_recommend', []);

            $cat_exists = Category::where('cat_name', $cat['cat_name'])
                ->where('parent_id', $cat['parent_id'])
                ->count();

            if ($cat_exists > 0) {
                /* 同级别下不能有重复的分类名称 */
                $link[] = ['text' => __('admin::common.go_back'), 'href' => 'javascript:history.back(-1)'];
                return sys_msg(__('admin::category.catname_exist'), 0, $link);
            }

            if ($cat['grade'] > 10 || $cat['grade'] < 0) {
                /* 价格区间数超过范围 */
                $link[] = ['text' => __('admin::common.go_back'), 'href' => 'javascript:history.back(-1)'];
                return sys_msg(__('admin::category.grade_error'), 0, $link);
            }

            /* 入库的操作 */
            $cat_name = explode(',', $cat['cat_name']);

            if (count($cat_name) > 1) {
                $cat['is_show_merchants'] = request()->get('is_show_merchants', 0);

                $this->categoryManageService->getBacthCategory($cat_name, $cat);

                clear_cache_files();    // 清除缓存

                /* 添加链接 */
                $link[0]['text'] = __('admin::common.continue_add');
                $link[0]['href'] = 'category.php?act=add&parent_id=' . $cat['parent_id'];

                $link[1]['text'] = __('admin::common.back_list');
                $link[1]['href'] = 'category.php?act=list&parent_id=' . $cat['parent_id'] . '&level=' . $cat['level'];

                return sys_msg(__('admin::category.catadd_succed'), 0, $link);
            } else {
                $catOther = BaseRepository::getArrayfilterTable($cat, 'category');

                $cat_id = Category::insertGetId($catOther);

                if ($cat_id > 0) {
                    $cachelist = [
                        'category_tree_child' . $cat['parent_id'],
                        'category_tree_leve_one'
                    ];
                    BaseRepository::getCacheForgetlist($cachelist);

                    if ($cat['show_in_nav'] == 1) {
                        $vieworder = Nav::where('type', 'middle')->max('vieworder');
                        $vieworder = $vieworder ? $vieworder : 0;

                        $vieworder += 2;

                        //显示在自定义导航栏中
                        $other = [
                            'name' => $cat['cat_name'],
                            'ctype' => 'c',
                            'cid' => $cat_id,
                            'ifshow' => 1,
                            'vieworder' => $vieworder,
                            'opennew' => 0,
                            'url' => $this->dscRepository->buildUri('category', ['cid' => $cat_id], $cat['cat_name']),
                            'type' => 'middle'
                        ];
                        Nav::insert($other);
                    }

                    $this->categoryService->getInsertCatRecommend($cat['cat_recommend'], $cat_id);

                    admin_log($cat['cat_name'], 'add', 'category');   // 记录管理员操作

                    $dt_list = request()->get('document_title', []);
                    $dt_id = request()->get('dt_id', []);

                    $this->categoryManageService->setDocumentTitleInsertUpdate($dt_list, $cat_id, $dt_id);

                    clear_cache_files();    // 清除缓存
                }

                /* 添加链接 */
                $link[0]['text'] = __('admin::common.continue_add');
                $link[0]['href'] = 'category.php?act=add&parent_id=' . $cat['parent_id'];

                $link[1]['text'] = __('admin::common.back_list');
                $link[1]['href'] = 'category.php?act=list&parent_id=' . $cat['parent_id'] . '&level=' . $cat['level'];

                return sys_msg(__('admin::category.catadd_succed'), 0, $link);
            }
        }

        /*------------------------------------------------------ */
        //-- 编辑商品分类信息
        /*------------------------------------------------------ */
        if ($act == 'edit') {
            admin_priv('cat_manage');   // 权限检查
            $cat_id = request()->get('cat_id', 0);

            $cat_info = $this->categoryManageService->getCategoryInfo($cat_id);

            $attr_list = $this->categoryManageService->getAttrList();
            $filter_attr_list = [];

            //获取下拉列表 by wu start
            $this->smarty->assign('parent_id', $cat_info['parent_id']); //上级分类
            $this->smarty->assign('parent_category', get_every_category($cat_info['parent_id'])); //上级分类导航
            set_default_filter(0, $cat_info['parent_id']); //设置默认筛选
            //获取下拉列表 by wu end

            if ($cat_info['filter_attr']) {
                $filter_attr = explode(",", $cat_info['filter_attr']);  //把多个筛选属性放到数组中

                foreach ($filter_attr as $k => $v) {
                    $attr_cat_id = Attribute::where('attr_id', $v)->value('cat_id');
                    $attr_cat_id = $attr_cat_id ? $attr_cat_id : 0;

                    $filter_attr_list[$k]['goods_type_list'] = goods_type_list($attr_cat_id);  //取得每个属性的商品类型
                    $filter_attr_list[$k]['goods_type'] = $attr_cat_id;  //by wu
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

            //属性分类
            $type_level = get_type_cat_arr(0, 0, 0, $adminru['ru_id']);
            $this->smarty->assign('type_level', $type_level);

            /* 模板赋值 */
            if ($cat_info['parent_id'] == 0) {
                $cat_name_arr = explode('、', $cat_info['cat_name']);
                $this->smarty->assign('cat_name_arr', $cat_name_arr); // 取得商品属性
            }

            $this->smarty->assign('attr_list', $attr_list); // 取得商品属性
            $this->smarty->assign('attr_cat_id', $attr_cat_id);
            $this->smarty->assign('ur_here', __('admin::category.category_edit'));
            $this->smarty->assign('action_link', ['text' => __('admin::common.03_category_list'), 'href' => 'category.php?act=list']);

            //分类是否存在首页推荐
            $res = $this->categoryManageService->getCatRecommendList($cat_id);

            $cat_recommend = [];
            if (!empty($res)) {
                foreach ($res as $data) {
                    $cat_recommend[$data['recommend_type']] = 1;
                }
            }

            $this->smarty->assign('cat_recommend', $cat_recommend);

            $title_list = $this->categoryManageService->getMerchantsDocumenttitleList($cat_id);

            $this->smarty->assign('title_list', $title_list);
            $this->smarty->assign('cat_id', $cat_id);

            $this->smarty->assign('ru_id', $adminru['ru_id']);

            $this->smarty->assign('cat_info', $cat_info);
            $this->smarty->assign('form_act', 'update');
            $this->smarty->assign('goods_type_list', goods_type_list(0)); // 取得商品类型

            /* 显示页面 */

            return $this->smarty->display('category_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 编辑类目证件标题
        /*------------------------------------------------------ */
        elseif ($act == 'titleFileView') {
            $cat_id = request()->get('cat_id', 0);

            $title_list = $this->categoryManageService->getMerchantsDocumenttitleList($cat_id);

            $this->smarty->assign('title_list', $title_list);
            $this->smarty->assign('cat_id', $cat_id);
            $this->smarty->assign('form_act', 'title_update');

            $cat_name = Category::where('cat_id', $cat_id)->value('cat_name');
            $cat_name = $cat_name ? $cat_name : '';

            $this->smarty->assign('cat_name', $cat_name);

            $this->smarty->assign('action_link', ['href' => 'category.php?act=edit&cat_id=' . $cat_id, 'text' => __('admin::common.go_back')]);

            /* 显示页面 */

            return $this->smarty->display('category_titleFileView.dwt');
        }

        /*------------------------------------------------------ */
        //-- 更新类目证件标题
        /*------------------------------------------------------ */
        elseif ($act == 'title_update') {
            $cat_id = request()->get('cat_id', 0);

            $dt_list = request()->get('document_title', []);
            $dt_id = request()->get('dt_id', []);

            $this->categoryManageService->setDocumentTitleInsertUpdate($dt_list, $cat_id, $dt_id);

            clear_cache_files(); // 清除缓存

            /* 提示信息 */
            $link[] = ['text' => __('admin::common.go_back'), 'href' => 'category.php?act=titleFileView&cat_id=' . $cat_id];
            return sys_msg(__('admin::category.title_catedit_succed'), 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 添加分类
        /*------------------------------------------------------ */
        elseif ($act == 'add_category') {
            $parent_id = request()->get('parent_id', 0);
            $category = json_str_iconv(request()->get('cat', ''));

            $cat_exists = Category::where('cat_name', $category)
                ->where('parent_id', $parent_id)
                ->count();

            if ($cat_exists > 0) {
                return make_json_error(__('admin::category.catname_exist'));
            } else {
                $other = [
                    'cat_name' => $category,
                    'parent_id' => $parent_id,
                    'is_show' => 1
                ];
                $category_id = Category::insertGetId($other);

                $arr = ["parent_id" => $parent_id, "id" => $category_id, "cat" => $category];

                clear_cache_files();    // 清除缓存

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
            $cat_id = $cat['cat_id'] = request()->get('cat_id', 0);

            $cat['parent_id'] = request()->get('parent_id', 0);
            $cat['level'] = count(get_select_category($cat['parent_id'], 1, true)) - 2;
            $old_cat_name = request()->get('old_cat_name', '');

            $link[0]['text'] = __('admin::common.go_back');
            if ($cat['cat_id'] > 0) {
                $link[0]['href'] = 'category.php?act=edit&cat_id=' . $cat['cat_id'];
            } else {
                $link[0]['href'] = 'category.php?act=add&parent_id=' . $cat['parent_id'];
            }

            $reject_cat = $this->categoryService->getCatListChildren($cat_id);

            if ($cat['parent_id'] == $cat_id || in_array($cat['parent_id'], $reject_cat)) {
                return sys_msg(__('admin::common.cat_prompt_notic_three'), 1, $link);
            }

            if ($cat['level'] > 1 && $adminru['ru_id'] == 0) {
                return sys_msg(__('admin::common.cat_prompt_notic_one'), 1, $link);
            }

            if ($cat['level'] < 2 && $adminru['ru_id'] > 0) {
                return sys_msg(__('admin::common.cat_prompt_notic_two'), 1, $link);
            }

            $cat_info = Category::catInfo($cat_id);
            $cat_info = BaseRepository::getToArrayFirst($cat_info);

            //上传分类菜单图标 by wu start
            $cat_icon = request()->file('cat_icon');
            if ($cat_icon && $cat_icon->isValid()) {
                $cat_icon_path = [
                    storage_public(DATA_DIR . "/cat_icon"),
                    DATA_DIR . "/cat_icon/"
                ];
                $cat['cat_icon'] = $this->categoryManageService->getTouchImagesUpload($cat_icon->getClientOriginalName(), $cat_icon->getRealPath(), $cat_icon->getClientSize(), $cat_icon_path, $link);

                if (is_array($cat['cat_icon']) && $cat['cat_icon']['error'] == 1) {
                    return sys_msg($cat['cat_icon']['msg'], 0, $link);
                }

                //删除文件
                if ($cat_info['cat_icon']) {
                    dsc_unlink(storage_public($cat_info['cat_icon']));
                    $this->dscRepository->getOssDelFile([$cat_info['cat_icon']]);
                }
            }
            //上传分类菜单图标 by wu end

            //上传分类菜单图标 by kong start
            $touch_icon = request()->file('touch_icon');
            if ($touch_icon && $touch_icon->isValid()) {
                $touch_icon_path = [
                    storage_public(DATA_DIR . "/touch_icon"),
                    DATA_DIR . "/touch_icon/"
                ];

                $cat['touch_icon'] = $this->categoryManageService->getTouchImagesUpload($touch_icon->getClientOriginalName(), $touch_icon->getRealPath(), $touch_icon->getClientSize(), $touch_icon_path, $link);

                if (is_array($cat['touch_icon']) && $cat['touch_icon']['error'] == 1) {
                    return sys_msg($cat['touch_icon']['msg'], 0, $link);
                }

                //删除文件
                if ($cat_info['touch_icon']) {
                    dsc_unlink(storage_public($cat_info['touch_icon']));
                    $this->dscRepository->getOssDelFile([$cat_info['touch_icon']]);
                }
            }
            //上传手机菜单图标 by kong end

            //手机分类列表广告图 by kong start
            $touch_catads = request()->file('touch_catads');
            if ($touch_catads && $touch_catads->isValid()) {
                $touch_catads_path = [
                    storage_public(DATA_DIR . "/touch_catads"),
                    DATA_DIR . "/touch_catads/"
                ];
                $cat['touch_catads'] = $this->categoryManageService->getTouchImagesUpload($touch_catads->getClientOriginalName(), $touch_catads->getRealPath(), $touch_catads->getClientSize(), $touch_catads_path, $link);

                if (is_array($cat['touch_catads']) && $cat['touch_catads']['error'] == 1) {
                    return sys_msg($cat['touch_catads']['msg'], 0, $link);
                }

                //删除文件
                if ($cat_info['touch_catads']) {
                    dsc_unlink(storage_public($cat_info['touch_catads']));
                    $this->dscRepository->getOssDelFile([$cat_info['touch_catads']]);
                }
            }
            //手机分类列表广告图 by kong end

            //手机分类列表广告图
            $cat['touch_catads_url'] = request()->get('touch_catads_url', '');

            //app分类列表广告图
            $cat['app_touch_catads_url'] = request()->get('app_touch_catads_url', '');
            //小程序分类列表广告图
            $cat['wxapp_touch_catads_url'] = request()->get('wxapp_touch_catads_url', '');

            //佣金比率 by wu
            $cat['commission_rate'] = request()->get('commission_rate', 0);
            if ($cat['commission_rate'] > 100 || $cat['commission_rate'] < 0) {
                return sys_msg(__('admin::common.commission_rate_prompt'), 0, $link);
            }

            $cat['style_icon'] = request()->get('style_icon', 'other'); //分类菜单图标
            $cat['sort_order'] = request()->get('sort_order', 0);
            $cat['keywords'] = request()->get('keywords', '');
            $cat['cat_desc'] = request()->get('cat_desc', '');
            $cat['measure_unit'] = request()->get('measure_unit', '');
            $cat['cat_name'] = request()->get('cat_name', '');
            $cat['cat_alias_name'] = request()->get('cat_alias_name', '');
            $cat['category_links'] = request()->get('category_links', '');
            $cat['category_topic'] = request()->get('category_topic', '');

            if (CROSS_BORDER === true) { // 跨境多商户
                $cat['rate'] = floatval(request()->get('rate', ''));
            }

            //by guan start
            $pin = new Pinyin();
            $pinyin = $pin->Pinyin($cat['cat_name'], 'UTF8');

            $cat['pinyin_keyword'] = $pinyin;
            //by guan end

            $cat['is_show'] = request()->get('is_show', 0);
            $cat['is_top_show'] = request()->get('is_top_show', 0);
            $cat['is_top_style'] = request()->get('is_top_style', 0);
            $cat['top_style_tpl'] = request()->get('top_style_tpl', 0); //顶级分类页模板
            $cat['floor_style_tpl'] = request()->get('floor_style_tpl', 0); //首页楼层模板
            $cat['show_in_nav'] = request()->get('show_in_nav', 0);
            $cat['grade'] = request()->get('grade', 0);
            $filter_attr = request()->get('filter_attr', []);
            $cat['filter_attr'] = !empty($filter_attr) ? implode(',', array_unique(array_diff($filter_attr, [0]))) : 0;
            $cat['cat_recommend'] = request()->get('cat_recommend', []);

            /* 判断分类名是否重复 */
            if ($cat['cat_name'] != $old_cat_name) {
                if (cat_exists($cat['cat_name'], $cat['parent_id'], $cat_id)) {
                    $link[] = ['text' => __('admin::common.go_back'), 'href' => 'javascript:history.back(-1)'];
                    return sys_msg(__('admin::category.catname_exist'), 0, $link);
                }
            }

            /* 判断上级目录是否合法 */
            $children = $this->categoryService->getCatListChildren($cat_id);     // 获得当前分类的所有下级分类
            if (in_array($cat['parent_id'], $children)) {
                /* 选定的父类是当前分类或当前分类的下级分类 */
                $link[] = ['text' => __('admin::common.go_back'), 'href' => 'javascript:history.back(-1)'];
                return sys_msg(__('admin::category.is_leaf_error'), 0, $link);
            }

            if ($cat['grade'] > 10 || $cat['grade'] < 0) {
                /* 价格区间数超过范围 */
                $link[] = ['text' => __('admin::common.go_back'), 'href' => 'javascript:history.back(-1)'];
                return sys_msg(__('admin::category.grade_error'), 0, $link);
            }

            $cachelist = [
                'category_tree_child' . $cat['parent_id'],
                'category_tree_leve_one'
            ];
            BaseRepository::getCacheForgetlist($cachelist);

            $dat = $this->categoryManageService->getCategoryInfo($cat_id);

            $catOther = BaseRepository::getArrayfilterTable($cat, 'category');

            $res = Category::where('cat_id', $cat_id)->update($catOther);

            if ($res >= 0) {
                if ($cat['cat_name'] != $dat['cat_name']) {
                    //如果分类名称发生了改变
                    Nav::where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->update([
                        'name' => $cat['cat_name']
                    ]);
                }

                if ($cat['show_in_nav'] != $dat['show_in_nav']) {
                    //是否显示于导航栏发生了变化
                    if ($cat['show_in_nav'] == 1) {

                        //显示
                        $nid = Nav::where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->value('id');
                        $nid = $nid ? $nid : 0;

                        if (empty($nid)) {

                            //不存在
                            $vieworder = Nav::where('type', 'middle')->max('vieworder');
                            $vieworder = $vieworder ? $vieworder : 0;

                            $vieworder += 2;
                            $uri = $this->dscRepository->buildUri('category', ['cid' => $cat_id], $cat['cat_name']);

                            $other = [
                                'name' => $cat['cat_name'],
                                'ctype' => 'c',
                                'cid' => $cat_id,
                                'ifshow' => 1,
                                'vieworder' => $vieworder,
                                'opennew' => 0,
                                'url' => $uri,
                                'type' => 'middle'
                            ];
                            Nav::insert($other);
                        } else {
                            Nav::where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->update([
                                'ifshow' => 1
                            ]);
                        }
                    } else {
                        //去除
                        Nav::where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->update([
                            'ifshow' => 0
                        ]);
                    }
                }

                //更新首页推荐
                $this->categoryService->getInsertCatRecommend($cat['cat_recommend'], $cat_id);
                /* 更新分类信息成功 */

                $dt_list = request()->get('document_title', []);
                $dt_id = request()->get('dt_id', []);
                $this->categoryManageService->setDocumentTitleInsertUpdate($dt_list, $cat_id, $dt_id);

                clear_cache_files(); // 清除缓存
                admin_log($cat['cat_name'], 'edit', 'category'); // 记录管理员操作
            }

            /* 提示信息 */
            $link[] = ['text' => __('admin::common.back_list'), 'href' => 'category.php?act=list&parent_id=' . $cat['parent_id'] . '&level=' . $cat['level']];
            return sys_msg(__('admin::category.catedit_succed'), 0, $link);
        }

        /* ------------------------------------------------------ */
        //-- 批量转移商品分类页面
        /* ------------------------------------------------------ */
        if ($act == 'move') {
            $check_auth = check_authz_json('cat_drop');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $cat_id = request()->get('cat_id', 0);

            //获取下拉列表 by wu start
            $this->smarty->assign('parent_id', $cat_id); //上级分类
            $this->smarty->assign('parent_category', get_every_category($cat_id)); //上级分类导航
            set_default_filter(0, $cat_id); //设置默认筛选
            //获取下拉列表 by wu end

            /* 模板赋值 */
            $this->smarty->assign('ur_here', __('admin::category.move_goods'));
            $this->smarty->assign('action_link', ['href' => 'category.php?act=list', 'text' => __('admin::common.03_category_list')]);

            $this->smarty->assign('form_act', 'move_cat');

            $html = $this->smarty->fetch("library/move_category.lbi");

            clear_cache_files();
            return make_json_result($html);
        }

        /*------------------------------------------------------ */
        //-- 处理批量转移商品分类的处理程序
        /*------------------------------------------------------ */
        if ($act == 'move_cat') {
            /* 权限检查 */
            admin_priv('cat_drop');

            $cat_id = request()->get('cat_id', 0);
            $target_cat_id = request()->get('target_cat_id', 0);

            /* 商品分类不允许为空 */
            if ($cat_id == 0 || $target_cat_id == 0) {
                $link[] = ['text' => __('admin::common.go_back'), 'href' => 'category.php?act=move'];
                return sys_msg(__('admin::category.cat_move_empty'), 0, $link);
            }

            $children = $this->categoryService->getCatListChildren($cat_id);

            /* 更新商品分类 */
            Goods::where('user_id', $adminru['ru_id'])
                ->whereIn('cat_id', $children)
                ->update([
                    'cat_id' => $target_cat_id
                ]);

            /* 提示信息 */
            $link[] = ['text' => __('admin::common.go_back'), 'href' => 'category.php?act=list'];
            return sys_msg(__('admin::category.move_cat_success'), 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 编辑排序序号
        /*------------------------------------------------------ */
        if ($act == 'edit_sort_order') {
            $check_auth = check_authz_json('cat_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = request()->get('id', 0);
            $val = request()->get('val', 0);

            Category::where('cat_id', $id)->update([
                'sort_order' => $val
            ]);

            return make_json_result($val);
        }

        /*------------------------------------------------------ */
        //-- 编辑税率
        /*------------------------------------------------------ */
        if ($act == 'edit_rate') {
            $check_auth = check_authz_json('cat_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = request()->get('id', 0);
            $val = round(floatval(request()->get('val', '')), 2);

            Category::where('cat_id', $id)->update([
                'rate' => $val
            ]);

            return make_json_result($val);
        }

        /*------------------------------------------------------ */
        //-- 编辑数量单位
        /*------------------------------------------------------ */
        if ($act == 'edit_measure_unit') {
            $check_auth = check_authz_json('cat_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = request()->get('id', 0);
            $val = json_str_iconv(request()->get('val', ''));

            Category::where('cat_id', $id)->update([
                'measure_unit' => $val
            ]);

            Goods::where('cat_id', $id)->update(['goods_unit' => $val]);

            return make_json_result($val);
        }

        /*------------------------------------------------------ */
        //-- 编辑排序序号
        /*------------------------------------------------------ */

        if ($act == 'edit_grade') {
            $check_auth = check_authz_json('cat_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = request()->get('id', 0);
            $val = request()->get('val', 0);

            if ($val > 10 || $val < 0) {
                /* 价格区间数超过范围 */
                return make_json_error(__('admin::category.grade_error'));
            }

            Category::where('cat_id', $id)->update([
                'grade' => $val
            ]);

            return make_json_result($val);
        }

        /*------------------------------------------------------ */
        //-- 编辑佣金比率
        /*------------------------------------------------------ */
        if ($act == 'edit_commission_rate') {
            $check_auth = check_authz_json('cat_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = request()->get('id', 0);
            $val = request()->get('val', 0);

            if ($val > 100 || $val < 0) {
                return make_json_error(__('admin::common.commission_rate_prompt'));
            }

            Category::where('cat_id', $id)->update([
                'commission_rate' => $val
            ]);

            return make_json_result($val);
        }

        /*------------------------------------------------------ */
        //-- 切换是否显示在导航栏
        /*------------------------------------------------------ */

        if ($act == 'toggle_show_in_nav') {
            $check_auth = check_authz_json('cat_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = request()->get('id', 0);
            $val = request()->get('val', 0);

            $res = Category::where('cat_id', $id)->update([
                'show_in_nav' => $val
            ]);

            if ($res) {
                if ($val == 1) {
                    //显示
                    $vieworder = Nav::where('type', 'middle')->max('vieworder');
                    $vieworder = $vieworder ? $vieworder : 0;

                    $vieworder += 2;

                    $catname = Category::where('cat_id', $id)->value('cat_name');
                    $catname = $catname ? $catname : '';

                    //显示在自定义导航栏中
                    $uri = $this->dscRepository->buildUri('category', ['cid' => $id], $catname);

                    $nid = Nav::where('ctype', 'c')->where('cid', $id)->where('type', 'middle')->value('id');
                    $nid = $nid ? $nid : 0;

                    if (empty($nid)) {
                        $other = [
                            'name' => $catname,
                            'ctype' => 'c',
                            'cid' => $id,
                            'ifshow' => 1,
                            'vieworder' => $vieworder,
                            'opennew' => 0,
                            'url' => $uri,
                            'type' => 'middle'
                        ];
                        Nav::insert($other);
                    } else {
                        Nav::where('ctype', 'c')->where('cid', $id)->where('type', 'middle')->update([
                            'ifshow' => 1
                        ]);
                    }
                } else {
                    //去除
                    Nav::where('ctype', 'c')->where('cid', $id)->where('type', 'middle')->update([
                        'ifshow' => 0
                    ]);
                }

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

            $id = request()->get('id', 0);
            $val = request()->get('val', 0);

            $children = $this->categoryService->getCatListChildren($id);

            //隐藏分类下所有商品
            if ($children) {
                Goods::whereIn('cat_id', $children)
                    ->update([
                        'is_show' => $val
                    ]);
            }

            Category::where('cat_id', $id)->update([
                'is_show' => $val
            ]);

            return make_json_result($val);
        }

        /*------------------------------------------------------ */
        //-- 删除分类 ajax实现删除分类后页面不刷新 //ecmoban模板堂 --kong
        /*------------------------------------------------------ */
        elseif ($act == 'remove') {
            $check_auth = check_authz_json('cat_drop');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $result = ['error' => 0, 'massege' => '', 'level' => ''];
            /* 初始化分类ID并取得分类名称 */
            $result['level'] = request()->get('level', 0);
            $cat_id = request()->get('cat_id', 0);
            $result['cat_id'] = $cat_id;

            $cat_name = Category::where('cat_id', $cat_id)->value('cat_name');
            $cat_name = $cat_name ? $cat_name : '';

            /* 当前分类下是否有子分类 */
            $cat_count = Category::where('parent_id', $cat_id)->count();

            /* 当前分类下是否存在商品 */
            $goods_count = Goods::where('cat_id', $cat_id)->count();

            $cat_info = $this->categoryManageService->getCategoryInfo($cat_id);

            /* 如果不存在下级子分类和商品，则删除之 */
            if ($cat_count == 0 && $goods_count == 0) {
                /* 删除分类 */
                $res = Category::where('cat_id', $cat_id)->delete();

                if ($res) {

                    //删除文件
                    if ($cat_info) {
                        if ($cat_info['cat_icon']) {
                            dsc_unlink(storage_public($cat_info['cat_icon']));
                            $this->dscRepository->getOssDelFile([$cat_info['cat_icon']]);
                        }

                        //删除文件
                        if ($cat_info['touch_icon']) {
                            dsc_unlink(storage_public($cat_info['touch_icon']));
                            $this->dscRepository->getOssDelFile([$cat_info['touch_icon']]);
                        }

                        //删除文件
                        if ($cat_info['touch_catads']) {
                            dsc_unlink(storage_public($cat_info['touch_catads']));
                            $this->dscRepository->getOssDelFile([$cat_info['touch_catads']]);
                        }
                    }

                    Nav::where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->delete();

                    admin_log($cat_name, 'remove', 'category');
                    $result['error'] = 0;
                } else {
                    $result['error'] = 1;
                    $result['message'] = 'delete failed!';
                }

                MerchantsDocumenttitle::where('cat_id', $cat_id)->delete();
            } else {
                $result['error'] = 2;
                $result['massege'] = $cat_name . ' ' . __('admin::category.cat_isleaf');
            }
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 删除类目证件标题 //ecmoban模板堂 --zhuo
        /*------------------------------------------------------ */
        if ($act == 'title_remove') {
            $check_auth = check_authz_json('cat_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $dt_id = request()->get('dt_id', 0);
            $cat_id = request()->get('cat_id', 0);

            MerchantsDocumenttitle::where('dt_id', $dt_id)->delete();

            $url = 'category.php?act=titleFileView&cat_id=' . $cat_id;

            return dsc_header("Location: $url\n");
        }

        /*------------------------------------------------------ */
        //-- 删除图片
        /*------------------------------------------------------ */
        if ($act == 'delete_icon_remove') {
            $check_auth = check_authz_json('cat_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $cat_id = request()->input('cat_id', 0);
            $type = request()->input('type', '');

            switch ($type) {
                case 'touch_catads':
                    $date = [
                        'touch_catads' => ''
                    ];
                    break;
                case 'cat_icon':
                    $date = [
                        'cat_icon' => ''
                    ];
                    break;
                case 'touch_icon':
                    $date = [
                        'touch_icon' => ''
                    ];
                    break;
            }

            Category::where('cat_id', $cat_id)->update($date);

            return make_json_result($type);
        }
    }
}
