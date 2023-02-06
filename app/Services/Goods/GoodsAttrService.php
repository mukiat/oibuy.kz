<?php

namespace App\Services\Goods;

use App\Models\Attribute;
use App\Models\Goods;
use App\Models\GoodsAttr;
use App\Models\Products;
use App\Models\ProductsArea;
use App\Models\ProductsWarehouse;
use App\Models\StoreProducts;
use App\Models\WarehouseAreaAttr;
use App\Models\WarehouseAttr;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Erp\JigonManageService;

class GoodsAttrService
{
    protected $commonRepository;
    protected $dscRepository;

    public function __construct(
        CommonRepository $commonRepository,
        DscRepository $dscRepository
    )
    {
        $this->commonRepository = $commonRepository;
        $this->dscRepository = $dscRepository;
    }


    /**
     * 商品属性  名称查询
     *
     * @param int $attrId
     * @return mixed
     */
    public function getAttrNameById($attrId = 0)
    {
        $attrId = empty($attrId) ? [] : $attrId;

        if ($attrId) {
            $goodsAttr = GoodsAttr::whereRaw(1);

            if (is_array($attrId)) {
                $goodsAttr = $goodsAttr->wherein('goods_attr_id', $attrId);
                $goodsAttr = $goodsAttr->with([
                    'getGoodsAttribute'
                ]);

                $goodsAttr = BaseRepository::getToArrayGet($goodsAttr);

                if ($goodsAttr) {
                    foreach ($goodsAttr as $key => $value) {
                        $goodsAttr[$key]['attr_name'] = $value['get_goods_attribute']['attr_name'] ?? '';
                    }
                }
            } elseif (is_int($attrId)) {
                $goodsAttr = $goodsAttr->where('goods_attr_id', $attrId);
                $goodsAttr = $goodsAttr->with([
                    'getGoodsAttribute'
                ]);

                $goodsAttr = BaseRepository::getToArrayFirst($goodsAttr);

                if ($goodsAttr) {
                    $goodsAttr['attr_name'] = $goodsAttr['get_goods_attribute']['attr_name'] ?? '';
                }
            }
        } else {
            $goodsAttr = [];
        }

        return $goodsAttr;
    }


