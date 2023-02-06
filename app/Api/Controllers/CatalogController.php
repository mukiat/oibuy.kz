<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Models\Category;
use App\Models\SearchKeyword;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscEncryptRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Category\CategoryAttributeService;
use App\Services\Category\CategoryBrandService;
use App\Services\Category\CategoryGoodsService;
use App\Services\Category\CategoryService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Presale\PresaleService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Class CatalogController
 * @package App\Api\Controllers
 */
class CatalogController extends Controller
{
    protected $categoryService;
    protected $categoryGoodsService;
    protected $categoryAttributeService;
    protected $categoryBrandService;
    protected $merchantCommonService;
    protected $presaleService;

    public function __construct(
        CategoryService $categoryService,
        CategoryGoodsService $categoryGoodsService,
        CategoryAttributeService $categoryAttributeService,
        CategoryBrandService $categoryBrandService,
        MerchantCommonService $merchantCommonService,
        PresaleService $presaleService
    )
    {
        $this->categoryService = $categoryService;
        $this->categoryGoodsService = $categoryGoodsService;
        $this->categoryAttributeService = $categoryAttributeService;
        $this->categoryBrandService = $categoryBrandService;
        $this->merchantCommonService = $merchantCommonService;
        $this->presaleService = $presaleService;
    }

    /**
     * 分类导航页
     * @param int $cat_id
     * @return JsonResponse
     * @throws Exception
     */
    public function index($cat_id = 0)
    {
        $data = $this->categoryService->getMobileCategoryList($cat_id);
        return $this->succeed($data);
    }

    /**
     * @param $cat_id
     * @return JsonResponse
     * @throws Exception
     */
    public function show($cat_id)
    {
        $data = $this->categoryService->getCategory($cat_id);
        return $this->succeed($data);
    }

