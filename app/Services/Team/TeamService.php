<?php

namespace App\Services\Team;

use App\Models\Cart;
use App\Models\CollectGoods;
use App\Models\Goods;
use App\Models\OrderAction;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\Products;
use App\Models\ProductsArea;
use App\Models\ProductsWarehouse;
use App\Models\TeamCategory;
use App\Models\TeamGoods;
use App\Models\TeamLog;
use App\Models\UserOrderNum;
use App\Models\Users;
use App\Models\WarehouseAreaGoods;
use App\Models\WarehouseGoods;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Brand\BrandDataHandleService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Goods\GoodsProdutsService;
use App\Services\Order\OrderRefoundService;
use App\Services\User\AccountService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * 拼团
 * Class CrowdFund
 * @package App\Services
 */
class TeamService
{
    protected $dscRepository;
    protected $sessionRepository;
    protected $goodsProdutsService;
    protected $accountService;

    public function __construct(
        DscRepository $dscRepository,
        SessionRepository $sessionRepository,
        GoodsProdutsService $goodsProdutsService,
        AccountService $accountService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->sessionRepository = $sessionRepository;
        $this->goodsProdutsService = $goodsProdutsService;
        $this->accountService = $accountService;
    }

    /**
     * 拼团主频道，获取子频道列表
     *
     * @param int $tc_id 拼团频道ID
     * @return array
     */
    public function teamCategories($tc_id = 0)
    {
        $team = TeamCategory::select('*');
        if ($tc_id > 0) {
            $team->where('parent_id', $tc_id);
        } else {
            $team->where('parent_id', 0);
        }
        $team = $team->where('status', 1)
            ->orderby('id', 'asc');

        $team_list = BaseRepository::getToArrayGet($team);

        $list = [];
        if ($team_list) {
            foreach ($team_list as $key => $val) {
                $list[$key]['tc_id'] = $val['id'];
                $list[$key]['name'] = $val['name'];
                $list[$key]['tc_img'] = $this->dscRepository->getImagePath($val['tc_img']);
            }
        }

        return $list;
    }

