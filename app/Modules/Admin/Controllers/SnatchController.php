<?php

namespace App\Modules\Admin\Controllers;

use App\Libraries\Exchange;
use App\Models\Goods;
use App\Models\GoodsActivity;
use App\Models\SnatchLog;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Goods\GoodsProdutsService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Store\StoreCommonService;

/**
 * 夺宝奇兵管理程序
 */
class SnatchController extends InitController
{
    protected $merchantCommonService;
    protected $storeCommonService;
    protected $goodsProdutsService;
    protected $dscRepository;

    public function __construct(
        MerchantCommonService $merchantCommonService,
        StoreCommonService $storeCommonService,
        GoodsProdutsService $goodsProdutsService,
        DscRepository $dscRepository
    )
    {
        $this->merchantCommonService = $merchantCommonService;
        $this->storeCommonService = $storeCommonService;
        $this->goodsProdutsService = $goodsProdutsService;
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {
        $exc = new Exchange($this->dsc->table("goods_activity"), $this->db, 'act_id', 'act_name');

        $adminru = get_admin_ru_id();
        if ($adminru['ru_id'] == 0) {
            $this->smarty->assign('priv_ru', 1);
        } else {
            $this->smarty->assign('priv_ru', 0);
        }

        /*------------------------------------------------------ */
        //-- 活动列表
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'list') {
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['02_snatch_list']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['snatch_add'], 'href' => 'snatch.php?act=add']);

            $snatchs = $this->get_snatchlist($adminru['ru_id']);

