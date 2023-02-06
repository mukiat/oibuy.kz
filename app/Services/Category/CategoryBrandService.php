<?php

namespace App\Services\Category;

use App\Models\Brand;
use App\Models\Goods;
use App\Models\GoodsCat;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Ads\AdsService;

class CategoryBrandService
{
    protected $dscRepository;
    protected $adsService;

    public function __construct(
        DscRepository $dscRepository,
        AdsService $adsService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->adsService = $adsService;
    }

    /**
     * 获得分类下的品牌
     *
     * @param array $children
     * @param string $keywords
     * @param number $ru_id
     * @return array
     */
    public function getCategoryFilterBrandList($children = [], $keywords = '', $ru_id = -1)
    {
        $children = BaseRepository::getExplode($children);

        /* 查询分类商品数据 */
        $res = Goods::select('goods_id', 'goods_name', 'brand_id')
            ->where('is_on_sale', 1)
            ->where('is_alone_sale', 1)
            ->where('goods_number', '>', 0)
            ->where('is_delete', 0);

        if (config('shop.review_goods')) {
            $res = $res->whereIn('review_status', [3, 4, 5]);
        }

        if ($keywords) {
            $res = $res->where('goods_name', 'like', '%' . $keywords . '%');
        }

        if ($ru_id >= 0) {
            $res = $res->where('user_id', $ru_id);
        }

        if ($children) {
            $res = $res->whereIn('cat_id', $children);
        }

        $res = $res->with(['getBrand' => function ($query) {
            $query->where('is_show', 1)
                ->where('is_delete', 0)
                ->where('audit_status', 1);
        }]);

        $res = $res->groupBy('brand_id');

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $k => $v) {
                if ($v['get_brand']) {
                    $brand = $v['get_brand'];
                    $arr[$k]['brand_id'] = $brand['brand_id'];
                    $arr[$k]['brand_name'] = $brand['brand_name'];
                }
            }

