<?php

namespace App\Services\Activity;

use App\Libraries\Pager;
use App\Models\AuctionLog;
use App\Models\Cart;
use App\Models\Category;
use App\Models\CollectGoods;
use App\Models\Goods;
use App\Models\GoodsActivity;
use App\Models\GoodsAttr;
use App\Models\GoodsCat;
use App\Models\OrderInfo;
use App\Models\Products;
use App\Models\SellerShopinfo;
use App\Models\Users;
use App\Repositories\Activity\ActivityRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Brand\BrandDataHandleService;
use App\Services\Category\CategoryService;
use App\Services\Common\TemplateService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\User\UserDataHandleService;

/**
 * 活动 ->【拍卖】
 */
class AuctionService
{
    protected $categoryService;
    protected $dscRepository;
    protected $merchantCommonService;
    protected $goodsCommonService;
    protected $templateService;

    public function __construct(
        CategoryService $categoryService,
        DscRepository $dscRepository,
        MerchantCommonService $merchantCommonService,
        GoodsCommonService $goodsCommonService,
        TemplateService $templateService
    )
    {
        $this->categoryService = $categoryService;
        $this->dscRepository = $dscRepository;
        $this->merchantCommonService = $merchantCommonService;
        $this->goodsCommonService = $goodsCommonService;
        $this->templateService = $templateService;
    }