            $this->smarty->assign('snatch_list', $snatchs['snatchs']);
            $this->smarty->assign('filter', $snatchs['filter']);
            $this->smarty->assign('record_count', $snatchs['record_count']);
            $this->smarty->assign('page_count', $snatchs['page_count']);

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            $sort_flag = sort_flag($snatchs['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            $this->smarty->assign('full_page', 1);

            //区分自营和店铺
            self_seller(basename(request()->getRequestUri()));


            return $this->smarty->display('snatch_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 查询、翻页、排序
        /*------------------------------------------------------ */

        elseif ($_REQUEST['act'] == 'query') {
            $snatchs = $this->get_snatchlist($adminru['ru_id']);

            $this->smarty->assign('snatch_list', $snatchs['snatchs']);
            $this->smarty->assign('filter', $snatchs['filter']);
            $this->smarty->assign('record_count', $snatchs['record_count']);
            $this->smarty->assign('page_count', $snatchs['page_count']);

            $store_list = $this->storeCommonService->getCommonStoreList();
            $this->smarty->assign('store_list', $store_list);

            $sort_flag = sort_flag($snatchs['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result(
                $this->smarty->fetch('snatch_list.dwt'),
                '',
                ['filter' => $snatchs['filter'], 'page_count' => $snatchs['page_count']]
            );
        }

        /*------------------------------------------------------ */
        //-- 添加活动
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'add') {
            /* 权限判断 */
            admin_priv('snatch_manage');

            /* 初始化信息 */
            $start_time = TimeRepository::getLocalDate('Y-m-d H:i:s');
            $end_time = TimeRepository::getLocalDate('Y-m-d H:i:s', strtotime('+1 week'));
            $snatch = ['start_price' => '1.00', 'end_price' => '800.00', 'max_price' => '0', 'cost_points' => '1', 'start_time' => $start_time, 'end_time' => $end_time, 'option' => '<option value="0">' . $GLOBALS['_LANG']['make_option'] . '</option>'];

            /* 创建 html editor */
            $snatch['act_desc'] = isset($snatch['act_desc']) && !empty($snatch['act_desc']) ? $snatch['act_desc'] : '';
            $snatch['act_promise'] = isset($snatch['act_promise']) && !empty($snatch['act_promise']) ? $snatch['act_promise'] : '';
            $snatch['act_ensure'] = isset($snatch['act_ensure']) && !empty($snatch['act_ensure']) ? $snatch['act_ensure'] : '';

            create_html_editor2('act_desc', 'act_desc', $snatch['act_desc']);
            create_html_editor2('act_promise', 'act_promise', $snatch['act_promise']);
            create_html_editor2('act_ensure', 'act_ensure', $snatch['act_ensure']);

            $this->smarty->assign('snatch', $snatch);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['snatch_add']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['02_snatch_list'], 'href' => 'snatch.php?act=list']);
            $this->smarty->assign('form_action', 'insert');
            $this->smarty->assign('ru_id', $adminru['ru_id']);

            set_default_filter(); //设置默认筛选


            return $this->smarty->display('snatch_info.dwt');
        } elseif ($_REQUEST['act'] == 'insert') {
            /* 权限判断 */
            admin_priv('snatch_manage');

            $goods_id = isset($_POST['goods_id']) && !empty($_POST['goods_id']) ? intval($_POST['goods_id']) : 0;
            $snatch_name = isset($_POST['snatch_name']) && !empty($_POST['snatch_name']) ? addslashes(trim($_POST['snatch_name'])) : '';

            /* 检查商品是否存在 */
            $goods_name = Goods::where('goods_id', $goods_id)->value('goods_name');
            $goods_name = $goods_name ? $goods_name : '';

            if (empty($goods_name)) {
                return sys_msg($GLOBALS['_LANG']['no_goods'], 1);
            }

            $count = GoodsActivity::where('act_type', GAT_SNATCH)->where('act_name', $snatch_name)->count();

            if ($count > 0) {
                return sys_msg(sprintf($GLOBALS['_LANG']['snatch_name_exist'], $_POST['snatch_name']), 1);
            }

            $act_desc = isset($_POST['act_desc']) ? $_POST['act_desc'] : '';
            $act_promise = isset($_POST['act_promise']) ? $_POST['act_promise'] : '';
            $dact_ensure = isset($_POST['act_ensure']) ? $_POST['act_ensure'] : '';

            /* 将时间转换成整数 */
            $start_time = TimeRepository::getLocalStrtoTime($_POST['start_time']);
            $end_time = TimeRepository::getLocalStrtoTime($_POST['end_time']);

            /* 处理提交数据 */
            if (empty($_POST['start_price'])) {
                $start_price = 0;
            } else {
                $start_price = $_POST['start_price'];
            }

            if (empty($_POST['end_price'])) {
                $end_price = 0;
            } else {
                $end_price = $_POST['end_price'];
            }

            if (empty($_POST['max_price'])) {
                $max_price = 0;
            } else {
                $max_price = $_POST['max_price'];
            }

            if (empty($_POST['cost_points'])) {
                $cost_points = 0;
            } else {
                $cost_points = $_POST['cost_points'];
            }

            $info = [
                'start_price' => $start_price,
                'end_price' => $end_price,
                'max_price' => $max_price,
                'cost_points' => $cost_points
            ];

            $product_id = isset($_POST['product_id']) && !empty($_POST['product_id']) ? intval($_POST['product_id']) : 0;
            $is_hot = isset($_POST['is_hot']) && !empty($_POST['is_hot']) ? intval($_POST['is_hot']) : 0;

            /* 插入数据 */
            $record = [
                'act_name' => $snatch_name,
                'act_type' => GAT_SNATCH,
                'goods_id' => $goods_id,
                'goods_name' => $goods_name,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'product_id' => $product_id,
                'is_hot' => $is_hot,
                'user_id' => $adminru['ru_id'],
                'act_desc' => $act_desc,
                'act_promise' => $act_promise,
                'act_ensure' => $dact_ensure,
                'review_status' => 3,
                'is_finished' => 0, 'ext_info' => serialize($info)
            ];

            $act_id = GoodsActivity::insertGetId($record);

            if ($act_id > 0) {
                admin_log($_POST['snatch_name'], 'add', 'snatch');
            }

            $link[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'snatch.php?act=list'];
            $link[] = ['text' => $GLOBALS['_LANG']['continue_add'], 'href' => 'snatch.php?act=add'];
            return sys_msg($GLOBALS['_LANG']['add_succeed'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 切换是否热销
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'toggle_hot') {
            $check_auth = check_authz_json('auction');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_POST['id']);
            $val = intval($_POST['val']);

            GoodsActivity::where('act_id', $id)->update([
                'is_hot' => $val
            ]);

            return make_json_result($val);
        }

        /*------------------------------------------------------ */
        //-- 编辑活动名称
        /*------------------------------------------------------ */

        elseif ($_REQUEST['act'] == 'edit_snatch_name') {
            $check_auth = check_authz_json('snatch_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_POST['id']);
            $val = json_str_iconv(trim($_POST['val']));

            /* 检查活动重名 */
            $count = GoodsActivity::where('act_type', GAT_SNATCH)->where('act_name', $val)->where('act_id', '<>', $id)->count();

            if ($count > 0) {
                return make_json_error(sprintf($GLOBALS['_LANG']['snatch_name_exist'], $val));
            }

            GoodsActivity::where('act_name', $id)->update([
                'is_hot' => $val
            ]);

            return make_json_result(stripslashes($val));
        }

        /*------------------------------------------------------ */
        //-- 删除指定的活动
        /*------------------------------------------------------ */

        elseif ($_REQUEST['act'] == 'remove') {
            $check_auth = check_authz_json('attr_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_GET['id']);
            if ($id > 0) {
                $res = $this->find_snatch_can_move($id);
                if ($res === false) {
                    return make_json_error($GLOBALS['_LANG']['has_snatch_recorded']);
                }
            }

            GoodsActivity::where('act_id', $id)->delete();

            $url = 'snatch.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));

            return dsc_header("Location: $url\n");
        }

        /*------------------------------------------------------ */
        //-- 编辑活动
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'edit') {
            /* 权限判断 */
            admin_priv('snatch_manage');

            $act_id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

            $snatch = $this->get_snatch_info($act_id);
            $snatch['option'] = '<option value="' . $snatch['goods_id'] . '">' . $snatch['goods_name'] . '</option>';
            $this->smarty->assign('snatch', $snatch);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['snatch_edit']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['02_snatch_list'], 'href' => 'snatch.php?act=list&' . list_link_postfix()]);
            $this->smarty->assign('form_action', 'update');
            $this->smarty->assign('ru_id', $adminru['ru_id']);

            /* 创建 html editor */
            create_html_editor2('act_desc', 'act_desc', $snatch['act_desc']);
            create_html_editor2('act_promise', 'act_promise', $snatch['act_promise']);
            create_html_editor2('act_ensure', 'act_ensure', $snatch['act_ensure']);

            /* 商品货品表 */
            $this->smarty->assign('good_products_select', get_good_products_select($snatch['goods_id']));

            set_default_filter(); //设置默认筛选


            return $this->smarty->display('snatch_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 更新活动
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'update') {
            /* 权限判断 */
            admin_priv('snatch_manage');

            $act_id = !empty($_POST['id']) ? intval($_POST['id']) : 0;
            $act_desc = isset($_POST['act_desc']) ? $_POST['act_desc'] : '';
            $act_promise = isset($_POST['act_promise']) ? $_POST['act_promise'] : '';
            $dact_ensure = isset($_POST['act_ensure']) ? $_POST['act_ensure'] : '';
            $goods_id = !empty($_POST['goods_id']) ? intval($_POST['goods_id']) : 0;
            $snatch_name = !empty($_POST['snatch_name']) ? trim($_POST['snatch_name']) : 0;

            /* 将时间转换成整数 */
            $_POST['start_time'] = TimeRepository::getLocalStrtoTime($_POST['start_time']);
            $_POST['end_time'] = TimeRepository::getLocalStrtoTime($_POST['end_time']);

            /* 处理提交数据 */
            if (empty($_POST['goods_id'])) {
                $_POST['goods_name'] = '';
            } else {
                $goods_name = Goods::where('goods_id', $goods_id)->value('goods_name');
                $_POST['goods_name'] = $goods_name;
            }

            if (empty($_POST['start_price'])) {
                $_POST['start_price'] = 0;
            }
            if (empty($_POST['end_price'])) {
                $_POST['end_price'] = 0;
            }
            if (empty($_POST['max_price'])) {
                $_POST['max_price'] = 0;
            }
            if (empty($_POST['cost_points'])) {
                $_POST['cost_points'] = 0;
            }
            if (isset($_POST['product_id']) && empty($_POST['product_id'])) {
                $_POST['product_id'] = 0;
            }

            /* 检查活动重名 */
            $count = GoodsActivity::where('act_type', GAT_SNATCH)->where('act_name', $snatch_name)->where('act_id', '<>', $act_id)->count();

            if ($count > 0) {
                return sys_msg(sprintf($GLOBALS['_LANG']['snatch_name_exist'], $snatch_name), 1);
            }

            $info = ['start_price' => $_POST['start_price'], 'end_price' => $_POST['end_price'], 'max_price' => $_POST['max_price'], 'cost_points' => $_POST['cost_points']];

            /* 更新数据 */
            $record = [
                'act_name' => $snatch_name,
                'goods_id' => $goods_id,
                'goods_name' => $goods_name,
                'start_time' => $_POST['start_time'],
                'end_time' => $_POST['end_time'],
                'act_desc' => isset($_POST['desc']) ? $_POST['desc'] : '',
                'product_id' => isset($_POST['product_id']) ? $_POST['product_id'] : '',
                'is_hot' => $_POST['is_hot'],
                'act_desc' => $act_desc,
                'act_promise' => $act_promise,
                'act_ensure' => $dact_ensure,
                'ext_info' => serialize($info)
            ];

            if (isset($_POST['review_status'])) {
                $review_status = !empty($_POST['review_status']) ? intval($_POST['review_status']) : 1;
                $review_content = !empty($_POST['review_content']) ? addslashes(trim($_POST['review_content'])) : '';

                $record['review_status'] = $review_status;
                $record['review_content'] = $review_content;
            }

            $res = GoodsActivity::where('act_id', $act_id)
                ->where('act_type', GAT_SNATCH)
                ->update($record);

            if ($res) {
                admin_log($_POST['snatch_name'], 'edit', 'snatch');
            }

            $link[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'snatch.php?act=list&' . list_link_postfix()];
            return sys_msg($GLOBALS['_LANG']['edit_succeed'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 查看活动详情
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'view') {
            /* 权限判断 */
            admin_priv('snatch_manage');

            $id = empty($_REQUEST['snatch_id']) ? 0 : intval($_REQUEST['snatch_id']);

            $bid_list = $this->get_snatch_detail();

            $this->smarty->assign('bid_list', $bid_list['bid']);
            $this->smarty->assign('filter', $bid_list['filter']);
            $this->smarty->assign('record_count', $bid_list['record_count']);
            $this->smarty->assign('page_count', $bid_list['page_count']);

            $sort_flag = sort_flag($bid_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);
            /* 赋值 */
            $this->smarty->assign('info', $this->get_snatch_info($id));
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('result', get_snatch_result($id));
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['view_detail']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['02_snatch_list'], 'href' => 'snatch.php?act=list']);
            return $this->smarty->display('snatch_view.dwt');
        }

        /*------------------------------------------------------ */
        //-- 排序、翻页活动详情
        /*------------------------------------------------------ */

        elseif ($_REQUEST['act'] == 'query_bid') {
            $bid_list = $this->get_snatch_detail();

            $this->smarty->assign('bid_list', $bid_list['bid']);
            $this->smarty->assign('filter', $bid_list['filter']);
            $this->smarty->assign('record_count', $bid_list['record_count']);
            $this->smarty->assign('page_count', $bid_list['page_count']);

            $sort_flag = sort_flag($bid_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result(
                $this->smarty->fetch('snatch_view.dwt'),
                '',
                ['filter' => $bid_list['filter'], 'page_count' => $bid_list['page_count']]
            );
        }


        /*------------------------------------------------------ */
        //-- 批量操作
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'batch') {
            /* 检查权限 */
            admin_priv('snatch_manage');

            if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes'])) {
                return sys_msg($GLOBALS['_LANG']['not_select_data'], 1);
            }

            $ids = !empty($_POST['checkboxes']) ? join(',', $_POST['checkboxes']) : 0;
            $del_count = count($_POST['checkboxes']);

            if (isset($_POST['type'])) {
                // 删除
                if ($_POST['type'] == 'batch_remove') {
                    $ids = BaseRepository::getExplode($ids);
                    $res = GoodsActivity::whereIn('act_id', $ids)->delete();

                    if ($res) {
                        /* 记日志 */
                        admin_log('', 'batch_remove', 'snatch_manage');
                    }

                    /*如果删除了夺宝活动，清除缓存*/
                    $links[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'snatch.php?act=list'];
                    return sys_msg(sprintf($GLOBALS['_LANG']['batch_drop_success'], $del_count), 0, $links);
                } // 审核
                elseif ($_POST['type'] == 'review_to') {
                    // review_status = 3审核通过 2审核未通过
                    $review_status = $_POST['review_status'];

                    GoodsActivity::whereIn('act_id', $ids)->update([
                        'review_status' => $review_status
                    ]);

                    $lnk[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'snatch.php?act=list&seller_list=1&' . list_link_postfix()];
                    return sys_msg($GLOBALS['_LANG']['snatch_adopt_status_success'], 0, $lnk);
                }
            }
        }

        /*------------------------------------------------------ */
        //-- 搜索商品
        /*------------------------------------------------------ */

        elseif ($_REQUEST['act'] == 'search_goods') {
            $filters = dsc_decode($_GET['JSON']);
            $filters->is_real = 1;//默认过滤虚拟商品
            $filters->no_product = 1;//过滤属性商品
            $arr['goods'] = get_goods_list($filters);

            if (!empty($arr['goods'][0]['goods_id'])) {
                $arr['products'] = $this->goodsProdutsService->getGoodProducts($arr['goods'][0]['goods_id']);
            }

            return make_json_result($arr);
        }

        /*------------------------------------------------------ */
        //-- 搜索货品
        /*------------------------------------------------------ */

        elseif ($_REQUEST['act'] == 'search_products') {
            $filters = dsc_decode($_GET['JSON']);

            if (!empty($filters->goods_id)) {
                $arr['products'] = $this->goodsProdutsService->getGoodProducts($filters->goods_id);
            }

            return make_json_result($arr);
        }
    }

