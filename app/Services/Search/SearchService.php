<?php

namespace App\Services\Search;

use App\Models\Attribute;
use App\Models\Coupons;
use App\Models\CouponsUser;
use App\Models\Goods;
use App\Models\GoodsAttr;
use App\Models\GoodsType;
use App\Models\SellerShopinfo;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Activity\CouponsService;
use App\Services\Category\CategoryService;
use App\Services\Common\AreaService;
use App\Services\Gallery\GalleryDataHandleService;
use App\Services\Goods\GoodsAttrService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Goods\GoodsGalleryService;
use App\Services\Goods\GoodsWarehouseService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;

/**
 * 商城搜索
 * Class SearchService
 * @package App\Services\Search
 */
class SearchService
{
    protected $couponsService;
    protected $goodsAttrService;
    protected $categoryService;
    protected $goodsCommonService;
    protected $merchantCommonService;
    protected $goodsGalleryService;
    protected $goodsWarehouseService;
    protected $dscRepository;
    protected $city = 0;
    protected $merchantDataHandleService;

    public function __construct(
        CouponsService $couponsService,
        GoodsAttrService $goodsAttrService,
        CategoryService $categoryService,
        GoodsCommonService $goodsCommonService,
        MerchantCommonService $merchantCommonService,
        GoodsGalleryService $goodsGalleryService,
        GoodsWarehouseService $goodsWarehouseService,
        DscRepository $dscRepository,
        MerchantDataHandleService $merchantDataHandleService
    )
    {
        $this->couponsService = $couponsService;
        $this->goodsAttrService = $goodsAttrService;
        $this->categoryService = $categoryService;
        $this->goodsCommonService = $goodsCommonService;
        $this->merchantCommonService = $merchantCommonService;
        $this->goodsGalleryService = $goodsGalleryService;
        $this->goodsWarehouseService = $goodsWarehouseService;
        $this->dscRepository = $dscRepository;
        $this->merchantDataHandleService = $merchantDataHandleService;

        /* 获取地区缓存 */
        $area_cookie = app(AreaService::class)->areaCookie();
        $this->city = $area_cookie['city'];
    }

    /**
     * @param $value
     * @return bool
     */
    public function isNotNull($value)
    {
        if (is_array($value)) {
            return (!empty($value['from'])) || (!empty($value['to']));
        } else {
            return !empty($value);
        }
    }

    /**
     * 获得可以检索的属性
     *
     * @param int $cat_id
     * @return array
     */
    public function getSeachableAttributes($cat_id = 0)
    {
        /* 获得可用的商品类型 */
        $attributes = [
            'cate' => [],
            'attr' => []
        ];

        $cat = GoodsType::where('enabled', 1);
        $cat = $cat->whereHasIn('getGoodsAttribute', function ($query) {
            $query->where('attr_index', '>', 0);
        });

        $cat = $cat->get();

        $cat = $cat ? $cat->toArray() : [];

        /* 获取可以检索的属性 */
        if (!empty($cat)) {
            foreach ($cat as $val) {
                $attributes['cate'][$val['cat_id']] = $val['cat_name'];
            }

            $res = Attribute::where('attr_index', '>', 0);

            if ($cat_id > 0) {
                $res = $res->where('cat_id', $cat_id)->where('cat_id', $cat[0]['cat_id']);
            }

            $res = $res->orderBy('cat_id')->orderBy('sort_order');

            $res = $res->get();

            $res = $res ? $res->toArray() : [];

            if ($res) {
                foreach ($res as $row) {
                    if ($row['attr_index'] == 1 && $row['attr_input_type'] == 1) {
                        $row['attr_values'] = str_replace("\r", '', $row['attr_values']);
                        $options = explode("\n", $row['attr_values']);

                        $attr_value = [];
                        foreach ($options as $opt) {
                            $attr_value[$opt] = $opt;
                        }
                        $attributes['attr'][] = [
                            'id' => $row['attr_id'],
                            'attr' => $row['attr_name'],
                            'options' => $attr_value,
                            'type' => 3
                        ];
                    } else {
                        $attributes['attr'][] = [
                            'id' => $row['attr_id'],
                            'attr' => $row['attr_name'],
                            'type' => $row['attr_index']
                        ];
                    }
                }
            }
        }

        return $attributes;
    }

