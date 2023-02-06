<?php

namespace App\Services\Category;

use App\Models\Attribute;
use App\Models\Goods;
use App\Models\GoodsAttr;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;

class CategoryAttributeService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    ) {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 获得分类商品属性类型
     *
     * @param int $attr_id
     * @return mixed
     */
    public function getCatAttribute($attr_id = 0)
    {
        $res = Attribute::select('attr_name', 'attr_cat_type')->where('attr_id', $attr_id);
        $res = BaseRepository::getToArrayFirst($res);

        return $res;
    }

    /**
     * 获得分类商品属性规格
     *
     * @param int $attr_id
     * @param array $children
     * @param string $brand_id
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param array $keywords
     * @param array $presale_goods_id
     * @param string $cat_type
     * @return mixed
     */
    public function getCatAttributeAttrList($attr_id = 0, $children = [], $brand_id = '', $warehouse_id = 0, $area_id = 0, $area_city = 0, $keywords = [], $presale_goods_id = [], $cat_type = 'cat_id')
    {
        $brand_id = BaseRepository::getExplode($brand_id);

        $arr = [
            'warehouse_id' => $warehouse_id,
            'area_id' => $area_id,
            'area_city' => $area_city,
            'children' => $children,
            'keywords' => $keywords,
            'presale_goods_id' => $presale_goods_id,
            'cat_type' => $cat_type
        ];

        $goods = Goods::select('goods_id')->where('is_on_sale', 1)
            ->where('is_alone_sale', 1)
            ->where('is_delete', 0);

        if ($brand_id) {
            $goods = $goods->whereIn('brand_id', $brand_id);
        }

        $goods = $goods->where(function ($query) use ($arr) {
            $query->orWhere(function ($query) use ($arr) {
                if ($arr['keywords']) {
                    $query->where(function ($query) use ($arr) {
                        $query->orWhere(function ($query) use ($arr) {
                            $query->where(function ($query) use ($arr) {
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
                        });


                        //兼容预售
                        if ($arr['keywords'] && isset($arr['presale_goods_id']) && $arr['presale_goods_id']) {
                            $query->orWhere(function ($query) use ($arr) {
                                $query->where(function ($query) use ($arr) {
                                    $query->where('is_on_sale', 0)
                                        ->whereIn('goods_id', $arr['presale_goods_id']);
                                });
                            });
                        }
                    });
                }
            });
        });

        $goods = $this->dscRepository->getAreaLinkGoods($goods, $arr['area_id'], $arr['area_city']);

        if (config('shop.review_goods')) {
            $goods = $goods->whereIn('review_status', [3, 4, 5]);
        }

        $goods = $goods->where(function ($query) use ($arr) {
            $query = $query->whereIn($arr['cat_type'], $arr['children']);

            if ($arr['cat_type'] == 'cat_id') {
                $query->orWhere(function ($query) use ($arr) {
                    $query->whereHasIn('getGoodsCat', function ($query) use ($arr) {
                        $query->where('cat_id', $arr['children']);
                    });
                });
            }
        });

        $goods_id = $goods->pluck('goods_id');
        $goods_id = BaseRepository::getToArray($goods_id);
        $goods_id = BaseRepository::getTake($goods_id, 1000);

        $res = GoodsAttr::selectRaw('goods_id, attr_id, goods_attr_id, attr_value, color_value')
            ->where('attr_id', $attr_id)
            ->whereIn('goods_id', $goods_id);

        $res = $res->orderBy('attr_sort', 'desc')
            ->orderBy('goods_attr_id', 'desc');

        $res = BaseRepository::getToArrayGet($res);

        return $res;
    }
}
