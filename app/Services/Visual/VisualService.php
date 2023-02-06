<?php

namespace App\Services\Visual;

use App\Extensions\File;
use App\Models\Article;
use App\Models\ArticleCat;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CollectStore;
use App\Models\Coupons;
use App\Models\CouponsUser;
use App\Models\GalleryAlbum;
use App\Models\Goods;
use App\Models\MerchantsCategory;
use App\Models\MerchantsShopInformation;
use App\Models\PicAlbum;
use App\Models\Seckill;
use App\Models\SeckillGoods;
use App\Models\SeckillTimeBucket;
use App\Models\SellerShopinfo;
use App\Models\TouchPageView;
use App\Models\TouchTopic;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Touch\TouchPageViewRepository;
use App\Services\Activity\CouponsService;
use App\Services\Category\CategoryGoodsService;
use App\Services\Category\CategoryService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Merchant\MerchantFollowService;
use App\Services\User\UserCommonService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * 可视化
 * Class VisualService
 * @package App\Services\Visual
 */
class VisualService
{
    protected $categoryGoodsService;
    protected $dscRepository;
    protected $goodsCommonService;
    protected $userCommonService;
    protected $categoryService;
    protected $merchantFollowService;
    protected $couponsService;

    public function __construct(
        CategoryGoodsService $categoryGoodsService,
        DscRepository $dscRepository,
        GoodsCommonService $goodsCommonService,
        UserCommonService $userCommonService,
        CategoryService $categoryService,
        MerchantFollowService $merchantFollowService,
        CouponsService $couponsService
    )
    {
        $this->categoryGoodsService = $categoryGoodsService;
        $this->dscRepository = $dscRepository;
        $this->goodsCommonService = $goodsCommonService;
        $this->userCommonService = $userCommonService;
        $this->categoryService = $categoryService;
        $this->merchantFollowService = $merchantFollowService;
        $this->couponsService = $couponsService;
    }

    /**
     * 编辑控制台
     */
    public function index()
    {
        $init_data = [];
        $init_data['app'] = config('shop.wap_app') ?? '';
        return $init_data;
    }

    /**
     * 保存模块配置
     *
     * @param int $ru_id 商家ID
     * @param string $type 类型[index：首页]
     * @param string $device 类型
     * @return mixed
     */
    public function default($ru_id = 0, $type = 'index', $device = '')
    {
        if ($ru_id > 0) {
            $id = TouchPageView::where('ru_id', $ru_id)
                ->where('type', $type)
                ->where('device', $device)
                ->value('id');
        } else {
            $id = TouchPageView::where('ru_id', 0)
                ->where('type', 'index')
                ->where('device', $device)
                ->where('default', 1)
                ->value('id');
        }

        return $id;
    }

    /**
     * 头部APP广告位
     */
    public function appNav()
    {
        $data = [];

        $data['wap_index_pro'] = config('shop.wap_index_pro') ? 1 : 0;
        $data['wap_app_ios'] = html_out(config('shop.wap_app_ios'));
        $data['wap_app_android'] = html_out(config('shop.wap_app_android'));

        return $data;
    }

    /**
     * 公告
     * @param int $cat_id
     * @param int $num
     * @return array
     */
    public function article($cat_id = 0, $num = 10)
    {
        $article_msg = Article::where('is_open', 1);

        if ($cat_id > 0) {
            $list = $this->article_tree($cat_id);

            $res = [];
            if ($list) {
                foreach ($list as $k => $val) {
                    $res[$k] = isset($val['cat_id']) ? $val['cat_id'] : $val;
                }
                if ($res) {
                    array_unshift($res, $cat_id);
                    $cat_id = BaseRepository::getExplode($res);
                    $article_msg = $article_msg->whereIn('cat_id', $cat_id);
                } else {
                    $article_msg = $article_msg->where('cat_id', $cat_id);
                }
            } else {
                $article_msg = $article_msg->where('cat_id', $cat_id);
            }
        }

        $article_msg = $article_msg->orderBy('article_id', 'DESC')
            ->limit($num);

        $article_msg = BaseRepository::getToArrayGet($article_msg);

        if ($article_msg) {
            foreach ($article_msg as $key => $value) {
                $article_msg[$key]['title'] = $value['title'];
                $article_msg[$key]['url'] = dsc_url('/#/articleDetail/' . $value['article_id']);
                $article_msg[$key]['app_page'] = config('route.article.detail') . $value['article_id'];
                $article_msg[$key]['applet_page'] = config('route.article.detail') . $value['article_id'];
                $article_msg[$key]['date'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $value['add_time']);
            }
        }

        return $article_msg;
    }

    /**
     * 商品列表模块(默认)
     *
     * @param int $user_id
     * @param int $cat_id
     * @param string $type
     * @param int $ru_id
     * @param int $number
     * @param int $brand
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param int $show_visual
     * @return array
     * @throws \Exception
     */
    public function product($user_id = 0, $cat_id = 0, $type = '', $ru_id = 0, $number = 10, $brand = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0, $show_visual = 0)
    {
        $children = [];
        if ($cat_id > 0) {
            $children = $this->categoryService->getCatListChildren($cat_id);
        }

        $where_ext = [
            'warehouse_id' => $warehouse_id,
            'area_id' => $area_id,
            'area_city' => $area_city,
            'intro' => $type,
            'ru_id' => $ru_id,
        ];

        $sort = 'click_count';

        $children = !is_array($children) ? explode(',', $children) : $children;
        $product = $this->categoryGoodsService->getMobileCategoryGoodsList($user_id, '', $children, $brand, '', '', '', $where_ext, 0, $number, 1, $sort, 'DESC', $show_visual);

        $product = $product ? $product : [];

        return $product;
    }