    /**
     * 返回分类价格区间
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function grade(Request $request)
    {
        $cat_id = $request->get('cat_id', 0);
        $cat_row = Category::catInfo($cat_id);
        $cat = BaseRepository::getToArrayFirst($cat_row);
        $children = $this->categoryService->getCatListChildren($cat_id);
        $price_max = (int)request()->input('price_max');
        $price_min = (int)request()->input('price_min');

        $price_grade = [];
        if ($cat['grade'] > 1) {
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
            $row = $this->categoryGoodsService->getGoodsPriceMaxMin($children);

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
                    if ($val['sn'] !== '') {
                        $price_grade[$key]['goods_num'] = $val['goods_num'];
                        $price_grade[$key]['start'] = $row['min'] + round($dx * $val['sn']);
                        $price_grade[$key]['end'] = $row['min'] + round($dx * ($val['sn'] + 1));
                        $price_grade[$key]['price_range'] = $price_grade[$key]['start'] . '&nbsp;-&nbsp;' . $price_grade[$key]['end'];
                    }
                }

                if ($price_min == 0 && $price_max == 0) {
                    asort($price_grade);
                }
            }
        }

        return $this->succeed(array_values($price_grade));
    }

    /**
     * 返回分类筛选属性
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function attribute(Request $request)
    {
        $cat_id = $request->get('cat_id', 0);
        $cat_row = Category::catInfo($cat_id);
        $cat = BaseRepository::getToArrayFirst($cat_row);
        $children = $this->categoryService->getCatListChildren($cat_id);
        $all_attr_list = [];

        /* 属性筛选 */
        if ($cat['filter_attr'] > 0) {
            $cat_filter_attr = explode(',', $cat['filter_attr']);       //提取出此分类的筛选属性
            $all_attr_list = [];

            foreach ($cat_filter_attr as $key => $value) {
                $attributeInfo = $this->categoryAttributeService->getCatAttribute($value);

                if ($attributeInfo) {
                    $all_attr_list[$key]['filter_attr_name'] = $attributeInfo['attr_name'];
                    $all_attr_list[$key]['attr_cat_type'] = $attributeInfo['attr_cat_type'];
                    $all_attr_list[$key]['filter_attr_id'] = (int)$value;

                    $attr_list = $this->categoryAttributeService->getCatAttributeAttrList($value, $children);

                    if ($attr_list) {
                        foreach ($attr_list as $k => $v) {
                            if (!empty($v['color_value'])) {
                                $arr_color2['c_value'] = $v['attr_value'];
                                $v['attr_value'] = $arr_color2;
                            }
                            $all_attr_list[$key]['attr_list'][$k]['attr_value'] = $v['attr_value'];
                            $all_attr_list[$key]['attr_list'][$k]['goods_id'] = $v['goods_id']; // 取分类ID
                            $all_attr_list[$key]['attr_list'][$k]['goods_attr_id'] = $v['goods_attr_id'];

                            if (!empty($filter_attr[$key])) {
                                if (!stripos($filter_attr[$key], ",") && $filter_attr[$key] == $v['goods_attr_id']) {
                                    $all_attr_list[$key]['attr_list'][$k]['selected'] = 1;
                                }

                                if (stripos($filter_attr[$key], ",")) {
                                    $color_arr = explode(",", $filter_attr[$key]);
                                    for ($i = 0; $i < count($color_arr); $i++) {
                                        if ($color_arr[$i] == $v['goods_attr_id']) {
                                            $all_attr_list[$key]['attr_list'][$k]['selected'] = 1;
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        unset($all_attr_list[$key]);
                    }
                }
            }

            $all_attr_list = DscEncryptRepository::allAttrList($all_attr_list);
        }

        return $this->succeed(array_values($all_attr_list));
    }

    /**
     * 店铺分类
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function shopcat(Request $request)
    {
        $ru_id = $request->input('ru_id', 0);

        $data = $this->categoryService->getShopCat(0, $ru_id);

        return $this->succeed($data);
    }

    /**
     * 分类商品列表
     *
     * @param Request $request
     * @param CategoryBrandService $categoryBrandService
     * @return JsonResponse
     * @throws Exception
     */
    public function goodslist(Request $request, CategoryBrandService $categoryBrandService)
    {
        //数据验证
        $this->validate($request, [
            'cat_id' => 'filled|integer',
            'intro' => ['nullable', 'string', Rule::in(['hot', 'new', 'best', 'promote'])],
            'sort' => ['filled', 'string', Rule::in(['goods_id', 'last_update', 'sales_volume', 'shop_price'])],
            'order' => ['filled', 'string', Rule::in(['DESC', 'ASC', 'desc', 'asc'])],
            'goods_num' => 'nullable|integer',
            'ship' => 'nullable|integer',
            'promotion' => 'nullable|integer',
            'filter_attr' => 'nullable|array',
        ]);

        $keywords = $request->input('keywords', []);
        $keywords = $keywords ? addslashes($keywords) : '';
        $sc_ds = e($request->input('sc_ds', ''));
        $cat_id = (int)$request->input('cat_id', 0);
        $intro = e($request->input('intro', '')); // 推荐 精品 热门, 'hot', 'new', 'best', 'promote'
        $brand = $request->input('brand', []);
        $price_min = $request->input('min', 0);
        $price_max = $request->input('max', 0);
        $price_min = floatval($price_min);
        $price_max = floatval($price_max);
        $ext = e($request->input('ext', ''));
        $self = (int)$request->input('self', 0);
        $size = (int)$request->input('size', 10);
        $page = (int)$request->input('page', 1);
        $sort = e($request->input('sort', 'goods_id'));
        $order = e($request->input('order', 'DESC'));
        $filter_attr = $request->input('filter_attr', []);
        $goods_num = (int)$request->input('goods_num', 0);
        $ship = (int)$request->input('ship', 0); // 是否支持配送
        $promotion = (int)$request->input('promotion', 0); // 是否促销
        $cou_id = (int)$request->input('cou_id', 0); // 优惠券条件
        $ru_id = (int)$request->input('ru_id', 0);

        if (config('scout.driver') === 'elasticsearch' && !empty($keywords)) {
            $searchEngine = new \App\Modules\Search\Services\SearchService();
            $searchKeyword = e($keywords);
            $searchResult = $searchEngine->search($searchKeyword, 0, $page);
            $count = $searchResult['total'];
            $goods_list = $searchResult['list'];
            return $this->succeed($goods_list);
        }

        $where_ext = [
            'warehouse_id' => $this->warehouse_id,
            'area_id' => $this->area_id,
            'area_city' => $this->area_city,
            'intro' => $intro,
            'ext' => $ext,
            'self' => $self,
            'ship' => $ship,
            'promotion' => $promotion,
            'cou_id' => $cou_id,
            'ru_id' => $ru_id,
            'sc_ds' => $sc_ds
        ];

        if ($where_ext['self'] == 1) {
            $selfRunList = $this->merchantCommonService->selfRunList();
            $where_ext['self_run_list'] = $selfRunList;
        }

        if ($cat_id == 0) {
            $children = [];
        } else {
            $children = $this->categoryService->getCatListChildren($cat_id);
        }

        $children = BaseRepository::getExplode($children);

        if (!empty($keywords)) {

            $keywords = is_array($keywords) ? BaseRepository::getImplode($keywords) : $keywords;

            //用法：
            if ($page == 1) {

                $session_id = app(SessionRepository::class)->realCartMacIp();

                $time = TimeRepository::getGmTime();
                $addtime = TimeRepository::getLocalDate('Y-m-d', $time);

                $keywordCount = SearchKeyword::where('addtime', $addtime)->where('keyword', $keywords);

                $uid = $this->authorization();
                if ($uid > 0) {
                    $keywordCount = $keywordCount->where('user_id', $uid);
                } else {
                    $keywordCount = $keywordCount->where('session_id', $session_id);
                }

                $search_id = $keywordCount->value('keyword_id');
                $search_id = $search_id ? $search_id : 0;

                if (empty($search_id)) {
                    $keywordOther = [
                        'keyword' => $keywords,
                        'pinyin' => '',
                        'is_on' => 0,
                        'count' => 1,
                        'addtime' => $addtime
                    ];

                    if ($uid > 0) {
                        $keywordOther['user_id'] = $uid;
                    } else {
                        $keywordOther['session_id'] = $session_id;
                    }

                    SearchKeyword::insertGetId($keywordOther);
                } else {
                    SearchKeyword::query()->where('keyword_id', $search_id)->increment('count', 1);
                }
            }

            // 关键词分词
            $keywords = CommonRepository::scwsWord($keywords);
            $keywords = BaseRepository::getArrayReversed($keywords);

            foreach ($keywords as $k => $v) {
                $keywords[0] = str_replace(' ', '', $keywords[0]);
            }

            $keywordBrand = $categoryBrandService->getCatBrand($brand, $children, $this->warehouse_id, $this->area_id, $this->area_city, $keywords);
            $brands_list = $keywordBrand['brand_list'];

            $searchKeywordBrand = ArrRepository::searchKeywordBrand($keywords, $brands_list);

            $brands_list = $searchKeywordBrand['brand_list'];
            $keywordBrand['is_keyword_brand'] = empty($keywordBrand['is_keyword_brand']) ? $searchKeywordBrand['is_keyword_brand'] : $keywordBrand['is_keyword_brand'];

            if ($keywordBrand['is_keyword_brand'] == 1) {
                $brand = BaseRepository::getKeyPluck($brands_list, 'brand_id');
                $brand = $brand ? implode(',', $brand) : '';

                if ($keywords) {

                    $is_keyword_brand = $searchKeywordBrand['is_keyword_brand'] ?? 0;

                    if ($is_keyword_brand == 1) {
                        $keywordBrandInfo = BaseRepository::getArraySqlFirst($brands_list);
                        if ($keywordBrandInfo) {
                            $where_ext['brand_name'] = $keywordBrandInfo['brand_name'];
                        }
                    } else {
                        foreach ($keywords as $k => $v) {

                            $sql = [
                                'where' => [
                                    [
                                        'name' => 'brand_name',
                                        'value' => $v
                                    ]
                                ]
                            ];

                            $keywordBrandInfo = BaseRepository::getArraySqlFirst($brands_list, $sql);
                            if ($keywordBrandInfo) {
                                $where_ext['brand_name'] = $keywordBrandInfo['brand_name'];
                                unset($keywords[$k]);
                            }
                        }
                    }

                    $keywords = $keywords ? array_values($keywords) : [];
                }
            }
        } else {
            $keywords = [];
        }

        $where_ext['presale_goods_id'] = $this->presaleService->presaleActivitySearch($keywords, $children);

        $data = $this->categoryGoodsService->getMobileCategoryGoodsList($this->uid, $keywords, $children, $brand, $price_min, $price_max, $filter_attr, $where_ext, $goods_num, $size, $page, $sort, $order);

        return $this->succeed($data);
    }

    /**
     * 品牌列表
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function brandList(Request $request)
    {
        $cat_id = $request->input('cat_id', 0);
        $ru_id = $request->input('ru_id', -1);
        $keywords = $request->input('keywords', '');

        $children = $this->categoryService->getCatListChildren($cat_id);
        $data = $this->categoryBrandService->getCategoryFilterBrandList($children, $keywords, $ru_id);

        return $this->succeed($data);
    }
}