    /**
     * 获取活动列表
     *
     * @param $ru_id
     * @return array
     * @throws \Exception
     */
    private function get_snatchlist($ru_id)
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'get_snatchlist' . '-' . $ru_id;
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 查询条件 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'act_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter['review_status'] = empty($_REQUEST['review_status']) ? 0 : intval($_REQUEST['review_status']);
        $filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? 1 : 0;  //商家和自营订单标识

        //卖场 start
        $filter['rs_id'] = empty($_REQUEST['rs_id']) ? 0 : intval($_REQUEST['rs_id']);
        $adminru = get_admin_ru_id();
        if ($adminru['rs_id'] > 0) {
            $filter['rs_id'] = $adminru['rs_id'];
        }
        //卖场 end

        $row = GoodsActivity::where('act_type', GAT_SNATCH);

        if (!empty($filter['keywords'])) {
            $row = $row->where('act_name', 'like', '%' . $this->dscRepository->mysqlLikeQuote($filter['keywords']) . '%');
        }

        if ($ru_id > 0) {
            $row = $row->where('user_id', $ru_id);
        }

        if ($filter['review_status']) {
            $row = $row->where('review_status', $filter['review_status']);
        }

        if ($filter['seller_list']) {
            $row = CommonRepository::constantMaxId($row, 'user_id');
        } else {
            $row = $row->where('user_id', 0);
        }

