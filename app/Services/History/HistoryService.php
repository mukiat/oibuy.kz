<?php

namespace App\Services\History;


use App\Models\Goods;
use App\Models\GoodsHistory;
use App\Models\SellerShopinfo;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Brand\BrandDataHandleService;
use App\Services\Gallery\GalleryDataHandleService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Goods\GoodsGalleryService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\User\UserCommonService;


class HistoryService
{
    protected $dscRepository;
    protected $timeRepository;
    protected $goodsCommonService;
    protected $sessionRepository;
    protected $goodsGalleryService;

    public function __construct(
        DscRepository $dscRepository,
        TimeRepository $timeRepository,
        GoodsCommonService $goodsCommonService,
        SessionRepository $sessionRepository,
        GoodsGalleryService $goodsGalleryService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->timeRepository = $timeRepository;
        $this->goodsCommonService = $goodsCommonService;
        $this->sessionRepository = $sessionRepository;
        $this->goodsGalleryService = $goodsGalleryService;
    }


    /**
     * 生成缓存------浏览记录
     *
     * @param int $user_id
     * @param int $goods_id
     * @return bool
     */
    public function goodsHistoryList($user_id = 0, $goods_id = 0)
    {
        if (empty($goods_id)) {
            return false;
        }

        //浏览历史数量
        $history_number = config('shop.history_number');
        if ($history_number <= 0) {
            return false;
        }

        $session_id = $this->sessionRepository->realCartMacIp();
        $count = GoodsHistory::where('goods_id', $goods_id);
        if ($user_id > 0) {
            $count = $count->where('user_id', $user_id);
        } else {
            $count = $count->where('session_id', $session_id);
        }
        $count = $count->count();
        if (empty($count)) {
            $info = array(
                'user_id' => $user_id,
                'session_id' => $session_id,
                'goods_id' => $goods_id,
                'add_time' => $this->timeRepository->getGmTime()
            );
            GoodsHistory::insert($info);

            //如果用户的浏览历史数量大于后台设置的浏览历史数量 就删除老的数据
            $goods_history_list = GoodsHistory::select('history_id');
            if ($user_id > 0) {
                $goods_history_list = $goods_history_list->where('user_id', $user_id);
            } else {
                $goods_history_list = $goods_history_list->where('session_id', $session_id);
            }
            $goods_history_list = $goods_history_list->orderBy('add_time', 'ASC');
            $goods_history_list = BaseRepository::getToArrayGet($goods_history_list);
            $surplus_count = count($goods_history_list) - $history_number;
            if ($surplus_count > 0) {
                $history_id_list = BaseRepository::getTake($goods_history_list, $surplus_count);
                GoodsHistory::whereIn('history_id', $history_id_list)->delete();
            }
        }
    }