    /**
     * 查询商品属性
     * @param int $goods_id
     * @return array
     */
    public function goodsAttr($goods_id = 0)
    {

        /* 获得商品的规格 */
        $res = GoodsAttr::where('goods_id', $goods_id);

        $res = $res->with([
            'getGoodsAttribute'
        ]);

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $key => $val) {
                $attribute = $val['get_goods_attribute'];

                $res[$key]['attr_id'] = $attribute['attr_id'];
                $res[$key]['attr_name'] = $attribute['attr_name'];
                $res[$key]['attr_group'] = $attribute['attr_group'];
                $res[$key]['is_linked'] = $attribute['is_linked'];
                $res[$key]['attr_type'] = $attribute['attr_type'];
                $res[$key]['sort_order'] = $attribute['sort_order'];
                $res[$key]['attr_img_flie'] = $val['attr_img_flie'] ? $this->dscRepository->getImagePath($val['attr_img_flie']) : '';
            }
        }

        if (is_null($res)) {
            return [];
        }

        $result = [];
        foreach ($res as $key => $value) {
            $result[$value['attr_name']][] = $value;
        }

        $ret = [];
        foreach ($result as $key => $value) {
            array_push($ret, $value);
        }
        $arr = [];
        foreach ($ret as $k => $v) {
            $arr[$k]['attr_id'] = $v[0]['attr_id'];
            $arr[$k]['attr_name'] = $v[0]['attr_name'];
            $arr[$k]['sort_order'] = $v[0]['sort_order'];

            $v = BaseRepository::getSortBy($v, 'attr_sort');

            $arr[$k]['attr_key'] = $v;
        }

        $arr = BaseRepository::getSortBy($arr, 'sort_order');

        return $arr;
    }

    /**
     * 查询商品参数规格
     *
     * @param int $goods_id
     * @param array $columns
     * @return mixed
     */
    public function goodsAttrParameter($goods_id = 0, $columns = ['*'])
    {
        $res = GoodsAttr::where('goods_id', $goods_id);

        $res = $res->whereHasIn('getGoodsAttribute', function ($query) {
            $query->where('attr_type', 0);
        });

        $res = $res->with([
            'getGoodsAttribute'
        ]);

        $res = $res->select($columns)->orderBy('attr_sort');

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $key => $value) {
                $res[$key]['attr_name'] = $value['get_goods_attribute']['attr_name'] ?? '';
            }
        }

        return $res;
    }

    /**
     * 商品属性组
     *
     * @param int $goods_id
     * @return array
     */
    public function attrGroup($goods_id = 0)
    {
        $model = GoodsAttr::where('goods_id', $goods_id);

        $model = $model->with([
            'getGoodsType'
        ]);

        $model = BaseRepository::getToArrayFirst($model);

        $attr_group = '';
        if ($model) {
            $attr_group = $model['get_goods_type']['attr_group'] ?? '';
        }

        return $attr_group;
    }

    /**
     * 是否存在规格
     *
     * @param array $goods_attr_id_array
     * @return array|bool
     */
    public function is_spec($goods_attr_id_array = [])
    {
        if (empty($goods_attr_id_array)) {
            return false;
        }

        $goods_attr_id_array = BaseRepository::getExplode($goods_attr_id_array);

        $GoodsAttrList = GoodsAttr::select('attr_id')->whereIn('goods_attr_id', $goods_attr_id_array);
        $GoodsAttrList = BaseRepository::getToArrayGet($GoodsAttrList);

        $attr_id = BaseRepository::getKeyPluck($GoodsAttrList, 'attr_id');

        $attribute = Attribute::select('attr_id')->whereIn('attr_id', $attr_id)->where('attr_type', 1);
        $attribute = BaseRepository::getToArrayGet($attribute);

        if (empty($attribute)) {
            return false;
        }

        $attr_id = BaseRepository::getKeyPluck($attribute, 'attr_id');

        $sql = [
            'whereIn' => [
                [
                    'name' => 'attr_id',
                    'value' => $attr_id
                ]
            ]
        ];
        $res = BaseRepository::getArraySqlGet($GoodsAttrList, $sql);

        $count = BaseRepository::getArrayCount($res);

        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 取指定规格的货品信息
     *
     * @param $goods_id
     * @param $spec_goods_attr_id
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param int $store_id
     * @return array
     */
    public function getProductsInfo($goods_id, $spec_goods_attr_id, $warehouse_id = 0, $area_id = 0, $area_city = 0, $store_id = 0)
    {
        $model_attr = Goods::where('goods_id', $goods_id)->value('model_attr');

        $return_array = [];

        if (empty($spec_goods_attr_id) || empty($goods_id)) {
            return $return_array;
        }

        $goods_attr_array = $this->sortGoodsAttrIdArray($spec_goods_attr_id);

        if (isset($goods_attr_array['sort']) && $goods_attr_array['sort']) {
            if ($store_id > 0) {
                /* 门店商品 */
                $res = StoreProducts::where('goods_id', $goods_id)
                    ->where('store_id', $store_id);
            } else {
                /* 普通商品 */
                if ($model_attr == 1) {
                    $res = ProductsWarehouse::where('goods_id', $goods_id)
                        ->where('warehouse_id', $warehouse_id);
                } elseif ($model_attr == 2) {
                    $res = ProductsArea::where('goods_id', $goods_id)
                        ->where('area_id', $area_id);

                    if (config('shop.area_pricetype') == 1) {
                        $res = $res->where('city_id', $area_city);
                    }
                } else {
                    $res = Products::where('goods_id', $goods_id);
                }
            }

            if (!empty($goods_attr_array['sort'])) {
                //获取货品信息
                foreach ($goods_attr_array['sort'] as $key => $val) {
                    $res = $res->whereRaw("FIND_IN_SET('$val', REPLACE(goods_attr, '|', ','))");
                }
            }

            $res = $res->orderBy('product_id', 'desc');

            $return_array = BaseRepository::getToArrayFirst($res);

            //贡云商品 获取贡云货品库存
            if (!empty($return_array)) {
                if (isset($return_array['cloud_product_id']) && $return_array['cloud_product_id'] > 0) {
                    $return_array['product_number'] = app(JigonManageService::class)->jigonGoodsNumber(['cloud_product_id' => $return_array['cloud_product_id']]);
                }
            }
        }

        return $return_array;
    }

    /**
     * 将 goods_attr_id 的序列按照 attr_id 重新排序
     *
     * @param array $goods_attr_id_array
     * @param string $sort
     * @return array
     */
    public function sortGoodsAttrIdArray($goods_attr_id_array = [], $sort = 'asc')
    {
        if (empty($goods_attr_id_array)) {
            return $goods_attr_id_array;
        }

        $goods_attr_id_array = BaseRepository::getExplode($goods_attr_id_array);

        //重新排序
        $res = GoodsAttr::whereIn('goods_attr_id', $goods_attr_id_array);
        $res = BaseRepository::getToArrayGet($res);

        $return_arr = [];
        if ($res) {

            $attr_id = BaseRepository::getKeyPluck($res, 'attr_id');
            $AttributeList = GoodsDataHandleService::getAttributeDataList($attr_id, 1);

            $attr_id = BaseRepository::getKeyPluck($AttributeList, 'attr_id');

            $sql = [
                'whereIn' => [
                    [
                        'name' => 'attr_id',
                        'value' => $attr_id
                    ]
                ]
            ];
            $res = BaseRepository::getArraySqlGet($res, $sql);

            if (empty($res)) {
                return [];
            }

            foreach ($res as $key => $val) {
                $attribute = $AttributeList[$val['attr_id']] ?? [];

                $res[$key]['sort_order'] = $attribute['sort_order'] ?? 0;
                $res[$key]['attr_name'] = $attribute['attr_name'] ?? '';
                $res[$key]['attr_id'] = $attribute['attr_id'] ?? 0;
            }

            $res = BaseRepository::getSortBy($res, 'attr_sort', $sort);

            $return_arr = [
                'sort',
                'row'
            ];
            foreach ($res as $value) {

                $attribute = $AttributeList[$value['attr_id']] ?? [];
                $value['attr_type'] = $attribute['attr_type'] ?? 0;

                $return_arr['sort'][] = $value['goods_attr_id'] ?? 0;
                $return_arr['row'][$value['goods_attr_id']] = $value;
            }

            $return_arr['row'] = $return_arr['row'] ? BaseRepository::getSortBy($return_arr['row'], 'attr_sort') : [];
        }

        return $return_arr;
    }

    /**
     * 获得指定的规格的价格
     *
     * @param array $spec
     * @param int $goods_id
     * @param array $warehouse_area
     * @return float
     */
    public function specPrice($spec = [], $goods_id = 0, $warehouse_area = [])
    {
        if (!empty($spec)) {
            $time = TimeRepository::getGmTime();

            if (is_array($spec)) {
                foreach ($spec as $key => $val) {
                    $spec[$key] = addslashes($val);
                }
            } else {
                $spec = addslashes($spec);
            }

            $spec = BaseRepository::getExplode($spec);

            $warehouse_id = $warehouse_area['warehouse_id'] ?? 0;
            $area_id = $warehouse_area['area_id'] ?? 0;
            $area_city = $warehouse_area['area_city'] ?? 0;

            $goods = Goods::select('model_attr', 'is_promote', 'promote_start_date', 'promote_end_date')->where('goods_id', $goods_id);
            $goods = BaseRepository::getToArrayFirst($goods);

            $model_attr = $goods['model_attr'] ?? 0;

            // 是否处于促销期间
            $is_promote = 0;
            if ($goods && $goods['is_promote'] && $goods['promote_start_date'] < $time && $goods['promote_end_date'] > $time) {
                $is_promote = 1;
            }

            $attr['price'] = 0;

            if (config('shop.goods_attr_price') == 1) {
                $attr_type_spec = '';
                //去掉复选属性by wu start
                foreach ($spec as $key => $val) {

                    $attr_id = GoodsAttr::where('goods_id', $goods_id)->where('goods_attr_id', $val)->value('attr_id');
                    $attr_id = $attr_id ? $attr_id : 0;

                    $goods_attr_info = Attribute::select('attr_type')->where('attr_id', $attr_id);
                    $goods_attr_info = BaseRepository::getToArrayFirst($goods_attr_info);
                    $attr_type = $goods_attr_info['attr_type'] ?? 0;

                    if ($attr_type == 2 && $spec[$key]) {
                        $attr_type_spec .= $spec[$key] . ",";
                        unset($spec[$key]);
                    }
                }

                /* 复选价格 start */
                $attr_type_spec_price = 0;

                if ($attr_type_spec) {
                    $attr_type_spec_price = $this->getGoodsAttrPrice($goods_id, $model_attr, $attr_type_spec, $warehouse_id, $area_id, $area_city);
                }
                /* 复选价格 end */
                //去掉复选属性by wu end

                /* 判断是否存在货品信息 */
                if ($model_attr == 1) {
                    $price = ProductsWarehouse::where('goods_id', $goods_id)->where('warehouse_id', $warehouse_id);
                } elseif ($model_attr == 2) {
                    $price = ProductsArea::where('goods_id', $goods_id)->where('area_id', $area_id);

                    if (config('shop.area_pricetype') == 1) {
                        $price = $price->where('city_id', $area_city);
                    }
                } else {
                    $price = Products::where('goods_id', $goods_id);
                }

                //获取货品信息
                foreach ($spec as $key => $val) {
                    $price = $price->whereRaw("FIND_IN_SET('$val', REPLACE(goods_attr, '|', ','))");
                }

                $price = $price->orderBy('product_id', 'DESC');

                if ($is_promote > 0 && config('shop.add_shop_price') == 0) { // 当开启商品价格+货品价格模式时 属性促销价格不适用 调用属性货品价格
                    $price = $price->value('product_promote_price');
                } else {
                    $price = $price->value('product_price');
                }

                $price += $attr_type_spec_price;
            } else {
                $price = $this->getGoodsAttrPrice($goods_id, $model_attr, $spec, $warehouse_id, $area_id, $area_city);
            }
        } else {
            $price = 0;
        }

        return floatval($price);
    }

    /**
     * 获取单一属性价格
     *
     * @param $goods_id
     * @param $model_attr
     * @param $spec
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @return mixed
     */
    public function getGoodsAttrPrice($goods_id, $model_attr, $spec, $warehouse_id = 0, $area_id = 0, $area_city = 0)
    {
        $spec = $spec && !is_array($spec) ? explode(",", $spec) : [];

        if ($model_attr == 1) { //仓库属性
            $price = WarehouseAttr::where('goods_id', $goods_id)->where('warehouse_id', $warehouse_id);
        } elseif ($model_attr == 2) { //地区属性
            $price = WarehouseAreaAttr::where('goods_id', $goods_id)->where('area_id', $area_id);

            if (config('shop.area_pricetype') == 1) {
                $price = $price->where('city_id', $area_city);
            }
        } else {
            $price = GoodsAttr::where('goods_id', $goods_id);
        }

        if ($spec) {
            $price = $price->whereIn('goods_attr_id', $spec);
        }

        $price = $price->sum('attr_price');

        return $price;
    }

    /**
     * @param array $where_select 查询条件
     * @param int $attr_type 唯一属性、单选属性、复选属性
     * @param int $retuen_db 返回值模式（0-单条、1-单组、2-多组）
     * @return mixed
     */
    public function getGoodsAttrId($where_select = [], $attr_type = 0, $retuen_db = 1)
    {
        if (isset($where_select['goods_attr_id'])) {
            $where_select['goods_attr_id'] = BaseRepository::getExplode($where_select['goods_attr_id']);
        }

        if ($retuen_db == 2) {
            $res = Attribute::whereRaw(1);

            if ($attr_type) {
                if (is_array($attr_type)) {
                    $res = $res->whereIn('attr_type', $attr_type);
                } else {
                    $res = $res->where('attr_type', $attr_type);
                }
            }

            $res = $res->whereHasIn('getGoodsAttr', function ($query) use ($where_select) {
                if (isset($where_select['goods_id'])) {
                    $query = $query->where('goods_id', $where_select['goods_id']);
                }

                if (isset($where_select['attr_value']) && !empty($where_select['attr_value'])) {
                    $query = $query->where('attr_value', $where_select['attr_value']);
                }

                if (isset($where_select['attr_id']) && !empty($where_select['attr_id'])) {
                    $query = $query->where('attr_id', $where_select['attr_id']);
                }

                if (isset($where_select['goods_attr_id']) && !empty($where_select['goods_attr_id'])) {
                    $query = $query->whereIn('goods_attr_id', $where_select['goods_attr_id']);
                }

                if (isset($where_select['admin_id']) && !empty($where_select['admin_id'])) {
                    $query->where('admin_id', $where_select['admin_id']);
                }
            });

            $res = $res->with([
                'getGoodsAttrList' => function ($query) use ($where_select) {
                    if (isset($where_select['goods_id'])) {
                        $query = $query->where('goods_id', $where_select['goods_id']);
                    }

                    if (isset($where_select['attr_value']) && !empty($where_select['attr_value'])) {
                        $query = $query->where('attr_value', $where_select['attr_value']);
                    }

                    if (isset($where_select['attr_id']) && !empty($where_select['attr_id'])) {
                        $query = $query->where('attr_id', $where_select['attr_id']);
                    }

                    if (isset($where_select['goods_attr_id']) && !empty($where_select['goods_attr_id'])) {
                        $query = $query->whereIn('goods_attr_id', $where_select['goods_attr_id']);
                    }

                    if (isset($where_select['admin_id']) && !empty($where_select['admin_id'])) {
                        $query = $query->where('admin_id', $where_select['admin_id']);
                    }

                    $query->orderBy('goods_attr_id');
                }
            ]);

            $res = $res->orderByRaw('sort_order, attr_id asc');

            $res = BaseRepository::getToArrayGet($res);

            $list = [];
            if ($res) {
                foreach ($res as $key => $row) {
                    foreach ($row['get_goods_attr_list'] as $idx => $val) {
                        $goods_attr_id = $val['goods_attr_id'];

                        $list[$goods_attr_id] = $val;
                        $list[$goods_attr_id]['attr_img_flie'] = $val['attr_img_flie'] ? $this->dscRepository->getImagePath($val['attr_img_flie']) : '';
                        $list[$goods_attr_id]['attr_gallery_flie'] = $val['attr_gallery_flie'] ? $this->dscRepository->getImagePath($val['attr_gallery_flie']) : '';

                        if (empty($list[$goods_attr_id]['attr_img_flie'])) {
                            $list[$goods_attr_id]['attr_img_flie'] = $list[$goods_attr_id]['attr_gallery_flie'];
                        }

                        if (empty($list[$goods_attr_id]['attr_gallery_flie'])) {
                            $list[$goods_attr_id]['attr_gallery_flie'] = $list[$goods_attr_id]['attr_img_flie'];
                        }

                        $list[$goods_attr_id]['attr_id'] = $row['attr_id'];
                        $list[$goods_attr_id]['cat_id'] = $row['cat_id'];
                        $list[$goods_attr_id]['attr_name'] = $row['attr_name'];
                        $list[$goods_attr_id]['attr_cat_type'] = $row['attr_cat_type'];
                        $list[$goods_attr_id]['attr_input_type'] = $row['attr_input_type'];
                        $list[$goods_attr_id]['attr_type'] = $row['attr_type'];
                        $list[$goods_attr_id]['attr_values'] = $row['attr_values'];
                        $list[$goods_attr_id]['color_values'] = $row['color_values'];
                        $list[$goods_attr_id]['attr_index'] = $row['attr_index'];
                        $list[$goods_attr_id]['sort_order'] = $row['sort_order'];
                        $list[$goods_attr_id]['is_linked'] = $row['is_linked'];
                        $list[$goods_attr_id]['attr_group'] = $row['attr_group'];
                        $list[$goods_attr_id]['attr_input_category'] = $row['attr_input_category'];
                    }
                }
            }
        } elseif ($retuen_db == 1) {
            $res = GoodsAttr::whereRaw(1);

            if (isset($where_select['goods_id'])) {
                $res = $res->where('goods_id', $where_select['goods_id']);
            }

            if (isset($where_select['attr_value']) && !empty($where_select['attr_value'])) {
                $res = $res->where('attr_value', $where_select['attr_value']);
            }

            if (isset($where_select['attr_id']) && !empty($where_select['attr_id'])) {
                $res = $res->where('attr_id', $where_select['attr_id']);
            }

            if (isset($where_select['goods_attr_id']) && !empty($where_select['goods_attr_id'])) {
                $res = $res->whereIn('goods_attr_id', $where_select['goods_attr_id']);
            }

            if (isset($where_select['admin_id']) && !empty($where_select['admin_id'])) {
                $res = $res->where('admin_id', $where_select['admin_id']);
            }

            if ($attr_type) {
                $attr_type = $attr_type && !is_array($attr_type) ? explode(",", $attr_type) : $attr_type;

                $res = $res->whereHasIn('getGoodsAttribute', function ($query) use ($attr_type) {
                    if (is_array($attr_type)) {
                        $query->whereIn('attr_type', $attr_type);
                    } else {
                        $query->where('attr_type', $attr_type);
                    }
                });
            }

            $res = $res->with(['getGoodsAttribute']);

            $res = BaseRepository::getToArrayFirst($res);

            if ($res) {
                $attribute = $res['get_goods_attribute'];
                $res['attr_id'] = $attribute['attr_id'];
                $res['cat_id'] = $attribute['cat_id'];
                $res['attr_name'] = $attribute['attr_name'];
                $res['attr_cat_type'] = $attribute['attr_cat_type'];
                $res['attr_input_type'] = $attribute['attr_input_type'];
                $res['attr_type'] = $attribute['attr_type'];
                $res['attr_values'] = $attribute['attr_values'];
                $res['color_values'] = $attribute['color_values'];
                $res['attr_index'] = $attribute['attr_index'];
                $res['sort_order'] = $attribute['sort_order'];
                $res['is_linked'] = $attribute['is_linked'];
                $res['attr_group'] = $attribute['attr_group'];
                $res['attr_input_category'] = $attribute['attr_input_category'];
                $res['attr_img_flie'] = $res['attr_img_flie'] ? $this->dscRepository->getImagePath($res['attr_img_flie']) : '';
                $res['attr_gallery_flie'] = $res['attr_gallery_flie'] ? $this->dscRepository->getImagePath($res['attr_gallery_flie']) : '';

                if (empty($res['attr_img_flie'])) {
                    $res['attr_img_flie'] = $res['attr_gallery_flie'];
                }

                if (empty($res['attr_gallery_flie'])) {
                    $res['attr_gallery_flie'] = $res['attr_img_flie'];
                }
            }

            return $res;
        }
    }

    /**
     * 获取属性设置默认值(如果无后台设置默认属性则选择id小的属性作为默认checked)
     *
     * @param array $attr
     * @return array
     */
    public function get_attr_end_checked($attr = [])
    {
        $attr_str = [];
        if ($attr) {
            foreach ($attr as $z => $v) {
                $select_key = 0;
                foreach ($v['attr_key'] as $key => $val) {
                    if ($val['attr_checked'] == 1) {
                        $select_key = $key;
                        break;
                    }
                }
                //默认选择第一个属性为checked
                if ($select_key == 0) {
                    $attr[$z]['attr_key'][0]['attr_checked'] = 1;
                }

                $attr_str[] = $v['attr_key'][$select_key]['goods_attr_id'];
            }
            if ($attr_str) {
                sort($attr_str);
            }
        }
        return $attr_str;
    }

    /**
     * 获得商品的属性和规格
     *
     * @param $goods_id
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param string $goods_attr_id
     * @param null $attr_type
     * @param bool $is_implode
     * @return array|mixed
     */
    public function getGoodsProperties($goods_id, $warehouse_id = 0, $area_id = 0, $area_city = 0, $goods_attr_id = '', $attr_type = null, $is_implode = false)
    {
        $row = $this->goodsPropertiesList($goods_id, $warehouse_id, $area_id, $area_city, $attr_type, $is_implode, $goods_attr_id);
        $attr = $row[$goods_id] ?? [];

        $attr['pro'] = $attr['pro'] ?? [];
        $attr['spe'] = $attr['spe'] ?? [];
        $attr['lnk'] = $attr['lnk'] ?? [];

        return $attr;
    }


    /**
     * 获取商品属性
     *
     * @param array $goods_id 商品ID
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param null $attr_type 属性类型[0:唯一属性,1:单选属性,2:复选属性]
     * @param array $data 传具体字段
     * @param bool $is_implode 转换成字符串
     * @param string $goods_attr_id
     * @return array
     */
    public function goodsPropertiesList($goods_id = [], $warehouse_id = 0, $area_id = 0, $area_city = 0, $attr_type = null, $is_implode = false, $goods_attr_id = '', $data = [])
    {
        /* 属性分组 */
        $goodsTypeList = GoodsDataHandleService::GoodsTypeDataList($goods_id);

        $attr_array = [];
        if (!empty($goods_attr_id)) {
            $attr_array = BaseRepository::getExplode($goods_attr_id);
        }

        $goods_id = BaseRepository::getExplode($goods_id);

        if (empty($goods_id)) {
            return [];
        }

        $goods_id = $goods_id ? array_unique($goods_id) : [];

        $data = $data ? $data : '*';

        $goodsAttr = GoodsAttr::select($data)
            ->whereIn('goods_id', $goods_id);

        $goodsAttr = BaseRepository::getToArrayGet($goodsAttr);

        $list = [];
        if ($goodsAttr) {
            $arr = [];

            $attr_id = BaseRepository::getKeyPluck($goodsAttr, 'attr_id');

            $attr_list = Attribute::select('attr_id', 'cat_id', 'attr_name', 'attr_cat_type', 'attr_index', 'attr_input_type', 'attr_type', 'is_linked', 'attr_group', 'sort_order')
                ->whereIn('attr_id', $attr_id);

            if (!is_null($attr_type)) {
                $attr_type = BaseRepository::getExplode($attr_type);
                $attr_list = $attr_list->whereIn('attr_type', $attr_type);
            }

            $attr_list = BaseRepository::getToArrayGet($attr_list);

            $attribute_id = BaseRepository::getKeyPluck($attr_list, 'attr_id');

            $attributeImg = GoodsDataHandleService::AttributeImgDataList($attribute_id);

            $sql = [
                'whereIn' => [
                    [
                        'name' => 'attr_id',
                        'value' => $attribute_id
                    ]
                ]
            ];

            $goodsAttr = BaseRepository::getArraySqlGet($goodsAttr, $sql);

            $attribute = [];
            foreach ($attr_list as $key => $val) {
                $attribute[$val['attr_id']] = $val;
            }

            foreach ($goodsAttr as $key => $val) {
                if (isset($attribute[$val['attr_id']])) {
                    $arr[$val['goods_id']][$val['attr_id']]['attr_info'] = $attribute[$val['attr_id']];

                    /* 显示属性图片 */
                    if ($is_implode == false) {
                        $val['id'] = $val['goods_attr_id'];

                        $attr_price = $val['attr_price'];
                        $attr_price = abs($attr_price);

                        $val['price'] = $attr_price;
                        $val['format_price'] = $this->dscRepository->getPriceFormat($attr_price, false);

                        $attrImg = $attributeImg[$val['attr_id']] ?? [];

                        if ($val['attr_img_flie']) {
                            $val['img_flie'] = $this->dscRepository->getImagePath($val['attr_img_flie']);
                        } else {
                            $val['img_flie'] = $attrImg ? $this->dscRepository->getImagePath($attrImg['attr_img']) : '';
                        }

                        $val['attr_gallery_flie'] = $val['attr_gallery_flie'] ? $this->dscRepository->getImagePath($val['attr_gallery_flie']) : '';
                        $val['attr_img_flie'] = $val['attr_img_flie'] ? $this->dscRepository->getImagePath($val['attr_img_flie']) : '';

                        if (empty($val['attr_gallery_flie'])) {
                            $val['attr_gallery_flie'] = $val['attr_img_flie'];
                        }

                        if (empty($val['attr_img_flie'])) {
                            $val['attr_img_flie'] = $val['attr_gallery_flie'];
                        }

                        $val['combo_checked'] = CommonRepository::getComboGodosAttr($attr_array, $val['goods_attr_id']);

                        $val['label'] = $val['attr_value'];
                        $val['checked'] = $val['attr_checked'];
                    }

                    $arr[$val['goods_id']][$val['attr_id']]['list'][] = $val;
                }
            }

            if ($arr) {
                foreach ($arr as $idx => $val) {
                    $arr[$idx] = BaseRepository::keepSortKeys($val, 'attr_info', 'sort_order');

                    foreach ($val as $jdx => $row) {
                        $arr[$idx][$jdx]['attr_info'] = $row['attr_info'];
                        $arr[$idx][$jdx]['list'] = BaseRepository::keepSortKeys($row['list'], 'attr_sort');
                        $arr[$idx][$jdx]['list'] = array_values($arr[$idx][$jdx]['list']); // 重新排序
                    }
                }
            }

            $pro = 'pro';
            $lnk = 'lnk';
            $spe = 'spe';

            if ($arr) {
                $spe_type = [1, 2]; //
                foreach ($arr as $key => $val) {
                    $list[$key][$spe] = $list[$key][$spe] ?? [];
                    $list[$key][$pro] = $list[$key][$pro] ?? [];
                    $list[$key][$lnk] = $list[$key][$lnk] ?? [];

                    foreach ($val as $jdx => $row) {
                        /* 单选、复选属性列表 start */
                        if (in_array($row['attr_info']['attr_type'], $spe_type)) {
                            $list[$key][$spe][$jdx]['attr_id'] = $row['attr_info']['attr_id'];
                            $list[$key][$spe][$jdx]['name'] = $row['attr_info']['attr_name'];
                            $list[$key][$spe][$jdx]['attr_type'] = $row['attr_info']['attr_type'];

                            if ($is_implode == true) {
                                $list[$key][$spe][$jdx]['values'] = BaseRepository::getKeyPluck($row['list'], 'attr_value');
                                $list[$key][$spe][$jdx]['values'] = implode(',', $list[$key][$spe][$jdx]['values']);
                            } else {
                                $list[$key][$spe] = $list[$key][$spe] ?? [];

                                $list[$key][$spe][$jdx]['values'] = $row['list'];
                                $list[$key][$spe][$jdx]['is_checked'] = BaseRepository::getArraySum($row['list'], 'attr_checked');
                            }
                        } else {

                            /* 唯一属性 */
                            if ($row['attr_info']['attr_type'] == 0) {
                                $cat_id = $row['attr_info']['cat_id'];
                                $attr_id = $row['attr_info']['attr_id'];

                                /* 唯一属性 */
                                $groups = $goodsTypeList[$cat_id] ?? '';

                                $group = isset($groups[$row['attr_info']['attr_group']]) ? $groups[$row['attr_info']['attr_group']] : '';

                                $list[$key][$pro][$group][$attr_id]['name'] = $row['attr_info']['attr_name'];
                                $attr_value = BaseRepository::getKeyPluck($row['list'], 'attr_value');
                                $list[$key][$pro][$group][$attr_id]['value'] = implode(',', $attr_value);
                            }

                            /* 相同属性值的商品是否关联 */
                            if ($row['attr_info']['is_linked'] == 1) {
                                $list[$key][$lnk][$jdx]['name'] = $row['attr_info']['attr_name'];
                                $attr_value = BaseRepository::getKeyPluck($row['list'], 'attr_value');
                                $list[$key][$lnk][$jdx]['value'] = implode(',', $attr_value);
                            }
                        }
                        /* 单选、复选属性列表 end */
                    }
                }
            }
        }

        return $list;
    }

    /**
     * 获得指定的商品属性
     *
     * @param string $goods_attr_id 规格、属性ID数组
     * @param string $type 设置返回结果类型：pice，显示价格，默认；no，不显示价格
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @return mixed|string
     */
    public function getGoodsAttrInfo($goods_attr_id = '', $type = 'pice', $warehouse_id = 0, $area_id = 0, $area_city = 0)
    {
        $attr = '';

        if (!empty($goods_attr_id)) {
            if ($type == 'pice') {
                $fmt = "%s:%s[%s] \n";
            } else {
                $fmt = "%s:%s \n";
            }

            $goods_attr_id = BaseRepository::getExplode($goods_attr_id);

            $res = GoodsAttr::whereIn('goods_attr_id', $goods_attr_id);

            $res = $res->whereHasIn('getGoods');

            $res = $res->whereHasIn('getGoodsAttribute');

            $res = $res->with([
                'getGoods' => function ($query) {
                    $query->select('goods_id', 'model_attr');
                },
                'getGoodsAttribute' => function ($query) {
                    $query->select('attr_id', 'attr_name', 'sort_order');
                }
            ]);

            $res = BaseRepository::getToArrayGet($res);

            if ($res) {
                foreach ($res as $key => $row) {
                    $row = $row['get_goods'] ? array_merge($row, $row['get_goods']) : $row;
                    $row = $row['get_goods_attribute'] ? array_merge($row, $row['get_goods_attribute']) : $row;

                    if ($row['model_attr'] == 1) {
                        $attr_price = WarehouseAttr::where('goods_id', $row['goods_id'])
                            ->where('warehouse_id', $warehouse_id)
                            ->where('goods_attr_id', $row['goods_id'])->value('attr_price');
                    } elseif ($row['model_attr'] == 2) {
                        $attr_price = WarehouseAreaAttr::where('goods_id', $row['goods_id'])
                            ->where('area_id', $area_id);

                        if (config('shop.area_pricetype')) {
                            $attr_price = $attr_price->where('city_id', $area_city);
                        }

                        $attr_price = $attr_price->where('goods_attr_id', $row['goods_id'])
                            ->value('attr_price');
                    } else {
                        $attr_price = $row['attr_price'];
                    }

                    $row['attr_price'] = $attr_price ? $attr_price : 0;

                    $res[$key] = $row;
                }

                $res = BaseRepository::getSortBy($res, 'sort_order');

                foreach ($res as $row) {
                    if (config('shop.goods_attr_price') == 1) {
                        $attr_price = 0;
                    } else {
                        $attr_price = round(floatval($row['attr_price']), 2);
                        $attr_price = $this->dscRepository->getPriceFormat($attr_price, false);
                    }

                    if ($type == 'pice') {
                        $attr .= sprintf($fmt, $row['attr_name'], $row['attr_value'], $attr_price);
                    } else {
                        $attr .= sprintf($fmt, $row['attr_name'], $row['attr_value']);
                    }
                }

                $attr = str_replace('[0]', '', $attr);
            }
        }

        return $attr;
    }

    /**
     * 获取商品的默认属性价格
     *
     * @param string $goods_id 商品ID
     * @return mixed|string
     */
    public function getGoodsDefaultAttrPrice($goods_id)
    {
        $price = 0; // 初始化

        if (!empty($goods_id)) {
            /* 获得商品的规格 */
            $res = GoodsAttr::select('goods_attr_id', 'attr_checked', 'attr_id')->where('goods_id', $goods_id);

            $res = $res->whereHasIn('getGoodsAttribute');
            $res = $res->orderBy('attr_checked', 'DESC')->orderBy('goods_attr_id', 'ASC'); // 返回根据选中状态倒序，属性ID正序排列的属性数组
            $res = BaseRepository::getToArrayGet($res);

            if ($res) {
                $unique_arr = BaseRepository::getArrayUnique($res, 'attr_id'); // 去重，剩下排在第一位的默认属性
                $attr_ids = BaseRepository::getKeyPluck($unique_arr, 'goods_attr_id'); // 去出属性ID数组
                $price = $this->specPrice($attr_ids, $goods_id); // 返回属性价格
            }
        }

        return $price;
    }

    /**
     * 获取购物车商品属性图片
     *
     * @param array $goods_attr_id
     * @param array $productsGoodsAttrList
     * @param string $goods_thumb
     * @return string
     */
    public function cartGoodsAttrImage($goods_attr_id = [], $productsGoodsAttrList = [], $goods_thumb = '')
    {
        if ($goods_attr_id) {
            $productsGoodsAttrId = BaseRepository::getKeyPluck($productsGoodsAttrList, 'goods_attr_id');
            $productsGoodsAttrId = BaseRepository::getArrayUnique($productsGoodsAttrId);
            $productsIntersectId = BaseRepository::getArrayIntersect($goods_attr_id, $productsGoodsAttrId);

            $sql = [
                'whereIn' => [
                    [
                        'name' => 'goods_attr_id',
                        'value' => $productsIntersectId
                    ]
                ],
                'where' => [
                    [
                        'name' => 'attr_img_flie',
                        'value' => '',
                        'condition' => '<>' //条件查询
                    ]
                ]
            ];

            $goodsAttrInfo = BaseRepository::getArraySqlFirst($productsGoodsAttrList, $sql);

            if ($goodsAttrInfo) {

                $goodsAttrInfo['attr_gallery_flie'] = $goodsAttrInfo['attr_gallery_flie'] ? $this->dscRepository->getImagePath($goodsAttrInfo['attr_gallery_flie']) : '';
                $goodsAttrInfo['attr_img_flie'] = $goodsAttrInfo['attr_img_flie'] ? $this->dscRepository->getImagePath($goodsAttrInfo['attr_img_flie']) : '';

                if (empty($goodsAttrInfo['attr_gallery_flie'])) {
                    $goodsAttrInfo['attr_gallery_flie'] = $goodsAttrInfo['attr_img_flie'];
                }

                $goods_thumb = $goodsAttrInfo['attr_gallery_flie'] ? $goodsAttrInfo['attr_gallery_flie'] : $goods_thumb;
            }
        }

        return $goods_thumb;
    }
}