    /**
     * 获取品牌分类下品牌
     * @param int $tc_id
     */
    public function teamCategoryBrands($tc_id = 0)
    {
        return Cache::remember('team_category_brands_' . $tc_id, now()->addWeekdays(), function () use ($tc_id) {
            $categories = $this->teamCategories($tc_id);

            $ids = [];
            foreach ($categories as $category) {
                array_push($ids, $category['tc_id']);
                $childCategories = $this->teamCategories($category['tc_id']);
                foreach ($childCategories as $child) {
                    array_push($ids, $child['tc_id']);
                }
            }

            $goods = TeamGoods::select('goods_id')->whereIn('tc_id', $ids)->where('is_team', 1)->limit(500);
            $res = BaseRepository::getToArrayGet($goods);

            $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'brand_id'], ['is_on_sale' => 1, 'is_show' => 1]);
            $brand_id = BaseRepository::getKeyPluck($goodsList, 'brand_id');
            $brands = BrandDataHandleService::goodsBrand($brand_id, ['brand_id', 'brand_name', 'brand_logo']);

            foreach ($brands as $k => $v) {
                $brands[$k]['brand_logo'] = $v['brand_logo'] ? $this->dscRepository->getImagePath('data/brandlogo/' . $v['brand_logo']) : '';
            }

            return array_values($brands);
        });
    }

    /**
     * 拼团频道信息
     *
     * @param int $tc_id 拼团频道ID
     * @return array
     */
    public function teamCategoriesInfo($tc_id = 0)
    {
        return TeamCategory::where('id', $tc_id)->where('status', 1)->value('name');
    }

    /**
     * 获取随机用户信息
     *
     * @param int $user_id
     * @return array
     * @throws \Exception
     */
    public function virtualOrder($user_id = 0)
    {
        $list = [];

        if (config('shop.virtual_order') == 1) {
            $info = Users::where('user_id', '<>', $user_id)
                ->orderBy(DB::raw('RAND()'))
                ->take(20);

            $info = BaseRepository::getToArrayGet($info);

            if ($info) {
                foreach ($info as $key => $value) {
                    $list[$key]['user_id'] = $value['user_id'];
                    $user_name = !empty($value['nick_name']) ? $value['nick_name'] : $value['user_name'];
                    $list[$key]['user_name'] = setAnonymous($user_name);
                    $list[$key]['user_picture'] = $this->dscRepository->getImagePath($value['user_picture']);
                    //随机秒数
                    $list[$key]['seconds'] = rand(1, 8) . lang('team.seconds');
                }
            }
        }

        return $list;
    }


    /**
     * 拼团首页商品列表,频道商品列表
     *
     * @param int $tc_id
     * @param int $page
     * @param int $size
     * @return array
     */
    public function getGoods($tc_id = 0, $page = 1, $size = 10)
    {
        $begin = ($page - 1) * $size;
        $type = [];
        if ($tc_id > 0) {
            $team_categories_child = $this->teamCategories($tc_id);  //获取拼团主频道

            if (!empty($team_categories_child)) {
                $one_id = [];
                foreach ($team_categories_child as $key) {
                    $one_id[] = $key['tc_id'];
                }
                $type = $one_id;
            }
            $type[] = $tc_id;
        }
        $goods = TeamGoods::where('is_team', 1)
            ->where('is_audit', 2)
            ->whereHasIn('getGoods', function ($query) {
                $query = $query->where('is_alone_sale', 1)
                    ->where('is_on_sale', 1)
                    ->where('is_delete', 0);

                if (config('shop.review_goods') == 1) {
                    $query->whereIn('review_status', [3, 4, 5]);
                }
            });

        $goods = $goods->with([
            'getGoods' => function ($query) {
                $query->select('goods_id', 'goods_name', 'user_id as ru_id', 'shop_price', 'goods_number', 'goods_thumb', 'sales_volume');
            }
        ]);

        if (!empty($type)) {
            $goods = $goods->whereIn('tc_id', $type);
        }
        $goods = $goods->orderby('id', 'desc')
            ->offset($begin)
            ->limit($size);

        $team_list = BaseRepository::getToArrayGet($goods);
        $list = [];
        if ($team_list) {
            foreach ($team_list as $key => $val) {
                $val = $val['get_goods'] ? array_merge($val, $val['get_goods']) : $val;
                $list[$key]['id'] = $val['id'];
                $list[$key]['goods_id'] = $val['goods_id'];
                $list[$key]['goods_name'] = $val['goods_name'];
                $list[$key]['shop_price'] = $val['shop_price'];

                $goodsSelf = false;
                if ($val['ru_id'] == 0) {
                    $goodsSelf = true;
                }

                $list[$key]['shop_price'] = $this->dscRepository->getPriceFormat($list[$key]['shop_price'], true, false, $goodsSelf);
                $list[$key]['shop_price_formated'] = $this->dscRepository->getPriceFormat($val['shop_price'], true, true, $goodsSelf);
                $list[$key]['goods_thumb'] = $this->dscRepository->getImagePath($val['goods_thumb']);
                $list[$key]['team_price'] = $val['team_price'];
                $list[$key]['team_price'] = $this->dscRepository->getPriceFormat($list[$key]['team_price'], true, false, $goodsSelf);
                $list[$key]['team_price_formated'] = $this->dscRepository->getPriceFormat($val['team_price'], true, true, $goodsSelf);
                $list[$key]['team_num'] = $val['team_num'];
                $list[$key]['limit_num'] = $val['limit_num'];
            }
        }

        return $list;
    }

    /**
     * 拼团首页商品列表,频道商品列表
     *
     * @param int $tc_id 拼团频道ID
     * @param int $brand_id 品牌ID
     * @param string $keywords 关键字
     * @param int $sortKey 排序 goods_id last_update  sales_volume  team_price
     * @param string $sortVal ASC DESC
     * @param int $page
     * @param int $size
     * @return array
     */
    public function getGoodsList($tc_id = 0, $brand_id = 0, $keywords = '', $sortKey = 0, $sortVal = '', $page = 1, $size = 10)
    {
        $goods = TeamGoods::from('team_goods as tg')
            ->select('g.goods_id', 'g.goods_name', 'g.shop_price', 'g.goods_number', 'g.sales_volume', 'g.goods_thumb', 'tg.id', 'tg.team_price', 'tg.team_num', 'tg.limit_num')
            ->leftjoin('goods as g', 'g.goods_id', '=', 'tg.goods_id');

        if ($tc_id > 0) {
            $ids = TeamCategory::where('parent_id', $tc_id)->pluck('id');
            $ids = collect($ids)->toArray();
            array_push($ids, intval($tc_id));
            $goods->whereIn('tg.tc_id', $ids);
        }
        if ($brand_id > 0) {
            $goods->where('g.brand_id', intval($brand_id));
        }
        // 关键词
        if (!empty($keywords)) {
            $goods->where('goods_name', 'like', "%{$keywords}%");
        }

        // 排序
        $sort = ['ASC', 'DESC'];

        switch ($sortKey) {
            // 默认
            case '0':
                $goods->orderby('g.goods_id', in_array($sortVal, $sort) ? $sortVal : 'ASC');
                break;
            // 新品
            case '1':
                $goods->orderby('g.last_update', in_array($sortVal, $sort) ? $sortVal : 'ASC');
                break;
            // 销量
            case '2':
                $goods->orderby('g.sales_volume', in_array($sortVal, $sort) ? $sortVal : 'ASC');
                break;
            // 价格
            case '3':
                $goods->orderby('tg.team_price', in_array($sortVal, $sort) ? $sortVal : 'ASC');
                break;
        }
        $begin = ($page - 1) * $size;
        $team_list = $goods->where('tg.is_team', 1)
            ->where('tg.is_audit', 2)
            ->where('g.is_on_sale', 1)
            ->where('g.is_alone_sale', 1)
            ->where('g.is_delete', 0);

        if (config('shop.review_goods') == 1) {
            $team_list = $team_list->where('g.review_status', '>', 2);
        }

        $team_list = $team_list->offset($begin)
            ->limit($size);

        $team_list = BaseRepository::getToArrayGet($team_list);

        $list = [];
        if ($team_list) {
            foreach ($team_list as $key => $val) {
                $list[$key]['id'] = $val['id'];
                $list[$key]['goods_id'] = $val['goods_id'];
                $list[$key]['goods_name'] = $val['goods_name'];
                $list[$key]['shop_price'] = $this->dscRepository->getPriceFormat($val['shop_price']);
                $list[$key]['goods_thumb'] = $this->dscRepository->getImagePath($val['goods_thumb']);
                $list[$key]['team_price'] = $this->dscRepository->getPriceFormat($val['team_price']);
                $list[$key]['team_num'] = $val['team_num'];
                $list[$key]['limit_num'] = $val['limit_num'];
            }
        }

        return $list;
    }


    /**
     * 拼团排行商品列表
     *
     * @param int $status
     * @param int $page
     * @param int $size
     * @return array
     */
    public function teamRankingList($status = 0, $page = 1, $size = 10)
    {
        $goods = TeamGoods::from('team_goods as tg')
            ->select('g.goods_id', 'g.goods_name', 'g.shop_price', 'g.goods_number', 'g.sales_volume', 'g.goods_thumb', 'tg.id', 'tg.team_price', 'tg.team_num', 'tg.limit_num', 'tg.virtual_num', DB::raw('limit_num+virtual_num as number'))
            ->leftjoin('goods as g', 'g.goods_id', '=', 'tg.goods_id');

        switch ($status) {
            // 热门
            case '0':
                if (config('shop.virtual_limit_nim') == 1) {
                    $goods->orderBy('number', 'DESC');
                } else {
                    $goods->orderby('tg.limit_num', 'DESC');
                };
                break;
            // 新品
            case '1':
                $goods->orderBy('g.add_time', 'DESC');
                break;
            // 优选
            case '2':
                $goods->where('g.is_hot', 1);
                break;
            case '3':
                $goods->where('g.is_best', 1);
                break;
        }
        $begin = ($page - 1) * $size;
        $team_list = $goods->where('tg.is_team', 1)
            ->where('tg.is_audit', 2)
            ->where('g.is_on_sale', 1)
            ->where('g.is_alone_sale', 1)
            ->where('g.is_delete', 0)
            ->where('g.review_status', '>', 2)
            ->offset($begin)
            ->limit($size)
            ->get();

        $team_list = $team_list ? $team_list->toArray() : [];

        $list = [];
        if ($team_list) {
            foreach ($team_list as $key => $val) {
                $list[$key]['key'] = $key + 1;
                $list[$key]['id'] = $val['id'];
                $list[$key]['goods_id'] = $val['goods_id'];
                $list[$key]['goods_name'] = $val['goods_name'];
                $list[$key]['shop_price'] = $this->dscRepository->getPriceFormat($val['shop_price']);
                $list[$key]['goods_thumb'] = $this->dscRepository->getImagePath($val['goods_thumb']);
                $list[$key]['team_price'] = $this->dscRepository->getPriceFormat($val['team_price']);
                $list[$key]['team_num'] = $val['team_num'];
                $list[$key]['limit_num'] = $val['limit_num'];
                if (config('shop.virtual_limit_nim') == 1) {
                    $list[$key]['limit_num'] = $val['number'];
                }
                $list[$key]['status'] = $status;
            }
        }

        return $list;
    }

    /**
     * 商品信息
     *
     * @param int $goods_id
     * @param int $user_id
     * @return array
     * @throws \Exception
     */
    public function goodsDetail($goods_id = 0, $user_id = 0)
    {
        $res = TeamGoods::where('goods_id', $goods_id)
            ->where('is_team', 1)
            ->whereHasIn('getGoods');

        $res = $res->with([
            'getGoods' => function ($query) {
                $query = $query->select('goods_id', 'brand_id', 'user_id', 'goods_sn', 'goods_name', 'is_real', 'is_shipping', 'is_on_sale', 'shop_price', 'market_price', 'goods_thumb', 'goods_img', 'goods_number', 'sales_volume', 'goods_desc', 'desc_mobile', 'goods_type', 'goods_brief', 'model_attr', 'goods_weight', 'review_status', 'freight', 'tid', 'shipping_fee');
                $query->with(['getBrand']);
            }
        ]);

        $res = BaseRepository::getToArrayFirst($res);

        if (empty($res)) {
            return [];
        }

        $res = array_merge($res, $res['get_goods']);

        unset($res['get_goods']);

        // 商品详情图 PC
        if (empty($res['desc_mobile']) && !empty($res['goods_desc'])) {
            $desc_preg = $this->dscRepository->descImagesPreg($res['goods_desc']);
            $res['goods_desc'] = $desc_preg['goods_desc'];
        }

        if (!empty($res['desc_mobile'])) {
            // 处理手机端商品详情 图片（手机相册图） data/gallery_album/
            $desc_preg = $this->dscRepository->descImagesPreg($res['desc_mobile'], 'desc_mobile', 1);
            $res['desc_mobile'] = $desc_preg['desc_mobile'];
            $res['goods_desc'] = $desc_preg['desc_mobile'];
        }

        if ($res['brand_id']) {
            $brand = $res['get_brand'] ?? [];
            $res['brand_name'] = $brand['brand_name'] ?? '';
        }

        $res['goods_thumb'] = $this->dscRepository->getImagePath($res['goods_thumb']);
        $res['goods_img'] = $this->dscRepository->getImagePath($res['goods_img']);
        if ($user_id) {
            $res['cart_number'] = Cart::where('user_id', $user_id)->where('rec_type', 0)
                ->sum('goods_number');
        } else {
            $session_id = $this->sessionRepository->realCartMacIp();
            $res['cart_number'] = Cart::where('session_id', $session_id)->where('rec_type', 0)
                ->sum('goods_number');
        }

        return $res;
    }


    /**
     * 查找我的收藏商品
     *
     * @param $goodsId
     * @param $uid
     * @return array
     */
    public function findOne($goodsId, $uid)
    {
        $cg = CollectGoods::where('goods_id', $goodsId)
            ->where('user_id', $uid);

        $cg = BaseRepository::getToArrayFirst($cg);

        return $cg;
    }

    /**
     * 验证参团活动信息
     *
     * @param int $team_id
     * @return array
     */
    public function teamIsFailure($team_id = 0)
    {
        if (empty($team_id)) {
            return [];
        }

        $team = TeamLog::where('team_id', $team_id);
        $team = $team->with([
            'getTeamGoods' => function ($query) {
                $query->select(
                    'id',
                    'goods_id',
                    'validity_time',
                    'team_price',
                    'team_num',
                    'limit_num',
                    'astrict_num',
                    'is_team'
                );
                $query->with([
                    'getGoods' => function ($query) {
                        $query->select('goods_id', 'goods_name');
                    }
                ]);
            }
        ]);

        $team = BaseRepository::getToArrayFirst($team);

        if ($team) {
            $team = $team['get_team_goods'] ? array_merge($team, $team['get_team_goods']) : $team;
            if (isset($team['get_team_goods'])) {
                unset($team['get_team_goods']);
            }
            $team = $team['get_goods'] ? array_merge($team, $team['get_goods']) : $team;
            if (isset($team['get_goods'])) {
                unset($team['get_goods']);
            }
        }

        return $team;
    }


    /**
     * 获取该商品已成功开团信息
     *
     * @access  public
     * @param integer $goods_id
     * @return mixed
     */
    public function teamGoodsLog($goods_id = 0)
    {
        if (empty($goods_id)) {
            return [];
        }

        $time = TimeRepository::getGmTime();

        $team = TeamLog::where('goods_id', $goods_id)
            ->where('status', 0)
            ->where('is_show', 1);

        $team = $team->whereHasIn('getOrderInfo', function ($query) {
            $query->where('extension_code', 'team_buy')->where('team_parent_id', '>', 0)->where('pay_status', PS_PAYED);
        });
        $team = $team->whereHasIn('getTeamGoods', function ($query) use ($time) {
            $query->whereRaw("$time < (start_time + validity_time * 3600)")
                ->where('is_show', 1)
                ->where('status', 0)
                ->where('is_team', 1);
        });

        $team = $team->with([
            'getOrderInfo' => function ($query) {
                $query->select('team_id', 'order_id', 'user_id');
                $query->with([
                    'getUsers' => function ($query) {
                        $query->select('user_id', 'user_name', 'nick_name', 'user_picture');
                    }
                ]);
            }
        ]);
        $team = $team->with([
            'getTeamGoods' => function ($query) {
                $query->select('id', 'goods_id', 'validity_time', 'team_price', 'team_num', 'limit_num', 'astrict_num', 'is_team');
            }
        ]);

        $team = $team->orderby('start_time', 'desc');

        return BaseRepository::getToArrayGet($team);
    }

    /**
     * 统计拼团中订单数量
     * @param int $user_id
     * @return int
     */
    public static function teamOrderNum($user_id = 0)
    {
        if (empty($user_id)) {
            return 0;
        }

        $time = TimeRepository::getGmTime();
        $res = OrderInfo::where('user_id', $user_id)
            ->where('extension_code', 'team_buy')
            ->where('order_status', '<>', 2);

        $res = $res->whereHasIn('getTeamLog', function ($query) use ($time) {
            $query->whereHasIn(
                'getTeamGoods',
                function ($query) use ($time) {
                    $query->whereRaw("$time < (start_time + validity_time * 3600)")->where('is_team', 1)
                        ->where('status', 0);
                }
            );
        });

        return $res->count();
    }


    /**
     * 统计该拼团已参与人数
     * @access  public
     * @param integer $team_id 拼团开团id
     * @return mixed
     */
    public function surplusNum($team_id = 0)
    {
        $num = OrderInfo::where('team_id', $team_id)
            ->where('extension_code', 'team_buy');
        $num = $num->where(function ($query) {
            $query->where('pay_status', PS_PAYED);
            $query->orWhere('order_status', 4);
        });
        return $num->count();
    }

    /**
     * 验证当前团是已否参与
     * @access  public
     * @param integer $team_id 拼团开团id
     * @return mixed
     */
    public function isTeamOrderNum($user_id = 0, $team_id = 0)
    {
        return OrderInfo::where('user_id', $user_id)
            ->where('team_id', $team_id)
            ->where('pay_status', PS_PAYED)
            ->where('extension_code', 'team_buy')
            ->count();
    }


    /**
     * 验证是否已经参团
     * @access  public
     * @param integer $user_id 会员id
     * @param integer $team_id 拼团开团id
     * @return mixed
     */
    public function teamJoin($user_id = 0, $team_id = 0)
    {
        if (empty($user_id) || empty($team_id)) {
            return 0;
        }

        return OrderInfo::where('team_id', $team_id)
            ->where('user_id', $user_id)
            ->where('extension_code', 'team_buy')
            ->count();
    }

    /**
     * 获取拼团新品
     * @param string $type
     * @param integer $size
     * @return mixed
     */
    public function teamNewGoods($type = 'is_new', $user_id = 0, $size = 10)
    {
        $where = [
            'user_id' => $user_id,
            'type' => $type
        ];

        $goods = TeamGoods::where('is_team', 1)
            ->where('is_audit', 2);

        $goods = $goods->whereHasIn('getGoods', function ($query) use ($where) {
            if ($where['type'] == 'is_new') {
                $query->where('is_new', 1);
            }

            $query = $query->where('is_alone_sale', 1)
                ->where('is_on_sale', 1)
                ->where('is_delete', 0)
                ->where('user_id', $where);

            if (config('shop.review_goods') == 1) {
                $query->whereIn('review_status', [3, 4, 5]);
            }
        });

        $goods = $goods->with([
            'getGoods' => function ($query) {
                $query->select('goods_id', 'goods_name', 'shop_price', 'goods_number', 'sales_volume', 'goods_thumb');
            }
        ]);

        $goods = $goods->orderby('id', 'desc')
            ->limit($size);

        $team_list = BaseRepository::getToArrayGet($goods);
        $list = [];
        if ($team_list) {
            foreach ($team_list as $key => $val) {
                $val = $val['get_goods'] ? array_merge($val, $val['get_goods']) : $val;
                $list[$key]['goods_id'] = $val['goods_id'];
                $list[$key]['goods_thumb'] = $this->dscRepository->getImagePath($val['goods_thumb']);
                $list[$key]['shop_price'] = $this->dscRepository->getPriceFormat($val['shop_price'], true);
                $list[$key]['team_price'] = $this->dscRepository->getPriceFormat($val['team_price'], true);
                $list[$key]['goods_name'] = $val['goods_name'];
            }
        }

        return $list;
    }

    /**
     * 取得商品最终使用价格
     *
     * @param $goods_id 商品编号
     * @param string $goods_num 购买数量
     * @param bool $is_spec_price 是否加入规格价格
     * @param array $property 规格ID的数组或者逗号分隔的字符串
     * @return int|mixed
     */
    public function getFinalPrice($goods_id, $goods_num = '1', $is_spec_price = false, $property = [])
    {
        $final_price = 0; //商品最终购买价格
        $spec_price = 0;

        //如果需要加入规格价格
        if ($is_spec_price) {
            if (!empty($property)) {
                $spec_price = $this->goodsProdutsService->goodsPropertyPrice($goods_id, $property);
            }
        }

        //商品信息
        $goods = $this->goodsDetail($goods_id);

        //如果需要加入规格价格
        if ($is_spec_price) {
            if (config('shop.add_shop_price') == 1) {
                $final_price = $goods['team_price'];
                $final_price += $spec_price;
            }
        }

        if (config('shop.add_shop_price') == 0) {
            //返回商品属性价
            $final_price = $goods['team_price'];
        }

        //返回商品最终购买价格
        return $final_price;
    }


    /**
     * 添加到购物车
     * @param $arguments
     * @return mixed
     */
    public function addGoodsToCart($arguments)
    {
        /* 插入一条新记录 */
        $cart_id = Cart::insertGetId($arguments);
        return $cart_id;
    }


    /**
     * 获取拼团信息
     * @param int $team_id
     * @return array
     */
    public function teamInfo($team_id = 0)
    {
        $res = TeamLog::where('team_id', $team_id);

        $res = $res->whereHasIn(
            'getOrderInfo',
            function ($query) {
                $query->where('extension_code', 'team_buy')
                    ->where('team_parent_id', '>', 0);
            }
        );

        $res = $res->with([
            'getOrderInfo' => function ($query) {
                $query = $query->select('order_id', 'user_id', 'team_parent_id', 'team_id');
                $query->with([
                    'getUsers' => function ($query) {
                        $query->select('user_id', 'user_name', 'nick_name', 'user_picture');
                    }
                ]);
            },
            'getTeamGoods' => function ($query) {
                $query->select('id', 'validity_time', 'team_num', 'team_price', 'is_team', 'team_desc');
            },
            'getGoods' => function ($query) {
                $query->select('goods_id', 'goods_thumb', 'goods_img', 'goods_name');
            }
        ]);

        $res = $res->first();

        $team_info = $res ? $res->toArray() : [];

        if ($team_info) {
            $time = TimeRepository::getGmTime();
            $team_info = array_merge($team_info, $team_info['get_order_info']);
            $team_info = array_merge($team_info, $team_info['get_users']);
            $team_info = array_merge($team_info, $team_info['get_team_goods']);
            $team_info = array_merge($team_info, $team_info['get_goods']);

            // 用户名、头像
            $team_info['user_name'] = !empty($team_info['nick_name']) ? setAnonymous($team_info['nick_name']) : setAnonymous($team_info['user_name']);

            if (empty($team_info['user_picture'])) {
                $team_info['user_picture'] = asset('img/user_default.png');
            }

            $team_info['user_picture'] = $this->dscRepository->getImagePath($team_info['user_picture']);

            $team_info['goods_thumb'] = $this->dscRepository->getImagePath($team_info['goods_thumb']);
            $team_info['team_price'] = $this->dscRepository->getPriceFormat($team_info['team_price']);
            // 当前时间
            $team_info['current_time'] = $time;
            $end_time = $team_info['start_time'] + ($team_info['validity_time'] * 3600);//剩余时间
            $team_info['end_time'] = $end_time; // + (8 * 3600);
            $team_num = $this->surplusNum($team_info['team_id']);  //统计几人参团
            $team_info['surplus'] = $team_info['team_num'] - $team_num;//还差几人
            $team_info['bar'] = round($team_num * 100 / $team_info['team_num'], 0);//百分比

            if ($team_info['status'] != 1 && $time < $end_time && $team_info['is_team'] == 1) {//进行中
                $team_info['status'] = 0;
            } elseif (($team_info['status'] != 1 && $time > $end_time) || $team_info['is_team'] != 1) {//失败
                $team_info['status'] = 2;
            } elseif ($team_info['status'] = 1) {//成功
                $team_info['status'] = 1;
            }

            unset($team_info['get_goods']);
            unset($team_info['get_order_info']);
            unset($team_info['get_team_goods']);
            unset($team_info['get_users']);
        }

        return $team_info;
    }

    /**
     * 获取拼团团员信息
     * @param int $team_id
     * @return array
     */
    public function teamUserList($team_id = 0)
    {
        $list = OrderInfo::select('add_time', 'team_id', 'user_id', 'team_parent_id', 'team_user_id')
            ->where('team_id', $team_id)
            ->where('extension_code', 'team_buy');

        $list = $list->where(function ($query) {
            $query->where('pay_status', PS_PAYED)
                ->orWhere('order_status', '>', 4);
        });

        $list = $list->with([
            'getUsers' => function ($query) {
                $query->select('user_id', 'user_name', 'nick_name', 'user_picture');
            }
        ]);

        $list = $list->orderby('add_time', 'asc');

        $list = BaseRepository::getToArrayGet($list);

        return $list;
    }

    /**
     * 我的拼团
     * @param $user_id
     * @param int $status 0
     * @param int $page
     * @param int $size
     * @return array
     */
    public function teamUserOrder($user_id, $status = 0, $page = 1, $size = 10)
    {
        $begin = ($page - 1) * $size;
        $where = [
            'time' => TimeRepository::getGmTime(),
            'status' => $status,
        ];
        $goods = OrderInfo::where('user_id', $user_id)
            ->where('extension_code', 'team_buy');
        if ($status == 0) {
            $goods = $goods->where('order_status', '<>', 2);
        }

        $goods = $goods->with([
            'getTeamLog' => function ($query) {
                $query->select('team_id', 'goods_id', 't_id', 'start_time', 'status');
                $query->with([
                    'getTeamGoods' => function ($query) {
                        $query->select('id', 'validity_time', 'team_num', 'team_price', 'limit_num');
                    },
                    'getGoods' => function ($query) {
                        $query->select('goods_id', 'goods_name', 'goods_thumb', 'shop_price');
                    }
                ]);
            }
        ]);

        $goods = $goods->whereHasIn('getTeamLog', function ($query) use ($where) {
            $query->whereHasIn(
                'getTeamGoods',
                function ($query) use ($where) {
                    $time = $where['time'];
                    switch ($where['status']) {
                        case '0'://拼团中
                            $query->whereRaw("$time < (start_time + validity_time * 3600)")
                                ->where('is_show', 1)
                                ->where('status', 0)
                                ->where('is_team', 1);
                            break;
                        case '1'://成功团
                            $query->where('status', 1)->where('is_show', 1);
                            break;
                        case '2'://失败团
                            $query = $query->where('status', 0)->where('is_show', 1);
                            $query->where(function ($query) use ($time) {
                                $query->whereRaw("$time > (start_time + validity_time * 3600)")->orWhere('is_team', '<>', 1);
                            });
                            break;
                    }
                }
            );
        });

        $goods = $goods->orderby('add_time', 'desc')
            ->offset($begin)
            ->limit($size);

        $team_list = BaseRepository::getToArrayGet($goods);

        $list = [];
        if ($team_list) {
            foreach ($team_list as $key => $val) {
                $val = $val['get_team_log'] ? array_merge($val, $val['get_team_log']) : $val;
                $val = $val['get_team_goods'] ? array_merge($val, $val['get_team_goods']) : $val;
                $val = $val['get_goods'] ? array_merge($val, $val['get_goods']) : $val;

                $list[$key]['id'] = $val['id'];
                $list[$key]['team_id'] = $val['team_id'];
                $list[$key]['goods_id'] = $val['goods_id'];
                $list[$key]['order_id'] = $val['order_id'];
                $list[$key]['order_sn'] = $val['order_sn'];
                $list[$key]['order_status'] = $val['order_status'];
                $list[$key]['pay_status'] = $val['pay_status'];
                $list[$key]['user_id'] = $val['user_id'];
                $list[$key]['goods_name'] = $val['goods_name'];
                $list[$key]['shop_price'] = $this->dscRepository->getPriceFormat($val['shop_price']);
                $list[$key]['goods_thumb'] = $this->dscRepository->getImagePath($val['goods_thumb']);
                $list[$key]['team_price'] = $this->dscRepository->getPriceFormat($val['team_price']);
                $list[$key]['team_num'] = $val['team_num'];
                $team_num = $this->surplusNum($val['team_id']);  //统计几人参团
                $list[$key]['limit_num'] = $team_num;
                $list[$key]['status'] = $status;  // 活动状态
                $list[$key]['is_pay'] = 0;
                if ($val['pay_status'] == 2) {
                    $list[$key]['is_pay'] = 1;
                }
            }
        }

        return $list;
    }

    /**
     * 获取已付款 拼团失败过期未退款的订单
     *
     * @param int $team_id
     * @return array
     */
    public static function teamUserOrderRefund($team_id = 0)
    {
        $order_list = OrderInfo::query()->where('main_count', 0)
            ->where('extension_code', 'team_buy')
            ->where('pay_status', PS_PAYED)
            ->where('pay_status', '<>', PS_REFOUND);

        if ($team_id > 0) {
            $order_list = $order_list->where('team_id', $team_id);
        }

        // 已过期、失败拼团订单
        $time = TimeRepository::getGmTime();

        $order_list = $order_list->whereHasIn('getTeamLog', function ($query) use ($time) {
            $query->whereHasIn('getTeamGoods', function ($query) use ($time) {
                $query = $query->where('status', 0)->where('is_show', 1);
                $query->where(function ($query) use ($time) {
                    $query->whereRaw("$time > (start_time + validity_time * 3600)")->orWhere('is_team', '<>', 1);
                });
            });
        });

        $order_list = $order_list->with([
            'getTeamLog' => function ($query) {
                $query = $query->select('team_id', 'goods_id', 't_id', 'start_time', 'status');
                $query->with([
                    'getTeamGoods' => function ($query) {
                        $query->select('id', 'validity_time', 'team_num', 'team_price', 'limit_num');
                    },
                    'getGoods' => function ($query) {
                        $query->select('goods_id', 'goods_name');
                    }
                ]);
            }
        ]);

        $order_list = $order_list->orderby('add_time', 'desc');

        $team_list = BaseRepository::getToArrayGet($order_list);
        $list = [];
        if ($team_list) {
            foreach ($team_list as $key => $val) {
                $val = $val['get_team_log'] ? array_merge($val, $val['get_team_log']) : $val;
                $val = $val['get_team_goods'] ? array_merge($val, $val['get_team_goods']) : $val;
                $val = $val['get_goods'] ? array_merge($val, $val['get_goods']) : $val;
                $val['goods_name'] = $val['goods_name'] ?? '';
                $val['team_price'] = $val['team_price'] ?? 0;
                $list[] = $val;
            }
        }

        return $list;
    }

    /**
     * 插入开团活动信息
     *
     * @param int $uid
     * @param int $flow_type
     * @param array $flow
     * @return mixed
     */
    public static function addTeamLog($uid = 0, $flow_type = 0, $flow = [])
    {
        $cart_goods = Cart::where('parent_id', 0)->where('is_gift', 0)->where('rec_type', $flow_type)->where('user_id', $uid)->where('is_checked', 1)
            ->select('goods_id')
            ->first();
        $cart_goods = $cart_goods ? $cart_goods->toArray() : [];

        if (empty($cart_goods)) {
            return 0;
        }

        $team['t_id'] = $flow['t_id'] ?? '';//拼团活动id
        $team['goods_id'] = $cart_goods['goods_id'] ?? 0;//拼团商品id
        $team['start_time'] = TimeRepository::getGmTime();
        $team['status'] = 0;

        $log_id = TeamLog::insertGetId($team);
        return $log_id;
    }

    /**
     * 更改拼团状态
     * @param $team_id
     * @return bool
     */
    public function updateTeamLogStatua($team_id = 0)
    {
        if (empty($team_id)) {
            return false;
        }

        return TeamLog::where('team_id', $team_id)->update(['status' => 1]);
    }

    /**
     * 更改拼团参团数量
     *
     * @param int $id
     * @param int $goods_id
     * @param int $limit_num
     * @return bool
     */
    public function updateTeamLimitNum($id = 0, $goods_id = 0, $limit_num = 0)
    {
        if (empty($id) || empty($goods_id)) {
            return false;
        }

        return TeamGoods::where('id', $id)->where('goods_id', $goods_id)->update(['limit_num' => $limit_num]);
    }

    /**
     * 付款更新拼团信息记录
     * @param int $team_id
     * @param int $team_parent_id
     * @param int $user_id
     */
    public function updateTeamInfo($team_id = 0, $team_parent_id = 0, $user_id = 0)
    {
        if ($team_id > 0) {
            // 拼团信息
            $res = $this->teamIsFailure($team_id);
            if (empty($res)) {
                return false;
            }

            //验证拼团是否成功
            $team_count = OrderInfo::where('team_id', $team_id)
                ->where('pay_status', PS_PAYED)
                ->where('extension_code', 'team_buy')
                ->count();

            if ($team_count > 0 && $team_count >= $res['team_num']) {
                // 更新团状态（1成功）
                TeamLog::where('team_id', $team_id)
                    ->update(['status' => 1]);

                $team_order = OrderInfo::select('order_sn', 'user_id')
                    ->where('team_id', $team_id)
                    ->where('pay_status', PS_PAYED)
                    ->where('extension_code', 'team_buy')
                    ->get();
                $team_order = $team_order ? $team_order->toArray() : [];
                if ($team_order) {
                    // 拼团成功提示会员等待发货
                    if (file_exists(MOBILE_WECHAT)) {
                        foreach ($team_order as $key => $vo) {
                            $pushData = [
                                'keyword1' => ['value' => $vo['order_sn'], 'color' => '#173177'],
                                'keyword2' => ['value' => $res['goods_name'], 'color' => '#173177']
                            ];
                            $url = dsc_url('/#/team/wait') . '?' . http_build_query(['team_id' => $team_id], '', '&');
                            app(\App\Modules\Wechat\Services\WechatService::class)->push_template('OPENTM407456411', $pushData, $url, $vo['user_id']);
                        }
                    }
                    /* 更新会员订单信息 */
                    foreach ($team_order as $key => $vo) {
                        $dbRaw = [
                            'order_team_num' => "order_team_num - 1",
                        ];
                        $dbRaw = BaseRepository::getDbRaw($dbRaw);
                        UserOrderNum::where('user_id', $vo['user_id'])->where('order_team_num', '>', 0)->update($dbRaw);
                    }
                }
            }

            //统计增加拼团人数
            TeamGoods::where('id', $res['id'])->where('goods_id', $res['goods_id'])->increment('limit_num', 1);

            if (file_exists(MOBILE_WECHAT)) {
                // 开团成功提醒
                if ($team_parent_id > 0) {
                    $pushData = [
                        'keyword1' => ['value' => $res['goods_name'], 'color' => '#173177'],
                        'keyword2' => ['value' => $res['team_price'] . lang('team.yuan'), 'color' => '#173177'],
                        'keyword3' => ['value' => $res['team_num'], 'color' => '#173177'],
                        'keyword4' => ['value' => lang('team.ordinary'), 'color' => '#173177'],
                        'keyword5' => ['value' => $res['validity_time'] . lang('team.hours'), 'color' => '#173177']
                    ];
                    $url = dsc_url('/#/team/wait') . '?' . http_build_query(['team_id' => $team_id], '', '&');
                    app(\App\Modules\Wechat\Services\WechatService::class)->push_template('OPENTM407307456', $pushData, $url, $user_id);
                } else {
                    // 参团成功通知
                    $pushData = [
                        'first' => ['value' => lang('team.team_success')],
                        'keyword1' => ['value' => $res['goods_name'], 'color' => '#173177'],
                        'keyword2' => ['value' => $res['team_price'] . lang('team.yuan'), 'color' => '#173177'],
                        'keyword3' => ['value' => $res['validity_time'] . lang('team.hours'), 'color' => '#173177']
                    ];
                    $url = dsc_url('/#/team/wait') . '?' . http_build_query(['team_id' => $team_id], '', '&');
                    app(\App\Modules\Wechat\Services\WechatService::class)->push_template('OPENTM400048581', $pushData, $url, $user_id);
                }
            }
        }
    }


    /**
     * 记录修改订单状态
     * @param int $order_id 订单id
     * @param string $action_user 操作人员
     * @param array $order 订单信息
     * @param string $action_note 变动说明
     * @return  void
     */
    public static function orderActionChange($order_id = 0, $action_user = 'admin', $order = [], $action_note = '')
    {
        $time = TimeRepository::getGmTime();

        $action_log = [
            'order_id' => $order_id,
            'action_user' => $action_user,
            'order_status' => $order['order_status'],
            'shipping_status' => $order['shipping_status'],
            'pay_status' => $order['pay_status'],
            'action_note' => $action_note,
            'log_time' => $time
        ];

        OrderAction::insert($action_log);

        /* 更新订单信息 */
        OrderInfo::where('order_id', $order_id)->update($order);
    }

    /**
     * 检测拼团失败，退款到余额或原路退款（在线支付）
     *
     * @param int $team_id
     * @throws \Exception
     */
    public function checkRefund($team_id = 0)
    {
        //失败拼团订单
        $order_list = self::teamUserOrderRefund($team_id);
        if ($order_list) {
            foreach ($order_list as $key => $val) {
                // 在线退款 状态
                $is_paid = false;
                // 余额退款 状态
                $surplus_is_paid = false;
                $order = [
                    'order_amount' => $val['order_amount']
                ];
                // - 订单如果使用了余额 退余额
                $surplus = empty($val['surplus']) ? 0 : $val['surplus'];
                if ($surplus > 0) {
                    $order['surplus'] = 0;
                    $money_paid = $val['money_paid'] ?? 0;
                    $order['money_paid'] = ($money_paid > 0 && $money_paid >= $surplus) ? $money_paid - $surplus : 0;
                    $order['order_amount'] = $order['order_amount'] + $surplus;
                    // 退款到账户余额 并记录会员账目明细
                    $change_desc = trans('team.team_order_fail_refound') . $val['order_sn'] . '，' . trans('team.team_money') . '：' . $surplus;
                    $surplus_is_paid = AccountService::logAccountChange($val['user_id'], $surplus, 0, 0, 0, $change_desc);
                }

                // - 订单在线支付部分 原路退款
                $money_paid = empty($val['money_paid']) ? 0 : $val['money_paid'];
                if ($money_paid > 0) {
                    // 原路退款
                    $return_order = [
                        'order_id' => $val['order_id'],
                        'pay_id' => $val['pay_id'],
                        'pay_status' => $val['pay_status'],
                        'referer' => $val['referer'],
                        'return_sn' => $val['order_sn'],
                        'ru_id' => $val['ru_id'],
                    ];
                    $is_paid = OrderRefoundService::refoundPay($return_order, $money_paid);
                }

                if ($surplus_is_paid == true || $is_paid == true) {

                    // - 订单在线支付部分 原路退款
                    if ($money_paid > 0) {
                        $order['money_paid'] = 0;
                        $order['order_amount'] = $order['order_amount'] + $money_paid;
                    }

                    // - 订单使用了储值卡 退储值卡
                    $use_val = OrderRefoundService::returnValueCardMoney($val['order_id']);
                    if ($use_val > 0) {
                        $order['order_amount'] = $order['order_amount'] + $use_val;
                    }

                    //记录订单操作记录
                    $action_note = trans('team.team_order_fail_refound');

                    // 修改订单状态为已取消，付款状态为未付款
                    $order['order_status'] = OS_CANCELED;
                    $order['to_buyer'] = trans('team.cancel_order_reason'); // 拼团失败
                    $order['pay_status'] = PS_REFOUND;
                    $order['pay_time'] = 0;
                    $order['shipping_status'] = $val['shipping_status'];
                    self::orderActionChange($val['order_id'], 'admin', $order, $action_note);

                    /* 更新会员拼团订单信息 */
                    DB::table('user_order_num')->where('user_id', $val['user_id'])->where('order_team_num', '>', 0)->decrement('order_team_num', 1);

                    // 检查商品库存
                    //--库存管理 use_storage 1为开启 0为未启用-- stock_dec_time：0发货时,  1 SDT_PLACE 为下单时, 2 SDT_PAID 为付款时
                    if (config('shop.use_storage') == '1' && (config('shop.stock_dec_time') == SDT_PLACE || config('shop.stock_dec_time') == SDT_PAID)) {
                        self::changeOrderGoodsStorage($val['order_id'], false, SDT_PLACE);
                    }

                    // 拼团失败退款通知
                    if (file_exists(MOBILE_WECHAT)) {
                        $pushData = [
                            'keyword1' => ['value' => $val['order_sn'], 'color' => '#173177'],
                            'keyword2' => ['value' => $val['goods_name'], 'color' => '#173177'],
                            'keyword3' => ['value' => trans('team.team_order_fail_refound'), 'color' => '#173177'],
                            'keyword4' => ['value' => $this->dscRepository->getPriceFormat($val['team_price']), 'color' => '#173177']
                        ];
                        $url = dsc_url('/#/user/orderDetail/' . $val['order_id']);
                        app(\App\Modules\Wechat\Services\WechatService::class)->push_template('OPENTM400940587', $pushData, $url, $val['user_id']);
                    }
                }
            }
        }
    }

    /**
     * 查询参与拼团的数量
     * @param int $t_id
     * @return int
     */
    public function get_team_log_count($t_id = 0)
    {
        if (empty($t_id)) {
            return 0;
        }
        return DB::table('team_log')->where('t_id', $t_id)->count();
    }

    /**
     * 修改拼团订单商品库存
     * @param int $order_id 订单号
     * @param bool $is_dec 是否减少库存
     * @param int $storage 减库存的时机，1，下订单时；0，发货时；
     */
    public static function changeOrderGoodsStorage($order_id = 0, $is_dec = true, $storage = 0)
    {
        $res = [];
        /* 查询订单商品信息 */
        switch ($storage) {
            case 0:
                $res = OrderGoods::where('order_id', $order_id)->where('is_real', 1)->groupBy('goods_id')->groupBy('product_id')
                    ->selectRaw('sum(send_number) as num, goods_id,max(extension_code) as extension_code, product_id, model_attr, warehouse_id, area_id, area_city')->get();
                $res = $res ? $res->toArray() : [];
                break;
            case 1:
                $res = OrderGoods::where('order_id', $order_id)->where('is_real', 1)->groupBy('goods_id')->groupBy('product_id')
                    ->selectRaw('sum(goods_number) as num, goods_id,max(extension_code) as extension_code, product_id, model_attr, warehouse_id, area_id, area_city')->get();
                $res = $res ? $res->toArray() : [];
                break;
        }

        if ($res) {
            foreach ($res as $key => $row) {
                if ($row['extension_code'] != "package_buy") {
                    if ($is_dec) {
                        self::changeStorageGoods($row['goods_id'], $row['product_id'], -$row['num'], $row['warehouse_id'], $row['area_id'], $row['area_city'], $row['model_attr']);
                    } else {
                        self::changeStorageGoods($row['goods_id'], $row['product_id'], $row['num'], $row['warehouse_id'], $row['area_id'], $row['area_city'], $row['model_attr']);
                    }
                }
            }
        }
    }

    /**
     * 商品库存增与减 货品库存增与减
     *
     * @param int $goods_id
     * @param int $product_id
     * @param int $number
     * @param int $warehouse_id
     * @param int $area_id
     * @param int $area_city
     * @param int $model_attr
     * @return bool true，成功；false，失败；
     */
    public static function changeStorageGoods($goods_id = 0, $product_id = 0, $number = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0, $model_attr = -1)
    {
        if ($number == 0) {
            return true; // 值为0即不做、增减操作，返回true
        }

        if (empty($goods_id) || empty($number)) {
            return false;
        }

        if ($model_attr != -1) {
            $model_attr = Goods::where('goods_id', $goods_id)->value('model_attr');
            $model_attr = $model_attr ? $model_attr : 0;
        }

        $number = ($number > 0) ? '+' . $number : $number;

        $abs_number = abs($number);

        /* 处理货品库存 */
        if (!empty($product_id)) {
            if ($model_attr == 1) {
                $query = ProductsWarehouse::whereRaw(1);
            } elseif ($model_attr == 2) {
                $query = ProductsArea::whereRaw(1);
            } else {
                $query = Products::whereRaw(1);
            }

            if ($number < 0) {
                $set_update = "IF(product_number >= $abs_number, product_number $number, 0)";
            } else {
                $set_update = "product_number $number";
            }

            $other = BaseRepository::getDbRaw(['product_number' => $set_update]);
            $res = $query->where('goods_id', $goods_id)->where('product_id', $product_id)->update($other);

            return (bool)$res;
        } else {
            /* 处理商品库存 */
            if ($model_attr == 1 || $model_attr == 2) {
                $set_update = "IF(region_number >= $abs_number, region_number $number, 0)";
            } else {
                $set_update = "IF(goods_number >= $abs_number, goods_number $number, 0)";
            }

            if ($model_attr == 1) {
                $other = BaseRepository::getDbRaw(['region_number' => $set_update]);

                $res = WarehouseGoods::where('goods_id', $goods_id)->where('region_id', $warehouse_id)->update($other);
            } elseif ($model_attr == 2) {

                $query = WarehouseAreaGoods::where('goods_id', $goods_id)->where('region_id', $area_id);
                if (config('shop.area_pricetype') == 1) {
                    $query = $query->where('city_id', $area_city);
                }

                $other = BaseRepository::getDbRaw(['region_number' => $set_update]);

                $res = $query->update($other);
            } else {
                $other = BaseRepository::getDbRaw(['goods_number' => $set_update]);
                $res = Goods::where('goods_id', $goods_id)->update($other);
            }

            return (bool)$res;
        }
    }
}