    /**
     * 返回浏览记录集合手机端专用
     *
     * @param int $user_id
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @return array
     */
    public function getHistoryListMobile($user_id = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0)
    {
        if (empty($user_id)) {
            return [];
        }

        $GoodsHistory = [];

        $user_rank = app(UserCommonService::class)->getUserRankByUid($user_id);
        $user_rank_id = $user_rank['rank_id'] ?? 0;

        // 仅显示70天内的浏览记录
        $time = TimeRepository::getLocalStrtoTime('-70 days');

        $result = GoodsHistory::where('user_id', $user_id)
            ->where('add_time', '>=', $time)
            ->orderBy('add_time', 'DESC')
            ->orderBy('goods_id', 'DESC');
        $result = BaseRepository::getToArrayGet($result);

        if ($result) {

            $goods_id = BaseRepository::getKeyPluck($result, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id);
            $sql = [
                'where' => [
                    [
                        'name' => 'is_on_sale',
                        'value' => 1
                    ],
                    [
                        'name' => 'is_alone_sale',
                        'value' => 1
                    ],
                    [
                        'name' => 'is_delete',
                        'value' => 0
                    ],
                    [
                        'name' => 'is_show',
                        'value' => 1
                    ]
                ]
            ];

            if (config('shop.review_goods') == 1) {
                $sql['where'][] = [
                    'name' => 'review_status',
                    'value' => 2,
                    'condition' => '>'
                ];
            }

            $goodsList = BaseRepository::getArraySqlGet($goodsList, $sql, 1);
            $goods_id = BaseRepository::getKeyPluck($goodsList, 'goods_id');

            $memberPriceList = GoodsDataHandleService::goodsMemberPrice($goods_id, $user_rank_id);
            $warehouseGoodsList = GoodsDataHandleService::getWarehouseGoodsDataList($goods_id, $warehouse_id);
            $warehouseAreaGoodsList = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods_id, $area_id, $area_city);

            $brand_id = BaseRepository::getKeyPluck($goodsList, 'brand_id');
            $brandList = BrandDataHandleService::goodsBrand($brand_id, ['brand_id', 'brand_name']);

            if ($goods_id) {
                $sql = [
                    'whereIn' => [
                        [
                            'name' => 'goods_id',
                            'value' => $goods_id
                        ]
                    ]
                ];
                $result = BaseRepository::getArraySqlGet($result, $sql);

                if (empty($result)) {
                    return [];
                }
            }

            foreach ($result as $key => $value) {

                $row = $goodsList[$value['goods_id']] ?? [];
                $brand = $brandList[$row['brand_id']] ?? [];
                $row = BaseRepository::getArrayMerge($row, $brand);

                $price = [
                    'model_price' => isset($row['model_price']) ? $row['model_price'] : 0,
                    'user_price' => $memberPriceList[$row['goods_id']]['user_price'] ?? 0,
                    'percentage' => $memberPriceList[$row['goods_id']]['percentage'] ?? 0,
                    'warehouse_price' => $warehouseGoodsList[$row['goods_id']]['warehouse_price'] ?? 0,
                    'region_price' => $warehouseAreaGoodsList[$row['goods_id']]['region_price'] ?? 0,
                    'shop_price' => isset($row['shop_price']) ? $row['shop_price'] : 0,
                    'warehouse_promote_price' => $warehouseGoodsList[$row['goods_id']]['warehouse_promote_price'] ?? 0,
                    'region_promote_price' => $warehouseAreaGoodsList[$row['goods_id']]['region_promote_price'] ?? 0,
                    'promote_price' => isset($row['promote_price']) ? $row['promote_price'] : 0,
                    'wg_number' => $warehouseGoodsList[$row['goods_id']]['region_number'] ?? 0,
                    'wag_number' => $warehouseAreaGoodsList[$row['goods_id']]['region_number'] ?? 0,
                    'goods_number' => isset($row['goods_number']) ? $row['goods_number'] : 0
                ];

                $price = $this->goodsCommonService->getGoodsPrice($price, $user_rank['discount'] / 100, $row);

                $goods['id'] = $row['goods_id'];
                $goods['name'] = $row['goods_name'];
                $goods['img'] = $this->dscRepository->getImagePath($row['goods_img']);

                $goodsSelf = false;
                if ($row['user_id'] == 0) {
                    $goodsSelf = true;
                }

                $goods['price'] = $this->dscRepository->getPriceFormat($price['shop_price'], true, true, $goodsSelf);
                $goods['addtime'] = TimeRepository::getLocalDate("Y-m-d H:i:s", $value['add_time']);
                $GoodsHistory[] = $goods;
            }
        }

