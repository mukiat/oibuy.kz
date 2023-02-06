<?php

namespace App\Services\User;

use App\Models\CollectGoods;
use App\Models\CollectStore;
use App\Models\Coupons;
use App\Models\CouponsUser;
use App\Models\MerchantsShopInformation;
use App\Models\SellerShopinfo;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Activity\CouponsService;
use App\Services\Common\AreaService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;

/**
 * 收藏
 * Class Collect
 * @package App\Services
 */
class CollectService
{
    protected $goodsService;
    protected $areaService;
    protected $dscRepository;
    protected $userRankService;
    protected $merchantCommonService;
    protected $goodsCommonService;
    protected $couponsService;

    public function __construct(
        CouponsService $couponsService,
        AreaService $areaService,
        DscRepository $dscRepository,
        UserRankService $userRankService,
        MerchantCommonService $merchantCommonService,
        GoodsCommonService $goodsCommonService
    )
    {
        $this->couponsService = $couponsService;
        $this->areaService = $areaService;
        $this->dscRepository = $dscRepository;
        $this->userRankService = $userRankService;
        $this->merchantCommonService = $merchantCommonService;
        $this->goodsCommonService = $goodsCommonService;
    }


    /**
     * 收藏店铺列表
     *
     * @param int $user_id
     * @param int $page
     * @param int $size
     * @return array
     * @throws \Exception
     */
    public function getUserShopList($user_id = 0, $page = 1, $size = 10)
    {
        $begin = $page > 0 ? ($page - 1) * $size : 0;
        $res = CollectStore::where("user_id", $user_id);
        $res = $res->whereHasIn('getSellerShopinfo');
        $res = $res->orderBy('ru_id', 'desc')
            ->offset($begin)
            ->limit($size);

        $res = BaseRepository::getToArrayGet($res);

        $store_list = [];
        if ($res) {

            $ru_id = BaseRepository::getKeyPluck($res, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($res as $key => $row) {

                $merchant = $merchantList[$row['ru_id']] ?? [];

                $row = BaseRepository::getArrayMerge($row, $merchant);

                $store_list[$key]['collect_number'] = CollectStore::where('ru_id', $row['ru_id'])->where('user_id', $user_id)->count();
                $store_list[$key]['rec_id'] = $row['rec_id'];

                $store_list[$key]['shoprz_brand_name'] = $row['shoprz_brand_name'];
                $store_list[$key]['shop_name_suffix'] = $row['shop_name_suffix'];
                $store_list[$key]['shoprz_brandName'] = $row['shoprz_brand_name'];
                $store_list[$key]['shopNameSuffix'] = $row['shop_name_suffix'];

                //取消关注链接
                $store_list[$key]['cancel_collect_shop'] = route('api.collect.collectshop', ['rec_id' => $row['rec_id']]);
                $store_list[$key]['shop_id'] = $row['ru_id'];
                $store_list[$key]['store_name'] = $merchant['shop_name'] ?? '';
                $store_list[$key]['shop_bg_logo'] = $this->dscRepository->getImagePath(str_replace('../', '', $row['shop_logo']));
                $store_list[$key]['shop_logo'] = $this->dscRepository->getImagePath(str_replace('../', '', $row['logo_thumb']));
                $store_list[$key]['count_store'] = CollectStore::where('ru_id', $row['ru_id'])->count();
                $store_list[$key]['add_time'] = TimeRepository::getLocalDate("Y-m-d", $row['add_time']);
                $store_list[$key]['kf_type'] = $row['kf_type'];
                $store_list[$key]['kf_ww'] = $row['kf_ww'];
                $store_list[$key]['kf_qq'] = $row['kf_qq'];
                $store_list[$key]['ru_id'] = $row['ru_id'];
                $store_list[$key]['brand_thumb'] = $this->dscRepository->getImagePath($row['brand_thumb']);
                $store_list[$key]['url'] = route('api.shop.shopdetail', ['id' => $row['ru_id']]);
            }
        }

        return $store_list;
    }

    /**
     * 收藏/移除收藏 店铺
     *
     * @param $shop_id
     * @param $user_id
     * @return int
     * @throws \Exception
     */
    public function collectShop($shop_id = 0, $user_id = 0)
    {
        if ($shop_id && $user_id) {
            $res = CollectStore::select('user_id', 'rec_id')
                ->where('ru_id', $shop_id)
                ->where('user_id', $user_id)
                ->count();
            //未收藏便增加 已收藏便删除
            if ($res > 0) {
                CollectStore::where('ru_id', $shop_id)
                    ->where('user_id', $user_id)
                    ->delete();

                $other = [
                    'collect_count' => 'collect_count - 1'
                ];
                $other = BaseRepository::getDbRaw($other);
                MerchantsShopInformation::where('user_id', $shop_id)->where('collect_count', '>', 0)->update($other);

                //已取消收藏
                return 1;
            } else {
                $time = TimeRepository::getGmTime();
                $rec_id = CollectStore::insertGetId([
                    'user_id' => $user_id,
                    'ru_id' => $shop_id,
                    'add_time' => $time,
                    'is_attention' => 1
                ]);

                if ($rec_id > 0) {
                    $other = [
                        'collect_count' => 'collect_count + 1'
                    ];
                    $other = BaseRepository::getDbRaw($other);
                    MerchantsShopInformation::where('user_id', $shop_id)->update($other);
                }

                $cou_id = Coupons::where('cou_type', VOUCHER_SHOP_CONLLENT)
                    ->where('ru_id', $shop_id)
                    ->where('status', COUPON_STATUS_EFFECTIVE)
                    ->value('cou_id');
                $cou_id = $cou_id ? $cou_id : 0;

                if (!empty($cou_id)) {
                    $this->couponsService->getCouponsReceive($cou_id, $user_id);
                }
                //已收藏
                return 2;
            }
        }
    }


    /**
     * 关注/移除关注 商品
     *
     * @param int $goods_id
     * @param int $user_id
     * @return int
     */
    public function collectGoods($goods_id = 0, $user_id = 0)
    {
        if ($goods_id && $user_id) {
            $res = collectGoods::select('user_id', 'rec_id')
                ->where('goods_id', $goods_id)
                ->where('user_id', $user_id)
                ->count();
            //未关注便增加 已关注便删除
            if ($res > 0) {
                collectGoods::where('goods_id', $goods_id)
                    ->where('user_id', $user_id)
                    ->delete();
                //已取消关注
                return 1;
            } else {
                $time = TimeRepository::getGmTime();
                collectGoods::insert([
                    'user_id' => $user_id,
                    'goods_id' => $goods_id,
                    'add_time' => $time,
                    'is_attention' => 1
                ]);
                //已关注
                return 2;
            }

            //获取关注此商品的会员数 权重
            $attention_num = CollectGoods::where('goods_id', $goods_id)->where('is_attention', 1)->count();

            $num = ['goods_id' => $goods_id, 'user_attention_number' => $attention_num];
            update_attention_num($goods_id, $num);
        } else {
            return 3;//无效参数
        }
    }

    /**
     * 关注商品列表
     *
     * @param int $user_id
     * @param int $page
     * @param int $size
     * @param int $province_id
     * @param int $city_id
     * @return array
     * @throws \Exception
     */
    public function getUserGoodsList($user_id = 0, $page = 1, $size = 10, $province_id = 0, $city_id = 0)
    {
        //用户的等级和折扣
        $user_info = $this->userRankService->getUserRankInfo($user_id);
        $user_rank = $user_info['user_rank'];
        $user_discount = $user_info['discount'];

        /**
         * Start
         *
         * @param $warehouse_id 仓库ID
         * @param $area_id 地区ID
         */
        $areaOther = [
            'province_id' => $province_id,
            'city_id' => $city_id,
        ];
        $areaInfo = $this->areaService->getAreaInfo($areaOther);

        $warehouse_id = $areaInfo['area']['warehouse_id'];
        $area_id = $areaInfo['area']['area_id'];
        $area_city = $areaInfo['area']['city_id'];

        $res = CollectGoods::select('rec_id', 'goods_id', 'is_attention', 'add_time', 'user_id')
            ->where('user_id', $user_id);

        $where = [
            'area_pricetype' => config('shop.area_pricetype'),
            'open_area_goods' => config('shop.open_area_goods'),
            'review_goods' => config('shop.review_goods'),
            'area_id' => $area_id,
            'area_city' => $area_city
        ];
        $res = $res->whereHasIn('getGoods', function ($query) use ($where) {
            $query = $query->where('is_on_sale', 1)
                ->where('is_alone_sale', 1)
                ->where('is_delete', 0)
                ->where('is_show', 1);

            if ($where['review_goods'] == 1) {
                $query = $query->whereIn('review_status', [3, 4, 5]);
            }

            $this->dscRepository->getAreaLinkGoods($query, $where['area_id'], $where['area_city']);
        });

        $res = $res->with([
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
            }
        ]);

        $res = $res->orderBy('rec_id', 'desc');

        $start = ($page - 1) * $size;

        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($size > 0) {
            $res = $res->take($size);
        }

        $res = BaseRepository::getToArrayGet($res);

        $goods_list = [];
        if ($res) {

            $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id);

            $ru_id = BaseRepository::getKeyPluck($goodsList, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($res as $key => $row) {

                $goods = $goodsList[$row['goods_id']] ?? [];

                $row = BaseRepository::getArrayMerge($row, $goods);

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

                $price = $this->goodsCommonService->getGoodsPrice($price, $user_discount, $row);

                $row['shop_price'] = $price['shop_price'];
                $row['promote_price'] = $price['promote_price'];
                $row['goods_number'] = $price['goods_number'];

                $goods_list[$key]['goods_number'] = $row['goods_number'];

                if ($row['promote_price'] > 0) {
                    $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                } else {
                    $promote_price = 0;
                }

                $goods_list[$key]['on_sale'] = $row['is_on_sale'];
                //商品未审核，展示状态已下架
                if ($row['review_status'] <= 2) {
                    $goods_list[$key]['on_sale'] = 0;
                }
                $goods_list[$key]['rec_id'] = $row['rec_id'];
                $goods_list[$key]['is_attention'] = $row['is_attention'];
                $goods_list[$key]['goods_id'] = $row['goods_id'];

                $shop_information = $merchantList[$row['user_id']] ?? [];

                $goods_list[$key]['shop_name'] = $shop_information['shop_name'];

                //IM or 客服
                if (config('shop.customer_service') == 0) {
                    $ru_id = 0;
                } else {
                    $ru_id = $row['user_id'];
                }

                $goods_list[$key]['is_im'] = isset($shop_information['is_im']) ? $shop_information['is_im'] : 0; //平台是否允许商家使用"在线客服";

                if ($ru_id == 0) {
                    //判断平台是否开启了IM在线客服
                    $kf_im_switch = SellerShopinfo::where('ru_id', 0)->value('kf_im_switch');
                    if ($kf_im_switch) {
                        $goods_list[$key]['is_dsc'] = true;
                    } else {
                        $goods_list[$key]['is_dsc'] = false;
                    }
                } else {
                    $goods_list[$key]['is_dsc'] = false;
                }

                $goods_list[$key]['add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['add_time']);
                $goods_list[$key]['goods_name'] = $row['goods_name'];
                $goods_list[$key]['market_price'] = $this->dscRepository->getPriceFormat($row['market_price']);
                $goods_list[$key]['shop_price'] = $this->dscRepository->getPriceFormat($row['shop_price']);
                $goods_list[$key]['promote_price'] = ($promote_price > 0) ? $this->dscRepository->getPriceFormat($promote_price) : '';
                $store_list[$key]['cancel_collect_goods'] = route('api.collect.collectgoods', ['rec_id' => $row['rec_id']]);
                $goods_list[$key]['url'] = dsc_url('/#/goods/' . $row['goods_id']);
                $goods_list[$key]['app_page'] = config('route.goods.detail') . $row['goods_id'];
                $goods_list[$key]['shop_url'] = dsc_url('/#/shopHome/' . $row['user_id']);
                $goods_list[$key]['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
            }
        }

        return $goods_list;
    }

    /**
     * 关注店铺
     *
     * @param int $user_id
     * @return array
     * @throws \Exception
     */
    public function ajaxStoreCollect($user_id = 0)
    {
        $result = ['error' => 0, 'content' => ''];

        if ($user_id > 0) {
            $ru_id = (int)request()->input('ru_id', 0);
            $type = (int)request()->input('type', 0);

            if ($type == 1) {
                $del = CollectStore::where('user_id', $user_id)->where('ru_id', $ru_id)->delete();

                if ($del > 0) {
                    $other = [
                        'collect_count' => 'collect_count - 1'
                    ];
                    $other = BaseRepository::getDbRaw($other);
                    MerchantsShopInformation::where('user_id', $ru_id)->where('collect_count', '>', 0)->update($other);
                }

            } else {
                //判断是否已经关注
                $rec_id = CollectStore::where('user_id', $user_id)->where('ru_id', $ru_id)->value('rec_id');

                if ($rec_id) {
                    $result['error'] = 1;//已关注
                } else {
                    $is_attention = 1;

                    $other = [
                        'user_id' => $user_id,
                        'ru_id' => $ru_id,
                        'add_time' => TimeRepository::getGmTime(),
                        'is_attention' => $is_attention
                    ];
                    $id = CollectStore::insertGetId($other);

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

                        $rec_id = CouponsUser::where('is_delete', 0)->where('user_id', $user_id)->where('cou_id', $cou_id)->value('uc_id');
                        $rec_id = $rec_id ? $rec_id : 0;

                        if (!empty($cou_id) && empty($rec_id)) {
                            $this->couponsService->getCouponsReceive($cou_id, $user_id);
                        }
                    }
                }
            }

            $result['type'] = $type;
        } else {
            $result['error'] = 0;
        }

        return $result;
    }

    /**
     * 关收藏商品数量
     * @param int $goods_id
     * @return int
     */
    public function collectNumber($goods_id = 0)
    {
        return collectGoods::where('goods_id', $goods_id)
            ->count();

    }
}
