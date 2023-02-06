<?php

namespace App\Services\Goods;

use App\Models\Attribute;
use App\Models\AttributeImg;
use App\Models\Category;
use App\Models\CollectGoods;
use App\Models\Comment;
use App\Models\DiscussCircle;
use App\Models\Goods;
use App\Models\GoodsActivity;
use App\Models\GoodsAttr;
use App\Models\GoodsConsumption;
use App\Models\GoodsExtend;
use App\Models\GoodsTransport;
use App\Models\GoodsTransportExpress;
use App\Models\GoodsTransportExtend;
use App\Models\GoodsTransportTpl;
use App\Models\GoodsUseLabel;
use App\Models\GoodsType;
use App\Models\MemberPrice;
use App\Models\PresaleActivity;
use App\Models\Products;
use App\Models\ProductsArea;
use App\Models\ProductsWarehouse;
use App\Models\VolumePrice;
use App\Models\WarehouseAreaAttr;
use App\Models\WarehouseAreaGoods;
use App\Models\WarehouseAttr;
use App\Models\WarehouseGoods;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;

class GoodsDataHandleService
{
    /**
     * @param array $id
     * @param array $data
     * @param int $limit
     * @param array $where
     * @return array
     */
    public static function getSellerGoodsDataList($id = [], $data = [], $limit = 0, $where = [])
    {
        $id = BaseRepository::getExplode($id);

        if (empty($id)) {
            return [];
        }

        $id = $id ? array_unique($id) : [];

        $data = $data ? $data : '*';

        $arr = [];
        foreach ($id as $k => $v) {
            $res = Goods::select($data)->where('user_id', $v);

            if ($where) {
                foreach ($where as $key => $val) {
                    if (is_array($val)) {
                        $res = $res->where($key, $val['condition'], $val['value']);
                    } else {
                        $res = $res->where($key, $val);
                    }
                }
            }

            if ($limit > 0) {
                $res = $res->take($limit);
            }
            $res = $res->orderBy('weights', 'DESC'); // 权重值

            $res = BaseRepository::getToArrayGet($res);


            if ($res) {
                foreach ($res as $key => $row) {
                    $arr[$row['user_id']][] = $row;
                }
            }
        }

        return $arr;
    }

