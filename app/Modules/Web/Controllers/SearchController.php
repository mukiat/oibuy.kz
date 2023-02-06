<?php

namespace App\Modules\Web\Controllers;

use App\Libraries\Pinyin;
use App\Models\Category;
use App\Models\Keywords;
use App\Models\SearchKeyword;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscEncryptRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Article\ArticleCommonService;
use App\Services\Category\CategoryAttributeService;
use App\Services\Category\CategoryBrandService;
use App\Services\Category\CategoryGoodsService;
use App\Services\Category\CategoryService;
use App\Services\Goods\GoodsGuessService;
use App\Services\Goods\GoodsService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Presale\PresaleService;
use App\Services\Search\SearchService;
use App\Services\Store\StoreStreetService;

/**
 * 搜索程序
 * Class SearchController
 * @package App\Modules\Web\Controllers
 */
class SearchController extends InitController
{
    protected $categoryService;
    protected $goodsService;
    protected $searchService;
    protected $storeStreetService;
    protected $dscRepository;
    protected $categoryGoodsService;
    protected $categoryBrandService;
    protected $categoryAttributeService;
    protected $articleCommonService;
    protected $goodsGuessService;
    protected $merchantCommonService;
    protected $presaleService;
    protected $sessionRepository;

    public function __construct(
        CategoryService $categoryService,
        GoodsService $goodsService,
        SearchService $searchService,
        StoreStreetService $storeStreetService,
        DscRepository $dscRepository,
        CategoryGoodsService $categoryGoodsService,
        CategoryBrandService $categoryBrandService,
        CategoryAttributeService $categoryAttributeService,
        ArticleCommonService $articleCommonService,
        GoodsGuessService $goodsGuessService,
        MerchantCommonService $merchantCommonService,
        PresaleService $presaleService,
        SessionRepository $sessionRepository
    )
    {
        $this->categoryService = $categoryService;
        $this->goodsService = $goodsService;
        $this->searchService = $searchService;
        $this->storeStreetService = $storeStreetService;
        $this->dscRepository = $dscRepository;
        $this->categoryGoodsService = $categoryGoodsService;
        $this->categoryBrandService = $categoryBrandService;
        $this->categoryAttributeService = $categoryAttributeService;
        $this->articleCommonService = $articleCommonService;
        $this->goodsGuessService = $goodsGuessService;
        $this->merchantCommonService = $merchantCommonService;
        $this->presaleService = $presaleService;
        $this->sessionRepository = $sessionRepository;
    }

