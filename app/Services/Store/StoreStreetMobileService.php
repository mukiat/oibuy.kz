<?php

namespace App\Services\Store;

use App\Libraries\QRCode;
use App\Models\CollectStore;
use App\Models\Goods;
use App\Models\GoodsAttr;
use App\Models\MerchantsGrade;
use App\Models\MerchantsShopBrand;
use App\Models\MerchantsShopInformation;
use App\Models\MerchantsStepsFields;
use App\Models\Products;
use App\Models\ProductsArea;
use App\Models\ProductsWarehouse;
use App\Models\Region;
use App\Models\SellerGrade;
use App\Models\SellerQrcode;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\StrRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Comment\CommentService;
use App\Services\Common\AreaService;
use App\Services\Goods\GoodsAttrService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Region\RegionDataHandleService;
use App\Services\User\UserCommonService;
use Endroid\QrCode\Exception\InvalidPathException;
use Illuminate\Support\Facades\DB;

/**
 * Class StoreStreetMobileService
 * @package App\Services\Store
 */
class StoreStreetMobileService
{
    protected $goodsService;
    protected $userCommonService;
    protected $goodsAttrService;
    protected $dscRepository;
    protected $goodsCommonService;
    protected $commentService;
    protected $city = 0;

    public function __construct(
        UserCommonService $userCommonService,
        GoodsAttrService $goodsAttrService,
        DscRepository $dscRepository,
        GoodsCommonService $goodsCommonService,
        CommentService $commentService
    )
    {
        $this->userCommonService = $userCommonService;
        $this->goodsAttrService = $goodsAttrService;
        $this->dscRepository = $dscRepository;
        $this->goodsCommonService = $goodsCommonService;
        $this->commentService = $commentService;
        $this->city = app(AreaService::class)->areaCookie();
    }