    /**
     * 取得拍卖活动列表
     *
     * @param int $user_id
     * @param string $keywords
     * @param string $sort
     * @param string $order
     * @param int $page
     * @param int $size
     * @return array|mixed
     */
    public function auctionList($user_id = 0, $keywords = '', $sort = 'act_id', $order = 'desc', $page = 1, $size = 10)
    {
        // 排序
        $default_sort_order_method = config('shop.sort_order_method') == 0 ? 'desc' : 'asc';
        $default_sort_order_type = config('shop.sort_order_type') == 0 ? 'act_id' : (config('shop.sort_order_type') == 1 ? 'start_time' : 'end_time');

        $sort = in_array($sort, ['act_id', 'start_time', 'end_time']) ? $sort : $default_sort_order_type;
        $order = in_array($order, ['asc', 'desc']) ? $order : $default_sort_order_method;


        $now = TimeRepository::getGmTime();
        $timeFormat = config('shop.time_format');
        $begin = ($page - 1) * $size;

        $res = GoodsActivity::where('review_status', 3)
            ->where('act_type', GAT_AUCTION)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->where('is_finished', 0);

        $res = $res->whereHasIn('getGoods', function ($query) {
            $query->where('is_on_sale', 1)
                ->where('is_alone_sale', 1)
                ->where('is_delete', 0);
        });

        if ($keywords) {
            $res = $res->where(function ($query) use ($keywords) {
                $query->where('act_name', 'like', '%' . $keywords . '%');

                $query->orWhere(function ($query) use ($keywords) {
                    $query->whereHasIn('getGoods', function ($query) use ($keywords) {
                        $query->where('goods_name', 'like', '%' . $keywords . '%');
                    });
                });
            });
        }

        $res = $res->orderBy($sort, $order);

        if ($begin > 0) {
            $res = $res->skip($begin);
        }

        if ($size > 0) {
            $res = $res->take($size);
        };

        $res = BaseRepository::getToArrayGet($res);

        $auction_list = [];
        if ($res) {
            $auction_list['under_way'] = [];
            $auction_list['finished'] = [];

            $goodsIdList = BaseRepository::getKeyPluck($res, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goodsIdList, ['goods_id', 'goods_thumb']);

            foreach ($res as $row) {

                $goods = $goodsList[$row['goods_id']] ?? [];

                $row = BaseRepository::getArrayMerge($row, $goods);

                $ext_info = unserialize($row['ext_info']);

                $auction = array_merge($row, $ext_info);

                $auction['status_no'] = ActivityRepository::getAuctionStatus($auction, $now);

                $auction['start_time'] = TimeRepository::getLocalDate($timeFormat, $auction['start_time']);

                $auction['end_time'] = TimeRepository::getLocalDate($timeFormat, $auction['end_time']);
                $auction['formated_start_price'] = $this->dscRepository->getPriceFormat($auction['start_price']);
                $auction['formated_end_price'] = $this->dscRepository->getPriceFormat($auction['end_price']);
                $auction['formated_deposit'] = $this->dscRepository->getPriceFormat($auction['deposit']);
                $auction['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);

                $auction['current_time'] = TimeRepository::getLocalDate($timeFormat, $now);

                /* 查询已确认订单数 */
                if ($auction['status_no'] > 1) {
                    $auction['order_count'] = OrderInfo::where('extension_code', 'auction')
                        ->where('extension_id', $auction['act_id'])
                        ->whereIn('order_status', [OS_CONFIRMED, OS_UNCONFIRMED])
                        ->count();
                } else {
                    $auction['order_count'] = 0;
                }

                /* 查询出价用户数和最后出价 */
                $auction['bid_user_count'] = AuctionLog::where('act_id', $auction['act_id'])->count();

                if ($auction['bid_user_count'] > 0) {
                    $log = AuctionLog::where('act_id', $auction['act_id']);

                    $log = $log->whereHasIn('getUsers');

                    $log = $log->with([
                        'getUsers' => function ($query) {
                            $query->select('user_id', 'user_name');
                        }
                    ]);

                    $log = $log->orderBy('log_id', 'desc')->first();

                    $log = $log ? $log->toArray() : [];

                    if (!empty($log)) {
                        $log = array_merge($log, $log['get_users']);
                        $log['formated_bid_price'] = $this->dscRepository->getPriceFormat($log['bid_price'], false); //最后出价
                        $log['bid_time'] = TimeRepository::getLocalDate($timeFormat, $log['bid_time']);
                        $auction['last_bid'] = $log;
                    }
                }

                $auction['is_winner'] = 0;
                if (isset($auction['last_bid']['bid_user']) && $auction['last_bid']['bid_user']) {
                    if ($auction['status_no'] == FINISHED && $auction['last_bid']['bid_user'] == $user_id && $auction['order_count'] == 0) {
                        $auction['is_winner'] = 1;
                    }
                }

                $auction['s_user_id'] = $user_id;
                if ($auction['status_no'] < 2) {
                    $auction_list['under_way'][] = $auction;
                } else {
                    $auction_list['finished'][] = $auction;
                }
            }

            if (isset($auction_list['under_way']) && $auction_list['under_way']) {
                $auction_list = array_merge($auction_list['under_way'], $auction_list['finished']);
            } else {
                $auction_list = $auction_list['finished'];
            }
        }

        return $auction_list;
    }


    /**
     * 取得拍卖活动信息
     *
     * @param int $act_id
     * @param bool $config
     * @param string $path
     * @return mixed
     */
    public function getAuctionInfo($act_id = 0, $config = false, $path = '')
    {
        $time = TimeRepository::getGmTime();

        $auction = GoodsActivity::where('act_id', $act_id);

        if (empty($path)) {
            $auction = $auction->where('review_status', 3);
        }

        $auction = BaseRepository::getToArrayFirst($auction);

        if (!$auction) {
            return [];
        }

        $auction['endTime'] = $auction['end_time'];
        $auction['startTime'] = $auction['start_time'];
        if (isset($auction['act_type']) && $auction['act_type'] != GAT_AUCTION) {
            return [];
        }

        $timeFormat = config('shop.time_format');
        $auction['status_no'] = ActivityRepository::getAuctionStatus($auction, $time);
        if ($config == true) {
            $auction['start_time'] = TimeRepository::getLocalDate($timeFormat, $auction['start_time']);
            $auction['end_time'] = TimeRepository::getLocalDate($timeFormat, $auction['end_time']);
        } else {
            $auction['start_time'] = TimeRepository::getLocalDate($timeFormat, $auction['start_time']);
            $auction['end_time'] = TimeRepository::getLocalDate($timeFormat, $auction['end_time']);
        }
        $ext_info = unserialize($auction['ext_info']);

        $auction = array_merge($auction, $ext_info);
        $auction['formated_start_price'] = $this->dscRepository->getPriceFormat($auction['start_price']);
        $auction['formated_end_price'] = $this->dscRepository->getPriceFormat($auction['end_price']);
        $auction['formated_amplitude'] = $this->dscRepository->getPriceFormat($auction['amplitude']);
        $auction['formated_deposit'] = $this->dscRepository->getPriceFormat($auction['deposit']);

        /* 查询出价用户数和最后出价 */
        $auction['bid_user_count'] = AuctionLog::where('act_id', $act_id)->count();

        if ($auction['bid_user_count'] > 0) {
            $row = AuctionLog::where('act_id', $act_id);

            $row = $row->whereHasIn('getUsers');

            $row = $row->with([
                'getUsers' => function ($query) {
                    $query->select('user_id', 'user_name');
                }
            ]);


            $row = $row->orderBy('log_id', 'desc');

            $row = BaseRepository::getToArrayFirst($row);

            if (!empty($row)) {
                if ($row['get_users']) {
                    $row = array_merge($row, $row['get_users']);
                }

                $row['formated_bid_price'] = $this->dscRepository->getPriceFormat($row['bid_price'], false);
                $row['bid_time'] = TimeRepository::getLocalDate($timeFormat, $row['bid_time']);
                $auction['last_bid'] = $row;
                $auction['bid_time'] = $row['bid_time'];
            }
        } else {
            $row['bid_time'] = $auction['end_time'];
        }


        /* 查询已确认订单数 */
        if ($auction['status_no'] > 1) {
            $auction['order_count'] = OrderInfo::where('extension_code', 'auction')
                ->where('extension_id', $act_id)
                ->whereIn('order_status', [OS_CONFIRMED, OS_UNCONFIRMED])
                ->count();
        } else {
            $auction['order_count'] = 0;
        }

        /* 当前价 */
        $auction['current_price'] = isset($auction['last_bid']) ? $auction['last_bid']['bid_price'] : $auction['start_price'];
        $auction['current_price_int'] = intval($auction['current_price']);
        $auction['formated_current_price'] = $this->dscRepository->getPriceFormat($auction['current_price'], false);

        return $auction;
    }

    /**
     * 查找我的收藏商品
     * @param $goodsId
     * @param $uid
     * @return array
     */
    public function findOne($goodsId, $uid)
    {
        $cg = CollectGoods::where('goods_id', $goodsId)
            ->where('user_id', $uid);

        return BaseRepository::getToArrayFirst($cg);
    }


    /**
     * 取得拍卖活动出价记录
     *
     * @param int $act_id
     * @param int $type
     * @return array
     */
    public function auction_log($act_id = 0, $type = 0)
    {
        if ($type == 1) {
            $log = AuctionLog::where('act_id', $act_id);

            $log = $log->whereHasIn('getUsers');

            $log = $log->count();
        } else {
            $res = AuctionLog::where('act_id', $act_id);

            $res = $res->whereHasIn('getUsers');

            $res = $res->orderBy('log_id', 'desc');

            $res = BaseRepository::getToArrayGet($res);

            $log = [];
            if ($res) {

                $userIdList = BaseRepository::getKeyPluck($res, 'bid_user');
                $userList = UserDataHandleService::userDataList($userIdList, ['user_id', 'user_name']);

                $timeFormat = config('shop.time_format');
                foreach ($res as $row) {

                    $user = $userList[$row['bid_user']] ?? [];

                    $row = BaseRepository::getArrayMerge($row, $user);

                    $row['user_name'] = isset($row['user_name']) ? setAnonymous($row['user_name']) : ''; //处理用户名 by wu
                    $row['bid_time'] = TimeRepository::getLocalDate($timeFormat, $row['bid_time']);
                    $row['formated_bid_price'] = $this->dscRepository->getPriceFormat($row['bid_price'], false);
                    $log[] = $row;
                }
            }
        }

        return $log;
    }

    /**
     * 推荐拍品
     *
     * @param string $type
     * @return array
     */
    public function recommend_goods($type = '')
    {
        $now = TimeRepository::getGmTime();

        $res = GoodsActivity::where('review_status', 3)
            ->where('act_type', GAT_AUCTION)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->where('is_finished', '<', 2);

        switch ($type) {
            case 'best':
                $res = $res->where('is_best', 1);
                break;
            case 'new':
                $res = $res->where('is_new', 1);
                break;
            case 'hot':
                $res = $res->where('is_hot', 1);
                break;
        }

        $res = $res->whereHasIn('getGoods', function ($query) {
            $query->where('is_on_sale', 1)
                ->where('is_alone_sale', 1)
                ->where('is_delete', 0);
        });

        $res = $res->with([
            'getGoods' => function ($query) {
                $query->select('goods_id', 'goods_thumb');
            }
        ]);

        $res = $res->limit(6);
        $res = BaseRepository::getToArrayGet($res);

        $info = [];
        if ($res) {
            $timeFormat = config('shop.time_format');
            foreach ($res as $row) {
                if ($row['get_goods']) {
                    $row = array_merge($row, $row['get_goods']);
                }
                $ext_info = unserialize($row['ext_info']);

                $auction = array_merge($row, $ext_info);

                $auction['status_no'] = ActivityRepository::getAuctionStatus($auction, $now);
                $auction['start_time'] = TimeRepository::getLocalDate($timeFormat, $auction['start_time']);
                $auction['end_time'] = TimeRepository::getLocalDate($timeFormat, $auction['end_time']);
                $auction['formated_start_price'] = $this->dscRepository->getPriceFormat($auction['start_price']);
                $auction['formated_end_price'] = $this->dscRepository->getPriceFormat($auction['end_price']);
                $auction['formated_deposit'] = $this->dscRepository->getPriceFormat($auction['deposit']);
                $auction['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                $auction['current_time'] = TimeRepository::getLocalDate($timeFormat, $now);
                $info[] = $auction;
            }
        }

        return $info;
    }


    /**
     * 取商品的规格列表
     *
     * @param int $goods_id
     * @return array
     */
    public function get_specifications_list($goods_id = 0)
    {
        /* 取商品属性 */
        $result = GoodsAttr::select('goods_attr_id', 'attr_id', 'attr_value', 'attr_name')
            ->where('ga.goods_id', $goods_id);

        $result = BaseRepository::getToArrayGet($result);

        $return_array = [];
        if ($result) {

            $attrIdList = BaseRepository::getKeyPluck($result, 'attr_id');
            $attributeList = GoodsDataHandleService::getAttributeDataList($attrIdList);

            foreach ($result as $value) {

                $attribute = $attributeList[$value['attr_id']] ?? [];
                $value['attr_name'] = $attribute['attr_name'] ?? '';

                $return_array[$value['goods_attr_id']] = $value;
            }
        }

        return $return_array;
    }


    /**
     * 获取商品属性组
     *
     * @param int $goods_id
     * @param int $product_id
     * @return array
     */
    public function getProducts($goods_id = 0, $product_id = 0)
    {
        $product = Products::select('goods_attr')
            ->where('goods_id', $goods_id)
            ->where('product_id', $product_id);
        $product = BaseRepository::getToArrayGet($product);

        return $product;
    }

    /**
     * 会员信息
     *
     * @param int $user_id
     * @param string $data
     * @return mixed
     */
    public function userInfo($user_id = 0, $data = '*')
    {
        $info = Users::select($data)->where('user_id', $user_id);
        $info = BaseRepository::getToArrayFirst($info);

        return $info;
    }

    /**
     * 插入出价记录
     *
     * @param array $auction_log
     * @return mixed
     */
    public function addAuctionLog($auction_log = [])
    {
        $id = AuctionLog::insertGetId($auction_log);

        return $id;
    }

    /**
     * 修改活动状态
     *
     * @param int $act_id
     */
    public function updateGoodsActivity($act_id = 0)
    {
        GoodsActivity::where('act_id', $act_id)
            ->update(['is_finished' => 1]);
    }


    /**
     * 添加到购物车
     *
     * @param $arguments
     * @return mixed
     */
    public function addGoodsToCart($arguments)
    {

        /* 插入一条新记录 */
        $rec_id = Cart::insertGetId($arguments);
        return $rec_id;
    }

    /**
     * 店铺信息
     *
     * @param int $ru_id
     * @return array
     */
    public function getSellerShopinfo($ru_id = 0)
    {
        $info = SellerShopinfo::select('province', 'city', 'kf_type', 'kf_ww', 'kf_qq', 'shop_name')
            ->where('ru_id', $ru_id);

        $info = BaseRepository::getToArrayFirst($info);

        return $info;
    }

    /**
     * 取得拍卖活动数量
     *
     * @param string $keywords
     * @param array $cats
     * @return mixed
     */
    public function getAuctionCount($keywords = '', $cats = [])
    {
        $now = TimeRepository::getGmTime();

        $res = GoodsActivity::where('review_status', 3)
            ->where('act_type', GAT_AUCTION)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->where('is_finished', '<', 2);

        $goodsWhere = [];
        if ($cats) {
            $cats = BaseRepository::getExplode($cats);

            /* 查询扩展分类数据 */
            $extension_goods = GoodsCat::select('goods_id')->whereIn('cat_id', $cats);
            $extension_goods = BaseRepository::getToArrayGet($extension_goods);
            $extension_goods = BaseRepository::getFlatten($extension_goods);

            $goodsWhere = [
                'cats' => $cats,
                'extension_goods' => $extension_goods
            ];
        }

        $res = $res->whereHasIn('getGoods', function ($query) use ($goodsWhere) {
            $query = $query->where('is_on_sale', 1)
                ->where('is_alone_sale', 1)
                ->where('is_delete', 0);

            if ($goodsWhere) {
                $query->where(function ($query) use ($goodsWhere) {
                    $query = $query->whereIn('cat_id', $goodsWhere['cats']);
                    $query->orWhereIn('goods_id', $goodsWhere['extension_goods']);
                });
            }
        });

        if ($keywords) {
            $res = $res->where(function ($query) use ($keywords) {
                $query->where('act_name', 'like', '%' . $keywords . '%');


                $query->orWhere(function ($query) use ($keywords) {
                    $query->whereHasIn('getGoods', function ($query) use ($keywords) {
                        $query->where('goods_name', 'like', '%' . $keywords . '%');
                    });
                });
            });
        }

        $res = $res->count();
        return $res;
    }

    /**
     * 取得某页的拍卖活动
     *
     * @param string $keywords
     * @param string $sort
     * @param string $order
     * @param int $size
     * @param int $page
     * @param array $cats
     * @return array|mixed
     * @throws \Exception
     */
    public function getAuctionList($keywords = '', $sort = 'act_id', $order = 'desc', $size = 20, $page = 1, $cats = [])
    {
        $goods_num = isset($_REQUEST['goods_num']) && !empty($_REQUEST['goods_num']) ? intval($_REQUEST['goods_num']) : 0;

        $auction_list = [];
        $auction_list['finished'] = $auction_list['finished'] = [];

        $now = TimeRepository::getGmTime();

        $res = GoodsActivity::where('review_status', 3)
            ->where('act_type', GAT_AUCTION)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->where('is_finished', 0);

        $goodsWhere = [];
        if ($cats) {
            $cats = BaseRepository::getExplode($cats);

            /* 查询扩展分类数据 */
            $extension_goods = GoodsCat::select('goods_id')->whereIn('cat_id', $cats);
            $extension_goods = BaseRepository::getToArrayGet($extension_goods);
            $extension_goods = BaseRepository::getFlatten($extension_goods);

            $goodsWhere = [
                'cats' => $cats,
                'extension_goods' => $extension_goods
            ];
        }

        $res = $res->whereHasIn('getGoods', function ($query) use ($goodsWhere) {
            $query = $query->where('is_on_sale', 1)
                ->where('is_alone_sale', 1)
                ->where('is_delete', 0);

            if ($goodsWhere) {
                $query->where(function ($query) use ($goodsWhere) {
                    $query = $query->whereIn('cat_id', $goodsWhere['cats']);
                    $query->orWhereIn('goods_id', $goodsWhere['extension_goods']);
                });
            }
        });

        if ($keywords) {
            $res = $res->where(function ($query) use ($keywords) {
                $query->where('act_name', 'like', '%' . $keywords . '%');


                $query->orWhere(function ($query) use ($keywords) {
                    $query->whereHasIn('getGoods', function ($query) use ($keywords) {
                        $query->where('goods_name', 'like', '%' . $keywords . '%');
                    });
                });
            });
        }

        //瀑布流加载分类商品 by wu
        if ($goods_num) {
            $start = $goods_num;
        } else {
            $start = ($page - 1) * $size;
        }

        $res = $res->orderBy($sort, $order);

        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($size > 0) {
            $res = $res->take($size);
        }

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $goodsIdList = BaseRepository::getKeyPluck($res, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goodsIdList, ['goods_id', 'goods_thumb']);

            $timeFormat = config('shop.time_format');
            foreach ($res as $row) {

                $goods = $goodsList[$row['goods_id']] ?? [];

                $row = BaseRepository::getArrayMerge($row, $goods);

                $ext_info = $row['ext_info'] ? unserialize($row['ext_info']) : [];
                $auction = BaseRepository::getArrayMerge($row, $ext_info);
                $auction['status_no'] = ActivityRepository::getAuctionStatus($auction, $now);

                $auction['start_time'] = TimeRepository::getLocalDate($timeFormat, $auction['start_time']);
                $auction['end_time'] = TimeRepository::getLocalDate($timeFormat, $auction['end_time']);
                $auction['start_price'] = isset($auction['start_price']) ? $auction['start_price'] : 0;
                $auction['formated_start_price'] = $this->dscRepository->getPriceFormat($auction['start_price']);
                $auction['formated_end_price'] = $this->dscRepository->getPriceFormat($auction['end_price']);
                $auction['formated_deposit'] = $this->dscRepository->getPriceFormat($auction['deposit']);
                $auction['goods_thumb'] = isset($row['goods_thumb']) ? $this->dscRepository->getImagePath($row['goods_thumb']) : '';
                $auction['url'] = $this->dscRepository->buildUri('auction', ['auid' => $auction['act_id']]);
                $auction['count'] = auction_log($auction['act_id'], 1);
                $auction['current_time'] = TimeRepository::getLocalDate($timeFormat);
                $auction['rz_shop_name'] = $this->merchantCommonService->getShopName($row['user_id'], 1); //店铺名称
                /* 查询已确认订单数 */
                if ($auction['status_no'] > 1) {
                    $auction['order_count'] = OrderInfo::where('extension_code', 'auction')
                        ->where('extension_id', $auction['act_id'])
                        ->whereIn('order_status', [OS_CONFIRMED, OS_UNCONFIRMED])
                        ->count();
                } else {
                    $auction['order_count'] = 0;
                }

                /* 查询出价用户数和最后出价  qin */
                $auction['bid_user_count'] = AuctionLog::where('act_id', $auction['act_id'])->count('act_id');

                if ($auction['bid_user_count'] > 0) {
                    $row = AuctionLog::where('act_id', $auction['act_id']);

                    $row = $row->whereHasIn('getUsers');

                    $row = $row->with([
                        'getUsers' => function ($query) {
                            $query->where('user_id', 'user_name');
                        }
                    ]);

                    $row = $row->orderBy('log_id', 'desc');
                    $row = BaseRepository::getToArrayFirst($row);

                    if (isset($row['get_users'])) {
                        $row = array_merge($row, $row['get_users']);
                    }
                    if ($row) {
                        $timeFormat = config('shop.time_format');
                        $row['formated_bid_price'] = $this->dscRepository->getPriceFormat($row['bid_price'], false);
                        $row['bid_time'] = TimeRepository::getLocalDate($timeFormat, $row['bid_time']);
                    }
                    $auction['last_bid'] = $row;
                }

                $auction['is_winner'] = 0;
                if (isset($auction['last_bid']['bid_user']) && $auction['last_bid']['bid_user']) {
                    if ($auction['status_no'] == FINISHED && $auction['last_bid']['bid_user'] == session('user_id') && $auction['order_count'] == 0) {
                        $auction['is_winner'] = 1;
                    }
                }

                $auction['s_user_id'] = session('user_id');

                if ($auction['status_no'] < 2) {
                    $auction_list['under_way'][] = $auction;
                } else {
                    $auction_list['finished'][] = $auction;
                }
            }

            if (isset($auction_list['under_way']) && $auction_list['under_way']) {
                $auction_list = array_merge($auction_list['under_way'], $auction_list['finished']);
            } else {
                $auction_list = $auction_list['finished'];
            }
        }

        return $auction_list;
    }

    /**
     * 取得拍卖活动所有商品分类的顶级分类
     *
     * @return array
     */
    public function getTopCat()
    {
        $now = TimeRepository::getGmTime();

        $cat_list = Goods::select('cat_id')->where('is_on_sale', 1)
            ->where('is_alone_sale', 1)
            ->where('is_delete', 0);

        $cat_list = $cat_list->whereHasIn('getGoodsActivity', function ($query) use ($now) {
            $query->where('review_status', 3)
                ->where('act_type', GAT_AUCTION)
                ->where('start_time', '<=', $now)
                ->where('end_time', '>=', $now)
                ->where('is_finished', '<', 2);
        });

        $cat_list = BaseRepository::getToArrayGet($cat_list);

        $cats = BaseRepository::getFlatten($cat_list);

        $parentsCatList = $this->categoryService->parentsCatList($cats);

        $parentsCatList = Category::whereIn('cat_id', $parentsCatList)
            ->where('parent_id', 0)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('cat_id', 'ASC');

        $cat_top_list = BaseRepository::getToArrayGet($parentsCatList);

        return $cat_top_list;
    }

    /**
     * 获得指定分类下的推荐商品
     *
     * @access  public
     * @param string $type 推荐类型，可以是 best, new, hot, promote
     * @param string $cats 分类的ID
     * @param integer $min 商品积分下限
     * @param integer $max 商品积分上限
     * @return  array
     */
    public function getAuctionRecommendGoods($type = '', $cats = '', $min = 0, $max = 0)
    {
        $now = TimeRepository::getGmTime();
        $order_type = $GLOBALS['_CFG']['recommend_order'];

        $type2lib = ['best' => 'auction_best', 'new' => 'auction_new', 'hot' => 'auction_hot'];
        $num = $this->templateService->getLibraryNumber($type2lib[$type], 'auction_list');

        $res = Goods::select(['goods_id', 'brand_id', 'goods_name', 'goods_name_style', 'goods_brief', 'goods_thumb', 'goods_img'])
            ->where('is_on_sale', 1)
            ->where('is_alone_sale', 1)
            ->where('is_delete', 0);

        $cats = BaseRepository::getExplode($cats);

        /* 查询扩展分类数据 */
        $extension_goods = GoodsCat::select('goods_id')->whereIn('cat_id', $cats);
        $extension_goods = BaseRepository::getToArrayGet($extension_goods);
        $extension_goods = BaseRepository::getFlatten($extension_goods);

        if ($cats) {
            $goodsWhere = [
                'cats' => $cats,
                'extension_goods' => $extension_goods
            ];

            $res = $res->where(function ($query) use ($goodsWhere) {
                $query = $query->whereIn('cat_id', $goodsWhere['cats']);
                $query->orWhereIn('goods_id', $goodsWhere['extension_goods']);
            });
        }

        if ($min > 0) {
            $res = $res->where('shop_price', '>=', $min);
        }

        if ($max > 0) {
            $res = $res->where('shop_price', '<=', $max);
        }

        $activity = [
            'type' => $type,
            'act_type' => GAT_AUCTION,
            'time' => $now
        ];

        $res = $res->whereHasIn('getGoodsActivity', function ($query) use ($activity) {
            switch ($activity['type']) {
                case 'best':
                    $query = $query->where('is_best', 1);
                    break;
                case 'new':
                    $query = $query->where('is_new', 1);
                    break;
                case 'hot':
                    $query = $query->where('is_hot', 1);
                    break;
            }

            $query->where('act_type', $activity['act_type'])
                ->where('review_status', 3)
                ->where('start_time', '<=', $activity['time'])
                ->where('end_time', '>=', $activity['time'])
                ->where('is_finished', '<', 2);
        });

        $res = $res->with([
            'getGoodsActivity' => function ($query) {
                $query->select('goods_id', 'act_name', 'act_id', 'ext_info', 'start_time', 'end_time');
            },
            'getBrand' => function ($query) {
                $query->select('brand_id', 'brand_name');
            }
        ]);

        if ($order_type == 0) {
            $res = $res->orderByRaw('sort_order, last_update DESC');
        } else {
            $res = $res->orderByRaw('RAND()');
        }

        $res = $res->take($num);

        $res = BaseRepository::getToArrayGet($res);

        $auction = [];
        if ($res) {

            $goodsIdList = BaseRepository::getKeyPluck($res, 'goods_id');
            $goodsActivityList = GoodsDataHandleService::getGoodsActivityDataList($goodsIdList, ['goods_id', 'act_name', 'act_id', 'ext_info', 'start_time', 'end_time']);

            $brandIdList = BaseRepository::getKeyPluck($res, 'brand_id');
            $brandList = BrandDataHandleService::goodsBrand($brandIdList, ['brand_id', 'brand_name']);

            $timeFormat = config('shop.time_format');
            foreach ($res as $key => $row) {

                $goodsActivity = $goodsActivityList[$row['goods_id']] ?? [];
                $row = BaseRepository::getArrayMerge($row, $goodsActivity);

                $brand = $brandList[$row['brand_id']] ?? [];
                $row = BaseRepository::getArrayMerge($row, $brand);

                $auction[$key]['id'] = $row['goods_id'];
                $auction[$key]['name'] = $row['goods_name'];
                $auction[$key]['brief'] = $row['goods_brief'];
                $auction[$key]['brand_name'] = !empty($row['brand_name']) ? $row['brand_name'] : '';
                $auction[$key]['short_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                    $this->dscRepository->subStr($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
                $auction[$key]['thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                $auction[$key]['goods_img'] = $this->dscRepository->getImagePath($row['goods_img']);
                $auction[$key]['url'] = $this->dscRepository->buildUri('auction', ['auid' => $row['act_id'], $row['act_name']]);

                $auction[$key]['format_start_time'] = TimeRepository::getLocalDate($timeFormat, $row['start_time']);
                $auction[$key]['format_end_time'] = TimeRepository::getLocalDate($timeFormat, $row['end_time']);

                $ext_info = $row['ext_info'] ? unserialize($row['ext_info']) : [];
                $auction_info = BaseRepository::getArrayMerge($row, $ext_info);

                $auction_info['start_price'] = $auction_info['start_price'] ?? 0;
                $auction_info['formated_start_price'] = $this->dscRepository->getPriceFormat($auction_info['start_price']);

                $auction[$key]['auction'] = $auction_info;
                $auction[$key]['status_no'] = ActivityRepository::getAuctionStatus($auction_info, $now);
                $auction[$key]['start_price'] = $this->dscRepository->getPriceFormat($auction_info['start_price']);
                $auction[$key]['count'] = auction_log($row['act_id'], 1);

                $auction[$key]['short_style_name'] = $this->goodsCommonService->addStyle($auction[$key]['short_name'], $row['goods_name_style']);
            }
        }

        return $auction;
    }

    /**
     * 获取会员竞拍的拍卖活动的数量
     *
     * @param int $user_id 出价会员ID
     * @param string $auction 活动类型
     * @return mixed
     */
    public function getAllAuction($user_id = 0, $auction = '')
    {
        $where = [
            'user_id' => $user_id,
            'auction' => $auction
        ];

        $auction_count = GoodsActivity::searchKeyword($auction)
            ->whereHasIn("getAuctionLog", function ($query) use ($where) {
                if ($where['auction']) {
                    $query = $query->searchKeyword($where['auction']);
                }

                $query->where('bid_user', $where['user_id']);
            });

        $auction_count = $auction_count->count();

        return $auction_count;
    }

    /**
     * 获取会员竞拍的拍卖活动列表
     *
     * @param int $user_id
     * @param int $record_count
     * @param int $page
     * @param array $list
     * @param int $size
     * @return array
     */
    public function getAuctionGoodsList($user_id = 0, $record_count = 0, $page = 1, $list = [], $size = 10)
    {
        if ($list) {
            $idTxt = $list->idTxt;
            $keyword = $list->keyword;
            $action = $list->action;
            $type = $list->type;
            $status_keyword = isset($list->status_keyword) ? $list->status_keyword : '';
            $date_keyword = isset($list->date_keyword) ? $list->date_keyword : '';

            $id = '"';
            $id .= $user_id . "=";
            $id .= "idTxt@" . $idTxt . "|";
            $id .= "keyword@" . $keyword . "|";
            $id .= "action@" . $action . "|";
            $id .= "type@" . $type . "|";

            if ($status_keyword) {
                $id .= "status_keyword@" . $status_keyword . "|";
            }

            if ($date_keyword) {
                $id .= "date_keyword@" . $date_keyword;
            }

            $substr = substr($id, -1);
            if ($substr == "|") {
                $id = substr($id, 0, -1);
            }

            $id .= '"';
        } else {
            $id = $user_id;
        }

        $config = ['header' => $GLOBALS['_LANG']['pager_2'], "prev" => "<i><<</i>" . $GLOBALS['_LANG']['page_prev'], "next" => "" . $GLOBALS['_LANG']['page_next'] . "<i>>></i>", "first" => $GLOBALS['_LANG']['page_first'], "last" => $GLOBALS['_LANG']['page_last']];

        $pagerParams = [
            'total' => $record_count,
            'listRows' => $size,
            'id' => $id,
            'page' => $page,
            'funName' => 'user_auction_gotoPage',
            'pageType' => 1,
            'config_zn' => $config
        ];
        $user_auction = new Pager($pagerParams);

        $pager = $user_auction->fpage([0, 4, 5, 6, 9]);

        $where = [
            'user_id' => $user_id,
            'auction' => $list
        ];

        /* 拍卖活动列表 */
        $res = GoodsActivity::searchKeyword($where['auction'])->whereHasIn("getAuctionLog", function ($query) use ($where) {
            if ($where['auction']) {
                $query = $query->searchKeyword($where['auction']);
            }

            $query->where('bid_user', $where['user_id']);
        });

        $res = $res->with([
            'getAuctionLog' => function ($query) {
                $query->select('act_id', 'bid_time', 'bid_price');
            },
            'getGoods' => function ($query) {
                $query->select('goods_id', 'goods_thumb');
            }
        ]);

        $res = $res->orderBy('act_id', 'desc');

        $start = ($page - 1) * $size;

        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($size > 0) {
            $res = $res->take($size);
        }

        $res = BaseRepository::getToArrayGet($res);

        $list = [];

        if ($res) {

            $goodsIdList = BaseRepository::getKeyPluck($res, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goodsIdList, 'goods_id', 'goods_thumb');

            $actIdList = BaseRepository::getKeyPluck($res, 'act_id');
            $actLogList = AuctionDataHandleService::AuctionLogDataList($actIdList, ['act_id', 'bid_time', 'bid_price']);

            $time = TimeRepository::getGmTime();
            $timeFormat = config('shop.time_format');
            foreach ($res as $row) {

                $actLog = $actLogList[$row['act_id']] ?? [];
                $goods = $goodsList[$row['goods_id']] ?? [];

                $row = BaseRepository::getArrayMerge($row, $actLog);
                $row = BaseRepository::getArrayMerge($row, $goods);
                $arr['status_no'] = ActivityRepository::getAuctionStatus($row, $time);
                $arr['act_id'] = $row['act_id'];
                $arr['act_name'] = $row['act_name'];
                $arr['goods_thumb'] = empty($row['goods_thumb']) ? '' : $this->dscRepository->getImagePath($row['goods_thumb']);
                $arr['goods_name'] = $row['goods_name'];
                $arr['start_time'] = $row['start_time'];
                $arr['end_time'] = $row['end_time'];
                $arr['bid_time'] = TimeRepository::getLocalDate($timeFormat, $row['bid_time']);
                $arr['bid_price'] = $this->dscRepository->getPriceFormat($row['bid_price']);
                $arr['status'] = lang('user.auction_staues.' . $arr['status_no']);
                $list[] = $arr;
            }
        }

        $auction_list = ['auction_list' => $list, 'pager' => $pager, 'record_count' => $record_count];
        return $auction_list;
    }

    /**
     * 获取会员竞拍的拍卖活动列表
     *
     * @param $user_id
     * @param $page
     * @param array $list
     * @param int $size
     * @return array
     */
    public function getAuctionBidGoodsList($user_id, $page, $list = [], $size = 10)
    {
        $where = [
            'user_id' => $user_id,
            'auction' => $list
        ];

        /* 拍卖活动列表 */
        $res = GoodsActivity::searchKeyword($where['auction'])->whereHasIn("getAuctionLog", function ($query) use ($where) {
            if ($where['auction']) {
                $query = $query->searchKeyword($where['auction']);
            }

            $query->where('bid_user', $where['user_id']);
        });

        $res = $res->orderBy('act_id', 'desc');

        $start = ($page - 1) * $size;

        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($size > 0) {
            $res = $res->take($size);
        }

        $res = BaseRepository::getToArrayGet($res);

        $list = [];

        if ($res) {

            $goodsIdList = BaseRepository::getKeyPluck($res, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goodsIdList, 'goods_id', 'goods_thumb');

            $actIdList = BaseRepository::getKeyPluck($res, 'act_id');
            $actLogList = AuctionDataHandleService::AuctionLogDataList($actIdList, ['act_id', 'bid_time', 'bid_price']);

            $time = TimeRepository::getGmTime();
            $timeFormat = config('shop.time_format');
            foreach ($res as $row) {

                $actLog = $actLogList[$row['act_id']] ?? [];
                $goods = $goodsList[$row['goods_id']] ?? [];

                $row = BaseRepository::getArrayMerge($row, $actLog);
                $row = BaseRepository::getArrayMerge($row, $goods);

                $arr['status_no'] = ActivityRepository::getAuctionStatus($row, $time);
                $arr['act_id'] = $row['act_id'];
                $arr['act_name'] = $row['act_name'];
                $arr['goods_thumb'] = $this->dscRepository->getImagePath($row['goods_thumb']);
                $arr['goods_name'] = $row['goods_name'];
                $arr['start_time'] = $row['start_time'];
                $arr['end_time'] = $row['end_time'];
                $arr['bid_time'] = TimeRepository::getLocalDate($timeFormat, $row['bid_time']);
                $arr['bid_price'] = $this->dscRepository->getPriceFormat($row['bid_price']);
                $arr['status'] = $GLOBALS['_LANG']['auction_staues'][$arr['status_no']];
                $list[] = $arr;
            }
        }

        return $list;
    }
}
