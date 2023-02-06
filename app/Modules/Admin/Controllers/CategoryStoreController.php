<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\CatRecommend;
use App\Models\Goods;
use App\Models\MerchantsCategory;
use App\Models\MerchantsNav;
use App\Models\Nav;
use App\Models\ShopConfig;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Category\CategoryService;

/**
 * 商品分类管理程序
 */
class CategoryStoreController extends InitController
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
        /* act操作项的初始化 */
        $act = request()->get('act', 'list');
        $adminru = get_admin_ru_id();
        $this->smarty->assign('ru_id', $adminru['ru_id']);

        $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => '03_store_category_list']);

        /*------------------------------------------------------ */
        //-- 商品分类列表
        /*------------------------------------------------------ */
        if ($act == 'list') {
            $parent_id = request()->get('parent_id', 0);

            //返回上一页 start
            $back_level = request()->get('back_level', 0);
            if ($back_level > 0) {
                $level = $back_level - 1;
                $parent_id = MerchantsCategory::where('cat_id', $parent_id)->value('parent_id');
                $parent_id = $parent_id ? $parent_id : 0;
            } else {
                $level = request()->has('level') ? request()->get('level', 0) + 1 : 0;
            }
            //返回上一页 end

            $this->smarty->assign('level', $level);
            $this->smarty->assign('parent_id', $parent_id);

            if ($parent_id > 0) {
                $cat_info = MerchantsCategory::catInfo($parent_id)->first();
                $cat_info = $cat_info ? $cat_info->toArray() : [];

                $user_id = $cat_info['user_id'];
            } else {
                $user_id = $adminru['ru_id'];
            }

            $cat_list = get_category_store_list($user_id);

            /* 模板赋值 */
            $this->smarty->assign('ur_here', __('admin::common.03_store_category_list'));
            $this->smarty->assign('full_page', 1);

            $this->smarty->assign('cat_info', $cat_list['cate']);
            $this->smarty->assign('filter', $cat_list['filter']);
            $this->smarty->assign('record_count', $cat_list['record_count']);
            $this->smarty->assign('page_count', $cat_list['page_count']);

            $cat_level = [__('admin::common.num_1'), __('admin::common.num_2'), __('admin::common.num_3'), __('admin::common.num_4'), __('admin::common.num_5'), __('admin::common.num_6'), __('admin::common.num_7'), __('admin::common.num_8'), __('admin::common.num_9'), __('admin::common.num_10')];
            $this->smarty->assign('cat_level', $cat_level[$level]);

            /* 列表页面 */

            return $this->smarty->display('category_store_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 排序、分页、查询
        /*------------------------------------------------------ */
        elseif ($act == 'query') {
            $cat_list = get_category_store_list();

            $this->smarty->assign('cat_info', $cat_list['cate']);
            $this->smarty->assign('filter', $cat_list['filter']);
            $this->smarty->assign('record_count', $cat_list['record_count']);
            $this->smarty->assign('page_count', $cat_list['page_count']);
            $this->smarty->assign('level', $cat_list['filter']['level']);
            $this->smarty->assign('parent_id', $cat_list['filter']['parent_id']);

            $cat_level = [__('admin::common.num_1'), __('admin::common.num_2'), __('admin::common.num_3'), __('admin::common.num_4'), __('admin::common.num_5'), __('admin::common.num_6'), __('admin::common.num_7'), __('admin::common.num_8'), __('admin::common.num_9'), __('admin::common.num_10')];
            $this->smarty->assign('cat_level', $cat_level[$cat_list['filter']['level']]);

            return make_json_result(
                $this->smarty->fetch('category_store_list.dwt'),
                '',
                ['filter' => $cat_list['filter'], 'page_count' => $cat_list['page_count']]
            );
        }

        /*------------------------------------------------------ */
        //-- 编辑商品分类信息
        /*------------------------------------------------------ */
        if ($act == 'edit') {
            admin_priv('cat_manage');   // 权限检查
            $cat_id = request()->get('cat_id', 0);

            $cat_info = MerchantsCategory::catInfo($cat_id)->first();
            $cat_info = $cat_info ? $cat_info->toArray() : [];

            $cat_info['is_show_merchants'] = $cat_info ? $cat_info['is_show'] : 0;

            $attr_list = $this->get_attr_list();
            $filter_attr_list = [];

            //获取下拉列表 by wu start
            $this->smarty->assign('parent_id', $cat_info['parent_id']); //上级分类
            $this->smarty->assign('parent_category', get_seller_every_category($cat_info['parent_id'])); //上级分类导航
            set_seller_default_filter(0, $cat_info['parent_id'], $cat_info['user_id']); //设置默认筛选
            //获取下拉列表 by wu end

            //属性分类
            $type_level = get_type_cat_arr(0, 0, 0, $cat_info['user_id']);
            $this->smarty->assign('type_level', $type_level);

            $this->smarty->assign('user_id', $cat_info['user_id']); //商家ID

            if ($cat_info['filter_attr']) {
                $filter_attr = explode(",", $cat_info['filter_attr']);  //把多个筛选属性放到数组中

                foreach ($filter_attr as $k => $v) {
                    $attr_cat_id = Attribute::where('attr_id', intval($v))->value('cat_id');
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

            /* 模板赋值 */

            //by guan start
            if ($cat_info['parent_id'] == 0) {
                $cat_name_arr = explode('、', $cat_info['cat_name']);
                $this->smarty->assign('cat_name_arr', $cat_name_arr); // 取得商品属性
            }
            //by guan end

            $this->smarty->assign('attr_list', $attr_list); // 取得商品属性
            $this->smarty->assign('attr_cat_id', $attr_cat_id);
            $this->smarty->assign('ur_here', __('admin::category_store.category_edit'));
            $this->smarty->assign('action_link', ['text' => __('admin::common.03_category_list'), 'href' => 'category_store.php?act=list']);

            //分类是否存在首页推荐
            $res = CatRecommend::where('cat_id', $cat_id)->get();
            if (!empty($res)) {
                $cat_recommend = [];
                foreach ($res as $data) {
                    $cat_recommend[$data['recommend_type']] = 1;
                }
                $this->smarty->assign('cat_recommend', $cat_recommend);
            }

            if (isset($cat_info['touch_icon']) && $cat_info['touch_icon']) {
                $cat_info['touch_icon'] = $this->dscRepository->getImagePath($cat_info['touch_icon']);
            }

            $this->smarty->assign('cat_info', $cat_info);
            $this->smarty->assign('form_act', 'update');
            $this->smarty->assign('goods_type_list', goods_type_list(0)); // 取得商品类型

            /* 显示页面 */

            return $this->smarty->display('category_store_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 商家分类分离平台，独立数据
        /*------------------------------------------------------ */
        elseif ($act == 'category_separate') {
            admin_priv('brand_manage');

            $this->smarty->assign('ur_here', __('admin::category_store.category_separate'));

            $cat_list = get_seller_category();
            $this->smarty->assign('record_count', count($cat_list));
            $this->smarty->assign('page', 1);

            write_static_cache('seller_cat_list', []);


            return $this->smarty->display('category_separate.dwt');
        }

        /*------------------------------------------------------ */
        //-- 商家分类分离平台，独立数据
        /*------------------------------------------------------ */
        elseif ($act == 'category_separate_initial') {
            $page = request()->get('page', 1);
            $page_size = request()->get('page_size', 1);

            $cat_list = get_seller_category();

            if ($cat_list) {
                $cat_list = get_array_sort($cat_list, 'level');
            }

            $cat_list = $this->dsc->page_array($page_size, $page, $cat_list);

            $result['list'] = isset($cat_list['list']) && $cat_list['list'] ? $cat_list['list'][0] : [];

            if ($result['list']) {
                if ($result['list']['level'] == 0) {
                    $parent_id = 0;
                } else {
                    $parent_id = $result['list']['parent_id'];
                }

                $other = [
                    'cat_name' => $result['list']['cat_name'],
                    'parent_id' => $parent_id,
                    'keywords' => $result['list']['keywords'],
                    'cat_desc' => $result['list']['cat_desc'],
                    'sort_order' => $result['list']['sort_order'],
                    'measure_unit' => $result['list']['measure_unit'],
                    'show_in_nav' => $result['list']['show_in_nav'],
                    'style' => $result['list']['style'],
                    'grade' => $result['list']['grade'],
                    'filter_attr' => $result['list']['filter_attr'],
                    'is_top_style' => $result['list']['is_top_style'],
                    'top_style_tpl' => $result['list']['top_style_tpl'],
                    'cat_icon' => $result['list']['cat_icon'],
                    'is_top_show' => $result['list']['is_top_show'],
                    'category_links' => $result['list']['category_links'],
                    'category_topic' => $result['list']['category_topic'],
                    'pinyin_keyword' => $result['list']['pinyin_keyword'],
                    'cat_alias_name' => $result['list']['cat_alias_name']
                ];

                MerchantsCategory::where('cat_id', $result['list']['cat_id'])->update($other);
                if ($result['list']['cat_id']) {
                    $new_arr = read_static_cache('seller_cat_list');
                    if ($new_arr === false) {
                        $new_arr = [$result['list']['cat_id']];
                    } else {
                        array_unshift($new_arr, ($result['list']['cat_id']));
                    }

                    write_static_cache('seller_cat_list', $new_arr);
                }
            }

            $result['page'] = $cat_list['filter']['page'] + 1;
            $result['page_size'] = $cat_list['filter']['page_size'];
            $result['record_count'] = $cat_list['filter']['record_count'];
            $result['page_count'] = $cat_list['filter']['page_count'];

            $result['is_stop'] = 1;
            if ($page > $cat_list['filter']['page_count']) {
                $result['is_stop'] = 0;

                ShopConfig::where('code', 'cat_belongs')->update(['value' => '1']);
                $cat = read_static_cache('seller_cat_list');
                if ($cat !== false) {
                    if ($cat) {
                        $cat = implode(',', $cat);

                        Goods::whereIn('cat_id', $cat)->update(['user_cat' => 'cat_id']);

                        Category::whereIn('cat_id', $cat)->delete();
                    }
                }

                clear_all_files();
            } else {
                $result['filter_page'] = $cat_list['filter']['page'];
            }

            return response()->json($result);
        }
        /*------------------------------------------------------ */
        //-- 新增商品分类信息
        /*------------------------------------------------------ */
        elseif ($act == 'add_category') {
            $parent_id = request()->get('parent_id', 0);
            $category = json_str_iconv(request()->get('cat', ''));

            if (cat_exists($category, $parent_id)) {
                return make_json_error(__('admin::category_store.catname_exist'));
            } else {
                $other = ['cat_name' => $category, 'parent_id' => $parent_id, 'is_show' => '1'];
                $category_id = MerchantsCategory::insertGetId($other);

                $arr = ["parent_id" => $parent_id, "id" => $category_id, "cat" => $category];

                clear_cache_files();    // 清除缓存

                return make_json_result($arr);
            }
        }

        /*------------------------------------------------------ */
        //-- 编辑商品分类信息
        /*------------------------------------------------------ */
        if ($act == 'update') {
            /* 权限检查 */
            admin_priv('cat_manage');

            /* 初始化变量 */
            $cat_id = request()->get('cat_id', 0);
            $user_id = request()->get('user_id', 0);

            //by guan start
            $cat['category_links'] = request()->get('category_links', '');
            //by guan end

            $old_cat_name = request()->get('old_cat_name', '');
            $cat['parent_id'] = request()->get('parent_id', '0_-1');
            //ecmoban模板堂 --zhuo start
            $parent_id = explode('_', $cat['parent_id']);
            if ($cat['parent_id'] > 0) {
                $cat['parent_id'] = intval($parent_id[0] ?? 0);
                $cat['level'] = intval($parent_id[1] ?? 0);
            } else {
                $cat['parent_id'] = 0;
                $cat['level'] = 0;
            }

            $link[0]['text'] = __('admin::common.go_back');

            if ($cat_id > 0) {
                $link[0]['href'] = 'category_store.php?act=edit&cat_id=' . $cat_id;
            } else {
                $link[0]['href'] = 'category_store.php?act=add';
            }

            $reject_cat = $this->categoryService->catList($cat_id, 1, 1, 'merchants_category');
            $reject_cat = arr_foreach($reject_cat);//获取当前分类相关分类数组

            if ($cat['parent_id'] == $cat_id || in_array($cat['parent_id'], $reject_cat)) {
                return sys_msg(__('admin::common.cat_prompt_notic_three'), 1, $link);
            }
            if ($cat['level'] < 2 && $adminru['ru_id'] > 0) {
                return sys_msg(__('admin::common.cat_prompt_notic_two'), 1, $link);
            }
            //ecmoban模板堂 --zhuo end
            //上传手机菜单图标 by kong start
            $touch_icon = request()->file('touch_icon');
            if ($touch_icon && $touch_icon->isValid()) {
                if ($touch_icon->getClientSize() > 200000) {
                    return sys_msg(__('admin::common.cat_prompt_file_size'), 1, $link);
                }

                $type = explode('.', $touch_icon->getClientOriginalName());
                $type = end($type);
                if ($type != 'jpg' && $type != 'png' && $type != 'gif' && $type != 'jpeg') {
                    return sys_msg(__('admin::common.cat_prompt_file_type'), 1, $link);
                }
                $touch_iconPrefix = time() . mt_rand(1001, 9999);
                //文件目录
                $touch_iconDir = storage_public(DATA_DIR . "/touch_icon");
                if (!file_exists($touch_iconDir)) {
                    mkdir($touch_iconDir);
                }
                //保存文件
                $touchimgName = $touch_iconDir . "/" . $touch_iconPrefix . '.' . $type;
                $touchsaveDir = DATA_DIR . "/touch_icon" . "/" . $touch_iconPrefix . '.' . $type;
                move_uploaded_file($touch_icon->getRealPath(), $touchimgName);
                $cat['touch_icon'] = $touchsaveDir;
                //删除文件
                if (!empty($cat_id)) {
                    $cat_info = Category::catInfo($cat_id)->first();
                    $cat_info = $cat_info ? $cat_info->toArray() : [];

                    @unlink(storage_public($cat_info['touch_icon']));
                }
            }
            $cat['sort_order'] = request()->get('sort_order', 0);
            $cat['keywords'] = request()->get('keywords', '');
            $cat['cat_desc'] = request()->get('cat_desc', '');
            $cat['measure_unit'] = request()->get('measure_unit', '');
            $cat['cat_name'] = request()->get('cat_name', '');
            $cat['is_show'] = request()->get('is_show', 0);
            $cat['is_top_show'] = request()->get('is_top_show', 0);
            $cat['is_top_style'] = request()->get('is_top_style', 0);
            $cat['show_in_nav'] = request()->get('show_in_nav', 0);
            $cat['grade'] = request()->get('grade', 0);
            $filter_attr = request()->get('filter_attr', []);
            $cat['filter_attr'] = !empty($filter_attr) ? implode(',', array_unique(array_diff($filter_attr, [0]))) : 0;
            $cat['cat_recommend'] = request()->get('cat_recommend', []);

            /* 判断分类名是否重复 */

            if ($cat['cat_name'] != $old_cat_name) {
                if (cat_exists($cat['cat_name'], $cat['parent_id'], $cat_id, $user_id)) {
                    $link[] = ['text' => __('admin::common.go_back'), 'href' => 'javascript:history.back(-1)'];
                    return sys_msg(__('admin::category_store.catname_exist'), 0, $link);
                }
            }

            /* 判断上级目录是否合法 */
            $children = $this->categoryService->getArrayKeysCat($cat_id);     // 获得当前分类的所有下级分类
            if (in_array($cat['parent_id'], $children)) {
                /* 选定的父类是当前分类或当前分类的下级分类 */
                $link[] = ['text' => __('admin::common.go_back'), 'href' => 'javascript:history.back(-1)'];
                return sys_msg(__('admin::category_store.is_leaf_error'), 0, $link);
            }

            if ($cat['grade'] > 10 || $cat['grade'] < 0) {
                /* 价格区间数超过范围 */
                $link[] = ['text' => __('admin::common.go_back'), 'href' => 'javascript:history.back(-1)'];
                return sys_msg(__('admin::category_store.grade_error'), 0, $link);
            }

            $dat = MerchantsCategory::where('cat_id', $cat_id)->first();
            //因为merchants_category表中没有level和cat_recommend字段所以注释掉
            $other = [
                'category_links' => $cat['category_links'],
                'parent_id' => $cat['parent_id'],
                'sort_order' => $cat['sort_order'],
                'keywords' => $cat['keywords'],
                'cat_desc' => $cat['cat_desc'],
                'measure_unit' => $cat['measure_unit'],
                'cat_name' => $cat['cat_name'],
                'is_show' => $cat['is_show'],
                'is_top_show' => $cat['is_top_show'],
                'is_top_style' => $cat['is_top_style'],
                'show_in_nav' => $cat['show_in_nav'],
                'style' => $cat['style'],
                'grade' => $cat['grade'],
                'filter_attr' => $cat['filter_attr']
            ];

            /* 手机小图标 */
            $touch_icon = $cat['touch_icon'] ?? '';
            if ($touch_icon) {
                $other['touch_icon'] = $touch_icon;
            }

            $update = MerchantsCategory::where('cat_id', $cat_id)->update($other);

            if ($update) {
                if ($cat['cat_name'] != $dat['cat_name']) {
                    //如果分类名称发生了改变
                    MerchantsNav::where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->update([
                        'name' => $cat['cat_name']
                    ]);
                }
                if ($cat['show_in_nav'] != $dat['show_in_nav']) {
                    //是否显示于导航栏发生了变化
                    if ($cat['show_in_nav'] == 1) {
                        //显示
                        $nid = MerchantsNav::where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->value('id');
                        $nid = $nid ? $nid : 0;
                        if (empty($nid)) {
                            //不存在
                            $vieworder = MerchantsNav::selectRaw('max(vieworder) max_vieworder')->where('type', '=', 'middle')->first();
                            $vieworder = $vieworder->max_vieworder ?? 0;
                            $vieworder += 2;
                            $uri = $this->dscRepository->buildUri('merchants_store', ['urid' => $user_id, 'cid' => $cat_id], $cat['cat_name']);

                            $insertData = [
                                'name' => $cat['cat_name'],
                                'ctype' => 'c',
                                'cid' => $cat_id,
                                'ifshow' => '1',
                                'vieworder' => $vieworder,
                                'opennew' => '0',
                                'url' => $uri,
                                'type' => 'middle',
                            ];
                            MerchantsNav::insert($insertData);
                        } else {
                            MerchantsNav::where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->update(['ifshow' => '1']);
                        }
                    } else {
                        //去除
                        MerchantsNav::where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->update(['ifshow' => '0']);
                    }
                }

                clear_cache_files(); // 清除缓存
                admin_log($cat['cat_name'], 'edit', 'merchants_category'); // 记录管理员操作
            }

            /* 提示信息 */
            $link[] = ['text' => __('admin::category_store.back_list'), 'href' => 'category_store.php?act=list'];
            return sys_msg(__('admin::category_store.catedit_succed'), 0, $link);
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

            $id = request()->get('id', 0);
            $val = json_str_iconv(request()->get('val', ''));

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

            $id = request()->get('id', 0);
            $val = request()->get('val', 0);

            if ($val > 10 || $val < 0) {
                /* 价格区间数超过范围 */
                return make_json_error(__('admin::category_store.grade_error'));
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

            $id = request()->get('id', 0);
            $val = request()->get('val', 0);

            if ($this->cat_update($id, ['show_in_nav' => $val]) != false) {
                if ($val == 1) {
                    //显示
                    $vieworder = MerchantsNav::selectRaw('max(vieworder) max_vieworder')->where('type', '=', 'middle')->first();
                    $vieworder = $vieworder->max_vieworder ?? 0;
                    $vieworder += 2;
                    $catname = MerchantsCategory::where('cat_id', $id)->value('cat_name');
                    $catname = $catname ? $catname : '';
                    //显示在自定义导航栏中
                    $GLOBALS['_CFG']['rewrite'] = 0;
                    $uri = $this->dscRepository->buildUri('category', ['cid' => $id], $catname);

                    $nid = MerchantsNav::where('ctype', 'c')->where('cid', $id)->where('type', 'middle')->value('id');
                    $nid = $nid ? $nid : 0;
                    if (empty($nid)) {
                        //不存在
                        $insertData = [
                            'name' => $catname,
                            'ctype' => 'c',
                            'cid' => $id,
                            'ifshow' => '1',
                            'vieworder' => $vieworder,
                            'opennew' => '0',
                            'url' => $uri,
                            'type' => 'middle',
                        ];
                        MerchantsNav::insert($insertData);
                    } else {
                        MerchantsNav::where('ctype', 'c')->where('cid', $id)->where('type', 'middle')->update(['ifshow' => '1']);
                    }
                } else {
                    //去除
                    MerchantsNav::where('ctype', 'c')->where('cid', $id)->where('type', 'middle')->update(['ifshow' => '0']);
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

            $id = request()->get('id', 0);
            $val = request()->get('val', 0);

            $children = $this->categoryService->getMerchantsCatListChildren($id);

            //隐藏分类下所有商品
            if ($children) {
                Goods::whereIn('user_cat', $children)
                    ->update([
                        'is_show' => $val
                    ]);
            }

            if ($this->cat_update($id, ['is_show' => $val]) != false) {
                clear_cache_files();
                return make_json_result($val);
            } else {
                return make_json_error($this->db->error());
            }
        }

        /*------------------------------------------------------ */
        //-- 删除分类 ajax实现删除分类后页面不刷新 //ecmoban模板堂 --kong
        /*------------------------------------------------------ */
        elseif ($act == 'remove') {
            $check_auth = check_authz_json('cat_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $result = ['error' => 0, 'massege' => '', 'level' => ''];
            /* 初始化分类ID并取得分类名称 */
            $result['level'] = request()->get('level', 0);
            $cat_id = request()->get('cat_id', 0);
            $result['cat_id'] = $cat_id;
            $row = MerchantsCategory::where('cat_id', $cat_id);
            $row = BaseRepository::getToArrayFirst($row);

            $cat_name = $row['cat_name'] ?? '';

            /* 当前分类下是否有子分类 */
            $cat_count = MerchantsCategory::where('parent_id', $cat_id)->count();

            /* 当前分类下是否存在商品 */
            $goods_count = Goods::where('user_cat', $cat_id)->count();

            /* 如果不存在下级子分类和商品，则删除之 */
            if ($cat_count == 0 && $goods_count == 0) {
                /* 删除分类 */
                $res = MerchantsCategory::where('cat_id', $cat_id)->delete();
                if ($res) {
                    dsc_unlink(storage_public($row['touch_icon']));

                    MerchantsNav::where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->delete();
                    clear_cache_files();
                    admin_log($cat_name, 'remove', 'category');
                    $result['error'] = 1;
                }
            } else {
                $result['error'] = 2;
                $result['massege'] = $cat_name . ' ' . __('admin::category_store.cat_isleaf');
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
            $url = 'category_store.php?act=titleFileView&cat_id=' . $cat_id;

            return dsc_header("Location: $url\n");
        }

        /*------------------------------------------------------ */
        //-- 删除分类 ajax实现删除分类后页面不刷新 //ecmoban模板堂 --kong
        /*------------------------------------------------------ */
        elseif ($act == 'remove_cat') {
            $check_auth = check_authz_json('cat_manage');
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

            /* 如果不存在下级子分类和商品，则删除之 */
            if ($cat_count == 0 && $goods_count == 0) {
                /* 删除分类 */
                $res = Category::where('cat_id', $cat_id)->delete();
                if ($res) {
                    Nav::where('ctype', 'c')->where('cid', $cat_id)->where('type', 'middle')->delete();
                    clear_cache_files();
                    admin_log($cat_name, 'remove', 'category');
                    $result['error'] = 1;
                }
                MerchantsDocumenttitle::where('cat_id', $cat_id)->delete();
            } else {
                $result['error'] = 2;
                $result['massege'] = $cat_name . ' ' . __('admin::category_store.cat_isleaf');
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

        return $this->db->autoExecute($this->dsc->table('merchants_category'), $args, 'update', "cat_id='$cat_id'");
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
        $list = [];
        $arr = Attribute::with(['goodsType' => function ($query) {
            $query->where('enabled', '1');
        }])->orderBy('cat_id')->orderBy('sort_order');
        $arr = BaseRepository::getToArrayGet($arr);
        foreach ($arr as $val) {
            $list[$val['cat_id']][] = [$val['attr_id'] => $val['attr_name']];
        }
        return $list;
    }
}