    /**
     * 分类店铺列表
     *
     * @param int $cat_id
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param int $size
     * @param int $page
     * @param string $sort
     * @param string $order
     * @param int $user_id
     * @param int $lat
     * @param int $lng
     * @param int $city_id
     * @param string $keywords
     * @return mixed
     * @throws \Exception
     */
    public function getCatStoreList($cat_id = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0, $size = 10, $page = 1, $sort = 'goods_id', $order = 'DESC', $user_id = 0, $lat = 0, $lng = 0, $city_id = 0, $keywords = '')
    {
        $store_user = [];
        if ($cat_id) {
            $store_user = $this->getMerCatStoreList($cat_id);
        }

        $current = ($page - 1) * $size;
        $store = MerchantsShopInformation::where('shop_close', 1)
            ->where('is_street', 1)
            ->where('merchants_audit', 1);

        if ($cat_id) { // 有分类的 都要筛选
            $store = $store->whereIn('user_id', $store_user);
        }

        if ($keywords) {
            $store = $store->Where('rz_shop_name', 'like', '%' . $keywords . '%');
        }

        $store = $store->whereHasIn('getUsers');

        $store = $store->whereHasIn('getSellerShopinfo', function ($query) use ($city_id) {
            if ($city_id) {
                $query->where('city', $city_id);
            }
        });

        if ($user_id) {
            $rank = $this->userCommonService->getUserRankByUid($user_id);
            $user_rank = isset($rank['rank_id']) ? $rank['rank_id'] : 1;
            $user_discount = isset($rank['discount']) ? $rank['discount'] : 100;
        } else {
            $user_rank = 1;
            $user_discount = 100;
        }

        $store = $store->withCount([
            'getSellerShopinfo as distance' => function ($query) use ($lat, $lng) {
                if ($lat && $lng) {
                    // 提供的距离以公里为单位。如果需要英里，请使用3959而不是6371。乘以1000后转为米单位
                    $query->select(DB::raw('( 6371 * acos( cos( radians(' . $lat . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $lng . ') ) + sin( radians(' . $lat . ') ) * sin( radians( latitude ) ) )) AS distance'));
                }
            }
        ]);

        $store = $store->offset($current)->limit($size);

        if ($sort == 'distance') {
            $store = $store->orderBy('distance', $order);
        } else {

            if ($order == 'ASC') {
                $store = $store->orderBy('collect_count', 'desc');
            } else {
                $store = $store->orderBy('collect_count', 'asc');
            }

            $store = $store->orderBy('sort_order', $order);
        }

        $store = BaseRepository::getToArrayGet($store);

        if ($store) {
            $seller_id = BaseRepository::getKeyPluck($store, 'user_id');

            $where = [
                'is_on_sale' => 1,
                'is_alone_sale' => 1,
                'review_status' => [
                    'value' => 2,
                    'condition' => '>'
                ],
                'is_delete' => 0
            ];
            $goodsList = GoodsDataHandleService::getSellerGoodsDataList($seller_id, '*', 4, $where);

            $list = BaseRepository::getArrayCollapse($goodsList);
            $goods_id = BaseRepository::getKeyPluck($list, 'goods_id');
            $memberPriceList = GoodsDataHandleService::goodsMemberPrice($goods_id, $user_rank);
            $warehouseGoodsList = GoodsDataHandleService::getWarehouseGoodsDataList($goods_id, $warehouse_id);
            $warehouseAreaGoodsList = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods_id, $area_id, $area_city);

            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($seller_id);
            $collectStoreList = MerchantDataHandleService::getCollectStoreDataList($user_id, $seller_id);

            foreach ($store as $key => $val) {

                $merchant = $merchantList[$val['user_id']] ?? [];

                $store[$key]['rz_shopName'] = $merchant['shop_name'];
                $store[$key]['rz_shop_name'] = $merchant['shop_name'];

                $val['logo_thumb'] = $merchant['logo_thumb'] ?? '';
                $store[$key]['logo_thumb'] = $this->dscRepository->getImagePath(str_replace('../', '', $val['logo_thumb']));
                $store[$key]['count_gaze'] = $val['collect_count'] ? $val['collect_count'] : CollectStore::where('ru_id', $val['user_id'])->count();

                $sql = [
                    'where' => [
                        [
                            'name' => 'ru_id',
                            'value' => $val['user_id']
                        ]
                    ]
                ];
                $collectStore = BaseRepository::getArraySqlFirst($collectStoreList, $sql);

                $store[$key]['is_collect_shop'] = $collectStore ? 1 : 0;

                $res = $goodsList[$val['user_id']] ?? [];

                if ($res) {
                    foreach ($res as $idx => $goods) {
                        $price = [
                            'model_price' => isset($goods['model_price']) ? $goods['model_price'] : 0,
                            'user_price' => $memberPriceList[$goods['goods_id']]['user_price'] ?? 0,
                            'percentage' => $memberPriceList[$goods['goods_id']]['percentage'] ?? 0,
                            'warehouse_price' => $warehouseGoodsList[$goods['goods_id']]['warehouse_price'] ?? 0,
                            'region_price' => $warehouseAreaGoodsList[$goods['goods_id']]['region_price'] ?? 0,
                            'shop_price' => isset($goods['shop_price']) ? $goods['shop_price'] : 0,
                            'warehouse_promote_price' => $warehouseGoodsList[$goods['goods_id']]['warehouse_promote_price'] ?? 0,
                            'region_promote_price' => $warehouseAreaGoodsList[$goods['goods_id']]['region_promote_price'] ?? 0,
                            'promote_price' => isset($goods['promote_price']) ? $goods['promote_price'] : 0,
                            'wg_number' => $warehouseGoodsList[$goods['goods_id']]['region_number'] ?? 0,
                            'wag_number' => $warehouseAreaGoodsList[$goods['goods_id']]['region_number'] ?? 0,
                            'goods_number' => isset($goods['goods_number']) ? $goods['goods_number'] : 0
                        ];

                        $price = $this->goodsCommonService->getGoodsPrice($price, $user_discount / 100, $goods);

                        $res[$idx]['shop_price'] = $price['shop_price'];
                        $res[$idx]['shop_price_formated'] = $this->dscRepository->getPriceFormat($price['shop_price']);
                        $res[$idx]['market_price'] = $goods['market_price'];
                        $res[$idx]['market_price_formated'] = $this->dscRepository->getPriceFormat($goods['market_price']);
                        $res[$idx]['promote_price'] = $price['promote_price'];
                        $res[$idx]['promote_price_formated'] = $this->dscRepository->getPriceFormat($price['promote_price']);
                        $res[$idx]['goods_number'] = $price['goods_number'];
                        $res[$idx]['goods_thumb'] = $this->dscRepository->getImagePath($goods['goods_thumb']);

                        $res[$idx]['market_price'] = $this->dscRepository->getPriceFormat($res[$idx]['market_price'], true, false);
                        $res[$idx]['shop_price'] = $this->dscRepository->getPriceFormat($res[$idx]['shop_price'], true, false);
                        $res[$idx]['promote_price'] = $this->dscRepository->getPriceFormat($res[$idx]['promote_price'], true, false);
                    }
                }

                $store[$key]['goods'] = $res;
                $store[$key]['distance'] = distance_format($val['distance']);
                unset($store[$key]['get_goods']);
                unset($store[$key]['hope_login_name']);
            }
        }

