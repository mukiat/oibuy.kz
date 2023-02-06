<?php

namespace App\Modules\Seller\Controllers;

use App\Models\KeywordList;
use App\Models\MerchantsCategory;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Goods\GoodsManageService;


/**
 * 商品关键词管理
 * Class GoodsKeywordController
 * @package App\Modules\Seller\Controllers
 */
class GoodsKeywordController extends InitController
{
    public $goodsManageService;

    public function __construct(
        GoodsManageService $goodsManageService
    )
    {
        $this->goodsManageService = $goodsManageService;
    }

    public function index()
    {
        $act = e(request()->input('act', 'list'));

        $menus = session('menus', '');
        $this->smarty->assign('menus', $menus);

        $keyword_lang = lang('admin/goods_keyword');
        $lang = BaseRepository::getArrayCollapse([$GLOBALS['_LANG'], $keyword_lang]);
        $lang['parent_id'] = __("admin::category.parent_id");
        $lang['cat_top'] = __("admin::category.cat_top");
        $lang['select_please'] = __("admin::common.select_please");

        $this->smarty->assign('lang', $lang);

        $adminru = get_admin_ru_id();

        $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => '22_goods_keyword']);
        $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_cat_and_goods']);

        /* ------------------------------------------------------ */
        //-- 关键词列表
        /* ------------------------------------------------------ */
        if ($act == 'list') {

            /* 权限判断 */
            admin_priv('goods_keyword');

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['22_goods_keyword']);
            $this->smarty->assign('action_link', ['text' => $lang['01_goods_keyword_add'], 'href' => 'goods_keyword.php?act=add']);
            $this->smarty->assign('full_page', 1);

            $keywordList = $this->goodsManageService->getGoodsKeywordList($adminru['ru_id']);

            $this->smarty->assign('keyword_list', $keywordList['keyword']);
            $this->smarty->assign('filter', $keywordList['filter']);
            $this->smarty->assign('record_count', $keywordList['record_count']);
            $this->smarty->assign('page_count', $keywordList['page_count']);

            //分页
            $page = isset($_REQUEST['page']) && !empty(intval($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;
            $page_count_arr = seller_page($keywordList, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);

            return $this->smarty->display('goods_keyword_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 排序、分页、查询
        /*------------------------------------------------------ */
        elseif ($act == 'query') {

            $keywordList = $this->goodsManageService->getGoodsKeywordList();

            $this->smarty->assign('keyword_list', $keywordList['keyword']);
            $this->smarty->assign('filter', $keywordList['filter']);
            $this->smarty->assign('record_count', $keywordList['record_count']);
            $this->smarty->assign('page_count', $keywordList['page_count']);

            //分页
            $page = isset($_REQUEST['page']) && !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
            $page_count_arr = seller_page($keywordList, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);

            return make_json_result(
                $this->smarty->fetch('goods_keyword_list.dwt'),
                '',
                ['filter' => $keywordList['filter'], 'page_count' => $keywordList['page_count']]
            );
        }

        /* ------------------------------------------------------ */
        //-- 添加关键词
        /* ------------------------------------------------------ */
        elseif ($act == 'add') {

            /* 权限判断 */
            admin_priv('goods_keyword');

            $this->smarty->assign('ur_here', $lang['01_goods_keyword_add']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['22_goods_keyword'], 'href' => 'goods_keyword.php?act=list']);
            $this->smarty->assign('form_action', 'insert');

            set_seller_default_filter(0, 0, $adminru['ru_id']); //by wu

            $this->smarty->assign('keyword', ['sort_order' => 50, 'is_show' => 1]);

            return $this->smarty->display('goods_keyword_info.dwt');
        }

        /* ------------------------------------------------------ */
        //-- 编辑关键词
        /* ------------------------------------------------------ */
        elseif ($act == 'edit') {

            /* 权限判断 */
            admin_priv('goods_keyword');

            $this->smarty->assign('ur_here', $lang['02_goods_keyword_edit']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['22_goods_keyword'], 'href' => 'goods_keyword.php?act=list']);
            $this->smarty->assign('form_action', 'update');

            $id = (int)request()->get('id', 0);

            $keyword = KeywordList::where('id', $id);
            $keyword = BaseRepository::getToArrayFirst($keyword);

            $this->smarty->assign('keyword', $keyword);

            $cat_id = $keyword['cat_id'] ?? 0;

            $this->smarty->assign('parent_id', $cat_id);
            $cat_name = MerchantsCategory::where('cat_id', $cat_id)->value('cat_name');
            $cat_name = $cat_name ? $cat_name : '';
            $this->smarty->assign('parent_category', $cat_name); //上级分类导航
            set_seller_default_filter(0, $cat_id, $adminru['ru_id']); //by wu

            return $this->smarty->display('goods_keyword_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 插入关键词
        /*------------------------------------------------------ */
        elseif ($act == 'insert') {

            /* 权限判断 */
            admin_priv('goods_keyword');

            $keyword_name = isset($_POST['keyword_name']) && !empty($_POST['keyword_name']) ? addslashes($_POST['keyword_name']) : '';
            $keywordList = BaseRepository::getExplode($keyword_name, '，');
            $cat_id = (int)request()->get('parent_id', 0);

            if ($keywordList) {

                $keywordList = BaseRepository::getArrayUnique($keywordList);

                $list = KeywordList::select('name')->whereIn('name', $keywordList)
                    ->where('ru_id', $adminru['ru_id']);
                $list = BaseRepository::getToArrayGet($list);
                $list = BaseRepository::getFlatten($list);

                $keywordList = BaseRepository::getArrayDiff($keywordList, $list);

                if ($keywordList) {
                    $arr = [];
                    foreach ($keywordList as $key => $val) {
                        $arr[$key]['name'] = trim($val);
                        $arr[$key]['add_time'] = TimeRepository::getGmTime();
                        $arr[$key]['cat_id'] = $cat_id;
                        $arr[$key]['ru_id'] = $adminru['ru_id'];
                    }
                    KeywordList::insert($arr);
                }
            }

            $link[0]['text'] = $lang['01_goods_keyword_add'];
            $link[0]['href'] = 'goods_keyword.php?act=add';

            $link[1]['text'] = $GLOBALS['_LANG']['back_list'];
            $link[1]['href'] = 'goods_keyword.php?act=list';

            return sys_msg($lang['keyword_add_succed'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 更新关键词
        /*------------------------------------------------------ */
        elseif ($act == 'update') {

            /* 权限判断 */
            admin_priv('goods_keyword');

            $id = (int)request()->get('id', 0);
            $cat_id = (int)request()->get('parent_id', 0);
            $keyword_name = isset($_POST['keyword_name']) && !empty($_POST['keyword_name']) ? addslashes($_POST['keyword_name']) : '';
            $keyword_name = trim($keyword_name);

            $link[0]['text'] = $lang['02_goods_keyword_edit'];
            $link[0]['href'] = 'goods_keyword.php?act=edit&id=' . $id;

            $link[1]['text'] = $GLOBALS['_LANG']['back_list'];
            $link[1]['href'] = 'goods_keyword.php?act=list';

            $is_only = KeywordList::where('id', '<>', $id)
                ->where('cat_id', $cat_id)
                ->where('name', $keyword_name)
                ->where('ru_id', $adminru['ru_id'])
                ->count('id');

            if ($is_only > 0) {
                return sys_msg($lang['keyword_edit_failed'], 0, $link);
            }

            $other = [
                'name' => $keyword_name,
                'update_time' => TimeRepository::getGmTime(),
                'cat_id' => $cat_id
            ];
            KeywordList::where('id', $id)->update($other);

            return sys_msg($lang['keyword_edit_succed'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 删除关键词
        /*------------------------------------------------------ */
        elseif ($act == 'remove') {
            $check_auth = check_authz_json('goods_keyword');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_GET['id']);

            KeywordList::where('id', $id)->delete();

            $url = 'goods_keyword.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));
            return dsc_header("Location: $url\n");
        }

        /*------------------------------------------------------ */
        //-- 批量删除
        /*------------------------------------------------------ */
        if ($act == 'batch') {
            admin_priv('goods_keyword');

            $checkboxes = request()->get('checkboxes', []);
            $type = request()->get('type', '');
            if (empty($checkboxes) || !is_array($checkboxes)) {
                return sys_msg(__('admin::discuss_circle.not_select_date'), 1);
            }

            $checkboxes = BaseRepository::getExplode($checkboxes);

            // 删除
            if ($type && $type == 'batch_remove') {
                KeywordList::whereIn('id', $checkboxes)->delete();

                admin_log('', $type, 'goods_keyword');

                $href = "goods_keyword.php?act=list";
                $back_list = $GLOBALS['_LANG']['back_list'];

                $link[] = ['text' => $back_list, 'href' => $href];
                return sys_msg(sprintf($lang['batch_drop_success'], count($checkboxes)), 0, $link);
            }
        }
    }
}