            $arr = array_values($arr);
        }

        return $arr;
    }

    /**
     * 获得分类品牌
     *
     * @param array $brand_id
     * @param array $children
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param array $keywords
     * @param array $presale_goods_id
     * @param array $sort
     * @param string $order
     * @param int $size
     * @param string $cat_type
     * @param int $merchant_id
     * @return mixed
     */
    public function getCatBrand($brand_id = [], $children = [], $warehouse_id = 0, $area_id = 0, $area_city = 0, $keywords = [], $presale_goods_id = [], $sort = [], $order = 'asc', $size = 0, $cat_type = 'cat_id', $merchant_id = 0)
    {
        $brand_id = BaseRepository::getExplode($brand_id);

        $keyword_brand_id = [];
        if (empty($brand_id)) {

            if (!empty($keywords)) {
                $brand = Brand::query()->select('brand_id');

                foreach ($keywords as $key => $val) {
                    $val = $this->dscRepository->mysqlLikeQuote(trim($val));
                    $brand->orWhere('brand_name', $val);
                }

                $brand = $brand->pluck('brand_id');

                $keyword_brand_id = BaseRepository::getToArray($brand);
            }

            if (empty($keyword_brand_id)) {
                $extension_goods = [];
                if ($children) {
                    $extension_goods = GoodsCat::select('goods_id')->whereIn('cat_id', $children);
                    $extension_goods = BaseRepository::getToArrayGet($extension_goods);
                    $extension_goods = BaseRepository::getFlatten($extension_goods);
                }

                $arr = [
                    'children' => $children,
                    'keywords' => $keywords,
                    'presale_goods_id' => $presale_goods_id,
                    'cat_type' => $cat_type,
                    'merchant_id' => $merchant_id,
                    'warehouse_id' => $warehouse_id,
                    'area_id' => $area_id,
                    'area_city' => $area_city,
                    'extension_goods' => $extension_goods
                ];

                $goods = Goods::query()->select('brand_id')
                    ->where('is_alone_sale', 1)
                    ->where('is_delete', 0)
                    ->where('is_show', 1);

                if ($arr['merchant_id'] > 0) {
                    $goods = $goods->where('user_id', $arr['merchant_id']);
                }

                $goods = $goods->where(function ($query) use ($arr) {
                    $query->orWhere(function ($query) use ($arr) {

                        $query->where(function ($query) use ($arr) {
                            $query->orWhere(function ($query) use ($arr) {
                                $query = $query->where('is_on_sale', 1);

                                $query->where(function ($query) use ($arr) {
                                    foreach ($arr['keywords'] as $key => $val) {
                                        $query->orWhere(function ($query) use ($val) {
                                            $val = $this->dscRepository->mysqlLikeQuote(trim($val));

                                            $query = $query->orWhere('goods_name', 'like', '%' . $val . '%');
                                            $query->orWhere('keywords', 'like', '%' . $val . '%');
                                        });
                                    }

                                    $keyword_goods_sn = $arr['keywords'][0] ?? '';

                                    if ($keyword_goods_sn) {
                                        // 搜索商品货号
                                        $query->orWhere('goods_sn', 'like', '%' . $keyword_goods_sn . '%');
                                    }
                                });
                            });


                            //兼容预售
                            if ($arr['keywords'] && isset($arr['presale_goods_id']) && $arr['presale_goods_id']) {
                                $query->orWhere(function ($query) use ($arr) {
                                    $query->where('is_on_sale', 0)
                                        ->whereIn('goods_id', $arr['presale_goods_id']);
                                });
                            }
                        });
                    });
                });

                /* 关联地区 */
                $goods = $this->dscRepository->getAreaLinkGoods($goods, $arr['area_id'], $arr['area_city']);

                if (!empty($children)) {
                    $goods = $goods->whereIn($arr['cat_type'], $children);
                }

                if ($arr['extension_goods']) {
                    $goods = $goods->orWhere(function ($query) use ($arr) {
                        $query->whereIn('goods_id', $arr['extension_goods']);
                    });
                }

                if (config('shop.review_goods')) {
                    $goods = $goods->whereIn('review_status', [3, 4, 5]);
                }

                $brand_id = $goods->pluck('brand_id');
                $brand_id = BaseRepository::getToArray($brand_id);
                $brand_id = $brand_id ? array_unique($brand_id) : [];

                if (empty($brand_id)) {
                    return [];
                }
            }
        }

        $res = Brand::where('is_show', 1);

        if ($keyword_brand_id) {
            $res = $res->whereIn('brand_id', $keyword_brand_id);
        } else {
            $res = $res->whereIn('brand_id', $brand_id);
        }

        if ($sort) {
            if (is_array($sort)) {
                $sort = implode(",", $sort);
                $res = $res->orderByRaw($sort . " " . $order);
            } else {
                $res = $res->orderBy($sort, $order);
            }
        }

        if ($size) {
            $res = $res->take($size);
        }

        $res = BaseRepository::getToArrayGet($res);
        $res = BaseRepository::getArrayUnique($res);

        if ($res) {
            foreach ($res as $key => $val) {
                $res[$key] = $val;
            }
        }

        $arr = [];
        if ($keyword_brand_id) {
            $arr['is_keyword_brand'] = 1;
        } else {
            $arr['is_keyword_brand'] = 0;
        }

        $arr['brand_list'] = $res;

        return $arr;
    }

    /**
     * 获得分类商品属性规格
     *
     * @param int $cat_id
     * @param array $children
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @return bool|\Illuminate\Cache\CacheManager|mixed
     * @throws \Exception
     */
    public function getCategoryBrandsAd($cat_id = 0, $children = [], $warehouse_id = 0, $area_id = 0, $area_city = 0)
    {
        $cache_name = "get_category_brands_ad_" . '_' . $cat_id . '_' . $warehouse_id . '_' . $area_id . '_' . $area_city;
        $arr = cache($cache_name);
        $arr = !is_null($arr) ? $arr : false;

        if ($arr === false) {
            $arr['ad_position'] = '';
            $arr['brands'] = '';

            $cat_name = '';
            for ($i = 1; $i <= config('shop.auction_ad'); $i++) {
                $cat_name .= "'cat_tree_" . $cat_id . "_" . $i . "',";
            }

            $cat_name = substr($cat_name, 0, -1);
            $arr['ad_position'] = $this->adsService->getAdPostiChild($cat_name);

            // 获取分类下品牌
            $sort = ['sort_order', 'brand_id'];
            $keywordBrand = $this->getCatBrand([], $children, $warehouse_id, $area_id, $area_city, [], [], $sort, 'asc', 20);
            $brands = $keywordBrand['brand_list'];

            if ($brands) {
                foreach ($brands as $key => $val) {
                    $temp_key = $key;
                    $brands[$temp_key]['brand_name'] = $val['brand_name'];
                    $brands[$temp_key]['url'] = route('category', ['id' => $cat_id, 'brand' => $val['brand_id']]);

                    $brands[$temp_key]['brand_logo'] = $this->dscRepository->getImagePath(DATA_DIR . '/brandlogo/' . $val['brand_logo']);

                    // 判断品牌是否被选中
                    $brands[$temp_key]['selected'] = 0;
                }
            }

            $arr['brands'] = $brands;

            cache()->forever($cache_name, $arr);
        }

        return $arr;
    }
}
