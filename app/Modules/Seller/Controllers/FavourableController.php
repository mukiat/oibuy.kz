<?php

namespace App\Modules\Seller\Controllers;

use App\Libraries\Exchange;
use App\Libraries\Image;
use App\Models\Category;
use App\Models\Goods;
use App\Models\Brand;
use App\Models\FavourableActivity;
use App\Models\UserRank;
use App\Models\MerchantsCategory;
use App\Models\MerchantsShopInformation;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Category\CategoryService;
use App\Services\Favourable\FavourableManageService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Store\StoreCommonService;

/**
 * 管理中心优惠活动管理
 */
class FavourableController extends InitController
{
    protected $categoryService;
    protected $merchantCommonService;
    protected $dscRepository;
    protected $storeCommonService;
    protected $favourableManageService;

    public function __construct(
        CategoryService $categoryService,
        MerchantCommonService $merchantCommonService,
        DscRepository $dscRepository,
        StoreCommonService $storeCommonService,
        FavourableManageService $favourableManageService
    )
    {
        $this->categoryService = $categoryService;
        $this->merchantCommonService = $merchantCommonService;
        $this->dscRepository = $dscRepository;
        $this->storeCommonService = $storeCommonService;
        $this->favourableManageService = $favourableManageService;
    }

    public function index()
    {
        load_helper('goods');

        $image = new Image(['bgcolor' => $GLOBALS['_CFG']['bgcolor']]);
        $menus = session('menus', '');
        $this->smarty->assign('menus', $menus);
        $this->smarty->assign('action_type', "bonus");
        $exc = new Exchange($this->dsc->table('favourable_activity'), $this->db, 'act_id', 'act_name');

        $adminru = get_admin_ru_id();

        //ecmoban模板堂 --zhuo start
        if ($adminru['ru_id'] == 0) {
            $this->smarty->assign('priv_ru', 1);
        } else {
            $this->smarty->assign('priv_ru', 0);
        }
        //ecmoban模板堂 --zhuo end

        $this->smarty->assign('controller', basename(PHP_SELF, '.php'));

        $this->smarty->assign('menu_select', ['action' => '02_promotion', 'current' => '12_favourable']);

        /*------------------------------------------------------ */
        //-- 活动列表页
        /*------------------------------------------------------ */

        if ($_REQUEST['act'] == 'list') {
            admin_priv('favourable');

            /* 模板赋值 */

            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_promotion']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['favourable_list']);
            $this->smarty->assign('action_link', ['href' => 'favourable.php?act=add', 'text' => $GLOBALS['_LANG']['add_favourable'], 'class' => 'icon-plus']);

            $list = $this->favourable_list($adminru['ru_id']);

            //分页
            $page = isset($_REQUEST['page']) && !empty(intval($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;
            $page_count_arr = seller_page($list, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);

            $this->smarty->assign('favourable_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            /* 显示商品列表页面 */

            return $this->smarty->display('favourable_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 分页、排序、查询
        /*------------------------------------------------------ */

        elseif ($_REQUEST['act'] == 'query') {
            $page = isset($_REQUEST['page']) && !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

            $list = $this->favourable_list($adminru['ru_id']);

            //分页
            $page_count_arr = seller_page($list, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);

            $this->smarty->assign('favourable_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result(
                $this->smarty->fetch('favourable_list.dwt'),
                '',
                ['filter' => $list['filter'], 'page_count' => $list['page_count']]
            );
        }

        /*------------------------------------------------------ */
        //-- 删除
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'remove') {
            $check_auth = check_authz_json('favourable');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_GET['id']);
            $favourable = favourable_info($id, 'seller');

            if ($favourable['user_id'] != $adminru['ru_id'] && $favourable['userFav_type'] == 0) {
                $url = 'favourable.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));
                return dsc_header("Location: $url\n");
            }

            if (empty($favourable)) {
                return make_json_error($GLOBALS['_LANG']['favourable_not_exist']);
            }
            $name = $favourable['act_name'];
            $this->dscRepository->getDelBatch('', $id, ['activity_thumb'], 'act_id', FavourableActivity::whereRaw(1), 1); //删除图片

            FavourableActivity::where('act_id', $id)->where('userFav_type', 0)->where('user_id', $adminru['ru_id'])->delete();

            /* 更新购物车 */
            $this->favourableManageService->updateCart($id);

            /* 记日志 */
            admin_log($name, 'remove', 'favourable');

            /* 清除缓存 */
            clear_cache_files();

            $url = 'favourable.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));

            return dsc_header("Location: $url\n");
        }

        /*------------------------------------------------------ */
        //-- 批量操作
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'batch') {
            /* 取得要操作的记录编号 */
            if (empty($_POST['checkboxes'])) {
                return sys_msg($GLOBALS['_LANG']['no_record_selected']);
            } else {
                /* 检查权限 */
                admin_priv('favourable');

                $ids = $_POST['checkboxes'];

                if (isset($_POST['drop'])) {
                    $this->dscRepository->getDelBatch($ids, '', ['activity_thumb'], 'act_id', FavourableActivity::whereRaw(1), 1);

                    /* 删除记录 */
                    $ids = BaseRepository::getExplode($ids);
                    if ($ids) {
                        FavourableActivity::whereIn('act_id', $ids)->where('userFav_type', 0)->where('user_id', $adminru['ru_id'])->delete();
                    }

                    /* 更新购物车 */
                    $this->favourableManageService->updateCart($ids);

                    /* 记日志 */
                    admin_log('', 'batch_remove', 'favourable');

                    /* 清除缓存 */
                    clear_cache_files();

                    $links[] = ['text' => $GLOBALS['_LANG']['back_favourable_list'], 'href' => 'favourable.php?act=list&' . list_link_postfix()];
                    return sys_msg($GLOBALS['_LANG']['batch_drop_ok'], 0, $links);
                }
            }
        }

        /*------------------------------------------------------ */
        //-- 修改排序
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'edit_sort_order') {
            $check_auth = check_authz_json('favourable');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_POST['id']);
            $val = intval($_POST['val']);

            FavourableActivity::where('act_id', $id)->update(['sort_order' => $val]);

            return make_json_result($val);
        }

        /*------------------------------------------------------ */
        //-- 添加、编辑
        /*------------------------------------------------------ */

        elseif ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit') {
            /* 检查权限 */
            admin_priv('favourable');
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_promotion']);

            /* 是否添加 */
            $is_add = $_REQUEST['act'] == 'add';
            $this->smarty->assign('form_action', $is_add ? 'insert' : 'update');

            /* 初始化、取得优惠活动信息 */
            if ($is_add) {

                $time = TimeRepository::getGmTime();

                $ru_id = $adminru['ru_id']; //ecmoban模板堂 --zhuo
                $favourable = [
                    'act_id' => 0,
                    'act_name' => '',
                    'start_time' => date('Y-m-d H:i:s', $time + 86400),
                    'end_time' => date('Y-m-d H:i:s', $time + 4 * 86400),
                    'user_rank' => '',
                    'act_range' => FAR_ALL,
                    'act_range_ext' => '',
                    'min_amount' => 0,
                    'max_amount' => 0,
                    'act_type' => FAT_GOODS,
                    'act_type_ext' => 0,
                    'user_id' => $ru_id, //ecmoban模板堂 --zhuo
                    'gift' => []
                ];
            } else {
                if (empty($_GET['id'])) {
                    return sys_msg('invalid param');
                }
                $id = intval($_GET['id']);
                $favourable = favourable_info($id, 'seller');
                if (empty($favourable)) {
                    return sys_msg($GLOBALS['_LANG']['favourable_not_exist']);
                }

                if ($favourable['user_id'] != $adminru['ru_id'] && $favourable['userFav_type'] == 0) {
                    $Loaction = "favourable.php?act=list";
                    return dsc_header("Location: $Loaction\n");
                }
            }

            $this->smarty->assign('favourable', $favourable);

            /* 取得用户等级 */
            $user_rank_list = [];
            $res = UserRank::select('rank_id', 'rank_name');
            $res = BaseRepository::getToArrayGet($res);
            foreach ($res as $row) {
                $row['checked'] = strpos(',' . $favourable['user_rank'] . ',', ',' . $row['rank_id'] . ',') !== false;
                $user_rank_list[] = $row;
            }
            $this->smarty->assign('user_rank_list', $user_rank_list);

            /* 取得优惠范围 */
            $act_range_ext = [];
            $favourable['act_range_ext'] = BaseRepository::getExplode($favourable['act_range_ext']);
            if ($favourable['act_range'] != FAR_ALL && !empty($favourable['act_range_ext'])) {
                if ($favourable['act_range'] == FAR_CATEGORY) {
                    $act_range_ext = Category::select('cat_id AS id', 'cat_name AS name')->whereIn('cat_id', $favourable['act_range_ext']);
                    $act_range_ext = BaseRepository::getToArrayGet($act_range_ext);
                } elseif ($favourable['act_range'] == FAR_BRAND) {
                    $act_range_ext = Brand::select('brand_id AS id', 'brand_name AS name')->whereIn('brand_id', $favourable['act_range_ext']);
                    $act_range_ext = BaseRepository::getToArrayGet($act_range_ext);
                } else {
                    $act_range_ext = Goods::select('goods_id AS id', 'goods_name AS name')
                        ->where('user_id', $adminru['ru_id'])
                        ->whereIn('goods_id', $favourable['act_range_ext']);
                    $act_range_ext = BaseRepository::getToArrayGet($act_range_ext);
                }
            }

            $this->smarty->assign('act_range_ext', $act_range_ext);

            /* 赋值时间控件的语言 */
            $this->smarty->assign('cfg_lang', $GLOBALS['_CFG']['lang']);

            /* 显示模板 */
            if ($is_add) {
                $this->smarty->assign('ur_here', $GLOBALS['_LANG']['add_favourable']);
            } else {
                $this->smarty->assign('ur_here', $GLOBALS['_LANG']['edit_favourable']);
            }
            $href = 'favourable.php?act=list';
            if (!$is_add) {
                $href .= '&' . list_link_postfix();
            }
            $this->smarty->assign('action_link', ['href' => $href, 'text' => $GLOBALS['_LANG']['favourable_list'], 'class' => 'icon-reply']);

            return $this->smarty->display('favourable_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 添加、编辑后提交
        /*------------------------------------------------------ */

        elseif ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update') {
            /* 检查权限 */
            admin_priv('favourable');

            $ru_id = isset($_REQUEST['ru_id']) ? intval($_REQUEST['ru_id']) : 0;
            $userFav_type = isset($_POST['userFav_type']) ? intval($_POST['userFav_type']) : 0;

            /* 是否添加 */
            $is_add = $_REQUEST['act'] == 'insert';

            // 验证商品是否只参与一个活动 by qin
            $act_name = isset($_POST['act_name']) ? addslashes($_POST['act_name']) : '';

            $act_range_ext = isset($_POST['act_range_ext']) && !empty($_POST['act_range_ext']) ? implode(",", $_POST['act_range_ext']) : '';

            /* 检查名称是否重复 */
            $act_name = $this->dscRepository->subStr($act_name, 255, false);
            $act_id = intval($_POST['id']);
            $is_only = FavourableActivity::where('act_name', $act_name)
                ->where('act_id', '<>', $act_id)
                ->where('user_id', $adminru['ru_id'])
                ->count();
            if ($is_only > 0) {
                return sys_msg($GLOBALS['_LANG']['act_name_exists']);
            }

            /* 检查享受优惠的会员等级 */
            if (!isset($_POST['user_rank'])) {
                return sys_msg($GLOBALS['_LANG']['pls_set_user_rank']);
            }

            /* 检查优惠范围扩展信息 */
            if (intval($_POST['act_range']) > 0 && !isset($_POST['act_range_ext'])) {
                return sys_msg($GLOBALS['_LANG']['pls_set_act_range']);
            }

            /* 检查金额上下限 */
            $min_amount = floatval($_POST['min_amount']) >= 0 ? floatval($_POST['min_amount']) : 0;
            $max_amount = floatval($_POST['max_amount']) >= 0 ? floatval($_POST['max_amount']) : 0;
            if ($max_amount > 0 && $min_amount > $max_amount) {
                return sys_msg($GLOBALS['_LANG']['amount_error']);
            }

            /* 取得赠品 */
            $gift = [];
            if (intval($_POST['act_type']) == FAT_GOODS && isset($_POST['gift_id'])) {
                foreach ($_POST['gift_id'] as $key => $id) {
                    $gift[] = ['id' => $id, 'name' => $_POST['gift_name'][$key], 'price' => $_POST['gift_price'][$key]];
                }
            }

            /* 提交值 */
            $favourable = [
                'act_id' => intval($_POST['id']),
                'act_name' => $act_name,
                'start_time' => local_strtotime($_POST['start_time']),
                'end_time' => local_strtotime($_POST['end_time']),
                'user_rank' => isset($_POST['user_rank']) ? join(',', $_POST['user_rank']) : '0',
                'act_range' => intval($_POST['act_range']),
                'act_range_ext' => $act_range_ext,
                'min_amount' => floatval($_POST['min_amount']),
                'max_amount' => floatval($_POST['max_amount']),
                'act_type' => intval($_POST['act_type']),
                'act_type_ext' => floatval($_POST['act_type_ext']),
                'gift' => serialize($gift),
                'userFav_type' => $userFav_type
            ];
            if ($favourable['act_type'] == FAT_GOODS) {
                $favourable['act_type_ext'] = round($favourable['act_type_ext']);
            }

            $activity_thumb = '';
            if (isset($_FILES['activity_thumb'])) {
                $activity_thumb = $image->upload_image($_FILES['activity_thumb'], 'activity_thumb');  //图片存放地址
            }

            $this->dscRepository->getOssAddFile([$activity_thumb]);

            /* 保存数据 */
            if ($is_add) {
                //ecmoban模板堂 -- zhuo
                $favourable['user_id'] = $adminru['ru_id'];
                $favourable['activity_thumb'] = $activity_thumb;
                $favourable['act_id'] = FavourableActivity::insertGetId($favourable);
            } else {
                $favourable['review_status'] = 1;

                if (!empty($activity_thumb)) {
                    $favourable['activity_thumb'] = $activity_thumb;
                }
                FavourableActivity::where('act_id', $favourable['act_id'])->update($favourable);
            }

            /* 记日志 */
            if ($is_add) {
                admin_log($favourable['act_name'], 'add', 'favourable');
            } else {
                admin_log($favourable['act_name'], 'edit', 'favourable');
            }

            /* 清除缓存 */
            clear_cache_files();

            /* 提示信息 */
            if ($is_add) {
                $links = [
                    ['href' => 'favourable.php?act=add', 'text' => $GLOBALS['_LANG']['continue_add_favourable']],
                    ['href' => 'favourable.php?act=list', 'text' => $GLOBALS['_LANG']['back_favourable_list']]
                ];
                return sys_msg($GLOBALS['_LANG']['add_favourable_ok'], 0, $links);
            } else {
                $links = [
                    ['href' => 'favourable.php?act=edit&id=' . $favourable['act_id'] . "&ru_id=" . $ru_id, 'text' => $GLOBALS['_LANG']['edit_favourable']],
                    ['href' => 'favourable.php?act=list&' . list_link_postfix(), 'text' => $GLOBALS['_LANG']['back_favourable_list']]
                ];
                return sys_msg($GLOBALS['_LANG']['edit_favourable_ok'], 0, $links);
            }
        }

        /*------------------------------------------------------ */
        //-- 删除活动图片
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'drop_thumb') {
            /* 权限判断 */
            admin_priv('brand_manage');
            $act_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $ru_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;

            $this->dscRepository->getDelBatch('', $act_id, ['activity_thumb'], 'act_id', FavourableActivity::whereRaw(1), 1); //删除图片
            FavourableActivity::where('act_id', $act_id)->update(['activity_thumb' => '']);

            $link = [['text' => $GLOBALS['_LANG']['edit_favourable'], 'href' => 'favourable.php?act=edit&id=' . $act_id . "&ru_id=" . $ru_id], ['text' => $GLOBALS['_LANG']['favourable_list'], 'href' => 'favourable.php?act=list']];
            return sys_msg($GLOBALS['_LANG']['drop_activity_thumb_success'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 搜索商品
        /*------------------------------------------------------ */

        elseif ($_REQUEST['act'] == 'search') {
            /* 检查权限 */
            $check_auth = check_authz_json('favourable');
            if ($check_auth !== true) {
                return $check_auth;
            }


            $filter = dsc_decode($_GET['JSON']);
            $filter->keyword = json_str_iconv($filter->keyword);
            $ru_id = $filter->ru_id; //ecmoban模板堂 --zhuo

            $where = '';
            if ($ru_id == 0) {
                $where .= " LIMIT 50";
            }
            if ($filter->act_range == FAR_ALL) {
                $arr[0] = [
                    'id' => 0,
                    'name' => $GLOBALS['_LANG']['js_languages']['all_need_not_search']
                ];
            } elseif ($filter->act_range == FAR_CATEGORY) {
                $arr = $this->get_user_cat_list($ru_id);
                $arr = $this->get_user_cat_search($ru_id, $filter->keyword, $arr);
                $arr = array_values($arr);
            } elseif ($filter->act_range == FAR_BRAND) {
                $arr = Brand::select('brand_id AS id', 'brand_name AS name')->where('brand_name', 'like', '%' . mysql_like_quote($filter->keyword) . '%');

                if ($ru_id == 0) {
                    $arr = $arr->limit(50);
                }

                $arr = BaseRepository::getToArrayGet($arr);

                if ($arr) {
                    foreach ($arr as $key => $row) {
                        if ($ru_id) {
                            $arr[$key]['is_brand'] = get_seller_brand_count($row['id'], $ru_id);
                        } else {
                            $arr[$key]['is_brand'] = 1;
                        }

                        if (!($arr[$key]['is_brand'] > 0)) {
                            unset($arr[$key]);
                        }
                    }

                    $arr = array_values($arr);
                }
            } else {
                $arr = Goods::select('goods_id AS id', 'goods_name AS name')
                    ->where('user_id', $ru_id)
                    ->where('is_real', 1)
                    ->where('is_on_sale', 1)
                    ->where('is_delete', 0);

                if (config('shop.review_goods') == 1) {
                    $arr = $arr->whereIn('review_status', [3, 4, 5]);
                }

                $arr = $arr->where(function ($query) use ($filter) {
                    $query->where('goods_name', 'like', '%' . mysql_like_quote($filter->keyword) . '%')
                        ->orWhere('goods_sn', 'like', '%' . mysql_like_quote($filter->keyword) . '%');
                });

                $arr = $arr->limit(50);
                $arr = BaseRepository::getToArrayGet($arr);
            }
            if (empty($arr)) {
                $arr = [0 => [
                    'id' => 0,
                    'name' => $GLOBALS['_LANG']['search_result_empty']
                ]];
            }

            return make_json_result($arr);
        }
    }

    /*
     * 取得优惠活动列表
     * @return   array
     */
    private function favourable_list($ru_id = 0)
    {
        $time = TimeRepository::getGmTime();

        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'favourable_list';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 过滤条件 */
        $filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
        }
        $filter['is_going'] = empty($_REQUEST['is_going']) ? 0 : 1;
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'act_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['use_type'] = empty($_REQUEST['use_type']) ? 0 : intval($_REQUEST['use_type']);
        $filter['fav_dateout'] = empty($_REQUEST['fav_dateout']) ? 0 : intval($_REQUEST['fav_dateout']);

        $filter['review_status'] = empty($_REQUEST['review_status']) ? 0 : intval($_REQUEST['review_status']);

        $res = FavourableActivity::query();

        if ($filter['use_type'] == 1) { //自营
            $res = $res->where('user_id', 0)->where('userFav_type', 0);
        } elseif ($filter['use_type'] == 2) { //商家
            $res = $res->where('userFav_type', 0);
            $res = CommonRepository::constantMaxId($res, 'user_id');
        } elseif ($filter['use_type'] == 3) { //全场
            $res = $res->where('userFav_type', 1);
        } elseif ($filter['use_type'] == 4) { //商家自主使用
            $res = $res->where('user_id', $ru_id)->where('userFav_type', 0);
        } else {
            if ($ru_id > 0) {
                $res = $res->where(function ($query) use ($ru_id) {
                    $query->where('user_id', $ru_id)->orWhere('userFav_type', 1);
                });
            }
        }

        if ($filter['review_status']) {
            $res = $res->where('review_status', $filter['review_status']);
        }

        if ($filter['fav_dateout'] > 0) {
            $firstSecToday = 24 * 60 * 60 * 2;
            $res = $res->whereRaw("(end_time - '$time') < '$firstSecToday'")->whereRaw("(end_time - '$time') > 0");
        }

        //管理员查询的权限 -- 店铺查询 start
        $filter['store_search'] = empty($_REQUEST['store_search']) ? 0 : intval($_REQUEST['store_search']);
        $filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
        $filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';

        if ($filter['store_search'] != 0) {
            if ($ru_id == 0) {
                $store_type = (int)request()->input('store_type', 0);

                $filter['store_type'] = $store_type;

                if ($filter['store_search'] == 1) {
                    $res = $res->where('user_id', $filter['merchant_id']);
                }

                if ($filter['store_search'] > 1) {
                    $res = $res->whereHasIn('getMerchantsShopInformation', function ($query) use ($filter) {
                        if ($filter['store_search'] == 2) {
                            $query->where('rz_shop_name', 'like', '%' . mysql_like_quote($filter['store_keyword']) . '%');
                        } elseif ($filter['store_search'] == 3) {
                            $query = $query->where('shoprz_brand_name', 'like', '%' . mysql_like_quote($filter['store_keyword']) . '%');

                            if ($filter['store_type']) {
                                $query->where('shop_name_suffix', $filter['store_type']);
                            }
                        }
                    });
                }
            }
        }
        //管理员查询的权限 -- 店铺查询 end

        if (!empty($filter['keyword'])) {
            $res = $res->where('act_name', 'like', '%' . mysql_like_quote($filter['keyword']) . '%');
        }

        if ($filter['is_going']) {
            $res = $res->where('start_time', '<=', $time)->where('end_time', '>=', $time);
        }

        $filter['record_count'] = $res->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $filter['keyword'] = stripslashes($filter['keyword']);

        /* 查询 */
        $res = $res->orderBy($filter['sort_by'], $filter['sort_order'])
            ->offset($filter['start'])
            ->limit($filter['page_size']);

        $res = BaseRepository::getToArrayGet($res);

        $list = [];
        foreach ($res as $row) {
            $row['start_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $row['start_time']);
            $row['end_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $row['end_time']);
            $row['user_name'] = $this->merchantCommonService->getShopName($row['user_id'], 1); //ecmoban模板堂 --zhuo

            $list[] = $row;
        }

        return ['item' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }

    private function get_user_cat_list($ru_id)
    {
        $user_cat = MerchantsShopInformation::where('user_id', $ru_id)->value('user_shop_main_category');
        $user_cat = $user_cat ?? 0;

        $arr = $new_arr = [];
        if (!empty($user_cat)) {
            $user_cat = explode("-", $user_cat);

            foreach ($user_cat as $key => $row) {
                $arr[$key] = explode(":", $row);
            }

            foreach ($arr as $key => $row) {
                foreach ($row as $ck => $rows) {
                    if ($ck > 0) {
                        $arr[$key][$ck] = explode(",", $rows);
                    }
                }
            }

            $arr = $this->get_level_three_cat1($arr);
            $arr = arr_foreach($arr);
            $arr = array_unique($arr);

            foreach ($arr as $key => $row) {
                $new_arr[$key]['id'] = $row;
                $cat_name = Category::where('cat_id', $row)->value('cat_name')->where('is_show', 1);
                $new_arr[$key]['name'] = $cat_name ?? 0;
            }

            $new_arr = get_array_sort($new_arr, 'id');
            return $new_arr;
        }
    }

    private function get_level_three_cat1($arr)
    {
        $new_arr = [];

        foreach ($arr as $key => $row) {
            $new_arr[$key]['cat'] = $row[0];
            $new_arr[$key]['cat_child'] = $row[1];
            $new_arr[$key]['cat_child_three'] = $this->get_level_three_cat2($row[1]);
        }

        foreach ($new_arr as $key => $row) {
            $new_arr[$key] = array_values($row);
        }

        return $new_arr;
    }

    private function get_level_three_cat2($arr)
    {
        $new_arr = [];

        foreach ($arr as $key => $row) {
            $new_arr[$key] = $this->get_cat_list_three($row);
        }

        $new_arr = arr_foreach($new_arr);
        return $new_arr;
    }

    private function get_cat_list_three($arr)
    {
        $res = Category::select('cat_id')->where('parent_id', $arr);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        foreach ($res as $key => $row) {
            $arr[$key] = $row['cat_id'];
        }

        return $arr;
    }

    private function get_user_cat_search($ru_id, $keyword = '', $arr = [])
    {
        $res = Category::select('cat_id')->where('is_show', 1)->whereHasIn('getMerchantsCategory', function ($query) use ($ru_id) {
            $query->where('user_id', $ru_id);
        })->with(['getMerchantsCategory' => function ($query) {
            $query->select('cat_id', 'cat_name');
        }]);

        $res = BaseRepository::getToArrayGet($res);

        $arr = $arr ? array_values($arr) : [];

        if ($res) {
            $arr = array_merge($arr, $res);
        }

        $new_arr = [];
        if (!empty($keyword)) {
            foreach ($arr as $key => $row) {
                $row = collect($row)->merge($row['get_merchants_category'])->except('get_merchants_category')->all();
                $pos = strpos($row['cat_name'], $keyword);
                if ($pos === false) {
                    unset($row);
                } else {
                    $row['id'] = $row['cat_id'];
                    $row['name'] = $row['cat_name'];
                    $new_arr[$key] = $row;
                }
            }
        } else {
            foreach ($arr as $key => $row) {
                $row = collect($row)->merge($row['get_merchants_category'])->except('get_merchants_category')->all();
                $row['id'] = $row['cat_id'];
                $row['name'] = $row['cat_name'];
                $new_arr[$key] = $row;
            }
        }

        return $new_arr;
    }
}
