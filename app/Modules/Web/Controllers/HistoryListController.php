<?php

namespace App\Modules\Web\Controllers;

use App\Services\Article\ArticleCommonService;
use App\Services\Category\CategoryGoodsService;
use App\Services\Category\CategoryService;
use App\Services\History\HistoryService;

/**
 * 浏览列表插件
 */
class HistoryListController extends InitController
{
    protected $categoryGoodsService;
    protected $articleCommonService;
    protected $categoryService;
    protected $historyService;

    public function __construct(
        CategoryGoodsService $categoryGoodsService,
        ArticleCommonService $articleCommonService,
        CategoryService $categoryService,
        HistoryService $historyService
    )
    {
        $this->categoryGoodsService = $categoryGoodsService;
        $this->articleCommonService = $articleCommonService;
        $this->categoryService = $categoryService;
        $this->historyService = $historyService;
    }

    public function index()
    {
        /* 初始化分页信息 */
        $page = (int)request()->input('page', 1);
        $size = (int)config('shop.page_size', 10);
        $ship = (int)request()->input('ship', 0);
        //by wang
        $self = (int)request()->input('self', 0);
        $have = (int)request()->input('have', 0);

        /* 排序、显示方式以及类型 */
        $default_sort_order_method = $GLOBALS['_CFG']['sort_order_method'] == '0' ? 'DESC' : 'ASC';
        $default_sort_order_type = $GLOBALS['_CFG']['sort_order_type'] == '0' ? 'goods_id' : ($GLOBALS['_CFG']['sort_order_type'] == '1' ? 'shop_price' : 'last_update');

        $sort = e(request()->input('sort', ''));
        $sort = in_array(trim(strtolower($sort)), ['goods_id', 'shop_price', 'last_update', 'sales_volume']) ? trim($sort) : $default_sort_order_type;
        $order = e(request()->input('order', ''));
        $order = in_array(trim(strtoupper($order)), ['ASC', 'DESC']) ? trim($order) : $default_sort_order_method;

        $act = addslashes(request()->input('act', ''));

        assign_template('c', 0);

        $position = assign_ur_here(0, $GLOBALS['_LANG']['view_history']);
        $this->smarty->assign('page_title', $position['title']);    // 页面标题
        $this->smarty->assign('ur_here', $position['ur_here']);  // 当前位置

        $categories_pro = $this->categoryService->getCategoryTreeLeveOne();
        $this->smarty->assign('categories_pro', $categories_pro); // 分类树加强版

        $this->smarty->assign('helps', $this->articleCommonService->getShopHelp());              // 网店帮助
        $this->smarty->assign('show_marketprice', $GLOBALS['_CFG']['show_marketprice']);

        /**
         * Start
         *
         * @param $warehouse_id 仓库ID
         * @param $area_id 省份ID
         * @param $area_city 城市ID
         */
        $warehouse_id = $this->warehouseId();
        $area_id = $this->areaId();
        $area_city = $this->areaCity();
        /* End */

        $count = $this->historyService->getGoodsHistoryCount(0, $ship, $self, $have);
        $max_page = ($count > 0) ? ceil($count / $size) : 1;

        if ($page > $max_page) {
            $page = $max_page;
        }

        if ($act == 'delHistory') {
            $goods_id = (int)request()->input('goods_id', 0);
            $user_id = session('user_id', 0);

            $res = ['err_msg' => '', 'result' => '', 'qty' => 1];
            $this->historyService->historyDel($user_id, $goods_id);
            return response()->json($res);
        }

        $goodslist = $this->historyService->getGoodsHistoryPc($size, $page, $warehouse_id, $area_id, $area_city, 0, $ship, $self, $have);

        //瀑布流加载分类商品 by wu start
        $this->smarty->assign('category_load_type', $GLOBALS['_CFG']['category_load_type']);
        $this->smarty->assign('query_string', request()->server('QUERY_STRING'));
        $this->smarty->assign('script_name', 'history_list');
        $this->smarty->assign('category', 0);

        $best_goods = $this->categoryGoodsService->getCategoryRecommendGoods('', 'best', 0, $warehouse_id, $area_id, $area_city);
        $this->smarty->assign('best_goods', $best_goods);

        $this->smarty->assign('region_id', $warehouse_id);
        $this->smarty->assign('area_id', $area_id);

        $this->smarty->assign('goods_list', $goodslist); // 分类游览历史记录 ecmoban模板堂 --zhuo
        $this->smarty->assign('dwt_filename', 'history_list');

        assign_pager('history_list', 0, $count, $size, $sort, $order, $page, '', '', '', '', '', '', '', '', '', '', '', '', $ship, $self, $have); // 分页

        return $this->smarty->display('history_list.dwt');
    }
}
