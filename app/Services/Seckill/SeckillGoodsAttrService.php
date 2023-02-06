<?php

namespace App\Services\Seckill;

use App\Models\SeckillGoodsAttr;
use App\Repositories\Activity\SeckillRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Goods\GoodsMobileService;
use App\Services\Goods\GoodsProdutsService;
use App\Services\Goods\GoodsWarehouseService;
use Illuminate\Support\Facades\DB;

/**
 * Class SeckillGoodsAttrService
 * @package App\Services\Seckill
 */
class SeckillGoodsAttrService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 查询商品货品信息
     *
     * @param int $goods_id
     * @param int $model_attr
     * @param int $seckill_goods_id
     * @return array
     */
    public static function getGoodsProducts($goods_id = 0, $model_attr = 0, $seckill_goods_id = 0)
    {
        $prod = SeckillRepository::getGoodsProducts($goods_id, $model_attr);

        foreach ($prod as $key => $item) {
            $goods_attr_id = $item['goods_attr'] ?? '';
            $goods_attr_id = explode('|', $goods_attr_id);
            $prod[$key]['goods_attr_format'] = self::getGoodsAttrFormat($goods_id, $goods_attr_id, ['goods_attr_id', 'goods_id', 'attr_id', 'attr_value']);
            // 秒杀商品属性 是否已参与秒杀
            $prod[$key]['is_seckill_goods_attr'] = 0;
            $seckill_goods_attr = self::isSeckillGoodsAttr($seckill_goods_id, $item['product_id']);
            if (!empty($seckill_goods_attr)) {
                $prod[$key]['is_seckill_goods_attr'] = 1;
            }
            $prod[$key]['sec_price'] = $seckill_goods_attr['sec_price'] ?? 0;
            $prod[$key]['sec_num'] = $seckill_goods_attr['sec_num'] ?? 0;
            $prod[$key]['sec_limit'] = $seckill_goods_attr['sec_limit'] ?? 0;
        }

        return $prod;
    }

    /**
     * 获取属性格式化
     *  exp: 颜色: 白红色; 大小: 大; 尺码: M;
     * @param int $goods_id
     * @param array $goods_attr_id
     * @param array $columns
     * @return string
     */
    public static function getGoodsAttrFormat($goods_id = 0, $goods_attr_id = [], $columns = ['*'])
    {
        $goods_attr = SeckillRepository::getGoodsAttr($goods_id, $goods_attr_id, $columns);

        $goods_attr_format = '';
        foreach ($goods_attr as $key => $item) {
            if ($item['attr_name']) {
                $goods_attr_format .= $item['attr_name'] . ": " . $item['attr_value'] . "; ";
            } else {
                $goods_attr_format .= $item['attr_value'] . "; ";
            }
        }

        return $goods_attr_format;
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
        return SeckillRepository::addSeckillGoodsAttr($seckill_goods_id, $product_id, $data);
    }

    /**
     * 取消秒杀属性
     * @param int $seckill_goods_id
     * @param int $product_id
     * @return bool
     */
    public static function removeSeckillGoodsAttr($seckill_goods_id = 0, $product_id = 0)
    {
        return SeckillRepository::removeSeckillGoodsAttr($seckill_goods_id, $product_id);
    }

    /**
     * 秒杀商品属性 是否已参与秒杀
     *
     * @param int $seckill_goods_id
     * @param int $product_id
     * @return array
     */
    public static function isSeckillGoodsAttr($seckill_goods_id = 0, $product_id = 0)
    {
        return SeckillRepository::isSeckillGoodsAttr($seckill_goods_id, $product_id);
    }

    /**
     * 获取秒杀商品已参与规格属性列表
     *
     * @param int $seckill_goods_id
     * @param int $goods_id
     * @return array
     */
    public static function getSeckillGoodsAttr($seckill_goods_id = 0, $goods_id = 0)
    {
        $prod = SeckillRepository::getSeckillGoodsAttr($seckill_goods_id, $goods_id);

        foreach ($prod as $key => $item) {
            $get_products = $item['get_products'] ?? [];
            $goods_attr_id = $get_products['goods_attr'] ?? '';
            $goods_attr_id = explode('|', $goods_attr_id);
            $prod[$key]['goods_attr_format'] = self::getGoodsAttrFormat($goods_id, $goods_attr_id, ['goods_attr_id', 'goods_id', 'attr_id', 'attr_value']);
            $prod[$key]['product_price'] = app(DscRepository::class)->getPriceFormat($get_products['product_price'] ?? 0);
        }

        return $prod;
    }

    /**
     * 秒杀商品属性价格与库存
     * @param int $user_id
     * @param int $goods_id
     * @param string $attr_id
     * @param int $num
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param int $store_id
     * @param array $region
     * @param int $seckill_goods_id
     * @return array
     */
    public function goodsPropertiesPrice($user_id = 0, $goods_id = 0, $attr_id = '', $num = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0, $store_id = 0, $region = [], $seckill_goods_id = 0)
    {
        $result = [
            'stock' => 0,       //秒杀商品库存
            'market_price' => 0,      //市场价
            'qty' => 0,               //数量
            'spec_price' => 0,        //秒杀属性价格
            'goods_price' => 0,           //商品价格
            'attr_name' => '',
            'attr_img' => '',         //商品属性图片
            'sec_limit' => 0, // 秒杀商品限购数量
            'spec_disable' => 0, // 秒杀属性是否可选 0 可选择 1 禁止选择
        ];

        $attr_id = BaseRepository::getExplode($attr_id);

        if ($attr_id) {
            sort($attr_id);
        }

        $result['spec_disable'] = 0; //可选择

        $seckillCountId = SeckillGoodsAttr::where('seckill_goods_id', $seckill_goods_id)->count('id');

        // 秒杀商品
        $seckill_goods = SeckillRepository::seckill_goods_info($seckill_goods_id, $goods_id, $attr_id, $warehouse_id, $area_id, $area_city, $store_id);
        $product = $seckill_goods['get_seckill_goods_product'] ?? [];

        if ($product || !empty($seckillCountId)) {
            $result['stock'] = $product['sec_num'] ?? 0;
            $result['sec_limit'] = $product['sec_limit'] ?? 0;
            $result['spec_price'] = $product['sec_price'] ?? 0;
        } else {
            $result['stock'] = $seckill_goods['sec_num'] ?? 0;
            $result['sec_limit'] = $seckill_goods['sec_limit'] ?? 0;
            $result['spec_price'] = $seckill_goods['sec_price'] ?? 0;
        }

        if ($result['stock'] == 0) {
            $result['spec_disable'] = 1; //禁止选择
        }

        $result['market_price'] = app(GoodsMobileService::class)->goodsMarketPrice($goods_id, $attr_id, $warehouse_id, $area_id, $area_city);
        $result['market_price_formated'] = $this->dscRepository->getPriceFormat($result['market_price'], true);
        $result['qty'] = $num;

        $result['spec_price_formated'] = $this->dscRepository->getPriceFormat($result['spec_price'], true);

        $result['goods_price'] = $this->dscRepository->getPriceFormat($result['spec_price'], true, false);
        $result['goods_price_formated'] = $this->dscRepository->getPriceFormat($result['spec_price'], true);

        $result['shop_price'] = $result['goods_price'];
        $result['shop_price_formated'] = $result['goods_price_formated'];

        // 商品属性运费
        $seckill_price = $result['sec_price'] ?? 0;
        $result['shipping_fee'] = goodsShippingFee($goods_id, $warehouse_id, $area_id, $area_city, $region, $seckill_price, $attr_id);

        if ($attr_id) {
            // 商品属性名称与图片
            $attr_value = [];
            $attr_img = [];
            foreach ($attr_id as $key => $val) {
                $res = DB::table('goods_attr')->select('attr_value', 'attr_img_flie', 'attr_gallery_flie')
                    ->where('goods_id', $goods_id)
                    ->where('goods_attr_id', $val)
                    ->first();
                if (!empty($res->attr_value)) {
                    $attr_value[$key] = $res->attr_value;
                }
                $attr_img[$key] = !empty($res->attr_gallery_flie) ? $res->attr_gallery_flie : (!empty($res->attr_img_flie) ? $res->attr_img_flie : '');
            }

            $result['attr_name'] = implode(' ', $attr_value);
            if (!empty($attr_img)) {
                $attr_img = collect($attr_img)->first();
                $result['attr_img'] = !empty($attr_img) ? $this->dscRepository->getImagePath($attr_img) : '';
            }
        }

        return $result;
    }
}