    /**
     * 搜索页检索的属性
     *
     * @param array $attr_list
     * @param int $pickout
     * @return array
     */
    public function getGoodsAttrListGoods($attr_list = [], $pickout = 0)
    {
        $attr_list = BaseRepository::getExplode($attr_list);

        $attr_num = 0;
        $attr_arg = [];
        if ($attr_list && $pickout == 0) {
            $res = GoodsAttr::select('goods_id');

            $where = [
                'pickout' => $pickout,
                'attr_url' => ''
            ];

            foreach ($attr_list as $key => $val) {
                if ($this->isNotNull($val) && is_numeric($key)) {
                    $attr_num++;

                    $where['val'] = $val;
                    $where['attr_id'] = $key;

                    $res = $res->orWhere(function ($query) use ($where) {
                        if (is_array($where['val'])) {
                            $query = $query->where('attr_id', $where['attr_id']);
                            if (!empty($where['val']['from'])) {
                                if (is_numeric($where['val']['from'])) {
                                    $query = $query->where('attr_value', '>=', floatval($where['val']['from']));
                                } else {
                                    $query = $query->where('attr_value', '>=', $where['val']['from']);
                                }

                                $attr_arg["attr[" . $where['attr_id'] . "][from]"] = $where['val']['from'];
                                $where['attr_url'] .= "&amp;attr[" . $where['attr_id'] . "][from]=" . $where['val']['from'];
                            }

                            if (!empty($where['val']['to'])) {
                                if (is_numeric($where['val']['val']['to'])) {
                                    $query->where('attr_value', '<=', floatval($where['val']['to']));
                                } else {
                                    $query->where('attr_value', '<=', $where['val']['to']);
                                }

                                $attr_arg["attr[" . $where['attr_id'] . "][to]"] = $where['val']['to'];
                                $where['attr_url'] .= "&amp;attr[" . $where['attr_id'] . "][to]=[to]";
                            }
                        } else {
                            /* 处理选购中心过来的链接 */
                            if ($where['pickout']) {
                                $query->where('attr_id', $where['attr_id'])
                                    ->where('attr_value', $where['val']);
                            } else {
                                $query->where('attr_id', $where['attr_id'])
                                    ->where('attr_value', 'like', '%' . $this->dscRepository->mysqlLikeQuote($where['val']) . '%');
                            }

                            $where['attr_url'] .= "&amp;attr[" . $where['attr_id'] . "]=" . $where['val'];
                            $attr_arg["attr[" . $where['attr_id'] . "]"] = $where['val'];
                        }
                    });
                }
            }

            $res = $res->groupBy('goods_id');

            $res = BaseRepository::getToArrayGet($res);
            $res = BaseRepository::getFlatten($res);
        } else {
            $res = [];
            $where['attr_url'] = '';
        }

        $arr = [
            'res' => $res,
            'attr_url' => $where['attr_url'],
            'attr_arg' => $attr_arg,
            'attr_num' => $attr_num
        ];

        return $arr;
    }