    /**
     * 已选则商品列表模块
     *
     * @param $goods_id
     * @param int $ru_id
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param int $uid
     * @param int $show_visual
     * @return array
     * @throws \Exception
     */
    public function checked($goods_id, $ru_id = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0, $uid = 0, $show_visual = 0)
    {
        $goodsList = [];
        $goods = [];
        if (!empty($goods_id)) {
            $goods_id = BaseRepository::getExplode($goods_id);

            $res = Goods::whereIn('goods_id', $goods_id)
                ->where('is_on_sale', 1)
                ->where('is_delete', 0)
                ->where('is_alone_sale', 1)
                ->where('is_show', 1);

            if (config('shop.review_goods')) {
                $res = $res->whereIn('review_status', [3, 4, 5]);
            }

            if ($ru_id > 0) {
                $res = $res->where('user_id', $ru_id);
            }

            /* 关联地区 */
            $res = $this->dscRepository->getAreaLinkGoods($res, $area_id, $area_city);

            if ($uid > 0) {
                $rank = $this->userCommonService->getUserRankByUid($uid);
                $user_rank = $rank['rank_id'];
                $discount = isset($rank['discount']) ? $rank['discount'] : 100;
            } else {
                $user_rank = 1;
                $discount = 100;
            }

            $res = $res->orderBy('weights', 'DESC'); // 权重值
            $res = $res->orderBy('sort_order', 'DESC')->orderBy('goods_id', 'DESC');

            $res = BaseRepository::getToArrayGet($res);

            if ($res) {
                $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');

                $memberPrice = GoodsDataHandleService::goodsMemberPrice($goods_id, $user_rank);
                $warehouseGoods = GoodsDataHandleService::getWarehouseGoodsDataList($goods_id, $warehouse_id, ['goods_id', 'warehouse_price', 'warehouse_promote_price', 'region_number']);
                $warehouseAreaGoods = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods_id, $area_id, $area_city, ['goods_id', 'region_price', 'region_promote_price', 'region_number']);

                $merchantUseGoodsLabelList = GoodsDataHandleService::gettMerchantUseGoodsLabelDataList($goods_id);
                $merchantNoUseGoodsLabelList = GoodsDataHandleService::getMerchantNoUseGoodsLabelDataList($goods_id);

                $seller_id = BaseRepository::getKeyPluck($res, 'user_id');

                $shopInformation = MerchantDataHandleService::MerchantsShopInformationDataList($seller_id);
                $sellerShopinfo = MerchantDataHandleService::SellerShopinfoDataList($seller_id);
                $merchantList = MerchantDataHandleService::getMerchantInfoDataList($seller_id, 0, $sellerShopinfo, $shopInformation);

                $productList = [];
                $productWarehouseList = [];
                $productAreaList = [];
                if ($show_visual == 1) {
                    $productList = GoodsDataHandleService::getProductsDataList($goods_id, ['product_id', 'goods_id', 'product_number']);
                    $productWarehouseList = GoodsDataHandleService::getProductsWarehouseDataList($goods_id, $warehouse_id, ['product_id', 'goods_id', 'product_number']);
                    $productAreaList = GoodsDataHandleService::getProductsAreaDataList($goods_id, $area_id, $area_city, ['product_id', 'goods_id', 'product_number']);
                }

                foreach ($res as $row) {

                    /* 查询商品货品总库存 */
                    if ($show_visual == 1) {
                        if ($row['model_price'] == 1) {
                            $product = $productWarehouseList[$row['goods_id']] ?? [];
                            $row['goods_number'] = $product ? BaseRepository::getArraySum($product, 'product_number') : $row['goods_number'];
                        } elseif ($row['model_price'] == 2) {
                            $product = $productAreaList[$row['goods_id']] ?? [];
                            $row['goods_number'] = $product ? BaseRepository::getArraySum($product, 'product_number') : $row['goods_number'];
                        } else {
                            $product = $productList[$row['goods_id']] ?? [];
                            $row['goods_number'] = $product ? BaseRepository::getArraySum($product, 'product_number') : $row['goods_number'];
                        }
                    }


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

                    $price = $this->goodsCommonService->getGoodsPrice($price, $discount / 100, $row);

                    $row['shop_price'] = $price['shop_price'];
                    $row['promote_price'] = $price['promote_price'];
                    $row['goods_number'] = $price['goods_number'];

                    if ($row['promote_price'] > 0) {
                        $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                    } else {
                        $promote_price = 0;
                    }

                    $goods[$row['goods_id']]['promote_price'] = $row['promote_price'];
                    $goods[$row['goods_id']]['shop_price'] = $row['shop_price'];

                    $goodsSelf = false;
                    if ($row['user_id'] == 0) {
                        $goodsSelf = true;
                    }

                    if ($promote_price > 0) {
                        $goods[$row['goods_id']]['shop_price'] = $promote_price;
                        $goods[$row['goods_id']]['shop_price_formated'] = $this->dscRepository->getPriceFormat($promote_price, true, true, $goodsSelf);
                    } else {
                        $goods[$row['goods_id']]['shop_price_formated'] = $this->dscRepository->getPriceFormat($row['shop_price'], true, true, $goodsSelf);
                    }

                    $goods[$row['goods_id']]['shop_price'] = $this->dscRepository->getPriceFormat($goods[$row['goods_id']]['shop_price'], true, false, $goodsSelf);

                    $goods[$row['goods_id']]['market_price'] = $row['market_price'] ?? 0;
                    $goods[$row['goods_id']]['market_price_formated'] = $this->dscRepository->getPriceFormat($row['market_price'] ?? 0, true, true, $goodsSelf);

                    $goods[$row['goods_id']]['market_price'] = $this->dscRepository->getPriceFormat($goods[$row['goods_id']]['market_price'], true, false, $goodsSelf);

                    $goods[$row['goods_id']]['goods_number'] = $row['goods_number'];
                    $goods[$row['goods_id']]['goods_id'] = $row['goods_id'];
                    $goods[$row['goods_id']]['title'] = $row['goods_name'];
                    $goods[$row['goods_id']]['sale'] = $row['sales_volume'];
                    $goods[$row['goods_id']]['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                    $goods[$row['goods_id']]['goods_img'] = $this->dscRepository->getImagePath($row['goods_img']);
                    $goods[$row['goods_id']]['url'] = dsc_url('/#/goods/' . $row['goods_id']);
                    $goods[$row['goods_id']]['app_page'] = config('route.goods.detail') . $row['goods_id'];
                    $goods[$row['goods_id']]['applet_page'] = config('route.goods.detail') . $row['goods_id'];

                    // 活动标签
                    $shop_information = $merchantList[$row['user_id']] ?? []; //通过ru_id获取到店铺信息;

                    $where = [
                        'user_id' => $row['user_id'],
                        'goods_id' => $row['goods_id'],
                        'self_run' => $shop_information['self_run'] ?? 0,
                    ];
                    $goods_label_all = $this->goodsCommonService->getListGoodsLabelList($merchantUseGoodsLabelList, $merchantNoUseGoodsLabelList, $where);

                    $goods[$row['goods_id']]['goods_label'] = $goods_label_all['goods_label'] ?? [];
                    $goods[$row['goods_id']]['goods_label_suspension'] = $goods_label_all['goods_label_suspension'] ?? [];
                }
            }

            // 按goods_id值顺序排序
            foreach ($goods_id as $k => $v) {
                if (isset($goods[$v]) && $goods[$v]) {
                    $goodsList[$k] = $goods[$v];
                }
            }
        }

        return $goodsList;
    }

    /**
     * 秒杀模块
     *
     * @param int $number
     * @return array|mixed
     * @throws \Exception
     */
    public function seckill($number = 10)
    {
        $now = TimeRepository::getGmTime();

        $sec_id = Seckill::query()->select('sec_id')->where('is_putaway', 1)
            ->where('review_status', 3)
            ->where('begin_time', '<=', $now)
            ->where('acti_time', '>', $now)
            ->pluck('sec_id');
        $sec_id = BaseRepository::getToArray($sec_id);

        $tb_id = SeckillGoods::select('tb_id')->whereIn('sec_id', $sec_id)->pluck('tb_id');
        $tb_id = BaseRepository::getToArray($tb_id);

        $sec = SeckillTimeBucket::whereIn('id', $tb_id);
        $sec = $sec->orderBy('begin_time');
        $sec = BaseRepository::getToArrayGet($sec);

        if (empty($sec)) {
            return [];
        }

        foreach ($sec as $key => $val) {
            if ($val) {
                $sec[$key]['begin_time'] = TimeRepository::getLocalStrtoTime($val['begin_time']);
                $sec[$key]['end_time'] = TimeRepository::getLocalStrtoTime($val['end_time']);
                if ($now > $sec[$key]['begin_time'] && $now < $sec[$key]['end_time']) {
                    $arr['id'] = $val['id'];
                    $arr['begin_time'] = $sec[$key]['begin_time'] + config('shop.timezone') * 3600;
                    $arr['end_time'] = $sec[$key]['end_time'] + config('shop.timezone') * 3600;
                    $arr['type'] = 1; // 当前活动
                } elseif ($now < $sec[$key]['begin_time']) {
                    $all[$key]['id'] = $val['id'];
                    $all[$key]['begin_time'] = $sec[$key]['begin_time'] + config('shop.timezone') * 3600;
                    $all[$key]['end_time'] = $sec[$key]['end_time'] + config('shop.timezone') * 3600;
                    $all[$key]['type'] = 0; // 过期活动
                }
            }
        }

        $allsec = [];
        if (!empty($all)) {
            $allsec = array_values($all);
        }
        if (empty($arr['type'])) {
            $arr = [];
            $len = count($allsec);
            for ($i = 0; $i < $len; $i++) {
                if ($i == 0) {
                    $arr = $allsec[$i];
                    continue;
                }
                if ($allsec[$i]['begin_time'] < $arr['begin_time']) {
                    $arr = $allsec[$i];
                }
            }
        }

        if (empty($arr['id'])) {
            return [];
        }

        $goods_cache_name = 'visual_service_seckill_goods_' . $arr['id'];
        $sec_goods = cache($goods_cache_name);
        $sec_goods = !is_null($sec_goods) ? $sec_goods : [];

        if (empty($sec_goods)) {

            $sec_id = Seckill::query()->select('sec_id')->where('is_putaway', 1)
                ->where('is_putaway', 1)
                ->where('review_status', 3)
                ->where('begin_time', '<=', $now)
                ->where('acti_time', '>', $now)
                ->pluck('sec_id');
            $sec_id = BaseRepository::getToArray($sec_id);

            if ($sec_id) {
                $sec_goods = SeckillGoods::where('tb_id', $arr['id'])->whereIn('sec_id', $sec_id);
                $sec_goods = $sec_goods->with([
                    'getSeckillGoodsAttr' => function ($query) {
                        $query->select('id', 'seckill_goods_id', 'product_id', 'sec_price', 'sec_num', 'sec_limit');
                    }
                ]);

                $sec_goods = $sec_goods->orderBy('goods_id', 'DESC')
                    ->take($number);

                $sec_goods = BaseRepository::getToArrayGet($sec_goods);

                cache()->forever($goods_cache_name, $sec_goods);
            } else {
                $sec_goods = [];
            }
        }

        if ($sec_goods) {

            $goods_id = BaseRepository::getKeyPluck($sec_goods, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'goods_name', 'market_price', 'goods_thumb']);

            foreach ($sec_goods as $key => $value) {

                $get_seckill_goods_attr = $value['get_seckill_goods_attr'] ?? [];
                unset($value['get_seckill_goods_attr']);
                if (!empty($get_seckill_goods_attr)) {
                    // 有秒杀属性取最小属性价、数量
                    $get_seckill_goods_attr = collect($get_seckill_goods_attr)->sortBy('sec_price')->first();
                    $value['sec_price'] = $get_seckill_goods_attr['sec_price'] ?? 0;
                    $value['sec_num'] = $get_seckill_goods_attr['sec_num'] ?? 0;
                    $value['sec_limit'] = $get_seckill_goods_attr['sec_limit'] ?? 0;
                }

                $arr['goods'][$key]['id'] = $value['id'];
                $arr['goods'][$key]['goods_id'] = $value['goods_id'];
                $arr['goods'][$key]['price'] = $value['sec_price'];
                $arr['goods'][$key]['price_formated'] = $this->dscRepository->getPriceFormat($value['sec_price']);
                $arr['goods'][$key]['shop_price'] = $arr['goods'][$key]['price'];
                $arr['goods'][$key]['shop_price_formated'] = $arr['goods'][$key]['price_formated'];
                $arr['goods'][$key]['stock'] = $value['sec_num'];

                /* 商品 */
                $goods = $goodsList[$value['goods_id']] ?? [];
                $goods['market_price'] = $goods['market_price'] ?? 0;
                $goods['goods_name'] = $goods['goods_name'] ?? '';
                $goods['goods_thumb'] = $goods['goods_thumb'] ?? '';

                $arr['goods'][$key]['market_price'] = $goods['market_price'] ?? 0;
                $arr['goods'][$key]['market_price_formated'] = $this->dscRepository->getPriceFormat($goods['market_price']);
                $arr['goods'][$key]['title'] = $goods['goods_name'];
                $arr['goods'][$key]['goods_thumb'] = empty($goods['goods_thumb']) ? '' : $this->dscRepository->getImagePath($goods['goods_thumb']);
                $arr['goods'][$key]['url'] = url('/#/seckill/detail') . '?' . http_build_query(['seckill_id' => $value['id'], 'tomorrow' => 0], '', '&');
                $arr['goods'][$key]['app_page'] = config('route.seckill.detail') . $value['id'] . '&tomorrow=0';
                $arr['goods'][$key]['applet_page'] = config('route.seckill.detail') . $value['id'] . '&tomorrow=0';
            }
        }

