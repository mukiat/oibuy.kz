<?php

namespace App\Repositories\Activity;

use App\Models\Goods;
use App\Models\GoodsAttr;
use App\Models\Products;
use App\Models\ProductsArea;
use App\Models\ProductsWarehouse;
use App\Models\SeckillGoods;
use App\Models\SeckillGoodsAttr;
use App\Models\StoreProducts;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use Illuminate\Support\Facades\DB;


class SeckillRepository
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 秒杀商品详情
     * @param int $seckill_goods_id
     * @return array
     */
    public static function seckill_detail($seckill_goods_id = 0)
    {

        $seckill_goods_id = intval($seckill_goods_id);
        if (empty($seckill_goods_id)) {
            return [];
        }

        $seckill = SeckillGoods::where('id', $seckill_goods_id);

        $seckill = $seckill->whereHasIn('getSeckill', function ($query) {
            $query->where('is_putaway', 1)->where('review_status', 3);
        });

        $seckill = $seckill->with([
            'getGoods' => function ($query) {
                $query->where('is_delete', 0);
            },
            'getSeckill',
            'getSeckillTimeBucket' => function ($query) {
                $query->select('id', 'begin_time', 'end_time');
            },
            'getSeckillGoodsAttr' => function ($query) {
                $query->select('id', 'seckill_goods_id', 'product_id', 'sec_price', 'sec_num', 'sec_limit');
            }
        ]);

        $seckill = $seckill->first();

        return $seckill ? $seckill->toArray() : [];
    }

    /**
     * 获得秒杀活动的商品列表
     *
     * @access  public
     * @param int $sec_id
     * @param int $tb_id
     * @return  array
     */
    public static function get_add_seckill_goods($sec_id = 0, $tb_id = 0)
    {
        $filter['sec_id'] = $sec_id;
        $filter['tb_id'] = $tb_id;

        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getSeckillGoodsList';
        $get_filter = app(DscRepository::class)->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $model = SeckillGoods::where('sec_id', $sec_id)->where('tb_id', $tb_id);

        $model = $model->whereHasIn('getGoods');

        $count = $model->count('id');

        $filter['record_count'] = $count;

        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        app(DscRepository::class)->setSessionFilter($filter, $param_str);

        $model = $model->with([
            'getGoods' => function ($query) {
                $query->select('goods_id', 'goods_name', 'shop_price', 'model_attr');
            }
        ]);

        $model = $model->offset($filter['start'])->limit($filter['page_size']);

        $row = $model->select('id', 'sec_id', 'tb_id', 'goods_id', 'sec_num', 'sec_limit', 'sec_price')
            ->orderBy('tb_id', 'ASC')
            ->orderBy('id', 'DESC')
            ->get();

        $row = $row ? $row->toArray() : [];

        /* 获活动数据 */
        foreach ($row as $key => $val) {
            $row[$key] = $val = collect($val)->merge($val['get_goods'])->except('get_goods')->all();

            $row[$key]['shop_price'] = app(DscRepository::class)->getPriceFormat($val['shop_price']);
            // 商品是否有规格属性
            $has_attr = DB::table('products')->where('goods_id', $val['goods_id'])->count('product_id');
            $row[$key]['has_attr'] = $has_attr ?? 0;
        }

        //获取秒杀商品ID列表
        $cat_goods = SeckillGoods::where('sec_id', $sec_id)->where('tb_id', $tb_id)->selectRaw('GROUP_CONCAT(goods_id) as goods_ids')->value('goods_ids');

        return ['seckill_goods' => $row, 'filter' => $filter, 'cat_goods' => $cat_goods, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }

    /**
     * 秒杀商品信息
     * @param int $seckill_goods_id
     * @param int $goods_id
     * @param string $attr_id
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param int $store_id
     * @return array
     */
    public static function seckill_goods_info($seckill_goods_id = 0, $goods_id = 0, $attr_id = '', $warehouse_id = 0, $area_id = 0, $area_city = 0, $store_id = 0)
    {
        if (empty($seckill_goods_id) || empty($goods_id)) {
            return [];
        }

        $product_id = 0;
        if (!empty($attr_id)) {
            $goods_attr = is_array($attr_id) ? implode('|', $attr_id) : $attr_id;
            $model_attr = Goods::where('goods_id', $goods_id)->value('model_attr');
            $products = self::getProduct($goods_id, $goods_attr, $warehouse_id, $area_id, $area_city, $model_attr, $store_id);
            $product_id = $products['product_id'] ?? 0;
        }

        $model = SeckillGoods::query()->where('id', $seckill_goods_id)->where('goods_id', $goods_id);

        if (!empty($attr_id) && $product_id > 0) {
            $model = $model->with([
                'getSeckillGoodsProduct' => function ($query) use ($product_id, $goods_id) {
                    $query->where('product_id', $product_id)->where('goods_id', $goods_id);
                }
            ]);
        }

        $model = $model->first();

        return $model ? $model->toArray() : [];
    }

    /**
     * 获取 秒杀商品信息 库存
     * @param int $seckill_goods_id
     * @param int $goods_id
     * @param int $product_id
     * @return array
     */
    public static function seckill_goods_stock($seckill_goods_id = 0, $goods_id = 0, $product_id = 0)
    {
        if (empty($seckill_goods_id) || empty($goods_id)) {
            return [];
        }

        $model = SeckillGoods::query()->where('id', $seckill_goods_id)->where('goods_id', $goods_id);

        if (!empty($product_id) && $product_id > 0) {
            $model = $model->with([
                'getSeckillGoodsProduct' => function ($query) use ($product_id, $goods_id) {
                    $query->where('product_id', $product_id)->where('goods_id', $goods_id);
                }
            ]);
        }

        $model = $model->first();

        $seckill_goods = $model ? $model->toArray() : [];

        if ($seckill_goods) {
            if (!empty($product_id) && $product_id > 0) {
                $seckill_goods['sec_price'] = $seckill_goods['get_seckill_goods_product']['sec_price'] ?? 0;
                $seckill_goods['sec_num'] = $seckill_goods['get_seckill_goods_product']['sec_num'] ?? 0;
                $seckill_goods['sec_limit'] = $seckill_goods['get_seckill_goods_product']['sec_limit'] ?? 0;
            }
        }

        return $seckill_goods;
    }

    /**
     * 更新秒杀商品库存
     * @param int $seckill_goods_id
     * @param int $goods_id
     * @param int $product_id
     * @param string $seckill_update
     * @return bool
     */
    public static function update_seckill_goods_stock($seckill_goods_id = 0, $goods_id = 0, $product_id = 0, $seckill_update = '')
    {
        if (empty($seckill_goods_id) || empty($goods_id) || empty($seckill_update)) {
            return false;
        }

        if (!empty($product_id) && $product_id > 0) {
            // 秒杀属性库存
            $other = [
                'sec_num' => DB::raw($seckill_update)
            ];
            SeckillGoodsAttr::where('seckill_goods_id', $seckill_goods_id)->where('product_id', $product_id)->where('goods_id', $goods_id)->update($other);
        } else {
            $other = [
                'sec_num' => DB::raw($seckill_update)
            ];
            SeckillGoods::where('id', $seckill_goods_id)->where('goods_id', $goods_id)->update($other);
        }

        return true;
    }

    /**
     * 查询商品货品信息
     *
     * @param int $goods_id
     * @param string $goods_attr
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param int $model_attr
     * @param int $store_id
     * @return array
     */
    public static function getProduct($goods_id = 0, $goods_attr = '', $warehouse_id = 0, $area_id = 0, $area_city = 0, $model_attr = 0, $store_id = 0)
    {

        if (empty($goods_attr)) {
            return [];
        }

        if ($store_id > 0) {
            $model = StoreProducts::where('goods_id', $goods_id)->where('store_id', $store_id);
        } else {
            if ($model_attr == 1) {
                $model = ProductsWarehouse::where('goods_id', $goods_id)->where('warehouse_id', $warehouse_id);
            } elseif ($model_attr == 2) {
                $model = ProductsArea::where('goods_id', $goods_id)->where('area_id', $area_id);
                if (config('shop.area_pricetype') == 1) {
                    $model = $model->where('city_id', $area_city);
                }
            } else {
                $model = Products::where('goods_id', $goods_id);
            }
        }

        $goods_attr = BaseRepository::getExplode($goods_attr, "|");

        foreach ($goods_attr as $key => $val) {
            $model = $model->whereRaw("FIND_IN_SET('$val', REPLACE(goods_attr, '|', ','))");
        }

        return BaseRepository::getToArrayFirst($model);
    }

    /**
     * 查询商品货品列表
     *
     * @param int $goods_id
     * @param int $model_attr
     * @return array
     */
    public static function getGoodsProducts($goods_id = 0, $model_attr = 0)
    {
        if (empty($goods_id)) {
            return [];
        }

        if ($model_attr == 1) {
            $model = ProductsWarehouse::where('goods_id', $goods_id);
        } elseif ($model_attr == 2) {
            $model = ProductsArea::where('goods_id', $goods_id);
        } else {
            $model = Products::where('goods_id', $goods_id);
        }

        return BaseRepository::getToArrayGet($model);
    }

    /**
     * @param int $goods_id
     * @param array $goods_attr_id
     * @param array $columns
     * @return array
     */
    public static function getGoodsAttr($goods_id = 0, $goods_attr_id = [], $columns = ['*'])
    {
        if (empty($goods_id)) {
            return [];
        }

        $model = GoodsAttr::where('goods_id', $goods_id);

        if ($goods_attr_id) {
            $model = $model->whereIn('goods_attr_id', $goods_attr_id);
        }

        $model = $model->with([
            'getGoodsAttribute' => function ($query) {
                $query->select('attr_id', 'attr_name');
            }
        ]);

        $model = $model->select($columns);

        $row = BaseRepository::getToArrayGet($model);

        foreach ($row as $key => $val) {
            $row[$key] = $val = collect($val)->merge($val['get_goods_attribute'])->except('get_goods_attribute')->all();
        }

        return $row;
    }

    /**
     * 参与秒杀属性
     * @param int $seckill_goods_id
     * @param int $product_id
     * @param array $data
     * @return bool
     */
    public static function addSeckillGoodsAttr($seckill_goods_id = 0, $product_id = 0, $data = [])
    {
        if (empty($seckill_goods_id) || empty($product_id) || empty($data)) {
            return false;
        }

        $values = [
            'goods_id' => $data['goods_id'] ?? 0,
            'sec_price' => $data['sec_price'] ?? 0,
            'sec_num' => $data['sec_num'] ?? 0,
            'sec_limit' => $data['sec_limit'] ?? 0
        ];
        return SeckillGoodsAttr::query()->updateOrInsert(['seckill_goods_id' => $seckill_goods_id, 'product_id' => $product_id], $values);
    }

    /**
     * 取消秒杀属性
     * @param int $seckill_goods_id
     * @param int $product_id
     * @return bool
     */
    public static function removeSeckillGoodsAttr($seckill_goods_id = 0, $product_id = 0)
    {
        if (empty($seckill_goods_id) || empty($product_id)) {
            return false;
        }

        return SeckillGoodsAttr::query()->where('seckill_goods_id', $seckill_goods_id)->where('product_id', $product_id)->delete();
    }

    /**
     * 是否参与秒杀属性
     * @param int $seckill_goods_id
     * @param int $product_id
     * @return array
     */
    public static function isSeckillGoodsAttr($seckill_goods_id = 0, $product_id = 0)
    {
        if (empty($seckill_goods_id) || empty($product_id)) {
            return [];
        }

        $model = SeckillGoodsAttr::query()->where('seckill_goods_id', $seckill_goods_id)->where('product_id', $product_id)->first();

        return $model ? $model->toArray() : [];
    }

    /**
     * 获取秒杀商品已参与规格属性列表
     *
     * @param int $seckill_goods_id
     * @param int $goods_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getSeckillGoodsAttr($seckill_goods_id = 0, $goods_id = 0)
    {
        $model = SeckillGoodsAttr::query()->where('seckill_goods_id', $seckill_goods_id)->where('goods_id', $goods_id);
        $model = $model->with([
            'getProducts' => function ($query) use ($goods_id) {
                $query->where('goods_id', $goods_id);
            }
        ]);

        $model = BaseRepository::getToArrayGet($model);

        return $model;
    }

    /**
     * 获取秒杀商品已参与规格属性
     *
     * @param int $seckill_goods_id
     * @param int $goods_id
     * @param int $product_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getSeckillGoodsAttrByProductId($seckill_goods_id = 0, $goods_id = 0, $product_id = 0)
    {
        $model = SeckillGoodsAttr::query()->where('seckill_goods_id', $seckill_goods_id)->where('goods_id', $goods_id)->where('product_id', $product_id);

        return BaseRepository::getToArrayFirst($model);
    }

    public static function getGoodsProductByProductId($goods_id = 0, $model_attr = 0, $product_id = 0)
    {
        if (!empty($product_id)) {
            if ($model_attr == 1) {
                $prod = ProductsWarehouse::where('goods_id', $goods_id)->where('product_id', $product_id);
            } elseif ($model_attr == 2) {
                $prod = ProductsArea::where('goods_id', $goods_id)->where('product_id', $product_id);
            } else {
                $prod = Products::where('goods_id', $goods_id)->where('product_id', $product_id);
            }

            $product_number = $prod->value('product_number');
        } else {
            $product_number = Goods::where('goods_id', $goods_id)->value('goods_number');
        }

        return $product_number;
    }
}
