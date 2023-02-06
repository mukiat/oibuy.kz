<?php

namespace App\Modules\Web\Controllers;

use App\Repositories\Common\DscRepository;
use App\Services\Activity\PackageService;
use App\Services\Article\ArticleCommonService;
use App\Services\Category\CategoryService;

/**
 * 超值礼包列表
 */
class PackageController extends InitController
{
    protected $packageService;
    protected $dscRepository;
    protected $articleCommonService;
    protected $categoryService;

    public function __construct(
        PackageService $packageService,
        DscRepository $dscRepository,
        ArticleCommonService $articleCommonService,
        CategoryService $categoryService
    ) {
        $this->packageService = $packageService;
        $this->dscRepository = $dscRepository;
        $this->articleCommonService = $articleCommonService;
        $this->categoryService = $categoryService;
    }


    public function index()
    {
        load_helper('transaction');

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

        /* 载入语言文件 */
        $this->dscRepository->helpersLang(['shopping_flow', 'user']);
        $this->dscRepository->helpersLang(['package'], 'admin');

        /* 跳转H5 start */
        $Loaction = dsc_url('/#/package');
        $uachar = $this->dscRepository->getReturnMobile($Loaction);

        if ($uachar) {
            return $uachar;
        }
        /* 跳转H5 end */

        /*------------------------------------------------------ */
        //-- PROCESSOR
        /*------------------------------------------------------ */

        assign_template();
        assign_dynamic('package');
        $position = assign_ur_here(0, $GLOBALS['_LANG']['shopping_package']);
        $this->smarty->assign('page_title', $position['title']);    // 页面标题
        $this->smarty->assign('ur_here', $position['ur_here']);  // 当前位置

        $categories_pro = $this->categoryService->getCategoryTreeLeveOne();
        $this->smarty->assign('categories_pro', $categories_pro); // 分类树加强版

        /* 读出所有礼包信息 */
        $where = [
            'warehouse_id' => $warehouse_id,
            'area_id' => $area_id,
            'user_id' => session('user_id', 0)
        ];
        $list = $this->packageService->getPackageList($where);
        $this->smarty->assign('list', $list);

        $this->smarty->assign('helps', $this->articleCommonService->getShopHelp());       // 网店帮助
        $this->smarty->assign('lang', $GLOBALS['_LANG']);
        $this->smarty->assign('category', 9999999999999999999);

        $this->smarty->assign('area_id', $area_id);
        $this->smarty->assign('region_id', $warehouse_id);
        $this->smarty->assign('area_city', $area_city);

        $this->smarty->assign('feed_url', (config('shop.rewrite') == 1) ? "feed-typepackage.xml" : 'feed.php?type=package'); // RSS URL
        return $this->smarty->display('package.dwt');
    }
}