        return $arr;
    }

    /**
     * 店铺街
     *
     * @param int $childrenNumber
     * @param int $number
     * @return bool|\Illuminate\Cache\CacheManager|mixed
     * @throws \Exception
     */
    public function store($childrenNumber = 100, $number = 10)
    {
        $cache_name = 'visual_service_store_' . $childrenNumber . '_' . $number;
        $store = cache($cache_name);
        $store = !is_null($store) ? $store : false;

        if ($store === false) {
            $store = MerchantsShopInformation::where('is_street', 1)
                ->where('shop_close', 1);

            $store = $store->orderBy('sort_order');

            $store = $store->take($number);

            $store = BaseRepository::getToArrayGet($store);

            if ($store) {

                $seller_id = BaseRepository::getKeyPluck($store, 'user_id');

                $where = [
                    'is_on_sale' => 1,
                    'is_alone_sale' => 1,
                    'is_delete' => 0
                ];
                $goodsList = GoodsDataHandleService::getSellerGoodsDataList($seller_id, ['goods_id', 'user_id', 'review_status', 'goods_name', 'goods_thumb'], $childrenNumber, $where);

                $merchantList = MerchantDataHandleService::getMerchantInfoDataList($seller_id);

                foreach ($store as $key => $value) {

                    $merchant = $merchantList[$value['user_id']] ?? [];

                    $value['ru_id'] = $merchant['ru_id'] ?? 0;
                    $value['logo_thumb'] = $merchant['logo_thumb'] ?? '';
                    $value['street_thumb'] = $merchant['street_thumb'] ?? '';

                    $store[$key]['rz_shopName'] = $merchant['shop_name'] ?? '';
                    $store[$key]['rz_shop_name'] = $merchant['shop_name'] ?? '';

                    $goods = $goodsList[$value['user_id']] ?? [];

                    if (config('shop.review_goods') == 1) {
                        $sql = [
                            'where' => [
                                [
                                    'name' => 'review_status',
                                    'condition' => '>',
                                    'value' => 2
                                ]
                            ]
                        ];
                        $goods = BaseRepository::getArraySqlGet($goods, $sql);
                    }

                    if ($goods) {
                        foreach ($goods as $a => $val) {
                            $goods[$a]['goods_thumb'] = $this->dscRepository->getImagePath($val['goods_thumb']);
                        }
                    }

                    $store[$key]['goods'] = $goods;
                    $store[$key]['total'] = count($goods);
                    $store[$key]['logo_thumb'] = $this->dscRepository->getImagePath(str_replace('../', '', $value['logo_thumb']));
                    $store[$key]['street_thumb'] = $this->dscRepository->getImagePath($value['street_thumb']);
                }
            }

            cache()->forever($cache_name, $store);
        }

        return $store;
    }

    /**
     * 店铺街详情
     *
     * @param int $ru_id
     * @param int $uid
     * @return array
     */
    public function storeIn($ru_id = 0, $uid = 0)
    {
        $store = MerchantsShopInformation::select('shop_id', 'user_id', 'rz_shop_name')
            ->where('user_id', $ru_id);
        $store = BaseRepository::getToArrayGet($store);

        if ($store) {
            foreach ($store as $key => $value) {

                $sellerShopinfo = SellerShopinfo::select('logo_thumb', 'street_thumb', 'shop_desc')->where('ru_id', $value['user_id']);
                $sellerShopinfo = BaseRepository::getToArrayFirst($sellerShopinfo);

                $value['logo_thumb'] = $sellerShopinfo['logo_thumb'];
                $value['street_thumb'] = $sellerShopinfo['street_thumb'];
                $value['shop_desc'] = $sellerShopinfo['shop_desc'];

                $store[$key]['total'] = $this->GoodsInfo($value['user_id']);
                $store[$key]['new'] = $this->GoodsInfo($value['user_id'], 1, 0);
                $store[$key]['promote'] = $this->GoodsInfo($value['user_id'], 0, 1);

                $store[$key]['logo_thumb'] = $this->dscRepository->getImagePath(str_replace('../', '', $value['logo_thumb']));
                $store[$key]['street_thumb'] = $this->dscRepository->getImagePath($value['street_thumb']);

                $follow = CollectStore::select('user_id')
                    ->where('ru_id', $value['user_id'])
                    ->where('user_id', $uid)
                    ->count();

                $store[$key]['count_gaze'] = empty($follow) ? 0 : 1;

                $like_num = $value['collect_count'] ? $value['collect_count'] : CollectStore::select('ru_id')
                    ->where('ru_id', $value['user_id'])
                    ->count();

                $store[$key]['like_num'] = empty($like_num) ? 0 : $like_num;
                $store[$key]['shop_desc'] = $value['shop_desc'];

                $store[$key]['follow_list'] = $this->merchantFollowService->getFollowList($value['user_id']);
            }
        }

        return $store;
    }

    /**
     * 商品数量
     *
     * @param $user_id
     * @param int $store_new
     * @param int $is_promote
     * @return mixed
     */
    public function goodsInfo($user_id, $store_new = 0, $is_promote = 0)
    {
        $time = TimeRepository::getGmTime();
        $res = Goods::select('goods_id')
            ->where('is_on_sale', 1)
            ->where('is_alone_sale', 1)
            ->where('is_delete', 0)
            ->where('goods_number', '>', 0)
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
     * 店铺街详情底部
     *
     * @param int $ru_id
     * @return array
     */
    public function storeDown($ru_id = 0)
    {
        $res = MerchantsShopInformation::from('merchants_shop_information as ms')
            ->select('ms.shop_id', 'ms.user_id', 'ms.is_im', 'ms.rz_shop_name', 'ss.kf_qq', 'ss.kf_ww', 'ss.meiqia')
            ->leftjoin('seller_shopinfo as ss', 'ms.user_id', 'ss.ru_id')
            ->where('ms.user_id', $ru_id)
            ->get();
        $shop = $res ? $res->toArray() : [];

        $store = [];
        foreach ($shop as $key => $value) {
            $store[$key]['shop_id'] = $value['shop_id'];
            $store[$key]['user_id'] = $value['user_id'];
            $store[$key]['rz_shop_name'] = $value['rz_shop_name'];
            $store[$key]['shop_category'] = $this->store_category(0, $value['user_id']);
        }

        return $store;
    }

    /**
     * 关注店铺
     *
     * @param int $ru_id
     * @param int $uid
     * @return array
     * @throws \Exception
     */
    public function addCollect($ru_id = 0, $uid = 0)
    {
        $time = TimeRepository::getGmTime();

        if (!empty($ru_id) && $uid > 0) {
            $collectStore = CollectStore::select('user_id', 'rec_id', 'ru_id')
                ->where('ru_id', $ru_id)
                ->where('user_id', $uid);
            $collectStore = BaseRepository::getToArrayFirst($collectStore);

            if ($collectStore) {
                CollectStore::where('rec_id', $collectStore['rec_id'])->delete();

                $other = [
                    'collect_count' => 'collect_count - 1'
                ];
                $other = BaseRepository::getDbRaw($other);
                MerchantsShopInformation::where('user_id', $collectStore['ru_id'])->where('collect_count', '>', 0)->update($other);

                $res = [
                    'count_gaze' => 0
                ];
                return $res;
            } else {
                $data = [
                    'user_id' => $uid,
                    'ru_id' => $ru_id,
                    'is_attention' => 1,
                    'add_time' => $time
                ];

                $id = CollectStore::insertGetId($data);

                if ($id > 0) {
                    $other = [
                        'collect_count' => 'collect_count + 1'
                    ];
                    $other = BaseRepository::getDbRaw($other);
                    MerchantsShopInformation::where('user_id', $ru_id)->update($other);

                    $cou_id = Coupons::where('cou_type', VOUCHER_SHOP_CONLLENT)
                        ->where('ru_id', $ru_id)
                        ->where('status', COUPON_STATUS_EFFECTIVE)
                        ->value('cou_id');
                    $cou_id = $cou_id ? $cou_id : 0;

                    $rec_id = CouponsUser::where('is_delete', 0)->where('user_id', $uid)->where('cou_id', $cou_id)->value('uc_id');
                    $rec_id = $rec_id ? $rec_id : 0;

                    if (!empty($cou_id) && empty($rec_id)) {
                        $this->couponsService->getCouponsReceive($cou_id, $uid);
                    }
                }

                $res = [
                    'count_gaze' => 1
                ];
                return $res;
            }
        }
    }

    /**
     * 显示页面
     *
     * @param int $id
     * @param string $type
     * @param int $default
     * @param int $ru_id
     * @param int $number
     * @param int $page_id
     * @return array
     * @throws \Exception
     */
    public function view($id = 0, $type = 'index', $default = 0, $ru_id = 0, $number = 10, $page_id = 0, $device = '')
    {
        $ru_id = is_null($ru_id) ? 0 : $ru_id;

        $model = TouchPageView::query();

        if ($id) {
            $res = $model->select('title', 'data')->where('id', $id);
            $res = BaseRepository::getToArrayFirst($res);
        } elseif ($default < 2) {
            if ($number > 0) {
                $res = $model->select('id', 'type', 'title', 'pic', 'thumb_pic', 'default')
                    ->where('default', $default)
                    ->where('ru_id', $ru_id)
                    ->where('page_id', $page_id)
                    ->where('device', $device)
                    ->orderBy('update_at', 'DESC')->limit($number);
            } else {
                $res = $model->select('id', 'type', 'title', 'pic', 'thumb_pic', 'default')
                    ->where('default', $default)->where('ru_id', $ru_id)
                    ->where('page_id', $page_id)
                    ->where('device', $device)
                    ->orderBy('update_at', 'DESC');
            }

            $res = BaseRepository::getToArrayGet($res);

            if ($res) {
                foreach ($res as $k => $v) {
                    $res[$k]['pic'] = !empty($v['pic']) && preg_match('/^(data:\s*image\/(\w+);base64,)/', $v['pic'], $matches) ? $v['pic'] : $this->dscRepository->getImagePath($v['pic']);
                    $res[$k]['thumb_pic'] = (isset($v['thumb_pic']) && !empty($v['thumb_pic'])) ? $this->dscRepository->getImagePath($v['thumb_pic']) : '';
                }
            }

            return $res;
        } elseif ($default == 3) {
            // 左侧默认首页、自定义页
            if ($number > 0) {
                $res = $model->select('id', 'type', 'title', 'pic', 'thumb_pic', 'default')
                    ->where('ru_id', $ru_id)
                    ->where('device', $device)
                    ->orderBy('update_at', 'DESC')
                    ->limit($number);
            } else {
                $res = $model->select('id', 'type', 'title', 'pic', 'thumb_pic', 'default')
                    ->where('ru_id', $ru_id)
                    ->where('device', $device)
                    ->orderBy('update_at', 'DESC');
            }

            $res = BaseRepository::getToArrayGet($res);

            if ($res) {
                foreach ($res as $k => $v) {
                    $res[$k]['pic'] = !empty($v['pic']) && preg_match('/^(data:\s*image\/(\w+);base64,)/', $v['pic'], $matches) ? $v['pic'] : $this->dscRepository->getImagePath($v['pic']);
                    $res[$k]['thumb_pic'] = (isset($v['thumb_pic']) && !empty($v['thumb_pic'])) ? $this->dscRepository->getImagePath($v['thumb_pic']) : '';
                }
            } else {
                $id = TouchPageView::query()->insertGetId([
                    'ru_id' => $ru_id,
                    'type' => $type,
                    'page_id' => 0,
                    'title' => $ru_id > 0 ? lang('merchants_store.Shop_home') : 'untitle',
                    'data' => '',
                    'default' => 1,
                    'review_status' => 1,
                    'is_show' => 1,
                    'device' => $device
                ]);

                $res = $model->select('id', 'type', 'title', 'pic', 'thumb_pic', 'default', 'data')->where('id', $id);
                $res = BaseRepository::getToArrayGet($res);
            }

            return $res;
        } else {
            $res = $model->select('title', 'data')->where('ru_id', $ru_id)->where('type', $type)->where('device', $device)->orderBy('update_at', 'DESC');
            $res = BaseRepository::getToArrayGet($res);
        }

        if (isset($res['data']) && $res['data']) {
            $res['data'] = $this->pageDataReplace($res['data']);
        }

        return $res;
    }

    /**
     * 可视化内容替换
     *
     * @param string $content 可视化数据
     * @param bool $absolute
     * @return null|string|string[]
     * @throws \Exception
     */
    public function pageDataReplace($content = '', $absolute = false)
    {
        if ($absolute == true) {
            /**
             * 图片路径 绝对路径转相对路径 （用于保存数据库）
             */
            $label = [
                '/storage\//' => '',
                '/\"img\"\:\"(http|https)\:\/\/(.*?)\/(.*?)\"/' => '"img":"../$3"',
                '/\"productImg\"\:\"(http|https)\:\/\/(.*?)\/(.*?)\"/' => '"productImg":"../$3"',
                '/\"titleImg\"\:\"(http|https)\:\/\/(.*?)\/(.*?)\"/' => '"titleImg":"../$3"',
            ];
        } else {
            if (config('shop.open_oss') == 1) {
                $bucket_info = $this->dscRepository->getBucketInfo();
                $url = $bucket_info['endpoint'] ?? '';
            } else {
                $url = Storage::url('/');
            }

            $label = [
                /**
                 * 图片路径 相对路径转绝对路径 （用于显示）
                 */
                '/\"img\"\:\"..\/(.*?)\"/' => '"img":"' . $url . '$1"',
                '/\"productImg\"\:\"..\/(.*?)\"/' => '"productImg":"' . $url . '$1"',
                '/\"titleImg\"\:\"..\/(.*?)\"/' => '"titleImg":"' . $url . '$1"',
            ];
        }

        foreach ($label as $key => $value) {
            $content = preg_replace($key, $value, $content);
        }

        return $content;
    }

    /**
     * 文章分类
     *
     * @param int $tree_id
     * @return array
     */
    public function article_tree($tree_id = 0)
    {
        $res = ArticleCat::select('cat_id', 'cat_name')
            ->where('parent_id', $tree_id)
            ->orderBy('sort_order', 'ASC')
            ->get();
        $res = $res ? $res->toArray() : [];

        $three_arr = [];
        foreach ($res as $k => $row) {
            $three_arr[$k]['cat_id'] = $row['cat_id'];
            $three_arr[$k]['cat_name'] = $row['cat_name'];
            $three_arr[$k]['haschild'] = 0;

            if (isset($row['cat_id'])) {
                $child_tree = $this->article_tree($row['cat_id']);
                if ($child_tree) {
                    $three_arr[$k]['tree'] = $child_tree;
                    $three_arr[$k]['haschild'] = 1;
                }
            }
        }

        return $three_arr;
    }

    /**
     * 商品分类
     *
     * @param int $cat_id
     * @param int $level 是否显示子分类
     * @return array
     */
    public function cat_list($cat_id = 0, $level = 0)
    {
        $model = Category::select('cat_id', 'cat_name', 'cat_alias_name', 'parent_id')
            ->where('parent_id', $cat_id)
            ->where('is_show', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('cat_id', 'DESC');

        $total = $model->count();

        $res = $model->get();

        $res = $res ? $res->toArray() : [];

        $three_arr = [];
        foreach ($res as $k => $row) {
            $three_arr[$k]['cat_id'] = $row['cat_id'];
            $three_arr[$k]['cat_name'] = !empty($row['cat_alias_name']) ? $row['cat_alias_name'] : $row['cat_name'];
            $three_arr[$k]['url'] = dsc_url('/#/list/' . $row['cat_id']);
            $three_arr[$k]['app_page'] = config('route.goods.list') . $row['cat_id'];
            $three_arr[$k]['applet_page'] = config('route.goods.list') . $row['cat_id'];
            $three_arr[$k]['parent_id'] = $row['parent_id'];
            $three_arr[$k]['haschild'] = 0;

            $three_arr[$k]['level'] = $level;

            if (isset($row['cat_id']) && $level > 0) {
                $child_tree = $this->cat_list($row['cat_id'], $level + 1);
                if ($child_tree && !empty($child_tree['category'])) {
                    $three_arr[$k]['child_tree'] = $child_tree['category'];
                    $three_arr[$k]['total'] = count($child_tree);
                    $three_arr[$k]['haschild'] = 1;
                }
            }
        }

        return ['category' => $three_arr, 'total' => $total];
    }

    /**
     * 文章分类
     * @param int $cat_id 文章分类id
     * @param int $level 是否显示子分类
     * @return array
     */
    public function article_list($cat_id = 0, $level = 0)
    {
        $model = ArticleCat::where('parent_id', $cat_id);

        $model = $model->select('cat_id', 'cat_name', 'parent_id')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('cat_id', 'DESC');

        $total = $model->count();

        $article = $model->get();
        $article = $article ? $article->toArray() : [];

        $three_arr = [];
        foreach ($article as $k => $row) {
            $three_arr[$k]['cat_id'] = $row['cat_id'];
            $three_arr[$k]['cat_name'] = $row['cat_name'];
            $three_arr[$k]['url'] = dsc_url('/#/article') . '?cat_id=' . $row['cat_id'];
            $three_arr[$k]['app_page'] = config('route.article.cat') . $row['cat_id'];
            $three_arr[$k]['applet_page'] = config('route.article.cat') . $row['cat_id'];
            $three_arr[$k]['parent_id'] = $row['parent_id'];
            $three_arr[$k]['haschild'] = 0;

            $three_arr[$k]['level'] = $level;

            if (isset($row['cat_id']) && $level > 0) {
                $child_tree = $this->article_list($row['cat_id'], $level + 1);
                if ($child_tree && !empty($child_tree['article'])) {
                    $three_arr[$k]['child_tree'] = $child_tree['article'];
                    $three_arr[$k]['total'] = count($child_tree);
                    $three_arr[$k]['haschild'] = 1;
                }
            }
        }

        return ['article' => $three_arr, 'total' => $total];
    }

    /**
     * 店铺分类导航
     *
     * @param int $cat_id
     * @param int $ru_id
     * @param int $level
     * @return array
     */
    public function store_category($cat_id = 0, $ru_id = 0, $level = 0)
    {
        $res = MerchantsCategory::select('cat_id', 'cat_name', 'parent_id', 'user_id')
            ->where('user_id', $ru_id)
            ->where('is_show', 1)
            ->where('parent_id', $cat_id)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('cat_id', 'DESC')
            ->get();
        $res = $res ? $res->toArray() : [];

        $three_arr = [];
        foreach ($res as $k => $row) {
            $three_arr[$k]['cat_id'] = $row['cat_id'];
            $three_arr[$k]['cat_name'] = $row['cat_name'];
            $three_arr[$k]['parent_id'] = $row['parent_id'];
            $three_arr[$k]['url'] = dsc_url('/#/ShopGoodsList') . '?' . http_build_query(['cat_id' => $row['cat_id'], 'ru_id' => $ru_id], '', '&');
            $three_arr[$k]['app_page'] = config('route.shop.category_goods') . '?' . http_build_query(['ru_id' => $ru_id, 'cat_id' => $row['cat_id']], '', '&');
            $three_arr[$k]['applet_page'] = config('route.shop.category_goods') . '?' . http_build_query(['ru_id' => $ru_id, 'cat_id' => $row['cat_id']], '', '&');
            $three_arr[$k]['opennew'] = 0;

            $three_arr[$k]['haschild'] = 0;
            $three_arr[$k]['level'] = $level;

            if (isset($row['cat_id']) && $level > 0) {
                $child_tree = $this->store_category($row['cat_id'], $ru_id, $level + 1);
                if ($child_tree) {
                    $three_arr[$k]['child'] = $child_tree;
                    $three_arr[$k]['haschild'] = 1;
                }
            }
        }

        return $three_arr;
    }

    /**
     * 更新页面
     *
     * @param $id
     * @param array $data
     * @param string $pic
     * @return bool
     * @throws \Exception
     */
    public function savePage($id, $data = [], $pic = '')
    {
        if ($id) {
            $model = TouchPageView::where('id', $id);

            $res = $model->first();
            $res = $res ? $res->toArray() : [];

            if ($res) {
                // 保存图片数据路径为相对路径
                if ($data) {
                    $data = $this->pageDataReplace($data, true);
                }

                // 保存封面图
                $img_path = \App\Extensions\Picture::base64ToImg($pic, 'uploads/image/'); // base64转存img
                // 上传oss
                File::ossMirror($img_path, true);
                // 删除原图片
                $file_path = $res['pic'] ?? '';
                $pattern = '/^(data:\s*image\/(\w+);base64,)/';
                if (!preg_match($pattern, $file_path) && !empty($img_path) && $file_path != $img_path) {
                    File::remove($file_path);
                }

                $keep = [
                    'data' => !empty($data) ? $data : $res['data'],
                    'pic' => !empty($img_path) ? $img_path : $res['pic'],
                    'thumb_pic' => !empty($img_path) ? $img_path : $res['pic'],
                    'update_at' => TimeRepository::getGmTime()
                ];

                $model->update($keep);

                Cache::flush();

                return true;
            }
        }
        return false;
    }

    /**
     * 删除页面
     * @param int $id
     * @return bool
     */
    public function del_page($id = 0)
    {
        if ($id) {
            $res = TouchPageView::where('id', $id)->delete();
            Cache::flush();
            return $res;
        }
        return false;
    }

    /**
     * 品牌列表
     *
     * @param int $num
     * @return array
     * @throws \Exception
     */
    public function brand_list($num = 100)
    {
        $brand = Brand::where(['is_show' => 1])
            ->limit($num)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('brand_id', 'DESC');

        $brand = BaseRepository::getToArrayGet($brand);

        if ($brand) {
            foreach ($brand as $k => $val) {
                $brand[$k]['brand_logo'] = $this->dscRepository->getImagePath($val['brand_logo']);
                $brand[$k]['index_img'] = $this->dscRepository->getImagePath($val['index_img']);
            }
        }

        return $brand;
    }

    /**
     * 创建相册
     *
     * @param int $ru_id
     * @param string $album_mame
     * @return mixed
     */
    public function make_gallery_action($ru_id = 0, $album_mame = '')
    {
        $time = TimeRepository::getGmTime();

        $data = [
            'ru_id' => $ru_id,
            'album_mame' => $album_mame,
            'sort_order' => 50,
            'add_time' => $time,
        ];
        return GalleryAlbum::create($data);
    }

    /**
     * 图库列表
     *
     * @param int $ru_id
     * @param int $album_id
     * @param string $thumb
     * @param int $pageSize
     * @return array
     * @throws \Exception
     */
    public function picture_list($ru_id = 0, $album_id = 0, $thumb = '', $pageSize = 15)
    {
        $model = PicAlbum::where(['ru_id' => $ru_id, 'album_id' => $album_id]);

        $list = $model->orderBy('pic_id', 'desc')
            ->paginate($pageSize);

        $res = [];
        foreach ($list as $key => $vo) {
            $res[$key]['id'] = $vo['pic_id'];
            $res[$key]['desc'] = $vo['pic_name'];
            $res[$key]['img'] = $this->dscRepository->getImagePath($vo['pic_file']);
            $res[$key]['isSelect'] = false;
        }

        $total = $model->count();

        return ['res' => $res, 'total' => $total];
    }

    /**
     * 相册或图片
     *
     * @param string $type
     * @param int $ru_id
     * @param int $album_id
     * @param int $pageSize
     * @param int $currentPage
     * @return array
     * @throws \Exception
     */
    public function get_thumb($type = '', $ru_id = 0, $album_id = 0, $pageSize = 10, $currentPage = 1)
    {
        $data = [];
        if ($type == 'thumb') {
            // 左侧相册列表
            $model = GalleryAlbum::where(['ru_id' => $ru_id, 'parent_album_id' => 0]);

            $total = $model->count();

            $gallery = $model->orderBy('add_time', 'DESC')
                ->get();
            $gallery = $gallery ? $gallery->toArray() : [];

            if ($gallery) {
                foreach ($gallery as $key => $value) {
                    $gallery[$key] = [
                        'album_id' => $value['album_id'],
                        'name' => $value['album_mame']
                    ];
                    $tree = GalleryAlbum::select('album_id', 'album_mame')
                        ->where(['parent_album_id' => $value['album_id']])
                        ->orderBy('add_time', 'DESC')
                        ->get();
                    $tree = $tree ? $tree->toArray() : [];
                    $gallery[$key]['tree'] = $tree;
                }
            }

            $data = ['thumb' => $gallery, 'total' => $total];
        } elseif ($type == 'img') {
            // 图片列表
            $current = ($currentPage == 1) ? 0 : ($currentPage - 1) * $pageSize;

            $model = PicAlbum::where(['album_id' => $album_id, 'ru_id' => $ru_id]);

            $total = $model->count();

            $pic = $model->select('pic_id', 'pic_name', 'pic_file')
                ->orderBy('add_time', 'DESC')
                ->offset($current)
                ->limit($pageSize)
                ->get();
            $pic = $pic ? $pic->toArray() : [];

            if ($pic) {
                foreach ($pic as $key => $value) {
                    $pic[$key]['pic_file'] = $this->dscRepository->getImagePath($value['pic_file']);
                }
            }

            $data = ['img' => $pic, 'total' => $total];
        }

        return $data;
    }

    /**
     * 可选导航链接 平台
     *
     * @param string $type
     * @param int $pageSize
     * @param int $currentPage
     * @return array
     * @throws \Exception
     */
    public function get_url($type = '', $pageSize = 10, $currentPage = 1)
    {
        $current = ($currentPage - 1) * $pageSize;

        if ($type == 'function' || $type == 'activity') {
            $list = TouchPageViewRepository::common_url($type);

            $url = collect($list)->slice($current, $pageSize)->values()->all();

            return ['url' => $url, 'total' => count($list), 'page' => $currentPage];
        } elseif ($type == 'category') {
            // 分类显示子分类
            $list = $this->cat_list(0, 1);
            // 分类分页
            $url = collect($list['category'])->slice($current, $pageSize)->values()->all();

            return ['url' => $url, 'total' => $list['total'], 'page' => $currentPage];
        } elseif ($type == 'article') {
            // 文章显示子分类
            $list = $this->article_list(0, 1);
            // 文章分页
            $url = collect($list['article'])->slice($current, $pageSize)->values()->all();

            return ['url' => $url, 'total' => $list['total'], 'page' => $currentPage];
        } elseif ($type == 'topic') {
            $time = TimeRepository::getGmTime();
            $model = TouchTopic::where('review_status', 3)
                ->where('start_time', '<=', $time)
                ->where('end_time', '>', $time);

            $total = $model->count();

            $list = $model->offset($current)
                ->limit($pageSize)
                ->get();
            $list = $list ? $list->toArray() : [];

            $url = [];
            if ($list) {
                foreach ($list as $key => $value) {
                    $url[$key] = [
                        'cat_id' => $value['topic_id'],
                        'cat_name' => $value['name'],
                        'parent_id' => 0,
                        'start_time' => $value['start_time'],
                        'end_time' => $value['end_time'],
                        'topic_img' => $this->dscRepository->getImagePath($value['topic_img']),
                        'url' => dsc_url('/#/topic/detail/' . $value['cat_id']),
                        'app_page' => config('route.topic.detail') . $value['cat_id'],
                        'applet_page' => config('route.topic.detail') . $value['cat_id'],
                    ];
                }
            }

            return ['url' => $url, 'total' => $total, 'page' => $currentPage];
        }

        return [];
    }

    /**
     * 可选导航链接 - 商家
     *
     * @param string $type
     * @param int $ru_id
     * @param int $pageSize
     * @param int $currentPage
     * @return array
     * @throws \Exception
     */
    public function get_seller_url($type = '', $ru_id = 0, $pageSize = 10, $currentPage = 1)
    {
        $current = ($currentPage - 1) * $pageSize;
        if ($type == 'activity') {
            $list = TouchPageViewRepository::common_url($type);

            $url = collect($list)->slice($current, $pageSize)->values()->all();

            return ['url' => $url, 'total' => count($list), 'page' => $currentPage];
        } elseif ($type == 'category') {
            $model = MerchantsCategory::select('cat_id', 'cat_name', 'parent_id')
                ->where(['parent_id' => 0, 'is_show' => 1, 'user_id' => $ru_id]);

            $total = $model->count();

            $url = $model->offset($current)
                ->limit($pageSize);

            $url = BaseRepository::getToArrayGet($url);

            if ($url) {
                foreach ($url as $key => $value) {
                    $url[$key]['url'] = dsc_url('/#/ShopGoodsList') . '?' . http_build_query(['ru_id' => $ru_id, 'cat_id' => $value['cat_id']], '', '&');
                    $url[$key]['app_page'] = config('route.shop.category_goods') . '?' . http_build_query(['ru_id' => $ru_id, 'cat_id' => $value['cat_id']], '', '&');
                    $url[$key]['applet_page'] = config('route.shop.category_goods') . '?' . http_build_query(['ru_id' => $ru_id, 'cat_id' => $value['cat_id']], '', '&');
                }
            }

            return ['url' => $url, 'total' => $total, 'page' => $currentPage];
        }

        return [];
    }

    /**
     * 添加自定义页面 专题页
     *
     * @param int $id
     * @param string $type
     * @param int $ru_id
     * @param int $page_id
     * @param string $title
     * @param string $description
     * @param array $data
     * @param string $device
     * @return array
     * @throws \Exception
     */
    public function add_topic_page($id = 0, $type = 'topic', $ru_id = 0, $page_id = 0, $title = '', $description = '', $data = [], $device = '')
    {
        $time = TimeRepository::getGmTime();
        if ($id) {
            // 编辑
            $view = TouchPageView::select('id', 'title', 'description', 'thumb_pic')
                ->where(['id' => $id, 'type' => $type])
                ->first();
            $view = $view ? $view->toArray() : [];

            if ($view) {
                $upload_file = isset($data['file']) && !empty($data['file']) ? $data['file'] : ''; // 上传图片
                // $pic = (isset($view['thumb_pic']) && !empty($view['thumb_pic'])) ? $view['thumb_pic'] : $upload_file;
                $pic = (isset($upload_file) && !empty($upload_file)) ? $upload_file : $view['thumb_pic'];
                $keep = [
                    'ru_id' => $ru_id,
                    'title' => !empty($title) ? $title : (isset($view['title']) ? $view['title'] : ''),
                    'thumb_pic' => $pic,
                    'description' => !empty($description) ? $description : (isset($view['description']) ? $view['description'] : ''),
                    'update_at' => $time
                ];
                TouchPageView::where(['id' => $id, 'type' => $type])->update($keep);

                $page = TouchPageView::where(['id' => $id])->first();
                $page = $page ? $page->toArray() : [];

                $page['thumb_pic'] = (isset($page['thumb_pic']) && !empty($page['thumb_pic'])) ? $this->dscRepository->getImagePath($page['thumb_pic']) : '';

                return ['status' => 0, 'pic_url' => $page['thumb_pic'], 'page' => $page, 'msg' => 'save success'];
            } else {
                return ['status' => 1, 'msg' => 'add error'];
            }
        } else {
            if (empty($device)) {
                return ['status' => 1, 'msg' => 'add error'];
            }

            // 添加
            $num = 0;
            if ($page_id > 0) {
                $num = TouchPageView::select('id', 'page_id', 'title', 'description', 'thumb_pic')->where(['page_id' => $page_id])->count();
            }
            if ($num < 1) {
                $keep = [
                    'ru_id' => $ru_id,
                    'type' => $type,
                    'title' => !empty($title) ? $title : '',
                    'page_id' => $page_id,
                    'thumb_pic' => !empty($data['file']) ? $data['file'] : '',
                    'description' => !empty($description) ? $description : '',
                    'create_at' => $time,
                    'device' => $device
                ];
                $new_id = TouchPageView::insertGetId($keep);

                $page = TouchPageView::where('id', $new_id)->first();
                $page = $page ? $page->toArray() : [];

                $page['thumb_pic'] = (isset($page['thumb_pic']) && !empty($page['thumb_pic'])) ? $this->dscRepository->getImagePath($page['thumb_pic']) : '';
                Cache::flush();
                return ['status' => 0, 'pic_url' => $page['thumb_pic'], 'page' => $page, 'msg' => 'add success'];
            } else {
                $page = TouchPageView::where(['page_id' => $page_id])->first();
                $page = $page ? $page->toArray() : [];
                if ($page) {
                    return ['status' => 1, 'msg' => 'page exist', 'page' => $page];
                }
            }
        }
    }

    /**
     * 搜索商品
     *
     * @param int $ru_id
     * @param string $keywords
     * @param int $cat_id
     * @param int $brand_id
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param int $pageSize
     * @param int $currentPage
     * @return array
     * @throws \Exception
     */
    public function search_goods($ru_id = 0, $keywords = '', $cat_id = 0, $brand_id = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0, $pageSize = 10, $currentPage = 1)
    {
        $current = ($currentPage == 1) ? 0 : ($currentPage - 1) * intval($pageSize);

        $model = Goods::where('is_on_sale', 1)
            ->where('is_alone_sale', 1)
            ->where('is_show', 1)
            ->where('is_delete', 0);

        if (config('shop.review_goods')) {
            $model = $model->whereIn('review_status', [3, 4, 5]);
        }

        if ($ru_id) {
            $model = $model->where('user_id', $ru_id);
        }
        if ($cat_id) {
            $model = $model->where('cat_id', $cat_id);
        }
        if ($brand_id) {
            $model = $model->where('brand_id', $brand_id);
        }
        if ($keywords) {
            $model = $model->where(function ($query) use ($keywords) {
                $query->where('goods_name', 'like', '%' . $keywords . '%')
                    ->orWhere('goods_sn', 'like', '%' . $keywords . '%')
                    ->orWhere('keywords', 'like', '%' . $keywords . '%');
            });
        }

        $user_rank = 1;

        $model = $model->whereHasIn('getSellerShopInfo', function ($query) {
            $query->where('shop_close', 1);
        });

        $where = [
            'area_pricetype' => config('shop.area_pricetype'),
            'area_id' => $area_id,
            'area_city' => $area_city
        ];

        $model = $model->with([
            'getMemberPrice' => function ($query) use ($user_rank) {
                $query->where('user_rank', $user_rank);
            },
            'getWarehouseGoods' => function ($query) use ($warehouse_id) {
                $query->where('region_id', $warehouse_id);
            },
            'getWarehouseAreaGoods' => function ($query) use ($where) {
                $query = $query->where('region_id', $where['area_id']);

                if ($where['area_pricetype'] == 1) {
                    $query->where('city_id', $where['area_city']);
                }
            },
            'getShopInfo' => function ($query) {
                $query->select('user_id', 'self_run');
            },
        ]);

        $total = $model->count();

        $goods = $model->select('goods_id', 'user_id', 'goods_name', 'goods_name_style', 'comments_number', 'sales_volume', 'market_price', 'is_new', 'is_best', 'is_hot', 'promote_start_date', 'promote_end_date', 'is_promote', 'shop_price', 'goods_brief', 'goods_thumb', 'goods_img')
            ->offset($current)
            ->limit($pageSize)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('goods_id', 'DESC');

        $goods = BaseRepository::getToArrayGet($goods);

        if ($goods) {
            foreach ($goods as $key => $val) {
                $goods[$key] = $val['get_shop_info'] ? BaseRepository::getArrayMerge($val, $val['get_shop_info']) : $val;
                $goods[$key] = BaseRepository::getArrayExcept($val, ['get_shop_info']);

                if (isset($val['promote_price']) && $val['promote_price'] > 0) {
                    $promote_price = $this->goodsCommonService->getBargainPrice($val['promote_price'], $val['promote_start_date'], $val['promote_end_date']);
                } else {
                    $promote_price = 0;
                }

                $goods[$key]['promote_price'] = $val['promote_price'] ?? 0;
                $goods[$key]['shop_price'] = $val['shop_price'] ?? 0;
                $goods[$key]['market_price'] = $val['market_price'] ?? 0;
                $goods[$key]['market_price_formated'] = $this->dscRepository->getPriceFormat($val['market_price']);

                if ($promote_price > 0) {
                    $goods[$key]['shop_price_formated'] = $this->dscRepository->getPriceFormat($promote_price);
                } else {
                    $goods[$key]['shop_price_formated'] = $this->dscRepository->getPriceFormat($val['shop_price']);
                }

                $goods[$key]['goods_img'] = $this->dscRepository->getImagePath($val['goods_img']);
                $goods[$key]['goods_thumb'] = $this->dscRepository->getImagePath($val['goods_thumb']);
                $goods[$key]['goods_img'] = $this->dscRepository->getImagePath($val['goods_img']);
            }
        }

        return ['goods' => $goods, 'total' => $total];
    }

    /**
     * 翻译POST数据类型
     * @param array $data
     * @return array
     */
    public function transform($data = [])
    {
        if (!empty($data)) {
            foreach ($data as $key => $vo) {
                if (is_array($vo)) {
                    $data[$key] = $this->transform($vo);
                } else {
                    if ($vo === 'true') {
                        $data[$key] = true;
                    }
                    if ($vo === 'false' || $key === 'setting') {
                        $data[$key] = false;
                    }
                }
            }
            return $data;
        }
    }

    /**顶级分类
     * @return mixed
     */
    public function getCategory()
    {
        $cache_id = 'visualCategory';
        $list = cache($cache_id);
        $list = !is_null($list) ? $list : false;
        if ($list === false) {
            $category = Category::select('cat_id', 'cat_alias_name', 'cat_name')
                ->where('parent_id', 0)
                ->where('is_show', 1)
                ->orderBy('sort_order');
            $res = BaseRepository::getToArrayGet($category);
            $res = collect($res)->values()->all();
            $list = [];
            if ($res) {
                foreach ($res as $key => $row) {
                    $list[$key]['cat_alias_name'] = empty($row['cat_alias_name']) ? $row['cat_name'] : $row['cat_alias_name'];
                    $list[$key]['cat_id'] = $row['cat_id'] ?? 0;
                    $list[$key]['url'] = $row['url'] ?? '';
                }
            }
            cache([$cache_id => $list], Carbon::now()->addDays(7));
        }
        return $list;
    }

    /**
     * 新的首页秒杀功能
     *
     * @param $id
     * @param $tomorrow
     * @return array
     * @throws \Exception
     */
    public function visualSeckill($id, $tomorrow)
    {
        $time_list = $this->seckillTimeList();
        $time_id = $time_list[0]['id'] ?? 0;
        if ($id > 0) {
            $time_id = $id;
        }
        $seckill_list = $this->seckillGoodsResults($time_id, 1, 10, $tomorrow);

        return ['time_list' => $time_list, 'seckill_list' => $seckill_list];
    }

    /**
     * 秒杀时间段
     *
     * @return array
     */
    private function seckillTimeList()
    {
        $now = $time = TimeRepository::getGmTime();
        $day = 24 * 60 * 60;

        $localData = TimeRepository::getLocalDate('Ymd');
        $date_begin = TimeRepository::getLocalStrtoTime($localData);
        $date_next = TimeRepository::getLocalStrtoTime($localData) + $day;

        $stb = SeckillTimeBucket::select('id', 'title', 'begin_time', 'end_time')
            ->orderBy('begin_time', 'ASC');
        $stb = BaseRepository::getToArrayGet($stb);

        $sec_id_today = Seckill::selectRaw('GROUP_CONCAT(sec_id) AS sec_id')
            ->where('begin_time', '<=', $date_begin)
            ->where('acti_time', '>', $date_begin)
            ->where('is_putaway', 1)
            ->where('review_status', 3)
            ->orderBy('acti_time', 'ASC');
        $sec_id_today = BaseRepository::getToArrayFirst($sec_id_today);

        $time_list = [];
        if ($stb) {
            foreach ($stb as $k => $v) {
                $v['local_end_time'] = TimeRepository::getLocalStrtoTime($v['end_time']);
                if ($v['local_end_time'] > $now && $sec_id_today) {
                    $time_list[$k]['id'] = $v['id'];
                    $time_list[$k]['title'] = $v['title'];
                    $time_list[$k]['status'] = false;
                    $time_list[$k]['is_end'] = false;
                    $time_list[$k]['soon'] = false;
                    $time_list[$k]['begin_time'] = $begin_time = TimeRepository::getLocalStrtoTime($v['begin_time']);
                    $time_list[$k]['end_time'] = $end_time = TimeRepository::getLocalStrtoTime($v['end_time']);
                    $time_list[$k]['frist_end_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', TimeRepository::getLocalStrtoTime($v['end_time']));
                    if ($begin_time < $now && $end_time > $now) {
                        $time_list[$k]['status'] = true;
                    }
                    if ($end_time < $now) {
                        $time_list[$k]['is_end'] = true;
                    }
                    if ($begin_time > $now) {
                        $time_list[$k]['soon'] = true;
                    }
                }
            }

            $sec_id_tomorrow = Seckill::selectRaw('GROUP_CONCAT(sec_id) AS sec_id')
                ->where('begin_time', '<=', $date_next)
                ->where('acti_time', '>', $date_next)
                ->where('is_putaway', 1)
                ->where('review_status', 3)
                ->orderBy('acti_time', 'ASC');
            $sec_id_tomorrow = BaseRepository::getToArrayFirst($sec_id_tomorrow);

            if (count($time_list) > 4) {
                $time_list = array_slice($time_list, 0, 4);
            }
            if (count($time_list) < 4) {
                if (count($time_list) == 0) {
                    $stb = array_slice($stb, 0, 4);
                }
                if (count($time_list) == 1) {
                    $stb = array_slice($stb, 0, 3);
                }
                if (count($time_list) == 2) {
                    $stb = array_slice($stb, 0, 2);
                }
                if (count($time_list) == 3) {
                    $stb = array_slice($stb, 0, 1);
                }
                foreach ($stb as $k => $v) {
                    if ($sec_id_tomorrow) {
                        $time_list['tmr' . $k]['id'] = $v['id'];
                        $time_list['tmr' . $k]['title'] = $v['title'];
                        $time_list['tmr' . $k]['status'] = false;
                        $time_list['tmr' . $k]['is_end'] = false;
                        $time_list['tmr' . $k]['soon'] = true;
                        $time_list['tmr' . $k]['begin_time'] = TimeRepository::getLocalStrtoTime($v['begin_time']) + $day;
                        $time_list['tmr' . $k]['end_time'] = TimeRepository::getLocalStrtoTime($v['end_time']) + $day;
                        $time_list['tmr' . $k]['frist_end_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', TimeRepository::getLocalStrtoTime($v['end_time']) + $day);
                        $time_list['tmr' . $k]['tomorrow'] = 1;
                    }
                }
            }

            $time_list = collect($time_list)->values()->all();
        }

        return $time_list;
    }

    /**
     * 当前时间的秒杀商品
     *
     * @param $id
     * @param int $page
     * @param int $size
     * @param int $tomorrow
     * @return mixed
     * @throws \Exception
     */
    private function seckillGoodsResults($id, $page = 1, $size = 10, $tomorrow = 0)
    {
        $begin = ($page - 1) * $size;
        $day = 24 * 60 * 60;
        $date_begin = ($tomorrow == 1) ? TimeRepository::getLocalStrtoTime(TimeRepository::getLocalDate('Ymd')) + $day : TimeRepository::getLocalStrtoTime(TimeRepository::getLocalDate('Ymd'));
        $seckill = Seckill::select('sec_id', 'ru_id')
            ->where('begin_time', '<=', $date_begin)
            ->where('acti_time', '>', $date_begin);

        $seckill = BaseRepository::getToArrayGet($seckill);

        $ru_id = BaseRepository::getKeyPluck($seckill, 'ru_id');
        $sec_id = BaseRepository::getKeyPluck($seckill, 'sec_id');

        $res = SeckillGoods::select('id', 'tb_id', 'sec_id', 'goods_id', 'sec_price', 'sec_num', 'sec_limit', 'sales_volume')
            ->whereHasIn('getSeckillTimeBucket', function ($query) use ($id) {
                $query->where('id', $id);
            });

        $where = [
            'begin_time' => $date_begin,
            'sec_id' => $sec_id
        ];
        $res = $res->whereHasIn('getSeckill', function ($query) use ($where) {
            $query->where('is_putaway', 1)
                ->where('review_status', 3)
                ->where('begin_time', '<=', $where['begin_time'])
                ->whereIn('sec_id', $where['sec_id']);
        });

        $res = $res->whereHasIn('getGoods');

        $res = $res->with([
            'getSeckillTimeBucket' => function ($query) {
                $query->select('id', 'begin_time', 'end_time');
            },
            'getSeckill' => function ($query) {
                $query->select('sec_id', 'acti_title', 'acti_time');
            },
            'getGoods' => function ($query) {
                $query->select('goods_id', 'user_id as ru_id', 'goods_thumb', 'shop_price', 'market_price', 'goods_name');
            },
            'getSeckillGoodsAttr' => function ($query) {
                $query->select('id', 'seckill_goods_id', 'product_id', 'sec_price', 'sec_num', 'sec_limit');
            }
        ]);

        $res = $res->withCount([
            'getSeckill as begin_time' => function ($query) {
                $query->select('begin_time');
            }
        ]);

        $res = $res->offset($begin)
            ->limit($size)
            ->orderBy('goods_id', 'DESC')
            ->orderBy('begin_time', 'ASC');

        $res = BaseRepository::getToArrayGet($res);

        $now = $time = TimeRepository::getGmTime();
        $tmr = 86400;

        if ($res) {

            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($res as $k => $v) {

                /* 删除冲突ID */
                if ($v['get_seckill_time_bucket']) {
                    unset($v['get_seckill_time_bucket']['id']);
                }

                $v = BaseRepository::getArrayCollapse([$v, $v['get_seckill_time_bucket'], $v['get_seckill'], $v['get_goods']]);
                $v = BaseRepository::getArrayExcept($v, ['get_seckill_time_bucket', 'get_seckill', 'get_goods']);

                $get_seckill_goods_attr = $v['get_seckill_goods_attr'] ?? [];
                unset($v['get_seckill_goods_attr']);
                if (!empty($get_seckill_goods_attr)) {
                    // 有秒杀属性取最小属性价、数量
                    $get_seckill_goods_attr = collect($get_seckill_goods_attr)->sortBy('sec_price')->first();
                    $v['sec_price'] = $get_seckill_goods_attr['sec_price'] ?? 0;
                    $v['sec_num'] = $get_seckill_goods_attr['sec_num'] ?? 0;
                    $v['sec_limit'] = $get_seckill_goods_attr['sec_limit'] ?? 0;
                }

                $res[$k] = $v;

                $res[$k]['current_time'] = $now;
                $res[$k]['begin_time'] = TimeRepository::getLocalStrtoTime($v['begin_time']);

                if ($tomorrow == 1) {
                    $res[$k]['begin_time'] = TimeRepository::getLocalStrtoTime($v['begin_time']) + $tmr;
                    $res[$k]['end_time'] = TimeRepository::getLocalStrtoTime($v['end_time']) + $tmr;
                } else {
                    $res[$k]['begin_time'] = TimeRepository::getLocalStrtoTime($v['begin_time']);
                    $res[$k]['end_time'] = TimeRepository::getLocalStrtoTime($v['end_time']);
                }
                if ($res[$k]['begin_time'] < $now && $res[$k]['end_time'] > $now) {
                    $res[$k]['status'] = true;
                }
                if ($res[$k]['end_time'] < $now) {
                    $res[$k]['is_end'] = true;
                }
                if ($res[$k]['begin_time'] > $now) {
                    $res[$k]['soon'] = true;
                }

                $goodsSelf = false;
                if ($v['ru_id'] == 0) {
                    $goodsSelf = true;
                }

                $res[$k]['sec_price'] = $this->dscRepository->getPriceFormat($v['sec_price'], true, false, $goodsSelf);
                $res[$k]['data_end_time'] = TimeRepository::getLocalDate('H:i:s', $res[$k]['begin_time']);
                $res[$k]['sec_price_formated'] = $this->dscRepository->getPriceFormat($v['sec_price'], true, true, $goodsSelf);

                $res[$k]['market_price'] = $this->dscRepository->getPriceFormat($v['market_price'], true, false, $goodsSelf);
                $res[$k]['market_price_formated'] = $this->dscRepository->getPriceFormat($v['market_price'], true, true, $goodsSelf);

                $res[$k]['percent'] = ($v['sec_num'] > 0) ? ceil($v['sales_volume'] / $v['sec_num'] * 100) : 100;
                $res[$k]['goods_thumb'] = $this->dscRepository->getImagePath($v['goods_thumb']);
                $res[$k]['url'] = dsc_url('/#/seckill/detail') . '?' . http_build_query(['seckill_id' => $v['id'], 'tomorrow' => $tomorrow], '', '&');
                $res[$k]['app_page'] = config('route.seckill.detail') . $v['id'] . '&tomorrow=' . $tomorrow;

                $merchant = $merchantList[$v['ru_id']] ?? [];
                $res[$k]['country_icon'] = $merchant['country_icon'] ?? '';
            }
        }

        return $res;
    }

    /**
     * 获取当前分类的子分类列表
     * @param int $cat_id
     * @return \Illuminate\Cache\CacheManager|mixed
     * @throws \Exception
     */
    public function getSecondCategory($cat_id = 0)
    {
        $cache_id = 'get_mobile_second_category_' . $cat_id;

        $list = cache($cache_id);
        $list = !is_null($list) ? $list : false;

        if ($list === false) {
            $arr = Category::where('is_show', 1)
                ->where('parent_id', $cat_id)
                ->orderBy('sort_order')
                ->orderBy('cat_id');

            $arr = $arr->with([
                'getGoods' => function ($query) {
                    $query = $query->where('is_on_sale', 1)
                        ->where('is_show', 1)
                        ->where('is_alone_sale', 1)
                        ->where('is_delete', 0);

                    if (config('shop.review_goods')) {
                        $query = $query->whereIn('review_status', [3, 4, 5]);
                    }

                    $query->orderBy('weights', 'DESC')->orderBy('goods_id', 'desc'); // 权重值
                }
            ]);
            $arr = $arr->limit(14);
            $arr = BaseRepository::getToArrayGet($arr);

            $list = [];
            if ($arr) {
                foreach ($arr as $key => $v) {
                    $list[$key]['cat_id'] = $v['cat_id'];
                    $list[$key]['parent_id'] = $v['parent_id'];
                    $list[$key]['cat_name'] = (isset($v['cat_alias_name']) && !empty($v['cat_alias_name'])) ? $v['cat_alias_name'] : $v['cat_name'];
                    if ($v['touch_icon']) {
                        $list[$key]['touch_icon'] = $this->dscRepository->getImagePath($v['touch_icon']);
                    } else {
                        $list[$key]['touch_icon'] = $this->dscRepository->getImagePath($v['get_goods']['goods_thumb'] ?? '');
                    }

                    $list[$key]['cat_icon'] = $this->dscRepository->getImagePath($v['cat_icon']);
                    $list[$key]['touch_catads'] = $this->dscRepository->getImagePath($v['touch_catads']);

                }
            }
            $list = collect($list)->values()->all();

            cache([$cache_id => $list], Carbon::now()->addDays(7));
        }

        return $list;
    }

    /**
     * 获得分类下的品牌
     *
     * @param array $children
     * @return array
     */
    public function getCategoryBrandList($children = [])
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

        if (empty($keywords) && $children) {
            $res = $res->whereIn('cat_id', $children);
        }

        $res = $res->with([
            'getBrand' => function ($query) {
                $query->where('is_show', 1)->limit(12);
            }
        ]);

        $res = $res->groupBy('brand_id');

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $k => $v) {
                if ($v['get_brand']) {
                    $brand = $v['get_brand'];
                    $arr[$k]['brand_id'] = $brand['brand_id'];
                    $arr[$k]['brand_name'] = $brand['brand_name'];
                    $arr[$k]['brand_logo'] = $this->dscRepository->getImagePath('data/brandlogo/' . $brand['brand_logo']);
                }
            }

            $arr = array_values($arr);
        }

        return $arr;
    }

    /**
     * 获取首页模块拼团商品
     *
     * @param int $tc_id
     * @return array
     */
    public function getTeamGoods($tc_id = 0)
    {
        if (!file_exists(MOBILE_TEAM)) {
            return [];
        }
        $list = app(\App\Services\Team\TeamService::class)->getGoods($tc_id);
        return $list;
    }
}