        return $GoodsHistory;
    }

    /**
     * 删除浏览记录
     *
     * @param int $user_id
     * @param int $goods_id
     */
    public function historyDel($user_id = 0, $goods_id = 0)
    {
        $result = GoodsHistory::whereRaw(1);

        if (!empty($goods_id)) {
            $result = GoodsHistory::where('goods_id', $goods_id);
        }

        if (!empty($user_id)) {
            $result = $result->where('user_id', $user_id);
        } else {
            $result = $result->where('session_id', $this->sessionRepository->realCartMacIp());
        }
        return $result->delete();
    }

    /**
     * 浏览历史PC专用
     *
     * @param int $size
     * @param int $page
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param int $goods_id
     * @param int $ship
     * @param int $self
     * @param int $have
     * @return array
     * @throws \Exception
     */
    public function getGoodsHistoryPc($size = 0, $page = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0, $goods_id = 0, $ship = 0, $self = 0, $have = 0)
    {
        $arr = [];

        $user_id = session('user_id', 0);
        $history_list = GoodsHistory::whereRaw(1);
        if ($user_id > 0) {
            $history_list = $history_list->where('user_id', $user_id);
        } else {
            $history_list = $history_list->where('session_id', $this->sessionRepository->realCartMacIp());
        }
        $history_list = $history_list->orderBy('add_time', 'DESC');
        $history_list = BaseRepository::getToArrayGet($history_list);
        $goods_id_array = BaseRepository::getKeyPluck($history_list, 'goods_id');

        $res = Goods::where('is_on_sale', 1)
            ->where('is_alone_sale', 1)
            ->where('is_delete', 0)
            ->where('is_show', 1)
            ->whereIn('goods_id', $goods_id_array);

        if ($goods_id > 0) {
            $res = $res->where('goods_id', '<>', $goods_id);
        }

        if ($ship == 1) {
            $res = $res->where('is_shipping', 1);
        }

        if ($have == 1) {
            $res = $res->where('goods_number', '>', 0);
        }

        if ($self == 1) {
            $res = $res->where(function ($query) {
                $query->where('user_id', 0)
                    ->orWhere(function ($query) {
                        $query->whereHasIn('getShopInfo', function ($query) {
                            $query->where('self_run', 1);
                        });
                    });
            });
        }

        if (config('shop.review_goods')) {
            $res = $res->whereIn('review_status', [3, 4, 5]);
        }

        $res = $this->dscRepository->getAreaLinkGoods($res, $area_id, $area_city);

        $start = ($page - 1) * $size;
        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($size > 0) {
            $res = $res->take($size);
        }

        $goods_str = BaseRepository::getImplode($goods_id_array);
        if (!empty($goods_str)) {
            $res = $res->orderByRaw('FIELD(goods_id,' . $goods_str . ')');
        }

        $res = BaseRepository::getToArrayGet($res);

        $user_rank = session('user_rank', 0);
        $discount = session('discount', 1);

        if ($res) {

            $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');
            $memberPriceList = GoodsDataHandleService::goodsMemberPrice($goods_id, $user_rank);
            $warehouseGoodsList = GoodsDataHandleService::getWarehouseGoodsDataList($goods_id, $warehouse_id);
            $warehouseAreaGoodsList = GoodsDataHandleService::getWarehouseAreaGoodsDataList($goods_id, $area_id, $area_city);
            $galleryList = GalleryDataHandleService::getGoodsGalleryDataList($goods_id, ['img_id', 'goods_id', 'external_url', 'img_url', 'thumb_url']);
            $commentGoodsList = GoodsDataHandleService::CommentGoodsReviewCount($goods_id, ['comment_id', 'id_value']);
            $collectGoodsList = GoodsDataHandleService::CollectGoodsDataList($goods_id, ['goods_id']);

            $brand_id = BaseRepository::getKeyPluck($res, 'brand_id');
            $brandList = BrandDataHandleService::goodsBrand($brand_id, ['brand_id', 'brand_name']);

            $seller_id = BaseRepository::getKeyPluck($res, 'user_id');

            $shopInformation = MerchantDataHandleService::MerchantsShopInformationDataList($seller_id);
            $sellerShopinfo = MerchantDataHandleService::SellerShopinfoDataList($seller_id);
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($seller_id, 0, $sellerShopinfo, $shopInformation);

            foreach ($res as $row) {

                $row['get_brand'] = $brandList[$row['brand_id']] ?? [];
                $row = BaseRepository::getArrayMerge($row, $row['get_brand']);

                $price = [
                    'model_price' => isset($row['model_price']) ? $row['model_price'] : 0,
                    'user_price' => $memberPriceList[$row['goods_id']]['user_price'] ?? 0,
                    'percentage' => $memberPriceList[$row['goods_id']]['percentage'] ?? 0,
                    'warehouse_price' => $warehouseGoodsList[$row['goods_id']]['warehouse_price'] ?? 0,
                    'region_price' => $warehouseAreaGoodsList[$row['goods_id']]['region_price'] ?? 0,
                    'shop_price' => isset($row['shop_price']) ? $row['shop_price'] : 0,
                    'warehouse_promote_price' => $warehouseGoodsList[$row['goods_id']]['warehouse_promote_price'] ?? 0,
                    'region_promote_price' => $warehouseAreaGoodsList[$row['goods_id']]['region_promote_price'] ?? 0,
                    'promote_price' => isset($row['promote_price']) ? $row['promote_price'] : 0,
                    'wg_number' => $warehouseGoodsList[$row['goods_id']]['region_number'] ?? 0,
                    'wag_number' => $warehouseAreaGoodsList[$row['goods_id']]['region_number'] ?? 0,
                    'goods_number' => isset($row['goods_number']) ? $row['goods_number'] : 0
                ];

                $price = $this->goodsCommonService->getGoodsPrice($price, $discount, $row);

                $row['shop_price'] = $price['shop_price'];
                $row['promote_price'] = $price['promote_price'];
                $row['goods_number'] = $price['goods_number'];

                $arr[$row['goods_id']] = $row;

                if ($row['promote_price'] > 0) {
                    $promote_price = $this->goodsCommonService->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                } else {
                    $promote_price = 0;
                }

                $arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
                $arr[$row['goods_id']]['goods_sn'] = $row['goods_sn'];
                $arr[$row['goods_id']]['sales_volume'] = $row['sales_volume'];
                $arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
                $arr[$row['goods_id']]['short_name'] = config('shop.goods_name_length') > 0 ? $this->dscRepository->subStr($row['goods_name'], config('shop.goods_name_length')) : $row['goods_name'];

                $goodsSelf = false;
                if ($row['user_id'] == 0) {
                    $goodsSelf = true;
                }

                $arr[$row['goods_id']]['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);

                $arr[$row['goods_id']]['shop_price'] = $this->dscRepository->getPriceFormat($row['shop_price'], true, true, $goodsSelf);
                $arr[$row['goods_id']]['promote_price'] = ($promote_price > 0) ? $this->dscRepository->getPriceFormat($promote_price, true, true, $goodsSelf) : '';

                $arr[$row['goods_id']]['brand_name'] = isset($row['brand_name']) ? $row['brand_name'] : '';
                $arr[$row['goods_id']]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);

                $arr[$row['goods_id']]['brand_url'] = $this->dscRepository->buildUri('brand', ['bid' => $row['brand_id']]);

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

                $arr[$row['goods_id']]['review_count'] = $review_count;

                $shop_information = $merchantList[$row['user_id']] ?? []; //通过ru_id获取到店铺信息;

                $arr[$row['goods_id']]['kf_type'] = $shop_information['kf_type'] ?? 0;

                /* 处理客服QQ数组 by kong */
                $arr[$row['goods_id']]['kf_qq'] = $shop_information['kf_qq'] ?? '';

                /* 处理客服旺旺数组 by kong */
                $arr[$row['goods_id']]['kf_ww'] = $shop_information['kf_ww'] ?? '';

                $arr[$row['goods_id']]['rz_shop_name'] = isset($shop_information['shop_name']) ? $shop_information['shop_name'] : ''; //店铺名称
                $arr[$row['goods_id']]['shop_name'] = $arr[$row['goods_id']]['rz_shop_name'];
                $arr[$row['goods_id']]['user_id'] = $row['user_id'];
                $arr[$row['goods_id']]['is_shipping'] = $row['is_shipping'];
                $arr[$row['goods_id']]['self_run'] = $self;

                $build_uri = [
                    'urid' => $row['user_id'],
                    'append' => $arr[$row['goods_id']]['rz_shop_name'],
                ];

                $shopUrl = app(DscRepository::class)->buildUri('merchants_store', $build_uri);
                $arr[$row['goods_id']]['store_url'] = $shopUrl;
                $arr[$row['goods_id']]['shopUrl'] = $shopUrl;

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
                $arr[$row['goods_id']]['is_collect'] = $collect_count;

                $arr[$row['goods_id']]['pictures'] = $this->goodsGalleryService->getGoodsGallery($row['goods_id'], $galleryList, $row['goods_thumb'], 6); // 商品相册

                if (config('shop.customer_service') == 0) {
                    $seller_id = 0;
                } else {
                    $seller_id = $row['user_id'];
                }

                /*  @author-bylu 判断当前商家是否允许"在线客服" */
                $arr[$row['goods_id']]['is_im'] = isset($shop_information['is_im']) ? $shop_information['is_im'] : ''; //平台是否允许商家使用"在线客服";
                if ($seller_id == 0) {
                    //判断平台是否开启了IM在线客服
                    $kf_im_switch = SellerShopinfo::where('ru_id', 0)->value('kf_im_switch');
                    if ($kf_im_switch) {
                        $arr[$row['goods_id']]['is_dsc'] = true;
                    } else {
                        $arr[$row['goods_id']]['is_dsc'] = false;
                    }
                } else {
                    $arr[$row['goods_id']]['is_dsc'] = false;
                }
            }
        }

        return $arr;
    }

    /**
     * 浏览记录数量PC专用
     * @param int $goods_id
     * @param int $ship
     * @param int $self
     * @param int $have
     * @return int
     */
    public function getGoodsHistoryCount($goods_id = 0, $ship = 0, $self = 0, $have = 0)
    {
        $user_id = session('user_id', 0);
        $history_list = GoodsHistory::whereRaw(1);
        if ($user_id > 0) {
            $history_list = $history_list->where('user_id', $user_id);
        } else {
            $session_id = $this->sessionRepository->realCartMacIp();
            $history_list = $history_list->where('session_id', $session_id);
        }

        $history_list = BaseRepository::getToArrayGet($history_list);
        if (empty($history_list)) {
            return 0;
        }
        $goods_id_array = BaseRepository::getKeyPluck($history_list, 'goods_id');

        $res = Goods::where('is_on_sale', 1)
            ->where('is_alone_sale', 1)
            ->where('is_delete', 0)
            ->where('is_show', 1)
            ->whereIn('goods_id', $goods_id_array);

        if ($goods_id > 0) {
            $res = $res->where('goods_id', '<>', $goods_id);
        }

        if ($ship == 1) {
            $res = $res->where('is_shipping', 1);
        }

        if ($have == 1) {
            $res = $res->where('goods_number', '>', 0);
        }

        if ($self == 1) {
            $res = $res->where(function ($query) {
                $query->where('user_id', 0)
                    ->orWhere(function ($query) {
                        $query->whereHasIn('getShopInfo', function ($query) {
                            $query->where('self_run', 1);
                        });
                    });
            });
        }

        if (config('shop.review_goods')) {
            $res = $res->whereIn('review_status', [3, 4, 5]);
        }

        $count = $res->count();

        return $count;
    }
}
