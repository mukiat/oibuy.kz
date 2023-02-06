<?php

namespace App\Services\User;

use App\Libraries\Pager;
use App\Models\CollectGoods;
use App\Models\SellerShopinfo;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Comment\CommentService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;

class UserCollectGoodsService
{
    protected $dscRepository;
    protected $goodsCommonService;
    protected $merchantCommonService;

    public function __construct(
        DscRepository $dscRepository,
        GoodsCommonService $goodsCommonService,
        MerchantCommonService $merchantCommonService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->goodsCommonService = $goodsCommonService;
        $this->merchantCommonService = $merchantCommonService;
    }

    /**
     * 获取指定用户的收藏商品列表
     *
     * @param int $user_id
     * @param int $record_count
     * @param int $page
     * @param string $pageFunc
     * @param int $size
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @return array
     */
    public function getCollectionGoods($user_id = 0, $record_count = 0, $page = 1, $pageFunc = '', $size = 10, $warehouse_id = 0, $area_id = 0, $area_city = 0)
    {
        $pagerParams = [
            'total' => $record_count,
            'listRows' => $size,
            'page' => $page,
            'funName' => $pageFunc,
            'pageType' => 1
        ];

        $collection = new Pager($pagerParams);
        $pager = $collection->fpage([0, 4, 5, 6, 9]);

        $res = CollectGoods::select('rec_id', 'goods_id', 'is_attention', 'add_time')
            ->where('user_id', $user_id);

        $where = [
            'open_area_goods' => config('shop.open_area_goods'),
            'review_goods' => config('shop.review_goods'),
            'area_id' => $area_id,
            'area_city' => $area_city
        ];
        $res = $res->whereHasIn('getGoods', function ($query) use ($where) {
            $query = $query->where('is_on_sale', 1)
                ->where('is_alone_sale', 1)
                ->where('is_delete', 0);

            $query = $this->dscRepository->getAreaLinkGoods($query, $where['area_id'], $where['area_city']);

            if ($where['review_goods'] == 1) {
                $query->whereIn('review_status', [3, 4, 5]);
            }
        });

        $where = [
            'area_id' => $area_id,
            'area_city' => $area_city,
            'area_pricetype' => config('shop.area_pricetype')
        ];

        $user_rank = session('user_rank', 0);
        $discount = session('discount', 1);

        $res = $res->with([
            'getGoods',
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
            foreach ($res as $key => $row) {
                $row = BaseRepository::getArrayMerge($row, $row['get_goods']);

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

                $price = $this->goodsCommonService->getGoodsPrice($price, $discount, $row);

                $row['shop_price'] = $price['shop_price'];
                $row['promote_price'] = $price['promote_price'];
                $row['goods_number'] = $price['goods_number'];

                $goods_list[$row['goods_id']] = $row;

                if ($row['promote_price'] > 0) {
                    $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                } else {
                    $promote_price = 0;
                }

                $goods_list[$row['goods_id']]['rec_id'] = $row['rec_id'];
                $goods_list[$row['goods_id']]['is_attention'] = $row['is_attention'];
                $goods_list[$row['goods_id']]['goods_id'] = $row['goods_id'];
                $goods_list[$row['goods_id']]['goods_name'] = $row['goods_name'];
                $goods_list[$row['goods_id']]['market_price'] = $this->dscRepository->getPriceFormat($row['market_price']);
                $goods_list[$row['goods_id']]['shop_price'] = $this->dscRepository->getPriceFormat($row['shop_price']);
                $goods_list[$row['goods_id']]['promote_price'] = ($promote_price > 0) ? $this->dscRepository->getPriceFormat($promote_price) : '';
                $goods_list[$row['goods_id']]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
                $goods_list[$row['goods_id']]['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                $goods_list[$row['goods_id']]['add_time'] = TimeRepository::getLocalDate("Y-m-d H:i:s", $row['add_time']);

                $goods_list[$row['goods_id']]['zconments'] = app(CommentService::class)->goodsZconments($row['goods_id']);
            }
        }

        $arr = ['goods_list' => $goods_list, 'record_count' => $record_count, 'pager' => $pager, 'size' => $size];

        return $arr;
    }

    /**
     * 获取指定用户的收藏商品列表
     *
     * @param $user_id
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @return array
     * @throws \Exception
     */
    public function getDefaultCollectionGoods($user_id, $warehouse_id = 0, $area_id = 0, $area_city = 0)
    {
        $res = CollectGoods::select('rec_id', 'goods_id', 'is_attention', 'add_time')
            ->where('user_id', $user_id);

        $where = [
            'open_area_goods' => config('shop.open_area_goods'),
            'review_goods' => config('shop.review_goods'),
            'area_id' => $area_id,
            'area_city' => $area_city
        ];
        $res = $res->whereHasIn('getGoods', function ($query) use ($where) {
            $query = $query->where('is_on_sale', 1)
                ->where('is_alone_sale', 1)
                ->where('is_delete', 0);

            $query = $this->dscRepository->getAreaLinkGoods($query, $where['area_id'], $where['area_city']);

            if ($where['review_goods'] == 1) {
                $query->whereIn('review_status', [3, 4, 5]);
            }
        });

        $where = [
            'area_id' => $area_id,
            'area_city' => $area_city,
            'area_pricetype' => config('shop.area_pricetype')
        ];

        $user_rank = session('user_rank', 0);
        $discount = session('discount', 1);

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

        $res = $res->take(5);

        $res = BaseRepository::getToArrayGet($res);

        $goods_list = [];
        if ($res) {

            $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id);

            $ru_id = BaseRepository::getKeyPluck($goodsList, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($res as $row) {

                $goods = $goodsList[$row['goods_id']] ?? [];

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

                $price = $this->goodsCommonService->getGoodsPrice($price, $discount, $row);

                $row['shop_price'] = $price['shop_price'];
                $row['promote_price'] = $price['promote_price'];
                $row['goods_number'] = $price['goods_number'];

                $row = BaseRepository::getArrayMerge($row, $goods);

                $goods_list[$row['goods_id']] = $row;

                if ($row['promote_price'] > 0) {
                    $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                } else {
                    $promote_price = 0;
                }

                $goods_list[$row['goods_id']]['rec_id'] = $row['rec_id'];
                $goods_list[$row['goods_id']]['is_attention'] = $row['is_attention'];
                $goods_list[$row['goods_id']]['goods_id'] = $row['goods_id'];

                $shop_information = $merchantList[$row['user_id']] ?? [];
                $goods_list[$row['goods_id']]['shop_name'] = $shop_information['shop_name'] ?? [];

                //IM or 客服
                if (config('shop.customer_service') == 0) {
                    $ru_id = 0;
                } else {
                    $ru_id = $row['user_id'];
                }

                $goods_list[$row['goods_id']]['is_im'] = isset($shop_information['is_im']) ? $shop_information['is_im'] : 0; //平台是否允许商家使用"在线客服";

                if ($ru_id == 0) {
                    //判断平台是否开启了IM在线客服
                    $kf_im_switch = SellerShopinfo::where('ru_id', 0)->value('kf_im_switch');
                    if ($kf_im_switch) {
                        $goods_list[$row['goods_id']]['is_dsc'] = true;
                    } else {
                        $goods_list[$row['goods_id']]['is_dsc'] = false;
                    }
                } else {
                    $goods_list[$row['goods_id']]['is_dsc'] = false;
                }

                $goods_list[$row['goods_id']]['add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['add_time']);
                $goods_list[$row['goods_id']]['goods_name'] = $row['goods_name'];
                $goods_list[$row['goods_id']]['market_price'] = $this->dscRepository->getPriceFormat($row['market_price']);
                $goods_list[$row['goods_id']]['shop_price'] = $this->dscRepository->getPriceFormat($row['shop_price']);
                $goods_list[$row['goods_id']]['promote_price'] = ($promote_price > 0) ? $this->dscRepository->getPriceFormat($promote_price) : '';
                $goods_list[$row['goods_id']]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);
                $goods_list[$row['goods_id']]['shop_url'] = $this->dscRepository->buildUri('merchants_store', ['urid' => $row['user_id']]);
                $goods_list[$row['goods_id']]['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
            }
        }

        return $goods_list;
    }
}