    public function index()
    {
        if ($this->checkReferer() === false) {
            return redirect('/');
        }

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

        if (request()->has('keywords')) {
            $keywords = request()->input('keywords', '');
            $Loaction = dsc_url('/#/searchList?keywords=' . $keywords);
        } else {
            $Loaction = dsc_url('/#/search/');
        }

        /* 跳转H5 start */
        $uachar = $this->dscRepository->getReturnMobile($Loaction);

        if ($uachar) {
            return $uachar;
        }
        /* 跳转H5 end */

        $time = TimeRepository::getGmTime();

        $encode = request()->input('encode', '');
        if (empty($encode)) {
            load_helper('base');
            $string = request()->all();
            $string = stripslashes_deep($string);

            $string['search_encode_time'] = $time;
            $string = str_replace('+', '%2b', base64_encode(serialize($string)));
            return dsc_header("Location: search.php?encode=$string\n");
        } else {
            $string = base64_decode(trim($encode));

            if ($string !== false) {
                $string = unserialize($string);

                if ($string !== false) {
                    /* 用户在重定向的情况下当作一次访问 */
                    if (!empty($string['search_encode_time'])) {
                        if ($time > $string['search_encode_time'] + 2) {
                            define('INGORE_VISIT_STATS', true);
                        }
                    } else {
                        define('INGORE_VISIT_STATS', true);
                    }

                    /*  @author-bylu 优惠券列表入口 start */
                    if (isset($string['keywords']) && $string['keywords'] == '优惠券') {
                        return redirect(url('/coupons.php?act=coupons_index'));
                    }
                    /*  @author-bylu  end */
                } else {
                    $string = [];
                }
            } else {
                $string = [];
            }
        }

        if ($string && is_array($string)) {
            $request = array_merge(request()->all(), addslashes_deep($string));

            $request['keywords'] = isset($request['keywords']) && !empty($request['keywords']) ? e($request['keywords']) : '';
            $request['is_ship'] = isset($request['is_ship']) && !empty($request['is_ship']) ? e($request['is_ship']) : '';
        } else {
            load_helper('base');
            $request = stripslashes_deep(request()->all());
            $request['search_encode_time'] = $time;
        }

        /* 搜索指定商品 */
        $search_goods_id = $request['search_goods_id'] ?? 0;
        $search_goods_id = (int)$search_goods_id;

        $intromode = '';    //方式，用于决定搜索结果页标题图片

        /* 过滤 XSS 攻击和SQL注入 */
        get_request_filter();

        $act = addslashes(trim(request()->input('act', '')));
        $search_type = isset($request['store_search_cmt']) && !empty($request['store_search_cmt']) ? intval($request['store_search_cmt']) : 0; //搜索类型
        $user_id = session('user_id', 0);
        $session_id = $this->sessionRepository->realCartMacIp();

        //调位置
        $request['keywords'] = isset($request['keywords']) && $request['keywords'] ? strip_tags($request['keywords']) : ''; //去除html、php代码，主要防止js注入 by wu

        $request['keywords'] = !empty($request['keywords']) ? trim($request['keywords']) : '';
        $request['category'] = isset($request['category']) && !empty($request['category']) ? intval($request['category']) : 0;
        $request['goods_type'] = isset($request['goods_type']) && !empty($request['goods_type']) ? intval($request['goods_type']) : 0;
        $request['sc_ds'] = isset($request['sc_ds']) && !empty($request['sc_ds']) ? intval($request['sc_ds']) : 0;
        $request['outstock'] = isset($request['outstock']) && !empty($request['outstock']) ? 1 : 0;

        $get_price_max = isset($request['price_max']) ? floatval($request['price_max']) : 0;
        $get_price_min = isset($request['price_min']) ? floatval($request['price_min']) : 0;

        $request['price_max'] = isset($request['price_max']) && intval($request['price_max']) > 0 ? intval($request['price_max']) : 0;
        $request['price_min'] = isset($request['price_min']) && intval($request['price_min']) > 0 ? intval($request['price_min']) : 0;

        $price_min = $request['price_min'];
        $price_max = $request['price_max'];

        $request['brand'] = isset($request['brand']) ? $request['brand'] : 0;
        $brands_id = $this->dsc->get_explode_filter($request['brand']); //过滤品牌参数
        $brands_id = $brands_id ? $brands_id : '';

        $request['use_cou'] = isset($request['use_cou']) ? addslashes($request['use_cou']) : 0;

        $this->smarty->assign('filename', "search");

        $this->smarty->assign('search_type', $search_type);
        $this->smarty->assign('search_keywords', stripslashes($request['keywords']));

        /* 排序、显示方式以及类型 */
        $default_sort_order_method = config('shop.sort_order_method') == 0 ? 'DESC' : 'ASC';

        $order = (isset($request['order']) && in_array(trim(strtoupper($request['order'])), ['ASC', 'DESC'])) ? trim($request['order']) : $default_sort_order_method;
        $display = (isset($request['display']) && in_array(trim(strtolower($request['display'])), ['list', 'grid', 'text'])) ? trim($request['display']) : session('display_search', 'list');

        session([
            'display_search' => $display
        ]);

        $request['sc_ds'] = isset($request['sc_ds']) ? addslashes($request['sc_ds']) : '';

        $request['intro'] = isset($request['intro']) && !empty($request['intro']) ? addslashes($request['intro']) : '';
        $goods_ids = isset($request['goods_ids']) && !empty($request['goods_ids']) ? addslashes($request['goods_ids']) : '';
        $user_cou = isset($request['user_cou']) && !empty($request['user_cou']) ? intval($request['user_cou']) : 0;
        $request['attr'] = isset($request['attr']) && !empty($request['attr']) ? $request['attr'] : [];
        $cat_id = isset($request['category']) && !empty($request['category']) ? intval($request['category']) : 0;
        $cou_id = isset($request['cou_id']) && !empty($request['cou_id']) ? intval($request['cou_id']) : 0;
        $request['pickout'] = isset($request['pickout']) && !empty($request['pickout']) ? addslashes($request['pickout']) : '';

        $sort = 'goods_id';
        if ($search_type == 1) {
            if ($display == 'list') { //店铺列表
                $default_sort_order_type = "shop_id";
                $sort = (isset($request['sort']) && in_array(trim(strtolower($request['sort'])), ['shop_id', 'goods_number', 'sales_volume'])) ? trim($request['sort']) : $default_sort_order_type;
            } elseif ($display == 'grid' || $display == 'text') { //大图商品列表
                $default_sort_order_type = config('shop.sort_order_type') == 0 ? 'goods_id' : (config('shop.sort_order_type') == 1 ? 'shop_price' : 'last_update');
                $sort = (isset($request['sort']) && in_array(trim(strtolower($request['sort'])), ['goods_id', 'shop_price', 'last_update', 'sales_volume'])) ? trim($request['sort']) : $default_sort_order_type;
            }

            $is_self = '';
            $have = '';
            $is_ship = '';
        } else {
            $this->smarty->assign('open_area_goods', config('shop.open_area_goods'));

            $default_sort_order_type = config('shop.sort_order_type') == 0 ? 'goods_id' : (config('shop.sort_order_type') == 1 ? 'shop_price' : 'last_update');
            $sort = (isset($request['sort']) && in_array(trim(strtolower($request['sort'])), ['goods_id', 'shop_price', 'last_update', 'sales_volume', 'comments_number'])) ? trim($request['sort']) : $default_sort_order_type;
            $is_ship = isset($request['is_ship']) && !empty($request['is_ship']) ? addslashes_deep(trim($request['is_ship'])) : '';
            $is_self = isset($request['is_self']) && !empty($request['is_self']) ? intval($request['is_self']) : '';
            $have = isset($request['have']) && !empty($request['have']) ? intval($request['have']) : '';
        }

        $where_ext = [
            'self' => $is_self,
            'have' => $have,
            'ship' => $is_ship
        ];

        if ($where_ext['self'] == 1) {
            $selfRunList = $this->merchantCommonService->selfRunList();
            $where_ext['self_run_list'] = $selfRunList;
        }

        $page = !empty($request['page']) && intval($request['page']) > 0 ? intval($request['page']) : 1;
        $size = !empty(config('shop.page_size')) && intval(config('shop.page_size')) > 0 ? intval(config('shop.page_size')) : 10;

        //瀑布流 by wu start
        $this->smarty->assign('category_load_type', config('shop.category_load_type'));
        $this->smarty->assign('query_string', request()->server('QUERY_STRING'));

        $goods_num = !isset($request['goods_num']) && empty($request['goods_num']) ? 0 : intval($request['goods_num']);
        $best_num = intval(request()->input('best_num', 0));
        //瀑布流 by wu end

        $filter_attr_str = isset($request['filter_attr']) ? addslashes(trim($request['filter_attr'])) : '0';

        $filter_attr_str = trim(urldecode($filter_attr_str));
        $filter_attr_str = preg_match('/^[\d,\.]+$/', $filter_attr_str) ? $filter_attr_str : '';

        $filter_attr = empty($filter_attr_str) ? '' : explode('.', $filter_attr_str);
        $goods_type = !empty($request['goods_type']) ? intval($request['goods_type']) : 0;

        $insert_keyword = '';
        $search_id = 0;
        if ($search_type == 0) { //搜索商品
            /* 初始化搜索条件 */
            $insert_keyword = isset($request['keywords']) && !empty($request['keywords']) ? addslashes(trim($request['keywords'])) : '';

            //用法：
            $pin = new Pinyin();
            $pinyin = $insert_keyword ? $pin->Pinyin($insert_keyword, 'UTF8') : $insert_keyword;
            if ($page == 1) {

                $time = TimeRepository::getGmTime();
                $addtime = TimeRepository::getLocalDate('Y-m-d', $time);

                $keywordCount = SearchKeyword::where('addtime', $addtime)->where('keyword', $insert_keyword);

                if ($user_id > 0) {
                    $keywordCount = $keywordCount->where('user_id', $user_id);
                } else {
                    $keywordCount = $keywordCount->where('session_id', $session_id);
                }

                $search_id = $keywordCount->value('keyword_id');
                $search_id = $search_id ? $search_id : 0;

                if (empty($search_id)) {
                    $keywordOther = [
                        'keyword' => $insert_keyword,
                        'pinyin' => '',
                        'is_on' => 0,
                        'count' => 1,
                        'addtime' => $addtime,
                        'pinyin_keyword' => $pinyin,
                    ];

                    if ($user_id > 0) {
                        $keywordOther['user_id'] = $user_id;
                    } else {
                        $keywordOther['session_id'] = $session_id;
                    }

                    $search_id = SearchKeyword::insertGetId($keywordOther);
                } else {
                    SearchKeyword::query()->where('keyword_id', $search_id)->increment('count', 1);
                }
            }

            $local_time = TimeRepository::getLocalDate('Y-m-d');
            $count = Keywords::where('keyword', $insert_keyword)
                ->where('searchengine', 'dscmall')
                ->count();

            if ($count <= 0) {
                $keywordOther = [
                    'date' => $local_time,
                    'searchengine' => 'dscmall',
                    'keyword' => $insert_keyword,
                    'count' => 1
                ];

                Keywords::insert($keywordOther);
            } else {
                Keywords::where('keyword', $insert_keyword)->increment('count');
            }

            // 关键词分词
            $insert_keyword = CommonRepository::scwsWord($insert_keyword);
            $insert_keyword = $this->insertKeyword($insert_keyword);
        }

        $insert_keyword = !empty($search_goods_id) ? [] : $insert_keyword;

        if ($is_ship == 'is_shipping') {
            $is_ship = 1;
        }

        if ($act == 'load_more_goods') {
            $goods_floor = floor($goods_num / 4 * 8 / 5 - $best_num);

            if ($goods_floor < 0) {
                $best_size = $best_num;
            } else {
                $best_size = $goods_floor + 2;
            }
        } else {
            $best_size = 6;
        }

        $helps = $this->articleCommonService->getShopHelp();

        /*------------------------------------------------------ */
        //-- 高级搜索
        /*------------------------------------------------------ */
        if ($act == 'advanced_search') {

            $attributes = $this->searchService->getSeachableAttributes($goods_type);

            $this->smarty->assign('goods_type_selected', $goods_type);
            $this->smarty->assign('goods_type_list', $attributes['cate']);
            $this->smarty->assign('goods_attributes', $attributes['attr']);

            assign_template();
            assign_dynamic('search');
            $position = assign_ur_here(0, $GLOBALS['_LANG']['advanced_search']);
            $this->smarty->assign('page_title', $position['title']);    // 页面标题
            $this->smarty->assign('ur_here', $position['ur_here']);  // 当前位置

            $categories_pro = $this->categoryService->getCategoryTreeLeveOne();
            $this->smarty->assign('categories_pro', $categories_pro); // 分类树加强版

            $this->smarty->assign('helps', $helps);       // 网店帮助

            $this->smarty->assign('action', 'form');
            $this->smarty->assign('use_storage', config('shop.use_storage'));

            if ($search_type == 0) {

                $where = [
                    'warehouse_id' => $warehouse_id,
                    'area_id' => $area_id,
                    'area_city' => $area_city,
                    'rec_type' => 1
                ];

                /* 推荐商品 【'best' ：精品, 'new' ：新品, 'hot'：热销】 */
                $where['type'] = 'best';
                $best_goods = $this->goodsService->getRecommendGoods($where);

                $this->smarty->assign('best_goods', $best_goods);

                return $this->smarty->display('search.dwt');
            } elseif ($search_type == 1) {
                return $this->smarty->display('merchants_shop_list.dwt');
            }
        }

        /*------------------------------------------------------ */
        //-- 搜索结果
        /*------------------------------------------------------ */
        else {
            if (empty($ur_here)) {
                $ur_here = $GLOBALS['_LANG']['search_goods'];
            }

            if ($search_type == 0) { //搜索商品
                $ur_here = $GLOBALS['_LANG']['search_goods'];
            } elseif ($search_type == 1) { //店铺搜索
                $ur_here = $GLOBALS['_LANG']['search_store'];
            }

            assign_template();
            assign_dynamic('search');

            $this->smarty->assign('intromode', $intromode);
            $this->smarty->assign('helps', $helps);      // 网店帮助

            $this->smarty->assign('region_id', $warehouse_id);
            $this->smarty->assign('area_id', $area_id);

            if ($search_type == 0) { //搜索商品
                $re_brand = $request['brand'];
                $re_goods_type = $request['goods_type'];
                $re_sc_ds = $request['sc_ds'];
                $re_outstock = $request['outstock'];

                //模板缓存
                $cache_id = session('user_rank') . '-' . $act . '-' . $cat_id . '-' . $insert_keyword . '-' . $price_min . '-' . $price_max . '-' . $brands_id . '-' . $warehouse_id . '-' . $area_id . '-' . $area_city . '-' .
                    $get_price_min . '-' . $get_price_max . '-' . $user_cou . '-' . $goods_ids . '-' . $display . '-' . $cou_id . '-' .
                    $goods_num . '-' . $size . '-' . $page . '-' . $sort . '-' . $order . '-' . $search_type . '-' . $is_ship . '-' . $is_self . '-' . $have . '-' .
                    $re_brand . '-' . $re_goods_type . '-' . $re_sc_ds . '-' . $re_outstock . '-' . $search_goods_id . '-' . csrf_token();
                $cache_id = sprintf('%X', crc32($cache_id . '-' . session('user_rank', 0) . '_' . config('shop.lang')));

                $content = cache()->remember('search.dwt.' . $cache_id, config('shop.cache_time'), function () use ($best_size, $act, $search_id, $goods_type, $filter_attr_str, $filter_attr, $user_id, $cat_id, $insert_keyword, $price_min, $price_max, $brands_id, $warehouse_id, $area_id, $area_city, $where_ext, $cou_id, $ur_here, $get_price_min, $get_price_max, $user_cou, $goods_ids, $display, $goods_num, $size, $page, $sort, $order, $search_type, $is_ship, $is_self, $have, $re_brand, $re_goods_type, $re_sc_ds, $re_outstock, $request, $search_goods_id) {
                    $ubrand = isset($request['ubrand']) ? intval($request['ubrand']) : 0;
                    $this->smarty->assign('ubrand', $ubrand);

                    $goods_ids = BaseRepository::getExplode($goods_ids);
                    $arr_keyword = BaseRepository::getExplode($insert_keyword);

                    if ($arr_keyword) {
                        foreach ($arr_keyword as $k => $v) {
                            $arr_keyword[0] = str_replace(' ', '', $arr_keyword[0]);
                        }
                    }

                    $where_ext['presale_goods_id'] = $this->presaleService->presaleActivitySearch($arr_keyword);

                    /* 如果页面没有被缓存则重新获取页面的内容 */
                    $children = [];
                    if ($cat_id > 0) {
                        $children = $this->categoryService->getCatListChildren($cat_id);
                    }

                    $brands_list = [];
                    $keywordBrand = [];
                    if ($search_goods_id == 0) {
                        /* 平台品牌筛选 */
                        $keywordBrand = $this->categoryBrandService->getCatBrand($brands_id, $children, $warehouse_id, $area_id, $area_city, $arr_keyword, $where_ext['presale_goods_id']);
                        $brands_list = $keywordBrand['brand_list'];

                        $searchKeywordBrand = ArrRepository::searchKeywordBrand($arr_keyword, $brands_list);

                        $brands_list = $searchKeywordBrand['brand_list'];
                        $keywordBrand['is_keyword_brand'] = empty($keywordBrand['is_keyword_brand']) ? $searchKeywordBrand['is_keyword_brand'] : $keywordBrand['is_keyword_brand'];

                        if ($keywordBrand['is_keyword_brand'] == 1) {
                            $brands_id = BaseRepository::getKeyPluck($brands_list, 'brand_id');
                            $brands_id = $brands_id ? implode(',', $brands_id) : '';
                        }
                    }

                    $brands = [];
                    $get_bd = [];
                    $get_brand = [];

                    // 分配字母
                    $letter = range('A', 'Z');
                    $this->smarty->assign('letter', $letter);

                    if ($brands_list) {
                        foreach ($brands_list as $key => $val) {
                            $temp_key = $key; //by zhang

                            $brands[$temp_key]['brand_id'] = $val['brand_id'];
                            $brands[$temp_key]['brand_name'] = $val['brand_name'];

                            $bdimg_path = "data/brandlogo/";          // 图片路径
                            $bd_logo = $val['brand_logo'] ? $val['brand_logo'] : ""; // 图片名称
                            if (empty($bd_logo)) {
                                $brands[$temp_key]['brand_logo'] = "";      // 获取品牌图片
                            } else {
                                $brands[$temp_key]['brand_logo'] = $this->dscRepository->getImagePath($bdimg_path . $bd_logo);
                            }

                            $brands[$temp_key]['brand_letters'] = $val['brand_first_char'];  //获取品牌字母

                            $brandUrlOther = [
                                'bid' => $val['brand_id'], 'chkw' => $request['keywords'],
                                'filter_attr' => $filter_attr_str, 'cou_id' => $cou_id
                            ];

                            if (!empty($price_min)) {
                                $brandUrlOther['price_min'] = $price_min;
                            }

                            if (!empty($price_max)) {
                                $brandUrlOther['price_max'] = $price_max;
                            }

                            if (!empty($cou_id)) {
                                $brandUrlOther['cou_id'] = $cou_id;
                            }

                            $brands[$temp_key]['url'] = $this->dscRepository->buildUri('search', $brandUrlOther, $val['brand_name']);

                            /* 判断品牌是否被选中 */ // by zhang
                            if (!strpos($brands_id, ",") && $brands_id == $brands_list[$key]['brand_id']) {
                                $brands[$temp_key]['selected'] = 1;
                            }
                            if (stripos($brands_id, ",")) {
                                $brand2 = explode(",", $brands_id);
                                for ($i = 0; $i < count($brand2); $i++) {
                                    if ($brand2[$i] == $brands_list[$key]['brand_id']) {
                                        $brands[$temp_key]['selected'] = 1;
                                    }
                                }
                            }
                        }

                        $bd = "";
                        $br_url = "";
                        foreach ($brands as $key => $value) {
                            if (isset($value['selected']) && $value['selected'] == 1) {
                                $bd .= $value['brand_name'] . ",";
                                $get_bd[$key]['brand_id'] = $value['brand_id'];

                                $brand_id = "brand=" . $get_bd[$key]['brand_id'];

                                $get_bd[$key]['url'] = '';
                                if (stripos($value['url'], $brand_id)) {
                                    if (strpos($value['url'], '&amp;' . $brand_id)) {
                                        $get_bd[$key]['url'] = str_replace('&amp;' . $brand_id, '', $value['url']);
                                    } elseif (strpos($value['url'], '&' . $brand_id)) {
                                        $get_bd[$key]['url'] = str_replace('&' . $brand_id, '', $value['url']);
                                    } else {
                                        $get_bd[$key]['url'] = str_replace($brand_id, '', $value['url']);
                                    }
                                }

                                $br_url = $get_bd[$key]['url'];
                            }
                        }

                        $get_brand['br_url'] = $br_url;
                        $get_brand['bd'] = substr($bd, 0, -1);
                    }

                    if ($keywordBrand && $keywordBrand['is_keyword_brand'] == 1) {
                        if ($arr_keyword) {
                            $is_keyword_brand = $searchKeywordBrand['is_keyword_brand'] ?? 0;

                            if ($is_keyword_brand == 1) {
                                $keywordBrandInfo = BaseRepository::getArraySqlFirst($brands);
                                if ($keywordBrandInfo) {
                                    $where_ext['brand_name'] = $keywordBrandInfo['brand_name'];
                                }
                            } else {
                                foreach ($arr_keyword as $k => $v) {

                                    $sql = [
                                        'where' => [
                                            [
                                                'name' => 'brand_name',
                                                'value' => $v
                                            ]
                                        ]
                                    ];

                                    $keywordBrandInfo = BaseRepository::getArraySqlFirst($brands, $sql);
                                    if ($keywordBrandInfo) {
                                        $where_ext['brand_name'] = $keywordBrandInfo['brand_name'];
                                        unset($arr_keyword[$k]);
                                    }
                                }
                            }

                            $arr_keyword = $arr_keyword ? array_values($arr_keyword) : [];
                        }

                        $brands = [];
                    }

                    $this->smarty->assign('brands', $brands);
                    $this->smarty->assign('get_bd', $get_brand);               // 品牌已选模块

                    $price_grade = [];
                    $cat['grade'] = $cat['grade'] ?? 0;
                    if ($cat['grade'] > 1 && $search_goods_id == 0) {
                        /* 需要价格分级 */

                        /*
                          算法思路：
                          1、当分级大于1时，进行价格分级
                          2、取出该类下商品价格的最大值、最小值
                          3、根据商品价格的最大值来计算商品价格的分级数量级：
                          价格范围(不含最大值)    分级数量级
                          0-0.1                   0.001
                          0.1-1                   0.01
                          1-10                    0.1
                          10-100                  1
                          100-1000                10
                          1000-10000              100
                          4、计算价格跨度：
                          取整((最大值-最小值) / (价格分级数) / 数量级) * 数量级
                          5、根据价格跨度计算价格范围区间
                          6、查询数据库

                          可能存在问题：
                          1、
                          由于价格跨度是由最大值、最小值计算出来的
                          然后再通过价格跨度来确定显示时的价格范围区间
                          所以可能会存在价格分级数量不正确的问题
                          该问题没有证明
                          2、
                          当价格=最大值时，分级会多出来，已被证明存在
                         */

                        //获得当前分类下商品价格的最大值、最小值

                        $row = $this->categoryGoodsService->getGoodsPriceMaxMin($children, $brands_id, $warehouse_id, $area_id, $area_city, $where_ext, $goods_ids, $arr_keyword, $re_sc_ds);

                        // 取得价格分级最小单位级数，比如，千元商品最小以100为级数
                        $price_grade = 0.0001;
                        for ($i = -2; $i <= log10($row['max']); $i++) {
                            $price_grade *= 10;
                        }

                        //跨度
                        $dx = ceil(($row['max'] - $row['min']) / ($cat['grade']) / $price_grade) * $price_grade;
                        if ($dx == 0) {
                            $dx = $price_grade;
                        }

                        for ($i = 1; $row['min'] > $dx * $i; $i++) ;

                        for ($j = 1; $row['min'] > $dx * ($i - 1) + $price_grade * $j; $j++) ;
                        $row['min'] = $dx * ($i - 1) + $price_grade * ($j - 1);

                        for (; $row['max'] >= $dx * $i; $i++) ;
                        $row['max'] = $dx * ($i) + $price_grade * ($j - 1);

                        $price_grade = $this->categoryGoodsService->getGoodsPriceGrade($row['list'], $row['min'], $dx);

                        if ($price_grade) {
                            foreach ($price_grade as $key => $val) {
                                if ($val['sn'] != '') {
                                    $temp_key = $key;
                                    $price_grade[$temp_key]['goods_num'] = $val['goods_num'];
                                    $price_grade[$temp_key]['start'] = $row['min'] + round($dx * $val['sn']);
                                    $price_grade[$temp_key]['end'] = $row['min'] + round($dx * ($val['sn'] + 1));
                                    $price_grade[$temp_key]['price_range'] = $price_grade[$temp_key]['start'] . '&nbsp;-&nbsp;' . $price_grade[$temp_key]['end'];
                                    $price_grade[$temp_key]['formated_start'] = $this->dscRepository->getPriceFormat($price_grade[$temp_key]['start']);
                                    $price_grade[$temp_key]['formated_end'] = $this->dscRepository->getPriceFormat($price_grade[$temp_key]['end']);
                                    $price_grade[$temp_key]['url'] = $this->dscRepository->buildUri('search', ['bid' => $brands_id, 'chkw' => $request['keywords'], 'price_min' => $price_grade[$temp_key]['start'], 'price_max' => $price_grade[$temp_key]['end'], 'filter_attr' => $filter_attr_str, 'cou_id' => $cou_id], $ur_here);
                                    /* 判断价格区间是否被选中 */
                                    if (isset($request['price_min']) && $price_grade[$temp_key]['start'] == $price_min && $price_grade[$temp_key]['end'] == $price_max && $price_max > 0) {
                                        $price_grade[$temp_key]['selected'] = 1;
                                    } else {
                                        $price_grade[$temp_key]['selected'] = 0;
                                    }
                                }
                            }
                        }

                        if ($price_min == 0 && $price_max == 0) {
                            $this->smarty->assign('price_grade', $price_grade);
                        }
                    }

                    $g_price = [];       // 选中的价格
                    if ($price_grade) {
                        for ($i = 0; $i < count($price_grade); $i++) {
                            if (isset($price_grade[$i]['selected']) && $price_grade[$i]['selected'] == 1) {
                                $g_price[$i]['price_range'] = $price_grade[$i]['price_range'];
                                $g_price[$i]['url'] = $price_grade[$i]['url'];
                                $p_url = $g_price[$i]['url'];
                                $p_a = intval($get_price_min);
                                $p_b = intval($get_price_max);
                                $p_a = (string)$p_a;
                                $p_b = (string)$p_b;

                                if (stripos($p_url, $p_a) && stripos($p_url, $p_b)) {
                                    if ($p_a > $p_b) {
                                        $price = [$p_a, $p_b];
                                        $p_a = $price[1];
                                        $p_b = $price[0];
                                    }

                                    if ($p_a > 0 && $p_b > 0) {
                                        $g_price[$i]['url'] = str_replace($p_b, 0, str_replace($p_a, 0, $p_url));
                                    } elseif ($p_a == 0 && $p_b > 0) {
                                        $g_price[$i]['url'] = str_replace($p_b, 0, $p_url);
                                    }
                                }

                                break;
                            }
                        }
                    }
                    // 处理交换价格

                    if (empty($g_price) && ($price_min > 0 || $price_max > 0)) {
                        if ($price_min > $price_max) {
                            $price = [$price_min, $price_max];
                            $price_min = $price[1];
                            $price_max = $price[0];
                        }

                        $parray = [];
                        $parray['purl'] = $this->dscRepository->buildUri('search', ['bid' => $brands_id, 'chkw' => $request['keywords'], 'price_min' => 0, 'price_max' => 0, 'filter_attr' => $filter_attr_str, 'cou_id' => $cou_id], $ur_here);
                        $parray['min_max'] = $price_min . " - " . $price_max;
                        $this->smarty->assign('parray', $parray);     // 自定义价格恢复
                    }

                    $this->smarty->assign('g_price', $g_price);              // 价格已选模块

                    /* 属性筛选 */
                    $search_filter_attr = '';
                    if ($search_filter_attr > 0 && $search_goods_id == 0) {
                        $cat_filter_attr = explode(',', $search_filter_attr);       //提取出此分类的筛选属性
                        $all_attr_list = [];

                        foreach ($cat_filter_attr as $key => $value) {
                            $attributeInfo = $this->categoryAttributeService->getCatAttribute($value);

                            if ($attributeInfo) {
                                $all_attr_list[$key]['filter_attr_name'] = $attributeInfo['attr_name'];
                                $all_attr_list[$key]['attr_cat_type'] = $attributeInfo['attr_cat_type'];

                                $all_attr_list[$key]['filter_attr_id'] = $value; //by zhang

                                $attr_list = $this->categoryAttributeService->getCatAttributeAttrList($value, $children, $brands_id, $warehouse_id, $area_id, $area_city, $arr_keyword, $where_ext['presale_goods_id']);

                                $temp_arrt_url_arr = [];

                                for ($i = 0; $i < count($cat_filter_attr); $i++) {        //获取当前url中已选择属性的值，并保留在数组中
                                    $temp_arrt_url_arr[$i] = !empty($filter_attr[$i]) ? $filter_attr[$i] : 0;
                                }

                                foreach ($attr_list as $k => $v) {
                                    $temp_key = $k;

                                    //为url中代表当前筛选属性的位置变量赋值,并生成以‘.’分隔的筛选属性字符串
                                    $temp_arrt_url_arr[$key] = $v['goods_attr_id'];
                                    $temp_arrt_url = implode('.', $temp_arrt_url_arr);

                                    if (!empty($v['color_value'])) {
                                        $arr_color2['c_value'] = $v['attr_value'];
                                        $arr_color2['c_url'] = "#" . $v['color_value'];
                                        $v['attr_value'] = $arr_color2;
                                    }
                                    $all_attr_list[$key]['attr_list'][$temp_key]['attr_value'] = $v['attr_value'];
                                    $all_attr_list[$key]['attr_list'][$temp_key]['goods_id'] = $v['goods_id']; // 取分类ID
                                    $all_attr_list[$key]['attr_list'][$temp_key]['goods_attr_id'] = $v['goods_attr_id'];

                                    $cat_name = '';
                                    if ($cat_id > 0) {
                                        $cat_name = Category::where('cat_id', $cat_id)->value('cat_id');
                                        $cat_name = $cat_name ? $cat_name : '';
                                    }

                                    $all_attr_list[$key]['attr_list'][$temp_key]['url'] = $this->dscRepository->buildUri('category', ['cid' => $cat_id, 'bid' => $brands_id, 'price_min' => $price_min, 'price_max' => $price_max, 'filter_attr' => $temp_arrt_url], $cat_name);

                                    /** @var TYPE_NAME $filter_attr */
                                    if (!empty($filter_attr[$key])) {
                                        if (!stripos($filter_attr[$key], ",") && $filter_attr[$key] == $v['goods_id']) {
                                            $all_attr_list[$key]['attr_list'][$temp_key]['selected'] = 1;
                                        }

                                        if (stripos($filter_attr[$key], ",")) {
                                            $color_arr = explode(",", $filter_attr[$key]);
                                            for ($i = 0; $i < count($color_arr); $i++) {
                                                if ($color_arr[$i] == $v['goods_attr_id']) {
                                                    $all_attr_list[$key]['attr_list'][$temp_key]['selected'] = 1;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        /** -------------------多条件筛选数组处理 start-----------------------**/
                        // 颜色区块单独拿出
                        $color_list = [];
                        for ($i = 0; $i < count($all_attr_list) + 1; $i++) {
                            if (isset($all_attr_list[$i]['attr_cat_type']) && $all_attr_list[$i]['attr_cat_type'] == 1) {
                                for ($k = 0; $k < count($all_attr_list[$i]['attr_list']); $k++) {
                                    $array_color = $all_attr_list[$i]['attr_list'];
                                    if (count($array_color[$k]['attr_value']) == 1) {
                                        $array['c_value'] = $array_color[$k]['attr_value'];
                                        $array['c_url'] = "#FFFFFF";
                                        $all_attr_list[$i]['attr_list'][$k]['attr_value'] = $array;
                                    }
                                }
                                $color_list = $all_attr_list[$i];
                                unset($all_attr_list[$i]);
                            }
                        }

                        $c_array = [];
                        $k = "";
                        if ($color_list) {
                            for ($i = 0; $i < count($color_list['attr_list']); $i++) {
                                if ($color_list['attr_list'][$i]['selected'] == 1) {
                                    $c_array[$i]['filter_attr_name'] = $color_list['filter_attr_name'];
                                    $c_array[$i]['attr_list']['attr_value'] = $color_list['attr_list'][$i]['attr_value']['c_value'];
                                    $c_array[$i]['attr_list']['goods_id'] = $color_list['attr_list'][$i]['goods_id'];
                                    $c_array[$i]['attr_list']['goods_attr_id'] = $color_list['attr_list'][$i]['goods_attr_id'];
                                    $color_id = $c_array[$i]['attr_list']['goods_id'];
                                    $k .= $c_array[$i]['attr_list']['attr_value'] . ","; // 取选中的名称
                                    $color_url = $color_list['attr_list'][$i]['url'];
                                    if (strpos($color_url, $color_id)) {
                                        $c_array[$i]['attr_list']['url'] = str_replace($color_id, 0, $color_url);
                                    }
                                    $c_url = $c_array[$i]['attr_list']['url'];         // 还原
                                }
                            }
                        }

                        // 颜色合并
                        $c_array = [];
                        $c_array['filter_attr_name'] = $color_list['filter_attr_name'];
                        $c_array['attr_value'] = substr($k, 0, -1);
                        $c_array['url'] = $c_url;


                        $g_array = [];        // 选中的分类( 不含颜色分类 )
                        if ($all_attr_list) {
                            $all_attr_list = array_values($all_attr_list);
                            for ($i = 0; $i < count($all_attr_list) + 3; $i++) {
                                $k = "";
                                if (isset($all_attr_list[$i]['attr_list']) && $all_attr_list[$i]['attr_list']) {
                                    $all_attr_list[$i]['attr_list'] = array_values($all_attr_list[$i]['attr_list']);

                                    for ($j = 0; $j < count($all_attr_list[$i]['attr_list']); $j++) {
                                        $selected = $all_attr_list[$i]['attr_list'][$j]['selected'] ?? 0;

                                        if ($selected == 1) {
                                            $g_array[$i]['filter_attr_name'] = $all_attr_list[$i]['filter_attr_name'];
                                            $g_array[$i]['attr_list']['value'] = $all_attr_list[$i]['attr_list'][$j]['attr_value'];
                                            $g_array[$i]['attr_list']['goods_id'] = $all_attr_list[$i]['attr_list'][$j]['goods_id'];
                                            $g_array[$i]['attr_list']['goods_attr_id'] = $all_attr_list[$i]['attr_list'][$j]['goods_attr_id'];
                                            $g_url = $all_attr_list[$i]['attr_list'][$j]['url']; // 被选中的URL

                                            $sid = $all_attr_list[$i]['attr_list'][$j]['goods_attr_id'];     // 被选中的ID
                                            $sid = (string)$sid;

                                            if (strpos($g_url, $sid)) {
                                                $g_array[$i]['attr_list']['url'] = str_replace($sid, 0, $g_url);
                                            }
                                            $k .= $all_attr_list[$i]['attr_list'][$j]['attr_value'] . ",";
                                            $g_array[$i]['g_name'] = substr($k, 0, -1);
                                            $g_array[$i]['g_url'] = $g_array[$i]['attr_list']['url'];
                                        }
                                    }
                                }
                            }
                        }

                        $this->smarty->assign('c_array', $c_array);              // 颜色已选模块
                        $this->smarty->assign('g_array', $g_array);              // 其他已选模块
                        $this->smarty->assign('color_search', $color_list);           // 颜色筛选模块
                        /*             * -------------------多条件筛选数组处理 by zhang end-----------------------* */

                        $all_attr_list = DscEncryptRepository::allAttrList($all_attr_list);

                        $this->smarty->assign('filter_attr_list', $all_attr_list);
                    }

                    $action = '';
                    if (isset($request['action']) && $request['action'] == 'form') {
                        /* 要显示高级搜索栏 */
                        $adv_value['keywords'] = htmlspecialchars(stripcslashes($request['keywords']));
                        $adv_value['brand'] = $request['brand'];
                        $adv_value['price_min'] = $request['price_min'];
                        $adv_value['price_max'] = $request['price_max'];
                        $adv_value['category'] = $request['category'];

                        $attributes = [];
                        if ($goods_type > 0) {
                            $attributes = $this->searchService->getSeachableAttributes($goods_type);
                        }

                        /* 将提交数据重新赋值 */
                        if (isset($attributes['attr']) && !empty($attributes['attr'])) {
                            foreach ($attributes['attr'] as $key => $val) {
                                if ($request['attr'] && !empty($request['attr'][$val['id']])) {
                                    if ($val['type'] == 2) {
                                        $attributes['attr'][$key]['value']['from'] = !empty($request['attr'][$val['id']]['from']) ? htmlspecialchars(stripcslashes(trim($request['attr'][$val['id']]['from']))) : '';
                                        $attributes['attr'][$key]['value']['to'] = !empty($request['attr'][$val['id']]['to']) ? htmlspecialchars(stripcslashes(trim($request['attr'][$val['id']]['to']))) : '';
                                    } else {
                                        $attributes['attr'][$key]['value'] = !empty($request['attr'][$val['id']]) ? htmlspecialchars(stripcslashes(trim($request['attr'][$val['id']]))) : '';
                                    }
                                }
                            }
                        }

                        if ($re_sc_ds) {
                            $this->smarty->assign('scck', 'checked');
                        }

                        $this->smarty->assign('adv_val', $adv_value);
                        $this->smarty->assign('goods_type_list', $attributes['cate'] ?? []);
                        $this->smarty->assign('goods_attributes', $attributes['attr'] ?? []);
                        $this->smarty->assign('goods_type_selected', $goods_type);
                        $this->smarty->assign('action', 'form');
                        $this->smarty->assign('use_storage', config('shop.use_storage'));

                        $action = 'form';
                    }

                    /* ------------------------------------------------------ */
                    //-- 属性检索
                    /* ------------------------------------------------------ */

                    /* 如果检索条件都是无效的，就不用检索 */
                    if (isset($request['pickout'])) {
                        $pickout = 1;
                    } else {
                        $pickout = 0;
                    }

                    $attrListGoods = $this->searchService->getGoodsAttrListGoods($request['attr'], $pickout);

                    /* 推荐商品 【'best' ：精品, 'new' ：新品, 'hot'：热销】 */
                    $where = [
                        'warehouse_id' => $warehouse_id,
                        'area_id' => $area_id,
                        'area_city' => $area_city,
                        'keywords' => $arr_keyword,
                        'brand_id' => $brands_id
                    ];
                    $where['type'] = 'best';
                    $best_goods = $this->goodsService->getRecommendGoods($where, $best_size);

                    if (empty($best_goods)) {
                        $size = 24;
                    }

                    /*  @author-bylu 优惠券商品条件 start */
                    $cou_page_data = '';

                    $cou_list = [
                        'cou_id' => $cou_id,
                        'user_cou' => $user_cou
                    ];

                    if ($cou_id) {
                        $cou_page_data = "&cou_id=" . $cou_id . "&use_cou=" . $user_cou; //优惠券商品搜索标记(用于分页) bylu
                        $this->smarty->assign('cou_id', $cou_id); // 优惠券商品搜索标记(用于列表顶部类型检索) bylu
                    }
                    /*  @author-bylu  end */

                    // Elasticsearch
                    if (config('scout.driver') === 'elasticsearch') {
                        $searchEngine = new \App\Modules\Search\Services\SearchService();
                        $searchKeyword = e(current($arr_keyword));
                        $searchResult = $searchEngine->search($searchKeyword, $brands_id, $page);
                        $count = $searchResult['total'];
                        $goods_list = $searchResult['list'];
                    } else {
                        $goods_ids = !empty($search_goods_id) ? [$search_goods_id] : $goods_ids;

                        /* 获得符合条件的商品总数 */
                        $count = $this->searchService->getSearchGoodsCount($cat_id, $brands_id, $children, $area_id, $area_city, $price_min, $price_max, $filter_attr, $where_ext, $goods_ids, $arr_keyword, $request['intro'], $request['outstock'], $attrListGoods, $cou_list);

                        //录入查询数量
                        if ($page == 1) {
                            SearchKeyword::where('keyword_id', $search_id)->update(['result_count' => $count]);
                        }

                        $max_page = ($count > 0) ? ceil($count / $size) : 1;
                        if ($page > $max_page) {
                            $page = $max_page;
                        }

                        $goods_list = $this->searchService->getSearchGoodsList($cat_id, $brands_id, $children, $warehouse_id, $area_id, $area_city, $display, $price_min, $price_max, $filter_attr, $where_ext, $goods_ids, $arr_keyword, $request['intro'], $request['outstock'], $attrListGoods, $cou_list, $goods_num, $size, $page, $sort, $order);
                    }

                    $this->smarty->assign('search_driver', config('scout.driver'));
                    $this->smarty->assign('goods_list', $goods_list);
                    $this->smarty->assign('category', $cat_id);
                    $this->smarty->assign('keywords', htmlspecialchars(stripslashes($insert_keyword)));
                    $this->smarty->assign('brand', $brands_id);
                    $this->smarty->assign('price_min', $price_min);
                    $this->smarty->assign('price_max', $price_max);
                    $this->smarty->assign('outstock', $request['outstock']);
                    $this->smarty->assign('script_name', 'search');

                    if ($act == 'load_more_goods') {
                        $pageSize = intval(config('shop.page_size'));
                        if (($count - $goods_num) <= 5) {
                            $best_goods = [];
                        } elseif (($count - $goods_num) <= $pageSize) {
                            $best_goods = [];
                        } elseif (($count - $goods_num) <= ($pageSize * 2)) {
                            $best_goods = BaseRepository::getTake($best_goods, 6);
                        }
                    }

                    $this->smarty->assign('best_goods', $best_goods);

                    //瀑布流 by wu start
                    if ($act == 'load_more_goods') {
                        $this->smarty->assign('model', intval($request['model']));
                        $result = ['error' => 0, 'message' => '', 'cat_goods' => '', 'best_goods' => ''];
                        $result['cat_goods'] = html_entity_decode($this->smarty->fetch('library/more_goods.lbi')); //分类商品
                        $result['best_goods'] = html_entity_decode($this->smarty->fetch('library/more_goods_best.lbi')); //推广商品
                        return response()->json($result);
                    }
                    //瀑布流 by wu end

                    /* 分页 */
                    $url_format = "search.php?category=$cat_id&amp;keywords=" . urlencode(stripslashes($request['keywords'])) . "&amp;brand=" . $request['brand'] . "&amp;action=" . $action . "&amp;goods_type=" . $request['goods_type'] . "&amp;sc_ds=" . $request['sc_ds'] . $cou_page_data; //搜索优惠券商品的标记 bylu;
                    if (!empty($intromode)) {
                        $url_format .= "&amp;intro=" . $intromode;
                    }
                    if (isset($request['pickout'])) {
                        $url_format .= '&amp;pickout=1';
                    }
                    $url_format .= "&amp;price_min=" . $request['price_min'] . "&amp;price_max=" . $request['price_max'] . "&amp;sort=$sort";

                    $url_format .= $attrListGoods['attr_url'] . "&amp;order=$order&amp;page=";

                    $pager['search'] = [
                        'keywords' => stripslashes(trim($request['keywords'])),
                        'category' => $cat_id,
                        'store_search_cmt' => $search_type,
                        'brand' => $request['brand'],
                        'sort' => $sort,
                        'order' => $order,
                        'price_min' => $request['price_min'],
                        'price_max' => $request['price_max'],
                        'action' => $action,
                        'intro' => empty($intromode) ? '' : trim($intromode),
                        'goods_type' => $request['goods_type'],
                        'sc_ds' => $request['sc_ds'],
                        'outstock' => $request['outstock'],
                        'is_ship' => $is_ship,
                        'self_support' => $is_self,
                        'have' => $have,
                        'use_cou' => $request['use_cou'],
                        'cou_id' => $cou_id //优惠券商品分页标记 bylu
                    ];

                    $pager['search'] = array_merge($pager['search'], $attrListGoods['attr_arg']);

                    $pager = get_pager('search.php', $pager['search'], $count, $page, $size);
                    $pager['display'] = $display;

                    $this->smarty->assign('url_format', $url_format);

                    $this->smarty->assign('pager', $pager);

                    $categories_pro = $this->categoryService->getCategoryTreeLeveOne();
                    $this->smarty->assign('categories_pro', $categories_pro); // 分类树加强版

                    /** 小图 start by wang头部广告 **/
                    $search_left_ad = '';
                    $search_right_ad = '';
                    for ($i = 1; $i <= config('shop.auction_ad'); $i++) {
                        $search_left_ad .= "'search_left_ad" . $i . ","; //搜索商品页面头部左侧广告
                        $search_right_ad .= "'search_right_ad" . $i . ","; //搜索商品页面头部右侧广告
                    }

                    $this->smarty->assign('search_left_ad', $search_left_ad);
                    $this->smarty->assign('search_right_ad', $search_right_ad);

                    /**
                     * Start
                     *
                     * 猜你喜欢商品
                     */
                    $where = [
                        'warehouse_id' => $warehouse_id,
                        'area_id' => $area_id,
                        'area_city' => $area_city,
                        'user_id' => $user_id,
                        'history' => 1,
                        'page' => 1,
                        'limit' => 7
                    ];
                    $guess_goods = $this->goodsGuessService->getGuessGoods($where);

                    $this->smarty->assign('guess_goods', $guess_goods);
                    /* End */

                    $cur_url = get_return_self_url();
                    $this->smarty->assign('cur_url', $cur_url);

                    $position = assign_ur_here(0, $ur_here . ($request['keywords'] ? '_' . $request['keywords'] : ''));
                    $this->smarty->assign('page_title', stripslashes($position['title']));    // 页面标题
                    $this->smarty->assign('ur_here', stripslashes($position['ur_here']));  // 当前位置

                    return $this->smarty->display('search.dwt');
                });

                return $content;
            }

            /* ------------------------------------------------------ */
            //-- 搜索店铺
            /* ------------------------------------------------------ */
            elseif ($search_type == 1) {
                $keywords = htmlspecialchars(stripcslashes($request['keywords']));

                //模板缓存
                $cache_id = sprintf('%X', crc32($search_type . '-' . $warehouse_id . '-' . $area_city . '-' . $area_id . '-' . $order . '-' . $size . '-' . $page . '-' . $sort . '-' . $keywords . '-' . $display . '-' . session('user_rank', 0) . '_' . config('shop.lang')));
                $content = cache()->remember('merchants_shop_list.dwt.' . $cache_id, config('shop.cache_time'), function () use ($request, $user_id, $search_type, $warehouse_id, $area_city, $area_id, $order, $size, $page, $sort, $keywords, $display, $ur_here) {
                    $shop_count = 0;
                    $count = 0;


                    if ($display == 'list') { //店铺列表

                        $count = $this->storeStreetService->getStoreShopCount($keywords, $sort, 0, 0, 0, '', 1);

                        $shop_count = $count;

                        $size = 10;
                        $store_shop_list = $this->storeStreetService->getStoreShopList(1, $keywords, $count, $size, $page, $sort, $order, $warehouse_id, $area_id, $area_city);

                        $this->smarty->assign('store_shop_list', $store_shop_list['shop_list']);
                        $this->smarty->assign('pager', $store_shop_list['pager']);
                    } elseif ($display == 'grid' || $display == 'text') {
                        $count = $this->storeStreetService->getStoreShopGoodsCount($keywords, $area_id, $area_city);
                        $shop_count = $count;

                        //大图商品列表
                        if ($display == 'text') {
                            $size = 21;
                        } else {
                            $size = 20;
                        }

                        $shop_goods_list = $this->storeStreetService->getStoreShopGoodsList($keywords, $size, $page, $sort, $order, $warehouse_id, $area_id, $area_city);

                        $this->smarty->assign('shop_goods_list', $shop_goods_list);
                    }

                    if ($display == 'grid' || $display == 'text') {
                        $categories_pro = $this->categoryService->getCategoryTreeLeveOne();
                        $this->smarty->assign('categories_pro', $categories_pro); // 分类树加强版

                        /* 分页 */
                        $url_format = "search.php?category=0&amp;keywords=" . urlencode(stripslashes($request['keywords']));

                        $url_format .= "&amp;sort=$sort";

                        $url_format .= "&amp;order=$order&amp;page=";

                        $pager['search'] = [
                            'keywords' => stripslashes(trim($request['keywords'])),
                            'category' => 0,
                            'store_search_cmt' => $search_type,
                            'sort' => $sort,
                            'order' => $order,
                        ];

                        $pager = get_pager('search.php', $pager['search'], $count, $page, $size);
                        $pager['display'] = $display;

                        $this->smarty->assign('url_format', $url_format);
                        $this->smarty->assign('count', $count);
                        $this->smarty->assign('page', $page);
                        $this->smarty->assign('pager', $pager);
                    }

                    $this->smarty->assign('size', $size);
                    $this->smarty->assign('count', $count);
                    $this->smarty->assign('display', $display);
                    $this->smarty->assign('sort', $sort);

                    /** 小图 start **/
                    $recommend_merchants = '';
                    for ($i = 1; $i <= config('shop.auction_ad'); $i++) {
                        $recommend_merchants .= "'recommend_merchants" . $i . ","; //新首页推荐店铺广告 liu
                    }

                    $this->smarty->assign('recommend_merchants', $recommend_merchants);

                    $guess_store = $this->storeStreetService->getGuessStore($user_id, 4);

                    $this->smarty->assign('guess_store', $guess_store);

                    $this->smarty->assign('shop_count', $shop_count);

                    $cur_url = get_return_self_url();
                    $this->smarty->assign('cur_url', $cur_url);
                    $this->smarty->assign('script_name', 'merchants_shop');

                    $position = assign_ur_here(0, $ur_here . ($request['keywords'] ? '_' . $request['keywords'] : ''));
                    $this->smarty->assign('page_title', stripslashes($position['title']));    // 页面标题
                    $this->smarty->assign('ur_here', stripslashes($position['ur_here']));  // 当前位置

                    return $this->smarty->display('merchants_shop_list.dwt');
                });

                return $content;
            }
        }
    }
}