        return $store;
    }

    /**
     * 获得店铺分类下的商品
     *
     * @param int $uid
     * @param int $ru_id
     * @param int $children
     * @param string $keywords
     * @param int $brand_id
     * @param int $size
     * @param int $page
     * @param string $sort
     * @param string $order
     * @param array $filter_attr
     * @param array $where_ext
     * @param string $type
     * @return array
     * @throws \Exception
     */
    public function getStoreGoodsList($uid = 0, $ru_id = 0, $children = 0, $keywords = '', $brand_id = 0, $size = 10, $page = 1, $sort = 'goods_id', $order = 'DESC', $filter_attr = [], $where_ext = [], $type = '')
    {
        /* 查询分类商品数据 */
        $res = Goods::where('user_id', $ru_id)
            ->where('is_on_sale', 1)
            ->where('is_alone_sale', 1)
            ->where('is_delete', 0)
            ->where('goods_number', '>', 0);

        if (config('shop.review_goods')) {
            $res = $res->whereIn('review_status', [3, 4, 5]);
        }

        if (!empty($type)) {
            if ($type == 'store_new') {
                $res = $res->where('store_new', 1);
            } elseif ($type == 'is_promote') {
                $time = TimeRepository::getGmTime();
                $res = $res->where('is_promote', 1)
                    ->where('promote_start_date', '<=', $time)
                    ->where('promote_end_date', '>=', $time);
            }
        }
        // 搜索
        if ($keywords) {
            $keywordsParam = [
                'keywords' => $keywords,
            ];
            $res = $res->where(function ($query) use ($keywordsParam) {
                $val = $keywordsParam['keywords'];
                $query->where(function ($query) use ($val) {
                    $query = $query->orWhere('goods_name', 'like', '%' . $val . '%');
                    $query = $query->orWhere('goods_sn', 'like', '%' . $val . '%');
                    $query->orWhere('keywords', 'like', '%' . $val . '%');
                });
            });
        } else {
            $goodsParam = [
                'children' => $children
            ];
            // 子分类
            $res = $res->where(function ($query) use ($goodsParam) {
                if (isset($goodsParam['children']) && $goodsParam['children']) {
                    $query->whereIn('user_cat', $goodsParam['children']);  // 商家分类id
                }
            });
        }

        if ($brand_id) {
            $brand_id = BaseRepository::getExplode($brand_id);
            $res = $res->whereIn('brand_id', $brand_id);
        }

        if (!empty($filter_attr)) {
            $goodsList = GoodsAttr::whereIn('goods_attr_id', $filter_attr)->get();
            $goodsList = $goodsList ? $goodsList->toArray() : [];

            if ($goodsList) {
                $res = $res->whereIn('goods_id', $goodsList);
            }
        }

        $where = [
            'warehouse_id' => $where_ext['warehouse_id'] ?? 0,
            'area_id' => $where_ext['area_id'] ?? 0,
            'area_city' => $where_ext['area_city'] ?? 0,
            'area_pricetype' => config('shop.area_pricetype') ?? 0,
        ];

        if (isset($where_ext['store_best']) && in_array($where_ext['store_best'], [0, 1])) {
            $res = $res->where('store_best', $where_ext['store_best']);
        }

        $res = $this->dscRepository->getAreaLinkGoods($res, $where['area_id'], $where['area_city']);

        if ($uid > 0) {
            $rank = $this->userCommonService->getUserRankByUid($uid);
            $user_rank = $rank['rank_id'];
            $discount = isset($rank['discount']) ? $rank['discount'] : 100;
        } else {
            $user_rank = 1;
            $discount = 100;
        }

        $res = $res->with([
            'getMemberPrice' => function ($query) use ($user_rank) {
                $query->where('user_rank', $user_rank);
            },
            'getWarehouseGoods' => function ($query) use ($where) {
                if (isset($where['warehouse_id']) && $where['warehouse_id']) {
                    $query->where('region_id', $where['warehouse_id']);
                }
            },
            'getWarehouseAreaGoods' => function ($query) use ($where) {
                $query = $query->where('region_id', $where['area_id']);

                if ($where['area_pricetype'] == 1) {
                    $query->where('city_id', $where['area_city']);
                }
            },
            'getShopInfo'
        ]);

        $start = ($page - 1) * $size;

        if ($start > 0) {
            $res = $res->offset($start);
        }

        if ($size > 0) {
            $res = $res->limit($size);
        }

        if ($sort == 'promote') {
            $sort = 'promote_price';
        }

        $res = $res->orderBy($sort, $order);

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {

            $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');

            $seller_id = BaseRepository::getKeyPluck($res, 'user_id');
            $sellerShopinfoList = MerchantDataHandleService::SellerShopinfoDataList($seller_id);
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($seller_id, 0, $sellerShopinfoList);
            $merchantUseGoodsLabelList = GoodsDataHandleService::gettMerchantUseGoodsLabelDataList($goods_id);
            $merchantNoUseGoodsLabelList = GoodsDataHandleService::getMerchantNoUseGoodsLabelDataList($goods_id);

            foreach ($res as $k => $row) {
                $price = [
                    'model_price' => isset($row['model_price']) ? $row['model_price'] : 0,
                    'user_price' => isset($row['get_member_price']['user_price']) ? $row['get_member_price']['user_price'] : 0,
                    'percentage' => isset($row['get_member_price']['percentage']) ? $row['get_member_price']['percentage'] : 0,
                    'warehouse_price' => isset($row['get_warehouse_goods']['warehouse_price']) ? $row['get_warehouse_goods']['warehouse_price'] : 0,
                    'region_price' => isset($row['get_warehouse_area_goods']['region_price']) ? $row['get_warehouse_area_goods']['region_price'] : 0,
                    'shop_price' => isset($row['shop_price']) ? $row['shop_price'] : 0,
                    'warehouse_promote_price' => isset($row['get_warehouse_goods']['warehouse_promote_price']) ? $row['get_warehouse_goods']['warehouse_promote_price'] : 0,
                    'region_promote_price' => isset($row['get_warehouse_area_goods']['region_promote_price']) ? $row['get_warehouse_area_goods']['region_promote_price'] : 0,
                    'promote_price' => isset($row['promote_price']) ? $row['promote_price'] : 0,
                    'wg_number' => isset($row['get_warehouse_goods']['region_number']) ? $row['get_warehouse_goods']['region_number'] : 0,
                    'wag_number' => isset($row['get_warehouse_area_goods']['region_number']) ? $row['get_warehouse_area_goods']['region_number'] : 0,
                    'goods_number' => isset($row['goods_number']) ? $row['goods_number'] : 0
                ];

                $price = $this->goodsCommonService->getGoodsPrice($price, $discount / 100, $row);

                $row['shop_price'] = $price['shop_price'];
                $row['promote_price'] = $price['promote_price'];
                $row['goods_number'] = $price['goods_number'];

                $arr[$k] = $row;

                $arr[$k]['model_price'] = $row['model_price'];

                if ($row['promote_price'] > 0) {
                    $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                } else {
                    $promote_price = 0;
                }

                //商品促销价格及属性促销价格
                $attr = $this->goodsAttrService->goodsAttr($row['goods_id']);
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
                }

                if ($attr_str) {
                    sort($attr_str);
                }

                /* 处理商品水印图片 */
                $watermark_img = '';
                if ($promote_price != 0) {
                    $watermark_img = "watermark_promote_small";
                } elseif ($row['store_new'] != 0) {
                    $watermark_img = "watermark_new_small";
                } elseif ($row['store_best'] != 0) {
                    $watermark_img = "watermark_best_small";
                } elseif ($row['store_hot'] != 0) {
                    $watermark_img = 'watermark_hot_small';
                }

                if ($watermark_img != '') {
                    $arr[$k]['watermark_img'] = $watermark_img;
                }
                $arr[$k]['sort_order'] = $row['sort_order'];

                $arr[$k]['goods_id'] = $row['goods_id'];
                $arr[$k]['goods_name'] = $row['goods_name'];
                $arr[$k]['name'] = $row['goods_name'];
                $arr[$k]['goods_brief'] = $row['goods_brief'];

                $arr[$k]['sales_volume'] = $row['sales_volume'];
                $arr[$k]['is_promote'] = $row['is_promote'];

                $arr[$k]['market_price'] = $this->dscRepository->getPriceFormat($row['market_price']);
                $arr[$k]['shop_price'] = $row['shop_price'];
                if ($promote_price > 0) {
                    $arr[$k]['shop_price_formated'] = $this->dscRepository->getPriceFormat($row['promote_price']);
                } else {
                    $arr[$k]['shop_price_formated'] = $this->dscRepository->getPriceFormat($row['shop_price']);
                }
                $arr[$k]['type'] = $row['goods_type'];
                $arr[$k]['promote_price'] = ($promote_price > 0) ? $this->dscRepository->getPriceFormat($promote_price) : '';
                $arr[$k]['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                $arr[$k]['goods_img'] = $this->dscRepository->getImagePath($row['goods_img']);

                if ($row['model_attr'] == 1) {
                    $prod = ProductsWarehouse::where('goods_id', $row['goods_id'])->where('warehouse_id', $where['warehouse_id'])->first();
                    $prod = $prod ? $prod->toArray() : [];
                } elseif ($row['model_attr'] == 2) {
                    $prod = ProductsArea::where('goods_id', $row['goods_id'])->where('area_id', $where['area_id'])->first();
                    $prod = $prod ? $prod->toArray() : [];
                } else {
                    $prod = Products::where('goods_id', $row['goods_id'])->first();
                    $prod = $prod ? $prod->toArray() : [];
                }

                if (empty($prod)) { //当商品没有属性库存时
                    $arr[$k]['prod'] = 1;
                } else {
                    $arr[$k]['prod'] = 0;
                }

                $arr[$k]['goods_number'] = $row['goods_number'];
                $arr[$k]['user_id'] = $row['user_id'];

                $sellerShopinfo = $merchantList[$row['user_id']] ?? [];
                $arr[$k]['country_icon'] = $sellerShopinfo['country_icon'] ?? '';

                $where = [
                    'user_id' => $arr[$k]['user_id'],
                    'goods_id' => $row['goods_id'],
                    'self_run' => $sellerShopinfo['self_run'] ?? 0,
                ];

                $goods_label_all = $this->goodsCommonService->getListGoodsLabelList($merchantUseGoodsLabelList, $merchantNoUseGoodsLabelList, $where);

                $arr[$k]['goods_label'] = $goods_label_all['goods_label'] ?? [];
                $arr[$k]['goods_label_suspension'] = $goods_label_all['goods_label_suspension'] ?? [];
            }
        }

        return $arr;
    }

    /**
     * 店铺的品牌
     *
     * @param int $ru_id
     * @return array
     */
    public function StoreBrand($ru_id = 0)
    {
        $data = MerchantsShopBrand::select('bid', 'bank_name_letter', 'brandName')
            ->where('user_id', $ru_id);

        $data = BaseRepository::getToArrayFirst($data);

        return $data;
    }

    /**
     * 店铺详情
     *
     * @param int $ru_id
     * @param int $user_id
     * @param string $platform
     * @return array|bool
     * @throws \Exception
     */
    public function StoreDetail($ru_id = 0, $user_id = 0, $platform = 'H5')
    {

        $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);
        $data = $merchantList[$ru_id] ?? [];

        $info = [];
        if ($data) {

            $sellerQrcode = SellerQrcode::where('ru_id', $data['ru_id']);
            $sellerQrcode = BaseRepository::getToArrayFirst($sellerQrcode);

            $MerchantsStepsFields = MerchantsStepsFields::where('user_id', $data['ru_id']);
            $MerchantsStepsFields = BaseRepository::getToArrayFirst($MerchantsStepsFields);

            $collect_count = CollectStore::where('ru_id', $data['user_id'])->count();
            $info['collect_count'] = intval($collect_count);

            $info = $this->shopdata($data);

            $info['shoprz_brand_name'] = $data['shoprz_brand_name']; // 主营品牌
            $info['shoprz_brandName'] = $info['shoprz_brand_name'];

            $grade_info = $this->get_seller_grade($ru_id);
            $info['grade_img'] = $this->dscRepository->getImagePath($grade_info['grade_img']);
            $info['logo_thumb'] = $this->dscRepository->getImagePath(str_replace('../', '', $data['logo_thumb']));
            $info['grade_name'] = $grade_info['grade_name'];
            $info['street_desc'] = $data['street_desc'];
            $info['count_gaze'] = intval($collect_count);
            $info['lat'] = $data['latitude'];
            $info['long'] = $data['longitude'];
            $info['is_collect_shop'] = $data['collect_count'];

            // 店铺二维码logo水印
            $qrcode_thumb = $sellerQrcode['qrcode_thumb'] ?? '';
            $water_logo = app(MerchantCommonService::class)->getLogoPath($qrcode_thumb);
            $linkExists = $this->dscRepository->remoteLinkExists($water_logo);
            if (!$linkExists) {
                $water_logo = null;
            }

            // 生成店铺首页二维码 H5、小程序
            $qrcode_res = $this->createShopQrcode($ru_id, $water_logo, $platform);
            $image_name = $qrcode_res['file'] ?? '';
            // 上传oss
            $this->dscRepository->getOssAddFile([$image_name]);
            // 返回图片路径与url
            $info['shop_qrcode_file'] = $image_name;
            $info['shop_qrcode'] = $qrcode_res['url'] ?? '';

            $basic_info = $data;
            if ($basic_info) {
                $basic_info = $sellerQrcode ? array_merge($basic_info, $sellerQrcode) : $basic_info;
                $basic_info = $MerchantsStepsFields ? array_merge($basic_info, $MerchantsStepsFields) : $basic_info;
            }

            $info['kf_tel'] = $basic_info['kf_tel'];

            if ($basic_info) {
                //营业执照有限期
                $basic_info['business_term'] = (isset($basic_info['business_term']) && !empty($basic_info['business_term'])) ? str_replace(',', '-', $basic_info['business_term']) : '';
                //处理营业执照所在地
                $license_comp_adress = '';
                if (isset($basic_info['license_comp_adress']) && $basic_info['license_comp_adress']) {

                    $adress = BaseRepository::getExplode($basic_info['license_comp_adress']);
                    $adress = ArrRepository::getArrayUnset($adress);

                    if (!empty($adress)) {

                        $regionList = RegionDataHandleService::getRegionDataList($adress, ['region_id', 'region_name']);

                        foreach ($adress as $v) {
                            $region_name = $regionList[$v]['region_name'] ?? '';
                            $region_name = $region_name ? $region_name : '';

                            $license_comp_adress .= $region_name;
                        }
                    }
                }
                $basic_info['license_comp_adress'] = $license_comp_adress;

                // 处理公司地址
                $company_located = '';
                if (isset($basic_info['company_located']) && $basic_info['company_located']) {
                    $adress = BaseRepository::getExplode($basic_info['company_located']);
                    $adress = ArrRepository::getArrayUnset($adress);

                    if (!empty($adress)) {
                        $regionList = RegionDataHandleService::getRegionDataList($adress, ['region_id', 'region_name']);
                        foreach ($adress as $v) {
                            $region_name = $regionList[$v]['region_name'] ?? '';
                            $company_located .= $region_name;
                        }
                    }
                    $company_located .= "&nbsp;&nbsp;" . $basic_info['company_adress'];
                }
                $basic_info['company_located'] = $company_located;
                $basic_info['merchants_url'] = dsc_url('/#/shopHome/' . $ru_id);
            }

            $info['basic_info'] = $basic_info;
        }

        return $info;
    }

    /**
     * 生成店铺二维码
     *
     * @param int $ru_id
     * @param string $water_logo
     * @param string $platform
     * @param int $type
     * @return string[]
     * @throws \Endroid\QrCode\Exception\QrCodeException
     */
    public function createShopQrcode($ru_id = 0, $water_logo = '', $platform = 'H5', $type = 0)
    {
        // 生成的文件位置
        $file_path = storage_public('data/attached/shop_qrcode/');
        if (!file_exists($file_path)) {
            make_dir($file_path);
        }

        // 客户端来源 h5或小程序
        $from_type = $platform == 'MP-WEIXIN' ? 1 : 0;

        // 输出二维码路径
        $out_img = $file_path . 'shop_qrcode_' . $from_type . '_' . $ru_id . '.png';

        // 生成二维码条件
        $generate = false;
        if (file_exists($out_img)) {
            $lastmtime = filemtime($out_img) + 3600 * 24 * 20; // 20天有效期
            if (time() >= $lastmtime) {
                $generate = true;
            }
        }

        if (!file_exists($out_img) || $generate == true) {

            if (file_exists(MOBILE_WXAPP) && $platform == 'MP-WEIXIN') {
                // 生成小程序码
                $app_page = 'pages/shop/shopHome/shopHome';

                // 推荐参数 $scene = 'ru_id=' . $ru_id. '&parent_id='. $user_id;
                $scene = $ru_id . '.' . 0;
                $qr_path = str_replace(storage_public(), '', $out_img);

                $wxacode = new \App\Modules\Wxapp\Libraries\Wxacode(0, $type);
                $wxacode->unlimit($app_page, $qr_path, $scene, '300px');

            } else {
                // 生成h5 二维码
                $url = dsc_url('/#/shopHome/' . $ru_id);

                if (!empty($water_logo)) {
                    QRCode::png($url, $out_img, $water_logo);
                } else {
                    QRCode::png($url, $out_img);
                }
            }

        }

        $image_name = 'data/attached/shop_qrcode/' . basename($out_img);

        return [
            'file' => $image_name,
            'url' => $this->dscRepository->getImagePath($image_name) . '?v=' . StrRepository::random(16)
        ];
    }

    /**
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    protected function shopdata($data = [])
    {
        $user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
        if (empty($user_id)) {
            return false;
        }

        $info['count_goods'] = $this->GoodsInfo($user_id);//所有商品

        $info['count_goods_new'] = $this->GoodsInfo($user_id, 1, 0); //所有新品
        $info['count_goods_promote'] = $this->GoodsInfo($user_id, 0, 1); //促销品
        $info['shop_id'] = $data['shop_id'];
        $info['ru_id'] = $data['user_id'];

        $info['shop_logo'] = $this->dscRepository->getImagePath(str_replace('../', '', $data['logo_thumb']));
        $info['street_thumb'] = $this->dscRepository->getImagePath(str_replace('../', '', $data['street_thumb']));
        $info['shop_name'] = $data['rz_shop_name'];
        $info['shop_desc'] = $data['shop_name'];

        $info['shop_start'] = $data['shop_expire_date_start'];
        $info['shop_address'] = $data['shop_address'];
        $info['shop_flash'] = $this->dscRepository->getImagePath($data['street_thumb']);
        $info['shop_wangwang'] = $data['kf_ww'];

        $info['shop_qq'] = $data['kf_qq'];
        $info['shop_tel'] = $data['kf_tel'];
        $info['is_im'] = $data['is_im'];
        $info['self_run'] = $data['self_run'];
        $info['meiqia'] = $data['meiqia'];
        $info['kf_appkey'] = $data['kf_appkey'];

        //评分 start
        if ($data['user_id'] > 0) {
            //商家所有商品评分类型汇总
            $merchants_goods_comment = $this->commentService->getMerchantsGoodsComment($data['user_id']);

            $info['commentrank'] = $merchants_goods_comment['cmt']['commentRank']['zconments']['score'] . lang('common.branch');//商品评分
            $info['commentserver'] = $merchants_goods_comment['cmt']['commentServer']['zconments']['score'] . lang('common.branch');//服务评分
            $info['commentdelivery'] = $merchants_goods_comment['cmt']['commentDelivery']['zconments']['score'] . lang('common.branch');//时效评分

            $info['commentrank_font'] = $this->font($merchants_goods_comment['cmt']['commentRank']['zconments']['score']);
            $info['commentserver_font'] = $this->font($merchants_goods_comment['cmt']['commentServer']['zconments']['score']);
            $info['commentdelivery_font'] = $this->font($merchants_goods_comment['cmt']['commentDelivery']['zconments']['score']);
        }

        return $info;
    }

    /**
     * 获取商家等级
     *
     * @param int $ru_id
     * @return string
     */
    public function get_seller_grade($ru_id = 0)
    {
        $merchantsGrade = MerchantsGrade::select('grade_id', 'add_time', 'year_num', 'amount')->where('ru_id', $ru_id);
        $merchantsGrade = BaseRepository::getToArrayFirst($merchantsGrade);

        $grade_id = $merchantsGrade['grade_id'];
        $sellerGrade = SellerGrade::select('grade_name', 'grade_introduce', 'white_bar')->where('id', $grade_id);
        $sellerGrade = BaseRepository::getToArrayFirst($sellerGrade);

        $res = BaseRepository::getArrayMerge($merchantsGrade, $sellerGrade);

        return $res;
    }

    public function GoodsInfo($user_id, $store_new = 0, $is_promote = 0)
    {
        $time = TimeRepository::getGmTime();

        $res = Goods::select('goods_id')
            ->where('is_delete', 0)
            ->where('is_alone_sale', 1)
            ->where('is_on_sale', 1)
            ->where('user_id', $user_id);

        if ($store_new > 0) {
            $res = $res->where('store_new', 1);
        }

        if ($is_promote > 0) {
            $res = $res->where('is_promote', 1)
                ->where('promote_start_date', '<', $time)
                ->where('promote_end_date', '>', $time);
        }

        if (config('shop.review_goods')) {
            $res = $res->whereIn('review_status', [3, 4, 5]);
        }

        $res = $res->count();

        return $res;
    }

    /**
     * 获取语言内容
     *
     * @param int $key
     * @return array|\Illuminate\Contracts\Translation\Translator|null|string
     * @throws \Exception
     */
    public function font($key = 0)
    {
        if ($key > 4) {
            return lang('store_street.font_high');
        } elseif ($key > 3) {
            return lang('store_street.font_middle');
        } else {
            return lang('store_street.font_low');
        }
    }

    /**
     * 查询所有商家的顶级分类
     *
     * @param int $cat_id
     * @return array
     */
    public function getMerCatStoreList($cat_id = 0)
    {
        $res = MerchantsShopInformation::select('user_shop_main_category AS user_cat', 'user_id')
            ->where('user_shop_main_category', '<>', '')
            ->where('merchants_audit', 1);

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        foreach ($res as $key => $row) {
            $row['cat_str'] = '';
            $row['user_cat'] = explode('-', $row['user_cat']);

            foreach ($row['user_cat'] as $uck => $ucrow) {
                if ($ucrow) {
                    $row['user_cat'][$uck] = explode(':', $ucrow);
                    if (!empty($row['user_cat'][$uck][0])) {
                        $row['cat_str'] .= $row['user_cat'][$uck][0] . ",";
                    }
                }
            }

            if ($row['cat_str']) {
                $row['cat_str'] = substr($row['cat_str'], 0, -1);
                $row['cat_str'] = explode(',', $row['cat_str']);
                if (in_array($cat_id, $row['cat_str']) || $cat_id == 0) {
                    $arr[] = $row['user_id'];
                }
            }
        }

        return $arr;
    }

    /**
     * 店铺定位位置
     *
     * @param int $lat
     * @param int $lng
     * @return string
     */
    public function StoreMap($lat = 0, $lng = 0)
    {
        $store = MerchantsShopInformation::from('merchants_shop_information as ms')
            ->select('ms.rz_shopname', 'ss.*')
            ->leftjoin('seller_shopinfo as ss', 'ms.user_id', 'ss.ru_id')
            ->where('ms.shop_close', 1)
            ->where('ms.is_street', 1);

        if ($lat && $lng) {
            $store = $store->selectRaw('( 6371 * acos( cos( radians(' . $lat . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $lng . ') ) + sin( radians(' . $lat . ') ) * sin( radians( latitude ) ) )) AS distance');
        }

        $store = $store->limit(10);

        $store = $store->orderBy('distance')->get();

        $seller_shopinfo = $store ? $store->toArray() : [];

        $list = [];
        $store = '';
        if ($seller_shopinfo) {
            foreach ($seller_shopinfo as $key => $vo) {
                $province = Region::where('region_id', $vo['province'])->value('region_name');
                $province = $province ? $province : '';

                $city = Region::where('region_id', $vo['city'])->value('region_name');
                $city = $city ? $city : '';

                $district = Region::where('region_id', $vo['district'])->value('region_name');
                $district = $district ? $district : '';

                $address = $province . $city . $district . $vo['shop_address'];

                $info = [
                    'coord' => $vo['latitude'] . ',' . $vo['longitude'],
                    'title' => empty($vo['shop_name']) ? $vo['rz_shopname'] : $vo['shop_name'],
                    'addr' => $address
                ];
                if (empty($vo['latitude']) || empty($vo['longitude'])) {
                    continue;
                }
                $list[] = urldecode(str_replace('=', ':', http_build_query($info, '', ';')));
            }
            if ($list) {
                $store = implode('|', $list);
            }
        }

        if (empty($store)) {
            $url = '';
        } else {
            $key = config('shop.tengxun_key');
            $url = 'http://apis.map.qq.com/tools/poimarker?type=0&marker=' . $store . '&key=' . $key . '&referer=ectouch';
        }
        return $url;
    }
}
