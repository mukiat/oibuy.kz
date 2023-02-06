<?php

namespace App\Modules\Web\Controllers;

use App\Models\CollectBrand;
use App\Services\Article\ArticleCommonService;
use App\Services\Brand\BrandGoodsService;
use App\Services\Brand\BrandService;

/**
 * 品牌页面 brand new
 */
class BrandnController extends InitController
{
    protected $brandService;
    protected $articleCommonService;
    protected $brandGoodsService;

    public function __construct(
        BrandService $brandService,
        ArticleCommonService $articleCommonService,
        BrandGoodsService $brandGoodsService
    )
    {
        $this->brandService = $brandService;
        $this->articleCommonService = $articleCommonService;
        $this->brandGoodsService = $brandGoodsService;
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

        $this->smarty->assign('open_area_goods', config('shop.open_area_goods'));

        $user_id = session('user_id', 0);

        /* 获得请求的品牌ID */
        $brand_id = intval(request()->input('id'));

        /* 初始化分页信息 */
        $page = intval(request()->input('page', 1));

        $size = !empty(config('shop.page_size')) && intval(config('shop.page_size')) > 0 ? intval(config('shop.page_size')) : 10;

        $cate = intval(request()->input('cat'));
        //by wang是否包邮
        $is_ship = addslashes_deep(request()->input('is_ship'));
        $is_self = intval(request()->input('is_self'));

        $where_ext = [
            'self' => $is_self,
            'ship' => $is_ship
        ];

        $price_min = floatval(request()->input('price_min', 0));
        $price_max = floatval(request()->input('price_max', 0));

        /* 排序、显示方式以及类型 */
        $default_display_type = config('shop.show_order_type') == 0 ? 'list' : (config('shop.show_order_type') == 1 ? 'grid' : 'text');
        $default_sort_order_method = config('shop.sort_order_method') == 0 ? 'DESC' : 'ASC';
        $default_sort_order_type = config('shop.sort_order_type') == 0 ? 'goods_id' : (config('shop.sort_order_type') == 1 ? 'shop_price' : 'last_update');

        $sort = $default_sort_order_type;
        if (request()->exists('sort')) {
            $get_sort = request()->input('sort');
            if (in_array(trim(strtolower($get_sort)), ['goods_id', 'shop_price', 'last_update', 'sales_volume', 'comments_number'])) {
                $sort = $get_sort;
            }
        }

        $order = $default_sort_order_method;
        if (request()->exists('order')) {
            $get_order = request()->input('order');
            if (in_array(trim(strtoupper($get_order)), ['ASC', 'DESC'])) {
                $order = $get_order;
            }
        }

        $display = request()->cookie('dsc_display', $default_display_type);
        if (request()->exists('display')) {
            $get_display = request()->input('display');
            if (in_array(trim(strtolower($get_display)), ['list', 'grid', 'text'])) {
                $display = $get_display;
            }
        }
        $display = in_array($display, ['list', 'grid', 'text']) ? $display : 'text';

        cookie()->queue('dsc_display', $display, 60 * 24 * 7);

        $this->smarty->assign('sort', $sort);
        $this->smarty->assign('order', $order);
        $this->smarty->assign('price_min', $price_min);
        $this->smarty->assign('price_max', $price_max);
        $this->smarty->assign('is_ship', $is_ship);
        $this->smarty->assign('self_support', $is_self);

        $act = addslashes(trim(request()->input('act', 'cat')));
        $act = $act ? $act : 'cat';

        $this->smarty->assign('act', $act);

        // 品牌收藏
        $brand_info = $this->brandService->getBrandInfo($brand_id);

        /* 赋值固定内容 */
        assign_template();

        $position = assign_ur_here($cate, $brand_info['brand_name']);

        $helps = $this->articleCommonService->getShopHelp();

        $brand_cat_list = $this->brandService->getBrandRelatedCat($brand_id, $act);

        if (empty($brand_info)) {
            return dsc_header("Location: ./\n");
        }

        $brand_info['brand_desc'] = $brand_info['brand_desc'] ?? '';
        $brand_info['brand_name'] = $brand_info['brand_name'] ?? '';

        $this->smarty->assign('brand', $brand_info);
        $this->smarty->assign('data_dir', DATA_DIR);
        $this->smarty->assign('keywords', htmlspecialchars($brand_info['brand_desc']));
        $this->smarty->assign('description', htmlspecialchars($brand_info['brand_desc']));

        $this->smarty->assign('ur_here', $position['ur_here']); // 当前位置
        $this->smarty->assign('brand_id', $brand_id);
        $this->smarty->assign('category', $cate);

        $this->smarty->assign('helps', $helps);              // 网店帮助
        $this->smarty->assign('show_marketprice', config('shop.show_marketprice'));

        $this->smarty->assign('brand_cat_list', $brand_cat_list); // 相关分类 品牌商品所在分类

        $this->smarty->assign('feed_url', (config('shop.rewrite') == 1) ? "feed-b$brand_id.xml" : 'feed.php?brand=' . $brand_id);

        $brandn_top_ad = "";
        $brandn_left_ad = "";
        /* * 小图 广告* */
        for ($i = 1; $i <= config('shop.auction_ad'); $i++) {
            $brandn_top_ad .= "'brandn_top_ad" . $i . ","; //品牌商品页面头部左侧广告
            $brandn_left_ad .= "'brandn_left_ad" . $i . ","; //品牌商品页面头部右侧广告
        }
        $this->smarty->assign('brandn_top_ad', $brandn_top_ad);
        $this->smarty->assign('brandn_left_ad', $brandn_left_ad);
        /* * 小图 广告 end* */

        /* ------------------------------------------------------ */
        //-- 新品牌首页
        /* ------------------------------------------------------ */
        if ($act == 'index') {

            $where = [
                'brand_id' => $brand_id,
                'cats' => $cate,
                'where_ext' => $where_ext,
                'min' => $price_min,
                'max' => $price_max,
                'warehouse_id' => $warehouse_id,
                'area_id' => $area_id,
                'area_city' => $area_city
            ];

            $where['type'] = 'best';
            $best_goods = $this->brandGoodsService->getBrandRecommendGoods($where);
            $this->smarty->assign('best_goods', $best_goods); // 精品

            $where['type'] = 'hot';
            $hot_goods = $this->brandGoodsService->getBrandRecommendGoods($where);
            $this->smarty->assign('hot_goods', $hot_goods);
            return $this->smarty->display('brandn_index.dwt');
        }

        /* ------------------------------------------------------ */
        //-- 新品牌新品商品
        /* ------------------------------------------------------ */
        elseif ($act == 'new') {

            $where = [
                'type' => 'new',
                'brand_id' => $brand_id,
                'cats' => $cate,
                'where_ext' => $where_ext,
                'min' => $price_min,
                'max' => $price_max,
                'warehouse_id' => $warehouse_id,
                'area_id' => $area_id,
                'area_city' => $area_city
            ];
            $goods = $this->brandGoodsService->getBrandRecommendGoods($where);
            $goods = $this->dsc->page_array($size, $page, $goods);

            $new_goods = $goods['list'];

            assign_pager('brandn', $cate, $goods['record_count'], $size, $sort, $order, $page, '', $brand_id, $price_min, $price_max, $display, '', '', '', 0, '', '', $act, $is_ship, $is_self); // 分页

            $this->smarty->assign('new_goods', $new_goods);
            return $this->smarty->display('brandn_new.dwt');
        }

        /* ------------------------------------------------------ */
        //-- 新品牌热门商品
        /* ------------------------------------------------------ */
        elseif ($act == 'hot') {
            $where = [
                'type' => 'hot',
                'brand_id' => $brand_id,
                'cats' => $cate,
                'where_ext' => $where_ext,
                'min' => $price_min,
                'max' => $price_max,
                'warehouse_id' => $warehouse_id,
                'area_id' => $area_id,
                'area_city' => $area_city
            ];
            $goods = $this->brandGoodsService->getBrandRecommendGoods($where);
            $goods = $this->dsc->page_array($size, $page, $goods);

            $hot_goods = $goods['list'];

            assign_pager('brandn', $cate, $goods['record_count'], $size, $sort, $order, $page, '', $brand_id, $price_min, $price_max, $display, '', '', '', 0, '', '', $act, $is_ship, $is_self); // 分页

            $this->smarty->assign('hot_goods', $hot_goods);
            return $this->smarty->display('brandn_hot.dwt');
        }

        /* ------------------------------------------------------ */
        //-- 新品牌全部分类
        /* ------------------------------------------------------ */
        elseif ($act == 'cat') {
            $where = [
                'brand_id' => $brand_id,
                'cats' => $cate,
                'where_ext' => $where_ext,
                'min' => $price_min,
                'max' => $price_max,
                'warehouse_id' => $warehouse_id,
                'area_id' => $area_id,
                'area_city' => $area_city
            ];

            $where['type'] = 'best';
            $best_goods = $this->brandGoodsService->getBrandRecommendGoods($where);
            $this->smarty->assign('best_goods', $best_goods); // 精品

            $where['type'] = 'hot';
            $hot_goods = $this->brandGoodsService->getBrandRecommendGoods($where);
            $this->smarty->assign('hot_goods', $hot_goods); //热卖

            $where['type'] = 'new';
            $new_goods = $this->brandGoodsService->getBrandRecommendGoods($where);
            $this->smarty->assign('new_goods', $new_goods); //新品

            $count = $this->brandGoodsService->getGoodsCountByBrand($brand_id, $cate, $price_min, $price_max, $where_ext, $warehouse_id, $area_id, $area_city);
            $goodslist = $this->brandGoodsService->getBrandGoodsList($brand_id, $cate, $price_min, $price_max, $where_ext, $warehouse_id, $area_id, $area_city, 0, $size, $page, $sort, $order);
            assign_pager('brandn', $cate, $count, $size, $sort, $order, $page, '', $brand_id, $price_min, $price_max, $display, '', '', '', 0, '', '', $act, $is_ship, $is_self); // 分页

            //新增分类页商品相册 by mike end
            $this->smarty->assign('goods_list', $goodslist);


            //获取seo start
            $seo = get_seo_words('brand');

            if ($seo) {
                foreach ($seo as $key => $value) {
                    $seo[$key] = str_replace(['{sitename}', '{description}', '{name}'], [$position['title'], $brand_info['brand_desc'], $brand_info['brand_name']], $value);
                }
            }

            if (isset($seo['keywords']) && !empty($seo['keywords'])) {
                $this->smarty->assign('keywords', htmlspecialchars($seo['keywords']));
            } else {
                $this->smarty->assign('keywords', htmlspecialchars(config('shop.shop_keywords')));
            }

            if (isset($seo['description']) && !empty($seo['description'])) {
                $this->smarty->assign('description', htmlspecialchars($seo['description']));
            } else {
                $this->smarty->assign('description', htmlspecialchars(config('shop.shop_desc')));
            }

            if (isset($seo['title']) && !empty($seo['title'])) {
                $this->smarty->assign('page_title', htmlspecialchars($seo['title']));
            } else {
                $this->smarty->assign('page_title', $position['title']);
            }
            //获取seo end

            return $this->smarty->display('brandn_cat.dwt');
        }

        /* ------------------------------------------------------ */
        //-- 换一组
        /* ------------------------------------------------------ */
        elseif ($act == 'change_index') {
            $result = ['err' => 0, 'msg' => '', 'content' => ''];

            $where = [
                'type' => 'rand',
                'brand_id' => $brand_id,
                'cats' => $cate,
                'where_ext' => $where_ext,
                'min' => $price_min,
                'max' => $price_max,
                'warehouse_id' => $warehouse_id,
                'area_id' => $area_id,
                'area_city' => $area_city
            ];
            $best_rand = $this->brandGoodsService->getBrandRecommendGoods($where);
            $this->smarty->assign('best_goods', $best_rand);
            $result['content'] = $this->smarty->fetch('library/brandn_best_goods.lbi');

            return response()->json($result);
        }

        /* ------------------------------------------------------ */
        //-- 收藏品牌
        /* ------------------------------------------------------ */
        elseif ($act == 'collect') {
            $result = ['error' => 0, 'message' => '', 'url' => ''];

            $cat_id = intval(request()->input('cat_id'));

            $merchant_id = intval(request()->input('merchant_id'));
            $script_name = htmlspecialchars(trim(request()->input('script_name')));
            $cur_url = htmlspecialchars(trim(request()->input('cur_url')));

            if ($user_id) {
                /* 检查是否已经存在于用户的收藏夹 */
                $count = CollectBrand::where('user_id', $user_id)->where('brand_id', $brand_id)->count();
                if ($count > 0) {
                    $result['error'] = 1;
                    $result['message'] = $GLOBALS['_LANG']['collect_brand_existed'];
                    return response()->json($result);
                } else {
                    $time = gmtime();

                    $collectBrand = [
                        'user_id' => $user_id,
                        'brand_id' => $brand_id,
                        'add_time' => $time,
                    ];

                    $rec_id = CollectBrand::insertGetId($collectBrand);

                    if (!$rec_id) {
                        $result['error'] = 1;
                        $result['message'] = $this->db->errorMsg();
                        return response()->json($result);
                    } else {
                        $result['collect_count'] = CollectBrand::where('brand_id', $brand_id)->count();
                        $result['brand_id'] = $brand_id;

                        $result['error'] = 0;
                        $result['message'] = $GLOBALS['_LANG']['collect_brand_success'];
                        return response()->json($result);
                    }
                }
            } else {
                if ($script_name != '') {
                    if ($script_name == 'category') {
                        $result['url'] = get_return_category_url($cat_id);
                    } elseif ($script_name == 'search' || $script_name == 'merchants_shop') {
                        $result['url'] = $cur_url;
                    } elseif ($script_name == 'merchants_store_shop') {
                        $result['url'] = get_return_store_shop_url($merchant_id);
                    }
                }
                $result['error'] = 2;
                $result['message'] = $GLOBALS['_LANG']['login_please'];
                return response()->json($result);
            }
        }

        /* ------------------------------------------------------ */
        //-- 取消关注品牌
        /* ------------------------------------------------------ */
        elseif ($act == 'cancel') {
            $result = ['error' => 0, 'message' => '', 'url' => ''];
            $user_id = intval(request()->input('user_id'));

            //type = 1从用户中心取消关注
            $type = intval(request()->input('type'));

            CollectBrand::where('brand_id', $brand_id)->where('user_id', $user_id)->delete();

            $count = CollectBrand::where('user_id', $user_id)->where('brand_id', $brand_id)->count();

            if ($type == 0) {
                if ($count) {
                    $result['error'] = 1;
                    $result['message'] = $this->db->errorMsg();
                    return response()->json($result);
                } else {
                    $result['collect_count'] = CollectBrand::where('brand_id', $brand_id)->where('user_id', $user_id)->count();

                    $result['error'] = 0;
                    $result['message'] = $GLOBALS['_LANG']['cancel_brand_success'];
                    $result['brand_id'] = $brand_id;

                    return response()->json($result);
                }
            } elseif ($type == 1) {
                if ($count) {
                    return show_message($this->db->errorMsg(), $GLOBALS['_LANG']['back'], $this->dsc->url, 'error');
                } else {
                    return dsc_header("Location: user_collect.php?act=focus_brand\n");
                }
            }
        }

        /* ------------------------------------------------------ */
        //-- 新品牌全部分类
        /* ------------------------------------------------------ */
        elseif ($act == 'get_brand_cat_goods') {
            $result = ['error' => 0, 'content' => ''];

            $count = $this->brandGoodsService->getGoodsCountByBrand($brand_id, $cate, $price_min, $price_max, $where_ext, $warehouse_id, $area_id, $area_city);
            $goods_list = $this->brandGoodsService->getBrandGoodsList($brand_id, $cate, $price_min, $price_max, $where_ext, $warehouse_id, $area_id, $area_city, 0, $size, $page, $sort, $order);

            assign_pager('brandn', $cate, $count, $size, $sort, $order, $page, '', $brand_id, $price_min, $price_max, $display, '', '', '', 0, '', '', $act, $is_ship, $is_self); // 分页

            $this->smarty->assign('goods_list', $goods_list);
            $this->smarty->assign('cat_id', $cate);

            $result['content'] = $this->smarty->fetch('library/brand_goods_list.lbi');
            return response()->json($result);
        }
    }
}