        //管理员查询的权限 -- 店铺查询 start
        $filter['store_search'] = !isset($_REQUEST['store_search']) ? -1 : intval($_REQUEST['store_search']);
        $filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
        $filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';

        if ($filter['store_search'] > -1) {
            if ($ru_id == 0) {
                if ($filter['store_search'] > 0) {
                    if ($filter['store_search'] == 1) {
                        $row = $row->where('ru_id', $filter['merchant_id']);
                    }

                    if ($filter['store_search'] > 1) {
                        $row = $row->where(function ($query) use ($filter) {
                            $query->whereHasIn('getMerchantsShopInformation', function ($query) use ($filter) {
                                if ($filter['store_search'] == 2) {
                                    $query->where('rz_shop_name', 'LIKE', '%' . mysql_like_quote($filter['store_keyword']) . '%');
                                } elseif ($filter['store_search'] == 3) {
                                    $query = $query->where('shoprz_brand_name', 'LIKE', '%' . mysql_like_quote($filter['store_keyword']) . '%');
                                    if ($filter['store_type']) {
                                        $query->where('shop_name_suffix', $filter['store_type']);
                                    }
                                }
                            });
                        });
                    }
                } else {
                    $row = $row->where('user_id', 0);
                }
            }
        }
        //管理员查询的权限 -- 店铺查询 end

