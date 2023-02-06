<?php

namespace App\Services\Order;

use App\Models\GoodsAttr;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Brand\BrandDataHandleService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Package\PackageGoodsService;
use App\Services\Region\RegionDataHandleService;

class OrderGoodsService
{
    protected $dscRepository;
    protected $packageGoodsService;

    public function __construct(
        DscRepository $dscRepository,
        PackageGoodsService $packageGoodsService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->packageGoodsService = $packageGoodsService;
    }

    /**
     * 获取订单商品列表
     *
     * @access  public
     * @param  $where
     * @return  array
     */
    public function getOrderGoodsList($where = [])
    {
        $res = OrderGoods::selectRaw("*, goods_number AS num")
            ->whereRaw(1);

        if (isset($where['order_id']) && !empty($where['order_id'])) {
            $where['order_id'] = !is_array($where['order_id']) ? explode(",", $where['order_id']) : $where['order_id'];

            $res = $res->whereIn('order_id', $where['order_id']);
        }

        if (isset($where['is_real'])) {
            $res = $res->where('is_real', $where['is_real']);
        }

        if (isset($where['extension_code'])) {
            $res = $res->where('extension_code', $where['extension_code']);
        }

        if (isset($where['sort']) && isset($where['order'])) {
            $res = $res->orderBy($where['sort'], $where['order']);
        }

        if (isset($where['size'])) {
            if (isset($where['page'])) {
                $start = ($where['page'] - 1) * $where['size'];

                if ($start > 0) {
                    $res = $res->skip($start);
                }
            }

            if ($where['size'] > 0) {
                $res = $res->take($where['size']);
            }
        }

        $res = BaseRepository::getToArrayGet($res);

        return $res;
    }

    /**
     * 查询限购商品已购买数量
     *
     * @param int $start_date
     * @param int $end_date
     * @param int $goods_id
     * @param int $user_id
     * @param string $extension_code
     * @param string $goods_attr_id
     * @param int $group_by_id
     * @return array
     */
    public function getForPurchasingGoods($start_date = 0, $end_date = 0, $goods_id = 0, $user_id = 0, $extension_code = '', $goods_attr_id = '', $group_by_id = 0)
    {
        $order = OrderInfo::select('order_id')->where('main_count', 0)
            ->whereNotIn('order_status', [OS_CANCELED, OS_INVALID]);

        if ($group_by_id) {
            $order = $order->where('extension_id', $group_by_id);
        }

        if ($extension_code && $extension_code != 'virtual_card') {
            $order = $order->where('extension_code', $extension_code);
        } else {
            $order = $order->where('extension_code', '');
        }

        if ($extension_code != 'group_buy') {
            $order = $order->where('user_id', $user_id);
        }

        if ($start_date && $end_date) {
            $order = $order->where('add_time', '>', $start_date)
                ->where('add_time', '<', $end_date);
        }

        $order = BaseRepository::getToArrayGet($order);

        $order_id = BaseRepository::getKeyPluck($order, 'order_id');

        $res = OrderGoods::select('goods_number')
            ->where('goods_id', $goods_id)
            ->whereIn('order_id', $order_id);

        //过滤活动商品
        if (empty($extension_code)) {
            $res = $res->where('extension_code', '');
        }

        if (in_array($extension_code, ['virtual_card'])) {
            $res = $res->where('extension_code', $extension_code);
        }

        if ($goods_attr_id) {
            $goods_attr_id = BaseRepository::getExplode($goods_attr_id);
            foreach ($goods_attr_id as $key => $val) {
                $res = $res->whereRaw("FIND_IN_SET('$val', REPLACE(goods_attr_id, '|', ','))");
            }
        }

        $goods_number = $res->sum('goods_number');
        $goods_number = $goods_number ? $goods_number : 0;

        return ['goods_number' => $goods_number];
    }

    /**
     * 购买商品的属性
     *
     * @param $goods_id
     * @param $user_id
     * @param $order_id
     * @return mixed
     */
    public function getUserBuyGoodsOrder($goods_id, $user_id, $order_id)
    {
        $orderWhere = [
            'user_id' => $user_id,
            'order_id' => $order_id,
        ];
        $buy_goods = OrderGoods::select('order_id', 'goods_attr_id')->where('goods_id', $goods_id)
            ->whereHasIn('getOrder', function ($query) use ($orderWhere) {
                $query->where('user_id', $orderWhere['user_id'])
                    ->where('order_id', $orderWhere['order_id']);
            });

        $buy_goods = $buy_goods->with([
            'getOrder' => function ($query) {
                $query->select('order_id', 'add_time');
            }
        ]);

        $buy_goods = BaseRepository::getToArrayFirst($buy_goods);

        $buy_goods = isset($buy_goods['get_oder']) ? BaseRepository::getArrayMerge($buy_goods, $buy_goods['get_oder']) : $buy_goods;

        $buy_goods['goods_attr'] = isset($buy_goods['goods_attr_id']) ? $this->getGoodsAttrOrder($buy_goods['goods_attr_id']) : '';
        $buy_goods['add_time'] = !empty($buy_goods['add_time']) ? TimeRepository::getLocalDate(config('shop.time_format'), $buy_goods['add_time']) : '';

        return $buy_goods;
    }