    /**
     * 普通商品列表
     *
     * @param array $id
     * @param array $data
     * @param array $where
     * @return array
     */
    public static function GoodsDataList($id = [], $data = [], $where = [])
    {
        $id = BaseRepository::getExplode($id);

        if (empty($id)) {
            return [];
        }

        $id = $id ? array_unique($id) : [];

        $data = $data ? $data : '*';

        $res = Goods::select($data)->whereIn('goods_id', $id);

        if (!empty($where)) {
            $res = $res->where($where);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['goods_id']] = $row;
            }
        }

        return $arr;
    }

    /**
     * 商品扩展表
     *
     * @param array $goods_id
     * @return array
     */
    public static function goodsExtendList($goods_id = [])
    {
        $goods_id = BaseRepository::getExplode($goods_id);

        if (empty($goods_id)) {
            return [];
        }

        $goods_id = $goods_id ? array_unique($goods_id) : [];

        $res = GoodsExtend::select('goods_id', 'is_reality', 'is_return', 'is_fast')->whereIn('goods_id', $goods_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['goods_id']] = $row;
            }
        }

        return $arr;
    }

    /**
     * 普通商品列表
     *
     * @param array $goods_id
     * @param array $goodsWhere
     * @return array
     */
    public static function GoodsCartDataList($goods_id = [], $goodsWhere = [])
    {
        $goods_id = BaseRepository::getExplode($goods_id);

        if (empty($goods_id)) {
            return [];
        }

        $goods_id = $goods_id ? array_unique($goods_id) : [];

        $res = Goods::select($goodsWhere['goods_select']);

        $res = $res->whereIn('goods_id', $goods_id);

        if (isset($goodsWhere['type']) && isset($goodsWhere['presale']) && $goodsWhere['type'] == $goodsWhere['presale']) {
            $res = $res->where('is_on_sale', 0);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['goods_id']] = $row;
            }
        }

        return $arr;
    }

    /**
     * 普通商品优化金额列表
     *
     * @param $goods_id
     * @return array
     */
    public static function GoodsConsumptionDataList($goods_id = [])
    {
        $goods_id = BaseRepository::getExplode($goods_id);

        if (empty($goods_id)) {
            return [];
        }

        $goods_id = $goods_id ? array_unique($goods_id) : [];

        $res = GoodsConsumption::whereIn('goods_id', $goods_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['goods_id']][$key] = $row;
            }
        }

        return $arr;
    }


    /**
     * 会员等级价格
     *
     * @param array $goods_id
     * @param $user_rank
     * @return array
     */
    public static function goodsMemberPrice($goods_id = [], $user_rank = 0)
    {
        $goods_id = BaseRepository::getExplode($goods_id);

        if (empty($goods_id) || empty($user_rank)) {
            return [];
        }

        $goods_id = $goods_id ? array_unique($goods_id) : [];

        $res = MemberPrice::whereIn('goods_id', $goods_id);

        if (is_array($user_rank)) {
            $res = $res->whereIn('user_rank', $user_rank);
        } else {
            $res = $res->where('user_rank', $user_rank);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                if (is_array($user_rank)) {
                    $arr[$val['goods_id']][$val['user_rank']] = $val;
                } else {
                    $arr[$val['goods_id']] = $val;
                }
            }
        }

        return $arr;
    }

    /**
     * 仓库商品信息
     *
     * @param array $goods_id
     * @param int $warehouse_id
     * @param array $data
     * @return array
     */
    public static function getWarehouseGoodsDataList($goods_id = [], $warehouse_id = 0, $data = [])
    {
        $goods_id = BaseRepository::getExplode($goods_id);

        if (empty($goods_id)) {
            return [];
        }

        $goods_id = $goods_id ? array_unique($goods_id) : [];

        $data = $data ? $data : '*';

        $res = WarehouseGoods::select($data)->whereIn('goods_id', $goods_id);

        if ($warehouse_id > 0) {
            $res = $res->where('region_id', $warehouse_id);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                if ($warehouse_id > 0) {
                    $arr[$val['goods_id']] = $val;
                } else {
                    $arr[$val['goods_id']][] = $val;
                }
            }
        }

        return $arr;
    }

    /**
     * 仓库地区商品信息
     *
     * @param array $goods_id
     * @param int $area_id
     * @param int $area_city
     * @param array $data
     * @return array
     */
    public static function getWarehouseAreaGoodsDataList($goods_id = [], $area_id = 0, $area_city = 0, $data = [])
    {
        $goods_id = BaseRepository::getExplode($goods_id);

        if (empty($goods_id)) {
            return [];
        }

        $goods_id = $goods_id ? array_unique($goods_id) : [];

        $data = $data ? $data : '*';

        $res = WarehouseAreaGoods::select($data)->whereIn('goods_id', $goods_id);

        if ($area_id > 0) {
            $res = $res->where('region_id', $area_id);

            if (config('shop.area_pricetype') == 1) {
                $res = $res->where('city_id', $area_city);
            }
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                if ($area_id > 0) {
                    $arr[$val['goods_id']] = $val;
                } else {
                    $arr[$val['goods_id']][] = $val;
                }
            }
        }

        return $arr;
    }

    /**
     * 仓库商品货品信息
     *
     * @param array $id
     * @param array $data
     * @param string $field
     * @param int $limit
     * @return array
     */
    public static function getProductsDataList($id = [], $data = [], $field = 'goods_id', $limit = 0)
    {
        $id = BaseRepository::getExplode($id);

        if (empty($id)) {
            return [];
        }

        $id = $id ? array_unique($id) : [];

        $data = $data ? $data : '*';

        $res = Products::select($data);

        if (stripos($field, 'goods_id') !== false) {
            $res = $res->whereIn('goods_id', $id);
        } else {
            $res = $res->whereIn('product_id', $id);
        }

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                if (stripos($field, 'goods_id') !== false) {
                    $arr[$val['goods_id']][] = $val;
                } else {
                    $arr[$val['product_id']] = $val;
                }
            }
        }

        return $arr;
    }

    /**
     * 仓库商品货品信息
     *
     * @param array $id
     * @param int $warehouse_id
     * @param array $data
     * @param string $field
     * @param int $limit
     * @return array
     */
    public static function getProductsWarehouseDataList($id = [], $warehouse_id = 0, $data = [], $field = 'goods_id', $limit = 0)
    {
        $id = BaseRepository::getExplode($id);

        if (empty($id)) {
            return [];
        }

        $id = $id ? array_unique($id) : [];

        $data = $data ? $data : '*';

        $res = ProductsWarehouse::select($data);

        if (stripos($field, 'goods_id') !== false) {
            $res = $res->whereIn('goods_id', $id)->where('warehouse_id', $warehouse_id);
        } else {
            $res = $res->whereIn('product_id', $id);
        }

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                if (stripos($field, 'goods_id') !== false) {
                    $arr[$val['goods_id']][] = $val;
                } else {
                    $arr[$val['product_id']] = $val;
                }
            }
        }

        return $arr;
    }

    /**
     * 仓库地区商品货品信息
     *
     * @param array $id
     * @param int $area_id
     * @param int $area_city
     * @param array $data
     * @param string $field
     * @param int $limit
     * @return array
     */
    public static function getProductsAreaDataList($id = [], $area_id = 0, $area_city = 0, $data = [], $field = 'goods_id', $limit = 0)
    {
        $id = BaseRepository::getExplode($id);

        if (empty($id)) {
            return [];
        }

        $id = $id ? array_unique($id) : [];

        $data = $data ? $data : '*';

        $res = ProductsArea::select($data);

        if (stripos($field, 'goods_id') !== false) {
            $res = $res->whereIn('goods_id', $id)->where('area_id', $area_id);

            if (config('shop.area_pricetype') == 1) {
                $res = $res->where('city_id', $area_city);
            }
        } else {
            $res = $res->whereIn('product_id', $id);
        }

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                if (stripos($field, 'goods_id') !== false) {
                    $arr[$val['goods_id']][] = $val;
                } else {
                    $arr[$val['product_id']] = $val;
                }
            }
        }

        return $arr;
    }

    /**
     * 活动商品
     *
     * @param array $goods_id
     * @param int $act_type
     * @return array
     */
    public static function getGoodsActivityDataList($goods_id = [], $act_type = 0)
    {
        $goods_id = BaseRepository::getExplode($goods_id);

        if (empty($goods_id)) {
            return [];
        }

        $goods_id = $goods_id ? array_unique($goods_id) : [];

        $res = GoodsActivity::whereIn('act_id', $goods_id)
            ->where('act_type', $act_type);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['act_id']] = $val;
            }
        }

        return $arr;
    }

    /**
     * 获取商品评论列表
     *
     * @param array $goods_id
     * @param array $data
     * @return array
     */
    public static function CommentGoodsReviewCount($goods_id = [], $data = [])
    {
        $goods_id = BaseRepository::getExplode($goods_id);

        if (empty($goods_id)) {
            return [];
        }

        $goods_id = $goods_id ? array_unique($goods_id) : [];

        $data = $data ? $data : '*';

        $res = Comment::select($data)->addSelect('id_value')->whereIn('id_value', $goods_id)->where('status', 1)
            ->where('parent_id', 0)
            ->whereIn('comment_rank', [1, 2, 3, 4, 5]);
        $res = BaseRepository::getToArrayGet($res);

        return $res;
    }

    /**
     * 获取商品收藏列表
     *
     * @param array $goods_id
     * @param array $data
     * @return array
     */
    public static function CollectGoodsDataList($goods_id = [], $data = [])
    {
        $goods_id = BaseRepository::getExplode($goods_id);

        if (empty($goods_id)) {
            return [];
        }

        $goods_id = $goods_id ? array_unique($goods_id) : [];

        $data = $data ? $data : '*';

        $res = CollectGoods::select($data)->addSelect('goods_id')
            ->whereIn('goods_id', $goods_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['goods_id']] = $row;
            }
        }

        return $arr;
    }

    /**
     * 属性图片
     *
     * @param array $attr_id
     * @return array
     */
    public static function AttributeImgDataList($attr_id = [])
    {
        $attr_id = BaseRepository::getExplode($attr_id);

        if (empty($attr_id)) {
            return [];
        }

        $attr_id = $attr_id ? array_unique($attr_id) : [];

        $res = AttributeImg::whereIn('attr_id', $attr_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['attr_id']] = $val;
            }
        }

        return $arr;
    }

    /**
     * 获取商品属性类型分组
     *
     * @param $goods_id
     * @return array
     */
    public static function GoodsTypeDataList($goods_id)
    {
        $goods_id = BaseRepository::getExplode($goods_id);

        $goods_id = BaseRepository::getExplode($goods_id);

        if (empty($goods_id)) {
            return [];
        }

        $goods_id = $goods_id ? array_unique($goods_id) : [];

        $goods = Goods::select('goods_id', 'goods_type')
            ->whereIn('goods_id', $goods_id)
            ->pluck('goods_type');
        $goods_type = BaseRepository::getToArray($goods);

        $arr = [];
        if ($goods_type) {
            $goods_type = array_unique($goods_type);

            /* 对属性进行重新排序和分组 */
            $grp = GoodsType::select('cat_id', 'attr_group')->whereIn('cat_id', $goods_type);
            $grp = BaseRepository::getToArrayGet($grp);

            if (!empty($grp)) {
                if ($grp) {
                    foreach ($grp as $key => $val) {
                        $attr_group = preg_replace(['/\r\n/', '/\n/', '/\r/'], ",", $val['attr_group']); //替换空格回车换行符为英文逗号
                        $attr_group = explode(',', $attr_group);

                        $arr[$val['cat_id']] = $attr_group;
                    }
                }
            }
        }

        return $arr;
    }

    /**
     * 商品扩展表
     *
     * @param array $goods_id
     * @return array
     */
    public static function PresaleActivityDataList($goods_id = [])
    {
        $goods_id = BaseRepository::getExplode($goods_id);

        if (empty($goods_id)) {
            return [];
        }

        $goods_id = $goods_id ? array_unique($goods_id) : [];

        $res = PresaleActivity::whereIn('goods_id', $goods_id)
            ->orderBy('act_id', 'desc');
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['goods_id']][] = $row;
            }
        }

        return $arr;
    }

    /**
     * 获取分类信息
     *
     * @param array $cat_id
     * @return array
     */
    public static function getGoodsCategoryDataList($cat_id = [])
    {
        $cat_id = BaseRepository::getExplode($cat_id);

        if (empty($cat_id)) {
            return [];
        }

        $cat_id = $cat_id ? array_unique($cat_id) : [];


        $res = Category::whereIn('cat_id', $cat_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['cat_id']] = $row;
            }
        }

        return $arr;
    }

    /**
     * 普通属性列表
     *
     * @param array $attr_id
     * @param null $attr_type
     * @param array $data
     * @param int $limit
     * @return array
     */
    public static function getAttributeDataList($attr_id = [], $attr_type = null, $data = [], $limit = 0)
    {
        $attr_id = BaseRepository::getExplode($attr_id);

        if (empty($attr_id)) {
            return [];
        }

        $attr_id = $attr_id ? array_unique($attr_id) : [];

        $data = $data ? $data : '*';

        $res = Attribute::select($data)->whereIn('attr_id', $attr_id);

        if (!is_null($attr_type)) {
            $attr_type = BaseRepository::getExplode($attr_type);
            $res = $res->whereIn('attr_type', $attr_type);
        }

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = $res->orderBy('sort_order')
            ->orderBy('attr_id');

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['attr_id']] = $row;
            }
        }

        return $arr;
    }

    /**
     * 普通商品属性列表
     *
     * @param array $id
     * @param array $data
     * @param string $field
     * @param int $limit
     * @return array
     */
    public static function getGoodsAttrDataList($id = [], $data = [], $field = 'goods_attr_id', $limit = 0)
    {
        $id = BaseRepository::getExplode($id);

        if (empty($id)) {
            return [];
        }

        $id = $id ? array_unique($id) : [];

        $data = $data ? $data : '*';

        $res = GoodsAttr::select($data);

        if (stripos($field, 'goods_id') !== false) {
            $res = $res->whereIn('goods_id', $id);
        } else {
            $res = $res->whereIn('goods_attr_id', $id);
        }

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                if (stripos($field, 'goods_id') !== false) {
                    $arr[$row['goods_id']][] = $row;
                } else {
                    $arr[$row['goods_attr_id']] = $row;
                }
            }
        }

        return $arr;
    }

    /**
     * 普通商品仓库属性列表
     *
     * @param array $id
     * @param int $warehouse_id
     * @param array $data
     * @param string $field
     * @param int $limit
     * @return array
     */
    public static function getWarehouseAttrDataList($id = [], $warehouse_id = 0, $data = [], $field = 'goods_attr_id', $limit = 0)
    {
        $id = BaseRepository::getExplode($id);

        if (empty($id)) {
            return [];
        }

        $id = $id ? array_unique($id) : [];

        $data = $data ? $data : '*';

        $res = WarehouseAttr::select($data);

        if (stripos($field, 'goods_id') !== false) {
            $res = $res->whereIn('goods_id', $id);
        } else {
            $res = $res->whereIn('goods_attr_id', $id);
        }

        if ($warehouse_id) {
            $res = $res->where('warehouse_id', $warehouse_id);
        }

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                if (stripos($field, 'goods_id') !== false) {
                    $arr[$row['goods_id']][] = $row;
                } else {
                    $arr[$row['goods_attr_id']] = $row;
                }
            }
        }

        return $arr;
    }

    /**
     * 普通商品仓库地区属性列表
     *
     * @param array $id
     * @param int $area_id
     * @param int $area_city
     * @param array $data
     * @param string $field
     * @param int $limit
     * @return array
     */
    public static function getWarehouseAreaAttrDataList($id = [], $area_id = 0, $area_city = 0, $data = [], $field = 'goods_attr_id', $limit = 0)
    {
        $id = BaseRepository::getExplode($id);

        if (empty($id)) {
            return [];
        }

        $id = $id ? array_unique($id) : [];

        $data = $data ? $data : '*';

        $res = WarehouseAreaAttr::select($data);

        if (stripos($field, 'goods_id') !== false) {
            $res = $res->whereIn('goods_id', $id);
        } else {
            $res = $res->whereIn('goods_attr_id', $id);
        }

        if ($area_id > 0) {
            $res = $res->where('area_id', $area_id);

            if (config('shop.area_pricetype') == 1) {
                $res = $res->where('city_id', $area_city);
            }
        }

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                if (stripos($field, 'goods_id') !== false) {
                    $arr[$row['goods_id']][] = $row;
                } else {
                    $arr[$row['goods_attr_id']] = $row;
                }
            }
        }

        return $arr;
    }

    /**
     * 普通商品运费模板列表
     *
     * @param array $tid
     * @param array $data
     * @param int $limit
     * @return array
     */
    public static function getGoodsTransportDataList($tid = [], $data = [], $limit = 0)
    {
        $tid = BaseRepository::getExplode($tid);

        if (empty($tid)) {
            return [];
        }

        $tid = $tid ? array_unique($tid) : [];

        $data = $data ? $data : '*';

        $res = GoodsTransport::select($data)->whereIn('tid', $tid);

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['tid']] = $row;
            }
        }

        return $arr;
    }

    /**
     * 取得商品优惠价格列表
     *
     * @param int $goods_id
     * @param int $price_type
     * @param int $is_pc
     * @return array
     * @throws \Exception
     */
    public static function getVolumePriceDataList($goods_id = 0, $price_type = 1, $is_pc = 0)
    {
        if (empty($goods_id)) {
            return [];
        }

        $goods_id = BaseRepository::getExplode($goods_id);

        $res = VolumePrice::whereIn('goods_id', $goods_id)
            ->where('price_type', $price_type);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['goods_id']][] = $row;
            }
        }

        $list = [];
        if ($arr) {
            foreach ($arr as $key => $val) {
                $goods = BaseRepository::getSortBy($val, 'volume_number');
                $res_count = count($goods);
                foreach ($goods as $k => $v) {
                    $list[$v['goods_id']][$k]['id'] = $v['id'];
                    $list[$v['goods_id']][$k]['price'] = $v['volume_price'];
                    $list[$v['goods_id']][$k]['format_price'] = app(DscRepository::class)->getPriceFormat($v['volume_price']);
                    //pc前台显示区分阶梯价格
                    if ($is_pc > 0) {
                        if (($res_count - 1) > $k) {
                            $list[$v['goods_id']][$k]['number'] = $v['volume_number'] . '-' . ($goods[$k + 1]['volume_number'] - 1);
                        } else {
                            $list[$v['goods_id']][$k]['number'] = $v['volume_number'] . lang('common.and_more');
                        }
                    } else {
                        $list[$v['goods_id']][$k]['number'] = $v['volume_number'];
                    }
                }
            }
        }

        return $list;
    }

    /**
     * 自定义
     *
     * @param array $tid
     * @param array $data
     * @param array $ru_id
     * @return array
     */
    public static function getGoodsTransportTplDataList($tid = [], $data = [], $ru_id = [])
    {
        $tid = BaseRepository::getExplode($tid);
        $ru_id = BaseRepository::getExplode($ru_id);

        if (empty($tid) && empty($ru_id)) {
            return [];
        }

        $tid = $tid ? array_unique($tid) : [];
        $ru_id = $ru_id ? array_unique($ru_id) : [];

        $data = $data ? $data : '*';

        $res = GoodsTransportTpl::select($data);

        if ($tid) {
            $res = $res->whereIn('tid', $tid);
        }

        if ($ru_id) {
            $res = $res->whereIn('user_id', $ru_id);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                if ($tid) {
                    $arr[$row['id']] = $row;
                }
            }
        }

        return $arr;
    }

    /**
     * 运费模板自定义地区运费列表
     *
     * @param array $tid
     * @param array $data
     * @param array $ru_id
     * @return array
     */
    public static function getGoodsTransportExtendDataList($tid = [], $data = [], $ru_id = [])
    {
        $tid = BaseRepository::getExplode($tid);
        $ru_id = BaseRepository::getExplode($ru_id);

        if (empty($tid) && empty($ru_id)) {
            return [];
        }

        $tid = $tid ? array_unique($tid) : [];
        $ru_id = $ru_id ? array_unique($ru_id) : [];

        $data = $data ? $data : '*';

        $res = GoodsTransportExtend::select($data)->addSelect('id');

        if ($tid) {
            $res = $res->whereIn('tid', $tid);
        }

        if ($ru_id) {
            $res = $res->whereIn('ru_id', $ru_id);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                if ($tid) {
                    $arr[$row['id']] = $row;
                }

                if (empty($tid) && $ru_id) {
                    $arr[$row['ru_id']][] = $row;
                }
            }
        }

        return $arr;
    }

    /**
     * 运费模板自定义快递方式列表
     *
     * @param array $tid
     * @param array $data
     * @param array $ru_id
     * @return array
     */
    public static function getGoodsTransportExpressDataList($tid = [], $data = [], $ru_id = [])
    {

        $tid = BaseRepository::getExplode($tid);
        $ru_id = BaseRepository::getExplode($ru_id);

        if (empty($tid) && empty($ru_id)) {
            return [];
        }

        $tid = $tid ? array_unique($tid) : [];
        $ru_id = $ru_id ? array_unique($ru_id) : [];

        $data = $data ? $data : '*';

        $res = GoodsTransportExpress::select($data);

        if ($tid) {
            $res = $res->whereIn('tid', $tid);
        }

        if ($ru_id) {
            $res = $res->whereIn('ru_id', $ru_id);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                if ($tid) {
                    $arr[$row['tid']][] = $row;
                }

                if (empty($tid) && $ru_id) {
                    $arr[$row['ru_id']][] = $row;
                }
            }
        }

        return $arr;
    }

    /**
     * 查询商品评论数
     *
     * @param array $goods_id
     * @param array $data
     * @return array
     */
    public static function getGoodsCommentDataList($goods_id = [], $data = [])
    {
        $goods_id = BaseRepository::getExplode($goods_id);

        if (empty($goods_id)) {
            $goods_id = [];
        }

        $data = $data ? $data : '*';

        $res = Comment::select($data)->where('id_value', $goods_id)
            ->where('comment_type', 0)
            ->where('status', 1)
            ->where('parent_id', 0)
            ->where('add_comment_id', 0);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['id_value']][] = $val;
            }
        }

        return $arr;
    }

    /**
     * 商品贴列表
     *
     * @param array $goods_id
     * @param array $data
     * @return array
     */
    public static function getGoodsDiscussTypeDataList($goods_id = [], $data = [])
    {
        $goods_id = BaseRepository::getExplode($goods_id);

        if (empty($goods_id)) {
            $goods_id = [];
        }

        $data = $data ? $data : '*';

        $res = DiscussCircle::select($data)->where('parent_id', 0)
            ->where('review_status', 3)
            ->whereIn('goods_id', $goods_id);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['goods_id']][] = $val;
            }
        }

        return $arr;
    }

    /**
     * 商家可用商品活动标签列表
     *
     * @param array $goods_id
     * @param int $type 标签类型 -1 所有 0 通用标签 1 悬浮标签
     * @return array
     */
    public static function gettMerchantUseGoodsLabelDataList($goods_id = [], $type = -1)
    {
        $goods_id = BaseRepository::getExplode($goods_id);

        if (empty($goods_id)) {
            $goods_id = [];
        }

        $res = GoodsUseLabel::whereIn('goods_id', $goods_id);

        $res = $res->whereHasIn('getGoodsLabel', function ($query) use ($type) {
            $query = $query->where('status', 1)->where('merchant_use', 1);
            if ($type > -1) {
                $query->where('type', $type);
            }
        });

        $res = $res->with('getGoodsLabel');

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            $dscRepository = app(DscRepository::class);
            foreach ($res as $key => $val) {
                $val = collect($val)->merge($val['get_goods_label'])->except('get_goods_label')->all();
                $val['formated_label_image'] = $dscRepository->getImagePath($val['label_image']);
                $arr[$val['goods_id']][] = $val;
            }
        }

        return $arr;
    }

    /**
     * 商家不可用商品活动标签列表
     *
     * @param array $goods_id
     * @param int $type 标签类型 -1 所有 0 通用标签 1 悬浮标签
     * @return array
     */
    public static function getMerchantNoUseGoodsLabelDataList($goods_id = [], $type = -1)
    {
        $goods_id = BaseRepository::getExplode($goods_id);

        if (empty($goods_id)) {
            $goods_id = [];
        }

        $res = GoodsUseLabel::whereIn('goods_id', $goods_id);

        $res = $res->whereHasIn('getGoodsLabel', function ($query) use ($type) {
            $query = $query->where('status', 1)->where('merchant_use', 0);
            if ($type > -1) {
                $query->where('type', $type);
            }
        });

        $res = $res->with('getGoodsLabel');

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            $dscRepository = app(DscRepository::class);
            foreach ($res as $key => $val) {
                $val = collect($val)->merge($val['get_goods_label'])->except('get_goods_label')->all();
                $val['formated_label_image'] = $dscRepository->getImagePath($val['label_image']);
                $arr[$val['goods_id']][] = $val;
            }
        }

        return $arr;
    }
}