        $res = $record_count = $row;

        $filter['record_count'] = $record_count->count();

        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 获活动数据 */
        $res = $res->orderBy($filter['sort_by'], $filter['sort_order']);

        if ($filter['start'] > 0) {
            $res = $res->skip($filter['start']);
        }

        if ($filter['page_size'] > 0) {
            $res = $res->take($filter['page_size']);
        }

        $filter['keywords'] = stripslashes($filter['keywords']);

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $ru_id = BaseRepository::getKeyPluck($res, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($res as $key => $val) {
                $res[$key]['snatch_name'] = $val['act_name'];
                $res[$key]['start_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $val['start_time']);
                $res[$key]['end_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $val['end_time']);
                $info = unserialize($res[$key]['ext_info']);
                unset($res[$key]['ext_info']);
                if ($info) {
                    foreach ($info as $info_key => $info_val) {
                        $res[$key][$info_key] = $info_val;
                    }
                }

                $res[$key]['ru_name'] = $merchantList[$val['user_id']]['shop_name'] ?? '';
            }
        }

        $arr = ['snatchs' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /**
     * 获取指定id snatch 的信息
     *
     * @access  public
     * @param int $id snatch_id
     *
     * @return array       array(snatch_id, snatch_name, goods_id,start_time, end_time, min_price, integral)
     */
    private function get_snatch_info($id)
    {
        $snatch = GoodsActivity::where('act_id', $id)->where('act_type', GAT_SNATCH);
        $snatch = BaseRepository::getToArrayFirst($snatch);

        /* 将时间转成可阅读格式 */
        $row = [];
        if ($snatch) {
            if ($GLOBALS['_CFG']['open_oss'] == 1) {
                $bucket_info = $this->dscRepository->getBucketInfo();
                $endpoint = $bucket_info['endpoint'];
            } else {
                $endpoint = url('/');
            }
            $desc_preg = get_goods_desc_images_preg($endpoint, $snatch['act_desc']);
            $snatch['act_desc'] = $desc_preg['goods_desc'];

            $snatch['snatch_name'] = $snatch['act_name'];
            $snatch['start_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $snatch['start_time']);
            $snatch['end_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $snatch['end_time']);
            $row = unserialize($snatch['ext_info']);
            unset($snatch['ext_info']);
        }

        if ($row) {
            foreach ($row as $key => $val) {
                $snatch[$key] = $val;
            }
        }

        return $snatch;
    }

    /**
     * 返回活动详细列表
     *
     * @access  public
     *
     * @return array
     */
    private function get_snatch_detail()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'get_snatch_detail';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $filter['snatch_id'] = empty($_REQUEST['snatch_id']) ? 0 : intval($_REQUEST['snatch_id']);
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'bid_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $row = SnatchLog::query();

        if (!empty($filter['snatch_id'])) {
            $row = $row->where('snatch_id', $filter['snatch_id']);
        }

        $res = $record_count = $row;

        /* 获得记录总数以及总页数 */
        $filter['record_count'] = $record_count->count();

        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 获得活动数据 */
        $res = $res->with([
            'getUsers'
        ]);

        $res = $res->orderBy($filter['sort_by'], $filter['sort_order']);

        if ($filter['start'] > 0) {
            $res = $res->skip($filter['start']);
        }

        if ($filter['page_size'] > 0) {
            $res = $res->take($filter['page_size']);
        }

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $key => $val) {
                $res[$key]['user_name'] = $val['get_users']['user_name'] ?? '';

                $res[$key]['bid_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $val['bid_time']);

                if (isset($GLOBALS['_CFG']['show_mobile']) && $GLOBALS['_CFG']['show_mobile'] == 0) {
                    $res[$key]['user_name'] = $this->dscRepository->stringToStar($val['user_name']);
                }
            }
        }

        $arr = ['bid' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /**夺宝是否可以删除
     * @param $act_id
     * @return bool
     */
    private function find_snatch_can_move($act_id)
    {
        $time = TimeRepository::getGmTime();
        $res = GoodsActivity::where('act_id', $act_id)->where('start_time', '<', $time)->where('end_time', '>', $time);
        $snatch_info = BaseRepository::getToArrayFirst($res);
        $user_snatch_count = SnatchLog::where('snatch_id', $act_id)->count();

        return $snatch_info || $user_snatch_count > 0 ? false : true;
    }
}
