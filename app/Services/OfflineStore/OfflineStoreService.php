<?php

namespace App\Services\OfflineStore;

use App\Models\Cart;
use App\Models\OfflineStore;
use App\Models\StoreGoods;
use App\Models\StoreProducts;
use App\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Goods\GoodsAttrService;
use Illuminate\Support\Facades\DB;

/**
 * Class OfflineStoreService
 * @package App\Services\OfflineStore
 */
class OfflineStoreService
{
    protected $goodsAttrService;
    protected $dscRepository;

    public function __construct(
        GoodsAttrService $goodsAttrService,
        DscRepository $dscRepository
    )
    {
        $this->goodsAttrService = $goodsAttrService;
        $this->dscRepository = $dscRepository;
    }

    /**
     * 门店列表
     * @param $provinces_id
     * @param $city_id
     * @param $district_id
     * @param $store_id
     * @param $goods_id
     * @param $spec_arr
     * @param int $page
     * @param int $size
     * @param int $num
     * @param array $rec_id
     * @param int $lat
     * @param int $lng
     * @return mixed
     */
    public function listOfflineStore($provinces_id, $city_id, $district_id, $store_id, $goods_id, $spec_arr, $page = 1, $size = 10, $num = 1, $rec_id = [], $lat = 0, $lng = 0)
    {
        $cart = [];
        $cart_list = [];
        if ($rec_id) {
            $rec_id = BaseRepository::getExplode($rec_id);

            $cart = cart::whereIn('rec_id', $rec_id)->get();
            if ($cart) {
                $cart_list = collect($cart)->keyBy(function ($item) {
                    return $item['goods_id'] . '_' . $item['goods_attr_id'];
                })->toArray();
                //通过rec_id 获取goods_id
                $goods_id = array_keys($cart_list);
            }
        }

        $begin = ($page - 1) * $size;
        $store_list = OfflineStore::where('is_confirm', 1);

        if ($provinces_id > 0) {
            $store_list = $store_list->where('province', $provinces_id);
        }

        if ($city_id > 0) {
            $store_list = $store_list->where('city', $city_id);
        }

        if ($district_id > 0) {
            $store_list = $store_list->where('district', $district_id);
        }

        if ($store_id > 0) {
            $store_list = $store_list->where('id', $store_id);
        }

        if (is_array($goods_id) && $goods_id) {
            //1.4.2 购物车自提，多商品
            $store_list = $store_list->whereHasIn('getStoreGoods', function ($query) use ($goods_id) {
                $query->whereIn('goods_id', $goods_id);
            });
        } else {
            if ($goods_id > 0) {
                $store_list = $store_list->whereHasIn('getStoreGoods', function ($query) use ($goods_id) {
                    $query->where('goods_id', $goods_id);
                });
            }
        }

        $store_list = $store_list->whereHasIn('getStoreGoods', function ($query) use ($goods_id) {
            $query->where('goods_id', $goods_id)->where('goods_number', '>', 0);
        });

        $store_list = $store_list->with([
            'getRegionProvince' => function ($query) {
                $query->select('region_id', 'region_name');
            },
            'getRegionCity' => function ($query) {
                $query->select('region_id', 'region_name');
            },
            'getRegionDistrict' => function ($query) {
                $query->select('region_id', 'region_name');
            }
        ]);

        if ($lat && $lng) {
            // 提供的距离以公里为单位。如果需要英里，请使用3959而不是6371。乘以1000后转为米单位
            $store_list->select(DB::raw('*,( 6371 * acos( cos( radians(' . $lat . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $lng . ') ) + sin( radians(' . $lat . ') ) * sin( radians( latitude ) ) )) AS distance'));
            $store_list = $store_list->orderBy('distance', 'ASC');
        }

        $store_list = $store_list->offset($begin)
            ->limit($size)->get();

        $store_list = $store_list ? $store_list->toArray() : [];

        if (!empty($store_list)) {
            foreach ($store_list as $k => $v) {
                if (isset($v['get_region_province']) && $v['get_region_province']) {
                    $province_name = $v['get_region_province']['region_name'];
                } else {
                    $province_name = '';
                }

                if (isset($v['get_region_city']) && $v['get_region_city']) {
                    $city_name = $v['get_region_city']['region_name'];
                } else {
                    $city_name = '';
                }

                if (isset($v['get_region_district']) && $v['get_region_district']) {
                    $district_name = $v['get_region_district']['region_name'];
                } else {
                    $district_name = '';
                }
                $store_list[$k]['address'] = $province_name . " " . $city_name . " " . $district_name;
                if ($v['id'] == $store_id) {
                    $store_list[$k]['checked'] = 1;
                }
                $store_list[$k]['is_stocks'] = 1;//1.4.3库存is_stocks，未使用stock废弃

                $store_list[$k]['distance_format'] = isset($v['distance']) && !empty($v['distance']) ? distance_format($v['distance']) : 0;//定位距离
                if (is_array($goods_id) && $goods_id && $cart_list) {
                    //1.4.2 购物车自提，多商品
                    $goods_list = StoreGoods::whereIn('goods_id', $goods_id)->where('store_id', $v['id'])->get();
                    if ($goods_list) {
                        //判断库存
                        foreach ($goods_list as $row) {
                            $row_cart_goods = collect($cart)->where('goods_id', $row['goods_id'])->toArray();
                            if ($row_cart_goods) {
                                foreach ($row_cart_goods as $v_cart_goods) {
                                    if ($v_cart_goods['goods_attr_id']) {
                                        //有属性
                                        $cart_spec_arr = explode(',', $v_cart_goods['goods_attr_id']);
                                        $is_spec = $this->goodsAttrService->is_spec($cart_spec_arr);

                                        if ($is_spec === true) {
                                            $products = $this->get_offline_num($row['goods_id'], $cart_spec_arr, $v['ru_id'], $v['id']);
                                            if ($products == 0 || $v_cart_goods['goods_number'] > $products) {
                                                $store_list[$k]['is_stocks'] = 0;
                                                break(2);
                                            }
                                        }
                                    } else {
                                        //无属性
                                        if ($row['goods_number'] < $v_cart_goods['goods_number']) {
                                            $store_list[$k]['is_stocks'] = 0;
                                            break(2);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $backurl = url('mobile/#/cart/');
                } else {
                    $goods_number = StoreGoods::where('goods_id', $goods_id)->where('store_id', $v['id'])->value('goods_number');
                    $goods_number = $goods_number ? $goods_number : 0;

                    $is_spec = $this->goodsAttrService->is_spec($spec_arr);

                    if ($is_spec === true) {
                        $products = $this->get_offline_num($goods_id, $spec_arr, $v['ru_id'], $v['id']);
                        if ($products == 0 || $num > $products) {
                            $store_list[$k]['is_stocks'] = 0;
                        }
                    } else {
                        //1.4.2 修复，设置属性库存时，则调取属性库存信息，请忽略默认库存
                        if ($goods_number < $num) {
                            $store_list[$k]['is_stocks'] = 0;
                        }
                    }

                    $backurl = url('mobile/#/goods/' . $goods_id);
                }

                $address = $province_name . $city_name . $district_name . $store_list[$k]['stores_address'];

                $store_list[$k]['map_url'] = "http://apis.map.qq.com/tools/routeplan/eword=" . $address . "?referer=myapp&key=" . config('shop.tengxun_key') . "&back=1&backurl=" . $backurl;
            }
        }
        return $store_list;
    }

    /**
     * 门店详情
     *
     * @param int $store_id
     * @return array
     */
    public function infoOfflineStore($store_id = 0)
    {
        $store_info = OfflineStore::where('id', $store_id);

        $store_info = $store_info->with([
            'getRegionProvince' => function ($query) {
                $query->select('region_id', 'region_name');
            },
            'getRegionCity' => function ($query) {
                $query->select('region_id', 'region_name');
            },
            'getRegionDistrict' => function ($query) {
                $query->select('region_id', 'region_name');
            },
        ]);

        $store_info = BaseRepository::getToArrayFirst($store_info);

        if ($store_info) {
            if ($store_info['get_region_province']) {
                $region_name = $store_info['get_region_province']['region_name'];
                unset($store_info['get_region_province']);
            } else {
                $region_name = '';
            }
            if ($store_info['get_region_city']) {
                $city_name = $store_info['get_region_city']['region_name'];
                unset($store_info['get_region_city']);
            } else {
                $city_name = '';
            }
            if ($store_info['get_region_district']) {
                $district_name = $store_info['get_region_district']['region_name'];
                unset($store_info['get_region_district']);
            } else {
                $district_name = '';
            }

            $store_info['address'] = $region_name . " " . $city_name . " " . $district_name;
        }

        return $store_info;
    }

    /**
     * 获取门店货品商品库存
     *
     * @param int $goods_id
     * @param string $spec_arr
     * @param int $ru_id
     * @param int $store_id
     * @return int
     */
    public function get_offline_num($goods_id = 0, $spec_arr = '', $ru_id = 0, $store_id = 0)
    {
        $spec_arr = BaseRepository::getExplode($spec_arr);

        if ($spec_arr) {
            $res = StoreProducts::select('product_number')
                ->where('goods_id', $goods_id)
                ->where('ru_id', $ru_id)
                ->where('store_id', $store_id);

            foreach ($spec_arr as $key => $val) {
                $res = $res->whereRaw("FIND_IN_SET('$val', REPLACE(goods_attr, '|', ','))");
            }

            $res = BaseRepository::getToArrayFirst($res);
        }

        $num = $res['product_number'] ?? 0;
        return $num;
    }

    /**
     * 获取用户手机号
     *
     * @param int $user_id
     * @return mixed
     */
    public function getUserMobile($user_id = 0)
    {
        $mobile = Users::where('user_id', $user_id)->value('mobile_phone');
        return $mobile;
    }

    /**
     * 判断地区是否有自提门店存在
     * @param $goods_id
     * @param int $seller_id
     * @param int $province
     * @param int $city
     * @param int $district
     * @return array|int
     */
    public function offilineStoreIsSet($goods_id, $seller_id = 0, $province = 0, $city = 0, $district = 0)
    {
        if (empty($province) || empty($city) || empty($district)) {
            return [];
        }
        //查询店铺下所有门店
        $model = OfflineStore::where('ru_id', $seller_id)->where('province', $province)
            ->where('city', $city)
            ->where('district', $district);

        $model = $model->with([
            'getRegionProvince' => function ($query) {
                $query->select('region_id', 'region_name');
            },
            'getRegionCity' => function ($query) {
                $query->select('region_id', 'region_name');
            },
            'getRegionDistrict' => function ($query) {
                $query->select('region_id', 'region_name');
            },
        ]);

        $model = $model->whereHasIn('getStoreGoods', function ($query) use ($goods_id) {
            $query->where('goods_id', $goods_id)->where('goods_number', '>', 0);
        });

        $store = $model->first();
        $store = $store ? $store->toArray() : [];

        if (!empty($store)) {

            if (isset($store['get_region_province']) && $store['get_region_province']) {
                $province_name = $store['get_region_province']['region_name'];
            } else {
                $province_name = '';
            }

            if (isset($store['get_region_city']) && $store['get_region_city']) {
                $city_name = $store['get_region_city']['region_name'];
            } else {
                $city_name = '';
            }

            if (isset($store['get_region_district']) && $store['get_region_district']) {
                $district_name = $store['get_region_district']['region_name'];
            } else {
                $district_name = '';
            }

            $store['stores_address'] = $province_name . " " . $city_name . " " . $district_name . " " . $store['stores_address'];
        }

        return $store;
    }
}
