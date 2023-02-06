<?php

namespace App\Modules\Web\Controllers;

use App\Services\Article\ArticleCommonService;
use App\Services\Category\CategoryService;

/**
 * 全部分类
 *
 * Class CategoryallController
 * @package App\Http\Controllers
 */
class CategoryallController extends InitController
{
    protected $articleCommonService;
    protected $categoryService;

    public function __construct(
        ArticleCommonService $articleCommonService,
        CategoryService $categoryService
    ) {
        $this->articleCommonService = $articleCommonService;
        $this->categoryService = $categoryService;
    }

    public function index()
    {

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

        //模板缓存
        $cache_id = sprintf('%X', crc32($warehouse_id . '-' . $area_id . '-' . $area_city . '-' . session('user_rank', 0) . '_' . config('shop.lang')));
        $content = cache()->remember('category_all.dwt.' . $cache_id, config('shop.cache_time'), function () use ($warehouse_id, $area_id, $area_city) {
            $position = assign_ur_here(0, $GLOBALS['_LANG']['all_category']);

            $this->smarty->assign('page_title', $position['title']);    // 页面标题
            $this->smarty->assign('ur_here', $position['ur_here']);  // 当前位置

            $category_all_left = '';
            $category_all_right = '';
            for ($i = 1; $i <= config('shop.auction_ad'); $i++) {
                $category_all_left .= "'category_all_left" . $i . ","; //左边广告位
                $category_all_right .= "'category_all_right" . $i . ","; //左边广告位
            }

            $this->smarty->assign('category_all_left', $category_all_left);
            $this->smarty->assign('category_all_right', $category_all_right);

            $categories_pro = $this->categoryService->getCategoryTreeLeveOne();
            $this->smarty->assign('categories_pro', $categories_pro); // 分类树加强版
            $this->smarty->assign('categories_list', $categories_pro);

            $top_goods = get_top10(0, '', 0, $warehouse_id, $area_id, $area_city);
            $this->smarty->assign('top_goods', $top_goods);           // 销售排行
            $this->smarty->assign('helps', $this->articleCommonService->getShopHelp());       // 网店帮助

            assign_dynamic('category_all');
            assign_template('c', $categories_pro);

            return $this->smarty->display('category_all.dwt');
        });

        return $content;
    }
}