    /**
     * 搜索页商品列表
     *
     * @param int $cat_id
     * @param int $brands_id
     * @param array $children
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param string $display
     * @param int $min
     * @param int $max
     * @param array $filter_attr
     * @param array $where_ext
     * @param array $goods_ids
     * @param array $keywords
     * @param string $intro
     * @param int $outstock
     * @param array $attr_in
     * @param int $cou_list
     * @param int $goods_num
     * @param int $size
     * @param int $page
     * @param string $sort
     * @param string $order
     * @return array
     * @throws \Exception
     */
    public function getSearchGoodsList($cat_id = 0, $brands_id = 0, $children = [], $warehouse_id = 0, $area_id = 0, $area_city = 0, $display = '', $min = 0, $max = 0, $filter_attr = [], $where_ext = [], $goods_ids = [], $keywords = [], $intro = '', $outstock = 0, $attr_in = [], $cou_list = 0, $goods_num = 0, $size = 10, $page = 1, $sort = 'goods_id', $order = 'desc')
    {
        $presale_goods_id = $where_ext['presale_goods_id'] ?? [];
        $user_cou = isset($_REQUEST['user_cou']) && !empty($_REQUEST['user_cou']) ? intval($_REQUEST['user_cou']) : 0;

        $time = TimeRepository::getGmTime();

        /* 查询扩展分类数据 */
        $extension_goods = [];
        if ($cat_id > 0) {
            $extension_goods = $this->goodsCommonService->getCategoryGoodsId($children);
        }

        $goodsParam = [
            'cat_id' => $cat_id,
            'children' => $children,
            'extension_goods' => $extension_goods,
        ];

        /* 查询分类商品数据 */
        $res = Goods::where('is_alone_sale', 1)
            ->where('is_delete', 0)
            ->where('is_show', 1)
            ->where(function ($query) use ($goodsParam) {
                if ($goodsParam['cat_id'] > 0) {
                    $query = $query->whereIn('cat_id', $goodsParam['children']);
                }

                if ($goodsParam['extension_goods']) {
                    $query->orWhere(function ($query) use ($goodsParam) {
                        $query->whereIn('goods_id', $goodsParam['extension_goods']);
                    });
                }
            });

        if (isset($where_ext['self']) && $where_ext['self'] == 1) {
            $res = $res->where(function ($query) use ($where_ext) {
                $query->where('user_id', 0)->orWhere(function ($query) use ($where_ext) {
                    $query->whereIn('user_id', $where_ext['self_run_list']);
                });
            });
        }

        if ($min > 0) {
            $res = $res->where('shop_price', '>=', $min);
        }

        if ($max > 0) {
            $res = $res->where('shop_price', '<=', $max);
        }

        if (isset($where_ext['have']) && $where_ext['have'] == 1) {
            $res = $res->where('goods_number', '>', 0);
        }

        if (isset($where_ext['ship']) && ($where_ext['ship'] == 1)) {
            $res = $res->where('is_shipping', 1);
        }

        if (config('shop.review_goods')) {
            $res = $res->whereIn('review_status', [3, 4, 5]);
        }

        /* 关联地区 */
        $res = $this->dscRepository->getAreaLinkGoods($res, $area_id, $area_city);

        $goods_arr = [
            'goods_ids' => $goods_ids,
            'keywords' => $keywords,
            'presale_goods_id' => $presale_goods_id,
            'brand_id' => $brands_id,
            'brand_name' => $where_ext['brand_name'] ?? '',
            'min' => $min,
            'max' => $max,
            'time' => $time
        ];

        if ($goods_arr['goods_ids']) {
            $res = $res->whereIn('goods_id', $goods_arr['goods_ids']);
        }

        if ($goods_arr['keywords']) {
            $brandKeyword = $this->goodsCommonService->keywordFilter($goods_arr);
            if ($brandKeyword) {
                $res = $this->goodsCommonService->searchKeywordFilter($res, $brandKeyword, $goods_arr);
            } else {
                $res = $this->goodsCommonService->searchKeywords($res, $goods_arr);
            }
        } else {

            if ($goods_arr['brand_id']) {
                $goods_arr['brand_id'] = BaseRepository::getExplode($goods_arr['brand_id']);
                $res = $res->whereIn('brand_id', $goods_arr['brand_id']);
            }

            $res = $res->where(function ($query) use ($presale_goods_id) {
                $query = $query->where('is_on_sale', 1);

                //兼容预售
                if ($presale_goods_id) {
                    $query->orWhere(function ($query) use ($presale_goods_id) {
                        $query->where('is_on_sale', 0)
                            ->whereIn('goods_id', $presale_goods_id);
                    });
                }
            });
        }

        if ($intro) {
            switch ($_REQUEST['intro']) {
                case 'best':
                    $res = $res->where('is_best', 1);
                    break;
                case 'new':
                    $res = $res->where('is_new', 1);
                    break;
                case 'hot':
                    $res = $res->where('is_hot', 1);
                    break;
                case 'promotion':
                    $res = $res->where('promote_price', '>', 0)
                        ->where('promote_start_date', '<=', $time)
                        ->where('promote_end_date', '>=', $time);
                    break;
            }
        }

        if ($outstock) {
            $res = $res->where('goods_number', '>', 0);
        }

        /* 如果检索条件都是无效的，就不用检索 */
        if (isset($attr_in['attr_num']) && $attr_in['attr_num'] > 0) {
            $res = $res->whereIn('goods_id', $attr_in['res']);
        }

        /* 会员中心储值卡  分类跳转 */
        if ($cou_list['cou_id'] > 0) {
            $cou_data = Coupons::where('cou_id', $cou_list['cou_id'])
                ->where('status', COUPON_STATUS_EFFECTIVE);
            $cou_data = BaseRepository::getToArrayFirst($cou_data);

            if ($cou_data) {
                //如果是购物送(任务集市)
                if ($cou_data['cou_type'] == VOUCHER_SHOPING && empty($user_cou)) {
                    $user_id = session('user_id', 0);
                    $cou_count = CouponsUser::where('is_delete', 0)->where('user_id', $user_id)->where('cou_id', $cou_list['cou_id'])->count();
                    $user_cou = $cou_count > 0 && $user_id > 0 ? 1 : 0;  // 0没有券， 去购买可赠券商品

                    if ($user_cou == 0) {
                        $res = $res->where('user_id', $cou_data['ru_id']);

                        if ($cou_data['cou_ok_goods']) {
                            $cou_ok_goods = BaseRepository::getExplode($cou_data['cou_ok_goods']);
                            $res = $res->whereIn('goods_id', $cou_ok_goods);
                        } elseif ($cou_data['cou_ok_cat']) {
                            $cou_children = $this->couponsService->getCouChildren($cou_data['cou_ok_cat']);
                            $cou_children = BaseRepository::getExplode($cou_children);
                            if ($cou_children) {
                                $res = $res->whereIn('cat_id', $cou_children);
                            }
                        }
                    } else {
                        $res = $res->where('user_id', $cou_data['ru_id']);

                        if ($cou_data['cou_goods']) {
                            $cou_data['cou_goods'] = !is_array($cou_data['cou_goods']) ? explode(",", $cou_data['cou_goods']) : [];
                            $res = $res->whereIn('goods_id', $cou_data['cou_goods']);
                        } elseif ($cou_data['spec_cat']) {
                            $cou_children = $this->couponsService->getCouChildren($cou_data['spec_cat']);
                            $cou_children = BaseRepository::getExplode($cou_children);
                            if ($cou_children) {
                                $res = $res->whereIn('cat_id', $cou_children);
                            }
                        }
                    }
                } else {
                    $res = $res->where('user_id', $cou_data['ru_id']);

                    if ($cou_data['cou_goods']) {
                        $cou_data['cou_goods'] = !is_array($cou_data['cou_goods']) ? explode(",", $cou_data['cou_goods']) : [];
                        $res = $res->whereIn('goods_id', $cou_data['cou_goods']);
                    } elseif ($cou_data['spec_cat']) {
                        $cou_children = $this->couponsService->getCouChildren($cou_data['spec_cat']);
                        $cou_children = BaseRepository::getExplode($cou_children);
                        if ($cou_children) {
                            $res = $res->whereIn('cat_id', $cou_children);
                        }
                    }
                }
            }
        }

        if (!empty($filter_attr)) {
            $attrList = GoodsAttr::whereIn('goods_attr_id', $filter_attr);
            $attrList = BaseRepository::getToArrayGet($attrList);
            $attr_value = BaseRepository::getKeyPluck($attrList, 'attr_value');

            $goodsList = GoodsAttr::whereIn('attr_value', $attr_value);
            $goodsList = BaseRepository::getToArrayGet($goodsList);
            $goodsList = BaseRepository::getKeyPluck($goodsList, 'goods_id');

            if ($goodsList) {
                $res = $res->whereIn('goods_id', $goodsList);
            }
        }

        /* 查询仅自营和标识自营店铺的商品 */
        if (isset($where_ext['self']) && $where_ext['self'] == 1) {
            $res = $res->where(function ($query) {
                $query->where('user_id', 0)->orWhere(function ($query) {
                    $query->whereHasIn('getShopInfo', function ($query) {
                        $query->where('self_run', 1);
                    });
                });
            });
        }

        $user_rank = session('user_rank', 0);
        $discount = session('discount', 1);

        //瀑布流加载分类商品 by wu
        if ($goods_num) {
            $start = $goods_num;
        } else {
            $start = ($page - 1) * $size;
        }

        if (!empty($keywords) && !empty($goods_arr['brand_name'])) {
            $keywords = BaseRepository::getArrayPrepend($keywords, $goods_arr['brand_name']);
            $keywords = $keywords ? BaseRepository::getArrayUnique($keywords) : [];
        }

        if (strpos($sort, 'goods_id') !== false) {
            // 排序关键词匹配度 默认时优先匹配关键词
            if (!empty($keywords)) {
                foreach ($keywords as $value) {
                    $res = $res->orderByRaw("LOCATE('" . $value . "',goods_name) DESC");
                }
            }

            $sort = "sort_order";
            $res = $res->orderBy('weights', 'DESC'); // 权重值
            $res = $res->orderBy($sort, $order)->orderBy('goods_id', $order);
        } else {

            $res = $res->orderBy($sort, $order);
            $res = $res->orderBy('weights', 'DESC'); // 权重值

            // 排序关键词匹配度 有筛选时优先匹配筛选
            if (!empty($keywords)) {
                foreach ($keywords as $value) {
                    $res = $res->orderByRaw("LOCATE('" . $value . "',goods_name) DESC");
                }
            }
        }

        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($size > 0) {
            $res = $res->take($size);
        }

        $res = BaseRepository::getToArrayGet($res);

        $idx = 0;
        $arr = [];

        if ($res) {

            if (!empty($keywords) && !empty($goods_arr['brand_name'])) {
                $keywords = ArrRepository::getArrCollapse([$keywords, [$goods_arr['brand_name']]]);
            }

            $arr_keyword = $keywords;

            if (isset($arr_keyword) && $arr_keyword) {
                $arr_keyword = array_values($arr_keyword);

                $built_key = "<font style='color:#ec5151;'></font>"; //高亮显示HTML
                //过滤掉高亮显示HTML可以匹配上的项，防止页面html错乱
                foreach ($arr_keyword as $key => $val_keyword) {
                    if (strpos($built_key, $val_keyword) !== false || empty($val_keyword)) {
                        unset($arr_keyword[$key]);
                    }
                }
            }

            $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');

            $memberPrice = GoodsDataHandleService::goodsMemberPrice($goods_id, $user_rank);
            $warehouseGoods = GoodsDataHandleService::getWarehouseGoodsDataList($goods_id, $warehouse_id);
            $warehouseAreaGoods = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods_id, $area_id, $area_city);
            $productsWarehouse = GoodsDataHandleService::getProductsWarehouseDataList($goods_id, $warehouse_id);
            $productsArea = GoodsDataHandleService::getProductsAreaDataList($goods_id, $area_id, $area_city);
            $products = GoodsDataHandleService::getProductsDataList($goods_id);
            $commentGoodsList = GoodsDataHandleService::CommentGoodsReviewCount($goods_id, ['comment_id', 'id_value']);
            $collectGoodsList = GoodsDataHandleService::CollectGoodsDataList($goods_id, ['goods_id']);
            $presaleActivityList = GoodsDataHandleService::PresaleActivityDataList($goods_id);

            $merchantUseGoodsLabelList = GoodsDataHandleService::gettMerchantUseGoodsLabelDataList($goods_id);
            $merchantNoUseGoodsLabelList = GoodsDataHandleService::getMerchantNoUseGoodsLabelDataList($goods_id);

            $seller_id = BaseRepository::getKeyPluck($res, 'user_id');

            $shopInformation = $this->merchantDataHandleService->MerchantsShopInformationDataList($seller_id);
            $sellerShopinfo = $this->merchantDataHandleService->SellerShopinfoDataList($seller_id);
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($seller_id, 0, $sellerShopinfo, $shopInformation);

            $galleryList = GalleryDataHandleService::getGoodsGalleryDataList($goods_id);

            foreach ($res as $row) {

                $shop_price = $row['shop_price'];

                $price = [
                    'model_price' => isset($row['model_price']) ? $row['model_price'] : 0,
                    'user_price' => $memberPrice[$row['goods_id']]['user_price'] ?? 0,
                    'percentage' => $memberPrice[$row['goods_id']]['percentage'] ?? 0,
                    'warehouse_price' => $warehouseGoods[$row['goods_id']]['warehouse_price'] ?? 0,
                    'region_price' => $warehouseAreaGoods[$row['goods_id']]['region_price'] ?? 0,
                    'shop_price' => isset($row['shop_price']) ? $row['shop_price'] : 0,
                    'warehouse_promote_price' => $warehouseGoods[$row['goods_id']]['warehouse_promote_price'] ?? 0,
                    'region_promote_price' => $warehouseAreaGoods[$row['goods_id']]['region_promote_price'] ?? 0,
                    'promote_price' => isset($row['promote_price']) ? $row['promote_price'] : 0,
                    'wg_number' => $warehouseGoods[$row['goods_id']]['region_number'] ?? 0,
                    'wag_number' => $warehouseAreaGoods[$row['goods_id']]['region_number'] ?? 0,
                    'goods_number' => isset($row['goods_number']) ? $row['goods_number'] : 0
                ];

                if (config('shop.customer_service') == 0) {
                    $seller_id = 0;
                } else {
                    $seller_id = $row['user_id'];
                }

                $shop_information = $merchantList[$row['user_id']] ?? []; //通过ru_id获取到店铺信息;

                $row['is_im'] = 0;
                if (isset($shop_information['is_im']) && !empty($shop_information['is_im']) && empty($shop_information['kf_qq'])) {
                    $row['is_im'] = $shop_information['is_im'];
                }

                //判断当前商家是平台,还是入驻商家 bylu
                if ($seller_id == 0) {
                    //判断平台是否开启了IM在线客服
                    $kf_im_switch = SellerShopinfo::where('ru_id', 0)->value('kf_im_switch');

                    if ($kf_im_switch) {
                        $row['is_dsc'] = true;
                    } else {
                        $row['is_dsc'] = false;
                    }
                } else {
                    $row['is_dsc'] = false;
                }

                $price = $this->goodsCommonService->getGoodsPrice($price, $discount, $row);
                $row['shop_price'] = $price['shop_price'];
                $row['promote_price'] = $price['promote_price'];
                $row['goods_number'] = $price['goods_number'];

                $arr[$idx] = $row;

                if ($row['promote_price'] > 0) {
                    $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                } else {
                    $promote_price = 0;
                }

                $sql = [
                    'where' => [
                        [
                            'name' => 'is_finished',
                            'value' => 0
                        ],
                        [
                            'name' => 'start_time',
                            'condition' => '<',
                            'value' => $time
                        ],
                        [
                            'name' => 'end_time',
                            'condition' => '>',
                            'value' => $time
                        ]
                    ]
                ];

                $goodsPresale = $presaleActivityList[$row['goods_id']] ?? [];
                $presale = BaseRepository::getArraySqlFirst($goodsPresale, $sql);

                /* 预售商品 start */
                if ($presale) {
                    $arr[$idx]['presale'] = lang('common.presell');
                    $arr[$idx]['act_id'] = $presale['act_id'];
                    $arr[$idx]['act_name'] = $presale['act_name'];
                    $arr[$idx]['thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                    $arr[$idx]['goods_img'] = $this->dscRepository->getImagePath($row['goods_img']);
                    $arr[$idx]['purl'] = $this->dscRepository->buildUri('presale', ['act' => 'view', 'presaleid' => $presale['act_id']], $presale['goods_name']);
                    $arr[$idx]['rz_shop_name'] = $shop_information['shop_name'] ?? ''; //店铺名称
                    $arr[$idx]['start_time_date'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $presale['start_time']);
                    $arr[$idx]['end_time_date'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $presale['end_time']);

                    //@Author guan 关键字高亮显示 start
                    $act_name_keyword = "<span>" . $presale['act_name'] . "</span>";
                    foreach ($arr_keyword as $key => $val_keyword) {
                        $act_name_keyword = preg_replace("/(>.*)($val_keyword)(.*<)/Ui", "$1<font style='color:#ec5151;'>$val_keyword</font>\$3", $act_name_keyword);
                    }
                    $arr[$idx]['act_name_keyword'] = $act_name_keyword;
                    //@Author guan 关键字高亮显示 end

                    if ($presale['start_time'] >= $time) {
                        $arr[$idx]['no_start'] = 1;
                    }
                    if ($presale['end_time'] <= $time) {
                        $arr[$idx]['already_over'] = 1;
                    }
                }
                /* 预售商品 end */

                // 最小起订量
                if ($row['is_minimum'] == 1 && $time > $row['minimum_start_date'] && $time < $row['minimum_end_date']) {
                    $arr[$idx]['is_minimum'] = 1;
                } else {
                    $arr[$idx]['is_minimum'] = 0;
                    $arr[$idx]['minimum'] = 0;
                }

                /* 处理商品水印图片 */
                $watermark_img = '';

                if ($promote_price > 0) {
                    $watermark_img = "watermark_promote_small";
                } elseif ($row['is_new'] != 0) {
                    $watermark_img = "watermark_new_small";
                } elseif ($row['is_best'] != 0) {
                    $watermark_img = "watermark_best_small";
                } elseif ($row['is_hot'] != 0) {
                    $watermark_img = 'watermark_hot_small';
                }

                if ($watermark_img != '') {
                    $arr[$idx]['watermark_img'] = $watermark_img;
                }

                $arr[$idx]['goods_id'] = $row['goods_id'];

                /* 商品仓库货品 */
                if ($row['model_price'] == 1) {
                    $prod = $productsWarehouse[$row['goods_id']] ?? [];
                } elseif ($row['model_price'] == 2) {
                    $prod = $productsArea[$row['goods_id']] ?? [];
                } else {
                    $prod = $products[$row['goods_id']] ?? [];
                }

                if (empty($prod)) { //当商品没有属性库存时
                    $arr[$idx]['prod'] = 1;
                } else {
                    $arr[$idx]['prod'] = 0;
                }

                if ($display == 'grid') {
                    //@Author guan 关键字高亮显示 start
                    $row['goods_name'] = $this->dscRepository->subStr($row['goods_name'], config('shop.goods_name_length'));
                    $goods_name_keyword = "<span>" . $row['goods_name'] . "</span>";
                    foreach ($arr_keyword as $key => $val_keyword) {
                        if (preg_match("/(>.*)($val_keyword)(.*<)/Ui", $goods_name_keyword)) {
                            $goods_name_keyword = preg_replace("/(>.*)($val_keyword)(.*<)/Ui", "$1<font style='color:#ec5151;'>$val_keyword</font>\$3", $row['goods_name']);
                        }
                    }

                    $arr[$idx]['goods_name_keyword'] = config('shop.goods_name_length') > 0 ? $goods_name_keyword : $goods_name_keyword;
                    //模版页面样式错误，为模版页面的的goods_name改为goods_name2。以防止样式错误。
                    $arr[$idx]['goods_name'] = config('shop.goods_name_length') > 0 ? $row['goods_name'] : $row['goods_name'];
                    //@Author guan 关键字高亮显示 end
                } else {
                    //@Author guan 关键字高亮显示 start
                    $goods_name_keyword = "<span>" . $row['goods_name'] . "</span>";

                    if (isset($arr_keyword) && $arr_keyword) {
                        foreach ($arr_keyword as $key => $val_keyword) {
                            if (preg_match("/(>.*)($val_keyword)(.*<)/Ui", $goods_name_keyword)) {
                                $goods_name_keyword = preg_replace("/(>.*)($val_keyword)(.*<)/Ui", "$1<font style='color:#ec5151;'>$val_keyword</font>\$3", $goods_name_keyword);
                            }
                        }
                    }

                    $arr[$idx]['goods_name_keyword'] = $goods_name_keyword;
                    $arr[$idx]['goods_name'] = $row['goods_name'];
                    //@Author guan 关键字高亮显示 end
                }

                if ($promote_price > 0) {
                    $row['is_promote'] = 1;
                    $row['shop_price'] = $shop_price;
                } else {
                    $row['is_promote'] = 0;
                }

                $goodsSelf = false;
                if ($row['user_id'] == 0) {
                    $goodsSelf = true;
                }

                $arr[$idx]['is_promote'] = $row['is_promote'];
                $arr[$idx]['goods_number'] = $row['goods_number'];
                $arr[$idx]['type'] = $row['goods_type'];
                $arr[$idx]['sales_volume'] = $row['sales_volume'];
                $arr[$idx]['market_price'] = $this->dscRepository->getPriceFormat($row['market_price'], true, true, $goodsSelf);
                $arr[$idx]['shop_price'] = $this->dscRepository->getPriceFormat($row['shop_price'], true, true, $goodsSelf);
                $arr[$idx]['promote_price'] = ($promote_price > 0) ? $this->dscRepository->getPriceFormat($promote_price, true, true, $goodsSelf) : '';
                $arr[$idx]['goods_brief'] = $row['goods_brief'];
                $arr[$idx]['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                $arr[$idx]['goods_img'] = $this->dscRepository->getImagePath($row['goods_img']);
                $arr[$idx]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
                $arr[$idx]['is_shipping'] = $row['is_shipping'];

                /* 评分数 */
                $sql = [
                    'where' => [
                        [
                            'name' => 'id_value',
                            'value' => $row['goods_id'],
                        ]
                    ]
                ];

                $comment_list = BaseRepository::getArraySqlGet($commentGoodsList, $sql, 1);
                $review_count = BaseRepository::getArrayCount($comment_list);

                $arr[$idx]['review_count'] = $review_count;

                $arr[$idx]['pictures'] = $this->goodsGalleryService->getGoodsGallery($row['goods_id'], $galleryList, $row['goods_thumb'], 6); // 商品相册

                $arr[$idx]['rz_shop_name'] = $shop_information['shop_name'] ?? '';
                $arr[$idx]['user_id'] = $row['user_id'];

                $build_uri = [
                    'urid' => $row['user_id'],
                    'append' => $arr[$idx]['rz_shop_name']
                ];

                $domain_url = $this->merchantCommonService->getSellerDomainUrl($row['user_id'], $build_uri);
                $arr[$idx]['store_url'] = $domain_url['domain_name'];

                $arr[$idx]['is_new'] = $row['is_new'];
                $arr[$idx]['is_best'] = $row['is_best'];
                $arr[$idx]['is_hot'] = $row['is_hot'];
                $arr[$idx]['user_id'] = $row['user_id'];
                $arr[$idx]['self_run'] = $shop_information['self_run'] ?? 0;

                $basic_info = $sellerShopinfo[$row['user_id']] ?? [];

                $chat = $this->dscRepository->chatQq($basic_info);
                $arr[$idx]['kf_type'] = $chat['kf_type'];
                $arr[$idx]['kf_qq'] = $chat['kf_qq'];
                $arr[$idx]['kf_ww'] = $chat['kf_ww'];

                /* 商品关注度 */
                $sql = [
                    'where' => [
                        [
                            'name' => 'goods_id',
                            'value' => $row['goods_id'],
                        ],
                        [
                            'name' => 'is_attention',
                            'value' => 1
                        ],
                        [
                            'name' => 'user_id',
                            'value' => session('user_id', 0)
                        ]
                    ]
                ];

                $collect_list = BaseRepository::getArraySqlGet($collectGoodsList, $sql, 1);
                $collect_count = BaseRepository::getArrayCount($collect_list);

                $arr[$idx]['is_collect'] = $collect_count;

                // 活动标签
                $where = [
                    'user_id' => $arr[$idx]['user_id'],
                    'goods_id' => $row['goods_id'],
                    'self_run' => $arr[$idx]['self_run'],
                ];
                $goods_label_all = $this->goodsCommonService->getListGoodsLabelList($merchantUseGoodsLabelList, $merchantNoUseGoodsLabelList, $where);

                $arr[$idx]['goods_label'] = $goods_label_all['goods_label'] ?? [];
                $arr[$idx]['goods_label_suspension'] = $goods_label_all['goods_label_suspension'] ?? [];

                $arr[$idx]['country_icon'] = $shop_information['country_icon'] ?? '';

                $idx++;
            }
        }

        if ($display == 'grid') {
            if (count($arr) % 2 != 0) {
                $arr[] = [];
            }
        }

        /* 返回商品总数 */
        return $arr;
    }

    /**
     * 搜索页商品数量
     *
     * @param int $cat_id
     * @param int $brands_id
     * @param array $children
     * @param int $area_id
     * @param int $area_city
     * @param int $min
     * @param int $max
     * @param array $filter_attr
     * @param array $where_ext
     * @param array $goods_ids
     * @param array $keywords
     * @param string $intro
     * @param int $outstock
     * @param array $attr_in
     * @param int $cou_list
     * @return mixed
     * @throws \Exception
     */
    public function getSearchGoodsCount($cat_id = 0, $brands_id = 0, $children = [], $area_id = 0, $area_city = 0, $min = 0, $max = 0, $filter_attr = [], $where_ext = [], $goods_ids = [], $keywords = [], $intro = '', $outstock = 0, $attr_in = [], $cou_list = 0)
    {
        $presale_goods_id = $where_ext['presale_goods_id'] ?? [];

        $user_cou = isset($_REQUEST['user_cou']) && !empty($_REQUEST['user_cou']) ? intval($_REQUEST['user_cou']) : 0;
        $time = TimeRepository::getGmTime();

        /* 查询扩展分类数据 */
        $extension_goods = [];
        if ($cat_id > 0) {
            $extension_goods = $this->goodsCommonService->getCategoryGoodsId($children);;
        }

        $goodsParam = [
            'cat_id' => $cat_id,
            'children' => $children,
            'extension_goods' => $extension_goods,
        ];

        /* 查询分类商品数据 */
        $res = Goods::where('is_alone_sale', 1)
            ->where('is_delete', 0)
            ->where('is_show', 1)
            ->where(function ($query) use ($goodsParam) {
                if ($goodsParam['cat_id'] > 0) {
                    $query = $query->whereIn('cat_id', $goodsParam['children']);
                }

                if ($goodsParam['extension_goods']) {
                    $query->orWhere(function ($query) use ($goodsParam) {
                        $query->whereIn('goods_id', $goodsParam['extension_goods']);
                    });
                }
            });

        if (isset($where_ext['self']) && $where_ext['self'] == 1) {
            $res = $res->where(function ($query) use ($where_ext) {
                $query->where('user_id', 0)->orWhere(function ($query) use ($where_ext) {
                    $query->whereIn('user_id', $where_ext['self_run_list']);
                });
            });
        }

        if ($min > 0) {
            $res = $res->where('shop_price', '>=', $min);
        }

        if ($max > 0) {
            $res = $res->where('shop_price', '<=', $max);
        }

        if (isset($where_ext['have']) && $where_ext['have'] == 1) {
            $res = $res->where('goods_number', '>', 0);
        }

        if (isset($where_ext['ship']) && ($where_ext['ship'] == 1)) {
            $res = $res->where('is_shipping', 1);
        }

        if (config('shop.review_goods')) {
            $res = $res->whereIn('review_status', [3, 4, 5]);
        }

        /* 关联地区 */
        $res = $this->dscRepository->getAreaLinkGoods($res, $area_id, $area_city);

        $goods_arr = [
            'goods_ids' => $goods_ids,
            'brand_id' => $brands_id,
            'brand_name' => $where_ext['brand_name'] ?? '',
            'keywords' => $keywords,
            'presale_goods_id' => $presale_goods_id,
            'min' => $min,
            'max' => $max,
            'time' => $time
        ];

        if ($goods_arr['goods_ids']) {
            $res = $res->whereIn('goods_id', $goods_arr['goods_ids']);
        }

        if ($goods_arr['keywords']) {
            $brandKeyword = $this->goodsCommonService->keywordFilter($goods_arr);
            if ($brandKeyword) {
                $res = $this->goodsCommonService->searchKeywordFilter($res, $brandKeyword, $goods_arr);
            } else {
                $res = $this->goodsCommonService->searchKeywords($res, $goods_arr);
            }
        } else {

            if ($goods_arr['brand_id']) {
                $goods_arr['brand_id'] = BaseRepository::getExplode($goods_arr['brand_id']);
                $res = $res->whereIn('brand_id', $goods_arr['brand_id']);
            }

            $res = $res->where(function ($query) use ($presale_goods_id) {
                $query = $query->where('is_on_sale', 1);

                //兼容预售
                if ($presale_goods_id) {
                    $query->orWhere(function ($query) use ($presale_goods_id) {
                        $query->where('is_on_sale', 0)
                            ->whereIn('goods_id', $presale_goods_id);
                    });
                }
            });
        }

        if ($intro) {
            switch ($_REQUEST['intro']) {
                case 'best':
                    $res = $res->where('is_best', 1);
                    break;
                case 'new':
                    $res = $res->where('is_new', 1);
                    break;
                case 'hot':
                    $res = $res->where('is_hot', 1);
                    break;
                case 'promotion':
                    $time = TimeRepository::getGmTime();
                    $res = $res->where('promote_price', '>', 0)
                        ->where('promote_start_date', '<=', $time)
                        ->where('promote_end_date', '>=', $time);
                    break;
            }
        }

        if ($outstock) {
            $res = $res->where('goods_number', '>', 0);
        }

        /* 如果检索条件都是无效的，就不用检索 */
        if (isset($attr_in['attr_num']) && $attr_in['attr_num'] > 0) {
            $res = $res->whereIn('goods_id', $attr_in['res']);
        }

        /* 会员中心储值卡  分类跳转 */
        if ($cou_list['cou_id'] > 0) {
            $cou_data = Coupons::where('cou_id', $cou_list['cou_id'])
                ->where('status', COUPON_STATUS_EFFECTIVE);
            $cou_data = BaseRepository::getToArrayFirst($cou_data);

            if ($cou_data) {
                //如果是购物送(任务集市)
                if ($cou_data['cou_type'] == VOUCHER_SHOPING && empty($user_cou)) {
                    $user_id = session('user_id', 0);
                    $cou_count = CouponsUser::where('is_delete', 0)->where('user_id', $user_id)->where('cou_id', $cou_list['cou_id'])->count();
                    $user_cou = $cou_count > 0 && $user_id > 0 ? 1 : 0;  // 0没有券， 去购买可赠券商品

                    if ($user_cou == 0) {
                        $res = $res->where('user_id', $cou_data['ru_id']);

                        if ($cou_data['cou_ok_goods']) {
                            $cou_goods = BaseRepository::getExplode($cou_data['cou_ok_goods']);
                            $res = $res->whereIn('goods_id', $cou_goods);
                        } elseif ($cou_data['cou_ok_cat']) {
                            $cou_children = $this->couponsService->getCouChildren($cou_data['cou_ok_cat']);
                            $cou_children = BaseRepository::getExplode($cou_children);
                            if ($cou_children) {
                                $res = $res->whereIn('cat_id', $cou_children);
                            }
                        }
                    } else {
                        $res = $res->where('user_id', $cou_data['ru_id']);

                        if ($cou_data['cou_goods']) {
                            $cou_goods = BaseRepository::getExplode($cou_data['cou_goods']);
                            $res = $res->whereIn('goods_id', $cou_goods);
                        } elseif ($cou_data['spec_cat']) {
                            $cou_children = $this->couponsService->getCouChildren($cou_data['spec_cat']);
                            $cou_children = BaseRepository::getExplode($cou_children);
                            if ($cou_children) {
                                $res = $res->whereIn('cat_id', $cou_children);
                            }
                        }
                    }
                } else {
                    $res = $res->where('user_id', $cou_data['ru_id']);

                    if ($cou_data['cou_goods']) {
                        $cou_goods = BaseRepository::getExplode($cou_data['cou_goods']);
                        $res = $res->whereIn('goods_id', $cou_goods);
                    } elseif ($cou_data['spec_cat']) {
                        $cou_children = $this->couponsService->getCouChildren($cou_data['spec_cat']);
                        $cou_children = BaseRepository::getExplode($cou_children);
                        if ($cou_children) {
                            $res = $res->whereIn('cat_id', $cou_children);
                        }
                    }
                }
            }
        }

        if (!empty($filter_attr)) {
            $attrList = GoodsAttr::whereIn('goods_attr_id', $filter_attr);
            $attrList = BaseRepository::getToArrayGet($attrList);
            $attr_value = BaseRepository::getKeyPluck($attrList, 'attr_value');

            $goodsList = GoodsAttr::whereIn('attr_value', $attr_value);
            $goodsList = BaseRepository::getToArrayGet($goodsList);
            $goodsList = BaseRepository::getKeyPluck($goodsList, 'goods_id');

            if ($goodsList) {
                $res = $res->whereIn('goods_id', $goodsList);
            }
        }

        $res = $res->count();

        /* 返回商品总数 */
        return $res;
    }
}
