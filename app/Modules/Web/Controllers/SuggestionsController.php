<?php

namespace App\Modules\Web\Controllers;

use App\Models\Category;
use App\Models\Goods;
use App\Models\Products;
use App\Models\ProductsArea;
use App\Models\ProductsWarehouse;
use App\Models\SearchKeyword;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Category\CategoryBrandService;
use App\Services\Category\CategoryService;
use App\Services\Search\SearchService;

/*
 * 搜索框提示功能
 */

class SuggestionsController extends InitController
{
    protected $categoryService;
    protected $searchService;
    protected $dscRepository;
    protected $categoryBrandService;

    public function __construct(
        CategoryService $categoryService,
        SearchService $searchService,
        DscRepository $dscRepository,
        CategoryBrandService $categoryBrandService
    )
    {
        $this->categoryService = $categoryService;
        $this->searchService = $searchService;
        $this->dscRepository = $dscRepository;
        $this->categoryBrandService = $categoryBrandService;
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

        $keyword = addslashes(trim(request()->input('keyword', '')));
        $category = trim(request()->input('category', 0));

        $children = [];
        if ($category == $GLOBALS['_LANG']['Template']) {
            $children = $this->categoryService->getCatListChildren($category);
        } elseif ($category == $GLOBALS['_LANG']['plugins']) {
            $children = $this->categoryService->getCatListChildren($category);
        }

        $keyword = $keyword ? addslashes($keyword) : '';

        if (empty($keyword)) {
            echo '';
        } else {

            /* 检查关键字是否在查货号商品 */
            $goods_id = Goods::where('goods_sn', 'like', '%' . $keyword . '%')->pluck('goods_id');
            $goods_id = BaseRepository::getToArray($goods_id);

            if (empty($goods_id)) {
                $goods_id = Products::where('product_sn', 'like', '%' . $keyword . '%')->pluck('goods_id');
                $goods_id = BaseRepository::getToArray($goods_id);

                if (empty($goods_id)) {
                    $goods_id = ProductsWarehouse::where('product_sn', 'like', '%' . $keyword . '%')->pluck('goods_id');
                    $goods_id = BaseRepository::getToArray($goods_id);

                }

                if (empty($goods_id)) {
                    $goods_id = ProductsArea::where('product_sn', 'like', '%' . $keyword . '%')->pluck('goods_id');
                    $goods_id = BaseRepository::getToArray($goods_id);
                }
            }

            if (empty($goods_id)) {
                $goodsKeyword = CommonRepository::scwsWord($keyword);
                $keywordBrand = $this->categoryBrandService->getCatBrand([], $children, $warehouse_id, $area_id, $area_city, $goodsKeyword);
                $brands_list = $keywordBrand['brand_list'] ?? [];

                $brand_id = BaseRepository::getKeyPluck($brands_list, 'brand_id');

                $keyword = $this->dscRepository->mysqlLikeQuote($keyword);

                $result = SearchKeyword::query()->distinct()->select('keyword')
                    ->where(function ($query) use ($keyword) {
                        $query->where('keyword', 'like', '%' . $keyword . '%')
                            ->orWhere('pinyin_keyword', 'like', '%' . $keyword . '%');
                    })
                    ->orderBy('result_count', 'desc')
                    ->take(10);
                $result = BaseRepository::getToArrayGet($result);

                //查询分类
                $cate_res = Category::whereRaw("cat_name LIKE '%" . mysql_like_quote($keyword) . "%' OR pinyin_keyword LIKE '%" . mysql_like_quote($keyword) . "%'");

                if ($children) {
                    $cate_res = $cate_res->whereIn('cat_id', $children);
                }

                $cate_res = $cate_res->take(4);

                $cate_res = BaseRepository::getToArrayGet($cate_res);

                $cat_html = '';
                if ($cate_res) {
                    foreach ($cate_res as $key => $row) {
                        if ($row['parent_id'] > 0) {
                            $cat_name = Category::where('cat_id', $row['parent_id'])->value('cat_name');

                            $url = $this->dscRepository->buildUri('category', ['cid' => $row['cat_id']], $row['cat_name']);
                            if ($url == "") {
                                $url = '#';
                            }
                            $cat_html .= '<li onmouseover="_over(this);" onmouseout="_out(this);">' . "&nbsp;&nbsp;&nbsp;在<a class='cate_user' href=" . $url . " style='color:#ec5151;'>" . $cat_name . ">" . $row['cat_name'] . "</a>" . $GLOBALS['_LANG']['cat_search'] . '</li>';
                        }
                    }
                }

                $html = '<ul id="suggestions_list_id"><input type="hidden" value="1" name="selectKeyOne" id="keyOne" />';
                $res_num = 0;
                $exist_keyword = [];
                if ($result) {

                    foreach ($result as $row) {
                        $insert_keyword = addslashes(trim($row['keyword']));

                        // 关键词分词
                        $arr_keyword = CommonRepository::scwsWord($insert_keyword);

                        if ($arr_keyword) {
                            foreach ($arr_keyword as $k => $v) {

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
                                    unset($arr_keyword[$k]);
                                }
                            }

                            $arr_keyword = $arr_keyword ? array_values($arr_keyword) : [];
                        }

                        /* 获得符合条件的商品总数 */
                        $count = $this->searchService->getSearchGoodsCount($category, $brand_id, $children, $area_id, $area_city, 0, 0, [], [], [], $arr_keyword);

                        /* 补充搜索条件 by wu end */

                        //如果查询的数量为空则不显示此关键词
                        if ($count <= 0) {
                            continue;
                        }

                        $keyword = preg_quote($keyword); //特殊字符自动添加转义符\
                        $keyword_style = preg_replace("/($keyword)/i", "<font style='font-weight:normal;color:#ec5151;'>$1</font>", $row['keyword']);
                        $html .= '<li onmouseover="_over(this);" title="' . $row['keyword'] . '" onmouseout="_out(this);" onClick="javascript:fill(\'' . $row['keyword'] . '\');"><div class="left-span">&nbsp;' . $keyword_style . '</div><div class="suggest_span">约' . $count . '个商品</div></li>';
                        $res_num++;
                        $exist_keyword[] = $row['keyword'];
                    }
                }

                if (isset($cat_html) && $cat_html != "") {
                    $html .= $cat_html;
                    $html .= '<li style="height:1px; overflow:hidden; border-bottom:1px #eee solid; margin-top:-1px;"></li>';
                    unset($cat_html);
                }

                //查询商品关键字
                if ($res_num < 10) {

                    $keyword_res = Goods::query()->distinct()->select('goods_id', 'goods_name')
                        ->where('is_delete', 0)
                        ->where('is_on_sale', 1)
                        ->where('is_alone_sale', 1);

                    if ($brand_id) {
                        $keyword_res = $keyword_res->whereIn('brand_id', $brand_id);
                    }

                    if ($goodsKeyword) {
                        $keyword_res = $keyword_res->where(function ($query) use ($goodsKeyword) {
                            $query->where(function ($query) use ($goodsKeyword) {
                                foreach ($goodsKeyword as $key => $val) {
                                    $query->orWhere(function ($query) use ($val) {
                                        $val = $this->dscRepository->mysqlLikeQuote(trim($val));

                                        $query = $query->orWhere('goods_name', 'like', '%' . $val . '%');
                                        $query->orWhere('keywords', 'like', '%' . $val . '%');
                                    });
                                }

                                $keyword_goods_sn = $goodsKeyword[0] ?? '';

                                if ($keyword_goods_sn) {
                                    // 搜索商品货号
                                    $query->orWhere('goods_sn', 'like', '%' . $keyword_goods_sn . '%');
                                }
                            });
                        });
                    }

                    if (config('shop.review_goods')) {
                        $keyword_res = $keyword_res->whereIn('review_status', [3, 4, 5]);
                    }

                    $keyword_res = $this->dscRepository->getAreaLinkGoods($keyword_res, $area_id, $area_city);

                    $keyword_res = BaseRepository::getToArrayGet($keyword_res);

                    $res_count = count($keyword_res);
                    if ($res_count <= 0) {
                        $html .= '</ul>';

                        if ($html == '<ul id="suggestions_list_id"><input type="hidden" value="1" name="selectKeyOne" id="keyOne" /></ul>') {
                            $html = '';
                        }
                    }
                    $len = 10 - $res_num;

                    for ($i = 0; $i < $len; $i++) {
                        if ($res_count == $i) {
                            break;
                        }

                        if (in_array($keyword_res[$i]['goods_name'], $exist_keyword)) {
                            continue;
                        }

                        $keyword_new_name = $keyword_res[$i]['goods_name'];
                        $this->cut_str($keyword_new_name, 25);

                        $keyword_style = $keyword ? preg_replace("/($keyword)/i", "<font style='font-weight:normal;color:#ec5151;'>$1</font>", $keyword_new_name) : '';

                        if ($keyword_style) {
                            $html .= '<li onmouseover="_over(this);" onmouseout="_out(this);" title="' . $keyword_new_name . '" onClick="javascript:fill(\'' . $keyword_new_name . '\');"><div class="left-span">&nbsp;' . $keyword_style . '</div>&nbsp;<b>' . '</b></li>';
                        }
                    }
                }

                $html .= '</ul>';

                if ($html == '<ul id="suggestions_list_id"><input type="hidden" value="1" name="selectKeyOne" id="keyOne" /></ul>') {
                    $html = '';
                }
            } else {
                $html = '<ul id="suggestions_list_id"><input type="hidden" value="1" name="selectKeyOne" id="keyOne" />';

                $goodsList = Goods::select('goods_id', 'goods_name')->whereIn('goods_id', $goods_id);
                $goodsList = BaseRepository::getToArrayGet($goodsList);

                foreach ($goodsList as $key => $row) {
                    $keyword_new_name = $row['goods_name'];
                    $this->cut_str($keyword_new_name, 25);

                    $keyword_style = $keyword ? preg_replace("/($keyword)/i", "<font style='font-weight:normal;color:#ec5151;'>$1</font>", $keyword_new_name) : '';

                    if ($keyword_style) {
                        $html .= '<li onmouseover="_over(this);" data-id="' . $row['goods_id'] . '" id="click_id" onmouseout="_out(this);" title="' . $keyword_new_name . '" onClick="javascript:fill(\'' . $keyword_new_name . '\');"><div class="left-span">&nbsp;' . $keyword_style . '</div>&nbsp;<b>' . '</b></li>';
                    }
                }

                $html .= '</ul>';

                if ($html == '<ul id="suggestions_list_id"><input type="hidden" value="1" name="selectKeyOne" id="keyOne" /></ul>') {
                    $html = '';
                }
            }

            echo $html;
        }
    }

    /**
     *  截取指定的中英文字符的长度
     *
     *    指定字符串
     *    保留长度
     *    开始位置
     *    编码
     */
    private function cut_str($string, $sublen, $start = 0, $code = 'gbk')
    {
        if ($code == 'utf-8') {
            $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
            preg_match_all($pa, $string, $t_string);
            if (count($t_string[0]) - $start > $sublen) {
                return join('', array_slice($t_string[0], $start, $sublen)) . "...";
            }
            return join('', array_slice($t_string[0], $start, $sublen));
        } else {
            $start = $start * 2;
            $sublen = $sublen * 2;
            $strlen = strlen($string);
            $tmpstr = '';

            for ($i = 0; $i < $strlen; $i++) {
                if ($i >= $start && $i < ($start + $sublen)) {
                    if (ord(substr($string, $i, 1)) > 129) {
                        $tmpstr .= substr($string, $i, 2);
                    } else {
                        $tmpstr .= substr($string, $i, 1);
                    }
                }

                if (ord(substr($string, $i, 1)) > 129) {
                    $i++;
                }
            }
            //超出多余的字段就显示...

            if (strlen($tmpstr) < $strlen) {
                $tmpstr .= "";
            }

            return $tmpstr;
        }
    }
}