    /**
     * 查询属性名称
     *
     * @param $goods_attr_id
     * @return mixed|string
     */
    public function getGoodsAttrOrder($goods_attr_id)
    {
        $attr = '';
        if ($goods_attr_id) {
            if (!empty($goods_attr_id)) {
                $fmt = "%s：%s <br/>";

                $goods_attr_id = BaseRepository::getExplode($goods_attr_id);

                $res = GoodsAttr::select('goods_attr_id', 'attr_id', 'attr_value')
                    ->whereIn('goods_attr_id', $goods_attr_id);

                $res = $res->with([
                    'getGoodsAttribute' => function ($query) {
                        $query->select('attr_id', 'attr_name', 'sort_order');
                    }
                ]);

                $res = BaseRepository::getToArrayGet($res);

                if ($res) {
                    foreach ($res as $key => $row) {
                        $row = $row['get_goods_attribute'] ? array_merge($row, $row['get_goods_attribute']) : $row;

                        $res[$key] = $row;
                    }

                    $res = BaseRepository::getSortBy($res, 'sort_order');

                    if ($res) {
                        foreach ($res as $row) {
                            $attr .= sprintf($fmt, $row['attr_name'], $row['attr_value'], '');
                        }

                        $attr = str_replace('[0]', '', $attr);
                    }
                }
            }
        }

        return $attr;
    }

    /**
     * 打印订单
     *
     * @param int $order_id
     * @return array
     */
    public function getOrderPdfGoods($order_id = 0)
    {

        /* 取得订单商品及货品 */
        $goods_list = [];
        $goods_attr = [];

        $res = OrderGoods::where('order_id', $order_id);
        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id);
            $ConsumptionList = GoodsDataHandleService::GoodsConsumptionDataList($goods_id);

            $brand_id = BaseRepository::getKeyPluck($goodsList, 'brand_id');
            $brandList = BrandDataHandleService::goodsBrand($brand_id, ['brand_id', 'brand_name']);

            $product_id = BaseRepository::getKeyPluck($res, 'product_id');
            $productsList = GoodsDataHandleService::getProductsDataList($product_id, '*', 'product_id');
            $productsWarehouseList = GoodsDataHandleService::getProductsWarehouseDataList($product_id, 0, '*', 'product_id');
            $productsAreaList = GoodsDataHandleService::getProductsAreaDataList($product_id, 0, 0, '*', 'product_id');

            $warehouse_id = BaseRepository::getKeyPluck($res, 'warehouse_id');
            $warehouseList = RegionDataHandleService::regionWarehouseDataList($warehouse_id, ['region_id', 'region_name']);

            foreach ($res as $row) {

                if (empty($row['extension_code'])) {
                    $row['get_goods'] = $goodsList[$row['goods_id']] ?? [];
                    $goods = $row['get_goods'];
                }

                $goods['brand_id'] = $goods['brand_id'] ?? 0;
                $brand = $brandList[$goods['brand_id']] ?? [];
                $row['brand_name'] = $brand['brand_name'] ?? '';

                $goods['storage'] = $goods['goods_number'] ?? 0;

                if (!empty($row['product_id'])) {
                    if ($row['model_attr'] == 1) {
                        $product = $productsWarehouseList[$row['product_id']] ?? [];
                    } elseif ($row['model_attr'] == 2) {
                        $product = $productsAreaList[$row['product_id']] ?? [];
                    } else {
                        $product = $productsList[$row['product_id']] ?? [];
                    }

                    $row['storage'] = $product['product_number'] ?? 0;
                }

                $row['product_sn'] = $product['product_sn'] ?? '';

                /* 虚拟商品支持 */
                if ($row['is_real'] == 0) {
                    /* 取得语言项 */
                    $filename = app_path('Plugins/' . $row['extension_code'] . '/Languages/common_' . $GLOBALS['_CFG']['lang'] . '.php');
                    if (file_exists($filename)) {
                        include_once($filename);
                        if (!empty($GLOBALS['_LANG'][$row['extension_code'] . '_link'])) {
                            $row['goods_name'] = $row['goods_name'] . sprintf($GLOBALS['_LANG'][$row['extension_code'] . '_link'], $row['goods_id'], $order_id);
                        }
                    }
                }

                //ecmoban模板堂 --zhuo start 商品金额促销
                $row['goods_amount'] = $row['goods_price'] * $row['goods_number'];
                $goods_consumption = $ConsumptionList[$row['goods_id']] ?? [];

                if ($goods_consumption) {
                    $row['amount'] = $this->dscRepository->getGoodsConsumptionPrice($goods_consumption, $row['goods_amount']);
                } else {
                    $row['amount'] = $row['goods_amount'];
                }

                $row['dis_amount'] = $row['goods_amount'] - $row['amount'];
                $row['discount_amount'] = $this->dscRepository->getPriceFormat($row['dis_amount'], false);
                //ecmoban模板堂 --zhuo end 商品金额促销

                $row['formated_subtotal'] = $this->dscRepository->getPriceFormat($row['amount']);
                $row['formated_goods_price'] = $this->dscRepository->getPriceFormat($row['goods_price']);

                $warehouse = $warehouseList[$row['warehouse_id']] ?? [];
                $row['warehouse_name'] = $warehouse['region_name'] ?? '';

                //将商品属性拆分为一个数组
                $goods_attr[] = BaseRepository::getExplode($row['goods_attr']);

                if ($row['extension_code'] == 'package_buy') {
                    $row['storage'] = '';
                    $row['brand_name'] = '';
                    $row['package_goods_list'] = $this->packageGoodsService->getPackageGoods($row['goods_id']);
                }

                $goods_list[] = $row;
            }
        }

        return $goods_list;
    }
}
